<?php
/**
 * api_calendar_trade_v2.php
 * Backend API for Calendar Trade Hist V2
 * - get_vps_list: vpsMaster with image
 * - get_dates: distinct trade dates from vpsTradeData
 * - get_trades_by_date: trade records for a date + optional serverCode
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();
    $action = isset($_GET['action']) ? trim($_GET['action']) : 'get_vps_list';

    // ─── GET VPS LIST ───
    if ($action === 'get_vps_list') {
        $rows = $db->query("SELECT id, vpsCode, vpsName, publicIP, cloudProvider, instanceStatus, image, DerivAccountID FROM vpsMaster ORDER BY CAST(vpsCode AS UNSIGNED) ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── GET DATES ───
    if ($action === 'get_dates') {
        $sc = isset($_GET['serverCode']) && trim($_GET['serverCode']) !== '' ? trim($_GET['serverCode']) : null;

        // 1. Get dates from vpsTradeData (primary)
        $whereVps = "WHERE COALESCE(NULLIF(vt.purchaseTime,0), vt.timeCandle) > 0";
        $paramsVps = [];
        if ($sc !== null) {
            $whereVps .= " AND vt.serverCode = :sc";
            $paramsVps[':sc'] = $sc;
        }

        $sqlVps = "SELECT
                    DATE(FROM_UNIXTIME(COALESCE(NULLIF(vt.purchaseTime,0), vt.timeCandle))) AS tradeDate,
                    COUNT(*) AS totalTrades,
                    SUM(CASE WHEN LOWER(vt.WinStatus)='win' THEN 1 ELSE 0 END) AS winCount,
                    SUM(CASE WHEN LOWER(vt.WinStatus)='loss' THEN 1 ELSE 0 END) AS lossCount,
                    SUM(CASE WHEN LOWER(vt.WinStatus)='skipped' THEN 1 ELSE 0 END) AS skipCount,
                    ROUND(COALESCE(SUM(vt.ThisProfit),0),2) AS totalProfit,
                    COUNT(DISTINCT vt.serverCode) AS vpsCount,
                    'vpsTradeData' AS source
                FROM vpsTradeData vt
                {$whereVps}
                GROUP BY tradeDate
                ORDER BY tradeDate DESC";

        $stmtVps = $db->prepare($sqlVps);
        $stmtVps->execute($paramsVps);
        $vpsDates = $stmtVps->fetchAll(PDO::FETCH_ASSOC);

        // 2. Get dates from derivTradeHistory (fallback)
        $whereDeriv = "WHERE dt.purchase_time > 0";
        $paramsDeriv = [];
        if ($sc !== null) {
            $whereDeriv .= " AND dt.serverCode = :sc";
            $paramsDeriv[':sc'] = $sc;
        }

        $sqlDeriv = "SELECT
                    DATE(FROM_UNIXTIME(dt.purchase_time)) AS tradeDate,
                    COUNT(*) AS totalTrades,
                    SUM(CASE WHEN dt.profit > 0 THEN 1 ELSE 0 END) AS winCount,
                    SUM(CASE WHEN dt.profit < 0 THEN 1 ELSE 0 END) AS lossCount,
                    SUM(CASE WHEN dt.profit = 0 THEN 1 ELSE 0 END) AS skipCount,
                    ROUND(COALESCE(SUM(dt.profit),0),2) AS totalProfit,
                    COUNT(DISTINCT dt.serverCode) AS vpsCount,
                    'derivTradeHistory' AS source
                FROM derivTradeHistory dt
                {$whereDeriv}
                GROUP BY tradeDate
                ORDER BY tradeDate DESC";

        $stmtDeriv = $db->prepare($sqlDeriv);
        $stmtDeriv->execute($paramsDeriv);
        $derivDates = $stmtDeriv->fetchAll(PDO::FETCH_ASSOC);

        // 3. Merge dates (prefer vpsTradeData if present for that date)
        $mergedDates = [];
        foreach ($vpsDates as $row) {
            $mergedDates[$row['tradeDate']] = $row;
        }
        foreach ($derivDates as $row) {
            $d = $row['tradeDate'];
            if (!isset($mergedDates[$d])) {
                $mergedDates[$d] = $row;
            }
        }
        krsort($mergedDates);
        $finalDates = array_values($mergedDates);

        echo json_encode(['success' => true, 'data' => $finalDates], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── GET TRADES BY DATE ───
    if ($action === 'get_trades_by_date') {
        $date = isset($_GET['date']) ? trim($_GET['date']) : '';
        if (empty($date)) {
            throw new Exception('Missing date parameter');
        }
        $sc = isset($_GET['serverCode']) && trim($_GET['serverCode']) !== '' ? trim($_GET['serverCode']) : null;

        $tz = new DateTimeZone('Asia/Bangkok');
        $startEpoch = (new DateTime("{$date} 00:00:00", $tz))->getTimestamp();
        $endEpoch   = (new DateTime("{$date} 23:59:59", $tz))->getTimestamp();

        $where = "WHERE COALESCE(NULLIF(vt.purchaseTime,0), vt.timeCandle) BETWEEN :s AND :e";
        $params = [':s' => $startEpoch, ':e' => $endEpoch];
        if ($sc !== null) {
            $where .= " AND vt.serverCode = :sc";
            $params[':sc'] = $sc;
        }

        $sql = "SELECT
                    vt.*,
                    COALESCE(vm.vpsName, CONCAT('Server #', vt.serverCode)) AS vpsName,
                    COALESCE(vm.vpsCode, CAST(vt.serverCode AS CHAR)) AS vpsCode
                FROM vpsTradeData vt
                LEFT JOIN vpsMaster vm ON (vt.serverCode = vm.vpsCode OR vt.serverCode = vm.id)
                {$where}
                ORDER BY COALESCE(NULLIF(vt.purchaseTime,0), vt.timeCandle) ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dataSource = 'vpsTradeData';

        // Fallback: If no trades found in vpsTradeData, query derivTradeHistory
        if (empty($trades)) {
            $whereDeriv = "WHERE dt.purchase_time BETWEEN :s AND :e";
            $paramsDeriv = [':s' => $startEpoch, ':e' => $endEpoch];
            if ($sc !== null) {
                $whereDeriv .= " AND dt.serverCode = :sc";
                $paramsDeriv[':sc'] = $sc;
            }

            $sqlDeriv = "SELECT
                            dt.*,
                            COALESCE(vm.vpsName, CONCAT('Server #', dt.serverCode)) AS vpsName,
                            COALESCE(vm.vpsCode, CAST(dt.serverCode AS CHAR)) AS vpsCode
                        FROM derivTradeHistory dt
                        LEFT JOIN vpsMaster vm ON (dt.serverCode = vm.vpsCode OR dt.serverCode = vm.id)
                        {$whereDeriv}
                        ORDER BY dt.purchase_time ASC";

            $stmtDeriv = $db->prepare($sqlDeriv);
            $stmtDeriv->execute($paramsDeriv);
            $derivRows = $stmtDeriv->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($derivRows)) {
                $dataSource = 'derivTradeHistory';
                $lossConPerAsset = [];
                $runningProfit = 0;

                foreach ($derivRows as $d) {
                    $pTime = intval($d['purchase_time']);
                    $sTime = !empty($d['sell_time']) ? intval($d['sell_time']) : null;
                    $dur = !empty($d['duration']) ? intval($d['duration']) : ($sTime && $pTime ? ($sTime - $pTime) : null);
                    $profit = isset($d['profit']) ? floatval($d['profit']) : 0.0;
                    $buyPrice = isset($d['buy_price']) ? floatval($d['buy_price']) : 0.0;
                    $symbol = trim($d['symbol'] ?? '');

                    // WinStatus
                    if ($profit > 0) {
                        $winStatus = 'WIN';
                    } elseif ($profit < 0) {
                        $winStatus = 'LOSS';
                    } else {
                        $winStatus = 'TIE';
                    }

                    // LossCon calculation per asset
                    if (!isset($lossConPerAsset[$symbol])) {
                        $lossConPerAsset[$symbol] = 0;
                    }
                    if ($winStatus === 'LOSS') {
                        $lossConPerAsset[$symbol]++;
                    } elseif ($winStatus === 'WIN') {
                        $lossConPerAsset[$symbol] = 0;
                    }
                    $currentLossCon = $lossConPerAsset[$symbol];

                    $runningProfit += $profit;

                    // Action & Color
                    $action = strtoupper(trim($d['contract_type'] ?? ''));
                    if ($action === 'PUT') {
                        $color = 'Red';
                    } elseif ($action === 'CALL') {
                        $color = 'Green';
                    } else {
                        $color = !empty($action) ? $action : '-';
                    }

                    $timeCandleDisplay = $pTime > 0 ? (new DateTime("@$pTime"))->setTimezone($tz)->format('H:i:s') : '-';
                    $purchaseTimeDisplay = $pTime > 0 ? (new DateTime("@$pTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : '-';

                    $trades[] = [
                        'id' => $d['id'],
                        'serverCode' => intval($d['serverCode']),
                        'vpsCode' => $d['vpsCode'],
                        'vpsName' => $d['vpsName'],
                        'symbol' => $symbol,
                        'assetCode' => $symbol,
                        'contractId' => $d['contract_id'],
                        'buyId' => $d['transaction_id'],
                        'sellId' => null,
                        'entrySpot' => null,
                        'exitSpot' => null,
                        'diffSpot' => null,
                        'purchaseTime' => $pTime,
                        'purchaseTimeDisplay' => $purchaseTimeDisplay,
                        'tradeRoundNo' => null,
                        'tradeNo' => null,
                        'subTradeNo' => null,
                        'timeCandle' => $pTime,
                        'timeCandleDisplay' => $timeCandleDisplay,
                        'sellTime' => $sTime,
                        'sellTimeDisplay' => $sTime ? (new DateTime("@$sTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : null,
                        'actualDuration' => $dur,
                        'isAnomaly' => 0,
                        'thisColor' => $color,
                        'thisAction' => $action,
                        'targetColor' => null,
                        'MoneyTrade' => $buyPrice,
                        'WinStatus' => $winStatus,
                        'winCon' => ($winStatus === 'WIN' ? 1 : 0),
                        'lossCon' => $currentLossCon,
                        'ThisProfit' => $profit,
                        'GrandBalance' => round($runningProfit, 2),
                        'maxLossCon' => null,
                        'tradeStrategy' => !empty($d['shortcode']) ? $d['shortcode'] : (!empty($d['longcode']) ? $d['longcode'] : 'Deriv Broker'),
                        'codeStrategy' => $action,
                        'code_no' => null,
                        'noiseCode' => null,
                        'source' => 'derivTradeHistory'
                    ];
                }
            }
        }

        // summary
        $win = 0; $loss = 0; $skip = 0; $profit = 0;
        foreach ($trades as $t) {
            $s = strtolower($t['WinStatus'] ?? '');
            if ($s === 'win') $win++;
            elseif ($s === 'loss') $loss++;
            else $skip++;
            $profit += floatval($t['ThisProfit'] ?? 0);
        }

        echo json_encode([
            'success' => true,
            'date' => $date,
            'source' => $dataSource,
            'summary' => [
                'total' => count($trades),
                'win' => $win,
                'loss' => $loss,
                'skip' => $skip,
                'profit' => round($profit, 2),
                'winRate' => count($trades) - $skip > 0 ? round($win / (count($trades) - $skip) * 100, 1) : 0
            ],
            'trades' => $trades
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $ex->getMessage()]);
}
