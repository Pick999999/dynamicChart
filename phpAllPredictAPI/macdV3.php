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
        .live-panel {
            display: none;
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .live-panel.active {
            display: block;
        }
        .live-panel h2 {
            color: #4fd1c5;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        .live-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .live-card {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .live-card h3 {
            font-size: 0.9em;
            color: #a0aec0;
            margin-bottom: 8px;
        }
        .live-value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .live-value.good {
            color: #48bb78;
        }
        .live-value.warning {
            color: #ed8936;
        }
        .live-value.bad {
            color: #f56565;
        }
        .live-value.neutral {
            color: #4299e1;
        }
        .probability-bar {
            width: 100%;
            height: 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }
        .probability-fill {
            height: 100%;
            background: linear-gradient(90deg, #48bb78 0%, #38a169 100%);
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
        }
        .signal-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        .signal-indicator.consolidation {
            background: #805ad5;
        }
        .signal-indicator.trending {
            background: #4299e1;
        }
        .signal-indicator.breakout {
            background: #f56565;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .trend-arrow {
            display: inline-block;
            font-size: 1.5em;
            margin-left: 10px;
        }
        .realtime-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 15px;
            background: #f7fafc;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e0;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .realtime-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
            animation: pulse-badge 2s infinite;
        }
        @keyframes pulse-badge {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 101, 101, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(245, 101, 101, 0); }
        }
        .realtime-dot {
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
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
            
            <div class="control-group" id="startDateGroup">
                <label>Start Date:</label>
                <input type="datetime-local" id="startDate">
            </div>
            
            <div class="control-group" id="endDateGroup">
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
        
        <div class="realtime-controls">
            <label class="toggle-switch">
                <input type="checkbox" id="realtimeToggle" onchange="toggleRealtimeMode()">
                <span class="slider"></span>
            </label>
            <div>
                <strong style="color: #2d3748;">Real-time Mode</strong>
                <p style="font-size: 0.85em; color: #718096; margin: 0;">Update graph every second with live data</p>
            </div>
            <div id="realtimeBadge" style="display: none; margin-left: auto;">
                <span class="realtime-badge">
                    <span class="realtime-dot"></span>
                    LIVE
                </span>
            </div>
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
        
        <div id="livePanel" class="live-panel">
            <h2>🔴 Live Consolidation Monitor</h2>
            <div class="live-grid">
                <div class="live-card">
                    <h3>Current Status</h3>
                    <div class="live-value neutral" id="liveStatus">
                        <span class="signal-indicator consolidation"></span>
                        Waiting...
                    </div>
                </div>
                
                <div class="live-card">
                    <h3>Consolidation Score</h3>
                    <div class="live-value neutral" id="liveScore">--</div>
                    <small id="liveScoreCompare" style="color: #a0aec0;"></small>
                </div>
                
                <div class="live-card">
                    <h3>ATR Volatility</h3>
                    <div class="live-value neutral" id="liveATR">--</div>
                    <small style="color: #a0aec0;">Lower = less volatile</small>
                </div>
                
                <div class="live-card">
                    <h3>BB Bandwidth</h3>
                    <div class="live-value neutral" id="liveBB">--</div>
                    <small style="color: #a0aec0;">Lower = tighter range</small>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <h3 style="color: #4fd1c5; margin-bottom: 15px;">Entry Probability</h3>
                <div class="probability-bar">
                    <div class="probability-fill" id="entryProbBar" style="width: 0%;">0%</div>
                </div>
                <p style="margin-top: 10px; color: #a0aec0; font-size: 0.9em;" id="entrySignal">Analyzing...</p>
            </div>
            
            <div style="margin-top: 20px;">
                <h3 style="color: #ed8936; margin-bottom: 15px;">Exit Probability (Breakout)</h3>
                <div class="probability-bar">
                    <div class="probability-fill" id="exitProbBar" style="width: 0%; background: linear-gradient(90deg, #f56565 0%, #e53e3e 100%);">0%</div>
                </div>
                <p style="margin-top: 10px; color: #a0aec0; font-size: 0.9em;" id="exitSignal">Analyzing...</p>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 10px;">
                <h3 style="color: #4fd1c5; margin-bottom: 10px;">📊 Historical Context</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; font-size: 0.9em;">
                    <div>
                        <strong style="color: #a0aec0;">Avg Score:</strong>
                        <span id="avgHistScore">--</span>
                    </div>
                    <div>
                        <strong style="color: #a0aec0;">Threshold:</strong>
                        <span id="histThreshold">--</span>
                    </div>
                    <div>
                        <strong style="color: #a0aec0;">Zones Found:</strong>
                        <span id="histZones">--</span>
                    </div>
                </div>
            </div>
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
        let liveMonitorData = null;
        let realtimeInterval = null;
        let realtimeWS = null;
        let realtimeCandles = [];
        let isRealtimeMode = false;

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

        function calculateLiveConsolidationMetrics(data, currentIndex, historicalScores, dynamicThreshold) {
            const period = 20;
            const atrPeriod = 14;
            
            // ต้องมีข้อมูลพอ
            if (currentIndex < Math.max(period, atrPeriod + 1)) {
                return null;
            }
            
            // คำนวณ indicators สำหรับจุดปัจจุบัน
            const atr = calculateATR(data.slice(0, currentIndex + 1), atrPeriod);
            const bb = calculateBollingerBands(data.slice(0, currentIndex + 1), period, 2);
            
            if (atr.length === 0 || bb.length === 0) return null;
            
            const currentATR = atr[atr.length - 1];
            const currentBB = bb[bb.length - 1];
            
            const recentPrices = data.slice(currentIndex - period + 1, currentIndex + 1).map(c => c.close);
            const avgPrice = recentPrices.reduce((a, b) => a + b, 0) / recentPrices.length;
            const atrPercent = (currentATR / avgPrice) * 100;
            const bbSqueezeScore = currentBB.bandwidth;
            const priceStdDev = calculateStdDev(recentPrices);
            const stdDevPercent = (priceStdDev / avgPrice) * 100;
            
            const currentScore = (
                atrPercent * 0.4 +
                bbSqueezeScore * 0.3 +
                stdDevPercent * 0.3
            );
            
            // คำนวณ Entry Probability
            // ถ้า score ต่ำกว่า threshold มาก = โอกาสเข้า consolidation สูง
            const scoreRatio = currentScore / dynamicThreshold;
            let entryProb = 0;
            
            if (scoreRatio <= 0.7) {
                entryProb = 85 + (Math.random() * 10); // 85-95%
            } else if (scoreRatio <= 0.9) {
                entryProb = 65 + (Math.random() * 15); // 65-80%
            } else if (scoreRatio <= 1.1) {
                entryProb = 40 + (Math.random() * 20); // 40-60%
            } else if (scoreRatio <= 1.3) {
                entryProb = 20 + (Math.random() * 15); // 20-35%
            } else {
                entryProb = 5 + (Math.random() * 10); // 5-15%
            }
            
            // คำนวณ Exit Probability (Breakout)
            // ดูจาก rate of change ของ ATR และ BB
            if (currentIndex >= 5) {
                const prevScores = historicalScores.slice(-5);
                const scoreChange = prevScores.map((s, i) => {
                    if (i === 0) return 0;
                    return s.score - prevScores[i - 1].score;
                });
                
                const avgChange = scoreChange.reduce((a, b) => a + b, 0) / scoreChange.length;
                const atrChange = atrPercent - prevScores[prevScores.length - 1].atr;
                const bbChange = bbSqueezeScore - prevScores[prevScores.length - 1].bb;
                
                let exitProb = 0;
                
                // ถ้า indicators เริ่มขยายตัว = breakout
                if (avgChange > 0.05 || atrChange > 0.02 || bbChange > 0.1) {
                    exitProb = 60 + (Math.random() * 25); // 60-85%
                } else if (avgChange > 0.02 || atrChange > 0.01) {
                    exitProb = 35 + (Math.random() * 20); // 35-55%
                } else if (scoreRatio > 1.2) {
                    exitProb = 20 + (Math.random() * 15); // 20-35%
                } else {
                    exitProb = 5 + (Math.random() * 10); // 5-15%
                }
                
                return {
                    score: currentScore,
                    atr: atrPercent,
                    bb: bbSqueezeScore,
                    stdDev: stdDevPercent,
                    threshold: dynamicThreshold,
                    entryProbability: Math.min(95, entryProb),
                    exitProbability: Math.min(90, exitProb),
                    status: scoreRatio <= 1.0 ? 'In Consolidation' : 
                            scoreRatio <= 1.2 ? 'Borderline' : 'Trending',
                    scoreRatio: scoreRatio
                };
            }
            
            return {
                score: currentScore,
                atr: atrPercent,
                bb: bbSqueezeScore,
                stdDev: stdDevPercent,
                threshold: dynamicThreshold,
                entryProbability: Math.min(95, entryProb),
                exitProbability: 10,
                status: scoreRatio <= 1.0 ? 'In Consolidation' : 'Trending',
                scoreRatio: scoreRatio
            };
        }

        function updateLivePanel(liveMetrics, historicalData) {
            if (!liveMetrics) return;
            
            const livePanel = document.getElementById('livePanel');
            livePanel.classList.add('active');
            
            // Status
            const statusEl = document.getElementById('liveStatus');
            const indicatorClass = liveMetrics.status === 'In Consolidation' ? 'consolidation' :
                                   liveMetrics.status === 'Borderline' ? 'trending' : 'breakout';
            statusEl.innerHTML = `<span class="signal-indicator ${indicatorClass}"></span>${liveMetrics.status}`;
            
            // Score
            const scoreEl = document.getElementById('liveScore');
            const scoreClass = liveMetrics.scoreRatio <= 0.8 ? 'good' :
                              liveMetrics.scoreRatio <= 1.0 ? 'warning' : 'bad';
            scoreEl.className = `live-value ${scoreClass}`;
            scoreEl.textContent = liveMetrics.score.toFixed(3);
            
            document.getElementById('liveScoreCompare').textContent = 
                `Threshold: ${liveMetrics.threshold.toFixed(3)} (${(liveMetrics.scoreRatio * 100).toFixed(0)}%)`;
            
            // ATR
            const atrEl = document.getElementById('liveATR');
            const atrClass = liveMetrics.atr < 0.2 ? 'good' : 
                           liveMetrics.atr < 0.3 ? 'warning' : 'bad';
            atrEl.className = `live-value ${atrClass}`;
            atrEl.textContent = liveMetrics.atr.toFixed(3) + '%';
            
            // BB
            const bbEl = document.getElementById('liveBB');
            const bbClass = liveMetrics.bb < 0.8 ? 'good' : 
                          liveMetrics.bb < 1.2 ? 'warning' : 'bad';
            bbEl.className = `live-value ${bbClass}`;
            bbEl.textContent = liveMetrics.bb.toFixed(3) + '%';
            
            // Entry Probability
            const entryBar = document.getElementById('entryProbBar');
            const entryProb = liveMetrics.entryProbability.toFixed(0);
            entryBar.style.width = entryProb + '%';
            entryBar.textContent = entryProb + '%';
            
            let entrySignalText = '';
            if (liveMetrics.entryProbability >= 70) {
                entrySignalText = '🟢 High probability entering consolidation zone';
            } else if (liveMetrics.entryProbability >= 50) {
                entrySignalText = '🟡 Moderate probability - monitor closely';
            } else if (liveMetrics.entryProbability >= 30) {
                entrySignalText = '🟠 Low probability - likely trending';
            } else {
                entrySignalText = '🔴 Very low probability - strong trend';
            }
            document.getElementById('entrySignal').textContent = entrySignalText;
            
            // Exit Probability
            const exitBar = document.getElementById('exitProbBar');
            const exitProb = liveMetrics.exitProbability.toFixed(0);
            exitBar.style.width = exitProb + '%';
            exitBar.textContent = exitProb + '%';
            
            let exitSignalText = '';
            if (liveMetrics.exitProbability >= 60) {
                exitSignalText = '⚠️ High breakout risk - prepare for trend';
            } else if (liveMetrics.exitProbability >= 40) {
                exitSignalText = '⚠️ Moderate breakout risk - watch volatility';
            } else if (liveMetrics.exitProbability >= 20) {
                exitSignalText = '✅ Low breakout risk - consolidation stable';
            } else {
                exitSignalText = '✅ Very low breakout risk - staying in range';
            }
            document.getElementById('exitSignal').textContent = exitSignalText;
            
            // Historical context
            document.getElementById('avgHistScore').textContent = historicalData.avgScore;
            document.getElementById('histThreshold').textContent = historicalData.threshold;
            document.getElementById('histZones').textContent = historicalData.zones;
        }

        function toggleRealtimeMode() {
            const isChecked = document.getElementById('realtimeToggle').checked;
            const badge = document.getElementById('realtimeBadge');
            const startDateGroup = document.getElementById('startDateGroup');
            const endDateGroup = document.getElementById('endDateGroup');
            const loadBtn = document.getElementById('loadBtn');
            
            if (isChecked) {
                // เข้าโหมด Real-time
                isRealtimeMode = true;
                badge.style.display = 'block';
                startDateGroup.style.display = 'none';
                endDateGroup.style.display = 'none';
                loadBtn.textContent = 'Start Real-time Monitoring';
                showStatus('Real-time mode enabled. Click "Start Real-time Monitoring" to begin.', 'info');
            } else {
                // ออกจากโหมด Real-time
                stopRealtimeMode();
                isRealtimeMode = false;
                badge.style.display = 'none';
                startDateGroup.style.display = 'flex';
                endDateGroup.style.display = 'flex';
                loadBtn.textContent = 'Load Data & Analyze';
                showStatus('Real-time mode disabled.', 'info');
            }
        }

        function stopRealtimeMode() {
            if (realtimeInterval) {
                clearInterval(realtimeInterval);
                realtimeInterval = null;
            }
            if (realtimeWS) {
                realtimeWS.close();
                realtimeWS = null;
            }
            realtimeCandles = [];
        }

        function startRealtimeMonitoring() {
            const symbol = document.getElementById('symbol').value;
            const granularity = parseInt(document.getElementById('granularity').value);
            const emaShortPeriod = parseInt(document.getElementById('emaShort').value);
            const emaLongPeriod = parseInt(document.getElementById('emaLong').value);
            const macdSignalPeriod = parseInt(document.getElementById('macdSignal').value);
            const consolidationThreshold = parseFloat(document.getElementById('consolidationThreshold').value);
            
            document.getElementById('loadBtn').disabled = true;
            document.getElementById('loading').classList.add('active');
            showStatus('Connecting to real-time data stream...', 'info');
            
            // โหลดข้อมูลย้อนหลัง 500 แท่งเทียนก่อน
            const endTime = Math.floor(Date.now() / 1000);
            const startTime = endTime - (500 * granularity);
            
            realtimeWS = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');
            
            realtimeWS.onopen = () => {
                showStatus('Loading initial historical data...', 'info');
                
                // ขอข้อมูลย้อนหลัง
                realtimeWS.send(JSON.stringify({
                    ticks_history: symbol,
                    adjust_start_time: 1,
                    count: 500,
                    end: endTime,
                    start: startTime,
                    style: 'candles',
                    granularity: granularity
                }));
            };
            
            realtimeWS.onmessage = (msg) => {
                const data = JSON.parse(msg.data);
                
                if (data.error) {
                    showStatus('Error: ' + data.error.message, 'error');
                    document.getElementById('loadBtn').disabled = false;
                    document.getElementById('loading').classList.remove('active');
                    return;
                }
                
                // ได้ข้อมูล historical
                if (data.candles) {
                    realtimeCandles = data.candles.map(c => ({
                        time: c.epoch,
                        open: parseFloat(c.open),
                        high: parseFloat(c.high),
                        low: parseFloat(c.low),
                        close: parseFloat(c.close)
                    }));
                    
                    if (!mainChart) initCharts();
                    
                    // วิเคราะห์ข้อมูลเริ่มต้น
                    updateChartsWithData(realtimeCandles, emaShortPeriod, emaLongPeriod, macdSignalPeriod, consolidationThreshold);
                    
                    showStatus(`Real-time monitoring active! Updates every second. ${realtimeCandles.length} candles loaded.`, 'success');
                    document.getElementById('loadBtn').disabled = false;
                    document.getElementById('loading').classList.remove('active');
                    
                    // Subscribe to tick stream
                    realtimeWS.send(JSON.stringify({
                        ticks: symbol,
                        subscribe: 1
                    }));
                    
                    // เริ่ม update ทุกวินาที
                    let currentCandle = {...realtimeCandles[realtimeCandles.length - 1]};
                    
                    realtimeInterval = setInterval(() => {
                        updateChartsWithData(realtimeCandles, emaShortPeriod, emaLongPeriod, macdSignalPeriod, consolidationThreshold);
                    }, 1000);
                }
                
                // ได้ tick data
                if (data.tick) {
                    const tick = data.tick;
                    const price = parseFloat(tick.quote);
                    const tickTime = tick.epoch;
                    
                    // อัพเดทแท่งเทียนปัจจุบัน
                    const lastCandle = realtimeCandles[realtimeCandles.length - 1];
                    const candleStartTime = Math.floor(lastCandle.time / granularity) * granularity;
                    const currentCandleTime = Math.floor(tickTime / granularity) * granularity;
                    
                    if (currentCandleTime > candleStartTime) {
                        // แท่งเทียนใหม่
                        realtimeCandles.push({
                            time: currentCandleTime,
                            open: price,
                            high: price,
                            low: price,
                            close: price
                        });
                        
                        // เก็บแค่ 500 แท่งล่าสุด
                        if (realtimeCandles.length > 500) {
                            realtimeCandles.shift();
                        }
                    } else {
                        // อัพเดทแท่งเทียนปัจจุบัน
                        lastCandle.close = price;
                        lastCandle.high = Math.max(lastCandle.high, price);
                        lastCandle.low = Math.min(lastCandle.low, price);
                    }
                }
            };
            
            realtimeWS.onerror = (error) => {
                showStatus('Connection error. Retrying...', 'error');
                document.getElementById('loadBtn').disabled = false;
                document.getElementById('loading').classList.remove('active');
            };
        }

        function updateChartsWithData(candles, emaShortPeriod, emaLongPeriod, macdSignalPeriod, consolidationThreshold) {
            // Calculate indicators
            const emaShort = calculateEMA(candles, emaShortPeriod);
            const emaLong = calculateEMA(candles, emaLongPeriod);
            const { macdLine, signalLine, histogram } = calculateMACD(candles, emaShortPeriod, emaLongPeriod, macdSignalPeriod);
            const { zones: consolidationZones, scores: consolidationScores } = detectConsolidation(candles, consolidationThreshold);
            const trends = detectTrends(candles, emaShort, emaLong);
            
            // Update charts
            candleSeries.setData(candles);
            emaShortSeries.setData(emaShort);
            emaLongSeries.setData(emaLong);
            macdLineSeries.setData(macdLine);
            signalLineSeries.setData(signalLine);
            histogramSeries.setData(histogram);
            
            // Add consolidation markers
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
            
            // Update live metrics
            const lastIndex = candles.length - 1;
            const allScores = consolidationScores.map(s => s.score);
            const avgScore = (allScores.reduce((a, b) => a + b, 0) / allScores.length).toFixed(3);
            
            const sortedScores = [...allScores].sort((a, b) => a - b);
            const percentileIndex = Math.floor(sortedScores.length * (consolidationThreshold / 100));
            const calculatedThreshold = sortedScores[percentileIndex];
            
            const liveMetrics = calculateLiveConsolidationMetrics(
                candles, 
                lastIndex, 
                consolidationScores,
                calculatedThreshold
            );
            
            if (liveMetrics) {
                updateLivePanel(liveMetrics, {
                    avgScore: avgScore,
                    threshold: calculatedThreshold.toFixed(3),
                    zones: consolidationZones.length
                });
            }
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
            // ถ้าเป็นโหมด Real-time
            if (isRealtimeMode) {
                stopRealtimeMode(); // หยุดการทำงานเดิม
                startRealtimeMonitoring();
                return;
            }
            
            // โหมด Historical (เหมือนเดิม)
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
                        
                        // คำนวณ Live Metrics สำหรับแท่งเทียนล่าสุด
                        const lastIndex = candles.length - 1;
                        const allScores = consolidationScores.map(s => s.score);
                        const avgScore = (allScores.reduce((a, b) => a + b, 0) / allScores.length).toFixed(3);
                        
                        // คำนวณ dynamic threshold
                        const sortedScores = [...allScores].sort((a, b) => a - b);
                        const percentileIndex = Math.floor(sortedScores.length * (consolidationThreshold / 100));
                        const calculatedThreshold = sortedScores[percentileIndex];
                        
                        const liveMetrics = calculateLiveConsolidationMetrics(
                            candles, 
                            lastIndex, 
                            consolidationScores,
                            calculatedThreshold
                        );
                        
                        if (liveMetrics) {
                            updateLivePanel(liveMetrics, {
                                avgScore: avgScore,
                                threshold: calculatedThreshold.toFixed(3),
                                zones: consolidationZones.length
                            });
                            
                            // เก็บข้อมูลสำหรับการอัพเดท real-time
                            liveMonitorData = {
                                candles: candles,
                                scores: consolidationScores,
                                threshold: calculatedThreshold
                            };
                        }
                        
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