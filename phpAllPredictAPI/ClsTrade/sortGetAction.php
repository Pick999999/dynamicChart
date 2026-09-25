<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
เป็น function สำหรับหา action ต่อจาก clsTradeV2 โดย ทำการเรียงลำดับ 
Case ที่ซับซ้อน -->Case ที่กว้างๆ และจะ return ค่า Action กลับไปเลย

*/


function getActionFromIDVerObject_Sorted($row,$macdThershold,$lastMacdHeight) { 
    
 
	if (

		$row['PreviousTurnType'] =='TurnDown' &&
		$row['PreviousTurnTypeBack2'] =='TurnUp' 
		) {
		//$actionCode = 'Code-000';
		//list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);
		//$thisAction = 'Idle';
		//return  array($thisAction,$actionCode,'CaseNo-000');
	}

	if (

		$row['PreviousTurnType'] =='TurnUp' &&
		$row['PreviousTurnTypeBack2'] =='TurnDown' 
		) {
		$actionCode = 'Code-000';
		//list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);
		//$thisAction = 'Idle';
		//return  array($thisAction,$actionCode,'CaseNo-000');
	}

// Step 6-24-3
// Number of conditions: 7
/* Case No =1 */
	if ($row['PreviousTurnType'] =='N' && 
		$row['PreviousTurnTypeBack2'] =='N' &&
		$row['PreviousTurnTypeBack3'] =='TurnDown' &&
		$row['PreviousTurnTypeBack4'] =='TurnUp' &&
		(abs($row['ema3SlopeValue']) < 5) &&
		$row['ema3slopeDirection'] =='Down' && 
		$row['emaConflict'] =='53G') {
		$actionCode = 'Code6-24-3';
		list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);
		return  array($thisAction,$actionCode,'CaseNo-1');
	}


// Step 6-26-8
// Number of conditions: 7
/* Case No =2 */
	if ($row['PreviousTurnType'] =='TurnDown' && 
		$row['PreviousTurnTypeBack2'] =='N' &&
		$row['PreviousTurnTypeBack3'] =='N' &&
		$row['PreviousTurnTypeBack4'] =='N' &&
		$row['ema3slopeDirection'] =='Down' && 
		$row['emaConflict'] =='35R' &&
		$row['MACDConvergence'] =='Conver') {

		$actionCode = 'Code6-26-8';
		list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);		 
		return  array($thisAction,$actionCode,'CaseNo-2');
	}

// Step 1-1-4
// Number of conditions: 6
/* Case No =3 */
if (
    $row['ema3slopeDirection'] =='Down' && 
    $row['CutPointType'] =='N' && 
    $row['PreviousTurnType'] =='' &&
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['PreviousTurnTypeBack3'] =='TurnUp' &&
    $row['PreviousTurnTypeBack3'] =='N') {

	$actionCode = 'Code1-1-4';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-3');
}

 
// Step 1-1-8
// Number of conditions: 6
/* Case No =5 */
if (
    $row['ema3slopeDirection'] =='Down' && 
    $row['CutPointType'] =='3->5' && 
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown' &&
    $row['MACDHeight'] < 8) {
	$actionCode = 'Code1-1-3';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	$thisAction = 'PUT';
	return  array($thisAction,$actionCode,'CaseNo-5');

     
}

