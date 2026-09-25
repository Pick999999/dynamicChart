/**
 * ตรวจจับ trend + แยกประเภท Rejected และ Sideways เป็น case ย่อย พร้อมคำอธิบายภาษาไทย
 *
 * @param {Array} candles - array ของแท่งเทียน { high, low, open, close }
 * @param {number} index
 * @param {Object} options
 * @param {number} options.closePositionThreshold - เกณฑ์ยืนยัน trend ปกติ (default 0.5)
 * @param {number} options.strongRejectionThreshold - เกณฑ์ตัดสิน "ปฏิเสธรุนแรง" (default 0.3)
 * @param {number} options.dojiBodyThreshold - เกณฑ์ body เล็ก เทียบกับ range (default 0.15 = 15%)
 * @param {number} options.flatTolerance - เกณฑ์ high ใกล้เคียงกัน (default 0.05 = 5%)
 * @returns {Object} { trend, subType, description, extremeTrend, closePosition, bodyRatio }
 */
function detectTrend(candles, index, options = {}) {
  const {
    closePositionThreshold = 0.5,
    strongRejectionThreshold = 0.3,
    dojiBodyThreshold = 0.15,
    flatTolerance = 0.05,
  } = options;

  if (index < 2) return null;

  const current = candles[index];
  const prev1 = candles[index - 1];
  const prev2 = candles[index - 2];

  const higherThanPrev1 = current.high > prev1.high;
  const higherThanPrev2 = current.high > prev2.high;
  const lowerThanPrev1 = current.high < prev1.high;
  const lowerThanPrev2 = current.high < prev2.high;

  const closeHigherThanPrev1 = current.close > prev1.close;
  const closeLowerThanPrev1 = current.close < prev1.close;

  const range = current.high - current.low;
  const closePosition = range > 0 ? (current.close - current.low) / range : 0.5;
  const bodyRatio = range > 0 ? Math.abs(current.close - current.open) / range : 0;

  // แท่งปัจจุบันอยู่ในกรอบของแท่งก่อนหน้าทั้งหมด (หดตัว/พักตัว)
  const isInsideBar = current.high <= prev1.high && current.low >= prev1.low;

  // high ใกล้เคียงกับ high ก่อนหน้ามาก (ชนแนวต้านเดิมซ้ำ) แต่ไม่ได้สูงกว่าจริง
  const highDiffRatio = prev1.high !== 0 ? Math.abs(current.high - prev1.high) / prev1.high : 0;
  const isFlatHigh = highDiffRatio <= flatTolerance && !higherThanPrev1;

  // สูงกว่าก่อนหน้าแค่ 1 ใน 2 แท่ง = สัญญาณขัดแย้งกันเอง
  const mixedSignal = higherThanPrev1 !== higherThanPrev2;

  let extremeTrend = 'Sideways';
  if (higherThanPrev1 || higherThanPrev2) extremeTrend = 'UpTrend';
  else if (lowerThanPrev1 && lowerThanPrev2) extremeTrend = 'DownTrend';

  let trend = 'Sideways';
  let subType = null;
  let description = '';

  if (extremeTrend === 'UpTrend') {
    if (closeHigherThanPrev1 && closePosition >= closePositionThreshold) {
      trend = 'UpTrend';
      subType = 'CONFIRMED_UP';
      description = 'ทำจุดสูงใหม่ ปิดสูงกว่าแท่งก่อนหน้า และปิดในโซนบนของแท่งตัวเอง ยืนยันแรงซื้อยังควบคุมตลาด';
    } else if (!closeHigherThanPrev1 && closePosition < strongRejectionThreshold) {
      trend = 'Rejected';
      subType = 'STRONG_BULL_TRAP';
      description = 'ทำจุดสูงใหม่ แต่ถูกแรงขายกดลงมาปิดใกล้จุดต่ำสุดของแท่ง และปิดต่ำกว่าแท่งก่อนหน้าด้วย สัญญาณกลับตัวลงที่ชัดเจน (Bull Trap)';
    } else if (closeHigherThanPrev1 && closePosition < closePositionThreshold) {
      trend = 'Rejected';
      subType = 'WEAK_UPPER_WICK';
      description = 'ทำจุดสูงใหม่ และยังปิดสูงกว่าแท่งก่อนหน้า แต่มีไส้บนยาวแสดงว่าเจอแรงขายกดในช่วงท้ายแท่ง ควรระวังแรงซื้อเริ่มอ่อนกำลัง';
    } else if (mixedSignal && bodyRatio < dojiBodyThreshold) {
      // สัญญาณขัดแย้งกัน + body เล็ก -> จริง ๆ คือ sideways ไม่ใช่ rejected
      trend = 'Sideways';
      subType = 'MIXED_SIGNAL';
      description = 'High สูงกว่าแท่งก่อนหน้าแค่ 1 ใน 2 แท่ง (สัญญาณขัดแย้งกันเอง) ประกอบกับ body เล็ก ตลาดกำลังสับสน ยังไม่มีทิศทางชัดเจนพอจะสรุป';
    } else {
      trend = 'Rejected';
      subType = 'FAILED_FOLLOWTHROUGH_UP';
      description = 'ทำจุดสูงใหม่ แต่ปิดต่ำกว่าแท่งก่อนหน้า แม้ตำแหน่งปิดในแท่งตัวเองจะยังไม่แย่มาก แสดงว่าโมเมนตัมขาขึ้นเริ่มแผ่ว ควรจับตาแท่งถัดไป';
    }
  } else if (extremeTrend === 'DownTrend') {
    if (closeLowerThanPrev1 && closePosition <= (1 - closePositionThreshold)) {
      trend = 'DownTrend';
      subType = 'CONFIRMED_DOWN';
      description = 'ทำจุดต่ำใหม่ ปิดต่ำกว่าแท่งก่อนหน้า และปิดในโซนล่างของแท่งตัวเอง ยืนยันแรงขายยังควบคุมตลาด';
    } else if (!closeLowerThanPrev1 && closePosition > (1 - strongRejectionThreshold)) {
      trend = 'Rejected';
      subType = 'STRONG_BEAR_TRAP';
      description = 'ทำจุดต่ำใหม่ แต่ถูกแรงซื้อดันขึ้นมาปิดใกล้จุดสูงสุดของแท่ง และปิดสูงกว่าแท่งก่อนหน้าด้วย สัญญาณกลับตัวขึ้นที่ชัดเจน (Bear Trap)';
    } else if (closeLowerThanPrev1 && closePosition > (1 - closePositionThreshold)) {
      trend = 'Rejected';
      subType = 'WEAK_LOWER_WICK';
      description = 'ทำจุดต่ำใหม่ และยังปิดต่ำกว่าแท่งก่อนหน้า แต่มีไส้ล่างยาวแสดงว่าเจอแรงซื้อดันกลับในช่วงท้ายแท่ง ควรระวังแรงขายเริ่มอ่อนกำลัง';
    } else {
      trend = 'Rejected';
      subType = 'FAILED_FOLLOWTHROUGH_DOWN';
      description = 'ทำจุดต่ำใหม่ แต่ปิดสูงกว่าแท่งก่อนหน้า แม้ตำแหน่งปิดในแท่งตัวเองจะยังไม่แย่มาก แสดงว่าโมเมนตัมขาลงเริ่มแผ่ว ควรจับตาแท่งถัดไป';
    }
  } else {
    // ===== extremeTrend === 'Sideways' -> แยก case ย่อย =====
    if (isInsideBar) {
      subType = 'INSIDE_BAR';
      description = 'แท่งปัจจุบันมี high/low อยู่ภายในกรอบของแท่งก่อนหน้าทั้งหมด (Inside Bar) ตลาดกำลังหดตัว/พักตัว รอการ breakout เพื่อยืนยันทิศทางถัดไป';
    } else if (bodyRatio < dojiBodyThreshold) {
      subType = 'DOJI_INDECISION';
      description = 'Body ของแท่งเล็กมากเมื่อเทียบกับ range ทั้งหมด (เปิด-ปิดใกล้กัน) แรงซื้อแรงขายสูสีกัน ไม่มีฝ่ายใดชนะชัดเจนในแท่งนี้';
    } else if (isFlatHigh) {
      subType = 'FLAT_RESISTANCE_TEST';
      description = 'High ของแท่งใกล้เคียงกับ high ของแท่งก่อนหน้ามาก แต่ไปต่อไม่ได้ เหมือนราคาชนแนวต้านเดิมซ้ำ (ทดสอบแนวต้าน) ยังไม่มีแรงมากพอจะทะลุ';
    } else {
      subType = 'NO_CLEAR_STRUCTURE';
      description = 'High ของแท่งไม่ได้ทำจุดสูงใหม่หรือจุดต่ำใหม่ชัดเจนเมื่อเทียบกับ 2 แท่งก่อนหน้า ตลาดยังไม่มีโครงสร้างที่ชัดเจนพอจะสรุปทิศทาง';
    }
  }

  return {
    trend,          // 'UpTrend' | 'DownTrend' | 'Rejected' | 'Sideways'
    subType,         // รหัส case ย่อย
    description,     // คำอธิบายภาษาไทย
    extremeTrend,     // trend แบบเดิม (ดู high อย่างเดียว)
    closePosition: Number(closePosition.toFixed(2)),
    bodyRatio: Number(bodyRatio.toFixed(2)),
  };
}

function detectTrendSeries(candles, options = {}) {
  return candles.map((_, i) => detectTrend(candles, i, options));
}