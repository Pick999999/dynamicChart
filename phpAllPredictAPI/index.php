<?php
//phpAllPredictAPI/index.php
//
//index.php->clsTradeV3->sortGetAction.php,noSortGetAction.php
date_default_timezone_set('Asia/Bangkok');

    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Origin: *'); 
    ob_start(); 
	/*
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);   
	*/

if (!defined('INDEX_INCLUDED_AS_LIB')) {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {        
      if (isset($data['Mode']) && $data['Mode'] == 'getLab') { 
          main($data); 
      } elseif (isset($data['candles'])) {
          main([
              'rawData' => $data['candles'],
              'assetCode' => isset($data['assetCode']) ? $data['assetCode'] : 'R_10'
          ]);
      }
      return;
    } else {
      main();
	}
}
    

	 

function main($data='') { 


if ($data === '') {
   $candleData0 = getCandleData2();
   $assetCode=  'R_10';
   
} else {
   $candleData0 = $data['rawData'];
   $assetCode  = $data['assetCode'] ;
   /*
   $sFileName =  'RawData/rawData.json';
   $myfile = fopen($sFileName, "w") or die("Unable to open file!");
   $str_json=json_encode($candleData, JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
   //echo '<pre>' . $str_json . '</pre>' ;
   fwrite($myfile, $str_json);
   fclose($myfile);
   */
}


//list($maxLoss0,$maxLoss2,$maxLoss3,$maxLoss4,$maxLoss5,$maxLoss6,$maxLoss7,$maxLoss8,$maxLoss9) = LabLossCon($assetCode,$candleData) ;
 
//$candleData1 = JSON_ENCODE($candleData0);
//$candleData = JSON_DECODE($candleData1,true);

$candleData =  $candleData0;

if (is_array($candleData)) {
    //$analyzer = new CandlestickAnalyzerClaude($candles);
	//echo "ข้อมูล Array ";
} else {
    //echo "ข้อมูลไม่ใช่ array: " . gettype($candleData);
}
//return;


list($ClaudeAnalyzer,$ChatGPTAnalyzer,$DeepSeekAnalyzer,$clsTradeAnalyzer,$clsEMAConflict) = getAllForecastClass($candleData) ;
$tradeArray = array();

 

$startIndex = 20 ; $endIndex = count($candleData) - 1;
$lossConClaude = 0;$lossConChatGpt = 0;
$lossConDeepSeek = 0; $lossConNoSort = 0; $lossConSort = 0;

$maxLoss0 = 0 ;$maxLoss2 = 0 ;
$maxLoss3 = 0 ;$maxLoss4 = 0 ;
$maxLoss5 = 0 ;$maxLoss6 = 0 ;
$maxLoss7 = 0 ;$maxLoss8 = 0 ; $maxLoss9 = 0 ;

$MaxlossConClaude = 0;$MaxlossConChatGpt = 0;
$MaxlossConDeepSeek = 0; $MaxlossConNoSort = 0; $MaxlossConSort = 0;
$tradeNo = 0 ;

for ($i=$startIndex;$i<= $endIndex ;$i++) {   
	$indexSelected = $i;
	$candleDataA =  array_slice($candleData, 0,$i);
	$candleDataB =  array_slice($candleData, 0,$i+1);
	
	// สร้าง Analyzer ใหม่สำหรับแต่ละ iteration ด้วย candleDataA
	list($ClaudeAnalyzerCurrent,$ChatGPTAnalyzerCurrent,$DeepSeekAnalyzerCurrent,$clsTradeAnalyzerCurrent,$clsEMAConflictCurrent) = getAllForecastClass($candleDataA);

	$suggestColorClaude = getSuggestByClaude($ClaudeAnalyzerCurrent,$candleDataA);  
	$suggestColorDeepSeek =  getSuggestByDeepSeek($DeepSeekAnalyzerCurrent,$candleDataA);
	$suggestColorChatGpt = getSuggestByCHATGPT($ChatGPTAnalyzerCurrent,$candleDataA);
	list($suggestColorNoSort,$thisAction)=getSuggestByClassTradeNoSort($clsTradeAnalyzerCurrent,$candleDataA);


	list($suggestColorClassTradeWithSort,$thisActionWithSort)=getSuggestByClassTradeWithSort($clsTradeAnalyzerCurrent,$candleDataA);

	list($timeCandle,$resultColor) = getResultColor($candleDataB) ;

	

	$sObj = new stdClass() ;
	$sObj->asset =  $assetCode;
	$sObj->TotalData = count($candleData);
	$sObj->timeCandle = $candleDataA[count($candleDataA)-1]['time'] ;
	$sObj->StartTime = date('d/m/Y H:i',$candleData[0]['time']);

	//$sObj->EndTime =  date('d/m/Y H:i',$candleData[count($candleData)-1]['time']);
	//echo "Len CandleDataA = " . count($candleDataA) . '<br>';
	$sObj->SuggestTimeCandle =  date('H:i:s',$candleDataA[count($candleDataA)-1]['time']);
	$sObj->TradeNoIndex =  $tradeNo+1; $tradeNo++ ;
	$sObj->SuggestIndex = $indexSelected ;

	//$sObj->suggestColorClaude = $suggestColorClaude;
	//$sObj->suggestColorDeepSeek = $suggestColorDeepSeek;
	//$sObj->suggestColorChatGpt = $suggestColorChatGpt;
	//$sObj->suggestColorClassTradeNoSort = $suggestColorNoSort ;
	//$sObj->suggestColorClassTradeWithSort = $suggestColorClassTradeWithSort ;

	//$sObj->claudeWinStatus =  ($suggestColorClaude == $resultColor) ? '💲Win' : 'Loss';
	$lossConClaude =  ($suggestColorClaude == $resultColor) ?  0 : ($lossConClaude+1) ;
	$sObj->lossConClaude = $lossConClaude ;
    
	//$sObj->DeepSeekWinStatus =  ($suggestColorDeepSeek == $resultColor) ? '💲Win' : 'Loss';
	$lossConDeepSeek =  ($suggestColorDeepSeek == $resultColor) ?  0 :($lossConDeepSeek+1);
	$sObj->lossConDeepSeek = $lossConDeepSeek ;

	//$sObj->ChatGptWinStatus =  ($suggestColorChatGpt == $resultColor) ? '💲Win' : 'Loss';
	$lossConChatGpt =  ($suggestColorChatGpt == $resultColor) ?  0 :($lossConChatGpt+1);
	$sObj->lossConChatGpt = $lossConChatGpt;
	
    
	//$sObj->NoSortWinStatus = ($suggestColorNoSort == $resultColor) ? '💲Win' : 'Loss';
	$lossConNoSort =  ($suggestColorNoSort == $resultColor) ?  0 :($lossConNoSort+1);
	$sObj->lossConNoSort = $lossConNoSort ;
	

	//$sObj->WithSortWinStatus =  ($suggestColorClassTradeWithSort == $resultColor) ? '💲Win' : 'Loss';
	$lossConSort =  ($suggestColorClassTradeWithSort  == $resultColor) ?  0 :($lossConSort+1);
	$sObj->lossConSort = $lossConSort ;

// Claude Object
	$sTmp = new stdClass();
	$sTmp->timeCandle = $candleDataA[count($candleDataA)-1]['time'];
    $sTmp->SuggestColor = $suggestColorClaude ;
    $sTmp->WinStatus = ($suggestColorClaude == $resultColor) ? '💲Win' : 'Loss' ;
	$sTmp->LossCon = $lossConClaude ;
	if ($MaxlossConClaude < $lossConClaude) {
		$MaxlossConClaude = $lossConClaude ;						
	} else {
        $MaxlossConClaudeTime = date('H:i:s',$candleDataA[count($candleDataA)-1]['time']);
	}	
	$sTmp->MaxlossConClaude = $MaxlossConClaude ;
    //$sTmp->MaxlossConClaudeTime = $MaxlossConClaudeTime ;
	
	$sObj->Claude = $sTmp;

// ChatGPT Object
	$sTmp = new stdClass();
	$sTmp->timeCandle = $candleDataA[count($candleDataA)-1]['time'];
    $sTmp->SuggestColor = $suggestColorChatGpt;
    $sTmp->WinStatus = ($suggestColorChatGpt == $resultColor) ? '💲Win' : 'Loss' ;
	$sTmp->LossCon = $lossConChatGpt ;
	if ($MaxlossConChatGpt < $lossConChatGpt) {
		$MaxlossConChatGpt = $lossConChatGpt ;
	}
	$sTmp->MaxlossConChatGpt= $MaxlossConChatGpt;	
	$sObj->ChatGPT  = $sTmp;
	
// DeepSeek Object
	$sTmp = new stdClass();
	$sTmp->timeCandle = $candleDataA[count($candleDataA)-1]['time'];
    $sTmp->SuggestColor = $suggestColorDeepSeek;
    $sTmp->WinStatus = ($suggestColorDeepSeek == $resultColor) ? '💲Win' : 'Loss' ;
	$sTmp->LossCon = $lossConDeepSeek ;
	if ($MaxlossConDeepSeek < $lossConDeepSeek) {
		$MaxlossConDeepSeek = $lossConDeepSeek ;
	}
	$sTmp->MaxlossConDeepSeek= $MaxlossConDeepSeek;	
	$sObj->DeepSeek  = $sTmp;

// NoSort Object
	$sTmp = new stdClass();
	$sTmp->timeCandle = $candleDataA[count($candleDataA)-1]['time'];
    $sTmp->SuggestColor = $suggestColorNoSort;
    $sTmp->WinStatus = ($suggestColorNoSort == $resultColor) ? '💲Win' : 'Loss' ;
	$sTmp->LossCon = $lossConNoSort;
	if ($MaxlossConNoSort < $lossConNoSort) {
		$MaxlossConNoSort = $lossConNoSort ;
	}
	$sTmp->MaxlossConNoSort = $MaxlossConNoSort;	
	$sObj->NoSort  = $sTmp ;

// Sorted Object
	$sTmp = new stdClass();
	$sTmp->timeCandle = $candleDataA[count($candleDataA)-1]['time'];
    $sTmp->SuggestColor = $suggestColorClassTradeWithSort;
    $sTmp->WinStatus = ($suggestColorClassTradeWithSort == $resultColor) ? '💲Win' : 'Loss' ;
	$sTmp->LossCon = $lossConSort;
	if ($MaxlossConSort < $lossConSort) {
		$MaxlossConSort = $lossConSort ;
	}
	$sTmp->MaxlossConSort = $MaxlossConSort;	
	$sObj->WithSort  = $sTmp;
	


    if ($resultColor === 'Green') {
      $sObj->resultColor = '🟢' . $resultColor;
    } else {
	  $sObj->resultColor = '🔴' . $resultColor;
	}
	$tradeArray[] = $sObj ;
	/*
	$lastIndex = count($tradeArray)-1 ;
    $MaxlossConClaude = $tradeArray[$lastIndex]->Claude->MaxlossConClaude ;
	for ($i=0;$i<=count($tradeArray)-1;$i++) {
		if ($tradeArray[$i]->Claude->LossCon == $MaxlossConClaude) {
		 $timeLossConCaude[] = $tradeArray[$i]->SuggestTimeCandle  ;			
		} 	   
	}
	echo '<hr>';
	echo implode(';',$timeLossConCaude) ;
*/

	//$datetime_string = date('Y-m-d H:i:s', $timestamp);
	//$str_json=json_encode($sObj, JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    //echo '<pre>' .$str_json . '</pre>';
 } // end for

$lossConList =  FindMax($tradeArray);

//$str_json =json_encode($lossConList , JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

$All = new stdClass();
$All->lossConListA = $lossConList;
$All->maxLoss0 = $maxLoss0 ;
$All->maxLoss2 = $maxLoss2 ;
$All->maxLoss3 = $maxLoss3 ;
$All->maxLoss4 = $maxLoss4;
$All->maxLoss4 = $maxLoss4;
$All->maxLoss5 = $maxLoss5;
$All->maxLoss6 = $maxLoss6;
$All->maxLoss7 = $maxLoss7;
$All->maxLoss8 = $maxLoss8;
$All->maxLoss9 = $maxLoss9;


    $All->tradeResult = $tradeArray ;

 
	$str_jsonLab=json_encode($All, JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    //echo '<pre>' .$str_jsonLab . '</pre>';
	displayLab($tradeArray) ;

	$result = new stdClass();
	$result->labStJson = $tradeArray ;

	
	$str_json = json_encode($result, JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	//echo '<pre>'. $str_json . '</pre>';
	if (!defined('INDEX_INCLUDED_AS_LIB')) {
		echo $str_json ;
	}
	//SaveLabTrade($tradeArray);
	return $result;

	// เรียกใช้งาน

//$result = findMaxLossStreak($tradeArray);
//displayLossResult($result);

// แสดง JSON (ถ้าต้องการ)
//echo "<br>" . str_repeat("=", 50) . "<br>";
//echo "📋 JSON OUTPUT:<br>";
//echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);



/*
list($suggestColor4,$timeClsTrade,$clsTradeColor,$actionReason,$CaseNo) =  getSuggestByClassTrade($candleData);  
*/

} // end function


function getSuggestByClaude($ClaudeAnalyzer,$candleData) {

// Get all analyses
$completeAnalysis = $ClaudeAnalyzer->getCompleteAnalysis(); 
$prediction = $ClaudeAnalyzer->getNextCandlePrediction();
//print_r($prediction);
//$recommendedIndicators = $analyzer->getRecommendedIndicators();
$greenPercent = $prediction['green'] ;
$RedPercent = $prediction['red'] ;
$suggestColor =   ($greenPercent > $RedPercent) ? 'Green' : 'Red';



return $suggestColor;


} // end function


function getSuggestByDeepSeek($DeepSeekAnalyzer,$candleData) {
	// ใช้ DeepSeekAnalyzer ที่ส่งเข้ามา ไม่ต้องสร้างใหม่
	// $DeepSeekAnalyzer = new AdvancedCandlestickAnalyzer($candleData); // ลบบรรทัดนี้

	// 4. ทำนายแท่งถัดไป
	$prediction = $DeepSeekAnalyzer->predictNextCandle();

	$greenPercent = $prediction['green'] ;
	$RedPercent = $prediction['red'] ;
	$suggestColor =   ($greenPercent > $RedPercent) ? 'Green' : 'Red';

	return $suggestColor;


} // end function


function getSuggestByCHATGPT($ChatGPTAnalyzer ,$candleData) {


//print_r($tradeAnalyzer->getIndicators());
$prediction = $ChatGPTAnalyzer->predictNextCandle();
if ($prediction['green'] > $prediction['red']) {
	$suggestColor = 'Green';
} else {
	$suggestColor = 'Red';
}
return $suggestColor;

echo '<h2> By CHATGPT </h2>';
echo "Probability of Green: " . $prediction['green'] . "<br>";
echo "Probability of Red: " . $prediction['red'] . "<br>";


} // end function


function getSuggestByClassTradeNoSort($clsTradeAnalyzer){ 


         list($thisAction,$actionReason)= $clsTradeAnalyzer->getSuggestColorNoSort() ;
		 $suggestColor = ($thisAction == 'CALL') ? 'Green' : 'Red';
		 
		 
		 return  array($suggestColor,$thisAction);

} // end function

function getSuggestByClassTradeWithSort($clsTradeAnalyzer){ 


         list($thisAction,$actionReason)= $clsTradeAnalyzer->getSuggestColorWithSort() ;
		 $suggestColor = ($thisAction == 'CALL') ? 'Green' : 'Red';
		 
		 
		 return  array($suggestColor,$thisAction);

} // end function


function getCandleData2() {

 
 $sFileName =  'RawData/rawData2.json';
 $st = '';
 $file = fopen($sFileName,"r");
 while(! feof($file))  {
   $st .= fgets($file) ;
 }
 fclose($file); 
 $st = str_replace('epoch','time',$st);

 $candleDataA = JSON_DECODE($st,true);

 
 //echo 'Len=' . count($candleDataA) . '<br>';
 return $candleDataA ;

} // end function

function getResultColor($candleDataB) { 

         $lastIndex = count($candleDataB)-1;
	     $diff =  $candleDataB[$lastIndex]['open'] - $candleDataB[$lastIndex]['close'] ;
		 $timeCandle = date('H:i:s',$candleDataB[$lastIndex]['time']);
		 $resultColor = '???';
		 if ($diff > 0) {
			 $resultColor = 'Red';
		 }
		 if ($diff < 0) {
			 $resultColor = 'Green';
		 }
		 if ($diff === 0) {
			 $resultColor = 'Equal';
		 }

		 return array($timeCandle,$resultColor) ;



} // end function


function findMaxLossStreak($dataArray) {
    if (empty($dataArray)) {
        return ['error' => 'Data array is empty'];
    }
    
    $maxLossData = [
        'claude' => ['max' => 0, 'time' => null, 'asset' => null],
        'chatgpt' => ['max' => 0, 'time' => null, 'asset' => null],
        'deepseek' => ['max' => 0, 'time' => null, 'asset' => null],
        'nosort' => ['max' => 0, 'time' => null, 'asset' => null],
        'sort' => ['max' => 0, 'time' => null, 'asset' => null]
    ];
    
    foreach ($dataArray as $data) {
        // ตรวจสอบและอัพเดท Claude
        if (isset($data->lossConClaude) && $data->lossConClaude > $maxLossData['claude']['max']) {
            $maxLossData['claude']['max'] = $data->lossConClaude;
            $maxLossData['claude']['time'] = $data->SuggestTimeCandle ?? 'N/A';
            $maxLossData['claude']['asset'] = $data->asset ?? 'N/A';
        }
        
        // ตรวจสอบและอัพเดท ChatGPT
        if (isset($data->lossConChatGpt) && $data->lossConChatGpt > $maxLossData['chatgpt']['max']) {
            $maxLossData['chatgpt']['max'] = $data->lossConChatGpt;
            $maxLossData['chatgpt']['time'] = $data->SuggestTimeCandle ?? 'N/A';
            $maxLossData['chatgpt']['asset'] = $data->asset ?? 'N/A';
        }
        
        // ตรวจสอบและอัพเดท DeepSeek
        if (isset($data->lossConDeepSeek) && $data->lossConDeepSeek > $maxLossData['deepseek']['max']) {
            $maxLossData['deepseek']['max'] = $data->lossConDeepSeek;
            $maxLossData['deepseek']['time'] = $data->SuggestTimeCandle ?? 'N/A';
            $maxLossData['deepseek']['asset'] = $data->asset ?? 'N/A';
        }
        
        // ตรวจสอบและอัพเดท NoSort
        if (isset($data->lossConNoSort) && $data->lossConNoSort > $maxLossData['nosort']['max']) {
            $maxLossData['nosort']['max'] = $data->lossConNoSort;
            $maxLossData['nosort']['time'] = $data->SuggestTimeCandle ?? 'N/A';
            $maxLossData['nosort']['asset'] = $data->asset ?? 'N/A';
        }
        
        // ตรวจสอบและอัพเดท Sort
        if (isset($data->lossConSort) && $data->lossConSort > $maxLossData['sort']['max']) {
            $maxLossData['sort']['max'] = $data->lossConSort;
            $maxLossData['sort']['time'] = $data->SuggestTimeCandle ?? 'N/A';
            $maxLossData['sort']['asset'] = $data->asset ?? 'N/A';
        }
    }
    
    // หาค่าสูงสุดโดยรวม
    $overallMax = findOverallMax($maxLossData);
    
    return [
        'individual_max' => $maxLossData,
        'overall_max' => $overallMax,
        'total_records' => count($dataArray)
    ];
}

/**
 * หาค่าสูงสุดโดยรวมจากทุก AI
 * @param array $maxLossData
 * @return array
 */
function findOverallMax($maxLossData) {
    $overallMax = ['max' => 0, 'ai' => null, 'time' => null, 'asset' => null];
    
    foreach ($maxLossData as $ai => $data) {
        if ($data['max'] > $overallMax['max']) {
            $overallMax['max'] = $data['max'];
            $overallMax['ai'] = $ai;
            $overallMax['time'] = $data['time'];
            $overallMax['asset'] = $data['asset'];
        }
    }
    
    return $overallMax;
}

/**
 * แสดงผลในรูปแบบที่อ่านง่าย
 * @param array $result
 */
function displayLossResult($result) {
    if (isset($result['error'])) {
        echo "❌ Error: " . $result['error'] . "<br>";
        return;
    }
    
    echo "🔍 LOSS STREAK ANALYSIS REPORT<br>";
    echo str_repeat("=", 50) . "<br><br>";
    
    echo "📊 Total Records Analyzed: " . $result['total_records'] . "<br><br>";
    
    echo "🎯 MAXIMUM LOSS STREAK BY AI:<br>";
    echo str_repeat("-", 40) . "<br>";
    
    $aiNames = [
        'claude' => '🤖 Claude',
        'chatgpt' => '💬 ChatGPT', 
        'deepseek' => '🔍 DeepSeek',
        'nosort' => '📈 ClassTrade (NoSort)',
        'sort' => '📊 ClassTrade (WithSort)'
    ];
    
    foreach ($result['individual_max'] as $ai => $data) {
        $emoji = $data['max'] > 5 ? '🔴' : ($data['max'] > 3 ? '🟡' : '🟢');
        echo sprintf(
            "%s %-25s: %s %d losses at %s (%s)<br>",
            $emoji,
            $aiNames[$ai],
            $emoji,
            $data['max'],
            $data['time'],
            $data['asset']
        );
    }
    
    echo "<br>" . str_repeat("=", 50) . "<br>";
    echo "🏆 OVERALL MAXIMUM LOSS STREAK:<br>";
    echo str_repeat("-", 30) . "<br>";
    
    $winner = $result['overall_max'];
    $winnerEmoji = $winner['max'] > 5 ? '🔴💀' : ($winner['max'] > 3 ? '🟡⚠️' : '🟢✅');
    
    echo sprintf(
        "%s %s had the highest loss streak of %d consecutive losses<br>",
        $winnerEmoji,
        $aiNames[$winner['ai']],
        $winner['max']
    );
    echo "⏰ Time: " . $winner['time'] . "<br>";
    echo "💰 Asset: " . $winner['asset'] . "<br><br>";
    
    // Risk Assessment
    showRiskAssessment($winner['max']);
}

/**
 * แสดงการประเมินความเสี่ยง
 * @param int $maxLoss
 */
function showRiskAssessment($maxLoss) {
    echo "⚠️ RISK ASSESSMENT:<br>";
    echo str_repeat("-", 20) . "<br>";
    
    if ($maxLoss <= 2) {
        echo "🟢 LOW RISK: Loss streak is acceptable<br>";
    } elseif ($maxLoss <= 4) {
        echo "🟡 MEDIUM RISK: Monitor closely<br>";
    } elseif ($maxLoss <= 6) {
        echo "🟠 HIGH RISK: Consider strategy adjustment<br>";
    } else {
        echo "🔴 CRITICAL RISK: Immediate action required!<br>";
    }
    
    echo "💡 Recommended max consecutive losses: 3-5<br>";
}

function getAllForecastClass($candleData) { 

$newUtilPath = '/home/thepaper/domains/thepapers.in/private_html/';
require_once("Claude/candleAnalyzerClaude.php"); 
require_once("Chatgpt/candleAnalyzerChatGPT.php"); 
require_once("DeepSeek/CandlestickAnalyzer_DeepSeek.php"); 
require_once("ClsTrade/clsTradeV3.php"); 

require_once("emaConflict/clsEMAConflict.php"); 

// Claude
$ClaudeAnalyzer = new CandlestickAnalyzerClaude($candleData);
//ChatGPT
$ChatGPTAnalyzer = new TradeAnalyzer($candleData);
//DeepSeek
$DeepSeekAnalyzer = new AdvancedCandlestickAnalyzer($candleData);
//Pick
$clsTradeAnalyzer = new clsTradeV3($candleData);
//emaConflict
$clsEMAConflict  = new clsEMAConflict($candleData);

return array($ClaudeAnalyzer,$ChatGPTAnalyzer,$DeepSeekAnalyzer,$clsTradeAnalyzer,$clsEMAConflict);

} // end function

function SaveLabTrade($tradeArray) { 

/*
Table Name : MixLab 
id,assetCode,startTime,endTime,rawdata,AnalyData
Table Name : LossConLabDetail 
MixLabId,clsName(claude,deepseek,..),
maxLossCon , startTimeLoss,endTimeLoss

*/

require_once("deriv/newutil2.php"); 
$dbname = 'thepaper_lab' ;
$pdo = getPDONew();
$sql = 'select * from '; 
$params = array();
$sValue =pdogetValue($sql,$params,$pdo) ;
$rs= pdogetMultiValue2($sql,$params,$pdo) ;
$rs= pdogetMultiValue($sql,$params,$dbname='');
$row = pdoRowSet($sql,$params,$pdo) ;
if (!pdoExecuteQueryV2($pdo,$sql,$params)) {
   echo 'Error' ;
   return false;
}

$pdo->commit();
while($row = $rs->fetch( PDO::FETCH_ASSOC )) {
		    
}



} // end function

function FindMax($dataA) { 

$dataB = JSON_ENCODE($dataA) ;
$data = JSON_DECODE($dataB,true) ;

$sObj = new stdClass();

// ตัวแปรเก็บข้อมูลการวิเคราะห์
$aiSystems = [
    'Claude' => ['maxLossCon' => 0, 'startIndex' => null, 'endIndex' => null, 'ranges' => []],
    'ChatGPT' => ['maxLossCon' => 0, 'startIndex' => null, 'endIndex' => null, 'ranges' => []],
    'DeepSeek' => ['maxLossCon' => 0, 'startIndex' => null, 'endIndex' => null, 'ranges' => []],
    'NoSort' => ['maxLossCon' => 0, 'startIndex' => null, 'endIndex' => null, 'ranges' => []],
    'WithSort' => ['maxLossCon' => 0, 'startIndex' => null, 'endIndex' => null, 'ranges' => []]
];

// วิเคราะห์ข้อมูลแต่ละ record
foreach ($data as $record) {
    $tradeIndex = $record['TradeNoIndex'];
    
    // วิเคราะห์แต่ละ AI System
    foreach (['Claude', 'ChatGPT', 'DeepSeek', 'NoSort', 'WithSort'] as $aiName) {
        $lossCon = $record[$aiName]['LossCon'];
        
        // หาค่าสูงสุด
        if ($lossCon > $aiSystems[$aiName]['maxLossCon']) {
            $aiSystems[$aiName]['maxLossCon'] = $lossCon;
            $aiSystems[$aiName]['startIndex'] = $tradeIndex;
            $aiSystems[$aiName]['endIndex'] = $tradeIndex;
            $aiSystems[$aiName]['ranges'] = [['start' => $tradeIndex, 'end' => $tradeIndex, 'value' => $lossCon]];
        } elseif ($lossCon == $aiSystems[$aiName]['maxLossCon'] && $lossCon > 0) {
            // เพิ่มช่วงที่มีค่าเท่ากับ max
            $aiSystems[$aiName]['ranges'][] = ['start' => $tradeIndex, 'end' => $tradeIndex, 'value' => $lossCon];
        }
    }
}

// แสดงผลการวิเคราะห์
//echo "🔍 การวิเคราะห์ค่า LossCon สูงสุดของแต่ละ AI System<br>";
//echo str_repeat("=", 70) . "<br><br>";

$lossConList = array();
foreach ($aiSystems as $aiName => $data) {
    //echo "🤖 {$aiName}:<br>";
	
    //echo "   📊 ค่า LossCon สูงสุด: {$data['maxLossCon']}<br>";
	$sObj = new stdClass();
	$sObj->aiName = $aiName ;
    $sObj->maxLossCon = $data['maxLossCon'] ;
    
    if (!empty($data['ranges']) && $data['maxLossCon'] > 0) {
       // echo "   📍 เกิดขึ้นที่ TradeNoIndex: ";
        $indexList = array_map(function($range) {
            return $range['start'];
        }, $data['ranges']);
        //echo implode(', ', $indexList) . "<br>";
        $sObj->indexList = implode(', ', $indexList)  ;
        //echo "   📈 รายละเอียดช่วงที่เกิดขึ้น:<br>";
        foreach ($data['ranges'] as $i => $range) {
          //  echo "      - ช่วงที่ " . ($i + 1) . ": TradeNoIndex {$range['start']} (LossCon = {$range['value']})<br>";
        }
    } else {
        //echo "   ✅ ไม่มี Loss ติดต่อกัน<br>";
    }
	$lossConList[] = $sObj ;

    //echo "<br>";
}
return $lossConList ;
// สรุปการเปรียบเทียบ
echo "📋 สรุปการเปรียบเทียบ:<br>";
echo str_repeat("-", 50) . "<br>";

// หา AI ที่มี LossCon สูงสุด
$maxOverall = 0;
$bestAI = [];
$worstAI = [];

foreach ($aiSystems as $aiName => $data) {
    if ($data['maxLossCon'] > $maxOverall) {
        $maxOverall = $data['maxLossCon'];
        $worstAI = [$aiName];
    } elseif ($data['maxLossCon'] == $maxOverall && $maxOverall > 0) {
        $worstAI[] = $aiName;
    }
    
    if ($data['maxLossCon'] == 0) {
        $bestAI[] = $aiName;
    }
}

echo "🏆 AI ที่มีประสิทธิภาพดีที่สุด (LossCon = 0): ";
if (!empty($bestAI)) {
    echo implode(', ', $bestAI) . "<br>";
} else {
    echo "ไม่มี<br>";
}

echo "⚠️  AI ที่มี LossCon สูงสุด ({$maxOverall} ครั้ง): " . implode(', ', $worstAI) . "<br>";

// สรุปข้อมูลเพิ่มเติม
echo "<br>📊 สถิติเพิ่มเติม:<br>";
echo str_repeat("-", 30) . "<br>";

foreach ($aiSystems as $aiName => $data) {
    $winCount = 0;
    $lossCount = 0;
    
    // นับจำนวน Win/Loss
    foreach ($data['ranges'] as $range) {
        if ($range['value'] > 0) {
            $lossCount++;
        }
    }
    
    // นับจำนวน Win จากข้อมูลทั้งหมด
    foreach ($GLOBALS['data'] as $record) {
        if ($record[$aiName]['WinStatus'] === "💲Win") {
            $winCount++;
        }
    }
    
    $totalTrades = count($GLOBALS['data']);
    $winRate = $totalTrades > 0 ? round(($winCount / $totalTrades) * 100, 2) : 0;
    
    echo "{$aiName}: Win Rate {$winRate}% ({$winCount}/{$totalTrades})<br>";
}

// แสดงตารางเปรียบเทียบ
echo "<br>📋 ตารางเปรียบเทียบ LossCon:<br>";
echo str_repeat("-", 60) . "<br>";
printf("%-10s | %-12s | %-20s<br>", "AI System", "Max LossCon", "TradeNoIndex");
echo str_repeat("-", 60) . "<br>";

foreach ($aiSystems as $aiName => $data) {
    $indexes = "";
    if (!empty($data['ranges']) && $data['maxLossCon'] > 0) {
        $indexList = array_map(function($range) {
            return $range['start'];
        }, $data['ranges']);
        $indexes = implode(', ', $indexList);
    } else {
        $indexes = "-";
    }
    
    printf("%-10s | %-12s | %-20s<br>", $aiName, $data['maxLossCon'], $indexes);
}

echo str_repeat("-", 60) . "<br>";

} // end function

function LabLossCon($assetCode,$candleData) { 
	 
/*
{
        "time": 1754992920,
        "open": 2824.863,
        "high": 2825.503,
        "low": 2824.43,
        "close": 2824.43,
        "thisColor": "Red",
        "emaShort": 2824.5866666666666,
        "emaLong": 2824.7433333333333,
        "emaDiff": -0.15666666666675155,
        "conflictType": "n"
    },
*/	     

require_once("../deriv/newutil2.php"); 
$dbname = 'thepaper_lab' ;
$pdo = getPDONew();

	$sql='Truncate  pageLabTmp ';
	$params = array();
	if (!pdoExecuteQueryV2($pdo,$sql,$params)) {
	   echo 'Error' ;
	   return false;
	}

    $sql='INSERT INTO pageLabTmp(timeCandle, open, high, low, close, Color) VALUES (?,?,?,?,?,?)';

	for ($i=0;$i<=count($candleData)-1;$i++) {
		$params= array(
		 $candleData[$i]['time'],
         $candleData[$i]['open'],
         $candleData[$i]['high'],
         $candleData[$i]['low'],
         $candleData[$i]['close'],
         $candleData[$i]['thisColor']			
		);
		if (!pdoExecuteQueryV2($pdo,$sql,$params)) {
	       echo 'Error' ;
	       return false;
	    }	   
	} 

    $lastIndex = count($candleData)-1 ;
	$startDate = $candleData[0]['time'] ;
	$stopDate  = $candleData[$lastIndex]['time'] ;

    // getLossCon0 
	$sqlLossCon0 ='SELECT * FROM ViewColor6 WHERE  Color1 = Color2 ';
	
	// getLossCon2 
	$sqlLossCon2 ='SELECT * FROM ViewColor6 WHERE  Color1 <> Color2 and 
    Color2 = Color3 ';
	// getLossCon3 
	$sqlLossCon3 ='SELECT * FROM ViewColor6 WHERE  Color1 <> Color2 and 
    Color2 <> Color3 and  Color3 = Color4';

	// getLossCon4 
	$sqlLossCon4 ='SELECT * FROM ViewColor6 WHERE  Color1 <> Color2 and 
    Color2 <> Color3 and  Color3 <> Color4 and  Color4 =  Color5';
	// getLossCon5 
	$sqlLossCon5='SELECT * FROM ViewColor6 WHERE 
    Color1 <> Color2 and  Color2 <> Color3 and   Color3 <> Color4 and   Color4 <> Color5 and Color5 =  Color6';

	$sqlLossCon6='SELECT * FROM ViewColor8 WHERE 
    Color1 <> Color2 and  Color2 <> Color3 and   Color3 <> Color4 and   Color4 <> Color5 and Color5 <>  Color6  and Color6 =  Color7';

	$sqlLossCon7='SELECT * FROM ViewColor8 WHERE 
    Color1 <> Color2 and  Color2 <> Color3 and   Color3 <> Color4 and   Color4 <> Color5 and Color5 <>  Color6  and Color6 <> Color7 and Color7 =  Color8';

	$sqlLossCon8='SELECT * FROM ViewColor12 WHERE 
    Color1 <> Color2 and  Color2 <> Color3 and   Color3 <> Color4 and   Color4 <> Color5 and Color5 <>  Color6  and Color6 <> Color7 and Color7 <>  Color8 and Color8=Color9';

	$sqlLossCon9='SELECT * FROM ViewColor12 WHERE 
    Color1 <> Color2 and  Color2 <> Color3 and   Color3 <> Color4 and   Color4 <> Color5 and Color5 <>  Color6  and Color6 <> Color7 and Color7 <>  Color8 
	and Color8 <>  Color9  and Color9 <>  Color10
	';
	$maxLoss0 = getRecordsAsStdClass1($pdo,$sqlLossCon0, $params = []);
    $maxLoss2 = getRecordsAsStdClass1($pdo,$sqlLossCon2, $params = []);
    $maxLoss3 = getRecordsAsStdClass1($pdo,$sqlLossCon3, $params = []);
    $maxLoss4 = getRecordsAsStdClass1($pdo,$sqlLossCon4, $params = []);
	$maxLoss5 = getRecordsAsStdClass1($pdo,$sqlLossCon5, $params = []);
	$maxLoss6 = getRecordsAsStdClass1($pdo,$sqlLossCon6, $params = []);
	$maxLoss7 = getRecordsAsStdClass1($pdo,$sqlLossCon7, $params = []);
	$maxLoss8 = getRecordsAsStdClass1($pdo,$sqlLossCon8, $params = []);
	$maxLoss9 = getRecordsAsStdClass1($pdo,$sqlLossCon9, $params = []);

	$sql = "select id from pageLabHead WHERE assetCode=? 
	and day(startDate)=? and  month(startDate)=?"; 
	$startdatetime_string = date('d', $startDate);
	$startMonthtime_string = date('m', $startDate);
	$params= array($assetCode,$startdatetime_string,$startMonthtime_string);
	$OldID =pdogetValue($sql,$params,$pdo) ;
	
	

	$sql = "DELETE from pageLabHead WHERE assetCode=? 
	and day(startDate)=? and  month(startDate)=?"; 
	$startdatetime_string = date('d', $startDate);
	$startMonthtime_string = date('m', $startDate);

	$params= array($assetCode,$startdatetime_string,$startMonthtime_string);
	if (!pdoExecuteQueryV2($pdo,$sql,$params)) {
	   echo 'Error' ;
	   return false;
	}

	$sql = "DELETE from pageLabDetail WHERE id=?"; 	
	$params= array($OldID);
	if (!pdoExecuteQueryV2($pdo,$sql,$params)) {
	   echo 'Error' ;
	   return false;
	}
	

	$sql='INSERT INTO pageLabHead(assetCode,
	startDateTimeStamp,stopDateTimeStamp, 
	startDate, stopDate,totalCandle, numLoss2, numLoss3,numLoss4, numLoss5, numLoss6, numLoss7, numLoss8, numLoss9) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
	$startdatetime_string = date('Y-m-d H:i:s', $startDate);
	$stopdatetime_string = date('Y-m-d H:i:s', $stopDate);
	
	//$assetCode=  $data['assetCode'];
	$params = array(
	$assetCode,$startDate,$stopDate,
    $startdatetime_string,$stopdatetime_string,
    count($candleData),
    count($maxLoss2),count($maxLoss3),
	count($maxLoss4),count($maxLoss5),
	count($maxLoss6),count($maxLoss7),
    count($maxLoss8),count($maxLoss9)
	);
	
	if (!pdoExecuteQueryV2($pdo,$sql,$params)) {
	   echo 'Error' ;
	   return false;
	}
	$headId=  $pdo->lastInsertId();

	$n = 0 ;
	InsertDetailStatic($pdo,$headId,$maxLoss0,$n);
	
	$n = 2 ;
	InsertDetailStatic($pdo,$headId,$maxLoss2,$n);
	$n = 3 ;
	InsertDetailStatic($pdo,$headId,$maxLoss3,$n);
	$n = 4 ;
	InsertDetailStatic($pdo,$headId,$maxLoss4,$n);
	$n = 5 ;
	InsertDetailStatic($pdo,$headId,$maxLoss5,$n);
	$n = 6 ;
	InsertDetailStatic($pdo,$headId,$maxLoss6,$n);
	$n = 7 ;
	InsertDetailStatic($pdo,$headId,$maxLoss7,$n);

	

	return array($maxLoss0,$maxLoss2,$maxLoss3,$maxLoss4,$maxLoss5,$maxLoss6,$maxLoss7,$maxLoss8,$maxLoss9 );

	/*
	$str_json=json_encode($maxLoss4[0], JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	echo '<pre>' . $str_json . '</pre>' ;$maxLoss4[0] ;
	*/





/*
Color 6
CREATE  VIEW  ViewColor6 as 
SELECT  
a.id,a.time,b.id,c.id,d.id,
a.Color as Color1,b.Color as Color2 , c.Color as Color3, d.Color as Color4, e.Color as Color5, f.Color as Color6
FROM pageLabTmp a 
inner join pageLabTmp b  on a.id+1 = b.id
inner join pageLabTmp c  on b.id+1 = c.id
inner join pageLabTmp d  on c.id+1 = d.id
inner join pageLabTmp e  on d.id+1 = e.id
inner join pageLabTmp f  on e.id+1 = f.id


*/



/*
Color 8
alter view ViewColor8 as 
SELECT  
a.id,a.timeCandle,
a.Color as Color1,b.Color as Color2 , c.Color as Color3, d.Color as Color4, e.Color as Color5, f.Color as Color6,g.Color as Color7, h.Color as Color8
FROM pageLabTmp a 
inner join pageLabTmp b  on a.id+1 = b.id
inner join pageLabTmp c  on b.id+1 = c.id
inner join pageLabTmp d  on c.id+1 = d.id
inner join pageLabTmp e  on d.id+1 = e.id
inner join pageLabTmp f  on e.id+1 = f.id
inner join pageLabTmp g  on f.id+1 = g.id
inner join pageLabTmp h  on g.id+1 = h.id

CREATE view ViewColor12 as 
SELECT  
a.id,a.timeCandle,
a.Color as Color1,b.Color as Color2 , c.Color as Color3, d.Color as Color4, e.Color as Color5, f.Color as Color6,g.Color as Color7, h.Color as Color8,
i.Color as Color9, j.Color as Color10,k.Color as Color11,l.Color as Color12
FROM pageLabTmp a 
inner join pageLabTmp b  on a.id+1 = b.id
inner join pageLabTmp c  on b.id+1 = c.id
inner join pageLabTmp d  on c.id+1 = d.id
inner join pageLabTmp e  on d.id+1 = e.id
inner join pageLabTmp f  on e.id+1 = f.id
inner join pageLabTmp g  on f.id+1 = g.id
inner join pageLabTmp h  on g.id+1 = h.id
inner join pageLabTmp i  on h.id+1 = i.id
inner join pageLabTmp j  on i.id+1 = j.id
inner join pageLabTmp k  on j.id+1 = k.id
inner join pageLabTmp l  on k.id+1 = l.id

*/







} // end function

function InsertDetailStatic($pdo,$id,$maxLossObject,$n) { 

$sql='INSERT INTO pageLabDetail(id,MaxLossCon, LossConNo, startTime, startTimeString) VALUES (?,?,?,?,?)';

for ($i=0;$i<=count($maxLossObject)-1;$i++) {
	$startTime = $maxLossObject[$i]->timeCandle ;
	$startTime_string = date('Y-m-d H:i:s', $startTime);
	$params = array($id,$n,$i+1,$startTime,$startTime_string);
    if (!pdoExecuteQueryV2($pdo,$sql,$params)) {
	   echo 'Error' ;
	   return false;
	}
   
}



} // end function


// ฟังก์ชันนับจำนวน LossCon แต่ละระดับ
function calculateLossConDistribution($tradeResult, $aiName, $maxLevel = 15) {
    $distribution = array_fill(0, $maxLevel + 1, 0);
    
    foreach ($tradeResult as $row) {
        $lossCon = $row[$aiName]['LossCon'];
        if ($lossCon <= $maxLevel) {
            $distribution[$lossCon]++;
        }
    }
    
    return $distribution;
}


// ฟังก์ชันคำนวณ Max LossCon
function calculateMaxLossCon($tradeResult, $aiName) {
    $maxLossCon = 0;
    $occurrences = [];
    
    foreach ($tradeResult as $index => $row) {
        $lossCon = $row[$aiName]['LossCon'];
        
        if ($lossCon > $maxLossCon) {
            $maxLossCon = $lossCon;
            $occurrences = [[
                'lineNo' => $index + 1,
                'time' => $row['SuggestTimeCandle']
            ]];
        } elseif ($lossCon == $maxLossCon && $maxLossCon > 0) {
            $occurrences[] = [
                'lineNo' => $index + 1,
                'time' => $row['SuggestTimeCandle']
            ];
        }
    }
    
    return [
        'max' => $maxLossCon,
        'occurrences' => $occurrences,
        'count' => count($occurrences)
    ];
}

function displayLab($tradeResult0) {  
	
	
$tradeResult = JSON_DECODE(JSON_ENCODE($tradeResult0),true);

// คำนวณสำหรับแต่ละ AI
$aiModels = ['Claude', 'ChatGPT', 'DeepSeek', 'NoSort', 'WithSort'];

$lossConDistribution = [];
foreach ($aiModels as $ai) {
    $lossConDistribution[$ai] = calculateLossConDistribution($tradeResult, $ai);
}

$stats = []; 
foreach ($aiModels as $ai) {
    $stats[$ai] = calculateMaxLossCon($tradeResult, $ai);
}
	
 
	  $stDetail = DetailTable($tradeResult,$stats);	
	  $stMaxLossCon = MaxLossConTable($aiModels,$stats);	
	  $stLossConDistribution = LossConDistribution($aiModels,$lossConDistribution) ;
	?>
	
     
    
    

<?php
} // end function

function DetailTable($tradeResult,$stats) { 

$st = '<h2>📊 Trade Result Report</h2>';
$st .= '<div class="table-container">';
$st .= '<table>';
$st .= '<thead>';
$st .= '<tr>';
$st .= '<th rowspan="2">Line No</th>';
$st .= '<th rowspan="2">Start Time</th>';
$st .= '<th rowspan="2">Result Color</th>';
$st .= '<th colspan="3">Claude</th>';
$st .= '<th colspan="3">ChatGPT</th>';
$st .= '<th colspan="3">DeepSeek</th>';
$st .= '<th colspan="3">NoSort</th>';
$st .= '<th colspan="3">WithSort</th>';
$st .= '</tr>';
$st .= '<tr>';
$st .= '<th>Suggest Color</th>';
$st .= '<th>Win Status</th>';
$st .= '<th>Loss Con</th>';
$st .= '<th>Suggest Color</th>';
$st .= '<th>Win Status</th>';
$st .= '<th>Loss Con</th>';
$st .= '<th>Suggest Color</th>';
$st .= '<th>Win Status</th>';
$st .= '<th>Loss Con</th>';
$st .= '<th>Suggest Color</th>';
$st .= '<th>Win Status</th>';
$st .= '<th>Loss Con</th>';
$st .= '<th>Suggest Color</th>';
$st .= '<th>Win Status</th>';
$st .= '<th>Loss Con</th>';
$st .= '</tr>';
$st .= '</thead>';
$st .= '<tbody>';

foreach ($tradeResult as $index => $row) {
    $st .= "<tr style='background:whitesmoke;color:black'>";
    $st .= '<td>' . ($index + 1) . '</td>';
    $st .= '<td>' . htmlspecialchars($row['SuggestTimeCandle']) . '</td>';
    $st .= '<td>' . htmlspecialchars($row['resultColor']) . '</td>';
    
    // Claude
    $st .= '<td class="' . strtolower($row['Claude']['SuggestColor']) . '">';
    $st .= htmlspecialchars($row['Claude']['SuggestColor']);
    $st .= '</td>';
    $st .= '<td>' . htmlspecialchars($row['Claude']['WinStatus']) . '</td>';
    $lossClass = ($row['Claude']['LossCon'] == $stats['Claude']['max'] && $stats['Claude']['max'] > 0) ? 'max-loss-highlight' : '';
    $st .= '<td class="' . $lossClass . '">';
    $st .= htmlspecialchars($row['Claude']['LossCon']);
    $st .= '</td>';
    
    // ChatGPT
    $st .= '<td class="' . strtolower($row['ChatGPT']['SuggestColor']) . '">';
    $st .= htmlspecialchars($row['ChatGPT']['SuggestColor']);
    $st .= '</td>';
    $st .= '<td>' . htmlspecialchars($row['ChatGPT']['WinStatus']) . '</td>';
    $lossClass = ($row['ChatGPT']['LossCon'] == $stats['ChatGPT']['max'] && $stats['ChatGPT']['max'] > 0) ? 'max-loss-highlight' : '';
    $st .= '<td class="' . $lossClass . '">';
    $st .= htmlspecialchars($row['ChatGPT']['LossCon']);
    $st .= '</td>';
    
    // DeepSeek
    $st .= '<td class="' . strtolower($row['DeepSeek']['SuggestColor']) . '">';
    $st .= htmlspecialchars($row['DeepSeek']['SuggestColor']);
    $st .= '</td>';
    $st .= '<td>' . htmlspecialchars($row['DeepSeek']['WinStatus']) . '</td>';
    $lossClass = ($row['DeepSeek']['LossCon'] == $stats['DeepSeek']['max'] && $stats['DeepSeek']['max'] > 0) ? 'max-loss-highlight' : '';
    $st .= '<td class="' . $lossClass . '">';
    $st .= htmlspecialchars($row['DeepSeek']['LossCon']);
    $st .= '</td>';
    
    // NoSort
    $st .= '<td class="' . strtolower($row['NoSort']['SuggestColor']) . '">';
    $st .= htmlspecialchars($row['NoSort']['SuggestColor']);
    $st .= '</td>';
    $st .= '<td>' . htmlspecialchars($row['NoSort']['WinStatus']) . '</td>';
    $lossClass = ($row['NoSort']['LossCon'] == $stats['NoSort']['max'] && $stats['NoSort']['max'] > 0) ? 'max-loss-highlight' : '';
    $st .= '<td class="' . $lossClass . '">';
    $st .= htmlspecialchars($row['NoSort']['LossCon']);
    $st .= '</td>';
    
    // WithSort
    $st .= '<td class="' . strtolower($row['WithSort']['SuggestColor']) . '">';
    $st .= htmlspecialchars($row['WithSort']['SuggestColor']);
    $st .= '</td>';
    $st .= '<td>' . htmlspecialchars($row['WithSort']['WinStatus']) . '</td>';
    $lossClass = ($row['WithSort']['LossCon'] == $stats['WithSort']['max'] && $stats['WithSort']['max'] > 0) ? 'max-loss-highlight' : '';
    $st .= '<td class="' . $lossClass . '">';
    $st .= htmlspecialchars($row['WithSort']['LossCon']);
    $st .= '</td>';
    
    $st .= '</tr>';
}

$st .= '</tbody>';
$st .= '</table>';
$st .= '</div>';
return $st ;

} // end function

function MaxLossConTable($aiModels,$stats) { 

$st = '<div class="stats-container">';
$st .= '<h3>📈 Max Loss Consecutive Statistics</h3>';
$st .= '<div class="stats-grid">';

foreach ($aiModels as $ai) {
    $st .= '<div class="stat-card">';
    $st .= '<h3>' . $ai . '</h3>';
    $st .= '<div class="stat-value">' . $stats[$ai]['max'] . '</div>';
    $st .= '<div class="stat-detail">';
    $st .= '<strong>จำนวนครั้งที่เกิด:</strong> ' . $stats[$ai]['count'] . ' ครั้ง';
    $st .= '</div>';
    
    if ($stats[$ai]['count'] > 0) {
        $st .= '<div class="occurrence-list">';
        $st .= '<strong>ช่วงเวลาที่เกิด:</strong>';
        
        foreach ($stats[$ai]['occurrences'] as $occ) {
            $st .= '<div class="occurrence-item">';
            $st .= '📍 Line ' . $occ['lineNo'] . ': ' . $occ['time'];
            $st .= '</div>';
        }
        
        $st .= '</div>';
    }
    
    $st .= '</div>';
}

$st .= '</div>';
$st .= '</div>';

return $st ;

} // end function


function LossConDistribution($aiModels,$lossConDistribution) { 

$st = '<div class="stats-container" style="margin-top: 20px;">';
$st .= '<h3>📊 Loss Consecutive Distribution Summary</h3>';
$st .= '<div style="overflow-x: auto;">';
$st .= '<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">';
$st .= '<thead>';
$st .= '<tr>';
$st .= '<th style="background-color: #2196F3; color: white; padding: 10px; border: 1px solid #ddd;">Loss Con Level</th>';

foreach ($aiModels as $ai) {
    $st .= '<th style="background-color: #2196F3; color: white; padding: 10px; border: 1px solid #ddd;">';
    $st .= $ai;
    $st .= '</th>';
}

$st .= '</tr>';
$st .= '</thead>';
$st .= '<tbody>';

for ($level = 0; $level <= 15; $level++) {
    $st .= "<tr style='background:whitesmoke;color:black'>";
    $st .= '<td style="padding: 8px; border: 1px solid #ddd; text-align: center; font-weight: bold;">';
    $st .= $level;
    $st .= '</td>';
    
    foreach ($aiModels as $ai) {
        $bgColor = ($lossConDistribution[$ai][$level] > 0) ? 'background-color: #fff3cd;' : '';
        $st .= '<td style="padding: 8px; border: 1px solid #ddd; text-align: center; ' . $bgColor . '">';
        $st .= $lossConDistribution[$ai][$level];
        $st .= '</td>';
    }
    
    $st .= '</tr>';
}

// แถวรวม
$st .= '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
$st .= '<td style="padding: 10px; border: 1px solid #ddd; text-align: center;">';
$st .= 'Total';
$st .= '</td>';

foreach ($aiModels as $ai) {
    $st .= '<td style="padding: 10px; border: 1px solid #ddd; text-align: center;">';
    $st .= array_sum($lossConDistribution[$ai]);
    $st .= '</td>';
}

$st .= '</tr>';
$st .= '</tbody>';
$st .= '</table>';
$st .= '</div>';
$st .= '</div>';

return $st ;


} // end function

?>


 