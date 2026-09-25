<?php

class AdvancedCandlestickAnalyzer {
    private $candles;
    private $ema3 = [];
    private $ema5 = [];
    private $bollingerUpper = [];
    private $bollingerLower = [];
    private $sma20 = [];
    private $rsi = [];
    private $adx = [];
    private $ichimoku = [];
    
    public function __construct(array $candles) {
        $this->candles = $candles;
        $this->calculateAllIndicators();
    }
    
    /**
     * คำนวณค่าตัวชี้วัดทั้งหมด
     */
    private function calculateAllIndicators() {
        $closes = array_column($this->candles, 'close');
        $highs = array_column($this->candles, 'high');
        $lows = array_column($this->candles, 'low');
        
        // คำนวณ EMA
        $this->calculateEMA($closes, 3, $this->ema3);
        $this->calculateEMA($closes, 5, $this->ema5);
        
        // คำนวณ Bollinger Bands
		$defaultBB = 20 ;
		$defaultBB = 10 ;


        $this->calculateBollingerBands($closes, $defaultBB);
        
        // คำนวณ RSI (14 คาบ)
        $this->calculateRSI($closes, 14);
        
        // คำนวณ ADX (14 คาบ)
        $this->calculateADX($highs, $lows, $closes, 7);
        
        // คำนวณ Ichimoku Cloud
        $this->calculateIchimokuCloud($highs, $lows);
    }
    
    /**
     * คำนวณ EMA
     */
    private function calculateEMA(array $data, $period, &$output) {
        $multiplier = 2 / ($period + 1);
        $sma = array_sum(array_slice($data, 0, $period)) / $period;
        $output = [$sma];
        
        for ($i = $period; $i < count($data); $i++) {
            $ema = ($data[$i] - $output[$i - $period]) * $multiplier + $output[$i - $period];
            $output[] = $ema;
        }
		for ($i=0;$i<=$period-1;$i++) {
		   $s[] = '-10000';
		} 
		$output = array_merge($s,$output) ;
        
		//echo 'period=' . $period . ' count=' . count($output) . '<hr>';
    }
    
    /**
     * คำนวณ Bollinger Bands
     */
    private function calculateBollingerBands(array $data, $period) {
        $count = count($data);
        
        for ($i = $period - 1; $i < $count; $i++) {
            $slice = array_slice($data, $i - $period + 1, $period);
            $sma = array_sum($slice) / $period;
            $this->sma20[] = $sma;
            
            $variance = 0;
            foreach ($slice as $value) {
                $variance += pow($value - $sma, 2);
            }
            $stddev = sqrt($variance / $period);
            
            $this->bollingerUpper[] = $sma + ($stddev * 2);
            $this->bollingerLower[] = $sma - ($stddev * 2);
        }
    }
    
    /**
     * คำนวณ RSI
     */
    private function calculateRSI(array $closes, $period = 14) {
        $count = count($closes);
        if ($count < $period + 1) return;
        
        $gains = [];
        $losses = [];
        
        // คำนวณการเปลี่ยนแปลง
        for ($i = 1; $i < $count; $i++) {
            $change = $closes[$i] - $closes[$i - 1];
            $gains[] = max($change, 0);
            $losses[] = abs(min($change, 0));
        }
        
        // ค่าเฉลี่ยแรก
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;
        
        $this->rsi = array_fill(0, $period, 50); // ใส่ค่าเริ่มต้น
        
        // คำนวณ RSI ต่อๆ ไป
        for ($i = $period; $i < count($gains); $i++) {
            $avgGain = (($avgGain * ($period - 1)) + $gains[$i]) / $period;
            $avgLoss = (($avgLoss * ($period - 1)) + $losses[$i]) / $period;
            
            $rs = ($avgLoss == 0) ? INF : ($avgGain / $avgLoss);
            $rsi = 100 - (100 / (1 + $rs));
            
            $this->rsi[] = $rsi;
        }
    }
    
