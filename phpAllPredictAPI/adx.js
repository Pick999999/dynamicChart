/**
 * คำนวณค่า ADX (Average Directional Index) จาก candle data
 * @param {Array} candleData - array ของ candle data จาก Deriv.com
 *   รูปแบบ: [timestamp, open, high, low, close, volume] หรือ {timestamp, open, high, low, close}
 * @param {number} period - ช่วงเวลาสำหรับคำนวณ ADX (default: 14)
 * @returns {Array} array ของค่า ADX, DI+, DI-
 */
function calculateADX(candleData, period = 14) {
    if (!candleData || candleData.length < period * 2) {
        console.error('ต้องมีข้อมูล candle อย่างน้อย', period * 2, 'periods');
        return [];
    }

    const result = [];
    const trueRanges = [];
    const plusDMs = [];
    const minusDMs = [];
    const plusDIs = [];
    const minusDIs = [];
    const dxValues = [];

    // แปลงข้อมูลให้เป็นรูปแบบเดียวกัน
    const normalizedData = candleData.map(candle => {
        if (Array.isArray(candle)) {
            return {
                timestamp: candle[0],
                open: candle[1],
                high: candle[2],
                low: candle[3],
                close: candle[4]
            };
        }
        return candle;
    });

    // คำนวณ True Range (TR), +DM, -DM สำหรับแต่ละ period
    for (let i = 1; i < normalizedData.length; i++) {
        const current = normalizedData[i];
        const previous = normalizedData[i - 1];

        // True Range = max(high-low, abs(high-prevClose), abs(low-prevClose))
        const tr = Math.max(
            current.high - current.low,
            Math.abs(current.high - previous.close),
            Math.abs(current.low - previous.close)
        );
        trueRanges.push(tr);

        // Directional Movement
        const upMove = current.high - previous.high;
        const downMove = previous.low - current.low;

        // +DM (Plus Directional Movement)
        let plusDM = 0;
        if (upMove > downMove && upMove > 0) {
            plusDM = upMove;
        }
        plusDMs.push(plusDM);

        // -DM (Minus Directional Movement)
        let minusDM = 0;
        if (downMove > upMove && downMove > 0) {
            minusDM = downMove;
        }
        minusDMs.push(minusDM);
    }

    // คำนวณ smoothed values สำหรับ ATR, +DM, -DM
    let atrSum = trueRanges.slice(0, period).reduce((sum, tr) => sum + tr, 0);
    let plusDMSum = plusDMs.slice(0, period).reduce((sum, dm) => sum + dm, 0);
    let minusDMSum = minusDMs.slice(0, period).reduce((sum, dm) => sum + dm, 0);

    for (let i = period; i < trueRanges.length; i++) {
        // Smoothed True Range (ATR)
        const smoothedTR = (atrSum * (period - 1) + trueRanges[i]) / period;

        // Smoothed +DM
        const smoothedPlusDM = (plusDMSum * (period - 1) + plusDMs[i]) / period;

        // Smoothed -DM
        const smoothedMinusDM = (minusDMSum * (period - 1) + minusDMs[i]) / period;

        // คำนวณ DI+ และ DI-
        const plusDI = smoothedTR !== 0 ? (smoothedPlusDM / smoothedTR) * 100 : 0;
        const minusDI = smoothedTR !== 0 ? (smoothedMinusDM / smoothedTR) * 100 : 0;

        plusDIs.push(plusDI);
        minusDIs.push(minusDI);

        // คำนวณ DX (Directional Index)
        const diSum = plusDI + minusDI;
        const dx = diSum !== 0 ? Math.abs(plusDI - minusDI) / diSum * 100 : 0;
        dxValues.push(dx);

        // อัปเดต sums สำหรับการคำนวณครั้งต่อไป
        atrSum = smoothedTR * period;
        plusDMSum = smoothedPlusDM * period;
        minusDMSum = smoothedMinusDM * period;
    }

    // คำนวณ ADX (เฉลี่ยของ DX)
    for (let i = 0; i < dxValues.length; i++) {
        if (i < period - 1) {
            // ยังไม่ครบ period สำหรับ ADX
            result.push({
                timestamp: normalizedData[i + period].timestamp,
                adx: null,
                plusDI: plusDIs[i] || null,
                minusDI: minusDIs[i] || null,
                dx: dxValues[i]
            });
        } else if (i === period - 1) {
            // ADX แรก = เฉลี่ยของ DX ใน period แรก
            const initialADX = dxValues.slice(0, period).reduce((sum, dx) => sum + dx, 0) / period;
            result.push({
                timestamp: normalizedData[i + period].timestamp,
                adx: initialADX,
                plusDI: plusDIs[i],
                minusDI: minusDIs[i],
                dx: dxValues[i]
            });
        } else {
            // ADX smoothed = (previousADX * (period-1) + currentDX) / period
            const previousADX = result[result.length - 1].adx;
            const smoothedADX = (previousADX * (period - 1) + dxValues[i]) / period;
            result.push({
                timestamp: normalizedData[i + period].timestamp,
                adx: smoothedADX,
                plusDI: plusDIs[i],
                minusDI: minusDIs[i],
                dx: dxValues[i]
            });
        }
    }

    return result;
}

