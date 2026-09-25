-- Create tables for storing tradeHead.json data
-- Trade Rounds Management System

USE dynamic_chart;

-- ตาราง 1: tradeRounds - ข้อมูลหลักของแต่ละรอบเทรด
CREATE TABLE IF NOT EXISTS tradeRounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serverCode INT NOT NULL DEFAULT 2,
    tradeRoundNo INT NOT NULL,
    usestrategyCode VARCHAR(50) DEFAULT NULL,
    startTimeTrade DATETIME DEFAULT NULL,
    stopTimeTrade DATETIME DEFAULT NULL,
    durationTrade VARCHAR(50) DEFAULT NULL,
    MaxLossCon INT DEFAULT NULL,
    totalAssets INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Unique constraint: serverCode + tradeRoundNo ต้องไม่ซ้ำกัน
    UNIQUE KEY uk_server_round (serverCode, tradeRoundNo),
    
    -- Indexes
    KEY idx_serverCode (serverCode),
    KEY idx_startTime (startTimeTrade),
    KEY idx_strategy (usestrategyCode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Trade rounds header information from tradeHead.json';

-- ตาราง 2: tradeRoundAssets - ข้อมูล Asset แต่ละตัวในแต่ละรอบ
CREATE TABLE IF NOT EXISTS tradeRoundAssets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tradeRoundId INT NOT NULL,
    serverCode INT NOT NULL DEFAULT 2,
    tradeRoundNo INT NOT NULL,
    assetCode VARCHAR(50) NOT NULL,
    MaxWinCon INT DEFAULT 0,
    MaxLossCon INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key to tradeRounds
    FOREIGN KEY (tradeRoundId) REFERENCES tradeRounds(id) ON DELETE CASCADE,
    
    -- Unique constraint: tradeRoundId + assetCode ต้องไม่ซ้ำกัน
    UNIQUE KEY uk_round_asset (tradeRoundId, assetCode),
    
    -- Indexes
    KEY idx_serverCode (serverCode),
    KEY idx_assetCode (assetCode),
    KEY idx_tradeRoundNo (tradeRoundNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Asset-level statistics for each trade round';

-- สร้าง View สำหรับดูข้อมูลแบบรวม
CREATE OR REPLACE VIEW vw_tradeRounds_summary AS
SELECT 
    r.id,
    r.serverCode,
    r.tradeRoundNo,
    r.usestrategyCode,
    r.startTimeTrade,
    r.stopTimeTrade,
    r.durationTrade,
    r.MaxLossCon as round_max_loss,
    r.totalAssets,
    COUNT(a.id) as asset_count,
    SUM(a.MaxWinCon) as total_max_wins,
    SUM(a.MaxLossCon) as total_max_losses,
    GROUP_CONCAT(a.assetCode ORDER BY a.assetCode) as assets,
    r.created_at
FROM tradeRounds r
LEFT JOIN tradeRoundAssets a ON r.id = a.tradeRoundId
GROUP BY r.id
ORDER BY r.serverCode, r.tradeRoundNo DESC;

-- แสดงข้อความสำเร็จ
SELECT 'Trade Rounds tables created successfully!' as status;
SELECT 'Tables created: tradeRounds, tradeRoundAssets, vw_tradeRounds_summary' as info;
