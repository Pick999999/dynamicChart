<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Timeframe Analysis</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a1a;
            color: #fff;
            padding: 20px;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4a9eff;
        }

        .controls {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .control-group {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .control-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        label {
            font-size: 12px;
            color: #aaa;
            font-weight: 500;
        }

        select, input[type="datetime-local"], button {
            padding: 8px 12px;
            border: 1px solid #444;
            background: #333;
            color: #fff;
            border-radius: 4px;
            font-size: 14px;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #4a9eff;
        }

        button {
            background: #4a9eff;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            padding: 10px 24px;
        }

        button:hover {
            background: #3a8eef;
        }

        button:disabled {
            background: #555;
            cursor: not-allowed;
        }

        .indicator-checkbox {
            cursor: pointer;
            width: 16px;
            height: 16px;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .chart-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .chart-wrapper {
            background: #2a2a2a;
            border-radius: 8px;
            padding: 15px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #4a9eff;
        }

        .chart-info {
            font-size: 12px;
            color: #888;
        }

        .chart {
            width: 100%;
            height: 400px;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #4a9eff;
        }

        .error {
            background: #ff4444;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .info {
            background: #2a4a2a;
            color: #8f8;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Multiple Timeframe Analysis Tool</h1>
        
        <div class="controls">
            <div class="control-group">
                <div class="control-item">
                    <label>สินทรัพย์ (Asset)</label>
                    <select id="assetSelect">
                        <option value="R_10">Volatility 10 Index</option>
                        <option value="R_25">Volatility 25 Index</option>
                        <option value="R_50">Volatility 50 Index</option>
                        <option value="R_75">Volatility 75 Index</option>
                        <option value="R_100">Volatility 100 Index</option>
                        <option value="frxEURUSD">EUR/USD</option>
                        <option value="frxGBPUSD">GBP/USD</option>
                        <option value="frxUSDJPY">USD/JPY</option>
                    </select>
                </div>

                <div class="control-item">
                    <label>Timeframe A (ใหญ่)</label>
                    <select id="timeframeA">
                        <option value="180">3 นาที</option>
                        <option value="300">5 นาที</option>
                        <option value="600">10 นาที</option>
                        <option value="900" selected>15 นาที</option>
                        <option value="1800">30 นาที</option>
                    </select>
                </div>

                <div class="control-item">
                    <label>Timeframe B (เล็ก)</label>
                    <select id="timeframeB">
                        <option value="60">1 นาที</option>
                        <option value="180">3 นาที</option>
                        <option value="300" selected>5 นาที</option>
                        <option value="600">10 นาที</option>
                    </select>
                </div>

                <div class="control-item">
                    <label>แนวรับ-แนวต้าน</label>
                    <select id="srMethod">
                        <option value="none">ไม่แสดง</option>
                        <option value="swing" selected>Swing High/Low</option>
                        <option value="zones">Support/Resistance Zones</option>
                        <option value="fibonacci">Fibonacci Retracement</option>
                        <option value="pivot">Pivot Points</option>
                        <option value="all">แสดงทั้งหมด</option>
                    </select>
                </div>
            </div>

            <div class="control-group">
                <div class="control-item">
                    <label>Swing Lookback</label>
                    <input type="number" id="swingLookback" value="3" min="2" max="10" style="width: 80px;">
                </div>

                <div class="control-item">
                    <label>Tolerance (%)</label>
                    <input type="number" id="tolerance" value="0.1" min="0.01" max="1" step="0.01" style="width: 80px;">
                </div>
            </div>

            <div class="control-group">
                <div class="control-item">
                    <label>เวลาเริ่มต้น</label>
                    <input type="datetime-local" id="startTime">
                </div>

                <div class="control-item">
                    <label>เวลาสิ้นสุด</label>
                    <input type="datetime-local" id="endTime">
                </div>

                <button id="loadBtn">โหลดข้อมูล</button>
            </div>

            <div class="control-group" style="border-top: 1px solid #444; padding-top: 15px; margin-top: 5px;">
                <label style="font-weight: 600; color: #4a9eff; margin-right: 15px;">📊 Indicators:</label>
                
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                    <input type="checkbox" id="showBollinger" class="indicator-checkbox">
                    <span>Bollinger Bands</span>
                </label>

                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                    <input type="checkbox" id="showVolume" class="indicator-checkbox">
                    <span>Volume</span>
                </label>

                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                    <input type="checkbox" id="showATR" class="indicator-checkbox">
                    <span>ATR</span>
                </label>

                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                    <input type="checkbox" id="showEMA200" class="indicator-checkbox">
                    <span>EMA 200</span>
                </label>

                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                    <input type="checkbox" id="showVWAP" class="indicator-checkbox">
                    <span>VWAP</span>
                </label>
            </div>
        </div>

        <div id="messageArea"></div>

        <div class="charts-container">
            <div class="chart-wrapper">
                <div class="chart-header">
                    <span class="chart-title">📈 Graph A - Timeframe ใหญ่</span>
                    <span class="chart-info" id="chartAInfo">คลิกที่แท่งเทียนเพื่อดูรายละเอียด</span>
                </div>
                <div id="chartA" class="chart"></div>
            </div>

            <div class="chart-row">
                <div class="chart-wrapper">
                    <div class="chart-header">
                        <span class="chart-title">📉 Graph B - Timeframe เล็ก (ภายในแท่ง A)</span>
                        <span class="chart-info" id="chartBInfo">แสดงข้อมูลย่อยจาก Graph A</span>
                    </div>
                    <div id="chartB" class="chart"></div>
                </div>

                <div class="chart-wrapper">
                    <div class="chart-header">
                        <span class="chart-title">🔮 Graph C - แท่งถัดไป (ความต่อเนื่อง)</span>
                        <span class="chart-info" id="chartCInfo">แสดง 3-4 แท่งถัดไป</span>
                    </div>
                    <div id="chartC" class="chart"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize variables
        let chartA, chartB, chartC;
        let candleSeriesA, candleSeriesB, candleSeriesC;
        let ema3SeriesA, ema5SeriesA, ema200SeriesA;
        let bollingerUpperA, bollingerLowerA, bollingerMiddleA;
        let volumeSeriesA, atrSeriesA, vwapSeriesA;
        let referenceLine;
        let markerSeriesA;
        let supportResistanceLines = [];
        let dataA = [];
        let dataB = [];
        let selectedCandleTime = null;

        // Set default datetime values
        const now = new Date();
        const endDate = new Date(now);
        const startDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000); // 7 days ago
        
        document.getElementById('startTime').value = formatDateTimeLocal(startDate);
        document.getElementById('endTime').value = formatDateTimeLocal(endDate);

        function formatDateTimeLocal(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Initialize charts
        function initCharts() {
            const chartOptionsA = {
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
                timeScale: {
                    timeVisible: true,
                    secondsVisible: false,
                },
            };

            chartA = LightweightCharts.createChart(document.getElementById('chartA'), chartOptionsA);
            chartB = LightweightCharts.createChart(document.getElementById('chartB'), chartOptionsA);
            chartC = LightweightCharts.createChart(document.getElementById('chartC'), chartOptionsA);

            candleSeriesA = chartA.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });

            ema3SeriesA = chartA.addLineSeries({
                color: '#2196F3',
                lineWidth: 2,
                title: 'EMA 3',
            });

            ema5SeriesA = chartA.addLineSeries({
                color: '#FF6B6B',
                lineWidth: 2,
                title: 'EMA 5',
            });

            // Initialize optional indicators (hidden by default)
            ema200SeriesA = chartA.addLineSeries({
                color: '#9C27B0',
                lineWidth: 3,
                title: 'EMA 200',
                visible: false,
            });

            bollingerUpperA = chartA.addLineSeries({
                color: '#2196F3',
                lineWidth: 1,
                title: 'BB Upper',
                visible: false,
            });

            bollingerMiddleA = chartA.addLineSeries({
                color: '#FFC107',
                lineWidth: 1,
                lineStyle: 2,
                title: 'BB Middle',
                visible: false,
            });

            bollingerLowerA = chartA.addLineSeries({
                color: '#2196F3',
                lineWidth: 1,
                title: 'BB Lower',
                visible: false,
            });

            volumeSeriesA = chartA.addHistogramSeries({
                priceFormat: {
                    type: 'volume',
                },
                priceScaleId: 'volume',
                scaleMargins: {
                    top: 0.8,
                    bottom: 0,
                },
                visible: false,
            });

            atrSeriesA = chartA.addLineSeries({
                color: '#FF9800',
                lineWidth: 2,
                title: 'ATR',
                priceScaleId: 'atr',
                scaleMargins: {
                    top: 0.85,
                    bottom: 0,
                },
                visible: false,
            });

            vwapSeriesA = chartA.addLineSeries({
                color: '#00BCD4',
                lineWidth: 2,
                title: 'VWAP',
                visible: false,
            });

            candleSeriesB = chartB.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });

            candleSeriesC = chartC.addCandlestickSeries({
                upColor: '#4a9eff',
                downColor: '#ff9800',
                borderVisible: false,
                wickUpColor: '#4a9eff',
                wickDownColor: '#ff9800',
            });

            // Add reference line series for Graph C
            referenceLine = chartC.addLineSeries({
                color: '#FFA726',
                lineWidth: 2,
                lineStyle: 2, // Dashed line
                title: 'ราคาปิด Graph B',
                priceLineVisible: false,
                lastValueVisible: true,
            });

            // Add click handler for Chart A
            chartA.subscribeClick((param) => {
                if (param.time) {
                    handleCandleClick(param.time);
                }
            });
        }

        // Calculate EMA
        function calculateEMA(data, period) {
            const ema = [];
            const k = 2 / (period + 1);
            let emaValue = data[0].close;

            for (let i = 0; i < data.length; i++) {
                if (i === 0) {
                    emaValue = data[i].close;
                } else {
                    emaValue = data[i].close * k + emaValue * (1 - k);
                }
                ema.push({ time: data[i].time, value: emaValue });
            }

            return ema;
        }

        // Calculate Bollinger Bands
        function calculateBollingerBands(data, period = 20, stdDev = 2) {
            const sma = [];
            const upper = [];
            const lower = [];
            
            for (let i = 0; i < data.length; i++) {
                if (i < period - 1) continue;
                
                const slice = data.slice(i - period + 1, i + 1);
                const mean = slice.reduce((sum, d) => sum + d.close, 0) / period;
                const variance = slice.reduce((sum, d) => sum + Math.pow(d.close - mean, 2), 0) / period;
                const std = Math.sqrt(variance);
                
                sma.push({ time: data[i].time, value: mean });
                upper.push({ time: data[i].time, value: mean + stdDev * std });
                lower.push({ time: data[i].time, value: mean - stdDev * std });
            }
            
            return { sma, upper, lower };
        }

        // Calculate ATR (Average True Range)
        function calculateATR(data, period = 14) {
            const atr = [];
            let atrValue = 0;
            
            for (let i = 1; i < data.length; i++) {
                const high = data[i].high;
                const low = data[i].low;
                const prevClose = data[i - 1].close;
                
                const tr = Math.max(
                    high - low,
                    Math.abs(high - prevClose),
                    Math.abs(low - prevClose)
                );
                
                if (i === 1) {
                    atrValue = tr;
                } else {
                    atrValue = ((atrValue * (period - 1)) + tr) / period;
                }
                
                if (i >= period) {
                    atr.push({ time: data[i].time, value: atrValue });
                }
            }
            
            return atr;
        }

        // Calculate VWAP (Volume Weighted Average Price)
        function calculateVWAP(data) {
            const vwap = [];
            let cumulativeTPV = 0;
            let cumulativeVolume = 0;
            
            for (let i = 0; i < data.length; i++) {
                const typical = (data[i].high + data[i].low + data[i].close) / 3;
                const volume = data[i].volume || 1000; // Use dummy volume if not available
                
                cumulativeTPV += typical * volume;
                cumulativeVolume += volume;
                
                vwap.push({
                    time: data[i].time,
                    value: cumulativeTPV / cumulativeVolume
                });
            }
            
            return vwap;
        }

        // Convert candlestick data to histogram data for volume
        function convertToVolumeData(data) {
            return data.map(d => ({
                time: d.time,
                value: d.volume || 1000,
                color: d.close >= d.open ? 'rgba(38, 166, 154, 0.5)' : 'rgba(239, 83, 80, 0.5)'
            }));
        }

        // Find Swing High and Low points
        function findSwingPoints(data, lookback = 3) {
            const swingHighs = [];
            const swingLows = [];
            
            for (let i = lookback; i < data.length - lookback; i++) {
                let isSwingHigh = true;
                let isSwingLow = true;
                
                for (let j = 1; j <= lookback; j++) {
                    if (data[i].high <= data[i-j].high || data[i].high <= data[i+j].high) {
                        isSwingHigh = false;
                    }
                    if (data[i].low >= data[i-j].low || data[i].low >= data[i+j].low) {
                        isSwingLow = false;
                    }
                }
                
                if (isSwingHigh) swingHighs.push({time: data[i].time, price: data[i].high, type: 'resistance'});
                if (isSwingLow) swingLows.push({time: data[i].time, price: data[i].low, type: 'support'});
            }
            
            return [...swingHighs, ...swingLows];
        }

        // Find key levels by clustering
        function findKeyLevels(swingPoints, tolerance = 0.001) {
            const levels = [];
            
            swingPoints.forEach(point => {
                let foundCluster = false;
                
                for (let level of levels) {
                    if (Math.abs(point.price - level.price) / level.price < tolerance) {
                        level.count++;
                        level.price = (level.price * (level.count - 1) + point.price) / level.count;
                        if (!level.times.includes(point.time)) {
                            level.times.push(point.time);
                        }
                        foundCluster = true;
                        break;
                    }
                }
                
                if (!foundCluster) {
                    levels.push({
                        price: point.price, 
                        count: 1, 
                        type: point.type,
                        times: [point.time]
                    });
                }
            });
            
            return levels.filter(l => l.count >= 2).sort((a, b) => b.count - a.count);
        }

        // Calculate Fibonacci levels
        function calculateFibonacci(data) {
            if (data.length < 2) return [];
            
            const high = Math.max(...data.map(d => d.high));
            const low = Math.min(...data.map(d => d.low));
            const diff = high - low;
            
            const levels = [
                {price: high, label: '0%', type: 'fib'},
                {price: high - diff * 0.236, label: '23.6%', type: 'fib'},
                {price: high - diff * 0.382, label: '38.2%', type: 'fib'},
                {price: high - diff * 0.5, label: '50%', type: 'fib'},
                {price: high - diff * 0.618, label: '61.8%', type: 'fib'},
                {price: high - diff * 0.786, label: '78.6%', type: 'fib'},
                {price: low, label: '100%', type: 'fib'}
            ];
            
            return levels;
        }

        // Calculate Pivot Points
        function calculatePivotPoints(data) {
            if (data.length < 1) return [];
            
            const lastCandle = data[data.length - 1];
            const high = lastCandle.high;
            const low = lastCandle.low;
            const close = lastCandle.close;
            
            const pivot = (high + low + close) / 3;
            const r1 = 2 * pivot - low;
            const r2 = pivot + (high - low);
            const r3 = high + 2 * (pivot - low);
            const s1 = 2 * pivot - high;
            const s2 = pivot - (high - low);
            const s3 = low - 2 * (high - pivot);
            
            return [
                {price: r3, label: 'R3', type: 'resistance'},
                {price: r2, label: 'R2', type: 'resistance'},
                {price: r1, label: 'R1', type: 'resistance'},
                {price: pivot, label: 'PP', type: 'pivot'},
                {price: s1, label: 'S1', type: 'support'},
                {price: s2, label: 'S2', type: 'support'},
                {price: s3, label: 'S3', type: 'support'}
            ];
        }

        // Create Support/Resistance Zones
        function createSRZones(data, tolerance = 0.002) {
            const swingPoints = findSwingPoints(data, parseInt(document.getElementById('swingLookback').value));
            const keyLevels = findKeyLevels(swingPoints, tolerance);
            
            return keyLevels.map(level => ({
                price: level.price,
                count: level.count,
                type: level.type,
                zone: {
                    upper: level.price * (1 + tolerance),
                    lower: level.price * (1 - tolerance)
                }
            }));
        }

        // Draw Support/Resistance on Chart
        function drawSupportResistance(method) {
            // Clear existing lines
            supportResistanceLines.forEach(line => {
                if (line && line.series) {
                    chartA.removeSeries(line.series);
                }
            });
            supportResistanceLines = [];

            if (method === 'none' || dataA.length === 0) return;

            const tolerance = parseFloat(document.getElementById('tolerance').value) / 100;
            let levels = [];

            if (method === 'swing' || method === 'all') {
                const swingPoints = findSwingPoints(dataA, parseInt(document.getElementById('swingLookback').value));
                const keyLevels = findKeyLevels(swingPoints, tolerance);
                
                keyLevels.slice(0, 8).forEach(level => {
                    const color = level.type === 'support' ? '#4CAF50' : '#F44336';
                    const series = chartA.addLineSeries({
                        color: color,
                        lineWidth: 2,
                        lineStyle: 2,
                        priceLineVisible: false,
                        lastValueVisible: false,
                    });
                    
                    const lineData = [
                        {time: dataA[0].time, value: level.price},
                        {time: dataA[dataA.length - 1].time, value: level.price}
                    ];
                    series.setData(lineData);
                    
                    supportResistanceLines.push({series, level});
                });
            }

            if (method === 'zones' || method === 'all') {
                const zones = createSRZones(dataA, tolerance);
                
                zones.slice(0, 5).forEach(zone => {
                    const color = zone.type === 'support' ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)';
                    
                    // Draw upper line
                    const upperSeries = chartA.addLineSeries({
                        color: zone.type === 'support' ? '#4CAF50' : '#F44336',
                        lineWidth: 1,
                        lineStyle: 0,
                        priceLineVisible: false,
                        lastValueVisible: false,
                    });
                    upperSeries.setData([
                        {time: dataA[0].time, value: zone.zone.upper},
                        {time: dataA[dataA.length - 1].time, value: zone.zone.upper}
                    ]);
                    
                    // Draw lower line
                    const lowerSeries = chartA.addLineSeries({
                        color: zone.type === 'support' ? '#4CAF50' : '#F44336',
                        lineWidth: 1,
                        lineStyle: 0,
                        priceLineVisible: false,
                        lastValueVisible: false,
                    });
                    lowerSeries.setData([
                        {time: dataA[0].time, value: zone.zone.lower},
                        {time: dataA[dataA.length - 1].time, value: zone.zone.lower}
                    ]);
                    
                    supportResistanceLines.push({series: upperSeries, level: zone});
                    supportResistanceLines.push({series: lowerSeries, level: zone});
                });
            }

            if (method === 'fibonacci' || method === 'all') {
                const fibLevels = calculateFibonacci(dataA);
                const fibColors = ['#9C27B0', '#673AB7', '#3F51B5', '#2196F3', '#03A9F4', '#00BCD4', '#009688'];
                
                fibLevels.forEach((level, idx) => {
                    const series = chartA.addLineSeries({
                        color: fibColors[idx % fibColors.length],
                        lineWidth: 1,
                        lineStyle: 2,
                        priceLineVisible: false,
                        lastValueVisible: false,
                    });
                    
                    series.setData([
                        {time: dataA[0].time, value: level.price},
                        {time: dataA[dataA.length - 1].time, value: level.price}
                    ]);
                    
                    supportResistanceLines.push({series, level});
                });
            }

            if (method === 'pivot' || method === 'all') {
                const pivotLevels = calculatePivotPoints(dataA);
                
                pivotLevels.forEach(level => {
                    let color = '#FFC107';
                    if (level.type === 'resistance') color = '#F44336';
                    if (level.type === 'support') color = '#4CAF50';
                    if (level.type === 'pivot') color = '#FF9800';
                    
                    const series = chartA.addLineSeries({
                        color: color,
                        lineWidth: 2,
                        lineStyle: 1,
                        priceLineVisible: false,
                        lastValueVisible: false,
                    });
                    
                    series.setData([
                        {time: dataA[0].time, value: level.price},
                        {time: dataA[dataA.length - 1].time, value: level.price}
                    ]);
                    
                    supportResistanceLines.push({series, level});
                });
            }
        }

        // Show message
        function showMessage(message, type = 'info') {
            const messageArea = document.getElementById('messageArea');
            messageArea.innerHTML = `<div class="${type}">${message}</div>`;
            setTimeout(() => {
                messageArea.innerHTML = '';
            }, 5000);
        }

        // Fetch data from Deriv API
        async function fetchDerivData(symbol, granularity, start, end) {
            try {
                const startTimestamp = Math.floor(start.getTime() / 1000);
                const endTimestamp = Math.floor(end.getTime() / 1000);
                
                const ws = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');
                
                return new Promise((resolve, reject) => {
                    ws.onopen = () => {
                        ws.send(JSON.stringify({
                            ticks_history: symbol,
                            adjust_start_time: 1,
                            count: 5000,
                            end: endTimestamp,
                            start: startTimestamp,
                            style: 'candles',
                            granularity: granularity
                        }));
                    };

                    ws.onmessage = (msg) => {
                        const data = JSON.parse(msg.data);
                        
                        if (data.error) {
                            reject(new Error(data.error.message));
                            ws.close();
                            return;
                        }

                        if (data.candles) {
                            const candles = data.candles.map(candle => ({
                                time: candle.epoch,
                                open: parseFloat(candle.open),
                                high: parseFloat(candle.high),
                                low: parseFloat(candle.low),
                                close: parseFloat(candle.close),
                            }));
                            resolve(candles);
                            ws.close();
                        }
                    };

                    ws.onerror = (error) => {
                        reject(new Error('WebSocket error'));
                        ws.close();
                    };

                    setTimeout(() => {
                        reject(new Error('Request timeout'));
                        ws.close();
                    }, 30000);
                });
            } catch (error) {
                throw error;
            }
        }

        // Handle candle click
        function handleCandleClick(time) {
            selectedCandleTime = time;
            
            // Find the selected candle in dataA
            const selectedCandle = dataA.find(c => c.time === time);
            if (!selectedCandle) return;

            // Add marker to the selected candle
            candleSeriesA.setMarkers([{
                time: time,
                position: 'aboveBar',
                color: '#f68410',
                shape: 'circle',
                text: '●'
            }]);

            // Get timeframes
            const tfA = parseInt(document.getElementById('timeframeA').value);
            const tfB = parseInt(document.getElementById('timeframeB').value);

            if (tfB >= tfA) {
                showMessage('⚠️ Timeframe B ต้องเล็กกว่า Timeframe A', 'error');
                return;
            }

            // Calculate how many B candles fit in one A candle
            const ratio = tfA / tfB;

            // Find corresponding B candles (within the selected A candle)
            const startTime = time;
            const endTime = time + tfA;
            
            const bCandles = dataB.filter(c => c.time >= startTime && c.time < endTime);

            if (bCandles.length > 0) {
                candleSeriesB.setData(bCandles);
                chartB.timeScale().fitContent();
                document.getElementById('chartBInfo').textContent = 
                    `แสดง ${bCandles.length} แท่งเทียนในช่วงเวลาที่เลือก (คาดหวัง ${ratio} แท่ง)`;

                // Find next 3-4 candles after the B candles for Graph C
                const lastBCandleTime = bCandles[bCandles.length - 1].time;
                const nextStartTime = lastBCandleTime + tfB;
                const nextEndTime = nextStartTime + (4 * tfB); // Get up to 4 candles

                const cCandles = dataB.filter(c => c.time >= nextStartTime && c.time < nextEndTime);

                if (cCandles.length > 0) {
                    candleSeriesC.setData(cCandles);
                    
                    // Get the close price of the last candle in Graph B
                    const lastBCandleClose = bCandles[bCandles.length - 1].close;
                    
                    // Create reference line data spanning across Graph C
                    const referenceLineData = cCandles.map(c => ({
                        time: c.time,
                        value: lastBCandleClose
                    }));
                    
                    referenceLine.setData(referenceLineData);
                    
                    chartC.timeScale().fitContent();
                    document.getElementById('chartCInfo').textContent = 
                        `แสดง ${cCandles.length} แท่งถัดไป (เส้นส้ม: ราคาปิด ${lastBCandleClose.toFixed(2)})`;
                } else {
                    candleSeriesC.setData([]);
                    referenceLine.setData([]);
                    document.getElementById('chartCInfo').textContent = 
                        '⚠️ ไม่มีข้อมูลแท่งถัดไป';
                }
            } else {
                showMessage('⚠️ ไม่พบข้อมูล Timeframe B ในช่วงเวลาที่เลือก', 'error');
                candleSeriesC.setData([]);
                referenceLine.setData([]);
            }
        }

        // Load data
        async function loadData() {
            const loadBtn = document.getElementById('loadBtn');
            const asset = document.getElementById('assetSelect').value;
            const timeframeA = parseInt(document.getElementById('timeframeA').value);
            const timeframeB = parseInt(document.getElementById('timeframeB').value);
            const startTime = new Date(document.getElementById('startTime').value);
            const endTime = new Date(document.getElementById('endTime').value);

            if (!startTime || !endTime) {
                showMessage('⚠️ กรุณาเลือกวันเวลาให้ครบถ้วน', 'error');
                return;
            }

            if (startTime >= endTime) {
                showMessage('⚠️ เวลาเริ่มต้นต้องน้อยกว่าเวลาสิ้นสุด', 'error');
                return;
            }

            if (timeframeB >= timeframeA) {
                showMessage('⚠️ Timeframe B ต้องเล็กกว่า Timeframe A', 'error');
                return;
            }

            loadBtn.disabled = true;
            loadBtn.textContent = 'กำลังโหลด...';

            try {
                showMessage('🔄 กำลังโหลดข้อมูล Timeframe A...', 'info');
                dataA = await fetchDerivData(asset, timeframeA, startTime, endTime);
                
                showMessage('🔄 กำลังโหลดข้อมูล Timeframe B...', 'info');
                dataB = await fetchDerivData(asset, timeframeB, startTime, endTime);

                if (dataA.length === 0) {
                    throw new Error('ไม่พบข้อมูล Timeframe A');
                }

                if (dataB.length === 0) {
                    throw new Error('ไม่พบข้อมูล Timeframe B');
                }

                // Set data for Chart A
                candleSeriesA.setData(dataA);

                // Calculate and set EMAs
                const ema3 = calculateEMA(dataA, 3);
                const ema5 = calculateEMA(dataA, 5);
                ema3SeriesA.setData(ema3);
                ema5SeriesA.setData(ema5);

                // Calculate optional indicators
                const ema200 = calculateEMA(dataA, 200);
                ema200SeriesA.setData(ema200);

                const bollinger = calculateBollingerBands(dataA, 20, 2);
                bollingerUpperA.setData(bollinger.upper);
                bollingerMiddleA.setData(bollinger.sma);
                bollingerLowerA.setData(bollinger.lower);

                const volumeData = convertToVolumeData(dataA);
                volumeSeriesA.setData(volumeData);

                const atr = calculateATR(dataA, 14);
                atrSeriesA.setData(atr);

                const vwap = calculateVWAP(dataA);
                vwapSeriesA.setData(vwap);

                // Apply checkbox states
                updateIndicatorVisibility();

                // Fit content
                chartA.timeScale().fitContent();

                // Draw Support/Resistance
                const srMethod = document.getElementById('srMethod').value;
                drawSupportResistance(srMethod);

                document.getElementById('chartAInfo').textContent = 
                    `แสดง ${dataA.length} แท่งเทียน - คลิกเพื่อดูรายละเอียด`;

                showMessage(`✅ โหลดข้อมูลสำเร็จ! Chart A: ${dataA.length} แท่ง, Chart B: ${dataB.length} แท่ง`, 'info');

            } catch (error) {
                showMessage(`❌ เกิดข้อผิดพลาด: ${error.message}`, 'error');
                console.error('Error loading data:', error);
            } finally {
                loadBtn.disabled = false;
                loadBtn.textContent = 'โหลดข้อมูล';
            }
        }

        // Update indicator visibility based on checkboxes
        function updateIndicatorVisibility() {
            const showBollinger = document.getElementById('showBollinger').checked;
            const showVolume = document.getElementById('showVolume').checked;
            const showATR = document.getElementById('showATR').checked;
            const showEMA200 = document.getElementById('showEMA200').checked;
            const showVWAP = document.getElementById('showVWAP').checked;

            if (bollingerUpperA && bollingerMiddleA && bollingerLowerA) {
                bollingerUpperA.applyOptions({ visible: showBollinger });
                bollingerMiddleA.applyOptions({ visible: showBollinger });
                bollingerLowerA.applyOptions({ visible: showBollinger });
            }

            if (volumeSeriesA) {
                volumeSeriesA.applyOptions({ visible: showVolume });
            }

            if (atrSeriesA) {
                atrSeriesA.applyOptions({ visible: showATR });
            }

            if (ema200SeriesA) {
                ema200SeriesA.applyOptions({ visible: showEMA200 });
            }

            if (vwapSeriesA) {
                vwapSeriesA.applyOptions({ visible: showVWAP });
            }
        }

        // Event listeners
        document.getElementById('loadBtn').addEventListener('click', loadData);
        
        document.getElementById('srMethod').addEventListener('change', function() {
            if (dataA.length > 0) {
                drawSupportResistance(this.value);
            }
        });
        
        document.getElementById('swingLookback').addEventListener('change', function() {
            const method = document.getElementById('srMethod').value;
            if (dataA.length > 0 && (method === 'swing' || method === 'zones' || method === 'all')) {
                drawSupportResistance(method);
            }
        });
        
        document.getElementById('tolerance').addEventListener('change', function() {
            const method = document.getElementById('srMethod').value;
            if (dataA.length > 0 && method !== 'none') {
                drawSupportResistance(method);
            }
        });

        // Indicator checkboxes
        document.querySelectorAll('.indicator-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateIndicatorVisibility);
        });

        // Initialize charts on load
        initCharts();
    </script>
</body>
</html>