let resultTrade = null ;
let originalData = [];

async function doAjaxGetLab() {



	$('#spanGetLab').addClass('spinner-circle') 	;
	conflictData = JSON.parse(document.getElementById("conflictDataText").value) ;
	rawDataList = []
	for (let i=0;i<=conflictData.length-1 ;i++ ) {
        rawData = {
          "time" :conflictData[i].time ,
          "open" :conflictData[i].open ,
          "high" :conflictData[i].high ,
          "close" :conflictData[i].close
		}
		rawDataList.push(rawData);
	}

    let result ;
    let ajaxurl = 'index.php';
    let data = {
	  "Mode": 'getLab' ,
      "assetCode" : document.getElementById("assetSelect").value ,
      "rawData" : rawDataList
      //"rawData" : JSON.parse(document.getElementById("conflictDataText").value)

    } ;
	console.clear();
	console.log(data);
	return ;

    data2 = JSON.stringify(data);

    try {
        result = await $.ajax({
            url: ajaxurl,
            type: 'POST',
	    dataType: "json",
            data: data2,
	    success: function(data, textStatus, jqXHR){
              console.log(textStatus + ": " + jqXHR.status);
              // do something with data
            },
            error: function(jqXHR, textStatus, errorThrown){
			  alert(textStatus + ": " + jqXHR.status + " " + errorThrown);
              console.log(textStatus + ": " + jqXHR.status + " " + errorThrown);
            }
        });
        //alert(result);
        //alert(JSON.stringify(result));
		document.getElementById("labResultTxt").value = JSON.stringify(result);

		labResult = result ;
		lossConList = result.lossConListA ;

		/*
		st = '<table border=1 style="width:600px">';
		st += '<tr><td>No</td><td>AI Name</td><td>Max LossCon</td><td>List Index</td></tr>';
		for (let i=0;i<=lossConList.length-1 ;i++ ) {
			st += '<tr><td>'+(i+1) + '</td><td>'+ lossConList[i].aiName +'</td><td>'+ lossConList[i].maxLossCon+'</td>';
			st += '</td><td>'+ lossConList[i].indexList + ' Time= '+ result.tradeResult[lossConList[i].indexList].SuggestTimeCandle +
			'</td></tr>';

		}
		st += '</table>';
        document.getElementById("resultsContainer").innerHTML = st ;
		document.getElementById("resultsContainer").innerHTML += JSON.stringify(result) ;
		*/

        $('#spanGetLab').removeClass('spinner-circle') 	;
		for (let i=0;i<=9 ;i++ ) {
			sName =  '#winAt'+ i ;
			console.log(sName);
            $(sName).removeClass('hide');

		}


        return result;
    } catch (error) {
        console.error(error);
    }
}

function findMaxLossCon(numCheckLossCon) {

 for (let i=0;i<=resultTrade.length-1 ;i++ ) {


 }

} // end func

function addColorField(data) {
            return data.map(candle => {
                let color;
                if (candle.close > candle.open) {
                    color = 'Green';
                } else if (candle.close < candle.open) {
                    color = 'Red';
                } else {
                    color = 'Equal';
                }
                return { ...candle, color: color };
            });
        }

        function findAlternatingPatterns(dataWithColor, minLength = 6) {
            const patterns = [];

            for (let i = 0; i <= dataWithColor.length - minLength; i++) {
                let patternLength = 1;
                let currentPattern = [dataWithColor[i].color];

                // หาความยาวของรูปแบบสลับสี
                for (let j = i + 1; j < dataWithColor.length; j++) {
                    const currentColor = dataWithColor[j].color;
                    const prevColor = dataWithColor[j - 1].color;

                    // ตรวจสอบว่าเป็นการสลับสีหรือไม่ (และไม่ใช่ Equal)
                    if (currentColor !== 'Equal' && prevColor !== 'Equal' &&
                        currentColor !== prevColor) {
                        patternLength++;
                        currentPattern.push(currentColor);
                    } else {
                        break;
                    }
                }

                // เก็บรูปแบบที่มีความยาว >= minLength
                if (patternLength >= minLength) {
                    patterns.push({
                        startIndex: i,
                        endIndex: i + patternLength - 1,
                        length: patternLength,
                        pattern: currentPattern,
                        startTime: dataWithColor[i].time,
                        endTime: dataWithColor[i + patternLength - 1].time
                    });
                }
            }

            // กรองรูปแบบที่ซ้อนทับกัน (เลือกรูปแบบที่ยาวที่สุด)
            const uniquePatterns = [];
            for (let i = 0; i < patterns.length; i++) {
                let isOverlapped = false;
                for (let j = 0; j < uniquePatterns.length; j++) {
                    if (patterns[i].startIndex >= uniquePatterns[j].startIndex &&
                        patterns[i].startIndex <= uniquePatterns[j].endIndex) {
                        if (patterns[i].length <= uniquePatterns[j].length) {
                            isOverlapped = true;
                            break;
                        } else {
                            uniquePatterns.splice(j, 1);
                            j--;
                        }
                    }
                }
                if (!isOverlapped) {
                    uniquePatterns.push(patterns[i]);
                }
            }

            return uniquePatterns;
        }

