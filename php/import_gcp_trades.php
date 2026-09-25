<?php
/**
 * import_gcp_trades.php
 * นำเข้าข้อมูลจากไฟล์ trades.json ในโฟลเดอร์ tradeData เข้าสู่ตาราง vpsTradeData ใน MySQL
 * โดยกำหนดค่าฟิลด์ symbol จากชื่อโฟลเดอร์ เช่น 1HZ50V
 */

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/db.php';
$tradeDataDir = dirname(__DIR__) . '/tradeData';

try {
    $db = getDbConnection();

    // ตรวจสอบและค้นหาไฟล์ trades.json ทั้งหมดภายใต้โฟลเดอร์ tradeData
    if (!file_exists($tradeDataDir)) {
        die("[ERROR] ไม่พบโฟลเดอร์ {$tradeDataDir}\n");
    }

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tradeDataDir));
    $files = [];

    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        if (basename($file->getPathname()) === 'trades.json') {
            $files[] = $file->getPathname();
        }
    }

    if (empty($files)) {
        echo "ไม่พบไฟล์ trades.json ในโฟลเดอร์ {$tradeDataDir}\n";
        exit(0);
    }

    $caseCodes = [];
    try {
        $stmtCC = $db->query("SELECT code_no, case_code FROM case_codes ORDER BY CHAR_LENGTH(case_code) DESC");
        $caseCodes = $stmtCC->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $caseCodes = [];
    }

    $resolveCaseCodeAndNo = function($rawCodeStrategy) use ($caseCodes) {
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

                if ($upper === $ccUpper) {
                    return ['codeStrategy' => $cc, 'code_no' => intval($c['code_no'])];
                }
                if (str_ends_with($upper, '-' . $ccUpper) || 
                    str_ends_with($upper, '_' . $ccUpper) || 
                    str_ends_with($upper, ' ' . $ccUpper)) {
                    return ['codeStrategy' => $cc, 'code_no' => intval($c['code_no'])];
                }
                $pattern = '/(?:^|[-_ ])' . preg_quote($ccUpper, '/') . '(?:$|[-_ ])/i';
                if (preg_match($pattern, $upper)) {
                    return ['codeStrategy' => $cc, 'code_no' => intval($c['code_no'])];
                }
            }
        }
        return ['codeStrategy' => $trimmed, 'code_no' => null];
    };

    $insertSql = "INSERT INTO vpsTradeData (
        serverCode, symbol, contractId, buyId, sellId, assetCode,
        entrySpot, exitSpot, diffSpot, purchaseTime, purchaseTimeDisplay,
        tradeRoundNo, tradeNo, subTradeNo, timeCandle, timeCandleDisplay,
        sellTime, sellTimeDisplay, actualDuration, isAnomaly, thisColor,
        thisAction, targetColor, emaShortDirection, emaMediumDirection, MoneyTrade,
        WinStatus, lossCon, ThisProfit, GrandBalance, gaveUp,
        maxLossCon, tradeStrategy, code_no, codeStrategy, noiseCode, spotPriceCall,
        spotPricePut, spotCallPositionCode, spotPutPositionCode, whipsawZone
    ) VALUES (
        :serverCode, :symbol, :contractId, :buyId, :sellId, :assetCode,
        :entrySpot, :exitSpot, :diffSpot, :purchaseTime, :purchaseTimeDisplay,
        :tradeRoundNo, :tradeNo, :subTradeNo, :timeCandle, :timeCandleDisplay,
        :sellTime, :sellTimeDisplay, :actualDuration, :isAnomaly, :thisColor,
        :thisAction, :targetColor, :emaShortDirection, :emaMediumDirection, :MoneyTrade,
        :WinStatus, :lossCon, :ThisProfit, :GrandBalance, :gaveUp,
        :maxLossCon, :tradeStrategy, :code_no, :codeStrategy, :noiseCode, :spotPriceCall,
        :spotPricePut, :spotCallPositionCode, :spotPutPositionCode, :whipsawZone
    )";

    $stmt = $db->prepare($insertSql);

    $db->beginTransaction();
    $totalImported = 0;

    foreach ($files as $filePath) {
        $folderSymbol = basename(dirname($filePath));
        echo "กำลังนำเข้าไฟล์: {$filePath} (symbol = {$folderSymbol})...\n";

        $jsonContent = file_get_contents($filePath);
        $trades = json_decode($jsonContent, true);

        if (!is_array($trades)) {
            echo "  [WARN] ข้อมูล JSON ไม่ถูกต้อง ข้ามไฟล์นี้\n";
            continue;
        }

        $count = 0;
        foreach ($trades as $row) {
            $roundNo = isset($row['tradeRoundNo']) ? $row['tradeRoundNo'] : (isset($row['scheduleTradeNo']) ? $row['scheduleTradeNo'] : null);
            $rawCodeStrategy = isset($row['codeStrategy']) ? $row['codeStrategy'] : (isset($row['code_strategy']) ? $row['code_strategy'] : null);
            $resolved = $resolveCaseCodeAndNo($rawCodeStrategy);
            $finalCodeStrategy = $resolved['codeStrategy'];
            $finalCodeNo = $resolved['code_no'];
            if ($finalCodeNo === null && isset($row['code_no']) && $row['code_no'] !== null && $row['code_no'] !== '') {
                $finalCodeNo = intval($row['code_no']);
            }

            $stmt->execute([
                ':serverCode'           => (isset($row['serverCode']) && $row['serverCode'] !== null) ? intval($row['serverCode']) : 2,
                ':symbol'               => $folderSymbol,
                ':contractId'           => isset($row['contractId']) ? $row['contractId'] : null,
                ':buyId'                => isset($row['buyId']) ? $row['buyId'] : null,
                ':sellId'               => isset($row['sellId']) ? $row['sellId'] : null,
                ':assetCode'            => isset($row['assetCode']) ? $row['assetCode'] : null,
                ':entrySpot'            => isset($row['entrySpot']) ? $row['entrySpot'] : null,
                ':exitSpot'             => isset($row['exitSpot']) ? $row['exitSpot'] : null,
                ':diffSpot'             => isset($row['diffSpot']) ? $row['diffSpot'] : (isset($row['DiffSpot']) ? $row['DiffSpot'] : null),
                ':purchaseTime'         => isset($row['purchaseTime']) ? $row['purchaseTime'] : null,
                ':purchaseTimeDisplay'  => isset($row['purchaseTimeDisplay']) ? $row['purchaseTimeDisplay'] : null,
                ':tradeRoundNo'         => $roundNo,
                ':tradeNo'              => isset($row['tradeNo']) ? $row['tradeNo'] : null,
                ':subTradeNo'           => isset($row['subTradeNo']) ? $row['subTradeNo'] : (isset($row['subTradeno']) ? $row['subTradeno'] : null),
                ':timeCandle'           => isset($row['timeCandle']) ? $row['timeCandle'] : null,
                ':timeCandleDisplay'    => isset($row['timeCandleDisplay']) ? $row['timeCandleDisplay'] : null,
                ':sellTime'             => isset($row['sellTime']) ? $row['sellTime'] : null,
                ':sellTimeDisplay'      => isset($row['sellTimeDisplay']) ? $row['sellTimeDisplay'] : null,
                ':actualDuration'       => isset($row['actualDuration']) ? $row['actualDuration'] : null,
                ':isAnomaly'            => !empty($row['isAnomaly']) ? 1 : 0,
                ':thisColor'            => isset($row['thisColor']) ? $row['thisColor'] : null,
                ':thisAction'           => isset($row['thisAction']) ? $row['thisAction'] : null,
                ':targetColor'          => isset($row['targetColor']) ? $row['targetColor'] : null,
                ':emaShortDirection'    => isset($row['emaShortDirection']) ? $row['emaShortDirection'] : null,
                ':emaMediumDirection'   => isset($row['emaMediumDirection']) ? $row['emaMediumDirection'] : null,
                ':MoneyTrade'           => isset($row['MoneyTrade']) ? $row['MoneyTrade'] : null,
                ':WinStatus'            => isset($row['WinStatus']) ? $row['WinStatus'] : null,
                ':lossCon'              => isset($row['lossCon']) ? $row['lossCon'] : null,
                ':ThisProfit'           => isset($row['ThisProfit']) ? $row['ThisProfit'] : null,
                ':GrandBalance'         => isset($row['GrandBalance']) ? $row['GrandBalance'] : null,
                ':gaveUp'               => !empty($row['gaveUp']) ? 1 : 0,
                ':maxLossCon'           => isset($row['maxLossCon']) ? $row['maxLossCon'] : null,
                ':tradeStrategy'        => isset($row['tradeStrategy']) ? $row['tradeStrategy'] : null,
                ':code_no'              => $finalCodeNo,
                ':codeStrategy'         => $finalCodeStrategy,
                ':noiseCode'            => isset($row['noiseCode']) ? $row['noiseCode'] : null,
                ':spotPriceCall'        => isset($row['spotPriceCall']) ? $row['spotPriceCall'] : null,
                ':spotPricePut'         => isset($row['spotPricePut']) ? $row['spotPricePut'] : null,
                ':spotCallPositionCode' => isset($row['spotCallPositionCode']) ? $row['spotCallPositionCode'] : null,
                ':spotPutPositionCode'  => isset($row['spotPutPositionCode']) ? $row['spotPutPositionCode'] : null,
                ':whipsawZone'          => !empty($row['whipsawZone']) ? 1 : 0
            ]);
            $count++;
        }

        echo "  -> นำเข้าสำเร็จ {$count} รายการ\n";
        $totalImported += $count;
    }

    $db->commit();
    echo "\n============================================\n";
    echo "นำเข้าข้อมูลทั้งหมดเสร็จสมบูรณ์: {$totalImported} รายการ\n";
    echo "============================================\n";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n[ERROR] เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
}
?>
