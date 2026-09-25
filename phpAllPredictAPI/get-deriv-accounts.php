<?php
// get-deriv-accounts.php - ดึงรายการ Deriv Account และสร้าง OTP WebSocket URL
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function getDatabaseConnection() {
    // พยายามโหลดการตั้งค่าจาก dynamicChart ก่อน
    $candidatePaths = [
        dirname(__DIR__) . '/php/db.php',
        dirname(__DIR__) . '/dynamicChart/php/db.php'
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
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

    // 1. ACTION: get_ws_url (ขอ OTP WebSocket URL จาก Deriv API)
    if ($action === 'get_ws_url' || $action === 'get_otp') {
        $accId = trim($_GET['accountId'] ?? $_POST['accountId'] ?? '');
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM derivAccount WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
        } elseif (!empty($accId)) {
            $stmt = $pdo->prepare("SELECT * FROM derivAccount WHERE accountId = :accId LIMIT 1");
            $stmt->execute([':accId' => $accId]);
        } else {
            $stmt = $pdo->query("SELECT * FROM derivAccount WHERE status = 'active' ORDER BY id ASC LIMIT 1");
        }

        $acc = $stmt->fetch();
        if (!$acc || empty($acc['token']) || empty($acc['appId']) || empty($acc['accountId'])) {
            echo json_encode([
                'success' => false,
                'error' => 'No active Deriv account found with valid appId/token/accountId',
                'fallbackWsUrl' => 'wss://api.derivws.com/trading/v1/options/ws/public'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $otpUrl = "https://api.derivws.com/trading/v1/options/accounts/{$acc['accountId']}/otp";
        $ch = curl_init($otpUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $acc['token'],
            'Deriv-App-ID: ' . $acc['appId'],
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($res, true);
        if ($code === 200 && !empty($json['data']['url'])) {
            echo json_encode([
                'success' => true,
                'wsUrl' => $json['data']['url'],
                'accountId' => $acc['accountId'],
                'appName' => $acc['appName'] ?? $acc['email'],
                'appId' => $acc['appId']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $json['message'] ?? "Deriv OTP returned HTTP {$code}",
                'raw' => $json,
                'fallbackWsUrl' => 'wss://api.derivws.com/trading/v1/options/ws/public'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 2. ACTION: get_assets (ดึงรายการ assets ตาม SQL: subgroup = 'synthetics' and symbol_type = 'stockindex' and submarket = 'random_index')
    if ($action === 'get_assets' || $action === 'assets') {
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
        exit;
    }

    // 3. ACTION: list (รายการ Accounts ทั้งหมด)
    $sql = "SELECT id, email, appName, appId, accountId, status, token 
            FROM derivAccount 
            WHERE status = 'active'
            ORDER BY id ASC";
    
    $stmt = $pdo->query($sql);
    $accounts = $stmt->fetchAll();
    
    // จัดการ preview token
    $safeAccounts = [];
    foreach ($accounts as $acc) {
        $token = $acc['token'] ?? '';
        $tokenPreview = '';
        if (strlen($token) > 10) {
            $tokenPreview = substr($token, 0, 4) . '***' . substr($token, -6);
        }
        $safeAccounts[] = [
            'id' => $acc['id'],
            'email' => $acc['email'],
            'appName' => $acc['appName'],
            'appId' => $acc['appId'],
            'accountId' => $acc['accountId'],
            'status' => $acc['status'],
            'tokenPreview' => $tokenPreview
        ];
    }
    
    echo json_encode([
        'success' => true,
        'accounts' => $safeAccounts,
        'total' => count($safeAccounts)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