function formatTimestamp(timestamp) {
            // แปลงจากรูปแบบ custom timestamp เป็นเวลาจริง
            // ถ้า 1754971320 = 04:00:00
            // คำนวณหาชั่วโมง นาที วินาที จาก timestamp

            // สมมติว่า timestamp เริ่มต้นที่ 04:00:00 เมื่อ timestamp = 1754971320
            const baseTimestamp = 1754971320; // timestamp ที่เท่ากับ 04:00:00
            const baseHour = 4; // 04:00:00

            // คำนวณความแตกต่างในวินาที
            const diffSeconds = timestamp - baseTimestamp;

            // แปลงเป็นชั่วโมง นาที วินาที
            const totalSeconds = baseHour * 3600 + diffSeconds;
            const hours = Math.floor(totalSeconds / 3600) % 24;
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            // แสดงผลในรูปแบบ HH:MM:SS
            //const timeStr = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
			const timeStr = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

            // ใช้วันที่ปัจจุบันสำหรับแสดง
            const today = new Date();
            const dateStr = today.toLocaleDateString('th-TH');

            return `${dateStr} ${timeStr}`;
        }



        function generateTable(patterns) {
            if (patterns.length === 0) {
                return '<div class="no-results">😔 ไม่พบรูปแบบการสลับสีที่มี 6 แท่งขึ้นไป</div>';
            }

            let html = '<h3>🎯 ช่วงการสลับสี (6 แท่งขึ้นไป)</h3>';
            html += '<table>';
            html += '<tr><th>ลำดับ</th><th>เวลาเริ่มต้น</th><th>เวลาสิ้นสุด</th><th>จำนวนแท่ง</th><th>รูปแบบสี</th><th>Copy</th></tr>';

            patterns.forEach((pattern, index) => {
                const patternDisplay = pattern.pattern.map(color => {
                    const colorClass = color.toLowerCase();
                    const symbol = color === 'Green' ? '🟢' : color === 'Red' ? '🔴' : '⚪';
                    return `<span class="${colorClass}">${symbol}</span>`;
                }).join(' ');

                html += '<tr>';
                html += `<td>${index + 1}</td>`;
                html += `<td>${formatTimestamp(pattern.startTime)}</td>`;
                html += `<td>${formatTimestamp(pattern.endTime)}</td>`;
                html += `<td style="text-align:center"><strong>${pattern.length}</strong></td>`;
                html += `<td class="pattern">${patternDisplay}</td>`;
                html += '</tr>';
            });

            html += '</table>';
            return html;
        }

        function analyzePattern() {
            const input = document.getElementById('conflictDataText').value.trim();
            const output = document.getElementById('resultsContainer');

            if (!input) {
                output.innerHTML = '<div class="no-results">⚠️ กรุณาใส่ข้อมูล JSON</div>';
                return;
            }

            try {
                const data = JSON.parse(input);

                if (!Array.isArray(data) || data.length === 0) {
                    output.innerHTML = '<div class="no-results">⚠️ ข้อมูลต้องเป็น Array และมีข้อมูลอย่างน้อย 1 รายการ</div>';
                    return;
                }

                // เพิ่ม color field
                const dataWithColor = addColorField(data);

                // หารูปแบบการสลับสี
                const patterns = findAlternatingPatterns(dataWithColor, 6);

                // สร้างตารางแสดงผล
                const tableHtml = generateTable(patterns);
                output.innerHTML = tableHtml;

            } catch (error) {
                output.innerHTML = '<div class="no-results">❌ Error: ข้อมูล JSON ไม่ถูกต้อง<br>' + error.message + '</div>';
            }
        }

