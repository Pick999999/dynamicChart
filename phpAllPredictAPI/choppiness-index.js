// Choppiness Index Lab - Multi-Indicator JavaScript
// แก้ไข BB ให้แสดงบน mainChart เดียวกับ Candle

const DERIV_WS_URL = 'wss://ws.derivws.com/websockets/v3?app_id=1089';
let ws, candleData = [], mainChart, ciChart, adxChart, candleSeries, emaSeries = [], ciSeries, adxSeries;
let sidewaysMarkers = [], signalHistory = [], lastSignal = null, allMarkers = [];
let bbUpperSeries = null, bbMiddleSeries = null, bbLowerSeries = null;

function initCharts() {
    const cfg = {
        layout: { backgroundColor: '#fff', textColor: '#333' },
        grid: { vertLines: { color: '#f0f0f0' }, horzLines: { color: '#f0f0f0' } },
        rightPriceScale: { borderColor: '#d1d4dc' },
        timeScale: { borderColor: '#d1d4dc', timeVisible: true }
    };

    // Main Chart
    mainChart = LightweightCharts.createChart(document.getElementById('mainChart'), {
        ...cfg,
        width: document.getElementById('mainChart').clientWidth,
        height: 400
    });

    // Candles
    candleSeries = mainChart.addCandlestickSeries({
        upColor: '#26a69a',
        downColor: '#ef5350',
        wickUpColor: '#26a69a',
        wickDownColor: '#ef5350'
    });

    // Bollinger Bands - อยู่บน mainChart เดียวกัน
    bbUpperSeries = mainChart.addLineSeries({
        color: 'rgba(156, 39, 176, 0.5)',
        lineWidth: 1,
        lineStyle: 2  // dashed
    });
    bbMiddleSeries = mainChart.addLineSeries({
        color: 'rgba(156, 39, 176, 0.7)',
        lineWidth: 1
    });
    bbLowerSeries = mainChart.addLineSeries({
        color: 'rgba(156, 39, 176, 0.5)',
        lineWidth: 1,
        lineStyle: 2  // dashed
    });

    // EMAs
    [9, 21].forEach((p, i) => {
        emaSeries.push({
            period: p,
            series: mainChart.addLineSeries({
                color: ['#2196F3', '#FF9800'][i],
                lineWidth: 2
            })
        });
    });

    // CI Chart
    ciChart = LightweightCharts.createChart(document.getElementById('ciChart'), {
        ...cfg,
        width: document.getElementById('ciChart').clientWidth,
        height: 180
    });
    ciSeries = ciChart.addLineSeries({ color: '#667eea', lineWidth: 3 });

    // ADX Chart
    adxChart = LightweightCharts.createChart(document.getElementById('adxChart'), {
        ...cfg,
        width: document.getElementById('adxChart').clientWidth,
        height: 180
    });
    adxSeries = adxChart.addLineSeries({ color: '#f45c43', lineWidth: 3 });

    // Resize handler
    window.addEventListener('resize', () => {
        mainChart.applyOptions({ width: document.getElementById('mainChart').clientWidth });
        ciChart.applyOptions({ width: document.getElementById('ciChart').clientWidth });
        adxChart.applyOptions({ width: document.getElementById('adxChart').clientWidth });
    });
}

async function connectToDeriv() {
    document.getElementById('connectBtn').textContent = '🔄 Connecting...';
    document.getElementById('connectBtn').disabled = true;

    ws = new WebSocket(DERIV_WS_URL);
    ws.onopen = () => {
        document.getElementById('connectBtn').textContent = '✅ Connected';
        document.getElementById('connectBtn').style.background = '#66bb6a';
        document.getElementById('loadDataBtn').disabled = false;
    };
    ws.onmessage = (msg) => {
        const data = JSON.parse(msg.data);
        if (data.msg_type === 'candles') processCandleData(data.candles);
    };
}

function loadCandleData() {
    document.getElementById('loadDataBtn').textContent = '⏳ Loading...';
    document.getElementById('loadDataBtn').disabled = true;

    ws.send(JSON.stringify({
        ticks_history: document.getElementById('symbolSelect').value,
        adjust_start_time: 1,
        count: 500,
        end: 'latest',
        granularity: parseInt(document.getElementById('timeframeSelect').value),
        style: 'candles'
    }));
}

