/**
 * ========================================
 * SMART MONEY CONCEPTS (SMC) ANALYZER
 * Complete Version with All Functions
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
 * กำหนดสีของแท่งเทียน
 */
function getCandleColor(open, close) {
  if (close > open) return 'Green';
  if (close < open) return 'Red';
  return 'Equal';
}

/**
 * วิเคราะห์ Momentum ของแท่งเทียน
 */
function analyzeMomentum(candle) {
  const open = parseFloat(candle.open);
  const close = parseFloat(candle.close);
  const high = parseFloat(candle.high);
  const low = parseFloat(candle.low);

  const bodySize = Math.abs(close - open);
  const candleSize = high - low;
  const bodyPercent = candleSize > 0 ? (bodySize / candleSize) * 100 : 0;

  const isStrong = bodyPercent > 70;
  const direction = close > open ? 'Bullish' : close < open ? 'Bearish' : 'Neutral';

  return {
    direction: direction,
    bodyPercent: bodyPercent,
    isStrong: isStrong,
    bodySize: bodySize
  };
}

/**
 * หา Fair Value Gap (FVG) จากข้อมูล Candle
 * @param {Array} candleData - Array ของข้อมูล candle
 * @param {number} pipDecimal - จำนวนทศนิยมสำหรับคำนวณ pip (default: 5)
 * @returns {Array} Array ของ FVG ที่พบพร้อมการทำนาย
 */