/**
 * ฟังก์ชันช่วยสำหรับดึงค่า ADX ล่าสุด
 * @param {Array} candleData - ข้อมูล candle
 * @param {number} period - period สำหรับ ADX
 * @returns {Object|null} ค่า ADX ล่าสุด
 */
function getLatestADX(candleData, period = 14) {
    const adxData = calculateADX(candleData, period);
    if (adxData.length === 0) return null;

    // หาค่าล่าสุดที่ไม่เป็น null
    for (let i = adxData.length - 1; i >= 0; i--) {
        if (adxData[i].adx !== null) {
            return adxData[i];
        }
    }
    return null;
}

/**
 * ฟังก์ชันตีความค่า ADX
 * @param {number} adxValue - ค่า ADX
 * @returns {string} การตีความค่า ADX
 */
function interpretADX(adxValue) {
    if (adxValue >= 50) return 'Strong Trend';
    if (adxValue >= 25) return 'Trending';
    if (adxValue >= 20) return 'Weak Trend';
    return 'No Trend / Sideways';
}

/**
 * ฟังก์ชันหาสัญญาณจาก ADX และ DI
 * @param {Array} adxData - ผลลัพธ์จาก calculateADX
 * @param {number} lookback - จำนวน periods ที่จะดูย้อนหลัง
 * @returns {Object} สัญญาณและข้อมูลเพิ่มเติม
 */
function getADXSignal(adxData, lookback = 3) {
    if (adxData.length < lookback + 1) return { signal: 'INSUFFICIENT_DATA' };

    const latest = adxData[adxData.length - 1];
    if (!latest || latest.adx === null) return { signal: 'NO_DATA' };

    const signal = {
        timestamp: latest.timestamp,
        adx: latest.adx,
        plusDI: latest.plusDI,
        minusDI: latest.minusDI,
        trendStrength: interpretADX(latest.adx),
        signal: 'HOLD'
    };

    // ตรวจสอบทิศทางของเทรนด์
    if (latest.plusDI > latest.minusDI && latest.adx > 25) {
        signal.signal = 'BUY';
        signal.reason = '+DI > -DI และ ADX แสดงเทรนด์แข็งแกร่ง';
    } else if (latest.minusDI > latest.plusDI && latest.adx > 25) {
        signal.signal = 'SELL';
        signal.reason = '-DI > +DI และ ADX แสดงเทรนด์แข็งแกร่ง';
    } else if (latest.adx < 20) {
        signal.signal = 'SIDEWAYS';
        signal.reason = 'ADX ต่ำ ไม่มีเทรนด์ชัดเจน';
    }

    // ตรวจสอบการเปลี่ยนแปลงของ ADX
    if (adxData.length >= 2) {
        const previous = adxData[adxData.length - 2];
        if (previous && previous.adx !== null) {
            signal.adxChange = latest.adx - previous.adx;
            signal.isRising = signal.adxChange > 0;
        }
    }

    return signal;
}