// Step 6-5-3
// Number of conditions: 6
/* Case No =6 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' && 
    $row['PreviousTurnTypeBack3'] =='N' && 
    $row['ema3slopeDirection']=='Up' && 
    $row['emaConflict']=='' && 
    $row['ema3SlopeValue'] < 10) {

	$actionCode = 'Code6-5-3';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-6');
     
}

// Step 6-9-2
// Number of conditions: 6
/* Case No =7 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='53G' && 
    $row['PreviousTurnType'] == '' && 
    $row['PreviousTurnTypeBack2'] == 'TurnUp' && 
    $row['PreviousTurnTypeBack3'] == 'N' && 
    $row['MACDHeight'] < 4) {

	$actionCode = 'Code6-9-2';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-7');
    
}

// Step 6-17-2
// Number of conditions: 6
/* Case No =8 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' && 
    $row['PreviousTurnTypeBack3'] =='N' && 
    $row['PreviousTurnTypeBack4'] =='TurnUp' &&
    ($row['emaConflict']) == '53G' && 
    $row['ema3slopeDirection'] =='Up') {

	$actionCode = 'Code6-17-2';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-8');

     
}

// Step 6-22-2
// Number of conditions: 6
/* Case No =9 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnUp' && 
    $row['PreviousTurnTypeBack4'] =='TurnDown' &&
    $row['emaConflict'] == '35R' && 
    $row['ema3slopeDirection'] =='Up') {

	$actionCode = 'Code6-22-2';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-9');
     
}

// Step 1-1-3
// Number of conditions: 5
/* Case No =10 */
if (
    $row['ema3slopeDirection'] =='Down' && 
    $row['CutPointType'] =='3->5' && 
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown') {

	$actionCode = 'Code1-1-3';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-10');
     
}

// Step 1-1-12
// Number of conditions: 5
/* Case No =11 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown' &&
    $row['PreviousTurnTypeBack4'] =='N' && 
    $row['CutPointType'] == '3->5') {

	$actionCode = 'Code1-1-10';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-11');
     
}

// Step 1-1-13
// Number of conditions: 5
/* Case No =12 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['PreviousTurnTypeBack3'] =='TurnUp' &&
    $row['PreviousTurnTypeBack4'] =='N' && 
    $row['CutPointType'] == 'N') {

	$actionCode = 'Code1-1-13';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-12');
     
}

// Step 6-9-1
// Number of conditions: 5
/* Case No =13 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='53G' && 
    $row['PreviousTurnType'] == 'N' && 
    $row['PreviousTurnTypeBack2'] == 'TurnUp' && 
    $row['PreviousTurnTypeBack3'] == 'N') {

	$actionCode = 'Code6-9-1';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-13');
     
}

// Step 6-22-1
// Number of conditions: 5
/* Case No =14 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['emaConflict'] == '35R' && 
    $row['ema3slopeDirection'] =='Up' &&
    (abs($row['MACDHeight']) > 15)) {

	$actionCode = 'Code6-22-1';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-14');
    
}

// Step 6-22-3
// Number of conditions: 5
/* Case No =15 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnUp' &&
    $row['emaConflict'] == '35R' && 
    $row['ema3slopeDirection'] =='Up') {

	$actionCode = 'Code6-22-3';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-15');
     
}

// Step 6-24-2
// Number of conditions: 5
/* Case No =16 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    abs($row['ema3SlopeValue']) < 8 &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] =='53G') {

	$actionCode = 'Code6-24-2';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-16');
     
}

// Step 6-24-4
// Number of conditions: 5
/* Case No =17 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] =='53G') {

	$actionCode = 'Code6-24-4';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-17');
     
}

// Step 6-24-6
// Number of conditions: 5
/* Case No =18 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack4'] =='TurnDown' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] =='53G') {

	$actionCode = 'Code6-24-6';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-18');
     
}

// Step 6-25-32
// Number of conditions: 5
/* Case No =19 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Up' && 
    (abs($row['MACDHeight']) > 5) &&
    $row['emaConflict'] =='53G') {
	$actionCode = 'Code6-25-32';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-19');
     
}

// Step 6-25-4
// Number of conditions: 5
/* Case No =20 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Up' && 
    (abs($row['MACDHeight']) > 5) &&
    $row['CutPointType'] == '5->3') {

	$actionCode = 'Code6-25-4';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-20');
     
}

// Step 6-26-6
// Number of conditions: 5
/* Case No =21 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['PreviousTurnTypeBack3'] =='N' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] == '35R') {

	$actionCode = 'Code6-26-6';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-21');
    
}

// Step 6-27
// Number of conditions: 5
/* Case No =22 */
if ($row['PreviousTurnType'] == 'N' && 
    $row['PreviousTurnTypeBack2'] == 'N' && 
    $row['emaConflict'] == '35R' && 
    $row['ema3slopeDirection'] =='Up' && 
    $row['PreviousTurnTypeBack4'] == 'TurnUp') {

	$actionCode = 'Code6-27';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-22');
     
}

