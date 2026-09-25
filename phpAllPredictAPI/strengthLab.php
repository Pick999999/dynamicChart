<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Candlestick + EMA + Reason</title>
<script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
<style>
  body { font-family: Arial, sans-serif; margin:0; padding:0; background:#1e1e1e; color:#fff; }
  #chart { width: 100%; height: 400px; margin-bottom: 20px; }
  .custom-tooltip {
    position: absolute; display: none; background: rgba(0,0,0,0.9); color: #fff;
    padding: 6px; border-radius: 5px; font-size: 12px; pointer-events: none; z-index: 10;
  }
  textarea { width: 100%; height: 80px; margin-bottom:10px; background:#333; color:#fff; border:1px solid #555; padding:5px; border-radius:5px; }
  button { padding:6px 12px; border:none; border-radius:4px; background:#007bff; color:#fff; cursor:pointer; margin-bottom:10px; }
  button:hover { background:#0056b3; }
  table { width: 100%; border-collapse: collapse; margin-top:10px; background:#2e2e2e; color:#fff; }
  th, td { border:1px solid #555; text-align:center; padding:6px; }
  th { background:#444; }
</style>
</head>
<body>

<div style="padding:10px;">
  <h3>ใส่ข้อมูล Candlestick (JSON)</h3>
  <textarea id="inputData">[
  {"time":1,"open":100,"high":110,"low":95,"close":105},
  {"time":2,"open":105,"high":115,"low":100,"close":108},
  {"time":3,"open":108,"high":112,"low":102,"close":103},
  {"time":4,"open":103,"high":106,"low":97,"close":99},
  {"time":5,"open":99,"high":105,"low":95,"close":101}
]</textarea>
  <button onclick="analyze()">วิเคราะห์</button>
</div>

<div id="chart"></div>
<div class="custom-tooltip" id="tooltip"></div>

<div style="padding:10px;">
  <h3>ตารางวิเคราะห์</h3>
  <table>
    <thead>
      <tr>
        <th>เวลา</th>
        <th>เปิด</th>
        <th>ปิด</th>
        <th>Strength</th>
        <th>Forecast</th>
        <th>Win/Loss</th>
        <th>Loss Continue</th>
        <th>Max Loss</th>
        <th>Reason</th>
      </tr>
    </thead>
    <tbody id="analysis-table"></tbody>
  </table>
</div>

<script>
// ====== EMA ======
function calculateEMA(values, period){
  const k = 2/(period+1);
  let emaArr = [];
  let ema = values[0];
  emaArr.push(ema);
  for(let i=1;i<values.length;i++){
    ema = values[i]*k + ema*(1-k);
    emaArr.push(ema);
  }
  return emaArr;
}

// ====== continuityStrength ======
function continuityStrength(candles, index){
  let strength = 0;
  let start = Math.max(0,index-4);
  for(let i=start+1;i<=index;i++){
    let prev = candles[i-1].close - candles[i-1].open;
    let curr = candles[i].close - candles[i].open;
    if((prev>0 && curr>0)||(prev<0 && curr<0)) strength++;
  }
  return strength;
}

// ====== Analyze ======
function analyze(){
  let textarea = document.getElementById("inputData");
  let tableBody = document.getElementById("analysis-table");
  tableBody.innerHTML = "";

  let input;
  try{ input = JSON.parse(textarea.value); }
  catch(e){ alert("JSON ไม่ถูกต้อง"); return; }

  // คำนวณ EMA
  let closes = input.map(d=>d.close);
  let ema3 = calculateEMA(closes,3);
  let ema5 = calculateEMA(closes,5);

  // คำนวณ Loss Continue
  let lossContinue = 0;
  let maxLossContinue = 0;
  let maxLossTime = null;

  input.forEach((d,i)=>{
    let strength = continuityStrength(input,i);
    let forecast = ema3[i]>ema5[i] ? "green" : "red";
    let actual = d.close>d.open ? "green" : "red";
    let winloss = (forecast===actual) ? "Win" : "Loss";

    if(winloss==="Loss") lossContinue++;
    else lossContinue=0;

    if(lossContinue>maxLossContinue){
      maxLossContinue = lossContinue;
      maxLossTime = d.time;
    }

    // Reason ภาษาไทย
    let reason = [];
    reason.push(`EMA3 ${ema3[i]>ema5[i] ? '>' : '<'} EMA5`);
    if(strength>=3) reason.push('แท่งต่อเนื่องสูง');
    else reason.push('แท่งต่อเนื่องต่ำ');
    reason = reason.join(', ');

    d.strength = strength;
    d.forecast = forecast;
    d.winloss = winloss;
    d.lossContinue = lossContinue;
    d.maxLossContinue = maxLossContinue;
    d.maxLossTime = maxLossTime;
    d.reason = reason;

    tableBody.innerHTML += `<tr>
      <td>${d.time}</td>
      <td>${d.open}</td>
      <td>${d.close}</td>
      <td>${strength}</td>
      <td style="color:${forecast}">${forecast}</td>
      <td>${winloss}</td>
      <td>${lossContinue}</td>
      <td>${maxLossContinue} (Time: ${maxLossTime})</td>
      <td>${reason}</td>
    </tr>`;
  });

  // ====== Plot chart ======
  const chartDiv = document.getElementById('chart');
  chartDiv.innerHTML="";
  const chart = LightweightCharts.createChart(chartDiv,{
    layout:{background:{color:'#1e1e1e'},textColor:'#fff'},
    grid:{vertLines:{color:'#555'},horzLines:{color:'#555'}},
    timeScale:{timeVisible:true,borderColor:'#555'}
  });

  const candleSeries = chart.addCandlestickSeries({
    upColor:'green', downColor:'red', borderVisible:true, wickUpColor:'green', wickDownColor:'red'
  });
  candleSeries.setData(input.map(d=>({
    time:d.time, open:d.open, high:d.high, low:d.low, close:d.close
  })));

  const ema3Series = chart.addLineSeries({color:'blue',lineWidth:2});
  const ema5Series = chart.addLineSeries({color:'yellow',lineWidth:2});
  ema3Series.setData(input.map((d,i)=>({time:d.time,value:ema3[i]})));
  ema5Series.setData(input.map((d,i)=>({time:d.time,value:ema5[i]})));

  // ===== Tooltip =====
  const tooltip = document.getElementById("tooltip");
  chart.subscribeCrosshairMove(param=>{
    if(!param.time || !param.point) { tooltip.style.display='none'; return; }
    const candle = input.find(d=>d.time==param.time);
    if(candle){
      tooltip.style.display='block';
      tooltip.style.left = param.point.x + 20 + 'px';
      tooltip.style.top = param.point.y + 20 + 'px';
      tooltip.innerHTML =
        `Time: ${candle.time}<br>`+
        `O: ${candle.open} H: ${candle.high}<br>`+
        `L: ${candle.low} C: ${candle.close}<br>`+
        `Strength: ${candle.strength}<br>`+
        `Forecast: <span style="color:${candle.forecast}">${candle.forecast}</span><br>`+
        `Win/Loss: ${candle.winloss}<br>`+
        `Loss Continue: ${candle.lossContinue}<br>`+
        `Max Loss: ${candle.maxLossContinue} (Time: ${candle.maxLossTime})<br>`+
        `Reason: ${candle.reason}`;
    }
  });
}
</script>

</body>
</html>
