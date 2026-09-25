// Sample data/*
/*
const candleData = [
    {"time":1754976120,"open":93315.2362,"high":93345.9311,"low":93265.4869,"close":93330.3913,"thisColor":"Green","emaShort":93341.62821666666,"emaLong":93352.86513333333,"emaDiff":-11.236916666661273,"conflictType":"Bullish Conflict"},
    {"time":1754976180,"open":93330.8189,"high":93330.8189,"low":93270.4137,"close":93287.0609,"thisColor":"Red","emaShort":93305.25000555556,"emaLong":93319.96301666666,"emaDiff":-14.713011111103697,"conflictType":"n"},
    {"time":1754976240,"open":93305.6591,"high":93362.3992,"low":93226.6996,"close":93362.3992,"thisColor":"Green","emaShort":93343.34946851851,"emaLong":93341.18110833333,"emaDiff":2.168360185183701,"conflictType":"n"},
    {"time":1754976300,"open":93340.9665,"high":93350.0391,"low":93281.0234,"close":93312.1972,"thisColor":"Red","emaShort":93322.58128950617,"emaLong":93326.68915416667,"emaDiff":-4.107864660501946,"conflictType":"n"}
];
*/

// Trading system class
class EMATradingSystem {
    constructor() {
        this.balance = 10000; // Starting balance
        this.winContinue = 0;
        this.lossContinue = 0;
        this.martingaleSequence = [1, 3, 6, 18, 54, 162, 384, 820, 1500, 3400];
        this.winRate = 0.95; // 95% payout
        this.tradeResults = [];

        // Tracking maximums
        this.maxWinContinue = 0;
        this.maxLossContinue = 0;
        this.maxMartingaleAmount = 0;
        this.maxMartingaleTrades = []; // Store trades with max martingale
    }

    // Determine trade action based on EMA
    getTradeAction(emaShort, emaLong) {
        if (emaShort > emaLong) {
            return {
                action: "CALL",
                suggestColor: "Green"
            };
        } else {
            return {
                action: "PUT",
                suggestColor: "Red"
            };
        }
    }

    // Calculate trade amount using Martingale
    getTradeAmount() {
        if (this.lossContinue >= this.martingaleSequence.length) {
            return this.martingaleSequence[this.martingaleSequence.length - 1];
        }
        return this.martingaleSequence[this.lossContinue];
    }

    // Format timestamp to readable date
    formatTime(timestamp) {
        return new Date(timestamp * 1000).toLocaleString();
    }

    // Process single trade
    processTrade(currentCandle, nextCandle) {
        const tradeInfo = this.getTradeAction(currentCandle.emaShort, currentCandle.emaLong);
        const tradeAmount = this.getTradeAmount();

        let winStatus = false;
        let profit = 0;

        if (nextCandle) {
            // Check if prediction matches next candle color
            winStatus = tradeInfo.suggestColor === nextCandle.thisColor;

            if (winStatus) {
                profit = tradeAmount * this.winRate; // 95% profit
                this.balance += profit;
                this.winContinue++;
                this.lossContinue = 0;
            } else {
                profit = -tradeAmount; // Loss
                this.balance += profit;
                this.lossContinue++;
                this.winContinue = 0;
            }
        }

        // Track maximums
        if (this.winContinue > this.maxWinContinue) {
            this.maxWinContinue = this.winContinue;
        }

        if (this.lossContinue > this.maxLossContinue) {
            this.maxLossContinue = this.lossContinue;
        }

        if (tradeAmount > this.maxMartingaleAmount) {
            this.maxMartingaleAmount = tradeAmount;
            this.maxMartingaleTrades = []; // Reset array for new max
        }

        // Store trades that used the maximum martingale amount
        if (tradeAmount === this.maxMartingaleAmount) {
            this.maxMartingaleTrades.push({
                time: currentCandle.time,
                timeDisplay: this.formatTime(currentCandle.time),
                amount: tradeAmount,
                lossContinue: this.lossContinue
            });
        }

        // Record trade result
        const tradeResult = {
            time: currentCandle.time,
            timeDisplay: this.formatTime(currentCandle.time),
            action: tradeInfo.action,
            suggestColor: tradeInfo.suggestColor,
            resultColor: nextCandle ? nextCandle.thisColor : "N/A",
            moneyTrade: tradeAmount,
            winStatus: winStatus,
            profit: profit,
            winContinue: this.winContinue,
            lossContinue: this.lossContinue,
            balance: this.balance
        };

        this.tradeResults.push(tradeResult);
        return tradeResult;
    }

    // Process all trades
    processAllTrades(data) {
        console.log("=== EMA Trading System Started ===");
        console.log(`Initial Balance: $${this.balance}`);
        console.log("\n");

        for (let i = 0; i < data.length - 1; i++) {
            const currentCandle = data[i];
            const nextCandle = data[i + 1];

            const result = this.processTrade(currentCandle, nextCandle);

            console.log(`Trade ${i + 1}:`);
            console.log(`Time: ${result.timeDisplay}`);
            console.log(`Action: ${result.action}`);
            console.log(`Predicted: ${result.suggestColor}, Actual: ${result.resultColor}`);
            console.log(`Trade Amount: $${result.moneyTrade}`);
            console.log(`Result: ${result.winStatus ? 'WIN' : 'LOSS'}`);
            console.log(`Profit/Loss: $${result.profit}`);
            console.log(`Balance: $${result.balance}`);
            console.log(`Win Streak: ${result.winContinue}, Loss Streak: ${result.lossContinue}`);
            console.log("---");
        }

        return this.getTradesSummary();
    }

