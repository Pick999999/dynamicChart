<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volume SuperTrend AI - Deriv Live Data</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>    
    <link href="css/supertrendAI_V5.css" rel="stylesheet">
	
<!-- 
	<script src="js/superTrendAI_V5.js"></script>
 -->
	<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
	<style>
	  .mBtn { border:2px solid white; }
	  .callBtn { background:#008000; color:white }
	  .callBtn:hover { background: }
      .putBtn { background:#ff0080; color:white }
	</style>
	
	
	
</head>
<body>
    <div class="container">
        <h1>📊 Volume SuperTrend AI - Deriv Live Data</h1>
        <div id="" class="bordergray flex" style='display:flex'>
		   <div id="ConnectStatus" class="status loading">🔄 รอการ Connecting to Deriv...</div>
           <div id="status" class="status loading"></div>
		</div>
        
        <div class="controls">
            <div class="control-group">
                <label>Symbol:</label>
                <select id="symbol" onclick='save2Local()'>
                    <option value="R_100" selected>Volatility 100 Index</option>
                    <option value="R_75">Volatility 75 Index</option>
                    <option value="R_50">Volatility 50 Index</option>
                    <option value="R_25">Volatility 25 Index</option>
                    <option value="R_10">Volatility 10 Index</option>
                </select>
            </div>
            <div class="control-group">
                <label>Timeframe:</label>
                <select id="timeframe" onclick='save2Local()'>
                    <option value="60" selected>1 Minute</option>
                    <option value="120">2 Minutes</option>
                    <option value="300">5 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="3600">1 Hour</option>
                </select>
            </div>
            <div class="control-group">
                <label>Candles:</label>
                <input type="number" id="candleCount" value="500" min="100" max="1000" step="100" onchange='save2Local()'>
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
                <select id="maType" onclick='save2Local()'>
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
        <div class="analysis-section" id="tradeSection" style="">
		  <h2>📊 Trade Board</h2>
		    Time Server<span style='color:red;font-weight:bold'></span>
		    <div class="summary-cards">
			    <button type='button' id='' class='mBtn callBtn'   onclick="fff()">CALL</button>
				<button type='button' id='' class='mBtn putBtn'  onclick="fff()">PUT</button>
				<div class="summary-card">
                    <h3>ASSET</h3>
                    <div class="value" id="currentAsset">-</div>
                </div>

                <div class="summary-card bullish">
                    <h3>TradeDuration</h3>
                    <div class="value signal-bullish" id="bullishCount">
					  <select id="granularitySelect" style='font-size:20px;width:100%' onclick='save2Local()'>
                <option value="60">1 Minute</option>
				<option value="180">3 Minutes</option>
                <option value="300">5 Minutes</option>
                <option value="900">15 Minutes</option>
				<option value="1800">30 Minutes</option>
                <option value="3600">1 Hour</option>
            </select>
					</div>
                </div>
                <div class="summary-card bearish">
                    <h3>Money Trade</h3>
                    <div class="value signal-bearish" id="bearishCount">
					 <input type="number" id="moneyTrade" onchange='save2Local()'>
					</div>
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
             
        </div>
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
						document.getElementById("ConnectStatus").innerHTML = '✅ Connected to Deriv';
						
                        resolve();
                    };

					this.ws.onmessage = (event) => {
                       const data = JSON.parse(event.data);
					   console.log(data.msg_type);
					   
					   if (data.msg_type === 'authorize') {
							console.log('Authorized successfully.');

							// เธ”เธถเธเธฃเธฒเธขเธเธฒเธฃเธชเธ–เธฒเธเธฐเธ—เธตเนเน€เธเธดเธ”เธญเธขเธนเน (Active Multipliers Contracts)
							ws.send(JSON.stringify({
								"portfolio": 1
							}));
					   }
					   if (data.msg_type === 'candles') {

					   }
					}
					this.ws.onclose = () => {                       
					   document.getElementById("ConnectStatus").innerHTML = '⛔ การเชื่อมต่อถูกปิด';
					   //alert('Try to Reconnect ');
					   $('#loadBtn').trigger('click');
					   alert('Try to Reconnect ');
					   //loadDerivData();
                    };
                    
                    this.ws.onerror = (error) => {
                        console.error('❌ WebSocket Error:', error);
                        reject(error);
                    };
                });
            }
            
            getOHLC(symbol, granularity, count) {				
				alert('aaaa');
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
                    time: candle.epoch + (7*3600),
                    open: parseFloat(candle.open),
                    high: parseFloat(candle.high),
                    low: parseFloat(candle.low),
                    close: parseFloat(candle.close),
                    volume: 1,
                    
                }));
				for (let i=0;i<=converted.length-1 ;i++ ) {
					if (converted[i].open > converted[i].close ) {
						converted[i].Color = 'Red';
					}
					if (converted[i].open < converted[i].close ) {
						converted[i].Color = 'Green';

					}
					if (converted[i].open === converted[i].close ) {
						converted[i].Color = 'Eaual';

					}			
				}
				analyzeCandleContinuity(converted);
                
				
				//console.log(converted);
				
                
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

        // ========================================
        // Forecast System with Tooltip
        // ========================================
        class ForecastSystem {
            constructor(indicator) {
                this.indicator = indicator;
                this.lookbackPeriod = 20;
            }
            
            // วิเคราะห์และให้คำแนะนำสำหรับแท่งเทียนที่ระบุ
            getForecast(index) {
                const results = this.indicator.results;
                const bars = this.indicator.bars;
                
                if (index >= results.length || !results[index] || !results[index].superTrend) {
                    return {
                        action: 'WAIT',
                        confidence: 0,
                        reasons: ['Insufficient data for analysis']
                    };
                }
                
                const current = results[index];
                const bar = bars[index];
                
                // ดึงข้อมูลย้อนหลัง
                const startIdx = Math.max(0, index - this.lookbackPeriod);
                const recentResults = results.slice(startIdx, index + 1);
                
                // วิเคราะห์หลายมิติ
                const trendAnalysis = this.analyzeTrend(recentResults, index);
                const pricePosition = this.analyzePricePosition(bar, current);
                const momentum = this.analyzeMomentum(recentResults);
                const marketState = this.detectMarketState(recentResults);
                
                // ตัดสินใจและสร้างเหตุผล
                return this.generateForecast({
                    current,
                    bar,
                    trendAnalysis,
                    pricePosition,
                    momentum,
                    marketState
                });
            }
            
            // วิเคราะห์ Trend
            analyzeTrend(recentResults, currentIndex) {
                const results = this.indicator.results;
                const current = results[currentIndex];
                const prev = currentIndex > 0 ? results[currentIndex - 1] : null;
                
                // นับจำนวนแท่งที่เป็นสีเดียวกัน
                let trendDuration = 0;
                const currentColor = current.color;
                for (let i = currentIndex; i >= 0; i--) {
                    if (results[i].color === currentColor) {
                        trendDuration++;
                    } else {
                        break;
                    }
                }
                
                // ตรวจสอบการเปลี่ยน direction
                const directionChange = prev && current.direction !== prev.direction;
                
                return {
                    color: currentColor,
                    direction: current.direction,
                    duration: trendDuration,
                    directionChange,
                    isBullish: currentColor === '#00FF00',
                    isBearish: currentColor === '#FF0000',

                };
            }
            
            // วิเคราะห์ตำแหน่งราคากับ SuperTrend
            analyzePricePosition(bar, result) {
                const price = bar.close;
                const st = result.superTrend;
                const distance = ((price - st) / st) * 100;
                
                return {
                    price,
                    superTrend: st,
                    distance: distance,
                    aboveSTrend: price > st,
                    belowSTrend: price < st,
                    nearSTrend: Math.abs(distance) < 0.1 // ลดจาก 0.5 เป็น 0.1 (ใกล้มากๆ เท่านั้น)
                };
            }
            
            // วิเคราะห์ Momentum
            analyzeMomentum(recentResults) {
                if (recentResults.length < 3) return { strength: 0 };
                
                const last = recentResults[recentResults.length - 1];
                const prev = recentResults[recentResults.length - 2];
                const bars = this.indicator.bars;
                
                // คำนวณความเร็วของการเปลี่ยนแปลง
                const priceChange = bars[bars.length - 1].close - bars[bars.length - 3].close;
                const stChange = last.superTrend - recentResults[recentResults.length - 3].superTrend;
                
                return {
                    priceChange,
                    stChange,
                    strength: Math.abs(priceChange),
                    increasing: priceChange > 0,
                    decreasing: priceChange < 0
                };
            } 
            
            // ตรวจจับสถานะตลาด (Trend หรือ Parallel)
            detectMarketState(recentResults) {
                let colorChanges = 0;
                for (let i = 1; i < recentResults.length; i++) {
                    if (recentResults[i].color !== recentResults[i - 1].color) {
                        colorChanges++;
                    }
                }
				console.log(colorChanges,recentResults)
				
                
                let totalSlope = 0;
                let slopeCount = 0;
                for (let i = 1; i < recentResults.length; i++) {
                    if (recentResults[i].superTrend && recentResults[i - 1].superTrend) {
                        const timeDiff = recentResults[i].time - recentResults[i - 1].time;
                        if (timeDiff > 0) {
                            const slope = Math.abs(
                                (recentResults[i].superTrend - recentResults[i - 1].superTrend) / timeDiff
                            );
                            totalSlope += slope;
                            slopeCount++;
                        }
                    }
                }
                const avgSlope = slopeCount > 0 ? totalSlope / slopeCount : 0;
                
                // ปรับเกณฑ์ให้เข้มงวดขึ้น - Parallel ต้องเปลี่ยนสีบ่อยมากๆ
                const isParallel = colorChanges >= 6; // เพิ่มจาก 4 เป็น 6
                
                return {
                    state: isParallel ? 'PARALLEL' : 'TREND',
                    colorChanges,
                    avgSlope,
                    isParallel,
                    isTrending: !isParallel
                };
            }
            
            // สร้างคำแนะนำ Forecast
            generateForecast(analysis) {
                const { current, bar, trendAnalysis, pricePosition, momentum, marketState } = analysis;
                
                let action = 'WAIT';
                let confidence = 0;
                let reasons = [];
                
                // ตรวจสอบสีของ SuperTrend เป็นหลัก
                const isBullish = trendAnalysis.isBullish;
                const isBearish = trendAnalysis.isBearish;
                
                // กรณีตลาด Parallel - ยังแนะนำได้แต่ความเสี่ยงสูง
                if (marketState.isParallel) {
                    if (isBullish) {
                        action = 'CALL';
                        reasons.push('📈 เส้น SuperTrend สีเขียว (เทรนด์ขึ้น)');
                        confidence = 40;
                    } else if (isBearish) {
                        action = 'PUT';
                        reasons.push('📉 เส้น SuperTrend สีแดง (เทรนด์ลง)');
                        confidence = 40;
                    }
                    
                    reasons.push(`⚠️ ตลาด Ranging - เปลี่ยนสี ${marketState.colorChanges} ครั้ง`);
                    confidence -= 10;
                    
                    if (trendAnalysis.duration < 5) {
                        reasons.push(`⚠️ เทรนด์สั้น (${trendAnalysis.duration} แท่ง) - ระวังกลับตัว`);
                        confidence -= 5;
                    }
                    
                    return { 
                        action, 
                        confidence, 
                        reasons, 
                        marketState: 'PARALLEL',
                        trendDuration: trendAnalysis.duration,
                        price: pricePosition.price.toFixed(2),
                        superTrend: pricePosition.superTrend.toFixed(2),
                        slope: (marketState.avgSlope * 1000).toFixed(4),
                        colorChanges: marketState.colorChanges
                    };
                }
                
                // กรณีตลาด Trending - ให้ความมั่นใจสูง
                if (isBullish) {
                    action = 'CALL';
                    reasons.push('📈 เทรนด์ขึ้นชัดเจน - SuperTrend สีเขียว');
                    confidence = 55; // เริ่มต้นสูง
                    
                    // วิเคราะห์ความแข็งแรงของเทรนด์
                    if (trendAnalysis.duration >= 20) {
                        reasons.push(`✅ เทรนด์แข็งแรงมาก (${trendAnalysis.duration} แท่ง)`);
                        confidence += 30;
                    } else if (trendAnalysis.duration >= 10) {
                        reasons.push(`✅ เทรนด์แข็งแรง (${trendAnalysis.duration} แท่ง)`);
                        confidence += 20;
                    } else if (trendAnalysis.duration >= 5) {
                        reasons.push(`✅ เทรนด์ปานกลาง (${trendAnalysis.duration} แท่ง)`);
                        confidence += 10;
                    } else {
                        reasons.push(`⚠️ เทรนด์ใหม่ (${trendAnalysis.duration} แท่ง)`);
                    }
                    
                    // ตำแหน่งราคา
                    if (pricePosition.aboveSTrend && !pricePosition.nearSTrend) {
                        reasons.push('✅ ราคาเหนือ SuperTrend (แนวรับ)');
                        confidence += 10;
                    } else if (pricePosition.nearSTrend) {
                        reasons.push('📍 ราคาใกล้ SuperTrend - จุดเข้าที่ดี');
                        confidence += 5;
                    } else {
                        reasons.push('⚠️ ราคาใต้ SuperTrend ชั่วคราว');
                    }
                    
                    // Momentum
                    if (momentum.increasing && momentum.strength > 0.5) {
                        reasons.push('✅ แรงซื้อเพิ่มขึ้น');
                        confidence += 10;
                    }
                    
                    // การเปลี่ยนทิศทาง
                    if (trendAnalysis.directionChange) {
                        reasons.push('🔄 ทิศทางเพิ่งเปลี่ยน - รอยืนยันเพิ่ม');
                        confidence -= 10;
                    }
                    
                    // ตลาดมีเทรนด์ชัด
                    reasons.push(`✅ มีเทรนด์ชัด - เปลี่ยนสี ${marketState.colorChanges} ครั้ง`);
                }
                
                if (isBearish) {
                    action = 'PUT';
                    reasons.push('📉 เทรนด์ลงชัดเจน - SuperTrend สีแดง');
                    confidence = 55; // เริ่มต้นสูง
                    
                    // วิเคราะห์ความแข็งแรงของเทรนด์
                    if (trendAnalysis.duration >= 20) {
                        reasons.push(`✅ เทรนด์แข็งแรงมาก (${trendAnalysis.duration} แท่ง)`);
                        confidence += 30;
                    } else if (trendAnalysis.duration >= 10) {
                        reasons.push(`✅ เทรนด์แข็งแรง (${trendAnalysis.duration} แท่ง)`);
                        confidence += 20;
                    } else if (trendAnalysis.duration >= 5) {
                        reasons.push(`✅ เทรนด์ปานกลาง (${trendAnalysis.duration} แท่ง)`);
                        confidence += 10;
                    } else {
                        reasons.push(`⚠️ เทรนด์ใหม่ (${trendAnalysis.duration} แท่ง)`);
                    }
                    
                    // ตำแหน่งราคา
                    if (pricePosition.belowSTrend && !pricePosition.nearSTrend) {
                        reasons.push('✅ ราคาใต้ SuperTrend (แนวต้าน)');
                        confidence += 10;
                    } else if (pricePosition.nearSTrend) {
                        reasons.push('📍 ราคาใกล้ SuperTrend - จุดเข้าที่ดี');
                        confidence += 5;
                    } else {
                        reasons.push('⚠️ ราคาเหนือ SuperTrend ชั่วคราว');
                    }
                    
                    // Momentum
                    if (momentum.decreasing && momentum.strength > 0.5) {
                        reasons.push('✅ แรงขายเพิ่มขึ้น');
                        confidence += 10;
                    }
                    
                    // การเปลี่ยนทิศทาง
                    if (trendAnalysis.directionChange) {
                        reasons.push('🔄 ทิศทางเพิ่งเปลี่ยน - รอยืนยันเพิ่ม');
                        confidence -= 10;
                    }
                    
                    // ตลาดมีเทรนด์ชัด
                    reasons.push(`✅ มีเทรนด์ชัด - เปลี่ยนสี ${marketState.colorChanges} ครั้ง`);
                }
                
                // จำกัดค่า confidence
                confidence = Math.max(0, Math.min(100, confidence));
                
                // เปลี่ยนเป็น WAIT เฉพาะเมื่อ confidence ต่ำมากๆ
                if (confidence < 25) {
                    action = 'WAIT';
                    reasons.push(`⚠️ ความมั่นใจต่ำมาก (${confidence}%) - ควรรอดู`);
                }
                
                return { 
                    action, 
                    confidence, 
                    reasons,
                    marketState: marketState.state,
                    trendDuration: trendAnalysis.duration,
                    price: pricePosition.price.toFixed(2),
                    superTrend: pricePosition.superTrend.toFixed(2),
                    slope: (marketState.avgSlope * 1000).toFixed(4),
                    colorChanges: marketState.colorChanges
                };
            }
        }

        // เพิ่ม Tooltip Handler
        let forecastSystem = null;
        
        function setupTooltip() {
            if (!chart || !forecastSystem) return;
            
            chart.subscribeCrosshairMove((param) => {
                if (!param.time || !param.point) {
                    hideTooltip();
                    return;
                }
                
                // หา index จาก time
                const bars = forecastSystem.indicator.bars;
                const index = bars.findIndex(b => b.time === param.time);
                
                if (index === -1) {
                    hideTooltip();
                    return;
                }
                
                // ดึงข้อมูล candle
                const candleData = bars[index];
                
                // ดึง forecast
                const forecast = forecastSystem.getForecast(index);
                
                // สร้าง tooltip
                showTooltip(param.point, forecast, candleData);
            });
        }
        
        function showTooltip(point, forecast, candleData) {
            // ลบ tooltip เก่า
            let tooltip = document.getElementById('custom-tooltip');
            if (!tooltip) {
                tooltip = document.createElement('div');
                tooltip.id = 'custom-tooltip';
                tooltip.style.position = 'absolute';
                tooltip.style.background = 'rgba(0, 0, 0, 0.9)';
                tooltip.style.color = '#fff';
                tooltip.style.padding = '12px';
                tooltip.style.borderRadius = '6px';
                tooltip.style.fontSize = '12px';
                tooltip.style.pointerEvents = 'none';
                tooltip.style.zIndex = '1000';
                tooltip.style.maxWidth = '300px';
                tooltip.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
                document.body.appendChild(tooltip);
            }
            
            // กำหนดสี
            let actionColor = '#FFA726';
            if (forecast.action === 'CALL') actionColor = '#00FF00';
            if (forecast.action === 'PUT') actionColor = '#FF0000';
            
            // สร้างเนื้อหา
            let html = `
                <div style="border-bottom: 2px solid ${actionColor}; padding-bottom: 8px; margin-bottom: 8px;">
                    <strong style="font-size: 16px; color: ${actionColor};">
                        ${forecast.action === 'CALL' ? '📈 ซื้อ CALL' : forecast.action === 'PUT' ? '📉 ซื้อ PUT' : '⏸️ รอดูก่อน'}
                    </strong>
                    <span style="margin-left: 10px; color: ${forecast.confidence >= 60 ? '#00FF00' : forecast.confidence >= 40 ? '#FFA726' : '#FF0000'};">
                        ${forecast.confidence}% ความมั่นใจ
                    </span>
                </div>
                <div style="margin-bottom: 8px; font-size: 11px; color: #787b86;">
                    <div>เปิด: ${candleData.open.toFixed(2)} | สูง: ${candleData.high.toFixed(2)}</div>
                    <div>ต่ำ: ${candleData.low.toFixed(2)} | ปิด: ${candleData.close.toFixed(2)}</div>
                    <div style="margin-top: 4px; color: #d1d4dc;">
                        <div>ST: ${forecast.superTrend} | สถานะ: ${forecast.marketState === 'TREND' ? 'มีเทรนด์' : 'ไม่มีทิศทาง'}</div>
                        <div>ความชัน: ${forecast.slope} | เทรนด์: ${forecast.trendDuration} แท่ง</div>
                    </div>
                </div>
                <div style="font-size: 11px; line-height: 1.6; border-top: 1px solid #2a2e39; padding-top: 8px;">
                    <strong style="color: #2962ff;">เหตุผล:</strong>
                    ${forecast.reasons.map(r => `<div style="margin: 3px 0; padding-left: 8px;">• ${r}</div>`).join('')}
                </div>
            `;
            
            tooltip.innerHTML = html;
            
            // ตำแหน่ง tooltip
            const chartRect = document.getElementById('chart').getBoundingClientRect();
            let left = chartRect.left + point.x + 15;
            let top = chartRect.top + point.y - tooltip.offsetHeight / 2;
            
            // ป้องกันไม่ให้เกินขอบ
            if (left + tooltip.offsetWidth > window.innerWidth) {
                left = chartRect.left + point.x - tooltip.offsetWidth - 15;
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.style.display = 'block';
        }

        // ซ่อน tooltip เมื่อ mouse out
        function hideTooltip() {
            const tooltip = document.getElementById('custom-tooltip');
            if (tooltip) {
                tooltip.style.display = 'none';
            }
        } 

// ฟังก์ชันช่วยแปลงเวลาเป็น hh:mm
     function formatTime(epoch) {
		const date = new Date(epoch * 1000);
    return date.toLocaleTimeString('th-TH', {
        timeZone: 'UTC',  // 👈 ไม่ต้องปรับเขตเวลาอีก
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    }

function analyzeCandleContinuity(candleDataArray) {
    if (!Array.isArray(candleDataArray) || candleDataArray.length === 0) return null;

    let greenCount = 0;
    let redCount = 0;
    let alterCount = 0;
    let prevColor = null;

    let maxAlter = 0;
    let alterList = [];

    for (let i = 0; i < candleDataArray.length; i++) {
        const candle = candleDataArray[i];
        const color = candle.Color;

        // รีเซ็ตค่าเริ่มต้น
        candle.GreenContinue = 0;
        candle.RedContinue = 0;
        candle.AlterContinue = 0;

        if (prevColor === null) {
            // แท่งแรก ไม่มีข้อมูลก่อนหน้า
            candle.GreenContinue = 0;
            candle.RedContinue = 0;
            candle.AlterContinue = 0;
        } else {
            if (color === prevColor) {
                // สีเหมือนเดิม → เพิ่มต่อเนื่องสีเดิม
                alterCount = 0;
                if (color === "Green") {
                    greenCount++;
                    redCount = 0;
                } else {
                    redCount++;
                    greenCount = 0;
                }
            } else {
                // สีต่างกัน → เพิ่ม alter ต่อเนื่อง
                alterCount++;
                greenCount = 0;
                redCount = 0;
            }

            // บันทึกค่าลงแท่งปัจจุบัน
            candle.GreenContinue = greenCount;
            candle.RedContinue = redCount;
            candle.AlterContinue = alterCount;

            // ตรวจจับค่า Max ของ AlterContinue
            if (alterCount > maxAlter) {
                maxAlter = alterCount;
                alterList = [candle.time];
            } else if (alterCount === maxAlter && alterCount > 0) {
                alterList.push(candle.time);
            }
        }

        prevColor = color;
    }

    // สร้างสรุป
     const summary = {
        MaxAlterContinue: maxAlter,
        ListTimeAlterContinue: alterList.map(t => ({
            time: t,
            timeDisplay: formatTime(t)
        }))
    };
    // console.log('summary',summary)
    
    return {
        data: candleDataArray,
        summary
    };
}

function save2Local() {

sObj = {
	"asset"  : document.getElementById("symbol").value ,
	"timeframe"  : document.getElementById("timeframe").value ,
	"candleCount"  : document.getElementById("candleCount").value ,
	"maType" : document.getElementById("maType").value ,
	"granularitySelect" : document.getElementById("granularitySelect").value ,
	"moneyTrade" : document.getElementById("moneyTrade").value
}
document.getElementById("currentAsset").innerHTML = document.getElementById("symbol").value ;

localStorage.setItem('superTrendV5',JSON.stringify(sObj));



} // end func

function getLocal() {

sObj = localStorage.getItem('superTrendV5');
sObj = JSON.parse(sObj);
document.getElementById("symbol").value = sObj.asset ;
document.getElementById("timeframe").value = sObj.timeframe ;
document.getElementById("candleCount").value = sObj.candleCount ;
document.getElementById("maType").value = sObj.maType ; 
document.getElementById("currentAsset").innerHTML = sObj.asset;
document.getElementById("granularitySelect").value  = sObj.granularitySelect;
document.getElementById("moneyTrade").value = sObj.moneyTrade ;





} // end func


        // อัพเดท updateIndicator function
        const originalUpdateIndicator = updateIndicator;
        updateIndicator = function() {
            originalUpdateIndicator();
            
            // สร้าง forecast system
            if (currentData.length > 0) {
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
                
                const indicator = new VolumeSuperTrendAI(options);
                indicator.calculate(currentData);
                
                forecastSystem = new ForecastSystem(indicator);
                setupTooltip();
                
                console.log('✅ Forecast system ready');
            }
        };

        // Initialize
        console.log('🚀 Initializing application...');
        initChart();
        console.log('✅ Chart ready. Click "Load Data" to fetch from Deriv.');
        
        // ซ่อน tooltip เมื่อ mouse ออกจากชาร์ท
        document.getElementById('chart').addEventListener('mouseleave', hideTooltip);
    </script>
 <script>

document.addEventListener('DOMContentLoaded', function() {
    // เนเธเนเธ”เธ—เธตเนเธ•เนเธญเธเธเธฒเธฃเนเธซเนเธ—เธณเธเธฒเธเน€เธกเธทเนเธญ DOM เนเธซเธฅเธ”เน€เธชเธฃเนเธ
	getLocal();
    console.log('DOM fully loaded and parsed');
});

 </script>
</body>
</html>
มี array ชื่อ candleDataArray ดังนี้[
{
    "time": 1760979660,
    "open": 985.91,
    "high": 986.91,
    "low": 985.44,
    "close": 986.35,
    "volume": 1,
    "Color": "Green"
},
{
    "time": 1760980080,
    "open": 993.48,
    "high": 993.93,
    "low": 992.1,
    "close": 993.27,
    "volume": 1,
    "Color": "Red"
}
] 
ต้องการ สร้าง field ชือ GreenContinue,RedContinue,AlterContinue
ซึ่งมีหลักการ
1.ถ้า สีของแท่งปัจจุบัน ตรงกับสี ก่อนหน้า ก็จะเพิ่มค่า GreenContinue,RedContinue และค่า AlterContinue ต้องเป็น 0 
2.ถ้า สีของแท่งปัจจุบัน ไม่ตรงกับสี ก่อนหน้า ก็จะเพิ่มค่า AlterContinue และ GreenContinue,RedContinue  ต้องเป็น 0 
3.เมื่อทำครบ สร้าง field สรุป 
{
  MaxAlterContinue : จำนวน Max ของ  AlterContinue,
  ListTimeAlterContinue : รายการช่วง เวลาที่เกิด MaxContinue ที่เวลาไหนบ้าง 
}
4.ทำด้วย pure javascript function ซึ่งจะรับค่า candleDataArray มาหาค่า