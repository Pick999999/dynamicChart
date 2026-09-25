<?php
/**
 * save_analysis_data.php
 * PHP API สำหรับบันทึกผลการวิเคราะห์แท่งเทียน (Candle Analysis JSON) ลง MySQL
 * Primary Key: asset + timestamp + timeframe
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'POST';

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $inputJSON = file_get_contents('php://input');
    if (empty($inputJSON)) {
        $inputJSON = file_get_contents('php://stdin');
    }
    $payload = json_decode($inputJSON, true);

    if (!$payload || !isset($payload['asset']) || !isset($payload['data']) || !is_array($payload['data'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error'   => 'Invalid JSON payload. Required fields: asset, data (array), and optional timeframe'
        ]);
        exit;
    }

    $asset = trim($payload['asset']);
    $timeframe = isset($payload['timeframe']) ? intval($payload['timeframe']) : (isset($payload['granularity']) ? intval($payload['granularity']) : 60);
    $serverCode = isset($payload['serverCode']) && $payload['serverCode'] !== null ? intval($payload['serverCode']) : 9999;

    $db = getDbConnection();
    $db->beginTransaction();

    $sql = "REPLACE INTO candle_analysis (
                asset, serverCode, timestamp, timeframe, time_display,
                open, high, low, close, color,
                trend, case_code, trend_score, trend_strength,
                color_sequence, color_switches, is_whipsaw, whipsaw_status, is_spike,
                analysis_data
            ) VALUES (
                :asset, :serverCode, :timestamp, :timeframe, :time_display,
                :open, :high, :low, :close, :color,
                :trend, :case_code, :trend_score, :trend_strength,
                :color_sequence, :color_switches, :is_whipsaw, :whipsaw_status, :is_spike,
                :analysis_data
            )";

    $stmt = $db->prepare($sql);
    $savedCount = 0;

    foreach ($payload['data'] as $item) {
        $timestamp = isset($item['time']) ? intval($item['time']) : (isset($item['timestamp']) ? intval($item['timestamp']) : 0);
        if ($timestamp <= 0) continue;

        $itemServerCode = isset($item['serverCode']) ? intval($item['serverCode']) : $serverCode;
        $timeDisplay   = isset($item['timeDisplay']) ? $item['timeDisplay'] : date('Y-m-d H:i:s', $timestamp);
        $open          = isset($item['open']) ? floatval($item['open']) : 0.0;
        $high          = isset($item['high']) ? floatval($item['high']) : 0.0;
        $low           = isset($item['low']) ? floatval($item['low']) : 0.0;
        $close         = isset($item['close']) ? floatval($item['close']) : 0.0;
        $color         = isset($item['color']) ? $item['color'] : (isset($item['colorCandle']) ? $item['colorCandle'] : ($close > $open ? 'Green' : ($close < $open ? 'Red' : 'Doji')));
        $trend         = isset($item['trend']) ? $item['trend'] : null;
        $caseCode      = isset($item['caseCode']) ? $item['caseCode'] : null;
        $trendScore    = isset($item['trendScore']) ? intval($item['trendScore']) : 0;
        $trendStrength = isset($item['trendStrength']) ? $item['trendStrength'] : 'NEUTRAL';
        $colorSequence = isset($item['colorSequence']) ? $item['colorSequence'] : null;
        $colorSwitches = isset($item['colorSwitches']) ? intval($item['colorSwitches']) : 0;
        $isWhipsaw     = !empty($item['isWhipsaw']) ? 1 : 0;
        $whipsawStatus = isset($item['whipsawStatus']) ? $item['whipsawStatus'] : 'TRENDING';
        $isSpike       = !empty($item['isSpike']) ? 1 : 0;
        
        $analysisJson  = json_encode($item, JSON_UNESCAPED_UNICODE);

        $stmt->execute([
            ':asset'          => $asset,
            ':serverCode'     => $itemServerCode,
            ':timestamp'      => $timestamp,
            ':timeframe'      => $timeframe,
            ':time_display'   => $timeDisplay,
            ':open'           => $open,
            ':high'           => $high,
            ':low'            => $low,
            ':close'          => $close,
            ':color'          => $color,
            ':trend'          => $trend,
            ':case_code'      => $caseCode,
            ':trend_score'    => $trendScore,
            ':trend_strength' => $trendStrength,
            ':color_sequence' => $colorSequence,
            ':color_switches' => $colorSwitches,
            ':is_whipsaw'     => $isWhipsaw,
            ':whipsaw_status' => $whipsawStatus,
            ':is_spike'       => $isSpike,
            ':analysis_data'  => $analysisJson
        ]);

        $savedCount++;
    }

    $db->commit();

    echo json_encode([
        'success'     => true,
        'message'     => 'บันทึกผลการวิเคราะห์แท่งเทียนเรียบร้อยแล้ว!',
        'asset'       => $asset,
        'timeframe'   => $timeframe,
        'saved_count' => $savedCount
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
