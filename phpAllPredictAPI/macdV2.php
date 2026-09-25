<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Candle Data Analysis</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 30px;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.5em;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 10px;
        }
        .control-group {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #4a5568;
            font-size: 0.9em;
        }
        input, select {
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            grid-column: span 2;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        button:active {
            transform: translateY(0);
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .chart-container {
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        #mainChart {
            height: 500px;
        }
        #macdChart {
            height: 200px;
        }
        .status {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .status.info {
            background: #bee3f8;
            color: #2c5282;
        }
        .status.error {
            background: #fed7d7;
            color: #9b2c2c;
        }
        .status.success {
            background: #c6f6d5;
            color: #276749;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .debug-info {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 300px;
            overflow-y: auto;
        }
        .debug-info h3 {
            color: #4fd1c5;
            margin-bottom: 10px;
        }
        .debug-info pre {
            margin: 5px 0;
            white-space: pre-wrap;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:hover {
            background: #f7fafc;
        }
        .trend-up {
            color: #38a169;
            font-weight: 600;
        }
        .trend-down {
            color: #e53e3e;
            font-weight: 600;
        }
        .consolidation {
            color: #805ad5;
            font-weight: 600;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loading.active {
            display: block;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📈 Deriv Candle Data Analysis</h1>
        
        <div class="controls">
            <div class="control-group">
                <label>Symbol:</label>
                <select id="symbol">
                    <option value="R_100">Volatility 100 Index</option>
                    <option value="R_50">Volatility 50 Index</option>
                    <option value="R_25">Volatility 25 Index</option>
                    <option value="R_10">Volatility 10 Index</option>
                    <option value="frxEURUSD">EUR/USD</option>
                    <option value="frxGBPUSD">GBP/USD</option>
                </select>
            </div>
            
            <div class="control-group">
                <label>Timeframe:</label>
                <select id="granularity">
                    <option value="60">1 Minute</option>
                    <option value="300">5 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="3600">1 Hour</option>
                    <option value="14400">4 Hours</option>
                    <option value="86400">1 Day</option>
                </select>
            </div>
            
            <div class="control-group">
                <label>Start Date:</label>
                <input type="datetime-local" id="startDate">
            </div>
            
            <div class="control-group">
                <label>End Date:</label>
                <input type="datetime-local" id="endDate">
            </div>
            
            <div class="control-group">
                <label>EMA Short Period:</label>
                <input type="number" id="emaShort" value="12" min="1">
            </div>
            
            <div class="control-group">
                <label>EMA Long Period:</label>
                <input type="number" id="emaLong" value="26" min="1">
            </div>
            
            <div class="control-group">
                <label>MACD Signal Period:</label>
                <input type="number" id="macdSignal" value="9" min="1">
            </div>
            
            <div class="control-group">
                <label>Consolidation Percentile:</label>
                <input type="number" id="consolidationThreshold" value="30" step="5" min="5" max="50">
                <small style="color: #718096; font-size: 0.85em;">% of lowest scores (lower = stricter)</small>
            </div>
            
            <button id="loadBtn" onclick="loadData()">Load Data & Analyze</button>
        </div>
        
        <div id="status" class="status" style="display:none;"></div>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p style="margin-top: 10px;">Loading data from Deriv...</p>
        </div>
        
        <div id="debugInfo" class="debug-info" style="display:none;">
            <h3>🔍 Debug Information</h3>
            <div id="debugContent"></div>
        </div>
        
        <div class="chart-container">
            <div id="mainChart"></div>
        </div>
        
        <div class="chart-container">
            <div id="macdChart"></div>
        </div>
        
        <h2 style="margin-top: 30px; color: #2d3748;">📊 Trend Analysis</h2>
        <table id="trendTable">
            <thead>
                <tr>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Price Change</th>
                    <th>Change %</th>
                </tr>
            </thead>
            <tbody id="trendTableBody">
                <tr><td colspan="6" style="text-align:center; color: #718096;">Load data to see trends</td></tr>
            </tbody>
        </table>
    </div>

    <script>
        let ws = null;
        let mainChart = null;
        let macdChart = null;
        let candleSeries = null;
        let emaShortSeries = null;
        let emaLongSeries = null;
        let macdLineSeries = null;
        let signalLineSeries = null;
        let histogramSeries = null;
        let consolidationMarkers = [];

        // Initialize default dates
        const now = new Date();
        const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        document.getElementById('endDate').value = now.toISOString().slice(0, 16);
        document.getElementById('startDate').value = yesterday.toISOString().slice(0, 16);

        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = `status ${type}`;
            status.style.display = 'block';
        }

        function calculateEMA(data, period) {
            const k = 2 / (period + 1);
            const emaData = [];
            let ema = data[0].close;
            
            for (let i = 0; i < data.length; i++) {
                ema = data[i].close * k + ema * (1 - k);
                emaData.push({ time: data[i].time, value: ema });
            }
            
            return emaData;
        }

        function calculateMACD(data, shortPeriod, longPeriod, signalPeriod) {
            const emaShort = calculateEMA(data, shortPeriod);
            const emaLong = calculateEMA(data, longPeriod);
            
            const macdLine = emaShort.map((item, i) => ({
                time: item.time,
                value: item.value - emaLong[i].value
            }));
            
            const signalLine = calculateEMA(
                macdLine.map((item, i) => ({ time: item.time, close: item.value })),
                signalPeriod
            );
            
            const histogram = macdLine.map((item, i) => ({
                time: item.time,
                value: item.value - signalLine[i].value,
                color: item.value - signalLine[i].value >= 0 ? '#26a69a' : '#ef5350'
            }));
            
            return { macdLine, signalLine, histogram };
        }

        function calculateATR(data, period = 14) {
            const tr = [];
            
            for (let i = 1; i < data.length; i++) {
                const high = data[i].high;
                const low = data[i].low;
                const prevClose = data[i - 1].close;
                
                const tr1 = high - low;
                const tr2 = Math.abs(high - prevClose);
                const tr3 = Math.abs(low - prevClose);
                
                tr.push(Math.max(tr1, tr2, tr3));
            }
            
            const atr = [];
            let sum = tr.slice(0, period).reduce((a, b) => a + b, 0);
            atr.push(sum / period);
            
            for (let i = period; i < tr.length; i++) {
                const newATR = (atr[atr.length - 1] * (period - 1) + tr[i]) / period;
                atr.push(newATR);
            }
            
            return atr;
        }

        function calculateBollingerBands(data, period = 20, stdDev = 2) {
            const bands = [];
            
            for (let i = period - 1; i < data.length; i++) {
                const slice = data.slice(i - period + 1, i + 1);
                const closes = slice.map(c => c.close);
                
                const sma = closes.reduce((a, b) => a + b, 0) / period;
                const variance = closes.reduce((sum, price) => sum + Math.pow(price - sma, 2), 0) / period;
                const std = Math.sqrt(variance);
                
                bands.push({
                    time: data[i].time,
                    upper: sma + (stdDev * std),
                    middle: sma,
                    lower: sma - (stdDev * std),
                    bandwidth: (2 * stdDev * std) / sma * 100,
                    std: std
                });
            }
            
            return bands;
        }

        function calculateStdDev(values) {
            const mean = values.reduce((a, b) => a + b, 0) / values.length;
            const variance = values.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) / values.length;
            return Math.sqrt(variance);
        }

        function detectConsolidation(data, threshold) {
            const consolidationZones = [];
            const period = 20;
            const atrPeriod = 14;
            
            // Calculate indicators
            const atr = calculateATR(data, atrPeriod);
            const bb = calculateBollingerBands(data, period, 2);
            
            let consolidationScores = [];
            
            // ต้องมีข้อมูลพอสำหรับคำนวณ
            const startIndex = Math.max(period, atrPeriod + 1);
            
            for (let i = startIndex; i < data.length; i++) {
                const atrIndex = i - 1;
                const bbIndex = i - period;
                
                if (atrIndex < 0 || atrIndex >= atr.length || bbIndex < 0 || bbIndex >= bb.length) {
                    continue;
                }
                
                const currentATR = atr[atrIndex];
                const currentBB = bb[bbIndex];
                
                // 1. ATR-based volatility
                const recentPrices = data.slice(i - period, i + 1).map(c => c.close);
                const avgPrice = recentPrices.reduce((a, b) => a + b, 0) / recentPrices.length;
                const atrPercent = (currentATR / avgPrice) * 100;
                
                // 2. Bollinger Bands Squeeze
                const bbSqueezeScore = currentBB.bandwidth;
                
                // 3. Price Standard Deviation
                const priceStdDev = calculateStdDev(recentPrices);
                const stdDevPercent = (priceStdDev / avgPrice) * 100;
                
                // Composite Score
                const consolidationScore = (
                    atrPercent * 0.4 +
                    bbSqueezeScore * 0.3 +
                    stdDevPercent * 0.3
                );
                
                consolidationScores.push({
                    time: data[i].time,
                    score: consolidationScore,
                    atr: atrPercent,
                    bb: bbSqueezeScore,
                    stdDev: stdDevPercent,
                    index: i
                });
            }
            
            // คำนวณ dynamic threshold จาก percentile
            const allScores = consolidationScores.map(s => s.score).sort((a, b) => a - b);
            const percentileIndex = Math.floor(allScores.length * (threshold / 100));
            const dynamicThreshold = allScores[percentileIndex];
            
            console.log(`Dynamic threshold at ${threshold}th percentile: ${dynamicThreshold.toFixed(3)}`);
            
            // ตรวจจับ consolidation zones ด้วย dynamic threshold
            let zoneStart = null;
            let scoresInZone = [];
            let indicesInZone = [];
            
            for (let i = 0; i < consolidationScores.length; i++) {
                const cs = consolidationScores[i];
                const isConsolidation = cs.score <= dynamicThreshold;
                
                if (isConsolidation) {
                    if (!zoneStart) {
                        zoneStart = cs.time;
                        scoresInZone = [cs.score];
                        indicesInZone = [cs.index];
                    } else {
                        scoresInZone.push(cs.score);
                        indicesInZone.push(cs.index);
                    }
                } else {
                    // ตรวจสอบว่า zone มีขนาดเหมาะสม
                    if (zoneStart && scoresInZone.length >= 5) {
                        const avgScore = scoresInZone.reduce((a, b) => a + b, 0) / scoresInZone.length;
                        const startIdx = indicesInZone[0];
                        const endIdx = indicesInZone[indicesInZone.length - 1];
                        
                        // คำนวณค่าเฉลี่ย ATR, BB, StdDev
                        const relevantScores = consolidationScores.filter(s => 
                            indicesInZone.includes(s.index)
                        );
                        
                        const avgATR = relevantScores.reduce((sum, s) => sum + s.atr, 0) / relevantScores.length;
                        const avgBB = relevantScores.reduce((sum, s) => sum + s.bb, 0) / relevantScores.length;
                        const avgStdDev = relevantScores.reduce((sum, s) => sum + s.stdDev, 0) / relevantScores.length;
                        
                        consolidationZones.push({
                            start: data[startIdx].time,
                            end: data[endIdx].time,
                            avgScore: avgScore.toFixed(3),
                            bars: scoresInZone.length,
                            details: {
                                atr: avgATR.toFixed(3),
                                bb: avgBB.toFixed(3),
                                stdDev: avgStdDev.toFixed(3)
                            }
                        });
                    }
                    
                    zoneStart = null;
                    scoresInZone = [];
                    indicesInZone = [];
                }
            }
            
            // จัดการ zone สุดท้าย
            if (zoneStart && scoresInZone.length >= 5) {
                const avgScore = scoresInZone.reduce((a, b) => a + b, 0) / scoresInZone.length;
                const startIdx = indicesInZone[0];
                const endIdx = indicesInZone[indicesInZone.length - 1];
                
                const relevantScores = consolidationScores.filter(s => 
                    indicesInZone.includes(s.index)
                );
                
                const avgATR = relevantScores.reduce((sum, s) => sum + s.atr, 0) / relevantScores.length;
                const avgBB = relevantScores.reduce((sum, s) => sum + s.bb, 0) / relevantScores.length;
                const avgStdDev = relevantScores.reduce((sum, s) => sum + s.stdDev, 0) / relevantScores.length;
                
                consolidationZones.push({
                    start: data[startIdx].time,
                    end: data[endIdx].time,
                    avgScore: avgScore.toFixed(3),
                    bars: scoresInZone.length,
                    details: {
                        atr: avgATR.toFixed(3),
                        bb: avgBB.toFixed(3),
                        stdDev: avgStdDev.toFixed(3)
                    }
                });
            }
            
            console.log(`Found ${consolidationZones.length} consolidation zones from ${data.length} candles`);
            console.log('Score range:', {
                min: Math.min(...allScores).toFixed(3),
                max: Math.max(...allScores).toFixed(3),
                dynamicThreshold: dynamicThreshold.toFixed(3),
                inputPercentile: threshold
            });
            
            return { zones: consolidationZones, scores: consolidationScores };
        }

        function detectTrends(data, emaShort, emaLong) {
            const trends = [];
            let currentTrend = null;
            
            for (let i = 1; i < data.length; i++) {
                const prevShort = emaShort[i - 1].value;
                const prevLong = emaLong[i - 1].value;
                const currShort = emaShort[i].value;
                const currLong = emaLong[i].value;
                
                let trendType = null;
                if (currShort > currLong && prevShort > prevLong) {
                    trendType = 'Uptrend';
                } else if (currShort < currLong && prevShort < prevLong) {
                    trendType = 'Downtrend';
                }
                
                if (trendType) {
                    if (!currentTrend || currentTrend.type !== trendType) {
                        if (currentTrend) {
                            currentTrend.endTime = data[i - 1].time;
                            currentTrend.endPrice = data[i - 1].close;
                            trends.push(currentTrend);
                        }
                        currentTrend = {
                            type: trendType,
                            startTime: data[i].time,
                            startPrice: data[i].close
                        };
                    }
                } else {
                    if (currentTrend) {
                        currentTrend.endTime = data[i - 1].time;
                        currentTrend.endPrice = data[i - 1].close;
                        trends.push(currentTrend);
                        currentTrend = null;
                    }
                }
            }
            
            if (currentTrend) {
                currentTrend.endTime = data[data.length - 1].time;
                currentTrend.endPrice = data[data.length - 1].close;
                trends.push(currentTrend);
            }
            
            return trends;
        }

        function formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        }

        function updateTrendTable(trends) {
            const tbody = document.getElementById('trendTableBody');
            tbody.innerHTML = '';
            
            if (trends.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color: #718096;">No clear trends detected</td></tr>';
                return;
            }
            
            trends.forEach(trend => {
                const row = document.createElement('tr');
                const duration = trend.endTime - trend.startTime;
                const priceChange = trend.endPrice - trend.startPrice;
                const changePercent = ((priceChange / trend.startPrice) * 100).toFixed(2);
                
                const trendClass = trend.type === 'Uptrend' ? 'trend-up' : 'trend-down';
                
                row.innerHTML = `
                    <td>${new Date(trend.startTime * 1000).toLocaleString('th-TH')}</td>
                    <td>${new Date(trend.endTime * 1000).toLocaleString('th-TH')}</td>
                    <td class="${trendClass}">${trend.type}</td>
                    <td>${formatDuration(duration)}</td>
                    <td>${priceChange.toFixed(5)}</td>
                    <td class="${trendClass}">${changePercent}%</td>
                `;
                tbody.appendChild(row);
            });
        }

        function initCharts() {
            const chartOptions = {
                layout: {
                    background: { color: '#ffffff' },
                    textColor: '#333',
                },
                grid: {
                    vertLines: { color: '#f0f0f0' },
                    horzLines: { color: '#f0f0f0' },
                },
                timeScale: {
                    timeVisible: true,
                    secondsVisible: false,
                },
            };

            mainChart = LightweightCharts.createChart(document.getElementById('mainChart'), {
                ...chartOptions,
                height: 500,
            });

            macdChart = LightweightCharts.createChart(document.getElementById('macdChart'), {
                ...chartOptions,
                height: 200,
            });

            candleSeries = mainChart.addCandlestickSeries();
            emaShortSeries = mainChart.addLineSeries({ color: '#2962FF', lineWidth: 2 });
            emaLongSeries = mainChart.addLineSeries({ color: '#FF6D00', lineWidth: 2 });
            
            macdLineSeries = macdChart.addLineSeries({ color: '#2962FF', lineWidth: 2 });
            signalLineSeries = macdChart.addLineSeries({ color: '#FF6D00', lineWidth: 2 });
            histogramSeries = macdChart.addHistogramSeries();
        }

        async function loadData() {
            const symbol = document.getElementById('symbol').value;
            const granularity = parseInt(document.getElementById('granularity').value);
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            const emaShortPeriod = parseInt(document.getElementById('emaShort').value);
            const emaLongPeriod = parseInt(document.getElementById('emaLong').value);
            const macdSignalPeriod = parseInt(document.getElementById('macdSignal').value);
            const consolidationThreshold = parseFloat(document.getElementById('consolidationThreshold').value);
            
            if (!startDate || !endDate) {
                showStatus('กรุณาเลือกวันที่เริ่มต้นและสิ้นสุด', 'error');
                return;
            }
            
            document.getElementById('loadBtn').disabled = true;
            document.getElementById('loading').classList.add('active');
            showStatus('กำลังเชื่อมต่อกับ Deriv API...', 'info');
            
            try {
                if (ws) ws.close();
                
                ws = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');
                
                ws.onopen = () => {
                    showStatus('เชื่อมต่อสำเร็จ กำลังโหลดข้อมูล...', 'info');
                    
                    ws.send(JSON.stringify({
                        ticks_history: symbol,
                        adjust_start_time: 1,
                        count: 5000,
                        end: Math.floor(endDate.getTime() / 1000),
                        start: Math.floor(startDate.getTime() / 1000),
                        style: 'candles',
                        granularity: granularity
                    }));
                };
                
                ws.onmessage = (msg) => {
                    const data = JSON.parse(msg.data);
                    
                    if (data.error) {
                        showStatus('Error: ' + data.error.message, 'error');
                        document.getElementById('loadBtn').disabled = false;
                        document.getElementById('loading').classList.remove('active');
                        return;
                    }
                    
                    if (data.candles) {
                        const candles = data.candles.map(c => ({
                            time: c.epoch,
                            open: parseFloat(c.open),
                            high: parseFloat(c.high),
                            low: parseFloat(c.low),
                            close: parseFloat(c.close)
                        }));
                        
                        if (!mainChart) initCharts();
                        
                        // Calculate indicators
                        const emaShort = calculateEMA(candles, emaShortPeriod);
                        const emaLong = calculateEMA(candles, emaLongPeriod);
                        const { macdLine, signalLine, histogram } = calculateMACD(candles, emaShortPeriod, emaLongPeriod, macdSignalPeriod);
                        
                        console.log('=== DEBUG INFO ===');
                        console.log('Total candles:', candles.length);
                        console.log('Threshold:', consolidationThreshold);
                        
                        const { zones: consolidationZones, scores: consolidationScores } = detectConsolidation(candles, consolidationThreshold);
                        
                        console.log('Consolidation Zones found:', consolidationZones.length);
                        
                        let debugHTML = `<pre>Total Candles: ${candles.length}\nThreshold: ${consolidationThreshold}\nZones Found: ${consolidationZones.length}\n\n`;
                        
                        if (consolidationScores.length > 0) {
                            const allScores = consolidationScores.map(s => s.score);
                            const scoreStats = {
                                min: Math.min(...allScores).toFixed(3),
                                max: Math.max(...allScores).toFixed(3),
                                avg: (allScores.reduce((a,b) => a+b, 0) / allScores.length).toFixed(3),
                                threshold: consolidationThreshold
                            };
                            
                            console.log('Score Statistics:', scoreStats);
                            debugHTML += `Score Stats:\n  Min: ${scoreStats.min}\n  Max: ${scoreStats.max}\n  Avg: ${scoreStats.avg}\n  Threshold: ${scoreStats.threshold}\n\n`;
                            
                            // Show score distribution
                            const belowThreshold = allScores.filter(s => s < consolidationThreshold).length;
                            console.log(`Scores below threshold: ${belowThreshold} / ${allScores.length} (${(belowThreshold/allScores.length*100).toFixed(1)}%)`);
                            debugHTML += `Scores < Threshold: ${belowThreshold} / ${allScores.length} (${(belowThreshold/allScores.length*100).toFixed(1)}%)\n\n`;
                            
                            // Show sample scores
                            debugHTML += `Sample Scores (first 5):\n`;
                            consolidationScores.slice(0, 5).forEach(s => {
                                debugHTML += `  ${new Date(s.time * 1000).toLocaleTimeString()} - Score: ${s.score.toFixed(3)} (ATR: ${s.atr.toFixed(3)}, BB: ${s.bb.toFixed(3)}, Std: ${s.stdDev.toFixed(3)})\n`;
                            });
                            
                            if (consolidationZones.length > 0) {
                                debugHTML += `\nConsolidation Zones:\n`;
                                consolidationZones.forEach((zone, i) => {
                                    debugHTML += `  ${i+1}. ${new Date(zone.start * 1000).toLocaleString()} to ${new Date(zone.end * 1000).toLocaleString()}\n`;
                                    debugHTML += `     Bars: ${zone.bars}, Avg Score: ${zone.avgScore}\n`;
                                });
                            }
                        }
                        
                        debugHTML += `</pre>`;
                        document.getElementById('debugInfo').style.display = 'block';
                        document.getElementById('debugContent').innerHTML = debugHTML;
                        
                        const trends = detectTrends(candles, emaShort, emaLong);
                        
                        // Update charts
                        candleSeries.setData(candles);
                        emaShortSeries.setData(emaShort);
                        emaLongSeries.setData(emaLong);
                        macdLineSeries.setData(macdLine);
                        signalLineSeries.setData(signalLine);
                        histogramSeries.setData(histogram);
                        
                        // Add consolidation markers with enhanced details
                        const markers = consolidationZones.flatMap(zone => [
                            {
                                time: zone.start,
                                position: 'belowBar',
                                color: '#9C27B0',
                                shape: 'arrowUp',
                                text: `📊 Consolidation (${zone.bars} bars)\nScore: ${zone.avgScore} | ATR: ${zone.details.atr}%\nBB: ${zone.details.bb}% | StdDev: ${zone.details.stdDev}%`
                            },
                            {
                                time: zone.end,
                                position: 'belowBar',
                                color: '#9C27B0',
                                shape: 'arrowDown',
                                text: `End`
                            }
                        ]);
                        candleSeries.setMarkers(markers);
                        
                        // Update trend table
                        updateTrendTable(trends);
                        
                        showStatus(`โหลดข้อมูลสำเร็จ! ได้ ${candles.length} แท่งเทียน, พบ ${trends.length} Trends และ ${consolidationZones.length} Consolidation Zones`, 'success');
                        document.getElementById('loadBtn').disabled = false;
                        document.getElementById('loading').classList.remove('active');
                        
                        ws.close();
                    }
                };
                
                ws.onerror = (error) => {
                    showStatus('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                    document.getElementById('loadBtn').disabled = false;
                    document.getElementById('loading').classList.remove('active');
                };
                
            } catch (error) {
                showStatus('Error: ' + error.message, 'error');
                document.getElementById('loadBtn').disabled = false;
                document.getElementById('loading').classList.remove('active');
            }
        }

        // Initialize charts on load
        window.addEventListener('load', () => {
            initCharts();
        });
    </script>
</body>
</html>