<?php

class TechnicalIndicators {
    // Calculate EMA
    private function calculateEMA($prices, $period) {
        $multiplier = 2 / ($period + 1);
        $ema = $prices[0];
        $emaValues = [];
        
        foreach ($prices as $index => $price) {
            if ($index === 0) {
                $emaValues[] = $ema;
                continue;
            }
            $ema = ($price - $ema) * $multiplier + $ema;
            $emaValues[] = $ema;
        }
        
        return $emaValues;
    }
    
    // Calculate RSI
    private function calculateRSI($prices, $period = 7) {
        $gains = [];
        $losses = [];
        $rsi = array_fill(0, count($prices), 0);
        
        // Calculate price changes
        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            $gains[] = $change > 0 ? $change : 0;
            $losses[] = $change < 0 ? -$change : 0;
        }
        
        // Calculate initial average gain and loss
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;
        if ($avgLoss != 0) {        
          $rsi[$period - 1] = 100 - (100 / (1 + $avgGain / $avgLoss));
		}
        //return;
        // Calculate RSI for remaining periods
        for ($i = $period; $i < count($prices); $i++) {
            $avgGain = ($avgGain * ($period - 1) + $gains[$i - 1]) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $losses[$i - 1]) / $period;
            $rsi[$i] = 100 - (100 / (1 + $avgGain / $avgLoss));
        }
        
        return $rsi;
    } 


