<?php
/**
 * VPS Master CRUD Management Page
 * Vision UI Dark Glassmorphism Design
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-vps-master">
  <style>
    /* Scoped styling for VPS Master */
    .vps-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .vps-stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 20px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }
    .vps-stat-val {
      font-size: 26px;
      font-weight: 800;
      color: var(--text-primary);
      margin-top: 4px;
    }
    .vps-stat-label {
      font-size: 13px;
      color: var(--text-tertiary);
      font-weight: 500;
    }
    .vps-stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--accent-gradient);
      box-shadow: 0 4px 12px rgba(0, 117, 255, 0.3);
    }
    .vps-stat-icon svg {
      width: 24px;
      height: 24px;
      fill: #fff;
    }

    /* Action bar */
    .vps-action-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 20px;
    }
    .vps-search-box {
      display: flex;
      align-items: center;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 8px 16px;
      min-width: 280px;
      gap: 10px;
      transition: var(--transition-fast);
    }
    .vps-search-box:focus-within {
      border-color: var(--accent-blue);
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
    }
    .vps-search-box svg {
      width: 18px;
      height: 18px;
      fill: var(--text-tertiary);
    }
    .vps-search-box input {
      background: transparent;
      border: none;
      outline: none;
      color: var(--text-primary);
      font-size: 13px;
      width: 100%;
    }

    /* Modal dialog */
    .vps-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(4, 7, 24, 0.78);
      backdrop-filter: blur(8px);
      z-index: 999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      transition: opacity 0.25s ease;
    }
    .vps-modal-backdrop.active {
      display: flex;
      opacity: 1;
    }
    .vps-modal {
      background: linear-gradient(135deg, rgba(16, 26, 68, 0.96) 0%, rgba(6, 11, 40, 0.98) 100%);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--radius-lg);
      width: 100%;
      max-width: 620px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
      overflow: hidden;
      transform: scale(0.95);
      transition: transform 0.25s ease;
    }
    .vps-modal-backdrop.active .vps-modal {
      transform: scale(1);
    }
    .vps-modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .vps-modal-title {
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .vps-modal-close {
      background: transparent;
      border: none;
      color: var(--text-tertiary);
      cursor: pointer;
      font-size: 24px;
      line-height: 1;
      padding: 4px 8px;
      border-radius: 6px;
      transition: var(--transition-fast);
    }
    .vps-modal-close:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
    }
    .vps-modal-body {
      padding: 24px;
      max-height: 75vh;
      overflow-y: auto;
    }
    .vps-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    .vps-form-full {
      grid-column: span 2;
    }
    .vps-field-label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 6px;
    }
    .vps-field-label span.req {
      color: var(--accent-red);
    }
    .vps-input, .vps-textarea, select.vps-input {
      width: 100%;
      background: rgba(10, 19, 48, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: var(--radius-sm);
      padding: 10px 14px;
      color: #fff;
      font-size: 13px;
      font-family: inherit;
      transition: var(--transition-fast);
      box-sizing: border-box;
    }
    select.vps-input option {
      background: #0b1437;
      color: #fff;
      padding: 8px;
    }
    .vps-input:focus, .vps-textarea:focus, select.vps-input:focus {
      border-color: var(--accent-blue);
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
      outline: none;
      background: rgba(15, 28, 70, 0.95);
    }
    .deriv-link-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: rgba(0, 117, 255, 0.12);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.25);
      padding: 3px 8px;
      border-radius: 6px;
      font-family: monospace;
      font-size: 12px;
      font-weight: 700;
      width: fit-content;
    }
    .vps-textarea {
      min-height: 80px;
      resize: vertical;
    }
    .vps-modal-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--border-color);
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      background: rgba(0, 0, 0, 0.2);
    }

    /* Badges & Clickable Links */
    .ip-link-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(0, 117, 255, 0.12);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.25);
      padding: 5px 12px;
      border-radius: 8px;
      font-family: monospace;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .ip-link-badge:hover {
      background: rgba(0, 117, 255, 0.25);
      color: #ffffff;
      border-color: #00d4ff;
      box-shadow: 0 0 12px rgba(0, 212, 255, 0.4);
      transform: translateY(-1px);
    }
    .ip-link-badge svg {
      width: 12px;
      height: 12px;
      fill: currentColor;
      opacity: 0.8;
      transition: transform 0.2s ease;
    }
    .ip-link-badge:hover svg {
      opacity: 1;
      transform: translate(1px, -1px);
    }

    .url-link-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(1, 181, 116, 0.12);
      color: #01b574;
      border: 1px solid rgba(1, 181, 116, 0.25);
      padding: 5px 12px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      max-width: 200px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      transition: all 0.2s ease;
    }
    .url-link-badge:hover {
      background: rgba(1, 181, 116, 0.25);
      color: #ffffff;
      border-color: #01b574;
      box-shadow: 0 0 12px rgba(1, 181, 116, 0.4);
      transform: translateY(-1px);
    }
    .url-link-badge svg {
      width: 12px;
      height: 12px;
      fill: currentColor;
      flex-shrink: 0;
    }

    .port-badge {
      display: inline-flex;
      align-items: center;
      background: rgba(255, 181, 71, 0.12);
      color: #ffb547;
      border: 1px solid rgba(255, 181, 71, 0.25);
      padding: 3px 8px;
      border-radius: 6px;
      font-family: monospace;
      font-size: 11px;
      font-weight: 700;
    }

    .ip-badge-private {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: rgba(117, 81, 255, 0.12);
      color: #a78bfa;
      border: 1px solid rgba(117, 81, 255, 0.25);
      padding: 4px 10px;
      border-radius: 8px;
      font-family: monospace;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .ip-badge-private.clickable:hover {
      background: rgba(117, 81, 255, 0.25);
      color: #fff;
      border-color: #a78bfa;
    }

    .vps-code-pill {
      background: linear-gradient(135deg, rgba(0, 117, 255, 0.2) 0%, rgba(0, 212, 255, 0.2) 100%);
      color: #fff;
      border: 1px solid rgba(0, 212, 255, 0.3);
      padding: 4px 10px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 12px;
      letter-spacing: 0.5px;
      display: inline-block;
    }
    .remark-box {
      max-width: 220px;
      white-space: normal;
      word-break: break-word;
      color: var(--text-secondary);
      font-size: 12px;
      line-height: 1.4;
    }
    .btn-action-icon {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-color);
      color: var(--text-secondary);
      width: 34px;
      height: 34px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition-fast);
      margin-left: 4px;
    }
    .btn-action-icon:hover {
      background: rgba(0, 117, 255, 0.2);
      color: #fff;
      border-color: var(--accent-blue);
    }
    .btn-action-icon.btn-delete:hover {
      background: rgba(227, 26, 26, 0.2);
      color: #ff5252;
      border-color: var(--accent-red);
    }
    .btn-action-icon svg {
      width: 16px;
      height: 16px;
      fill: currentColor;
    }

    /* VPS Live Status Badges */
    .vps-live-status {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 9px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.3px;
      white-space: nowrap;
      transition: all 0.2s ease;
    }
    .vps-live-status.running {
      background: rgba(1, 181, 116, 0.15);
      color: #01b574;
      border: 1px solid rgba(1, 181, 116, 0.35);
      box-shadow: 0 0 10px rgba(1, 181, 116, 0.15);
    }
    .vps-live-status.trading {
      background: rgba(0, 212, 255, 0.15);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.35);
      box-shadow: 0 0 10px rgba(0, 212, 255, 0.25);
    }
    .vps-live-status.stopped {
      background: rgba(255, 255, 255, 0.08);
      color: #94a3b8;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .vps-live-status.offline {
      background: rgba(255, 82, 82, 0.15);
      color: #ff5252;
      border: 1px solid rgba(255, 82, 82, 0.3);
    }
    .vps-live-status.pending, .vps-live-status.stopping {
      background: rgba(255, 181, 71, 0.15);
      color: #ffb547;
      border: 1px solid rgba(255, 181, 71, 0.35);
    }
    .vps-live-status.unknown {
      background: rgba(255, 255, 255, 0.05);
      color: #64748b;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .status-pulse-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background-color: currentColor;
      box-shadow: 0 0 6px currentColor;
      animation: pulseDot 2s infinite ease-in-out;
    }
    @keyframes pulseDot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.35; transform: scale(0.85); }
    }
    .spinner-sm {
      width: 12px;
      height: 12px;
      border: 2px solid currentColor;
      border-top-color: transparent;
      border-radius: 50%;
      display: inline-block;
      animation: spin 0.8s linear infinite;
    }

    /* Start / Stop Control Buttons */
    .vps-ctrl-btn-group {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: rgba(10, 19, 48, 0.7);
      padding: 3px;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }
    .btn-vps-ctrl {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      border: 1px solid transparent;
      transition: all 0.2s ease;
      outline: none;
      user-select: none;
    }
    .btn-vps-ctrl svg {
      width: 11px;
      height: 11px;
      fill: currentColor;
    }
    .btn-vps-ctrl.btn-vps-start {
      background: rgba(1, 181, 116, 0.1);
      color: #01b574;
      border-color: rgba(1, 181, 116, 0.22);
    }
    .btn-vps-ctrl.btn-vps-start:hover {
      background: rgba(1, 181, 116, 0.3);
      color: #fff;
      border-color: #01b574;
      box-shadow: 0 0 12px rgba(1, 181, 116, 0.45);
      transform: translateY(-1px);
    }
    .btn-vps-ctrl.btn-vps-start.ready {
      background: linear-gradient(135deg, rgba(1, 181, 116, 0.28) 0%, rgba(1, 181, 116, 0.16) 100%);
      color: #01b574;
      border-color: rgba(1, 181, 116, 0.45);
      box-shadow: 0 0 8px rgba(1, 181, 116, 0.2);
    }
    .btn-vps-ctrl.btn-vps-stop {
      background: rgba(255, 82, 82, 0.1);
      color: #ff5252;
      border-color: rgba(255, 82, 82, 0.22);
    }
    .btn-vps-ctrl.btn-vps-stop:hover {
      background: rgba(255, 82, 82, 0.3);
      color: #fff;
      border-color: #ff5252;
      box-shadow: 0 0 12px rgba(255, 82, 82, 0.45);
      transform: translateY(-1px);
    }
    .btn-vps-ctrl.btn-vps-stop.ready {
      background: linear-gradient(135deg, rgba(255, 82, 82, 0.28) 0%, rgba(255, 82, 82, 0.16) 100%);
      color: #ff5252;
      border-color: rgba(255, 82, 82, 0.45);
      box-shadow: 0 0 8px rgba(255, 82, 82, 0.2);
    }
    .btn-vps-ctrl.idle {
      opacity: 0.65;
    }
    .btn-vps-ctrl:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      transform: none !important;
      box-shadow: none !important;
    }

    /* Toast Notification */
    #vpsToast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: rgba(6, 11, 40, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 14px 20px;
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(16px);
      z-index: 10000;
      display: none;
      align-items: center;
      gap: 12px;
      transform: translateY(20px);
      transition: transform 0.25s ease, opacity 0.25s ease;
      opacity: 0;
    }
    #vpsToast.show {
      display: flex;
      transform: translateY(0);
      opacity: 1;
    }
    #vpsToast.success {
      border-left: 4px solid var(--accent-green);
    }
    #vpsToast.error {
      border-left: 4px solid var(--accent-red);
    }
  </style>

  <!-- Stats Grid -->
  <div class="vps-stats-grid">
    <div class="vps-stat-card">
      <div>
        <div class="vps-stat-label">Total VPS Servers</div>
        <div class="vps-stat-val" id="statTotalVps">0</div>
      </div>
      <div class="vps-stat-icon">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z"/></svg>
      </div>
    </div>
    <div class="vps-stat-card">
      <div>
        <div class="vps-stat-label">Public Endpoints Active</div>
        <div class="vps-stat-val" id="statPublicIps" style="color:var(--accent-cyan);">0</div>
      </div>
      <div class="vps-stat-icon" style="background:linear-gradient(135deg, #01B574 0%, #00D4FF 100%);">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
      </div>
    </div>
    <div class="vps-stat-card">
      <div>
        <div class="vps-stat-label">Database Status</div>
        <div class="vps-stat-val" style="font-size:18px;color:var(--accent-green);" id="statDbStatus">Online</div>
      </div>
      <div class="vps-stat-icon" style="background:linear-gradient(135deg, #7551FF 0%, #0075FF 100%);">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
      </div>
    </div>
    <div class="vps-stat-card">
      <div>
        <div class="vps-stat-label">AWS Cloud Spend (MTD)</div>
        <div class="vps-stat-val" id="statAwsCost" style="color:#FF9900;">$0.00</div>
        <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;" id="statAwsStatus">EC2: -</div>
      </div>
      <div class="vps-stat-icon" style="background:linear-gradient(135deg, #FF9900 0%, #FF5500 100%);">
        <svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
      </div>
    </div>
  </div>

  <!-- VPS Master Table Card -->
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">VPS Master Management</h3>
        <p class="card-subtitle">จัดการข้อมูลเซิร์ฟเวอร์ VPS (คลิกที่ Public IP หรือ URL เพื่อเปิดหน้าเว็บในแท็บใหม่ ↗️)</p>
      </div>
      <button class="btn btn-primary" id="btnOpenAddModal" style="font-size:13px;padding:9px 18px;display:flex;align-items:center;gap:8px;">
        <svg style="width:16px;height:16px;fill:currentColor;" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        + เพิ่ม VPS ใหม่
      </button>
    </div>

    <!-- Filter & Action Bar -->
    <div class="vps-action-bar">
      <div class="vps-search-box">
        <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        <input type="text" id="vpsSearchInput" placeholder="ค้นหารหัส, ชื่อ VPS, IP, Port, URL หรือหมายเหตุ...">
      </div>
      <button class="btn btn-secondary" id="btnRefreshVps" style="font-size:12px;padding:8px 14px;display:flex;align-items:center;gap:6px;">
        <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
        รีเฟรชข้อมูล
      </button>
    </div>

    <!-- Data Table -->
    <div style="overflow-x:auto;">
      <table class="data-table" id="vpsDataTable">
        <thead>
          <tr>
            <th style="width:75px;">VPS Code</th>
            <th style="min-width:180px;">VPS Name</th>
            <th style="min-width:160px;text-align:center;">ควบคุม (Start / Stop)</th>
            <th style="min-width:180px;">Deriv Account (เชื่อมโยง)</th>
            <th style="min-width:145px;">Public IP / Domain (คลิกเพื่อเปิด)</th>
            <th style="width:75px;">Port</th>
            <th style="min-width:160px;">Web URL</th>
            <th style="min-width:115px;">Private IP</th>
            <th style="min-width:130px;">หมายเหตุ (Remark)</th>
            <th style="width:115px;">วันที่อัปเดต</th>
            <th style="width:85px;text-align:right;">จัดการ</th>
          </tr>
        </thead>
        <tbody id="vpsTableBody">
          <tr>
            <td colspan="11" style="text-align:center;padding:40px;color:var(--text-tertiary);">
              กำลังโหลดข้อมูล VPS...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Form Dialog (Add / Edit VPS) -->
