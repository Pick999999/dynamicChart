// Big Snapper Alerts R3.0 - Pure JavaScript Implementation
// Original: JustUncleL (Pine Script)
// Converted to JavaScript

class BigSnapperAlerts {
  constructor(config = {}) {
    // Default configuration
    this.config = {
      // Coloured MA settings
      typeColoured: config.typeColoured || 'HullMA',
      lenColoured: config.lenColoured || 18,
      
      // Fast/Medium/Slow MA settings
      typeFast: config.typeFast || 'EMA',
      lenFast: config.lenFast || 21,
      typeMedium: config.typeMedium || 'EMA',
      lenMedium: config.lenMedium || 55,
      typeSlow: config.typeSlow || 'EMA',
      lenSlow: config.lenSlow || 89,
      
      // Filter options
      filterOption: config.filterOption || 'SuperTrend',
      
      // Display options
      hideMALines: config.hideMALines || false,
      hideSuperTrend: config.hideSuperTrend || true,
      hideBollingerBands: config.hideBollingerBands || true,
      hideTrendDirection: config.hideTrendDirection || true,
      
      // MA Filter toggles
      disableFastMAFilter: config.disableFastMAFilter || false,
      disableMediumMAFilter: config.disableMediumMAFilter || false,
      disableSlowMAFilter: config.disableSlowMAFilter || false,
      
      // Bollinger Bands settings
      bbLength: config.bbLength || 20,
      bbStddev: config.bbStddev || 2.0,
      oiLength: config.oiLength || 8,
      
      // SuperTrend settings
      SFactor: config.SFactor || 3.618,
      SPd: config.SPd || 5,
      
      // Colors
      buyColour: config.buyColour || 'Green',
      sellColour: config.sellColour || 'Maroon'
    };
    
    this.prevValues = {};
  }
  
  // === MOVING AVERAGE FUNCTIONS ===
  
  sma(data, period) {
    if (data.length < period) return null;
    const sum = data.slice(-period).reduce((a, b) => a + b, 0);
    return sum / period;
  }
  
  ema(data, period) {
    if (data.length === 0) return null;
    const k = 2 / (period + 1);
    let ema = data[0];
    
    for (let i = 1; i < data.length; i++) {
      ema = data[i] * k + ema * (1 - k);
    }
    return ema;
  }
  
  wma(data, period) {
    if (data.length < period) return null;
    const weights = [];
    let sum = 0;
    let weightSum = 0;
    
    for (let i = 0; i < period; i++) {
      weights.push(i + 1);
      weightSum += i + 1;
    }
    
    const slice = data.slice(-period);
    for (let i = 0; i < period; i++) {
      sum += slice[i] * weights[i];
    }
    
    return sum / weightSum;
  }
  
  vwma(prices, volumes, period) {
    if (prices.length < period) return null;
    let sumPV = 0;
    let sumV = 0;
    
    for (let i = prices.length - period; i < prices.length; i++) {
      sumPV += prices[i] * volumes[i];
      sumV += volumes[i];
    }
    
    return sumV !== 0 ? sumPV / sumV : null;
  }
  
  smma(data, period, prevSmma = null) {
    if (data.length < period) return null;
    
    if (prevSmma === null) {
      return this.sma(data, period);
    }
    
    const current = data[data.length - 1];
    return (prevSmma * (period - 1) + current) / period;
  }
  
  dema(data, period) {
    const ema1 = this.ema(data, period);
    if (ema1 === null) return null;
    
    const emaData = [];
    let tempEma = data[0];
    const k = 2 / (period + 1);
    
    for (let i = 0; i < data.length; i++) {
      tempEma = data[i] * k + tempEma * (1 - k);
      emaData.push(tempEma);
    }
    
    const ema2 = this.ema(emaData, period);
    return 2 * ema1 - ema2;
  }
  
  tema(data, period) {
    const ema1 = this.ema(data, period);
    if (ema1 === null) return null;
    
    const emaData1 = [];
    let tempEma = data[0];
    const k = 2 / (period + 1);
    
    for (let i = 0; i < data.length; i++) {
      tempEma = data[i] * k + tempEma * (1 - k);
      emaData1.push(tempEma);
    }
    
    const ema2 = this.ema(emaData1, period);
    
    const emaData2 = [];
    tempEma = emaData1[0];
    
    for (let i = 0; i < emaData1.length; i++) {
      tempEma = emaData1[i] * k + tempEma * (1 - k);
      emaData2.push(tempEma);
    }
    
    const ema3 = this.ema(emaData2, period);
    return 3 * (ema1 - ema2) + ema3;
  }
  