    // Get trading summary
    getTradesSummary() {
        const totalTrades = this.tradeResults.length;
        const wins = this.tradeResults.filter(trade => trade.winStatus).length;
        const losses = totalTrades - wins;
        const winRate = totalTrades > 0 ? (wins / totalTrades * 100).toFixed(2) : 0;
        const totalProfit = this.balance - 10000;

        const summary = {
            totalTrades: totalTrades,
            wins: wins,
            losses: losses,
            winRate: winRate + '%',
            startingBalance: 10000,
            finalBalance: this.balance,
            totalProfit: totalProfit,
            profitPercentage: ((totalProfit / 10000) * 100).toFixed(2) + '%',

            // New maximum tracking
            maxWinContinue: this.maxWinContinue,
            maxLossContinue: this.maxLossContinue,
            maxMartingaleAmount: this.maxMartingaleAmount,
            maxMartingaleTrades: this.maxMartingaleTrades
        };

        console.log("=== Trading Summary ===");
        console.log(`Total Trades: ${summary.totalTrades}`);
        console.log(`Wins: ${summary.wins}`);
        console.log(`Losses: ${summary.losses}`);
        console.log(`Win Rate: ${summary.winRate}`);
        console.log(`Starting Balance: ${summary.startingBalance}`);
        console.log(`Final Balance: ${summary.finalBalance}`);
        console.log(`Total Profit: ${summary.totalProfit}`);
        console.log(`Profit Percentage: ${summary.profitPercentage}`);

        console.log("\n=== Maximum Statistics ===");
        console.log(`Max Win Streak: ${summary.maxWinContinue}`);
        console.log(`Max Loss Streak: ${summary.maxLossContinue}`);
        console.log(`Max Martingale Amount: ${summary.maxMartingaleAmount}`);

        if (summary.maxMartingaleTrades.length > 0) {
            console.log(`\nTimes when Max Martingale (${summary.maxMartingaleAmount}) was used:`);
            summary.maxMartingaleTrades.forEach((trade, index) => {
                console.log(`${index + 1}. ${trade.timeDisplay} - Loss Streak: ${trade.lossContinue}`);
            });
        }

        return {
            summary: summary,
            detailedResults: this.tradeResults
        };
    }

    // Get advanced statistics
    getAdvancedStats() {
        const stats = {
            maxWinContinue: this.maxWinContinue,
            maxLossContinue: this.maxLossContinue,
            maxMartingaleAmount: this.maxMartingaleAmount,
            maxMartingaleTrades: this.maxMartingaleTrades,

            // Additional useful stats
            totalWinStreaks: this.calculateWinStreaks(),
            totalLossStreaks: this.calculateLossStreaks(),
            martingaleUsageStats: this.getMartingaleUsageStats()
        };

        return stats;
    }

    // Calculate all win streaks
    calculateWinStreaks() {
        const streaks = [];
        let currentStreak = 0;

        this.tradeResults.forEach(trade => {
            if (trade.winStatus) {
                currentStreak++;
            } else {
                if (currentStreak > 0) {
                    streaks.push(currentStreak);
                    currentStreak = 0;
                }
            }
        });

        if (currentStreak > 0) {
            streaks.push(currentStreak);
        }

        return streaks;
    }

    // Calculate all loss streaks
    calculateLossStreaks() {
        const streaks = [];
        let currentStreak = 0;

        this.tradeResults.forEach(trade => {
            if (!trade.winStatus) {
                currentStreak++;
            } else {
                if (currentStreak > 0) {
                    streaks.push(currentStreak);
                    currentStreak = 0;
                }
            }
        });

        if (currentStreak > 0) {
            streaks.push(currentStreak);
        }

        return streaks;
    }

    // Get martingale usage statistics
    getMartingaleUsageStats() {
        const usageCount = {};

        this.tradeResults.forEach(trade => {
            const amount = trade.moneyTrade;
            usageCount[amount] = (usageCount[amount] || 0) + 1;
        });

        return usageCount;
    }

    // Get results as JSON
    getResultsJSON() {
        return JSON.stringify(this.tradeResults, null, 2);
    }

    // Reset system
    reset() {
        this.balance = 10000;
        this.winContinue = 0;
        this.lossContinue = 0;
        this.tradeResults = [];
        this.maxWinContinue = 0;
        this.maxLossContinue = 0;
        this.maxMartingaleAmount = 0;
        this.maxMartingaleTrades = [];
    }
}

/*
// Initialize and run trading system
const tradingSystem = new EMATradingSystem();

// Process the sample data
const results = tradingSystem.processAllTrades(candleData);

// Display results in JSON format
console.log("\n=== Detailed Results JSON ===");
console.log(tradingSystem.getResultsJSON());

// Display advanced statistics
console.log("\n=== Advanced Statistics ===");
const advancedStats = tradingSystem.getAdvancedStats();
console.log(`All Win Streaks: [${advancedStats.totalWinStreaks.join(', ')}]`);
console.log(`All Loss Streaks: [${advancedStats.totalLossStreaks.join(', ')}]`);
console.log("\nMartingale Usage Statistics:");
Object.entries(advancedStats.martingaleUsageStats).forEach(([amount, count]) => {
    console.log(`${amount}: used ${count} times`);
});

// Example of how to use with different data
console.log("\n=== Usage Example ===");
console.log("// To use with your own data:");
console.log("// const myTradingSystem = new EMATradingSystem();");
console.log("// const results = myTradingSystem.processAllTrades(yourCandleData);");
console.log("// console.log(results.summary);");
console.log("// const advancedStats = myTradingSystem.getAdvancedStats();");
console.log("// console.log(advancedStats);");

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EMATradingSystem;
}*/