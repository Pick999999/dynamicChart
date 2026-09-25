สร้าง html เพจ เพื่อดึง ข้อมูล candle data จาก deriv.com มาแสดง โดยมี จุดประสงค์เพื่อศึกษาว่า 
แท่งทียนใหญ่ ประกอด้วยแท่งเทียนเล็กอะไรบ้าง และให้ทำตามหัวข้อ
   1. มี asset ให้เลือก 
   2. startdatetime,stopdatettime
   3. มี timeframe 2 อันให้เลือกคือ timeframe เล็ก และ timeframe ใหญ่ 
   3. เมือดึงข้อมูลมาได้ ให้วาด กราฟ candlestick+ema2 + ema3 ด้วย lightweightchart 2 อัน แต่ละอัน จะมี timeframe ที่ต่างกันให้เลือกได้ 
   โดย Graph A จะเป็น timeframe เล็กและ Graph B จะเป็น timeframe ใหญ่ แต่ละอันเลือก timeframe ได้
   4.เมื่อคลิก ที่ตัวแท่งเทียน ของ timeframe ใหญ่ ก็จะ นำ ข้อมูลจาก แท่งเทียนใหญ่ มาค้นข้อมูลใน timeframeเล็ก ว่าประกอปด้วยแท่งเทียนย่อยอะไรบ้าง
   5.ใช้  pure javascript
   6.เอาข้อมูลจริงๆ จาก deriv.com ไม่เอา mock data เด็ดขาด
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Candle Data Analyzer</title>
    
	<script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .controls {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
        }

        select, input {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-wrapper {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .chart-title {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
            text-align: center;
        }

        .chart {
            width: 100%;
            height: 400px;
            margin-bottom: 10px;
        }

        .loading {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #667eea;
        }

        .error {
            background: #ffe6e6;
            color: #d32f2f;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #d32f2f;
        }

        .info-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            margin-top: 20px;
        }

        .candle-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
        }

        .detail-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .controls {
                grid-template-columns: 1fr;
            }
            
            .candle-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📈 Deriv Candle Data Analyzer</h1>
        
        <div class="controls">
            <div class="form-group">
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
                </select>
            </div>
            
            <div class="form-group">
                <label for="startDate">Start Date:</label>
                <input type="datetime-local" id="startDate">
            </div>
            
            <div class="form-group">
                <label for="endDate">End Date:</label>
                <input type="datetime-local" id="endDate">
            </div>
            
            <div class="form-group">
                <label for="smallTimeframe">Small Timeframe:</label>
                <select id="smallTimeframe">
                    <option value="60">1 Minute</option>
                    <option value="120">2 Minutes</option>
                    <option value="180">3 Minutes</option>
                    <option value="300">5 Minutes</option>
                    <option value="600">10 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="1800">30 Minutes</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="largeTimeframe">Large Timeframe:</label>
                <select id="largeTimeframe">
                    <option value="180">3 Minutes</option>
                    <option value="300">5 Minutes</option>
                    <option value="600">10 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="1800">30 Minutes</option>
                </select>
            </div>
            
            <button id="fetchData">Fetch Data</button>
        </div>

        <div id="loadingIndicator" class="loading" style="display: none;">
            🔄 Loading candle data...
        </div>

        <div id="errorMessage" class="error" style="display: none;"></div>

        <div class="charts-container">
            <div class="chart-wrapper">
                <div class="chart-title">Graph A - Small Timeframe</div>
                <div id="chartSmall" class="chart"></div>
            </div>
            
            <div class="chart-wrapper">
                <div class="chart-title">Graph B - Large Timeframe</div>
                <div id="chartLarge" class="chart"></div>
            </div>
        </div>

        <div id="infoPanel" class="info-panel" style="display: none;">
            <h3>📊 Selected Large Candle Analysis</h3>
            <div id="candleInfo"></div>
            <div id="candleDetails" class="candle-details"></div>
        </div>
    </div>

    <script>
        class DerivCandleAnalyzer {
            constructor() {
                this.wsConnection = null;
                this.chartSmall = null;
                this.chartLarge = null;
                this.candleSeriesSmall = null;
                this.candleSeriesLarge = null;
                this.ema2SeriesSmall = null;
                this.ema3SeriesSmall = null;
                this.ema2SeriesLarge = null;
                this.ema3SeriesLarge = null;
                this.smallTimeframeData = [];
                this.largeTimeframeData = [];
                this.reqId = 1;
				this.selectedCandleMarker = null;
                
                this.initializeApp();
            }

            initializeApp() {
                this.setDefaultDates();
                this.initializeCharts();
                this.attachEventListeners();
            }

            setDefaultDates() {
                const now = new Date();
                const oneDayAgo = new Date(now.getTime() - 24 * 60 * 60 * 1000);
                
                document.getElementById('endDate').value = this.formatDateTimeLocal(now);
                document.getElementById('startDate').value = this.formatDateTimeLocal(oneDayAgo);
            }

            formatDateTimeLocal(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }

            initializeCharts() {
                // Initialize small timeframe chart
                this.chartSmall = LightweightCharts.createChart(document.getElementById('chartSmall'), {
                    width: document.getElementById('chartSmall').clientWidth,
                    height: 400,
                    layout: {
                        backgroundColor: '#ffffff',
                        textColor: '#333',
                    },
                    grid: {
                        vertLines: { color: '#e1e1e1' },
                        horzLines: { color: '#e1e1e1' },
                    },
                    crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                    rightPriceScale: { borderColor: '#cccccc' },
                    timeScale: { 
					  borderColor: '#cccccc',
					  timeVisible: true,
                      secondsVisible: false
					},
                });

                // Initialize large timeframe chart
                this.chartLarge = LightweightCharts.createChart(document.getElementById('chartLarge'), {
                    width: document.getElementById('chartLarge').clientWidth,
                    height: 400,
                    layout: {
                        backgroundColor: '#ffffff',
                        textColor: '#333',
                    },
                    grid: {
                        vertLines: { color: '#e1e1e1' },
                        horzLines: { color: '#e1e1e1' },
                    },
                    crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                    rightPriceScale: { borderColor: '#cccccc' },
                    timeScale: { 
					  borderColor: '#cccccc',
					  timeVisible: true,
                      secondsVisible: false
					},
                });

                // Add series to charts
                this.candleSeriesSmall = this.chartSmall.addCandlestickSeries({
                    upColor: '#26a69a',
                    downColor: '#ef5350',
                    borderVisible: false,
                    wickUpColor: '#26a69a',
                    wickDownColor: '#ef5350',
                });

                this.candleSeriesLarge = this.chartLarge.addCandlestickSeries({
                    upColor: '#26a69a',
                    downColor: '#ef5350',
                    borderVisible: false,
                    wickUpColor: '#26a69a',
                    wickDownColor: '#ef5350',
                });

                // Add EMA series
                this.ema2SeriesSmall = this.chartSmall.addLineSeries({
                    color: '#2196f3',
                    lineWidth: 2,
                });

                this.ema3SeriesSmall = this.chartSmall.addLineSeries({
                    color: '#ff9800',
                    lineWidth: 2,
                });

                this.ema2SeriesLarge = this.chartLarge.addLineSeries({
                    color: '#2196f3',
                    lineWidth: 2,
                });

                this.ema3SeriesLarge = this.chartLarge.addLineSeries({
                    color: '#ff9800',
                    lineWidth: 2,
                });

                // Handle window resize
                window.addEventListener('resize', () => {
                    this.chartSmall.applyOptions({ width: document.getElementById('chartSmall').clientWidth });
                    this.chartLarge.applyOptions({ width: document.getElementById('chartLarge').clientWidth });
                });
            }

            attachEventListeners() {
                document.getElementById('fetchData').addEventListener('click', () => {
                    this.fetchCandleData();
                });

                // Add click handler for large timeframe chart
                this.chartLarge.subscribeClick((param) => {
                    if (param.time && param.point) {
                        this.handleLargeCandleClick(param.time);
                    }
                });
            }

            connectWebSocket() {
                return new Promise((resolve, reject) => {
                    if (this.wsConnection && this.wsConnection.readyState === WebSocket.OPEN) {
                        resolve(this.wsConnection);
                        return;
                    }

                    this.wsConnection = new WebSocket('wss://ws.binaryws.com/websockets/v3?app_id=1089');
                    
                    this.wsConnection.onopen = () => {
                        console.log('WebSocket connected');
                        resolve(this.wsConnection);
                    };

                    this.wsConnection.onerror = (error) => {
                        console.error('WebSocket error:', error);
                        reject(error);
                    };

                    this.wsConnection.onclose = () => {
                        console.log('WebSocket disconnected');
                    };
                });
            }

            async fetchCandleData() {
                const asset = document.getElementById('asset').value;
                const startDate = new Date(document.getElementById('startDate').value);
                const endDate = new Date(document.getElementById('endDate').value);
                const smallTf = parseInt(document.getElementById('smallTimeframe').value);
                const largeTf = parseInt(document.getElementById('largeTimeframe').value);

                if (!startDate || !endDate || startDate >= endDate) {
                    this.showError('กรุณาเลือกวันที่ให้ถูกต้อง');
                    return;
                }

                this.showLoading(true);
                this.hideError();

                try {
                    const ws = await this.connectWebSocket();
                    
                    // Fetch both timeframes
                    const [smallData, largeData] = await Promise.all([
                        this.fetchTicksHistory(ws, asset, smallTf, startDate, endDate),
                        this.fetchTicksHistory(ws, asset, largeTf, startDate, endDate)
                    ]);

                    this.smallTimeframeData = smallData;
                    this.largeTimeframeData = largeData;

                    this.updateCharts();
                    
                } catch (error) {
                    console.error('Error fetching data:', error);
                    this.showError('เกิดข้อผิดพลาดในการดึงข้อมูล: ' + error.message);
                } finally {
                    this.showLoading(false);
                }
            }

            fetchTicksHistory(ws, symbol, granularity, startDate, endDate) {
                return new Promise((resolve, reject) => {
                    const reqId = this.reqId++;
                    
                    const request = {
                        ticks_history: symbol,
                        adjust_start_time: 1,
                        count: 5000,
                        end: 'latest',
                        granularity: granularity,
                        start: Math.floor(startDate.getTime() / 1000),
                        style: 'candles',
                        req_id: reqId
                    };

                    const messageHandler = (event) => {
                        const data = JSON.parse(event.data);
                        
                        if (data.req_id === reqId) {
                            ws.removeEventListener('message', messageHandler);
                            
                            if (data.error) {
                                reject(new Error(data.error.message));
                                return;
                            }

                            if (data.candles) {
                                const candles = data.candles.map(candle => ({
                                    time: candle.epoch,
                                    open: parseFloat(candle.open),
                                    high: parseFloat(candle.high),
                                    low: parseFloat(candle.low),
                                    close: parseFloat(candle.close)
                                }));
                                resolve(candles);
                            } else {
                                reject(new Error('No candle data received'));
                            }
                        }
                    };

                    ws.addEventListener('message', messageHandler);
                    ws.send(JSON.stringify(request));

                    // Timeout after 30 seconds
                    setTimeout(() => {
                        ws.removeEventListener('message', messageHandler);
                        reject(new Error('Request timeout'));
                    }, 30000);
                });
            }

            updateCharts() {
                if (this.smallTimeframeData.length === 0 || this.largeTimeframeData.length === 0) {
                    this.showError('ไม่พบข้อมูลในช่วงเวลาที่เลือก');
                    return;
                }

                // Reset chart title to default
                const chartTitle = document.querySelector('.chart-wrapper:first-child .chart-title');
                chartTitle.innerHTML = 'Graph A - Small Timeframe';

                // Update candlestick data
                this.candleSeriesSmall.setData(this.smallTimeframeData);
                this.candleSeriesLarge.setData(this.largeTimeframeData);

                // Calculate and update EMA
                const smallEma2 = this.calculateEMA(this.smallTimeframeData, 2);
                const smallEma3 = this.calculateEMA(this.smallTimeframeData, 3);
                const largeEma2 = this.calculateEMA(this.largeTimeframeData, 2);
                const largeEma3 = this.calculateEMA(this.largeTimeframeData, 3);

                this.ema2SeriesSmall.setData(smallEma2);
                this.ema3SeriesSmall.setData(smallEma3);
                this.ema2SeriesLarge.setData(largeEma2);
                this.ema3SeriesLarge.setData(largeEma3);

                // Fit content
                this.chartSmall.timeScale().fitContent();
                this.chartLarge.timeScale().fitContent();

                // Hide info panel when new data is loaded
                document.getElementById('infoPanel').style.display = 'none';
            }

            calculateEMA(data, period) {
                const ema = [];
                const multiplier = 2 / (period + 1);
                
                if (data.length === 0) return ema;
                
                // First EMA value is the first close price
                ema.push({
                    time: data[0].time,
                    value: data[0].close
                });
                
                // Calculate subsequent EMA values
                for (let i = 1; i < data.length; i++) {
                    const emaValue = (data[i].close * multiplier) + (ema[i-1].value * (1 - multiplier));
                    ema.push({
                        time: data[i].time,
                        value: emaValue
                    });
                }
                
                return ema;
            }

            handleLargeCandleClick(clickedTime) {
                // Find the clicked large candle
                const largeCandle = this.largeTimeframeData.find(candle => candle.time === clickedTime);
                if (!largeCandle) return;
				// Remove existing marker
				if (this.selectedCandleMarker) {
					//this.candleSeriesLarge.removeMarker(this.selectedCandleMarker);
				}
				this.candleSeriesLarge.setMarkers([]);

				// Add new marker
				this.selectedCandleMarker = {
					time: clickedTime,
					position: 'aboveBar',
					color: '#ff6b35',
					shape: 'arrowDown',
					text: 'Sel'
				};
				this.candleSeriesLarge.setMarkers([this.selectedCandleMarker]);

                // Get timeframe durations
                const largeTf = parseInt(document.getElementById('largeTimeframe').value);
                const smallTf = parseInt(document.getElementById('smallTimeframe').value);
                
                // Calculate the time range for this large candle
                const candleStart = largeCandle.time;
                const candleEnd = candleStart + largeTf;

                // Find all small candles within this large candle's time range
                const smallCandles = this.smallTimeframeData.filter(candle => 
                    candle.time >= candleStart && candle.time < candleEnd
                );

                // Update small chart to show only the selected timeframe range
                this.updateSmallChartForTimeRange(smallCandles, candleStart, candleEnd);

                this.displayCandleAnalysis(largeCandle, smallCandles, largeTf, smallTf);
            }

            displayCandleAnalysis(largeCandle, smallCandles, largeTf, smallTf) {
                const infoPanel = document.getElementById('infoPanel');
                const candleInfo = document.getElementById('candleInfo');
                const candleDetails = document.getElementById('candleDetails');

                // Format large candle info
                const largeCandleDate = new Date(largeCandle.time * 1000);
                const largeCandleEndDate = new Date((largeCandle.time + largeTf) * 1000);
                const isLargeBullish = largeCandle.close > largeCandle.open;
                const largeCandleRange = largeCandle.high - largeCandle.low;

                candleInfo.innerHTML = `
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div><strong>เวลาเริ่มต้น:</strong> ${largeCandleDate.toLocaleString('th-TH')}</div>
                        <div><strong>เวลาสิ้นสุด:</strong> ${largeCandleEndDate.toLocaleString('th-TH')}</div>
                        <div><strong>Open:</strong> ${largeCandle.open.toFixed(5)}</div>
                        <div><strong>High:</strong> ${largeCandle.high.toFixed(5)}</div>
                        <div><strong>Low:</strong> ${largeCandle.low.toFixed(5)}</div>
                        <div><strong>Close:</strong> ${largeCandle.close.toFixed(5)}</div>
                        <div><strong>ทิศทาง:</strong> <span style="color: ${isLargeBullish ? '#26a69a' : '#ef5350'}">${isLargeBullish ? '📈 ขาขึ้น' : '📉 ขาลง'}</span></div>
                        <div><strong>ช่วงเวลา:</strong> ${this.getTimeframeName(largeTf)}</div>
                    </div>
                `;

                // Analyze small candles
                const bullishSmall = smallCandles.filter(c => c.close > c.open).length;
                const bearishSmall = smallCandles.filter(c => c.close < c.open).length;
                const dojiSmall = smallCandles.filter(c => Math.abs(c.close - c.open) < (c.high - c.low) * 0.1).length;

                // Calculate volume-like metrics
                const totalSmallRange = smallCandles.reduce((sum, c) => sum + (c.high - c.low), 0);
                const avgSmallRange = totalSmallRange / smallCandles.length || 0;

                candleDetails.innerHTML = `
                    <div class="detail-card">
                        <h4>📊 สรุปแท่งเทียนย่อย (${smallCandles.length} แท่ง)</h4>
                        <div style="margin-top: 10px;">
                            <div>🟢 แท่งเทียนขาขึ้น: ${bullishSmall} แท่ง (${((bullishSmall/smallCandles.length)*100).toFixed(1)}%)</div>
                            <div>🔴 แท่งเทียนขาลง: ${bearishSmall} แท่ง (${((bearishSmall/smallCandles.length)*100).toFixed(1)}%)</div>
                            <div>⚫ แท่งเทียน Doji: ${dojiSmall} แท่ง (${((dojiSmall/smallCandles.length)*100).toFixed(1)}%)</div>
                            <div>🎯 <strong>กราฟซ้ายแสดงเฉพาะช่วงเวลานี้</strong></div>
                        </div>
                    </div>
                    <div class="detail-card">
                        <h4>📈 การวิเคราะห์ Range</h4>
                        <div style="margin-top: 10px;">
                            <div><strong>Range แท่งใหญ่:</strong> ${largeCandleRange.toFixed(5)}</div>
                            <div><strong>Range เฉลี่ยแท่งเล็ก:</strong> ${avgSmallRange.toFixed(5)}</div>
                            <div><strong>อัตราส่วน:</strong> ${(largeCandleRange / avgSmallRange).toFixed(2)}:1</div>
                            <div><strong>ประสิทธิภาพ:</strong> ${((Math.abs(largeCandle.close - largeCandle.open) / largeCandleRange) * 100).toFixed(1)}%</div>
                        </div>
                    </div>
                `;

                // Add individual small candles list
                const smallCandlesList = smallCandles.map((candle, index) => {
                    const date = new Date(candle.time * 1000);
                    const isBullish = candle.close > candle.open;
                    const range = candle.high - candle.low;
                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${date.toLocaleTimeString('th-TH')}</td>
                            <td>${candle.open.toFixed(5)}</td>
                            <td>${candle.high.toFixed(5)}</td>
                            <td>${candle.low.toFixed(5)}</td>
                            <td>${candle.close.toFixed(5)}</td>
                            <td style="color: ${isBullish ? '#26a69a' : '#ef5350'}">${isBullish ? '↗️' : '↘️'}</td>
                            <td>${range.toFixed(5)}</td>
                        </tr>
                    `;
                }).join('');

                candleDetails.innerHTML += `
                    <div class="detail-card" style="grid-column: 1/-1;">
                        <h4>📋 รายละเอียดแท่งเทียนย่อยทั้งหมด</h4>
                        <div style="overflow-x: auto; margin-top: 10px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead>
                                    <tr style="background: #f0f0f0;">
                                        <th style="padding: 5px; border: 1px solid #ddd;">ลำดับ</th>
                                        <th style="padding: 5px; border: 1px solid #ddd;">เวลา</th>
                                        <th style="padding: 5px; border: 1px solid #ddd;">Open</th>
                                        <th style="padding: 5px; border: 1px solid #ddd;">High</th>
                                        <th style="padding: 5px; border: 1px solid #ddd;">Low</th>
                                        <th style="padding: 5px; border: 1px solid #ddd;">Close</th>
                                        <th style="padding: 5px; border: 1px solid #ddd;">ทิศทาง</th>
                                        <th style="padding: 5px; border: 1px solid #ddd;">Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${smallCandlesList}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;

                infoPanel.style.display = 'block';
            }

            updateSmallChartForTimeRange(smallCandles, startTime, endTime) {
                if (smallCandles.length === 0) {
                    // If no small candles in range, clear the chart
                    this.candleSeriesSmall.setData([]);
                    this.ema2SeriesSmall.setData([]);
                    this.ema3SeriesSmall.setData([]);
                    return;
                }

                // Update candlestick data for the specific time range
                this.candleSeriesSmall.setData(smallCandles);

                // Calculate and update EMA for the filtered data
                const smallEma2 = this.calculateEMA(smallCandles, 2);
                const smallEma3 = this.calculateEMA(smallCandles, 3);

                this.ema2SeriesSmall.setData(smallEma2);
                this.ema3SeriesSmall.setData(smallEma3);

                // Set visible time range to focus on the selected period
                this.chartSmall.timeScale().setVisibleRange({
                    from: startTime,
                    to: endTime
                });

                // Update chart title to show focused time range
                const startDate = new Date(startTime * 1000);
                const endDate = new Date(endTime * 1000);
                const chartTitle = document.querySelector('.chart-wrapper:first-child .chart-title');
                chartTitle.innerHTML = `Graph A - Small Timeframe<br><small style="color: #666; font-weight: normal;">Focus: ${startDate.toLocaleTimeString('th-TH')} - ${endDate.toLocaleTimeString('th-TH')}</small>`;
            }

            getTimeframeName(seconds) {
                const minutes = seconds / 60;
                if (minutes < 60) {
                    return `${minutes} นาที`;
                } else {
                    const hours = minutes / 60;
                    return `${hours} ชั่วโมง`;
                }
            }

            showLoading(show) {
                document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
                document.getElementById('fetchData').disabled = show;
            }

            showError(message) {
                const errorDiv = document.getElementById('errorMessage');
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }

            hideError() {
                document.getElementById('errorMessage').style.display = 'none';
            }
        }

        // Initialize the application when the page loads
        window.addEventListener('DOMContentLoaded', () => {
            new DerivCandleAnalyzer();
        });
    </script>
</body>
</html>