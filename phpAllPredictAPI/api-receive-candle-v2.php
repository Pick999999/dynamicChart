<?php
// api-receive-candle-v2.php - Version 2 (In-Process Execution, No shell_exec)
date_default_timezone_set('Asia/Bangkok');

// เพิ่มเวลาและหน่วยความจำสำหรับการประมวลผลแท่งเทียนจำนวนมาก
@ini_set('memory_limit', '512M');
@set_time_limit(300);

// ปิดการแสดง error/warning บนหน้าเว็บเพื่อป้องกัน JSON เสียหาย
@ini_set('display_errors', 0);
@error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// อ่านข้อมูล
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['candles'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid data format. Expected: {"candles": [...], "assetCode": "..."}'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$candleData = $data['candles'];
$assetCode = isset($data['assetCode']) ? $data['assetCode'] : 'R_10';

// Validate
if (empty($candleData) || !is_array($candleData)) {
    echo json_encode([
        'success' => false,
        'error' => 'Candle data is empty'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (count($candleData) < 21) {
    echo json_encode([
        'success' => false,
        'error' => 'Candle data must have at least 21 candles for analysis (received: ' . count($candleData) . ')'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// บันทึกเป็นไฟล์ที่ index.php อ่านได้
$rawDataFile = 'RawData/rawData2.json';
$dir = dirname($rawDataFile);
if (!file_exists($dir)) {
    @mkdir($dir, 0777, true);
}

// แปลง time เป็น epoch ถ้าจำเป็น และเพิ่ม thisColor
$processedCandles = [];

foreach ($candleData as $candle) {
    $timeVal = isset($candle['time']) ? $candle['time'] : (isset($candle['epoch']) ? $candle['epoch'] : 0);
    $processed = [
        'epoch' => $timeVal,
        'time' => $timeVal,
        'open' => floatval($candle['open']),
        'high' => floatval($candle['high']),
        'low' => floatval($candle['low']),
        'close' => floatval($candle['close'])
    ];
    
    // คำนวณ thisColor
    if ($processed['close'] > $processed['open']) {
        $processed['thisColor'] = 'Green';
    } elseif ($processed['close'] < $processed['open']) {
        $processed['thisColor'] = 'Red';
    } else {
        $processed['thisColor'] = 'Equal';
    }
    
    $processedCandles[] = $processed;
}

@file_put_contents($rawDataFile, json_encode($processedCandles, JSON_PRETTY_PRINT));

// เรียก index.php โดยตรงในกระบวนการเดียวกัน (ไม่ใช้ shell_exec เพื่อให้ทำงานได้บน Web Hosting ทุกที่)
try {
    define('INDEX_INCLUDED_AS_LIB', true);
    
    // ปิดกั้น output buffer ชั่วคราว ป้องกัน HTML หรือคำเตือนรั่วไหล
    ob_start();
    require_once __DIR__ . '/index.php';
    
    $requestData = [
        'rawData' => $processedCandles,
        'assetCode' => $assetCode
    ];
    
    $analysisResult = main($requestData);
    
    // เคลียร์ buffer ที่อาจมีข้อความหรือ HTML ออก
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    $tradeArray = [];
    if (is_object($analysisResult) && isset($analysisResult->labStJson)) {
        $tradeArray = $analysisResult->labStJson;
    } elseif (is_array($analysisResult) && isset($analysisResult['labStJson'])) {
        $tradeArray = $analysisResult['labStJson'];
    }
    
    $result = [
        'labStJson' => $tradeArray
    ];
    
    // บันทึก output.json
    @file_put_contents('output.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    
    echo json_encode([
        'success' => true,
        'message' => 'Analysis completed',
        'data' => $result,
        'stats' => [
            'totalCandles' => count($candleData),
            'totalTrades' => count($tradeArray),
            'assetCode' => $assetCode
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Analysis error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
}
?>
