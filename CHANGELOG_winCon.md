# 📝 Changelog - เพิ่มฟิลด์ winCon

## 🆕 สิ่งที่เพิ่มเข้ามา

### 1. Database Schema Update
เพิ่มฟิลด์ `winCon` ในตาราง `gcpTradeData`:

```sql
ALTER TABLE gcpTradeData 
ADD COLUMN winCon INT DEFAULT NULL AFTER WinStatus;
```

### 2. ฟิลด์ที่แสดงในตาราง

ตอนนี้ตารางใน `analysisTrade.html` แสดงฟิลด์เพิ่มเติม:

| Column | Field Name | Description |
|--------|------------|-------------|
| Symbol | symbol | รหัส asset (เช่น R_10, R_25) |
| Contract ID | contractId | รหัสสัญญา |
| Purchase Time | purchaseTime | เวลาซื้อ |
| **Action** | **thisAction** | **CALL หรือ PUT** ⭐ |
| **Strategy** | **codeStrategy** | **กลยุทธ์การเทรด** ⭐ |
| Buy Price | MoneyTrade | ราคาซื้อ |
| Sell Price | sell_price | ราคาขาย |
| Profit | ThisProfit | กำไร/ขาดทุน |
| **Win/Loss Con** | **winCon / lossCon** | **จำนวนชนะ/แพ้ติดกัน** ⭐ |
| Status | WinStatus | WIN หรือ LOSS |

### 3. ไฟล์ที่แก้ไข

#### `php/save_gcp_trades.php`
- ✅ เพิ่ม `winCon INT DEFAULT NULL` ใน CREATE TABLE
- ✅ เพิ่ม AUTO ALTER TABLE เพื่อเพิ่มคอลัมน์หากยังไม่มี
- ✅ เพิ่ม `:winCon` parameter ใน INSERT statement
- ✅ เพิ่มการ bind `winCon` จาก JSON data

#### `analysisTrade.html`
- ✅ อัปเดตหัวตารางเพิ่ม: Action, Strategy, Win/Loss Con
- ✅ แก้ไข `renderTradeTableFromArray()` แสดงฟิลด์ใหม่
- ✅ เพิ่มสีสัน:
  - CALL = สีฟ้า (#00d4ff)
  - PUT = สีส้ม (#fb923c)
  - Win Count = สีเขียว
  - Loss Count = สีแดง
- ✅ อัปเดต colspan จาก 8 → 10

## 📊 ตัวอย่างการแสดงผล

```
┌─────────┬──────────────┬──────────────────┬────────┬──────────┬──────────┬──────────┬──────────┬──────────┬────────┐
│ Symbol  │ Contract ID  │ Purchase Time    │ Action │ Strategy │ Buy Price│Sell Price│  Profit  │ Win/Loss │ Status │
├─────────┼──────────────┼──────────────────┼────────┼──────────┼──────────┼──────────┼──────────┼──────────┼────────┤
│ 1HZ50V  │ 117505424299 │ 5/9/2569 09:27:00│ CALL   │ TREND_UP │  $32.00  │  $0.00   │ -$32.00  │   L:2    │  LOSS  │
│ 1HZ75V  │ 117508136899 │ 5/9/2569 09:25:00│ CALL   │ TREND_UP │  $1.00   │  $0.00   │ +$0.88   │   W:1    │  WIN   │
└─────────┴──────────────┴──────────────────┴────────┴──────────┴──────────┴──────────┴──────────┴──────────┴────────┘
```

## 🔄 กระบวนการบันทึกข้อมูล

```javascript
// ข้อมูลจาก VPS (trades.json)
{
  "contractId": 117505424299,
  "symbol": "1HZ50V",
  "thisAction": "CALL",
  "codeStrategy": "TREND_UP",
  "winCon": 0,        // ⭐ ใหม่
  "lossCon": 2,
  "MoneyTrade": 32.00,
  "ThisProfit": -32.00,
  "WinStatus": "loss"
}
```

↓ บันทึกผ่าน `save_gcp_trades.php`

↓ เก็บลง MySQL table `gcpTradeData`

↓ แสดงผลใน `analysisTrade.html`

## 📝 SQL Query ตัวอย่าง

### ดูข้อมูล winCon และ lossCon
```sql
SELECT 
    symbol,
    thisAction,
    codeStrategy,
    winCon,
    lossCon,
    ThisProfit,
    WinStatus
FROM gcpTradeData
WHERE serverCode = 2
ORDER BY purchaseTime DESC
LIMIT 20;
```

### สรุปสถิติ win/loss streaks
```sql
SELECT 
    symbol,
    MAX(winCon) as max_win_streak,
    MAX(lossCon) as max_loss_streak,
    AVG(CASE WHEN WinStatus = 'win' THEN winCon ELSE NULL END) as avg_win_streak,
    AVG(CASE WHEN WinStatus = 'loss' THEN lossCon ELSE NULL END) as avg_loss_streak
FROM gcpTradeData
WHERE serverCode = 2
GROUP BY symbol;
```

### วิเคราะห์ตาม Strategy
```sql
SELECT 
    codeStrategy,
    COUNT(*) as total_trades,
    SUM(CASE WHEN ThisProfit >= 0 THEN 1 ELSE 0 END) as wins,
    ROUND(AVG(ThisProfit), 2) as avg_profit,
    MAX(winCon) as max_win_streak,
    MAX(lossCon) as max_loss_streak
FROM gcpTradeData
WHERE serverCode = 2 AND codeStrategy IS NOT NULL
GROUP BY codeStrategy
ORDER BY wins DESC;
```

## 🚀 การทดสอบ

1. **เปิด analysisTrade.html**
   ```
   http://localhost/analysisTrade.html
   ```

2. **เลือก VPS และดาวน์โหลดข้อมูล**

3. **ตรวจสอบตารางว่าแสดงฟิลด์ครบ**:
   - ✅ Action (CALL/PUT)
   - ✅ Strategy
   - ✅ Win/Loss Con (W:1, L:2, etc.)

4. **ตรวจสอบ Database**:
   ```sql
   SELECT * FROM gcpTradeData 
   WHERE winCon IS NOT NULL 
   LIMIT 10;
   ```

## 📋 Migration สำหรับฐานข้อมูลเดิม

หากมีตาราง `gcpTradeData` อยู่แล้ว ให้รัน:

```bash
mysql -u root -p dynamic_chart < add_winCon_column.sql
```

หรือใน phpMyAdmin:
1. เปิดฐานข้อมูล `dynamic_chart`
2. Import file `add_winCon_column.sql`

หรือ PHP จะ ALTER TABLE อัตโนมัติเมื่อรันครั้งแรก!

## ✅ เสร็จสมบูรณ์!

ตอนนี้ระบบพร้อมแสดงและบันทึกฟิลด์:
- ✅ `codeStrategy` - กลยุทธ์การเทรด
- ✅ `thisAction` - CALL หรือ PUT
- ✅ `winCon` - จำนวนครั้งที่ชนะติดกัน
- ✅ `lossCon` - จำนวนครั้งที่แพ้ติดกัน

---

**Version**: 1.1  
**Updated**: 2026-09-05  
**Author**: Kiro AI Assistant