/**
 * คำนวณค่า ADX (Average Directional Index) จาก candle data
 * @param {Array} candleData - array ของ candle data จาก Deriv.com
 *   รูปแบบ: [timestamp, open, high, low, close, volume] หรือ {timestamp, open, high, low, close}
 * @param {number} period - ช่วงเวลาสำหรับคำนวณ ADX (default: 14)
 * @returns {Array} array ของค่า ADX, DI+, DI-
 */
function calculateADX(candleData, period = 14) {
    if (!candleData || candleData.length < period * 2) {
        console.error('ต้องมีข้อมูล candle อย่างน้อย', period * 2, 'periods');
        return [];
    }

    const result = [];
    const trueRanges = [];
    const plusDMs = [];
    const minusDMs = [];
    const plusDIs = [];
    const minusDIs = [];
    const dxValues = [];

    // แปลงข้อมูลให้เป็นรูปแบบเดียวกัน
    const normalizedData = candleData.map(candle => {
        if (Array.isArray(candle)) {
            return {
                timestamp: candle[0],
                open: candle[1],
                high: candle[2],
                low: candle[3],
                close: candle[4]
            };
        }
        return candle;
    });

    // คำนวณ True Range (TR), +DM, -DM สำหรับแต่ละ period
    for (let i = 1; i < normalizedData.length; i++) {
        const current = normalizedData[i];
        const previous = normalizedData[i - 1];

        // True Range = max(high-low, abs(high-prevClose), abs(low-prevClose))
        const tr = Math.max(
            current.high - current.low,
            Math.abs(current.high - previous.close),
            Math.abs(current.low - previous.close)
        );
        trueRanges.push(tr);

        // Directional Movement
        const upMove = current.high - previous.high;
        const downMove = previous.low - current.low;

        // +DM (Plus Directional Movement)
        let plusDM = 0;
        if (upMove > downMove && upMove > 0) {
            plusDM = upMove;
        }
        plusDMs.push(plusDM);

        // -DM (Minus Directional Movement)
        let minusDM = 0;
        if (downMove > upMove && downMove > 0) {
            minusDM = downMove;
        }
        minusDMs.push(minusDM);
    }

    // คำนวณ smoothed values สำหรับ ATR, +DM, -DM
    let atrSum = trueRanges.slice(0, period).reduce((sum, tr) => sum + tr, 0);
    let plusDMSum = plusDMs.slice(0, period).reduce((sum, dm) => sum + dm, 0);
    let minusDMSum = minusDMs.slice(0, period).reduce((sum, dm) => sum + dm, 0);

    for (let i = period; i < trueRanges.length; i++) {
        // Smoothed True Range (ATR)
        const smoothedTR = (atrSum * (period - 1) + trueRanges[i]) / period;

        // Smoothed +DM
        const smoothedPlusDM = (plusDMSum * (period - 1) + plusDMs[i]) / period;

        // Smoothed -DM
        const smoothedMinusDM = (minusDMSum * (period - 1) + minusDMs[i]) / period;

        // คำนวณ DI+ และ DI-
        const plusDI = smoothedTR !== 0 ? (smoothedPlusDM / smoothedTR) * 100 : 0;
        const minusDI = smoothedTR !== 0 ? (smoothedMinusDM / smoothedTR) * 100 : 0;

        plusDIs.push(plusDI);
        minusDIs.push(minusDI);

        // คำนวณ DX (Directional Index)
        const diSum = plusDI + minusDI;
        const dx = diSum !== 0 ? Math.abs(plusDI - minusDI) / diSum * 100 : 0;
        dxValues.push(dx);

        // อัปเดต sums สำหรับการคำนวณครั้งต่อไป
        atrSum = smoothedTR * period;
        plusDMSum = smoothedPlusDM * period;
        minusDMSum = smoothedMinusDM * period;
    }

    // คำนวณ ADX (เฉลี่ยของ DX)
    for (let i = 0; i < dxValues.length; i++) {
        if (i < period - 1) {
            // ยังไม่ครบ period สำหรับ ADX
            result.push({
                timestamp: normalizedData[i + period].timestamp,
                adx: null,
                plusDI: plusDIs[i] || null,
                minusDI: minusDIs[i] || null,
                dx: dxValues[i]
            });
        } else if (i === period - 1) {
            // ADX แรก = เฉลี่ยของ DX ใน period แรก
            const initialADX = dxValues.slice(0, period).reduce((sum, dx) => sum + dx, 0) / period;
            result.push({
                timestamp: normalizedData[i + period].timestamp,
                adx: initialADX,
                plusDI: plusDIs[i],
                minusDI: minusDIs[i],
                dx: dxValues[i]
            });
        } else {
            // ADX smoothed = (previousADX * (period-1) + currentDX) / period
            const previousADX = result[result.length - 1].adx;
            const smoothedADX = (previousADX * (period - 1) + dxValues[i]) / period;
            result.push({
                timestamp: normalizedData[i + period].timestamp,
                adx: smoothedADX,
                plusDI: plusDIs[i],
                minusDI: minusDIs[i],
                dx: dxValues[i]
            });
        }
    }

    return result;
}

