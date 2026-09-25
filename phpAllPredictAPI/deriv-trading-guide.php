<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>📚 Deriv Trading Knowledge Base</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.8;
      background: linear-gradient(135deg, #1e1e2e 0%, #2d1b69 50%, #1e1e2e 100%);
      color: #e5e7eb;
      padding: 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: rgba(30, 30, 46, 0.95);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }

    .header {
      text-align: center;
      margin-bottom: 50px;
      padding-bottom: 30px;
      border-bottom: 2px solid rgba(167, 139, 250, 0.3);
    }

    .header h1 {
      font-size: 3rem;
      background: linear-gradient(45deg, #a78bfa, #ec4899);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 10px;
    }

    .header p {
      color: #9ca3af;
      font-size: 1.2rem;
    }

    .toc {
      background: rgba(59, 130, 246, 0.1);
      border: 1px solid rgba(59, 130, 246, 0.3);
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 40px;
    }

    .toc h2 {
      color: #60a5fa;
      margin-bottom: 20px;
      font-size: 1.8rem;
    }

    .toc ul {
      list-style: none;
      padding-left: 0;
    }

    .toc li {
      padding: 10px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .toc li:last-child {
      border-bottom: none;
    }

    .toc a {
      color: #93c5fd;
      text-decoration: none;
      font-size: 1.1rem;
      transition: all 0.3s;
      display: block;
    }

    .toc a:hover {
      color: #60a5fa;
      padding-left: 10px;
    }

    .section {
      margin-bottom: 60px;
      padding: 30px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 15px;
      border-left: 4px solid #a78bfa;
    }

    .section h2 {
      color: #a78bfa;
      font-size: 2rem;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section h3 {
      color: #ec4899;
      font-size: 1.5rem;
      margin-top: 30px;
      margin-bottom: 15px;
    }

    .section h4 {
      color: #60a5fa;
      font-size: 1.2rem;
      margin-top: 20px;
      margin-bottom: 10px;
    }

    pre {
      background: #0a0a0f;
      border: 1px solid rgba(167, 139, 250, 0.3);
      border-radius: 10px;
      padding: 20px;
      overflow-x: auto;
      margin: 20px 0;
      font-size: 0.9rem;
    }

    code {
      color: #a78bfa;
      font-family: 'Courier New', monospace;
    }

    .highlight {
      background: rgba(167, 139, 250, 0.2);
      padding: 2px 6px;
      border-radius: 4px;
      color: #c4b5fd;
    }

    .note {
      background: rgba(34, 197, 94, 0.1);
      border-left: 4px solid #22c55e;
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
    }

    .warning {
      background: rgba(239, 68, 68, 0.1);
      border-left: 4px solid #ef4444;
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
    }

    .tip {
      background: rgba(59, 130, 246, 0.1);
      border-left: 4px solid #3b82f6;
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
      background: rgba(0, 0, 0, 0.3);
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    th {
      background: rgba(167, 139, 250, 0.2);
      color: #a78bfa;
      font-weight: bold;
    }

    .emoji {
      font-size: 1.5rem;
    }

    .btn {
      display: inline-block;
      padding: 12px 24px;
      background: linear-gradient(45deg, #a78bfa, #ec4899);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
      transition: all 0.3s;
      margin: 10px 5px;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(167, 139, 250, 0.3);
    }

    .footer {
      text-align: center;
      margin-top: 60px;
      padding-top: 30px;
      border-top: 2px solid rgba(167, 139, 250, 0.3);
      color: #9ca3af;
    }

    @media print {
      body {
        background: white;
        color: black;
      }
      .container {
        background: white;
        box-shadow: none;
      }
      .section {
        page-break-inside: avoid;
      }
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
      width: 10px;
    }

    ::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.3);
    }

    ::-webkit-scrollbar-thumb {
      background: #a78bfa;
      border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #c4b5fd;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <h1>📚 Deriv Trading Knowledge Base</h1>
      <p>คู่มือการเทรดและเขียนโปรแกรมสำหรับ Deriv.com</p>
      <p style="font-size: 0.9rem; margin-top: 10px;">สร้างโดย Claude AI • บันทึก: <span id="date"></span></p>
    </div>

    <!-- Table of Contents -->
    <div class="toc">
      <h2>📑 สารบัญ</h2>
      <ul>
        <li><a href="#section1">1. Hull MA & Hull Suite Indicator</a></li>
        <li><a href="#section2">2. Trading Psychology & Risk Management</a></li>
        <li><a href="#section3">3. Multiply Risk Calculator</a></li>
        <li><a href="#section4">4. Pine Script to JavaScript</a></li>
        <li><a href="#section5">5. Deriv WebSocket API</a></li>
        <li><a href="#section6">6. Lightweight Charts Integration</a></li>
        <li><a href="#section7">7. Date & Timestamp Conversion</a></li>
        <li><a href="#section8">8. Trading Emoji Icons</a></li>
        <li><a href="#section9">9. Code Examples & Templates</a></li>
      </ul>
    </div>

    <!-- Section 1: Hull MA -->
    <div class="section" id="section1">
      <h2><span class="emoji">📈</span> Hull MA & Hull Suite Indicator</h2>
      
      <h3>Hull MA คืออะไร?</h3>
      <p>Hull Moving Average (HMA) คือเส้น Moving Average ที่ออกแบบโดย Alan Hull เพื่อแก้ปัญหาของ MA ทั่วไป คือ <strong>ลด Lag และลด Noise</strong></p>

      <div class="note">
        <strong>✅ ข้อดี:</strong>
        <ul>
          <li>เร็วกว่า SMA/EMA</li>
          <li>ลด Lag ได้มาก</li>
          <li>นุ่มนวล ลด Noise</li>
          <li>ติดตามราคาชิดที่สุด</li>
        </ul>
      </div>

      <h4>สูตรคำนวณ:</h4>
      <pre><code>HMA = WMA(2 × WMA(n/2) - WMA(n), sqrt(n))

โดย:
- WMA = Weighted Moving Average
- n = Period (ระยะเวลา)
- sqrt(n) = รากที่สองของ n</code></pre>

      <h4>Length แนะนำ:</h4>
      <table>
        <tr>
          <th>ประเภท</th>
          <th>Length</th>
          <th>วัตถุประสงค์</th>
        </tr>
        <tr>
          <td>Scalping</td>
          <td>20-30</td>
          <td>ตอบสนองเร็ว แต่ noise มาก</td>
        </tr>
        <tr>
          <td>Day Trading</td>
          <td>40-60</td>
          <td>สมดุลระหว่างเร็วและแม่นยำ</td>
        </tr>
        <tr>
          <td>Swing Trading</td>
          <td>55</td>
          <td>หา Entry/Exit Points</td>
        </tr>
        <tr>
          <td>S/R Lines</td>
          <td>180-200</td>
          <td>ดู Support/Resistance ระยะยาว</td>
        </tr>
      </table>

      <h4>JavaScript Class:</h4>
      <pre><code>// ไฟล์: hullsuite.js
class HullSuite {
  constructor(config = {}) {
    this.config = {
      source: 'close',
      mode: 'Hma',     // 'Hma', 'Thma', 'Ehma'
      length: 55,
      lengthMult: 1.0,
      switchColor: true
    };
  }
  
  processDerivCandles(candleData, options = {}) {
    // คำนวณ Hull MA และ Trading Range
    // ส่งคืน: MHULL, SHULL, trend, color, tradingRange
  }
}</code></pre>

      <div class="tip">
        <strong>💡 Tips:</strong><br>
        - ใช้ HMA(55) สำหรับ Swing Trading<br>
        - ใช้ร่วมกับ Multiple Timeframe<br>
        - Color Change = สัญญาณเปลี่ยน Trend
      </div>
    </div>

    <!-- Section 2: Trading Psychology -->
    <div class="section" id="section2">
      <h2><span class="emoji">🧠</span> Trading Psychology & Risk Management</h2>
      
      <h3>ทำไมกราฟถึงกลับทิศทันทีที่เข้า Trade?</h3>
      
      <div class="warning">
        <strong>⚠️ สาเหตุที่แท้จริง (ไม่ใช่การโกง):</strong>
        <ol>
          <li><strong>Confirmation Bias</strong> - จำแต่ครั้งที่แพ้</li>
          <li><strong>Entry Timing ผิด</strong> - เข้าช้าไป (Late Entry)</li>
          <li><strong>Trading Against Trend</strong> - เทรดทวนเทรนด์</li>
          <li><strong>Psychological Price Levels</strong> - เลขกลมมี S/R แข็ง</li>
          <li><strong>Market Maker Liquidity Hunt</strong> - ดัก Stop Loss</li>
          <li><strong>Spread & Commission</strong> - เริ่มต้นติดลบทันที</li>
          <li><strong>Small Timeframe = High Noise</strong> - สัญญาณรบกวนมาก</li>
          <li><strong>Overtrading</strong> - เทรดบ่อยเกินไป</li>
        </ol>
      </div>

      <h3>วิธีแก้ปัญหา:</h3>
      
      <h4>1. ใช้ Confirmation</h4>
      <pre><code>❌ ไม่ควร: เห็นกราฟขึ้น → เข้า CALL ทันที

✅ ควร: เห็นกราฟขึ้น → รอ confirmation:
  - Volume เพิ่มขึ้น
  - Hull MA เปลี่ยนสี
  - Price break ผ่าน Resistance
  - Candle Pattern
  → ถึงค่อยเข้า CALL</code></pre>

      <h4>2. Trade With The Trend</h4>
      <pre><code>if (HMA สีเขียว) {
  // เข้าแต่ CALL เท่านั้น ✅
} else {
  // เข้าแต่ PUT เท่านั้น ✅
}

// ❌ ห้าม Trade Against Trend!</code></pre>

      <h4>3. Set Stop Loss & Take Profit</h4>
      <table>
        <tr>
          <th>Loss %</th>
          <th>Profit ที่ต้องการคืนทุน</th>
        </tr>
        <tr>
          <td>-10%</td>
          <td>+11.1%</td>
        </tr>
        <tr>
          <td>-20%</td>
          <td>+25%</td>
        </tr>
        <tr>
          <td>-50%</td>
          <td>+100%</td>
        </tr>
        <tr>
          <td>-90%</td>
          <td>+900%</td>
        </tr>
      </table>

      <div class="note">
        <strong>🎯 กฎทอง:</strong><br>
        "Never risk more than 1-2% of your account per trade"
      </div>
    </div>

    <!-- Section 3: Multiply Calculator -->
    <div class="section" id="section3">
      <h2><span class="emoji">⚡</span> Multiply Risk Calculator</h2>
      
      <h3>ทำไม Loss วิ่งเร็วกว่า Profit?</h3>
      
      <h4>ตัวอย่าง:</h4>
      <pre><code>ทุนเริ่มต้น: $100

Loss 10%:  $100 → $90  (ขาดทุน $10)
Profit 10%: $90 → $99   (กำไร $9)

❌ Loss 10% ≠ Profit 10%
✅ ต้องทำ Profit 11.11% ถึงจะคืนทุน!</code></pre>

      <h4>กับ Multiplier ยิ่งรุนแรง:</h4>
      <pre><code>Multiply x100:

Loss 1%:   ทุน $100 → Loss 100% → $0 (Liquidation!)
Profit 1%: ทุน $100 → Profit 100% → $200</code></pre>

      <h4>Function คำนวณ Position Size:</h4>
      <pre><code>function calculateTradeParams(
  accountBalance, 
  riskPercent, 
  multiplier, 
  stopLossPercent, 
  takeProfitPercent
) {
  const maxRiskAmount = accountBalance * (riskPercent / 100);
  const stake = maxRiskAmount / (stopLossPercent / 100) / multiplier;
  const stopLoss = -(stake * multiplier * (stopLossPercent / 100));
  const takeProfit = stake * multiplier * (takeProfitPercent / 100);
  
  return { stake, multiplier, stopLoss, takeProfit };
}</code></pre>

      <div class="warning">
        <strong>⚠️ Multiplier Recommendations:</strong><br>
        - x2-x5: มือใหม่<br>
        - x10-x20: มีประสบการณ์<br>
        - x50+: มืออาชีพเท่านั้น<br>
        - x100+: EXTREME RISK!
      </div>
    </div>

    <!-- Section 4: Pine Script to JS -->
    <div class="section" id="section4">
      <h2><span class="emoji">🔄</span> Pine Script to JavaScript</h2>
      
      <h3>Lightweight Charts ไม่รองรับ Pine Script</h3>
      <p>ต้องแปลเป็น JavaScript เอง</p>

      <h4>ตัวอย่างการแปลง:</h4>
      <pre><code>// Pine Script
hma = wma(2 * wma(close, length/2) - wma(close, length), round(sqrt(length)))

// JavaScript
function HMA(data, length) {
  const halfLength = Math.floor(length / 2);
  const sqrtLength = Math.round(Math.sqrt(length));
  
  const wma1 = WMA(data, halfLength);
  const wma2 = WMA(data, length);
  
  const diff = [];
  for (let i = 0; i < Math.min(wma1.length, wma2.length); i++) {
    diff.push(2 * wma1[i] - wma2[i]);
  }
  
  return WMA(diff, sqrtLength);
}</code></pre>

      <div class="tip">
        <strong>💡 Libraries แนะนำ:</strong><br>
        - <strong>technicalindicators</strong> (NPM) - มี SMA, EMA, RSI, MACD<br>
        - <strong>tulind</strong> - เร็วมาก (C++ binding)<br>
        - <strong>เขียนเอง</strong> - ควบคุมได้เต็มที่ (แนะนำ!)
      </div>
    </div>

    <!-- Section 5: Deriv WebSocket -->
    <div class="section" id="section5">
      <h2><span class="emoji">🔌</span> Deriv WebSocket API</h2>
      
      <h3>ขั้นตอนการ Buy Contract:</h3>
      <ol>
        <li>เชื่อมต่อ WebSocket</li>
        <li>Authorize (ถ้าจำเป็น)</li>
        <li>ส่ง Proposal Request</li>
        <li>รับ Proposal Response</li>
        <li>ส่ง Buy Request</li>
        <li>รับ Buy Response</li>
        <li>ติดตาม Contract</li>
      </ol>

      <h4>1. เชื่อมต่อ WebSocket:</h4>
      <pre><code>const ws = new WebSocket(
  'wss://ws.derivws.com/websockets/v3?app_id=YOUR_APP_ID'
);

ws.onopen = () => {
  console.log('Connected!');
};</code></pre>

      <h4>2. ส่ง Proposal:</h4>
      <pre><code>ws.send(JSON.stringify({
  proposal: 1,
  amount: 1,
  basis: "stake",
  contract_type: "CALLE",  // ไม่ใช่ "CALL"!
  currency: "USD",
  duration: 55,
  duration_unit: "s",
  symbol: "R_100"
}));</code></pre>

      <h4>3. รับ Response:</h4>
      <pre><code>ws.onmessage = (msg) => {
  const data = JSON.parse(msg.data);
  
  if (data.msg_type === 'proposal') {
    const proposalId = data.proposal.id;
    const askPrice = data.proposal.ask_price;
    const payout = data.proposal.payout;
  }
};</code></pre>

      <h4>4. ซื้อ Contract:</h4>
      <pre><code>ws.send(JSON.stringify({
  buy: proposalId,
  price: askPrice
}));</code></pre>

      <h3>Contract Types:</h3>
      <table>
        <tr>
          <th>Type</th>
          <th>Code</th>
          <th>Description</th>
        </tr>
        <tr>
          <td>Rise/Fall</td>
          <td>CALLE / PUTE</td>
          <td>Call/Put แบบธรรมดา</td>
        </tr>
        <tr>
          <td>Higher/Lower</td>
          <td>CALL / PUT</td>
          <td>สูงกว่า/ต่ำกว่า</td>
        </tr>
        <tr>
          <td>Multiplier</td>
          <td>MULTUP / MULTDOWN</td>
          <td>Leverage Trading</td>
        </tr>
      </table>
    </div>

    <!-- Section 6: Lightweight Charts -->
    <div class="section" id="section6">
      <h2><span class="emoji">📊</span> Lightweight Charts Integration</h2>
      
      <h3>ตีเส้น Trendline:</h3>
      <pre><code>// สร้าง Chart
const chart = LightweightCharts.createChart(container);
const candlestickSeries = chart.addCandlestickSeries();

// วาด Trendline
const trendlineSeries = chart.addLineSeries({
  color: '#2962FF',
  lineWidth: 2,
  lineStyle: 0  // 0=solid, 1=dotted, 2=dashed
});

// ข้อมูล 2 จุด
const trendlineData = [
  { time: '2024-01-01', value: 100 },
  { time: '2024-01-05', value: 110 }
];

trendlineSeries.setData(trendlineData);</code></pre>

      <h4>Function สร้าง Trendline:</h4>
      <pre><code>function createTrendline(candles, startIdx, endIdx, useHigh = false) {
  const start = candles[startIdx];
  const end = candles[endIdx];
  
  const startValue = useHigh ? start.high : start.low;
  const endValue = useHigh ? end.high : end.low;
  const slope = (endValue - startValue) / (endIdx - startIdx);
  
  return candles.map((candle, i) => {
    if (i < startIdx || i > endIdx) return null;
    return {
      time: candle.time,
      value: startValue + slope * (i - startIdx)
    };
  }).filter(x => x);
}</code></pre>
    </div>

    <!-- Section 7: Date Conversion -->
    <div class="section" id="section7">
      <h2><span class="emoji">🕐</span> Date & Timestamp Conversion</h2>
      
      <h3>แปลง DateTime → Timestamp:</h3>
      <pre><code>const dateString = '2025-08-13T01:00';
const timestamp = Math.floor(new Date(dateString).getTime() / 1000);
// → 1723514400</code></pre>

      <h3>แปลง Timestamp → DateTime:</h3>
      <pre><code>const timestamp = 1723514400;
const dateString = new Date(timestamp * 1000).toISOString();
// → '2025-08-13T01:00:00.000Z'</code></pre>

      <h4>Helper Class:</h4>
      <pre><code>class DerivDateHelper {
  static toTimestamp(dateString) {
    return Math.floor(new Date(dateString).getTime() / 1000);
  }
  
  static toDateString(timestamp) {
    return new Date(timestamp * 1000).toISOString();
  }
  
  static now() {
    return Math.floor(Date.now() / 1000);
  }
  
  static minutesAgo(minutes) {
    return Math.floor((Date.now() - minutes * 60 * 1000) / 1000);
  }
  
  static hoursAgo(hours) {
    return Math.floor((Date.now() - hours * 60 * 60 * 1000) / 1000);
  }
  
  static daysAgo(days) {
    return Math.floor((Date.now() - days * 24 * 60 * 60 * 1000) / 1000);
  }
}</code></pre>

      <h4>ใช้กับ Deriv API:</h4>
      <pre><code>const startTime = DerivDateHelper.toTimestamp('2025-08-13T01:00');
const endTime = DerivDateHelper.toTimestamp('2025-08-13T02:00');

const request = {
  ticks_history: "R_100",
  start: startTime,
  end: endTime,
  style: "candles",
  granularity: 60
};</code></pre>
    </div>

    <!-- Section 8: Emoji Icons -->
    <div class="section" id="section8">
      <h2><span class="emoji">😀</span> Trading Emoji Icons</h2>
      
      <h3>Arrow Icons:</h3>
      <pre><code>🟢 ⬆️ 📈 ↗️ ⤴️ 🔼 ▲ 🚀 💹 ✅  // Green (Up/Long/Buy)
🔴 ⬇️ 📉 ↘️ ⤵️ 🔽 ▼ 💔 ❌ 🛑  // Red (Down/Short/Sell)
⚪ ➡️ ⏸️ 🟡 ⏳ 🔄 🔃 ↔️ ⏯️   // Neutral/Wait</code></pre>

      <h3>Trading Signals:</h3>
      <pre><code>🟢 ⬆️  = Buy/Long/CALL
🔴 ⬇️  = Sell/Short/PUT
⚪ ➡️  = Sideways/Neutral
🟡 ⏸️  = Wait/Hold</code></pre>

      <h3>Status Icons:</h3>
      <pre><code>✅ = Success/Profit
❌ = Loss/Stop Loss
⚠️ = Warning/Risky
💰 = Money/Profit
📊 = Analysis/Chart
🎯 = Target/Goal
🛡️ = Protection/Stop Loss
⚡ = Fast/Quick
🔥 = Hot/Trending
💎 = Quality/Premium</code></pre>

      <h4>JavaScript Object:</h4>
      <pre><code>const TradingEmoji = {
  // Directions
  up: '🟢 ⬆️',
  down: '🔴 ⬇️',
  neutral: '⚪ ➡️',
  
  // Actions
  buy: '💚 BUY',
  sell: '❤️ SELL',
  hold: '💛 HOLD',
  
  // Results
  profit: '✅ 💰',
  loss: '❌ 📉',
  breakeven: '➖',
  
  // Status
  success: '✅',
  error: '❌',
  warning: '⚠️',
  info: 'ℹ️'
};</code></pre>
    </div>

    <!-- Section 9: Code Examples -->
    <div class="section" id="section9">
      <h2><span class="emoji">💻</span> Code Examples & Templates</h2>
      
      <h3>Complete Trading Bot Example:</h3>
      <pre><code>class DerivTradingBot {
  constructor(appId, apiToken) {
    this.api = new DerivAPI({ app_id: appId });
    this.token = apiToken;
    this.hullSuite = new HullSuite({ length: 55 });
  }

  async connect() {
    await this.api.basic.ping();
    if (this.token) {
      await this.api.basic.authorize(this.token);
    }
    console.log('✅ Connected');
  }

  async analyzeMarket(symbol, granularity = 60) {
    // 1. ดึงข้อมูล Candles
    const candles = await this.getCandles(symbol, granularity, 200);
    
    // 2. คำนวณ Hull MA
    const hullData = this.hullSuite.processDerivCandles(candles);
    
    // 3. วิเคราะห์ Trend
    const trend = hullData.trend;
    const color = hullData.color;
    const alert = hullData.alert;
    
    // 4. ตรวจสอบ Trading Range
    const { high, low, midpoint } = hullData.tradingRange;
    const position = hullData.pricePosition.position;
    
    return {
      trend,
      color,
      alert,
      tradingRange: { high, low, midpoint },
      position,
      signal: this.generateSignal(trend, position, alert)
    };
  }

  generateSignal(trend, position, alert) {
    // Uptrend + Pullback + Alert = Strong Buy
    if (trend === 'up' && position === 'lower' && alert?.type === 'up') {
      return {
        action: 'CALL',
        confidence: 'HIGH',
        reason: 'Uptrend + Pullback + Confirmation'
      };
    }
    
    // Downtrend + Pullback + Alert = Strong Sell
    if (trend === 'down' && position === 'upper' && alert?.type === 'down') {
      return {
        action: 'PUT',
        confidence: 'HIGH',
        reason: 'Downtrend + Pullback + Confirmation'
      };
    }
    
    return null;
  }

  async executeTrade(signal, amount = 1, duration = 55) {
    if (!signal) {
      console.log('❌ No signal');
      return;
    }
    
    const contractType = signal.action === 'CALL' ? 'CALLE' : 'PUTE';
    
    // 1. Get Proposal
    const proposal = await this.api.proposal({
      proposal: 1,
      amount,
      basis: "stake",
      contract_type: contractType,
      currency: "USD",
      duration,
      duration_unit: "s",
      symbol: "R_100"
    });
    
    console.log(`📊 ${signal.action} Signal (${signal.confidence})`);
    console.log(`💰 Price: ${proposal.proposal.ask_price}`);
    
    // 2. Buy Contract
    const buy = await this.api.buy({
      buy: proposal.proposal.id,
      price: proposal.proposal.ask_price
    });
    
    console.log(`✅ Trade Executed: ${buy.buy.contract_id}`);
    
    return buy;
  }

  async getCandles(symbol, granularity, count) {
    const response = await this.api.ticksHistory({
      ticks_history: symbol,
      granularity,
      count,
      end: "latest",
      style: "candles"
    });
    
    return response.candles;
  }
}

// ใช้งาน
const bot = new DerivTradingBot(1089, 'YOUR_TOKEN');
await bot.connect();

// วิเคราะห์ตลาด
const analysis = await bot.analyzeMarket('R_100');
console.log('Analysis:', analysis);

// เทรดถ้ามีสัญญาณ
if (analysis.signal) {
  await bot.executeTrade(analysis.signal);
}</code></pre>

      <h3>Risk Management Template:</h3>
      <pre><code>class RiskManager {
  constructor(accountBalance, maxRiskPercent = 2) {
    this.accountBalance = accountBalance;
    this.maxRiskPercent = maxRiskPercent;
  }

  calculatePosition(multiplier, stopLossPercent) {
    const maxRisk = this.accountBalance * (this.maxRiskPercent / 100);
    const stake = maxRisk / (stopLossPercent / 100) / multiplier;
    
    return {
      stake: Math.max(stake, 1),
      maxLoss: maxRisk,
      multiplier,
      stopLoss: -(maxRisk),
      takeProfit: maxRisk * 2  // Risk:Reward 1:2
    };
  }

  canTrade() {
    // ตรวจสอบว่าสามารถเทรดได้หรือไม่
    return this.accountBalance > 10;
  }

  updateBalance(profit) {
    this.accountBalance += profit;
    console.log(`💰 New Balance: ${this.accountBalance}`);
  }
}

// ใช้งาน
const risk = new RiskManager(10000, 2);
const position = risk.calculatePosition(50, 5);

console.log('Position:', position);
// { stake: 8, maxLoss: 200, multiplier: 50, stopLoss: -200, takeProfit: 400 }</code></pre>

      <h3>Candle Pattern Detection:</h3>
      <pre><code>class CandlePatterns {
  static isEngulfing(current, previous) {
    // Bullish Engulfing
    if (previous.close < previous.open && 
        current.close > current.open &&
        current.close > previous.open &&
        current.open < previous.close) {
      return 'BULLISH_ENGULFING';
    }
    
    // Bearish Engulfing
    if (previous.close > previous.open && 
        current.close < current.open &&
        current.close < previous.open &&
        current.open > previous.close) {
      return 'BEARISH_ENGULFING';
    }
    
    return null;
  }

  static isDoji(candle) {
    const bodySize = Math.abs(candle.close - candle.open);
    const totalSize = candle.high - candle.low;
    
    return bodySize < (totalSize * 0.1);
  }

  static isPinBar(candle) {
    const bodySize = Math.abs(candle.close - candle.open);
    const upperWick = candle.high - Math.max(candle.open, candle.close);
    const lowerWick = Math.min(candle.open, candle.close) - candle.low;
    
    // Bullish Pin Bar
    if (lowerWick > (bodySize * 2) && upperWick < bodySize) {
      return 'BULLISH_PIN';
    }
    
    // Bearish Pin Bar
    if (upperWick > (bodySize * 2) && lowerWick < bodySize) {
      return 'BEARISH_PIN';
    }
    
    return null;
  }

  static detectPattern(candles) {
    if (candles.length < 2) return null;
    
    const current = candles[candles.length - 1];
    const previous = candles[candles.length - 2];
    
    // ตรวจสอบ Patterns
    const engulfing = this.isEngulfing(current, previous);
    if (engulfing) return engulfing;
    
    const pinBar = this.isPinBar(current);
    if (pinBar) return pinBar;
    
    if (this.isDoji(current)) return 'DOJI';
    
    return null;
  }
}

// ใช้งาน
const pattern = CandlePatterns.detectPattern(candles);
if (pattern) {
  console.log(`📊 Pattern Detected: ${pattern}`);
}</code></pre>
    </div>

    <!-- Footer -->
    <div class="footer">
      <h3>📚 Additional Resources</h3>
      <p>
        <a href="https://docs.deriv.com" class="btn" target="_blank">Deriv API Docs</a>
        <a href="https://tradingview.github.io/lightweight-charts/" class="btn" target="_blank">Lightweight Charts</a>
        <a href="https://github.com/binary-com/deriv-api" class="btn" target="_blank">Deriv GitHub</a>
      </p>
      
      <div style="margin-top: 40px;">
        <p><strong>⚠️ Disclaimer:</strong></p>
        <p style="font-size: 0.9rem; max-width: 800px; margin: 10px auto; color: #9ca3af;">
          เอกสารนี้จัดทำขึ้นเพื่อการศึกษาเท่านั้น การเทรดมีความเสี่ยง 
          อาจส่งผลให้สูญเสียเงินทุนทั้งหมด โปรดศึกษาและทำความเข้าใจก่อนการเทรดจริง 
          ผู้เขียนไม่รับผิดชอบต่อความเสียหายที่เกิดขึ้นจากการนำไปใช้
        </p>
      </div>

      <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <p style="color: #6b7280;">
          Created with ❤️ by Claude AI<br>
          Last Updated: <span id="updateDate"></span>
        </p>
      </div>
    </div>
  </div>

  <script>
    // Set current date
    const now = new Date();
    document.getElementById('date').textContent = now.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
    document.getElementById('updateDate').textContent = now.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });

    // Smooth scroll for TOC links
    document.querySelectorAll('.toc a').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });

    // Print function
    function printPage() {
      window.print();
    }

    // Export as PDF hint
    console.log('💡 Tip: Press Ctrl+P to save as PDF');
  </script>
</body>
</html>