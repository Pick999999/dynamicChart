<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
คลาส PHP นี้ใช้หลักการผสมผสานหลายๆ ตัวชี้วัดทางเทคนิคเพื่อทำนายแท่งเทียนถัดไป โดยมีหลักการหลักดังนี้:
หลักการทำนาย (Prediction Logic)
1. การนับคะแนนสัญญาณ (Signal Scoring)
ระบบจะนับสัญญาณจากแหล่งต่างๆ และแบ่งเป็นสัญญาณขาขึ้น (bullish) และขาลง (bearish) จากนั้นคำนวณเป็นเปอร์เซ็นต์ความน่าจะเป็น
2. แหล่งสัญญาณที่ใช้ในการทำนาย:
รูปแบบแท่งเทียน (Candlestick Patterns) - ตรวจสอบรูปแบบ เช่น Bullish Engulfing, Hammer, Shooting Star, Morning/Evening Star โดยรูปแบบที่มีคำว่า "Bullish" ได้คะแนนขาขึ้น ส่วนที่มี "Bearish" ได้คะแนนขาลง
ตัวชี้วัด EMA (2 สัญญาณ)

เปรียบเทียบ EMA3 กับ EMA5: ถ้า EMA3 > EMA5 = สัญญาณขาขึ้น
เปรียบเทียบราคาปิดกับ EMA3: ถ้าราคา > EMA3 = สัญญาณขาขึ้น

Bollinger Bands (2 สัญญาณ)

ถ้าราคาต่ำกว่าแถบล่าง = สัญญาณขาขึ้น (คาดว่าจะมีการตีกลับ)
ถ้าราคาสูงกว่าแถบบน = สัญญาณขาลง (คาดว่าจะมีการกลับตัว)
Mean Reversion: ถ้าราคาอยู่เหนือเส้นกลาง = อาจกลับมาเส้นกลาง (สัญญาณขาลง)

เทรนด์โดยรวม (1 สัญญาณ) - ถ้าเทรนด์เป็น Bullish ให้คะแนนขาขึ้น, ถ้าเป็น Bearish ให้คะแนนขาลง

สูตรคำนวณความน่าจะเป็น
ความน่าจะเป็นขาขึ้น (%) = (จำนวนสัญญาณขาขึ้น / สัญญาณทั้งหมด) × 100
ความน่าจะเป็นขาลง (%) = (จำนวนสัญญาณขาลง / สัญญาณทั้งหมด) × 100
จุดเด่นของวิธีนี้

ใช้หลักการ Multi-Indicator Confluence (การรวมสัญญาณจากหลายแหล่ง)
ไม่พึ่งพาตัวชี้วัดตัวเดียว ลดความเสี่ยงจาก False Signal
พิจารณาทั้ง Technical Indicators และ Price Action
ใช้หลักการ Mean Reversion และ Trend Following ผสมกัน

ข้อจำกัด
วิธีนี้เป็นการวิเคราะห์ทางสถิติและไม่ได้รับประกันความแม่นยำ 100% เพราะตลาดมีปัจจัยอื่นๆ มากมายที่ไม่สามารถวัดได้จากแท่งเทียนเพียงอย่างเดียว


ถ้ามี ข้อมูล  array candle ในรูปแบบ 

[{close: 1634.25, time: 1744270440, high: 1635.97, low: 1632.44, open: 1634.28},...] 
ให้ทำการ 
1.สร้าง  ema3,ema5, bollinger band
2.วิเคราะห์  ลักษณะแท่งเทียน แต่ละแท่ง
3.วิเคราะห์  แนวโน้ม trend และความแข็งแกร่ง  
4.วิเคราะห์  เปอร์เซนต์  ว่าแท่งต่อไป จะเป็น Red หรือ Green 
5.ให้แนะนำ Indicator เพิ่มเติมได้ ที่ท่านเห็นว่าเหมาะสม

ทั้งหมดให้ทำเป็น php class 
*/
?>

<?php

class CandlestickAnalyzerClaude {
    private $candles;
    private $ema3;
    private $ema5;
    private $bollingerBands;
    private $patterns;
    private $trend;
    private $trendStrength;
    private $nextCandlePrediction;
    
