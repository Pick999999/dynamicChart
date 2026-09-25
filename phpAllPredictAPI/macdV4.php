<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Trading Analysis</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
        .control-group { display: flex; flex-direction: column; }
        label { font-weight: 600; margin-bottom: 5px; color: #4a5568; font-size: 0.9em; }
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
            transition: transform 0.2s;
            grid-column: span 2;
        }
        button:hover { transform: translateY(-2px); }
        button:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .chart-container {
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        #mainChart { height: 500px; }
        #macdChart { height: 200px; }
        .status {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .status.info { background: #bee3f8; color: #2c5282; }
        .status.error { background: #fed7d7; color: #9b2c2c; }
        .status.success { background: #c6f6d5; color: #276749; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; }
        tr:hover { background: #f7fafc; }
        .trend-up { color: #38a169; font-weight: 600; }
        .trend-down { color: #e53e3e; font-weight: 600; }
        .loading { display: none; text-align: center; padding: 20px; }
        .loading.active { display: block; }
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
        .live-panel {
            display: none;
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .live-panel.active { display: block; }
        .live-panel h2 { color: #4fd1c5; margin-bottom: 20px; font-size: 1.5em; }
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
        }
        .live-card h3 { font-size: 0.9em; color: #a0aec0; margin-bottom: 8px; }
        .live-value { font-size: 2em; font-weight: bold; margin-bottom: 5px; }
        .live-value.good { color: #48bb78; }
        .live-value.warning { color: #ed8936; }
        .live-value.bad { color: #f56565; }
        .live-value.neutral { color: #4299e1; }
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
        .signal-indicator.consolidation { background: #805ad5; }
        .signal-indicator.trending { background: #4299e1; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .trading-panel {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .trading-panel h2 { color: #4fd1c5; margin-bottom: 20px; }
        .trading-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { color: #a0aec0; font-size: 0.9em; margin-bottom: 5px; }
        .form-group input, .form-group select {
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            color: white;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
        }
        .trade-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        .trade-btn {
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .trade-btn.rise { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .trade-btn.fall { background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; }
        .trade-btn:hover { transform: translateY(-2px); }
        .trade-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .contracts-panel {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .contract-card {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid;
        }
        .contract-card.rise { border-left-color: #48bb78; }
        .contract-card.fall { border-left-color: #f56565; }
        .contract-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .contract-type { font-weight: 600; font-size: 1.1em; }
        .contract-status { padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600; }
        .contract-status.open { background: #4299e1; }
        .contract-status.won { background: #48bb78; }
        .contract-status.lost { background: #f56565; }
        .contract-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .detail-item { display: flex; justify-content: space-between; }
        .detail-label { color: #a0aec0; }
        .detail-value { font-weight: 600; }
        .profit-display {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .profit-display.positive { background: rgba(72, 187, 120, 0.2); color: #48bb78; }
        .profit-display.negative { background: rgba(245, 101, 101, 0.2); color: #f56565; }
        .sell-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .auth-panel {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .auth-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 600;
        }
        .auth-status.connected { background: rgba(72, 187, 120, 0.2); color: #48bb78; }
        .auth-status.disconnected { background: rgba(245, 101, 101, 0.2); color: #f56565; }
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
    </style>
</head>
<body>
    <div class="container">
        <h1>📈 Deriv Analysis & Trading</h1>        
        <div class="controls">
            <div class="control-group">
                <label>Symbol:</label>
                <select id="symbol" onchange='SaveLocal()'>
                    <option value="R_100">Volatility 100</option>
                    <option value="R_50">Volatility 50</option>
                    <option value="R_25">Volatility 25</option>
                </select>
            </div>
            <div class="control-group">
                <label>Timeframe:</label>
                <select id="granularity">
                    <option value="60">1 Minute</option>
                    <option value="300">5 Minutes</option>
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
                <label>EMA Short:</label>
                <input type="number" id="emaShort" value="12" min="1">
            </div>
            <div class="control-group">
                <label>EMA Long:</label>
                <input type="number" id="emaLong" value="26" min="1">
            </div>
            <div class="control-group">
                <label>Consolidation %:</label>
                <input type="number" id="consolidationThreshold" value="30" step="5" min="5" max="50">
            </div>
            <button id="loadBtn" onclick="loadData()">Load Data</button>
        </div>
        
        <div id="status" class="status" style="display:none;"></div>
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p style="margin-top: 10px;">Loading...</p>
        </div>
        
        <div id="livePanel" class="live-panel">
            <h2>🔴 Live Monitor</h2>
            <div class="live-grid">
                <div class="live-card">
                    <h3>Status</h3>
                    <div class="live-value neutral" id="liveStatus">
                        <span class="signal-indicator consolidation"></span>Waiting
                    </div>
                </div>
                <div class="live-card">
                    <h3>Score</h3>
                    <div class="live-value neutral" id="liveScore">--</div>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <h3 style="color: #4fd1c5; margin-bottom: 15px;">Entry Probability</h3>
                <div class="probability-bar">
                    <div class="probability-fill" id="entryProbBar" style="width: 0%;">0%</div>
                </div>
            </div>
        </div>
        
        <div class="trading-panel">
            <h2>💰 Trading</h2>
            <div class="auth-panel">
                <input type="password" id="apiTokenInput" placeholder="API Token" style="width: 100%; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); color: white; padding: 10px; border-radius: 8px; margin-bottom: 10px;" value='lt5UMO6bNvmZQaR'>
                <button onclick="connectTradingAPI()" style="width: 100%; padding: 10px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Connect</button>
                <div style="margin-top: 10px;">
                    <span class="auth-status disconnected" id="authStatus">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></span>
                        Disconnected
                    </span>
                </div>
            </div>
            
            <div class="trading-form">
                <div class="form-group">
                    <label>Type</label>
                    <select id="contractType">
                        <option value="CALL">Rise/Fall</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stake (USD)</label>
                    <input type="number" id="stakeAmount" onchange='SaveLocal()' value="10" min="1">
                </div>
                <div class="form-group">
                    <label>Duration (min)</label>
					<!-- 
                    <input type="number" id="duration" value="1" min="1" max="60">
					 -->
					<select id="duration" style='color:black;background:white'
					onchange='CalProfitTarget(this.value);SaveLocal()'>
					 <option value="1">1 Minute</option>
                     <option value="5">5 Minute</option>
					 <option value="10">10 Minute</option>
					 <option value="15">15 Minute</option>
					 <option value="30">30 Minute</option>
					 <option value="60">60 Minute</option>

					</select>

                </div>
            </div>
			<div class="trading-form">
                <div class="form-group">
                    <label>Profit %</label>
                    <select id="ptrofitPercent" style='color:black;background:white'
					onchange='CalProfitTarget(this.value);SaveLocal()'>
					<option value="1">1</option>			
					<option value="5">5</option>			
					   <?php
					     for ($i=1;$i<=10;$i++) { ?>
                            <option value="<?=$i*10;?>"><?=$i*10;?></option>
					     <?php }
					   ?>
                    </select>
                </div>
				<div class="form-group">
                    <label>Profit(USD)</label>
                    <input type="number" id="profitUSD" value="10" min="1" onchange='SetProfitUSD()'>
                </div>
                
            </div>
            
            <div class="trade-buttons">
                <button class="trade-btn rise" onclick="placeTrade('CALL')" id="riseBtn" disabled>📈 RISE</button>
                <button class="trade-btn fall" onclick="placeTrade('PUT')" id="fallBtn" disabled>📉 FALL</button>
            </div>
            
            <div class="contracts-panel">
                <h3 style="color: #4fd1c5; margin-bottom: 15px;">Active Contracts</h3>
                <div id="contractsList">
                    <p style="text-align: center; color: #a0aec0;">No active contracts</p>
                </div>
            </div>
        </div>
        
        <div class="chart-container"><div id="mainChart"></div></div>
        <div class="chart-container"><div id="macdChart"></div></div>
        
        <table>
            <thead>
                <tr><th>Start</th><th>End</th><th>Type</th><th>Duration</th><th>Change %</th></tr>
            </thead>
            <tbody id="trendTableBody">
                <tr><td colspan="5" style="text-align:center;">Load data</td></tr>
            </tbody>
        </table>
    </div>

<?php	DisplayTradeHistory() ; ?>

    <script>
        let ws = null, mainChart = null, macdChart = null;
        let candleSeries, emaShortSeries, emaLongSeries, macdLineSeries, signalLineSeries, histogramSeries;
        let tradingWS = null, activeContracts = [], apiToken = '';
        let userCurrency = 'USD';
        let proposalId = null; 
		let profitUSD = null ;
		let currentContractID = null;
        
        const now = new Date();
        const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        document.getElementById('endDate').value = now.toISOString().slice(0, 16);
        document.getElementById('startDate').value = yesterday.toISOString().slice(0, 16);

        function showStatus(msg, type) {
            const s = document.getElementById('status');
            s.textContent = msg;
            s.className = `status ${type}`;
            s.style.display = 'block';
        }

        function calculateEMA(data, period) {
            const k = 2 / (period + 1);
            const ema = [];
            let e = data[0].close;
            for (let i = 0; i < data.length; i++) {
                e = data[i].close * k + e * (1 - k);
                ema.push({ time: data[i].time, value: e });
            }
            return ema;
        }

        function calculateMACD(data, shortP, longP, signalP) {
            const emaShort = calculateEMA(data, shortP);
            const emaLong = calculateEMA(data, longP);
            const macdLine = emaShort.map((item, i) => ({
                time: item.time,
                value: item.value - emaLong[i].value
            }));
            const signalLine = calculateEMA(
                macdLine.map((item) => ({ time: item.time, close: item.value })),
                signalP
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
                const h = data[i].high, l = data[i].low, pc = data[i - 1].close;
                tr.push(Math.max(h - l, Math.abs(h - pc), Math.abs(l - pc)));
            }
            const atr = [];
            let sum = tr.slice(0, period).reduce((a, b) => a + b, 0);
            atr.push(sum / period);
            for (let i = period; i < tr.length; i++) {
                atr.push((atr[atr.length - 1] * (period - 1) + tr[i]) / period);
            }
            return atr;
        }

        function calculateBollingerBands(data, period = 20, stdDev = 2) {
            const bands = [];
            for (let i = period - 1; i < data.length; i++) {
                const slice = data.slice(i - period + 1, i + 1);
                const closes = slice.map(c => c.close);
                const sma = closes.reduce((a, b) => a + b, 0) / period;
                const variance = closes.reduce((sum, p) => sum + Math.pow(p - sma, 2), 0) / period;
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
            const zones = [], period = 20, atrP = 14;
            const atr = calculateATR(data, atrP);
            const bb = calculateBollingerBands(data, period, 2);
            let scores = [];
            const startIdx = Math.max(period, atrP + 1);
            
            for (let i = startIdx; i < data.length; i++) {
                const atrIdx = i - 1, bbIdx = i - period;
                if (atrIdx < 0 || atrIdx >= atr.length || bbIdx < 0 || bbIdx >= bb.length) continue;
                
                const recentPrices = data.slice(i - period, i + 1).map(c => c.close);
                const avgPrice = recentPrices.reduce((a, b) => a + b, 0) / recentPrices.length;
                const atrPercent = (atr[atrIdx] / avgPrice) * 100;
                const bbSqueezeScore = bb[bbIdx].bandwidth;
                const stdDevPercent = (calculateStdDev(recentPrices) / avgPrice) * 100;
                const score = atrPercent * 0.4 + bbSqueezeScore * 0.3 + stdDevPercent * 0.3;
                
                scores.push({ time: data[i].time, score, atr: atrPercent, bb: bbSqueezeScore, stdDev: stdDevPercent, index: i });
            }
            
            const allScores = scores.map(s => s.score).sort((a, b) => a - b);
            const pIdx = Math.floor(allScores.length * (threshold / 100));
            const dynThreshold = allScores[pIdx];
            
            let zoneStart = null, scoresInZone = [], indicesInZone = [];
            for (let i = 0; i < scores.length; i++) {
                const cs = scores[i];
                if (cs.score <= dynThreshold) {
                    if (!zoneStart) {
                        zoneStart = cs.time;
                        scoresInZone = [cs.score];
                        indicesInZone = [cs.index];
                    } else {
                        scoresInZone.push(cs.score);
                        indicesInZone.push(cs.index);
                    }
                } else {
                    if (zoneStart && scoresInZone.length >= 5) {
                        const avgScore = scoresInZone.reduce((a, b) => a + b, 0) / scoresInZone.length;
                        zones.push({
                            start: data[indicesInZone[0]].time,
                            end: data[indicesInZone[indicesInZone.length - 1]].time,
                            avgScore: avgScore.toFixed(3),
                            bars: scoresInZone.length
                        });
                    }
                    zoneStart = null;
                    scoresInZone = [];
                    indicesInZone = [];
                }
            }
            
            return { zones, scores };
        }

        function detectTrends(data, emaShort, emaLong) {
            const trends = [];
            let currentTrend = null;
            for (let i = 1; i < data.length; i++) {
                let trendType = null;
                if (emaShort[i].value > emaLong[i].value && emaShort[i - 1].value > emaLong[i - 1].value) trendType = 'Uptrend';
                else if (emaShort[i].value < emaLong[i].value && emaShort[i - 1].value < emaLong[i - 1].value) trendType = 'Downtrend';
                
                if (trendType) {
                    if (!currentTrend || currentTrend.type !== trendType) {
                        if (currentTrend) {
                            currentTrend.endTime = data[i - 1].time;
                            currentTrend.endPrice = data[i - 1].close;
                            trends.push(currentTrend);
                        }
                        currentTrend = { type: trendType, startTime: data[i].time, startPrice: data[i].close };
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

        function updateTrendTable(trends) {
            const tbody = document.getElementById('trendTableBody');
            if (trends.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No trends</td></tr>';
                return;
            }
            tbody.innerHTML = trends.map(t => {
                const dur = t.endTime - t.startTime;
                const change = ((t.endPrice - t.startPrice) / t.startPrice * 100).toFixed(2);
                const cls = t.type === 'Uptrend' ? 'trend-up' : 'trend-down';
                return `<tr>
                    <td>${new Date(t.startTime * 1000).toLocaleString('th-TH')}</td>
                    <td>${new Date(t.endTime * 1000).toLocaleString('th-TH')}</td>
                    <td class="${cls}">${t.type}</td>
                    <td>${Math.floor(dur / 60)}m</td>
                    <td class="${cls}">${change}%</td>
                </tr>`;
            }).join('');
        }

        function initCharts() {
            const opts = {
                layout: { background: { color: '#ffffff' }, textColor: '#333' },
                grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
                timeScale: { timeVisible: true, secondsVisible: false }
            };
            mainChart = LightweightCharts.createChart(document.getElementById('mainChart'), { ...opts, height: 500 });
            macdChart = LightweightCharts.createChart(document.getElementById('macdChart'), { ...opts, height: 200 });
            candleSeries = mainChart.addCandlestickSeries();
            emaShortSeries = mainChart.addLineSeries({ color: '#2962FF', lineWidth: 2 });
            emaLongSeries = mainChart.addLineSeries({ color: '#FF6D00', lineWidth: 2 });
            macdLineSeries = macdChart.addLineSeries({ color: '#2962FF', lineWidth: 2 });
            signalLineSeries = macdChart.addLineSeries({ color: '#FF6D00', lineWidth: 2 });
            histogramSeries = macdChart.addHistogramSeries();
        }

        function updateLivePanel(metrics) {
            if (!metrics) return;
            document.getElementById('livePanel').classList.add('active');
            const statusEl = document.getElementById('liveStatus');
            const indicatorClass = metrics.status === 'In Consolidation' ? 'consolidation' : 'trending';
            statusEl.innerHTML = `<span class="signal-indicator ${indicatorClass}"></span>${metrics.status}`;
            
            const scoreEl = document.getElementById('liveScore');
            const scoreClass = metrics.scoreRatio <= 0.8 ? 'good' : metrics.scoreRatio <= 1.0 ? 'warning' : 'bad';
            scoreEl.className = `live-value ${scoreClass}`;
            scoreEl.textContent = metrics.score.toFixed(3);
            
            const entryBar = document.getElementById('entryProbBar');
            const entryProb = metrics.entryProbability.toFixed(0);
            entryBar.style.width = entryProb + '%';
            entryBar.textContent = entryProb + '%';
        }

        function calculateLiveMetrics(data, idx, scores, dynThreshold) {
            if (idx < 20) return null;
            const lastScore = scores[scores.length - 1];
            if (!lastScore) return null;
            
            const scoreRatio = lastScore.score / dynThreshold;
            let entryProb = 0;
            if (scoreRatio <= 0.7) entryProb = 85 + (Math.random() * 10);
            else if (scoreRatio <= 0.9) entryProb = 65 + (Math.random() * 15);
            else if (scoreRatio <= 1.1) entryProb = 40 + (Math.random() * 20);
            else entryProb = 5 + (Math.random() * 10);
            
            return {
                score: lastScore.score,
                atr: lastScore.atr,
                bb: lastScore.bb,
                stdDev: lastScore.stdDev,
                threshold: dynThreshold,
                entryProbability: Math.min(95, entryProb),
                exitProbability: 10,
                status: scoreRatio <= 1.0 ? 'In Consolidation' : 'Trending',
                scoreRatio: scoreRatio
            };
        }

        async function loadData() {
            const symbol = document.getElementById('symbol').value;
            const granularity = parseInt(document.getElementById('granularity').value);
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            const emaShortPeriod = parseInt(document.getElementById('emaShort').value);
            const emaLongPeriod = parseInt(document.getElementById('emaLong').value);
            const consolidationThreshold = parseFloat(document.getElementById('consolidationThreshold').value);
            
            if (!startDate || !endDate) {
                showStatus('Select dates', 'error');
                return;
            }
            
            document.getElementById('loadBtn').disabled = true;
            document.getElementById('loading').classList.add('active');
            showStatus('Connecting...', 'info');
            
            try {
                if (ws) ws.close();
                ws = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=66726');
                
                ws.onopen = () => {
                    showStatus('Loading data...', 'info');
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
                        
                        const emaShort = calculateEMA(candles, emaShortPeriod);
                        const emaLong = calculateEMA(candles, emaLongPeriod);
                        const { macdLine, signalLine, histogram } = calculateMACD(candles, emaShortPeriod, emaLongPeriod, 9);
                        const { zones, scores } = detectConsolidation(candles, consolidationThreshold);
                        const trends = detectTrends(candles, emaShort, emaLong);
                        
                        candleSeries.setData(candles);
                        emaShortSeries.setData(emaShort);
                        emaLongSeries.setData(emaLong);
                        macdLineSeries.setData(macdLine);
                        signalLineSeries.setData(signalLine);
                        histogramSeries.setData(histogram);
                        
                        const markers = zones.flatMap(z => [
                            { time: z.start, position: 'belowBar', color: '#9C27B0', shape: 'arrowUp', text: `📊 ${z.bars}` },
                            { time: z.end, position: 'belowBar', color: '#9C27B0', shape: 'arrowDown', text: 'End' }
                        ]);
                        candleSeries.setMarkers(markers);
                        
                        updateTrendTable(trends);
                        
                        const allScores = scores.map(s => s.score).sort((a, b) => a - b);
                        const pIdx = Math.floor(allScores.length * (consolidationThreshold / 100));
                        const dynThreshold = allScores[pIdx];
                        const liveMetrics = calculateLiveMetrics(candles, candles.length - 1, scores, dynThreshold);
                        if (liveMetrics) updateLivePanel(liveMetrics);
                        
                        showStatus(`Success! ${candles.length} candles, ${zones.length} zones`, 'success');
                        document.getElementById('loadBtn').disabled = false;
                        document.getElementById('loading').classList.remove('active');
                        ws.close();
                    }
                };
                
                ws.onerror = () => {
                    showStatus('Connection error', 'error');
                    document.getElementById('loadBtn').disabled = false;
                    document.getElementById('loading').classList.remove('active');
                };
            } catch (error) {
                showStatus('Error: ' + error.message, 'error');
                document.getElementById('loadBtn').disabled = false;
                document.getElementById('loading').classList.remove('active');
            }
        }

        async function connectTradingAPI() {
            apiToken = document.getElementById('apiTokenInput').value.trim();
            if (!apiToken) {
                showStatus('Enter API token', 'error');
                return;
            }
            
            if (tradingWS) tradingWS.close();
            showStatus('Connecting to Trading API...', 'info');
            tradingWS = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');
            
            tradingWS.onopen = () => {
                tradingWS.send(JSON.stringify({ authorize: apiToken }));
            };
            
            tradingWS.onmessage = (msg) => {
                const data = JSON.parse(msg.data);
                
                if (data.error) {
                    showStatus('API Error: ' + data.error.message, 'error');
                    document.getElementById('authStatus').className = 'auth-status disconnected';
                    document.getElementById('authStatus').innerHTML = '<span style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></span>Disconnected';
                    document.getElementById('riseBtn').disabled = true;
                    document.getElementById('fallBtn').disabled = true;
                    return;
                }
                
                if (data.authorize) {
                    userCurrency = data.authorize.currency;
                    showStatus(`Connected! Balance: ${data.authorize.balance} ${userCurrency}`, 'success');
                    document.getElementById('authStatus').className = 'auth-status connected';
                    document.getElementById('authStatus').innerHTML = `<span style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></span>Connected (${userCurrency} ${data.authorize.balance})`;
                    document.getElementById('riseBtn').disabled = false;
                    document.getElementById('fallBtn').disabled = false;
                    tradingWS.send(JSON.stringify({ balance: 1, subscribe: 1 }));
                }
                
                if (data.balance) {
                    document.getElementById('authStatus').innerHTML = `<span style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></span>Connected (${data.balance.currency} ${data.balance.balance})`;
                }
                
                if (data.proposal) {
                    proposalId = data.proposal.id;
                    console.log('Proposal received:', data.proposal);
					/*
					stake = document.getElementById("stakeAmount").value ;
					ws.send(JSON.stringify({
                        buy: proposalId,
                        price: stake
                    }));
					*/
                }
				 
                
                if (data.buy) {
				    console.log('Buy Response ',data)					
                    const contract = {
                        id: data.buy.contract_id,
                        type: data.buy.longcode,
                        stake: data.buy.buy_price,
                        payout: data.buy.payout,
                        status: 'open',
                        profit: 0,
                        currentPrice: 0,
                        buyPrice: data.buy.buy_price,
                        sellPrice: 0
                    };
                    currentContractID = data.buy.contract_id ;
                    activeContracts.push(contract);
                    updateContractsDisplay();
                    tradingWS.send(JSON.stringify({ proposal_open_contract: 1, contract_id: contract.id, subscribe: 1 }));
                    showStatus('Trade opened!', 'success');
                }
                
                if (data.proposal_open_contract) {
                    const poc = data.proposal_open_contract;
                    const contract = activeContracts.find(c => c.id == poc.contract_id);
                    if (contract) {
                        contract.currentPrice = parseFloat(poc.current_spot) || 0;
                        contract.profit = parseFloat(poc.profit) || 0;
                        contract.sellPrice = parseFloat(poc.bid_price) || 0;
                        if (poc.status === 'sold') {
                            contract.status = parseFloat(poc.profit) >= 0 ? 'won' : 'lost';
                        }
                        updateContractsDisplay(poc);
                    }
                }
                
                if (data.sell) {
                    showStatus(`Sold! Profit: ${data.sell.sold_for - data.sell.buy_price}`, 
                              data.sell.sold_for >= data.sell.buy_price ? 'success' : 'error');
                }
            };
            
            tradingWS.onerror = () => {
                showStatus('Connection error', 'error');
                document.getElementById('authStatus').className = 'auth-status disconnected';
            };
        }

        async function placeTrade(direction) {
            if (!tradingWS || tradingWS.readyState !== WebSocket.OPEN) {
                showStatus('Connect to Trading API first', 'error');
                return;
            }
            
            const symbol = document.getElementById('symbol').value;
            const stake = parseFloat(document.getElementById('stakeAmount').value);
            const duration = parseInt(document.getElementById('duration').value);
            
            showStatus('Getting quote...', 'info');
            
            // Reset proposal ID
            proposalId = null;
            
            // Send proposal request
            const proposal = {
                proposal: 1,
                amount: stake,
                basis: 'stake',
                currency: userCurrency,
                symbol: symbol,
                contract_type: direction,
                duration: duration,
                duration_unit: 'm'
            };
            
            console.log('Sending proposal:', proposal);
            tradingWS.send(JSON.stringify(proposal));
            
            // Wait for proposal response, then buy
            setTimeout(() => {
                if (proposalId) {
                    console.log('Buying with proposal ID:', proposalId);
                    tradingWS.send(JSON.stringify({
                        buy: proposalId,
                        price: stake
                    }));
                } else {
                    showStatus('Failed to get proposal', 'error');
                }
            }, 1000);
        }

        function updateContractsDisplay(poc) {
        // poc คือ  proposal_open_contract

            const container = document.getElementById('contractsList');
            if (activeContracts.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #a0aec0;">No active contracts</p>';
                return;
            }
            
            container.innerHTML = activeContracts.map((c) => {
                const profitClass = c.profit >= 0 ? 'positive' : 'negative';
                const cardClass = c.type.toLowerCase().includes('rise') || c.type.toLowerCase().includes('up') ? 'rise' : 'fall';

				if (c.profit >= profitUSD) {
					//alert(profitUSD);
					thisContractID = currentContractID ;
					console.log('Sale Contract ID=',thisContractID);					
					sellContract(thisContractID);
				}
                
				//
				let current_spot_time = '';
				let date_expiry = '';
				let timeRemain  = '';
				let usedTime = '';
				let isSold = '';
				let Current_profit = '';

				if (poc && poc.current_spot_time) {				
                  console.log('Object เวลา:', poc);
				  current_spot_time = convertToThaiTime(poc.current_spot_time);
				  date_expiry = convertToThaiTime(poc.date_expiry);
				  console.log('เวลาไทย:', current_spot_time);
				  timeRemain = calculateTimeRemaining(poc.date_expiry,poc.current_spot_time);
			      //console.log('poc.entry_tick_time',poc.entry_tick_time)				  
				  usedTime = calculateTimeRemaining(poc.current_spot_time,poc.entry_tick_time);
				  isSold  = poc.is_sold === 0 ? "Open" : "ขายแล้ว";
				  if (poc.is_sold === 0) {
                    Current_profit = '***' ;
				  } else {
 				    Current_profit = poc.profit + 'USD :: '+ poc.profit*32 + ' บาท ';
					SaveTradeToLocal(poc);
				  }
				}
                //

                return `
                    <div class="contract-card ${cardClass}">
                        <div class="contract-header">
                            <span class="contract-type">${c.type}</span>
                            <span class="contract-status ${c.status}">${c.status.toUpperCase()}</span>
                        </div>
                        <div class="contract-details">
                            <div class="detail-item">
                                <span class="detail-label">Stake:</span>
                                <span class="detail-value">${c.stake.toFixed(2)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Payout:</span>
                                <span class="detail-value">${c.payout ? c.payout.toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Current:</span>
                                <span class="detail-value">${c.currentPrice.toFixed(5)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Sell Price:</span>
                                <span class="detail-value">${c.sellPrice ? c.sellPrice.toFixed(2) : '0.00'}</span>
                            </div>
    					    <div class="detail-item">
                                <span class="detail-label">Profit Target:</span>
                                <span class="detail-value">${profitUSD} </span>
                            </div>
   					        <div class="detail-item">
                                <span class="detail-label">เวลา ปัจจุบัน:</span>
                                <span class="detail-value">${current_spot_time} </span>
                            </div>
						    <div class="detail-item">
                                <span class="detail-label">เวลา หมดอายุ :</span>
                                <span class="detail-value">${date_expiry} </span>
                            </div>
							<div class="detail-item">
                                <span class="detail-label">เวลา ที่ ใช้ไป:</span>
                                <span class="detail-value">${usedTime} </span>
                            </div>
							<div class="detail-item">
                                <span class="detail-label">เวลา ที่เหลือ:</span>
                                <span class="detail-value">${timeRemain} </span>
                            </div>
							<div class="detail-item">
                                <span class="detail-label">สถานะการขาย :</span>
                                <span class="detail-value">${isSold} </span>
                            </div>
							<div class="detail-item">
                                <span class="detail-label">Profit :</span>
                                <span class="detail-value">${Current_profit} </span>
                            </div>


                        </div>
                        <div class="profit-display ${profitClass}">
                            ${c.profit >= 0 ? '+' : ''}${c.profit.toFixed(2)}
                        </div>
                        ${c.status === 'open' && c.sellPrice ? `
                            <button class="sell-btn" onclick="sellContract(${c.id})">
                                Sell Now (${c.sellPrice.toFixed(2)})
                            </button>
                        ` : ''}
                    </div>
                `;
            }).join('');
        } 

		 // ฟังก์ชันแปลง Unix timestamp เป็นเวลาไทย (GMT+7)
		function convertToThaiTime(currentSpotTime) {

		  
		  // แปลง Unix timestamp เป็น milliseconds
		  const date = new Date(currentSpotTime * 1000);
		  
		  // เพิ่ม offset สำหรับเวลาไทย (GMT+7 = +7 ชั่วโมง = +25200000 milliseconds)
		  const thaiOffset = 7 * 60 * 60 * 1000;
		  const thaiTime = new Date(date.getTime() + thaiOffset);
		  
		  // ดึงค่าชั่วโมง นาที วินาที
		  const hours = String(thaiTime.getUTCHours()).padStart(2, '0');
		  const minutes = String(thaiTime.getUTCMinutes()).padStart(2, '0');
		  const seconds = String(thaiTime.getUTCSeconds()).padStart(2, '0');
		  
		  return `${hours}:${minutes}:${seconds}`;
		}

				// ฟังก์ชันคำนวณเวลาที่เหลือ
		function calculateTimeRemaining(dateExpiry, currentSpotTime) {
		  // คำนวณเวลาที่เหลือเป็นวินาที
		  const remainingSeconds = dateExpiry - currentSpotTime;
		  
		  // ถ้าหมดเวลาแล้ว
		  if (remainingSeconds <= 0) {
			return '00:00:00';
		  }
		  
		  // แปลงเป็น ชั่วโมง นาที วินาที
		  const hours = Math.floor(remainingSeconds / 3600);
		  const minutes = Math.floor((remainingSeconds % 3600) / 60);
		  const seconds = remainingSeconds % 60;
		  
		  // จัดรูปแบบให้มี 2 หลัก
		  const hh = String(hours).padStart(2, '0');
		  const mm = String(minutes).padStart(2, '0');
		  const ss = String(seconds).padStart(2, '0');
		  
		  return `${hh}:${mm}:${ss}`;
		}
		 

        function CalProfitTarget(percentValue) {

			     stake = document.getElementById("stakeAmount").value ;
				 document.getElementById("profitUSD").value = (percentValue * stake)/100 ;
				 profitUSD = parseFloat((percentValue * stake)/100) ;
        
        
        } // end func

		function SetProfitUSD() {

		        
			   profitUSD = parseFloat(document.getElementById("profitUSD").value ) ;   
			   SaveLocal();
		
		} // end func
		

		function SaveLocal() {
			 
			     formDatamacdV4 = {
				  "symbol" : document.getElementById("symbol").value ,
                  "stakeAmount" : document.getElementById("stakeAmount").value ,
                  "duration" : document.getElementById("duration").value ,
                  "profitPercent" : document.getElementById("ptrofitPercent").value ,
				  "profitUSD" : document.getElementById("profitUSD").value ,
				 }
                 localStorage.setItem('formDatamacdV4',JSON.stringify(formDatamacdV4))

		
		
		} // end func

		function getLocal() {
			
			formDatamacdV4 = JSON.parse(localStorage.getItem('formDatamacdV4'));
            document.getElementById("symbol").value  = formDatamacdV4.symbol ;
			document.getElementById("stakeAmount").value  = formDatamacdV4.stakeAmount ;
            document.getElementById("duration").value  = formDatamacdV4.duration ;
			document.getElementById("ptrofitPercent").value  = formDatamacdV4.profitPercent;
			document.getElementById("profitUSD").value  = formDatamacdV4.profitUSD;

            profitUSD = formDatamacdV4.profitUSD ;

		
		
		} // end func

		function SaveTradeToLocal(poc) { 

            usedTime = poc.exit_tick_time - poc.entry_tick_time ;
			formDatamacdV4 = {
				  "symbol" : document.getElementById("symbol").value ,
                  "stakeAmount" : parseFloat(document.getElementById("stakeAmount").value) ,
                  "duration" : document.getElementById("duration").value ,
                  "TargetprofitPercent" : parseFloat(document.getElementById("ptrofitPercent").value) ,
				  "TargetProfitUSD" : document.getElementById("profitUSD").value ,
                  "contractID" : poc.contract_id,
                  "contractType" : poc.contract_type,
				  "purchasePrice" : poc.entry_spot,	   
                  "purchaseATTime" : poc.entry_tick_time,
                  "WinStatus" : poc.status,
                  "SoldATTime" : poc.exit_tick_time,
                  "SoldPrice" : poc.sell_spot,
				  "isSold" : poc.status,
				  "profit" : poc.profit,	
				  "usedTime" : usedTime,	  

			 }
			 console.log('Trade Data',formDatamacdV4)
             OldtradeDatamacdV4 = JSON.parse(localStorage.getItem('tradeDatamacdV4'));
	//		 newTradeList = []
    //         newTradeList.push(OldtradeDatamacdV4);
             OldtradeDatamacdV4.push(formDatamacdV4);  
             
			 
             console.log(OldtradeDatamacdV4)
             createTable(OldtradeDatamacdV4);
             createSummary(OldtradeDatamacdV4);
             
             localStorage.setItem('tradeDatamacdV4',JSON.stringify(OldtradeDatamacdV4))

		
		
		} // end func 

		function formatTimestamp(timestamp) {
            const date = new Date(timestamp * 1000);
            const thaiOffset = 7 * 60 * 60 * 1000;
            const thaiTime = new Date(date.getTime() + thaiOffset);
            
            const hours = String(thaiTime.getUTCHours()).padStart(2, '0');
            const minutes = String(thaiTime.getUTCMinutes()).padStart(2, '0');
            const seconds = String(thaiTime.getUTCSeconds()).padStart(2, '0');
            
            return `${hours}:${minutes}:${seconds}`;
        }

        function createTable(data) {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            // เรียงข้อมูลจากใหม่สุดไปเก่าสุด (ตาม purchaseATTime)
            const sortedData = [...data].sort((a, b) => b.purchaseATTime - a.purchaseATTime);

            sortedData.forEach((row, index) => {
                const tr = document.createElement('tr');
                
                const profitClass = row.profit >= 0 ? 'profit-positive' : 'profit-negative';
                const profitSign = row.profit >= 0 ? '+' : '';
                
                tr.innerHTML = `
                    <td><strong>${index + 1}</strong></td>
                    <td>${row.symbol}</td>
                    <td>${row.stakeAmount}</td>
                    <td>${row.duration} นาที</td>
                    <td>${row.TargetprofitPercent}%</td>
                    <td>${row.TargetProfitUSD}</td>
                    <td>${row.contractID}</td>
                    <td><span class="contract-type">${row.contractType}</span></td>
                    <td>${row.purchasePrice.toFixed(2)}</td>
                    <td>${formatTimestamp(row.purchaseATTime)}</td>
                    <td><span class="status-sold">${row.WinStatus}</span></td>
                    <td>${formatTimestamp(row.SoldATTime)}</td>
                    <td>${row.SoldPrice.toFixed(2)}</td>
                    <td class="${profitClass}">${profitSign}${row.profit.toFixed(2)}</td>
                    <td>${row.usedTime}</td>
                `;
                
                tbody.appendChild(tr);
            });
        }

        function createSummary(data) {
            const totalTrades = data.length;
            const totalProfit = data.reduce((sum, item) => sum + item.profit, 0);
            const winTrades = data.filter(item => item.profit > 0).length;
            const winRate = ((winTrades / totalTrades) * 100).toFixed(2);
            const avgProfit = (totalProfit / totalTrades).toFixed(2);

            const summary = document.getElementById('summary');
            summary.innerHTML = `
                <div class="summary-item">
                    <div class="summary-label">จำนวนเทรด</div>
                    <div class="summary-value">${totalTrades}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">กำไรรวม</div>
                    <div class="summary-value" style="color: ${totalProfit >= 0 ? '#10b981' : '#ef4444'}">
                        ${totalProfit >= 0 ? '+' : ''}$${totalProfit.toFixed(2)}
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">อัตราชนะ</div>
                    <div class="summary-value">${winRate}%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">กำไรเฉลี่ย</div>
                    <div class="summary-value">$${avgProfit}</div>
                </div>
            `;
        }
		
		
        

        function sellContract(contractId) {

			
            if (!tradingWS || tradingWS.readyState !== WebSocket.OPEN) {
                showStatus('Trading connection lost', 'error');
                return;
            }
            
            const contract = activeContracts.find(c => c.id == contractId);
            if (!contract || !contract.sellPrice) {
                showStatus('Cannot sell', 'error');
                return;
            }
            
            //if (confirm(`Sell for ${contract.sellPrice.toFixed(2)}?\nP&L: ${contract.profit >= 0 ? '+' : ''}${contract.profit.toFixed(2)}`)) {
                tradingWS.send(JSON.stringify({ sell: contractId, price: contract.sellPrice }));
                showStatus('Selling...', 'info');
            //}
        }

        window.addEventListener('load', () => {
            initCharts();
			getLocal();
        });
    </script>
<?php

function DisplayTradeHistory() { ?>
  
<style>
 .table-wrapper {
            overflow-x: auto;
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tbody tr {
            transition: background-color 0.2s;
        }
        
        tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .profit-positive {
            color: #10b981;
            font-weight: 600;
        }
        
        .profit-negative {
            color: #ef4444;
            font-weight: 600;
        }
        
        .status-sold {
            background: #10b981;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }
        
        .contract-type {
            background: #3b82f6;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }
        
        .summary {
            padding: 20px;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
</style>


  <div class="container">
        <h1>📊 ประวัติการเทรด</h1>
        <div class="table-wrapper">
            <table id="contractTable">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>สัญลักษณ์</th>
                        <th>เงินเดิมพัน</th>
                        <th>ระยะเวลา</th>
                        <th>เป้ากำไร %</th>
                        <th>เป้ากำไร USD</th>
                        <th>Contract ID</th>
                        <th>ประเภท</th>
                        <th>ราคาซื้อ</th>
                        <th>เวลาซื้อ</th>
                        <th>สถานะ</th>
                        <th>เวลาขาย</th>
                        <th>ราคาขาย</th>
                        <th>กำไร/ขาดทุน</th>
                        <th>เวลาที่ใช้ (วิ)</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
        </div>
        <div class="summary" id="summary"></div>
    </div>

  
<?php
} // end function
  
?>

</body>
</html>