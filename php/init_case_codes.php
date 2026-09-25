<?php
/**
 * init_case_codes.php
 * สร้างตาราง MySQL 'case_codes' และบันทึกข้อมูลรหัสแท่งเทียน 27 รูปแบบ (20 Core + 7 Extended) จาก caseCodesTable.html
 */

require_once __DIR__ . '/db.php';

try {
    $db = getDbConnection();

    // 1. สร้างตาราง case_codes
    $sql = "CREATE TABLE IF NOT EXISTS case_codes (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);

    // 2. ข้อมูล 27 Case Codes ทั้งหมดจาก caseCodesTable.html
    $caseData = [
        // Core 20 Cases (1-20)
        [
            'code_no' => 1,
            'case_code' => 'UP-CONFIRM',
            'case_desc' => 'CONFIRMED_UP',
            'category' => 'CORE',
            'group_name' => 'Up',
            'trend' => 'UpTrend',
            'description' => 'ทำ Higher High ปิดสูงกว่าหรือเท่าแท่งก่อนหน้า และปิดในโซนบนของแท่งตัวเอง ยืนยันแรงซื้อยังควบคุมตลาด'
        ],
        [
            'code_no' => 2,
            'case_code' => 'DN-CONFIRM',
            'case_desc' => 'CONFIRMED_DOWN',
            'category' => 'CORE',
            'group_name' => 'Down',
            'trend' => 'DownTrend',
            'description' => 'ทำ Lower Low ปิดต่ำกว่าหรือเท่าแท่งก่อนหน้า และปิดในโซนล่างของแท่งตัวเอง ยืนยันแรงขายยังควบคุมตลาด'
        ],
        [
            'code_no' => 3,
            'case_code' => 'SPK-CONTINUE-UP',
            'case_desc' => 'SPIKE_CONTINUATION_UP',
            'category' => 'CORE',
            'group_name' => 'Spike',
            'trend' => 'UpTrend',
            'description' => 'แท่งนี้เป็น Spike ที่พุ่งขึ้นแรงและปิดยืนในโซนบนของแท่ง ไม่โดนกดกลับ มีโอกาสเป็นแรงส่งจริง (Breakout Momentum) ไม่ใช่แค่หลอก'
        ],
        [
            'code_no' => 4,
            'case_code' => 'SPK-CONTINUE-DN',
            'case_desc' => 'SPIKE_CONTINUATION_DOWN',
            'category' => 'CORE',
            'group_name' => 'Spike',
            'trend' => 'DownTrend',
            'description' => 'แท่งนี้เป็น Spike ที่ทิ่มลงแรงและปิดยืนในโซนล่างของแท่ง ไม่โดนดันกลับ มีโอกาสเป็นแรงส่งจริง (Breakout Momentum) ฝั่งขาย'
        ],
        [
            'code_no' => 5,
            'case_code' => 'SPK-BULLTRAP',
            'case_desc' => 'SPIKE_BULL_TRAP',
            'category' => 'CORE',
            'group_name' => 'Spike',
            'trend' => 'Rejected',
            'description' => 'แท่งนี้เป็น Spike ที่พุ่งขึ้นแรงทำ Higher High แต่ถูกกดลงมาปิดใกล้จุดต่ำสุดของแท่ง คล้ายการล่าสภาพคล่องฝั่งซื้อ (Bull Trap) เสี่ยงกลับตัวลงสูง'
        ],
        [
            'code_no' => 6,
            'case_code' => 'SPK-BEARTRAP',
            'case_desc' => 'SPIKE_BEAR_TRAP',
            'category' => 'CORE',
            'group_name' => 'Spike',
            'trend' => 'Rejected',
            'description' => 'แท่งนี้เป็น Spike ที่ทิ่มลงแรงทำ Lower Low แต่ถูกดันกลับขึ้นมาปิดใกล้จุดสูงสุดของแท่ง คล้ายการล่าสภาพคล่องฝั่งขาย (Bear Trap) เสี่ยงกลับตัวขึ้นสูง'
        ],
        [
            'code_no' => 7,
            'case_code' => 'SPK-NODIRECTION',
            'case_desc' => 'SPIKE_NO_CLEAR_DIRECTION',
            'category' => 'CORE',
            'group_name' => 'Spike',
            'trend' => 'Sideways',
            'description' => 'แท่งนี้เป็น Spike (range กว้างเกิน ATR) แต่ไม่ได้ทำ Higher High / Lower Low ชัดเจน ตลาดผันผวนสูงไร้ทิศทาง'
        ],
        [
            'code_no' => 8,
            'case_code' => 'SPK-HESITANT-UP',
            'case_desc' => 'SPIKE_HESITANT_UP',
            'category' => 'CORE',
            'group_name' => 'Spike',
            'trend' => 'Sideways',
            'description' => 'แท่งนี้เป็น Spike ฝั่ง Higher High แต่ปิดในโซนกลางของแท่ง มีแรงซื้อแต่ยังไม่ยืนยันชัดเจน ตลาดเริ่มลังเล'
        ],
        [
            'code_no' => 9,
            'case_code' => 'EG-BULLISH',
            'case_desc' => 'BULLISH_ENGULFING',
            'category' => 'CORE',
            'group_name' => 'Engulfing',
            'trend' => 'UpTrend',
            'description' => 'Outside Bar ที่กลืนกรอบแท่งก่อนหน้า (high สูงกว่า + low ต่ำกว่า) และปิดในโซนบน สัญญาณกลับตัวขึ้นหรือแรงซื้อเข้ามาชัดเจน'
        ],
        [
            'code_no' => 10,
            'case_code' => 'EG-BEARISH',
            'case_desc' => 'BEARISH_ENGULFING',
            'category' => 'CORE',
            'group_name' => 'Engulfing',
            'trend' => 'DownTrend',
            'description' => 'Outside Bar ที่กลืนกรอบแท่งก่อนหน้า (high สูงกว่า + low ต่ำกว่า) และปิดในโซนล่าง สัญญาณกลับตัวลงหรือแรงขายเข้ามาชัดเจน'
        ],
        [
            'code_no' => 11,
            'case_code' => 'RJ-BULLTRAP-STRONG',
            'case_desc' => 'STRONG_BULL_TRAP',
            'category' => 'CORE',
            'group_name' => 'Rejected',
            'trend' => 'Rejected',
            'description' => 'ทำ Higher High แต่ถูกแรงขายกดลงมาปิดใกล้จุดต่ำสุดของแท่ง และปิดต่ำกว่าแท่งก่อนหน้าด้วย สัญญาณกลับตัวลงที่ชัดเจน (Bull Trap)'
        ],
        [
            'code_no' => 12,
            'case_code' => 'RJ-BEARTRAP-STRONG',
            'case_desc' => 'STRONG_BEAR_TRAP',
            'category' => 'CORE',
            'group_name' => 'Rejected',
            'trend' => 'Rejected',
            'description' => 'ทำ Lower Low แต่ถูกแรงซื้อดันขึ้นมาปิดใกล้จุดสูงสุดของแท่ง และปิดสูงกว่าแท่งก่อนหน้าด้วย สัญญาณกลับตัวขึ้นที่ชัดเจน (Bear Trap)'
        ],
        [
            'code_no' => 13,
            'case_code' => 'RJ-FOLLOWFAIL-UP',
            'case_desc' => 'FAILED_FOLLOWTHROUGH_UP',
            'category' => 'CORE',
            'group_name' => 'Rejected',
            'trend' => 'Rejected',
            'description' => 'ทำ Higher High แต่ปิดต่ำกว่าแท่งก่อนหน้า แสดงว่าโมเมนตัมขาขึ้นเริ่มแผ่ว ควรจับตาแท่งถัดไป'
        ],
        [
            'code_no' => 14,
            'case_code' => 'RJ-FOLLOWFAIL-DN',
            'case_desc' => 'FAILED_FOLLOWTHROUGH_DOWN',
            'category' => 'CORE',
            'group_name' => 'Rejected',
            'trend' => 'Rejected',
            'description' => 'ทำ Lower Low แต่ปิดสูงกว่าแท่งก่อนหน้า แสดงว่าโมเมนตัมขาลงเริ่มแผ่ว ควรจับตาแท่งถัดไป'
        ],
        [
            'code_no' => 15,
            'case_code' => 'RJ-UPWICK-WEAK',
            'case_desc' => 'WEAK_UPPER_WICK',
            'category' => 'CORE',
            'group_name' => 'Rejected',
            'trend' => 'Rejected',
            'description' => 'ทำ Higher High และยังปิดสูงกว่าแท่งก่อนหน้า แต่มีไส้บนยาวแสดงว่าเจอแรงขายกดในช่วงท้ายแท่ง แรงซื้อเริ่มอ่อนกำลัง'
        ],
        [
            'code_no' => 16,
            'case_code' => 'RJ-LOWWICK-WEAK',
            'case_desc' => 'WEAK_LOWER_WICK',
            'category' => 'CORE',
            'group_name' => 'Rejected',
            'trend' => 'Rejected',
            'description' => 'ทำ Lower Low และยังปิดต่ำกว่าแท่งก่อนหน้า แต่มีไส้ล่างยาวแสดงว่าเจอแรงซื้อดันกลับในช่วงท้ายแท่ง แรงขายเริ่มอ่อนกำลัง'
        ],
        [
            'code_no' => 17,
            'case_code' => 'SD-INSIDEBAR',
            'case_desc' => 'INSIDE_BAR',
            'category' => 'CORE',
            'group_name' => 'Sideways',
            'trend' => 'Sideways',
            'description' => 'แท่งปัจจุบันมี high/low อยู่ภายในกรอบของแท่งก่อนหน้าทั้งหมด (Inside Bar) ตลาดกำลังหดตัว/พักตัว รอการ breakout'
        ],
        [
            'code_no' => 18,
            'case_code' => 'SD-MIXEDSIGNAL',
            'case_desc' => 'MIXED_SIGNAL',
            'category' => 'CORE',
            'group_name' => 'Sideways',
            'trend' => 'Sideways',
            'description' => 'High สูงกว่าแท่งก่อนหน้าแค่ 1 ใน 2 แท่ง (สัญญาณขัดแย้งกันเอง) ประกอบกับ body เล็ก ตลาดสับสน ยังไม่มีทิศทางชัดเจน'
        ],
        [
            'code_no' => 19,
            'case_code' => 'SD-MIXEDSIGNAL-DN',
            'case_desc' => 'MIXED_SIGNAL_DOWN',
            'category' => 'CORE',
            'group_name' => 'Sideways',
            'trend' => 'Sideways',
            'description' => 'Low ต่ำกว่าแท่งก่อนหน้าแค่ 1 ใน 2 แท่ง (สัญญาณขัดแย้งกันเอง) ประกอบกับ body เล็ก ตลาดสับสนฝั่งขาลง'
        ],
        [
            'code_no' => 20,
            'case_code' => 'SYS-NODATA',
            'case_desc' => 'INSUFFICIENT_DATA',
            'category' => 'CORE',
            'group_name' => 'System',
            'trend' => 'null',
            'description' => 'ข้อมูลไม่พอสำหรับเปรียบเทียบ ต้องมีอย่างน้อย 3 แท่ง (ปัจจุบัน + ก่อนหน้า 2 แท่ง)'
        ],

        // Extended 7 Cases (21-27)
        [
            'code_no' => 21,
            'case_code' => 'SD-DOJI',
            'case_desc' => 'DOJI_INDECISION',
            'category' => 'EXTENDED',
            'group_name' => 'Sideways',
            'trend' => 'Sideways',
            'description' => 'Body ของแท่งเล็กมากเมื่อเทียบกับ range ทั้งหมด (เปิด-ปิดใกล้กัน) แรงซื้อแรงขายสูสีกัน ไม่มีฝ่ายใดชนะชัดเจน'
        ],
        [
            'code_no' => 22,
            'case_code' => 'SD-FLATRESISTANCE',
            'case_desc' => 'FLAT_RESISTANCE_TEST',
            'category' => 'EXTENDED',
            'group_name' => 'Sideways',
            'trend' => 'Sideways',
            'description' => 'High ของแท่งใกล้เคียงกับ high ของแท่งก่อนหน้ามาก ชนแนวต้านเดิมซ้ำ (ทดสอบแนวต้าน) ยังไม่มีแรงมากพอจะทะลุ'
        ],
        [
            'code_no' => 23,
            'case_code' => 'SD-FLATSUPPORT',
            'case_desc' => 'FLAT_SUPPORT_TEST',
            'category' => 'EXTENDED',
            'group_name' => 'Sideways',
            'trend' => 'Sideways',
            'description' => 'Low ของแท่งใกล้เคียงกับ low ของแท่งก่อนหน้ามาก ชนแนวรับเดิมซ้ำ (ทดสอบแนวรับ/Double Bottom) ยังไม่มีแรงพอจะหลุด'
        ],
        [
            'code_no' => 24,
            'case_code' => 'SD-NOSTRUCTURE',
            'case_desc' => 'NO_CLEAR_STRUCTURE',
            'category' => 'EXTENDED',
            'group_name' => 'Sideways',
            'trend' => 'Sideways',
            'description' => 'ไม่ได้ทำ Higher High หรือ Lower Low ชัดเจนเมื่อเทียบกับ 2 แท่งก่อนหน้า ตลาดยังไม่มีโครงสร้างที่ชัดเจนพอจะสรุปทิศทาง'
        ],
        [
            'code_no' => 25,
            'case_code' => 'EG-INDECISION',
            'case_desc' => 'ENGULFING_INDECISION',
            'category' => 'EXTENDED',
            'group_name' => 'Engulfing',
            'trend' => 'Sideways',
            'description' => 'Outside Bar ที่กลืนกรอบแท่งก่อนหน้า แต่ปิดใกล้กลางแท่ง แรงซื้อแรงขายสู้กันไม่มีฝ่ายใดชนะ ผันผวนสูงแต่ไร้ทิศทาง'
        ],
        [
            'code_no' => 26,
            'case_code' => 'SPK-HESITANT-DN',
            'case_desc' => 'SPIKE_HESITANT_DOWN',
            'category' => 'EXTENDED',
            'group_name' => 'Spike',
            'trend' => 'Sideways',
            'description' => 'แท่งนี้เป็น Spike ฝั่ง Lower Low แต่ปิดในโซนกลางของแท่ง หรือปิดไม่ต่ำกว่าแท่งก่อนหน้า มีแรงขายแต่ยังไม่ยืนยันชัดเจน'
        ],
        [
            'code_no' => 27,
            'case_code' => 'SPK-WHIPSAW',
            'case_desc' => 'SPIKE_WHIPSAW',
            'category' => 'EXTENDED',
            'group_name' => 'Spike',
            'trend' => 'Sideways',
            'description' => 'แท่งนี้เป็น Spike ที่ range กว้างผิดปกติ (Outside Bar) แต่ปิดใกล้กลางแท่ง ผันผวนรุนแรงสับขาหลอก อาจเป็นจุดกลับตัวหรือพักตัว'
        ]
    ];

    // 3. ทำการ INSERT หรือ REPLACE ข้อมูล
    $stmt = $db->prepare("INSERT INTO case_codes (
        code_no, case_code, case_desc, category, group_name, trend, description, updated_at
    ) VALUES (
        :code_no, :case_code, :case_desc, :category, :group_name, :trend, :description, CURRENT_TIMESTAMP
    ) ON DUPLICATE KEY UPDATE
        case_code = VALUES(case_code),
        case_desc = VALUES(case_desc),
        category = VALUES(category),
        group_name = VALUES(group_name),
        trend = VALUES(trend),
        description = VALUES(description),
        updated_at = CURRENT_TIMESTAMP;");

    $db->beginTransaction();
    $insertedCount = 0;

    foreach ($caseData as $row) {
        $stmt->execute([
            ':code_no'     => $row['code_no'],
            ':case_code'   => $row['case_code'],
            ':case_desc'   => $row['case_desc'],
            ':category'    => $row['category'],
            ':group_name'  => $row['group_name'],
            ':trend'       => $row['trend'],
            ':description' => $row['description']
        ]);
        $insertedCount++;
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "บันทึกข้อมูล Case Codes จำนวน $insertedCount รายการลงตาราง 'case_codes' สำเร็จเรียบร้อย",
        'total_cases' => $insertedCount
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
