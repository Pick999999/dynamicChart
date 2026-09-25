/**
 * choppinessCombined.js
 * ------------------------------------------------------------------
 * รวม 2 มุมมองเข้าด้วยกัน:
 *
 *  1) MACRO  - Choppiness Index (Dreiss) คำนวณแบบ rolling window
 *              บนลำดับแท่งเทียน "ใหญ่" ที่ aggregate มาแล้ว
 *              -> ตอบว่า "ช่วง N แท่งที่ผ่านมา ตลาดแกว่งหรือมีเทรนด์"
 *
 *  2) MICRO  - bodyRatio / Efficiency Ratio / switchCount
 *              คำนวณจากแท่งย่อยที่ประกอบกันเป็นแท่งใหญ่ 1 แท่ง
 *              -> ตอบว่า "แท่งใหญ่แท่งนี้แท่งเดียว เกิดจากแรงหักล้าง
 *                 กันของแท่งย่อยที่สลับสีถี่ๆ หรือเปล่า"
 *
 * Input: แท่งเทียนย่อย (เช่น TF 1 นาทีจาก Deriv) รูปแบบ
 *   { open, high, low, close, epoch }   // epoch = unix time วินาที, เรียง ascending
 * ------------------------------------------------------------------
 */

/**
 * @param {Array<{open:number, high:number, low:number, close:number, epoch:number}>} smallCandles
 * @param {Object} [options]
 * @param {number} [options.groupSize=15]            จำนวนแท่งย่อยต่อ 1 แท่งใหญ่
 * @param {number} [options.groupBySeconds]           group ตามช่วงเวลา (วินาที) แทน groupSize
 * @param {boolean} [options.includeIncomplete=false] รวมกลุ่มสุดท้ายที่แท่งไม่ครบด้วยหรือไม่
 * @param {number} [options.chopPeriod=14]             จำนวนแท่งใหญ่ที่ใช้คำนวณ Choppiness Index
 * @param {number} [options.chopThreshold=61.8]        ค่า CHOP ที่ถือว่า choppy (มาตรฐาน Dreiss)
 * @param {number} [options.bodyRatioThreshold=0.3]    เกณฑ์ body/range ว่า "บาง"
 * @param {number} [options.erThreshold=0.35]          เกณฑ์ Efficiency Ratio ว่า choppy
 * @param {number} [options.minSwitches=3]             จำนวนสลับสีขั้นต่ำในแท่งย่อย
 * @param {'and'|'or'|'macroOnly'|'microOnly'} [options.combineMode='and']
 *        วิธีรวมผล macro + micro เป็น isChoppy สุดท้าย
 * @returns {Array<Object>} แท่งใหญ่ที่ aggregate แล้ว พร้อม metric ทั้งสองมุมมองและ isChoppy รวม
 */
function analyzeChoppiness(smallCandles, options = {}) {
  const {
    groupSize = 15,
    groupBySeconds = null,
    includeIncomplete = false,
    chopPeriod = 14,
    chopThreshold = 61.8,
    bodyRatioThreshold = 0.3,
    erThreshold = 0.35,
    minSwitches = 3,
    combineMode = 'or',
  } = options;

  if (!Array.isArray(smallCandles) || smallCandles.length === 0) return [];

  // 1) แบ่งกลุ่มแท่งย่อย -> แท่งใหญ่ (พร้อม micro metrics)
  const groups = groupBySeconds
    ? groupByTimeWindow(smallCandles, groupBySeconds)
    : groupByCount(smallCandles, groupSize);

  const bigCandles = [];
  for (const group of groups) {
    if (!includeIncomplete && !groupBySeconds && group.length < groupSize) continue;
    if (group.length < 1) continue;
    bigCandles.push(
      buildAggregatedCandle(group, { bodyRatioThreshold, erThreshold, minSwitches })
    );
  }

  // 2) คำนวณ Choppiness Index (macro) บนลำดับแท่งใหญ่
  const chopValues = computeChoppinessIndex(bigCandles, chopPeriod);

  // 3) รวมผล macro + micro
  return bigCandles.map((candle, idx) => {
    const chop = chopValues[idx]; // number | null
    const isChoppyMacro = chop === null ? null : chop >= chopThreshold;
    const isChoppyMicro = candle.isChoppyMicro;

    let isChoppy;
    if (combineMode === 'macroOnly') isChoppy = isChoppyMacro;
    else if (combineMode === 'microOnly') isChoppy = isChoppyMicro;
    else if (combineMode === 'or') isChoppy = Boolean(isChoppyMacro) || isChoppyMicro;
    else isChoppy = isChoppyMacro === null ? isChoppyMicro : (isChoppyMacro && isChoppyMicro); // 'and' with fallback to micro if macro not ready

    return {
      epoch: candle.epoch,
      open: candle.open,
      high: candle.high,
      low: candle.low,
      close: candle.close,
      color: candle.color,
      candleCount: candle.candleCount,
      // micro
      bodyRatio: candle.bodyRatio,
      efficiencyRatio: candle.efficiencyRatio,
      switchCount: candle.switchCount,
      isChoppyMicro,
      // macro
      chop: chop === null ? null : Number(chop.toFixed(2)),
      isChoppyMacro,
      // รวม
      isChoppy,
    };
  });
}

