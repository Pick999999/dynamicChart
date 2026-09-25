# 📊 Analysis Trade - คู่มือการใช้งาน

## 🎯 ภาพรวม

`analysisTrade.html` เป็นเครื่องมือสำหรับดาวน์โหลดและวิเคราะห์ข้อมูล Trade History จาก VPS Server โดยอัตโนมัติ พร้อมบันทึกลงฐานข้อมูล MySQL

## ✨ ฟีเจอร์หลัก

### 1. 🖥️ VPS Thumbnail Gallery
- แสดงรายการ VPS Server พร้อมรูปภาพจากตาราง `vpsMaster`
- คลิกเพื่อเลือก VPS ที่ต้องการดาวน์โหลดข้อมูล
- รองรับ Base64 image หรือ placeholder icon

### 2. 📅 Date Picker
- เลือกวันที่สำหรับดาวน์โหลดข้อมูล
- ตั้งค่าเริ่มต้นเป็นวันปัจจุบัน

### 3. ⚙️ Server Code Configuration
- กำหนดรหัส serverCode สำหรับบันทึกลงฐานข้อมูล
- Auto-fill จาก vpsCode ของ VPS ที่เลือก

### 4. 📥 Download & Process
- ดาวน์โหลดไฟล์ ZIP จาก VPS API (`/api/export_trade_data`)
- ผ่าน PHP Backend เพื่อแก้ปัญหา CORS
- แตกไฟล์ ZIP ด้วย JSZip ใน Browser
- บันทึกข้อมูลลง MySQL table `gcpTradeData`

### 5. 📊 Statistics Dashboard
- **Total Trades**: จำนวน trade ทั้งหมด
- **Win Rate**: อัตราการชนะ (%)
- **Total Profit**: กำไร/ขาดทุนรวม
- **Assets Traded**: จำนวน assets

### 6. 💻 Live Console Log
- แสดงสถานะการทำงานแบบ real-time
- มีปุ่ม Clear console

### 7. 📋 Trade History Table
- แสดงรายการ trade ที่ดาวน์โหลด (100 รายการแรก)
- แสดง Symbol, Contract ID, เวลา, ราคา, กำไร/ขาดทุน
- มี badge สำหรับแสดงสถานะ WIN/LOSS

## 🔧 การติดตั้ง

### ข้อกำหนดเบื้องต้น:
1. **Web Server**: Apache (XAMPP)
2. **PHP**: 7.4 หรือสูงกว่า
3. **MySQL Database**: มีตาราง `vpsMaster` และ `gcpTradeData`
4. **PHP Extensions**:
   - cURL (สำหรับดาวน์โหลดไฟล์)

### โครงสร้างไฟล์:
```
htdocs/
├── analysisTrade.html          # หน้าเว็บหลัก
└── php/
    ├── api_vps.php             # API สำหรับดึงข้อมูล VPS
    ├── save_gcp_trades.php     # API สำหรับดาวน์โหลดและบันทึกข้อมูล
    └── db.php                  # Database connection
```

## 🚀 วิธีใช้งาน

### ขั้นตอนที่ 1: เปิดหน้าเว็บ
```
http://localhost/analysisTrade.html
```

### ขั้นตอนที่ 2: เลือก VPS Server
- คลิกที่ VPS thumbnail ที่ต้องการ
- ระบบจะแสดง Target URL และข้อมูล VPS

### ขั้นตอนที่ 3: เลือกวันที่
- เลือกวันที่ที่ต้องการดาวน์โหลดข้อมูล

### ขั้นตอนที่ 4: กำหนด Server Code (Optional)
- ระบบจะ auto-fill จาก VPS Code
- สามารถแก้ไขได้ตามต้องการ

### ขั้นตอนที่ 5: ดาวน์โหลดข้อมูล
- กดปุ่ม **"ดาวน์โหลดข้อมูล"**
- ระบบจะดำเนินการ:
  1. ดาวน์โหลด ZIP จาก VPS ผ่าน PHP
  2. แตกไฟล์ ZIP ด้วย JSZip
  3. บันทึกข้อมูลลง MySQL
  4. แสดงผลสถิติและตารางข้อมูล

## 🔄 กระบวนการทำงาน (Flow)

```
┌─────────────────────────────────────────────────────────┐
│ 1. Browser: เลือก VPS และวันที่                        │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 2. JavaScript: ส่ง POST request ไปยัง PHP Backend     │
│    { action: 'download_and_save', exportUrl, ... }     │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 3. PHP (save_gcp_trades.php):                          │
│    - ดาวน์โหลด ZIP จาก VPS ด้วย cURL                  │
│    - Encode เป็น Base64                                 │
│    - Return กลับไปยัง Browser                           │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 4. JavaScript + JSZip:                                  │
│    - Decode Base64 → Binary                             │
│    - แตกไฟล์ ZIP                                        │
│    - Parse JSON files (trades.json)                     │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 5. JavaScript → PHP API:                                │
│    - ส่งข้อมูล trades ไปบันทึกลง MySQL                │
│    - บันทึกลงตาราง gcpTradeData                        │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 6. Browser: แสดงผลสถิติและตารางข้อมูล                 │
└─────────────────────────────────────────────────────────┘
```

