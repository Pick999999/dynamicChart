<?php
// api-get-deriv-accounts.php - ดึงรายชื่อ Deriv Accounts จาก MySQL

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // เชื่อมต่อ MySQL
    $host = 'localhost';
    $dbname = 'dynamic_chart';
    $username = 'root'; // แก้ไขตามของคุณ
    $password = ''; // แก้ไขตามของคุณ
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // ดึงข้อมูลจาก derivAccount table
    $sql = "SELECT * FROM derivAccount WHERE status = 1 ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    $accounts = $stmt->fetchAll();
    
    if (empty($accounts)) {
        echo json_encode([
            'success' => false,
            'error' => 'No active accounts found in database'
        ]);
        exit;
    }
    
    // ส่งข้อมูลกลับ
    echo json_encode([
        'success' => true,
        'accounts' => $accounts,
        'total' => count($accounts)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'hint' => 'Please check database connection settings'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
