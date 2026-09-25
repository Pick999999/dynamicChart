<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Timeframe Analysis Tool</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
	
	
	<script src="autoSaveInputs.js" ></script>
	<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
	

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a1a; color: #fff; padding: 20px; }
        .container { max-width: 1600px; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 20px; color: #4a9eff; }
        
        .controls { background: #2a2a2a; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .control-group { display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; align-items: center; }
        .control-item { display: flex; flex-direction: column; gap: 5px; }
        label { font-size: 12px; color: #aaa; font-weight: 500; }
        select, input[type="datetime-local"], input[type="number"], button {
            padding: 8px 12px; border: 1px solid #444; background: #333; color: #fff;
            border-radius: 4px; font-size: 14px;
        }
        select:focus, input:focus { outline: none; border-color: #4a9eff; }
        button { background: #4a9eff; border: none; cursor: pointer; font-weight: 600; transition: background 0.3s; }
        button:hover { background: #3a8eef; }
        button:disabled { background: #555; cursor: not-allowed; }
        .analyze-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-left: 10px; }
        
        .charts-container { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .chart-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .chart-wrapper { background: #2a2a2a; border-radius: 8px; padding: 15px; position: relative; }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .chart-title { font-size: 18px; font-weight: 600; color: #4a9eff; }
        .chart-info { font-size: 12px; color: #888; }
        .chart { width: 100%; height: 400px; }
        
        .error { background: #ff4444; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .info { background: #2a4a2a; color: #8f8; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        
        .signal-panel { background: rgba(0,0,0,0.9); padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 2px solid #4a9eff; }
        .signal-badge { font-size: 24px; font-weight: bold; padding: 10px 20px; border-radius: 8px; }
        .signal-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 15px 0; }
        .metric-box { background: rgba(255,255,255,0.05); padding: 10px; border-radius: 5px; text-align: center; }
        .metric-label { font-size: 12px; color: #888; margin-bottom: 5px; }
        .metric-value { font-size: 20px; font-weight: bold; color: #4a9eff; }
        
        .reasons-list { list-style: none; padding: 0; margin: 15px 0; }
        .reasons-list li { padding: 8px; margin: 5px 0; background: rgba(255,255,255,0.03); border-radius: 4px; border-left: 3px solid #4a9eff; font-size: 13px; }
        
        .trade-plan-box { background: rgba(74, 158, 255, 0.1); padding: 15px; border-radius: 8px; margin-top: 15px; }
        .trade-plan-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px; }
        .trade-plan-item { padding: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; }
        .trade-plan-label { font-size: 11px; color: #888; }
        .trade-plan-value { font-size: 15px; font-weight: bold; color: #00d2ff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Multiple Timeframe Analysis Tool</h1>
        
        <div class="controls">
            <div class="control-group">
                <div class="control-item">
                    <label>สินทรัพย์</label>
                    <select id="asset">
                        <option value="R_10">Volatility 10</option>
                        <option value="R_50">Volatility 50</option>
                        <option value="frxEURUSD">EUR/USD</option>
                    </select>
                </div>
                <div class="control-item">
                    <label>TF A (ใหญ่)</label>
                    <select id="tfA">
                        <option value="300">5 นาที</option>
                        <option value="900" selected>15 นาที</option>
                        <option value="1800">30 นาที</option>
                    </select>
                </div>
                <div class="control-item">
                    <label>TF B (เล็ก)</label>
                    <select id="tfB">
                        <option value="60">1 นาที</option>
                        <option value="300" selected>5 นาที</option>
                        <option value="600">10 นาที</option>
                    </select>
                </div>
                <div class="control-item">
                    <label>เริ่มต้น</label>
                    <input type="datetime-local" id="start">
                </div>
                <div class="control-item">
                    <label>สิ้นสุด</label>
                    <input type="datetime-local" id="end">
                </div>
                <button id="loadBtn">โหลด</button>
                <button id="analyzeBtn" class="analyze-btn" disabled>วิเคราะห์</button>
            </div>
        </div>

        <div id="msg"></div>
        <div id="signalPanel" style="display:none;"></div>

        <div class="charts-container">
            <div class="chart-wrapper">
                <div class="chart-header">
                    <span class="chart-title">Graph A</span>
                    <span class="chart-info" id="infoA">-</span>
                </div>
                <div id="chartA" class="chart"></div>
            </div>
            <div class="chart-row">
                <div class="chart-wrapper">
                    <div class="chart-header">
                        <span class="chart-title">Graph B</span>
                        <span class="chart-info" id="infoB">-</span>
                    </div>
                    <div id="chartB" class="chart"></div>
                </div>
                <div class="chart-wrapper">
                    <div class="chart-header">
                        <span class="chart-title">Graph C</span>
                        <span class="chart-info" id="infoC">-</span>
                    </div>
                    <div id="chartC" class="chart"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== GLOBAL VARIABLES =====
        let chartA, chartB, chartC, seriesA, seriesB, seriesC, refLine, emaSeries,emaSeries2 ;
        let dataA = [], dataB = [];
        let selectedCandleIndex = null;
        
        // ===== INITIALIZATION =====
        (function init() {
            setDefaultDates();
            createCharts();
            setupEventListeners();
        })();

        function setDefaultDates() {
            const now = new Date();
            const week = 7 * 24 * 60 * 60 * 1000;
            document.getElementById('end').value = formatDate(now);
            document.getElementById('start').value = formatDate(new Date(now - week));
        } 

		// Calculate EMA
        function calculateEMA(data, period,useMaxOrClose) {
            const ema = [];
			
            const k = 2 / (period + 1);
			if (useMaxOrClose ==='open') {
				let emaValue = data[0].open;
			} else  {
                let emaValue = data[0].close;
		    }

            for (let i = 0; i < data.length; i++) {
                if (i === 0) {
					if (useMaxOrClose =='open') {
                      emaValue = data[i].open;
					} else {
                      emaValue = data[i].close;
					}
                } else {
				     if (useMaxOrClose =='open') {
                       console.log('high')
                       emaValue = data[i].open * k + emaValue * (1 - k);
					 } else {
                       console.log('close')
                       emaValue = data[i].close * k + emaValue * (1 - k);
					 }
                }
                ema.push({ time: data[i].time, value: emaValue });
            }

            return ema;
        }

        function formatDate(d) {
            return d.getFullYear() + '-' + 
                   String(d.getMonth() + 1).padStart(2, '0') + '-' +
                   String(d.getDate()).padStart(2, '0') + 'T' +
                   String(d.getHours()).padStart(2, '0') + ':' +
                   String(d.getMinutes()).padStart(2, '0');
        }

        function createCharts() {
            const opts = {
                layout: { background: { color: '#1a1a1a' }, textColor: '#d1d4dc' },
                grid: { vertLines: { color: '#2a2a2a' }, horzLines: { color: '#2a2a2a' } },
                timeScale: { timeVisible: true }
            };
            
            chartA = LightweightCharts.createChart(document.getElementById('chartA'), opts);
            chartB = LightweightCharts.createChart(document.getElementById('chartB'), opts);
            chartC = LightweightCharts.createChart(document.getElementById('chartC'), opts);
            
            seriesA = chartA.addCandlestickSeries({ upColor: '#00C851', downColor: '#ef5350' });
            emaSeries = chartA.addLineSeries({
                color: '#ff0000',
                lineWidth: 2,
                title: 'EMA',
            });

            emaSeries2 = chartA.addLineSeries({
                color: '#ffffff',
                lineWidth: 2,
                title: 'EMA',
            });


            seriesB = chartB.addCandlestickSeries({ upColor: '#00C851', downColor: '#ef5350' });
            seriesC = chartC.addCandlestickSeries({ upColor: '#00C851', downColor: '#ef5350' });
            
            refLine = chartC.addLineSeries({ color: '#FFA726', lineWidth: 2, lineStyle: 2 }); 
            
            // 👇 เพิ่ม Tooltips
            createCandleTooltip(document.getElementById('chartA'), chartA, seriesA, 'Graph A');
            createCandleTooltip(document.getElementById('chartB'), chartB, seriesB, 'Graph B');
            createCandleTooltip(document.getElementById('chartC'), chartC, seriesC, 'Graph C');

			
            
            chartA.subscribeClick(param => {
                if (param.time) {
                    handleClick(param.time);
                }
            });
        }

        function setupEventListeners() {
            document.getElementById('loadBtn').onclick = loadData;
            document.getElementById('analyzeBtn').onclick = analyze;
        }

        // ===== DATA LOADING =====
        async function loadData() {
            const asset = document.getElementById('asset').value;
            const tfA = parseInt(document.getElementById('tfA').value);
            const tfB = parseInt(document.getElementById('tfB').value);
            const start = new Date(document.getElementById('start').value);
            const end = new Date(document.getElementById('end').value);

            if (tfB >= tfA) return msg('TF B ต้องเล็กกว่า TF A', 'error');

            const btn = document.getElementById('loadBtn');
            btn.disabled = true;
            btn.textContent = 'กำลังโหลด...';

            try {
                msg('โหลด TF A...', 'info');
                dataA = await fetchData(asset, tfA, start, end);
                msg('โหลด TF B...', 'info');
                dataB = await fetchData(asset, tfB, start, end);
                
                seriesA.setData(dataA);

                chartA.timeScale().fitContent();
                const emaValues1 = calculateEMA(dataA, 5,'close');
                //console.log('emaShort-',emaShortValues) ;
				emaSeries.setData(emaValues1);

				//const emaValues2 = calculateEMA(dataA, 5,'high');                
				const emaValues2 = calculateEMA(dataA, 5,'open');                
				console.log('emaValues2',emaValues2);
				
				emaSeries2.setData(emaValues2);


                
                selectedCandleIndex = null;
                
                document.getElementById('infoA').textContent = `${dataA.length} แท่ง - คลิกเพื่อเลือกแท่งวิเคราะห์`;
                document.getElementById('analyzeBtn').disabled = false;
                msg(`โหลดสำเร็จ! A:${dataA.length} B:${dataB.length}`, 'info');
            } catch (e) {
                msg('Error: ' + e.message, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'โหลด';
            }
        }

        function fetchData(symbol, granularity, start, end) {
            return new Promise((resolve, reject) => {
                const ws = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');
                ws.onopen = () => ws.send(JSON.stringify({
                    ticks_history: symbol,
                    adjust_start_time: 1,
                    count: 5000,
                    end: Math.floor(end / 1000),
                    start: Math.floor(start / 1000),
                    style: 'candles',
                    granularity
                }));
                ws.onmessage = msg => {
                    const d = JSON.parse(msg.data);
                    if (d.error) reject(new Error(d.error.message));
                    if (d.candles) {
                        resolve(d.candles.map(c => ({
                            time: c.epoch,
                            open: parseFloat(c.open),
                            high: parseFloat(c.high),
                            low: parseFloat(c.low),
                            close: parseFloat(c.close)
                        })));
                        ws.close();
                    }
                };
                ws.onerror = () => reject(new Error('WebSocket error'));
                setTimeout(() => reject(new Error('Timeout')), 30000);
            });
        }

        // ===== CANDLE CLICK =====
        function handleClick(time) {
            const tfA = parseInt(document.getElementById('tfA').value);
            const tfB = parseInt(document.getElementById('tfB').value);
            const endTime = time + tfA;
            
            const index = dataA.findIndex(c => c.time === time);
            if (index === -1) return;
            
            selectedCandleIndex = index;
            
            seriesA.setMarkers([{
                time: time,
                position: 'aboveBar',
                color: '#f68410',
                shape: 'circle',
                text: '●'
            }]);
            
            const bCandles = dataB.filter(c => c.time >= time && c.time < endTime);
            if (bCandles.length === 0) return msg('ไม่พบข้อมูล B', 'error');
            
            seriesB.setData(bCandles);
            chartB.timeScale().fitContent();
            document.getElementById('infoB').textContent = `${bCandles.length} แท่ง`;
            
            const lastTime = bCandles[bCandles.length - 1].time;
            const lastClose = bCandles[bCandles.length - 1].close;
            const nextStart = lastTime + tfB;
            const nextEnd = nextStart + (4 * tfB);
            
            const cCandles = dataB.filter(c => c.time >= nextStart && c.time < nextEnd);
            if (cCandles.length > 0) {
                seriesC.setData(cCandles);
                refLine.setData(cCandles.map(c => ({ time: c.time, value: lastClose })));
                chartC.timeScale().fitContent();
                document.getElementById('infoC').textContent = `${cCandles.length} แท่ง`;
            }
            
            document.getElementById('infoA').textContent = `เลือกแท่ง #${index + 1}/${dataA.length}`;
        }

        // ===== SIGNAL ANALYZER =====
        function analyze() {
            if (dataA.length === 0) return msg('โหลดข้อมูลก่อน', 'error');
            
            let candleIndex = selectedCandleIndex !== null ? selectedCandleIndex : dataA.length - 1;
            const candle = dataA[candleIndex];
            const isSelected = selectedCandleIndex !== null;
            
            if (!isSelected) {
                seriesA.setMarkers([{
                    time: candle.time,
                    position: 'aboveBar',
                    color: '#4a9eff',
                    shape: 'arrowDown',
                    text: 'Analyzed'
                }]);
            }
            
            const prev1 = dataA[candleIndex - 1] || candle;
            const prev2 = dataA[candleIndex - 2] || prev1;
            const prev3 = dataA[candleIndex - 3] || prev2;
            
            const isBullish = candle.close > candle.open;
            const bodySize = Math.abs(candle.close - candle.open);
            const totalRange = candle.high - candle.low;
            const bodyPercent = totalRange > 0 ? (bodySize / totalRange * 100) : 0;
            const upperWick = candle.high - Math.max(candle.open, candle.close);
            const lowerWick = Math.min(candle.open, candle.close) - candle.low;
            
            let bullishCount = 0;
            let bearishCount = 0;
            [prev3, prev2, prev1].forEach(c => {
                if (c.close > c.open) bullishCount++;
                else if (c.close < c.open) bearishCount++;
            });
            
            const priceChange = ((candle.close - prev1.close) / prev1.close * 100);
            
            let score = 50;
            const reasons = [];
            
            if (isBullish) {
                score += 12.5;
                reasons.push(`🟢 แท่งปัจจุบันเป็นสีเขียว (Bullish)`);
            } else {
                score -= 12.5;
                reasons.push(`🔴 แท่งปัจจุบันเป็นสีแดง (Bearish)`);
            }
            
            if (bodyPercent > 70) {
                if (isBullish) {
                    score += 15;
                    reasons.push(`💪 Body แข็งแกร่ง ${bodyPercent.toFixed(1)}% (แรงซื้อ)`);
                } else {
                    score -= 15;
                    reasons.push(`💪 Body แข็งแกร่ง ${bodyPercent.toFixed(1)}% (แรงขาย)`);
                }
            } else if (bodyPercent < 30) {
                score -= 5;
                reasons.push(`😐 Body อ่อนแอ ${bodyPercent.toFixed(1)}% (ไม่แน่ชัด)`);
            } else {
                reasons.push(`📊 Body ปานกลาง ${bodyPercent.toFixed(1)}%`);
            }
            
            if (bullishCount > bearishCount) {
                score += 12.5;
                reasons.push(`📈 เทรนด์ขาขึ้น (${bullishCount}/3 แท่งเขียว)`);
            } else if (bearishCount > bullishCount) {
                score -= 12.5;
                reasons.push(`📉 เทรนด์ขาลง (${bearishCount}/3 แท่งแดง)`);
            } else {
                reasons.push(`↔️ Sideways (${bullishCount}🟢 vs ${bearishCount}🔴)`);
            }
            
            if (priceChange > 0.5) {
                score += 10;
                reasons.push(`⬆️ โมเมนตัมบวก +${priceChange.toFixed(2)}%`);
            } else if (priceChange < -0.5) {
                score -= 10;
                reasons.push(`⬇️ โมเมนตัมลบ ${priceChange.toFixed(2)}%`);
            } else {
                reasons.push(`➡️ โมเมนตัมเรียบ ${priceChange.toFixed(2)}%`);
            }
            
            if (lowerWick > bodySize * 2 && upperWick < bodySize) {
                score += 10;
                reasons.push(`🔨 Wick ล่างยาว (แรงซื้อรองรับ)`);
            } else if (upperWick > bodySize * 2 && lowerWick < bodySize) {
                score -= 10;
                reasons.push(`💥 Wick บนยาว (แรงขายกดทับ)`);
            } else if (upperWick > bodySize && lowerWick > bodySize) {
                score -= 5;
                reasons.push(`🎭 Wick 2 ด้านยาว (ไม่แน่นอน)`);
            }
            
            if (candle.close > prev1.high) {
                score += 5;
                reasons.push(`🚀 ทะลุ High แท่งก่อน`);
            } else if (candle.close < prev1.low) {
                score -= 5;
                reasons.push(`💔 ทะลุ Low แท่งก่อน`);
            }
            
            score = Math.max(0, Math.min(100, score));
            
            const signal = score >= 70 ? 'STRONG_BUY' : 
                          score >= 55 ? 'BUY' : 
                          score >= 45 ? 'NEUTRAL' : 
                          score >= 30 ? 'SELL' : 
                          'STRONG_SELL';
            
            const color = score >= 70 ? '#00C851' : 
                         score >= 55 ? '#4CAF50' : 
                         score >= 45 ? '#FFC107' : 
                         score >= 30 ? '#FF5722' : 
                         '#D32F2F';
            
            reasons.unshift(`📍 แท่งที่ ${candleIndex + 1} จาก ${dataA.length} แท่ง`);
            reasons.unshift(isSelected ? '✅ คุณเลือกแท่งนี้เอง' : '🔄 ใช้แท่งล่าสุดอัตโนมัติ');
            
            const candleTime = new Date(candle.time * 1000);
            const timeStr = candleTime.toLocaleString('th-TH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            document.getElementById('signalPanel').style.display = 'block';
            document.getElementById('signalPanel').innerHTML = `
                <div class="signal-panel">
                    <div style="background:rgba(74,158,255,0.1);padding:15px;border-radius:8px;margin-bottom:15px;">
                        <strong style="color:#4a9eff;">📍 แท่งที่วิเคราะห์</strong>
                        <div style="margin-top:10px;font-size:14px;">
                            <div style="margin:5px 0;">🔢 แท่งที่: <strong>#${candleIndex + 1}</strong> / ${dataA.length}</div>
                            <div style="margin:5px 0;">🕐 เวลา: <strong>${timeStr}</strong></div>
                            <div style="margin:5px 0;">📌 วิธีเลือก: <strong style="color:${isSelected ? '#00C851' : '#FFC107'}">${isSelected ? 'คลิกเลือกเอง' : 'แท่งล่าสุด (อัตโนมัติ)'}</strong></div>
                        </div>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:15px;align-items:center;">
                        <div class="signal-badge" style="background:${color};color:white;flex:1;margin-right:15px;">${signal}</div>
                        <div style="text-align:right;">
                            <div style="font-size:32px;font-weight:bold;color:${color};">${score}</div>
                            <div style="font-size:12px;color:#888;">/ 100</div>
                        </div>
                    </div>
                    
                    <div style="background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;margin-bottom:15px;">
                        <strong style="color:#00d2ff;">📋 เหตุผลการวิเคราะห์:</strong>
                        <ul class="reasons-list" style="margin-top:10px;">
                            ${reasons.map(r => `<li>${r}</li>`).join('')}
                        </ul>
                    </div>
                    
                    <div class="signal-metrics">
                        <div class="metric-box">
                            <div class="metric-label">Open</div>
                            <div class="metric-value">${candle.open.toFixed(4)}</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-label">High</div>
                            <div class="metric-value" style="color:#00C851;">${candle.high.toFixed(4)}</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-label">Low</div>
                            <div class="metric-value" style="color:#ef5350;">${candle.low.toFixed(4)}</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-label">Close</div>
                            <div class="metric-value" style="color:${isBullish ? '#00C851' : '#ef5350'};">${candle.close.toFixed(4)}</div>
                        </div>
                    </div>
                    
                    <div style="margin:15px 0;padding:10px;background:rgba(255,255,255,0.05);border-radius:5px;">
                        <div style="font-size:12px;color:#888;margin-bottom:5px;">ข้อมูลเทคนิค:</div>
                        <div style="font-size:13px;">
                            📏 Body: ${bodyPercent.toFixed(1)}% | 
                            ⬆️ Wick บน: ${(upperWick / totalRange * 100).toFixed(1)}% | 
                            ⬇️ Wick ล่าง: ${(lowerWick / totalRange * 100).toFixed(1)}%
                        </div>
                        <div style="font-size:13px;margin-top:5px;">
                            📊 เทรนด์ 3 แท่ง: ${bullishCount}🟢 vs ${bearishCount}🔴 | 
                            📈 เปลี่ยนแปลง: ${priceChange.toFixed(2)}%
                        </div>
                    </div>
                    
                    ${signal !== 'NEUTRAL' ? `
                    <div class="trade-plan-box">
                        <strong style="color:#00d2ff;">🎯 Trade Plan - ${signal.includes('BUY') ? 'BUY' : 'SELL'}</strong>
                        <div class="trade-plan-grid" style="margin-top:10px;">
                            <div class="trade-plan-item">
                                <div class="trade-plan-label">Entry</div>
                                <div class="trade-plan-value">${candle.close.toFixed(4)}</div>
                            </div>
                            <div class="trade-plan-item">
                                <div class="trade-plan-label">Stop Loss</div>
                                <div class="trade-plan-value">${(signal.includes('BUY') ? candle.low * 0.998 : candle.high * 1.002).toFixed(4)}</div>
                            </div>
                            <div class="trade-plan-item">
                                <div class="trade-plan-label">TP1 (R:R 1.5)</div>
                                <div class="trade-plan-value">${(signal.includes('BUY') ? candle.close * 1.003 : candle.close * 0.997).toFixed(4)}</div>
                            </div>
                            <div class="trade-plan-item">
                                <div class="trade-plan-label">TP2 (R:R 2.5)</div>
                                <div class="trade-plan-value">${(signal.includes('BUY') ? candle.close * 1.006 : candle.close * 0.994).toFixed(4)}</div>
                            </div>
                        </div>
                    </div>
                    ` : `
                    <div style="text-align:center;padding:20px;background:rgba(255,193,7,0.1);border-radius:8px;">
                        <div style="font-size:20px;margin-bottom:10px;">⚠️</div>
                        <strong style="color:#FFC107;">NEUTRAL - รอสัญญาณที่ชัดเจนกว่า</strong>
                        <div style="font-size:13px;color:#aaa;margin-top:10px;">
                            สัญญาณไม่แข็งแกร่งพอสำหรับการเทรด
                        </div>
                    </div>
                    `}
                </div>
            `;
            
            document.getElementById('signalPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
            msg(`วิเคราะห์เสร็จสิ้น! สัญญาณ: ${signal} (${score}/100)`, 'info');
        }

        // ===== UTILITIES =====
        function msg(text, type = 'info') {
            const el = document.getElementById('msg');
            el.innerHTML = `<div class="${type}">${text}</div>`;
            setTimeout(() => el.innerHTML = '', 5000);
        }

        // ===== TOOLTIP FUNCTIONS =====
        function analyzeCandleStick(candle) {
            const { open, high, low, close } = candle;
            
            const color = close >= open ? 'green' : 'red';
            const isBullish = close >= open;
			const pip = close-open;
            
            const totalRange = high - low;
            const bodySize = Math.abs(close - open);
            const upperWick = high - Math.max(open, close);
            const lowerWick = Math.min(open, close) - low;
            
            const upperWickPercent = totalRange > 0 ? (upperWick / totalRange) * 100 : 0;
            const bodyPercent = totalRange > 0 ? (bodySize / totalRange) * 100 : 0;
            const lowerWickPercent = totalRange > 0 ? (lowerWick / totalRange) * 100 : 0;
            
            let wickRatio = '-';
            if (lowerWick > 0) {
                wickRatio = (upperWick / lowerWick).toFixed(2);
            } else if (upperWick > 0) {
                wickRatio = '∞';
            }
            
            let pressure = '';
            let pressureStrength = '';
            let hint = '';
            
            if (isBullish) {
                if (lowerWickPercent > upperWickPercent * 2) {
                    pressure = 'แรงซื้อแรง';
                    pressureStrength = 'Strong Buy Pressure';
                    hint = '📈 ผู้ซื้อแข็งแกร่งมาก! มีโอกาสขึ้นต่อ';
                } else if (lowerWickPercent > upperWickPercent) {
                    pressure = 'แรงซื้อมากกว่า';
                    pressureStrength = 'Buy Pressure';
                    hint = '📊 แรงซื้อดี แนวโน้มขาขึ้น';
                } else if (upperWickPercent > lowerWickPercent * 2) {
                    pressure = 'แรงขายต้าน';
                    pressureStrength = 'Sell Resistance';
                    hint = '⚠️ ระวัง! มีแรงขายกดทับ อาจกลับตัวลง';
                } else {
                    pressure = 'สมดุล';
                    pressureStrength = 'Balanced';
                    hint = '↔️ ไม่ชัดเจน รอสัญญาณที่ดีกว่า';
                }
            } else {
                if (upperWickPercent > lowerWickPercent * 2) {
                    pressure = 'แรงขายแรง';
                    pressureStrength = 'Strong Sell Pressure';
                    hint = '📉 ผู้ขายแข็งแกร่งมาก! มีโอกาสลงต่อ';
                } else if (upperWickPercent > lowerWickPercent) {
                    pressure = 'แรงขายมากกว่า';
                    pressureStrength = 'Sell Pressure';
                    hint = '📊 แรงขายดี แนวโน้มขาลง';
                } else if (lowerWickPercent > upperWickPercent * 2) {
                    pressure = 'แรงซื้อรับ';
                    pressureStrength = 'Buy Support';
                    hint = '🛡️ มีแรงซื้อรองรับ! อาจกลับตัวขึ้น';
                } else {
                    pressure = 'สมดุล';
                    pressureStrength = 'Balanced';
                    hint = '↔️ ไม่ชัดเจน รอสัญญาณที่ดีกว่า';
                }
            }
            
            return {
                color: color,
                pip : pip,
                upperWickPercent: upperWickPercent.toFixed(2),
                bodyPercent: bodyPercent.toFixed(2),
                lowerWickPercent: lowerWickPercent.toFixed(2),
                wickRatio: wickRatio,
                pressure: pressure,
                pressureStrength: pressureStrength,
                hint: hint,
                totalRange: totalRange.toFixed(6)
            };
        }

        function createCandleTooltip(chartContainer, chart, series, chartName) {
            const tooltip = document.createElement('div');
            tooltip.style.position = 'absolute';
            tooltip.style.display = 'none';
            tooltip.style.padding = '10px';
            tooltip.style.background = 'rgba(0, 0, 0, 0.9)';
            tooltip.style.color = '#fff';
            tooltip.style.borderRadius = '5px';
            tooltip.style.fontSize = '11px';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.zIndex = '10000';
            tooltip.style.minWidth = '220px';
            tooltip.style.boxShadow = '0 2px 10px rgba(0,0,0,0.5)';
            tooltip.style.border = '1px solid #4a9eff';
            chartContainer.style.position = 'relative';
            chartContainer.appendChild(tooltip);
            
            chart.subscribeCrosshairMove(param => {
                if (!param || !param.time || !param.point) {
                    tooltip.style.display = 'none';
                    return;
                }
                
                // ✅ Lightweight Charts 3.8 ใช้ seriesPrices แทน seriesData
                let data = null;
                if (param.seriesPrices && param.seriesPrices.has(series)) {
                    data = param.seriesPrices.get(series);
                }
                
                if (!data || typeof data.open === 'undefined' || data.open === null) {
                    tooltip.style.display = 'none';
                    return;
                }
                
                const analysis = analyzeCandleStick(data);
                const colorStyle = analysis.color === 'green' ? '#00C851' : '#ef5350';
                
                tooltip.innerHTML = `
                    <div style="margin-bottom: 6px; border-bottom: 1px solid #444; padding-bottom: 4px;">
                        <strong style="color: ${colorStyle};">${chartName} ${analysis.color === 'green' ? '🟢' : '🔴'}</strong>
                    </div>
                    <div style="line-height: 1.5; font-size: 10px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:3px;margin-bottom:5px;">
                            <div>O: ${data.open.toFixed(4)}</div>
                            <div>H: <span style="color:#00C851;">${data.high.toFixed(4)}</span></div>
                            <div>L: <span style="color:#ef5350;">${data.low.toFixed(4)}</span></div>
                            <div>C: <span style="color:${colorStyle};">${data.close.toFixed(4)}</span></div>

					        <div>PIP: <span style="color:${colorStyle};">${analysis.pip.toFixed(4)}</span></div>

                        </div>
                        <div style="margin-top: 6px; border-top: 1px solid #444; padding-top: 4px;">
                            <div style="margin:2px 0;">🔺 Upper: <strong>${analysis.upperWickPercent}%</strong></div>
                            <div style="margin:2px 0;">📦 Body: <strong>${analysis.bodyPercent}%</strong></div>
                            <div style="margin:2px 0;">🔻 Lower: <strong>${analysis.lowerWickPercent}%</strong></div>
                        </div>
                        <div style="margin-top: 6px; border-top: 1px solid #444; padding-top: 4px;">
                            <div style="margin:2px 0;">📊 Ratio: <strong>${analysis.wickRatio}</strong></div>
                            <div style="margin:2px 0;">📈 Range: ${analysis.totalRange}</div>
                        </div>
                        <div style="margin-top: 6px; border-top: 1px solid #444; padding-top: 4px; background: rgba(255,255,255,0.05); padding: 4px; border-radius: 3px;">
                            <div style="font-weight: bold; color: ${colorStyle}; font-size: 11px;">
                                💡 ${analysis.pressure}
                            </div>
                            <div style="font-size: 9px; color: #aaa;">
                                ${analysis.pressureStrength}
                            </div>
                            <div style="font-size: 10px; color: #ffeb3b; margin-top: 4px; font-weight: 500;">
                                ${analysis.hint}
                            </div>
                        </div>
                    </div>
                `;
                
                const x = param.point.x;
                const y = param.point.y;
                
                tooltip.style.display = 'block';
                tooltip.style.left = (x + 15) + 'px';
                tooltip.style.top = (y + 15) + 'px';
                
                const chartRect = chartContainer.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                
                if (x + tooltipRect.width + 20 > chartRect.width) {
                    tooltip.style.left = (x - tooltipRect.width - 15) + 'px';
                }
                if (y + tooltipRect.height + 20 > chartRect.height) {
                    tooltip.style.top = (y - tooltipRect.height - 15) + 'px';
                }
            });
            
            return tooltip;
        }
    </script>
</body>
</html>

1,2,4,9,18,38,78,161,333,687