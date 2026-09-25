# แผนงานการพัฒนาระบบ Sync การตั้งค่า (Settings.json) จาก Dashboard สู่โปรแกรม Rust บน VPS

## 1. บทนำและวัตถุประสงค์ (Overview & Objectives)

เอกสารนี้จัดทำขึ้นเพื่อใช้เป็นพิมพ์เขียว (Blueprint) และขั้นตอนการปฏิบัติงาน (Implementation Roadmap) สำหรับการส่งข้อมูลการตั้งค่า (`settings.json`) จาก **Dashboard** (ทั้งบน Localhost หรือบนโดเมนจริง เช่น `https://lovetoshopmall.com`) ไปยังโปรแกรม **Rust Indicator Engine / Trading Bot** ที่ทำงานอยู่บน Cloud VPS (เช่น `gpkderiv.shop`) โดยตรงแบบอัตโนมัติ

### วัตถุประสงค์หลัก:
1. สามารถปรับค่าพารามิเตอร์ของ Indicator/Strategy จากหน้า Dashboard แล้วกดซิงค์ (Sync) ไปยัง VPS แต่ละเครื่องได้ทันที
2. ฝั่งโปรแกรม Rust สามารถรับข้อมูล JSON, บันทึกลงไฟล์ `settings.json`, และอัปเดตตัวแปรในหน่วยความจำ (Hot-Reload) ได้ทันทีโดยไม่ต้องรีสตาร์ทโปรแกรม
3. มีระบบความปลอดภัย (Security / Token Authentication) ป้องกันคำสั่งที่ไม่ได้รับอนุญาต
4. รองรับการเรียกข้ามโดเมน (CORS) และรองรับการเชื่อมต่อแบบปลอดภัยผ่าน HTTPS (ป้องกันปัญหา Mixed Content)

---

## 2. สถาปัตยกรรมระบบ (System Architecture)

```text
┌─────────────────────────────────────────────────────────────┐
│                 Client (Web Dashboard)                      │
│   - http://localhost/dynamicChart/dashboard                 │
│   - https://lovetoshopmall.com                              │
│                                                             │
│   [เลือก VPS] ──► [กรอก/แก้ไข Settings] ──► [กดปุ่ม "ซิงค์ไป VPS"]│
└──────────────────────────────┬──────────────────────────────┘
                               │
                               │ HTTPS POST /api/settings
                               │ Header: Authorization: Bearer <SECRET_TOKEN>
                               │ Body: { settings: { ... } }
                               ▼
┌─────────────────────────────────────────────────────────────┐
│              VPS Server (เช่น gpkderiv.shop)                 │
│                                                             │
│   ┌─────────────────────────────────────────────────────┐   │
│   │ 1. Nginx Web Server (Reverse Proxy & SSL)           │   │
│   │    - พอร์ต 443 (HTTPS - Let's Encrypt)              │   │
│   │    - จัดการ SSL Certificate                         │   │
│   │    - ส่งต่อคำขอเข้าพอร์ตภายใน (Proxy Pass)          │   │
│   └──────────────────────────┬──────────────────────────┘   │
│                              │ Proxy to 127.0.0.1:8088      │
│                              ▼                              │
│   ┌─────────────────────────────────────────────────────┐   │
│   │ 2. Rust Application (Axum / Actix-web API Service)  │   │
│   │    - เปิดรับคำขอที่ 127.0.0.1:8088                  │   │
│   │    - ตรวจสอบ Token (Authentication Middleware)      │   │
│   │    - ตรวจสอบความถูกต้องของ JSON Data                │   │
│   │    - บันทึกลง `settings.json`                       │   │
│   │    - อัปเดต Memory State ของระบบคำนวณทันที           │   │
│   └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. รายละเอียดการพัฒนาในแต่ละส่วน (Component Breakdown)

### ส่วนที่ 1: ฝั่ง Rust Application (VPS Engine)
* **Framework ที่แนะนำ:** `axum` (พัฒนาโดยทีมงาน Tokio มั่นคง ประสิทธิภาพสูง และรองรับ Asynchronous เต็มรูปแบบ) หรือ `actix-web`
* **ฟังก์ชันการทำงาน:**
  1. **Endpoints:**
     * `GET /api/settings` : ดึงค่าการตั้งค่าปัจจุบันที่กำลังใช้งาน
     * `POST /api/settings` : รับค่าการตั้งค่าใหม่ นำไปบันทึกและอัปเดตระบบ
     * `GET /api/health` : เช็คสถานะการทำงานของ Rust Engine (Online/Offline)
  2. **Security & Middleware:**
     * **CORS Middleware (`tower-http::cors`):** อนุญาต Origins ที่ระบุ (เช่น `https://lovetoshopmall.com`, `http://localhost`) และเปิดรับ Header `Authorization`, `Content-Type`
     * **Auth Guard:** ตรวจสอบ Header `Authorization: Bearer <CONFIG_SYNC_TOKEN>`
  3. **File I/O & Hot Reload:**
     * ใช้ `tokio::fs` หรือ `std::fs` เขียนทับไฟล์ `settings.json`
     * ใช้ `Arc<RwLock<AppSettings>>` ใน Rust เพื่อแชร์ค่าการตั้งค่าไปยังเธรดคำนวณ Indicator/Trading ทันทีโดยไม่ต้อง Restart

---

### ส่วนที่ 2: ฝั่ง VPS Networking & Reverse Proxy (Nginx)
เพื่อความปลอดภัยและป้องกันข้อผิดพลาด Mixed Content (เบราว์เซอร์จาก HTTPS จะไม่อนุญาตให้ยิงหา HTTP ทั่วไป)

