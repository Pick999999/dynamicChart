<?php
/**
 * api_deriv_account.php
 * RESTful JSON API สำหรับการจัดการตาราง derivAccount (CRUD Operations)
 * 
 * รองรับ:
 * - List: GET /php/api_deriv_account.php หรือ GET ?action=list[&search=...]
 * - Get Single: GET ?action=get&id={id}
 * - Create: POST action=create { email, appName, appId, tokenName, expiryDate, token, accountId, status }
 * - Update: POST action=update { id, email, appName, appId, tokenName, expiryDate, token, accountId, status }
 * - Delete: POST action=delete { id }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

// Helper function: ส่งคืน JSON Response
function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = getDbConnection();

    // 1. ตรวจสอบและสร้างตาราง derivAccount อัตโนมัติ (หากยังไม่มี)
    $db->exec("CREATE TABLE IF NOT EXISTS `derivAccount` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL,
        `appName` VARCHAR(100) DEFAULT NULL,
        `appId` VARCHAR(100) DEFAULT NULL,
        `tokenName` VARCHAR(100) DEFAULT NULL,
        `expiryDate` DATE DEFAULT NULL,
        `token` VARCHAR(255) DEFAULT NULL,
        `accountId` VARCHAR(50) DEFAULT NULL,
        `status` VARCHAR(20) DEFAULT 'active',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_email` (`email`),
        INDEX `idx_accountId` (`accountId`),
        INDEX `idx_appId` (`appId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // ดึง Request Data (รองรับทั้ง JSON Payload และ Form URL Encoded / $_POST)
    $inputRaw = file_get_contents('php://input');
    $inputData = json_decode($inputRaw, true);
    if (!is_array($inputData)) {
        $inputData = $_POST;
    }

    $action = $_GET['action'] ?? $inputData['action'] ?? ($method === 'GET' ? 'list' : '');

    // ==========================================
    // ACTION 0: GET WS URL (ขอ URL WebSocket ผ่าน OTP ฝั่งเซิร์ฟเวอร์)
    // ==========================================
    if ($action === 'get_ws_url' || $action === 'get_otp') {
        $accId = trim($_GET['accountId'] ?? $inputData['accountId'] ?? '');
        $id    = intval($_GET['id'] ?? $inputData['id'] ?? 0);

        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM `derivAccount` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
        } elseif (!empty($accId)) {
            $stmt = $db->prepare("SELECT * FROM `derivAccount` WHERE `accountId` = :accId LIMIT 1");
            $stmt->execute([':accId' => $accId]);
        } else {
            $stmt = $db->query("SELECT * FROM `derivAccount` WHERE `status` = 'active' AND `token` LIKE 'pat_%' ORDER BY `id` ASC LIMIT 1");
        }

        $acc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$acc || empty($acc['token']) || empty($acc['appId']) || empty($acc['accountId'])) {
            respond([
                'success' => false,
                'error'   => 'No active Deriv account with valid token found',
                'wsUrl'   => 'wss://api.derivws.com/trading/v1/options/ws/public'
            ]);
        }

        $ch = curl_init("https://api.derivws.com/trading/v1/options/accounts/{$acc['accountId']}/otp");
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
            respond([
                'success'    => true,
                'wsUrl'      => $json['data']['url'],
                'accountId'  => $acc['accountId'],
                'appId'      => $acc['appId'],
                'email'      => $acc['email']
            ]);
        } else {
            respond([
                'success'       => false,
                'error'         => $json['message'] ?? "Deriv OTP returned HTTP {$code}",
                'raw'           => $json,
                'fallbackWsUrl' => 'wss://api.derivws.com/trading/v1/options/ws/public'
            ], 200);
        }
    }

    // ==========================================
    // ACTION 1: LIST / SEARCH (ดึงรายการ Accounts)
    // ==========================================
    if ($action === 'list' || ($method === 'GET' && empty($_GET['action']))) {
        $search = trim($_GET['search'] ?? '');
        
        if ($search !== '') {
            $stmt = $db->prepare("SELECT * FROM `derivAccount` 
                                  WHERE `email` LIKE :kw 
                                     OR `appName` LIKE :kw 
                                     OR `appId` LIKE :kw 
                                     OR `tokenName` LIKE :kw 
                                     OR `token` LIKE :kw 
                                     OR `accountId` LIKE :kw 
                                     OR `status` LIKE :kw 
                                  ORDER BY `id` DESC");
            $stmt->execute([':kw' => "%{$search}%"]);
        } else {
            $stmt = $db->query("SELECT * FROM `derivAccount` ORDER BY `id` DESC");
        }

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // คำนวณวันหมดอายุ และสถานะ expiration
        $today = new DateTime('today');
        foreach ($records as &$rec) {
            $rec['is_expired'] = false;
            $rec['days_remaining'] = null;
            if (!empty($rec['expiryDate'])) {
                try {
                    $exp = new DateTime($rec['expiryDate']);
                    $diff = $today->diff($exp);
                    $rec['days_remaining'] = (int)$diff->format("%r%a");
                    $rec['is_expired'] = ($rec['days_remaining'] < 0);
                } catch (Exception $e) {
                    // ignore date parse error
                }
            }
        }

        respond([
            'success' => true,
            'count'   => count($records),
            'data'    => $records
        ]);
    }

    // ==========================================
    // ACTION 2: GET SINGLE RECORD
    // ==========================================
    if ($action === 'get') {
        $id = intval($_GET['id'] ?? $inputData['id'] ?? 0);
        if ($id <= 0) {
            respond(['success' => false, 'error' => 'Invalid ID specified.'], 400);
        }

        $stmt = $db->prepare("SELECT * FROM `derivAccount` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            respond(['success' => false, 'error' => 'Account not found.'], 404);
        }

        respond(['success' => true, 'data' => $row]);
    }

    // ==========================================
    // ACTION 3: CREATE RECORD (สร้างบัญชีใหม่)
    // ==========================================
    if ($action === 'create' || ($method === 'POST' && $action === '')) {
        $email      = trim($inputData['email'] ?? '');
        $appName    = trim($inputData['appName'] ?? $inputData['AppName'] ?? '');
        $appId      = trim($inputData['appId'] ?? $inputData['AppID'] ?? '');
        $tokenName  = trim($inputData['tokenName'] ?? '');
        $expiryDate = trim($inputData['expiryDate'] ?? $inputData['expiry_date'] ?? '');
        $token      = trim($inputData['token'] ?? '');
        $accountId  = trim($inputData['accountId'] ?? $inputData['AccountID'] ?? '');
        $status     = trim($inputData['status'] ?? 'active');

        if (empty($email)) {
            respond(['success' => false, 'error' => 'กรุณากรอก Email'], 422);
        }

        // แปลงรูปแบบวันที่กรณีรับมาเป็น d/m/Y เช่น 02/12/2026
        if (!empty($expiryDate)) {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $expiryDate, $matches)) {
                $expiryDate = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
            }
        } else {
            $expiryDate = null;
        }

        $stmt = $db->prepare("INSERT INTO `derivAccount` 
            (`email`, `appName`, `appId`, `tokenName`, `expiryDate`, `token`, `accountId`, `status`) 
            VALUES (:email, :appName, :appId, :tokenName, :expiryDate, :token, :accountId, :status)");
        
        $stmt->execute([
            ':email'      => $email,
            ':appName'    => $appName ?: null,
            ':appId'      => $appId ?: null,
            ':tokenName'  => $tokenName ?: null,
            ':expiryDate' => $expiryDate,
            ':token'      => $token ?: null,
            ':accountId'  => $accountId ?: null,
            ':status'     => $status ?: 'active'
        ]);

        $newId = (int)$db->lastInsertId();

        respond([
            'success' => true,
            'message' => "สร้างข้อมูล Deriv Account (ID: {$newId}) สำเร็จ",
            'id'      => $newId
        ], 201);
    }

    // ==========================================
    // ACTION 4: UPDATE RECORD (แก้ไขบัญชี)
    // ==========================================
    if ($action === 'update') {
        $id = intval($inputData['id'] ?? 0);
        if ($id <= 0) {
            respond(['success' => false, 'error' => 'ไม่พบ ID สำหรับการแก้ไข'], 400);
        }

        // ตรวจสอบว่ามีข้อมูลอยู่จริงหรือไม่
        $checkStmt = $db->prepare("SELECT `id` FROM `derivAccount` WHERE `id` = :id LIMIT 1");
        $checkStmt->execute([':id' => $id]);
        if (!$checkStmt->fetch()) {
            respond(['success' => false, 'error' => "ไม่พบ Deriv Account ID: {$id} ในระบบ"], 404);
        }

        $email      = trim($inputData['email'] ?? '');
        $appName    = trim($inputData['appName'] ?? $inputData['AppName'] ?? '');
        $appId      = trim($inputData['appId'] ?? $inputData['AppID'] ?? '');
        $tokenName  = trim($inputData['tokenName'] ?? '');
        $expiryDate = trim($inputData['expiryDate'] ?? $inputData['expiry_date'] ?? '');
        $token      = trim($inputData['token'] ?? '');
        $accountId  = trim($inputData['accountId'] ?? $inputData['AccountID'] ?? '');
        $status     = trim($inputData['status'] ?? 'active');

        if (empty($email)) {
            respond(['success' => false, 'error' => 'กรุณากรอก Email'], 422);
        }

        // แปลงรูปแบบวันที่กรณีรับมาเป็น d/m/Y เช่น 02/12/2026
        if (!empty($expiryDate)) {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $expiryDate, $matches)) {
                $expiryDate = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
            }
        } else {
            $expiryDate = null;
        }

        $updateStmt = $db->prepare("UPDATE `derivAccount` SET
            `email`      = :email,
            `appName`    = :appName,
            `appId`      = :appId,
            `tokenName`  = :tokenName,
            `expiryDate` = :expiryDate,
            `token`      = :token,
            `accountId`  = :accountId,
            `status`     = :status
            WHERE `id`   = :id");

        $updateStmt->execute([
            ':email'      => $email,
            ':appName'    => $appName ?: null,
            ':appId'      => $appId ?: null,
            ':tokenName'  => $tokenName ?: null,
            ':expiryDate' => $expiryDate,
            ':token'      => $token ?: null,
            ':accountId'  => $accountId ?: null,
            ':status'     => $status ?: 'active',
            ':id'         => $id
        ]);

        respond([
            'success' => true,
            'message' => "อัปเดตข้อมูล Deriv Account (ID: {$id}) เรียบร้อยแล้ว"
        ]);
    }

    // ==========================================
    // ACTION 5: DELETE RECORD (ลบบัญชี)
    // ==========================================
    if ($action === 'delete') {
        $id = intval($inputData['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            respond(['success' => false, 'error' => 'ไม่พบ ID สำหรับการลบ'], 400);
        }

        $delStmt = $db->prepare("DELETE FROM `derivAccount` WHERE `id` = :id");
        $delStmt->execute([':id' => $id]);

        if ($delStmt->rowCount() > 0) {
            respond([
                'success' => true,
                'message' => "ลบข้อมูล Deriv Account (ID: {$id}) สำเร็จ"
            ]);
        } else {
            respond(['success' => false, 'error' => "ไม่พบข้อมูลที่ต้องการลบ (ID: {$id})"], 404);
        }
    }

    respond(['success' => false, 'error' => "Action '{$action}' is not supported."], 400);

} catch (PDOException $pe) {
    respond(['success' => false, 'error' => 'Database Error: ' . $pe->getMessage()], 500);
} catch (Exception $e) {
    respond(['success' => false, 'error' => 'Server Error: ' . $e->getMessage()], 500);
}
