<?php
// api-receive-candle.php - Bridge API สำหรับรับข้อมูล candle จาก deriv-full-analysis.html

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// อ่านข้อมูลที่ส่งมา
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['candles'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid data format. Required: {"candles": [...], "assetCode": "..."}'
    ]);
    exit;
}

$candleData = $data['candles'];
$assetCode = isset($data['assetCode']) ? $data['assetCode'] : 'R_10';

// Validate candle data
if (empty($candleData) || !is_array($candleData)) {
    echo json_encode([
        'success' => false,
        'error' => 'Candle data is empty or invalid'
    ]);
    exit;
}

// ตรวจสอบว่า candle แต่ละตัวมีข้อมูลครบถ้วน
$requiredFields = ['time', 'open', 'high', 'low', 'close'];
foreach ($candleData as $candle) {
    foreach ($requiredFields as $field) {
        if (!isset($candle[$field])) {
            echo json_encode([
                'success' => false,
                'error' => "Missing required field: {$field} in candle data"
            ]);
            exit;
        }
    }
}

// บันทึกข้อมูล candle เป็นไฟล์ชั่วคราว (สำหรับ debug)
$debugFile = 'RawData/api-candle-data.json';
$dir = dirname($debugFile);
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}
file_put_contents($debugFile, json_encode([
    'assetCode' => $assetCode,
    'totalCandles' => count($candleData),
    'firstCandle' => $candleData[0],
    'lastCandle' => $candleData[count($candleData) - 1],
    'receivedAt' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// เตรียมข้อมูลสำหรับส่งไปยัง index.php
$requestData = [
    'Mode' => 'getLab',
    'rawData' => $candleData,
    'assetCode' => $assetCode
];

// บันทึกข้อมูลลง temp file
$tempInputFile = 'RawData/temp-api-input.json';
file_put_contents($tempInputFile, json_encode($requestData));

// เรียก index.php โดยตรง
try {
    // เปลี่ยน php://input ให้อ่านจาก temp file แทน
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Mock php://input
    $GLOBALS['mock_input'] = json_encode($requestData);
    
    // Capture output จาก index.php
    ob_start();
    
    // ปิด error reporting ชั่วคราว
    $oldErrorReporting = error_reporting(0);
    
    include('index.php');
    
    // คืนค่า error reporting
    error_reporting($oldErrorReporting);
    
    $output = ob_get_clean();
    
    // ลบ warning/error messages ออก (เก็บแค่ JSON)
    $jsonStart = strpos($output, '{');
    if ($jsonStart !== false) {
        $output = substr($output, $jsonStart);
    }
    
    // Parse JSON response
    $result = json_decode($output, true);
    $jsonError = json_last_error();
    
    if ($result && $jsonError === JSON_ERROR_NONE) {
        // บันทึกผลลัพธ์เป็น output.json
        file_put_contents('output.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        // ส่งผลลัพธ์กลับ
        echo json_encode([
            'success' => true,
            'message' => 'Analysis completed',
            'data' => $result,
            'stats' => [
                'totalCandles' => count($candleData),
                'totalTrades' => isset($result['labStJson']) ? count($result['labStJson']) : 0,
                'assetCode' => $assetCode
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Debug: บันทึก raw output
        file_put_contents('RawData/debug-output.txt', $output);
        
        echo json_encode([
            'success' => false,
            'error' => 'Failed to parse analysis result. JSON Error: ' . json_last_error_msg(),
            'raw_output_preview' => substr($output, 0, 1000),
            'debug_file' => 'RawData/debug-output.txt'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Analysis error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

?>
