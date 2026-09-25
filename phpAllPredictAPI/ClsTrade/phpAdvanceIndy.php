<?php
/*
$threshold = 0.001 คือค่าที่ใช้ตัดสินว่าความชันเป็น "parallel" หรือไม่
ถ้าความชันน้อยกว่า 0.001 จะถือว่าเส้นขนาน
เราสามารถปรับค่านี้ให้มากขึ้นหรือน้อยลงได้ตามต้องการ

ถ้าให้ค่ามากขึ้น เช่น 0.01 จะทำให้มีโอกาสเป็น parallel มากขึ้น
ถ้าให้ค่าน้อยลง เช่น 0.0001 จะทำให้มีโอกาสเป็น parallel น้อยลง

*/

class AdvancedIndicators {

    public function calculateAdvancedIndicators($data) {
        $result = [];
        
        foreach ($data as $index => $current) {
            $previous = $index > 0 ? $data[$index - 1] : null;
            
            // Calculate all indicators
            $macd = $this->calculateMACD($current);
            $ema3Slope = $this->calculateEMA3Slope($current, $previous);
            $ema5Slope = $this->calculateEMA5Slope($current, $previous);
            $emaAbove = $this->determineEMAPosition($current);
            $emaCross = $this->detectEMACross($current, $previous);
            $turnPointType = $this->detectTurnPoint($data, $index);
			//echo '->' . $turnPointType;
            //$turnPointType = '';
            $color = $this->determineColor($current, $previous);
			$color = $current['thisColor'];

            
            // New calculations
            $ema3SlopeDirection = $this->determineEMASlopeDirection($ema3Slope);
            $ema5SlopeDirection = $this->determineEMASlopeDirection($ema5Slope);
            $emaConflict = $this->detectEMAConflict($current, $color);
            $ema3Position = $this->detectEMA3CandlePosition($current);
			$ema5Position = $this->detectEMA5CandlePosition($current);
            
            // Combine all data
            $result[] = array_merge($current, [
				'CandleCode'  => '',
                'thisColor' => $color,
                'MACDHeight' => number_format($macd, 2, '.', ''),
                'ema3SlopeValue' => number_format($ema3Slope, 2, '.', ''),
                'ema5SlopeValue' => number_format($ema5Slope, 2, '.', ''),
                'ema3slopeDirection' => $ema3SlopeDirection,
                'ema5slopeDirection' => $ema5SlopeDirection,
				'ema3Position' => $ema3Position,
				'ema5Position' => $ema5Position,
				'isBongton' => '',
                'isPreviousBongton' => '',
                'isPreviousBongtonBack2' => '',
				'PreviousSlopeDirection' => '',
                'emaAbove' => $emaAbove,
                'CutPointType' => $emaCross,
                'emaConflict' => $emaConflict,
                'ema3Position' => $ema3Position,
                'lastTurnID' => 0,
				'TurnType' => $turnPointType,
                'distance' => 0,
                'PreviousTurnType' => 'N',
				'PreviousTurnTypeBack2' => 'N',
				'PreviousTurnTypeBack3' => 'N',
				'PreviousTurnTypeBack4' => 'N',
                'lastTurnTypeCandleID' => '-1',
                'exTra1'  => '',
                'adxDirection'  => '',
				'adxDirectionCount'  => 0,
                'TurnMode999'  => '',

            ]);
        } 

		for ($i=1;$i<=count($result)-1;$i++) {
			if (
				$result[$i]['PreviousTurnType'] === 'TurnUp'  ||
				$result[$i]['PreviousTurnType'] === 'TurnDown'  
				) {
                $lastTurnTypeCandleID = $result[$i-1]['candleID'] ;
				$result[$i]['lastTurnTypeCandleID'] = $lastTurnTypeCandleID;
				
				echo $lastTurnTypeCandleID . '@@' ;
			} else {
              //$result[$i]['OnTurnMode'] = $result[$i-1]['OnTurnMode'];
			}		   
		} 

		$result = $this->Final_AdvanceIndy($result) ;

		 
        
        return $result;
    }
    
    private function calculateMACD($candle) {
        return floatval($candle['ema3']) - floatval($candle['ema5']);
    }
    
    private function calculateEMA3Slope($current, $previous) {
        if (!$previous) return 0;
        return floatval($current['ema3']) - floatval($previous['ema3']);
    }
    
    private function calculateEMA5Slope($current, $previous) {
        if (!$previous) return 0;
        return floatval($current['ema5']) - floatval($previous['ema5']);
    }
    
