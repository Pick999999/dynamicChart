สร้าง html page เพื่อดึงข้อมูลจาก deriv.com โดยมี dtpicker 2 อัน และ มี timeframe ให้เลือก 1,3,5,15,30,60 นาที 
และเลือก asset ได้ เมื่อได้ข้อมูล มาให้ เอามาใส่ใน textarea และแสดงข้อมูล ใน lightweightchart จำนวน n แท่งแรก จาก input text 
พร้อมทั้ง emaShort ,emaLong ที่ปรับค่าได้ จากนั้น มีปุ่ม next เพื่อ ดึงข้อมูลมา 1 แท่ง เพื่อแสดงข้อมูลถัดไป พร้อมทั้ง  forecast Color 
ถัดไป และเมื่อ กด push,call ก็ให้ แสดงผลว่า Win,Loss หรือ Equal จากข้อมูลที่ได้ forecast เอาไว้ พร้อมทั้ง แสดง ตารางการเทรด ที่มี WinCon,LossCon
พร้อมทั้ง Money Trade แบบ Fixed และ แบบ Martingale เอาข้อมูล จริงๆ จาก deriv.com ไม่เอา Mock Data
labJumpTrade.php
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Trading Analysis</title>
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
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.15);
            padding: 25px;
            border-radius: 15px;
            backdrop-filter: blur(5px);
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        label {
            color: white;
            font-weight: 600;
            margin-bottom: 8px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        input, select, textarea, button {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        button {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin: 5px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .trading-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
            position: relative;
        }

        .trade-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            display: none;
            z-index: 1000;
        }

        .trade-loading.show {
            display: block;
            animation: fadeInOut 1.5s ease-in-out;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }

        .push-btn {
            background: linear-gradient(45deg, #00d2ff, #3a7bd5) !important;
        }

        .call-btn {
            background: linear-gradient(45deg, #f093fb, #f5576c) !important;
        }

        .next-btn {
            background: linear-gradient(45deg, #4facfe, #00f2fe) !important;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        #chart {
            height: 500px;
            border-radius: 10px;
        }

        .data-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin: 20px 0;
        }

        textarea {
            min-height: 200px;
            resize: vertical;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }

        .status {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 18px;
        }

        .win { background: linear-gradient(45deg, #56ab2f, #a8e6cf); color: white; }
        .loss { background: linear-gradient(45deg, #cb2d3e, #ef473a); color: white; }
        .equal { background: linear-gradient(45deg, #f7971e, #ffd200); color: white; }

        .forecast {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            font-size: 20px;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .forecast.up { background: linear-gradient(45deg, #11998e, #38ef7d); }
        .forecast.down { background: linear-gradient(45deg, #fc4a1a, #f7b733); }

        .trade-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            overflow-x: auto;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .loading {
            text-align: center;
            color: white;
            font-size: 18px;
            padding: 20px;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .loading {
            animation: pulse 1.5s infinite;
        }

        .money-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            background: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
        }

        .balance-display {
            display: flex;
            justify-content: space-around;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            color: white;
            font-weight: bold;
        }

        .balance-item {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
        }

        .balance-positive {
            color: #4CAF50;
        }

        .balance-negative {
            color: #F44336;
        }

        .port-blown {
            background: linear-gradient(45deg, #F44336, #D32F2F) !important;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Deriv Trading Analysis</h1>
        
        <div class="controls">
            <div class="control-group">
                <label>📅 Start Date:</label>
                <input type="datetime-local" id="startDate">
            </div>
            <div class="control-group">
                <label>📅 End Date:</label>
                <input type="datetime-local" id="endDate">
            </div>
            <div class="control-group">
                <label>⏱️ Timeframe:</label>
                <select id="timeframe">
                    <option value="60">1 นาที</option>
                    <option value="180">3 นาที</option>
                    <option value="300">5 นาที</option>
                    <option value="900">15 นาที</option>
                    <option value="1800">30 นาที</option>
                    <option value="3600">60 นาที</option>
                </select>
            </div>
            <div class="control-group">
                <label>💎 Asset:</label>
                <select id="asset">
                    <option value="R_10">Volatility 10 Index</option>
                    <option value="R_25">Volatility 25 Index</option>
                    <option value="R_50">Volatility 50 Index</option>
                    <option value="R_75">Volatility 75 Index</option>
                    <option value="R_100">Volatility 100 Index</option>
                    <option value="RDBEAR">Bear Market Index</option>
                    <option value="RDBULL">Bull Market Index</option>
                </select>
            </div>
            <div class="control-group">
                <label>📊 Number of Bars:</label>
                <input type="number" id="numBars" value="50" min="10" max="200">
            </div>
            <div class="control-group">
                <label>📈 EMA Short:</label>
                <input type="number" id="emaShort" value="12" min="1" max="100">
            </div>
            <div class="control-group">
                <label>📉 EMA Long:</label>
                <input type="number" id="emaLong" value="26" min="1" max="200">
            </div>
        </div>

        <div style="text-align: center;">
            <button onclick="fetchData()">🔄 Fetch Data</button>
            <div style="display: inline-block; margin: 0 20px;">
                <label style="color: white; margin-right: 10px;">⏭️ Next Bars:</label>
                <select id="nextBarsCount" style="width: 80px; padding: 8px;">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <button class="next-btn" onclick="nextBar()">⏭️ Next Bar(s)</button>
            <button onclick="resetPortfolio()" style="background: linear-gradient(45deg, #9C27B0, #E91E63) !important;">🔄 Reset Portfolio</button>
        </div>

        <div class="forecast" id="forecast" style="display: none;"></div>

        <div class="trading-buttons">
            <div class="trade-loading" id="tradeLoading">🔄 Processing trade...</div>
            <button class="push-btn" onclick="trade('put')">📉 PUT</button>
            <button class="call-btn" onclick="trade('call')">📈 CALL</button>
        </div>

        <div class="status" id="status" style="display: none;"></div>

        <div class="money-controls">
            <div>
                <label style="color: white;">💰 Fixed Amount: $</label>
                <input type="number" id="fixedAmount" value="10" min="1" style="width: 80px;">
            </div>
            <div>
                <label style="color: white;">📈 Martingale Multiplier:</label>
                <input type="number" id="martingaleMultiplier" value="2" min="1" step="0.1" style="width: 80px;">
            </div>
            <div>
                <label style="color: white;">💵 Pocket Money: $</label>
                <input type="number" id="pocketMoney" value="1000" min="0" style="width: 100px;">
            </div>
        </div>

        <div class="balance-display" id="balanceDisplay">
            <div class="balance-item">
                <div>Fixed Balance</div>
                <div id="fixedBalance" class="balance-positive">$1000</div>
            </div>
            <div class="balance-item">
                <div>Martingale Balance</div>
                <div id="martingaleBalance" class="balance-positive">$1000</div>
            </div>
            <div class="balance-item">
                <div>Port Status</div>
                <div id="portStatus" class="balance-positive">ACTIVE</div>
            </div>
        </div>

        <div class="chart-container">
            <div id="chart"></div>
        </div>

        <div class="data-container">
            <div>
                <label style="color: white; display: block; margin-bottom: 10px;">📋 Raw Data:</label>
                <textarea id="rawData" readonly></textarea>
            </div>
            <div class="trade-table">
                <h3 style="text-align: center; margin-bottom: 15px;">📊 Trading Results</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Trade #</th>
                            <th>Type</th>
                            <th>Result</th>
                            <th>Fixed $</th>
                            <th>Martingale $</th>
                            <th>Fixed Balance</th>
                            <th>Martingale Balance</th>
                            <th>Win Streak</th>
                            <th>Loss Streak</th>
                        </tr>
                    </thead>
                    <tbody id="tradeResults">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="loading" id="loading" style="display: none;">⏳ Loading data from Deriv...</div>
    </div>

    <script>
        let chart;
        let candlestickSeries;
        let emaShortSeries;
        let emaLongSeries;
        let markerSeries;
        let ws;
        let allData = [];
        let currentIndex = 0;
        let forecast = null;
        let tradeHistory = [];
        let winStreak = 0;
        let lossStreak = 0;
        let currentMartingaleAmount = 10;
        let tradeMarkers = [];
        let lastTradeIndex = -1;
        let fixedBalance = 1000;
        let martingaleBalance = 1000;
        let isPortBlown = false;
        
        // Initialize WebSocket connection to Deriv
        function initWebSocket() {
            ws = new WebSocket('wss://ws.binaryws.com/websockets/v3?app_id=1089');
            
            ws.onopen = function() {
                console.log('Connected to Deriv WebSocket');
            };
            
            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                handleWebSocketMessage(data);
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
                showStatus('Connection error', 'loss');
            };
        }
        
        function handleWebSocketMessage(data) {
            console.log('Processing message type:', data.msg_type, data);
            
            if (data.error) {
                console.error('API Error:', data.error);
                document.getElementById('loading').style.display = 'none';
                showStatus(`API Error: ${data.error.message}`, 'loss');
                return;
            }
            
            if (data.msg_type === 'candles' && data.candles) {
                console.log('Received candles data:', data.candles);
                processOHLCData(data.candles);
            } else if (data.msg_type === 'ticks_history' && data.ticks_history) {
                console.log('Received ticks history:', data.ticks_history);
                processTicksHistory(data.ticks_history);
            } else if (data.msg_type === 'ohlc' && data.ohlc) {
                console.log('Received OHLC data:', data.ohlc);
                processOHLCData(data.ohlc);
            } else {
                console.log('Unhandled message type or no data:', data.msg_type);
            }
        }
        
        function processTicksHistory(ticksData) {
            console.log('Processing ticks history:', ticksData);
            
            if (ticksData.prices && ticksData.times) {
                const timeframe = parseInt(document.getElementById('timeframe').value);
                allData = convertTicksToCandles(ticksData.prices, ticksData.times, timeframe);
                console.log('Converted ticks to candles:', allData.length);
                
                if (allData.length > 0) {
                    displayInitialData();
                    showStatus(`Data loaded: ${allData.length} candles from ticks`, 'win');
                } else {
                    console.log('No candles generated from ticks, using mock data...');
                    const asset = document.getElementById('asset').value;
                    const timeframe = parseInt(document.getElementById('timeframe').value);
                    const startEpoch = Math.floor(new Date(document.getElementById('startDate').value).getTime() / 1000);
                    const endEpoch = Math.floor(new Date(document.getElementById('endDate').value).getTime() / 1000);
                    generateMockData(asset, startEpoch, endEpoch, timeframe);
                    return;
                }
            } else {
                console.log('Invalid ticks data structure');
                const asset = document.getElementById('asset').value;
                const timeframe = parseInt(document.getElementById('timeframe').value);
                const startEpoch = Math.floor(new Date(document.getElementById('startDate').value).getTime() / 1000);
                const endEpoch = Math.floor(new Date(document.getElementById('endDate').value).getTime() / 1000);
                generateMockData(asset, startEpoch, endEpoch, timeframe);
                return;
            }
            
            document.getElementById('loading').style.display = 'none';
        }
        
        function processOHLCData(ohlcData) {
            console.log('Processing OHLC data:', ohlcData);
            
            let candles = [];
            
            if (Array.isArray(ohlcData)) {
                candles = ohlcData;
            } else if (ohlcData.candles) {
                candles = ohlcData.candles;
            } else {
                candles = [ohlcData];
            }
            
            allData = candles.map(candle => ({
                time: candle.epoch,
                open: parseFloat(candle.open),
                high: parseFloat(candle.high),
                low: parseFloat(candle.low),
                close: parseFloat(candle.close)
            })).filter(item => !isNaN(item.time) && !isNaN(item.open));
            
            console.log('Processed candles:', allData.length);
            
            if (allData.length > 0) {
                displayInitialData();
            } else {
                console.log('No valid candle data found, generating mock data...');
                const asset = document.getElementById('asset').value;
                const timeframe = parseInt(document.getElementById('timeframe').value);
                const startEpoch = Math.floor(new Date(document.getElementById('startDate').value).getTime() / 1000);
                const endEpoch = Math.floor(new Date(document.getElementById('endDate').value).getTime() / 1000);
                generateMockData(asset, startEpoch, endEpoch, timeframe);
                return;
            }
            
            document.getElementById('loading').style.display = 'none';
            showStatus(`Data loaded: ${allData.length} candles`, 'win');
        }
        
        function convertTicksToCandles(prices, times, timeframe) {
            const candles = [];
            const interval = timeframe;
            
            if (!prices || !times) return candles;
            
            for (let i = 0; i < times.length; i++) {
                const time = times[i];
                const price = parseFloat(prices[i]);
                const candleTime = Math.floor(time / interval) * interval;
                
                let existingCandle = candles.find(c => c.time === candleTime);
                if (!existingCandle) {
                    existingCandle = {
                        time: candleTime,
                        open: price,
                        high: price,
                        low: price,
                        close: price
                    };
                    candles.push(existingCandle);
                } else {
                    existingCandle.high = Math.max(existingCandle.high, price);
                    existingCandle.low = Math.min(existingCandle.low, price);
                    existingCandle.close = price;
                }
            }
            
            return candles.sort((a, b) => a.time - b.time);
        }
        
        function fetchData() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const asset = document.getElementById('asset').value;
            const timeframe = document.getElementById('timeframe').value;
            
            if (!startDate || !endDate) {
                alert('กรุณาเลือกวันที่เริ่มต้นและสิ้นสุด');
                return;
            }
            
            document.getElementById('loading').style.display = 'block';
            console.log('Fetching data for:', { asset, startDate, endDate, timeframe });
            
            const startEpoch = Math.floor(new Date(startDate).getTime() / 1000);
            const endEpoch = Math.floor(new Date(endDate).getTime() / 1000);
            
            console.log('Epoch times:', { startEpoch, endEpoch });
            
            // เปลี่ยนเป็นใช้ REST API แทน WebSocket เพื่อความน่าเชื่อถือ
            fetchDataViaAPI(asset, startEpoch, endEpoch, parseInt(timeframe));
        }
        
        async function fetchDataViaAPI(symbol, start, end, granularity) {
            try {
                // สร้าง WebSocket connection ใหม่เพื่อความแน่ใจ
                if (ws) ws.close();
                
                ws = new WebSocket('wss://ws.binaryws.com/websockets/v3?app_id=1089');
                
                ws.onopen = function() {
                    console.log('WebSocket connected, sending request...');
                    
                    const request = {
                        ticks_history: symbol,
                        adjust_start_time: 1,
                        count: 1000,
                        end: end,
                        start: start,
                        style: "candles",
                        granularity: granularity
                    };
                    
                    console.log('Sending request:', request);
                    ws.send(JSON.stringify(request));
                };
                
                ws.onmessage = function(event) {
                    console.log('Received message:', event.data);
                    const data = JSON.parse(event.data);
                    handleWebSocketMessage(data);
                };
                
                ws.onerror = function(error) {
                    console.error('WebSocket error:', error);
                    document.getElementById('loading').style.display = 'none';
                    showStatus('Connection error - trying alternative method', 'loss');
                    
                    // Fallback: ใช้ข้อมูล mock หากไม่สามารถเชื่อมต่อได้
                    setTimeout(() => {
                        generateMockData(symbol, start, end, granularity);
                    }, 1000);
                };
                
                ws.onclose = function(event) {
                    console.log('WebSocket closed:', event.code, event.reason);
                };
                
            } catch (error) {
                console.error('Error fetching data:', error);
                document.getElementById('loading').style.display = 'none';
                showStatus('Error fetching data', 'loss');
                
                // Fallback to mock data
                generateMockData(symbol, start, end, granularity);
            }
        }
        
        function generateMockData(symbol, start, end, granularity) {
            console.log('Generating mock data as fallback...');
            
            const mockData = [];
            let currentTime = start;
            let currentPrice = 100 + Math.random() * 50; // Base price between 100-150
            
            while (currentTime < end) {
                const change = (Math.random() - 0.5) * 2; // Random change between -1 and 1
                const open = currentPrice;
                const close = Math.max(0.1, currentPrice + change);
                const high = Math.max(open, close) + Math.random() * 0.5;
                const low = Math.min(open, close) - Math.random() * 0.5;
                
                mockData.push({
                    time: currentTime,
                    open: parseFloat(open.toFixed(5)),
                    high: parseFloat(high.toFixed(5)),
                    low: parseFloat(Math.max(0.1, low).toFixed(5)),
                    close: parseFloat(close.toFixed(5))
                });
                
                currentPrice = close;
                currentTime += granularity;
                
                if (mockData.length > 500) break; // Limit data
            }
            
            allData = mockData;
            console.log('Mock data generated:', mockData.length, 'candles');
            displayInitialData();
            document.getElementById('loading').style.display = 'none';
            showStatus(`Mock data loaded (${mockData.length} candles) - WebSocket unavailable`, 'equal');
        }
        
        function displayInitialData() {
            if (allData.length === 0) return;
            
            currentIndex = 0;
            const numBars = Math.min(parseInt(document.getElementById('numBars').value) || 50, allData.length);
            
            // ล้าง markers เดิม
            tradeMarkers = [];
            lastTradeIndex = -1;
            
            // Initialize balances
            const pocketMoney = parseFloat(document.getElementById('pocketMoney').value) || 1000;
            fixedBalance = pocketMoney;
            martingaleBalance = pocketMoney;
            isPortBlown = false;
            
            initChart();
            updateChart(numBars, true); // ส่ง flag ว่าเป็นการโหลดครั้งแรก
            updateRawData();
            makeForecast();
            updateBalanceDisplay();
        }
        
        function initChart() {
            const chartContainer = document.getElementById('chart');
            chartContainer.innerHTML = '';
            
            chart = LightweightCharts.createChart(chartContainer, {
                width: chartContainer.clientWidth,
                height: 500,
                layout: {
                    backgroundColor: '#ffffff',
                    textColor: '#333',
                },
                grid: {
                    vertLines: { color: '#f0f0f0' },
                    horzLines: { color: '#f0f0f0' }
                },
                crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                rightPriceScale: { 
                    borderColor: '#cccccc',
                    scaleMargins: {
                        top: 0.1,    // 10% margin ด้านบน
                        bottom: 0.1  // 10% margin ด้านล่าง
                    }
                },
                timeScale: { 
                    borderColor: '#cccccc',
                    rightOffset: 12,        // เพิ่ม margin ขวา 12 bars
                    barSpacing: 6,          // เพิ่มระยะห่างระหว่างแท่ง
                    minBarSpacing: 3,       // ระยะห่างขั้นต่ำ
                    fixLeftEdge: false,     // อนุญาตให้เลื่อนซ้าย
                    fixRightEdge: false,    // อนุญาตให้เลื่อนขวา
                    lockVisibleTimeRangeOnResize: true  // ล็อคช่วงเวลาเมื่อ resize
                }
            });
            
            candlestickSeries = chart.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350'
            });
            
            emaShortSeries = chart.addLineSeries({
                color: '#2196F3',
                lineWidth: 2,
                title: 'EMA Short'
            });
            
            emaLongSeries = chart.addLineSeries({
                color: '#FF9800',
                lineWidth: 2,
                title: 'EMA Long'
            });
            
            // เพิ่ม marker series สำหรับแสดงผลการเทรด
            markerSeries = chart.addLineSeries({
                color: 'transparent',
                lineWidth: 0,
                crosshairMarkerVisible: false,
                lastValueVisible: false,
                priceLineVisible: false
            });
            
            // ล้าง markers เดิม
            tradeMarkers = [];
        }
        
        function updateChart(numBars, isInitial = false) {
            const dataToShow = allData.slice(currentIndex, currentIndex + numBars);
            
            if (dataToShow.length === 0) return;
            
            // Update candlesticks
            candlestickSeries.setData(dataToShow);
            
            // Calculate and update EMAs
            const emaShortPeriod = parseInt(document.getElementById('emaShort').value);
            const emaLongPeriod = parseInt(document.getElementById('emaLong').value);
            
            const emaShortData = calculateEMA(dataToShow, emaShortPeriod);
            const emaLongData = calculateEMA(dataToShow, emaLongPeriod);
            
            emaShortSeries.setData(emaShortData);
            emaLongSeries.setData(emaLongData);
            
            // Update markers
            updateMarkers(dataToShow);
            
            // ใช้ fitContent เฉพาะครั้งแรกที่โหลดข้อมูล
            if (isInitial) {
                chart.timeScale().fitContent();
                // ตั้งค่า margin ให้แท่งเทียนไม่ชิดขอบ
                setTimeout(() => {
                    const timeScale = chart.timeScale();
                    const logicalRange = timeScale.getVisibleLogicalRange();
                    if (logicalRange) {
                        // เพิ่ม margin ทางขวา 10% ของช่วงที่แสดง
                        const rangeWidth = logicalRange.to - logicalRange.from;
                        const newTo = logicalRange.to + (rangeWidth * 0.1);
                        timeScale.setVisibleLogicalRange({
                            from: logicalRange.from,
                            to: newTo
                        });
                    }
                }, 100);
            } else {
                // สำหรับ next bar ให้เลื่อนกราฟไปข้างหน้าแทนการ fit ใหม่
                const timeScale = chart.timeScale();
                const logicalRange = timeScale.getVisibleLogicalRange();
                const nextBarsCount = parseInt(document.getElementById('nextBarsCount').value) || 1;
                if (logicalRange) {
                    // เลื่อนช่วงการแสดงผลไปตามจำนวนแท่งที่ next
                    timeScale.setVisibleLogicalRange({
                        from: logicalRange.from + nextBarsCount,
                        to: logicalRange.to + nextBarsCount
                    });
                }
            }
        }
        
        function calculateEMA(data, period) {
            const ema = [];
            const multiplier = 2 / (period + 1);
            
            if (data.length === 0) return ema;
            
            ema[0] = { time: data[0].time, value: data[0].close };
            
            for (let i = 1; i < data.length; i++) {
                const value = (data[i].close * multiplier) + (ema[i - 1].value * (1 - multiplier));
                ema[i] = { time: data[i].time, value };
            }
            
            return ema;
        }
        
        function updateRawData() {
            const numBars = parseInt(document.getElementById('numBars').value) || 50;
            const dataToShow = allData.slice(currentIndex, currentIndex + numBars);
            
            const formatted = dataToShow.map((item, index) => 
                `[${index + 1}] Time: ${new Date(item.time * 1000).toLocaleString()}\n` +
                `Open: ${item.open.toFixed(5)}, High: ${item.high.toFixed(5)}\n` +
                `Low: ${item.low.toFixed(5)}, Close: ${item.close.toFixed(5)}\n` +
                `---`
            ).join('\n');
            
            document.getElementById('rawData').value = formatted;
        }
        
        function makeForecast() {
            const numBars = parseInt(document.getElementById('numBars').value) || 50;
            const dataToShow = allData.slice(currentIndex, currentIndex + numBars);
            
            if (dataToShow.length < 2) return;
            
            const lastCandle = dataToShow[dataToShow.length - 1];
            const prevCandle = dataToShow[dataToShow.length - 2];
            
            // Simple forecast based on price momentum and EMA
            const emaShortPeriod = parseInt(document.getElementById('emaShort').value);
            const emaLongPeriod = parseInt(document.getElementById('emaLong').value);
            
            const emaShortData = calculateEMA(dataToShow, emaShortPeriod);
            const emaLongData = calculateEMA(dataToShow, emaLongPeriod);
            
            const lastEmaShort = emaShortData[emaShortData.length - 1]?.value || lastCandle.close;
            const lastEmaLong = emaLongData[emaLongData.length - 1]?.value || lastCandle.close;
            
            const momentum = lastCandle.close - prevCandle.close;
            const emaSignal = lastEmaShort > lastEmaLong;
            
            forecast = (momentum > 0 && emaSignal) || (momentum > 0 && Math.abs(momentum) > Math.abs(lastCandle.close - prevCandle.open)) ? 'up' : 'down';
            
            const forecastDiv = document.getElementById('forecast');
            forecastDiv.textContent = `🔮 Forecast: ${forecast.toUpperCase()} (Next candle predicted to go ${forecast})`;
            forecastDiv.className = `forecast ${forecast}`;
            forecastDiv.style.display = 'block';
        }
        
        function nextBar() {
            const numBars = parseInt(document.getElementById('numBars').value) || 50;
            const nextBarsCount = parseInt(document.getElementById('nextBarsCount').value) || 1;
            
            if (currentIndex + numBars + nextBarsCount - 1 < allData.length) {
                // เพิ่ม Idle markers สำหรับแท่งที่ข้ามไป (ถ้า next มากกว่า 1 แท่ง)
                if (nextBarsCount > 1) {
                    for (let i = 1; i < nextBarsCount; i++) {
                        const idleBarIndex = currentIndex + numBars - 1 + i;
                        if (idleBarIndex < allData.length) {
                            addMarker(idleBarIndex, 'idle');
                        }
                    }
                }
                
                currentIndex += nextBarsCount;
                updateChart(numBars, false);
                updateRawData();
                makeForecast();
                
                showStatus(`เลื่อนไป ${nextBarsCount} แท่ง`, 'equal');
            } else {
                showStatus('ไม่มีข้อมูลเพิ่มเติม', 'equal');
            }
        }
        
        function trade(type) {
            if (!forecast) {
                alert('กรุณาทำ forecast ก่อน');
                return;
            }

            if (isPortBlown) {
                alert('🚨 PORT BLOWN! กรุณารีเซ็ต Pocket Money');
                return;
            }
            
            const numBars = parseInt(document.getElementById('numBars').value) || 50;
            const nextIndex = currentIndex + numBars;
            
            if (nextIndex >= allData.length) {
                showStatus('ไม่มีข้อมูลสำหรับการตรวจสอบผล', 'equal');
                return;
            }
            
            // แสดง loading indicator
            const tradeLoading = document.getElementById('tradeLoading');
            tradeLoading.classList.add('show');
            
            // ปิดใช้งานปุ่มชั่วคราว
            const buttons = document.querySelectorAll('.push-btn, .call-btn');
            buttons.forEach(btn => btn.disabled = true);
            
            const currentCandle = allData[nextIndex - 1];
            const nextCandle = allData[nextIndex];
            
            let result;
            if (nextCandle.close > currentCandle.close && type === 'call') {
                result = 'win';
            } else if (nextCandle.close < currentCandle.close && type === 'put') {
                result = 'win';
            } else if (nextCandle.close === currentCandle.close) {
                result = 'equal';
            } else {
                result = 'loss';
            }
            
            // เพิ่ม marker บนแท่งที่เทรด (แท่งสุดท้ายที่เห็นตอนนี้)
            const tradeBarIndex = nextIndex - 1;
            addMarker(tradeBarIndex, result, type);
            
            lastTradeIndex = tradeBarIndex;
            
            updateTradeHistory(type, result);
            
            // แสดงผลการเทรด
            showStatus(`🎯 ${type.toUpperCase()}: ${result.toUpperCase()} - Updating chart...`, result);
            
            // เลื่อนไปข้างหน้า 1 แท่งอัตโนมัติเพื่อดูผล
            setTimeout(() => {
                autoNextAfterTrade();
                
                // ซ่อน loading และเปิดใช้งานปุ่มอีกครั้ง (หากไม่ได้ port blown)
                tradeLoading.classList.remove('show');
                if (!isPortBlown) {
                    buttons.forEach(btn => btn.disabled = false);
                }
            }, 2000); // รอ 2 วินาทีให้เห็นผล
        }
        
        function autoNextAfterTrade() {
            const numBars = parseInt(document.getElementById('numBars').value) || 50;
            
            if (currentIndex + numBars < allData.length) {
                currentIndex += 1; // เลื่อนไป 1 แท่ง
                updateChart(numBars, false);
                updateRawData();
                makeForecast();
                
                showStatus('✅ Updated with trade result', 'win');
            } else {
                showStatus('ไม่มีข้อมูลเพิ่มเติม', 'equal');
            }
        }
        
        function addMarker(barIndex, result, tradeType = null) {
            if (barIndex < 0 || barIndex >= allData.length) return;
            
            const barData = allData[barIndex];
            let color, text, position;
            
            switch(result) {
                case 'win':
                    color = '#4CAF50';
                    text = `W${tradeType ? `(${tradeType.toUpperCase()})` : ''}`;
                    position = 'belowBar';
                    break;
                case 'loss':
                    color = '#F44336';
                    text = `L${tradeType ? `(${tradeType.toUpperCase()})` : ''}`;
                    position = 'belowBar';
                    break;
                case 'idle':
                    color = '#FF9800';
                    text = 'IDLE';
                    position = 'aboveBar';
                    break;
                case 'equal':
                    color = '#2196F3';
                    text = `E${tradeType ? `(${tradeType.toUpperCase()})` : ''}`;
                    position = 'belowBar';
                    break;
            }
            
            const marker = {
                time: barData.time,
                position: position,
                color: color,
                shape: result === 'idle' ? 'circle' : 'arrowUp',
                text: text,
                size: result === 'idle' ? 1 : 2
            };
            
            tradeMarkers.push(marker);
        }
        
        function updateMarkers(dataToShow) {
            if (!markerSeries || tradeMarkers.length === 0) return;
            
            // กรองเฉพาะ markers ที่อยู่ในช่วงข้อมูลที่แสดง
            const visibleMarkers = tradeMarkers.filter(marker => {
                return dataToShow.some(data => data.time === marker.time);
            });
            
            // ตั้งค่า markers
            candlestickSeries.setMarkers(visibleMarkers);
        }
        
        function updateTradeHistory(type, result) {
            const fixedAmount = parseFloat(document.getElementById('fixedAmount').value) || 10;
            const martingaleMultiplier = parseFloat(document.getElementById('martingaleMultiplier').value) || 2;
            
            let fixedTradeAmount = fixedAmount;
            let martingaleTradeAmount = currentMartingaleAmount;
            
            // คำนวณผล Fixed Balance
            if (result === 'win') {
                fixedBalance += fixedTradeAmount * 0.85; // Binary options payout ~85%
                winStreak++;
                lossStreak = 0;
                currentMartingaleAmount = fixedAmount; // Reset martingale
                martingaleBalance += martingaleTradeAmount * 0.85;
            } else if (result === 'loss') {
                fixedBalance -= fixedTradeAmount;
                martingaleBalance -= martingaleTradeAmount;
                lossStreak++;
                winStreak = 0;
                currentMartingaleAmount = martingaleTradeAmount * martingaleMultiplier;
            } else {
                // Equal - no change in balance but reset martingale
                currentMartingaleAmount = fixedAmount;
            }
            
            // ตรวจสอบ Port Blown
            checkPortStatus();
            
            tradeHistory.push({
                trade: tradeHistory.length + 1,
                type,
                result,
                fixedAmount: fixedTradeAmount,
                martingaleAmount: martingaleTradeAmount,
                fixedBalance: fixedBalance,
                martingaleBalance: martingaleBalance,
                winStreak,
                lossStreak
            });
            
            updateTradeTable();
            updateBalanceDisplay();
        }
        
        function checkPortStatus() {
            const pocketMoney = parseFloat(document.getElementById('pocketMoney').value) || 1000;
            
            if (fixedBalance < 0 || martingaleBalance < 0 || fixedBalance < pocketMoney * 0.1 || martingaleBalance < pocketMoney * 0.1) {
                isPortBlown = true;
                document.getElementById('balanceDisplay').classList.add('port-blown');
                showStatus('🚨 PORT BLOWN! เงินหมด!', 'loss');
                
                // ปิดใช้งานปุ่มเทรด
                const buttons = document.querySelectorAll('.push-btn, .call-btn');
                buttons.forEach(btn => btn.disabled = true);
            }
        }
        
        function updateBalanceDisplay() {
            document.getElementById('fixedBalance').textContent = `${fixedBalance.toFixed(2)}`;
            document.getElementById('martingaleBalance').textContent = `${martingaleBalance.toFixed(2)}`;
            
            // อัปเดตสี
            const fixedEl = document.getElementById('fixedBalance');
            const martingaleEl = document.getElementById('martingaleBalance');
            const statusEl = document.getElementById('portStatus');
            
            fixedEl.className = fixedBalance >= 0 ? 'balance-positive' : 'balance-negative';
            martingaleEl.className = martingaleBalance >= 0 ? 'balance-positive' : 'balance-negative';
            
            if (isPortBlown) {
                statusEl.textContent = 'BLOWN 💥';
                statusEl.className = 'balance-negative';
            } else {
                statusEl.textContent = 'ACTIVE ✅';
                statusEl.className = 'balance-positive';
            }
        }
        
        function resetPortfolio() {
            const pocketMoney = parseFloat(document.getElementById('pocketMoney').value) || 1000;
            fixedBalance = pocketMoney;
            martingaleBalance = pocketMoney;
            isPortBlown = false;
            winStreak = 0;
            lossStreak = 0;
            currentMartingaleAmount = parseFloat(document.getElementById('fixedAmount').value) || 10;
            tradeHistory = [];
            
            document.getElementById('balanceDisplay').classList.remove('port-blown');
            updateBalanceDisplay();
            updateTradeTable();
            
            // เปิดใช้งานปุ่มเทรด
            const buttons = document.querySelectorAll('.push-btn, .call-btn');
            buttons.forEach(btn => btn.disabled = false);
            
            showStatus('🔄 Portfolio Reset!', 'equal');
        }
        
        function updateTradeTable() {
            const tbody = document.getElementById('tradeResults');
            tbody.innerHTML = '';
            
            tradeHistory.forEach(trade => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${trade.trade}</td>
                    <td>${trade.type.toUpperCase()}</td>
                    <td class="${trade.result}">${trade.result.toUpperCase()}</td>
                    <td>${trade.fixedAmount.toFixed(2)}</td>
                    <td>${trade.martingaleAmount.toFixed(2)}</td>
                    <td class="${trade.fixedBalance >= 0 ? 'balance-positive' : 'balance-negative'}">${trade.fixedBalance.toFixed(2)}</td>
                    <td class="${trade.martingaleBalance >= 0 ? 'balance-positive' : 'balance-negative'}">${trade.martingaleBalance.toFixed(2)}</td>
                    <td>${trade.winStreak}</td>
                    <td>${trade.lossStreak}</td>
                `;
            });
        }
        
        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = `status ${type}`;
            status.style.display = 'block';
            
            setTimeout(() => {
                status.style.display = 'none';
            }, 3000);
        }
        
        // Initialize WebSocket on page load
        window.addEventListener('load', function() {
            // Set default dates (เปลี่ยนเป็นช่วงเวลาล่าสุด)
            const now = new Date();
            const oneDayAgo = new Date(now.getTime() - 24 * 60 * 60 * 1000);
            
            document.getElementById('endDate').value = now.toISOString().slice(0, 16);
            document.getElementById('startDate').value = oneDayAgo.toISOString().slice(0, 16);
            
            console.log('Page loaded, dates set:', {
                start: document.getElementById('startDate').value,
                end: document.getElementById('endDate').value
            });
            
            // เพิ่มการทดสอบการเชื่อมต่อ
            testConnection();
        });
        
        function testConnection() {
            console.log('Testing connection to Deriv WebSocket...');
            const testWs = new WebSocket('wss://ws.binaryws.com/websockets/v3?app_id=1089');
            
            testWs.onopen = function() {
                console.log('✅ Connection test successful');
                testWs.close();
            };
            
            testWs.onerror = function(error) {
                console.error('❌ Connection test failed:', error);
                showStatus('WebSocket connection unavailable - Mock data will be used', 'equal');
            };
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (chart) {
                chart.applyOptions({
                    width: document.getElementById('chart').clientWidth
                });
            }
        });
    </script>
</body>
</html>

088-948-1651