  hullMA(data, period) {
    const halfPeriod = Math.floor(period / 2);
    const sqrtPeriod = Math.round(Math.sqrt(period));
    
    const wma1 = this.wma(data, halfPeriod);
    const wma2 = this.wma(data, period);
    
    if (wma1 === null || wma2 === null) return null;
    
    const rawHull = 2 * wma1 - wma2;
    
    // Create array for WMA calculation
    const hullData = data.map((_, i) => {
      const w1 = this.wma(data.slice(0, i + 1), halfPeriod);
      const w2 = this.wma(data.slice(0, i + 1), period);
      return w1 !== null && w2 !== null ? 2 * w1 - w2 : null;
    }).filter(v => v !== null);
    
    return this.wma(hullData, sqrtPeriod);
  }
  
  ssma(data, period) {
    // SuperSmoother filter by John F. Ehlers
    const a1 = Math.exp(-1.414 * Math.PI / period);
    const b1 = 2 * a1 * Math.cos(1.414 * Math.PI / period);
    const c2 = b1;
    const c3 = -a1 * a1;
    const c1 = 1 - c2 - c3;
    
    const result = [];
    for (let i = 0; i < data.length; i++) {
      if (i === 0) {
        result.push(data[i]);
      } else if (i === 1) {
        result.push(c1 * (data[i] + data[i - 1]) / 2 + c2 * result[i - 1]);
      } else {
        result.push(c1 * (data[i] + data[i - 1]) / 2 + c2 * result[i - 1] + c3 * result[i - 2]);
      }
    }
    
    return result[result.length - 1];
  }
  
  zema(data, period) {
    const sma = this.sma(data, period);
    const ema = this.ema(data, period);
    if (sma === null || ema === null) return null;
    return sma + (sma - ema);
  }
  
  tma(data, period) {
    // Triangular Moving Average
    const sma1 = this.sma(data, period);
    if (sma1 === null) return null;
    
    const smaData = [];
    for (let i = period - 1; i < data.length; i++) {
      smaData.push(this.sma(data.slice(0, i + 1), period));
    }
    
    return this.sma(smaData.filter(v => v !== null), period);
  }
  
  variant(type, data, period, volumes = null) {
    switch (type) {
      case 'SMA': return this.sma(data, period);
      case 'EMA': return this.ema(data, period);
      case 'WMA': return this.wma(data, period);
      case 'VWMA': return volumes ? this.vwma(data, volumes, period) : this.sma(data, period);
      case 'SMMA': return this.smma(data, period, this.prevValues.smma);
      case 'DEMA': return this.dema(data, period);
      case 'TEMA': return this.tema(data, period);
      case 'HullMA': return this.hullMA(data, period);
      case 'SSMA': return this.ssma(data, period);
      case 'ZEMA': return this.zema(data, period);
      case 'TMA': return this.tma(data, period);
      default: return this.sma(data, period);
    }
  }
  
  // === HELPER FUNCTIONS ===
  
  atr(highs, lows, closes, period) {
    if (highs.length < period + 1) return null;
    
    const trueRanges = [];
    for (let i = 1; i < highs.length; i++) {
      const tr = Math.max(
        highs[i] - lows[i],
        Math.abs(highs[i] - closes[i - 1]),
        Math.abs(lows[i] - closes[i - 1])
      );
      trueRanges.push(tr);
    }
    
    return this.sma(trueRanges, period);
  }
  
  stdev(data, period) {
    if (data.length < period) return null;
    const mean = this.sma(data, period);
    const slice = data.slice(-period);
    const squaredDiffs = slice.map(v => Math.pow(v - mean, 2));
    const variance = squaredDiffs.reduce((a, b) => a + b, 0) / period;
    return Math.sqrt(variance);
  }
  
  highest(data, period) {
    if (data.length < period) return null;
    return Math.max(...data.slice(-period));
  }
  
  lowest(data, period) {
    if (data.length < period) return null;
    return Math.min(...data.slice(-period));
  }
  
  highestBars(data, period) {
    if (data.length < period) return 0;
    const slice = data.slice(-period);
    const maxVal = Math.max(...slice);
    return slice.indexOf(maxVal) - period + 1;
  }
  
  lowestBars(data, period) {
    if (data.length < period) return 0;
    const slice = data.slice(-period);
    const minVal = Math.min(...slice);
    return slice.indexOf(minVal) - period + 1;
  }
  
  rising(values, period) {
    if (values.length < period) return false;
    for (let i = values.length - period; i < values.length - 1; i++) {
      if (values[i + 1] <= values[i]) return false;
    }
    return true;
  }
  
  falling(values, period) {
    if (values.length < period) return false;
    for (let i = values.length - period; i < values.length - 1; i++) {
      if (values[i + 1] >= values[i]) return false;
    }
    return true;
  }
  
