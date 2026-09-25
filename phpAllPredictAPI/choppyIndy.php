<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choppiness Index Lab - Deriv Trading</title>
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
            color: #333;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        h1 {
            text-align: center;
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2em;
        }

        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 25px;
            font-size: 0.9em;
        }

        .control-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        .control-group label {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.85em;
            color: #555;
        }

        .control-group input,
        .control-group select,
        .control-group button {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 0.9em;
            transition: all 0.3s;
        }

        .control-group input:focus,
        .control-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            background: #667eea;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        button:hover:not(:disabled) {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Trading Hint Box - ขยายใหญ่และชัดเจน */
        .trading-hint {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            border: 3px solid rgba(255,255,255,0.3);
        }

        .trading-hint.call {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-color: #38ef7d;
        }

        .trading-hint.put {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            border-color: #f45c43;
        }

        .trading-hint.idle {
            background: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%);
            border-color: #95a5a6;
        }

        .hint-title {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hint-content {
            font-size: 1.2em;
            line-height: 1.8;
            opacity: 0.95;
            margin-bottom: 20px;
        }

        .hint-details {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
        }

        .hint-detail-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .hint-detail-item strong {
            display: block;
            font-size: 0.9em;
            margin-bottom: 8px;
            opacity: 0.9;
        }

        .hint-detail-item span {
            font-size: 1.4em;
            font-weight: bold;
        }

        .status-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .status-card.choppy {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .status-card.neutral {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #333;
        }

        .status-card.trending {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }

        .status-card h3 {
            font-size: 0.9em;
            margin-bottom: 8px;
            opacity: 0.9;
        }

        .status-card .value {
            font-size: 2em;
            font-weight: bold;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .chart-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chart-box {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        /* History Panel */
        .history-panel {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .history-header h3 {
            color: #667eea;
            margin: 0;
        }

        .history-list {
            max-height: 300px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .history-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid #667eea;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 15px;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .history-item.call {
            border-left-color: #38ef7d;
        }

        .history-item.put {
            border-left-color: #f45c43;
        }

        .history-item.idle {
            border-left-color: #95a5a6;
        }

        .history-icon {
            font-size: 2em;
        }

        .history-content {
            flex: 1;
        }

        .history-action {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        .history-time {
            font-size: 0.85em;
            color: #888;
        }

        .history-confidence {
            font-size: 1.2em;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 20px;
            background: #f0f0f0;
        }

        .info-panel {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .info-panel h3 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .info-item h4 {
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        .info-item p {
            color: #666;
            font-size: 0.85em;
            line-height: 1.5;
        }

        .legend {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85em;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .marker-count {
            background: #ff6b6b;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .loading {
            animation: pulse 1.5s infinite;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .history-item {
            animation: slideIn 0.3s ease-out;
        }

        /* Scrollbar styling */
        .history-list::-webkit-scrollbar {
            width: 8px;
        }

        .history-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .history-list::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Choppiness Index Lab</h1>
        <div class="subtitle">Advanced Market State Detection with Trading Hints & Alerts</div>

        <!-- Control Panel -->
        <div class="control-panel">
            <div class="control-group">
                <label>Symbol</label>
                <select id="symbolSelect">
                    <option value="R_10">Volatility 10 Index</option>
                    <option value="R_25">Volatility 25 Index</option>
                    <option value="R_50">Volatility 50 Index</option>
                    <option value="R_75">Volatility 75 Index</option>
                    <option value="R_100">Volatility 100 Index</option>
                    <option value="1HZ10V">Volatility 10 (1s) Index</option>
                    <option value="frxEURUSD">EUR/USD</option>
                    <option value="frxGBPUSD">GBP/USD</option>
                </select>
            </div>

            <div class="control-group">
                <label>Timeframe</label>
                <select id="timeframeSelect">
                    <option value="60" selected>1 Minute</option>
                    <option value="300">5 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="3600" >1 Hour</option>
                    <option value="14400">4 Hours</option>
                    <option value="86400">1 Day</option>
                </select>
            </div>

            <div class="control-group">
                <label>CI Period</label>
                <input type="number" id="ciPeriod" value="9" min="5" max="50">
            </div>

            <div class="control-group">
                <label>Candles Count</label>
                <input type="number" id="candlesCount" value="200" min="50" max="1000">
            </div>

            <div class="control-group">
                <label>Actions</label>
                <button id="connectBtn">🔌 Connect to Deriv</button>
            </div>

            <div class="control-group">
                <label>&nbsp;</label>
                <button id="loadDataBtn" disabled>📥 Load Data</button>
            </div>

            <div class="control-group">
                <label>&nbsp;</label>
                <button id="updateCIBtn" disabled>🔄 Update CI</button>
            </div>

            <div class="control-group">
                <label>Alert Settings</label>
                <div class="checkbox-group">
                    <input type="checkbox" id="alertEnabled" checked>
                    <label for="alertEnabled" style="margin: 0;">Enable Sound Alert</label>
                </div>
            </div>
        </div>

        <!-- Trading Hint Box - ขยายใหญ่และชัดเจน -->
        <div class="trading-hint idle" id="tradingHint">
            <div class="hint-title">
                <span id="hintIcon">⏸️</span>
                <span id="hintTitle">Waiting for Data...</span>
            </div>
            <div class="hint-content" id="hintContent">
                Connect to Deriv and load candle data to get trading suggestions
            </div>
            <div class="hint-details" id="hintDetails" style="display: none;">
                <div class="hint-detail-item">
                    <strong>CI Value</strong>
                    <span id="hintCI">--</span>
                </div>
                <div class="hint-detail-item">
                    <strong>EMA Trend</strong>
                    <span id="hintEMA">--</span>
                </div>
                <div class="hint-detail-item">
                    <strong>Price Position</strong>
                    <span id="hintPrice">--</span>
                </div>
                <div class="hint-detail-item">
                    <strong>Confidence</strong>
                    <span id="hintConfidence">--</span>
                </div>
            </div>
        </div>

        <!-- Status Panel -->
        <div class="status-panel">
            <div class="status-card" id="ciCard">
                <h3>Choppiness Index</h3>
                <div class="value" id="ciValue">--</div>
            </div>

            <div class="status-card" id="marketStateCard">
                <h3>Market State</h3>
                <div class="value" id="marketState">WAITING</div>
            </div>

            <div class="status-card">
                <h3>Trading Signal</h3>
                <div class="value" id="tradingSignal">⏸️ WAIT</div>
            </div>

            <div class="status-card">
                <h3>Sideways Markers</h3>
                <div class="value marker-count" id="markerCount" style="font-size: 1.5em;">0</div>
				<button type='button' id='' class='mBtn' onclick="AddMarkerSideWays()">Show Markers</button>
            </div>
        </div>

        <!-- History Panel -->
        <div class="history-panel">
            <div class="history-header">
                <h3>📜 Signal History (Last 10)</h3>
                <button onclick="clearHistory()" style="padding: 5px 15px; font-size: 0.85em;">🗑️ Clear History</button>
            </div>
            <div class="history-list" id="historyList">
                <div style="text-align: center; color: #888; padding: 20px;">
                    No signals yet. Load data to see trading hints.
                </div>
            </div>
        </div>

        <!-- Main Chart -->
        <div class="chart-container">
            <div class="chart-title">
                <span>💹 Price Chart with EMA & Sideways Markers</span>
                <span id="chartSymbol" style="color: #888; font-size: 0.9em;">No data</span>
            </div>
            <div class="chart-box" id="mainChart" style="height: 400px;"></div>
        </div>

        <!-- Choppiness Index Chart -->
        <div class="chart-container">
            <div class="chart-title">
                <span>📈 Choppiness Index (0-100)</span>
            </div>
            <div class="chart-box" id="ciChart" style="height: 200px;"></div>
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #ef5350;"></div>
                    <span>Choppy (61.8-100) - 🛑 IDLE</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffa726;"></div>
                    <span>Neutral (38.2-61.8) - ⚠️ CAUTION</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #66bb6a;"></div>
                    <span>Trending (0-38.2) - ✅ TRADE</span>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="info-panel">
            <h3>💡 Trading Logic</h3>
            <div class="info-grid">
                <div class="info-item">
                    <h4>📈 CALL Signal</h4>
                    <p>• CI < 38.2 (Trending)<br>• Price > EMA9 > EMA21<br>• EMA9 กำลังขึ้น<br>• จะมี Alert เสียง 🔔</p>
                </div>
                <div class="info-item">
                    <h4>📉 PUT Signal</h4>
                    <p>• CI < 38.2 (Trending)<br>• Price < EMA9 < EMA21<br>• EMA9 กำลังลง<br>• จะมี Alert เสียง 🔔</p>
                </div>
                <div class="info-item">
                    <h4>⏸️ IDLE Signal</h4>
                    <p>• CI > 61.8 (Choppy)<br>• หรือ EMA แกว่งไปมา<br>• ไม่มีทิศทางชัด<br>• ไม่มี Alert</p>
                </div>
                <div class="info-item">
                    <h4>🔴 Sideways Markers</h4>
                    <p>แสดงจุดที่ CI เข้าสู่โซน Choppy (>61.8) บน Chart เพื่อเตือนไม่ให้เทรด</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <script>
        const DERIV_APP_ID = 1089;
        const DERIV_WS_URL = `wss://ws.derivws.com/websockets/v3?app_id=${DERIV_APP_ID}`;

        let ws = null;
        let isConnected = false;
        let candleData = [];
        let mainChart = null;
        let ciChart = null;
        let candleSeries = null;
        let emaSeries = [];
        let ciSeries = null;
        let sidewaysMarkers = [];
        let signalHistory = [];
        let lastSignal = null; 
		let ciData = [];

        const connectBtn = document.getElementById('connectBtn');
        const loadDataBtn = document.getElementById('loadDataBtn');
        const updateCIBtn = document.getElementById('updateCIBtn');
        const symbolSelect = document.getElementById('symbolSelect');
        const timeframeSelect = document.getElementById('timeframeSelect');
        const ciPeriodInput = document.getElementById('ciPeriod');
        const candlesCountInput = document.getElementById('candlesCount');
        const alertEnabled = document.getElementById('alertEnabled');

        function initCharts() {
            mainChart = LightweightCharts.createChart(document.getElementById('mainChart'), {
                width: document.getElementById('mainChart').clientWidth,
                height: 400,
                layout: { backgroundColor: '#ffffff', textColor: '#333' },
                grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
                crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                rightPriceScale: { borderColor: '#d1d4dc' },
                timeScale: { borderColor: '#d1d4dc', timeVisible: true, secondsVisible: false },
            });

            candleSeries = mainChart.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });

            const emaColors = ['#2196F3', '#FF9800', '#9C27B0', '#F44336'];
            const emaPeriods = [9, 21, 50, 200];
            
            emaPeriods.forEach((period, index) => {
                const series = mainChart.addLineSeries({
                    color: emaColors[index],
                    lineWidth: 2,
                    title: `EMA ${period}`,
                });
                emaSeries.push({ period, series });
            });

            ciChart = LightweightCharts.createChart(document.getElementById('ciChart'), {
                width: document.getElementById('ciChart').clientWidth,
                height: 200,
                layout: { backgroundColor: '#ffffff', textColor: '#333' },
                grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
                rightPriceScale: { borderColor: '#d1d4dc' },
                timeScale: { borderColor: '#d1d4dc', timeVisible: true, secondsVisible: false },
            });

            ciSeries = ciChart.addLineSeries({
                color: '#667eea',
                lineWidth: 3,
                title: 'CI',
            });

            window.addEventListener('resize', () => {
                mainChart.applyOptions({ width: document.getElementById('mainChart').clientWidth });
                ciChart.applyOptions({ width: document.getElementById('ciChart').clientWidth });
            });
        }

        async function connectToDeriv() {
            return new Promise((resolve, reject) => {
                connectBtn.textContent = '🔄 Connecting...';
                connectBtn.disabled = true;

                ws = new WebSocket(DERIV_WS_URL);

                ws.onopen = () => {
                    console.log('Connected to Deriv API');
                    isConnected = true;
                    connectBtn.textContent = '✅ Connected';
                    connectBtn.style.background = '#66bb6a';
                    loadDataBtn.disabled = false;
                    resolve();
                };

                ws.onerror = (error) => {
                    console.error('WebSocket error:', error);
                    connectBtn.textContent = '❌ Connection Failed';
                    connectBtn.disabled = false;
                    reject(error);
                };

                ws.onclose = () => {
                    console.log('Disconnected from Deriv API');
                    isConnected = false;
                    connectBtn.textContent = '🔌 Connect to Deriv';
                    connectBtn.disabled = false;
                    connectBtn.style.background = '#667eea';
                    loadDataBtn.disabled = true;
                    updateCIBtn.disabled = true;
                };

                ws.onmessage = (msg) => {
                    const data = JSON.parse(msg.data);
                    handleDerivMessage(data);
                };
            });
        }

        function handleDerivMessage(data) {
            if (data.msg_type === 'candles') {
                processCandleData(data.candles);
            } else if (data.error) {
                console.error('Deriv API Error:', data.error.message);
                alert('Error: ' + data.error.message);
            }
        }

        function loadCandleData() {
            if (!isConnected) {
                alert('Please connect to Deriv first');
                return;
            }

            const symbol = symbolSelect.value;
            const granularity = parseInt(timeframeSelect.value);
            const count = parseInt(candlesCountInput.value);

            loadDataBtn.textContent = '⏳ Loading...';
            loadDataBtn.disabled = true;
            loadDataBtn.classList.add('loading');

            const request = {
                ticks_history: symbol,
                adjust_start_time: 1,
                count: count,
                end: 'latest',
                granularity: granularity,
                style: 'candles'
            };

            ws.send(JSON.stringify(request));
        }

        function processCandleData(candles) {
            candleData = candles.map(c => ({
                time: c.epoch + (7*3600) ,
                open: parseFloat(c.open),
                high: parseFloat(c.high),
                low: parseFloat(c.low),
                close: parseFloat(c.close),
            }));

            console.log(`Loaded ${candleData.length} candles`);

            candleSeries.setData(candleData);
            calculateAndDisplayEMAs();
            calculateChoppinessIndex();

            loadDataBtn.textContent = '✅ Data Loaded';
            loadDataBtn.classList.remove('loading');
            loadDataBtn.disabled = false;
            updateCIBtn.disabled = false;

            document.getElementById('chartSymbol').textContent = `${symbolSelect.options[symbolSelect.selectedIndex].text} - ${timeframeSelect.options[timeframeSelect.selectedIndex].text}`;
        }

        function calculateEMA(data, period) {
            const k = 2 / (period + 1);
            const emaData = [];
            let ema = data[0].close;

            data.forEach((candle, index) => {
                if (index === 0) {
                    ema = candle.close;
                } else {
                    ema = (candle.close * k) + (ema * (1 - k));
                }
                emaData.push({ time: candle.time, value: ema });
            });

            return emaData;
        }

        function calculateAndDisplayEMAs() {
            emaSeries.forEach(({ period, series }) => {
                const emaData = calculateEMA(candleData, period);
                series.setData(emaData);
            });
        }

        function calculateTrueRange(current, previous) {
            if (!previous) return current.high - current.low;
            
            return Math.max(
                current.high - current.low,
                Math.abs(current.high - previous.close),
                Math.abs(current.low - previous.close)
            );
        }

        function calculateATR(data, period) {
            const atrData = [];
            let atr = 0;

            for (let i = 0; i < data.length; i++) {
                const tr = calculateTrueRange(data[i], data[i - 1]);
                
                if (i === 0) {
                    atr = tr;
                } else if (i < period) {
                    atr = ((atr * i) + tr) / (i + 1);
                } else {
                    atr = ((atr * (period - 1)) + tr) / period;
                }
                
                atrData.push(atr);
            }

            return atrData;
        }

        function calculateChoppinessIndex() {
            const period = parseInt(ciPeriodInput.value);
            const atrData = calculateATR(candleData, period);
            ciData = [];

            sidewaysMarkers.forEach(marker => {
                candleSeries.removeMarker(marker);
            });
            sidewaysMarkers = [];

            let inSidewaysZone = false;

            for (let i = period - 1; i < candleData.length; i++) {
                const slice = candleData.slice(i - period + 1, i + 1);
                const atrSlice = atrData.slice(i - period + 1, i + 1);
                
                const highest = Math.max(...slice.map(c => c.high));
                const lowest = Math.min(...slice.map(c => c.low));
                const sumATR = atrSlice.reduce((sum, atr) => sum + atr, 0);
                
                const range = highest - lowest;
                
                if (range > 0) {
                    const ci = 100 * Math.log10(sumATR / range) / Math.log10(period);
                    const clampedCI = Math.max(0, Math.min(100, ci));
                    ciData.push({
                        time: candleData[i].time,
                        value: clampedCI
                    });

                    if (clampedCI > 61.8 && !inSidewaysZone) {
                        //const markers = candleSeries.markers() || [];
                        const markers = [];
                        const newMarker = {
                            time: candleData[i].time,
                            position: 'aboveBar',
                            color: '#ef5350',
                            shape: 'circle',
                            text: '⚠️',
                        };
                        candleSeries.setMarkers([...markers, newMarker]);
                        sidewaysMarkers.push(newMarker);
                        inSidewaysZone = true;
                    } else if (clampedCI < 38.2 && inSidewaysZone) {
                        inSidewaysZone = false;
                    }
                }
            }
            console.log('ciData',ciData);
            
            ciSeries.setData(ciData);
            document.getElementById('markerCount').textContent = sidewaysMarkers.length;
			console.log('sidewaysMarkers',sidewaysMarkers)
            for (let i=0;i<=sidewaysMarkers.length-1 ;i++ ) {
				 time = sidewaysMarkers[i].time ;
				 sDate =  new Date(time * 1000).toLocaleString('th-TH');
				 console.log(sDate) ;
				 
            //return new Date(time * 1000).toLocaleString('th-TH');
            }
 
			

            if (ciData.length > 0) {
                const currentCI = ciData[ciData.length - 1].value;
                updateCIDisplay(currentCI);
                generateTradingHint(currentCI);
            }

            console.log('CI calculated:', ciData.length, 'points, Markers:', sidewaysMarkers.length);
        }

        function updateCIDisplay(ciValue) {
            const ciCard = document.getElementById('ciCard');
            const marketStateCard = document.getElementById('marketStateCard');
            
            document.getElementById('ciValue').textContent = ciValue.toFixed(2);

            let state, signal, cardClass;

            if (ciValue > 61.8) {
                state = '🔴 CHOPPY';
                signal = '🛑 STOP';
                cardClass = 'choppy';
            } else if (ciValue > 38.2) {
                state = '🟡 NEUTRAL';
                signal = '⚠️ CAUTION';
                cardClass = 'neutral';
            } else {
                state = '🟢 TRENDING';
                signal = '✅ TRADE';
                cardClass = 'trending';
            }

            document.getElementById('marketState').textContent = state;
            document.getElementById('tradingSignal').textContent = signal;

            ciCard.className = 'status-card ' + cardClass;
            marketStateCard.className = 'status-card ' + cardClass;
        }

        function generateTradingHint(ciValue) {
            const hintBox = document.getElementById('tradingHint');
            const hintIcon = document.getElementById('hintIcon');
            const hintTitle = document.getElementById('hintTitle');
            const hintContent = document.getElementById('hintContent');
            const hintDetails = document.getElementById('hintDetails');

            const currentCandle = candleData[candleData.length - 1];
            const currentPrice = currentCandle.close;

            const ema9Data = calculateEMA(candleData, 9);
            const ema21Data = calculateEMA(candleData, 21);
            
            const ema9 = ema9Data[ema9Data.length - 1].value;
            const ema21 = ema21Data[ema21Data.length - 1].value;
            const ema9Prev = ema9Data[ema9Data.length - 2].value;

            const emaUptrend = ema9 > ema21 && ema9 > ema9Prev;
            const emaDowntrend = ema9 < ema21 && ema9 < ema9Prev;

            let action = 'IDLE';
            let icon = '⏸️';
            let title = '';
            let content = '';
            let confidence = 0;
			const isColorChoppy = checkColorAlternation(candleData);

            if (ciValue > 48 || isColorChoppy) {
                action = 'IDLE';
                icon = '🛑';
                title = 'IDLE - หยุดเทรด';
                content = 'ตลาดอยู่ในโซน Sideways (Choppy) ไม่มีทิศทางชัดเจน แนะนำไม่เทรดในช่วงนี้ เพราะมีโอกาสแพ้สูง รอจนกว่า CI จะลดลงต่ำกว่า 38.2';
                confidence = 0;
            } else if (ciValue > 38.2) {
                action = 'IDLE';
                icon = '⚠️';
                title = 'CAUTION - ระวัง';
                content = 'ตลาดอยู่ในโซนกลาง ยังไม่ชัดเจนว่าจะเป็น Trend หรือ Sideways แนะนำลด Position Size หรือรอให้ชัดเจนกว่านี้';
                confidence = 30;
            } else {
                if (currentPrice > ema9 && emaUptrend) {
                    action = 'CALL';
                    icon = '📈';
                    title = 'CALL - เข้า Buy';
                    content = `ตลาดมี Uptrend ชัดเจน! ราคาอยู่เหนือ EMA9 (${ema9.toFixed(4)}) และ EMA9 อยู่เหนือ EMA21 (${ema21.toFixed(4)}) แนะนำเข้า CALL/BUY`;
                    confidence = 85;
                } else if (currentPrice < ema9 && emaDowntrend) {
                    action = 'PUT';
                    icon = '📉';
                    title = 'PUT - เข้า Sell';
                    content = `ตลาดมี Downtrend ชัดเจน! ราคาอยู่ต่ำกว่า EMA9 (${ema9.toFixed(4)}) และ EMA9 อยู่ต่ำกว่า EMA21 (${ema21.toFixed(4)}) แนะนำเข้า PUT/SELL`;
                    confidence = 85;
                } else {
                    action = 'IDLE';
                    icon = '🤔';
                    title = 'IDLE - รอสัญญาณ';
                    content = 'แม้ตลาดกำลัง Trending แต่ราคาและ EMA ยังไม่ชัดเจน ควรรอให้ราคาเข้าสู่จังหวะที่ดีกว่า';
                    confidence = 40;
                }
            }

            hintBox.className = 'trading-hint ' + action.toLowerCase();
            hintIcon.textContent = icon;
            hintTitle.textContent = title;
            hintContent.textContent = content;

            hintDetails.style.display = 'grid';
            document.getElementById('hintCI').textContent = ciValue.toFixed(2);
            
            let emaTrend = 'Sideways';
            if (emaUptrend) emaTrend = '↗️ Uptrend';
            else if (emaDowntrend) emaTrend = '↘️ Downtrend';
            document.getElementById('hintEMA').textContent = emaTrend;

            let pricePosition = 'ในกลาง';
            if (currentPrice > ema9) pricePosition = '⬆️ เหนือ EMA9';
            else if (currentPrice < ema9) pricePosition = '⬇️ ต่ำกว่า EMA9';
            document.getElementById('hintPrice').textContent = pricePosition;

            let confidenceText = `${confidence}%`;
            if (confidence >= 70) confidenceText += ' 🟢';
            else if (confidence >= 40) confidenceText += ' 🟡';
            else confidenceText += ' 🔴';
            document.getElementById('hintConfidence').textContent = confidenceText;

            // Check if signal changed and add to history + play alert
            const signalKey = `${action}-${confidence}`;
            if (signalKey !== lastSignal) {
                addToHistory(action, title, confidence, ciValue);
                lastSignal = signalKey;

                // Play alert for good signals
                if ((action === 'CALL' || action === 'PUT') && confidence >= 70 && alertEnabled.checked) {
                    playAlert();
                }
            }

            console.log('Trading Hint:', {
                action,
                ciValue: ciValue.toFixed(2),
                currentPrice: currentPrice.toFixed(4),
                ema9: ema9.toFixed(4),
                ema21: ema21.toFixed(4),
                confidence
            });
        }

        function addToHistory(action, title, confidence, ciValue) {
            const timestamp = new Date().toLocaleString('th-TH');
            
            const historyItem = {
                action,
                title,
                confidence,
                ciValue,
                timestamp
            };

            signalHistory.unshift(historyItem);
            
            // Keep only last 10
            if (signalHistory.length > 10) {
                signalHistory = signalHistory.slice(0, 10);
            }

            updateHistoryDisplay();
        }

        function updateHistoryDisplay() {
            const historyList = document.getElementById('historyList');
            
            if (signalHistory.length === 0) {
                historyList.innerHTML = `
                    <div style="text-align: center; color: #888; padding: 20px;">
                        No signals yet. Load data to see trading hints.
                    </div>
                `;
                return;
            }

            historyList.innerHTML = signalHistory.map(item => {
                const iconMap = {
                    'CALL': '📈',
                    'PUT': '📉',
                    'IDLE': '⏸️'
                };

                const colorMap = {
                    'CALL': 'call',
                    'PUT': 'put',
                    'IDLE': 'idle'
                };

                return `
                    <div class="history-item ${colorMap[item.action]}">
                        <div class="history-icon">${iconMap[item.action]}</div>
                        <div class="history-content">
                            <div class="history-action">${item.title}</div>
                            <div class="history-time">🕒 ${item.timestamp} | CI: ${item.ciValue.toFixed(2)}</div>
                        </div>
                        <div class="history-confidence">${item.confidence}%</div>
                    </div>
                `;
            }).join('');
        }

        function clearHistory() {
            if (confirm('Clear all signal history?')) {
                signalHistory = [];
                lastSignal = null;
                updateHistoryDisplay();
            }
        }

		// ตรวจจับการสลับสีโดยตรง
function checkColorAlternation(candles, count = 3) {
  let alternations = 0;
  console.log('Candle Length= ',candles);
  for (let i = 1; i < candles.length; i++) {
    const prev = candles[i-1];
    const curr = candles[i];
    
    const prevColor = prev.close > prev.open ? 'green' : 'red';
    const currColor = curr.close > curr.open ? 'green' : 'red';
    
    if (prevColor !== currColor) {
      alternations++;
      if (alternations >= count) {
        return true; // พบ Sideways!
      }
    } else {
      alternations = 0; // Reset
    }
  }
  return false;
}

function AddMarkerSideWays() {

         sidewaysMarkersList = [] ;
         for (let i=0;i<=sidewaysMarkers.length-1 ;i++ ) {
             const newMarker = {
                  time: sidewaysMarkers[i].time,
                  position: 'belowBar',
                  color: '#ef5350',
                  shape: 'circle',
                  text: '⚠️',
             };
	         sidewaysMarkersList.push(newMarker);
         } 

		 for (let i=0;i<=ciData.length-1 ;i++ ) {
			 if (ciData[i].value >=60) {			 
                const newMarker = {
                  time: ciData[i].time,
                  position: 'aboveBar',
                  color: '#ef5350',
                  shape: 'circle',
                  text: '🌡️',
                };
	            sidewaysMarkersList.push(newMarker);
			 }
         } 



		 candleSeries.setMarkers(sidewaysMarkersList);



} // end func


        function playAlert() {
            // Create audio context and play beep sound
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);

                console.log('🔔 Alert played!');
            } catch (e) {
                console.error('Could not play alert:', e);
            }
        }

        // Event Listeners
        connectBtn.addEventListener('click', connectToDeriv);
        loadDataBtn.addEventListener('click', loadCandleData);
        updateCIBtn.addEventListener('click', calculateChoppinessIndex);

        // Initialize
        initCharts();
    </script>
</body>
</html>

//return new Date(time * 1000).toLocaleString('th-TH');