function findFairValueGaps(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 3) {
    return [];
  }

  const fvgResults = [];

  /**
   * ตรวจสอบว่ามี FVG หรือไม่
   */
  function checkFVG(candle1, candle2, candle3) {
    const c1_high = parseFloat(candle1.high);
    const c1_low = parseFloat(candle1.low);
    const c3_high = parseFloat(candle3.high);
    const c3_low = parseFloat(candle3.low);

    let fvgType = null;
    let gapTop = 0;
    let gapBottom = 0;
    let gapSize = 0;

    // Bullish FVG
    if (c1_high < c3_low) {
      fvgType = 'Bullish';
      gapBottom = c1_high;
      gapTop = c3_low;
      gapSize = gapTop - gapBottom;
    }
    // Bearish FVG
    else if (c1_low > c3_high) {
      fvgType = 'Bearish';
      gapTop = c1_low;
      gapBottom = c3_high;
      gapSize = gapTop - gapBottom;
    }

    return {
      type: fvgType,
      gapTop: gapTop,
      gapBottom: gapBottom,
      gapSize: gapSize,
      gapMidPoint: fvgType ? (gapTop + gapBottom) / 2 : 0
    };
  }

  /**
   * ทำนายทิศทางและแท่งต่อไป
   */
  function predictNextMove(fvg, recentCandles) {
    const lastCandle = recentCandles[recentCandles.length - 1];
    const currentPrice = parseFloat(lastCandle.close);

    let prediction = {
      trend: 'Unknown',
      confidence: 0,
      targetZone: null,
      expectedAction: '',
      reasons: [],
      nextCandleExpectation: {}
    };

    if (fvg.type === 'Bullish') {
      prediction.trend = 'Bullish';

      if (currentPrice > fvg.gapTop) {
        prediction.expectedAction = 'รอราคากลับมาทดสอบ FVG Zone';
        prediction.targetZone = {
          entry: fvg.gapMidPoint,
          stopLoss: fvg.gapBottom,
          takeProfit: currentPrice + (fvg.gapSize * 2)
        };
        prediction.confidence = 70;
        prediction.reasons.push('ราคาอยู่เหนือ Bullish FVG - รอ Retest');
        prediction.reasons.push('โอกาสสูงที่ราคาจะกลับมาเติม Gap ก่อนขึ้นต่อ');

        prediction.nextCandleExpectation = {
          expectedDirection: 'Pullback (ลงมาทดสอบ FVG)',
          expectedRange: [fvg.gapTop, currentPrice],
          idealScenario: 'แท่งเทียนสีแดง/Doji แล้วตีกลับขึ้น'
        };
      } else if (currentPrice >= fvg.gapBottom && currentPrice <= fvg.gapTop) {
        prediction.expectedAction = 'ซื้อทันที - ราคาอยู่ในโซน FVG';
        prediction.targetZone = {
          entry: currentPrice,
          stopLoss: fvg.gapBottom - fvg.gapSize * 0.2,
          takeProfit: fvg.gapTop + (fvg.gapSize * 2)
        };
        prediction.confidence = 85;
        prediction.reasons.push('ราคาอยู่ในโซน Bullish FVG - โอกาสดี');
        prediction.reasons.push('Smart Money มักวาง Buy Order ในโซนนี้');

        prediction.nextCandleExpectation = {
          expectedDirection: 'Bullish (ขาขึ้น)',
          expectedRange: [currentPrice, fvg.gapTop + fvg.gapSize],
          idealScenario: 'แท่งเทียนสีเขียวแรง พุ่งออกจาก FVG'
        };
      } else {
        prediction.expectedAction = 'รอราคาเข้าสู่โซน FVG';
        prediction.confidence = 60;
        prediction.reasons.push('ราคายังอยู่ใต้ Bullish FVG');

        prediction.nextCandleExpectation = {
          expectedDirection: 'Bullish (ขาขึ้นเข้า FVG)',
          expectedRange: [currentPrice, fvg.gapBottom],
          idealScenario: 'แท่งเทียนสีเขียวเข้าสู่โซน FVG'
        };
      }
    }
    else if (fvg.type === 'Bearish') {
      prediction.trend = 'Bearish';

      if (currentPrice < fvg.gapBottom) {
        prediction.expectedAction = 'รอราคากลับมาทดสอบ FVG Zone';
        prediction.targetZone = {
          entry: fvg.gapMidPoint,
          stopLoss: fvg.gapTop,
          takeProfit: currentPrice - (fvg.gapSize * 2)
        };
        prediction.confidence = 70;
        prediction.reasons.push('ราคาอยู่ใต้ Bearish FVG - รอ Retest');
        prediction.reasons.push('โอกาสสูงที่ราคาจะกลับมาเติม Gap ก่อนลงต่อ');

        prediction.nextCandleExpectation = {
          expectedDirection: 'Pullback (ขึ้นมาทดสอบ FVG)',
          expectedRange: [currentPrice, fvg.gapBottom],
          idealScenario: 'แท่งเทียนสีเขียว/Doji แล้วตีกลับลง'
        };
      } else if (currentPrice >= fvg.gapBottom && currentPrice <= fvg.gapTop) {
        prediction.expectedAction = 'ขายทันที - ราคาอยู่ในโซน FVG';
        prediction.targetZone = {
          entry: currentPrice,
          stopLoss: fvg.gapTop + fvg.gapSize * 0.2,
          takeProfit: fvg.gapBottom - (fvg.gapSize * 2)
        };
        prediction.confidence = 85;
        prediction.reasons.push('ราคาอยู่ในโซน Bearish FVG - โอกาสดี');
        prediction.reasons.push('Smart Money มักวาง Sell Order ในโซนนี้');

        prediction.nextCandleExpectation = {
          expectedDirection: 'Bearish (ขาลง)',
          expectedRange: [fvg.gapBottom - fvg.gapSize, currentPrice],
          idealScenario: 'แท่งเทียนสีแดงแรง ดิ่งออกจาก FVG'
        };
      } else {
        prediction.expectedAction = 'รอราคาเข้าสู่โซน FVG';
        prediction.confidence = 60;
        prediction.reasons.push('ราคายังอยู่เหนือ Bearish FVG');

        prediction.nextCandleExpectation = {
          expectedDirection: 'Bearish (ขาลงเข้า FVG)',
          expectedRange: [fvg.gapTop, currentPrice],
          idealScenario: 'แท่งเทียนสีแดงเข้าสู่โซน FVG'
        };
      }
    }

    const lastMomentum = analyzeMomentum(lastCandle);
    if (lastMomentum.isStrong) {
      if (lastMomentum.direction === prediction.trend) {
        prediction.confidence += 10;
        prediction.reasons.push(`แท่งล่าสุด${lastMomentum.direction}แรง - ยืนยันทิศทาง`);
      } else {
        prediction.confidence -= 15;
        prediction.reasons.push(`แท่งล่าสุด${lastMomentum.direction}แรง - ขัดกับทิศทาง FVG`);
      }
    }

    prediction.confidence = Math.min(95, Math.max(30, prediction.confidence));

    return prediction;
  }

  /**
   * ตรวจสอบว่า FVG ถูกเติม (Filled) แล้วหรือยัง
   */
  function checkFVGFilled(fvg, subsequentCandles) {
    let filled = false;
    let filledAt = null;
    let fillPercentage = 0;

    for (let i = 0; i < subsequentCandles.length; i++) {
      const candle = subsequentCandles[i];
      const high = parseFloat(candle.high);
      const low = parseFloat(candle.low);

      if (fvg.type === 'Bullish') {
        if (low <= fvg.gapTop && high >= fvg.gapBottom) {
          const touchedRange = Math.min(high, fvg.gapTop) - Math.max(low, fvg.gapBottom);
          fillPercentage = (touchedRange / fvg.gapSize) * 100;

          if (fillPercentage >= 50) {
            filled = true;
            filledAt = {
              index: i,
              candle: candle,
              fillPercentage: fillPercentage
            };
            break;
          }
        }
      } else if (fvg.type === 'Bearish') {
        if (high >= fvg.gapBottom && low <= fvg.gapTop) {
          const touchedRange = Math.min(high, fvg.gapTop) - Math.max(low, fvg.gapBottom);
          fillPercentage = (touchedRange / fvg.gapSize) * 100;

          if (fillPercentage >= 50) {
            filled = true;
            filledAt = {
              index: i,
              candle: candle,
              fillPercentage: fillPercentage
            };
            break;
          }
        }
      }
    }

    return {
      isFilled: filled,
      filledAt: filledAt,
      fillPercentage: fillPercentage
    };
  }

  // หา FVG ทั้งหมด
  for (let i = 0; i < candleData.length - 2; i++) {
    const candle1 = candleData[i];
    const candle2 = candleData[i + 1];
    const candle3 = candleData[i + 2];

    const fvgCheck = checkFVG(candle1, candle2, candle3);

    if (fvgCheck.type) {
      const momentum = analyzeMomentum(candle2);
      const subsequentCandles = candleData.slice(i + 3);
      const fillStatus = checkFVGFilled(fvgCheck, subsequentCandles);
      const recentCandles = candleData.slice(Math.max(0, i - 5), i + 3);
      const prediction = predictNextMove(fvgCheck, recentCandles);

      const fvgData = {
        fvgIndex: i,
        fvgType: fvgCheck.type,
        candle1Index: i,
        candle2Index: i + 1,
        candle3Index: i + 2,

        candle1Time: parseInt(candle1.epoch),
        candle1ThaiTime: toThaiTime(parseInt(candle1.epoch)),
        candle2Time: parseInt(candle2.epoch),
        candle2ThaiTime: toThaiTime(parseInt(candle2.epoch)),
        candle3Time: parseInt(candle3.epoch),
        candle3ThaiTime: toThaiTime(parseInt(candle3.epoch)),

        gapTop: parseFloat(fvgCheck.gapTop.toFixed(pipDecimal + 2)),
        gapBottom: parseFloat(fvgCheck.gapBottom.toFixed(pipDecimal + 2)),
        gapSize: parseFloat(fvgCheck.gapSize.toFixed(pipDecimal + 2)),
        gapSizePip: calculatePip(fvgCheck.gapSize, pipDecimal),
        gapMidPoint: parseFloat(fvgCheck.gapMidPoint.toFixed(pipDecimal + 2)),

        impulsiveCandleMomentum: momentum,
        fillStatus: fillStatus,
        prediction: prediction,

        importance: fvgCheck.gapSize > 0.001 ? 'High' : fvgCheck.gapSize > 0.0005 ? 'Medium' : 'Low',
        status: fillStatus.isFilled ? 'Filled' : 'Active'
      };

      fvgResults.push(fvgData);
    }
  }

  return fvgResults;
}