    private function determineEMAPosition($candle) {
        
		$diff = floatval($candle['ema3']) - floatval($candle['ema5']) ;
		//echo  $candle['ema3'] . " vs " . $candle['ema5'] . '-->' . $diff  . '=';
		if ($diff > 0 ) {
            //echo "ema3Above" . '<br>';
			return '3' ;
		}  else {
            //echo "ema5Above" . '<br>';
			return '5' ;
		}
		if ($diff === 0 ) {
			return 'P';
		}

        return floatval($candle['ema3']) > floatval($candle['ema5']) ? '3' : '5';
    }
    
    private function detectEMACross($current, $previous) {
        if (!$previous) return 'N';
        
        $currentEma3 = floatval($current['ema3']);
        $currentEma5 = floatval($current['ema5']);
        $previousEma3 = floatval($previous['ema3']);
        $previousEma5 = floatval($previous['ema5']);
        
        if ($previousEma3 <= $previousEma5 && $currentEma3 > $currentEma5) {
            return '5->3';
        }
        if ($previousEma3 >= $previousEma5 && $currentEma3 < $currentEma5) {
            return '3->5';
        }
        
        return 'N';
    }
    
    private function detectTurnPoint($data, $currentIndex) {
        
		if ($currentIndex < 2 || $currentIndex >= count($data) - 1) return 'N';
        
        $prev2 = floatval($data[$currentIndex - 2]['ema3']);
        $prev1 = floatval($data[$currentIndex - 1]['ema3']);
        $current = floatval($data[$currentIndex]['ema3']);
        $next1 = floatval($data[$currentIndex + 1]['ema3']);
        /*
        if ($prev2 > $prev1 && $prev1 > $current && $current < $next1) {
            return 'TurnUp';
        }
        if ($prev2 < $prev1 && $prev1 < $current && $current > $next1) {
            return 'TurnDown';
        }
		*/
		if ($current > $prev1 && $prev1 < $prev2) {
            return 'TurnUp';
        }
		if ($current < $prev1 && $prev1 > $prev2) {
            return 'TurnDown';
        }


        
        return 'N';
    }
    
    private function determineColor($current, $previous) {
        if (!$previous) return 'gray';
        
        $currentPrice = floatval($current['close']);
        $previousPrice = floatval($previous['close']);
        $close = floatval($current['close']);
		$open = floatval($current['open']);

        
        if ($close > $open) {
            return 'Green';
        } elseif ($close < $open) {
            return 'Red';
        }
        return 'Gray';
    }
    
    private function determineEMASlopeDirection($slope) {
        $threshold = 0.001; // Small threshold to determine parallel movement
        if (abs($slope) < $threshold) {
            return 'P';
        }
        return $slope > 0 ? 'Up' : 'Down';
    }
    
    private function detectEMAConflict($candle, $color) {
        $ema3 = floatval($candle['ema3']);
        $ema5 = floatval($candle['ema5']);
        
        // Conflict conditions
        if ($ema3 > $ema5 && $color === 'Red') {
            //return true;
			return '35R';

        }
        if ($ema3 < $ema5 && $color === 'Green') {
            return '53G';
        }
        return 'N';
    }
    
    private function detectEMA3CandlePosition($candle) {
        $ema3 = floatval($candle['ema3']);
        $high = floatval($candle['high']);
        $low = floatval($candle['low']);
        $open = floatval($candle['open']);
        $close = floatval($candle['close']);
        
        if ($ema3 > $high) {
            return 'aboveHigh';
        } elseif ($ema3 <= $high && $ema3 > max($open, $close)) {
            return 'betweenHighOpen';
        } elseif ($ema3 <= max($open, $close) && $ema3 >= min($open, $close)) {
            return 'betweenOpenClose';
        } elseif ($ema3 < min($open, $close) && $ema3 > $low) {
            return 'betweenCloseLow';
        } else {
            return 'belowLow';
        }
    }

	private function detectEMA5CandlePosition($candle) {
        $ema5 = floatval($candle['ema5']);
        $high = floatval($candle['high']);
        $low = floatval($candle['low']);
        $open = floatval($candle['open']);
        $close = floatval($candle['close']);
        
        if ($ema5 > $high) {
            return 'aboveHigh';
        } elseif ($ema5 <= $high && $ema5 > max($open, $close)) {
            return 'betweenHighOpen';
        } elseif ($ema5 <= max($open, $close) && $ema5 >= min($open, $close)) {
            return 'betweenOpenClose';
        } elseif ($ema5 < min($open, $close) && $ema5 > $low) {
            return 'betweenCloseLow';
        } else {
            return 'belowLow';
        }
    } 

