<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>Document</title>
  
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>

 

 </head>
 <body>
  <h2>Raw Data</h2>
  <?php
    
     $st = "";        
     $file = fopen('RawData/rawData2.json',"r");
     while(! feof($file))  {
       $st .= fgets($file) ;
     }
     fclose($file);
    
  ?> 
  <button onclick="pasteText()">Paste</button>
  <textarea id="rawData" style='width:100%;height:300px'><?=$st;?></textarea>
  <button type='button' id='' class='mBtn' onclick="analyFVG()">Analysis FVG</button>

  theresHold :: <select id="theresHold">
    <option value="10" selected>10
	<option value="20" >20
    <option value="25" >25
	<option value="30">30
	<option value="35">35

	<option value="40">40
	<option value="45">45
	<option value="50">50
	<option value="55">55

	<option value="60">60
	<option value="70">70

  </select>
  <button type='button' id='' class='mBtn' onclick="analyzeSideway()">
   Analysis Sideway
  </button> 

  <div id="resultDiv" class="bordergray flex">
       
  </div>


 
 <script>

 function analyFVG() {

    return ;
    candleData = JSON.parse(document.getElementById("rawData").value) ;
	const result = analyzeFVG(candleData, 5);
    printFVGReport(result);

 } 

 
 

function analyzeSideway() {

document.getElementById("resultDiv").innerHTML = '';

//displayResult('🔍 วิเคราะห์ข้อมูลที่ให้มา...\n');
candleData = JSON.parse(document.getElementById("rawData").value) ;
//displayResult(candleData.length)
theresHold = document.getElementById("theresHold").value ;
displayHTMLReportWithAction(candleData,theresHold, 5);

//displayHTMLReport(candleData, 5);

//const result = analyzeChoppyMarket(candleData, 3);
//printChoppyAnalysis(result);


return ;

const testData = [
  { "close": 2866.354, "epoch": 1753270320, "high": 2867.35, "low": 2866.138, "open": 2867.148 },
  { "close": 2866.771, "epoch": 1753270380, "high": 2866.771, "low": 2865.845, "open": 2866.079 },
  { "close": 2865.694, "epoch": 1753270440, "high": 2866.93, "low": 2865.694, "open": 2866.93 },
  { "close": 2866.164, "epoch": 1753270500, "high": 2866.404, "low": 2865.671, "open": 2865.671 },
  { "close": 2865.904, "epoch": 1753270560, "high": 2866.199, "low": 2865.158, "open": 2866.199 },
  { "close": 2866.276, "epoch": 1753270620, "high": 2866.969, "low": 2865.631, "open": 2866.039 },
  { "close": 2866.201, "epoch": 1753270680, "high": 2866.446, "low": 2865.172, "open": 2866.211 },
  { "close": 2866.959, "epoch": 1753270740, "high": 2867.042, "low": 2865.745, "open": 2865.965 },
  { "close": 2864.889, "epoch": 1753270800, "high": 2866.947, "low": 2864.889, "open": 2866.947 }
];



// 1. บันทึกเป็นไฟล์ HTML
saveHTMLReport(testData, 'choppy-analysis.html', 3);

// 2. แสดงใน Browser (ถ้าอยู่ใน Browser)
// displayHTMLReport(testData, 3);

// 3. ได้ HTML String
const htmlString = generateChoppyHTMLReport(testData, 3);
console.log('✅ สร้าง HTML Report สำเร็จ');
} // end func

 



function pasteText() {
  document.getElementById("rawData").value = '';
  navigator.clipboard.readText()
    .then(text => {
      document.getElementById("rawData").value = text;
      alert("Pasted from clipboard!");
    })
    .catch(err => {
      console.error("Failed to read clipboard: ", err);
    });
}
</script>

 <script src="fvgV0.js"></script>
 <script src="analyzeChoppyMarketV2.js"></script>
 

 </body>
</html>
