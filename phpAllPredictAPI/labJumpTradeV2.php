<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Trading Chart - Binary Options Strategy</title>
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
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(248, 249, 250, 0.8);
            border-radius: 15px;
            border: 2px solid rgba(108, 117, 125, 0.2);
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
        }
        
        .control-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input, select, textarea, button {
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
        }
        
        .fetch-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        }
        
        .fetch-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40,167,69,0.4);
        }
        
        .fetch-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .chart-wrapper {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 2px solid rgba(108, 117, 125, 0.1);
        }
        
        #chart {
            height: 500px;
            border-radius: 10px;
        }
        
        .chart-controls {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 2px solid rgba(108, 117, 125, 0.1);
        }
        
        .chart-controls h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .ema-control {
            margin-bottom: 20px;
        }
        
        .ema-control input {
            width: 100%;
        }
        
        .data-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 2px solid rgba(108, 117, 125, 0.1);
        }
        
        .data-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        textarea {
            width: 100%;
            height: 150px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            resize: vertical;
            background: #f8f9fa;
        }
        
        .status {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        
        .status.loading {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #00b894;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #e17055;
        }
        
        .trading-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #007bff;
        }
        
        @media (max-width: 768px) {
            .chart-container {
                grid-template-columns: 1fr;
            }
            
            .controls {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📈 Deriv Trading Chart</h1>
            <p>Binary Options Strategy with EMA Analysis</p>
        </div>
        
        <div id="status" class="status" style="display: none;"></div>
        
        <div class="controls">
            <div class="control-group">
                <label for="startDate">📅 วันที่เริ่มต้น:</label>
                <input type="datetime-local" id="startDate">
            </div>
            
            <div class="control-group">
                <label for="endDate">📅 วันที่สิ้นสุด:</label>
                <input type="datetime-local" id="endDate">
            </div>
            
            <div class="control-group">
                <label for="timeframe">⏱️ Timeframe:</label>
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
                <label for="asset">💰 Asset:</label>
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
            
            <div class="control-group">
                <label for="candleCount">📊 จำนวนแท่งเทียน:</label>
                <input type="number" id="candleCount" value="100" min="50" max="1000">
            </div>
            
            <div class="control-group">
                <label for="minSameColor">🎯 แท่งสีเดียวกันขั้นต่ำ:</label>
                <input type="number" id="minSameColor" value="3" min="2" max="10">
            </div>
        </div>
        
        <button class="fetch-btn" onclick="fetchData()">🚀 ดึงข้อมูลและวิเคราะห์</button>

		<button class="fetch-btn" onclick="BTrade()">🚀 Binary วิเคราะห์</button>

		<button class="fetch-btn" onclick="analyzeCandleArray()">🚀 Alter Color วิเคราะห์</button>

		
        
        <div class="chart-container">
            <div class="chart-wrapper">
                <div id="chart"></div>
            </div>
            
            <div class="chart-controls">
                <h3>⚙️ การตั้งค่า EMA</h3>
                
                <div class="ema-control">
                    <label for="emaShort">EMA สั้น (เส้นสีน้ำเงิน):</label>
                    <input type="number" id="emaShort" value="9" min="2" max="50" onchange="updateChart()">
                </div>
                
                <div class="ema-control">
                    <label for="emaLong">EMA ยาว (เส้นสีแดง):</label>
                    <input type="number" id="emaLong" value="21" min="5" max="100" onchange="updateChart()">
                </div>
                
                <div class="trading-stats">
                    <h4>📊 สถิติการเทรด</h4>
                    <div id="tradingStats"></div>
                </div>
            </div>
        </div>
		<div class="data-section">
            <h3>📋 ข้อมูล Alter Data</h3>
            <div id="alterReport" class="bordergray flex">
                 
            </div>
        </div>
        
        <div class="data-section">
            <h3>📋 ข้อมูล Raw Data</h3>
            <textarea id="rawData" placeholder="ข้อมูลจาก Deriv จะแสดงที่นี่..."></textarea>
        </div>
    </div>

    <script>
        let chart;
        let candlestickSeries;
        let emaShortSeries;
        let emaLongSeries;
        let rawCandleData = [];
        let entryMarkers = [];
        let exitMarkers = [];
		let ColorStreams = [];
		const CANDLE_TYPES = {
            GREEN: 'Green',
            RED: 'Red',
            EQUAL: 'Equal'
        };

        const ACTIONS = {
            CALL: 'CALL',
            PUT: 'PUT',
            WAIT: 'WAIT',
            NO_TRADE: 'NO_TRADE'
        };

        const RISK_LEVELS = {
            LOW: 'ต่ำ',
            MEDIUM: 'ปานกลาง',
            HIGH: 'สูง',
            VERY_HIGH: 'สูงมาก'
        };

        
        // Initialize date inputs with default values
        function initializeDates() {
            const now = new Date();
            const oneHourAgo = new Date(now.getTime() - 60 * 60 * 1000);
            
            document.getElementById('endDate').value = now.toISOString().slice(0, 16);
            document.getElementById('startDate').value = oneHourAgo.toISOString().slice(0, 16);
        }
        
        // Initialize chart
        function initializeChart() {
            const chartContainer = document.getElementById('chart');
            chart = LightweightCharts.createChart(chartContainer, {
                width: chartContainer.offsetWidth,
                height: 500,
                layout: {
                    backgroundColor: '#ffffff',
                    textColor: '#333',
                },
                grid: {
                    vertLines: {
                        color: '#f0f3fa',
                    },
                    horzLines: {
                        color: '#f0f3fa',
                    },
                },
                crosshair: {
                    mode: LightweightCharts.CrosshairMode.Normal,
                },
                rightPriceScale: {
                    borderColor: '#cccccc',
                },
                timeScale: {
                    borderColor: '#cccccc',
                    timeVisible: true,
                    secondsVisible: false,
                },
            });
            
            // Create series
            candlestickSeries = chart.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });
            
            emaShortSeries = chart.addLineSeries({
                color: '#2196f3',
                lineWidth: 2,
                title: 'EMA Short',
            });
            
            emaLongSeries = chart.addLineSeries({
                color: '#f44336',
                lineWidth: 2,
                title: 'EMA Long',
            });
            
            // Handle resize
            window.addEventListener('resize', () => {
                chart.applyOptions({ width: chartContainer.offsetWidth });
            });
        }
        
        // Show status message
        function showStatus(message, type) {
            const statusEl = document.getElementById('status');
            statusEl.textContent = message;
            statusEl.className = `status ${type}`;
            statusEl.style.display = 'block';
            
            if (type === 'success') {
                setTimeout(() => {
                    statusEl.style.display = 'none';
                }, 3000);
            }
        }
        
        // Calculate EMA
        function calculateEMA(data, period) {
            const ema = [];
            const multiplier = 2 / (period + 1);
            
            if (data.length === 0) return ema;
            
            // First EMA value is the first closing price
            ema[0] = { time: data[0].time, value: data[0].close };
            
            // Calculate subsequent EMA values
            for (let i = 1; i < data.length; i++) {
                const emaValue = (data[i].close * multiplier) + (ema[i-1].value * (1 - multiplier));
                ema[i] = { time: data[i].time, value: emaValue };
            }
            
            return ema;
        }
        
        // Detect trading signals based on same color candles
        function detectTradingSignals(data, minSameColor) {
            const signals = [];
            let currentColorCount = 1;
            let currentColor = null;
            let entryPoint = null;
			getColorStreams(data);
            
            for (let i = 1; i < data.length; i++) {
                const prevCandle = data[i-1];
                const currentCandle = data[i];
                
                const prevColor = prevCandle.close > prevCandle.open ? 'green' : 'red';
                const currentCandleColor = currentCandle.close > currentCandle.open ? 'green' : 'red';
                
                if (currentCandleColor === prevColor) {
                    currentColorCount++;
                    currentColor = currentCandleColor;
                    
                    // Entry signal: reached minimum same color candles
                    if (currentColorCount === minSameColor && !entryPoint) {
                        entryPoint = {
                            time: currentCandle.time,
                            price: currentCandle.close,
                            type: currentColor === 'green' ? 'CALL' : 'PUT',
                            index: i
                        };
                    }
                } else {
                    // Color changed - exit signal
                    if (entryPoint && currentColorCount >= minSameColor) {
                        const exitPoint = {
                            time: currentCandle.time,
                            price: currentCandle.close,
                            type: 'EXIT'
                        };
                        
                        signals.push({
                            entry: entryPoint,
                            exit: exitPoint,
                            duration: i - entryPoint.index,
                            result: entryPoint.type === 'CALL' ? 
                                (exitPoint.price > entryPoint.price ? 'WIN' : 'LOSS') :
                                (exitPoint.price < entryPoint.price ? 'WIN' : 'LOSS')
                        });
                    }
                    
                    currentColorCount = 1;
                    currentColor = currentCandleColor;
                    entryPoint = null;
                }
            }
			 console.log('Signals',signals);
			
            
            return signals;
        }
        
        // Create markers for trading signals
        function createMarkers(signals) {
            const markers = [];
            
            signals.forEach(signal => {
                // Entry marker
                markers.push({
                    time: signal.entry.time,
                    position: signal.entry.type === 'CALL' ? 'belowBar' : 'aboveBar',
                    color: signal.entry.type === 'CALL' ? '#26a69a' : '#ef5350',
                    shape: signal.entry.type === 'CALL' ? 'arrowUp' : 'arrowDown',
                    textA: `${signal.entry.type} @ ${signal.entry.price.toFixed(5)}`,
                    text: `${signal.entry.type}`,
                    size: 2
                });
                
                // Exit marker
                markers.push({
                    time: signal.exit.time,
                    position: signal.result === 'WIN' ? 'aboveBar' : 'belowBar',
                    color: signal.result === 'WIN' ? '#4caf50' : '#f44336',
                    shape: 'circle',
                    textA: `${signal.result} @ ${signal.exit.price.toFixed(5)}`,
                    text: `${signal.result} `,
                    size: 1
                });
            });
            
            return markers;
        }
        
        // Update trading statistics
        function updateTradingStats(signals) {
            const statsEl = document.getElementById('tradingStats');
            
            if (signals.length === 0) {
                statsEl.innerHTML = '<p>ไม่พบสัญญาณการเทรด</p>';
                return;
            }
            
            const wins = signals.filter(s => s.result === 'WIN').length;
            const losses = signals.filter(s => s.result === 'LOSS').length;
            const winRate = ((wins / signals.length) * 100).toFixed(1);
            
            statsEl.innerHTML = `
                <p><strong>จำนวนสัญญาณ:</strong> ${signals.length}</p>
                <p><strong>ชนะ:</strong> ${wins} ครั้ง</p>
                <p><strong>แพ้:</strong> ${losses} ครั้ง</p>
                <p><strong>อัตราชนะ:</strong> ${winRate}%</p>
            `;
        }
        
        // Fetch data from Deriv API
        async function fetchData() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const timeframe = document.getElementById('timeframe').value;
            const asset = document.getElementById('asset').value;
            const candleCount = parseInt(document.getElementById('candleCount').value);
			document.getElementById("rawData").value = '';
            
            if (!startDate || !endDate) {
                showStatus('กรุณาเลือกวันที่เริ่มต้นและสิ้นสุด', 'error');
                return;
            }
            
            showStatus('กำลังดึงข้อมูลจาก Deriv...', 'loading');
            
            try {
                // Calculate the number of ticks we need
                const startTime = Math.floor(new Date(startDate).getTime() / 1000);
                const endTime = Math.floor(new Date(endDate).getTime() / 1000);
                
                // Deriv WebSocket API call
                const ws = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');
                
                ws.onopen = function() {
                    const request = {
                        ticks_history: asset,
                        adjust_start_time: 1,
                        count: candleCount,
                        end: "latest",
                        granularity: parseInt(timeframe),
                        style: "candles"
                    };

					const request2 = {
                        ticks_history: asset,
                        adjust_start_time: 1,
						start: 1704067200,   
                        end: 1704153600, 	
                        
                        granularity: parseInt(timeframe),
                        style: "candles"
                    };
                    
                    ws.send(JSON.stringify(request2));
                };
                
                ws.onmessage = function(event) {
                    const response = JSON.parse(event.data);
                    
                    if (response.error) {
                        showStatus(`Error: ${response.error.message}`, 'error');
                        ws.close();
                        return;
                    }
                    
                    if (response.candles) {
                        const candles = response.candles;
                        
                        // Convert to LightweightCharts format
                        rawCandleData = candles.map(candle => ({
                            time: candle.epoch,
                            open: parseFloat(candle.open),
                            high: parseFloat(candle.high),
                            low: parseFloat(candle.low),
                            close: parseFloat(candle.close)
                        }));
                        
                        // Limit to requested number of candles
                        rawCandleData = rawCandleData.slice(-candleCount);
                        
                        // Display raw data
                        document.getElementById('rawData').value = JSON.stringify(response, null, 2);
                        
                        // Update chart
                        updateChart();
                        
                        showStatus(`ดึงข้อมูลสำเร็จ: ${rawCandleData.length} แท่งเทียน`, 'success');
                        ws.close();
                    }
                };
                
                ws.onerror = function(error) {
                    showStatus('เกิดข้อผิดพลาดในการเชื่อมต่อ Deriv API', 'error');
                    console.error('WebSocket error:', error);
                };
                
            } catch (error) {
                showStatus(`เกิดข้อผิดพลาด: ${error.message}`, 'error');
                console.error('Fetch error:', error);
            }
        }

		function getColorStreams(data) {
			     
				  console.log(data)
				  console.log('Color Streams')
				 
                 lastIndex = data.length-1 ;

				 ColorStreams = [] ;
				 GreenScore = 0 ; RedScore = 0 ; EqualScore = 0;
				 GreenScore2 = 0 ; RedScore2 = 0 ; EqualScore2 = 0;
				 for (let i=lastIndex-10;i<=lastIndex ;i++ ) {
                    
                    let diff = parseFloat(data[i].close) - parseFloat(data[i].open);
					Color = '??'; 
					if (diff < 0.0 ) { Color = 'Red' ; RedScore++ ; }
					if (diff === 0.0 ) { Color = 'Equal'  ;  EqualScore++ ; }
				    if (diff > 0.0 ) { Color = 'Green' ; GreenScore++ }					
					if (i > lastIndex-3) {
					   if (Color==='Red')   { RedScore2++; }
					   if (Color==='E') { EqualScore2++; }
                       if (Color==='Green') { GreenScore2++; }						
					}
					
					ColorStreams.push(Color);				    
				 }

				 lastindex = ColorStreams.length-1 ;
				 if (ColorStreams[lastindex-1] !== ColorStreams[lastindex] ) {
					 action = 'Idle';
				 }
				 if (ColorStreams[lastindex-1] == ColorStreams[lastindex] ) {
					 if (ColorStreams[lastindex] == 'Red') {
						 action = 'PUT';
					 }
					 if (ColorStreams[lastindex] == 'Green') {
						 action = 'CALL';
					 }
					 if (ColorStreams[lastindex] == 'Equal') {
						 action = 'CALL';
					 }					 
				 }
				 


				 console.log(ColorStreams);
				 console.log(GreenScore,'-',RedScore,'-',EqualScore);
				 console.log(GreenScore2,'-',RedScore2,'-',EqualScore2);
				 console.log('action',action)
				 return ColorStreams;
				 



				 
            
			//ColorStreams[]
		
		
		} // end func

		function getColorStreamsAll(data) {
			     
				 //console.log(data)
				 //console.log('Color Streams All')
				 
                 lastIndex = data.length-1 ;
				 
				 let ColorStreams = [] ;
				 GreenScore = 0 ; RedScore = 0 ; EqualScore = 0;
				 GreenScore2 = 0 ; RedScore2 = 0 ; EqualScore2 = 0;
				 for (let i=0;i<=lastIndex ;i++ ) {
                    
                    let diff = parseFloat(data[i].close) - parseFloat(data[i].open);
					Color = '??'; 
					if (diff < 0.0 ) { Color = 'Red' ; RedScore++ ; }
					if (diff === 0.0 ) { Color = 'Equal'  ;  EqualScore++ ; }
				    if (diff > 0.0 ) { Color = 'Green' ; GreenScore++ }					
					if (i > lastIndex-3) {
					   if (Color==='Red')   { RedScore2++; }
					   if (Color==='E') { EqualScore2++; }
                       if (Color==='Green') { GreenScore2++; }						
					}
					data[i].Color = Color 
					ColorStreams.push(Color);				    
				 }

				 lastindex = ColorStreams.length-1 ;
				 if (ColorStreams[lastindex-1] !== ColorStreams[lastindex] ) {
					 action = 'Idle';
				 }
				 if (ColorStreams[lastindex-1] == ColorStreams[lastindex] ) {
					 if (ColorStreams[lastindex] == 'Red') {
						 action = 'PUT';
					 }
					 if (ColorStreams[lastindex] == 'Green') {
						 action = 'CALL';
					 }
					 if (ColorStreams[lastindex] == 'Equal') {
						 action = 'CALL';
					 }					 
				 }
				 
                 //console.log(' Data After',data)
                  

				 //console.log(ColorStreams);
				 //console.log(GreenScore,'-',RedScore,'-',EqualScore);
				 //console.log(GreenScore2,'-',RedScore2,'-',EqualScore2);
				 //console.log('action',action)
				 //return ColorStreams;
				 return data ;
				 



				 
            
			//ColorStreams[]
		
		
		} // end func
		
		function BTrade() {
 
              
              let rawData = document.getElementById("rawData").value;
			  let rawDataList = JSON.parse(rawData);
			  data = getColorStreamsAll(rawCandleData);
			  //console.log(rawDataList.candles.length);
			  console.clear()
			  
			 // console.log('Before BTrade',data);
			  WinCon = 0 ; LossCon = 0 ;
			  for (let i=1;i<=data.length-2 ;i++ ) {
				  //console.log(data[i].Color)
				  
				  let candle2 = data[i-1].Color;
				  let candle1 = data[i].Color;
                  Analysis = getBinaryOptionsDecision(candle2, candle1);			 
				  data[i].Analysis  = Analysis ;				  
				  data[i].Action  = Analysis.action ;				  
				  if (Analysis.action ==='PUT' ) {
                     data[i].SuggestColor = 'Red';
				  }
				  if (Analysis.action ==='CALL' ) {
                     data[i].SuggestColor = 'Green';
				  }
				  if (Analysis.action ==='NO-TRADE' ) {
                     data[i].SuggestColor = '';
				  }
				  data[i].NextColor = data[i+1].Color ;
                  if (Analysis.action != 'NO-TRADE' ) {
				    if (data[i].NextColor === data[i].SuggestColor) {
					    data[i].WinStatus = 'Win';
						WinCon++ ; LossCon = 0 ;
				    } else {
						data[i].WinStatus = 'Loss';
						WinCon = 0 ; LossCon++;
					}
                  } else {
                      data[i].WinStatus = 'N';
				  }
				  data[i].WinCon = WinCon ;
				  data[i].LossCon = LossCon ;


			  }


			  console.log('Data After',data) ;
              markers = [] ;
              for (let i=1;i<=data.length-2 ;i++ ) {
                 //console.log(i,data[i].Analysis.action);
				 LossCon = data[i].LossCon ;
                
                if (data[i].Analysis.action) {
				  if (data[i].WinStatus ==='Loss') {
                     console.log(data[i].Analysis.action )
					 markers.push({
                       time: data[i].time,
                       position:  'aboveBar' ,
                       color:  '#4caf50' ,
                       shape: 'circle',                     
                       text: 'L-' +LossCon ,
                       size: 1
                      }); 
				  }
				}
              
              } 
			  candlestickSeries.setMarkers(markers);
              
              
               /*
              
              markers.push({
                    time: signal.exit.time,
                    position: signal.result === 'WIN' ? 'aboveBar' : 'belowBar',
                    color: signal.result === 'WIN' ? '#4caf50' : '#f44336',
                    shape: 'circle',
                    textA: `${signal.result} @ ${signal.exit.price.toFixed(5)}`,
                    text: `${signal.result} `,
                    size: 1
              }); 

              
			  */
			   
			  
			  
			  
		
		
		} // end func

		function getBinaryOptionsDecision(candle2, candle1) {
            const pattern = `${candle2}_${candle1}`;
            // console.log('pattern',pattern)
            
            switch (pattern) {
                case `${CANDLE_TYPES.GREEN}_${CANDLE_TYPES.GREEN}`:
                    return {
                        action: ACTIONS.CALL,
                        pattern: 'Bullish Continuation',
                        risk: RISK_LEVELS.MEDIUM,
                        confidence: 'สูง',
                        reason: 'เทรนด์ขาขึ้นต่อเนื่อง',
                        emoji: '🟢🟢'
                    };

                case `${CANDLE_TYPES.RED}_${CANDLE_TYPES.RED}`:
                    return {
                        action: ACTIONS.PUT,
                        pattern: 'Bearish Continuation',
                        risk: RISK_LEVELS.MEDIUM,
                        confidence: 'สูง',
                        reason: 'เทรนด์ขาลงต่อเนื่อง',
                        emoji: '🔴🔴'
                    };

                case `${CANDLE_TYPES.GREEN}_${CANDLE_TYPES.RED}`:
                    return {
                        action: ACTIONS.NO_TRADE,
                        pattern: 'Bullish Reversal',
                        risk: RISK_LEVELS.HIGH,
                        confidence: 'ปานกลาง',
                        reason: 'สัญญาณการพลิกกลับจากขาขึ้นเป็นขาลง',
                        emoji: '🟢🔴'
                    };

                case `${CANDLE_TYPES.RED}_${CANDLE_TYPES.GREEN}`:
                    return {
                        action: ACTIONS.NO_TRADE,
                        pattern: 'Bearish Reversal',
                        risk: RISK_LEVELS.HIGH,
                        confidence: 'ปานกลาง',
                        reason: 'สัญญาณการพลิกกลับจากขาลงเป็นขาขึ้น',
                        emoji: '🔴🟢'
                    };

                case `${CANDLE_TYPES.GREEN}_${CANDLE_TYPES.EQUAL}`:
                    return {
                        action: ACTIONS.WAIT,
                        pattern: 'Bullish Pause',
                        risk: RISK_LEVELS.VERY_HIGH,
                        confidence: 'ต่ำ',
                        reason: 'ตลาดหยุดชะงักหลังเทรนด์ขาขึ้น',
                        emoji: '🟢⚪'
                    };

                case `${CANDLE_TYPES.RED}_${CANDLE_TYPES.EQUAL}`:
                    return {
                        action: ACTIONS.WAIT,
                        pattern: 'Bearish Pause',
                        risk: RISK_LEVELS.VERY_HIGH,
                        confidence: 'ต่ำ',
                        reason: 'ตลาดหยุดชะงักหลังเทรนด์ขาลง',
                        emoji: '🔴⚪'
                    };

                case `${CANDLE_TYPES.EQUAL}_${CANDLE_TYPES.GREEN}`:
                    return {
                        action: ACTIONS.CALL,
                        pattern: 'Doji to Bullish',
                        risk: RISK_LEVELS.HIGH,
                        confidence: 'ปานกลาง',
                        reason: 'ตลาดเริ่มมีทิศทางขาขึ้น',
                        emoji: '⚪🟢'
                    };

                case `${CANDLE_TYPES.EQUAL}_${CANDLE_TYPES.RED}`:
                    return {
                        action: ACTIONS.PUT,
                        pattern: 'Doji to Bearish',
                        risk: RISK_LEVELS.HIGH,
                        confidence: 'ปานกลาง',
                        reason: 'ตลาดเริ่มมีทิศทางขาลง',
                        emoji: '⚪🔴'
                    };

                case `${CANDLE_TYPES.EQUAL}_${CANDLE_TYPES.EQUAL}`:
                    return {
                        action: ACTIONS.NO_TRADE,
                        pattern: 'Double Doji',
                        risk: RISK_LEVELS.VERY_HIGH,
                        confidence: 'ไม่มี',
                        reason: 'ตลาดไม่มีทิศทาง',
                        emoji: '⚪⚪'
                    };

                default:
                    throw new Error('Unknown pattern');
            }

        } 

