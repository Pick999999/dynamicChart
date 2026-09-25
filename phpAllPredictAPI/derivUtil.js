let TableBodyID = 'contractsTableBody'
// ร้องขอ รายการ Order ทั้งหมด จะได้แค่ รายการ contract_id กลับมา

// Step 1 ร้องขอ request portfolio แลัวรอ data.msg_type === 'portfolio'
function RequestPortfolio(ws) {

			const portfolioRequest = {
                        portfolio: 1
            };
            console.log('Sending portfolio request:', portfolioRequest);
            ws.send(JSON.stringify(portfolioRequest));
} // end func

function SavePriceDiff(priceDiff) {

         localStorage.setItem('priceDiff',JSON.stringify(priceDiff));

} // end func


// Step2 เมื่อได้  data.msg_type === 'portfolio'
// จะได้รายการ  portfolio->array ของ  contracts แต่เป็น รายละเอียด คร่าวๆ
// ถ้าต้องการ รายละเอียดจริงๆ ต้อง Request_Proposal_From_Portfolio

function Request_Proposal_From_Portfolio(ws,contractsList) {

     function getTrackOrder(thisContractID) {
       ws.send(JSON.stringify( {
		  proposal_open_contract: 1,
	      contract_id: thisContractID,
          subscribe : 1
        }));
		console.log('Send Track Order',thisContractID)
	 }
     CreateTableOpenProposal() ;
     // เตรียมการ เรื่อง Display Table ก่อน
	 for (let i=0;i<=contractsList.length-1 ;i++ ) {
         if (foundOnTable(contractsList[i].contract_id) ) {
			 console.log('Found-',contractsList[i].contract_id);
			 UpdateTable(contractsList[i])
		 } else {
             console.log('Not Found-',contractsList[i].contract_id);
			 InsertNewRowTable(contractsList[i]);
		 }

     }

// ส่งการ subscribe ไปเพื่อ track order แบบทุกวินาที
     for (let i=0;i<=contractsList.length-1 ;i++ ) {
         getTrackOrder(contractsList[i].contract_id) ;
     }



} // end func

function CreateTableOpenProposal() {
// ต้องสร้าง <div id="tableWrapper" class="table-wrapper" style="sdisplay: none;">
// บน page ก่อน

          st = ` <table id="tblTrade">
                    <thead>
			  <tr>
                  <th>Balance :: </th>
			      <th id="balanceShow" style="color:white"	></th>
			      <th colspan=20>
			        <button type='button' id="btnCalBalance" class="mBtn" onclick="CalTotalBalance()">Cal Balance</button></th>
			      </th>
			  </tr>
                        <tr>
                            <th>ลำดับ</th>
                            <th>Contract ID</th>
                            <th>Symbol</th>
                            <th>ประเภท</th>
                            <th>ราคาซื้อ</th>
                            <th>Payout</th>
                            <th>เวลาซื้อ</th>
                            <th>เวลาหมดอายุ</th>
                            <th>เวลาที่เหลือ</th>
							<th>Min Profit</th>
							<th>Max Profit</th>
                            <th>Target</th>
			                <th>กำไร/ขาดทุน</th>
							<th>Entry Spot</th>
							<th>B-Entry Spot</th>
                            <th>Win-Status</th>
                            <th>Win-Status</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody id=${TableBodyID}></tbody>
                </table>`

         document.getElementById("tableWrapper").innerHTML = st;

} // end func