function SaveLocal() {

         assetCode = document.getElementById("assetSelect").value ;
         startDate = document.getElementById("startDate").value ;
		 stopDate = document.getElementById("stopDate").value ;

		 timeFrame  = document.getElementById("granularitySelect").value ;
		 isUseNow = document.getElementById("useLatest").checked ;
		 CountBar = document.getElementById("countInput").value ;
		 isUsedSave = document.getElementById("useSaved").checked ;
		 ema3 = document.getElementById("ema3").value ;
		 ema5 = document.getElementById("ema5").value ;
		 emaLong = document.getElementById("emaLong").value ;
		 emaSuperLong = document.getElementById("emaSuperLong").value ;
		 isShowEMALong = document.getElementById("isShowSuperLong").checked ;

		 tradeGranu = document.getElementById("tradeGranu").value ;

		 document.getElementById("SymBols").value = assetCode;
		 document.getElementById("assetPlan2").value = assetCode;

		 MoneyTrade = document.getElementById("moneyTrade").value ;

		 //console.log(startDate,'-',stopDate)
         sObj= {
          assetCode :  assetCode,
		  startDate :  startDate,
          stopDate  :  stopDate,
          timeframe :  timeFrame,
          isUseNow  :  isUseNow,
          CountBar  :  CountBar,
		  isUsedSave : isUsedSave,
		  ema3	   : ema3,
          ema5	   : ema5,
          emaLong	   : emaLong,
          emaSuperLong : emaSuperLong,
          isShowEMALong : isShowEMALong,
          tradeGranu : tradeGranu,
          MoneyTrade : MoneyTrade
		 }

         console.log(JSON.stringify(sObj))


         localStorage.setItem('pageLab',JSON.stringify(sObj));


} // end func

function getLocal() {

         sObj =  JSON.parse(localStorage.getItem('pageLab'));
		 document.getElementById("assetSelect").value = sObj.assetCode ;
		 document.getElementById("startDate").value = sObj.startDate ;
		 document.getElementById("stopDate").value = sObj.stopDate ;
		 document.getElementById("granularitySelect").value  = sObj.timeframe ;
		 console.log(JSON.stringify(sObj));

         if (sObj.isUseNow === true ) {
		   document.getElementById("useLatest").checked  = true ;
		 }
		 if (sObj.isUsedSave === true) {
		   document.getElementById("useSaved").checked  = true ;
		 }
		 document.getElementById("ema3").value =  sObj.ema3;
         document.getElementById("ema5").value =  sObj.ema5;
		 document.getElementById("emaLong").value =  sObj.emaLong ;
		 document.getElementById("emaSuperLong").value =  sObj.emaSuperLong ;
		 document.getElementById("countInput").value =  sObj.CountBar ;

         if (sObj.isShowEMALong === true) {
		    document.getElementById("isShowSuperLong").checked  = true ;
		 }

		 document.getElementById("SymBols").value = document.getElementById("assetSelect").value ;
		 document.getElementById("tradeGranu").value = sObj.tradeGranu;
		 document.getElementById("moneyTrade").value = sObj.MoneyTrade;







} // end func

function MainCalADX() {

         derivCandleData = JSON.parse(document.getElementById("conflictDataText").value)
	     const adxResult = calculateADX(derivCandleData, 14);
		 //console.log(adxResult);
		 const adxWithDirection = calculateADXDirection(adxResult, 5);
        // console.log('ADX with Direction:', adxWithDirection);

		 // หาสัญญาณพร้อม direction analysis
        const advancedSignal = getADXSignal(adxResult);
        console.log('Advanced Signal:', advancedSignal);




} // end func