//*******************************

function generateLossConTable(analysisResult) {
  // ตรวจสอบข้อมูล
  if (!analysisResult || !analysisResult.lossConGreaterThan4 || analysisResult.lossConGreaterThan4.length === 0) {
    return '<p>ไม่พบช่วงเวลาที่ LossCon มากกว่า 4</p>';
  }

  let html = `
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
      <thead>
        <tr style="background-color: #f2f2f2;">
          <th style="text-align: center;">ลำดับ</th>
          <th style="text-align: center;">เวลาเริ่มต้น</th>
          <th style="text-align: center;">เวลาสิ้นสุด</th>
          <th style="text-align: center;">Index เริ่มต้น</th>
          <th style="text-align: center;">Index สิ้นสุด</th>
          <th style="text-align: center;">จำนวน LossCon</th>
        </tr>
      </thead>
      <tbody>
  `;

  analysisResult.lossConGreaterThan4.forEach((period, index) => {
    const rowColor = index % 2 === 0 ? '#ffffff' : '#f9f9f9';
    html += `
        <tr style="background-color: ${rowColor};">
          <td style="text-align: center;">${index + 1}</td>
          <td style="text-align: center;">${period.startTime}</td>
          <td style="text-align: center;">${period.endTime}</td>
          <td style="text-align: center;">${period.startIndex}</td>
          <td style="text-align: center;">${period.endIndex}</td>
          <td style="text-align: center; font-weight: bold; color: #d9534f;">${period.lossCon}</td>
        </tr>
    `;
  });

  html += `
      </tbody>
      <tfoot>
        <tr style="background-color: #e9ecef; font-weight: bold;">
          <td colspan="5" style="text-align: right; padding-right: 10px;">รวมทั้งหมด:</td>
          <td style="text-align: center;">${analysisResult.lossConGreaterThan4.length} ช่วง</td>
        </tr>
      </tfoot>
    </table>
  `;

  return html;
}

 function analyzeCandleArray() {

  candleA = JSON.parse(document.getElementById("rawData").value) ;
  candles = candleA.candles;
  // ตรวจสอบข้อมูลเบื้องต้น
  if (!candles || candles.length < 2) {
    return {
      results: [],
      lossConGreaterThan4: []
    };
  }

  const results = [];
  let winCon = 0;
  let lossCon = 0;

  for (let i = 0; i < candles.length; i++) {
    const currentCandle = candles[i];
    
    // 1. หา Color ของแต่ละแท่งเทียน
    const currentColor = currentCandle.close >= currentCandle.open ? 'Green' : 'Red';
    
    let status = null;
    let nextColor = null;

    // ตรวจสอบแท่งถัดไป (n+1)
    if (i < candles.length - 1) {
      const nextCandle = candles[i + 1];
      nextColor = nextCandle.close >= nextCandle.open ? 'Green' : 'Red';

      // 2. ถ้า Color ของแท่ง n = Color ของแท่ง n+1
      if (currentColor === nextColor) {
        status = 'Win';
        winCon++;
        lossCon = 0;
      } 
      // 3. ถ้า Color ของแท่ง n <> Color ของแท่ง n+1
      else {
        status = 'Loss';
        lossCon++;
        winCon = 0;
      }
    }

    results.push({
      index: i,
      time: currentCandle.time || currentCandle.timestamp || i,
      open: currentCandle.open,
      high: currentCandle.high,
      low: currentCandle.low,
      close: currentCandle.close,
      color: currentColor,
      nextColor: nextColor,
      status: status,
      winCon: winCon,
      lossCon: lossCon
    });
  }

  // 4. หา LossCon ที่มากกว่า 4 โดยสแกนจาก results
  const lossConGreaterThan4 = [];
  let inLossPeriod = false;
  let periodStart = -1;

  for (let i = 0; i < results.length; i++) {
    const current = results[i];
    
    if (current.lossCon > 4 && !inLossPeriod) {
      // เริ่มต้นช่วงใหม่
      inLossPeriod = true;
      periodStart = i - current.lossCon + 1;
    }
    
    if (inLossPeriod && (current.lossCon === 0 || i === results.length - 1)) {
      // สิ้นสุดช่วง
      const endIndex = current.lossCon === 0 ? i - 1 : i;
      const maxLossCon = results[endIndex].lossCon;
      
      if (maxLossCon > 4) {
        lossConGreaterThan4.push({
          startIndex: periodStart,
          endIndex: endIndex,
          startTime: results[periodStart].time,
          endTime: results[endIndex].time,
          lossCon: maxLossCon
        });
      }
      
      inLossPeriod = false;
      periodStart = -1;
    }
  }

  resultAlter= {
    results: results,
    lossConGreaterThan4: lossConGreaterThan4,
    summary: {
      totalCandles: candles.length,
      totalLossConGreaterThan4Periods: lossConGreaterThan4.length
    }
  };

   html = generateLossConTable(resultAlter) ;
   console.log('html',html)

   document.getElementById("alterReport").innerHTML = html;
   
   
  
}