function UpdateRowTable(ContractList) {

		  //console.log('99999999',ContractList)
          profitid = 'profit_' +  ContractList.contract_id ;
		  document.getElementById(profitid).innerHTML = ContractList.profit;

          document.getElementById("profitTxt999").value = ContractList.profit;
		  MinprofitID = 'Minprofit_' + ContractList.contract_id ;
          MaxprofitID = 'Maxprofit_' + ContractList.contract_id ;
		  if (ContractList.profit > maxProfit) {
			  maxProfit = ContractList.profit ;
              document.getElementById(MaxprofitID).innerHTML = maxProfit;
		  }
		  if (ContractList.profit < minProfit) {
			   minProfit = ContractList.profit ;
			   document.getElementById(MinprofitID).innerHTML = minProfit;
		  }

		  assetID =  'asset_' + ContractList.contract_id ;
		  document.getElementById(assetID).innerHTML = ContractList.underlying;

		  assetID =  'entrySpot_' + ContractList.contract_id ;
		  document.getElementById(assetID).innerHTML = ContractList.entry_spot;


		  expiryTime_id = 'expiryTime_' + ContractList.contract_id ;
		  document.getElementById(expiryTime_id).innerHTML =
		  formatTimestamp(ContractList.expiry_time);

		  timeRemain = calculateTimeRemaining(ContractList.expiry_time) ;
		  timeRemainID = 'remainTime_' + ContractList.contract_id ;
		  document.getElementById(timeRemainID).innerHTML = timeRemain;
		  statusID = 'winStatus_' + ContractList.contract_id ;
		  document.getElementById(statusID).innerHTML = ContractList.status;

		  if (ContractList.profit >=0 && ContractList.profit <=0.1) {
			  spotDiff = ContractList.current_spot - ContractList.entry_spot ;
			  contractTypeID =  'contract_type_' + ContractList.contract_id ;
			  actionType  = document.getElementById(contractTypeID).textContent ;
              moneyTrade = document.getElementById("moneyTrade").value ;
			  symbolID =  'asset_' + ContractList.contract_id ;
			  symbol = document.getElementById(symbolID).textContent ;


			  sObj = {
				  contractid : ContractList.contract_id,
                  symbol   : symbol ,
			      moneyTrade : moneyTrade,
                  profit : ContractList.profit,
				  spotDiff : spotDiff.toFixed(4),
                  entry_spot : ContractList.entry_spot,
				  current_spot : ContractList.current_spot,
                  actionType : actionType
			  }
			  sNew = [];
              olddata = localStorage.getItem('profitAnaly');
			  if (olddata !== '') {
			    oldData = JSON.parse(olddata);
				oldData.push(sObj)
			    sNew.push(oldData);
			  } else {
			    sNew.push(sObj);
			  }

              localStorage.setItem('profitAnaly',JSON.stringify(sNew));

              document.getElementById("analTradeProfitList").value = JSON.stringify(sNew);
			  console.log('profitAnaly',JSON.stringify(sObj)) ;


		  }


//formatTime(timestamp, format = 'full')


} // end func

function Manage_OpenProposal_Contract(OpenContractList) {






	UpdateRowTable(OpenContractList);

	for (let i=0;i<=OpenContractList.length-1 ;i++ ) {


	}






} // end func

function foundOnTable(contractid) {

const tableBody = document.getElementById("contractsTableBody");
const rows = tableBody.rows;


        found = false;
		for (let i = 0; i < rows.length; i++) {
           const row = rows[i]; // 'row' คือ Element <tr> แต่ละตัว
           //console.log(`พบแถวที่ดัชนี ${i}:`, row);
		   const ContractCell = row.cells[1].innerHTML; // เข้าถึง <td> ตัวแรกของแถว
		   if (ContractCell==contractid) {
			   found = true ; return true ;
		   }
        }
		return found ;
} // end foundOnTable


