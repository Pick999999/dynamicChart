<?php
/**
 * get_candles.php
 * PHP API สำหรับดึงข้อมูลแท่งเทียนที่เคยบันทึกไว้ใน MySQL ตามเงื่อนไขช่วงเวลาและสินทรัพย์
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if (!isset($_GET['symbol']) || !isset($_GET['granularity']) || !isset($_GET['start']) || !isset($_GET['end'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters (symbol, granularity, start, end)']);
    exit;
}

$symbol = trim($_GET['symbol']);
$granularity = intval($_GET['granularity']);
$start = intval($_GET['start']);
$end = intval($_GET['end']);

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    $sql = "SELECT time, open, high, low, close, code_no_by_js, case_code_by_js, suggestAction 
            FROM candles 
            WHERE symbol = :symbol 
              AND granularity = :granularity 
              AND time BETWEEN :start AND :end
            ORDER BY time ASC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':symbol'      => $symbol,
        ':granularity' => $granularity,
        ':start'       => $start,
        ':end'         => $end
    ]);

    $candles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formatted = [];
    foreach ($candles as $c) {
        $formatted[] = [
            'time'             => intval($c['time']),
            'open'             => floatval($c['open']),
            'high'             => floatval($c['high']),
            'low'              => floatval($c['low']),
            'close'            => floatval($c['close']),
            'code_no_by_js'    => isset($c['code_no_by_js']) && $c['code_no_by_js'] !== null ? intval($c['code_no_by_js']) : null,
            'case_code_by_js'  => isset($c['case_code_by_js']) ? $c['case_code_by_js'] : null,
            'suggestAction'    => isset($c['suggestAction']) ? $c['suggestAction'] : null
        ];
    }

    echo json_encode([
        'success' => true,
        'candles' => $formatted
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
