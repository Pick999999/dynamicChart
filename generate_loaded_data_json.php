<?php
/**
 * generate_loaded_data_json.php
 * ดึงข้อมูลสถิติวันเวลาที่ถูก load มาแล้วของตารางในกลุ่ม 3 และ 4 จาก MySQL
 * โดยเพิ่ม field serverCode เพื่อแยกตามแต่ละ server
 */

require_once __DIR__ . '/php/db.php';

try {
    $pdo = getDbConnection();

    // กำหนดโครงสร้างตารางของกลุ่ม 3 และ 4 พร้อม query ที่แยก serverCode
    $groupsDef = [
        [
            'groupcode' => 3,
            'groupname' => 'สำหรับการวิเคราะห์แท่งเทียน+indicator',
            'tables' => [
                [
                    'tablename' => 'full_analysis_data',
                    'has_server' => true,
                    'sql' => "
                        SELECT 
                            serverCode,
                            DATE(candletime_display) as d,
                            DATE_FORMAT(MIN(candletime_display), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(MAX(candletime_display), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM full_analysis_data
                        WHERE candletime_display IS NOT NULL
                        GROUP BY serverCode, DATE(candletime_display)
                        ORDER BY d ASC, serverCode ASC
                    "
                ],
                [
                    'tablename' => 'ticks',
                    'has_server' => true,
                    'sql' => "
                        SELECT 
                            serverCode,
                            DATE(FROM_UNIXTIME(time)) as d,
                            DATE_FORMAT(FROM_UNIXTIME(MIN(time)), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(FROM_UNIXTIME(MAX(time)), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM ticks
                        WHERE time > 1000000000
                        GROUP BY serverCode, DATE(FROM_UNIXTIME(time))
                        ORDER BY d ASC, serverCode ASC
                    "
                ],
                [
                    'tablename' => 'candles',
                    'has_server' => true,
                    'sql' => "
                        SELECT 
                            serverCode,
                            DATE(FROM_UNIXTIME(time)) as d,
                            DATE_FORMAT(FROM_UNIXTIME(MIN(time)), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(FROM_UNIXTIME(MAX(time)), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM candles
                        WHERE time > 1000000000
                        GROUP BY serverCode, DATE(FROM_UNIXTIME(time))
                        ORDER BY d ASC, serverCode ASC
                    "
                ],
                [
                    'tablename' => 'candle_analysis',
                    'has_server' => true,
                    'sql' => "
                        SELECT 
                            serverCode,
                            DATE(FROM_UNIXTIME(timestamp)) as d,
                            DATE_FORMAT(FROM_UNIXTIME(MIN(timestamp)), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(FROM_UNIXTIME(MAX(timestamp)), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM candle_analysis
                        WHERE timestamp > 1000000000
                        GROUP BY serverCode, DATE(FROM_UNIXTIME(timestamp))
                        ORDER BY d ASC, serverCode ASC
                    "
                ]
            ]
        ],
        [
            'groupcode' => 4,
            'groupname' => 'สำหรับวิเคราะห์ ผลการเทรด',
            'tables' => [
                [
                    'tablename' => 'vpstradedata',
                    'has_server' => true,
                    'sql' => "
                        SELECT 
                            serverCode,
                            DATE(FROM_UNIXTIME(purchaseTime)) as d,
                            DATE_FORMAT(FROM_UNIXTIME(MIN(purchaseTime)), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(FROM_UNIXTIME(MAX(purchaseTime)), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM vpsTradeData
                        WHERE purchaseTime > 1000000000
                        GROUP BY serverCode, DATE(FROM_UNIXTIME(purchaseTime))
                        ORDER BY d ASC, serverCode ASC
                    "
                ],
                [
                    'tablename' => 'derivtradehistory',
                    'has_server' => true,
                    'sql' => "
                        SELECT 
                            serverCode,
                            DATE(FROM_UNIXTIME(purchase_time)) as d,
                            DATE_FORMAT(FROM_UNIXTIME(MIN(purchase_time)), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(FROM_UNIXTIME(MAX(purchase_time)), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM derivTradeHistory
                        WHERE purchase_time > 1000000000
                        GROUP BY serverCode, DATE(FROM_UNIXTIME(purchase_time))
                        ORDER BY d ASC, serverCode ASC
                    "
                ],
                [
                    'tablename' => 'tradehead',
                    'has_server' => true,
                    'sql' => "
                        SELECT 
                            serverCode,
                            DATE(startTimeTrade) as d,
                            DATE_FORMAT(MIN(startTimeTrade), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(MAX(startTimeTrade), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM tradeHead
                        WHERE startTimeTrade IS NOT NULL
                        GROUP BY serverCode, DATE(startTimeTrade)
                        ORDER BY d ASC, serverCode ASC
                    "
                ],
                [
                    'tablename' => 'trade_history',
                    'has_server' => false,
                    'sql' => "
                        SELECT 
                            NULL as serverCode,
                            DATE(FROM_UNIXTIME(purchase_time)) as d,
                            DATE_FORMAT(FROM_UNIXTIME(MIN(purchase_time)), '%Y-%m-%d %H:%i:%s') as startdatetime,
                            DATE_FORMAT(FROM_UNIXTIME(MAX(purchase_time)), '%Y-%m-%d %H:%i:%s') as stopdatetime,
                            COUNT(*) as numData
                        FROM trade_history
                        WHERE purchase_time > 1000000000
                        GROUP BY DATE(FROM_UNIXTIME(purchase_time))
                        ORDER BY d ASC
                    "
                ]
            ]
        ]
    ];

    $result = [];

    foreach ($groupsDef as $g) {
        $tableList = [];

        foreach ($g['tables'] as $t) {
            $tableName = $t['tablename'];
            $sql = $t['sql'];

            $dataDate = [];
            try {
                $stmt = $pdo->query($sql);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rawServer = $row['serverCode'];
                    $srvVal = null;
                    if ($rawServer !== null) {
                        $srvVal = is_numeric($rawServer) ? (int)$rawServer : $rawServer;
                    }

                    $dataDate[] = [
                        'serverCode'    => $srvVal,
                        'startdatetime' => $row['startdatetime'],
                        'stopdatetime'  => $row['stopdatetime'],
                        'จำนวนข้อมูล'     => (int)$row['numData']
                    ];
                }
            } catch (Exception $ex) {
                error_log("Error querying $tableName: " . $ex->getMessage());
            }

            $tableList[] = [
                'tablename' => $tableName,
                'dataDate'  => $dataDate
            ];
        }

        $result[] = [
            'groupcode' => $g['groupcode'],
            'groupname' => $g['groupname'],
            'tablelist' => $tableList
        ];
    }

    $jsonOutput = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // บันทึกไฟล์ JSON ทั้งสองชื่อเพื่อความเข้ากันได้
    $outputFile1 = __DIR__ . '/table_loaded_data.json';
    $outputFile2 = __DIR__ . '/table_load_data.json';
    file_put_contents($outputFile1, $jsonOutput);
    file_put_contents($outputFile2, $jsonOutput);

    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'success' => true,
            'message' => 'สร้าง table_loaded_data.json และ table_load_data.json สำเร็จ',
            'generated_at' => date('Y-m-d H:i:s'),
            'total_groups' => count($result),
            'data' => $result
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo "Successfully generated table_loaded_data.json and table_load_data.json with serverCode!\n";
    foreach ($result as $grp) {
        echo "Group {$grp['groupcode']} ({$grp['groupname']}):\n";
        foreach ($grp['tablelist'] as $tbl) {
            echo "  - {$tbl['tablename']}: " . count($tbl['dataDate']) . " date/server entries\n";
        }
    }

} catch (Exception $e) {
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json; charset=utf-8', true, 500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo "Error: " . $e->getMessage() . "\n";
}
