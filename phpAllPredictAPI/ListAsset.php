<?php
 ob_start();
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);  
   $st = "";   
   
  
   $sFileName = 'allAssets.json';
   $file = fopen($sFileName,"r");
   while(! feof($file))  {
     $st .= fgets($file) ;
   }
   fclose($file);
  // echo $st ."<hr>";
  
?>
<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>Document</title>
 </head>
 <body>
  <?php
     $fixedJson = preg_replace('/(\w+):/i', '"$1":', $st);

// ลองแปลง
    $data = json_decode($fixedJson, true);
    
//	echo count($data); 
    $name1 = 'market' ;$name2 = 'market_display_name' ;
	$name3 = 'symbol' ;
/*
	$symbolNames = array_column($data, $name1, $name2);
	printJson($symbolNames);
	return ;
*/
// วิธีที่ 4: เลือกหลาย field (PHP 7.4+)
$result = array_map(fn($i) => [
    'symbol' => $i['symbol'],
    'name' => $i['display_name'],
    'pip' => $i['pip']
], $data);
//printJson($result);
echo "<hr><hr>";


// วิธีที่ 1: สั้นที่สุด - เอาเฉพาะค่า submarket ไม่ซ้ำ
$uniqueSubmarkets = array_unique(array_column($data, 'submarket'));
printJson($uniqueSubmarkets);
echo "<hr><hr>";

return;

    $str_json=json_encode($data, JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT |   JSON_UNESCAPED_SLASHES);
   // echo '<pre>' . $str_json . '</pre>' ;
	
	
// วิธีที่ 1: สั้นที่สุด (PHP 7.4+)
   $stockIndex = array_filter($data, fn($item) => $item['symbol_type'] === 'stockindex');
   printJson($stockIndex);

  
function printJson($data) { 
   
   echo "Total Data = " . count($data) . '<hr>';
   $str_json=json_encode($data, JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT |   JSON_UNESCAPED_SLASHES);
   echo '<pre>' . $str_json . '</pre>' ;

} // end function


    
  ?>

 </body>
</html>
