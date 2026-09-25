<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volume SuperTrend AI - Deriv Live Data</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1e222d;
            color: #d1d4dc;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .status {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #2a2e39;
        }
        .status.loading {
            color: #FFA726;
        }
        .status.success {
            color: #00FF00;
        }
        .status.error {
            color: #FF0000;
        }
        #chart {
            width: 100%;
            height: 600px;
            background: #131722;
            margin-bottom: 20px;
        }
        .controls {
            background: #2a2e39;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .control-group {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
        }
        label {
            display: inline-block;
            margin-right: 8px;
            font-size: 14px;
        }
        input, select {
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #434651;
            background: #1e222d;
            color: #d1d4dc;
        }
        button {
            padding: 8px 16px;
            background: #2962ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #1e53e5;
        }
        button:disabled {
            background: #434651;
            cursor: not-allowed;
        }
        .analysis-section {
            background: #2a2e39;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .analysis-section h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2962ff;
        }
        .analysis-section h3 {
            margin-top: 20px;
            margin-bottom: 10px;
            color: #d1d4dc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #131722;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #2a2e39;
        }
        th {
            background: #1e222d;
            color: #2962ff;
            font-weight: 600;
        }
        tr:hover {
            background: #1e222d;
        }
        .signal-bullish {
            color: #00FF00;
            font-weight: bold;
        }
        .signal-bearish {
            color: #FF0000;
            font-weight: bold;
        }
        .trend-up {
            background: rgba(0, 255, 0, 0.05);
        }
        .trend-down {
            background: rgba(255, 0, 0, 0.05);
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #131722;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2962ff;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #787b86;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
        }
        .summary-card.bullish {
            border-left-color: #00FF00;
        }
        .summary-card.bearish {
            border-left-color: #FF0000;
        }
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Volume SuperTrend AI - Deriv Live Data</h1>
        
        <div id="status" class="status loading">🔄 Connecting to Deriv...</div>
        
        <div class="controls">
            <div class="control-group">
                <label>Symbol:</label>
                <select id="symbol">
                    <option value="R_100" selected>Volatility 100 Index</option>
                    <option value="R_75">Volatility 75 Index</option>
                    <option value="R_50">Volatility 50 Index</option>
                    <option value="R_25">Volatility 25 Index</option>
                    <option value="R_10">Volatility 10 Index</option>
                </select>
            </div>
            <div class="control-group">
                <label>Timeframe:</label>
                <select id="timeframe">
                    <option value="60" selected>1 Minute</option>
                    <option value="120">2 Minutes</option>
                    <option value="300">5 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="3600">1 Hour</option>
                </select>
            </div>
            <div class="control-group">
                <label>Candles:</label>
                <input type="number" id="candleCount" value="500" min="100" max="1000" step="100">
            </div>
            <div class="control-group">
                <label>Neighbors (k):</label>
                <input type="number" id="k" value="3" min="1" max="100">
            </div>
            <div class="control-group">
                <label>Data (n):</label>
                <input type="number" id="n" value="10" min="1" max="100">
            </div>
            <div class="control-group">
                <label>Price Trend:</label>
                <input type="number" id="priceLen" value="20" min="2" max="500">
            </div>
            <div class="control-group">
                <label>Prediction Trend:</label>
                <input type="number" id="stLen" value="100" min="2" max="500">
            </div>
            <div class="control-group">
                <label>ST Length:</label>
                <input type="number" id="len" value="10" min="1" max="100">
            </div>
            <div class="control-group">
                <label>ST Factor:</label>
                <input type="number" id="factor" value="3.0" min="0.1" max="10" step="0.1">
            </div>
            <div class="control-group">
                <label>MA Type:</label>
                <select id="maType">
                    <option value="SMA">SMA</option>
                    <option value="EMA">EMA</option>
                    <option value="WMA" selected>WMA</option>
                    <option value="RMA">RMA</option>
                    <option value="VWMA">VWMA</option>
                </select>
            </div>
            <div class="control-group">
                <button id="loadBtn" onclick="loadDerivData()">Load Data</button>
            </div>
        </div>
        
        <div id="chart"></div>
        
        <div class="analysis-section" id="analysisSection" style="display: none;">
            <h2>📊 AI SuperTrend Analysis</h2>
            
            <div class="summary-cards">
                <div class="summary-card bullish">
                    <h3>Bullish Signals</h3>
                    <div class="value signal-bullish" id="bullishCount">0</div>
                </div>
                <div class="summary-card bearish">
                    <h3>Bearish Signals</h3>
                    <div class="value signal-bearish" id="bearishCount">0</div>
                </div>
                <div class="summary-card">
                    <h3>Current Trend</h3>
                    <div class="value" id="currentTrend">-</div>
                </div>
                <div class="summary-card">
                    <h3>Trend Duration</h3>
                    <div class="value" id="trendDuration">0 bars</div>
                </div>
            </div>
            
            <h3>Signal History</h3>
            <div class="table-container">
                <table id="signalTable">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Signal Type</th>
                            <th>Price</th>
                            <th>SuperTrend</th>
                            <th>Direction</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="signalTableBody">
                        <tr><td colspan="6" style="text-align: center; padding: 20px;">No data loaded</td></tr>
                    </tbody>
                </table>
            </div>
            
            <h3>Trend Periods</h3>
            <div class="table-container">
                <table id="trendTable">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Trend</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration (bars)</th>
                            <th>Price Change</th>
                            <th>Change %</th>
                        </tr>
                    </thead>
                    <tbody id="trendTableBody">
                        <tr><td colspan="7" style="text-align: center; padding: 20px;">No data loaded</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
	<?php Manual();?>

    <script>
        // ========================================
        // Deriv Data Fetcher
        // ========================================
        class DerivDataFetcher {
            constructor() {
                this.ws = null;
                this.appId = '1089'; // Demo app_id
            }
            
            connect() {
                return new Promise((resolve, reject) => {
                    this.ws = new WebSocket(`wss://ws.derivws.com/websockets/v3?app_id=${this.appId}`);
                    
                    this.ws.onopen = () => {
                        console.log('✅ Connected to Deriv');
                        resolve();
                    };
                    
                    this.ws.onerror = (error) => {
                        console.error('❌ WebSocket Error:', error);
                        reject(error);
                    };
                });
            }
            
            getOHLC(symbol, granularity, count) {
                return new Promise((resolve, reject) => {
                    const request = {
                        ticks_history: symbol,
                        adjust_start_time: 1,
                        count: count,
                        end: 'latest',
                        start: 1,
                        style: 'candles',
                        granularity: granularity
                    };
                    
                    this.ws.send(JSON.stringify(request));
                    
                    this.ws.onmessage = (msg) => {
                        const data = JSON.parse(msg.data);
                        
                        if (data.error) {
                            console.error('❌ Deriv Error:', data.error.message);
                            reject(data.error);
                            return;
                        }
                        
                        if (data.candles) {
                            console.log('✅ Received', data.candles.length, 'candles');
                            resolve(data);
                        }
                    };
                });
            }
            
            convertToIndicatorFormat(derivData) {
                const candles = derivData.candles;
                
                const converted = candles.map(candle => ({
                    time: candle.epoch,
                    open: parseFloat(candle.open),
                    high: parseFloat(candle.high),
                    low: parseFloat(candle.low),
                    close: parseFloat(candle.close),
                    volume: 1
                }));
                
                converted.sort((a, b) => a.time - b.time);
                
                const filtered = converted.filter((candle, index, arr) => {
                    if (index > 0 && candle.time === arr[index - 1].time) return false;
                    return candle.open > 0 && candle.high > 0 && 
                           candle.low > 0 && candle.close > 0 &&
                           candle.high >= candle.low;
                });
                
                console.log('📊 Valid candles:', filtered.length);
                return filtered;
            }
            
            close() {
                if (this.ws) {
                    this.ws.close();
                    console.log('🔌 Disconnected');
                }
            }
        }

        // ========================================
        // Volume SuperTrend AI Class
        // ========================================
        class VolumeSuperTrendAI {
            constructor(options = {}) {
                this.k = options.k || 3;
                this.n_ = options.n_ || 10;
                this.n = Math.max(this.k, this.n_);
                this.KNN_PriceLen = options.KNN_PriceLen || 20;
                this.KNN_STLen = options.KNN_STLen || 100;
                this.len = options.len || 10;
                this.factor = options.factor || 3.0;
                this.maSrc = options.maSrc || 'WMA';
                this.upCol = options.upCol || '#00FF00';
                this.dnCol = options.dnCol || '#FF0000';
                this.neCol = options.neCol || '#0000FF';
                this.bars = [];
                this.results = [];
            }
            
            sma(data, period) {
                if (data.length < period) return null;
                let sum = 0;
                for (let i = 0; i < period; i++) sum += data[i];
                return sum / period;
            }
            
            ema(data, period) {
                if (data.length < period) return null;
                const multiplier = 2 / (period + 1);
                let ema = this.sma(data.slice(0, period), period);
                for (let i = period; i < data.length; i++) {
                    ema = (data[i] - ema) * multiplier + ema;
                }
                return ema;
            }
            
            wma(data, period) {
                if (data.length < period) return null;
                let weightedSum = 0, weightTotal = 0;
                for (let i = 0; i < period; i++) {
                    const weight = period - i;
                    weightedSum += data[i] * weight;
                    weightTotal += weight;
                }
                return weightedSum / weightTotal;
            }
            
            rma(data, period) {
                if (data.length < period) return null;
                let sum = 0;
                for (let i = 0; i < period; i++) sum += data[i];
                let rma = sum / period;
                for (let i = period; i < data.length; i++) {
                    rma = (rma * (period - 1) + data[i]) / period;
                }
                return rma;
            }
            
            vwma(prices, volumes, period) {
                if (prices.length < period) return null;
                let sumPV = 0, sumV = 0;
                for (let i = 0; i < period; i++) {
                    sumPV += prices[i] * volumes[i];
                    sumV += volumes[i];
                }
                return sumV > 0 ? sumPV / sumV : prices[0];
            }
            
            calculateMA(prices, volumes, period, type) {
                const pv = prices.map((p, i) => p * volumes[i]);
                const v = volumes.slice(0, period);
                
                switch(type) {
                    case 'SMA': return this.sma(pv, period) / this.sma(v, period);
                    case 'EMA': return this.ema(pv, period) / this.ema(v, period);
                    case 'WMA': return this.wma(pv, period) / this.wma(v, period);
                    case 'RMA': return this.rma(pv, period) / this.rma(v, period);
                    case 'VWMA': return this.vwma(prices, volumes, period);
                    default: return this.wma(pv, period) / this.wma(v, period);
                }
            }
            
            calculateATR(bars, period) {
                if (bars.length < period + 1) return null;
                const trueRanges = [];
                for (let i = 1; i < bars.length; i++) {
                    const tr = Math.max(
                        bars[i].high - bars[i].low,
                        Math.abs(bars[i].high - bars[i - 1].close),
                        Math.abs(bars[i].low - bars[i - 1].close)
                    );
                    trueRanges.push(tr);
                }
                return this.sma(trueRanges.slice(0, period), period);
            }
            
            distance(x1, x2) {
                return Math.abs(x1 - x2);
            }
            
            knnWeighted(data, labels, k, x) {
                const distances = [];
                for (let i = 0; i < data.length; i++) {
                    distances.push({ dist: this.distance(x, data[i]), label: labels[i] });
                }
                distances.sort((a, b) => a.dist - b.dist);
                let weightedSum = 0, totalWeight = 0;
                for (let i = 0; i < k; i++) {
                    const weight = 1 / (distances[i].dist + 1e-6);
                    weightedSum += weight * distances[i].label;
                    totalWeight += weight;
                }
                return weightedSum / totalWeight;
            }
            
            calculate(bars) {
                this.bars = bars;
                this.results = [];
                const requiredBars = Math.max(this.len, this.n, this.KNN_PriceLen, this.KNN_STLen) + 10;
                
                for (let i = 0; i < bars.length; i++) {
                    if (i < requiredBars) {
                        this.results.push({ time: bars[i].time, superTrend: null, direction: null, color: this.neCol, label: null });
                        continue;
                    }
                    
                    const historicalBars = bars.slice(Math.max(0, i - requiredBars), i + 1);
                    const closes = historicalBars.map(b => b.close);
                    const volumes = historicalBars.map(b => b.volume || 1);
                    const vwma = this.calculateMA(closes.slice(-this.len), volumes.slice(-this.len), this.len, this.maSrc);
                    const atr = this.calculateATR(historicalBars.slice(-this.len - 1), this.len);
                    
                    if (!vwma || !atr) continue;
                    
                    let upperBand = vwma + this.factor * atr;
                    let lowerBand = vwma - this.factor * atr;
                    const prev = i > 0 ? this.results[i - 1] : null;
                    const prevClose = i > 0 ? bars[i - 1].close : bars[i].close;
                    
                    if (prev && prev.superTrend !== null) {
                        const prevLowerBand = prev.lowerBand || lowerBand;
                        const prevUpperBand = prev.upperBand || upperBand;
                        lowerBand = (lowerBand > prevLowerBand || prevClose < prevLowerBand) ? lowerBand : prevLowerBand;
                        upperBand = (upperBand < prevUpperBand || prevClose > prevUpperBand) ? upperBand : prevUpperBand;
                    }
                    
                    let direction;
                    if (!prev || prev.superTrend === null) {
                        direction = 1;
                    } else if (prev.superTrend === prev.upperBand) {
                        direction = bars[i].close > upperBand ? -1 : 1;
                    } else {
                        direction = bars[i].close < lowerBand ? 1 : -1;
                    }
                    
                    const superTrend = direction === -1 ? lowerBand : upperBand;
                    const data = [], labels = [];
                    
                    for (let j = 0; j < Math.min(this.n, i); j++) {
                        const idx = i - j;
                        if (idx >= 0 && idx < this.results.length && this.results[idx] && this.results[idx].superTrend !== null) {
                            data.push(this.results[idx].superTrend);
                            const priceWMA = this.wma(bars.slice(Math.max(0, idx - this.KNN_PriceLen), idx + 1).map(b => b.close), Math.min(this.KNN_PriceLen, idx + 1));
                            const stArray = this.results.slice(Math.max(0, idx - this.KNN_STLen), idx + 1).filter(r => r && r.superTrend !== null).map(r => r.superTrend);
                            const stWMA = stArray.length > 0 ? this.wma(stArray, Math.min(this.KNN_STLen, stArray.length)) : null;
                            labels.push((priceWMA && stWMA && priceWMA > stWMA) ? 1 : 0);
                        }
                    }
                    
                    let label = 0;
                    if (data.length >= this.k) {
                        label = this.knnWeighted(data, labels, this.k, superTrend);
                    }
                    
                    const color = label >= 0.5 ? this.upCol : this.dnCol;
                    this.results.push({ time: bars[i].time, superTrend, direction, color, label, upperBand, lowerBand });
                }
                return this.results;
            }
            
            getLineData() {
                return this.results.filter(r => r.superTrend !== null).map(r => ({ time: r.time, value: r.superTrend, color: r.color }));
            }
            
            getSignals() {
                const signals = [];
                for (let i = 1; i < this.results.length; i++) {
                    const curr = this.results[i];
                    const prev = this.results[i - 1];
                    if (!curr || !prev || !curr.superTrend || !prev.superTrend) continue;
                    
                    if (curr.color === this.upCol && prev.color !== this.upCol) {
                        signals.push({ time: curr.time, position: 'belowBar', color: this.upCol, shape: 'circle', text: '🟢', type: 'Bullish Trend Start', price: this.bars[i].close, superTrend: curr.superTrend });
                    }
                    if (curr.color === this.dnCol && prev.color !== this.dnCol) {
                        signals.push({ time: curr.time, position: 'aboveBar', color: this.dnCol, shape: 'circle', text: '🔴', type: 'Bearish Trend Start', price: this.bars[i].close, superTrend: curr.superTrend });
                    }
                    if (curr.direction === -1 && prev.direction === 1 && curr.label >= 0.5) {
                        signals.push({ time: curr.time, position: 'belowBar', color: this.upCol, shape: 'arrowUp', text: '▲', type: 'Bullish Signal', price: this.bars[i].close, superTrend: curr.superTrend });
                    }
                    if (curr.direction === 1 && prev.direction === -1 && curr.label < 0.5) {
                        signals.push({ time: curr.time, position: 'aboveBar', color: this.dnCol, shape: 'arrowDown', text: '▼', type: 'Bearish Signal', price: this.bars[i].close, superTrend: curr.superTrend });
                    }
                }
                return signals;
            }
            
            getTrendPeriods() {
                const periods = [];
                let currentTrend = null, trendStart = null, startPrice = null;
                
                for (let i = 0; i < this.results.length; i++) {
                    const result = this.results[i];
                    if (!result || !result.superTrend) continue;
                    
                    const trend = result.color === this.upCol ? 'Bullish' : result.color === this.dnCol ? 'Bearish' : 'Neutral';
                    
                    if (trend !== currentTrend && trend !== 'Neutral') {
                        if (currentTrend !== null && trendStart !== null) {
                            periods.push({
                                trend: currentTrend,
                                startTime: this.bars[trendStart].time,
                                endTime: this.bars[i - 1].time,
                                duration: i - trendStart,
                                startPrice: startPrice,
                                endPrice: this.bars[i - 1].close
                            });
                        }
                        currentTrend = trend;
                        trendStart = i;
                        startPrice = this.bars[i].close;
                    }
                }
                
                if (currentTrend !== null && trendStart !== null) {
                    const lastIdx = this.results.length - 1;
                    periods.push({
                        trend: currentTrend,
                        startTime: this.bars[trendStart].time,
                        endTime: this.bars[lastIdx].time,
                        duration: lastIdx - trendStart + 1,
                        startPrice: startPrice,
                        endPrice: this.bars[lastIdx].close
                    });
                }
                
                return periods;
            }
        }

        // ========================================
        // Chart & UI
        // ========================================
        let chart, candlestickSeries, superTrendSeries;
        let derivFetcher = null;
        let currentData = [];

        function setStatus(message, type = 'loading') {
            const statusEl = document.getElementById('status');
            statusEl.textContent = message;
            statusEl.className = `status ${type}`;
        }

        function initChart() {
            const chartElement = document.getElementById('chart');
            chart = LightweightCharts.createChart(chartElement, {
                width: chartElement.clientWidth,
                height: 600,
                layout: { backgroundColor: '#131722', textColor: '#d1d4dc' },
                grid: { vertLines: { color: '#2B2B43' }, horzLines: { color: '#2B2B43' } },
                crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                rightPriceScale: { borderColor: '#2B2B43' },
                timeScale: { borderColor: '#2B2B43', timeVisible: true, secondsVisible: false },
            });

            candlestickSeries = chart.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });

            superTrendSeries = chart.addLineSeries({
                color: '#2196F3',
                lineWidth: 2,
                title: 'SuperTrend AI',
            });
            
            window.addEventListener('resize', () => {
                chart.applyOptions({ width: chartElement.clientWidth });
            });
        }

        async function loadDerivData() {
            const btn = document.getElementById('loadBtn');
            btn.disabled = true;
            
            try {
                setStatus('🔄 Connecting to Deriv...', 'loading');
                
                if (!derivFetcher) {
                    derivFetcher = new DerivDataFetcher();
                    await derivFetcher.connect();
                }
                
                const symbol = document.getElementById('symbol').value;
                const timeframe = parseInt(document.getElementById('timeframe').value);
                const count = parseInt(document.getElementById('candleCount').value);
                
                setStatus(`📥 Loading ${count} candles from ${symbol}...`, 'loading');
                
                const derivData = await derivFetcher.getOHLC(symbol, timeframe, count);
                currentData = derivFetcher.convertToIndicatorFormat(derivData);
                
                if (currentData.length === 0) {
                    throw new Error('No valid data received');
                }
                
                setStatus(`✅ Loaded ${currentData.length} candles successfully!`, 'success');
                
                candlestickSeries.setData(currentData);
                chart.timeScale().fitContent();
                
                updateIndicator();
                
                document.getElementById('analysisSection').style.display = 'block';
                
            } catch (error) {
                console.error('Error:', error);
                setStatus(`❌ Error: ${error.message}`, 'error');
            } finally {
                btn.disabled = false;
            }
        }

        function updateIndicator() {
            if (currentData.length === 0) return;
            
            const options = {
                k: parseInt(document.getElementById('k').value),
                n_: parseInt(document.getElementById('n').value),
                KNN_PriceLen: parseInt(document.getElementById('priceLen').value),
                KNN_STLen: parseInt(document.getElementById('stLen').value),
                len: parseInt(document.getElementById('len').value),
                factor: parseFloat(document.getElementById('factor').value),
                maSrc: document.getElementById('maType').value,
                upCol: '#00FF00',
                dnCol: '#FF0000',
            };

            console.log('🔄 Calculating indicator...');
            const indicator = new VolumeSuperTrendAI(options);
            indicator.calculate(currentData);
            
            const lineData = indicator.getLineData();
            console.log('📈 Line data points:', lineData.length);
            superTrendSeries.setData(lineData);
            
            const signals = indicator.getSignals();
            console.log('🎯 Signals found:', signals.length);
            
            const markers = signals.map(s => ({
                time: s.time,
                position: s.position,
                color: s.color,
                shape: s.shape,
                text: s.text
            }));
            candlestickSeries.setMarkers(markers);
            
            updateAnalysisTables(indicator, signals);
        }

        function formatDate(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function getSignalDescription(type) {
            const descriptions = {
                'Bullish Trend Start': '🟢 AI detects the beginning of a bullish trend. The line color changes to green, indicating potential upward movement.',
                'Bearish Trend Start': '🔴 AI detects the beginning of a bearish trend. The line color changes to red, indicating potential downward movement.',
                'Bullish Signal': '▲ Strong bullish confirmation. SuperTrend flips to support, and AI predicts upward price movement. Consider long positions.',
                'Bearish Signal': '▼ Strong bearish confirmation. SuperTrend flips to resistance, and AI predicts downward price movement. Consider short positions.'
            };
            return descriptions[type] || type;
        }
        
        function updateAnalysisTables(indicator, signals) {
            const bullishSignals = signals.filter(s => s.type.includes('Bullish')).length;
            const bearishSignals = signals.filter(s => s.type.includes('Bearish')).length;
            
            document.getElementById('bullishCount').textContent = bullishSignals;
            document.getElementById('bearishCount').textContent = bearishSignals;
            
            const lastResult = indicator.results[indicator.results.length - 1];
            if (lastResult && lastResult.color) {
                const trendText = lastResult.color === '#00FF00' ? 'Bullish 📈' : 
                                 lastResult.color === '#FF0000' ? 'Bearish 📉' : 'Neutral';
                const trendColor = lastResult.color === '#00FF00' ? '#00FF00' : 
                                  lastResult.color === '#FF0000' ? '#FF0000' : '#787b86';
                document.getElementById('currentTrend').textContent = trendText;
                document.getElementById('currentTrend').style.color = trendColor;
            }
            
            const trendPeriods = indicator.getTrendPeriods();
            if (trendPeriods.length > 0) {
                const currentPeriod = trendPeriods[trendPeriods.length - 1];
                document.getElementById('trendDuration').textContent = `${currentPeriod.duration} bars`;
            }
            
            const signalTableBody = document.getElementById('signalTableBody');
            signalTableBody.innerHTML = '';
            
            if (signals.length === 0) {
                signalTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #787b86;">No signals detected</td></tr>';
            } else {
                const sortedSignals = [...signals].reverse();
                
                sortedSignals.forEach(signal => {
                    const row = document.createElement('tr');
                    const isBullish = signal.type.includes('Bullish');
                    row.className = isBullish ? 'trend-up' : 'trend-down';
                    
                    const description = getSignalDescription(signal.type);
                    
                    row.innerHTML = `
                        <td>${formatDate(signal.time)}</td>
                        <td class="${isBullish ? 'signal-bullish' : 'signal-bearish'}">${signal.text} ${signal.type}</td>
                        <td>${signal.price.toFixed(2)}</td>
                        <td>${signal.superTrend.toFixed(2)}</td>
                        <td class="${isBullish ? 'signal-bullish' : 'signal-bearish'}">${isBullish ? '↑ UP' : '↓ DOWN'}</td>
                        <td style="font-size: 12px;">${description}</td>
                    `;
                    signalTableBody.appendChild(row);
                });
            }
            
            const trendTableBody = document.getElementById('trendTableBody');
            trendTableBody.innerHTML = '';
            
            if (trendPeriods.length === 0) {
                trendTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #787b86;">No trend periods detected</td></tr>';
            } else {
                const reversedPeriods = [...trendPeriods].reverse();
                
                reversedPeriods.forEach((period, index) => {
                    const row = document.createElement('tr');
                    const isBullish = period.trend === 'Bullish';
                    row.className = isBullish ? 'trend-up' : 'trend-down';
                    
                    const priceChange = period.endPrice - period.startPrice;
                    const changePercent = ((priceChange / period.startPrice) * 100).toFixed(2);
                    
                    row.innerHTML = `
                        <td>#${trendPeriods.length - index}</td>
                        <td class="${isBullish ? 'signal-bullish' : 'signal-bearish'}">
                            ${isBullish ? '🟢 Bullish' : '🔴 Bearish'}
                        </td>
                        <td>${formatDate(period.startTime)}</td>
                        <td>${formatDate(period.endTime)}</td>
                        <td>${period.duration}</td>
                        <td class="${priceChange >= 0 ? 'signal-bullish' : 'signal-bearish'}">
                            ${priceChange >= 0 ? '+' : ''}${priceChange.toFixed(2)}
                        </td>
                        <td class="${priceChange >= 0 ? 'signal-bullish' : 'signal-bearish'}">
                            ${priceChange >= 0 ? '+' : ''}${changePercent}%
                        </td>
                    `;
                    trendTableBody.appendChild(row);
                });
            }
            
            console.log('✅ Analysis tables updated');
        }

        // Initialize
        console.log('🚀 Initializing application...');
        initChart();
        console.log('✅ Chart ready. Click "Load Data" to fetch from Deriv.');
    </script>

<?php
  function Manual() {  ?>
  
<div class="trend-parallel-guide">
    <style>
        .trend-parallel-guide {
            background: #2a2e39;
            padding: 20px;
            border-radius: 8px;
            color: #d1d4dc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 20px 0;
        }
        .trend-parallel-guide h2 {
            color: #2962ff;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .trend-parallel-guide h3 {
            color: #d1d4dc;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 18px;
            border-left: 4px solid #2962ff;
            padding-left: 12px;
        }
        .guide-box {
            background: #131722;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .guide-box.trend {
            border-left: 4px solid #00FF00;
        }
        .guide-box.parallel {
            border-left: 4px solid #FFA726;
        }
        .guide-box.balanced {
            border-left: 4px solid #2962ff;
        }
        .variable-item {
            background: rgba(255, 255, 255, 0.03);
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .variable-name {
            font-weight: bold;
            color: #2962ff;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .variable-desc {
            color: #d1d4dc;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        .value-range {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .range-item {
            flex: 1;
            background: rgba(41, 98, 255, 0.1);
            padding: 8px;
            border-radius: 4px;
            font-size: 13px;
        }
        .range-item strong {
            display: block;
            color: #2962ff;
            margin-bottom: 4px;
        }
        .comparison-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .comparison-table th {
            background: #1e222d;
            padding: 10px;
            text-align: left;
            color: #2962ff;
            font-weight: 600;
        }
        .comparison-table td {
            padding: 10px;
            border-bottom: 1px solid #2a2e39;
        }
        .comparison-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        .code-block {
            background: #1e222d;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
            border-left: 3px solid #2962ff;
        }
        .highlight-green {
            color: #00FF00;
            font-weight: bold;
        }
        .highlight-orange {
            color: #FFA726;
            font-weight: bold;
        }
        .highlight-red {
            color: #FF0000;
            font-weight: bold;
        }
        .tip-box {
            background: rgba(41, 98, 255, 0.1);
            padding: 12px;
            border-radius: 4px;
            border-left: 4px solid #2962ff;
            margin: 15px 0;
        }
        .detection-method {
            background: #131722;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .detection-method h4 {
            color: #FFA726;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .method-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .method-list li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        .method-list li:before {
            content: "▸";
            position: absolute;
            left: 0;
            color: #2962ff;
            font-weight: bold;
        }
    </style>

    <h2>📊 คู่มือการตรวจจับ Trend vs Parallel</h2>

    <!-- ส่วนที่ 1: ตัวแปรที่ควบคุมความชัน -->
    <h3>🎯 ตัวแปรที่ควบคุมความชันของกราฟ</h3>

    <div class="variable-item">
        <div class="variable-name">1. factor (ATR Multiplier) ⭐ สำคัญที่สุด!</div>
        <div class="variable-desc">
            ควบคุมระยะห่างระหว่างเส้น SuperTrend กับราคา - เป็นตัวแปรหลักที่กำหนดว่าจะจับ Trend ใหญ่ หรือ Movement เล็กๆ
        </div>
        <div class="code-block">factor: 3.0  // ค่าเริ่มต้น</div>
        <div class="value-range">
            <div class="range-item">
                <strong class="highlight-green">เพิ่มค่า (4.0 - 5.0)</strong>
                เส้นห่างจากราคา → จับ Trend ใหญ่ → เส้นชันมาก, เปลี่ยนสีช้า
            </div>
            <div class="range-item">
                <strong class="highlight-red">ลดค่า (1.5 - 2.5)</strong>
                เส้นใกล้ราคา → ไวต่อการเปลี่ยนแปลง → เส้น Parallel บ่อย, เปลี่ยนสีเร็ว
            </div>
        </div>
    </div>

    <div class="variable-item">
        <div class="variable-name">2. len (SuperTrend Length)</div>
        <div class="variable-desc">
            ควบคุมความนุ่มนวลของเส้น - ระยะเวลาที่ใช้คำนวณ ATR
        </div>
        <div class="code-block">len: 10  // ค่าเริ่มต้น</div>
        <div class="value-range">
            <div class="range-item">
                <strong class="highlight-green">เพิ่มค่า (14 - 30)</strong>
                เส้นนุ่มนวล → จับ Trend ยาว → ชันน้อย, ไม่กระตุก
            </div>
            <div class="range-item">
                <strong class="highlight-red">ลดค่า (5 - 8)</strong>
                เส้นไว → เปลี่ยนทิศเร็ว → ชันมากแต่กระตุก
            </div>
        </div>
    </div>

    <div class="variable-item">
        <div class="variable-name">3. KNN_STLen (Prediction Trend)</div>
        <div class="variable-desc">
            ควบคุมระยะเวลาที่ AI มอง - กำหนดว่า AI จะวิเคราะห์ในมุมมอง Short-term หรือ Long-term
        </div>
        <div class="code-block">KNN_STLen: 100  // ค่าเริ่มต้น</div>
        <div class="value-range">
            <div class="range-item">
                <strong class="highlight-green">เพิ่มค่า (150 - 300)</strong>
                AI มอง Long-term → เส้นชันน้อย, Smooth
            </div>
            <div class="range-item">
                <strong class="highlight-red">ลดค่า (30 - 70)</strong>
                AI มอง Short-term → ไวต่อ Trend เล็ก
            </div>
        </div>
    </div>

    <!-- ตารางเปรียบเทียบ -->
    <h3>⚖️ ตารางเปรียบเทียบ Setting</h3>
    
    <table class="comparison-table">
        <thead>
            <tr>
                <th>ประเภท</th>
                <th>factor</th>
                <th>len</th>
                <th>KNN_STLen</th>
                <th>ผลลัพธ์</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="highlight-green">🟢 จับ TREND ชัด</span></td>
                <td>4.0 - 5.0</td>
                <td>14 - 20</td>
                <td>150 - 200</td>
                <td>เส้นชัน, ทิศทางชัดเจน, ไม่กระตุก</td>
            </tr>
            <tr>
                <td><span class="highlight-red">🔴 จับทุก Movement</span></td>
                <td>1.5 - 2.5</td>
                <td>5 - 8</td>
                <td>30 - 50</td>
                <td>เส้น Parallel บ่อย, เปลี่ยนสีเร็ว</td>
            </tr>
            <tr>
                <td><span class="highlight-orange">⚖️ Balanced (แนะนำ)</span></td>
                <td>3.0</td>
                <td>10</td>
                <td>100</td>
                <td>สมดุล, ใช้ได้ทั่วไป</td>
            </tr>
        </tbody>
    </table>

    <!-- ส่วนที่ 2: การตรวจจับ Trend → Parallel -->
    <h3>🚨 การตรวจจับเมื่อกราฟเปลี่ยนจาก Trend → Parallel</h3>

    <div class="detection-method">
        <h4>วิธีที่ 1: นับการเปลี่ยนสี (Color Changes)</h4>
        <div class="variable-desc">
            ตรวจจับจำนวนครั้งที่เส้นเปลี่ยนสีในช่วง N แท่งหลังสุด
        </div>
        <div class="code-block">
// ดูที่ results[i].color
let colorChanges = 0;
for (let i = 1; i < recentResults.length; i++) {
    if (results[i].color !== results[i-1].color) {
        colorChanges++;
    }
}

// ถ้าเปลี่ยนสี >= 4 ครั้ง ใน 20 แท่ง = Parallel
if (colorChanges >= 4) {
    alert('⚠️ ตลาดกำลัง Ranging (Parallel)!');
}
        </div>
        <ul class="method-list">
            <li><strong>เปลี่ยนสีน้อย</strong> (1-2 ครั้ง) = <span class="highlight-green">Strong Trend</span></li>
            <li><strong>เปลี่ยนสีปานกลาง</strong> (3-4 ครั้ง) = <span class="highlight-orange">Weak Trend</span></li>
            <li><strong>เปลี่ยนสีบ่อย</strong> (5+ ครั้ง) = <span class="highlight-red">Parallel/Ranging</span></li>
        </ul>
    </div>

    <div class="detection-method">
        <h4>วิธีที่ 2: คำนวณความชัน (Slope)</h4>
        <div class="variable-desc">
            วัดความชันของเส้น SuperTrend - ถ้าใกล้ 0 แสดงว่าเป็น Parallel
        </div>
        <div class="code-block">
// คำนวณความชันระหว่าง 2 จุด
const slope = (superTrend[i] - superTrend[i-1]) / (time[i] - time[i-1]);

// คำนวณความชันเฉลี่ยใน N แท่ง
let totalSlope = 0;
for (let i = 1; i < recentResults.length; i++) {
    const s = Math.abs(
        (results[i].superTrend - results[i-1].superTrend) / 
        (results[i].time - results[i-1].time)
    );
    totalSlope += s;
}
const avgSlope = totalSlope / (recentResults.length - 1);

// ถ้าความชันน้อยกว่า threshold = Parallel
if (avgSlope < 0.5) {
    alert('⚠️ เส้นวิ่งแนวราบ - ตลาด Ranging!');
}
        </div>
        <ul class="method-list">
            <li><strong>Slope สูง</strong> (&gt; 1.0) = <span class="highlight-green">Strong Trend</span></li>
            <li><strong>Slope ปานกลาง</strong> (0.5 - 1.0) = <span class="highlight-orange">Moderate Trend</span></li>
            <li><strong>Slope ต่ำ</strong> (&lt; 0.5) = <span class="highlight-red">Parallel/Flat</span></li>
        </ul>
    </div>

    <div class="detection-method">
        <h4>วิธีที่ 3: อายุของ Trend (Trend Duration)</h4>
        <div class="variable-desc">
            วัดว่า Trend ปัจจุบันมีอายุกี่แท่ง - ถ้าสั้นมาก แสดงว่าไม่ทรงตัว
        </div>
        <div class="code-block">
// หาจำนวนแท่งที่เส้นเป็นสีเดียวกัน
const currentColor = results[results.length - 1].color;
let trendDuration = 0;

for (let i = results.length - 1; i >= 0; i--) {
    if (results[i].color === currentColor) {
        trendDuration++;
    } else {
        break;
    }
}

// ถ้า Trend สั้นมาก (< 10 แท่ง) = ไม่มี Trend ชัด
if (trendDuration < 10) {
    alert('⚠️ Trend ไม่ทรงตัว - อาจกำลังเข้าสู่ Ranging!');
}
        </div>
        <ul class="method-list">
            <li><strong>Duration ยาว</strong> (&gt; 20 แท่ง) = <span class="highlight-green">Strong Sustained Trend</span></li>
            <li><strong>Duration ปานกลาง</strong> (10-20 แท่ง) = <span class="highlight-orange">Moderate Trend</span></li>
            <li><strong>Duration สั้น</strong> (&lt; 10 แท่ง) = <span class="highlight-red">Choppy/Parallel</span></li>
        </ul>
    </div>

    <!-- ตัวอย่างแบบรวม -->
    <h3>💻 ตัวอย่าง Code แบบรวม (All-in-One Detection)</h3>
    
    <div class="code-block">
function detectMarketState(indicator) {
    const results = indicator.results;
    const lookback = 20; // ดูย้อนหลัง 20 แท่ง
    const recentResults = results.slice(-lookback);
    
    // 1. นับการเปลี่ยนสี
    let colorChanges = 0;
    for (let i = 1; i < recentResults.length; i++) {
        if (recentResults[i].color !== recentResults[i-1].color) {
            colorChanges++;
        }
    }
    
    // 2. คำนวณความชันเฉลี่ย
    let totalSlope = 0;
    for (let i = 1; i < recentResults.length; i++) {
        const slope = Math.abs(
            (recentResults[i].superTrend - recentResults[i-1].superTrend) /
            (recentResults[i].time - recentResults[i-1].time)
        );
        totalSlope += slope;
    }
    const avgSlope = totalSlope / (recentResults.length - 1);
    
    // 3. หาอายุ Trend ปัจจุบัน
    const currentColor = results[results.length - 1].color;
    let trendDuration = 0;
    for (let i = results.length - 1; i >= 0; i--) {
        if (results[i].color === currentColor) {
            trendDuration++;
        } else {
            break;
        }
    }
    
    // 4. ตัดสินใจ
    let parallelScore = 0;
    
    if (colorChanges >= 4) parallelScore += 0.4;
    if (avgSlope < 0.5) parallelScore += 0.3;
    if (trendDuration < 10) parallelScore += 0.3;
    
    const isParallel = parallelScore >= 0.6;
    
    return {
        state: isParallel ? 'PARALLEL' : 'TREND',
        colorChanges: colorChanges,
        avgSlope: avgSlope.toFixed(4),
        trendDuration: trendDuration,
        confidence: (parallelScore * 100).toFixed(0) + '%'
    };
}

// วิธีใช้งาน
const marketState = detectMarketState(indicator);

if (marketState.state === 'PARALLEL') {
    alert('⚠️ ALERT: ตลาดกำลัง RANGING (Parallel) - หลีกเลี่ยงการเทรด Trend!');
    console.log('Details:', marketState);
} else {
    alert('✅ ตลาดมี TREND ชัดเจน - เทรดตาม Trend ได้!');
    console.log('Details:', marketState);
}
    </div>

    <!-- สรุป -->
    <div class="tip-box">
        <strong>💡 สรุปสั้นๆ:</strong>
        <ul class="method-list">
            <li><strong>Trend:</strong> เส้นชัน, สีคงที่นาน, ความชันสูง</li>
            <li><strong>Parallel:</strong> เส้นราบ, เปลี่ยนสีบ่อย, ความชันต่ำ</li>
            <li><strong>ตัวแปรสำคัญ:</strong> factor, len, KNN_STLen</li>
            <li><strong>ตรวจจับด้วย:</strong> Color Changes + Slope + Trend Duration</li>
        </ul>
    </div>

    <div class="guide-box trend">
        <strong style="color: #00FF00;">🟢 เมื่อเป็น TREND:</strong>
        <p style="margin: 10px 0 0 0;">เทรดตามทิศทาง, ใช้ลูกศร ▲▼ เป็นจุดเข้า, ออกเมื่อสีเปลี่ยน</p>
    </div>

    <div class="guide-box parallel">
        <strong style="color: #FFA726;">⚠️ เมื่อเป็น PARALLEL:</strong>
        <p style="margin: 10px 0 0 0;">หลีกเลี่ยงการเทรด Trend, พิจารณาใช้กลยุทธ์ Range Trading หรือรอให้ Breakout</p>
    </div>
</div>

  <?php
  } // end function
  
?>
</body>
</html>