	private function Final_AdvanceIndy($result2) { 

          $lastTurnID = 0;  
		  for ($i=2;$i<=count($result2)-1;$i++) {
			  $curIndex = $i;
              $previousIndex = $i-1 ;
			  $previousIndexBack2 = $i-2 ;
			  if (
				 $result2[$previousIndex]['ema3'] < $result2[$curIndex]['ema3'] &&
                 $result2[$previousIndex]['ema3'] < $result2[$previousIndexBack2]['ema3'] 
				 ) {
                 $result2[$curIndex]['PreviousTurnType'] = 'TurnUp' ;
				 $result2[$curIndex-1]['TurnType'] = 'TurnUp' ;
				 $lastTurnID = $result2[$curIndex]['candleID'] ;
				 //$result2[$curIndex]['lastTurnID'] = $lastTurnID;
			  }
			  if (
				 $result2[$previousIndex]['ema3'] > $result2[$curIndex]['ema3'] &&
                 $result2[$previousIndex]['ema3'] > $result2[$previousIndexBack2]['ema3'] 
				 ) {
                 $result2[$curIndex]['PreviousTurnType'] = 'TurnDown' ;
				 $result2[$curIndex-1]['TurnType'] = 'TurnDown' ;
				 $lastTurnID = $result2[$curIndex]['candleID'] ;
				 
			  } 
			  $result2[$curIndex]['lastTurnID'] = $lastTurnID;			   
		  }
		  

          //ปรับค่า
		  for ($i=0;$i<=count($result2)-1;$i++) {
                
               $pip = $result2[$i]['open'] - $result2[$i]['close'];
               $pip = number_format($pip , 2) ;
			   $previousColor = null ;$previousColorBack2 = null;
			   $previousColorBack3 = null ;$previousColorBack4 = null; 

			   //$previousTurnType = null ;$previousTurnTypeBack2 = null;
			  // $previousTurnTypeBack3 = null ;$previousTurnTypeBack4 = null; 

			   $macdconverValue = 0.0 ;
			   $MACDConvergence = '';

			   if ($i >= 1) {
				   $previousColor = $result2[$i-1]['thisColor'] ;
				   $previousTurnType = $result2[$i-1]['PreviousTurnType'] ;
				   $macdconverValue = abs($result2[$i]['MACDHeight']) - abs($result2[$i-1]['MACDHeight']);
				   if ($macdconverValue < 0) {
					   $MACDConvergence ='Conver';
				   }
				   if ($macdconverValue > 0) {
					   $MACDConvergence ='Diver';
				   }
				   if ($macdconverValue == 0) {
					   $MACDConvergence ='P';
				   }

			   }
			   if ($i >= 2) {
				   $previousColorBack2 = $result2[$i-2]['thisColor'] ;
				   //$previousTurnTypeBack2 = $result2[$i-1]['PreviousTurnType'] ;
			   }
			   if ($i >= 3) {
				   $previousColorBack3 = $result2[$i-3]['thisColor'] ;
				   //$previousTurnTypeBack3 = $result2[$i-1]['PreviousTurnType'] ;
			   }
			   if ($i >= 4) {
				   $previousColorBack4 = $result2[$i-4]['thisColor'] ;
				   //$previousTurnTypeBack4 = $result2[$i-1]['PreviousTurnType'] ;
			   }
               $result2[$i]['pip'] = $pip ;
			   $result2[$i]['previousColor'] = $previousColor;
			   $result2[$i]['previousColorBack2'] = $previousColorBack2;
			   $result2[$i]['previousColorBack3'] = $previousColorBack3;
			   $result2[$i]['previousColorBack4'] = $previousColorBack4;

			   
			   $result2[$i]['macdconverValue'] = $macdconverValue ; 			   
			   $result2[$i]['MACDConvergence'] = $MACDConvergence ; 

			   $result2[$i]['timefrom_unix'] =  date('H:i',$result2[$i]['timestamp']); 

			   if ($result2[$i]['TurnType'] === 'TurnUp' || 
				   $result2[$i]['TurnType'] === 'TurnDown' ) {
				    $result2[$i]['lastTurnID'] = $result2[$i]['candleID'] ;
			   } else {
				   if ($i-1 > 0) {				   
                     $result2[$i]['lastTurnID'] = $result2[$i-1]['lastTurnID'];
				   }
			   }			  
		  }
		  for ($i=2;$i<=count($result2)-1;$i++) {
			  $result2[$i]['PreviousTurnTypeBack2'] = $result2[$i-1]['PreviousTurnType'] ; 
			  $result2[$i]['PreviousTurnTypeBack3'] = $result2[$i-2]['PreviousTurnType'] ; 
			  if ($i > 2) {
                $result2[$i]['PreviousTurnTypeBack4'] = $result2[$i-3]['PreviousTurnType'] ; 
			  }
		  }
		  for ($i=1;$i<=count($result2)-1;$i++) {
			  $distance= ($result2[$i]['candleID'] - $result2[$i]['lastTurnID'])/60 ; 
			  $result2[$i]['distance'] = $distance ; 
		  } 

		  for ($i=2;$i<=count($result2)-1;$i++) {
			  $candleCode = $result2[$i]['emaAbove'].'-' . $result2[$i]['thisColor'].'-'.
              $result2[$i]['emaConflict'].'-' . $result2[$i]['MACDConvergence'].'-' ;
			  $candleCode .= 'dis'.$result2[$i]['distance'].'-' ;
			  $candleCode .= 'cut'.$result2[$i]['CutPointType'].'-' ;
			  //$candleCode .= $result2[$i]['candleWick']['candleType'].'-' ;
			  $result2[$i]['CandleCode'] = $candleCode;
		  } 

		 for ($i=1;$i<=count($result2)-1;$i++) {
			$previousADX = floatval($result2[$i-1]['adx']) ;
			$ADX = floatval($result2[$i]['adx']) ;
			if ($ADX > $previousADX) {
			   $result2[$i]['adxDirection'] = 'Up';
			} else {
			   $result2[$i]['adxDirection'] = 'Down';
			}		   
		  }

		  for ($i=1;$i<=count($result2)-1;$i++) {
			 
               if ($result2[$i]['PreviousTurnType'] ==='' || $i==1) {				  
			   }
             
			   if ($result2[$i]['PreviousTurnType'] ==='TurnUp') {
			      $result2[$i]['TurnMode999'] = 'TurnUp';
			   }
			   if ($result2[$i]['PreviousTurnType'] ==='TurnDown') {
			      $result2[$i]['TurnMode999'] = 'TurnDown';
			   }
			   if ($result2[$i]['PreviousTurnType'] ==='N') {
			      $result2[$i]['TurnMode999'] = $result2[$i-1]['TurnMode999']  ;
			   } 
               
			   
			   if ($result2[$i]['TurnMode999'] ==='' || $result2[$i]['TurnMode999'] ==='I') {				  
			      if ($result2[$i]['emaAbove'] ==='3') {				  
				    $result2[$i]['TurnMode999'] = 'TurnUp';
				  } else {
                    $result2[$i]['TurnMode999'] = 'TurnDown';
				  }
			   }
		  } 

		  return $result2;


    } // end function





