/**
 * ========================================
 * EARLY CHOPPY MARKET DETECTION
 * ตรวจจับ Choppy Market แต่เนิ่นๆ (2-3 แท่ง)
 * ========================================
 */

/**
 * ตรวจจับสัญญาณ Choppy Market แต่เนิ่นๆ
 * @param {Array} candleData - ข้อมูลแท่งเทียนทั้งหมด
 * @param {number} lookbackPeriod - จำนวนแท่งที่จะดูย้อนหลัง (default: 10)
 * @param {number} pipDecimal - ทศนิยม
 * @returns {Object} ผลการตรวจจับ
 */

function analyzeChoppyMarket(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 3) {
    return null;
  }

  // วิเคราะห์แต่ละแท่ง
  let redCount = 0;
  let greenCount = 0;
  let dojiCount = 0;
  let colorChanges = 0;
  let previousColor = null;

  const candles = [];

  for (let i = 0; i < candleData.length; i++) {
    const c = candleData[i];
    const open = parseFloat(c.open);
    const close = parseFloat(c.close);
    const high = parseFloat(c.high);
    const low = parseFloat(c.low);

    const color = close > open ? 'Green' : close < open ? 'Red' : 'Doji';
    const bodySize = Math.abs(close - open);
    const candleSize = high - low;
    const bodyPercent = candleSize > 0 ? (bodySize / candleSize) * 100 : 0;

    // นับสี
    if (color === 'Green') greenCount++;
    else if (color === 'Red') redCount++;
    else dojiCount++;

    // นับการสลับสี
    if (previousColor && color !== previousColor && color !== 'Doji') {
      colorChanges++;
    }

    candles.push({
      index: i,
      time: toThaiTime(c.epoch),
      open: open,
      high: high,
      low: low,
      close: close,
      color: color,
      bodySize: bodySize,
      bodyPercent: bodyPercent.toFixed(2),
      candleSize: candleSize,
      isWeakCandle: bodyPercent < 50
    });

    previousColor = color;
  }

  // คำนวณ High-Low Range
  const allHighs = candleData.map(c => parseFloat(c.high));
  const allLows = candleData.map(c => parseFloat(c.low));
  const rangeHigh = Math.max(...allHighs);
  const rangeLow = Math.min(...allLows);
  const totalRange = rangeHigh - rangeLow;
  const totalRangePip = calculatePip(totalRange, pipDecimal);

  // คำนวณ ATR (Average True Range) แบบง่าย
  let totalCandleSize = 0;
  for (let c of candles) {
    totalCandleSize += c.candleSize;
  }
  const avgCandleSize = totalCandleSize / candles.length;
  const avgCandleSizePip = calculatePip(avgCandleSize, pipDecimal);

  // คำนวณความถี่ในการสลับสี
  const colorChangeRate = (colorChanges / (candleData.length - 1)) * 100;

  // คำนวณ Body เฉลี่ย
  let totalBodyPercent = 0;
  for (let c of candles) {
    totalBodyPercent += parseFloat(c.bodyPercent);
  }
  const avgBodyPercent = totalBodyPercent / candles.length;

  // ตรวจจับภาวะตลาด
  let marketCondition = 'Normal';
  let choppyScore = 0;
  const reasons = [];

  // เกณฑ์การตรวจจับ Choppy Market

  // 1. การสลับสีบ่อย
  if (colorChangeRate > 60) {
    choppyScore += 30;
    reasons.push(`🔄 สลับสีบ่อยมาก ${colorChanges} ครั้งจาก ${candleData.length - 1} แท่ง (${colorChangeRate.toFixed(1)}%)`);
  } else if (colorChangeRate > 40) {
    choppyScore += 20;
    reasons.push(`🔄 สลับสีปานกลาง ${colorChanges} ครั้ง (${colorChangeRate.toFixed(1)}%)`);
  }

  // 2. Range แคบ
  if (totalRangePip < 30) {
    choppyScore += 25;
    reasons.push(`📏 Range แคบมาก ${totalRangePip} pips (${rangeLow.toFixed(3)} - ${rangeHigh.toFixed(3)})`);
  } else if (totalRangePip < 50) {
    choppyScore += 15;
    reasons.push(`📏 Range แคบ ${totalRangePip} pips`);
  }

  // 3. Body เล็ก (wick ยาว)
  if (avgBodyPercent < 40) {
    choppyScore += 25;
    reasons.push(`📊 Body เฉลี่ยเล็กมาก ${avgBodyPercent.toFixed(1)}% (wick ยาว)`);
  } else if (avgBodyPercent < 50) {
    choppyScore += 15;
    reasons.push(`📊 Body เฉลี่ยเล็ก ${avgBodyPercent.toFixed(1)}%`);
  }

  // 4. จำนวนสีใกล้เคียงกัน (ไม่มีทิศทางชัด)
  const colorBalance = Math.abs(greenCount - redCount);
  if (colorBalance <= 1) {
    choppyScore += 20;
    reasons.push(`⚖️ จำนวนสีเท่ากัน (Green: ${greenCount}, Red: ${redCount})`);
  } else if (colorBalance <= 2) {
    choppyScore += 10;
    reasons.push(`⚖️ จำนวนสีใกล้เคียง (Green: ${greenCount}, Red: ${redCount})`);
  }

  // สรุปภาวะตลาด
  if (choppyScore >= 70) {
    marketCondition = 'Very Choppy (Sideways แรง)';
  } else if (choppyScore >= 50) {
    marketCondition = 'Choppy (Sideways)';
  } else if (choppyScore >= 30) {
    marketCondition = 'Slightly Choppy';
  } else {
    marketCondition = 'Trending';
  }

  // คำแนะนำ
  let recommendation = '';
  if (choppyScore >= 50) {
    recommendation = '⚠️ ไม่แนะนำให้เทรด - ตลาด Choppy/Sideways มาก อาจถูก Stop Loss บ่อย';
  } else if (choppyScore >= 30) {
    recommendation = '⚡ ระมัดระวัง - ตลาดไม่ค่อยมีทิศทาง ควรรอสัญญาณชัดเจน';
  } else {
    recommendation = '✅ สามารถเทรดได้ - ตลาดมีทิศทางชัดเจน';
  }

  return {
    candles: candles,
    analysis: {
      totalCandles: candleData.length,
      greenCount: greenCount,
      redCount: redCount,
      dojiCount: dojiCount,
      colorChanges: colorChanges,
      colorChangeRate: parseFloat(colorChangeRate.toFixed(2)),
      rangeHigh: rangeHigh,
      rangeLow: rangeLow,
      totalRange: parseFloat(totalRange.toFixed(pipDecimal + 2)),
      totalRangePip: totalRangePip,
      avgCandleSize: parseFloat(avgCandleSize.toFixed(pipDecimal + 2)),
      avgCandleSizePip: avgCandleSizePip,
      avgBodyPercent: parseFloat(avgBodyPercent.toFixed(2)),
      choppyScore: choppyScore,
      marketCondition: marketCondition,
      reasons: reasons,
      recommendation: recommendation
    }
  };
}

