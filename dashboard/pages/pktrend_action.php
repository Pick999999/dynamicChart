<?php
/**
 * pktrend_action.php
 * pkTrend Strategy Case Codes CRUD Management
 * Vision UI Dark Glassmorphism Design
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-pktrend-action">
  <style>
    /* Scoped Styles for pkTrend Action */
    .pktrend-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .pktrend-stat-card {
      background: var(--bg-card, rgba(15, 23, 42, 0.7));
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
      border-radius: var(--radius-md, 16px);
      padding: 20px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card, 0 10px 30px rgba(0, 0, 0, 0.3));
      position: relative;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .pktrend-stat-card:hover {
      transform: translateY(-2px);
      border-color: rgba(255, 255, 255, 0.25);
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
    }
    .pktrend-stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: var(--card-glow, linear-gradient(90deg, #0075ff, #00d4ff));
      opacity: 0.9;
    }
    .pktrend-stat-card.active-filter {
      border-color: #00d4ff;
      box-shadow: 0 0 15px rgba(0, 212, 255, 0.25);
    }
    .pktrend-stat-val {
      font-size: 28px;
      font-weight: 800;
      color: var(--text-primary, #fff);
      margin-top: 4px;
      line-height: 1.2;
    }
    .pktrend-stat-label {
      font-size: 13px;
      color: var(--text-tertiary, #a0aec0);
      font-weight: 600;
      letter-spacing: 0.3px;
    }
    .pktrend-stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      flex-shrink: 0;
    }
    .pktrend-stat-icon svg {
      width: 24px;
      height: 24px;
      fill: currentColor;
    }

    /* Action bar controls */
    .pktrend-action-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 20px;
    }
    .pktrend-filter-group {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      flex-wrap: wrap;
    }
    .pktrend-search-box {
      display: flex;
      align-items: center;
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.12));
      border-radius: var(--radius-sm, 10px);
      padding: 8px 14px;
      gap: 10px;
      min-width: 240px;
      flex: 1;
      max-width: 360px;
      transition: all 0.2s;
    }
    .pktrend-search-box:focus-within {
      border-color: var(--accent-blue, #0075ff);
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
    }
    .pktrend-search-box svg {
      width: 18px;
      height: 18px;
      fill: var(--text-tertiary, #a0aec0);
      flex-shrink: 0;
    }
    .pktrend-search-box input {
      background: transparent;
      border: none;
      outline: none;
      color: #fff;
      font-size: 13px;
      width: 100%;
    }
    .pktrend-select {
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.12));
      border-radius: var(--radius-sm, 10px);
      padding: 8px 12px;
      color: var(--text-primary, #fff);
      font-size: 13px;
      outline: none;
      cursor: pointer;
      transition: border-color 0.2s;
    }
    .pktrend-select:focus {
      border-color: var(--accent-blue, #0075ff);
    }
    .pktrend-btn-add {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm, 10px);
      padding: 9px 18px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0, 117, 255, 0.35);
      transition: all 0.2s ease;
    }
    .pktrend-btn-add:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(0, 117, 255, 0.5);
    }
    .pktrend-btn-add svg {
      width: 16px;
      height: 16px;
      fill: currentColor;
    }

    /* Download strategy_case_codes Button */
    .pktrend-btn-download {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, rgba(1, 181, 116, 0.2) 0%, rgba(0, 212, 255, 0.2) 100%);
      border: 1px solid rgba(1, 181, 116, 0.45);
      color: #00f2fe;
      border-radius: var(--radius-sm, 10px);
      padding: 9px 18px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(1, 181, 116, 0.25);
      transition: all 0.2s ease;
    }
    .pktrend-btn-download:hover {
      transform: translateY(-1px);
      background: linear-gradient(135deg, #01b574 0%, #00d4ff 100%);
      border-color: #00d4ff;
      color: #fff;
      box-shadow: 0 6px 20px rgba(0, 212, 255, 0.45);
    }
    .pktrend-btn-download:disabled {
      opacity: 0.65;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    .pktrend-btn-download svg {
      width: 16px;
      height: 16px;
      fill: currentColor;
    }

    @keyframes pktrend-spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* 3 Action Buttons in Table & Modal */
    .pktrend-action-toggles {
      display: inline-flex;
      align-items: center;
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 3px;
      gap: 3px;
      user-select: none;
    }
    .btn-act-opt {
      border: none;
      background: transparent;
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.5px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      color: #94a3b8;
      transition: all 0.2s ease;
    }
    .btn-act-opt svg {
      width: 12px;
      height: 12px;
      fill: currentColor;
    }
    .btn-act-opt:hover:not(.active) {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
    }

    /* Active States for the 3 Buttons */
    .btn-act-opt.act-call.active {
      background: linear-gradient(135deg, #01b574 0%, #008f5a 100%);
      color: #fff;
      box-shadow: 0 2px 8px rgba(1, 181, 116, 0.45);
    }
    .btn-act-opt.act-put.active {
      background: linear-gradient(135deg, #e31a1a 0%, #b31414 100%);
      color: #fff;
      box-shadow: 0 2px 8px rgba(227, 26, 26, 0.45);
    }
    .btn-act-opt.act-idle.active {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: #fff;
      box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
    }

    /* Large 3 Button Selector in Modal */
    .modal-action-selector {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 10px;
      margin-top: 4px;
    }
    .modal-btn-act {
      background: rgba(15, 28, 70, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 10px;
      padding: 10px 14px;
      color: #94a3b8;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s ease;
    }
    .modal-btn-act svg {
      width: 16px;
      height: 16px;
      fill: currentColor;
    }
    .modal-btn-act:hover:not(.active) {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.2);
    }
    .modal-btn-act.btn-call.active {
      background: linear-gradient(135deg, #01b574 0%, #008f5a 100%);
      border-color: #01b574;
      color: #fff;
      box-shadow: 0 4px 14px rgba(1, 181, 116, 0.4);
    }
    .modal-btn-act.btn-put.active {
      background: linear-gradient(135deg, #e31a1a 0%, #b31414 100%);
      border-color: #e31a1a;
      color: #fff;
      box-shadow: 0 4px 14px rgba(227, 26, 26, 0.4);
    }
    .modal-btn-act.btn-idle.active {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      border-color: #f59e0b;
      color: #fff;
      box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
    }

    /* Badges */
    .badge-code-no {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 8px;
      background: rgba(0, 117, 255, 0.15);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.3);
      font-weight: 700;
      font-family: monospace;
      font-size: 12px;
    }
    .badge-case-code {
      font-family: monospace;
      font-weight: 700;
      font-size: 13px;
      color: #fff;
      letter-spacing: 0.5px;
    }
    .badge-category {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.3px;
    }
    .badge-category.core {
      background: rgba(0, 117, 255, 0.15);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.3);
    }
    .badge-category.extended {
      background: rgba(117, 81, 233, 0.15);
      color: #b794f4;
      border: 1px solid rgba(183, 148, 244, 0.3);
    }
    .badge-trend {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 2px 7px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
    }
    .badge-trend.uptrend {
      background: rgba(1, 181, 116, 0.12);
      color: #01b574;
    }
    .badge-trend.downtrend {
      background: rgba(227, 26, 26, 0.12);
      color: #ff5e5e;
    }
    .badge-trend.sideways {
      background: rgba(245, 166, 35, 0.12);
      color: #f5a623;
    }
    .badge-trend.rejected {
      background: rgba(236, 72, 153, 0.12);
      color: #ec4899;
    }

    /* Actions column buttons */
    .pktrend-row-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .btn-row-action {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 6px;
      padding: 5px 8px;
      color: var(--text-secondary, #cbd5e1);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }
    .btn-row-action svg {
      width: 14px;
      height: 14px;
      fill: currentColor;
    }
    .btn-row-action.edit:hover {
      background: rgba(0, 117, 255, 0.2);
      border-color: #0075ff;
      color: #00d4ff;
    }
    .btn-row-action.delete:hover {
      background: rgba(227, 26, 26, 0.2);
      border-color: #e31a1a;
      color: #ff5e5e;
    }

    /* Modal Backdrop & Dialog */
    .pktrend-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(4, 7, 24, 0.85);
      backdrop-filter: blur(8px);
      z-index: 999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      transition: opacity 0.25s ease;
    }
    .pktrend-modal-backdrop.active {
      display: flex;
      opacity: 1;
    }
    .pktrend-modal {
      background: linear-gradient(135deg, rgba(16, 26, 68, 0.98) 0%, rgba(6, 11, 40, 0.99) 100%);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: var(--radius-lg, 20px);
      width: 100%;
      max-width: 660px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7);
      overflow: hidden;
      transform: scale(0.95);
      transition: transform 0.25s ease;
    }
    .pktrend-modal-backdrop.active .pktrend-modal {
      transform: scale(1);
    }
    .pktrend-modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .pktrend-modal-title {
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .pktrend-modal-close {
      background: transparent;
      border: none;
      color: var(--text-tertiary, #a0aec0);
      cursor: pointer;
      font-size: 22px;
      line-height: 1;
      padding: 4px 8px;
      border-radius: 6px;
      transition: all 0.2s;
    }
    .pktrend-modal-close:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
    }
    .pktrend-modal-body {
      padding: 24px;
      max-height: 75vh;
      overflow-y: auto;
    }
    .pktrend-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    .pktrend-form-full {
      grid-column: span 2;
    }
    .pktrend-field-label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary, #cbd5e1);
      margin-bottom: 6px;
    }
    .pktrend-field-label span.req {
      color: #ff5e5e;
    }
    .pktrend-input, .pktrend-textarea {
      width: 100%;
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--radius-sm, 10px);
      padding: 10px 14px;
      color: #fff;
      font-size: 13px;
      font-family: inherit;
      transition: all 0.2s;
      box-sizing: border-box;
    }
    .pktrend-input:focus, .pktrend-textarea:focus {
      border-color: var(--accent-blue, #0075ff);
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
      outline: none;
      background: rgba(15, 28, 70, 0.95);
    }
    .pktrend-textarea {
      resize: vertical;
      min-height: 80px;
    }
    .pktrend-modal-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      background: rgba(0, 0, 0, 0.2);
    }
    .btn-secondary {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #cbd5e1;
      padding: 9px 18px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
    }
    .btn-primary {
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      border: none;
      color: #fff;
      padding: 9px 20px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0, 117, 255, 0.35);
      transition: all 0.2s;
    }
    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(0, 117, 255, 0.5);
    }

    /* Confirm delete box */
    .pktrend-confirm-box {
      max-width: 440px;
      text-align: center;
      padding: 32px 24px;
    }
    .pktrend-confirm-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: rgba(227, 26, 26, 0.15);
      color: #e31a1a;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .pktrend-confirm-icon svg {
      width: 32px;
      height: 32px;
      fill: currentColor;
    }

    /* Toast */
    #pktrendToast {
      position: fixed;
      bottom: 28px;
      right: 28px;
      background: rgba(6, 11, 40, 0.95);
      border: 1px solid rgba(0, 117, 255, 0.4);
      color: #fff;
      padding: 12px 24px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 13px;
      z-index: 9999;
      transform: translateY(100px);
      opacity: 0;
      pointer-events: none;
      transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }
    #pktrendToast.show {
      transform: translateY(0);
      opacity: 1;
      pointer-events: auto;
    }
    #pktrendToast.success {
      border-color: rgba(1, 181, 116, 0.6);
    }
    #pktrendToast.error {
      border-color: rgba(227, 26, 26, 0.6);
    }
  </style>

  <!-- Page Header -->
  <div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">
      <span style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, rgba(1,181,116,0.2) 0%, rgba(0,212,255,0.2) 100%); border:1px solid rgba(1,181,116,0.4); color:#01b574;">
        <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:currentColor;"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
      </span>
      pkTrend Strategy Action Manager
    </h2>
    <p style="color: var(--text-tertiary, #a0aec0); font-size: 13px; margin: 0;">
      จัดการรูปแบบแท่งเทียนและกำหนด Action กลยุทธ์ (<strong>CALL</strong> / <strong>PUT</strong> / <strong>IDLE</strong>) สำหรับตาราง <code>strategy_case_codes</code>
    </p>
  </div>

  <!-- Summary Stat Cards -->
  <div class="pktrend-stats-grid">
    <!-- Total Card -->
    <div class="pktrend-stat-card" id="card-filter-all" onclick="pkTrendManager.filterByAction('')" style="--card-glow: linear-gradient(90deg, #0075ff, #00d4ff);">
      <div>
        <div class="pktrend-stat-label">TOTAL CASE CODES</div>
        <div class="pktrend-stat-val" id="stat-total">0</div>
      </div>
      <div class="pktrend-stat-icon" style="background: rgba(0, 117, 255, 0.15); color: #00d4ff;">
        <svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
      </div>
    </div>

    <!-- CALL Card -->
    <div class="pktrend-stat-card" id="card-filter-call" onclick="pkTrendManager.filterByAction('call')" style="--card-glow: linear-gradient(90deg, #01b574, #00d4ff);">
      <div>
        <div class="pktrend-stat-label">CALL SIGNALS</div>
        <div class="pktrend-stat-val" style="color: #01b574;" id="stat-call">0</div>
      </div>
      <div class="pktrend-stat-icon" style="background: rgba(1, 181, 116, 0.15); color: #01b574;">
        <svg viewBox="0 0 24 24"><path d="M7 14l5-5 5 5z"/></svg>
      </div>
    </div>

    <!-- PUT Card -->
    <div class="pktrend-stat-card" id="card-filter-put" onclick="pkTrendManager.filterByAction('put')" style="--card-glow: linear-gradient(90deg, #e31a1a, #ff5e5e);">
      <div>
        <div class="pktrend-stat-label">PUT SIGNALS</div>
        <div class="pktrend-stat-val" style="color: #ff5e5e;" id="stat-put">0</div>
      </div>
      <div class="pktrend-stat-icon" style="background: rgba(227, 26, 26, 0.15); color: #ff5e5e;">
        <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
      </div>
    </div>

    <!-- IDLE Card -->
    <div class="pktrend-stat-card" id="card-filter-idle" onclick="pkTrendManager.filterByAction('idle')" style="--card-glow: linear-gradient(90deg, #f59e0b, #fbbf24);">
      <div>
        <div class="pktrend-stat-label">IDLE / WAIT</div>
        <div class="pktrend-stat-val" style="color: #f59e0b;" id="stat-idle">0</div>
      </div>
      <div class="pktrend-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
        <svg viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
      </div>
    </div>
  </div>

  <!-- Action Bar / Filters -->
  <div class="pktrend-action-bar">
    <div class="pktrend-filter-group">
      <!-- Search Input -->
      <div class="pktrend-search-box">
        <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        <input type="text" id="pktrend-search-input" placeholder="ค้นหา Code No, Case Code, Trend, คำอธิบาย..." oninput="pkTrendManager.onSearchInput(this.value)">
      </div>

      <!-- Action Filter -->
      <select class="pktrend-select" id="filter-action-select" onchange="pkTrendManager.onActionSelectChange(this.value)">
        <option value="">ทุก Action (All)</option>
        <option value="call">เฉพาะ CALL</option>
        <option value="put">เฉพาะ PUT</option>
        <option value="idle">เฉพาะ IDLE</option>
        <option value="none">ยังไม่ระบุ (Unassigned)</option>
      </select>

      <!-- Category Filter -->
      <select class="pktrend-select" id="filter-category-select" onchange="pkTrendManager.onCategoryChange(this.value)">
        <option value="">ทุก Category</option>
      </select>

      <!-- Group Filter -->
      <select class="pktrend-select" id="filter-group-select" onchange="pkTrendManager.onGroupChange(this.value)">
        <option value="">ทุก Group</option>
      </select>

      <!-- Refresh Button -->
      <button class="btn-secondary" onclick="pkTrendManager.loadData()" title="รีเฟรชข้อมูล" style="padding: 8px 12px; display:inline-flex; align-items:center; gap:6px;">
        <svg viewBox="0 0 24 24" style="width:15px; height:15px; fill:currentColor;"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
        รีเฟรช
      </button>
    </div>

    <!-- Action Buttons Group: Download & Add -->
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
      <!-- Download strategy_case_codes Button -->
      <button class="pktrend-btn-download" id="btn-download-strategy-cases" onclick="pkTrendManager.downloadStrategyCaseCodes()" title="ดาวน์โหลดข้อมูลจากตาราง strategy_case_codes เป็นไฟล์ JSON">
        <svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
        <span>download strategy_case_codes</span>
      </button>

      <!-- Add New Case Code Button -->
      <button class="pktrend-btn-add" onclick="pkTrendManager.openAddModal()">
        <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        <span>เพิ่ม Case Code ใหม่</span>
      </button>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card" style="padding: 0; overflow: hidden; border-radius: 16px;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.08)); display:flex; justify-content:space-between; align-items:center;">
      <span style="font-weight: 700; color: #fff; font-size: 14px;">รายการรูปแบบแท่งเทียน (Strategy Case Codes)</span>
      <span id="pktrend-row-count-badge" style="font-size: 12px; color: var(--text-tertiary, #a0aec0); background: rgba(255,255,255,0.05); padding: 3px 10px; border-radius: 20px;">0 รายการ</span>
    </div>

    <div class="table-container" style="overflow-x: auto;">
      <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr>
            <th style="width: 60px; text-align: center;">#</th>
            <th style="width: 160px;">Case Code</th>
            <th style="width: 220px; text-align: center;">Action (3 Buttons)</th>
            <th style="width: 180px;">Case Description</th>
            <th style="width: 100px;">Category</th>
            <th style="width: 100px;">Group</th>
            <th style="width: 100px;">Trend</th>
            <th>Description</th>
            <th style="width: 90px; text-align: center;">จัดการ</th>
          </tr>
        </thead>
        <tbody id="pktrend-table-body">
          <tr>
            <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-tertiary);">
              <div class="spinner" style="margin: 0 auto 12px;"></div>
              กำลังโหลดข้อมูล strategy_case_codes...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add / Edit Modal -->
  <div class="pktrend-modal-backdrop" id="pktrendModalBackdrop">
    <div class="pktrend-modal">
      <div class="pktrend-modal-header">
        <div class="pktrend-modal-title" id="modalTitle">
          <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:var(--accent-blue, #0075ff);"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
          <span>แก้ไข Case Code</span>
        </div>
        <button class="pktrend-modal-close" onclick="pkTrendManager.closeModal()">&times;</button>
      </div>

      <form id="pktrendForm" onsubmit="pkTrendManager.saveForm(event)">
        <input type="hidden" id="form-id" name="id" value="">
        <!-- Dedicated hidden input for action value, controlled by the 3 buttons -->
        <input type="hidden" id="form-action" name="action" value="idle">

        <div class="pktrend-modal-body">
          <div class="pktrend-form-grid">

            <!-- Code No -->
            <div>
              <label class="pktrend-field-label">Code No <span class="req">*</span></label>
              <input type="number" class="pktrend-input" id="form-code-no" name="code_no" required min="1" placeholder="เช่น 1, 2, 28">
            </div>

            <!-- Case Code -->
            <div>
              <label class="pktrend-field-label">Case Code <span class="req">*</span></label>
              <input type="text" class="pktrend-input" id="form-case-code" name="case_code" required placeholder="เช่น UP-CONFIRM, DN-CONFIRM" style="font-family: monospace; font-weight:700;">
            </div>

            <!-- 3 BUTTON ACTION SELECTOR -->
            <div class="pktrend-form-full">
              <label class="pktrend-field-label">
                Action Strategy <span class="req">*</span> 
                <span style="font-weight:normal; opacity:0.8; font-size:11px;">(เลือก Call, Put หรือ Idle สำหรับเงื่อนไขนี้)</span>
              </label>
              <div class="modal-action-selector">
                <button type="button" class="modal-btn-act btn-call" id="modal-btn-call" onclick="pkTrendManager.setModalAction('call')">
                  <svg viewBox="0 0 24 24"><path d="M7 14l5-5 5 5z"/></svg>
                  CALL
                </button>
                <button type="button" class="modal-btn-act btn-put" id="modal-btn-put" onclick="pkTrendManager.setModalAction('put')">
                  <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
                  PUT
                </button>
                <button type="button" class="modal-btn-act btn-idle" id="modal-btn-idle" onclick="pkTrendManager.setModalAction('idle')">
                  <svg viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                  IDLE
                </button>
              </div>
            </div>

            <!-- Case Desc -->
            <div class="pktrend-form-full">
              <label class="pktrend-field-label">Case Description <span class="req">*</span></label>
              <input type="text" class="pktrend-input" id="form-case-desc" name="case_desc" required placeholder="เช่น CONFIRMED_UP, SPIKE_CONTINUATION_UP">
            </div>

            <!-- Category -->
            <div>
              <label class="pktrend-field-label">Category</label>
              <input type="text" class="pktrend-input" id="form-category" name="category" list="category-datalist" placeholder="CORE, EXTENDED">
              <datalist id="category-datalist">
                <option value="CORE">
                <option value="EXTENDED">
              </datalist>
            </div>

            <!-- Group Name -->
            <div>
              <label class="pktrend-field-label">Group Name</label>
              <input type="text" class="pktrend-input" id="form-group-name" name="group_name" list="group-datalist" placeholder="Up, Down, Spike, Rejection, Sideways">
              <datalist id="group-datalist">
                <option value="Up">
                <option value="Down">
                <option value="Spike">
                <option value="Rejected">
                <option value="Sideways">
                <option value="Engulfing">
                <option value="System">
              </datalist>
            </div>

            <!-- Trend -->
            <div class="pktrend-form-full">
              <label class="pktrend-field-label">Trend</label>
              <input type="text" class="pktrend-input" id="form-trend" name="trend" list="trend-datalist" placeholder="UpTrend, DownTrend, Sideways, Rejected">
              <datalist id="trend-datalist">
                <option value="UpTrend">
                <option value="DownTrend">
                <option value="Sideways">
                <option value="Rejected">
              </datalist>
            </div>

            <!-- Description -->
            <div class="pktrend-form-full">
              <label class="pktrend-field-label">Description (คำอธิบายรายละเอียด)</label>
              <textarea class="pktrend-textarea" id="form-description" name="description" rows="3" placeholder="อธิบายพฤติกรรมแท่งเทียน ความหมาย และแนวทางเทรด..."></textarea>
            </div>

          </div>
        </div>

        <div class="pktrend-modal-footer">
          <button type="button" class="btn-secondary" onclick="pkTrendManager.closeModal()">ยกเลิก</button>
          <button type="submit" class="btn-primary" id="btnSaveSubmit">บันทึกข้อมูล</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirm Modal -->
  <div class="pktrend-modal-backdrop" id="pktrendDeleteBackdrop">
    <div class="pktrend-modal pktrend-confirm-box">
      <div class="pktrend-confirm-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
      </div>
      <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">ยืนยันการลบ Case Code?</h3>
      <p id="deleteConfirmText" style="font-size: 13px; color: var(--text-tertiary); margin-bottom: 24px;">
        คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้? การกระทำนี้ไม่สามารถย้อนกลับได้
      </p>
      <div style="display: flex; justify-content: center; gap: 12px;">
        <button type="button" class="btn-secondary" onclick="pkTrendManager.closeDeleteModal()">ยกเลิก</button>
        <button type="button" class="btn-primary" style="background: linear-gradient(135deg, #e31a1a 0%, #b31414 100%); box-shadow: 0 4px 15px rgba(227, 26, 26, 0.4);" onclick="pkTrendManager.confirmDelete()">ลบข้อมูล</button>
      </div>
    </div>
  </div>

  <!-- Toast Element -->
  <div id="pktrendToast">
    <span id="toastIcon"></span>
    <span id="toastMsg"></span>
  </div>

  <!-- Client-side Controller Script -->
  <script>
    (function () {
      const API_URL = '../php/api_strategy_case_codes.php';

      const pkTrendManager = {
        records: [],
        filteredRecords: [],
        currentDeleteId: null,
        searchKeyword: '',
        activeActionFilter: '',
        activeCategoryFilter: '',
        activeGroupFilter: '',
        debounceTimer: null,

        init: function () {
          this.loadStats();
          this.loadData();
        },

        // ==========================================
        // 1. DATA FETCHING
        // ==========================================
        loadStats: function () {
          fetch(API_URL + '?action=stats')
            .then(res => res.json())
            .then(res => {
              if (res.status === 'success' && res.data) {
                document.getElementById('stat-total').textContent = res.data.total;
                document.getElementById('stat-call').textContent = res.data.call;
                document.getElementById('stat-put').textContent = res.data.put;
                document.getElementById('stat-idle').textContent = res.data.idle;
              }
            })
            .catch(err => console.error('Stats fetch error:', err));
        },

        loadData: function () {
          const tbody = document.getElementById('pktrend-table-body');
          tbody.innerHTML = `
            <tr>
              <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-tertiary);">
                <div class="spinner" style="margin: 0 auto 12px;"></div>
                กำลังโหลดข้อมูล...
              </td>
            </tr>`;

          let url = API_URL + '?action=list';
          if (this.searchKeyword) url += '&search=' + encodeURIComponent(this.searchKeyword);
          if (this.activeActionFilter) url += '&action_filter=' + encodeURIComponent(this.activeActionFilter);
          if (this.activeCategoryFilter) url += '&category=' + encodeURIComponent(this.activeCategoryFilter);
          if (this.activeGroupFilter) url += '&group_name=' + encodeURIComponent(this.activeGroupFilter);

          fetch(url)
            .then(res => res.json())
            .then(res => {
              if (res.status === 'success') {
                this.records = res.data || [];
                this.populateFilterDropdowns(res.categories || [], res.groups || []);
                this.renderTable(this.records);
                this.loadStats();
              } else {
                this.showToast(res.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
              }
            })
            .catch(err => {
              console.error('Fetch error:', err);
              tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:30px; color:#ff5e5e;">ไม่สามารถโหลดข้อมูลได้: ${err.message}</td></tr>`;
            });
        },

        populateFilterDropdowns: function (categories, groups) {
          const catSelect = document.getElementById('filter-category-select');
          const grpSelect = document.getElementById('filter-group-select');

          if (catSelect && catSelect.options.length <= 1) {
            categories.forEach(cat => {
              const opt = document.createElement('option');
              opt.value = cat;
              opt.textContent = 'หมวด: ' + cat;
              catSelect.appendChild(opt);
            });
          }

          if (grpSelect && grpSelect.options.length <= 1) {
            groups.forEach(grp => {
              const opt = document.createElement('option');
              opt.value = grp;
              opt.textContent = 'กลุ่ม: ' + grp;
              grpSelect.appendChild(opt);
            });
          }
        },

        // ==========================================
        // 2. RENDER TABLE & 3 BUTTONS
        // ==========================================
        renderTable: function (list) {
          const tbody = document.getElementById('pktrend-table-body');
          const countBadge = document.getElementById('pktrend-row-count-badge');
          if (countBadge) countBadge.textContent = list.length + ' รายการ';

          if (!list || list.length === 0) {
            tbody.innerHTML = `
              <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-tertiary);">
                  <svg viewBox="0 0 24 24" style="width:36px; height:36px; fill:rgba(255,255,255,0.2); margin-bottom:8px;"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>
                  <div>ไม่พบรายการข้อมูลตามเงื่อนไขที่ค้นหา</div>
                </td>
              </tr>`;
            return;
          }

          let html = '';
          list.forEach(row => {
            const act = (row.action || '').toLowerCase();
            const catClass = (row.category || '').toLowerCase() === 'core' ? 'core' : 'extended';

            let trendClass = 'sideways';
            const tr = (row.trend || '').toLowerCase();
            if (tr.includes('up')) trendClass = 'uptrend';
            else if (tr.includes('down')) trendClass = 'downtrend';
            else if (tr.includes('reject')) trendClass = 'rejected';

            // 3 BUTTONS RENDER FOR ACTION
            const callActive = act === 'call' ? 'active' : '';
            const putActive = act === 'put' ? 'active' : '';
            const idleActive = act === 'idle' ? 'active' : '';

            html += `
              <tr id="row-${row.id}">
                <td style="text-align: center;">
                  <span class="badge-code-no">${row.code_no}</span>
                </td>
                <td>
                  <span class="badge-case-code">${this.escapeHtml(row.case_code)}</span>
                </td>
                <td style="text-align: center;">
                  <div class="pktrend-action-toggles">
                    <button type="button" class="btn-act-opt act-call ${callActive}" 
                            title="เลือก CALL" 
                            onclick="pkTrendManager.quickSetAction(${row.id}, 'call')">
                      <svg viewBox="0 0 24 24"><path d="M7 14l5-5 5 5z"/></svg>
                      CALL
                    </button>
                    <button type="button" class="btn-act-opt act-put ${putActive}" 
                            title="เลือก PUT" 
                            onclick="pkTrendManager.quickSetAction(${row.id}, 'put')">
                      <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
                      PUT
                    </button>
                    <button type="button" class="btn-act-opt act-idle ${idleActive}" 
                            title="เลือก IDLE" 
                            onclick="pkTrendManager.quickSetAction(${row.id}, 'idle')">
                      <svg viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                      IDLE
                    </button>
                  </div>
                </td>
                <td>
                  <span style="font-weight:600; color:#e2e8f0; font-size:13px;">${this.escapeHtml(row.case_desc || '-')}</span>
                </td>
                <td>
                  <span class="badge-category ${catClass}">${this.escapeHtml(row.category || '-')}</span>
                </td>
                <td>
                  <span style="font-size:12px; color:var(--text-secondary);">${this.escapeHtml(row.group_name || '-')}</span>
                </td>
                <td>
                  <span class="badge-trend ${trendClass}">${this.escapeHtml(row.trend || '-')}</span>
                </td>
                <td style="max-width: 300px; font-size: 12px; color: var(--text-tertiary); line-height: 1.4;">
                  ${this.escapeHtml(row.description || '-')}
                </td>
                <td style="text-align: center;">
                  <div class="pktrend-row-actions">
                    <button class="btn-row-action edit" title="แก้ไข" onclick="pkTrendManager.openEditModal(${row.id})">
                      <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    </button>
                    <button class="btn-row-action delete" title="ลบ" onclick="pkTrendManager.openDeleteModal(${row.id}, '${this.escapeJs(row.case_code)}')">
                      <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            `;
          });

          tbody.innerHTML = html;
        },

        // ==========================================
        // 3. 1-CLICK QUICK ACTION SETTER
        // ==========================================
        quickSetAction: function (id, actionVal) {
          fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'set_action',
              id: id,
              new_action: actionVal
            })
          })
          .then(res => res.json())
          .then(res => {
            if (res.status === 'success') {
              // Update local record
              const rec = this.records.find(r => r.id == id);
              if (rec) rec.action = actionVal;

              // Update DOM buttons for this row
              const row = document.getElementById('row-' + id);
              if (row) {
                const btnCall = row.querySelector('.act-call');
                const btnPut = row.querySelector('.act-put');
                const btnIdle = row.querySelector('.act-idle');

                if (btnCall) btnCall.classList.toggle('active', actionVal === 'call');
                if (btnPut) btnPut.classList.toggle('active', actionVal === 'put');
                if (btnIdle) btnIdle.classList.toggle('active', actionVal === 'idle');
              }

              this.loadStats();
              this.showToast(`บันทึก Action เป็น "${actionVal.toUpperCase()}" เรียบร้อย`, 'success');
            } else {
              this.showToast(res.message || 'ไม่สามารถอัปเดต Action ได้', 'error');
            }
          })
          .catch(err => {
            console.error('Update action error:', err);
            this.showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
          });
        },

        // ==========================================
        // 4. FILTERS & SEARCH
        // ==========================================
        onSearchInput: function (val) {
          clearTimeout(this.debounceTimer);
          this.debounceTimer = setTimeout(() => {
            this.searchKeyword = val.trim();
            this.loadData();
          }, 300);
        },

        onActionSelectChange: function (val) {
          this.activeActionFilter = val;
          this.updateCardFilterGlow(val);
          this.loadData();
        },

        filterByAction: function (val) {
          this.activeActionFilter = val;
          const select = document.getElementById('filter-action-select');
          if (select) select.value = val;
          this.updateCardFilterGlow(val);
          this.loadData();
        },

        updateCardFilterGlow: function (val) {
          ['all', 'call', 'put', 'idle'].forEach(type => {
            const card = document.getElementById('card-filter-' + type);
            if (card) {
              if ((val === '' && type === 'all') || val === type) {
                card.classList.add('active-filter');
              } else {
                card.classList.remove('active-filter');
              }
            }
          });
        },

        onCategoryChange: function (val) {
          this.activeCategoryFilter = val;
          this.loadData();
        },

        onGroupChange: function (val) {
          this.activeGroupFilter = val;
          this.loadData();
        },

        // ==========================================
        // 5. MODAL ADD & EDIT
        // ==========================================
        setModalAction: function (actionVal) {
          document.getElementById('form-action').value = actionVal;
          document.getElementById('modal-btn-call').classList.toggle('active', actionVal === 'call');
          document.getElementById('modal-btn-put').classList.toggle('active', actionVal === 'put');
          document.getElementById('modal-btn-idle').classList.toggle('active', actionVal === 'idle');
        },

        openAddModal: function () {
          document.getElementById('modalTitle').innerHTML = `
            <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:#01b574;"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            <span>เพิ่ม Case Code ใหม่</span>`;
          document.getElementById('form-id').value = '';
          
          // Suggest next code_no
          const maxCode = this.records.reduce((max, r) => Math.max(max, parseInt(r.code_no) || 0), 0);
          document.getElementById('form-code-no').value = maxCode + 1;
          document.getElementById('form-case-code').value = '';
          document.getElementById('form-case-desc').value = '';
          document.getElementById('form-category').value = 'CORE';
          document.getElementById('form-group-name').value = '';
          document.getElementById('form-trend').value = '';
          document.getElementById('form-description').value = '';

          this.setModalAction('idle');
          document.getElementById('btnSaveSubmit').textContent = 'เพิ่มข้อมูล';
          document.getElementById('pktrendModalBackdrop').classList.add('active');
        },

        openEditModal: function (id) {
          const rec = this.records.find(r => r.id == id);
          if (!rec) {
            this.showToast('ไม่พบข้อมูลรายการที่เลือก', 'error');
            return;
          }

          document.getElementById('modalTitle').innerHTML = `
            <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:#0075ff;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            <span>แก้ไข Case Code: ${this.escapeHtml(rec.case_code)}</span>`;

          document.getElementById('form-id').value = rec.id;
          document.getElementById('form-code-no').value = rec.code_no;
          document.getElementById('form-case-code').value = rec.case_code;
          document.getElementById('form-case-desc').value = rec.case_desc || '';
          document.getElementById('form-category').value = rec.category || '';
          document.getElementById('form-group-name').value = rec.group_name || '';
          document.getElementById('form-trend').value = rec.trend || '';
          document.getElementById('form-description').value = rec.description || '';

          const actionVal = (rec.action || 'idle').toLowerCase();
          this.setModalAction(actionVal);

          document.getElementById('btnSaveSubmit').textContent = 'บันทึกการแก้ไข';
          document.getElementById('pktrendModalBackdrop').classList.add('active');
        },

        closeModal: function () {
          document.getElementById('pktrendModalBackdrop').classList.remove('active');
        },

        saveForm: function (e) {
          e.preventDefault();
          const id = document.getElementById('form-id').value;
          const isEdit = id && parseInt(id) > 0;

          const payload = {
            action: isEdit ? 'update' : 'create',
            code_no: parseInt(document.getElementById('form-code-no').value),
            case_code: document.getElementById('form-case-code').value.trim(),
            action: document.getElementById('form-action').value,
            case_desc: document.getElementById('form-case-desc').value.trim(),
            category: document.getElementById('form-category').value.trim(),
            group_name: document.getElementById('form-group-name').value.trim(),
            trend: document.getElementById('form-trend').value.trim(),
            description: document.getElementById('form-description').value.trim()
          };

          if (isEdit) payload.id = parseInt(id);

          const submitBtn = document.getElementById('btnSaveSubmit');
          submitBtn.disabled = true;
          submitBtn.textContent = 'กำลังบันทึก...';

          fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          })
          .then(res => res.json())
          .then(res => {
            submitBtn.disabled = false;
            submitBtn.textContent = isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูล';

            if (res.status === 'success') {
              this.closeModal();
              this.showToast(isEdit ? 'อัปเดตข้อมูลสำเร็จ' : 'สร้างข้อมูลใหม่สำเร็จ', 'success');
              this.loadData();
            } else {
              this.showToast(res.message || 'บันทึกข้อมูลล้มเหลว', 'error');
            }
          })
          .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูล';
            console.error('Save error:', err);
            this.showToast('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + err.message, 'error');
          });
        },

        // ==========================================
        // 6. DELETE OPERATIONS
        // ==========================================
        openDeleteModal: function (id, code) {
          this.currentDeleteId = id;
          document.getElementById('deleteConfirmText').textContent = `คุณแน่ใจหรือไม่ว่าต้องการลบ Case Code "${code}" (ID: ${id})?`;
          document.getElementById('pktrendDeleteBackdrop').classList.add('active');
        },

        closeDeleteModal: function () {
          this.currentDeleteId = null;
          document.getElementById('pktrendDeleteBackdrop').classList.remove('active');
        },

        confirmDelete: function () {
          if (!this.currentDeleteId) return;

          fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'delete',
              id: this.currentDeleteId
            })
          })
          .then(res => res.json())
          .then(res => {
            this.closeDeleteModal();
            if (res.status === 'success') {
              this.showToast('ลบรายการเรียบร้อยแล้ว', 'success');
              this.loadData();
            } else {
              this.showToast(res.message || 'ไม่สามารถลบข้อมูลได้', 'error');
            }
          })
          .catch(err => {
            this.closeDeleteModal();
            console.error('Delete error:', err);
            this.showToast('เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
          });
        },

        // ==========================================
        // 7. DOWNLOAD STRATEGY CASE CODES (JSON)
        // ==========================================
        downloadStrategyCaseCodes: async function () {
          const btn = document.getElementById('btn-download-strategy-cases');
          const origHtml = btn ? btn.innerHTML : '';
          if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
              <svg viewBox="0 0 24 24" style="width:16px;height:16px;animation:pktrend-spin 1s linear infinite;fill:currentColor;"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
              <span>กำลังดาวน์โหลด...</span>`;
          }

          try {
            const res = await fetch(API_URL + '?action=download');
            if (!res.ok) {
              throw new Error('HTTP ' + res.status + ': ' + res.statusText);
            }
            const data = await res.json();
            const count = Array.isArray(data) ? data.length : (data.total || (data.data && data.data.length) || 0);

            // Convert to formatted JSON string
            const jsonText = JSON.stringify(data, null, 2);
            const blob = new Blob([jsonText], { type: 'application/json;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'strategy_case_codes.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            this.showToast('ดาวน์โหลด strategy_case_codes.json สำเร็จ (' + count + ' รายการ)', 'success');
          } catch (err) {
            console.error('Download error:', err);
            this.showToast('เกิดข้อผิดพลาดในการดาวน์โหลด: ' + err.message, 'error');
          } finally {
            if (btn) {
              btn.disabled = false;
              btn.innerHTML = origHtml;
            }
          }
        },

        // ==========================================
        // 8. TOAST NOTIFICATION & UTILS
        // ==========================================
        showToast: function (msg, type = 'info') {
          const toast = document.getElementById('pktrendToast');
          const toastMsg = document.getElementById('toastMsg');
          const toastIcon = document.getElementById('toastIcon');

          if (!toast) return;

          toast.className = type;
          toastMsg.textContent = msg;

          if (type === 'success') {
            toastIcon.innerHTML = `<svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#01b574;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`;
          } else if (type === 'error') {
            toastIcon.innerHTML = `<svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#ff5e5e;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`;
          } else {
            toastIcon.innerHTML = `<svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#00d4ff;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>`;
          }

          toast.classList.add('show');
          setTimeout(() => {
            toast.classList.remove('show');
          }, 3500);
        },

        escapeHtml: function (text) {
          if (!text) return '';
          return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
        },

        escapeJs: function (text) {
          if (!text) return '';
          return String(text).replace(/'/g, "\\'").replace(/"/g, '\\"');
        }
      };

      // Export globally so inline handlers can reach it
      window.pkTrendManager = pkTrendManager;

      // Auto initialize
      pkTrendManager.init();
    })();
  </script>
</div>
