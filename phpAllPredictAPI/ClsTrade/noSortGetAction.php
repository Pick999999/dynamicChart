<?php

function getActionFromID_NoSorted($AnalyObj,$macdThershold,$lastMacdHeight) { 
//function getActionFromIDVerObject($AnalyObj,$macdThershold,$lastMacdHeight) { 


   $LockedAction = false ;
   //$slopeValue = $AnalyObj['ema3SlopeValue'] ;
   $slopeDirection = $AnalyObj['ema3slopeDirection'] ;
   $AnalyObj['MACDHeight']= $AnalyObj['MACDHeight']*1000*1000 ;
   $macd = $AnalyObj['MACDHeight'] ;
   
   $pipSize = round(abs($AnalyObj['pip'])/10,2);
   //$delTapip = abs(abs($AnalyObj['pip']/10) - abs($AnalyObj['previousPIP']/10)) ;

   
   /*
   $sql="SELECT a.id ,b.id,b.minuteno, a.ema3,b.ema3, (b.ema3-a.ema3)*1000*1000 as differ
    FROM  $tableName a INNER join $tableName b on b.id-1 = a.id 
    WHERE b.id = ?";
   $params = array($AnalyObj['id']);   
   $rowDifferEMA = pdoRowSet($sql,$params,$this->pdo) ;      

   $slopeValue = $rowDifferEMA['differ']  ;
*/
   
   $slopeValue = $AnalyObj['ema3SlopeValue']  ;
   if ($slopeValue < 0) {
      $slopeDirection = 'Down' ;
   } else {
      $slopeDirection = 'Up' ;
   }
   $slopeDirection = $AnalyObj['ema3slopeDirection'] ;
   
   
   /*


   if ($macd < $lastMacdHeight) {
	  $macdConver = 'Conver';
   } else {
      $macdConver = 'Diver';
   }
   $lastMacdHeight = $macd;

   //$macdConver = $AnalyObj['MACDConvergence'] ;
   $thisAction = ''; $remark = '';
   $forecastColor = ''; 
   $forecastClass = '';
   $winStatus = '';
   //$slopeValue = $AnalyObj['ema3SlopeValue'] ;
   $actionReason = '';

   $sql = "select thisColor  from $tableName where id=? "; 
   $params = array($AnalyObj['id']+1);
   $nextColor =pdogetValue($sql,$params,$pdo) ;

   if ($nextColor=='Green') {
	   $nextColorClass ='bgGreen' ;
   }
   if ($nextColor=='Red') {
	   $nextColorClass ='bgRed' ;
   }
   if ($nextColor=='Equal') {
	   $nextColorClass ='bgGray' ;
   } 
   */
//
// *******************   เริ่ม Case ตรงนี้   ************************
  // Step 1-1
   if ($slopeDirection=='Down') {
      $thisAction = 'PUT'; 
	  $forecastColor = 'Red';
	  $forecastClass = 'bgRed';
	  $ActionClass = 'bgRed';
	  $actionReason = 'Code1-1(R)';
   }
   if ($slopeDirection=='Up') {
      $thisAction = 'CALL'; 
	  $forecastColor = 'Green';
	  $forecastClass = 'bgGreen';
	  $ActionClass = 'bgGreen';
	  $actionReason = 'Code1-1(G)';
   }

   // Step 1-1-2
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown') {
    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-2(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-2(R)';
	 }
   }
   // Step 1-1-3
   if (
	   $AnalyObj['ema3slopeDirection'] =='Down' && 
	   $AnalyObj['CutPointType'] =='3->5' && 
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='N' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown') {

    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-3(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-3(R)';
	 }
   }
   // Step 1-1-4
   if (
	   $AnalyObj['ema3slopeDirection'] =='Down' && 
	   $AnalyObj['CutPointType'] =='N' && 
	   $AnalyObj['PreviousTurnType'] =='' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnUp' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='N') {

    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-4(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-4(R)';
	 }
   }
   // Step 1-1-5
   if (
	   $AnalyObj['ema3slopeDirection'] =='Down' && 
	   $AnalyObj['CutPointType'] =='N' && 
	   $AnalyObj['PreviousTurnType'] =='' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnUp' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='N') {

    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-4(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-4(R)';
	 }
   }
   // Step 1-1-6
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack4'] =='TurnUp' 
	   ) 
  {
    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-6(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-6(R)';
	 }
   }
   // Step 1-1-7
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown' &&
       $AnalyObj['CutPointType'] =='5->3' 
	   ) {
    
      if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-7(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-7(R)';
	 }
   }

   // Step 1-1-8
   if (
	   $AnalyObj['ema3slopeDirection'] =='Down' && 
	   $AnalyObj['CutPointType'] =='3->5' && 
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='N' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown' &&
       $AnalyObj['MACDHeight'] < 8 
   
   ) {

    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-3(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-3(R)';
	 }
   }

 
   // Step 1-1-9
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnUp' &&
       $AnalyObj['PreviousTurnTypeBack4'] =='N' 
       
	   ) {
    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-9(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-9(R)';
	 }
   }

   // Step 1-1-10
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='N' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown' &&
	   $AnalyObj['PreviousTurnTypeBack4'] =='N' 
	  ) 
  {
    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-10(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-10(R)';
	 }
   }
   // Step 1-1-11
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='N' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='N' &&
	   $AnalyObj['PreviousTurnTypeBack4'] =='TurnUp' 
	  ) 
  {
    
      if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-11(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-11(R)';
	 }
   }
   // Step 1-1-12
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='N' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown' &&
	   $AnalyObj['PreviousTurnTypeBack4'] =='N'  && 
       $AnalyObj['CutPointType'] == '3->5'
	  ) 
  {
    
      if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-10(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-10(R)';
	 }
   }
   // Step 1-1-13
   if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' &&
	   $AnalyObj['PreviousTurnTypeBack3'] =='TurnUp' &&
	   $AnalyObj['PreviousTurnTypeBack4'] =='N'  && 
       $AnalyObj['CutPointType'] == 'N'
	  ) 
  {
    
      if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code1-1-13(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code1-1-13(R)';
	 }
   }


   if ($slopeDirection=='P') {
     $thisAction = 'Idle'; 
	 $remark = ' Slope ขนาน ';
   }

   if ($AnalyObj['thisColor']=='Equal') {
     $thisAction = 'Idle'; 
	 $remark .= ' ,Equal ';
   }

   if (abs($AnalyObj['MACDHeight']) < $macdThershold) {
      if ($actionReason !='Code2') {      
        $thisAction = 'Idle'; 
	    $remark .= ' ,MACD ..น้อยกว่า  ' .$macdThershold;
	  }
   }
 
    // Step 2-1
   if ($AnalyObj['emaConflict'] == '3-5-R' && $AnalyObj['MACDConvergence'] == 'Diver' && $AnalyObj['ema3slopeDirection'] != 'Up') {
		   $thisAction = 'PUT'; 
		   $forecastColor = 'Red';
		   $forecastClass = 'bgRed';
  	       $ActionClass = 'bgRed'; 
		   $actionReason .= '->Code2_1(R)';
   } 

   // Step 2-2
   if ($AnalyObj['emaConflict'] == '3-5-R' && $macdConver == 'Conver'
      && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['PreviousTurnType']=='TurnDown'
   ) {
		   $thisAction = 'PUT'; 
		   $forecastColor = 'Red';
		   $forecastClass = 'bgRed';
  	       $ActionClass = 'bgRed'; 
		   $actionReason .= '->Code2_2(R)';
		   $LockedAction = false;

   } 

    
 
   // ตรวจสอบการ Sideway
      // Step 3-1
   if ($AnalyObj['PreviousSlopeDirection'] !== $AnalyObj['ema3slopeDirection'] && 
	   $LockedAction == false && $AnalyObj['PreviousSlopeDirection'] !=='N') {

	   // toggle thisColor
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code3-1(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code3-1(R)';
		}
   }

