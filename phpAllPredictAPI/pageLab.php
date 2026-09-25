<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="https://api.iconify.design/mdi/chart-line.svg?color=%2326a69a">
<!-- สีเขียว #26a69a (เหมือนเทียนขาขึ้น) -->
    <title>Real Deriv API Candlestick Chart</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>

	
	<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>

	
	<link href="wating.css" rel="stylesheet">
	

	
	<script src="pageLab.js?ver=<?=rand(0,10000);?>"></script>
	<script src="derivUtil.js?ver=<?=rand(0,10000);?>"></script>

	<script src="CandleAnalysis.js?ver=<?=rand(0,10000);?>"></script>
<!-- 
	<script src="fvg.js?ver=<?=rand(0,10000);?>"></script>
 -->	

	
	<script src="clsExtra.js?ver=<?=rand(0,10000);?>"></script>
    <script src="adx.js?ver=<?=rand(0,10000);?>"></script>
	
    <script src="EMATradingSystem.js"></script>	
	<script src="clsTradeByAdjacentColor.js"></script>

	<script src="clsTradeByEMATrend.js"></script>
	<script src="clsTrendAnalyzer.js"></script>
	<script src="extra.js"></script>
	<script src="adxV3.js"></script>
	<script src="alternateColor.js"></script>
	<script src="bodyCode.js"></script>
    

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #1e1e1e;
            color: white;
         }
        .hide { display:none; }
		.selected { background:#ffff33 ; color:black } 
        #container {
            position: relative;
            width: 100%;
            height: 500px;
            margin-bottom: 20px;
        }
        
        #chart {
            width: 100%;
            height: 100%;
        }
		#chartOHLC {
            width: 100%;
            height: 100%;
        }
        
        #tooltip {
            position: absolute;
            display: none;
            background: rgba(0, 0, 0, 0.95);
            border: 1px solid #444;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            min-width: 200px;
        }
        
        .tooltip-row {
            margin: 3px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .tooltip-label {
            font-weight: bold;
            margin-right: 15px;
            color: #ccc;
        }
        
        .tooltip-value {
            color: #00ff88;
            font-family: monospace;
        }
        
        .tooltip-value.red { color: #ff4444; }
        .tooltip-value.blue { color: #4488ff; }
        .tooltip-value.orange { color: #ff8800; }
        .tooltip-value.time { color: #ffff88; }
        
        .controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
            padding: 20px;
            background: #2a2a2a;
            border-radius: 10px;
        }
        
        .control-group {
            background: #333;
            padding: 15px;
            border-radius: 8px;
        }
        
        .control-group h3 {
            margin: 0 0 10px 0;
            color: #00ff88;
            font-size: 14px;
        }
        
        input[type="text"] {
            margin: 5px 2px;
            padding: 3px 5px;
            background: #444;
            color: white;
            border: 1px solid #666;
            border-radius: 5px;
            font-size: 13px;
			height:20px;
        }

		select, button {
            margin: 5px 2px;
            padding: 8px 12px;
            background: #444;
            color: white;
            border: 1px solid #666;
            border-radius: 5px;
            font-size: 13px;
			height:33px;
        }
        
        button {
            cursor: pointer;
            transition: background 0.2s;
        }
        
        button:hover { background: #555; }
        button:active { background: #666; }
        
        .time-controls {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .time-input-group {
            display: flex;
            flex-direction: column;
            margin: 5px;
        }
        
        .time-input-group label {
            font-size: 12px;
            color: #ccc;
            margin-bottom: 3px;
        }
        
        .btn-group {
            display: flex;
            gap: 2px;
        }
        
        .btn-small {
            padding: 5px 8px;
            font-size: 12px;
            min-width: 30px;
        }
        
        .selected-info {
            background: #1a4a3a;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .status {
            padding: 10px;
            background: #333;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
        }
        
        .connection-status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .connected { background: #00ff88; }
        .disconnected { background: #ff4444; }
        .connecting { background: #ffaa00; }
        
        .loading {
            color: #ffaa00;
            font-weight: bold;
        }
		table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
			cursor:pointer;
			overflow-y: auto;    
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        } 

		tr:nth-child(odd) {
           background-color: #cee7ff;
           color: #fff;

        }    
		tr:nth-child(even) {
           background-color: white;
           color: black;
        } 

		/* fix header (เฉพาะ th แถวแรก) */
		#dataTable tr:first-child th {
		  position: sticky;
		  top: 0;
		  background: #f8f8f8;
		  z-index: 2;
		  border-bottom: 2px solid #999;
		}
         
        .filter {
            width: 100%;
            box-sizing: border-box;
        }
		th { color:black;background:#0080ff}
		td {  color:black ; border:1px solid #e2e2e2 ;}
		.profit-positive {
            color: #10b981;
            font-weight: 600;
			font-size:22px;
        }
        
        .profit-negative {
            color: #ef4444;
            font-weight: 600;
			font-size:22px;
        }
        
        .time-remaining {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #3b82f6;
        }
		.targetClass {
		   height:25px; padding:5px;
		   width:40px;text-align:center;
		   font-size:20px;
		   color: #0080c0;
		}
		.btnSelected {
          border:1px solid #00ff00 ;
		}
		.btnNormal { border:none }
		.flex { display:flex; align-items:center} 
    </style>

<style>
    .bordergray : { border:1px solid white; }
    td { border:1px solid gray }
    .asset-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .asset-buttons button {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .asset-buttons button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    #forexBtn { background: #2196F3; color: white; }
    #volatilityBtn { background: #9C27B0; color: white; }
    #cryptoBtn { background: #FF9800; color: white; }
    #commoditiesBtn { background: #4CAF50; color: white; }
    #indicesBtn { background: #F44336; color: white; }
    
    #assetSelect {
        width: 100%;
        height: 50px;
        padding: 10px;
        background: #2a2a2a;
        color: #fff;
        border: 1px solid #444;
        border-radius: 6px;
        font-size: 14px;
        font-family: monospace;
    }
    
    #assetSelect option {
        padding: 8px;
        margin: 2px 0;
    }
    
    #loadingStatus {
        margin-top: 10px;
        padding: 10px;
        background: #252525;
        border-radius: 4px;
        color: #999;
        font-size: 13px;
    }

	.box {
    position: relative;
    border: 2px solid #007bff;
    border-radius: 8px;
    padding: 20px;
    margin: 30px;
  }

  .box-label {
    position: absolute;
    top: -12px;
    left: 15px;
    background: white; /* ให้พื้นหลังกลืนกับหน้า */
    padding: 0 8px;
    font-weight: bold;
    color: #007bff;
    font-family: sans-serif;
  }

  .content {
    font-family: sans-serif;
    color: #333;
  }
  .flex { display:flex; }
	</style>


</head>
<body>
    <h1>Real-Time Deriv API Candlestick Chart</h1>
    
    <div class="controls">
        <div class="control-group">
            <h3>Asset & Connection</h3>
<div class="asset-buttons">
    <button id="forexBtn">🌍 Forex</button>
    <button id="volatilityBtn">📈 Volatility</button>
    <button id="cryptoBtn">₿ Crypto</button>
    <button id="commoditiesBtn">💰 Commodities</button>
    <button id="indicesBtn">📊 Indices</button>
</div>
<div id="loadingStatus">Select a group to load assets</div>

            <select id="assetSelect" onclick='SaveLocal()'>
			    <option value="frxEURUSD">EURUSD</option>
                <option value="R_10">Volatility 10 Index</option>
                <option value="R_25" selected>Volatility 25 Index</option>
                <option value="R_50">Volatility 50 Index</option>
                <option value="R_75">Volatility 75 Index</option>
                <option value="R_100">Volatility 100 Index</option>
                <option value="RDBEAR">Bear Market Index</option>
                <option value="RDBULL">Bull Market Index</option>
            </select>
            <select id="granularitySelect" onclick='SaveLocal()'>
                <option value="60">1 Minute</option>
				<option value="180">3 Minutes</option>
                <option value="300">5 Minutes</option>
                <option value="900">15 Minutes</option>
				<option value="1800">30 Minutes</option>
                <option value="3600">1 Hour</option>
            </select>
			<select id="AlterCheck">
				<option value="used" selected>Use
				<option value="noused">No Use
			</select>

            <button onclick="connectAndLoadData()">Connect & Load</button>
            <button onclick="disconnectWebSocket()">Disconnect</button>
        </div>
		<div class="control-group">
            <table>
			 <tr><td>
		       <label for="ema1">EMA 3:</label>
             </td>
			 <td>
              <input type="number" id="ema3" value="3" min="1" max="100" onchange='SaveLocal()'>
             </td>
			 </tr>
            <tr><td>
            <label for="ema1">EMA(5) Short:</label></td>
            <td> 
			 <input type="number" id="ema5" value="5" min="1" max="100" onchange='SaveLocal()'></td></tr>
			<tr><td>
            <label for="ema2">EMA Long:(50)</label></td><td>
            <input type="number" id="emaLong" value="50" min="1" max="100" onchange='SaveLocal()'>
            </td></tr> 
			<tr><td>
			<label for="emaLong">EMA Super Long:(100)</label>
			</td><td>
            <input type="number" id="emaSuperLong" value="100" min="1" max="1000" onchange='SaveLocal()'>
			</td></tr></table>
			<input type="checkbox" id="isShowSuperLong" onclick='ToggleSuperLong();SaveLocal()'>isShowSuperLong
        </div>
            
        
        <div class="control-group">
            <h3>Time Range</h3>
            <div class="control-group">
                <label for="startDate">Start Date:</label>
                <input type="datetime-local" id="startDate" onchange='SaveLocal()' value="2025-08-12T10:00"><hr>
				<select id="hourList">
				<?php
				  for ($i=0;$i<=23;$i++) { ?>
						<option value="<?=$i;?>"><?=$i;?>
					  
				  <?php    
				  }
				?>
				</select>
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('startDate','-', 'minute')">-1m</button>
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('startDate','+', 'minute')">+1m</button>
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('startDate','-', 'day')">-1D</button>
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('startDate','+', 'day');adjustDateTime2('stopDate','+', 'day')">+1D</button>
            </div>
			<div class="control-group">
                <label for="stopDate">Stop Time:</label>
                <input type="datetime-local" id="stopDate" onchange='SaveLocal()'  value="2025-08-12T12:00"><hr>
				
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('stopDate','-', 'minute')">-1m</button>
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('stopDate','+', 'minute')">+1m</button>
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('stopDate','-', 'day')">-1D</button>
				<button type='button' id='' class='mBtn' 
				onclick="adjustDateTime2('stopDate','+', 'day')">+1D</button>
            </div>
            <div class="time-input-group">
                <label>Count (bars):</label>
                <input type="number" id="countInput" value="0" min="10" max="1000" onchange='SaveLocal()'>
            </div>
			<div class="time-input-group">
                <label>ดึงจากปัจจุบัน(Now):</label>
                <input type="checkbox" id="useLatest" style='width:30px;height:30px' onchange='SaveLocal()'>
            </div>
			<div class="time-input-group">
                <label>ต้องการบันทึก :</label>
                <input type="checkbox" id="useSaved" style='width:30px;height:30px' onchange='SaveLocal()'>
            </div>

			

            <button id='btnHistoryData' onclick="loadHistoricalData()">Load Historical</button>
			<button onclick="testTrade()">Test Trade</button>
        </div>
        
        <div class="control-group">
            <h3>Time Controls</h3>
            <div class="time-controls">
                <div class="btn-group">
                    <button class="btn-small" onclick="adjustTime('start', 'hour', -1)">-1H</button>
                    <button class="btn-small" onclick="adjustTime('start', 'hour', 1)">+1H</button>
                    <button class="btn-small" onclick="adjustTime('start', 'minute', -15)">-15M</button>
                    <button class="btn-small" onclick="adjustTime('start', 'minute', 15)">+15M</button>
                </div>
                <span style="margin: 0 10px;">Start Time</span>
            </div>
            <button onclick="toggleTooltip()">Toggle Tooltip</button>
            <button onclick="toggleRealTime()">Toggle Real-Time</button>
			<hr>
			<h3>เข้า Orders (Long Time)</h3>
			<div id="authMessage" class="bordergray flex">
			     
			</div>
			<table>
			<tr>
				<td>Assets</td>
				<td><input type="text" id="SymBols"></td>
			</tr>
			<tr>
				<td>Granularity</td>
				<td><select id="tradeGranu" onclick='SaveLocal()'>
				    <option value="1-m" selected>1 นาที
                    <option value="3-m" selected>3 นาที
				    <option value="5-m" selected>5 นาที
					<option value="10-m" >10 นาที
                    <option value="15-m" >15 นาที
					<option value="30-m">30 นาที
					<option value="1-h">1 ชั่วโมง
					<option value="2-h">2 ชั่วโมง
					<option value="3-h">3 ชั่วโมง
					<option value="4-h">4 ชั่วโมง
					<option value="5-h">5 ชั่วโมง
					<option value="6-h">6 ชั่วโมง
				</select></td>
			</tr>
			<tr>
				<td>จำนวนเงิน</td>
				<td><input type="number" id="moneyTrade" onBlur='SaveLocal()'></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
			</tr>
			</table>
			<div id="hint" class="bordergray flex">
			 <span style='color:#ffff00;font-weight:bold'>🧏‍♂️ Hint!!!</span> 
             <div id='hintMsg' style='padding:5px;border:1px solid white;min-height:50px'></div> 
			     
			</div>
			<button onclick="PlaceTrade('PUT')">📕 PUT</button>
            <button onclick="PlaceTrade('CALL')">📗 CALL</button>

			<button onclick="TrackOrder()">Track Order</button>
			<div id="msgResistance" class="bordergray flex">
			  ยังไม่มี แนวรับ-แนวต้าน
			     
			</div>
			

			<div id="ContractList" class="bordergray flex">
			     
			</div>


        </div>
        
        <div class="control-group" style='display:none'>
            <h3>Selected Candle</h3>
            <div class="time-input-group">
                <label>Start:</label>
                <input type="text" id="selectedStart" readonly>
            </div>
            <div class="time-input-group">
                <label>End:</label>
                <input type="text" id="selectedEnd" readonly>
            </div>
            <button onclick="clearSelection()">Clear</button>
        </div>
    </div>
    
    <div class="selected-info" id="selectedInfo" style="display: none;">
        <strong>Selected:</strong> <span id="selectedCandleInfo">None</span>
    </div>

	<button type='button' id='' class='mBtn' onclick="AddMarkerConflicts()">Show Conflicts</button>
	<button id='btnGetLab' onclick="doAjaxGetLab()">
	<div id='spanGetLab'></div>
	สร้าง ผล Lab 
	</button>
    <button onclick="AddMarkerLoss(0)" id='winAt0' class='hide'>Win At =First </button>
	<button onclick="AddMarkerLoss(2)" id='winAt2' class='hide'>Win At =2 </button>
	<button onclick="AddMarkerLoss(3)" id='winAt3' class='hide'>Win At =3 </button>
	<button onclick="AddMarkerLoss(4)" id='winAt4' class='hide'>Win At =4 </button>
	<button onclick="AddMarkerLoss(5)" id='winAt5' class='hide'>Win At =5 </button>
	<button onclick="AddMarkerLoss(6)" id='winAt6' class='hide'>Win At =6 </button>
	<button onclick="AddMarkerLoss(7)" id='winAt7' class='hide'>Win At =7 </button>
	<button onclick="AddMarkerLoss(8)" id='winAt8' class='hide'>Win At =8 </button>
	<button onclick="AddMarkerLoss(9)" id='winAt9' class='hide'>Win At =9 </button>
<!-- 
    <button onclick="analyzePattern()">Mark จุดที่ Loss >=5 </button>
 -->
	<button onclick="MarkTrend()">Mark จุดที่เป็น Sideway</button>
	<button onclick="toggleTooltip()">Mark จุดที่เป็น Pararell</button>
	
	<button onclick="MainCalADX()">Cal ADX</button>
	<button onclick="MainCalTradeAdjacent()">Cal Adjacent Trade</button>

	<button onclick="newTradeAdjacentV2()">Cal Adjacent Trade V2</button>

	<button onclick="MainCalTradeEMATrend()">Cal EMA Trend</button><hr>
	<button onclick="ClearMarker()">Clear Marker</button>
	<button onclick="AddMarkerAdjacent()">Add Marker Adjacent</button>
	<button onclick="AddMarkerAdjacentV2()">Add Marker Adjacent V2</button>

	<button type='button' id='' class='mBtn' onclick="getPortfolio()">GetPortfolio()</button>

	<button type='button' id='' class='mBtn' onclick="loadHistoricalData()">ReLoad Data</button>
	<button type='button' id='' class='mBtn' onclick="subscribeToTime()">Subscribe Time</button>

	 <button type='button' id='' class='mBtn' onclick="DrawEntrySpot()">DrawEntrySpot</button>

	<button type='button' id='' class='mBtn' onclick="unsubscribeToTime()">ยกเลิก Subscribe Time</button>

	<button type='button' id='' class='mBtn' onclick="showtradeHistory()">สรุป Trade Hist</button>


    Process เมื่อ คลิก Candle ::
	<select id="onProcessclick">
		<option value="AddPriceLine" selected>Add Price Line
		<option value="AnalyTrend" >วิเคราะห์ ค่าต่างๆ 
	</select>

	<button type='button' id='' class='mBtn' onclick="clearAllLines()">ClearAllLines</button>

	<button type='button' id='' class='mBtn' onclick="MainCutPoint()"> หา CutPoint</button>

	<button type='button' id='' class='mBtn' onclick="DrawPriceLine()"> วาด Price Line</button>
	


	<div id="" class="bordergray flex">
	   Current Asset :: <span id= 'assetCandleID' style='color:red;font-weight:bold'></span>  
	</div>
	ServerTime :: <span id='serverTime' style='color:red;font-weight:bold'>ยังไม่ Subscribe Time</span>

	<div id="btnAssetZone" class="bordergray flex">
	     
	</div>
	<div id="contractZone" class="bordergray flex">
	       Contract List<select id="contractList" onchange='FillContractData(this.value)'>
		   <option value="" selected>*** ยังไม่มี *** 
			
	     </select> 
		 Asset :<input type="text" id="assetContract999">
		 Contract Type:<input type="text" id="contractType999">
		 Entry Spot:<input type="number" id="entrySpot999">

		 <button type='button' id='' class='mBtn' onclick="DrawEntry()">Draw 
		 Entry</button>
		 <button type='button' id='' class='mBtn' onclick="SavePriceDiff(priceDiff)">SavePriceDiff</button>
		 Profit:<input type="number" id="profit999">
	</div>
	<input type="hidden" id="allSymbolsThisGroup">


	<input type="hidden" id="labResultTxt">
	<div id="analysisContainer" class="bordergray flex">
	   <input type="hidden" id="conflictList">
	   <input type="hidden" id="adxList">
	     
	</div>




    <div class="status" id="status">
        <span class="connection-status disconnected" id="connectionStatus"></span>
        <span id="statusText">Ready - Click Connect & Load to fetch real data from Deriv API</span>
		<div id="statusText2" style='color:#ffff00'> </div>
		<div id="statusText3" style='color:#ff8000'> </div>
		<div id="statusText4" style='color:#00ff00'> </div>


    </div>
    
    <div id="container">
        <div id="chart"></div>
        <!-- <div id="chartOHLC" style='border:5px solid yellow'></div> -->
        <div id="tooltip">
            <div class="tooltip-row">
                <span class="tooltip-label">เวลา:</span>
                <span class="tooltip-value time" id="tooltip-time">-</span>
            </div>
			<!-- 
            <div class="tooltip-row">
                <span class="tooltip-label">Open:</span>
                <span class="tooltip-value" id="tooltip-open">-</span>
            </div>
            <div class="tooltip-row">
                <span class="tooltip-label">High:</span>
                <span class="tooltip-value" id="tooltip-high">-</span>
            </div>
            <div class="tooltip-row">
                <span class="tooltip-label">Low:</span>
                <span class="tooltip-value red" id="tooltip-low">-</span>
            </div>
-->
            <div class="tooltip-row">
                <span class="tooltip-label">Close:</span>
                <span class="tooltip-value" id="tooltip-close">-</span>
            </div>

            <div class="tooltip-row">
                <span class="tooltip-label">EMA3:</span>
                <span class="tooltip-value blue" id="tooltip-ema3">-</span>
            </div>
            <div class="tooltip-row">
                <span class="tooltip-label">EMA5:</span>
                <span class="tooltip-value orange" id="tooltip-ema5">-</span>
            </div>

			<div class="tooltip-row">
                <span class="tooltip-label">EMA-Above:</span>
                <span class="tooltip-value orange" id="tooltip-emaAbove">-</span>
            </div>
			<div class="tooltip-row">
                <span class="tooltip-label">ADX:</span>
                <span class="tooltip-value orange" id="tooltip-adxValue">-</span>
            </div>
			<div class="tooltip-row">
                <span class="tooltip-label">ADX-UpCon:</span>
                <span class="tooltip-value orange" id="tooltip-adxUpCon">-</span>
            </div>
			<div class="tooltip-row">
                <span class="tooltip-label">ADX-DownCon:</span>
                <span class="tooltip-value orange" id="tooltip-adxDownCon">-</span>
            </div>
			<div class="tooltip-row">
                <span class="tooltip-label">ADX-TrendStrength:</span>
                <span class="tooltip-value orange" id="tooltip-adxTrendStrength">-</span>
            </div>
			<div class="tooltip-row">
                <span class="tooltip-label">ADX-nextCandlePrediction:</span>
                <span class="tooltip-value orange" id="tooltip-nextCandlePrediction">-</span>
            </div>
			<div class="tooltip-row">
                <span class="tooltip-label">Body Diff</span>
                <span class="tooltip-value orange" id="tooltip-BodyDiff">-</span>
            </div>
			<div class="tooltip-row">
                <span class="tooltip-label">EMA Diff</span>
                <span class="tooltip-value orange" id="tooltip-emdDiff">-</span>
            </div>

			nextCandlePrediction

			
        </div>
    </div>
	<div id="" class="bordergray flex">
	   Profit :: <input type="text" id="profitTxt999">
	</div>
	<div id="priceMeterPercent" class="bordergray flex"></div>
	<div id="priceMeterDiv" class="bordergray flex"></div>

	     
	
	<div id="" class="bordergray flex" style='sdisplay:none'>
      กำหนด <select id="LineSupport">
		<option value="SupportLine" selected>แนวรับ
		<option value="ResistanceLine">แนวต้าน
      </select>
	  แนวรับ :: <input type="text" id="Support" value=''> 
	  แนวต้าน :: <input type="text" id="Resistance" value = ''> 
	  จุดเข้าซื้อ :: <input type="text" id="enTrySpot"> 

      
	  Analy EMA Data <textarea id="analyEMAData"></textarea>     
	</div>
	<h2>OHLC</h2>
	<button type='button' id='' class='mBtn' onclick="requestOHLC()">แสดง ohlc</button>
	<div id='chartOHLC' style='border:1px solid yellow;width:100%;height:400px'>
	</div>
	
	<br>Analy Candle::
	<div id="analyCandleResult" class="bordergray flex">
	     
	</div>
	

	<?php
	  BoxTradePlan2();
	  
	?>
	<div id="Hint" class="bordergray flex">
	  🚦🚦 Hint 📈
	  <h2>ข้อพิจารณา ก่อนการเลือก Action </h2>
	  <ol>
	   <li>ดู Sideway ที่จุดล่าสุด และตีเส้นแนวรับและแนวต้าน  </li>
	   <li>ดูให้ออกว่า ขณะนี้ มันพักตัว หรือ มี Trend ถ้าพักตัว ห้ามเสี่ยงเด็ดขาด เพราะ กราฟยังไม่เลือกทาง</li>
	   <li>ดูว่า ขนาด Body เล็กไปไหม </li>
	   <li>ดูเส้น ema Short </li>
	   <li>ดูเส้น ema เหลืองว่า trend ยาวจะไปทางไหน และขณะนี้  ราคาปิด วิ่งออกห่างหรือ วิ่งเข้าหาเส้น ที่ timeframe ต่างๆ  </li>
	   <li>ดูเส้น ema เหลืองว่า  วิ่งแนวราบไหม ถ้าแนวราบ แสดงว่า กราฟยังไม่เลือกแนว ความเสียงสูง  </li>
	   <li>ดู ว่าแท่งเทียน วิ่งเข้าหา เส้น ema เหลือง หรือไม่ </li>
       <li>ไม่ควรเข้า เมื่อ Candles วิ่งตัดเส้นเหลืองไปๆมา ในช่วงสั้นๆ  </li>
       <li>ไม่ควรเข้า เมื่อ Candles วิ่ง บน Trend ยาวๆ ควร รอให้มันกลับตัว ก่อนและมีแท่งยืนยัน</li>
	   <li>ควรตีเส้นแนวรับ - แนวต้าน</li>

	   <li>ห้ามใจร้อน ในการเลือกกราฟ การเลือก กราฟที่ ถูกต้อง จะทำให้ทำกำไร ได้ง่าย กว่าการเสี่ยงเล่น </li>







	  </ol>
	     
	</div>
	<div id="alterContainer" class="bordergray flex">
	     
	</div>
    <h2>Raw Candle</h2><button type='button' id='' class='mBtn' onclick="CopyRawCandle()">Copy RawCandle</button>
	 <textarea id="rawCandle" style='height:100px;width:100%;padding:10px'></textarea>
    <h2>Body Analysis</h2>
	 <textarea id="bodyAnalysis" style='height:100px;width:100%;padding:10px'></textarea>
	<h2>AllTrade List</h2>
	 <textarea id="txtAllTradelist" style='height:100px;width:100%;padding:10px'></textarea>
	 <textarea id="profitList" style='height:100px;width:100%;padding:10px'></textarea>


    <?php
      CreateResultTxt();
	  
    ?>
	<h2>Trade Profit List</h2>
	 <textarea id="txtTradeProfitList" style='height:100px;width:100%;padding:10px'></textarea>

	<div id="tradeTable" class="bordergray flex">
	     
	</div>
	<h2>วิเคราะห์ Profit List</h2>
	 <textarea id="analTradeProfitList" style='height:100px;width:100%;padding:10px'></textarea>

    <script>
        // Global variables
        let chart, candleSeries, ema3Series, ema5Series, emaLongSeries,emaSuperLongSeries;
		let chartOHLC ,ohlcSeries
		let ohlcArray = [] ;
        let ws = null;
        let tooltipEnabled = false;
        let realTimeEnabled = false;
        let selectedCandle = null;
        let candleData = [];
		let ohlcData = [] ;
		let maxProfit = 0 ;
		let minProfit = 0 ;
		let currentProfit = 0 ;
        let entry_spot = 0 ;
		let portfolioRequestEver = false;

		
        let ema3Data = [], ema5Data = []; 
		let emaSuperLongData = [] ;
		let ema0Values = [] ;
        let ema3Values = [] ;
		ema5Values = []; emaLongValues = [];

		let markersLoss = [] ;
		let labResult = null ;
		let conflictData =  null; 
		let mixedData = null ;
		let lastColor = '-';

		const supportResistanceLines = [];
        const supportResistanceLinesOHLC = [];
        let tempLine = null;
		let lossCon  = 0 ;
		let martingalePlan = [1,2,6,18,54,162,384,800,1700] ;
		let currentMoneyTrade = 1 ;
		let tradeHistory = [];
		let winStatus = [] ;
		let AllTradeList = [] ;
		
		let priceDiff  = [] ;
		let candleArray = [] ;
		let lastClosePrice = 0 ;
		priceMeter = [] ;
		var CurrentTradeObject = null;

		var profitList = [] ;
		var profitObj = {
           "asset" : "",
           "MoneyTrade" : 0,
           "Granu" : 0,
           "ContractType" : "",
		   "StartTimeStamp" : 0,
		   "StartTimeDisplay" : "",
           "Entry_Spot" : 0.0 ,
           "ProfitTimeList" : []
		}
		   
     /*
			 ProfitTimeObj = {
                timeProfit : 0,
				Current_Spot: 0.0,
				Differ_Spot: 0.0,
				Profit: 0.0 

             }

			 */
        



		
// Deriv Asset Groups with Trading Status (Hardcoded)
const DERIV_ASSETS = {
    forex: [
        { symbol: 'frxEURUSD', name: 'EUR/USD', open: true },
        { symbol: 'frxGBPUSD', name: 'GBP/USD', open: true },
        { symbol: 'frxUSDJPY', name: 'USD/JPY', open: true },
        { symbol: 'frxAUDUSD', name: 'AUD/USD', open: true },
        { symbol: 'frxUSDCHF', name: 'USD/CHF', open: true },
        { symbol: 'frxEURGBP', name: 'EUR/GBP', open: true },
        { symbol: 'frxEURJPY', name: 'EUR/JPY', open: true },
        { symbol: 'frxGBPJPY', name: 'GBP/JPY', open: true },
        { symbol: 'frxAUDJPY', name: 'AUD/JPY', open: true },
        { symbol: 'frxNZDUSD', name: 'NZD/USD', open: true }
    ],
    volatility: [
        { symbol: 'R_10', name: 'Volatility 10 Index', open: true },
        { symbol: 'R_25', name: 'Volatility 25 Index', open: true },
        { symbol: 'R_50', name: 'Volatility 50 Index', open: true },
        { symbol: 'R_75', name: 'Volatility 75 Index', open: true },
        { symbol: 'R_100', name: 'Volatility 100 Index', open: true },
        { symbol: '1HZ10V', name: 'Volatility 10 (1s)', open: true },
        { symbol: '1HZ25V', name: 'Volatility 25 (1s)', open: true },
        { symbol: '1HZ50V', name: 'Volatility 50 (1s)', open: true },
        { symbol: '1HZ100V', name: 'Volatility 100 (1s)', open: true }
    ],
    crypto: [
        { symbol: 'cryBTCUSD', name: 'Bitcoin', open: true },
        { symbol: 'cryETHUSD', name: 'Ethereum', open: true },
        { symbol: 'cryLTCUSD', name: 'Litecoin', open: true },
        { symbol: 'cryXRPUSD', name: 'Ripple', open: true },
        { symbol: 'cryBCHUSD', name: 'Bitcoin Cash', open: true },
        { symbol: 'cryEOSUSD', name: 'EOS', open: true },
        { symbol: 'cryBNBUSD', name: 'Binance Coin', open: false }
    ],
    commodities: [
        { symbol: 'frxXAUUSD', name: 'Gold/USD', open: true },
        { symbol: 'frxXAGUSD', name: 'Silver/USD', open: true },
        { symbol: 'frxBROUSD', name: 'Oil/USD', open: true },
        { symbol: 'frxXPTUSD', name: 'Platinum/USD', open: true },
        { symbol: 'frxXPDUSD', name: 'Palladium/USD', open: false }
    ],
    indices: [
        { symbol: 'OTC_DJI', name: 'Wall Street 30', open: true },
        { symbol: 'OTC_SPC', name: 'US 500', open: true },
        { symbol: 'OTC_NDX', name: 'US Tech 100', open: true },
        { symbol: 'OTC_FTSE', name: 'UK 100', open: true },
        { symbol: 'OTC_DAX', name: 'Germany 40', open: true },
        { symbol: 'OTC_AS51', name: 'Australia 200', open: true },
        { symbol: 'OTC_N225', name: 'Japan 225', open: false }
    ]
};

		
        // Deriv WebSocket API endpoint
        const DERIV_WS_URL = 'wss://ws.binaryws.com/websockets/v3?app_id=66726';

        // Initialize chart
        function initChart() {
            const container = document.getElementById('chart');
            
            chart = LightweightCharts.createChart(container, {
                width: container.clientWidth,
                height: container.clientHeight,
                layout: {
                    backgroundColor: '#1e1e1e',
                    textColor: '#d1d4dc',
                },
                grid: {
                    vertLines: { color: '#2B2B43' },
                    horzLines: { color: '#2B2B43' },
                },
                crosshair: {
                    mode: LightweightCharts.CrosshairMode.Normal,
                },
                priceScale: {
                    borderColor: '#485c7b',
                },
                timeScale: {
                    timeZone: 'UTC+7', // หรือ 'UTC+7' 
                    borderColor: '#485c7b',
                    timeVisible: true,
                    secondsVisible: false,
                },
            });

            candleSeries = chart.addCandlestickSeries({
                upColor: '#00ff88',
                downColor: '#ff4444',
                borderDownColor: '#ff4444',
                borderUpColor: '#00ff88',
                wickDownColor: '#ff4444',
                wickUpColor: '#00ff88',
            });

            ema3Series = chart.addLineSeries({
                color: '#ffffff',
                lineWidth: 2,
                title: 'EMA3',
            });
            ema5Series = chart.addLineSeries({
                color: '#ff8000',
                lineWidth: 2,
                title: 'EMA3',
            });



            emaLongSeries = chart.addLineSeries({
				color: '#ffff00',                
                lineWidth: 2,
                title: 'EMA5',
            });

			emaSuperLongSeries= chart.addLineSeries({
                color: '#ff0000',
                lineWidth: 5,
                title: 'EMASuperLong',
            });

            // Tooltip on hover
            chart.subscribeCrosshairMove(param => {

                if (!tooltipEnabled) return;

				if (!param.time || !param.point) return;  
                const price = candleSeries.coordinateToPrice(param.point.y);
			    if (price) {
					// แสดงราคาบน console หรือ tooltip
					console.log(`ราคาปัจจุบัน: ${price.toFixed(5)}`);
				}
                
                const tooltip = document.getElementById('tooltip');
                
                if (param.point === undefined || !param.time || param.point.x < 0 || param.point.x > container.clientWidth || param.point.y < 0 || param.point.y > container.clientHeight) {
                    tooltip.style.display = 'none';
                } else {
                    tooltip.style.display = 'block';
                    
                    const candleData = param.seriesPrices.get(candleSeries);
					//console.log('sssssss',candleData) ;
					
                    const ema3Data = param.seriesPrices.get(ema3Series);
                    const ema5Data = param.seriesPrices.get(ema5Series); 
                    
                    
                    updateTooltipContent(param.time, candleData, ema3Data, ema5Data);
                    positionTooltip(param.point, tooltip, container);
                }
            });

            // Click to select candle
            chart.subscribeClick(param => {
                if ( param.point === undefined || !param.time) return;
                
				console.clear()
				if (!param.time || !param.point) return;

				  processClick = document.getElementById("onProcessclick").value ; 
				  if (processClick === 'AddPriceLine') {				  
					  // หาราคาที่คลิก
					  const price = candleSeries.coordinateToPrice(param.point.y);
					  alert(price);
					  
					  if (!price) return;
					  
					  // สร้างเส้น Support/Resistance
					  const line = candleSeries.createPriceLine({
						price: price,
						color: '#2196F3',
						lineWidth: 2,
						lineStyle: 2, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });

					if (document.getElementById("LineSupport").value === 'SupportLine') {
			           document.getElementById("Support").value = price;
		            }
		            if (document.getElementById("LineSupport").value === 'ResistanceLine') {
                      document.getElementById("Resistance").value = price;              
		            }

					if (document.getElementById("Support").value !== '' && document.getElementById("Resistance").value !=='') {
						document.getElementById("msgResistance").innerHTML = ' มีทั้ง แนวรับและแนวต้านแล้ว';
					}
		   
					  
					supportResistanceLines.push(line);					  
					console.log(`เส้น S/R ที่ราคา: ${price.toFixed(2)}`);
				  }
				  if (processClick === 'AnalyTrend') {				  
					  console.log('param.time ',param.time );
					  AnalyTrend(param.time );
					  // Add marker to the selected candle
                      candleSeries.setMarkers([{
                       time: param.time,
                       position: 'aboveBar',
                       color: '#ffff00',
                       shape: 'arrowDown',
                       text: '●'
                      }]);
					  
				  }
				  
				//console.log(param.point,'-',param.time)
				
                const candleData = param.seriesPrices.get(candleSeries);
				//console.log(candleData)
				
                
                
                let thisTime = parseInt(param.time);				 
                if (document.getElementById("TimeSelected").value =='s') {
					document.getElementById("startTimeSelected").value = param.time
                } 


                if (document.getElementById("TimeSelected").value =='e') {
					document.getElementById("stopTimeSelected").value = param.time
                } 
				startTime = parseInt(document.getElementById("startTimeSelected").value) ; 
				stopTime = parseInt(document.getElementById("stopTimeSelected").value) ; 

				
				
				candleData2 = JSON.parse(document.getElementById("conflictDataText").value);
				//const result = candleData2.filter(object => object.time === startTime);
				//console.log('Result',result)
				
                for (let i=0;i<=candleData2.length-1 ;i++ ) {
                   if (candleData2[i].time === startTime) {
					   document.getElementById("startTimeIndex").value = i;
					   break;
                   } 
                } 
				for (let i=0;i<=candleData2.length-1 ;i++ ) {
                   if (candleData2[i].time === stopTime) {
					   document.getElementById("stopTimeIndex").value = i;
					   break;
                   } 
                } 
				
				adjacent = JSON.parse(document.getElementById("adjacentText").value);
				winStatus = '';
				for (let i=0;i<=adjacent.length-1 ;i++ ) {
					if (adjacent[i].time === startTime) {
						winStatus = adjacent[i].WinStatus;
						//alert('Found-' + winStatus); break ;
					}				
				}

				conflict = JSON.parse(document.getElementById("conflictDataText").value);
				conflictStatus = '';
				for (let i=0;i<=conflict.length-1 ;i++ ) {
					if (conflict[i].time === startTime) {
						conflictStatus = conflict[i].conflictType;
						//alert('Found-' + conflictStatus); break ;
					}				
				}
				tblShowTrade = document.getElementById("tblShowTrade");
				if (tblShowTrade) {
					//console.log('Length=',tblShowTrade.rows.length)
					
					for (let i=0;i<=tblShowTrade.rows.length-1 ;i++ ) {
                       if (parseInt(tblShowTrade.rows[i].cells[1].innerHTML) === startTime ) {						  
						   const tableB = document.getElementById('tableB');
						   const row50 = tblShowTrade.rows[i].cloneNode(true);
						   if (tableB.rows.length > 0) {
                             //tableB.deleteRow(0);
                           }
                           // เพิ่ม row ที่ copy มาไว้ที่ตำแหน่งแรก
                           tableB.insertBefore(row50, tableB.rows[1]);
						   break;						   
                       }					
					}
				}

				resultTradeV2 = JSON.parse(document.getElementById("adjacentTextV2").value);
				if (resultTradeV2) {					
					for (let i=0;i<=resultTradeV2.length-1 ;i++ ) {
                       if (parseInt(resultTradeV2[i].time) === startTime ) {			WinStatus =  resultTradeV2[i].WinStatus;
					       alert('Adjacent V2 ',WinStatus);
						   //const row50 = tblShowTrade.rows[i].cloneNode(true);
						   break;						   
                       }					
					}
				}
				
				
				
                 
				//alert(param.time)
				//allMarkers = [];
		        //candleSeries.setMarkers(allMarkers);
				let isBullish  = false;
				let markerStart = {
					   time: startTime ,
					   position: isBullish ? 'belowBar' : 'aboveBar',
					   color:   '#ffff00',
					   shape: 'circle',
					   text: 'S-' + winStatus + '-'+conflictStatus,
					   size: 1
				};
                let markerStop = {
					   time: stopTime ,
					   position: isBullish ? 'belowBar' : 'aboveBar',
					   color:   '#ffff00',
					   shape: 'circle',
					   text: 'E-',
					   size: 1
				};
					 
				allMarkers.push(markerStart);
                allMarkers.push(markerStop);
				 
				candleSeries.setMarkers(allMarkers);




                if (candleData) {
                    selectCandle(param.time, candleData);
                }
            });
        }  

		function InitChartOHLC() {


		    const container = document.getElementById('chartOHLC');
            
            chartOHLC = LightweightCharts.createChart(container, {
                width: container.clientWidth,
                height: container.clientHeight,
                layout: {
                    backgroundColor: '#1e1e1e',
                    textColor: '#d1d4dc',
                },
                grid: {
                    vertLines: { color: '#2B2B43' },
                    horzLines: { color: '#2B2B43' },
                },
                crosshair: {
                    mode: LightweightCharts.CrosshairMode.Normal,
                },
                priceScale: {
                    borderColor: '#485c7b',
                },
                timeScale: {
                    timeZone: 'UTC+7', // หรือ 'UTC+7' 
                    borderColor: '#485c7b',
                    timeVisible: true,
                    secondsVisible: false,
                },
            });

            ohlcSeries = chartOHLC.addCandlestickSeries({
                upColor: '#00ff88',
                downColor: '#ff4444',
                borderDownColor: '#ff4444',
                borderUpColor: '#00ff88',
                wickDownColor: '#ff4444',
                wickUpColor: '#00ff88',
            });


		
		} // end func

		function processRealTimeTick(candleOHLC) {
		//processRealTimeTick(data.ohlc)
		    
		    
		    candleDataohlc = {
                "time"  : parseFloat(candleOHLC.epoch),
				"open"  : parseFloat(candleOHLC.open),
				"close" : parseFloat(candleOHLC.close),
				"high"  : parseFloat(candleOHLC.high),
				"low"   : parseFloat(candleOHLC.low)
			}	

            
			//console.log('New Ohlc',candleDataohlc)
			ohlcSeries.setData(ohlcArray);
			console.log('ohlcArray',ohlcArray);
			

			
			


		
		} // end func
		

		function requestOHLC() {
			
			    InitChartOHLC();
                asset = document.getElementById("assetSelect").value ;  
				
				countBar = 50 ;
				 
				 granularity = document.getElementById("granularitySelect").value ;
				 requestOHLC = {
						ticks_history: asset,                    
						count: countBar,           
                        end: 'latest', 
						style: 'candles',
						granularity: granularity,
						req_id: 1,
						subscribe : 1
				 };
				 ws.send(JSON.stringify(requestOHLC));

		
		} // end func
		
		

		function AnalyTrend(candleTime) {
			      
                 sData= document.getElementById("analyEMAData").value ;
			     sObj = JSON.parse(sData) ;
				 console.log('sObj',sObj)
				 
				 candleTimeDisplay = formatTime2(candleTime) ;
				 console.log('Analy Trend',candleTime, candleTimeDisplay);
				 document.getElementById("analyCandleResult").innerHTML = candleTimeDisplay;
				 //s = sObj.candleData //.find(u => u.time === candleTime);
				 //console.log('s',s)
				 const index = sObj.candleData.findIndex(item => item.time === candleTime);
				 console.log('Index',index) 
                 const indexShortEMA = sObj.emaShortData.findIndex(item => item.time === candleTime);
				 console.log('Index Short ema',indexShortEMA)  ;
                 slopeShort = sObj.emaShortData[indexShortEMA].slope ;
				 directionShort = sObj.emaShortData[indexShortEMA].direction ;

				 st = candleTimeDisplay + '-' + slopeShort + '-' + directionShort;

				 document.getElementById("analyCandleResult").innerHTML = st;

                 
			     
					 
                 //console.log(s[index].time,' vs ', candleTime) ;
                  
				 
				 //console.log('s',s);

				 
				 

			//analyCandleResult
		
		
		} // end func

		function DrawEntrySpot() {
			     
				 //alert(entry_spot.entry_spot)
				 price = entry_spot.entry_spot ;
				 if (entry_spot.contract_type === 'CALL') {
                    lineColor = '#2196F3' ;
				 } else {
                    lineColor = '#ff0000' ;
				 }
				 
			     const line = candleSeries.createPriceLine({
						price: price,
						color: lineColor,
						lineWidth: 3,
						lineStyle: 0, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });
					  
				 supportResistanceLines.push(line);					  

				 const line2 = ohlcSeries.createPriceLine({
						price: price,
						color: lineColor,
						lineWidth: 3,
						lineStyle: 0, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });
					  
				 supportResistanceLinesOHLC.push(line2);					  
					  //console.log(`เส้น S/R ที่ราคา: ${price.toFixed(2)}`);
		
		
		} // end func
		
		



		// ฟังก์ชันลบเส้นทั้งหมด
		function clearAllLines() {
		  supportResistanceLines.forEach(line => {
			candleSeries.removePriceLine(line);
		  });
		  supportResistanceLines.length = 0;
		}

		function sendAuth() {
		
		    // 1) authorize
            ws.send(JSON.stringify({ authorize: "YOUR_API_TOKEN_HERE" }));
		} // end func
		

		function PlaceTrade(action) {

			 amount = 30 ;
			 contractType = action ;	
			 tradeGranuSelected0 = document.getElementById("tradeGranu").value ;
			 tradeGranuSelected = tradeGranuSelected0.split('-');

             //console.log('tradeGranuSelected',tradeGranuSelected) ;
			
                
			 duration = tradeGranuSelected[0] ;
			 durationUnit = tradeGranuSelected[1] ;

			 //symbol = document.getElementById("assetSelect").value ;
             symbol = document.getElementById("SymBols").value ;
			 amount = document.getElementById("moneyTrade").value  ;
			 

			 const proposalReq = {
             buy: 1,
             price: parseFloat(amount),
             parameters: {
               amount: parseFloat(amount),
               basis: "stake",
               contract_type: contractType,
               currency: "USD",
               duration: parseInt(duration),
               duration_unit: durationUnit,
               symbol: symbol
             }
            };
            ws.send(JSON.stringify(proposalReq));
         
		
		} // end func

		function PlaceTradePlan2(action) {

             if (document.getElementById("assetPlan2").value === '') {
				 alert('ยังไม่เลือก Asset') ; return ;
             }
			 amount = 1 ;
			 contractType = action ;	
			 tradeGranuSelected0 = document.getElementById("tradeGranu").value ;
			 tradeGranuSelected = tradeGranuSelected0.split('-');

             //console.log('tradeGranuSelected',tradeGranuSelected) ;
			
                
			 duration = 1  ;
			 durationUnit = 'm' ;
			 duration = 55  ;
			 durationUnit = 's' ;


			 //symbol = document.getElementById("assetSelect").value ;
             symbol = document.getElementById("SymBols").value ;
			 if (currentProfit < 0) {
				 lossCon++ ;
			 }
			 currentMoneyTrade  = martingalePlan[lossCon] ;
             document.getElementById("MoneyTradePlan2").value = currentMoneyTrade ;
			 amount = document.getElementById("MoneyTradePlan2").value  ;
			 

			 const proposalReq = {
             buy: 1,
             price: parseFloat(amount),
             parameters: {
               amount: parseFloat(amount),
               basis: "stake",
               contract_type: contractType,
               currency: "USD",
               duration: parseInt(duration),
               duration_unit: durationUnit,			   
               symbol: symbol
             }
            };
            ws.send(JSON.stringify(proposalReq));

			//console.log('Proposal=',JSON.stringify(proposalReq))
			
         
		
		} // end func
		


		function AddMarkerLoss(lossNo) {

         for (let i=0;i<=9 ;i++ ) {
            thisName = '#winAt'+ i;
		    $(thisName).removeClass('selected') ;  
         } 
         thisName = '#winAt'+ lossNo ;
		 $(thisName).addClass('selected') ;  
         //console.log('resultTxt=',document.getElementById("labResultTxt").value);
         resultTrade = JSON.parse(document.getElementById("labResultTxt").value);
         //alert(resultTrade[0]); 
		 if (lossNo == 0) { maxLossList = resultTrade.maxLoss0 ; }
		 if (lossNo == 2) { maxLossList = resultTrade.maxLoss2 ; }
		 if (lossNo == 3) { maxLossList = resultTrade.maxLoss3 ; }
		 if (lossNo == 4) { maxLossList = resultTrade.maxLoss4 ; }
		 if (lossNo == 5) { maxLossList = resultTrade.maxLoss5 ; }
		 if (lossNo == 6) { maxLossList = resultTrade.maxLoss6 ; }
		 if (lossNo == 7) { maxLossList = resultTrade.maxLoss7 ; }
		 
		 isBullish  = true  ;
		 ema1Higher = true ;
         allMarkers = [];
		 candleSeries.setMarkers(allMarkers);
         for (let i=0;i<=maxLossList.length-1 ;i++ ) {         
 		     let marker = {
               time: (parseInt(maxLossList[i].timeCandle)) ,
               position: isBullish ? 'belowBar' : 'aboveBar',
               color: ema1Higher ? '#ffff00' : '#26a69a',
               shape: 'circle',
               text: 'W-'+lossNo,
               size: 1
             };
             console.log(marker.time,' = ',formatTime(marker.time))
             allMarkers.push(marker);
		 }
		 if (allMarkers.length > 0) {
                candleSeries.setMarkers(allMarkers);
         }
		 totalCandles = document.getElementById("countInput").value;
		 perCent = (allMarkers.length/totalCandles )*100 ;
		 document.getElementById("status").innerHTML =
			 'จำนวน Occur = '+ allMarkers.length + ' จาก ' + totalCandles +' = ' + perCent.toFixed(2) + ' % ';
		} // end func

		
		

        // Connect to Deriv WebSocket
        function connectWebSocket() {
            return new Promise((resolve, reject) => {
                if (ws && ws.readyState === WebSocket.OPEN) {
                    resolve(ws);
                    return;
                }

                updateStatus('connecting', 'Connecting to Deriv API...');                
                ws = new WebSocket(DERIV_WS_URL);                
                ws.onopen = () => {
					
                    updateStatus('connected', 'Connected to Deriv API');
					ws.send(JSON.stringify({ authorize: "lt5UMO6bNvmZQaR" }));
                    resolve(ws);
                };
                
                ws.onerror = (error) => {
                    updateStatus('disconnected', 'Connection error: ' + error.message);
                    reject(error);
                };
                
                ws.onclose = () => {
                    updateStatus('disconnected', 'Disconnected from Deriv API');
                };
                
                ws.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    handleWebSocketMessage(data);
                };
            });
        }

        // Handle WebSocket messages
        function handleWebSocketMessage(data) {

            if (data.authorize) { 
				console.log('Auth',data)				
                document.getElementById("authMessage").innerHTML = 'AUTH Success';
			} 
			//console.log(data.msg_type)
			

			if (data.time) {
              updateServerTime(data.time);
			  //console.log(data.time);
			  
            }
			 // เมื่อได้รับ proposal (เช็คโครงสร้างจริงจาก response)
            if (data.proposal) {
              // ตัวอย่าง: proposal.proposal.id หรือ data.proposal.id (ขึ้นกับ response)
              const proposalId = data.proposal.proposal?.id || data.proposal.id;
              const askPrice = data.proposal.proposal?.ask_price || data.proposal.ask_price;
             // ตัวอย่างตัดสินใจ: ซื้อทันทีที่ได้ proposal
			 CurrentTradeObject = null;
             if (proposalId) {
                ws.send(JSON.stringify({ buy: proposalId }));
            }
           }
		   // จัดการผลลัพธ์การซื้อ (มี contract_id)
           if (data.buy) {
               console.log("Bought contract:", data);
			   thisContractID = data.buy.contract_id ;
			   addElement2ListBox('contractList', thisContractID, thisContractID) ;
			   CurrentTradeObject = getemptyTradeObj();
			   CurrentTradeObject.contractID = thisContractID ;
			   CurrentTradeObject.contractType = data.echo_req.parameters.contract_type;
			   CurrentTradeObject.asset = data.echo_req.parameters.symbol;
			   CurrentTradeObject.duration = data.echo_req.parameters.duration +'-'+ data.echo_req.parameters.duration_unit;
			   CurrentTradeObject.amount = data.echo_req.parameters.amount;
			   CurrentTradeObject.purhaseTimeStamp = data.buy.purchase_time ;
			   CurrentTradeObject.purhaseTimeDisplay=formatTime(data.buy.purchase_time+(7*3600));
			   
			   console.log('CurrentTradeObject',JSON.stringify(CurrentTradeObject));

	 
			   AllTradeList.push(CurrentTradeObject);
			   
			   document.getElementById("txtAllTradelist").value = JSON.stringify(AllTradeList);
			   //document.getElementById("ContractList").innerHTML = data.buy.contract_id;
			   thisContractID = data.buy.contract_id ;
	           
			   ws.send(JSON.stringify( {
		         proposal_open_contract: 1,
	             contract_id: thisContractID,
                 subscribe : 1
               }));
		       console.log('Send Track Order',thisContractID)
			   //createNewRow(data);
           }

		   if (data.proposal_open_contract) {
               //alert('Yes')  ;
               const tbl = document.getElementById("tblTrade");
			   currentProfit = data.proposal_open_contract.profit;
			   if (!tbl) {			   
                 // alert('Create Table'); 
			      CreateTableOpenProposal();

			   }
			   contractID = data.proposal_open_contract.contract_id
               thisID = "contractID_" + contractID ;
			   cellTbl = document.getElementById(thisID);
			   if (!cellTbl) {			   
			      if (InsertNewRowTable(data.proposal_open_contract)) {
					  sObj = {
                        "contractid" : contractID 
					  }
					  tradeHistory.push(contractID);
			      }
			   } else {                  
                  const result = tradeHistory.filter(object => object.contractid === contractID);
				  if (result) {
					//console.log('Data On UpDate',data.proposal_open_contract)
					entry_spot =  data.proposal_open_contract; 
					//console.log('Entry Spot***-',entry_spot) ;
					
                    UpdateRowTable(data.proposal_open_contract);
					
					
				  } else {
                     console.log('Not Found Result History');                    
				  }
			   }
              Diff = parseFloat(data.proposal_open_contract.current_spot)  - 
	          parseFloat(data.proposal_open_contract.entry_spot) ;
			   sObj = {
                 "buy_price" : data.proposal_open_contract.buy_price, 
                 "entry_spot" : data.proposal_open_contract.entry_spot, 
				 "current_spot" : data.proposal_open_contract.current_spot,
                 "Diff" : Diff,
				 "profit" : data.proposal_open_contract.profit  
			   }
               priceDiff.push(sObj); 
			   //CurrentTradeObject.ProfitList.push(sObj);
			   //console.log(CurrentTradeObject)
			   
/*
               Stake = data.proposal_open_contract.buy_price ;
			   ProfitByCal = (Diff * Stake * 0.17) -(Stake * 0.0507)   ;
			   console.log('Cal Profit=',data.proposal_open_contract.profit,' VS ',ProfitByCal);

	*/		   

			   

               
			  // UpdateTxtContractList(data.proposal_open_contract) ;
			   const isSold = data?.proposal_open_contract?.is_sold;
			   if (isSold === 1) {					
					thisid = 'message_' + data.proposal_open_contract.contract_id ;
					thisMsg= 'สัญญาสิ้นสุดแล้ว  กำไร/ขาดทุน: '+ data.proposal_open_contract.profit;
					document.getElementById(thisid).innerHTML = thisMsg;
					statusID = 'winStatus_' + data.proposal_open_contract.contract_id ;
					console.log(statusID,'-',data.proposal_open_contract.status)
                    
					document.getElementById("profitList").value = JSON.stringify(CurrentTradeObject );
					
		            document.getElementById(statusID).innerHTML = data.proposal_open_contract.status;
					if (data.proposal_open_contract.status !='won') {
						//lossCon++ ;
						winStatus.push(data.proposal_open_contract.status);
						document.getElementById("lossCon").value = lossCon;
					} 
					if (data.proposal_open_contract.status =='won') {
						lossCon = 0 ;
						document.getElementById("lossCon").value = lossCon;
						winStatus.push(data.proposal_open_contract.status);
						document.getElementById("startTradePlan2").checked = false;
						playSoldSound();
						setTimeout(() => {
						 console.log("ทำงานหลังจากผ่านไป 3 วินาที");
						}, 5000);
						thaiBath=  CalTotalBalance();
						speechText = 'ชนะแล้วค่ะ ยอดกำไร ' + thaiBath ;
						textToSpeech(speechText);
					} 

					

				//		playSoldSound();
				}	
				CalTotalBalance();
				
			   
			  // console.log('Open Contract List ',data);
			   //Manage_OpenProposal_Contract(data.proposal_open_contract)
			   return ;
			   
			   
               const contract = data.proposal_open_contract;                  
			   //console.log('Proposal OPen Contract',data)
			   UpdateRow(data.proposal_open_contract);
               //displayContracts(contract);
			   
			   entry_tick = contract.entry_tick ;
			   
			   //AddPriceLine(contract.entry_tick);

			} 
			// รับ trade history
            if (data.profit_table) {
                displayHistory(data)   ;
			}


		   if (data.msg_type === 'portfolio') {
               console.log('Portfolio received:', data);

               contractsDataList = data.portfolio.contracts || [];			   
			   console.log(contractsDataList) ;
			   const select = document.getElementById('contractList');
               // 1. ลบรายการเก่าทั้งหมด
                
			   // contractID,contractType
			   
			   Request_Proposal_From_Portfolio(ws,contractsDataList)
			   //sendTrackOrderRequests(contractsData);
               //displayContracts(contractsData);
			   /*
			   for (let i=0;i<=contractsData.length-1 ;i++ ) {
				   thisContractID = contractsData[i].contract_id ; 
				   document.getElementById("ContractList").innerHTML = thisContractID;
			   }
               getTrackOrder(thisContractID);
			   */
            } 

			if (data.proposal_open_contract ) {
				const isSold = data?.proposal_open_contract?.is_sold;
			  //const contract = data.proposal_open_contract; 
			    console.log('entry spot',entry_spot) ;
			   
			   if (isSold === 1) {					
					thisid = 'message_' + data.proposal_open_contract.contract_id ;
					thisMsg= 'สัญญาสิ้นสุดแล้ว  กำไร/ขาดทุน: '+ data.proposal_open_contract.profit;
					document.getElementById(thisid).innerHTML = thisMsg;
					CalTotalBalance();
					
				//		playSoldSound();
				}	
				CalTotalBalance();
			}

			


            
             
            
            if (data.msg_type === 'candles') {
				rawCandles = JSON.stringify(data.candles) ;
				//alert(data.candles.length);
                document.getElementById("rawCandle").value = rawCandles ;
				//candleArray.push(data.candles);

				previousClosePrice = lastClosePrice ;
				currentClosePrice = data.candles[data.candles.length-1].close ;
				fillUpDownMeter(currentClosePrice,previousClosePrice);

				lastClosePrice = data.candles[data.candles.length-1].close ;

				assetSelect = document.getElementById("assetSelect").value ;
				//bodyAnalysis = analyzeCandleData999(data.candles,assetSelect, 5);
				
				//console.log('Body',bodyAnalysis) 
                apiUrl = 'https://thepapers.in/phpAllPredictAPI/save-candle-analysis.php'
                //sendCandleAnalysisToServer(bodyAnalysis,apiUrl ) ;
				
                document.getElementById("bodyAnalysis").value = JSON.stringify(bodyAnalysis);				
                processCandleData(data.candles);
				
				//fillUpDownMeter(data.candles);
            } else if (data.msg_type === 'ohlc') {
                processRealTimeTick(data.ohlc);
            } else if (data.error) {
                updateStatus('disconnected', 'API Error: ' + data.error.message);
				
				
                console.error('Deriv API Error:', data.error);
				document.getElementById("ContractList").innerHTML = JSON.stringify(data.error);
            }
        }

		function subscribeToTime() {   
            ws.send(JSON.stringify({
              "time": 1
             }));
			 
             timeSubscription = setInterval(() => {
				 if (ws && ws.readyState === WebSocket.OPEN) {
					 // ส่ง request time
					 ws.send(JSON.stringify({
					   "time": 1
					 }));
                     // ส่ง request ohlc
					 asset = document.getElementById("assetSelect").value ;
                     granularity = document.getElementById("granularitySelect").value ;
					 countBar= document.getElementById("countInput").value ;
                     request = {
						ticks_history: asset,                    
						count: countBar,           
                        end: 'latest', 
						style: 'candles',
						granularity: granularity,
						req_id: 1
                        
					 };
					 ws.send(JSON.stringify(request));

				 }
				 }, 1000);
			 

        }
		function unsubscribeToTime() {   
             
			 clearInterval(timeSubscription);
		}

		function showtradeHistory() {
			const today = new Date();
            today.setHours(0, 0, 0, 0);
            const todayTimestamp = Math.floor(today.getTime() / 1000);
        
			ws.send(JSON.stringify({
				profit_table: 1,
				description: 1,
				limit: 100,
				date_from: todayTimestamp
			}));
		
		
		} // end func

		function displayHistory(data) {

                 const transactions = data.profit_table.transactions || [];
				 console.log(transactions) ;
				 
        
        // กรองเฉพาะรายการขาย (sell)
        //const sellTransactions = transactions.filter(t => t.action_type === 'sell');
		const sellTransactions = transactions ;
        
			// สร้าง HTML table
			let html = '<table border="1" style="border-collapse: collapse; width: 100%;">';
			html += '<thead><tr>';
			html += '<th>เวลาซื้อ</th>';
			html += '<th>เวลาขาย</th>';

			html += '<th>สัญญา</th>';
			html += '<th>ราคาซื้อ</th>';
			html += '<th>ราคาขาย</th>';
			html += '<th>กำไร/ขาดทุน</th>';
			html += '</tr></thead><tbody>';
			
			let totalProfit = 0;
			
			sellTransactions.forEach(trade => {
				const pdate = new Date(trade.purchase_time * 1000);
				const sdate = new Date(trade.sell_time * 1000);
				const profit = parseFloat(trade.sell_price) - parseFloat(trade.buy_price);
				totalProfit += profit;
				assetContract = trade.underlying_symbol + ' - ' + trade.contract_type ;
				totalProfitBath = (totalProfit.toFixed(2))*32 ;
				
				html += '<tr>';
				html += `<td>${pdate.toLocaleString('th-TH')}</td>`;
				html += `<td>${sdate.toLocaleString('th-TH')}</td>`;
				html += `<td>${assetContract}</td>`;
				html += `<td>${parseFloat(trade.buy_price).toFixed(2)}</td>`;
				html += `<td>${parseFloat(trade.sell_price).toFixed(2)}</td>`;
				html += `<td style="color: ${profit >= 0 ? 'green' : 'red'}">${profit.toFixed(2)} -$</td>`;
				html += '</tr>';
			});
			
			// แถวสรุป
			html += '<tr style="font-weight: bold; background: #f0f0f0;">';
			html += '<td colspan="4">รวม</td>';
			html += `<td style="color: ${totalProfit >= 0 ? 'green' : 'red'}">${totalProfit.toFixed(2)} $</td>`;
			html += `<td style="color: ${totalProfit >= 0 ? 'green' : 'red'}">${totalProfitBath.toFixed(2)} Bath</td>`;
			html += '</tr>';
			
			html += '</tbody></table>';
			
			// แสดงผล
			document.getElementById('tradeTable').innerHTML = html;
		}
		


        // Load historical data
        async function loadHistoricalData() {
            try {
                const ws = await connectWebSocket();
                
                const asset = document.getElementById('assetSelect').value;
				
                const granularity = parseInt(document.getElementById('granularitySelect').value);
                const count = parseInt(document.getElementById('countInput').value);

                date1 = document.getElementById('startDate').value ;
				date2 = document.getElementById('stopDate').value ;
                const startDate = new Date(date1);
				const stopDate = new Date(date2);
				//console.log('startDate==>',startDate)
				//console.log('stopDate==>',stopDate)
				document.getElementById("assetCandleID").innerHTML = asset;
				
				
				 
				
                const startTimestamp = Math.floor(startDate.getTime() / 1000);
				const stopTimestamp = Math.floor(stopDate.getTime() / 1000);
				
                
                updateStatus('connecting', `Loading ${asset} data...`);

				
				const startUTC = Math.floor(startDate.getTime() / 1000);  // แปลงเป็น Unix Timestamp (UTC)
				const endUTC = Math.floor(stopDate.getTime() / 1000);
/*
				console.log('startTimestamp',startTimestamp)
				console.log('startTime UTC',startUTC)
*/				
				
				// ตรวจสอบว่าเป็นเวลาอะไรใน UTC	
				//alert(document.getElementById("useLatest").checked)
				if (asset == '') {
					$("#volatilityBtn").trigger("click");
					alert('Select Asset') ;
					$("#volatilityBtn").trigger("click");
					return ;
				}
				if (document.getElementById("useLatest").checked === false) {			
					
					request = {
						ticks_history: asset,                    
						start: startUTC,
						end: endUTC,                    
						style: 'candles',
						granularity: granularity,
						req_id: 1
					};                    
                } else {
					countBar = parseInt(document.getElementById("countInput").value) ;
					request = {
						ticks_history: asset,                    
						count: countBar,           
                        end: 'latest', 
						style: 'candles',
						granularity: granularity,
						req_id: 1
					};
				}
				//console.log(request)
               
				
/*				
const request = {
 adjust_start_time :  1,
 end :  1754968080 ,
 granularity :  60,
start :  1754964000,
style :  "candles",
ticks_history :  "R_50"
};
*/

                console.log('Send Requests=',JSON.stringify(request))
                


                ws.send(JSON.stringify(request));
                
            } catch (error) {
                updateStatus('disconnected', 'Failed to load data: ' + error.message);
                console.error('Error loading data:', error);
            }
        }  

		function updateServerTime(timestamp) {

		   const date = new Date(timestamp * 1000);
		   const timeStr = date.toLocaleTimeString();
		   document.getElementById('serverTime').textContent = timeStr;
		   document.getElementById('serverTime2').textContent = timeStr;
		   

		   if (date.getSeconds() === 0) {
//			  fetchCandles();
              if (document.getElementById("startTradePlan2").checked) {
				  
				  if (lastColor ==='green') {
					  action = 'CALL';
				  }
				  if (lastColor ==='red') {
					  action = 'PUT';
				  }

				  PlaceTradePlan2(action);
				  
              }  
		   }
        }


		function getTrackOrder(thisContractID) {

		          ws.send(JSON.stringify( { 
					   proposal_open_contract: 1, 
					   contract_id: thisContractID, 
                       subscribe : 1
					   
            	  }));
				 //  console.log('Send Track Order',thisContractID)
				  
		
		} // end func
		

        // Process candle data from API
        function processCandleData(candles) {
            if (!candles || candles.length === 0) {
                updateStatus('connected', 'No candle data received');
                return;
            }
			//console.log('Candles',candles) ;
			document.getElementById("countInput").value = candles.length; 

            candleData = [] ;
			/*
            candleData = candles.map(candle => ({
                time: candle.epoch+ (7*3600),
                open: parseFloat(candle.open),
                high: parseFloat(candle.high),
                low: parseFloat(candle.low),
                close: parseFloat(candle.close)
            }));
			*/

			candleData = candles.map(candle => {
				const open = parseFloat(candle.open);
				const close = parseFloat(candle.close);
				const high = parseFloat(candle.high);
				const low = parseFloat(candle.low);
				
				// กำหนดสีจากการเปรียบเทียบ open/close
				const color = close > open ? 'green' : 
							  close < open ? 'red' : 'gray'; // เท่ากันถือว่า Doji

				return {
					time: candle.epoch + (7 * 3600),
					open,
					high,
					low,
					close,
					color
				};
			});


            //console.log('Candle2 ',candleData) ;
            CandleTimeFirst = candleData[0].time ;
			//console.log('First Time Stamp',CandleTimeFirst) ;
			//console.log('First Time',formatTime(CandleTimeFirst))
			
			
			
			lastIndex = candleData.length -1 
			CandleTimeLast = candleData[lastIndex].time ;

            
            document.getElementById("startTimeSelected").value = CandleTimeFirst ;
			document.getElementById("stopTimeSelected").value = CandleTimeLast ;
			document.getElementById("stopTimeIndex").value = lastIndex ;

			
            //console.log('Last Time',formatTime(CandleTimeLast))
            
            

            // Calculate EMAs
            calculateEMAs();

            // Update chart
            candleSeries.setData(candleData);
            ema3Series.setData(ema3Data);
            ema5Series.setData(ema5Data); 

			emaLongSeries.setData(emaLongData); 
			emaSuperLongSeries.setData(emaSuperLongData); 

            if (document.getElementById("isShowSuperLong").checked ) {            
              emaLongSeries.setData(emaSuperLongData);
			}
			//emaSuperLongSeries.setData(emaSuperLongSeries);

			//detectConflicts(candleData, ema3Data, ema5Data);
			MainCutPoint();

            const asset = document.getElementById('assetSelect').value;
            updateStatus('connected', `Loaded ${candleData.length} ${asset} candles`);
			//AjaxSaveToRawData(candleData);
			mixedData = MixedDataAll(candleData,ema3Data,ema5Data) ;
			//console.log('Mixed Data---',mixedData) ;
            IndexStart = mixedData.length-5 ;
			IndexStop = mixedData.length-1 ;
			//console.log(IndexStart,'->',IndexStop);
			
            n = 1 ;
			for (let i=IndexStart;i<=IndexStop ;i++ ) {
				
				if (mixedData[i].color==='green') {
					thisColor = '🟢';
				}
				if (mixedData[i].color ==='red') {
					thisColor = '🔴';
				}
				if (mixedData[i].color === 'gray') {
					thisColor = '🔘';
				}
				
				thisID = 'color'+ (n++);
				//console.log(thisID,'=',thisColor);
				//thisColor = mixedData[i].color ;				
				document.getElementById(thisID).innerHTML = thisColor;				

			}

			lastIndex2 = IndexStop ;
			lastColor =  mixedData[lastIndex].color ;

			document.getElementById('lastColor').innerHTML = lastColor  ;	
			
			
			
		
        } 

		function setPriceLine(price) {
         // remove old
		  if (currentPriceLine) {
			try { candleSeries.removePriceLine(currentPriceLine); } catch (e) { /* ignore */ }
			currentPriceLine = null;
		  }

		  // สร้าง price line ใหม่
		  currentPriceLine = candleSeries.createPriceLine({
			price: price,
			color: '#ff0000',
			lineWidth: 1,
			lineStyle: LightweightCharts.LineStyle.Solid,
			axisLabelVisible: true,
			title: `P ${price.toFixed(2)}`,
		  });
        }

		function MainCutPoint() {
			/*
			crossType : "Golden Cross"
            description:  "EMA สั้นตัดขึ้นข้าม EMA ยาว - สัญญาณซื้อ (Bullish)"
            emaLongDirection : "up"
            emaLongSlope: 0.00762196159122747
            emaLongValue: 5680.680364609055
            emaShortDirection: "up"
            emaShortSlope : 0.012524175347213411
            emaShortValue: 5680.8435494791665
            percentDifference: "0.0029"
            priceDifference: 0.1631848701117633
            time: 1761643647
			timestamp: "2025-10-28T09:27:27.000Z"
		    trendDirection: "Bullish"
			value: 5680.426644784875
			
			*/
			     
				 const cutPoints = findCutPoint(ema3Data, ema5Data);
                 //console.log(cutPoints);
				 allMarkers = [] ;
				 for (let i=0;i<=cutPoints.length-1 ;i++ ) {
					 if (cutPoints[i].trendDirection != 'Bullish') {
						 position= 'aboveBar';
						 shape = 'arrowDown' ;
						 text =  'ลง' ;
					 } else {
                         position = 'belowBar';
                         shape = 'arrowUp';
						 text =  'ขึ้น' ;
                     }
					 time = cutPoints[i].time;
					 
					 thisMarkers = {
                       time: time,
                       position: position,
                       color: '#ffff00',
                       shape: shape,
                       text: '●'
                     };
					 allMarkers.push(thisMarkers);
				 
				 }
				 candleSeries.setMarkers(allMarkers);
					  
		
		
		} // end func
		

        // Calculate EMA indicators
        function calculateEMAs() {
            if (candleData.length === 0) return;
            ema3Data = [] ;
            ema5Data = [];
            emaLongData = [];
			emaSuperLongData = [];

            ema3Values = [];
            ema5Values = [];
            emaLongValues = [];
			emaSuperLongValues = [];

			period3  = parseInt(document.getElementById("ema3").value) ;
            period5  = parseInt(document.getElementById("ema5").value) ;
			periodShort = parseInt(document.getElementById("emaLong").value) ;
			periodLong = parseInt(document.getElementById("emaLong").value) ;
 
            periodSuperLong = parseInt(document.getElementById("emaSuperLong").value) ;            
			ema3Data = calculateEMA2(candleData, period3) ;
			ema5Data = calculateEMA2(candleData, period5) ;
			emaLongData = calculateEMA2(candleData, periodLong) ;
			emaSuperLongData = calculateEMA2(candleData, periodSuperLong) ;
            //console.log('emaSuperLong',emaSuperLongData)
            
			sObj = {
              "candleData" : candleData,
              "ema3Data" : ema3Data,
              "ema5Data" : ema5Data,
              "emaLongData" : emaLongData,
			  "emaSuperLongData" : emaSuperLongData
			}



			//console.log('All Candle+EMA ',sObj);
			document.getElementById("analyEMAData").value = JSON.stringify(sObj);
			lastIndex= ema5Data.length-1 ;
			diff = ema5Data[lastIndex].value - ema3Data[lastIndex].value ;
            
			if (ema5Data[lastIndex].value > ema3Data[lastIndex].value) {
				//console.log('Case Up :: ', diff)
				
				marketStatus = 'Down' ;
			}
			if (ema5Data[lastIndex].value < ema3Data[lastIndex].value) {
				//console.log('Case Up :: ',diff)
				marketStatus = 'up' ;
			}
            
			updateFavicon(marketStatus)
			//updateFaviconEmoji(marketStatus)
			  
			return sObj ;


            
            const alpha3 = 2 / (3 + 1);
            const alpha5 = 2 / (5 + 1);

            candleData.forEach((candle, index) => {
                if (index === 0) {
                    ema3Values.push(candle.close);
                    ema5Values.push(candle.close);
                } else {
                    const ema3 = alpha3 * candle.close + (1 - alpha3) * ema3Values[index - 1];
                    const ema5 = alpha5 * candle.close + (1 - alpha5) * ema5Values[index - 1];
                    
                    ema3Values.push(ema3);
                    ema5Values.push(ema5);
                }

                ema3Data.push({
                    time: candle.time,
                    value: ema3Values[index]
                });

                ema5Data.push({
                    time: candle.time,
                    value: ema5Values[index]
                });
            });
			sObj = {
              "candleData" : candleData,
              "emaShortData" : ema3Data,
              "emaLongData" : ema5Data
			}
			return sObj ;
        } 

		// Calculate EMA
        function calculateEMA2(data, period) {
            const ema = [];
            const multiplier = 2 / (period + 1);
            
            if (data.length === 0) return ema;
            
            // First EMA value is the simple average
            let sum = 0;
            for (let i = 0; i < Math.min(period, data.length); i++) {
                sum += data[i].close;
            }
            ema.push({
                time: data[Math.min(period - 1, data.length - 1)].time,
                value: sum / Math.min(period, data.length)
            });
            
            // Calculate subsequent EMA values
            for (let i = Math.min(period, data.length); i < data.length; i++) {
                const emaValue = (data[i].close * multiplier) + (ema[ema.length - 1].value * (1 - multiplier));
                ema.push({
                    time: data[i].time,
                    value: emaValue
                });
            }

            const result = calculateEmaSlope(ema);
            //console.log('EMA Period = ',period,' = ',result);
			
            
            return result;
        } 

		function calculateEmaSlope(emaData, flatThreshold = 0.0003) {
			if (!emaData || emaData.length < 2) return emaData;
			
			const result = [];
			let prevDirection = null;
			let consecutiveCount = 0;
			
			for (let i = 0; i < emaData.length; i++) {
				const current = emaData[i];
				
				// แท่งแรกไม่มี slope
				if (i === 0) {
					result.push({
						...current,
						slope: null,
						direction: null,
						turnType: null,
						consecutiveBars: 0
					});
					continue;
				}
				
				const prev = emaData[i - 1];
				
				// คำนวณ slope (ความชัน)
				const timeDiff = current.time - prev.time;
				const valueDiff = current.value - prev.value;
				const slope = valueDiff / timeDiff;
				const slopePercent = (valueDiff / prev.value) * 100;
				
				// กำหนด direction (ใช้ threshold เพื่อตรวจสอบ parallel)
				let direction;
				if (Math.abs(slopePercent) < flatThreshold) {
					direction = 'parallel'; // แนวราบ
				} else if (valueDiff > 0) {
					direction = 'up';
				} else {
					direction = 'down';
				}
				
				// ตรวจสอบ turn type
				let turnType = null;
				if (i > 0 && prevDirection !== null && prevDirection !== 'parallel' && direction !== 'parallel') {
					if (prevDirection === 'up' && direction === 'down') {
						turnType = 'peak'; // จุดสูงสุด
					} else if (prevDirection === 'down' && direction === 'up') {
						turnType = 'trough'; // จุดต่ำสุด
					}
				}
				
				// นับจำนวนแท่งติดต่อกันในทิศทางเดียวกัน
				if (direction === prevDirection) {
					consecutiveCount++;
				} else {
					consecutiveCount = 1;
				}
				
				result.push({
					...current,
					slope: slope,
					slopePercent: slopePercent,
					direction: direction,
					turnType: turnType,
					consecutiveBars: consecutiveCount
				});
				
				prevDirection = direction;
			}
			
			return result;
		}


		 
        // Format time for Bangkok timezone
        function formatTime(timestamp, format = 'full') {

			// ระบุ Timezone ใน Date Object โดยตรง
			const date = new Date(timestamp * 1000);
			date.setMinutes(date.getMinutes() + date.getTimezoneOffset()); // ปรับให้เป็น Local Time ก่อน
			
			if (format === 'input') {
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const day = String(date.getDate()).padStart(2, '0');
				const hours = String(date.getHours()).padStart(2, '0');
				const minutes = String(date.getMinutes()).padStart(2, '0');
				return `${year}-${month}-${day}T${hours}:${minutes}`;
			}
			
			// ใช้ toLocaleString เพื่อแสดงผลแบบไทย (Asia/Bangkok)
			const options = {
				timeZone: 'Asia/Bangkok',
				year: 'numeric',
				month: '2-digit',
				day: '2-digit',
				hour: '2-digit',
				minute: '2-digit',
				second: '2-digit',
				hour12: false
			};
			
			return date.toLocaleString('th-TH', options);
		} 

		function formatTime2(timestamp, format = 'full') {

			// ระบุ Timezone ใน Date Object โดยตรง
			const date = new Date(timestamp * 1000);
			date.setMinutes(date.getMinutes() + date.getTimezoneOffset()); // ปรับให้เป็น Local Time ก่อน
			
			if (format === 'input') {
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const day = String(date.getDate()).padStart(2, '0');
				const hours = String(date.getHours()).padStart(2, '0');
				const minutes = String(date.getMinutes()).padStart(2, '0');
				return `${year}-${month}-${day}T${hours}:${minutes}`;
			}
			
			// ใช้ toLocaleString เพื่อแสดงผลแบบไทย (Asia/Bangkok)
			const options = {
				timeZone: 'Asia/Bangkok',
				
				hour: '2-digit',
				minute: '2-digit',
				
				hour12: false
			};
			
			return date.toLocaleString('th-TH', options);
		}

        // Update tooltip content
        function updateTooltipContent(time, candleData, ema3Data, ema5Data) {
            document.getElementById('tooltip-time').textContent = formatTime(time);
            
            if (candleData) {
				/*
                document.getElementById('tooltip-open').textContent = candleData.open.toFixed(5);
                document.getElementById('tooltip-high').textContent = candleData.high.toFixed(5);
                document.getElementById('tooltip-low').textContent = candleData.low.toFixed(5);
                document.getElementById('tooltip-close').textContent = candleData.close.toFixed(5);
				*/
                
                const closeElement = document.getElementById('tooltip-close');
                closeElement.className = candleData.close >= candleData.open ? 'tooltip-value' : 'tooltip-value red';
            }
            if (ema3Data > ema5Data) {
				emaAbove = '3';
            } else {
                emaAbove = '5';
			}
            document.getElementById('tooltip-ema3').textContent = ema3Data ? ema3Data.toFixed(5) : '-';
            document.getElementById('tooltip-ema5').textContent = ema5Data ? ema5Data.toFixed(5) : '-';
       
	        diff = ema3Data.toFixed(5)  - ema5Data.toFixed(5);
			//document.getElementById('tooltip-emaAbove').textContent = emaAbove+ 'Diff='+   ;
			document.getElementById('tooltip-emaAbove').textContent = diff.toFixed(4)   ;

			for (let i=0;i<=conflictData.length-1 ;i++ ) {
				if (conflictData[i].time === time) {
					adxUpContinue = conflictData[i].adxUpContinue;
					document.getElementById("tooltip-adxValue").innerHTML = conflictData[i].adxValue;
					document.getElementById("tooltip-adxUpCon").innerHTML = adxUpContinue;
					document.getElementById("tooltip-adxDownCon").innerHTML = conflictData[i].adxDownContinue;;

					document.getElementById("tooltip-adxTrendStrength").innerHTML = conflictData[i].adxTrendStrength ;

					document.getElementById("tooltip-nextCandlePrediction").innerHTML = conflictData[i].nextCandlePrediction.suggestion ;
                    
					//BodyData = JSON.parse(document.getElementById("bodyAnalysis").value) ;
					//s = BodyData.find(u => u.timeCandle === time);

					//console.log('s = ',s, 'time=',time) ;
					 
					

					

					
					
					break;
					

				}
			
			}
        }

        // Position tooltip
        function positionTooltip(point, tooltip, container) {
            const tooltipWidth = 220;
            const tooltipHeight = 160;
            
            let left = point.x + 15;
            let top = point.y - tooltipHeight / 2;
            
            if (left + tooltipWidth > container.clientWidth) {
                left = point.x - tooltipWidth - 15;
            }
            
            if (top < 10) {
                top = 10;
            } else if (top + tooltipHeight > container.clientHeight - 10) {
                top = container.clientHeight - tooltipHeight - 10;
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }

        // Select candle
        function selectCandle(time, candleData) {
            selectedCandle = { time, data: candleData };
            
            const granularity = parseInt(document.getElementById('granularitySelect').value);
            const startTime = time;
            const endTime = time + granularity;
            
            document.getElementById('selectedStart').value = formatTime(startTime, 'input');
            document.getElementById('selectedEnd').value = formatTime(endTime, 'input');
            
            const selectedInfo = document.getElementById('selectedInfo');
            const selectedCandleInfo = document.getElementById('selectedCandleInfo');
            
            selectedCandleInfo.textContent = `${formatTime(startTime)} | O:${candleData.open.toFixed(5)} H:${candleData.high.toFixed(5)} L:${candleData.low.toFixed(5)} C:${candleData.close.toFixed(5)}`;
            selectedInfo.style.display = 'block';
        }

        // Clear selection
        function clearSelection() {
            selectedCandle = null;
            document.getElementById('selectedStart').value = '';
            document.getElementById('selectedEnd').value = '';
            document.getElementById('selectedInfo').style.display = 'none';
        }

        // Adjust time
        function adjustTime(target, unit, amount) {
            const input = document.getElementById('startDate');
            const currentValue = input.value;
            
            if (!currentValue) return;
            
            const date = new Date(currentValue);
            
            if (unit === 'hour') {
                date.setHours(date.getHours() + amount);
            } else if (unit === 'minute') {
                date.setMinutes(date.getMinutes() + amount);
            }
            
            input.value = formatTime(date.getTime() / 1000, 'input');
        } 

		// Detect EMA conflicts
        function detectConflicts(candleData, ema1Data, ema2Data) {
            conflicts = []; // Clear existing conflicts
            allMarkers = []; // Clear existing markers
            newCandle = [] 
            conflictNo = 0 ;
			slopeTheresHold = 0.1 ;
			//console.log('******',candleData)

		    //console.log('Con Strength',continuityStrength(candleData, 5));
			
            
            for (let i = 1; i < candleData.length; i++) {
                const candle = candleData[i];
				
                const ema1 = ema1Data.find(e => e.time === candle.time);
                const ema2 = ema2Data.find(e => e.time === candle.time);
				
				//console.log('Diff',ema1,' -', ema1.value);
				

				//const ema1 = candleData[i].emaShort;
				//const ema2 = candleData[i].emaLong;

				//console.log(ema1)
				
				if (candle.open > candle.close) {
					thisColor = 'Red' ;
				} 
				if (candle.open < candle.close) {
					thisColor = 'Green';
				} 
				if (candle.open  === candle.close) {
					thisColor = 'Equal';
				} 
				emaDirection ='?';
				
                

				
                //console.log(JSON.stringify(sObj))
                
                if (ema1 && ema2) {
                    if (ema1.value > ema2.value) { emaAbove = '3'; }
					if (ema1.value == ema2.value) { emaAbove = 'E'; }
                    if (ema1.value < ema2.value) { emaAbove = '5'; }
					//const ema1Higher = ema1.value > ema2.value;
					sObj = {
                     "time": candle.time,
				     "open": candle.open,
				     "high": candle.high,
				     "low": candle.low,
                     "close": candle.close,
                     "thisColor" : thisColor ,
                     "emaShort" : ema1.value,
                     "emaLong"  : ema2.value,
                     "macd"  : ema1.value - ema2.value,                    
                     "emaSlope" : '', 
                     "emaDirection" : '',
					 "emaAboveA" : '',
                     "emaAbove" : emaAbove,
                     "conflictType"  : "n",
                     "conflictCon" : 0 ,
                     "conflictNo" : 0,
                     "adxValue" : 0 ,
					 "adxValue" : 0 ,
				    }

                   // sObj.emaShort = ema1.value ;
                   // sObj.emaLong  = ema2.value ;

                    let isBullish = candle.close > candle.open;
                    const ema1Higher = ema1.value > ema2.value;
                    
                    // Conflict conditions
                    if ((ema1Higher && !isBullish) || (!ema1Higher && isBullish)) {
						conflictNo++;
                        const conflict = {
                            time: candle.time,
                            timeString: new Date(candle.time * 1000).toLocaleString(),
                            type: ema1Higher ? 'Bearish Conflict' : 'Bullish Conflict',
                            candleColor: isBullish ? 'Green' : 'Red',
                            ema1Value: ema1.value.toFixed(4),
                            ema2Value: ema2.value.toFixed(4)
                            
                        };
						sObj.conflictType = conflict.type ;
						sObj.conflictNo = conflictNo ;
						
                        
                        conflicts.push(conflict);
                        
                        // Add marker
						isAbove = true;
                        const marker = {
                            time: candle.time,
                            position: isAbove ? 'belowBar' : 'aboveBar',
                            color: ema1Higher ? '#ef5350' : '#26a69a',
                            shape: 'circle',
                            text: 'C-'+conflictNo,
                            size: 1
                        };
                        
                        allMarkers.push(marker);
						
						
                    } 


					
					
					newCandle.push(sObj);
                }
            } 

            //console.log('candle length',newCandle.length);
            maxLossCon = 0 ; numCon2 = 0 ;
			for (let i=1;i<=newCandle.length-1 ;i++ ) {
			    if (newCandle[i].conflictType !== 'n' ) {
					newCandle[i].conflictCon = newCandle[i-1].conflictCon +1 ;
					if (newCandle[i].conflictCon == 2  ) {
						numCon2++;
					}
					if (newCandle[i].conflictCon > maxLossCon ) {
                       maxLossCon = newCandle[i].conflictCon ;
					}
			    }  else {
                    newCandle[i].conflictCon = 0 ;
			    }	
				if (i>=1) {				
				  emaSlope = newCandle[i].emaShort - newCandle[i-1].emaShort ;
				  if (emaSlope > slopeTheresHold) {
					  emaDirection = 'U'
				  }
				  if (emaSlope < slopeTheresHold) {
					  emaDirection = 'D'
				  }
				  if (emaSlope === 0) {
					  emaDirection = 'P'
				  }
				  newCandle[i].emaDirection = emaDirection ;
				  newCandle[i].emaSlope = emaSlope ;


                } 
				
				
			}

            let percent = (numCon2/newCandle.length)*100 ;
			//alert(numCon2+'->'+percent.toFixed(2));
			newCandle2 = calculateADXWithDirections(newCandle,4);
			//console.log('adx',newCandle2[100])

		  // สร้างรายงานครบ
          


//Alternate Color เริ่มคำนวณ ที่นี่


          const result = generateAlternationReport(newCandle2, 4);
          //console.log('รายงานการสลับสี:', result.report.patterns);
		  const dataWithAlternateFields = addColorAlternationFields(newCandle2, 6);
		  conflictData = dataWithAlternateFields ;

		  result2 = generateAlternationReport(newCandle2,6);
          // console.log('Data with alternation fields:', result.dataWithAlternation);
          //console.log('Alternation report:', result2.htmlTable);
		  document.getElementById("alterContainer").innerHTML = result2.htmlTable ;

// Candle Body Code 
          newCandle3 = processCandleArray(newCandle2);			
		  document.getElementById("conflictDataText").value = JSON.stringify(newCandle3); 

			
			//console.log('newCandle',newCandle);
			percentConflicts = (allMarkers.length/candleData.length)*100 ;
            percentConflicts = percentConflicts.toFixed(2);
			document.getElementById("statusText2").innerHTML = ' จำนวน Conflicts= '+ 
			allMarkers.length +'/'+ candleData.length + ' = '+ percentConflicts.toString() +' % ';
			//document.getElementById("conflictDataText").value = JSON.stringify(allMarkers);
			//document.getElementById("conflictDataText").value = JSON.stringify(newCandle);

			document.getElementById("conflictList").value = JSON.stringify(allMarkers);
			
			
            
            // Set all markers at once
            if (allMarkers.length > 0) {
                //candleSeries.setMarkers(allMarkers);
            }
			//alert(conflicts.length);
            
            return conflicts;
        } 

		

		// EMA period change handlers
        document.getElementById('ema3').addEventListener('change', (e) => {
            
			sObj = calculateEMAs();
			 //console.log(sObj)
			
			/*sObj = {
              "candleData" : candleData,
              "emaShortData" : ema3Data,
              "emaLongData" : ema5Data
			}
			*/
            // Update chart
            
            ema3Series.setData(ema3Data);
			ema1Period = parseInt(e.target.value);
            ema3Series.applyOptions({
                title: 'EMA ' + ema1Period
            });
            ema5Series.setData(ema5Data);
            candleData2 = sObj.candleData ;
			ema1Data2 = sObj.emaShortData;
			ema2Data2 = sObj.emaLongData; 

			emaLongData = sObj.emaSuperLongData ;
			emaLongData = sObj.emaSuperLongData ;
			//console.log('emaLong Data',emaLongData)
			
			//emaSuperLongSeries.setData(emaSuperLongData);
            

			detectConflicts(candleData2, ema1Data2, ema2Data2);
			
			
			//loadHistoricalData()
            //updateChart();
			
        });
        
        document.getElementById('ema5').addEventListener('change', (e) => {
            ema2Period = parseInt(e.target.value);
            ema5Series.applyOptions({
                title: 'EMA ' + ema2Period
            });
            //updateChart();
			loadHistoricalData()
        });

          document.getElementById('emaLong').addEventListener('change', (e) => {
            emaLongPeriod = parseInt(e.target.value);
            emaLongSeries.applyOptions({
                title: 'EMA ' + emaLongPeriod
            });
            //updateChart();
			loadHistoricalData()
        });

        // Update status
        function updateStatus(status, message) {
            const statusElement = document.getElementById('connectionStatus');
            const textElement = document.getElementById('statusText');
            
            statusElement.className = `connection-status ${status}`;
            textElement.textContent = message;
        }

        // Main functions
        function connectAndLoadData() {
            loadHistoricalData();
        }

        function disconnectWebSocket() {
            if (ws) {
                ws.close();
                ws = null;
            }
            updateStatus('disconnected', 'Disconnected');
        } 

		function AddMarkerConflicts() {

            //console.log('Conflict List=',document.getElementById("conflictList").value)
              
            
			allMarkers = JSON.parse(document.getElementById("conflictList").value);
			//console.log('allMarkers',allMarkers);
			
			//console.log('candleSeries Length=',candleSeries.length);
			if (allMarkers.length > 0) {
                candleSeries.setMarkers(allMarkers);
				//console.log('After candleSeries',candleSeries);
            }
			
            
            //return conflicts;
         
		
		
		} // end func

		function MainCalTradeAdjacent() {

         // แบบ NoMartingale
		 
         candleData = JSON.parse(document.getElementById("conflictDataText").value) ;
		 
		 
		 startTime  = parseInt(document.getElementById("startTimeSelected").value) ;
		 stopTime   = parseInt(document.getElementById("stopTimeSelected").value) ;
		 
		  
         startIndex = parseInt(document.getElementById("startTimeIndex").value) ;
		 stopIndex = parseInt(document.getElementById("stopTimeIndex").value) ;
		 
		 const candleData2 = candleData.slice(startIndex, stopIndex);
		 //console.log('Len =',candleData2.length) ;
		 
         targetBalance = 10000000 ;
		 console.clear();
         //console.log(candleData2);
		 const trader1 = new TradeByAdjacentColor(candleData2, true, 0.94,targetBalance);
         const results = trader1.runTrade(0);
		 summary = trader1.getSummaryStats() ;

		 st = 'Adjacent Method :: Total Trade =' +   summary.totalTrades ;
		 st += ' Total Win =' +   summary.winRate  ;
		 st += ' Balance =' +   summary.finalBalance.toFixed(2) ;
         st += ' Max Loss Con =' +   summary.maxLossContinue ;
		 st += ' Max Money Trade=' + summary.maxMoneyTrade ;
		 document.getElementById("statusText2").innerHTML = st ; 

				
         console.log('Results Adjacent ,',results);
		 document.getElementById("adjacentText").value = JSON.stringify(results);
         console.log(trader1.getSummaryStats());
         console.log(trader1.getStopStatus());
		 //getTableTrade(results);
		 
         let sourceA = [] ;
         //alert(source.length); 
		 //alert(results.length); 
         for (let i=0;i<=results.length-1 ;i++ ) {
			//console.log(i,' = ',results[i].LossContinue)			 
            if (parseInt(results[i].LossContinue) >= 1 ) {
               //console.log('Yes',results[i])               
               sourceA.push(results[i]) ;
			   //console.log(sourceA.length)			   
            }
         }
		// alert(sourceA.length);
		 allMarkers = AddMarker(sourceA,candleSeries);
		 generateTableTrade('adjacentText','resultsContainer');
		 


} // end func

function SetAssetSelect(btnID,assetName) {
/*
         console.log('asset id=',btnID) ;
	     btnID1 = btnID.substr(4,100);
		 assetName = btnID1[1] ;
*/
		 console.log('asset Name=',assetName)
		 
		 document.getElementById('assetSelect').value = assetName
         document.getElementById("assetCandleID").innerHTML = assetName ;

		 document.getElementById("SymBols").value = assetName ;
		 document.getElementById("assetPlan2").value = assetName ;

		 assetList = document.getElementById("allSymbolsThisGroup").value  ;
         assetListAr = assetList.split(';');
		 for (let i=0;i<=assetListAr.length-1 ;i++ ) {
			 thisName = '#btn_' + assetListAr[i] ;
			 $(thisName).removeClass('btnSelected');		 
		 }
		 CurrentName = '#' + btnID ;
		 $(CurrentName).removeClass('btnNormal');		 
		 $(CurrentName).addClass('btnSelected');		 





} // end func


// Populate asset select listbox
function populateAssetSelect(groupName) {
    const assetSelect = document.getElementById('assetSelect');
    const statusDiv = document.getElementById('loadingStatus');
    
    // Clear existing options
    assetSelect.innerHTML = '';
    
    const assets = DERIV_ASSETS[groupName];
    if (!assets) {
        statusDiv.textContent = 'Invalid group';
        return;
    }

	let st = '';
	let assetList = '' ;

	for (let i=0;i<=assets.length-1 ;i++ ) {
		let ch = "<button type='button'  class='btnNormal' id= 'btn_" + assets[i].symbol +"' onclick=SetAssetSelect(this.id,'" + assets[i].symbol+ "')>"+ assets[i].symbol+"</button>";
		st = st + ch ;
		assetList += assets[i].symbol+ ';';	
	}

	
	document.getElementById("allSymbolsThisGroup").value = assetList ;
	document.getElementById("btnAssetZone").innerHTML = st ;
	
    
    // Add all assets to listbox
    assets.forEach(asset => {
        const option = document.createElement('option');
        option.value = asset.symbol;
        option.textContent = `${asset.open ? '🟢' : '🔴'} ${asset.name}`;
        option.dataset.status = asset.open ? 'open' : 'closed';
        option.style.color = asset.open ? '#26a69a' : '#ef5350';
        option.style.fontWeight = asset.open ? 'normal' : '300';
        
        assetSelect.appendChild(option);
    });
    
    const openCount = assets.filter(a => a.open).length;
    statusDiv.textContent = `Loaded ${assets.length} ${groupName} assets (${openCount} open, ${assets.length - openCount} closed)`;
}

// Setup button event listeners
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('forexBtn')?.addEventListener('click', () => populateAssetSelect('forex'));
    document.getElementById('volatilityBtn')?.addEventListener('click', () => populateAssetSelect('volatility'));
    document.getElementById('cryptoBtn')?.addEventListener('click', () => populateAssetSelect('crypto'));
    document.getElementById('commoditiesBtn')?.addEventListener('click', () => populateAssetSelect('commodities'));
    document.getElementById('indicesBtn')?.addEventListener('click', () => populateAssetSelect('indices'));
    
    // Load forex by default
    populateAssetSelect('forex');
});

		function MainCalTradeEMATrend() {

				console.clear();
				candleData= JSON.parse(document.getElementById("conflictDataText").value) ;
				console.log("=== EMA Trend Trading (NoMartingale, Index 0-2) ===");
				let startIndex = 0 ;
                let stopIndex = 0 ;
				timeSearch = parseInt(document.getElementById("startTimeSelected").value) ;
				//console.log(formatTime(timeSearch));
				
				for (let i=0;i<=candleData.length-1 ;i++ ) {
				   if ((parseInt(candleData[i].time)+(7*3600) === timeSearch)) {
					   let startIndex = i ; break ;
				   }
				}
				timeSearch = parseInt(document.getElementById("stopTimeSelected").value) ;
				for (let i=0;i<=candleData.length-1 ;i++ ) {
				   if (candleData[i].time === timeSearch) {
					   let stopIndex = i ;break ;
				   }
				}
				
				startIndex = parseInt(document.getElementById("startTimeIndex").value) ;
				stopIndex = parseInt(document.getElementById("stopTimeIndex").value) ;
				
				

				const emaTrader1 = new TradeByEmaTrend(candleData, true, 0.94, null,startIndex,stopIndex);
				const results1 = emaTrader1.runTrade();
				console.log("Trade Results:", results1);
				let summary = emaTrader1.getSummaryStats() ;
				console.log("Summary Stats:", summary);
                 
                st = 'EMA Trend Method :: Total Trade =' +   summary.totalTrades ;
				st += ' Total Win =' +   summary.winRate  ;
				st += ' Balance =' +   summary.finalBalance.toFixed(2) ;

                st += ' Max Loss Con =' +   summary.maxLossContinue ;
				st += ' Max Money Trade=' + summary.maxMoneyTrade ;
				document.getElementById("statusText4").innerHTML = st ;
				

				allMarkers = [] ;
				candleSeries.setMarkers(allMarkers);

				document.getElementById("resultEMATrend").value = JSON.stringify(results1);
				const resultLoss = results1.filter(object => object.Profit < 0);
				//alert(resultLoss.length);
				let isBullish = true ;
				conflict = JSON.parse(document.getElementById("conflictDataText").value) ;
				for (let i=0;i<=resultLoss.length-1 ;i++ ) {
					const marker = {
                            time: resultLoss[i].time,
                            position: isBullish ? 'belowBar' : 'aboveBar',
                            color:  '#ff0080',
                            shape: 'circle',
                            text: 'L',
                            size: 1
                     };
                     allMarkers.push(marker);
				
					 thisTime =  resultLoss[i].time; 
					 
					  

						/*for (let i2=0;i2<= conflict.length-1 ;i2++ ) {
							 //console.log(conflict[i2].conflictType)
							 
							 if (conflict[i2].conflictType == 'y' && conflict[i2].time == thisTime ) {
								 console.log('Found') ;
								 const marker2 = {
								  time: conflict[i2].time,
								  position: 'aboveBar',
								  color:  '#ff0080',
								  shape: 'circle',
								  text: 'C',
								  size: 1
								 };
								 allMarkers.push(marker2);

							 } // end if

						 }
						 */
				}
				candleSeries.setMarkers(allMarkers);



        } // end func

		

        function toggleTooltip() {
            tooltipEnabled = !tooltipEnabled;
            if (!tooltipEnabled) {
                document.getElementById('tooltip').style.display = 'none';
            }
        }

        function toggleRealTime() {
            realTimeEnabled = !realTimeEnabled;
            // Real-time implementation would go here
            updateStatus('connected', realTimeEnabled ? 'Real-time mode ON' : 'Real-time mode OFF');
        } 
		function createTableFromJson(jsonArray, containerId) {
            // ตรวจสอบว่า jsonArray เป็น array และไม่ว่าง
            if (!Array.isArray(jsonArray) || jsonArray.length === 0) {
                document.getElementById(containerId).innerHTML = '<p style="text-align: center; color: #666;">ไม่มีข้อมูลให้แสดง</p>';
                return;
            }

            // สร้าง table element
            const table = document.createElement('table');
            
            // สร้าง header จาก keys ของ object แรก
            const headerRow = document.createElement('tr');
            const firstItem = jsonArray[0];
            
            // กำหนด header names ที่เป็นภาษาไทย
            const headerMapping = {
                'time': 'Unix Time',
                'timeDisplay': 'วันที่/เวลา',
                'action': 'การกระทำ',
                'suggestColor': 'สีที่แนะนำ',
                'resultColor': 'สีผลลัพธ์',
                'moneyTrade': 'จำนวนเทรด',
                'winStatus': 'สถานะ',
                'profit': 'กำไร/ขาดทุน',
                'winContinue': 'ชนะต่อเนื่อง',
                'lossContinue': 'แพ้ต่อเนื่อง',
                'balance': 'ยอดเงิน'
            };

            Object.keys(firstItem).forEach(key => {
                const th = document.createElement('th');
                th.textContent = headerMapping[key] || key;
                headerRow.appendChild(th);
            });
            
            table.appendChild(headerRow);
            
            // สร้าง body rows
            jsonArray.forEach(item => {
                const row = document.createElement('tr');
                
                Object.entries(item).forEach(([key, value]) => {
                    const td = document.createElement('td');
                    
                    // จัดรูปแบบข้อมูลตาม key
                    switch(key) {
                        case 'timeDisplay':
                            td.textContent = value;
                            td.style.fontSize = '12px';
                            break;
                            
                        case 'winStatus':
                            td.textContent = value ? 'ชนะ' : 'แพ้';
                            td.className = value ? 'win' : 'loss';
                            break;
                            
                        case 'profit':
                            td.textContent = value > 0 ? `+${value}` : value.toString();
                            td.className = value > 0 ? 'profit-positive' : 'profit-negative';
                            break;
                            
                        case 'suggestColor':
                        case 'resultColor':
                            td.textContent = value;
                            td.className = `color-${value.toLowerCase()}`;
                            break;
                            
                        case 'balance':
                            td.textContent = value.toLocaleString('th-TH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            td.className = 'balance';
                            break;
                            
                        case 'moneyTrade':
                            td.textContent = value.toLocaleString('th-TH');
                            break;
                            
                        case 'time':
                            td.textContent = value;
                            td.style.fontSize = '11px';
                            td.style.color = '#666';
                            break;
                            
                        default:
                            td.textContent = value;
                    }
                    
                    row.appendChild(td);
                });
                
                table.appendChild(row);
            });
            
            // แสดงผลตารางใน container
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            container.appendChild(table);
        } 

		function getTableTrade(candleData) {
            
             
			st = `<table id='tblShowTrade' style='background:white;width:100%'>
            <tr>
				<th>ลำดับ</th>
				<th>TimeStamp</th>
				<th>Time</th>
				<th>Color</th>
				<th>Ema Above</th>
				<th>Action</th>
				<th>Win Status</th>
				<th>Loss Con</th>				
				<th>Conflict Con</th>
            </tr>
            ` ;

			startTime =document.getElementById("startTimeSelected").value ;
			stopTime =document.getElementById("stopTimeSelected").value ;

			const result = candleData.filter(object => (
				(object.time >= startTime) &&
				(object.time <= stopTime) )
				
			);
			for (let i=0;i<=result.length-1 ;i++ ) {
				time1 = result[i].time ;
				stime = formatTime2(result[i].time) ;
				color = result[i].thisColor ;
				conflictCon = result[i].ConflictCon ;
                lossCon = result[i].LossContinue;
				emaAbove = result[i].emaAbove ;
				action = result[i].Action ;
				winStatus = result[i].WinStatus ;

				st += `
                <tr>
					<td>${i+1}</td>
					<td>${time1}</td>
					<td>${stime}</td>
					<td>${color}</td>
					<td>${emaAbove}</td>
					<td>${action}</td>
					<td>${winStatus}</td>
					
					<td>${lossCon}</td>
					<td>${conflictCon}</td>
                </tr>
             ` ;			
			}
			st += '</table>';
			//alert(result.length);
			
            document.getElementById("resultsContainer").innerHTML = st;
            
		
		
		} // end func

		function ClearMarker() {
		         allMarkers = [] ;  
		         candleSeries.setMarkers(allMarkers); 
		
		} // end func
		
		function AddMarkerAdjacent() {

		         allMarkers = [] ;  
				 sData = JSON.parse(document.getElementById("adjacentText").value );
				 for (let i=0;i<=sData.length-1 ;i++ ) {
					 if (sData[i].WinStatus === 'Loss') {
						 const marker = {
                           time: sData[i].time,
                           position: 'aboveBar',
                           color:  '#ff0080',
                           shape: 'circle',
                           text: 'L'+ (sData[i].LossContinue),
                           size: 1
                         };
                         allMarkers.push(marker);
				     }	
				 }
				// alert(allMarkers.length)
				  
		         candleSeries.setMarkers(allMarkers); 
		
		} // end func

		function AddMarkerAdjacentV2() {

		         allMarkers = [] ;  
				 sData = JSON.parse(document.getElementById("adjacentTextV2").value );
				 for (let i=0;i<=sData.length-1 ;i++ ) {
					 if (sData[i].WinStatus === 'Loss') {
						 const marker = {
                           time: sData[i].time,
                           position: 'belowBar',
                           color:  '#ffff00',
                           shape: 'circle',
                           text: 'L'+ (sData[i].LossContinue),
                           size: 1
                         };
                         allMarkers.push(marker);
				     }	
				 }
				 //alert(allMarkers.length)
				  
		         candleSeries.setMarkers(allMarkers); 
		
		} // end func
		
		function CopyRawCandle() {
		  
		    textArea= document.getElementById("rawCandle");
			navigator.clipboard.writeText(textArea.value)
              .then(() => {
                alert("Copied to clipboard!");
              })
               .catch(err => {
                 console.error("Failed to copy: ", err);
            });
		
		} // end func
		
		 
		function testTrade() {
			sdata = JSON.parse(document.getElementById("conflictDataText").value) ;
			const tradingSystem = new EMATradingSystem();
            // รันการเทรดกับข้อมูล
            const results = tradingSystem.processAllTrades(sdata);
           // ดูสรุปผล
            console.log(results.summary);
			//console.log(results.detailedResults);
			containerId = 'resultsContainer' ;
			createTableFromJson(results.detailedResults, containerId);
			
			return ;

		    sdata = JSON.parse(document.getElementById("conflictDataText").value) ;
			numWin= 0 ; numLoss = 0 ;
			for (let i=3;i<=sdata.length-2 ;i++ ) {
				if (sdata[i].emaDiff > 0) {
					suggestColor = 'Green' ;
				} else {
                    suggestColor = 'Red' ;
				}
				nextColor = sdata[i].thisColor ;
				if (suggestColor == nextColor ) {
					numWin++
				} else {
                   numLoss++
				}
			
			}
			console.log('NumWin=',numWin,'NumLoss=',numLoss);
			document.getElementById('statusText2').innerHTML += ' NumWin=' +numWin +' NumLoss=' + numLoss ;

			
		
		} // end func

		function AddPriceLine(price, opts = {}){

		   if (document.getElementById("LineSupport").value === 'SupportLine') {
			   document.getElementById("Support").value = price;
		   }
		   if (document.getElementById("LineSupport").value === 'ResistanceLine') {
               document.getElementById("Resistance").value = price;              
		   }
		   
       // สร้าง price line ใหม่
	   //candleSeries
			  priceLine = candleSeries.createPriceLine({
				price: price,
				color: '#ffffff',
				lineWidth: opts.lineWidth ||3,
				lineStyle: opts.lineStyle || LightweightCharts.LineStyle.Solid,
				axisLabelVisible: opts.axisLabelVisible !== undefined ? opts.axisLabelVisible : true,
				title: opts.title || `Price ${price}`
			  });
		}

		function ToggleSuperLong() {

			isShowSuperLong = document.getElementById("isShowSuperLong").checked;
			if (isShowSuperLong) {
               emaLongSeries.setData(emaSuperLongData);  
			   emaLongSeries.applyOptions({
                 visible: true  // 
               });

			} else {
               emaLongSeries.applyOptions({
                 visible: false  // ซ่อน
               });
			}
		} // end func

		function getPortfolio() {

            if (portfolioRequestEver === false) {
            
			  const portfolioRequest = {
                   portfolio: 1
              };
              console.log('Sending portfolio request:', portfolioRequest);
              ws.send(JSON.stringify(portfolioRequest));
			  portfolioRequestEver= true ;
			} else {
              alert('เคย portfolioRequestEver ')
			}
		} // end func

		function UpdateRow(data) {

           contract_id = data.contract_id ;
		   expiry_time = data.expiry_time ; 
 		   //profit = data.profit ;

		   profit_id = 'profit_'+contract_id
           expiryTime_id = "expiryTime_" +contract_id ;
		   //document.getElementById(profit_id).innerHTML = data.profit;
		   document.getElementById(expiryTime_id).innerHTML = data.expiry_time;
		   

 
			return;



             
        } // end function

		function createNewRow(data) {

/*
            data.buy.contract_id,data.buy.start_time,
            data.echo_req.parameters.amount
            data.echo_req.parameters.contract_type,
            data.echo_req.parameters.contract_symbol,
*/
             
			 //console.log('Data9999',data)
			 
			 
             const tableWrapper = document.getElementById('tableWrapper');           
             tableWrapper.style.display = 'block';
            
             const tbody = document.getElementById('contractsTable');
             tbody.innerHTML = '';
			 const tr = document.createElement('tr');
			  tr.dataset.contractId = data.buy.contract_id;
					
			 const profit = 0;
			 const profitClass = profit >= 0 ? 'profit-positive' : 'profit-negative';
			 const profitSign = profit >= 0 ? '+' : '';
					
			 const contractTypeClass = data.echo_req.parameters.contract_type === 'CALL' ? 'type-call' : 'type-put';
			//		document.getElementById("symbol").value = contract.symbol;
			index = 0		
			tr.innerHTML = `
						<td><strong>${index + 1}</strong></td>
						<td>${data.buy.contract_id}</td>
						<td><strong>${data.buy.symbol}</strong></td>
						<td><span class="contract-type ${contractTypeClass}">${data.echo_req.parameters.contract_type}</span></td>
						<td>${data.buy.buy_price}</td>
						<td>${data.buy.payout}</td>
						<td id="profit_${data.buy.contract_id}" class="${profitClass}"></td>
						<td>${formatTimestamp(data.buy.start_time)}</td>
						<td
				         id="purchase_time_${data.buy.contract_id}"
				        ></td>
						<td 
						id="expiryTime_${data.buy.contract_id}"
						class="time-remaining"></td>

						<td id="Minprofit_${data.buy.contract_id}" class="${profitClass}"></td>
						<td id="Maxprofit_${data.buy.contract_id}" style="color:green"></td>
						<td id="entryspot_${data.buy.contract_id}">
							 ${data.buy.entry_spot}
						</td>


						<td id="message_${data.buy.contract_id}">
							<button class="action-btn btn-sell" onclick="sellContract(${data.buy.contract_id})">
								ขาย-
							</button>
						</td>
					`;
					
					tbody.appendChild(tr);

		
		
		} // end func

		function addElement2ListBox(listboxId, text, value) {
			// 1. Get a reference to the listbox element
			const listbox = document.getElementById(listboxId);

			// 2. Create a new Option object: new Option(text, value, defaultSelected, selected)
			const newOption = new Option(text, value); // Only text and value are typically needed

			// 3. Add the new option to the listbox
			listbox.add(newOption);
       }

	   function getemptyTradeObj() {

		   emptyTradeObj ={
			  "contractID" : '',
			  "contractType" : '',
			  "asset" : '',
			  "amount" : 0.0,
			  "duration" : '',
			  "purhaseTimeStamp" : 0,
			  "purhaseTimeDisplay" : '',
			  "expiryTimeStamp" : 0,
			  "expiryTimeDisplay" : '',
			  "entrySpot" : 0.0,
			  "exitSpot" : 0.0,
			  "targetMoney" : 0.0 ,
			  "winStatus" : '',
			  "Profit"  : 0.0 ,
			  "ProfitList" : []	   
			 }

            return emptyTradeObj ;
	   
	   
	   } // end func

	   
	   function UpdateTxtContractList(proposal_open_contract) {

           const select = document.getElementById('contractList');
		   st = document.getElementById("txtAllTradelist").value ;
		   //alert(st);
		   tradelist = JSON.parse(st); 
		   for (let i=0;i<=tradelist.length-1 ;i++ ) {
			   if (tradelist[i].contractID === proposal_open_contract.contract_id) {
                 tradelist[i].winStatus = proposal_open_contract.status;
                 tradelist[i].entrySpot = proposal_open_contract.entry_spot;
                 tradelist[i].Profit = proposal_open_contract.profit ;
				 tradelist[i].expiryTimeStamp = proposal_open_contract.date_expiry+(7*3600);
				 tradelist[i].expiryTimeDisplay = formatTime(proposal_open_contract.date_expiry + (7*3600)) ;

				 if (proposal_open_contract.status=='sold' ||    proposal_open_contract.status=='won') {
				 
				   tradelist[i].SaleTimeStamp = proposal_open_contract.current_spot_time;
				   tradelist[i].SaleTimeDisplay =formatTime(proposal_open_contract.current_spot_time + (7*3600));
                   tradelist[i].exitAtSpot = proposal_open_contract.current_spot ;
			     }
				 st = JSON.stringify(tradelist);
				 document.getElementById("txtAllTradelist").value = st ;
				 //alert('Update Trade') ;


			   } 
 
               const option = document.createElement('option');
               option.value = tradelist[i].contractID;        // ค่า value (เช่น id)
               option.textContent = tradelist[i].contractID; // ข้อความที่แสดง
               select.appendChild(option);
               
			   // contractID,contractType
		   
		   } // end for 
		   sObj = {
             "timestamp"    : proposal_open_contract.current_spot_time, 
             "entrySpot"    : proposal_open_contract.entry_spot,
			 "current_Spot" : proposal_open_contract.current_spot,
             "SpotDiff"     : proposal_open_contract.current_spot- proposal_open_contract.entry_spot,
             "profit"       : proposal_open_contract.profit 
		   } 



		   CurrentTradeObject.ProfitList.push(sObj);
		   console.log('CurrentTradeObject',CurrentTradeObject) ;
		   

	   
	   
	   
	   } // end func

	   function FillContractData(ContractID) {

		   //alert(ContractID);
		   ContractID = parseInt(ContractID);

		   st = document.getElementById("txtAllTradelist").value ;
		   tradelist = JSON.parse(st); 
		   for (let i=0;i<=tradelist.length-1 ;i++ ) {
              //console.log(tradelist[i].contractID ,' vs ', ContractID)
             
			 if (tradelist[i].contractID === ContractID) {
			   console.log('Found-',tradelist[i]) ;
			   a = tradelist[i] ;
			   console.log('a',a.asset) ;
			   let assetA = a.asset 
			   console.log('aA',assetA) ;

			   
				 
			   document.getElementById("assetContract999").value = assetA;
               document.getElementById("contractType999").value = a.contractType;
			   document.getElementById("entrySpot999").value = a.entrySpot ;
			 }
	       }
	   
	   } // end func

	   function DrawEntry() {



                 price =  parseFloat(document.getElementById("entrySpot999").value)  ; 
				 contractType999 = document.getElementById("contractType999").value ;
				 
				 
				 if (contractType999 === 'CALL') {
                    lineColor = '#2196F3' ;
				 } else {
                    lineColor = '#ff0000' ;
				 }
				 
			     const line = candleSeries.createPriceLine({
						price: price,
						color: lineColor,
						lineWidth: 3,
						lineStyle: 0, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });
					  
				 supportResistanceLines.push(line);					  

				 const line2 = ohlcSeries.createPriceLine({
						price: price,
						color: lineColor,
						lineWidth: 3,
						lineStyle: 0, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });
					  
				 supportResistanceLinesOHLC.push(line2);	
	   
	   
	   } // end func

	   function DrawEntry2(contractID) {

                contractTypeID = 'contract_type_' + contractID ; 
                contractType999 = document.getElementById(contractTypeID).innerHTML;
				entrySpotID = 'entrySpot_' + contractID ;
				price = parseFloat(document.getElementById(entrySpotID).innerHTML) ;
                
				zeroProfitvalue = price + 0.3010 ;
                   				 
				//alert(contractType999) ;
				contractType999 = contractType999.replace(/<[^>]*>/g, '');
				//alert(contractType999) ;
				 
				 if (contractType999 === 'CALL') {
                    lineColor = '#2196F3' ;
					zeroProfitvalue = price + 0.3010 ;
					zerolineColor = '#ffff00' ;
				 } else {
                    lineColor = '#ff0000' ;
					zeroProfitvalue = price - 0.3010 ;
					zerolineColor = '#ff0080' ;

				 }
				 
			     const line = candleSeries.createPriceLine({
						price: price,
						color: lineColor,
						lineWidth: 3,
						lineStyle: 0, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });
					  
				 //supportResistanceLines.push(line);
				 
				 const lineZero = candleSeries.createPriceLine({
						price: zeroProfitvalue,
						color: zerolineColor,
						lineWidth: 3,
						lineStyle: 3, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });
					  
				 supportResistanceLines.push(lineZero);


				 const line2 = ohlcSeries.createPriceLine({
						price: price,
						color: lineColor,
						lineWidth: 3,
						lineStyle: 0, // 0=Solid, 1=Dotted, 2=Dashed, 3=LargeDashed, 4=SparseDotted
						axisLabelVisible: true,
						title: `S/R: ${price.toFixed(2)}`,
					  });
					  
				 supportResistanceLinesOHLC.push(line2);	
	   
	   
	   } // end func
	   
	   
	   
	   
	   function updateOptionTextByIndex(listboxId, indexToUpdate, newText) {
			// 1. รับ Reference ไปยัง ListBox (<select>)
			const listbox = document.getElementById(listboxId);

			// 2. ตรวจสอบว่า Index ที่ระบุมีอยู่จริงใน ListBox หรือไม่
			if (indexToUpdate >= 0 && indexToUpdate < listbox.options.length) {
				// 3. เข้าถึง Element <option> โดยใช้ Index
				const optionToUpdate = listbox.options[indexToUpdate];

				// 4. เปลี่ยนข้อความที่แสดง
				optionToUpdate.text = newText;
				// หรือใช้ optionToUpdate.textContent = newText;
			} else {
				console.error("Index ที่ระบุอยู่นอกขอบเขตของ ListBox");
			}
       }

// ตัวอย่างการใช้งาน: อัพเดทรายการที่ 2 (Index = 1)
// updateOptionTextByIndex("myListBox", 1, "ข้อความใหม่สำหรับรายการที่ 2");
		
		
		
		

        // Handle window resize
        window.addEventListener('resize', () => {
            if (chart) {
                const container = document.getElementById('chart');
                chart.applyOptions({
                    width: container.clientWidth,
                    height: container.clientHeight,
                });
            }
        });

        // Initialize on load
        window.addEventListener('load', initChart);
    </script>

	resultsContainer

    <button type='button' id='' class='mBtn' onclick="generateTableTrade('adjacentText','resultsContainer')">Create Table Adjacent </button>
	<button type='button' id='' class='mBtn' onclick="generateTableTrade('adjacentTextV2','resultsContainer')">Create Table Adjacent V2 </button>
	<select id="columnSelect" class="column-select">
        <option value="">Select Column</option>
    </select>
    <button onclick="showMaxValues()" class="max-button">Show Max Values</button>

	<div  id='resultsContainer'></div>
</body>
</html>
<?php

function CreateResultTxt() {  ?>
<style>
 .txtBox { width:120px; }
</style>

         
         <input type="text" id="resultEMATrend">

		 <input type="text" id="startTimeSelected" class='txtBox' value=0
		 onfocus= document.getElementById("TimeSelected").value='s'
		 >
		 Start Index:: <input type="text" id="startTimeIndex" class='txtBox' value=0
		 onfocus= document.getElementById("TimeSelected").value='s'
		 >


		 <input type="text" id="stopTimeSelected" class='txtBox' value= 0 
		 onfocus= document.getElementById("TimeSelected").value='e'
		 >
		 Stop Index:: <input type="text" id="stopTimeIndex"class='txtBox' value=0
		 onfocus= document.getElementById("TimeSelected").value='e'
		 >
		 <button type='button' id='' class='mBtn' onclick="CopyData()">Copy Selected Point</button>
		 <input type="hidden" id="TimeSelected" value= 's'>
		 <hr>
          <table id='tableB'>
		  <tr>
				<th>ลำดับ</th>
				<th>TimeStamp</th>
				<th>Time</th>
				<th>Color</th>
				<th>Ema Above</th>
				<th>Action(ตามสี-Current)</th>
				<th>Win Status</th>
				<th>Loss Con</th>				
				<th>Conflict Con</th>
           </tr>
           
          </table>
		 <hr>
		 Conflict::<textarea id="conflictDataText"></textarea>
		 Adjacent::<textarea id="adjacentText"></textarea>
		 Adjacent V2::<textarea id="adjacentTextV2"></textarea>


<div id="" class="bordergray flex">
   <h2>Conflict Point</h2>
   <ol>
    <li>คือ จุดที่ emaAbove ไม่สอดคล้องกับ Color </li>
    <li>เราจะหาความจริง จาก Conflict Point คือ </li>
    <li>จากจุด Conflict ถ้าเราเทรด โดยยึด emaAbove จากจุด Previous จุดนี้ จะทำให้เกิด Loss  </li>
    <li>เราจะ เทรด เมื่อเกิดจุด Conflict Point ดังนี่
	  <ol>
	   <li>เทรดตามสี ที่เกิดจุด Conflict Point </li>
	   <li>หาดูว่า จุด Conflict Point มี  Max Continue เท่าไร </li>
	   <li>เข้าเทรด โดย  Adjacent + Conflict Point </li>
	   <li>เข้าเทรด โดย EMA Trend + Conflict Point </li>
	   <li> </li>
	  </ol>
	</li>
   </ol>
</div>


<?php } // end function
  
function BoxTradePlan2() {  ?>

<div class="box">
  <div  class="box-label">Trade Plan 2</div>
  Server-Time :: <span id='serverTime2' style='color:red;font-weight:bold'></span>
  <table style='width:250px'>
  <tr>
    <td id='' style='background:#0080ff'>Color List :: </td>
	<td id='color1' class=''></td>
	<td id='color2' class=''></td>
	<td id='color3' class=''></td>
	<td id='color4' class=''></td>
	<td id='color5' class=''></td>
	<td id='' class=''>Start Trade Plan2</td>
	<td id='' class=''>
	  <input type="checkbox" id="startTradePlan2">
	 </td>

  </tr>
  </table>
  Last Color:: <span id= 'lastColor' style='color:red;font-weight:bold'></span>
  
  <div id= 'result1' class="bordergray flex" style='sheight:250px;margin-top:20px'>
    <table><tr>
     <td>Asset::</td><td><input type="text" id="assetPlan2"></td>
	 <td>Action-Stratigies ::</td><td> <select id="ActionStratigies">
		<option value="S1" selected>Alter Color
		<option value="S2">Class Trade
	</select>
	</td>
	<td>
	 TradeStrateGy: 
    </td>
	<td>
	<select name="TradeStratigies">
		<option value="" selected>ชนะตาเดียวเลิก
		<option value="">ชนะแล้วเทรดต่อจนกว่าจะแพ้
	</select>
	</td>
	</tr>
    <tr> 
	 <td>MartinGale Plan ::</td>
	 <td><select id="martingalePlanNo">
		<option value="m0" selected>No MartinGale
		<option value="m1">Martingale Plan1
		<option value="m2">Martingale Plan2
	</select>
	</td>
    <td>
	 Money Trade::</td><td> <input type="text" id="MoneyTradePlan2" value=1></td>
    
	<td><input type="checkbox" id="signalAuto" checked style='width:50px'>Signal Auto
	</td><td>Loss Con :: <input type="text" id="lossCon" value=0></td>

	</tr>
	<tr><td colspan=6>
	<button type='button' id='' class='mBtn' style='background:#ff0080' onclick="PlaceTradePlan2('PUT')">PUT</button>
	<button type='button' id='' class='mBtn' style='background:#00ff00'  onclick="PlaceTradePlan2('CALL')">CALL</button>

	<button type='button' id='' class='mBtn' onclick="RequestPortfolio(ws)">TrackAllOrder</button>
	</td></tr></table>
	</div>


  </div>
  <div id= 'result1' class="bordergray flex">
    Table Track 
       <?php //TableTrack(); ?>
	   <div id="tableWrapper" class="table-wrapper" style="sdisplay: none;">

	   </div>
  </div>  
</div>


<?php
} // end function

function TableTrack() {  ?>

          <div id="tableWrapper" class="table-wrapper" style="sdisplay: none;">
                <table>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>Contract ID</th>
                            <th>Symbol</th>
                            <th>ประเภท</th>
                            <th>ราคาซื้อ</th>
                            <th>Payout</th>
                            <th>กำไร/ขาดทุน</th>
                            <th>เวลาซื้อ</th>
                            <th>เวลาหมดอายุ</th>
                            <th>เวลาที่เหลือ</th>
							<th>Min Profit</th>
							<th>Max Profit</th>


                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="contractsTable"></tbody>
                </table>
            </div>


<?php
} // end function


?>
<h2>หาข้อสรุป โดยใช้ ข้อมูล 1 เดือน </h2>
<ol>
 <li>แต่ละวัน มี แท่งเทียน สลับสี สูงสุดเท่าไร </li>
 <li>ค่า ADX เมื่อเกิดการสลับสี เป็นอย่างไร </li>
 <li> </li>
 <li> </li>
</ol>

<!-- 
<h2>สิ่งที่วางแผนไว้ </h2>
ทำ lab ใช้ ema2+ema3 วางแผน เข้าเทรด โดยดูว่า จะมี loss con เท่าไรบ้าง โดยจะให้ mark 2 จุด 
แล้ว ทำ lab Trade
<textarea id="conflictDataText" rows="" cols=""></textarea>
มี  array ของ candledata ดังนี้ 

ให้ทำการ backtest โดยมี เงื่อนไข ตามนี้ 
จาก ต้นแบบ candle data
[{"time":1750737720,"open":109760.9659,"high":109778.6756,"low":109507.1193,"close":109521.3281,"thisColor":"Red"}] 
ให้เพิ่ม field candleCode  โดยคำนวณจาก  ขนาดของ  high-low คิดเป็น 100% จากนั้น คำนวณสัดส่วนของ
UpperWick,Body,LowerWick ออกมาเป็น Percent แล้วนำมาสร้างรหัส ทีแทนถึง สัดส่วน ในรูปแบบ UpperWick-Body-LowerWick-Color โดยให้  F แทนรหัส เท่ากับ 100% ด้วย pure javascript และทำมาเป็น function ไม่ต้องทำทั้งเพจ 



New 
  - จุดมุ่งหมาย เข้าเทรดแล้ว Loss ไม่เกิน 4 หรือ Max Trade ครั้งที่ 5 ต้องชนะ
    ตอนนี้เราใช้ การเทรดแบบสีติดกันแล้ว ไล่ตรวจพบว่า บางครั้ง มีโอกาศหลุด ไปถึง  14 ครั้ง 
	วิธี ที่ควรจะเพิ่มเติมลงไป คือ 
	  1. สร้าง  function เพิ่ม การ ตรวจเชคลงไป แล้วให้มี Check Box เลือกและดูเงื่อนไขว่า Work ไหม
	  2. สร้าง เงื่อนไขการ เข้าเทรด เพื่อหลีกเลี่ยง การเข้าเทรดใน สภาวะดังกล่าว
         


Buy Response 
data.buy.contract_id,data.buy.start_time,
data.echo_req.parameters.amount
data.echo_req.parameters.contract_type,
data.echo_req.parameters.contract_symbol,



{
    "buy": {
        "balance_after": 5816.9,
        "buy_price": 30,
        "contract_id": 297563077028,
        "longcode": "Win payout if Volatility 10 Index is strictly lower than entry spot at 30 minutes after contract start time.",
        "payout": 58.35,
        "purchase_time": 1761256583,
        "shortcode": "PUT_R_10_58.35_1761256583_1761258383_S0P_0",
        "start_time": 1761256583,
        "transaction_id": 592492790568
    },
    "echo_req": {
        "buy": 1,
        "parameters": {
            "amount": 30,
            "app_markup_percentage": "0.2",
            "basis": "stake",
            "contract_type": "PUT",
            "currency": "USD",
            "duration": 30,
            "duration_unit": "m",
            "symbol": "R_10"
        },
        "price": 30
    },
    "msg_type": "buy"
}
contract_id,expiry_time,profit
Proposal_Open_Contract Response
{
    "account_id": 191869168,
    "barrier": "5738.692",
    "barrier_count": 1,
    "bid_price": 28.48,
    "buy_price": 30,
    "contract_id": 297560202228,
    "contract_type": "CALL",
    "currency": "USD",
    "current_spot": 5738.692,
    "current_spot_display_value": "5738.692",
    "current_spot_time": 1761253816,
    "date_expiry": 1761255615,
    "date_settlement": 1761255615,
    "date_start": 1761253815,
    "display_name": "Volatility 10 Index",
    "entry_spot": 5738.692,
    "entry_spot_display_value": "5738.692",
    "entry_tick": 5738.692,
    "entry_tick_display_value": "5738.692",
    "entry_tick_time": 1761253816,
    "expiry_time": 1761255615,
    "id": "a853e87c-9392-f94f-b4b2-9670a58bae5a",
    "is_expired": 0,
    "is_forward_starting": 0,
    "is_intraday": 1,
    "is_path_dependent": 0,
    "is_settleable": 0,
    "is_sold": 0,
    "is_valid_to_cancel": 0,
    "is_valid_to_sell": 1,
    "longcode": "ได้รับเงินผลตอบแทน หาก Volatility 10 Index มีค่าสูงกว่า จุดเข้า ที่ 30 minutes หลังจาก เวลาเริ่มต้นของสัญญา",
    "payout": 58.38,
    "profit": -1.52,
    "profit_percentage": -5.07,
    "purchase_time": 1761253815,
    "shortcode": "CALL_R_10_58.38_1761253815_1761255615_S0P_0",
    "status": "open",
    "transaction_ids": {
        "buy": 592487155308
    },
    "underlying": "R_10"
}

 

 emptyTradeObj : {
  "contractID" : '',
  "contractType" : '',
  "asset" : '',
  "amount" : 0.0,
  "duration" : '',
  "purhaseTimeStamp" : 0,
  "purhaseTimeDisplay" : '',
  "expiryTimeStamp" : 0,
  "expiryTimeDisplay" : '',
  "entrySpot" : 0.0,
  "exitSpot" : 0.0,
  "targetMoney" : 0.0 ,
  "winStatus" : '',
  "Profit"  : 0.0 
 }
-->


<!-- 
มีค่า Profit = 0 พอดี 
R_10 ที่ 30 นาที เงิน = 30USD

{"contractID":299465918488,"contractType":"CALL","asset":"R_10","amount":30,"duration":"30-m","purhaseTimeStamp":1763249397,"purhaseTimeDisplay":"16/11/2568 06:29:57","expiryTimeStamp":0,"expiryTimeDisplay":"","entrySpot":0,"exitSpot":0,"targetMoney":0,"winStatus":"","Profit":0,"ProfitList":[{"timestamp":1763249396,"current_Spot":5444.502,"SpotDiff":null,"profit":27.68},{"timestamp":1763249398,"entrySpot":5444.465,"current_Spot":5444.465,"SpotDiff":0,"profit":-1.52},{"timestamp":1763249400,"entrySpot":5444.465,"current_Spot":5444.674,"SpotDiff":0.20899999999983265,"profit":-0.34},{"timestamp":1763249402,"entrySpot":5444.465,"current_Spot":5444.636,"SpotDiff":0.1710000000002765,"profit":-0.55},{"timestamp":1763249404,"entrySpot":5444.465,"current_Spot":5444.419,"SpotDiff":-0.046000000000276486,"profit":-1.78},{"timestamp":1763249406,"entrySpot":5444.465,"current_Spot":5444.56,"SpotDiff":0.09500000000025466,"profit":-0.98},{"timestamp":1763249408,"entrySpot":5444.465,"current_Spot":5444.733,"SpotDiff":0.2680000000000291,"profit":0},{"timestamp":1763249410,"entrySpot":5444.465,"current_Spot":5444.511,"SpotDiff":0.046000000000276486,"profit":-1.26},{"timestamp":1763249412,"entrySpot":5444.465,"current_Spot":5444.565,"SpotDiff":0.0999999999994543,"profit":-0.95},{"timestamp":1763249414,"entrySpot":5444.465,"current_Spot":5444.607,"SpotDiff":0.14199999999982538,"profit":-0.71},{"timestamp":1763249416,"entrySpot":5444.465,"current_Spot":5444.571,"SpotDiff":0.10599999999976717,"profit":-0.92},{"timestamp":1763249418,"entrySpot":5444.465,"current_Spot":5444.818,"SpotDiff":0.3530000000000655,"profit":0.49},{"timestamp":1763249420,"entrySpot":5444.465,"current_Spot":5445.018,"SpotDiff":0.5529999999998836,"profit":0.49}]}

-->

/*
profitAnaly {"contractid":303286855948,"profit":0.02,"entry_spot":5737.418,"current_spot":5737.704,"spotDiff":0.2860000000000582,"actionType":""}
derivUtil.js?ver=1135:145 profitAnaly {"contractid":303286854888,"profit":0.01,"entry_spot":5737.339,"current_spot":5737.065,"spotDiff":-0.27400000000034197,"actionType":""}
derivUtil.js?ver=1135:145 profitAnaly {"contractid":303286854888,"profit":0.05,"entry_spot":5737.339,"current_spot":5737.046,"spotDiff":-0.2929999999996653,"actionType":""}


{"contractid":303286854888,"profit":0.05,"entry_spot":5737.339,"current_spot":5737.046,"spotDiff":-0.2929999999996653,"actionType":""}

{"contractid":303286854888,"profit":0.05,"entry_spot":5737.339,"current_spot":5737.046,"spotDiff":-0.2929999999996653,"actionType":""}

profitAnaly {"contractid":303287681248,"symbol":"R_100","moneyTrade":"10","profit":0.04,"entry_spot":758.64,"current_spot":758.24,"spotDiff":"-0.4000","actionType":"<span class=\"contract-type \">PUT</span>"}
derivUtil.js?ver=3937:159 profitAnaly {"contractid":303287681248,"symbol":"R_100","moneyTrade":"10","profit":0,"entry_spot":758.64,"current_spot":758.27,"spotDiff":"-0.3700","actionType":"<span class=\"contract-type \">PUT</span>"}
derivUtil.js?ver=3937:159 profitAnaly {"contractid":303287681248,"symbol":"R_100","moneyTrade":"10","profit":0,"entry_spot":758.64,"current_spot":758.27,"spotDiff":"-0.3700","actionType":"<span class=\"contract-type \">PUT</span>"}



[[[[[[[{"contractid":303288456908,"symbol":"R_25","moneyTrade":"10","profit":0,"entry_spot":2463.354,"current_spot":2463.655,"spotDiff":"0.3010","actionType":"<span class=\"contract-type \">CALL</span>"}],{"contractid":303288944128,"symbol":"R_10","moneyTrade":"10","profit":0,"spotDiff":"-0.2810","entry_spot":5745.772,"current_spot":5745.491,"actionType":"PUT"}],{"contractid":303288944128,"symbol":"R_10","moneyTrade":"10","profit":0.06,"spotDiff":"-0.3150","entry_spot":5745.772,"current_spot":5745.457,"actionType":"PUT"}],{"contractid":303288944128,"symbol":"R_10","moneyTrade":"10","profit":0.05,"spotDiff":"-0.3110","entry_spot":5745.772,"current_spot":5745.461,"actionType":"PUT"}],{"contractid":303288944128,"symbol":"R_10","moneyTrade":"10","profit":0.1,"spotDiff":"-0.3340","entry_spot":5745.772,"current_spot":5745.438,"actionType":"PUT"}],{"contractid":303288944128,"symbol":"R_10","moneyTrade":"10","profit":0.07,"spotDiff":"-0.3180","entry_spot":5745.772,"current_spot":5745.454,"actionType":"PUT"}],{"contractid":303288944128,"symbol":"R_10","moneyTrade":"10","profit":0.05,"spotDiff":"-0.3090","entry_spot":5745.772,"current_spot":5745.463,"actionType":"PUT"}]

R_25->0,SPOTDIFF = 0.3010

*/


 }
<?php

?>
