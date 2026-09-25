/**
 * TradingSignalAnalyzer Class
 * วิเคราะห์สัญญาณการเทรดตามกลยุทธ์ Multiple Timeframe Analysis
 * 
 * @author Trading Strategy Guide
 * @version 1.0.0
 */

class TradingSignalAnalyzer {
    constructor() {
        // Weight สำหรับแต่ละ Indicator (รวมต้องเท่ากับ 100)
        this.weights = {
            trend: 25,          // EMA 200, EMA 3/5 Cross
            supportResistance: 25, // Pivot, Fib, Swing
            momentum: 20,       // Bollinger, VWAP
            volume: 15,         // Volume confirmation
            priceAction: 15     // Candlestick patterns, MTF
        };

        // Threshold สำหรับสัญญาณ
        this.thresholds = {
            strongBuy: 70,      // >= 70 = Strong Buy
            buy: 55,            // 55-69 = Buy
            neutral: 45,        // 45-54 = Neutral
            sell: 30,           // 31-44 = Sell
            strongSell: 0       // <= 30 = Strong Sell
        };
    }

    /**
     * วิเคราะห์สัญญาณการเทรดจากข้อมูลทั้งหมด
     * @param {Object} data - ข้อมูลการวิเคราะห์
     * @returns {Object} ผลการวิเคราะห์พร้อมคำแนะนำ
     */
    analyze(data) {
        const trendScore = this.analyzeTrend(data.trend);
        const srScore = this.analyzeSupportResistance(data.supportResistance);
        const momentumScore = this.analyzeMomentum(data.momentum);
        const volumeScore = this.analyzeVolume(data.volume);
        const priceActionScore = this.analyzePriceAction(data.priceAction);

        // คำนวณคะแนนรวม (ถ่วงน้ำหนัก)
        const totalScore = 
            (trendScore * this.weights.trend / 100) +
            (srScore * this.weights.supportResistance / 100) +
            (momentumScore * this.weights.momentum / 100) +
            (volumeScore * this.weights.volume / 100) +
            (priceActionScore * this.weights.priceAction / 100);

        // กำหนดสัญญาณ
        const signal = this.determineSignal(totalScore);

        // สร้าง Trade Plan
        const tradePlan = this.generateTradePlan(signal, data);

        return {
            signal: signal.type,
            strength: signal.strength,
            score: Math.round(totalScore),
            breakdown: {
                trend: Math.round(trendScore),
                supportResistance: Math.round(srScore),
                momentum: Math.round(momentumScore),
                volume: Math.round(volumeScore),
                priceAction: Math.round(priceActionScore)
            },
            reasons: this.generateReasons(data, signal),
            tradePlan: tradePlan,
            riskLevel: this.assessRisk(data),
            confidence: this.calculateConfidence(totalScore, data)
        };
    }

    /**
     * วิเคราะห์เทรนด์
     */
    analyzeTrend(trend) {
        let score = 50; // เริ่มที่กลาง

        // EMA 200 Position (น้ำหนัก 40%)
        if (trend.priceVsEMA200 === 'above') {
            score += 20; // Bullish
        } else if (trend.priceVsEMA200 === 'below') {
            score -= 20; // Bearish
        }

        // EMA 3/5 Cross (น้ำหนัก 30%)
        if (trend.emaCross === 'golden') {
            score += 15; // Bullish crossover
        } else if (trend.emaCross === 'death') {
            score -= 15; // Bearish crossover
        }

        // EMA Slope (น้ำหนัก 20%)
        if (trend.emaSlope > 0.0005) {
            score += 10; // Strong uptrend
        } else if (trend.emaSlope < -0.0005) {
            score -= 10; // Strong downtrend
        }

        // Distance from EMA (น้ำหนัก 10%)
        const distance = Math.abs(trend.distanceFromEMA200);
        if (distance > 0.02) {
            score += (trend.priceVsEMA200 === 'above' ? 5 : -5);
        }

        return Math.max(0, Math.min(100, score));
    }