function AddMarker(source,candleSeries) {

         conflict = JSON.parse(document.getElementById("conflictDataText").value) ;

	     let isBullish = true ;
		 allMarkers = [];
		 //candleSeries.setMarkers(allMarkers);
		 for (let i=0;i<=source.length-1 ;i++ ) {
			const marker = {
                  time: source[i].time,
                  position: isBullish ? 'belowBar' : 'aboveBar',
                  color:  '#ff0080',
                  shape: 'circle',
                  text: 'L'+ (source[i].LossContinue),
                  size: 1
             };
             allMarkers.push(marker);
			 if (source[i].conflictType !== 'n' && source[i].ConflictCon > 0) {
				 thisTime = source[i].time ;
				 isBullish = false;
				 let marker2 = {
					  time: source[i].time,
					  position: isBullish ? 'belowBar' : 'aboveBar',
					  color:  '#ffff33',
					  shape: 'circle',
					  text: 'C-'+ source[i].ConflictCon,
					  size: 1
				 };
                 allMarkers.push(marker2);
			 }
		  }
/*
		  tradeV2 = JSON.parse(document.getElementById("adjacentTextV2").value) ;
		  for (let i=0;i<=tradeV2.length-1 ;i++ ) {
		      if (tradeV2[i].LossContinue >= 1) {
				  thisTime = tradeV2[i].time ;
				 isBullish = false;
				 let marker3 = {
					  time: tradeV2[i].time,
					  position:  'belowBar' ,
					  color:  '#ffff33',
					  shape: 'circle',
					  text: 'L-'+ tradeV2[i].LossContinue,
					  size: 1
				 };
                 allMarkers.push(marker3);

		      }
		  }

*/


		  candleSeries.setMarkers(allMarkers);
		  return allMarkers ;


} // end func

function determineAction(candleDataTmp) {
//"conflictType": "Bullish Conflict",
//"conflictType": "Bearish Conflict",
// ตามหลักเกณฑ์นี้ ให้ดูว่า เป็น conflict ไหม ถ้าเป็น conflict ให้ยึด emaAbove เป็นหลัก แต่ถ้าไม่
// conflict ให้ยึด Color

	    if (candleDataTmp.conflictType ==='n') { // ยึดจาก Color
          if (candleDataTmp.thisColor ==='Red') {
			 action = 'PUT'; suggestColor = 'Red';
		  } else {
             action = 'CALL'; suggestColor = 'Green';
		  }
		  caseAction= 'ByColor' ;
	    } else {
			// เกิด conflict ยึด  emaAbove
			//"conflictType": "Bullish Conflict",
            //"conflictType": "Bearish Conflict",
			if (candleDataTmp.emaAbove ==='3') {
			  action = 'CALL'; suggestColor = 'Green';
		    } else {
              action = 'PUT'; suggestColor = 'Red';
		    }
			caseAction= 'ByEMA' ;
		}
		sObj = {
		 "action" : action,
         "suggestColor" :  suggestColor ,
         "caseAction" : caseAction
		}

		return sObj ;



} // end func