// Step 1-1-2
// Number of conditions: 3
/* Case No =23 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown') {

	$actionCode = 'Code1-1-2';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-23');
     
}

// Step 1-1-6
// Number of conditions: 4
/* Case No =24 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['PreviousTurnTypeBack3'] =='N' &&
    $row['PreviousTurnTypeBack4'] =='TurnUp') {

	$actionCode = 'Code1-1-6';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	$thisAction ='CALL';
	return  array($thisAction,$actionCode,'CaseNo-24');
    
}

// Step 1-1-7
// Number of conditions: 4
/* Case No =25 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown' &&
    $row['CutPointType'] =='5->3') {

	$actionCode = 'Code1-1-7';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-25');
     
}

// Step 1-1-9
// Number of conditions: 4
/* Case No =26 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['PreviousTurnTypeBack3'] =='TurnUp' &&
    $row['PreviousTurnTypeBack4'] =='N') {

	$actionCode = 'Code1-1-9';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-26');
     
}

// Step 1-1-10
// Number of conditions: 4
/* Case No =27 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown' &&
    $row['PreviousTurnTypeBack4'] =='N' &&
    $row['TurnMode999'] =='TurnDown' 
	
	) {

	$actionCode = 'Code1-1-10';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	$thisAction = 'PUT';
	return  array($thisAction,$actionCode,'CaseNo-27');
     
}
 
$macdConver = $row['MACDConvergence'];

// Step 2-2
// Number of conditions: 4
/* Case No =29 */
if ($row['emaConflict'] == '35R' && 
    $macdConver == 'Conver' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['PreviousTurnType']=='TurnDown') {

    $thisAction = 'PUT'; 
    $forecastColor = 'Red';    
    
    $actionCode = '->Code2_2(R)';
    $LockedAction = false;
	return  array($thisAction,$actionCode,'CaseNo-29');
}

// Step 6-5-2A
// Number of conditions: 4
/* Case No =B30 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' && 
    $row['ema3slopeDirection']=='Up' && 

    $row['emaConflict']=='N') {

	$actionCode = 'Code6-5-2A';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-30');
     
}


// Step 6-5-2
// Number of conditions: 4
/* Case No =30 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' && 
    $row['ema3slopeDirection']=='Up' && 
    $row['emaConflict']=='N') {

	$actionCode = 'Code6-5-2';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-30');
     
}

// Step 6-7-2
// Number of conditions: 4
/* Case No =31 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='35R' &&
    $row['PreviousTurnTypeBack2'] == 'TurnUp' &&
    $row['CutPointType'] == '5->3') {

	$actionCode = 'Code6-7-2';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-31');
     
}

// Step 6-7-3
// Number of conditions: 4
/* Case No =32 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='35R' &&
    $row['PreviousTurnTypeBack2'] == 'TurnUp' &&
    $row['PreviousTurnTypeBack3'] == 'TurnDown') {

	$actionCode = 'Code6-7-3';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
    return  array($thisAction,$actionCode,'CaseNo-32');     
}

// Step 6-8-2
// Number of conditions: 4
/* Case No =33 */
if ($row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] =='35R' &&
    $row['PreviousTurnTypeBack2'] =='TurnDown' && 
    $row['MACDHeight'] <=1) {

	$actionCode = 'Code6-8-2';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-33');
     
}

