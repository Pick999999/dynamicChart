<?php
/**
 * Load VPS Trade Data Page
 * Vision UI Dark Glassmorphism Design
 * 
 * Features:
 * - Select VPS Server from vpsMaster
 * - Select Date (dtpicker)
 * - Request /api/export_trade_data from VPS (ZIP archive)
 * - Unzip client-side with JSZip
 * - Extract tradeHead.json & trades.json
 * - Save / Insert into MySQL table vpsTradeData with serverCode
 * - Live Log Console & Summary Analytics
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-load-vps-trade-data">
  <style>
    /* Scoped Styles for Load VPS Trade Data */
    .lv-control-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 24px;
      margin-bottom: 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }
    .lv-control-grid {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr auto;
      gap: 16px;
      align-items: flex-end;
    }
    @media (max-width: 992px) {
      .lv-control-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
    @media (max-width: 576px) {
      .lv-control-grid {
        grid-template-columns: 1fr;
      }
    }
    .lv-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 8px;
    }
    .lv-select, .lv-input {
      width: 100%;
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--radius-sm);
      padding: 10px 14px;
      color: #fff;
      font-size: 13px;
      font-family: inherit;
      transition: var(--transition-fast);
      box-sizing: border-box;
    }
    .lv-select:focus, .lv-input:focus {
      border-color: var(--accent-blue);
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
      outline: none;
      background: rgba(15, 28, 70, 0.95);
    }
    .lv-select option {
      background: #0f172a;
      color: #fff;
    }
    .lv-btn-group {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .lv-btn-primary {
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      padding: 10px 20px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(0, 117, 255, 0.35);
      white-space: nowrap;
    }
    .lv-btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 212, 255, 0.45);
    }
    .lv-btn-primary:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    .lv-btn-secondary {
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-secondary);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 10px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
      white-space: nowrap;
    }
    .lv-btn-secondary:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
    }

    /* URL preview sub-bar */
    .lv-url-bar {
      margin-top: 14px;
      padding: 10px 14px;
      background: rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: monospace;
      font-size: 12px;
      color: #00d4ff;
      overflow-x: auto;
    }
    .lv-url-bar span.tag {
      background: rgba(0, 212, 255, 0.15);
      color: #00d4ff;
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 11px;
    }

    /* Stats Grid */
    .lv-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .lv-stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 18px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }
    .lv-stat-val {
      font-size: 24px;
      font-weight: 800;
      color: var(--text-primary);
      margin-top: 4px;
    }
    .lv-stat-label {
      font-size: 12px;
      color: var(--text-tertiary);
      font-weight: 500;
    }
    .lv-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--accent-gradient);
    }
    .lv-stat-icon svg {
      width: 22px;
      height: 22px;
      fill: #fff;
    }

    /* Terminal Console */
    .lv-console-box {
      background: #090d16;
      border: 1px solid rgba(0, 212, 255, 0.2);
      border-radius: var(--radius-md);
      padding: 16px 20px;
      font-family: 'Consolas', 'Courier New', monospace;
      font-size: 12px;
      color: #e2e8f0;
      max-height: 220px;
      overflow-y: auto;
      margin-bottom: 24px;
      box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.6);
    }
    .lv-console-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 8px;
      margin-bottom: 10px;
      font-weight: 600;
      color: #94a3b8;
    }
    .lv-log-entry {
      line-height: 1.6;
      word-break: break-all;
    }
    .lv-log-info { color: #38bdf8; }
    .lv-log-success { color: #34d399; font-weight: bold; }
    .lv-log-warn { color: #fbbf24; }
    .lv-log-error { color: #f87171; font-weight: bold; }

    /* Results Table Card */
    .lv-table-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }
    .lv-table-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .lv-card-title {
      font-size: 16px;
      font-weight: 700;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .lv-table-wrap {
      overflow-x: auto;
    }
    .lv-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .lv-table th {
      background: rgba(255, 255, 255, 0.03);
      color: var(--text-tertiary);
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }
    .lv-table td {
      padding: 12px 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: var(--text-secondary);
      vertical-align: middle;
    }
    .lv-table tr:hover td {
      background: rgba(255, 255, 255, 0.02);
      color: var(--text-primary);
    }
    .badge-win {
      background: rgba(52, 211, 153, 0.15);
      color: #34d399;
      border: 1px solid rgba(52, 211, 153, 0.3);
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
    }
    .badge-loss {
      background: rgba(248, 113, 113, 0.15);
      color: #f87171;
      border: 1px solid rgba(248, 113, 113, 0.3);
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
    }
    .badge-asset {
      background: rgba(0, 117, 255, 0.15);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.3);
      padding: 3px 10px;
      border-radius: 6px;
      font-weight: 700;
      font-family: monospace;
    }
  </style>

  <!-- 1. Control Panel Card -->
  <div class="lv-control-card">
    <div class="lv-control-grid">
      <!-- VPS Selector -->
      <div>
        <label class="lv-label">🖥️ เลือก VPS Server (vpsMaster)</label>
        <select class="lv-select" id="lvVpsSelect">
          <option value="">⏳ กำลังโหลดรายการ VPS...</option>
        </select>
      </div>

      <!-- Date Picker -->
      <div>
        <label class="lv-label">📅 เลือกวันที่ (Date Picker)</label>
        <input type="date" class="lv-input" id="lvDatePicker">
      </div>

      <!-- Server Code Override -->
      <div>
        <label class="lv-label">🔢 serverCode (กำหนดค่ารหัสเครื่อง)</label>
        <input type="number" class="lv-input" id="lvServerCodeInput" value="2" min="1" placeholder="default = 2">
      </div>

      <!-- Action Buttons -->
      <div class="lv-btn-group">
        <button class="lv-btn-primary" id="lvBtnLoad">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
          <span id="lvBtnText">โหลด & Unzip เข้า DB</span>
        </button>
        <button class="lv-btn-secondary" id="lvBtnDownloadZip" title="ดาวน์โหลดไฟล์ .ZIP อย่างเดียว">
          💾 ZIP
        </button>
      </div>
    </div>

    <!-- URL Preview Bar -->
    <div class="lv-url-bar">
      <span class="tag">TARGET API</span>
      <span id="lvUrlPreview">https://.../api/export_trade_data?startdatetime=...</span>
    </div>
  </div>

  <!-- 2. Statistics Grid -->
  <div class="lv-stats-grid">
    <div class="lv-stat-card">
      <div>
        <div class="lv-stat-label">🏢 SELECTED VPS</div>
        <div class="lv-stat-val" id="lvStatVpsName">-</div>
        <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;" id="lvStatServerCode">serverCode: -</div>
      </div>
      <div class="lv-stat-icon" style="background:linear-gradient(135deg,#0075ff,#00d4ff);">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z"/></svg>
      </div>
    </div>

    <div class="lv-stat-card">
      <div>
        <div class="lv-stat-label">📊 TRADES IMPORTED</div>
        <div class="lv-stat-val" id="lvStatTotalTrades">0</div>
        <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;" id="lvStatTrackOrders">0 track orders</div>
      </div>
      <div class="lv-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
      </div>
    </div>

    <div class="lv-stat-card">
      <div>
        <div class="lv-stat-label">🎯 ASSETS PROCESSED</div>
        <div class="lv-stat-val" id="lvStatTotalAssets">0</div>
        <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;" id="lvStatAssetsList">-</div>
      </div>
      <div class="lv-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#6366f1);">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
      </div>
    </div>

    <div class="lv-stat-card">
      <div>
        <div class="lv-stat-label">💰 PROFIT & WIN RATE</div>
        <div class="lv-stat-val" id="lvStatProfit">$0.00</div>
        <div style="font-size:11px;color:#34d399;margin-top:2px;" id="lvStatWinRate">Win Rate: 0%</div>
      </div>
      <div class="lv-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
      </div>
    </div>
  </div>

  <!-- 3. Terminal Live Console Box -->
  <div class="lv-console-box" id="lvConsoleBox">
    <div class="lv-console-header">
      <span>💻 LIVE EXECUTION CONSOLE</span>
      <span style="font-size:11px;cursor:pointer;color:#38bdf8;" onclick="document.getElementById('lvLogArea').innerHTML=''">Clear</span>
    </div>
    <div id="lvLogArea">
      <div class="lv-log-entry lv-log-info">✨ พร้อมสำหรับการโหลดข้อมูล Trade Data จาก VPS... กรุณาเลือก VPS และวันที่ แล้วกดปุ่ม "โหลด & Unzip เข้า DB"</div>
    </div>
  </div>

  <!-- 4. Data Breakdown Table -->
  <div class="lv-table-card">
    <div class="lv-table-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
      <div class="lv-card-title" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="var(--accent-blue)"><path d="M4 14h4v-4H4v4zm0 5h4v-4H4v4zM4 9h4V5H4v4zm5 5h12v-4H9v4zm0 5h12v-4H9v4zM9 5v4h12V5H9z"/></svg>
        <span>สรุปรายการข้อมูลที่นำเข้า (Unzipped Trade Data Summary)</span>
        <span id="lvSummaryVpsBadge" style="display:none; font-size:12px; font-weight:600; background:rgba(0,212,255,0.12); color:#00d4ff; border:1px solid rgba(0,212,255,0.3); padding:3px 10px; border-radius:14px; align-items:center; gap:5px;">
          🖥️ <span id="lvSummaryVpsText">-</span>
        </span>
        <span id="lvSummaryDateBadge" style="display:none; font-size:12px; font-weight:600; background:rgba(16,185,129,0.12); color:#34d399; border:1px solid rgba(16,185,129,0.3); padding:3px 10px; border-radius:14px; align-items:center; gap:5px;">
          📅 <span id="lvSummaryDateText">-</span>
        </span>
      </div>
      <div id="lvTradeHeadBadge" style="display:none;" class="badge-win">
        🏷️ tradeHead.json: Loaded
      </div>
    </div>

    <div class="lv-table-wrap">
      <table class="lv-table" id="lvSummaryTable">
        <thead>
          <tr>
            <th>Asset Symbol</th>
            <th>Trades Count</th>
            <th>Track Orders</th>
            <th>Wins</th>
            <th>Losses</th>
            <th>Win Rate</th>
            <th>Total Profit</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="lvSummaryTbody">
          <tr>
            <td colspan="8" style="text-align:center;color:var(--text-tertiary);padding:30px;">
              ยังไม่มีข้อมูลที่โหลด — กรุณากดปุ่ม "โหลด & Unzip เข้า DB"
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function() {
  const API_VPS_URL = '../php/api_vps.php';
  const SAVE_GCP_URL = '../php/save_gcp_trades.php';
  const SAVE_TRADE_ROUNDS_URL = '../php/save_trade_rounds.php';

  const vpsSelect = document.getElementById('lvVpsSelect');
  const datePicker = document.getElementById('lvDatePicker');
  const serverCodeInput = document.getElementById('lvServerCodeInput');
  const btnLoad = document.getElementById('lvBtnLoad');
  const btnDownloadZip = document.getElementById('lvBtnDownloadZip');
  const btnText = document.getElementById('lvBtnText');
  const urlPreview = document.getElementById('lvUrlPreview');
  const logArea = document.getElementById('lvLogArea');
  const consoleBox = document.getElementById('lvConsoleBox');
  const summaryTbody = document.getElementById('lvSummaryTbody');

  const statVpsName = document.getElementById('lvStatVpsName');
  const statServerCode = document.getElementById('lvStatServerCode');
  const statTotalTrades = document.getElementById('lvStatTotalTrades');
  const statTrackOrders = document.getElementById('lvStatTrackOrders');
  const statTotalAssets = document.getElementById('lvStatTotalAssets');
  const statAssetsList = document.getElementById('lvStatAssetsList');
  const statProfit = document.getElementById('lvStatProfit');
  const statWinRate = document.getElementById('lvStatWinRate');
  const tradeHeadBadge = document.getElementById('lvTradeHeadBadge');

  const summaryVpsBadge = document.getElementById('lvSummaryVpsBadge');
  const summaryVpsText = document.getElementById('lvSummaryVpsText');
  const summaryDateBadge = document.getElementById('lvSummaryDateBadge');
  const summaryDateText = document.getElementById('lvSummaryDateText');

  let vpsList = [];
  let currentVps = null;

  // Set Default Date to Today
  const todayStr = new Date().toISOString().split('T')[0];
  datePicker.value = todayStr;

  function appendLog(msg, type = 'info') {
    const entry = document.createElement('div');
    entry.className = `lv-log-entry lv-log-${type}`;
    const time = new Date().toLocaleTimeString();
    entry.innerHTML = `[${time}] ${msg}`;
    logArea.appendChild(entry);
    consoleBox.scrollTop = consoleBox.scrollHeight;
  }

  // Helper: Build target URL for export_trade_data
  function getVpsExportUrl(vps, date) {
    if (!vps) return '';
    let base = '';
    if (vps.url && vps.url.trim() !== '') {
      base = vps.url.trim().replace(/\/+$/, '');
    } else if (vps.publicIP && vps.publicIP.trim() !== '') {
      let ip = vps.publicIP.trim().replace(/\/+$/, '');
      if (vps.portno && String(vps.portno).trim() !== '') {
        base = `${ip}:${String(vps.portno).trim()}`;
      } else {
        base = ip;
      }
    } else {
      base = window.location.origin;
    }

    // Ensure protocol (http:// or https://) is always present
    if (!/^https?:\/\//i.test(base)) {
      if (base.includes('pkderiv.online') || base.includes('gpkderiv.shop') || base.endsWith('.shop') || base.endsWith('.online')) {
        base = 'https://' + base;
      } else {
        base = 'http://' + base;
      }
    }

    return `${base}/api/export_trade_data?startdatetime=${date}`;
  }

  function updateUrlPreview() {
    const selectedId = vpsSelect.value;
    currentVps = vpsList.find(v => String(v.id) === String(selectedId)) || null;

    if (currentVps) {
      statVpsName.textContent = currentVps.vpsName;
      
      // Try extract numeric serverCode from vpsCode if possible
      let sc = parseInt(currentVps.vpsCode, 10);
      if (isNaN(sc) || sc <= 0) sc = 2;
      serverCodeInput.value = sc;
      statServerCode.textContent = `serverCode: ${sc} (${currentVps.vpsCode})`;

      const targetUrl = getVpsExportUrl(currentVps, datePicker.value);
      urlPreview.textContent = targetUrl;

      // Update VPS name and Date badges on Unzipped Trade Data Summary
      if (summaryVpsBadge && summaryVpsText) {
        summaryVpsBadge.style.display = 'inline-flex';
        summaryVpsText.textContent = `VPS: [${currentVps.vpsCode}] ${currentVps.vpsName}`;
      }
      if (summaryDateBadge && summaryDateText) {
        summaryDateBadge.style.display = 'inline-flex';
        summaryDateText.textContent = `วันที่: ${datePicker.value}`;
      }
    } else {
      statVpsName.textContent = '-';
      statServerCode.textContent = 'serverCode: -';
      urlPreview.textContent = '-';
      if (summaryVpsBadge) summaryVpsBadge.style.display = 'none';
      if (summaryDateBadge) summaryDateBadge.style.display = 'none';
    }
  }

  // 1. Fetch VPS List from MySQL (api_vps.php)
  async function loadVpsList() {
    try {
      appendLog('📡 กำลังดึงรายชื่อ VPS จากฐานข้อมูล (vpsMaster)...', 'info');
      const res = await fetch(API_VPS_URL);
      if (!res.ok) throw new Error(`HTTP Error ${res.status}`);
      const json = await res.json();

      if (json.success && Array.isArray(json.data) && json.data.length > 0) {
        vpsList = json.data;
        // เรียงลำดับตาม 1, 2, 3, 4 (vpsCode ASC)
        vpsList.sort((a, b) => {
          const codeA = parseInt(a.vpsCode, 10);
          const codeB = parseInt(b.vpsCode, 10);
          if (!isNaN(codeA) && !isNaN(codeB)) return codeA - codeB;
          return (a.vpsCode || '').localeCompare(b.vpsCode || '') || (a.id - b.id);
        });

        vpsSelect.innerHTML = vpsList.map(v => {
          const ipText = v.url ? v.url : (v.publicIP ? `${v.publicIP}${v.portno ? ':'+v.portno : ''}` : 'Local');
          return `<option value="${v.id}">[${v.vpsCode}] ${v.vpsName} (${ipText})</option>`;
        }).join('');

        appendLog(`✅ โหลดรายชื่อ VPS สำเร็จ (${vpsList.length} เครื่อง)`, 'success');
        updateUrlPreview();
      } else {
        vpsSelect.innerHTML = '<option value="">⚠️ ยังไม่มีข้อมูล VPS ใน vpsMaster</option>';
        appendLog('⚠️ ไม่พบข้อมูล VPS ในตาราง vpsMaster กรุณาเพิ่มข้อมูลในเมนู VPS Master ก่อน', 'warn');
      }
    } catch (e) {
      vpsSelect.innerHTML = `<option value="">❌ ไม่สามารถโหลด VPS ได้: ${e.message}</option>`;
      appendLog(`❌ เกิดข้อผิดพลาดในการโหลด VPS: ${e.message}`, 'error');
    }
  }

  // 2. Fetch & Unzip Trade Data from VPS
  async function fetchAndProcessZip(downloadOnly = false) {
    if (!currentVps) {
      alert('กรุณาเลือก VPS Server ก่อน');
      return;
    }

    const date = datePicker.value;
    if (!date) {
      alert('กรุณาเลือกวันที่');
      return;
    }

    const exportUrl = getVpsExportUrl(currentVps, date);
    const serverCode = parseInt(serverCodeInput.value, 10) || 2;

    if (summaryVpsBadge && summaryVpsText) {
      summaryVpsBadge.style.display = 'inline-flex';
      summaryVpsText.textContent = `VPS: [${currentVps.vpsCode}] ${currentVps.vpsName}`;
    }
    if (summaryDateBadge && summaryDateText) {
      summaryDateBadge.style.display = 'inline-flex';
      summaryDateText.textContent = `วันที่: ${date}`;
    }

    btnLoad.disabled = true;
    btnDownloadZip.disabled = true;
    btnText.textContent = 'กำลังดาวน์โหลด...';
    appendLog(`🌐 กำลังส่ง Request ไปยัง: ${exportUrl}`, 'info');

    try {
      let arrayBuffer = null;

      try {
        // Method 1: Try Direct Fetch
        const response = await fetch(exportUrl);
        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`API error (${response.status}): ${errorText}`);
        }
        appendLog(`✅ ได้รับ Response สำเร็จ (Status: ${response.status}) กำลังอ่าน Binary ArrayBuffer...`, 'success');
        arrayBuffer = await response.arrayBuffer();
      } catch (directErr) {
        // Method 2: Fallback to PHP Backend Proxy (bypasses CORS / local network security blocks)
        appendLog(`⚠️ Direct Fetch: ${directErr.message}`, 'warn');
        appendLog(`🔄 กำลังสลับไปดาวน์โหลดผ่าน PHP Backend Proxy...`, 'info');

        const proxyRes = await fetch(SAVE_GCP_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'download_and_save',
            exportUrl: exportUrl,
            serverCode: serverCode,
            date: date,
            vpsId: currentVps.id,
            vpsName: currentVps.vpsName
          })
        });

        if (!proxyRes.ok) {
          const proxyErr = await proxyRes.text();
          throw new Error(`Backend Proxy Error (${proxyRes.status}): ${proxyErr}`);
        }

        const proxyJson = await proxyRes.json();
        if (!proxyJson.success) {
          throw new Error(proxyJson.error || proxyJson.message || 'ไม่สามารถดาวน์โหลดข้อมูลผ่าน Proxy ได้');
        }

        appendLog(`✅ ดาวน์โหลดผ่าน Backend Proxy สำเร็จ (${(proxyJson.zipSize / 1024).toFixed(1)} KB)`, 'success');
        const binStr = atob(proxyJson.zipBase64);
        const u8 = new Uint8Array(binStr.length);
        for (let i = 0; i < binStr.length; i++) {
          u8[i] = binStr.charCodeAt(i);
        }
        arrayBuffer = u8.buffer;
      }

      if (downloadOnly) {
        // Download as ZIP file
        const blob = new Blob([arrayBuffer], { type: 'application/zip' });
        const downloadUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = downloadUrl;
        a.download = `trade_data_${currentVps.vpsCode}_${date}.zip`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(downloadUrl);
        document.body.removeChild(a);
        appendLog(`💾 ดาวน์โหลดไฟล์ ZIP เรียบร้อย: trade_data_${currentVps.vpsCode}_${date}.zip`, 'success');
        btnLoad.disabled = false;
        btnDownloadZip.disabled = false;
        btnText.textContent = 'โหลด & Unzip เข้า DB';
        return;
      }

      // Check JSZip
      if (typeof JSZip === 'undefined') {
        throw new Error('ไม่พบ JSZip Library ในหน้าเว็บ กรุณารีเฟรชหน้าใหม่อีกครั้ง');
      }

      btnText.textContent = 'กำลัง Unzip...';
      appendLog(`📂 กำลังแตกไฟล์ ZIP ด้วย JSZip ใน Browser...`, 'info');
      const zip = await JSZip.loadAsync(arrayBuffer);
      const zipFiles = Object.keys(zip.files);
      appendLog(`📂 แตกไฟล์ ZIP สำเร็จ! พบไฟล์ทั้งหมด ${zipFiles.length} รายการ`, 'success');

      // Parse JSON files
      const tradeData = {};
      let tradeHeadData = null;

      for (const [filename, zipEntry] of Object.entries(zip.files)) {
        if (zipEntry.dir) continue;

        const content = await zipEntry.async('text');
        let jsonData;
        try {
          jsonData = JSON.parse(content);
        } catch (pe) {
          appendLog(`⚠️ ไม่สามารถ Parse JSON (${filename}): ${pe.message}`, 'warn');
          continue;
        }

        const cleanPath = filename.replace(/^\.\//, '');
        const pathParts = cleanPath.split('/');

        if (pathParts.length === 1) {
          if (pathParts[0] === 'tradeHead.json') {
            tradeHeadData = jsonData;
            appendLog(`📄 [tradeHead.json] โหลดข้อมูลรอบการเทรดสำเร็จ: ${Array.isArray(jsonData) ? jsonData.length + ' รอบ' : 'OK'}`, 'success');
          }
        } else if (pathParts.length === 2) {
          const [assetCode, jsonFile] = pathParts;
          if (!tradeData[assetCode]) {
            tradeData[assetCode] = { assetCode: assetCode, trades: null, trackOrders: null, tradeHead: null };
          }
          if (jsonFile === 'trades.json') {
            tradeData[assetCode].trades = jsonData;
            appendLog(`📄 [${assetCode}/trades.json] พบ ${jsonData.length} records`, 'info');
          } else if (jsonFile === 'track_orders.json') {
            tradeData[assetCode].trackOrders = jsonData;
            appendLog(`📄 [${assetCode}/track_orders.json] พบ ${jsonData.length} records`, 'info');
          } else if (jsonFile === 'tradeHead.json') {
            tradeData[assetCode].tradeHead = jsonData;
            if (!tradeHeadData) tradeHeadData = jsonData;
          }
        }
      }

      // Check tradeHead for serverCode
      if (tradeHeadData) {
        tradeHeadBadge.style.display = 'inline-block';
        if (Array.isArray(tradeHeadData) && tradeHeadData.length > 0 && tradeHeadData[0].serverCode) {
          const scFromHead = parseInt(tradeHeadData[0].serverCode, 10);
          if (!isNaN(scFromHead)) {
            appendLog(`ℹ️ ตรวจพบ serverCode = ${scFromHead} จาก tradeHead.json`, 'info');
          }
        }
      } else {
        tradeHeadBadge.style.display = 'none';
      }

      // 3. Post to Database via save_gcp_trades.php
      btnText.textContent = 'กำลังบันทึกลง Database...';
      appendLog(`💾 กำลังส่งข้อมูล Trades ไปบันทึกลง MySQL (vpsTradeData) ด้วย serverCode = ${serverCode}...`, 'info');

      const itemsToSave = [];
      let totalTradeCount = 0;
      let totalTrackOrderCount = 0;
      let totalProfitSum = 0;
      let totalWinsSum = 0;
      let totalTradesAnalyzed = 0;

      const summaryRows = [];

      for (const [sym, data] of Object.entries(tradeData)) {
        if (data.trades && Array.isArray(data.trades) && data.trades.length > 0) {
          itemsToSave.push({
            symbol: sym,
            serverCode: serverCode,
            trades: data.trades
          });

          const tCount = data.trades.length;
          const trCount = data.trackOrders ? data.trackOrders.length : 0;
          totalTradeCount += tCount;
          totalTrackOrderCount += trCount;

          let assetProfit = 0;
          let assetWins = 0;
          let assetLosses = 0;

          data.trades.forEach(t => {
            const p = parseFloat(t.ThisProfit || 0);
            assetProfit += p;
            totalProfitSum += p;
            totalTradesAnalyzed++;
            if (p >= 0) {
              assetWins++;
              totalWinsSum++;
            } else {
              assetLosses++;
            }
          });

          const winRate = tCount > 0 ? ((assetWins / tCount) * 100).toFixed(1) : 0;
          summaryRows.push({
            symbol: sym,
            trades: tCount,
            trackOrders: trCount,
            wins: assetWins,
            losses: assetLosses,
            winRate: winRate,
            profit: assetProfit
          });
        }
      }

      if (itemsToSave.length === 0) {
        appendLog(`⚠️ ไม่พบข้อมูล trades.json ในไฟล์ ZIP สำหรับนำเข้า`, 'warn');
        summaryTbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#fbbf24;padding:20px;">⚠️ ไม่พบข้อมูลการเทรดในไฟล์ ZIP</td></tr>';
      } else {
        const saveResponse = await fetch(SAVE_GCP_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            serverCode: serverCode,
            items: itemsToSave
          })
        });

        if (!saveResponse.ok) {
          const errSave = await saveResponse.text();
          throw new Error(`Save to DB Error: ${errSave}`);
        }

        const saveResult = await saveResponse.json();
        appendLog(`🎉 [MySQL Commit] ${saveResult.message || 'บันทึกลงตาราง vpsTradeData สำเร็จ!'}`, 'success');

        // Update Stats Display
        statTotalTrades.textContent = totalTradeCount;
        statTrackOrders.textContent = `${totalTrackOrderCount} track orders`;
        statTotalAssets.textContent = itemsToSave.length;
        statAssetsList.textContent = itemsToSave.map(i => i.symbol).join(', ');
        statProfit.textContent = (totalProfitSum >= 0 ? '+' : '') + '$' + totalProfitSum.toFixed(2);
        statProfit.style.color = totalProfitSum >= 0 ? '#34d399' : '#f87171';
        const overallWinRate = totalTradesAnalyzed > 0 ? ((totalWinsSum / totalTradesAnalyzed) * 100).toFixed(1) : 0;
        statWinRate.textContent = `Win Rate: ${overallWinRate}% (${totalWinsSum}W / ${totalTradesAnalyzed - totalWinsSum}L)`;

        // Render Summary Table
        summaryTbody.innerHTML = summaryRows.map(r => `
          <tr>
            <td><span class="badge-asset">${r.symbol}</span></td>
            <td><strong>${r.trades}</strong> รายการ</td>
            <td>${r.trackOrders}</td>
            <td><span class="badge-win">${r.wins} W</span></td>
            <td><span class="badge-loss">${r.losses} L</span></td>
            <td><strong style="color:#00d4ff;">${r.winRate}%</strong></td>
            <td style="color:${r.profit >= 0 ? '#34d399' : '#f87171'};font-weight:700;">
              ${r.profit >= 0 ? '+' : ''}$${r.profit.toFixed(2)}
            </td>
            <td><span class="badge-win">✅ Saved to DB</span></td>
          </tr>
        `).join('');
      }

      // 4. Save tradeHead.json to MySQL (tradeHead)
      if (tradeHeadData && Array.isArray(tradeHeadData) && tradeHeadData.length > 0) {
        try {
          appendLog(`📋 กำลังบันทึกข้อมูลรอบการเทรด tradeHead.json (${tradeHeadData.length} รอบ) ลงตาราง tradeHead...`, 'info');
          const thRes = await fetch(SAVE_TRADE_ROUNDS_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              serverCode: serverCode,
              tradeHead: tradeHeadData
            })
          });
          if (thRes.ok) {
            const thResult = await thRes.json();
            appendLog(`🎉 [MySQL Commit] บันทึก tradeHead สำเร็จ: ${thResult.savedRounds || tradeHeadData.length} รอบ (${thResult.savedRows || 0} รายการสินทรัพย์)`, 'success');
          } else {
            const thErrText = await thRes.text();
            appendLog(`⚠️ ไม่สามารถบันทึก tradeHead ได้ (HTTP ${thRes.status}): ${thErrText}`, 'warn');
          }
        } catch (thErr) {
          appendLog(`⚠️ บันทึก tradeHead.json ไม่สำเร็จ: ${thErr.message}`, 'warn');
        }
      }

    } catch (err) {
      appendLog(`❌ เกิดข้อผิดพลาด: ${err.message}`, 'error');
      alert(`เกิดข้อผิดพลาด: ${err.message}`);
    } finally {
      btnLoad.disabled = false;
      btnDownloadZip.disabled = false;
      btnText.textContent = 'โหลด & Unzip เข้า DB';
    }
  }

  // Event Listeners
  vpsSelect.addEventListener('change', updateUrlPreview);
  datePicker.addEventListener('change', updateUrlPreview);
  serverCodeInput.addEventListener('input', () => {
    statServerCode.textContent = `serverCode: ${serverCodeInput.value || 2}`;
  });

  btnLoad.addEventListener('click', () => fetchAndProcessZip(false));
  btnDownloadZip.addEventListener('click', () => fetchAndProcessZip(true));

  // Init
  loadVpsList();
})();
</script>