// เพิ่มเติมจาก Step-6 ใน Python 
   // Step 6-1
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['MACDConvergence'] =='Diver' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-1(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-1(R)';
		}
   }
   // Step 6-2
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['emaConflict'] =='3-5-R' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-2(R)';
		}
   }
   // Step 6-3
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['emaConflict'] =='N' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-3(R)';
		}
   }
   // Step 6-4
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['emaConflict'] =='5-3-G' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-4(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-4(R)';
		}
   }
   // Step 6-5

   if ($AnalyObj['PreviousTurnType'] =='TurnUp'  ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-5(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-5(R)';
		}
   }
   // Step 6-5-2

   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && 
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' && 
	   $AnalyObj['ema3slopeDirection']=='Up' && $AnalyObj['emaConflict']=='N'  ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-5-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-5-2(R)';
		}
   }

   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && 
	   $AnalyObj['ema3slopeDirection']=='Up' && $AnalyObj['emaConflict']=='N'  ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-5-2.2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-5-2.2(R)';
		}
		
   }

   // Step 6-5-3
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && 
       $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' && 
	   $AnalyObj['PreviousTurnTypeBack3'] =='N' && 
	   $AnalyObj['ema3slopeDirection']=='Up' && $AnalyObj['emaConflict']=='' && $AnalyObj['ema3SlopeValue'] <10 ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-5-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-5-3(R)';
		}
   }
   // Step 6-6
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['emaConflict'] !='3-5-R' ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-6(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-6(R)';
		}
   }
   // Step 6-7
   if ($AnalyObj['ema3slopeDirection'] =='Up' && $AnalyObj['emaConflict'] =='3-5-R' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-7(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-7(R)';
		}
   }

   // Step 6-7-1
   if ($AnalyObj['ema3slopeDirection'] =='Up' && $AnalyObj['emaConflict'] =='3-5-R'
   && $AnalyObj['PreviousTurnTypeBack2'] == 'TurnUp'
   
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-7-1(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-7-1(R)';
		}
   }

    // Step 6-7-2
   if ($AnalyObj['ema3slopeDirection'] =='Up' && $AnalyObj['emaConflict'] =='3-5-R'
   && $AnalyObj['PreviousTurnTypeBack2'] == 'TurnUp'
   && $AnalyObj['CutPointType'] == '5->3'
   
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-7-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-7-1(R)';
		}
   }

   // Step 6-7-3
   if ($AnalyObj['ema3slopeDirection'] =='Up' && $AnalyObj['emaConflict'] =='3-5-R'
   && $AnalyObj['PreviousTurnTypeBack2'] == 'TurnUp'
   && $AnalyObj['PreviousTurnTypeBack3'] == 'TurnDown'
   
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-7-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-7-3(R)';
		}
   }

   // Step 6-8
   if ($AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['emaConflict'] =='3-5-R' ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-8(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-8(R)';
		}
   }

   // Step 6-8-2
   if ($AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['emaConflict'] =='3-5-R' &&
	   $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' && $AnalyObj['MACDHeight'] <=1
	   
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-8-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-8-2(R)';
		}
   }
   // Step 6-9
   if ($AnalyObj['ema3slopeDirection'] =='Up' && $AnalyObj['emaConflict'] =='5-3-G' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-9(G77)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-9(R77)';
		}
   }
   // Step 6-9-1
   if ($AnalyObj['ema3slopeDirection'] =='Up' && $AnalyObj['emaConflict'] =='5-3-G' 
   && $AnalyObj['PreviousTurnType'] == 'N' 
   && $AnalyObj['PreviousTurnTypeBack2'] == 'TurnUp' 
   && $AnalyObj['PreviousTurnTypeBack3'] == 'N' 

	   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-9-1(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-9-1(R)';
		}
   }

   // Step 6-9-2
   if ($AnalyObj['ema3slopeDirection'] =='Up' 
   && $AnalyObj['emaConflict'] =='5-3-G' 
   && $AnalyObj['PreviousTurnType'] == '' 
   && $AnalyObj['PreviousTurnTypeBack2'] == 'TurnUp' 
   && $AnalyObj['PreviousTurnTypeBack3'] == 'N' 
   && $AnalyObj['MACDHeight'] < 4

	   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-9-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-9-2(R)';
		}
   }