// Step 6-9-3
// Number of conditions: 4
/* Case No =34 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='53G' &&
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['PreviousTurnTypeBack3'] =='TurnUp') {

	$actionCode = 'Code6-9-3';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-34');
     
}

// Step 6-12
// Number of conditions: 4
/* Case No =35 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' && 
    $row['emaConflict'] =='35R' && 
    abs($row['MACDHeight']*1000*1000) < 10) {

	$actionCode = 'Code6-12';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-35');

}

// Step 6-13
// Number of conditions: 4
/* Case No =36 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' && 
    $row['emaConflict'] =='53G' && 
    $row['ema3slopeDirection']=='Up') {

	$actionCode = 'Code6-13';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
    $thisAction = 'CALL';
	return  array($thisAction,$actionCode,'CaseNo-36');
     
}

// Step 6-16
// Number of conditions: 4
/* Case No =37 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    $row['ema3slopeDirection'] =='Up' &&
    abs($row['ema3SlopeValue']) > 20) {

	$actionCode = 'Code6-16';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-37');
     
}

// Step 6-16-3
// Number of conditions: 4
/* Case No =38 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    $row['ema3slopeDirection'] =='Down' &&
    abs($row['ema3SlopeValue']) > 20) {

	$actionCode = 'Code6-16-3';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-38');
     
}

// Step 6-17@
// Number of conditions: 4
/* Case No =39 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' && 
    ($row['emaConflict']) == '53G' && 
    $row['ema3slopeDirection'] =='Up') {

	$actionCode = 'Code6-17@';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-39');
     
}

// Step 6-22
// Number of conditions: 4
/* Case No =40 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['emaConflict'] == '35R' && 
    $row['ema3slopeDirection'] =='Up') {

	$actionCode = 'Code6-22';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-40');
    
}

// Step 6-24
// Number of conditions: 4
/* Case No =41 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] =='53G') { 

	$actionCode = 'Code6-24';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-41');
     
}

// Step 6-25-2
// Number of conditions: 4
/* Case No =42 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Up' && 
    (abs($row['MACDHeight']) < 4)) {

	$actionCode = 'Code6-25-2';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-42');

     
}

// Step 6-25-3
// Number of conditions: 4
/* Case No =43 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Up' && 
    (abs($row['MACDHeight']) > 5)) {

	$actionCode = 'Code6-25-3';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-43');
     
}

// Step 6-26-02
// Number of conditions: 4
/* Case No =44-0A */
if ($row['TurnMode999'] === 'TurnUp' && 
    $row['ema3slopeDirection'] === 'Up' ) { 

	$thisAction = 'CALL'; 
    $forecastColor = 'Green';        
    $actionCode = 'CodeNew-6-26-02G';
	return  array($thisAction,$actionCode,'CaseNo-44-0A-'. $row['timefrom_unix'] . '-'.$row['TurnMode999'] );
     
}
if ($row['TurnMode999'] === 'TurnDown' 
) { 

	$thisAction = 'PUT'; 
    $forecastColor = 'Red';        
    $actionCode = 'CodeNew-6-26-02R';
	//return  array($thisAction,$actionCode,'CaseNo-44-0B-'. $row['timefrom_unix'] .'-'.$row['TurnMode999']);
     
}

// Step 6-26-2
// Number of conditions: 4
/* Case No =44 */
/*
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['MACDHeight'] < 0.4) { 

	$actionCode = 'Code6-26-2';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-44');
     
}
*/
// Step 6-26-3
// Number of conditions: 4
/* Case No =45 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['CutPointType'] =='3->5') { 

	$actionCode = 'Code6-26-3';

	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
    $thisAction = 'PUT';
	return  array($thisAction,$actionCode,'CaseNo-45');
    
}

// Step 6-26-4
// Number of conditions: 4
/* Case No =46 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['PreviousTurnTypeBack3'] =='N' &&
    $row['ema3slopeDirection'] =='Down') {

	$actionCode = 'Code6-26-4';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-46');
     
}

// Step 6-26-5
// Number of conditions: 4
/* Case No =47 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] =='35R') {

	$actionCode = 'Code6-26-5';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-47');
     
}

// CodeNew-1-1 (Conflict+MACD)
// Number of conditions: 4
/* Case No =48 */
if (        
    $row['emaConflict'] =='N' &&
    $row['PreviousTurnType'] =='TurnUp' &&
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['previousColor'] =='Green') {
    $thisAction = 'CALL'; 
    $forecastColor = 'Green';        
    $actionCode = '->CodeNew-1-1(G)';
	return  array($thisAction,$actionCode,'CaseNo-48');
}

