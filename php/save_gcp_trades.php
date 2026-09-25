<?php
/**
 * save_gcp_trades.php
 * PHP API สำหรับโหลดข้อมูล trades.json จาก URL (หรือรับ JSON trades) และบันทึกลงตาราง vpsTradeData ใน MySQL
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'POST';

if ($requestMethod === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

function ensureVpsTable($db) {
    $sql = "CREATE TABLE IF NOT EXISTS vpsTradeData (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serverCode INT DEFAULT 2,
        symbol VARCHAR(50) DEFAULT NULL,
        contractId BIGINT DEFAULT NULL,
        buyId BIGINT DEFAULT NULL,
        sellId BIGINT DEFAULT NULL,
        assetCode VARCHAR(50) DEFAULT NULL,
        entrySpot DOUBLE DEFAULT NULL,
        exitSpot DOUBLE DEFAULT NULL,
        diffSpot DOUBLE DEFAULT NULL,
        purchaseTime BIGINT DEFAULT NULL,
        purchaseTimeDisplay VARCHAR(100) DEFAULT NULL,
        tradeRoundNo INT DEFAULT NULL,
        tradeNo INT DEFAULT NULL,
        subTradeNo INT DEFAULT NULL,
        timeCandle BIGINT DEFAULT NULL,
        timeCandleDisplay VARCHAR(100) DEFAULT NULL,
        sellTime BIGINT DEFAULT NULL,
        sellTimeDisplay VARCHAR(100) DEFAULT NULL,
        actualDuration INT DEFAULT NULL,
        isAnomaly INT DEFAULT 0,
        thisColor VARCHAR(30) DEFAULT NULL,
        thisAction VARCHAR(30) DEFAULT NULL,
        targetColor VARCHAR(30) DEFAULT NULL,
        emaShortDirection VARCHAR(50) DEFAULT NULL,
        emaMediumDirection VARCHAR(50) DEFAULT NULL,
        MoneyTrade DOUBLE DEFAULT NULL,
        WinStatus VARCHAR(30) DEFAULT NULL,
        winCon INT DEFAULT NULL,
        lossCon INT DEFAULT NULL,
        ThisProfit DOUBLE DEFAULT NULL,
        GrandBalance DOUBLE DEFAULT NULL,
        gaveUp INT DEFAULT 0,
        maxLossCon INT DEFAULT NULL,
        tradeStrategy VARCHAR(100) DEFAULT NULL,
        code_no INT DEFAULT NULL,
        codeStrategy VARCHAR(100) DEFAULT NULL,
        noiseCode VARCHAR(100) DEFAULT NULL,
        spotPriceCall DOUBLE DEFAULT NULL,
        spotPricePut DOUBLE DEFAULT NULL,
        spotCallPositionCode VARCHAR(100) DEFAULT NULL,
        spotPutPositionCode VARCHAR(100) DEFAULT NULL,
        whipsawZone INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_gcp_symbol_time (symbol, purchaseTime),
        KEY idx_gcp_contractId (contractId),
        KEY idx_gcp_buyId (buyId),
        KEY idx_server_tradeRoundNo (serverCode, tradeRoundNo),
        KEY idx_gcp_code_no (code_no)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เก็บข้อมูลที่ได้ จาก การเทรดแต่ละครั้ง ที่บันทึกบน folder tradedata บน vps ของแต่ละวัน';";
    $db->exec($sql);

    // ตรวจสอบและอัปเดตคอลัมน์ใหม่อัตโนมัติ
    try {
        $checkCol = $db->query("SHOW COLUMNS FROM vpsTradeData LIKE 'serverCode'");
        if ($checkCol && $checkCol->rowCount() === 0) {
            $db->exec("ALTER TABLE vpsTradeData ADD COLUMN serverCode INT DEFAULT 2 AFTER id");
        }
        
        $checkWinCon = $db->query("SHOW COLUMNS FROM vpsTradeData LIKE 'winCon'");
        if ($checkWinCon && $checkWinCon->rowCount() === 0) {
            $db->exec("ALTER TABLE vpsTradeData ADD COLUMN winCon INT DEFAULT NULL AFTER WinStatus");
        }

        // Rename scheduleTradeNo to tradeRoundNo หากยังเป็นชื่อเดิม
        $checkOldRound = $db->query("SHOW COLUMNS FROM vpsTradeData LIKE 'scheduleTradeNo'");
        if ($checkOldRound && $checkOldRound->rowCount() > 0) {
            $db->exec("ALTER TABLE vpsTradeData CHANGE COLUMN scheduleTradeNo tradeRoundNo INT DEFAULT NULL");
        }

        $checkCodeNo = $db->query("SHOW COLUMNS FROM vpsTradeData LIKE 'code_no'");
        if ($checkCodeNo && $checkCodeNo->rowCount() === 0) {
            $db->exec("ALTER TABLE vpsTradeData ADD COLUMN code_no INT DEFAULT NULL AFTER tradeStrategy");
        } else {
            $db->exec("ALTER TABLE vpsTradeData MODIFY COLUMN code_no INT DEFAULT NULL");
        }
    } catch (Exception $e) {
        // ข้ามข้อผิดพลาดกรณีไม่รองรับ SHOW COLUMNS
    }
}

function ensureGcpTable($db) {
    ensureVpsTable($db);
}

/**
 * ดึงรายการ case_codes ทั้งหมดจากตาราง case_codes เรียงตามความยาว case_code จากมากไปน้อย
 */