function InsertNewRowTable(OpenContractList) {
const tableBody = document.getElementById("contractsTableBody");
const rows = tableBody.rows;
    const tr = document.createElement('tr');
/*
{
    "account_id": 191869168,
    "barrier": "5736.046",
    "barrier_count": 1,
    "bid_price": 1.68,
    "buy_price": 1,
    "contract_id": 297568688928,
    "contract_type": "PUT",
    "currency": "USD",
    "current_spot": 5732.05,
    "current_spot_display_value": "5732.050",
    "current_spot_time": 1761263604,
    "date_expiry": 1761264737,
    "date_settlement": 1761264737,
    "date_start": 1761262937,
    "display_name": "Volatility 10 Index",
    "entry_spot": 5736.046,
    "entry_spot_display_value": "5736.046",
    "entry_tick": 5736.046,
    "entry_tick_display_value": "5736.046",
    "entry_tick_time": 1761262938,
    "expiry_time": 1761264737,
    "id": "48bb03f2-6609-b0fb-781c-4d977fd3ff72",
    "is_expired": 0,
    "is_forward_starting": 0,
    "is_intraday": 1,
    "is_path_dependent": 0,
    "is_settleable": 0,
    "is_sold": 0,
    "is_valid_to_cancel": 0,
    "is_valid_to_sell": 1,
    "longcode": "Win payout if Volatility 10 Index is strictly lower than entry spot at 30 minutes after contract start time.",
    "payout": 1.94,
    "profit": 0.68,
    "profit_percentage": 68,
    "purchase_time": 1761262937,
    "shortcode": "PUT_R_10_1.94_1761262937_1761264737_S0P_0",
    "status": "open",
    "transaction_ids": {
        "buy": 592503790048
    },
    "underlying": "R_10"
}
*/
	      index = rows.length ; profitClass = ''; contractTypeClass = '';
		  profitClass = OpenContractList.profit >= 0 ? 'profit-positive' : 'profit-negative';

		  trackList = localStorage.getItem('ContractList');
		  trackList = JSON.parse(trackList);
		  //targetMoney = trackList.find(x => x.contractid === OpenContractList.contract_id).target ;
		  targetMoney = 5;


          console.log('Open Contract List',OpenContractList);


		  tr.innerHTML = `
			<td><strong>${index + 1}</strong></td>
			<td id="contractID_${OpenContractList.contract_id}">${OpenContractList.contract_id}</td>
			<td id="asset_${OpenContractList.contract_id}"><strong>${OpenContractList.underlying}</strong></td>
			<td id="contract_type_${OpenContractList.contract_id}"><span class="contract-type ${contractTypeClass}">${OpenContractList.contract_type}</span></td>
			<td>${OpenContractList.buy_price}</td>
			<td>${OpenContractList.payout}</td>

			<td id="purchase_time_${OpenContractList.contract_id}">${formatTimestamp(OpenContractList.purchase_time+(7*3600))}</td>
			<td
			 id="expiryTime_${OpenContractList.contract_id}"
		 	 class="time-remaining">
		   </td>
            <td
			 id="remainTime_${OpenContractList.contract_id}"
		 	 class="time-remaining">
		   </td>


			<td id="Minprofit_${OpenContractList.contract_id}" class="${profitClass}"></td>
			<td id="Maxprofit_${OpenContractList.contract_id}" style="color:green"></td>
			<td><input type="number" class="targetClass" id="target_${OpenContractList.contract_id}" onchange="setTarget(${OpenContractList.contract_id})" value="${targetMoney}"></td>
            <td id="profit_${OpenContractList.contract_id}" class="${profitClass}"></td>
            <td id="entrySpot_${OpenContractList.contract_id}"></td>
			<td id="message_${OpenContractList.contract_id}">
			  <button class="action-btn btn-sell"
				  onclick="DrawEntry2(${OpenContractList.contract_id})">
					Draw Line Entry
			  </button>
			 </td>;
			<td id="winStatus_${OpenContractList.contract_id}"></td>

			<td id="message_${OpenContractList.contract_id}">
			  <button class="action-btn btn-sell" onclick="sellContract(ws,${OpenContractList.contract_id})">
					ขาย*
			  </button>
			 </td>`;

		     tableBody.appendChild(tr);

             thisTargetID = 'target_' + OpenContractList.contract_id ;
			 trackData  = {
               "contractid" : OpenContractList.contract_id,
               "target" : document.getElementById(thisTargetID).value
			 }

             OldTrackData = localStorage.getItem("ContractList") ;
			 OldTrackData = JSON.parse(OldTrackData);
			 if (OldTrackData) {

			   found= false;
			   for (let i=0;i<=OldTrackData.length-1 ;i++ ) {
				   //console.log(OldTrackData[i].contractid ,'===', OpenContractList.contract_id);
			       if (OldTrackData[i].contractid === OpenContractList.contract_id) {
					   found = true ;break ;
			       }
			   }
			   console.log('Found=',found);

			   if (found === false) {
                 OldTrackData.push(trackData);
			   }
             } else {
               OldTrackData = [] ;
			   OldTrackData.push(trackData);
			 }

             //OldTrackData.push(trackData);
			 localStorage.setItem("ContractList",JSON.stringify(OldTrackData));
			 return true;




} // end func

function setTarget(ContractID) {

         trackList = localStorage.getItem('ContractList');
		 trackList = JSON.parse(trackList);
		 TxtTargetID = 'target_' + ContractID ;
		 newTarget = parseFloat(document.getElementById(TxtTargetID).value) ;

		 trackList.find(x => x.contractid === ContractID).target = newTarget;
		 console.log('trackList New',trackList) ;
         localStorage.setItem("ContractList",JSON.stringify(trackList));

		 /*
		 const tableBody = document.getElementById("contractsTableBody");
         const rows = tableBody.rows;
         found = false;

		 for (let i = 0; i < rows.length; i++) {
           const row = rows[i]; // 'row' คือ Element <tr> แต่ละตัว
		   const ContractID = 'target_' +  row.cells[1].innerHTML; // เข้าถึง <td> ตัวแรกของแถว

		   if (ContractTD==TxtTargetID) {
			   alert('Found-'+ ContractTD);
			   found = true ; break ;
		   }
         }

		 if (found) {
		 }
		 return found ;
		 */





} // end func