// Step 2-1
// Number of conditions: 3
/* Case No =49 */
if ($row['emaConflict'] == '35R' && 
    $row['MACDConvergence'] == 'Diver' && 
    $row['ema3slopeDirection'] != 'Up') {
    $thisAction = 'PUT'; 
    $forecastColor = 'Red';       
    $actionCode = '->Code2_1(R)';
	return  array($thisAction,$actionCode,'CaseNo-49');
}

// Step 3-0
// Number of conditions: 3
/* Case No =49-1 */
if ($row['ema3slopeDirection'] === 'Up'	
) {
	$actionCode = 'Code3-A';
	//list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	$thisAction = 'CALL'; 
    $forecastColor = 'Green';       
	return  array($thisAction,$actionCode,'CaseNo-49-1');
     
}
if ($row['ema3slopeDirection'] === 'Down'	
) {
	$actionCode = 'Code3-B';
	$thisAction = 'PUT'; 
    $forecastColor = 'Red';       
	//list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-49-1');
     
}

// Step 3-1
// Number of conditions: 3
/* Case No =50 */
if ($row['PreviousSlopeDirection'] !== $row['ema3slopeDirection'] &&     
    $row['PreviousSlopeDirection'] !=='N') {

	$actionCode = 'Code3-1';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-50');

     
}

// Step 6-7-1
// Number of conditions: 3
/* Case No =51 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='35R' &&
    $row['PreviousTurnTypeBack2'] == 'TurnUp') {

	$actionCode = 'Code6-7-1';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-51');
     
}

// Step 6-9-4
// Number of conditions: 3
/* Case No =52 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='53G' &&
    $row['PreviousTurnType'] == 'TurnUp') {

	$actionCode = 'Code6-9-4-77';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-52');
     
}

// Step 6-10
// Number of conditions: 3
/* Case No =53 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnType'] =='TurnDown' && 
    $row['emaConflict'] =='') { 

	$actionCode = 'Code6-10';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-53');
     
}

// Step 6-11
// Number of conditions: 3
/* Case No =54 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' && 
    $row['emaConflict'] =='N') { 

	$actionCode = 'Code6-11';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-54');
     
}

// Step 6-14
// Number of conditions: 3
/* Case No =55 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' && 
    abs($row['ema3SlopeValue']) < 0.9) {

	$actionCode = 'Code6-14';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-55');
     
}

// Step 6-15
// Number of conditions: 3
/* Case No =56 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' && 
    abs($row['ema3SlopeValue']) < 0.9) {

	$actionCode = 'Code6-15';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-56');
     
}

// Step 6-16
// Number of conditions: 3
/* Case No =57 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' && 
    abs($row['ema3SlopeValue']) > 0.9) { 

	$actionCode = 'Code6-16';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-57');

    
}

// Step 6-17
// Number of conditions: 3
/* Case No =58 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' && 
    ($row['emaConflict']) == '53G') {

	$actionCode = 'Code6-17';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-58');
     
}

// Step 6-18
// Number of conditions: 3
/* Case No =59 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' && 
    ($row['emaConflict']) == '53G') { 

	$actionCode = 'Code6-18';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-59');
     
}

// Step 6-19
// Number of conditions: 3
/* Case No =60 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    $row['emaConflict'] == '35R') { 

	$actionCode = 'Code6-19';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-60');
     
}

// Step 6-19-2
// Number of conditions: 3
/* Case No =61 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    $row['emaConflict'] == 'N') {

	$actionCode = 'Code6-19-2@';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-61');
     
}

// Step 6-20-2
// Number of conditions: 3
/* Case No =62 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['MACDHeight'] < 4) {

	$actionCode = 'Code6-20-2';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-62');
     
}

// Step 6-21
// Number of conditions: 3
/* Case No =63 */
if ($row['PreviousTurnType'] =='N' && 
    $row['PreviousTurnTypeBack2'] =='TurnDown' &&
    $row['emaConflict'] == '53G') {

	$actionCode = 'Code6-21';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-63');
     
}

