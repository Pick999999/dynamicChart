<?php
/**
 * API สำหรับรับและบันทึกข้อมูล Candle Analysis
 * File: save-candle-analysis.php
 * Location: https://thepapers.com/api/save-candle-analysis.php
 */

// ตั้งค่า Headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *'); // ปรับตาม domain ที่ต้องการอนุญาต
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// จัดการ OPTIONS request (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ตรวจสอบ Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed. ใช้ POST เท่านั้น'
    ]);
    exit();
} 






// การตั้งค่าฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_NAME', 'thepaper_lab');
define('DB_USER', 'thepaper_lab');
define('DB_PASS', 'maithong');
define('DB_CHARSET', 'utf8mb4');

/**
 * เชื่อมต่อฐานข้อมูล
 */
function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database Connection Failed: ' . $e->getMessage());
    }
}

/**
 * บันทึกข้อมูล Candle Analysis
 */
function saveCandleAnalysis($pdo, $analysisData) {

	//v_candle_analysis_summary
    $sql = "INSERT INTO candle_body_analysis (
        symbol,
        time_candle,
        thai_time_display,
        pip,
        color,
        previous_color,
        candle_size,
        upper_wick,
        body_size,
        body_size_pip,
        lower_wick,
        upper_wick_percent,
        body_size_percent,
        lower_wick_percent,
        total_percent,
        upper_wick_code,
        body_code,
        lower_wick_code,
        previous_body_pip,
        current_body_pip,
        pip_difference,
        comparison_status,
        percent_difference
    ) VALUES (
        :symbol,
        :time_candle,
        :thai_time_display,
        :pip,
        :color,
        :previous_color,
        :candle_size,
        :upper_wick,
        :body_size,
        :body_size_pip,
        :lower_wick,
        :upper_wick_percent,
        :body_size_percent,
        :lower_wick_percent,
        :total_percent,
        :upper_wick_code,
        :body_code,
        :lower_wick_code,
        :previous_body_pip,
        :current_body_pip,
        :pip_difference,
        :comparison_status,
        :percent_difference
    ) ON DUPLICATE KEY UPDATE
        pip = VALUES(pip),
        color = VALUES(color),
        previous_color = VALUES(previous_color),
        candle_size = VALUES(candle_size),
        upper_wick = VALUES(upper_wick),
        body_size = VALUES(body_size),
        body_size_pip = VALUES(body_size_pip),
        lower_wick = VALUES(lower_wick),
        upper_wick_percent = VALUES(upper_wick_percent),
        body_size_percent = VALUES(body_size_percent),
        lower_wick_percent = VALUES(lower_wick_percent),
        total_percent = VALUES(total_percent),
        upper_wick_code = VALUES(upper_wick_code),
        body_code = VALUES(body_code),
        lower_wick_code = VALUES(lower_wick_code),
        previous_body_pip = VALUES(previous_body_pip),
        current_body_pip = VALUES(current_body_pip),
        pip_difference = VALUES(pip_difference),
        comparison_status = VALUES(comparison_status),
        percent_difference = VALUES(percent_difference),
        updated_at = CURRENT_TIMESTAMP";
    
    $stmt = $pdo->prepare($sql);
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($analysisData as $index => $data) {
        try {
            // เตรียมข้อมูลสำหรับ BodyComparison
            $bodyComparison = isset($data['BodyComparison']) ? $data['BodyComparison'] : null;
            
            $params = [
                ':symbol' => $data['symbol'],
                ':time_candle' => $data['timeCandle'],
                ':thai_time_display' => $data['thaiTimeDisplay'],
                ':pip' => $data['pip'],
                ':color' => $data['Color'],
                ':previous_color' => $data['PreviousColor'],
                ':candle_size' => $data['CandleSize'],
                ':upper_wick' => $data['UpperWick'],
                ':body_size' => $data['BodySize'],
                ':body_size_pip' => $data['BodySizePip'],
                ':lower_wick' => $data['LowerWick'],
                ':upper_wick_percent' => $data['UpperWickPercent'],
                ':body_size_percent' => $data['BodySizePercent'],
                ':lower_wick_percent' => $data['LowerWickPercent'],
                ':total_percent' => $data['TotalPercent'],
                ':upper_wick_code' => $data['UpperWickCode'],
                ':body_code' => $data['BodyCode'],
                ':lower_wick_code' => $data['LowerWickCode'],
                ':previous_body_pip' => $bodyComparison ? $bodyComparison['previousBodyPip'] : null,
                ':current_body_pip' => $bodyComparison ? $bodyComparison['currentBodyPip'] : $data['BodySizePip'],
                ':pip_difference' => $bodyComparison ? $bodyComparison['pipDifference'] : null,
                ':comparison_status' => $bodyComparison ? $bodyComparison['status'] : null,
                ':percent_difference' => $bodyComparison ? $bodyComparison['percentDifference'] : null
            ];
            
            $stmt->execute($params);
            $successCount++;
            
        } catch (PDOException $e) {
            $errorCount++;
            $errors[] = [
                'index' => $index,
                'symbol' => $data['symbol'] ?? 'unknown',
                'time' => $data['timeCandle'] ?? 'unknown',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'total' => count($analysisData),
        'success' => $successCount,
        'failed' => $errorCount,
        'errors' => $errors
    ];
}

/**
 * Main Process
 */
try {
    // อ่านข้อมูล JSON จาก request body
    $jsonInput = file_get_contents('php://input');
    
    if (empty($jsonInput)) {
        throw new Exception('ไม่มีข้อมูลที่ส่งมา');
    }
    
    // แปลง JSON เป็น Array
    $requestData = json_decode($jsonInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Format ไม่ถูกต้อง: ' . json_last_error_msg());
    }
    
    // ตรวจสอบโครงสร้างข้อมูล
    if (!isset($requestData['data']) || !is_array($requestData['data'])) {
        throw new Exception('โครงสร้างข้อมูลไม่ถูกต้อง ต้องมี key "data" เป็น array');
    }
    
    $analysisData = $requestData['data'];
    
    if (empty($analysisData)) {
        throw new Exception('ไม่มีข้อมูลที่จะบันทึก');
    }
    
    // เชื่อมต่อฐานข้อมูล
    $pdo = getDatabaseConnection();
    
    // เริ่ม Transaction
    $pdo->beginTransaction();
    
    try {
        // บันทึกข้อมูล
        $result = saveCandleAnalysis($pdo, $analysisData);
        
        // Commit Transaction
        $pdo->commit();
        
        // ส่ง Response สำเร็จ
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'บันทึกข้อมูลสำเร็จ',
            'data' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // Rollback Transaction
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // ส่ง Response ผิดพลาด
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>