// Step 6-9-3
   if ($AnalyObj['ema3slopeDirection'] =='Up' && $AnalyObj['emaConflict'] =='5-3-G'
   && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['PreviousTurnTypeBack3'] =='TurnUp'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-9-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-9-3(R)';
		}
   }

   // Step 6-9-4
   if ($AnalyObj['ema3slopeDirection'] =='Up' && 
	   $AnalyObj['emaConflict'] =='5-3-G' &&
	   $AnalyObj['PreviousTurnType'] == 'TurnUp'
	   
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-9-4(G77)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-9-4(R77)';
		}
   }

// Step 6-10
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['emaConflict'] =='' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-10(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-10(R)';
		}
   }
   // Step 6-11
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' && $AnalyObj['emaConflict'] =='N' ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-11(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-11(R)';
		}
   }
   // Step 6-12
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' && $AnalyObj['emaConflict'] =='3-5-R' && abs($AnalyObj['MACDHeight']*1000*1000) < 10 ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-12(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-12(R)';
		}
   }
   // Step 6-13
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N' && $AnalyObj['emaConflict'] =='5-3-G' && $AnalyObj['ema3slopeDirection']=='Up') {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-13(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-13(R)';
		}
   }
   // Step 6-14
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' && abs($AnalyObj['ema3SlopeValue']) < 0.9 ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-14(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-14(R)';
		}
   }
   // Step 6-15
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' && abs($AnalyObj['ema3SlopeValue']) < 0.9 ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-15(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-15(R)';
		}
   }
   // Step 6-16
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' && abs($AnalyObj['ema3SlopeValue']) > 0.9 ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-16(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-16(R)';
		}
   }

   // Step 6-16-2
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp'
   && $AnalyObj['ema3slopeDirection'] =='Up'
   && abs($AnalyObj['ema3SlopeValue']) > 20 ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-16(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-16(R)';
		}
   }
   // Step 6-16-3
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp'
   && $AnalyObj['ema3slopeDirection'] =='Down'
   && abs($AnalyObj['ema3SlopeValue']) > 20 ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-16(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-16(R)';
		}
   }

    // Step 6-17
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' && ($AnalyObj['emaConflict']) == '5-3-G' ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-17(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-17(R)';
		}
   }
    // Step 6-17@
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' && ($AnalyObj['emaConflict']) == '5-3-G' && $AnalyObj['ema3slopeDirection'] =='Up') {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-@17(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-@17(R)';
		}
   }
   // Step 6-17-2
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' 
	   && $AnalyObj['PreviousTurnTypeBack3'] =='N' 
       && $AnalyObj['PreviousTurnTypeBack4'] =='TurnUp' 
	   && ($AnalyObj['emaConflict']) == '5-3-G' && $AnalyObj['ema3slopeDirection'] =='Up') {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-17-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-17-2(R)';
		}
   }
    // Step 6-18
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N' && ($AnalyObj['emaConflict']) == '5-3-G' ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-18(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-18(R)';
		}
   }
   // Step 6-19
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' 
    && $AnalyObj['emaConflict'] == '3-5-R') {
	   //&& abs($AnalyObj['ema3SlopeValue']) < 0.9 ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-19(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-19(R)';
		}
   }
   // Step 6-19-2
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp' 
    && $AnalyObj['emaConflict'] == 'N') {
	   //&& abs($AnalyObj['ema3SlopeValue']) < 0.9 ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-19-2@(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-19-2@(R)';
		}
   }