// Step 6-23
// Number of conditions: 3
/* Case No =64 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    abs($row['ema3SlopeValue']) < 0.8) {

	$actionCode = 'Code6-5';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-64');
     
}

// Step 6-25
// Number of conditions: 3
/* Case No =65 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Up') {

	$actionCode = 'Code6-25';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-65');
     
}

// Step 6-26
// Number of conditions: 3
/* Case No =66 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='N' &&
    $row['ema3slopeDirection'] =='Down') {

	$actionCode = 'Code6-26';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-66');
    
}

// Step 6-26-7
// Number of conditions: 3
/* Case No =67 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp' &&
    $row['PreviousTurnTypeBack3'] =='TurnDown') { 

	$actionCode = 'Code6-26-7';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-67');

     
}

// CodeNew-2-1
// Number of conditions: 3
/* Case No =68 */
if (        
    $row['emaConflict'] =='53G' &&
    $row['PreviousTurnType'] =='N' &&
    $row['emaAbove'] =='5') {
    $thisAction = 'PUT'; 
    $forecastColor = 'Red';        
    $actionCode = '->CodeNew-2-1(R)';
	return  array($thisAction,$actionCode,'CaseNo-68');
}

// Step 6-1
// Number of conditions: 2
/* Case No =69 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['MACDConvergence'] =='Diver') {

	$actionCode = 'Code6-1';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-69');
     
}

// Step 6-2
// Number of conditions: 2
/* Case No =70 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['emaConflict'] =='35R') {

	$actionCode = 'Code6-2';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-70');
     
}

// Step 6-3
// Number of conditions: 2
/* Case No =71 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['emaConflict'] =='N') {

	$actionCode = 'Code6-3';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-71');
     
}

// Step 6-4
// Number of conditions: 2
/* Case No =72 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['emaConflict'] =='53G') {

	$actionCode = 'Code6-4';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-72');
     
}

// Step 6-6
// Number of conditions: 2
/* Case No =73 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['emaConflict'] !='35R') {

	$actionCode = 'Code6-6';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-73');

}

// Step 6-7
// Number of conditions: 2
/* Case No =74 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='35R') {

	$actionCode = 'Code6-7';
	list($thisAction,$forecastColor,$actionCode) = getSamection($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-74');
     
}

// Step 6-8
// Number of conditions: 2
/* Case No =75 */
if ($row['ema3slopeDirection'] =='Down' && 
    $row['emaConflict'] =='35R') {

	$actionCode = 'Code6-8';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-75');
     
}

// Step 6-9
// Number of conditions: 2
/* Case No =76 */
if ($row['ema3slopeDirection'] =='Up' && 
    $row['emaConflict'] =='53G') {

	$actionCode = 'Code6-9(77)';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-76');
    
}

// Step 6-20
// Number of conditions: 2
/* Case No =77 */
if ($row['PreviousTurnType'] =='TurnUp' && 
    $row['PreviousTurnTypeBack2'] =='N') {

	$actionCode = 'Code6-20';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-77');
     
}

// Step 6-24-5
// Number of conditions: 2
/* Case No =78 */
if ($row['PreviousTurnType'] =='TurnDown' && 
    $row['PreviousTurnTypeBack2'] =='TurnUp') {

	$actionCode = 'Code6-24-5';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-78');
     
}

// CodeNew-1-1
// Number of conditions: 2
/* Case No =79 */
if (
    $row['PreviousTurnType'] =='N' &&
    $row['PreviousTurnTypeBack2'] =='N') { 

	$actionCode = 'CodeNew-1-1';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode,'CaseNo-79');
     
}

// MACDHeight < macdThershold
// Number of conditions: 2
/* Case No =80 */
if (abs($row['MACDHeight']) < $macdThershold) {
    if ($actionCode !='Code2') {      
        $thisAction = 'Idle'; 
        $remark .= ' ,MACD ..น้อยกว่า  ' .$macdThershold;
		return  array($thisAction,$actionCode,'CaseNo-80');
    }
}

// Step 1-1 (Down)
// Number of conditions: 1
/* Case No =81 */
if ($slopeDirection=='Down') {
    $thisAction = 'PUT'; 
    $forecastColor = 'Red';    
    $ActionClass = 'bgRed';
    $actionCode = 'Code1-1(R)';
	return  array($thisAction,$actionCode,'CaseNo-81');
}

