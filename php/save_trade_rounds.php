<?php
/**
 * save_trade_rounds.php
 * API for saving tradeHead.json data directly to MySQL table: tradeHead
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

function ensureTradeHeadTable($db) {
    $sql = "CREATE TABLE IF NOT EXISTS `tradeHead` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `serverCode` INT NOT NULL DEFAULT 2,
        `tradeRoundNo` INT NOT NULL,
        `usestrategyCode` VARCHAR(50) DEFAULT NULL,
        `startTimeTrade` DATETIME DEFAULT NULL,
        `stopTimeTrade` DATETIME DEFAULT NULL,
        `durationTrade` VARCHAR(50) DEFAULT NULL,
        `totalAssets` INT DEFAULT 0,
        `assetCode` VARCHAR(50) NOT NULL,
        `MaxWinCon` INT DEFAULT 0,
        `MaxLossCon` INT DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_server_round_asset` (`serverCode`, `tradeRoundNo`, `assetCode`),
        KEY `idx_server_round` (`serverCode`, `tradeRoundNo`),
        KEY `idx_assetCode` (`assetCode`),
        KEY `idx_startTime` (`startTimeTrade`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
}

function saveTradeHead($db, $tradeHeadData, $serverCode = 2) {
    if (!is_array($tradeHeadData) || empty($tradeHeadData)) {
        return ['success' => false, 'error' => 'Invalid tradeHead data', 'savedRows' => 0];
    }
    
    $db->beginTransaction();
    
    $savedRounds = 0;
    $savedRows = 0;
    $results = [];
    
    try {
        $sql = "INSERT INTO `tradeHead` 
            (`serverCode`, `tradeRoundNo`, `usestrategyCode`, `startTimeTrade`, `stopTimeTrade`, 
             `durationTrade`, `totalAssets`, `assetCode`, `MaxWinCon`, `MaxLossCon`)
            VALUES 
            (:serverCode, :tradeRoundNo, :usestrategyCode, :startTimeTrade, :stopTimeTrade,
             :durationTrade, :totalAssets, :assetCode, :MaxWinCon, :MaxLossCon)
            ON DUPLICATE KEY UPDATE
                `usestrategyCode` = VALUES(`usestrategyCode`),
                `startTimeTrade` = VALUES(`startTimeTrade`),
                `stopTimeTrade` = VALUES(`stopTimeTrade`),
                `durationTrade` = VALUES(`durationTrade`),
                `totalAssets` = VALUES(`totalAssets`),
                `MaxWinCon` = VALUES(`MaxWinCon`),
                `MaxLossCon` = VALUES(`MaxLossCon`),
                `updated_at` = CURRENT_TIMESTAMP";
        $stmt = $db->prepare($sql);

        foreach ($tradeHeadData as $round) {
            if (!is_array($round)) continue;
            
            $sc = isset($round['serverCode']) ? intval($round['serverCode']) : $serverCode;
            $roundNo = isset($round['tradeRoundNo']) ? intval($round['tradeRoundNo']) : 0;
            $strategyCode = isset($round['usestrategyCode']) ? $round['usestrategyCode'] : null;
            $startTime = isset($round['startTimeTrade']) ? $round['startTimeTrade'] : null;
            $stopTime = isset($round['stopTimeTrade']) ? $round['stopTimeTrade'] : null;
            $duration = isset($round['durationTrade']) ? $round['durationTrade'] : null;
            $roundMaxLoss = isset($round['MaxLossCon']) ? intval($round['MaxLossCon']) : 0;
            $assetTrade = isset($round['assetTrade']) && is_array($round['assetTrade']) ? $round['assetTrade'] : [];
            $totalAssets = count($assetTrade);
            
            if ($roundNo <= 0) continue;
            
            $savedRounds++;
            $roundAssetCount = 0;

            if (!empty($assetTrade)) {
                foreach ($assetTrade as $asset) {
                    if (!is_array($asset)) continue;
                    $assetCode = trim($asset['assetCode'] ?? '');
                    if ($assetCode === '') continue;

                    $maxWinCon = isset($asset['MaxWinCon']) ? intval($asset['MaxWinCon']) : 0;
                    $maxLossCon = isset($asset['MaxLossCon']) ? intval($asset['MaxLossCon']) : $roundMaxLoss;

                    $stmt->execute([
                        ':serverCode'      => $sc,
                        ':tradeRoundNo'    => $roundNo,
                        ':usestrategyCode' => $strategyCode,
                        ':startTimeTrade'  => $startTime,
                        ':stopTimeTrade'   => $stopTime,
                        ':durationTrade'   => $duration,
                        ':totalAssets'     => $totalAssets,
                        ':assetCode'       => $assetCode,
                        ':MaxWinCon'       => $maxWinCon,
                        ':MaxLossCon'      => $maxLossCon
                    ]);

                    $savedRows++;
                    $roundAssetCount++;
                }
            } else {
                // กรณีไม่มี assetTrade แตกย่อย ให้บันทึก 1 แถวสรุป
                $stmt->execute([
                    ':serverCode'      => $sc,
                    ':tradeRoundNo'    => $roundNo,
                    ':usestrategyCode' => $strategyCode,
                    ':startTimeTrade'  => $startTime,
                    ':stopTimeTrade'   => $stopTime,
                    ':durationTrade'   => $duration,
                    ':totalAssets'     => 0,
                    ':assetCode'       => 'ALL',
                    ':MaxWinCon'       => 0,
                    ':MaxLossCon'      => $roundMaxLoss
                ]);
                $savedRows++;
                $roundAssetCount++;
            }
            
            $results[] = [
                'roundNo' => $roundNo,
                'assets'  => $roundAssetCount,
                'status'  => 'saved'
            ];
        }
        
        $db->commit();
        
        return [
            'success'     => true,
            'savedRows'   => $savedRows,
            'savedRounds' => $savedRounds,
            'results'     => $results,
            'message'     => "Saved {$savedRows} tradeHead records for {$savedRounds} trade rounds"
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        return [
            'success'   => false,
            'error'     => $e->getMessage(),
            'savedRows' => 0
        ];
    }
}

// Alias for backwards compatibility
function saveTradeRounds($db, $tradeHeadData, $serverCode = 2) {
    return saveTradeHead($db, $tradeHeadData, $serverCode);
}

try {
    $db = getDbConnection();
    ensureTradeHeadTable($db);
    
    $inputJSON = file_get_contents('php://input');
    $payload = json_decode($inputJSON, true);
    
    if (!$payload) {
        $payload = array_merge($_GET, $_POST);
    }
    
    $serverCode = isset($payload['serverCode']) ? intval($payload['serverCode']) : 2;
    $tradeHeadData = isset($payload['tradeHead']) ? $payload['tradeHead'] : null;
    
    if (!$tradeHeadData || !is_array($tradeHeadData)) {
        throw new Exception('Missing or invalid tradeHead data');
    }
    
    $result = saveTradeHead($db, $tradeHeadData, $serverCode);
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