// Step 6-20
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N'  ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-20(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-20(R)';
		}
   }

// Step 6-20-2
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N' &&
	   $AnalyObj['MACDHeight'] < 4
	   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-20-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-20-2(R)';
		}
   }

// Step 6-21
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown'
   && $AnalyObj['emaConflict'] == '5-3-G'
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-21(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-21(R)';
		}
   }
// Step 6-22
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['emaConflict'] == '3-5-R' && $AnalyObj['ema3slopeDirection'] =='Up'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-22(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-22(R)';
		}
   }

   // Step 6-22-1
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['emaConflict'] == '3-5-R' && $AnalyObj['ema3slopeDirection'] =='Up'
   && (abs($AnalyObj['MACDHeight']) > 15)
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-22-1(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-22-1(R)';
		}
   }

   // Step 6-22-2
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['PreviousTurnTypeBack3'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack4'] =='TurnDown'

   && $AnalyObj['emaConflict'] == '3-5-R' && $AnalyObj['ema3slopeDirection'] =='Up'

   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-22-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-22-2(R)';
		}
   }
   // Step 6-22-3
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['PreviousTurnTypeBack3'] =='TurnUp'
   && $AnalyObj['emaConflict'] == '3-5-R' && $AnalyObj['ema3slopeDirection'] =='Up'
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-22-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-22-3(R)';
		}
   }

   // Step 6-23
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp'
   && abs($AnalyObj['ema3SlopeValue']) < 0.8
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-5(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-5(R)';
		}  
   }
   // Step 6-24
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['emaConflict'] =='5-3-G'
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-24(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-24(R)';
		}
   }

   // Step 6-24-2
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && abs($AnalyObj['ema3SlopeValue']) < 8
   && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['emaConflict'] =='5-3-G'

   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-24-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-24-2(R)';
		}
   }
   // Step 6-24-3
   if ($AnalyObj['PreviousTurnType'] =='N' 
   && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown'
   && $AnalyObj['PreviousTurnTypeBack4'] =='TurnUp'
   && (abs($AnalyObj['ema3SlopeValue']) < 5)
   && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['emaConflict'] =='5-3-G'

   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-24-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-24-2(R)';
		}
   }

   // Step 6-24-4
   if ($AnalyObj['PreviousTurnType'] =='N' 
   && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown'
   && $AnalyObj['ema3slopeDirection'] =='Down' 
   && $AnalyObj['emaConflict'] =='5-3-G'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-24-4(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-24-4(R)';
		}
   }

   // Step 6-24-5
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' 
   && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp'
   
   
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-24-5(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-24-5(R)';
		}
   } 

   // Step 6-24-6
   if ($AnalyObj['PreviousTurnType'] =='N' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['PreviousTurnTypeBack4'] =='TurnDown'
   && $AnalyObj['ema3slopeDirection'] =='Down' 
   && $AnalyObj['emaConflict'] =='5-3-G'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-24-6(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-24-6(R)';
		}
   }



   // Step 6-25
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Up' 
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-25(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-25(R)';
		}
   }
   // Step 6-25-2
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Up' && (abs($AnalyObj['MACDHeight']) < 4)
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-25-2(G)-'. abs($AnalyObj['MACDHeight']);
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-25-2(R)-'.abs($AnalyObj['MACDHeight']);
		}
   }

   // Step 6-25-3
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Up' && (abs($AnalyObj['MACDHeight']) > 5)
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {	
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-25-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-25-3(R)';
		}
   }
   // Step 6-25-32
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Up' && (abs($AnalyObj['MACDHeight']) > 5) 
   && $AnalyObj['emaConflict'] =='5-3-G'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {	
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-25-32(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-25-32(R)';
		}
   }


   // Step 6-25-4
   if ($AnalyObj['PreviousTurnType'] =='TurnUp' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Up' && (abs($AnalyObj['MACDHeight']) > 5)
   && $AnalyObj['CutPointType'] == '5->3'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {	
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-25-4(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-25-4(R)';
		}
   }
   // Step 6-26
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' 
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26(R)';
		}
   }
   // Step 6-26-2
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['MACDHeight'] < 4
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26-2(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26-2(R)';
		}
   }

   // Step 6-26-3
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['CutPointType'] =='3->5'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26-3(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26-3(R)';
		}
   }

   // Step 6-26-4
   if ($AnalyObj['PreviousTurnType'] =='N' 
   && $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown'
   && $AnalyObj['PreviousTurnTypeBack3'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' 

   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26-4(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26-4(R)';
		}
   }
   // Step 6-26-5
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['emaConflict'] =='3-5-R'
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26-5(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26-5(R)';
		}
   }

   // Step 6-26-6
   if ($AnalyObj['PreviousTurnType'] =='N' 
   && $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown'
   && $AnalyObj['PreviousTurnTypeBack3'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' 
   && $AnalyObj['emaConflict'] == '3-5-R'

   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26-6(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26-6(R)';
		}
   } 

   // Step 6-26-7
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='TurnUp'
   && $AnalyObj['PreviousTurnTypeBack3'] =='TurnDown'
   
   ) {
	   if ( $AnalyObj['thisColor'] =='Red' || $AnalyObj['thisColor'] =='Equal') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26-7(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26-7(R)';
		}
   }

   // Step 6-26-8
   if ($AnalyObj['PreviousTurnType'] =='TurnDown' && $AnalyObj['PreviousTurnTypeBack2'] =='N'
   && $AnalyObj['PreviousTurnTypeBack3'] =='N'
   && $AnalyObj['PreviousTurnTypeBack4'] =='N'
   && $AnalyObj['ema3slopeDirection'] =='Down' && $AnalyObj['emaConflict'] =='3-5-R'
   && $AnalyObj['MACDConvergence'] =='Conver'
   ) {
	   if ( $AnalyObj['thisColor'] =='Red') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-26-8(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-26-8(R)';
		}
   }

   // Step 6-27
   if ($AnalyObj['PreviousTurnType'] == 'N' && 
	  $AnalyObj['PreviousTurnTypeBack2'] == 'N' && 
	  $AnalyObj['emaConflict'] == '3-5-R' && 
	  $AnalyObj['ema3slopeDirection'] =='Up'  && 
	  $AnalyObj['PreviousTurnTypeBack4'] == 'TurnUp'
   ) {
	   if ( $AnalyObj['thisColor'] =='Green') {
				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->Code6-27(G)';
		} else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->Code6-27(R)';
		}
   }
