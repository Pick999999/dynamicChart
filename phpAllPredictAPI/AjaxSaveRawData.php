<?php
  
  header('Access-Control-Allow-Methods: GET, POST');
  //header('Access-Control-Allow-Origin: *'); 
  ob_start();
  //https://www.thaicreate.com/community/login-php-jquery-2encrypt.html
  //https://www.cyfence.com/article/design-secured-api/
  
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);   
     $data = json_decode(file_get_contents('php://input'), true);
     if ($data) {
        
        
        if ($data['Mode'] == 'SaveRawData') { SaveRawData($data); }
        return;
     }
  
  function SaveRawData($data) { 

	   
	      $txt=json_encode($data['candleData'], JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	      //echo '<pre>' . $str_json . '</pre>' ;
  
	       
		   $myfile = fopen("rawData.json", "w") or die("Unable to open file!");		   
		   fwrite($myfile, $txt);
		   fclose($myfile);
		   $sObj = new stdClass();
		   $sObj->TotalData = count($data['candleData']);

		   $txt=json_encode($sObj, JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		   echo $txt;
           

		   
	     
  
  } // end function


?>