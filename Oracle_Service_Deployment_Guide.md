# คู่มือการติดตั้งและเปิดใช้งาน Service บน Oracle Cloud (Ubuntu)

คู่มือนี้สรุปขั้นตอนทั้งหมดตั้งแต่การสร้าง Systemd Service, การจัดการ Service, การเปิด Firewall (ทั้งระดับ OS และ Oracle Console) จนถึงการเข้าใช้งานผ่าน Browser สำหรับนำไปทำซ้ำกับ Instance อื่นได้ทันที

---

## สารบัญขั้นตอน
1. [สร้างและตั้งค่า Systemd Service](#1-สร้างและตั้งค่า-systemd-service)
2. [สั่งรันและตรวจสอบการทำงานของ Service](#2-สั่งรันและตรวจสอบการทำงานของ-service)
3. [เปิด Firewall ระดับ OS (iptables)](#3-เปิด-firewall-ระดับ-os-iptables)
4. [เปิด Ingress Rules ใน Oracle Cloud Console](#4-เปิด-ingress-rules-ใน-oracle-cloud-console)
5. [ตรวจสอบและเข้าใช้งานผ่าน Browser](#5-ตรวจสอบและเข้าใช้งานผ่าน-browser)
6. [คำสั่งสรุปสำหรับการจัดการ Service](#6-คำสั่งสรุปสำหรับการจัดการ-service)

---

## 1. สร้างและตั้งค่า Systemd Service

โฟลเดอร์เก็บ Service file ของระบบ Linux คือ `/etc/systemd/system/`

รันคำสั่งด้านล่างนี้ใน Terminal เพื่อสร้างไฟล์ `/etc/systemd/system/tradeMultiplex.service`:

```bash
sudo tee /etc/systemd/system/tradeMultiplex.service > /dev/null << 'EOF'
[Unit]
Description=Trade Multiplex Server
After=network.target

[Service]
Type=simple
User=ubuntu
Group=ubuntu
WorkingDirectory=/home/ubuntu/indicatorMultiplex
ExecStart=/home/ubuntu/indicatorMultiplex/target/release/turbo-indicators
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF
```

> **ข้อควรระวัง:**
> - ตรวจสอบ `User` และ `Group` ให้ตรงกับ username ในเครื่อง (เช่น `ubuntu`)
> - ตรวจสอบ Path ของ `WorkingDirectory` และ `ExecStart` ให้มีอยู่จริงและมีสิทธิ์ execute

---

## 2. สั่งรันและตรวจสอบการทำงานของ Service

รันตามลำดับขั้นตอนดังนี้:

```bash
# 1. โหลดการตั้งค่า systemd ใหม่ (ต้องทำทุกครั้งหลังสร้าง/แก้ไฟล์ .service)
sudo systemctl daemon-reload

# 2. เริ่มต้นการทำงานของ Service
sudo systemctl start tradeMultiplex.service

# 3. ตั้งให้ Service เริ่มทำงานอัตโนมัติเมื่อเปิดเครื่อง (Boot on startup)
sudo systemctl enable tradeMultiplex.service

# 4. ตรวจสอบสถานะการทำงาน
sudo systemctl status tradeMultiplex.service
```

### การตรวจสอบสถานะ:
- ต้องขึ้นตัวหนังสือสีเขียวว่า **`Active: active (running)`**
- มี **Main PID** แสดงอยู่
- *(หากติดในหน้า status ให้กดปุ่ม `q` เพื่อออก)*

### ตรวจสอบ Real-time Log ของโปรแกรม:
```bash
sudo journalctl -u tradeMultiplex.service -f
```
*(กด `Ctrl + C` เพื่อออกจากหน้า log)*

---

## 3. เปิด Firewall ระดับ OS (iptables)

บน Ubuntu ของ Oracle Cloud จะบล็อก Port ภายนอกไว้โดยค่าเริ่มต้น ต้องเปิด Port ในระบบก่อน:

```bash
# 1. เพิ่มกฎเปิด Port 3000 ไว้ที่ลำดับแรกสุดของ INPUT table
sudo iptables -I INPUT 1 -p tcp --dport 3000 -j ACCEPT

# 2. ติดตั้งตัวช่วยเซฟกฎ Firewall (ไม่ให้หายเมื่อ Restart เครื่อง)
sudo apt-get update && sudo apt-get install -y iptables-persistent

# 3. เซฟกฎ Firewall ปัจจุบัน
sudo netfilter-persistent save
```

### ตรวจสอบว่าโปรแกรมเปิดรับการเชื่อมต่อจากภายนอก:
```bash
sudo ss -tulpn | grep 3000
```
- ต้องแสดงว่า **`0.0.0.0:3000`** หรือ `*:3000` (หากเป็น `127.0.0.1:3000` แปลว่าโปรแกรมรับเฉพาะ localhost ต้องแก้โค้ดให้ bind `0.0.0.0`)

---

## 4. เปิด Ingress Rules ใน Oracle Cloud Console

Oracle Cloud มี Hardware Firewall (Security List) กั้นอยู่อีกชั้น ต้องเปิดที่หน้าเว็บ Console ด้วย:

1. ล็อกอินเข้า [Oracle Cloud Console](https://cloud.oracle.com/)
2. ไปที่เมนู **Compute** > **Instances** > คลิกเลือกเครื่อง Instance ที่ต้องการ
3. เลื่อนลงมาที่แท็บด้านล่างในส่วน **Instance Information** > คลิกที่ชื่อ **Subnet** (ลิงก์สีฟ้า)
4. คลิกที่ **Security Lists** (มักจะชื่อ *Default Security List for...*)
5. กดปุ่ม **Add Ingress Rules** และกรอกข้อมูล:
   - **Source Type:** `CIDR`
   - **Source CIDR:** `0.0.0.0/0`
   - **IP Protocol:** `TCP`
   - **Source Port Range:** *(เว้นว่างไว้ หรือ All)*
   - **Destination Port Range:** `3000`
   - **Description:** `Trade Multiplex UI`
6. กดปุ่ม **Add Ingress Rules**

---

## 5. ตรวจสอบและเข้าใช้งานผ่าน Browser

1. เช็ค **Public IP** ของเครื่อง Server:
   ```bash
   curl ifconfig.me
   ```

2. เปิด Browser บนคอมพิวเตอร์ของคุณ แล้วพิมพ์ URL:
   ```text
   http://<PUBLIC_IP_ของเครื่อง>:3000
   ```
   *(ตัวอย่าง: `http://161.118.217.177:3000`)*

---

## 6. คำสั่งสรุปสำหรับการจัดการ Service

| การทำงาน | คำสั่ง |
| :--- | :--- |
| **ดูสถานะ** | `sudo systemctl status tradeMultiplex.service` |
| **เริ่มทำงาน** | `sudo systemctl start tradeMultiplex.service` |
| **หยุดทำงาน** | `sudo systemctl stop tradeMultiplex.service` |
| **รีสตาร์ท** | `sudo systemctl restart tradeMultiplex.service` |
| **ดู Log สด (Live)** | `sudo journalctl -u tradeMultiplex.service -f` |
| **ดู Log ย้อนหลัง 50 บรรทัด** | `sudo journalctl -u tradeMultiplex.service -n 50 --no-pager` |
| **แก้ไขไฟล์ Service** | `sudo nano /etc/systemd/system/tradeMultiplex.service` |
