/**
 * ========================================
 * FAIR VALUE GAP (FVG) ANALYZER
 * + Candle Prediction
 * ========================================
 */

/**
 * แปลง timestamp เป็นเวลาไทย (UTC+7)
 */
function toThaiTime(timestamp) {
  const date = new Date(timestamp * 1000);
  const thaiDate = new Date(date.getTime() + (7 * 60 * 60 * 1000));
  const hours = String(thaiDate.getUTCHours()).padStart(2, '0');
  const minutes = String(thaiDate.getUTCMinutes()).padStart(2, '0');
  const seconds = String(thaiDate.getUTCSeconds()).padStart(2, '0');
  return `${hours}:${minutes}:${seconds}`;
}

/**
 * คำนวณค่า pip
 */
function calculatePip(value, decimals) {
  return Math.round(value * Math.pow(10, decimals));
}

/**
 * วิเคราะห์แท่งเทียนเฉพาะ
 * @param {Array} candleData - ข้อมูลแท่งเทียนทั้งหมด
 * @param {number} candleIndex - index ของแท่งที่ต้องการวิเคราะห์
 * @param {number} pipDecimal - จำนวนทศนิยม
 * @returns {Object} ผลการวิเคราะห์แท่งเทียน
 */
function analyzeSpecificCandle(candleData, candleIndex, pipDecimal = 5) {
  if (!candleData || candleIndex < 0 || candleIndex >= candleData.length) {
    return null;
  }

  const candle = candleData[candleIndex];
  const open = parseFloat(candle.open);
  const close = parseFloat(candle.close);
  const high = parseFloat(candle.high);
  const low = parseFloat(candle.low);

  // ข้อมูลพื้นฐาน
  const candleColor = close > open ? 'Green' : close < open ? 'Red' : 'Doji';
  const bodySize = Math.abs(close - open);
  const candleSize = high - low;
  const bodyPercent = candleSize > 0 ? (bodySize / candleSize) * 100 : 0;

  // ตรวจสอบว่าแท่งนี้เป็นส่วนหนึ่งของ FVG หรือไม่
  let fvgRole = 'None';
  let fvgDetails = null;

  // ตรวจสอบว่าเป็นแท่งที่ 1 ของ FVG
  if (candleIndex <= candleData.length - 3) {
    const c1 = candle;
    const c2 = candleData[candleIndex + 1];
    const c3 = candleData[candleIndex + 2];

    const c1_high = parseFloat(c1.high);
    const c1_low = parseFloat(c1.low);
    const c3_high = parseFloat(c3.high);
    const c3_low = parseFloat(c3.low);

    if (c1_high < c3_low) {
      fvgRole = 'Candle 1 of Bullish FVG';
      fvgDetails = {
        type: 'Bullish',
        role: 'แท่งที่ 1 (ก่อนกระโดด)',
        gapTop: c3_low,
        gapBottom: c1_high,
        gapSize: c3_low - c1_high
      };
    } else if (c1_low > c3_high) {
      fvgRole = 'Candle 1 of Bearish FVG';
      fvgDetails = {
        type: 'Bearish',
        role: 'แท่งที่ 1 (ก่อนกระโดด)',
        gapTop: c1_low,
        gapBottom: c3_high,
        gapSize: c1_low - c3_high
      };
    }
  }

  // ตรวจสอบว่าเป็นแท่งที่ 2 ของ FVG (แท่งกระโดด)
  if (candleIndex >= 1 && candleIndex <= candleData.length - 2) {
    const c1 = candleData[candleIndex - 1];
    const c2 = candle;
    const c3 = candleData[candleIndex + 1];

    const c1_high = parseFloat(c1.high);
    const c1_low = parseFloat(c1.low);
    const c3_high = parseFloat(c3.high);
    const c3_low = parseFloat(c3.low);

    if (c1_high < c3_low) {
      fvgRole = 'Candle 2 of Bullish FVG';
      fvgDetails = {
        type: 'Bullish',
        role: 'แท่งที่ 2 (แท่งกระโดด - Impulsive)',
        gapTop: c3_low,
        gapBottom: c1_high,
        gapSize: c3_low - c1_high
      };
    } else if (c1_low > c3_high) {
      fvgRole = 'Candle 2 of Bearish FVG';
      fvgDetails = {
        type: 'Bearish',
        role: 'แท่งที่ 2 (แท่งกระโดด - Impulsive)',
        gapTop: c1_low,
        gapBottom: c3_high,
        gapSize: c1_low - c3_high
      };
    }
  }

  // ตรวจสอบว่าเป็นแท่งที่ 3 ของ FVG
  if (candleIndex >= 2) {
    const c1 = candleData[candleIndex - 2];
    const c2 = candleData[candleIndex - 1];
    const c3 = candle;

    const c1_high = parseFloat(c1.high);
    const c1_low = parseFloat(c1.low);
    const c3_high = parseFloat(c3.high);
    const c3_low = parseFloat(c3.low);

    if (c1_high < c3_low) {
      fvgRole = 'Candle 3 of Bullish FVG';
      fvgDetails = {
        type: 'Bullish',
        role: 'แท่งที่ 3 (หลังกระโดด)',
        gapTop: c3_low,
        gapBottom: c1_high,
        gapSize: c3_low - c1_high
      };
    } else if (c1_low > c3_high) {
      fvgRole = 'Candle 3 of Bearish FVG';
      fvgDetails = {
        type: 'Bearish',
        role: 'แท่งที่ 3 (หลังกระโดด)',
        gapTop: c1_low,
        gapBottom: c3_high,
        gapSize: c1_low - c3_high
      };
    }
  }

  // ทำนายแท่งถัดไป
  let nextCandlePrediction = predictNextCandle(candleData, candleIndex, fvgDetails);

  return {
    index: candleIndex,
    time: parseInt(candle.epoch),
    thaiTime: toThaiTime(parseInt(candle.epoch)),
    open: open,
    high: high,
    low: low,
    close: close,
    color: candleColor,
    bodySize: parseFloat(bodySize.toFixed(pipDecimal + 2)),
    bodySizePip: calculatePip(bodySize, pipDecimal),
    candleSize: parseFloat(candleSize.toFixed(pipDecimal + 2)),
    candleSizePip: calculatePip(candleSize, pipDecimal),
    bodyPercent: parseFloat(bodyPercent.toFixed(2)),
    isStrongCandle: bodyPercent > 70,
    fvgRole: fvgRole,
    fvgDetails: fvgDetails,
    nextCandlePrediction: nextCandlePrediction
  };
}