	/*
	// แบบที่ 1: ใช้ Ternary Operator
const getPreviousColors1 = (data, currentId) => {
  const idx = data.findIndex(item => item.id === currentId);
  return {
    previousColor: idx > 0 ? data[idx - 1].color : null,
    previousColorBack2: idx > 1 ? data[idx - 2].color : null,
    previousColorBack3: idx > 2 ? data[idx - 3].color : null,
    previousColorBack4: idx > 3 ? data[idx - 4].color : null
  };
};

// แบบที่ 2: ใช้ Optional Chaining และ Nullish Coalescing
const getPreviousColors2 = (data, currentId) => {
  const idx = data.findIndex(item => item.id === currentId);
  return {
    previousColor: data[idx - 1]?.color ?? null,
    previousColorBack2: data[idx - 2]?.color ?? null,
    previousColorBack3: data[idx - 3]?.color ?? null,
    previousColorBack4: data[idx - 4]?.color ?? null
  };
};

// แบบที่ 3: ใช้ Array Methods (map และ slice)
const getPreviousColors3 = (data, currentId) => {
  const idx = data.findIndex(item => item.id === currentId);
  const prevColors = idx > 0 
    ? data.slice(Math.max(0, idx - 4), idx).map(item => item.color).reverse()
    : [];
    
  return {
    previousColor: prevColors[0] ?? null,
    previousColorBack2: prevColors[1] ?? null,
    previousColorBack3: prevColors[2] ?? null,
    previousColorBack4: prevColors[3] ?? null
  };
};

// ตัวอย่างข้อมูล
const data = [
  { id: 1, color: 'Red' },
  { id: 2, color: 'Green' },
  { id: 3, color: 'gray' },
  { id: 4, color: 'blue' },
  { id: 5, color: 'yellow' },
  { id: 6, color: 'black' }
];

// ทดสอบการใช้งาน
console.log(getPreviousColors1(data, 6));
console.log(getPreviousColors2(data, 6));
console.log(getPreviousColors3(data, 6));
	
	*/
} // end Class


// Example usage:
$sampleData = [
    [
        "candleID" => 1739085540,
        "timeframe" => "1m",
        "id" => "1",
        "timestamp" => "2025-02-09T07:19:00.000Z",
        "timefrom_unix" => "1739085540",
        "pip" => "177.00",
        "ema3" => "1642.97",
        "ema5" => "1642.97",
        "BB" => 0,
        "rsi" => 0,
        "atr" => "1.77",
        "high" => "1663.88",
        "low" => "1659.78",
        "open" => "1663.88",
        "close" => "1660.18"
    ]
    // ... more data elements
];
/*
$analyzer = new AdvancedIndicators();
$enrichedData = $analyzer->calculateAdvancedIndicators($sampleData);
echo json_encode($enrichedData, JSON_PRETTY_PRINT);
*/


?>