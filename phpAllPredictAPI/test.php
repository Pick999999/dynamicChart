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
  <button type='button' id='' class='mBtn' onclick="fff()">Start Track</button>

<script>

let chart, candleSeries, ema3Series, ema5Series, emaLongSeries;
let ws = null;
let tooltipEnabled = true;
let realTimeEnabled = false;
let selectedCandle = null;
let candleData = [];
		
let ema3Data = [], ema5Data = [];
let ema3Values = [], ema5Values = []; emaLongValues = [];
let markersLoss = [] ;
let labResult = null ;
let conflictData =  null; 
let mixedData = null ;
const ws = new WebSocket('wss://ws.binaryws.com/websockets/v3?app_id=YOUR_APP_ID');

ws.onopen = () => {
    // Authorize เธ”เนเธงเธข API Token
    ws.send(JSON.stringify({
        "authorize": "YOUR_API_TOKEN"
    }));
};

ws.onmessage = (event) => {
    const data = JSON.parse(event.data);

    if (data.time) {
        updateServerTime(data.time);
    }

    if (data.msg_type === 'authorize') {
        console.log('Authorized successfully.');

        // เธ”เธถเธเธฃเธฒเธขเธเธฒเธฃเธชเธ–เธฒเธเธฐเธ—เธตเนเน€เธเธดเธ”เธญเธขเธนเน (Active Multipliers Contracts)
        ws.send(JSON.stringify({
            "portfolio": 1
        }));
    }
    if (data.msg_type === 'candles') {
        
    }
    
    // เธ•เธญเธเธเธฅเธฑเธ proposal เนเธ”เธขเน€เธเนเธฒ Buy  เนเธ”เนเธเธฒเธ PlaceTradeProposal
    if (data.proposal) {
       // เธ•เธฑเธงเธญเธขเนเธฒเธ: proposal.proposal.id เธซเธฃเธทเธญ data.proposal.id (เธเธถเนเธเธเธฑเธ response)
       const proposalId = data.proposal.proposal?.id || data.proposal.id;
       const askPrice = data.proposal.proposal?.ask_price || data.proposal.ask_price;       
       PlaceBuyOrder(proposalId) ;
     }

     // เธเธฑเธ”เธเธฒเธฃเธเธฅเธฅเธฑเธเธเนเธเธฒเธฃเธเธทเนเธญ / เน€เธเธดเธ” Order เธ—เธตเนเธเธญเน€เธเธดเธ”เนเธเนเธฅเนเธง   (เธกเธต contract_id)
     if (data.buy) {
         console.log("Bought contract:", data.buy.contract_id);
	 document.getElementById("ContractList").innerHTML = data.buy.contract_id;
	 // เธ—เธณเธเธฒเธฃ TrackOrder เนเธ”เธข เธฃเนเธญเธเธเธญ data.proposal_open_contract
	 thisContractID = data.buy.contract_id ;
	 getTrackOrder(thisContractID) ;
     }
     
     // เธฃเธฒเธขเธเธฒเธฃ Order เธ—เธตเนเธ–เธนเธ Track เนเธเนเธฅเนเธง 
     if (data.proposal_open_contract) {
        alert('Yes')  ;
        const contractList = data.proposal_open_contract;                  
	entry_tick = contractList.entry_tick ;
	
     } 

     // เน€เธเธดเธ”เน€เธกเธทเนเธญเธชเนเธเธเธณเธชเธฑเนเธเธเธฒเธข Sale เนเธ  เนเธฅเธฐเธฃเธญ เธชเธ–เธฒเธเธฐ เธเธฒเธฃเธเธฒเธขเนเธ”เน 
     if (data.msg_type === 'sell') {
        // เธเธฅเธฅเธฑเธเธเนเธเธฒเธเธเธณเธชเธฑเนเธเธเธดเธ”เธชเธ–เธฒเธเธฐ
        console.log('Sell response:', data);
     } else {
        console.log('Received message:', data);
     }
			}



    // เธฃเธฒเธขเธเธฒเธฃเน€เธ—เธฃเธ”เธ—เธตเนเน€เธเธดเธ”เธญเธขเธนเน 
    if (data.msg_type === 'portfolio') {
        console.log('Active contracts:', data);

        if (data.portfolio && data.portfolio.contracts.length > 0) {
            // เธเธดเธ”เธชเธ–เธฒเธเธฐ Multipliers Order เธ•เธฑเธงเนเธฃเธ
            const contractId = data.portfolio.contracts[0].contract_id; // เน€เธฅเธทเธญเธ contract_id เธ•เธฑเธงเนเธฃเธ
            console.log('Closing contract with ID:', contractId);

            ws.send(JSON.stringify({
                "sell": contractId,
                "price": 0 // เธเธดเธ”เธชเธ–เธฒเธเธฐเธ—เธตเนเธฃเธฒเธเธฒเธ•เธฅเธฒเธ”เธเธฑเธเธเธธเธเธฑเธ
            }));
        } else {
            console.log('No active contracts to close.');
        }
     } 
     
};