/**
 * ทำนายแท่งเทียนถัดไป
 */
function predictNextCandle(candleData, currentIndex, fvgDetails) {
  if (currentIndex >= candleData.length - 1) {
    // แท่งสุดท้าย - ทำนายแท่งใหม่
    const currentCandle = candleData[currentIndex];
    const currentClose = parseFloat(currentCandle.close);
    const currentOpen = parseFloat(currentCandle.open);
    const prevCandle = currentIndex > 0 ? candleData[currentIndex - 1] : null;

    let prediction = {
      expectedColor: 'Unknown',
      confidence: 50,
      expectedDirection: 'Sideways',
      reasons: [],
      priceTargets: {
        high: null,
        low: null
      }
    };

    // วิเคราะห์จาก FVG
    if (fvgDetails) {
      if (fvgDetails.type === 'Bullish') {
        if (fvgDetails.role.includes('แท่งที่ 2') || fvgDetails.role.includes('แท่งที่ 3')) {
          prediction.expectedColor = 'Green';
          prediction.expectedDirection = 'Bullish (ขาขึ้นต่อ)';
          prediction.confidence = 75;
          prediction.reasons.push(`อยู่ใน Bullish FVG - คาดว่าจะขาขึ้นต่อ`);
          prediction.priceTargets.high = currentClose + (fvgDetails.gapSize * 1.5);
          prediction.priceTargets.low = fvgDetails.gapTop;
        } else if (fvgDetails.role.includes('แท่งที่ 1')) {
          prediction.expectedColor = 'Green';
          prediction.expectedDirection = 'Bullish (เริ่มกระโดด)';
          prediction.confidence = 70;
          prediction.reasons.push(`แท่งที่ 1 ของ Bullish FVG - แท่งถัดไปคาดว่าจะกระโดดขึ้น`);
        }
      } else if (fvgDetails.type === 'Bearish') {
        if (fvgDetails.role.includes('แท่งที่ 2') || fvgDetails.role.includes('แท่งที่ 3')) {
          prediction.expectedColor = 'Red';
          prediction.expectedDirection = 'Bearish (ขาลงต่อ)';
          prediction.confidence = 75;
          prediction.reasons.push(`อยู่ใน Bearish FVG - คาดว่าจะขาลงต่อ`);
          prediction.priceTargets.low = currentClose - (fvgDetails.gapSize * 1.5);
          prediction.priceTargets.high = fvgDetails.gapBottom;
        } else if (fvgDetails.role.includes('แท่งที่ 1')) {
          prediction.expectedColor = 'Red';
          prediction.expectedDirection = 'Bearish (เริ่มกระโดด)';
          prediction.confidence = 70;
          prediction.reasons.push(`แท่งที่ 1 ของ Bearish FVG - แท่งถัดไปคาดว่าจะกระโดดลง`);
        }
      }
    } else {
      // ไม่มี FVG - วิเคราะห์จากเทรนด์
      if (prevCandle) {
        const prevClose = parseFloat(prevCandle.close);
        const prevOpen = parseFloat(prevCandle.open);

        // เช็คแนวโน้ม 3 แท่งล่าสุด
        let bullishCount = 0;
        let bearishCount = 0;

        for (let i = Math.max(0, currentIndex - 2); i <= currentIndex; i++) {
          const c = candleData[i];
          if (parseFloat(c.close) > parseFloat(c.open)) bullishCount++;
          else if (parseFloat(c.close) < parseFloat(c.open)) bearishCount++;
        }

        if (bullishCount > bearishCount) {
          prediction.expectedColor = 'Green';
          prediction.expectedDirection = 'Bullish';
          prediction.confidence = 60;
          prediction.reasons.push(`แนวโน้ม ${bullishCount} แท่งเป็นขาขึ้น`);
        } else if (bearishCount > bullishCount) {
          prediction.expectedColor = 'Red';
          prediction.expectedDirection = 'Bearish';
          prediction.confidence = 60;
          prediction.reasons.push(`แนวโน้ม ${bearishCount} แท่งเป็นขาลง`);
        } else {
          prediction.expectedColor = 'Doji/Sideways';
          prediction.expectedDirection = 'Sideways';
          prediction.confidence = 50;
          prediction.reasons.push('ไม่มีแนวโน้มชัดเจน');
        }
      }
    }

    return prediction;
  } else {
    // มีแท่งถัดไปอยู่แล้ว - เช็คว่าทำนายถูกหรือไม่
    const actualNext = candleData[currentIndex + 1];
    const actualColor = parseFloat(actualNext.close) > parseFloat(actualNext.open) ? 'Green' :
                       parseFloat(actualNext.close) < parseFloat(actualNext.open) ? 'Red' : 'Doji';

    return {
      actualColor: actualColor,
      actualOpen: parseFloat(actualNext.open),
      actualClose: parseFloat(actualNext.close),
      actualHigh: parseFloat(actualNext.high),
      actualLow: parseFloat(actualNext.low)
    };
  }
}

