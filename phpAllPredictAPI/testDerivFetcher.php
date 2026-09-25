<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>Document</title>
  <script src="js/clsDerivDataFetcher.js" ></script>
  

  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>

  <style>
  .box {
    position: relative;
    border: 2px solid #007bff;
    border-radius: 8px;
    padding: 20px;
    margin: 30px;
  }

  .box-label {
    position: absolute;
    top: -12px;
    left: 15px;
    background: white; /* ให้พื้นหลังกลืนกับหน้า */
    padding: 0 8px;
    font-weight: bold;
    color: #007bff;
    font-family: sans-serif;
  }

  .content {
    font-family: sans-serif;
    color: #333;
  }
  .flex { height:100px; max-height:100px;overflow:scroll}
  </style>


  
 </head>
 <body>
 <button type='button' id='' class='mBtn' onclick="fff()">Fetch Data</button>

 <div class="box">
  <div  class="box-label">ข้อมูล ที่ดึงมา</div>
  <div id= 'result1' class="bordergray flex">
       
  </div>  
</div>

<div class="box">
  <div  class="box-label">ข้อมูล ที่ แปลงแล้ว</div>
  <div id= 'result2' class="bordergray flex">
       
  </div>  
</div>
 
 </body>

 <script>
  async function example() {
    try {
        // 1. สร้าง instance (ใส่ app_id ของคุณ)
        const fetcher = new DerivDataFetcher('1089'); // ใช้ app_id ทดสอบ
        
        // 2. เชื่อมต่อ
        await fetcher.connect();
        
        // 3. ดึงข้อมูล OHLC
        const derivData = await fetcher.getOHLC(
            'R_100',  // Volatility 100 Index
            60,       // 1 minute candles
            500       // 500 candles
        ); 

		console.log('First candle time:', new Date(derivData.candles[0].epoch * 1000));
        console.log('Current time:', new Date());
        
        console.log('📊 Raw Deriv Data:', derivData);
		document.getElementById("result1").innerHTML = JSON.stringify(derivData);
		
        
        // 4. แปลงข้อมูลให้ใช้กับ Indicator
        const indicatorData = fetcher.convertToIndicatorFormat(derivData);

		// Validate ก่อนส่งเข้าชาร์ท
        if (indicatorData.length === 0) {
            console.error('❌ No valid data');
            return;
        }

        console.log('✅ Data ready:', indicatorData.length, 'candles');
        console.log('Sample:', indicatorData.slice(0, 3));



		document.getElementById("result2").innerHTML = JSON.stringify(indicatorData);
        
        console.log('✅ Converted Data (first 3):', indicatorData.slice(0, 3));
        console.log('Total candles:', indicatorData.length);
        
        // 5. ใช้กับ SuperTrend AI Indicator
        // const indicator = new VolumeSuperTrendAI({ ... });
        // indicator.calculate(indicatorData);
        
        // 6. ปิดการเชื่อมต่อ
        fetcher.close();
        
        return indicatorData;
        
    } catch (error) {
        console.error('Error:', error);
    }
}


  $(document).ready(function () {
    console.log("Hello World!");
  });
  document.addEventListener('DOMContentLoaded', function() {
      // เนเธเนเธ”เธ—เธตเนเธ•เนเธญเธเธเธฒเธฃเนเธซเนเธ—เธณเธเธฒเธเน€เธกเธทเนเธญ DOM เนเธซเธฅเธ”เน€เธชเธฃเนเธ
      console.log('DOM fully loaded and parsed');
	  example();
  });
  
  </script>
</html>
