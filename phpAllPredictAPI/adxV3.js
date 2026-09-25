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
    return { greenProbability: 50, redProbability: 50, suggestion: "ข้อมูลไม่เพียงพอ" };
  }

  const lastCandle = data[data.length - 1];
  const currentColor = lastCandle.thisColor;
  const adxTrendStrength = lastCandle.adxTrendStrength || 'No Clear Trend';
  const adxValue = lastCandle.adxValue || 0;

  // วิเคราะห์ pattern การสลับสี
  const recentColors = data.slice(-6).map(d => d.thisColor); // ดู 6 แท่งล่าสุด
  const lastTwoColors = data.slice(-2).map(d => d.thisColor);
  const lastThreeColors = data.slice(-3).map(d => d.thisColor);
  const lastFourColors = data.slice(-4).map(d => d.thisColor);

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

  // ตรวจสอบ alternating pattern (สลับสี)
  let isAlternating = false;
  let alternatingStrength = 0;

  if (recentColors.length >= 4) {
    // ตรวจสอบ pattern สลับสี 4 แท่งล่าสุด
    const alt4 = recentColors.slice(-4);
    if (alt4[0] !== alt4[1] && alt4[1] !== alt4[2] && alt4[2] !== alt4[3] && alt4[0] === alt4[2] && alt4[1] === alt4[3]) {
      isAlternating = true;
      alternatingStrength = 4;
    }
    // ตรวจสอบ pattern สลับสี 6 แท่ง
    else if (recentColors.length >= 6) {
      const alt6 = recentColors;
      if (alt6[0] !== alt6[1] && alt6[1] !== alt6[2] && alt6[2] !== alt6[3] &&
          alt6[3] !== alt6[4] && alt6[4] !== alt6[5] &&
          alt6[0] === alt6[2] && alt6[2] === alt6[4] &&
          alt6[1] === alt6[3] && alt6[3] === alt6[5]) {
        isAlternating = true;
        alternatingStrength = 6;
      }
    }
  }

  // Base probability
  let greenProb = 50;
  let redProb = 50;
  let suggestion = "";

  // Priority 1: การสลับสี (สำคัญที่สุด)
  if (isAlternating) {
    const nextExpectedColor = currentColor === 'Green' ? 'Red' : 'Green';
    if (nextExpectedColor === 'Green') {
      greenProb = 85; // สลับสีมีโอกาสสูงมาก
      suggestion = `รูปแบบสลับสี ${alternatingStrength} แท่ง - คาดว่าจะเป็นเขียว`;
    } else {
      redProb = 85;
      suggestion = `รูปแบบสลับสี ${alternatingStrength} แท่ง - คาดว่าจะเป็นแดง`;
    }
  }
  // Priority 2: สีต่อเนื่องเกิน 3 แท่ง (การกลับตัว)
  else if (consecutiveCount >= 5) {
    if (currentColor === 'Green') {
      redProb = 75;
      suggestion = `เขียวต่อเนื่อง ${consecutiveCount} แท่ง - แนวโน้มกลับเป็นแดงสูง`;
    } else {
      greenProb = 75;
      suggestion = `แดงต่อเนื่อง ${consecutiveCount} แท่ง - แนวโน้มกลับเป็นเขียวสูง`;
    }
  }
  else if (consecutiveCount >= 3) {
    if (currentColor === 'Green') {
      redProb = 65;
      suggestion = `เขียวต่อเนื่อง ${consecutiveCount} แท่ง - มีแนวโน้มกลับเป็นแดง`;
    } else {
      greenProb = 65;
      suggestion = `แดงต่อเนื่อง ${consecutiveCount} แท่ง - มีแนวโน้มกลับเป็นเขียว`;
    }
  }
  // Priority 3: ADX Trend (รองลงมา)
  else {
    if (adxTrendStrength.includes('Uptrend')) {
      if (adxTrendStrength.includes('Very Strong')) {
        greenProb = 70;
        suggestion = "เทรนด์ขาขึ้นแรงมาก - โอกาสเขียวสูง";
      } else if (adxTrendStrength.includes('Strong')) {
        greenProb = 65;
        suggestion = "เทรนด์ขาขึ้นแรง - โอกาสเขียวค่อนข้างสูง";
      } else if (adxTrendStrength.includes('Moderate')) {
        greenProb = 60;
        suggestion = "เทรนด์ขาขึ้นปานกลาง - โอกาสเขียวเล็กน้อย";
      } else {
        greenProb = 55;
        suggestion = "เทรนด์ขาขึ้นอ่อน - โอกาสเขียวเล็กน้อย";
      }
    } else if (adxTrendStrength.includes('Downtrend')) {
      if (adxTrendStrength.includes('Very Strong')) {
        redProb = 70;
        suggestion = "เทรนด์ขาลงแรงมาก - โอกาสแดงสูง";
      } else if (adxTrendStrength.includes('Strong')) {
        redProb = 65;
        suggestion = "เทรนด์ขาลงแรง - โอกาสแดงค่อนข้างสูง";
      } else if (adxTrendStrength.includes('Moderate')) {
        redProb = 60;
        suggestion = "เทรนด์ขาลงปานกลาง - โอกาสแดงเล็กน้อย";
      } else {
        redProb = 55;
        suggestion = "เทรนด์ขาลงอ่อน - โอกาสแดงเล็กน้อย";
      }
    } else {
      suggestion = "ไม่มีเทรนด์ชัดเจน - เป็นกลาง";
    }

    // เพิ่มข้อมูล ADX strength
    if (adxValue > 0) {
      suggestion += ` (ADX: ${adxValue})`;
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
    adxStrength: adxValue,
    isAlternating: isAlternating,
    alternatingStrength: alternatingStrength
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