    /**
     * Constructor
     * 
     * @param array $candles Array of candlestick data
     */
    public function __construct(array $candles) {
        $this->candles = $candles;
        $this->patterns = [];
        $this->initialize();
    }
    
    /**
     * Initialize all calculations
     */
    private function initialize() {
        $this->calculateEMA();
        $this->calculateBollingerBands();
        $this->analyzeCandlePatterns();
        $this->analyzeTrend();
        $this->predictNextCandle();
    }
    
    /**
     * Calculate EMAs (3 and 5 period)
     */
    private function calculateEMA() {
        $this->ema3 = $this->calculateEMAValues(3);
        $this->ema5 = $this->calculateEMAValues(5);
    }
    
    /**
     * Calculate EMA for a specific period
     * 
     * @param int $period EMA period
     * @return array Array of EMA values
     */
    private function calculateEMAValues($period) {
        $ema = [];
        $multiplier = 2 / ($period + 1);
        
        // Use SMA for the first value
        $sum = 0;
        for ($i = 0; $i < $period && $i < count($this->candles); $i++) {
            $sum += $this->candles[$i]['close'];
        }
        
        $sma = $sum / $period;
        $ema[0] = $sma;
        
        // Calculate EMA for remaining values
        for ($i = 1; $i < count($this->candles); $i++) {
            $close = $this->candles[$i]['close'];
            $ema[$i] = ($close - $ema[$i-1]) * $multiplier + $ema[$i-1];
        }
        
        return $ema;
    }
    
    /**
     * Calculate Bollinger Bands (20 period, 2 standard deviations)
     */
    private function calculateBollingerBands() {
        $period = 20;
        $standardDeviations = 2;
        $this->bollingerBands = [];
        
        for ($i = 0; $i < count($this->candles); $i++) {
            if ($i < $period - 1) {
                $this->bollingerBands[$i] = [
                    'middle' => null,
                    'upper' => null,
                    'lower' => null
                ];
                continue;
            }
            
            // Calculate SMA for middle band
            $sum = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $sum += $this->candles[$j]['close'];
            }
            $sma = $sum / $period;
            
            // Calculate standard deviation
            $sumSquaredDiff = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $diff = $this->candles[$j]['close'] - $sma;
                $sumSquaredDiff += $diff * $diff;
            }
            $standardDeviation = sqrt($sumSquaredDiff / $period);
            