// ------------------------------------------------------------------
// Helpers: การจัดกลุ่มแท่งย่อย
// ------------------------------------------------------------------

function groupByCount(candles, groupSize) {
  const groups = [];
  for (let i = 0; i < candles.length; i += groupSize) {
    groups.push(candles.slice(i, i + groupSize));
  }
  return groups;
}

function groupByTimeWindow(candles, windowSeconds) {
  const groups = [];
  let current = [];
  let windowStart = null;

  for (const c of candles) {
    if (windowStart === null) {
      windowStart = Math.floor(c.epoch / windowSeconds) * windowSeconds;
    }
    if (c.epoch >= windowStart + windowSeconds) {
      if (current.length) groups.push(current);
      current = [];
      windowStart = Math.floor(c.epoch / windowSeconds) * windowSeconds;
    }
    current.push(c);
  }
  if (current.length) groups.push(current);
  return groups;
}

// ------------------------------------------------------------------
// MICRO: aggregate OHLC + bodyRatio / Efficiency Ratio / switchCount
// ------------------------------------------------------------------

function buildAggregatedCandle(group, { bodyRatioThreshold, erThreshold, minSwitches }) {
  const open = group[0].open;
  const close = group[group.length - 1].close;
  const high = Math.max(...group.map((c) => c.high));
  const low = Math.min(...group.map((c) => c.low));

  const range = high - low;
  const body = Math.abs(close - open);
  const bodyRatio = range === 0 ? 0 : body / range;

  // Kaufman Efficiency Ratio: net movement / total path movement (บน close ของแท่งย่อย)
  const sequence = [open, ...group.map((c) => c.close)];
  let path = 0;
  for (let j = 1; j < sequence.length; j++) {
    path += Math.abs(sequence[j] - sequence[j - 1]);
  }
  const efficiencyRatio = path === 0 ? 0 : body / path;

  // นับจำนวนครั้งที่สีแท่งย่อยสลับกัน (ข้าม doji)
  let switchCount = 0;
  let prevColor = null;
  for (const c of group) {
    const color = c.close > c.open ? 'up' : c.close < c.open ? 'down' : null;
    if (color && prevColor && color !== prevColor) switchCount++;
    if (color) prevColor = color;
  }

  const isChoppyMicro =
    bodyRatio <= bodyRatioThreshold &&
    efficiencyRatio <= erThreshold &&
    switchCount >= minSwitches;

  return {
    epoch: group[0].epoch,
    open,
    high,
    low,
    close,
    color: close > open ? 'bullish' : close < open ? 'bearish' : 'doji',
    bodyRatio: Number(bodyRatio.toFixed(4)),
    efficiencyRatio: Number(efficiencyRatio.toFixed(4)),
    switchCount,
    candleCount: group.length,
    isChoppyMicro,
  };
}

// ------------------------------------------------------------------
// MACRO: Choppiness Index (Dreiss) - rolling window บนแท่งใหญ่
// ตรรกะ/โครงสร้าง loop ตรงกับเวอร์ชัน Rust (ChoppinessIndex) ทุกจุด
// เพื่อให้พอร์ตกลับไป Rust ได้ตรงๆ ภายหลัง
// ------------------------------------------------------------------

function computeChoppinessIndex(bigCandles, period) {
  const len = bigCandles.length;
  const results = new Array(len).fill(null);
  if (len < period) return results;

  const logPeriod = Math.log(period);

  for (let i = period; i <= len; i++) {
    const window = bigCandles.slice(i - period, i);

    let atrSum = 0;
    for (let j = 1; j < window.length; j++) {
      const highLow = window[j].high - window[j].low;
      const highClose = Math.abs(window[j].high - window[j - 1].close);
      const lowClose = Math.abs(window[j].low - window[j - 1].close);
      atrSum += Math.max(highLow, highClose, lowClose);
    }

    const high = Math.max(...window.map((c) => c.high));
    const low = Math.min(...window.map((c) => c.low));
    const range = high - low;

    const chop = range > 0 ? (100 * Math.log(atrSum / range)) / logPeriod : 0;
    results[i - 1] = chop; // align ค่ากับแท่งสุดท้ายของ window
  }

  return results;
}

if (typeof module !== 'undefined' && module.exports) {
  module.exports = { analyzeChoppiness };
}
if (typeof window !== 'undefined') {
  window.analyzeChoppiness = analyzeChoppiness;
}

/* ------------------------------------------------------------------
 * ตัวอย่างการใช้งาน:
 *
 *   const { analyzeChoppiness } = require('./choppinessCombined');
 *
 *   const result = analyzeChoppiness(candles1M, {
 *     groupSize: 15,        // 1M -> 15M
 *     chopPeriod: 14,       // CHOP มาตรฐาน 14 แท่งใหญ่
 *     chopThreshold: 61.8,
 *     bodyRatioThreshold: 0.3,
 *     erThreshold: 0.35,
 *     minSwitches: 3,
 *     combineMode: 'and',   // ต้อง choppy ทั้ง macro และ micro พร้อมกัน
 *   });
 *
 *   const choppyCandles = result.filter(c => c.isChoppy);
 *   console.log(choppyCandles);
 * ------------------------------------------------------------------ */