/**
 * คำนวณทิศทางของ ADX และความผันผวน
 * @param {Array} adxData - ผลลัพธ์จาก calculateADX
 * @param {number} volatilityPeriod - ช่วงเวลาสำหรับคำนวณความผันผวน
 * @returns {Array} ข้อมูล ADX พร้อม direction และ volatility
 */
function calculateADXDirection(adxData, volatilityPeriod = 5) {
    if (adxData.length < 2) return adxData;

    const result = [...adxData];
    const adxChanges = [];

    // คำนวณ direction และ change rate
    for (let i = 1; i < result.length; i++) {
        const current = result[i];
        const previous = result[i - 1];

        if (current.adx !== null && previous.adx !== null) {
            const adxChange = current.adx - previous.adx;
            const adxChangePercent = previous.adx !== 0 ? (adxChange / previous.adx) * 100 : 0;

            current.adxDirection = adxChange > 0 ? 'Up' : adxChange < 0 ? 'Down' : 'Flat';
            current.adxChange = adxChange;
            current.adxChangePercent = adxChangePercent;

            adxChanges.push(Math.abs(adxChange));
        } else {
            current.adxDirection = null;
            current.adxChange = null;
            current.adxChangePercent = null;
        }
    }

    // คำนวณความผันผวนของ ADX Direction
    for (let i = volatilityPeriod; i < result.length; i++) {
        const recentChanges = adxChanges.slice(i - volatilityPeriod, i);

        if (recentChanges.length === volatilityPeriod) {
            // Standard Deviation ของการเปลี่ยนแปลง ADX
            const mean = recentChanges.reduce((sum, change) => sum + change, 0) / recentChanges.length;
            const variance = recentChanges.reduce((sum, change) => sum + Math.pow(change - mean, 2), 0) / recentChanges.length;
            const standardDeviation = Math.sqrt(variance);

            // Average True Range ของ ADX (ความผันผวนแบบ ATR)
            const atr = recentChanges.reduce((sum, change) => sum + change, 0) / recentChanges.length;

            result[i].adxVolatility = {
                standardDeviation: standardDeviation,
                averageTrueRange: atr,
                volatilityLevel: getVolatilityLevel(standardDeviation),
                isHighVolatility: standardDeviation > mean * 1.5
            };

            // นับจำนวนการเปลี่ยนทิศทาง
            const directionChanges = countDirectionChanges(result.slice(i - volatilityPeriod, i + 1));
            result[i].directionStability = {
                changes: directionChanges,
                stability: getDirectionStability(directionChanges, volatilityPeriod)
            };
        } else {
            result[i].adxVolatility = null;
            result[i].directionStability = null;
        }
    }

    return result;
}

