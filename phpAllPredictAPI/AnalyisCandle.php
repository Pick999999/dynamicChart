
function analyzeCandle(open, high, low, close) {
    const body = Math.abs(close - open);           // ตัวแท่ง
    const upperWick = high - Math.max(open, close); // ไส้บน
    const lowerWick = Math.min(open, close) - low;  // ไส้ล่าง
    const range = high - low;                      // ความสูงทั้งแท่ง
    const isBullish = close > open;                // สีแท่ง

    let prediction = "Neutral";
    let pattern = "Unknown";

    // ✅ แพทเทิร์นมาตรฐาน
    if (body === 0) {
        pattern = "Doji";
        prediction = "ลังเล รอดูแท่งถัดไป";
    }
    else if (isBullish && lowerWick === 0 && upperWick === 0) {
        pattern = "Bullish Marubozu";
        prediction = "Strong Bullish → มีโอกาสขึ้นต่อ";
    }
    else if (!isBullish && lowerWick === 0 && upperWick === 0) {
        pattern = "Bearish Marubozu";
        prediction = "Strong Bearish → มีโอกาสลงต่อ";
    }
    else if (lowerWick >= body * 2 && upperWick < body) {
        pattern = isBullish ? "Hammer (Bullish)" : "Hammer (Bearish)";
        prediction = "อาจกลับตัวขึ้น";
    }
    else if (upperWick >= body * 2 && lowerWick < body) {
        pattern = isBullish ? "Inverted Hammer (Bullish)" : "Shooting Star";
        prediction = "อาจกลับตัวลง";
    }
    else {
        // fallback logic
        if (isBullish && body > upperWick && lowerWick === 0) {
            prediction = "แรงซื้อเด่น อาจขึ้นต่อ";
        } else if (!isBullish && body > upperWick && lowerWick === 0) {
            prediction = "แรงขายเด่น อาจลงต่อ";
        } else {
            prediction = "ไม่มีสัญญาณชัดเจน (Sideway)";
        }
    }

    return {
        open,
        high,
        low,
        close,
        body,
        upperWick,
        lowerWick,
        range,
        color: isBullish ? "Green" : "Red",
        pattern,
        prediction
    };
} 
/*
เกณฑ์การจำแนก:

A (Bearish Long): body > 60% ของ range และปิดต่ำกว่าเปิด
B (Bullish Long): body > 60% ของ range และปิดสูงกว่าเปิด
C (Long Lower Shadow): หางล่าง > 40% ของ range
D (Long Upper Shadow): หางบน > 40% ของ range
E (Doji/Small Body): body < 30% ของ range
F/G (Small Body): body ปานกลาง แยกตามทิศทาง
H (Both Long Shadows): หางทั้งสองด้าน > 30%

*/

/**
 * กำหนดรหัสแท่งเทียนตามลักษณะ OHLC
 * @param {number} open - ราคาเปิด
 * @param {number} high - ราคาสูงสุด
 * @param {number} low - ราคาต่ำสุด
 * @param {number} close - ราคาปิด
 * @returns {string} รหัสแท่งเทียน (A-H)
 */
function getCandlestickCode(open, high, low, close) {
  // คำนวณค่าต่างๆ
  const body = Math.abs(close - open);
  const range = high - low;
  const upperShadow = high - Math.max(open, close);
  const lowerShadow = Math.min(open, close) - low;
  
  // กำหนดเกณฑ์
  const bodyRatio = body / range;
  const upperShadowRatio = upperShadow / range;
  const lowerShadowRatio = lowerShadow / range;
  
  // ตรวจสอบทิศทาง
  const isBullish = close > open;
  const isBearish = close < open;
  
  // เกณฑ์การจัดกลุ่ม
  const isLongBody = bodyRatio > 0.6;
  const isSmallBody = bodyRatio < 0.3;
  const hasLongUpperShadow = upperShadowRatio > 0.4;
  const hasLongLowerShadow = lowerShadowRatio > 0.4;
  const hasBothLongShadows = upperShadowRatio > 0.3 && lowerShadowRatio > 0.3;
  
  // กำหนดรหัส
  if (hasBothLongShadows) {
    return 'H'; // Long Wicks Both Sides
  }
  
  if (hasLongLowerShadow && bodyRatio < 0.4) {
    return 'C'; // Long Lower Shadow (Hammer-like)
  }
  
  if (hasLongUpperShadow && bodyRatio < 0.4) {
    return 'D'; // Long Upper Shadow (Shooting Star-like)
  }
  
  if (isSmallBody || body / range < 0.1) {
    return 'E'; // Small Body / Doji
  }
  
  if (isLongBody && isBearish) {
    return 'A'; // Bearish Long Body
  }
  
  if (isLongBody && isBullish) {
    return 'B'; // Bullish Long Body
  }
  
  if (isBearish) {
    return 'F'; // Bearish Small Body
  }
  
  if (isBullish) {
    return 'G'; // Bullish Small Body
  }
  
  return 'E'; // Default: Small Body / Doji
}