## 🗄️ โครงสร้างฐานข้อมูล

### ตาราง: `vpsMaster`
```sql
CREATE TABLE vpsMaster (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vpsCode VARCHAR(50) NOT NULL,
    vpsName VARCHAR(255) NOT NULL,
    publicIP VARCHAR(255),
    portno VARCHAR(10),
    url VARCHAR(500),
    privateIP VARCHAR(255),
    image LONGTEXT,  -- Base64 encoded image
    remark TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### ตาราง: `gcpTradeData`
```sql
CREATE TABLE gcpTradeData (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serverCode INT DEFAULT 2,
    symbol VARCHAR(50),
    contractId BIGINT,
    purchaseTime BIGINT,
    sellTime BIGINT,
    MoneyTrade DOUBLE,
    ThisProfit DOUBLE,
    WinStatus VARCHAR(30),
    -- ... (more columns)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## 🛠️ การแก้ปัญหา (Troubleshooting)

### ปัญหา: ไม่สามารถโหลดรายการ VPS
**สาเหตุ**: ไม่มีข้อมูลในตาราง `vpsMaster` หรือ PHP API ไม่ทำงาน

**แก้ไข**:
1. ตรวจสอบว่ามีข้อมูลในตาราง `vpsMaster`
2. เช็ค PHP API: `http://localhost/php/api_vps.php`
3. ดู console log ใน browser (F12)

### ปัญหา: Error 404 - Not Found
**สาเหตุ**: VPS API endpoint ไม่ถูกต้อง

**แก้ไข**:
1. ตรวจสอบ URL/IP ของ VPS ในตาราง `vpsMaster`
2. ตรวจสอบว่า VPS มี endpoint `/api/export_trade_data`
3. ทดสอบ URL โดยตรงใน browser

### ปัญหา: Error 500 - Internal Server Error
**สาเหตุ**: PHP ไม่สามารถดาวน์โหลดไฟล์หรือ cURL ไม่ทำงาน

**แก้ไข**:
1. ตรวจสอบว่า cURL extension เปิดใช้งานใน PHP
2. ตรวจสอบ error log ใน `C:\xampp\apache\logs\error.log`
3. ตรวจสอบ console log ใน browser

### ปัญหา: CORS Policy Error
**สาเหตุ**: ปัญหานี้ไม่ควรเกิดเพราะใช้ PHP Backend

**แก้ไข**:
- ถ้ายังเกิด แสดงว่าอาจมีการเรียก API โดยตรงจาก JavaScript
- ตรวจสอบให้แน่ใจว่าใช้ action `download_and_save` ผ่าน PHP

## 📝 API Endpoints

### 1. GET: `/php/api_vps.php`
ดึงรายการ VPS Server จากตาราง `vpsMaster`

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "vpsCode": "VPS-01",
      "vpsName": "Oracle Cloud Free Tier",
      "publicIP": "gpkderiv.shop",
      "portno": "",
      "url": "http://gpkderiv.shop",
      "image": "data:image/png;base64,...",
      ...
    }
  ]
}
```

### 2. POST: `/php/save_gcp_trades.php`
ดาวน์โหลด ZIP และบันทึกข้อมูล

**Request** (Step 1 - Download ZIP):
```json
{
  "action": "download_and_save",
  "exportUrl": "http://gpkderiv.shop/api/export_trade_data?startdatetime=2026-09-05",
  "serverCode": 2,
  "date": "2026-09-05"
}
```

**Response**:
```json
{
  "success": true,
  "zipBase64": "UEsDBBQAAAAIAB...",
  "zipSize": 125843,
  "serverCode": 2,
  "message": "ZIP file downloaded successfully"
}
```

**Request** (Step 2 - Save to DB):
```json
{
  "serverCode": 2,
  "items": [
    {
      "symbol": "R_100",
      "serverCode": 2,
      "trades": [ /* trade objects */ ]
    }
  ]
}
```

## 🎨 Design & UI

- **Theme**: Vision UI Dark Glassmorphism
- **Colors**: Blue gradient (#0075ff → #00d4ff)
- **Responsive**: รองรับทุกขนาดหน้าจอ
- **Animations**: Smooth transitions และ hover effects

## 📦 Dependencies

- **JSZip** (CDN): สำหรับแตกไฟล์ ZIP ใน browser
  ```html
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  ```

## 🔒 Security

- ใช้ PHP Backend เป็นตัวกลางในการดาวน์โหลด (ป้องกัน CORS และ expose ข้อมูล)
- ไม่เก็บ credentials ใน browser
- Validate input ก่อนบันทึกลง database
- Use prepared statements (PDO) ป้องกัน SQL injection

## 📄 License

Internal use only - Dynamic Chart Project

## 👨‍💻 Developer Notes

- ไฟล์หลัก: `analysisTrade.html`
- Backend: `php/save_gcp_trades.php`
- Database: MySQL
- วิธีการแก้ CORS: ใช้ PHP backend ดาวน์โหลดแล้ว return เป็น base64
- Unzip: ใช้ JSZip ฝั่ง browser (ไม่ต้องติดตั้ง PHP ZipArchive extension)

---

**Version**: 1.0  
**Last Updated**: 2026-09-05  
**Created by**: Kiro AI Assistant
