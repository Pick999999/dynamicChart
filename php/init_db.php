<?php
/**
 * init_db.php
 * PHP Script สำหรับเชื่อมต่อ MySQL และสร้างตารางข้อมูลทั้งหมด
 * โหลดการตั้งค่าจาก configDB.php
 */

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/configDB.php';

$host = DB_HOST;
$port = DB_PORT;
$dbName = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

try {
    echo "กำลังเชื่อมต่อกับ MySQL Server ($host:$port)...\n";
    $pdoServer = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass);
    $pdoServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "กำลังตรวจสอบ/สร้างฐานข้อมูล `$dbName`...\n";
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "-> ฐานข้อมูล `$dbName` พร้อมใช้งาน\n\n";

    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. ตาราง page_configs
    echo "กำลังตรวจสอบและสร้างตาราง 'page_configs'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS page_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        config_data LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'page_configs' พร้อมใช้งาน\n\n";

    // 2. ตาราง users
    echo "กำลังตรวจสอบและสร้างตาราง 'users'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) UNIQUE,
        role VARCHAR(50) DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'users' พร้อมใช้งาน\n\n";

    // 3. ตาราง candles
    echo "กำลังตรวจสอบและสร้างตาราง 'candles'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS candles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        symbol VARCHAR(50) NOT NULL,
        granularity INT NOT NULL,
        time BIGINT NOT NULL,
        open DOUBLE NOT NULL,
        high DOUBLE NOT NULL,
        low DOUBLE NOT NULL,
        close DOUBLE NOT NULL,
        code_no_by_js INT DEFAULT NULL,
        case_code_by_js VARCHAR(100) DEFAULT NULL,
        suggestAction VARCHAR(50) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_candle (symbol, granularity, time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'candles' พร้อมใช้งาน\n\n";

    // 4. ตาราง ticks
    echo "กำลังตรวจสอบและสร้างตาราง 'ticks'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS ticks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        symbol VARCHAR(50) NOT NULL,
        time BIGINT NOT NULL,
        price DOUBLE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_tick (symbol, time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'ticks' พร้อมใช้งาน\n\n";

    // 5. ตาราง derivTradeHistory (และ trade_history สำหรับ backward compatibility)
    echo "กำลังตรวจสอบและสร้างตาราง 'derivTradeHistory'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS derivTradeHistory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serverCode INT DEFAULT 2,
        transaction_id BIGINT UNIQUE,
        contract_id BIGINT DEFAULT NULL,
        app_id INT DEFAULT NULL,
        purchase_time BIGINT DEFAULT NULL,
        sell_time BIGINT DEFAULT NULL,
        duration INT DEFAULT NULL,
        symbol VARCHAR(50) DEFAULT NULL,
        contract_type VARCHAR(50) DEFAULT NULL,
        barrier VARCHAR(50) DEFAULT NULL,
        high_barrier VARCHAR(50) DEFAULT NULL,
        low_barrier VARCHAR(50) DEFAULT NULL,
        buy_price DOUBLE DEFAULT NULL,
        sell_price DOUBLE DEFAULT NULL,
        profit DOUBLE DEFAULT NULL,
        payout DOUBLE DEFAULT NULL,
        currency VARCHAR(20) DEFAULT NULL,
        shortcode VARCHAR(255) DEFAULT NULL,
        longcode TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_serverCode (serverCode),
        INDEX idx_symbol_time (symbol, purchase_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'derivTradeHistory' พร้อมใช้งาน\n\n";

    $db->exec("CREATE TABLE IF NOT EXISTS trade_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id BIGINT UNIQUE,
        contract_id BIGINT DEFAULT NULL,
        app_id INT DEFAULT NULL,
        purchase_time BIGINT DEFAULT NULL,
        sell_time BIGINT DEFAULT NULL,
        duration INT DEFAULT NULL,
        symbol VARCHAR(50) DEFAULT NULL,
        contract_type VARCHAR(50) DEFAULT NULL,
        barrier VARCHAR(50) DEFAULT NULL,
        high_barrier VARCHAR(50) DEFAULT NULL,
        low_barrier VARCHAR(50) DEFAULT NULL,
        buy_price DOUBLE DEFAULT NULL,
        sell_price DOUBLE DEFAULT NULL,
        profit DOUBLE DEFAULT NULL,
        payout DOUBLE DEFAULT NULL,
        currency VARCHAR(20) DEFAULT NULL,
        shortcode VARCHAR(255) DEFAULT NULL,
        longcode TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 6. ตาราง candle_analysis
    echo "กำลังตรวจสอบและสร้างตาราง 'candle_analysis'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS candle_analysis (
        asset VARCHAR(50) NOT NULL,
        timestamp BIGINT NOT NULL,
        timeframe INT NOT NULL,
        time_display VARCHAR(100) DEFAULT NULL,
        open DOUBLE NOT NULL,
        high DOUBLE NOT NULL,
        low DOUBLE NOT NULL,
        close DOUBLE NOT NULL,
        color VARCHAR(30) DEFAULT NULL,
        trend VARCHAR(50) DEFAULT NULL,
        case_code VARCHAR(100) DEFAULT NULL,
        trend_score INT DEFAULT NULL,
        trend_strength VARCHAR(50) DEFAULT NULL,
        color_sequence VARCHAR(100) DEFAULT NULL,
        color_switches INT DEFAULT NULL,
        is_whipsaw INT DEFAULT NULL,
        whipsaw_status VARCHAR(50) DEFAULT NULL,
        is_spike INT DEFAULT NULL,
        analysis_data LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (asset, timestamp, timeframe)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'candle_analysis' พร้อมใช้งาน\n\n";

    // 7. ตาราง vpsTradeData
    echo "กำลังตรวจสอบและสร้างตาราง 'vpsTradeData'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS vpsTradeData (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serverCode INT DEFAULT 2,
        symbol VARCHAR(50) DEFAULT NULL,
        contractId BIGINT DEFAULT NULL,
        buyId BIGINT DEFAULT NULL,
        sellId BIGINT DEFAULT NULL,
        assetCode VARCHAR(50) DEFAULT NULL,
        entrySpot DOUBLE DEFAULT NULL,
        exitSpot DOUBLE DEFAULT NULL,
        diffSpot DOUBLE DEFAULT NULL,
        purchaseTime BIGINT DEFAULT NULL,
        purchaseTimeDisplay VARCHAR(100) DEFAULT NULL,
        tradeRoundNo INT DEFAULT NULL,
        tradeNo INT DEFAULT NULL,
        subTradeNo INT DEFAULT NULL,
        timeCandle BIGINT DEFAULT NULL,
        timeCandleDisplay VARCHAR(100) DEFAULT NULL,
        sellTime BIGINT DEFAULT NULL,
        sellTimeDisplay VARCHAR(100) DEFAULT NULL,
        actualDuration INT DEFAULT NULL,
        isAnomaly INT DEFAULT 0,
        thisColor VARCHAR(30) DEFAULT NULL,
        thisAction VARCHAR(30) DEFAULT NULL,
        targetColor VARCHAR(30) DEFAULT NULL,
        emaShortDirection VARCHAR(50) DEFAULT NULL,
        emaMediumDirection VARCHAR(50) DEFAULT NULL,
        MoneyTrade DOUBLE DEFAULT NULL,
        WinStatus VARCHAR(30) DEFAULT NULL,
        lossCon INT DEFAULT NULL,
        ThisProfit DOUBLE DEFAULT NULL,
        GrandBalance DOUBLE DEFAULT NULL,
        gaveUp INT DEFAULT 0,
        maxLossCon INT DEFAULT NULL,
        tradeStrategy VARCHAR(100) DEFAULT NULL,
        code_no INT DEFAULT NULL,
        codeStrategy VARCHAR(100) DEFAULT NULL,
        noiseCode VARCHAR(100) DEFAULT NULL,
        spotPriceCall DOUBLE DEFAULT NULL,
        spotPricePut DOUBLE DEFAULT NULL,
        spotCallPositionCode VARCHAR(100) DEFAULT NULL,
        spotPutPositionCode VARCHAR(100) DEFAULT NULL,
        whipsawZone INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_gcp_symbol_time (symbol, purchaseTime),
        KEY idx_gcp_contractId (contractId),
        KEY idx_gcp_buyId (buyId),
        KEY idx_gcp_code_no (code_no)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เก็บข้อมูลที่ได้ จาก การเทรดแต่ละครั้ง ที่บันทึกบน folder tradedata บน vps ของแต่ละวัน';");

    try {
        $checkCol = $db->query("SHOW COLUMNS FROM vpsTradeData LIKE 'serverCode'");
        if ($checkCol && $checkCol->rowCount() === 0) {
            $db->exec("ALTER TABLE vpsTradeData ADD COLUMN serverCode INT DEFAULT 2 AFTER id");
        }

        $checkCodeNo = $db->query("SHOW COLUMNS FROM vpsTradeData LIKE 'code_no'");
        if ($checkCodeNo && $checkCodeNo->rowCount() === 0) {
            $db->exec("ALTER TABLE vpsTradeData ADD COLUMN code_no INT DEFAULT NULL AFTER tradeStrategy");
        } else {
            $db->exec("ALTER TABLE vpsTradeData MODIFY COLUMN code_no INT DEFAULT NULL");
        }
    } catch (Exception $e) {
        // ข้ามข้อผิดพลาดกรณีไม่รองรับ SHOW COLUMNS
    }

    echo "-> ตาราง 'vpsTradeData' พร้อมใช้งาน\n\n";

    // 8. ตาราง case_codes
    echo "กำลังตรวจสอบและสร้างตาราง 'case_codes'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS case_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code_no INT NOT NULL UNIQUE,
        case_code VARCHAR(100) NOT NULL,
        case_desc VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        group_name VARCHAR(50) NOT NULL,
        trend VARCHAR(50) DEFAULT NULL,
        description TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'case_codes' พร้อมใช้งาน\n\n";

    // 9. ตาราง vpsMaster
    echo "กำลังตรวจสอบและสร้างตาราง 'vpsMaster'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS vpsMaster (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vpsCode VARCHAR(50) NOT NULL UNIQUE,
        vpsName VARCHAR(100) NOT NULL,
        publicIP VARCHAR(100) DEFAULT NULL,
        privateIP VARCHAR(100) DEFAULT NULL,
        portno VARCHAR(20) DEFAULT NULL,
        url VARCHAR(255) DEFAULT NULL,
        remark TEXT DEFAULT NULL,
        DerivAccountID INT DEFAULT NULL,
        image LONGTEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_vps_derivAccountID (DerivAccountID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    try {
        $checkCol = $db->query("SHOW COLUMNS FROM vpsMaster LIKE 'DerivAccountID'");
        if ($checkCol && $checkCol->rowCount() === 0) {
            $db->exec("ALTER TABLE vpsMaster ADD COLUMN DerivAccountID INT DEFAULT NULL AFTER remark");
        }
        $checkImgCol = $db->query("SHOW COLUMNS FROM vpsMaster LIKE 'image'");
        if ($checkImgCol && $checkImgCol->rowCount() === 0) {
            $db->exec("ALTER TABLE vpsMaster ADD COLUMN image LONGTEXT DEFAULT NULL COMMENT 'รูปภาพ VPS ในแบบ Base64' AFTER DerivAccountID");
        }
    } catch (Exception $e) {}

    echo "-> ตาราง 'vpsMaster' พร้อมใช้งาน\n\n";

    // 10. ตาราง derivAccount
    echo "กำลังตรวจสอบและสร้างตาราง 'derivAccount'...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS derivAccount (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "-> ตาราง 'derivAccount' พร้อมใช้งาน\n\n";


    // 9. View v_candles_vs_gcp_trades
    echo "กำลังตรวจสอบและสร้าง View 'v_candles_vs_gcp_trades'...\n";
    $db->exec("CREATE OR REPLACE VIEW v_candles_vs_gcp_trades AS
    SELECT 
        c.symbol,
        c.granularity,
        c.time AS candle_epoch,
        FROM_UNIXTIME(c.time) AS candle_time_th,
        FROM_UNIXTIME(g.purchaseTime) AS trade_time_th,
        
        c.open,
        c.high,
        c.low,
        c.close,
        
        c.code_no_by_js,
        c.case_code_by_js,
        g.codeStrategy AS gcp_code_strategy,
        CASE 
            WHEN c.case_code_by_js IS NULL OR g.codeStrategy IS NULL THEN 'NO_DATA'
            WHEN UPPER(TRIM(c.case_code_by_js)) = UPPER(TRIM(g.codeStrategy)) THEN 'MATCH ✅'
            ELSE 'MISMATCH ❌'
        END AS code_match_status,

        c.suggestAction AS js_suggest_action,
        g.thisAction AS gcp_actual_action,
        CASE 
            WHEN c.suggestAction IS NULL OR g.thisAction IS NULL THEN 'NO_DATA'
            WHEN UPPER(TRIM(c.suggestAction)) = UPPER(TRIM(g.thisAction)) THEN 'MATCH ✅'
            ELSE 'MISMATCH ❌'
        END AS action_match_status,

        g.contractId,
        g.tradeStrategy,
        g.entrySpot,
        g.exitSpot,
        g.diffSpot,
        g.WinStatus,
        g.ThisProfit,
        g.MoneyTrade,
        g.lossCon,
        g.whipsawZone

    FROM candles c
    INNER JOIN vpsTradeData g 
        ON c.symbol = g.symbol 
        AND (
            c.time = g.timeCandle 
            OR (c.time <= g.purchaseTime AND g.purchaseTime < c.time + c.granularity)
        )
    ORDER BY c.time DESC;");
    echo "-> View 'v_candles_vs_gcp_trades' พร้อมใช้งาน\n\n";

    // Seed users if empty
    $countUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($countUsers == 0) {
        echo "กำลังเพิ่มข้อมูลผู้ใช้เริ่มต้น (Seed data)...\n";
        $insertUser = $db->prepare("INSERT INTO users (username, email, role) VALUES (:username, :email, :role)");
        $insertUser->execute([
            ':username' => 'admin',
            ':email'    => 'admin@example.com',
            ':role'     => 'administrator'
        ]);
        $insertUser->execute([
            ':username' => 'trader_john',
            ':email'    => 'john@example.com',
            ':role'     => 'user'
        ]);
        echo "-> เพิ่มข้อมูลตัวอย่างผู้ใช้เรียบร้อยแล้ว\n\n";
    }

    echo "===========================================\n";
    echo "กระบวนการสร้างและเตรียม MySQL Database เสร็จสมบูรณ์!\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    echo "\n[ERROR] เกิดข้อผิดพลาดในการทำงานกับฐานข้อมูล:\n";
    echo $e->getMessage() . "\n";
}
?>