/**
 * แสดงรายงานการวิเคราะห์ Choppy Market
 */
function printChoppyAnalysis(result) {
  displayResult('═══════════════════════════════════════════════════');
  displayResult('       🌊 CHOPPY MARKET ANALYSIS');
  displayResult('═══════════════════════════════════════════════════\n');

  const a = result.analysis;

  displayResult('📊 ข้อมูลทั่วไป:');
  displayResult(`   จำนวนแท่ง: ${a.totalCandles}`);
  displayResult(`   แท่งเขียว: ${a.greenCount}`);
  displayResult(`   แท่งแดง: ${a.redCount}`);
  displayResult(`   Doji: ${a.dojiCount}`);
  displayResult(`   การสลับสี: ${a.colorChanges} ครั้ง (${a.colorChangeRate}%)`);
  displayResult('');

  displayResult('📏 ข้อมูลราคา:');
  displayResult(`   High สุด: ${a.rangeHigh}`);
  displayResult(`   Low สุด: ${a.rangeLow}`);
  displayResult(`   Range รวม: ${a.totalRangePip} pips (${a.totalRange.toFixed(3)})`);
  displayResult(`   ขนาดแท่งเฉลี่ย: ${a.avgCandleSizePip} pips`);
  displayResult(`   Body เฉลี่ย: ${a.avgBodyPercent}%`);
  displayResult('');

  displayResult('🎯 การวินิจฉัย:');
  displayResult(`   คะแนน Choppy: ${a.choppyScore}/100`);
  displayResult(`   ภาวะตลาด: ${a.marketCondition}`);
  displayResult('');

  if (a.reasons.length > 0) {
    displayResult('🔍 เหตุผล:');
    a.reasons.forEach((reason, idx) => {
      displayResult(`   ${idx + 1}. ${reason}`);
    });
    displayResult('');
  }

  displayResult('💡 คำแนะนำ:');
  displayResult(`   ${a.recommendation}`);
  displayResult('');

  displayResult('📋 รายละเอียดแท่งเทียน:');
  result.candles.forEach((c, idx) => {
    const indicator = c.isWeakCandle ? '⚠️' : '✓';
    displayResult(`   ${idx + 1}. [${c.time}] ${c.color.padEnd(5)} - Body: ${c.bodyPercent}% ${indicator}`);
  });

  displayResult('\n═══════════════════════════════════════════════════\n');
}