/**
 * หา FVG ที่ยังไม่ถูกเติม (Active FVG)
 */
function getActiveFVGs(fvgResults) {
  return fvgResults.filter(fvg => fvg.status === 'Active');
}

/**
 * หา FVG ที่ใกล้ราคาปัจจุบันที่สุด
 */
function getNearestFVG(fvgResults, currentPrice) {
  const activeFVGs = getActiveFVGs(fvgResults);

  if (activeFVGs.length === 0) return null;

  let nearest = activeFVGs[0];
  let minDistance = Math.abs(currentPrice - activeFVGs[0].gapMidPoint);

  for (let i = 1; i < activeFVGs.length; i++) {
    const distance = Math.abs(currentPrice - activeFVGs[i].gapMidPoint);
    if (distance < minDistance) {
      minDistance = distance;
      nearest = activeFVGs[i];
    }
  }

  return {
    fvg: nearest,
    distance: minDistance,
    distancePip: Math.round(minDistance * Math.pow(10, 5))
  };
}

/**
 * หา Order Block (OB)
 */
function findOrderBlocks(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 5) {
    return [];
  }

  const orderBlocks = [];

  for (let i = 1; i < candleData.length - 3; i++) {
    const currentCandle = candleData[i];
    const nextCandle1 = candleData[i + 1];
    const nextCandle2 = candleData[i + 2];
    const nextCandle3 = candleData[i + 3];

    const curr_open = parseFloat(currentCandle.open);
    const curr_close = parseFloat(currentCandle.close);
    const curr_high = parseFloat(currentCandle.high);
    const curr_low = parseFloat(currentCandle.low);

    const next1_close = parseFloat(nextCandle1.close);
    const next2_close = parseFloat(nextCandle2.close);
    const next3_close = parseFloat(nextCandle3.close);

    let obType = null;
    let obTop = 0;
    let obBottom = 0;
    let strength = 0;

    // Bullish Order Block
    if (curr_close < curr_open) {
      if (next1_close > curr_close && next2_close > next1_close && next3_close > next2_close) {
        const priceMovement = next3_close - curr_close;
        const candleSize = curr_high - curr_low;

        if (priceMovement > candleSize * 2) {
          obType = 'Bullish';
          obTop = curr_high;
          obBottom = curr_low;
          strength = Math.min(100, (priceMovement / candleSize) * 20);
        }
      }
    }
    // Bearish Order Block
    else if (curr_close > curr_open) {
      if (next1_close < curr_close && next2_close < next1_close && next3_close < next2_close) {
        const priceMovement = curr_close - next3_close;
        const candleSize = curr_high - curr_low;

        if (priceMovement > candleSize * 2) {
          obType = 'Bearish';
          obTop = curr_high;
          obBottom = curr_low;
          strength = Math.min(100, (priceMovement / candleSize) * 20);
        }
      }
    }

    if (obType) {
      const subsequentCandles = candleData.slice(i + 4);
      let tested = false;
      let testedAt = null;
      let holdStrength = 0;

      for (let j = 0; j < subsequentCandles.length; j++) {
        const testCandle = subsequentCandles[j];
        const test_high = parseFloat(testCandle.high);
        const test_low = parseFloat(testCandle.low);

        if (obType === 'Bullish') {
          if (test_low <= obTop && test_low >= obBottom) {
            tested = true;
            testedAt = j;

            if (j < subsequentCandles.length - 2) {
              const bounce1 = parseFloat(subsequentCandles[j + 1].close);
              const bounce2 = parseFloat(subsequentCandles[j + 2].close);
              if (bounce1 > test_low && bounce2 > bounce1) {
                holdStrength = 80;
              } else {
                holdStrength = 40;
              }
            }
            break;
          } else if (test_low < obBottom) {
            tested = true;
            testedAt = j;
            holdStrength = 0;
            break;
          }
        } else if (obType === 'Bearish') {
          if (test_high >= obBottom && test_high <= obTop) {
            tested = true;
            testedAt = j;

            if (j < subsequentCandles.length - 2) {
              const bounce1 = parseFloat(subsequentCandles[j + 1].close);
              const bounce2 = parseFloat(subsequentCandles[j + 2].close);
              if (bounce1 < test_high && bounce2 < bounce1) {
                holdStrength = 80;
              } else {
                holdStrength = 40;
              }
            }
            break;
          } else if (test_high > obTop) {
            tested = true;
            testedAt = j;
            holdStrength = 0;
            break;
          }
        }
      }

      const obSize = obTop - obBottom;
      const obMidPoint = (obTop + obBottom) / 2;

      orderBlocks.push({
        obIndex: i,
        obType: obType,
        obTop: parseFloat(obTop.toFixed(pipDecimal + 2)),
        obBottom: parseFloat(obBottom.toFixed(pipDecimal + 2)),
        obSize: parseFloat(obSize.toFixed(pipDecimal + 2)),
        obSizePip: calculatePip(obSize, pipDecimal),
        obMidPoint: parseFloat(obMidPoint.toFixed(pipDecimal + 2)),
        candleTime: parseInt(currentCandle.epoch),
        candleThaiTime: toThaiTime(parseInt(currentCandle.epoch)),
        strength: parseFloat(strength.toFixed(2)),
        tested: tested,
        testedAt: testedAt,
        holdStrength: holdStrength,
        status: tested ? (holdStrength > 50 ? 'Held' : 'Broken') : 'Untested',
        importance: strength > 70 ? 'High' : strength > 40 ? 'Medium' : 'Low'
      });
    }
  }

  return orderBlocks;
}