/**
 * วิเคราะห์แท่งเทียนหลายแท่ง
 * @param {Array} candles - Array of {open, high, low, close}
 * @returns {Array} Array ของ {index, code, description, ...ohlc}
 */
function analyzeCandlesticks(candles) {
  const descriptions = {
    'A': 'Bearish Long Body (แท่งแดงตัวใหญ่)',
    'B': 'Bullish Long Body (แท่งเขียวตัวใหญ่)',
    'C': 'Long Lower Shadow (หางล่างยาว)',
    'D': 'Long Upper Shadow (หางบนยาว)',
    'E': 'Small Body / Doji (ตัวเล็ก)',
    'F': 'Bearish Small Body (แท่งแดงตัวเล็ก)',
    'G': 'Bullish Small Body (แท่งเขียวตัวเล็ก)',
    'H': 'Long Wicks Both Sides (หางยาวทั้งสองด้าน)'
  };
  
  return candles.map((candle, index) => {
    const code = getCandlestickCode(candle.open, candle.high, candle.low, candle.close);
    return {
      index: index + 1,
      code: code,
      description: descriptions[code],
      open: candle.open,
      high: candle.high,
      low: candle.low,
      close: candle.close
    };
  });
}

/**
 * สรุปความถี่ของแต่ละรหัส
 * @param {Array} results - ผลลัพธ์จาก analyzeCandlesticks
 * @returns {Object} สรุปความถี่
 */
function summarizePatterns(results) {
  const summary = {};
  results.forEach(result => {
    if (!summary[result.code]) {
      summary[result.code] = {
        count: 0,
        description: result.description
      };
    }
    summary[result.code].count++;
  });
  return summary;
}

// ตัวอย่างการใช้งาน
const sampleData = [
  { open: 100, high: 105, low: 95, close: 97 },   // A - Bearish Long
  { open: 97, high: 99, low: 90, close: 98 },     // C - Long Lower Shadow
  { open: 98, high: 110, low: 97, close: 108 },   // B - Bullish Long
  { open: 108, high: 110, low: 107, close: 109 }, // G - Bullish Small
  { open: 109, high: 112, low: 108, close: 109 }, // E - Doji
  { open: 109, high: 120, low: 108, close: 110 }, // D - Long Upper Shadow
  { open: 110, high: 115, low: 105, close: 114 }  // H - Long Both Sides
];

console.log("=== วิเคราะห์แท่งเทียนแต่ละแท่ง ===");
const results = analyzeCandlesticks(sampleData);
results.forEach(r => {
  console.log(`แท่งที่ ${r.index}: [${r.code}] ${r.description}`);
  console.log(`  O:${r.open} H:${r.high} L:${r.low} C:${r.close}\n`);
});

console.log("\n=== สรุปความถี่รูปแบบ ===");
const summary = summarizePatterns(results);
Object.entries(summary).forEach(([code, data]) => {
  console.log(`[${code}] ${data.description}: ${data.count} แท่ง`);
});

// Export functions
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    getCandlestickCode,
    analyzeCandlesticks,
    summarizePatterns
  };
}

/*

// 🟢 ตัวอย่างการใช้งาน
let candle1 = analyzeCandle(1.2000, 1.2050, 1.1900, 1.1900); // Bearish Marubozu
let candle2 = analyzeCandle(1.1900, 1.2100, 1.1900, 1.2100); // Bullish Marubozu
let candle3 = analyzeCandle(1.2000, 1.2100, 1.1950, 1.2050); // Normal green
let candle4 = analyzeCandle(1.2000, 1.2050, 1.1900, 1.2010); // Hammer
let candle5 = analyzeCandle(1.2000, 1.2200, 1.1980, 1.2010); // Shooting Star
let candle6 = analyzeCandle(1.2000, 1.2100, 1.1900, 1.2000); // Doji

console.log(candle1);
console.log(candle2);
console.log(candle3);
console.log(candle4);
console.log(candle5);
console.log(candle6);
*/