/**
 * นับจำนวนการเปลี่ยนทิศทางของ ADX
 */
function countDirectionChanges(dataSlice) {
    let changes = 0;
    for (let i = 1; i < dataSlice.length; i++) {
        if (dataSlice[i].adxDirection && dataSlice[i-1].adxDirection &&
            dataSlice[i].adxDirection !== dataSlice[i-1].adxDirection &&
            dataSlice[i].adxDirection !== 'Flat' && dataSlice[i-1].adxDirection !== 'Flat') {
            changes++;
        }
    }
    return changes;
}

/**
 * กำหนดระดับความผันผวน
 */
function getVolatilityLevel(volatility) {
    if (volatility > 2) return 'Very High';
    if (volatility > 1.5) return 'High';
    if (volatility > 1) return 'Medium';
    if (volatility > 0.5) return 'Low';
    return 'Very Low';
}

/**
 * กำหนดความเสถียรของทิศทาง
 */
function getDirectionStability(changes, period) {
    const changeRatio = changes / period;
    if (changeRatio > 0.6) return 'Very Unstable';
    if (changeRatio > 0.4) return 'Unstable';
    if (changeRatio > 0.2) return 'Moderate';
    return 'Stable';
}

/**
 * ฟังก์ชันช่วยสำหรับดึงค่า ADX ล่าสุด
 * @param {Array} candleData - ข้อมูล candle
 * @param {number} period - period สำหรับ ADX
 * @returns {Object|null} ค่า ADX ล่าสุด
 */
function getLatestADX(candleData, period = 14) {
    const adxData = calculateADX(candleData, period);
    if (adxData.length === 0) return null;

    // หาค่าล่าสุดที่ไม่เป็น null
    for (let i = adxData.length - 1; i >= 0; i--) {
        if (adxData[i].adx !== null) {
            return adxData[i];
        }
    }
    return null;
}

/**
 * ฟังก์ชันตีความค่า ADX
 * @param {number} adxValue - ค่า ADX
 * @returns {string} การตีความค่า ADX
 */
function interpretADX(adxValue) {
    if (adxValue >= 50) return 'Strong Trend';
    if (adxValue >= 25) return 'Trending';
    if (adxValue >= 20) return 'Weak Trend';
    return 'No Trend / Sideways';
}

/**
 * ฟังก์ชันหาสัญญาณจาก ADX และ DI
 * @param {Array} adxData - ผลลัพธ์จาก calculateADX
 * @param {number} lookback - จำนวน periods ที่จะดูย้อนหลัง
 * @returns {Object} สัญญาณและข้อมูลเพิ่มเติม
 */
function getADXSignal(adxData, lookback = 3) {
    if (adxData.length < lookback + 1) return { signal: 'INSUFFICIENT_DATA' };

    const latest = adxData[adxData.length - 1];
    if (!latest || latest.adx === null) return { signal: 'NO_DATA' };

    const signal = {
        timestamp: latest.timestamp,
        adx: latest.adx,
        plusDI: latest.plusDI,
        minusDI: latest.minusDI,
        trendStrength: interpretADX(latest.adx),
        signal: 'HOLD'
    };

    // ตรวจสอบทิศทางของเทรนด์
    if (latest.plusDI > latest.minusDI && latest.adx > 25) {
        signal.signal = 'BUY';
        signal.reason = '+DI > -DI และ ADX แสดงเทรนด์แข็งแกร่ง';
    } else if (latest.minusDI > latest.plusDI && latest.adx > 25) {
        signal.signal = 'SELL';
        signal.reason = '-DI > +DI และ ADX แสดงเทรนด์แข็งแกร่ง';
    } else if (latest.adx < 20) {
        signal.signal = 'SIDEWAYS';
        signal.reason = 'ADX ต่ำ ไม่มีเทรนด์ชัดเจน';
    }

    // ตรวจสอบการเปลี่ยนแปลงของ ADX
    if (adxData.length >= 2) {
        const previous = adxData[adxData.length - 2];
        if (previous && previous.adx !== null) {
            signal.adxChange = latest.adx - previous.adx;
            signal.isRising = signal.adxChange > 0;
        }
    }

    return signal;
}