    /**
     * วิเคราะห์แนวรับ-แนวต้าน
     */
    analyzeSupportResistance(sr) {
        let score = 50;

        // Pivot Points (น้ำหนัก 30%)
        if (sr.nearPivot) {
            if (sr.pivotType === 'support') {
                score += 15;
            } else if (sr.pivotType === 'resistance') {
                score -= 15;
            }
        }

        // Fibonacci (น้ำหนัก 25%)
        if (sr.nearFibonacci) {
            const fibLevel = sr.fibonacciLevel;
            if (fibLevel >= 38.2 && fibLevel <= 61.8) {
                // ในโซนสำคัญ
                if (sr.fibonacciType === 'support') {
                    score += 12.5;
                } else {
                    score -= 12.5;
                }
            }
        }

        // Swing High/Low (น้ำหนัก 25%)
        if (sr.nearSwingPoint) {
            if (sr.swingType === 'low') {
                score += 12.5;
            } else if (sr.swingType === 'high') {
                score -= 12.5;
            }
        }

        // Confluence (น้ำหนัก 20%)
        const confluenceCount = sr.confluenceCount || 0;
        if (confluenceCount >= 3) {
            score += (sr.pivotType === 'support' ? 10 : -10);
        } else if (confluenceCount === 2) {
            score += (sr.pivotType === 'support' ? 5 : -5);
        }

        return Math.max(0, Math.min(100, score));
    }

    /**
     * วิเคราะห์ Momentum
     */
    analyzeMomentum(momentum) {
        let score = 50;

        // Bollinger Bands (น้ำหนัก 40%)
        if (momentum.bollingerPosition === 'lower') {
            score += 20; // Oversold
        } else if (momentum.bollingerPosition === 'upper') {
            score -= 20; // Overbought
        }

        // VWAP (น้ำหนัก 30%)
        if (momentum.priceVsVWAP === 'above') {
            score += 15;
        } else if (momentum.priceVsVWAP === 'below') {
            score -= 15;
        }

        // ATR (Volatility) (น้ำหนัก 30%)
        if (momentum.atrLevel === 'high') {
            // High volatility = ระวัง แต่อาจมีโอกาส
            score += (momentum.atrTrend === 'increasing' ? 0 : 5);
        } else if (momentum.atrLevel === 'low') {
            // Low volatility = Consolidation
            score += 10; // รอ Breakout
        }

        return Math.max(0, Math.min(100, score));
    }

    /**
     * วิเคราะห์ Volume
     */
    analyzeVolume(volume) {
        let score = 50;

        // Volume Level (น้ำหนัก 60%)
        if (volume.level === 'high') {
            // Volume สูง = ยืนยันการเคลื่อนไหว
            if (volume.trend === 'up') {
                score += 30; // Volume สูง + ราคาขึ้น
            } else if (volume.trend === 'down') {
                score -= 30; // Volume สูง + ราคาลง
            }
        } else if (volume.level === 'low') {
            // Volume ต่ำ = อ่อนแอ
            score += 0; // Neutral
        }

        // Volume Confirmation (น้ำหนัก 40%)
        if (volume.confirmsDirection) {
            score += 20;
        } else {
            score -= 10; // Divergence
        }

        return Math.max(0, Math.min(100, score));
    }

    /**
     * วิเคราะห์ Price Action
     */
    analyzePriceAction(priceAction) {
        let score = 50;

        // Candlestick Pattern (น้ำหนัก 50%)
        const pattern = priceAction.candlestickPattern;
        if (pattern === 'hammer' || pattern === 'bullishEngulfing' || pattern === 'morningStar') {
            score += 25; // Bullish patterns
        } else if (pattern === 'shootingStar' || pattern === 'bearishEngulfing' || pattern === 'eveningStar') {
            score -= 25; // Bearish patterns
        } else if (pattern === 'doji') {
            score += 0; // Indecision
        }

        // Multiple Timeframe Confirmation (น้ำหนัก 50%)
        if (priceAction.mtfAlignment) {
            const alignment = priceAction.mtfAlignment;
            if (alignment === 'bullish') {
                score += 25; // ทุก Timeframe bullish
            } else if (alignment === 'bearish') {
                score -= 25; // ทุก Timeframe bearish
            } else {
                score += 5; // Mixed signals
            }
        }

        return Math.max(0, Math.min(100, score));
    }

    /**
     * กำหนดสัญญาณการเทรด
     */
    determineSignal(score) {
        if (score >= this.thresholds.strongBuy) {
            return { type: 'STRONG_BUY', strength: 'Very High', color: '#00C853' };
        } else if (score >= this.thresholds.buy) {
            return { type: 'BUY', strength: 'High', color: '#4CAF50' };
        } else if (score >= this.thresholds.neutral) {
            return { type: 'NEUTRAL', strength: 'Medium', color: '#FFC107' };
        } else if (score >= this.thresholds.sell) {
            return { type: 'SELL', strength: 'High', color: '#FF5722' };
        } else {
            return { type: 'STRONG_SELL', strength: 'Very High', color: '#D32F2F' };
        }
    }

