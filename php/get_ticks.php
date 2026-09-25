<?php
/**
 * get_ticks.php
 * PHP API สำหรับดึงข้อมูลราคาติ๊ก (Tick Data) ที่เคยบันทึกไว้ใน MySQL ตามเงื่อนไขช่วงเวลาและสินทรัพย์
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if (!isset($_GET['symbol']) || !isset($_GET['start']) || !isset($_GET['end'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters (symbol, start, end)']);
    exit;
}

$symbol = trim($_GET['symbol']);
$start = intval($_GET['start']);
$end = intval($_GET['end']);

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    $sql = "SELECT time, price 
            FROM ticks 
            WHERE symbol = :symbol 
              AND time BETWEEN :start AND :end
            ORDER BY time ASC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':symbol' => $symbol,
        ':start'  => $start,
        ':end'    => $end
    ]);

    $ticks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formatted = [];
    foreach ($ticks as $t) {
        $formatted[] = [
            'candleTimestamp'   => intval($t['time']),
            'candleTimeDisplay' => date('Y-m-d H:i:s', $t['time']),
            'closePrices'       => floatval($t['price'])
        ];
    }

    echo json_encode([
        'success' => true,
        'ticks'   => $formatted
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
