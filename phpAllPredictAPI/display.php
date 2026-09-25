<?php
// display.php - แสดงผล JSON เป็น HTML Table และบันทึกเป็น output.json

// ตรวจสอบว่ามีไฟล์ output.json หรือไม่
$outputFile = 'output.json';

if (file_exists($outputFile)) {
    // อ่านจากไฟล์ output.json ที่มีอยู่แล้ว
    $jsonOutput = file_get_contents($outputFile);
    $data = json_decode($jsonOutput, true);
    
    if (!$data || !isset($data['labStJson'])) {
        die('<h2>❌ ไม่สามารถอ่านข้อมูล JSON ได้</h2><p>ไฟล์ output.json มีข้อมูลไม่ถูกต้อง</p><p><a href="deriv-full-analysis.html">กลับไปโหลดข้อมูลใหม่</a></p>');
    }
} else {
    // ถ้ายังไม่มีไฟล์ ให้ redirect ไปหน้า deriv-full-analysis.html
    die('
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .message-box {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 500px;
            }
            h2 { color: #667eea; margin-bottom: 20px; }
            p { color: #666; margin-bottom: 30px; line-height: 1.6; }
            .btn {
                display: inline-block;
                padding: 15px 30px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: bold;
                transition: all 0.3s ease;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class="message-box">
            <h2>📊 ยังไม่มีข้อมูลการวิเคราะห์</h2>
            <p>กรุณาโหลดข้อมูล Candle และทำการวิเคราะห์ก่อน<br>แล้วค่อยกลับมาดูรายงานที่นี่</p>
            <a href="deriv-full-analysis.html" class="btn">📈 ไปโหลดข้อมูล</a>
        </div>
    </body>
    </html>
    ');
}

$tradeData = $data['labStJson'];

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trade Analysis Report</title>
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
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .info-bar {
            display: flex;
            justify-content: space-around;
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 3px solid #667eea;
            flex-wrap: wrap;
        }
        
        .info-item {
            text-align: center;
            padding: 10px;
        }
        
        .info-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
        }
        
        .table-wrapper {
            overflow-x: auto;
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9em;
        }
        
        thead {
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        thead tr:first-child th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 10px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
            border-right: 2px solid rgba(255,255,255,0.3);
        }
        
        thead tr:nth-child(2) th {
            padding: 12px 8px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8em;
            letter-spacing: 0.3px;
            color: white;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        
        /* สีพื้นหลังของแต่ละ AI Group */
        .group-claude {
            background: linear-gradient(135deg, #FF6B6B 0%, #C92A2A 100%);
        }
        
        .group-chatgpt {
            background: linear-gradient(135deg, #51CF66 0%, #2F9E44 100%);
        }
        
        .group-deepseek {
            background: linear-gradient(135deg, #339AF0 0%, #1864AB 100%);
        }
        
        .group-nosort {
            background: linear-gradient(135deg, #FAB005 0%, #E67700 100%);
        }
        
        .group-withsort {
            background: linear-gradient(135deg, #AE3EC9 0%, #7048E8 100%);
        }
        
        .group-info {
            background: linear-gradient(135deg, #495057 0%, #212529 100%);
        }
        
        th {
            padding: 12px 8px;
            text-align: center;
        }
        
        td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            border-right: 1px solid #f0f0f0;
        }
        
        /* สีพื้นหลังของ columns ในแต่ละกลุ่ม */
        td.col-claude {
            background-color: #fff5f5;
        }
        
        td.col-chatgpt {
            background-color: #f4fce3;
        }
        
        td.col-deepseek {
            background-color: #e7f5ff;
        }
        
        td.col-nosort {
            background-color: #fff9db;
        }
        
        td.col-withsort {
            background-color: #f8f0fc;
        }
        
        tbody tr:hover td {
            filter: brightness(0.95);
        }
        
        tbody tr {
            transition: all 0.3s ease;
        }
        
        tbody tr:hover {
            background-color: #f5f5ff;
            transform: scale(1.01);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .win {
            color: #28a745;
            font-weight: bold;
        }
        
        .loss {
            color: #dc3545;
            font-weight: bold;
        }
        
        .loss-high {
            background-color: #ffe6e6;
        }
        
        .loss-critical {
            background-color: #ffcccc;
            font-weight: bold;
        }
        
        .ai-section {
            background: #f8f9fa;
            padding: 5px;
            border-radius: 5px;
            margin: 2px 0;
        }
        
        .green-result {
            color: #28a745;
            font-weight: bold;
        }
        
        .red-result {
            color: #dc3545;
            font-weight: bold;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-title {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .max-loss-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .max-loss-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .max-loss-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .max-loss-card.claude-card {
            border-left-color: #FF6B6B;
        }
        
        .max-loss-card.chatgpt-card {
            border-left-color: #51CF66;
        }
        
        .max-loss-card.deepseek-card {
            border-left-color: #339AF0;
        }
        
        .max-loss-card.nosort-card {
            border-left-color: #FAB005;
        }
        
        .max-loss-card.withsort-card {
            border-left-color: #AE3EC9;
        }
        
        .max-loss-title {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .max-loss-value {
            font-size: 3em;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .max-loss-card.claude-card .max-loss-value {
            color: #FF6B6B;
        }
        
        .max-loss-card.chatgpt-card .max-loss-value {
            color: #51CF66;
        }
        
        .max-loss-card.deepseek-card .max-loss-value {
            color: #339AF0;
        }
        
        .max-loss-card.nosort-card .max-loss-value {
            color: #FAB005;
        }
        
        .max-loss-card.withsort-card .max-loss-value {
            color: #AE3EC9;
        }
        
        .max-loss-label {
            color: #666;
            font-size: 0.85em;
            margin-top: 5px;
        }
        
        .section-title {
            text-align: center;
            padding: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5em;
            }
            
            table {
                font-size: 0.75em;
            }
            
            th, td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Trade Analysis Report</h1>
            <p>AI Trading Prediction Results</p>
        </div>
        
        <?php if (!empty($tradeData)): 
            $firstRecord = $tradeData[0];
            $lastRecord = $tradeData[count($tradeData) - 1];
            
            // คำนวณสถิติ
            $totalTrades = count($tradeData);
            $claudeWins = 0;
            $chatgptWins = 0;
            $deepseekWins = 0;
            $nosortWins = 0;
            $sortWins = 0;
            
            // หา Max LossCon
            $maxLossClaude = 0;
            $maxLossChatGPT = 0;
            $maxLossDeepSeek = 0;
            $maxLossNoSort = 0;
            $maxLossWithSort = 0;
            
            foreach ($tradeData as $trade) {
                if ($trade['Claude']['WinStatus'] === '💲Win') $claudeWins++;
                if ($trade['ChatGPT']['WinStatus'] === '💲Win') $chatgptWins++;
                if ($trade['DeepSeek']['WinStatus'] === '💲Win') $deepseekWins++;
                if ($trade['NoSort']['WinStatus'] === '💲Win') $nosortWins++;
                if ($trade['WithSort']['WinStatus'] === '💲Win') $sortWins++;
                
                // หาค่า Max LossCon
                if ($trade['Claude']['LossCon'] > $maxLossClaude) $maxLossClaude = $trade['Claude']['LossCon'];
                if ($trade['ChatGPT']['LossCon'] > $maxLossChatGPT) $maxLossChatGPT = $trade['ChatGPT']['LossCon'];
                if ($trade['DeepSeek']['LossCon'] > $maxLossDeepSeek) $maxLossDeepSeek = $trade['DeepSeek']['LossCon'];
                if ($trade['NoSort']['LossCon'] > $maxLossNoSort) $maxLossNoSort = $trade['NoSort']['LossCon'];
                if ($trade['WithSort']['LossCon'] > $maxLossWithSort) $maxLossWithSort = $trade['WithSort']['LossCon'];
            }
        ?>
        
        <div class="info-bar">
            <div class="info-item">
                <div class="info-label">Asset</div>
                <div class="info-value"><?= $firstRecord['asset'] ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Start Time</div>
                <div class="info-value"><?= $firstRecord['StartTime'] ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Trades</div>
                <div class="info-value"><?= $totalTrades ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Data</div>
                <div class="info-value"><?= $firstRecord['TotalData'] ?></div>
            </div>
        </div>
        
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-title">🤖 Claude Win Rate</div>
                <div class="stat-value"><?= round(($claudeWins/$totalTrades)*100, 1) ?>%</div>
                <small><?= $claudeWins ?> / <?= $totalTrades ?></small>
            </div>
            <div class="stat-card">
                <div class="stat-title">💬 ChatGPT Win Rate</div>
                <div class="stat-value"><?= round(($chatgptWins/$totalTrades)*100, 1) ?>%</div>
                <small><?= $chatgptWins ?> / <?= $totalTrades ?></small>
            </div>
            <div class="stat-card">
                <div class="stat-title">🔍 DeepSeek Win Rate</div>
                <div class="stat-value"><?= round(($deepseekWins/$totalTrades)*100, 1) ?>%</div>
                <small><?= $deepseekWins ?> / <?= $totalTrades ?></small>
            </div>
            <div class="stat-card">
                <div class="stat-title">📈 NoSort Win Rate</div>
                <div class="stat-value"><?= round(($nosortWins/$totalTrades)*100, 1) ?>%</div>
                <small><?= $nosortWins ?> / <?= $totalTrades ?></small>
            </div>
            <div class="stat-card">
                <div class="stat-title">📊 WithSort Win Rate</div>
                <div class="stat-value"><?= round(($sortWins/$totalTrades)*100, 1) ?>%</div>
                <small><?= $sortWins ?> / <?= $totalTrades ?></small>
            </div>
        </div>
        
        <div class="section-title">
            📉 Maximum Loss Streak Summary
        </div>
        
        <div class="max-loss-summary">
            <div class="max-loss-card claude-card">
                <div class="max-loss-title">🤖 Claude</div>
                <div class="max-loss-value"><?= $maxLossClaude ?></div>
                <div class="max-loss-label">Max Consecutive Losses</div>
            </div>
            
            <div class="max-loss-card chatgpt-card">
                <div class="max-loss-title">💬 ChatGPT</div>
                <div class="max-loss-value"><?= $maxLossChatGPT ?></div>
                <div class="max-loss-label">Max Consecutive Losses</div>
            </div>
            
            <div class="max-loss-card deepseek-card">
                <div class="max-loss-title">🔍 DeepSeek</div>
                <div class="max-loss-value"><?= $maxLossDeepSeek ?></div>
                <div class="max-loss-label">Max Consecutive Losses</div>
            </div>
            
            <div class="max-loss-card nosort-card">
                <div class="max-loss-title">📈 NoSort</div>
                <div class="max-loss-value"><?= $maxLossNoSort ?></div>
                <div class="max-loss-label">Max Consecutive Losses</div>
            </div>
            
            <div class="max-loss-card withsort-card">
                <div class="max-loss-title">📊 WithSort</div>
                <div class="max-loss-value"><?= $maxLossWithSort ?></div>
                <div class="max-loss-label">Max Consecutive Losses</div>
            </div>
        </div>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" class="group-info">Trade #</th>
                        <th rowspan="2" class="group-info">Time</th>
                        <th rowspan="2" class="group-info">Result</th>
                        <th colspan="3" class="group-claude">🤖 Claude</th>
                        <th colspan="3" class="group-chatgpt">💬 ChatGPT</th>
                        <th colspan="3" class="group-deepseek">🔍 DeepSeek</th>
                        <th colspan="3" class="group-nosort">📈 NoSort</th>
                        <th colspan="3" class="group-withsort">📊 WithSort</th>
                    </tr>
                    <tr>
                        <th class="group-claude">Predict</th>
                        <th class="group-claude">Status</th>
                        <th class="group-claude">Loss</th>
                        <th class="group-chatgpt">Predict</th>
                        <th class="group-chatgpt">Status</th>
                        <th class="group-chatgpt">Loss</th>
                        <th class="group-deepseek">Predict</th>
                        <th class="group-deepseek">Status</th>
                        <th class="group-deepseek">Loss</th>
                        <th class="group-nosort">Predict</th>
                        <th class="group-nosort">Status</th>
                        <th class="group-nosort">Loss</th>
                        <th class="group-withsort">Predict</th>
                        <th class="group-withsort">Status</th>
                        <th class="group-withsort">Loss</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tradeData as $trade): 
                        $claudeLoss = $trade['Claude']['LossCon'];
                        $chatgptLoss = $trade['ChatGPT']['LossCon'];
                        $deepseekLoss = $trade['DeepSeek']['LossCon'];
                        $nosortLoss = $trade['NoSort']['LossCon'];
                        $sortLoss = $trade['WithSort']['LossCon'];
                        
                        $maxLoss = max($claudeLoss, $chatgptLoss, $deepseekLoss, $nosortLoss, $sortLoss);
                        $rowClass = $maxLoss > 5 ? 'loss-critical' : ($maxLoss > 3 ? 'loss-high' : '');
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><strong><?= $trade['TradeNoIndex'] ?></strong></td>
                        <td><?= $trade['SuggestTimeCandle'] ?></td>
                        <td class="<?= strpos($trade['resultColor'], 'Green') !== false ? 'green-result' : 'red-result' ?>">
                            <?= $trade['resultColor'] ?>
                        </td>
                        
                        <!-- Claude -->
                        <td class="col-claude"><?= $trade['Claude']['SuggestColor'] ?></td>
                        <td class="col-claude <?= $trade['Claude']['WinStatus'] === '💲Win' ? 'win' : 'loss' ?>">
                            <?= $trade['Claude']['WinStatus'] ?>
                        </td>
                        <td class="col-claude"><strong><?= $trade['Claude']['LossCon'] ?></strong></td>
                        
                        <!-- ChatGPT -->
                        <td class="col-chatgpt"><?= $trade['ChatGPT']['SuggestColor'] ?></td>
                        <td class="col-chatgpt <?= $trade['ChatGPT']['WinStatus'] === '💲Win' ? 'win' : 'loss' ?>">
                            <?= $trade['ChatGPT']['WinStatus'] ?>
                        </td>
                        <td class="col-chatgpt"><strong><?= $trade['ChatGPT']['LossCon'] ?></strong></td>
                        
                        <!-- DeepSeek -->
                        <td class="col-deepseek"><?= $trade['DeepSeek']['SuggestColor'] ?></td>
                        <td class="col-deepseek <?= $trade['DeepSeek']['WinStatus'] === '💲Win' ? 'win' : 'loss' ?>">
                            <?= $trade['DeepSeek']['WinStatus'] ?>
                        </td>
                        <td class="col-deepseek"><strong><?= $trade['DeepSeek']['LossCon'] ?></strong></td>
                        
                        <!-- NoSort -->
                        <td class="col-nosort"><?= $trade['NoSort']['SuggestColor'] ?></td>
                        <td class="col-nosort <?= $trade['NoSort']['WinStatus'] === '💲Win' ? 'win' : 'loss' ?>">
                            <?= $trade['NoSort']['WinStatus'] ?>
                        </td>
                        <td class="col-nosort"><strong><?= $trade['NoSort']['LossCon'] ?></strong></td>
                        
                        <!-- WithSort -->
                        <td class="col-withsort"><?= $trade['WithSort']['SuggestColor'] ?></td>
                        <td class="col-withsort <?= $trade['WithSort']['WinStatus'] === '💲Win' ? 'win' : 'loss' ?>">
                            <?= $trade['WithSort']['WinStatus'] ?>
                        </td>
                        <td class="col-withsort"><strong><?= $trade['WithSort']['LossCon'] ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php endif; ?>
        
        <div class="footer">
            <p>✅ JSON Data saved to: <strong>output.json</strong></p>
            <p style="margin-top: 10px; font-size: 0.9em;">
                Generated: <?= date('Y-m-d H:i:s') ?> | 
                Last Modified: <?= date('Y-m-d H:i:s', filemtime('output.json')) ?>
            </p>
            <div style="margin-top: 20px;">
                <a href="deriv-full-analysis.html" style="display: inline-block; padding: 12px 25px; 
                   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                   color: white; text-decoration: none; border-radius: 8px; 
                   font-weight: bold; margin: 0 10px; transition: all 0.3s ease;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.2)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    📈 Load New Data
                </a>
                <a href="display.php" style="display: inline-block; padding: 12px 25px; 
                   background: linear-gradient(135deg, #51cf66 0%, #40c057 100%); 
                   color: white; text-decoration: none; border-radius: 8px; 
                   font-weight: bold; margin: 0 10px; transition: all 0.3s ease;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.2)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    🔄 Refresh Report
                </a>
            </div>
        </div>
    </div>
</body>
</html>
