<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$possibleFiles = [
    __DIR__ . '/testLabBarrier.js',
    __DIR__ . '/labBarrier.js'
];

$code = '';
$loadedFrom = '';

foreach ($possibleFiles as $file) {
    if (file_exists($file)) {
        $code = file_get_contents($file);
        $loadedFrom = basename($file);
        break;
    }
}

echo json_encode([
    'success' => true,
    'file' => $loadedFrom,
    'code' => $code
], JSON_UNESCAPED_UNICODE);
