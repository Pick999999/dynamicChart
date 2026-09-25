<?php
  ob_start();
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

 class clsEMAConflict {
  
   
  
  function __construct($dataservice) { 
 
  } // end __construct
  
  function TestCase() {
  
  
  }
    // Methods
  function init_CSS_JS($foldername) { ?>
  
  
  <?php
  } 
  function init_Data() { 
  
          require_once($_SERVER['DOCUMENT_ROOT'] ."/dataservice/clsDataService.php"); 
          $clsDataService = new clsDataService($this->shopName,$this->memberid) ;
	$newUtilPath = '/home/ddhousin/domains/lovetoshopmall.com/private_html/';
          require_once($newUtilPath ."src/dataservice/index.php"); 
  
  }
  
  function Rendor() { 
  
          require_once($_SERVER['DOCUMENT_ROOT'] ."/dataservice/clsDataService.php"); 
          $clsDataService = new clsDataService($this->shopName,$this->memberid) ;
  
  }
  
  /*
  require_once($_SERVER['DOCUMENT_ROOT'] ."/shopA/cls***.php"); 
  $cls_aa = new $cls_aa() ;
  */
  
  
  } // end class
  
  
  
   
  

?>