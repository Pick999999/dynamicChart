-- ==========================================================
-- SQL Script: Create table `asset` for dynamicChart
-- Purpose: บันทึกรายการ asset code และ asset name ทั้งหมดจาก Deriv.com
-- ==========================================================

CREATE TABLE IF NOT EXISTS `asset` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_code` VARCHAR(50) NOT NULL COMMENT 'รหัสย่อสินทรัพย์ เช่น 1HZ100V, R_50, frxEURUSD',
  `asset_name` VARCHAR(150) NOT NULL COMMENT 'ชื่อสินทรัพย์ เช่น Volatility 100 (1s) Index, EUR/USD',
  `market` VARCHAR(50) DEFAULT NULL COMMENT 'ตลาดหลัก เช่น synthetic_index, forex, commodities, indices, cryptocurrency',
  `submarket` VARCHAR(50) DEFAULT NULL COMMENT 'ตลาดย่อย เช่น random_index, major_pairs, crash_index, metals',
  `subgroup` VARCHAR(50) DEFAULT NULL COMMENT 'กลุ่มย่อย เช่น synthetics, baskets, none',
  `symbol_type` VARCHAR(50) DEFAULT NULL COMMENT 'ประเภท เช่น stockindex, forex, commodities, cryptocurrency',
  `pip_size` DECIMAL(12, 6) DEFAULT 0.000100 COMMENT 'ขนาด pip/tick size',
  `exchange_is_open` TINYINT(1) DEFAULT 1 COMMENT 'สถานะตลาดเปิด/ปิด (1=Open, 0=Closed)',
  `is_trading_suspended` TINYINT(1) DEFAULT 0 COMMENT 'สถานะระงับการซื้อขายชั่วคราว (1=Suspended, 0=Normal)',
  `is_active` TINYINT(1) DEFAULT 1 COMMENT 'สถานะเปิดใช้งานในระบบ (1=Active, 0=Inactive)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_asset_code` (`asset_code`),
  KEY `idx_market` (`market`),
  KEY `idx_submarket` (`submarket`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางรายการสินทรัพย์ทั้งหมดจาก Deriv.com';