function getCaseCodesList($db) {
    static $caseCodes = null;
    if ($caseCodes === null) {
        try {
            $stmt = $db->query("SELECT code_no, case_code FROM case_codes ORDER BY CHAR_LENGTH(case_code) DESC");
            $caseCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $caseCodes = [];
        }
    }
    return $caseCodes;
}

/**
 * ตรวจสอบค่า codeStrategy (เช่น PKT5-RJ-BULLTRAP-STRONG) กับ table case_codes
 * และคืนค่า case_code และ code_no ที่ตรงกันเพื่อนำไปบันทึก
 */
function resolveCaseCodeAndNo($rawCodeStrategy, $caseCodes) {
    if ($rawCodeStrategy === null || $rawCodeStrategy === '') {
        return ['codeStrategy' => null, 'code_no' => null];
    }
    $trimmed = trim($rawCodeStrategy);
    if ($trimmed === '') {
        return ['codeStrategy' => null, 'code_no' => null];
    }
    $upper = strtoupper($trimmed);

    if (is_array($caseCodes)) {
        foreach ($caseCodes as $c) {
            $cc = $c['case_code'];
            $ccUpper = strtoupper($cc);

            // 1. ตรวจสอบกรณีตรงกันพอดี (Exact match)
            if ($upper === $ccUpper) {
                return ['codeStrategy' => $cc, 'code_no' => intval($c['code_no'])];
            }

            // 2. ตรวจสอบกรณีลงท้ายด้วย delimiter + case_code (เช่น PKT5-RJ-BULLTRAP-STRONG, V2-PKT-DN-CONFIRM)
            if (str_ends_with($upper, '-' . $ccUpper) || 
                str_ends_with($upper, '_' . $ccUpper) || 
                str_ends_with($upper, ' ' . $ccUpper)) {
                return ['codeStrategy' => $cc, 'code_no' => intval($c['code_no'])];
            }

            // 3. ตรวจสอบกรณีคำว่า case_code อยู่ภายในข้อความโดยมีขอบเขตตัวคั่น
            $pattern = '/(?:^|[-_ ])' . preg_quote($ccUpper, '/') . '(?:$|[-_ ])/i';
            if (preg_match($pattern, $upper)) {
                return ['codeStrategy' => $cc, 'code_no' => intval($c['code_no'])];
            }
        }
    }
    return ['codeStrategy' => $trimmed, 'code_no' => null];
}

