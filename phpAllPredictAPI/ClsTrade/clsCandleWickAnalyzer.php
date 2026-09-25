<?php

class CandleAnalyzer {
    
    /**
     * วิเคราะห์แท่งเทียนและสร้างรหัส
     * @param array $candleData ข้อมูลแท่งเทียน ['time', 'open', 'high', 'low', 'close']
     * @return array ผลการวิเคราะห์
     */
    public function analyzeCandleCode($candleData) {
        $open = floatval($candleData['open']);
        $high = floatval($candleData['high']);
        $low = floatval($candleData['low']);
        $close = floatval($candleData['close']);
        
        // 1. คำนวณขนาดแท่งเทียนทั้งหมด (high - low)
        $totalRange = $high - $low;
        
        // 2. กำหนดสีของแท่งเทียน
        $color = ($close > $open) ? 'G' : (($close < $open) ? 'R' : 'D'); // Green, Red, Doji
        
        // 3. คำนวณขนาดของแต่ละส่วน
        $bodyTop = max($open, $close);
        $bodyBottom = min($open, $close);
        
        $upperWickSize = $high - $bodyTop;
        $bodySize = $bodyTop - $bodyBottom;
        $lowerWickSize = $bodyBottom - $low;
        
        // 4. แปลงเป็นสัดส่วน 10 ส่วน
        $result = $this->convertToTenParts($upperWickSize, $bodySize, $lowerWickSize, $totalRange);
        
        // 5. สร้างรหัส
        $code = $result['upperWick'] . '-' . $result['body'] . '-' . $result['lowerWick'] . '-' . $color;
        
        return [
            'originalData' => $candleData,
            'calculations' => [
                'totalRange' => $totalRange,
                'upperWickSize' => $upperWickSize,
                'bodySize' => $bodySize,
                'lowerWickSize' => $lowerWickSize,
                'color' => $color
            ],
            'tenParts' => $result,
            'code' => $code,
            'verification' => [
                'sum' => $result['upperWick'] + $result['body'] + $result['lowerWick'],
                'isValid' => ($result['upperWick'] + $result['body'] + $result['lowerWick']) === 10
            ]
        ];
    }
    
    /**
     * แปลงขนาดต่างๆ เป็นสัดส่วน 10 ส่วน
     * @param float $upperWickSize
     * @param float $bodySize  
     * @param float $lowerWickSize
     * @param float $totalRange
     * @return array
     */
    private function convertToTenParts($upperWickSize, $bodySize, $lowerWickSize, $totalRange) {
        if ($totalRange == 0) {
            // กรณีที่ high = low (แท่งเทียนแบน)
            return ['upperWick' => 0, 'body' => 10, 'lowerWick' => 0];
        }
        
        // คำนวณสัดส่วนเบื้องต้น
        $upperWickRatio = $upperWickSize / $totalRange;
        $bodyRatio = $bodySize / $totalRange;
        $lowerWickRatio = $lowerWickSize / $totalRange;
        
        // แปลงเป็นค่า 10 ส่วน (ปัดขึ้น/ลง)
        $upperWick = round($upperWickRatio * 10);
        $body = round($bodyRatio * 10);
        $lowerWick = round($lowerWickRatio * 10);
        
        // ปรับให้ผลรวมเป็น 10 พอดี
        $sum = $upperWick + $body + $lowerWick;
        
        if ($sum !== 10) {
            // หาส่วนที่มีค่ามากที่สุดเพื่อปรับ
            $parts = [
                'upperWick' => ['value' => $upperWick, 'ratio' => $upperWickRatio],
                'body' => ['value' => $body, 'ratio' => $bodyRatio],
                'lowerWick' => ['value' => $lowerWick, 'ratio' => $lowerWickRatio]
            ];
            
            // เรียงตามสัดส่วนจากมากไปน้อย
            uasort($parts, function($a, $b) {
                return $b['ratio'] <=> $a['ratio'];
            });
            
            $difference = 10 - $sum;
            
            // ปรับส่วนที่มีสัดส่วนมากที่สุด
            $maxPart = array_key_first($parts);
            
            if ($maxPart === 'upperWick') {
                $upperWick += $difference;
            } elseif ($maxPart === 'body') {
                $body += $difference;
            } else {
                $lowerWick += $difference;
            }
            
            // ตรวจสอบไม่ให้มีค่าติดลบ
            $upperWick = max(0, $upperWick);
            $body = max(0, $body);
            $lowerWick = max(0, $lowerWick);
        }
        
        return [
            'upperWick' => intval($upperWick),
            'body' => intval($body),
            'lowerWick' => intval($lowerWick)
        ];
    }
    
