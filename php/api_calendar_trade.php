<?php
/**
 * api_calendar_trade.php
 * RESTful JSON API for Calendar Trade feature
 * Aggregates trading data from vpsTradeData and joins with vpsMaster
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    if (empty($_GET) && !empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $_GET);
    }

    $action = isset($_GET['action']) ? trim($_GET['action']) : 'month_summary';

    // -------------------------------------------------------------
    // ACTION: vps_list
    // Returns list of VPS from vpsMaster for filtering
    // -------------------------------------------------------------
    if ($action === 'vps_list') {
        $stmt = $db->query("SELECT id, vpsCode, vpsName, publicIP, portno, url, cloudProvider FROM vpsMaster ORDER BY CAST(vpsCode AS UNSIGNED) ASC, id ASC");
        $vpsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Also check if there are serverCodes in vpsTradeData that are not in vpsMaster
        $usedServers = $db->query("SELECT DISTINCT serverCode FROM vpsTradeData WHERE serverCode IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
        $masterCodes = array_map(function($v) { return (string)$v['vpsCode']; }, $vpsList);
        $masterIds = array_map(function($v) { return (string)$v['id']; }, $vpsList);

        foreach ($usedServers as $sc) {
            $scStr = (string)$sc;
            if (!in_array($scStr, $masterCodes) && !in_array($scStr, $masterIds)) {
                $vpsList[] = [
                    'id'            => $sc,
                    'vpsCode'       => $scStr,
                    'vpsName'       => 'Server #' . $scStr,
                    'publicIP'      => '',
                    'portno'        => '',
                    'url'           => '',
                    'cloudProvider' => 'Unknown'
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'data'    => $vpsList
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: month_summary
    // Aggregates trades by date and by VPS for a given month/year
    // -------------------------------------------------------------
    if ($action === 'month_summary') {
        $year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
        $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));
        $serverCodeFilter = isset($_GET['serverCode']) && trim((string)$_GET['serverCode']) !== '' ? trim((string)$_GET['serverCode']) : null;

        // Support Thai Buddhist year (e.g. 2569 -> 2026)
        if ($year > 2400) {
            $year -= 543;
        }

        // Expand range by +/- 7 days to cover calendar view margins (previous month end and next month start)
        $startRange = date('Y-m-d 00:00:00', strtotime("{$year}-{$month}-01 -7 days"));
        $endRange = date('Y-m-d 23:59:59', strtotime("{$year}-{$month}-01 +1 month +7 days"));

        $timezone = new DateTimeZone('Asia/Bangkok');
        $startEpoch = (new DateTime($startRange, $timezone))->getTimestamp();
        $endEpoch = (new DateTime($endRange, $timezone))->getTimestamp();

        $whereClause = "WHERE COALESCE(NULLIF(vt.purchaseTime, 0), vt.timeCandle) BETWEEN :startEpoch AND :endEpoch";
        $params = [
            ':startEpoch' => $startEpoch,
            ':endEpoch'   => $endEpoch
        ];

        if ($serverCodeFilter !== null) {
            $whereClause .= " AND vt.serverCode = :serverCode";
            $params[':serverCode'] = $serverCodeFilter;
        }

        $sql = "SELECT 
                    DATE(FROM_UNIXTIME(COALESCE(NULLIF(vt.purchaseTime, 0), vt.timeCandle))) AS tradeDate,
                    vt.serverCode,
                    COALESCE(NULLIF(vt.symbol, ''), vt.assetCode) AS assetCode,
                    COALESCE(vm.vpsName, CONCAT('Server #', vt.serverCode)) AS vpsName,
                    COALESCE(vm.vpsCode, CAST(vt.serverCode AS CHAR)) AS vpsCode,
                    COALESCE(vm.publicIP, '') AS publicIP,
                    COALESCE(vm.cloudProvider, 'Manual') AS cloudProvider,
                    COUNT(*) AS totalTrades,
                    SUM(CASE WHEN LOWER(vt.WinStatus) = 'win' THEN 1 ELSE 0 END) AS winCount,
                    SUM(CASE WHEN LOWER(vt.WinStatus) = 'loss' THEN 1 ELSE 0 END) AS lossCount,
                    SUM(CASE WHEN LOWER(vt.WinStatus) = 'skipped' THEN 1 ELSE 0 END) AS skipCount,
                    ROUND(COALESCE(SUM(vt.ThisProfit), 0), 2) AS totalProfit,
                    MIN(COALESCE(NULLIF(vt.purchaseTime, 0), vt.timeCandle)) AS firstTradeEpoch,
                    MAX(COALESCE(NULLIF(vt.purchaseTime, 0), vt.timeCandle)) AS lastTradeEpoch
                FROM vpsTradeData vt
                LEFT JOIN vpsMaster vm ON (vt.serverCode = vm.vpsCode OR vt.serverCode = vm.id)
                {$whereClause}
                GROUP BY tradeDate, vt.serverCode, assetCode
                ORDER BY tradeDate ASC, vt.serverCode ASC, assetCode ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by tradeDate and then by serverCode
        $grouped = [];
        $monthTotalTrades = 0;
        $monthWinCount = 0;
        $monthLossCount = 0;
        $monthSkipCount = 0;
        $monthProfit = 0.0;
        $monthVpsMap = [];
        $monthPrefix = sprintf('%04d-%02d', $year, $month);

        foreach ($rows as $row) {
            $tDate = $row['tradeDate'];
            $sc = $row['serverCode'];

            if (!isset($grouped[$tDate])) {
                $grouped[$tDate] = [];
            }

            if (!isset($grouped[$tDate][$sc])) {
                $grouped[$tDate][$sc] = [
                    'serverCode'      => $sc,
                    'vpsCode'         => $row['vpsCode'],
                    'vpsName'         => $row['vpsName'],
                    'publicIP'        => $row['publicIP'],
                    'cloudProvider'   => $row['cloudProvider'],
                    'totalTrades'     => 0,
                    'winCount'        => 0,
                    'lossCount'       => 0,
                    'skipCount'       => 0,
                    'totalProfit'     => 0.0,
                    'firstTradeEpoch' => $row['firstTradeEpoch'],
                    'lastTradeEpoch'  => $row['lastTradeEpoch'],
                    'assets'          => [],
                    'assetCodes'      => []
                ];
            }

            $trades = intval($row['totalTrades']);
            $win = intval($row['winCount']);
            $loss = intval($row['lossCount']);
            $skip = intval($row['skipCount']);
            $profit = floatval($row['totalProfit']);

            $grouped[$tDate][$sc]['totalTrades'] += $trades;
            $grouped[$tDate][$sc]['winCount'] += $win;
            $grouped[$tDate][$sc]['lossCount'] += $loss;
            $grouped[$tDate][$sc]['skipCount'] += $skip;
            $grouped[$tDate][$sc]['totalProfit'] = round($grouped[$tDate][$sc]['totalProfit'] + $profit, 2);

            $assetCode = $row['assetCode'];
            if ($assetCode) {
                $grouped[$tDate][$sc]['assets'][] = [
                    'assetCode'   => $assetCode,
                    'totalTrades' => $trades,
                    'winCount'    => $win,
                    'lossCount'   => $loss,
                    'skipCount'   => $skip,
                    'totalProfit' => $profit
                ];
                $grouped[$tDate][$sc]['assetCodes'][] = $assetCode;
            }

            // Monthly KPIs
            if (strpos($tDate, $monthPrefix) === 0) {
                $monthTotalTrades += $trades;
                $monthWinCount += $win;
                $monthLossCount += $loss;
                $monthSkipCount += $skip;
                $monthProfit += $profit;
                $monthVpsMap[$sc] = true;
            }
        }

        // Build daysData array
        $daysData = [];
        foreach ($grouped as $tDate => $vpsMap) {
            $dayTotalTrades = 0;
            $dayWinCount = 0;
            $dayLossCount = 0;
            $daySkipCount = 0;
            $dayProfit = 0.0;
            $vpsList = [];

            foreach ($vpsMap as $sc => $vpsInfo) {
                $dayTotalTrades += $vpsInfo['totalTrades'];
                $dayWinCount += $vpsInfo['winCount'];
                $dayLossCount += $vpsInfo['lossCount'];
                $daySkipCount += $vpsInfo['skipCount'];
                $dayProfit = round($dayProfit + $vpsInfo['totalProfit'], 2);

                $vpsInfo['firstTime'] = $vpsInfo['firstTradeEpoch'] ? date('H:i:s', $vpsInfo['firstTradeEpoch']) : null;
                $vpsInfo['lastTime'] = $vpsInfo['lastTradeEpoch'] ? date('H:i:s', $vpsInfo['lastTradeEpoch']) : null;
                $vpsInfo['symbols'] = implode(', ', $vpsInfo['assetCodes']);
                $vpsList[] = $vpsInfo;
            }

            $daysData[] = [
                'date'        => $tDate,
                'totalTrades' => $dayTotalTrades,
                'winCount'    => $dayWinCount,
                'lossCount'   => $dayLossCount,
                'skipCount'   => $daySkipCount,
                'totalProfit' => $dayProfit,
                'vpsList'     => $vpsList
            ];
        }

        // Available distinct trading months in the entire database (for quick dropdown selector)
        $monthStmt = $db->query("SELECT DISTINCT 
                                    DATE_FORMAT(FROM_UNIXTIME(COALESCE(NULLIF(purchaseTime, 0), timeCandle)), '%Y-%m') AS ym,
                                    COUNT(*) AS cnt
                                 FROM vpsTradeData 
                                 WHERE COALESCE(NULLIF(purchaseTime, 0), timeCandle) > 0
                                 GROUP BY ym 
                                 ORDER BY ym DESC");
        $availableMonths = $monthStmt->fetchAll(PDO::FETCH_ASSOC);

        $validTradeCount = $monthWinCount + $monthLossCount;
        $winRate = $validTradeCount > 0 ? round(($monthWinCount / $validTradeCount) * 100, 1) : 0;

        echo json_encode([
            'success' => true,
            'year'    => $year,
            'month'   => $month,
            'kpis'    => [
                'totalTrades'       => $monthTotalTrades,
                'winCount'          => $monthWinCount,
                'lossCount'         => $monthLossCount,
                'skipCount'         => $monthSkipCount,
                'netProfit'         => round($monthProfit, 2),
                'winRate'           => $winRate,
                'activeDays'        => count(array_filter($daysData, function($d) use ($monthPrefix) { return strpos($d['date'], $monthPrefix) === 0; })),
                'activeVpsCount'    => count($monthVpsMap)
            ],
            'availableMonths' => $availableMonths,
            'days'            => array_values($daysData)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: day_details
    // Fetches all individual trade records for a given date & optional VPS
    // -------------------------------------------------------------
    if ($action === 'day_details') {
        $dateStr = isset($_GET['date']) ? trim($_GET['date']) : '';
        if (empty($dateStr)) {
            throw new Exception('Missing date parameter (expected YYYY-MM-DD)');
        }

        // Check if format is dd/mm/yyyy
        if (strpos($dateStr, '/') !== false) {
            $p = explode('/', $dateStr);
            if (count($p) === 3) {
                $y = intval($p[2]);
                if ($y > 2400) $y -= 543;
                $dateStr = sprintf('%04d-%02d-%02d', $y, intval($p[1]), intval($p[0]));
            }
        }

        $timezone = new DateTimeZone('Asia/Bangkok');
        $startOfDay = new DateTime("{$dateStr} 00:00:00", $timezone);
        $endOfDay = new DateTime("{$dateStr} 23:59:59", $timezone);
        $startEpoch = $startOfDay->getTimestamp();
        $endEpoch = $endOfDay->getTimestamp();

        $serverCodeFilter = isset($_GET['serverCode']) && trim((string)$_GET['serverCode']) !== '' ? trim((string)$_GET['serverCode']) : null;

        $whereClause = "WHERE COALESCE(NULLIF(vt.purchaseTime, 0), vt.timeCandle) BETWEEN :startEpoch AND :endEpoch";
        $params = [
            ':startEpoch' => $startEpoch,
            ':endEpoch'   => $endEpoch
        ];

        if ($serverCodeFilter !== null) {
            $whereClause .= " AND vt.serverCode = :serverCode";
            $params[':serverCode'] = $serverCodeFilter;
        }

        $sql = "SELECT 
                    vt.*,
                    COALESCE(vm.vpsName, CONCAT('Server #', vt.serverCode)) AS vpsName,
                    COALESCE(vm.vpsCode, CAST(vt.serverCode AS CHAR)) AS vpsCode,
                    COALESCE(vm.publicIP, '') AS publicIP
                FROM vpsTradeData vt
                LEFT JOIN vpsMaster vm ON (vt.serverCode = vm.vpsCode OR vt.serverCode = vm.id)
                {$whereClause}
                ORDER BY vt.serverCode ASC, COALESCE(NULLIF(vt.purchaseTime, 0), vt.timeCandle) ASC, vt.tradeNo ASC, vt.subTradeNo ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate summary by VPS for this date
        $vpsSummary = [];
        $totalDayProfit = 0.0;
        $totalDayWin = 0;
        $totalDayLoss = 0;
        $totalDaySkip = 0;

        $formattedTrades = [];
        foreach ($trades as $t) {
            $sc = $t['serverCode'];
            if (!isset($vpsSummary[$sc])) {
                $vpsSummary[$sc] = [
                    'serverCode'  => $sc,
                    'vpsCode'     => $t['vpsCode'],
                    'vpsName'     => $t['vpsName'],
                    'publicIP'    => $t['publicIP'],
                    'totalTrades' => 0,
                    'winCount'    => 0,
                    'lossCount'   => 0,
                    'skipCount'   => 0,
                    'totalProfit' => 0.0
                ];
            }

            $wStatus = strtolower($t['WinStatus'] ?? '');
            $profit = floatval($t['ThisProfit'] ?? 0);
            $vpsSummary[$sc]['totalTrades']++;
            if ($wStatus === 'win') {
                $vpsSummary[$sc]['winCount']++;
                $totalDayWin++;
            } elseif ($wStatus === 'loss') {
                $vpsSummary[$sc]['lossCount']++;
                $totalDayLoss++;
            } else {
                $vpsSummary[$sc]['skipCount']++;
                $totalDaySkip++;
            }
            $vpsSummary[$sc]['totalProfit'] = round($vpsSummary[$sc]['totalProfit'] + $profit, 2);
            $totalDayProfit = round($totalDayProfit + $profit, 2);

            $epoch = intval($t['purchaseTime'] > 0 ? $t['purchaseTime'] : $t['timeCandle']);
            $formattedTrades[] = [
                'id'                  => intval($t['id']),
                'serverCode'          => $t['serverCode'],
                'vpsName'             => $t['vpsName'],
                'vpsCode'             => $t['vpsCode'],
                'symbol'              => !empty($t['symbol']) ? $t['symbol'] : $t['assetCode'],
                'assetCode'           => $t['assetCode'],
                'contractId'          => $t['contractId'],
                'tradeNo'             => $t['tradeNo'],
                'subTradeNo'          => $t['subTradeNo'],
                'tradeRoundNo'        => $t['tradeRoundNo'],
                'time'                => $epoch ? date('H:i:s', $epoch) : '-',
                'epoch'               => $epoch,
                'thisAction'          => $t['thisAction'],
                'thisColor'           => $t['thisColor'],
                'tradeStrategy'       => $t['tradeStrategy'],
                'codeStrategy'        => $t['codeStrategy'],
                'MoneyTrade'          => $t['MoneyTrade'] !== null ? floatval($t['MoneyTrade']) : null,
                'WinStatus'           => $t['WinStatus'],
                'ThisProfit'          => $t['ThisProfit'] !== null ? floatval($t['ThisProfit']) : 0.0,
                'spotPriceCall'       => $t['spotPriceCall'] !== null ? floatval($t['spotPriceCall']) : null,
                'spotPricePut'        => $t['spotPricePut'] !== null ? floatval($t['spotPricePut']) : null,
                'entrySpot'           => $t['entrySpot'] !== null ? floatval($t['entrySpot']) : null,
                'exitSpot'            => $t['exitSpot'] !== null ? floatval($t['exitSpot']) : null,
                'actualDuration'      => $t['actualDuration']
            ];
        }

        echo json_encode([
            'success'     => true,
            'date'        => $dateStr,
            'summary'     => [
                'totalTrades' => count($trades),
                'winCount'    => $totalDayWin,
                'lossCount'   => $totalDayLoss,
                'skipCount'   => $totalDaySkip,
                'totalProfit' => $totalDayProfit,
                'vpsSummary'  => array_values($vpsSummary)
            ],
            'trades'      => $formattedTrades
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    throw new Exception("Unknown action: {$action}");

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
