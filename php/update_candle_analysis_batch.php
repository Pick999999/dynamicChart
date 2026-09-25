<?php
/**
 * update_candle_analysis_batch.php
 * PHP API สำหรับ Batch Update ฟิลด์ code_no_by_js, case_code_by_js, suggestAction ลงในตาราง candles ใน MySQL
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $inputJSON = file_get_contents('php://input');
    $payload = json_decode($inputJSON, true);

    if (!$payload || !isset($payload['symbol']) || !isset($payload['updates']) || !is_array($payload['updates'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid payload (required: symbol, updates array)']);
        exit;
    }

    $symbol = trim($payload['symbol']);
    $granularity = isset($payload['granularity']) ? intval($payload['granularity']) : 60;
    $updates = $payload['updates'];

    $db = getDbConnection();
    $db->beginTransaction();

    $sql = "UPDATE candles 
            SET code_no_by_js = :code_no, 
                case_code_by_js = :case_code, 
                suggestAction = :suggest_action 
            WHERE symbol = :symbol 
              AND granularity = :granularity 
              AND time = :time";

    $stmt = $db->prepare($sql);
    $updatedCount = 0;

    foreach ($updates as $item) {
        $time = isset($item['time']) ? intval($item['time']) : 0;
        if ($time <= 0) continue;

        $codeNo = isset($item['code_no_by_js']) && $item['code_no_by_js'] !== null ? intval($item['code_no_by_js']) : null;
        $caseCode = isset($item['case_code_by_js']) ? strval($item['case_code_by_js']) : null;
        $suggestAction = isset($item['suggestAction']) ? strval($item['suggestAction']) : null;

        $stmt->execute([
            ':code_no'        => $codeNo,
            ':case_code'      => $caseCode,
            ':suggest_action' => $suggestAction,
            ':symbol'         => $symbol,
            ':granularity'    => $granularity,
            ':time'           => $time
        ]);

        $updatedCount++;
    }

    $db->commit();

    echo json_encode([
        'success'       => true,
        'message'       => "อัปเดตข้อมูลการวิเคราะห์สำเร็จ {$updatedCount} แท่ง",
        'symbol'        => $symbol,
        'granularity'   => $granularity,
        'updated_count' => $updatedCount
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
