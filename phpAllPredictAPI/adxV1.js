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
    const previous = i > period * 2 - 1 ? result[i - 1] : null;

    // กำหนด direction โดยเปรียบเทียบกับค่าก่อนหน้า
    if (previous && current.adxValue !== undefined && previous.adxValue !== undefined) {
      if (current.adxValue > previous.adxValue) {
        const newDirection = 'Up';

        // ถ้าทิศทางเปลี่ยน ให้ reset counter
        if (currentDirection !== newDirection) {
          adxUpContinue = 1;
          adxDownContinue = 0;
          currentDirection = newDirection;
        } else {
          adxUpContinue++;
        }

      } else if (current.adxValue < previous.adxValue) {
        const newDirection = 'Down';

        // ถ้าทิศทางเปลี่ยน ให้ reset counter
        if (currentDirection !== newDirection) {
          adxDownContinue = 1;
          adxUpContinue = 0;
          currentDirection = newDirection;
        } else {
          adxDownContinue++;
        }

      } else {
        // ค่าเท่าเดิม ไม่เปลี่ยน direction และ counter
      }
    } else if (!previous && current.adxValue !== undefined) {
      // จุดแรกที่มี ADX value
      currentDirection = 'Up'; // default
      adxUpContinue = 1;
      adxDownContinue = 0;
    }

    // กำหนดค่าลงใน object
    current.adxDirection = currentDirection;
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

  return result;
}

// เรียกใช้ function
// const result = calculateADXWithDirections(sampleData, 14);
// console.log(result);