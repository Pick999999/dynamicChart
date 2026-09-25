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
  .flex { height:auto; smin-height:100px;soverflow:scroll}
  </style>
  
  

 </head>
 <body>

  <button type='button' id='' class='mBtn' onclick="connectDeriv()">Connect</button> 

  <button type='button' id='' class='mBtn' onclick="disconnectDeriv()">Close Connect</button> 

  <button type='button' id='' class='mBtn' onclick="reConnect()">reConnect</button> 
  <button type='button' id='' class='mBtn' onclick="subscribeToTime()">Start Track Time</button>
  <button type='button' id='' class='mBtn' onclick="StopSubScribeTime()">Stop Track Time</button>
  <button type='button' id='' class='mBtn' onclick="subscribeCandles('R_100', 60)">Start Track Candle</button>

  Request Type :: 
  <select id= "requestType" onclick='displayRequestJson(this.value)'>
	<option value="timeserver" selected>Time Server
	<option value="auth">Auth
	<option value="auth">Candle History
	<option value="auth">Candle Latest
	<option value="auth">Candle OHLC
    <option value="auth">Proposal To Buy
    <option value="auth">Order/Buy
	<option value="auth">Track Order
	<option value="auth">Sale
  </select>


  Server Time :: 
  <div id="serverTime" class="bordergray flex">
       
  </div>

  <div id="result1" class="bordergray flex">
       timeCandle :: <span id='timeCandle1' style='color:red;font-weight:bold'></span><BR>
	   timeCandle-2 :: <span id='timeCandleOhlc' style='color:red;font-weight:bold'></span><br>
	   Mode : <span id= 'ModeCandle' style='color:red;font-weight:bold'></span>
  </div>

  <div class="box" style='display:none'>
    <div  class="box-label">ตัวอย่าง Json</div>
    <div id= 'resultJson' class="bordergray flex">
      <div id="jsonRequestsCaption" class="bordergray flex" style='height:50px'>
      </div>
      <textarea id="requestJsonTxt" style='width:100%;min-height:150px;height:auto;padding:10px'></textarea>
	  <div id="jsonResponseCaption" class="bordergray flex" style='height:50px'>
      </div>
      <textarea id="responseJsonTxt" style='width:100%;min-height:150px;height:auto;padding:10px'></textarea>
  </div>  

  



</div>


<script>
// ตัวแปรสำหรับจัดการ WebSocket
var derivWS = null;
var isConnected = false;
var reconnectAttempts = 0;
var maxReconnectAttempts = 10;
var reconnectDelay = 1000;
var maxReconnectDelay = 30000;
var shouldReconnect = true;
var messageQueue = [];
var pingInterval = null;
var pingIntervalTime = 30000;
var appId = 'YOUR_APP_ID'; // ใส่ App ID ของคุณ
var endpoint = 'wss://ws.binaryws.com/websockets/v3?app_id=1089';

let timeCandle = 0 ;
let timeOHLC = 0 ;	
let currentCandle = {} ;

// Event callbacks
var callbacks = {
  onConnect: null,
  onDisconnect: null,
  onMessage: null,
  onError: null,
  onReconnecting: null,
  onReconnectFailed: null
};

