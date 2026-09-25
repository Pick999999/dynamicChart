<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚙️ Choppiness Index Lab - Multi-Indicator</title>


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        } 

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2em;
        }

        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 25px;
            font-size: 0.9em;
        }

        .control-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        .control-group label {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.85em;
            color: #555;
        }

        .control-group input,
        .control-group select,
        .control-group button {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 0.9em;
            transition: all 0.3s;
        }

        button {
            background: #667eea;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        button:hover:not(:disabled) {
            background: #5568d3;
            transform: translateY(-2px);
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .trading-hint {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .trading-hint.call {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .trading-hint.put {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .trading-hint.idle {
            background: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%);
        }

        .hint-title {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .hint-content {
            font-size: 1.2em;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .hint-details {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
        }

        .hint-detail-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }

        .hint-detail-item strong {
            display: block;
            font-size: 0.75em;
            margin-bottom: 5px;
        }

        .hint-detail-item span {
            font-size: 1.2em;
            font-weight: bold;
        }

        .status-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .status-card.choppy {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .status-card.neutral {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #333;
        }

        .status-card.trending {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }

        .status-card h3 {
            font-size: 0.8em;
            margin-bottom: 5px;
        }

        .status-card .value {
            font-size: 1.6em;
            font-weight: bold;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .chart-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #667eea;
        }

        .chart-box {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }

        .history-panel {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .history-list {
            max-height: 300px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .history-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            border-left: 5px solid #667eea;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 12px;
            align-items: center;
        }

        .history-item.call {
            border-left-color: #38ef7d;
        }

        .history-item.put {
            border-left-color: #f45c43;
        }

        .history-item.idle {
            border-left-color: #95a5a6;
        }

        .history-icon {
            font-size: 1.8em;
        }

        .history-action {
            font-weight: bold;
            font-size: 1em;
            margin-bottom: 5px;
        }

        .history-time {
            font-size: 0.75em;
            color: #888;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
    
        .mBtn {
            border-radius: 15px;
            background: white;
            color: #0080c0;
            padding: 10px;
            cursor: pointer;
            border: 1px solid gray;
            margin: 5px;
        }

        .mBtn:hover {
            border: 1px solid lightgray;
            color: white;
        } 
		.btnSelected {
           background:#00ff00;
		   color:white;
		}

		.tt  { 
		  padding: 8px 12px;
          border: 2px solid #ddd;
          border-radius: 6px;
          font-size: 0.9em;
          transition: all 0.3s;
		}
		.box,.boxStatus {
		  border:1px solid gray; border-radius:8px;padding:8px ;
		  margin:10px ;cursor:pointer
		 }
		.bgred { background: #ff0000 ; color:white }
		.bgGreen { background: #00ff00 }
		.bgGray { background: #dbdbdb }
		input[type="text"] {
			swidth: 100%;
			padding: 10px;
			border: 2px solid #ccc;
			border-radius: 4px;
        }
		input[type="text"]:focus {
			border-color: #3498db;
			outline: none;
			box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
		}


    </style>


</head>

<body>
    <div class="container">
        <h1>📊 Choppiness Index Lab - Multi-Indicator</h1>
        <div class="subtitle">CI + ADX + BB Width + Color Pattern Detection</div>

		onclick="preLoadCandle('1HZ10V')">1HZ-R10</button>
		<div id="" class="bordergray flex">
		   ☑️  🔘 ⚪  ⚫ 🔴 🔵 🟤 🟣 🟢 🟠 🟧 🟦 🍏 🍎 🍊 ✅ ✅ ❎ ❌  
		</div>
	

        <div class="control-panel">
            <div class="control-group">
                <label>Symbol</label>
                <select id="symbolSelect" onchange='RetrieveCodeStatusFromDB()' onclick='RetrieveCodeStatusFromDB()'>
                    <option value="R_10">Volatility 10</option>
                    <option value="R_25">Volatility 25</option>
                    <option value="R_50">Volatility 50</option>
                    <option value="R_75">Volatility 75</option>
                    <option value="R_100">Volatility 100</option>
                    <option value="1HZ10V">Vol 10 (1s)</option>
					<option value="1HZ25V">Vol 25 (1s)</option>
					<option value="1HZ50V">Vol 50 (1s)</option>
					<option value="1HZ75V">Vol 75 (1s)</option>
					<option value="1HZ100V">Vol 100 (1s)</option>
                    <option value="frxEURUSD">EUR/USD</option>
                </select>
            </div>
            <div class="control-group">
                <label>Timeframe</label>
                <select id="timeframeSelect">
                    <option value="60" selected>1 Minute</option>
                    <option value="300">5 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="3600">1 Hour</option>
                    <option value="14400">4 Hours</option>
                </select>
            </div>

            <div class="control-group">
                <div class="form-group">
                    <label><span class="icon">📅</span> Start Date</label>
                    <input type="datetime-local" class="form-control" id="startDate">
                </div>
                <div class="form-group">
                    <label><span class="icon">📅</span> End Date</label>
                    <input type="datetime-local" class="form-control" id="endDate">
                </div>
                <div class="form-group" style='padding:10px'>
                    <input type="checkbox" id="useLatests">&nbsp;&nbsp;Use Latest
                </div>
            </div>

            <div class="control-group">
                <label>CI Period</label>
                <input type="number" id="ciPeriod" value="7" min="5" max="50">
            </div>
            <div class="control-group">
                <label>CI Threshold</label>
                <input type="number" id="ciThreshold" value="61.8" min="40" max="80" step="0.1">
            </div>
            <div class="control-group">
                <label>ADX Period</label>
                <input type="number" id="adxPeriod" value="14" min="7" max="30">
            </div>
            <div class="control-group">
                <label>BB Period</label>
                <input type="number" id="bbPeriod" value="20" min="10" max="50">
            </div>
            <div class="control-group">
                <label>ATR Period</label>
                <input type="number" id="atrPeriod" value="14" min="5" max="50">
            </div>

            
            <div class="control-group">
                <label>&nbsp;</label>
                <button id="connectBtn">🔌 Connect</button>
                <div class="checkbox-group" style="margin-top:8px;">
                    <input type="checkbox" id="autoLoadingChk" checked>
                    <label for="autoLoadingChk" style="margin:0; font-size:0.85em;">Auto Loading</label>
                </div>
            </div>
            <div class="control-group">
                <label>&nbsp;</label>
                <button id="loadDataBtn" disabled>📥 Load Data</button>
            </div>
            <div class="control-group">
                <label>&nbsp;</label>
                <button id="updateBtn" disabled>🔄 Update</button>
            </div>
            <div class="control-group">
                <label>&nbsp;</label>
                <button id="BtnClassEMA" onclick='BtnClassEMA_Click()'>🔄 Test Class</button>
            </div>
            <div class="control-group">
                <label>Alert</label>
                <div class="checkbox-group">
                    <input type="checkbox" id="alertEnabled" checked>
                    <label for="alertEnabled" style="margin:0">Enable</label>
                </div>
            </div>
            <div class="control-group">
                <label>BB Display</label>
                <div class="checkbox-group">
                    <input type="checkbox" id="bbEnabled" checked>
                    <label for="bbEnabled" style="margin:0">Show BB</label>
                </div>
            </div>
        </div>

        <div class="trading-hint idle" id="tradingHint">
            <div class="hint-title">
                <span id="hintIcon">⏸️</span>
                <span id="hintTitle">Waiting for Data...</span>
            </div>
            <div class="hint-content" id="hintContent">
                Connect to Deriv and load data
            </div>
            <div class="hint-details" id="hintDetails" style="display:none;">
                <div class="hint-detail-item"><strong>CI</strong><span id="hintCI">--</span></div>
                <div class="hint-detail-item"><strong>ADX</strong><span id="hintADX">--</span></div>
                <div class="hint-detail-item"><strong>BB Width</strong><span id="hintBB">--</span></div>
                <div class="hint-detail-item"><strong>Color Alt</strong><span id="hintColor">--</span></div>
                <div class="hint-detail-item"><strong>EMA</strong><span id="hintEMA">--</span></div>
                <div class="hint-detail-item"><strong>Conf</strong><span id="hintConfidence">--</span></div>
            </div>
        </div>

        <div class="status-panel">
            <div class="status-card" id="ciCard">
                <h3>CI</h3>
                <div class="value" id="ciValue">--</div>
            </div>
            <div class="status-card" id="adxCard">
                <h3>ADX</h3>
                <div class="value" id="adxValue">--</div>
            </div>
            <div class="status-card" id="bbCard">
                <h3>BB Width</h3>
                <div class="value" id="bbValue">--</div>
            </div>
            <div class="status-card" id="marketStateCard">
                <h3>Market</h3>
                <div class="value" id="marketState">WAIT</div>
            </div>
            <div class="status-card">
                <h3>Signal</h3>
                <div class="value" id="tradingSignal">⏸️</div>
            </div>
            <div class="status-card">
                <h3>Markers</h3>
                <div class="value" id="markerCount">0</div>
            </div>

            <div id='colorCard' class="status-card">
                <h3>Color List</h3>
                <div class="value" id="markerCount">0</div>
            </div>


        </div>

        <div class="history-panel">
            <div class="history-header">
                <h3>📜 Signal History</h3>
                <button onclick="clearHistory()" style="padding:5px 12px;font-size:0.85em">🗑️ Clear</button>
            </div>
            <div class="history-list" id="historyList">
                <div style="text-align:center;color:#888;padding:20px">No signals yet</div>
            </div>
        </div>
		<div style="display:flex;flex-direction:row;background:lightgray;border:1px solid gray;padding:10px">
		<h2>Asset List</h2>
	<button type='button' id='btnR10' class='mBtn' onclick="preLoadCandle('R_10')">R_10</button>
	<button type='button' id='btnR25' class='mBtn' onclick="preLoadCandle('R_25')">R_25</button>
	<button type='button' id='btnR50' class='mBtn' onclick="preLoadCandle('R_50')">R_50</button>
	<button type='button' id='btnR75' class='mBtn' onclick="preLoadCandle('R_75')">R_75</button>
	<button type='button' id='btnR100' class='mBtn' onclick="preLoadCandle('R_100')">R_100</button>
	<button type='button' id='btnR10' class='mBtn' onclick="preLoadCandle('1HZ10V')">1HZ-R10</button>
	<button type='button' id='btnR25' class='mBtn' onclick="preLoadCandle('1HZ25V')">1HZ-R25</button>
	<button type='button' id='btnR50' class='mBtn' onclick="preLoadCandle('1HZ50V')">1HZ-R50</button>
	<button type='button' id='btnR75' class='mBtn' onclick="preLoadCandle('1HZ75V')">1HZ-R75</button>
	<button type='button' id='btnR100' class='mBtn' onclick="preLoadCandle('1HZ100V')">1HZ-R_100</button>
</div>


        <div class="chart-container">
            <div class="chart-title">💹 Price Chart <span id="chartSymbol"
                    style="color:#888;font-size:0.9em;float:right">No data</span></div>
            <button type='button' id='' class='mBtn' onclick="clearAllMarkers()">Clear Marker</button>

            <button type='button' id='' class='mBtn' onclick="AddMACD12Markers()"> Marker MACD12</button>


            <button type='button' id='' class='mBtn' onclick="AddPIPSmallMarkers()"> Marker PIP Small</button>

            <button type='button' id='' class='mBtn' onclick="AddFlatSlopeMarkers()"> Marker Flat Slope</button>

			<button type='button' id='' class='mBtn' onclick="AddChoppyMarkers()"> Marker Choppy Over</button>

			<button type='button' id='' class='mBtn' onclick="AddEMAConflictMarkers()"> EMA Conflict Marker</button>

			<button type='button' id='' class='mBtn' onclick="CreateTradeBackTest()">Test Trade</button>



            <button type='button' id='' class='mBtn' onclick="highlightCandles()"> ATR</button>
            <br>
			<input type="checkbox" id="createNewStatusCandleCode">Create-NewStatusCandleCode
            <button type='button' id='btnGenerateAnalysis' class='mBtn' onclick="generateAnalysisData()" title='indicator.js'>📊 Generate
                Analysis</button>

            <button type='button' id='btnEvalAA' class='mBtn' onclick="processStatusMaster()">📊 สร้างข้อมูล Candle Code จาก AnalysisData(ลงใน CandleCode Text)
             </button>

            <button type='button' id='btnEval' class='mBtn' onclick="EvaluateMarket()">📊 สร้าง Candle Code
             </button>

			 <button type='button' id='btnEval' class='mBtn' onclick="doAjaxRetrieveCodeCandle()">📊 ดึง Candle Code จาก Database
             </button>
			 
			 

			 <button type='button' id='btnEval' class='mBtn' onclick="doAjaxPostCodeCandle()">📊 บันทึก ข้อมูล CodeCandle ไปยัง DB
             </button>

 

            <select id="atrMultiplier" style="padding:8px; border-radius:6px; margin:5px;">
                <option value="1.5">ATR x1.5</option>
                <option value="2" selected>ATR x2</option>
                <option value="2.5">ATR x2.5</option>
                <option value="3">ATR x3</option>
            </select>

            <button type="button" id="previousIndex" class="mBtn">⬅️</button>
            <button type="button" id="nextIndex" class="mBtn">➡️</button>
			<div id="" class="bordergray flex">
			   <h2>Code Candle Master Information </h2>
			   <div id="candleState" class="bordergray flex" style='display:flex;'>
			       <div id="rawDataStatus" class="bordergray flex box" >
			           Raw Data :: <span id='rawDataStatus2' style='color:red;font-weight:bold'></span>  
			        </div>
			       <div id="analysisDataStatus" class="bordergray flex box" >
			           AnalysisData :: <span id='analysisDataStatus2' style='color:red;font-weight:bold'></span>  
			        </div>
			        <div id="CandleTextStatus" class="bordergray flex box" >
			           Code Candle Text :: <span id='CandleTextStatus2' style='color:red;font-weight:bold'></span>  
			        </div>
			        <div id="CandleDBStatus" class="bordergray flex box">
			           Code Candle DB  :: <span id='CandleDBStatus2'style='color:red;font-weight:bold'></span> 
			        </div>
			   </div>
			   <button type='button' id='' class='mBtn' onclick="UpdateCandleStatus()">ตรวจสอบข้อมูลรวม </button>
			   <div id="CodeCandleInfo" class="bordergray flex" style='padding:8px;width:100%;background:#dedede'>

			   <button type='button' id='' class='mBtn' onclick="RetrieveCodeStatusFromDB()">Retrieve StatusCode Master From DB</button>
			   <div id="CodeCandleInfo" class="bordergray flex" style='padding:8px;width:100%;background:#dedede'>
			        
			   </div>
			</div>
            <table>
                <tr>
                    <td><input type="checkbox" id="ema1Show" checked onclick='ToggleEMA(1)'>EMA-1 </td>
                    <td><input type="checkbox" id="ema2Show" checked onclick='ToggleEMA(2)'>EMA-2 </td>
                    <td><input type="checkbox" id="ema3Show" checked onclick='ToggleEMA(3)'>EMA-3 </td>
                    <td><input type="checkbox" id="BBShow" checked onclick='ToggleEMA(4)'>BB </td>
					<td>
					  <button type='button' id='' class='mBtn' onclick="AdjustDisplayEMA()">Adjust All</button>
					</td>
					</tr>
					<tr>
					<td class="">Flat TheresHold<input type="number"  class= 'tt' id="flatThereshold" placeholder='0.05' title='0.05;0.005'> </td>
					<td class="scontrol-group">MACD TheresHold<input type="number" class= 'tt' id="macdThereshold"> </td>
					<td class="scontrol-group">
						ประเภทการตรวจสอบ ::<select id="markTypeCase" style="padding:8px; border-radius:6px; margin:5px;">
						    <option value="case1">slope12
							<option value="case2" selected>macd12
							
							<option value="case3">macd12+slope12 

							<option value="case4" selected>macd23
							<option value="case5">slope23
							<option value="case6">macd23+slope23 

							<option value="case7">EMALongCut
							<option value="case8">EMALong-Flat

							<option value="case9">Code=UU
							<option value="case92">Code=DD
							<option value="case10">Code=UF
							<option value="case11">Code=DF
							<option value="case12">Code=FU
							<option value="case13">Code=FF

							<option value="case14">MACD
							


						 </select>
					 </td>
					 <td><button type='button' id='' class='mBtn' onclick="MarkByCase()">เริ่มตรวจสอบ</button>
					 </td>

                </tr>
            </table>
			<div
                style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; background: #eef2f5; padding: 10px; border-radius: 8px; margin-bottom: 5px;">
                <div class="control-group">
				   <div class="checkbox-group">
                        <input type="checkbox" id="isLabMode">
                        <label for="ma1Enabled" style="font-weight:bold; color:#ff0080">Is Lab Mode</label>
						&nbsp;&nbsp;&nbsp;
						 Time Candle <input type="number" id="timeCandleSelected" value="20" style="swidth: 70px;" placeholder="">

                    </div>
				   
                    <div class="checkbox-group">
                        <input type="checkbox" id="ma1Enabled" checked>
                        <label for="ma1Enabled" style="font-weight:bold; color:#007bff;">Line 1 (Blue)</label>
                    </div>
                    <div style="display:flex; gap:5px; margin-top:5px;">
                        <input type="number" id="ma1Period" value="20" style="width: 70px;" placeholder="Period" onchange='calculateAllIndicators()'>
                        <select id="ma1Type" style="flex:1;" onclick='calculateAllIndicators()'>
                            <option value="EMA">EMA</option>
                            <option value="HMA">HMA</option>
                            <option value="EHMA">EHMA</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="ma2Enabled" checked>
                        <label for="ma2Enabled" style="font-weight:bold; color:#00ff00">Line 2 (Green)</label>
                    </div>
                    <div style="display:flex; gap:5px; margin-top:5px;">
                        <input type="number" id="ma2Period" value="50" style="width: 70px;" placeholder="Period" onchange='calculateAllIndicators()'>
                        <select id="ma2Type" style="flex:1;" onclick='calculateAllIndicators()'>
                            <option value="EMA">EMA</option>
                            <option value="HMA">HMA</option>
                            <option value="EHMA">EHMA</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="ma3Enabled" checked>
                        <label for="ma3Enabled" style="font-weight:bold; color: #ff0000">Line 3 (Red)</label>
                    </div>
                    <div style="display:flex; gap:5px; margin-top:5px;">
                        <input type="number" id="ma3Period" value="200" style="width: 70px;" placeholder="Period" onclick='calculateAllIndicators()'>
                        <select id="ma3Type" style="flex:1;">
                            <option value="EMA">EMA</option>
                            <option value="HMA">HMA</option>
                            <option value="EHMA">EHMA</option>
                        </select>
                    </div>
                </div>
            </div>
			<div id="" class="bordergray flex">
			   
			   <span style='font-size:22px;color:red;font-weight:bold' id='assetSelect'></span>
			</div>


            <div id="" class="bordergray flex" style='border:1px solid gray;border-radius:8px;background:whitesmoke;padding:10px'>
			 Status CodeTo Mark Red 🔴 : <input type="text" id="CodeToSearch" style='padding:8px;border-radius:8px;'>
			 Status CodeTo Mark Green 🟢 : <input type="text" id="CodeToSearchGreen" style='padding:8px;border-radius:8px;'>


			 Choppy Indicator :: <input type="text" id="choppyFilter" value=40>
			 <button type='button' id='' class='mBtn' onclick="MarkByCandleCode()">Mark Status Candle Code</button>

			 <button type='button' id='' class='mBtn' onclick="LabCode()">
			  LabCode()
			 </button>

			 <button type='button' id='' class='mBtn' onclick="LabSlopeFlat()">
			  Lab Slope Flat()
			 </button>


			 <div id="labResultA" class="bordergray flex box" >
			      
			 </div>
			 <div id="labResult" class="bordergray flex box" >
			      
			 </div>



			 🍏 Hint&nbsp;🔔<span id='hintCandleCode' 
			 style='color:red;font-weight:bold'></span>
			 <div id="" class="bordergray flex box">
			   Asset Code  ::<input type="text" id="AssetCode">
			   Candle Code List ::<input type="text" id="CandleCodeList">
			   Hint ::<input type="text" id="hint">
			   Suggest ::<input type="text" id="SuggestAction">
			   <button type='button' id='' class='mBtn' onclick="fff()">บันทึก</button>

			      
			 </div>
                 
            </div>
            <div class="chart-box" id="mainChart" style="height:400px"></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
            <div class="chart-container">
                <div class="chart-title">📈 Choppiness Index</div>
                <div class="chart-box" id="ciChart" style="height:180px"></div>
            </div>
            <div class="chart-container">
                <div class="chart-title">📊 ADX</div>
                <div class="chart-box" id="adxChart" style="height:180px"></div>
            </div>
        </div>
    </div>

    <h2>RawData</h2>
    Current-Index :: <input type="text" id="currentIndex" value=0>
    <textarea id="dataAllTxt" style='width:100%;height:150px;margin-top:20px'></textarea>

    <h2>📊 Analysis Data</h2>
    <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
        <span>Total Records: <strong id="analysisCount">0</strong></span>
        <span>| EMA Crossovers: <strong id="emaCrossCount">0</strong></span>
        <span style="color:#11998e">| UpTrend: <strong id="upTrendCount">0</strong></span>
        <span style="color:#eb3349">| DownTrend: <strong id="downTrendCount">0</strong></span>
		<button type="button" class="mBtn" onclick="document.getElementById('analysisDataTxt').value=''">📋 Clear Data</button>
        <button type="button" class="mBtn" onclick="copyAnalysisData()">📋 Copy</button>
        <button type="button" class="mBtn" onclick="downloadAnalysisData()">💾 Download</button>
    </div>
	<div id="statusDescStatic" class="bordergray flex">
	     
	</div>
    <textarea id="analysisDataTxt" nonsave onchange = 'UpdateCandleStatus()' title='indicator.js'
        style='width:100%;height:300px;margin-top:10px;font-family:monospace;font-size:12px;background:#1e1e1e;color:#d4d4d4;border-radius:8px;padding:10px;'
        readonly></textarea>


    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <script src="choppiness-indexV3.js?ver=<?=rand(0,20000);?>"></script>
    <script src="js/clsAnalyEMA.js"></script>
    <script src="js/clsIndicator.js"></script>

	<script src="https://thepapers.in/js/indicator.js?ver=<?=rand(1,10000);?>" ></script>

    <script src="https://thepapers.in/phpAllPredictAPI/autoSaveInputs.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"
        integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>

<select name="">
	<option value="case1" selected>macd12
    <option value="case2">slope12
    <option value="case3">macd12+slope12 

	<option value="case4" selected>macd23
    <option value="case5">slope23
    <option value="case6">macd23+slope23 

    <option value="case7">EMA Cut
	<option value="case8">EMA Long Flat 

</select>

<style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        /* Highlight Classes */
        .bullish { color: #27ae60; font-weight: bold; }
        .bearish { color: #e74c3c; font-weight: bold; }
        .neutral { color: #f39c12; font-weight: bold; }
        .bg-bullish { background-color: #e8f6ef; }
        .bg-bearish { background-color: #fdeaea; }
        .bg-neutral { background-color: #fef5e7; }
    </style>
<div id="evalResult" class="bordergray flex">
     
</div>
<h2>All Code Candle </h2>
<textarea id="CodeCandle" nonsave style='width:100%;height:150px'>
</textarea>
<h2>All Code Candle DB </h2>
<textarea id="CodeCandleDB" nonsave style='width:100%;height:150px'>
</textarea>




1.macd12 < macdthereshold
2.macd23 < macdthereshold

3.slope12 <  slopethereshold
4.slope23 <  slopethereshold

1+3 และ 2+4 


เราจะใช้  
  emaMedium + emaLong บ่งบอกถึงสภาวะ ของ กราฟ
   A.) emaMediumDirection = Up, emaLongDirection = Up
   B.) emaMediumDirection = Down, emaLongDirection = Up
   C.) emaMediumDirection = Down, emaLongDirection = Down
   D.) emaMediumDirection = Up, emaLongDirection = Down
   E.) emaCutLongType = LongTrend
   F.) emaCutLongType = DownTrend
  ใช้ emaMeduim บ่งบอกสภาวะเริ่มกลับตัว
    ขณะที่  M กำลัง Downtrend ย่อมเกิด แท่งแดง แต่เมื่อมีแท่งเขียว ปรากฏมีสิ่งที่จะเกิดได้  2 อย่าง
	  แท่งต่อไปเป็น แท่ง แดง  -->waterFall
	  แท่งต่อไปเป็น แท่ง เขียว -->กลับตัวสู่ UpTrend

  สภาวะ  colorConflict คือสภาวะ ที่ 
   1.M เหนือ L -> Red
   2.L เหนือ M -> Green
  เกิดได้ ตามนี้ 
    1.waterFall
	2.emaLong เกิด Lag
	3.เกิด False Candle หรือ แท่งเทียนที่ มี atr ปกติ เช่น กำลังเกิด DownTrend 
	แต่มี แท่งเขียว มาแทรก ทำให้ emaLong ถูกดึง ให้ไปอยู่เหนือเส้น  Medium 
	  
 สรุป Code เบื้องต้น ให้ พิจารณาจาก Direction ->U-P-D เมื่อนำมาผสม
 ระหว่าง emaMedium+emaLong จะเกิดได้  6 แบบ
   UU
   UD

   DD
   DU

   UP
   DP

   PU
   PD



</body>

</html>