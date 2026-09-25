<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Trading Chart - Big Snapper Alerts</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #131722;
            color: #d1d4dc;
            overflow: hidden;
        }

        .header {
            background: #1e222d;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #2a2e39;
        }

        .symbol-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .symbol-name {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }

        .price-info {
            display: flex;
            gap: 8px;
            font-size: 12px;
        }

        .price-item {
            color: #787b86;
        }

        .price-item.positive {
            color: #26a69a;
        }

        .price-item.negative {
            color: #ef5350;
        }

        .price-item span {
            margin-left: 3px;
        }

        .controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        select, button {
            background: #2a2e39;
            color: #d1d4dc;
            border: 1px solid #434651;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }

        button:hover, select:hover {
            background: #363a45;
        }

        button.active {
            background: #2962ff;
            border-color: #2962ff;
        }

        .chart-container {
            width: 100%;
            height: calc(100vh - 50px);
            position: relative;
        }

        .status {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(30, 34, 45, 0.95);
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 11px;
            z-index: 10;
            border: 1px solid #2a2e39;
        }

        .status.connected {
            color: #26a69a;
        }

        .status.disconnected {
            color: #ef5350;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #787b86;
        }

        .signal-panel {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(30, 34, 45, 0.95);
            padding: 10px;
            border-radius: 4px;
            font-size: 11px;
            z-index: 10;
            border: 1px solid #2a2e39;
            min-width: 150px;
        }

        .signal-panel h3 {
            font-size: 12px;
            margin-bottom: 8px;
            color: #fff;
        }

        .signal-item {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            padding: 4px;
            border-radius: 2px;
        }

        .signal-item.buy {
            background: rgba(38, 166, 154, 0.1);
            color: #26a69a;
        }

        .signal-item.sell {
            background: rgba(239, 83, 80, 0.1);
            color: #ef5350;
        }

        .trade-buttons {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .trade-btn {
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            cursor: pointer;
        }

        .trade-btn.buy {
            background: #26a69a;
            color: white;
        }

        .trade-btn.sell {
            background: #ef5350;
            color: white;
        }

        .trade-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="symbol-info">
            <div class="symbol-name">EUR/USD</div>
            <div class="price-info">
                <div class="price-item">O <span id="open">-</span></div>
                <div class="price-item">H <span id="high">-</span></div>
                <div class="price-item">L <span id="low">-</span></div>
                <div class="price-item" id="close-item">C <span id="close">-</span></div>
                <div class="price-item" id="change-item"><span id="change">-</span></div>
            </div>
        </div>
        <div class="controls">
            <select id="symbol">
                <option value="frxEURUSD">EUR/USD</option>
                <option value="frxGBPUSD">GBP/USD</option>
                <option value="frxUSDJPY">USD/JPY</option>
                <option value="frxAUDUSD">AUD/USD</option>
            </select>
            <select id="timeframe">
                <option value="60">1m</option>
                <option value="300">5m</option>
                <option value="900">15m</option>
                <option value="3600">1h</option>
            </select>
            <button id="toggleIndicators">Indicators</button>
            <button id="refresh">Refresh</button>
        </div>
    </div>

    <div class="chart-container">
        <div class="loading" id="loading">Loading chart data...</div>
        <div class="status disconnected" id="status">Connecting...</div>
        
        <div class="trade-buttons" id="tradeButtons" style="display:none;">
            <button class="trade-btn buy" id="buyBtn">BUY 1.16580</button>
            <button class="trade-btn sell" id="sellBtn">SELL 1.16570</button>
        </div>

        <div class="signal-panel" id="signalPanel" style="display:none;">
            <h3>📊 Big Snapper Signals</h3>
            <div id="signalList"></div>
        </div>

        <div id="chart"></div>
    </div>

    <script>
        // Big Snapper Alerts Class
        class BigSnapperAlerts {
            constructor(config = {}) {
                this.config = {
                    typeColoured: config.typeColoured || 'HullMA',
                    lenColoured: config.lenColoured || 18,
                    typeFast: config.typeFast || 'EMA',
                    lenFast: config.lenFast || 21,
                    typeMedium: config.typeMedium || 'EMA',
                    lenMedium: config.lenMedium || 55,
                    typeSlow: config.typeSlow || 'EMA',
                    lenSlow: config.lenSlow || 89,
                    filterOption: config.filterOption || 'SuperTrend',
                    bbLength: config.bbLength || 20,
                    bbStddev: config.bbStddev || 2.0,
                    oiLength: config.oiLength || 8,
                    SFactor: config.SFactor || 3.618,
                    SPd: config.SPd || 5,
                    disableFastMAFilter: false,
                    disableMediumMAFilter: false,
                    disableSlowMAFilter: false
                };
                this.prevValues = {};
				
            }

            sma(data, period) {
                if (data.length < period) return null;
                const sum = data.slice(-period).reduce((a, b) => a + b, 0);
                return sum / period;
            }

            ema(data, period) {
                if (data.length === 0) return null;
                const k = 2 / (period + 1);
                let ema = data[0];
                for (let i = 1; i < data.length; i++) {
                    ema = data[i] * k + ema * (1 - k);
                }
                return ema;
            }

            wma(data, period) {
                if (data.length < period) return null;
                let sum = 0, weightSum = 0;
                const slice = data.slice(-period);
                for (let i = 0; i < period; i++) {
                    const weight = i + 1;
                    sum += slice[i] * weight;
                    weightSum += weight;
                }
                return sum / weightSum;
            }

            hullMA(data, period) {
                const halfPeriod = Math.floor(period / 2);
                const sqrtPeriod = Math.round(Math.sqrt(period));
                
                if (data.length < period) return null;
                
                const wma1 = this.wma(data, halfPeriod);
                const wma2 = this.wma(data, period);
                
                if (wma1 === null || wma2 === null) return null;
                
                const hullData = [];
                for (let i = Math.max(halfPeriod, period); i <= data.length; i++) {
                    const w1 = this.wma(data.slice(0, i), halfPeriod);
                    const w2 = this.wma(data.slice(0, i), period);
                    if (w1 !== null && w2 !== null) {
                        hullData.push(2 * w1 - w2);
                    }
                }
                
                return this.wma(hullData, sqrtPeriod);
            }

            variant(type, data, period) {
                switch (type) {
                    case 'SMA': return this.sma(data, period);
                    case 'EMA': return this.ema(data, period);
                    case 'WMA': return this.wma(data, period);
                    case 'HullMA': return this.hullMA(data, period);
                    default: return this.sma(data, period);
                }
            }

            atr(highs, lows, closes, period) {
                if (highs.length < period + 1) return null;
                const trueRanges = [];
                for (let i = 1; i < highs.length; i++) {
                    const tr = Math.max(
                        highs[i] - lows[i],
                        Math.abs(highs[i] - closes[i - 1]),
                        Math.abs(lows[i] - closes[i - 1])
                    );
                    trueRanges.push(tr);
                }
                return this.sma(trueRanges.slice(-period), period);
            }

            stdev(data, period) {
                if (data.length < period) return null;
                const mean = this.sma(data, period);
                const slice = data.slice(-period);
                const variance = slice.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) / period;
                return Math.sqrt(variance);
            }

            rising(values, period) {
                if (values.length < period) return false;
                for (let i = values.length - period; i < values.length - 1; i++) {
                    if (values[i + 1] <= values[i]) return false;
                }
                return true;
            }

            falling(values, period) {
                if (values.length < period) return false;
                for (let i = values.length - period; i < values.length - 1; i++) {
                    if (values[i + 1] >= values[i]) return false;
                }
                return true;
            }

            calculate(ohlcvData) {
                const { open, high, low, close } = ohlcvData;
                
                if (!close || close.length < Math.max(this.config.lenSlow, this.config.bbLength)) {
                    return null;
                }

                // Calculate MAs
                const ma_coloured = this.variant(this.config.typeColoured, close, this.config.lenColoured);
                const ma_fast = this.variant(this.config.typeFast, close, this.config.lenFast);
                const ma_medium = this.variant(this.config.typeMedium, close, this.config.lenMedium);
                const ma_slow = this.variant(this.config.typeSlow, close, this.config.lenSlow);

                // Get MA direction
                const ma_coloured_array = close.map((_, i) => 
                    this.variant(this.config.typeColoured, close.slice(0, i + 1), this.config.lenColoured)
                ).filter(v => v !== null);

                let clrdirection = this.rising(ma_coloured_array, 2) ? 1 : 
                                 this.falling(ma_coloured_array, 2) ? -1 : 
                                 (this.prevValues.clrdirection || 1);

                // 3xMA Trend
                let madirection = (ma_fast > ma_medium && ma_medium > ma_slow) ? 1 :
                                (ma_fast < ma_medium && ma_medium < ma_slow) ? -1 : 0;

                // SuperTrend
                const hl2 = high.map((h, i) => (h + low[i]) / 2);
                const atrValue = this.atr(high, low, close, this.config.SPd) || 0;
                const SUp = hl2[hl2.length - 1] - (this.config.SFactor * atrValue);
                const SDn = hl2[hl2.length - 1] + (this.config.SFactor * atrValue);

                let STrendUp = this.prevValues.STrendUp || SUp;
                let STrendDown = this.prevValues.STrendDown || SDn;

                if (close.length > 1) {
                    STrendUp = close[close.length - 2] > STrendUp ? Math.max(SUp, STrendUp) : SUp;
                    STrendDown = close[close.length - 2] < STrendDown ? Math.min(SDn, STrendDown) : SDn;
                }

                let STrend = close[close.length - 1] > STrendDown ? 1 : 
                           close[close.length - 1] < STrendUp ? -1 : 
                           (this.prevValues.STrend || 1);

                const Tsl = STrend === 1 ? STrendUp : STrendDown;

                // Bollinger Bands
                const basis = this.sma(close, this.config.bbLength);
                const dev = this.config.bbStddev * this.stdev(close, this.config.bbLength);
                const upper = basis + dev;
                const lower = basis - dev;

                // Generate Signals
                const signals = this.generateSignals({
                    clrdirection,
                    madirection,
                    STrend,
                    ma_fast,
                    close: close[close.length - 1]
                });

                // Store prev values
                this.prevValues = { clrdirection, STrendUp, STrendDown, STrend };

                return {
                    ma_coloured,
                    ma_fast,
                    ma_medium,
                    ma_slow,
                    clrdirection,
                    madirection,
                    STrend,
                    Tsl,
                    upper,
                    lower,
                    basis,
                    signals
                };
            }

            generateSignals(data) {
                const { clrdirection, madirection, STrend, ma_fast, close } = data;
                
                let long = false;
                let short = false;

                if (this.config.filterOption === 'SuperTrend') {
                    long = clrdirection === 1 && STrend === 1;
                    short = clrdirection === -1 && STrend === -1;
                } else if (this.config.filterOption === '3xMATrend') {
                    long = clrdirection === 1 && close > ma_fast && madirection === 1;
                    short = clrdirection === -1 && close < ma_fast && madirection === -1;
                } else {
                    long = clrdirection === 1;
                    short = clrdirection === -1;
                }

                return { long, short };
            }
        }

        // Chart Application
        class DerivChart {
            constructor() {
				
                this.ws = null;
                this.chart = null;
                this.candleSeries = null;
                this.indicators = {
                    ma_fast: null,
                    ma_medium: null,
                    ma_slow: null,
                    ma_coloured: null,
                    supertrend: null,
                    bb_upper: null,
                    bb_lower: null
                };
                this.candles = [];
				
				
                this.snapper = new BigSnapperAlerts({
                    typeColoured: 'HullMA',
                    lenColoured: 18,
                    filterOption: 'SuperTrend'
                });
				
                this.showIndicators = true;
                this.init();
				
            }

            init() {
                this.createChart();
                this.connectWebSocket();
				
                this.setupEventListeners();
            }

            createChart() {
                const container = document.getElementById('chart');
                this.chart = LightweightCharts.createChart(container, {
                    width: container.clientWidth,
                    height: container.clientHeight,
                    layout: {
                        backgroundColor: '#131722',
                        textColor: '#d1d4dc',
                    },
                    grid: {
                        vertLines: { color: '#1e222d' },
                        horzLines: { color: '#1e222d' },
                    },
                    crosshair: {
                        mode: LightweightCharts.CrosshairMode.Normal,
                    },
                    rightPriceScale: {
                        borderColor: '#2a2e39',
                    },
                    timeScale: {
                        borderColor: '#2a2e39',
                        timeVisible: true,
                        secondsVisible: false,
                    },
                });

                this.candleSeries = this.chart.addCandlestickSeries({
                    upColor: '#26a69a',
                    downColor: '#ef5350',
                    borderVisible: false,
                    wickUpColor: '#26a69a',
                    wickDownColor: '#ef5350',
                });

                // Create indicator series
                this.indicators.ma_coloured = this.chart.addLineSeries({
                    color: '#2962ff',
                    lineWidth: 3,
                    title: 'Hull MA'
                });

                this.indicators.ma_fast = this.chart.addLineSeries({
                    color: '#26a69a',
                    lineWidth: 2,
                    title: 'Fast MA'
                });

                this.indicators.ma_medium = this.chart.addLineSeries({
                    color: '#ef5350',
                    lineWidth: 2,
                    title: 'Medium MA'
                });

                this.indicators.ma_slow = this.chart.addLineSeries({
                    color: '#787b86',
                    lineWidth: 2,
                    title: 'Slow MA'
                });

                this.indicators.supertrend = this.chart.addLineSeries({
                    color: '#ff6b6b',
                    lineWidth: 2,
                    title: 'SuperTrend'
                });

                this.indicators.bb_upper = this.chart.addLineSeries({
                    color: '#1e90ff',
                    lineWidth: 1,
                    lineStyle: 2,
                    title: 'BB Upper'
                });

                this.indicators.bb_lower = this.chart.addLineSeries({
                    color: '#1e90ff',
                    lineWidth: 1,
                    lineStyle: 2,
                    title: 'BB Lower'
                });

                window.addEventListener('resize', () => {
                    this.chart.applyOptions({
                        width: container.clientWidth,
                        height: container.clientHeight
                    });
                });
            }

            connectWebSocket() {
                const appId = '1089';
                this.ws = new WebSocket(`wss://ws.binaryws.com/websockets/v3?app_id=${appId}`);

                this.ws.onopen = () => {
                    this.updateStatus('Connected', true);
                    this.subscribeToTicks();
                };

                this.ws.onmessage = (msg) => {
                    const data = JSON.parse(msg.data);
                    this.handleMessage(data);
                };

                this.ws.onerror = () => {
                    this.updateStatus('Error', false);
                };

                this.ws.onclose = () => {
                    this.updateStatus('Disconnected', false);
                    setTimeout(() => this.connectWebSocket(), 3000);
                };
            }

            subscribeToTicks() {
                const symbol = document.getElementById('symbol').value;
                const granularity = parseInt(document.getElementById('timeframe').value);

                // Request historical candles
                this.ws.send(JSON.stringify({
                    ticks_history: symbol,
                    adjust_start_time: 1,
                    count: 300,
                    end: 'latest',
                    start: 1,
                    style: 'candles',
                    granularity: granularity
                }));

                // Subscribe to candle updates
                this.ws.send(JSON.stringify({
                    ticks_history: symbol,
                    adjust_start_time: 1,
                    count: 1,
                    end: 'latest',
                    start: 1,
                    style: 'candles',
                    granularity: granularity,
                    subscribe: 1
                }));
            }

            handleMessage(data) {

				//alert(data.msg_type) 
				//console.log(data.msg_type);
				
                if (data.msg_type === 'candles') {
                    this.processCandles(data.candles);
                } else if (data.msg_type === 'ohlc') {
                    this.updateCandle(data.ohlc);
                }
            }

            processCandles(candles) {
                document.getElementById('loading').style.display = 'none';
                
                this.candles = candles.map(c => ({
                    time: c.epoch,
                    open: parseFloat(c.open),
                    high: parseFloat(c.high),
                    low: parseFloat(c.low),
                    close: parseFloat(c.close)
                }));

                this.candleSeries.setData(this.candles);
                this.updateIndicators();
                this.updatePriceInfo();
                
                document.getElementById('tradeButtons').style.display = 'flex';
                document.getElementById('signalPanel').style.display = 'block';
            }

            updateCandle(ohlc) {
                const newCandle = {
                    time: ohlc.epoch,
                    open: parseFloat(ohlc.open),
                    high: parseFloat(ohlc.high),
                    low: parseFloat(ohlc.low),
                    close: parseFloat(ohlc.close)
                };

                const lastCandle = this.candles[this.candles.length - 1];
                if (lastCandle && lastCandle.time === newCandle.time) {
                    this.candles[this.candles.length - 1] = newCandle;
                    this.candleSeries.update(newCandle);
                } else {
                    this.candles.push(newCandle);
                    this.candleSeries.update(newCandle);
                }

                this.updateIndicators();
                this.updatePriceInfo();
            }

            updateIndicators() {
                if (!this.showIndicators || this.candles.length < 100) return;

                const close = this.candles.map(c => c.close);
                const high = this.candles.map(c => c.high);
                const low = this.candles.map(c => c.low);
                const open = this.candles.map(c => c.open);

                const result = this.snapper.calculate({ open, high, low, close });
                
                if (!result) return;

                // Update MA lines
                const ma_coloured_data = [];
                const ma_fast_data = [];
                const ma_medium_data = [];
                const ma_slow_data = [];
                const supertrend_data = [];
                const bb_upper_data = [];
                const bb_lower_data = [];

                for (let i = 100; i < this.candles.length; i++) {
                    const slice = {
                        open: open.slice(0, i + 1),
                        high: high.slice(0, i + 1),
                        low: low.slice(0, i + 1),
                        close: close.slice(0, i + 1)
                    };
                    
                    const calc = this.snapper.calculate(slice);
                    if (calc) {
                        const time = this.candles[i].time;
                        
                        if (calc.ma_coloured) ma_coloured_data.push({ time, value: calc.ma_coloured });
                        if (calc.ma_fast) ma_fast_data.push({ time, value: calc.ma_fast });
                        if (calc.ma_medium) ma_medium_data.push({ time, value: calc.ma_medium });
                        if (calc.ma_slow) ma_slow_data.push({ time, value: calc.ma_slow });
                        if (calc.Tsl) supertrend_data.push({ time, value: calc.Tsl });
                        if (calc.upper) bb_upper_data.push({ time, value: calc.upper });
                        if (calc.lower) bb_lower_data.push({ time, value: calc.lower });
                    }
                }

                this.indicators.ma_coloured.setData(ma_coloured_data);
                this.indicators.ma_fast.setData(ma_fast_data);
                this.indicators.ma_medium.setData(ma_medium_data);
                this.indicators.ma_slow.setData(ma_slow_data);
                this.indicators.supertrend.setData(supertrend_data);
                this.indicators.bb_upper.setData(bb_upper_data);
                this.indicators.bb_lower.setData(bb_lower_data);

                // Update signals
                this.updateSignals(result.signals);
            }

            updateSignals(signals) {
                const signalList = document.getElementById('signalList');
                if (signals.long) {
                    signalList.innerHTML = '<div class="signal-item buy">🟢 BUY Signal Active</div>';
                } else if (signals.short) {
                    signalList.innerHTML = '<div class="signal-item sell">🔴 SELL Signal Active</div>';
                } else {
                    signalList.innerHTML = '<div class="signal-item">⏸ No Signal</div>';
                }
            }

            updatePriceInfo() {
                if (this.candles.length === 0) return;

                const last = this.candles[this.candles.length - 1];
                const prev = this.candles.length > 1 ? this.candles[this.candles.length - 2] : last;
                
                document.getElementById('open').textContent = last.open.toFixed(5);
                document.getElementById('high').textContent = last.high.toFixed(5);
                document.getElementById('low').textContent = last.low.toFixed(5);
                document.getElementById('close').textContent = last.close.toFixed(5);

                const change = last.close - prev.close;
                const changePercent = (change / prev.close * 100).toFixed(2);
                const changeText = `${change > 0 ? '+' : ''}${change.toFixed(5)} (${changePercent}%)`;
                
                document.getElementById('change').textContent = changeText;
                document.getElementById('change-item').className = change >= 0 ? 'price-item positive' : 'price-item negative';
                document.getElementById('close-item').className = change >= 0 ? 'price-item positive' : 'price-item negative';

                // Update trade buttons
                document.getElementById('buyBtn').textContent = `BUY ${last.close.toFixed(5)}`;
                document.getElementById('sellBtn').textContent = `SELL ${last.close.toFixed(5)}`;
            }

            updateStatus(text, connected) {
                const status = document.getElementById('status');
                status.textContent = text;
                status.className = connected ? 'status connected' : 'status disconnected';
            }

            setupEventListeners() {
                document.getElementById('symbol').addEventListener('change', () => {
                    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                        this.subscribeToTicks();
                    }
                });

                document.getElementById('timeframe').addEventListener('change', () => {
                    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                        this.subscribeToTicks();
                    }
                });

                document.getElementById('toggleIndicators').addEventListener('click', (e) => {
                    this.showIndicators = !this.showIndicators;
                    e.target.classList.toggle('active');
                    
                    Object.values(this.indicators).forEach(series => {
                        if (series) {
                            series.applyOptions({
                                visible: this.showIndicators
                            });
                        }
                    });
                });

                document.getElementById('refresh').addEventListener('click', () => {
                    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                        this.subscribeToTicks();
                    }
                });

                document.getElementById('buyBtn').addEventListener('click', () => {
                    const last = this.candles[this.candles.length - 1];
                    alert(`BUY order at ${last.close.toFixed(5)}\n\nNote: This is a demo. Connect to Deriv API for real trading.`);
                });

                document.getElementById('sellBtn').addEventListener('click', () => {
                    const last = this.candles[this.candles.length - 1];
                    alert(`SELL order at ${last.close.toFixed(5)}\n\nNote: This is a demo. Connect to Deriv API for real trading.`);
                });
            }

            toggleIndicatorVisibility() {
                Object.values(this.indicators).forEach(series => {
                    if (series) {
                        series.applyOptions({
                            visible: this.showIndicators
                        });
                    }
                });
            }
        }

        // Initialize the chart when page loads
	 
        window.addEventListener('DOMContentLoaded', () => {
            new DerivChart();
        });
		 
    </script>
</body>
</html>