    /**
     * แสดงผลในรูปแบบที่อ่านง่าย
     * @param array $result
     */
    public function displayResult($result) {
        echo "=== Candle Analysis Result ===<br><br>";
        
        echo "📊 Original Data:<br>";
        echo "Time: " . date('Y-m-d H:i:s', $result['originalData']['time']) . "<br>";
        echo "Open: " . $result['originalData']['open'] . "<br>";
        echo "High: " . $result['originalData']['high'] . "<br>";
        echo "Low: " . $result['originalData']['low'] . "<br>";
        echo "Close: " . $result['originalData']['close'] . "<br><br>";
        
        echo "🧮 Calculations:<br>";
        echo "Total Range: " . number_format($result['calculations']['totalRange'], 4) . "<br>";
        echo "Upper Wick Size: " . number_format($result['calculations']['upperWickSize'], 4) . "<br>";
        echo "Body Size: " . number_format($result['calculations']['bodySize'], 4) . "<br>";
        echo "Lower Wick Size: " . number_format($result['calculations']['lowerWickSize'], 4) . "<br>";
        echo "Color: " . $this->getColorName($result['calculations']['color']) . "<br><br>";
        
        echo "📏 10-Parts Division:<br>";
        echo "Upper Wick: " . $result['tenParts']['upperWick'] . " parts<br>";
        echo "Body: " . $result['tenParts']['body'] . " parts<br>";
        echo "Lower Wick: " . $result['tenParts']['lowerWick'] . " parts<br>";
        echo "Total: " . $result['verification']['sum'] . " parts<br><br>";
        
        echo "🎯 Generated Code: " . $result['code'] . "<br><br>";
        
        echo "✅ Verification: " . ($result['verification']['isValid'] ? "VALID" : "INVALID") . "<br>";
        
        // Visual representation
        echo "<br>📈 Visual Representation:<br>";
        $this->drawCandle($result['tenParts'], $result['calculations']['color']);
    }
    
    /**
     * วาดแท่งเทียนแบบ HTML/CSS
     */
    private function drawCandle($parts, $color) {
        $upperHeight = ($parts['upperWick'] * 10); // แปลงเป็น %
        $bodyHeight = ($parts['body'] * 10); // แปลงเป็น %
        $lowerHeight = ($parts['lowerWick'] * 10); // แปลงเป็น %
        
        // กำหนดสี body
        $bodyColor = ($color === 'G') ? '#4CAF50' : (($color === 'R') ? '#f44336' : '#9E9E9E');
        $bodyText = ($color === 'G') ? 'GREEN' : (($color === 'R') ? 'RED' : 'DOJI');
        
        echo "<div style='display: flex; flex-direction: column; width: 60px; height: 300px; border: 2px solid whitesmoke; background: #f9f9f9; margin: 20px;'>";
        
        // Upper Wick
        if ($parts['upperWick'] > 0) {
            echo "<div style='position:relative;margin:0 auto;width:2px;height: {$upperHeight}%; background: linear-gradient(to bottom, #666, #999); border-bottom: 1px solid #333; display: flex; align-items: center; justify-content: center; color: red; font-size: 24px; swriting-mode: vertical-rl;'>{$parts['upperWick']}
			
			</div>";
        }
        
        // Body
        if ($parts['body'] > 0) {
            echo "  <div style='height: {$bodyHeight}%; background: {$bodyColor}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 24px; writing-mode: vertical-rl;'>{$parts['body']}</div>";
        }
        
        // Lower Wick
        if ($parts['lowerWick'] > 0) {
            echo "  <div style='position:relative;margin:0 auto;width:2px;height: {$lowerHeight}%; background: linear-gradient(to top, #666, #999); border-top: 1px solid #333; display: flex; align-items: center; justify-content: center; color: red; font-size: 24px; swriting-mode: vertical-rl;'>{$parts['lowerWick']}</div>";
        }
        
        echo "</div><br>";
        
        // Summary
        echo "<div style='margin: 10px; padding: 10px; background: #f0f0f0; border-radius: 5px;'><br>";
        echo "  <strong>📋 Summary:</strong> {$parts['upperWick']} + {$parts['body']} + {$parts['lowerWick']} = 10 parts<br><br>";
        echo "  <strong>🎨 Color:</strong> <span style='color: {$bodyColor}; font-weight: bold;'>{$bodyText}</span><br>";
        echo "</div><br>";
    }
    
    /**
     * แปลงรหัสสีเป็นชื่อ
     */
    private function getColorName($colorCode) {
        switch ($colorCode) {
            case 'G': return 'Green (Bullish)';
            case 'R': return 'Red (Bearish)';  
            case 'D': return 'Doji (Neutral)';
            default: return 'Unknown';
        }
    }
}

// การใช้งาน
try {
    $analyzer = new CandleAnalyzer();
    
    // ข้อมูลตัวอย่าง
    $candleData = [
        "time" => 1742331600,
        "open" => 208.3267,
        "high" => 208.4270, 
        "low" => 208.2594,
        "close" => 208.3493
    ];
    
//"open":208.3267,"high":208.427,"low":208.2594,"close":208.3493

    echo "🕯️ Candle Code Analyzer<br>";
    echo "=====================<br><br>";
    
    // วิเคราะห์
    $result = $analyzer->analyzeCandleCode($candleData);
    
    // แสดงผล
    $analyzer->displayResult($result);
    
    // ทดสอบกับข้อมูลหลายแท่ง
    echo "<br>" . str_repeat("=", 50) . "<br><br>";
    echo "🔄 Testing Multiple Candles:<br><br>";
    
    $testCases = [
        // Green candle with upper wick
        ["time" => time(), "open" => 100.0, "high" => 105.0, "low" => 99.0, "close" => 103.0],
        // Red candle with lower wick  
        ["time" => time(), "open" => 100.0, "high" => 101.0, "low" => 95.0, "close" => 97.0],
        // Doji
        ["time" => time(), "open" => 100.0, "high" => 102.0, "low" => 98.0, "close" => 100.0],
    ];
    
    foreach ($testCases as $index => $testCandle) {
        echo "Test Case " . ($index + 1) . ":<br>";
        $testResult = $analyzer->analyzeCandleCode($testCandle);
        echo "Code: " . $testResult['code'] . "<br>";
        echo "Valid: " . ($testResult['verification']['isValid'] ? "✅" : "❌") . "<br><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

?>