function newTradeAdjacentV2() {

         console.clear();
		 candleData = JSON.parse(document.getElementById("conflictDataText").value) ;
		 tradeResults = [] ;
		 percentWin = 0.95 ;
		 martinGale = [1,2,2,2,2,2,2,3,2,2,2,2,2,2,2,2,2,2,2,4,2,2,2,2,2,2];
		 for (let i=0;i<=candleData.length-2 ;i++ ) {

			 const currentCandle = candleData[i];
             const nextCandle    = candleData[i + 1];
             const thisColor = currentCandle.thisColor ;
             const nextColor = nextCandle.thisColor ;

			 if (i>=1) {
			   lastWinCon = tradeResults[i - 1].WinContinue ;
               lastLossCon = tradeResults[i - 1].LossContinue ;
			 } else {
               lastWinCon  = 0 ;
               lastLossCon = 0 ;
			 }


             const sObj = determineAction(candleData[i]);
			 const action = sObj.action ;
			 const suggestColor = sObj.suggestColor ;
             const caseAction = sObj.caseAction ;


             const moneyTrade = martinGale[lastLossCon] ;
             winStatus = '' ;
			 if (suggestColor === nextColor) {
                 winStatus = 'Win';
			 } else {
                 winStatus = 'Loss';
			 }

			 const ConflictType = candleData[i].conflictType ;
			 const ConflictCon =  candleData[i].conflictCon  ;
			 let winContinue = 0 ;
			 if (winStatus === 'Win') {
                 winContinue = parseInt(lastWinCon) ;
				 winContinue++ ;
				 profit = moneyTrade * percentWin ;
				 //console.log(lastWinCon,'-',winContinue);

				 lossContinue = 0 ;
			 }
			 if (winStatus === 'Loss') {
                 lossContinue = parseInt(lastLossCon) ;
				 profit = moneyTrade * -1 ;
				 lossContinue++ ;
				 winContinue  = 0 ;
			 }


			 //winContinue = 0 ;
			 //lossContinue = 0 ;
			 balance= 0 ;


			 const tradeResult = {
                time: currentCandle.time,
				timeDesplay: formatTime2(currentCandle.time),
                thisColor: thisColor,
                macd: currentCandle.macd.toFixed(2),
                emaAbove : currentCandle.emaAbove,
                MoneyTrade: moneyTrade,
                Action: action,
                caseAction: caseAction,
                SuggestColor : suggestColor,
                nextColor: nextColor,
                WinStatus: winStatus,
                lastWinCon: lastWinCon,
                lastLossCon: lastLossCon,
                WinContinue: winContinue,
                LossContinue: lossContinue,
                ConflictType : ConflictType,
                ConflictCon : ConflictCon,
                Profit: profit,
                Balance: balance
            };

            tradeResults.push(tradeResult);
		 }
		 balance = 0 ;
         maxLossCon = 0 ; totalWin = 0 ; num = 0 ;
		 for (let i=0;i<=tradeResults.length-2 ;i++ ) {
			 balance = balance + parseFloat(tradeResults[i].Profit) ;
			 tradeResults[i].Balance = balance ;
			 if (tradeResults[i].Balance > 18) {
				 num++ ;
			 }
			 if (tradeResults[i].LossContinue > maxLossCon) {
				 maxLossCon = tradeResults[i].LossContinue
			 }
             if (tradeResults[i].WinStatus ==='Win') {
				 totalWin++ ;
             }
		 }


		 const maxLossContinue = Math.max(...tradeResults.map(obj => obj.LossContinue));
         const maxIndices = tradeResults.reduce((indices, obj, i) => {
         if (obj.LossContinue === maxLossContinue) indices.push(i);
              return indices;
         }, []);
		 console.log('Indices',maxIndices);
		 maxIndicesSt = maxIndices.join(',');
		 maxIndicesTime = '';
		 for (let i=0;i<=maxIndices.length-1 ;i++ ) {
			 maxIndicesTime += formatTime2(tradeResults[i].time);
		 }



         const balanceWant  = tradeResults.filter(object => parseFloat(object.balance) > 0);
		 //console.log(balanceWant.length);





		 //console.log('tradeResults V2', tradeResults) ;
		 percentWin =  0 ;
		 //alert('tradeResults V2 MaxLossCon='+ maxLossCon);
		 st = 'Adjacent Method V2 :: Total Trade =868 ' ;
		 st += 'Total Win = ' + totalWin + '('+ totalWin/tradeResults.length + ' %'
		 st += ' Balance = ' + balance.toFixed(2) ;
		 st += ' Max Loss Con = ' + maxLossCon + ' AT ' + maxIndicesTime ;
		 st += ' Max Money Trade='   ;
		 document.getElementById("statusText3").innerHTML = st ;
		 document.getElementById("adjacentTextV2").value = JSON.stringify(tradeResults);
		 generateTableTrade('adjacentTextV2','resultsContainer');




} // end func

async function AjaxSaveToRawData(candleData){

	if (document.getElementById("useSaved").checked === false) {
		return ;
	}

	st = 'ต้องการบันทึก RawData ?' ;
	if (!confirm(st)) {
		return ;
	}

    let result ;
    let ajaxurl = 'AjaxSaveRawData.php';
    let data = { "Mode": 'SaveRawData' ,
    "candleData" : candleData,

    } ;
    data2 = JSON.stringify(data);
	//alert(data2);
    try {
        result = await $.ajax({
            url: ajaxurl,
            type: 'POST',
	    //dataType: "json",
            data: data2,
	    success: function(data, textStatus, jqXHR){
              console.log(textStatus + ": " + jqXHR.status);
              // do something with data
            },
            error: function(jqXHR, textStatus, errorThrown){
			  alert(textStatus + ": " + jqXHR.status + " " + errorThrown);
              console.log(textStatus + ": " + jqXHR.status + " " + errorThrown);
            }
        });

       // alert(result);


        return result;
    } catch (error) {
        console.error(error);
    }
}