function processCandleData(candles) {
    candleData = candles.map(c => ({
        time: c.epoch + (7*3600),
        open: +c.open,
        high: +c.high,
        low: +c.low,
        close: +c.close
    }));

    candleSeries.setData(candleData);
    calculateAllIndicators();

	colorCandle = processColorCandleData(candles) ;
	//console.log('colorCandle',colorCandle);

	for (let i=0;i<=colorCandle.length-4 ;i++ ) {
		thisColor = colorCandle[i].color ;
		next1Color = colorCandle[i+1].color ;
		next2Color = colorCandle[i+2].color ;
		next3Color = colorCandle[i+3].color ;
	    //console.log(thisColor ,next1Color,next2Color,next3Color) ;
		mixColor = thisColor +'-' + next1Color+ '-' + next2Color + '-' +next3Color;
		if (mixColor == 'Green-Red-Green-Red' || mixColor == 'Red-Green-Red-Green'  ) {
			 //console.log(mixColor);
			 colorCandle[i].markA = 1 ;
		} else {
			colorCandle[i].markA = 0 ;
		}



	 }

	 lastIndex = colorCandle.length-1 ;
	 stColor = '';
	 for (let i=lastIndex-5;i<=lastIndex ;i++ ) {
		 let thisColor = colorCandle[i].color === 'Green' ? "🟢" : "🔴";
		 stColor =  stColor + thisColor + '-';

	 }
     document.getElementById("colorCard").innerHTML = stColor ;




	const result = colorCandle.filter(object => object.markA > 0);
	allMarker = [];
	//alert(result.length);
	for (let i=0;i<=result.length-1 ;i++ ) {
       timeCandle = result[i].epoch ;
       sLocal = new Date(timeCandle*1000).toLocaleTimeString('th-TH');
	   console.log(result[i].epoch,sLocal) ;

	   sObj = {
		   time: result[i].epoch + (7*3600),
	       position: 'aboveBar',
	       color: '#f68410',
	       shape: 'circle',
	       text: '1'
       }
	   allMarker.push(sObj);

	}
	candleSeries.setMarkers(allMarker);

    document.getElementById('loadDataBtn').textContent = '✅ Loaded';
    document.getElementById('loadDataBtn').disabled = false;
    document.getElementById('updateBtn').disabled = false;
    document.getElementById('chartSymbol').textContent =
    document.getElementById('symbolSelect').options[document.getElementById('symbolSelect').selectedIndex].text;
}

function calculateEMA(data, period) {
    const k = 2 / (period + 1);
    const result = [];
    let ema = data[0].close;
    data.forEach((c, i) => {
        ema = i === 0 ? c.close : (c.close * k) + (ema * (1 - k));
        result.push({ time: c.time, value: ema });
    });
    return result;
}

function calculateATR(data, period) {
    const atr = [];
    let avg = 0;
    for (let i = 0; i < data.length; i++) {
        const tr = i === 0 ? data[i].high - data[i].low : Math.max(
            data[i].high - data[i].low,
            Math.abs(data[i].high - data[i - 1].close),
            Math.abs(data[i].low - data[i - 1].close)
        );
        avg = i < period ? ((avg * i) + tr) / (i + 1) : ((avg * (period - 1)) + tr) / period;
        atr.push(avg);
    }
    return atr;
}

function calculateCI(data, period) {
    const atr = calculateATR(data, period);
    const result = [];
    for (let i = period - 1; i < data.length; i++) {
        const slice = data.slice(i - period + 1, i + 1);
        const atrSlice = atr.slice(i - period + 1, i + 1);
        const highest = Math.max(...slice.map(c => c.high));
        const lowest = Math.min(...slice.map(c => c.low));
        const sumATR = atrSlice.reduce((sum, a) => sum + a, 0);
        const range = highest - lowest;
        if (range > 0) {
            const ci = 100 * Math.log10(sumATR / range) / Math.log10(period);
            result.push({ time: data[i].time, value: Math.max(0, Math.min(100, ci)) });
        }
    }
    return result;
}

