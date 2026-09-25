จาก ข้อมูล candle data ที่ได้จาก deriv.com 
[
{"time":1751428920,"open":101356.6507,"high":101419.4899,"low":101306.393,"close":101314.5965,"thisColor":"Red" }
]
ให้ทำการเทรด โดย ยึดหลัก ดังนี้ 
1.ให้มี textArea สำหรับรับ rawDataและตรวจสอบว่า มี field thisColor หรือไม่ ถ้าไม่มีให้ทำการสร้าง  field thisColor
2.ถ้าแท่งปัจจุบันเป็น Green ให้เทรด Green ถ้าแท่งปัจจุบันเป็น Red ให้เทรด Red
3.บันทึกผลการเทรด ด้วย winStatus ,lossContinue,winContinue
4.ถ้า lossContinue = 4 แสดงว่ามีการสลับสี หรือเป็น Sideway ให้ทำ Idle การเทรด ไป n วินาที และ รอจน
กว่า การสลับสี จะหมดไป จึงเข้าเทรดใหม่ ด้วย วิธีเดิม ในจำนวนเงินที่ เริ่มจาก martingale money เดิม เช่น
ตรงจุด lossContinue = 4 มีการเดินเงิน = 54 พอ เริ่มเทรด รอบใหม่ก็ให้ เดินเงินที่ 54 ต่อเลย
5.การเทรด มี ทั้งแบบ fix money และ martingale โดย martingale 
ให้เดินเงินในรูปแบบ 1,2,6,18,54,162
6.บันทึกผล ช่วงที่เป็นจุด lossContinue = 4 ออกมาด้วย 
7.สร้าง กราฟแสดงผลด้วย lightweightChart     <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
8.ทำ marker แสดงจุด lossContinue= 4 และจุด Idle

