<?php
// get-assets.php - ดึงรายการสินทรัพย์จาก MySQL ตาราง `asset`
// WHERE subgroup = 'synthetics' AND symbol_type = 'stockindex' AND submarket = 'random_index'
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function getDatabaseConnection() {
    $candidatePaths = [
        dirname(__DIR__) . '/php/db.php',
        dirname(__DIR__) . '/dynamicChart/php/db.php',
        __DIR__ . '/db.php'
    ];
    foreach ($candidatePaths as $dynamicDbPath) {
        if (file_exists($dynamicDbPath)) {
            require_once $dynamicDbPath;
            if (function_exists('getDbConnection')) {
                return getDbConnection();
            }
        }
    }

    $host = '127.0.0.1';
    $dbname = 'dynamic_chart';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    return $pdo;
}

try {
    $pdo = getDatabaseConnection();

    // SQL: SELECT * FROM `asset` WHERE subgroup = 'synthetics' AND symbol_type = 'stockindex' AND submarket = 'random_index'
    $sql = "SELECT * FROM `asset` 
            WHERE subgroup = 'synthetics' AND symbol_type = 'stockindex' AND submarket = 'random_index'
            ORDER BY 
                CASE 
                    WHEN asset_code LIKE 'R_%' THEN 1
                    WHEN asset_code LIKE '1HZ%' THEN 2
                    ELSE 3
                END ASC,
                asset_code ASC";

    $stmt = $pdo->query($sql);
    $assets = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'total' => count($assets),
        'assets' => $assets
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