<div class="vps-modal-backdrop" id="vpsModalBackdrop">
  <div class="vps-modal">
    <div class="vps-modal-header">
      <div class="vps-modal-title">
        <svg style="width:20px;height:20px;fill:var(--accent-cyan);" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z"/></svg>
        <span id="modalFormTitle">เพิ่มเครื่อง VPS ใหม่</span>
      </div>
      <button class="vps-modal-close" id="btnCloseModal">&times;</button>
    </div>

    <form id="vpsForm" onsubmit="return false;">
      <div class="vps-modal-body">
        <input type="hidden" id="vpsId" value="">
        <input type="hidden" id="vpsAction" value="create">

        <div class="vps-form-grid">
          <!-- VPS Code -->
          <div>
            <label class="vps-field-label">รหัส VPS (vpsCode) <span class="req">*</span></label>
            <input type="text" class="vps-input" id="formVpsCode" placeholder="เช่น VPS-SG-01 หรือ 1, 2" required>
          </div>

          <!-- VPS Name -->
          <div>
            <label class="vps-field-label">ชื่อ VPS (vpsName) <span class="req">*</span></label>
            <input type="text" class="vps-input" id="formVpsName" placeholder="เช่น Oracle-Free-Tier-4" required>
          </div>

          <!-- Deriv Account Selection -->
          <div class="vps-form-full">
            <label class="vps-field-label">🔗 เชื่อมโยงบัญชี Deriv (Deriv Account)</label>
            <select class="vps-input" id="formDerivAccountId" style="cursor:pointer;">
              <option value="">-- ไม่เชื่อมโยงบัญชี Deriv (None) --</option>
            </select>
            <small style="color:var(--text-tertiary);font-size:11px;margin-top:4px;display:block;">
              เลือกบัญชี Deriv Account (ดึงข้อมูลจากตาราง derivAccount) ที่เชื่อมโยงกับเซิร์ฟเวอร์ VPS เครื่องนี้
            </small>
          </div>

          <!-- Cloud Provider -->
          <div>
            <label class="vps-field-label">☁️ Cloud Provider</label>
            <select class="vps-input" id="formCloudProvider" style="cursor:pointer;">
              <option value="Manual">ทั่วไป / Manual (ระบุเอง)</option>
              <option value="AWS">Amazon Web Services (AWS)</option>
              <option value="Oracle">Oracle Cloud Infrastructure (OCI)</option>
              <option value="GCP">Google Cloud Platform (GCP)</option>
            </select>
          </div>

          <!-- AWS Instance ID -->
          <div>
            <label class="vps-field-label">AWS Instance ID (ถ้ามี)</label>
            <input type="text" class="vps-input" id="formAwsInstanceId" placeholder="เช่น i-0dfe609694660b8d2">
          </div>

          <!-- Public IP / Domain -->
          <div>
            <label class="vps-field-label">Public IP / Domain Name</label>
            <input type="text" class="vps-input" id="formPublicIP" placeholder="เช่น 161.118.217.177 หรือ gpkderiv.shop">
          </div>

          <!-- Port No -->
          <div>
            <label class="vps-field-label">Port No (พอร์ต)</label>
            <input type="text" class="vps-input" id="formPortNo" placeholder="เช่น 80, 8000, 8080">
          </div>

          <!-- URL -->
          <div class="vps-form-full">
            <label class="vps-field-label">Web URL (ลิงก์เปิดเว็บ)</label>
            <input type="text" class="vps-input" id="formUrl" placeholder="เช่น http://gpkderiv.shop:8000 หรือ https://pkderiv.shop">
          </div>

          <!-- Private IP -->
          <div class="vps-form-full">
            <label class="vps-field-label">Private IP Address</label>
            <input type="text" class="vps-input" id="formPrivateIP" placeholder="เช่น 10.104.0.5 หรือ 192.168.1.100">
          </div>

          <!-- Remark (หมายเหตุ) -->
          <div class="vps-form-full">
            <label class="vps-field-label">หมายเหตุ (Remark)</label>
            <textarea class="vps-textarea" id="formRemark" placeholder="ระบุหมายเหตุ เช่น วัตถุประสงค์การใช้งาน, สเปกเครื่อง, ผู้ดูแล หรือข้อมูลเพิ่มเติม..."></textarea>
          </div>

          <!-- Image (Base64) -->
          <div class="vps-form-full">
            <label class="vps-field-label">🖼️ รูปภาพ VPS (Base64 Image)</label>
            <div style="display:flex; gap:16px; align-items:center; background:rgba(15,23,42,0.5); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:12px 14px;">
              <div id="imagePreviewBox" style="width:64px; height:64px; border-radius:10px; border:1px dashed rgba(255,255,255,0.25); background:rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; flex-shrink:0;">
                <img id="vpsImagePreview" src="" alt="" style="width:100%; height:100%; object-fit:cover; display:none;" />
                <span id="vpsImagePlaceholder" style="font-size:26px; color:var(--text-tertiary);">🖼️</span>
              </div>
              <div style="flex:1; display:flex; flex-direction:column; gap:6px;">
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                  <input type="file" id="formImageFile" accept="image/*" style="display:none;" />
                  <button type="button" class="btn btn-secondary" id="btnSelectImage" style="font-size:12px; padding:6px 14px; display:inline-flex; align-items:center; gap:6px;">
                    📁 เลือกไฟล์รูปภาพ
                  </button>
                  <button type="button" class="btn btn-secondary" id="btnClearImage" style="font-size:12px; padding:6px 14px; display:none; color:var(--accent-red); border-color:rgba(255,75,75,0.35);">
                    🗑️ ลบรูป
                  </button>
                </div>
                <input type="hidden" id="formImageBase64" value="" />
                <div style="font-size:11px; color:var(--text-tertiary);">
                  รองรับ PNG, JPG, WEBP, GIF (ระบบจะแปลงเป็น Base64 Data URL อัตโนมัติ)
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="vps-modal-footer">
        <button type="button" class="btn btn-secondary" id="btnCancelForm" style="font-size:13px;padding:8px 18px;">ยกเลิก</button>
        <button type="submit" class="btn btn-primary" id="btnSaveVps" style="font-size:13px;padding:8px 20px;">
          💾 บันทึกข้อมูล
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Image Lightbox Modal -->
<div class="vps-modal-backdrop" id="vpsImageLightbox" style="z-index:1050;">
  <div style="position:relative; max-width:85vw; max-height:85vh; display:flex; flex-direction:column; align-items:center; background:rgba(6,11,40,0.95); border:1px solid rgba(255,255,255,0.15); border-radius:16px; padding:20px; box-shadow:0 20px 60px rgba(0,0,0,0.8);">
    <div style="display:flex; justify-content:space-between; align-items:center; width:100%; margin-bottom:12px; gap:12px;">
      <span id="lightboxTitle" style="color:#fff; font-weight:700; font-size:16px;">VPS Image</span>
      <button class="vps-modal-close" id="btnCloseLightbox" style="font-size:24px; color:#aaa; background:none; border:none; cursor:pointer;">&times;</button>
    </div>
    <img id="lightboxImg" src="" alt="VPS Image" style="max-width:100%; max-height:70vh; border-radius:10px; object-fit:contain; box-shadow:0 8px 30px rgba(0,0,0,0.5);" />
  </div>