/**
 * หา Market Structure (BOS/CHoCH)
 */
function findMarketStructure(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 10) {
    return { swings: [], bos: [], choch: [], currentTrend: 'Unknown' };
  }

  function findSwingPoints(data, swingLength = 5) {
    const swings = [];

    for (let i = swingLength; i < data.length - swingLength; i++) {
      const currentHigh = parseFloat(data[i].high);
      const currentLow = parseFloat(data[i].low);

      let isSwingHigh = true;
      for (let j = 1; j <= swingLength; j++) {
        if (parseFloat(data[i - j].high) >= currentHigh ||
            parseFloat(data[i + j].high) >= currentHigh) {
          isSwingHigh = false;
          break;
        }
      }

      let isSwingLow = true;
      for (let j = 1; j <= swingLength; j++) {
        if (parseFloat(data[i - j].low) <= currentLow ||
            parseFloat(data[i + j].low) <= currentLow) {
          isSwingLow = false;
          break;
        }
      }

      if (isSwingHigh) {
        swings.push({
          type: 'High',
          index: i,
          price: currentHigh,
          time: parseInt(data[i].epoch),
          thaiTime: toThaiTime(parseInt(data[i].epoch))
        });
      }

      if (isSwingLow) {
        swings.push({
          type: 'Low',
          index: i,
          price: currentLow,
          time: parseInt(data[i].epoch),
          thaiTime: toThaiTime(parseInt(data[i].epoch))
        });
      }
    }

    return swings.sort((a, b) => a.index - b.index);
  }

  const swings = findSwingPoints(candleData);
  const bosPoints = [];
  const chochPoints = [];
  let currentTrend = 'Unknown';

  if (swings.length >= 4) {
    let lastHigherHigh = null;
    let lastLowerLow = null;

    for (let i = 0; i < swings.length; i++) {
      const swing = swings[i];

      if (swing.type === 'High') {
        if (!lastHigherHigh || swing.price > lastHigherHigh.price) {
          if (lastHigherHigh) {
            bosPoints.push({
              type: 'BOS',
              direction: 'Bullish',
              index: swing.index,
              price: swing.price,
              previousPrice: lastHigherHigh.price,
              time: swing.time,
              thaiTime: swing.thaiTime,
              strength: ((swing.price - lastHigherHigh.price) / lastHigherHigh.price) * 100
            });
          }
          lastHigherHigh = swing;
          currentTrend = 'Bullish';
        } else if (lastHigherHigh && swing.price < lastHigherHigh.price) {
          if (currentTrend === 'Bullish') {
            chochPoints.push({
              type: 'CHoCH',
              direction: 'Bearish',
              index: swing.index,
              price: swing.price,
              previousPrice: lastHigherHigh.price,
              time: swing.time,
              thaiTime: swing.thaiTime,
              strength: ((lastHigherHigh.price - swing.price) / lastHigherHigh.price) * 100
            });
            currentTrend = 'Bearish';
          }
        }
      } else if (swing.type === 'Low') {
        if (!lastLowerLow || swing.price < lastLowerLow.price) {
          if (lastLowerLow) {
            bosPoints.push({
              type: 'BOS',
              direction: 'Bearish',
              index: swing.index,
              price: swing.price,
              previousPrice: lastLowerLow.price,
              time: swing.time,
              thaiTime: swing.thaiTime,
              strength: ((lastLowerLow.price - swing.price) / lastLowerLow.price) * 100
            });
          }
          lastLowerLow = swing;
          currentTrend = 'Bearish';
        } else if (lastLowerLow && swing.price > lastLowerLow.price) {
          if (currentTrend === 'Bearish') {
            chochPoints.push({
              type: 'CHoCH',
              direction: 'Bullish',
              index: swing.index,
              price: swing.price,
              previousPrice: lastLowerLow.price,
              time: swing.time,
              thaiTime: swing.thaiTime,
              strength: ((swing.price - lastLowerLow.price) / lastLowerLow.price) * 100
            });
            currentTrend = 'Bullish';
          }
        }
      }
    }
  }

  return {
    swings: swings,
    bos: bosPoints,
    choch: chochPoints,
    currentTrend: currentTrend,
    lastSwing: swings.length > 0 ? swings[swings.length - 1] : null
  };
}

