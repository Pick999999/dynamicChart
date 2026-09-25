class TradeByAdjacentColor {
    constructor(candleData, useMartingale = false, profitRate = 0.94, targetBalance = null) {
        this.candleData = candleData;

        this.useMartingale = useMartingale;
        this.profitRate = profitRate; // 0.94%
        this.targetBalance = targetBalance; // เป้าหมาย Balance ที่จะหยุดเทรด
        this.martingaleLevels = [1, 2, 6, 18, 54, 162, 384, 816, 1800, 3600,7000,14000,31000];
        this.currentMartingaleIndex = 0;
        this.balance = 0;
        this.winContinue = 0;
        this.lossContinue = 0;
        this.tradeResults = [];
        this.hourlyResults = [];
        this.tradeStopped = false;
        this.stopReason = '';
        this.tradesCompleted = 0;
        this.maxMoneyTradeUsed = 0;

    }

    // หาสีของแท่งเทียน
    getCandleColor(candle) {
        if (candle.close > candle.open) return 'Green';
        if (candle.close < candle.open) return 'Red';
        return 'Equal';
    }

    // กำหนด Action ตามสีของแท่งเทียน
    determineAction(currentIndex,lossContinue) {
        let candle = this.candleData[currentIndex];
        let color = candle.thisColor || this.getCandleColor(candle);
		let codeAction = '?';
		let action = '?';

		let result = {
            action : action,
            codeAction : codeAction
		}

		if (candle.time === 1755624000) {
            console.log('Step 0 ',formatTime2(candle.time),'-->',color);
	    }

		if (currentIndex >= 1) {
			let preViousCandle = this.candleData[currentIndex-1];
            let preViousColor1 = preViousCandle.thisColor ;
		}
		if (currentIndex >= 2) {
			let preViousCandle2 = this.candleData[currentIndex-2];
            let preViousColor2 = preViousCandle2.thisColor || this.getCandleColor(preViousCandle2);
		}

		if (currentIndex >= 4 && lossContinue > 3) {
			let preViousCandle = this.candleData[currentIndex-1];
            let preViousColor1 = preViousCandle.thisColor ;

			let preViousCandle2 = this.candleData[currentIndex-2];
            let preViousColor2 = preViousCandle2.thisColor ;

			let preViousCandle3 = this.candleData[currentIndex-3];
            let preViousColor3 = preViousCandle3.thisColor || this.getCandleColor(preViousCandle3);

			let preViousCandle4 = this.candleData[currentIndex-4];
            let preViousColor4 = preViousCandle4.thisColor || this.getCandleColor(preViousCandle4);

			if (this.lossContinue === 4) {


				let action = '???';
				if (color == 'Red') {
					action =  'CALL';
				}
				if (color == 'Green') {
					action = 'PUT';
				}
				result.action = action;
				result.codeAction ='A-5';
				console.log('B******************',this.lossContinue)
                return result;


			}
/*
			let action = Check4(color,preViousColor1,preViousColor2,preViousColor3,preViousColor4) ;
			if (action !='') {
				return action ;
			}
*/

			if (
				(color != preViousColor1) &&
				(preViousColor1 != preViousColor2)
			) {
				console.log('C-----------');

				let action = '???';
				if (color == 'Red') {
					action =  'CALL';
				}
				if (color == 'Green') {
					action = 'PUT';
				}
				let thisTime = formatTime2(this.candleData[currentIndex-3].time);
				result.action = action ;
				result.codeAction = codeAction ;
                return result;


			}

		}


        // ถ้าสีเป็น Equal ให้ย้อนกลับไปดูแท่งก่อนหน้า
        while (color === 'Equal' && currentIndex > 0) {
            currentIndex--;
            candle = this.candleData[currentIndex];
            color = candle.thisColor || this.getCandleColor(candle);
        }

        if (color === 'Green') result.action = 'CALL';
        if (color === 'Red') result.action = 'PUT';
		return result ;

    }

    // คำนวณจำนวนเงินที่จะเทรด
    getTradeAmount() {
        if (this.useMartingale) {
            return this.martingaleLevels[this.currentMartingaleIndex];
			//return this.martingaleLevels[this.lossContinue];
        }
        return 1;
    }

    // ตรวจสอบผลการเทรด
    checkWinStatus(action, nextColor) {
        if ((action === 'CALL' && nextColor === 'Green') ||
            (action === 'PUT' && nextColor === 'Red')) {
            return 'Win';
        }
        return 'Loss';
    }

    // ตรวจสอบว่าควรหยุดเทรดหรือไม่
    shouldStopTrading() {
        if (this.targetBalance !== null && this.balance >= this.targetBalance) {
            this.tradeStopped = true;
            this.stopReason = `Target balance reached: ${this.balance}`;
            return true;
        }
        return false;
    }
    // อัพเดท Martingale index
    updateMartingaleIndex(winStatus) {
        if (winStatus === 'Win') {
            this.currentMartingaleIndex = 0; // รีเซ็ตกลับไปเริ่มต้น
        } else {
            if (this.currentMartingaleIndex < this.martingaleLevels.length - 1) {
                this.currentMartingaleIndex++;
            }
        }
    }

    // อัพเดท Win/Loss Continue
    updateContinueCounters(winStatus) {
        if (winStatus === 'Win') {
            this.winContinue++;
            this.lossContinue = 0;
        } else {
            this.lossContinue++;
            this.winContinue = 0;
        }
    }

    // คำนวณ Profit และ Balance
    calculateProfitAndBalance(moneyTrade, winStatus) {
        let profit = 0;
        if (winStatus === 'Win') {
            profit = moneyTrade * this.profitRate;
            this.balance += profit;
        } else {
            profit = -moneyTrade;
            this.balance += profit;
        }
        return profit;
    }

    // แปลง timestamp เป็น hour
    getHourFromTimestamp(timestamp) {
        return Math.floor(timestamp / 3600) * 3600;
    }

    // สรุปผลรายชั่วโมง
    summarizeHourlyResults() {
        const hourlyData = {};

        this.tradeResults.forEach(trade => {
            const hour = this.getHourFromTimestamp(trade.time);

            if (!hourlyData[hour]) {
                hourlyData[hour] = {
                    hour: hour,
                    balance: 0,
                    maxMoneyTrade: 0,
                    maxWinContinue: 0,
                    maxLossContinue: 0,
                    trades: []
                };
            }

            hourlyData[hour].trades.push(trade);
            hourlyData[hour].balance = trade.Balance;
            hourlyData[hour].maxMoneyTrade = Math.max(hourlyData[hour].maxMoneyTrade, trade.MoneyTrade);
            hourlyData[hour].maxWinContinue = Math.max(hourlyData[hour].maxWinContinue, trade.WinContinue);
            hourlyData[hour].maxLossContinue = Math.max(hourlyData[hour].maxLossContinue, trade.LossContinue);
        });

        this.hourlyResults = Object.values(hourlyData);
        return this.hourlyResults;
    }

    // รัน การเทรด
    runTrade(startID) {
        // รีเซ็ตค่าเริ่มต้น
        this.balance = 0;
        this.winContinue = 0;
        this.lossContinue = 0;
        this.currentMartingaleIndex = 0;
        this.tradeResults = [];
        this.tradeStopped = false;
        this.stopReason = '';
        this.tradesCompleted = 0;
        this.maxMoneyTradeUsed = 0;

        for (let i = startID; i < this.candleData.length - 1; i++) {
            // ตรวจสอบว่าควรหยุดเทรดหรือไม่
            if (this.shouldStopTrading()) {
                break;
            }

            const currentCandle = this.candleData[i];
            const nextCandle = this.candleData[i + 1];

            const thisColor = currentCandle.thisColor || this.getCandleColor(currentCandle);
            const nextColor = nextCandle.thisColor || this.getCandleColor(nextCandle);
            //const action = this.determineAction(i,this.lossContinue);
			const result = this.determineAction(i,this.lossContinue);


			let action = result.action ;
			let codeAction = result.codeAction ;



            const moneyTrade = this.getTradeAmount();
            const winStatus = this.checkWinStatus(action, nextColor);
			if (currentCandle.time === 1755624000) {
                console.log('After Determine ',formatTime2(currentCandle.time),'-->',thisColor,'=',action,'--',nextColor);
			}
			const ConflictType = this.candleData[i].conflictType;
			const ConflictCon = this.candleData[i].conflictCon;

            const emaAbove = this.candleData[i].emaAbove ;
            // อัพเดท MaxMoneyTradeUsed
            this.maxMoneyTradeUsed = Math.max(this.maxMoneyTradeUsed, moneyTrade);

            this.updateContinueCounters(winStatus);
            const profit = this.calculateProfitAndBalance(moneyTrade, winStatus);

            // เก็บผลการเทรด
            const tradeResult = {
                time: currentCandle.time,
                timeDisplay: formatTime2(currentCandle.time),
                thisColor: thisColor,
                emaAbove : emaAbove,
                MoneyTrade: moneyTrade,
                Action: action,
                CodeAction : codeAction,
                nextColor: nextColor,
                WinStatus: winStatus,
                WinContinue: this.winContinue,
                LossContinue: this.lossContinue,
                ConflictType : ConflictType,
                ConflictCon : ConflictCon,
                Profit: profit,
                Balance: this.balance
            };

            this.tradeResults.push(tradeResult);
            this.tradesCompleted++;
			/*
			if (this.lossContinue >=5) {
				i = i +5 ;
				this.lossContinue = 0 ;
				this.winContinue = 0 ;
			}
			*/

            // อัพเดท Martingale
            if (this.useMartingale) {
                this.updateMartingaleIndex(winStatus);
            }

            // ตรวจสอบอีกครั้งหลังจากคำนวณ profit
            if (this.shouldStopTrading()) {
                break;
            }
        }

        // ถ้าเทรดจบโดยไม่ถึงเป้าหมาย
        if (!this.tradeStopped) {
            this.stopReason = 'All candle data processed';
        }

        // สรุปผลรายชั่วโมง
        this.summarizeHourlyResults();

        return this.tradeResults;
    }

    // ดึงผลการเทรดทั้งหมด
    getAllResults() {
        return this.tradeResults;
    }

    // ดึงสรุปรายชั่วโมง
    getHourlyResults() {
        return this.hourlyResults;
    }

    // ดึงสถิติรวม
    getSummaryStats() {
        const totalTrades = this.tradeResults.length;
        const winTrades = this.tradeResults.filter(trade => trade.WinStatus === 'Win').length;
        const lossTrades = totalTrades - winTrades;
        const winRate = totalTrades > 0 ? (winTrades / totalTrades * 100).toFixed(2) : 0;

        const maxMoneyTrade = Math.max(...this.tradeResults.map(trade => trade.MoneyTrade));
        const maxWinContinue = Math.max(...this.tradeResults.map(trade => trade.WinContinue));
        const maxLossContinue = Math.max(...this.tradeResults.map(trade => trade.LossContinue));

        return {
            totalTrades,
            winTrades,
            lossTrades,
            winRate: `${winRate}%`,
            finalBalance: this.balance,
            maxMoneyTrade,
            maxWinContinue,
            maxLossContinue,
            tradesCompleted: this.tradesCompleted,
            maxMoneyTradeUsed: this.maxMoneyTradeUsed,
            tradeStopped: this.tradeStopped,
            stopReason: this.stopReason,
            targetBalance: this.targetBalance
        };
    }

    // ดึงสถานะการหยุดเทรด
    getStopStatus() {
        return {
            stopped: this.tradeStopped,
            reason: this.stopReason,
            tradesCompleted: this.tradesCompleted,
            maxMoneyTradeUsed: this.maxMoneyTradeUsed,
            finalBalance: this.balance,
            targetBalance: this.targetBalance
        };
    }
}
/*
// ตัวอย่างการใช้งาน
const sampleData = [
    {
        "time": 1755687360,
        "open": 122.1392,
        "high": 122.1894,
        "low": 122.1252,
        "close": 122.1741,
        "thisColor": "Green",
        "emaShort": 122.15652222222222,
        "emaLong": 122.13721666666666,
        "emaDiff": 0.019305555555561682,
        "conflictType": "n"
    },
    {
        "time": 1755687420,
        "open": 122.1862,
        "high": 122.2736,
        "low": 122.1763,
        "close": 122.2241,
        "thisColor": "Green",
        "emaShort": 122.20157407407407,
        "emaLong": 122.18065833333333,
        "emaDiff": 0.020915740740747424,
        "conflictType": "n"
    }
];

// การใช้งาน
console.log("=== NoMartingale (หยุดเมื่อ Balance = 10) ===");
const trader1 = new TradeByAdjacentColor(sampleData, false, 0.94, 10);
const results1 = trader1.runTrade();
console.log("Trade Results:", results1);
console.log("Summary Stats:", trader1.getSummaryStats());
console.log("Stop Status:", trader1.getStopStatus());
console.log("Hourly Results:", trader1.getHourlyResults());

console.log("\n=== Martingale (หยุดเมื่อ Balance = 50) ===");
const trader2 = new TradeByAdjacentColor(sampleData, true, 0.94, 50);
const results2 = trader2.runTrade();
console.log("Trade Results:", results2);
console.log("Summary Stats:", trader2.getSummaryStats());
console.log("Stop Status:", trader2.getStopStatus());
console.log("Hourly Results:", trader2.getHourlyResults());

console.log("\n=== ไม่จำกัด Balance ===");
const trader3 = new TradeByAdjacentColor(sampleData, false);
const results3 = trader3.runTrade();
console.log("Summary Stats:", trader3.getSummaryStats());
*/