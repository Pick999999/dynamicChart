<?php
    
     $st = "";        
     $file = fopen('RawData/rawData2.json',"r");
     while(! feof($file))  {
       $st .= fgets($file) ;
     }
     fclose($file);
    
 ?> 
  <button onclick="TestIndy()">Calculate </button>
  <textarea id="rawData" style='width:100%;height:100px'><?=$st;?></textarea>
<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">

  <title>Document</title>
  <style>
   .bordergray { border:1px solid gray ; padding:10px }

   .table-ocean {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Segoe UI', sans-serif;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.table-ocean thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-ocean thead th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.95em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-ocean tbody tr {
    transition: all 0.3s ease;
}

.table-ocean tbody tr:nth-child(odd) {
    background-color: #f0f4ff;
}

.table-ocean tbody tr:nth-child(even) {
    background-color: #ffffff;
}

.table-ocean tbody tr:hover {
    background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);
    color: #0080ff ;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.table-ocean tbody td {
    /*padding: 12px 15px;*/
    border-bottom: 1px solid #e3f2fd;
    color: #333;
} 

.table-ice {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-family: 'Segoe UI', sans-serif;
}

.table-ice thead {
    background: #0288d1;
    color: white;
}

.table-ice thead th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    border: none;
}

.table-ice thead th:first-child {
    border-radius: 8px 0 0 0;
}

.table-ice thead th:last-child {
    border-radius: 0 8px 0 0;
}

.table-ice tbody tr {
    /*transition: background 0.3s ease, transform 0.2s ease;*/
}

.table-ice tbody tr:nth-child(odd) {
    background-color: #e1f5fe;
}

.table-ice tbody tr:nth-child(even) {
    background-color: #ffffff;
}

.table-ice tbody tr:hover {
    background: #b3e5fc;
    
    cursor: pointer;
}

.table-ice tbody td {
    /*padding: 14px 16px;*/
	padding:8px;
    border-bottom: 1px solid #b3e5fc;
}

  </style>
  <link href="" rel="stylesheet">

  <hr>
  <h2>Result</h2>
  <div id="result" class="bordergray flex" style='min-height:100px;height:100px;width:100%;overflow:scroll'>       
  </div>
  <div id="OutPut" class="bordergray flex">
       
  </div>

  <table>
  <tr>
	<td>1</td>
	<td>EMA</td>
	<td></td>
  </tr>
  <tr>
	<td>2</td>
	<td>MACD</td>
	<td></td>
  </tr>
  <tr>
	<td>3</td>
	<td>RSI</td>
	<td></td>
  </tr>
  <tr>
	<td>4</td>
	<td>Bollinger</td>
	<td></td>
  </tr>
  <tr>
	<td>5</td>
	<td>ADX</td>
	<td></td>
  </tr>
  <tr>
	<td>6</td>
	<td>Fair Value Gap</td>
	<td></td>
  </tr>
  <tr>
	<td>7</td>
	<td>Choppie</td>
	<td></td>
  </tr>


  </table>
  

  <script src="js/clsAnalyEMA.js"></script>
  <script src="js/clsIndicator.js"></script>

  <script>
  function TestIndy() {

     let rawData = JSON.parse(document.getElementById("rawData").value) ;
     rawData = rawData.splice(0,100)  ;    
	 const emaTrendObject = new AnalyEMATrend(rawData) ;
	 rawData = emaTrendObject.convertEPOCH(rawData);	 
	 emaShortPeriod = 5 ; emaLongPeriod = 10 ;
	 results = emaTrendObject.analyzeCandleData(rawData, emaShortPeriod, emaLongPeriod ) ;
	 emaTrendObject.getAnalysisOutput(results) 
	  

  }
  </script>
  
  
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
  
  

 </head>
 <body>
  
 </body>
</html>
