function calculateADXWithDirections(data, period = 14) {
  if (!data || data.length < period * 2) {
    return data; // ต้องมีข้อมูลเพียงพอสำหรับการคำนวณ
  }

  const result = [...data];

  // คำนวณ True Range (TR), +DM, -DM
  for (let i = 1; i < result.length; i++) {
    const current = result[i];
    const previous = result[i - 1];

    // True Range
    const tr1 = current.high - current.low;
    const tr2 = Math.abs(current.high - previous.close);
    const tr3 = Math.abs(current.low - previous.close);
    current.tr = Math.max(tr1, tr2, tr3);

    // Directional Movement
    const highDiff = current.high - previous.high;
    const lowDiff = previous.low - current.low;

    current.plusDM = (highDiff > lowDiff && highDiff > 0) ? highDiff : 0;
    current.minusDM = (lowDiff > highDiff && lowDiff > 0) ? lowDiff : 0;
  }

  // คำนวณ smoothed values
  for (let i = period; i < result.length; i++) {
    if (i === period) {
      // ครั้งแรก ใช้ simple average
      result[i].smoothedTR = result.slice(1, i + 1).reduce((sum, item) => sum + item.tr, 0);
      result[i].smoothedPlusDM = result.slice(1, i + 1).reduce((sum, item) => sum + item.plusDM, 0);
      result[i].smoothedMinusDM = result.slice(1, i + 1).reduce((sum, item) => sum + item.minusDM, 0);
    } else {
      // Wilder's smoothing
      result[i].smoothedTR = result[i - 1].smoothedTR - (result[i - 1].smoothedTR / period) + result[i].tr;
      result[i].smoothedPlusDM = result[i - 1].smoothedPlusDM - (result[i - 1].smoothedPlusDM / period) + result[i].plusDM;
      result[i].smoothedMinusDM = result[i - 1].smoothedMinusDM - (result[i - 1].smoothedMinusDM / period) + result[i].minusDM;
    }

    // คำนวณ DI+ และ DI-
    result[i].plusDI = (result[i].smoothedPlusDM / result[i].smoothedTR) * 100;
    result[i].minusDI = (result[i].smoothedMinusDM / result[i].smoothedTR) * 100;

    // คำนวณ DX
    const diSum = result[i].plusDI + result[i].minusDI;
    const diDiff = Math.abs(result[i].plusDI - result[i].minusDI);
    result[i].dx = diSum > 0 ? (diDiff / diSum) * 100 : 0;
  }

  // คำนวณ ADX
  let adxSum = 0;
  for (let i = period; i < result.length; i++) {
    if (i < period * 2 - 1) {
      adxSum += result[i].dx;
      if (i === period * 2 - 2) {
        result[i].adxValue = adxSum / period;
      }
    } else {
      // Wilder's smoothing สำหรับ ADX
      result[i].adxValue = (result[i - 1].adxValue * (period - 1) + result[i].dx) / period;
    }
  }

  // กำหนด ADX Direction และติดตาม continue counts
  let currentDirection = null;
  let adxUpContinue = 0;
  let adxDownContinue = 0;

  for (let i = period * 2 - 1; i < result.length; i++) {
    const current = result[i];

    // กำหนด direction โดยเปรียบเทียบ +DI กับ -DI
    let newDirection = null;
    if (current.plusDI !== undefined && current.minusDI !== undefined) {
      if (current.plusDI > current.minusDI) {
        newDirection = 'Up';   // uptrend แข็งแกร่งกว่า
      } else if (current.minusDI > current.plusDI) {
        newDirection = 'Down'; // downtrend แข็งแกร่งกว่า
      } else {
        newDirection = currentDirection; // เท่ากัน ให้ใช้ direction เดิม
      }
    }

    // ถ้าทิศทางเปลี่ยน ให้ reset counter
    if (currentDirection !== newDirection) {
      if (newDirection === 'Up') {
        adxUpContinue = 1;
        adxDownContinue = 0;
      } else if (newDirection === 'Down') {
        adxDownContinue = 1;
        adxUpContinue = 0;
      }
      currentDirection = newDirection;
    } else {
      // ทิศทางเดิม เพิ่ม counter
      if (currentDirection === 'Up') {
        adxUpContinue++;
      } else if (currentDirection === 'Down') {
        adxDownContinue++;
      }
    }

    // กำหนดความแข็งแกร่งของ trend
    let trendStrength = 'No Trend';
    if (current.adxValue !== undefined) {
      if (current.adxValue >= 50) {
        trendStrength = 'Very Strong';
      } else if (current.adxValue >= 25) {
        trendStrength = 'Strong';
      } else if (current.adxValue >= 20) {
        trendStrength = 'Moderate';
      } else {
        trendStrength = 'Weak';
      }
    }

    // รวม direction และ strength
    let adxTrendStrength = 'No Trend';
    if (current.adxValue !== undefined && current.adxValue >= 20) {
      if (currentDirection === 'Up') {
        adxTrendStrength = `${trendStrength} Uptrend`;
      } else if (currentDirection === 'Down') {
        adxTrendStrength = `${trendStrength} Downtrend`;
      } else {
        adxTrendStrength = `${trendStrength} Sideways`;
      }
    } else {
      adxTrendStrength = 'No Clear Trend';
    }

    // กำหนดค่าลงใน object
    current.adxDirection = currentDirection;
    current.adxTrendStrength = adxTrendStrength;
    current.adxUpContinue = adxUpContinue;
    current.adxDownContinue = adxDownContinue;

    // ปัดเศษ ADX value
    if (current.adxValue !== undefined) {
      current.adxValue = Math.round(current.adxValue * 100) / 100;
    }
  }

  // ลบ properties ที่ใช้ในการคำนวณออก (optional)
  result.forEach(item => {
    delete item.tr;
    delete item.plusDM;
    delete item.minusDM;
    delete item.smoothedTR;
    delete item.smoothedPlusDM;
    delete item.smoothedMinusDM;
    delete item.plusDI;
    delete item.minusDI;
    delete item.dx;
  });

  // เพิ่ม prediction สำหรับแต่ละแท่ง
  for (let i = period * 2; i < result.length; i++) {
    const prediction = predictNextCandleColor(result.slice(0, i + 1));
    result[i].nextCandlePrediction = {
      greenProbability: prediction.greenProbability,
      redProbability: prediction.redProbability,
      suggestion: prediction.suggestion,
      consecutiveCount: prediction.consecutiveCount,
      currentColor: prediction.currentColor,
      adxStrength: prediction.adxStrength
    };
  }

  return result;
}

