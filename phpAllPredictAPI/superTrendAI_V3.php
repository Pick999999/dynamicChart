<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volume SuperTrend AI Indicator</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
	<script src="js/clsDerivDataFetcher.js?ver=<?=rand(0,10000);?>"></script>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1e222d;
            color: #d1d4dc;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        #chart {
            width: 100%;
            height: 600px;
            background: #131722;
            margin-bottom: 20px;
        }
        .controls {
            background: #2a2e39;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .control-group {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
        }
        label {
            display: inline-block;
            margin-right: 8px;
            font-size: 14px;
        }
        input, select {
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #434651;
            background: #1e222d;
            color: #d1d4dc;
        }
        button {
            padding: 8px 16px;
            background: #2962ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #1e53e5;
        }
        .analysis-section {
            background: #2a2e39;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .analysis-section h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2962ff;
        }
        .analysis-section h3 {
            margin-top: 20px;
            margin-bottom: 10px;
            color: #d1d4dc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #131722;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #2a2e39;
        }
        th {
            background: #1e222d;
            color: #2962ff;
            font-weight: 600;
        }
        tr:hover {
            background: #1e222d;
        }
        .signal-bullish {
            color: #00FF00;
            font-weight: bold;
        }
        .signal-bearish {
            color: #FF0000;
            font-weight: bold;
        }
        .trend-up {
            background: rgba(0, 255, 0, 0.05);
        }
        .trend-down {
            background: rgba(255, 0, 0, 0.05);
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #131722;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2962ff;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #787b86;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
        }
        .summary-card.bullish {
            border-left-color: #00FF00;
        }
        .summary-card.bearish {
            border-left-color: #FF0000;
        }
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Volume SuperTrend AI Indicator</h1>
        
        <div class="controls">
            <div class="control-group">
                <label>Neighbors (k):</label>
                <input type="number" id="k" value="3" min="1" max="100">
            </div>
            <div class="control-group">
                <label>Data (n):</label>
                <input type="number" id="n" value="10" min="1" max="100">
            </div>
            <div class="control-group">
                <label>Price Trend:</label>
                <input type="number" id="priceLen" value="20" min="2" max="500">
            </div>
            <div class="control-group">
                <label>Prediction Trend:</label>
                <input type="number" id="stLen" value="100" min="2" max="500">
            </div>
            <div class="control-group">
                <label>ST Length:</label>
                <input type="number" id="len" value="10" min="1" max="100">
            </div>
            <div class="control-group">
                <label>ST Factor:</label>
                <input type="number" id="factor" value="3.0" min="0.1" max="10" step="0.1">
            </div>
            <div class="control-group">
                <label>MA Type:</label>
                <select id="maType">
                    <option value="SMA">SMA</option>
                    <option value="EMA">EMA</option>
                    <option value="WMA" selected>WMA</option>
                    <option value="RMA">RMA</option>
                    <option value="VWMA">VWMA</option>
                </select>
            </div>
            <div class="control-group">
                <button onclick="updateIndicator()">Update Indicator</button>
            </div>
        </div>
        
        <div id="chart"></div>
        
        <div class="analysis-section" id="analysisSection">
            <h2>📊 AI SuperTrend Analysis</h2>
            
            <div class="summary-cards">
                <div class="summary-card bullish">
                    <h3>Bullish Signals</h3>
                    <div class="value signal-bullish" id="bullishCount">0</div>
                </div>
                <div class="summary-card bearish">
                    <h3>Bearish Signals</h3>
                    <div class="value signal-bearish" id="bearishCount">0</div>
                </div>
                <div class="summary-card">
                    <h3>Current Trend</h3>
                    <div class="value" id="currentTrend">-</div>
                </div>
                <div class="summary-card">
                    <h3>Trend Duration</h3>
                    <div class="value" id="trendDuration">0 bars</div>
                </div>
            </div>
            
            <h3>Signal History</h3>
            <div class="table-container">
                <table id="signalTable">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Signal Type</th>
                            <th>Price</th>
                            <th>SuperTrend</th>
                            <th>Direction</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="signalTableBody">
                        <tr><td colspan="6" style="text-align: center; padding: 20px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            
            <h3>Trend Periods</h3>
            <div class="table-container">
                <table id="trendTable">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Trend</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration (bars)</th>
                            <th>Price Change</th>
                            <th>Change %</th>
                        </tr>
                    </thead>
                    <tbody id="trendTableBody">
                        <tr><td colspan="7" style="text-align: center; padding: 20px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<div class="trading-guide">
    <style>
        .trading-guide {
            background: #2a2e39;
            padding: 20px;
            border-radius: 8px;
            color: #d1d4dc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .trading-guide h2 {
            color: #2962ff;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .trading-guide h3 {
            color: #d1d4dc;
            margin-top: 20px;
            margin-bottom: 12px;
            font-size: 18px;
        }
        .guide-section {
            background: #131722;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #2962ff;
        }
        .guide-section.bullish {
            border-left-color: #00FF00;
        }
        .guide-section.bearish {
            border-left-color: #FF0000;
        }
        .guide-section.warning {
            border-left-color: #FFA726;
        }
        .signal-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 4px;
        }
        .signal-icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
            text-align: center;
        }
        .signal-content {
            flex: 1;
        }
        .signal-title {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .signal-desc {
            font-size: 14px;
            color: #787b86;
        }
        .action-tag {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .action-tag.call {
            background: rgba(0, 255, 0, 0.2);
            color: #00FF00;
        }
        .action-tag.put {
            background: rgba(255, 0, 0, 0.2);
            color: #FF0000;
        }
        .action-tag.wait {
            background: rgba(255, 167, 38, 0.2);
            color: #FFA726;
        }
        .tips-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .tips-list li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        .tips-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2962ff;
            font-weight: bold;
        }
    </style>

    <h2>📚 คู่มือการเทรดด้วย Volume SuperTrend AI</h2>

    <!-- เส้นสี -->
    <h3>🎨 เส้นสี (SuperTrend Line)</h3>
    
    <div class="signal-item">
        <div class="signal-icon">━━━</div>
        <div class="signal-content">
            <div class="signal-title" style="color: #00FF00;">เส้นสีเขียว (Bullish Trend)</div>
            <div class="signal-desc">แนวโน้มขึ้น - ราคาอยู่เหนือเส้น SuperTrend</div>
        </div>
        <span class="action-tag call">พิจารณา CALL</span>
    </div>

    <div class="signal-item">
        <div class="signal-icon">━━━</div>
        <div class="signal-content">
            <div class="signal-title" style="color: #FF0000;">เส้นสีแดง (Bearish Trend)</div>
            <div class="signal-desc">แนวโน้มลง - ราคาอยู่ใต้เส้น SuperTrend</div>
        </div>
        <span class="action-tag put">พิจารณา PUT</span>
    </div>

    <div class="guide-section warning" style="margin-top: 15px;">
        <strong style="color: #FFA726;">📏 เส้นวิ่งในแนวราบ (Sideways/Flat)</strong>
        <div style="margin-top: 10px; font-size: 14px; line-height: 1.6;">
            <div style="margin-bottom: 8px;">
                <strong>หมายถึง:</strong> ตลาดอยู่ในภาวะ <strong>Consolidation</strong> หรือ <strong>Ranging</strong> 
                (ไม่มีแนวโน้มชัดเจน แกว่งตัวในช่วงราคา)
            </div>
            <div style="margin-bottom: 8px;">
                <strong style="color: #FFA726;">⚠️ คำแนะนำ:</strong>
                <ul class="tips-list" style="margin: 5px 0;">
                    <li><strong>ระมัดระวัง!</strong> อย่าเทรดตามเส้นในช่วงนี้ (มักเป็น false signal)</li>
                    <li>รอให้เส้นเริ่มทะยานขึ้น หรือดิ่งลง อย่างชัดเจนก่อน</li>
                    <li>หรือรอ <strong>Breakout</strong> จากช่วงราคา พร้อมลูกศรยืนยัน</li>
                    <li>ใช้กลยุทธ์ <strong>Range Trading</strong> แทน (ซื้อที่ Support/ขายที่ Resistance)</li>
                </ul>
            </div>
            <div style="padding: 8px; background: rgba(255, 167, 38, 0.1); border-radius: 4px;">
                💡 <strong>จำง่ายๆ:</strong> เส้นราบ = ตลาดสับสน → <span class="action-tag wait">รอดูก่อน</span>
            </div>
        </div>
    </div>

    <!-- จุดกลม -->
    <h3>🔴🟢 จุดกลม (Trend Start)</h3>
    
    <div class="signal-item">
        <div class="signal-icon">🟢</div>
        <div class="signal-content">
            <div class="signal-title" style="color: #00FF00;">จุดกลมเขียว - Bullish Trend Start</div>
            <div class="signal-desc">AI ตรวจพบการเริ่มต้นแนวโน้มขึ้น - เตรียมตัวเข้า CALL</div>
        </div>
        <span class="action-tag call">เตรียมตัว CALL</span>
    </div>

    <div class="signal-item">
        <div class="signal-icon">🔴</div>
        <div class="signal-content">
            <div class="signal-title" style="color: #FF0000;">จุดกลมแดง - Bearish Trend Start</div>
            <div class="signal-desc">AI ตรวจพบการเริ่มต้นแนวโน้มลง - เตรียมตัวเข้า PUT</div>
        </div>
        <span class="action-tag put">เตรียมตัว PUT</span>
    </div>

    <!-- ลูกศร -->
    <h3>▲▼ ลูกศร (Strong Signals)</h3>
    
    <div class="guide-section bullish">
        <div class="signal-item" style="background: transparent; padding: 0;">
            <div class="signal-icon">▲</div>
            <div class="signal-content">
                <div class="signal-title" style="color: #00FF00;">ลูกศรขึ้น - Bullish Signal</div>
                <div class="signal-desc">
                    สัญญาณยืนยัน Bullish แรง - SuperTrend กลับมาเป็น Support + AI ทำนายแนวโน้มขึ้น
                </div>
            </div>
            <span class="action-tag call">✅ เข้า CALL</span>
        </div>
    </div>

    <div class="guide-section bearish">
        <div class="signal-item" style="background: transparent; padding: 0;">
            <div class="signal-icon">▼</div>
            <div class="signal-content">
                <div class="signal-title" style="color: #FF0000;">ลูกศรลง - Bearish Signal</div>
                <div class="signal-desc">
                    สัญญาณยืนยัน Bearish แรง - SuperTrend กลับมาเป็น Resistance + AI ทำนายแนวโน้มลง
                </div>
            </div>
            <span class="action-tag put">✅ เข้า PUT</span>
        </div>
    </div>

    <!-- แนะนำการเทรด -->
    <h3>💡 แนะนำการเทรด</h3>
    
    <div class="guide-section bullish">
        <strong style="color: #00FF00;">✅ เมื่อไหร่ควรเข้าเทรด</strong>
        <ul class="tips-list">
            <li>เห็นลูกศร ▲ หรือ ▼ (สัญญาณแรงที่สุด มีการยืนยันจาก AI + SuperTrend)</li>
            <li>เส้นสีชัดเจน และราคายังเคลื่อนไหวไปในทิศทางเดียวกัน</li>
            <li>Volume สูง (ถ้ามี) แสดงถึงความเชื่อมั่นของตลาด</li>
        </ul>
    </div>

    <div class="guide-section warning">
        <strong style="color: #FFA726;">⚠️ ระวัง - อย่าเข้าเทรดเมื่อ</strong>
        <ul class="tips-list">
            <li>เส้นกำลังเปลี่ยนสี (อาจเป็น false signal หรือ choppy market)</li>
            <li>เห็นแค่จุดกลม แต่ยังไม่มีลูกศรยืนยัน</li>
            <li>ราคาอยู่ใกล้เส้น SuperTrend มาก (อาจเกิดการกลับตัว)</li>
            <li>Timeframe เล็ก + ตลาดผันผวนมาก</li>
        </ul>
    </div>

    <div class="guide-section bearish">
        <strong style="color: #FF0000;">🚪 เมื่อไหร่ควรออกจากเทรด</strong>
        <ul class="tips-list">
            <li>เส้น SuperTrend เปลี่ยนสี (จากเขียวเป็นแดง หรือ แดงเป็นเขียว)</li>
            <li>เห็นลูกศรฝั่งตรงข้าม (เช่น กำลังถือ CALL แล้วเห็น ▼)</li>
            <li>ราคาทะลุเส้น SuperTrend ไปอีกด้าน</li>
            <li>ถึง Target Profit หรือ Stop Loss ที่วางไว้</li>
        </ul>
    </div>

    <div class="guide-section">
        <strong style="color: #2962ff;">📊 สรุป Priority ของสัญญาณ</strong>
        <div style="margin-top: 10px; line-height: 2;">
            <div>1. <strong style="color: #00FF00;">ลูกศร ▲▼</strong> = สัญญาณแข็งแรงที่สุด ✅ (เข้าเทรด)</div>
            <div>2. <strong style="color: #FFA726;">จุดกลม 🟢🔴</strong> = เตือนล่วงหน้า ⚠️ (เตรียมตัว)</div>
            <div>3. <strong style="color: #787b86;">เส้นสี</strong> = บอกทิศทาง 📈📉 (Follow Trend)</div>
        </div>
    </div>

    <div style="background: rgba(41, 98, 255, 0.1); padding: 12px; border-radius: 4px; margin-top: 20px; border-left: 4px solid #2962ff;">
        <strong>💡 Tips:</strong> ใช้ร่วมกับการวิเคราะห์อื่นๆ เช่น Support/Resistance, Price Action, หรือ Volume เพื่อความแม่นยำสูงสุด
    </div>
</div>
<textarea id="dataCandle" >
[{"time":1760889660,"open":1022.71,"high":1023.47,"low":1021.89,"close":1022.67,"volume":1},{"time":1760889720,"open":1022.87,"high":1023.53,"low":1022.25,"close":1023.49,"volume":1},{"time":1760889780,"open":1023.75,"high":1026.22,"low":1023.27,"close":1025.3,"volume":1},{"time":1760889840,"open":1025.53,"high":1026.44,"low":1024.73,"close":1026.44,"volume":1},{"time":1760889900,"open":1026.36,"high":1027.15,"low":1026.04,"close":1026.64,"volume":1},{"time":1760889960,"open":1026.57,"high":1027.95,"low":1025.98,"close":1026.93,"volume":1},{"time":1760890020,"open":1027.05,"high":1027.87,"low":1026.16,"close":1027.46,"volume":1},{"time":1760890080,"open":1027.14,"high":1027.84,"low":1026.48,"close":1027.72,"volume":1},{"time":1760890140,"open":1028.03,"high":1028.07,"low":1026.37,"close":1026.92,"volume":1},{"time":1760890200,"open":1027.02,"high":1029.91,"low":1027.02,"close":1029.5,"volume":1},{"time":1760890260,"open":1030.21,"high":1031.56,"low":1029.88,"close":1031.56,"volume":1},{"time":1760890320,"open":1031.21,"high":1032.82,"low":1031.13,"close":1031.54,"volume":1},{"time":1760890380,"open":1031.73,"high":1033.75,"low":1030.98,"close":1033.74,"volume":1},{"time":1760890440,"open":1033.92,"high":1034.31,"low":1032.85,"close":1033.32,"volume":1},{"time":1760890500,"open":1033.04,"high":1033.23,"low":1030.83,"close":1031.54,"volume":1},{"time":1760890560,"open":1031.09,"high":1031.09,"low":1029.91,"close":1030.44,"volume":1},{"time":1760890620,"open":1030.48,"high":1031.12,"low":1029.98,"close":1030.7,"volume":1},{"time":1760890680,"open":1030.89,"high":1030.89,"low":1028.94,"close":1029.3,"volume":1},{"time":1760890740,"open":1029.25,"high":1030.77,"low":1029.14,"close":1030.77,"volume":1},{"time":1760890800,"open":1030.55,"high":1031.23,"low":1029.42,"close":1031.23,"volume":1},{"time":1760890860,"open":1031.04,"high":1032.25,"low":1030.56,"close":1031.91,"volume":1},{"time":1760890920,"open":1032.02,"high":1032.46,"low":1030.73,"close":1032.11,"volume":1},{"time":1760890980,"open":1032.06,"high":1032.79,"low":1031.7,"close":1032.05,"volume":1},{"time":1760891040,"open":1031.95,"high":1031.95,"low":1029.02,"close":1029.02,"volume":1},{"time":1760891100,"open":1029.22,"high":1029.22,"low":1026.71,"close":1026.71,"volume":1},{"time":1760891160,"open":1026.83,"high":1027.93,"low":1026.66,"close":1027.63,"volume":1},{"time":1760891220,"open":1027.97,"high":1029.87,"low":1027.78,"close":1029.59,"volume":1},{"time":1760891280,"open":1029.9,"high":1032.58,"low":1029.9,"close":1032.57,"volume":1},{"time":1760891340,"open":1032.32,"high":1033.04,"low":1030.93,"close":1031.27,"volume":1},{"time":1760891400,"open":1031.36,"high":1032.18,"low":1030.65,"close":1031.7,"volume":1},{"time":1760891460,"open":1031.4,"high":1031.89,"low":1030.22,"close":1030.81,"volume":1},{"time":1760891520,"open":1030.74,"high":1032.33,"low":1030.74,"close":1031.7,"volume":1},{"time":1760891580,"open":1031.81,"high":1032.46,"low":1030.89,"close":1031.16,"volume":1},{"time":1760891640,"open":1031.49,"high":1031.49,"low":1030.04,"close":1030.43,"volume":1},{"time":1760891700,"open":1030.03,"high":1031.33,"low":1029.98,"close":1030.39,"volume":1},{"time":1760891760,"open":1029.92,"high":1030.51,"low":1029.48,"close":1030.23,"volume":1},{"time":1760891820,"open":1029.97,"high":1030.75,"low":1027.46,"close":1027.49,"volume":1},{"time":1760891880,"open":1028.13,"high":1028.97,"low":1027.69,"close":1028.58,"volume":1},{"time":1760891940,"open":1028.27,"high":1029.76,"low":1028.13,"close":1029.76,"volume":1},{"time":1760892000,"open":1029.7,"high":1030.58,"low":1029.23,"close":1029.71,"volume":1},{"time":1760892060,"open":1029.75,"high":1031.22,"low":1029.29,"close":1029.59,"volume":1},{"time":1760892120,"open":1029.25,"high":1031.43,"low":1029.25,"close":1031.13,"volume":1},{"time":1760892180,"open":1031.4,"high":1033.78,"low":1031.39,"close":1032.57,"volume":1},{"time":1760892240,"open":1032.67,"high":1032.67,"low":1029.92,"close":1030.29,"volume":1},{"time":1760892300,"open":1030.29,"high":1030.55,"low":1029.26,"close":1029.86,"volume":1},{"time":1760892360,"open":1030.19,"high":1031.28,"low":1029.48,"close":1030.44,"volume":1},{"time":1760892420,"open":1029.93,"high":1030.71,"low":1028.14,"close":1028.14,"volume":1},{"time":1760892480,"open":1027.77,"high":1027.77,"low":1024.81,"close":1025.6,"volume":1},{"time":1760892540,"open":1025.75,"high":1026.53,"low":1024.71,"close":1026.53,"volume":1},{"time":1760892600,"open":1026.19,"high":1027.72,"low":1026.19,"close":1026.98,"volume":1},{"time":1760892660,"open":1026.93,"high":1027.32,"low":1025.94,"close":1026.72,"volume":1},{"time":1760892720,"open":1026.54,"high":1026.91,"low":1024.22,"close":1024.84,"volume":1},{"time":1760892780,"open":1024.5,"high":1025.42,"low":1023.59,"close":1025.26,"volume":1},{"time":1760892840,"open":1025.44,"high":1026.99,"low":1025.44,"close":1026.13,"volume":1},{"time":1760892900,"open":1026.15,"high":1026.15,"low":1022.29,"close":1022.31,"volume":1},{"time":1760892960,"open":1022.29,"high":1023.56,"low":1020.95,"close":1020.95,"volume":1},{"time":1760893020,"open":1020.83,"high":1024.14,"low":1020.83,"close":1023.67,"volume":1},{"time":1760893080,"open":1023.91,"high":1025.42,"low":1023.22,"close":1023.22,"volume":1},{"time":1760893140,"open":1022.99,"high":1024.24,"low":1022.3,"close":1023.9,"volume":1},{"time":1760893200,"open":1024.25,"high":1025.9,"low":1023.88,"close":1023.97,"volume":1},{"time":1760893260,"open":1023.63,"high":1025.17,"low":1023.43,"close":1024.95,"volume":1},{"time":1760893320,"open":1025.18,"high":1026.26,"low":1024.41,"close":1024.53,"volume":1},{"time":1760893380,"open":1024.89,"high":1025.78,"low":1024.13,"close":1024.75,"volume":1},{"time":1760893440,"open":1024.94,"high":1024.94,"low":1023.13,"close":1023.81,"volume":1},{"time":1760893500,"open":1023.95,"high":1023.95,"low":1022.59,"close":1022.59,"volume":1},{"time":1760893560,"open":1022.39,"high":1022.41,"low":1020.62,"close":1021.95,"volume":1},{"time":1760893620,"open":1021.67,"high":1022.15,"low":1020.9,"close":1021.87,"volume":1},{"time":1760893680,"open":1022.2,"high":1023.69,"low":1021.04,"close":1022.94,"volume":1},{"time":1760893740,"open":1023.08,"high":1024.36,"low":1022.07,"close":1024.25,"volume":1},{"time":1760893800,"open":1024.23,"high":1024.23,"low":1021.22,"close":1021.67,"volume":1},{"time":1760893860,"open":1021.64,"high":1022.32,"low":1021.22,"close":1021.89,"volume":1},{"time":1760893920,"open":1021.52,"high":1021.52,"low":1018.76,"close":1018.76,"volume":1},{"time":1760893980,"open":1018.83,"high":1019.72,"low":1018.14,"close":1018.98,"volume":1},{"time":1760894040,"open":1018.37,"high":1019.69,"low":1017.38,"close":1019.69,"volume":1},{"time":1760894100,"open":1019.39,"high":1019.39,"low":1015.17,"close":1016.22,"volume":1},{"time":1760894160,"open":1016.29,"high":1017.43,"low":1015.68,"close":1016.38,"volume":1},{"time":1760894220,"open":1016.75,"high":1016.76,"low":1015.31,"close":1015.31,"volume":1},{"time":1760894280,"open":1014.86,"high":1015.31,"low":1014.36,"close":1014.66,"volume":1},{"time":1760894340,"open":1014.77,"high":1015.41,"low":1014.34,"close":1015.28,"volume":1},{"time":1760894400,"open":1015.42,"high":1015.7,"low":1014.14,"close":1015.31,"volume":1},{"time":1760894460,"open":1015.25,"high":1015.25,"low":1013.28,"close":1013.85,"volume":1},{"time":1760894520,"open":1013.89,"high":1014.2,"low":1012.03,"close":1012.22,"volume":1},{"time":1760894580,"open":1012.19,"high":1012.19,"low":1010.39,"close":1010.79,"volume":1},{"time":1760894640,"open":1010.67,"high":1012.58,"low":1010.46,"close":1010.62,"volume":1},{"time":1760894700,"open":1010.61,"high":1010.98,"low":1009.21,"close":1009.22,"volume":1},{"time":1760894760,"open":1009.05,"high":1009.59,"low":1007.75,"close":1007.86,"volume":1},{"time":1760894820,"open":1007.48,"high":1009,"low":1007.17,"close":1008.54,"volume":1},{"time":1760894880,"open":1009.29,"high":1010.69,"low":1008.62,"close":1010.6,"volume":1},{"time":1760894940,"open":1010.75,"high":1010.75,"low":1006.74,"close":1006.74,"volume":1},{"time":1760895000,"open":1006.73,"high":1008.51,"low":1006.33,"close":1007.38,"volume":1},{"time":1760895060,"open":1007.03,"high":1007.2,"low":1005.11,"close":1006.81,"volume":1},{"time":1760895120,"open":1006.76,"high":1007.36,"low":1004.66,"close":1004.66,"volume":1},{"time":1760895180,"open":1004.63,"high":1006.88,"low":1004.57,"close":1006.88,"volume":1},{"time":1760895240,"open":1006.78,"high":1007.2,"low":1006.32,"close":1007.09,"volume":1},{"time":1760895300,"open":1007.4,"high":1007.4,"low":1005.49,"close":1005.68,"volume":1},{"time":1760895360,"open":1006.19,"high":1006.19,"low":1004.75,"close":1005.08,"volume":1},{"time":1760895420,"open":1004.8,"high":1006.04,"low":1004.65,"close":1004.85,"volume":1},{"time":1760895480,"open":1004.86,"high":1005.09,"low":1002.88,"close":1003.1,"volume":1},{"time":1760895540,"open":1003.06,"high":1006.01,"low":1003.06,"close":1005.42,"volume":1},{"time":1760895600,"open":1005.38,"high":1005.58,"low":1002.68,"close":1003.29,"volume":1},{"time":1760895660,"open":1003.54,"high":1005.28,"low":1003.15,"close":1004.92,"volume":1},{"time":1760895720,"open":1005.03,"high":1005.35,"low":1003.17,"close":1003.17,"volume":1},{"time":1760895780,"open":1003.08,"high":1003.69,"low":1001.18,"close":1001.29,"volume":1},{"time":1760895840,"open":1001.39,"high":1001.8,"low":998.9,"close":999.11,"volume":1},{"time":1760895900,"open":999.06,"high":1000.77,"low":998.55,"close":999.91,"volume":1},{"time":1760895960,"open":999.83,"high":1000.82,"low":999.83,"close":1000.49,"volume":1},{"time":1760896020,"open":1001.12,"high":1002.17,"low":1000.59,"close":1001.13,"volume":1},{"time":1760896080,"open":1000.99,"high":1002.46,"low":1000.99,"close":1001.75,"volume":1},{"time":1760896140,"open":1001.53,"high":1001.92,"low":1000.15,"close":1000.81,"volume":1},{"time":1760896200,"open":1000.93,"high":1001.7,"low":1000.47,"close":1000.7,"volume":1},{"time":1760896260,"open":1000.71,"high":1002.81,"low":1000.6,"close":1000.6,"volume":1},{"time":1760896320,"open":1000.47,"high":1001.04,"low":998.67,"close":999.25,"volume":1},{"time":1760896380,"open":999.37,"high":999.37,"low":997.85,"close":998.98,"volume":1},{"time":1760896440,"open":999.09,"high":1001.01,"low":998.93,"close":999.93,"volume":1},{"time":1760896500,"open":1000.21,"high":1001.16,"low":999.69,"close":1000.44,"volume":1},{"time":1760896560,"open":1000.22,"high":1001.6,"low":1000.22,"close":1001.56,"volume":1},{"time":1760896620,"open":1001.61,"high":1002.73,"low":1001.57,"close":1001.87,"volume":1},{"time":1760896680,"open":1002.36,"high":1003.69,"low":1001.42,"close":1003.53,"volume":1},{"time":1760896740,"open":1003.56,"high":1003.77,"low":1001.12,"close":1001.57,"volume":1},{"time":1760896800,"open":1001.79,"high":1002.73,"low":1001.21,"close":1002.18,"volume":1},{"time":1760896860,"open":1002.13,"high":1002.67,"low":1001.2,"close":1001.22,"volume":1},{"time":1760896920,"open":1001.32,"high":1002.81,"low":1001.23,"close":1001.76,"volume":1},{"time":1760896980,"open":1001.97,"high":1004.33,"low":1001.92,"close":1002.99,"volume":1},{"time":1760897040,"open":1002.85,"high":1002.95,"low":1002,"close":1002.02,"volume":1},{"time":1760897100,"open":1001.9,"high":1003.59,"low":1001.23,"close":1001.29,"volume":1},{"time":1760897160,"open":1001.27,"high":1001.44,"low":999.83,"close":999.95,"volume":1},{"time":1760897220,"open":1000.33,"high":1000.88,"low":999.61,"close":999.85,"volume":1},{"time":1760897280,"open":999.61,"high":999.71,"low":998.01,"close":998.17,"volume":1},{"time":1760897340,"open":998.21,"high":1000.99,"low":998.21,"close":1000.9,"volume":1},{"time":1760897400,"open":1001.02,"high":1003.61,"low":1000.59,"close":1002.67,"volume":1},{"time":1760897460,"open":1002.43,"high":1002.45,"low":1000.89,"close":1001.25,"volume":1},{"time":1760897520,"open":1001.44,"high":1003.08,"low":1000.4,"close":1003.08,"volume":1},{"time":1760897580,"open":1002.7,"high":1003.63,"low":1002.45,"close":1003.48,"volume":1},{"time":1760897640,"open":1003.44,"high":1006.49,"low":1003.44,"close":1006.49,"volume":1},{"time":1760897700,"open":1006.83,"high":1007.14,"low":1005.97,"close":1006.3,"volume":1},{"time":1760897760,"open":1006.37,"high":1007.71,"low":1005.47,"close":1005.77,"volume":1},{"time":1760897820,"open":1005.24,"high":1005.87,"low":1003.63,"close":1003.63,"volume":1},{"time":1760897880,"open":1003.4,"high":1003.4,"low":1001.73,"close":1003.3,"volume":1},{"time":1760897940,"open":1003.58,"high":1005.4,"low":1003.54,"close":1005.09,"volume":1},{"time":1760898000,"open":1005.33,"high":1005.82,"low":1002.36,"close":1002.49,"volume":1},{"time":1760898060,"open":1002.35,"high":1003.84,"low":1001.27,"close":1001.78,"volume":1},{"time":1760898120,"open":1001.86,"high":1004.63,"low":1001.52,"close":1004.17,"volume":1},{"time":1760898180,"open":1003.86,"high":1004.51,"low":1002.42,"close":1002.74,"volume":1},{"time":1760898240,"open":1002.87,"high":1005.84,"low":1002.46,"close":1005.39,"volume":1},{"time":1760898300,"open":1005.55,"high":1006.84,"low":1005.01,"close":1006.67,"volume":1},{"time":1760898360,"open":1006.65,"high":1006.66,"low":1004.76,"close":1004.76,"volume":1},{"time":1760898420,"open":1004.3,"high":1004.95,"low":1003.29,"close":1004.03,"volume":1},{"time":1760898480,"open":1004.08,"high":1005.69,"low":1004.08,"close":1004.45,"volume":1},{"time":1760898540,"open":1004.29,"high":1006.08,"low":1004.12,"close":1006.08,"volume":1},{"time":1760898600,"open":1006.21,"high":1006.26,"low":1005.15,"close":1005.99,"volume":1},{"time":1760898660,"open":1005.31,"high":1006.02,"low":1004.43,"close":1004.43,"volume":1},{"time":1760898720,"open":1004.26,"high":1004.39,"low":1001.98,"close":1003,"volume":1},{"time":1760898780,"open":1002.84,"high":1003.84,"low":1002.35,"close":1002.53,"volume":1},{"time":1760898840,"open":1002.66,"high":1003.51,"low":1002.32,"close":1003.07,"volume":1},{"time":1760898900,"open":1002.89,"high":1003.79,"low":1001.69,"close":1002.5,"volume":1},{"time":1760898960,"open":1002.77,"high":1003.37,"low":1002,"close":1002.19,"volume":1},{"time":1760899020,"open":1002.27,"high":1003.37,"low":1001.09,"close":1001.09,"volume":1},{"time":1760899080,"open":1001.01,"high":1001.26,"low":1000.24,"close":1000.31,"volume":1},{"time":1760899140,"open":1000.36,"high":1000.36,"low":998.61,"close":999.41,"volume":1},{"time":1760899200,"open":999.07,"high":1000.54,"low":999.07,"close":1000.54,"volume":1},{"time":1760899260,"open":1000.31,"high":1000.61,"low":998.24,"close":999.24,"volume":1},{"time":1760899320,"open":998.97,"high":999.41,"low":998.16,"close":998.54,"volume":1},{"time":1760899380,"open":998.54,"high":998.95,"low":998.09,"close":998.73,"volume":1},{"time":1760899440,"open":998.91,"high":1000.42,"low":998.75,"close":1000.27,"volume":1},{"time":1760899500,"open":1000.14,"high":1000.87,"low":999.24,"close":999.92,"volume":1},{"time":1760899560,"open":999.79,"high":1000.26,"low":998.89,"close":999.93,"volume":1},{"time":1760899620,"open":999.94,"high":999.94,"low":996.41,"close":997.2,"volume":1},{"time":1760899680,"open":997.32,"high":999.87,"low":997,"close":999.87,"volume":1},{"time":1760899740,"open":999.72,"high":1000.35,"low":997.6,"close":997.6,"volume":1},{"time":1760899800,"open":997.66,"high":997.8,"low":995.22,"close":995.48,"volume":1},{"time":1760899860,"open":995.5,"high":995.72,"low":993.77,"close":994.43,"volume":1},{"time":1760899920,"open":994.54,"high":996.29,"low":994.43,"close":995,"volume":1},{"time":1760899980,"open":995.21,"high":995.85,"low":994.17,"close":994.18,"volume":1},{"time":1760900040,"open":994.52,"high":995.98,"low":994.1,"close":994.1,"volume":1},{"time":1760900100,"open":994.24,"high":994.67,"low":992.6,"close":992.6,"volume":1},{"time":1760900160,"open":992.53,"high":994.15,"low":992.53,"close":993.43,"volume":1},{"time":1760900220,"open":993.49,"high":994.95,"low":993.08,"close":994.35,"volume":1},{"time":1760900280,"open":994.31,"high":995.12,"low":993.38,"close":994.46,"volume":1},{"time":1760900340,"open":994.34,"high":994.72,"low":993.48,"close":994.39,"volume":1},{"time":1760900400,"open":994.3,"high":994.34,"low":992.81,"close":993.27,"volume":1},{"time":1760900460,"open":993.78,"high":993.78,"low":992.3,"close":992.32,"volume":1},{"time":1760900520,"open":992.89,"high":992.94,"low":991.35,"close":991.35,"volume":1},{"time":1760900580,"open":991.83,"high":991.94,"low":990.89,"close":991.94,"volume":1},{"time":1760900640,"open":992.13,"high":992.13,"low":991,"close":991.86,"volume":1},{"time":1760900700,"open":992.09,"high":994.07,"low":991.3,"close":994.07,"volume":1},{"time":1760900760,"open":993.95,"high":995.06,"low":992.74,"close":994.76,"volume":1},{"time":1760900820,"open":994.72,"high":995.51,"low":994.36,"close":995.2,"volume":1},{"time":1760900880,"open":995.08,"high":999.28,"low":995.08,"close":999.26,"volume":1},{"time":1760900940,"open":999.36,"high":1001.11,"low":998.91,"close":1001.11,"volume":1},{"time":1760901000,"open":1001.07,"high":1002.83,"low":1000.25,"close":1000.25,"volume":1},{"time":1760901060,"open":1000.36,"high":1000.36,"low":998.1,"close":999.22,"volume":1},{"time":1760901120,"open":999.4,"high":1000.37,"low":998.76,"close":998.98,"volume":1},{"time":1760901180,"open":998.95,"high":1001.11,"low":998.81,"close":1001.11,"volume":1},{"time":1760901240,"open":1001.05,"high":1001.94,"low":1000.42,"close":1001.52,"volume":1},{"time":1760901300,"open":1001.33,"high":1001.33,"low":999.3,"close":999.4,"volume":1},{"time":1760901360,"open":999.72,"high":1001.07,"low":999.38,"close":1000.87,"volume":1},{"time":1760901420,"open":1000.91,"high":1002.68,"low":1000.91,"close":1001.34,"volume":1},{"time":1760901480,"open":1000.93,"high":1002.14,"low":999.82,"close":1001.73,"volume":1},{"time":1760901540,"open":1001.59,"high":1003.08,"low":1001.46,"close":1002.24,"volume":1},{"time":1760901600,"open":1002.09,"high":1003.23,"low":1001.58,"close":1002.69,"volume":1},{"time":1760901660,"open":1002.84,"high":1005.43,"low":1002.84,"close":1005.39,"volume":1},{"time":1760901720,"open":1005.48,"high":1006.27,"low":1005.01,"close":1006.27,"volume":1},{"time":1760901780,"open":1006.64,"high":1006.96,"low":1004.93,"close":1005.58,"volume":1},{"time":1760901840,"open":1005.74,"high":1007.4,"low":1005.04,"close":1007.4,"volume":1},{"time":1760901900,"open":1007.74,"high":1008.65,"low":1007.41,"close":1008.22,"volume":1},{"time":1760901960,"open":1008.48,"high":1009.68,"low":1008.11,"close":1009.68,"volume":1},{"time":1760902020,"open":1009.52,"high":1009.52,"low":1007.54,"close":1008.87,"volume":1},{"time":1760902080,"open":1008.74,"high":1010.54,"low":1008.37,"close":1009.9,"volume":1},{"time":1760902140,"open":1010.1,"high":1010.47,"low":1008.82,"close":1009.5,"volume":1},{"time":1760902200,"open":1009.4,"high":1011.3,"low":1009.4,"close":1011.11,"volume":1},{"time":1760902260,"open":1011.32,"high":1011.32,"low":1007.85,"close":1007.85,"volume":1},{"time":1760902320,"open":1007.97,"high":1008.87,"low":1006.52,"close":1008.87,"volume":1},{"time":1760902380,"open":1008.73,"high":1010.74,"low":1008.73,"close":1010.6,"volume":1},{"time":1760902440,"open":1010.83,"high":1011.74,"low":1010.83,"close":1011.66,"volume":1},{"time":1760902500,"open":1011.72,"high":1012.12,"low":1010.84,"close":1012.07,"volume":1},{"time":1760902560,"open":1011.9,"high":1012.66,"low":1011.54,"close":1012.54,"volume":1},{"time":1760902620,"open":1012.05,"high":1013.36,"low":1011.59,"close":1012.09,"volume":1},{"time":1760902680,"open":1012.37,"high":1012.66,"low":1011.16,"close":1011.59,"volume":1},{"time":1760902740,"open":1011.5,"high":1013.43,"low":1011.5,"close":1012.73,"volume":1},{"time":1760902800,"open":1012.77,"high":1013.41,"low":1011.63,"close":1012,"volume":1},{"time":1760902860,"open":1012.15,"high":1013.31,"low":1012.15,"close":1012.92,"volume":1},{"time":1760902920,"open":1012.49,"high":1012.83,"low":1011.85,"close":1012.83,"volume":1},{"time":1760902980,"open":1013.03,"high":1014.52,"low":1012.7,"close":1013.94,"volume":1},{"time":1760903040,"open":1014,"high":1016.39,"low":1013.93,"close":1016.05,"volume":1},{"time":1760903100,"open":1016.07,"high":1018.17,"low":1015.96,"close":1018.16,"volume":1},{"time":1760903160,"open":1018.07,"high":1018.07,"low":1016.92,"close":1017.32,"volume":1},{"time":1760903220,"open":1017.59,"high":1018.64,"low":1017.59,"close":1017.63,"volume":1},{"time":1760903280,"open":1017.69,"high":1020.94,"low":1017.65,"close":1020.79,"volume":1},{"time":1760903340,"open":1020.75,"high":1021.2,"low":1020.07,"close":1020.28,"volume":1},{"time":1760903400,"open":1020.09,"high":1022.84,"low":1020.03,"close":1021.75,"volume":1},{"time":1760903460,"open":1021.66,"high":1023.08,"low":1020.91,"close":1022.76,"volume":1},{"time":1760903520,"open":1022.81,"high":1023.36,"low":1021.32,"close":1021.39,"volume":1},{"time":1760903580,"open":1021.35,"high":1022.07,"low":1020.58,"close":1021.77,"volume":1},{"time":1760903640,"open":1021.57,"high":1023.69,"low":1021.51,"close":1022.68,"volume":1},{"time":1760903700,"open":1022.56,"high":1024.71,"low":1022.56,"close":1024.12,"volume":1},{"time":1760903760,"open":1024.08,"high":1025.31,"low":1024.08,"close":1024.85,"volume":1},{"time":1760903820,"open":1024.76,"high":1025.67,"low":1024.03,"close":1025.57,"volume":1},{"time":1760903880,"open":1025.37,"high":1025.98,"low":1024.7,"close":1024.92,"volume":1},{"time":1760903940,"open":1025.1,"high":1025.87,"low":1024,"close":1024,"volume":1},{"time":1760904000,"open":1024.22,"high":1025.02,"low":1023.19,"close":1023.7,"volume":1},{"time":1760904060,"open":1023.77,"high":1023.98,"low":1022.02,"close":1023.98,"volume":1},{"time":1760904120,"open":1024.29,"high":1025.08,"low":1023.16,"close":1024.28,"volume":1},{"time":1760904180,"open":1023.9,"high":1025.11,"low":1023.61,"close":1024.99,"volume":1},{"time":1760904240,"open":1025.23,"high":1025.23,"low":1023.55,"close":1024.05,"volume":1},{"time":1760904300,"open":1023.55,"high":1024.76,"low":1023.47,"close":1024.76,"volume":1},{"time":1760904360,"open":1025,"high":1027.67,"low":1025,"close":1027.39,"volume":1},{"time":1760904420,"open":1027.44,"high":1029.14,"low":1027.03,"close":1028.93,"volume":1},{"time":1760904480,"open":1028.66,"high":1028.66,"low":1026.96,"close":1027.32,"volume":1},{"time":1760904540,"open":1027.12,"high":1027.12,"low":1025.29,"close":1025.38,"volume":1},{"time":1760904600,"open":1024.5,"high":1025.6,"low":1024.5,"close":1024.6,"volume":1},{"time":1760904660,"open":1024.73,"high":1024.85,"low":1023.13,"close":1024.04,"volume":1},{"time":1760904720,"open":1023.93,"high":1026.09,"low":1023.53,"close":1025.08,"volume":1},{"time":1760904780,"open":1025.23,"high":1027.12,"low":1024.86,"close":1027.08,"volume":1},{"time":1760904840,"open":1027.06,"high":1027.19,"low":1025.08,"close":1025.08,"volume":1},{"time":1760904900,"open":1024.99,"high":1026.19,"low":1024.76,"close":1025.2,"volume":1},{"time":1760904960,"open":1025.2,"high":1026.17,"low":1024.91,"close":1025.42,"volume":1},{"time":1760905020,"open":1025.62,"high":1025.85,"low":1023.34,"close":1023.34,"volume":1},{"time":1760905080,"open":1023.96,"high":1024.48,"low":1021.8,"close":1023.66,"volume":1},{"time":1760905140,"open":1023.54,"high":1024.67,"low":1022.98,"close":1023.14,"volume":1},{"time":1760905200,"open":1023.56,"high":1023.56,"low":1021.78,"close":1022.39,"volume":1},{"time":1760905260,"open":1022.33,"high":1023.89,"low":1022.33,"close":1023.56,"volume":1},{"time":1760905320,"open":1023.93,"high":1024.91,"low":1023.31,"close":1023.9,"volume":1},{"time":1760905380,"open":1024.3,"high":1025.1,"low":1023.81,"close":1023.84,"volume":1},{"time":1760905440,"open":1023.84,"high":1025.98,"low":1023.84,"close":1025.09,"volume":1},{"time":1760905500,"open":1025.44,"high":1025.44,"low":1023.19,"close":1023.24,"volume":1},{"time":1760905560,"open":1023.49,"high":1024.4,"low":1022.99,"close":1023.44,"volume":1},{"time":1760905620,"open":1023.9,"high":1023.9,"low":1021.15,"close":1021.15,"volume":1},{"time":1760905680,"open":1021.44,"high":1022.1,"low":1021.22,"close":1021.57,"volume":1},{"time":1760905740,"open":1021.95,"high":1023.56,"low":1021.95,"close":1023.05,"volume":1},{"time":1760905800,"open":1023.38,"high":1023.38,"low":1021.14,"close":1021.68,"volume":1},{"time":1760905860,"open":1021.32,"high":1023.75,"low":1020.97,"close":1023.74,"volume":1},{"time":1760905920,"open":1023.57,"high":1024.13,"low":1022.3,"close":1022.83,"volume":1},{"time":1760905980,"open":1022.99,"high":1022.99,"low":1021.45,"close":1021.64,"volume":1},{"time":1760906040,"open":1021.75,"high":1022.44,"low":1021.14,"close":1021.48,"volume":1},{"time":1760906100,"open":1021.23,"high":1021.3,"low":1020.24,"close":1020.69,"volume":1},{"time":1760906160,"open":1021.07,"high":1022,"low":1020.63,"close":1021.31,"volume":1},{"time":1760906220,"open":1021.24,"high":1023.62,"low":1021.12,"close":1023.16,"volume":1},{"time":1760906280,"open":1023.29,"high":1023.76,"low":1022.88,"close":1023.52,"volume":1},{"time":1760906340,"open":1023.48,"high":1023.93,"low":1022.18,"close":1023.8,"volume":1},{"time":1760906400,"open":1023.73,"high":1023.81,"low":1022.86,"close":1023.81,"volume":1},{"time":1760906460,"open":1023.61,"high":1023.82,"low":1021.81,"close":1021.81,"volume":1},{"time":1760906520,"open":1021.7,"high":1021.84,"low":1020.3,"close":1020.74,"volume":1},{"time":1760906580,"open":1020.77,"high":1022.3,"low":1019.45,"close":1021.94,"volume":1},{"time":1760906640,"open":1021.81,"high":1023.8,"low":1021.12,"close":1023.8,"volume":1},{"time":1760906700,"open":1023.48,"high":1023.57,"low":1022.34,"close":1022.61,"volume":1},{"time":1760906760,"open":1022.71,"high":1023.26,"low":1021.88,"close":1022.92,"volume":1},{"time":1760906820,"open":1022.69,"high":1022.69,"low":1021.06,"close":1022.64,"volume":1},{"time":1760906880,"open":1022.64,"high":1023.88,"low":1022.3,"close":1022.32,"volume":1},{"time":1760906940,"open":1022.7,"high":1023.71,"low":1022.2,"close":1023.45,"volume":1},{"time":1760907000,"open":1023.38,"high":1024.67,"low":1023.13,"close":1024.06,"volume":1},{"time":1760907060,"open":1023.75,"high":1025.01,"low":1023.42,"close":1025.01,"volume":1},{"time":1760907120,"open":1024.74,"high":1025.86,"low":1024.42,"close":1025.42,"volume":1},{"time":1760907180,"open":1025.63,"high":1026.29,"low":1025.3,"close":1026.19,"volume":1},{"time":1760907240,"open":1026.12,"high":1026.73,"low":1025.25,"close":1026.73,"volume":1},{"time":1760907300,"open":1026.66,"high":1028.32,"low":1026.15,"close":1027.98,"volume":1},{"time":1760907360,"open":1028,"high":1028.28,"low":1025.66,"close":1025.81,"volume":1},{"time":1760907420,"open":1025.98,"high":1026.39,"low":1024.63,"close":1025.53,"volume":1},{"time":1760907480,"open":1025.46,"high":1026.79,"low":1025.46,"close":1026.79,"volume":1},{"time":1760907540,"open":1026.8,"high":1027.54,"low":1026.21,"close":1026.75,"volume":1},{"time":1760907600,"open":1026.91,"high":1029.16,"low":1026.91,"close":1028.06,"volume":1},{"time":1760907660,"open":1027.68,"high":1027.68,"low":1026.34,"close":1026.38,"volume":1},{"time":1760907720,"open":1026.24,"high":1026.56,"low":1024.94,"close":1026.45,"volume":1},{"time":1760907780,"open":1026.09,"high":1026.09,"low":1025.12,"close":1025.2,"volume":1},{"time":1760907840,"open":1025.54,"high":1025.54,"low":1023.85,"close":1025.23,"volume":1},{"time":1760907900,"open":1025.44,"high":1025.52,"low":1024.57,"close":1024.78,"volume":1},{"time":1760907960,"open":1025.13,"high":1027.97,"low":1025.02,"close":1027.97,"volume":1},{"time":1760908020,"open":1028.33,"high":1028.62,"low":1026.87,"close":1028.11,"volume":1},{"time":1760908080,"open":1028.07,"high":1028.31,"low":1026.52,"close":1026.64,"volume":1},{"time":1760908140,"open":1026.47,"high":1026.67,"low":1023.66,"close":1023.82,"volume":1},{"time":1760908200,"open":1023.49,"high":1023.49,"low":1020.39,"close":1020.79,"volume":1},{"time":1760908260,"open":1020.2,"high":1020.2,"low":1017.93,"close":1018.18,"volume":1},{"time":1760908320,"open":1017.89,"high":1018.18,"low":1016.75,"close":1017.02,"volume":1},{"time":1760908380,"open":1017.2,"high":1018.52,"low":1017.1,"close":1017.53,"volume":1},{"time":1760908440,"open":1017.71,"high":1020.74,"low":1017.71,"close":1020.72,"volume":1},{"time":1760908500,"open":1020.84,"high":1022.89,"low":1020.4,"close":1021.95,"volume":1},{"time":1760908560,"open":1022.17,"high":1022.75,"low":1021.01,"close":1022.4,"volume":1},{"time":1760908620,"open":1022.8,"high":1023.54,"low":1021.92,"close":1022.73,"volume":1},{"time":1760908680,"open":1022.72,"high":1023.69,"low":1022.46,"close":1022.65,"volume":1},{"time":1760908740,"open":1022.72,"high":1025.76,"low":1022.31,"close":1025.61,"volume":1},{"time":1760908800,"open":1025.67,"high":1026.16,"low":1024.52,"close":1025.44,"volume":1},{"time":1760908860,"open":1025.17,"high":1025.49,"low":1023.39,"close":1023.39,"volume":1},{"time":1760908920,"open":1023.37,"high":1024.31,"low":1022.62,"close":1023.64,"volume":1},{"time":1760908980,"open":1023.49,"high":1025.15,"low":1021.79,"close":1023.85,"volume":1},{"time":1760909040,"open":1023.49,"high":1024.12,"low":1022.38,"close":1022.64,"volume":1},{"time":1760909100,"open":1022.95,"high":1023.38,"low":1019.51,"close":1019.88,"volume":1},{"time":1760909160,"open":1020.05,"high":1022.96,"low":1019.96,"close":1022.57,"volume":1},{"time":1760909220,"open":1022.73,"high":1023.88,"low":1022.73,"close":1023.6,"volume":1},{"time":1760909280,"open":1023.79,"high":1024.55,"low":1022.86,"close":1023,"volume":1},{"time":1760909340,"open":1022.76,"high":1024.52,"low":1022.68,"close":1022.68,"volume":1},{"time":1760909400,"open":1023.11,"high":1026.14,"low":1023.11,"close":1026.07,"volume":1},{"time":1760909460,"open":1026.76,"high":1028.46,"low":1026.68,"close":1028.11,"volume":1},{"time":1760909520,"open":1027.66,"high":1027.94,"low":1025.92,"close":1025.92,"volume":1},{"time":1760909580,"open":1025.89,"high":1026.73,"low":1024.94,"close":1026.73,"volume":1},{"time":1760909640,"open":1026.74,"high":1027.53,"low":1026.14,"close":1026.14,"volume":1},{"time":1760909700,"open":1026.19,"high":1027.88,"low":1026.18,"close":1027.05,"volume":1},{"time":1760909760,"open":1026.99,"high":1026.99,"low":1025.36,"close":1026.63,"volume":1},{"time":1760909820,"open":1026.48,"high":1026.48,"low":1025.38,"close":1026.14,"volume":1},{"time":1760909880,"open":1026.56,"high":1027.03,"low":1026.1,"close":1026.45,"volume":1},{"time":1760909940,"open":1026.06,"high":1026.62,"low":1025.73,"close":1026.22,"volume":1},{"time":1760910000,"open":1026.34,"high":1026.69,"low":1025.43,"close":1026.35,"volume":1},{"time":1760910060,"open":1026.13,"high":1026.26,"low":1024.59,"close":1024.64,"volume":1},{"time":1760910120,"open":1024.94,"high":1026.08,"low":1024.1,"close":1024.89,"volume":1},{"time":1760910180,"open":1024.55,"high":1024.55,"low":1021.01,"close":1021.43,"volume":1},{"time":1760910240,"open":1021.13,"high":1022.63,"low":1020.94,"close":1020.94,"volume":1},{"time":1760910300,"open":1021.12,"high":1024.14,"low":1021.08,"close":1024.14,"volume":1},{"time":1760910360,"open":1024.21,"high":1027.19,"low":1023.9,"close":1026.86,"volume":1},{"time":1760910420,"open":1027.03,"high":1028.17,"low":1026.73,"close":1028.17,"volume":1},{"time":1760910480,"open":1028.17,"high":1028.56,"low":1027.19,"close":1028.13,"volume":1},{"time":1760910540,"open":1027.82,"high":1027.88,"low":1025.88,"close":1026.21,"volume":1},{"time":1760910600,"open":1026.15,"high":1027.66,"low":1026.15,"close":1027.59,"volume":1},{"time":1760910660,"open":1026.99,"high":1027.86,"low":1026.38,"close":1026.38,"volume":1},{"time":1760910720,"open":1026.43,"high":1026.85,"low":1026.03,"close":1026.61,"volume":1},{"time":1760910780,"open":1026.32,"high":1026.32,"low":1023.52,"close":1023.68,"volume":1},{"time":1760910840,"open":1023.54,"high":1027.21,"low":1023.27,"close":1027.21,"volume":1},{"time":1760910900,"open":1027.11,"high":1027.14,"low":1025.02,"close":1025.02,"volume":1},{"time":1760910960,"open":1024.89,"high":1026.17,"low":1024.89,"close":1025.67,"volume":1},{"time":1760911020,"open":1025.67,"high":1025.72,"low":1024.06,"close":1024.7,"volume":1},{"time":1760911080,"open":1024.97,"high":1025.71,"low":1023.33,"close":1023.33,"volume":1},{"time":1760911140,"open":1023.31,"high":1024.29,"low":1022.48,"close":1022.79,"volume":1},{"time":1760911200,"open":1023,"high":1023.86,"low":1022.33,"close":1022.71,"volume":1},{"time":1760911260,"open":1022.29,"high":1023.81,"low":1022.29,"close":1023.81,"volume":1},{"time":1760911320,"open":1024.29,"high":1025.18,"low":1023.01,"close":1023.08,"volume":1},{"time":1760911380,"open":1023.41,"high":1024.24,"low":1022.99,"close":1023.05,"volume":1},{"time":1760911440,"open":1022.95,"high":1024.17,"low":1022.64,"close":1022.72,"volume":1},{"time":1760911500,"open":1022.52,"high":1022.52,"low":1020.12,"close":1020.14,"volume":1},{"time":1760911560,"open":1020.07,"high":1020.69,"low":1018.94,"close":1020.07,"volume":1},{"time":1760911620,"open":1020.33,"high":1021.24,"low":1018.99,"close":1019.11,"volume":1},{"time":1760911680,"open":1019.27,"high":1019.9,"low":1017.88,"close":1018.39,"volume":1},{"time":1760911740,"open":1019.12,"high":1020.72,"low":1019.01,"close":1019.96,"volume":1},{"time":1760911800,"open":1019.65,"high":1020.14,"low":1018.95,"close":1019.01,"volume":1},{"time":1760911860,"open":1018.95,"high":1019.22,"low":1016.82,"close":1017.93,"volume":1},{"time":1760911920,"open":1017.67,"high":1019.13,"low":1017.67,"close":1018.37,"volume":1},{"time":1760911980,"open":1018.72,"high":1020.33,"low":1018.22,"close":1019.21,"volume":1},{"time":1760912040,"open":1018.99,"high":1020.49,"low":1018.99,"close":1019.58,"volume":1},{"time":1760912100,"open":1019.65,"high":1020.67,"low":1018.37,"close":1020.67,"volume":1},{"time":1760912160,"open":1020.79,"high":1020.92,"low":1018.92,"close":1019.64,"volume":1},{"time":1760912220,"open":1019.44,"high":1021.69,"low":1019.44,"close":1020.72,"volume":1},{"time":1760912280,"open":1020.73,"high":1021.06,"low":1018.99,"close":1019.23,"volume":1},{"time":1760912340,"open":1019.28,"high":1020.36,"low":1018.86,"close":1019.89,"volume":1},{"time":1760912400,"open":1019.45,"high":1020.5,"low":1018.75,"close":1019.4,"volume":1},{"time":1760912460,"open":1019.51,"high":1020.42,"low":1018.68,"close":1020.08,"volume":1},{"time":1760912520,"open":1019.49,"high":1019.73,"low":1018.83,"close":1019.01,"volume":1},{"time":1760912580,"open":1019.3,"high":1020.9,"low":1017.96,"close":1020.52,"volume":1},{"time":1760912640,"open":1020.86,"high":1022.28,"low":1020.09,"close":1021.84,"volume":1},{"time":1760912700,"open":1021.91,"high":1021.91,"low":1020.03,"close":1020.96,"volume":1},{"time":1760912760,"open":1021.13,"high":1022.39,"low":1020.81,"close":1021.73,"volume":1},{"time":1760912820,"open":1021.37,"high":1023.82,"low":1020.84,"close":1023.37,"volume":1},{"time":1760912880,"open":1023.28,"high":1025.99,"low":1022.94,"close":1025.93,"volume":1},{"time":1760912940,"open":1025.92,"high":1025.92,"low":1022.66,"close":1022.66,"volume":1},{"time":1760913000,"open":1023.09,"high":1024.27,"low":1022.75,"close":1023.25,"volume":1},{"time":1760913060,"open":1023.13,"high":1023.32,"low":1022.35,"close":1022.85,"volume":1},{"time":1760913120,"open":1022.87,"high":1023.68,"low":1020.53,"close":1020.84,"volume":1},{"time":1760913180,"open":1021.04,"high":1021.98,"low":1020.44,"close":1021.98,"volume":1},{"time":1760913240,"open":1021.97,"high":1022.62,"low":1021.26,"close":1021.79,"volume":1},{"time":1760913300,"open":1022.14,"high":1022.14,"low":1018.92,"close":1018.92,"volume":1},{"time":1760913360,"open":1018.96,"high":1019.5,"low":1016.99,"close":1017.03,"volume":1},{"time":1760913420,"open":1017,"high":1018.05,"low":1016.2,"close":1017.86,"volume":1},{"time":1760913480,"open":1017.77,"high":1019.79,"low":1017.48,"close":1019.34,"volume":1},{"time":1760913540,"open":1019.5,"high":1021.05,"low":1019.5,"close":1020.39,"volume":1},{"time":1760913600,"open":1020.37,"high":1020.54,"low":1018.23,"close":1018.23,"volume":1},{"time":1760913660,"open":1018.15,"high":1020.09,"low":1017.55,"close":1019.18,"volume":1},{"time":1760913720,"open":1019.42,"high":1020.28,"low":1018.76,"close":1020.28,"volume":1},{"time":1760913780,"open":1020.26,"high":1021.23,"low":1019.29,"close":1019.46,"volume":1},{"time":1760913840,"open":1019.5,"high":1021.65,"low":1018.73,"close":1021.65,"volume":1},{"time":1760913900,"open":1021.96,"high":1023.54,"low":1021.61,"close":1023.54,"volume":1},{"time":1760913960,"open":1023.4,"high":1023.4,"low":1019.98,"close":1019.98,"volume":1},{"time":1760914020,"open":1019.61,"high":1019.61,"low":1017.85,"close":1018.72,"volume":1},{"time":1760914080,"open":1019.04,"high":1019.97,"low":1017.99,"close":1019.97,"volume":1},{"time":1760914140,"open":1019.93,"high":1019.93,"low":1017.8,"close":1017.94,"volume":1},{"time":1760914200,"open":1018.47,"high":1019.49,"low":1017.81,"close":1019.49,"volume":1},{"time":1760914260,"open":1019.42,"high":1019.94,"low":1017.81,"close":1019.94,"volume":1},{"time":1760914320,"open":1019.97,"high":1021.18,"low":1019.72,"close":1021.13,"volume":1},{"time":1760914380,"open":1021.04,"high":1021.37,"low":1020.07,"close":1020.85,"volume":1},{"time":1760914440,"open":1021,"high":1021.83,"low":1020.75,"close":1021.52,"volume":1},{"time":1760914500,"open":1021.86,"high":1023.83,"low":1021.5,"close":1023.67,"volume":1},{"time":1760914560,"open":1023.8,"high":1024.55,"low":1022.42,"close":1023.24,"volume":1},{"time":1760914620,"open":1023.19,"high":1024.45,"low":1022.87,"close":1024.05,"volume":1},{"time":1760914680,"open":1024.01,"high":1025.69,"low":1024.01,"close":1025.39,"volume":1},{"time":1760914740,"open":1025.43,"high":1025.58,"low":1024.31,"close":1024.85,"volume":1},{"time":1760914800,"open":1024.97,"high":1027.57,"low":1024.97,"close":1027.21,"volume":1},{"time":1760914860,"open":1027.22,"high":1028.2,"low":1027.04,"close":1027.69,"volume":1},{"time":1760914920,"open":1027.55,"high":1028.24,"low":1027.05,"close":1027.76,"volume":1},{"time":1760914980,"open":1027.94,"high":1028.69,"low":1027.16,"close":1027.16,"volume":1},{"time":1760915040,"open":1027.13,"high":1029.47,"low":1027.13,"close":1028.85,"volume":1},{"time":1760915100,"open":1028.68,"high":1031.13,"low":1028.4,"close":1030.99,"volume":1},{"time":1760915160,"open":1030.52,"high":1031.81,"low":1030.11,"close":1030.11,"volume":1},{"time":1760915220,"open":1030.12,"high":1031.29,"low":1029.69,"close":1030.98,"volume":1},{"time":1760915280,"open":1030.92,"high":1032.13,"low":1029.71,"close":1031.62,"volume":1},{"time":1760915340,"open":1031.64,"high":1034.52,"low":1031.36,"close":1034.52,"volume":1},{"time":1760915400,"open":1034.78,"high":1035.14,"low":1032.94,"close":1034.93,"volume":1},{"time":1760915460,"open":1034.32,"high":1035.41,"low":1032.81,"close":1034.88,"volume":1},{"time":1760915520,"open":1034.98,"high":1037.17,"low":1034.7,"close":1036.62,"volume":1},{"time":1760915580,"open":1036.87,"high":1037.29,"low":1035.42,"close":1036.22,"volume":1},{"time":1760915640,"open":1036.31,"high":1037.08,"low":1035.8,"close":1036.98,"volume":1},{"time":1760915700,"open":1037.08,"high":1037.08,"low":1035.37,"close":1035.37,"volume":1},{"time":1760915760,"open":1035.53,"high":1035.99,"low":1034.68,"close":1035.76,"volume":1},{"time":1760915820,"open":1035.85,"high":1037.31,"low":1035.85,"close":1036.54,"volume":1},{"time":1760915880,"open":1036.86,"high":1038.5,"low":1036.25,"close":1036.25,"volume":1},{"time":1760915940,"open":1036.11,"high":1037.37,"low":1035.71,"close":1036.26,"volume":1},{"time":1760916000,"open":1036.16,"high":1036.52,"low":1035.23,"close":1035.43,"volume":1},{"time":1760916060,"open":1035.74,"high":1036.62,"low":1035.2,"close":1035.83,"volume":1},{"time":1760916120,"open":1035.89,"high":1037.86,"low":1035.89,"close":1037.51,"volume":1},{"time":1760916180,"open":1037.37,"high":1038.99,"low":1036.97,"close":1038.99,"volume":1},{"time":1760916240,"open":1038.76,"high":1038.82,"low":1037.28,"close":1038.01,"volume":1},{"time":1760916300,"open":1037.62,"high":1037.82,"low":1036.15,"close":1036.19,"volume":1},{"time":1760916360,"open":1035.93,"high":1038.39,"low":1035.93,"close":1038.34,"volume":1},{"time":1760916420,"open":1038.61,"high":1039.61,"low":1037.52,"close":1038.98,"volume":1},{"time":1760916480,"open":1039.56,"high":1039.82,"low":1038.27,"close":1038.27,"volume":1},{"time":1760916540,"open":1038.08,"high":1039.19,"low":1038.08,"close":1038.83,"volume":1},{"time":1760916600,"open":1039.11,"high":1040.06,"low":1038.8,"close":1039.83,"volume":1},{"time":1760916660,"open":1039.92,"high":1040.38,"low":1038.52,"close":1039.56,"volume":1},{"time":1760916720,"open":1039.66,"high":1040.02,"low":1038.7,"close":1040.02,"volume":1},{"time":1760916780,"open":1040.09,"high":1040.09,"low":1037.89,"close":1038.14,"volume":1},{"time":1760916840,"open":1038.2,"high":1038.42,"low":1037.1,"close":1037.1,"volume":1},{"time":1760916900,"open":1037.67,"high":1038.82,"low":1037.06,"close":1037.62,"volume":1},{"time":1760916960,"open":1037.32,"high":1037.95,"low":1036.19,"close":1036.22,"volume":1},{"time":1760917020,"open":1036.38,"high":1036.38,"low":1034.85,"close":1034.91,"volume":1},{"time":1760917080,"open":1035.01,"high":1037.01,"low":1034.91,"close":1037.01,"volume":1},{"time":1760917140,"open":1037.08,"high":1038.62,"low":1036.28,"close":1037.4,"volume":1},{"time":1760917200,"open":1037.06,"high":1037.42,"low":1036.17,"close":1036.71,"volume":1},{"time":1760917260,"open":1036.22,"high":1037.22,"low":1035.26,"close":1037.22,"volume":1},{"time":1760917320,"open":1037.09,"high":1037.44,"low":1036.62,"close":1036.85,"volume":1},{"time":1760917380,"open":1036.89,"high":1036.89,"low":1033.04,"close":1033.12,"volume":1},{"time":1760917440,"open":1033.31,"high":1034.39,"low":1032.88,"close":1033.57,"volume":1},{"time":1760917500,"open":1033.89,"high":1035.02,"low":1033.38,"close":1035.02,"volume":1},{"time":1760917560,"open":1035.01,"high":1035.64,"low":1034.55,"close":1034.93,"volume":1},{"time":1760917620,"open":1035.65,"high":1036.11,"low":1033.54,"close":1033.54,"volume":1},{"time":1760917680,"open":1033.51,"high":1033.51,"low":1032.35,"close":1032.55,"volume":1},{"time":1760917740,"open":1032.16,"high":1033.36,"low":1031.25,"close":1031.25,"volume":1},{"time":1760917800,"open":1031.19,"high":1031.19,"low":1028.77,"close":1028.95,"volume":1},{"time":1760917860,"open":1028.89,"high":1028.89,"low":1025.55,"close":1025.55,"volume":1},{"time":1760917920,"open":1025.31,"high":1026.68,"low":1023.83,"close":1024.79,"volume":1},{"time":1760917980,"open":1024.8,"high":1025.75,"low":1023.59,"close":1023.59,"volume":1},{"time":1760918040,"open":1023.68,"high":1025.22,"low":1023.2,"close":1023.2,"volume":1},{"time":1760918100,"open":1022.85,"high":1024.97,"low":1022.85,"close":1023.67,"volume":1},{"time":1760918160,"open":1023.68,"high":1023.97,"low":1022.59,"close":1023.35,"volume":1},{"time":1760918220,"open":1023.28,"high":1023.54,"low":1022.55,"close":1022.55,"volume":1},{"time":1760918280,"open":1022.06,"high":1022.65,"low":1021.5,"close":1022.65,"volume":1},{"time":1760918340,"open":1023.02,"high":1023.42,"low":1021.88,"close":1022.56,"volume":1},{"time":1760918400,"open":1022.72,"high":1024.77,"low":1022.29,"close":1024.77,"volume":1},{"time":1760918460,"open":1024.76,"high":1026.07,"low":1024.7,"close":1025.14,"volume":1},{"time":1760918520,"open":1025.19,"high":1025.93,"low":1024.08,"close":1025.7,"volume":1},{"time":1760918580,"open":1025.88,"high":1027.71,"low":1025.26,"close":1027.51,"volume":1},{"time":1760918640,"open":1027.28,"high":1027.28,"low":1025.57,"close":1025.94,"volume":1},{"time":1760918700,"open":1025.73,"high":1026.15,"low":1023.89,"close":1024.16,"volume":1},{"time":1760918760,"open":1023.94,"high":1023.94,"low":1021.85,"close":1023.22,"volume":1},{"time":1760918820,"open":1023.27,"high":1026.82,"low":1023.27,"close":1026.82,"volume":1},{"time":1760918880,"open":1026.58,"high":1026.73,"low":1025.06,"close":1025.8,"volume":1},{"time":1760918940,"open":1025.7,"high":1028.71,"low":1024.17,"close":1028.59,"volume":1},{"time":1760919000,"open":1028.89,"high":1031.61,"low":1028.61,"close":1030.41,"volume":1},{"time":1760919060,"open":1030.56,"high":1030.56,"low":1026.82,"close":1027.16,"volume":1},{"time":1760919120,"open":1027.32,"high":1028.21,"low":1027.14,"close":1027.53,"volume":1},{"time":1760919180,"open":1028.08,"high":1028.18,"low":1026.9,"close":1027.68,"volume":1},{"time":1760919240,"open":1027.7,"high":1028.87,"low":1027.7,"close":1028.26,"volume":1},{"time":1760919300,"open":1028.09,"high":1030.16,"low":1028.09,"close":1030.16,"volume":1},{"time":1760919360,"open":1030.38,"high":1032.05,"low":1029.95,"close":1032.05,"volume":1},{"time":1760919420,"open":1032.08,"high":1032.08,"low":1029.87,"close":1030.12,"volume":1},{"time":1760919480,"open":1030.35,"high":1030.76,"low":1029.34,"close":1029.96,"volume":1},{"time":1760919540,"open":1030.32,"high":1031.14,"low":1029.14,"close":1029.62,"volume":1},{"time":1760919600,"open":1029.46,"high":1031.66,"low":1029.46,"close":1031.58,"volume":1}]
</textarea>
    <script>
        // Volume SuperTrend AI Class
        class VolumeSuperTrendAI {
            constructor(options = {}) {
                this.k = options.k || 3;
                this.n_ = options.n_ || 10;
                this.n = Math.max(this.k, this.n_);
                this.KNN_PriceLen = options.KNN_PriceLen || 20;
                this.KNN_STLen = options.KNN_STLen || 100;
                this.len = options.len || 10;
                this.factor = options.factor || 3.0;
                this.maSrc = options.maSrc || 'WMA';
                this.upCol = options.upCol || '#00FF00';
                this.dnCol = options.dnCol || '#FF0000';
                this.neCol = options.neCol || '#0000FF';
                this.bars = [];
                this.results = [];
            }
            
            sma(data, period) {
                if (data.length < period) return null;
                let sum = 0;
                for (let i = 0; i < period; i++) {
                    sum += data[i];
                }
                return sum / period;
            }
            
            ema(data, period) {
                if (data.length < period) return null;
                const multiplier = 2 / (period + 1);
                let ema = this.sma(data.slice(0, period), period);
                for (let i = period; i < data.length; i++) {
                    ema = (data[i] - ema) * multiplier + ema;
                }
                return ema;
            }
            
            wma(data, period) {
                if (data.length < period) return null;
                let weightedSum = 0;
                let weightTotal = 0;
                for (let i = 0; i < period; i++) {
                    const weight = period - i;
                    weightedSum += data[i] * weight;
                    weightTotal += weight;
                }
                return weightedSum / weightTotal;
            }
            
            rma(data, period) {
                if (data.length < period) return null;
                let sum = 0;
                for (let i = 0; i < period; i++) {
                    sum += data[i];
                }
                let rma = sum / period;
                for (let i = period; i < data.length; i++) {
                    rma = (rma * (period - 1) + data[i]) / period;
                }
                return rma;
            }
            
            vwma(prices, volumes, period) {
                if (prices.length < period) return null;
                let sumPV = 0;
                let sumV = 0;
                for (let i = 0; i < period; i++) {
                    sumPV += prices[i] * volumes[i];
                    sumV += volumes[i];
                }
                return sumV > 0 ? sumPV / sumV : prices[0];
            }
            
            calculateMA(prices, volumes, period, type) {
                const pv = prices.map((p, i) => p * volumes[i]);
                const v = volumes.slice(0, period);
                
                switch(type) {
                    case 'SMA':
                        return this.sma(pv, period) / this.sma(v, period);
                    case 'EMA':
                        return this.ema(pv, period) / this.ema(v, period);
                    case 'WMA':
                        return this.wma(pv, period) / this.wma(v, period);
                    case 'RMA':
                        return this.rma(pv, period) / this.rma(v, period);
                    case 'VWMA':
                        return this.vwma(prices, volumes, period);
                    default:
                        return this.wma(pv, period) / this.wma(v, period);
                }
            }
            
            calculateATR(bars, period) {
                if (bars.length < period + 1) return null;
                const trueRanges = [];
                for (let i = 1; i < bars.length; i++) {
                    const high = bars[i].high;
                    const low = bars[i].low;
                    const prevClose = bars[i - 1].close;
                    const tr = Math.max(high - low, Math.abs(high - prevClose), Math.abs(low - prevClose));
                    trueRanges.push(tr);
                }
                return this.sma(trueRanges.slice(0, period), period);
            }
            
            distance(x1, x2) {
                return Math.abs(x1 - x2);
            }
            
            knnWeighted(data, labels, k, x) {
                const n = data.length;
                const distances = [];
                for (let i = 0; i < n; i++) {
                    distances.push({ dist: this.distance(x, data[i]), label: labels[i], index: i });
                }
                distances.sort((a, b) => a.dist - b.dist);
                let weightedSum = 0;
                let totalWeight = 0;
                for (let i = 0; i < k; i++) {
                    const weight = 1 / (distances[i].dist + 1e-6);
                    weightedSum += weight * distances[i].label;
                    totalWeight += weight;
                }
                return weightedSum / totalWeight;
            }
            
            calculate(bars) {
                this.bars = bars;
                this.results = [];
                const requiredBars = Math.max(this.len, this.n, this.KNN_PriceLen, this.KNN_STLen) + 10;
                
                for (let i = 0; i < bars.length; i++) {
                    if (i < requiredBars) {
                        this.results.push({ time: bars[i].time, superTrend: null, direction: null, color: this.neCol, label: null });
                        continue;
                    }
                    
                    const historicalBars = bars.slice(Math.max(0, i - requiredBars), i + 1);
                    const closes = historicalBars.map(b => b.close);
                    const volumes = historicalBars.map(b => b.volume || 1);
                    const vwma = this.calculateMA(closes.slice(-this.len), volumes.slice(-this.len), this.len, this.maSrc);
                    const atr = this.calculateATR(historicalBars.slice(-this.len - 1), this.len);
                    
                    if (!vwma || !atr) continue;
                    
                    let upperBand = vwma + this.factor * atr;
                    let lowerBand = vwma - this.factor * atr;
                    const prev = i > 0 ? this.results[i - 1] : null;
                    const prevClose = i > 0 ? bars[i - 1].close : bars[i].close;
                    
                    if (prev && prev.superTrend !== null) {
                        const prevLowerBand = prev.lowerBand || lowerBand;
                        const prevUpperBand = prev.upperBand || upperBand;
                        lowerBand = (lowerBand > prevLowerBand || prevClose < prevLowerBand) ? lowerBand : prevLowerBand;
                        upperBand = (upperBand < prevUpperBand || prevClose > prevUpperBand) ? upperBand : prevUpperBand;
                    }
                    
                    let direction;
                    if (!prev || prev.superTrend === null) {
                        direction = 1;
                    } else if (prev.superTrend === prev.upperBand) {
                        direction = bars[i].close > upperBand ? -1 : 1;
                    } else {
                        direction = bars[i].close < lowerBand ? 1 : -1;
                    }
                    
                    const superTrend = direction === -1 ? lowerBand : upperBand;
                    const data = [];
                    const labels = [];
                    
                    for (let j = 0; j < Math.min(this.n, i); j++) {
                        const idx = i - j;
                        if (idx >= 0 && idx < this.results.length && this.results[idx] && this.results[idx].superTrend !== null) {
                            data.push(this.results[idx].superTrend);
                            const priceWMA = this.wma(bars.slice(Math.max(0, idx - this.KNN_PriceLen), idx + 1).map(b => b.close), Math.min(this.KNN_PriceLen, idx + 1));
                            const stArray = this.results.slice(Math.max(0, idx - this.KNN_STLen), idx + 1)
                                .filter(r => r && r.superTrend !== null)
                                .map(r => r.superTrend);
                            const stWMA = stArray.length > 0 ? this.wma(stArray, Math.min(this.KNN_STLen, stArray.length)) : null;
                            const label = (priceWMA && stWMA && priceWMA > stWMA) ? 1 : 0;
                            labels.push(label);
                        }
                    }
                    
                    let label = 0;
                    if (data.length >= this.k) {
                        label = this.knnWeighted(data, labels, this.k, superTrend);
                    }
                    
                    const color = label >= 0.5 ? this.upCol : this.dnCol;
                    this.results.push({ time: bars[i].time, superTrend: superTrend, direction: direction, color: color, label: label, upperBand: upperBand, lowerBand: lowerBand });
                }
                return this.results;
            }
            
            getLineData() {
                return this.results.filter(r => r.superTrend !== null).map(r => ({ time: r.time, value: r.superTrend, color: r.color }));
            }
            
            getSignals() {
                const signals = [];
                for (let i = 1; i < this.results.length; i++) {
                    const curr = this.results[i];
                    const prev = this.results[i - 1];
                    if (!curr || !prev || !curr.superTrend || !prev.superTrend) continue;
                    
                    if (curr.color === this.upCol && prev.color !== this.upCol) {
                        signals.push({ 
                            time: curr.time, 
                            position: 'belowBar', 
                            color: this.upCol, 
                            shape: 'circle', 
                            text: '🟢',
                            type: 'Bullish Trend Start',
                            price: this.bars[i].close,
                            superTrend: curr.superTrend,
                            index: i
                        });
                    }
                    if (curr.color === this.dnCol && prev.color !== this.dnCol) {
                        signals.push({ 
                            time: curr.time, 
                            position: 'aboveBar', 
                            color: this.dnCol, 
                            shape: 'circle', 
                            text: '🔴',
                            type: 'Bearish Trend Start',
                            price: this.bars[i].close,
                            superTrend: curr.superTrend,
                            index: i
                        });
                    }
                    if (curr.direction === -1 && prev.direction === 1 && curr.label >= 0.5) {
                        signals.push({ 
                            time: curr.time, 
                            position: 'belowBar', 
                            color: this.upCol, 
                            shape: 'arrowUp', 
                            text: '▲',
                            type: 'Bullish Signal',
                            price: this.bars[i].close,
                            superTrend: curr.superTrend,
                            index: i
                        });
                    }
                    if (curr.direction === 1 && prev.direction === -1 && curr.label < 0.5) {
                        signals.push({ 
                            time: curr.time, 
                            position: 'aboveBar', 
                            color: this.dnCol, 
                            shape: 'arrowDown', 
                            text: '▼',
                            type: 'Bearish Signal',
                            price: this.bars[i].close,
                            superTrend: curr.superTrend,
                            index: i
                        });
                    }
                }
                return signals;
            }
            
            getTrendPeriods() {
                const periods = [];
                let currentTrend = null;
                let trendStart = null;
                let startPrice = null;
                
                for (let i = 0; i < this.results.length; i++) {
                    const result = this.results[i];
                    if (!result || !result.superTrend) continue;
                    
                    const trend = result.color === this.upCol ? 'Bullish' : 
                                 result.color === this.dnCol ? 'Bearish' : 'Neutral';
                    
                    if (trend !== currentTrend && trend !== 'Neutral') {
                        if (currentTrend !== null && trendStart !== null) {
                            periods.push({
                                trend: currentTrend,
                                startTime: this.bars[trendStart].time,
                                endTime: this.bars[i - 1].time,
                                startIndex: trendStart,
                                endIndex: i - 1,
                                duration: i - trendStart,
                                startPrice: startPrice,
                                endPrice: this.bars[i - 1].close
                            });
                        }
                        currentTrend = trend;
                        trendStart = i;
                        startPrice = this.bars[i].close;
                    }
                }
                
                if (currentTrend !== null && trendStart !== null) {
                    const lastIdx = this.results.length - 1;
                    periods.push({
                        trend: currentTrend,
                        startTime: this.bars[trendStart].time,
                        endTime: this.bars[lastIdx].time,
                        startIndex: trendStart,
                        endIndex: lastIdx,
                        duration: lastIdx - trendStart + 1,
                        startPrice: startPrice,
                        endPrice: this.bars[lastIdx].close
                    });
                }
                
                return periods;
            }
        }

        // Generate sample data
        async function generateSampleData(numBars = 500) {
            data = [];

			//data = JSON.parse(document.getElementById("dataCandle").value);			
			//numBars = data.length-2 ;

            let basePrice = 100;
            let trend = 0.1;
            const startTime = Math.floor(Date.now() / 1000) - (numBars * 86400);
            
            for (let i = 0; i < numBars; i++) {
                const volatility = 2 + Math.random() * 3;
                const change = (Math.random() - 0.48) * volatility + trend;
                basePrice += change;
                
                if (i % 50 === 0) {
                    trend = (Math.random() - 0.5) * 0.5;
                }
                
                const open = basePrice;
                const close = basePrice + (Math.random() - 0.5) * volatility;
                const high = Math.max(open, close) + Math.random() * volatility;
                const low = Math.min(open, close) - Math.random() * volatility;
                const volume = Math.floor(1000000 + Math.random() * 5000000);
                
                data.push({
                    time: startTime + (i * 86400),
                    open: parseFloat(open.toFixed(2)),
                    high: parseFloat(high.toFixed(2)),
                    low: parseFloat(low.toFixed(2)),
                    close: parseFloat(close.toFixed(2)),
                    volume: volume
                });
            }
			console.log(data) 
  
            const fetcher = new DerivDataFetcher('66726'); // ใช้ app_id ทดสอบ            
			const derivData = await fetcher.getOHLC('R_100', 60, 500);
			const indicatorData = fetcher.convertToIndicatorFormat(derivData);
            // Validate ก่อนส่งเข้าชาร์ท
            if (indicatorData.length === 0) {
               console.error('❌ No valid data');
               return;
            }
            console.log('✅ Data ready:', indicatorData.length, 'candles');
            console.log('Sample:', indicatorData.slice(0, 3));


            return indicatorData;
			
            return data;
        }

        // Initialize chart
        let chart, candlestickSeries, superTrendSeries;
        let sampleData = generateSampleData(500);

        function initChart() {
            const chartElement = document.getElementById('chart');
            chart = LightweightCharts.createChart(chartElement, {
                width: chartElement.clientWidth,
                height: 600,
                layout: {
                    backgroundColor: '#131722',
                    textColor: '#d1d4dc',
                },
                grid: {
                    vertLines: { color: '#2B2B43' },
                    horzLines: { color: '#2B2B43' },
                },
                crosshair: {
                    mode: LightweightCharts.CrosshairMode.Normal,
                },
                rightPriceScale: {
                    borderColor: '#2B2B43',
                },
                timeScale: {
                    borderColor: '#2B2B43',
                    timeVisible: true,
                    secondsVisible: false,
                },
            });

            candlestickSeries = chart.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });

            candlestickSeries.setData(sampleData);

            superTrendSeries = chart.addLineSeries({
                color: '#2196F3',
                lineWidth: 2,
                title: 'SuperTrend AI',
            });

            chart.timeScale().fitContent();
            
            window.addEventListener('resize', () => {
                chart.applyOptions({ width: chartElement.clientWidth });
            });
        }

        function formatDate(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function getSignalDescription(type) {
            const descriptions = {
                'Bullish Trend Start': '🟢 AI detects the beginning of a bullish trend. The line color changes to green, indicating potential upward movement.',
                'Bearish Trend Start': '🔴 AI detects the beginning of a bearish trend. The line color changes to red, indicating potential downward movement.',
                'Bullish Signal': '▲ Strong bullish confirmation. SuperTrend flips to support, and AI predicts upward price movement. Consider long positions.',
                'Bearish Signal': '▼ Strong bearish confirmation. SuperTrend flips to resistance, and AI predicts downward price movement. Consider short positions.'
            };
            return descriptions[type] || type;
        }
        
        function updateAnalysisTables(indicator, signals) {
            console.log('Updating analysis tables...');
            console.log('Signals:', signals.length);
            
            // Update summary cards
            const bullishSignals = signals.filter(s => s.type.includes('Bullish')).length;
            const bearishSignals = signals.filter(s => s.type.includes('Bearish')).length;
            
            document.getElementById('bullishCount').textContent = bullishSignals;
            document.getElementById('bearishCount').textContent = bearishSignals;
            
            // Current trend
            const lastResult = indicator.results[indicator.results.length - 1];
            if (lastResult && lastResult.color) {
                const trendText = lastResult.color === '#00FF00' ? 'Bullish 📈' : 
                                 lastResult.color === '#FF0000' ? 'Bearish 📉' : 'Neutral';
                const trendColor = lastResult.color === '#00FF00' ? '#00FF00' : 
                                  lastResult.color === '#FF0000' ? '#FF0000' : '#787b86';
                document.getElementById('currentTrend').textContent = trendText;
                document.getElementById('currentTrend').style.color = trendColor;
            }
            
            // Trend duration
            const trendPeriods = indicator.getTrendPeriods();
            console.log('Trend Periods:', trendPeriods.length);
            
            if (trendPeriods.length > 0) {
                const currentPeriod = trendPeriods[trendPeriods.length - 1];
                document.getElementById('trendDuration').textContent = `${currentPeriod.duration} bars`;
            }
            
            // Populate signal table
            const signalTableBody = document.getElementById('signalTableBody');
            signalTableBody.innerHTML = '';
            
            if (signals.length === 0) {
                signalTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #787b86;">No signals detected</td></tr>';
            } else {
                const sortedSignals = [...signals].reverse();
                
                sortedSignals.forEach(signal => {
                    const row = document.createElement('tr');
                    const isBullish = signal.type.includes('Bullish');
                    row.className = isBullish ? 'trend-up' : 'trend-down';
                    
                    const description = getSignalDescription(signal.type);
                    
                    row.innerHTML = `
                        <td>${formatDate(signal.time)}</td>
                        <td class="${isBullish ? 'signal-bullish' : 'signal-bearish'}">${signal.text} ${signal.type}</td>
                        <td>${signal.price.toFixed(2)}</td>
                        <td>${signal.superTrend.toFixed(2)}</td>
                        <td class="${isBullish ? 'signal-bullish' : 'signal-bearish'}">${isBullish ? '↑ UP' : '↓ DOWN'}</td>
                        <td style="font-size: 12px;">${description}</td>
                    `;
                    signalTableBody.appendChild(row);
                });
            }
            
            // Populate trend periods table
            const trendTableBody = document.getElementById('trendTableBody');
            trendTableBody.innerHTML = '';
            
            if (trendPeriods.length === 0) {
                trendTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #787b86;">No trend periods detected</td></tr>';
            } else {
                const reversedPeriods = [...trendPeriods].reverse();
                
                reversedPeriods.forEach((period, index) => {
                    const row = document.createElement('tr');
                    const isBullish = period.trend === 'Bullish';
                    row.className = isBullish ? 'trend-up' : 'trend-down';
                    
                    const priceChange = period.endPrice - period.startPrice;
                    const changePercent = ((priceChange / period.startPrice) * 100).toFixed(2);
                    
                    row.innerHTML = `
                        <td>#${trendPeriods.length - index}</td>
                        <td class="${isBullish ? 'signal-bullish' : 'signal-bearish'}">
                            ${isBullish ? '🟢 Bullish' : '🔴 Bearish'}
                        </td>
                        <td>${formatDate(period.startTime)}</td>
                        <td>${formatDate(period.endTime)}</td>
                        <td>${period.duration}</td>
                        <td class="${priceChange >= 0 ? 'signal-bullish' : 'signal-bearish'}">
                            ${priceChange >= 0 ? '+' : ''}${priceChange.toFixed(2)}
                        </td>
                        <td class="${priceChange >= 0 ? 'signal-bullish' : 'signal-bearish'}">
                            ${priceChange >= 0 ? '+' : ''}${changePercent}%
                        </td>
                    `;
                    trendTableBody.appendChild(row);
                });
            }
            
            console.log('Analysis tables updated successfully!');
        }

        function updateIndicator() {
            console.log('Update indicator clicked');
            
            const options = {
                k: parseInt(document.getElementById('k').value),
                n_: parseInt(document.getElementById('n').value),
                KNN_PriceLen: parseInt(document.getElementById('priceLen').value),
                KNN_STLen: parseInt(document.getElementById('stLen').value),
                len: parseInt(document.getElementById('len').value),
                factor: parseFloat(document.getElementById('factor').value),
                maSrc: document.getElementById('maType').value,
                upCol: '#00FF00',
                dnCol: '#FF0000',
            };

            const indicator = new VolumeSuperTrendAI(options);
            console.log('Calculating indicator...');
            indicator.calculate(sampleData);
            
            const lineData = indicator.getLineData();
            console.log('Line data points:', lineData.length);
            superTrendSeries.setData(lineData);
            
            const signals = indicator.getSignals();
            console.log('Signals found:', signals.length);
            
            const markers = signals.map(s => ({
                time: s.time,
                position: s.position,
                color: s.color,
                shape: s.shape,
                text: s.text
            }));
            candlestickSeries.setMarkers(markers);
            
            // Update analysis tables
            updateAnalysisTables(indicator, signals);
        }

        // Initialize
        console.log('Initializing chart...');
        initChart();
        console.log('Running initial indicator calculation...');
        updateIndicator();
    </script>
</body>

</html>

