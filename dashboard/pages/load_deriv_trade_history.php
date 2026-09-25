<?php
/**
 * Load Deriv Trade History Page
 * Vision UI Dark Glassmorphism Design
 * 
 * Features:
 * - Select VPS Server from vpsMaster
 * - Select Deriv Account (auto-linked by VPS, or manual choice from derivAccount)
 * - Select Trade Date (with options for specific single day or date_from onwards)
 * - Request OTP from Deriv REST API (https://api.derivws.com/trading/v1/options/accounts/{accountId}/otp)
 * - Connect to Private WebSocket and paginate profit_table
 * - Save into MySQL table derivTradeHistory via ../php/save_trade_history.php
 * - Real-time Execution Console & KPI Summary Cards
 * - Interactive Table with Symbol filtering and Win/Loss streak breakdown
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-load-deriv-trade-history">
  <style>
    /* Scoped Styles for Load Deriv Trade History */
    .ld-control-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 24px;
      margin-bottom: 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }
    .ld-control-grid {
      display: grid;
      grid-template-columns: 1.3fr 1.3fr 1.1fr auto;
      gap: 16px;
      align-items: flex-end;
    }
    @media (max-width: 992px) {
      .ld-control-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
    @media (max-width: 576px) {
      .ld-control-grid {
        grid-template-columns: 1fr;
      }
    }
    .ld-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 8px;
    }
    .ld-select, .ld-input {
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
    .ld-select:focus, .ld-input:focus {
      border-color: #a855f7;
      box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.25);
      outline: none;
      background: rgba(15, 28, 70, 0.95);
    }
    .ld-select option {
      background: #0f172a;
      color: #fff;
    }
    .ld-btn-group {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }
    .ld-btn-fetch-save {
      background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
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
      box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35);
      white-space: nowrap;
    }
    .ld-btn-fetch-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(139, 92, 246, 0.45);
    }
    .ld-btn-fetch-save:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    .ld-btn-preview {
      background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      padding: 10px 18px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
      white-space: nowrap;
    }
    .ld-btn-preview:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(6, 182, 212, 0.45);
    }
    .ld-btn-preview:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    .ld-btn-save-only {
      background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      padding: 10px 16px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
      white-space: nowrap;
    }
    .ld-btn-save-only:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
    .ld-meta-preview {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-top: 16px;
      padding: 12px 16px;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: var(--radius-sm);
      font-size: 12px;
      color: var(--text-secondary);
      flex-wrap: wrap;
    }
    .ld-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 11.5px;
      font-weight: 600;
    }
    .ld-badge-app {
      background: rgba(168, 85, 247, 0.15);
      color: #c084fc;
      border: 1px solid rgba(168, 85, 247, 0.35);
    }
    .ld-badge-acc {
      background: rgba(59, 130, 246, 0.15);
      color: #60a5fa;
      border: 1px solid rgba(59, 130, 246, 0.35);
    }
    .ld-badge-status {
      background: rgba(34, 197, 94, 0.15);
      color: #4ade80;
      border: 1px solid rgba(34, 197, 94, 0.35);
    }
    .ld-badge-vps {
      background: rgba(0, 212, 255, 0.15);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.35);
    }

    /* KPI Stats Grid */
    .ld-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .ld-stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 18px 20px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .ld-stat-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }
    .ld-stat-val {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
    }
    .ld-stat-sub {
      font-size: 11px;
      color: var(--text-tertiary);
      margin-top: 3px;
    }
    .ld-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .ld-stat-icon svg {
      width: 22px;
      height: 22px;
      fill: #fff;
    }

    /* Console Terminal Box */
    .ld-console-box {
      background: #090d16;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-md);
      padding: 16px;
      margin-bottom: 24px;
      box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6);
      font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
    }
    .ld-console-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      padding-bottom: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 12px;
      font-weight: 700;
      color: var(--text-secondary);
    }
    #ldLogArea {
      max-height: 220px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-size: 12px;
      line-height: 1.5;
    }
    .ld-log-entry {
      padding: 4px 8px;
      border-radius: 4px;
      word-break: break-all;
    }
    .ld-log-info { color: #94a3b8; }
    .ld-log-success { color: #34d399; background: rgba(52, 211, 153, 0.08); }
    .ld-log-warn { color: #fbbf24; background: rgba(251, 191, 36, 0.08); }
    .ld-log-error { color: #f87171; background: rgba(248, 113, 113, 0.1); font-weight: 600; }
    .ld-log-highlight { color: #c084fc; font-weight: 600; }

    /* Results Table Card */
    .ld-table-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }
    .ld-table-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .ld-asset-filter-bar {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 16px;
    }
    .ld-filter-btn {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-secondary);
      padding: 4px 12px;
      border-radius: 14px;
      font-size: 11.5px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .ld-filter-btn:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
    }
    .ld-filter-btn.active {
      background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
      color: #fff;
      border-color: transparent;
      box-shadow: 0 2px 8px rgba(168, 85, 247, 0.4);
    }
    .ld-table-wrap {
      overflow-x: auto;
      max-height: 480px;
    }
    .ld-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12.5px;
      white-space: nowrap;
    }
    .ld-table th {
      background: rgba(15, 23, 42, 0.8);
      color: var(--text-secondary);
      font-weight: 600;
      padding: 10px 14px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      position: sticky;
      top: 0;
      z-index: 2;
    }
    .ld-table td {
      padding: 10px 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: #e2e8f0;
    }
    .ld-table tr:hover td {
      background: rgba(255, 255, 255, 0.03);
    }
    .badge-win {
      background: rgba(52, 211, 153, 0.15);
      color: #34d399;
      border: 1px solid rgba(52, 211, 153, 0.35);
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: 700;
      font-size: 11px;
    }
    .badge-loss {
      background: rgba(248, 113, 113, 0.15);
      color: #f87171;
      border: 1px solid rgba(248, 113, 113, 0.35);
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: 700;
      font-size: 11px;
    }
    .badge-call {
      background: rgba(16, 185, 129, 0.15);
      color: #10b981;
      border: 1px solid rgba(16, 185, 129, 0.3);
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 10.5px;
      font-weight: 700;
    }
    .badge-put {
      background: rgba(239, 68, 68, 0.15);
      color: #ef4444;
      border: 1px solid rgba(239, 68, 68, 0.3);
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 10.5px;
      font-weight: 700;
    }
  </style>

  <!-- 1. Control & Setup Card -->
  <div class="ld-control-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
      <div>
        <h3 style="margin:0 0 4px 0; font-size:18px; font-weight:700; color:#fff; display:flex; align-items:center; gap:8px;">
          <span>📥</span> Fetch Deriv Trade History to MySQL
        </h3>
        <p style="margin:0; font-size:12.5px; color:var(--text-tertiary);">
          เลือก VPS Server และ Deriv Account ที่ผูกไว้, ระบุวันที่เทรด แล้วดึงประวัติการเทรด (profit_table) จาก Deriv.com บันทึกลงตาราง <code style="color:#38bdf8;">derivTradeHistory</code>
        </p>
      </div>
      <div style="display:flex; gap:8px; align-items:center;">
        <span id="ldConnectionBadge" class="ld-badge" style="background:rgba(255,255,255,0.06); color:var(--text-tertiary); border:1px solid rgba(255,255,255,0.1);">
          ⚪ Standby
        </span>
      </div>
    </div>

    <div class="ld-control-grid">
      <!-- 1.1 VPS Server Selection -->
      <div>
        <label class="ld-label" for="ldVpsSelect">🖥️ 1. เลือก VPS Server</label>
        <select class="ld-select" id="ldVpsSelect">
          <option value="">-- กำลังโหลดรายการ VPS... --</option>
        </select>
      </div>

      <!-- 1.2 Deriv Account Selection -->
      <div>
        <label class="ld-label" for="ldAccountSelect">🔑 2. เลือก Deriv Account</label>
        <select class="ld-select" id="ldAccountSelect">
          <option value="">-- กำลังโหลด Account... --</option>
        </select>
      </div>

      <!-- 1.3 Trade Date Selection -->
      <div>
        <label class="ld-label" for="ldTradeDate">📅 3. วันที่ทำการเทรด (Trade Date)</label>
        <div style="display:flex; gap:6px;">
          <input type="date" class="ld-input" id="ldTradeDate" style="cursor:pointer;">
          <button type="button" class="ld-filter-btn" id="ldBtnToday" title="เลือกวันนี้">วันนี้</button>
          <button type="button" class="ld-filter-btn" id="ldBtnYesterday" title="เลือกเมื่อวาน">เมื่อวาน</button>
        </div>
        <div style="margin-top:6px; display:flex; align-items:center; gap:6px; font-size:11.5px; color:var(--text-tertiary);">
          <input type="checkbox" id="ldSpecificDateOnly" checked style="cursor:pointer;">
          <label for="ldSpecificDateOnly" style="cursor:pointer;" title="หากเลือก จะดึงเฉพาะช่วงเวลา 00:00:00 - 23:59:59 ของวันที่เลือกเท่านั้น">
            ดึงเฉพาะวันที่นี้ (00:00 - 23:59)
          </label>
        </div>
      </div>

      <!-- 1.4 Action Buttons -->
      <div class="ld-btn-group">
        <button type="button" class="ld-btn-fetch-save" id="ldFetchAndSaveBtn" title="ขอ OTP แล้วดึง profit_table จาก Deriv และบันทึกลง MySQL ทันที">
          <span>📥💾</span>
          <span id="ldBtnText">Fetch &amp; Save to DB</span>
        </button>
        <button type="button" class="ld-btn-preview" id="ldPreviewBtn" title="ดึงข้อมูลมาแสดงตัวอย่างบนตารางก่อนโดยยังไม่บันทึก">
          <span>🔍</span> Preview Only
        </button>
        <button type="button" class="ld-btn-save-only" id="ldSaveOnlyBtn" title="บันทึกข้อมูลที่โหลดไว้เข้า MySQL derivTradeHistory" disabled>
          <span>💾</span> Save to DB
        </button>
      </div>
    </div>

    <!-- 1.5 Account Info Preview Bar -->
    <div class="ld-meta-preview" id="ldAccountMetaPreview">
      <span style="color:var(--text-tertiary);">ข้อมูลบัญชีที่เลือก:</span>
      <span class="ld-badge ld-badge-vps" id="metaVpsBadge">🖥️ VPS: -</span>
      <span class="ld-badge ld-badge-acc" id="metaAccBadge">👤 Account ID: -</span>
      <span class="ld-badge ld-badge-app" id="metaAppBadge">🆔 App ID: -</span>
      <span class="ld-badge" id="metaTokenBadge" style="background:rgba(236,72,153,0.15); color:#f472b6; border:1px solid rgba(236,72,153,0.35); font-family:monospace;">
        🔑 Token: <span id="metaTokenText">••••••••</span>
      </span>
      <span class="ld-badge ld-badge-status" id="metaStatusBadge">🟢 Status: -</span>
    </div>
  </div>

  <!-- 2. KPI Summary Cards -->
  <div class="ld-stats-grid">
    <div class="ld-stat-card">
      <div>
        <div class="ld-stat-label">📊 TOTAL TRADES</div>
        <div class="ld-stat-val" id="statTotalTrades">0</div>
        <div class="ld-stat-sub" id="statSubTrades">0 transactions</div>
      </div>
      <div class="ld-stat-icon" style="background:linear-gradient(135deg, #3b82f6, #06b6d4);">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
      </div>
    </div>

    <div class="ld-stat-card">
      <div>
        <div class="ld-stat-label">🎯 WIN RATE &amp; W/L</div>
        <div class="ld-stat-val" id="statWinRate">0.0%</div>
        <div class="ld-stat-sub" id="statWinLossSub" style="color:#34d399;">0W / 0L</div>
      </div>
      <div class="ld-stat-icon" style="background:linear-gradient(135deg, #10b981, #059669);">
        <svg viewBox="0 0 24 24"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg>
      </div>
    </div>

    <div class="ld-stat-card">
      <div>
        <div class="ld-stat-label">💰 NET PROFIT (USD)</div>
        <div class="ld-stat-val" id="statNetProfit">$0.00</div>
        <div class="ld-stat-sub" id="statTotalPayout">Payout: $0.00</div>
      </div>
      <div class="ld-stat-icon" style="background:linear-gradient(135deg, #f59e0b, #d97706);">
        <svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
      </div>
    </div>

    <div class="ld-stat-card">
      <div>
        <div class="ld-stat-label">⚡ MAX STREAKS</div>
        <div class="ld-stat-val" id="statMaxLossCon" style="color:#f87171;">LC: 0</div>
        <div class="ld-stat-sub" id="statMaxWinCon" style="color:#34d399;">Max WinCon: 0</div>
      </div>
      <div class="ld-stat-icon" style="background:linear-gradient(135deg, #ef4444, #dc2626);">
        <svg viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg>
      </div>
    </div>

    <div class="ld-stat-card">
      <div>
        <div class="ld-stat-label">🌐 ASSETS TRADED</div>
        <div class="ld-stat-val" id="statTotalAssets">0</div>
        <div class="ld-stat-sub" id="statAssetsList" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;">-</div>
      </div>
      <div class="ld-stat-icon" style="background:linear-gradient(135deg, #8b5cf6, #ec4899);">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
      </div>
    </div>
  </div>

  <!-- 3. Terminal Live Execution Console -->
  <div class="ld-console-box" id="ldConsoleBox">
    <div class="ld-console-header">
      <div style="display:flex; align-items:center; gap:8px;">
        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
        <span>💻 LIVE EXECUTION CONSOLE</span>
      </div>
      <span style="font-size:11px; cursor:pointer; color:#38bdf8; text-decoration:underline;" onclick="document.getElementById('ldLogArea').innerHTML=''">Clear</span>
    </div>
    <div id="ldLogArea">
      <div class="ld-log-entry ld-log-info">✨ พร้อมสำหรับการดึงข้อมูล Trade History จาก Deriv.com... กรุณาเลือก VPS/Account และวันที่ แล้วกดปุ่ม "Fetch &amp; Save to DB"</div>
    </div>
  </div>

  <!-- 4. Trade History Table Card -->
  <div class="ld-table-card">
    <div class="ld-table-card-header">
      <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <h4 style="margin:0; font-size:15px; font-weight:700; color:#fff; display:flex; align-items:center; gap:8px;">
          <span>📋</span> รายการประวัติการเทรดที่ดึงมา (Fetched Trades)
        </h4>
        <span id="ldLoadedCountBadge" class="ld-badge ld-badge-app" style="display:none;">0 trades</span>
      </div>
      <div style="display:flex; gap:8px; align-items:center;">
        <button type="button" class="ld-filter-btn active" id="filterOutcomeAll" data-outcome="all">ทั้งหมด</button>
        <button type="button" class="ld-filter-btn" id="filterOutcomeWin" data-outcome="win">เฉพาะชนะ (Wins)</button>
        <button type="button" class="ld-filter-btn" id="filterOutcomeLoss" data-outcome="loss">เฉพาะแพ้ (Losses)</button>
      </div>
    </div>

    <!-- Asset filter buttons bar -->
    <div class="ld-asset-filter-bar" id="ldAssetFilterBar" style="display:none;">
      <span style="font-size:12px; font-weight:600; color:var(--text-secondary);">กรองตามคู่เหรียญ:</span>
      <!-- Populated dynamically -->
    </div>

    <div class="ld-table-wrap">
      <table class="ld-table" id="ldTradesTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Tx ID</th>
            <th>Buy Time (TH)</th>
            <th>Sell Time (TH)</th>
            <th>Duration</th>
            <th>Asset</th>
            <th>Action</th>
            <th>Buy Price ($)</th>
            <th>Sell Price ($)</th>
            <th>P/L ($)</th>
            <th>Outcome</th>
            <th>LossCon</th>
            <th>WinCon</th>
            <th>Contract ID</th>
          </tr>
        </thead>
        <tbody id="ldTradesTbody">
          <tr>
            <td colspan="14" style="text-align:center; padding:36px; color:var(--text-tertiary);">
              ยังไม่มีข้อมูลการเทรด — กรุณากดปุ่ม <b>"Fetch &amp; Save to DB"</b> หรือ <b>"Preview Only"</b>
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
  const API_DERIV_ACC_URL = '../php/api_deriv_account.php';
  const SAVE_TRADE_HISTORY_URL = '../php/save_trade_history.php';

  // DOM Elements
  const vpsSelect = document.getElementById('ldVpsSelect');
  const accountSelect = document.getElementById('ldAccountSelect');
  const tradeDateInput = document.getElementById('ldTradeDate');
  const specificDateCheckbox = document.getElementById('ldSpecificDateOnly');
  const btnToday = document.getElementById('ldBtnToday');
  const btnYesterday = document.getElementById('ldBtnYesterday');
  const fetchAndSaveBtn = document.getElementById('ldFetchAndSaveBtn');
  const previewBtn = document.getElementById('ldPreviewBtn');
  const saveOnlyBtn = document.getElementById('ldSaveOnlyBtn');
  const btnText = document.getElementById('ldBtnText');
  const connBadge = document.getElementById('ldConnectionBadge');
  const logArea = document.getElementById('ldLogArea');

  // Meta Elements
  const metaVpsBadge = document.getElementById('metaVpsBadge');
  const metaAccBadge = document.getElementById('metaAccBadge');
  const metaAppBadge = document.getElementById('metaAppBadge');
  const metaTokenBadge = document.getElementById('metaTokenBadge');
  const metaTokenText = document.getElementById('metaTokenText');
  const metaStatusBadge = document.getElementById('metaStatusBadge');

  // KPI Stat Elements
  const statTotalTrades = document.getElementById('statTotalTrades');
  const statSubTrades = document.getElementById('statSubTrades');
  const statWinRate = document.getElementById('statWinRate');
  const statWinLossSub = document.getElementById('statWinLossSub');
  const statNetProfit = document.getElementById('statNetProfit');
  const statTotalPayout = document.getElementById('statTotalPayout');
  const statMaxLossCon = document.getElementById('statMaxLossCon');
  const statMaxWinCon = document.getElementById('statMaxWinCon');
  const statTotalAssets = document.getElementById('statTotalAssets');
  const statAssetsList = document.getElementById('statAssetsList');

  // Table Elements
  const tradesTbody = document.getElementById('ldTradesTbody');
  const loadedCountBadge = document.getElementById('ldLoadedCountBadge');
  const assetFilterBar = document.getElementById('ldAssetFilterBar');

  // State
  let vpsList = [];
  let accountsList = [];
  let selectedVps = null;
  let selectedAccount = null;
  let allFetchedTrades = [];
  let currentAssetFilter = null;
  let currentOutcomeFilter = 'all';

  // Helper: Append log
  function appendLog(msg, type = 'info') {
    const timeStr = new Date().toLocaleTimeString('th-TH');
    const entry = document.createElement('div');
    entry.className = `ld-log-entry ld-log-${type}`;
    entry.textContent = `[${timeStr}] ${msg}`;
    logArea.appendChild(entry);
    logArea.scrollTop = logArea.scrollHeight;
  }

  // Set today's date initially
  function setDateToToday() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    tradeDateInput.value = `${y}-${m}-${d}`;
  }

  function setDateToYesterday() {
    const now = new Date();
    now.setDate(now.getDate() - 1);
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    tradeDateInput.value = `${y}-${m}-${d}`;
  }

  setDateToToday();
  btnToday.addEventListener('click', setDateToToday);
  btnYesterday.addEventListener('click', setDateToYesterday);

  // Initialize: Load VPS list & Deriv Accounts list
  async function initData() {
    try {
      appendLog('🔄 กำลังโหลดรายการ VPS Servers และ Deriv Accounts...', 'info');

      const [vpsRes, accRes] = await Promise.all([
        fetch(API_VPS_URL + '?action=list').then(r => r.json()).catch(() => ({ data: [] })),
        fetch(API_DERIV_ACC_URL).then(r => r.json()).catch(() => ({ data: [] }))
      ]);

      vpsList = vpsRes.data || [];
      accountsList = accRes.data || [];

      // เรียงลำดับ VPS ตาม 1, 2, 3, 4 (vpsCode ASC)
      vpsList.sort((a, b) => {
        const codeA = parseInt(a.vpsCode, 10);
        const codeB = parseInt(b.vpsCode, 10);
        if (!isNaN(codeA) && !isNaN(codeB)) {
          return codeA - codeB;
        }
        return (a.vpsCode || '').localeCompare(b.vpsCode || '') || (a.id - b.id);
      });

      // เรียงลำดับ Account ตาม 1, 2, 3, 4 (AppNo หรือ ID ASC)
      accountsList.sort((a, b) => {
        const noA = parseInt(a.AppNo, 10);
        const noB = parseInt(b.AppNo, 10);
        if (!isNaN(noA) && !isNaN(noB)) {
          return noA - noB;
        }
        return (parseInt(a.id, 10) || 0) - (parseInt(b.id, 10) || 0);
      });

      // Populate VPS Dropdown
      vpsSelect.innerHTML = '<option value="">-- เลือก VPS Server --</option>';
      vpsList.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v.id;
        const linkText = v.DerivAccountID ? ` [Acc #${v.DerivAccountID}]` : '';
        opt.textContent = `${v.vpsCode} - ${v.vpsName} (${v.publicIP || 'No IP'})${linkText}`;
        vpsSelect.appendChild(opt);
      });

      // Populate Account Dropdown
      accountSelect.innerHTML = '<option value="">-- เลือก Deriv Account --</option>';
      accountsList.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a.id;
        const appNoPrefix = a.AppNo ? `#${a.AppNo} (ID:${a.id})` : `#${a.id}`;
        opt.textContent = `${appNoPrefix} ${a.tokenName || a.appName || 'Account'} (${a.accountId || 'No ID'}) - App: ${a.appId || 'No AppID'}`;
        accountSelect.appendChild(opt);
      });

      appendLog(`✅ โหลดข้อมูลสำเร็จ: พบ ${vpsList.length} VPS Servers และ ${accountsList.length} Deriv Accounts`, 'success');

      // Auto-select first VPS if available
      if (vpsList.length > 0) {
        vpsSelect.selectedIndex = 1;
        onVpsChange();
      }
    } catch (err) {
      appendLog(`❌ เกิดข้อผิดพลาดในการโหลดข้อมูล: ${err.message}`, 'error');
    }
  }

  // When VPS dropdown changes -> find linked DerivAccountID & auto-select
  function onVpsChange() {
    const vpsId = parseInt(vpsSelect.value, 10);
    selectedVps = vpsList.find(v => v.id === vpsId) || null;

    if (!selectedVps) {
      metaVpsBadge.textContent = '🖥️ VPS: -';
      return;
    }

    metaVpsBadge.textContent = `🖥️ VPS: ${selectedVps.vpsCode} (${selectedVps.vpsName})`;
    appendLog(`🖥️ เลือก VPS: [${selectedVps.vpsCode}] ${selectedVps.vpsName}`, 'info');

    // Auto-select linked Deriv Account
    if (selectedVps.DerivAccountID) {
      accountSelect.value = selectedVps.DerivAccountID;
      appendLog(`🔗 พบ Account ที่เชื่อมโยงกับ VPS นี้ (ID: ${selectedVps.DerivAccountID}) -> ทำการเลือกให้อัตโนมัติ`, 'highlight');
    }
    onAccountChange();
  }

  // When Account dropdown changes -> update preview card
  function onAccountChange() {
    const accId = parseInt(accountSelect.value, 10);
    selectedAccount = accountsList.find(a => a.id === accId) || null;

    if (!selectedAccount) {
      metaAccBadge.textContent = '👤 Account ID: -';
      metaAppBadge.textContent = '🆔 App ID: -';
      metaTokenText.textContent = '••••••••';
      metaStatusBadge.textContent = '⚪ Status: -';
      return;
    }

    metaAccBadge.textContent = `👤 Account ID: ${selectedAccount.accountId || '-'}`;
    metaAppBadge.textContent = `🆔 App ID: ${selectedAccount.appId || '-'}`;

    // Masked token preview
    const t = selectedAccount.token || '';
    if (t.length > 12) {
      metaTokenText.textContent = t.substring(0, 8) + '...' + t.substring(t.length - 6);
    } else {
      metaTokenText.textContent = t ? '••••••••' : '(ว่าง)';
    }

    const st = selectedAccount.status || 'active';
    metaStatusBadge.textContent = st.toLowerCase() === 'active' ? '🟢 Active' : `⚠️ ${st}`;
    metaStatusBadge.style.color = st.toLowerCase() === 'active' ? '#4ade80' : '#fbbf24';

    appendLog(`🔑 กำหนดบัญชี Deriv: [${selectedAccount.tokenName || selectedAccount.appName}] Account: ${selectedAccount.accountId} (AppID: ${selectedAccount.appId})`, 'info');
  }

  vpsSelect.addEventListener('change', onVpsChange);
  accountSelect.addEventListener('change', onAccountChange);

  // Toggle token peek on click
  let tokenVisible = false;
  metaTokenBadge.style.cursor = 'pointer';
  metaTokenBadge.addEventListener('click', function() {
    if (!selectedAccount || !selectedAccount.token) return;
    tokenVisible = !tokenVisible;
    if (tokenVisible) {
      metaTokenText.textContent = selectedAccount.token;
    } else {
      const t = selectedAccount.token;
      metaTokenText.textContent = t.substring(0, 8) + '...' + t.substring(t.length - 6);
    }
  });

  // Calculate epoch range based on selected date
  function getDateEpochRange(dateStr, specificDateOnly) {
    if (!dateStr) return { startEpoch: null, endEpoch: null };

    // Format YYYY-MM-DD
    const parts = dateStr.split('-');
    if (parts.length !== 3) return { startEpoch: null, endEpoch: null };

    const y = parseInt(parts[0], 10);
    const m = parseInt(parts[1], 10) - 1;
    const d = parseInt(parts[2], 10);

    // Local start of day 00:00:00
    const startObj = new Date(y, m, d, 0, 0, 0, 0);
    const startEpoch = Math.floor(startObj.getTime() / 1000);

    let endEpoch = null;
    if (specificDateOnly) {
      // Local end of day 23:59:59
      const endObj = new Date(y, m, d, 23, 59, 59, 999);
      endEpoch = Math.floor(endObj.getTime() / 1000);
    }

    return { startEpoch, endEpoch };
  }

  // Core Function: Fetch from Deriv via OTP REST & Private WebSocket
  async function fetchTradesFromDeriv(autoSave = false) {
    if (!selectedAccount) {
      alert('กรุณาเลือก Deriv Account ก่อนดำเนินการ');
      appendLog('❌ กรุณาเลือก Deriv Account', 'error');
      return;
    }

    const appId = (selectedAccount.appId || '').trim();
    const accountId = (selectedAccount.accountId || '').trim();
    const token = (selectedAccount.token || '').trim();

    if (!appId || !accountId || !token) {
      alert('ข้อมูล App ID, Account ID หรือ Token ในบัญชีนี้ไม่ครบถ้วน กรุณาตรวจสอบที่เมนู Deriv Account');
      appendLog('❌ บัญชีที่เลือกมีข้อมูล App ID / Account ID / Token ไม่ครบ', 'error');
      return;
    }

    const dateVal = tradeDateInput.value;
    if (!dateVal) {
      alert('กรุณาเลือกวันที่ทำการเทรด');
      return;
    }

    const isSpecificOnly = specificDateCheckbox.checked;
    const { startEpoch, endEpoch } = getDateEpochRange(dateVal, isSpecificOnly);

    if (!startEpoch) {
      alert('วันที่ไม่ถูกต้อง');
      return;
    }

    // Set UI Loading state
    fetchAndSaveBtn.disabled = true;
    previewBtn.disabled = true;
    saveOnlyBtn.disabled = true;
    btnText.textContent = 'กำลังเชื่อมต่อ Deriv...';
    connBadge.textContent = '⏳ Connecting...';
    connBadge.style.color = '#fbbf24';

    appendLog('====================================================', 'info');
    appendLog(`🚀 เริ่มต้นกระบวนการดึงประวัติการเทรดจาก Deriv.com`, 'info');
    appendLog(`📅 วันที่เลือก: ${dateVal} ${isSpecificOnly ? '(เฉพาะวันนี้ 00:00 - 23:59)' : '(ตั้งแต่วันนี้เป็นต้นไป)'}`, 'info');
    appendLog(`⏱️ Epoch Range: date_from = ${startEpoch}${endEpoch ? `, date_to = ${endEpoch}` : ''}`, 'info');
    appendLog(`🔑 ร้องขอ OTP ผ่าน REST: https://api.derivws.com/trading/v1/options/accounts/${accountId}/otp`, 'info');

    let wsUrl = null;
    try {
      // Step 1: Request OTP
      const otpRes = await fetch(`https://api.derivws.com/trading/v1/options/accounts/${accountId}/otp`, {
        method: 'POST',
        headers: {
          'Deriv-App-ID': appId,
          'Authorization': 'Bearer ' + token
        }
      });

      const otpJson = await otpRes.json();
      if (!otpRes.ok || !otpJson.data || !otpJson.data.url) {
        const errText = otpJson.error ? (otpJson.error.message || JSON.stringify(otpJson.error)) : (otpJson.message || 'ขอ OTP ไม่สำเร็จ');
        throw new Error(`REST Auth Error: ${errText}`);
      }

      wsUrl = otpJson.data.url;
      appendLog(`✅ ได้รับ OTP สำเร็จ! กำลังเปิด WebSocket ไปยัง Deriv...`, 'success');
      connBadge.textContent = '🟢 Connected WS';
      connBadge.style.color = '#34d399';

    } catch (err) {
      appendLog(`❌ เกิดข้อผิดพลาดในขั้นตอนขอ OTP: ${err.message}`, 'error');
      connBadge.textContent = '🔴 OTP Error';
      connBadge.style.color = '#f87171';
      fetchAndSaveBtn.disabled = false;
      previewBtn.disabled = false;
      btnText.textContent = 'Fetch & Save to DB';
      return;
    }

    // Step 2: Open WebSocket & Paginate profit_table
    allFetchedTrades = [];
    let offset = 0;
    const LIMIT = 100;

    const thWs = new WebSocket(wsUrl);

    function sendProfitTableReq() {
      const req = {
        profit_table: 1,
        description: 1,
        limit: LIMIT,
        offset: offset,
        date_from: startEpoch,
        sort: 'ASC'
      };
      if (endEpoch) {
        req.date_to = endEpoch;
      }
      appendLog(`📤 ส่งคำขอ profit_table (offset: ${offset}, limit: ${LIMIT})...`, 'info');
      thWs.send(JSON.stringify(req));
    }

    thWs.onopen = function() {
      appendLog(`🔗 WebSocket เชื่อมต่อสำเร็จ! เริ่มดึงรายการ transactions...`, 'success');
      offset = 0;
      sendProfitTableReq();
    };

    thWs.onmessage = async function(event) {
      let data;
      try {
        data = JSON.parse(event.data);
      } catch (e) {
        return;
      }

      if (data.error) {
        const errMsg = data.error.message || data.error.code || 'API Error';
        appendLog(`❌ Deriv API Error: ${errMsg}`, 'error');
        thWs.close();
        finishFetch(false);
        return;
      }

      if (data.msg_type === 'profit_table') {
        const pt = data.profit_table;
        const txns = (pt && pt.transactions) ? pt.transactions : [];

        for (let i = 0; i < txns.length; i++) {
          allFetchedTrades.push(txns[i]);
        }

        appendLog(`📥 ได้รับ ${txns.length} รายการ (ยอดสะสม: ${allFetchedTrades.length} trades)...`, 'info');

        // Check pagination
        if (txns.length >= LIMIT) {
          offset += LIMIT;
          sendProfitTableReq();
        } else {
          // Finished fetching all transactions
          thWs.close();
          appendLog(`🎉 ดึงข้อมูลจาก Deriv สำเร็จสมบูรณ์! รวมทั้งหมด ${allFetchedTrades.length} รายการ`, 'success');
          connBadge.textContent = '✅ Finished Fetch';

          // Process calculations & render UI
          processAndDisplayResults();

          // Auto-save if requested
          if (autoSave && allFetchedTrades.length > 0) {
            await saveTradesToDatabase();
          }

          finishFetch(true);
        }
      }
    };

    thWs.onerror = function(err) {
      appendLog(`❌ WebSocket เกิดข้อผิดพลาดในการรับ-ส่งข้อมูล`, 'error');
      finishFetch(false);
    };

    thWs.onclose = function() {
      connBadge.textContent = '⚪ Closed WS';
      connBadge.style.color = 'var(--text-tertiary)';
    };

    function finishFetch(success) {
      fetchAndSaveBtn.disabled = false;
      previewBtn.disabled = false;
      saveOnlyBtn.disabled = (allFetchedTrades.length === 0);
      btnText.textContent = 'Fetch & Save to DB';
    }
  }

  // Calculate Streaks (WinCon/LossCon) & KPIs
  function processAndDisplayResults() {
    if (allFetchedTrades.length === 0) {
      tradesTbody.innerHTML = '<tr><td colspan="14" style="text-align:center;padding:36px;color:#fbbf24;">⚠️ ไม่พบประวัติการเทรดในช่วงเวลาและบัญชีที่ระบุ</td></tr>';
      loadedCountBadge.style.display = 'none';
      assetFilterBar.style.display = 'none';
      statTotalTrades.textContent = '0';
      statSubTrades.textContent = '0 transactions';
      statWinRate.textContent = '0.0%';
      statWinLossSub.textContent = '0W / 0L';
      statNetProfit.textContent = '$0.00';
      statNetProfit.style.color = '#fff';
      statTotalPayout.textContent = 'Payout: $0.00';
      statMaxLossCon.textContent = 'LC: 0';
      statMaxWinCon.textContent = 'Max WinCon: 0';
      statTotalAssets.textContent = '0';
      statAssetsList.textContent = '-';
      return;
    }

    // Sort ASC by purchase_time
    allFetchedTrades.sort((a, b) => (a.purchase_time || 0) - (b.purchase_time || 0));

    // Group by asset to compute streaks
    const tradesByAsset = {};
    allFetchedTrades.forEach(t => {
      const sym = t.underlying_symbol || t.shortcode || 'UNKNOWN';
      if (!tradesByAsset[sym]) tradesByAsset[sym] = [];
      tradesByAsset[sym].push(t);
    });

    let overallWins = 0;
    let overallLosses = 0;
    let overallProfit = 0;
    let overallPayout = 0;
    let overallMaxLossCon = 0;
    let overallMaxWinCon = 0;

    const assetSummary = {};

    for (const sym in tradesByAsset) {
      const list = tradesByAsset[sym];
      list.sort((a, b) => (a.purchase_time || 0) - (b.purchase_time || 0));

      let w = 0, l = 0, p = 0;
      let curLoss = 0, maxLoss = 0;
      let curWin = 0, maxWin = 0;

      list.forEach(t => {
        const buyP = Number(t.buy_price) || 0;
        const sellP = Number(t.sell_price) || 0;
        let profit = 0;

        if (t.profit !== undefined && t.profit !== null) {
          profit = Number(t.profit);
        } else {
          profit = sellP - buyP;
        }

        const payout = Number(t.payout) || (sellP);
        p += profit;
        overallProfit += profit;
        overallPayout += payout;

        if (profit > 0) {
          w++;
          overallWins++;
          curWin++;
          curLoss = 0;
          if (curWin > maxWin) maxWin = curWin;
          t._winCon = curWin;
          t._lossCon = 0;
          t._outcome = 'WIN';
        } else if (profit < 0) {
          l++;
          overallLosses++;
          curLoss++;
          curWin = 0;
          if (curLoss > maxLoss) maxLoss = curLoss;
          t._lossCon = curLoss;
          t._winCon = 0;
          t._outcome = 'LOSS';
        } else {
          curLoss = 0;
          curWin = 0;
          t._lossCon = 0;
          t._winCon = 0;
          t._outcome = 'TIE';
        }
      });

      if (maxLoss > overallMaxLossCon) overallMaxLossCon = maxLoss;
      if (maxWin > overallMaxWinCon) overallMaxWinCon = maxWin;

      assetSummary[sym] = {
        count: list.length,
        wins: w,
        losses: l,
        profit: p,
        maxLossCon: maxLoss,
        maxWinCon: maxWin
      };
    }

    // Update KPI Displays
    const totalCount = allFetchedTrades.length;
    statTotalTrades.textContent = totalCount;
    statSubTrades.textContent = `${totalCount} transactions`;

    const winRate = totalCount > 0 ? ((overallWins / totalCount) * 100).toFixed(1) : 0;
    statWinRate.textContent = `${winRate}%`;
    statWinLossSub.textContent = `${overallWins}W / ${overallLosses}L`;
    statWinLossSub.style.color = overallWins >= overallLosses ? '#34d399' : '#f87171';

    statNetProfit.textContent = (overallProfit >= 0 ? '+' : '') + '$' + overallProfit.toFixed(2);
    statNetProfit.style.color = overallProfit >= 0 ? '#34d399' : '#f87171';
    statTotalPayout.textContent = `Payout: $${overallPayout.toFixed(2)}`;

    statMaxLossCon.textContent = `LC: ${overallMaxLossCon}`;
    statMaxWinCon.textContent = `Max WinCon: ${overallMaxWinCon}`;

    const symList = Object.keys(assetSummary);
    statTotalAssets.textContent = symList.length;
    statAssetsList.textContent = symList.join(', ');

    loadedCountBadge.style.display = 'inline-flex';
    loadedCountBadge.textContent = `${totalCount} trades loaded`;

    // Render Asset Filter Buttons
    renderAssetFilterButtons(symList, assetSummary);

    // Render Trades Table
    renderTable();
  }

  // Render filter buttons
  function renderAssetFilterButtons(symbols, assetSummary) {
    assetFilterBar.innerHTML = '<span style="font-size:12px; font-weight:600; color:var(--text-secondary);">กรองตามคู่เหรียญ:</span>';
    assetFilterBar.style.display = 'flex';

    // All button
    const allBtn = document.createElement('button');
    allBtn.type = 'button';
    allBtn.className = 'ld-filter-btn active';
    allBtn.textContent = `ALL (${allFetchedTrades.length})`;
    allBtn.addEventListener('click', () => {
      currentAssetFilter = null;
      updateFilterButtons(allBtn);
      renderTable();
    });
    assetFilterBar.appendChild(allBtn);

    // Individual symbol buttons
    symbols.forEach(sym => {
      const info = assetSummary[sym];
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ld-filter-btn';
      btn.innerHTML = `${sym} <span style="font-size:10.5px; opacity:0.8;">(${info.count})</span>`;
      btn.addEventListener('click', () => {
        currentAssetFilter = sym;
        updateFilterButtons(btn);
        renderTable();
      });
      assetFilterBar.appendChild(btn);
    });
  }

  function updateFilterButtons(activeBtn) {
    const btns = assetFilterBar.querySelectorAll('.ld-filter-btn');
    btns.forEach(b => b.classList.remove('active'));
    activeBtn.classList.add('active');
  }

  // Outcome filter buttons (All / Wins / Losses)
  document.querySelectorAll('[data-outcome]').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('[data-outcome]').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentOutcomeFilter = this.dataset.outcome;
      renderTable();
    });
  });

  // Render Table
  function renderTable() {
    let filtered = allFetchedTrades;

    if (currentAssetFilter) {
      filtered = filtered.filter(t => (t.underlying_symbol || t.shortcode) === currentAssetFilter);
    }

    if (currentOutcomeFilter === 'win') {
      filtered = filtered.filter(t => t._outcome === 'WIN');
    } else if (currentOutcomeFilter === 'loss') {
      filtered = filtered.filter(t => t._outcome === 'LOSS');
    }

    if (filtered.length === 0) {
      tradesTbody.innerHTML = '<tr><td colspan="14" style="text-align:center;padding:24px;color:var(--text-tertiary);">ไม่มีรายการเทรดที่ตรงกับตัวกรองที่เลือก</td></tr>';
      return;
    }

    const rowsHtml = filtered.map((t, idx) => {
      const sym = t.underlying_symbol || t.shortcode || '-';
      const cType = (t.contract_type || 'CALL').toUpperCase();
      const typeBadge = cType.includes('CALL') ? '<span class="badge-call">CALL</span>' : '<span class="badge-put">PUT</span>';
      
      const buyP = Number(t.buy_price || 0).toFixed(2);
      const sellP = Number(t.sell_price || 0).toFixed(2);
      const profit = Number(t.profit !== undefined ? t.profit : (t.sell_price - t.buy_price));
      const profitStr = (profit >= 0 ? '+' : '') + profit.toFixed(2);
      const profitColor = profit > 0 ? '#34d399' : (profit < 0 ? '#f87171' : 'var(--text-tertiary)');

      const outcomeBadge = t._outcome === 'WIN' 
        ? '<span class="badge-win">WIN</span>' 
        : (t._outcome === 'LOSS' ? '<span class="badge-loss">LOSS</span>' : '<span style="color:var(--text-tertiary);">TIE</span>');

      // Formatted Date-Times (TH UTC+7)
      const buyTimeStr = t.purchase_time ? new Date(t.purchase_time * 1000).toLocaleString('th-TH') : '-';
      const sellTimeStr = t.sell_time ? new Date(t.sell_time * 1000).toLocaleString('th-TH') : '-';
      const duration = t.duration ? `${t.duration}s` : (t.sell_time && t.purchase_time ? `${t.sell_time - t.purchase_time}s` : '-');

      return `
        <tr>
          <td>${idx + 1}</td>
          <td style="font-family:monospace;font-size:11px;color:#94a3b8;">${t.transaction_id || '-'}</td>
          <td style="font-size:12px;">${buyTimeStr}</td>
          <td style="font-size:12px;">${sellTimeStr}</td>
          <td>${duration}</td>
          <td style="font-weight:700;color:#38bdf8;">${sym}</td>
          <td>${typeBadge}</td>
          <td>$${buyP}</td>
          <td>$${sellP}</td>
          <td style="font-weight:700;color:${profitColor};">${profitStr}</td>
          <td>${outcomeBadge}</td>
          <td style="color:#f87171;font-weight:700;">${t._lossCon > 0 ? t._lossCon : '-'}</td>
          <td style="color:#34d399;font-weight:700;">${t._winCon > 0 ? t._winCon : '-'}</td>
          <td style="font-family:monospace;font-size:11px;color:var(--text-tertiary);" title="${t.longcode || ''}">${t.contract_id || '-'}</td>
        </tr>
      `;
    }).join('');

    tradesTbody.innerHTML = rowsHtml;
  }

  // Save trades to MySQL `derivTradeHistory`
  async function saveTradesToDatabase() {
    if (!allFetchedTrades || allFetchedTrades.length === 0) {
      alert('ไม่มีข้อมูล Trade ให้บันทึก');
      return;
    }

    try {
      const serverCode = selectedVps ? (parseInt(selectedVps.vpsCode, 10) || parseInt(selectedVps.id, 10) || 2) : 2;
      appendLog(`💾 กำลังส่งข้อมูล ${allFetchedTrades.length} trades (serverCode: ${serverCode}) ไปบันทึกลง MySQL table derivTradeHistory...`, 'info');
      saveOnlyBtn.disabled = true;

      const res = await fetch(SAVE_TRADE_HISTORY_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          serverCode: serverCode,
          trades: allFetchedTrades 
        })
      });

      if (!res.ok) {
        const errText = await res.text();
        throw new Error(`HTTP ${res.status}: ${errText}`);
      }

      const result = await res.json();
      if (result.success) {
        appendLog(`🎉 [MySQL Commit] บันทึกประวัติการเทรดสำเร็จจำนวน ${result.count || allFetchedTrades.length} รายการลงตาราง derivTradeHistory (serverCode: ${serverCode})!`, 'success');
        alert(`บันทึกประวัติการเทรดลง MySQL ตาราง derivTradeHistory สำเร็จเรียบร้อย (${result.count || allFetchedTrades.length} รายการ)`);
      } else {
        throw new Error(result.error || 'บันทึกลงฐานข้อมูลไม่สำเร็จ');
      }
    } catch (err) {
      appendLog(`❌ เกิดข้อผิดพลาดในการบันทึกลงฐานข้อมูล: ${err.message}`, 'error');
      alert(`บันทึกลง MySQL ล้มเหลว: ${err.message}`);
    } finally {
      saveOnlyBtn.disabled = false;
    }
  }

  // Button Listeners
  fetchAndSaveBtn.addEventListener('click', () => fetchTradesFromDeriv(true));
  previewBtn.addEventListener('click', () => fetchTradesFromDeriv(false));
  saveOnlyBtn.addEventListener('click', () => saveTradesToDatabase());

  // Init Data on load
  initData();
})();
</script>