/**
 * หา FVG และวิเคราะห์
 */
function analyzeFVG(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 3) {
    return {
      fvgs: [],
      summary: {
        total: 0,
        bullish: 0,
        bearish: 0,
        active: 0,
        filled: 0
      },
      prediction: {
        trend: 'Unknown',
        confidence: 0,
        action: 'ไม่มีข้อมูล'
      }
    };
  }

  const fvgs = [];
  const currentPrice = parseFloat(candleData[candleData.length - 1].close);

  // หา FVG ทั้งหมด
  for (let i = 0; i < candleData.length - 2; i++) {
    const c1 = candleData[i];
    const c2 = candleData[i + 1];
    const c3 = candleData[i + 2];

    const c1_high = parseFloat(c1.high);
    const c1_low = parseFloat(c1.low);
    const c3_high = parseFloat(c3.high);
    const c3_low = parseFloat(c3.low);

    let fvgType = null;
    let gapTop = 0;
    let gapBottom = 0;

    // Bullish FVG
    if (c1_high < c3_low) {
      fvgType = 'Bullish';
      gapBottom = c1_high;
      gapTop = c3_low;
    }
    // Bearish FVG
    else if (c1_low > c3_high) {
      fvgType = 'Bearish';
      gapTop = c1_low;
      gapBottom = c3_high;
    }

    if (fvgType) {
      const gapSize = gapTop - gapBottom;
      const gapMid = (gapTop + gapBottom) / 2;

      // ตรวจสอบว่าถูกเติมแล้วหรือยัง
      let isFilled = false;
      for (let j = i + 3; j < candleData.length; j++) {
        const testHigh = parseFloat(candleData[j].high);
        const testLow = parseFloat(candleData[j].low);

        if (fvgType === 'Bullish') {
          if (testLow <= gapMid) {
            isFilled = true;
            break;
          }
        } else {
          if (testHigh >= gapMid) {
            isFilled = true;
            break;
          }
        }
      }

      const distanceFromPrice = Math.abs(currentPrice - gapMid);
      const distancePip = calculatePip(distanceFromPrice, pipDecimal);

      let prediction = '';
      let confidence = 0;

      if (fvgType === 'Bullish' && !isFilled) {
        if (currentPrice > gapTop) {
          prediction = 'รอราคากลับมาทดสอบโซน FVG (Pullback)';
          confidence = 70;
        } else if (currentPrice >= gapBottom && currentPrice <= gapTop) {
          prediction = 'ซื้อทันที - ราคาอยู่ในโซน Bullish FVG';
          confidence = 85;
        } else {
          prediction = 'รอราคาขึ้นเข้าโซน FVG';
          confidence = 60;
        }
      } else if (fvgType === 'Bearish' && !isFilled) {
        if (currentPrice < gapBottom) {
          prediction = 'รอราคากลับมาทดสอบโซน FVG (Pullback)';
          confidence = 70;
        } else if (currentPrice >= gapBottom && currentPrice <= gapTop) {
          prediction = 'ขายทันที - ราคาอยู่ในโซน Bearish FVG';
          confidence = 85;
        } else {
          prediction = 'รอราคาลงเข้าโซน FVG';
          confidence = 60;
        }
      }

      fvgs.push({
        index: i,
        type: fvgType,
        candle1Index: i,
        candle2Index: i + 1,
        candle3Index: i + 2,
        top: parseFloat(gapTop.toFixed(pipDecimal + 2)),
        bottom: parseFloat(gapBottom.toFixed(pipDecimal + 2)),
        size: parseFloat(gapSize.toFixed(pipDecimal + 2)),
        sizePip: calculatePip(gapSize, pipDecimal),
        midPoint: parseFloat(gapMid.toFixed(pipDecimal + 2)),
        time: parseInt(c2.epoch),
        thaiTime: toThaiTime(parseInt(c2.epoch)),
        status: isFilled ? 'Filled' : 'Active',
        distanceFromCurrentPrice: parseFloat(distanceFromPrice.toFixed(pipDecimal + 2)),
        distancePip: distancePip,
        prediction: prediction,
        confidence: confidence
      });
    }
  }

  const activeFVGs = fvgs.filter(f => f.status === 'Active');
  const bullishFVGs = activeFVGs.filter(f => f.type === 'Bullish');
  const bearishFVGs = activeFVGs.filter(f => f.type === 'Bearish');

  let nearestFVG = null;
  if (activeFVGs.length > 0) {
    nearestFVG = activeFVGs.reduce((nearest, current) => {
      return current.distancePip < nearest.distancePip ? current : nearest;
    });
  }

  let overallTrend = 'Neutral';
  let overallConfidence = 50;
  let overallAction = 'รอสัญญาณ';

  if (bullishFVGs.length > bearishFVGs.length && nearestFVG && nearestFVG.type === 'Bullish') {
    overallTrend = 'Bullish';
    overallConfidence = Math.min(85, 50 + (bullishFVGs.length * 10));
    overallAction = nearestFVG.prediction;
  } else if (bearishFVGs.length > bullishFVGs.length && nearestFVG && nearestFVG.type === 'Bearish') {
    overallTrend = 'Bearish';
    overallConfidence = Math.min(85, 50 + (bearishFVGs.length * 10));
    overallAction = nearestFVG.prediction;
  }

  return {
    currentPrice: currentPrice,
    fvgs: fvgs,
    activeFVGs: activeFVGs,
    nearestFVG: nearestFVG,
    summary: {
      total: fvgs.length,
      bullish: bullishFVGs.length,
      bearish: bearishFVGs.length,
      active: activeFVGs.length,
      filled: fvgs.filter(f => f.status === 'Filled').length
    },
    prediction: {
      trend: overallTrend,
      confidence: overallConfidence,
      action: overallAction
    }
  };
}

