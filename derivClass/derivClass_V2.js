/**
 * ============================================================================
 * DerivAppVerNewAuth Class (derivClass.js)
 * ============================================================================
 * คลาสสำหรับควบคุม UI และติดต่อสื่อสารกับเซิร์ฟเวอร์ Deriv (ผ่าน REST และ WebSocket)
 * 
 * [ Features & Connection Modes ]
 * 1. Discover Accounts (REST) - ค้นหารายการบัญชีทั้งหมดภายใต้ Token และ App ID
 * 2. 2 Connection Modes (WebSocket) - รูปแบบการเชื่อมต่อ WebSocket แบ่งออกเป็น 2 โหมด:
 *    - New Public:
 *      * รายละเอียด: เชื่อมต่อดึงข้อมูลตลาดสาธารณะโดยไม่ต้องระบุ App ID (เชื่อมตรงเข้า wss://api.derivws.com/trading/v1/options/ws/public)
 *      * แนะนำสำหรับการใช้งาน: ขอข้อมูลคู่เงิน/สินทรัพย์ (Asset List), ราคา Tick สดเรียลไทม์, ประวัติแท่งเทียนย้อนหลัง (Candle History) และประวัติ Tick ดิบย้อนหลัง (Tick History)
 *      * ข้อจำกัด: ไม่สามารถทำรายการเกี่ยวกับ Order (เช่น ซื้อสัญญา/เทรด), ขอใบเสนอราคาสัญญา (Proposal) หรือดึงประวัติการเทรดส่วนตัวได้
 *    - Private Authenticated via OTP:
 *      * รายละเอียด: ส่ง POST ไปขอ OTP ผ่าน REST API แล้วเชื่อม WebSocket ส่วนตัวระดับบัญชีเทรด
 *      * แนะนำสำหรับการใช้งาน: จำเป็นต้องใช้เมื่อต้องการทำรายการเทรด สั่งซื้อสัญญา, ขอใบเสนอราคาสัญญา (Proposal), แสดงรายงานการติดตามคำสั่งซื้อ (Order Tracker) หรือแสดงประวัติการเทรด (Trade History)
 *      * ความต้องการ: ต้องการพารามิเตอร์ครบถ้วน ได้แก่ App ID, Account ID และ API Token (ที่เปิดสิทธิ์ขอบเขต "trade")
 * 3. Contract Proposals & Order Tracking (WebSocket) - ขอใบเสนอราคาสัญญา CALL/PUT พร้อมระบบติดตามคำสั่งซื้อเรียลไทม์หลังการสั่งซื้อสำเร็จ
 * 4. Contract Purchase (WebSocket) - ซื้อสัญญาเทรดและส่งคำสั่งซื้อตรงไปยังเซิร์ฟเวอร์
 * 5. Raw Request Sender (WebSocket) - ส่งชุดคำสั่งแบบดิบในรูปแบบ JSON เพื่อทดสอบ API
 * 6. Trade History (WebSocket) - ดึงและกรองประวัติธุรกรรมกำไร/ขาดทุนย้อนหลังแสดงผลเป็นตารางพร้อมอัปเดตแบบไดนามิก
 * 
 * [ Functions & Methods ]
 * - constructor(els)        : รับอ็อบเจกต์เก็บ Element UI references ทั้งหมด
 * - init()                  : ลงทะเบียน Event Listeners สำหรับปุ่มควบคุมทั้งหมดในหน้าเว็บ
 * - log(tag, msg)           : เขียนรายงานเหตุการณ์พร้อมเวลาและประเภทลงใน Log Panel
 * - setStatus(state)        : เปลี่ยนสีจุดไฟแจ้งเตือนสถานะการเชื่อมต่อ WebSocket
 * - setConnectedUI(status)  : ปรับแต่งการปิด/เปิดใช้งานอินพุตและปุ่มตามสถานะเชื่อมต่อ
 * - listAccounts() (async)  : ยิง GET API ไปยัง Deriv เพื่อดึงและแสดงผลบัญชีทั้งหมด
 * - connect() (async)       : ฟังก์ชันเชื่อมต่อหลัก (ไม่ต้องใช้ parameter - จะอ่านและประเมินโหมดอัตโนมัติจากค่าใน UI)
 * - connectNewPublic() (async) : เชื่อมต่อแบบ New Public WebSocket (ไม่ต้องใช้ parameter - ต่อเข้า wss://api.derivws.com/trading/v1/options/ws/public)
 * - connectPrivateOTP(appId, accountId, token) (async) : เชื่อมต่อแบบระบุตัวตน (ต้องการ parameter: appId [string], accountId [string], token [string])
 * - establishConnection(wsUrl, accountName, isAuthenticated) : ฟังก์ชันย่อยสำหรับเปิด WebSocket และตั้ง Event Listeners (ต้องการ parameter: wsUrl [string], accountName [string], isAuthenticated [boolean])
 * - fetchActiveSymbols()    : ส่งคำสั่งขอรายการสัญลักษณ์สินทรัพย์ (Active Symbols) จากเซิร์ฟเวอร์ (ไม่ต้องใช้ parameter)
 * - populateSymbols(list)   : นำรายการ Symbol จากเซิร์ฟเวอร์มาวาดลงบน Dropdown ของ UI (ต้องการ parameter: list [array])
 * - subscribeTicks(symbol, subscribe) : ส่งคำร้องสตรีมราคาวิ่งเรียลไทม์ หรือขอยกเลิกสตรีม (ต้องการ parameter: symbol [string], subscribe [number 1/0])
 * - fetchCandleHistory(symbol, granularity, start, end, count, subscribe) : ขอข้อมูลกราฟแท่งเทียนย้อนหลัง
 * - handleTickHistoryData(history, echoReq) : รับผลลัพธ์ประวัติ Tick (msg_type: "history") มาวาดเป็นตารางราคา+เวลาใน UI (ต้องการ parameter: history [object], echoReq [object])
 * - trackOrder(contractId)  : ส่งคำขอสตรีมติดตามความเคลื่อนไหวและกำไร/ขาดทุนของสัญญาเทรดเรียลไทม์ (ต้องการ parameter: contractId [number])
 * - handleOpenContract(poc) : รับข้อมูลสถานะสัญญาล่าสุดและจัดรูปแบบอัปเดตลงบนการ์ด Order Tracker ใน UI (ต้องการ parameter: poc [object])
 * - disconnect()            : ทำการปิดการเชื่อมต่อ WebSocket ที่ใช้งานอยู่
 * - sendProposal(type)      : ส่งคำขอราคาของสัญญาประเภท CALL หรือ PUT ตามค่าใน UI
 * - buyContract()           : ส่งคำสั่งซื้อสัญญา (Buy) ล่าสุดที่เสนอราคามา
 * - sendRaw()               : อ่านข้อความในช่อง Raw Request และส่งไปบน WebSocket
 * - fetchHistory()          : ส่งคำขอดึงข้อมูลประวัติการเทรด (profit_table) แบบย้อนหลัง
 * - renderProfitTable(data) : แปลงวันเวลา คำนวณกำไร/ขาดทุน และสร้างแถวตารางข้อมูลเทรด
 * ============================================================================
 */