</div>

<!-- Toast Notification Element -->
<div id="vpsToast">
  <span id="vpsToastMsg">Notification message</span>
</div>

<script>
(function() {
  const API_URL = '../php/api_vps.php';
  let vpsDataList = [];
  let derivAccountsList = [];

  const tableBody = document.getElementById('vpsTableBody');
  const searchInput = document.getElementById('vpsSearchInput');
  const modalBackdrop = document.getElementById('vpsModalBackdrop');
  const modalTitle = document.getElementById('modalFormTitle');
  const vpsForm = document.getElementById('vpsForm');
  const vpsIdInput = document.getElementById('vpsId');
  const vpsActionInput = document.getElementById('vpsAction');
  const formVpsCode = document.getElementById('formVpsCode');
  const formVpsName = document.getElementById('formVpsName');
  const formDerivAccountId = document.getElementById('formDerivAccountId');
  const formPublicIP = document.getElementById('formPublicIP');
  const formPortNo = document.getElementById('formPortNo');
  const formUrl = document.getElementById('formUrl');
  const formPrivateIP = document.getElementById('formPrivateIP');
  const formRemark = document.getElementById('formRemark');
  const formCloudProvider = document.getElementById('formCloudProvider');
  const formAwsInstanceId = document.getElementById('formAwsInstanceId');
  const statTotalVps = document.getElementById('statTotalVps');
  const statPublicIps = document.getElementById('statPublicIps');
  const statAwsCost = document.getElementById('statAwsCost');
  const statAwsStatus = document.getElementById('statAwsStatus');

  // Image handling elements
  const formImageFile = document.getElementById('formImageFile');
  const formImageBase64 = document.getElementById('formImageBase64');
  const vpsImagePreview = document.getElementById('vpsImagePreview');
  const vpsImagePlaceholder = document.getElementById('vpsImagePlaceholder');
  const btnSelectImage = document.getElementById('btnSelectImage');
  const btnClearImage = document.getElementById('btnClearImage');
  const vpsImageLightbox = document.getElementById('vpsImageLightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxTitle = document.getElementById('lightboxTitle');
  const btnCloseLightbox = document.getElementById('btnCloseLightbox');

  // Set / Clear Image Preview
  function setImagePreview(base64Str) {
    if (base64Str && base64Str.trim() !== '') {
      vpsImagePreview.src = base64Str;
      vpsImagePreview.style.display = 'block';
      if (vpsImagePlaceholder) vpsImagePlaceholder.style.display = 'none';
      if (btnClearImage) btnClearImage.style.display = 'inline-flex';
      if (formImageBase64) formImageBase64.value = base64Str;
    } else {
      vpsImagePreview.src = '';
      vpsImagePreview.style.display = 'none';
      if (vpsImagePlaceholder) vpsImagePlaceholder.style.display = 'block';
      if (btnClearImage) btnClearImage.style.display = 'none';
      if (formImageBase64) formImageBase64.value = '';
      if (formImageFile) formImageFile.value = '';
    }
  }

  // Handle Image File Selection with auto-compression for large images
  function handleImageFile(file) {
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('กรุณาเลือกไฟล์รูปภาพเท่านั้น (PNG, JPG, WEBP, GIF)', 'error');
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      const rawBase64 = e.target.result;
      const img = new Image();
      img.onload = function() {
        const maxDim = 1000;
        let width = img.width;
        let height = img.height;
        if (width > maxDim || height > maxDim) {
          if (width > height) {
            height = Math.round((height * maxDim) / width);
            width = maxDim;
          } else {
            width = Math.round((width * maxDim) / height);
            height = maxDim;
          }
          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, width, height);
          const compressed = canvas.toDataURL(file.type === 'image/png' ? 'image/png' : 'image/jpeg', 0.85);
          setImagePreview(compressed);
        } else {
          setImagePreview(rawBase64);
        }
      };
      img.src = rawBase64;
    };
    reader.readAsDataURL(file);
  }

  if (btnSelectImage && formImageFile) {
    btnSelectImage.addEventListener('click', () => formImageFile.click());
    formImageFile.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        handleImageFile(this.files[0]);
      }
    });
  }

  if (btnClearImage) {
    btnClearImage.addEventListener('click', () => setImagePreview(''));
  }

  if (btnCloseLightbox && vpsImageLightbox) {
    btnCloseLightbox.addEventListener('click', () => vpsImageLightbox.classList.remove('active'));
    vpsImageLightbox.addEventListener('click', (e) => {
      if (e.target === vpsImageLightbox) vpsImageLightbox.classList.remove('active');
    });
  }

  // Toast Function
  function showToast(msg, type = 'success') {
    const toast = document.getElementById('vpsToast');
    const toastMsg = document.getElementById('vpsToastMsg');
    if (!toast || !toastMsg) return;
    toastMsg.textContent = msg;
    toast.className = 'show ' + (type === 'error' ? 'error' : 'success');
    setTimeout(() => {
      toast.className = '';
    }, 3500);
  }

  // Load Deriv Accounts for Dropdown
  function loadDerivAccounts() {
    fetch('../php/api_deriv_account.php?action=list')
      .then(res => res.json())
      .then(res => {
        if (res.success && Array.isArray(res.data)) {
          derivAccountsList = res.data;
          if (modalBackdrop && modalBackdrop.classList.contains('active')) {
            populateDerivAccountSelect(formDerivAccountId ? formDerivAccountId.value : null);
          }
        }
      })
      .catch(err => {
        console.error('Error loading deriv accounts:', err);
      });
  }

  function populateDerivAccountSelect(selectedId = null) {
    if (!formDerivAccountId) return;
    let html = '<option value="">-- ไม่เชื่อมโยงบัญชี Deriv (None) --</option>';
    derivAccountsList.forEach(acc => {
      const accId = acc.accountId ? acc.accountId : ('ID #' + acc.id);
      const email = acc.email || '';
      const tokenName = acc.tokenName ? ` [Token: ${acc.tokenName}]` : '';
      const appName = acc.appName ? ` (${acc.appName})` : '';
      const isSel = (selectedId !== null && selectedId !== undefined && String(selectedId) === String(acc.id)) ? 'selected' : '';
      html += `<option value="${acc.id}" ${isSel}>${escapeHtml(accId)} - ${escapeHtml(email)}${escapeHtml(tokenName)}${escapeHtml(appName)}</option>`;
    });
    formDerivAccountId.innerHTML = html;
  }

  // Helper: Build Full Web URL from raw input
  function formatHttpUrl(target, port = '') {
    if (!target || target.trim() === '') return null;
    let t = target.trim();
    if (!/^https?:\/\//i.test(t)) {
      t = 'http://' + t;
    }
    if (port && String(port).trim() !== '') {
      const p = String(port).trim();
      // Check if port is not already inside URL
      const urlObj = tryParseUrl(t);
      if (urlObj && !urlObj.port) {
        t = `${urlObj.protocol}//${urlObj.hostname}:${p}${urlObj.pathname}${urlObj.search}${urlObj.hash}`;
      }
    }
    return t;
  }

  function tryParseUrl(str) {
    try {
      return new URL(str);
    } catch(e) {
      return null;
    }
  }

  // Load VPS List
  function loadVpsList(keyword = '') {
    tableBody.innerHTML = `
      <tr>
        <td colspan="11" style="text-align:center;padding:40px;color:var(--text-tertiary);">
          <div class="spinner" style="margin:0 auto 12px auto;width:24px;height:24px;border-width:2px;"></div>
          กำลังโหลดข้อมูล VPS...
        </td>
      </tr>
    `;

    const url = keyword ? `${API_URL}?action=list&search=${encodeURIComponent(keyword)}` : `${API_URL}?action=list`;
    fetch(url)
      .then(res => res.json())
      .then(res => {
        if (!res.success) {
          throw new Error(res.error || 'Failed to load VPS data');
        }
        vpsDataList = res.data || [];
        renderTable(vpsDataList);
        updateStats(vpsDataList);
        checkAllVpsStatuses();
      })
      .catch(err => {
        tableBody.innerHTML = `
          <tr>
            <td colspan="11" style="text-align:center;padding:30px;color:var(--accent-red);">
              ⚠️ เกิดข้อผิดพลาดในการโหลดข้อมูล: ${escapeHtml(err.message)}
            </td>
          </tr>
        `;
      });
  }

  // Check Real-time Live Statuses of all VPS
  let isCheckingStatus = false;
  function checkAllVpsStatuses() {
    if (isCheckingStatus) return;
    isCheckingStatus = true;
    fetch(`${API_URL}?action=check_statuses`)
      .then(res => res.json())
      .then(res => {
        if (res.success && res.statuses) {
          let updated = false;
          Object.values(res.statuses).forEach(item => {
            const vps = vpsDataList.find(x => String(x.id) === String(item.id));
            if (vps) {
              if (vps.instanceStatus !== item.status) {
                vps.instanceStatus = item.status;
                updated = true;
              }
              vps.isOnline = item.isOnline;
              if (item.balance !== undefined && item.balance !== null) {
                vps.botBalance = item.balance;
              }
            }
          });
          if (updated) {
            renderTable(vpsDataList);
            updateStats(vpsDataList);
          }
        }
      })
      .catch(err => console.warn('Status check warning:', err))
      .finally(() => {
        isCheckingStatus = false;
      });
  }

  // Update Mini Stats
  function updateStats(list) {
    if (statTotalVps) statTotalVps.textContent = list.length;
    const pubCount = list.filter(item => item.publicIP && item.publicIP.trim() !== '').length;
    statPublicIps.textContent = pubCount;

    // Update AWS Stats
    let totalAwsCost = 0;
    let awsStatusText = 'None';
    list.forEach(item => {
      if (item.cloudProvider === 'AWS') {
        totalAwsCost += parseFloat(item.currentMonthCost || 0);
        awsStatusText = item.instanceStatus || 'unknown';
      }
    });
    if (statAwsCost) statAwsCost.textContent = '$' + totalAwsCost.toFixed(2);
    if (statAwsStatus) statAwsStatus.textContent = 'EC2: ' + awsStatusText;
  }

  // Escape HTML helper
  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Render HTML Table
  function renderTable(list) {
    if (!list || list.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="11" style="text-align:center;padding:40px;color:var(--text-tertiary);">
            <div style="font-size:28px;margin-bottom:8px;">🖥️</div>
            ไม่พบข้อมูลเครื่อง VPS ในระบบ<br>
            <button class="btn btn-primary" onclick="document.getElementById('btnOpenAddModal').click()" style="margin-top:14px;font-size:12px;padding:6px 14px;">+ เพิ่ม VPS เครื่องแรก</button>
          </td>
        </tr>
      `;
      return;
    }

    let html = '';
    list.forEach(vps => {
      // 1. Deriv Account Link Badge
      let derivAccHtml = `<span style="color:var(--text-tertiary);font-size:12px;">-</span>`;
      if (vps.DerivAccountID && (vps.deriv_accountId || vps.deriv_email)) {
        const dAccId = vps.deriv_accountId ? escapeHtml(vps.deriv_accountId) : ('ID #' + vps.DerivAccountID);
        const dEmail = vps.deriv_email ? escapeHtml(vps.deriv_email) : '';
        const dTokenName = vps.deriv_tokenName ? escapeHtml(vps.deriv_tokenName) : '';
        const dAppName = vps.deriv_appName ? escapeHtml(vps.deriv_appName) : '';
        derivAccHtml = `
          <div style="display:flex; flex-direction:column; gap:3px;">
            <div style="display:flex; align-items:center; gap:6px;">
              <span class="deriv-link-pill" title="Deriv AccountID: ${dAccId}">
                🔗 ${dAccId}
              </span>
              ${dAppName ? `<span style="font-size:10px;color:#b794f4;background:rgba(117,81,233,0.15);padding:1px 6px;border-radius:4px;">${dAppName}</span>` : ''}
            </div>
            ${dEmail ? `<div style="font-size:11px;color:var(--text-secondary);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${dEmail}">${dEmail}</div>` : ''}
            ${dTokenName ? `<div style="font-size:10px;color:var(--text-tertiary);">Token: ${dTokenName}</div>` : ''}
          </div>
        `;
      }

      // 2. Public IP Link (Clickable, opens in new tab)
      let pubIpHtml = `<span style="color:var(--text-tertiary);font-size:12px;">-</span>`;
      if (vps.publicIP && vps.publicIP.trim() !== '') {
        const targetIpUrl = formatHttpUrl(vps.publicIP, vps.portno);
        pubIpHtml = `
          <a href="${escapeHtml(targetIpUrl)}" target="_blank" rel="noopener noreferrer" class="ip-link-badge" title="คลิกเพื่อเปิด ${escapeHtml(targetIpUrl)} ในแท็บใหม่">
            <span style="color:var(--accent-green)">●</span>
            ${escapeHtml(vps.publicIP)}
            <svg viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
          </a>
        `;
      }

      // 3. Port No
      const portHtml = vps.portno && String(vps.portno).trim() !== '' 
        ? `<span class="port-badge">:${escapeHtml(vps.portno)}</span>` 
        : `<span style="color:var(--text-tertiary);font-size:12px;">-</span>`;

      // 4. Web URL (Clickable, opens in new tab)
      let urlHtml = `<span style="color:var(--text-tertiary);font-size:12px;">-</span>`;
      if (vps.url && vps.url.trim() !== '') {
        const fullUrl = formatHttpUrl(vps.url);
        urlHtml = `
          <a href="${escapeHtml(fullUrl)}" target="_blank" rel="noopener noreferrer" class="url-link-badge" title="คลิกเพื่อเปิด URL: ${escapeHtml(fullUrl)}">
            <svg viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
            ${escapeHtml(vps.url)}
          </a>
        `;
      }

      // 5. Private IP
      let privIpHtml = `<span style="color:var(--text-tertiary);font-size:12px;">-</span>`;
      if (vps.privateIP && vps.privateIP.trim() !== '') {
        const targetPrivUrl = formatHttpUrl(vps.privateIP, vps.portno);
        privIpHtml = `
          <a href="${escapeHtml(targetPrivUrl)}" target="_blank" rel="noopener noreferrer" class="ip-badge-private clickable" title="คลิกเพื่อเปิด ${escapeHtml(targetPrivUrl)}">
            🔒 ${escapeHtml(vps.privateIP)}
          </a>
        `;
      }

      // 6. Remark
      const remarkText = vps.remark && vps.remark.trim() !== '' 
        ? `<div class="remark-box">${escapeHtml(vps.remark)}</div>` 
        : `<span style="color:var(--text-tertiary);font-size:12px;">-</span>`;

      // 7. Date
      const dateDisplay = vps.updated_at ? vps.updated_at.substring(0, 16) : (vps.created_at ? vps.created_at.substring(0, 16) : '-');

      // 8. Provider & Live Status & Control Logic
      const lowerName = (vps.vpsName || '').toLowerCase();
      const isAws = (vps.cloudProvider === 'AWS' || lowerName.includes('aws'));
      const isGcp = (lowerName.includes('google') || (vps.url && vps.url.includes('gpkderiv')));
      const isOracle = (lowerName.includes('oracle') || (vps.publicIP && vps.publicIP.startsWith('161.118')));

      let provLabel = 'VPS Server';
      let provBadgeColor = 'rgba(0, 212, 255, 0.15)';
      let provTextColor = '#00d4ff';
      let provBorder = 'rgba(0, 212, 255, 0.3)';
      let provIcon = '🖥️';

      if (isAws) {
        provLabel = 'AWS EC2';
        provBadgeColor = 'rgba(255, 153, 0, 0.18)';
        provTextColor = '#FF9900';
        provBorder = 'rgba(255, 153, 0, 0.35)';
        provIcon = '☁️';
      } else if (isGcp) {
        provLabel = 'Google Cloud';
        provBadgeColor = 'rgba(66, 133, 244, 0.18)';
        provTextColor = '#60a5fa';
        provBorder = 'rgba(66, 133, 244, 0.35)';
        provIcon = '🌐';
      } else if (isOracle) {
        provLabel = 'Oracle Cloud';
        provBadgeColor = 'rgba(248, 0, 0, 0.15)';
        provTextColor = '#f87171';
        provBorder = 'rgba(248, 0, 0, 0.3)';
        provIcon = '🏛️';
      }

      // Live Status determination
      const rawStatus = (vps.instanceStatus || 'unknown').toLowerCase();
      let statusClass = 'offline';
      let statusLabel = 'Offline';
      let isRunning = false;
      let isPending = false;

      if (rawStatus === 'running') {
        statusClass = 'running';
        statusLabel = 'Running';
        isRunning = true;
      } else if (rawStatus === 'trading') {
        statusClass = 'trading';
        statusLabel = 'Trading';
        isRunning = true;
      } else if (rawStatus === 'stopped') {
        statusClass = 'stopped';
        statusLabel = 'Stopped';
        isRunning = false;
      } else if (rawStatus === 'pending') {
        statusClass = 'pending';
        statusLabel = 'Starting...';
        isPending = true;
      } else if (rawStatus === 'stopping') {
        statusClass = 'stopping';
        statusLabel = 'Stopping...';
        isPending = true;
      } else {
        statusClass = 'unknown';
        statusLabel = 'Checking...';
      }

      const startTitle = isAws ? 'สั่งเปิดเครื่อง AWS EC2 (Start Instance)' : 'สั่งเริ่มทำงาน / รีสตาร์ทบอท (Start Bot)';
      const stopTitle = isAws ? 'สั่งปิดเครื่อง AWS EC2 (Stop Instance)' : 'สั่งหยุดเทรด (Stop Trade)';

      const controlHtml = `
        <td style="text-align:center; vertical-align:middle; white-space:nowrap;">
          <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
            <!-- Status Badge -->
            <div class="vps-live-status ${statusClass}" id="status-pill-${vps.id}">
              <span class="status-pulse-dot"></span>
              <span class="status-text">${statusLabel}</span>
            </div>

            <!-- Start / Stop Button Group -->
            <div class="vps-ctrl-btn-group">
              ${isPending ? `
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:#ffb547;padding:4px 8px;">
                  <span class="spinner-sm"></span>
                  <span>รอระบบ...</span>
                </div>
              ` : `
                <button type="button" 
                        class="btn-vps-ctrl btn-vps-start ${!isRunning ? 'ready' : 'idle'}" 
                        data-id="${vps.id}" 
                        data-action="start" 
                        data-provider="${isAws ? 'AWS' : 'BOT'}" 
                        title="${startTitle}">
                  <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                  <span>Start</span>
                </button>

                <button type="button" 
                        class="btn-vps-ctrl btn-vps-stop ${isRunning ? 'ready' : 'idle'}" 
                        data-id="${vps.id}" 
                        data-action="stop" 
                        data-provider="${isAws ? 'AWS' : 'BOT'}" 
                        title="${stopTitle}">
                  <svg viewBox="0 0 24 24"><path d="M6 6h12v12H6z"/></svg>
                  <span>Stop</span>
                </button>
              `}
            </div>
          </div>
        </td>
      `;

      html += `
        <tr>
          <td>
            <span class="vps-code-pill">${escapeHtml(vps.vpsCode)}</span>
          </td>
          <td>
            <div class="table-project" style="gap:12px;">
              <div class="table-project-icon" style="background:rgba(0,117,255,0.15);color:#00D4FF;border-radius:10px;width:38px;height:38px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                ${vps.image && vps.image.trim() !== '' 
                  ? `<img src="${escapeHtml(vps.image)}" alt="${escapeHtml(vps.vpsName)}" class="vps-thumb-clickable" style="width:100%;height:100%;object-fit:cover;cursor:pointer;" title="คลิกเพื่อดูรูปภาพขนาดเต็ม" />` 
                  : provIcon}
              </div>
              <div style="display:flex; flex-direction:column; gap:4px;">
                <span class="table-project-name" style="font-weight:700;">${escapeHtml(vps.vpsName)}</span>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                  <span style="font-size:10px; font-weight:700; background:${provBadgeColor}; color:${provTextColor}; border:1px solid ${provBorder}; padding:1px 6px; border-radius:4px;">
                    ${provLabel}
                  </span>
                  ${isAws && vps.currentMonthCost ? `
                    <span style="font-size:10px; color:#00D4FF; font-weight:600;">$${parseFloat(vps.currentMonthCost || 0).toFixed(2)} / mo</span>
                  ` : ''}
                </div>
              </div>
            </div>
          </td>
          ${controlHtml}
          <td>${derivAccHtml}</td>
          <td>${pubIpHtml}</td>
          <td>${portHtml}</td>
          <td>${urlHtml}</td>
          <td>${privIpHtml}</td>
          <td>${remarkText}</td>
          <td><span style="font-size:12px;color:var(--text-tertiary);">${dateDisplay}</span></td>
          <td style="text-align:right;white-space:nowrap;">
            <button class="btn-action-icon btn-edit" title="แก้ไขข้อมูล" data-id="${vps.id}">
              <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </button>
            <button class="btn-action-icon btn-delete" title="ลบรายการ" data-id="${vps.id}" data-code="${escapeHtml(vps.vpsCode)}">
              <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
            </button>
          </td>
        </tr>
      `;
    });

    tableBody.innerHTML = html;

    // Bind Image Click for Lightbox Preview
    tableBody.querySelectorAll('.vps-thumb-clickable').forEach(img => {
      img.addEventListener('click', function(e) {
        e.stopPropagation();
        if (lightboxImg && vpsImageLightbox) {
          lightboxImg.src = this.src;
          if (lightboxTitle) lightboxTitle.textContent = this.alt || 'VPS Image';
          vpsImageLightbox.classList.add('active');
        }
      });
    });

    // Bind Start / Stop Control Buttons for ALL VPS
    tableBody.querySelectorAll('.btn-vps-ctrl').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const id = this.dataset.id;
        const action = this.dataset.action;
        const provider = this.dataset.provider;
        handleVpsControl(id, action, provider);
      });
    });

    // Bind Edit & Delete Buttons
    tableBody.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.dataset.id;
        openEditModal(id);
      });
    });

    tableBody.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const code = this.dataset.code;
        confirmDelete(id, code);
      });
    });
  }

  // Handle Start / Stop Control for Any VPS
  let awsPollingTimer = null;
  function handleVpsControl(id, action, provider) {
    const vps = vpsDataList.find(item => String(item.id) === String(id));
    const vpsName = vps ? vps.vpsName : ('VPS #' + id);

    let confirmMsg = '';
    if (provider === 'AWS') {
      confirmMsg = (action === 'stop')
        ? `⚠️ คำเตือน: คุณต้องการสั่งปิดเครื่อง (Stop EC2) สำหรับ "${vpsName}" ใช่หรือไม่?\n\n• บอทและโปรแกรมทั้งหมดจะหยุดทำงานทันที\n• ค่าชั่วโมงเปิดเครื่อง (EC2 Compute) จะหยุดคิดเงินชั่วคราว`
        : `🚀 คุณต้องการสั่งเปิดเครื่อง (Start EC2) สำหรับ "${vpsName}" ใช่หรือไม่?\n\n• ระบบจะส่งคำสั่งเปิดเครื่องไปยัง AWS ทันที\n• ตรวจสอบและอัปเดต Public IP ใหม่อัตโนมัติ`;
    } else {
      confirmMsg = (action === 'stop')
        ? `🛑 ยืนยันการสั่งหยุดเทรด (Stop Trade) สำหรับ "${vpsName}" ใช่หรือไม่?\n\n• บอทจะหยุดส่งคำสั่งเทรดทันทีและบันทึกสถานะปิดเทรด`
        : `🚀 ยืนยันการสั่งเริ่มทำงาน / รีสตาร์ทบอท (Start Bot) สำหรับ "${vpsName}" ใช่หรือไม่?\n\n• ระบบจะส่งคำสั่งเริ่มทำงานใหม่ไปยังเซิร์ฟเวอร์ทันที`;
    }

    if (!confirm(confirmMsg)) return;

    // Optimistic UI update
    if (vps) {
      vps.instanceStatus = (action === 'start') ? 'pending' : 'stopping';
      renderTable(vpsDataList);
    }

    showToast(`กำลังส่งคำสั่ง ${action.toUpperCase()} ไปยัง ${vpsName}...`, 'info');

    if (provider === 'AWS') {
      const apiBilling = window.location.pathname.includes('/pages/') 
        ? '../../php/api_aws_billing.php' 
        : (window.location.pathname.includes('/dashboard/') ? '../php/api_aws_billing.php' : 'php/api_aws_billing.php');

      fetch(`${apiBilling}?action=control_instance`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ command: action, vpsId: id })
      })
      .then(res => res.json())
      .then(res => {
        if (!res.success) throw new Error(res.error || 'Failed to control instance');
        showToast(res.message || `ส่งคำสั่ง ${action.toUpperCase()} สำเร็จ กำลังรอการตอบรับ...`, 'success');

        // Poll until state is reached
        if (awsPollingTimer) clearInterval(awsPollingTimer);
        let attempts = 0;
        const targetState = (action === 'start') ? 'running' : 'stopped';

        awsPollingTimer = setInterval(() => {
          attempts++;
          fetch(`${apiBilling}?action=get_status&vpsId=${id}`)
            .then(r => r.json())
            .then(r => {
              if (r.success && r.data) {
                const currentState = (r.data.state || '').toLowerCase();
                if (vps) {
                  vps.instanceStatus = currentState;
                  if (r.data.publicIp) vps.publicIP = r.data.publicIp;
                }
                renderTable(vpsDataList);

                if (currentState === targetState) {
                  clearInterval(awsPollingTimer);
                  awsPollingTimer = null;
                  showToast(`AWS ${vpsName} เปลี่ยนสถานะเป็น ${currentState.toUpperCase()} สำเร็จ`, 'success');
                }
              }
            })
            .catch(err => console.warn(err));

          if (attempts >= 25) {
            clearInterval(awsPollingTimer);
            awsPollingTimer = null;
          }
        }, 3500);
      })
      .catch(err => {
        alert('เกิดข้อผิดพลาด: ' + err.message);
        loadVpsList();
      });
    } else {
      // Non-AWS VPS control via api_vps.php
      fetch(`${API_URL}?action=control_vps`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, command: action })
      })
      .then(res => res.json())
      .then(res => {
        if (!res.success) throw new Error(res.error || 'Failed to control VPS');
        showToast(res.message || `ส่งคำสั่ง ${action.toUpperCase()} สำเร็จ`, 'success');
        setTimeout(() => {
          checkAllVpsStatuses();
        }, 2000);
      })
      .catch(err => {
        alert('เกิดข้อผิดพลาด: ' + err.message);
        loadVpsList();
      });
    }
  }

  // Open Modal for Create
  function openAddModal() {
    vpsForm.reset();
    vpsIdInput.value = '';
    vpsActionInput.value = 'create';
    modalTitle.textContent = 'เพิ่มเครื่อง VPS ใหม่';
    setImagePreview('');
    if (formCloudProvider) formCloudProvider.value = 'Manual';
    if (formAwsInstanceId) formAwsInstanceId.value = '';
    populateDerivAccountSelect(null);
    modalBackdrop.classList.add('active');
    formVpsCode.focus();
  }

  // Open Modal for Edit
  function openEditModal(id) {
    const vps = vpsDataList.find(item => String(item.id) === String(id));
    if (!vps) return;

    vpsIdInput.value = vps.id;
    vpsActionInput.value = 'update';
    modalTitle.textContent = 'แก้ไขข้อมูล VPS: ' + vps.vpsCode;
    formVpsCode.value = vps.vpsCode || '';
    formVpsName.value = vps.vpsName || '';
    formPublicIP.value = vps.publicIP || '';
    formPortNo.value = vps.portno || '';
    formUrl.value = vps.url || '';
    formPrivateIP.value = vps.privateIP || '';
    formRemark.value = vps.remark || '';
    if (formCloudProvider) formCloudProvider.value = vps.cloudProvider || 'Manual';
    if (formAwsInstanceId) formAwsInstanceId.value = vps.awsInstanceId || '';
    setImagePreview(vps.image || '');
    
    populateDerivAccountSelect(vps.DerivAccountID);
    if (formDerivAccountId) {
      formDerivAccountId.value = vps.DerivAccountID ? String(vps.DerivAccountID) : '';
    }

    modalBackdrop.classList.add('active');
    formVpsName.focus();
  }

  // Close Modal
  function closeModal() {
    modalBackdrop.classList.remove('active');
  }

  // Confirm and Delete VPS
  function confirmDelete(id, code) {
    if (!confirm(`คุณต้องการลบเครื่อง VPS รหัส [${code}] ใช่หรือไม่?`)) {
      return;
    }

    fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id: id })
    })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          showToast(`ลบ VPS [${code}] สำเร็จเรียบร้อย`, 'success');
          loadVpsList(searchInput.value.trim());
        } else {
          showToast(res.error || 'เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
        }
      })
      .catch(err => {
        showToast('Error: ' + err.message, 'error');
      });
  }

  // Handle Form Submit
  vpsForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const derivAccVal = formDerivAccountId ? formDerivAccountId.value : '';
    const payload = {
      action: vpsActionInput.value,
      id: vpsIdInput.value,
      vpsCode: formVpsCode.value.trim(),
      vpsName: formVpsName.value.trim(),
      publicIP: formPublicIP.value.trim(),
      portno: formPortNo.value.trim(),
      url: formUrl.value.trim(),
      privateIP: formPrivateIP.value.trim(),
      remark: formRemark.value.trim(),
      cloudProvider: formCloudProvider ? formCloudProvider.value : 'Manual',
      awsInstanceId: formAwsInstanceId ? formAwsInstanceId.value.trim() : '',
      DerivAccountID: derivAccVal ? parseInt(derivAccVal) : null,
      image: formImageBase64 ? (formImageBase64.value.trim() || null) : null
    };

    if (!payload.vpsCode || !payload.vpsName) {
      showToast('กรุณากรอกรหัสและชื่อ VPS ให้ครบถ้วน', 'error');
      return;
    }

    const saveBtn = document.getElementById('btnSaveVps');
    saveBtn.disabled = true;
    saveBtn.textContent = 'กำลังบันทึก...';

    fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(res => {
        saveBtn.disabled = false;
        saveBtn.textContent = '💾 บันทึกข้อมูล';
        if (res.success) {
          showToast(res.message || 'บันทึกข้อมูลเรียบร้อย', 'success');
          closeModal();
          loadVpsList(searchInput.value.trim());
        } else {
          showToast(res.error || 'เกิดข้อผิดพลาดในการบันทึก', 'error');
        }
      })
      .catch(err => {
        saveBtn.disabled = false;
        saveBtn.textContent = '💾 บันทึกข้อมูล';
        showToast('Error: ' + err.message, 'error');
      });
  });

  // Search input debounce
  let searchTimer = null;
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      loadVpsList(this.value.trim());
    }, 300);
  });

  // Modal event listeners
  document.getElementById('btnOpenAddModal').addEventListener('click', openAddModal);
  document.getElementById('btnCloseModal').addEventListener('click', closeModal);
  document.getElementById('btnCancelForm').addEventListener('click', closeModal);
  document.getElementById('btnRefreshVps').addEventListener('click', () => {
    loadDerivAccounts();
    loadVpsList(searchInput.value.trim());
  });

  modalBackdrop.addEventListener('click', function(e) {
    if (e.target === modalBackdrop) {
      closeModal();
    }
  });

  // Initial Load
  loadDerivAccounts();
  loadVpsList();
})();
</script>