  crossover(series1, series2) {
    if (series1.length < 2 || series2.length < 2) return false;
    const curr1 = series1[series1.length - 1];
    const prev1 = series1[series1.length - 2];
    const curr2 = series2[series2.length - 1];
    const prev2 = series2[series2.length - 2];
    return prev1 <= prev2 && curr1 > curr2;
  }
  
  crossunder(series1, series2) {
    if (series1.length < 2 || series2.length < 2) return false;
    const curr1 = series1[series1.length - 1];
    const prev1 = series1[series1.length - 2];
    const curr2 = series2[series2.length - 1];
    const prev2 = series2[series2.length - 2];
    return prev1 >= prev2 && curr1 < curr2;
  }
  
  // === MAIN CALCULATION ===
  
  calculate(ohlcvData) {
    const { open, high, low, close, volume } = ohlcvData;
    
    if (!close || close.length < Math.max(this.config.lenSlow, this.config.bbLength)) {
      return null;
    }
    
    // Calculate HL2
    const hl2 = high.map((h, i) => (h + low[i]) / 2);
    
    // Calculate Moving Averages
    const ma_coloured = this.variant(this.config.typeColoured, close, this.config.lenColoured);
    const ma_fast = this.variant(this.config.typeFast, close, this.config.lenFast);
    const ma_medium = this.variant(this.config.typeMedium, close, this.config.lenMedium);
    const ma_slow = this.variant(this.config.typeSlow, close, this.config.lenSlow);
    
    // Get Coloured MA Direction
    const ma_coloured_values = close.map((_, i) => 
      this.variant(this.config.typeColoured, close.slice(0, i + 1), this.config.lenColoured)
    ).filter(v => v !== null);
    
    let clrdirection = this.rising(ma_coloured_values, 2) ? 1 : 
                       this.falling(ma_coloured_values, 2) ? -1 : 
                       (this.prevValues.clrdirection || 1);
    this.prevValues.clrdirection = clrdirection;
    
    // Get 3xMA Trend Direction
    let madirection = 0;
    const { disableFastMAFilter, disableMediumMAFilter, disableSlowMAFilter } = this.config;
    
    if (!disableFastMAFilter && !disableMediumMAFilter && !disableSlowMAFilter) {
      madirection = (ma_fast > ma_medium && ma_medium > ma_slow) ? 1 :
                   (ma_fast < ma_medium && ma_medium < ma_slow) ? -1 : 0;
    } else if (disableSlowMAFilter && !disableFastMAFilter && !disableMediumMAFilter) {
      madirection = ma_fast > ma_medium ? 1 : ma_fast < ma_medium ? -1 : 0;
    } else if (disableMediumMAFilter && !disableFastMAFilter && !disableSlowMAFilter) {
      madirection = ma_fast > ma_slow ? 1 : ma_fast < ma_slow ? -1 : 0;
    } else if (disableFastMAFilter && !disableMediumMAFilter && !disableSlowMAFilter) {
      madirection = ma_medium > ma_slow ? 1 : ma_medium < ma_slow ? -1 : 0;
    } else if (disableFastMAFilter && disableMediumMAFilter) {
      madirection = ma_coloured > ma_slow ? 1 : -1;
    } else if (disableFastMAFilter && disableSlowMAFilter) {
      madirection = ma_coloured > ma_medium ? 1 : -1;
    } else if (disableSlowMAFilter && disableMediumMAFilter) {
      madirection = ma_coloured > ma_fast ? 1 : -1;
    }
    
    // SuperTrend Calculation
    const atrValue = this.atr(high, low, close, this.config.SPd);
    const SUp = hl2[hl2.length - 1] - (this.config.SFactor * atrValue);
    const SDn = hl2[hl2.length - 1] + (this.config.SFactor * atrValue);
    
    let STrendUp = this.prevValues.STrendUp || SUp;
    let STrendDown = this.prevValues.STrendDown || SDn;
    
    STrendUp = close[close.length - 2] > STrendUp ? Math.max(SUp, STrendUp) : SUp;
    STrendDown = close[close.length - 2] < STrendDown ? Math.min(SDn, STrendDown) : SDn;
    
    let STrend = close[close.length - 1] > STrendDown ? 1 : 
                 close[close.length - 1] < STrendUp ? -1 : 
                 (this.prevValues.STrend || 1);
    
    const Tsl = STrend === 1 ? STrendUp : STrendDown;
    
    this.prevValues.STrendUp = STrendUp;
    this.prevValues.STrendDown = STrendDown;
    this.prevValues.STrend = STrend;
    
    // Bollinger Bands
    const basis = this.sma(close, this.config.bbLength);
    const dev = this.config.bbStddev * this.stdev(close, this.config.bbLength);
    const upper = basis + dev;
    const lower = basis - dev;
    
    // Outside In Calculation
    const noiupper = Math.abs(this.highestBars(high, this.config.oiLength));
    const noilower = Math.abs(this.lowestBars(low, this.config.oiLength));
    
    // MA Cross values
    const ma_fast_values = close.map((_, i) => 
      this.variant(this.config.typeFast, close.slice(0, i + 1), this.config.lenFast)
    ).filter(v => v !== null);
    
    const ma_coloured_full = close.map((_, i) => 
      this.variant(this.config.typeColoured, close.slice(0, i + 1), this.config.lenColoured)
    ).filter(v => v !== null);
    
    const oiMACrossupper = this.crossunder(ma_fast_values, ma_coloured_full) && 
                           noiupper > 0 && 
                           this.highest(high, this.config.oiLength) > upper ? 1 : 0;
                           
    const oiMACrosslower = this.crossover(ma_fast_values, ma_coloured_full) && 
                           noilower > 0 && 
                           this.lowest(low, this.config.oiLength) < lower ? 1 : 0;
    
    // Signal Generation (simplified - you'll need to implement full logic)
    const signals = this.generateSignals({
      clrdirection,
      madirection,
      STrend,
      ma_fast,
      ma_medium,
      ma_slow,
      ma_coloured,
      ma_fast_values,
      ma_coloured_full,
      close: close[close.length - 1],
      oiMACrossupper,
      oiMACrosslower
    });
    
    return {
      ma_coloured,
      ma_fast,
      ma_medium,
      ma_slow,
      clrdirection,
      madirection,
      STrend,
      Tsl,
      upper,
      lower,
      basis,
      signals
    };
  }
  