/**
 * แสดงรายงาน FVG + การวิเคราะห์แท่งเฉพาะ
 */
function printDetailedReport(candleData, candleIndex, pipDecimal = 5) {
  console.log('═══════════════════════════════════════════════════');
  console.log('     📊 DETAILED CANDLE & FVG ANALYSIS');
  console.log('═══════════════════════════════════════════════════\n');

  // วิเคราะห์ FVG ทั้งหมด
  const fvgResult = analyzeFVG(candleData, pipDecimal);

  // วิเคราะห์แท่งเฉพาะ
  const candleAnalysis = analyzeSpecificCandle(candleData, candleIndex, pipDecimal);

  if (!candleAnalysis) {
    console.log('❌ ไม่พบข้อมูลแท่งเทียนที่ระบุ\n');
    return;
  }

  console.log(`🕐 แท่งเทียนที่ ${candleIndex + 1} (Index: ${candleIndex})`);
  console.log(`   เวลา: ${candleAnalysis.thaiTime}`);
  console.log(`   สี: ${candleAnalysis.color}`);
  console.log(`   Open: ${candleAnalysis.open}`);
  console.log(`   High: ${candleAnalysis.high}`);
  console.log(`   Low: ${candleAnalysis.low}`);
  console.log(`   Close: ${candleAnalysis.close}`);
  console.log(`   Body Size: ${candleAnalysis.bodySizePip} pips (${candleAnalysis.bodyPercent}%)`);
  console.log(`   Candle Size: ${candleAnalysis.candleSizePip} pips`);
  console.log(`   แท่งแรง: ${candleAnalysis.isStrongCandle ? 'ใช่ ✓' : 'ไม่ใช่ ✗'}\n`);

  console.log('📍 สถานะ FVG ของแท่งนี้:');
  if (candleAnalysis.fvgRole !== 'None' && candleAnalysis.fvgDetails) {
    console.log(`   บทบาท: ${candleAnalysis.fvgRole}`);
    console.log(`   ${candleAnalysis.fvgDetails.role}`);
    console.log(`   ประเภท FVG: ${candleAnalysis.fvgDetails.type}`);
    console.log(`   โซน FVG: ${candleAnalysis.fvgDetails.gapBottom.toFixed(5)} - ${candleAnalysis.fvgDetails.gapTop.toFixed(5)}`);
    console.log(`   ขนาด Gap: ${calculatePip(candleAnalysis.fvgDetails.gapSize, pipDecimal)} pips\n`);
  } else {
    console.log(`   แท่งนี้ไม่ได้เป็นส่วนหนึ่งของ FVG\n`);
  }

  console.log('🔮 การทำนายแท่งถัดไป:');
  const nextPred = candleAnalysis.nextCandlePrediction;

  if (nextPred.expectedColor) {
    console.log(`   คาดว่าจะเป็นสี: ${nextPred.expectedColor}`);
    console.log(`   ทิศทาง: ${nextPred.expectedDirection}`);
    console.log(`   ความมั่นใจ: ${nextPred.confidence}%`);

    if (nextPred.priceTargets && nextPred.priceTargets.high) {
      console.log(`   เป้าหมายด้านบน: ${nextPred.priceTargets.high.toFixed(5)}`);
    }
    if (nextPred.priceTargets && nextPred.priceTargets.low) {
      console.log(`   เป้าหมายด้านล่าง: ${nextPred.priceTargets.low.toFixed(5)}`);
    }

    if (nextPred.reasons.length > 0) {
      console.log(`   เหตุผล:`);
      nextPred.reasons.forEach(reason => {
        console.log(`     - ${reason}`);
      });
    }
  } else if (nextPred.actualColor) {
    console.log(`   แท่งจริงที่เกิดขึ้น: ${nextPred.actualColor}`);
    console.log(`   Open: ${nextPred.actualOpen}`);
    console.log(`   Close: ${nextPred.actualClose}`);
    console.log(`   High: ${nextPred.actualHigh}`);
    console.log(`   Low: ${nextPred.actualLow}`);
  }
  console.log('');

  console.log('📊 สรุป FVG ทั้งหมด:');
  console.log(`   ทั้งหมด: ${fvgResult.summary.total}`);
  console.log(`   Active: ${fvgResult.summary.active} (Bullish: ${fvgResult.summary.bullish}, Bearish: ${fvgResult.summary.bearish})`);
  console.log(`   Filled: ${fvgResult.summary.filled}\n`);

  console.log('💡 แนวโน้มโดยรวม:');
  console.log(`   เทรนด์: ${fvgResult.prediction.trend}`);
  console.log(`   ความมั่นใจ: ${fvgResult.prediction.confidence}%`);
  console.log(`   คำแนะนำ: ${fvgResult.prediction.action}\n`);

  if (fvgResult.nearestFVG) {
    console.log('🎯 FVG ที่ใกล้ที่สุด:');
    console.log(`   ประเภท: ${fvgResult.nearestFVG.type}`);
    console.log(`   โซน: ${fvgResult.nearestFVG.bottom} - ${fvgResult.nearestFVG.top}`);
    console.log(`   ขนาด: ${fvgResult.nearestFVG.sizePip} pips`);
    console.log(`   ระยะห่าง: ${fvgResult.nearestFVG.distancePip} pips\n`);
  }

  console.log('═══════════════════════════════════════════════════\n');
}

