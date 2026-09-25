/**
 * วิเคราะห์ข้อมูล Candle จาก Deriv.com
 * @param {Array} candleData - Array ของข้อมูล candle จาก Deriv API
 * @param {string} symbol - Symbol ของคู่เงิน
 * @param {number} pipDecimal - จำนวนทศนิยมสำหรับคำนวณ pip (default: 5)
 * @returns {Array} Array ของ CandleBodyAnalysis objects
 */
function analyzeCandleData999(candleData, symbol, pipDecimal = 5) {
  if (!candleData || candleData.length === 0) {
    return [];
  }

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
   * แปลงเปอร์เซ็นต์เป็นโค้ด (A-T)
   * 0-5% = A, 5-10% = B, ..., 95-100% = T
   */
  function percentToCode(percent) {
    const codes = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
                   'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
    const index = Math.min(Math.floor(percent / 5), 19);
    return codes[index];
  }

  const results = [];

  for (let i = 0; i < candleData.length; i++) {
    const candle = candleData[i];
    const open = parseFloat(candle.open);
    const close = parseFloat(candle.close);
    const high = parseFloat(candle.high);
    const low = parseFloat(candle.low);
    const epoch = parseInt(candle.epoch);

    // คำนวณขนาดต่างๆ
    const candleSize = high - low;
    const bodySize = Math.abs(close - open);
    const bodySizePip = calculatePip(bodySize, pipDecimal);

    // คำนวณ Wick
    let upperWick, lowerWick;
    if (close >= open) {
      // Green candle
      upperWick = high - close;
      lowerWick = open - low;
    } else {
      // Red candle
      upperWick = high - open;
      lowerWick = close - low;
    }

    // คำนวณเปอร์เซ็นต์ (ป้องกันการหารด้วย 0)
    let upperWickPercent = 0;
    let bodySizePercent = 0;
    let lowerWickPercent = 0;

    if (candleSize > 0) {
      upperWickPercent = (upperWick / candleSize) * 100;
      bodySizePercent = (bodySize / candleSize) * 100;
      lowerWickPercent = (lowerWick / candleSize) * 100;
    }

    // ปัดเศษให้ผลรวมได้ 100%
    const totalPercent = upperWickPercent + bodySizePercent + lowerWickPercent;
    if (totalPercent > 0 && totalPercent !== 100) {
      const adjustment = 100 / totalPercent;
      upperWickPercent *= adjustment;
      bodySizePercent *= adjustment;
      lowerWickPercent *= adjustment;
    }

    // กำหนดสี
    const color = getCandleColor(open, close);
    const previousColor = i > 0
      ? getCandleColor(
          parseFloat(candleData[i - 1].open),
          parseFloat(candleData[i - 1].close)
        )
      : null;

    // เปรียบเทียบกับแท่งก่อนหน้า
    let bodyComparison = null;
    if (i > 0) {
      const prevCandle = candleData[i - 1];
      const prevBodySize = Math.abs(parseFloat(prevCandle.close) - parseFloat(prevCandle.open));
      const prevBodySizePip = calculatePip(prevBodySize, pipDecimal);

      const pipDiff = bodySizePip - prevBodySizePip;
      let percentDiff = 0;
      let status = 'Equal';

      if (prevBodySizePip !== 0) {
        percentDiff = (pipDiff / prevBodySizePip) * 100;

        if (pipDiff > 0) {
          status = 'Larger';
        } else if (pipDiff < 0) {
          status = 'Smaller';
          percentDiff = Math.abs(percentDiff);
        }
      } else if (bodySizePip > 0) {
        status = 'Larger';
        percentDiff = 100;
      }

      bodyComparison = {
        previousBodyPip: prevBodySizePip,
        currentBodyPip: bodySizePip,
        pipDifference: Math.abs(pipDiff),
        status: status,
        percentDifference: parseFloat(percentDiff.toFixed(2))
      };
    }

    // สร้าง analysis object
    const analysis = {
      symbol: symbol,
      timeCandle: epoch,
      thaiTimeDisplay: toThaiTime(epoch),
      pip: calculatePip(close, pipDecimal),
      Color: color,
      PreviousColor: previousColor,
      CandleSize: parseFloat(candleSize.toFixed(pipDecimal + 2)),
      UpperWick: parseFloat(upperWick.toFixed(pipDecimal + 2)),
      BodySize: parseFloat(bodySize.toFixed(pipDecimal + 2)),
      BodySizePip: bodySizePip,
      LowerWick: parseFloat(lowerWick.toFixed(pipDecimal + 2)),
      UpperWickPercent: parseFloat(upperWickPercent.toFixed(2)),
      BodySizePercent: parseFloat(bodySizePercent.toFixed(2)),
      LowerWickPercent: parseFloat(lowerWickPercent.toFixed(2)),
      TotalPercent: parseFloat((upperWickPercent + bodySizePercent + lowerWickPercent).toFixed(2)),
      UpperWickCode: percentToCode(upperWickPercent),
      BodyCode: percentToCode(bodySizePercent),
      LowerWickCode: percentToCode(lowerWickPercent),
      BodyComparison: bodyComparison
    };

    results.push(analysis);
  }

  return results;
}