            // Calculate bands
            $this->bollingerBands[$i] = [
                'middle' => $sma,
                'upper' => $sma + ($standardDeviation * $standardDeviations),
                'lower' => $sma - ($standardDeviation * $standardDeviations)
            ];
        }
    }
    
    /**
     * Analyze patterns for each candlestick
     */
    private function analyzeCandlePatterns() {
        $this->patterns = [];
        
        foreach ($this->candles as $i => $candle) {
            $body = abs($candle['close'] - $candle['open']);
            $upperShadow = $candle['high'] - max($candle['open'], $candle['close']);
            $lowerShadow = min($candle['open'], $candle['close']) - $candle['low'];
            $isGreen = $candle['close'] > $candle['open'];
            $totalRange = $candle['high'] - $candle['low'];
            
            $pattern = [];
            
            // Determine candlestick type
            if ($body < 0.1 * $totalRange) {
                $pattern[] = "Doji";
                
                if ($upperShadow > 2 * $body && $lowerShadow < 0.1 * $totalRange) {
                    $pattern[] = "Gravestone Doji";
                } elseif ($lowerShadow > 2 * $body && $upperShadow < 0.1 * $totalRange) {
                    $pattern[] = "Dragonfly Doji";
                }
            } elseif ($body > 0.7 * $totalRange) {
                $pattern[] = $isGreen ? "Bullish Marubozu" : "Bearish Marubozu";
            } elseif ($upperShadow > 2 * $body && $lowerShadow > 2 * $body) {
                $pattern[] = "Spinning Top";
            } elseif ($isGreen && $lowerShadow > 2 * $body && $upperShadow < 0.1 * $totalRange) {
                $pattern[] = "Hammer (Bullish)";
            } elseif (!$isGreen && $lowerShadow > 2 * $body && $upperShadow < 0.1 * $totalRange) {
                $pattern[] = "Hanging Man (Bearish)";
            } elseif ($isGreen && $upperShadow > 2 * $body && $lowerShadow < 0.1 * $totalRange) {
                $pattern[] = "Inverted Hammer (Bullish)";
            } elseif (!$isGreen && $upperShadow > 2 * $body && $lowerShadow < 0.1 * $totalRange) {
                $pattern[] = "Shooting Star (Bearish)";
            } else {
                $pattern[] = $isGreen ? "Bullish Candle" : "Bearish Candle";
            }
            
            // Check for engulfing patterns (requires previous candle)
            if ($i > 0) {
                $prevCandle = $this->candles[$i-1];
                $prevIsGreen = $prevCandle['close'] > $prevCandle['open'];
                
                if ($isGreen && !$prevIsGreen) {
                    if ($candle['open'] < $prevCandle['close'] && $candle['close'] > $prevCandle['open']) {
                        $pattern[] = "Bullish Engulfing";
                    }
                } elseif (!$isGreen && $prevIsGreen) {
                    if ($candle['open'] > $prevCandle['close'] && $candle['close'] < $prevCandle['open']) {
                        $pattern[] = "Bearish Engulfing";
                    }
                }
                
                // Check for Morning/Evening Star (requires 3 candles)
                if ($i >= 2) {
                    $prevPrevCandle = $this->candles[$i-2];
                    $prevPrevIsGreen = $prevPrevCandle['close'] > $prevPrevCandle['open'];
                    $prevBodySize = abs($prevCandle['close'] - $prevCandle['open']);
                    $prevPrevBodySize = abs($prevPrevCandle['close'] - $prevPrevCandle['open']);
                    
                    // Morning Star
                    if (!$prevPrevIsGreen && $prevBodySize < 0.3 * $prevPrevBodySize && $isGreen) {
                        $pattern[] = "Morning Star (Bullish)";
                    }
                    
                    // Evening Star
                    if ($prevPrevIsGreen && $prevBodySize < 0.3 * $prevPrevBodySize && !$isGreen) {
                        $pattern[] = "Evening Star (Bearish)";
                    }
                }
            }
            
            $this->patterns[$i] = $pattern;
        }
    }
    
    /**
     * Analyze trend and trend strength
     */
    private function analyzeTrend() {
        // Use EMA crossovers to determine trend
        $lastIndex = count($this->candles) - 1;
        
        // Check EMA trend
        if ($this->ema3[$lastIndex] > $this->ema5[$lastIndex]) {
            $emaTrend = "Bullish";
        } else {
            $emaTrend = "Bearish";
        }
        
        // Check price action trend (last 10 candles)
        $priceActionTrend = $this->analyzePriceActionTrend(10);
        
        // Determine overall trend
        if ($emaTrend == $priceActionTrend) {
            $this->trend = $emaTrend;
            $this->trendStrength = "Strong";
        } else {
            // If EMA and price action disagree, trend might be changing
            $this->trend = $emaTrend;
            $this->trendStrength = "Weak";
        }
        
        // Check Bollinger Bands for volatility and trend strength
        $lastBand = $this->bollingerBands[$lastIndex];
        if ($lastBand['middle'] !== null) {
            $bandwidth = ($lastBand['upper'] - $lastBand['lower']) / $lastBand['middle'];
            
            if ($bandwidth > 0.05) {
                $this->trendStrength .= " with High Volatility";
            } else {
                $this->trendStrength .= " with Low Volatility";
            }
            
            // Check for Bollinger Squeeze (potential breakout)
            if ($bandwidth < 0.02) {
                $this->trendStrength .= " (Bollinger Squeeze - Potential Breakout)";
            }
        }
    }
    
    /**
     * Analyze price action trend based on recent candles
     * 
     * @param int $period Number of candles to analyze
     * @return string Trend direction
     */
    private function analyzePriceActionTrend($period) {
        $count = count($this->candles);
        $startIdx = max(0, $count - $period);
        
        $bullishCount = 0;
        $bearishCount = 0;
        
        for ($i = $startIdx; $i < $count; $i++) {
            if ($this->candles[$i]['close'] > $this->candles[$i]['open']) {
                $bullishCount++;
            } else {
                $bearishCount++;
            }
        }
        
        return ($bullishCount > $bearishCount) ? "Bullish" : "Bearish";
    }
    
    /**
     * Predict next candle color based on patterns and indicators
     */
    private function predictNextCandle() {
        $lastIndex = count($this->candles) - 1;
        $lastCandle = $this->candles[$lastIndex];
        $lastPattern = $this->patterns[$lastIndex];
        
        // Calculate probability based on multiple factors
        $bullishSignals = 0;
        $bearishSignals = 0;
        $totalSignals = 0;
        
        // 1. Candlestick Pattern signals
        foreach ($lastPattern as $pattern) {
            $totalSignals++;
            if (strpos($pattern, "Bullish") !== false || 
                strpos($pattern, "Hammer") !== false || 
                strpos($pattern, "Morning Star") !== false) {
                $bullishSignals++;
            } elseif (strpos($pattern, "Bearish") !== false || 
                     strpos($pattern, "Hanging Man") !== false || 
                     strpos($pattern, "Shooting Star") !== false || 
                     strpos($pattern, "Evening Star") !== false) {
                $bearishSignals++;
            }
        }
        
        // 2. EMA signals
        $totalSignals += 2;
        if ($this->ema3[$lastIndex] > $this->ema5[$lastIndex]) {
            $bullishSignals++;
        } else {
            $bearishSignals++;
        }
        
        if ($lastCandle['close'] > $this->ema3[$lastIndex]) {
            $bullishSignals++;
        } else {
            $bearishSignals++;
        }
        
        // 3. Bollinger Band signals
        if ($lastIndex >= 19) { // Only if Bollinger Bands are available
            $totalSignals += 2;
            $lastBand = $this->bollingerBands[$lastIndex];
            
            // Check for overbought/oversold conditions
            if ($lastCandle['close'] < $lastBand['lower']) {
                $bullishSignals++; // Potential bounce from lower band
            } elseif ($lastCandle['close'] > $lastBand['upper']) {
                $bearishSignals++; // Potential reversal from upper band
            }
            
            // Check for mean reversion
            if ($lastCandle['close'] > $lastBand['middle']) {
                $bearishSignals++; // Potential reversion to mean
            } else {
                $bullishSignals++; // Potential reversion to mean
            }
        }
        
        // 4. Consider overall trend
        $totalSignals++;
        if ($this->trend === "Bullish") {
            $bullishSignals++;
        } else {
            $bearishSignals++;
        }
        
        // Calculate probabilities
        $bullishProbability = ($totalSignals > 0) ? ($bullishSignals / $totalSignals) * 100 : 50;
        $bearishProbability = ($totalSignals > 0) ? ($bearishSignals / $totalSignals) * 100 : 50;
        
        $this->nextCandlePrediction = [
            'green' => round($bullishProbability, 2),
            'red' => round($bearishProbability, 2)
        ];
    }
    
    /**
     * Get EMA values
     * 
     * @return array Associative array of EMA values
     */
    public function getEMA() {
        return [
            'ema3' => $this->ema3,
            'ema5' => $this->ema5
        ];
    }
    
    /**
     * Get Bollinger Bands values
     * 
     * @return array Bollinger Bands values
     */
    public function getBollingerBands() {
        return $this->bollingerBands;
    }
    
    /**
     * Get candlestick pattern analysis
     * 
     * @return array Candlestick patterns for each candle
     */
    public function getCandlePatterns() {
        return $this->patterns;
    }
    
    /**
     * Get trend analysis
     * 
     * @return array Trend and trend strength
     */
    public function getTrendAnalysis() {
        return [
            'trend' => $this->trend,
            'strength' => $this->trendStrength
        ];
    }
    
    /**
     * Get next candle prediction
     * 
     * @return array Prediction probabilities
     */
    public function getNextCandlePrediction() {
        return $this->nextCandlePrediction;
    }
    
    /**
     * Calculate RSI (Relative Strength Index)
     * 
     * @param int $period RSI period (default 14)
     * @return array RSI values
     */
    public function calculateRSI($period = 14) {
        $rsi = [];
        $gains = [];
        $losses = [];
        
        // Calculate initial gains and losses
        for ($i = 1; $i < count($this->candles); $i++) {
            $change = $this->candles[$i]['close'] - $this->candles[$i-1]['close'];
            $gains[$i] = max(0, $change);
            $losses[$i] = max(0, -$change);
        }
        
        // Calculate average gains and losses
        for ($i = $period; $i < count($this->candles); $i++) {
            if ($i == $period) {
                $avgGain = array_sum(array_slice($gains, 1, $period)) / $period;
                $avgLoss = array_sum(array_slice($losses, 1, $period)) / $period;
            } else {
                $avgGain = (($avgGain * ($period - 1)) + $gains[$i]) / $period;
                $avgLoss = (($avgLoss * ($period - 1)) + $losses[$i]) / $period;
            }
            
            if ($avgLoss == 0) {
                $rsi[$i] = 100;
            } else {
                $rs = $avgGain / $avgLoss;
                $rsi[$i] = 100 - (100 / (1 + $rs));
            }
        }
        
        return $rsi;
    }
    
    /**
     * Calculate MACD (Moving Average Convergence Divergence)
     * 
     * @param int $fastPeriod Fast EMA period (default 12)
     * @param int $slowPeriod Slow EMA period (default 26)
     * @param int $signalPeriod Signal EMA period (default 9)
     * @return array MACD values
     */
    public function calculateMACD($fastPeriod = 12, $slowPeriod = 26, $signalPeriod = 9) {
        $fastEMA = $this->calculateEMAValues($fastPeriod);
        $slowEMA = $this->calculateEMAValues($slowPeriod);
        
        $macdLine = [];
        for ($i = 0; $i < count($this->candles); $i++) {
            $macdLine[$i] = $fastEMA[$i] - $slowEMA[$i];
        }
        
        // Calculate signal line (EMA of MACD line)
        $signalLine = [];
        $multiplier = 2 / ($signalPeriod + 1);
        
        // Use SMA for the first value
        $sum = 0;
        for ($i = 0; $i < $signalPeriod && $i < count($macdLine); $i++) {
            $sum += $macdLine[$i];
        }
        
        $sma = $sum / $signalPeriod;
        $signalLine[0] = $sma;
        
        // Calculate signal line EMA for remaining values
        for ($i = 1; $i < count($macdLine); $i++) {
            $signalLine[$i] = ($macdLine[$i] - $signalLine[$i-1]) * $multiplier + $signalLine[$i-1];
        }
        
        // Calculate histogram
        $histogram = [];
        for ($i = 0; $i < count($macdLine); $i++) {
            $histogram[$i] = $macdLine[$i] - $signalLine[$i];
        }
        
        return [
            'macd' => $macdLine,
            'signal' => $signalLine,
            'histogram' => $histogram
        ];
    }
    
    /**
     * Calculate Stochastic Oscillator
     * 
     * @param int $kPeriod %K period (default 14)
     * @param int $dPeriod %D period (default 3)
     * @return array Stochastic Oscillator values
     */
    public function calculateStochastic($kPeriod = 14, $dPeriod = 3) {
        $stochK = [];
        $stochD = [];
        
        for ($i = $kPeriod - 1; $i < count($this->candles); $i++) {
            // Find highest high and lowest low in the period
            $highestHigh = -INF;
            $lowestLow = INF;
            
            for ($j = $i - $kPeriod + 1; $j <= $i; $j++) {
                $highestHigh = max($highestHigh, $this->candles[$j]['high']);
                $lowestLow = min($lowestLow, $this->candles[$j]['low']);
            }
            
            // Calculate %K
            $range = $highestHigh - $lowestLow;
            if ($range == 0) {
                $stochK[$i] = 50; // Default to middle value if no range
            } else {
                $stochK[$i] = (($this->candles[$i]['close'] - $lowestLow) / $range) * 100;
            }
        }
        
        // Calculate %D (SMA of %K)
        for ($i = $kPeriod + $dPeriod - 2; $i < count($this->candles); $i++) {
            $sum = 0;
            for ($j = $i - $dPeriod + 1; $j <= $i; $j++) {
                $sum += $stochK[$j];
            }
            $stochD[$i] = $sum / $dPeriod;
        }
        
        return [
            'k' => $stochK,
            'd' => $stochD
        ];
    }
    
    /**
     * Get recommended indicators for current market conditions
     * 
     * @return array Recommended indicators with explanations
     */
    public function getRecommendedIndicators() {
        $lastIndex = count($this->candles) - 1;
        $recommendations = [];
        
        // Calculate volatility to determine appropriate indicators
        $volatility = $this->calculateVolatility(20);
        
        // Already implemented indicators
        $recommendations[] = [
            'name' => 'EMA (3 and 5)',
            'explanation' => 'Already implemented. Short-term EMAs help identify immediate trend direction.'
        ];
        
        $recommendations[] = [
            'name' => 'Bollinger Bands',
            'explanation' => 'Already implemented. Helps identify volatility and potential reversal points.'
        ];
        
        // RSI recommendation
        $rsi = $this->calculateRSI();
        $lastRSI = end($rsi);
        
        $recommendations[] = [
            'name' => 'RSI (Relative Strength Index)',
            'explanation' => 'Already implemented as additional method. ' . 
                            ($lastRSI > 70 ? 'Current market appears overbought (RSI: ' . round($lastRSI, 2) . ').' : 
                             ($lastRSI < 30 ? 'Current market appears oversold (RSI: ' . round($lastRSI, 2) . ').' : 
                             'Current RSI is ' . round($lastRSI, 2) . ' indicating neutral momentum.'))
        ];
        
        // MACD recommendation
        $macd = $this->calculateMACD();
        $lastMACD = end($macd['macd']);
        $lastSignal = end($macd['signal']);
        
        $recommendations[] = [
            'name' => 'MACD',
            'explanation' => 'Already implemented as additional method. ' . 
                            ($lastMACD > $lastSignal ? 'MACD is above signal line, indicating bullish momentum.' : 
                             'MACD is below signal line, indicating bearish momentum.')
        ];
        
        // Additional recommendations based on market conditions
        if ($volatility > 0.03) {
            $recommendations[] = [
                'name' => 'ATR (Average True Range)',
                'explanation' => 'Recommended for high volatility markets. Helps in setting appropriate stop losses and take profit levels.'
            ];
        }
        
        if ($this->trendStrength === "Strong") {
            $recommendations[] = [
                'name' => 'ADX (Average Directional Index)',
                'explanation' => 'Recommended for trending markets. Helps confirm trend strength and potential continuation.'
            ];
            
            $recommendations[] = [
                'name' => 'Parabolic SAR',
                'explanation' => 'Recommended for trending markets. Helps identify potential reversal points and trailing stop levels.'
            ];
        } else {
            $recommendations[] = [
                'name' => 'Stochastic Oscillator',
                'explanation' => 'Already implemented as additional method. Useful for identifying overbought/oversold conditions in range-bound markets.'
            ];
            
            $recommendations[] = [
                'name' => 'Ichimoku Cloud',
                'explanation' => 'Recommended for uncertain or changing trends. Provides support/resistance levels and trend direction across multiple timeframes.'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Calculate market volatility
     * 
     * @param int $period Period for volatility calculation
     * @return float Volatility value
     */
    private function calculateVolatility($period = 20) {
        $lastIndex = count($this->candles) - 1;
        $startIdx = max(0, $lastIndex - $period + 1);
        
        $changes = [];
        for ($i = $startIdx + 1; $i <= $lastIndex; $i++) {
            $changes[] = abs(($this->candles[$i]['close'] - $this->candles[$i-1]['close']) / $this->candles[$i-1]['close']);
        }
        
        return array_sum($changes) / count($changes);
    }
    
    /**
     * Generate a comprehensive analysis report
     * 
     * @return array Complete analysis
     */
    public function getCompleteAnalysis() {
        return [
            'indicators' => [
                'ema' => $this->getEMA(),
                'bollingerBands' => $this->getBollingerBands(),
                'rsi' => $this->calculateRSI(),
                'macd' => $this->calculateMACD(),
                'stochastic' => $this->calculateStochastic()
            ],
            'candlePatterns' => $this->getCandlePatterns(),
            'trend' => $this->getTrendAnalysis(),
            'prediction' => $this->getNextCandlePrediction(),
            'recommendedIndicators' => $this->getRecommendedIndicators()
        ];
    }
} // end class

// Example usage:
/*

function getCandleData() {


 $st = "";   
 

 $sFileName = 'rawData.json';
 $file = fopen($sFileName,"r");
 while(! feof($file))  {
   $st .= fgets($file) ;
 }
 fclose($file);


$candleDataA = JSON_DECODE($st,true);
echo 'Len=' . count($candleDataA) . '<br>';
$candleData =  array_slice($candleDataA, 0,25);

return $candleData ;

} // end function

Main2();

function Main1() {
 

$candleData  = getCandleData() ;

$analyzer = new CandlestickAnalyzerClaude($candleData);

// Get all analyses
$completeAnalysis = $analyzer->getCompleteAnalysis();

// Or get specific analysis
$emaValues = $analyzer->getEMA();
$bollingerBands = $analyzer->getBollingerBands();
$candlePatterns = $analyzer->getCandlePatterns();
$trendAnalysis = $analyzer->getTrendAnalysis();
$prediction = $analyzer->getNextCandlePrediction();
$recommendedIndicators = $analyzer->getRecommendedIndicators();

// Additional technical indicators
$rsi = $analyzer->calculateRSI();
$macd = $analyzer->calculateMACD();
$stochastic = $analyzer->calculateStochastic();

//print_r($stochastic);

} // end function main1 

function Main2() {
//การวิเคราะห์ข้อมูลแบบต่างๆ
// การวิเคราะห์ทั้งหมดในครั้งเดียว
$candleData  = getCandleData() ;
$analyzer = new CandlestickAnalyzerClaude($candleData);



$completeAnalysis = $analyzer->getCompleteAnalysis();
//print_r($completeAnalysis);

// หรือวิเคราะห์แยกส่วน
$emaValues = $analyzer->getEMA();
$bollingerBands = $analyzer->getBollingerBands();
$candlePatterns = $analyzer->getCandlePatterns();
$trendAnalysis = $analyzer->getTrendAnalysis();
$prediction = $analyzer->getNextCandlePrediction();

 print_r($prediction);
 echo "<hr>";
 print_r($trendAnalysis);
  echo "<hr>";
 print_r($candlePatterns);


} // end function

function main3() {
 // คำนวณ RSI
$rsi = $analyzer->calculateRSI(); // ค่า default period = 14
$rsi = $analyzer->calculateRSI(7); // ปรับ period เป็น 7

// คำนวณ MACD
$macd = $analyzer->calculateMACD(); // ค่า default (12, 26, 9)
$macd = $analyzer->calculateMACD(8, 17, 9); // ปรับค่า fast period, slow period, signal period

// คำนวณ Stochastic Oscillator
$stochastic = $analyzer->calculateStochastic(); // ค่า default (%K=14, %D=3)
$stochastic = $analyzer->calculateStochastic(5, 3); // ปรับค่า %K period และ %D period

} // end function

function main4() {
 //การขยายความสามารถของคลาส

 class ExtendedCandlestickAnalyzerClaude extends CandlestickAnalyzerClaude {
    
    public function calculateFibonacciRetracement() {
        // ค้นหาจุดสูงสุดและต่ำสุดในช่วงเวลาล่าสุด
        $high = -INF;
        $low = INF;
        
        foreach ($this->candles as $candle) {
            $high = max($high, $candle['high']);
            $low = min($low, $candle['low']);
        }
        
        $range = $high - $low;
        
        // คำนวณระดับ Fibonacci
        return [
            '0.0' => $high,
            '0.236' => $high - 0.236 * $range,
            '0.382' => $high - 0.382 * $range,
            '0.5' => $high - 0.5 * $range,
            '0.618' => $high - 0.618 * $range,
            '0.786' => $high - 0.786 * $range,
            '1.0' => $low
        ];
    }
    
    // เพิ่มเติมฟังก์ชันอื่นๆ ได้ตามต้องการ
}

} // end function

function main5() {
 //การใช้งานกับระบบ Real-time
 // สมมติมีฟังก์ชันที่ดึงข้อมูลแท่งเทียนล่าสุด
function getLatestCandles($symbol, $timeframe, $limit = 100) {

    // ดึงข้อมูลจาก API หรือฐานข้อมูล
    // return $candleData;
}

// ใช้งานกับระบบ real-time
$symbol = 'BTCUSDT';
$timeframe = '15m';

while (true) {
    // ดึงข้อมูลล่าสุด
    $candleData = getLatestCandles($symbol, $timeframe);
    
    // วิเคราะห์
    $analyzer = new CandlestickAnalyzerClaude($candleData);
    $analysis = $analyzer->getCompleteAnalysis();
    
    // แสดงผลหรือใช้ในการตัดสินใจ
    if ($analysis['prediction']['green'] > 70) {
        // ส่งสัญญาณซื้อ
        echo "STRONG BUY SIGNAL: {$analysis['prediction']['green']}% chance of bullish candle\n";
    } elseif ($analysis['prediction']['red'] > 70) {
        // ส่งสัญญาณขาย
        echo "STRONG SELL SIGNAL: {$analysis['prediction']['red']}% chance of bearish candle\n";
    }
    
    // รอก่อนดึงข้อมูลใหม่
    sleep(60); // รอ 1 นาที
}

} // end function

function main6() {
//การผสมผสาน Indicators เพื่อสร้างกลยุทธ์การเทรด

// ตัวอย่างการสร้างฟังก์ชันตัดสินใจซื้อ/ขาย
function makeTradeDecision($analyzer) {
    $rsi = $analyzer->calculateRSI();
    $macd = $analyzer->calculateMACD();
    $trendAnalysis = $analyzer->getTrendAnalysis();
    $prediction = $analyzer->getNextCandlePrediction();
    $bollingerBands = $analyzer->getBollingerBands();
    
    $lastIndex = count($rsi) - 1;
    $lastRSI = $rsi[$lastIndex] ?? null;
    $lastPrice = $analyzer->candles[$lastIndex]['close'] ?? null;
    $lastBand = end($bollingerBands);
    
    // ตัวอย่างกลยุทธ์: RSI + MACD + Bollinger Bands
    $buySignal = 
        ($lastRSI < 30) && // RSI บ่งชี้ภาวะขายมากเกินไป
        ($macd['macd'][$lastIndex] > $macd['signal'][$lastIndex]) && // MACD เหนือเส้น Signal
        ($lastPrice < $lastBand['lower']); // ราคาต่ำกว่า Bollinger ล่าง
    
    $sellSignal = 
        ($lastRSI > 70) && // RSI บ่งชี้ภาวะซื้อมากเกินไป
        ($macd['macd'][$lastIndex] < $macd['signal'][$lastIndex]) && // MACD ต่ำกว่าเส้น Signal
        ($lastPrice > $lastBand['upper']); // ราคาสูงกว่า Bollinger บน
    
    if ($buySignal) {
        return 'BUY';
    } elseif ($sellSignal) {
        return 'SELL';
    } else {
        return 'HOLD';
    }
} 


function main7() {

class TradingSystem {
//การบันทึกประวัติและสถิติ

    private $analyzer;
    private $tradeHistory = [];
    
    public function __construct($candleData) {
        $this->analyzer = new CandlestickAnalyzerClaude($candleData);
    }
    
    public function executeStrategy() {
        $decision = makeTradeDecision($this->analyzer);
        $prediction = $this->analyzer->getNextCandlePrediction();
        $currentPrice = end($this->analyzer->candles)['close'];
        
        // บันทึกการตัดสินใจ
        $this->tradeHistory[] = [
            'time' => date('Y-m-d H:i:s'),
            'price' => $currentPrice,
            'decision' => $decision,
            'prediction' => $prediction
        ];
        
        return $decision;
    }
    
    public function calculateSuccessRate() {
        // คำนวณอัตราความสำเร็จจากประวัติการเทรด
        // ...
    }
    
    public function saveHistory($filename) {
        // บันทึกประวัติลงไฟล์
        file_put_contents($filename, json_encode($this->tradeHistory));
    }
}


} // end function

function HowToPlug() {

	$newUtilPath = '/home/thepaper/domains/thepapers.in/private_html/';
	require_once($newUtilPath.'deriv/candleAnalyzerClaude.php');
	$completeAnalysis = $analyzer->getCompleteAnalysis();
//print_r($completeAnalysis);
// อยู่ใน Main2
// หรือวิเคราะห์แยกส่วน
$emaValues = $analyzer->getEMA();
$bollingerBands = $analyzer->getBollingerBands();
$candlePatterns = $analyzer->getCandlePatterns();
$trendAnalysis = $analyzer->getTrendAnalysis();
$prediction = $analyzer->getNextCandlePrediction();

print_r($prediction);

//$st = JSON_ENCODE($, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ;


} // end function
 


*/