    /**
     * สร้างเหตุผลการแนะนำ
     */
    generateReasons(data, signal) {
        const reasons = [];

        // Trend
        if (data.trend.priceVsEMA200 === 'above') {
            reasons.push('✅ ราคาเหนือ EMA 200 - Uptrend');
        } else if (data.trend.priceVsEMA200 === 'below') {
            reasons.push('⚠️ ราคาใต้ EMA 200 - Downtrend');
        }

        if (data.trend.emaCross === 'golden') {
            reasons.push('✅ EMA Golden Cross - สัญญาณขาขึ้น');
        } else if (data.trend.emaCross === 'death') {
            reasons.push('⚠️ EMA Death Cross - สัญญาณขาลง');
        }

        // Support/Resistance
        if (data.supportResistance.confluenceCount >= 3) {
            reasons.push(`🎯 Confluence Zone (${data.supportResistance.confluenceCount} ระดับ) - แนวแข็งแกร่ง`);
        }

        if (data.supportResistance.nearPivot) {
            reasons.push(`📊 ใกล้ ${data.supportResistance.pivotType === 'support' ? 'แนวรับ' : 'แนวต้าน'} Pivot`);
        }

        // Momentum
        if (data.momentum.bollingerPosition === 'lower') {
            reasons.push('📉 ราคาแตะ Bollinger Lower - Oversold');
        } else if (data.momentum.bollingerPosition === 'upper') {
            reasons.push('📈 ราคาแตะ Bollinger Upper - Overbought');
        }

        // Volume
        if (data.volume.level === 'high' && data.volume.confirmsDirection) {
            reasons.push('📦 Volume สูงยืนยันทิศทาง');
        }

        // Price Action
        if (data.priceAction.candlestickPattern) {
            reasons.push(`🕯️ Pattern: ${data.priceAction.candlestickPattern}`);
        }

        if (data.priceAction.mtfAlignment === 'bullish') {
            reasons.push('🔮 Multiple Timeframe ชี้ขาขึ้น');
        } else if (data.priceAction.mtfAlignment === 'bearish') {
            reasons.push('🔮 Multiple Timeframe ชี้ขาลง');
        }

        return reasons;
    }

    /**
     * สร้าง Trade Plan
     */
    generateTradePlan(signal, data) {
        const currentPrice = data.currentPrice || 0;
        const atr = data.momentum.atrValue || 0;

        let entry, stopLoss, takeProfit1, takeProfit2, takeProfit3;

        if (signal.type === 'STRONG_BUY' || signal.type === 'BUY') {
            // Buy Setup
            entry = currentPrice;
            stopLoss = currentPrice - (atr * 1.5);
            takeProfit1 = currentPrice + (atr * 1.5);
            takeProfit2 = currentPrice + (atr * 2.5);
            takeProfit3 = currentPrice + (atr * 4);

            // ปรับตาม Support/Resistance
            if (data.supportResistance.nextResistance) {
                takeProfit1 = Math.min(takeProfit1, data.supportResistance.nextResistance);
            }
            if (data.supportResistance.nearestSupport) {
                stopLoss = Math.max(stopLoss, data.supportResistance.nearestSupport - (atr * 0.5));
            }
        } else if (signal.type === 'STRONG_SELL' || signal.type === 'SELL') {
            // Sell Setup
            entry = currentPrice;
            stopLoss = currentPrice + (atr * 1.5);
            takeProfit1 = currentPrice - (atr * 1.5);
            takeProfit2 = currentPrice - (atr * 2.5);
            takeProfit3 = currentPrice - (atr * 4);

            // ปรับตาม Support/Resistance
            if (data.supportResistance.nextSupport) {
                takeProfit1 = Math.max(takeProfit1, data.supportResistance.nextSupport);
            }
            if (data.supportResistance.nearestResistance) {
                stopLoss = Math.min(stopLoss, data.supportResistance.nearestResistance + (atr * 0.5));
            }
        } else {
            return null; // Neutral - ไม่แนะนำเทรด
        }

        const risk = Math.abs(entry - stopLoss);
        const reward1 = Math.abs(entry - takeProfit1);
        const reward2 = Math.abs(entry - takeProfit2);
        const reward3 = Math.abs(entry - takeProfit3);

        return {
            direction: signal.type.includes('BUY') ? 'BUY' : 'SELL',
            entry: this.formatPrice(entry),
            stopLoss: this.formatPrice(stopLoss),
            takeProfit: {
                tp1: this.formatPrice(takeProfit1),
                tp2: this.formatPrice(takeProfit2),
                tp3: this.formatPrice(takeProfit3)
            },
            riskReward: {
                rr1: (reward1 / risk).toFixed(2),
                rr2: (reward2 / risk).toFixed(2),
                rr3: (reward3 / risk).toFixed(2)
            },
            positionSizing: this.calculatePositionSize(data.accountSize, data.riskPercentage, risk)
        };
    }