// ฟังก์ชันเชื่อมต่อ
function connectDeriv() {
  appId = 1089 ;
  //var url = 'wss://' + endpoint + '/websockets/v3?app_id=' + appId;
  var url = endpoint ;
  console.log('กำลังเชื่อมต่อไปยัง:', url);
  
  derivWS = new WebSocket(url);

  derivWS.onopen = function() {
    console.log('✓ เชื่อมต่อสำเร็จ');
    isConnected = true;
    reconnectAttempts = 0;
    reconnectDelay = 1000;
    
    flushMessageQueue();
    startPingInterval();
    
    if (callbacks.onConnect) {
      callbacks.onConnect();
    }
  };

  
  derivWS.onmessage = function(event) {

    try {
      var data = JSON.parse(event.data);
	  //document.getElementById("timeCandle1").innerHTML += data.msg_type+ ' : ';
	  if (data.msg_type=='candles') {
		//console.log('',data.candles)		  
        lastIndex = data.candles.length -1;
        lastCandle = data.candles[lastIndex] ;
		console.log('lastCandle',lastCandle) ;
		timeCandle = lastCandle.epoch ;
        document.getElementById("timeCandle1").innerHTML += displayTime(lastCandle.epoch) + ' : '
	  }

	  if (data.msg_type=='ohlc') {
		   console.log('',data.ohlc)		  
		   timeOHLC = data.ohlc.epoch;
           document.getElementById("timeCandleOhlc").innerHTML = displayTime(timeOHLC);
		   if (timeOHLC < timeCandle +60 ) {
			   ModeCandle = 'Update'  ;			   
			   document.getElementById("ModeCandle").innerHTML = ModeCandle;
		   } else {
               ModeCandle = 'New Candle'  ;			   
			   document.getElementById("ModeCandle").innerHTML = ModeCandle;
			   timeCandle = timeOHLC ;
		   }

		   
		   
              
	  }
      return;   
	  
      console.log('← ได้รับข้อความ:', data);
      
      if (callbacks.onMessage) {
        callbacks.onMessage(data);
      }
    } catch (error) {
      console.error('ข้อผิดพลาดในการ parse ข้อความ:', error);
    }
  };

  derivWS.onerror = function(error) {
    console.error('× WebSocket error:', error);
    
    if (callbacks.onError) {
      callbacks.onError(error);
    }
  };

  derivWS.onclose = function(event) {
    console.log('× การเชื่อมต่อปิด:', event.code, event.reason);
    isConnected = false;
    stopPingInterval();
    
    if (callbacks.onDisconnect) {
      callbacks.onDisconnect(event);
    }
    
    if (shouldReconnect) {
      handleReconnect();
    }
  };
}

// ฟังก์ชัน reconnect
function handleReconnect() {
  if (reconnectAttempts >= maxReconnectAttempts) {
    console.error('× ครบจำนวนครั้งในการ reconnect แล้ว');
    
    if (callbacks.onReconnectFailed) {
      callbacks.onReconnectFailed();
    }
    return;
  }

  reconnectAttempts++;
  var delay = Math.min(
    reconnectDelay * Math.pow(2, reconnectAttempts - 1),
    maxReconnectDelay
  );

  console.log('⟳ กำลัง reconnect (ครั้งที่ ' + reconnectAttempts + '/' + maxReconnectAttempts + ') ใน ' + (delay/1000) + ' วินาที...');

  if (callbacks.onReconnecting) {
    callbacks.onReconnecting({
      attempt: reconnectAttempts,
      delay: delay
    });
  }

  setTimeout(function() {
    if (shouldReconnect) {
      connectDeriv();
    }
  }, delay);
}

// ฟังก์ชันส่งข้อความ
function sendMessage(data) {
  var message = typeof data === 'string' ? data : JSON.stringify(data);
  
  if (isConnected && derivWS.readyState === WebSocket.OPEN) {
    console.log('→ ส่งข้อความ:', data);
    derivWS.send(message);
  } else {
    console.log('↓ เก็บข้อความไว้ในคิว (ยังไม่ได้เชื่อมต่อ)');
    messageQueue.push(message);
  }
}

// ส่งข้อความในคิว
function flushMessageQueue() {
  if (messageQueue.length > 0) {
    console.log('กำลังส่งข้อความในคิว (' + messageQueue.length + ' ข้อความ)');
    while (messageQueue.length > 0) {
      var message = messageQueue.shift();
      derivWS.send(message);
    }
  }
}

// เริ่ม ping interval
function startPingInterval() {
  stopPingInterval();
  pingInterval = setInterval(function() {
    if (isConnected) {
      sendMessage({ ping: 1 });
    }
  }, pingIntervalTime);
}

// หยุด ping interval
function stopPingInterval() {
  if (pingInterval) {
    clearInterval(pingInterval);
    pingInterval = null;
  }
}

// ฟังก์ชันยกเลิกการเชื่อมต่อ
function disconnectDeriv() {
  console.log('กำลังยกเลิกการเชื่อมต่อ...');
  shouldReconnect = false;
  stopPingInterval();
  
  if (derivWS) {
    derivWS.close();
  }
}

// ฟังก์ชัน helper สำหรับ API calls
function authorize(token) {
  sendMessage({
    authorize: token
  });
}

function subscribeTicks(symbol) {
  return;
  sendMessage({
    ticks: symbol,
    subscribe: 1
  });
}

