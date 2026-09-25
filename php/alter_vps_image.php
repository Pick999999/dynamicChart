<?php
/**
 * alter_vps_image.php
 * เพิ่มคอลัมน์ image (LONGTEXT) สำหรับเก็บรูปภาพแบบ Base64 ในตาราง vpsMaster
 */

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    // 1. ตรวจสอบว่ามีคอลัมน์ image หรือยัง
    $cols = $db->query("SHOW COLUMNS FROM `vpsMaster` LIKE 'image'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `image` LONGTEXT DEFAULT NULL COMMENT 'รูปภาพ VPS ในแบบ Base64 (Data URL หรือ Base64 String)' AFTER `DerivAccountID`");
        echo "Successfully added column 'image' (LONGTEXT) to 'vpsMaster'.\n";
    } else {
        echo "Column 'image' already exists in 'vpsMaster'.\n";
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
