class TradeByEmaTrend {
    constructor(candleData, useMartingale = false, profitRate = 0.94, targetBalance = null, startIndex = 0, stopIndex = null) {
        this.candleData = candleData;
        this.useMartingale = useMartingale;
        this.profitRate = profitRate; // 0.94%
        this.targetBalance = targetBalance; // เป้าหมาย Balance ที่จะหยุดเทรด
        this.startIndex = startIndex; // จุดเริ่มต้นการคำนวณ
        this.stopIndex = stopIndex || (candleData.length - 1); // จุดสิ้นสุดการคำนวณ
        this.martingaleLevels = [1, 2, 6, 18, 54, 162, 384, 816, 1800, 3600];
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

    // กำหนด Action ตาม EMA Trend
    determineActionByEma(candle) {
        const emaShort = candle.emaShort;
        const emaLong = candle.emaLong;

        if (emaShort > emaLong) {
            return 'CALL';
        } else if (emaShort < emaLong) {
            return 'PUT';
        } else {
            // เมื่อ emaShort = emaLong ให้ดูสีของแท่งเทียนเป็นตัวตัดสิน
            const color = candle.thisColor || this.getCandleColor(candle);
            if (color === 'Green') return 'CALL';
            if (color === 'Red') return 'PUT';
            return 'CALL'; // default เมื่อสีเป็น Equal
        }
    }

    // คำนวณจำนวนเงินที่จะเทรด
    getTradeAmount() {
        if (this.useMartingale) {
            return this.martingaleLevels[this.currentMartingaleIndex];
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
    runTrade() {
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

        // ตรวจสอบ index ที่ถูกต้อง
        const actualStopIndex = Math.min(this.stopIndex, this.candleData.length - 1);

        for (let i = this.startIndex; i < actualStopIndex; i++) {
            // ตรวจสอบว่าควรหยุดเทรดหรือไม่
            if (this.shouldStopTrading()) {
                break;
            }

            const currentCandle = this.candleData[i];
            const nextCandle = this.candleData[i + 1];

            const thisColor = currentCandle.thisColor || this.getCandleColor(currentCandle);
            const nextColor = nextCandle.thisColor || this.getCandleColor(nextCandle);
            const action = this.determineActionByEma(currentCandle);
            const moneyTrade = this.getTradeAmount();
            const winStatus = this.checkWinStatus(action, nextColor);

            // อัพเดท MaxMoneyTradeUsed
            this.maxMoneyTradeUsed = Math.max(this.maxMoneyTradeUsed, moneyTrade);

            this.updateContinueCounters(winStatus);
            const profit = this.calculateProfitAndBalance(moneyTrade, winStatus);

            // เก็บผลการเทรด
            const tradeResult = {
                time: currentCandle.time,
                thisColor: thisColor,
                MoneyTrade: moneyTrade,
                Action: action,
                nextColor: nextColor,
                WinStatus: winStatus,
                WinContinue: this.winContinue,
                LossContinue: this.lossContinue,
                Profit: profit,
                Balance: this.balance,
                emaShort: currentCandle.emaShort,
                emaLong: currentCandle.emaLong,
                emaDiff: currentCandle.emaDiff
            };

            this.tradeResults.push(tradeResult);
            this.tradesCompleted++;

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
            this.stopReason = `All candle data processed (${this.startIndex} to ${actualStopIndex})`;
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

        let maxMoneyTrade = 0;
        let maxWinContinue = 0;
        let maxLossContinue = 0;

        if (this.tradeResults.length > 0) {
            maxMoneyTrade = Math.max(...this.tradeResults.map(trade => trade.MoneyTrade));
            maxWinContinue = Math.max(...this.tradeResults.map(trade => trade.WinContinue));
            maxLossContinue = Math.max(...this.tradeResults.map(trade => trade.LossContinue));
        }

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
            targetBalance: this.targetBalance,
            startIndex: this.startIndex,
            stopIndex: this.stopIndex
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
            targetBalance: this.targetBalance,
            startIndex: this.startIndex,
            stopIndex: this.stopIndex
        };
    }

    // เปลี่ยน Index ช่วงการคำนวณ
    setTradeRange(startIndex, stopIndex) {
        this.startIndex = startIndex;
        this.stopIndex = stopIndex || this.candleData.length - 1;
        return this;
    }
}

// ตัวอย่างข้อมูล
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
    },
    {
        "time": 1755687480,
        "open": 122.2241,
        "high": 122.2500,
        "low": 122.2100,
        "close": 122.2150,
        "thisColor": "Red",
        "emaShort": 122.19500000000000,
        "emaLong": 122.19000000000000,
        "emaDiff": 0.005000000000000000,
        "conflictType": "n"
    },
    {
        "time": 1755687540,
        "open": 122.2150,
        "high": 122.2400,
        "low": 122.2000,
        "close": 122.2300,
        "thisColor": "Green",
        "emaShort": 122.21000000000000,
        "emaLong": 122.20500000000000,
        "emaDiff": 0.005000000000000000,
        "conflictType": "n"
    }
];

/*
// การใช้งาน
console.log("=== EMA Trend Trading (NoMartingale, Index 0-2) ===");
const emaTrader1 = new TradeByEmaTrend(sampleData, false, 0.94, null, 0, 2);
const results1 = emaTrader1.runTrade();
console.log("Trade Results:", results1);
console.log("Summary Stats:", emaTrader1.getSummaryStats());

console.log("\n=== EMA Trend Trading (Martingale, หยุดเมื่อ Balance = 10) ===");
const emaTrader2 = new TradeByEmaTrend(sampleData, true, 0.94, 10);
const results2 = emaTrader2.runTrade();
console.log("Summary Stats:", emaTrader2.getSummaryStats());
console.log("Stop Status:", emaTrader2.getStopStatus());

console.log("\n=== EMA Trend Trading (เทรดเฉพาะ Index 1-3) ===");
const emaTrader3 = new TradeByEmaTrend(sampleData, false, 0.94, null, 1, 3);
const results3 = emaTrader3.runTrade();
console.log("Summary Stats:", emaTrader3.getSummaryStats());

console.log("\n=== เปลี่ยน Index ระหว่างการใช้งาน ===");
const emaTrader4 = new TradeByEmaTrend(sampleData, false);
emaTrader4.setTradeRange(0, 2); // เปลี่ยนช่วงเป็น 0-2
const results4 = emaTrader4.runTrade();
console.log("Summary Stats:", emaTrader4.getSummaryStats());
*/