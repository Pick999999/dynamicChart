-- --------------------------------------------------------
-- Database: dynamic_chart
-- Table structure for table: vpsMaster
-- --------------------------------------------------------

USE `dynamic_chart`;

CREATE TABLE IF NOT EXISTS `vpsMaster` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vpsCode` VARCHAR(50) NOT NULL UNIQUE COMMENT 'รหัสระบุ VPS (Unique Code)',
    `vpsName` VARCHAR(100) NOT NULL COMMENT 'ชื่อเครื่อง VPS',
    `publicIP` VARCHAR(100) DEFAULT NULL COMMENT 'Public IP Address หรือ Domain Name',
    `privateIP` VARCHAR(100) DEFAULT NULL COMMENT 'Private / Local IP Address',
    `portno` VARCHAR(20) DEFAULT NULL COMMENT 'Port Number (เช่น 80, 8000, 8080)',
    `url` VARCHAR(255) DEFAULT NULL COMMENT 'Web URL / Endpoint',
    `remark` TEXT DEFAULT NULL COMMENT 'หมายเหตุ / รายละเอียดเพิ่มเติม',
    `DerivAccountID` INT DEFAULT NULL COMMENT 'ID เชื่อมโยงตาราง derivAccount',
    `image` LONGTEXT DEFAULT NULL COMMENT 'รูปภาพ VPS ในแบบ Base64 (Data URL หรือ Base64 String)',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_vpsCode` (`vpsCode`),
    INDEX `idx_vps_derivAccountID` (`DerivAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บข้อมูล Master ของเครื่อง VPS';

-- หากตารางมีอยู่แล้วแต่ยังไม่มีฟิลด์ portno, url, remark, DerivAccountID, image สามารถรันคำสั่งด้านล่างนี้ได้:
-- ALTER TABLE `vpsMaster` ADD COLUMN IF NOT EXISTS `portno` VARCHAR(20) DEFAULT NULL COMMENT 'Port Number' AFTER `privateIP`;
-- ALTER TABLE `vpsMaster` ADD COLUMN IF NOT EXISTS `url` VARCHAR(255) DEFAULT NULL COMMENT 'Web URL' AFTER `portno`;
-- ALTER TABLE `vpsMaster` ADD COLUMN IF NOT EXISTS `remark` TEXT DEFAULT NULL COMMENT 'หมายเหตุ' AFTER `url`;
-- ALTER TABLE `vpsMaster` ADD COLUMN IF NOT EXISTS `DerivAccountID` INT DEFAULT NULL COMMENT 'ID เชื่อมโยง derivAccount' AFTER `remark`;
-- ALTER TABLE `vpsMaster` ADD COLUMN IF NOT EXISTS `image` LONGTEXT DEFAULT NULL COMMENT 'รูปภาพ VPS ในแบบ Base64' AFTER `DerivAccountID`;
