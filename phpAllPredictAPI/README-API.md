# 📊 Deriv Candle Analysis API Integration

## สารบัญ
1. [ภาพรวมระบบ](#ภาพรวมระบบ)
2. [ไฟล์ที่เกี่ยวข้อง](#ไฟล์ที่เกี่ยวข้อง)
3. [วิธีใช้งาน](#วิธีใช้งาน)
4. [API Documentation](#api-documentation)
5. [การทดสอบ](#การทดสอบ)

---

## ภาพรวมระบบ

ระบบนี้เชื่อมต่อข้อมูล Candle Data จาก Deriv API เข้ากับระบบวิเคราะห์ AI 5 ตัว:
- 🤖 **Claude** - AI Candlestick Analyzer
- 💬 **ChatGPT** - Trade Analyzer
- 🔍 **DeepSeek** - Advanced Candlestick Analyzer
- 📈 **NoSort** - Class Trade (No Sort)
- 📊 **WithSort** - Class Trade (With Sort)

### Data Flow:
```
deriv-full-analysis.html 
    ↓ (fetch candles from Deriv WebSocket)
    ↓ (POST to api-receive-candle.php)
api-receive-candle.php
    ↓ (process and call index.php)
index.php
    ↓ (analyze with 5 AI models)
    ↓ (return JSON results)
api-receive-candle.php
    ↓ (save to output.json)
    ↓ (return to frontend)
display.php
    ↓ (show results in HTML table)
```

---

## ไฟล์ที่เกี่ยวข้อง

### Core Files:
| ไฟล์ | หน้าที่ |
|------|---------|
| `deriv-full-analysis.html` | Frontend - เชื่อมต่อ Deriv WebSocket, แสดงกราฟและผลวิเคราะห์ |
| `api-receive-candle.php` | Bridge API - รับข้อมูล candle และส่งให้ index.php |
| `index.php` | Core Analysis Engine - วิเคราะห์ด้วย AI 5 ตัว |
| `display.php` | Full Report Display - แสดงผลเป็นตาราง HTML |
| `output.json` | ไฟล์ผลลัพธ์ล่าสุด |

### Support Files:
| ไฟล์ | หน้าที่ |
|------|---------|
| `test-api.html` | หน้าทดสอบ API Integration |
| `phpserver.bat` | Batch file สำหรับเริ่ม PHP Server |
| `RawData/` | โฟลเดอร์เก็บข้อมูล candle |

---

## วิธีใช้งาน

### 1. เริ่ม PHP Server
```bash
.\phpserver.bat
```
หรือ
```bash
php -S localhost:8000
```

### 2. เปิดหน้าวิเคราะห์
เปิด browser แล้วไปที่:
```
http://localhost:8000/deriv-full-analysis.html
```

### 3. เลือกการตั้งค่า
- **Asset**: เลือกประเภทสินทรัพย์ (R_10, R_25, R_50, ฯลฯ)
- **Granularity**: เลือกกรอบเวลา (60s, 120s, 300s, ฯลฯ)
- **Mode**: เลือก Count (จำนวนแท่ง) หรือ Date Range (ช่วงเวลา)

### 4. โหลดข้อมูล
กดปุ่ม **"📈 Load & Analyze"**

### 5. ดูผลลัพธ์
- ระบบจะแสดงกราฟ Candlestick
- แสดงการทำนายของ AI แต่ละตัว
- แสดง Consensus (ข้อสรุปรวม)
- กดปุ่ม **"📊 View Full Report & Details"** เพื่อดูตารางแบบเต็ม

---

## API Documentation

### Endpoint: `api-receive-candle.php`

#### Request:
**Method:** `POST`  
**Content-Type:** `application/json`

**Body:**
```json
{
  "candles": [
    {
      "time": 1234567890,
      "open": 100.5,
      "high": 101.0,
      "low": 100.0,
      "close": 100.8
    },
    ...
  ],
  "assetCode": "R_10"
}
```

#### Response (Success):
```json
{
  "success": true,
  "message": "Analysis completed",
  "data": {
    "labStJson": [
      {
        "asset": "R_10",
        "TradeNoIndex": 1,
        "SuggestTimeCandle": "15:30:45",
        "resultColor": "🟢Green",
        "Claude": {
          "SuggestColor": "Green",
          "WinStatus": "💲Win",
          "LossCon": 0,
          "MaxlossConClaude": 2
        },
        "ChatGPT": {...},
        "DeepSeek": {...},
        "NoSort": {...},
        "WithSort": {...}
      },
      ...
    ]
  },
  "stats": {
    "totalCandles": 100,
    "totalTrades": 80,
    "assetCode": "R_10"
  }
}
```

#### Response (Error):
```json
{
  "success": false,
  "error": "Error message here"
}
```

---

## การทดสอบ

### วิธีที่ 1: ใช้หน้า Test API
เปิด browser ไปที่:
```
http://localhost:8000/test-api.html
```

**Test 1:** ส่งข้อมูล Sample Candles (สร้างจากโค้ด)  
**Test 2:** โหลดจากไฟล์ RawData/rawData2.json

### วิธีที่ 2: ใช้ curl
```bash
curl -X POST http://localhost:8000/api-receive-candle.php \
  -H "Content-Type: application/json" \
  -d '{
    "candles": [
      {"time": 1234567890, "open": 100, "high": 101, "low": 99, "close": 100.5}
    ],
    "assetCode": "R_10"
  }'
```

### วิธีที่ 3: ใช้ JavaScript Fetch
```javascript
fetch('http://localhost:8000/api-receive-candle.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    candles: [...],
    assetCode: 'R_10'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## Output Files

### output.json
ไฟล์ JSON ที่เก็บผลลัพธ์ล่าสุดจากการวิเคราะห์

**โครงสร้าง:**
```json
{
  "labStJson": [
    {
      "asset": "R_10",
      "TotalData": 100,
      "TradeNoIndex": 1,
      "SuggestTimeCandle": "15:30:45",
      "StartTime": "14/09/2026 15:00",
      "SuggestIndex": 20,
      "resultColor": "🟢Green",
      "Claude": {
        "timeCandle": 1234567890,
        "SuggestColor": "Green",
        "WinStatus": "💲Win",
        "LossCon": 0,
        "MaxlossConClaude": 2
      },
      ...
    }
  ]
}
```

### RawData/api-candle-data.json
ไฟล์ debug ที่เก็บข้อมูล candle ที่ได้รับล่าสุด

---

## Tips & Troubleshooting

### ❓ ไม่มีข้อมูลแสดง
- ตรวจสอบว่า PHP Server ทำงานอยู่
- เช็ค Console ใน Browser (F12) ดู error
- ตรวจสอบว่าไฟล์ index.php ใช้งานได้

### ❓ Error: "Invalid data format"
- ตรวจสอบว่าส่งข้อมูล candles และ assetCode ครบถ้วน
- แต่ละ candle ต้องมี time, open, high, low, close

### ❓ Analysis ช้า
- ลดจำนวน candles ที่ส่งมา
- ปิด debug logging ใน index.php

### ❓ ต้องการเพิ่ม AI Model
- แก้ไข index.php → เพิ่ม AI class ใหม่
- แก้ไข display.php → เพิ่ม column ใหม่
- แก้ไข deriv-full-analysis.html → แสดงผล AI ใหม่

---

## การพัฒนาต่อ (Future Enhancements)

- [ ] เพิ่มการบันทึกลง Database
- [ ] Real-time Analysis (WebSocket)
- [ ] Historical Data Comparison
- [ ] Export to Excel/CSV
- [ ] Win Rate Chart & Visualization
- [ ] Alert System (Email/LINE Notify)
- [ ] Multi-Asset Analysis

---

## License & Credits

Created for Deriv Trading Analysis  
Powered by Claude + ChatGPT + DeepSeek + Custom Trading Algorithms

---

📅 Last Updated: September 14, 2026  
📝 Version: 1.0.0