    /**
     * คำนวณ ADX
     */
    private function calculateADX(array $highs, array $lows, array $closes, $period = 14) {
        $count = count($highs);
        if ($count < $period * 2) return;
        
        $plusDM = [];
        $minusDM = [];
        $trueRanges = [];
        
        // คำนวณ +DM, -DM และ True Range
        for ($i = 1; $i < $count; $i++) {
            $upMove = $highs[$i] - $highs[$i - 1];
            $downMove = $lows[$i - 1] - $lows[$i];
            
            $plusDM[] = ($upMove > $downMove && $upMove > 0) ? $upMove : 0;
            $minusDM[] = ($downMove > $upMove && $downMove > 0) ? $downMove : 0;
            
            $trueRanges[] = max(
                $highs[$i] - $lows[$i],
                abs($highs[$i] - $closes[$i - 1]),
                abs($lows[$i] - $closes[$i - 1])
            );
        }
        
        // ค่าแรกเป็น SMA
        $sumPlusDM = array_sum(array_slice($plusDM, 0, $period));
        $sumMinusDM = array_sum(array_slice($minusDM, 0, $period));
        $sumTR = array_sum(array_slice($trueRanges, 0, $period));
        
        $plusDI = ($sumTR == 0) ? 0 : (100 * $sumPlusDM / $sumTR);
        $minusDI = ($sumTR == 0) ? 0 : (100 * $sumMinusDM / $sumTR);
        $dx = (($plusDI + $minusDI) == 0) ? 0 : (100 * abs($plusDI - $minusDI) / ($plusDI + $minusDI));
        
        $adx = array_sum(array_slice(array_fill(0, $period, $dx), 0, $period)) / $period;
        $this->adx = array_fill(0, $period + 13, null); // ใส่ค่า null สำหรับช่วงแรก
        
        // คำนวณ ADX ต่อๆ ไป
        for ($i = $period; $i < count($plusDM); $i++) {
            $sumPlusDM = (($sumPlusDM * ($period - 1)) + $plusDM[$i]) / $period;
            $sumMinusDM = (($sumMinusDM * ($period - 1)) + $minusDM[$i]) / $period;
            $sumTR = (($sumTR * ($period - 1)) + $trueRanges[$i]) / $period;
            
            $plusDI = ($sumTR == 0) ? 0 : (100 * $sumPlusDM / $sumTR);
            $minusDI = ($sumTR == 0) ? 0 : (100 * $sumMinusDM / $sumTR);
            $dx = (($plusDI + $minusDI) == 0) ? 0 : (100 * abs($plusDI - $minusDI) / ($plusDI + $minusDI));
            
            $adx = (($adx * ($period - 1)) + $dx) / $period;
            $this->adx[] = $adx;
        }
    }
    
    /**
     * คำนวณ Ichimoku Cloud
     */
    private function calculateIchimokuCloud(array $highs, array $lows) {
        $count = count($highs);
        $this->ichimoku = array_fill(0, $count, [
            'tenkan' => null,
            'kijun' => null,
            'senkou_a' => null,
            'senkou_b' => null,
            'chikou' => null
        ]);
        
        // คำนวณ Tenkan-sen (Conversion Line)
        for ($i = 8; $i < $count; $i++) {
            $highSlice = array_slice($highs, $i - 8, 9);
            $lowSlice = array_slice($lows, $i - 8, 9);
            $this->ichimoku[$i]['tenkan'] = (max($highSlice) + min($lowSlice)) / 2;
        }
        
        // คำนวณ Kijun-sen (Base Line)
        for ($i = 25; $i < $count; $i++) {
            $highSlice = array_slice($highs, $i - 25, 26);
            $lowSlice = array_slice($lows, $i - 25, 26);
            $this->ichimoku[$i]['kijun'] = (max($highSlice) + min($lowSlice)) / 2;
        }
        
        // คำนวณ Senkou Span A (Leading Span A)
        for ($i = 25; $i < $count; $i++) {
            if ($this->ichimoku[$i]['tenkan'] !== null && $this->ichimoku[$i]['kijun'] !== null) {
                $this->ichimoku[$i]['senkou_a'] = ($this->ichimoku[$i]['tenkan'] + $this->ichimoku[$i]['kijun']) / 2;
            }
        }
        
        // คำนวณ Senkou Span B (Leading Span B)
        for ($i = 51; $i < $count; $i++) {
            $highSlice = array_slice($highs, $i - 51, 52);
            $lowSlice = array_slice($lows, $i - 51, 52);
            $this->ichimoku[$i]['senkou_b'] = (max($highSlice) + min($lowSlice)) / 2;
        }
        
        // คำนวณ Chikou Span (Lagging Span)
        for ($i = 0; $i < $count - 25; $i++) {
            $this->ichimoku[$i]['chikou'] = $this->candles[$i + 25]['close'];
        }
    }
    