function sendCandleAnalysisToServer(analysisData,
	apiUrl = 'https://thepapers.in/phpAllPredictAPI/save-candle-analysis.php') {
  return new Promise((resolve, reject) => {
    // ตรวจสอบข้อมูล
    if (!analysisData || analysisData.length === 0) {
      reject(new Error('ไม่มีข้อมูลที่จะส่ง'));
      return;
    }

    // สร้าง XMLHttpRequest
    const xhr = new XMLHttpRequest();

    // ตั้งค่า timeout (30 วินาที)
    xhr.timeout = 30000;

    // เตรียมข้อมูลที่จะส่ง
    const payload = {
      data: analysisData,
      timestamp: Date.now()
    };

    // เปิด connection
    xhr.open('POST', apiUrl, true);

    // ตั้งค่า headers
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    xhr.setRequestHeader('Accept', 'application/json');

    // จัดการเมื่อได้รับ response
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          const response = JSON.parse(xhr.responseText);
          resolve(response);
        } catch (e) {
          reject(new Error('ไม่สามารถแปลง response เป็น JSON: ' + e.message));
        }
      } else {
        reject(new Error(`HTTP Error: ${xhr.status} - ${xhr.statusText}`));
      }
    };

    // จัดการ error
    xhr.onerror = function() {
      reject(new Error('เกิดข้อผิดพลาดในการเชื่อมต่อกับ Server'));
    };

    // จัดการ timeout
    xhr.ontimeout = function() {
      reject(new Error('การเชื่อมต่อหมดเวลา (Timeout)'));
    };

    // ส่งข้อมูล
    try {
      xhr.send(JSON.stringify(payload));
    } catch (e) {
      reject(new Error('ไม่สามารถส่งข้อมูล: ' + e.message));
    }
  });
}


// ตัวอย่างการใช้งาน:
/*
const rawCandles = [
  { epoch: 1697097600, open: "1.05234", close: "1.05289", high: "1.05312", low: "1.05201" },
  { epoch: 1697097660, open: "1.05289", close: "1.05267", high: "1.05298", low: "1.05245" },
  { epoch: 1697097720, open: "1.05267", close: "1.05310", high: "1.05325", low: "1.05260" }
];

const analysis = analyzeCandleData(rawCandles, "EURUSD", 5);

console.log(analysis[1].BodyComparison);
// Output: {
//   previousBodyPip: 55,
//   currentBodyPip: 22,
//   pipDifference: 33,
//   status: 'Smaller',
//   percentDifference: 60.00
// }

console.log(analysis[2].BodyComparison);
// Output: {
//   previousBodyPip: 22,
//   currentBodyPip: 43,
//   pipDifference: 21,
//   status: 'Larger',
//   percentDifference: 95.45
// }
*/