// Step 1-1 (Up)
// Number of conditions: 1
/* Case No =82 */
if ($slopeDirection=='Up') {
    $thisAction = 'CALL'; 
    $forecastColor = 'Green';    
    $ActionClass = 'bgGreen';
    $actionCode = 'Code1-1(G)';
	return  array($thisAction,$actionCode,'CaseNo-82');
}

// SlopeDirection == 'P'
// Number of conditions: 1
/* Case No =83 */
if ($slopeDirection=='P') {
    $thisAction = 'Idle'; 
    $remark = ' Slope ขนาน ';
}

// thisColor == 'Equal'
// Number of conditions: 1
/* Case No =84 */
if ($row['thisColor']=='Equal') {
    $thisAction = 'Idle'; 
    $remark .= ' ,Equal ';
	return  array($thisAction,$actionCode,'CaseNo-84');
}

// previousColor === previousColorBack2
// Number of conditions: 1
/* Case No =85 */
if (
    $row["previousColor"] === $row["previousColorBack2"]) {

	$actionCode = 'CodeNew-1-1';
	list($thisAction,$forecastColor,$actionCode) = getSameAction($row,$actionCode);	
	return  array($thisAction,$actionCode);
    
}

// Step 6-5
// Number  Number of conditions: 1
/* Case No =86 */
if ($row['PreviousTurnType'] =='TurnUp') {

	$actionCode = 'Code-6-5';
	list($thisAction,$forecastColor,$actionCode) = getToggleAction($row,$actionCode);	
	return  array($thisAction,$actionCode);
     
}

} // end function check case 






function getToggleAction($row,$actionCode){

		if ( $row['thisColor'] =='Red') {
				$thisAction = 'CALL'; $forecastColor = 'Green';        
				$actionCode = '->' . $actionCode .'(G)';
		} else {
				$thisAction = 'PUT'; $forecastColor = 'Red';        
				$actionCode = '->' . $actionCode .'(R)';
		}			
		return array($thisAction,$forecastColor = 'Green',$actionCode) ;

		 

} // end function getSameAction

function getSameAction($row,$actionCode){

		if ( $row['thisColor'] =='Green') {
				$thisAction = 'CALL'; $forecastColor = 'Green';        
				$actionCode = '->' . $actionCode .'(G)';
		} else {
				$thisAction = 'PUT'; $forecastColor = 'Red';        
				$actionCode = '->' . $actionCode .'(R)';
		}			
		return array($thisAction,$forecastColor = 'Green',$actionCode) ;

		 

} // end function getSameAction

function getResultColor999($jsonAnalyzed,$thisIndex) { 

	     return $jsonAnalyzed[$thisIndex+1]['Color'] ;


} // end function



function CalWinById($jsonAnalyzed,$thisIndex) { 

	     $win= false ; 
		 $macdThershold = 0.2 ;$lastMacdHeight = 0.0 ;
		 $numTrade = 0 ; $numLoss = 0 ; $limitTrade = 10 ;
		 while ($win === false ) {
           $row = $jsonAnalyzed[$thisIndex] ;
           list($thisAction,$actionCode,$CaseNo)= getActionFromIDVerObject_Sorted($row,$macdThershold,$lastMacdHeight);
		   $numTrade++ ;
		   $suggestColor = ($thisAction == 'CALL') ? 'Green' : 'Red';
		   $resultColor = getResultColor999($jsonAnalyzed,$thisIndex) ;
		   if ($suggestColor === $resultColor) {
			   $win = true; break ;
		   } else {
			   $win = false; $numLoss++ ; 
		   }
		   if ($numTrade > $limitTrade) {
			   break;
		   }
		 }

		 $sObj = new stdClass();
		 $sObj->id = $jsonAnalyzed[$thisIndex]['id'];
		 $sObj->timeCandle = $jsonAnalyzed[$thisIndex]['time'];
		 $sObj->ColorCandle = $jsonAnalyzed[$thisIndex]['Color'];
		 $sObj->totalTrade =  $numTrade;
		 $sObj->numLoss  = $numLoss  ;

		 return $sObj ;

} // end function



?>