    /**
     * วิเคราะห์ลักษณะแท่งเทียน
     */
    public function analyzeCandlestickPatterns() {
        $patterns = [];
        
        for ($i = 1; $i < count($this->candles); $i++) {
            $current = $this->candles[$i];
            $previous = $this->candles[$i - 1];
            
            $pattern = [];
            
            // ตรวจสอบ Doji
			if ($current['high'] != $current['low']) {			
              if (abs($current['open'] - $current['close']) / ($current['high'] -  $current['low']) < 0.1 ) {
                $pattern[] = 'Doji';
              }
			}
            
            // ตรวจสอบ Hammer หรือ Hanging Man
            $bodySize = abs($current['open'] - $current['close']);
            $lowerShadow = $current['close'] > $current['open'] ? $current['open'] - $current['low'] : $current['close'] - $current['low'];
            $upperShadow = $current['high'] - ($current['close'] > $current['open'] ? $current['close'] : $current['open']);
            
            if ($lowerShadow >= 2 * $bodySize && $upperShadow <= $bodySize * 0.5) {
                $pattern[] = $current['close'] > $current['open'] ? 'Hammer' : 'Hanging Man';
            }
            
            // ตรวจสอบ Engulfing
            if ($current['open'] < $previous['close'] && $current['close'] > $previous['open']) {
                $pattern[] = 'Bullish Engulfing';
            } elseif ($current['open'] > $previous['close'] && $current['close'] < $previous['open']) {
                $pattern[] = 'Bearish Engulfing';
            }
            
            $patterns[$i] = $pattern ?: ['Normal'];
        }
        
        return $patterns;
    }
    
