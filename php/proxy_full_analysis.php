<?php
/**
 * proxy_full_analysis.php
 * PHP Proxy สำหรับเรียก API /getFullAnalysisData ไปยัง VPS / Rust Server
 * ป้องกันปัญหา Mixed Content และ Browser CORS
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$vpsUrl = isset($_GET['vps_url']) ? trim($_GET['vps_url']) : '';
$assetcode = isset($_GET['assetcode']) ? trim($_GET['assetcode']) : '';
$startdatetime = isset($_GET['startdatetime']) ? trim($_GET['startdatetime']) : '';
$enddatetime = isset($_GET['enddatetime']) ? trim($_GET['enddatetime']) : '';
$timeframe = isset($_GET['timeframe']) ? trim($_GET['timeframe']) : '1M';

if (empty($vpsUrl)) {
    // Default fallback to localhost:3000
    $vpsUrl = 'http://127.0.0.1:3000';
}

if (!preg_match('/^https?:\/\//i', $vpsUrl)) {
    $vpsUrl = 'http://' . $vpsUrl;
}

$vpsUrl = rtrim($vpsUrl, '/');

$queryParams = [
    'assetcode'     => $assetcode,
    'startdatetime' => $startdatetime,
    'enddatetime'   => $enddatetime,
    'timeframe'     => $timeframe
];

$targetUrl = $vpsUrl . '/getFullAnalysisData?' . http_build_query($queryParams);

$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false || $curlErr) {
    http_response_code(502);
    echo json_encode([
        'status'    => 'error',
        'error'     => 'Failed to connect to VPS analysis server: ' . $curlErr,
        'targetUrl' => $targetUrl
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code($httpCode ?: 200);
echo $response;
