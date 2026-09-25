<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>Document</title>
  <script src="candleAnalysis.js" ></script>

  <script>
  function test() {
	  candle  = {
	    close : 5876.822,
        high  : 5877.55,
        low   :  5875.329,
        open :  5875.329,
        time : 1759366800
      }

      CalBodyAndWickSize(candle);
	 
  
  } // end func
  
  </script>

  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
  
  
 </head>
 <body>
 <button type='button' id='' class='mBtn' onclick="test()">Test</button>
 <div id="result" class="bordergray flex">
      
 </div>
  
 </body>
</html>