    /**
     * วิเคราะห์แนวโน้มและความแข็งแกร่ง
     */
    public function analyzeTrend() {
        $count = count($this->candles);
        //if ($count < 52) return ['trend' => 'Not enough data', 'strength' => 'Weak'];
		if ($count < 2) return ['trend' => 'Not enough data', 'strength' => 'Weak'];
		if ($count- 5 < 0) {
			return ;
		}

        
        $trend = 'Sideways';
        $strength = 'Weak';
		

  
        // ตรวจสอบแนวโน้มจาก EMA
        $ema3Slope = $this->ema3[$count-1] - $this->ema3[$count-2];
        $ema5Slope = $this->ema5[$count-1] - $this->ema5[$count-2];
        
        if ($ema3Slope > 0 && $ema5Slope > 0) {
            $trend = 'Uptrend';
            $strength = $ema3Slope > $ema5Slope ? 'Strong' : 'Moderate';
        } elseif ($ema3Slope < 0 && $ema5Slope < 0) {
            $trend = 'Downtrend';
            $strength = $ema3Slope < $ema5Slope ? 'Strong' : 'Moderate';
        }
        
        // ตรวจสอบ Bollinger Bands
        $currentClose = $this->candles[$count-1]['close'];
        if ($currentClose > $this->bollingerUpper[$count-20]) {
            $trend = 'Strong Uptrend (Overbought)';
        } elseif ($currentClose < $this->bollingerLower[$count-20]) {
            $trend = 'Strong Downtrend (Oversold)';
        }
        
        // ตรวจสอบ ADX
        $adxValue = $this->adx[$count-1] ?? 0;
        if ($adxValue > 25) {
            $strength = 'Strong';
        } elseif ($adxValue > 20) {
            $strength = 'Moderate';
        }
        
        // ตรวจสอบ Ichimoku Cloud
        $ichimoku = $this->ichimoku[$count-1];
        $priceAboveCloud = ($currentClose > $ichimoku['senkou_a'] && $currentClose > $ichimoku['senkou_b']);
        $priceBelowCloud = ($currentClose < $ichimoku['senkou_a'] && $currentClose < $ichimoku['senkou_b']);
        
        if ($priceAboveCloud) {
            $trend = 'Strong Uptrend (Above Cloud)';
        } elseif ($priceBelowCloud) {
            $trend = 'Strong Downtrend (Below Cloud)';
        }
        
        return [
            'trend' => $trend,
            'strength' => $strength,
            'ema3_slope' => $ema3Slope,
            'ema5_slope' => $ema5Slope,
            'adx' => $adxValue,
            'rsi' => $this->rsi[$count-1] ?? null,
            'ichimoku_signal' => $this->getIchimokuSignal($count-1)
        ];
    }
    
    /**
     * ตรวจสอบสัญญาณจาก Ichimoku
     */
    private function getIchimokuSignal($index) {
        if ($index < 26 || !isset($this->ichimoku[$index]['tenkan'])) {
            return 'No Signal';
        }
        
        $current = $this->ichimoku[$index];
        $price = $this->candles[$index]['close'];
        
        // Tenkan/Kijun crossover
        if ($current['tenkan'] > $current['kijun'] && 
            isset($this->ichimoku[$index-1]['tenkan']) && 
            $this->ichimoku[$index-1]['tenkan'] <= $this->ichimoku[$index-1]['kijun']) {
            return 'Bullish Crossover';
        } elseif ($current['tenkan'] < $current['kijun'] && 
                 isset($this->ichimoku[$index-1]['tenkan']) && 
                 $this->ichimoku[$index-1]['tenkan'] >= $this->ichimoku[$index-1]['kijun']) {
            return 'Bearish Crossover';
        }
        
        // Price vs Cloud
        if ($price > $current['senkou_a'] && $price > $current['senkou_b']) {
            return 'Above Cloud';
        } elseif ($price < $current['senkou_a'] && $price < $current['senkou_b']) {
            return 'Below Cloud';
        } else {
            return 'Inside Cloud';
        }
    }
    
