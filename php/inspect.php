<?php
/**
 * inspect.php
 * แสดงรายชื่อตารางและข้อมูลทดสอบใน MySQL (dynamic_chart) เพื่อตรวจสอบความถูกต้อง
 */

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();
    
    echo "--- รายชื่อตารางใน Database (MySQL: dynamic_chart) ---\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- Table: " . $table . "\n";
        $count = $db->query("SELECT COUNT(*) FROM `" . $table . "`")->fetchColumn();
        echo "  จำนวนข้อมูล: " . $count . " แถว\n";
    }
    
    echo "\n--- ข้อมูลในตาราง users ---\n";
    $users = $db->query("SELECT id, username, email, role, created_at FROM users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Username: {$user['username']} | Email: {$user['email']} | Role: {$user['role']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
