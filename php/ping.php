<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/db.php';

$dbConnected = false;
$dbError = null;

try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        $dbConnected = true;
    }
} catch (Throwable $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

if ($dbConnected) {
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'db_status' => 'connected',
        'message' => 'PHP & MySQL Database are connected and ready',
        'database' => defined('DB_NAME') ? DB_NAME : 'dynamic_chart',
        'php_version' => PHP_VERSION,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'db_status' => 'disconnected',
        'error' => $dbError,
        'message' => 'PHP is running, but MySQL database connection failed. Please ensure MySQL is started in XAMPP.',
        'php_version' => PHP_VERSION,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

