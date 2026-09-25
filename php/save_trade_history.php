<?php
/**
 * save_trade_history.php
 * PHP API สำหรับบันทึกประวัติการเทรด (Trade History) ลงใน MySQL
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

    if (!$payload || !isset($payload['trades']) || !is_array($payload['trades'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input (required: trades array)']);
        exit;
    }

    $db = getDbConnection();

    // ตรวจสอบและสร้างตาราง derivTradeHistory อัตโนมัติหากยังไม่มี
    $db->exec("CREATE TABLE IF NOT EXISTS `derivTradeHistory` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `serverCode` INT DEFAULT 2,
        `transaction_id` BIGINT UNIQUE,
        `contract_id` BIGINT DEFAULT NULL,
        `app_id` INT DEFAULT NULL,
        `purchase_time` BIGINT DEFAULT NULL,
        `sell_time` BIGINT DEFAULT NULL,
        `duration` INT DEFAULT NULL,
        `symbol` VARCHAR(50) DEFAULT NULL,
        `contract_type` VARCHAR(50) DEFAULT NULL,
        `barrier` VARCHAR(50) DEFAULT NULL,
        `high_barrier` VARCHAR(50) DEFAULT NULL,
        `low_barrier` VARCHAR(50) DEFAULT NULL,
        `buy_price` DOUBLE DEFAULT NULL,
        `sell_price` DOUBLE DEFAULT NULL,
        `profit` DOUBLE DEFAULT NULL,
        `payout` DOUBLE DEFAULT NULL,
        `currency` VARCHAR(20) DEFAULT NULL,
        `shortcode` VARCHAR(255) DEFAULT NULL,
        `longcode` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_serverCode` (`serverCode`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // ตรวจสอบคอลัมน์ serverCode หากตารางมีอยู่ก่อนแล้ว
    try {
        $checkCol = $db->query("SHOW COLUMNS FROM `derivTradeHistory` LIKE 'serverCode'");
        if ($checkCol && $checkCol->rowCount() === 0) {
            $db->exec("ALTER TABLE `derivTradeHistory` ADD COLUMN `serverCode` INT DEFAULT 2 AFTER `id`");
            $db->exec("ALTER TABLE `derivTradeHistory` ADD INDEX `idx_serverCode` (`serverCode`)");
        }
    } catch (Exception $e) {}

    $defaultServerCode = isset($payload['serverCode']) && $payload['serverCode'] !== null ? intval($payload['serverCode']) : 2;

    // เริ่ม Transaction เพื่อความรวดเร็วในการเขียนข้อมูลพร้อมกันจำนวนมาก
    $db->beginTransaction();

    $sql = "REPLACE INTO `derivTradeHistory` (
                serverCode, transaction_id, contract_id, app_id, purchase_time, sell_time, duration,
                symbol, contract_type, barrier, high_barrier, low_barrier,
                buy_price, sell_price, profit, payout, currency, shortcode, longcode
            ) VALUES (
                :serverCode, :transaction_id, :contract_id, :app_id, :purchase_time, :sell_time, :duration,
                :symbol, :contract_type, :barrier, :high_barrier, :low_barrier,
                :buy_price, :sell_price, :profit, :payout, :currency, :shortcode, :longcode
            )";
    $stmt = $db->prepare($sql);

    $savedCount = 0;
    foreach ($payload['trades'] as $tx) {
        $txId = isset($tx['transaction_id']) ? $tx['transaction_id'] : null;
        if (!$txId) continue;

        $serverCode = isset($tx['serverCode']) && $tx['serverCode'] !== null ? intval($tx['serverCode']) : $defaultServerCode;
        $contractId = isset($tx['contract_id']) ? $tx['contract_id'] : null;
        $appId = isset($tx['app_id']) ? intval($tx['app_id']) : null;
        $purchaseTime = isset($tx['purchase_time']) ? intval($tx['purchase_time']) : null;
        $sellTime = isset($tx['sell_time']) ? intval($tx['sell_time']) : null;
        
        $duration = null;
        if ($purchaseTime && $sellTime) {
            $duration = $sellTime - $purchaseTime;
        } elseif (isset($tx['duration'])) {
            $duration = intval($tx['duration']);
        }

        $symbol = isset($tx['underlying_symbol']) ? $tx['underlying_symbol'] : (isset($tx['shortcode']) ? $tx['shortcode'] : '—');
        $contractType = isset($tx['contract_type']) ? $tx['contract_type'] : '—';
        
        $barrier = isset($tx['barrier']) ? strval($tx['barrier']) : null;
        $highBarrier = isset($tx['high_barrier']) ? strval($tx['high_barrier']) : null;
        $lowBarrier = isset($tx['low_barrier']) ? strval($tx['low_barrier']) : null;

        $buyPrice = isset($tx['buy_price']) ? floatval($tx['buy_price']) : 0.0;
        $sellPrice = isset($tx['sell_price']) ? floatval($tx['sell_price']) : null;
        
        $profit = null;
        if ($sellPrice !== null) {
            $profit = $sellPrice - $buyPrice;
        } elseif (isset($tx['profit'])) {
            $profit = floatval($tx['profit']);
        }

        $payout = isset($tx['payout']) ? floatval($tx['payout']) : null;
        $currency = isset($tx['currency']) ? $tx['currency'] : 'USD';
        $shortcode = isset($tx['shortcode']) ? $tx['shortcode'] : null;
        $longcode = isset($tx['longcode']) ? $tx['longcode'] : null;

        $stmt->execute([
            ':serverCode'     => $serverCode,
            ':transaction_id' => $txId,
            ':contract_id'    => $contractId,
            ':app_id'         => $appId,
            ':purchase_time'  => $purchaseTime,
            ':sell_time'      => $sellTime,
            ':duration'       => $duration,
            ':symbol'         => $symbol,
            ':contract_type'  => $contractType,
            ':barrier'        => $barrier,
            ':high_barrier'   => $highBarrier,
            ':low_barrier'    => $lowBarrier,
            ':buy_price'      => $buyPrice,
            ':sell_price'     => $sellPrice,
            ':profit'         => $profit,
            ':payout'         => $payout,
            ':currency'       => $currency,
            ':shortcode'      => $shortcode,
            ':longcode'       => $longcode
        ]);
        $savedCount++;
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'บันทึกประวัติการเทรดเรียบร้อยแล้ว!',
        'count'   => $savedCount
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