function searchEMA(candleTime,emaData) {

	     thisema = null;
         for (let i2=0;i2<=emaData.length-1 ;i2++ ) {
			if (emaData[i2].time === candleTime) {
			  thisema = emaData[i2].value ;
              found=true ;break ;
			  }
         } // i2
		 return thisema ;



} // end func



function MixedDataAll(candleData,ema3Data,ema5Data)  {

         let all = [];
		 //console.log('Mix Started',ema3Data)

	     for (let i=0;i<=candleData.length-1 ;i++ ) {
			 found = false;
			 thisema3 = null ;thisema5 = null ;
			 thisema3 = searchEMA(candleData[i].time,ema3Data)
             thisema5 = searchEMA(candleData[i].time,ema5Data)


			 sObj = {
               epoch : candleData[i].time ,
			   open  : candleData[i].open ,
               close : candleData[i].close ,
               high  : candleData[i].high ,
               low   : candleData[i].low ,
               color : candleData[i].color,
			   ema3  : thisema3,
               ema5  : thisema5,
			 }
             all.push(sObj)

	     }


		 return all ;



} // end func

function placeTrade(ws) {

	     // send Auth
		 // Send proposal
		 // send Trade



} // end func

function findCutPoint(emaShort, emaLong) {
    const cutPoints = [];

    // ตรวจสอบว่ามีข้อมูลครบหรือไม่
    if (!emaShort || !emaLong || emaShort.length < 2 || emaLong.length < 2) {
        return cutPoints;
    }

    // วนลูปเช็คทุกจุดที่มีเวลาตรงกัน
    for (let i = 1; i < emaShort.length; i++) {
        const shortCurrent = emaShort[i];
        const shortPrevious = emaShort[i - 1];

        // หา emaLong ที่มีเวลาตรงกัน
        const longCurrent = emaLong.find(item => item.time === shortCurrent.time);
        const longPrevious = emaLong.find(item => item.time === shortPrevious.time);

        if (!longCurrent || !longPrevious) continue;

        // เช็คว่ามีการตัดกันหรือไม่
        const previousDiff = shortPrevious.value - longPrevious.value;
        const currentDiff = shortCurrent.value - longCurrent.value;

        // ถ้าเครื่องหมายเปลี่ยน = มีการตัดกัน
        if (previousDiff * currentDiff < 0) {
            let crossType = '';
            let trendDirection = '';

            // กำหนดประเภทการตัด
            if (previousDiff < 0 && currentDiff > 0) {
                // EMA Short ตัดขึ้นเหนือ EMA Long
                crossType = 'Golden Cross'; // ตัดขึ้น
                trendDirection = 'Bullish'; // สัญญาณขาขึ้น
            } else if (previousDiff > 0 && currentDiff < 0) {
                // EMA Short ตัดลงต่ำกว่า EMA Long
                crossType = 'Death Cross'; // ตัดลง
                trendDirection = 'Bearish'; // สัญญาณขาลง
            }

            // คำนวณจุดตัดโดยประมาณ (Linear Interpolation)
            const ratio = Math.abs(previousDiff) / (Math.abs(previousDiff) + Math.abs(currentDiff));
            const crossTime = shortPrevious.time + (shortCurrent.time - shortPrevious.time) * ratio;
            const crossValue = shortPrevious.value + (shortCurrent.value - shortPrevious.value) * ratio;

            cutPoints.push({
                time: Math.round(crossTime),
                timestamp: new Date(Math.round(crossTime) * 1000).toISOString(),
                value: crossValue,
                crossType: crossType,
                trendDirection: trendDirection,
                emaShortValue: shortCurrent.value,
                emaLongValue: longCurrent.value,
                emaShortSlope: shortCurrent.slope,
                emaLongSlope: longCurrent.slope,
                emaShortDirection: shortCurrent.direction,
                emaLongDirection: longCurrent.direction,
                priceDifference: currentDiff,
                percentDifference: ((currentDiff / longCurrent.value) * 100).toFixed(4),
                description: crossType === 'Golden Cross'
                    ? 'EMA สั้นตัดขึ้นข้าม EMA ยาว - สัญญาณซื้อ (Bullish)'
                    : 'EMA สั้นตัดลงข้าม EMA ยาว - สัญญาณขาย (Bearish)'
            });
        }
    }

    return cutPoints;
}

