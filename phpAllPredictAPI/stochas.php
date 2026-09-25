ให้สร้าง  html page สำหรับวาด candlestick + stochastic + CCI (Commodity Channel Index) ด้วย
lightweightchart <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script> 
โดย ให้ ดึงข้อมูล จาก deriv.com จาก ช่วงเวลา 2 ช่วง ที่ timeframe ต่างๆกัน นับแต่ 1 นาที ถึง 1 ชั่วโมง และให้เลือก asset ได้ 
จากนั้น ทำการ วิเคราะห์ ช่วงที่ควรเป็น จุดเข้าซื้อ สำหรับ เทรด แบบ Binary Option พร้อมทั้ง แสดงผลเป็น  html table และวาด marker ในกราฟสำหรับ จุดเข้า  ไม่เอา mock data เด็ดขาด

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Trading Analyzer</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        .controls {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .control-row {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        label {
            font-weight: bold;
            color: #333;
            font-size: 12px;
        }
        select, button, input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="datetime-local"] {
            width: 180px;
        }
        button {
            background: #007bff;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .charts-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .chart-section {
            flex: 1;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .chart-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-weight: bold;
        }
        .chart-container {
            height: 400px;
        }
        .indicators-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .indicator-chart {
            flex: 1;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .indicator-chart-container {
            height: 200px;
        }
        .analysis-section {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .analysis-table {
            flex: 1;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .analysis-reasoning {
            flex: 1;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .analysis-header {
            background: #007bff;
            color: white;
            padding: 15px;
            font-weight: bold;
        }
        .reasoning-header {
            background: #28a745;
            color: white;
            padding: 15px;
            font-weight: bold;
        }
        .reasoning-content {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        .reasoning-item {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }
        .reasoning-item.buy {
            border-left-color: #28a745;
        }
        .reasoning-item.sell {
            border-left-color: #dc3545;
        }
        .reasoning-item.neutral {
            border-left-color: #ffc107;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            font-size: 12px;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .buy-signal {
            background-color: #d4edda;
            color: #155724;
        }
        .sell-signal {
            background-color: #f8d7da;
            color: #721c24;
        }
        .neutral-signal {
            background-color: #fff3cd;
            color: #856404;
        }
        .status {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status.loading {
            background: #cce5ff;
            color: #004085;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        .datetime-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .datetime-label {
            font-weight: bold;
            color: #007bff;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Deriv Trading Analyzer - Binary Options Signal Analysis</h1>
        
        <div class="controls">
            <div class="control-row">
                <div class="control-group">
                    <label for="asset">Asset:</label>
                    <select id="asset">
                        <option value="R_50">Volatility 50 Index</option>
                        <option value="R_75">Volatility 75 Index</option>
                        <option value="R_100">Volatility 100 Index</option>
                        <option value="RDBEAR">Bear Market Index</option>
                        <option value="RDBULL">Bull Market Index</option>
                        <option value="frxEURUSD">EUR/USD</option>
                        <option value="frxGBPUSD">GBP/USD</option>
                        <option value="frxUSDJPY">USD/JPY</option>
                        <option value="frxAUDUSD">AUD/USD</option>
                        <option value="frxUSDCHF">USD/CHF</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label for="timeframe1">Timeframe 1:</label>
                    <select id="timeframe1">
                        <option value="60">1 นาที</option>
                        <option value="120">2 นาที</option>
                        <option value="300">5 นาที</option>
                        <option value="900">15 นาที</option>
                        <option value="1800" selected>30 นาที</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label for="timeframe2">Timeframe 2:</label>
                    <select id="timeframe2">
                        <option value="900">15 นาที</option>
                        <option value="1800">30 นาที</option>
                        <option value="3600" selected>1 ชั่วโมง</option>
                    </select>
                </div>
            </div>
            
            <div class="control-row">
                <div class="datetime-group">
                    <div class="datetime-label">📅 ช่วงเวลาที่ 1 (Timeframe 1)</div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div class="control-group">
                            <label for="startDate1">เริ่มต้น:</label>
                            <input type="datetime-local" id="startDate1">
                        </div>
                        <div class="control-group">
                            <label for="endDate1">สิ้นสุด:</label>
                            <input type="datetime-local" id="endDate1">
                        </div>
                    </div>
                </div>
                
                <div class="datetime-group">
                    <div class="datetime-label">📅 ช่วงเวลาที่ 2 (Timeframe 2)</div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div class="control-group">
                            <label for="startDate2">เริ่มต้น:</label>
                            <input type="datetime-local" id="startDate2">
                        </div>
                        <div class="control-group">
                            <label for="endDate2">สิ้นสุด:</label>
                            <input type="datetime-local" id="endDate2">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="control-row">
                <button id="loadData">📊 วิเคราะห์</button>
                <button id="refreshData">🔄 รีเฟรช</button>
                <button id="setDefaultDates">📆 กำหนดวันที่เริ่มต้น</button>
            </div>
        </div>

        <div id="status"></div>

        <div class="charts-container">
            <div class="chart-section">
                <div class="chart-header" id="chart1-header">Chart 1 - 30 นาที</div>
                <div class="chart-container" id="chart1"></div>
            </div>
            <div class="chart-section">
                <div class="chart-header" id="chart2-header">Chart 2 - 1 ชั่วโมง</div>
                <div class="chart-container" id="chart2"></div>
            </div>
        </div>

        <div class="indicators-container">
            <div class="indicator-chart">
                <div class="chart-header">Stochastic Oscillator (%K=Blue, %D=Orange)</div>
                <div class="indicator-chart-container" id="stochastic-chart"></div>
            </div>
            <div class="indicator-chart">
                <div class="chart-header">CCI (Commodity Channel Index)</div>
                <div class="indicator-chart-container" id="cci-chart"></div>
            </div>
        </div>

        <div class="analysis-section">
            <div class="analysis-table">
                <div class="analysis-header">📈 Binary Options Trading Signals</div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table id="signalsTable">
                        <thead>
                            <tr>
                                <th>เวลา</th>
                                <th>ราคา</th>
                                <th>สัญญาณ</th>
                                <th>%K</th>
                                <th>%D</th>
                                <th>CCI</th>
                                <th>แนะนำ</th>
                                <th>เชื่อมั่น</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #666;">กดปุ่ม "วิเคราะห์" เพื่อดูสัญญาณการเทรด</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="analysis-reasoning">
                <div class="reasoning-header">🧠 เหตุผลการวิเคราะห์</div>
                <div class="reasoning-content" id="reasoningContent">
                    <p style="text-align: center; color: #666;">รอการวิเคราะห์...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        class DerivTradingAnalyzer {
            constructor() {
                this.wsUrl = 'wss://ws.binaryws.com/websockets/v3?app_id=1089';
                this.ws = null;
                this.requestId = 1;
                this.isConnected = false;
                this.pendingRequests = new Map();
                
                this.chart1 = null;
                this.chart2 = null;
                this.stochasticChart = null;
                this.cciChart = null;
                
                this.candlestickSeries1 = null;
                this.candlestickSeries2 = null;
                this.stochasticKSeries = null;
                this.stochasticDSeries = null;
                this.cciSeries = null;
                
                this.data1 = [];
                this.data2 = [];
                
                this.initializeCharts();
                this.initializeEventListeners();
                this.setDefaultDates();
            }

            setDefaultDates() {
                const now = new Date();
                const threeDaysAgo = new Date(now.getTime() - (3 * 24 * 60 * 60 * 1000));
                const sevenDaysAgo = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000));
                
                // Format dates for datetime-local input
                const formatDateTime = (date) => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                };

                // Set default dates for period 1 (last 3 days)
                document.getElementById('startDate1').value = formatDateTime(threeDaysAgo);
                document.getElementById('endDate1').value = formatDateTime(now);
                
                // Set default dates for period 2 (last 7 days) 
                document.getElementById('startDate2').value = formatDateTime(sevenDaysAgo);
                document.getElementById('endDate2').value = formatDateTime(now);
            }

            initializeCharts() {
                // Main charts
                this.chart1 = LightweightCharts.createChart(document.getElementById('chart1'), {
                    width: document.getElementById('chart1').clientWidth,
                    height: 400,
                    layout: { backgroundColor: '#ffffff', textColor: '#333' },
                    grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
                    crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                    timeScale: { timeVisible: true, secondsVisible: false }
                });

                this.chart2 = LightweightCharts.createChart(document.getElementById('chart2'), {
                    width: document.getElementById('chart2').clientWidth,
                    height: 400,
                    layout: { backgroundColor: '#ffffff', textColor: '#333' },
                    grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
                    crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                    timeScale: { timeVisible: true, secondsVisible: false }
                });

                // Stochastic chart
                this.stochasticChart = LightweightCharts.createChart(document.getElementById('stochastic-chart'), {
                    width: document.getElementById('stochastic-chart').clientWidth,
                    height: 200,
                    layout: { backgroundColor: '#ffffff', textColor: '#333' },
                    grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
                    timeScale: { timeVisible: true, secondsVisible: false },
                    priceScale: { scaleMargins: { top: 0.1, bottom: 0.1 } }
                });

                // CCI chart
                this.cciChart = LightweightCharts.createChart(document.getElementById('cci-chart'), {
                    width: document.getElementById('cci-chart').clientWidth,
                    height: 200,
                    layout: { backgroundColor: '#ffffff', textColor: '#333' },
                    grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
                    timeScale: { timeVisible: true, secondsVisible: false }
                });

                // Add series
                this.candlestickSeries1 = this.chart1.addCandlestickSeries({
                    upColor: '#26a69a',
                    downColor: '#ef5350',
                    borderVisible: false,
                    wickUpColor: '#26a69a',
                    wickDownColor: '#ef5350'
                });

                this.candlestickSeries2 = this.chart2.addCandlestickSeries({
                    upColor: '#26a69a',
                    downColor: '#ef5350',
                    borderVisible: false,
                    wickUpColor: '#26a69a',
                    wickDownColor: '#ef5350'
                });

                this.stochasticKSeries = this.stochasticChart.addLineSeries({
                    color: '#2196F3',
                    lineWidth: 2,
                    title: '%K'
                });

                this.stochasticDSeries = this.stochasticChart.addLineSeries({
                    color: '#FF9800',
                    lineWidth: 2,
                    title: '%D'
                });

                this.cciSeries = this.cciChart.addLineSeries({
                    color: '#9C27B0',
                    lineWidth: 2,
                    title: 'CCI'
                });

                // Add reference lines
                const currentTime = Math.floor(Date.now() / 1000);
                const pastTime = currentTime - (7 * 24 * 60 * 60);

                this.stochasticChart.addLineSeries({
                    color: '#ff0000',
                    lineWidth: 1,
                    lineStyle: LightweightCharts.LineStyle.Dashed
                }).setData([{ time: pastTime, value: 80 }, { time: currentTime, value: 80 }]);

                this.stochasticChart.addLineSeries({
                    color: '#ff0000',
                    lineWidth: 1,
                    lineStyle: LightweightCharts.LineStyle.Dashed
                }).setData([{ time: pastTime, value: 20 }, { time: currentTime, value: 20 }]);

                this.cciChart.addLineSeries({
                    color: '#ff0000',
                    lineWidth: 1,
                    lineStyle: LightweightCharts.LineStyle.Dashed
                }).setData([{ time: pastTime, value: 100 }, { time: currentTime, value: 100 }]);

                this.cciChart.addLineSeries({
                    color: '#ff0000',
                    lineWidth: 1,
                    lineStyle: LightweightCharts.LineStyle.Dashed
                }).setData([{ time: pastTime, value: -100 }, { time: currentTime, value: -100 }]);

                this.cciChart.addLineSeries({
                    color: '#00ff00',
                    lineWidth: 1,
                    lineStyle: LightweightCharts.LineStyle.Dashed
                }).setData([{ time: pastTime, value: 0 }, { time: currentTime, value: 0 }]);
            }

            initializeEventListeners() {
                document.getElementById('loadData').addEventListener('click', () => this.loadData());
                document.getElementById('refreshData').addEventListener('click', () => this.refreshData());
                document.getElementById('setDefaultDates').addEventListener('click', () => this.setDefaultDates());

                // Handle window resize
                window.addEventListener('resize', () => {
                    if (this.chart1) this.chart1.resize(document.getElementById('chart1').clientWidth, 400);
                    if (this.chart2) this.chart2.resize(document.getElementById('chart2').clientWidth, 400);
                    if (this.stochasticChart) this.stochasticChart.resize(document.getElementById('stochastic-chart').clientWidth, 200);
                    if (this.cciChart) this.cciChart.resize(document.getElementById('cci-chart').clientWidth, 200);
                });
            }

            showStatus(message, type = 'loading') {
                const statusDiv = document.getElementById('status');
                statusDiv.innerHTML = `<div class="status ${type}">${message}</div>`;
            }

            async connectWebSocket() {
                return new Promise((resolve, reject) => {
                    if (this.isConnected) {
                        resolve();
                        return;
                    }

                    this.ws = new WebSocket(this.wsUrl);
                    
                    this.ws.onopen = () => {
                        this.isConnected = true;
                        resolve();
                    };

                    this.ws.onmessage = (event) => {
                        const response = JSON.parse(event.data);
                        this.handleWebSocketMessage(response);
                    };

                    this.ws.onclose = () => {
                        this.isConnected = false;
                    };

                    this.ws.onerror = (error) => {
                        this.isConnected = false;
                        reject(error);
                    };

                    setTimeout(() => {
                        if (!this.isConnected) {
                            reject(new Error('WebSocket connection timeout'));
                        }
                    }, 5000);
                });
            }

            handleWebSocketMessage(response) {
                if (response.req_id && this.pendingRequests.has(response.req_id)) {
                    const resolver = this.pendingRequests.get(response.req_id);
                    this.pendingRequests.delete(response.req_id);
                    resolver(response);
                }
            }

            async sendRequest(request) {
                const reqId = this.requestId++;
                request.req_id = reqId;

                return new Promise((resolve, reject) => {
                    this.pendingRequests.set(reqId, resolve);
                    this.ws.send(JSON.stringify(request));

                    setTimeout(() => {
                        if (this.pendingRequests.has(reqId)) {
                            this.pendingRequests.delete(reqId);
                            reject(new Error('Request timeout'));
                        }
                    }, 15000);
                });
            }

            async getCandlesByDateRange(symbol, granularity, startTime, endTime) {
                const request = {
                    ticks_history: symbol,
                    adjust_start_time: 1,
                    start: startTime,
                    end: endTime,
                    granularity: granularity,
                    style: 'candles'
                };

                const response = await this.sendRequest(request);
                
                if (response.error) {
                    throw new Error(response.error.message);
                }

                return response.candles || [];
            }

            calculateStochastic(data, period = 14) {
                const stochastic = [];
                
                for (let i = period - 1; i < data.length; i++) {
                    const slice = data.slice(i - period + 1, i + 1);
                    const highest = Math.max(...slice.map(d => d.high));
                    const lowest = Math.min(...slice.map(d => d.low));
                    const current = data[i].close;
                    
                    const k = highest === lowest ? 50 : ((current - lowest) / (highest - lowest)) * 100;
                    stochastic.push({
                        time: data[i].time,
                        k: k,
                        d: 0
                    });
                }

                // Calculate %D as 3-period SMA of %K
                for (let i = 2; i < stochastic.length; i++) {
                    const slice = stochastic.slice(i - 2, i + 1);
                    const d = slice.reduce((sum, item) => sum + item.k, 0) / 3;
                    stochastic[i].d = d;
                }

                return stochastic;
            }

            calculateCCI(data, period = 20) {
                const cci = [];
                
                for (let i = period - 1; i < data.length; i++) {
                    const slice = data.slice(i - period + 1, i + 1);
                    
                    const typicalPrices = slice.map(d => (d.high + d.low + d.close) / 3);
                    const sma = typicalPrices.reduce((sum, tp) => sum + tp, 0) / period;
                    const meanDeviation = typicalPrices.reduce((sum, tp) => sum + Math.abs(tp - sma), 0) / period;
                    
                    const currentTypicalPrice = (data[i].high + data[i].low + data[i].close) / 3;
                    const cciValue = meanDeviation === 0 ? 0 : (currentTypicalPrice - sma) / (0.015 * meanDeviation);
                    
                    cci.push({
                        time: data[i].time,
                        value: cciValue
                    });
                }

                return cci;
            }

            analyzeSignals(data, stochastic, cci) {
                const signals = [];
                const reasonings = [];
                let signalCount = { buy: 0, sell: 0, neutral: 0 };
                
                // วิเคราะห์ทุกแท่ง แต่แสดงผล 50 แท่งล่าสุด
                const startIndex = Math.max(0, data.length - 50);
                
                for (let i = startIndex; i < data.length; i++) {
                    if (i < stochastic.length && i < cci.length && i > 0) {
                        const candle = data[i];
                        const prevCandle = data[i - 1];
                        const stoch = stochastic[i];
                        const prevStoch = i > 0 ? stochastic[i - 1] : stoch;
                        const cciValue = cci[i];
                        const prevCci = i > 0 ? cci[i - 1] : cciValue;
                        
                        let signal = 'NEUTRAL';
                        let confidence = 50; // เริ่มต้นที่ 50%
                        let recommendation = 'รอสัญญาณ';
                        let reasoning = [];
                        
                        const stochK = stoch.k || 50;
                        const stochD = stoch.d || 50;
                        const prevStochK = prevStoch.k || 50;
                        const prevStochD = prevStoch.d || 50;
                        const cciVal = cciValue.value || 0;
                        const prevCciVal = prevCci.value || 0;
                        
                        // === BUY SIGNALS ===
                        
                        // 1. Super Strong BUY
                        if (stochK < 15 && stochD < 15 && cciVal < -150) {
                            signal = 'BUY';
                            confidence = 95;
                            recommendation = 'CALL (ซื้อขึ้น) - แรงมาก';
                            reasoning.push(`🔥🔥 สัญญาณซื้อแรงมาก: Stochastic %K=${stochK.toFixed(1)} %D=${stochD.toFixed(1)} อยู่ในโซน Extreme Oversold (<15) และ CCI=${cciVal.toFixed(1)} < -150`);
                        }
                        // 2. Strong BUY
                        else if (stochK < 25 && stochD < 25 && cciVal < -80) {
                            signal = 'BUY';
                            confidence = 85;
                            recommendation = 'CALL (ซื้อขึ้น)';
                            reasoning.push(`🔥 สัญญาณซื้อแรง: Stochastic อยู่ในโซน Oversold และ CCI=${cciVal.toFixed(1)} < -80`);
                        }
                        // 3. Bullish Crossover
                        else if (stochK > stochD && prevStochK <= prevStochD && stochK < 50) {
                            signal = 'BUY';
                            confidence = 75;
                            recommendation = 'CALL (ซื้อขึ้น)';
                            reasoning.push(`📈 Bullish Crossover: %K ข้าม %D ขึ้นไป (${prevStochK.toFixed(1)}→${stochK.toFixed(1)}) ใน Mid-range`);
                        }
                        // 4. CCI Bullish Divergence
                        else if (cciVal > prevCciVal && cciVal < -30 && candle.close > prevCandle.close) {
                            signal = 'BUY';
                            confidence = 70;
                            recommendation = 'CALL (ซื้อขึ้น)';
                            reasoning.push(`📊 CCI กำลังฟื้นตัว: ${prevCciVal.toFixed(1)}→${cciVal.toFixed(1)} พร้อมราคาขึ้น`);
                        }
                        // 5. Stochastic Rising in Oversold
                        else if (stochK > prevStochK && stochK < 35 && stochK > stochD) {
                            signal = 'BUY';
                            confidence = 65;
                            recommendation = 'CALL (ซื้อขึ้น) - ปานกลาง';
                            reasoning.push(`⚡ Stochastic กำลังขึ้น: %K=${stochK.toFixed(1)} เพิ่มขึ้นจาก ${prevStochK.toFixed(1)} ในโซนต่ำ`);
                        }
                        // 6. Double Bottom Pattern (CCI)
                        else if (i >= 5) {
                            const recent5Cci = cci.slice(i-4, i+1).map(c => c.value);
                            const minCci = Math.min(...recent5Cci);
                            if (cciVal === minCci && cciVal < -50 && cciVal > prevCciVal) {
                                signal = 'BUY';
                                confidence = 68;
                                recommendation = 'CALL (ซื้อขึ้น)';
                                reasoning.push(`🎯 CCI Double Bottom: ค่าต่ำสุดใน 5 แท่ง (${cciVal.toFixed(1)}) และเริ่มฟื้นตัว`);
                            }
                        }
                        // 7. Mild Oversold Recovery
                        else if (stochK < 40 && stochK > prevStochK && cciVal > -100 && cciVal < 0) {
                            signal = 'BUY';
                            confidence = 60;
                            recommendation = 'CALL (ซื้อขึ้น) - ระวัง';
                            reasoning.push(`💡 การฟื้นตัวเบาๆ: Stochastic เริ่มขึ้น CCI=${cciVal.toFixed(1)} ยังในแนวลบ`);
                        }
                        
                        // === SELL SIGNALS ===
                        
                        // 1. Super Strong SELL
                        else if (stochK > 85 && stochD > 85 && cciVal > 150) {
                            signal = 'SELL';
                            confidence = 95;
                            recommendation = 'PUT (ซื้อลง) - แรงมาก';
                            reasoning.push(`🔥🔥 สัญญาณขายแรงมาก: Stochastic %K=${stochK.toFixed(1)} %D=${stochD.toFixed(1)} อยู่ในโซน Extreme Overbought (>85) และ CCI=${cciVal.toFixed(1)} > 150`);
                        }
                        // 2. Strong SELL
                        else if (stochK > 75 && stochD > 75 && cciVal > 80) {
                            signal = 'SELL';
                            confidence = 85;
                            recommendation = 'PUT (ซื้อลง)';
                            reasoning.push(`🔥 สัญญาณขายแรง: Stochastic อยู่ในโซน Overbought และ CCI=${cciVal.toFixed(1)} > 80`);
                        }
                        // 3. Bearish Crossover
                        else if (stochK < stochD && prevStochK >= prevStochD && stochK > 50) {
                            signal = 'SELL';
                            confidence = 75;
                            recommendation = 'PUT (ซื้อลง)';
                            reasoning.push(`📉 Bearish Crossover: %K ตัดลง %D (${prevStochK.toFixed(1)}→${stochK.toFixed(1)}) ใน Mid-High range`);
                        }
                        // 4. CCI Bearish Divergence
                        else if (cciVal < prevCciVal && cciVal > 30 && candle.close < prevCandle.close) {
                            signal = 'SELL';
                            confidence = 70;
                            recommendation = 'PUT (ซื้อลง)';
                            reasoning.push(`📊 CCI กำลังปรับลง: ${prevCciVal.toFixed(1)}→${cciVal.toFixed(1)} พร้อมราคาลง`);
                        }
                        // 5. Stochastic Falling in Overbought
                        else if (stochK < prevStochK && stochK > 65 && stochK < stochD) {
                            signal = 'SELL';
                            confidence = 65;
                            recommendation = 'PUT (ซื้อลง) - ปานกลาง';
                            reasoning.push(`⚡ Stochastic กำลังลง: %K=${stochK.toFixed(1)} ลดลงจาก ${prevStochK.toFixed(1)} ในโซนสูง`);
                        }
                        // 6. Double Top Pattern (CCI)
                        else if (i >= 5) {
                            const recent5Cci = cci.slice(i-4, i+1).map(c => c.value);
                            const maxCci = Math.max(...recent5Cci);
                            if (cciVal === maxCci && cciVal > 50 && cciVal < prevCciVal) {
                                signal = 'SELL';
                                confidence = 68;
                                recommendation = 'PUT (ซื้อลง)';
                                reasoning.push(`🎯 CCI Double Top: ค่าสูงสุดใน 5 แท่ง (${cciVal.toFixed(1)}) และเริ่มปรับลง`);
                            }
                        }
                        // 7. Mild Overbought Correction
                        else if (stochK > 60 && stochK < prevStochK && cciVal < 100 && cciVal > 0) {
                            signal = 'SELL';
                            confidence = 60;
                            recommendation = 'PUT (ซื้อลง) - ระวัง';
                            reasoning.push(`💡 การปรับตัวเบาๆ: Stochastic เริ่มลง CCI=${cciVal.toFixed(1)} ยังในแนวบวก`);
                        }
                        
                        // === ADDITIONAL PATTERNS ===
                        
                        // 8. Momentum Shift BUY
                        else if (stochK > 30 && stochK < 70 && (stochK - prevStochK) > 5 && cciVal > prevCciVal) {
                            signal = 'BUY';
                            confidence = 62;
                            recommendation = 'CALL (ซื้อขึ้น) - Momentum';
                            reasoning.push(`🚀 Momentum เปลี่ยน: %K เพิ่มขึ้น ${(stochK - prevStochK).toFixed(1)} จุด CCI สนับสนุน`);
                        }
                        // 9. Momentum Shift SELL
                        else if (stochK > 30 && stochK < 70 && (prevStochK - stochK) > 5 && cciVal < prevCciVal) {
                            signal = 'SELL';
                            confidence = 62;
                            recommendation = 'PUT (ซื้อลง) - Momentum';
                            reasoning.push(`💥 Momentum เปลี่ยน: %K ลดลง ${(prevStochK - stochK).toFixed(1)} จุด CCI สนับสนุน`);
                        }
                        // 10. CCI Zero Line Cross BUY
                        else if (cciVal > 0 && prevCciVal <= 0 && stochK > 40) {
                            signal = 'BUY';
                            confidence = 58;
                            recommendation = 'CALL (ซื้อขึ้น) - Breakout';
                            reasoning.push(`⬆️ CCI ข้าม Zero Line: ${prevCciVal.toFixed(1)}→${cciVal.toFixed(1)} สัญญาณ Bullish`);
                        }
                        // 11. CCI Zero Line Cross SELL
                        else if (cciVal < 0 && prevCciVal >= 0 && stochK < 60) {
                            signal = 'SELL';
                            confidence = 58;
                            recommendation = 'PUT (ซื้อลง) - Breakdown';
                            reasoning.push(`⬇️ CCI ข้าม Zero Line: ${prevCciVal.toFixed(1)}→${cciVal.toFixed(1)} สัญญาณ Bearish`);
                        }
                        
                        // Strong SELL signals
                        else if (stochK > 80 && stochD > 80 && cciVal > 100) {
                            signal = 'SELL';
                            confidence = 90;
                            recommendation = 'PUT (ซื้อลง)';
                            reasoning.push(`🔥 สัญญาณขายแรง: Stochastic %K=${stochK.toFixed(1)} และ %D=${stochD.toFixed(1)} อยู่ในโซน Overbought (>80) และ CCI=${cciVal.toFixed(1)} อยู่สูงกว่า 100 แสดงว่าราคาถูกซื้อมากเกินไป`);
                        }
                        // Medium SELL signals
                        else if (stochK < prevStochK && stochK > 70 && cciVal > 50 && cciVal < prevCciVal) {
                            signal = 'SELL';
                            confidence = 75;
                            recommendation = 'PUT (ซื้อลง)';
                            reasoning.push(`📉 สัญญาณขายปานกลาง: Stochastic %K กำลังลดลง (${prevStochK.toFixed(1)}→${stochK.toFixed(1)}) ในโซนสูง และ CCI กำลังปรับลง (${prevCciVal.toFixed(1)}→${cciVal.toFixed(1)})`);
                        }
                        // Weak SELL signals
                        else if (stochK < stochD && stochK > 60 && cciVal > 0) {
                            signal = 'SELL';
                            confidence = 60;
                            recommendation = 'PUT (ซื้อลง) - ระวัง';
                            reasoning.push(`⚠️ สัญญาณขายอ่อน: Stochastic %K (${stochK.toFixed(1)}) ตัดลง %D (${stochD.toFixed(1)}) และ CCI=${cciVal.toFixed(1)} อยู่ในแนวบวก`);
                        // 12. Stochastic Mid-line Cross BUY
                        else if (stochK > 50 && prevStochK <= 50 && cciVal > -50) {
                            signal = 'BUY';
                            confidence = 55;
                            recommendation = 'CALL (ซื้อขึ้น) - Weak';
                            reasoning.push(`📊 Stochastic ข้าม Mid-line: %K ข้าม 50 ขึ้นไป CCI=${cciVal.toFixed(1)}`);
                        }
                        // 13. Stochastic Mid-line Cross SELL
                        else if (stochK < 50 && prevStochK >= 50 && cciVal < 50) {
                            signal = 'SELL';
                            confidence = 55;
                            recommendation = 'PUT (ซื้อลง) - Weak';
                            reasoning.push(`📊 Stochastic ข้าม Mid-line: %K ข้าม 50 ลงไป CCI=${cciVal.toFixed(1)}`);
                        }
                        
                        // === NEUTRAL CONDITIONS WITH DETAILED REASONING ===
                        else {
                            // วิเคราะห์เหตุผลที่ไม่มีสัญญาณ
                            if (stochK >= 30 && stochK <= 70 && Math.abs(cciVal) <= 50) {
                                reasoning.push(`😐 ไม่มีสัญญาณชัดเจน: Stochastic %K=${stochK.toFixed(1)} อยู่ในช่วงกลาง (30-70) และ CCI=${cciVal.toFixed(1)} อยู่ในช่วงปกติ (-50 ถึง 50)`);
                                reasoning.push(`📊 ตลาดอยู่ในภาวะ Sideways/Consolidation - รอสัญญาณที่ชัดเจนกว่า`);
                            }
                            
                            if (Math.abs(stochK - stochD) < 3) {
                                reasoning.push(`🔀 Stochastic %K=${stochK.toFixed(1)} และ %D=${stochD.toFixed(1)} ใกล้เคียงกัน - ไม่มี Momentum ชัดเจน`);
                            }
                            
                            if (Math.abs(cciVal) < 25) {
                                reasoning.push(`⚖️ CCI=${cciVal.toFixed(1)} ใกล้ศูนย์ - แรงซื้อขายสมดุล`);
                            }
                            
                            if (stochK > 20 && stochK < 80 && ((stochK > prevStochK && cciVal < prevCciVal) || (stochK < prevStochK && cciVal > prevCciVal))) {
                                reasoning.push(`🤔 สัญญาณขัดแย้งกัน: Stochastic และ CCI เคลื่อนไหวในทิศทางตรงข้าม`);
                            }
                            
                            // เพิ่มการวิเคราะห์เทรนด์
                            if (i >= 5) {
                                const recent5Closes = data.slice(i-4, i+1).map(d => d.close);
                                const priceChange = recent5Closes[4] - recent5Closes[0];
                                const priceChangePercent = (priceChange / recent5Closes[0]) * 100;
                                
                                if (Math.abs(priceChangePercent) < 0.1) {
                                    reasoning.push(`📈 ราคา 5 แท่งล่าสุดเคลื่อนไหวเพียง ${priceChangePercent.toFixed(3)}% - ตลาด Flat`);
                                } else if (priceChangePercent > 0 && stochK < 60) {
                                    reasoning.push(`🚀 ราคาขึ้น ${priceChangePercent.toFixed(2)}% แต่ Stochastic ยังไม่ Overbought - อาจมีโอกาสขึ้นต่อ`);
                                } else if (priceChangePercent < 0 && stochK > 40) {
                                    reasoning.push(`💥 ราคาลง ${priceChangePercent.toFixed(2)}% แต่ Stochastic ยังไม่ Oversold - อาจมีโอกาสลงต่อ`);
                                }
                        
                        // === CONFIDENCE ADJUSTMENTS ===
                        if (signal !== 'NEUTRAL') {
                            // Volume analysis (using high-low range as proxy)
                            const currentRange = candle.high - candle.low;
                            const avgRange = data.slice(Math.max(0, i-10), i).reduce((sum, c) => sum + (c.high - c.low), 0) / Math.min(10, i);
                            
                            if (currentRange > avgRange * 1.8) {
                                confidence += 8;
                                reasoning.push(`📊 Volume สูง: ช่วงราคา ${(currentRange/avgRange).toFixed(1)}x ปกติ เพิ่มความน่าเชื่อถือ`);
                            } else if (currentRange < avgRange * 0.5) {
                                confidence -= 5;
                                reasoning.push(`📉 Volume ต่ำ: ช่วงราคาแคบกว่าปกติ ลดความน่าเชื่อถือ`);
                            }
                            
                            // Trend confirmation
                            if (i >= 3) {
                                const recent3Closes = data.slice(i-2, i+1).map(d => d.close);
                                const isUptrend = recent3Closes[2] > recent3Closes[1] && recent3Closes[1] > recent3Closes[0];
                                const isDowntrend = recent3Closes[2] < recent3Closes[1] && recent3Closes[1] < recent3Closes[0];
                                
                                if (signal === 'BUY' && isUptrend) {
                                    confidence += 12;
                                    reasoning.push(`🎯 Uptrend ช่วย: ราคาขึ้น 3 แท่งติดกัน สนับสนุนสัญญาณซื้อ`);
                                } else if (signal === 'SELL' && isDowntrend) {
                                    confidence += 12;
                                    reasoning.push(`🎯 Downtrend ช่วย: ราคาลง 3 แท่งติดกัน สนับสนุนสัญญาณขาย`);
                                } else if ((signal === 'BUY' && isDowntrend) || (signal === 'SELL' && isUptrend)) {
                                    confidence -= 8;
                                    reasoning.push(`⚠️ ขัดกับเทรนด์: แนวโน้มราคาไม่สอดคล้องกับสัญญาณ`);
                                }
                            }
                            
                            // Multi-timeframe confirmation
                            if (this.data2.length > 0) {
                                const nearestData2 = this.data2.find(d => Math.abs(d.time - candle.time) < 7200); // 2 hours tolerance
                                if (nearestData2) {
                                    const data2Context = this.data2.filter(d => d.time <= nearestData2.time).slice(-50);
                                    if (data2Context.length >= 20) {
                                        const data2Stoch = this.calculateStochastic(data2Context);
                                        const data2Cci = this.calculateCCI(data2Context);
                                        
                                        if (data2Stoch.length > 0 && data2Cci.length > 0) {
                                            const tf2Stoch = data2Stoch[data2Stoch.length - 1];
                                            const tf2Cci = data2Cci[data2Cci.length - 1];
                                            
                                            if (signal === 'BUY' && tf2Stoch.k < 60 && tf2Cci.value < 50) {
                                                confidence += 10;
                                                reasoning.push(`✅ Higher TF OK: Stoch=${tf2Stoch.k.toFixed(1)}, CCI=${tf2Cci.value.toFixed(1)}`);
                                            } else if (signal === 'SELL' && tf2Stoch.k > 40 && tf2Cci.value > -50) {
                                                confidence += 10;
                                                reasoning.push(`✅ Higher TF OK: Stoch=${tf2Stoch.k.toFixed(1)}, CCI=${tf2Cci.value.toFixed(1)}`);
                                            } else {
                                                confidence -= 8;
                                                reasoning.push(`❌ Higher TF ไม่ช่วย: Stoch=${tf2Stoch.k.toFixed(1)}, CCI=${tf2Cci.value.toFixed(1)}`);
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // RSI-like momentum check
                            if (i >= 7) {
                                const recentPrices = data.slice(i-6, i+1).map(d => d.close);
                                const priceVelocity = (recentPrices[6] - recentPrices[0]) / 6;
                                const priceAcceleration = (recentPrices[6] - recentPrices[3]) - (recentPrices[3] - recentPrices[0]);
                                
                                if (signal === 'BUY' && priceVelocity > 0 && priceAcceleration > 0) {
                                    confidence += 7;
                                    reasoning.push(`🚀 Price Momentum+: ความเร็วและความเร่งราคาสนับสนุน`);
                                } else if (signal === 'SELL' && priceVelocity < 0 && priceAcceleration < 0) {
                                    confidence += 7;
                                    reasoning.push(`💥 Price Momentum-: ความเร็วและความเร่งราคาสนับสนุน`);
                                }
                            }
                            
                            confidence = Math.max(35, Math.min(98, confidence));
                        }
                        
                        // Count signals
                        if (signal === 'BUY') signalCount.buy++;
                        else if (signal === 'SELL') signalCount.sell++;
                        else signalCount.neutral++;
                        
                        signals.push({
                            time: new Date(candle.time * 1000).toLocaleString('th-TH'),
                            price: candle.close.toFixed(5),
                            signal: signal,
                            stochK: stochK.toFixed(2),
                            stochD: stochD.toFixed(2),
                            cci: cciVal.toFixed(2),
                            recommendation: recommendation,
                            confidence: signal === 'NEUTRAL' ? '-' : confidence + '%',
                            reasoning: reasoning
                        });

                        // Add to reasoning list
                        if (reasoning.length > 0) {
                            reasonings.push({
                                time: new Date(candle.time * 1000).toLocaleString('th-TH'),
                                signal: signal,
                                reasoning: reasoning
                            });
                        }

                        // Add markers to chart for signals with confidence >= 60%
                        if (signal !== 'NEUTRAL' && confidence >= 60) {
                            const marker = {
                                time: candle.time,
                                position: signal === 'BUY' ? 'belowBar' : 'aboveBar',
                                color: signal === 'BUY' ? '#2196F3' : '#f23645',
                                shape: signal === 'BUY' ? 'arrowUp' : 'arrowDown',
                                text: `${signal} ${confidence}%`,
                                size: confidence >= 80 ? 2 : 1
                            };
                            
                            if (this.candlestickSeries1) {
                                const markers = this.candlestickSeries1.markers() || [];
                                markers.push(marker);
                                this.candlestickSeries1.setMarkers(markers);
                            }
                        }
                    }
                }
                
                return { signals, reasonings, signalCount };
            }ัญญาณชัดเจน: Stochastic %K=${stochK.toFixed(1)}, %D=${stochD.toFixed(1)} อยู่ในช่วงกลาง (20-80) และ CCI=${cciVal.toFixed(1)} อยู่ในช่วงปกติ (-100 ถึง 100)`);
                            
                            // Additional analysis for neutral signals
                            if (Math.abs(stochK - stochD) < 5) {
                                reasoning.push(`📊 Stochastic %K และ %D ใกล้เคียงกัน แสดงถึงภาวะ Consolidation`);
                            }
                            if (Math.abs(cciVal) < 50) {
                                reasoning.push(`📊 CCI อยู่ใกล้ศูนย์ แสดงถึงแรงซื้อขายที่สมดุล`);
                            }
                            if (candle.close > prevCandle.close && stochK < 50) {
                                reasoning.push(`🤔 ราคาเพิ่มขึ้นแต่ Stochastic ยังต่ำ อาจเป็นจุดเริ่มต้นของแนวโน้มขึ้น`);
                            }
                            if (candle.close < prevCandle.close && stochK > 50) {
                                reasoning.push(`🤔 ราคาลดลงแต่ Stochastic ยังสูง อาจเป็นจุดเริ่มต้นของแนวโน้มลง`);
                            }
                        }
                        
                        // Additional confluence factors
                        if (signal !== 'NEUTRAL') {
                            // Volume analysis (using high-low range as proxy)
                            const currentRange = candle.high - candle.low;
                            const avgRange = data.slice(Math.max(0, i-10), i).reduce((sum, c) => sum + (c.high - c.low), 0) / Math.min(10, i);
                            
                            if (currentRange > avgRange * 1.5) {
                                confidence += 5;
                                reasoning.push(`📈 ช่วงราคา (High-Low) สูงกว่าค่าเฉลี่ย เพิ่มความน่าเชื่อถือ`);
                            }
                            
                            // Trend confirmation
                            if (i >= 5) {
                                const recentCloses = data.slice(i-5, i+1).map(d => d.close);
                                const isUptrend = recentCloses[recentCloses.length-1] > recentCloses[0];
                                const isDowntrend = recentCloses[recentCloses.length-1] < recentCloses[0];
                                
                                if (signal === 'BUY' && isUptrend) {
                                    confidence += 10;
                                    reasoning.push(`🚀 แนวโน้มราคาขึ้น 5 แท่งล่าสุด สนับสนุนสัญญาณซื้อ`);
                                } else if (signal === 'SELL' && isDowntrend) {
                                    confidence += 10;
                                    reasoning.push(`💥 แนวโน้มราคาลง 5 แท่งล่าสุด สนับสนุนสัญญาณขาย`);
                                } else if (signal === 'BUY' && isDowntrend) {
                                    confidence -= 15;
                                    reasoning.push(`⚠️ แนวโน้มราคาลง ขัดแย้งกับสัญญาณซื้อ ลดความเชื่อมั่น`);
                                } else if (signal === 'SELL' && isUptrend) {
                                    confidence -= 15;
                                    reasoning.push(`⚠️ แนวโน้มราคาขึ้น ขัดแย้งกับสัญญาณขาย ลดความเชื่อมั่น`);
                                }
                            }
                            
                            // Multi-timeframe confirmation (if data2 exists)
                            if (this.data2.length > 0) {
                                const nearestData2 = this.data2.find(d => Math.abs(d.time - candle.time) < 3600);
                                if (nearestData2) {
                                    const data2Stoch = this.calculateStochastic(this.data2.filter(d => d.time <= nearestData2.time).slice(-50));
                                    const data2Cci = this.calculateCCI(this.data2.filter(d => d.time <= nearestData2.time).slice(-50));
                                    
                                    if (data2Stoch.length > 0 && data2Cci.length > 0) {
                                        const tf2Stoch = data2Stoch[data2Stoch.length - 1];
                                        const tf2Cci = data2Cci[data2Cci.length - 1];
                                        
                                        if (signal === 'BUY' && tf2Stoch.k < 30 && tf2Cci.value < 0) {
                                            confidence += 15;
                                            reasoning.push(`✅ Timeframe สูงสนับสนุน: Stochastic=${tf2Stoch.k.toFixed(1)}, CCI=${tf2Cci.value.toFixed(1)}`);
                                        } else if (signal === 'SELL' && tf2Stoch.k > 70 && tf2Cci.value > 0) {
                                            confidence += 15;
                                            reasoning.push(`✅ Timeframe สูงสนับสนุน: Stochastic=${tf2Stoch.k.toFixed(1)}, CCI=${tf2Cci.value.toFixed(1)}`);
                                        } else {
                                            confidence -= 10;
                                            reasoning.push(`❌ Timeframe สูงไม่สนับสนุน: Stochastic=${tf2Stoch.k.toFixed(1)}, CCI=${tf2Cci.value.toFixed(1)}`);
                                        }
                                    }
                                }
                            }
                            
                            confidence = Math.max(30, Math.min(95, confidence));
                        }
                        
                        // Count signals
                        if (signal === 'BUY') signalCount.buy++;
                        else if (signal === 'SELL') signalCount.sell++;
                        else signalCount.neutral++;
                        
                        signals.push({
                            time: new Date(candle.time * 1000).toLocaleString('th-TH'),
                            price: candle.close.toFixed(5),
                            signal: signal,
                            stochK: stochK.toFixed(2),
                            stochD: stochD.toFixed(2),
                            cci: cciVal.toFixed(2),
                            recommendation: recommendation,
                            confidence: confidence + '%',
                            reasoning: reasoning
                        });

                        // Add to reasoning list
                        if (reasoning.length > 0) {
                            reasonings.push({
                                time: new Date(candle.time * 1000).toLocaleString('th-TH'),
                                signal: signal,
                                reasoning: reasoning
                            });
                        }

                        // Add markers to chart for strong signals
                        if (signal !== 'NEUTRAL' && confidence >= 70) {
                            const marker = {
                                time: candle.time,
                                position: signal === 'BUY' ? 'belowBar' : 'aboveBar',
                                color: signal === 'BUY' ? '#2196F3' : '#f23645',
                                shape: signal === 'BUY' ? 'arrowUp' : 'arrowDown',
                                text: `${signal} - ${confidence}%`
                            };
                            
                            if (this.candlestickSeries1) {
                                const markers = this.candlestickSeries1.markers() || [];
                                markers.push(marker);
                                this.candlestickSeries1.setMarkers(markers);
                            }
                        }
                    }
                }
                
                return { signals, reasonings, signalCount };
            }

            updateSignalsTable(signals) {
                const tbody = document.querySelector('#signalsTable tbody');
                tbody.innerHTML = '';

                if (signals.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">ไม่พบสัญญาณการเทรด</td></tr>';
                    return;
                }

                signals.reverse().forEach(signal => {
                    const row = document.createElement('tr');
                    let rowClass = 'neutral-signal';
                    
                    if (signal.signal === 'BUY') rowClass = 'buy-signal';
                    else if (signal.signal === 'SELL') rowClass = 'sell-signal';
                    
                    row.className = rowClass;
                    row.innerHTML = `
                        <td>${signal.time}</td>
                        <td>${signal.price}</td>
                        <td><strong>${signal.signal}</strong></td>
                        <td>${signal.stochK}</td>
                        <td>${signal.stochD}</td>
                        <td>${signal.cci}</td>
                        <td><strong>${signal.recommendation}</strong></td>
                        <td>${signal.confidence}</td>
                    `;
                    tbody.appendChild(row);
                });
            }

            updateReasoningContent(reasonings, signalCount) {
                const content = document.getElementById('reasoningContent');
                
                if (reasonings.length === 0) {
                    content.innerHTML = '<p style="text-align: center; color: #666;">ไม่มีการวิเคราะห์</p>';
                    return;
                }

                let html = `
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                        <h4 style="margin: 0 0 10px 0;">📊 สรุปผลการวิเคราะห์</h4>
                        <p><strong>สัญญาณซื้อ:</strong> ${signalCount.buy} ครั้ง | 
                        <strong>สัญญาณขาย:</strong> ${signalCount.sell} ครั้ง | 
                        <strong>ไม่มีสัญญาณ:</strong> ${signalCount.neutral} ครั้ง</p>
                    </div>
                `;

                reasonings.reverse().forEach(item => {
                    let itemClass = 'neutral';
                    if (item.signal === 'BUY') itemClass = 'buy';
                    else if (item.signal === 'SELL') itemClass = 'sell';
                    
                    html += `
                        <div class="reasoning-item ${itemClass}">
                            <h4 style="margin: 0 0 10px 0; color: #333;">
                                ${item.time} - ${item.signal === 'BUY' ? '📈 สัญญาณซื้อ' : item.signal === 'SELL' ? '📉 สัญญาณขาย' : '😐 ไม่มีสัญญาณ'}
                            </h4>
                            <ul style="margin: 0; padding-left: 20px;">
                                ${item.reasoning.map(reason => `<li style="margin-bottom: 5px;">${reason}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                });

                content.innerHTML = html;
            }

            async loadData() {
                try {
                    this.showStatus('🔌 กำลังเชื่อมต่อ Deriv WebSocket...', 'loading');
                    
                    const loadButton = document.getElementById('loadData');
                    loadButton.disabled = true;
                    
                    await this.connectWebSocket();
                    
                    const asset = document.getElementById('asset').value;
                    const timeframe1 = parseInt(document.getElementById('timeframe1').value);
                    const timeframe2 = parseInt(document.getElementById('timeframe2').value);
                    
                    // Get date ranges
                    const startDate1 = new Date(document.getElementById('startDate1').value);
                    const endDate1 = new Date(document.getElementById('endDate1').value);
                    const startDate2 = new Date(document.getElementById('startDate2').value);
                    const endDate2 = new Date(document.getElementById('endDate2').value);
                    
                    if (isNaN(startDate1.getTime()) || isNaN(endDate1.getTime()) || 
                        isNaN(startDate2.getTime()) || isNaN(endDate2.getTime())) {
                        throw new Error('กรุณาเลือกวันที่ให้ครบถ้วน');
                    }
                    
                    const start1 = Math.floor(startDate1.getTime() / 1000);
                    const end1 = Math.floor(endDate1.getTime() / 1000);
                    const start2 = Math.floor(startDate2.getTime() / 1000);
                    const end2 = Math.floor(endDate2.getTime() / 1000);
                    
                    // Update chart headers
                    const tf1Text = document.querySelector('#timeframe1 option:checked').text;
                    const tf2Text = document.querySelector('#timeframe2 option:checked').text;
                    const assetText = document.querySelector('#asset option:checked').text;
                    
                    document.getElementById('chart1-header').textContent = 
                        `${assetText} - ${tf1Text} (${startDate1.toLocaleDateString('th-TH')} - ${endDate1.toLocaleDateString('th-TH')})`;
                    document.getElementById('chart2-header').textContent = 
                        `${assetText} - ${tf2Text} (${startDate2.toLocaleDateString('th-TH')} - ${endDate2.toLocaleDateString('th-TH')})`;
                    
                    this.showStatus('📊 กำลังดึงข้อมูลราคาจาก Deriv...', 'loading');
                    
                    // Get candle data for both periods
                    const [candles1, candles2] = await Promise.all([
                        this.getCandlesByDateRange(asset, timeframe1, start1, end1),
                        this.getCandlesByDateRange(asset, timeframe2, start2, end2)
                    ]);
                    
                    if (!candles1.length || !candles2.length) {
                        throw new Error('ไม่สามารถดึงข้อมูลราคาได้ ลองเปลี่ยนช่วงวันที่');
                    }
                    
                    this.showStatus('📈 กำลังคำนวณ Technical Indicators...', 'loading');
                    
                    // Transform data
                    this.data1 = candles1.map(candle => ({
                        time: candle.epoch,
                        open: parseFloat(candle.open),
                        high: parseFloat(candle.high),
                        low: parseFloat(candle.low),
                        close: parseFloat(candle.close)
                    }));
                    
                    this.data2 = candles2.map(candle => ({
                        time: candle.epoch,
                        open: parseFloat(candle.open),
                        high: parseFloat(candle.high),
                        low: parseFloat(candle.low),
                        close: parseFloat(candle.close)
                    }));
                    
                    // Calculate indicators for timeframe 1
                    const stochastic1 = this.calculateStochastic(this.data1);
                    const cci1 = this.calculateCCI(this.data1);
                    
                    // Clear previous markers
                    if (this.candlestickSeries1) {
                        this.candlestickSeries1.setMarkers([]);
                    }
                    
                    // Update charts
                    this.candlestickSeries1.setData(this.data1);
                    this.candlestickSeries2.setData(this.data2);
                    
                    // Update indicator charts
                    const stochKData = stochastic1.map(s => ({ time: s.time, value: s.k }));
                    const stochDData = stochastic1.map(s => ({ time: s.time, value: s.d }));
                    
                    this.stochasticKSeries.setData(stochKData);
                    this.stochasticDSeries.setData(stochDData);
                    this.cciSeries.setData(cci1);
                    
                    this.showStatus('🎯 กำลังวิเคราะห์สัญญาณการเทรดและเหตุผล...', 'loading');
                    
                    // Analyze signals
                    const analysis = this.analyzeSignals(this.data1, stochastic1, cci1);
                    
                    // Update tables and reasoning
                    this.updateSignalsTable(analysis.signals);
                    this.updateReasoningContent(analysis.reasonings, analysis.signalCount);
                    
                    const tradingSignals = analysis.signals.filter(s => s.signal !== 'NEUTRAL');
                    this.showStatus(
                        `✅ วิเคราะห์เสร็จสิ้น - ข้อมูล ${this.data1.length} แท่ง | สัญญาณเทรด ${tradingSignals.length} ครั้ง | ซื้อ ${analysis.signalCount.buy} | ขาย ${analysis.signalCount.sell}`, 
                        'success'
                    );
                    
                } catch (error) {
                    console.error('Error:', error);
                    this.showStatus(`❌ เกิดข้อผิดพลาด: ${error.message}`, 'error');
                } finally {
                    const loadButton = document.getElementById('loadData');
                    loadButton.disabled = false;
                }
            }

            async refreshData() {
                if (this.ws) {
                    this.ws.close();
                    this.isConnected = false;
                }
                await this.loadData();
            }
        }

        // Initialize the application
        const analyzer = new DerivTradingAnalyzer();
    </script>
</body>
</html>