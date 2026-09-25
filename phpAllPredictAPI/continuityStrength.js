/**
 * คำนวณสีแท่ง (green/red/doji)
 */
function getColor(c) {
  const o = typeof c.open === 'number' ? c.open : c.o;
  const cl = typeof c.close === 'number' ? c.close : c.c;
  if (cl > o) return 'green';
  if (cl < o) return 'red';
  return 'doji';
}

/**
 * คำนวณ strength ของ "สีล่าสุด" ใน window ย้อนหลัง N แท่ง
 * ที่ index j
 */
function calcStrengthAt(candles, j, lookback = 5) {
  const start = Math.max(0, j - lookback + 1);
  const win = candles.slice(start, j + 1);
  const colors = win.map(getColor);

  // หา refColor (แท่งล่าสุดที่ไม่ใช่ doji)
  let refColor = null;
  for (let i = colors.length - 1; i >= 0; i--) {
    if (colors[i] !== 'doji') { refColor = colors[i]; break; }
  }
  if (!refColor) {
    return { index: j, color: null, strength: 0, strengthPct: 0, runLength: 0, windowSize: win.length, nonDoji: 0 };
  }

  // weights = [k,...,1]
  const k = win.length;
  const weights = Array.from({ length: k }, (_, i) => k - i);

  let num = 0, den = 0, nonDoji = 0;
  for (let i = 0; i < k; i++) {
    if (colors[i] !== 'doji') {
      den += weights[i];
      nonDoji++;
      if (colors[i] === refColor) num += weights[i];
    }
  }
  const strength = den > 0 ? num / den : 0;

  // runLength
  let runLength = 0;
  for (let i = k - 1; i >= 0; i--) {
    if (colors[i] === refColor) runLength++;
    else break;
  }

  return {
    index: j,
    color: refColor,
    strength: +strength.toFixed(4),
    strengthPct: Math.round(strength * 100),
    runLength,
    windowSize: k,
    nonDoji
  };
}

/**
 * continuityStrength แบบใหม่:
 * คำนวณ strength ทุก index ตั้งแต่ 0..candles.length-1
 */
function continuityStrengthAll(candles, lookback = 5) {
  return candles.map((_, j) => calcStrengthAt(candles, j, lookback));
}

/*
const data = [
  { open: 1.1000, close: 1.1010 }, // green
  { open: 1.1010, close: 1.1005 }, // red
  { open: 1.1005, close: 1.1015 }, // green
  { open: 1.1015, close: 1.1022 }, // green
  { open: 1.1022, close: 1.1030 }  // green
];

console.log(continuityStrengthAll(data, 5));


*/