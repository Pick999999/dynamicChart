class AnalyEMATrend {


   constructor(CandleDataList) {
	   this.lastRowSeleced = 0 ;
       this.CandleData = this.convertEPOCH(CandleDataList) ;
	   //this.clsIndy = new(clsIndicator);
	   this.clsIndy  = new(clsIndicator);



   }

   setCandleData(candleArray) {
     this.CandleData = candleArray ;
	 console.log('Candle Data ',this.CandleData) ;
	 this.clsIndy.setCandleData(this.CandleData)

   }
   // Format timestamp to HH:MM
   formatTime(epoch) {
	  const date = new Date(epoch * 1000);
	  const hours = String(date.getHours()).padStart(2, '0');
	  const minutes = String(date.getMinutes()).padStart(2, '0');
	  return `${hours}:${minutes}`;
   }

   convertEPOCH(CandleDataList) {

	   const converted = CandleDataList.map(candle => ({
                    time: candle.epoch + (0*3600),
                    open: parseFloat(candle.open),
                    high: parseFloat(candle.high),
                    low: parseFloat(candle.low),
                    close: parseFloat(candle.close),
                    volume: 1,
              }));
			  for (let i=0;i<=converted.length-1 ;i++ ) {
					if (converted[i].open > converted[i].close ) {
						converted[i].Color = 'Red';
					}
					if (converted[i].open < converted[i].close ) {
						converted[i].Color = 'Green';
					}
					if (converted[i].open === converted[i].close ) {
						converted[i].Color = 'Eaual';
					}
				}

				return converted ;

   }

   AllIndicator() {

	   let lookback = 3 ;
	   let tolerance = 0.002 ;
	   let swingLookback  = 3 ;

       const ema = this.clsIndy.calculateEMAIndy(this.CandleData,3);
	   const bb  = this.clsIndy.calculateBollingerBands(this.CandleData, 20, 2);
	   const atr  = this.clsIndy.calculateATR(this.CandleData,14);
	   const vwap  = this.clsIndy.calculateVWAP(this.CandleData);
	   const swingPoints = this.clsIndy.findSwingPoints(this.CandleData, lookback = 3) ;
	   const fibo = this.clsIndy.calculateFibonacci(this.CandleData) ;
	   const pivotPoints = this.clsIndy.calculatePivotPoints(this.CandleData) ;
	   const SRZones = this.clsIndy.createSRZones(this.CandleData,tolerance,swingLookback);

	   const candleBody = this.clsIndy.analyzeCandleBody(this.CandleData)  ;

	   //console.log('bb',bb)


	   //document.getElementById("result").innerHTML = JSON.stringify(ema);
	   document.getElementById("result").innerHTML = JSON.stringify(candleBody);

   }

   // Main analysis function
  analyzeCandleData(candleData, emaShortPeriod, emaLongPeriod) {

	  //alert('aaa Candle Data Length='+candleData.length);
	  // Validate input
	  candleData = this.CandleData ;
	  if (!candleData || candleData.length === 0) {
		console.error('No candle data provided');
		return [];
	  }

	  console.log('Time Candle 0 =',candleData[0].time);



	  if (emaShortPeriod >= emaLongPeriod) {
		console.error('EMA Short period must be less than EMA Long period');
		return [];
	  }
	  //emaShort =  clsIndy.calculateEMA(currentCandleData, periodShort);

	  let emaShortValues =  emaShortData ;//this.clsIndy.calculateEMA(candleData, emaShortPeriod);
	  const emaLongValues = emaLongData ; //this.clsIndy.calculateEMA(candleData, emaLongPeriod);

	  //console.log('EMA analy Short999',emaShortValues[0].time,' ',emaShortValues[0].value);

	  //console.log('EMA Length',emaShortValues.length,' ',candleData.length);



	  const results = [];
	  const startIndex = Math.max(emaShortPeriod, emaLongPeriod) - 1;

	  for (let i = 0; i <= candleData.length-1; i++) {
		const candle = candleData[i];
		let currentEMAShort = 0 ;
		let timeCandle = candleData[i].time ;

		let val = emaShortValues[i]?.value;
		let diffEMAShort = 0 ;
        currentEMAShort = val ;
		let currentEMALong = 0 ;
		let diffEMALong = 0 ;

        let   emaShortSlopeDirection = '??' ;
		let   emaLongSlopeDirection = '??' ;
		if (i >= 2) {
			const prevEMAShort = emaShortValues[i-1].value;
		    const diffEMAShort = currentEMAShort - prevEMAShort ;
			//console.log('diffEMAShort',diffEMAShort)

		    if (diffEMAShort > 0) {
		      emaShortSlopeDirection = 'Up' ;
		    }
		    if (diffEMAShort < 0) {
			  emaShortSlopeDirection = 'Down' ;
		    }

			const prevEMALong = emaLongValues[i-1].value;
			currentEMALong = emaLongValues[i].value;
		    diffEMALong = currentEMALong - prevEMALong ;


		    if (diffEMALong > 0) {
		      emaLongSlopeDirection = 'Up' ;
		    }
		    if (diffEMALong < 0) {
			  emaLongSlopeDirection = 'Down' ;
		    }

		}



		//currentEMALong = emaLongValues[i].value;





		const currentColor = this.clsIndy.AgetCandleColor(candle);
		const previousColorBack1 = i > 0 ? this.clsIndy.AgetCandleColor(candleData[i - 1]) : 'Equal';
		const previousColorBack2 = i > 1 ? this.clsIndy.AgetCandleColor(candleData[i - 2]) : 'Equal';



		const analysis = {
		  candleTime: candle.time ,
		  candleDisplay: this.formatTime(candle.time-(7*3600)),
		  candleBody: this.clsIndy.analyzeCandleBody_OneCandle(candle),
		  emaShortValue: currentEMAShort,
          TurnType : '-',
          previousIsTurnType : '-',
		  emaShortSlopeDirection: emaShortSlopeDirection,
          emaLongValue: currentEMALong.toFixed(4),
		  emaLongSlopeValue: diffEMALong.toFixed(5),
		  emaLongSlopeDirection: emaLongSlopeDirection,
          emaLongTurnType : '-',
          LongGroupNo : 0,
		  previousIsTurnTypeLong : '-',
          UpCon : 0,
          DownCon : 0,
          emaAbove: currentEMAShort > currentEMALong ? 'emaShort' : 'emaLong',
          macd: (currentEMAShort - currentEMALong).toFixed(4),
          diverValue: 0 ,
          diverDiffValue: 0 ,
          diverDesc : '',

		  currentColor,
		  previousColorBack1,
		  previousColorBack2,
		  //emaConflict
		};

        results.push(analysis);
      }
      let LongGroupNo = 1;
	  for (let i=2;i<=results.length-1 ;i++ ) {
          let p2SlopeDirectionShort = results[i-2].emaShortSlopeDirection ;
          let p1SlopeDirectionShort = results[i-1].emaShortSlopeDirection ;
		  let curSlopeDirectionShort = results[i].emaShortSlopeDirection ;

          if (curSlopeDirectionShort ==='Down' && p1SlopeDirectionShort ==='Up' ) {
             results[i].TurnType = 'TurnDown' ;

		  }

		  if (curSlopeDirectionShort ==='Down' && p1SlopeDirectionShort ==='Up' ) {
             results[i].previousIsTurnType = 'TurnDown' ;
			 results[i-1].TurnType = 'TurnDown' ;
		  }
		  if (curSlopeDirectionShort ==='Up' && p1SlopeDirectionShort ==='Down' ) {
             results[i].previousIsTurnType = 'TurnUp' ;
			 results[i-1].TurnType = 'TurnUp' ;
		  }
		  if (curSlopeDirectionShort ==='Up') {
              results[i].UpCon =  results[i-1].UpCon+1 ;
              results[i].DownCon =  0;
		  }
		  if (curSlopeDirectionShort ==='Down') {
              results[i].DownCon =  results[i-1].DownCon+1 ;
              results[i].UpCon =  0;
		  }


	  }

	  for (let i=2;i<=results.length-1 ;i++ ) {
          let p2SlopeDirectionLong = results[i-2].emaLongSlopeDirection ;
          let p1SlopeDirectionLong = results[i-1].emaLongSlopeDirection ;
		  let curSlopeDirectionLong = results[i].emaLongSlopeDirection ;

          if (curSlopeDirectionLong ==='Down' && p1SlopeDirectionLong ==='Up' ) {
             results[i].emaLongTurnType = 'TurnDown' ;
		  }

		  if (curSlopeDirectionLong ==='Down' && p1SlopeDirectionLong ==='Up' ) {
             //results[i].previousIsTurnType = 'TurnDown' ;
			 //results[i-1].emaLongTurnType = 'TurnDown' ;
		  }
		  if (curSlopeDirectionLong ==='Up' && p1SlopeDirectionLong ==='Down' ) {
             //results[i].previousIsTurnType = 'TurnUp' ;
			 results[i-1].emaLongTurnType = 'TurnUp' ;
		  }
		  if (curSlopeDirectionLong ==='Up') {
              results[i].UpCon =  results[i-1].UpCon+1 ;
              results[i].DownCon =  0;
		  }
		  if (curSlopeDirectionLong ==='Down') {
              results[i].DownCon =  results[i-1].DownCon+1 ;
              results[i].UpCon =  0;
		  }

	  }

	  let LGroupNo = 1 ;
	  for (let i=0;i<=results.length-1 ;i++ ) {
		  if (results[i].emaLongTurnType ==='TurnDown' || results[i].emaLongTurnType ==='TurnUp') {
			  LGroupNo++ ;
		  }
		  results[i].LongGroupNo  = LGroupNo;
	  }

      console.log('All Analysis ',results)
      analysisData = results;
      return results;
   } // end AllAnlysis

   getHeadTable() {
     let st =
     `<div class="table-container stable-ocean ">
            <table id="resultsTable" style='height:300px'>
                <thead>
                    <tr>
                        <th>#</th>
		                <th>timeStamp</th>
                        <th>เวลา</th>
                        <th>สี</th>
                        <th>Candle Type</th>
                        <th>Body %</th>
                        <th>Upper Wick %</th>
                        <th>Lower Wick %</th>
                        <th>EMA3 Value</th>
                        <th>Slope Short</th>
                        <th>Turn Short</th>
                        <th>Position Short</th>
                        <th>EMA5 Value</th>
                        <th>Slope Long</th>
		                <th>Slope Direction</th>
                        <th>Turn Long</th>
		                <th>Long GroupNo</th>
                        <th>Position Long</th>
                        <th>EMA Above</th>
                        <th>MACD</th>
                        <th>EMA Cut</th>
                        <th>EMA Conflict</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
        </div>`

		//console.log(st)
        return st;



   } // end HeadTable

   getColorClass(color) {
       if (color === 'Green') return 'green';
       if (color === 'Red') return 'red';
       return 'equal';
   }

   getSlopeClass(direction) {
         if (direction === 'Up') return 'badge-up';
         if (direction === 'Down') return 'badge-down';
         return 'badge-parallel';
   }


   getAnalysisOutput(results) {

      const  headTable = this.getHeadTable() ;
	  document.getElementById("OutPut").innerHTML = headTable ;
	  const tbody = document.getElementById('tableBody');
      tbody.innerHTML = '';
      if (results.length === 0) {
        tbody.innerHTML = '<tr><td colspan="19" class="no-data">ไม่มีข้อมูล</td></tr>';
        return;
      }

      results.forEach((result, index) => {

           const row = document.createElement('tr');
		   row.id = 'row_'+ result.candleTime ;
		   row.addEventListener('click', function() {

              //alert('You clicked row with id: ' + this.id);
			  let lastRowSeleced = document.getElementById("lastRowSelected").value;
			  $("#"+lastRowSeleced).removeClass('trSelected');
			  //alert(this.lastRowSeleced) ;
			  this.lastRowSeleced = row.id ;
			  document.getElementById("lastRowSelected").value = row.id;
			  $("#"+row.id).addClass('trSelected');
               /*
			  const copiedRow = row.cloneNode(true);
			  let tableB = document.getElementById("tableB");
			  tableB.appendChild(copiedRow);
			  */


           });
//previousIsTurnType
           row.innerHTML = `
                    <td><strong>${index + 1}</strong></td>
			        <td>${result.candleTime}</td>
                    <td>${result.candleDisplay}</td>
                    <td class="${this.getColorClass(result.currentColor)}">${result.currentColor}</td>
                    <td>${result.candleBody.candleDesc}</td>
                    <td>${result.candleBody.bodyPercent}%</td>
                    <td>${result.candleBody.upperWickPercent}%</td>
                    <td>${result.candleBody.lowerWickPercent}%</td>
                    <td class="colShort">${result.emaShortValue}</td>
                    <td class="colShort"><span class="badge ${this.getSlopeClass(result.emaShortSlopeDirection)}">${result.emaShortSlopeDirection}</span></td>
                    <td class="colShort">${result.previousIsTurnType}</td>
                    <td style="font-size: 0.85em;">${result.emaShortCutPosition}</td>
                    <td class="colLong">${result.emaLongValue}</td>
			        <td class="colLong">${result.emaLongSlopeValue}</td>

                    <td class="colLong"><span class="badge ${this.getSlopeClass(result.emaLongSlopeDirection)}">${result.emaLongSlopeDirection}</span></td>
                    <td class="colLong">${result.emaLongTurnType !== 'NoTurn' ? '<span class="badge badge-turn">' + result.emaLongTurnType + '</span>' : result.emaLongTurnType}</td>
                    <td class="colLong">${result.LongGroupNo}</td>
                    <td style="font-size: 0.85em;">${result.emaLongCutPosition}</td>
                    <td><strong>${result.emaAbove === 'emaShort' ? '📈 Short' : '<span style="color:red">📉 Long</span>'}</strong></td>
                    <td>${result.macd}</td>
                    <td>${result.isEMACut !== 'No' ? '<span class="badge badge-cut">' + result.isEMACut + '</span>' : result.isEMACut}</td>
                    <td>${result.emaConflict !== 'No' ? '<span class="conflict">' + result.emaConflict + '</span>' : result.emaConflict}</td>
                `;

                tbody.appendChild(row);
            });

   }

} // end Class Analy