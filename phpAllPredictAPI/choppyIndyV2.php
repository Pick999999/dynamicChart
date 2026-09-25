<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choppiness Index Lab - Multi-Indicator</title>


    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { text-align: center; color: #667eea; margin-bottom: 10px; font-size: 2em; }
        .subtitle { text-align: center; color: #888; margin-bottom: 25px; font-size: 0.9em; }
        .control-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .control-group { display: flex; flex-direction: column; }
        .control-group label { font-weight: 600; margin-bottom: 5px; font-size: 0.85em; color: #555; }
        .control-group input, .control-group select, .control-group button {
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
        button:disabled { background: #ccc; cursor: not-allowed; }
        .trading-hint {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            border: 3px solid rgba(255,255,255,0.3);
        }
        .trading-hint.call { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .trading-hint.put { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .trading-hint.idle { background: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%); }
        .hint-title {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .hint-content { font-size: 1.2em; line-height: 1.8; margin-bottom: 20px; }
        .hint-details {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
        }
        .hint-detail-item {
            background: rgba(255,255,255,0.2);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }
        .hint-detail-item strong { display: block; font-size: 0.75em; margin-bottom: 5px; }
        .hint-detail-item span { font-size: 1.2em; font-weight: bold; }
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
        .status-card.choppy { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .status-card.neutral { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333; }
        .status-card.trending { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
        .status-card h3 { font-size: 0.8em; margin-bottom: 5px; }
        .status-card .value { font-size: 1.6em; font-weight: bold; }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .chart-title { font-weight: 600; margin-bottom: 10px; color: #667eea; }
        .chart-box { border: 2px solid #e0e0e0; border-radius: 8px; }
        .history-panel { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .history-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .history-list { max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
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
        .history-item.call { border-left-color: #38ef7d; }
        .history-item.put { border-left-color: #f45c43; }
        .history-item.idle { border-left-color: #95a5a6; }
        .history-icon { font-size: 1.8em; }
        .history-action { font-weight: bold; font-size: 1em; margin-bottom: 5px; }
        .history-time { font-size: 0.75em; color: #888; }
        .checkbox-group { display: flex; align-items: center; gap: 8px; }
    </style>

	
</head>

<body>
    <div class="container">
        <h1>📊 Choppiness Index Lab - Multi-Indicator</h1>
        <div class="subtitle">CI + ADX + BB Width + Color Pattern Detection</div>

        <div class="control-panel">
            <div class="control-group">
                <label>Symbol</label>
                <select id="symbolSelect">
                    <option value="R_10">Volatility 10</option>
                    <option value="R_25">Volatility 25</option>
                    <option value="R_50">Volatility 50</option>
                    <option value="R_75">Volatility 75</option>
                    <option value="R_100">Volatility 100</option>
                    <option value="1HZ10V">Vol 10 (1s)</option>
                    <option value="frxEURUSD">EUR/USD</option>
                </select>
            </div>
            <div class="control-group">
                <label>Timeframe</label>
                <select id="timeframeSelect">
                    <option value="60" selected>1 Minute</option>
                    <option value="300">5 Minutes</option>
                    <option value="900">15 Minutes</option>
                    <option value="3600" >1 Hour</option>
                    <option value="14400">4 Hours</option>
                </select>
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
                <label>&nbsp;</label>
                <button id="connectBtn">🔌 Connect</button>
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
            <div class="status-card" id="ciCard"><h3>CI</h3><div class="value" id="ciValue">--</div></div>
            <div class="status-card" id="adxCard"><h3>ADX</h3><div class="value" id="adxValue">--</div></div>
            <div class="status-card" id="bbCard"><h3>BB Width</h3><div class="value" id="bbValue">--</div></div>
            <div class="status-card" id="marketStateCard"><h3>Market</h3><div class="value" id="marketState">WAIT</div></div>
            <div class="status-card"><h3>Signal</h3><div class="value" id="tradingSignal">⏸️</div></div>
            <div class="status-card"><h3>Markers</h3><div class="value" id="markerCount">0</div></div>

			<div id='colorCard' class="status-card"><h3>Color List</h3><div class="value" id="markerCount">0</div></div>


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

        <div class="chart-container">
            <div class="chart-title">💹 Price Chart <span id="chartSymbol" style="color:#888;font-size:0.9em;float:right">No data</span></div>
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

<script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
<script src="choppiness-index.js?ver=<?=rand(0,20000);?>"></script>
<script src="js/clsAnalyEMA.js"></script>
<script src="js/clsIndicator.js"></script>


    
</body>
</html>