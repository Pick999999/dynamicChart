<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class TradeAnalyzer {
    private $candles;
    private $ema3 = [];
    private $ema5 = [];
    private $bollinger = [];
    private $rsi = [];
    private $atr = [];
    private $ichimoku = [];
    private $macd = [];
    private $stochRsi = [];
    private $adx = [];
    private $cci = [];

    public function __construct(array $candles) {
        $this->candles = $candles;
		//echo 'Total Len=' . count($candles) . '<hr>';
        $this->calculateIndicators();
    } 

	// ฟังก์ชั่นสำหรับทำนายโอกาส Green หรือ Red สำหรับแท่งถัดไป
    public function predictNextCandle() {
        $latestCandle = $this->candles[count($this->candles) - 1];  // แท่งล่าสุด
        $currentRSI = end($this->rsi);  // RSI ล่าสุด
        $currentStochRsi = end($this->stochRsi);  // Stochastic RSI ล่าสุด
        $currentMACD = end($this->macd['macd']);  // MACD ล่าสุด
        $currentADX = end($this->adx);  // ADX ล่าสุด

        $probabilityGreen = 50;  // ค่าเริ่มต้น, เราจะปรับตาม Indicators
        $probabilityRed = 50;    // ค่าเริ่มต้น

        // ใช้ RSI ทำนาย
        if ($currentRSI > 70) {
            $probabilityRed += 20;  // RSI > 70 (Overbought) มีโอกาสลง
            $probabilityGreen -= 20;
        } elseif ($currentRSI < 30) {
            $probabilityGreen += 20;  // RSI < 30 (Oversold) มีโอกาสขึ้น
            $probabilityRed -= 20;
        }

        // ใช้ MACD ทำนาย
        if ($currentMACD > 0) {
            $probabilityGreen += 15;  // MACD บวกแสดงถึงขาขึ้น
            $probabilityRed -= 15;
        } else {
            $probabilityRed += 15;  // MACD ลบแสดงถึงขาลง
            $probabilityGreen -= 15;
        }

        // ใช้ Stochastic RSI ทำนาย
        if ($currentStochRsi > 80) {
            $probabilityRed += 10;  // Stochastic RSI > 80, Overbought
            $probabilityGreen -= 10;
        } elseif ($currentStochRsi < 20) {
            $probabilityGreen += 10;  // Stochastic RSI < 20, Oversold
            $probabilityRed -= 10;
        }

        // ใช้ ADX ทำนาย
        if ($currentADX > 25) {
            $probabilityGreen += 10;  // ADX สูงแสดงถึงเทรนด์ที่แข็งแกร่ง
            $probabilityRed += 10;
        }

        // การคำนวณสุดท้าย: แนวโน้มสูงสุดที่คำนวณได้จาก Indicators
        $probabilityGreen = max(0, min(100, $probabilityGreen));
        $probabilityRed = max(0, min(100, $probabilityRed));

        return [
			'timeCandle' => $latestCandle['time'],
            'green' => $probabilityGreen,
            'red' => $probabilityRed
        ];
    }


    private function getClosePrices() {
        return array_column($this->candles, 'close');
    }

    private function getHighs() {
        return array_column($this->candles, 'high');
    }

    private function getLows() {
        return array_column($this->candles, 'low');
    }

    private function getTypicalPrices() {
        $typical = [];
        foreach ($this->candles as $c) {
            $typical[] = ($c['high'] + $c['low'] + $c['close']) / 3;
        }
        return $typical;
    }

    private function ema(array $data, int $period) {
        $multiplier = 2 / ($period + 1);
        $ema = [];
        $ema[0] = $data[0];
        for ($i = 1; $i < count($data); $i++) {
            $ema[$i] = ($data[$i] - $ema[$i - 1]) * $multiplier + $ema[$i - 1];
        }
        return $ema;
    }

    private function calculateIndicators() {
        $close = $this->getClosePrices();
        $highs = $this->getHighs();
        $lows = $this->getLows();
        $typical = $this->getTypicalPrices();

        $this->ema3 = $this->ema($close, 3);
        $this->ema5 = $this->ema($close, 5);
        $this->bollinger = $this->calculateBollinger($close);
        $this->rsi = $this->calculateRSI($close, 14);
        $this->atr = $this->calculateATR($highs, $lows, $close, 14);
        $this->ichimoku = $this->calculateIchimoku($highs, $lows, $close);
        $this->macd = $this->calculateMACD($close);
        $this->stochRsi = $this->calculateStochasticRSI($close, 14);
        $this->adx = $this->calculateADX($highs, $lows, $close, 14);
        $this->cci = $this->calculateCCI($typical, 20);
    }

    private function calculateBollinger(array $close, $period = 20) {
        $boll = [];
        for ($i = $period - 1; $i < count($close); $i++) {
            $slice = array_slice($close, $i - $period + 1, $period);
            $avg = array_sum($slice) / $period;
            $sq_diff = array_map(fn($v) => pow($v - $avg, 2), $slice);
            $std = sqrt(array_sum($sq_diff) / $period);
            $boll[$i] = [
                'middle' => $avg,
                'upper' => $avg + 2 * $std,
                'lower' => $avg - 2 * $std,
            ];
        }
        return $boll;
    }

    private function calculateRSI(array $prices, $period = 14) {
        $rsi = [];
        $gains = $losses = 0;
        for ($i = 1; $i <= $period; $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            if ($change >= 0) $gains += $change;
            else $losses -= $change;
        }
        $avgGain = $gains / $period;
        $avgLoss = $losses / $period;
        $rs = $avgLoss == 0 ? 100 : $avgGain / $avgLoss;
        $rsi[$period] = 100 - (100 / (1 + $rs));
        for ($i = $period + 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            $gain = $change > 0 ? $change : 0;
            $loss = $change < 0 ? -$change : 0;
            $avgGain = ($avgGain * ($period - 1) + $gain) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $loss) / $period;
            $rs = $avgLoss == 0 ? 100 : $avgGain / $avgLoss;
            $rsi[$i] = 100 - (100 / (1 + $rs));
        }
        return $rsi;
    }

    private function calculateATR(array $highs, array $lows, array $closes, $period = 14) {
        $tr = [];
        for ($i = 1; $i < count($closes); $i++) {
            $tr[] = max(
                $highs[$i] - $lows[$i],
                abs($highs[$i] - $closes[$i - 1]),
                abs($lows[$i] - $closes[$i - 1])
            );
        }
        $atr = [];
        $atr[$period - 1] = array_sum(array_slice($tr, 0, $period)) / $period;
        for ($i = $period; $i < count($tr); $i++) {
            $atr[$i] = ($atr[$i - 1] * ($period - 1) + $tr[$i]) / $period;
        }
        return $atr;
    }

    private function calculateIchimoku($highs, $lows, $closes) {
        $conversion = [];
        $base = [];
        $spanA = [];
        $spanB = [];
        for ($i = 9; $i < count($highs); $i++) {
            $high9 = max(array_slice($highs, $i - 8, 9));
            $low9 = min(array_slice($lows, $i - 8, 9));
            $conversion[$i] = ($high9 + $low9) / 2;
        }
        for ($i = 26; $i < count($highs); $i++) {
            $high26 = max(array_slice($highs, $i - 25, 26));
            $low26 = min(array_slice($lows, $i - 25, 26));
            $base[$i] = ($high26 + $low26) / 2;
            $spanA[$i] = ($conversion[$i] + $base[$i]) / 2;
        }
        for ($i = 52; $i < count($highs); $i++) {
            $high52 = max(array_slice($highs, $i - 51, 52));
            $low52 = min(array_slice($lows, $i - 51, 52));
            $spanB[$i] = ($high52 + $low52) / 2;
        }
        return [
            'conversion' => $conversion,
            'base' => $base,
            'spanA' => $spanA,
            'spanB' => $spanB
        ];
    }

    private function calculateMACD($close) {
        $ema12 = $this->ema($close, 12);
        $ema26 = $this->ema($close, 26);
        $macd = [];
        $signal = [];
        $histogram = [];
        for ($i = 0; $i < count($close); $i++) {
            $macd[$i] = $ema12[$i] - $ema26[$i] ?? 0;
        }
        $signal = $this->ema($macd, 9);
        for ($i = 0; $i < count($macd); $i++) {
            $histogram[$i] = $macd[$i] - ($signal[$i] ?? 0);
        }
        return [
            'macd' => $macd,
            'signal' => $signal,
            'histogram' => $histogram
        ];
    }

    private function calculateStochasticRSI($close, $period = 14) {
        $rsi = $this->calculateRSI($close, $period);
        $stoch = [];
        for ($i = $period; $i < count($rsi); $i++) {
            $rsiSlice = array_slice($rsi, $i - $period + 1, $period);
            $minRsi = min($rsiSlice);
            $maxRsi = max($rsiSlice);
            $stoch[$i] = $maxRsi - $minRsi == 0 ? 0 : (($rsi[$i] - $minRsi) / ($maxRsi - $minRsi)) * 100;
        }
        return $stoch;
    }

    private function calculateADX($highs, $lows, $closes, $period = 14) {
        $plusDM = $minusDM = $tr = [];
        for ($i = 1; $i < count($closes); $i++) {
            $upMove = $highs[$i] - $highs[$i - 1];
            $downMove = $lows[$i - 1] - $lows[$i];
            $plusDM[] = ($upMove > $downMove && $upMove > 0) ? $upMove : 0;
            $minusDM[] = ($downMove > $upMove && $downMove > 0) ? $downMove : 0;
            $tr[] = max(
                $highs[$i] - $lows[$i],
                abs($highs[$i] - $closes[$i - 1]),
                abs($lows[$i] - $closes[$i - 1])
            );
        }
        $adx = [];
        for ($i = $period - 1; $i < count($tr); $i++) {
            $plusDMAvg = array_sum(array_slice($plusDM, $i - $period + 1, $period)) / $period;
            $minusDMAvg = array_sum(array_slice($minusDM, $i - $period + 1, $period)) / $period;
            $trAvg = array_sum(array_slice($tr, $i - $period + 1, $period)) / $period;
            $pDI = ($plusDMAvg / $trAvg) * 100;
            $mDI = ($minusDMAvg / $trAvg) * 100;
            $adx[$i] = abs($pDI - $mDI);
        }
        return $adx;
    }

    private function calculateCCI($typical, $period = 20) {
        $cci = [];
        for ($i = $period - 1; $i < count($typical); $i++) {
            $slice = array_slice($typical, $i - $period + 1, $period);
            $avg = array_sum($slice) / $period;
            $mad = array_sum(array_map(fn($v) => abs($v - $avg), $slice)) / $period;
            $cci[$i] = ($typical[$i] - $avg) / (0.015 * $mad);
        }
        return $cci;
    }

    public function getIndicators() {
        return [
            'ema3' => $this->ema3,
            'ema5' => $this->ema5,
            'bollinger' => $this->bollinger,
            'rsi' => $this->rsi,
            'atr' => $this->atr,
            'ichimoku' => $this->ichimoku,
            'macd' => $this->macd,
            'stochastic_rsi' => $this->stochRsi,
            'adx' => $this->adx,
            'cci' => $this->cci
        ];
    } 