function insertTrades($db, $symbol, $trades, $defaultServerCode = 2) {
    if (!is_array($trades) || empty($trades)) {
        return 0;
    }

    $caseCodes = getCaseCodesList($db);

    $insertSql = "INSERT INTO vpsTradeData (
        serverCode, symbol, contractId, buyId, sellId, assetCode,
        entrySpot, exitSpot, DiffSpot, purchaseTime, purchaseTimeDisplay,
        tradeRoundNo, tradeNo, subTradeno, timeCandle, timeCandleDisplay,
        sellTime, sellTimeDisplay, actualDuration, isAnomaly, thisColor,
        thisAction, targetColor, emaShortDirection, emaMediumDirection, MoneyTrade,
        WinStatus, winCon, lossCon, ThisProfit, GrandBalance, gaveUp,
        maxLossCon, tradeStrategy, code_no, codeStrategy, noiseCode, spotPriceCall,
        spotPricePut, spotCallPositionCode, spotPutPositionCode, whipsawZone
    ) VALUES (
        :serverCode, :symbol, :contractId, :buyId, :sellId, :assetCode,
        :entrySpot, :exitSpot, :DiffSpot, :purchaseTime, :purchaseTimeDisplay,
        :tradeRoundNo, :tradeNo, :subTradeno, :timeCandle, :timeCandleDisplay,
        :sellTime, :sellTimeDisplay, :actualDuration, :isAnomaly, :thisColor,
        :thisAction, :targetColor, :emaShortDirection, :emaMediumDirection, :MoneyTrade,
        :WinStatus, :winCon, :lossCon, :ThisProfit, :GrandBalance, :gaveUp,
        :maxLossCon, :tradeStrategy, :code_no, :codeStrategy, :noiseCode, :spotPriceCall,
        :spotPricePut, :spotCallPositionCode, :spotPutPositionCode, :whipsawZone
    )";

    $deleteSql = "DELETE FROM vpsTradeData WHERE contractId = :contractId AND contractId IS NOT NULL";
    $stmtDel = $db->prepare($deleteSql);
    $stmtIns = $db->prepare($insertSql);

    $count = 0;
    foreach ($trades as $row) {
        if (!is_array($row)) continue;

        $contractId = isset($row['contractId']) && $row['contractId'] !== null ? intval($row['contractId']) : null;
        if ($contractId) {
            $stmtDel->execute([':contractId' => $contractId]);
        }

        $sym = !empty($symbol) ? $symbol : (isset($row['assetCode']) ? $row['assetCode'] : 'UNKNOWN');

        // ตรวจสอบ serverCode: หากไม่มีหรือเป็น null ให้ใช้ default เป็น 2
        $serverCode = isset($row['serverCode']) && $row['serverCode'] !== null 
            ? intval($row['serverCode']) 
            : (isset($defaultServerCode) && $defaultServerCode !== null ? intval($defaultServerCode) : 2);

        // ดึงค่ารอบการเทรด (รองรับทั้ง key tradeRoundNo และ scheduleTradeNo)
        $roundNo = isset($row['tradeRoundNo']) && $row['tradeRoundNo'] !== null
            ? intval($row['tradeRoundNo'])
            : (isset($row['scheduleTradeNo']) && $row['scheduleTradeNo'] !== null ? intval($row['scheduleTradeNo']) : null);

        // ตรวจสอบและแยกค่า codeStrategy และ code_no จาก table case_codes
        $rawCodeStrategy = isset($row['codeStrategy']) ? $row['codeStrategy'] : (isset($row['code_strategy']) ? $row['code_strategy'] : null);
        $resolved = resolveCaseCodeAndNo($rawCodeStrategy, $caseCodes);
        $finalCodeStrategy = $resolved['codeStrategy'];
        $finalCodeNo = $resolved['code_no'];
        if ($finalCodeNo === null && isset($row['code_no']) && $row['code_no'] !== null && $row['code_no'] !== '') {
            $finalCodeNo = intval($row['code_no']);
        }

        $stmtIns->execute([
            ':serverCode'           => $serverCode,
            ':symbol'               => $sym,
            ':contractId'           => $contractId,
            ':buyId'                => isset($row['buyId']) && $row['buyId'] !== null ? intval($row['buyId']) : null,
            ':sellId'               => isset($row['sellId']) && $row['sellId'] !== null ? intval($row['sellId']) : null,
            ':assetCode'            => isset($row['assetCode']) ? $row['assetCode'] : $sym,
            ':entrySpot'            => isset($row['entrySpot']) && $row['entrySpot'] !== null ? floatval($row['entrySpot']) : null,
            ':exitSpot'             => isset($row['exitSpot']) && $row['exitSpot'] !== null ? floatval($row['exitSpot']) : null,
            ':DiffSpot'             => isset($row['DiffSpot']) && $row['DiffSpot'] !== null ? floatval($row['DiffSpot']) : (isset($row['diffSpot']) && $row['diffSpot'] !== null ? floatval($row['diffSpot']) : null),
            ':purchaseTime'         => isset($row['purchaseTime']) && $row['purchaseTime'] !== null ? intval($row['purchaseTime']) : null,
            ':purchaseTimeDisplay'  => isset($row['purchaseTimeDisplay']) ? $row['purchaseTimeDisplay'] : null,
            ':tradeRoundNo'         => $roundNo,
            ':tradeNo'              => isset($row['tradeNo']) && $row['tradeNo'] !== null ? intval($row['tradeNo']) : null,
            ':subTradeno'           => isset($row['subTradeno']) && $row['subTradeno'] !== null ? intval($row['subTradeno']) : (isset($row['subTradeNo']) && $row['subTradeNo'] !== null ? intval($row['subTradeNo']) : null),
            ':timeCandle'           => isset($row['timeCandle']) && $row['timeCandle'] !== null ? intval($row['timeCandle']) : null,
            ':timeCandleDisplay'    => isset($row['timeCandleDisplay']) ? $row['timeCandleDisplay'] : null,
            ':sellTime'             => isset($row['sellTime']) && $row['sellTime'] !== null ? intval($row['sellTime']) : null,
            ':sellTimeDisplay'      => isset($row['sellTimeDisplay']) ? $row['sellTimeDisplay'] : null,
            ':actualDuration'       => isset($row['actualDuration']) && $row['actualDuration'] !== null ? intval($row['actualDuration']) : null,
            ':isAnomaly'            => !empty($row['isAnomaly']) ? 1 : 0,
            ':thisColor'            => isset($row['thisColor']) ? $row['thisColor'] : null,
            ':thisAction'           => isset($row['thisAction']) ? $row['thisAction'] : null,
            ':targetColor'          => isset($row['targetColor']) ? $row['targetColor'] : null,
            ':emaShortDirection'    => isset($row['emaShortDirection']) ? $row['emaShortDirection'] : null,
            ':emaMediumDirection'   => isset($row['emaMediumDirection']) ? $row['emaMediumDirection'] : null,
            ':MoneyTrade'           => isset($row['MoneyTrade']) && $row['MoneyTrade'] !== null ? floatval($row['MoneyTrade']) : null,
            ':WinStatus'            => isset($row['WinStatus']) ? $row['WinStatus'] : null,
            ':winCon'               => isset($row['winCon']) && $row['winCon'] !== null ? intval($row['winCon']) : null,
            ':lossCon'              => isset($row['lossCon']) && $row['lossCon'] !== null ? intval($row['lossCon']) : null,
            ':ThisProfit'           => isset($row['ThisProfit']) && $row['ThisProfit'] !== null ? floatval($row['ThisProfit']) : null,
            ':GrandBalance'         => isset($row['GrandBalance']) && $row['GrandBalance'] !== null ? floatval($row['GrandBalance']) : null,
            ':gaveUp'               => !empty($row['gaveUp']) ? 1 : 0,
            ':maxLossCon'           => isset($row['maxLossCon']) && $row['maxLossCon'] !== null ? intval($row['maxLossCon']) : null,
            ':tradeStrategy'        => isset($row['tradeStrategy']) ? $row['tradeStrategy'] : null,
            ':code_no'              => $finalCodeNo,
            ':codeStrategy'         => $finalCodeStrategy,
            ':noiseCode'            => isset($row['noiseCode']) ? $row['noiseCode'] : null,
            ':spotPriceCall'        => isset($row['spotPriceCall']) && $row['spotPriceCall'] !== null ? floatval($row['spotPriceCall']) : null,
            ':spotPricePut'         => isset($row['spotPricePut']) && $row['spotPricePut'] !== null ? floatval($row['spotPricePut']) : null,
            ':spotCallPositionCode' => isset($row['spotCallPositionCode']) ? $row['spotCallPositionCode'] : null,
            ':spotPutPositionCode'  => isset($row['spotPutPositionCode']) ? $row['spotPutPositionCode'] : null,
            ':whipsawZone'          => !empty($row['whipsawZone']) ? 1 : 0,
        ]);
        $count++;
    }
    return $count;
}

