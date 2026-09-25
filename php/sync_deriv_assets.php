<?php
/**
 * sync_deriv_assets.php
 * รวบรวม asset_code, asset_name ทั้งหมดจาก Deriv.com และนำเข้าสู่ MySQL table `asset`
 * 
 * สามารถรันได้ทั้งผ่าน Command Line:
 *   php php/sync_deriv_assets.php
 * 
 * หรือเรียกผ่าน Web Browser / HTTP GET:
 *   http://localhost/dynamicChart/php/sync_deriv_assets.php
 */

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
}

require_once __DIR__ . '/db.php';

$startTime = microtime(true);

try {
    $pdo = getDbConnection();

    // 1. สร้างตาราง asset หากยังไม่มี
    $createTableSql = "
    CREATE TABLE IF NOT EXISTS `asset` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `asset_code` VARCHAR(50) NOT NULL COMMENT 'รหัสย่อสินทรัพย์ เช่น 1HZ100V, R_50, frxEURUSD',
      `asset_name` VARCHAR(150) NOT NULL COMMENT 'ชื่อสินทรัพย์ เช่น Volatility 100 (1s) Index, EUR/USD',
      `market` VARCHAR(50) DEFAULT NULL COMMENT 'ตลาดหลัก เช่น synthetic_index, forex, commodities, indices, cryptocurrency',
      `submarket` VARCHAR(50) DEFAULT NULL COMMENT 'ตลาดย่อย เช่น random_index, major_pairs, crash_index, metals',
      `subgroup` VARCHAR(50) DEFAULT NULL COMMENT 'กลุ่มย่อย เช่น synthetics, baskets, none',
      `symbol_type` VARCHAR(50) DEFAULT NULL COMMENT 'ประเภท เช่น stockindex, forex, commodities, cryptocurrency',
      `pip_size` DECIMAL(12, 6) DEFAULT 0.000100 COMMENT 'ขนาด pip/tick size',
      `exchange_is_open` TINYINT(1) DEFAULT 1 COMMENT 'สถานะตลาดเปิด/ปิด (1=Open, 0=Closed)',
      `is_trading_suspended` TINYINT(1) DEFAULT 0 COMMENT 'สถานะระงับการซื้อขาย (1=Suspended, 0=Normal)',
      `is_active` TINYINT(1) DEFAULT 1 COMMENT 'สถานะเปิดใช้งานในระบบ (1=Active, 0=Inactive)',
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `idx_asset_code` (`asset_code`),
      KEY `idx_market` (`market`),
      KEY `idx_submarket` (`submarket`),
      KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางรายการสินทรัพย์ทั้งหมดจาก Deriv.com';
    ";
    $pdo->exec($createTableSql);

    // 2. เรียก node script เพื่อดึงข้อมูลสดจาก Deriv WebSocket API
    $nodeScript = __DIR__ . '/fetch_deriv_symbols.js';
    if (!file_exists($nodeScript)) {
        throw new Exception("ไม่พบไฟล์ {$nodeScript}");
    }

    $command = 'node ' . escapeshellarg($nodeScript);
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    $jsonOutput = implode("\n", $output);
    if ($returnVar !== 0 || empty($jsonOutput)) {
        throw new Exception("ไม่สามารถดึงข้อมูลจาก Deriv ได้: " . ($jsonOutput ?: "Execution failed with code {$returnVar}"));
    }

    $assets = json_decode($jsonOutput, true);
    if (!is_array($assets) || count($assets) === 0) {
        throw new Exception("ข้อมูล Symbol จาก Deriv ว่างเปล่าหรือไม่ถูกต้อง");
    }

    // เซฟ backup เป็น JSON ไว้ในโฟลเดอร์ด้วย
    @file_put_contents(__DIR__ . '/deriv_assets_backup.json', json_encode($assets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // 3. บันทึก/อัปเดตลงใน MySQL Table `asset`
    $pdo->beginTransaction();

    $upsertSql = "
    INSERT INTO `asset` (
        `asset_code`,
        `asset_name`,
        `market`,
        `submarket`,
        `subgroup`,
        `symbol_type`,
        `pip_size`,
        `exchange_is_open`,
        `is_trading_suspended`,
        `is_active`
    ) VALUES (
        :asset_code,
        :asset_name,
        :market,
        :submarket,
        :subgroup,
        :symbol_type,
        :pip_size,
        :exchange_is_open,
        :is_trading_suspended,
        :is_active
    )
    ON DUPLICATE KEY UPDATE
        `asset_name` = VALUES(`asset_name`),
        `market` = VALUES(`market`),
        `submarket` = VALUES(`submarket`),
        `subgroup` = VALUES(`subgroup`),
        `symbol_type` = VALUES(`symbol_type`),
        `pip_size` = VALUES(`pip_size`),
        `exchange_is_open` = VALUES(`exchange_is_open`),
        `is_trading_suspended` = VALUES(`is_trading_suspended`),
        `is_active` = VALUES(`is_active`),
        `updated_at` = CURRENT_TIMESTAMP
    ";

    $stmt = $pdo->prepare($upsertSql);

    $inserted = 0;
    $updated = 0;
    $marketSummary = [];

    foreach ($assets as $a) {
        $market = $a['market'] ?? 'unknown';
        $marketSummary[$market] = ($marketSummary[$market] ?? 0) + 1;

        $stmt->execute([
            ':asset_code'            => $a['asset_code'],
            ':asset_name'            => $a['asset_name'],
            ':market'                => $a['market'] ?? null,
            ':submarket'             => $a['submarket'] ?? null,
            ':subgroup'              => $a['subgroup'] ?? 'none',
            ':symbol_type'           => $a['symbol_type'] ?? null,
            ':pip_size'              => $a['pip_size'] ?? 0.0001,
            ':exchange_is_open'      => isset($a['exchange_is_open']) ? (int)$a['exchange_is_open'] : 1,
            ':is_trading_suspended'  => isset($a['is_trading_suspended']) ? (int)$a['is_trading_suspended'] : 0,
            ':is_active'             => isset($a['is_active']) ? (int)$a['is_active'] : 1,
        ]);
        
        $rc = $stmt->rowCount();
        if ($rc === 1) {
            $inserted++;
        } elseif ($rc === 2) {
            $updated++;
        }
    }

    $pdo->commit();

    // ดึงจำนวนรวมทั้งหมดในตาราง asset
    $totalInDb = (int)$pdo->query("SELECT COUNT(*) FROM `asset`")->fetchColumn();

    $elapsedSec = round(microtime(true) - $startTime, 3);

    $response = [
        'status' => 'success',
        'message' => "รวบรวม asset code และ asset name จาก Deriv.com สำเร็จเรียบร้อย",
        'table' => 'asset',
        'database' => defined('DB_NAME') ? DB_NAME : 'dynamic_chart',
        'fetched_count' => count($assets),
        'inserted_count' => $inserted,
        'updated_count' => $updated,
        'total_in_table' => $totalInDb,
        'market_summary' => $marketSummary,
        'elapsed_seconds' => $elapsedSec,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if (php_sapi_name() === 'cli') {
        echo "====================================================\n";
        echo " ผลการรวบรวม Asset จาก Deriv.com -> MySQL Table `asset`\n";
        echo "====================================================\n";
        echo "สถานะ: " . $response['status'] . "\n";
        echo "ฐานข้อมูล: " . $response['database'] . "\n";
        echo "ตาราง: " . $response['table'] . "\n";
        echo "ดึงจาก Deriv ได้: " . $response['fetched_count'] . " รายการ\n";
        echo "เพิ่มใหม่ (Inserted): " . $response['inserted_count'] . " รายการ\n";
        echo "อัปเดต (Updated): " . $response['updated_count'] . " รายการ\n";
        echo "ยอดรวมในฐานข้อมูลปัจจุบัน: " . $response['total_in_table'] . " รายการ\n";
        echo "เวลาที่ใช้: " . $response['elapsed_seconds'] . " วินาที\n";
        echo "\nแยกตามตลาด (Market Summary):\n";
        foreach ($marketSummary as $m => $c) {
            echo " - {$m}: {$c} รายการ\n";
        }
        echo "====================================================\n";
    } else {
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $errorResponse = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if (php_sapi_name() === 'cli') {
        echo "ERROR: " . $e->getMessage() . "\n";
    } else {
        http_response_code(500);
        echo json_encode($errorResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