class DerivAppVerNewAuth {
  constructor(els) {
    this.els = els || {};
    this.ws = null;
    this.lastProposal = null;
    if (this.els.listAccountsBtn || this.els.connectBtn) {
      this.init();
    }
  }

  log(tag, msg) {
    const time = new Date().toLocaleTimeString('th-TH', { hour12: false });
    if (this.els && this.els.log) {
      const line = document.createElement('div');
      line.className = 'log-line';
      line.innerHTML = `<span class="log-time">${time}</span><span class="log-tag ${tag}">${tag.toUpperCase()}</span><span class="log-msg"></span>`;
      line.querySelector('.log-msg').textContent = msg;
      this.els.log.appendChild(line);
      this.els.log.scrollTop = this.els.log.scrollHeight;
    } else {
      console.log(`[Deriv ${tag.toUpperCase()}]`, msg);
    }
  }

  setStatus(state) {
    if (this.els && this.els.statusDot) {
      this.els.statusDot.className = 'status-dot' + (state ? ' ' + state : '');
    }
  }

  setConnectedUI(connected) {
    if (this.els.connectBtn) this.els.connectBtn.disabled = connected;
    if (this.els.disconnectBtn) this.els.disconnectBtn.disabled = !connected;
    if (this.els.proposalCallBtn) this.els.proposalCallBtn.disabled = !connected;
    if (this.els.proposalPutBtn) this.els.proposalPutBtn.disabled = !connected;
    if (this.els.sendRawBtn) this.els.sendRawBtn.disabled = !connected;
    if (this.els.fetchHistoryBtn) this.els.fetchHistoryBtn.disabled = !connected;
    if (this.els.subscribeTicksBtn) this.els.subscribeTicksBtn.disabled = !connected;
    if (this.els.unsubscribeTicksBtn) this.els.unsubscribeTicksBtn.disabled = !connected;
    if (this.els.fetchCandlesBtn) this.els.fetchCandlesBtn.disabled = !connected;
    if (this.els.fetchTickHistoryBtn) this.els.fetchTickHistoryBtn.disabled = !connected;
    if (this.els.appId) this.els.appId.disabled = connected;
    if (this.els.accountId) this.els.accountId.disabled = connected;
    if (this.els.token) this.els.token.disabled = connected;
    if (!connected) {
      if (this.els.accountPanel) this.els.accountPanel.style.display = 'none';
      if (this.els.proposalResult) this.els.proposalResult.style.display = 'none';
      if (this.els.buyBtn) this.els.buyBtn.disabled = true;
      this.lastProposal = null;
      if (this.els.historyTableContainer) this.els.historyTableContainer.style.display = 'none';
      if (this.els.historyTableBody) this.els.historyTableBody.innerHTML = '';
      if (this.els.candlesTableContainer) this.els.candlesTableContainer.style.display = 'none';
      if (this.els.candlesTableBody) this.els.candlesTableBody.innerHTML = '';
      if (this.els.tickHistoryTableContainer) this.els.tickHistoryTableContainer.style.display = 'none';
      if (this.els.tickHistoryTableBody) this.els.tickHistoryTableBody.innerHTML = '';
      if (this.els.tickQuote) {
        this.els.tickQuote.textContent = '—';
        this.els.tickSymbol.textContent = '—';
        this.els.tickEpoch.textContent = '—';
        this.els.tickPip.textContent = '—';
      }
      if (this.els.orderTrackerCard) this.els.orderTrackerCard.style.display = 'none';
      if (this.els.trackContractId) {
        this.els.trackContractId.textContent = '—';
        this.els.trackStatus.textContent = '—';
        this.els.trackStatus.style.color = 'inherit';
        this.els.trackSymbol.textContent = '—';
        this.els.trackType.textContent = '—';
        this.els.trackBuyPrice.textContent = '—';
        this.els.trackPayout.textContent = '—';
        this.els.trackEntrySpot.textContent = '—';
        this.els.trackCurrentSpot.textContent = '—';
        this.els.trackProfit.textContent = '—';
        this.els.trackProfit.style.color = 'inherit';
      }
    }
  }

