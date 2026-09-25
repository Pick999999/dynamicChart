<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <title>Sideway Predictor - Candle Analysis</title>
  <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
  <style>
    body { font-family: "Segoe UI", sans-serif; padding:16px; background:#f6f8fa;}
    textarea { width:100%; height:140px; font-family:monospace; }
    button{ padding:10px 14px; margin-top:8px; background:#0b74de;color:#fff;border:none;border-radius:6px; cursor:pointer;}
    #chart { width:100%; height:420px; margin-top:14px; background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
    table{ width:100%; border-collapse:collapse; margin-top:12px; background:#fff;}
    th,td{ border:1px solid #e6eef7; padding:6px 8px; font-size:13px; text-align:center;}
    th{ background:#0b74de; color:#fff; }
    #prediction { margin-top:8px; font-weight:700; }
  </style>
</head>
<body>
  <h2>🔮 Sideway Predictor + Candle Analysis</h2>
  <p>วาง JSON ของ candles (รองรับ epoch)</p>
  <textarea id="candleInput">[
  {"close":122.8445,"epoch":1753482960,"high":122.9085,"low":122.7098,"open":122.8679},
  {"close":123.0225,"epoch":1753483140,"high":123.0541,"low":122.8352,"open":122.8599},
  {"close":123.019,"epoch":1753483320,"high":123.0867,"low":122.9977,"open":123.0293},
  {"close":123.0428,"epoch":1753483500,"high":123.2252,"low":123.01,"open":123.0113},
  {"close":123.0113,"epoch":1753483680,"high":123.1562,"low":122.8668,"open":123.0383},
  {"close":123.0894,"epoch":1753483860,"high":123.1308,"low":122.9564,"open":122.9785},
  {"close":122.8852,"epoch":1753484040,"high":123.0982,"low":122.8273,"open":123.092},
  {"close":122.9725,"epoch":1753484220,"high":122.9725,"low":122.6842,"open":122.8689}
]</textarea>
  <br>
  <button onclick="analyze()">Analyze</button>

  <div id="prediction"></div>
  <div id="chart"></div>
  <table id="resultTable"></table>

<script>
class IndicatorCalc {
  constructor(candles) { this.candles = candles; }

  ema(period) {
    const k = 2 / (period + 1);
    const out = [];
    let prev = this.candles[0].close;
    for (let i = 0; i < this.candles.length; i++) {
      const price = this.candles[i].close;
      const val = (i === 0) ? price : (price - prev) * k + prev;
      out.push(val);
      prev = val;
    }
    return out;
  }

  rsi(period = 14) {
    const res = [null];
    const gains = [], losses = [];
    for (let i = 1; i < this.candles.length; i++) {
      const diff = this.candles[i].close - this.candles[i-1].close;
      gains.push(Math.max(diff, 0));
      losses.push(Math.max(-diff, 0));
      if (i >= period) {
        const avgG = gains.slice(-period).reduce((a,b)=>a+b,0)/period;
        const avgL = losses.slice(-period).reduce((a,b)=>a+b,0)/period;
        const rs = (avgL === 0) ? 9999 : avgG / avgL;
        res.push(100 - (100 / (1 + rs)));
      } else res.push(null);
    }
    return res;
  }

  bollinger(period = 20, mult = 2) {
    const out = [];
    for (let i = 0; i < this.candles.length; i++) {
      if (i < period) { out.push({upper:null, lower:null, width:null}); continue; }
      const slice = this.candles.slice(i - period, i).map(c => c.close);
      const mean = slice.reduce((a,b)=>a+b,0)/period;
      const varr = slice.reduce((a,b)=>a+Math.pow(b-mean,2),0)/period;
      const sd = Math.sqrt(varr);
      out.push({ upper: mean + mult*sd, lower: mean - mult*sd, width: (mult*2*sd) });
    }
    return out;
  }

  atr(period = 14) {
    const trs = [];
    const out = [null];
    for (let i = 1; i < this.candles.length; i++) {
      const h = this.candles[i].high, l = this.candles[i].low, pc = this.candles[i-1].close;
      const tr = Math.max(h - l, Math.abs(h - pc), Math.abs(l - pc));
      trs.push(tr);
      if (i >= period) {
        out.push(trs.slice(-period).reduce((a,b)=>a+b,0)/period);
      } else out.push(null);
    }
    return out;
  }

  bodyRatio() {
    return this.candles.map(c => {
      const denom = (c.high - c.low) || 1e-9;
      return Math.abs(c.close - c.open) / denom;
    });
  }

  analyzeEach(ema3, ema5, rsi, bb, atr, body) {
    const out = [];
    for (let i = 0; i < this.candles.length; i++) {
      const c = this.candles[i];
      const color = c.close > c.open ? "Green" : (c.close < c.open ? "Red" : "Doji");
      const emaTrend = ema3[i] > ema5[i] ? "Bullish" : (ema3[i] < ema5[i] ? "Bearish" : "Flat");
      const rsiZone = (rsi[i] === null) ? "-" : (rsi[i] > 70 ? "Overbought" : (rsi[i] < 30 ? "Oversold" : "Neutral"));
      const vol = (i < 1 || atr[i] === null || atr[i-1] === null) ? "-" :
                  (atr[i] > atr[i-1]*1.05 ? "Increasing" : (atr[i] < atr[i-1]*0.95 ? "Decreasing" : "Stable"));
      const summary =
        (rsiZone === "Neutral" && Math.abs(ema3[i]-ema5[i]) < (ema5[i]*0.0002) && body[i] < 0.2)
          ? "Sideway"
          : (emaTrend === "Bullish" ? "Trend Up" : (emaTrend === "Bearish" ? "Trend Down" : "Unclear"));
      out.push({ color, emaTrend, rsiZone, vol, summary });
    }
    return out;
  }

  predict() {
    const ema3 = this.ema(3), ema5 = this.ema(5);
    const rsi = this.rsi(14), bb = this.bollinger(20,2), atr = this.atr(14), body = this.bodyRatio();
    const i = this.candles.length - 1;
    if (i < 20) return "uncertain";
    const emaDiffNow = Math.abs(ema3[i] - ema5[i]), emaDiffPrev = Math.abs(ema3[i-1] - ema5[i-1]);
    const rsiNow = rsi[i], atrNow = atr[i], atrPrev = atr[i-1];
    const bbNow = bb[i].width, bbPrev = bb[i-1].width;
    const bodyNow = body[i], avgBody = body.slice(-5).reduce((a,b)=>a+b,0)/5;

    const isEmaFlat = emaDiffNow < emaDiffPrev * 0.7;
    const isRsiMid = rsiNow > 45 && rsiNow < 55;
    const isLowVol = (atrNow !== null && atrPrev !== null) ? (atrNow < atrPrev * 0.9 && bbNow < bbPrev * 0.9) : false;
    const isShortBody = bodyNow < avgBody * 0.8;

    if (isEmaFlat && isRsiMid && isLowVol && isShortBody) return "sideway_coming";
    if (!isEmaFlat && (rsiNow < 40 || rsiNow > 60)) return "trend_continue";
    return "uncertain";
  }
}

function analyze() {
  let candles;
  try {
    candles = JSON.parse(document.getElementById('candleInput').value);
  } catch (err) {
    alert('JSON ไม่ถูกต้อง: ' + err.message);
    return;
  }

  const calc = new IndicatorCalc(candles);
  const ema3 = calc.ema(3), ema5 = calc.ema(5);
  const rsi = calc.rsi(), bb = calc.bollinger(), atr = calc.atr(), body = calc.bodyRatio();
  const analysis = calc.analyzeEach(ema3, ema5, rsi, bb, atr, body);
  const prediction = calc.predict();

  document.getElementById('prediction').innerHTML =
    '📊 การทำนายแท่งถัดไป: <b>' + prediction.toUpperCase() + '</b>';

  // chart
  const chartDiv = document.getElementById('chart');
  chartDiv.innerHTML = '';
  const chart = LightweightCharts.createChart(chartDiv, {
    width: chartDiv.clientWidth,
    height: 420,
    layout: { background: { color: '#ffffff' }, textColor: '#333' },
    grid: { vertLines: { visible: false }, horzLines: { color: '#eee' } }
  });

  const candleSeries = chart.addCandlestickSeries();
  const data = candles.map((c,i)=>({
    time: c.epoch ?? i+1,
    open:c.open, high:c.high, low:c.low, close:c.close
  }));
  candleSeries.setData(data);

  const ema3Series = chart.addLineSeries({ color:'#007bff', lineWidth:2 });
  ema3Series.setData(data.map((p,i)=>({time:p.time, value:ema3[i]})));
  const ema5Series = chart.addLineSeries({ color:'#ff7f0e', lineWidth:2 });
  ema5Series.setData(data.map((p,i)=>({time:p.time, value:ema5[i]})));

  window.addEventListener('resize',()=>chart.applyOptions({width:chartDiv.clientWidth}));

  // table
  const tbl = document.getElementById('resultTable');
  let html = `<tr>
  <th>#</th><th>Time</th><th>Open</th><th>Close</th>
  <th>Color</th><th>EMA Trend</th><th>RSI Zone</th>
  <th>Volatility</th><th>Body%</th><th>Summary</th>
  </tr>`;
  for (let i = 0; i < candles.length; i++) {
    html += `<tr>
      <td>${i+1}</td>
      <td>${candles[i].epoch ?? (i+1)}</td>
      <td>${candles[i].open.toFixed(4)}</td>
      <td>${candles[i].close.toFixed(4)}</td>
      <td>${analysis[i].color}</td>
      <td>${analysis[i].emaTrend}</td>
      <td>${analysis[i].rsiZone}</td>
      <td>${analysis[i].vol}</td>
      <td>${(body[i]*100).toFixed(2)}%</td>
      <td><b>${analysis[i].summary}</b></td>
    </tr>`;
  }
  tbl.innerHTML = html;
}
</script>
</body>
</html>
