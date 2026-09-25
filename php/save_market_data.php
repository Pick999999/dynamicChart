<?php
/**
 * save_market_data.php
 * PHP API สำหรับบันทึกข้อมูล Candle และ Tick ลงในฐานข้อมูล MySQL
 * รองรับการเรียกผ่าน HTTP POST ด้วย JSON payload
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $inputJSON = file_get_contents('php://input');
    $payload = json_decode($inputJSON, true);

    if (!$payload || !isset($payload['symbol'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input (required: symbol)']);
        exit;
    }

    $symbol = trim($payload['symbol']);
    $granularity = isset($payload['granularity']) ? intval($payload['granularity']) : 60; // ค่าเริ่มต้น 1 นาที (60 วินาที)
    $serverCode = isset($payload['serverCode']) && $payload['serverCode'] !== null ? intval($payload['serverCode']) : 9999;

    $db = getDbConnection();

    // เริ่ม Transaction เพื่อประสิทธิภาพในการ Insert ข้อมูลจำนวนมาก (Bulk Insert)
    $db->beginTransaction();

    $candlesSaved = 0;
    $ticksSaved = 0;

    // 1. บันทึกข้อมูล Candle History (หากส่งมา)
    if (isset($payload['candles']) && is_array($payload['candles'])) {
        $sqlCandle = "REPLACE INTO candles (serverCode, symbol, granularity, time, open, high, low, close, code_no_by_js, case_code_by_js, suggestAction) 
                      VALUES (:serverCode, :symbol, :granularity, :time, :open, :high, :low, :close, :code_no_by_js, :case_code_by_js, :suggestAction)";
        $stmtCandle = $db->prepare($sqlCandle);

        foreach ($payload['candles'] as $c) {
            $time = isset($c['time']) ? intval($c['time']) : (isset($c['epoch']) ? intval($c['epoch']) : 0);
            if ($time <= 0) continue;

            $cServerCode = isset($c['serverCode']) ? intval($c['serverCode']) : $serverCode;
            $codeNoByJs = isset($c['code_no_by_js']) ? intval($c['code_no_by_js']) : (isset($c['code_no']) ? intval($c['code_no']) : null);
            $caseCodeByJs = isset($c['case_code_by_js']) ? strval($c['case_code_by_js']) : (isset($c['case_code']) ? strval($c['case_code']) : (isset($c['caseCode']) ? strval($c['caseCode']) : null));
            $suggestAction = isset($c['suggestAction']) ? strval($c['suggestAction']) : (isset($c['suggest_action']) ? strval($c['suggest_action']) : null);

            $stmtCandle->execute([
                ':serverCode'      => $cServerCode,
                ':symbol'          => $symbol,
                ':granularity'     => $granularity,
                ':time'            => $time,
                ':open'            => floatval($c['open']),
                ':high'            => floatval($c['high']),
                ':low'             => floatval($c['low']),
                ':close'           => floatval($c['close']),
                ':code_no_by_js'   => $codeNoByJs,
                ':case_code_by_js' => $caseCodeByJs,
                ':suggestAction'   => $suggestAction
            ]);
            $candlesSaved++;
        }
    }

    // 2. บันทึกข้อมูล Tick Data (หากส่งมา)
    if (isset($payload['ticks']) && is_array($payload['ticks'])) {
        $sqlTick = "REPLACE INTO ticks (serverCode, symbol, time, price) 
                    VALUES (:serverCode, :symbol, :time, :price)";
        $stmtTick = $db->prepare($sqlTick);

        foreach ($payload['ticks'] as $t) {
            $time = isset($t['time']) ? intval($t['time']) : (isset($t['candleTimestamp']) ? intval($t['candleTimestamp']) : 0);
            $price = isset($t['price']) ? floatval($t['price']) : (isset($t['closePrices']) ? floatval($t['closePrices']) : 0.0);
            $tServerCode = isset($t['serverCode']) ? intval($t['serverCode']) : $serverCode;

            if ($time <= 0 || $price <= 0.0) continue;

            $stmtTick->execute([
                ':serverCode' => $tServerCode,
                ':symbol'     => $symbol,
                ':time'       => $time,
                ':price'      => $price
            ]);
            $ticksSaved++;
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว!',
        'details' => [
            'symbol'        => $symbol,
            'granularity'   => $granularity,
            'candles_count' => $candlesSaved,
            'ticks_count'   => $ticksSaved
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
