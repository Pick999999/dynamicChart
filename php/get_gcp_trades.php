<?php
/**
 * get_gcp_trades.php
 * PHP API สำหรับดึงข้อมูลการเทรดจากตาราง vpsTradeData ตามวันที่ หรือ ช่วงเวลา Epoch และชื่อสัญลักษณ์ (symbol)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    if (empty($_GET) && !empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $_GET);
    }

    $symbol = isset($_GET['symbol']) ? trim($_GET['symbol']) : '';
    $dateInput = isset($_GET['date']) ? trim($_GET['date']) : '';
    $startParam = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $endParam = isset($_GET['end']) ? intval($_GET['end']) : 0;

    $startEpoch = null;
    $endEpoch = null;
    $dateStr = null;

    if (!empty($dateInput)) {
        // รองรับทั้งรูปแบบ dd/mm/yyyy (พ.ศ.) และ yyyy-mm-dd (ค.ศ.)
        if (strpos($dateInput, '/') !== false) {
            $parts = explode('/', $dateInput);
            if (count($parts) === 3) {
                $day = intval($parts[0]);
                $month = intval($parts[1]);
                $year = intval($parts[2]);
                if ($year > 2400) {
                    $year -= 543; // แปลง พ.ศ. -> ค.ศ.
                }
                $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $day);
            }
        } elseif (strpos($dateInput, '-') !== false) {
            $dateStr = $dateInput;
        }

        if ($dateStr) {
            $timezone = new DateTimeZone('Asia/Bangkok');
            $startOfDay = new DateTime("{$dateStr} 00:00:00", $timezone);
            $endOfDay = new DateTime("{$dateStr} 23:59:59", $timezone);
            $startEpoch = $startOfDay->getTimestamp();
            $endEpoch = $endOfDay->getTimestamp();
        }
    }

    if ($startParam > 0 && $endParam > 0) {
        $startEpoch = $startParam;
        $endEpoch = $endParam;
    }

    $serverCode = isset($_GET['serverCode']) && trim((string)$_GET['serverCode']) !== '' ? trim((string)$_GET['serverCode']) : null;

    $conditions = [];
    $params = [];

    if (!empty($symbol)) {
        $conditions[] = "(symbol = :symbol OR assetCode = :symbol)";
        $params[':symbol'] = $symbol;
    }

    if ($serverCode !== null) {
        $conditions[] = "serverCode = :serverCode";
        $params[':serverCode'] = $serverCode;
    }

    if ($startEpoch !== null && $endEpoch !== null) {
        $conditions[] = "(purchaseTime BETWEEN :startEpoch AND :endEpoch OR timeCandle BETWEEN :startEpoch AND :endEpoch)";
        $params[':startEpoch'] = $startEpoch;
        $params[':endEpoch'] = $endEpoch;
    }

    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    $sql = "SELECT * FROM vpsTradeData {$whereClause} ORDER BY purchaseTime ASC, tradeNo ASC, subTradeNo ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedRows = [];
    foreach ($rows as $r) {
        $formattedRows[] = [
            'id'                   => intval($r['id']),
            'serverCode'           => isset($r['serverCode']) && $r['serverCode'] !== null ? intval($r['serverCode']) : 2,
            'symbol'               => $r['symbol'],
            'contractId'           => $r['contractId'] !== null ? intval($r['contractId']) : null,
            'buyId'                => $r['buyId'] !== null ? intval($r['buyId']) : null,
            'sellId'               => $r['sellId'] !== null ? intval($r['sellId']) : null,
            'assetCode'            => $r['assetCode'],
            'entrySpot'            => $r['entrySpot'] !== null ? floatval($r['entrySpot']) : null,
            'exitSpot'             => $r['exitSpot'] !== null ? floatval($r['exitSpot']) : null,
            'DiffSpot'             => $r['diffSpot'] !== null ? floatval($r['diffSpot']) : null,
            'purchaseTime'         => $r['purchaseTime'] !== null ? intval($r['purchaseTime']) : null,
            'purchaseTimeDisplay'  => $r['purchaseTimeDisplay'],
            'tradeRoundNo'         => isset($r['tradeRoundNo']) && $r['tradeRoundNo'] !== null ? intval($r['tradeRoundNo']) : (isset($r['scheduleTradeNo']) && $r['scheduleTradeNo'] !== null ? intval($r['scheduleTradeNo']) : null),
            'scheduleTradeNo'      => isset($r['tradeRoundNo']) && $r['tradeRoundNo'] !== null ? intval($r['tradeRoundNo']) : (isset($r['scheduleTradeNo']) && $r['scheduleTradeNo'] !== null ? intval($r['scheduleTradeNo']) : null),
            'tradeNo'              => $r['tradeNo'] !== null ? intval($r['tradeNo']) : null,
            'subTradeno'           => $r['subTradeNo'] !== null ? intval($r['subTradeNo']) : null,
            'timeCandle'           => $r['timeCandle'] !== null ? intval($r['timeCandle']) : null,
            'timeCandleDisplay'    => $r['timeCandleDisplay'],
            'sellTime'             => $r['sellTime'] !== null ? intval($r['sellTime']) : null,
            'sellTimeDisplay'      => $r['sellTimeDisplay'],
            'actualDuration'       => $r['actualDuration'] !== null ? intval($r['actualDuration']) : null,
            'isAnomaly'            => intval($r['isAnomaly']),
            'thisColor'            => $r['thisColor'],
            'thisAction'           => $r['thisAction'],
            'targetColor'          => $r['targetColor'],
            'emaShortDirection'    => $r['emaShortDirection'],
            'emaMediumDirection'   => $r['emaMediumDirection'],
            'MoneyTrade'           => $r['MoneyTrade'] !== null ? floatval($r['MoneyTrade']) : null,
            'WinStatus'            => $r['WinStatus'],
            'lossCon'              => $r['lossCon'] !== null ? intval($r['lossCon']) : null,
            'ThisProfit'           => $r['ThisProfit'] !== null ? floatval($r['ThisProfit']) : null,
            'GrandBalance'         => $r['GrandBalance'] !== null ? floatval($r['GrandBalance']) : null,
            'gaveUp'               => intval($r['gaveUp']),
            'maxLossCon'           => $r['maxLossCon'] !== null ? intval($r['maxLossCon']) : null,
            'tradeStrategy'        => $r['tradeStrategy'],
            'code_no'              => isset($r['code_no']) && $r['code_no'] !== null ? intval($r['code_no']) : null,
            'codeStrategy'         => $r['codeStrategy'],
            'noiseCode'            => $r['noiseCode'],
            'spotPriceCall'        => $r['spotPriceCall'] !== null ? floatval($r['spotPriceCall']) : null,
            'spotPricePut'         => $r['spotPricePut'] !== null ? floatval($r['spotPricePut']) : null,
            'spotCallPositionCode' => $r['spotCallPositionCode'],
            'spotPutPositionCode'  => $r['spotPutPositionCode'],
            'whipsawZone'          => intval($r['whipsawZone']),
            'created_at'           => $r['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'count'   => count($formattedRows),
        'trades'  => $formattedRows
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