function displayResult(msg) {

	 document.getElementById("resultDiv").innerHTML += msg + '<br>';



 } // end func

 function generateChoppyHTMLReport(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 3) {
    return '<p>ข้อมูลไม่เพียงพอ</p>';
  }

  const rows = [];
  let previousChoppyState = false;
  let choppyStartIndex = -1;
  let consecutiveChoppy = 0;

  // วิเคราะห์แต่ละแท่ง
  for (let i = 0; i < candleData.length; i++) {
    const subset = candleData.slice(0, i + 1);

    // ต้องมีอย่างน้อย 3 แท่งถึงจะวิเคราะห์ได้
    if (subset.length < 3) {
      const c = candleData[i];
      const color = parseFloat(c.close) > parseFloat(c.open) ? 'Green' :
                    parseFloat(c.close) < parseFloat(c.open) ? 'Red' : 'Doji';

      rows.push({
        index: i + 1,
        time: toThaiTime(c.epoch),
        open: parseFloat(c.open),
        high: parseFloat(c.high),
        low: parseFloat(c.low),
        close: parseFloat(c.close),
        color: color,
        score: 0,
        status: 'N/A',
        trend: 'รอข้อมูล',
        warning: '-',
        statusIcon: '⚪',
        rowClass: 'neutral',
        isChoppyZone: false,
        zoneStatus: '-'
      });
      continue;
    }

    const result = detectEarlyChoppy(subset, 10, pipDecimal);
    const c = candleData[i];
    const color = parseFloat(c.close) > parseFloat(c.open) ? 'Green' :
                  parseFloat(c.close) < parseFloat(c.open) ? 'Red' : 'Doji';

    // ตรวจสอบสถานะ Choppy
    const isChoppy = result.earlyWarningScore >= 60;
    const isWarning = result.earlyWarningScore >= 40 && result.earlyWarningScore < 60;

    // ตรวจสอบการเข้า/ออก Choppy Zone
    let zoneStatus = '-';
    let zoneStatusClass = '';

    if (isChoppy && !previousChoppyState) {
      zoneStatus = '🚨 เข้าสู่ Choppy Zone!';
      zoneStatusClass = 'zone-enter';
      choppyStartIndex = i;
      consecutiveChoppy = 1;
    } else if (isChoppy && previousChoppyState) {
      consecutiveChoppy++;
      zoneStatus = `⚠️ อยู่ใน Choppy (${consecutiveChoppy} แท่ง)`;
      zoneStatusClass = 'zone-inside';
    } else if (!isChoppy && previousChoppyState) {
      zoneStatus = '✅ ออกจาก Choppy Zone';
      zoneStatusClass = 'zone-exit';
      consecutiveChoppy = 0;
    } else if (isWarning) {
      zoneStatus = '⚡ มีสัญญาณเตือน';
      zoneStatusClass = 'zone-warning';
    }

    // กำหนดสถานะและไอคอน
    let statusIcon = '🟢';
    let statusText = 'ปกติ';
    let rowClass = 'safe';
    let trendText = 'เทรดได้';

    if (result.earlyWarningScore >= 80) {
      statusIcon = '🔴';
      statusText = 'อันตรายมาก';
      rowClass = 'critical';
      trendText = 'หยุดเทรด!';
    } else if (result.earlyWarningScore >= 60) {
      statusIcon = '🟠';
      statusText = 'อันตราย';
      rowClass = 'danger';
      trendText = 'ระวัง! ลด Position';
    } else if (result.earlyWarningScore >= 40) {
      statusIcon = '🟡';
      statusText = 'เสี่ยงปานกลาง';
      rowClass = 'warning';
      trendText = 'ระมัดระวัง';
    } else if (result.earlyWarningScore >= 20) {
      statusIcon = '🟢';
      statusText = 'เสี่ยงต่ำ';
      rowClass = 'low-risk';
      trendText = 'เทรดได้ แต่สังเกต';
    }

    // เตรียมคำเตือนสั้นๆ
    let warningText = result.warnings.length > 0 ? result.warnings[0] : '-';
    warningText = warningText.replace(/🔄|📏|📊|⚖️|⚠️|🔁|🌊/g, '').substring(0, 50);

    rows.push({
      index: i + 1,
      time: toThaiTime(c.epoch),
      open: parseFloat(c.open),
      high: parseFloat(c.high),
      low: parseFloat(c.low),
      close: parseFloat(c.close),
      color: color,
      score: result.earlyWarningScore,
      status: statusText,
      trend: trendText,
      warning: warningText,
      statusIcon: statusIcon,
      rowClass: rowClass,
      isChoppyZone: isChoppy,
      zoneStatus: zoneStatus,
      zoneStatusClass: zoneStatusClass
    });

    previousChoppyState = isChoppy;
  }

  // สร้าง HTML
  return generateHTML(rows, candleData, pipDecimal);
}

/**
 * บันทึก HTML ลงไฟล์
 */