function analysis_1_Body($data) {

	$open = $data['open'] ;
	$close = $data['close'] ;
	$high = $data['high'] ;
	$low = $data['low'] ;
	$epoch = $data['time'] ;

	// Calculate percentages
	$bodySize = abs($close - $open);
	$trend = '';
	$totalRange = $high - $low;
	if ($totalRange != 0) {	
	 $bodyPercent = ($bodySize / $totalRange) * 100;
	} else {
     $bodyPercent = 0;
	}
	$upperWickPercent = 0;
	$lowerWickPercent = 0;
	if ($totalRange == 0) {
		//die('ไม่มีช่วงราคา (High = Low)');
		return '';
	} 

	if ($close > $open) { // Bullish candle
		$upperWickPercent = (($high - $close) / $totalRange) * 100;
		$lowerWickPercent = (($open - $low) / $totalRange) * 100;
	} else { // Bearish candle
		$upperWickPercent = (($high - $open) / $totalRange) * 100;
		$lowerWickPercent = (($close - $low) / $totalRange) * 100;
	}

	// Round percentages
	$bodyPercent = round($bodyPercent, 2);
	$upperWickPercent = round($upperWickPercent, 2);
	$lowerWickPercent = round($lowerWickPercent, 2);

// Determine candle type
	$candleType = '';
	$force = '';
	$nextTrend = '';
	$rowClass = '';

	if ($bodyPercent >= 70) {
		// Strong candle
		if ($close > $open) {
			$candleType = 'แท่งแข็งแรง (Bullish)';
			$force = 'แรงซื้อสูง';
			$nextTrend = 'มีแนวโน้มขึ้นต่อ หรือปรับฐานเล็กน้อย';
			$rowClass = 'bullish';
		} else {
			$candleType = 'แท่งอ่อนแอ (Bearish)';
			$force = 'แรงขายสูง';
			$nextTrend = 'มีแนวโน้มลงต่อ หรือ反弹เล็กน้อย';
			$rowClass = 'bearish';
		}
	} elseif ($bodyPercent < 10 && $upperWickPercent >= 45 && $lowerWickPercent >= 45) {
		// Doji
		$candleType = 'Doji';
		$force = 'ซื้อ-ขายสมดุล';
		$nextTrend = 'อาจเปลี่ยนแนวโน้ม (reversal)';
		$rowClass = 'neutral';
	} elseif ($bodyPercent >= 30 && $bodyPercent <= 40 && $lowerWickPercent >= 50 && $upperWickPercent <= 15) {
		// Hammer (only valid in downtrend)
		if ($trend == 'downtrend' && $close > $open) {
			$candleType = 'Hammer (Bullish Reversal)';
			$force = 'แรงซื้อฟื้นตัว';
			$nextTrend = 'ขึ้นต่อหากยืนยันด้วยแท่งเขียว';
			$rowClass = 'bullish';
		} else {
			$candleType = 'แท่งมี Lower Wick ยาว';
			$force = 'มีแรงซื้อเข้ามา';
			$nextTrend = 'อาจปรับตัวขึ้น';
			$rowClass = 'bullish';
		}
	} elseif ($bodyPercent >= 30 && $bodyPercent <= 40 && $upperWickPercent >= 50 && $lowerWickPercent <= 15) {
		// Hanging Man (only valid in uptrend)
		if ($trend == 'uptrend' && $close < $open) {
			$candleType = 'Hanging Man (Bearish Reversal)';
			$force = 'แรงขายฟื้นตัว';
			$nextTrend = 'ลงต่อหากยืนยันด้วยแท่งแดง';
			$rowClass = 'bearish';
		} else {
			$candleType = 'แท่งมี Upper Wick ยาว';
			$force = 'มีแรงขายเข้ามา';
			$nextTrend = 'อาจปรับตัวลง';
			$rowClass = 'bearish';
		}
	} elseif ($bodyPercent >= 30 && $bodyPercent <= 40 && abs($upperWickPercent - $lowerWickPercent) <= 10) {
		// Spinning Top
		$candleType = 'Spinning Top';
		$force = 'ซื้อ-ขายดุลกัน';
		$nextTrend = 'รอสัญญาณชัดเจนจากแท่งถัดไป';
		$rowClass = 'neutral';
	} elseif ($bodyPercent < 5 && $lowerWickPercent >= 90) {
		// Dragonfly Doji
		$candleType = 'Dragonfly Doji';
		$force = 'แรงซื้อชนะ';
		$nextTrend = 'สัญญาณกลับขึ้น (Bullish)';
		$rowClass = 'bullish';
	} elseif ($bodyPercent < 5 && $upperWickPercent >= 90) {
		// Gravestone Doji
		$candleType = 'Gravestone Doji';
		$force = 'แรงขายชนะ';
		$nextTrend = 'สัญญาณกลับลง (Bearish)';
		$rowClass = 'bearish';
	} else {
		// Custom analysis based on percentages
		if ($close > $open) {
			$candleType = 'Bullish Candle';
			$rowClass = 'bullish';
		} else {
			$candleType = 'Bearish Candle';
			$rowClass = 'bearish';
		}
		
		if ($bodyPercent > $upperWickPercent && $bodyPercent > $lowerWickPercent) {
			if ($bodyPercent >= 50) {
				$force = $upperWickPercent > $lowerWickPercent ? 'แรงขายปานกลาง' : 'แรงซื้อปานกลาง';
				$nextTrend = $upperWickPercent > $lowerWickPercent ? 'อาจปรับตัวลง' : 'อาจปรับตัวขึ้น';
			} else {
				$force = 'แรงซื้อ-ขายใกล้เคียงกัน';
				$nextTrend = 'รอสัญญาณยืนยัน';
				$rowClass = 'neutral';
			}
		} else {
			if ($upperWickPercent > $lowerWickPercent) {
				$force = 'มีแรงขายเหนือกว่า';
				$nextTrend = 'แนวโน้มลง';
				$rowClass = 'bearish';
			} else {
				$force = 'มีแรงซื้อเหนือกว่า';
				$nextTrend = 'แนวโน้มขึ้น';
				$rowClass = 'bullish';
			}
		}
	}

	// Add trend context to analysis
	$trendText = '';
	switch ($trend) {
		case 'uptrend':
			$trendText = 'อยู่ในแนวโน้มขึ้น';
			break;
		case 'downtrend':
			$trendText = 'อยู่ในแนวโน้มลง';
			break;
		case 'sideways':
			$trendText = 'อยู่ในช่วง sideways';
			break;
	}

	// Generate visual candle representation
	$candleVisual = '';
	if ($close > $open) {
		$candleVisual = '<div class="candle bullish-candle">
			<div class="upper-wick" style="height: '.$upperWickPercent.'%"></div>
			<div class="body" style="height: '.$bodyPercent.'%"></div>
			<div class="lower-wick" style="height: '.$lowerWickPercent.'%"></div>
		</div>';
	} else {
		$candleVisual = '<div class="candle bearish-candle">
			<div class="upper-wick" style="height: '.$upperWickPercent.'%"></div>
			<div class="body" style="height: '.$bodyPercent.'%"></div>
			<div class="lower-wick" style="height: '.$lowerWickPercent.'%"></div>
		</div>';
}

$sObj = new stdClass() ;
$sObj->upperWickPercent = $upperWickPercent;
$sObj->bodyPercent = $bodyPercent;
$sObj->lowerWickPercent = $lowerWickPercent;
$sObj->candleType = $candleType ;
$sObj->force = $force ;
$sObj->trendText  = $trendText ;
$sObj->nextTrend = $nextTrend ;

return $sObj ;



} // end function