  generateSignals(data) {
    const { clrdirection, madirection, STrend, ma_fast, ma_coloured, close, 
            oiMACrossupper, oiMACrosslower, ma_fast_values, ma_coloured_full } = data;
    
    const disable3xMAFilter = this.config.disableFastMAFilter && 
                             this.config.disableMediumMAFilter && 
                             this.config.disableSlowMAFilter;
    
    let long = false;
    let short = false;
    
    const filterOption = this.config.filterOption;
    
    // 3xMA Trend Filter
    if (filterOption === '3xMATrend') {
      long = clrdirection === 1 && close > ma_fast && madirection === 1;
      short = clrdirection === -1 && close < ma_fast && madirection === -1;
    }
    
    // SuperTrend Filter
    else if (filterOption === 'SuperTrend') {
      long = clrdirection === 1 && STrend === 1;
      short = clrdirection === -1 && STrend === -1;
    }
    
    // SuperTrend + 3xMA
    else if (filterOption === 'SuperTrend+3xMA') {
      long = (disable3xMAFilter || (clrdirection === 1 && close > ma_fast && madirection === 1)) && 
             clrdirection === 1 && STrend === 1;
      short = (disable3xMAFilter || (clrdirection === -1 && close < ma_fast && madirection === -1)) && 
              clrdirection === -1 && STrend === -1;
    }
    
    // MA Cross
    else if (filterOption === 'MACross') {
      long = this.crossover(ma_fast_values, ma_coloured_full);
      short = this.crossunder(ma_fast_values, ma_coloured_full);
    }
    
    // Outside In filters
    else if (filterOption.includes('OutsideIn')) {
      if (filterOption === 'OutsideIn:MACross') {
        long = oiMACrosslower === 1;
        short = oiMACrossupper === 1;
      } else if (filterOption === 'OutsideIn:MACross+3xMA') {
        long = oiMACrosslower === 1 && (disable3xMAFilter || madirection === 1);
        short = oiMACrossupper === 1 && (disable3xMAFilter || madirection === -1);
      } else if (filterOption === 'OutsideIn:MACross+ST') {
        long = oiMACrosslower === 1 && STrend === 1;
        short = oiMACrossupper === 1 && STrend === -1;
      }
    }
    
    // No Filters
    else if (filterOption === 'ColouredMA') {
      long = clrdirection === 1;
      short = clrdirection === -1;
    }
    
    return { long, short };
  }
}

// === USAGE EXAMPLE ===

// Create instance
const snapper = new BigSnapperAlerts({
  typeColoured: 'HullMA',
  lenColoured: 18,
  filterOption: 'SuperTrend',
  SFactor: 3.618,
  SPd: 5
});

// Prepare OHLCV data
const ohlcvData = {
  open: [/* your open prices */],
  high: [/* your high prices */],
  low: [/* your low prices */],
  close: [/* your close prices */],
  volume: [/* your volumes */]
};

// Calculate signals
const result = snapper.calculate(ohlcvData);

if (result) {
  console.log('Signals:', result.signals);
  console.log('Long:', result.signals.long);
  console.log('Short:', result.signals.short);
  console.log('Coloured MA:', result.ma_coloured);
  console.log('SuperTrend:', result.Tsl);
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = BigSnapperAlerts;
}