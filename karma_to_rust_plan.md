# แผนงานการผสาน KAMA & Choppiness Combined สู่ Rust Backend (karma_to_rust_plan.md)

> **เป้าหมาย**: นำระบบวิเคราะห์ความผันผวนหลายมิติ (Kaufman Efficiency Ratio + Micro Price Action + Dreiss Choppiness Index) จาก `choppinessCombined.js` ไปพัฒนาเป็น Native Rust Module ใน [`full_analysis_ver2.rs`](file:///d:/Rust/turbo-indicators/turbo-indicators_v2/indicators_Multiplex_Ver1/src/full_analysis_ver2.rs) เพื่อให้การวิเคราะห์ฝั่ง Backend ทำงานได้อย่างรวดเร็วระดับ Microsecond รองรับการคำนวณแบบ Real-time และ Backtest ขนาดใหญ่

---

## 1. ภาพรวมสถาปัตยกรรม (Architecture Overview)

ในไฟล์ [`full_analysis_ver2.rs`](file:///d:/Rust/turbo-indicators/turbo-indicators_v2/indicators_Multiplex_Ver1/src/full_analysis_ver2.rs) ปัจจุบันมีการคำนวณ:
* `compute_bb` (Bollinger Bands)
* `BB Squeeze` (Causal Percentile Window)
* `Flat / Choppy Zone` (Middle band slope & Piercing)
* `ChoppinessIndex` (Dreiss CI บนแท่งเทียน 1M เดี่ยวๆ)

### สิ่งที่จะเพิ่มเข้าไปใหม่ (New Capabilities):
เพิ่มฟังก์ชัน **Multi-Timeframe Aggregator & KAMA Micro Engine**:
1. **Aggregator Engine**: รวมแท่งเทียน 1M จำนวน $G$ แท่ง (เช่น 15 แท่ง) เป็นแท่งใหญ่ 15M เสมือน
2. **Kaufman Efficiency Ratio (ER)**: คำนวณ $\text{Net Change} / \text{Total Path}$ บนแท่งย่อย 1M
3. **Micro Price Action Metrics**: คำนวณ Body Ratio และ Direction Switch Count ภายในกลุ่มแท่งย่อย
4. **Macro Dreiss Choppiness**: คำนวณ CHOP 14 คาบบนลำดับแท่งใหญ่ 15M
5. **Combined Evaluator**: ตัดสินสถานะ `is_choppy_combined` ตามโหมด (`OR` / `AND` / `MicroOnly` / `MacroOnly`) พร้อม Fallback ตรรกะกรณีประวัติแท่งเทียนยังไม่ถึง 14 คาบ

---

## 2. แผนการเปลี่ยนแปลงโค้ด (Proposed Code Changes)

### 2.1 ส่วนที่ 1: ขยาย `AppConfigPayload` และ `IndicatorsConfigGroup`
เพิ่มการรับค่า Configuration สำหรับ KAMA & Choppy Combined ใน Struct:

```rust
// ใน full_analysis_ver2.rs

fn default_choppy_combined_mode() -> String { "or".to_string() }
fn default_choppy_group_size() -> usize { 15 }
fn default_choppy_threshold() -> f64 { 61.8 }
fn default_kama_er_threshold() -> f64 { 0.35 }
fn default_body_ratio_threshold() -> f64 { 0.30 }
fn default_min_switches() -> usize { 3 }

#[derive(Debug, Deserialize, Clone)]
#[serde(rename_all = "camelCase")]
pub struct IndicatorsConfigGroup {
    // ... ค่า config เดิม ...
    
    // -- KAMA & Choppiness Combined Config --
    #[serde(default = "default_choppy_combined_mode", alias = "choppyCombinedMode")]
    pub choppy_combined_mode: String,
    
    #[serde(default = "default_choppy_group_size", alias = "choppyGroupSize")]
    pub choppy_group_size: usize,
    
    #[serde(default = "default_choppy_threshold", alias = "choppyThreshold")]
    pub choppy_threshold: f64,
    
    #[serde(default = "default_kama_er_threshold", alias = "kamaErThreshold", alias = "erThreshold")]
    pub kama_er_threshold: f64,
    
    #[serde(default = "default_body_ratio_threshold", alias = "bodyRatioThreshold")]
    pub body_ratio_threshold: f64,
    
    #[serde(default = "default_min_switches", alias = "minSwitches")]
    pub min_switches: usize,
}
```

---

### 2.2 ส่วนที่ 2: โครงสร้างข้อมูลใหม่ (Data Structures)

```rust
/// ข้อมูลแท่งใหญ่ที่เกิดจากการ Aggregate แท่งย่อย 1M พร้อม Micro Metrics
#[derive(Debug, Clone)]
pub struct AggregatedBigCandle {
    pub start_epoch: i64,
    pub end_epoch: i64,
    pub open: f64,
    pub high: f64,
    pub low: f64,
    pub close: f64,
    pub candle_count: usize,
    // Micro metrics
    pub kama_er: f64,
    pub body_ratio: f64,
    pub switch_count: usize,
    pub is_choppy_micro: bool,
    // Macro metrics
    pub macro_chop: Option<f64>,
    pub is_choppy_macro: Option<bool>,
    // Combined result
    pub is_choppy_combined: bool,
}
```

---

### 2.3 ส่วนที่ 3: ฟังก์ชันแกนหลักใน Rust (Core Implementations)

สร้างฟังก์ชัน Pure Rust ที่ตรงกับตรรกะใน `choppinessCombined.js`:

#### 1) คำนวณ Kaufman Efficiency Ratio และ Micro Metrics
```rust
pub fn compute_micro_kama_metrics(
    group: &[OHLCV],
    body_ratio_threshold: f64,
    er_threshold: f64,
    min_switches: usize,
) -> (f64, f64, usize, bool) {
    if group.is_empty() {
        return (0.0, 0.0, 0, false);
    }

    let open = group[0].open;
    let close = group[group.len() - 1].close;
    let high = group.iter().map(|c| c.high).fold(f64::NEG_INFINITY, f64::max);
    let low = group.iter().map(|c| c.low).fold(f64::INFINITY, f64::min);

    let net_change = (close - open).abs();
    let range = high - low;
    let body_ratio = if range > 0.0 { net_change / range } else { 0.0 };

    // Kaufman Efficiency Ratio: Net Change / Sum of 1M Absolute Price Steps
    let mut total_path = 0.0;
    let mut prev_price = open;
    for c in group {
        total_path += (c.close - prev_price).abs();
        prev_price = c.close;
    }
    let efficiency_ratio = if total_path > 0.0 { net_change / total_path } else { 0.0 };

    // Color Switch Count
    let mut switch_count = 0usize;
    let mut prev_color: Option<bool> = None; // true = green, false = red
    for c in group {
        let color = if c.close > c.open {
            Some(true)
        } else if c.close < c.open {
            Some(false)
        } else {
            None
        };
        if let Some(col) = color {
            if let Some(p_col) = prev_color {
                if col != p_col {
                    switch_count += 1;
                }
            }
            prev_color = Some(col);
        }
    }

    let is_choppy_micro = body_ratio <= body_ratio_threshold
        && efficiency_ratio <= er_threshold
        && switch_count >= min_switches;

    (efficiency_ratio, body_ratio, switch_count, is_choppy_micro)
}
```

#### 2) Multi-Timeframe Grouping & Macro Choppiness Engine
```rust
pub fn analyze_choppiness_combined(
    candles: &[OHLCV],
    group_size: usize,
    chop_period: usize,
    chop_threshold: f64,
    body_ratio_threshold: f64,
    er_threshold: f64,
    min_switches: usize,
    combine_mode: &str,
) -> Vec<AggregatedBigCandle> {
    if candles.is_empty() || group_size == 0 {
        return Vec::new();
    }

    // 1. Group 1M candles into Big Candles
    let mut big_candles = Vec::new();
    for chunk in candles.chunks(group_size) {
        if chunk.is_empty() { continue; }
        let (er, body_ratio, switches, is_micro) =
            compute_micro_kama_metrics(chunk, body_ratio_threshold, er_threshold, min_switches);

        let high = chunk.iter().map(|c| c.high).fold(f64::NEG_INFINITY, f64::max);
        let low = chunk.iter().map(|c| c.low).fold(f64::INFINITY, f64::min);

        big_candles.push(AggregatedBigCandle {
            start_epoch: chunk[0].timestamp,
            end_epoch: chunk[chunk.len() - 1].timestamp,
            open: chunk[0].open,
            high,
            low,
            close: chunk[chunk.len() - 1].close,
            candle_count: chunk.len(),
            kama_er: er,
            body_ratio,
            switch_count: switches,
            is_choppy_micro: is_micro,
            macro_chop: None,
            is_choppy_macro: None,
            is_choppy_combined: false,
        });
    }

    // 2. Compute Rolling Dreiss Choppiness Index on Big Candles
    let len = big_candles.len();
    let log_period = (chop_period as f64).ln();

    if len >= chop_period && chop_period > 1 {
        for i in chop_period..=len {
            let window = &big_candles[i - chop_period..i];
            let mut atr_sum = 0.0;
            for j in 1..window.len() {
                let hl = window[j].high - window[j].low;
                let hc = (window[j].high - window[j - 1].close).abs();
                let lc = (window[j].low - window[j - 1].close).abs();
                atr_sum += hl.max(hc).max(lc);
            }
            let win_high = window.iter().map(|c| c.high).fold(f64::NEG_INFINITY, f64::max);
            let win_low = window.iter().map(|c| c.low).fold(f64::INFINITY, f64::min);
            let range = win_high - win_low;

            let chop = if range > 0.0 {
                (100.0 * (atr_sum / range).ln()) / log_period
            } else {
                0.0
            };

            big_candles[i - 1].macro_chop = Some(chop);
            big_candles[i - 1].is_choppy_macro = Some(chop >= chop_threshold);
        }
    }

    // 3. Combine Macro + Micro
    for item in &mut big_candles {
        let is_macro = item.is_choppy_macro;
        let is_micro = item.is_choppy_micro;

        item.is_choppy_combined = match combine_mode {
            "macroOnly" => is_macro.unwrap_or(false),
            "microOnly" => is_micro,
            "and" => {
                // If macro is warming up (None), fallback to micro
                is_macro.map(|m| m && is_micro).unwrap_or(is_micro)
            }
            _ => {
                // "or" (default)
                is_macro.unwrap_or(false) || is_micro
            }
        };
    }

    big_candles
}
```

---

### 2.4 ส่วนที่ 4: การขยายฟิลด์ใน `FullAnalysisResult`

เพิ่มฟิลด์ลงในผลลัพธ์การวิเคราะห์ต่อแท่งเทียน 1 นาที:

```rust
pub struct FullAnalysisResult {
    // ... ฟิลด์เดิม ...

    // -- KAMA & Choppiness Combined Fields --
    pub kama_er: f64,
    pub candle_body_ratio: f64,
    pub color_switch_count: usize,
    pub is_choppy_micro: bool,
    pub macro_chop: Option<f64>,
    pub is_choppy_macro: Option<bool>,
    pub is_choppy_combined: bool,
}
```

---

## 3. ขั้นตอนการลงมือพัฒนา (Implementation Roadmap)

```
┌─────────────────────────────────────────────────────────────┐
│                       ขั้นตอนการพัฒนา                        │
├─────────────────────────────────────────────────────────────┤
│ 1. [Struct & Config Update]                                 │
│    - เพิ่มพารามิเตอร์ KAMA & Choppy Combined ใน             │
│      IndicatorsConfigGroup                                  │
├─────────────────────────────────────────────────────────────┤
│ 2. [Core Algorithm Implementation]                          │
│    - เพิ่ม compute_micro_kama_metrics                       │
│    - เพิ่ม analyze_choppiness_combined                      │
├─────────────────────────────────────────────────────────────┤
│ 3. [Integration in compute_full_analysis_indicators]        │
│    - สร้าง Map จับคู่ผลลัพธ์ Big Candle กลับสู่แท่งย่อย 1M   │
│    - บันทึกค่า kama_er, macro_chop, is_choppy_combined      │
├─────────────────────────────────────────────────────────────┤
│ 4. [Testing & Validation]                                   │
│    - เขียน Unit Test เปรียบเทียบผลลัพธ์กับ                   │
│      choppinessCombined.js                                  │
│    - ทดสอบ Benchmark ความเร็วการประมวลผล                    │
├─────────────────────────────────────────────────────────────┤
│ 5. [Frontend & API Alignment]                               │
│    - ปรับ API Response ให้ส่งฟิลด์ใหม่ไปยังแดชบอร์ด         │
│    - ซิงค์การแสดงผลหน้า analysis_trade_chart.php            │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. แผนการทดสอบและความเข้ากันได้ (Testing & Verification Plan)

1. **Unit Test เปรียบเทียบค่าระหว่าง JS และ Rust**:
   - ใช้ชุดข้อมูลทดสอบ 90 แท่งจาก `Round #8 (2026-09-22 1HZ10V)`
   - ยืนยันว่าค่าที่คำนวณได้ตรงกันทุกตำแหน่ง:
     - `kama_er` $\pm 0.0001$
     - `body_ratio` $\pm 0.0001$
     - `switch_count` เท่ากันทุกประการ
     - `is_choppy_combined` เป็น `true` ที่ช่วงเวลา 20:18 และ 20:48 ตรงกัน
2. **Performance Benchmark**:
   - วัดระยะเวลาในการคำนวณข้อมูล 10,000 แท่งเทียน (เป้าหมาย: $< 5$ มิลลิวินาที)

---

*สร้างไฟล์แผนงานนี้ไว้ที่ `karma_to_rust_plan.md` เพื่อใช้เป็นพิมพ์เขียวในการพัฒนาในรอบงานถัดไป*
