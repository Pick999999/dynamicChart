<!DOCTYPE html>
<html>
<head><script src="https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js"></script></head>
<body>
  <select id="asset-select"><option>R_100</option></select>
  <button id="btnFetch">Fetch & Analyze</button>
  <div id="chart" style="height: 400px;"></div>
  <table border="1" id="entryTable">
    <tr><th>Time</th><th>Price</th><th>%K</th><th>%D</th><th>CCI</th><th>Action</th></tr>
  </table>
<script>
const sock = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=66726');
sock.onopen = () => console.log('WS open');
sock.onmessage = async ({ data }) => {
  const res = JSON.parse(data);
  if (res.id === 1 || res.id === 2) processCandles(res);
};

document.getElementById('btnFetch').onclick = () => {
  const asset = document.getElementById('asset-select').value;
  const now = Math.floor(Date.now() / 1000);
  const ago1min = now - 60 * 60;
  const ago1h = now - 60 * 60 * 24;
  sock.send(JSON.stringify({ method: 'public/get_tradingview_chart_data', id: 1, params: { symbol: asset, granularity: 60, range: { start: ago1min, end: now }}}));
  sock.send(JSON.stringify({ method: 'public/get_tradingview_chart_data', id: 2, params: { symbol: asset, granularity: 3600, range: { start: ago1h, end: now }}}));
};

let candles = [], stoch = [], cciArr = [], chart, candleSeries, stochSeries, cciSeries;

function processCandles(res) {
  if (res.result && res.result.open) {
    const data = res.result;
    const arr = data.ticks.map((t,i) => ({ time: t/1000, open: data.open[i], high: data.high[i], low: data.low[i], close: data.close[i] }));
    candles = arr;
    computeIndicators();
    renderChart();
    analyzeEntries();
  }
}

function computeIndicators() {
  const period = 14;
  stoch = [];
  cciArr = [];
  for (let i = 0; i < candles.length; i++) {
    if (i >= period - 1) {
      const slice = candles.slice(i-period+1, i+1);
      const lows = slice.map(c=>c.low), highs = slice.map(c=>c.high), closes = slice.map(c=>c.close);
      const lowest = Math.min(...lows), highest = Math.max(...highs), close = candles[i].close;
      const percentK = 100*(close - lowest)/(highest - lowest);
      stoch.push({ time: candles[i].time, value: percentK });
      const sma = slice.reduce((s,c)=>s+c.close,0)/period;
      const dev = slice.reduce((s,c)=>s+Math.abs((c.close - sma)),0)/period;
      cciArr.push({ time: candles[i].time, value: (close - sma)/(0.015*dev) });
    } else {
      stoch.push({ time: candles[i].time, value: null });
      cciArr.push({ time: candles[i].time, value: null });
    }
  }
}

function renderChart() {
  if (!chart) {
    chart = LightweightCharts.createChart(document.getElementById('chart'), { width:600, height:400 });
    candleSeries = chart.addCandlestickSeries();
    stochSeries = chart.addLineSeries({ color: 'blue', yAxisId: 'stoch' });
    cciSeries = chart.addLineSeries({ color: 'purple', yAxisId: 'cci' });
    chart.addAxis('stoch', { title: 'Stochastic' });
    chart.addAxis('cci', { title: 'CCI' });
  }
  candleSeries.setData(candles);
  stochSeries.setData(stoch.filter(d=>d.value!=null));
  cciSeries.setData(cciArr.filter(d=>d.value!=null));
}

function analyzeEntries() {
  const table = document.getElementById('entryTable');
  while (table.rows.length > 1) table.deleteRow(1);
  candles.forEach((c,i) => {
    const k = stoch[i].value, ci = cciArr[i].value;
    let action = '';
    if (k < 20 && ci < -100) action = 'Call';
    else if (k > 80 && ci > 100) action = 'Put';
    if (action) {
      const row = table.insertRow();
      row.insertCell().textContent = new Date(c.time * 1000).toLocaleTimeString();
      row.insertCell().textContent = c.close;
      row.insertCell().textContent = k.toFixed(2);
      row.insertCell().textContent = '-'; 
      row.insertCell().textContent = ci.toFixed(2);
      row.insertCell().textContent = action;
      chart.addMarker(candleSeries, { time: c.time, position: action==='Call'?'belowBar':'aboveBar', shape: 'arrowUp', color: action==='Call'?'green':'red', text: action });
    }
  });
}
</script>
</body>
</html>