function calculateADX($data, $period = 14) {
    // ตรวจสอบว่าข้อมูลมีเพียงพอสำหรับคำนวณหรือไม่
    if (count($data) < $period + 1) {
        return array("error" => "ต้องการข้อมูลอย่างน้อย " . ($period + 1) . " แท่ง");
    }

    $dataCount = count($data);
    
    // เตรียม arrays สำหรับเก็บค่าต่างๆ โดยเริ่มต้นด้วยค่า null หรือ 0 ตามจำนวนข้อมูล
    $trueRanges = array_fill(0, $dataCount, null);
    $plusDMs = array_fill(0, $dataCount, null);
    $minusDMs = array_fill(0, $dataCount, null);
    $plusDIs = array_fill(0, $dataCount, null);
    $minusDIs = array_fill(0, $dataCount, null);
    $dxs = array_fill(0, $dataCount, null);
    $adxs = array_fill(0, $dataCount, null);
    
    // 1. คำนวณ True Range (TR) และ Directional Movement (DM)
    for ($i = 1; $i < $dataCount; $i++) {
        $high = $data[$i]['high'];
        $low = $data[$i]['low'];
        $prevHigh = $data[$i-1]['high'];
        $prevLow = $data[$i-1]['low'];
        $prevClose = $data[$i-1]['close'];
        
        // คำนวณ True Range
        $tr1 = abs($high - $low);
        $tr2 = abs($high - $prevClose);
        $tr3 = abs($low - $prevClose);
        $tr = max($tr1, $tr2, $tr3);
        $trueRanges[$i] = $tr;
        
        // คำนวณ Plus Directional Movement (+DM) และ Minus Directional Movement (-DM)
        $upMove = $high - $prevHigh;
        $downMove = $prevLow - $low;
        
        if ($upMove > $downMove && $upMove > 0) {
            $plusDMs[$i] = $upMove;
        } else {
            $plusDMs[$i] = 0;
        }
        
        if ($downMove > $upMove && $downMove > 0) {
            $minusDMs[$i] = $downMove;
        } else {
            $minusDMs[$i] = 0;
        }
    }
    
    // 2. คำนวณ Smoothed TR, +DM, -DM เมื่อมีข้อมูลครบตาม period
    if ($dataCount > $period) {
        // เก็บค่า Smoothed ในแต่ละวัน
        $smoothedTRs = array_fill(0, $dataCount, null);
        $smoothedPlusDMs = array_fill(0, $dataCount, null);
        $smoothedMinusDMs = array_fill(0, $dataCount, null);
        
        // คำนวณค่า Smoothed แรก
        $smoothedTRs[$period] = array_sum(array_slice($trueRanges, 1, $period));
        $smoothedPlusDMs[$period] = array_sum(array_slice($plusDMs, 1, $period));
        $smoothedMinusDMs[$period] = array_sum(array_slice($minusDMs, 1, $period));
        
        // คำนวณ +DI และ -DI สำหรับ period แรก
        if ($smoothedTRs[$period] > 0) {
            $plusDIs[$period] = 100 * ($smoothedPlusDMs[$period] / $smoothedTRs[$period]);
            $minusDIs[$period] = 100 * ($smoothedMinusDMs[$period] / $smoothedTRs[$period]);
        } else {
            $plusDIs[$period] = 0;
            $minusDIs[$period] = 0;
        }
        
        // คำนวณ DX สำหรับ period แรก
        $diDiff = abs($plusDIs[$period] - $minusDIs[$period]);
        $diSum = $plusDIs[$period] + $minusDIs[$period];
        $dxs[$period] = ($diSum > 0) ? 100 * ($diDiff / $diSum) : 0;
        
        // คำนวณค่าต่อไปสำหรับข้อมูลที่เหลือ
        for ($i = $period + 1; $i < $dataCount; $i++) {
            // อัพเดต Smoothed TR, +DM, -DM
            $smoothedTRs[$i] = $smoothedTRs[$i-1] - ($smoothedTRs[$i-1] / $period) + $trueRanges[$i];
            $smoothedPlusDMs[$i] = $smoothedPlusDMs[$i-1] - ($smoothedPlusDMs[$i-1] / $period) + $plusDMs[$i];
            $smoothedMinusDMs[$i] = $smoothedMinusDMs[$i-1] - ($smoothedMinusDMs[$i-1] / $period) + $minusDMs[$i];
            
            // คำนวณ +DI และ -DI
            if ($smoothedTRs[$i] > 0) {
                $plusDIs[$i] = 100 * ($smoothedPlusDMs[$i] / $smoothedTRs[$i]);
                $minusDIs[$i] = 100 * ($smoothedMinusDMs[$i] / $smoothedTRs[$i]);
            } else {
                $plusDIs[$i] = 0;
                $minusDIs[$i] = 0;
            }
            
            // คำนวณ DX
            $diDiff = abs($plusDIs[$i] - $minusDIs[$i]);
            $diSum = $plusDIs[$i] + $minusDIs[$i];
            $dxs[$i] = ($diSum > 0) ? 100 * ($diDiff / $diSum) : 0;
        }
        
        // 4. คำนวณ ADX หลังจากมี DX อย่างน้อย period ค่า
        if ($dataCount > 2 * $period - 1) {
            // คำนวณ ADX แรก
            $adxs[2 * $period - 1] = array_sum(array_slice($dxs, $period, $period)) / $period;
            
            // คำนวณค่า ADX ที่เหลือโดยใช้ค่าเฉลี่ยเคลื่อนที่
            for ($i = 2 * $period; $i < $dataCount; $i++) {
                $adxs[$i] = (($period - 1) * $adxs[$i - 1] + $dxs[$i]) / $period;
            }
        }
    }
    
    // สร้าง array ผลลัพธ์
    $result = array(
        'plusDI' => $plusDIs,
        'minusDI' => $minusDIs,
        'adx' => $adxs
    );
    
    return $result;
}
 


