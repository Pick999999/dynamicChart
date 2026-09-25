//clsExtra.js

// Mock Candle Data
const mockCandleData = [
  { epoch: 1696800000, open: 100, high: 105, low: 98, close: 103 },
  { epoch: 1696800060, open: 103, high: 107, low: 102, close: 106 },
  { epoch: 1696800120, open: 106, high: 108, low: 104, close: 105 },
  { epoch: 1696800180, open: 105, high: 106, low: 101, close: 102 },
  { epoch: 1696800240, open: 102, high: 104, low: 100, close: 103 },
  { epoch: 1696800300, open: 103, high: 109, low: 102, close: 108 },
  { epoch: 1696800360, open: 108, high: 110, low: 106, close: 107 },
  { epoch: 1696800420, open: 107, high: 109, low: 105, close: 109 },
  { epoch: 1696800480, open: 109, high: 111, low: 108, close: 110 },
  { epoch: 1696800540, open: 110, high: 112, low: 107, close: 108 },
  { epoch: 1696800600, open: 108, high: 109, low: 105, close: 106 },
  { epoch: 1696800660, open: 106, high: 108, low: 104, close: 107 },
  { epoch: 1696800720, open: 107, high: 113, low: 106, close: 112 },
  { epoch: 1696800780, open: 112, high: 115, low: 111, close: 114 },
  { epoch: 1696800840, open: 114, high: 116, low: 112, close: 113 },
  { epoch: 1696800900, open: 113, high: 114, low: 110, close: 111 },
  { epoch: 1696800960, open: 111, high: 113, low: 109, close: 112 },
  { epoch: 1696801020, open: 112, high: 114, low: 111, close: 111 },
  { epoch: 1696801080, open: 111, high: 112, low: 108, close: 109 },
  { epoch: 1696801140, open: 109, high: 111, low: 107, close: 110 }
];

// Calculate EMA
function calculateEMA999(data, period) {

  const k = 2 / (period + 1);
  const emaValues = [];

  // First EMA is SMA
  let sum = 0;
  for (let i = 0; i < period && i < data.length; i++) {
    sum += data[i].close;
  }
  emaValues.push(sum / Math.min(period, data.length));

  // Calculate remaining EMAs
  for (let i = period; i < data.length; i++) {
    const ema = data[i].close * k + emaValues[emaValues.length - 1] * (1 - k);
    emaValues.push(ema);
  }

  return emaValues;
}

