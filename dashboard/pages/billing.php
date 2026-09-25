<?php
/**
 * Billing Page Content
 * Real-time AWS EC2 & Cloud Billing Management
 * Vision UI Dark Glassmorphism Design
 */

header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-billing">
  <style>
    /* Scoped styling for Cloud Billing */
    .billing-header-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 24px;
    }
    .billing-header-title h2 {
      font-size: 22px;
      font-weight: 800;
      color: var(--text-primary);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .billing-header-title p {
      font-size: 13px;
      color: var(--text-secondary);
      margin: 4px 0 0;
    }
    .billing-header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-sync-aws {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #FF9900 0%, #FF6600 100%);
      color: #ffffff;
      border: none;
      padding: 10px 20px;
      border-radius: var(--radius-sm);
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(255, 153, 0, 0.35);
      transition: all 0.2s ease;
    }
    .btn-sync-aws:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 153, 0, 0.5);
    }
    .btn-sync-aws.spinning svg {
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      100% { transform: rotate(360deg); }
    }

    .btn-currency-toggle {
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      padding: 10px 16px;
      border-radius: var(--radius-sm);
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-currency-toggle:hover {
      border-color: var(--accent-blue);
      background: rgba(0, 117, 255, 0.1);
    }

    /* 4 Stats Cards */
    .billing-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .b-stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 20px 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      position: relative;
      overflow: hidden;
      transition: transform 0.2s ease;
    }
    .b-stat-card:hover {
      transform: translateY(-2px);
    }
    .b-stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--accent-gradient);
    }
    .b-stat-card.orange::before {
      background: linear-gradient(135deg, #FF9900, #FF5500);
    }
    .b-stat-card.green::before {
      background: linear-gradient(135deg, #01B574, #00D4FF);
    }
    .b-stat-card.purple::before {
      background: linear-gradient(135deg, #7928CA, #FF0080);
    }

    .b-stat-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .b-stat-val {
      font-size: 26px;
      font-weight: 800;
      color: var(--text-primary);
      margin: 6px 0 4px;
    }
    .b-stat-sub {
      font-size: 12px;
      color: var(--text-secondary);
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .b-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: rgba(0, 117, 255, 0.15);
      border: 1px solid rgba(0, 117, 255, 0.25);
    }
    .b-stat-icon.orange {
      background: rgba(255, 153, 0, 0.15);
      border-color: rgba(255, 153, 0, 0.3);
    }
    .b-stat-icon.green {
      background: rgba(1, 181, 116, 0.15);
      border-color: rgba(1, 181, 116, 0.3);
    }
    .b-stat-icon.purple {
      background: rgba(121, 40, 202, 0.15);
      border-color: rgba(121, 40, 202, 0.3);
    }
    .b-stat-icon svg {
      width: 22px;
      height: 22px;
      fill: #fff;
    }

    /* Cloud Instance Status Pill */
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .status-pill.running {
      background: rgba(1, 181, 116, 0.15);
      color: #01B574;
      border: 1px solid rgba(1, 181, 116, 0.3);
    }
    .status-pill.running::before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #01B574;
      box-shadow: 0 0 8px #01B574;
    }
    .status-pill.stopped {
      background: rgba(227, 26, 26, 0.15);
      color: #E31A1A;
      border: 1px solid rgba(227, 26, 26, 0.3);
    }
    .status-pill.stopped::before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #E31A1A;
    }

    /* Service Breakdown Bars */
    .service-breakdown-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-top: 16px;
    }
    .svc-item {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .svc-item-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 13px;
    }
    .svc-name {
      color: var(--text-primary);
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .svc-cost {
      color: var(--text-primary);
      font-weight: 700;
      font-family: monospace;
    }
    .svc-bar-bg {
      width: 100%;
      height: 6px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      overflow: hidden;
    }
    .svc-bar-fill {
      height: 100%;
      border-radius: 10px;
      transition: width 0.5s ease;
    }

    /* Sync banner */
    .sync-info-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(15, 23, 42, 0.4);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 10px 16px;
      margin-bottom: 20px;
      font-size: 12px;
      color: var(--text-secondary);
    }
    .sync-badge {
      color: var(--accent-cyan);
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* 5-Category Breakdown Cards Grid */
    .category-cards-section-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin: 10px 0 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .category-cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .cat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      backdrop-filter: blur(20px);
      padding: 16px 18px;
      box-shadow: var(--shadow-card);
      position: relative;
      overflow: hidden;
      transition: all 0.25s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .cat-card:hover {
      transform: translateY(-2px);
      border-color: rgba(255, 255, 255, 0.2);
    }
    .cat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
    }
    .cat-card.cat-runtime::before { background: #0075FF; }
    .cat-card.cat-ipv4::before { background: #FF9900; }
    .cat-card.cat-storage::before { background: #01B574; }
    .cat-card.cat-bandwidth::before { background: #7928CA; }
    .cat-card.cat-tax::before { background: #64748B; }

    .cat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }
    .cat-icon-badge {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      background: rgba(255, 255, 255, 0.05);
    }
    .cat-pct-badge {
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-secondary);
    }
    .cat-title {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary);
    }
    .cat-amount {
      font-size: 22px;
      font-weight: 800;
      color: var(--text-primary);
      margin: 4px 0 6px;
      font-family: monospace;
    }
    .cat-subtext {
      font-size: 11px;
      color: var(--text-tertiary);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .cat-bar-bg {
      width: 100%;
      height: 4px;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 10px;
      margin-top: 8px;
      overflow: hidden;
    }
    .cat-bar-fill {
      height: 100%;
      border-radius: 10px;
      transition: width 0.5s ease;
    }

    /* Chart toggles */
    .chart-btn-group {
      display: inline-flex;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 3px;
      gap: 3px;
    }
    .chart-pill-btn {
      background: transparent;
      border: none;
      color: var(--text-tertiary);
      font-size: 11px;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .chart-pill-btn.active {
      background: var(--accent-gradient);
      color: #fff;
      box-shadow: 0 2px 8px rgba(0, 117, 255, 0.4);
    }

    /* Daily Breakdown Table */
    .daily-table-container {
      overflow-x: auto;
      max-height: 420px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border-color);
    }
    .daily-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }
    .daily-table th {
      position: sticky;
      top: 0;
      z-index: 2;
      text-align: center !important;
      padding: 12px 14px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.3px;
      border-bottom: 2px solid rgba(255, 255, 255, 0.12);
      border-right: 1px solid rgba(255, 255, 255, 0.05);
      white-space: nowrap;
    }
    .daily-table td {
      font-size: 12px;
      white-space: nowrap;
      text-align: center !important;
      padding: 11px 14px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      border-right: 1px solid rgba(255, 255, 255, 0.05);
    }
    .daily-table th:last-child,
    .daily-table td:last-child {
      border-right: none;
    }

    /* Alternating column colors (Lightgray column zebra striping) */
    .daily-table th:nth-child(even) {
      background-color: #141f48 !important; /* lightgray tinted header */
      color: #e2e8f0;
    }
    .daily-table td:nth-child(even) {
      background-color: rgba(211, 211, 211, 0.055) !important; /* lightgray column */
    }
    .daily-table th:nth-child(odd) {
      background-color: #0b1437 !important;
    }
    .daily-table td:nth-child(odd) {
      background-color: rgba(6, 11, 40, 0.35) !important;
    }
    .daily-table tbody tr:hover td:nth-child(odd) {
      background-color: rgba(255, 255, 255, 0.04) !important;
    }
    .daily-table tbody tr:hover td:nth-child(even) {
      background-color: rgba(211, 211, 211, 0.1) !important;
    }

    .daily-badge-today {
      background: rgba(0, 212, 255, 0.18);
      color: #00D4FF;
      font-size: 10px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 4px;
      margin-left: 6px;
      display: inline-block;
      vertical-align: middle;
    }

    /* EC2 Control Buttons */
    .btn-ec2-control {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 12px;
      font-weight: 700;
      padding: 5px 13px;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid transparent;
      outline: none;
      user-select: none;
    }
    .btn-ec2-control:disabled {
      opacity: 0.65;
      cursor: not-allowed !important;
      transform: none !important;
    }
    .btn-ec2-control.start-mode {
      background: linear-gradient(135deg, rgba(1, 181, 116, 0.25) 0%, rgba(1, 181, 116, 0.12) 100%);
      color: #01B574;
      border-color: rgba(1, 181, 116, 0.45);
      box-shadow: 0 4px 12px rgba(1, 181, 116, 0.2);
    }
    .btn-ec2-control.start-mode:hover:not(:disabled) {
      background: linear-gradient(135deg, rgba(1, 181, 116, 0.4) 0%, rgba(1, 181, 116, 0.2) 100%);
      border-color: #01B574;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(1, 181, 116, 0.35);
    }
    .btn-ec2-control.stop-mode {
      background: linear-gradient(135deg, rgba(227, 26, 26, 0.25) 0%, rgba(227, 26, 26, 0.12) 100%);
      color: #FF5252;
      border-color: rgba(227, 26, 26, 0.45);
      box-shadow: 0 4px 12px rgba(227, 26, 26, 0.2);
    }
    .btn-ec2-control.stop-mode:hover:not(:disabled) {
      background: linear-gradient(135deg, rgba(227, 26, 26, 0.4) 0%, rgba(227, 26, 26, 0.2) 100%);
      border-color: #FF5252;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(227, 26, 26, 0.35);
    }
    .btn-ec2-control.transition-mode {
      background: rgba(100, 116, 139, 0.2);
      color: #94A3B8;
      border-color: rgba(100, 116, 139, 0.3);
    }
    .control-spinner {
      width: 12px;
      height: 12px;
      border: 2px solid currentColor;
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      display: inline-block;
    }

    /* Modal Backdrop & Dialog */
    .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(4, 7, 24, 0.78);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 9999;
      display: none;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.25s ease;
      padding: 16px;
      box-sizing: border-box;
    }
    .modal-backdrop.active {
      display: flex;
      opacity: 1;
    }
    .modal-box {
      background: linear-gradient(135deg, rgba(16, 26, 68, 0.96) 0%, rgba(6, 11, 40, 0.98) 100%);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(0, 117, 255, 0.15);
      width: 100%;
      max-width: 480px;
      overflow: hidden;
      transform: scale(0.95);
      transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modal-backdrop.active .modal-box {
      transform: scale(1);
    }

    /* Billing Toast */
    .billing-toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: rgba(10, 19, 48, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 14px 20px;
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      z-index: 10000;
      display: none;
      align-items: center;
      gap: 12px;
      transform: translateY(20px);
      transition: all 0.25s ease;
      opacity: 0;
    }
    .billing-toast.show {
      display: flex;
      transform: translateY(0);
      opacity: 1;
    }
    .billing-toast.success {
      border-left: 4px solid #01B574;
    }
    .billing-toast.error {
      border-left: 4px solid #FF5252;
    }
    .billing-toast.info {
      border-left: 4px solid #00D4FF;
    }
  </style>

  <!-- Header -->
  <div class="billing-header-bar">
    <div class="billing-header-title">
      <h2>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="#FF9900"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
        Cloud VPS Billing & Cost Explorer
      </h2>
      <p>ติดตามค่าใช้จ่ายและสถานะเครื่อง AWS EC2 แบบ Real-time ผ่าน AWS Cost Explorer API</p>
    </div>
    <div class="billing-header-actions">
      <button class="btn-currency-toggle" id="btnToggleCurrency" title="สลับการแสดงผล USD / THB">
        <span id="currencyBadge">🇺🇸 USD</span>
      </button>
      <button class="btn-sync-aws" id="btnSyncAws">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
        <span id="syncBtnText">Sync กับ AWS</span>
      </button>
    </div>
  </div>

  <!-- Sync Status Bar -->
  <div class="sync-info-bar">
    <div class="sync-badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
      <span>เชื่อมต่อกับ AWS Cost Explorer API สำเร็จ</span>
    </div>
    <div>
      <span>อัปเดตล่าสุด: </span>
      <strong id="labelLastSync" style="color:var(--text-primary);">-</strong>
    </div>
  </div>

  <!-- 4 Stats Cards Grid -->
  <div class="billing-stats-grid">
    <!-- Card 1: Month-To-Date Cost -->
    <div class="b-stat-card orange">
      <div>
        <span class="b-stat-label">ยอดใช้จ่ายเดือนนี้ (MTD)</span>
        <div class="b-stat-val" id="statMtdCost">$0.00</div>
        <div class="b-stat-sub">
          <span>อัตราใช้จ่าย: </span>
          <strong id="statDailyBurn" style="color:#FF9900;">$0.00 / วัน</strong>
        </div>
      </div>
      <div class="b-stat-icon orange">
        <svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
      </div>
    </div>

    <!-- Card 2: Projected Month-End -->
    <div class="b-stat-card">
      <div>
        <span class="b-stat-label">ประมาณการสิ้นเดือน (Forecast)</span>
        <div class="b-stat-val" id="statProjectedCost">$0.00</div>
        <div class="b-stat-sub">
          <span>คำนวณตามการใช้งานสะสม</span>
        </div>
      </div>
      <div class="b-stat-icon">
        <svg viewBox="0 0 24 24"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>
      </div>
    </div>

    <!-- Card 3: Active AWS Instance Status -->
    <div class="b-stat-card green" id="cardAwsInstance">
      <div>
        <span class="b-stat-label">สถานะ AWS EC2 เครื่องหลัก</span>
        <div style="display:flex;align-items:center;gap:10px;margin:8px 0 4px;flex-wrap:wrap;">
          <div style="font-size:18px;font-weight:700;" id="statInstanceName">AWS London (nutv99)</div>
          <span class="status-pill running" id="statInstancePill">Running</span>
          <button id="btnQuickEc2Control" class="btn-ec2-control" style="display:none;"></button>
        </div>
        <div class="b-stat-sub">
          <span id="statInstanceType">t3.micro</span> &nbsp;•&nbsp;
          <span id="statInstanceRegion">eu-west-2</span>
        </div>
      </div>
      <div class="b-stat-icon green">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2z"/></svg>
      </div>
    </div>

    <!-- Card 4: 6-Month Total / Avg -->
    <div class="b-stat-card purple">
      <div>
        <span class="b-stat-label">ค่าใช้จ่ายย้อนหลัง 6 เดือน</span>
        <div class="b-stat-val" id="stat6mTotal">$0.00</div>
        <div class="b-stat-sub">
          <span>เฉลี่ย: </span>
          <strong id="stat6mAvg" style="color:var(--accent-cyan);">$0.00 / เดือน</strong>
        </div>
      </div>
      <div class="b-stat-icon purple">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
      </div>
    </div>
  </div>

  <!-- 5 Categorized Cost Breakdown Cards Grid -->
  <div class="category-cards-section-title">
    <div style="display:flex;align-items:center;gap:8px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="#00D4FF"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
      <span>แยกค่าใช้จ่ายตามประเภทบริการ (Categorized Cost Breakdown - Month to Date)</span>
    </div>
    <span style="font-size:11px;color:var(--text-tertiary);">ข้อมูลสรุปรอบบิลปัจจุบัน</span>
  </div>

  <div class="category-cards-grid" id="categoryCardsContainer">
    <!-- Card 1: Server Runtime -->
    <div class="cat-card cat-runtime">
      <div>
        <div class="cat-header">
          <div class="cat-icon-badge" style="background:rgba(0,117,255,0.15);color:#0075FF;">🖥️</div>
          <span class="cat-pct-badge" id="catPct_server_runtime">0%</span>
        </div>
        <div class="cat-title">Server Runtime (EC2 Compute)</div>
        <div class="cat-amount" id="catAmt_server_runtime">$0.00</div>
      </div>
      <div>
        <div class="cat-subtext">
          <span>ชั่วโมงเปิดเครื่อง:</span>
          <strong id="catQty_server_runtime" style="color:#00D4FF;">-</strong>
        </div>
        <div class="cat-bar-bg">
          <div class="cat-bar-fill" id="catBar_server_runtime" style="width:0%;background:linear-gradient(90deg, #0075FF, #00D4FF);"></div>
        </div>
      </div>
    </div>

    <!-- Card 2: Public IPv4 Address -->
    <div class="cat-card cat-ipv4">
      <div>
        <div class="cat-header">
          <div class="cat-icon-badge" style="background:rgba(255,153,0,0.15);color:#FF9900;">🌐</div>
          <span class="cat-pct-badge" id="catPct_public_ipv4">0%</span>
        </div>
        <div class="cat-title">Public IPv4 Address</div>
        <div class="cat-amount" id="catAmt_public_ipv4">$0.00</div>
      </div>
      <div>
        <div class="cat-subtext">
          <span>ชั่วโมงถือครอง IP:</span>
          <strong id="catQty_public_ipv4" style="color:#FF9900;">-</strong>
        </div>
        <div class="cat-bar-bg">
          <div class="cat-bar-fill" id="catBar_public_ipv4" style="width:0%;background:linear-gradient(90deg, #FF9900, #FF5500);"></div>
        </div>
      </div>
    </div>

    <!-- Card 3: Storage (EBS gp3) -->
    <div class="cat-card cat-storage">
      <div>
        <div class="cat-header">
          <div class="cat-icon-badge" style="background:rgba(1,181,116,0.15);color:#01B574;">💾</div>
          <span class="cat-pct-badge" id="catPct_storage">0%</span>
        </div>
        <div class="cat-title">Storage (EBS gp3 Volumes)</div>
        <div class="cat-amount" id="catAmt_storage">$0.00</div>
      </div>
      <div>
        <div class="cat-subtext">
          <span>ขนาดพื้นที่จัดเก็บ:</span>
          <strong id="catQty_storage" style="color:#01B574;">-</strong>
        </div>
        <div class="cat-bar-bg">
          <div class="cat-bar-fill" id="catBar_storage" style="width:0%;background:linear-gradient(90deg, #01B574, #48BB78);"></div>
        </div>
      </div>
    </div>

    <!-- Card 4: Bandwidth & Data Transfer -->
    <div class="cat-card cat-bandwidth">
      <div>
        <div class="cat-header">
          <div class="cat-icon-badge" style="background:rgba(121,40,202,0.15);color:#7928CA;">📡</div>
          <span class="cat-pct-badge" id="catPct_bandwidth">0%</span>
        </div>
        <div class="cat-title">Bandwidth & Transfer</div>
        <div class="cat-amount" id="catAmt_bandwidth">$0.00</div>
      </div>
      <div>
        <div class="cat-subtext">
          <span>ปริมาณเน็ตเวิร์ก:</span>
          <strong id="catQty_bandwidth" style="color:#FF0080;">-</strong>
        </div>
        <div class="cat-bar-bg">
          <div class="cat-bar-fill" id="catBar_bandwidth" style="width:0%;background:linear-gradient(90deg, #7928CA, #FF0080);"></div>
        </div>
      </div>
    </div>

    <!-- Card 5: Tax & Fees -->
    <div class="cat-card cat-tax">
      <div>
        <div class="cat-header">
          <div class="cat-icon-badge" style="background:rgba(100,116,139,0.15);color:#94A3B8;">🧾</div>
          <span class="cat-pct-badge" id="catPct_tax_other">0%</span>
        </div>
        <div class="cat-title">Tax & Surcharges</div>
        <div class="cat-amount" id="catAmt_tax_other">$0.00</div>
      </div>
      <div>
        <div class="cat-subtext">
          <span>ภาษีมูลค่าเพิ่ม:</span>
          <strong id="catQty_tax_other" style="color:#94A3B8;">VAT 7%</strong>
        </div>
        <div class="cat-bar-bg">
          <div class="cat-bar-fill" id="catBar_tax_other" style="width:0%;background:linear-gradient(90deg, #64748B, #94A3B8);"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts & Analysis Section -->
  <div class="grid-2" style="margin-bottom:24px;">
    <!-- Chart: Spend History / Daily Trend -->
    <div class="card">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
        <div>
          <h3 class="card-title" id="chartMainTitle">แนวโน้มค่าใช้จ่ายรายเดือน (Monthly Spend History)</h3>
          <p class="card-subtitle" id="chartSubTitle">ข้อมูลจริงจาก AWS Cost Explorer ย้อนหลัง 6 เดือน</p>
        </div>
        <div class="chart-btn-group">
          <button class="chart-pill-btn active" id="btnChartMonthly">รายเดือน (6M)</button>
          <button class="chart-pill-btn" id="btnChartDaily">รายวันเดือนนี้ (Daily)</button>
        </div>
      </div>
      <div style="height: 280px; position: relative; margin-top: 10px;">
        <canvas id="monthlySpendChart"></canvas>
      </div>
    </div>

    <!-- Service Breakdown Card -->
    <div class="card">
      <div class="card-header">
        <div>
          <h3 class="card-title">สัดส่วนค่าบริการแยกหมวดหมู่ (Category Breakdown)</h3>
          <p class="card-subtitle">แจกแจงตามประเภทการใช้งานจริงและปริมาณ Resource</p>
        </div>
      </div>
      <div id="serviceBreakdownContainer" class="service-breakdown-list">
        <div style="text-align:center;padding:40px 0;color:var(--text-tertiary);">
          กำลังโหลดข้อมูลสัดส่วนบริการ...
        </div>
      </div>
    </div>
  </div>

  <!-- Daily Cost Breakdown Table Section -->
  <div class="card" style="margin-bottom:24px;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h3 class="card-title">แจกแจงค่าใช้จ่ายรายวันในรอบปัจจุบัน (Daily Cost Breakdown)</h3>
        <p class="card-subtitle">บันทึกค่าบริการจริงในแต่ละวันของเดือนปัจจุบัน โดยจำแนกตามประเภท</p>
      </div>
      <div style="font-size:12px;color:var(--text-secondary);">
        รอบบัญชี: <strong id="dailyMonthBadge" style="color:var(--accent-cyan);">-</strong>
      </div>
    </div>
    <div class="daily-table-container">
      <table class="table daily-table" id="dailyTable">
        <thead>
          <tr>
            <th>วันที่ (Date)</th>
            <th>🖥️ Server Runtime</th>
            <th>🌐 Public IPv4</th>
            <th>💾 Storage (EBS)</th>
            <th>📡 Bandwidth</th>
            <th>🧾 Tax / อื่นๆ</th>
            <th>รวมรายวัน (Total)</th>
          </tr>
        </thead>
        <tbody id="dailyBreakdownTableBody">
          <tr>
            <td colspan="7" style="text-align:center;color:var(--text-tertiary);padding:24px;">กำลังโหลดข้อมูลรายวัน...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Connected AWS VPS Details Card -->
  <div class="card" style="margin-bottom:24px;">
    <div class="card-header" style="flex-wrap:wrap;gap:12px;">
      <div>
        <h3 class="card-title">ข้อมูลเครื่อง AWS EC2 ที่เชื่อมต่ออยู่</h3>
        <p class="card-subtitle">เครื่อง VPS ที่ผูกเข้ากับระบบบัญชีและตรวจจับการทำงาน</p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <button id="btnHeaderEc2Control" class="btn-ec2-control" style="display:none;"></button>
        <a id="linkAwsConsole" href="https://eu-west-2.console.aws.amazon.com/ec2/home?region=eu-west-2#Instances:" target="_blank" rel="noopener" class="btn btn-outline" style="font-size:12px;padding:6px 14px;display:inline-flex;align-items:center;gap:6px;">
          <span>เปิด AWS Console</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
        </a>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;padding-top:10px;">
      <div style="background:rgba(15,23,42,0.4);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:14px 18px;">
        <p style="font-size:11px;color:var(--text-tertiary);margin-bottom:4px;">INSTANCE NAME / CODE</p>
        <h4 style="font-size:15px;font-weight:700;color:var(--text-primary);" id="detailInstanceName">-</h4>
        <p style="font-size:12px;color:var(--text-secondary);margin-top:2px;" id="detailVpsCode">-</p>
      </div>

      <div style="background:rgba(15,23,42,0.4);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:14px 18px;">
        <p style="font-size:11px;color:var(--text-tertiary);margin-bottom:4px;">AWS INSTANCE ID</p>
        <code style="font-size:13px;color:var(--accent-cyan);font-weight:700;" id="detailInstanceId">-</code>
        <p style="font-size:11px;color:var(--text-secondary);margin-top:4px;" id="detailRegion">-</p>
      </div>

      <div style="background:rgba(15,23,42,0.4);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:14px 18px;">
        <p style="font-size:11px;color:var(--text-tertiary);margin-bottom:4px;">PUBLIC IP & DNS</p>
        <a id="detailPublicIpLink" href="#" target="_blank" style="font-size:13px;font-weight:700;color:#00D4FF;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
          <span id="detailPublicIp">-</span>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
        </a>
        <p style="font-size:11px;color:var(--text-tertiary);margin-top:4px;word-break:break-all;" id="detailPublicDns">-</p>
      </div>

      <div style="background:rgba(15,23,42,0.4);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:14px 18px;">
        <p style="font-size:11px;color:var(--text-tertiary);margin-bottom:4px;">WEB URL SERVICE</p>
        <a id="detailWebUrl" href="#" target="_blank" style="font-size:13px;font-weight:700;color:#01B574;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
          <span id="detailWebUrlText">-</span>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
        </a>
        <p style="font-size:11px;color:var(--text-secondary);margin-top:4px;">ผูกกับ Deriv Account</p>
      </div>
    </div>
  </div>

  <!-- Historical Invoices Table -->
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">ประวัติบิลรายเดือน (AWS Billing Invoices)</h3>
        <p class="card-subtitle">บันทึกยอดชำระที่เรียกเก็บจริงในแต่ละรอบบัญชี</p>
      </div>
    </div>
    <div style="overflow-x:auto;">
      <table class="table" id="invoicesTable">
        <thead>
          <tr>
            <th>รอบบิล (Billing Month)</th>
            <th>ยอดชำระ (USD)</th>
            <th>ยอดแปลงโดยประมาณ (THB)</th>
            <th>สถานะ</th>
            <th>บันทึกล่าสุด</th>
          </tr>
        </thead>
        <tbody id="invoicesTableBody">
          <tr>
            <td colspan="5" style="text-align:center;color:var(--text-tertiary);padding:24px;">กำลังโหลดประวัติบิล...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Confirm EC2 Start / Stop -->
<div class="modal-backdrop" id="ec2ConfirmModal">
  <div class="modal-box">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
      <div style="display:flex;align-items:center;gap:12px;">
        <div id="modalEc2Icon" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">
          ⚡
        </div>
        <div>
          <h4 id="modalEc2Title" style="font-size:16px;font-weight:700;color:#fff;margin:0;">ยืนยันคำสั่งควบคุมเซิร์ฟเวอร์</h4>
          <p id="modalEc2Subtitle" style="font-size:11px;color:var(--text-tertiary);margin:3px 0 0 0;">AWS EC2 Cloud Instance Management</p>
        </div>
      </div>
      <button class="modal-close" id="btnCancelEc2Modal" style="background:transparent;border:none;color:var(--text-tertiary);font-size:22px;cursor:pointer;line-height:1;">&times;</button>
    </div>

    <div style="padding:20px 24px;">
      <!-- Dynamic Warning / Info Alert -->
      <div id="modalEc2AlertBox" style="padding:14px 16px;border-radius:10px;font-size:13px;line-height:1.5;margin-bottom:16px;">
        <!-- Filled via JS -->
      </div>

      <!-- Instance Meta Summary Box -->
      <div style="background:rgba(15,23,42,0.6);border:1px solid var(--border-color);border-radius:10px;padding:14px;font-size:12px;display:flex;flex-direction:column;gap:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--text-tertiary);">ชื่อเครื่อง (Instance Name):</span>
          <strong style="color:#fff;" id="modalEc2InstanceName">-</strong>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--text-tertiary);">Instance ID:</span>
          <code style="color:var(--accent-cyan);font-weight:700;" id="modalEc2InstanceId">-</code>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--text-tertiary);">AWS Region:</span>
          <span style="color:var(--text-secondary);" id="modalEc2Region">-</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="color:var(--text-tertiary);">สถานะปัจจุบัน:</span>
          <span id="modalEc2CurrentState" class="status-pill running">-</span>
        </div>
      </div>
    </div>

    <div style="padding:16px 24px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:10px;background:rgba(10,19,48,0.5);">
      <button class="btn btn-outline" id="btnDismissEc2Modal" style="font-size:12px;padding:8px 18px;">ยกเลิก</button>
      <button class="btn" id="btnExecuteEc2Action" style="font-size:12px;padding:8px 22px;font-weight:700;">
        <span id="btnExecuteText">ยืนยัน</span>
      </button>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div id="billingToast" class="billing-toast">
  <span id="toastIcon">🔔</span>
  <span id="toastMsg">Notification message</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
  const API_BILLING = window.location.pathname.includes('/pages/') 
    ? '../../php/api_aws_billing.php' 
    : (window.location.pathname.includes('/dashboard/') ? '../php/api_aws_billing.php' : 'php/api_aws_billing.php');
  let isThb = false;
  let cachedBillingData = null;
  let chartInstance = null;

  let chartMode = 'monthly'; // 'monthly' or 'daily'

  // Format currency
  function formatMoney(amountUsd, forceCurrency = null) {
    const amt = parseFloat(amountUsd || 0);
    const showThb = (forceCurrency === 'THB') || (forceCurrency === null && isThb);
    if (showThb) {
      const thb = (amt * 35.5).toFixed(2);
      return '฿' + Number(thb).toLocaleString();
    }
    return '$' + Number(amt.toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Render Charts (Supports both Monthly 6M and Daily Stacked Trend)
  function renderHistoryChart(monthlyHistory, dailyBreakdown) {
    if (typeof Chart === 'undefined') {
      console.warn('Chart.js not loaded yet, skipping chart render.');
      return;
    }
    const canvas = document.getElementById('monthlySpendChart');
    if (!canvas) return;

    if (chartInstance) {
      chartInstance.destroy();
      chartInstance = null;
    }

    const ctx = canvas.getContext('2d');
    const currencySym = isThb ? '฿' : '$';
    const rate = isThb ? 35.5 : 1.0;

    if (chartMode === 'daily') {
      document.getElementById('chartMainTitle').textContent = 'แนวโน้มค่าใช้จ่ายรายวันเดือนนี้ (Daily Spend Trend)';
      document.getElementById('chartSubTitle').textContent = 'แจกแจงค่าบริการแต่ละวันแยกตามประเภทบริการ (Stacked Bar Chart)';

      const days = dailyBreakdown || [];
      const labels = days.map(d => d.date ? d.date.substring(5) : ''); // MM-DD
      
      const dsIpv4 = days.map(d => parseFloat((d.public_ipv4 * rate).toFixed(2)));
      const dsStorage = days.map(d => parseFloat((d.storage * rate).toFixed(2)));
      const dsCompute = days.map(d => parseFloat((d.server_runtime * rate).toFixed(2)));
      const dsBw = days.map(d => parseFloat((d.bandwidth * rate).toFixed(2)));
      const dsTax = days.map(d => parseFloat((d.tax_other * rate).toFixed(2)));

      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Public IPv4',
              data: dsIpv4,
              backgroundColor: '#FF9900',
              borderRadius: 4,
              borderSkipped: false
            },
            {
              label: 'Storage (EBS)',
              data: dsStorage,
              backgroundColor: '#01B574',
              borderRadius: 4,
              borderSkipped: false
            },
            {
              label: 'Server Runtime',
              data: dsCompute,
              backgroundColor: '#0075FF',
              borderRadius: 4,
              borderSkipped: false
            },
            {
              label: 'Bandwidth',
              data: dsBw,
              backgroundColor: '#7928CA',
              borderRadius: 4,
              borderSkipped: false
            },
            {
              label: 'Tax & Fees',
              data: dsTax,
              backgroundColor: '#64748B',
              borderRadius: 4,
              borderSkipped: false
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                color: '#8F9BBA',
                boxWidth: 10,
                padding: 12,
                font: { size: 10 }
              }
            },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.95)',
              borderColor: 'rgba(0, 212, 255, 0.4)',
              borderWidth: 1,
              titleColor: '#fff',
              bodyColor: '#E2E8F0',
              callbacks: {
                label: function(ctx) {
                  return ctx.dataset.label + ': ' + currencySym + Number(ctx.parsed.y).toFixed(2);
                },
                footer: function(items) {
                  let sum = 0;
                  items.forEach(i => sum += i.parsed.y);
                  return 'รวมวันนั้น: ' + currencySym + sum.toFixed(2);
                }
              }
            }
          },
          scales: {
            x: {
              stacked: true,
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: { color: '#8F9BBA', font: { size: 10 } }
            },
            y: {
              stacked: true,
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: {
                color: '#8F9BBA',
                font: { size: 10 },
                callback: function(val) { return currencySym + val; }
              }
            }
          }
        }
      });

    } else {
      // Monthly 6-Month Chart
      document.getElementById('chartMainTitle').textContent = 'แนวโน้มค่าใช้จ่ายรายเดือน (Monthly Spend History)';
      document.getElementById('chartSubTitle').textContent = 'ข้อมูลจริงจาก AWS Cost Explorer ย้อนหลัง 6 เดือน';

      const history = monthlyHistory || [];
      const labels = history.map(item => item.billingMonth || item.month);
      const dataValues = history.map(item => parseFloat(item.costAmount || item.amount || 0));

      const gradient = ctx.createLinearGradient(0, 0, 0, 260);
      gradient.addColorStop(0, 'rgba(255, 153, 0, 0.45)');
      gradient.addColorStop(1, 'rgba(255, 153, 0, 0.02)');

      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: isThb ? 'ยอดบิล (THB)' : 'ยอดบิล (USD)',
            data: isThb ? dataValues.map(v => (v * 35.5).toFixed(2)) : dataValues,
            backgroundColor: gradient,
            borderColor: '#FF9900',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.95)',
              borderColor: 'rgba(255, 153, 0, 0.5)',
              borderWidth: 1,
              titleColor: '#fff',
              bodyColor: '#00D4FF',
              callbacks: {
                label: function(ctx) {
                  return (isThb ? '฿' : '$') + Number(ctx.parsed.y).toLocaleString();
                }
              }
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: { color: '#8F9BBA', font: { size: 11 } }
            },
            y: {
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: {
                color: '#8F9BBA',
                font: { size: 11 },
                callback: function(val) {
                  return (isThb ? '฿' : '$') + val;
                }
              }
            }
          }
        }
      });
    }
  }

  // Render 5 Category Cards
  function renderCategoryCards(categories, totalMtd) {
    if (!categories || categories.length === 0) return;

    categories.forEach(cat => {
      const id = cat.id;
      const amt = parseFloat(cat.amount || 0);
      const pct = parseFloat(cat.percentage || 0);
      const qty = cat.quantityText || '-';

      const elAmt = document.getElementById(`catAmt_${id}`);
      const elPct = document.getElementById(`catPct_${id}`);
      const elQty = document.getElementById(`catQty_${id}`);
      const elBar = document.getElementById(`catBar_${id}`);

      if (elAmt) elAmt.textContent = formatMoney(amt);
      if (elPct) elPct.textContent = `${pct}%`;
      if (elQty) elQty.textContent = qty;
      if (elBar) elBar.style.width = `${Math.min(100, pct)}%`;
    });
  }

  // Render Category / Service Breakdown List
  function renderServiceBreakdown(categories, services, totalMtd) {
    const container = document.getElementById('serviceBreakdownContainer');
    if (!container) return;

    const items = (categories && categories.length > 0) ? categories : services;

    if (!items || items.length === 0) {
      container.innerHTML = `
        <div style="text-align:center;padding:30px;color:var(--text-tertiary);">
          ยังไม่มีรายการค่าบริการแยกประเภทในรอบปัจจุบัน
        </div>
      `;
      return;
    }

    const colorMap = {
      'server_runtime': 'linear-gradient(90deg, #0075FF, #00D4FF)',
      'public_ipv4':    'linear-gradient(90deg, #FF9900, #FF5500)',
      'storage':        'linear-gradient(90deg, #01B574, #48BB78)',
      'bandwidth':      'linear-gradient(90deg, #7928CA, #FF0080)',
      'tax_other':      'linear-gradient(90deg, #64748B, #94A3B8)'
    };

    let html = '';
    items.forEach((s, idx) => {
      const amt = parseFloat(s.amount || 0);
      const pct = s.percentage !== undefined ? s.percentage : (totalMtd > 0 ? Math.min(100, Math.round((amt / totalMtd) * 100)) : 0);
      const barColor = colorMap[s.id] || 'linear-gradient(90deg, #0075FF, #00D4FF)';
      const title = s.nameTh || s.name || s.service || 'Service';
      const qtyText = s.quantityText && s.quantityText !== '-' ? ` <span style="font-size:11px;color:var(--text-tertiary);margin-left:6px;">(${s.quantityText})</span>` : '';

      html += `
        <div class="svc-item">
          <div class="svc-item-header">
            <span class="svc-name">
              <span style="width:10px;height:10px;border-radius:3px;background:${barColor};display:inline-block;flex-shrink:0;"></span>
              <span>${escapeHtml(title)}${qtyText}</span>
            </span>
            <span class="svc-cost">
              ${formatMoney(amt)} <span style="font-size:11px;color:var(--text-tertiary);font-weight:normal;">(${pct}%)</span>
            </span>
          </div>
          <div class="svc-bar-bg">
            <div class="svc-bar-fill" style="width: ${pct}%; background: ${barColor};"></div>
          </div>
        </div>
      `;
    });

    container.innerHTML = html;
  }

  // Render Daily Cost Breakdown Table
  function renderDailyTable(dailyBreakdown) {
    const tbody = document.getElementById('dailyBreakdownTableBody');
    if (!tbody) return;

    if (!dailyBreakdown || dailyBreakdown.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-tertiary);">ไม่มีข้อมูลการใช้งานรายวัน</td></tr>`;
      return;
    }

    const todayStr = new Date().toISOString().substring(0, 10);
    let html = '';

    // Show latest days first
    [...dailyBreakdown].reverse().forEach(row => {
      const date = row.date;
      const isToday = (date === todayStr);
      const total = parseFloat(row.total || 0);
      const runtime = parseFloat(row.server_runtime || 0);
      const ipv4 = parseFloat(row.public_ipv4 || 0);
      const storage = parseFloat(row.storage || 0);
      const bw = parseFloat(row.bandwidth || 0);
      const tax = parseFloat(row.tax_other || 0);

      html += `
        <tr>
          <td>
            <div style="display:inline-flex;align-items:center;justify-content:center;gap:6px;">
              <strong style="color:var(--text-primary);font-family:monospace;">${escapeHtml(date)}</strong>
              ${isToday ? '<span class="daily-badge-today">วันนี้ (Today)</span>' : ''}
            </div>
          </td>
          <td><span style="font-family:monospace;color:${runtime > 0 ? '#00D4FF' : '#94A3B8'};font-weight:${runtime > 0 ? '700' : 'normal'};">${formatMoney(runtime)}</span></td>
          <td><span style="font-family:monospace;color:${ipv4 > 0 ? '#FF9900' : '#94A3B8'};font-weight:${ipv4 > 0 ? '700' : 'normal'};">${formatMoney(ipv4)}</span></td>
          <td><span style="font-family:monospace;color:${storage > 0 ? '#01B574' : '#94A3B8'};font-weight:${storage > 0 ? '700' : 'normal'};">${formatMoney(storage)}</span></td>
          <td><span style="font-family:monospace;color:${bw > 0 ? '#7928CA' : '#94A3B8'};font-weight:${bw > 0 ? '700' : 'normal'};">${formatMoney(bw)}</span></td>
          <td><span style="font-family:monospace;color:${tax > 0 ? '#E2E8F0' : '#94A3B8'};font-weight:${tax > 0 ? '700' : 'normal'};">${formatMoney(tax)}</span></td>
          <td>
            <strong style="font-family:monospace;color:${total > 0 ? '#00D4FF' : '#94A3B8'};font-size:13px;font-weight:700;">${formatMoney(total)}</strong>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  // Render Invoices Table
  function renderInvoicesTable(historyRows) {
    const tbody = document.getElementById('invoicesTableBody');
    if (!tbody) return;

    if (!historyRows || historyRows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text-tertiary);">ไม่มีประวัติบิล</td></tr>`;
      return;
    }

    const currentMonthStr = new Date().toISOString().substring(0, 7);

    let html = '';
    // Display in reverse order (newest first)
    [...historyRows].reverse().forEach(row => {
      const m = row.billingMonth || row.month;
      const amt = parseFloat(row.costAmount || row.amount || 0);
      const isCurr = (m === currentMonthStr);

      html += `
        <tr>
          <td>
            <strong style="color:var(--text-primary);">${escapeHtml(m)}</strong>
            ${isCurr ? '<span style="margin-left:8px;background:rgba(0,117,255,0.2);color:#00D4FF;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;">รอบปัจจุบัน</span>' : ''}
          </td>
          <td><strong style="color:#00D4FF;font-family:monospace;">$${amt.toFixed(2)}</strong></td>
          <td><span style="color:var(--text-secondary);font-family:monospace;">฿${(amt * 35.5).toFixed(2)}</span></td>
          <td>
            ${isCurr 
              ? '<span style="color:#FF9900;font-size:12px;font-weight:600;">⏳ กำลังสะสม (Accruing)</span>' 
              : '<span style="color:#01B574;font-size:12px;font-weight:600;">✔ เรียกเก็บแล้ว (Paid)</span>'}
          </td>
          <td><span style="font-size:11px;color:var(--text-tertiary);">${row.syncedAt || '-'}</span></td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  // Update UI with Billing Data
  function updateUI(data) {
    cachedBillingData = data;
    const vps = data.vps || {};
    const mtdCost = parseFloat(data.totalMtdCost || 0);
    const dailyBurn = parseFloat(data.dailyBurnRate || 0);
    const projectedCost = parseFloat(data.projectedMonthCost || 0);
    const lastSync = data.lastSyncAt || vps.lastSyncAt || '-';
    const currentMonth = data.currentMonth || '-';

    // Top Stats
    document.getElementById('statMtdCost').textContent = formatMoney(mtdCost);
    document.getElementById('statDailyBurn').textContent = `${formatMoney(dailyBurn)} / วัน`;
    document.getElementById('statProjectedCost').textContent = formatMoney(projectedCost);
    document.getElementById('labelLastSync').textContent = lastSync;
    const monthBadge = document.getElementById('dailyMonthBadge');
    if (monthBadge) monthBadge.textContent = currentMonth;

    // Instance details
    const instName = vps.vpsName || 'AWS';
    document.getElementById('statInstanceName').textContent = instName;
    document.getElementById('detailInstanceName').textContent = instName;
    document.getElementById('detailVpsCode').textContent = `รหัส VPS: [${vps.vpsCode || '1'}]`;

    const statusPill = document.getElementById('statInstancePill');
    const statusVal = (vps.instanceStatus || 'unknown').toLowerCase();
    statusPill.textContent = statusVal;
    statusPill.className = 'status-pill ' + (statusVal === 'running' ? 'running' : 'stopped');
    renderEc2ControlButtons(statusVal);

    const instType = vps.instanceType || 't3.micro';
    const region = vps.awsRegion || 'eu-west-2';
    document.getElementById('statInstanceType').textContent = instType;
    document.getElementById('statInstanceRegion').textContent = region;
    document.getElementById('detailInstanceId').textContent = vps.awsInstanceId || 'i-0dfe609694660b8d2';
    document.getElementById('detailRegion').textContent = `Region: ${region}`;

    const publicIp = vps.publicIP || '-';
    document.getElementById('detailPublicIp').textContent = publicIp;
    const ipLink = document.getElementById('detailPublicIpLink');
    if (publicIp !== '-') {
      ipLink.href = 'http://' + publicIp;
    }

    const webUrl = vps.url || '-';
    document.getElementById('detailWebUrlText').textContent = webUrl;
    const urlLink = document.getElementById('detailWebUrl');
    if (webUrl !== '-') {
      urlLink.href = webUrl;
    }

    // Calculate 6-month historical totals
    const history = data.monthlyHistory || [];
    let sum6m = 0;
    history.forEach(item => {
      sum6m += parseFloat(item.costAmount || item.amount || 0);
    });
    const avg6m = history.length > 0 ? (sum6m / history.length) : 0;

    document.getElementById('stat6mTotal').textContent = formatMoney(sum6m);
    document.getElementById('stat6mAvg').textContent = `${formatMoney(avg6m)} / เดือน`;

    // Render 5 Category Cards
    renderCategoryCards(data.categoryBreakdown || [], mtdCost);

    // Render Charts and Breakdown
    renderHistoryChart(history, data.dailyBreakdown || []);
    renderServiceBreakdown(data.categoryBreakdown || [], data.serviceBreakdown || [], mtdCost);
    renderDailyTable(data.dailyBreakdown || []);
    renderInvoicesTable(history);
  }

  // Fetch Billing Data
  function loadBillingSummary() {
    fetch(API_BILLING + '?action=get_summary')
      .then(res => res.json())
      .then(res => {
        if (res.success && res.data) {
          updateUI(res.data);
        } else {
          console.error('Error loading billing summary:', res.error);
        }
      })
      .catch(err => {
        console.error('Fetch error:', err);
      });
  }

  // Trigger Sync with AWS
  function triggerSync() {
    const syncBtn = document.getElementById('btnSyncAws');
    const syncText = document.getElementById('syncBtnText');
    syncBtn.classList.add('spinning');
    syncBtn.disabled = true;
    syncText.textContent = 'กำลังเชื่อมต่อ AWS...';

    fetch(API_BILLING + '?action=sync', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
      .then(res => res.json())
      .then(res => {
        syncBtn.classList.remove('spinning');
        syncBtn.disabled = false;
        syncText.textContent = 'Sync กับ AWS';

        if (res.success) {
          loadBillingSummary();
        } else {
          alert('เกิดข้อผิดพลาดในการ Sync กับ AWS: ' + (res.error || 'Unknown error'));
        }
      })
      .catch(err => {
        syncBtn.classList.remove('spinning');
        syncBtn.disabled = false;
        syncText.textContent = 'Sync กับ AWS';
        alert('Network Error: ' + err.message);
      });
  }

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Events
  document.getElementById('btnSyncAws').addEventListener('click', triggerSync);

  const btnMonthly = document.getElementById('btnChartMonthly');
  const btnDaily = document.getElementById('btnChartDaily');

  if (btnMonthly && btnDaily) {
    btnMonthly.addEventListener('click', function() {
      if (chartMode === 'monthly') return;
      chartMode = 'monthly';
      btnMonthly.classList.add('active');
      btnDaily.classList.remove('active');
      if (cachedBillingData) {
        renderHistoryChart(cachedBillingData.monthlyHistory || [], cachedBillingData.dailyBreakdown || []);
      }
    });

    btnDaily.addEventListener('click', function() {
      if (chartMode === 'daily') return;
      chartMode = 'daily';
      btnDaily.classList.add('active');
      btnMonthly.classList.remove('active');
      if (cachedBillingData) {
        renderHistoryChart(cachedBillingData.monthlyHistory || [], cachedBillingData.dailyBreakdown || []);
      }
    });
  }
  
  // =========================================================================
  // EC2 INSTANCE CONTROL (START / STOP / POLLING / MODAL)
  // =========================================================================
  let toastTimer = null;
  function showToast(msg, type = 'info', icon = '🔔', duration = 4500) {
    const toast = document.getElementById('billingToast');
    const toastMsg = document.getElementById('toastMsg');
    const toastIcon = document.getElementById('toastIcon');
    if (!toast || !toastMsg) return;

    toast.className = `billing-toast ${type} show`;
    toastMsg.textContent = msg;
    if (toastIcon) toastIcon.textContent = icon;

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toast.classList.remove('show');
    }, duration);
  }

  let ec2PollTimer = null;
  let currentModalAction = null; // 'start' or 'stop'

  function renderEc2ControlButtons(status) {
    const btnQuick = document.getElementById('btnQuickEc2Control');
    const btnHeader = document.getElementById('btnHeaderEc2Control');
    const cardAws = document.getElementById('cardAwsInstance');

    const st = (status || 'unknown').toLowerCase();

    // Update Card background border highlight
    if (cardAws) {
      cardAws.className = 'b-stat-card ' + (st === 'running' ? 'green' : (st === 'stopped' ? 'red' : ''));
    }

    const configureBtn = (btn) => {
      if (!btn) return;
      btn.style.display = 'inline-flex';

      if (st === 'running') {
        btn.className = 'btn-ec2-control stop-mode';
        btn.disabled = false;
        btn.innerHTML = `
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h12v12H6z"/></svg>
          <span>Stop Server</span>
        `;
        btn.title = 'สั่งปิดเครื่อง AWS EC2 (หยุดชั่วคราวเพื่อประหยัดค่า Compute Runtime)';
        btn.onclick = (e) => { e.stopPropagation(); openEc2ConfirmModal('stop'); };
      } else if (st === 'stopped') {
        btn.className = 'btn-ec2-control start-mode';
        btn.disabled = false;
        btn.innerHTML = `
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          <span>Start Server</span>
        `;
        btn.title = 'สั่งเปิดเครื่อง AWS EC2 (เริ่มการทำงานของเซิร์ฟเวอร์)';
        btn.onclick = (e) => { e.stopPropagation(); openEc2ConfirmModal('start'); };
      } else if (st === 'pending' || st === 'stopping') {
        btn.className = 'btn-ec2-control transition-mode';
        btn.disabled = true;
        const txt = st === 'pending' ? 'กำลังเปิดเครื่อง...' : 'กำลังปิดเครื่อง...';
        btn.innerHTML = `
          <span class="control-spinner"></span>
          <span>${txt}</span>
        `;
        btn.title = 'AWS กำลังประมวลผลการเปลี่ยนสถานะ โปรดรอสักครู่...';
      } else {
        btn.className = 'btn-ec2-control transition-mode';
        btn.disabled = false;
        btn.innerHTML = `<span>Refresh State</span>`;
        btn.onclick = (e) => { e.stopPropagation(); pollEc2StatusOnce(); };
      }
    };

    configureBtn(btnQuick);
    configureBtn(btnHeader);
  }

  function openEc2ConfirmModal(action) {
    currentModalAction = action;
    const modal = document.getElementById('ec2ConfirmModal');
    const alertBox = document.getElementById('modalEc2AlertBox');
    const modalIcon = document.getElementById('modalEc2Icon');
    const modalTitle = document.getElementById('modalEc2Title');
    const btnExecute = document.getElementById('btnExecuteEc2Action');
    const btnExecuteText = document.getElementById('btnExecuteText');

    const vps = cachedBillingData?.vps || {};
    const instName = vps.vpsName || 'AWS London (nutv99)';
    const instId = vps.awsInstanceId || 'i-0dfe609694660b8d2';
    const region = vps.awsRegion || 'eu-west-2';
    const currentSt = (vps.instanceStatus || 'unknown').toLowerCase();

    document.getElementById('modalEc2InstanceName').textContent = instName;
    document.getElementById('modalEc2InstanceId').textContent = instId;
    document.getElementById('modalEc2Region').textContent = region;
    
    const currPill = document.getElementById('modalEc2CurrentState');
    currPill.textContent = currentSt;
    currPill.className = 'status-pill ' + (currentSt === 'running' ? 'running' : 'stopped');

    if (action === 'stop') {
      modalIcon.innerHTML = '⏹';
      modalIcon.style.background = 'rgba(227, 26, 26, 0.2)';
      modalIcon.style.color = '#FF5252';
      modalTitle.textContent = 'ยืนยันคำสั่งปิดเครื่อง (Stop Server)';
      modalTitle.style.color = '#FF5252';
      
      alertBox.style.background = 'rgba(227, 26, 26, 0.15)';
      alertBox.style.border = '1px solid rgba(227, 26, 26, 0.35)';
      alertBox.style.color = '#FF8A8A';
      alertBox.innerHTML = `
        <strong>⚠️ คำเตือนสำคัญ:</strong> การสั่ง Stop จะทำให้เซิร์ฟเวอร์และโปรแกรมเทรดทั้งหมดหยุดทำงานทันที<br>
        <span style="font-size:12px;opacity:0.9;margin-top:5px;display:block;">
          • ค่าชั่วโมงเปิดเครื่อง (EC2 Compute Runtime) จะหยุดคิดเงินทันที ($0.00)<br>
          • พื้นที่จัดเก็บ EBS Disk ยังคงคิดเงินตามขนาดปกติ
        </span>
      `;

      btnExecute.style.background = 'linear-gradient(135deg, #E31A1A 0%, #B71515 100%)';
      btnExecute.style.color = '#fff';
      btnExecute.style.borderColor = '#E31A1A';
      btnExecuteText.textContent = '⏹ ยืนยันปิดเครื่อง (Stop)';
    } else {
      modalIcon.innerHTML = '▶';
      modalIcon.style.background = 'rgba(1, 181, 116, 0.2)';
      modalIcon.style.color = '#01B574';
      modalTitle.textContent = 'ยืนยันคำสั่งเปิดเครื่อง (Start Server)';
      modalTitle.style.color = '#01B574';

      alertBox.style.background = 'rgba(1, 181, 116, 0.15)';
      alertBox.style.border = '1px solid rgba(1, 181, 116, 0.35)';
      alertBox.style.color = '#68D391';
      alertBox.innerHTML = `
        <strong>🚀 เตรียมเริ่มระบบ:</strong> ระบบจะส่งคำสั่งเปิดเครื่อง EC2 ไปยัง AWS ทันที<br>
        <span style="font-size:12px;opacity:0.9;margin-top:5px;display:block;">
          • ใช้เวลาบูตประมาณ 15 - 30 วินาที<br>
          • เมื่อเครื่องพร้อม ระบบจะตรวจจับและดึง Public IP ใหม่มาอัปเดตให้อัตโนมัติ
        </span>
      `;

      btnExecute.style.background = 'linear-gradient(135deg, #01B574 0%, #00875A 100%)';
      btnExecute.style.color = '#fff';
      btnExecute.style.borderColor = '#01B574';
      btnExecuteText.textContent = '▶ ยืนยันเปิดเครื่อง (Start)';
    }

    modal.classList.add('active');
  }

  function closeEc2ConfirmModal() {
    const modal = document.getElementById('ec2ConfirmModal');
    if (modal) modal.classList.remove('active');
    currentModalAction = null;
  }

  // Modal Close Events
  document.getElementById('btnCancelEc2Modal')?.addEventListener('click', closeEc2ConfirmModal);
  document.getElementById('btnDismissEc2Modal')?.addEventListener('click', closeEc2ConfirmModal);
  document.getElementById('ec2ConfirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEc2ConfirmModal();
  });

  // Execute Action
  document.getElementById('btnExecuteEc2Action')?.addEventListener('click', function() {
    if (!currentModalAction) return;
    const action = currentModalAction;
    closeEc2ConfirmModal();

    const vps = cachedBillingData?.vps || {};
    const vpsId = vps.id || 1;

    // Transition state
    const tempState = (action === 'start') ? 'pending' : 'stopping';
    if (vps) vps.instanceStatus = tempState;
    renderEc2ControlButtons(tempState);
    const statusPill = document.getElementById('statInstancePill');
    if (statusPill) {
      statusPill.textContent = tempState;
      statusPill.className = 'status-pill running';
    }

    showToast(`กำลังส่งคำสั่ง ${action.toUpperCase()} ไปยัง AWS...`, 'info', '⏳');

    fetch(`${API_BILLING}?action=control_instance`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ command: action, vpsId: vpsId })
    })
    .then(res => res.json())
    .then(res => {
      if (!res.success) {
        throw new Error(res.error || 'Failed to control instance');
      }
      showToast(res.message || `ส่งคำสั่ง ${action.toUpperCase()} สำเร็จ กำลังรอสถานะจาก AWS...`, 'info', '⚡');
      startEc2StatusPolling(action);
    })
    .catch(err => {
      console.error('EC2 Control Error:', err);
      showToast(`เกิดข้อผิดพลาด: ${err.message}`, 'error', '❌', 6000);
      pollEc2StatusOnce();
    });
  });

  function startEc2StatusPolling(expectedAction) {
    if (ec2PollTimer) clearInterval(ec2PollTimer);

    let attempts = 0;
    const maxAttempts = 30; // 30 * 3.5s = ~105s
    const targetState = (expectedAction === 'start') ? 'running' : 'stopped';

    ec2PollTimer = setInterval(() => {
      attempts++;
      const vpsId = cachedBillingData?.vps?.id || 1;

      fetch(`${API_BILLING}?action=get_status&vpsId=${vpsId}`)
        .then(res => res.json())
        .then(res => {
          if (res.success && res.data) {
            const inst = res.data;
            const currentState = (inst.state || '').toLowerCase();

            if (cachedBillingData && cachedBillingData.vps) {
              cachedBillingData.vps.instanceStatus = currentState;
              if (inst.publicIp) {
                cachedBillingData.vps.publicIP = inst.publicIp;
                const ipElem = document.getElementById('detailPublicIp');
                if (ipElem) ipElem.textContent = inst.publicIp;
                const ipLink = document.getElementById('detailPublicIpLink');
                if (ipLink) ipLink.href = 'http://' + inst.publicIp;
              }
            }

            const statusPill = document.getElementById('statInstancePill');
            if (statusPill) {
              statusPill.textContent = currentState;
              statusPill.className = 'status-pill ' + (currentState === 'running' ? 'running' : 'stopped');
            }

            renderEc2ControlButtons(currentState);

            if (currentState === targetState) {
              clearInterval(ec2PollTimer);
              ec2PollTimer = null;
              const msg = (currentState === 'running')
                ? `เซิร์ฟเวอร์เปิดทำงานเรียบร้อยแล้ว (IP: ${inst.publicIp || '-'})`
                : `เซิร์ฟเวอร์หยุดการทำงานเรียบร้อยแล้ว (Stopped)`;
              showToast(msg, 'success', '✅', 6000);
            }
          }
        })
        .catch(err => console.warn('Polling error:', err));

      if (attempts >= maxAttempts) {
        clearInterval(ec2PollTimer);
        ec2PollTimer = null;
        showToast('การเปลี่ยนสถานะใช้เวลานานกว่าปกติ โปรดกดปุ่ม Sync เพื่ออัปเดตข้อมูล', 'info', 'ℹ️');
      }
    }, 3500);
  }

  function pollEc2StatusOnce() {
    const vpsId = cachedBillingData?.vps?.id || 1;
    fetch(`${API_BILLING}?action=get_status&vpsId=${vpsId}`)
      .then(res => res.json())
      .then(res => {
        if (res.success && res.data) {
          const currentState = (res.data.state || 'unknown').toLowerCase();
          if (cachedBillingData && cachedBillingData.vps) {
            cachedBillingData.vps.instanceStatus = currentState;
          }
          const statusPill = document.getElementById('statInstancePill');
          if (statusPill) {
            statusPill.textContent = currentState;
            statusPill.className = 'status-pill ' + (currentState === 'running' ? 'running' : 'stopped');
          }
          renderEc2ControlButtons(currentState);
        }
      });
  }

  // Initial Load
  loadBillingSummary();
})();
</script>