function calculateADX(data, period) {
    const result = [];
    let plusDM = [], minusDM = [], tr = [];

    for (let i = 1; i < data.length; i++) {
        const hDiff = data[i].high - data[i - 1].high;
        const lDiff = data[i - 1].low - data[i].low;
        plusDM.push(hDiff > lDiff && hDiff > 0 ? hDiff : 0);
        minusDM.push(lDiff > hDiff && lDiff > 0 ? lDiff : 0);
        tr.push(Math.max(
            data[i].high - data[i].low,
            Math.abs(data[i].high - data[i - 1].close),
            Math.abs(data[i].low - data[i - 1].close)
        ));
    }

    let sPlusDM = 0, sMinusDM = 0, sTR = 0, adx = [], prevDX = 0;

    for (let i = 0; i < plusDM.length; i++) {
        if (i < period) {
            sPlusDM += plusDM[i];
            sMinusDM += minusDM[i];
            sTR += tr[i];
        } else {
            sPlusDM = sPlusDM - (sPlusDM / period) + plusDM[i];
            sMinusDM = sMinusDM - (sMinusDM / period) + minusDM[i];
            sTR = sTR - (sTR / period) + tr[i];

            const pDI = 100 * (sPlusDM / sTR);
            const mDI = 100 * (sMinusDM / sTR);
            const dx = 100 * Math.abs(pDI - mDI) / (pDI + mDI || 1);
            prevDX = i === period ? dx : (prevDX * (period - 1) + dx) / period;
            adx.push(prevDX);
        }
    }

    for (let i = 0; i < data.length; i++) {
        result.push({ time: data[i].time, value: i < period + 1 ? 0 : (adx[i - period - 1] || 0) });
    }
    return result;
}

function calculateBB(data, period, stdDev = 2) {
    const upper = [], middle = [], lower = [];
    for (let i = period - 1; i < data.length; i++) {
        const slice = data.slice(i - period + 1, i + 1);
        const closes = slice.map(c => c.close);
        const avg = closes.reduce((s, c) => s + c, 0) / period;
        const variance = closes.reduce((s, c) => s + Math.pow(c - avg, 2), 0) / period;
        const std = Math.sqrt(variance);

        upper.push({ time: data[i].time, value: avg + (stdDev * std) });
        middle.push({ time: data[i].time, value: avg });
        lower.push({ time: data[i].time, value: avg - (stdDev * std) });
    }
    return { upper, middle, lower };
}

function calculateBBWidth(data, period) {
    const result = [];
    for (let i = period - 1; i < data.length; i++) {
        const slice = data.slice(i - period + 1, i + 1);
        const closes = slice.map(c => c.close);
        const avg = closes.reduce((s, c) => s + c, 0) / period;
        const variance = closes.reduce((s, c) => s + Math.pow(c - avg, 2), 0) / period;
        const std = Math.sqrt(variance);
        const width = ((2 * std) / avg) * 100;
        result.push({ time: data[i].time, value: width });
    }
    return result;
}

function detectColorAlt(data, count = 4) {
    if (data.length < count) return 0;
    const last = data.slice(-count);
    let alt = 0;
    for (let i = 1; i < last.length; i++) {
        if ((last[i - 1].close > last[i - 1].open) !== (last[i].close > last[i].open)) alt++;
    }
    return alt;
}

