<?php
/**
 * api_page_config.php
 * Backend API for managing page configurations in MySQL table `pageConfig`
 * 
 * Actions:
 * - get: Fetch config by pageName and optional configKey (with fallback to 'default')
 * - save: Save/update config for pageName and configKey (JSON payload)
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

try {
    $db = getDbConnection();

    // Ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS `pageConfig` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `pageName` VARCHAR(100) NOT NULL,
        `configKey` VARCHAR(100) NOT NULL DEFAULT 'default',
        `configData` LONGTEXT NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_page_key` (`pageName`, `configKey`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? trim($_GET['action']) : ($method === 'POST' ? 'save' : 'get');

    // ─── GET CONFIG ───
    if ($action === 'get') {
        $pageName = isset($_GET['pageName']) ? trim($_GET['pageName']) : 'analysis_trade_chart';
        $configKey = isset($_GET['configKey']) && trim($_GET['configKey']) !== '' ? trim($_GET['configKey']) : 'default';

        // 1. Try exact match (pageName + configKey)
        $stmt = $db->prepare("SELECT pageName, configKey, configData, updated_at FROM `pageConfig` WHERE pageName = :p AND configKey = :k LIMIT 1");
        $stmt->execute([':p' => $pageName, ':k' => $configKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. If not found and configKey was not 'default', fallback to 'default'
        if (!$row && $configKey !== 'default') {
            $stmt = $db->prepare("SELECT pageName, configKey, configData, updated_at FROM `pageConfig` WHERE pageName = :p AND configKey = 'default' LIMIT 1");
            $stmt->execute([':p' => $pageName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 3. If still not found, fallback to any config for this page
        if (!$row) {
            $stmt = $db->prepare("SELECT pageName, configKey, configData, updated_at FROM `pageConfig` WHERE pageName = :p ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([':p' => $pageName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($row) {
            $data = json_decode($row['configData'], true);
            echo json_encode([
                'success'    => true,
                'pageName'   => $row['pageName'],
                'configKey'  => $row['configKey'],
                'configData' => $data !== null ? $data : $row['configData'],
                'updated_at' => $row['updated_at']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success'    => false,
                'message'    => 'No configuration found',
                'pageName'   => $pageName,
                'configKey'  => $configKey,
                'configData' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ─── SAVE CONFIG ───
    if ($action === 'save') {
        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput, true);

        if (!$payload && !empty($_POST)) {
            $payload = $_POST;
        }

        if (!$payload) {
            throw new Exception('Invalid JSON input for saving config');
        }

        $pageName = isset($payload['pageName']) ? trim($payload['pageName']) : 'analysis_trade_chart';
        $configKey = isset($payload['configKey']) && trim($payload['configKey']) !== '' ? trim($payload['configKey']) : 'default';
        
        $configData = isset($payload['configData']) ? $payload['configData'] : null;
        if ($configData === null) {
            throw new Exception('Missing configData parameter');
        }

        $configDataStr = is_string($configData) ? $configData : json_encode($configData, JSON_UNESCAPED_UNICODE);

        $stmt = $db->prepare("REPLACE INTO `pageConfig` (pageName, configKey, configData, updated_at) VALUES (:p, :k, :d, NOW())");
        $stmt->execute([
            ':p' => $pageName,
            ':k' => $configKey,
            ':d' => $configDataStr
        ]);

        echo json_encode([
            'success'   => true,
            'message'   => 'บันทึกการตั้งค่าลง MySQL table pageConfig เรียบร้อยแล้ว',
            'pageName'  => $pageName,
            'configKey' => $configKey
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
