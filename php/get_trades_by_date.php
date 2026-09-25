<?php
/**
 * get_trades_by_date.php
 * PHP API สำหรับดึงรายการเทรดทั้งหมดจาก MySQL ตามวันที่ระบุ (พ.ศ.) และชื่อสินทรัพย์ที่เลือก
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if (!isset($_GET['date']) || !isset($_GET['symbol'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing date or symbol parameter']);
    exit;
}

$date = trim($_GET['date']);
$symbol = trim($_GET['symbol']);

require_once __DIR__ . '/db.php';

try {
    // 1. แปลงวันที่ พ.ศ. (เช่น 17/08/2569) กลับเป็น ค.ศ. (17/08/2026) เพื่อใช้คำนวณ Epoch
    $parts = explode('/', $date);
    if (count($parts) !== 3) {
        throw new Exception("รูปแบบวันที่ไม่ถูกต้อง (คาดหวัง: วัน/เดือน/ปีพ.ศ.)");
    }

    $day = intval($parts[0]);
    $month = intval($parts[1]);
    $year = intval($parts[2]) - 543; // แปลง พ.ศ. -> ค.ศ.

    $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $day);

    // 2. คำนวณช่วงเวลาเริ่มต้นและสิ้นสุดวัน (เวลาประเทศไทย UTC+7)
    $timezone = new DateTimeZone('Asia/Bangkok');
    $startOfDay = new DateTime("{$dateStr} 00:00:00", $timezone);
    $endOfDay = new DateTime("{$dateStr} 23:59:59", $timezone);

    $startEpoch = $startOfDay->getTimestamp();
    $endEpoch = $endOfDay->getTimestamp();

    $serverCode = isset($_GET['serverCode']) && trim((string)$_GET['serverCode']) !== '' ? trim((string)$_GET['serverCode']) : null;

    $db = getDbConnection();

    $whereParts = ["symbol = :symbol", "purchase_time BETWEEN :start_epoch AND :end_epoch"];
    $params = [
        ':symbol'      => $symbol,
        ':start_epoch' => $startEpoch,
        ':end_epoch'   => $endEpoch
    ];

    if ($serverCode !== null) {
        $whereParts[] = "serverCode = :serverCode";
        $params[':serverCode'] = $serverCode;
    }

    $whereSql = implode(" AND ", $whereParts);
    $sql = "SELECT * FROM derivTradeHistory 
            WHERE {$whereSql}
            ORDER BY purchase_time ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedTrades = [];
    foreach ($trades as $t) {
        $formattedTrades[] = [
            'serverCode'        => isset($t['serverCode']) && $t['serverCode'] !== null ? intval($t['serverCode']) : null,
            'transaction_id'    => intval($t['transaction_id']),
            'contract_id'       => intval($t['contract_id']),
            'app_id'            => $t['app_id'] !== null ? intval($t['app_id']) : null,
            'purchase_time'     => intval($t['purchase_time']),
            'sell_time'         => $t['sell_time'] !== null ? intval($t['sell_time']) : null,
            'duration'          => $t['duration'] !== null ? intval($t['duration']) : null,
            'underlying_symbol' => $t['symbol'],
            'contract_type'     => $t['contract_type'],
            'barrier'           => $t['barrier'],
            'high_barrier'      => $t['high_barrier'],
            'low_barrier'       => $t['low_barrier'],
            'buy_price'         => floatval($t['buy_price']),
            'sell_price'        => $t['sell_price'] !== null ? floatval($t['sell_price']) : null,
            'profit'            => $t['profit'] !== null ? floatval($t['profit']) : null,
            'payout'            => $t['payout'] !== null ? floatval($t['payout']) : null,
            'currency'          => $t['currency'],
            'shortcode'         => $t['shortcode'],
            'longcode'          => $t['longcode']
        ];
    }

    // หากไม่พบข้อมูลใน derivTradeHistory ให้ลองดึงจากตาราง vpsTradeData
    if (empty($formattedTrades)) {
        try {
            $sqlGcp = "SELECT * FROM vpsTradeData 
                       WHERE (symbol = :symbol OR assetCode = :symbol)
                         AND (purchaseTime BETWEEN :start_epoch AND :end_epoch OR timeCandle BETWEEN :start_epoch AND :end_epoch)
                       ORDER BY purchaseTime ASC";
            $stmtGcp = $db->prepare($sqlGcp);
            $stmtGcp->execute([
                ':symbol'      => $symbol,
                ':start_epoch' => $startEpoch,
                ':end_epoch'   => $endEpoch
            ]);
            $gcpRows = $stmtGcp->fetchAll(PDO::FETCH_ASSOC);

            foreach ($gcpRows as $g) {
                $moneyTrade = isset($g['MoneyTrade']) && $g['MoneyTrade'] !== null ? floatval($g['MoneyTrade']) : 1.0;
                $thisProfit = isset($g['ThisProfit']) && $g['ThisProfit'] !== null ? floatval($g['ThisProfit']) : 0.0;
                $sellPrice = $moneyTrade + $thisProfit;
                $pTime = isset($g['purchaseTime']) && intval($g['purchaseTime']) > 0 ? intval($g['purchaseTime']) : (isset($g['timeCandle']) ? intval($g['timeCandle']) : 0);
                $sTime = isset($g['sellTime']) ? intval($g['sellTime']) : null;
                $dur = isset($g['actualDuration']) ? intval($g['actualDuration']) : ($sTime && $pTime ? ($sTime - $pTime) : null);

                $formattedTrades[] = [
                    'transaction_id'    => isset($g['contractId']) ? intval($g['contractId']) : intval($g['id']),
                    'contract_id'       => isset($g['contractId']) ? intval($g['contractId']) : null,
                    'app_id'            => null,
                    'purchase_time'     => $pTime,
                    'sell_time'         => $sTime,
                    'duration'          => $dur,
                    'underlying_symbol' => !empty($g['symbol']) ? $g['symbol'] : $g['assetCode'],
                    'contract_type'     => !empty($g['thisAction']) ? $g['thisAction'] : 'CALL',
                    'barrier'           => null,
                    'high_barrier'      => null,
                    'low_barrier'       => null,
                    'buy_price'         => $moneyTrade,
                    'sell_price'        => $sellPrice,
                    'profit'            => $thisProfit,
                    'payout'            => $sellPrice,
                    'currency'          => 'USD',
                    'shortcode'         => !empty($g['codeStrategy']) ? $g['codeStrategy'] : null,
                    'longcode'          => !empty($g['tradeStrategy']) ? $g['tradeStrategy'] : null,
                    '_lossCon'          => isset($g['lossCon']) ? intval($g['lossCon']) : 0,
                    '_winCon'           => (isset($g['WinStatus']) && strtolower($g['WinStatus']) === 'win') ? 1 : 0
                ];
            }
        } catch (Exception $eGcp) {
            // ข้ามหากตารางยังไม่มี
        }
    }

    echo json_encode([
        'success' => true,
        'date'    => $dateStr,
        'trades'  => $formattedTrades
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