/**
 * หา Liquidity Zones
 */
function findLiquidityZones(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 20) {
    return [];
  }

  const liquidityZones = [];

  for (let i = 10; i < candleData.length; i++) {
    const candle = candleData[i];
    const open = parseFloat(candle.open);
    const close = parseFloat(candle.close);
    const high = parseFloat(candle.high);
    const low = parseFloat(candle.low);

    const candleSize = high - low;
    const bodySize = Math.abs(close - open);

    let upperWick, lowerWick;
    if (close >= open) {
      upperWick = high - close;
      lowerWick = open - low;
    } else {
      upperWick = high - open;
      lowerWick = close - low;
    }

    // Buy Side Liquidity (BSL)
    if (upperWick > bodySize * 2 && upperWick > candleSize * 0.5) {
      let isLocalHigh = true;
      for (let j = Math.max(0, i - 10); j < Math.min(candleData.length, i + 10); j++) {
        if (j !== i && parseFloat(candleData[j].high) > high) {
          isLocalHigh = false;
          break;
        }
      }

      if (isLocalHigh) {
        liquidityZones.push({
          type: 'BSL',
          direction: 'Buy Side Liquidity',
          index: i,
          price: high,
          zone: [high - upperWick * 0.3, high],
          time: parseInt(candle.epoch),
          thaiTime: toThaiTime(parseInt(candle.epoch)),
          wickSize: parseFloat(upperWick.toFixed(pipDecimal + 2)),
          wickSizePip: calculatePip(upperWick,wickSizePip: calculatePip(upperWick, pipDecimal),
          strength: Math.min(100, (upperWick / candleSize) * 100),
          description: 'มี Stop Loss ของคนขายอยู่เหนือราคานี้'
        });
      }
    }

    // Sell Side Liquidity (SSL)
    if (lowerWick > bodySize * 2 && lowerWick > candleSize * 0.5) {
      let isLocalLow = true;
      for (let j = Math.max(0, i - 10); j < Math.min(candleData.length, i + 10); j++) {
        if (j !== i && parseFloat(candleData[j].low) < low) {
          isLocalLow = false;
          break;
        }
      }

      if (isLocalLow) {
        liquidityZones.push({
          type: 'SSL',
          direction: 'Sell Side Liquidity',
          index: i,
          price: low,
          zone: [low, low + lowerWick * 0.3],
          time: parseInt(candle.epoch),
          thaiTime: toThaiTime(parseInt(candle.epoch)),
          wickSize: parseFloat(lowerWick.toFixed(pipDecimal + 2)),
          wickSizePip: calculatePip(lowerWick, pipDecimal),
          strength: Math.min(100, (lowerWick / candleSize) * 100),
          description: 'มี Stop Loss ของคนซื้ออยู่ใต้ราคานี้'
        });
      }
    }
  }

  return liquidityZones;
}

/**
 * วิเคราะห์ SMC แบบครบวงจร
 */
