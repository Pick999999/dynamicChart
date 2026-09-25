// Trading Signal Analyzer - Converted from IQOption Lua Script

/**
 * Simple Moving Average calculation
 * @param {number[]} data - Array of price values
 * @param {number} period - Period for moving average
 * @returns {number[]} - Array of SMA values
 */
function sma(data, period) {
    const result = [];

    for (let i = 0; i < data.length; i++) {
        if (i < period - 1) {
            result.push(null); // Not enough data points
        } else {
            let sum = 0;
            for (let j = i - period + 1; j <= i; j++) {
                sum += data[j];
            }
            result.push(sum / period);
        }
    }

    return result;
}

/**
 * Weighted Moving Average calculation
 * @param {number[]} data - Array of values
 * @param {number} period - Period for weighted moving average
 * @returns {number[]} - Array of WMA values
 */
function wma(data, period) {
    const result = [];

    for (let i = 0; i < data.length; i++) {
        if (i < period - 1) {
            result.push(null); // Not enough data points
        } else {
            let weightedSum = 0;
            let weightSum = 0;

            for (let j = 0; j < period; j++) {
                const weight = period - j;
                weightedSum += data[i - j] * weight;
                weightSum += weight;
            }

            result.push(weightedSum / weightSum);
        }
    }

    return result;
}

/**
 * Main trading signal analyzer function
 * @param {Object} priceData - Object containing OHLC data
 * @param {number[]} priceData.open - Array of open prices
 * @param {number[]} priceData.high - Array of high prices
 * @param {number[]} priceData.low - Array of low prices
 * @param {number[]} priceData.close - Array of close prices
 * @param {Object} config - Configuration object
 * @param {number} config.maFastPeriod - Fast MA period (default: 1)
 * @param {string} config.maValue - Which price to use ('close', 'open', 'high', 'low') (default: 'close')
 * @param {number} config.maSlowPeriod - Slow MA period (default: 34)
 * @param {number} config.signalPeriod - Signal period (default: 5)
 * @returns {Object} - Analysis results with signals and indicators
 */
function analyzeTrading(priceData, config = {}) {
    // Set default parameters
    const {
        maFastPeriod = 1,
        maValue = 'close',
        maSlowPeriod = 34,
        signalPeriod = 5
    } = config;

    // Get price data arrays
    const { open, high, low, close } = priceData;
    const dataLength = close.length;

    // Validate input data
    if (!open || !high || !low || !close ||
        open.length !== dataLength ||
        high.length !== dataLength ||
        low.length !== dataLength) {
        throw new Error('Invalid or inconsistent price data provided');
    }

    // Select which price value to use for MA calculation
    let titleValue;
    switch (maValue.toLowerCase()) {
        case 'open': titleValue = open; break;
        case 'high': titleValue = high; break;
        case 'low': titleValue = low; break;
        case 'close':
        default: titleValue = close; break;
    }

    // Calculate moving averages
    const smaFast = sma(titleValue, maFastPeriod);
    const smaSlow = sma(titleValue, maSlowPeriod);

    // Calculate buffers
    const buffer1 = [];
    const buffer2 = [];

    // Calculate buffer1 (difference between fast and slow MA)
    for (let i = 0; i < dataLength; i++) {
        if (smaFast[i] !== null && smaSlow[i] !== null) {
            buffer1.push(smaFast[i] - smaSlow[i]);
        } else {
            buffer1.push(null);
        }
    }

    // Calculate buffer2 (WMA of buffer1)
    const validBuffer1 = buffer1.map(val => val !== null ? val : 0);
    const wmaBuffer = wma(validBuffer1, signalPeriod);

    for (let i = 0; i < dataLength; i++) {
        buffer2.push(buffer1[i] !== null ? wmaBuffer[i] : null);
    }

    // Calculate buy and sell conditions
    const buySignals = [];
    const sellSignals = [];

    for (let i = 1; i < dataLength; i++) {
        const currentBuffer1 = buffer1[i];
        const currentBuffer2 = buffer2[i];
        const prevBuffer1 = buffer1[i - 1];
        const prevBuffer2 = buffer2[i - 1];

        // Buy condition: buffer1 > buffer2 and previous buffer1 < previous buffer2
        const buyCondition = currentBuffer1 !== null && currentBuffer2 !== null &&
                             prevBuffer1 !== null && prevBuffer2 !== null &&
                             currentBuffer1 > currentBuffer2 && prevBuffer1 < prevBuffer2;

        // Sell condition: buffer1 < buffer2 and previous buffer1 > previous buffer2
        const sellCondition = currentBuffer1 !== null && currentBuffer2 !== null &&
                              prevBuffer1 !== null && prevBuffer2 !== null &&
                              currentBuffer1 < currentBuffer2 && prevBuffer1 > prevBuffer2;

        buySignals.push(buyCondition);
        sellSignals.push(sellCondition);
    }

    // Add first element (no signal possible)
    buySignals.unshift(false);
    sellSignals.unshift(false);

    // Calculate Engulfing patterns
    const bearishEngulfing = [];
    const bullishEngulfing = [];

    for (let i = 1; i < dataLength; i++) {
        const prevOpen = open[i - 1];
        const prevClose = close[i - 1];
        const currOpen = open[i];
        const currClose = close[i];

        // Bearish Engulfing: prev candle bullish, current bearish, current engulfs previous
        const bearish = (prevClose > prevOpen) && // Previous candle was bullish
                       (currOpen > currClose) && // Current candle is bearish
                       (currOpen >= prevClose) && // Current open >= previous close
                       (prevOpen >= currClose) && // Previous open >= current close
                       (currOpen - currClose > prevClose - prevOpen); // Current body > previous body

        // Bullish Engulfing: prev candle bearish, current bullish, current engulfs previous
        const bullish = (prevOpen > prevClose) && // Previous candle was bearish
                       (currClose > currOpen) && // Current candle is bullish
                       (currClose >= prevOpen) && // Current close >= previous open
                       (prevClose >= currOpen) && // Previous close >= current open
                       (currClose - currOpen > prevOpen - prevClose); // Current body > previous body

        bearishEngulfing.push(bearish);
        bullishEngulfing.push(bullish);
    }

    // Add first element (no pattern possible)
    bearishEngulfing.unshift(false);
    bullishEngulfing.unshift(false);

    // Return comprehensive analysis results
    return {
        // Calculated indicators
        indicators: {
            smaFast: smaFast,
            smaSlow: smaSlow,
            buffer1: buffer1,
            buffer2: buffer2
        },

        // Trading signals
        signals: {
            buy: buySignals,
            sell: sellSignals,
            bearishEngulfing: bearishEngulfing,
            bullishEngulfing: bullishEngulfing
        },

        // Latest signals (most recent values)
        latest: {
            buySignal: buySignals[buySignals.length - 1],
            sellSignal: sellSignals[sellSignals.length - 1],
            bearishEngulfing: bearishEngulfing[bearishEngulfing.length - 1],
            bullishEngulfing: bullishEngulfing[bullishEngulfing.length - 1],
            buffer1: buffer1[buffer1.length - 1],
            buffer2: buffer2[buffer2.length - 1]
        },

        // Configuration used
        config: {
            maFastPeriod,
            maValue,
            maSlowPeriod,
            signalPeriod
        }
    };
}