function calculateAllIndicators() {
    const ciPeriod = +document.getElementById('ciPeriod').value;
    const adxPeriod = +document.getElementById('adxPeriod').value;
    const bbPeriod = +document.getElementById('bbPeriod').value;

    // Calculate EMAs
    emaSeries.forEach(({ period, series }) => series.setData(calculateEMA(candleData, period)));

    // Calculate and display Bollinger Bands
    const bb = calculateBB(candleData, bbPeriod);
    const bbShow = document.getElementById('bbEnabled') ? document.getElementById('bbEnabled').checked : true;

    if (bbUpperSeries && bbMiddleSeries && bbLowerSeries) {
        bbUpperSeries.applyOptions({ visible: bbShow });
        bbMiddleSeries.applyOptions({ visible: bbShow });
        bbLowerSeries.applyOptions({ visible: bbShow });

        if (bbShow) {
            bbUpperSeries.setData(bb.upper);
            bbMiddleSeries.setData(bb.middle);
            bbLowerSeries.setData(bb.lower);
        }
    }

    // Calculate CI
    const ciData = calculateCI(candleData, ciPeriod);
    ciSeries.setData(ciData);

    // Calculate ADX
    const adxData = calculateADX(candleData, adxPeriod);
    adxSeries.setData(adxData);

    // Calculate BB Width for sideways detection
    const bbData = calculateBBWidth(candleData, bbPeriod);

    // Reset markers
    allMarkers = [];
    sidewaysMarkers = [];
    let inSW = false;
    const thresh = +document.getElementById('ciThreshold').value;

    for (let i = 0; i < ciData.length; i++) {
        const ci = ciData[i].value;
        const adx = adxData.find(a => a.time === ciData[i].time)?.value || 0;
        const bbWidth = bbData[i - (ciPeriod - bbPeriod)]?.value || 0;
        const candleIdx = candleData.findIndex(c => c.time === ciData[i].time);
        const colorAlt = candleIdx >= 4 ? detectColorAlt(candleData.slice(0, candleIdx + 1), 6) : 0;

        let swCount = 0;
        if (ci > thresh) swCount++;
        if (adx < 20 && adx > 0) swCount++;
        if (bbWidth < 2 && bbWidth > 0) swCount++;
        if (colorAlt >= 5) swCount++;

        if (swCount >= 3 && !inSW) {
            const newMarker = {
                time: ciData[i].time,
                position: 'aboveBar',
                color: '#ef5350',
                shape: 'circle',
                text: '⚠️'
            };
            allMarkers.push(newMarker);
            sidewaysMarkers.push(newMarker);
            inSW = true;
        } else if (swCount < 2 && inSW) {
            inSW = false;
        }
    }

    // Set all markers at once
    candleSeries.setMarkers(allMarkers);

    document.getElementById('markerCount').textContent = sidewaysMarkers.length;

    if (ciData.length > 0) {
        const ci = ciData[ciData.length - 1].value;
        const adx = adxData[adxData.length - 1]?.value || 0;
        const bbWidth = bbData[bbData.length - 1]?.value || 0;
        const colorAlt = detectColorAlt(candleData, 6);
        updateDisplay(ci, adx, bbWidth, colorAlt);
        generateHint(ci, adx, bbWidth, colorAlt);
    }
}

function updateDisplay(ci, adx, bb, colorAlt) {
    document.getElementById('ciValue').textContent = ci.toFixed(2);
    document.getElementById('adxValue').textContent = adx.toFixed(2);
    document.getElementById('bbValue').textContent = bb.toFixed(2);

    const thresh = +document.getElementById('ciThreshold').value;
    let swCount = 0;
    if (ci > thresh) swCount++;
    if (adx < 20) swCount++;
    if (bb < 2) swCount++;
    if (colorAlt >= 5) swCount++;

    let state, signal, cls;
    if (swCount >= 3) {
        state = '🔴 CHOPPY';
        signal = '🛑';
        cls = 'choppy';
    } else if (swCount === 2) {
        state = '🟡 NEUTRAL';
        signal = '⚠️';
        cls = 'neutral';
    } else {
        state = '🟢 TREND';
        signal = '✅';
        cls = 'trending';
    }

    document.getElementById('marketState').textContent = state;
    document.getElementById('tradingSignal').textContent = signal;
    document.getElementById('ciCard').className = 'status-card ' + (ci > thresh ? 'choppy' : 'trending');
    document.getElementById('adxCard').className = 'status-card ' + (adx < 20 ? 'choppy' : 'trending');
    document.getElementById('bbCard').className = 'status-card ' + (bb < 2 ? 'choppy' : 'trending');
    document.getElementById('marketStateCard').className = 'status-card ' + cls;
}

