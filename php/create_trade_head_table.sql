-- SQL Schema for tradeHead Table
-- Consolidates round header information and asset statistics into a single table

USE dynamic_chart;

CREATE TABLE IF NOT EXISTS `tradeHead` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `serverCode` INT NOT NULL DEFAULT 2,
    `tradeRoundNo` INT NOT NULL,
    `usestrategyCode` VARCHAR(50) DEFAULT NULL,
    `startTimeTrade` DATETIME DEFAULT NULL,
    `stopTimeTrade` DATETIME DEFAULT NULL,
    `durationTrade` VARCHAR(50) DEFAULT NULL,
    `totalAssets` INT DEFAULT 0,
    `assetCode` VARCHAR(50) NOT NULL,
    `MaxWinCon` INT DEFAULT 0,
    `MaxLossCon` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_server_round_asset` (`serverCode`, `tradeRoundNo`, `assetCode`),
    KEY `idx_server_round` (`serverCode`, `tradeRoundNo`),
    KEY `idx_assetCode` (`assetCode`),
    KEY `idx_startTime` (`startTimeTrade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Trade rounds header and asset configuration from tradeHead.json';
