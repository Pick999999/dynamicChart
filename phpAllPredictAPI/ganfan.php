<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gann Fan Chart</title>
    <script src="https://unpkg.com/lightweight-charts@4.1.1/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #0f0f0f;
            color: #e0e0e0;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .controls {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .control-group label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
        }
        input[type="datetime-local"] {
            padding: 8px 12px;
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: #e0e0e0;
            border-radius: 4px;
            font-size: 14px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .checkbox-group label {
            cursor: pointer;
            font-size: 14px;
        }
        button {
            padding: 10px 20px;
            background: #2962ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        button:hover {
            background: #1e53e5;
        }
        button.secondary {
            background: #424242;
        }
        button.secondary:hover {
            background: #535353;
        }
        .time-btn {
            padding: 8px 12px;
            background: #2a2a2a;
            color: white;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            min-width: 40px;
        }
        .time-btn:hover {
            background: #3a3a3a;
        }
        #chart {
            background: #1a1a1a;
            border-radius: 8px;
            height: 600px;
        }
        .info {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="margin-bottom: 20px; font-size: 24px;">Gann Fan Chart - Deriv Data</h1>
        
        <div class="controls">
            <div class="control-group">
                <label>Asset</label>
                <select id="assetSelect" style="padding: 8px 12px; background: #2a2a2a; border: 1px solid #3a3a3a; color: #e0e0e0; border-radius: 4px; font-size: 14px;">
                    <option value="R_100">Volatility 100 Index</option>
                    <option value="R_75">Volatility 75 Index</option>
                    <option value="R_50">Volatility 50 Index</option>
                    <option value="R_25">Volatility 25 Index</option>
                    <option value="R_10">Volatility 10 Index</option>
                    <option value="frxEURUSD">EUR/USD</option>
                    <option value="frxGBPUSD">GBP/USD</option>
                    <option value="frxUSDJPY">USD/JPY</option>
                    <option value="frxAUDUSD">AUD/USD</option>
                    <option value="BTCUSD">Bitcoin</option>
                    <option value="ETHUSD">Ethereum</option>
                </select>
            </div>

            <div class="control-group">
                <label>Timeframe</label>
                <select id="timeframeSelect" style="padding: 8px 12px; background: #2a2a2a; border: 1px solid #3a3a3a; color: #e0e0e0; border-radius: 4px; font-size: 14px;">
                    <option value="60">1 นาที</option>
                    <option value="120">2 นาที</option>
                    <option value="180">3 นาที</option>
                    <option value="300">5 นาที</option>
                    <option value="600">10 นาที</option>
                    <option value="900">15 นาที</option>
                    <option value="1800">30 นาที</option>
                    <option value="3600" selected>1 ชั่วโมง</option>
                    <option value="7200">2 ชั่วโมง</option>
                    <option value="14400">4 ชั่วโมง</option>
                    <option value="28800">8 ชั่วโมง</option>
                    <option value="86400">1 วัน</option>
                </select>
            </div>

            
            <div class="control-group">
                <label>วันที่เริ่มต้น</label>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <button class="time-btn" onclick="adjustStartDate(-1)">-</button>
                    <input type="datetime-local" id="startDate">
                    <button class="time-btn" onclick="adjustStartDate(1)">+</button>
                </div>
            </div>
            
            <div class="control-group">
                <label>วันที่สิ้นสุด</label>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <button class="time-btn" onclick="adjustEndDate(-1)">-</button>
                    <input type="datetime-local" id="endDate">
                    <button class="time-btn" onclick="adjustEndDate(1)">+</button>
                </div>
            </div>
			<div class="control-group">
                <label>Scale Multiply</label>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <input type="text" id="ScaleMultiply" value=0.1>
                </div>
            </div>

            0.1
            <div class="checkbox-group">
                <input type="checkbox" id="useCurrentTime" checked>
                <label for="useCurrentTime">ใช้เวลาปัจจุบัน</label>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="enableGannFan" checked>
                <label for="enableGannFan">เปิดใช้ Gann Fan</label>
            </div>
            
            <button onclick="loadData()">โหลดข้อมูล</button>
            <button class="secondary" onclick="clearGannFan()">ลบ Gann Fan</button>
        </div>
        
        <div id="chart"></div>
        
        <div class="info">
            <strong>วิธีใช้:</strong> คลิกที่จุดใดๆ บนแท่งเทียน (candlestick) เพื่อวาด Gann Fan จากจุดนั้น<br>
            <strong>มุม Gann:</strong> 8x1 (แดง), 4x1 (ส้ม), 3x1 (เหลือง), 2x1 (เขียว), 1x1 (ฟ้า-หลัก 45°), 1x2 (ม่วง), 1x3 (ชมพู), 1x4 (น้ำตาล), 1x8 (เทา)
        </div>
    </div>

    <script>
        class GannFan {
            constructor(chart, series) {
                this.chart = chart;
                this.series = series;
                this.lineSeries = [];
                this.areaSeries = [];
                this.angles = [
                    { ratio: 8, color: '#ff5252', fillColor: 'rgba(255, 82, 82, 0.1)', name: '8x1' },
                    { ratio: 4, color: '#ff9800', fillColor: 'rgba(255, 152, 0, 0.1)', name: '4x1' },
                    { ratio: 3, color: '#ffeb3b', fillColor: 'rgba(255, 235, 59, 0.1)', name: '3x1' },
                    { ratio: 2, color: '#4caf50', fillColor: 'rgba(76, 175, 80, 0.1)', name: '2x1' },
                    { ratio: 1, color: '#2196f3', fillColor: 'rgba(33, 150, 243, 0.15)', name: '1x1' },
                    { ratio: 1/2, color: '#9c27b0', fillColor: 'rgba(156, 39, 176, 0.1)', name: '1x2' },
                    { ratio: 1/3, color: '#e91e63', fillColor: 'rgba(233, 30, 99, 0.1)', name: '1x3' },
                    { ratio: 1/4, color: '#795548', fillColor: 'rgba(121, 85, 72, 0.1)', name: '1x4' },
                    { ratio: 1/8, color: '#9e9e9e', fillColor: 'rgba(158, 158, 158, 0.1)', name: '1x8' }
                ];
            }

            draw(baseTime, basePrice, isUpTrend = true) {
                this.clear();
                
                const data = this.series.data();
                if (!data || data.length === 0) return;

                // หา index ของจุดฐาน
                let baseIndex = -1;
                for (let i = 0; i < data.length; i++) {
                    if (data[i].time === baseTime) {
                        baseIndex = i;
                        break;
                    }
                }

                if (baseIndex === -1 || baseIndex >= data.length - 1) return;

                // คำนวณ price range ของข้อมูลทั้งหมด
                let minPrice = data[0].low;
                let maxPrice = data[0].high;
                for (let i = 0; i < data.length; i++) {
                    if (data[i].low < minPrice) minPrice = data[i].low;
                    if (data[i].high > maxPrice) maxPrice = data[i].high;
                }
                const totalPriceRange = maxPrice - minPrice;
                
                // คำนวณจำนวนบาร์ที่เหลือ
                const remainingBars = data.length - baseIndex;
                
                // ปรับ scale ให้เหมาะสม: ใช้ 30% ของ price range ทั้งหมด
				const ScaleMultiply = document.getElementById("ScaleMultiply").value ;
                const scaleFactor = (totalPriceRange * ScaleMultiply) / remainingBars;
                
                // วาด Gann Fan สำหรับแต่ละมุม
                for (let i = 0; i < this.angles.length; i++) {
                    const angle = this.angles[i];
                    
                    // สร้าง line series
                    const lineSeries = this.chart.addLineSeries({
                        color: angle.color,
                        lineWidth: angle.ratio === 1 ? 3 : 2,
                        lineStyle: 0,
                        crosshairMarkerVisible: false,
                        lastValueVisible: false,
                        priceLineVisible: false,
                    });

                    // สร้างข้อมูลเส้น Gann
                    const lineData = [];
                    const direction = isUpTrend ? 1 : -1;
                    
                    for (let j = baseIndex; j < data.length; j++) {
                        const barsFromBase = j - baseIndex;
                        
                        // Gann Fan: ใช้ scale factor ที่ปรับแล้ว
                        const priceChange = barsFromBase * scaleFactor * angle.ratio * direction;
                        const price = basePrice + priceChange;
                        
                        lineData.push({
                            time: data[j].time,
                            value: price
                        });
                    }

                    lineSeries.setData(lineData);
                    this.lineSeries.push(lineSeries);
                }

                // วาดจุดฐาน
                const baseMarker = this.chart.addLineSeries({
                    color: '#ffffff',
                    lineWidth: 0,
                    crosshairMarkerVisible: true,
                    lastValueVisible: true,
                    priceLineVisible: false,
                });

                const markerData = [];
                for (let j = baseIndex; j < Math.min(baseIndex + 3, data.length); j++) {
                    markerData.push({ time: data[j].time, value: basePrice });
                }
                baseMarker.setData(markerData);
                baseMarker.setMarkers([{
                    time: baseTime,
                    position: 'inBar',
                    color: '#ffffff',
                    shape: 'circle',
                    text: 'Base'
                }]);
                
                this.lineSeries.push(baseMarker);
            }

            clear() {
                this.lineSeries.forEach(series => this.chart.removeSeries(series));
                this.areaSeries.forEach(series => this.chart.removeSeries(series));
                this.lineSeries = [];
                this.areaSeries = [];
            }
        }

        // สร้าง chart
        const chartContainer = document.getElementById('chart');
        const chart = LightweightCharts.createChart(chartContainer, {
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
                borderColor: '#3a3a3a',
            },
            timeScale: {
                borderColor: '#3a3a3a',
                timeVisible: true,
                secondsVisible: false,
            },
        });

        const candlestickSeries = chart.addCandlestickSeries({
            upColor: '#26a69a',
            downColor: '#ef5350',
            borderVisible: false,
            wickUpColor: '#26a69a',
            wickDownColor: '#ef5350',
        });

        const gannFan = new GannFan(chart, candlestickSeries);

        // ตั้งค่าวันที่เริ่มต้น
        const now = new Date();
        const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
        
        document.getElementById('startDate').value = formatDateTimeLocal(sevenDaysAgo);
        document.getElementById('endDate').value = formatDateTimeLocal(now);

        function formatDateTimeLocal(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // จัดการ checkbox
        document.getElementById('useCurrentTime').addEventListener('change', function(e) {
            const endDateInput = document.getElementById('endDate');
            endDateInput.disabled = e.target.checked;
            if (e.target.checked) {
                endDateInput.value = formatDateTimeLocal(new Date());
            }
        });

        document.getElementById('enableGannFan').addEventListener('change', function(e) {
            if (!e.target.checked) {
                gannFan.clear();
            }
        });

        // คลิกบน chart เพื่อวาด Gann Fan
        chart.subscribeClick((param) => {
            if (!document.getElementById('enableGannFan').checked) return;
            if (!param.point || !param.time) return;

            const data = param.seriesData.get(candlestickSeries);
            if (!data) return;

            const isUpTrend = data.close >= data.open;
            gannFan.draw(param.time, data.close, isUpTrend);
        });

        function clearGannFan() {
            gannFan.clear();
        }

        // ฟังก์ชันปรับวันที่
        function adjustStartDate(hours) {
            const startInput = document.getElementById('startDate');
            const currentDate = new Date(startInput.value);
            currentDate.setHours(currentDate.getHours() + hours);
            startInput.value = formatDateTimeLocal(currentDate);
        }

        function adjustEndDate(hours) {
            const endInput = document.getElementById('endDate');
            const currentDate = new Date(endInput.value);
            currentDate.setHours(currentDate.getHours() + hours);
            endInput.value = formatDateTimeLocal(currentDate);
        }

        // โหลดข้อมูลจาก Deriv
        async function loadData() {
            try {
                const asset = document.getElementById('assetSelect').value;
                const timeframe = parseInt(document.getElementById('timeframeSelect').value);
                const startDate = new Date(document.getElementById('startDate').value);
                const endDate = document.getElementById('useCurrentTime').checked 
                    ? new Date() 
                    : new Date(document.getElementById('endDate').value);

                const startTimestamp = Math.floor(startDate.getTime() / 1000);
                const endTimestamp = Math.floor(endDate.getTime() / 1000);

                const ws = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');

                ws.onopen = () => {
                    ws.send(JSON.stringify({
                        ticks_history: asset,
                        adjust_start_time: 1,
                        count: 1000,
                        end: endTimestamp,
                        start: startTimestamp,
                        style: 'candles',
                        granularity: timeframe
                    }));
                };

                ws.onmessage = (msg) => {
                    const data = JSON.parse(msg.data);
                    
                    if (data.candles) {
                        const candles = data.candles.map(candle => ({
                            time: candle.epoch,
                            open: parseFloat(candle.open),
                            high: parseFloat(candle.high),
                            low: parseFloat(candle.low),
                            close: parseFloat(candle.close)
                        }));

                        candlestickSeries.setData(candles);
                        chart.timeScale().fitContent();
                        ws.close();
                    }
                };

                ws.onerror = (error) => {
                    console.error('WebSocket Error:', error);
                    alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                };

            } catch (error) {
                console.error('Error loading data:', error);
                alert('เกิดข้อผิดพลาด: ' + error.message);
            }
        }

        // โหลดข้อมูลเริ่มต้น
        window.addEventListener('load', () => {
            setTimeout(loadData, 500);
        });

        // Responsive
        window.addEventListener('resize', () => {
            chart.applyOptions({
                width: chartContainer.clientWidth,
                height: 600
            });
        });
    </script>
</body>
</html>