// ตัวอย่างการใช้งาน:
/*
const emaShort = [
    { time: 1761650400, value: 5688.5, slope: 0.0015, direction: "up" },
    { time: 1761654000, value: 5689.5, slope: 0.0018, direction: "up" },
    { time: 1761657600, value: 5690.8, slope: 0.0020, direction: "up" }
];

const emaLong = [
    { time: 1761650400, value: 5689.0, slope: 0.0012, direction: "up" },
    { time: 1761654000, value: 5689.2, slope: 0.0013, direction: "up" },
    { time: 1761657600, value: 5689.5, slope: 0.0014, direction: "up" }
];

const cutPoints = findCutPoint(emaShort, emaLong);
console.log(cutPoints);

// ผลลัพธ์:
[
    {
        time: 1761654800,
        timestamp: "2025-10-27T12:30:00.000Z",
        value: 5689.85,
        crossType: "Golden Cross",
        trendDirection: "Bullish",
        emaShortValue: 5690.8,
        emaLongValue: 5689.5,
        emaShortSlope: 0.0020,
        emaLongSlope: 0.0014,
        emaShortDirection: "up",
        emaLongDirection: "up",
        priceDifference: 1.3,
        percentDifference: "0.0228",
        description: "EMA สั้นตัดขึ้นข้าม EMA ยาว - สัญญาณซื้อ (Bullish)"
    }
]
*/

function fillUpDownMeter(currentClose,previousClose) {






			  closeA1 = parseFloat(previousClose) ;
			  closeA2 = parseFloat(currentClose) ;
			  diff = currentClose-previousClose ;
			  ch   = '';
			  if (diff < 0) {
				  ch = '🔴D';
			  }
			  if (diff > 0) {
				  ch = '🟢U';
			  }
			  priceMeter.push(ch);
			  if (priceMeter.length > 25) {
				  let priceMeter2 = priceMeter.slice(1);
				  priceMeter = priceMeter2 ;
			  }
			  let totalRed = 0  ;
			  let totalGreen  = 0 ;
			  let allTotal = 0 ;
			  let PercentRed =0 ;
			  let PercentGreen = 0 ;
              if (priceMeter.length > 10) {
			    for (let i=priceMeter.length-10;i<=priceMeter.length-1 ;i++ ) {
				  if (priceMeter[i]==='🔴D') {
					  totalRed++ ;
				  }
				  if (priceMeter[i]==='🟢U') {
					  totalGreen++ ;
				  }
				  allTotal++;
			    } // end for
				allTotal = totalRed+ totalGreen;
				PercentRed = (totalRed/allTotal)*100;
				PercentGreen = (totalGreen/allTotal)*100;

			  } //end if

			  st = priceMeter.join(' ');
			  document.getElementById("priceMeterPercent").innerHTML =
				  'AllTotal=' + allTotal +' Green='+ totalGreen + ' Red='+ totalRed + ' 🟢 ='+ PercentGreen.toFixed(2)+ ' %  🔴=' + PercentRed.toFixed(2) + ' %' ;
			  document.getElementById("priceMeterDiv").innerHTML = st;






} // end func



$(document).ready(function () {
	//SaveLocal();
    $("#volatilityBtn").trigger("click");
	//alert('998989');
	getLocal();
	$("#btnHistoryData").trigger("click");



});
document.addEventListener('DOMContentLoaded', function() {
    // เนเธเนเธ”เธ—เธตเนเธ•เนเธญเธเธเธฒเธฃเนเธซเนเธ—เธณเธเธฒเธเน€เธกเธทเนเธญ DOM เนเธซเธฅเธ”เน€เธชเธฃเนเธ
//    console.log('DOM fully loaded and parsed');
});

