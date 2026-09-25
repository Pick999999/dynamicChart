<?php
/**
 * Calendar Trade Hist V2 Page
 * - VPS buttons with background-image from vpsMaster.image
 * - Date picker for trade history
 * - Trade data table with KPI summary
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-calendar-trade-hist-v2">
  <style>
    .cthv2-wrap { display:flex; flex-direction:column; gap:20px; animation: cthv2FadeIn .3s ease; }
    @keyframes cthv2FadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

    /* VPS Selector */
    .cthv2-vps-bar {
      background: var(--bg-card, rgba(6,11,40,.94));
      border: 1px solid var(--border-color, rgba(255,255,255,.08));
      border-radius: 16px; padding: 20px 24px;
      backdrop-filter: blur(20px);
      box-shadow: 0 8px 32px rgba(0,0,0,.37);
    }
    .cthv2-vps-bar-title {
      font-size:14px; font-weight:700; color:#fff; margin-bottom:14px;
      display:flex; align-items:center; gap:8px;
    }
    .cthv2-vps-bar-title svg { width:20px; height:20px; fill:#ec4899; }
    .cthv2-vps-grid {
      display:flex; flex-wrap:wrap; gap:12px;
    }
    .cthv2-vps-btn {
      position:relative; width:130px; height:90px;
      border-radius:12px; border:2px solid rgba(255,255,255,.1);
      background-size:cover; background-position:center; background-repeat:no-repeat;
      cursor:pointer; overflow:hidden; transition:all .25s ease;
      display:flex; align-items:flex-end; justify-content:center;
    }
    .cthv2-vps-btn::before {
      content:''; position:absolute; inset:0;
      background:linear-gradient(0deg, rgba(0,0,0,.75) 0%, rgba(0,0,0,.15) 50%, rgba(0,0,0,.05) 100%);
      border-radius:10px; transition:all .25s;
    }
    .cthv2-vps-btn:hover { transform:translateY(-3px); border-color:rgba(236,72,153,.6); box-shadow:0 6px 20px rgba(236,72,153,.3); }
    .cthv2-vps-btn:hover::before { background:linear-gradient(0deg, rgba(236,72,153,.5) 0%, rgba(0,0,0,.1) 60%); }
    .cthv2-vps-btn.active { border-color:#ec4899; box-shadow:0 0 18px rgba(236,72,153,.45); }
    .cthv2-vps-btn.active::before { background:linear-gradient(0deg, rgba(236,72,153,.55) 0%, rgba(0,0,0,.12) 60%); }
    .cthv2-vps-label {
      position:relative; z-index:1; padding:6px 8px; width:100%; text-align:center;
      font-size:11px; font-weight:700; color:#fff; text-shadow:0 1px 4px rgba(0,0,0,.7);
      line-height:1.3;
    }
    .cthv2-vps-label small { display:block; font-size:9px; font-weight:500; opacity:.8; }
    .cthv2-vps-btn-all {
      width:90px; height:90px; border-radius:12px;
      border:2px solid rgba(255,255,255,.15);
      background:linear-gradient(135deg, rgba(139,92,246,.25), rgba(236,72,153,.25));
      display:flex; align-items:center; justify-content:center; flex-direction:column;
      cursor:pointer; transition:all .25s; gap:4px;
      font-size:12px; font-weight:700; color:#e2e8f0;
    }
    .cthv2-vps-btn-all:hover { border-color:#8b5cf6; transform:translateY(-3px); box-shadow:0 6px 20px rgba(139,92,246,.3); }
    .cthv2-vps-btn-all.active { border-color:#8b5cf6; box-shadow:0 0 18px rgba(139,92,246,.45); background:linear-gradient(135deg, rgba(139,92,246,.4), rgba(236,72,153,.4)); }
    .cthv2-vps-btn-all svg { width:24px; height:24px; fill:#c084fc; }

    /* No-image fallback */
    .cthv2-vps-btn-noimg {
      background: linear-gradient(135deg, rgba(30,41,82,.9), rgba(15,23,50,.95)) !important;
    }

    /* Controls bar */
    .cthv2-controls {
      background: var(--bg-card, rgba(6,11,40,.94));
      border: 1px solid var(--border-color, rgba(255,255,255,.08));
      border-radius: 16px; padding: 18px 24px;
      backdrop-filter: blur(20px);
      box-shadow: 0 8px 32px rgba(0,0,0,.37);
      display:flex; flex-wrap:wrap; align-items:center; gap:14px;
    }
    .cthv2-dt-label { font-size:13px; font-weight:600; color:var(--text-secondary,#cbd5e1); display:flex; align-items:center; gap:6px; }
    .cthv2-dt-label svg { width:16px; height:16px; fill:#ec4899; }
    .cthv2-dt-input {
      background:rgba(10,19,48,.9); border:1px solid rgba(255,255,255,.15);
      border-radius:8px; padding:8px 12px; color:#fff; font-size:13px;
      font-family:inherit; outline:none; color-scheme:dark; transition:all .2s;
    }
    .cthv2-dt-input:focus { border-color:#ec4899; box-shadow:0 0 8px rgba(236,72,153,.35); }
    .cthv2-qbtn {
      padding:7px 14px; font-size:12px; font-weight:600; border-radius:8px;
      border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.05);
      color:#fff; cursor:pointer; transition:all .2s;
    }
    .cthv2-qbtn:hover { background:rgba(236,72,153,.2); border-color:#ec4899; }
    .cthv2-qbtn.active { background:rgba(236,72,153,.3); border-color:#ec4899; }

    /* KPI row */
    .cthv2-kpi-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:16px; }
    .cthv2-kpi {
      background: var(--bg-card, rgba(6,11,40,.94));
      border: 1px solid var(--border-color, rgba(255,255,255,.08));
      border-radius: 14px; padding: 18px 20px;
      backdrop-filter: blur(20px); box-shadow: 0 8px 32px rgba(0,0,0,.37);
      display:flex; align-items:center; justify-content:space-between;
    }
    .cthv2-kpi-label { font-size:12px; font-weight:500; color:var(--text-tertiary,rgba(255,255,255,.5)); }
    .cthv2-kpi-val { font-size:22px; font-weight:800; color:#fff; margin-top:2px; }
    .cthv2-kpi-icon {
      width:44px; height:44px; border-radius:12px;
      display:flex; align-items:center; justify-content:center;
      flex-shrink:0;
    }
    .cthv2-kpi-icon svg { width:22px; height:22px; fill:#fff; }

    /* Table */
    .cthv2-table-card {
      background: var(--bg-card, rgba(6,11,40,.94));
      border: 1px solid var(--border-color, rgba(255,255,255,.08));
      border-radius: 16px; padding: 20px;
      backdrop-filter: blur(20px); box-shadow: 0 8px 32px rgba(0,0,0,.37);
    }
    .cthv2-table-header {
      display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;
      gap:12px; margin-bottom:14px; padding-bottom:12px;
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    .cthv2-table-header-left {
      display:flex; align-items:center; flex-wrap:wrap; gap:12px;
    }
    .cthv2-table-title { font-size:15px; font-weight:700; color:#fff; display:flex; align-items:center; gap:8px; }
    .cthv2-table-title svg { width:18px; height:18px; fill:#ec4899; }
    
    /* Asset Filter Bar */
    .cthv2-asset-bar {
      display:inline-flex; align-items:center; flex-wrap:wrap; gap:6px;
    }
    .cthv2-asset-btn {
      display:inline-flex; align-items:center; gap:6px;
      padding:4px 10px; font-size:11.5px; font-weight:600; border-radius:8px;
      border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.05);
      color:#cbd5e1; cursor:pointer; transition:all .2s cubic-bezier(0.4, 0, 0.2, 1);
      user-select:none;
    }
    .cthv2-asset-btn:hover {
      background:rgba(236,72,153,.15); border-color:rgba(236,72,153,.5);
      color:#fff; transform:translateY(-1px);
    }
    .cthv2-asset-btn.active {
      background:linear-gradient(135deg,#ec4899,#8b5cf6);
      border-color:transparent; color:#fff;
      box-shadow:0 4px 12px rgba(236,72,153,.35); font-weight:700;
    }
    .cthv2-losscon-tag {
      background:rgba(239,68,68,.22); color:#fca5a5;
      border:1px solid rgba(239,68,68,.4);
      padding:1px 6px; border-radius:6px;
      font-size:10.5px; font-weight:700; line-height:1.2;
    }
    .cthv2-asset-btn.active .cthv2-losscon-tag {
      background:rgba(0,0,0,.3); color:#fff;
      border-color:rgba(255,255,255,.35);
    }

    /* Lab Checkbox */
    .cthv2-lab-check-label {
      display:inline-flex; align-items:center; gap:6px;
      font-size:11.5px; font-weight:500; color:#94a3b8;
      cursor:pointer; padding:4px 9px; border-radius:8px;
      background:rgba(255,255,255,.03); border:1px dashed rgba(255,255,255,.15);
      transition:all .2s; user-select:none;
    }
    .cthv2-lab-check-label:hover {
      color:#e2e8f0; background:rgba(255,255,255,.07);
      border-color:rgba(236,72,153,.5);
    }
    .cthv2-lab-check-label input[type="checkbox"] {
      accent-color:#ec4899; cursor:pointer;
      width:14px; height:14px; margin:0;
    }

    /* Deriv Fetch Button & Status */
    .cthv2-btn-deriv {
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(249, 115, 22, 0.2));
      border: 1px solid rgba(249, 115, 22, 0.45);
      color: #fdba74;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .cthv2-btn-deriv:hover {
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.35), rgba(249, 115, 22, 0.35));
      border-color: #f97316;
      color: #fff;
      box-shadow: 0 0 12px rgba(249, 115, 22, 0.35);
      transform: translateY(-1px);
    }
    .cthv2-btn-deriv:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    .cthv2-deriv-status {
      font-size: 11.5px;
      font-weight: 600;
      color: #fbbf24;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .cthv2-badge {
      display:inline-flex; align-items:center; padding:3px 9px;
      font-size:11px; font-weight:600; border-radius:6px;
    }
    .cthv2-badge-pink { background:rgba(236,72,153,.2); color:#f472b6; border:1px solid rgba(236,72,153,.35); }
    .cthv2-table-wrap { overflow-x:auto; max-height:500px; border-radius:8px; border:1px solid rgba(255,255,255,.06); }
    .cthv2-table { width:100%; border-collapse:collapse; font-size:12px; text-align:left; }
    .cthv2-table th {
      background:rgba(15,23,42,.9); color:var(--text-secondary,#cbd5e1);
      font-weight:600; padding:10px 12px; position:sticky; top:0;
      border-bottom:1px solid rgba(255,255,255,.08); white-space:nowrap;
    }
    .cthv2-table td {
      padding:8px 12px; border-bottom:1px solid rgba(255,255,255,.04);
      color:var(--text-primary,#e2e8f0); white-space:nowrap;
    }
    .cthv2-table tbody tr:hover { background:rgba(236,72,153,.08); }
    .cthv2-win { color:#34d399; font-weight:700; }
    .cthv2-loss { color:#f87171; font-weight:700; }
    .cthv2-skip { color:#94a3b8; }
    .cthv2-idle { color:#fbbf24; }

    .cthv2-empty {
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      padding:50px 20px; color:var(--text-tertiary,#a0aec0); text-align:center; gap:10px;
    }
    .cthv2-empty svg { width:42px; height:42px; fill:rgba(255,255,255,.12); }
  </style>

  <div class="cthv2-wrap">
    <!-- 1. VPS Selector Bar -->
    <div class="cthv2-vps-bar">
      <div class="cthv2-vps-bar-title">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2z"/></svg>
        เลือก VPS Server
      </div>
      <div class="cthv2-vps-grid" id="cthv2VpsGrid">
        <div class="cthv2-empty"><div class="spinner" style="width:20px;height:20px;"></div><span>กำลังโหลด VPS...</span></div>
      </div>
    </div>

    <!-- 2. Date Picker & Quick Nav -->
    <div class="cthv2-controls">
      <span class="cthv2-dt-label">
        <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
        วันที่:
      </span>
      <input type="date" id="cthv2DatePicker" class="cthv2-dt-input">
      <button class="cthv2-qbtn" id="cthv2BtnToday">วันนี้</button>
      <button class="cthv2-qbtn" id="cthv2BtnYesterday">เมื่อวาน</button>
      <button class="cthv2-qbtn" id="cthv2BtnPrev">◀ ก่อนหน้า</button>
      <button class="cthv2-qbtn" id="cthv2BtnNext">ถัดไป ▶</button>
      
      <!-- Deriv Fetch & Save Button -->
      <button type="button" class="cthv2-qbtn cthv2-btn-deriv" id="cthv2BtnLoadDeriv" title="ขอ OTP แล้วดึง profit_table จาก Deriv และบันทึกลง MySQL (derivTradeHistory)">
        <span>📥</span>
        <span id="cthv2BtnLoadDerivText">Load Trade from Deriv</span>
      </button>
      <span id="cthv2DerivStatus" class="cthv2-deriv-status"></span>

      <button class="cthv2-qbtn" id="cthv2BtnRefresh" style="margin-left:auto; color:#ec4899; border-color:rgba(236,72,153,.4);">🔄 Refresh</button>
    </div>

    <!-- 3. KPI Summary -->
    <div class="cthv2-kpi-row">
      <div class="cthv2-kpi">
        <div><div class="cthv2-kpi-label">Total Trades</div><div class="cthv2-kpi-val" id="cthv2KpiTotal">—</div></div>
        <div class="cthv2-kpi-icon" style="background:linear-gradient(135deg,#0075ff,#00d4ff);"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg></div>
      </div>
      <div class="cthv2-kpi">
        <div><div class="cthv2-kpi-label">Win Rate</div><div class="cthv2-kpi-val" id="cthv2KpiWinRate">—</div></div>
        <div class="cthv2-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><svg viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0011 15.9V19H7v2h10v-2h-4v-3.1c1.8-.45 3.19-1.98 3.61-3.96C19.08 11.63 21 9.55 21 8V7c0-1.1-.9-2-2-2z"/></svg></div>
      </div>
      <div class="cthv2-kpi">
        <div><div class="cthv2-kpi-label">Net Profit</div><div class="cthv2-kpi-val" id="cthv2KpiProfit">—</div></div>
        <div class="cthv2-kpi-icon" id="cthv2KpiProfitIcon" style="background:linear-gradient(135deg,#8b5cf6,#6366f1);"><svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
      </div>
      <div class="cthv2-kpi">
        <div><div class="cthv2-kpi-label">Win / Loss</div><div class="cthv2-kpi-val" id="cthv2KpiWL">—</div></div>
        <div class="cthv2-kpi-icon" style="background:linear-gradient(135deg,#ec4899,#d946ef);"><svg viewBox="0 0 24 24"><path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg></div>
      </div>
    </div>

    <!-- 4. Trade Table -->
    <div class="cthv2-table-card">
      <div class="cthv2-table-header">
        <div class="cthv2-table-header-left">
          <div class="cthv2-table-title">
            <svg viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
            Trade History
          </div>
          <!-- Asset Filter Buttons -->
          <div class="cthv2-asset-bar" id="cthv2AssetBar"></div>
          <!-- Open ResistanceLab in new tab checkbox -->
          <label class="cthv2-lab-check-label" title="เปิดหน้า ResistanceLab ในแท็บใหม่เมื่อคลิกเลือก Asset">
            <input type="checkbox" id="cthv2OpenLabCheck">
            <span>เปิด ResistanceLab แท็บใหม่</span>
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
          </label>
        </div>
        <span class="cthv2-badge cthv2-badge-pink" id="cthv2TradeCount">0 trades</span>
      </div>
      <div class="cthv2-table-wrap">
        <table class="cthv2-table">
          <thead>
            <tr>
              <th>#</th><th>VPS</th><th>เวลา</th><th>Symbol</th><th>Action</th>
              <th>Color</th><th>Win/Loss</th><th>Money ($)</th><th>Profit ($)</th>
              <th>Balance ($)</th><th>LossCon</th><th>Strategy</th>
            </tr>
          </thead>
          <tbody id="cthv2Tbody">
            <tr><td colspan="12"><div class="cthv2-empty"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg><p>เลือก VPS และวันที่ เพื่อดูประวัติเทรด</p></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
(function() {
  'use strict';

  var API = '../php/api_calendar_trade_v2.php';
  var API_DERIV_ACC = '../php/api_deriv_account.php';
  var API_SAVE_TRADE_HIST = '../php/save_trade_history.php';
  var vpsList = [];
  var accountsList = [];
  var datesList = [];
  var selectedVps = null; // null = all
  var selectedDate = null;
  var currentRawTrades = [];
  var currentSummary = null;
  var selectedAsset = null; // null = All
  var currentSource = null;

  // DOM
  var vpsGrid     = document.getElementById('cthv2VpsGrid');
  var datePicker  = document.getElementById('cthv2DatePicker');
  var btnToday    = document.getElementById('cthv2BtnToday');
  var btnYesterday= document.getElementById('cthv2BtnYesterday');
  var btnPrev     = document.getElementById('cthv2BtnPrev');
  var btnNext     = document.getElementById('cthv2BtnNext');
  var btnRefresh  = document.getElementById('cthv2BtnRefresh');
  var btnLoadDeriv = document.getElementById('cthv2BtnLoadDeriv');
  var btnLoadDerivText = document.getElementById('cthv2BtnLoadDerivText');
  var derivStatus = document.getElementById('cthv2DerivStatus');
  var tbody       = document.getElementById('cthv2Tbody');
  var tradeCountBadge = document.getElementById('cthv2TradeCount');
  var kpiTotal    = document.getElementById('cthv2KpiTotal');
  var kpiWinRate  = document.getElementById('cthv2KpiWinRate');
  var kpiProfit   = document.getElementById('cthv2KpiProfit');
  var kpiProfitIcon = document.getElementById('cthv2KpiProfitIcon');
  var kpiWL       = document.getElementById('cthv2KpiWL');
  var assetBar    = document.getElementById('cthv2AssetBar');
  var openLabCheck= document.getElementById('cthv2OpenLabCheck');

  // Init open lab checkbox state from localStorage
  if (openLabCheck) {
    if (localStorage.getItem('cthv2_open_lab_newtab') === 'true') {
      openLabCheck.checked = true;
    }
    openLabCheck.addEventListener('change', function() {
      localStorage.setItem('cthv2_open_lab_newtab', this.checked ? 'true' : 'false');
    });
  }

  // ─── Load VPS List & Deriv Accounts ───
  function loadVpsList() {
    vpsGrid.innerHTML = '<div class="cthv2-empty"><div class="spinner" style="width:20px;height:20px;"></div><span>กำลังโหลด...</span></div>';
    Promise.all([
      fetch(API + '?action=get_vps_list&_t=' + Date.now()).then(function(r) { return r.json(); }),
      fetch(API_DERIV_ACC + '?_t=' + Date.now()).then(function(r) { return r.json(); }).catch(function() { return { data: [] }; })
    ])
      .then(function(results) {
        var d = results[0];
        var accRes = results[1];
        if (!d.success) throw new Error(d.error || 'Failed');
        vpsList = d.data || [];
        accountsList = accRes.data || [];
        renderVpsButtons();
        loadDates();
      })
      .catch(function(e) {
        vpsGrid.innerHTML = '<div class="cthv2-empty" style="color:#f87171;">โหลด VPS ไม่สำเร็จ: ' + e.message + '</div>';
      });
  }

  function renderVpsButtons() {
    var html = '';
    // All button
    html += '<div class="cthv2-vps-btn-all' + (selectedVps === null ? ' active' : '') + '" data-vps="">';
    html += '<svg viewBox="0 0 24 24"><path d="M4 8h4V4H4v4zm6 12h4v-4h-4v4zm-6 0h4v-4H4v4zm0-6h4v-4H4v4zm6 0h4v-4h-4v4zm6-10v4h4V4h-4zm-6 4h4V4h-4v4zm6 6h4v-4h-4v4zm0 6h4v-4h-4v4z"/></svg>';
    html += '<span>All VPS</span>';
    html += '</div>';

    vpsList.forEach(function(v) {
      var hasImg = v.image && v.image.length > 10;
      var cls = 'cthv2-vps-btn' + (!hasImg ? ' cthv2-vps-btn-noimg' : '') + (selectedVps === String(v.vpsCode) ? ' active' : '');
      var style = hasImg ? 'background-image:url(' + v.image + ');' : '';
      html += '<div class="' + cls + '" style="' + style + '" data-vps="' + v.vpsCode + '">';
      html += '<div class="cthv2-vps-label">' + (v.vpsName || 'VPS #' + v.vpsCode);
      html += '<small>' + (v.cloudProvider || '') + '</small>';
      html += '</div></div>';
    });

    vpsGrid.innerHTML = html;

    // Bind clicks
    vpsGrid.querySelectorAll('[data-vps]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var code = this.dataset.vps;
        selectedVps = code === '' ? null : code;
        selectedAsset = null;
        // Update active class
        vpsGrid.querySelectorAll('[data-vps]').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        loadDates();
      });
    });
  }

  // ─── Load Dates ───
  function loadDates() {
    var url = API + '?action=get_dates&_t=' + Date.now();
    if (selectedVps) url += '&serverCode=' + encodeURIComponent(selectedVps);

    fetch(url)
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (!d.success) throw new Error(d.error || 'Failed');
        datesList = (d.data || []).map(function(x) { return x.tradeDate; });
        // Auto-select latest date if none selected
        if (!selectedDate && datesList.length > 0) {
          selectedDate = datesList[0];
          datePicker.value = selectedDate;
        }
        if (selectedDate) loadTrades();
      })
      .catch(function(e) { console.error('loadDates error:', e); });
  }

  // ─── Load Trades ───
  function loadTrades() {
    if (!selectedDate) return;
    datePicker.value = selectedDate;

    if (assetBar) assetBar.innerHTML = '';
    tbody.innerHTML = '<tr><td colspan="12"><div class="cthv2-empty"><div class="spinner" style="width:22px;height:22px;"></div><span>กำลังโหลดข้อมูล...</span></div></td></tr>';

    var url = API + '?action=get_trades_by_date&date=' + encodeURIComponent(selectedDate) + '&_t=' + Date.now();
    if (selectedVps) url += '&serverCode=' + encodeURIComponent(selectedVps);

    fetch(url)
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (!d.success) throw new Error(d.error || 'Failed');
        currentRawTrades = d.trades || [];
        currentSummary = d.summary;
        currentSource = d.source || 'vpsTradeData';
        renderAssetButtons();
        applyFilter();
      })
      .catch(function(e) {
        tbody.innerHTML = '<tr><td colspan="12"><div class="cthv2-empty" style="color:#f87171;">Error: ' + e.message + '</div></td></tr>';
      });
  }

  // ─── Render Asset Filter Buttons ───
  function renderAssetButtons() {
    if (!assetBar) return;
    if (!currentRawTrades || currentRawTrades.length === 0) {
      assetBar.innerHTML = '';
      return;
    }

    // Group assets & find max lossCon
    var assetMap = {};
    currentRawTrades.forEach(function(t) {
      var sym = (t.symbol || t.assetCode || '').trim();
      if (!sym || sym === '-') return;
      if (!assetMap[sym]) {
        assetMap[sym] = {
          symbol: sym,
          trades: [],
          maxLossCon: 0,
          serverCode: selectedVps || t.serverCode || t.vpsCode || 1
        };
      }
      assetMap[sym].trades.push(t);
      var lc = parseInt(t.lossCon) || 0;
      if (lc > assetMap[sym].maxLossCon) {
        assetMap[sym].maxLossCon = lc;
      }
    });

    var assetList = Object.keys(assetMap).sort();
    if (assetList.length === 0) {
      assetBar.innerHTML = '';
      return;
    }

    // Check if currently selected asset exists in this date's trades
    if (selectedAsset && !assetMap[selectedAsset]) {
      selectedAsset = null;
    }

    var html = '';
    // "All" button
    html += '<button type="button" class="cthv2-asset-btn' + (!selectedAsset ? ' active' : '') + '" data-asset="ALL" title="แสดงทุก Asset">';
    html += '<span>All</span>';
    html += '</button>';

    // Asset buttons: Asset Code + Max LossCon
    assetList.forEach(function(sym) {
      var info = assetMap[sym];
      var isActive = selectedAsset === sym;
      html += '<button type="button" class="cthv2-asset-btn' + (isActive ? ' active' : '') + '" data-asset="' + sym + '" data-vps="' + info.serverCode + '" data-maxloss="' + info.maxLossCon + '" title="Asset: ' + sym + ' | Max LossCon: ' + info.maxLossCon + ' | Trades: ' + info.trades.length + '">';
      html += '<span>' + sym + '</span>';
      html += '<span class="cthv2-losscon-tag" title="Max Loss Consecutive: ' + info.maxLossCon + '">' + info.maxLossCon + '</span>';
      html += '</button>';
    });

    assetBar.innerHTML = html;

    // Attach click events
    assetBar.querySelectorAll('.cthv2-asset-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var asset = this.dataset.asset;
        var vps = this.dataset.vps || selectedVps || 1;

        if (asset === 'ALL') {
          selectedAsset = null;
        } else {
          selectedAsset = asset;
          // If checkbox is checked, open new tab to ResistanceLab
          if (openLabCheck && openLabCheck.checked) {
            var labUrl = '../resistanceLab/lab1.html?vps=' + encodeURIComponent(vps) +
                         '&assetcode=' + encodeURIComponent(asset) +
                         '&daytrade=' + encodeURIComponent(selectedDate);
            window.open(labUrl, '_blank');
          }
        }

        // Update active class on buttons
        assetBar.querySelectorAll('.cthv2-asset-btn').forEach(function(b) {
          b.classList.toggle('active', (selectedAsset ? b.dataset.asset === selectedAsset : b.dataset.asset === 'ALL'));
        });

        applyFilter();
      });
    });
  }

  // ─── Filter & Render ───
  function applyFilter() {
    var filtered = currentRawTrades;
    var srcTag = currentSource === 'derivTradeHistory' ? ' • Deriv' : '';
    if (selectedAsset) {
      filtered = currentRawTrades.filter(function(t) {
        return (t.symbol || t.assetCode || '').trim() === selectedAsset;
      });
      renderKpis(computeSummary(filtered));
      tradeCountBadge.textContent = filtered.length + ' / ' + currentRawTrades.length + ' trades (' + selectedAsset + ')' + srcTag;
    } else {
      if (currentSummary) {
        renderKpis(currentSummary);
      } else {
        renderKpis(computeSummary(currentRawTrades));
      }
      tradeCountBadge.textContent = currentRawTrades.length + ' trades' + srcTag;
    }
    renderTable(filtered);
  }

  function computeSummary(trades) {
    var win = 0, loss = 0, skip = 0, profit = 0;
    trades.forEach(function(t) {
      var ws = (t.WinStatus || '').toLowerCase();
      if (ws === 'win') win++;
      else if (ws === 'loss') loss++;
      else skip++;
      profit += parseFloat(t.ThisProfit) || 0;
    });
    var total = trades.length;
    var counted = total - skip;
    var winRate = counted > 0 ? (win / counted * 100).toFixed(1) : '0.0';
    return {
      total: total,
      win: win,
      loss: loss,
      skip: skip,
      profit: profit,
      winRate: winRate
    };
  }

  function renderKpis(s) {
    kpiTotal.textContent = s.total;
    kpiWinRate.textContent = s.winRate + '%';
    kpiWinRate.style.color = s.winRate >= 55 ? '#34d399' : s.winRate >= 45 ? '#fbbf24' : '#f87171';
    kpiProfit.textContent = '$' + s.profit.toFixed(2);
    kpiProfit.style.color = s.profit >= 0 ? '#34d399' : '#f87171';
    kpiProfitIcon.style.background = s.profit >= 0 ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#ef4444,#dc2626)';
    kpiWL.innerHTML = '<span style="color:#34d399">' + s.win + 'W</span> / <span style="color:#f87171">' + s.loss + 'L</span>';
    if (s.skip > 0) kpiWL.innerHTML += ' / <span style="color:#94a3b8">' + s.skip + 'S</span>';
  }

  function renderTable(trades) {
    if (trades.length === 0) {
      tbody.innerHTML = '<tr><td colspan="12"><div class="cthv2-empty"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg><p>ไม่มีข้อมูลเทรดในวันที่เลือก</p></div></td></tr>';
      return;
    }

    var html = '';
    trades.forEach(function(t, i) {
      var ws = (t.WinStatus || '').toLowerCase();
      var wsCls = ws === 'win' ? 'cthv2-win' : ws === 'loss' ? 'cthv2-loss' : 'cthv2-skip';
      var actionCls = (t.thisAction || '').toLowerCase() === 'idle' ? 'cthv2-idle' : '';
      var epoch = parseInt(t.purchaseTime) || parseInt(t.timeCandle) || 0;
      var timeStr = t.timeCandleDisplay || (epoch > 0 ? new Date(epoch * 1000).toLocaleTimeString('th-TH', {hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '-');

      html += '<tr>';
      html += '<td>' + (i+1) + '</td>';
      html += '<td>' + (t.vpsName || t.serverCode || '-') + '</td>';
      html += '<td>' + timeStr + '</td>';
      html += '<td>' + (t.symbol || t.assetCode || '-') + '</td>';
      html += '<td class="' + actionCls + '">' + (t.thisAction || '-') + '</td>';
      html += '<td>' + (t.thisColor || '-') + '</td>';
      html += '<td class="' + wsCls + '">' + (t.WinStatus || '-') + '</td>';
      html += '<td>' + (parseFloat(t.MoneyTrade) || 0).toFixed(2) + '</td>';
      html += '<td class="' + (parseFloat(t.ThisProfit) >= 0 ? 'cthv2-win' : 'cthv2-loss') + '">' + (parseFloat(t.ThisProfit) || 0).toFixed(2) + '</td>';
      html += '<td>' + (parseFloat(t.GrandBalance) || 0).toFixed(2) + '</td>';
      html += '<td>' + (t.lossCon || 0) + '</td>';
      html += '<td>' + (t.tradeStrategy || t.codeStrategy || '-') + '</td>';
      html += '</tr>';
    });
    tbody.innerHTML = html;
  }

  // ─── Date Navigation ───
  function todayStr() {
    var d = new Date(); return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
  }
  function shiftDate(dateStr, days) {
    var d = new Date(dateStr + 'T00:00:00');
    d.setDate(d.getDate() + days);
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
  }
  function findNearestDate(dir) {
    if (datesList.length === 0) return null;
    var sorted = datesList.slice().sort();
    if (dir === -1) {
      for (var i = sorted.length - 1; i >= 0; i--) {
        if (sorted[i] < selectedDate) return sorted[i];
      }
    } else {
      for (var j = 0; j < sorted.length; j++) {
        if (sorted[j] > selectedDate) return sorted[j];
      }
    }
    return null;
  }

  datePicker.addEventListener('change', function() {
    selectedDate = this.value;
    selectedAsset = null;
    loadTrades();
  });
  btnToday.addEventListener('click', function() { selectedDate = todayStr(); selectedAsset = null; loadTrades(); });
  btnYesterday.addEventListener('click', function() { selectedDate = shiftDate(todayStr(), -1); selectedAsset = null; loadTrades(); });
  btnPrev.addEventListener('click', function() {
    var d = findNearestDate(-1);
    if (d) { selectedDate = d; }
    else { selectedDate = shiftDate(selectedDate || todayStr(), -1); }
    selectedAsset = null;
    loadTrades();
  });
  btnNext.addEventListener('click', function() {
    var d = findNearestDate(1);
    if (d) { selectedDate = d; }
    else { selectedDate = shiftDate(selectedDate || todayStr(), 1); }
    selectedAsset = null;
    loadTrades();
  });
  btnRefresh.addEventListener('click', function() { loadDates(); });

  // ─── Deriv Fetch & Save to MySQL (Matches ldFetchAndSaveBtn) ───
  function setDerivStatus(msg, color) {
    if (!derivStatus) return;
    derivStatus.textContent = msg || '';
    if (color) derivStatus.style.color = color;
  }

  async function fetchDerivTradesForAccount(vpsObj, accObj, dateVal) {
    var appId = (accObj.appId || '').trim();
    var accountId = (accObj.accountId || '').trim();
    var token = (accObj.token || '').trim();

    if (!appId || !accountId || !token) {
      throw new Error('บัญชี ' + (accObj.accountId || accObj.id) + ' มีข้อมูล App ID / Token ไม่ครบ');
    }

    var startEpoch = Math.floor(new Date(dateVal + 'T00:00:00+07:00').getTime() / 1000);
    var endEpoch   = Math.floor(new Date(dateVal + 'T23:59:59+07:00').getTime() / 1000);

    setDerivStatus('[VPS ' + vpsObj.vpsCode + '] ขอ OTP จาก Deriv...', '#fbbf24');

    // 1. Request OTP via REST
    var otpRes = await fetch('https://api.derivws.com/trading/v1/options/accounts/' + accountId + '/otp', {
      method: 'POST',
      headers: {
        'Deriv-App-ID': appId,
        'Authorization': 'Bearer ' + token
      }
    });

    var otpJson = await otpRes.json();
    if (!otpRes.ok || !otpJson.data || !otpJson.data.url) {
      var errText = otpJson.error ? (otpJson.error.message || JSON.stringify(otpJson.error)) : (otpJson.message || 'ขอ OTP ไม่สำเร็จ');
      throw new Error('OTP Error (' + accountId + '): ' + errText);
    }

    var wsUrl = otpJson.data.url;
    setDerivStatus('[VPS ' + vpsObj.vpsCode + '] เปิด WebSocket ดึง profit_table...', '#38bdf8');

    // 2. Open WebSocket & Paginate profit_table
    return new Promise(function(resolve, reject) {
      var allTrades = [];
      var offset = 0;
      var LIMIT = 100;
      var ws = null;

      try {
        ws = new WebSocket(wsUrl);
      } catch (wsErr) {
        return reject(wsErr);
      }

      function sendReq() {
        var req = {
          profit_table: 1,
          description: 1,
          limit: LIMIT,
          offset: offset,
          date_from: startEpoch,
          date_to: endEpoch,
          sort: 'ASC'
        };
        ws.send(JSON.stringify(req));
      }

      ws.onopen = function() {
        sendReq();
      };

      ws.onmessage = function(event) {
        var data;
        try {
          data = JSON.parse(event.data);
        } catch (e) {
          return;
        }

        if (data.error) {
          var errMsg = data.error.message || data.error.code || 'API Error';
          ws.close();
          return reject(new Error('Deriv Error: ' + errMsg));
        }

        if (data.msg_type === 'profit_table') {
          const pt = data.profit_table;
          const txns = (pt && pt.transactions) ? pt.transactions : [];

          for (var i = 0; i < txns.length; i++) {
            allTrades.push(txns[i]);
          }

          setDerivStatus('[VPS ' + vpsObj.vpsCode + '] ได้รับ ' + txns.length + ' รายการ (สะสม ' + allTrades.length + ')...', '#38bdf8');

          if (txns.length >= LIMIT) {
            offset += LIMIT;
            sendReq();
          } else {
            ws.close();
            resolve(allTrades);
          }
        }
      };

      ws.onerror = function() {
        reject(new Error('WebSocket connection error'));
      };
    });
  }

  async function handleLoadTradeFromDeriv() {
    if (!selectedDate) {
      alert('กรุณาเลือกวันที่ต้องการดึงข้อมูลก่อน');
      return;
    }

    // Determine target VPS list
    var targetVpsList = [];
    if (selectedVps) {
      var matched = vpsList.find(function(v) {
        return String(v.vpsCode) === String(selectedVps) || String(v.id) === String(selectedVps);
      });
      if (matched) targetVpsList.push(matched);
    } else {
      // All VPS: get all VPS with linked Deriv account
      targetVpsList = vpsList.filter(function(v) {
        return v.DerivAccountID && accountsList.some(function(a) { return String(a.id) === String(v.DerivAccountID); });
      });
    }

    if (targetVpsList.length === 0) {
      alert('ไม่พบข้อมูล VPS ที่ผูก Deriv Account ไว้ กรุณาตรวจสอบการตั้งค่า Deriv Account / VPS Master');
      return;
    }

    btnLoadDeriv.disabled = true;
    if (btnLoadDerivText) btnLoadDerivText.textContent = 'กำลังดึงจาก Deriv...';

    var totalSaved = 0;
    var totalFetched = 0;
    var errors = [];

    try {
      for (var i = 0; i < targetVpsList.length; i++) {
        var vps = targetVpsList[i];
        var acc = accountsList.find(function(a) { return String(a.id) === String(vps.DerivAccountID); });
        if (!acc) continue;

        try {
          var trades = await fetchDerivTradesForAccount(vps, acc, selectedDate);
          totalFetched += trades.length;

          if (trades.length > 0) {
            setDerivStatus('[VPS ' + vps.vpsCode + '] กำลังบันทึก ' + trades.length + ' รายการลง MySQL...', '#fbbf24');
            var serverCode = parseInt(vps.vpsCode, 10) || parseInt(vps.id, 10) || 1;
            var saveRes = await fetch(API_SAVE_TRADE_HIST, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                serverCode: serverCode,
                trades: trades
              })
            });
            var saveJson = await saveRes.json();
            if (saveJson.success) {
              totalSaved += (saveJson.count || trades.length);
            } else {
              errors.push('VPS ' + vps.vpsCode + ': ' + (saveJson.error || 'บันทึกไม่สำเร็จ'));
            }
          }
        } catch (fetchErr) {
          errors.push('VPS ' + vps.vpsCode + ': ' + fetchErr.message);
        }
      }

      if (errors.length > 0) {
        setDerivStatus('⚠️ ' + errors.join('; '), '#f87171');
        alert('ผลการดึงข้อมูลจาก Deriv:\n' + (totalSaved > 0 ? 'บันทึกสำเร็จ: ' + totalSaved + ' รายการ\n' : '') + 'ข้อผิดพลาด:\n' + errors.join('\n'));
      } else {
        setDerivStatus('✅ บันทึกลง MySQL เรียบร้อย (' + totalSaved + ' รายการ)', '#34d399');
        alert('🎉 ดึงข้อมูลจาก Deriv และบันทึกลง MySQL ตาราง derivTradeHistory สำเร็จเรียบร้อย!\nจำนวน: ' + totalSaved + ' รายการ (วันที่ ' + selectedDate + ')');
      }

      // Reload dates and trades
      loadDates();

      setTimeout(function() {
        setDerivStatus('', '#fbbf24');
      }, 7000);

    } catch (err) {
      setDerivStatus('❌ Error: ' + err.message, '#f87171');
      alert('เกิดข้อผิดพลาด: ' + err.message);
    } finally {
      btnLoadDeriv.disabled = false;
      if (btnLoadDerivText) btnLoadDerivText.textContent = 'Load Trade from Deriv';
    }
  }

  if (btnLoadDeriv) {
    btnLoadDeriv.addEventListener('click', handleLoadTradeFromDeriv);
  }

  // ─── Init ───
  loadVpsList();
})();
</script>