// Format timestamp to HH:MM
function formatTime(epoch) {
  const date = new Date(epoch * 1000);
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${hours}:${minutes}`;
}

// Analyze candle body
function analyzeCandleBody(candle) {
  const { open, high, low, close } = candle;

  const fullCandleSize = high - low;
  const bodySize = Math.abs(close - open);
  const upperWickSize = high - Math.max(open, close);
  const lowerWickSize = Math.min(open, close) - low;

  const upperWickPercent = fullCandleSize > 0 ? (upperWickSize / fullCandleSize) * 100 : 0;
  const bodyPercent = fullCandleSize > 0 ? (bodySize / fullCandleSize) * 100 : 0;
  const lowerWickPercent = fullCandleSize > 0 ? (lowerWickSize / fullCandleSize) * 100 : 0;

  // Determine candle type
  let candleDesc = [];
  let codeCandleBody = '';

  // Doji detection (body < 10% of full candle)
  if (bodyPercent < 10) {
    candleDesc.push('Doji');
    codeCandleBody = 'DOJI';
  }

  // Hammer detection (small body at top, long lower wick)
  if (bodyPercent < 30 && lowerWickPercent > 60 && upperWickPercent < 10) {
    candleDesc.push('Hammer');
    codeCandleBody = 'HAMMER';
  }

  // Shooting Star (small body at bottom, long upper wick)
  if (bodyPercent < 30 && upperWickPercent > 60 && lowerWickPercent < 10) {
    candleDesc.push('ShootingStar');
    codeCandleBody = 'SHOOTING_STAR';
  }

  if (candleDesc.length === 0) {
    candleDesc.push('Normal');
    codeCandleBody = 'NORMAL';
  }

  return {
    fullCandleSize: fullCandleSize.toFixed(4),
    upperWickPercent: upperWickPercent.toFixed(2),
    bodyPercent: bodyPercent.toFixed(2),
    lowerWickPercent: lowerWickPercent.toFixed(2),
    codeCandleBody,
    candleDesc: candleDesc.join(', ')
  };
}

// Determine slope direction
function getSlopeDirection(slopeValue, threshold = 0.0001) {
  if (Math.abs(slopeValue) < threshold) return 'Parallel';
  return slopeValue > 0 ? 'Up' : 'Down';
}

// Determine turn type
function getTurnType(currentSlope, previousSlope, threshold = 0.0001) {
  const currentDir = getSlopeDirection(currentSlope, threshold);
  const previousDir = getSlopeDirection(previousSlope, threshold);

  if (currentDir === previousDir || previousDir === 'Parallel') return 'NoTurn';
  if (currentDir === 'Up' && previousDir === 'Down') return 'TurnUp';
  if (currentDir === 'Down' && previousDir === 'Up') return 'TurnDown';
  return 'NoTurn';
}

// Determine EMA position relative to candle
function getEMACutPosition(emaValue, candle) {
  const { open, high, low, close } = candle;
  const upperWick = high;
  const lowerWick = low;
  const bodyTop = Math.max(open, close);
  const bodyBottom = Math.min(open, close);

  if (emaValue > upperWick) return 'Above Upper Wick';
  if (emaValue >= bodyTop && emaValue <= upperWick) return 'Between Upper Wick and Body';
  if (emaValue >= bodyBottom && emaValue <= bodyTop) return 'Inside Body';
  if (emaValue >= lowerWick && emaValue <= bodyBottom) return 'Between Body and Lower Wick';
  return 'Below Lower Wick';
}

// Get candle color
function getCandleColor(candle) {
  if (candle.close > candle.open) return 'Green';
  if (candle.close < candle.open) return 'Red';
  return 'Equal';
}

// Main analysis function
function analyzeCandleData(candleData, emaShortPeriod , emaLongPeriod ) {
  // Validate input
  if (!candleData || candleData.length === 0) {
    console.error('No candle data provided');
    return [];
  }

  if (emaShortPeriod >= emaLongPeriod) {
    console.error('EMA Short period must be less than EMA Long period');
    return [];
  }

  alert('998989');

  //const emaShortValues = calculateEMA999(candleData, emaShortPeriod);
  const emaLongValues = clsIndy.calculateEMA(candleData, emaLongPeriod);
  const emaShortValues = clsIndy.calculateEMA(candleData, emaShortPeriod);

  console.log('Class Extra EMA',emaShortValues);


  const results = [];
  const startIndex = Math.max(emaShortPeriod, emaLongPeriod) - 1;

  for (let i = startIndex; i < candleData.length; i++) {
    const candle = candleData[i];

    // Calculate correct indices for EMA arrays
    const emaShortIndex = Math.min(i, emaShortValues.length - 1);
    const emaLongIndex = Math.min(i - (emaLongPeriod - emaShortPeriod), emaLongValues.length - 1);

    // Safety check
    if (emaShortIndex < 0 || emaLongIndex < 0 ||
        emaShortIndex >= emaShortValues.length ||
        emaLongIndex >= emaLongValues.length) {
      continue;
    }

    const currentEMAShort = emaShortValues[emaShortIndex];
    const currentEMALong = emaLongValues[emaLongIndex];
    const prevEMAShort = emaShortIndex > 0 ? emaShortValues[emaShortIndex - 1] : currentEMAShort;
    const prevEMALong = emaLongIndex > 0 ? emaLongValues[emaLongIndex - 1] : currentEMALong;

    // Additional safety check
    if (currentEMAShort === undefined || currentEMALong === undefined) {
      continue;
    }

    // Calculate slopes
    const emaShortSlopeValue = currentEMAShort - prevEMAShort;
    const emaLongSlopeValue = currentEMALong - prevEMALong;

    // Previous slopes for turn detection
    const prevPrevEMAShort = emaShortIndex > 1 ? emaShortValues[emaShortIndex - 2] : prevEMAShort;
    const prevPrevEMALong = emaLongIndex > 1 ? emaLongValues[emaLongIndex - 2] : prevEMALong;
    const prevEMAShortSlope = prevEMAShort - prevPrevEMAShort;
    const prevEMALongSlope = prevEMALong - prevPrevEMALong;

    // Check for EMA crossover
    let isEMACut = 'No';
    if (i > startIndex) {
      const prevResult = results[results.length - 1];
      if (prevResult) {
        if (prevResult.emaAbove === 'emaLong' && currentEMAShort > currentEMALong) {
          isEMACut = 'Short->Long';
        } else if (prevResult.emaAbove === 'emaShort' && currentEMALong > currentEMAShort) {
          isEMACut = 'Long->Short';
        }
      }
    }

    // Get previous colors
    const currentColor = getCandleColor(candle);
    const previousColorBack1 = i > 0 ? getCandleColor(candleData[i - 1]) : 'Equal';
    const previousColorBack2 = i > 1 ? getCandleColor(candleData[i - 2]) : 'Equal';

    // Check EMA conflict
    let emaConflict = 'No';
    if (currentEMAShort > currentEMALong && currentColor === 'Red') {
      emaConflict = 'EMA Short > Long but Candle is Red';
    } else if (currentEMAShort < currentEMALong && currentColor === 'Green') {
      emaConflict = 'EMA Short < Long but Candle is Green';
    }

    const analysis = {
      candleTime: candle.time ,
      candleDisplay: candle.time,
      candleBody: analyzeCandleBody(candle),
      emaShortValue: currentEMAShort.toFixed(4),
      emaShortSlopeValue: emaShortSlopeValue.toFixed(6),
      emaShortSlopeDirection: getSlopeDirection(emaShortSlopeValue),
      emaShortTurnType: getTurnType(emaShortSlopeValue, prevEMAShortSlope),
      emaShortCutPosition: getEMACutPosition(currentEMAShort, candle),
      emaLongValue: currentEMALong.toFixed(4),
      emaLongSlopeValue: emaLongSlopeValue.toFixed(6),
      emaLongSlopeDirection: getSlopeDirection(emaLongSlopeValue),
      emaLongTurnType: getTurnType(emaLongSlopeValue, prevEMALongSlope),
      emaLongCutPosition: getEMACutPosition(currentEMALong, candle),
      emaAbove: currentEMAShort > currentEMALong ? 'emaShort' : 'emaLong',
      isEMACut,
      macd: (currentEMAShort - currentEMALong).toFixed(4),
      currentColor,
      previousColorBack1,
      previousColorBack2,
      emaConflict
    };

    results.push(analysis);
  }

  return results;
}

// Export for use
// module.exports = { analyzeCandleData, mockCandleData };

/*
จาก ข้อมูลดิบ CandleData ที่เป็น array ของ Candle ที่ได้จาก deriv.com
ให้ใช้ mock data ได้
ต้องการ สร้างข้อมูล วิเคราะห์ดังนี้ โดย pure javascript และ สามารถ กำหนดค่า emaShort,emaLong ได้

{
CandleTime : CandleTimeStamp,
CandleDisplay : CandleTimeDisplay เช่น HH:MM,
candleBody {
  fullCandleSize : UpperWick-LowerWick,
  upperWickPercent : (UpperwickSize/fullCandleSize)*100
  BodyPercent      : (BodySize/fullCandleSize)*100%
  LowerWickPercent : (LowerwickSize/fullCandleSize)*100%
  CodeCandleBody : ""
  CandleDesc : "DOji,Hammer"
}
emaShortSlopeValue : (CurrentEMAShort - PreviousEMAShort),
emaShortSlopeDirection : "Up,Down,Pararell",
emaShortTurnType : "NoTurn,TurnUp,TurnDown",
emaShortCutPosition : เหนือ UpperWick,ระหว่าง UpperWick กับ open,Close,ระหว่าง OPen,Close กับ LowerWick,ต่ำกว่า LoweWick

emaLongSlopeValue : (CurrentEMALong - PreviousEMALong),
emaLongSlopeDirection : "Up,Down,Pararell",
emaLongTurnType : "NoTurn,TurnUp,TurnDown",
emaLongCutPosition : เหนือ UpperWick,ระหว่าง UpperWick กับ open,Close,ระหว่าง OPen,Close กับ LowerWick,ต่ำกว่า LoweWick,
emaAbove : emaShort or emaLong
isEMACut : Short->Long, Long-Short
macd : emaShortValue - emaLongValue,
previousColorBack1 : Green,Red,Equal
previousColorBack2 : Green,Red,Equal

emaConflict : emaShort > emaLong but Color is Red ,emaShort < emaLong but Color is Green

}



*/

