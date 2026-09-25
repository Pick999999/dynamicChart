<?php
/**
 * save_case_codes.php
 * API สำหรับรับข้อมูล JSON ของ Case Codes ทั้ง 27 รูปแบบ
 * และทำการบันทึกลงในไฟล์ case_codes.json ทั้ง 2 Folder ในโปรเจกต์ (Root, indicator, และ php)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error'   => 'Method Not Allowed. Only POST is accepted.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        throw new Exception('ไม่พบข้อมูล Payload ที่ส่งเข้ามา (Empty input)');
    }

    $data = json_decode($rawInput, true);
    if (!is_array($data)) {
        throw new Exception('รูปแบบข้อมูล JSON ไม่ถูกต้อง (Invalid JSON format)');
    }

    // กรณีส่งเข้ามาเป็น array ของ cases โดยตรง หรือเป็น object ที่มี key "cases"
    $cases = [];
    if (isset($data['cases']) && is_array($data['cases'])) {
        $cases = $data['cases'];
    } elseif (isset($data[0]) && is_array($data[0])) {
        $cases = $data;
    } else {
        throw new Exception('ไม่พบรายการ cases ในข้อมูลที่ส่งมา');
    }

    // ตรวจสอบและ normalize โครงสร้างแต่ละ case
    $formattedCases = [];
    foreach ($cases as $c) {
        $codeNo = isset($c['codeNo']) ? intval($c['codeNo']) : 0;
        if ($codeNo <= 0) continue;

        $isSelected = isset($c['isSelectedToAction']) ? trim($c['isSelectedToAction']) : 'n';
        if (strtolower($isSelected) === 'y' || $isSelected === '1' || $isSelected === true) {
            $isSelected = 'y';
        } else {
            $isSelected = 'n';
        }

        $formattedCases[] = [
            'codeNo'             => $codeNo,
            'caseCode'           => isset($c['caseCode']) ? trim($c['caseCode']) : '',
            'caseDesc'           => isset($c['caseDesc']) ? trim($c['caseDesc']) : '',
            'group'              => isset($c['group']) ? trim($c['group']) : '',
            'trend'              => isset($c['trend']) ? $c['trend'] : null,
            'category'           => isset($c['category']) ? trim($c['category']) : 'CORE',
            'isSelectedToAction' => $isSelected,
            'description'        => isset($c['description']) ? trim($c['description']) : ''
        ];
    }

    // เรียงตาม codeNo
    usort($formattedCases, function($a, $b) {
        return $a['codeNo'] <=> $b['codeNo'];
    });

    // สร้างโครงสร้าง JSON หลัก
    $finalJsonData = [
        'total'        => count($formattedCases),
        'version'      => '5.0',
        'updated_at'   => date('c'),
        'source'       => 'detectTrend_V5.js',
        'cases'        => $formattedCases
    ];

    $jsonString = json_encode($finalJsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // กำหนด Path ปลายทางของทั้ง 2 โฟลเดอร์หลักในโปรเจกต์ (+ โฟลเดอร์ php)
    $baseDir = dirname(__DIR__); // d:\Rust\dynamicChart
    $targetPaths = [
        'root'      => $baseDir . DIRECTORY_SEPARATOR . 'case_codes.json',
        'indicator' => $baseDir . DIRECTORY_SEPARATOR . 'indicator' . DIRECTORY_SEPARATOR . 'case_codes.json',
        'php'       => __DIR__ . DIRECTORY_SEPARATOR . 'case_codes.json'
    ];

    $savedResults = [];
    foreach ($targetPaths as $key => $filePath) {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $written = file_put_contents($filePath, $jsonString);
        if ($written === false) {
            throw new Exception("ไม่สามารถเขียนไฟล์ลงที่ {$filePath} ได้");
        }
        $savedResults[$key] = [
            'path'  => $filePath,
            'bytes' => $written
        ];
    }

    echo json_encode([
        'success'      => true,
        'message'      => 'บันทึกข้อมูล case_codes.json ลงทั้ง 2 โฟลเดอร์เรียบร้อยแล้ว',
        'total'        => count($formattedCases),
        'saved_paths'  => $savedResults,
        'updated_at'   => $finalJsonData['updated_at']
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
