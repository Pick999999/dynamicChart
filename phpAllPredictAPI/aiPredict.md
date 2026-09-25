# 🤖 สรุปแนวทางและวิธีการ Predict Action ของ AI แต่ละตัว

เอกสารนี้สรุปแนวคิด กลไกการคำนวณ ตัวชี้วัด (Indicators) และเงื่อนไขการตัดสินใจ (Decision Logic) ในการทำนายทิศทางแท่งเทียนถัดไป (**Green / CALL** หรือ **Red / PUT**) ของโมเดล AI ทั้ง 5 ตัวในโปรเจกต์ `phpAllPredictAPI`

---

## 📑 สารบัญโมเดล AI

1. [🤖 Claude (CandlestickAnalyzerClaude)](#1--claude-candlestickanalyzerclaude)
2. [💬 ChatGPT (TradeAnalyzer)](#2--chatgpt-tradeanalyzer)
3. [🔍 DeepSeek (AdvancedCandlestickAnalyzer)](#3--deepseek-advancedcandlestickanalyzer)
4. [📈 NoSort (clsTradeV3 - NoSorted)](#4--nosort-clstradev3---nosorted)
5. [📊 WithSort (clsTradeV3 - Sorted by Specificity)](#5--withsort-clstradev3---sorted-by-specificity)
6. [⚖️ ตารางเปรียบเทียบเชิงลึก](#6-️-ตารางเปรียบเทียบจุดเด่นของแต่ละโมเดล)

---

## 1. 🤖 Claude (`CandlestickAnalyzerClaude`)
- **ไฟล์ต้นทาง**: `Claude/candleAnalyzerClaude.php`
- **แนวคิดหลัก**: **Pattern Recognition + Multi-Indicator Consensus (โหวตเสียงตามสัญญาณพฤติกรรมราคา)**
- **ลักษณะการทำงาน**: ใช้การนับคะแนนสัญญาณกระทิง (Bullish Signals) เทียบกับสัญญาณหมี (Bearish Signals) จาก 4 องค์ประกอบหลัก

### ⚙️ ปัจจัยที่ใช้คำนวณ:
1. **Candlestick Pattern (รูปแบบแท่งเทียนล่าสุด)**:
   - สัญญาณขึ้น (+Bullish): `Bullish Engulfing`, `Hammer`, `Morning Star`, `Piercing Line`
   - สัญญาณลง (+Bearish): `Bearish Engulfing`, `Hanging Man`, `Shooting Star`, `Evening Star`
2. **EMA Trend & Position (EMA3 & EMA5)**:
   - หาก `EMA(3) > EMA(5)` ➔ +Bullish, มิฉะนั้น ➔ +Bearish
   - หาก `Close > EMA(3)` ➔ +Bullish, มิฉะนั้น ➔ +Bearish
3. **Bollinger Bands (Period: 20, StdDev: 2)**:
   - **Overbought/Oversold Bounce**:
     - ถ้าราคาปิด `Close < Lower Band` ➔ +Bullish (มองว่าราคาแตะแนวรับล่างแล้วจะเด้งกลับ)
     - ถ้าราคาปิด `Close > Upper Band` ➔ +Bearish (มองว่าราคาแตะแนวต้านบนแล้วจะย่อตัว)
   - **Mean Reversion (การกลับเข้าหาเส้นกลาง)**:
     - ถ้าราคาปิด `Close > Middle Band` ➔ +Bearish (มองว่ามีแรงดึงกลับเข้าหาเส้นกลาง)
     - ถ้าราคาปิด `Close < Middle Band` ➔ +Bullish (มองว่ามีแรงดึงขึ้นหาเส้นกลาง)
4. **Overall Trend (แนวโน้มภาพรวม 10 แท่งล่าสุด)**:
   - นับจำนวนแท่งเขียว vs แดงใน 10 แท่งที่ผ่านมา ถ้าแท่งเขียวมากกว่าถือเป็น `Bullish` ➔ +Bullish, มิฉะนั้น ➔ +Bearish

### 🎯 การตัดสินใจ (Decision Rule):
$$\text{Bullish Probability (\%)} = \left( \frac{\text{Bullish Signals}}{\text{Total Signals}} \right) \times 100$$
$$\text{Bearish Probability (\%)} = \left( \frac{\text{Bearish Signals}}{\text{Total Signals}} \right) \times 100$$
- ถ้า **$\text{Bullish \%} > \text{Bearish \%}$** ➔ ทำนาย **🟢 Green (CALL)**
- นอกนั้น ➔ ทำนาย **🔴 Red (PUT)**

---

## 2. 💬 ChatGPT (`TradeAnalyzer`)
- **ไฟล์ต้นทาง**: `Chatgpt/candleAnalyzerChatGPT.php`
- **แนวคิดหลัก**: **Technical Momentum Adjustment (ปรับน้ำหนักความน่าจะเป็นจากเส้นเทคนิคอล)**
- **ลักษณะการทำงาน**: เริ่มต้นที่ค่า Base Probability **50% Green / 50% Red** แล้วใช้ค่าทางเทคนิคอล 4 กลุ่มใหญ่มาบวก/ลบความน่าจะเป็น

### ⚙️ ปัจจัยที่ใช้คำนวณ (`predictNextCandle`):
1. **RSI (Period: 14)**:
   - ถ้า `RSI > 70` (Overbought ซื้อมากเกินไป) ➔ **Red +20**, Green -20
   - ถ้า `RSI < 30` (Oversold ขายมากเกินไป) ➔ **Green +20**, Red -20
2. **MACD (Fast: 12, Slow: 26, Signal: 9)**:
   - ถ้า `MACD Line > 0` (โมเมนตัมบวก) ➔ **Green +15**, Red -15
   - ถ้า `MACD Line <= 0` (โมเมนตัมลบ) ➔ **Red +15**, Green -15
3. **Stochastic RSI (Period: 14)**:
   - ถ้า `Stoch RSI > 80` (ภาวะซื้อตึงตัว) ➔ **Red +10**, Green -10
   - ถ้า `Stoch RSI < 20` (ภาวะขายตึงตัว) ➔ **Green +10**, Red -10
4. **ADX (Period: 14 - Strength Confirmation)**:
   - ถ้า `ADX > 25` (เทรนด์แข็งแรงชัดเจน) ➔ หนุนความน่าจะเป็นให้ฝั่งที่มีโมเมนตัมเด่นขึ้น (+10)

*(นอกจากนี้ยังมีฟังก์ชันเวอร์ชัน V2 ที่รองรับการวิเคราะห์ Ichimoku Cloud, ATR Volatility และ EMA Crossover อีก 10 ปัจจัย)*

### 🎯 การตัดสินใจ (Decision Rule):
- จำกัดค่าให้อยู่ในช่วง 0% - 100%
- ถ้า **$\text{Probability Green} > \text{Probability Red}$** ➔ ทำนาย **🟢 Green (CALL)**
- นอกนั้น ➔ ทำนาย **🔴 Red (PUT)**

---

## 3. 🔍 DeepSeek (`AdvancedCandlestickAnalyzer`)
- **ไฟล์ต้นทาง**: `DeepSeek/CandlestickAnalyzer_DeepSeek.php`
- **แนวคิดหลัก**: **Weighted Multi-Factor Quantitative Scoring (โมเดลคำนวณคะแนนถ่วงน้ำหนักเชิงปริมาณ)**
- **ลักษณะการทำงาน**: นำ 7 ปัจจัยสำคัญมาคูณกับค่าน้ำหนัก (Weight) เฉพาะตัว แล้วรวมเป็น Total Score ก่อนแปลงกลับมาเป็นความน่าจะเป็น

### ⚙️ ปัจจัยและน้ำหนัก (7 Factors & Weights):
| ปัจจัยที่วิเคราะห์ | น้ำหนัก (Weight) | เงื่อนไขคะแนนย่อย |
|---|---|---|
| **1. Trend Analysis** | **$\times 1.5$** | Uptrend แข็งแกร่ง (+1.5) / ปานกลาง (+1.0), Downtrend แข็งแกร่ง (-1.5) / ปานกลาง (-1.0) |
| **2. Recent 3 Candles** | **$\times 0.8$** | วัดสัดส่วนสีแท่งเทียน 3 แท่งล่าสุด $(-1.0 \text{ ถึง } +1.0)$ |
| **3. Bollinger Bands** | **$\times 1.2$** | แตะ Lower Band (+1.2 เด้งขึ้น) / แตะ Upper Band (-1.2 ย่อลง) |
| **4. EMA Crossover** | **$\times 1.0$** | EMA3 ตัดขึ้น EMA5 (+1.2) / EMA3 ตัดลง EMA5 (-1.2) |
| **5. RSI (14)** | **$\times 1.3$** | < 30 (+1.5), > 70 (-1.5), > 50 (+0.5), < 50 (-0.5) |
| **6. ADX Trend Strength** | **$\times 1.1$** | ถ้า ADX > 25 และ Trend เป็นบวก (+1.0) / Trend เป็นลบ (-1.0) |
| **7. Ichimoku Cloud** | **$\times 1.4$** | Bullish Crossover หรือ Above Cloud (+1.5), Below Cloud (-1.5), Inside Cloud (-0.5) |

### 🎯 การตัดสินใจ (Decision Rule):
$$\text{Total Score} = \left( \sum (\text{Factor}_i \times \text{Weight}_i) \right) \times 5$$
$$\text{Green Probability (\%)} = \text{Clamp}(50 + \text{Total Score}, 15\%, 85\%)$$
$$\text{Red Probability (\%)} = 100 - \text{Green Probability}$$
- ถ้า **$\text{Green Probability} > \text{Red Probability}$** ➔ ทำนาย **🟢 Green (CALL)**
- นอกนั้น ➔ ทำนาย **🔴 Red (PUT)**

---

## 4. 📈 NoSort (`clsTradeV3` ➔ `noSortGetAction.php`)
- **ไฟล์ต้นทาง**: `ClsTrade/clsTradeV3.php`, `ClsTrade/noSortGetAction.php`
- **แนวคิดหลัก**: **Sequential Microstructure Rule Engine (ระบบกฎโครงสร้างจุลภาคตามลำดับเดิม)**
- **ลักษณะการทำงาน**: 
  - คำนวณอินดิเคเตอร์เชิงลึก: ขนาด Pip (`pipSize`), ผลต่าง Pip (`delTapip`), ความชัน EMA3 (`ema3SlopeValue`, `ema3slopeDirection`), MACD Height และ MACD Convergence/Divergence
  - วิ่งตรวจสอบตามกฎกว่า 1,700 บรรทัด (ตั้งแต่ `Code 1-1`, `Code 1-2`, ... จนถึงขั้นตอนสุดท้าย) โดย**ไม่จัดเรียงความซับซ้อนของเงื่อนไข**

### ⚙️ ตัวอย่างลำดับกฎ (Sequential Flow):
1. **ตรวจสอบความชันหลัก (Baseline Slope)**:
   - ถ้า `ema3slopeDirection == 'Down'` ➔ กำหนด Action เบื้องต้นเป็น `PUT` (`Red`)
   - ถ้า `ema3slopeDirection == 'Up'` ➔ กำหนด Action เบื้องต้นเป็น `CALL` (`Green`)
2. **ตรวจสอบ Overrides & Contextual Conditions**:
   - นำเงื่อนไขย่อย เช่น ขนาดตัวเทียน, การหดตัวของ MACD, ไส้เทียน มาปรับเปลี่ยน Action ตามลำดับขั้น
3. **ส่งค่าออก**: คืนค่าแอ็กชันสุดท้ายที่ผ่านการประเมิน

### 🎯 การตัดสินใจ:
- ผลลัพธ์ `CALL` ➔ ทำนาย **🟢 Green**
- ผลลัพธ์ `PUT` ➔ ทำนาย **🔴 Red**

---

## 5. 📊 WithSort (`clsTradeV3` ➔ `sortGetAction.php`)
- **ไฟล์ต้นทาง**: `ClsTrade/clsTradeV3.php`, `ClsTrade/sortGetAction.php`
- **แนวคิดหลัก**: **Prioritized Specificity-First Decision Tree (ระบบกฎคัดกรองเคสซับซ้อนสูงก่อน / Early Exit)**
- **ลักษณะการทำงาน**: 
  - ปัญหาของระบบกฎทั่วไปคือ กฎกว้างๆ อาจดักจับสัญญาณก่อนที่กฎที่มีเงื่อนไขจำเพาะจะได้ทำงาน
  - `WithSort` จึงนำชุดกฎเดียวกันมา**จัดเรียงลำดับใหม่ (Sort by Condition Count)** จากเคสที่มีเงื่อนไขตรวจสอบหนาแน่นที่สุด (7-8 เงื่อนไข) ไปหาเคสทั่วไป

### ⚙️ โครงสร้างการคัดกรอง (Priority Hierarchy):
1. **กลุ่มเคสความแม่นยำสูง (High Specificity - 7 เงื่อนไข)**:
   - เช็คประวัติการกลับตัวย้อนหลัง 4 แท่ง (`PreviousTurnType`, `Back2`, `Back3`, `Back4`)
   - เช็คค่าความชันจำเพาะ `abs(ema3SlopeValue) < 5`
   - เช็คความขัดแย้งของเส้น EMA (`emaConflict` เช่น `53G`, `35R`)
   - เช็ค `MACDConvergence == 'Conver'`
   - **เมื่อตรวจพบเคสที่ตรงกัน จะทำการ `return` ออกทันที (Early Exit)** โดยไม่ตรวจเคสอื่นต่อ
2. **กลุ่มเคสปานกลาง (Medium Specificity - 4-6 เงื่อนไข)**:
   - รูปแบบการตัดกันของราคา (`CutPointType`), สัดส่วนความยาวแท่ง
3. **กลุ่มเคสทั่วไป (General Cases - 1-3 เงื่อนไข)**:
   - หากไม่ตรงกับเคสจำเพาะใดๆ จึงค่อยใช้เกณฑ์ความชันและทิศทางพื้นฐาน

### 🎯 การตัดสินใจ:
- ผลลัพธ์ `CALL` ➔ ทำนาย **🟢 Green**
- ผลลัพธ์ `PUT` ➔ ทำนาย **🔴 Red**

---

## 6. ⚖️ ตารางเปรียบเทียบจุดเด่นของแต่ละโมเดล

| โมเดล AI | วิธีการประมวลผล | จุดเด่น | สไตล์การเทรดที่เหมาะสม |
|---|---|---|---|
| **🤖 Claude** | โหวตสัดส่วนสัญญาณ (Pattern + EMA + BB) | ตรวจจับรูปแบบแท่งเทียนกลับตัวร่วมกับกรอบ Bollinger ได้แม่นยำ | Reversal & Swing Trading ในสภาวะตลาดไซด์เวย์ถึงเทรนด์อ่อน |
| **💬 ChatGPT** | ปรับคะแนนความน่าจะเป็นจากโมเมนตัม | ให้น้ำหนักกับ RSI, MACD, Stoch RSI ชัดเจน หลีกเลี่ยงช่วง Overbought/Oversold | Momentum Trading & Trend Continuation |
| **🔍 DeepSeek** | รวมคะแนน 7 ปัจจัยถ่วงน้ำหนัก (Weighted Score) | ครอบคลุมหลายมิติมากที่สุด มีระบบ Ichimoku Cloud และ ADX กรองความแข็งแกร่งเทรนด์ | Trend Following & Strong Breakout |
| **📈 NoSort** | เช็คกฎ Microstructure ตามลำดับ (Sequential) | วิเคราะห์การเคลื่อนที่ระดับ Pip และ Slope อย่างละเอียดตามขั้นตอนดั้งเดิม | Scalping & Micro-movement |
| **📊 WithSort** | เช็คกฎที่มีเงื่อนไขซับซ้อนก่อน (Sorted Early-Exit) | ดักจับแพทเทิร์นพิเศษที่มีความแม่นยำสูงก่อนที่จะหลุดไปเคสทั่วไป | High-probability Pattern Matching |

---

*สร้างขึ้นสำหรับโปรเจกต์ `d:\Rust\phpAllPredictAPI`*