    /**
     * ประเมินความเสี่ยง
     */
    assessRisk(data) {
        let riskScore = 0;

        // ATR สูง = เสี่ยงสูง
        if (data.momentum.atrLevel === 'high') riskScore += 2;
        else if (data.momentum.atrLevel === 'medium') riskScore += 1;

        // Volume ต่ำ = เสี่ยง
        if (data.volume.level === 'low') riskScore += 1;

        // ไม่มี Confluence = เสี่ยง
        if ((data.supportResistance.confluenceCount || 0) < 2) riskScore += 1;

        // MTF ไม่ตรงกัน = เสี่ยง
        if (data.priceAction.mtfAlignment === 'mixed') riskScore += 1;

        if (riskScore <= 1) return { level: 'LOW', color: '#4CAF50' };
        if (riskScore <= 3) return { level: 'MEDIUM', color: '#FFC107' };
        return { level: 'HIGH', color: '#F44336' };
    }

    /**
     * คำนวณความเชื่อมั่น
     */
    calculateConfidence(score, data) {
        let confidence = score;

        // ลด confidence ถ้ามี conflicting signals
        if (data.volume && !data.volume.confirmsDirection) {
            confidence -= 10;
        }

        if (data.priceAction.mtfAlignment === 'mixed') {
            confidence -= 15;
        }

        // เพิ่ม confidence ถ้ามี strong confluence
        if ((data.supportResistance.confluenceCount || 0) >= 3) {
            confidence += 10;
        }

        return Math.max(0, Math.min(100, Math.round(confidence))) + '%';
    }

    /**
     * คำนวณขนาด Position
     */
    calculatePositionSize(accountSize, riskPercentage, riskAmount) {
        if (!accountSize || !riskPercentage || !riskAmount) return null;

        const riskMoney = accountSize * (riskPercentage / 100);
        const positionSize = riskMoney / riskAmount;

        return {
            lots: positionSize.toFixed(2),
            riskMoney: riskMoney.toFixed(2),
            accountSize: accountSize.toFixed(2)
        };
    }

    /**
     * Format ราคา
     */
    formatPrice(price) {
        if (price >= 1000) {
            return price.toFixed(0);
        } else if (price >= 1) {
            return price.toFixed(4);
        } else {
            return price.toFixed(6);
        }
    }

    /**
     * สร้างรายงานแบบเต็ม
     */
    generateFullReport(data) {
        const analysis = this.analyze(data);

        return {
            timestamp: new Date().toISOString(),
            symbol: data.symbol || 'N/A',
            timeframe: data.timeframe || 'N/A',
            analysis: analysis,
            rawData: data,
            recommendation: this.generateRecommendation(analysis)
        };
    }

    /**
     * สร้างคำแนะนำ
     */
    generateRecommendation(analysis) {
        const { signal, strength, score, confidence, riskLevel, tradePlan } = analysis;

        let recommendation = '';

        if (signal === 'STRONG_BUY') {
            recommendation = `🟢 แนะนำ BUY อย่างยิ่ง! สัญญาณแข็งแกร่ง (คะแนน: ${score}/100, ความมั่นใจ: ${confidence})`;
        } else if (signal === 'BUY') {
            recommendation = `🟢 แนะนำ BUY (คะแนน: ${score}/100, ความมั่นใจ: ${confidence})`;
        } else if (signal === 'NEUTRAL') {
            recommendation = `⚪ ไม่แนะนำเทรด รอสัญญาณที่ชัดเจนกว่า (คะแนน: ${score}/100)`;
        } else if (signal === 'SELL') {
            recommendation = `🔴 แนะนำ SELL (คะแนน: ${score}/100, ความมั่นใจ: ${confidence})`;
        } else if (signal === 'STRONG_SELL') {
            recommendation = `🔴 แนะนำ SELL อย่างยิ่ง! สัญญาณแข็งแกร่ง (คะแนน: ${score}/100, ความมั่นใจ: ${confidence})`;
        }

        recommendation += `\nความเสี่ยง: ${riskLevel.level}`;

        if (tradePlan) {
            recommendation += `\n\n📋 Trade Plan:`;
            recommendation += `\nEntry: ${tradePlan.entry}`;
            recommendation += `\nStop Loss: ${tradePlan.stopLoss}`;
            recommendation += `\nTake Profit 1: ${tradePlan.takeProfit.tp1} (R:R ${tradePlan.riskReward.rr1})`;
            recommendation += `\nTake Profit 2: ${tradePlan.takeProfit.tp2} (R:R ${tradePlan.riskReward.rr2})`;
            recommendation += `\nTake Profit 3: ${tradePlan.takeProfit.tp3} (R:R ${tradePlan.riskReward.rr3})`;
        }

        return recommendation;
    }
}

