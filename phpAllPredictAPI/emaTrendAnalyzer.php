<?php
/*
emaTrendAnalyzer.php
ฉันต้องการ สร้าง เครื่องมือ ทดสอบ Trend ดังนี้
  1.สร้าง list ของ asset จาก deriv.com เป็น 3 กลุ่ม กลุ่มละ 7 อัน
  2.มี  text box ให้กำหนดค่า ema Period,candleCount
  3.มี listbox ให้เลือก timeframe 
  4.มี button ให้ทำงาน โดยเลือก ชุด asset มาจาก list ทำการดึงข้อมูล candle History แบบ latest ของ แต่ละ asset ใน list
  5.เมื่อได้  candleData มาแล้วให้ ทำการหา ema  จาก emaPediod
  6.หาจุดทั้งหมด ของ turnPoint ประเภทต่าง ๆ จาก ema 
  7.สรุปจำนวนจุด TurnPoint ของ asset นั้นๆ 
  8.สร้างสรุปเป็น ตาราง  html table 
  9.เมือคลิกที่ table ก็จะดึง asset มาวาดกราฟ candlestick + ema ลงใน lightweightchart 3.8
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMA Trend Analyzer</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>



<script src="autoSaveInputs.js" ></script>
<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>


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
            max-width: 1600px;
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
        }
        
        .control-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
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
        
        input, select, button {
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
            padding: 10px 20px;
        }
        
        button:hover {
            background: #1e4fd6;
        }
        
        button:disabled {
            background: #333;
            cursor: not-allowed;
        }
        
        .asset-groups {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .asset-group {
            background: #252525;
            padding: 15px;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .asset-group:hover {
            border-color: #2962ff;
        }
        
        .asset-group.selected {
            background: #2962ff22;
            border-color: #2962ff;
        }
        
        .asset-group h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #2962ff;
        }
        
        .asset-list {
            font-size: 12px;
            line-height: 1.8;
            color: #bbb;
        }
        
        .results {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .results h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #fff;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        thead {
            background: #252525;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        
        th {
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
        }
        
        tbody tr {
            cursor: pointer;
            transition: background 0.2s;
        }
        
        tbody tr:hover {
            background: #252525;
        }
        
        tbody tr.selected {
            background: #2962ff22;
            border-left: 3px solid #2962ff;
        }
        
        .turnup {
            color: #26a69a;
            font-weight: 600;
        }
        
        .turndown {
            color: #ef5350;
            font-weight: 600;
        }
        
        .total {
            color: #2962ff;
            font-weight: 600;
        }
        
        #chart {
            background: #1a1a1a;
            border-radius: 8px;
            height: 500px;
            margin-bottom: 20px;
        }
        
        .chart-title {
            background: #252525;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #fff;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #999;
        }
        
        .status {
            background: #252525;
            padding: 10px 15px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 12px;
            color: #999;
        }
        
        .progress {
            height: 4px;
            background: #333;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-bar {
            height: 100%;
            background: #2962ff;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 EMA Trend Analyzer - Deriv Assets</h1>
        
        <div class="controls">
            <div class="asset-groups">
                <div class="asset-group" data-group="forex">
                    <h3>🌍 Forex</h3>
                    <div class="asset-list">
                        EUR/USD, GBP/USD, USD/JPY, AUD/USD,<br>
                        USD/CHF, EUR/GBP, EUR/JPY
                    </div>
                </div>
                <div class="asset-group" data-group="volatility">
                    <h3>📈 Volatility Indices</h3>
                    <div class="asset-list">
                        Vol 10, Vol 25, Vol 50, Vol 75,<br>
                        Vol 100, VIX 10, VIX 25
                    </div>
                </div>
                <div class="asset-group" data-group="commodities">
                    <h3>💰 Commodities</h3>
                    <div class="asset-list">
                        Gold/USD, Silver/USD, Oil/USD,<br>
                        Platinum, Palladium, Dow Jones, FTSE
                    </div>
                </div>
                <div class="asset-group" data-group="crypto">
                    <h3>₿ Crypto</h3>
                    <div class="asset-list">
                        BTC/USD, ETH/USD, LTC/USD,<br>
                        XRP/USD, BCH/USD, EOS/USD, BNB/USD
                    </div>
                </div>
                <div class="asset-group" data-group="indices">
                    <h3>📊 Stock Indices</h3>
                    <div class="asset-list">
                        US 500, US Tech 100, Wall Street 30,<br>
                        Germany 40, UK 100, Japan 225, Australia 200
                    </div>
                </div>
            </div>
            
            <div class="control-row">
                <div class="control-group">
                    <label>EMA Period</label>
                    <input type="number" id="emaPeriod" value="20" min="5" max="200">
                </div>
                
                <div class="control-group">
                    <label>Candle Count</label>
                    <input type="number" id="candleCount" value="500" min="100" max="2000" step="100">
                </div>
                
                <div class="control-group">
                    <label>Timeframe</label>
                    <select id="timeframe">
                        <option value="60">1 Minute</option>
                        <option value="300">5 Minutes</option>
                        <option value="900" selected>15 Minutes</option>
                        <option value="3600">1 Hour</option>
                        <option value="14400">4 Hours</option>
                        <option value="86400">1 Day</option>
                    </select>
                </div>
                
                <button id="analyzeBtn">🔍 Analyze Selected Groups</button>
                <button id="selectAllBtn" style="background: #666;">✓ Select All</button>
                <button id="clearSelectionBtn" style="background: #666;">✕ Clear Selection</button>
            </div>
            
            <div class="status" id="status" style="display: none;">
                <div id="statusText">Processing...</div>
                <div class="progress">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
            </div>
        </div>
        
        <div class="results" id="resultsSection" style="display: none;">
            <h2>Analysis Results</h2>
            <table id="resultsTable">
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Group</th>
                        <th class="turnup">Turn Up ↑</th>
                        <th class="turndown">Turn Down ↓</th>
                        <th class="total">Total Turns</th>
                        <th>Trend</th>
                        <th>Last Price</th>
                    </tr>
                </thead>
                <tbody id="resultsBody">
                </tbody>
            </table>
        </div>
        
        <div id="chartSection" style="display: none;">
            <div class="chart-title" id="chartTitle">Select an asset from the table</div>
            <div id="chart"></div>
        </div>
    </div>

    <script>
        // Asset Lists
        const ASSETS = {
            forex: [
                { symbol: 'frxEURUSD', name: 'EUR/USD' },
                { symbol: 'frxGBPUSD', name: 'GBP/USD' },
                { symbol: 'frxUSDJPY', name: 'USD/JPY' },
                { symbol: 'frxAUDUSD', name: 'AUD/USD' },
                { symbol: 'frxUSDCHF', name: 'USD/CHF' },
                { symbol: 'frxEURGBP', name: 'EUR/GBP' },
                { symbol: 'frxEURJPY', name: 'EUR/JPY' }
            ],
            volatility: [
                { symbol: 'R_10', name: 'Vol 10' },
                { symbol: 'R_25', name: 'Vol 25' },
                { symbol: 'R_50', name: 'Vol 50' },
                { symbol: 'R_75', name: 'Vol 75' },
                { symbol: 'R_100', name: 'Vol 100' },
                { symbol: '1HZ10V', name: 'VIX 10' },
                { symbol: '1HZ25V', name: 'VIX 25' }
            ],
            commodities: [
                { symbol: 'frxXAUUSD', name: 'Gold/USD' },
                { symbol: 'frxXAGUSD', name: 'Silver/USD' },
                { symbol: 'frxBROUSD', name: 'Oil/USD' },
                { symbol: 'frxXPTUSD', name: 'Platinum' },
                { symbol: 'frxXPDUSD', name: 'Palladium' },
                { symbol: 'OTC_DJI', name: 'Dow Jones' },
                { symbol: 'OTC_FTSE', name: 'FTSE 100' }
            ],
            crypto: [
                { symbol: 'cryBTCUSD', name: 'BTC/USD' },
                { symbol: 'cryETHUSD', name: 'ETH/USD' },
                { symbol: 'cryLTCUSD', name: 'LTC/USD' },
                { symbol: 'cryXRPUSD', name: 'XRP/USD' },
                { symbol: 'cryBCHUSD', name: 'BCH/USD' },
                { symbol: 'cryEOSUSD', name: 'EOS/USD' },
                { symbol: 'cryBNBUSD', name: 'BNB/USD' }
            ],
            indices: [
                { symbol: 'OTC_SPC', name: 'US 500' },
                { symbol: 'OTC_NDX', name: 'US Tech 100' },
                { symbol: 'OTC_DJI', name: 'Wall Street 30' },
                { symbol: 'OTC_DAX', name: 'Germany 40' },
                { symbol: 'OTC_FTSE', name: 'UK 100' },
                { symbol: 'OTC_N225', name: 'Japan 225' },
                { symbol: 'OTC_AS51', name: 'Australia 200' }
            ]
        };

        let chart, candleSeries, emaSeries;
        let currentResults = {};
        let selectedGroups = new Set();

        // Initialize Chart
        function initChart() {
            const chartContainer = document.getElementById('chart');
            chartContainer.innerHTML = '';
            
            chart = LightweightCharts.createChart(chartContainer, {
                width: chartContainer.clientWidth,
                height: 500,
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

            emaSeries = chart.addLineSeries({
                color: '#FFD700',
                lineWidth: 2,
                title: 'EMA',
            });

            window.addEventListener('resize', () => {
                chart.applyOptions({ width: chartContainer.clientWidth });
            });
        }

        // Calculate EMA
        function calculateEMA(data, period) {
            const ema = [];
            const k = 2 / (period + 1);
            
            if (data.length < period) return ema;
            
            // First EMA is SMA
            let sum = 0;
            for (let i = 0; i < period; i++) {
                sum += data[i].close;
            }
            ema.push(sum / period);
            
            // Calculate rest with EMA formula
            for (let i = period; i < data.length; i++) {
                const value = data[i].close * k + ema[ema.length - 1] * (1 - k);
                ema.push(value);
            }
            
            return ema;
        }

        // Analyze EMA Direction and Turn Points
        function analyzeEMADirection(emaValues, times, threshold = 0.00001) {
            const analysis = [];
            let prevDirection = null;
            
            for (let i = 0; i < emaValues.length; i++) {
                const item = {
                    time: times[i],
                    emaValue: emaValues[i],
                    emadiff : 0,
                    emaDirection: null,
                    emaTurnType: null
                };
                
                if (i > 0) {
                    const diff = emaValues[i] - emaValues[i - 1];
					item.emadiff = diff;
                    
                    // กำหนด Direction
                    if (Math.abs(diff) < threshold) {
                        item.emaDirection = 'Parallel';
                    } else if (diff > 0) {
                        item.emaDirection = 'Up';
                    } else {
                        item.emaDirection = 'Down';
                    }
                    
                    // ตรวจสอบ Turn Point (ไม่สนใจ Parallel)
                    if (prevDirection && item.emaDirection !== 'Parallel' && prevDirection !== 'Parallel') {
                        if (prevDirection === 'Down' && item.emaDirection === 'Up') {
                            item.emaTurnType = 'TurnUp';
                        } else if (prevDirection === 'Up' && item.emaDirection === 'Down') {
                            item.emaTurnType = 'TurnDown';
                        }
                    }
                    
                    // อัพเดท prevDirection (ข้าม Parallel)
                    if (item.emaDirection !== 'Parallel') {
                        prevDirection = item.emaDirection;
                    }
                } else {
                    // แท่งแรก ไม่มี direction
                    item.emaDirection = 'N/A';
                }
                
                analysis.push(item);
            }
            
            return analysis;
        }
        
        // Count Turn Points from analysis
        function countTurnPoints(analysis) {
            const turnPoints = {
                turnUp: 0,
                turnDown: 0,
                points: []
            };
            
            analysis.forEach((item, index) => {
                if (item.emaTurnType === 'TurnUp') {
                    turnPoints.turnUp++;
                    turnPoints.points.push({
                        index: index,
                        type: 'turnUp',
                        value: item.emaValue,
                        time: item.time
                    });
                } else if (item.emaTurnType === 'TurnDown') {
                    turnPoints.turnDown++;
                    turnPoints.points.push({
                        index: index,
                        type: 'turnDown',
                        value: item.emaValue,
                        time: item.time
                    });
                }
            });
            
            return turnPoints;
        }

        // Fetch Deriv Data
        async function fetchDerivData(symbol, granularity, count) {
            const app_id = 1089;
            const ws = new WebSocket(`wss://ws.derivws.com/websockets/v3?app_id=${app_id}`);
            
            return new Promise((resolve, reject) => {
                const timeout = setTimeout(() => {
                    ws.close();
                    reject(new Error('Timeout'));
                }, 15000);

                ws.onopen = () => {
                    const end = Math.floor(Date.now() / 1000);
                    ws.send(JSON.stringify({
                        ticks_history: symbol,
                        adjust_start_time: 1,
                        count: count,
                        end: end,
                        style: 'candles',
                        granularity: parseInt(granularity)
                    }));
                };

                ws.onmessage = (msg) => {
                    const data = JSON.parse(msg.data);
                    
                    if (data.error) {
                        clearTimeout(timeout);
                        reject(new Error(data.error.message));
                        ws.close();
                        return;
                    }
                    
                    if (data.candles) {
                        clearTimeout(timeout);
                        resolve(data.candles);
                        ws.close();
                    }
                };

                ws.onerror = () => {
                    clearTimeout(timeout);
                    reject(new Error('WebSocket error'));
                    ws.close();
                };
            });
        }

        // Analyze Single Asset
        async function analyzeAsset(asset, group, emaPeriod, timeframe, candleCount) {
            try {
                const candles = await fetchDerivData(asset.symbol, timeframe, candleCount);
                
                const candleData = candles.map(c => ({
                    time: c.epoch + (7*3600),
                    open: parseFloat(c.open),
                    high: parseFloat(c.high),
                    low: parseFloat(c.low),
                    close: parseFloat(c.close)
                }));

                const emaValues = calculateEMA(candleData, emaPeriod);
                
                // Create times array for EMA (start from emaPeriod-1)
                const emaTimes = candleData.slice(emaPeriod - 1).map(c => c.time);
                
                // Analyze EMA with direction and turn points
                const emaAnalysis = analyzeEMADirection(emaValues, emaTimes);
				console.log('emaAnalysis',emaAnalysis)
				
                const turnPoints = countTurnPoints(emaAnalysis);
                
                const lastPrice = candleData[candleData.length - 1].close;
                const lastEmaItem = emaAnalysis[emaAnalysis.length - 1];
                const trend = lastEmaItem.emaDirection === 'Up' ? 'Up' : 
                             lastEmaItem.emaDirection === 'Down' ? 'Down' : 'Parallel';

                return {
                    asset: asset.name,
                    symbol: asset.symbol,
                    group: group,
                    turnUp: turnPoints.turnUp,
                    turnDown: turnPoints.turnDown,
                    total: turnPoints.turnUp + turnPoints.turnDown,
                    trend: trend,
                    lastPrice: lastPrice.toFixed(5),
                    candleData: candleData,
                    emaValues: emaValues,
                    emaAnalysis: emaAnalysis,
                    turnPoints: turnPoints.points
                };
            } catch (error) {
                console.error(`Error analyzing ${asset.name}:`, error);
                return {
                    asset: asset.name,
                    symbol: asset.symbol,
                    group: group,
                    turnUp: 0,
                    turnDown: 0,
                    total: 0,
                    trend: 'Error',
                    lastPrice: '-',
                    error: error.message
                };
            }
        }

        // Analyze All Assets
        async function analyzeAll() {
            if (selectedGroups.size === 0) {
                alert('⚠️ Please select at least one asset group!');
                return;
            }
            
            const emaPeriod = parseInt(document.getElementById('emaPeriod').value);
            const candleCount = parseInt(document.getElementById('candleCount').value);
            const timeframe = document.getElementById('timeframe').value;
            
            const analyzeBtn = document.getElementById('analyzeBtn');
            analyzeBtn.disabled = true;
            analyzeBtn.textContent = '⏳ Analyzing...';
            
            const status = document.getElementById('status');
            const statusText = document.getElementById('statusText');
            const progressBar = document.getElementById('progressBar');
            status.style.display = 'block';
            
            // Build asset list from selected groups
            const allAssets = [];
            selectedGroups.forEach(groupName => {
                const groupDisplayName = {
                    forex: 'Forex',
                    volatility: 'Volatility',
                    commodities: 'Commodities',
                    crypto: 'Crypto',
                    indices: 'Indices'
                }[groupName];
                
                ASSETS[groupName].forEach(asset => {
                    allAssets.push({ ...asset, group: groupDisplayName });
                });
            });
            
            const results = [];
            
            for (let i = 0; i < allAssets.length; i++) {
                const asset = allAssets[i];
                statusText.textContent = `Analyzing ${asset.name} (${i + 1}/${allAssets.length})...`;
                progressBar.style.width = `${((i + 1) / allAssets.length) * 100}%`;
                
                const result = await analyzeAsset(asset, asset.group, emaPeriod, timeframe, candleCount);
                results.push(result);
                currentResults[asset.symbol] = result;
            }
            
            displayResults(results);
            
            status.style.display = 'none';
            analyzeBtn.disabled = false;
            analyzeBtn.textContent = '🔍 Analyze Selected Groups';
        }

        // Display Results Table
        function displayResults(results) {
            const tbody = document.getElementById('resultsBody');
            tbody.innerHTML = '';
            
            // Sort by total turns (descending)
            results.sort((a, b) => b.total - a.total);
            
            results.forEach(result => {
                const row = document.createElement('tr');
                row.dataset.symbol = result.symbol;
                
                const trendColor = result.trend === 'Up' ? '#26a69a' : result.trend === 'Down' ? '#ef5350' : '#999';
                
                row.innerHTML = `
                    <td><strong>${result.asset}</strong></td>
                    <td>${result.group}</td>
                    <td class="turnup">${result.turnUp}</td>
                    <td class="turndown">${result.turnDown}</td>
                    <td class="total">${result.total}</td>
                    <td style="color: ${trendColor}; font-weight: 600;">${result.trend}</td>
                    <td>${result.lastPrice}</td>
                `;
                
                row.addEventListener('click', () => {
                    document.querySelectorAll('#resultsBody tr').forEach(r => r.classList.remove('selected'));
                    row.classList.add('selected');
                    displayChart(result);
                });
                
                tbody.appendChild(row);
            });
            
            document.getElementById('resultsSection').style.display = 'block';
        }

        // Display Chart
        function displayChart(result) {
            if (!result.candleData || result.error) {
                alert('Cannot display chart: ' + (result.error || 'No data'));
                return;
            }
            
            document.getElementById('chartSection').style.display = 'block';
            document.getElementById('chartTitle').textContent = `${result.asset} - EMA(${document.getElementById('emaPeriod').value}) - Turn Up: ${result.turnUp} | Turn Down: ${result.turnDown} | Total: ${result.total}`;
            
            if (!chart) initChart();
            
            candleSeries.setData(result.candleData);
            
            // Use emaAnalysis for accurate time alignment
            const emaData = result.emaAnalysis.map(item => ({
                time: item.time,
                value: item.emaValue
            }));
            
            emaSeries.setData(emaData);
            
            // Add markers for turn points
            const markers = result.turnPoints.map(point => ({
                time: point.time,
                position: point.type === 'turnUp' ? 'belowBar' : 'aboveBar',
                color: point.type === 'turnUp' ? '#26a69a' : '#ef5350',
                shape: point.type === 'turnUp' ? 'arrowUp' : 'arrowDown',
                text: point.type === 'turnUp' ? 'TU' : 'TD'
            }));
            
            candleSeries.setMarkers(markers);
            
            // Log analysis for debugging
            console.log('EMA Analysis Sample (first 10):');
            console.table(result.emaAnalysis.slice(0, 10));
            console.log('Turn Points:', result.turnPoints);
            
            chart.timeScale().fitContent();
        }

        // Event Listeners
        document.getElementById('analyzeBtn').addEventListener('click', analyzeAll);
        
        // Group Selection
        document.querySelectorAll('.asset-group').forEach(group => {
            group.addEventListener('click', () => {
                const groupName = group.dataset.group;
                if (selectedGroups.has(groupName)) {
                    selectedGroups.delete(groupName);
                    group.classList.remove('selected');
                } else {
                    selectedGroups.add(groupName);
                    group.classList.add('selected');
                }
                updateButtonText();
            });
        });
        
        document.getElementById('selectAllBtn').addEventListener('click', () => {
            document.querySelectorAll('.asset-group').forEach(group => {
                selectedGroups.add(group.dataset.group);
                group.classList.add('selected');
            });
            updateButtonText();
        });
        
        document.getElementById('clearSelectionBtn').addEventListener('click', () => {
            selectedGroups.clear();
            document.querySelectorAll('.asset-group').forEach(group => {
                group.classList.remove('selected');
            });
            updateButtonText();
        });
        
        function updateButtonText() {
            const analyzeBtn = document.getElementById('analyzeBtn');
            if (selectedGroups.size === 0) {
                analyzeBtn.textContent = '🔍 Analyze Selected Groups';
            } else {
                const totalAssets = Array.from(selectedGroups).reduce((sum, group) => {
                    return sum + ASSETS[group].length;
                }, 0);
                analyzeBtn.textContent = `🔍 Analyze ${selectedGroups.size} Group${selectedGroups.size > 1 ? 's' : ''} (${totalAssets} assets)`;
            }
        }
        
        // Initialize
        window.addEventListener('DOMContentLoaded', () => {
            initChart();
        });
    </script>
</body>
</html>