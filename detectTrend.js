
/**
 * ตรวจจับ trend ของแท่งเทียนปัจจุบัน โดยเทียบ high กับแท่งก่อนหน้า 1-2 แท่ง
 * @param {Array} candles - array ของแท่งเทียน แต่ละตัวมี { high, low, open, close }
 * @param {number} index - index ของแท่งเทียนที่ต้องการตรวจสอบ
 * @returns {string} 'UpTrend' | 'DownTrend' | 'Sideways' | null
 */
function detectTrend(candles, index) {
  if (index < 2) return null; // ข้อมูลไม่พอสำหรับเทียบ 2 แท่งก่อนหน้า

  const current = candles[index];
  const prev1 = candles[index - 1];
  const prev2 = candles[index - 2];

  const higherThanPrev1 = current.high > prev1.high;
  const higherThanPrev2 = current.high > prev2.high;

  const lowerThanPrev1 = current.high < prev1.high;
  const lowerThanPrev2 = current.high < prev2.high;

  // สูงกว่าแท่งก่อนหน้า อย่างน้อย 1 ใน 2 แท่ง -> Up Trend
  if (higherThanPrev1 || higherThanPrev2) {
    return 'UpTrend';
  }

  // ต่ำกว่าแท่งก่อนหน้า ทั้ง 2 แท่ง -> Down Trend
  if (lowerThanPrev1 && lowerThanPrev2) {
    return 'DownTrend';
  }

  return 'Sideways';
}

/**
 * ตรวจจับ trend ของทั้ง array แท่งเทียน แล้วคืนค่าเป็น array ของ trend แต่ละแท่ง
 * @param {Array} candles - array ของแท่งเทียนทั้งหมด
 * @returns {Array} array ของ trend ('UpTrend' | 'DownTrend' | 'Sideways' | null)
 */
function detectTrendSeries(candles) {
  return candles.map((_, i) => detectTrend(candles, i));
}