/**
 * Helper function to get signal summary for latest candle
 * @param {Object} analysisResult - Result from analyzeTrading function
 * @returns {Object} - Summary of current signals
 */
function getCurrentSignals(analysisResult) {
    const { latest } = analysisResult;

    return {
        recommendation: latest.buySignal ? 'BUY' :
                       latest.sellSignal ? 'SELL' : 'HOLD',
        maSignal: latest.buySignal || latest.sellSignal,
        engulfingPattern: latest.bullishEngulfing ? 'BULLISH_ENGULFING' :
                         latest.bearishEngulfing ? 'BEARISH_ENGULFING' : null,
        buffer1Value: latest.buffer1,
        buffer2Value: latest.buffer2,
        trend: latest.buffer1 > latest.buffer2 ? 'BULLISH' : 'BEARISH'
    };
}

/**
 * Helper function to get signal summary for specific index
 * @param {Object} analysisResult - Result from analyzeTrading function
 * @param {number} index - Index position (0-based)
 * @returns {Object} - Summary of signals at specific index
 */
function getSignalAtIndex(analysisResult, index) {
    const { signals, indicators } = analysisResult;

    // Validate index
    if (index < 0 || index >= signals.buy.length) {
        throw new Error(`Index ${index} is out of range. Valid range: 0-${signals.buy.length - 1}`);
    }

    return {
        index: index,
        recommendation: signals.buy[index] ? 'BUY' :
                       signals.sell[index] ? 'SELL' : 'HOLD',
        maSignal: signals.buy[index] || signals.sell[index],
        engulfingPattern: signals.bullishEngulfing[index] ? 'BULLISH_ENGULFING' :
                         signals.bearishEngulfing[index] ? 'BEARISH_ENGULFING' : null,
        buffer1Value: indicators.buffer1[index],
        buffer2Value: indicators.buffer2[index],
        trend: indicators.buffer1[index] > indicators.buffer2[index] ? 'BULLISH' : 'BEARISH',
        smaFast: indicators.smaFast[index],
        smaSlow: indicators.smaSlow[index]
    };
}

// Example usage:
/*
// Sample price data (OHLC format)
const sampleData = {
    open: [100, 102, 101, 103, 105, 104, 106, 108],
    high: [102, 104, 103, 105, 107, 106, 108, 110],
    low: [99, 101, 100, 102, 104, 103, 105, 107],
    close: [101, 103, 102, 104, 106, 105, 107, 109]
};

// Configuration (optional, uses defaults if not provided)
const config = {
    maFastPeriod: 1,
    maValue: 'close',
    maSlowPeriod: 34,
    signalPeriod: 5
};

// Run analysis
const result = analyzeTrading(sampleData, config);

// Get current signals (latest candle)
const currentSignals = getCurrentSignals(result);

// Get signal at specific index (e.g., index 50 for 100 records)
const signalAt50 = getSignalAtIndex(result, 50);

// Get multiple signals at once
const signalAt49 = getSignalAtIndex(result, 49);
const signalAt51 = getSignalAtIndex(result, 51);

console.log('Latest Signals:', currentSignals);
console.log('Signal at index 50:', signalAt50);
console.log('Signal at index 49:', signalAt49);
console.log('Signal at index 51:', signalAt51);

// Check if there are any BUY signals in the entire dataset
const hasBuySignals = result.signals.buy.some(signal => signal === true);
console.log('Has any BUY signals:', hasBuySignals);

// Find all indices where BUY signals occurred
const buySignalIndices = result.signals.buy
    .map((signal, index) => signal ? index : null)
    .filter(index => index !== null);
console.log('BUY signal indices:', buySignalIndices);
*/