/*
// ตัวอย่างการใช้งาน:

// ข้อมูล candle จาก Deriv (รูปแบบ array)
const derivCandleData = [
    [1703001600, 1.0850, 1.0875, 1.0845, 1.0860], // [timestamp, open, high, low, close]
    [1703001900, 1.0860, 1.0880, 1.0855, 1.0870],
    // ... ข้อมูลเพิ่มเติม (ต้องมีอย่างน้อย 30+ candles)
];

// คำนวณ ADX พื้นฐาน
const adxResult = calculateADX(derivCandleData, 14);

// เพิ่ม direction และ volatility analysis
const adxWithDirection = calculateADXDirection(adxResult, 5);
console.log('ADX with Direction:', adxWithDirection);

// ตัวอย่างผลลัพธ์:
// {
//   timestamp: 1703001600,
//   adx: 32.5,
//   plusDI: 28.3,
//   minusDI: 15.7,
//   adxDirection: 'Up',
//   adxChange: 2.3,
//   adxChangePercent: 7.6,
//   adxVolatility: {
//     standardDeviation: 1.8,
//     averageTrueRange: 1.2,
//     volatilityLevel: 'Medium',
//     isHighVolatility: false
//   },
//   directionStability: {
//     changes: 2,
//     stability: 'Moderate'
//   }
// }

// หาสัญญาณพร้อม direction analysis
const advancedSignal = getADXSignal(adxResult);
console.log('Advanced Signal:', advancedSignal);

// ตัวอย่างผลลัพธ์ Signal:
// {
//   signal: 'BUY',
//   adx: 32.5,
//   adxDirection: 'Up',
//   adxChange: 2.3,
//   adxChangePercent: 7.6,
//   trendConfirmation: 'STRENGTHENING',
//   volatility: {...},
//   warning: null,
//   reason: '+DI > -DI และ ADX แสดงเทรนด์แข็งแกร่ง'
// }

// ตัวอย่างการใช้งานเพื่อตรวจสอบคุณภาพสัญญาณ
function validateADXSignal(signal) {
    const warnings = [];

    if (signal.volatility && signal.volatility.isHighVolatility) {
        warnings.push('ADX มีความผันผวนสูง');
    }

    if (signal.directionStability && signal.directionStability.stability === 'Very Unstable') {
        warnings.push('ทิศทาง ADX ไม่เสถียร');
    }

    if (signal.adxDirection === 'Down' && signal.adx < 30) {
        warnings.push('ADX กำลังลดลงและอ่อนแรง');
    }

    return {
        isReliable: warnings.length === 0,
        warnings: warnings,
        confidence: calculateConfidence(signal)
    };
}

function calculateConfidence(signal) {
    let confidence = 50; // Base confidence

    // เพิ่ม confidence ตาม ADX strength
    if (signal.adx > 40) confidence += 20;
    else if (signal.adx > 25) confidence += 10;
    else if (signal.adx < 20) confidence -= 20;

    // เพิ่ม confidence ตาม direction
    if (signal.adxDirection === 'Up' && signal.adx > 25) confidence += 15;
    if (signal.adxDirection === 'Down' && signal.adx > 25) confidence -= 10;

    // ลด confidence ถ้ามี volatility สูง
    if (signal.volatility && signal.volatility.isHighVolatility) confidence -= 15;

    // ลด confidence ถ้า direction ไม่เสถียร
    if (signal.directionStability) {
        if (signal.directionStability.stability === 'Very Unstable') confidence -= 20;
        else if (signal.directionStability.stability === 'Unstable') confidence -= 10;
        else if (signal.directionStability.stability === 'Stable') confidence += 10;
    }

    return Math.max(0, Math.min(100, confidence));
}
*/


