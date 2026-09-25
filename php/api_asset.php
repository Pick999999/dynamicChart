<?php
/**
 * api_asset.php
 * REST API สำหรับจัดการและค้นหารายการ Asset ใน MySQL ตาราง `asset`
 * 
 * Endpoints:
 * - GET /php/api_asset.php (ดึงรายการสินทรัพย์ทั้งหมด รองรับ filter: ?market=synthetic_index&search=1HZ)
 * - GET /php/api_asset.php?action=sync (ซิงค์อัปเดตข้อมูลล่าสุดจาก Deriv.com)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? 'list';

try {
    $pdo = getDbConnection();

    if ($action === 'sync') {
        // ทำการซิงค์ข้อมูลสดจาก Deriv
        require_once __DIR__ . '/sync_deriv_assets.php';
        exit;
    }

    // Default: list assets
    $where = ["1=1"];
    $params = [];

    if (!empty($_GET['market'])) {
        $where[] = "`market` = :market";
        $params[':market'] = trim($_GET['market']);
    }

    if (!empty($_GET['submarket'])) {
        $where[] = "`submarket` = :submarket";
        $params[':submarket'] = trim($_GET['submarket']);
    }

    if (isset($_GET['is_active']) && $_GET['is_active'] !== '') {
        $where[] = "`is_active` = :is_active";
        $params[':is_active'] = (int)$_GET['is_active'];
    }

    if (!empty($_GET['search'])) {
        $kw = '%' . trim($_GET['search']) . '%';
        $where[] = "(`asset_code` LIKE :search OR `asset_name` LIKE :search)";
        $params[':search'] = $kw;
    }

    $whereClause = implode(" AND ", $where);
    $sql = "SELECT `id`, `asset_code`, `asset_name`, `market`, `submarket`, `subgroup`, `symbol_type`, `pip_size`, `exchange_is_open`, `is_trading_suspended`, `is_active`, `updated_at` 
            FROM `asset` 
            WHERE {$whereClause} 
            ORDER BY `market` ASC, `asset_code` ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary markets
    $marketsSql = "SELECT `market`, COUNT(*) as total FROM `asset` GROUP BY `market` ORDER BY total DESC";
    $marketSummary = $pdo->query($marketsSql)->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode([
        'status' => 'success',
        'total' => count($rows),
        'market_summary' => $marketSummary,
        'data' => $rows
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
