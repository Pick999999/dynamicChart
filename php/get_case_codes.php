<?php
/**
 * get_case_codes.php
 * API สำหรับดึงข้อมูลรายการ Case Codes ทั้ง 27 รูปแบบจากตาราง 'case_codes' ใน MySQL
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    $codeNo   = isset($_GET['code_no']) ? intval($_GET['code_no']) : null;
    $category = isset($_GET['category']) ? trim($_GET['category']) : null;
    $group    = isset($_GET['group']) ? trim($_GET['group']) : null;
    $search   = isset($_GET['q']) ? trim($_GET['q']) : null;

    $query = "SELECT * FROM case_codes WHERE 1=1";
    $params = [];

    if ($codeNo !== null && $codeNo > 0) {
        $query .= " AND code_no = :code_no";
        $params[':code_no'] = $codeNo;
    }

    if (!empty($category) && strtoupper($category) !== 'ALL') {
        $query .= " AND category = :category";
        $params[':category'] = strtoupper($category);
    }

    if (!empty($group) && strtoupper($group) !== 'ALL') {
        $query .= " AND group_name = :group";
        $params[':group'] = $group;
    }

    if (!empty($search)) {
        $query .= " AND (
            case_code LIKE :search 
            OR case_desc LIKE :search 
            OR description LIKE :search 
            OR CAST(code_no AS CHAR) LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }

    $query .= " ORDER BY code_no ASC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedRows = array_map(function($r) {
        return [
            'id'          => intval($r['id']),
            'code_no'     => intval($r['code_no']),
            'case_code'   => $r['case_code'],
            'case_desc'   => $r['case_desc'],
            'category'    => $r['category'],
            'group_name'  => $r['group_name'],
            'trend'       => $r['trend'],
            'description' => $r['description'],
            'created_at'  => $r['created_at'],
            'updated_at'  => $r['updated_at']
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'total'   => count($formattedRows),
        'cases'   => $formattedRows
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
