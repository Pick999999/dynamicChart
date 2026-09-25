// Supertrend Calculator for Lightweight Charts 3.8
class Supertrend {
  constructor(atrPeriod = 10, factor = 3.0) {
    this.atrPeriod = atrPeriod;
    this.factor = factor;
  }

  // Calculate True Range
  calculateTR(high, low, prevClose) {
    const hl = high - low;
    const hc = Math.abs(high - prevClose);
    const lc = Math.abs(low - prevClose);
    return Math.max(hl, hc, lc);
  }

  // Calculate ATR using SMA
  calculateATR(data) {
    const atr = [];
    let trSum = 0;

    for (let i = 0; i < data.length; i++) {
      if (i === 0) {
        atr.push(0);
        continue;
      }

      const tr = this.calculateTR(data[i].high, data[i].low, data[i - 1].close);

      if (i < this.atrPeriod) {
        trSum += tr;
        if (i === this.atrPeriod - 1) {
          atr.push(trSum / this.atrPeriod);
        } else {
          atr.push(0);
        }
      } else {
        const newATR = (atr[i - 1] * (this.atrPeriod - 1) + tr) / this.atrPeriod;
        atr.push(newATR);
      }
    }

    return atr;
  }

  // Calculate Supertrend
  calculate(data) {
    if (data.length < this.atrPeriod) {
      return { upTrend: [], downTrend: [], signals: [] };
    }

    const atr = this.calculateATR(data);
    const supertrend = [];
    const direction = []; // 1 = down, -1 = up
    const upTrend = [];
    const downTrend = [];
    const signals = [];

    for (let i = 0; i < data.length; i++) {
      if (i < this.atrPeriod - 1) {
        supertrend.push(null);
        direction.push(0);
        upTrend.push(null);
        downTrend.push(null);
        continue;
      }

      const hl2 = (data[i].high + data[i].low) / 2;
      const upperBand = hl2 + (this.factor * atr[i]);
      const lowerBand = hl2 - (this.factor * atr[i]);

      // Initialize
      if (i === this.atrPeriod - 1) {
        supertrend.push(lowerBand);
        direction.push(-1);
        upTrend.push({ time: data[i].time, value: lowerBand });
        downTrend.push(null);
        continue;
      }

      // Calculate final bands
      let finalUpperBand = upperBand;
      let finalLowerBand = lowerBand;

      if (upperBand < supertrend[i - 1] || data[i - 1].close > supertrend[i - 1]) {
        finalUpperBand = upperBand;
      } else {
        finalUpperBand = supertrend[i - 1];
      }

      if (lowerBand > supertrend[i - 1] || data[i - 1].close < supertrend[i - 1]) {
        finalLowerBand = lowerBand;
      } else {
        finalLowerBand = supertrend[i - 1];
      }

      // Determine trend
      let currentDirection;
      let currentSupertrend;

      if (supertrend[i - 1] === finalUpperBand) {
        currentDirection = data[i].close <= finalUpperBand ? 1 : -1;
      } else {
        currentDirection = data[i].close >= finalLowerBand ? -1 : 1;
      }

      currentSupertrend = currentDirection === -1 ? finalLowerBand : finalUpperBand;

      supertrend.push(currentSupertrend);
      direction.push(currentDirection);

      // Store trend lines
      if (currentDirection === -1) {
        upTrend.push({ time: data[i].time, value: currentSupertrend });
        downTrend.push(null);
      } else {
        upTrend.push(null);
        downTrend.push({ time: data[i].time, value: currentSupertrend });
      }

      // Detect signals
      if (i > this.atrPeriod && direction[i] !== direction[i - 1]) {
        if (currentDirection === -1) {
          signals.push({
            time: data[i].time,
            position: 'belowBar',
            color: '#26a69a',
            shape: 'arrowUp',
            text: 'Buy'
          });
        } else {
          signals.push({
            time: data[i].time,
            position: 'aboveBar',
            color: '#ef5350',
            shape: 'arrowDown',
            text: 'Sell'
          });
        }
      }
    }

    return { upTrend, downTrend, signals };
  }
}

// Example usage with Lightweight Charts 3.8
function setupSupertrendChart(container, candleData) {
  // Create chart
  const chart = LightweightCharts.createChart(container, {
    width: container.offsetWidth,
    height: 600,
    layout: {
      backgroundColor: '#1e222d',
      textColor: '#d1d4dc',
    },
    grid: {
      vertLines: { color: '#2b2b43' },
      horzLines: { color: '#363c4e' },
    },
    crosshair: {
      mode: LightweightCharts.CrosshairMode.Normal,
    },
    timeScale: {
      borderColor: '#485c7b',
    },
  });

  // Add candlestick series
  const candleSeries = chart.addCandlestickSeries({
    upColor: '#26a69a',
    downColor: '#ef5350',
    borderVisible: false,
    wickUpColor: '#26a69a',
    wickDownColor: '#ef5350',
  });
  candleSeries.setData(candleData);

  // Calculate Supertrend
  const supertrend = new Supertrend(10, 3.0);
  const result = supertrend.calculate(candleData);

  // Add uptrend line
  const upTrendSeries = chart.addLineSeries({
    color: '#26a69a',
    lineWidth: 2,
    title: 'Uptrend',
  });
  upTrendSeries.setData(result.upTrend.filter(d => d !== null));

  // Add downtrend line
  const downTrendSeries = chart.addLineSeries({
    color: '#ef5350',
    lineWidth: 2,
    title: 'Downtrend',
  });
  downTrendSeries.setData(result.downTrend.filter(d => d !== null));

  // Add markers for buy/sell signals
  candleSeries.setMarkers(result.signals);

  // Handle resize
  window.addEventListener('resize', () => {
    chart.applyOptions({ width: container.offsetWidth });
  });

  return { chart, candleSeries, upTrendSeries, downTrendSeries, signals: result.signals };
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { Supertrend, setupSupertrendChart };
}