* **การตั้งค่า Nginx Config (ตัวอย่าง):**
  ```nginx
  server {
      server_name gpkderiv.shop;

      location /api/ {
          proxy_pass http://127.0.0.1:8088/;
          proxy_http_version 1.1;
          proxy_set_header Host $host;
          proxy_set_header X-Real-IP $remote_addr;
          proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
          proxy_set_header X-Forwarded-Proto $scheme;
      }

      listen 443 ssl;
      # SSL Cert จาก Certbot / Let's Encrypt
  }
  ```

---

### ส่วนที่ 3: ฝั่ง Dashboard (Frontend & UI)
* **การเชื่อมโยงกับฐานข้อมูล `vpsMaster`:**
  * หน้า Dashboard จะดึงรายการ VPS จากฐานข้อมูล (มี Public IP / Domain เช่น `gpkderiv.shop`)
  * ผู้ใช้เลือกได้ว่าจะส่งการตั้งค่าไปที่ VPS เครื่องไหน
* **Interface ที่ต้องเพิ่ม:**
  1. **หน้า Settings Editor / Form:** กรอกพารามิเตอร์ต่างๆ ของ Indicator / Strategy หรือ JSON Code Editor
  2. **ปุ่ม "บันทึกลง Local (`settings.json`)"** (กรณีรันบนเครื่องตนเอง)
  3. **ปุ่ม "🚀 ซิงค์การตั้งค่าไปยัง VPS"**
     * แสดง Modal ให้เลือก VPS ปลายทาง
     * สั่ง `fetch()` ยิง POST ข้อมูล JSON ไปยัง URL ของ VPS
     * แสดง Toast แจ้งเตือนเมื่อสำเร็จ หรือแสดงข้อความ Error หากเชื่อมต่อไม่ได้
  4. **ช่องใส่ Secret Token / API Key:** บันทึกไว้ใน LocalStorage เพื่อความปลอดภัย ไม่ต้องพิมพ์ซ้ำทุกครั้ง

---

## 4. โครงสร้างข้อมูล JSON และ API Contract

### Request: `POST /api/settings`
* **Headers:**
  ```http
  Content-Type: application/json
  Authorization: Bearer my_super_secret_token_123456
  ```
* **Body:**
  ```json
  {
    "updated_by": "dashboard_admin",
    "timestamp": 1788396000,
    "settings": {
      "symbol": "R_100",
      "granularity": 60,
      "parameters": {
        "fast_period": 12,
        "slow_period": 26,
        "signal_period": 9
      },
      "trade_enabled": true,
      "max_stake": 50.0
    }
  }
  ```

### Response:
* **กรณีสำเร็จ (HTTP 200 OK):**
  ```json
  {
    "success": true,
    "message": "Settings updated and saved to settings.json successfully",
    "timestamp": 1788396001
  }
  ```
* **กรณีปฏิเสธสิทธิ์ (HTTP 401 Unauthorized):**
  ```json
  {
    "success": false,
    "error": "Invalid or missing Authorization token"
  }
  ```

---

## 5. ขั้นตอนการดำเนินงานจริงเมื่อเริ่มทำ (Step-by-Step Roadmap)

| เฟส (Phase) | รายละเอียดงาน | ผลลัพธ์ที่ได้ |
| :--- | :--- | :--- |
| **เฟส 1: Rust API Service** | - เพิ่ม crate `axum`, `tokio`, `tower-http`, `serde_json` ใน Rust Project<br>- สร้าง API handler รับ POST `/api/settings`<br>- ทำระบบ Auth Token และเขียนไฟล์ `settings.json` | ได้ API ฝั่ง Rust พร้อมรับคำขอในเครื่อง |
| **เฟส 2: ทดสอบบน Localhost** | - ทดสอบยิงผ่าน Postman / cURL<br>- ทดสอบส่งจาก Dashboard (Local) เข้า Rust พอร์ตทดสอบ | ยืนยันว่าไฟล์ `settings.json` ถูกเขียนจริงและถูกต้อง |
| **เฟส 3: ติดตั้งบน VPS & SSL** | - อัปเดตโปรแกรม Rust ขึ้น VPS (เช่น `gpkderiv.shop`)<br>- ตั้งค่า Nginx Reverse Proxy และ SSL Let's Encrypt<br>- เปิด Systemd Service เพื่อให้ Rust รันอัตโนมัติ | VPS พร้อมรับคำขอจากภายนอกผ่าน HTTPS |
| **เฟส 4: พัฒนา UI บน Dashboard** | - สร้างหน้าจอตั้งค่า Settings UI ในโฟลเดอร์ `dashboard/pages/`<br>- ทำปุ่มเลือก VPS จากฐานข้อมูล `vpsMaster`<br>- เชื่อมโยงระบบส่งและแจ้งเตือนผลลัพธ์ผ่าน Toast | ระบบใช้งานได้สมบูรณ์จากหน้าเว็บ |

---

## 6. ข้อควรระวังและการป้องกันปัญหา (Gotchas & Best Practices)

1. **ปัญหา Mixed Content:** อย่าเรียก `http://` จากหน้า Dashboard ที่เปิดด้วย `https://` เด็ดขาด ต้องต่อผ่าน HTTPS เสมอ
2. **CORS:** ตรวจสอบให้แน่ใจว่า Rust API อนุญาต Header `Authorization` และ Method `OPTIONS` (Preflight request)
3. **Backup ก่อนเขียนทับ:** ฝั่ง Rust ควรทำ Backup `settings.json.bak` เผื่อกรณี JSON ที่ส่งมามีข้อผิดพลาด
4. **Token Security:** เก็บ Secret Token ไว้ใน `.env` ฝั่ง VPS และไม่ Commit ขึ้น Git Public