function generateHint(ci, adx, bb, colorAlt) {
    const curr = candleData[candleData.length - 1];
    const ema9 = calculateEMA(candleData, 9);
    const ema21 = calculateEMA(candleData, 21);
    const e9 = ema9[ema9.length - 1].value;
    const e21 = ema21[ema21.length - 1].value;
    const e9Prev = ema9[ema9.length - 2].value;

    const up = e9 > e21 && e9 > e9Prev;
    const down = e9 < e21 && e9 < e9Prev;
    const thresh = +document.getElementById('ciThreshold').value;

    let swCount = 0, reasons = [];
    if (ci > thresh) { swCount++; reasons.push('CI>' + thresh); }
    if (adx < 20) { swCount++; reasons.push('ADX<20'); }
    if (bb < 2) { swCount++; reasons.push('BB<2%'); }
    if (colorAlt >= 5) { swCount++; reasons.push('Alt:' + colorAlt); }

    let action, icon, title, content, conf;

    if (swCount >= 3) {
        action = 'IDLE';
        icon = '🛑';
        title = 'STRONG SIDEWAYS';
        content = `${swCount}/4: ${reasons.join(', ')}. หยุดเทรด!`;
        conf = 0;
    } else if (swCount === 2) {
        action = 'IDLE';
        icon = '⚠️';
        title = 'MODERATE SIDEWAYS';
        content = `${swCount}/4: ${reasons.join(', ')}. ระวัง!`;
        conf = 30;
    } else {
        if (curr.close > e9 && up && adx > 25) {
            action = 'CALL';
            icon = '📈';
            title = 'CALL - Uptrend';
            content = `Price>${e9.toFixed(4)}, ADX=${adx.toFixed(1)}, ${4 - swCount}/4 OK`;
            conf = 85 + (adx > 30 ? 10 : 0);
        } else if (curr.close < e9 && down && adx > 25) {
            action = 'PUT';
            icon = '📉';
            title = 'PUT - Downtrend';
            content = `Price<${e9.toFixed(4)}, ADX=${adx.toFixed(1)}, ${4 - swCount}/4 OK`;
            conf = 85 + (adx > 30 ? 10 : 0);
        } else {
            action = 'IDLE';
            icon = '🤔';
            title = 'IDLE - Wait';
            content = 'สัญญาณยังไม่ชัดเจน รอ ADX>25';
            conf = 40;
        }
    }


    document.getElementById('tradingHint').className = 'trading-hint ' + action.toLowerCase();
    document.getElementById('hintIcon').textContent = icon;
    document.getElementById('hintTitle').innerHTML = "<span style='color:#ff0080'>"+ document.getElementById("symbolSelect").value +' </span> ' + title;
    document.getElementById('hintContent').textContent = content;
    document.getElementById('hintDetails').style.display = 'grid';
    document.getElementById('hintCI').textContent = ci.toFixed(2);
    document.getElementById('hintADX').textContent = adx.toFixed(2);
    document.getElementById('hintBB').textContent = bb.toFixed(2) + '%';
    document.getElementById('hintColor').textContent = colorAlt + '/6';
    document.getElementById('hintEMA').textContent = up ? '↗️' : down ? '↘️' : '↔️';
    document.getElementById('hintConfidence').textContent = conf + '%' + (conf >= 70 ? '🟢' : conf >= 40 ? '🟡' : '🔴');

    const signalKey = `${action}-${conf}-${swCount}`;
    if (signalKey !== lastSignal) {
        addToHistory(action, title, conf, ci, adx, bb, colorAlt, swCount);
        lastSignal = signalKey;
        if ((action === 'CALL' || action === 'PUT') && conf >= 80 && document.getElementById('alertEnabled').checked) {
            playAlert();
        }
    }
}

function addToHistory(action, title, conf, ci, adx, bb, colorAlt, swCount) {
    const time = new Date().toLocaleString('th-TH');
    signalHistory.unshift({ action, title, conf, ci, adx, bb, colorAlt, swCount, time });
    if (signalHistory.length > 10) signalHistory = signalHistory.slice(0, 10);
    updateHistory();
}