//*****************************************




        
        // Update chart with current data and settings
        function updateChart() {
            if (rawCandleData.length === 0) return;
            
            const emaShortPeriod = parseInt(document.getElementById('emaShort').value);
            const emaLongPeriod = parseInt(document.getElementById('emaLong').value);
            const minSameColor = parseInt(document.getElementById('minSameColor').value);
            
            // Set candlestick data
            candlestickSeries.setData(rawCandleData);
            
            // Calculate and set EMA data
            const emaShortData = calculateEMA(rawCandleData, emaShortPeriod);
            const emaLongData = calculateEMA(rawCandleData, emaLongPeriod);
            
            emaShortSeries.setData(emaShortData);
            emaLongSeries.setData(emaLongData);
            
            // Detect trading signals
            const signals = detectTradingSignals(rawCandleData, minSameColor);
            
            // Create and set markers
            const markers = createMarkers(signals);
            candlestickSeries.setMarkers(markers);
            
            // Update trading statistics
            updateTradingStats(signals);
            
            // Fit chart to data
            chart.timeScale().fitContent();
        }
        
        // Initialize when page loads
        window.addEventListener('load', function() {
            initializeDates();
            initializeChart();
        });
    </script>
</body>
</html>

{
  "candles": [
    {
      "close": 125.9261,
      "epoch": 1759187520,
      "high": 126.0283,
      "low": 125.9261,
      "open": 126.0157
    },
    {
      "close": 125.8886,
      "epoch": 1759187580,
      "high": 125.922,
      "low": 125.8147,
      "open": 125.922
    }
 ]
}

จากข้อมูล Candle Array ให้ทำการ สรุปรายการดังนี้ ด้วย pure javascript
  1. หา Color ของแต่ละแท่งเทียน
  2. ถ้า Color ของแท่ง n = Color ของแท่ง n+1 ให้ status = 'Win' และให้ Set ค่า WinCon=WinCon+1;LossCon=0
  3. ถ้า Color ของแท่ง n <> Color ของแท่ง n+1 ให้ status = 'Loss' และให้ Set ค่า LossCon=LossCon+1;WinCon=0
  4. สรุป LossCon ที่มากกว่า 4 พร้อมช่วงเวลาที่เกิด  
สร้างเป็น function อย่างเดียว ไม่่ต้องทำทั้งเวบ