  init() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;
    if (this.els.historyDateFrom) this.els.historyDateFrom.value = `${todayStr}T00:05`;
    if (this.els.historyDateTo) this.els.historyDateTo.value = `${todayStr}T23:59`;

    if (this.els.listAccountsBtn) this.els.listAccountsBtn.addEventListener('click', () => this.listAccounts());

    if (this.els.connectBtn) this.els.connectBtn.addEventListener('click', () => this.connect());
    if (this.els.disconnectBtn) this.els.disconnectBtn.addEventListener('click', () => this.disconnect());
    if (this.els.clearLogBtn) this.els.clearLogBtn.addEventListener('click', () => { if (this.els.log) this.els.log.innerHTML = ''; });
    if (this.els.refreshSymbols) {
      this.els.refreshSymbols.addEventListener('click', () => this.fetchActiveSymbols());
    }

    if (this.els.fetchTickHistoryBtn) {
      this.els.fetchTickHistoryBtn.addEventListener('click', () => {
        const symbol = this.els.tickHistorySymbolSelect
          ? this.els.tickHistorySymbolSelect.value
          : (this.els.candleSymbolSelect ? this.els.candleSymbolSelect.value : (this.els.symbol ? this.els.symbol.value : null));
        const start = this.els.tickHistoryDateFrom ? this.els.tickHistoryDateFrom.value : null;
        const end = this.els.tickHistoryDateTo ? this.els.tickHistoryDateTo.value : null;
        const count = this.els.tickHistoryCount ? Number(this.els.tickHistoryCount.value) : 100;
        this.fetchTickHistory(symbol, start || null, end || null, count || 100);
      });
    }

    if (this.els.proposalCallBtn) this.els.proposalCallBtn.addEventListener('click', () => this.sendProposal('CALL'));
    if (this.els.proposalPutBtn) this.els.proposalPutBtn.addEventListener('click', () => this.sendProposal('PUT'));
    if (this.els.buyBtn) this.els.buyBtn.addEventListener('click', () => this.buyContract());

    if (this.els.sendRawBtn) this.els.sendRawBtn.addEventListener('click', () => this.sendRaw());

    if (this.els.fetchHistoryBtn) {
      this.els.fetchHistoryBtn.addEventListener('click', () => {
        const dateFrom = this.els.historyDateFrom ? this.els.historyDateFrom.value : null;
        const dateTo = this.els.historyDateTo ? this.els.historyDateTo.value : null;
        this.fetchHistory(dateFrom || null, dateTo || null);
      });
    }