// Function วิเคราะห์สีแท่งถัดไป
function predictNextCandleColor(data) {
  if (!data || data.length < 3) {
    return { greenProbability: 50, redProbability: 50, suggestion: "Insufficient Data" };
  }

  const lastCandle = data[data.length - 1];
  const currentColor = lastCandle.thisColor;
  const adxTrendStrength = lastCandle.adxTrendStrength || 'No Clear Trend';
  const adxValue = lastCandle.adxValue || 0;

  // นับลำดับสีต่อเนื่อง
  let consecutiveCount = 1;
  let currentColorSeq = currentColor;

  for (let i = data.length - 2; i >= 0; i--) {
    if (data[i].thisColor === currentColorSeq) {
      consecutiveCount++;
    } else {
      break;
    }
  }

  // Base probability
  let greenProb = 50;
  let redProb = 50;
  let suggestion = "";

  // ปรับ probability ตาม ADX Trend Strength
  if (adxTrendStrength.includes('Uptrend')) {
    if (adxTrendStrength.includes('Very Strong')) {
      greenProb += 35; // Very Strong Uptrend
      suggestion = "Very Strong Uptrend - High chance of Green";
    } else if (adxTrendStrength.includes('Strong')) {
      greenProb += 25; // Strong Uptrend
      suggestion = "Strong Uptrend - Good chance of Green";
    } else if (adxTrendStrength.includes('Moderate')) {
      greenProb += 15; // Moderate Uptrend
      suggestion = "Moderate Uptrend - Slightly favor Green";
    } else {
      greenProb += 5; // Weak Uptrend
      suggestion = "Weak Uptrend - Slightly favor Green";
    }
  } else if (adxTrendStrength.includes('Downtrend')) {
    if (adxTrendStrength.includes('Very Strong')) {
      redProb += 35; // Very Strong Downtrend
      suggestion = "Very Strong Downtrend - High chance of Red";
    } else if (adxTrendStrength.includes('Strong')) {
      redProb += 25; // Strong Downtrend
      suggestion = "Strong Downtrend - Good chance of Red";
    } else if (adxTrendStrength.includes('Moderate')) {
      redProb += 15; // Moderate Downtrend
      suggestion = "Moderate Downtrend - Slightly favor Red";
    } else {
      redProb += 5; // Weak Downtrend
      suggestion = "Weak Downtrend - Slightly favor Red";
    }
  } else {
    suggestion = "No Clear Trend - Neutral";
  }

  // ปรับตามลำดับสีต่อเนื่อง (Mean Reversion)
  if (consecutiveCount >= 5) {
    // สีต่อเนื่อง 5 แท่งขึ้นไป มีแนวโน้มจะกลับ
    if (currentColor === 'Green') {
      redProb += 15;
      suggestion += " + Long Green streak - expect reversal";
    } else {
      greenProb += 15;
      suggestion += " + Long Red streak - expect reversal";
    }
  } else if (consecutiveCount >= 3) {
    // สีต่อเนื่อง 3-4 แท่ง มีแนวโน้มจะกลับเล็กน้อย
    if (currentColor === 'Green') {
      redProb += 10;
      suggestion += " + Green streak - slight reversal bias";
    } else {
      greenProb += 10;
      suggestion += " + Red streak - slight reversal bias";
    }
  }

  // ปรับค่าให้รวมเป็น 100%
  const total = greenProb + redProb;
  greenProb = Math.round((greenProb / total) * 100);
  redProb = 100 - greenProb;

  return {
    greenProbability: greenProb,
    redProbability: redProb,
    suggestion: suggestion.trim(),
    consecutiveCount: consecutiveCount,
    currentColor: currentColor,
    adxStrength: adxValue
  };
}
/*
// ตัวอย่างการใช้งาน
const sampleData = [
  {
    "time": 1750997160,
    "open": 99427.1961,
    "high": 99579.3184,
    "low": 99385.5709,
    "close": 99579.3184,
    "thisColor": "Green"
  }
  // เพิ่มข้อมูลเพิ่มเติมตามต้องการ...
];

// เรียกใช้ function
// const resultWithADX = calculateADXWithDirections(sampleData, 14);
// const prediction = predictNextCandleColor(resultWithADX);
// console.log('ADX Analysis:', resultWithADX);
// console.log('Next Candle Prediction:', prediction);
*/