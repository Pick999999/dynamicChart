<?php
/**
 * get_assets_by_date.php
 * PHP API สำหรับค้นหาว่าในวันที่ระบุ มีสินทรัพย์ (symbol) อะไรบ้างที่บันทึกอยู่ในตาราง candles ของ MySQL
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    $startEpoch = null;
    $endEpoch = null;

    // 1. รับค่า start และ end เป็น epoch หรือ date string
    if (isset($_GET['start']) && isset($_GET['end'])) {
        $startEpoch = intval($_GET['start']);
        $endEpoch = intval($_GET['end']);
    } elseif (isset($_GET['date'])) {
        $dateStr = trim($_GET['date']); // คาดหวัง YYYY-MM-DD หรือ DD/MM/YYYY
        if (strpos($dateStr, '/') !== false) {
            $parts = explode('/', $dateStr);
            if (count($parts) === 3) {
                $d = intval($parts[0]);
                $m = intval($parts[1]);
                $y = intval($parts[2]);
                if ($y > 2400) $y -= 543; // แปลง พ.ศ. เป็น ค.ศ.
                $dateStr = sprintf("%04d-%02d-%02d", $y, $m, $d);
            }
        }
        $tz = new DateTimeZone('Asia/Bangkok');
        $startDate = new DateTime("{$dateStr} 00:00:00", $tz);
        $endDate = new DateTime("{$dateStr} 23:59:59", $tz);
        $startEpoch = $startDate->getTimestamp();
        $endEpoch = $endDate->getTimestamp();
    }

    if ($startEpoch !== null && $endEpoch !== null) {
        $sql = "SELECT symbol, granularity, COUNT(*) as candle_count, MIN(time) as min_time, MAX(time) as max_time 
                FROM candles 
                WHERE time BETWEEN :start AND :end 
                GROUP BY symbol, granularity 
                ORDER BY symbol ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':start' => $startEpoch,
            ':end'   => $endEpoch
        ]);
    } else {
        // หากไม่ระบุช่วงเวลา ให้ดึงรายการสินทรัพย์ทั้งหมดที่มี
        $sql = "SELECT symbol, granularity, COUNT(*) as candle_count, MIN(time) as min_time, MAX(time) as max_time 
                FROM candles 
                GROUP BY symbol, granularity 
                ORDER BY symbol ASC";
        $stmt = $db->query($sql);
    }

    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'    => true,
        'startEpoch' => $startEpoch,
        'endEpoch'   => $endEpoch,
        'count'      => count($assets),
        'assets'     => $assets
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
