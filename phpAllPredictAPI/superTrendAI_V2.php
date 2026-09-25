<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SupertrendAI + Deriv.com Live Data</title>
  <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
    }

    h1 {
      color: white;
      text-align: center;
      margin-bottom: 30px;
      font-size: 2.5rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .controls {
      background: white;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      border-bottom: 2px solid #e0e0e0;
    }

    .tab {
      padding: 12px 24px;
      background: transparent;
      border: none;
      font-size: 16px;
      font-weight: 600;
      color: #666;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      transition: all 0.3s;
    }

    .tab.active {
      color: #667eea;
      border-bottom-color: #667eea;
    }

    .tab:hover {
      color: #667eea;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .control-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 15px;
      align-items: end;
    }

    .control-group {
      display: flex;
      flex-direction: column;
    }

    label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      font-size: 14px;
    }

    input[type="number"],
    select {
      padding: 10px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s;
    }

    input[type="number"]:focus,
    select:focus {
      outline: none;
      border-color: #667eea;
    }

    textarea {
      width: 100%;
      min-height: 150px;
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-family: 'Courier New', monospace;
      font-size: 13px;
      resize: vertical;
      transition: border-color 0.3s;
    }

    textarea:focus {
      outline: none;
      border-color: #667eea;
    }

    .hint {
      color: #666;
      font-size: 12px;
      margin-top: 5px;
    }

    button {
      padding: 12px 30px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    button:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    }

    button:active:not(:disabled) {
      transform: translateY(0);
    }

    button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .sample-btn {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
      padding: 10px 20px;
      font-size: 14px;
    }

    #chart {
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      overflow: hidden;
    }

    .info-box {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .info-box h3 {
      color: #333;
      margin-bottom: 15px;
    }

    .signals-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 10px;
    }

    .signal-item {
      padding: 10px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
    }

    .signal-buy {
      background: #e8f5e9;
      color: #2e7d32;
      border-left: 4px solid #4caf50;
    }

    .signal-sell {
      background: #ffebee;
      color: #c62828;
      border-left: 4px solid #f44336;
    }

    .error {
      background: #ffebee;
      color: #c62828;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      border-left: 4px solid #f44336;
    }

    .success {
      background: #e8f5e9;
      color: #2e7d32;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      border-left: 4px solid #4caf50;
    }

    .info {
      background: #e3f2fd;
      color: #1565c0;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      border-left: 4px solid #2196f3;
    }

    .warning {
      background: #fff3e0;
      color: #e65100;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      border-left: 4px solid #ff9800;
    }

    .auto-refresh-active {
      background: #e8f5e9;
      color: #2e7d32;
      padding: 15px;
      border-radius: 8px;
      margin-top: 15px;
      border-left: 4px solid #4caf50;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .next-refresh {
      font-weight: 600;
      font-size: 16px;
    }

    .stop-btn {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
      padding: 8px 20px;
      font-size: 14px;
    }

    .loading {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .spinner {
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #667eea;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .status-indicator {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 8px;
    }

    .status-connected {
      background: #4caf50;
      box-shadow: 0 0 8px #4caf50;
    }

    .status-disconnected {
      background: #f44336;
    }

    @media (max-width: 768px) {
      .control-row {
        grid-template-columns: 1fr;
      }
      
      h1 {
        font-size: 1.8rem;
      }

      .tabs {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>📈 SupertrendAI + Deriv.com Live</h1>
    
    <div class="controls">
      <div class="tabs">
        <button class="tab active" onclick="switchTab('deriv')">🌐 Deriv Live Data</button>
        <button class="tab" onclick="switchTab('manual')">✏️ Manual Data</button>
      </div>

      <!-- Deriv Tab -->
      <div id="derivTab" class="tab-content active">
        <div class="control-row">
          <div class="control-group">
            <label for="symbol">Symbol</label>
            <select id="symbol">
              <option value="R_10">Volatility 10 Index</option>
              <option value="R_25">Volatility 25 Index</option>
              <option value="R_50">Volatility 50 Index</option>
              <option value="R_75">Volatility 75 Index</option>
              <option value="R_100">Volatility 100 Index</option>
              <option value="1HZ10V">Volatility 10 (1s) Index</option>
              <option value="1HZ25V">Volatility 25 (1s) Index</option>
              <option value="1HZ50V">Volatility 50 (1s) Index</option>
              <option value="1HZ75V">Volatility 75 (1s) Index</option>
              <option value="1HZ100V">Volatility 100 (1s) Index</option>
              <option value="frxEURUSD">EUR/USD</option>
              <option value="frxGBPUSD">GBP/USD</option>
              <option value="frxUSDJPY">USD/JPY</option>
              <option value="frxAUDUSD">AUD/USD</option>
            </select>
          </div>

          <div class="control-group">
            <label for="granularity">Timeframe</label>
            <select id="granularity">
              <option value="60">1 minute</option>
              <option value="120">2 minutes</option>
              <option value="180">3 minutes</option>
              <option value="300">5 minutes</option>
              <option value="600">10 minutes</option>
              <option value="900">15 minutes</option>
              <option value="1800">30 minutes</option>
              <option value="3600">1 hour</option>
              <option value="14400">4 hours</option>
              <option value="28800">8 hours</option>
              <option value="86400">1 day</option>
            </select>
          </div>

          <div class="control-group">
            <label for="count">Number of Candles</label>
            <input type="number" id="count" value="100" min="10" max="1000">
          </div>
        </div>

        <div class="control-row">
          <div class="control-group">
            <label for="atrPeriodDeriv">ATR Length</label>
            <input type="number" id="atrPeriodDeriv" value="10" min="1" max="100">
          </div>
          
          <div class="control-group">
            <label for="factorDeriv">Factor</label>
            <input type="number" id="factorDeriv" value="3.0" min="0.1" max="10" step="0.1">
          </div>
          
          <div class="control-group">
            <label for="chartHeightDeriv">Chart Height</label>
            <input type="number" id="chartHeightDeriv" value="600" min="300" max="1000" step="50">
          </div>
          
          <div class="control-group">
            <label>&nbsp;</label>
            <button onclick="fetchDerivData()" id="fetchBtn">🚀 Fetch & Analyze</button>
          </div>
        </div>

        <div class="control-row" style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-top: 15px;">
          <div class="control-group">
            <label style="display: flex; align-items: center; cursor: pointer;">
              <input type="checkbox" id="autoRefresh" onchange="toggleAutoRefresh()" style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
              <span style="font-size: 16px;">🔄 Auto-refresh every N minutes</span>
            </label>
          </div>
          
          <div class="control-group">
            <label for="refreshInterval">Refresh Interval (minutes)</label>
            <input type="number" id="refreshInterval" value="5" min="1" max="60">
          </div>

          <div class="control-group">
            <label style="display: flex; align-items: center; cursor: pointer;">
              <input type="checkbox" id="enableNotifications" checked style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
              <span style="font-size: 16px;">🔔 Enable Signal Notifications</span>
            </label>
          </div>

          <div class="control-group">
            <label style="display: flex; align-items: center; cursor: pointer;">
              <input type="checkbox" id="enableSound" checked style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
              <span style="font-size: 16px;">🔊 Enable Sound Alert</span>
            </label>
          </div>
        </div>

        <div id="connectionStatus"></div>
        <div id="autoRefreshStatus"></div>
      </div>

      <!-- Manual Tab -->
      <div id="manualTab" class="tab-content">
        <div class="control-row">
          <div class="control-group">
            <label for="atrPeriod">ATR Length</label>
            <input type="number" id="atrPeriod" value="10" min="1" max="100">
          </div>
          
          <div class="control-group">
            <label for="factor">Factor</label>
            <input type="number" id="factor" value="3.0" min="0.1" max="10" step="0.1">
          </div>
          
          <div class="control-group">
            <label for="chartHeight">Chart Height</label>
            <input type="number" id="chartHeight" value="600" min="300" max="1000" step="50">
          </div>
          
          <div class="control-group">
            <label>&nbsp;</label>
            <button onclick="updateChart()">🚀 Update Chart</button>
          </div>
        </div>
        
        <div class="control-group">
          <label for="candleData">
            Candle Data (JSON Format)
            <button class="sample-btn" onclick="loadSampleData()">📊 Load Sample Data</button>
          </label>
          <textarea id="candleData" placeholder='[{"time": "2024-01-01", "open": 100, "high": 105, "low": 95, "close": 102}, ...]'></textarea>
          <div class="hint">
            Format: [{"time": "YYYY-MM-DD", "open": number, "high": number, "low": number, "close": number}, ...]
          </div>
        </div>
      </div>
    </div>

    <div id="messageBox"></div>
    
    <div id="chart"></div>
    
    <div class="info-box">
      <h3>📊 Signals Summary</h3>
      <div id="signalsInfo" class="signals-list"></div>
    </div>
  </div>

  <script>
    // SupertrendAI Class
    class SupertrendAI {
      constructor(atrPeriod = 10, factor = 3.0) {
        this.atrPeriod = atrPeriod;
        this.factor = factor;
      }

      calculateTR(high, low, prevClose) {
        if (prevClose === undefined) return high - low;
        const hl = high - low;
        const hc = Math.abs(high - prevClose);
        const lc = Math.abs(low - prevClose);
        return Math.max(hl, hc, lc);
      }

      calculateATR(data) {
        const atr = new Array(data.length).fill(null);
        if (data.length < this.atrPeriod) return atr;

        let trSum = 0;
        for (let i = 1; i <= this.atrPeriod; i++) {
          const tr = this.calculateTR(data[i].high, data[i].low, data[i - 1].close);
          trSum += tr;
        }
        
        atr[this.atrPeriod] = trSum / this.atrPeriod;
        
        for (let i = this.atrPeriod + 1; i < data.length; i++) {
          const tr = this.calculateTR(data[i].high, data[i].low, data[i - 1].close);
          atr[i] = (atr[i - 1] * (this.atrPeriod - 1) + tr) / this.atrPeriod;
        }
        
        return atr;
      }

      calculateSupertrend(data) {
        const atr = this.calculateATR(data);
        const supertrend = new Array(data.length).fill(null);
        const direction = new Array(data.length).fill(null);
        
        if (data.length <= this.atrPeriod) {
          return { supertrend, direction };
        }

        let finalUpperBand = null;
        let finalLowerBand = null;
        let currentTrend = 1;

        for (let i = this.atrPeriod; i < data.length; i++) {
          if (atr[i] === null) continue;

          const hl2 = (data[i].high + data[i].low) / 2;
          let upperBand = hl2 + this.factor * atr[i];
          let lowerBand = hl2 - this.factor * atr[i];

          if (finalUpperBand === null || upperBand < finalUpperBand || data[i - 1].close > finalUpperBand) {
            finalUpperBand = upperBand;
          }
          
          if (finalLowerBand === null || lowerBand > finalLowerBand || data[i - 1].close < finalLowerBand) {
            finalLowerBand = lowerBand;
          }

          if (currentTrend === 1) {
            if (data[i].close > finalUpperBand) {
              currentTrend = -1;
              finalLowerBand = lowerBand;
            }
          } else {
            if (data[i].close < finalLowerBand) {
              currentTrend = 1;
              finalUpperBand = upperBand;
            }
          }

          supertrend[i] = currentTrend === 1 ? finalUpperBand : finalLowerBand;
          direction[i] = currentTrend;
        }

        return { supertrend, direction };
      }

      detectChange(array) {
        const changes = new Array(array.length).fill(null);
        for (let i = 1; i < array.length; i++) {
          if (array[i] !== null && array[i - 1] !== null) {
            changes[i] = array[i] - array[i - 1];
          }
        }
        return changes;
      }

      calculate(data) {
        const { supertrend, direction } = this.calculateSupertrend(data);
        const directionChanged = this.detectChange(direction);
        
        const upTrend = [];
        const downTrend = [];
        const signals = [];

        for (let i = 0; i < data.length; i++) {
          const time = data[i].time;

          if (direction[i] !== null) {
            if (direction[i] < 0) {
              upTrend.push({ time: time, value: supertrend[i] });
              downTrend.push({ time: time, value: null });
            } else {
              upTrend.push({ time: time, value: null });
              downTrend.push({ time: time, value: supertrend[i] });
            }
          } else {
            upTrend.push({ time: time, value: null });
            downTrend.push({ time: time, value: null });
          }

          if (directionChanged[i] !== null) {
            if (directionChanged[i] < 0) {
              signals.push({
                time: time,
                position: 'belowBar',
                color: '#26a69a',
                shape: 'arrowUp',
                text: 'Buy'
              });
            } else if (directionChanged[i] > 0) {
              signals.push({
                time: time,
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

    // Deriv API Handler
    class DerivAPI {
      constructor() {
        this.ws = null;
        this.requestId = 1;
        this.callbacks = {};
      }

      connect() {
        return new Promise((resolve, reject) => {
          try {
            this.ws = new WebSocket('wss://ws.derivws.com/websockets/v3?app_id=1089');
            
            this.ws.onopen = () => {
              showMessage('Connected to Deriv API', 'success');
              updateConnectionStatus(true);
              resolve();
            };

            this.ws.onmessage = (msg) => {
              const data = JSON.parse(msg.data);
              if (data.req_id && this.callbacks[data.req_id]) {
                this.callbacks[data.req_id](data);
                delete this.callbacks[data.req_id];
              }
            };

            this.ws.onerror = (error) => {
              showMessage('WebSocket error: ' + error.message, 'error');
              updateConnectionStatus(false);
              reject(error);
            };

            this.ws.onclose = () => {
              updateConnectionStatus(false);
            };
          } catch (error) {
            reject(error);
          }
        });
      }

      async getCandles(symbol, granularity, count) {
        if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
          await this.connect();
        }

        return new Promise((resolve, reject) => {
          const reqId = this.requestId++;
          
          this.callbacks[reqId] = (response) => {
            if (response.error) {
              reject(new Error(response.error.message));
            } else if (response.candles) {
              resolve(response.candles);
            } else {
              reject(new Error('Invalid response format'));
            }
          };

          const request = {
            ticks_history: symbol,
            adjust_start_time: 1,
            count: count,
            end: 'latest',
            granularity: parseInt(granularity),
            start: 1,
            style: 'candles',
            req_id: reqId
          };

          this.ws.send(JSON.stringify(request));
        });
      }

      disconnect() {
        if (this.ws) {
          this.ws.close();
          this.ws = null;
        }
      }
    }

    // Global variables
    let chart = null;
    let candleSeries = null;
    let upTrendSeries = null;
    let downTrendSeries = null;
    let derivAPI = new DerivAPI();
    let autoRefreshInterval = null;
    let nextRefreshTimeout = null;
    let lastSignals = [];
    let notificationPermission = false;

    // Request notification permission
    async function requestNotificationPermission() {
      if ('Notification' in window) {
        const permission = await Notification.requestPermission();
        notificationPermission = permission === 'granted';
        return notificationPermission;
      }
      return false;
    }

    // Play sound alert
    function playSoundAlert(type) {
      if (!document.getElementById('enableSound').checked) return;
      
      const audioContext = new (window.AudioContext || window.webkitAudioContext)();
      const oscillator = audioContext.createOscillator();
      const gainNode = audioContext.createGain();
      
      oscillator.connect(gainNode);
      gainNode.connect(audioContext.destination);
      
      // Different frequencies for Buy/Sell
      oscillator.frequency.value = type === 'buy' ? 800 : 400;
      oscillator.type = 'sine';
      
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
      
      oscillator.start(audioContext.currentTime);
      oscillator.stop(audioContext.currentTime + 0.5);
    }

    // Send notification
    function sendNotification(signal) {
      if (!document.getElementById('enableNotifications').checked) return;
      
      const title = signal.text === 'Buy' ? '🟢 BUY SIGNAL!' : '🔴 SELL SIGNAL!';
      const body = `${document.getElementById('symbol').options[document.getElementById('symbol').selectedIndex].text}\nTime: ${new Date(signal.time * 1000).toLocaleString()}`;
      
      // Browser notification
      if (notificationPermission && 'Notification' in window) {
        new Notification(title, {
          body: body,
          icon: signal.text === 'Buy' ? 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="%2326a69a"/><path d="M50 20 L50 80 M30 40 L50 20 L70 40" stroke="white" stroke-width="8" fill="none"/></svg>' : 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="%23ef5350"/><path d="M50 80 L50 20 M30 60 L50 80 L70 60" stroke="white" stroke-width="8" fill="none"/></svg>',
          tag: 'supertrend-signal',
          requireInteraction: true
        });
      }
      
      // Play sound
      playSoundAlert(signal.text.toLowerCase());
      
      // Visual alert
      showMessage(`${title}\n${body}`, signal.text === 'Buy' ? 'success' : 'warning');
    }

    // Check for new signals
    function checkNewSignals(signals) {
      if (signals.length === 0) return;
      
      // Get the latest signal
      const latestSignal = signals[signals.length - 1];
      
      // Check if this is a new signal (not in lastSignals)
      const isNewSignal = !lastSignals.some(s => 
        s.time === latestSignal.time && s.text === latestSignal.text
      );
      
      if (isNewSignal) {
        sendNotification(latestSignal);
      }
      
      lastSignals = signals;
    }

    // Toggle auto refresh
    function toggleAutoRefresh() {
      const isEnabled = document.getElementById('autoRefresh').checked;
      
      if (isEnabled) {
        startAutoRefresh();
      } else {
        stopAutoRefresh();
      }
    }

    // Start auto refresh
    async function startAutoRefresh() {
      // Request notification permission
      await requestNotificationPermission();
      
      const intervalMinutes = parseInt(document.getElementById('refreshInterval').value);
      
      // Clear any existing intervals
      stopAutoRefresh();
      
      // Show status
      updateAutoRefreshStatus(intervalMinutes, true);
      
      // Fetch data immediately
      await fetchDerivData();
      
      // Schedule next fetch at the beginning of next interval
      scheduleNextFetch(intervalMinutes);
    }

    // Schedule next fetch at the start of the interval
    function scheduleNextFetch(intervalMinutes) {
      const now = new Date();
      const currentSeconds = now.getSeconds();
      const currentMinutes = now.getMinutes();
      
      // Calculate next trigger time (at second 2-3 of next interval)
      const minutesUntilNext = intervalMinutes - (currentMinutes % intervalMinutes);
      const nextTriggerTime = new Date(now);
      nextTriggerTime.setMinutes(now.getMinutes() + minutesUntilNext);
      nextTriggerTime.setSeconds(2); // Trigger at second 2
      nextTriggerTime.setMilliseconds(0);
      
      // If we're past second 2 of current interval, move to next interval
      if (minutesUntilNext === 0 && currentSeconds > 3) {
        nextTriggerTime.setMinutes(nextTriggerTime.getMinutes() + intervalMinutes);
      }
      
      const msUntilNext = nextTriggerTime - now;
      
      // Update countdown
      updateCountdown(nextTriggerTime, intervalMinutes);
      
      // Schedule the fetch
      nextRefreshTimeout = setTimeout(async () => {
        await fetchDerivData();
        
        // Schedule next fetch
        if (document.getElementById('autoRefresh').checked) {
          scheduleNextFetch(intervalMinutes);
        }
      }, msUntilNext);
    }

    // Update countdown display
    function updateCountdown(nextTime, intervalMinutes) {
      const countdownInterval = setInterval(() => {
        if (!document.getElementById('autoRefresh').checked) {
          clearInterval(countdownInterval);
          return;
        }
        
        const now = new Date();
        const msRemaining = nextTime - now;
        
        if (msRemaining <= 0) {
          clearInterval(countdownInterval);
          return;
        }
        
        const seconds = Math.floor((msRemaining / 1000) % 60);
        const minutes = Math.floor((msRemaining / 1000 / 60) % 60);
        
        updateAutoRefreshStatus(intervalMinutes, false, `${minutes}m ${seconds}s`);
      }, 1000);
    }

    // Stop auto refresh
    function stopAutoRefresh() {
      if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
      }
      
      if (nextRefreshTimeout) {
        clearTimeout(nextRefreshTimeout);
        nextRefreshTimeout = null;
      }
      
      document.getElementById('autoRefreshStatus').innerHTML = '';
    }

    // Update auto refresh status
    function updateAutoRefreshStatus(intervalMinutes, isStarting, countdown = '') {
      const statusDiv = document.getElementById('autoRefreshStatus');
      
      if (isStarting) {
        statusDiv.innerHTML = `
          <div class="auto-refresh-active">
            <div>
              <strong>🔄 Auto-refresh Active</strong><br>
              <span style="font-size: 14px;">Fetching every ${intervalMinutes} minute${intervalMinutes > 1 ? 's' : ''} at seconds 2-3</span>
            </div>
            <button class="stop-btn" onclick="document.getElementById('autoRefresh').checked = false; stopAutoRefresh();">⏹️ Stop</button>
          </div>
        `;
      } else if (countdown) {
        statusDiv.innerHTML = `
          <div class="auto-refresh-active">
            <div>
              <strong>🔄 Auto-refresh Active</strong><br>
              <span class="next-refresh">Next refresh in: ${countdown}</span>
            </div>
            <button class="stop-btn" onclick="document.getElementById('autoRefresh').checked = false; stopAutoRefresh();">⏹️ Stop</button>
          </div>
        `;
      }
    }
    function switchTab(tab) {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      
      if (tab === 'deriv') {
        document.querySelector('.tab:first-child').classList.add('active');
        document.getElementById('derivTab').classList.add('active');
      } else {
        document.querySelector('.tab:last-child').classList.add('active');
        document.getElementById('manualTab').classList.add('active');
      }
    }

    // Update connection status
    function updateConnectionStatus(connected) {
      const statusDiv = document.getElementById('connectionStatus');
      const statusClass = connected ? 'status-connected' : 'status-disconnected';
      const statusText = connected ? 'Connected' : 'Disconnected';
      statusDiv.innerHTML = `
        <div class="info">
          <span class="status-indicator ${statusClass}"></span>
          ${statusText} to Deriv API
        </div>
      `;
    }

    // Fetch data from Deriv
    async function fetchDerivData() {
      try {
        showMessage('', 'clear');
        const fetchBtn = document.getElementById('fetchBtn');
        fetchBtn.disabled = true;
        fetchBtn.innerHTML = '<div class="loading"><div class="spinner"></div>Fetching data...</div>';

        const symbol = document.getElementById('symbol').value;
        const granularity = document.getElementById('granularity').value;
        const count = parseInt(document.getElementById('count').value);
        const atrPeriod = parseInt(document.getElementById('atrPeriodDeriv').value);
        const factor = parseFloat(document.getElementById('factorDeriv').value);
        const chartHeight = parseInt(document.getElementById('chartHeightDeriv').value);

        showMessage(`Fetching ${count} candles for ${symbol}...`, 'info');

        // Fetch candles from Deriv
        const candles = await derivAPI.getCandles(symbol, granularity, count);
        
        // Convert Deriv format to our format
        // Lightweight Charts requires time as Unix timestamp (seconds)
        const candleData = candles.map(candle => ({
          time: candle.epoch, // Use epoch directly (Unix timestamp in seconds)
          open: parseFloat(candle.open),
          high: parseFloat(candle.high),
          low: parseFloat(candle.low),
          close: parseFloat(candle.close)
        }));

        showMessage(`Successfully fetched ${candleData.length} candles! Analyzing...`, 'success');

        // Create chart
        createChartWithData(candleData, atrPeriod, factor, chartHeight);

        fetchBtn.disabled = false;
        fetchBtn.innerHTML = '🚀 Fetch & Analyze';

      } catch (error) {
        showMessage(`❌ Error: ${error.message}`, 'error');
        console.error(error);
        const fetchBtn = document.getElementById('fetchBtn');
        fetchBtn.disabled = false;
        fetchBtn.innerHTML = '🚀 Fetch & Analyze';
      }
    }

    // Create chart with data
    function createChartWithData(candleData, atrPeriod, factor, chartHeight) {
      try {
        // Remove existing chart
        const container = document.getElementById('chart');
        container.innerHTML = '';

        // Create new chart
        chart = LightweightCharts.createChart(container, {
          width: container.offsetWidth,
          height: chartHeight,
          layout: {
            backgroundColor: '#ffffff',
            textColor: '#333',
          },
          grid: {
            vertLines: { color: '#f0f0f0' },
            horzLines: { color: '#f0f0f0' },
          },
          crosshair: {
            mode: LightweightCharts.CrosshairMode.Normal,
          },
          timeScale: {
            borderColor: '#ddd',
            timeVisible: true,
            secondsVisible: false,
          },
          rightPriceScale: {
            borderColor: '#ddd',
          },
        });

        // Add candlestick series
        candleSeries = chart.addCandlestickSeries({
          upColor: '#26a69a',
          downColor: '#ef5350',
          borderVisible: false,
          wickUpColor: '#26a69a',
          wickDownColor: '#ef5350',
        });
        candleSeries.setData(candleData);

        // Calculate Supertrend
        const supertrendAI = new SupertrendAI(atrPeriod, factor);
        const result = supertrendAI.calculate(candleData);

        // Add uptrend line
        upTrendSeries = chart.addLineSeries({
          color: '#26a69a',
          lineWidth: 2,
          title: 'Up Trend',
        });
        upTrendSeries.setData(result.upTrend.filter(d => d.value !== null));

        // Add downtrend line
        downTrendSeries = chart.addLineSeries({
          color: '#ef5350',
          lineWidth: 2,
          title: 'Down Trend',
        });
        downTrendSeries.setData(result.downTrend.filter(d => d.value !== null));

        // Add markers
        candleSeries.setMarkers(result.signals);

        // Check for new signals and notify
        checkNewSignals(result.signals);

        // Update signals info
        updateSignalsInfo(result.signals);

        // Fit content
        chart.timeScale().fitContent();

        // Handle resize
        window.addEventListener('resize', () => {
          chart.applyOptions({ width: container.offsetWidth });
        });

        showMessage('✅ Chart created successfully!', 'success');

      } catch (error) {
        showMessage(`❌ Error creating chart: ${error.message}`, 'error');
        console.error(error);
      }
    }

    // Show message
    function showMessage(message, type) {
      const messageBox = document.getElementById('messageBox');
      if (!message || type === 'clear') {
        messageBox.innerHTML = '';
        return;
      }

      const className = type === 'error' ? 'error' : 
                       type === 'success' ? 'success' : 
                       type === 'warning' ? 'warning' : 'info';
      messageBox.innerHTML = `<div class="${className}">${message}</div>`;
      
      // Auto-hide success/info messages after 5 seconds
      if (type === 'success' || type === 'info') {
        setTimeout(() => {
          if (messageBox.querySelector(`.${className}`)) {
            messageBox.innerHTML = '';
          }
        }, 5000);
      }
    }

    // Update signals info
    function updateSignalsInfo(signals) {
      const signalsInfo = document.getElementById('signalsInfo');
      
      if (signals.length === 0) {
        signalsInfo.innerHTML = '<p style="color: #666;">No signals found</p>';
        return;
      }

      let html = '';
      signals.forEach(signal => {
        const isBuy = signal.text === 'Buy';
        const className = isBuy ? 'signal-buy' : 'signal-sell';
        const emoji = isBuy ? '🟢' : '🔴';
        html += `<div class="${className}">${emoji} ${signal.text} - ${signal.time}</div>`;
      });
      
      signalsInfo.innerHTML = html;
    }

    // Load sample data
    function loadSampleData() {
      // Use Unix timestamps for Lightweight Charts
      const baseTime = Math.floor(new Date('2024-01-01').getTime() / 1000);
      const oneDay = 86400; // seconds in a day
      
      const sampleData = [
        {"time": baseTime,"open":2800,"high":2850,"low":2780,"close":2820},
        {"time": baseTime + oneDay,"open":2820,"high":2880,"low":2810,"close":2860},
        {"time": baseTime + oneDay * 2,"open":2860,"high":2900,"low":2850,"close":2890},
        {"time": baseTime + oneDay * 3,"open":2890,"high":2920,"low":2870,"close":2880},
        {"time": baseTime + oneDay * 4,"open":2880,"high":2910,"low":2860,"close":2900},
        {"time": baseTime + oneDay * 7,"open":2900,"high":2950,"low":2890,"close":2940},
        {"time": baseTime + oneDay * 8,"open":2940,"high":2980,"low":2930,"close":2970},
        {"time": baseTime + oneDay * 9,"open":2970,"high":3000,"low":2950,"close":2990},
        {"time": baseTime + oneDay * 10,"open":2990,"high":3020,"low":2980,"close":3010},
        {"time": baseTime + oneDay * 11,"open":3010,"high":3050,"low":3000,"close":3030},
        {"time": baseTime + oneDay * 14,"open":3030,"high":3040,"low":2990,"close":3000},
        {"time": baseTime + oneDay * 15,"open":3000,"high":3020,"low":2960,"close":2980},
        {"time": baseTime + oneDay * 16,"open":2980,"high":2990,"low":2940,"close":2950},
        {"time": baseTime + oneDay * 17,"open":2950,"high":2970,"low":2920,"close":2930},
        {"time": baseTime + oneDay * 18,"open":2930,"high":2950,"low":2900,"close":2940},
        {"time": baseTime + oneDay * 21,"open":2940,"high":2960,"low":2920,"close":2950},
        {"time": baseTime + oneDay * 22,"open":2950,"high":2980,"low":2940,"close":2970},
        {"time": baseTime + oneDay * 23,"open":2970,"high":3000,"low":2960,"close":2990},
        {"time": baseTime + oneDay * 24,"open":2990,"high":3020,"low":2980,"close":3010},
        {"time": baseTime + oneDay * 25,"open":3010,"high":3050,"low":3000,"close":3040},
        {"time": baseTime + oneDay * 28,"open":3040,"high":3080,"low":3030,"close":3070},
        {"time": baseTime + oneDay * 29,"open":3070,"high":3100,"low":3060,"close":3090},
        {"time": baseTime + oneDay * 30,"open":3090,"high":3120,"low":3080,"close":3110},
        {"time": baseTime + oneDay * 31,"open":3110,"high":3140,"low":3100,"close":3130},
        {"time": baseTime + oneDay * 32,"open":3130,"high":3160,"low":3120,"close":3150},
        {"time": baseTime + oneDay * 35,"open":3150,"high":3170,"low":3130,"close":3140},
        {"time": baseTime + oneDay * 36,"open":3140,"high":3150,"low":3100,"close":3110},
        {"time": baseTime + oneDay * 37,"open":3110,"high":3120,"low":3070,"close":3080},
        {"time": baseTime + oneDay * 38,"open":3080,"high":3100,"low":3050,"close":3060},
        {"time": baseTime + oneDay * 39,"open":3060,"high":3080,"low":3030,"close":3070},
        {"time": baseTime + oneDay * 42,"open":3070,"high":3100,"low":3060,"close":3090},
        {"time": baseTime + oneDay * 43,"open":3090,"high":3120,"low":3080,"close":3110},
        {"time": baseTime + oneDay * 44,"open":3110,"high":3140,"low":3100,"close":3130},
        {"time": baseTime + oneDay * 45,"open":3130,"high":3160,"low":3120,"close":3150},
        {"time": baseTime + oneDay * 46,"open":3150,"high":3180,"low":3140,"close":3170}
      ];
      
      document.getElementById('candleData').value = JSON.stringify(sampleData, null, 2);
      showMessage('', 'clear');
    }

    // Update chart (manual mode)
    function updateChart() {
      try {
        showMessage('', 'clear');
        
        const atrPeriod = parseInt(document.getElementById('atrPeriod').value);
        const factor = parseFloat(document.getElementById('factor').value);
        const chartHeight = parseInt(document.getElementById('chartHeight').value);
        
        const candleDataText = document.getElementById('candleData').value.trim();
        if (!candleDataText) {
          showMessage('⚠️ Please enter candle data or load sample data', 'error');
          return;
        }
        
        const candleData = JSON.parse(candleDataText);
        
        if (!Array.isArray(candleData) || candleData.length === 0) {
          showMessage('⚠️ Invalid data format. Please provide an array of candle objects', 'error');
          return;
        }

        const requiredFields = ['time', 'open', 'high', 'low', 'close'];
        const isValid = candleData.every(candle => 
          requiredFields.every(field => candle.hasOwnProperty(field))
        );
        
        if (!isValid) {
          showMessage('⚠️ Each candle must have: time, open, high, low, close', 'error');
          return;
        }

        createChartWithData(candleData, atrPeriod, factor, chartHeight);

      } catch (error) {
        showMessage(`❌ Error: ${error.message}`, 'error');
        console.error(error);
      }
    }

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
      stopAutoRefresh();
      derivAPI.disconnect();
    });

    // Initialize on page load
    window.addEventListener('load', () => {
      requestNotificationPermission();
    });
  </script>
</body>
</html>