function analyzeSMC(candleData, pipDecimal = 5) {
  console.log('🔍 เริ่มวิเคราะห์ Smart Money Concepts...\n');

  // 1. หา FVG
  console.log('📊 กำลังหา Fair Value Gaps...');
  const fvgResults = findFairValueGaps(candleData, pipDecimal);
  const activeFVGs = fvgResults.filter(fvg => fvg.status === 'Active');

  // 2. หา Order Blocks
  console.log('📦 กำลังหา Order Blocks...');
  const orderBlocks = findOrderBlocks(candleData, pipDecimal);
  const activeOBs = orderBlocks.filter(ob => ob.status === 'Untested' || ob.status === 'Held');

  // 3. หา Market Structure
  console.log('🏗️  กำลังวิเคราะห์โครงสร้างตลาด...');
  const marketStructure = findMarketStructure(candleData, pipDecimal);

  // 4. หา Liquidity Zones
  console.log('💧 กำลังหา Liquidity Zones...');
  const liquidityZones = findLiquidityZones(candleData, pipDecimal);

  // 5. สรุปและทำนาย
  const currentPrice = parseFloat(candleData[candleData.length - 1].close);
  const lastCandle = candleData[candleData.length - 1];

  const nearestFVG = getNearestFVG(fvgResults, currentPrice);

  let nearestOB = null;
  let minOBDistance = Infinity;
  for (const ob of activeOBs) {
    const distance = Math.abs(currentPrice - ob.obMidPoint);
    if (distance < minOBDistance) {
      minOBDistance = distance;
      nearestOB = ob;
    }
  }

  // วิเคราะห์ทิศทางโดยรวม
  const bullishSignals = [];
  const bearishSignals = [];

  if (marketStructure.currentTrend === 'Bullish') {
    bullishSignals.push('โครงสร้างตลาดเป็น Bullish (Higher High, Higher Low)');
  } else if (marketStructure.currentTrend === 'Bearish') {
    bearishSignals.push('โครงสร้างตลาดเป็น Bearish (Lower Low, Lower High)');
  }

  const bullishFVGCount = activeFVGs.filter(fvg => fvg.fvgType === 'Bullish').length;
  const bearishFVGCount = activeFVGs.filter(fvg => fvg.fvgType === 'Bearish').length;

  if (bullishFVGCount > bearishFVGCount) {
    bullishSignals.push(`มี Bullish FVG ${bullishFVGCount} โซน มากกว่า Bearish FVG`);
  } else if (bearishFVGCount > bullishFVGCount) {
    bearishSignals.push(`มี Bearish FVG ${bearishFVGCount} โซน มากกว่า Bullish FVG`);
  }

  const bullishOBCount = activeOBs.filter(ob => ob.obType === 'Bullish').length;
  const bearishOBCount = activeOBs.filter(ob => ob.obType === 'Bearish').length;

  if (bullishOBCount > bearishOBCount) {
    bullishSignals.push(`มี Bullish Order Block ${bullishOBCount} โซน`);
  } else if (bearishOBCount > bullishOBCount) {
    bearishSignals.push(`มี Bearish Order Block ${bearishOBCount} โซน`);
  }

  const lastBOS = marketStructure.bos.length > 0 ?
    marketStructure.bos[marketStructure.bos.length - 1] : null;
  const lastCHoCH = marketStructure.choch.length > 0 ?
    marketStructure.choch[marketStructure.choch.length - 1] : null;

  if (lastBOS && lastBOS.direction === 'Bullish') {
    bullishSignals.push('BOS Bullish ล่าสุด - ทะลุ High เดิม');
  } else if (lastBOS && lastBOS.direction === 'Bearish') {
    bearishSignals.push('BOS Bearish ล่าสุด - ทะลุ Low เดิม');
  }

  if (lastCHoCH && lastCHoCH.direction === 'Bullish') {
    bullishSignals.push('CHoCH เปลี่ยนเป็น Bullish');
  } else if (lastCHoCH && lastCHoCH.direction === 'Bearish') {
    bearishSignals.push('CHoCH เปลี่ยนเป็น Bearish');
  }

  const biasScore = bullishSignals.length - bearishSignals.length;
  let overallBias = 'Neutral';
  let biasConfidence = 50;

  if (biasScore >= 2) {
    overallBias = 'Bullish';
    biasConfidence = Math.min(90, 50 + (biasScore * 10));
  } else if (biasScore <= -2) {
    overallBias = 'Bearish';
    biasConfidence = Math.min(90, 50 + (Math.abs(biasScore) * 10));
  }

  // สร้างคำแนะนำการเทรด
  let tradingRecommendation = {
    bias: overallBias,
    confidence: biasConfidence,
    action: 'รอสัญญาณ',
    entry: null,
    stopLoss: null,
    takeProfit: null,
    reasons: [],
    keyLevels: []
  };

  if (overallBias === 'Bullish') {
    tradingRecommendation.action = 'มองหาโอกาสซื้อ (Buy)';
    tradingRecommendation.reasons = bullishSignals;

    if (nearestFVG && nearestFVG.fvg.fvgType === 'Bullish') {
      tradingRecommendation.entry = nearestFVG.fvg.gapMidPoint;
      tradingRecommendation.stopLoss = nearestFVG.fvg.gapBottom - (nearestFVG.fvg.gapSize * 0.2);
      tradingRecommendation.takeProfit = currentPrice + (nearestFVG.fvg.gapSize * 3);
      tradingRecommendation.keyLevels.push({
        type: 'Bullish FVG',
        zone: [nearestFVG.fvg.gapBottom, nearestFVG.fvg.gapTop]
      });
    } else if (nearestOB && nearestOB.obType === 'Bullish') {
      tradingRecommendation.entry = nearestOB.obMidPoint;
      tradingRecommendation.stopLoss = nearestOB.obBottom - (nearestOB.obSize * 0.2);
      tradingRecommendation.takeProfit = currentPrice + (nearestOB.obSize * 3);
      tradingRecommendation.keyLevels.push({
        type: 'Bullish Order Block',
        zone: [nearestOB.obBottom, nearestOB.obTop]
      });
    }
  } else if (overallBias === 'Bearish') {
    tradingRecommendation.action = 'มองหาโอกาสขาย (Sell)';
    tradingRecommendation.reasons = bearishSignals;

    if (nearestFVG && nearestFVG.fvg.fvgType === 'Bearish') {
      tradingRecommendation.entry = nearestFVG.fvg.gapMidPoint;
      tradingRecommendation.stopLoss = nearestFVG.fvg.gapTop + (nearestFVG.fvg.gapSize * 0.2);
      tradingRecommendation.takeProfit = currentPrice - (nearestFVG.fvg.gapSize * 3);
      tradingRecommendation.keyLevels.push({
        type: 'Bearish FVG',
        zone: [nearestFVG.fvg.gapBottom, nearestFVG.fvg.gapTop]
      });
    } else if (nearestOB && nearestOB.obType === 'Bearish') {
      tradingRecommendation.entry = nearestOB.obMidPoint;
      tradingRecommendation.stopLoss = nearestOB.obTop + (nearestOB.obSize * 0.2);
      tradingRecommendation.takeProfit = currentPrice - (nearestOB.obSize * 3);
      tradingRecommendation.keyLevels.push({
        type: 'Bearish Order Block',
        zone: [nearestOB.obBottom, nearestOB.obTop]
      });
    }
  }

  // เพิ่ม Liquidity Zones
  const nearbyLiquidity = liquidityZones.filter(liq => {
    const distance = Math.abs(currentPrice - liq.price);
    const threshold = currentPrice * 0.01;
    return distance < threshold;
  });

  nearbyLiquidity.forEach(liq => {
    tradingRecommendation.keyLevels.push({
      type: liq.type,
      price: liq.price,
      description: liq.description
    });
  });

  console.log('✅ วิเคราะห์เสร็จสิ้น!\n');

  return {
    rawData: {
      fvgs: fvgResults,
      orderBlocks: orderBlocks,
      marketStructure: marketStructure,
      liquidityZones: liquidityZones
    },
    active: {
      fvgs: activeFVGs,
      orderBlocks: activeOBs,
      currentTrend: marketStructure.currentTrend,
      lastBOS: lastBOS,
      lastCHoCH: lastCHoCH
    },
    nearest: {
      fvg: nearestFVG,
      orderBlock: nearestOB ? {
        ob: nearestOB,
        distance: minOBDistance,
        distancePip: Math.round(minOBDistance * Math.pow(10, pipDecimal))
      } : null,
      liquidityZones: nearbyLiquidity
    },
    analysis: {
      currentPrice: currentPrice,
      currentPriceTime: toThaiTime(parseInt(lastCandle.epoch)),
      bias: overallBias,
      biasConfidence: biasConfidence,
      bullishSignals: bullishSignals,
      bearishSignals: bearishSignals,
      signalScore: biasScore
    },
    recommendation: tradingRecommendation,
    statistics: {
      totalFVGs: fvgResults.length,
      activeFVGs: activeFVGs.length,
      bullishFVGs: bullishFVGCount,
      bearishFVGs: bearishFVGCount,
      totalOrderBlocks: orderBlocks.length,
      activeOrderBlocks: activeOBs.length,
      bullishOBs: bullishOBCount,
      bearishOBs: bearishOBCount,
      totalSwingPoints: marketStructure.swings.length,
      bosCount: marketStructure.bos.length,
      chochCount: marketStructure.choch.length,
      liquidityZonesCount: liquidityZones.length
    }
  };
}

