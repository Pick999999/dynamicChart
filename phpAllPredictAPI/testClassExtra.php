<?php
     $stCandle = "";        
     $file = fopen('rawData.json',"r");
     while(! feof($file))  {
       $stCandle .= fgets($file) ;
     }
     fclose($file);

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candle Analysis - Deriv Data</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin-bottom: 10px;
            font-size: 2em;
        }
        
        .config {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            display: inline-block;
        }
        
        .config label {
            margin: 0 10px;
            font-weight: bold;
        }
        
        .config input {
            width: 60px;
            padding: 5px;
            border: none;
            border-radius: 3px;
            text-align: center;
        }
        
        .config button {
            background: white;
            color: #667eea;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-left: 15px;
            transition: all 0.3s;
        }
        
        .config button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .table-container {
            overflow-x: auto;
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tr:hover {
            background: #f5f5f5;
        } 

		/* ⭐ Fixed Header - เพิ่มส่วนนี้ ⭐ */
        table thead {
          position: sticky !important;
          top: 0 !important;
          z-index: 100 !important;
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
          box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important;
        }
        
        table thead th {
          position: sticky !important;
          top: 0 !important;
          background: inherit !important;
          border-bottom: 2px solid rgba(255,255,255,0.2) !important;
        }
        
        /* Animation เมื่อ scroll */
        @keyframes headerShadow {
          from { box-shadow: 0 0 0 rgba(0,0,0,0); }
          to { box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        }
        
        .green {
            color: #22c55e;
            font-weight: bold;
        }
        
        .red {
            color: #ef4444;
            font-weight: bold;
        }
        
        .equal {
            color: #6b7280;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-up {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .badge-down {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .badge-parallel {
            background: #e5e7eb;
            color: #6b7280;
        }
        
        .badge-turn {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-cut {
            background: #ddd6fe;
            color: #7c3aed;
            font-weight: bold;
        }
        
        .conflict {
            background: #fecaca;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.85em;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-size: 1.1em;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.85em;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <textarea id="candleDataTxt" ><?=$stCandle;?></textarea>
    <div class="container">
        <div class="header">
            <h1>📊 Candle Analysis Dashboard</h1>
            <p>วิเคราะห์ข้อมูล Candle จาก Deriv.com พร้อม EMA และ MACD</p>
            <div class="config">
                <label>EMA Short: <input type="number" id="emaShort" value="5" min="1"></label>
                <label>EMA Long: <input type="number" id="emaLong" value="10" min="1"></label>
                <button onclick="runAnalysis()">🔄 วิเคราะห์</button>
            </div>
        </div>
        
        <div class="stats" id="stats"></div>
        
        <div class="table-container">
            <table id="resultsTable" style='height:300px'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>เวลา</th>
                        <th>สี</th>
                        <th>Candle Type</th>
                        <th>Body %</th>
                        <th>Upper Wick %</th>
                        <th>Lower Wick %</th>
                        <th>EMA Short</th>
                        <th>Slope Short</th>
                        <th>Turn Short</th>
                        <th>Position Short</th>
                        <th>EMA Long</th>
                        <th>Slope Long</th>
                        <th>Turn Long</th>
                        <th>Position Long</th>
                        <th>EMA Above</th>
                        <th>MACD</th>
                        <th>EMA Cut</th>
                        <th>EMA Conflict</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
        </div>
    </div>

    
	<script src="clsExtra.js"></script>
    <script>
        function getColorClass(color) {
            if (color === 'Green') return 'green';
            if (color === 'Red') return 'red';
            return 'equal';
        }
        
        function getSlopeClass(direction) {
            if (direction === 'Up') return 'badge-up';
            if (direction === 'Down') return 'badge-down';
            return 'badge-parallel';
        }
        
        function displayResults(results) {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';
            
            if (results.length === 0) {
                tbody.innerHTML = '<tr><td colspan="19" class="no-data">ไม่มีข้อมูล</td></tr>';
                return;
            }
            
            results.forEach((result, index) => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td><strong>${index + 1}</strong></td>
                    <td>${result.candleDisplay}</td>
                    <td class="${getColorClass(result.currentColor)}">${result.currentColor}</td>
                    <td>${result.candleBody.candleDesc}</td>
                    <td>${result.candleBody.bodyPercent}%</td>
                    <td>${result.candleBody.upperWickPercent}%</td>
                    <td>${result.candleBody.lowerWickPercent}%</td>
                    <td>${result.emaShortValue}</td>
                    <td><span class="badge ${getSlopeClass(result.emaShortSlopeDirection)}">${result.emaShortSlopeDirection}</span></td>
                    <td>${result.emaShortTurnType !== 'NoTurn' ? '<span class="badge badge-turn">' + result.emaShortTurnType + '</span>' : result.emaShortTurnType}</td>
                    <td style="font-size: 0.85em;">${result.emaShortCutPosition}</td>
                    <td>${result.emaLongValue}</td>
                    <td><span class="badge ${getSlopeClass(result.emaLongSlopeDirection)}">${result.emaLongSlopeDirection}</span></td>
                    <td>${result.emaLongTurnType !== 'NoTurn' ? '<span class="badge badge-turn">' + result.emaLongTurnType + '</span>' : result.emaLongTurnType}</td>
                    <td style="font-size: 0.85em;">${result.emaLongCutPosition}</td>
                    <td><strong>${result.emaAbove === 'emaShort' ? '📈 Short' : '📉 Long'}</strong></td>
                    <td>${result.macd}</td>
                    <td>${result.isEMACut !== 'No' ? '<span class="badge badge-cut">' + result.isEMACut + '</span>' : result.isEMACut}</td>
                    <td>${result.emaConflict !== 'No' ? '<span class="conflict">' + result.emaConflict + '</span>' : result.emaConflict}</td>
                `;
                
                tbody.appendChild(row);
            });
            
            // Display statistics
            displayStats(results);
        }
        
        function displayStats(results) {
            const statsDiv = document.getElementById('stats');
            
            const greenCandles = results.filter(r => r.currentColor === 'Green').length;
            const redCandles = results.filter(r => r.currentColor === 'Red').length;
            const emaCuts = results.filter(r => r.isEMACut !== 'No').length;
            const conflicts = results.filter(r => r.emaConflict !== 'No').length;
            const dojiCandles = results.filter(r => r.candleBody.candleDesc.includes('Doji')).length;
/*
			if (emaValue > upperWick) return 'Above Upper Wick';
            if (emaValue >= bodyTop && emaValue <= upperWick) return 'Between Upper Wick and Body';
            if (emaValue >= bodyBottom && emaValue <= bodyTop) return 'Inside Body';
            if (emaValue >= lowerWick && emaValue <= bodyBottom) return 'Between Body and  Lower Wick';
            return 'Below Lower Wick';
  */

listPosition = ['Above Upper Wick','Between Upper Wick and Body','Inside Body','Between Body and  Lower Wick','Below Lower Wick'] ;

            checkPositionIndex = 0 ; 
			const bodyBelowEMALong = results.filter(r => r.emaLongCutPosition === listPosition[checkPositionIndex]).length; 
			console.log(bodyBelowEMALong)
			
			alert(bodyBelowEMALong);
            
            statsDiv.innerHTML = `
                <div class="stat-card">
                    <div class="stat-label">Total Candles</div>
                    <div class="stat-value">${results.length}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Green Candles</div>
                    <div class="stat-value" style="color: #22c55e;">${greenCandles}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Red Candles</div>
                    <div class="stat-value" style="color: #ef4444;">${redCandles}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">EMA Crossovers</div>
                    <div class="stat-value" style="color: #7c3aed;">${emaCuts}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">EMA Conflicts</div>
                    <div class="stat-value" style="color: #f59e0b;">${conflicts}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Doji Patterns</div>
                    <div class="stat-value" style="color: #6366f1;">${dojiCandles}</div>
                </div>
				<div class="stat-card">
                    <div class="stat-label">จำนวนที่ ${listPosition[checkPositionIndex]}</div>
                    <div class="stat-value" style="color: #6366f1;">${bodyBelowEMALong}</div>
                </div>

            `;
        }
        
        function runAnalysis() {
            const emaShort = parseInt(document.getElementById('emaShort').value);
            const emaLong = parseInt(document.getElementById('emaLong').value);
            
            if (emaShort >= emaLong) {
                alert('⚠️ EMA Short ต้องน้อยกว่า EMA Long');
                return;
            }
            candleDataTxt = document.getElementById("candleDataTxt").value ;
			candleDataObj = JSON.parse(candleDataTxt) ;
            const results = analyzeCandleData(candleDataObj, emaShort, emaLong);
			 console.log(results)
			
            displayResults(results);
        }
        
        // Run on page load
        window.onload = function() {
            runAnalysis();
        };
    </script>

	<script>
        // เพิ่ม JavaScript เพื่อ enhance การทำงาน
        window.addEventListener('DOMContentLoaded', function() {
            const thead = document.querySelector('table thead');
            const tableContainer = document.querySelector('.table-container');
            
            // เพิ่ม class เมื่อ scroll
            tableContainer.addEventListener('scroll', function() {
                if (this.scrollTop > 0) {
                    thead.classList.add('scrolled');
                } else {
                    thead.classList.remove('scrolled');
                }
            });
            
            console.log('✅ Fixed header พร้อมใช้งาน');
        });
    </script>
</body>
</html>