function calculateTimeRemaining(expiryTime) {
            const now = Math.floor(Date.now() / 1000);
            const remaining = expiryTime - now;

            if (remaining <= 0) return '00:00:00';

            const hours = Math.floor(remaining / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const seconds = remaining % 60;

            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function sellContract(ws,contractId) {
			/*
            if (!confirm(`คุณต้องการขาย Contract ID: ${contractId} หรือไม่?`)) {
                return;
            }
			*/

            const sellRequest = {
                sell: contractId,
                price: 0
            };

            ws.send(JSON.stringify(sellRequest));

			thisid = 'message_' + contractId ;
			document.getElementById(thisid).innerHTML = 'ส่งคำสั่งขาย Contract แล้ว';

            //alert('ส่งคำสั่งขาย Contract แล้ว');
}

function playSoldSound() {
         const audio = new Audio('electronic-door-bell-39969.mp3');
         audio.volume = 0.5; // ปรับระดับเสียง 0.0 - 1.0
         audio.play().catch(e => console.error('Cannot play sound:', e));

}

function CalTotalBalance() {
const tableBody = document.getElementById("contractsTableBody");
const rows = tableBody.rows;

//        console.log('CalTotalBalance') ;

        found = false;
		totalBalance = 0 ;
		for (let i = 0; i < rows.length; i++) {
           const row = rows[i]; // 'row' คือ Element <tr> แต่ละตัว
           //console.log(`พบแถวที่ดัชนี ${i}:`, row);
		   profitCell = row.cells[12].innerHTML;
		   totalBalance = totalBalance + parseFloat(profitCell) ;
        }
		thaiBath = totalBalance.toFixed(2) *32 ;
		document.getElementById("balanceShow").innerHTML = totalBalance.toFixed(2) + '<hr>'+ thaiBath + ' บาท';

        thaiBathTxt = numberToThaiText(thaiBath) ;
		return thaiBathTxt ;



} // end func


function numberToThaiText(num) {
	return ;
    num = parseFloat(num).toFixed(2);
    const [baht, satang] = num.split('.');

    const thNum = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    const thUnit = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

    function readNumber(n) {
        let result = '';
        const len = n.length;
        for (let i = 0; i < len; i++) {
            const digit = parseInt(n[i]);
            const pos = len - i - 1;
            if (digit !== 0) {
                if (pos === 1 && digit === 2) {
                    result += 'ยี่';
                } else if (pos === 1 && digit === 1) {
                    result += '';
                } else if (pos === 0 && digit === 1 && len > 1) {
                    result += 'เอ็ด';
                } else {
                    result += thNum[digit];
                }
                result += thUnit[pos];
            }
        }
        return result;
    }

    let bahtText = '';
    let numStr = baht;
    while (numStr.length > 0) {
        const part = numStr.length > 6 ? numStr.slice(-6) : numStr;
        numStr = numStr.slice(0, -6);
        let segmentText = readNumber(parseInt(part).toString());
        if (segmentText) {
            bahtText = segmentText + (bahtText ? 'ล้าน' + bahtText : '');
        }
    }

    bahtText = bahtText || 'ศูนย์';
    bahtText += 'บาท';

    let satangText = '';
    if (parseInt(satang) === 0) {
        satangText = 'ถ้วน';
    } else {
        satangText = readNumber(satang) + 'สตางค์';
    }

    return bahtText + satangText;
}

function textToSpeech(text, options = {}) {
    // หยุดเสียงเดิมก่อน (ถ้ามี)
    window.speechSynthesis.cancel();

    const utterance = new SpeechSynthesisUtterance(text);

    // ตั้งค่า
    utterance.lang = options.lang || 'th-TH'; // th-TH, en-US
    utterance.rate = options.rate || 1; // 0.5-2 (ช้า-เร็ว)
    utterance.pitch = options.pitch || 1; // 0.5-2 (ต่ำ-สูง)
    utterance.volume = options.volume || 1; // 0-1

    // Events
    utterance.onstart = () => console.log('เริ่มพูด');
    utterance.onend = () => console.log('พูดเสร็จ');
    utterance.onerror = (e) => console.error('Error:', e);

    window.speechSynthesis.speak(utterance);
}

// ฟังก์ชันเปลี่ยน favicon ตามสถานะตลาด
function updateFavicon(marketStatus) {

	// up,down,sideway
    const link = document.querySelector("link[rel*='icon']") || document.createElement('link');
    link.type = 'image/x-icon';
    link.rel = 'shortcut icon';

    let iconUrl;

    switch(marketStatus.toLowerCase()) {
        case 'up':
            // เทียนเขียวขาขึ้น
            iconUrl = 'https://api.iconify.design/mdi/trending-up.svg?color=%2326a69a';
            break;
        case 'down':
            // เทียนแดงขาลง
            iconUrl = 'https://api.iconify.design/mdi/trending-down.svg?color=%23ef5350';
            break;
        case 'sideway':
            // แนวนอน/sideways
            iconUrl = 'https://api.iconify.design/mdi/trending-neutral.svg?color=%23ff9800';
            break;
        default:
            // default เป็น chart ธรรมดา
            iconUrl = 'https://api.iconify.design/mdi/chart-candlestick.svg?color=%230d6efd';
    }

    link.href = iconUrl;
    document.getElementsByTagName('head')[0].appendChild(link);
//    console.log('Favicon updated to:', marketStatus);
}

function updateFaviconEmoji(marketStatus) {
    const canvas = document.createElement('canvas');
    canvas.width = 64;
    canvas.height = 64;
    const ctx = canvas.getContext('2d');

    let emoji;
    switch(marketStatus.toLowerCase()) {
        case 'up':
            emoji = '📈'; // chart increasing
            break;
        case 'down':
            emoji = '📉'; // chart decreasing
            break;
        case 'sideway':
            emoji = '➡️'; // right arrow
            break;
        default:
            emoji = '📊'; // bar chart
    }

    ctx.font = '56px Arial';
    ctx.fillText(emoji, 4, 56);

    const link = document.querySelector("link[rel*='icon']") || document.createElement('link');
    link.type = 'image/x-icon';
    link.rel = 'shortcut icon';
    link.href = canvas.toDataURL();
    document.getElementsByTagName('head')[0].appendChild(link);
}

// ใช้งาน
/*
textToSpeech('แรงซื้อแรง');
textToSpeech('Strong Buy Pressure', { lang: 'en-US' });
textToSpeech('ระวัง อาจกลับตัวลง', { rate: 0.8 }); // พูดช้าลง
*/

/*
{
    "echo_req": {
        "portfolio": 1
    },
    "msg_type": "portfolio",
    "portfolio": {
        "contracts": [
            {
                "app_id": 66726,
                "buy_price": 1,
                "contract_id": 297570261628,
                "contract_type": "CALL",
                "currency": "USD",
                "date_start": 1761264980,
                "expiry_time": 1761266780,
                "longcode": "Win payout if Volatility 10 Index is strictly higher than entry spot at 30 minutes after contract start time.",
                "payout": 1.95,
                "purchase_time": 1761264980,
                "shortcode": "CALL_R_10_1.95_1761264980_1761266780_S0P_0",
                "symbol": "R_10",
                "transaction_id": 592506901988
            }
        ]
    }
}

*/


/*
{
    "echo_req": {
        "contract_id": 297568688928,
        "proposal_open_contract": 1,
        "subscribe": 1
    },
    "msg_type": "proposal_open_contract",
    "proposal_open_contract": {
        "account_id": 191869168,
        "barrier": "5736.046",
        "barrier_count": 1,
        "bid_price": 1.91,
        "buy_price": 1,
        "contract_id": 297568688928,
        "contract_type": "PUT",
        "currency": "USD",
        "current_spot": 5729.377,
        "current_spot_display_value": "5729.377",
        "current_spot_time": 1761264188,
        "date_expiry": 1761264737,
        "date_settlement": 1761264737,
        "date_start": 1761262937,
        "display_name": "Volatility 10 Index",
        "entry_spot": 5736.046,
        "entry_spot_display_value": "5736.046",
        "entry_tick": 5736.046,
        "entry_tick_display_value": "5736.046",
        "entry_tick_time": 1761262938,
        "expiry_time": 1761264737,
        "id": "727c4177-8fbb-9442-de64-4485a1660167",
        "is_expired": 0,
        "is_forward_starting": 0,
        "is_intraday": 1,
        "is_path_dependent": 0,
        "is_settleable": 0,
        "is_sold": 0,
        "is_valid_to_cancel": 0,
        "is_valid_to_sell": 1,
        "longcode": "Win payout if Volatility 10 Index is strictly lower than entry spot at 30 minutes after contract start time.",
        "payout": 1.94,
        "profit": 0.91,
        "profit_percentage": 91,
        "purchase_time": 1761262937,
        "shortcode": "PUT_R_10_1.94_1761262937_1761264737_S0P_0",
        "status": "open",
        "transaction_ids": {
            "buy": 592503790048
        },
        "underlying": "R_10"
    },
    "subscription": {
        "id": "727c4177-8fbb-9442-de64-4485a1660167"
    }
}

Daily Reset-->Bear Market Index,Bull Market Index


*/



