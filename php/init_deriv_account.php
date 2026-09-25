<?php
/**
 * init_deriv_account.php
 * สร้างตาราง derivAccount ในฐานข้อมูล MySQL และเพิ่มข้อมูลเริ่มต้น
 */

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    $tableSql = "CREATE TABLE IF NOT EXISTS derivAccount (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        appName VARCHAR(100) DEFAULT NULL,
        appId VARCHAR(100) DEFAULT NULL,
        tokenName VARCHAR(100) DEFAULT NULL,
        expiryDate DATE DEFAULT NULL,
        token VARCHAR(255) DEFAULT NULL,
        accountId VARCHAR(50) DEFAULT NULL,
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_accountId (accountId),
        INDEX idx_appId (appId)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($tableSql);
    echo "Table derivAccount created or already exists.\n";

    // ตรวจสอบและเพิ่มข้อมูลตัวอย่าง
    $stmt = $db->prepare("SELECT COUNT(*) FROM derivAccount WHERE email = ? AND accountId = ?");
    $stmt->execute(['fantathailandfanta@gmail.com', 'DOT94414158']);
    
    if ($stmt->fetchColumn() == 0) {
        $insertStmt = $db->prepare("INSERT INTO derivAccount 
            (email, appName, appId, tokenName, expiryDate, token, accountId, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insertStmt->execute([
            'fantathailandfanta@gmail.com',
            'fantathailand',
            '34i6ru7qJ0CQtfnWNTApX',
            'Oracle4',
            '2026-12-02',
            'pat_ab25a5233f2c9008a0f4e6930ed965be5c32fa2997b2afae4629b7f0dc93629d',
            'DOT94414158',
            'active'
        ]);
        echo "Inserted initial derivAccount record successfully.\n";
    } else {
        echo "Sample derivAccount record already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