รู้สึกว่า ผลการเทรด ไม่ถูกต้อง
ช่วย สร้างรายงาน สรุปการเทรด เป็น table โดยมี หัวข้อ ตามนี้ 
  1.ลำดับ
  2.timestamp
  3.timeDisplay ในรูปแบบ 17:00
  4.thisColor
  5.action ได้แก่  CALL,PUT,Idle
  6.suggestColor,
  7.nextColor
  8.winStatus
  9.winContinue
  10.lossContinue
  11.profit
  12.balance
  
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบเทรด Candle Data Analysis</title>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .input-section {
            margin-bottom: 30px;
        }
        .input-section textarea {
            width: 100%;
            height: 150px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-family: monospace;
            font-size: 14px;
            resize: vertical;
        }
        .controls {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .control-group label {
            font-weight: bold;
            color: #555;
        }
        .control-group input, .control-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .results-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        .stats-panel {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .stats-panel h3 {
            margin-top: 0;
            color: #333;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .stat-value {
            font-weight: bold;
        }
        .profit { color: #28a745; }
        .loss { color: #dc3545; }
        .chart-container {
            width: 100%;
            height: 400px;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .trade-log {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .trade-entry {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .trade-entry:nth-child(even) {
            background: #f8f9fa;
        }
        .idle-periods {
            margin-top: 20px;
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        .idle-entry {
            margin: 8px 0;
            padding: 8px;
            background: white;
            border-radius: 4px;
        }
        .trade-table-section {
            margin: 30px 0;
        }
        .table-container {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 15px;
        }
        #tradeTable {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        #tradeTable th {
            background: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #0056b3;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        #tradeTable td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 13px;
        }
        #tradeTable tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        #tradeTable tbody tr:hover {
            background: #e3f2fd;
        }
        .action-call {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .action-put {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .action-idle {
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .color-green {
            background: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-win { background: #28a745; }
        .status-loss { background: #dc3545; }
        .status-idle { background: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔥 ระบบเทรด Candle Data Analysis</h1>
            <p>ระบบเทรดอัตโนมัติพร้อม Martingale Strategy & Idle Management</p>
        </div>

        <div class="input-section">
            <h3>📊 ข้อมูล Candle Data</h3>
            <textarea id="rawData" placeholder='วางข้อมูล JSON ของ candle data ที่นี่ เช่น:
[
  {"time":1751428920,"open":101356.6507,"high":101419.4899,"low":101306.393,"close":101314.5965,"thisColor":"Red"},
  {"time":1751428980,"open":101314.5965,"high":101350.2103,"low":101298.7841,"close":101342.8976,"thisColor":"Green"}
]'></textarea>
        </div>

        <div class="controls">
            <div class="control-group">
                <label for="tradingMode">โหมดเทรด:</label>
                <select id="tradingMode">
                    <option value="fix">Fix Money</option>
                    <option value="martingale">Martingale</option>
                </select>
            </div>
            <div class="control-group">
                <label for="fixAmount">จำนวนเงิน Fix:</label>
                <input type="number" id="fixAmount" value="10" min="1">
            </div>
            <div class="control-group">
                <label for="idleSeconds">Idle Time (วินาที):</label>
                <input type="number" id="idleSeconds" value="60" min="10">
            </div>
            <div class="control-group">
                <label for="winRate">Win Rate (%):</label>
                <input type="number" id="winRate" value="80" min="0" max="100">
            </div>
        </div>

        <div class="buttons">
            <button class="btn btn-primary" onclick="processData()">🚀 เริ่มการวิเคราะห์</button>
            <button class="btn btn-success" onclick="generateSampleData()">📝 สร้างข้อมูลตัวอย่าง</button>
            <button class="btn btn-warning" onclick="clearResults()">🗑️ ล้างผลลัพธ์</button>
        </div>

        <div class="results-section">
            <div class="stats-panel">
                <h3>📈 สถิติการเทรด</h3>
                <div id="tradingStats"></div>
            </div>
            <div class="stats-panel">
                <h3>💰 ผลกำไร/ขาดทุน</h3>
                <div id="profitStats"></div>
            </div>
        </div>

        <div class="chart-container">
            <div id="chart"></div>
        </div>

        <div class="idle-periods">
            <h3>⏳ ช่วง Idle Periods (Loss Continue = 4)</h3>
            <div id="idlePeriods"></div>
        </div>

        <div class="trade-table-section">
            <h3>📋 รายงานสรุปการเทรด</h3>
            <div class="table-container">
                <table id="tradeTable">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>Timestamp</th>
                            <th>Time</th>
                            <th>ThisColor</th>
                            <th>Action</th>
                            <th>SuggestColor</th>
                            <th>NextColor</th>
                            <th>WinStatus</th>
                            <th>WinContinue</th>
                            <th>LossContinue</th>
                            <th>Profit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody id="tradeTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="trade-log">
            <h3 style="padding: 15px; margin: 0; background: #f8f9fa; border-bottom: 1px solid #ddd;">📋 Log การเทรด (สำรอง)</h3>
            <div id="tradeLog"></div>
        </div>
    </div>

    <script>
        let chart;
        let candlestickSeries;
        let tradeData = [];
        let idleData = [];

        // Martingale sequence
        const martingaleSequence = [1, 2, 6, 18, 54, 162];

        function initChart() {
            const chartContainer = document.getElementById('chart');
            chartContainer.innerHTML = '';
            
            chart = LightweightCharts.createChart(chartContainer, {
                width: chartContainer.clientWidth,
                height: 400,
                layout: {
                    backgroundColor: '#ffffff',
                    textColor: '#333',
                },
                grid: {
                    vertLines: { color: '#f0f0f0' },
                    horzLines: { color: '#f0f0f0' },
                },
                crosshair: {
                    mode: LightweightCharts.CrosshairMode.Normal,
                },
                rightPriceScale: {
                    borderColor: '#cccccc',
                },
                timeScale: {
                    borderColor: '#cccccc',
                    timeVisible: true,
                    secondsVisible: true,
                },
            });

            candlestickSeries = chart.addCandlestickSeries({
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350',
            });
        }

        function addThisColorField(data) {
            console.log('🎨 เริ่มเพิ่ม thisColor field...');
            return data.map((candle, index) => {
                if (!candle.thisColor) {
                    candle.thisColor = candle.close >= candle.open ? 'Green' : 'Red';
                    console.log(`➕ เพิ่ม thisColor สำหรับ candle ${index + 1}: ${candle.thisColor}`);
                }
                return candle;
            });
        }

        function simulateTrade(prediction, actualColor, winRate) {
            const random = Math.random() * 100;
            if (prediction === actualColor) {
                return random <= winRate ? 'win' : 'loss';
            } else {
                return random <= (100 - winRate) ? 'loss' : 'win';
            }
        }

        function processData() {
            console.log('🚀 เริ่มการประมวลผลข้อมูล...');
            
            const rawData = document.getElementById('rawData').value.trim();
            const tradingMode = document.getElementById('tradingMode').value;
            const fixAmount = parseFloat(document.getElementById('fixAmount').value);
            const idleSeconds = parseInt(document.getElementById('idleSeconds').value);
            const winRate = parseFloat(document.getElementById('winRate').value);

            console.log('⚙️ การตั้งค่า:', { tradingMode, fixAmount, idleSeconds, winRate });

            if (!rawData) {
                alert('กรุณาใส่ข้อมูล candle data');
                return;
            }

            try {
                let candleData = JSON.parse(rawData);
                console.log('📊 ข้อมูล candle ดิบ:', candleData.length, 'รายการ');
                
                candleData = addThisColorField(candleData);
                console.log('🎨 เพิ่ม thisColor แล้ว');
                
                // Sort by time
                candleData.sort((a, b) => a.time - b.time);

                // Initialize trading variables
                let balance = 0;
                let lossContinue = 0;
                let winContinue = 0;
                let martingaleLevel = 0;
                let isIdle = false;
                let idleEndTime = 0;
                let currentMartingaleMoney = fixAmount;
                let currentWinStatus = 'Idle'; // เพิ่มตัวแปรนี้
                
                tradeData = [];
                idleData = [];

                console.log('🔄 เริ่มประมวลผลแต่ละ candle...');

                // Process each candle
                for (let i = 0; i < candleData.length; i++) {
                    const candle = candleData[i];
                    const currentTime = candle.time;
                    const nextCandle = candleData[i + 1];
                    
                    console.log(`📍 Candle ${i + 1}:`, {
                        time: currentTime,
                        thisColor: candle.thisColor,
                        isIdle: isIdle,
                        lossContinue: lossContinue
                    });
                    
                    // Determine action and colors
                    let action = 'Idle';
                    let suggestColor = candle.thisColor;
                    let nextColor = nextCandle ? nextCandle.thisColor : 'Unknown';
                    let profitLoss = 0;
                    let tradeAmount = 0;
                    
                    // Check if we're in idle period
                    if (isIdle && currentTime < idleEndTime) {
                        action = 'Idle';
                        console.log('⏳ อยู่ในช่วง Idle');
                    } else if (isIdle && currentTime >= idleEndTime) {
                        isIdle = false; // End idle period
                        if (idleData.length > 0) {
                            idleData[idleData.length - 1].endTime = currentTime;
                        }
                        console.log('✅ จบช่วง Idle');
                    }

                    // If not in idle, perform trade
                    if (!isIdle && nextCandle) {
                        // Suggest same color as current candle
                        suggestColor = candle.thisColor;
                        action = suggestColor === 'Green' ? 'CALL' : 'PUT';
                        
                        // Calculate trade amount
                        if (tradingMode === 'fix') {
                            tradeAmount = fixAmount;
                        } else {
                            tradeAmount = currentMartingaleMoney * martingaleSequence[martingaleLevel];
                        }

                        console.log('💰 การเทรด:', {
                            action: action,
                            suggestColor: suggestColor,
                            nextColor: nextColor,
                            amount: tradeAmount,
                            martingaleLevel: martingaleLevel
                        });

                        // ตรวจสอบผลการเทรดตามเงื่อนไขที่ถูกต้อง
                        let tradeResult;
                        if (suggestColor === nextColor) {
                            tradeResult = 'win';
                        } else {
                            tradeResult = 'loss';
                        }
                        
                        console.log('🎯 ผลการเทรด:', {
                            prediction: suggestColor,
                            actual: nextColor,
                            result: tradeResult
                        });
                        
                        // คำนวณกำไร/ขาดทุนและกำหนด winStatus
                        let currentWinStatus;
                        if (tradeResult === 'win') {
                            profitLoss = tradeAmount * 0.8; // 80% payout
                            balance += profitLoss;
                            currentWinStatus = 'Win';
                            winContinue++;
                            lossContinue = 0;
                            martingaleLevel = 0; // Reset martingale on win
                        } else {
                            profitLoss = -tradeAmount;
                            balance += profitLoss;
                            currentWinStatus = 'Loss';
                            lossContinue++;
                            winContinue = 0;
                            
                            if (tradingMode === 'martingale') {
                                martingaleLevel = Math.min(martingaleLevel + 1, martingaleSequence.length - 1);
                            }
                        }

                        console.log('📊 สถิติปัจจุบัน:', {
                            balance: balance,
                            winStatus: currentWinStatus,
                            lossContinue: lossContinue,
                            winContinue: winContinue
                        });

                        // ตรวจสอบเงื่อนไข Idle (lossContinue = 4)
                        if (lossContinue >= 4) {
                            isIdle = true;
                            idleEndTime = currentTime + idleSeconds;
                            currentMartingaleMoney = tradeAmount; // Save current martingale money
                            
                            console.log('🛑 เข้าสู่ช่วง Idle - Loss Continue = 4');
                            
                            idleData.push({
                                startTime: currentTime,
                                endTime: null, // Will be set when idle ends
                                martingaleMoney: currentMartingaleMoney,
                                price: candle.close
                            });
                            
                            lossContinue = 0; // Reset after entering idle
                            martingaleLevel = 0; // Reset martingale level during idle
                        }
                    } else {
                        // ถ้าไม่เทรด (Idle หรือไม่มี nextCandle)
                        currentWinStatus = 'Idle';
                    }

                    // Record trade entry with safety checks
                    const tradeEntry = {
                        sequence: i + 1,
                        timestamp: currentTime || 0,
                        timeDisplay: currentTime ? new Date(currentTime * 1000).toLocaleTimeString('th-TH', {hour: '2-digit', minute: '2-digit'}) : '--:--',
                        thisColor: candle.thisColor || 'Unknown',
                        action: action || 'Idle',
                        suggestColor: suggestColor || 'Unknown',
                        nextColor: nextColor || 'Unknown',
                        winStatus: currentWinStatus || 'Idle',
                        winContinue: winContinue || 0,
                        lossContinue: lossContinue || 0,
                        profit: profitLoss || 0,
                        balance: balance || 0,
                        amount: tradeAmount || 0,
                        price: candle.close || 0
                    };
                    
                    console.log(`✅ บันทึกข้อมูลเทรด ${i + 1}:`, tradeEntry);
                    tradeData.push(tradeEntry);
                }

                console.log('🏁 ประมวลผลเสร็จสิ้น - รายการเทรดทั้งหมด:', tradeData.length);
                console.log('📈 ข้อมูลเทรดทั้งหมด:', tradeData);
                console.log('⏳ ข้อมูล Idle ทั้งหมด:', idleData);

                // Update UI
                updateStats();
                updateChart(candleData);
                updateTradeLog();
                updateIdlePeriods();
                updateTradeTable();

            } catch (error) {
                console.error('❌ Error ในการประมวลผล:', error);
                console.error('📍 Error stack:', error.stack);
                alert('เกิดข้อผิดพลาดในการประมวลผลข้อมูล: ' + error.message);
            }
        }

        function updateStats() {
            console.log('📊 อัพเดทสถิติ...');
            console.log('tradeData length:', tradeData.length);
            console.log('tradeData sample:', tradeData.slice(0, 3));
            
            const totalTrades = tradeData.length;
            const winTrades = tradeData.filter(t => t.profit > 0).length;
            const lossTrades = tradeData.filter(t => t.profit < 0).length;
            const winPercentage = totalTrades > 0 ? ((winTrades / totalTrades) * 100).toFixed(2) : 0;
            const finalBalance = tradeData.length > 0 ? tradeData[tradeData.length - 1].balance : 0;
            const totalVolume = tradeData.reduce((sum, t) => sum + (t.amount || 0), 0);

            console.log('📈 สถิติที่คำนวณได้:', {
                totalTrades,
                winTrades, 
                lossTrades,
                winPercentage,
                finalBalance,
                totalVolume
            });

            document.getElementById('tradingStats').innerHTML = `
                <div class="stat-item">
                    <span>จำนวนเทรดทั้งหมด:</span>
                    <span class="stat-value">${totalTrades}</span>
                </div>
                <div class="stat-item">
                    <span>เทรดชนะ:</span>
                    <span class="stat-value profit">${winTrades}</span>
                </div>
                <div class="stat-item">
                    <span>เทรดแพ้:</span>
                    <span class="stat-value loss">${lossTrades}</span>
                </div>
                <div class="stat-item">
                    <span>เปอร์เซ็นต์ชนะ:</span>
                    <span class="stat-value">${winPercentage}%</span>
                </div>
                <div class="stat-item">
                    <span>ช่วง Idle:</span>
                    <span class="stat-value">${idleData.length}</span>
                </div>
            `;

            const finalBalanceFixed = typeof finalBalance === 'number' ? finalBalance.toFixed(2) : '0.00';
            const totalVolumeFixed = typeof totalVolume === 'number' ? totalVolume.toFixed(2) : '0.00';
            const maxBalance = tradeData.length > 0 ? Math.max(...tradeData.map(t => t.balance || 0)) : 0;
            const minBalance = tradeData.length > 0 ? Math.min(...tradeData.map(t => t.balance || 0)) : 0;

            document.getElementById('profitStats').innerHTML = `
                <div class="stat-item">
                    <span>ยอดรวม P/L:</span>
                    <span class="stat-value ${finalBalance >= 0 ? 'profit' : 'loss'}">${finalBalanceFixed}</span>
                </div>
                <div class="stat-item">
                    <span>Volume รวม:</span>
                    <span class="stat-value">${totalVolumeFixed}</span>
                </div>
                <div class="stat-item">
                    <span>กำไรสูงสุด:</span>
                    <span class="stat-value profit">${maxBalance.toFixed(2)}</span>
                </div>
                <div class="stat-item">
                    <span>ขาดทุนสูงสุด:</span>
                    <span class="stat-value loss">${minBalance.toFixed(2)}</span>
                </div>
            `;
        }

        function updateChart(candleData) {
            if (!chart) initChart();
            
            // Prepare candlestick data
            const chartData = candleData.map(candle => ({
                time: candle.time,
                open: candle.open,
                high: candle.high,
                low: candle.low,
                close: candle.close
            }));

            candlestickSeries.setData(chartData);

            // Add markers for loss continue = 4 points and idle periods
            const markers = [];
            
            // Add loss continue = 4 markers
            idleData.forEach(idle => {
                markers.push({
                    time: idle.startTime,
                    position: 'aboveBar',
                    color: '#f68410',
                    shape: 'circle',
                    text: 'Loss=4'
                });
            });

            // Add idle period markers
            idleData.forEach(idle => {
                if (idle.endTime) {
                    markers.push({
                        time: idle.endTime,
                        position: 'aboveBar',
                        color: '#2196F3',
                        shape: 'square',
                        text: 'Idle End'
                    });
                }
            });

            candlestickSeries.setMarkers(markers);
        }

        function updateTradeTable() {
            console.log('📋 อัพเดท Trade Table...');
            console.log('tradeData for table:', tradeData.length);
            
            const tableBody = document.getElementById('tradeTableBody');
            tableBody.innerHTML = '';
            
            tradeData.forEach((trade, index) => {
                console.log(`📝 สร้าง table row ${index + 1}:`, trade);
                
                const row = document.createElement('tr');
                
                // Format action display with safety
                let actionDisplay = trade.action || 'Idle';
                let actionClass = '';
                if (trade.action === 'CALL') {
                    actionClass = 'action-call';
                } else if (trade.action === 'PUT') {
                    actionClass = 'action-put';
                } else {
                    actionClass = 'action-idle';
                }
                
                // Format color displays with safety checks
                const thisColor = trade.thisColor || 'Unknown';
                const thisColorClass = thisColor === 'Green' ? 'color-green' : thisColor === 'Red' ? 'color-red' : '';
                
                const suggestColor = trade.suggestColor || 'Unknown';
                const suggestColorClass = suggestColor === 'Green' ? 'color-green' : suggestColor === 'Red' ? 'color-red' : '';
                
                const nextColor = trade.nextColor || 'Unknown';
                const nextColorClass = nextColor === 'Green' ? 'color-green' : nextColor === 'Red' ? 'color-red' : '';
                
                // Safe number formatting
                const sequence = trade.sequence || (index + 1);
                const timestamp = trade.timestamp || 0;
                const timeDisplay = trade.timeDisplay || '--:--';
                const winStatus = trade.winStatus || 0;
                const winContinue = trade.winContinue || 0;
                const lossContinue = trade.lossContinue || 0;
                
                const profit = (trade.profit !== undefined && trade.profit !== null && typeof trade.profit === 'number') ? 
                              trade.profit.toFixed(2) : '0.00';
                              
                const balance = (trade.balance !== undefined && trade.balance !== null && typeof trade.balance === 'number') ? 
                               trade.balance.toFixed(2) : '0.00';
                
                const profitNum = parseFloat(profit);
                const balanceNum = parseFloat(balance);
                
                row.innerHTML = `
                    <td>${sequence}</td>
                    <td>${timestamp}</td>
                    <td>${timeDisplay}</td>
                    <td><span class="${thisColorClass}">${thisColor}</span></td>
                    <td><span class="${actionClass}">${actionDisplay}</span></td>
                    <td><span class="${suggestColorClass}">${suggestColor}</span></td>
                    <td><span class="${nextColorClass}">${nextColor}</span></td>
                    <td>${winStatus}</td>
                    <td>${winContinue}</td>
                    <td>${lossContinue}</td>
                    <td class="${profitNum >= 0 ? 'profit' : 'loss'}">${profit}</td>
                    <td class="${balanceNum >= 0 ? 'profit' : 'loss'}">${balance}</td>
                `;
                
                tableBody.appendChild(row);
            });
            
            console.log('✅ อัพเดท Trade Table เสร็จสิ้น');
        }

        function updateTradeLog() {
            console.log('📋 อัพเดท Trade Log...');
            console.log('tradeData for log:', tradeData.length);
            
            const logContainer = document.getElementById('tradeLog');
            logContainer.innerHTML = '<h3 style="padding: 15px; margin: 0; background: #f8f9fa; border-bottom: 1px solid #ddd;">📋 Log การเทรด (สำรอง)</h3>';
            
            tradeData.forEach((trade, index) => {
                console.log(`📝 กำลังสร้าง log entry ${index + 1}:`, trade);
                
                // Safety checks for all values
                const result = (trade.profit > 0) ? 'win' : (trade.profit < 0) ? 'loss' : 'no-trade';
                const statusClass = result === 'win' ? 'status-win' : result === 'loss' ? 'status-loss' : 'status-idle';
                const profitClass = (trade.profit || 0) >= 0 ? 'profit' : 'loss';
                
                // Safe value extraction with defaults
                const amount = (trade.amount !== undefined && trade.amount !== null) ? trade.amount.toFixed(2) : '0.00';
                const profitLoss = (trade.profit !== undefined && trade.profit !== null) ? trade.profit.toFixed(2) : '0.00';
                const balance = (trade.balance !== undefined && trade.balance !== null) ? trade.balance.toFixed(2) : '0.00';
                const timeDisplay = trade.timeDisplay || '--:--';
                const action = trade.action || 'Idle';
                const lossContinue = trade.lossContinue || 0;
                
                const entry = document.createElement('div');
                entry.className = 'trade-entry';
                entry.innerHTML = `
                    <div>
                        <span class="status-indicator ${statusClass}"></span>
                        #${index + 1} - ${action} - ${timeDisplay}
                    </div>
                    <div>
                        <span>Amount: ${amount}</span> |
                        <span class="${profitClass}">P/L: ${profitLoss}</span> |
                        <span>Balance: ${balance}</span> |
                        <span>LC: ${lossContinue}</span>
                    </div>
                `;
                logContainer.appendChild(entry);
            });
            
            console.log('✅ อัพเดท Trade Log เสร็จสิ้น');
        }

        function updateIdlePeriods() {
            const container = document.getElementById('idlePeriods');
            
            if (idleData.length === 0) {
                container.innerHTML = '<p>ไม่มีช่วง Idle</p>';
                return;
            }

            let html = '';
            idleData.forEach((idle, index) => {
                const startTime = new Date(idle.startTime * 1000).toLocaleString();
                const endTime = idle.endTime ? new Date(idle.endTime * 1000).toLocaleString() : 'ยังไม่จบ';
                
                html += `
                    <div class="idle-entry">
                        <strong>Idle #${index + 1}</strong><br>
                        เริ่ม: ${startTime}<br>
                        จบ: ${endTime}<br>
                        Martingale Money: ${idle.martingaleMoney.toFixed(2)}<br>
                        Price: ${idle.price.toFixed(4)}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function generateSampleData() {
            const sampleData = [];
            let baseTime = Math.floor(Date.now() / 1000);
            let basePrice = 101300;
            
            // Generate 100 sample candles
            for (let i = 0; i < 100; i++) {
                const time = baseTime + (i * 60); // 1 minute intervals
                const open = basePrice + (Math.random() - 0.5) * 20;
                const volatility = 10 + Math.random() * 15;
                const high = open + Math.random() * volatility;
                const low = open - Math.random() * volatility;
                const close = low + Math.random() * (high - low);
                
                const thisColor = close >= open ? 'Green' : 'Red';
                
                sampleData.push({
                    time: time,
                    open: parseFloat(open.toFixed(4)),
                    high: parseFloat(high.toFixed(4)),
                    low: parseFloat(low.toFixed(4)),
                    close: parseFloat(close.toFixed(4)),
                    thisColor: thisColor
                });
                
                basePrice = close;
            }
            
            document.getElementById('rawData').value = JSON.stringify(sampleData, null, 2);
        }

        function clearResults() {
            document.getElementById('rawData').value = '';
            document.getElementById('tradingStats').innerHTML = '';
            document.getElementById('profitStats').innerHTML = '';
            document.getElementById('tradeLog').innerHTML = '';
            document.getElementById('idlePeriods').innerHTML = '';
            
            if (chart) {
                chart.remove();
                chart = null;
            }
            updateTradeTable();
            
            tradeData = [];
            idleData = [];
        }

        // Initialize chart on page load
        window.addEventListener('load', () => {
            initChart();
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (chart) {
                chart.applyOptions({
                    width: document.getElementById('chart').clientWidth
                });
            }
        });
    </script>
</body>
</html>