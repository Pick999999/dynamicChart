<?php
/**
 * alter_vps_deriv.php
 * เพิ่มคอลัมน์ DerivAccountID ในตาราง vpsMaster และสร้างความสัมพันธ์กับ derivAccount(id)
 */

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    // 1. ตรวจสอบว่ามีคอลัมน์ DerivAccountID หรือยัง
    $cols = $db->query("SHOW COLUMNS FROM `vpsMaster` LIKE 'DerivAccountID'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE `vpsMaster` ADD COLUMN `DerivAccountID` INT DEFAULT NULL AFTER `remark`");
        echo "Successfully added column 'DerivAccountID' to 'vpsMaster'.\n";
    } else {
        echo "Column 'DerivAccountID' already exists in 'vpsMaster'.\n";
    }

    // 2. เพิ่ม Index
    try {
        $db->exec("ALTER TABLE `vpsMaster` ADD INDEX `idx_vps_derivAccountID` (`DerivAccountID`)");
        echo "Index 'idx_vps_derivAccountID' added.\n";
    } catch (Exception $e) {
        echo "Index notice: " . $e->getMessage() . "\n";
    }

    // 3. เพิ่ม Foreign Key เชื่อมกับ derivAccount(id)
    try {
        $db->exec("ALTER TABLE `vpsMaster` ADD CONSTRAINT `fk_vps_derivAccount` 
                   FOREIGN KEY (`DerivAccountID`) REFERENCES `derivAccount`(`id`) 
                   ON DELETE SET NULL ON UPDATE CASCADE");
        echo "Foreign key constraint 'fk_vps_derivAccount' added.\n";
    } catch (Exception $e) {
        echo "FK notice: " . $e->getMessage() . "\n";
    }

    // 4. เชื่อมโยง Oracle-Free-Tier-4 (id: 4) กับ Deriv Account (id: 1) เป็นตัวอย่างเริ่มต้นถ้ายังไม่มี
    $linkStmt = $db->prepare("UPDATE `vpsMaster` SET `DerivAccountID` = 1 WHERE `vpsCode` = '4' AND `DerivAccountID` IS NULL");
    $linkStmt->execute();
    if ($linkStmt->rowCount() > 0) {
        echo "Linked VPS 'Oracle-Free-Tier-4' (code: 4) to Deriv Account (id: 1).\n";
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