    /**
     * ทำนายเปอร์เซ็นต์แท่งถัดไปจะเป็น Green หรือ Red
     */
    public function predictNextCandle() {
        $count = count($this->candles);
        if ($count < 12) return ['green' => 50, 'red' => 50];
        
        $factors = [
            'trend' => 0,
            'recent_candles' => 0,
            'bollinger_position' => 0,
            'ema_cross' => 0,
            'rsi' => 0,
            'adx' => 0,
            'ichimoku' => 0
        ]; 
		$Desc = '';
        
        // 1. วิเคราะห์จากแนวโน้ม
        $trend = $this->analyzeTrend();
        if (strpos($trend['trend'], 'Uptrend') !== false) {
            $factors['trend'] += ($trend['strength'] == 'Strong') ? 1.5 : 1;
        } elseif (strpos($trend['trend'], 'Downtrend') !== false) {
            $factors['trend'] -= ($trend['strength'] == 'Strong') ? 1.5 : 1;
        }
        
        // 2. วิเคราะห์จากแท่งเทียนล่าสุด
        $last3Green = 0;
        for ($i = max(0, $count-3); $i < $count; $i++) {
            if ($this->candles[$i]['close'] > $this->candles[$i]['open']) {
                $last3Green++;
            } else {
                $last3Green--;
            }
        }
        $factors['recent_candles'] = $last3Green / 3;
        
        // 3. วิเคราะห์จาก Bollinger Bands
        $currentClose = $this->candles[$count-1]['close'];
        if ($currentClose < $this->bollingerLower[$count-20]) {
            $factors['bollinger_position'] = 1.2; // มีแนวโน้มจะ反弹 ขึ้น
        } elseif ($currentClose > $this->bollingerUpper[$count-20]) {
            $factors['bollinger_position'] = -1.2; // มีแนวโน้มจะ回落 ลง
        }
        
        // 4. วิเคราะห์จาก EMA crossover
        if ($this->ema3[$count-1] > $this->ema5[$count-1] && $this->ema3[$count-2] <= $this->ema5[$count-2]) {
            $factors['ema_cross'] = 1.2; // EMA3 ตัดขึ้นผ่าน EMA5 - Bullish
        } elseif ($this->ema3[$count-1] < $this->ema5[$count-1] && $this->ema3[$count-2] >= $this->ema5[$count-2]) {
            $factors['ema_cross'] = -1.2; // EMA3 ตัดลงผ่าน EMA5 - Bearish
        }
        
        // 5. วิเคราะห์จาก RSI
        $rsiValue = $this->rsi[$count-1] ?? 50;
        if ($rsiValue < 30) {
            $factors['rsi'] = 1.5; // Oversold - มีแนวโน้มจะ反弹 ขึ้น
        } elseif ($rsiValue > 70) {
            $factors['rsi'] = -1.5; // Overbought - มีแนวโน้มจะ回落 ลง
        } elseif ($rsiValue > 50) {
            $factors['rsi'] = 0.5; // Bullish momentum
        } else {
            $factors['rsi'] = -0.5; // Bearish momentum
        }
        
        // 6. วิเคราะห์จาก ADX
        $adxValue = $this->adx[$count-1] ?? 0;
        if ($adxValue > 25) {
            // ยืนยันแนวโน้มที่แข็งแกร่ง
            if ($factors['trend'] > 0) {
                $factors['adx'] = 1;
            } elseif ($factors['trend'] < 0) {
                $factors['adx'] = -1;
            }
        }
        
        // 7. วิเคราะห์จาก Ichimoku Cloud
        $ichimokuSignal = $this->getIchimokuSignal($count-1);
        switch ($ichimokuSignal) {
            case 'Bullish Crossover':
            case 'Above Cloud':
                $factors['ichimoku'] = 1.5;
                break;
            case 'Bearish Crossover':
            case 'Below Cloud':
                $factors['ichimoku'] = -1.5;
                break;
            case 'Inside Cloud':
                $factors['ichimoku'] = -0.5; // ไม่มีแนวโน้มชัดเจน
                break;
        }
        
        // คำนวณคะแนนรวม (ปรับน้ำหนักตามความสำคัญของแต่ละปัจจัย)
        $totalScore = (
            ($factors['trend'] * 1.5) +
            ($factors['recent_candles'] * 0.8) +
            ($factors['bollinger_position'] * 1.2) +
            ($factors['ema_cross'] * 1.0) +
            ($factors['rsi'] * 1.3) +
            ($factors['adx'] * 1.1) +
            ($factors['ichimoku'] * 1.4)
        ) * 5;
        
        $greenProbability = 50 + $totalScore;
        $greenProbability = max(15, min(85, $greenProbability)); // จำกัดระหว่าง 15%-85%
        
        return [
            'green' => round($greenProbability, 2),
            'red' => round(100 - $greenProbability, 2),
            'factors' => $factors,
			'totalScore'=>$totalScore,
            'indicators' => [
                'rsi' => $rsiValue,
                'adx' => $adxValue,
                'ichimoku_signal' => $ichimokuSignal
            ]
        ];
    }
    
