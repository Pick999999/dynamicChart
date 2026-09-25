<?php
/**
 * get_analysis_data.php
 * PHP API สำหรับดึงข้อมูลผลการวิเคราะห์แท่งเทียนจากตาราง candle_analysis ใน MySQL
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $symbol      = isset($_GET['symbol']) ? trim($_GET['symbol']) : (isset($_GET['asset']) ? trim($_GET['asset']) : '');
    $granularity = isset($_GET['granularity']) ? intval($_GET['granularity']) : (isset($_GET['timeframe']) ? intval($_GET['timeframe']) : 60);
    $start       = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $end         = isset($_GET['end']) ? intval($_GET['end']) : 0;

    if (empty($symbol)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing parameter: symbol (or asset)']);
        exit;
    }

    $db = getDbConnection();

    $sql = "SELECT * FROM candle_analysis WHERE asset = :asset AND timeframe = :timeframe";
    $params = [
        ':asset'     => $symbol,
        ':timeframe' => $granularity
    ];

    if ($start > 0) {
        $sql .= " AND timestamp >= :start";
        $params[':start'] = $start;
    }
    if ($end > 0) {
        $sql .= " AND timestamp <= :end";
        $params[':end'] = $end;
    }

    $sql .= " ORDER BY timestamp ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($rows as $row) {
        $analysis = json_decode($row['analysis_data'], true);
        if (!$analysis) {
            $analysis = [
                'time'          => intval($row['timestamp']),
                'timeDisplay'   => $row['time_display'],
                'open'          => floatval($row['open']),
                'high'          => floatval($row['high']),
                'low'           => floatval($row['low']),
                'close'         => floatval($row['close']),
                'color'         => isset($row['color']) ? $row['color'] : ($row['close'] >= $row['open'] ? 'Green' : 'Red'),
                'trend'         => $row['trend'],
                'caseCode'      => $row['case_code'],
                'trendScore'    => intval($row['trend_score']),
                'trendStrength' => $row['trend_strength'],
                'colorSequence' => $row['color_sequence'],
                'colorSwitches' => intval($row['color_switches']),
                'isWhipsaw'     => boolval($row['is_whipsaw']),
                'whipsawStatus' => $row['whipsaw_status'],
                'isSpike'       => boolval($row['is_spike'])
            ];
        }
        $data[] = $analysis;
    }

    echo json_encode([
        'success'     => true,
        'symbol'      => $symbol,
        'granularity' => $granularity,
        'count'       => count($data),
        'data'        => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
