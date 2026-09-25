<?php
/**
 * save_config.php — PHP Script สำหรับจัดการไฟล์ DynamicChart Config ในโฟลเดอร์ pageconfig
 * ====================================================================================
 * รองรับทั้งการ:
 * 1. GET  : ดึงรายชื่อไฟล์ใน pageconfig หรืออ่านเนื้อหาไฟล์ที่เลือก
 * 2. POST : บันทึกไฟล์ JSON ใหม่ลงโฟลเดอร์ pageconfig
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$configDir = dirname(__DIR__) . '/pageconfig';
if (!file_exists($configDir)) {
    mkdir($configDir, 0777, true);
}

// -------------------------------------------------------------------------
//  GET: ดึงรายชื่อไฟล์ หรืออ่านไฟล์ที่ระบุ
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // ถ้ามีการระบุชื่อไฟล์ผ่าน ?file=filename.json
    if (isset($_GET['file']) && !empty($_GET['file'])) {
        $filename = basename(trim($_GET['file']));
        $filePath = $configDir . '/' . $filename;

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            echo $content;
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'File not found']);
        }
        exit;
    }

    // ถ้าไม่มี ?file= -> ส่งคืนรายชื่อไฟล์ทั้งหมดใน pageconfig
    $files = [];
    if (file_exists($configDir)) {
        $dirFiles = scandir($configDir);
        foreach ($dirFiles as $file) {
            if ($file !== '.' && $file !== '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'json') {
                $files[] = $file;
            }
        }
    }

    // เรียงตามเวลาแก้ไขล่าสุด (ใหม่สุดอยู่บน)
    usort($files, function($a, $b) use ($configDir) {
        return filemtime($configDir . '/' . $b) - filemtime($configDir . '/' . $a);
    });

    echo json_encode([
        'success' => true,
        'files'   => $files
    ]);
    exit;
}

// -------------------------------------------------------------------------
//  POST: บันทึกไฟล์ JSON ใหม่ลงโฟลเดอร์ pageconfig
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $payload = json_decode($inputJSON, true);

    if (!$payload) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        exit;
    }

    $filename = isset($payload['filename']) && !empty(trim($payload['filename']))
        ? trim($payload['filename'])
        : 'config_' . date('Y-m-d_H-i-s') . '.json';

    if (strtolower(substr($filename, -5)) !== '.json') {
        $filename .= '.json';
    }

    $filename = basename($filename);
    $configToSave = isset($payload['config']) ? $payload['config'] : $payload;

    $filePath = $configDir . '/' . $filename;
    $jsonData = json_encode($configToSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (file_put_contents($filePath, $jsonData) !== false) {
        echo json_encode([
            'success'  => true,
            'message'  => "บันทึกไฟล์ {$filename} เรียบร้อยแล้ว!",
            'filename' => $filename,
            'path'     => "pageconfig/{$filename}"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save config file']);
    }
    exit;
}