/*
// ตัวอย่างการใช้งาน:

// ข้อมูล candle จาก Deriv (รูปแบบ array)
const derivCandleData = [
    [1703001600, 1.0850, 1.0875, 1.0845, 1.0860], // [timestamp, open, high, low, close]
    [1703001900, 1.0860, 1.0880, 1.0855, 1.0870],
    // ... ข้อมูลเพิ่มเติม (ต้องมีอย่างน้อย 30+ candles)
];

// คำนวณ ADX พื้นฐาน
const adxResult = calculateADX(derivCandleData, 14);

// เพิ่ม direction และ volatility analysis
const adxWithDirection = calculateADXDirection(adxResult, 5);
console.log('ADX with Direction:', adxWithDirection);

// ตัวอย่างผลลัพธ์:
// {
//   timestamp: 1703001600,
//   adx: 32.5,
//   plusDI: 28.3,
//   minusDI: 15.7,
//   adxDirection: 'Up',
//   adxChange: 2.3,
//   adxChangePercent: 7.6,
//   adxVolatility: {
//     standardDeviation: 1.8,
//     averageTrueRange: 1.2,
//     volatilityLevel: 'Medium',
//     isHighVolatility: false
//   },
//   directionStability: {
//     changes: 2,
//     stability: 'Moderate'
//   }
// }

// หาสัญญาณพร้อม direction analysis
const advancedSignal = getADXSignal(adxResult);
console.log('Advanced Signal:', advancedSignal);

// ตัวอย่างผลลัพธ์ Signal:
// {
//   signal: 'BUY',
//   adx: 32.5,
//   adxDirection: 'Up',
//   adxChange: 2.3,
//   adxChangePercent: 7.6,
//   trendConfirmation: 'STRENGTHENING',
//   volatility: {...},
//   warning: null,
//   reason: '+DI > -DI และ ADX แสดงเทรนด์แข็งแกร่ง'
// }

// ตัวอย่างการใช้งานเพื่อตรวจสอบคุณภาพสัญญาณ
function validateADXSignal(signal) {
    const warnings = [];

    if (signal.volatility && signal.volatility.isHighVolatility) {
        warnings.push('ADX มีความผันผวนสูง');
    }

    if (signal.directionStability && signal.directionStability.stability === 'Very Unstable') {
        warnings.push('ทิศทาง ADX ไม่เสถียร');
    }

    if (signal.adxDirection === 'Down' && signal.adx < 30) {
        warnings.push('ADX กำลังลดลงและอ่อนแรง');
    }

    return {
        isReliable: warnings.length === 0,
        warnings: warnings,
        confidence: calculateConfidence(signal)
    };
}

function calculateConfidence(signal) {
    let confidence = 50; // Base confidence

    // เพิ่ม confidence ตาม ADX strength
    if (signal.adx > 40) confidence += 20;
    else if (signal.adx > 25) confidence += 10;
    else if (signal.adx < 20) confidence -= 20;

    // เพิ่ม confidence ตาม direction
    if (signal.adxDirection === 'Up' && signal.adx > 25) confidence += 15;
    if (signal.adxDirection === 'Down' && signal.adx > 25) confidence -= 10;

    // ลด confidence ถ้ามี volatility สูง
    if (signal.volatility && signal.volatility.isHighVolatility) confidence -= 15;

    // ลด confidence ถ้า direction ไม่เสถียร
    if (signal.directionStability) {
        if (signal.directionStability.stability === 'Very Unstable') confidence -= 20;
        else if (signal.directionStability.stability === 'Unstable') confidence -= 10;
        else if (signal.directionStability.stability === 'Stable') confidence += 10;
    }

    return Math.max(0, Math.min(100, confidence));
}
*/