ws.onerror = (error) => {
    console.error('WebSocket Error:', error);
};


function subscribeToTime() {
   if (timeSubscription) {
      clearInterval(timeSubscription);
   }

   websocket.send(JSON.stringify({
      "time": 1
   }));
   timeSubscription = setInterval(() => {
      if (websocket && websocket.readyState === WebSocket.OPEN) {
         websocket.send(JSON.stringify({
            "time": 1
         }));
      }
   }, 1000);
}
function updateServerTime(timestamp) {

   const date = new Date(timestamp * 1000);
   const timeStr = date.toLocaleTimeString();
   document.getElementById('serverTime').textContent = timeStr;

   if (date.getSeconds() === 0) {
      fetchCandles();
   }
}

// เธฃเนเธญเธเธเธญ Proposal เธเนเธญเธเธ—เธณเธเธฒเธฃ Buy
function PlaceTradeProposal(action) {

			 amount = 30 ;
			 contractType = action ;	
			 tradeGranuSelected0 = document.getElementById("tradeGranu").value ;
			 tradeGranuSelected = tradeGranuSelected0.split('-');

             console.log('tradeGranuSelected',tradeGranuSelected) ;
			
                
			 duration = tradeGranuSelected[0] ;
			 durationUnit = tradeGranuSelected[1] ;

			 //symbol = document.getElementById("assetSelect").value ;
             symbol = document.getElementById("SymBols").value ;
			 amount = document.getElementById("moneyTrade").value  ;
			 

			 const proposalReq = {
             buy: 1,
             price: parseFloat(amount),
             parameters: {
               amount: parseFloat(amount),
               basis: "stake",
               contract_type: contractType,
               currency: "USD",
               duration: parseInt(duration),
               duration_unit: durationUnit,
               symbol: symbol
             }
            };
            ws.send(JSON.stringify(proposalReq));
         
		
} // end func

// เน€เธเนเธฒ Buy เธเธทเนเธญเธ—เธฑเธเธ—เธตเธ—เธตเนเนเธ”เน proposal->data.buy
function PlaceBuyOrder(proposalId)  {

       if (proposalId) {
          ws.send(JSON.stringify({ buy: proposalId }));
       }
}

function getTrackOrder(thisContractID) {

         ws.send(JSON.stringify( { 
	   proposal_open_contract: 1, 
	   contract_id: thisContractID, 
         }));
		
} // end func

function sellContract(contractId) {
			/*
            if (!confirm(`เธเธธเธ“เธ•เนเธญเธเธเธฒเธฃเธเธฒเธข Contract ID: ${contractId} เธซเธฃเธทเธญเนเธกเน?`)) {
                return;
            }
			*/

            const sellRequest = {
                sell: contractId,
                price: 0
            };

            ws.send(JSON.stringify(sellRequest));

			thisid = 'message_' + contractId ;
			document.getElementById(thisid).innerHTML = 'เธชเนเธเธเธณเธชเธฑเนเธเธเธฒเธข Contract เนเธฅเนเธง';
			
            //alert('เธชเนเธเธเธณเธชเธฑเนเธเธเธฒเธข Contract เนเธฅเนเธง');
}






</script>



 </body>
</html>