function updateHistory() {
    const list = document.getElementById('historyList');
    if (signalHistory.length === 0) {
        list.innerHTML = '<div style="text-align:center;color:#888;padding:20px">No signals yet</div>';
        return;
    }
    list.innerHTML = signalHistory.map(item => {
        const iconMap = { 'CALL': '📈', 'PUT': '📉', 'IDLE': '⏸️' };
        const colorMap = { 'CALL': 'call', 'PUT': 'put', 'IDLE': 'idle' };
        return `
            <div class="history-item ${colorMap[item.action]}">
                <div class="history-icon">${iconMap[item.action]}</div>
                <div>
                    <div class="history-action">${item.title}</div>
                    <div class="history-time">🕒 ${item.time} | CI:${item.ci.toFixed(1)} ADX:${item.adx.toFixed(1)} BB:${item.bb.toFixed(1)}% Alt:${item.colorAlt} SW:${item.swCount}/4</div>
                </div>
                <div style="font-weight:bold;font-size:1.2em">${item.conf}%</div>
            </div>
        `;
    }).join('');
}

function clearHistory() {
    if (confirm('Clear history?')) {
        signalHistory = [];
        lastSignal = null;
        updateHistory();
    }
}

function playAlert() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = 800;
        osc.type = 'sine';
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.5);
    } catch (e) {
        console.error('Alert error:', e);
    }
}

function BtnClassEMA_Click() {

const emaTrendObject = new (AnalyEMATrend) ;

emaTrendObject.setCandleData('Test');


} // end func

function processColorCandleData(candles) {
  if (!candles || candles.length === 0) {
    return [];
  }

  return candles.map((candle, index) => {
    // 1. กำหนดสี (Color)
    let color;
    if (candle.close > candle.open) {
      color = 'Green';
    } else if (candle.close < candle.open) {
      color = 'Red';
    } else {
      color = 'Equal';
    }

    // 2. ตรวจสอบการสลับสี (Color Switch)
    let colorSwitch = 0;
    if (index > 0) {
      const prevCandle = candles[index - 1];
      const prevColor = prevCandle.close > prevCandle.open ? 'Green'
                      : prevCandle.close < prevCandle.open ? 'Red'
                      : 'Equal';

      // ถ้าสีปัจจุบันต่างจากสีก่อนหน้า (และไม่ใช่ Equal)
      if (color !== prevColor && color !== 'Equal' && prevColor !== 'Equal') {
        colorSwitch = index + 1; // นับเป็นแท่งที่เท่าไหร่
      }
    }


    // 3. นับ Green Con และ Red Con (Consecutive)
    let greenCon = 0;
    let redCon = 0;

    if (color === 'Green') {
      greenCon = 1;
      // นับย้อนหลังว่ามีแท่งเขียวติดกันกี่แท่ง
      for (let i = index - 1; i >= 0; i--) {
        const c = candles[i];
        if (c.close > c.open) {
          greenCon++;
        } else {
          break;
        }
      }
    } else if (color === 'Red') {
      redCon = 1;
      // นับย้อนหลังว่ามีแท่งแดงติดกันกี่แท่ง
      for (let i = index - 1; i >= 0; i--) {
        const c = candles[i];
        if (c.close < c.open) {
          redCon++;
        } else {
          break;
        }
      }
    }

    return {
      ...candle,
      color: color,
      colorSwitch: colorSwitch,
      greenCon: greenCon,
      redCon: redCon
    };
  });
}
/*
// ตัวอย่างการใช้งาน
const candleData = [
  { open: 100, close: 105, high: 106, low: 99 },  // Green
  { open: 105, close: 103, high: 107, low: 102 }, // Red - มีการสลับสี
  { open: 103, close: 101, high: 104, low: 100 }, // Red - ไม่มีการสลับ
  { open: 101, close: 104, high: 105, low: 100 }, // Green - มีการสลับสี
  { open: 104, close: 107, high: 108, low: 103 }, // Green - ไม่มีการสลับ
  { open: 107, close: 107, high: 108, low: 106 }, // Equal
];

const result = processCandleData(candleData);
console.log(result);
*/

// Event Listeners
document.getElementById('connectBtn').addEventListener('click', connectToDeriv);
document.getElementById('loadDataBtn').addEventListener('click', loadCandleData);
document.getElementById('updateBtn').addEventListener('click', calculateAllIndicators);

// BB Toggle
if (document.getElementById('bbEnabled')) {
    document.getElementById('bbEnabled').addEventListener('change', calculateAllIndicators);
}

// Initialize
initCharts();

