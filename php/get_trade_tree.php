<?php
/**
 * get_trade_tree.php
 * PHP API สำหรับดึงประวัติการเทรดในฐานข้อมูล MySQL มาจัดกลุ่มเป็น Tree (วันที่ พ.ศ. -> ชื่อสินทรัพย์)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    $timezone = new DateTimeZone('Asia/Bangkok');
    $tree = [];

    $serverCode = isset($_GET['serverCode']) && trim((string)$_GET['serverCode']) !== '' ? trim((string)$_GET['serverCode']) : null;

    // 1. ดึงข้อมูลแท่งเทียนจาก full_analysis_data (ที่มี serverCode)
    try {
        $whereFull = $serverCode !== null ? "WHERE serverCode = :serverCode" : "";
        $sqlFull = "SELECT FROM_UNIXTIME(candletime + 25200, '%Y-%m-%d') as day_str, 
                           assetCode as symbol, 
                           COUNT(*) as candle_count, 
                           MIN(candletime) as min_time, 
                           MAX(candletime) as max_time
                    FROM full_analysis_data 
                    {$whereFull}
                    GROUP BY day_str, assetCode
                    ORDER BY day_str DESC";
        $stmtFull = $db->prepare($sqlFull);
        if ($serverCode !== null) {
            $stmtFull->execute([':serverCode' => $serverCode]);
        } else {
            $stmtFull->execute();
        }
        $fullRows = $stmtFull->fetchAll(PDO::FETCH_ASSOC);

        foreach ($fullRows as $frow) {
            $dayStr = $frow['day_str'];
            if (!$dayStr) continue;
            $parts = explode('-', $dayStr);
            if (count($parts) !== 3) continue;

            $y = intval($parts[0]);
            $m = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            $d = str_pad($parts[2], 2, '0', STR_PAD_LEFT);
            $thaiYear = $y + 543;
            $thaiDate = "{$d}/{$m}/{$thaiYear}";
            $symbol = $frow['symbol'] ? $frow['symbol'] : '—';

            if (!isset($tree[$thaiDate])) {
                $tree[$thaiDate] = [];
            }
            if (!isset($tree[$thaiDate][$symbol])) {
                $tree[$thaiDate][$symbol] = [
                    'count' => 0,
                    'profit_sum' => 0.0,
                    'candle_count' => intval($frow['candle_count']),
                    'min_epoch' => intval($frow['min_time']),
                    'max_epoch' => intval($frow['max_time']),
                    'date_iso' => $dayStr
                ];
            } else {
                $tree[$thaiDate][$symbol]['candle_count'] += intval($frow['candle_count']);
                $tree[$thaiDate][$symbol]['date_iso'] = $dayStr;
            }
        }
    } catch (Exception $eFull) {
        // ตาราง full_analysis_data อาจยังไม่มีข้อมูล
    }

    // 2. ดึงข้อมูลประวัติแท่งเทียนทั่วไป (candles) จาก MySQL (กรณีไม่ได้เลือกเฉพาะ serverCode)
    if ($serverCode === null) {
        try {
            $sqlCandles = "SELECT FROM_UNIXTIME(time + 25200, '%Y-%m-%d') as day_str, 
                                  symbol, 
                                  COUNT(*) as candle_count, 
                                  MIN(time) as min_time, 
                                  MAX(time) as max_time
                           FROM candles 
                           GROUP BY day_str, symbol
                           ORDER BY day_str DESC";
            $stmtCandles = $db->query($sqlCandles);
            $candleRows = $stmtCandles->fetchAll(PDO::FETCH_ASSOC);

            foreach ($candleRows as $crow) {
                $dayStr = $crow['day_str'];
                if (!$dayStr) continue;
                $parts = explode('-', $dayStr);
                if (count($parts) !== 3) continue;

                $y = intval($parts[0]);
                $m = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                $d = str_pad($parts[2], 2, '0', STR_PAD_LEFT);
                $thaiYear = $y + 543;
                $thaiDate = "{$d}/{$m}/{$thaiYear}";
                $symbol = $crow['symbol'] ? $crow['symbol'] : '—';

                if (!isset($tree[$thaiDate])) {
                    $tree[$thaiDate] = [];
                }
                if (!isset($tree[$thaiDate][$symbol])) {
                    $tree[$thaiDate][$symbol] = [
                        'count' => 0,
                        'profit_sum' => 0.0,
                        'candle_count' => intval($crow['candle_count']),
                        'min_epoch' => intval($crow['min_time']),
                        'max_epoch' => intval($crow['max_time']),
                        'date_iso' => $dayStr
                    ];
                } else {
                    $tree[$thaiDate][$symbol]['candle_count'] += intval($crow['candle_count']);
                    $tree[$thaiDate][$symbol]['date_iso'] = $dayStr;
                }
            }
        } catch (Exception $eCandles) {
            // ตาราง candles อาจยังไม่มีข้อมูล
        }
    }

    // 3. ดึงประวัติการเทรด (derivTradeHistory) เพื่อคำนวณกำไร/ขาดทุน และจำนวน Trades
    $whereTrades = $serverCode !== null ? "WHERE serverCode = :serverCode" : "";
    $sqlTrades = "SELECT purchase_time, symbol, buy_price, sell_price, profit
                  FROM derivTradeHistory 
                  {$whereTrades}
                  ORDER BY purchase_time DESC";
    $stmtTrades = $db->prepare($sqlTrades);
    if ($serverCode !== null) {
        $stmtTrades->execute([':serverCode' => $serverCode]);
    } else {
        $stmtTrades->execute();
    }
    $tradeRows = $stmtTrades->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tradeRows as $row) {
        $epoch = intval($row['purchase_time']);
        if ($epoch <= 0) continue;

        // แปลงเวลาสากล (Epoch) เป็นเวลาท้องถิ่น
        $dt = new DateTime("@$epoch");
        $dt->setTimezone($timezone);

        $day = $dt->format('d');
        $month = $dt->format('m');
        $year = intval($dt->format('Y'));
        $dayStr = $dt->format('Y-m-d');

        // แปลงปีเป็น พ.ศ. ไทย
        $thaiYear = $year + 543;
        $thaiDate = "{$day}/{$month}/{$thaiYear}";

        $symbol = $row['symbol'] ? $row['symbol'] : '—';
        $buyPrice = floatval($row['buy_price']);
        $sellPrice = isset($row['sell_price']) ? floatval($row['sell_price']) : 0.0;
        $profit = isset($row['profit']) ? floatval($row['profit']) : ($sellPrice - $buyPrice);

        if (!isset($tree[$thaiDate])) {
            $tree[$thaiDate] = [];
        }

        if (!isset($tree[$thaiDate][$symbol])) {
            $tree[$thaiDate][$symbol] = [
                'count' => 0,
                'profit_sum' => 0.0,
                'candle_count' => 0,
                'min_epoch' => $epoch,
                'max_epoch' => $epoch,
                'date_iso' => $dayStr
            ];
        }

        $tree[$thaiDate][$symbol]['count']++;
        $tree[$thaiDate][$symbol]['profit_sum'] += $profit;
        $tree[$thaiDate][$symbol]['date_iso'] = $dayStr;

        if ($epoch < $tree[$thaiDate][$symbol]['min_epoch']) {
            $tree[$thaiDate][$symbol]['min_epoch'] = $epoch;
        }
        if ($epoch > $tree[$thaiDate][$symbol]['max_epoch']) {
            $tree[$thaiDate][$symbol]['max_epoch'] = $epoch;
        }
    }

    // 4. ดึงประวัติการเทรดจาก vpsTradeData เพื่อนำมาแสดงใน Tree ด้วย
    try {
        $whereGcp = $serverCode !== null ? "WHERE serverCode = :serverCode" : "";
        $sqlGcp = "SELECT purchaseTime, symbol, ThisProfit, WinStatus FROM vpsTradeData {$whereGcp} ORDER BY purchaseTime DESC";
        $stmtGcp = $db->prepare($sqlGcp);
        if ($serverCode !== null) {
            $stmtGcp->execute([':serverCode' => $serverCode]);
        } else {
            $stmtGcp->execute();
        }
        $gcpRows = $stmtGcp->fetchAll(PDO::FETCH_ASSOC);

        foreach ($gcpRows as $grow) {
            $epoch = intval($grow['purchaseTime']);
            if ($epoch <= 0) continue;

            $dt = new DateTime("@$epoch");
            $dt->setTimezone($timezone);

            $day = $dt->format('d');
            $month = $dt->format('m');
            $year = intval($dt->format('Y'));
            $dayStr = $dt->format('Y-m-d');
            $thaiYear = $year + 543;
            $thaiDate = "{$day}/{$month}/{$thaiYear}";

            $symbol = !empty($grow['symbol']) ? $grow['symbol'] : '—';
            $profit = isset($grow['ThisProfit']) ? floatval($grow['ThisProfit']) : 0.0;

            if (!isset($tree[$thaiDate])) {
                $tree[$thaiDate] = [];
            }

            if (!isset($tree[$thaiDate][$symbol])) {
                $tree[$thaiDate][$symbol] = [
                    'count' => 0,
                    'profit_sum' => 0.0,
                    'candle_count' => 0,
                    'gcp_count' => 0,
                    'min_epoch' => $epoch,
                    'max_epoch' => $epoch,
                    'date_iso' => $dayStr
                ];
            }

            if (!isset($tree[$thaiDate][$symbol]['gcp_count'])) {
                $tree[$thaiDate][$symbol]['gcp_count'] = 0;
            }

            $tree[$thaiDate][$symbol]['gcp_count']++;

            // ถ้าไม่มี derivTradeHistory ให้ใช้ count และ profit_sum จาก vpsTradeData
            if ($tree[$thaiDate][$symbol]['count'] === 0) {
                $tree[$thaiDate][$symbol]['profit_sum'] += $profit;
            }

            if ($epoch < $tree[$thaiDate][$symbol]['min_epoch']) {
                $tree[$thaiDate][$symbol]['min_epoch'] = $epoch;
            }
            if ($epoch > $tree[$thaiDate][$symbol]['max_epoch']) {
                $tree[$thaiDate][$symbol]['max_epoch'] = $epoch;
            }
        }

        // ปรับ count ให้รวม gcp_count ถ้า count จาก derivTradeHistory เป็น 0
        foreach ($tree as $tDate => $syms) {
            foreach ($syms as $sKey => $sVal) {
                if ($sVal['count'] === 0 && !empty($sVal['gcp_count'])) {
                    $tree[$tDate][$sKey]['count'] = $sVal['gcp_count'];
                }
            }
        }
    } catch (Exception $eGcp) {
        // ข้ามถ้าเกิดข้อผิดพลาด
    }

    echo json_encode([
        'success' => true,
        'tree'    => $tree
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