// Case New ตรวจสอบการลงต่อเนื่อง
if (
	    
	   $AnalyObj['PreviousTurnType'] =='N' &&
       $AnalyObj['PreviousTurnTypeBack2'] =='N' ) {
    
      if ( $AnalyObj['thisColor'] =='Green') {

				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->CodeNew-1-1(G)';
	  } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->CodeNew-1-1(R)';
	 }
} // end if

// Case New ตรวจสอบสี Conflict
    if (	    
	   $AnalyObj['emaConflict'] =='53G' &&
       $AnalyObj['PreviousTurnType'] =='N' &&
	   $AnalyObj['emaAbove'] =='5' 
	   ) {
    
			 $thisAction = 'PUT'; 
	         $forecastColor = 'Red';
			 $forecastClass = 'bgRed';
  	         $ActionClass = 'bgRed'; 
			 $actionReason .= '->CodeNew-2-1(R)';

     } // end if

// Case New ตรวจสอบสี Conflict+ MACD
    if (	    
	   $AnalyObj['emaConflict'] =='N' &&
       $AnalyObj['PreviousTurnType'] =='TurnUp' &&
	   $AnalyObj['PreviousTurnTypeBack2'] =='TurnDown' &&
	   $AnalyObj['previousColor'] =='Green'  
	   ) {
    
		 $thisAction = 'CALL'; 
		 $forecastColor = 'Green';
		 $forecastClass = 'bgGreen';
  	     $ActionClass = 'bgGreen'; 
		 $actionReason .= '->CodeNew-1-1(G)';

     } // end if

	 if (
		 $AnalyObj["previousColor"] === $AnalyObj["previousColorBack2"] 
        ) {
		 if ( $AnalyObj['thisColor'] =='Green') {

				 $thisAction = 'CALL'; 
		         $forecastColor = 'Green';
				 $forecastClass = 'bgGreen';
  	             $ActionClass = 'bgGreen'; 
				 $actionReason .= '->CodeNew-1-1(G)';
	     } else {
				 $thisAction = 'PUT'; 
		         $forecastColor = 'Red';
				 $forecastClass = 'bgRed';
  	             $ActionClass = 'bgRed'; 
				 $actionReason .= '->CodeNew-1-1(R)';
	     }


	 }


    

