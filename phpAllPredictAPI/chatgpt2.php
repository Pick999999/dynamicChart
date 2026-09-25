

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading Signal Analyzer</title>
	<script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
        }
        
        .control-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #e8f4f8;
        }
        
        .control-group input, .control-group select {
            padding: 10px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 14px;
        }
        
        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        #chart {
            height: 600px;
            border-radius: 10px;
        }
        
        .data-input {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        
        .data-input textarea {
            width: 100%;
            height: 120px;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            resize: vertical;
        }
        
        .results {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .result-panel {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .result-panel h3 {
            margin-bottom: 20px;
            color: #4fc3f7;
            border-bottom: 2px solid #4fc3f7;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            color: #333;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }
        
        .profit { color: #4caf50; font-weight: bold; }
        .loss { color: #f44336; font-weight: bold; }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 5px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            opacity: 0.7;
        }
        
        @media (max-width: 768px) {
            .results {
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
            <h1>🔥 Trading Signal Analyzer</h1>
            <p>Advanced Technical Analysis with Lightweight Charts</p>
        </div>
        
        <div class="controls">
            <div class="control-group">
                <label>Fast MA Period:</label>
                <input type="number" id="maFastPeriod" value="1" min="1" max="100">
            </div>
            <div class="control-group">
                <label>Slow MA Period:</label>
                <input type="number" id="maSlowPeriod" value="34" min="1" max="200">
            </div>
            <div class="control-group">
                <label>Signal Period:</label>
                <input type="number" id="signalPeriod" value="5" min="1" max="50">
            </div>
            <div class="control-group">
                <label>MA Value:</label>
                <select id="maValue">
                    <option value="close">Close</option>
                    <option value="open">Open</option>
                    <option value="high">High</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="control-group">
                <label>Initial Trade Amount:</label>
                <input type="number" id="tradeAmount" value="10" min="1" step="1">
            </div>
            <div class="control-group">
                <label>Martingale Multiplier:</label>
                <input type="number" id="martingaleMultiplier" value="2.2" min="1" step="0.1">
            </div>
        </div>
        
        <div class="data-input">
            <label><strong>Candle Data (JSON format):</strong></label>
            <textarea id="candleData" placeholder='[{"time": "2023-01-01", "open": 100, "high": 102, "low": 99, "close": 101}, ...]'></textarea>
            <button onclick="loadSampleData()">Load Sample Data</button>
            <button onclick="analyzeData()">Analyze</button>
        </div>
        
        <div class="chart-container">
            <div id="chart"></div>
        </div>
        
        <div class="results">
            <div class="result-panel">
                <h3>📊 Trading Statistics</h3>
                <div class="summary-stats" id="summaryStats"></div>
                <div id="tradingTable"></div>
            </div>
            
            <div class="result-panel">
                <h3>🎯 Latest Signals</h3>
                <div id="latestSignals"></div>
            </div>
        </div>
    </div>

    <script>
        // Trading analysis functions (converted from Lua)
		let currentAmount = 0 ;
        function sma(data, period) {
            const result = [];
            for (let i = 0; i < data.length; i++) {
                if (i < period - 1) {
                    result.push(null);
                } else {
                    let sum = 0;
                    for (let j = i - period + 1; j <= i; j++) {
                        sum += data[j];
                    }
                    result.push(sum / period);
                }
            }
            return result;
        }

        function wma(data, period) {
            const result = [];
            for (let i = 0; i < data.length; i++) {
                if (i < period - 1) {
                    result.push(null);
                } else {
                    let weightedSum = 0;
                    let weightSum = 0;
                    for (let j = 0; j < period; j++) {
                        const weight = period - j;
                        weightedSum += data[i - j] * weight;
                        weightSum += weight;
                    }
                    result.push(weightedSum / weightSum);
                }
            }
            return result;
        }

        function analyzeTrading(priceData, config = {}) {
            const {
                maFastPeriod = 1,
                maValue = 'close',
                maSlowPeriod = 34,
                signalPeriod = 5
            } = config;

            const { open, high, low, close } = priceData;
            const dataLength = close.length;

            let titleValue;
            switch (maValue.toLowerCase()) {
                case 'open': titleValue = open; break;
                case 'high': titleValue = high; break;
                case 'low': titleValue = low; break;
                case 'close':
                default: titleValue = close; break;
            }

            const smaFast = sma(titleValue, maFastPeriod);
            const smaSlow = sma(titleValue, maSlowPeriod);

            const buffer1 = [];
            for (let i = 0; i < dataLength; i++) {
                if (smaFast[i] !== null && smaSlow[i] !== null) {
                    buffer1.push(smaFast[i] - smaSlow[i]);
                } else {
                    buffer1.push(null);
                }
            }

            const validBuffer1 = buffer1.map(val => val !== null ? val : 0);
            const wmaBuffer = wma(validBuffer1, signalPeriod);
            const buffer2 = [];
            for (let i = 0; i < dataLength; i++) {
                buffer2.push(buffer1[i] !== null ? wmaBuffer[i] : null);
            }

            const buySignals = [false];
            const sellSignals = [false];

            for (let i = 1; i < dataLength; i++) {
                const currentBuffer1 = buffer1[i];
                const currentBuffer2 = buffer2[i];
                const prevBuffer1 = buffer1[i - 1];
                const prevBuffer2 = buffer2[i - 1];

                const buyCondition = currentBuffer1 !== null && currentBuffer2 !== null &&
                                   prevBuffer1 !== null && prevBuffer2 !== null &&
                                   currentBuffer1 > currentBuffer2 && prevBuffer1 < prevBuffer2;

                const sellCondition = currentBuffer1 !== null && currentBuffer2 !== null &&
                                    prevBuffer1 !== null && prevBuffer2 !== null &&
                                    currentBuffer1 < currentBuffer2 && prevBuffer1 > prevBuffer2;

                buySignals.push(buyCondition);
                sellSignals.push(sellCondition);
            }

            const bearishEngulfing = [false];
            const bullishEngulfing = [false];

            for (let i = 1; i < dataLength; i++) {
                const prevOpen = open[i - 1];
                const prevClose = close[i - 1];
                const currOpen = open[i];
                const currClose = close[i];

                const bearish = (prevClose > prevOpen) &&
                               (currOpen > currClose) &&
                               (currOpen >= prevClose) &&
                               (prevOpen >= currClose) &&
                               (currOpen - currClose > prevClose - prevOpen);

                const bullish = (prevOpen > prevClose) &&
                               (currClose > currOpen) &&
                               (currClose >= prevOpen) &&
                               (prevClose >= currOpen) &&
                               (currClose - currOpen > prevOpen - prevClose);

                bearishEngulfing.push(bearish);
                bullishEngulfing.push(bullish);
            }

            return {
                indicators: { smaFast, smaSlow, buffer1, buffer2 },
                signals: { buy: buySignals, sell: sellSignals, bearishEngulfing, bullishEngulfing }
            };
        }

        // Chart variables
        let chart;
        let candlestickSeries;
        let smaFastSeries;
        let smaSlowSeries;
        let buffer1Series;
        let buffer2Series;
        let currentData = [];
		let consecutiveLosses = 0;
            let totalProfit = 0;
            let maxDrawdown = 0;
            let peak = 0;

        // Initialize chart
        function initChart() {
            const chartContainer = document.getElementById('chart');
            chart = LightweightCharts.createChart(chartContainer, {
                width: chartContainer.clientWidth,
                height: 600,
                layout: {
                    background: { type: 'solid', color: '#1a1a1a' },
                    textColor: 'white',
                },
                grid: {
                    vertLines: { color: 'rgba(70, 130, 180, 0.2)' },
                    horzLines: { color: 'rgba(70, 130, 180, 0.2)' },
                },
                crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                rightPriceScale: { borderColor: 'rgba(197, 203, 206, 0.4)' },
                timeScale: { borderColor: 'rgba(197, 203, 206, 0.4)' },
            });

            candlestickSeries = chart.addCandlestickSeries({
                upColor: '#4caf50',
                downColor: '#f44336',
                borderDownColor: '#f44336',
                borderUpColor: '#4caf50',
                wickDownColor: '#f44336',
                wickUpColor: '#4caf50',
            });

            smaFastSeries = chart.addLineSeries({
                color: '#2196f3',
                lineWidth: 2,
                title: 'Fast MA'
            });

            smaSlowSeries = chart.addLineSeries({
                color: '#ff9800',
                lineWidth: 2,
                title: 'Slow MA'
            });

            buffer1Series = chart.addLineSeries({
                color: '#9c27b0',
                lineWidth: 1,
                title: 'Buffer 1'
            });

            buffer2Series = chart.addLineSeries({
                color: '#e91e63',
                lineWidth: 1,
                title: 'Buffer 2'
            });

            window.addEventListener('resize', () => {
                chart.applyOptions({ width: chartContainer.clientWidth });
            });
        }

        function loadSampleData() {
            const sampleData = [];
            const basePrice = 1000;
            const startDate = new Date('2023-01-01');
            
            for (let i = 0; i < 100; i++) {
                const date = new Date(startDate);
                date.setDate(date.getDate() + i);
                
                const trend = Math.sin(i * 0.1) * 50;
                const noise = (Math.random() - 0.5) * 20;
                const open = basePrice + trend + noise;
                const close = open + (Math.random() - 0.5) * 30;
                const high = Math.max(open, close) + Math.random() * 15;
                const low = Math.min(open, close) - Math.random() * 15;
                
                sampleData.push({
                    time: date.toISOString().split('T')[0],
                    open: Math.round(open * 100) / 100,
                    high: Math.round(high * 100) / 100,
                    low: Math.round(low * 100) / 100,
                    close: Math.round(close * 100) / 100
                });
            }
            
            document.getElementById('candleData').value = JSON.stringify(sampleData, null, 2);
        }

        function analyzeData() {
            try {
                const rawData = JSON.parse(document.getElementById('candleData').value);
                currentData = rawData;

                const priceData = {
                    open: rawData.map(d => d.open),
                    high: rawData.map(d => d.high),
                    low: rawData.map(d => d.low),
                    close: rawData.map(d => d.close)
                };

                const config = {
                    maFastPeriod: parseInt(document.getElementById('maFastPeriod').value),
                    maValue: document.getElementById('maValue').value,
                    maSlowPeriod: parseInt(document.getElementById('maSlowPeriod').value),
                    signalPeriod: parseInt(document.getElementById('signalPeriod').value)
                };

                const analysis = analyzeTrading(priceData, config);
				console.log('Analysis ',analysis)
				
                
                updateChart(rawData, analysis);
                updateResults(rawData, analysis);

            } catch (error) {
                alert('Error parsing data: ' + error.message);
            }
        }

        function updateChart(rawData, analysis) {
            // Clear existing data
            candlestickSeries.setData([]);
            smaFastSeries.setData([]);
            smaSlowSeries.setData([]);
            buffer1Series.setData([]);
            buffer2Series.setData([]);

            // Set candlestick data
            const candleData = rawData.map(d => ({
                time: d.time,
                open: d.open,
                high: d.high,
                low: d.low,
                close: d.close
            }));
            candlestickSeries.setData(candleData);

            // Set MA data
            const timeData = rawData.map(d => d.time);
            
            const smaFastData = analysis.indicators.smaFast
                .map((value, index) => value !== null ? { time: timeData[index], value } : null)
                .filter(item => item !== null);
            smaFastSeries.setData(smaFastData);

            const smaSlowData = analysis.indicators.smaSlow
                .map((value, index) => value !== null ? { time: timeData[index], value } : null)
                .filter(item => item !== null);
            smaSlowSeries.setData(smaSlowData);


            // Add markers for signals
            const markers = [];
            for (let i = 0; i < rawData.length; i++) {
                if (analysis.signals.buy[i]) {
                    markers.push({
                        time: rawData[i].time,
                        position: 'belowBar',
                        color: '#4caf50',
                        shape: 'arrowUp',
                        text: 'BUY',
                        size: 2
                    });
                }
                if (analysis.signals.sell[i]) {
                    markers.push({
                        time: rawData[i].time,
                        position: 'aboveBar',
                        color: '#f44336',
                        shape: 'arrowDown',
                        text: 'SELL',
                        size: 2
                    });
                }
                if (analysis.signals.bullishEngulfing[i]) {
                    markers.push({
                        time: rawData[i].time,
                        position: 'belowBar',
                        color: '#00e676',
                        shape: 'circle',
                        text: 'BE',
                        size: 1
                    });
                }
                if (analysis.signals.bearishEngulfing[i]) {
                    markers.push({
                        time: rawData[i].time,
                        position: 'aboveBar',
                        color: '#ff1744',
                        shape: 'circle',
                        text: 'BE',
                        size: 1
                    });
                }
            }
            candlestickSeries.setMarkers(markers);

            chart.timeScale().fitContent();
        }

        function analyzeLossStreaks(trades) {
            const lossStreaks = [];
            let currentStreak = [];
            let streakStartIndex = -1;
            
            for (let i = 0; i < trades.length; i++) {
                const trade = trades[i];
                
                if (!trade.isWin) {
                    // Start new streak or continue existing
                    if (currentStreak.length === 0) {
                        streakStartIndex = i;
                    }
                    currentStreak.push(trade);
                } else {
                    // End current streak if it exists and is > 5
                    if (currentStreak.length > 5) {
                        const streakEndIndex = i - 1;
                        const totalLoss = currentStreak.reduce((sum, t) => sum + Math.abs(parseFloat(t.profit)), 0);
                        const maxAmount = Math.max(...currentStreak.map(t => parseFloat(t.amount)));
                        
                        lossStreaks.push({
                            startIndex: streakStartIndex,
                            endIndex: streakEndIndex,
                            startTime: currentStreak[0].time,
                            endTime: currentStreak[currentStreak.length - 1].time,
                            streakLength: currentStreak.length,
                            totalLoss: totalLoss,
                            maxAmount: maxAmount,
                            trades: [...currentStreak]
                        });
                    }
                    currentStreak = [];
                    streakStartIndex = -1;
                }
            }
            
            // Check if we ended with a long streak
            if (currentStreak.length > 5) {
                const totalLoss = currentStreak.reduce((sum, t) => sum + Math.abs(parseFloat(t.profit)), 0);
                const maxAmount = Math.max(...currentStreak.map(t => parseFloat(t.amount)));
                
                lossStreaks.push({
                    startIndex: streakStartIndex,
                    endIndex: trades.length - 1,
                    startTime: currentStreak[0].time,
                    endTime: currentStreak[currentStreak.length - 1].time,
                    streakLength: currentStreak.length,
                    totalLoss: totalLoss,
                    maxAmount: maxAmount,
                    trades: [...currentStreak]
                });
            }
            
            return lossStreaks;
        }
        
        function calculateTradingResults(rawData, analysis) {
            const tradeAmount = parseFloat(document.getElementById('tradeAmount').value);
            const martingaleMultiplier = parseFloat(document.getElementById('martingaleMultiplier').value);
            
            const trades = [];
            currentAmount = tradeAmount;
            consecutiveLosses = 0;
            totalProfit = 0;
            maxDrawdown = 0;
            peak = 0;
            
            for (let i = 1; i < rawData.length - 1; i++) {
                let signal = null;
                let signalType = '';
                
                // Priority: MA signals over Engulfing patterns
                if (analysis.signals.buy[i]) {
                    signal = 'BUY';
                    signalType = 'MA Cross';
                } else if (analysis.signals.sell[i]) {
                    signal = 'SELL';
                    signalType = 'MA Cross';
                } else if (analysis.signals.bullishEngulfing[i]) {
                    signal = 'BUY';
                    signalType = 'Bullish Engulfing';
                } else if (analysis.signals.bearishEngulfing[i]) {
                    signal = 'SELL';
                    signalType = 'Bearish Engulfing';
                }
                
                if (signal) {
                    const entryPrice = rawData[i].close;
                    const exitPrice = rawData[i + 1].close;
                    const priceChange = ((exitPrice - entryPrice) / entryPrice * 100);
                    
                    let isWin = false;
                    
                    // Binary option logic: predict direction correctly
                    if (signal === 'BUY') {
                        isWin = exitPrice > entryPrice;
                    } else {
                        isWin = exitPrice < entryPrice;
                    }
                    
                    // Binary option payout: 80% profit on win, 100% loss on lose
                    const profit = isWin ? currentAmount * 0.94 : -currentAmount;
                    totalProfit += profit;
                    
                    // Track max drawdown
                    if (totalProfit > peak) {
                        peak = totalProfit;
                    }
                    const currentDrawdown = peak - totalProfit;
                    if (currentDrawdown > maxDrawdown) {
                        maxDrawdown = currentDrawdown;
                    }
                    
                    trades.push({
                        index: i,
                        time: rawData[i].time,
                        signal: signal,
                        type: signalType,
                        entryPrice: entryPrice.toFixed(4),
                        exitPrice: exitPrice.toFixed(4),
                        priceChange: priceChange.toFixed(2) + '%',
                        amount: currentAmount.toFixed(2),
                        profit: profit.toFixed(2),
                        isWin: isWin,
                        totalProfit: totalProfit.toFixed(2),
                        consecutiveLosses: consecutiveLosses
                    });
                    
                    // Martingale logic
                    if (isWin) {
                        consecutiveLosses = 0;
                        currentAmount = tradeAmount; // Reset to base amount
                    } else {
                        consecutiveLosses++;
                        // Prevent exponential growth beyond reasonable limits
                        const maxMultiplier = Math.min(Math.pow(martingaleMultiplier, consecutiveLosses), 64);
                        currentAmount = tradeAmount * maxMultiplier;
                    }
                }
            }
            console.log('Trades Results',trades);
            
            return {
                trades: trades,
                stats: {
                    totalTrades: trades.length,
                    winTrades: trades.filter(t => t.isWin).length,
                    lossTrades: trades.filter(t => !t.isWin).length,
                    winRate: trades.length > 0 ? (trades.filter(t => t.isWin).length / trades.length * 100) : 0,
                    totalProfit: totalProfit,
                    maxDrawdown: maxDrawdown,
                    profitFactor: this.calculateProfitFactor(trades)
                },
                lossStreaks: analyzeLossStreaks(trades)
            };
        }
        
        function calculateProfitFactor(trades) {
            const totalWins = trades.filter(t => t.isWin).reduce((sum, t) => sum + parseFloat(t.profit), 0);
            const totalLosses = Math.abs(trades.filter(t => !t.isWin).reduce((sum, t) => sum + parseFloat(t.profit), 0));
            return totalLosses > 0 ? (totalWins / totalLosses) : 0;
        }

        function updateResults(rawData, analysis) {
            const tradingResults = calculateTradingResults(rawData, analysis);
            const { trades, stats } = tradingResults;			
            console.log('stats',stats)

            maxLossCon = 0 ; lossCon = 0;
            for (let i=0;i<=trades.length-1 ;i++ ) {
				
                if (trades[i].isWin === false) {
					lossCon = lossCon+1 ;					
					if (maxLossCon < lossCon) {
						maxLossCon = lossCon ;
					}					
                } else {
					lossCon = 0 ;
				}
            }
			console.log(maxLossCon);
			
            
            // Update summary stats
            const summaryHTML = `
                <div class="stat-card">
                    <div class="stat-value">${stats.totalTrades}</div>
                    <div class="stat-label">Total Trades</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${stats.winTrades}</div>
                    <div class="stat-label">Wins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${stats.lossTrades}</div>
                    <div class="stat-label">Losses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${stats.winRate.toFixed(1)}%</div>
                    <div class="stat-label">Win Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value ${stats.totalProfit >= 0 ? 'profit' : 'loss'}">${stats.totalProfit.toFixed(2)}</div>
                    <div class="stat-label">Total P&L</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value ${stats.maxDrawdown <= 0 ? 'profit' : 'loss'}">${stats.maxDrawdown.toFixed(2)}</div>
                    <div class="stat-label">Max Drawdown</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value ${stats.profitFactor >= 1 ? 'profit' : 'loss'}">${stats.profitFactor.toFixed(2)}</div>
                    <div class="stat-label">Profit Factor</div>
                </div>
            `;
            document.getElementById('summaryStats').innerHTML = summaryHTML;

            // Update trading table (show last 20 trades)
            let tableHTML = `
                <div style="max-height: 400px; overflow-y: auto;">
                    <table>
                        <thead style="position: sticky; top: 0;">
                            <tr>
                                <th>#</th>
                                <th>Time</th>
                                <th>Signal</th>
                                <th>Type</th>
                                <th>Entry</th>
                                <th>Exit</th>
                                <th>Change</th>
                                <th>Amount</th>
                                <th>P&L</th>
                                <th>Total</th>
                                <th>Losses</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            trades.slice(-30).forEach((trade) => {
                const profitClass = trade.isWin ? 'profit' : 'loss';
                const signalColor = trade.signal === 'BUY' ? '#4caf50' : '#f44336';
                tableHTML += `
                    <tr style="border-left: 4px solid ${signalColor};">
                        <td>${trade.index}</td>
                        <td>${trade.time}</td>
                        <td style="font-weight: bold; color: ${signalColor};">${trade.signal}</td>
                        <td>${trade.type}</td>
                        <td>${trade.entryPrice}</td>
                        <td>${trade.exitPrice}</td>
                        <td class="${profitClass}">${trade.priceChange}</td>
                        <td>${trade.amount}</td>
                        <td class="${profitClass}">${trade.profit}</td>
                        <td class="${parseFloat(trade.totalProfit) >= 0 ? 'profit' : 'loss'}">${trade.totalProfit}</td>
                        <td>${trade.consecutiveLosses}</td>
                    </tr>
                `;
            });
            
            tableHTML += '</tbody></table></div>';
            
            // Add martingale explanation
            tableHTML += `
                <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; font-size: 12px;">
                    <strong>💡 Martingale Strategy:</strong><br>
                    • Win: Reset to base amount (${document.getElementById('tradeAmount').value})<br>
                    • Loss: Multiply by ${document.getElementById('martingaleMultiplier').value}x<br>
                    • Max multiplier limited to 64x for safety<br>
                    • Binary options: 80% payout on win, 100% loss on lose
                </div>
            `;
            
            document.getElementById('tradingTable').innerHTML = tableHTML;

            // Update latest signals with more details
            const lastIndex = rawData.length - 1;
            const currentPrice = rawData[lastIndex];
            const prevPrice = rawData[lastIndex - 1];
            
            const latestSignalsHTML = `
                <div style="background: rgba(255,255,255,0.9); color: #333; padding: 20px; border-radius: 10px;">
                    <h4>📈 Current Market Status</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0;">
                        <div>
                            <p><strong>Open:</strong> ${currentPrice.open}</p>
                            <p><strong>High:</strong> ${currentPrice.high}</p>
                            <p><strong>Low:</strong> ${currentPrice.low}</p>
                            <p><strong>Close:</strong> ${currentPrice.close}</p>
                        </div>
                        <div>
                            <p><strong>Fast MA:</strong> ${analysis.indicators.smaFast[lastIndex]?.toFixed(4) || 'N/A'}</p>
                            <p><strong>Slow MA:</strong> ${analysis.indicators.smaSlow[lastIndex]?.toFixed(4) || 'N/A'}</p>
                            <p><strong>Buffer 1:</strong> ${analysis.indicators.buffer1[lastIndex]?.toFixed(4) || 'N/A'}</p>
                            <p><strong>Buffer 2:</strong> ${analysis.indicators.buffer2[lastIndex]?.toFixed(4) || 'N/A'}</p>
                        </div>
                    </div>
                    
                    <hr style="margin: 15px 0; border: 1px solid #ddd;">
                    
                    <h4>🎯 Active Signals</h4>
                    <div style="padding: 10px; margin: 10px 0; border-radius: 8px; background: ${analysis.signals.buy[lastIndex] ? '#e8f5e8' : analysis.signals.sell[lastIndex] ? '#ffeaea' : '#f5f5f5'};">
                        <p><strong>MA Signal:</strong> 
                            ${analysis.signals.buy[lastIndex] ? '🟢 BUY (Bullish Crossover)' : 
                              analysis.signals.sell[lastIndex] ? '🔴 SELL (Bearish Crossover)' : 
                              '⚪ HOLD (No Crossover)'}</p>
                        <p><strong>Engulfing Pattern:</strong> 
                            ${analysis.signals.bullishEngulfing[lastIndex] ? '🟢 Bullish Engulfing' : 
                              analysis.signals.bearishEngulfing[lastIndex] ? '🔴 Bearish Engulfing' : 
                              '⚪ No Pattern'}</p>
                        <p><strong>Overall Trend:</strong> 
                            ${analysis.indicators.buffer1[lastIndex] > analysis.indicators.buffer2[lastIndex] ? '📈 BULLISH' : '📉 BEARISH'}</p>
                    </div>
                    
                    <div style="margin-top: 15px; padding: 10px; background: #f0f8ff; border-radius: 8px;">
                        <p><strong>Next Trade Amount:</strong> ${currentAmount.toFixed(2)}</p>
                        <p><strong>Consecutive Losses:</strong> ${consecutiveLosses}</p>
                    </div>
                </div>
            `;
            document.getElementById('latestSignals').innerHTML = latestSignalsHTML;
        }

        // Initialize chart on page load
        window.addEventListener('load', () => {
            initChart();
            loadSampleData();
        });
    </script>
</body>
</html>