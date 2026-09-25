<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Structure Break & Order Block Analysis</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f0f;
            color: #e0e0e0;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .controls {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
        }
        
        select, input, button {
            padding: 8px 12px;
            border: 1px solid #333;
            background: #2a2a2a;
            color: #fff;
            border-radius: 4px;
            font-size: 14px;
        }
        
        button {
            background: #2962ff;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        button:hover {
            background: #1e4fd6;
        }
        
        button:disabled {
            background: #333;
            cursor: not-allowed;
        }
        
        #chart {
            background: #1a1a1a;
            border-radius: 8px;
            height: 600px;
            margin-bottom: 20px;
        }
        
        .status {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .status-row {
            display: flex;
            gap: 30px;
            margin-bottom: 10px;
        }
        
        .status-item {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .status-label {
            color: #999;
            font-size: 13px;
        }
        
        .status-value {
            font-weight: 600;
            font-size: 14px;
        }
        
        .bullish {
            color: #26a69a;
        }
        
        .bearish {
            color: #ef5350;
        }
        
        .blocks-info {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .block-section {
            background: #252525;
            padding: 15px;
            border-radius: 6px;
        }
        
        .block-section h3 {
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .block-item {
            background: #2a2a2a;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 8px;
            font-size: 12px;
            border-left: 3px solid;
        }
        
        .block-item.bu {
            border-left-color: #26a69a;
        }
        
        .block-item.be {
            border-left-color: #ef5350;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Market Structure Break & Order Block Analysis</h1>
        
        <div class="controls">
            <div class="control-group">
                <label>Symbol</label>
                <select id="symbol">
                    <option value="R_100">Volatility 100 Index</option>
                    <option value="R_75">Volatility 75 Index</option>
                    <option value="R_50">Volatility 50 Index</option>
                    <option value="R_25">Volatility 25 Index</option>
                    <option value="R_10">Volatility 10 Index</option>
                    <option value="frxEURUSD">EUR/USD</option>
                    <option value="frxGBPUSD">GBP/USD</option>
                    <option value="frxUSDJPY">USD/JPY</option>
                </select>
            </div>
            
            <div class="control-group">
                <label>Timeframe</label>
                <select id="timeframe">
                    <option value="60">1 Minute</option>
                    <option value="300">5 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="3600">1 Hour</option>
                    <option value="14400">4 Hours</option>
                    <option value="86400">1 Day</option>
                </select>
            </div>
            
            <div class="control-group">
                <label>Candles</label>
                <input type="number" id="count" value="500" min="100" max="2000" step="100">
            </div>
            
            <div class="control-group">
                <label>ZigZag Length</label>
                <input type="number" id="zigzagLen" value="9" min="3" max="50">
            </div>
            
            <div class="control-group">
                <label>Fib Factor</label>
                <input type="number" id="fibFactor" value="0.33" min="0" max="1" step="0.01">
            </div>
            
            <button id="loadBtn">Load & Analyze</button>
        </div>
        
        <div class="status">
            <div class="status-row">
                <div class="status-item">
                    <span class="status-label">Trend:</span>
                    <span class="status-value" id="trend">-</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Market:</span>
                    <span class="status-value" id="market">-</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Candles Loaded:</span>
                    <span class="status-value" id="candlesCount">0</span>
                </div>
            </div>
        </div>
        
        <div id="chart"></div>
        
        <div class="blocks-info">
            <div class="block-section">
                <h3 style="color: #26a69a;">🟢 Bullish Order Blocks</h3>
                <div id="buObList"></div>
            </div>
            
            <div class="block-section">
                <h3 style="color: #ef5350;">🔴 Bearish Order Blocks</h3>
                <div id="beObList"></div>
            </div>
            
            <div class="block-section">
                <h3 style="color: #26a69a;">🟢 Bullish Breaker Blocks</h3>
                <div id="buBbList"></div>
            </div>
            
            <div class="block-section">
                <h3 style="color: #ef5350;">🔴 Bearish Breaker Blocks</h3>
                <div id="beBbList"></div>
            </div>
        </div>
    </div>

    <script>
        // Market Structure Break & Order Block Class
        class MarketStructureBreakOrderBlock {
            constructor(config = {}) {
                this.zigzagLen = config.zigzagLen || 9;
                this.fibFactor = config.fibFactor || 0.33;
                this.deleteBoxes = config.deleteBoxes !== undefined ? config.deleteBoxes : true;
                
                this.highPointsArr = [];
                this.highIndexArr = [];
                this.lowPointsArr = [];
                this.lowIndexArr = [];
                
                this.buObBoxes = [];
                this.beObBoxes = [];
                this.buBbBoxes = [];
                this.beBbBoxes = [];
                
                this.trend = 1;
                this.market = 1;
                this.lastL0 = null;
                this.lastH0 = null;
                
                this.highs = [];
                this.lows = [];
                this.opens = [];
                this.closes = [];
                this.times = [];
                this.barIndex = 0;
                
                this.trendHistory = [];
            }
            
            addCandle(time, open, high, low, close) {
                this.times.push(time);
                this.opens.push(open);
                this.highs.push(high);
                this.lows.push(low);
                this.closes.push(close);
                
                this.barIndex = this.opens.length - 1;
                this.processCandle();
                
                return this.getSignals();
            }
            
            highest(length, offset = 0) {
                const start = Math.max(0, this.barIndex - length + 1 - offset);
                const end = this.barIndex + 1 - offset;
                return Math.max(...this.highs.slice(start, end));
            }
            
            lowest(length, offset = 0) {
                const start = Math.max(0, this.barIndex - length + 1 - offset);
                const end = this.barIndex + 1 - offset;
                return Math.min(...this.lows.slice(start, end));
            }
            
            processCandle() {
                if (this.barIndex < this.zigzagLen) return;
                
                const currentHigh = this.highs[this.barIndex];
                const currentLow = this.lows[this.barIndex];
                
                const toUp = currentHigh >= this.highest(this.zigzagLen);
                const toDown = currentLow <= this.lowest(this.zigzagLen);
                
                const prevTrend = this.trend;
                if (this.trend === 1 && toDown) {
                    this.trend = -1;
                } else if (this.trend === -1 && toUp) {
                    this.trend = 1;
                }
                
                this.trendHistory.push(this.trend);
                
                if (prevTrend !== this.trend) {
                    if (this.trend === 1) {
                        const lowVal = this.findRecentLow();
                        const lowIndex = this.findIndexOfValue(this.lows, lowVal);
                        this.lowPointsArr.push(lowVal);
                        this.lowIndexArr.push(lowIndex);
                    }
                    if (this.trend === -1) {
                        const highVal = this.findRecentHigh();
                        const highIndex = this.findIndexOfValue(this.highs, highVal);
                        this.highPointsArr.push(highVal);
                        this.highIndexArr.push(highIndex);
                    }
                }
                
                this.updateMarketStructure();
                this.updateBoxes();
            }
            
            findRecentLow() {
                const searchRange = Math.min(this.zigzagLen * 3, this.barIndex);
                const start = Math.max(0, this.barIndex - searchRange);
                return Math.min(...this.lows.slice(start, this.barIndex + 1));
            }
            
            findRecentHigh() {
                const searchRange = Math.min(this.zigzagLen * 3, this.barIndex);
                const start = Math.max(0, this.barIndex - searchRange);
                return Math.max(...this.highs.slice(start, this.barIndex + 1));
            }
            
            findIndexOfValue(arr, value) {
                for (let i = arr.length - 1; i >= Math.max(0, arr.length - this.zigzagLen * 3); i--) {
                    if (arr[i] === value) return i;
                }
                return arr.length - 1;
            }
            
            getHigh(index) {
                const arrSize = this.highPointsArr.length;
                if (arrSize === 0 || index >= arrSize) return [null, null];
                return [
                    this.highPointsArr[arrSize - 1 - index],
                    this.highIndexArr[arrSize - 1 - index]
                ];
            }
            
            getLow(index) {
                const arrSize = this.lowPointsArr.length;
                if (arrSize === 0 || index >= arrSize) return [null, null];
                return [
                    this.lowPointsArr[arrSize - 1 - index],
                    this.lowIndexArr[arrSize - 1 - index]
                ];
            }
            
            updateMarketStructure() {
                const [h0, h0i] = this.getHigh(0);
                const [h1, h1i] = this.getHigh(1);
                const [l0, l0i] = this.getLow(0);
                const [l1, l1i] = this.getLow(1);
                
                if (!h0 || !h1 || !l0 || !l1) return;
                
                const prevMarket = this.market;
                
                if (this.lastL0 !== l0 && this.lastH0 !== h0) {
                    if (this.market === 1 && l0 < l1 && 
                        l0 < l1 - Math.abs(h0 - l1) * this.fibFactor) {
                        this.market = -1;
                        this.lastL0 = l0;
                        this.lastH0 = h0;
                    } else if (this.market === -1 && h0 > h1 && 
                               h0 > h1 + Math.abs(h1 - l0) * this.fibFactor) {
                        this.market = 1;
                        this.lastL0 = l0;
                        this.lastH0 = h0;
                    }
                }
                
                if (prevMarket !== this.market) {
                    this.createOrderBlocks(h0, h1, l0, l1, h0i, h1i, l0i, l1i);
                }
            }
            
            createOrderBlocks(h0, h1, l0, l1, h0i, h1i, l0i, l1i) {
                if (this.market === 1) {
                    const buObIndex = this.findLastBearishCandle(Math.max(0, h1i), l0i);
                    const buBbIndex = this.findLastBullishCandle(Math.max(0, l1i - this.zigzagLen), h1i);
                    
                    if (buObIndex !== null) {
                        this.buObBoxes.push({
                            left: buObIndex,
                            top: this.highs[buObIndex],
                            right: this.barIndex + 10,
                            bottom: this.lows[buObIndex],
                            type: 'Bu-OB',
                            time: this.times[buObIndex]
                        });
                    }
                    
                    if (buBbIndex !== null) {
                        this.buBbBoxes.push({
                            left: buBbIndex,
                            top: this.highs[buBbIndex],
                            right: this.barIndex + 10,
                            bottom: this.lows[buBbIndex],
                            type: l0 < l1 ? 'Bu-BB' : 'Bu-MB',
                            time: this.times[buBbIndex]
                        });
                    }
                } else if (this.market === -1) {
                    const beObIndex = this.findLastBullishCandle(Math.max(0, l1i), h0i);
                    const beBbIndex = this.findLastBearishCandle(Math.max(0, h1i - this.zigzagLen), l1i);
                    
                    if (beObIndex !== null) {
                        this.beObBoxes.push({
                            left: beObIndex,
                            top: this.highs[beObIndex],
                            right: this.barIndex + 10,
                            bottom: this.lows[beObIndex],
                            type: 'Be-OB',
                            time: this.times[beObIndex]
                        });
                    }
                    
                    if (beBbIndex !== null) {
                        this.beBbBoxes.push({
                            left: beBbIndex,
                            top: this.highs[beBbIndex],
                            right: this.barIndex + 10,
                            bottom: this.lows[beBbIndex],
                            type: h0 > h1 ? 'Be-BB' : 'Be-MB',
                            time: this.times[beBbIndex]
                        });
                    }
                }
            }
            
            findLastBearishCandle(startIdx, endIdx) {
                for (let i = Math.min(endIdx, this.barIndex); i >= Math.max(0, startIdx); i--) {
                    if (this.opens[i] > this.closes[i]) return i;
                }
                return null;
            }
            
            findLastBullishCandle(startIdx, endIdx) {
                for (let i = Math.min(endIdx, this.barIndex); i >= Math.max(0, startIdx); i--) {
                    if (this.opens[i] < this.closes[i]) return i;
                }
                return null;
            }
            
            updateBoxes() {
                const currentClose = this.closes[this.barIndex];
                
                this.buObBoxes = this.buObBoxes.filter(box => {
                    if (currentClose < box.bottom) {
                        return !this.deleteBoxes;
                    }
                    box.right = this.barIndex + 10;
                    return true;
                });
                
                this.beObBoxes = this.beObBoxes.filter(box => {
                    if (currentClose > box.top) {
                        return !this.deleteBoxes;
                    }
                    box.right = this.barIndex + 10;
                    return true;
                });
                
                this.buBbBoxes = this.buBbBoxes.filter(box => {
                    if (currentClose < box.bottom) {
                        return !this.deleteBoxes;
                    }
                    box.right = this.barIndex + 10;
                    return true;
                });
                
                this.beBbBoxes = this.beBbBoxes.filter(box => {
                    if (currentClose > box.top) {
                        return !this.deleteBoxes;
                    }
                    box.right = this.barIndex + 10;
                    return true;
                });
            }
            
            getSignals() {
                return {
                    trend: this.trend === 1 ? 'Bullish' : 'Bearish',
                    market: this.market === 1 ? 'Bullish' : 'Bearish',
                    buObBoxes: [...this.buObBoxes],
                    beObBoxes: [...this.beObBoxes],
                    buBbBoxes: [...this.buBbBoxes],
                    beBbBoxes: [...this.beBbBoxes]
                };
            }
        }

        // Chart and API Integration
        let chart, candleSeries;
        let msb;
        const boxes = [];

        function initChart() {
            const chartContainer = document.getElementById('chart');
            chartContainer.innerHTML = '';
            
            chart = LightweightCharts.createChart(chartContainer, {
                width: chartContainer.clientWidth,
                height: 600,
                layout: {
                    background: { color: '#1a1a1a' },
                    textColor: '#d1d4dc',
                },
                grid: {
                    vertLines: { color: '#2a2a2a' },
                    horzLines: { color: '#2a2a2a' },
                },
                crosshair: {
                    mode: LightweightCharts.CrosshairMode.Normal,
                },
                rightPriceScale: {
                    borderColor: '#2a2a2a',
                },
                timeScale: {
                    borderColor: '#2a2a2a',
                    timeVisible: true,
                    secondsVisible: false,
                },
            });

            candleSeries = chart.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });

            window.addEventListener('resize', () => {
                chart.applyOptions({ width: chartContainer.clientWidth });
            });
        }

        async function fetchDerivData(symbol, granularity, count) {
            const app_id = 1089; // Public Deriv app_id
            const ws = new WebSocket(`wss://ws.derivws.com/websockets/v3?app_id=${app_id}`);
            
            return new Promise((resolve, reject) => {
                ws.onopen = () => {
                    const end = Math.floor(Date.now() / 1000);
                    const start = end - (granularity * count);
                    
                    ws.send(JSON.stringify({
                        ticks_history: symbol,
                        adjust_start_time: 1,
                        count: count,
                        end: end,
                        start: start,
                        style: 'candles',
                        granularity: parseInt(granularity)
                    }));
                };

                ws.onmessage = (msg) => {
                    const data = JSON.parse(msg.data);
                    
                    if (data.error) {
                        reject(data.error.message);
                        ws.close();
                        return;
                    }
                    
                    if (data.candles) {
                        resolve(data.candles);
                        ws.close();
                    }
                };

                ws.onerror = (error) => {
                    reject('WebSocket error');
                    ws.close();
                };

                setTimeout(() => {
                    reject('Timeout');
                    ws.close();
                }, 15000);
            });
        }

        function drawBox(box, color, borderColor) {
            const rectSeries = chart.addLineSeries({
                color: 'transparent',
                lineWidth: 0,
                priceLineVisible: false,
                lastValueVisible: false,
                crosshairMarkerVisible: false,
            });

            const startTime = msb.times[box.left];
            const endTime = msb.times[Math.min(box.right, msb.times.length - 1)] || msb.times[msb.times.length - 1];

            // Top line
            rectSeries.setData([
                { time: startTime, value: box.top },
                { time: endTime, value: box.top }
            ]);

            // Create visual rectangle using price line
            rectSeries.createPriceLine({
                price: box.top,
                color: borderColor,
                lineWidth: 1,
                lineStyle: LightweightCharts.LineStyle.Solid,
                axisLabelVisible: false,
            });

            rectSeries.createPriceLine({
                price: box.bottom,
                color: borderColor,
                lineWidth: 1,
                lineStyle: LightweightCharts.LineStyle.Solid,
                axisLabelVisible: false,
            });

            boxes.push(rectSeries);
        }

        function clearBoxes() {
            boxes.forEach(series => chart.removeSeries(series));
            boxes.length = 0;
        }

        function updateBlocksList(signals) {
            const formatPrice = (price) => price.toFixed(5);
            const formatTime = (time) => new Date(time * 1000).toLocaleString();

            document.getElementById('buObList').innerHTML = signals.buObBoxes.length 
                ? signals.buObBoxes.map(box => `
                    <div class="block-item bu">
                        <strong>${box.type}</strong><br>
                        Top: ${formatPrice(box.top)}<br>
                        Bottom: ${formatPrice(box.bottom)}<br>
                        <small>${formatTime(box.time)}</small>
                    </div>
                `).join('') 
                : '<div style="color: #666; font-size: 12px;">No active blocks</div>';

            document.getElementById('beObList').innerHTML = signals.beObBoxes.length 
                ? signals.beObBoxes.map(box => `
                    <div class="block-item be">
                        <strong>${box.type}</strong><br>
                        Top: ${formatPrice(box.top)}<br>
                        Bottom: ${formatPrice(box.bottom)}<br>
                        <small>${formatTime(box.time)}</small>
                    </div>
                `).join('') 
                : '<div style="color: #666; font-size: 12px;">No active blocks</div>';

            document.getElementById('buBbList').innerHTML = signals.buBbBoxes.length 
                ? signals.buBbBoxes.map(box => `
                    <div class="block-item bu">
                        <strong>${box.type}</strong><br>
                        Top: ${formatPrice(box.top)}<br>
                        Bottom: ${formatPrice(box.bottom)}<br>
                        <small>${formatTime(box.time)}</small>
                    </div>
                `).join('') 
                : '<div style="color: #666; font-size: 12px;">No active blocks</div>';

            document.getElementById('beBbList').innerHTML = signals.beBbBoxes.length 
                ? signals.beBbBoxes.map(box => `
                    <div class="block-item be">
                        <strong>${box.type}</strong><br>
                        Top: ${formatPrice(box.top)}<br>
                        Bottom: ${formatPrice(box.bottom)}<br>
                        <small>${formatTime(box.time)}</small>
                    </div>
                `).join('') 
                : '<div style="color: #666; font-size: 12px;">No active blocks</div>';
        }

        async function loadAndAnalyze() {
            const symbol = document.getElementById('symbol').value;
            const timeframe = document.getElementById('timeframe').value;
            const count = parseInt(document.getElementById('count').value);
            const zigzagLen = parseInt(document.getElementById('zigzagLen').value);
            const fibFactor = parseFloat(document.getElementById('fibFactor').value);
            
            const loadBtn = document.getElementById('loadBtn');
            loadBtn.disabled = true;
            loadBtn.textContent = 'Loading...';

            try {
                // Fetch data from Deriv
                const candles = await fetchDerivData(symbol, timeframe, count);
                
                // Initialize MSB analyzer
                msb = new MarketStructureBreakOrderBlock({
                    zigzagLen: zigzagLen,
                    fibFactor: fibFactor,
                    deleteBoxes: true
                });

                // Prepare chart data
                const chartData = candles.map(candle => ({
                    time: candle.epoch,
                    open: parseFloat(candle.open),
                    high: parseFloat(candle.high),
                    low: parseFloat(candle.low),
                    close: parseFloat(candle.close)
                }));

                // Set chart data
                candleSeries.setData(chartData);

                // Analyze with MSB
                let signals;
                candles.forEach(candle => {
                    signals = msb.addCandle(
                        candle.epoch,
                        parseFloat(candle.open),
                        parseFloat(candle.high),
                        parseFloat(candle.low),
                        parseFloat(candle.close)
                    );
                });

                // Update status
                document.getElementById('trend').textContent = signals.trend;
                document.getElementById('trend').className = `status-value ${signals.trend === 'Bullish' ? 'bullish' : 'bearish'}`;
                
                document.getElementById('market').textContent = signals.market;
                document.getElementById('market').className = `status-value ${signals.market === 'Bullish' ? 'bullish' : 'bearish'}`;
                
                document.getElementById('candlesCount').textContent = candles.length;

                // Clear old boxes and draw new ones
                clearBoxes();
                
                signals.buObBoxes.forEach(box => drawBox(box, '#26a69a33', '#26a69a'));
                signals.beObBoxes.forEach(box => drawBox(box, '#ef535033', '#ef5350'));
                signals.buBbBoxes.forEach(box => drawBox(box, '#26a69a22', '#26a69a'));
                signals.beBbBoxes.forEach(box => drawBox(box, '#ef535022', '#ef5350'));

                // Update blocks list
                updateBlocksList(signals);

                chart.timeScale().fitContent();

            } catch (error) {
                alert('Error loading data: ' + error);
                console.error(error);
            } finally {
                loadBtn.disabled = false;
                loadBtn.textContent = 'Load & Analyze';
            }
        }

        // Initialize
        window.addEventListener('DOMContentLoaded', () => {
            initChart();
            document.getElementById('loadBtn').addEventListener('click', loadAndAnalyze);
        });
    </script>
</body>
</html>