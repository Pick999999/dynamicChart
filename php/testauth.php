<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deriv API Tester (New OTP API)</title>
<style>
  :root {
    --bg: #0d1117;
    --panel: #151b23;
    --border: #2a323c;
    --text: #e6edf3;
    --dim: #8b949e;
    --green: #3fb950;
    --red: #f85149;
    --amber: #d29922;
    --accent: #58a6ff;
    --mono: 'SF Mono', 'Consolas', 'Menlo', monospace;
  }
  * { box-sizing: border-box; }
  body {
    background: var(--bg);
    color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    margin: 0;
    padding: 24px;
    min-height: 100vh;
  }
  .wrap { max-width: 920px; margin: 0 auto; }
  h1 {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .subtitle { color: var(--dim); font-size: 13px; margin-bottom: 20px; }

  .status-dot {
    width: 9px; height: 9px; border-radius: 50%;
    background: var(--red);
    box-shadow: 0 0 0 3px rgba(248,81,73,0.15);
    transition: all .2s;
  }
  .status-dot.connected {
    background: var(--green);
    box-shadow: 0 0 0 3px rgba(63,185,80,0.15);
  }
  .status-dot.connecting {
    background: var(--amber);
    box-shadow: 0 0 0 3px rgba(210,153,34,0.15);
  }

  .panel {
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 16px;
  }
  .panel h2 {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--dim);
    margin: 0 0 14px;
    font-weight: 600;
  }

  .row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
  .field { flex: 1; min-width: 160px; }
  label {
    display: block;
    font-size: 12px;
    color: var(--dim);
    margin-bottom: 5px;
  }
  input, select {
    width: 100%;
    background: #0d1117;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 9px 11px;
    border-radius: 6px;
    font-size: 13px;
    font-family: var(--mono);
  }
  input:focus, select:focus {
    outline: none;
    border-color: var(--accent);
  }

  button {
    background: #21262d;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 9px 16px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    font-weight: 500;
    transition: background .15s;
  }
  button:hover:not(:disabled) { background: #30363d; }
  button:disabled { opacity: 0.4; cursor: not-allowed; }
  button.primary { background: #238636; border-color: #2ea043; }
  button.primary:hover:not(:disabled) { background: #2ea043; }
  button.call { background: #16563a; border-color: #3fb950; color: #7ee2a8; }
  button.call:hover:not(:disabled) { background: #1b6b47; }
  button.put { background: #5a1a1a; border-color: #f85149; color: #ff9b96; }
  button.put:hover:not(:disabled) { background: #6e2020; }
  button.danger { background: #3a1216; border-color: #6e2029; color: #ff7b72; }
  button.danger:hover:not(:disabled) { background: #4a181d; }

  .btn-row { display: flex; gap: 8px; flex-wrap: wrap; }

  .account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-top: 4px;
  }
  .stat {
    background: #0d1117;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 12px;
  }
  .stat .label { font-size: 11px; color: var(--dim); margin-bottom: 3px; }
  .stat .value { font-size: 15px; font-weight: 600; font-family: var(--mono); }

  #log {
    background: #0a0e13;
    border: 1px solid var(--border);
    border-radius: 8px;
    height: 340px;
    overflow-y: auto;
    padding: 10px;
    font-family: var(--mono);
    font-size: 12px;
    line-height: 1.6;
  }
  .log-line { display: flex; gap: 8px; padding: 2px 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
  .log-time { color: #565f6c; flex-shrink: 0; }
  .log-tag { flex-shrink: 0; font-weight: 700; width: 36px; }
  .log-tag.send { color: var(--accent); }
  .log-tag.recv { color: var(--green); }
  .log-tag.sys { color: var(--amber); }
  .log-tag.err { color: var(--red); }
  .log-msg { color: var(--text); word-break: break-all; white-space: pre-wrap; }

  .hint { font-size: 12px; color: var(--dim); margin-top: 8px; line-height: 1.5; }
  .hint a { color: var(--accent); }
  .warn {
    background: #2b1d0a; border: 1px solid #6e4b1a; color: #f0c674;
    border-radius: 8px; padding: 10px 12px; font-size: 12px; margin-bottom: 16px; line-height: 1.5;
  }
  code { background: #0d1117; padding: 1px 5px; border-radius: 4px; font-size: 11px; }
</style>
</head>
<body>
<div class="wrap">

  <h1><span id="statusDot" class="status-dot"></span> Deriv API Tester — New OTP API</h1>
  <div class="subtitle">Flow ใหม่: App ID + Account ID + Token → ขอ OTP ผ่าน REST → ต่อ WebSocket ด้วย URL ที่ได้</div>

  <div class="warn">
    ⚠️ ใช้ token จาก <b>Demo account</b> ระหว่างทดสอบเท่านั้น จนกว่าจะมั่นใจว่า flow ทำงานถูกต้อง — ปุ่ม CALL/PUT ในเครื่องมือนี้ยัง<b>ไม่ยิง order จริง</b> เป็นแค่ขอราคา (proposal) เท่านั้น ต้องกด "Buy" อีกครั้งเพื่อยืนยันสั่งซื้อจริง
  </div>

  <!-- Step 0: Find Account ID -->
  <div class="panel">
    <h2>0. หา Account ID (ถ้ายังไม่รู้)</h2>
    <div class="row">
      <div class="field">
        <label for="lookupAppId">App ID</label>
        <input id="lookupAppId" type="text" placeholder="App ID จาก developers.deriv.com">
      </div>
      <div class="field">
        <label for="lookupToken">PAT Token</label>
        <input id="lookupToken" type="password" placeholder="Token ของคุณ">
      </div>
    </div>
    <div class="btn-row">
      <button id="listAccountsBtn">List My Accounts</button>
    </div>
    <div class="hint">
      เรียก <code>GET /trading/v1/options/accounts</code> เพื่อดูรายชื่อบัญชีทั้งหมด (account_id, balance, currency, account_type) — คัดลอก account_id ที่ต้องการไปใส่ในขั้นตอนที่ 1 ด้านล่าง
    </div>
  </div>

  <!-- Connection panel -->
  <div class="panel">
    <h2>1. Connect &amp; Authorize (OTP flow)</h2>
    <div class="row">
      <div class="field">
        <label for="appId">Trading App ID</label>
        <input id="appId" type="text" placeholder="เช่น 33NozJ2OzGNSgrRZjZzi0">
      </div>
      <div class="field">
        <label for="accountId">Account ID</label>
        <input id="accountId" type="text" placeholder="เช่น DOT92631882">
      </div>
      <div class="field">
        <label for="token">PAT Token</label>
        <input id="token" type="password" placeholder="วาง token ของคุณตรงนี้">
      </div>
    </div>
    <div class="btn-row">
      <button id="connectBtn" class="primary">Connect &amp; Authorize</button>
      <button id="disconnectBtn" disabled>Disconnect</button>
      <button id="clearLogBtn">Clear Log</button>
    </div>
    <div class="hint">
      ยังไม่มี App ID? สร้างได้ที่ <a href="https://developers.deriv.com/dashboard/" target="_blank" rel="noopener">developers.deriv.com/dashboard</a> → เลือก "Native apps"
    </div>
  </div>

  <!-- Account info panel -->
  <div class="panel" id="accountPanel" style="display:none;">
    <h2>Connection Status</h2>
    <div class="account-grid">
      <div class="stat"><div class="label">Account ID</div><div class="value" id="accLoginid">—</div></div>
      <div class="stat"><div class="label">WebSocket</div><div class="value" id="accWsStatus">—</div></div>
    </div>
  </div>

  <!-- Trade test panel -->
  <div class="panel">
    <h2>2. ทดสอบขอราคา (Proposal) — ยังไม่ใช่การสั่งซื้อจริง</h2>
    <div class="row">
      <div class="field">
        <label for="symbol">Symbol</label>
        <select id="symbol">
          <option value="R_10">Volatility 10 Index (R_10)</option>
          <option value="R_25">Volatility 25 Index (R_25)</option>
          <option value="R_50">Volatility 50 Index (R_50)</option>
          <option value="R_75">Volatility 75 Index (R_75)</option>
          <option value="R_100">Volatility 100 Index (R_100)</option>
        </select>
      </div>
      <div class="field">
        <label for="durationUnit">Duration Unit</label>
        <select id="durationUnit">
          <option value="t">Ticks</option>
          <option value="s">Seconds</option>
          <option value="m">Minutes</option>
        </select>
      </div>
      <div class="field">
        <label for="duration">Duration</label>
        <input id="duration" type="number" value="5" min="1">
      </div>
      <div class="field">
        <label for="stake">Stake Amount (USD)</label>
        <input id="stake" type="number" value="10" min="1">
      </div>
    </div>
    <div class="btn-row">
      <button id="proposalCallBtn" class="call" disabled>Get Proposal — CALL</button>
      <button id="proposalPutBtn" class="put" disabled>Get Proposal — PUT</button>
    </div>

    <div class="account-grid" id="proposalResult" style="display:none; margin-top:12px;">
      <div class="stat"><div class="label">Proposal ID</div><div class="value" id="propId">—</div></div>
      <div class="stat"><div class="label">Ask Price</div><div class="value" id="propPrice">—</div></div>
      <div class="stat"><div class="label">Payout</div><div class="value" id="propPayout">—</div></div>
    </div>
    <div class="btn-row" style="margin-top:10px;">
      <button id="buyBtn" class="primary" disabled>⚠️ Buy This Proposal (สั่งซื้อจริง)</button>
    </div>
  </div>

  <!-- Raw request panel -->
  <div class="panel">
    <h2>3. ส่ง Raw Request (สำหรับทดสอบ API call อื่นๆ)</h2>
    <div class="row">
      <div class="field">
        <input id="rawRequest" type="text" placeholder='เช่น {"ping": 1} หรือ {"balance": 1}' value='{"ping": 1}'>
      </div>
    </div>
    <div class="btn-row">
      <button id="sendRawBtn" disabled>Send</button>
    </div>
  </div>

  <!-- Log panel -->
  <div class="panel">
    <h2>Log</h2>
    <div id="log"></div>
  </div>

</div>

<script>
let ws = null;
let lastProposal = null; // { id, price }

const els = {
  lookupAppId: document.getElementById('lookupAppId'),
  lookupToken: document.getElementById('lookupToken'),
  listAccountsBtn: document.getElementById('listAccountsBtn'),
  appId: document.getElementById('appId'),
  accountId: document.getElementById('accountId'),
  token: document.getElementById('token'),
  connectBtn: document.getElementById('connectBtn'),
  disconnectBtn: document.getElementById('disconnectBtn'),
  clearLogBtn: document.getElementById('clearLogBtn'),
  statusDot: document.getElementById('statusDot'),
  accountPanel: document.getElementById('accountPanel'),
  accLoginid: document.getElementById('accLoginid'),
  accWsStatus: document.getElementById('accWsStatus'),
  symbol: document.getElementById('symbol'),
  durationUnit: document.getElementById('durationUnit'),
  duration: document.getElementById('duration'),
  stake: document.getElementById('stake'),
  proposalCallBtn: document.getElementById('proposalCallBtn'),
  proposalPutBtn: document.getElementById('proposalPutBtn'),
  proposalResult: document.getElementById('proposalResult'),
  propId: document.getElementById('propId'),
  propPrice: document.getElementById('propPrice'),
  propPayout: document.getElementById('propPayout'),
  buyBtn: document.getElementById('buyBtn'),
  rawRequest: document.getElementById('rawRequest'),
  sendRawBtn: document.getElementById('sendRawBtn'),
  log: document.getElementById('log'),
};

function log(tag, msg) {
  const time = new Date().toLocaleTimeString('th-TH', { hour12: false });
  const line = document.createElement('div');
  line.className = 'log-line';
  line.innerHTML = `<span class="log-time">${time}</span><span class="log-tag ${tag}">${tag.toUpperCase()}</span><span class="log-msg"></span>`;
  line.querySelector('.log-msg').textContent = msg;
  els.log.appendChild(line);
  els.log.scrollTop = els.log.scrollHeight;
}

function setStatus(state) {
  els.statusDot.className = 'status-dot' + (state ? ' ' + state : '');
}

function setConnectedUI(connected) {
  els.connectBtn.disabled = connected;
  els.disconnectBtn.disabled = !connected;
  els.proposalCallBtn.disabled = !connected;
  els.proposalPutBtn.disabled = !connected;
  els.sendRawBtn.disabled = !connected;
  els.appId.disabled = connected;
  els.accountId.disabled = connected;
  els.token.disabled = connected;
  if (!connected) {
    els.accountPanel.style.display = 'none';
    els.proposalResult.style.display = 'none';
    els.buyBtn.disabled = true;
    lastProposal = null;
  }
}

// --- Step 0: List accounts to discover Account ID ---
els.listAccountsBtn.addEventListener('click', async () => {
  const appId = els.lookupAppId.value.trim();
  const token = els.lookupToken.value.trim();
  if (!appId || !token) {
    log('err', 'ใส่ App ID และ Token ในส่วน "หา Account ID" ก่อน');
    return;
  }
  log('sys', 'กำลังเรียก GET /trading/v1/options/accounts ...');
  try {
    const res = await fetch('https://api.derivws.com/trading/v1/options/accounts', {
      headers: {
        'Deriv-App-ID': appId,
        'Authorization': 'Bearer ' + token,
      },
    });
    const json = await res.json();
    log(res.ok ? 'recv' : 'err', JSON.stringify(json, null, 2));
    if (res.ok && json.data) {
      const accounts = Array.isArray(json.data) ? json.data : [json.data];
      accounts.forEach(acc => {
        log('sys', `พบบัญชี: ${acc.account_id} — ${acc.account_type} — ${acc.balance} ${acc.currency}`);
      });
      if (accounts.length > 0) {
        els.accountId.value = accounts[0].account_id;
        els.appId.value = appId;
        els.token.value = token;
        log('sys', `กรอก Account ID (${accounts[0].account_id}) ให้อัตโนมัติในขั้นตอนที่ 1 แล้ว`);
      }
    }
  } catch (e) {
    log('err', 'เรียก API ไม่สำเร็จ: ' + e.message + ' (อาจติด CORS หรือ network — เช็ค console ของเบราว์เซอร์ด้วย)');
  }
});

// --- Step 1: Connect via OTP flow ---
els.connectBtn.addEventListener('click', async () => {
  const appId = els.appId.value.trim();
  const accountId = els.accountId.value.trim();
  const token = els.token.value.trim();

  if (!appId || !accountId || !token) {
    log('err', 'กรุณาใส่ App ID, Account ID และ Token ให้ครบ');
    return;
  }

  setStatus('connecting');
  log('sys', `กำลังขอ OTP สำหรับบัญชี ${accountId} ...`);

  try {
    const otpRes = await fetch(`https://api.derivws.com/trading/v1/options/accounts/${accountId}/otp`, {
      method: 'POST',
      headers: {
        'Deriv-App-ID': appId,
        'Authorization': 'Bearer ' + token,
      },
    });
    const otpJson = await otpRes.json();
    log(otpRes.ok ? 'recv' : 'err', JSON.stringify(otpJson, null, 2));

    if (!otpRes.ok || !otpJson.data || !otpJson.data.url) {
      log('err', 'ขอ OTP ไม่สำเร็จ — เช็ค App ID / Account ID / Token / scope (ต้องมี "trade")');
      setStatus('');
      return;
    }

    const wsUrl = otpJson.data.url;
    log('sys', `ได้ WebSocket URL แล้ว กำลังเชื่อมต่อ ...`);

    ws = new WebSocket(wsUrl);

    ws.onopen = () => {
      setStatus('connected');
      setConnectedUI(true);
      els.accountPanel.style.display = 'block';
      els.accLoginid.textContent = accountId;
      els.accWsStatus.textContent = 'Connected';
      log('sys', `เชื่อมต่อสำเร็จ — Account ID: ${accountId} (ไม่ต้องส่ง authorize message อีก เพราะ OTP auth ให้แล้ว)`);
    };

    ws.onmessage = (event) => {
      const data = JSON.parse(event.data);
      log('recv', event.data);

      if (data.error || (data.errors && data.errors.length)) {
        const errMsg = data.error ? data.error.message : data.errors[0].message;
        log('err', errMsg);
        return;
      }

      switch (data.msg_type) {
        case 'proposal':
          lastProposal = { id: data.proposal.id, price: data.proposal.ask_price };
          els.proposalResult.style.display = 'grid';
          els.propId.textContent = data.proposal.id;
          els.propPrice.textContent = data.proposal.ask_price;
          els.propPayout.textContent = data.proposal.payout ?? '—';
          els.buyBtn.disabled = false;
          break;
        case 'buy':
          log('sys', `ซื้อสำเร็จ! Contract ID: ${data.buy.contract_id}`);
          break;
        case 'ping':
          log('sys', 'pong received');
          break;
      }
    };

    ws.onerror = () => {
      log('err', 'WebSocket error หลังเชื่อมต่อ — เช็ค network หรือ OTP หมดอายุ (OTP ใช้ได้ครั้งเดียว/ระยะสั้นมาก)');
      setStatus('');
    };

    ws.onclose = () => {
      log('sys', 'การเชื่อมต่อถูกปิด');
      setStatus('');
      setConnectedUI(false);
      ws = null;
    };

  } catch (e) {
    log('err', 'เกิดข้อผิดพลาด: ' + e.message + ' (อาจติด CORS — ถ้าเว็บของคุณเรียกได้ปกติ ให้เช็คว่ามี Access-Control-Allow-Origin header จาก server ไหม)');
    setStatus('');
  }
});

els.disconnectBtn.addEventListener('click', () => {
  if (ws) ws.close();
});

els.clearLogBtn.addEventListener('click', () => {
  els.log.innerHTML = '';
});

// --- Step 2: Proposal (price quote, not a real order yet) ---
function sendProposal(contractType) {
  if (!ws || ws.readyState !== WebSocket.OPEN) return;
  const req = {
    proposal: 1,
    subscribe: 1,
    amount: Number(els.stake.value),
    basis: 'stake',
    contract_type: contractType,
    currency: 'USD',
    duration: Number(els.duration.value),
    duration_unit: els.durationUnit.value,
    symbol: els.symbol.value,
  };
  ws.send(JSON.stringify(req));
  log('send', JSON.stringify(req));
}

els.proposalCallBtn.addEventListener('click', () => sendProposal('CALL'));
els.proposalPutBtn.addEventListener('click', () => sendProposal('PUT'));

els.buyBtn.addEventListener('click', () => {
  if (!ws || ws.readyState !== WebSocket.OPEN || !lastProposal) return;
  const confirmed = confirm(`ยืนยันซื้อสัญญาราคา ${lastProposal.price} USD จริงหรือไม่?\nการกดตกลงจะส่งคำสั่งซื้อจริงไปยัง Deriv ทันที`);
  if (!confirmed) return;
  const req = { buy: lastProposal.id, price: lastProposal.price };
  ws.send(JSON.stringify(req));
  log('send', JSON.stringify(req));
});

// --- Raw request ---
els.sendRawBtn.addEventListener('click', () => {
  if (!ws || ws.readyState !== WebSocket.OPEN) return;
  try {
    const parsed = JSON.parse(els.rawRequest.value);
    ws.send(JSON.stringify(parsed));
    log('send', JSON.stringify(parsed));
  } catch (e) {
    log('err', 'JSON ไม่ถูกต้อง: ' + e.message);
  }
});

log('sys', 'พร้อมใช้งาน — เริ่มจากขั้นตอนที่ 0 (หา Account ID) หรือข้ามไปขั้นตอนที่ 1 ถ้ารู้ Account ID แล้ว');
</script>
</body>
</html>