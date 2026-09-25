# 📈 DynamicChart — แพลตฟอร์มวิเคราะห์กราฟและ Smart Money Concepts (SMC)

[![Node.js](https://img.shields.io/badge/Node.js-Built--in_HTTP-339933?style=flat&logo=nodedotjs)](https://nodejs.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat&logo=php)](https://www.php.net/)
[![Deriv API](https://img.shields.io/badge/Deriv%20API-V2%20WebSocket-FF444F?style=flat)](https://deriv.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

**DynamicChart** คือระบบวิเคราะห์กราฟเทคนิคอลและสัญญาณการเทรดแบบเรียลไทม์ ออกแบบมาสำหรับนักเทรดและนักพัฒนาระบบเทรดอัตโนมัติ เชื่อมต่อกับ **Deriv API (WebSocket)** รองรับอินดิเคเตอร์ขั้นสูง โดยเฉพาะกลุ่ม **Smart Money Concepts (SMC)**, การทำนายแนวโน้มแท่งเทียน, และการดึงข้อมูลสถิติการเทรดจาก VPS มาวิเคราะห์

---

## ✨ ฟีเจอร์หลัก (Key Features)

### 1. 🌐 การเชื่อมต่อข้อมูลสด (Deriv API V2 Integration)
- **Auto-Connection**: เชื่อมต่อ WebSocket อัตโนมัติเมื่อเริ่มเรียกดูข้อมูล
- **Candle History & Timeframes**: ดึงประวัติแท่งเทียนย้อนหลัง รองรับ Timeframe หลากหลาย (1m, 3m, 5m, 15m, 30m, 60m, 240m, 1d)
- **Tick Stream**: รับข้อมูล Tick ราคาแบบ Real-time Streaming
- **Active Symbols Sync**: ซิงค์รายชื่อสินทรัพย์จริง (Volatility Indices, Crash/Boom, Forex ฯลฯ) ลงดร็อปดาวน์อัตโนมัติ

### 2. 🧠 Smart Money Concepts (SMC Engine)
- **Market Structure**: ตรวจจับสัญญาณ **CHoCH** (Change of Character) และ **BOS** (Break of Structure)
- **Swing Points**: ระบุจุดสวิง **HH**, **HL**, **LH**, **LL** อัตโนมัติ
- **Order Blocks (OB)**: แรเงากล่องโซนราคา Order Block ตามระดับจริง
- **Fair Value Gaps (FVG)**: ไฮไลท์ช่องว่างราคาที่ไม่สมดุล (Imbalance)
- **Liquidity Zones**: ตรวจจับ Equal Highs / Lows (EQH/EQL) เพื่อระบุจุดสภาพคล่อง
- **Premium & Discount Zones**: เส้น Equilibrium (50%) พร้อมแรเงาโซนราคาถูก/ราคาแพง

### 3. 📊 Technical Indicators & Analysis Engine
- **Moving Averages Suite**: รองรับ EMA, SMA, HMA, WMA พร้อมเลือก Source และสีได้อย่างอิสระ
- **Bollinger Bands (BB)**: กำหนด Period และ StdDev
- **SuperTrend AI & Predictor**: เครื่องมือคาดการณ์ทิศทางแท่งเทียนถัดไป (`predictNextCandle.js`, `phpAllPredictAPI`)
- **Full Analysis Pipeline**: วิเคราะห์แท่งเทียนแบบ Multi-tab และส่งผลลัพธ์เป็น JSON สำหรับนำไปใช้งานต่อ

### 4. 🖥️ VPS Trade History & Sync Management
- **VPS Thumbnail Gallery**: แสดงรายการ VPS และเลือกเพื่อดึงข้อมูลประวัติการเทรด
- **Automated Download & ZIP Extraction**: ดึงประวัติการเทรดและแตกไฟล์เข้าฐานข้อมูล MySQL อัตโนมัติ
- **Win Rate & Profit Dashboard**: คำนวณสรุปกำไรขาดทุน อัตราการชนะ (Win Rate) และสถิติการเทรดแบบเรียลไทม์

### 5. 🎨 ดีไซน์ระดับพรีเมียม (Glassmorphism Trading UI)
- Dark Theme สไตล์กระดานเทรดระดับมืออาชีพ
- พาแนลควบคุมแบบพับเก็บได้ (Collapsible Sidebars) เพื่อขยายพื้นที่แสดงกราฟ
- ระบบบันทึกและโหลดหน้าการตั้งค่า (JSON Config Manager) สามารถบันทึกเป็น Default ได้

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```plaintext
dynamicChart/
├── index.html                  # หน้าจอกราฟหลักและ Trading Dashboard
├── analysisTrade.html          # หน้าดาวน์โหลดและวิเคราะห์ผลเทรดจาก VPS
├── server.js                   # Node.js Web Server (Pure Built-in ไม่ต้องลง npm เพิ่ม)
├── server.bat                  # Script สำหรับรัน Web Server บน Windows
├── dynamicChart.js             # Logic ควบคุมกราฟหลัก
├── Pktrendentryevaluator.js     # โมดูลประเมินจุดเข้าเทรดตามเทรนด์
├── zoneAnalysis.js             # วิเคราะห์โซนราคา
│
├── smc/                        # โมดูล Smart Money Concepts (SMC)
│   ├── SMCChartRenderer.js     # ตัววาดเส้นและโซน SMC บนกราฟ
│   ├── SMCIndicator.js         # อัลกอริทึมคำนวณ CHoCH, BOS, OB, FVG
│   └── backgroundColorZonesPlugin.js # Plugin แรเงาโซนบนกราฟ
│
├── indicator/                  # ชุดเอนจินการวิเคราะห์ทางเทคนิค
│   ├── FullAnalysisEngine.js   # เอนจินวิเคราะห์เต็มรูปแบบ
│   └── case_codes.json         # นิยามโค้ดกรณีวิเคราะห์
│
├── php/                        # Backend API สำหรับเชื่อมต่อ Database & VPS
│   ├── db.php                  # ไฟล์เชื่อมต่อฐานข้อมูล MySQL
│   ├── api_vps.php             # API ดึงข้อมูลและสถานะ VPS
│   └── save_gcp_trades.php     # API บันทึกข้อมูล Trade History
│
├── phpAllPredictAPI/           # โมดูลและ API วิเคราะห์/ทำนายสัญญาณแท่งเทียน
├── pageconfig/                 # โฟลเดอร์เก็บไฟล์ Preset การตั้งค่าหน้าจอ (JSON)
├── rust_debug_demo/            # โมดูลทดสอบโค้ด Rust สำหรับคำนวณ Logic
└── .gitignore                  # กำหนดไฟล์ที่ไม่ push ขึ้น Git (database.db, node_modules)
```

---

## 🚀 การติดตั้งและเริ่มใช้งาน (Getting Started)

### ข้อกำหนดเบื้องต้น (Prerequisites)
1. **Node.js**: เวอร์ชัน 14.x ขึ้นไป (สำหรับรัน Web Server)
2. **PHP & MySQL**: สำหรับผู้ที่ต้องการใช้ฟีเจอร์ฝั่ง Backend / Analysis Trade (เช่น ผ่าน XAMPP หรือ Docker)
3. **เว็บเบราว์เซอร์**: Google Chrome, Edge หรือเบราว์เซอร์สมัยใหม่ที่รองรับ WebSocket

---

### วิธีเปิดใช้งาน

#### ทางเลือกที่ 1: รันผ่าน Node.js (แนะนำสำหรับหน้ากราฟ)
1. ดับเบิ้ลคลิกไฟล์ `server.bat` หรือเปิด Terminal แล้วรัน:
   ```bash
   node server.js
   ```
2. เปิดเบราว์เซอร์ไปที่:
   ```
   http://localhost:3000
   ```

#### ทางเลือกที่ 2: รันผ่าน Apache / XAMPP (สำหรับหน้า PHP APIs)
1. คัดลอกหรือตั้ง Alias โฟลเดอร์โปรเจกต์ไปยังโฟลเดอร์ `htdocs` ของ XAMPP
2. นำเข้าตารางฐานข้อมูลจากไฟล์ `.sql` ในโฟลเดอร์ `php/`
3. เข้าใช้งานผ่าน `http://localhost/dynamicChart/`

---

## ⚙️ การตั้งค่า Deriv API
1. ไปที่เว็บไซต์ [Deriv](https://deriv.com) เพื่อรับ **App ID** หรือ **API Token**
2. ใส่ Token หรือตั้งค่าในเมนู Settings (⚙️) บนหน้าเว็บของ DynamicChart เพื่อเชื่อมต่อบัญชีจริงหรือบัญชี Demo

---

## 🔒 ข้อมูลความปลอดภัยและฐานข้อมูล
* ไฟล์ฐานข้อมูลทดสอบในเครื่อง (`database.db`) และไฟล์แคชต่างๆ ถูกใส่ไว้ใน [`.gitignore`](.gitignore) เพื่อความปลอดภัยและหลีกเลี่ยงข้อจำกัดขนาดไฟล์ของ GitHub
* ห้าม Commit API Token หรือรหัสผ่านที่เป็นความลับขึ้น Public Repository

---

## 📄 ลิขสิทธิ์ (License)
โปรเจกต์นี้เผยแพร่ภายใต้ลิขสิทธิ์ [MIT License](LICENSE)
