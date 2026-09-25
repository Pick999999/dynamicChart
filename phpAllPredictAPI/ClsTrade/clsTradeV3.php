<?php
  
class clsTradeV3 {

private $candlesAnalysis;
private $totalData ;
private $lastIndex;
private $macdThershold,$lastMacdHeight ;

function __construct($rawData) { 


   require_once('noSortGetAction.php');
   require_once('sortGetAction.php');
   require_once('phpCandlestickIndy.php');
   $clsStep1 = new TechnicalIndicators();   

   require_once('phpAdvanceIndy.php');
   $clsStep2 = new AdvancedIndicators();   
   $result = $clsStep1->calculateIndicators($rawData);
   $this->candlesAnalysis = $clsStep2->calculateAdvancedIndicators($result);
   $this->totalData = count($this->candlesAnalysis);
   $this->lastIndex = count($this->candlesAnalysis)-1;

   $this->macdThershold = 0.05 ;
   $this->lastMacdHeight = 0 ;




} // end __construct

function getSuggestColorNoSort($indexToForecast=null) {

if (!isset($indexToForecast)) {
   $indexToForecast=$this->lastIndex ;
}
$row = $this->candlesAnalysis[$this->lastIndex];
list($thisAction,$actionReason)= getActionFromID_NoSorted($row,$this->macdThershold,$this->lastMacdHeight);
//getActionFromID_NoSorted

return array($thisAction,$actionReason);
}

function getSuggestColorWithSort($indexToForecast=null) {

if (!isset($indexToForecast)) {
   $indexToForecast=$this->lastIndex ;
}
$row = $this->candlesAnalysis[$this->lastIndex];
list($thisAction,$actionReason)= getActionFromIDVerObject_Sorted($row,$this->macdThershold,$this->lastMacdHeight);
//getActionFromID_NoSorted

return array($thisAction,$actionReason);
}
   


function getResultColor($AnalyObj,$thisIndex) { 

           
         $nextColor = $AnalyObj[$thisIndex+1]['thisColor'] ;
		 return $nextColor;


} // end function

} // end class



 


?>