public function analyzeTrend() {

        $count = count($this->candles);
        if ($count < 52) return ['trend' => 'Not enough data', 'strength' => 'Weak'];
        
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

public function predictNextCandleV2() {


    $count = count($this->candles);
    if ($count < 52) return ['green' => 50, 'red' => 50];
    
    $factors = [
        'trend' => 0,
        'recent_candles' => 0,
        'bollinger_position' => 0,
        'ema_cross' => 0,
        'rsi' => 0,
        'adx' => 0,
        'ichimoku' => 0,
        'stochastic_rsi' => 0,  // เพิ่ม Stochastic RSI
        'macd' => 0,             // เพิ่ม MACD
        'atr' => 0               // เพิ่ม ATR
    ];
    
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
    
    // 8. วิเคราะห์จาก Stochastic RSI
    $stochasticRsiValue = end($this->stochRsi);
    if ($stochasticRsiValue > 80) {
        $factors['stochastic_rsi'] = -1.2; // Overbought
    } elseif ($stochasticRsiValue < 20) {
        $factors['stochastic_rsi'] = 1.2; // Oversold
    } else {
        $factors['stochastic_rsi'] = 0.5; // Neutral
    }
    
    // 9. วิเคราะห์จาก MACD
    $macdValue = end($this->macd['macd']);
    if ($macdValue > 0) {
        $factors['macd'] = 1.5; // Bullish
    } elseif ($macdValue < 0) {
        $factors['macd'] = -1.5; // Bearish
    } else {
        $factors['macd'] = 0; // Neutral
    }
    
    // 10. วิเคราะห์จาก ATR
    $atrValue = end($this->atr);
    if ($atrValue > 0) {
        $factors['atr'] = 1; // ATR สูง = ความผันผวนสูง
    } else {
        $factors['atr'] = -1; // ATR ต่ำ = ความผันผวนต่ำ
    }
    
    // คำนวณคะแนนรวม (ปรับน้ำหนักตามความสำคัญของแต่ละปัจจัย)
    $totalScore = (
        ($factors['trend'] * 1.5) +
        ($factors['recent_candles'] * 0.8) +
        ($factors['bollinger_position'] * 1.2) +
        ($factors['ema_cross'] * 1.0) +
        ($factors['rsi'] * 1.3) +
        ($factors['adx'] * 1.1) +
        ($factors['ichimoku'] * 1.4) +
        ($factors['stochastic_rsi'] * 1.1) +
        ($factors['macd'] * 1.2) +
        ($factors['atr'] * 1.0)
    ) * 5;
    
    $greenProbability = 50 + $totalScore;
    $greenProbability = max(15, min(85, $greenProbability)); // จำกัดระหว่าง 15%-85%
    
    return [
        'green' => round($greenProbability, 2),
        'red' => round(100 - $greenProbability, 2),
        'factors' => $factors,
        'indicators' => [
            'rsi' => $rsiValue,
            'adx' => $adxValue,
            'ichimoku_signal' => $ichimokuSignal,
            'stochastic_rsi' => $stochasticRsiValue,
            'macd' => $macdValue,
            'atr' => $atrValue
        ]
    ];
}

} // end class

function getCandleDataChatGPT() {


 $st = "";   
 

 $sFileName = 'rawData.json';
 $file = fopen($sFileName,"r");
 while(! feof($file))  {
   $st .= fgets($file) ;
 }
 fclose($file);


$candleDataA = JSON_DECODE($st,true);
//echo 'Len=' . count($candleDataA) . '<br>';
$candleData =  array_slice($candleDataA, 0,52);

return $candleData ;

} // end function 

// Example usage
/*
$candles = [
    ['open' => 1634.25, 'high' => 1635.97, 'low' => 1632.44, 'close' => 1634.28, 'time' => 1744270440],
    // Add more candles here
];
*/
/*
$candles = getCandleDataChatGPT();

$tradeAnalyzer = new TradeAnalyzer($candles);
//print_r($tradeAnalyzer->getIndicators());
$prediction = $tradeAnalyzer->predictNextCandle();
echo "Probability of Green: " . $prediction['green'] . "%\n";
echo "Probability of Red: " . $prediction['red'] . "%\n";
*/
?>