function subscribeCandles(symbol, interval) {
  if (!interval) interval = 60;
  
  sendMessage({
    ticks_history: symbol,
    adjust_start_time: 1,
    count: 10,
    end: 'latest',
    start: 1,
    style: 'candles',
    granularity: interval,
    subscribe: 1
  });
}

function getActiveSymbols() {
  sendMessage({
    active_symbols: 'brief',
    product_type: 'basic'
  });
}

function getBalance() {
  sendMessage({
    balance: 1,
    subscribe: 1
  });
}

// ตั้งค่า callbacks
function setOnConnect(callback) {
  callbacks.onConnect = callback;
}

function setOnDisconnect(callback) {
  callbacks.onDisconnect = callback;
}

function setOnMessage(callback) {
  callbacks.onMessage = callback;
}

function setOnError(callback) {
  callbacks.onError = callback;
}

function setOnReconnecting(callback) {
  callbacks.onReconnecting = callback;
}

function setOnReconnectFailed(callback) {
  callbacks.onReconnectFailed = callback;
}

function displayRequestJson(thisvalue) {
	     
		 if (thisvalue === 'timeserver') {
            st = 'ws.send(JSON.stringify({ "time": 1}));' ;
			st += '<hr><h2>Requests TimeServer</h2>';
			st += 'request = { "time": 1} ; <br>' ;
			st += 'ws.send(JSON.stringify(request));' ;
			st += '<hr><h2>Response TimeServer</h2>';
		 }

         document.getElementById("jsonRequestsCaption").innerHTML = '<h2>Time Server Requests</h2>';         
		 res = getRequestsTimeserver();
		 document.getElementById("requestJsonTxt").value = res.stRequest;

         document.getElementById("jsonResponseCaption").innerHTML = '<h2>Time Server Response</h2>';           
		 document.getElementById("responseJsonTxt").value = res.stResponse;

		 


} // end func

function getRequestsTimeserver() {

	st = `     timeSubscription = setInterval(() => {
           if (ws && ws.readyState === WebSocket.OPEN) {
             // ส่ง request time
             ws.send(JSON.stringify({
               "time": 1
             }));                      
           }
    }, 1000);`
    
	st2 = `ใส่ ส่วนนี้ ลงใน html div
serverTime :: <span id='serverTime' style='color:red;font-weight:bold'></span>
// ใส่ใน javascript ส่วน ws.onmessage
            if (data.time) {
              updateServerTime(data.time);			
			  
            }
// ใส่ใน javascript นอก ส่วน ws.onmessage

function updateServerTime(timestamp) {

		   const date = new Date(timestamp * 1000);
		   const timeStr = date.toLocaleTimeString();
		   document.getElementById('serverTime').textContent = timeStr;

		   if (date.getSeconds() === 0) {
//			  fetchCandles();
		   }
}`;
	 

    stObj = {
      "stRequest" : st,
      "stResponse" : st2
	}

    return stObj;

} // end func

function displayTime(timestamp) {

		 const date = new Date(timestamp * 1000);
		 const timeStr = date.toLocaleTimeString();

		 return timeStr ;
		   
}


// ========================================
// ตัวอย่างการใช้งาน
// ========================================

// ตั้งค่า callbacks
setOnConnect(function() {
  console.log('🎉 เชื่อมต่อสำเร็จ!');
  
  // ขอข้อมูล active symbols
  //getActiveSymbols();
  
  // Subscribe ticks
  //subscribeTicks('R_100');
});

setOnDisconnect(function(event) {
  console.log('💔 ตัดการเชื่อมต่อแล้ว');
});

setOnReconnecting(function(info) {
  console.log('🔄 กำลัง reconnect ครั้งที่ ' + info.attempt + '...');
});

setOnReconnectFailed(function() {
  console.log('❌ Reconnect ล้มเหลว');
});

setOnMessage(function(data) {
  console.log('ข้อความใหม่:', data);
  
  // จัดการข้อความตาม msg_type
  if (data.msg_type === 'tick') {
    console.log('Tick:', data.tick);
  } else if (data.msg_type === 'active_symbols') {
    console.log('Active Symbols:', data.active_symbols);
  }
});

setOnError(function(error) {
  console.error('เกิดข้อผิดพลาด:', error);
});

// เริ่มการเชื่อมต่อ
//connectDeriv();

// ตัดการเชื่อมต่อเมื่อต้องการ (เรียกฟังก์ชันนี้)
// disconnectDeriv();

</script>



 </body>
</html>




<script>