    this.log('sys', 'พร้อมใช้งาน — เริ่มจากขั้นตอนที่ 0 (หา Account ID) หรือข้ามไปขั้นตอนที่ 1 ถ้ารู้ Account ID แล้ว');
  }

  async listAccounts() {
    const appId = (this.els.lookupAppId && this.els.lookupAppId.value) ? this.els.lookupAppId.value.trim() : '';
    const token = (this.els.lookupToken && this.els.lookupToken.value) ? this.els.lookupToken.value.trim() : '';
    if (!appId || !token) {
      this.log('err', 'ใส่ App ID และ Token ในส่วน "หา Account ID" ก่อน');
      return;
    }
    this.log('sys', 'กำลังเรียก GET /trading/v1/options/accounts ...');
    try {
      const res = await fetch('https://api.derivws.com/trading/v1/options/accounts', {
        headers: {
          'Deriv-App-ID': appId,
          'Authorization': 'Bearer ' + token,
        },
      });
      const json = await res.json();
      this.log(res.ok ? 'recv' : 'err', JSON.stringify(json, null, 2));
      if (res.ok && json.data) {
        const accounts = Array.isArray(json.data) ? json.data : [json.data];
        accounts.forEach(acc => {
          this.log('sys', `พบบัญชี: ${acc.account_id} — ${acc.account_type} — ${acc.balance} ${acc.currency}`);
        });
        if (accounts.length > 0) {
          if (this.els.accountId) this.els.accountId.value = accounts[0].account_id;
          if (this.els.appId) this.els.appId.value = appId;
          if (this.els.token) this.els.token.value = token;
          this.log('sys', `กรอก Account ID (${accounts[0].account_id}) ให้อัตโนมัติในขั้นตอนที่ 1 แล้ว`);
        }
      }
    } catch (e) {
      this.log('err', 'เรียก API ไม่สำเร็จ: ' + e.message + ' (อาจติด CORS หรือ network — เช็ค console ของเบราว์เซอร์ด้วย)');
    }
  }

  async connect() {
    let connType = 'otp';
    if (this.els.connectionType) {
      connType = this.els.connectionType.value;
    } else if (this.els.endpointSelect) {
      connType = this.els.endpointSelect.value;
    } else {
      const appId = (this.els.appId && this.els.appId.value) ? this.els.appId.value.trim() : '';
      const accountId = (this.els.accountId && this.els.accountId.value) ? this.els.accountId.value.trim() : '';
      const token = (this.els.token && this.els.token.value) ? this.els.token.value.trim() : '';
      if (!token || !accountId) {
        connType = 'new';
      }
    }

    if (connType === 'new') {
      await this.connectNewPublic();
    } else {
      const appId = (this.els.appId && this.els.appId.value) ? this.els.appId.value.trim() : '';
      const accountId = (this.els.accountId && this.els.accountId.value) ? this.els.accountId.value.trim() : '';
      const token = (this.els.token && this.els.token.value) ? this.els.token.value.trim() : '';
      await this.connectPrivateOTP(appId, accountId, token);
    }
  }

  async connectNewPublic() {
    this.setStatus('connecting');
    this.log('sys', 'กำลังเชื่อมต่อแบบ New Public WebSocket...');
    const wsUrl = 'wss://api.derivws.com/trading/v1/options/ws/public';
    this.establishConnection(wsUrl, 'Public (New)', false);
  }

  async connectPrivateOTP(appId, accountId, token) {
    if (!appId || !accountId || !token) {
      this.log('err', 'กรุณาใส่ App ID, Account ID และ Token ให้ครบสำหรับการต่อแบบ OTP');
      this.setStatus('');
      return;
    }

    this.setStatus('connecting');
    this.log('sys', `กำลังขอ OTP สำหรับบัญชี ${accountId} ...`);

    try {
      const otpRes = await fetch(`https://api.derivws.com/trading/v1/options/accounts/${accountId}/otp`, {
        method: 'POST',
        headers: {
          'Deriv-App-ID': appId,
          'Authorization': 'Bearer ' + token,
        },
      });
      const otpJson = await otpRes.json();
      this.log(otpRes.ok ? 'recv' : 'err', JSON.stringify(otpJson, null, 2));

      if (!otpRes.ok || !otpJson.data || !otpJson.data.url) {
        this.log('err', 'ขอ OTP ไม่สำเร็จ — เช็ค App ID / Account ID / Token / scope (ต้องมี "trade")');
        this.setStatus('');
        if (typeof this.onError === 'function') this.onError(otpJson.error || 'ขอ OTP ไม่สำเร็จ');
        return;
      }

      const wsUrl = otpJson.data.url;
      this.log('sys', `ได้ WebSocket URL จาก OTP แล้ว กำลังเชื่อมต่อ...`);
      this.establishConnection(wsUrl, accountId, true);

    } catch (e) {
      this.log('err', 'เกิดข้อผิดพลาดในการขอ OTP: ' + e.message);
      this.setStatus('');
      if (typeof this.onError === 'function') this.onError(e);
    }
  }

  establishConnection(wsUrl, accountName, isAuthenticated = false) {
    if (this.ws) {
      try {
        this.ws.onopen = null;
        this.ws.onmessage = null;
        this.ws.onerror = null;
        this.ws.onclose = null;
        if (this.ws.readyState === WebSocket.OPEN) {
          this.ws.close();
        }
      } catch(e) {}
    }
    this.ws = new WebSocket(wsUrl);

    this.ws.onopen = () => {
      this.setStatus('connected');
      this.setConnectedUI(true);
      if (this.els.accountPanel) this.els.accountPanel.style.display = 'block';
      if (this.els.accLoginid) this.els.accLoginid.textContent = accountName;
      if (this.els.accWsStatus) this.els.accWsStatus.textContent = 'Connected';
      if (isAuthenticated) {
        this.log('sys', `เชื่อมต่อสำเร็จแบบระบุตัวตน (Authenticated) — Account ID: ${accountName}`);
      } else {
        this.log('sys', `เชื่อมต่อสำเร็จแบบสาธารณะ (Public) — ${accountName}`);
      }
      this.fetchActiveSymbols();
      if (typeof this.onOpen === 'function') this.onOpen();
    };

    this.ws.onmessage = (event) => {
      let data;
      try { data = JSON.parse(event.data); } catch(e) { return; }
      this.log('recv', event.data);

      if (data.error || (data.errors && data.errors.length)) {
        const errMsg = data.error ? data.error.message : data.errors[0].message;
        this.log('err', errMsg);
        if (typeof this.onError === 'function') this.onError(data.error || data.errors);
        return;
      }

      switch (data.msg_type) {
        case 'proposal':
          this.lastProposal = { id: data.proposal.id, price: data.proposal.ask_price };
          if (this.els.proposalResult) this.els.proposalResult.style.display = 'grid';
          if (this.els.propId) this.els.propId.textContent = data.proposal.id;
          if (this.els.propPrice) this.els.propPrice.textContent = data.proposal.ask_price;
          if (this.els.propPayout) this.els.propPayout.textContent = data.proposal.payout ?? '—';
          if (this.els.buyBtn) this.els.buyBtn.disabled = false;
          if (typeof this.onProposal === 'function') this.onProposal(data.proposal);
          break;
        case 'buy':
          this.log('sys', `ซื้อสำเร็จ! Contract ID: ${data.buy.contract_id}`);
          this.trackOrder(data.buy.contract_id);
          if (typeof this.onBuy === 'function') this.onBuy(data.buy);
          break;
        case 'proposal_open_contract':
          if (data.proposal_open_contract && typeof this.handleOpenContract === 'function') {
            this.handleOpenContract(data.proposal_open_contract);
          }
          if (typeof this.onOpenContract === 'function') this.onOpenContract(data.proposal_open_contract);
          break;
        case 'portfolio':
          if (data.portfolio && Array.isArray(data.portfolio.contracts)) {
            data.portfolio.contracts.forEach(c => {
              if (c.contract_id) this.trackOrder(c.contract_id);
            });
          }
          if (typeof this.onPortfolio === 'function') this.onPortfolio(data.portfolio);
          break;
        case 'profit_table':
          this.renderProfitTable(data.profit_table);
          if (typeof this.onProfitTable === 'function') this.onProfitTable(data.profit_table);
          break;
        case 'active_symbols':
          if (Array.isArray(data.active_symbols)) {
            this.populateSymbols(data.active_symbols);
            this.log('sys', `โหลดรายการ Symbol สำเร็จจำนวน ${data.active_symbols.length} รายการ`);
          }
          if (typeof this.onActiveSymbols === 'function') this.onActiveSymbols(data.active_symbols);
          break;
        case 'tick':
          if (data.tick) {
            this.handleTickData(data.tick);
          }
          if (typeof this.onTick === 'function') this.onTick(data.tick);
          break;
        case 'candles':
          if (Array.isArray(data.candles)) {
            this.handleCandlesData(data.candles);
          }
          if (typeof this.onCandles === 'function') this.onCandles(data);
          break;
        case 'ohlc':
          if (data.ohlc) {
            this.handleOhlcData(data.ohlc);
          }
          if (typeof this.onOhlc === 'function') this.onOhlc(data.ohlc);
          break;
        case 'history':
          if (data.history) {
            this.handleTickHistoryData(data.history, data.echo_req);
          }
          if (typeof this.onTickHistory === 'function') this.onTickHistory(data);
          break;
        case 'ping':
          this.log('sys', 'pong received');
          break;
      }
      if (typeof this.onMessage === 'function') this.onMessage(data);
    };

    this.ws.onerror = (err) => {
      this.log('err', 'WebSocket error หลังเชื่อมต่อ — กรุณาตรวจสอบ Network หรือ Token/OTP (ถ้าใช้ OTP มันอาจหมดอายุเร็วมาก)');
      this.setStatus('');
      if (typeof this.onError === 'function') this.onError(err);
    };

    this.ws.onclose = () => {
      this.log('sys', 'การเชื่อมต่อถูกปิด');
      this.setStatus('');
      this.setConnectedUI(false);
      this.ws = null;
      if (typeof this.onClose === 'function') this.onClose();
    };
  }

  fetchActiveSymbols() {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
      this.log('err', 'กรุณาเชื่อมต่อก่อนโหลดรายการ symbol');
      return;
    }
    const req = { active_symbols: 'brief' };
    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
  }

  populateSymbols(list) {
    const selectElements = [
      this.els.symbol,
      this.els.symbolSelect,
      this.els.tickSymbolSelect,
      this.els.candleSymbolSelect
    ].filter(el => el !== undefined && el !== null);

    if (selectElements.length === 0) return;

    selectElements.forEach(selectEl => {
      const currentVal = selectEl.value;
      selectEl.innerHTML = '';

      list.forEach(item => {
        const symbolCode = item.underlying_symbol || item.symbol;
        const opt = document.createElement('option');
        opt.value = symbolCode;
        opt.textContent = item.display_name ? `${item.display_name}` : symbolCode;
        selectEl.appendChild(opt);
      });

      const stillValid = Array.from(selectEl.options).some(o => o.value === currentVal);
      if (stillValid) {
        selectEl.value = currentVal;
      } else if (selectEl.options.length > 0) {
        selectEl.value = selectEl.options[0].value;
      }
    });
  }

  forgetAll(style = 'candles') {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
    const req = { forget_all: style };
    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
  }

  subscribeTicks(symbol, subscribe = 1) {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
      this.log('err', 'กรุณาเชื่อมต่อก่อนสมัครรับข้อมูลราคา Ticks');
      return;
    }
    if (subscribe === 0) {
      const req = { forget_all: 'ticks' };
      this.ws.send(JSON.stringify(req));
      this.log('send', JSON.stringify(req));
      this.log('sys', 'ส่งคำสั่งขอยกเลิกสตรีมราคาทั้งหมด');
    } else {
      this.ws.send(JSON.stringify({ forget_all: 'ticks' }));
      const req = { ticks: symbol, subscribe: 1 };
      this.ws.send(JSON.stringify(req));
      this.log('send', JSON.stringify(req));
    }
  }

  fetchCandleHistory(symbol, granularity = 60, start = null, end = null, count = 100, subscribe = 0) {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
      this.log('err', 'กรุณาเชื่อมต่อก่อนดึงประวัติราคาแท่งเทียน');
      return;
    }
    const req = {
      ticks_history: symbol,
      adjust_start_time: 1,
      style: 'candles',
      granularity: Number(granularity)
    };

    if (start) {
      req.start = typeof start === 'number' ? start : Math.floor(new Date(start).getTime() / 1000);
    }

    if (end) {
      req.end = (end === 'latest' || !end) ? 'latest' : (typeof end === 'number' ? end : Math.floor(new Date(end).getTime() / 1000));
    } else {
      req.end = 'latest';
    }

    if (count && !start) {
      req.count = Number(count);
    } else if (!start) {
      req.count = 100;
    }

    if (subscribe) {
      req.subscribe = 1;
    }

    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
  }

  fetchTickHistory(symbol, start = null, end = null, count = 100, subscribe = 0) {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
      this.log('err', 'กรุณาเชื่อมต่อก่อนดึงประวัติข้อมูล Tick');
      return;
    }
    if (!symbol) {
      this.log('err', 'กรุณาระบุ Symbol ก่อนขอข้อมูล Tick History');
      return;
    }

    const req = {
      ticks_history: symbol,
      adjust_start_time: 1,
      style: 'ticks'
    };

    if (start) {
      req.start = typeof start === 'number' ? start : Math.floor(new Date(start).getTime() / 1000);
    }

    if (end) {
      req.end = (end === 'latest' || !end) ? 'latest' : (typeof end === 'number' ? end : Math.floor(new Date(end).getTime() / 1000));
    } else {
      req.end = 'latest';
    }

    if (count && !start) {
      req.count = Number(count);
    } else if (!start) {
      req.count = 100;
    }

    if (subscribe) {
      req.subscribe = 1;
    }

    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
  }

  handleTickData(tick) {
    if (this.els.tickSymbol) this.els.tickSymbol.textContent = tick.symbol;
    if (this.els.tickQuote) this.els.tickQuote.textContent = tick.quote;
    if (this.els.tickEpoch) this.els.tickEpoch.textContent = tick.epoch;
    if (this.els.tickPip) this.els.tickPip.textContent = tick.pip_size !== undefined ? tick.pip_size : '—';
    
    this.log('sys', `[Tick Recv] ${tick.symbol}: ${tick.quote} (epoch: ${tick.epoch})`);
  }

  handleOhlcData(ohlc) {
    this.log('sys', `[OHLC Recv] ${ohlc.symbol || ''}: O:${ohlc.open} H:${ohlc.high} L:${ohlc.low} C:${ohlc.close}`);
  }

  handleCandlesData(candles) {
    this.log('sys', `ได้รับประวัติแท่งเทียนจำนวน ${candles.length} แท่ง`);
    if (this.els.candlesTableBody) {
      this.els.candlesTableBody.innerHTML = '';
      candles.forEach(c => {
        const tr = document.createElement('tr');
        const timeStr = new Date(c.epoch * 1000).toLocaleString('th-TH', { hour12: false });
        tr.innerHTML = `
          <td>${timeStr}</td>
          <td>$${Number(c.open).toFixed(5)}</td>
          <td>$${Number(c.high).toFixed(5)}</td>
          <td>$${Number(c.low).toFixed(5)}</td>
          <td>$${Number(c.close).toFixed(5)}</td>
        `;
        this.els.candlesTableBody.appendChild(tr);
      });
      if (this.els.candlesTableContainer) {
        this.els.candlesTableContainer.style.display = 'block';
      }
    }
  }

  handleTickHistoryData(history, echoReq) {
    const prices = Array.isArray(history.prices) ? history.prices : [];
    const times = Array.isArray(history.times) ? history.times : [];
    const symbol = (echoReq && echoReq.ticks_history) ? echoReq.ticks_history : '—';

    if (prices.length === 0) {
      this.log('sys', `ไม่พบข้อมูล Tick History ของ ${symbol} ในช่วงเวลาที่ระบุ`);
      if (this.els.tickHistoryTableContainer) this.els.tickHistoryTableContainer.style.display = 'none';
      return;
    }

    this.log('sys', `ได้รับประวัติ Tick ของ ${symbol} ย้อนหลังจำนวน ${prices.length} รายการ`);

    if (this.els.tickHistoryTableBody) {
      this.els.tickHistoryTableBody.innerHTML = '';
      const frag = document.createDocumentFragment();

      for (let i = 0; i < prices.length; i++) {
        const tr = document.createElement('tr');
        const epoch = times[i];
        const timeStr = epoch ? new Date(epoch * 1000).toLocaleString('th-TH', { hour12: false }) : '—';
        tr.innerHTML = `
          <td>${i + 1}</td>
          <td>${timeStr}</td>
          <td>${epoch ?? '—'}</td>
          <td>$${Number(prices[i]).toFixed(5)}</td>
        `;
        frag.appendChild(tr);
      }

      this.els.tickHistoryTableBody.appendChild(frag);

      if (this.els.tickHistoryTableContainer) {
        this.els.tickHistoryTableContainer.style.display = 'block';
      }
    }
  }

  fetchPortfolio() {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
    const req = { portfolio: 1 };
    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
    this.log('sys', 'ส่งคำขอรายการสัญญาทั้งหมดที่กำลังเปิดเทรด (portfolio)...');
  }

  trackOrder(contractId) {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
    const req = {
      proposal_open_contract: 1,
      contract_id: Number(contractId),
      subscribe: 1
    };
    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
    this.log('sys', `เริ่มติดตามสถานะสัญญา ID: ${contractId} แบบเรียลไทม์...`);
  }

  handleOpenContract(poc) {
    if (this.els.orderTrackerCard) {
      this.els.orderTrackerCard.style.display = 'block';
    }

    if (this.els.trackContractId) this.els.trackContractId.textContent = poc.contract_id;

    if (this.els.trackStatus) {
      let statusText = poc.status.toUpperCase();
      if (poc.status === 'won') {
        this.els.trackStatus.textContent = 'ชนะ (WON) 🎉';
        this.els.trackStatus.style.color = 'var(--up)';
      } else if (poc.status === 'lost') {
        this.els.trackStatus.textContent = 'แพ้ (LOST) ❌';
        this.els.trackStatus.style.color = 'var(--down)';
      } else if (poc.status === 'open') {
        this.els.trackStatus.textContent = 'กำลังวิ่ง (OPEN) ⏳';
        this.els.trackStatus.style.color = 'var(--amber)';
      } else {
        this.els.trackStatus.textContent = statusText;
        this.els.trackStatus.style.color = 'var(--text-hi)';
      }
    }

    if (this.els.trackSymbol) this.els.trackSymbol.textContent = poc.underlying || poc.symbol || '—';
    if (this.els.trackType) this.els.trackType.textContent = poc.contract_type || '—';
    if (this.els.trackBuyPrice) this.els.trackBuyPrice.textContent = `$${Number(poc.buy_price || 0).toFixed(2)}`;
    if (this.els.trackPayout) this.els.trackPayout.textContent = `$${Number(poc.payout || 0).toFixed(2)}`;
    if (this.els.trackEntrySpot) this.els.trackEntrySpot.textContent = poc.entry_spot ? `$${Number(poc.entry_spot).toFixed(5)}` : '—';
    if (this.els.trackCurrentSpot) this.els.trackCurrentSpot.textContent = poc.current_spot ? `$${Number(poc.current_spot).toFixed(5)}` : '—';

    if (this.els.trackProfit) {
      const profit = Number(poc.profit || 0);
      const profitStr = (profit >= 0 ? '+' : '') + profit.toFixed(2) + ' USD';
      this.els.trackProfit.textContent = profitStr;

      if (profit > 0) {
        this.els.trackProfit.style.color = 'var(--up)';
      } else if (profit < 0) {
        this.els.trackProfit.style.color = 'var(--down)';
      } else {
        this.els.trackProfit.style.color = 'var(--text-hi)';
      }
    }

    this.log('sys', `[Order Tracking] Contract ID: ${poc.contract_id} | Status: ${poc.status} | Profit: ${poc.profit} USD`);

    if (poc.status === 'won' || poc.status === 'lost') {
      this.log('sys', `สัญญา ID: ${poc.contract_id} จบลงแล้ว (${poc.status.toUpperCase()}) ดึงประวัติธุรกรรมใหม่...`);
      setTimeout(() => {
        this.fetchHistory();
      }, 1500);
    }
  }

  disconnect() {
    if (this.ws) this.ws.close();
  }

  sendProposal(contractType) {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
    const req = {
      proposal: 1,
      subscribe: 1,
      amount: Number(this.els.stake.value),
      basis: 'stake',
      contract_type: contractType,
      currency: 'USD',
      duration: Number(this.els.duration.value),
      duration_unit: this.els.durationUnit.value,
      underlying_symbol: this.els.symbol.value,
    };
    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
  }

  buyContract() {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN || !this.lastProposal) return;
    const confirmed = confirm(`ยืนยันซื้อสัญญาราคา ${this.lastProposal.price} USD จริงหรือไม่?\nการกดตกลงจะส่งคำสั่งซื้อจริงไปยัง Deriv ทันที`);
    if (!confirmed) return;
    const req = { buy: this.lastProposal.id, price: this.lastProposal.price };
    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
  }

  sendRaw() {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
    try {
      const parsed = JSON.parse(this.els.rawRequest.value);
      this.ws.send(JSON.stringify(parsed));
      this.log('send', JSON.stringify(parsed));
    } catch (e) {
      this.log('err', 'JSON ไม่ถูกต้อง: ' + e.message);
    }
  }

  fetchHistory(dateFrom = null, dateTo = null) {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
    const limit = Number(this.els.historyLimit.value) || 50;
    const req = {
      profit_table: 1,
      description: 1,
      limit: limit,
      sort: 'DESC'
    };
    if (dateFrom) {
      const d = new Date(dateFrom);
      req.date_from = Math.floor(d.getTime() / 1000);
    }
    if (dateTo) {
      const d = new Date(dateTo);
      req.date_to = Math.floor(d.getTime() / 1000);
    }
    
    this.ws.send(JSON.stringify(req));
    this.log('send', JSON.stringify(req));
  }

  renderProfitTable(profitTable) {
    if (!this.els.historyTableBody) return;
    this.els.historyTableBody.innerHTML = '';
    if (!profitTable || !profitTable.transactions || profitTable.transactions.length === 0) {
      this.log('sys', 'ไม่พบประวัติการเทรด');
      if (this.els.historyTableContainer) this.els.historyTableContainer.style.display = 'none';
      return;
    }

    profitTable.transactions.forEach(tx => {
      const tr = document.createElement('tr');

      let buyTimeStr = '—';
      if (tx.purchase_time) {
        const d = new Date(tx.purchase_time * 1000);
        const hh = String(d.getHours()).padStart(2, '0');
        const mm = String(d.getMinutes()).padStart(2, '0');
        const ss = String(d.getSeconds()).padStart(2, '0');
        buyTimeStr = `${hh}:${mm}:${ss}`;
      }

      let sellTimeStr = '—';
      if (tx.sell_time) {
        const d = new Date(tx.sell_time * 1000);
        const hh = String(d.getHours()).padStart(2, '0');
        const mm = String(d.getMinutes()).padStart(2, '0');
        const ss = String(d.getSeconds()).padStart(2, '0');
        sellTimeStr = `${hh}:${mm}:${ss}`;
      }

      let durationStr = '—';
      if (tx.sell_time && tx.purchase_time) {
        const diffSec = tx.sell_time - tx.purchase_time;
        if (diffSec >= 0) {
          const m = Math.floor(diffSec / 60);
          const s = diffSec % 60;
          durationStr = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }
      }

      const buyPrice = Number(tx.buy_price) || 0;
      const sellPrice = Number(tx.sell_price) || 0;
      const profitLoss = sellPrice - buyPrice;
      const profitTextClass = profitLoss > 0 ? 'profit-text win' : (profitLoss < 0 ? 'profit-text loss' : 'profit-text');
      const profitStr = (profitLoss >= 0 ? '+' : '') + profitLoss.toFixed(2);

      const symbol = tx.underlying_symbol || '—';
      const type = tx.contract_type || '—';
      const payout = tx.payout !== undefined && tx.payout !== null ? Number(tx.payout) : 0;

      tr.innerHTML = `
        <td>${tx.transaction_id || tx.contract_id || '—'}</td>
        <td>${buyTimeStr}</td>
        <td>${sellTimeStr}</td>
        <td>${durationStr}</td>
        <td>${symbol}</td>
        <td>${type}</td>
        <td>$${buyPrice.toFixed(2)}</td>
        <td>${tx.sell_price !== undefined && tx.sell_price !== null ? '$' + sellPrice.toFixed(2) : '—'}</td>
        <td class="${profitTextClass}">${profitStr}</td>
        <td>$${payout.toFixed(2)}</td>
      `;
      this.els.historyTableBody.appendChild(tr);
    });

    if (this.els.historyTableContainer) this.els.historyTableContainer.style.display = 'block';
    this.log('sys', `ดึงข้อมูลประวัติการเทรดสำเร็จ จำนวน ${profitTable.transactions.length} รายการ`);
  }
}