/**
 * แสดงรายงานการวิเคราะห์แบบละเอียด
 */
function printSMCReport(smcResult) {
  console.log('═══════════════════════════════════════════════════');
  console.log('       📈 SMART MONEY CONCEPTS ANALYSIS REPORT');
  console.log('═══════════════════════════════════════════════════\n');

  console.log('📊 ข้อมูลพื้นฐาน');
  console.log('─────────────────────────────────────────────────');
  console.log(`ราคาปัจจุบัน: ${smcResult.analysis.currentPrice}`);
  console.log(`เวลา: ${smcResult.analysis.currentPriceTime}`);
  console.log(`เทรนด์ตลาด: ${smcResult.active.currentTrend}`);
  console.log('');

  console.log('📈 สถิติ');
  console.log('─────────────────────────────────────────────────');
  console.log(`Fair Value Gaps: ${smcResult.statistics.activeFVGs}/${smcResult.statistics.totalFVGs} (Active/Total)`);
  console.log(`  - Bullish FVG: ${smcResult.statistics.bullishFVGs}`);
  console.log(`  - Bearish FVG: ${smcResult.statistics.bearishFVGs}`);
  console.log(`Order Blocks: ${smcResult.statistics.activeOrderBlocks}/${smcResult.statistics.totalOrderBlocks} (Active/Total)`);
  console.log(`  - Bullish OB: ${smcResult.statistics.bullishOBs}`);
  console.log(`  - Bearish OB: ${smcResult.statistics.bearishOBs}`);
  console.log(`Break of Structure: ${smcResult.statistics.bosCount}`);
  console.log(`Change of Character: ${smcResult.statistics.chochCount}`);
  console.log(`Liquidity Zones: ${smcResult.statistics.liquidityZonesCount}`);
  console.log('');

  console.log('🎯 โซนที่ใกล้ราคาปัจจุบัน');
  console.log('─────────────────────────────────────────────────');

  if (smcResult.nearest.fvg) {
    console.log(`FVG ที่ใกล้ที่สุด (${smcResult.nearest.fvg.distancePip} pips):`);
    console.log(`  ประเภท: ${smcResult.nearest.fvg.fvg.fvgType}`);
    console.log(`  โซน: ${smcResult.nearest.fvg.fvg.gapBottom} - ${smcResult.nearest.fvg.fvg.gapTop}`);
    console.log(`  สถานะ: ${smcResult.nearest.fvg.fvg.status}`);
  } else {
    console.log('FVG: ไม่พบโซนที่ใกล้');
  }

  if (smcResult.nearest.orderBlock) {
    console.log(`Order Block ที่ใกล้ที่สุด (${smcResult.nearest.orderBlock.distancePip} pips):`);
    console.log(`  ประเภท: ${smcResult.nearest.orderBlock.ob.obType}`);
    console.log(`  โซน: ${smcResult.nearest.orderBlock.ob.obBottom} - ${smcResult.nearest.orderBlock.ob.obTop}`);
    console.log(`  สถานะ: ${smcResult.nearest.orderBlock.ob.status}`);
    console.log(`  ความแรง: ${smcResult.nearest.orderBlock.ob.strength}%`);
  } else {
    console.log('Order Block: ไม่พบโซนที่ใกล้');
  }

  if (smcResult.nearest.liquidityZones.length > 0) {
    console.log(`Liquidity Zones ใกล้เคียง: ${smcResult.nearest.liquidityZones.length} โซน`);
    smcResult.nearest.liquidityZones.forEach((liq, idx) => {
      console.log(`  ${idx + 1}. ${liq.type} @ ${liq.price} - ${liq.description}`);
    });
  }
  console.log('');

  console.log('🔍 การวิเคราะห์');
  console.log('─────────────────────────────────────────────────');
  console.log(`Bias: ${smcResult.analysis.bias}`);
  console.log(`ความมั่นใจ: ${smcResult.analysis.biasConfidence}%`);
  console.log(`คะแนนสัญญาณ: ${smcResult.analysis.signalScore}`);
  console.log('');

  if (smcResult.analysis.bullishSignals.length > 0) {
    console.log('✅ สัญญาณ Bullish:');
    smcResult.analysis.bullishSignals.forEach((signal, idx) => {
      console.log(`  ${idx + 1}. ${signal}`);
    });
    console.log('');
  }

  if (smcResult.analysis.bearishSignals.length > 0) {
    console.log('❌ สัญญาณ Bearish:');
    smcResult.analysis.bearishSignals.forEach((signal, idx) => {
      console.log(`  ${idx + 1}. ${signal}`);
    });
    console.log('');
  }

  console.log('💡 คำแนะนำการเทรด');
  console.log('─────────────────────────────────────────────────');
  console.log(`การกระทำ: ${smcResult.recommendation.action}`);
  console.log(`ความมั่นใจ: ${smcResult.recommendation.confidence}%`);

  if (smcResult.recommendation.entry) {
    console.log(`\n📍 จุดเข้า (Entry): ${smcResult.recommendation.entry.toFixed(5)}`);
    console.log(`🛡️  Stop Loss: ${smcResult.recommendation.stopLoss.toFixed(5)}`);
    console.log(`🎯 Take Profit: ${smcResult.recommendation.takeProfit.toFixed(5)}`);

    const riskReward = Math.abs(
      (smcResult.recommendation.takeProfit - smcResult.recommendation.entry) /
      (smcResult.recommendation.entry - smcResult.recommendation.stopLoss)
    );
    console.log(`📊 Risk:Reward = 1:${riskReward.toFixed(2)}`);
  }

  if (smcResult.recommendation.keyLevels.length > 0) {
    console.log('\n🔑 ระดับราคาสำคัญ:');
    smcResult.recommendation.keyLevels.forEach((level, idx) => {
      if (level.zone) {
        console.log(`  ${idx + 1}. ${level.type}: ${level.zone[0].toFixed(5)} - ${level.zone[1].toFixed(5)}`);
      } else {
        console.log(`  ${idx + 1}. ${level.type} @ ${level.price.toFixed(5)}`);
        if (level.description) console.log(`     ${level.description}`);
      }
    });
  }

  if (smcResult.recommendation.reasons.length > 0) {
    console.log('\n📝 เหตุผล:');
    smcResult.recommendation.reasons.forEach((reason, idx) => {
      console.log(`  ${idx + 1}. ${reason}`);
    });
  }

  console.log('\n═══════════════════════════════════════════════════');
  console.log('                  END OF REPORT');
  console.log('═══════════════════════════════════════════════════\n');
}