/*
   if (abs($AnalyObj['MACDHeight'] *1000*1000) < 1	) {
	   $thisAction = 'Idle'; 
	   $forecastColor = 'Gray';
	   $forecastClass = 'bgGray';
  	   $ActionClass = 'bgGray'; 
	   $actionReason .= '->Code6-21(E)';

   }

   if (abs($pipSize) <= 0.02	) {
	   $thisAction = 'Idle'; 
	   $forecastColor = 'Gray';
	   $forecastClass = 'bgGray';
  	   $ActionClass = 'bgGray'; 
	   $actionReason .= '->Code6-22(E)'. '=' . $pipSize;

   } 
*/
// *******************   สิ้นสุด Case ตรงนี้   ************************

 // 
   $thisColor = $AnalyObj['thisColor'] ;
   // Step 6-7 ตรวจสอบ แท่งแดง UWick = 0 ; Lwick <=10% ให้ เป็นสีเขียว
/*
   $sql = "select * from RawData where id=?"; 
   $params = array($AnalyObj['id']);
   $rowRawCandle = pdoRowSet($sql,$params,$this->pdo) ;
   $pip= ($rowRawCandle['close'] > $rowRawCandle['open'])*10000;
   if ($rowRawCandle['close'] > $rowRawCandle['open']) { //Green
	   $totalHeight=abs(($rowRawCandle['max'] - $rowRawCandle['min'])*1000*1000);
	   $UHeight = abs(($rowRawCandle['max'] - $rowRawCandle['close'])*1000*1000);
	   $BodyUwick = abs($rowRawCandle['close'] - $rowRawCandle['open'])*1000*1000;
	   $Lwick = abs($rowRawCandle['open'] - $rowRawCandle['min'])*1000*1000;

   } else {
	   $totalHeight=abs(($rowRawCandle['max'] - $rowRawCandle['min'])*1000*1000);
	   $UHeight = abs(($rowRawCandle['max'] - $rowRawCandle['open'])*1000*1000);
	   $BodyUwick = abs($rowRawCandle['open'] - $rowRawCandle['close'])*1000*1000;
	   $Lwick = abs($rowRawCandle['close'] - $rowRawCandle['min'])*1000*1000;
	   $BodywickPercent = round(($BodyUwick/$totalHeight)*100,2) ;

   } 
    
 */


return array($thisAction,$actionReason);

} // end function

?>