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
      if ($data['Mode'] == 'saveCandleCode') { saveCandleCode($data); }
	  if ($data['Mode'] == 'getCandleCode') { getCandleCode($data); }
      return;
   }

function saveCandleCode($data) { 

         $myfile = fopen("CandleCode.txt", "w") or die("Unable to open file!");
         $txt =  JSON_ENCODE($data['candleCodeList'], JSON_UNESCAPED_UNICODE  | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
         fwrite($myfile, $txt);
         fclose($myfile);

		 echo "Success";
	      
	     

} // end function 

function getCandleCode($data) { 

         
		  $st = "";   
		  
		  
		  $sFileName = 'CandleCode.txt';
		  $file = fopen($sFileName,"r");
		  while(! feof($file))  {
		    $st .= fgets($file) ;
		  }
		  fclose($file);
		 



		 echo $st ;
	      
	     

} // end function 

?>