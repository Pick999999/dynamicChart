# คู่มือการเลือกและประเมิน Trade Spot (pkTrend Entry)

เอกสารอธิบายรายละเอียดพารามิเตอร์, เกณฑ์การประเมิน, และการตั้งค่าระบบ **Trade Spot** ทั้งผ่าน UI หน้ากราฟ และผ่าน Options ในฟังก์ชันคำนวณ

---

## 1. พารามิเตอร์บนหน้า UI (กราฟ macd.html)

| พารามิเตอร์ / Control | ตัวเลือกที่ปรับได้ | หน้าที่ / ผลลัพธ์ |
| :--- | :--- | :--- |
| **Trade Type** (`#tradeSpotTypeSelect`) | • `ALL`<br>• `CALL`<br>• `PUT` | กรองทิศทางจุดเข้าเทรด:<br>- `ALL`: แสดงทั้ง Call (BUY) และ Put (SELL)<br>- `CALL`: แสดงเฉพาะจุดเข้าซื้อฝั่งขึ้น (🟢/🔄)<br>- `PUT`: แสดงเฉพาะจุดเข้าซื้อฝั่งลง (🔴/🔄) |
| **Marker Format** (`#tradeSpotFormatSelect`) | • `LONG`<br>• `SHORT` | รูปแบบการแสดงผลบนแท่งเทียน:<br>- `LONG`: แสดงป้ายข้อความเต็ม เช่น `🟢 CALL: UP-CONFIRM`<br>- `SHORT`: แสดงเฉพาะหัวลูกศร Up/Down เพื่อความโล่งตา |

---

## 2. พารามิเตอร์เงื่อนไขการคำนวณ (Pktrendentryevaluator.js)

ฟังก์ชัน `evaluateTradeSpot(pkTrend, options)` และ `evaluatePkTrendEntry(pkTrend, options)` รองรับการส่ง `options` เข้าไปปรับแต่งเกณฑ์ความเข้มงวดได้ดังนี้:

```javascript
const customOptions = {
  minTrendScoreStrong: 40,      // คะแนนเทรนด์ขั้นต่ำ
  minCloseConvictionUp: 0.70,   // ตำแหน่งราคาปิดฝั่ง Up (0.0 - 1.0)
  maxCloseConvictionDown: 0.30, // ตำแหน่งราคาปิดฝั่ง Down (0.0 - 1.0)
  allowTrapReversal: true,      // สัญญาณสวนเทรนด์จาก Trap (Bull/Bear Trap)
  allowSpikeContinuation: true  // สัญญาณตามแรงแท่ง Spike ต่อเนื่อง
};
```

### รายละเอียดพารามิเตอร์แต่ละตัว:
1. **`minTrendScoreStrong` (ค่าเริ่มต้น: `40`)**:
   - คะแนนความแรงของเทรนด์ (Trend Score) ขั้นต่ำที่ระบบจะยอมรับเป็นสัญญาณตามเทรนด์
   - *ตัวอย่างการปรับ:* หากปรับเป็น `70` ระบบจะเลือกเฉพาะแท่งที่เทรนด์แรงระดับ **ULTRA STRONG** เท่านั้น (สัญญาณน้อยลงแต่แม่นขึ้น)
2. **`minCloseConvictionUp` (ค่าเริ่มต้น: `0.7` หรือ 70%)**:
   - `closePosition` ของแท่งเทียนฝั่ง CALL (ราคาปิดต้องอยู่ชิดส่วนบน 70%-100% ของความยาวแท่ง เพื่อยืนยันว่าแรงซื้อยังคุมอยู่)
3. **`maxCloseConvictionDown` (ค่าเริ่มต้น: `0.3` หรือ 30%)**:
   - `closePosition` ของแท่งเทียนฝั่ง PUT (ราคาปิดต้องอยู่ชิดส่วนล่าง 0%-30% เพื่อยืนยันว่าแรงขายกดมิด)