// การใช้งานจริงควรมีข้อมูลมากกว่านี้
// ตัวอย่าง:
// $adxResult = calculateADX($data, 14); // ใช้ period 14 (ค่าปกติ)
// echo "ADX: " . end($adxResult['adx']) . "\n";
// echo "+DI: " . end($adxResult['plusDI']) . "\n";
// echo "-DI: " . end($adxResult['minusDI']) . "\n";

    
    // Calculate Bollinger Bands
    private function calculateBB($prices, $period = 20, $stdDev = 2) {
        $bands = [];
        $count = count($prices);
        
        for ($i = 0; $i < $count; $i++) {
            if ($i < $period - 1) {
                $bands[] = null;
                continue;
            }
            
            $slice = array_slice($prices, $i - $period + 1, $period);
            $sma = array_sum($slice) / $period;
            
            // Calculate standard deviation
            $variance = 0;
            foreach ($slice as $price) {
                $variance += pow($price - $sma, 2);
            }
            $variance /= $period;
            $std = sqrt($variance);
            
            $bands[] = [
                'upper' => $sma + ($stdDev * $std),
                'middle' => $sma,
                'lower' => $sma - ($stdDev * $std)
            ];
        }
        
        return $bands;
    }
    
    // Calculate ATR
    private function calculateATR($candles, $period = 14) {
        $tr = [];
        $atr = [];
        
        foreach ($candles as $index => $candle) {
            if ($index === 0) {
                $tr[] = $candle['high'] - $candle['low'];
                continue;
            }
            
            $previousClose = $candles[$index - 1]['close'];
            $tr[] = max(
                $candle['high'] - $candle['low'],
                abs($candle['high'] - $previousClose),
                abs($candle['low'] - $previousClose)
            );
        }
        
        // Calculate ATR
        $atr[0] = $tr[0];
        for ($i = 1; $i < count($tr); $i++) {
            $atr[$i] = (($atr[$i - 1] * ($period - 1)) + $tr[$i]) / $period;
        }
        
        return $atr;
    }
    
    // Main function to calculate all indicators
    public function calculateIndicators($candlesticks,$emaShort=3,$emaLong=5) {
        // Extract close prices
        $closePrices = array_map(function($candle) {
            return $candle['close'];
        }, $candlesticks);
		
        
        // Calculate indicators
        $ema3 = $this->calculateEMA($closePrices, 2);		
        $ema5 = $this->calculateEMA($closePrices, 4);
        $rsi = $this->calculateRSI($closePrices);		
        $bb = $this->calculateBB($closePrices);
        $atr = $this->calculateATR($candlesticks);
		
		$period = 7 ;
        $adx = $this->calculateADX($candlesticks,$period);

		$headADX = array();
		for ($i=0;$i<= count($candlesticks)-1;$i++) {
          if ($i <= $period-1) {
           $headADX[] = 0 ;		   
          } else {
           
		   if (isset($adx['adx'][$i-$period-1])) {		   
			// echo $i-$period-1 . '='. $adx['adx'][$i-$period-1].  '-';
             $headADX[] = $adx['adx'][$i-$period-1];
		   }
		  }
		} 
		//print_r($headADX) ; return ;
		$candleWick = array();
		for ($i=0;$i<= count($candlesticks)-1;$i++) {       
  		  $bodyObj = $this->analysis_1_Body($candlesticks[$i]) ;
		  $candleWick[] = $bodyObj ;
		}
        
		
		 

        
        // Transform data
        $result = [];
        foreach ($candlesticks as $index => $candle) {
			if ($candle['open'] > $candle['close']) {
				$thisColor = 'Red';
			}
			if ($candle['open'] < $candle['close']) {
				$thisColor = 'Green';
			}
			if ($candle['open'] === $candle['close']) {
				$thisColor = 'Gray';
			}

            $result[] = [
                'candleID' => $candle['time'],
                'timeframe' => '1m',
                'id' => (string)($index + 1),
                'timestamp' => (string)$candle['time'] ,
                'timefrom_unix' => date('c', $candle['time']),
				'high'=> $candle['high'],
				'low'=> $candle['low'],
				'open'=> $candle['open'],
				'close'=> $candle['close'],
				'thisColor'=>$thisColor,
                'pip' => 0 ,
                'ema3' => isset($ema3[$index]) ? $ema3[$index] : 0,
                'ema5' => isset($ema5[$index]) ? $ema5[$index] : 0,
                'BB' => isset($bb[$index]) ? [
                    'upper' => number_format($bb[$index]['upper'], 2),
                    'middle' => number_format($bb[$index]['middle'], 2),
                    'lower' => number_format($bb[$index]['lower'], 2)
                ] : 0,
                'rsi' => isset($rsi[$index]) ? number_format($rsi[$index], 2) : 0,
                'atr' => isset($atr[$index]) ? number_format($atr[$index], 2) : 0,

				'adx' => isset($adx['adx'][$index]) ? number_format($adx['adx'][$index], 2) : 0,
                'candleWick' => $candleWick[$index],  
            ];
        }
        
        return $result;
    }
}

// Example usage:
/*
$candlesticks = [
    [
        "close" => 1660.18,
        "epoch" => 1739071140,
        "high" => 1663.88,
        "low" => 1659.78,
        "open" => 1663.88
    ],
    [
        "close" => 1657.04,
        "epoch" => 1739071200,
        "high" => 1659.47,
        "low" => 1657.04,
        "open" => 1659.47
    ]
];

$calculator = new TechnicalIndicators();
$result = $calculator->calculateIndicators($candlesticks);
echo json_encode($result, JSON_PRETTY_PRINT);
*/
?>