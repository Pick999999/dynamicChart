<?php
/**
 * api_aws_billing.php
 * RESTful API endpoint for AWS EC2 status and Cost Explorer Billing synchronization
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

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = getDbConnection();

    // Auto-create / migrate tables if needed
    $db->exec("CREATE TABLE IF NOT EXISTS `vpsBillingHistory` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `vpsId` INT NOT NULL,
        `billingMonth` VARCHAR(7) NOT NULL,
        `costAmount` DECIMAL(10,4) NOT NULL,
        `currency` VARCHAR(10) DEFAULT 'USD',
        `detailsJson` LONGTEXT DEFAULT NULL,
        `syncedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `idx_vps_month` (`vpsId`, `billingMonth`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $inputRaw = file_get_contents('php://input');
    $inputData = json_decode($inputRaw, true) ?: $_POST;
    $action = $_GET['action'] ?? $inputData['action'] ?? 'get_summary';

    // Helper: Execute python AWS bridge
    function runAwsBridge($instanceId = 'i-0dfe609694660b8d2', $region = 'eu-west-2') {
        $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'aws_service.py';
        $cmd = 'python ' . escapeshellarg($scriptPath) . ' --action all --instance-id ' . escapeshellarg($instanceId) . ' --region ' . escapeshellarg($region);
        
        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        $jsonStr = implode("\n", $output);
        $data = json_decode($jsonStr, true);

        if ($returnCode !== 0 || !$data || empty($data['success'])) {
            $errMsg = $data['error'] ?? ('Command failed with code ' . $returnCode . ': ' . $jsonStr);
            throw new Exception("AWS Bridge Error: " . $errMsg);
        }

        return $data;
    }

    // Helper: Execute python AWS control bridge (Start / Stop)
    function runAwsControlBridge($instanceId = 'i-0dfe609694660b8d2', $region = 'eu-west-2', $command = 'start') {
        $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'aws_service.py';
        $cmd = 'python ' . escapeshellarg($scriptPath) . ' --action control --cmd ' . escapeshellarg($command) . ' --instance-id ' . escapeshellarg($instanceId) . ' --region ' . escapeshellarg($region);
        
        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        $jsonStr = implode("\n", $output);
        $data = json_decode($jsonStr, true);

        if ($returnCode !== 0 || !$data || empty($data['success'])) {
            $errMsg = $data['error'] ?? ('Command failed with code ' . $returnCode . ': ' . $jsonStr);
            throw new Exception("AWS Control Error: " . $errMsg);
        }

        return $data;
    }

    // =========================================================================
    // ACTION 1: SYNC (ดึงข้อมูลสดจาก AWS และบันทึกลง Database)
    // =========================================================================
    if ($action === 'sync') {
        // Find AWS VPS from vpsMaster
        $vpsStmt = $db->prepare("SELECT * FROM `vpsMaster` WHERE `cloudProvider` = 'AWS' OR `id` = 1 LIMIT 1");
        $vpsStmt->execute();
        $vps = $vpsStmt->fetch(PDO::FETCH_ASSOC);

        $vpsId = $vps ? intval($vps['id']) : 1;
        $instId = (!empty($vps['awsInstanceId'])) ? $vps['awsInstanceId'] : 'i-0508419bd5a4dd23f';
        $region = (!empty($vps['awsRegion'])) ? $vps['awsRegion'] : 'ap-southeast-1';

        $bridgeData = runAwsBridge($instId, $region);
        $instData = $bridgeData['instance'] ?? [];
        $costData = $bridgeData['cost'] ?? [];

        // 1. Update vpsMaster
        $status = $instData['state'] ?? 'unknown';
        $instType = $instData['instanceType'] ?? 't2.micro';
        $publicIp = $instData['publicIp'] ?? ($vps['publicIP'] ?? null);
        $mtdCost = floatval($costData['totalMtdCost'] ?? 0.0);

        $upd = $db->prepare("UPDATE `vpsMaster` 
                             SET `instanceStatus` = :status,
                                 `instanceType` = :instType,
                                 `currentMonthCost` = :mtdCost,
                                 `lastSyncAt` = NOW(),
                                 `cloudProvider` = 'AWS',
                                 `awsInstanceId` = :instId,
                                 `awsRegion` = :region
                             WHERE `id` = :id");
        $upd->execute([
            ':status'   => $status,
            ':instType' => $instType,
            ':mtdCost'  => $mtdCost,
            ':instId'   => $instId,
            ':region'   => $region,
            ':id'       => $vpsId
        ]);

        // 2. Insert/Update monthly history in vpsBillingHistory
        if (!empty($costData['monthlyHistory']) && is_array($costData['monthlyHistory'])) {
            $histUpsert = $db->prepare("INSERT INTO `vpsBillingHistory` (`vpsId`, `billingMonth`, `costAmount`, `currency`, `detailsJson`, `syncedAt`)
                                        VALUES (:vpsId, :billingMonth, :costAmount, :currency, :detailsJson, NOW())
                                        ON DUPLICATE KEY UPDATE 
                                            `costAmount` = VALUES(`costAmount`),
                                            `currency` = VALUES(`currency`),
                                            `detailsJson` = VALUES(`detailsJson`),
                                            `syncedAt` = NOW()");
            
            foreach ($costData['monthlyHistory'] as $item) {
                $month = $item['month'];
                $amount = floatval($item['amount']);
                $currency = $item['currency'] ?? 'USD';
                
                $details = null;
                if ($item['isCurrent'] ?? false) {
                    $detailsPayload = [
                        'categoryBreakdown' => $costData['categoryBreakdown'] ?? [],
                        'dailyBreakdown'    => $costData['dailyBreakdown'] ?? [],
                        'serviceBreakdown'  => $costData['serviceBreakdown'] ?? []
                    ];
                    $details = json_encode($detailsPayload, JSON_UNESCAPED_UNICODE);
                }

                $histUpsert->execute([
                    ':vpsId'        => $vpsId,
                    ':billingMonth' => $month,
                    ':costAmount'   => $amount,
                    ':currency'     => $currency,
                    ':detailsJson'  => $details
                ]);
            }
        }

        respond([
            'success' => true,
            'message' => 'ซิงค์ข้อมูลสถานะและยอดบิล AWS สำเร็จเรียบร้อย',
            'data'    => [
                'vpsId'    => $vpsId,
                'instance' => $instData,
                'cost'     => $costData
            ]
        ]);
    }

    // =========================================================================
    // ACTION 2: GET SUMMARY (ดึงข้อมูลสรุปสำหรับหน้า Billing Dashboard)
    // =========================================================================
    if ($action === 'get_summary') {
        // Query AWS VPS info (exclude heavy base64 image blob)
        $stmt = $db->query("SELECT `id`, `vpsCode`, `vpsName`, `publicIP`, `url`, `cloudProvider`, `awsInstanceId`, `awsRegion`, `instanceStatus`, `instanceType`, `currentMonthCost`, `lastSyncAt` 
                            FROM `vpsMaster` 
                            WHERE `cloudProvider` = 'AWS' OR `id` = 1 
                            LIMIT 1");
        $vps = $stmt->fetch(PDO::FETCH_ASSOC);
        $vpsId = $vps ? intval($vps['id']) : 1;

        // Query Monthly History from vpsBillingHistory
        $histStmt = $db->prepare("SELECT `billingMonth`, `costAmount`, `currency`, `detailsJson`, `syncedAt` 
                                  FROM `vpsBillingHistory` 
                                  WHERE `vpsId` = :vpsId 
                                  ORDER BY `billingMonth` ASC");
        $histStmt->execute([':vpsId' => $vpsId]);
        $historyRows = $histStmt->fetchAll(PDO::FETCH_ASSOC);

        // Find service and category breakdown of current month
        $latestDetails = [];
        foreach (array_reverse($historyRows) as $row) {
            if (!empty($row['detailsJson'])) {
                $latestDetails = json_decode($row['detailsJson'], true) ?: [];
                break;
            }
        }

        // If never synced or missing category breakdown, trigger sync once
        if (!$vps || empty($vps['lastSyncAt']) || empty($latestDetails['categoryBreakdown'])) {
            try {
                $instId = (!empty($vps['awsInstanceId'])) ? $vps['awsInstanceId'] : 'i-0508419bd5a4dd23f';
                $region = (!empty($vps['awsRegion'])) ? $vps['awsRegion'] : 'ap-southeast-1';
                $bridgeData = runAwsBridge($instId, $region);
                
                $instData = $bridgeData['instance'] ?? [];
                $costData = $bridgeData['cost'] ?? [];

                // Update vpsMaster
                $status = $instData['state'] ?? 'unknown';
                $instType = $instData['instanceType'] ?? 't2.micro';
                $mtdCost = floatval($costData['totalMtdCost'] ?? 0.0);

                $upd = $db->prepare("UPDATE `vpsMaster` 
                                     SET `instanceStatus` = :status,
                                         `instanceType` = :instType,
                                         `currentMonthCost` = :mtdCost,
                                         `lastSyncAt` = NOW(),
                                         `cloudProvider` = 'AWS',
                                         `awsInstanceId` = :instId,
                                         `awsRegion` = :region
                                     WHERE `id` = :id");
                $upd->execute([
                    ':status'   => $status,
                    ':instType' => $instType,
                    ':mtdCost'  => $mtdCost,
                    ':instId'   => $instId,
                    ':region'   => $region,
                    ':id'       => $vpsId
                ]);

                // Update vpsBillingHistory with rich details
                if (!empty($costData['monthlyHistory']) && is_array($costData['monthlyHistory'])) {
                    $histUpsert = $db->prepare("INSERT INTO `vpsBillingHistory` (`vpsId`, `billingMonth`, `costAmount`, `currency`, `detailsJson`, `syncedAt`)
                                                VALUES (:vpsId, :billingMonth, :costAmount, :currency, :detailsJson, NOW())
                                                ON DUPLICATE KEY UPDATE 
                                                    `costAmount` = VALUES(`costAmount`),
                                                    `currency` = VALUES(`currency`),
                                                    `detailsJson` = VALUES(`detailsJson`),
                                                    `syncedAt` = NOW()");
                    
                    foreach ($costData['monthlyHistory'] as $item) {
                        $month = $item['month'];
                        $amount = floatval($item['amount']);
                        $currency = $item['currency'] ?? 'USD';
                        $details = null;
                        if ($item['isCurrent'] ?? false) {
                            $detailsPayload = [
                                'categoryBreakdown' => $costData['categoryBreakdown'] ?? [],
                                'dailyBreakdown'    => $costData['dailyBreakdown'] ?? [],
                                'serviceBreakdown'  => $costData['serviceBreakdown'] ?? []
                            ];
                            $details = json_encode($detailsPayload, JSON_UNESCAPED_UNICODE);
                        }

                        $histUpsert->execute([
                            ':vpsId'        => $vpsId,
                            ':billingMonth' => $month,
                            ':costAmount'   => $amount,
                            ':currency'     => $currency,
                            ':detailsJson'  => $details
                        ]);
                    }
                }

                // Re-fetch VPS & history after sync
                $stmt = $db->query("SELECT * FROM `vpsMaster` WHERE `id` = {$vpsId}");
                $vps = $stmt->fetch(PDO::FETCH_ASSOC);

                $histStmt->execute([':vpsId' => $vpsId]);
                $historyRows = $histStmt->fetchAll(PDO::FETCH_ASSOC);

                $latestDetails = [
                    'categoryBreakdown' => $costData['categoryBreakdown'] ?? [],
                    'dailyBreakdown'    => $costData['dailyBreakdown'] ?? [],
                    'serviceBreakdown'  => $costData['serviceBreakdown'] ?? []
                ];
            } catch (Exception $e) {
                // Continue with whatever data is available
            }
        }

        // Extract breakdowns (supporting both new structured format and legacy array format)
        $categoryBreakdown = $latestDetails['categoryBreakdown'] ?? [];
        $dailyBreakdown = $latestDetails['dailyBreakdown'] ?? [];
        $serviceBreakdown = $latestDetails['serviceBreakdown'] ?? (isset($latestDetails[0]['service']) ? $latestDetails : []);

        // Calculate stats
        $currentCost = floatval($vps['currentMonthCost'] ?? 0);
        $dayOfMonth = intval(date('j'));
        $dailyBurn = $dayOfMonth > 0 ? round($currentCost / $dayOfMonth, 4) : 0;
        $daysInMonth = intval(date('t'));
        $projectedCost = round($dailyBurn * $daysInMonth, 4);

        // Approximate USD to THB rate
        $thbRate = 35.5;

        respond([
            'success' => true,
            'data' => [
                'vps' => $vps,
                'currentMonth' => date('Y-m'),
                'totalMtdCost' => $currentCost,
                'totalMtdCostThb' => round($currentCost * $thbRate, 2),
                'dailyBurnRate' => $dailyBurn,
                'dailyBurnRateThb' => round($dailyBurn * $thbRate, 2),
                'projectedMonthCost' => $projectedCost,
                'projectedMonthCostThb' => round($projectedCost * $thbRate, 2),
                'usdThbRate' => $thbRate,
                'categoryBreakdown' => $categoryBreakdown,
                'dailyBreakdown' => $dailyBreakdown,
                'serviceBreakdown' => $serviceBreakdown,
                'monthlyHistory' => $historyRows,
                'lastSyncAt' => $vps['lastSyncAt'] ?? null
            ]
        ]);
    }

    // =========================================================================
    // ACTION 3: CONTROL INSTANCE (สั่ง Start / Stop เครื่อง AWS EC2)
    // =========================================================================
    if ($action === 'control_instance') {
        $command = strtolower(trim($inputData['command'] ?? $_GET['command'] ?? ''));
        if (!in_array($command, ['start', 'stop'])) {
            respond(['success' => false, 'error' => "คำสั่งไม่ถูกต้อง: '$command' (ต้องเป็น 'start' หรือ 'stop' เท่านั้น)"], 400);
        }

        $vpsId = intval($inputData['vpsId'] ?? $_GET['vpsId'] ?? 1);
        $vpsStmt = $db->prepare("SELECT * FROM `vpsMaster` WHERE `id` = :id LIMIT 1");
        $vpsStmt->execute([':id' => $vpsId]);
        $vps = $vpsStmt->fetch(PDO::FETCH_ASSOC);

        if (!$vps) {
            respond(['success' => false, 'error' => "ไม่พบข้อมูล VPS รหัส: $vpsId"], 404);
        }

        $instId = (!empty($vps['awsInstanceId'])) ? $vps['awsInstanceId'] : 'i-0dfe609694660b8d2';
        $region = (!empty($vps['awsRegion'])) ? $vps['awsRegion'] : 'eu-west-2';

        $controlRes = runAwsControlBridge($instId, $region, $command);
        $controlInfo = $controlRes['control'] ?? [];

        // Temporary transition state to store in database
        $tempState = ($command === 'start') ? 'pending' : 'stopping';
        $upd = $db->prepare("UPDATE `vpsMaster` SET `instanceStatus` = :state, `lastSyncAt` = NOW() WHERE `id` = :id");
        $upd->execute([':state' => $tempState, ':id' => $vpsId]);

        respond([
            'success' => true,
            'message' => ($command === 'start') 
                ? 'ส่งคำสั่งเปิดเครื่อง (Start) ไปยัง AWS สำเร็จ กำลังเริ่มระบบ...' 
                : 'ส่งคำสั่งปิดเครื่อง (Stop) ไปยัง AWS สำเร็จ กำลังปิดระบบ...',
            'data' => [
                'vpsId'         => $vpsId,
                'instanceId'    => $instId,
                'region'        => $region,
                'command'       => $command,
                'previousState' => $controlInfo['previousState'] ?? 'unknown',
                'currentState'  => $controlInfo['currentState'] ?? $tempState
            ]
        ]);
    }

    // =========================================================================
    // ACTION 4: GET INSTANCE STATUS (ดึงเฉพาะสถานะสด EC2 แบบเร็วสำหรับ Polling)
    // =========================================================================
    if ($action === 'get_status') {
        $vpsId = intval($inputData['vpsId'] ?? $_GET['vpsId'] ?? 1);
        $vpsStmt = $db->prepare("SELECT * FROM `vpsMaster` WHERE `id` = :id LIMIT 1");
        $vpsStmt->execute([':id' => $vpsId]);
        $vps = $vpsStmt->fetch(PDO::FETCH_ASSOC);

        $instId = (!empty($vps['awsInstanceId'])) ? $vps['awsInstanceId'] : 'i-0dfe609694660b8d2';
        $region = (!empty($vps['awsRegion'])) ? $vps['awsRegion'] : 'eu-west-2';

        $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'aws_service.py';
        $cmd = 'python ' . escapeshellarg($scriptPath) . ' --action status --instance-id ' . escapeshellarg($instId) . ' --region ' . escapeshellarg($region);
        
        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        $data = json_decode(implode("\n", $output), true);
        if ($returnCode === 0 && !empty($data['instance']['success'])) {
            $inst = $data['instance'];
            $st = $inst['state'] ?? 'unknown';
            $pubIp = $inst['publicIp'] ?? ($vps['publicIP'] ?? null);

            $upd = $db->prepare("UPDATE `vpsMaster` 
                                 SET `instanceStatus` = :st, 
                                     `publicIP` = COALESCE(:ip, `publicIP`),
                                     `lastSyncAt` = NOW() 
                                 WHERE `id` = :id");
            $upd->execute([':st' => $st, ':ip' => $pubIp, ':id' => $vpsId]);

            respond([
                'success' => true,
                'data' => $inst
            ]);
        } else {
            $errMsg = $data['error'] ?? ('Status check failed with code ' . $returnCode);
            respond(['success' => false, 'error' => $errMsg], 500);
        }
    }

    respond(['success' => false, 'error' => "Unknown action: '$action'"], 400);

} catch (PDOException $e) {
    respond(['success' => false, 'error' => 'Database Error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    respond(['success' => false, 'error' => $e->getMessage()], 500);
}
?>