4. **`allowTrapReversal` (ค่าเริ่มต้น: `true`)**:
   - เปิด/ปิด การให้สัญญาณแบบสวนเทรนด์เมื่อเกิด Trap ชัดเจน เช่น `RJ-BULLTRAP-STRONG` (เปิด PUT สวน) หรือ `RJ-BEARTRAP-STRONG` (เปิด CALL สวน)
5. **`allowSpikeContinuation` (ค่าเริ่มต้น: `true`)**:
   - เปิด/ปิด สัญญาณตามแรงทะลุของแท่ง Spike เช่น `SPK-CONTINUE-UP` / `SPK-CONTINUE-DN`

---

## 3. ตัวกรองความปลอดภัย (Safety Filters ที่ระบบเช็คอัตโนมัติ)
- **`isWhipsaw = true`**: ตรวจจับช่วงตลาดฟันปลา (`ENTERING_WHIPSAW`, `CONFIRMED_WHIPSAW`) และตัดทิ้งอัตโนมัติเพื่อป้องกันสัญญาณหลอก
- **`group = 'Sideways'`**: กรองช่วงตลาดพักตัว ไม่มีทิศทางออก
- **Spike Trap ปลายคลื่น**: กรองรูปแบบเสี่ยงสูงทิ้งอัตโนมัติ เช่น `SPK-BULLTRAP`, `SPK-BEARTRAP`, `SPK-NODIRECTION` และ `SPK-HESITANT-UP`

---

## 4. โครงสร้างสถิติและช่วงข้อมูลจริง (จากไฟล์ pkTrend.json)

| trendStrength | ช่วงคะแนน trendScore (จริง) |
| :--- | :--- |
| `ULTRA_STRONG_UP` | 70 ถึง 100 |
| `STRONG_UP` | 40 ถึง 68 |
| `MILD_UP` | 20 ถึง 38 |
| `NEUTRAL` | -18 ถึง 18 |
| `MILD_DOWN` | -38 ถึง -20 |
| `STRONG_DOWN` | -68 ถึง -40 |
| `ULTRA_STRONG_DOWN` | -100 ถึง -70 |
| `WHIPSAW_ZONE` | -55 ถึง 55 (ต้องกรองด้วย `isWhipsaw` เสมอ) |

- **ค่าเฉลี่ย `closePosition`**:
  - `UP-CONFIRM`: 0.84 (ช่วง 0.50–1.00)
  - `DN-CONFIRM`: 0.18 (ช่วง 0.00–0.48)
  - `SPK-CONTINUE-UP`: 0.88 (ช่วง 0.66–1.00)
  - `SPK-CONTINUE-DN`: 0.06 (ช่วง 0.00–0.31)

- **กลุ่ม Case Code ทั้งหมด (20 รูปแบบหลัก + รหัสตัวเลข `CodeNo` ช่วยจำ)**:

| CodeNo | Case Code | หมวดหมู่ (Group) | แนวโน้ม (Trend) | คำอธิบายและความหมาย |
| :---: | :--- | :--- | :--- | :--- |
| **1** | `UP-CONFIRM` | **Up** | UpTrend | ทำ Higher High ปิดสูงกว่าแท่งก่อนหน้า ยืนยันแรงซื้อคุมตลาด |
| **2** | `DN-CONFIRM` | **Down** | DownTrend | ทำ Lower Low ปิดต่ำกว่าแท่งก่อนหน้า ยืนยันแรงขายคุมตลาด |
| **3** | `SPK-CONTINUE-UP` | **Spike** | UpTrend | แท่ง Spike พุ่งขึ้นแรง ปิดโซนบน โมเมนตัมขาขึ้นต่อเนื่อง |
| **4** | `SPK-CONTINUE-DN` | **Spike** | DownTrend | แท่ง Spike ทิ่มลงแรง ปิดโซนล่าง โมเมนตัมขาลงต่อเนื่อง |
| **5** | `SPK-BULLTRAP` | **Spike** | Rejected | แท่ง Spike พุ่งขึ้นแรงแต่โดนกดกลับปิดต่ำ (Bull Trap ระวังกลับตัวลง) |
| **6** | `SPK-BEARTRAP` | **Spike** | Rejected | แท่ง Spike ทิ่มลงแรงแต่โดนดันกลับปิดสูง (Bear Trap ระวังกลับตัวขึ้น) |
| **7** | `SPK-NODIRECTION` | **Spike** | Sideways | แท่ง Spike ผันผวนกว้าง แต่ไร้ทิศทางชัดเจน |
| **8** | `SPK-HESITANT-UP` | **Spike** | Sideways | แท่ง Spike ฝั่งขึ้น แต่ปิดโซนกลาง ตลาดเริ่มลังเล |
| **9** | `EG-BULLISH` | **Engulfing** | UpTrend | กลืนกินแท่งก่อนหน้า (Outside Bar) และปิดโซนบน แรงซื้อชัดเจน |
| **10** | `EG-BEARISH` | **Engulfing** | DownTrend | กลืนกินแท่งก่อนหน้า (Outside Bar) และปิดโซนล่าง แรงขายชัดเจน |
| **11** | `RJ-BULLTRAP-STRONG`| **Rejected** | Rejected | ทำ High ใหม่แต่โดนเทขายกดปิดต่ำสุดแท่ง สัญญาณ Bull Trap รุนแรง |
| **12** | `RJ-BEARTRAP-STRONG`| **Rejected** | Rejected | ทำ Low ใหม่แต่โดนซื้อดันกลับปิดสูงสุดแท่ง สัญญาณ Bear Trap รุนแรง |
| **13** | `RJ-FOLLOWFAIL-UP` | **Rejected** | Rejected | ทำ High ใหม่แต่ปิดต่ำกว่าแท่งก่อน โมเมนตัมขึ้นเริ่มแผ่ว |
| **14** | `RJ-FOLLOWFAIL-DN` | **Rejected** | Rejected | ทำ Low ใหม่แต่ปิดสูงกว่าแท่งก่อน โมเมนตัมลงเริ่มแผ่ว |
| **15** | `RJ-UPWICK-WEAK` | **Rejected** | Rejected | ไส้บนยาว เจอแรงขายกดช่วงท้ายแท่ง แรงซื้อเริ่มอ่อนแรง |
| **16** | `RJ-LOWWICK-WEAK` | **Rejected** | Rejected | ไส้ล่างยาว เจอแรงซื้อดันช่วงท้ายแท่ง แรงขายเริ่มอ่อนแรง |
| **17** | `SD-INSIDEBAR` | **Sideways** | Sideways | ราคาอยู่ภายในกรอบของแท่งก่อนหน้าทั้งหมด พักตัวรอ Breakout |
| **18** | `SD-MIXEDSIGNAL` | **Sideways** | Sideways | สัญญาณฝั่งขึ้นขัดแย้งกันเอง ตลาดสับสน |
| **19** | `SD-MIXEDSIGNAL-DN`| **Sideways** | Sideways | สัญญาณฝั่งลงขัดแย้งกันเอง ตลาดสับสน |
| **20** | `SYS-NODATA` | **System** | null | ข้อมูลแท่งเทียนไม่เพียงพอสำหรับเปรียบเทียบ (< 3 แท่ง) |

> 💡 **รูปแบบขยายเพิ่มเติม (Extended Cases: 21–27)**:
> - `21`: `SD-DOJI` (แท่ง Doji เนื้อเทียนเล็กมาก)
> - `22`: `SD-FLATRESISTANCE` (ทดสอบแนวต้านเดิมซ้ำ ไม่ผ่าน)
> - `23`: `SD-FLATSUPPORT` (ทดสอบแนวรับเดิมซ้ำ ไม่หลุด)
> - `24`: `SD-NOSTRUCTURE` (ไม่มีโครงสร้างราคาที่ชัดเจน)
> - `25`: `EG-INDECISION` (Engulfing ปิดกลางแท่ง ลังเล)
> - `26`: `SPK-HESITANT-DN` (Spike ฝั่งลง แต่ปิดโซนกลาง ลังเล)
> - `27`: `SPK-WHIPSAW` (Spike แกว่งกว้างรุนแรงสับขาหลอก)

