<?php
/**
 * api_vps.php
 * RESTful JSON API สำหรับการจัดการตาราง vpsMaster (CRUD Operations)
 * 
 * รองรับ:
 * - List: GET /php/api_vps.php หรือ GET ?action=list
 * - Get Single: GET ?action=get&id={id}
 * - Create: POST action=create { vpsCode, vpsName, publicIP, privateIP, portno, url, remark }
 * - Update: POST action=update { id, vpsCode, vpsName, publicIP, privateIP, portno, url, remark }
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

// Helper function: จัดรูปแบบ URL ให้มี scheme (http:// หรือ https://) เสมอ
function normalizeVpsUrl($url) {
    $u = trim($url ?? '');
    if ($u === '') return null;
    $u = rtrim($u, '/');
    if (!preg_match('/^https?:\/\//i', $u)) {
        if (strpos($u, 'pkderiv.online') !== false || strpos($u, 'gpkderiv.shop') !== false || substr($u, -5) === '.shop' || substr($u, -7) === '.online') {
            $u = 'https://' . $u;
        } else {
            $u = 'http://' . $u;
        }
    }
    return $u;
}

// Helper function: หา Base URL ของ VPS เพื่อใช้ยิง API ไปยัง bot/service
function getVpsBaseUrl($vps) {
    if (!empty($vps['url'])) {
        $u = trim($vps['url']);
        if (preg_match('/^https?:\/\//i', $u)) {
            return rtrim($u, '/');
        }
        if (strpos($u, '.shop') !== false || strpos($u, '.online') !== false) {
            return 'https://' . rtrim($u, '/');
        }
        return 'http://' . rtrim($u, '/');
    }
    $ip = trim($vps['publicIP'] ?? '');
    if (empty($ip)) return null;
    $port = !empty($vps['portno']) ? ':' . trim($vps['portno']) : '';
    return 'http://' . $ip . $port;
}


try {
    $db = getDbConnection();

    // 1. ตรวจสอบและสร้างตาราง vpsMaster อัตโนมัติ (หากยังไม่มี)
    $db->exec("CREATE TABLE IF NOT EXISTS `vpsMaster` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `vpsCode` VARCHAR(50) NOT NULL UNIQUE,
        `vpsName` VARCHAR(100) NOT NULL,
        `publicIP` VARCHAR(100) DEFAULT NULL,
        `privateIP` VARCHAR(100) DEFAULT NULL,
        `portno` VARCHAR(20) DEFAULT NULL,
        `url` VARCHAR(255) DEFAULT NULL,
        `remark` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_vpsCode` (`vpsCode`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // ตรวจสอบและเพิ่มคอลัมน์ใหม่หากตารางเดิมยังไม่มี
    try {
        $cols = $db->query("SHOW COLUMNS FROM `vpsMaster`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('portno', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `portno` VARCHAR(20) DEFAULT NULL AFTER `privateIP`");
        }
        if (!in_array('url', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `url` VARCHAR(255) DEFAULT NULL AFTER `portno`");
        }
        if (!in_array('remark', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `remark` TEXT DEFAULT NULL AFTER `url`");
        }
        if (!in_array('DerivAccountID', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `DerivAccountID` INT DEFAULT NULL AFTER `remark`");
        }
        if (!in_array('image', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `image` LONGTEXT DEFAULT NULL COMMENT 'รูปภาพ VPS ในแบบ Base64' AFTER `DerivAccountID`");
        }
        if (!in_array('cloudProvider', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `cloudProvider` VARCHAR(50) DEFAULT 'Manual' AFTER `remark`");
        }
        if (!in_array('awsInstanceId', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `awsInstanceId` VARCHAR(100) DEFAULT NULL AFTER `cloudProvider`");
        }
        if (!in_array('awsRegion', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `awsRegion` VARCHAR(50) DEFAULT 'ap-southeast-1' AFTER `awsInstanceId`");
        }
        if (!in_array('instanceStatus', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `instanceStatus` VARCHAR(50) DEFAULT 'unknown' AFTER `awsRegion`");
        }
        if (!in_array('instanceType', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `instanceType` VARCHAR(50) DEFAULT NULL AFTER `instanceStatus`");
        }
        if (!in_array('currentMonthCost', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `currentMonthCost` DECIMAL(10,4) DEFAULT 0.0000 AFTER `instanceType`");
        }
        if (!in_array('lastSyncAt', $cols)) {
            $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `lastSyncAt` DATETIME DEFAULT NULL AFTER `currentMonthCost`");
        }
    } catch (Exception $ex) {
        // ข้ามหากตรวจสอบไม่ผ่าน
    }

    $method = $_SERVER['REQUEST_METHOD'];
    
    // ดึง Request Data (รองรับทั้ง JSON Payload และ Form URL Encoded / $_POST)
    $inputRaw = file_get_contents('php://input');
    $inputData = json_decode($inputRaw, true);
    if (!is_array($inputData)) {
        $inputData = $_POST;
    }

    $action = $_GET['action'] ?? $inputData['action'] ?? ($method === 'GET' ? 'list' : '');

    // ==========================================
    // ACTION 1: LIST / SEARCH (ดึงรายการ VPS ทั้งหมด พร้อมข้อมูล Deriv Account)
    // ==========================================
    if ($action === 'list' || ($method === 'GET' && empty($_GET['action']))) {
        $search = trim($_GET['search'] ?? '');
        
        $baseSql = "SELECT 
                        v.*,
                        d.email AS deriv_email,
                        d.accountId AS deriv_accountId,
                        d.appName AS deriv_appName,
                        d.tokenName AS deriv_tokenName,
                        d.status AS deriv_status
                    FROM `vpsMaster` v
                    LEFT JOIN `derivAccount` d ON v.DerivAccountID = d.id";

        if ($search !== '') {
            $stmt = $db->prepare("{$baseSql} 
                                  WHERE v.`vpsCode` LIKE :kw 
                                     OR v.`vpsName` LIKE :kw 
                                     OR v.`publicIP` LIKE :kw 
                                     OR v.`privateIP` LIKE :kw 
                                     OR v.`portno` LIKE :kw 
                                     OR v.`url` LIKE :kw 
                                     OR v.`remark` LIKE :kw 
                                     OR d.`email` LIKE :kw
                                     OR d.`accountId` LIKE :kw
                                     OR d.`appName` LIKE :kw
                                     OR d.`tokenName` LIKE :kw
                                   ORDER BY CAST(v.`vpsCode` AS UNSIGNED) ASC, v.`id` ASC");
            $stmt->execute([':kw' => "%{$search}%"]);
        } else {
            $stmt = $db->query("{$baseSql} ORDER BY CAST(v.`vpsCode` AS UNSIGNED) ASC, v.`id` ASC");
        }

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        $stmt = $db->prepare("SELECT 
                                v.*,
                                d.email AS deriv_email,
                                d.accountId AS deriv_accountId,
                                d.appName AS deriv_appName,
                                d.tokenName AS deriv_tokenName,
                                d.status AS deriv_status
                              FROM `vpsMaster` v
                              LEFT JOIN `derivAccount` d ON v.DerivAccountID = d.id
                              WHERE v.`id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            respond(['success' => false, 'error' => 'Record not found.'], 404);
        }

        respond(['success' => true, 'data' => $row]);
    }

    // ==========================================
    // ACTION 3: CREATE (เพิ่มข้อมูล VPS ใหม่)
    // ==========================================
    if ($action === 'create') {
        $vpsCode        = trim($inputData['vpsCode'] ?? '');
        $vpsName        = trim($inputData['vpsName'] ?? '');
        $publicIP       = trim($inputData['publicIP'] ?? '');
        $privateIP      = trim($inputData['privateIP'] ?? '');
        $portno         = trim($inputData['portno'] ?? '');
        $url            = trim($inputData['url'] ?? '');
        $remark         = trim($inputData['remark'] ?? '');
        $derivAccountId = !empty($inputData['DerivAccountID']) ? intval($inputData['DerivAccountID']) : (!empty($inputData['derivAccountId']) ? intval($inputData['derivAccountId']) : null);
        $image          = trim($inputData['image'] ?? $inputData['imageBase64'] ?? '');

        if ($vpsCode === '') {
            respond(['success' => false, 'error' => 'กรุณาระบุรหัส VPS (vpsCode)'], 400);
        }
        if ($vpsName === '') {
            respond(['success' => false, 'error' => 'กรุณาระบุชื่อ VPS (vpsName)'], 400);
        }

        // ตรวจสอบว่า vpsCode ซ้ำหรือไม่
        $checkStmt = $db->prepare("SELECT `id` FROM `vpsMaster` WHERE `vpsCode` = :vpsCode LIMIT 1");
        $checkStmt->execute([':vpsCode' => $vpsCode]);
        if ($checkStmt->fetch()) {
            respond(['success' => false, 'error' => "รหัส VPS '$vpsCode' มีอยู่ในระบบแล้ว กรุณาใช้รหัสอื่น"], 400);
        }

        $cloudProvider  = trim($inputData['cloudProvider'] ?? 'Manual');
        $awsInstanceId  = trim($inputData['awsInstanceId'] ?? '');
        $awsRegion      = trim($inputData['awsRegion'] ?? 'ap-southeast-1');

        $insertStmt = $db->prepare("INSERT INTO `vpsMaster` (`vpsCode`, `vpsName`, `publicIP`, `privateIP`, `portno`, `url`, `remark`, `cloudProvider`, `awsInstanceId`, `awsRegion`, `DerivAccountID`, `image`, `created_at`, `updated_at`) 
                                    VALUES (:vpsCode, :vpsName, :publicIP, :privateIP, :portno, :url, :remark, :cloudProvider, :awsInstanceId, :awsRegion, :DerivAccountID, :image, NOW(), NOW())");
        $insertStmt->execute([
            ':vpsCode'        => $vpsCode,
            ':vpsName'        => $vpsName,
            ':publicIP'       => $publicIP !== '' ? $publicIP : null,
            ':privateIP'      => $privateIP !== '' ? $privateIP : null,
            ':portno'         => $portno !== '' ? $portno : null,
            ':url'            => normalizeVpsUrl($url),
            ':remark'         => $remark !== '' ? $remark : null,
            ':cloudProvider'  => $cloudProvider !== '' ? $cloudProvider : 'Manual',
            ':awsInstanceId'  => $awsInstanceId !== '' ? $awsInstanceId : null,
            ':awsRegion'      => $awsRegion !== '' ? $awsRegion : 'ap-southeast-1',
            ':DerivAccountID' => ($derivAccountId && $derivAccountId > 0) ? $derivAccountId : null,
            ':image'          => $image !== '' ? $image : null
        ]);

        $newId = $db->lastInsertId();
        respond([
            'success' => true,
            'message' => 'บันทึกข้อมูล VPS สำเร็จเรียบร้อย',
            'id'      => $newId
        ], 201);
    }

    // ==========================================
    // ACTION 4: UPDATE (แก้ไขข้อมูล VPS)
    // ==========================================
    if ($action === 'update') {
        $id             = intval($inputData['id'] ?? 0);
        $vpsCode        = trim($inputData['vpsCode'] ?? '');
        $vpsName        = trim($inputData['vpsName'] ?? '');
        $publicIP       = trim($inputData['publicIP'] ?? '');
        $privateIP      = trim($inputData['privateIP'] ?? '');
        $portno         = trim($inputData['portno'] ?? '');
        $url            = trim($inputData['url'] ?? '');
        $remark         = trim($inputData['remark'] ?? '');
        $cloudProvider  = trim($inputData['cloudProvider'] ?? '');
        $awsInstanceId  = trim($inputData['awsInstanceId'] ?? '');
        $awsRegion      = trim($inputData['awsRegion'] ?? '');
        $rawDerivAccId  = $inputData['DerivAccountID'] ?? $inputData['derivAccountId'] ?? null;
        $derivAccountId = ($rawDerivAccId !== null && $rawDerivAccId !== '' && intval($rawDerivAccId) > 0) ? intval($rawDerivAccId) : null;
        
        $hasImageKey    = array_key_exists('image', $inputData) || array_key_exists('imageBase64', $inputData);
        $rawImage       = $inputData['image'] ?? $inputData['imageBase64'] ?? null;

        if ($id <= 0) {
            respond(['success' => false, 'error' => 'Invalid ID specified.'], 400);
        }
        if ($vpsCode === '') {
            respond(['success' => false, 'error' => 'กรุณาระบุรหัส VPS (vpsCode)'], 400);
        }
        if ($vpsName === '') {
            respond(['success' => false, 'error' => 'กรุณาระบุชื่อ VPS (vpsName)'], 400);
        }

        // ตรวจสอบว่า vpsCode ซ้ำกับ record อื่นหรือไม่
        $checkStmt = $db->prepare("SELECT `id` FROM `vpsMaster` WHERE `vpsCode` = :vpsCode AND `id` != :id LIMIT 1");
        $checkStmt->execute([':vpsCode' => $vpsCode, ':id' => $id]);
        if ($checkStmt->fetch()) {
            respond(['success' => false, 'error' => "รหัส VPS '$vpsCode' ซ้ำกับเครื่องอื่นในระบบ"], 400);
        }

        // ดึงข้อมูลเดิมเพื่อเก็บรักษาค่าเดิมไว้ถ้าไม่ได้ส่งมา
        $origStmt = $db->prepare("SELECT `image`, `cloudProvider`, `awsInstanceId`, `awsRegion` FROM `vpsMaster` WHERE `id` = :id LIMIT 1");
        $origStmt->execute([':id' => $id]);
        $origRow = $origStmt->fetch(PDO::FETCH_ASSOC);

        $imageVal = (!$hasImageKey) ? ($origRow ? $origRow['image'] : null) : (($rawImage !== null && trim($rawImage) !== '') ? trim($rawImage) : null);
        $cloudProviderVal = ($cloudProvider !== '') ? $cloudProvider : ($origRow['cloudProvider'] ?? 'Manual');
        $awsInstanceIdVal = ($awsInstanceId !== '') ? $awsInstanceId : ($origRow['awsInstanceId'] ?? null);
        $awsRegionVal = ($awsRegion !== '') ? $awsRegion : ($origRow['awsRegion'] ?? 'ap-southeast-1');

        $updateStmt = $db->prepare("UPDATE `vpsMaster` 
                                    SET `vpsCode` = :vpsCode,
                                        `vpsName` = :vpsName,
                                        `publicIP` = :publicIP,
                                        `privateIP` = :privateIP,
                                        `portno` = :portno,
                                        `url` = :url,
                                        `remark` = :remark,
                                        `cloudProvider` = :cloudProvider,
                                        `awsInstanceId` = :awsInstanceId,
                                        `awsRegion` = :awsRegion,
                                        `DerivAccountID` = :DerivAccountID,
                                        `image` = :image,
                                        `updated_at` = NOW()
                                    WHERE `id` = :id");
        $updateStmt->execute([
            ':vpsCode'        => $vpsCode,
            ':vpsName'        => $vpsName,
            ':publicIP'       => $publicIP !== '' ? $publicIP : null,
            ':privateIP'      => $privateIP !== '' ? $privateIP : null,
            ':portno'         => $portno !== '' ? $portno : null,
            ':url'            => normalizeVpsUrl($url),
            ':remark'         => $remark !== '' ? $remark : null,
            ':cloudProvider'  => $cloudProviderVal,
            ':awsInstanceId'  => $awsInstanceIdVal,
            ':awsRegion'      => $awsRegionVal,
            ':DerivAccountID' => $derivAccountId,
            ':image'          => $imageVal,
            ':id'             => $id
        ]);

        respond([
            'success' => true,
            'message' => 'อัปเดตข้อมูล VPS สำเร็จเรียบร้อย'
        ]);
    }

    // ==========================================
    // ACTION 5: DELETE (ลบข้อมูล VPS)
    // ==========================================
    if ($action === 'delete') {
        $id = intval($inputData['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            respond(['success' => false, 'error' => 'Invalid ID specified.'], 400);
        }

        $delStmt = $db->prepare("DELETE FROM `vpsMaster` WHERE `id` = :id");
        $delStmt->execute([':id' => $id]);

        respond([
            'success' => true,
            'message' => 'ลบข้อมูล VPS สำเร็จเรียบร้อย'
        ]);
    }

    // ==========================================
    // ACTION 6: CONTROL VPS (Start / Stop)
    // ==========================================
    if ($action === 'control_vps') {
        $vpsId = intval($inputData['id'] ?? $_GET['id'] ?? 0);
        $command = strtolower(trim($inputData['command'] ?? $_GET['command'] ?? ''));

        if ($vpsId <= 0 || !in_array($command, ['start', 'stop'])) {
            respond(['success' => false, 'error' => 'Invalid parameters: id and command (start|stop) required'], 400);
        }

        $stmt = $db->prepare("SELECT * FROM `vpsMaster` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vps) {
            respond(['success' => false, 'error' => 'Record not found'], 404);
        }

        $cloudProvider = strtoupper(trim($vps['cloudProvider'] ?? ''));

        if ($cloudProvider === 'AWS') {
            $instId = (!empty($vps['awsInstanceId'])) ? $vps['awsInstanceId'] : 'i-0dfe609694660b8d2';
            $region = (!empty($vps['awsRegion'])) ? $vps['awsRegion'] : 'eu-west-2';

            $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'aws_service.py';
            $cmd = 'python ' . escapeshellarg($scriptPath) . ' --action control --instance-id ' . escapeshellarg($instId) . ' --region ' . escapeshellarg($region) . ' --cmd ' . escapeshellarg($command);
            
            $output = [];
            $returnCode = 0;
            exec($cmd, $output, $returnCode);

            $tempState = ($command === 'start') ? 'pending' : 'stopping';
            $db->prepare("UPDATE `vpsMaster` SET `instanceStatus` = :st, `lastSyncAt` = NOW() WHERE `id` = :id")
               ->execute([':st' => $tempState, ':id' => $vpsId]);

            respond([
                'success' => true,
                'message' => ($command === 'start') 
                    ? 'ส่งคำสั่งเปิดเครื่อง (Start EC2) สำเร็จ กำลังเริ่มระบบ...' 
                    : 'ส่งคำสั่งปิดเครื่อง (Stop EC2) สำเร็จ กำลังปิดระบบ...',
                'data' => [
                    'status' => $tempState,
                    'provider' => 'AWS'
                ]
            ]);
        } else {
            // GCP / Oracle / Manual Bot VPS Control
            $baseUrl = getVpsBaseUrl($vps);
            if (!$baseUrl) {
                respond(['success' => false, 'error' => "ไม่พบข้อมูล Public IP หรือ URL สำหรับเชื่อมต่อ VPS เครื่องนี้"], 400);
            }

            if ($command === 'stop') {
                $targetUrl = rtrim($baseUrl, '/') . '/api/stop';
                $ch = curl_init($targetUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $res = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                $db->prepare("UPDATE `vpsMaster` SET `instanceStatus` = 'stopped', `updated_at` = NOW() WHERE `id` = :id")->execute([':id' => $vpsId]);

                respond([
                    'success' => true,
                    'message' => "สั่งหยุดทำงาน ({$vps['vpsName']}) สำเร็จ",
                    'status' => 'stopped'
                ]);
            } else {
                // start command: restart service or initialize bot
                $targetUrl = rtrim($baseUrl, '/') . '/api/system/restart';
                $ch = curl_init($targetUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $res = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $db->prepare("UPDATE `vpsMaster` SET `instanceStatus` = 'running', `updated_at` = NOW() WHERE `id` = :id")->execute([':id' => $vpsId]);

                respond([
                    'success' => true,
                    'message' => "ส่งคำสั่งเริ่มทำงาน ({$vps['vpsName']}) สำเร็จ กำลังเริ่มระบบ...",
                    'status' => 'running'
                ]);
            }
        }
    }

    // ==========================================
    // ACTION 7: CHECK STATUSES (ตรวจสอบสถานะสดทุก VPS)
    // ==========================================
    if ($action === 'check_statuses') {
        $rows = $db->query("SELECT id, vpsCode, vpsName, cloudProvider, publicIP, portno, url, instanceStatus FROM `vpsMaster` ORDER BY `id` ASC")->fetchAll(PDO::FETCH_ASSOC);
        $statuses = [];

        $mh = curl_multi_init();
        $curlHandles = [];

        foreach ($rows as $vps) {
            $vId = $vps['id'];
            $cProv = strtoupper(trim($vps['cloudProvider'] ?? ''));

            if ($cProv === 'AWS') {
                $statuses[$vId] = [
                    'id' => $vId,
                    'status' => $vps['instanceStatus'] ?: 'unknown',
                    'isOnline' => in_array($vps['instanceStatus'], ['running', 'pending']),
                    'provider' => 'AWS'
                ];
            } else {
                $baseUrl = getVpsBaseUrl($vps);
                if ($baseUrl) {
                    $ch = curl_init(rtrim($baseUrl, '/') . '/api/status');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_multi_add_handle($mh, $ch);
                    $curlHandles[$vId] = $ch;
                } else {
                    $statuses[$vId] = [
                        'id' => $vId,
                        'status' => 'offline',
                        'isOnline' => false,
                        'provider' => $vps['cloudProvider'] ?? 'Manual'
                    ];
                }
            }
        }

        if (!empty($curlHandles)) {
            $running = null;
            do {
                $mrc = curl_multi_exec($mh, $running);
                if ($running) {
                    curl_multi_select($mh, 0.05);
                }
            } while ($running > 0 && $mrc == CURLM_OK);

            foreach ($curlHandles as $vId => $ch) {
                $content = curl_multi_getcontent($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);

                $data = json_decode($content, true);
                $isOk = ($httpCode >= 200 && $httpCode < 300);
                $st = 'offline';
                if ($isOk) {
                    $st = ($data && !empty($data['is_trading'])) ? 'trading' : 'running';
                }

                $statuses[$vId] = [
                    'id' => $vId,
                    'status' => $st,
                    'isOnline' => $isOk,
                    'balance' => $data['balance'] ?? null,
                    'accountName' => $data['account_name'] ?? null,
                    'provider' => 'Manual'
                ];

                // Update DB instanceStatus for non-AWS
                $db->prepare("UPDATE `vpsMaster` SET `instanceStatus` = :st WHERE `id` = :id")
                   ->execute([':st' => $st, ':id' => $vId]);
            }
            curl_multi_close($mh);
        }

        respond([
            'success' => true,
            'statuses' => $statuses
        ]);
    }

    respond(['success' => false, 'error' => "Unknown action: '$action'"], 400);

} catch (PDOException $e) {
    respond(['success' => false, 'error' => 'Database Error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    respond(['success' => false, 'error' => 'Server Error: ' . $e->getMessage()], 500);
}
?>
