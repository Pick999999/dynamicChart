<?php
/**
 * api_deriv_trade_history.php
 * API สำหรับจัดการและค้นหาข้อมูลจากตาราง derivTradeHistory
 * 
 * Actions:
 * - action=get_assets: ค้นหารายการสินทรัพย์ (symbols) ทั้งหมดของ VPS (serverCode) ในวันที่ระบุ
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

$action = isset($_GET['action']) ? trim($_GET['action']) : 'get_assets';

try {
    $db = getDbConnection();

    switch ($action) {
        case 'get_assets':
            $serverCode = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : null;
            $dateStr = isset($_GET['date']) ? trim($_GET['date']) : '';

            if (empty($dateStr)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing date parameter (YYYY-MM-DD or DD/MM/YYYY)']);
                exit;
            }

            // จัดการรูปแบบวันที่
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
            $startDt = new DateTime("{$dateStr} 00:00:00", $tz);
            $endDt = new DateTime("{$dateStr} 23:59:59", $tz);
            $startEpoch = $startDt->getTimestamp();
            $endEpoch = $endDt->getTimestamp();

            // สร้างคำสั่ง SQL
            $whereParts = ["purchase_time BETWEEN :start_epoch AND :end_epoch", "symbol IS NOT NULL", "symbol != ''"];
            $params = [
                ':start_epoch' => $startEpoch,
                ':end_epoch'   => $endEpoch
            ];

            if ($serverCode !== null && $serverCode > 0) {
                $whereParts[] = "serverCode = :serverCode";
                $params[':serverCode'] = $serverCode;
            }

            $whereSql = implode(" AND ", $whereParts);

            $sql = "SELECT 
                        symbol,
                        COUNT(*) AS trade_count,
                        MIN(purchase_time) AS min_time,
                        MAX(purchase_time) AS max_time,
                        SUM(COALESCE(profit, sell_price - buy_price, 0)) AS total_profit,
                        SUM(CASE WHEN COALESCE(profit, sell_price - buy_price, 0) > 0 THEN 1 ELSE 0 END) AS wins,
                        SUM(CASE WHEN COALESCE(profit, sell_price - buy_price, 0) < 0 THEN 1 ELSE 0 END) AS losses
                    FROM derivTradeHistory
                    WHERE {$whereSql}
                    GROUP BY symbol
                    ORDER BY symbol ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $assets = [];
            $totalTrades = 0;
            $details = [];

            foreach ($rows as $r) {
                $assets[] = $r['symbol'];
                $cnt = intval($r['trade_count']);
                $totalTrades += $cnt;
                $details[] = [
                    'symbol'       => $r['symbol'],
                    'trade_count'  => $cnt,
                    'wins'         => intval($r['wins']),
                    'losses'       => intval($r['losses']),
                    'total_profit' => round(floatval($r['total_profit']), 2),
                    'min_time'     => intval($r['min_time']),
                    'max_time'     => intval($r['max_time'])
                ];
            }

            echo json_encode([
                'success'      => true,
                'serverCode'   => $serverCode,
                'date'         => $dateStr,
                'startEpoch'   => $startEpoch,
                'endEpoch'     => $endEpoch,
                'total_trades' => $totalTrades,
                'asset_count'  => count($assets),
                'assets'       => $assets,
                'details'      => $details
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Unknown action: {$action}"]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
