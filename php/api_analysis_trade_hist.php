<?php
/**
 * api_analysis_trade_hist.php
 * Backend API for Analysis Trade History
 * 
 * Provides endpoints for:
 * 1. action=get_dates: List distinct dates from tradeHead.startTimeTrade formatted as dd/mm/yyyy
 * 2. action=get_tree_data: Tree hierarchy grouped by { serverCode -> tradeRoundNo -> assetCode }
 * 3. action=get_asset_trades: List trade records for a specific asset within a round
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/db.php';

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

try {
    $db = getDbConnection();

    switch ($action) {
        case 'get_dates':
            // 1. Get list of distinct dates from tradeHead.startTimeTrade
            $sql = "SELECT 
                        DATE_FORMAT(startTimeTrade, '%d/%m/%Y') AS date_display,
                        DATE(startTimeTrade) AS date_val,
                        COUNT(DISTINCT serverCode) AS total_servers,
                        COUNT(DISTINCT tradeRoundNo) AS total_rounds,
                        COUNT(*) AS total_assets
                    FROM tradeHead
                    WHERE startTimeTrade IS NOT NULL
                    GROUP BY date_val, date_display
                    ORDER BY date_val DESC";
            $stmt = $db->query($sql);
            $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 1.2 Include dates from derivTradeHistory (fallback)
            $derivSql = "SELECT 
                            DATE_FORMAT(FROM_UNIXTIME(purchase_time), '%d/%m/%Y') AS date_display,
                            DATE(FROM_UNIXTIME(purchase_time)) AS date_val,
                            COUNT(DISTINCT serverCode) AS total_servers,
                            1 AS total_rounds,
                            COUNT(DISTINCT symbol) AS total_assets
                        FROM derivTradeHistory
                        WHERE purchase_time > 0
                        GROUP BY date_val, date_display
                        ORDER BY date_val DESC";
            $derivStmt = $db->query($derivSql);
            $derivDates = $derivStmt->fetchAll(PDO::FETCH_ASSOC);

            $mergedDates = [];
            foreach ($dates as $d) {
                $mergedDates[$d['date_val']] = $d;
            }
            foreach ($derivDates as $d) {
                if (!isset($mergedDates[$d['date_val']])) {
                    $mergedDates[$d['date_val']] = $d;
                }
            }
            krsort($mergedDates);
            $finalDates = array_values($mergedDates);

            echo json_encode([
                'success' => true,
                'total'   => count($finalDates),
                'dates'   => $finalDates
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'check_date_data':
            // Check if there is existing data in vpsTradeData, tradeHead, derivTradeHistory for given server & date
            $serverCode = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : 0;
            $dateInput = isset($_GET['date']) ? trim($_GET['date']) : '';

            $dateVal = $dateInput;
            if (strpos($dateInput, '/') !== false) {
                $parts = explode('/', $dateInput);
                if (count($parts) === 3) {
                    $day = intval($parts[0]);
                    $month = intval($parts[1]);
                    $year = intval($parts[2]);
                    if ($year > 2400) $year -= 543;
                    $dateVal = sprintf("%04d-%02d-%02d", $year, $month, $day);
                }
            }

            $tz = new DateTimeZone('Asia/Bangkok');
            if (empty($dateVal)) {
                $dateVal = (new DateTime('now', $tz))->format('Y-m-d');
            }

            $startEpoch = (new DateTime("{$dateVal} 00:00:00", $tz))->getTimestamp();
            $endEpoch = (new DateTime("{$dateVal} 23:59:59", $tz))->getTimestamp();
            $dayStart0005Epoch = (new DateTime("{$dateVal} 00:05:00", $tz))->getTimestamp();
            $nowEpoch = time();
            $todayDate = (new DateTime('now', $tz))->format('Y-m-d');
            $isToday = ($dateVal === $todayDate);
            $currentTimeDisplay = (new DateTime('now', $tz))->format('H:i:s');

            // 1. Check vpsTradeData & Max trade timestamp
            $vpsTradeSql = "SELECT COUNT(*), MAX(COALESCE(NULLIF(timeCandle, 0), purchaseTime)) FROM vpsTradeData 
                            WHERE (serverCode = :serverCode OR :serverCode = 0)
                              AND (
                                (timeCandle BETWEEN :s AND :e)
                                OR (purchaseTime BETWEEN :s AND :e)
                                OR (DATE(created_at) = :dateVal)
                              )";
            $stmt1 = $db->prepare($vpsTradeSql);
            $stmt1->execute([':serverCode' => $serverCode, ':s' => $startEpoch, ':e' => $endEpoch, ':dateVal' => $dateVal]);
            $row1 = $stmt1->fetch(PDO::FETCH_NUM);
            $vpsTradeCount = intval($row1[0] ?? 0);
            $maxTradeTime = intval($row1[1] ?? 0);

            // 2. Check tradeHead
            $headSql = "SELECT COUNT(*) FROM tradeHead 
                        WHERE (serverCode = :serverCode OR :serverCode = 0)
                          AND DATE(startTimeTrade) = :dateVal";
            $stmt2 = $db->prepare($headSql);
            $stmt2->execute([':serverCode' => $serverCode, ':dateVal' => $dateVal]);
            $tradeHeadCount = intval($stmt2->fetchColumn());

            // 3. Check derivTradeHistory
            $derivSql = "SELECT COUNT(*), MAX(purchase_time) FROM derivTradeHistory 
                         WHERE (serverCode = :serverCode OR :serverCode = 0)
                           AND purchase_time BETWEEN :s AND :e";
            $stmt3 = $db->prepare($derivSql);
            $stmt3->execute([':serverCode' => $serverCode, ':s' => $startEpoch, ':e' => $endEpoch]);
            $row3 = $stmt3->fetch(PDO::FETCH_NUM);
            $derivHistoryCount = intval($row3[0] ?? 0);
            $maxDerivTradeTime = intval($row3[1] ?? 0);
            if ($maxDerivTradeTime > $maxTradeTime) {
                $maxTradeTime = $maxDerivTradeTime;
            }

            // 4. Check candles & Max candle timestamp
            $candleSql = "SELECT COUNT(*), MAX(time) FROM candles 
                          WHERE time BETWEEN :s AND :e";
            $stmt4 = $db->prepare($candleSql);
            $stmt4->execute([':s' => $startEpoch, ':e' => $endEpoch]);
            $row4 = $stmt4->fetch(PDO::FETCH_NUM);
            $candleCount = intval($row4[0] ?? 0);
            $maxCandleTime = intval($row4[1] ?? 0);

            $hasData = ($vpsTradeCount > 0 || $tradeHeadCount > 0 || $derivHistoryCount > 0);

            // Calculate gaps and need for updating (e.g. data up to 07:00 while now is 11:40)
            $needsUpdate = false;
            $timeGapDescription = '';
            $maxRecordedTime = max($maxTradeTime, $maxCandleTime);
            $maxRecordedDisplay = $maxRecordedTime > 0 ? (new DateTime("@$maxRecordedTime"))->setTimezone($tz)->format('H:i') : '-';

            if (!$hasData || $candleCount === 0) {
                $needsUpdate = true;
                $timeGapDescription = "ยังไม่มีข้อมูลในระบบสำหรับวันที่ {$dateVal}";
            } elseif ($isToday) {
                // If today, check if last recorded data is older than 5 minutes from now
                if ($maxRecordedTime > 0 && ($nowEpoch - $maxRecordedTime) > 300) {
                    $needsUpdate = true;
                    $timeGapDescription = "ข้อมูลในระบบมีถึงเวลา {$maxRecordedDisplay} แต่วันนี้ขณะนี้เป็นเวลา {$currentTimeDisplay} (ควรดึงข้อมูล 00:05 - now)";
                }
            }

            // Fetch server info from vpsMaster if available
            $vpsInfo = null;
            if ($serverCode > 0) {
                $vStmt = $db->prepare("SELECT id, vpsCode, vpsName, publicIP, portno, url FROM vpsMaster WHERE vpsCode = :sc OR id = :sc LIMIT 1");
                $vStmt->execute([':sc' => $serverCode]);
                $vpsInfo = $vStmt->fetch(PDO::FETCH_ASSOC);
            }

            echo json_encode([
                'success'             => true,
                'serverCode'          => $serverCode,
                'date'                => $dateVal,
                'isToday'             => $isToday,
                'nowEpoch'            => $nowEpoch,
                'currentTime'         => $currentTimeDisplay,
                'dayStart0005Epoch'   => $dayStart0005Epoch,
                'hasData'             => $hasData,
                'needsUpdate'         => $needsUpdate,
                'timeGapDescription'  => $timeGapDescription,
                'maxRecordedTime'     => $maxRecordedTime,
                'maxRecordedDisplay'  => $maxRecordedDisplay,
                'maxTradeTime'        => $maxTradeTime,
                'maxTradeDisplay'     => $maxTradeTime > 0 ? (new DateTime("@$maxTradeTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : '-',
                'maxCandleTime'       => $maxCandleTime,
                'maxCandleDisplay'    => $maxCandleTime > 0 ? (new DateTime("@$maxCandleTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : '-',
                'counts'              => [
                    'vpsTradeData'      => $vpsTradeCount,
                    'tradeHead'         => $tradeHeadCount,
                    'derivTradeHistory' => $derivHistoryCount,
                    'candles'           => $candleCount
                ],
                'vps'                 => $vpsInfo
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_chart_filter_options':
            // Returns available VPS servers, dates, assets, and rounds
            $dateInput = isset($_GET['date']) ? trim($_GET['date']) : '';
            $selectedServer = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : 0;
            $selectedAsset = isset($_GET['assetCode']) ? trim($_GET['assetCode']) : '';

            $dateVal = '';
            if (!empty($dateInput)) {
                $dateVal = $dateInput;
                if (strpos($dateInput, '/') !== false) {
                    $parts = explode('/', $dateInput);
                    if (count($parts) === 3) {
                        $day = intval($parts[0]);
                        $month = intval($parts[1]);
                        $year = intval($parts[2]);
                        if ($year > 2400) $year -= 543;
                        $dateVal = sprintf("%04d-%02d-%02d", $year, $month, $day);
                    }
                }
            }

            // 1. VPS List: from vpsMaster joined with active trade counts
            $vpsSql = "SELECT 
                          COALESCE(vm.id, th.serverCode) as id,
                          COALESCE(vm.vpsCode, th.serverCode) as serverCode,
                          COALESCE(vm.vpsName, CONCAT('Server #', th.serverCode)) as serverName,
                          COALESCE(vm.publicIP, '') as publicIP,
                          COALESCE(vm.portno, '') as portno,
                          COALESCE(vm.url, '') as url,
                          COUNT(DISTINCT th.id) as totalHeadRounds
                       FROM tradeHead th
                       LEFT JOIN vpsMaster vm ON (th.serverCode = vm.vpsCode OR th.serverCode = vm.id)
                       GROUP BY th.serverCode
                       UNION
                       SELECT 
                          id, vpsCode as serverCode, vpsName, publicIP, portno, url, 0 as totalHeadRounds
                       FROM vpsMaster
                       WHERE vpsCode NOT IN (SELECT DISTINCT serverCode FROM tradeHead WHERE serverCode IS NOT NULL)
                       ORDER BY serverCode ASC";
            $vpsStmt = $db->query($vpsSql);
            $vpsList = $vpsStmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Dates List: distinct dates from tradeHead and derivTradeHistory
            $datesSql = "SELECT 
                            DATE_FORMAT(startTimeTrade, '%d/%m/%Y') AS date_display,
                            DATE(startTimeTrade) AS date_val,
                            COUNT(DISTINCT serverCode) AS total_servers,
                            COUNT(DISTINCT tradeRoundNo) AS total_rounds,
                            COUNT(*) AS total_assets
                        FROM tradeHead
                        WHERE startTimeTrade IS NOT NULL
                        GROUP BY date_val, date_display
                        ORDER BY date_val DESC";
            $datesStmt = $db->query($datesSql);
            $allDatesRows = $datesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fallback derivTradeHistory dates
            $derivDatesSql = "SELECT 
                                DATE_FORMAT(FROM_UNIXTIME(purchase_time), '%d/%m/%Y') AS date_display,
                                DATE(FROM_UNIXTIME(purchase_time)) AS date_val,
                                COUNT(DISTINCT serverCode) AS total_servers,
                                1 AS total_rounds,
                                COUNT(DISTINCT symbol) AS total_assets
                            FROM derivTradeHistory
                            WHERE purchase_time > 0
                            GROUP BY date_val, date_display
                            ORDER BY date_val DESC";
            $derivDatesStmt = $db->query($derivDatesSql);
            $derivDatesRows = $derivDatesStmt->fetchAll(PDO::FETCH_ASSOC);

            $mergedDates = [];
            foreach ($allDatesRows as $d) {
                $mergedDates[$d['date_val']] = $d;
            }
            foreach ($derivDatesRows as $d) {
                if (!isset($mergedDates[$d['date_val']])) {
                    $mergedDates[$d['date_val']] = $d;
                }
            }
            krsort($mergedDates);
            $finalDates = array_values($mergedDates);

            // 3. All Distinct Assets in System
            $assetsSql = "SELECT DISTINCT assetCode FROM tradeHead WHERE assetCode IS NOT NULL AND assetCode != ''
                          UNION 
                          SELECT DISTINCT assetCode FROM vpsTradeData WHERE assetCode IS NOT NULL AND assetCode != ''
                          UNION 
                          SELECT DISTINCT symbol as assetCode FROM derivTradeHistory WHERE symbol IS NOT NULL AND symbol != ''
                          ORDER BY assetCode ASC";
            $assetsStmt = $db->query($assetsSql);
            $allAssets = $assetsStmt->fetchAll(PDO::FETCH_COLUMN);

            // 4. Contextual Assets & Rounds for selected date and server (if provided)
            $availableAssets = [];
            $availableRounds = [];
            if (!empty($dateVal) && $selectedServer > 0) {
                $ctxAssetSql = "SELECT DISTINCT assetCode, tradeRoundNo, usestrategyCode
                                FROM tradeHead 
                                WHERE serverCode = :s 
                                  AND (DATE(startTimeTrade) = :d OR DATE(created_at) = :d)
                                ORDER BY assetCode ASC, tradeRoundNo ASC";
                $ctxStmt = $db->prepare($ctxAssetSql);
                $ctxStmt->execute([':s' => $selectedServer, ':d' => $dateVal]);
                $ctxRows = $ctxStmt->fetchAll(PDO::FETCH_ASSOC);

                $roundMap = [];
                $assetMap = [];
                $roundMap[0] = [
                    'tradeRoundNo'    => 0,
                    'usestrategyCode' => 'ทุกรอบของวัน (All Rounds - ทั้งวัน)'
                ];
                foreach ($ctxRows as $cr) {
                    $assetMap[$cr['assetCode']] = true;
                    if (empty($selectedAsset) || $cr['assetCode'] === $selectedAsset) {
                        $roundMap[$cr['tradeRoundNo']] = [
                            'tradeRoundNo'    => intval($cr['tradeRoundNo']),
                            'usestrategyCode' => $cr['usestrategyCode'] ?: 'N/A'
                        ];
                    }
                }
                $availableAssets = array_keys($assetMap);
                $availableRounds = array_values($roundMap);
            }

            echo json_encode([
                'success'         => true,
                'servers'         => $vpsList,
                'dates'           => $finalDates,
                'allAssets'       => $allAssets,
                'availableAssets' => $availableAssets,
                'availableRounds' => $availableRounds
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_tree_data':
            // 2. Query tradeHead by date to build hierarchy: serverCode -> tradeRoundNo -> assetCode
            $dateInput = isset($_GET['date']) ? trim($_GET['date']) : '';
            if (empty($dateInput)) {
                throw new Exception("Missing date parameter");
            }

            // Standardize date to YYYY-MM-DD
            $dateVal = $dateInput;
            if (strpos($dateInput, '/') !== false) {
                $parts = explode('/', $dateInput);
                if (count($parts) === 3) {
                    $day = intval($parts[0]);
                    $month = intval($parts[1]);
                    $year = intval($parts[2]);
                    if ($year > 2400) $year -= 543;
                    $dateVal = sprintf("%04d-%02d-%02d", $year, $month, $day);
                }
            }

            // Fetch tradeHead joined with vpsMaster
            $sql = "SELECT 
                        th.id,
                        th.serverCode,
                        COALESCE(vm.vpsName, CONCAT('Server #', th.serverCode)) AS serverName,
                        COALESCE(vm.publicIP, '') AS publicIP,
                        th.tradeRoundNo,
                        th.usestrategyCode,
                        th.startTimeTrade,
                        th.stopTimeTrade,
                        th.durationTrade,
                        th.totalAssets,
                        th.assetCode,
                        th.MaxWinCon,
                        th.MaxLossCon
                    FROM tradeHead th
                    LEFT JOIN vpsMaster vm ON (th.serverCode = vm.vpsCode OR th.serverCode = vm.id)
                    WHERE DATE(th.startTimeTrade) = :tradeDate
                    ORDER BY th.serverCode ASC, th.tradeRoundNo ASC, th.assetCode ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':tradeDate' => $dateVal]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch trade stats from vpsTradeData for matching servers & rounds on this specific date
            $statsSql = "SELECT 
                            serverCode,
                            tradeRoundNo,
                            assetCode,
                            COUNT(*) AS total_trades,
                            SUM(CASE WHEN LOWER(WinStatus) = 'win' THEN 1 ELSE 0 END) AS win_count,
                            SUM(CASE WHEN LOWER(WinStatus) = 'loss' THEN 1 ELSE 0 END) AS loss_count,
                            SUM(CASE WHEN LOWER(WinStatus) = 'skipped' THEN 1 ELSE 0 END) AS skipped_count,
                            ROUND(COALESCE(SUM(ThisProfit), 0), 2) AS total_profit
                         FROM vpsTradeData
                         WHERE DATE(FROM_UNIXTIME(COALESCE(NULLIF(timeCandle, 0), NULLIF(purchaseTime, 0)))) = :tradeDate
                            OR (COALESCE(timeCandle, 0) = 0 AND COALESCE(purchaseTime, 0) = 0 AND DATE(created_at) = :tradeDate)
                         GROUP BY serverCode, tradeRoundNo, assetCode";
            $statsStmt = $db->prepare($statsSql);
            $statsStmt->execute([':tradeDate' => $dateVal]);
            $statsRows = $statsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Index stats by key "serverCode_tradeRoundNo_assetCode"
            $statsMap = [];
            foreach ($statsRows as $sr) {
                $key = $sr['serverCode'] . '_' . $sr['tradeRoundNo'] . '_' . $sr['assetCode'];
                $statsMap[$key] = $sr;
            }

            // Group into tree structure: Servers -> Rounds -> Assets
            $treeServers = [];

            foreach ($rows as $row) {
                $sCode = $row['serverCode'];
                $rNo = $row['tradeRoundNo'];
                $aCode = $row['assetCode'];

                if (!isset($treeServers[$sCode])) {
                    $treeServers[$sCode] = [
                        'serverCode' => (int)$sCode,
                        'serverName' => $row['serverName'],
                        'publicIP'   => $row['publicIP'],
                        'rounds'     => []
                    ];
                }

                if (!isset($treeServers[$sCode]['rounds'][$rNo])) {
                    $treeServers[$sCode]['rounds'][$rNo] = [
                        'tradeRoundNo'    => (int)$rNo,
                        'usestrategyCode' => $row['usestrategyCode'] ?: 'N/A',
                        'startTimeTrade'  => $row['startTimeTrade'],
                        'stopTimeTrade'   => $row['stopTimeTrade'],
                        'durationTrade'   => $row['durationTrade'] ?: '-',
                        'totalAssets'     => (int)$row['totalAssets'],
                        'assets'          => []
                    ];
                }

                // Check trade stats for this asset
                $statKey = $sCode . '_' . $rNo . '_' . $aCode;
                $stat = isset($statsMap[$statKey]) ? $statsMap[$statKey] : null;

                $treeServers[$sCode]['rounds'][$rNo]['assets'][] = [
                    'assetCode'    => $aCode,
                    'MaxWinCon'    => (int)$row['MaxWinCon'],
                    'MaxLossCon'   => (int)$row['MaxLossCon'],
                    'totalTrades'  => $stat ? (int)$stat['total_trades'] : 0,
                    'winCount'     => $stat ? (int)$stat['win_count'] : 0,
                    'lossCount'    => $stat ? (int)$stat['loss_count'] : 0,
                    'skippedCount' => $stat ? (int)$stat['skipped_count'] : 0,
                    'totalProfit'  => $stat ? (float)$stat['total_profit'] : 0.00
                ];
            }

            // Convert assoc arrays to indexed arrays for JSON
            $formattedServers = [];
            foreach ($treeServers as $s) {
                $s['rounds'] = array_values($s['rounds']);
                $formattedServers[] = $s;
            }

            // Fallback to derivTradeHistory if no tradeHead records exist for this date
            if (empty($formattedServers)) {
                $tz = new DateTimeZone('Asia/Bangkok');
                $startEpoch = (new DateTime("{$dateVal} 00:00:00", $tz))->getTimestamp();
                $endEpoch   = (new DateTime("{$dateVal} 23:59:59", $tz))->getTimestamp();

                $derivTreeSql = "SELECT 
                                    dt.serverCode,
                                    COALESCE(vm.vpsName, CONCAT('Server #', dt.serverCode)) AS serverName,
                                    COALESCE(vm.publicIP, '') AS publicIP,
                                    dt.symbol AS assetCode,
                                    MIN(dt.purchase_time) AS min_p,
                                    MAX(dt.purchase_time) AS max_p,
                                    COUNT(*) AS total_trades,
                                    SUM(CASE WHEN dt.profit > 0 THEN 1 ELSE 0 END) AS win_count,
                                    SUM(CASE WHEN dt.profit < 0 THEN 1 ELSE 0 END) AS loss_count,
                                    SUM(CASE WHEN dt.profit = 0 THEN 1 ELSE 0 END) AS skipped_count,
                                    ROUND(COALESCE(SUM(dt.profit), 0), 2) AS total_profit
                                 FROM derivTradeHistory dt
                                 LEFT JOIN vpsMaster vm ON (dt.serverCode = vm.vpsCode OR dt.serverCode = vm.id)
                                 WHERE dt.purchase_time BETWEEN :s AND :e
                                 GROUP BY dt.serverCode, dt.symbol
                                 ORDER BY dt.serverCode ASC, dt.symbol ASC";
                $dtStmt = $db->prepare($derivTreeSql);
                $dtStmt->execute([':s' => $startEpoch, ':e' => $endEpoch]);
                $derivTreeRows = $dtStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($derivTreeRows)) {
                    $streakSql = "SELECT serverCode, symbol, profit FROM derivTradeHistory WHERE purchase_time BETWEEN :s AND :e ORDER BY purchase_time ASC";
                    $streakStmt = $db->prepare($streakSql);
                    $streakStmt->execute([':s' => $startEpoch, ':e' => $endEpoch]);
                    $streakRows = $streakStmt->fetchAll(PDO::FETCH_ASSOC);

                    $conMap = [];
                    foreach ($streakRows as $sr) {
                        $k = $sr['serverCode'] . '_' . $sr['symbol'];
                        if (!isset($conMap[$k])) $conMap[$k] = ['curW' => 0, 'maxW' => 0, 'curL' => 0, 'maxL' => 0];
                        $p = floatval($sr['profit']);
                        if ($p > 0) {
                            $conMap[$k]['curW']++;
                            $conMap[$k]['curL'] = 0;
                            if ($conMap[$k]['curW'] > $conMap[$k]['maxW']) $conMap[$k]['maxW'] = $conMap[$k]['curW'];
                        } elseif ($p < 0) {
                            $conMap[$k]['curL']++;
                            $conMap[$k]['curW'] = 0;
                            if ($conMap[$k]['curL'] > $conMap[$k]['maxL']) $conMap[$k]['maxL'] = $conMap[$k]['curL'];
                        }
                    }

                    $treeServers = [];
                    foreach ($derivTreeRows as $dRow) {
                        $sCode = $dRow['serverCode'];
                        $rNo = 1;
                        $aCode = $dRow['assetCode'];
                        $k = $sCode . '_' . $aCode;
                        $maxW = $conMap[$k]['maxW'] ?? 0;
                        $maxL = $conMap[$k]['maxL'] ?? 0;

                        if (!isset($treeServers[$sCode])) {
                            $treeServers[$sCode] = [
                                'serverCode' => (int)$sCode,
                                'serverName' => $dRow['serverName'],
                                'publicIP'   => $dRow['publicIP'],
                                'rounds'     => []
                            ];
                        }

                        if (!isset($treeServers[$sCode]['rounds'][$rNo])) {
                            $minP = intval($dRow['min_p']);
                            $maxP = intval($dRow['max_p']);
                            $durSec = max(0, $maxP - $minP);
                            $durStr = sprintf("%02d:%02d:%02d", floor($durSec / 3600), floor(($durSec % 3600) / 60), $durSec % 60);

                            $treeServers[$sCode]['rounds'][$rNo] = [
                                'tradeRoundNo'    => 1,
                                'usestrategyCode' => 'Deriv Broker History',
                                'startTimeTrade'  => (new DateTime("@$minP"))->setTimezone($tz)->format('Y-m-d H:i:s'),
                                'stopTimeTrade'   => (new DateTime("@$maxP"))->setTimezone($tz)->format('Y-m-d H:i:s'),
                                'durationTrade'   => $durStr,
                                'totalAssets'     => 0,
                                'assets'          => []
                            ];
                        }

                        $treeServers[$sCode]['rounds'][$rNo]['assets'][] = [
                            'assetCode'    => $aCode,
                            'MaxWinCon'    => $maxW,
                            'MaxLossCon'   => $maxL,
                            'totalTrades'  => (int)$dRow['total_trades'],
                            'winCount'     => (int)$dRow['win_count'],
                            'lossCount'    => (int)$dRow['loss_count'],
                            'skippedCount' => (int)$dRow['skipped_count'],
                            'totalProfit'  => (float)$dRow['total_profit']
                        ];
                    }

                    foreach ($treeServers as &$s) {
                        foreach ($s['rounds'] as &$r) {
                            $r['totalAssets'] = count($r['assets']);
                        }
                        $s['rounds'] = array_values($s['rounds']);
                    }
                    unset($s, $r);
                    $formattedServers = array_values($treeServers);
                }
            }

            // Calculate display date (dd/mm/yyyy)
            $dateDisplay = $dateVal;
            $dParts = explode('-', $dateVal);
            if (count($dParts) === 3) {
                $dateDisplay = sprintf("%02d/%02d/%04d", (int)$dParts[2], (int)$dParts[1], (int)$dParts[0]);
            }

            echo json_encode([
                'success'      => true,
                'date_val'     => $dateVal,
                'date_display' => $dateDisplay,
                'total_servers'=> count($formattedServers),
                'servers'      => $formattedServers
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_asset_trades':
            // 3. Get individual trades from vpsTradeData for selected asset and round
            $serverCode = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : 0;
            $tradeRoundNo = isset($_GET['tradeRoundNo']) ? intval($_GET['tradeRoundNo']) : 0;
            $assetCode = isset($_GET['assetCode']) ? trim($_GET['assetCode']) : '';
            $dateInput = isset($_GET['date']) ? trim($_GET['date']) : '';

            $dateVal = '';
            if (!empty($dateInput)) {
                $dateVal = $dateInput;
                if (strpos($dateInput, '/') !== false) {
                    $parts = explode('/', $dateInput);
                    if (count($parts) === 3) {
                        $day = intval($parts[0]);
                        $month = intval($parts[1]);
                        $year = intval($parts[2]);
                        if ($year > 2400) $year -= 543;
                        $dateVal = sprintf("%04d-%02d-%02d", $year, $month, $day);
                    }
                }
            }

            $sql = "SELECT 
                        id,
                        serverCode,
                        tradeRoundNo,
                        assetCode,
                        tradeNo,
                        subTradeNo,
                        purchaseTimeDisplay,
                        sellTimeDisplay,
                        thisAction,
                        WinStatus,
                        winCon,
                        lossCon,
                        MoneyTrade,
                        ThisProfit,
                        GrandBalance,
                        entrySpot,
                        exitSpot,
                        diffSpot,
                        tradeStrategy,
                        codeStrategy,
                        code_no,
                        isAnomaly
                    FROM vpsTradeData
                    WHERE serverCode = :serverCode
                      AND tradeRoundNo = :tradeRoundNo
                      AND assetCode = :assetCode";
            $tradeParams = [
                ':serverCode'   => $serverCode,
                ':tradeRoundNo' => $tradeRoundNo,
                ':assetCode'    => $assetCode
            ];
            if (!empty($dateVal)) {
                $sqlWithDate = $sql . " AND (DATE(FROM_UNIXTIME(COALESCE(NULLIF(timeCandle, 0), NULLIF(purchaseTime, 0)))) = :tradeDate 
                               OR (COALESCE(timeCandle, 0) = 0 AND COALESCE(purchaseTime, 0) = 0 AND DATE(created_at) = :tradeDate))";
                $tradeParams[':tradeDate'] = $dateVal;
                $stmt = $db->prepare($sqlWithDate . " ORDER BY id ASC");
                $stmt->execute($tradeParams);
                $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // If no trades found with date filter but tradeRoundNo is specified, fallback to round query without date restriction
                if (empty($trades) && $tradeRoundNo > 0) {
                    unset($tradeParams[':tradeDate']);
                    $stmt = $db->prepare($sql . " ORDER BY id ASC");
                    $stmt->execute($tradeParams);
                    $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } else {
                $sql .= " ORDER BY id ASC";
                $stmt = $db->prepare($sql);
                $stmt->execute($tradeParams);
                $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Fallback to derivTradeHistory if no trades in vpsTradeData
            if (empty($trades)) {
                $tz = new DateTimeZone('Asia/Bangkok');
                $startEpoch = !empty($dateVal) ? (new DateTime("{$dateVal} 00:00:00", $tz))->getTimestamp() : 0;
                $endEpoch   = !empty($dateVal) ? (new DateTime("{$dateVal} 23:59:59", $tz))->getTimestamp() : 0;

                $dSql = "SELECT 
                            id,
                            serverCode,
                            1 AS tradeRoundNo,
                            symbol AS assetCode,
                            id AS tradeNo,
                            1 AS subTradeNo,
                            purchase_time,
                            sell_time,
                            contract_type AS thisAction,
                            buy_price AS MoneyTrade,
                            profit AS ThisProfit,
                            shortcode AS tradeStrategy,
                            contract_type AS codeStrategy
                         FROM derivTradeHistory
                         WHERE serverCode = :serverCode
                           AND symbol = :assetCode";
                $dParams = [':serverCode' => $serverCode, ':assetCode' => $assetCode];
                if ($startEpoch > 0 && $endEpoch > 0) {
                    $dSql .= " AND purchase_time BETWEEN :s AND :e";
                    $dParams[':s'] = $startEpoch;
                    $dParams[':e'] = $endEpoch;
                }
                $dSql .= " ORDER BY purchase_time ASC";
                $dStmt = $db->prepare($dSql);
                $dStmt->execute($dParams);
                $dRows = $dStmt->fetchAll(PDO::FETCH_ASSOC);

                $runningBal = 0;
                $lossCon = 0;
                $winCon = 0;
                foreach ($dRows as $dr) {
                    $p = floatval($dr['ThisProfit']);
                    $runningBal += $p;
                    if ($p > 0) {
                        $winStatus = 'WIN';
                        $winCon++;
                        $lossCon = 0;
                    } elseif ($p < 0) {
                        $winStatus = 'LOSS';
                        $lossCon++;
                        $winCon = 0;
                    } else {
                        $winStatus = 'TIE';
                    }
                    $pTime = intval($dr['purchase_time']);
                    $sTime = intval($dr['sell_time']);
                    $trades[] = [
                        'id'                  => $dr['id'],
                        'serverCode'          => $dr['serverCode'],
                        'tradeRoundNo'        => 1,
                        'assetCode'           => $dr['assetCode'],
                        'tradeNo'             => $dr['tradeNo'],
                        'subTradeNo'          => 1,
                        'purchaseTimeDisplay' => $pTime > 0 ? (new DateTime("@$pTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : '-',
                        'sellTimeDisplay'     => $sTime > 0 ? (new DateTime("@$sTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : '-',
                        'thisAction'          => $dr['thisAction'],
                        'WinStatus'           => $winStatus,
                        'winCon'              => $winCon,
                        'lossCon'             => $lossCon,
                        'MoneyTrade'          => floatval($dr['MoneyTrade']),
                        'ThisProfit'          => $p,
                        'GrandBalance'        => round($runningBal, 2),
                        'entrySpot'           => null,
                        'exitSpot'            => null,
                        'diffSpot'            => null,
                        'tradeStrategy'       => $dr['tradeStrategy'],
                        'codeStrategy'        => $dr['codeStrategy'],
                        'code_no'             => null,
                        'isAnomaly'           => 0
                    ];
                }
            }

            echo json_encode([
                'success'      => true,
                'total_trades' => count($trades),
                'trades'       => $trades
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_asset_chart_data':
            // 4. Get comprehensive chart and trade data for an asset in a round
            $serverCode = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : 0;
            $tradeRoundNo = isset($_GET['tradeRoundNo']) ? intval($_GET['tradeRoundNo']) : 0;
            $assetCode = isset($_GET['assetCode']) ? trim($_GET['assetCode']) : '';
            $dateInput = isset($_GET['date']) ? trim($_GET['date']) : '';

            $dateVal = '';
            if (!empty($dateInput)) {
                $dateVal = $dateInput;
                if (strpos($dateInput, '/') !== false) {
                    $parts = explode('/', $dateInput);
                    if (count($parts) === 3) {
                        $day = intval($parts[0]);
                        $month = intval($parts[1]);
                        $year = intval($parts[2]);
                        if ($year > 2400) $year -= 543;
                        $dateVal = sprintf("%04d-%02d-%02d", $year, $month, $day);
                    }
                }
            }

            $tz = new DateTimeZone('Asia/Bangkok');
            if (empty($dateVal)) {
                $dateVal = (new DateTime('now', $tz))->format('Y-m-d');
            }

            $todayDate = (new DateTime('now', $tz))->format('Y-m-d');
            $isToday = ($dateVal === $todayDate);
            $dayStart0005Epoch = (new DateTime("{$dateVal} 00:05:00", $tz))->getTimestamp();
            $dayEndEpoch = $isToday ? time() : (new DateTime("{$dateVal} 23:59:59", $tz))->getTimestamp();
            $currentTimeDisplay = (new DateTime('now', $tz))->format('H:i:s');

            $roundInfo = null;
            $allRoundsList = [];

            // 1. If tradeRoundNo <= 0: ALL ROUNDS of the day!
            if ($tradeRoundNo <= 0) {
                // Query all rounds for this server, asset, and date
                $allRoundsSql = "SELECT 
                                    th.*,
                                    COALESCE(vm.vpsName, CONCAT('Server #', th.serverCode)) AS serverName,
                                    COALESCE(vm.publicIP, '') AS publicIP
                                FROM tradeHead th
                                LEFT JOIN vpsMaster vm ON (th.serverCode = vm.vpsCode OR th.serverCode = vm.id)
                                WHERE th.serverCode = :serverCode 
                                  AND th.assetCode = :assetCode";
                $allRoundsParams = [
                    ':serverCode' => $serverCode,
                    ':assetCode'  => $assetCode
                ];
                if (!empty($dateVal)) {
                    $allRoundsSql .= " AND (DATE(th.startTimeTrade) = :tradeDate OR DATE(th.created_at) = :tradeDate)";
                    $allRoundsParams[':tradeDate'] = $dateVal;
                }
                $allRoundsSql .= " ORDER BY th.tradeRoundNo ASC, th.id ASC";
                $arStmt = $db->prepare($allRoundsSql);
                $arStmt->execute($allRoundsParams);
                $allRoundsList = $arStmt->fetchAll(PDO::FETCH_ASSOC);

                $maxW = 0;
                $maxL = 0;
                $strategyNames = [];
                foreach ($allRoundsList as $r) {
                    if (intval($r['MaxWinCon']) > $maxW) $maxW = intval($r['MaxWinCon']);
                    if (intval($r['MaxLossCon']) > $maxL) $maxL = intval($r['MaxLossCon']);
                    if (!empty($r['usestrategyCode']) && !in_array($r['usestrategyCode'], $strategyNames)) {
                        $strategyNames[] = $r['usestrategyCode'];
                    }
                }

                $firstStart = !empty($allRoundsList) ? $allRoundsList[0]['startTimeTrade'] : '-';
                $lastStop = !empty($allRoundsList) ? $allRoundsList[count($allRoundsList)-1]['stopTimeTrade'] : '-';
                $serverName = !empty($allRoundsList) ? $allRoundsList[0]['serverName'] : ('Server #' . $serverCode);
                $publicIP = !empty($allRoundsList) ? $allRoundsList[0]['publicIP'] : '';

                $roundInfo = [
                    'serverCode'        => $serverCode,
                    'serverName'        => $serverName,
                    'publicIP'          => $publicIP,
                    'tradeRoundNo'      => 0,
                    'tradeRoundDisplay' => 'All (' . count($allRoundsList) . ' Rounds)',
                    'totalRounds'       => count($allRoundsList),
                    'assetCode'         => $assetCode,
                    'usestrategyCode'   => !empty($strategyNames) ? implode(', ', $strategyNames) : 'All Rounds',
                    'startTimeTrade'    => $firstStart,
                    'stopTimeTrade'     => $lastStop,
                    'durationTrade'     => count($allRoundsList) > 0 ? (count($allRoundsList) . ' Rounds') : '-',
                    'MaxWinCon'         => $maxW,
                    'MaxLossCon'        => $maxL
                ];

                // Get all trade records from vpsTradeData for ALL rounds of the date
                $tradeSql = "SELECT 
                                id,
                                serverCode,
                                tradeRoundNo,
                                assetCode,
                                tradeNo,
                                subTradeNo,
                                timeCandle,
                                timeCandleDisplay,
                                purchaseTime,
                                purchaseTimeDisplay,
                                sellTime,
                                sellTimeDisplay,
                                thisAction,
                                thisColor,
                                targetColor,
                                codeStrategy,
                                code_no,
                                WinStatus,
                                winCon,
                                lossCon,
                                MoneyTrade,
                                ThisProfit,
                                GrandBalance,
                                entrySpot,
                                exitSpot,
                                diffSpot,
                                tradeStrategy,
                                isAnomaly
                             FROM vpsTradeData
                             WHERE serverCode = :serverCode
                               AND assetCode = :assetCode";
                $tradeParams = [
                    ':serverCode' => $serverCode,
                    ':assetCode'  => $assetCode
                ];
                if (!empty($dateVal)) {
                    $tradeSql .= " AND (DATE(FROM_UNIXTIME(COALESCE(NULLIF(timeCandle, 0), NULLIF(purchaseTime, 0)))) = :tradeDate 
                                   OR (COALESCE(timeCandle, 0) = 0 AND COALESCE(purchaseTime, 0) = 0 AND DATE(created_at) = :tradeDate))";
                    $tradeParams[':tradeDate'] = $dateVal;
                }
                $tradeSql .= " ORDER BY COALESCE(NULLIF(timeCandle, 0), purchaseTime) ASC, id ASC";
                $tradeStmt = $db->prepare($tradeSql);
                $tradeStmt->execute($tradeParams);
                $trades = $tradeStmt->fetchAll(PDO::FETCH_ASSOC);

            } else {
                // 2. Specific single round requested (tradeRoundNo > 0)
                $headSql = "SELECT 
                                th.*,
                                COALESCE(vm.vpsName, CONCAT('Server #', th.serverCode)) AS serverName,
                                COALESCE(vm.publicIP, '') AS publicIP
                            FROM tradeHead th
                            LEFT JOIN vpsMaster vm ON (th.serverCode = vm.vpsCode OR th.serverCode = vm.id)
                            WHERE th.serverCode = :serverCode 
                              AND th.tradeRoundNo = :tradeRoundNo
                              AND th.assetCode = :assetCode";
                $headParams = [
                    ':serverCode'   => $serverCode,
                    ':tradeRoundNo' => $tradeRoundNo,
                    ':assetCode'    => $assetCode
                ];
                if (!empty($dateVal)) {
                    $headSql .= " AND (DATE(th.startTimeTrade) = :tradeDate OR DATE(th.created_at) = :tradeDate)";
                    $headParams[':tradeDate'] = $dateVal;
                }
                $headSql .= " ORDER BY th.id DESC LIMIT 1";
                $headStmt = $db->prepare($headSql);
                $headStmt->execute($headParams);
                $roundInfo = $headStmt->fetch(PDO::FETCH_ASSOC);

                // Fallback if not matched with specific date
                if (!$roundInfo && !empty($dateVal)) {
                    $headFallbackSql = "SELECT 
                                    th.*,
                                    COALESCE(vm.vpsName, CONCAT('Server #', th.serverCode)) AS serverName,
                                    COALESCE(vm.publicIP, '') AS publicIP
                                FROM tradeHead th
                                LEFT JOIN vpsMaster vm ON (th.serverCode = vm.vpsCode OR th.serverCode = vm.id)
                                WHERE th.serverCode = :serverCode 
                                  AND th.tradeRoundNo = :tradeRoundNo
                                  AND th.assetCode = :assetCode
                                ORDER BY th.id DESC LIMIT 1";
                    $hStmt = $db->prepare($headFallbackSql);
                    $hStmt->execute([
                        ':serverCode'   => $serverCode,
                        ':tradeRoundNo' => $tradeRoundNo,
                        ':assetCode'    => $assetCode
                    ]);
                    $roundInfo = $hStmt->fetch(PDO::FETCH_ASSOC);
                }

                // Get trade records for this specific round
                $tradeSql = "SELECT 
                                id,
                                serverCode,
                                tradeRoundNo,
                                assetCode,
                                tradeNo,
                                subTradeNo,
                                timeCandle,
                                timeCandleDisplay,
                                purchaseTime,
                                purchaseTimeDisplay,
                                sellTime,
                                sellTimeDisplay,
                                thisAction,
                                thisColor,
                                targetColor,
                                codeStrategy,
                                code_no,
                                WinStatus,
                                winCon,
                                lossCon,
                                MoneyTrade,
                                ThisProfit,
                                GrandBalance,
                                entrySpot,
                                exitSpot,
                                diffSpot,
                                tradeStrategy,
                                isAnomaly
                             FROM vpsTradeData
                             WHERE serverCode = :serverCode
                               AND tradeRoundNo = :tradeRoundNo
                               AND assetCode = :assetCode";
                $tradeParams = [
                    ':serverCode'   => $serverCode,
                    ':tradeRoundNo' => $tradeRoundNo,
                    ':assetCode'    => $assetCode
                ];
                if (!empty($dateVal)) {
                    $tradeSql .= " AND (DATE(FROM_UNIXTIME(COALESCE(NULLIF(timeCandle, 0), NULLIF(purchaseTime, 0)))) = :tradeDate 
                                   OR (COALESCE(timeCandle, 0) = 0 AND COALESCE(purchaseTime, 0) = 0 AND DATE(created_at) = :tradeDate))";
                    $tradeParams[':tradeDate'] = $dateVal;
                }
                $tradeSql .= " ORDER BY timeCandle ASC, id ASC";
                $tradeStmt = $db->prepare($tradeSql);
                $tradeStmt->execute($tradeParams);
                $trades = $tradeStmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($trades) && !empty($dateVal)) {
                    unset($tradeParams[':tradeDate']);
                    $tradeSqlNoDate = "SELECT * FROM vpsTradeData WHERE serverCode = :serverCode AND tradeRoundNo = :tradeRoundNo AND assetCode = :assetCode ORDER BY timeCandle ASC, id ASC";
                    $tradeStmt = $db->prepare($tradeSqlNoDate);
                    $tradeStmt->execute($tradeParams);
                    $trades = $tradeStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            $isBrokerFallback = false;

            // Fallback to derivTradeHistory if no trades in vpsTradeData
            if (empty($trades)) {
                $isBrokerFallback = true;
                $startEpoch = !empty($dateVal) ? (new DateTime("{$dateVal} 00:00:00", $tz))->getTimestamp() : 0;
                $endEpoch   = !empty($dateVal) ? (new DateTime("{$dateVal} 23:59:59", $tz))->getTimestamp() : 0;

                $dSql = "SELECT * FROM derivTradeHistory
                         WHERE serverCode = :serverCode
                           AND symbol = :assetCode";
                $dParams = [':serverCode' => $serverCode, ':assetCode' => $assetCode];
                if ($startEpoch > 0 && $endEpoch > 0) {
                    $dSql .= " AND purchase_time BETWEEN :s AND :e";
                    $dParams[':s'] = $startEpoch;
                    $dParams[':e'] = $endEpoch;
                }
                $dSql .= " ORDER BY purchase_time ASC";
                $dStmt = $db->prepare($dSql);
                $dStmt->execute($dParams);
                $dRows = $dStmt->fetchAll(PDO::FETCH_ASSOC);

                $runningBal = 0;
                $lossCon = 0;
                $winCon = 0;
                $maxW = 0;
                $maxL = 0;

                foreach ($dRows as $dr) {
                    $pTime = intval($dr['purchase_time']);
                    $sTime = intval($dr['sell_time']);
                    $profit = floatval($dr['profit']);
                    $buyPrice = floatval($dr['buy_price']);
                    $action = strtoupper(trim($dr['contract_type'] ?? ''));
                    $color = $action === 'PUT' ? 'Red' : ($action === 'CALL' ? 'Green' : $action);

                    if ($profit > 0) {
                        $winStatus = 'WIN';
                        $winCon++;
                        $lossCon = 0;
                        if ($winCon > $maxW) $maxW = $winCon;
                    } elseif ($profit < 0) {
                        $winStatus = 'LOSS';
                        $lossCon++;
                        $winCon = 0;
                        if ($lossCon > $maxL) $maxL = $lossCon;
                    } else {
                        $winStatus = 'TIE';
                    }

                    $runningBal += $profit;

                    $trades[] = [
                        'id'                  => $dr['id'],
                        'serverCode'          => $dr['serverCode'],
                        'tradeRoundNo'        => 1,
                        'assetCode'           => $dr['symbol'],
                        'tradeNo'             => $dr['id'],
                        'subTradeNo'          => 1,
                        'timeCandle'          => $pTime,
                        'timeCandleDisplay'   => $pTime > 0 ? (new DateTime("@$pTime"))->setTimezone($tz)->format('H:i:s') : '-',
                        'purchaseTime'        => $pTime,
                        'purchaseTimeDisplay' => $pTime > 0 ? (new DateTime("@$pTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : '-',
                        'sellTime'            => $sTime,
                        'sellTimeDisplay'     => $sTime > 0 ? (new DateTime("@$sTime"))->setTimezone($tz)->format('Y-m-d H:i:s') : '-',
                        'thisAction'          => $action,
                        'thisColor'           => $color,
                        'targetColor'         => null,
                        'codeStrategy'        => $action,
                        'code_no'             => null,
                        'WinStatus'           => $winStatus,
                        'winCon'              => $winCon,
                        'lossCon'             => $lossCon,
                        'MoneyTrade'          => $buyPrice,
                        'ThisProfit'          => $profit,
                        'GrandBalance'        => round($runningBal, 2),
                        'entrySpot'           => null,
                        'exitSpot'            => null,
                        'diffSpot'            => null,
                        'tradeStrategy'       => $dr['shortcode'] ?: ($dr['longcode'] ?: 'Deriv Broker History'),
                        'isAnomaly'           => 0
                    ];
                }

                // If roundInfo was missing, synthesize it
                if (!$roundInfo) {
                    $vpsInfo = $db->query("SELECT vpsName, publicIP FROM vpsMaster WHERE vpsCode = " . intval($serverCode) . " OR id = " . intval($serverCode))->fetch(PDO::FETCH_ASSOC);
                    $firstT = !empty($trades) ? $trades[0]['purchaseTime'] : time();
                    $lastT = !empty($trades) ? $trades[count($trades)-1]['purchaseTime'] : time();
                    $durSec = max(0, $lastT - $firstT);
                    $durStr = sprintf("%02d:%02d:%02d", floor($durSec / 3600), floor(($durSec % 3600) / 60), $durSec % 60);

                    $roundInfo = [
                        'serverCode'      => $serverCode,
                        'serverName'      => $vpsInfo['vpsName'] ?? ('Server #' . $serverCode),
                        'publicIP'        => $vpsInfo['publicIP'] ?? '',
                        'tradeRoundNo'    => $tradeRoundNo > 0 ? $tradeRoundNo : 0,
                        'assetCode'       => $assetCode,
                        'usestrategyCode' => 'Deriv Broker History',
                        'startTimeTrade'  => (new DateTime("@$firstT"))->setTimezone($tz)->format('Y-m-d H:i:s'),
                        'stopTimeTrade'   => (new DateTime("@$lastT"))->setTimezone($tz)->format('Y-m-d H:i:s'),
                        'durationTrade'   => $durStr,
                        'totalAssets'     => 1,
                        'MaxWinCon'       => $maxW,
                        'MaxLossCon'      => $maxL
                    ];
                }
            }

            // Calculate min and max time for candle fetching
            $minTime = 0;
            $maxTime = 0;
            $winCount = 0;
            $lossCount = 0;
            $skippedCount = 0;
            $totalProfit = 0.0;

            foreach ($trades as $t) {
                $tc = intval($t['timeCandle']);
                $pt = intval($t['purchaseTime']);
                $tTime = $tc > 0 ? $tc : $pt;

                if ($tTime > 0) {
                    if ($minTime === 0 || $tTime < $minTime) $minTime = $tTime;
                    if ($tTime > $maxTime) $maxTime = $tTime;
                }

                $st = strtolower(trim($t['WinStatus'] ?: ''));
                if ($st === 'win') $winCount++;
                elseif ($st === 'loss') $lossCount++;
                elseif ($st === 'skipped') $skippedCount++;

                $totalProfit += floatval($t['ThisProfit'] ?: 0);
            }

            // Determine candle bounds:
            // If tradeRoundNo <= 0 (All rounds of the day):
            // Default candle range must span from 00:05:00 to now (or 23:59:59)
            if ($tradeRoundNo <= 0) {
                $candleQueryStart = $dayStart0005Epoch;
                $candleQueryEnd = $dayEndEpoch;
                $minTimeWithBuffer = $dayStart0005Epoch;
                $maxTimeWithBuffer = $dayEndEpoch;
            } else {
                // Buffer time by 15 minutes before and after the round trades
                $minTimeWithBuffer = $minTime > 0 ? ($minTime - 900) : $dayStart0005Epoch;
                $maxTimeWithBuffer = $maxTime > 0 ? ($maxTime + 900) : $dayEndEpoch;
                $candleQueryStart = $minTimeWithBuffer;
                $candleQueryEnd = $maxTimeWithBuffer;
            }

            // Check candles from MySQL candles table
            $candles = [];
            $hasCoverage = false;
            $firstCandleTime = 0;
            $lastCandleTime = 0;

            if ($candleQueryStart > 0 && $candleQueryEnd > 0) {
                $candleSql = "SELECT time, open, high, low, close 
                              FROM candles 
                              WHERE symbol = :symbol 
                                AND granularity = 60 
                                AND time BETWEEN :start AND :end 
                              ORDER BY time ASC";
                $cStmt = $db->prepare($candleSql);
                $cStmt->execute([
                    ':symbol' => $assetCode,
                    ':start'  => $candleQueryStart,
                    ':end'    => $candleQueryEnd
                ]);
                $rawCandles = $cStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rawCandles)) {
                    $firstCandleTime = intval($rawCandles[0]['time']);
                    $lastCandleTime = intval($rawCandles[count($rawCandles) - 1]['time']);

                    if ($tradeRoundNo <= 0) {
                        // For full day: check if coverage reaches within 5 minutes of candleQueryEnd
                        if ($isToday) {
                            if ($lastCandleTime >= ($dayEndEpoch - 300) && count($rawCandles) >= 30) {
                                $hasCoverage = true;
                            }
                        } else {
                            if ($lastCandleTime >= ($dayEndEpoch - 600) && count($rawCandles) >= 30) {
                                $hasCoverage = true;
                            }
                        }
                    } else {
                        if ($firstCandleTime <= $minTime && $lastCandleTime >= ($maxTime - 120)) {
                            $hasCoverage = true;
                        }
                    }

                    // Always provide all existing candles to frontend immediately
                    foreach ($rawCandles as $rc) {
                        $candles[] = [
                            'time'  => intval($rc['time']),
                            'open'  => floatval($rc['open']),
                            'high'  => floatval($rc['high']),
                            'low'   => floatval($rc['low']),
                            'close' => floatval($rc['close'])
                        ];
                    }
                }
            }

            echo json_encode([
                'success'    => true,
                'server'     => [
                    'serverCode' => $serverCode,
                    'serverName' => $roundInfo ? $roundInfo['serverName'] : ('Server #' . $serverCode),
                    'publicIP'   => $roundInfo ? $roundInfo['publicIP'] : ''
                ],
                'round'      => $roundInfo ?: [
                    'serverCode'      => $serverCode,
                    'tradeRoundNo'    => $tradeRoundNo,
                    'assetCode'       => $assetCode,
                    'usestrategyCode' => 'N/A',
                    'durationTrade'   => '-',
                    'MaxWinCon'       => 0,
                    'MaxLossCon'      => 0
                ],
                'summary'    => [
                    'totalTrades'  => count($trades),
                    'winCount'     => $winCount,
                    'lossCount'    => $lossCount,
                    'skippedCount' => $skippedCount,
                    'totalProfit'  => round($totalProfit, 2),
                    'minTime'      => $minTimeWithBuffer,
                    'maxTime'      => $maxTimeWithBuffer
                ],
                'date'             => $dateVal,
                'isToday'          => $isToday,
                'currentTime'      => $currentTimeDisplay,
                'dayStart0005'     => $dayStart0005Epoch,
                'dayEnd'           => $dayEndEpoch,
                'hasCoverage'      => $hasCoverage,
                'firstCandleTime'  => $firstCandleTime,
                'lastCandleTime'   => $lastCandleTime,
                'candles'          => $candles,
                'trades'           => $trades,
                'isBrokerFallback' => $isBrokerFallback
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_loss_con_summary':
            $serverCode = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : 0;
            $dateInput = isset($_GET['date']) ? trim($_GET['date']) : '';

            $dateVal = $dateInput;
            if (strpos($dateInput, '/') !== false) {
                $parts = explode('/', $dateInput);
                if (count($parts) === 3) {
                    $day = intval($parts[0]);
                    $month = intval($parts[1]);
                    $year = intval($parts[2]);
                    if ($year > 2400) $year -= 543;
                    $dateVal = sprintf("%04d-%02d-%02d", $year, $month, $day);
                }
            }

            // 1. Fetch tradeHead rows for this serverCode and date
            $thSql = "SELECT 
                        th.tradeRoundNo,
                        th.usestrategyCode,
                        th.startTimeTrade,
                        th.stopTimeTrade,
                        th.durationTrade,
                        th.assetCode,
                        th.MaxWinCon,
                        th.MaxLossCon
                      FROM tradeHead th
                      WHERE th.serverCode = :serverCode
                        AND (DATE(th.startTimeTrade) = :tradeDate OR DATE(th.created_at) = :tradeDate)
                      ORDER BY th.tradeRoundNo ASC, th.assetCode ASC";
            $thStmt = $db->prepare($thSql);
            $thStmt->execute([':serverCode' => $serverCode, ':tradeDate' => $dateVal]);
            $thRows = $thStmt->fetchAll(PDO::FETCH_ASSOC);

            $roundNos = array_values(array_unique(array_column($thRows, 'tradeRoundNo')));
            $roundProfit = [];
            $assetStats = [];

            if (!empty($roundNos)) {
                $inRounds = implode(',', array_map('intval', $roundNos));
                $vpsSql = "SELECT tradeRoundNo, assetCode, lossCon, winCon, purchaseTimeDisplay, timeCandleDisplay, ThisProfit 
                           FROM vpsTradeData 
                           WHERE serverCode = :serverCode 
                             AND tradeRoundNo IN ($inRounds) 
                           ORDER BY id ASC";
                $vpsStmt = $db->prepare($vpsSql);
                $vpsStmt->execute([':serverCode' => $serverCode]);
                $vpsRows = $vpsStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($vpsRows as $v) {
                    $rNo = intval($v['tradeRoundNo']);
                    $aCode = $v['assetCode'];
                    $lCon = intval($v['lossCon'] ?? 0);
                    $wCon = intval($v['winCon'] ?? 0);
                    $roundProfit[$rNo] = ($roundProfit[$rNo] ?? 0) + floatval($v['ThisProfit'] ?? 0);

                    $timeCandidate = (!empty($v['purchaseTimeDisplay']) && $v['purchaseTimeDisplay'] !== '-') 
                        ? $v['purchaseTimeDisplay'] 
                        : (!empty($v['timeCandleDisplay']) && $v['timeCandleDisplay'] !== '-' ? $v['timeCandleDisplay'] : '');

                    if (!isset($assetStats[$rNo][$aCode])) {
                        $assetStats[$rNo][$aCode] = [
                            'maxLossCon' => 0,
                            'maxWinCon' => 0,
                            'timeOfMaxLossCon' => '-',
                            'hasRealTime' => false
                        ];
                    }
                    if ($wCon > $assetStats[$rNo][$aCode]['maxWinCon']) {
                        $assetStats[$rNo][$aCode]['maxWinCon'] = $wCon;
                    }
                    if ($lCon > $assetStats[$rNo][$aCode]['maxLossCon']) {
                        $assetStats[$rNo][$aCode]['maxLossCon'] = $lCon;
                        $assetStats[$rNo][$aCode]['timeOfMaxLossCon'] = $timeCandidate ?: '-';
                        $assetStats[$rNo][$aCode]['hasRealTime'] = !empty($timeCandidate);
                    } else if ($lCon === $assetStats[$rNo][$aCode]['maxLossCon'] && $lCon > 0) {
                        if (!$assetStats[$rNo][$aCode]['hasRealTime'] && !empty($timeCandidate)) {
                            $assetStats[$rNo][$aCode]['timeOfMaxLossCon'] = $timeCandidate;
                            $assetStats[$rNo][$aCode]['hasRealTime'] = true;
                        }
                    }
                }
            }

            $distinctAssets = [];
            $rounds = [];
            foreach ($thRows as $row) {
                $rNo = intval($row['tradeRoundNo']);
                $aCode = $row['assetCode'];
                if (!in_array($aCode, $distinctAssets)) {
                    $distinctAssets[] = $aCode;
                }
                if (!isset($rounds[$rNo])) {
                    $rounds[$rNo] = [
                        'tradeRoundNo'   => $rNo,
                        'startTimeTrade' => $row['startTimeTrade'] ?: '-',
                        'stopTimeTrade'  => $row['stopTimeTrade'] ?: '-',
                        'durationTrade'  => $row['durationTrade'] ?: '-',
                        'strategy'       => $row['usestrategyCode'] ?: 'N/A',
                        'profit'         => round($roundProfit[$rNo] ?? 0, 2),
                        'assets'         => []
                    ];
                }
                $calcMaxLoss = max(intval($row['MaxLossCon']), $assetStats[$rNo][$aCode]['maxLossCon'] ?? 0);
                $calcMaxWin = max(intval($row['MaxWinCon']), $assetStats[$rNo][$aCode]['maxWinCon'] ?? 0);
                $rounds[$rNo]['assets'][$aCode] = [
                    'assetCode'        => $aCode,
                    'maxLossCon'       => $calcMaxLoss,
                    'maxWinCon'        => $calcMaxWin,
                    'timeOfMaxLossCon' => $assetStats[$rNo][$aCode]['timeOfMaxLossCon'] ?? '-'
                ];
            }
            sort($distinctAssets);

            echo json_encode([
                'success'        => true,
                'serverCode'     => $serverCode,
                'date'           => $dateVal,
                'distinctAssets' => $distinctAssets,
                'rounds'         => array_values($rounds)
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode([
                'success' => false,
                'error'   => 'Unknown action: ' . htmlspecialchars($action)
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
