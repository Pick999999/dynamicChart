<?php
/**
 * api_strategy_case_codes.php
 * RESTful JSON API สำหรับจัดการตาราง strategy_case_codes (CRUD Operations)
 * 
 * ฟิลด์ที่ใช้: code_no, case_code, action (call, put, idle), case_desc, category, group_name, trend, description
 * (ยกเว้น created_at, updated_at)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = getDbConnection();

    // ตรวจสอบและสร้างตาราง strategy_case_codes อัตโนมัติ (หากยังไม่มี)
    $db->exec("CREATE TABLE IF NOT EXISTS `strategy_case_codes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `code_no` INT NOT NULL,
        `case_code` VARCHAR(100) NOT NULL,
        `action` CHAR(5) NOT NULL DEFAULT 'idle' COMMENT 'call,put,idle',
        `case_desc` VARCHAR(255) NOT NULL,
        `category` VARCHAR(50) NOT NULL,
        `group_name` VARCHAR(50) NOT NULL,
        `trend` VARCHAR(50) DEFAULT NULL,
        `description` TEXT NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_code_no` (`code_no`),
        INDEX `idx_case_code` (`case_code`),
        INDEX `idx_action` (`action`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    $inputRaw = file_get_contents('php://input');
    $inputData = json_decode($inputRaw, true);
    if (!is_array($inputData)) {
        $inputData = $_POST;
    }

    $action = $_GET['action'] ?? $inputData['action'] ?? ($method === 'GET' ? 'list' : '');

    // ==========================================
    // ACTION 1: LIST / SEARCH
    // ==========================================
    if ($action === 'list' || ($method === 'GET' && empty($_GET['action']))) {
        $search = trim($_GET['search'] ?? '');
        $actionFilter = trim($_GET['action_filter'] ?? '');
        $categoryFilter = trim($_GET['category'] ?? '');
        $groupFilter = trim($_GET['group_name'] ?? '');

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(`code_no` LIKE :kw OR `case_code` LIKE :kw OR `case_desc` LIKE :kw OR `category` LIKE :kw OR `group_name` LIKE :kw OR `trend` LIKE :kw OR `description` LIKE :kw)";
            $params[':kw'] = "%{$search}%";
        }

        if ($actionFilter !== '') {
            if ($actionFilter === 'none') {
                $where[] = "(`action` IS NULL OR `action` = '' OR `action` NOT IN ('call','put','idle'))";
            } else {
                $where[] = "`action` = :act";
                $params[':act'] = strtolower($actionFilter);
            }
        }

        if ($categoryFilter !== '') {
            $where[] = "`category` = :cat";
            $params[':cat'] = $categoryFilter;
        }

        if ($groupFilter !== '') {
            $where[] = "`group_name` = :grp";
            $params[':grp'] = $groupFilter;
        }

        $sql = "SELECT id, code_no, case_code, action, case_desc, category, group_name, trend, description FROM `strategy_case_codes`";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY `code_no` ASC, `id` ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch distinct categories and groups for filter dropdowns
        $categories = $db->query("SELECT DISTINCT `category` FROM `strategy_case_codes` WHERE `category` IS NOT NULL AND `category` != '' ORDER BY `category` ASC")->fetchAll(PDO::FETCH_COLUMN);
        $groups = $db->query("SELECT DISTINCT `group_name` FROM `strategy_case_codes` WHERE `group_name` IS NOT NULL AND `group_name` != '' ORDER BY `group_name` ASC")->fetchAll(PDO::FETCH_COLUMN);

        respond([
            'status' => 'success',
            'count' => count($records),
            'categories' => $categories,
            'groups' => $groups,
            'data' => $records
        ]);
    }

    // ==========================================
    // ACTION 2: GET SINGLE
    // ==========================================
    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? $inputData['id'] ?? 0);
        if ($id <= 0) {
            respond(['status' => 'error', 'message' => 'Invalid ID specified'], 400);
        }

        $stmt = $db->prepare("SELECT id, code_no, case_code, action, case_desc, category, group_name, trend, description FROM `strategy_case_codes` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            respond(['status' => 'error', 'message' => 'Record not found'], 404);
        }

        respond(['status' => 'success', 'data' => $row]);
    }

    // ==========================================
    // ACTION 3: STATS
    // ==========================================
    if ($action === 'stats') {
        $total = (int)$db->query("SELECT COUNT(*) FROM `strategy_case_codes`")->fetchColumn();
        $callCount = (int)$db->query("SELECT COUNT(*) FROM `strategy_case_codes` WHERE LOWER(`action`) = 'call'")->fetchColumn();
        $putCount = (int)$db->query("SELECT COUNT(*) FROM `strategy_case_codes` WHERE LOWER(`action`) = 'put'")->fetchColumn();
        $idleCount = (int)$db->query("SELECT COUNT(*) FROM `strategy_case_codes` WHERE LOWER(`action`) = 'idle'")->fetchColumn();
        $unassigned = $total - ($callCount + $putCount + $idleCount);

        respond([
            'status' => 'success',
            'data' => [
                'total' => $total,
                'call' => $callCount,
                'put' => $putCount,
                'idle' => $idleCount,
                'unassigned' => $unassigned
            ]
        ]);
    }

    // ==========================================
    // ACTION 4: QUICK SET ACTION (3 BUTTONS)
    // ==========================================
    if ($action === 'set_action' || in_array(strtolower($action), ['call', 'put', 'idle'], true)) {
        $id = (int)($inputData['id'] ?? $_GET['id'] ?? 0);
        if (in_array(strtolower($action), ['call', 'put', 'idle'], true)) {
            $newAction = strtolower($action);
        } else {
            $newAction = strtolower(trim($inputData['new_action'] ?? $inputData['action_val'] ?? $inputData['action'] ?? ''));
        }

        if ($id <= 0) {
            respond(['status' => 'error', 'message' => 'Invalid ID specified'], 400);
        }

        $validActions = ['call', 'put', 'idle'];
        if (!in_array($newAction, $validActions, true)) {
            respond(['status' => 'error', 'message' => 'Action must be one of: call, put, idle'], 400);
        }

        $stmt = $db->prepare("UPDATE `strategy_case_codes` SET `action` = :act WHERE `id` = :id");
        $stmt->execute([
            ':act' => $newAction,
            ':id' => $id
        ]);

        respond([
            'status' => 'success',
            'message' => "Action updated to '{$newAction}' successfully",
            'id' => $id,
            'action' => $newAction
        ]);
    }

    // ==========================================
    // ACTION 5: CREATE
    // ==========================================
    if ($action === 'create') {
        $codeNo = isset($inputData['code_no']) ? (int)$inputData['code_no'] : null;
        $caseCode = trim($inputData['case_code'] ?? '');
        $actVal = strtolower(trim($inputData['action'] ?? 'idle'));
        $caseDesc = trim($inputData['case_desc'] ?? '');
        $category = trim($inputData['category'] ?? '');
        $groupName = trim($inputData['group_name'] ?? '');
        $trend = trim($inputData['trend'] ?? '');
        $description = trim($inputData['description'] ?? '');

        if ($codeNo === null || $caseCode === '') {
            respond(['status' => 'error', 'message' => 'code_no and case_code are required'], 400);
        }

        if (!in_array($actVal, ['call', 'put', 'idle', ''])) {
            $actVal = 'idle';
        }

        // Check if code_no or case_code already exists
        $checkStmt = $db->prepare("SELECT id FROM `strategy_case_codes` WHERE `code_no` = :cn OR `case_code` = :cc LIMIT 1");
        $checkStmt->execute([':cn' => $codeNo, ':cc' => $caseCode]);
        if ($checkStmt->fetch()) {
            respond(['status' => 'error', 'message' => "Case code #{$codeNo} ({$caseCode}) already exists"], 409);
        }

        $sql = "INSERT INTO `strategy_case_codes` 
                (`code_no`, `case_code`, `action`, `case_desc`, `category`, `group_name`, `trend`, `description`) 
                VALUES (:code_no, :case_code, :action, :case_desc, :category, :group_name, :trend, :description)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':code_no' => $codeNo,
            ':case_code' => $caseCode,
            ':action' => $actVal,
            ':case_desc' => $caseDesc,
            ':category' => $category,
            ':group_name' => $groupName,
            ':trend' => $trend,
            ':description' => $description
        ]);

        $newId = (int)$db->lastInsertId();

        respond([
            'status' => 'success',
            'message' => 'New case code created successfully',
            'id' => $newId
        ], 201);
    }

    // ==========================================
    // ACTION 6: UPDATE
    // ==========================================
    if ($action === 'update') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            respond(['status' => 'error', 'message' => 'Invalid ID specified for update'], 400);
        }

        $codeNo = isset($inputData['code_no']) ? (int)$inputData['code_no'] : null;
        $caseCode = trim($inputData['case_code'] ?? '');
        $actVal = strtolower(trim($inputData['action'] ?? 'idle'));
        $caseDesc = trim($inputData['case_desc'] ?? '');
        $category = trim($inputData['category'] ?? '');
        $groupName = trim($inputData['group_name'] ?? '');
        $trend = trim($inputData['trend'] ?? '');
        $description = trim($inputData['description'] ?? '');

        if ($codeNo === null || $caseCode === '') {
            respond(['status' => 'error', 'message' => 'code_no and case_code are required'], 400);
        }

        if (!in_array($actVal, ['call', 'put', 'idle', ''])) {
            $actVal = 'idle';
        }

        // Check if code_no or case_code duplicates another row
        $checkStmt = $db->prepare("SELECT id FROM `strategy_case_codes` WHERE (`code_no` = :cn OR `case_code` = :cc) AND `id` != :id LIMIT 1");
        $checkStmt->execute([':cn' => $codeNo, ':cc' => $caseCode, ':id' => $id]);
        if ($checkStmt->fetch()) {
            respond(['status' => 'error', 'message' => "Another case code with #{$codeNo} or code {$caseCode} already exists"], 409);
        }

        $sql = "UPDATE `strategy_case_codes` SET 
                `code_no` = :code_no,
                `case_code` = :case_code,
                `action` = :action,
                `case_desc` = :case_desc,
                `category` = :category,
                `group_name` = :group_name,
                `trend` = :trend,
                `description` = :description
                WHERE `id` = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':code_no' => $codeNo,
            ':case_code' => $caseCode,
            ':action' => $actVal,
            ':case_desc' => $caseDesc,
            ':category' => $category,
            ':group_name' => $groupName,
            ':trend' => $trend,
            ':description' => $description,
            ':id' => $id
        ]);

        respond([
            'status' => 'success',
            'message' => 'Case code updated successfully',
            'id' => $id
        ]);
    }

    // ==========================================
    // ACTION 7: DELETE
    // ==========================================
    if ($action === 'delete') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            respond(['status' => 'error', 'message' => 'Invalid ID specified for delete'], 400);
        }

        $stmt = $db->prepare("DELETE FROM `strategy_case_codes` WHERE `id` = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            respond([
                'status' => 'success',
                'message' => 'Case code deleted successfully',
                'id' => $id
            ]);
        } else {
            respond(['status' => 'error', 'message' => 'Record not found or already deleted'], 404);
        }
    }

    // ==========================================
    // ACTION 8: DOWNLOAD / EXPORT TABLE AS JSON
    // ==========================================
    if ($action === 'download' || $action === 'export') {
        $sql = "SELECT id, code_no, case_code, action, case_desc, category, group_name, trend, description, created_at, updated_at 
                FROM `strategy_case_codes` 
                ORDER BY `code_no` ASC, `id` ASC";
        $stmt = $db->query($sql);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalize numeric types
        foreach ($records as &$row) {
            if (isset($row['id'])) $row['id'] = (int)$row['id'];
            if (isset($row['code_no'])) $row['code_no'] = (int)$row['code_no'];
        }
        unset($row);

        $format = strtolower(trim($_GET['format'] ?? 'array'));
        if ($format === 'structured' || $format === 'meta' || isset($_GET['with_meta'])) {
            $exportData = [
                'table'       => 'strategy_case_codes',
                'total'       => count($records),
                'exported_at' => date('c'),
                'data'        => $records
            ];
        } else {
            $exportData = $records;
        }

        $filename = 'strategy_case_codes.json';
        if (!empty($_GET['timestamp'])) {
            $filename = 'strategy_case_codes_' . date('Ymd_His') . '.json';
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode($exportData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    respond(['status' => 'error', 'message' => 'Unknown action: ' . htmlspecialchars($action)], 400);

} catch (PDOException $e) {
    respond([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ], 500);
} catch (Exception $e) {
    respond([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ], 500);
}
