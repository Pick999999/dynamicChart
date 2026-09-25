
/**
 * ========================================
 * HTML TABLE REPORT - CHOPPY DETECTION
 * รายงานแบบตารางแสดงสภาวะแต่ละแท่ง
 * ========================================
 */

/**
 * สร้างรายงาน HTML Table
 */
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
 * บันทึก HTML ลงไฟล์
 */
function saveHTMLReport(candleData, filename = 'choppy-report.html', pipDecimal = 5) {
  const html = generateChoppyHTMLReport(candleData, pipDecimal);
  
  // สำหรับ Node.js
  if (typeof require !== 'undefined') {
    const fs = require('fs');
    fs.writeFileSync(filename, html, 'utf8');
    console.log(`✅ บันทึกรายงานที่ ${filename}`);
  } else {
    // สำหรับ Browser - ดาวน์โหลดไฟล์
    const blob = new Blob([html], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
    console.log(`✅ ดาวน์โหลดรายงาน ${filename}`);
  }
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

// ====================================
// ตัวอย่างการใช้งาน
// ====================================

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

// 1. บันทึกเป็นไฟล์ HTML
saveHTMLReport(testData, 'choppy-analysis.html', 3);

// 2. แสดงใน Browser (ถ้าอยู่ใน Browser)
// displayHTMLReport(testData, 3);

// 3. ได้ HTML String
const htmlString = generateChoppyHTMLReport(testData, 3);
console.log('✅ สร้าง HTML Report สำเร็จ');