// ====================================
// ตัวอย่างการใช้งาน
// ====================================

/*
const rawCandles = [
  { epoch: 1697097600, open: "1.05234", close: "1.05289", high: "1.05312", low: "1.05201" },
  { epoch: 1697097660, open: "1.05289", close: "1.05456", high: "1.05478", low: "1.05280" },
  { epoch: 1697097720, open: "1.05456", close: "1.05445", high: "1.05467", low: "1.05334" },
  { epoch: 1697097780, open: "1.05445", close: "1.05420", high: "1.05455", low: "1.05410" }
];

// วิเคราะห์แท่งเฉพาะ (เช่น แท่งที่ 2)
printDetailedReport(rawCandles, 1, 5);

// หรือวิเคราะห์แท่งสุดท้าย (แท่งปัจจุบัน)
printDetailedReport(rawCandles, rawCandles.length - 1, 5);

// วิเคราะห์หลายแท่ง
for (let i = 0; i < rawCandles.length; i++) {
  console.log(`\n========== วิเคราะห์แท่งที่ ${i + 1} ==========`);
  const analysis = analyzeSpecificCandle(rawCandles, i, 5);
  console.log(`สี: ${analysis.color}`);
  console.log(`FVG Role: ${analysis.fvgRole}`);
  if (analysis.nextCandlePrediction.expectedColor) {
    console.log(`ทำนายแท่งถัดไป: ${analysis.nextCandlePrediction.expectedColor} (${analysis.nextCandlePrediction.confidence}%)`);
  }
}
*/