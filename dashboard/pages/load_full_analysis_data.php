<?php
/**
 * Load FullAnalysis Data Page
 * Vision UI Dark Glassmorphism Design
 * 
 * Features:
 * - VPS Server Selection & Deriv Account Selection (Like load_deriv_trade_history)
 * - Tab Navigation:
 *   1. Overview & Controls (KPI Cards & Meta Setup)
 *   2. Raw Analysis Data (Interactive Textarea for receiving/pasting analysisdata)
 *   3. Live Execution Console (Terminal Logging)
 *   4. Data Table / Breakdown (Preview Table)
 * - Prepared for future customizations and processing logic
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-load-full-analysis-data">
  <style>
    /* Scoped Styles for Load FullAnalysis Data */
    .fa-control-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 24px;
      margin-bottom: 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }
    .fa-control-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
      gap: 16px;
      align-items: flex-end;
    }
    @media (max-width: 992px) {
      .fa-control-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
    @media (max-width: 576px) {
      .fa-control-grid {
        grid-template-columns: 1fr;
      }
    }
    .fa-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 8px;
    }
    .fa-select, .fa-input {
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
    .fa-select:focus, .fa-input:focus {
      border-color: #38bdf8;
      box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
      outline: none;
      background: rgba(15, 28, 70, 0.95);
    }
    .fa-select option {
      background: #0f172a;
      color: #fff;
    }

    /* Tab Navigation */
    .fa-tabs-header {
      display: flex;
      gap: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
      margin-bottom: 20px;
      padding-bottom: 4px;
      overflow-x: auto;
    }
    .fa-tab-btn {
      background: transparent;
      border: none;
      border-bottom: 2px solid transparent;
      color: var(--text-tertiary);
      padding: 10px 18px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      white-space: nowrap;
    }
    .fa-tab-btn:hover {
      color: #fff;
    }
    .fa-tab-btn.active {
      color: #38bdf8;
      border-bottom-color: #38bdf8;
    }
    .fa-tab-pane {
      display: none;
    }
    .fa-tab-pane.active {
      display: block;
      animation: faFadeIn 0.2s ease-in-out;
    }
    @keyframes faFadeIn {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Buttons */
    .fa-btn-primary {
      background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
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
      box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35);
      white-space: nowrap;
    }
    .fa-btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(59, 130, 246, 0.45);
    }
    .fa-btn-success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
      white-space: nowrap;
    }
    .fa-btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
    }
    .fa-btn-success:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }
    .fa-btn-secondary {
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-secondary);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 8px 14px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
      white-space: nowrap;
    }
    .fa-btn-secondary:hover {
      background: rgba(255, 255, 255, 0.14);
      color: #fff;
    }

    /* Textarea Box */
    .fa-textarea-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 20px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
      margin-bottom: 24px;
    }
    .fa-textarea-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      flex-wrap: wrap;
      gap: 10px;
    }
    .fa-textarea {
      width: 100%;
      height: 380px;
      background: #090d16;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--radius-sm);
      padding: 14px;
      color: #38bdf8;
      font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
      font-size: 12.5px;
      line-height: 1.6;
      box-sizing: border-box;
      resize: vertical;
      box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6);
      transition: border-color 0.2s ease;
    }
    .fa-textarea:focus {
      outline: none;
      border-color: #38bdf8;
      box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6), 0 0 0 2px rgba(56, 189, 248, 0.2);
    }
    .fa-textarea-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 10px;
      font-size: 12px;
      color: var(--text-tertiary);
      flex-wrap: wrap;
      gap: 8px;
    }

    /* Badges & Stats */
    .fa-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 11.5px;
      font-weight: 600;
    }
    .fa-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .fa-stat-card {
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
    .fa-stat-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }
    .fa-stat-val {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
    }
    .fa-stat-sub {
      font-size: 11px;
      color: var(--text-tertiary);
      margin-top: 3px;
    }
    .fa-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .fa-stat-icon svg {
      width: 22px;
      height: 22px;
      fill: #fff;
    }

    /* Console Terminal */
    .fa-console-box {
      background: #090d16;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-md);
      padding: 16px;
      margin-bottom: 24px;
      box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6);
      font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
    }
    .fa-console-header {
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
    #faLogArea {
      max-height: 240px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-size: 12px;
      line-height: 1.5;
    }
    .fa-log-entry {
      padding: 4px 8px;
      border-radius: 4px;
      word-break: break-all;
    }
    .fa-log-info { color: #94a3b8; }
    .fa-log-success { color: #34d399; background: rgba(52, 211, 153, 0.08); }
    .fa-log-warn { color: #fbbf24; background: rgba(251, 191, 36, 0.08); }
    .fa-log-error { color: #f87171; background: rgba(248, 113, 113, 0.1); font-weight: 600; }
  </style>

  <!-- 1. Control & Setup Card (สไตล์แบบ load_deriv_trade_history) -->
  <div class="fa-control-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
      <div>
        <h3 style="margin:0 0 4px 0; font-size:18px; font-weight:700; color:#fff; display:flex; align-items:center; gap:8px;">
          <span>📈</span> Load FullAnalysis Data
        </h3>
        <p style="margin:0; font-size:12.5px; color:var(--text-tertiary);">
          ระบบโหลดและจัดเตรียมข้อมูล FullAnalysis Data จาก VPS / Deriv หรือป้อนข้อมูลดิบผ่าน Textarea สำหรับประมวลผล
        </p>
      </div>
      <div style="display:flex; gap:8px; align-items:center;">
        <span id="faStatusBadge" class="fa-badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.35);">
          ⚪ Standby
        </span>
      </div>
    </div>

    <div class="fa-control-grid">
      <!-- 1.1 VPS Server Selection -->
      <div>
        <label class="fa-label" for="faVpsSelect">🖥️ 1. เลือก VPS Server</label>
        <select class="fa-select" id="faVpsSelect">
          <option value="">-- กำลังโหลดรายการ VPS... --</option>
        </select>
      </div>

      <!-- 1.2 Deriv Account Selection -->
      <div>
        <label class="fa-label" for="faAccountSelect">🔑 2. เลือก Deriv Account</label>
        <select class="fa-select" id="faAccountSelect">
          <option value="">-- กำลังโหลด Account... --</option>
        </select>
      </div>

      <!-- 1.3 Date Selection -->
      <div>
        <label class="fa-label" for="faDateInput">📅 3. วันที่วิเคราะห์ (Analysis Date)</label>
        <input type="date" class="fa-input" id="faDateInput" style="cursor:pointer;">
      </div>

      <!-- 1.4 Asset Scope Selection -->
      <div>
        <label class="fa-label" for="faAssetScope">🎯 4. ขอบเขต Asset</label>
        <select class="fa-select" id="faAssetScope">
          <option value="all_standard" selected>🌐 ทุก Asset มาตรฐาน (10 Assets)</option>
          <option value="1hz_all">⚡ ทุก Asset ตระกูล 1HZ (5 Assets)</option>
          <option value="trade_history">📊 ทุก Asset จาก Trade History</option>
          <option value="custom">✏️ กำหนดเอง (Custom)</option>
        </select>
        <input type="text" class="fa-input" id="faCustomAssets" placeholder="เช่น 1HZ75V, 1HZ100V" style="display:none; margin-top:8px;">
      </div>

      <!-- 1.5 Action Buttons -->
      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <button type="button" class="fa-btn-success" id="faLoadAndSaveBtn" title="โหลดข้อมูลและบันทึกลง full_analysis_data, analysis_pk_trend, analysis_smc ให้ครบทุก Asset">
          <span>💾</span> โหลดและบันทึก (Load &amp; Save)
        </button>
        <button type="button" class="fa-btn-primary" id="faLoadDataBtn" title="โหลดข้อมูลสำหรับดูตัวอย่างเท่านั้น">
          <span>📥</span> โหลดดูข้อมูล (Preview)
        </button>
      </div>
    </div>

    <!-- 1.5 Account Info Preview Bar -->
    <div style="display:flex; align-items:center; gap:12px; margin-top:16px; padding:12px 16px; background:rgba(15,23,42,0.6); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius-sm); font-size:12px; color:var(--text-secondary); flex-wrap:wrap;">
      <span style="color:var(--text-tertiary);">ข้อมูลเป้าหมาย:</span>
      <span class="fa-badge" id="faMetaVpsBadge" style="background:rgba(0,212,255,0.15); color:#00d4ff; border:1px solid rgba(0,212,255,0.35);">🖥️ VPS: -</span>
      <span class="fa-badge" id="faMetaAccBadge" style="background:rgba(59,130,246,0.15); color:#60a5fa; border:1px solid rgba(59,130,246,0.35);">👤 Account ID: -</span>
      <span class="fa-badge" id="faMetaAppBadge" style="background:rgba(168,85,247,0.15); color:#c084fc; border:1px solid rgba(168,85,247,0.35);">🆔 App ID: -</span>
    </div>
  </div>

  <!-- 2. Tab Navigation Header -->
  <div class="fa-tabs-header">
    <button type="button" class="fa-tab-btn active" data-tab="tab-textarea">
      <span>📝</span> Raw Analysis Data (Textarea)
    </button>
    <button type="button" class="fa-tab-btn" data-tab="tab-overview">
      <span>📊</span> ภาพรวม &amp; สถิติ (Overview &amp; Stats)
    </button>
    <button type="button" class="fa-tab-btn" data-tab="tab-console">
      <span>💻</span> Live Execution Console
    </button>
    <button type="button" class="fa-tab-btn" data-tab="tab-table">
      <span>📋</span> ตารางรายการข้อมูล (Data Table)
    </button>
  </div>

  <!-- 3. Tab Content Panes -->

  <!-- TAB 1: Raw Analysis Data Textarea (เปิดเป็นค่าเริ่มต้น) -->
  <div class="fa-tab-pane active" id="tab-textarea">
    <div class="fa-textarea-card">
      <div class="fa-textarea-header">
        <div style="display:flex; align-items:center; gap:8px;">
          <h4 style="margin:0; font-size:15px; font-weight:700; color:#fff;">
            📝 ช่องรับข้อมูลดิบ analysisdata (Raw Analysis Data Input)
          </h4>
          <span id="faJsonStatusBadge" class="fa-badge" style="background:rgba(255,255,255,0.06); color:var(--text-tertiary); border:1px solid rgba(255,255,255,0.12);">
            ⚪ ยังไม่มีข้อมูล
          </span>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <button type="button" class="fa-btn-secondary" id="faPasteBtn" title="วางข้อมูลจากคลิปบอร์ด">
            📋 วางจากคลิปบอร์ด
          </button>
          <button type="button" class="fa-btn-secondary" id="faFormatJsonBtn" title="จัดรูปแบบ JSON ให้สวยงาม">
            ✨ Format JSON
          </button>
          <button type="button" class="fa-btn-secondary" id="faValidateBtn" title="ตรวจสอบความถูกต้อง">
            🔍 Validate
          </button>
          <button type="button" class="fa-btn-secondary" id="faClearTextareaBtn" style="color:#f87171;" title="ล้างข้อความใน textarea">
            🧹 ล้าง
          </button>
        </div>
      </div>

      <textarea class="fa-textarea" id="faAnalysisDataInput" placeholder="วางข้อมูล analysisdata (JSON หรือข้อความข้อมูลดิบ) ที่นี่..."></textarea>

      <div class="fa-textarea-footer">
        <div>
          <span>ความยาว: <b id="faCharCount" style="color:#fff;">0</b> ตัวอักษร</span> | 
          <span>จำนวน: <b id="faLineCount" style="color:#fff;">0</b> บรรทัด</span>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <button type="button" class="fa-btn-success" id="faSaveTextareaToDbBtn" style="padding:6px 16px; font-size:12px;" title="บันทึกข้อมูลใน Textarea ลง full_analysis_data, analysis_pk_trend, analysis_smc">
            <span>💾</span> บันทึก Textarea ลง DB
          </button>
          <button type="button" class="fa-btn-primary" id="faProcessDataBtn" style="padding:6px 16px; font-size:12px;">
            <span>⚡</span> ประมวลผลข้อมูล (Process Data)
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB 2: Overview & Stats -->
  <div class="fa-tab-pane" id="tab-overview">
    <div class="fa-stats-grid">
      <div class="fa-stat-card">
        <div>
          <div class="fa-stat-label">📊 DATA POINTS</div>
          <div class="fa-stat-val" id="statDataPoints">0</div>
          <div class="fa-stat-sub">จุดข้อมูลทั้งหมด</div>
        </div>
        <div class="fa-stat-icon" style="background:linear-gradient(135deg, #38bdf8, #0284c7);">
          <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        </div>
      </div>

      <div class="fa-stat-card">
        <div>
          <div class="fa-stat-label">🕯️ CANDLES ANALYZED</div>
          <div class="fa-stat-val" id="statCandlesCount">0</div>
          <div class="fa-stat-sub">แท่งเทียนที่ประมวลผล</div>
        </div>
        <div class="fa-stat-icon" style="background:linear-gradient(135deg, #10b981, #059669);">
          <svg viewBox="0 0 24 24"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/></svg>
        </div>
      </div>

      <div class="fa-stat-card">
        <div>
          <div class="fa-stat-label">🎯 STRATEGY MATCHES</div>
          <div class="fa-stat-val" id="statStrategyMatches">0</div>
          <div class="fa-stat-sub">เคสที่ตรงกับเงื่อนไข</div>
        </div>
        <div class="fa-stat-icon" style="background:linear-gradient(135deg, #f59e0b, #d97706);">
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        </div>
      </div>

      <div class="fa-stat-card">
        <div>
          <div class="fa-stat-label">🌐 ASSETS IDENTIFIED</div>
          <div class="fa-stat-val" id="statAssetsCount">0</div>
          <div class="fa-stat-sub">คู่เหรียญที่พบ</div>
        </div>
        <div class="fa-stat-icon" style="background:linear-gradient(135deg, #8b5cf6, #ec4899);">
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB 3: Live Execution Console -->
  <div class="fa-tab-pane" id="tab-console">
    <div class="fa-console-box">
      <div class="fa-console-header">
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#38bdf8;"></span>
          <span>💻 LIVE EXECUTION CONSOLE</span>
        </div>
        <span style="font-size:11px; cursor:pointer; color:#38bdf8; text-decoration:underline;" onclick="document.getElementById('faLogArea').innerHTML=''">Clear</span>
      </div>
      <div id="faLogArea">
        <div class="fa-log-entry fa-log-info">✨ หน้ารับข้อมูล FullAnalysis Data พร้อมใช้งาน...</div>
      </div>
    </div>
  </div>

  <!-- TAB 4: Data Table / Breakdown -->
  <div class="fa-tab-pane" id="tab-table">
    <div class="fa-control-card">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h4 style="margin:0; font-size:15px; font-weight:700; color:#fff;">
          📋 ตารางรายการข้อมูล (Data Breakdown Table)
        </h4>
        <span class="fa-badge" id="faTableCountBadge" style="background:rgba(255,255,255,0.06); color:var(--text-tertiary);">0 records</span>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:12.5px; white-space:nowrap;">
          <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.1); color:var(--text-secondary);">
              <th style="padding:10px; text-align:left;">#</th>
              <th style="padding:10px; text-align:left;">Time (TH)</th>
              <th style="padding:10px; text-align:left;">Symbol</th>
              <th style="padding:10px; text-align:left;">Open</th>
              <th style="padding:10px; text-align:left;">High</th>
              <th style="padding:10px; text-align:left;">Low</th>
              <th style="padding:10px; text-align:left;">Close</th>
              <th style="padding:10px; text-align:left;">Case Code</th>
              <th style="padding:10px; text-align:left;">Trend</th>
              <th style="padding:10px; text-align:left;">Action</th>
            </tr>
          </thead>
          <tbody id="faTableBody">
            <tr>
              <td colspan="10" style="text-align:center; padding:36px; color:var(--text-tertiary);">
                ยังไม่มีข้อมูลในตาราง — พร้อมสำหรับการเชื่อมต่อข้อมูลตามขั้นตอนต่อไป
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  const API_VPS_URL = '../php/api_vps.php';
  const API_DERIV_ACC_URL = '../php/api_deriv_account.php';
  const API_DERIV_TRADE_HIST_URL = '../php/api_deriv_trade_history.php';
  const API_SAVE_TRADE_HIST_URL = '../php/save_trade_history.php';
  const PROXY_FULL_ANALYSIS_URL = '../php/proxy_full_analysis.php';
  const API_SAVE_FULL_ANALYSIS_URL = '../php/save_full_analysis.php';

  // Standard Deriv Synthetic Symbols
  const STANDARD_ALL_ASSETS = ['1HZ10V', '1HZ25V', '1HZ50V', '1HZ75V', '1HZ100V', 'R_10', 'R_25', 'R_50', 'R_75', 'R_100'];
  const STANDARD_1HZ_ASSETS = ['1HZ10V', '1HZ25V', '1HZ50V', '1HZ75V', '1HZ100V'];

  // DOM Elements
  const vpsSelect = document.getElementById('faVpsSelect');
  const accountSelect = document.getElementById('faAccountSelect');
  const dateInput = document.getElementById('faDateInput');
  const assetScopeSelect = document.getElementById('faAssetScope');
  const customAssetsInput = document.getElementById('faCustomAssets');
  const textarea = document.getElementById('faAnalysisDataInput');
  const logArea = document.getElementById('faLogArea');
  const statusBadge = document.getElementById('faStatusBadge');
  const jsonStatusBadge = document.getElementById('faJsonStatusBadge');
  const charCount = document.getElementById('faCharCount');
  const lineCount = document.getElementById('faLineCount');

  // Meta Badges
  const metaVpsBadge = document.getElementById('faMetaVpsBadge');
  const metaAccBadge = document.getElementById('faMetaAccBadge');
  const metaAppBadge = document.getElementById('faMetaAppBadge');

  // KPI & Table Elements
  const statDataPoints = document.getElementById('statDataPoints');
  const statCandlesCount = document.getElementById('statCandlesCount');
  const statStrategyMatches = document.getElementById('statStrategyMatches');
  const statAssetsCount = document.getElementById('statAssetsCount');
  const tableBody = document.getElementById('faTableBody');
  const tableCountBadge = document.getElementById('faTableCountBadge');

  // Buttons
  const pasteBtn = document.getElementById('faPasteBtn');
  const formatJsonBtn = document.getElementById('faFormatJsonBtn');
  const validateBtn = document.getElementById('faValidateBtn');
  const clearBtn = document.getElementById('faClearTextareaBtn');
  const processBtn = document.getElementById('faProcessDataBtn');
  const loadDataBtn = document.getElementById('faLoadDataBtn');
  const loadAndSaveBtn = document.getElementById('faLoadAndSaveBtn');
  const saveTextareaToDbBtn = document.getElementById('faSaveTextareaToDbBtn');

  // State
  let vpsList = [];
  let accountsList = [];

  // Asset Scope Toggle
  if (assetScopeSelect) {
    assetScopeSelect.addEventListener('change', () => {
      if (assetScopeSelect.value === 'custom') {
        customAssetsInput.style.display = 'block';
        customAssetsInput.focus();
      } else {
        customAssetsInput.style.display = 'none';
      }
    });
  }

  // Helper: Append log
  function appendLog(msg, type = 'info') {
    const timeStr = new Date().toLocaleTimeString('th-TH');
    const entry = document.createElement('div');
    entry.className = `fa-log-entry fa-log-${type}`;
    entry.textContent = `[${timeStr}] ${msg}`;
    logArea.appendChild(entry);
    logArea.scrollTop = logArea.scrollHeight;
  }

  // Set today date
  const now = new Date();
  const y = now.getFullYear();
  const m = String(now.getMonth() + 1).padStart(2, '0');
  const d = String(now.getDate()).padStart(2, '0');
  dateInput.value = `${y}-${m}-${d}`;

  // Tab switching
  const tabBtns = document.querySelectorAll('.fa-tab-btn');
  const tabPanes = document.querySelectorAll('.fa-tab-pane');

  function switchTab(tabId) {
    tabBtns.forEach(b => {
      if (b.dataset.tab === tabId) b.classList.add('active');
      else b.classList.remove('active');
    });
    tabPanes.forEach(p => {
      if (p.id === tabId) p.classList.add('active');
      else p.classList.remove('active');
    });
  }

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      switchTab(btn.dataset.tab);
    });
  });

  // Initialize: Load VPS & Accounts
  async function initData() {
    try {
      appendLog('🔄 กำลังโหลดรายการ VPS Servers และ Deriv Accounts...', 'info');

      const [vpsRes, accRes] = await Promise.all([
        fetch(API_VPS_URL + '?action=list').then(r => r.json()).catch(() => ({ data: [] })),
        fetch(API_DERIV_ACC_URL).then(r => r.json()).catch(() => ({ data: [] }))
      ]);

      vpsList = vpsRes.data || [];
      accountsList = accRes.data || [];

      // Sort VPS 1, 2, 3, 4
      vpsList.sort((a, b) => {
        const codeA = parseInt(a.vpsCode, 10);
        const codeB = parseInt(b.vpsCode, 10);
        if (!isNaN(codeA) && !isNaN(codeB)) return codeA - codeB;
        return (a.vpsCode || '').localeCompare(b.vpsCode || '') || (a.id - b.id);
      });

      // Sort Accounts 1, 2, 3, 4
      accountsList.sort((a, b) => {
        const noA = parseInt(a.AppNo, 10);
        const noB = parseInt(b.AppNo, 10);
        if (!isNaN(noA) && !isNaN(noB)) return noA - noB;
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

      if (vpsList.length > 0) {
        vpsSelect.selectedIndex = 1;
        onVpsChange();
      }
    } catch (err) {
      appendLog(`❌ เกิดข้อผิดพลาดในการโหลดข้อมูล: ${err.message}`, 'error');
    }
  }

  function onVpsChange() {
    const vpsId = parseInt(vpsSelect.value, 10);
    const selectedVps = vpsList.find(v => v.id === vpsId) || null;

    if (!selectedVps) {
      metaVpsBadge.textContent = '🖥️ VPS: -';
      return;
    }

    metaVpsBadge.textContent = `🖥️ VPS: ${selectedVps.vpsCode} (${selectedVps.vpsName})`;
    if (selectedVps.DerivAccountID) {
      accountSelect.value = selectedVps.DerivAccountID;
    }
    onAccountChange();
  }

  function onAccountChange() {
    const accId = parseInt(accountSelect.value, 10);
    const selectedAccount = accountsList.find(a => a.id === accId) || null;

    if (!selectedAccount) {
      metaAccBadge.textContent = '👤 Account ID: -';
      metaAppBadge.textContent = '🆔 App ID: -';
      return;
    }

    metaAccBadge.textContent = `👤 Account ID: ${selectedAccount.accountId || '-'}`;
    metaAppBadge.textContent = `🆔 App ID: ${selectedAccount.appId || '-'}`;
  }

  vpsSelect.addEventListener('change', onVpsChange);
  accountSelect.addEventListener('change', onAccountChange);

  // Textarea input counters & JSON detection
  function updateTextareaStats() {
    const text = textarea.value;
    charCount.textContent = text.length.toLocaleString();
    lineCount.textContent = text ? text.split('\n').length.toLocaleString() : '0';

    const trimmed = text.trim();
    if (!trimmed) {
      jsonStatusBadge.textContent = '⚪ ยังไม่มีข้อมูล';
      jsonStatusBadge.style.color = 'var(--text-tertiary)';
      jsonStatusBadge.style.borderColor = 'rgba(255,255,255,0.12)';
      return;
    }

    if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
      try {
        const parsed = JSON.parse(trimmed);
        const count = Array.isArray(parsed) ? parsed.length : (parsed.count || (parsed.data ? (Array.isArray(parsed.data) ? parsed.data.length : Object.keys(parsed.data).length) : Object.keys(parsed).length));
        jsonStatusBadge.textContent = `🟢 Valid JSON (${count.toLocaleString()} items)`;
        jsonStatusBadge.style.color = '#34d399';
        jsonStatusBadge.style.borderColor = 'rgba(52,211,153,0.4)';
      } catch (e) {
        jsonStatusBadge.textContent = '⚠️ Invalid JSON Syntax';
        jsonStatusBadge.style.color = '#fbbf24';
        jsonStatusBadge.style.borderColor = 'rgba(251,191,36,0.4)';
      }
    } else {
      jsonStatusBadge.textContent = '📝 Plain Text Data';
      jsonStatusBadge.style.color = '#38bdf8';
      jsonStatusBadge.style.borderColor = 'rgba(56,189,248,0.4)';
    }
  }

  textarea.addEventListener('input', updateTextareaStats);

  // Paste from Clipboard
  pasteBtn.addEventListener('click', async () => {
    try {
      const clipText = await navigator.clipboard.readText();
      textarea.value = clipText;
      updateTextareaStats();
      appendLog(`📋 วางข้อมูลจากคลิปบอร์ดเรียบร้อย (${clipText.length.toLocaleString()} ตัวอักษร)`, 'success');
    } catch (e) {
      appendLog('⚠️ ไม่สามารถเข้าถึงคลิปบอร์ดได้โดยตรง กรุณาใช้ Ctrl+V เพื่อวางในช่อง Textarea', 'warn');
    }
  });

  // Format Pretty JSON
  formatJsonBtn.addEventListener('click', () => {
    const text = textarea.value.trim();
    if (!text) return;
    try {
      const parsed = JSON.parse(text);
      textarea.value = JSON.stringify(parsed, null, 2);
      updateTextareaStats();
      appendLog('✨ จัดรูปแบบ JSON เรียบร้อยแล้ว', 'success');
    } catch (e) {
      appendLog(`❌ ไม่สามารถจัดรูปแบบได้เนื่องจาก JSON ไม่ถูกต้อง: ${e.message}`, 'error');
    }
  });

  // Validate Data
  validateBtn.addEventListener('click', () => {
    const text = textarea.value.trim();
    if (!text) {
      appendLog('⚠️ ช่อง Textarea ว่างเปล่า กรุณาวางข้อมูลก่อนตรวจสอบ', 'warn');
      return;
    }
    try {
      const parsed = JSON.parse(text);
      const isArr = Array.isArray(parsed);
      const keysCount = isArr ? parsed.length : Object.keys(parsed).length;
      appendLog(`✅ ตรวจสอบ JSON สำเร็จ: เป็นชนิด ${isArr ? 'Array' : 'Object'} ที่มี ${keysCount.toLocaleString()} รายการ/คีย์`, 'success');
    } catch (e) {
      appendLog(`⚠️ ข้อมูลไม่ใช่ JSON หรือมีข้อผิดพลาด: ${e.message}`, 'warn');
    }
  });

  // Clear Textarea
  clearBtn.addEventListener('click', () => {
    textarea.value = '';
    updateTextareaStats();
    appendLog('🧹 ล้างข้อมูลใน Textarea แล้ว', 'info');
  });

  // Process Data from Textarea into Table & KPIs
  function processCurrentTextarea() {
    const text = textarea.value.trim();
    if (!text) {
      alert('ไม่มีข้อมูลในช่อง Textarea กรุณาโหลดข้อมูลหรือวาง JSON ก่อน');
      return;
    }
    try {
      const parsed = JSON.parse(text);
      renderAnalysisResults(parsed);
      appendLog('⚡ ประมวลผลข้อมูลใน Textarea สำเร็จ นำขึ้นตารางและสถิติเรียบร้อย', 'success');
    } catch (e) {
      alert('ข้อมูลใน Textarea ไม่ใช่ JSON ที่ถูกต้อง: ' + e.message);
    }
  }

  processBtn.addEventListener('click', processCurrentTextarea);

  // Helper: Render Analysis Results into KPIs & Table
  function renderAnalysisResults(analysisData, assetsList = null) {
    let allCandles = [];

    if (Array.isArray(analysisData)) {
      allCandles = analysisData;
    } else if (analysisData && Array.isArray(analysisData.data)) {
      allCandles = analysisData.data;
    } else if (analysisData && typeof analysisData === 'object') {
      const pool = analysisData.data || analysisData;
      for (const k in pool) {
        if (Array.isArray(pool[k])) {
          allCandles = allCandles.concat(pool[k]);
        } else if (pool[k] && Array.isArray(pool[k].data)) {
          allCandles = allCandles.concat(pool[k].data);
        }
      }
    }

    allCandles.sort((a, b) => (a.candletime || 0) - (b.candletime || 0));

    const totalCount = allCandles.length;
    if (statDataPoints) statDataPoints.textContent = totalCount.toLocaleString();
    if (statCandlesCount) statCandlesCount.textContent = totalCount.toLocaleString();

    const distinctSymbols = new Set(allCandles.map(c => c.assetCode || c.symbol).filter(Boolean));
    if (statAssetsCount) {
      statAssetsCount.textContent = (assetsList && assetsList.length) ? assetsList.length : (distinctSymbols.size || 1);
    }

    const matches = allCandles.filter(c => c.pkTrend && c.pkTrend.trend && c.pkTrend.trend !== 'Sideways').length;
    if (statStrategyMatches) statStrategyMatches.textContent = matches.toLocaleString();

    if (tableCountBadge) tableCountBadge.textContent = `${totalCount.toLocaleString()} records`;

    if (tableBody) {
      if (allCandles.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding:36px; color:var(--text-tertiary);">ไม่พบข้อมูลแท่งเทียน</td></tr>';
        return;
      }

      const displayList = allCandles.slice(0, 500);
      const rows = displayList.map((c, idx) => {
        const timeStr = c.candletime_display || (c.candletime ? new Date(c.candletime * 1000).toLocaleString('th-TH') : '-');
        const sym = c.assetCode || c.symbol || '-';
        const open = Number(c.open || 0).toFixed(2);
        const high = Number(c.high || 0).toFixed(2);
        const low = Number(c.low || 0).toFixed(2);
        const close = Number(c.close || 0).toFixed(2);
        
        const pk = c.pkTrend || {};
        const caseCode = pk.caseCode || '-';
        const trend = pk.trend || '-';
        const trendColor = trend.toLowerCase().includes('up') ? '#34d399' : (trend.toLowerCase().includes('down') ? '#f87171' : '#fbbf24');
        const desc = pk.description || pk.caseDesc || '-';

        return `
          <tr>
            <td>${idx + 1}</td>
            <td style="font-size:11.5px;color:#94a3b8;">${timeStr}</td>
            <td style="font-weight:700;color:#38bdf8;">${sym}</td>
            <td>${open}</td>
            <td>${high}</td>
            <td>${low}</td>
            <td style="font-weight:700;">${close}</td>
            <td style="font-family:monospace;font-size:11px;color:#c084fc;">${caseCode}</td>
            <td style="font-weight:600;color:${trendColor};">${trend}</td>
            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:var(--text-secondary);" title="${desc}">${desc}</td>
          </tr>
        `;
      }).join('');

      tableBody.innerHTML = rows;
    }
  }

  // Fetch from Deriv and Save to MySQL derivTradeHistory
  async function fetchAndSaveDerivTrades(selectedVps, dateVal) {
    const accId = parseInt(selectedVps.DerivAccountID || accountSelect.value, 10);
    const acc = accountsList.find(a => a.id === accId) || accountsList[0];

    if (!acc || !acc.token || !acc.appId || !acc.accountId) {
      appendLog('❌ ไม่พบข้อมูล Token หรือ App ID ของ Deriv Account สำหรับ VPS นี้ กรุณาตรวจสอบการตั้งค่า Deriv Account', 'error');
      alert('ไม่พบข้อมูล Deriv Account (Token / App ID) ที่ผูกไว้กับ VPS นี้');
      return false;
    }

    const serverCode = parseInt(selectedVps.vpsCode, 10) || parseInt(selectedVps.id, 10) || 2;
    const startEpoch = Math.floor(new Date(`${dateVal}T00:00:00+07:00`).getTime() / 1000);
    const endEpoch = Math.floor(new Date(`${dateVal}T23:59:59+07:00`).getTime() / 1000);

    appendLog(`🔑 ร้องขอ OTP จาก Deriv REST สำหรับ Account ${acc.accountId}...`, 'info');
    statusBadge.textContent = '⏳ Requesting OTP...';
    statusBadge.style.color = '#fbbf24';

    let wsUrl = null;
    try {
      const otpRes = await fetch(`https://api.derivws.com/trading/v1/options/accounts/${acc.accountId}/otp`, {
        method: 'POST',
        headers: {
          'Deriv-App-ID': acc.appId,
          'Authorization': 'Bearer ' + acc.token
        }
      });
      const otpJson = await otpRes.json();
      if (!otpRes.ok || !otpJson.data || !otpJson.data.url) {
        const errMsg = (otpJson.error && otpJson.error.message) || otpJson.message || 'ขอ OTP ไม่สำเร็จ';
        throw new Error(errMsg);
      }
      wsUrl = otpJson.data.url;
      appendLog('✅ ได้รับ OTP สำเร็จ! กำลังเปิด WebSocket ไปยัง Deriv...', 'success');
    } catch (err) {
      appendLog(`❌ ขอ OTP จาก Deriv ไม่สำเร็จ: ${err.message}`, 'error');
      return false;
    }

    // Connect WebSocket and fetch profit_table
    return new Promise((resolve) => {
      const fetchedTrades = [];
      let offset = 0;
      const LIMIT = 100;
      const ws = new WebSocket(wsUrl);

      function sendReq() {
        const req = {
          profit_table: 1,
          description: 1,
          limit: LIMIT,
          offset: offset,
          date_from: startEpoch,
          date_to: endEpoch,
          sort: 'ASC'
        };
        appendLog(`📤 ส่งคำขอ profit_table (offset: ${offset}, limit: ${LIMIT})...`, 'info');
        ws.send(JSON.stringify(req));
      }

      ws.onopen = function() {
        appendLog('🔗 WebSocket เชื่อมต่อสำเร็จ! เริ่มดึงรายการ transactions...', 'success');
        sendReq();
      };

      ws.onmessage = async function(event) {
        let data;
        try {
          data = JSON.parse(event.data);
        } catch (e) {
          return;
        }

        if (data.error) {
          const errMsg = data.error.message || data.error.code || 'API Error';
          appendLog(`❌ Deriv API Error: ${errMsg}`, 'error');
          ws.close();
          resolve(false);
          return;
        }

        if (data.msg_type === 'profit_table') {
          const pt = data.profit_table;
          const txns = (pt && pt.transactions) ? pt.transactions : [];

          for (let i = 0; i < txns.length; i++) {
            fetchedTrades.push(txns[i]);
          }

          appendLog(`📥 ได้รับ ${txns.length} รายการ (สะสม: ${fetchedTrades.length} trades)...`, 'info');

          if (txns.length >= LIMIT) {
            offset += LIMIT;
            sendReq();
          } else {
            ws.close();
            appendLog(`🎉 ดึงข้อมูลจาก Deriv สำเร็จสมบูรณ์ รวม ${fetchedTrades.length} รายการ!`, 'success');

            if (fetchedTrades.length === 0) {
              appendLog(`⚠️ ไม่พบรายการเทรดใน Deriv สำหรับวันที่ ${dateVal}`, 'warn');
              resolve(false);
              return;
            }

            // Save to MySQL derivTradeHistory
            try {
              appendLog(`💾 กำลังส่งข้อมูล ${fetchedTrades.length} trades ไปบันทึกลงตาราง derivTradeHistory (serverCode: ${serverCode})...`, 'info');
              const saveRes = await fetch(API_SAVE_TRADE_HIST_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  serverCode: serverCode,
                  trades: fetchedTrades
                })
              });
              const saveJson = await saveRes.json();
              if (saveJson.success) {
                appendLog(`🎉 [MySQL Commit] บันทึกประวัติการเทรด ${saveJson.count || fetchedTrades.length} รายการลงตาราง derivTradeHistory เรียบร้อย!`, 'success');
                resolve(true);
              } else {
                appendLog(`❌ บันทึกลงฐานข้อมูลไม่สำเร็จ: ${saveJson.error || 'Unknown Error'}`, 'error');
                resolve(false);
              }
            } catch (saveErr) {
              appendLog(`❌ เกิดข้อผิดพลาดในการบันทึกลงฐานข้อมูล: ${saveErr.message}`, 'error');
              resolve(false);
            }
          }
        }
      };

      ws.onerror = function(err) {
        appendLog('❌ WebSocket เกิดข้อผิดพลาดในการรับ-ส่งข้อมูล', 'error');
        resolve(false);
      };
    });
  }

  // Request FullAnalysisData from /getFullAnalysisData
  async function requestFullAnalysisData(vpsBaseUrl, assets, dateVal) {
    const startDt = `${dateVal}T00:00`;
    const endDt = `${dateVal}T23:59`;
    const resultsByAsset = {};
    let totalCandlesLoaded = 0;

    for (let i = 0; i < assets.length; i++) {
      const sym = assets[i];
      appendLog(`📡 [${i + 1}/${assets.length}] กำลังส่งคำขอ FullAnalysisData สำหรับ ${sym} (${dateVal})...`, 'info');

      const queryStr = `assetcode=${encodeURIComponent(sym)}&startdatetime=${startDt}&enddatetime=${endDt}&timeframe=1M`;
      const directUrl = `${vpsBaseUrl}/getFullAnalysisData?${queryStr}`;
      const proxyUrl = `${PROXY_FULL_ANALYSIS_URL}?vps_url=${encodeURIComponent(vpsBaseUrl)}&${queryStr}`;

      let dataJson = null;

      // 1. ลองเรียก Direct ไปยัง VPS ก่อน
      try {
        const res = await fetch(directUrl, { method: 'GET', signal: AbortSignal.timeout(15000) });
        if (res.ok) {
          dataJson = await res.json();
        } else {
          throw new Error(`HTTP ${res.status}`);
        }
      } catch (directErr) {
        appendLog(`ℹ️ Direct fetch ไม่สำเร็จ (${directErr.message}) -> กำลังสลับไปใช้ PHP Proxy...`, 'info');
        try {
          const pRes = await fetch(proxyUrl, { method: 'GET' });
          if (pRes.ok) {
            dataJson = await pRes.json();
          } else {
            const errTxt = await pRes.text();
            throw new Error(`Proxy HTTP ${pRes.status}: ${errTxt}`);
          }
        } catch (proxyErr) {
          appendLog(`❌ ไม่สามารถดึง FullAnalysisData สำหรับ ${sym}: ${proxyErr.message}`, 'error');
          continue;
        }
      }

      if (dataJson && (dataJson.status === 'success' || dataJson.data || Array.isArray(dataJson))) {
        resultsByAsset[sym] = dataJson;
        const count = Array.isArray(dataJson) ? dataJson.length : (dataJson.count || (dataJson.data ? dataJson.data.length : 0));
        totalCandlesLoaded += count;
        appendLog(`✅ ได้รับข้อมูล ${sym} จำนวน ${count.toLocaleString()} แท่งเทียนเรียบร้อย!`, 'success');
      } else {
        const msg = (dataJson && (dataJson.message || dataJson.error)) || 'ไม่พบข้อมูลแท่งเทียน';
        appendLog(`⚠️ ผลลัพธ์ ${sym}: ${msg}`, 'warn');
      }
    }

    // Build Final Payload
    let combinedPayload;
    if (assets.length === 1 && resultsByAsset[assets[0]]) {
      combinedPayload = resultsByAsset[assets[0]];
    } else {
      combinedPayload = {
        status: "success",
        date: dateVal,
        assets: assets,
        total_candles: totalCandlesLoaded,
        generated_at: new Date().toISOString(),
        data: resultsByAsset
      };
    }

    // Put into Textarea
    textarea.value = JSON.stringify(combinedPayload, null, 2);
    updateTextareaStats();

    // Render Table and KPIs
    renderAnalysisResults(combinedPayload, assets);

    // Switch to Textarea Tab
    switchTab('tab-textarea');

    statusBadge.textContent = `🟢 Finished (${totalCandlesLoaded.toLocaleString()} candles)`;
    statusBadge.style.color = '#34d399';
    appendLog(`🎉 ดึงข้อมูล FullAnalysis Data ครบถ้วนแล้ว! รวม ${totalCandlesLoaded.toLocaleString()} แท่งเทียน จาก ${assets.length} สินทรัพย์`, 'success');
  }

  // Helper: Resolve which assets to process based on Asset Scope
  async function resolveAssets(selectedVps, serverCode, dateVal) {
    const scope = assetScopeSelect ? assetScopeSelect.value : 'all_standard';

    if (scope === 'all_standard') {
      appendLog(`🎯 ขอบเขต: ทุก Asset มาตรฐาน (${STANDARD_ALL_ASSETS.length} รายการ)`, 'info');
      return [...STANDARD_ALL_ASSETS];
    }

    if (scope === '1hz_all') {
      appendLog(`⚡ ขอบเขต: สินทรัพย์ตระกูล 1HZ ทั้งหมด (${STANDARD_1HZ_ASSETS.length} รายการ)`, 'info');
      return [...STANDARD_1HZ_ASSETS];
    }

    if (scope === 'custom') {
      const txt = (customAssetsInput.value || '').trim();
      if (!txt) {
        alert('กรุณาระบุชื่อ Asset เช่น 1HZ75V, 1HZ100V');
        return null;
      }
      const list = txt.split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
      appendLog(`✏️ ขอบเขต: ระบุเอง (${list.length} รายการ): [${list.join(', ')}]`, 'info');
      return list;
    }

    // Default: 'trade_history' scope
    appendLog(`🔍 กำลังค้นหารายการ asset ของ VPS: ${selectedVps.vpsCode} (${selectedVps.vpsName}) ในวันที่ ${dateVal} จากตาราง derivTradeHistory...`, 'info');
    const checkRes = await fetch(`${API_DERIV_TRADE_HIST_URL}?action=get_assets&serverCode=${serverCode}&date=${dateVal}`);
    if (!checkRes.ok) throw new Error(`HTTP ${checkRes.status}`);
    const checkData = await checkRes.json();

    let assets = checkData.assets || [];
    let totalTrades = checkData.total_trades || 0;

    if (totalTrades === 0 || assets.length === 0) {
      appendLog(`⚠️ ไม่พบข้อมูล trade_history ของ VPS ${selectedVps.vpsCode} ในวันที่ ${dateVal}`, 'warn');
      const confirmLoad = confirm(
        `ไม่พบข้อมูล Trade History ของ VPS "${selectedVps.vpsCode}" ในวันที่ ${dateVal}\n\n` +
        `• กด [ตกลง (OK)] เพื่อให้ระบบดึงข้อมูลจาก Deriv.com ให้ก่อน\n` +
        `• หรือกด [ยกเลิก (Cancel)] เพื่อใช้รายชื่อสินทรัพย์มาตรฐาน 10 ตัวแทน`
      );

      if (confirmLoad) {
        appendLog(`🚀 ผู้ใช้ยืนยัน: เริ่มกระบวนการ Fetch & Save จาก Deriv สำหรับวันที่ ${dateVal}...`, 'info');
        statusBadge.textContent = '⏳ Fetching Deriv...';
        statusBadge.style.color = '#38bdf8';

        const fetchOk = await fetchAndSaveDerivTrades(selectedVps, dateVal);
        if (!fetchOk) {
          alert('ไม่สามารถดึงข้อมูลประวัติการเทรดจาก Deriv ได้');
          return null;
        }

        const reCheckRes = await fetch(`${API_DERIV_TRADE_HIST_URL}?action=get_assets&serverCode=${serverCode}&date=${dateVal}`);
        const reCheckData = await reCheckRes.json();
        assets = reCheckData.assets || [];
      } else {
        appendLog('ℹ️ ผู้ใช้เลือกใช้รายการสินทรัพย์มาตรฐาน 10 รายการแทน', 'info');
        return [...STANDARD_ALL_ASSETS];
      }
    }

    return assets;
  }

  // Helper: Save candles array to MySQL (full_analysis_data, analysis_pk_trend, analysis_smc)
  async function saveCandlesBatch(serverCode, candles) {
    if (!candles || candles.length === 0) return { success: true, count: 0 };
    const res = await fetch(API_SAVE_FULL_ANALYSIS_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        serverCode: String(serverCode),
        candles: candles
      })
    });
    if (!res.ok) {
      const errTxt = await res.text();
      throw new Error(`Save API HTTP ${res.status}: ${errTxt}`);
    }
    return await res.json();
  }

  // Helper: Extract candles array from diverse API response shapes
  function extractCandlesArray(dataJson) {
    if (!dataJson) return [];
    if (Array.isArray(dataJson)) return dataJson;
    if (Array.isArray(dataJson.data)) return dataJson.data;
    if (typeof dataJson === 'object') {
      for (const k in dataJson) {
        if (Array.isArray(dataJson[k])) return dataJson[k];
      }
    }
    return [];
  }

  // 1. ACTION: โหลดและบันทึก (Load & Save to MySQL) ให้ครบทุก Asset
  async function handleLoadAndSave() {
    const vpsId = parseInt(vpsSelect.value, 10);
    const selectedVps = vpsList.find(v => v.id === vpsId) || null;

    if (!selectedVps) {
      alert('กรุณาเลือก VPS Server ก่อนดำเนินการ');
      return;
    }

    const dateVal = dateInput.value;
    if (!dateVal) {
      alert('กรุณาระบุวันที่วิเคราะห์ (Analysis Date)');
      return;
    }

    const serverCode = selectedVps.vpsCode || String(selectedVps.id) || '2';

    if (loadAndSaveBtn) loadAndSaveBtn.disabled = true;
    if (loadDataBtn) loadDataBtn.disabled = true;
    statusBadge.textContent = '⏳ Loading & Saving...';
    statusBadge.style.color = '#10b981';

    appendLog('====================================================', 'info');
    appendLog(`🚀 [เริ่มต้น: โหลดและบันทึก] สำหรับ VPS: ${serverCode} (${selectedVps.vpsName}) ประจำวันที่ ${dateVal}...`, 'info');

    try {
      const assets = await resolveAssets(selectedVps, serverCode, dateVal);
      if (!assets || assets.length === 0) {
        appendLog('⚠️ ไม่พบรายการสินทรัพย์สำหรับประมวลผล', 'warn');
        statusBadge.textContent = '⚪ Standby';
        statusBadge.style.color = '#38bdf8';
        return;
      }

      appendLog(`🎯 เริ่มดำเนินการสำหรับ ${assets.length} สินทรัพย์: [${assets.join(', ')}]`, 'info');

      let vpsBaseUrl = selectedVps.url || (selectedVps.publicIP ? ('http://' + selectedVps.publicIP + (selectedVps.portno ? ':' + selectedVps.portno : '')) : 'http://127.0.0.1:3000');
      if (!/^https?:\/\//i.test(vpsBaseUrl)) vpsBaseUrl = 'http://' + vpsBaseUrl;
      vpsBaseUrl = vpsBaseUrl.replace(/\/+$/, '');

      const startDt = `${dateVal}T00:00`;
      const endDt = `${dateVal}T23:59`;
      const resultsByAsset = {};
      let totalCandlesLoaded = 0;
      let totalCandlesSaved = 0;

      for (let i = 0; i < assets.length; i++) {
        const sym = assets[i];
        appendLog(`📡 [${i + 1}/${assets.length}] ร้องขอ /getFullAnalysisData สำหรับ ${sym}...`, 'info');

        const queryStr = `assetcode=${encodeURIComponent(sym)}&startdatetime=${startDt}&enddatetime=${endDt}&timeframe=1M`;
        const directUrl = `${vpsBaseUrl}/getFullAnalysisData?${queryStr}`;
        const proxyUrl = `${PROXY_FULL_ANALYSIS_URL}?vps_url=${encodeURIComponent(vpsBaseUrl)}&${queryStr}`;

        let dataJson = null;

        // 1. ลองเรียก Direct ไปยัง VPS ก่อน
        try {
          const res = await fetch(directUrl, { method: 'GET', signal: AbortSignal.timeout(15000) });
          if (res.ok) {
            dataJson = await res.json();
          } else {
            throw new Error(`HTTP ${res.status}`);
          }
        } catch (directErr) {
          appendLog(`ℹ️ Direct fetch ${sym} ไม่สำเร็จ (${directErr.message}) -> กำลังใช้ PHP Proxy...`, 'info');
          try {
            const pRes = await fetch(proxyUrl, { method: 'GET' });
            if (pRes.ok) {
              dataJson = await pRes.json();
            } else {
              const errTxt = await pRes.text();
              throw new Error(`Proxy HTTP ${pRes.status}: ${errTxt}`);
            }
          } catch (proxyErr) {
            appendLog(`❌ ไม่สามารถดึงข้อมูล ${sym}: ${proxyErr.message}`, 'error');
            continue;
          }
        }

        const candleList = extractCandlesArray(dataJson);
        if (candleList.length > 0) {
          resultsByAsset[sym] = candleList;
          totalCandlesLoaded += candleList.length;

          // บันทึกลง MySQL ทันที
          appendLog(`💾 [${i + 1}/${assets.length}] กำลังบันทึก ${sym} จำนวน ${candleList.length.toLocaleString()} แท่งเทียนลง MySQL...`, 'info');
          try {
            const saveRes = await saveCandlesBatch(serverCode, candleList);
            const savedCount = saveRes.count || candleList.length;
            totalCandlesSaved += savedCount;
            appendLog(`🎉 [MySQL Commit] บันทึก ${sym} เรียบร้อย ${savedCount.toLocaleString()} รายการ (full_analysis_data, analysis_pk_trend, analysis_smc)`, 'success');
          } catch (saveErr) {
            appendLog(`❌ บันทึก ${sym} ไม่สำเร็จ: ${saveErr.message}`, 'error');
          }
        } else {
          const msg = (dataJson && (dataJson.message || dataJson.error)) || 'ไม่พบข้อมูลแท่งเทียน';
          appendLog(`⚠️ ผลลัพธ์ ${sym}: ${msg}`, 'warn');
        }
      }

      // Build Combined Payload
      const combinedPayload = {
        status: "success",
        serverCode: serverCode,
        date: dateVal,
        assets: assets,
        total_candles: totalCandlesLoaded,
        total_saved: totalCandlesSaved,
        generated_at: new Date().toISOString(),
        data: resultsByAsset
      };

      textarea.value = JSON.stringify(combinedPayload, null, 2);
      updateTextareaStats();
      renderAnalysisResults(combinedPayload, assets);
      switchTab('tab-overview');

      statusBadge.textContent = `🟢 Saved (${totalCandlesSaved.toLocaleString()} candles)`;
      statusBadge.style.color = '#34d399';
      appendLog(`🎉 [เสร็จสมบูรณ์] โหลดและบันทึกข้อมูลครบทุก Asset เรียบร้อย! รวม ${totalCandlesSaved.toLocaleString()} แท่งเทียน จาก ${assets.length} สินทรัพย์`, 'success');
      alert(`โหลดและบันทึกข้อมูลครบทุก Asset เรียบร้อยแล้ว!\n\n• Server: ${serverCode}\n• วันที่: ${dateVal}\n• บันทึกสำเร็จ: ${totalCandlesSaved.toLocaleString()} แท่งเทียน\n• ลงตาราง: full_analysis_data, analysis_pk_trend, analysis_smc`);

    } catch (err) {
      appendLog(`❌ เกิดข้อผิดพลาดในการโหลดและบันทึก: ${err.message}`, 'error');
      alert(`เกิดข้อผิดพลาด: ${err.message}`);
      statusBadge.textContent = '🔴 Error';
      statusBadge.style.color = '#f87171';
    } finally {
      if (loadAndSaveBtn) loadAndSaveBtn.disabled = false;
      if (loadDataBtn) loadDataBtn.disabled = false;
    }
  }

  // 2. ACTION: โหลดดูข้อมูลตัวอย่าง (Preview Only)
  async function handleLoadData() {
    const vpsId = parseInt(vpsSelect.value, 10);
    const selectedVps = vpsList.find(v => v.id === vpsId) || null;

    if (!selectedVps) {
      alert('กรุณาเลือก VPS Server ก่อนดำเนินการ');
      return;
    }

    const dateVal = dateInput.value;
    if (!dateVal) {
      alert('กรุณาระบุวันที่วิเคราะห์ (Analysis Date)');
      return;
    }

    const serverCode = selectedVps.vpsCode || String(selectedVps.id) || '2';

    if (loadDataBtn) loadDataBtn.disabled = true;
    if (loadAndSaveBtn) loadAndSaveBtn.disabled = true;
    statusBadge.textContent = '⏳ Loading Preview...';
    statusBadge.style.color = '#fbbf24';

    appendLog('====================================================', 'info');
    appendLog(`🔍 [โหลดดูตัวอย่าง] สำหรับ VPS: ${serverCode} ในวันที่ ${dateVal}...`, 'info');

    try {
      const assets = await resolveAssets(selectedVps, serverCode, dateVal);
      if (!assets || assets.length === 0) {
        statusBadge.textContent = '⚪ Standby';
        statusBadge.style.color = '#38bdf8';
        return;
      }

      let vpsBaseUrl = selectedVps.url || (selectedVps.publicIP ? ('http://' + selectedVps.publicIP + (selectedVps.portno ? ':' + selectedVps.portno : '')) : 'http://127.0.0.1:3000');
      if (!/^https?:\/\//i.test(vpsBaseUrl)) vpsBaseUrl = 'http://' + vpsBaseUrl;
      vpsBaseUrl = vpsBaseUrl.replace(/\/+$/, '');

      appendLog(`🚀 กำลังร้องขอ fullAnalysisData จาก API: ${vpsBaseUrl}/getFullAnalysisData...`, 'info');
      await requestFullAnalysisData(vpsBaseUrl, assets, dateVal);

    } catch (err) {
      appendLog(`❌ เกิดข้อผิดพลาดในการโหลดข้อมูล: ${err.message}`, 'error');
      alert(`เกิดข้อผิดพลาด: ${err.message}`);
      statusBadge.textContent = '🔴 Error';
      statusBadge.style.color = '#f87171';
    } finally {
      if (loadDataBtn) loadDataBtn.disabled = false;
      if (loadAndSaveBtn) loadAndSaveBtn.disabled = false;
    }
  }

  // 3. ACTION: บันทึกข้อมูลที่อยู่ใน Textarea ลง MySQL โดยตรง
  async function handleSaveTextareaToDb() {
    const text = textarea.value.trim();
    if (!text) {
      alert('ไม่มีข้อมูลในช่อง Textarea กรุณาโหลดข้อมูลหรือวาง JSON ก่อน');
      return;
    }

    let parsed;
    try {
      parsed = JSON.parse(text);
    } catch (e) {
      alert('ข้อมูลใน Textarea ไม่ใช่ JSON ที่ถูกต้อง: ' + e.message);
      return;
    }

    let allCandles = [];
    if (Array.isArray(parsed)) {
      allCandles = parsed;
    } else if (parsed && Array.isArray(parsed.data)) {
      allCandles = parsed.data;
    } else if (parsed && typeof parsed === 'object') {
      const pool = parsed.data || parsed;
      for (const k in pool) {
        if (Array.isArray(pool[k])) {
          allCandles = allCandles.concat(pool[k]);
        } else if (pool[k] && Array.isArray(pool[k].data)) {
          allCandles = allCandles.concat(pool[k].data);
        }
      }
    }

    if (allCandles.length === 0) {
      alert('ไม่พบข้อมูลรายการแท่งเทียนที่สามารถบันทึกได้ใน JSON');
      return;
    }

    const vpsId = parseInt(vpsSelect.value, 10);
    const selectedVps = vpsList.find(v => v.id === vpsId) || null;
    const serverCode = (selectedVps && (selectedVps.vpsCode || selectedVps.id)) || (parsed.serverCode || '2');

    if (!confirm(`คุณต้องการบันทึกข้อมูล ${allCandles.length.toLocaleString()} แท่งเทียน (serverCode: ${serverCode}) ลงตาราง MySQL หรือไม่?`)) {
      return;
    }

    saveTextareaToDbBtn.disabled = true;
    appendLog(`💾 กำลังส่งข้อมูล ${allCandles.length.toLocaleString()} แท่งเทียนจาก Textarea ไปบันทึกลง MySQL...`, 'info');

    try {
      const saveRes = await saveCandlesBatch(serverCode, allCandles);
      const savedCount = saveRes.count || allCandles.length;
      appendLog(`🎉 [MySQL Commit] บันทึกข้อมูล ${savedCount.toLocaleString()} แท่งเทียนจาก Textarea ลงตารางเรียบร้อย!`, 'success');
      alert(`บันทึกข้อมูล ${savedCount.toLocaleString()} แท่งเทียนลงตารางเรียบร้อยแล้ว!`);
    } catch (err) {
      appendLog(`❌ บันทึกลง MySQL ล้มเหลว: ${err.message}`, 'error');
      alert(`บันทึกลง MySQL ล้มเหลว: ${err.message}`);
    } finally {
      saveTextareaToDbBtn.disabled = false;
    }
  }

  // Button Listeners
  if (loadAndSaveBtn) loadAndSaveBtn.addEventListener('click', handleLoadAndSave);
  if (loadDataBtn) loadDataBtn.addEventListener('click', handleLoadData);
  if (saveTextareaToDbBtn) saveTextareaToDbBtn.addEventListener('click', handleSaveTextareaToDb);

  // Init
  initData();
})();
</script>