function saveHTMLReport(candleData, filename = 'choppy-report.html', pipDecimal = 5) {
  const html = generateChoppyHTMLReport(candleData, pipDecimal);

  // สำหรับ Node.js
  if (typeof require !== 'undefined') {
    const fs = require('fs');
    fs.writeFileSync(filename, html, 'utf8');
    displayResult(`✅ บันทึกรายงานที่ ${filename}`);
  } else {
    // สำหรับ Browser - ดาวน์โหลดไฟล์
    const blob = new Blob([html], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
    displayResult(`✅ ดาวน์โหลดรายงาน ${filename}`);
  }
}

/**
 * สร้าง HTML
 */
function generateHTML(rows, candleData, pipDecimal) {
  const fullAnalysis = analyzeChoppyMarket(candleData, pipDecimal);
  const lastResult = detectEarlyChoppy(candleData, 10, pipDecimal);

  // นับสถานะต่างๆ
  const criticalCount = rows.filter(r => r.score >= 80).length;
  const dangerCount = rows.filter(r => r.score >= 60 && r.score < 80).length;
  const warningCount = rows.filter(r => r.score >= 40 && r.score < 60).length;
  const safeCount = rows.filter(r => r.score < 40).length;

  const html = `

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choppy Market Detection Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }

        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card h3 {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .summary-card .label {
            font-size: 0.85em;
            color: #999;
        }

        .critical { color: #dc3545; }
        .danger { color: #fd7e14; }
        .warning { color: #ffc107; }
        .safe { color: #28a745; }

        .current-status {
            padding: 30px;
            text-align: center;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .current-status h2 {
            font-size: 2em;
            margin-bottom: 15px;
        }

        .current-status .status-badge {
            display: inline-block;
            padding: 15px 30px;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            font-size: 1.5em;
            margin: 10px;
            backdrop-filter: blur(10px);
        }

        .table-container {
            padding: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        th {
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9em;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .row-critical {
            background: rgba(220, 53, 69, 0.1) !important;
            border-left: 4px solid #dc3545;
        }

        .row-danger {
            background: rgba(253, 126, 20, 0.1) !important;
            border-left: 4px solid #fd7e14;
        }

        .row-warning {
            background: rgba(255, 193, 7, 0.1) !important;
            border-left: 4px solid #ffc107;
        }

        .row-low-risk {
            background: rgba(40, 167, 69, 0.05) !important;
        }

        .row-safe {
            background: white;
        }

        .color-green {
            color: #28a745;
            font-weight: bold;
        }

        .color-red {
            color: #dc3545;
            font-weight: bold;
        }

        .color-doji {
            color: #6c757d;
            font-weight: bold;
        }

        .score-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85em;
        }

        .score-critical {
            background: #dc3545;
            color: white;
        }

        .score-danger {
            background: #fd7e14;
            color: white;
        }

        .score-warning {
            background: #ffc107;
            color: #000;
        }

        .score-low {
            background: #28a745;
            color: white;
        }

        .score-safe {
            background: #17a2b8;
            color: white;
        }

        .zone-enter {
            background: rgba(220, 53, 69, 0.2);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #dc3545;
        }

        .zone-inside {
            background: rgba(253, 126, 20, 0.2);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fd7e14;
        }

        .zone-exit {
            background: rgba(40, 167, 69, 0.2);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #28a745;
        }

        .zone-warning {
            background: rgba(255, 193, 7, 0.2);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #856404;
        }

        .legend {
            padding: 20px 30px;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .legend-icon {
            font-size: 1.5em;
        }

        @media (max-width: 768px) {
            .summary {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.8em;
            }

            th, td {
                padding: 8px 5px;
            }
        }

        .footer {
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🌊 Choppy Market Detection Report</h1>
            <p>รายงานการตรวจจับภาวะตลาด Choppy/Sideways แบบละเอียด</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary">
            <div class="summary-card">
                <h3>จำนวนแท่งทั้งหมด</h3>
                <div class="value">${rows.length}</div>
                <div class="label">แท่งเทียน</div>
            </div>
            <div class="summary-card">
                <h3>สถานะปัจจุบัน</h3>
                <div class="value ${lastResult.alertLevel.includes('Critical') ? 'critical' :
                                     lastResult.alertLevel.includes('High') ? 'danger' :
                                     lastResult.alertLevel.includes('Medium') ? 'warning' : 'safe'}">
                    ${lastResult.earlyWarningScore}
                </div>
                <div class="label">คะแนน / 100</div>
            </div>
            <div class="summary-card">
                <h3>แท่งอันตราย</h3>
                <div class="value critical">${criticalCount + dangerCount}</div>
                <div class="label">🔴 Critical: ${criticalCount} | 🟠 Danger: ${dangerCount}</div>
            </div>
            <div class="summary-card">
                <h3>แท่งปกติ</h3>
                <div class="value safe">${safeCount}</div>
                <div class="label">ปลอดภัย</div>
            </div>
            <div class="summary-card">
                <h3>Range</h3>
                <div class="value">${fullAnalysis.analysis.totalRangePip}</div>
                <div class="label">pips</div>
            </div>
            <div class="summary-card">
                <h3>การสลับสี</h3>
                <div class="value">${fullAnalysis.analysis.colorChangeRate}%</div>
                <div class="label">${fullAnalysis.analysis.colorChanges} ครั้ง</div>
            </div>
        </div>

        <!-- Current Status -->
        <div class="current-status">
            <h2>${lastResult.alertColor} สถานะปัจจุบัน</h2>
            <div class="status-badge">
                ${lastResult.alertLevel}
            </div>
            <div class="status-badge">
                ${lastResult.recommendation}
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>เวลา</th>
                        <th>Open</th>
                        <th>High</th>
                        <th>Low</th>
                        <th>Close</th>
                        <th>สี</th>
                        <th>คะแนน</th>
                        <th>สถานะ</th>
                        <th>แนวโน้ม</th>
                        <th>สัญญาณเตือน</th>
                        <th>Choppy Zone</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map(row => `
                    <tr class="row-${row.rowClass}">
                        <td><strong>${row.index}</strong></td>
                        <td>${row.time}</td>
                        <td>${row.open.toFixed(3)}</td>
                        <td>${row.high.toFixed(3)}</td>
                        <td>${row.low.toFixed(3)}</td>
                        <td>${row.close.toFixed(3)}</td>
                        <td class="color-${row.color.toLowerCase()}">${row.color}</td>
                        <td>
                            <span class="score-badge score-${
                                row.score >= 80 ? 'critical' :
                                row.score >= 60 ? 'danger' :
                                row.score >= 40 ? 'warning' :
                                row.score >= 20 ? 'low' : 'safe'
                            }">
                                ${row.score}
                            </span>
                        </td>
                        <td>${row.statusIcon} ${row.status}</td>
                        <td>${row.trend}</td>
                        <td style="font-size: 0.85em;">${row.warning}</td>
                        <td>
                            ${row.zoneStatus !== '-' ?
                              `<span class="${row.zoneStatusClass}">${row.zoneStatus}</span>` :
                              '-'}
                        </td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="legend">
            <div class="legend-item">
                <span class="legend-icon">🔴</span>
                <span>อันตรายมาก (≥80)</span>
            </div>
            <div class="legend-item">
                <span class="legend-icon">🟠</span>
                <span>อันตราย (60-79)</span>
            </div>
            <div class="legend-item">
                <span class="legend-icon">🟡</span>
                <span>เสี่ยงปานกลาง (40-59)</span>
            </div>
            <div class="legend-item">
                <span class="legend-icon">🟢</span>
                <span>ปกติ (&lt;40)</span>
            </div>
            <div class="legend-item">
                <span class="legend-icon">🚨</span>
                <span>เข้าสู่ Choppy Zone</span>
            </div>
            <div class="legend-item">
                <span class="legend-icon">✅</span>
                <span>ออกจาก Choppy Zone</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>📊 รายงานสร้างเมื่อ: ${new Date().toLocaleString('th-TH')}</p>
            <p>⚠️ คำเตือน: ข้อมูลนี้ใช้สำหรับการวิเคราะห์เท่านั้น ไม่ใช่คำแนะนำในการลงทุน</p>
        </div>
    </div>
</body>
</html>
  `;

  return html;
}

/**
 * แสดง HTML ใน Browser
 */
function displayHTMLReport(candleData, pipDecimal = 5) {
  const html = generateChoppyHTMLReport(candleData, pipDecimal);

  // เปิดในหน้าต่างใหม่
  const newWindow = window.open('', '_blank');
  newWindow.document.write(html);
  newWindow.document.close();
}

function detectEarlyChoppy(candleData, lookbackPeriod = 10, pipDecimal = 5) {
  if (!candleData || candleData.length < 3) {
    return null;
  }

  const recentCandles = candleData.slice(-lookbackPeriod);
  const last3Candles = candleData.slice(-3);
  const last5Candles = candleData.slice(-5);

  // ========== วิเคราะห์ 3 แท่งล่าสุด ==========

  const analysis3 = analyzeWindow(last3Candles, pipDecimal);
  const analysis5 = analyzeWindow(last5Candles, pipDecimal);
  const analysisAll = analyzeWindow(recentCandles, pipDecimal);

  // คำนวณคะแนนความเสี่ยง Choppy
  let earlyWarningScore = 0;
  const warnings = [];

  // === เกณฑ์ที่ 1: การสลับสีใน 3 แท่งล่าสุด ===
  if (analysis3.colorChanges === 2) {
    earlyWarningScore += 35;
    warnings.push(`🔄 สลับสีทั้ง 3 แท่ง (${analysis3.colorSequence})`);
  } else if (analysis3.colorChanges === 1) {
    earlyWarningScore += 15;
    warnings.push(`🔄 สลับสี 1 ครั้งใน 3 แท่ง`);
  }

  // === เกณฑ์ที่ 2: Range แคบใน 3 แท่งล่าสุด ===
  if (analysis3.rangePip < 20) {
    earlyWarningScore += 30;
    warnings.push(`📏 Range 3 แท่งแคบมาก (${analysis3.rangePip} pips)`);
  } else if (analysis3.rangePip < 35) {
    earlyWarningScore += 20;
    warnings.push(`📏 Range 3 แท่งแคบ (${analysis3.rangePip} pips)`);
  }

  // === เกณฑ์ที่ 3: Body เล็ก (Wick ยาว) ===
  if (analysis3.avgBodyPercent < 40) {
    earlyWarningScore += 25;
    warnings.push(`📊 Body เล็กมาก ${analysis3.avgBodyPercent.toFixed(1)}% (Wick ยาว - Rejection แรง)`);
  } else if (analysis3.avgBodyPercent < 50) {
    earlyWarningScore += 15;
    warnings.push(`📊 Body เล็ก ${analysis3.avgBodyPercent.toFixed(1)}%`);
  }

  // === เกณฑ์ที่ 4: แท่งล่าสุดมี Upper และ Lower Wick ยาว ===
  const lastCandle = last3Candles[last3Candles.length - 1];
  const lastOpen = parseFloat(lastCandle.open);
  const lastClose = parseFloat(lastCandle.close);
  const lastHigh = parseFloat(lastCandle.high);
  const lastLow = parseFloat(lastCandle.low);

  let upperWick, lowerWick;
  if (lastClose >= lastOpen) {
    upperWick = lastHigh - lastClose;
    lowerWick = lastOpen - lastLow;
  } else {
    upperWick = lastHigh - lastOpen;
    lowerWick = lastClose - lastLow;
  }

  const lastCandleSize = lastHigh - lastLow;
  const upperWickPercent = lastCandleSize > 0 ? (upperWick / lastCandleSize) * 100 : 0;
  const lowerWickPercent = lastCandleSize > 0 ? (lowerWick / lastCandleSize) * 100 : 0;

  // Doji-like (Upper และ Lower wick ยาวทั้ง 2 ฝั่ง)
  if (upperWickPercent > 35 && lowerWickPercent > 35) {
    earlyWarningScore += 20;
    warnings.push(`⚠️ แท่งล่าสุดเป็น Doji-like (Wick 2 ฝั่งยาว U:${upperWickPercent.toFixed(1)}% L:${lowerWickPercent.toFixed(1)}%)`);
  }

  // === เกณฑ์ที่ 5: ตรวจสอบแนวโน้มใน 5 แท่ง ===
  if (analysis5.colorBalance <= 1) {
    earlyWarningScore += 15;
    warnings.push(`⚖️ ไม่มีทิศทางชัด 5 แท่ง (G:${analysis5.greenCount} R:${analysis5.redCount})`);
  }

  // === เกณฑ์ที่ 6: ราคาวนเวียนในโซนแคบ ===
  if (analysis5.rangePip < 40) {
    earlyWarningScore += 15;
    warnings.push(`🔁 ราคาวนเวียนใน 5 แท่ง (${analysis5.rangePip} pips)`);
  }

  // === เกณฑ์ที่ 7: อัตราการสลับสีสูงใน 10 แท่ง ===
  const colorChangeRate10 = (analysisAll.colorChanges / (analysisAll.totalCandles - 1)) * 100;
  if (colorChangeRate10 > 60) {
    earlyWarningScore += 20;
    warnings.push(`🌊 ประวัติสลับสีบ่อย 10 แท่ง (${colorChangeRate10.toFixed(1)}%)`);
  }

  // สรุประดับเตือน
  let alertLevel = 'Safe';
  let alertColor = '🟢';
  let recommendation = 'ปกติ - สามารถเทรดได้';

  if (earlyWarningScore >= 80) {
    alertLevel = 'Critical - กำลังเข้า Choppy!';
    alertColor = '🔴';
    recommendation = '🛑 หยุดเทรดทันที! กำลังเข้าสู่ Choppy Market';
  } else if (earlyWarningScore >= 60) {
    alertLevel = 'High Risk - มีสัญญาณ Choppy';
    alertColor = '🟠';
    recommendation = '⚠️ ระวัง! มีสัญญาณเข้า Choppy - ลด Position หรือพักเทรด';
  } else if (earlyWarningScore >= 40) {
    alertLevel = 'Medium Risk - เริ่มมีสัญญาณ';
    alertColor = '🟡';
    recommendation = '⚡ ระมัดระวัง - เริ่มมีสัญญาณ Choppy ใช้ SL แน่น';
  } else if (earlyWarningScore >= 20) {
    alertLevel = 'Low Risk - มีสัญญาณเล็กน้อย';
    alertColor = '🟢';
    recommendation = '✓ ยังเทรดได้ แต่สังเกตอาการ';
  }

  return {
    alertLevel: alertLevel,
    alertColor: alertColor,
    earlyWarningScore: earlyWarningScore,
    warnings: warnings,
    recommendation: recommendation,
    analysis: {
      last3Candles: analysis3,
      last5Candles: analysis5,
      last10Candles: analysisAll,
      lastCandleWicks: {
        upperWickPercent: parseFloat(upperWickPercent.toFixed(2)),
        lowerWickPercent: parseFloat(lowerWickPercent.toFixed(2)),
        isDoji: upperWickPercent > 35 && lowerWickPercent > 35
      }
    },
    nextAction: getNextAction(earlyWarningScore)
  };
}

/**
 * วิเคราะห์หน้าต่างเทียน
 */
function analyzeWindow(candles, pipDecimal) {
  let greenCount = 0;
  let redCount = 0;
  let dojiCount = 0;
  let colorChanges = 0;
  let previousColor = null;
  const colors = [];

  let totalBodyPercent = 0;

  const highs = [];
  const lows = [];

  for (let c of candles) {
    const open = parseFloat(c.open);
    const close = parseFloat(c.close);
    const high = parseFloat(c.high);
    const low = parseFloat(c.low);

    highs.push(high);
    lows.push(low);

    const color = close > open ? 'Green' : close < open ? 'Red' : 'Doji';
    colors.push(color[0]);

    if (color === 'Green') greenCount++;
    else if (color === 'Red') redCount++;
    else dojiCount++;

    if (previousColor && color !== previousColor && color !== 'Doji') {
      colorChanges++;
    }

    const candleSize = high - low;
    const bodySize = Math.abs(close - open);
    const bodyPercent = candleSize > 0 ? (bodySize / candleSize) * 100 : 0;
    totalBodyPercent += bodyPercent;

    previousColor = color;
  }

  const rangeHigh = Math.max(...highs);
  const rangeLow = Math.min(...lows);
  const range = rangeHigh - rangeLow;
  const rangePip = calculatePip(range, pipDecimal);

  const avgBodyPercent = totalBodyPercent / candles.length;
  const colorBalance = Math.abs(greenCount - redCount);

  return {
    totalCandles: candles.length,
    greenCount: greenCount,
    redCount: redCount,
    dojiCount: dojiCount,
    colorChanges: colorChanges,
    colorSequence: colors.join('-'),
    colorBalance: colorBalance,
    rangeHigh: rangeHigh,
    rangeLow: rangeLow,
    range: range,
    rangePip: rangePip,
    avgBodyPercent: avgBodyPercent
  };
}

/**
 * คำแนะนำการกระทำถัดไป
 */
function getNextAction(score) {
  if (score >= 80) {
    return {
      action: 'STOP_TRADING',
      description: 'หยุดเทรดทันที และปิด Position ที่มี',
      stoplossAdvice: 'ขยาย SL หรือปิด Position',
      entryAdvice: 'ห้ามเข้า Position ใหม่'
    };
  } else if (score >= 60) {
    return {
      action: 'REDUCE_POSITION',
      description: 'ลดขนาด Position และเพิ่มความระมัดระวัง',
      stoplossAdvice: 'ใช้ SL แน่นกว่าปกติ',
      entryAdvice: 'รอ Confirmation ชัดเจนก่อนเข้า'
    };
  } else if (score >= 40) {
    return {
      action: 'BE_CAUTIOUS',
      description: 'ซื้อขายด้วยความระมัดระวัง',
      stoplossAdvice: 'ตั้ง SL ตามปกติ',
      entryAdvice: 'เข้าได้แต่เลือกจังหวะดีๆ'
    };
  } else {
    return {
      action: 'TRADE_NORMALLY',
      description: 'เทรดตามปกติ',
      stoplossAdvice: 'SL ปกติ',
      entryAdvice: 'เข้า Position ได้ตามสัญญาณ'
    };
  }
}

/**
 * แสดงรายงานการตรวจจับแต่เนิ่น
 */
function printEarlyChoppyReport(result) {
  if (!result) {
    displayResult('❌ ไม่มีข้อมูลเพียงพอ\n');
    return;
  }

  displayResult('═══════════════════════════════════════════════════');
  displayResult('       🔔 EARLY CHOPPY MARKET DETECTION');
  displayResult('═══════════════════════════════════════════════════\n');

  displayResult(`${result.alertColor} สถานะ: ${result.alertLevel}`);
  displayResult(`📊 คะแนนเตือน: ${result.earlyWarningScore}/100\n`);

  if (result.warnings.length > 0) {
    displayResult('⚠️  สัญญาณเตือน:');
    result.warnings.forEach((w, idx) => {
      displayResult(`   ${idx + 1}. ${w}`);
    });
    displayResult('');
  }

  displayResult('💡 คำแนะนำ:');
  displayResult(`   ${result.recommendation}\n`);

  displayResult('🎯 การกระทำ:');
  displayResult(`   Action: ${result.nextAction.action}`);
  displayResult(`   ${result.nextAction.description}`);
  displayResult(`   Stop Loss: ${result.nextAction.stoplossAdvice}`);
  displayResult(`   Entry: ${result.nextAction.entryAdvice}\n`);

  displayResult('📈 การวิเคราะห์:');
  const a3 = result.analysis.last3Candles;
  const a5 = result.analysis.last5Candles;

  displayResult(`\n   3 แท่งล่าสุด:`);
  displayResult(`     ลำดับสี: ${a3.colorSequence}`);
  displayResult(`     สลับสี: ${a3.colorChanges} ครั้ง`);
  displayResult(`     Range: ${a3.rangePip} pips`);
  displayResult(`     Body เฉลี่ย: ${a3.avgBodyPercent.toFixed(1)}%`);

  displayResult(`\n   5 แท่งล่าสุด:`);
  displayResult(`     Green: ${a5.greenCount}, Red: ${a5.redCount}`);
  displayResult(`     Range: ${a5.rangePip} pips`);
  displayResult(`     Body เฉลี่ย: ${a5.avgBodyPercent.toFixed(1)}%`);

  const wicks = result.analysis.lastCandleWicks;
  displayResult(`\n   แท่งล่าสุด:`);
  displayResult(`     Upper Wick: ${wicks.upperWickPercent}%`);
  displayResult(`     Lower Wick: ${wicks.lowerWickPercent}%`);
  displayResult(`     Doji-like: ${wicks.isDoji ? 'ใช่ ⚠️' : 'ไม่ใช่'}`);

  displayResult('\n═══════════════════════════════════════════════════\n');
}

/**
 * ติดตามและเตือนแบบ Real-time
 */
function monitorChoppyRealtime(candleData, pipDecimal = 5) {
  const result = detectEarlyChoppy(candleData, 10, pipDecimal);

  if (!result) return;

  // เตือนถ้าคะแนนสูง
  if (result.earlyWarningScore >= 60) {
    displayResult('\n🚨 ═══════════════════════════════════════════════ 🚨');
    displayResult(`   ${result.alertColor} ALERT: ${result.alertLevel}`);
    displayResult(`   คะแนน: ${result.earlyWarningScore}/100`);
    displayResult(`   ${result.recommendation}`);
    displayResult('🚨 ═══════════════════════════════════════════════ 🚨\n');
  }

  return result;
}
// ====================================
// ทดสอบกับข้อมูลจริง
// ====================================

/*
const testData = [
  { "close": 2866.354, "epoch": 1753270320, "high": 2867.35, "low": 2866.138, "open": 2867.148 },
  { "close": 2866.771, "epoch": 1753270380, "high": 2866.771, "low": 2865.845, "open": 2866.079 },
  { "close": 2865.694, "epoch": 1753270440, "high": 2866.93, "low": 2865.694, "open": 2866.93 },
  { "close": 2866.164, "epoch": 1753270500, "high": 2866.404, "low": 2865.671, "open": 2865.671 },
  { "close": 2865.904, "epoch": 1753270560, "high": 2866.199, "low": 2865.158, "open": 2866.199 },
  { "close": 2866.276, "epoch": 1753270620, "high": 2866.969, "low": 2865.631, "open": 2866.039 },
  { "close": 2866.201, "epoch": 1753270680, "high": 2866.446, "low": 2865.172, "open": 2866.211 },
  { "close": 2866.959, "epoch": 1753270740, "high": 2867.042, "low": 2865.745, "open": 2865.965 },
  { "close": 2864.889, "epoch": 1753270800, "high": 2866.947, "low": 2864.889, "open": 2866.947 }
];

displayResult('🧪 ทดสอบการตรวจจับแต่เนิ่น...\n');

// ทดสอบทีละแท่ง
displayResult('📊 จำลองการเทรดแบบ Real-time:\n');

for (let i = 3; i <= testData.length; i++) {
  const subset = testData.slice(0, i);
  displayResult(`\n━━━ หลังแท่งที่ ${i} ━━━`);

  const result = detectEarlyChoppy(subset, 10, 3);

  if (result) {
    displayResult(`${result.alertColor} Score: ${result.earlyWarningScore}/100 - ${result.alertLevel}`);

    if (result.earlyWarningScore >= 40) {
      displayResult(`⚠️  คำเตือน:`);
      result.warnings.slice(0, 2).forEach(w => displayResult(`   - ${w}`));
    }
  }
}

// รายงานเต็ม
displayResult('\n\n📋 รายงานเต็ม (ข้อมูลทั้งหมด):');
displayResult('════════════════════════════════════════════════════\n');
const fullResult = detectEarlyChoppy(testData, 10, 3);
printEarlyChoppyReport(fullResult);

// ทดสอบ Monitor
displayResult('🔔 ทดสอบ Real-time Monitor:');
monitorChoppyRealtime(testData, 3);
*/