// ========================
// ตัวอย่างการใช้งาน
// ========================

// สร้าง instance
const analyzer = new TradingSignalAnalyzer();

// ข้อมูลตัวอย่าง
const sampleData = {
    symbol: 'EUR/USD',
    timeframe: '15min',
    currentPrice: 1.0850,
    accountSize: 10000,
    riskPercentage: 1,

    trend: {
        priceVsEMA200: 'above',      // 'above', 'below', 'near'
        emaCross: 'golden',           // 'golden', 'death', 'none'
        emaSlope: 0.0008,            // Positive = uptrend
        distanceFromEMA200: 0.0025   // 0.25%
    },

    supportResistance: {
        nearPivot: true,
        pivotType: 'support',         // 'support', 'resistance'
        nearFibonacci: true,
        fibonacciLevel: 61.8,
        fibonacciType: 'support',
        nearSwingPoint: true,
        swingType: 'low',             // 'high', 'low'
        confluenceCount: 3,           // จำนวนแนวที่บรรจบ
        nearestSupport: 1.0840,
        nearestResistance: 1.0870,
        nextSupport: 1.0820,
        nextResistance: 1.0890
    },

    momentum: {
        bollingerPosition: 'lower',   // 'upper', 'middle', 'lower'
        priceVsVWAP: 'above',         // 'above', 'below'
        atrValue: 0.0015,
        atrLevel: 'medium',           // 'low', 'medium', 'high'
        atrTrend: 'stable'            // 'increasing', 'decreasing', 'stable'
    },

    volume: {
        level: 'high',                // 'low', 'medium', 'high'
        trend: 'up',                  // 'up', 'down', 'stable'
        confirmsDirection: true       // Volume ยืนยันทิศทาง?
    },

    priceAction: {
        candlestickPattern: 'hammer', // 'hammer', 'shootingStar', 'doji', etc.
        mtfAlignment: 'bullish'       // 'bullish', 'bearish', 'mixed'
    }
};

// วิเคราะห์
const result = analyzer.analyze(sampleData);

// แสดงผล
console.log('=== Trading Signal Analysis ===');
console.log('Signal:', result.signal);
console.log('Strength:', result.strength);
console.log('Score:', result.score, '/100');
console.log('Confidence:', result.confidence);
console.log('\nBreakdown:');
console.log('- Trend:', result.breakdown.trend);
console.log('- Support/Resistance:', result.breakdown.supportResistance);
console.log('- Momentum:', result.breakdown.momentum);
console.log('- Volume:', result.breakdown.volume);
console.log('- Price Action:', result.breakdown.priceAction);
console.log('\nReasons:');
result.reasons.forEach(reason => console.log(reason));
console.log('\nRisk Level:', result.riskLevel.level);

if (result.tradePlan) {
    console.log('\n=== Trade Plan ===');
    console.log('Direction:', result.tradePlan.direction);
    console.log('Entry:', result.tradePlan.entry);
    console.log('Stop Loss:', result.tradePlan.stopLoss);
    console.log('Take Profit 1:', result.tradePlan.takeProfit.tp1, '(R:R', result.tradePlan.riskReward.rr1 + ')');
    console.log('Take Profit 2:', result.tradePlan.takeProfit.tp2, '(R:R', result.tradePlan.riskReward.rr2 + ')');
    console.log('Take Profit 3:', result.tradePlan.takeProfit.tp3, '(R:R', result.tradePlan.riskReward.rr3 + ')');
}

// สร้างรายงานแบบเต็ม
const fullReport = analyzer.generateFullReport(sampleData);
console.log('\n=== Recommendation ===');
console.log(fullReport.recommendation);