function fetchJsonUrl($url) {
    $opts = [
        'http' => [
            'method'  => 'GET',
            'timeout' => 15,
            'header'  => "User-Agent: DynamicChart-App/1.0\r\n"
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ]
    ];
    $context = stream_context_create($opts);
    $content = @file_get_contents($url, false, $context);
    if ($content === false) {
        return null;
    }
    return json_decode($content, true);
}

try {
    $db = getDbConnection();
    ensureGcpTable($db);

    $inputJSON = file_get_contents('php://input');
    $payload = json_decode($inputJSON, true);

    if (!$payload) {
        $payload = array_merge($_GET, $_POST);
    }

    // Handle download_and_save action (for analysisTrade.html)
    // Strategy: Download ZIP via PHP (bypass CORS), return as base64, let browser unzip with JSZip
    if (isset($payload['action']) && $payload['action'] === 'download_and_save') {
        $exportUrl = isset($payload['exportUrl']) ? trim($payload['exportUrl']) : '';
        $serverCode = isset($payload['serverCode']) ? intval($payload['serverCode']) : 2;
        
        if (empty($exportUrl)) {
            throw new Exception('Missing exportUrl parameter');
        }

        // Normalize URL (fix backslashes)
        $exportUrl = str_replace('\\', '/', $exportUrl);

        // Download ZIP file from VPS using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $exportUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DynamicChart-App/1.0');
        
        $zipContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($zipContent === false || $httpCode !== 200) {
            $errorMsg = 'Failed to download ZIP file from: ' . $exportUrl;
            if (!empty($curlError)) {
                $errorMsg .= ' (cURL Error: ' . $curlError . ')';
            }
            $errorMsg .= ' (HTTP Code: ' . $httpCode . ')';
            throw new Exception($errorMsg);
        }

        if (strlen($zipContent) < 100) {
            throw new Exception('Downloaded file is too small to be a valid ZIP archive (Size: ' . strlen($zipContent) . ' bytes)');
        }

        // Return ZIP as base64 to browser for processing with JSZip
        echo json_encode([
            'success'      => true,
            'zipBase64'    => base64_encode($zipContent),
            'zipSize'      => strlen($zipContent),
            'serverCode'   => $serverCode,
            'message'      => 'ZIP file downloaded successfully (' . number_format(strlen($zipContent)) . ' bytes). Ready for client-side extraction.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $results = [];
    $totalSaved = 0;
    $defaultServerCode = isset($payload['serverCode']) && $payload['serverCode'] !== null ? intval($payload['serverCode']) : 2;

    $db->beginTransaction();

    if (isset($payload['items']) && is_array($payload['items'])) {
        foreach ($payload['items'] as $item) {
            $sym = isset($item['symbol']) ? $item['symbol'] : '';
            $trades = isset($item['trades']) ? $item['trades'] : null;
            $itemServerCode = isset($item['serverCode']) && $item['serverCode'] !== null ? intval($item['serverCode']) : $defaultServerCode;

            if (empty($trades) && !empty($item['url'])) {
                $trades = fetchJsonUrl($item['url']);
            }

            if (is_array($trades)) {
                $c = insertTrades($db, $sym, $trades, $itemServerCode);
                $totalSaved += $c;
                $results[] = [
                    'symbol' => $sym,
                    'url'    => isset($item['url']) ? $item['url'] : null,
                    'count'  => $c,
                    'status' => 'success'
                ];
            } else {
                $results[] = [
                    'symbol' => $sym,
                    'url'    => isset($item['url']) ? $item['url'] : null,
                    'count'  => 0,
                    'status' => 'failed_or_empty'
                ];
            }
        }
    }
    elseif (isset($payload['url'])) {
        $url = trim($payload['url']);
        $sym = isset($payload['symbol']) ? trim($payload['symbol']) : '';
        
        if (empty($sym)) {
            $parts = explode('/', trim($url, '/'));
            if (count($parts) >= 2) {
                $sym = $parts[count($parts) - 2];
            }
        }

        $trades = fetchJsonUrl($url);
        if (is_array($trades)) {
            $c = insertTrades($db, $sym, $trades, $defaultServerCode);
            $totalSaved += $c;
            $results[] = ['symbol' => $sym, 'url' => $url, 'count' => $c, 'status' => 'success'];
        } else {
            $results[] = ['symbol' => $sym, 'url' => $url, 'count' => 0, 'status' => 'fetch_failed'];
        }
    }
    elseif (isset($payload['trades']) && is_array($payload['trades'])) {
        $sym = isset($payload['symbol']) ? trim($payload['symbol']) : '';
        $c = insertTrades($db, $sym, $payload['trades'], $defaultServerCode);
        $totalSaved += $c;
        $results[] = ['symbol' => $sym, 'count' => $c, 'status' => 'success'];
    }
    elseif (isset($payload['assets']) && is_array($payload['assets'])) {
        $monthBE = isset($payload['monthBE']) ? trim($payload['monthBE']) : '';
        $dateBE = isset($payload['dateBE']) ? trim($payload['dateBE']) : '';

        foreach ($payload['assets'] as $sym) {
            $sym = trim($sym);
            if (empty($sym)) continue;

            $url = "https://gpkderiv.shop/tradeData/{$monthBE}/{$dateBE}/{$sym}/trades.json";
            $trades = fetchJsonUrl($url);

            if (is_array($trades)) {
                $c = insertTrades($db, $sym, $trades, $defaultServerCode);
                $totalSaved += $c;
                $results[] = ['symbol' => $sym, 'url' => $url, 'count' => $c, 'status' => 'success'];
            } else {
                $results[] = ['symbol' => $sym, 'url' => $url, 'count' => 0, 'status' => 'not_found_or_error'];
            }
        }
    }
    else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'Invalid payload: required url, items, trades, or assets'
        ], JSON_UNESCAPED_UNICODE);
        $db->rollBack();
        exit;
    }

    $db->commit();

    echo json_encode([
        'success'    => true,
        'totalSaved' => $totalSaved,
        'results'    => $results,
        'message'    => "บันทึกข้อมูล vpsTradeData สำเร็จรวม {$totalSaved} รายการ"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