/**
 * สรุปการวิเคราะห์ FVG ทั้งหมด
 */
function summarizeFVGAnalysis(fvgResults, currentPrice) {
  const activeFVGs = getActiveFVGs(fvgResults);
  const filledFVGs = fvgResults.filter(fvg => fvg.status === 'Filled');

  const bullishFVGs = activeFVGs.filter(fvg => fvg.fvgType === 'Bullish');
  const bearishFVGs = activeFVGs.filter(fvg => fvg.fvgType === 'Bearish');

  const nearest = getNearestFVG(fvgResults, currentPrice);

  let overallTrend = 'Neutral';
  if (bullishFVGs.length > bearishFVGs.length * 1.5) {
    overallTrend = 'Bullish';
  } else if (bearishFVGs.length > bullishFVGs.length * 1.5) {
    overallTrend = 'Bearish';
  }

  return {
    totalFVGs: fvgResults.length,
    activeFVGs: activeFVGs.length,
    filledFVGs: filledFVGs.length,
    bullishActiveFVGs: bullishFVGs.length,
    bearishActiveFVGs: bearishFVGs.length,
    overallTrend: overallTrend,
    nearestFVG: nearest,
    currentPrice: currentPrice,
    recommendation: nearest ? nearest.fvg.prediction.expectedAction : 'ไม่มี FVG ที่ใช้งานได้'
  };
}

// ====================================
// ตัวอย่างการใช้งาน
// ====================================

/*
// ข้อมูล Candle ตัวอย่าง
const rawCandles = [
  { epoch: 1697097600, open: "1.05234", close: "1.05289", high: "1.05312", low: "1.05201" },
  { epoch: 1697097660, open: "1.05289", close: "1.05456", high: "1.05478", low: "1.05280" },
  { epoch: 1697097720, open: "1.05456", close: "1.05445", high: "1.05467", low: "1.05334" },
  // ... เพิ่มข้อมูลเพิ่มเติม
];

// วิเคราะห์ SMC แบบครบวงจร
const smcResult = analyzeSMC(rawCandles, 5);

// แสดงรายงาน
printSMCReport(smcResult);

// เข้าถึงข้อมูลเฉพาะส่วน
console.log('Active FVGs:', smcResult.active.fvgs);
console.log('Recommendation:', smcResult.recommendation);
*/