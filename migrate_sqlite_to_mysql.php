<?php
/**
 * migrate_sqlite_to_mysql.php
 * Script for migrating all tables and data from SQLite (database.db) to MySQL (XAMPP).
 */

ini_set('memory_limit', '1024M');
set_time_limit(0);

$sqliteFile = __DIR__ . '/database.db';
if (file_exists(__DIR__ . '/php/configDB.php')) {
    require_once __DIR__ . '/php/configDB.php';
}

$mysqlHost = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
$mysqlPort = defined('DB_PORT') ? DB_PORT : 3306;
$mysqlUser = defined('DB_USER') ? DB_USER : 'root';
$mysqlPass = defined('DB_PASS') ? DB_PASS : '';
$mysqlDb   = defined('DB_NAME') ? DB_NAME : 'dynamic_chart';

echo "====================================================\n";
echo "  SQLite -> MySQL (XAMPP) Migration Script\n";
echo "====================================================\n\n";

if (!file_exists($sqliteFile)) {
    die("[ERROR] SQLite database file not found: $sqliteFile\n");
}

try {
    // 1. Connect to SQLite
    echo "[1/4] Connecting to SQLite ($sqliteFile)...\n";
    $sqlite = new PDO("sqlite:" . $sqliteFile);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo " -> Connected to SQLite successfully.\n\n";

    // 2. Connect to MySQL Server and create database
    echo "[2/4] Connecting to MySQL Server ($mysqlHost:$mysqlPort)...\n";
    $mysqlServer = new PDO("mysql:host=$mysqlHost;port=$mysqlPort;charset=utf8mb4", $mysqlUser, $mysqlPass);
    $mysqlServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo " -> Creating database `$mysqlDb` (if not exists)...\n";
    $mysqlServer->exec("CREATE DATABASE IF NOT EXISTS `$mysqlDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    
    // Connect directly to target database
    $mysql = new PDO("mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try {
        $mysql->exec("SET GLOBAL max_allowed_packet = 1073741824;");
    } catch (Exception $e) {}
    echo " -> Connected to MySQL `$mysqlDb` successfully.\n\n";

    // 3. Create tables in MySQL
    echo "[3/4] Ensuring table schemas in MySQL...\n";

    $schemas = [
        "page_configs" => "CREATE TABLE IF NOT EXISTS page_configs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL UNIQUE,
            config_data LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) UNIQUE,
            role VARCHAR(50) DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "candles" => "CREATE TABLE IF NOT EXISTS candles (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "ticks" => "CREATE TABLE IF NOT EXISTS ticks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            symbol VARCHAR(50) NOT NULL,
            time BIGINT NOT NULL,
            price DOUBLE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_tick (symbol, time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "derivTradeHistory" => "CREATE TABLE IF NOT EXISTS derivTradeHistory (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "candle_analysis" => "CREATE TABLE IF NOT EXISTS candle_analysis (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "vpsTradeData" => "CREATE TABLE IF NOT EXISTS vpsTradeData (
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
            scheduleTradeNo INT DEFAULT NULL,
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
            KEY idx_gcp_buyId (buyId)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เก็บข้อมูลที่ได้ จาก การเทรดแต่ละครั้ง ที่บันทึกบน folder tradedata บน vps ของแต่ละวัน';",

        "case_codes" => "CREATE TABLE IF NOT EXISTS case_codes (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
    ];

    foreach ($schemas as $tableName => $sql) {
        $mysql->exec($sql);
        echo " -> Table `$tableName` ready.\n";
    }
    echo "\n";

    // 4. Migrate Data
    echo "[4/4] Migrating data from SQLite to MySQL...\n";

    $mysql->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $mysql->exec("SET UNIQUE_CHECKS = 0;");
    $mysql->exec("SET AUTOCOMMIT = 0;");

    $tables = array_keys($schemas);
    $batchSizes = [
        "page_configs" => 50,
        "candle_analysis" => 150,
        "vpsTradeData" => 500,
        "derivTradeHistory" => 1000,
        "candles" => 2500,
        "ticks" => 5000,
        "users" => 100,
        "case_codes" => 100
    ];

    foreach ($tables as $tableName) {
        $sourceSqliteTable = $tableName;
        if ($tableName === 'vpsTradeData') {
            $hasVps = (int)$sqlite->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='vpsTradeData'")->fetchColumn();
            if (!$hasVps) {
                $sourceSqliteTable = 'gcpTradeData';
            }
        }
        if ($tableName === 'derivTradeHistory') {
            $hasDeriv = (int)$sqlite->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='derivTradeHistory'")->fetchColumn();
            if (!$hasDeriv) {
                $sourceSqliteTable = 'trade_history';
            }
        }

        $sqliteCount = (int)$sqlite->query("SELECT COUNT(*) FROM \"$sourceSqliteTable\"")->fetchColumn();
        
        // Check MySQL row count
        $mysqlCount = 0;
        try {
            $mysqlCount = (int)$mysql->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
        } catch (Exception $e) {}

        echo " --------------------------------------------------\n";
        echo " Table `$tableName`: SQLite = $sqliteCount rows | MySQL = $mysqlCount rows\n";

        if ($sqliteCount === 0) {
            echo "  -> 0 rows in SQLite, skipped.\n";
            continue;
        }

        if ($sqliteCount === $mysqlCount) {
            echo "  -> Already fully migrated ($mysqlCount / $sqliteCount rows). Skipped!\n";
            continue;
        }

        // If partially migrated or needs re-import, truncate and import
        $mysql->exec("TRUNCATE TABLE `$tableName`;");
        $batchSize = $batchSizes[$tableName] ?? 1000;
        echo "  -> Importing $sqliteCount rows (batch size: $batchSize)...\n";

        $selectStmt = $sqlite->query("SELECT * FROM \"$sourceSqliteTable\"");
        $batch = [];
        $migrated = 0;
        $cols = null;

        while ($row = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($cols === null) {
                $cols = array_keys($row);
            }
            $batch[] = $row;

            if (count($batch) >= $batchSize) {
                insertBatch($mysql, $tableName, $cols, $batch);
                $migrated += count($batch);
                $batch = [];
                $percent = round(($migrated / $sqliteCount) * 100, 1);
                echo "  -> Migrated $migrated / $sqliteCount ($percent%)\r";
            }
        }

        if (!empty($batch)) {
            insertBatch($mysql, $tableName, $cols, $batch);
            $migrated += count($batch);
        }

        $mysql->commit();
        echo "  -> Complete! Migrated $migrated / $sqliteCount rows into `$tableName`.\n";
    }

    $mysql->exec("SET FOREIGN_KEY_CHECKS = 1;");
    $mysql->exec("SET UNIQUE_CHECKS = 1;");
    $mysql->exec("SET AUTOCOMMIT = 1;");

    echo "\n====================================================\n";
    echo "  ALL TABLES AND DATA MIGRATED SUCCESSFULLY!\n";
    echo "====================================================\n";

} catch (Exception $e) {
    if (isset($mysql)) {
        try { $mysql->rollBack(); } catch (Exception $ignored) {}
    }
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

function insertBatch($pdo, $tableName, $cols, $rows) {
    if (empty($rows)) return;

    $colNames = implode(", ", array_map(function($c) { return "`$c`"; }, $cols));
    $placeholders = "(" . implode(", ", array_fill(0, count($cols), "?")) . ")";
    $allPlaceholders = implode(", ", array_fill(0, count($rows), $placeholders));

    $sql = "INSERT INTO `$tableName` ($colNames) VALUES $allPlaceholders";
    $stmt = $pdo->prepare($sql);

    $values = [];
    foreach ($rows as $row) {
        foreach ($cols as $c) {
            $values[] = $row[$c];
        }
    }

    $stmt->execute($values);
}
