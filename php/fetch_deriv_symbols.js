/**
 * fetch_deriv_symbols.js
 * ดึงรายการ asset code และ asset name ทั้งหมดจาก Deriv WebSocket API (Public Options/Synthetics/Forex/Crypto/Commodities)
 */

const WebSocket = require('ws');

function fetchSymbols(timeoutMs = 15000) {
  return new Promise((resolve, reject) => {
    const wsUrl = 'wss://api.derivws.com/trading/v1/options/ws/public';
    let settled = false;

    const timer = setTimeout(() => {
      if (!settled) {
        settled = true;
        try { ws.close(); } catch(e) {}
        reject(new Error(`Timeout after ${timeoutMs}ms connecting to Deriv WebSocket`));
      }
    }, timeoutMs);

    const ws = new WebSocket(wsUrl);

    ws.on('open', () => {
      ws.send(JSON.stringify({ active_symbols: 'full' }));
    });

    ws.on('message', (msg) => {
      if (settled) return;
      try {
        const data = JSON.parse(msg.toString());
        if (data.error) {
          settled = true;
          clearTimeout(timer);
          ws.close();
          return reject(new Error(data.error.message || 'Deriv API Error'));
        }

        if (Array.isArray(data.active_symbols)) {
          settled = true;
          clearTimeout(timer);
          ws.close();

          const formatted = data.active_symbols.map((item) => {
            const assetCode = item.underlying_symbol || item.symbol || '';
            const assetName = item.underlying_symbol_name || item.display_name || assetCode;
            const market = item.market || '';
            const submarket = item.submarket || '';
            const subgroup = item.subgroup || 'none';
            const symbolType = item.underlying_symbol_type || item.symbol_type || '';
            const pipSize = typeof item.pip_size === 'number' ? item.pip_size : 0.0001;
            const exchangeIsOpen = item.exchange_is_open ? 1 : 0;
            const isSuspended = item.is_trading_suspended ? 1 : 0;

            return {
              asset_code: assetCode,
              asset_name: assetName,
              market: market,
              submarket: submarket,
              subgroup: subgroup,
              symbol_type: symbolType,
              pip_size: pipSize,
              exchange_is_open: exchangeIsOpen,
              is_trading_suspended: isSuspended,
              is_active: 1
            };
          }).filter(item => item.asset_code.length > 0);

          resolve(formatted);
        }
      } catch (err) {
        settled = true;
        clearTimeout(timer);
        ws.close();
        reject(err);
      }
    });

    ws.on('error', (err) => {
      if (!settled) {
        settled = true;
        clearTimeout(timer);
        reject(err);
      }
    });
  });
}

async function main() {
  try {
    const symbols = await fetchSymbols();
    process.stdout.write(JSON.stringify(symbols));
    process.exit(0);
  } catch (err) {
    process.stderr.write(JSON.stringify({ error: err.message }));
    process.exit(1);
  }
}

if (require.main === module) {
  main();
}

module.exports = { fetchSymbols };