    /**
     * แนะนำ Indicator เพิ่มเติม
     */
    public function suggestAdditionalIndicators() {
        return [
            'MACD' => 'ช่วยยืนยันแนวโน้มและจุดกลับตัว',
            'Volume Profile' => 'ช่วยระบุพื้นที่สำคัญของราคา',
            'Fibonacci Retracement' => 'ช่วยหาจุด支撑และ阻力',
            'Stochastic Oscillator' => 'ช่วยระบุภาวะ overbought/oversold',
            'Volume Weighted Average Price (VWAP)' => 'เหมาะสำหรับเทรดเดอร์ระยะสั้น'
        ];
    }
    
    /**
     * รับค่าตัวชี้วัดที่คำนวณแล้ว
     */
    public function getIndicators() {
        return [
            'ema3' => $this->ema3,
            'ema5' => $this->ema5,
            'bollinger_upper' => $this->bollingerUpper,
            'bollinger_lower' => $this->bollingerLower,
            'sma20' => $this->sma20,
            'rsi' => $this->rsi,
            'adx' => $this->adx,
            'ichimoku' => $this->ichimoku
        ];
    }
}

function getCandleDataDeepSeek() {


 $st = "";   
 

 $sFileName = 'rawData.json';
 $file = fopen($sFileName,"r");
 while(! feof($file))  {
   $st .= fgets($file) ;
 }
 fclose($file);


$candleDataA = JSON_DECODE($st,true);
//echo 'Len=' . count($candleDataA) . '<br>';
$candleData =  array_slice($candleDataA, 0,59);

return $candleData ;

} // end function 
// ตัวอย่างการใช้งาน
/*
$candles = getCandleDataDeepSeek() ;
$analyzer = new AdvancedCandlestickAnalyzer($candles);

// 1. ตัวชี้วัด
$indicators = $analyzer->getIndicators();

// 2. วิเคราะห์ลักษณะแท่งเทียน
$patterns = $analyzer->analyzeCandlestickPatterns();

// 3. วิเคราะห์แนวโน้ม
$trend = $analyzer->analyzeTrend();

// 4. ทำนายแท่งถัดไป
$prediction = $analyzer->predictNextCandle();

// 5. แนะนำ Indicator เพิ่มเติม
$suggestions = $analyzer->suggestAdditionalIndicators();

///print_r($prediction);
echo '<pre>';
echo 'Prediction<br>';
echo JSON_ENCODE($prediction, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ;
echo '</pre>' ;
return;

$st = JSON_ENCODE($trend,  JSON_PRETTY_PRINT) ;
//echo '<hr>' . $st ;
/*
$array = array(
    "trend" => "Bullish",
    "strength" => "Strong with Low Volatility (Bollinger Squeeze - Potential Breakout)"
);

echo nl2br(json_encode($array, JSON_PRETTY_PRINT));


function HowToPlug() {


	$newUtilPath = '/home/thepaper/domains/thepapers.in/private_html/';
	require_once($newUtilPath.'deriv/CandlestickAnalyzer_DeepSeek.php');

	$candles = getCandleData() ; // ไปอ่าน  rawData.json
    $analyzer = new AdvancedCandlestickAnalyzer($candles);

	// 1. ตัวชี้วัด
	$indicators = $analyzer->getIndicators();
	// 2. วิเคราะห์ลักษณะแท่งเทียน
	$patterns = $analyzer->analyzeCandlestickPatterns();
	// 3. วิเคราะห์แนวโน้ม
    $trend = $analyzer->analyzeTrend();
    // 4. ทำนายแท่งถัดไป
    $prediction = $analyzer->predictNextCandle();
    // 5. แนะนำ Indicator เพิ่มเติม
    $suggestions = $analyzer->suggestAdditionalIndicators();

    print_r($prediction);
	$st = JSON_ENCODE($prediction, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ;
	



} // end function

*/
?>