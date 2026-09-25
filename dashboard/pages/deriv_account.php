<?php
/**
 * deriv_account.php
 * Deriv Account Management (CRUD)
 * Vision UI Dark Glassmorphism Design
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-deriv-account">
  <style>
    /* Scoped styling for Deriv Account Manager */
    .deriv-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .deriv-stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 20px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
      position: relative;
      overflow: hidden;
    }
    .deriv-stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: var(--stat-glow, linear-gradient(90deg, #0075ff, #00d4ff));
      opacity: 0.8;
    }
    .deriv-stat-val {
      font-size: 26px;
      font-weight: 800;
      color: var(--text-primary);
      margin-top: 4px;
    }
    .deriv-stat-label {
      font-size: 13px;
      color: var(--text-tertiary);
      font-weight: 500;
    }
    .deriv-stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--stat-icon-bg, var(--accent-gradient));
      box-shadow: 0 4px 12px rgba(0, 117, 255, 0.3);
    }
    .deriv-stat-icon svg {
      width: 24px;
      height: 24px;
      fill: #fff;
    }

    /* Action bar */
    .deriv-action-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 20px;
    }
    .deriv-search-group {
      display: flex;
      align-items: center;
      gap: 12px;
      flex: 1;
      max-width: 540px;
    }
    .deriv-search-box {
      display: flex;
      align-items: center;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 8px 16px;
      flex: 1;
      gap: 10px;
      transition: var(--transition-fast);
    }
    .deriv-search-box:focus-within {
      border-color: var(--accent-blue);
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
    }
    .deriv-search-box svg {
      width: 18px;
      height: 18px;
      fill: var(--text-tertiary);
      flex-shrink: 0;
    }
    .deriv-search-box input {
      background: transparent;
      border: none;
      outline: none;
      color: var(--text-primary);
      font-size: 13px;
      width: 100%;
    }
    .deriv-filter-select {
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 8px 14px;
      color: var(--text-primary);
      font-size: 13px;
      outline: none;
      cursor: pointer;
    }

    /* Badges & Chips */
    .deriv-acc-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(0, 117, 255, 0.12);
      color: #00d4ff;
      border: 1px solid rgba(0, 212, 255, 0.25);
      padding: 5px 10px;
      border-radius: 8px;
      font-family: monospace;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .deriv-token-wrapper {
      display: flex;
      align-items: center;
      gap: 6px;
      max-width: 260px;
    }
    .deriv-token-text {
      font-family: monospace;
      font-size: 12px;
      color: var(--text-secondary);
      background: rgba(15, 23, 42, 0.5);
      padding: 4px 8px;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      flex: 1;
    }
    .deriv-mini-btn {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: var(--text-secondary);
      padding: 4px 6px;
      border-radius: 6px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .deriv-mini-btn:hover {
      background: rgba(0, 117, 255, 0.25);
      color: #fff;
      border-color: var(--accent-blue);
    }
    .deriv-mini-btn svg {
      width: 14px;
      height: 14px;
      fill: currentColor;
    }

    .deriv-tag-app {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(117, 81, 233, 0.12);
      color: #b794f4;
      border: 1px solid rgba(183, 148, 244, 0.25);
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
    }
    .deriv-tag-appid {
      display: inline-block;
      font-family: monospace;
      font-size: 11px;
      color: var(--text-tertiary);
      margin-top: 3px;
    }

    .deriv-expiry-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 9px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    .deriv-expiry-badge.good {
      background: rgba(1, 181, 116, 0.12);
      color: #01b574;
      border: 1px solid rgba(1, 181, 116, 0.25);
    }
    .deriv-expiry-badge.warn {
      background: rgba(245, 166, 35, 0.15);
      color: #f5a623;
      border: 1px solid rgba(245, 166, 35, 0.3);
    }
    .deriv-expiry-badge.expired {
      background: rgba(227, 26, 26, 0.15);
      color: #e31a1a;
      border: 1px solid rgba(227, 26, 26, 0.3);
    }

    /* Action Buttons */
    .btn-action-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .btn-table-action {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12px;
      color: var(--text-secondary);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .btn-table-action:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
    }
    .btn-table-action.edit:hover {
      background: rgba(0, 117, 255, 0.2);
      border-color: var(--accent-blue);
      color: #00d4ff;
    }
    .btn-table-action.delete:hover {
      background: rgba(227, 26, 26, 0.2);
      border-color: var(--accent-red);
      color: #ff5e5e;
    }

    /* Modal dialog */
    .deriv-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(4, 7, 24, 0.8);
      backdrop-filter: blur(8px);
      z-index: 999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      transition: opacity 0.25s ease;
    }
    .deriv-modal-backdrop.active {
      display: flex;
      opacity: 1;
    }
    .deriv-modal {
      background: linear-gradient(135deg, rgba(16, 26, 68, 0.98) 0%, rgba(6, 11, 40, 0.99) 100%);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: var(--radius-lg);
      width: 100%;
      max-width: 650px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7);
      overflow: hidden;
      transform: scale(0.95);
      transition: transform 0.25s ease;
    }
    .deriv-modal-backdrop.active .deriv-modal {
      transform: scale(1);
    }
    .deriv-modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .deriv-modal-title {
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .deriv-modal-close {
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
    .deriv-modal-close:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
    }
    .deriv-modal-body {
      padding: 24px;
      max-height: 75vh;
      overflow-y: auto;
    }
    .deriv-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    .deriv-form-full {
      grid-column: span 2;
    }
    .deriv-field-label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 6px;
    }
    .deriv-field-label span.req {
      color: var(--accent-red);
    }
    .deriv-input, .deriv-select {
      width: 100%;
      background: rgba(10, 19, 48, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--radius-sm);
      padding: 10px 14px;
      color: #fff;
      font-size: 13px;
      font-family: inherit;
      transition: var(--transition-fast);
      box-sizing: border-box;
    }
    .deriv-input:focus, .deriv-select:focus {
      border-color: var(--accent-blue);
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
      outline: none;
      background: rgba(15, 28, 70, 0.95);
    }
    .deriv-input-group {
      position: relative;
      display: flex;
      align-items: center;
    }
    .deriv-input-group .deriv-input {
      padding-right: 42px;
    }
    .deriv-input-group .input-action-btn {
      position: absolute;
      right: 8px;
      background: transparent;
      border: none;
      color: var(--text-tertiary);
      cursor: pointer;
      padding: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
    }
    .deriv-input-group .input-action-btn:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
    }
    .deriv-modal-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--border-color);
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      background: rgba(0, 0, 0, 0.2);
    }

    /* Confirm delete modal */
    .confirm-modal-box {
      max-width: 420px;
      text-align: center;
      padding: 32px 24px;
    }
    .confirm-modal-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: rgba(227, 26, 26, 0.15);
      color: var(--accent-red);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .confirm-modal-icon svg {
      width: 32px;
      height: 32px;
      fill: currentColor;
    }

    /* Toast Notification */
    #derivToast {
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
    #derivToast.show {
      transform: translateY(0);
      opacity: 1;
      pointer-events: auto;
    }
    #derivToast.error {
      border-color: rgba(227, 26, 26, 0.5);
    }
    #derivToast.success {
      border-color: rgba(1, 181, 116, 0.5);
    }
  </style>

  <!-- Stat Cards -->
  <div class="deriv-stats-grid">
    <!-- Stat 1: Total Accounts -->
    <div class="deriv-stat-card" style="--stat-glow: linear-gradient(90deg, #0075ff, #00d4ff);">
      <div>
        <div class="deriv-stat-label">บัญชีทั้งหมด (Total Accounts)</div>
        <div class="deriv-stat-val" id="statTotalAccounts">0</div>
      </div>
      <div class="deriv-stat-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
      </div>
    </div>

    <!-- Stat 2: Active Accounts -->
    <div class="deriv-stat-card" style="--stat-glow: linear-gradient(90deg, #01b574, #48bb78);">
      <div>
        <div class="deriv-stat-label">พร้อมใช้งาน (Active)</div>
        <div class="deriv-stat-val" id="statActiveAccounts" style="color: #01b574;">0</div>
      </div>
      <div class="deriv-stat-icon" style="background: linear-gradient(135deg, #01b574, #48bb78); box-shadow: 0 4px 12px rgba(1, 181, 116, 0.3);">
        <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
      </div>
    </div>

    <!-- Stat 3: Expired / Expiring Soon -->
    <div class="deriv-stat-card" style="--stat-glow: linear-gradient(90deg, #f5a623, #e31a1a);">
      <div>
        <div class="deriv-stat-label">หมดอายุ / ใกล้หมดอายุ</div>
        <div class="deriv-stat-val" id="statExpiringAccounts" style="color: #f5a623;">0</div>
      </div>
      <div class="deriv-stat-icon" style="background: linear-gradient(135deg, #f5a623, #e31a1a); box-shadow: 0 4px 12px rgba(245, 166, 35, 0.3);">
        <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
      </div>
    </div>

    <!-- Stat 4: App Count -->
    <div class="deriv-stat-card" style="--stat-glow: linear-gradient(90deg, #7551e9, #b794f4);">
      <div>
        <div class="deriv-stat-label">จำนวนแอปพลิเคชัน (Apps)</div>
        <div class="deriv-stat-val" id="statUniqueApps" style="color: #b794f4;">0</div>
      </div>
      <div class="deriv-stat-icon" style="background: linear-gradient(135deg, #7551e9, #b794f4); box-shadow: 0 4px 12px rgba(117, 81, 233, 0.3);">
        <svg viewBox="0 0 24 24"><path d="M4 8h4V4H4v4zm6 12h4v-4h-4v4zm-6 0h4v-4H4v4zm0-6h4v-4H4v4zm6 0h4v-4h-4v4zm6-10v4h4V4h-4zm-6 4h4V4h-4v4zm6 6h4v-4h-4v4zm0 6h4v-4h-4v4z"/></svg>
      </div>
    </div>
  </div>

  <!-- Main Card -->
  <div class="card">
    <div class="card-header">
      <div>
        <h2 class="card-title">Deriv Accounts Management</h2>
        <p class="card-subtitle">จัดการข้อมูลบัญชี Deriv, API Token, App ID และวันหมดอายุ (CRUD)</p>
      </div>

      <div class="btn-group">
        <button type="button" class="btn btn-secondary" id="btnRefreshDeriv" title="รีเฟรชข้อมูล">
          <svg style="width:16px;height:16px;fill:currentColor;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
          รีเฟรช
        </button>
        <button type="button" class="btn btn-primary" id="btnOpenAddModal">
          <svg style="width:16px;height:16px;fill:currentColor;" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
          + เพิ่มบัญชี Deriv
        </button>
      </div>
    </div>

    <!-- Action & Filter Bar -->
    <div class="deriv-action-bar">
      <div class="deriv-search-group">
        <div class="deriv-search-box">
          <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
          <input type="text" id="derivSearchInput" placeholder="ค้นหา Email, AccountID, AppName, TokenName...">
        </div>
        <select id="derivStatusFilter" class="deriv-filter-select">
          <option value="all">สถานะทั้งหมด</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="expired">Expired (หมดอายุ)</option>
        </select>
      </div>

      <div style="font-size: 12px; color: var(--text-tertiary);" id="derivTableSummary">
        แสดง 0 บัญชี
      </div>
    </div>

    <!-- Table Container -->
    <div class="table-responsive">
      <table class="data-table" id="derivTable">
        <thead>
          <tr>
            <th style="width: 60px;">#ID</th>
            <th>Account ID / Email</th>
            <th>App Name & App ID</th>
            <th>Token Name / Token</th>
            <th>Expiry Date</th>
            <th>สถานะ</th>
            <th style="text-align: right; width: 140px;">จัดการ (Actions)</th>
          </tr>
        </thead>
        <tbody id="derivTableBody">
          <tr>
            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-tertiary);">
              <div class="spinner" style="margin: 0 auto 12px;"></div>
              กำลังโหลดข้อมูล Deriv Accounts...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ===== MODAL: CREATE / EDIT DERIV ACCOUNT ===== -->
<div class="deriv-modal-backdrop" id="derivModalBackdrop">
  <div class="deriv-modal">
    <div class="deriv-modal-header">
      <h3 class="deriv-modal-title" id="derivModalTitle">
        <svg style="width:20px;height:20px;fill:var(--accent-blue);" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>
        <span id="modalFormTitleText">เพิ่มบัญชี Deriv Account</span>
      </h3>
      <button type="button" class="deriv-modal-close" id="btnModalClose">&times;</button>
    </div>

    <form id="derivAccountForm">
      <input type="hidden" id="derivId" value="">
      <input type="hidden" id="derivFormAction" value="create">

      <div class="deriv-modal-body">
        <div class="deriv-form-grid">
          <!-- Email (Full) -->
          <div class="deriv-form-full">
            <label class="deriv-field-label">Email (อีเมล) <span class="req">*</span></label>
            <input type="email" class="deriv-input" id="formEmail" placeholder="เช่น fantathailandfanta@gmail.com" required>
          </div>

          <!-- AccountID -->
          <div>
            <label class="deriv-field-label">Account ID (รหัสบัญชี)</label>
            <input type="text" class="deriv-input" id="formAccountId" placeholder="เช่น DOT94414158 หรือ CR123456">
          </div>

          <!-- Status -->
          <div>
            <label class="deriv-field-label">สถานะ (Status)</label>
            <select class="deriv-select" id="formStatus">
              <option value="active">Active (เปิดใช้งาน)</option>
              <option value="inactive">Inactive (ปิดใช้งาน)</option>
            </select>
          </div>

          <!-- AppName -->
          <div>
            <label class="deriv-field-label">AppName (ชื่อแอป)</label>
            <input type="text" class="deriv-input" id="formAppName" placeholder="เช่น fantathailand">
          </div>

          <!-- AppID -->
          <div>
            <label class="deriv-field-label">AppID (รหัสแอป Deriv)</label>
            <input type="text" class="deriv-input" id="formAppId" placeholder="เช่น 34i6ru7qJ0CQtfnWNTApX">
          </div>

          <!-- tokenName -->
          <div>
            <label class="deriv-field-label">Token Name (ชื่อโทเค็น)</label>
            <input type="text" class="deriv-input" id="formTokenName" placeholder="เช่น Oracle4">
          </div>

          <!-- Expiry Date -->
          <div>
            <label class="deriv-field-label">Expiry Date (วันหมดอายุ)</label>
            <input type="date" class="deriv-input" id="formExpiryDate">
          </div>

          <!-- token (Full) -->
          <div class="deriv-form-full">
            <label class="deriv-field-label">Token (Personal Access Token / API Token)</label>
            <div class="deriv-input-group">
              <input type="password" class="deriv-input" id="formToken" placeholder="เช่น pat_ab25a5233f2c9008a0f4e6930ed965be5c32fa2997b2afae4629b7f0dc93629d">
              <button type="button" class="input-action-btn" id="btnToggleTokenVisibility" title="แสดง/ซ่อน Token">
                <svg id="eyeIcon" style="width:16px;height:16px;fill:currentColor;" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
              </button>
            </div>
            <small style="color: var(--text-tertiary); font-size: 11px; margin-top: 4px; display: block;">
              โทเค็นเข้าถึง Deriv API สำหรับการเทรดหรืออ่านข้อมูล
            </small>
          </div>
        </div>
      </div>

      <div class="deriv-modal-footer">
        <button type="button" class="btn btn-secondary" id="btnCancelDerivModal">ยกเลิก</button>
        <button type="submit" class="btn btn-primary" id="btnSaveDeriv">
          💾 บันทึกข้อมูล
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL: DELETE CONFIRMATION ===== -->
<div class="deriv-modal-backdrop" id="deleteModalBackdrop">
  <div class="deriv-modal confirm-modal-box">
    <div class="confirm-modal-icon">
      <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
    </div>
    <h3 style="color:#fff; font-size:18px; margin-bottom: 8px;">ยืนยันการลบบัญชี Deriv?</h3>
    <p style="color:var(--text-tertiary); font-size:13px; margin-bottom: 24px;">
      คุณต้องการลบบัญชี <span id="delAccTarget" style="color:#00d4ff; font-weight:700;"></span> ออกจากระบบหรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้
    </p>
    <div style="display:flex; justify-content:center; gap:12px;">
      <button type="button" class="btn btn-secondary" id="btnCancelDelete">ยกเลิก</button>
      <button type="button" class="btn btn-primary" id="btnConfirmDelete" style="background: linear-gradient(135deg, #e31a1a, #9b1111); border-color: #e31a1a;">
        ลบข้อมูลถาวร
      </button>
    </div>
  </div>
</div>

<!-- Toast Element -->
<div id="derivToast">
  <span id="derivToastMsg">Notification message</span>
</div>

<script>
(function() {
  const API_URL = '../php/api_deriv_account.php';
  let accountList = [];
  let deleteTargetId = null;

  // DOM Elements
  const tableBody = document.getElementById('derivTableBody');
  const searchInput = document.getElementById('derivSearchInput');
  const statusFilter = document.getElementById('derivStatusFilter');
  const tableSummary = document.getElementById('derivTableSummary');
  const modalBackdrop = document.getElementById('derivModalBackdrop');
  const modalTitle = document.getElementById('modalFormTitleText');
  const accountForm = document.getElementById('derivAccountForm');
  const derivIdInput = document.getElementById('derivId');
  const formActionInput = document.getElementById('derivFormAction');
  
  const formEmail = document.getElementById('formEmail');
  const formAccountId = document.getElementById('formAccountId');
  const formStatus = document.getElementById('formStatus');
  const formAppName = document.getElementById('formAppName');
  const formAppId = document.getElementById('formAppId');
  const formTokenName = document.getElementById('formTokenName');
  const formExpiryDate = document.getElementById('formExpiryDate');
  const formToken = document.getElementById('formToken');
  const btnToggleToken = document.getElementById('btnToggleTokenVisibility');

  const deleteModalBackdrop = document.getElementById('deleteModalBackdrop');
  const delAccTarget = document.getElementById('delAccTarget');
  const btnConfirmDelete = document.getElementById('btnConfirmDelete');
  const btnCancelDelete = document.getElementById('btnCancelDelete');

  // Stats Elements
  const statTotalAccounts = document.getElementById('statTotalAccounts');
  const statActiveAccounts = document.getElementById('statActiveAccounts');
  const statExpiringAccounts = document.getElementById('statExpiringAccounts');
  const statUniqueApps = document.getElementById('statUniqueApps');

  // Toast Function
  function showToast(msg, type = 'success') {
    const toast = document.getElementById('derivToast');
    const toastMsg = document.getElementById('derivToastMsg');
    if (!toast || !toastMsg) return;
    toastMsg.textContent = msg;
    toast.className = 'show ' + (type === 'error' ? 'error' : 'success');
    setTimeout(() => {
      toast.className = '';
    }, 3500);
  }

  // Copy to clipboard helper
  window.copyDerivText = function(text, label) {
    if (!text) return;
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(() => {
        showToast(`คัดลอก ${label} เรียบร้อยแล้ว: ${text.substring(0, 18)}...`);
      }).catch(() => fallbackCopy(text, label));
    } else {
      fallbackCopy(text, label);
    }
  };

  function fallbackCopy(text, label) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
      showToast(`คัดลอก ${label} เรียบร้อยแล้ว`);
    } catch (e) {
      showToast('ไม่สามารถคัดลอกได้', 'error');
    }
    document.body.removeChild(ta);
  }

  // Format Date for display
  function formatDateDisplay(dateStr) {
    if (!dateStr) return '<span style="color:var(--text-tertiary);">-</span>';
    const parts = dateStr.split('-');
    if (parts.length === 3) {
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
  }

  // Update Stats Cards
  function updateStats(items) {
    if (!statTotalAccounts) return;
    const total = items.length;
    let active = 0;
    let expiringOrExpired = 0;
    const apps = new Set();

    items.forEach(item => {
      if (item.status === 'active' && !item.is_expired) {
        active++;
      }
      if (item.is_expired || (item.days_remaining !== null && item.days_remaining <= 30)) {
        expiringOrExpired++;
      }
      if (item.appName && item.appName.trim() !== '') {
        apps.add(item.appName.trim());
      }
    });

    statTotalAccounts.textContent = total;
    statActiveAccounts.textContent = active;
    statExpiringAccounts.textContent = expiringOrExpired;
    statUniqueApps.textContent = apps.size;
  }

  // Render Table
  function renderTable(items) {
    if (!tableBody) return;

    if (!items || items.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align:center; padding: 40px; color: var(--text-tertiary);">
            <div style="font-size: 32px; margin-bottom: 8px;">📭</div>
            <div>ไม่พบข้อมูลบัญชี Deriv</div>
          </td>
        </tr>
      `;
      if (tableSummary) tableSummary.textContent = 'แสดง 0 บัญชี';
      return;
    }

    let rowsHtml = '';
    items.forEach(item => {
      const accId = item.accountId ? escapeHtml(item.accountId) : '<span style="color:var(--text-tertiary);">ไม่ระบุ</span>';
      const email = escapeHtml(item.email || '');
      const appName = item.appName ? escapeHtml(item.appName) : '-';
      const appId = item.appId ? escapeHtml(item.appId) : '-';
      const tokenName = item.tokenName ? escapeHtml(item.tokenName) : '-';
      const rawToken = item.token || '';
      
      let tokenDisplay = '-';
      if (rawToken) {
        const masked = rawToken.length > 12 
          ? rawToken.substring(0, 7) + '...' + rawToken.substring(rawToken.length - 5)
          : rawToken;
        
        tokenDisplay = `
          <div class="deriv-token-wrapper">
            <span class="deriv-token-text" title="${escapeHtml(rawToken)}">${escapeHtml(masked)}</span>
            <button class="deriv-mini-btn" onclick="copyDerivText('${escapeHtml(rawToken)}', 'Token')" title="คัดลอก Token เต็ม">
              <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
            </button>
          </div>
        `;
      }

      // Expiry badge
      let expiryBadge = '<span style="color:var(--text-tertiary);">-</span>';
      if (item.expiryDate) {
        const dateText = formatDateDisplay(item.expiryDate);
        if (item.is_expired) {
          expiryBadge = `
            <div>
              <span class="deriv-expiry-badge expired">⚠️ หมดอายุแล้ว</span>
              <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;">${dateText}</div>
            </div>
          `;
        } else if (item.days_remaining !== null && item.days_remaining <= 30) {
          expiryBadge = `
            <div>
              <span class="deriv-expiry-badge warn">⏳ อีก ${item.days_remaining} วัน</span>
              <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;">${dateText}</div>
            </div>
          `;
        } else {
          expiryBadge = `
            <div>
              <span class="deriv-expiry-badge good">✓ อีก ${item.days_remaining} วัน</span>
              <div style="font-size:11px;color:var(--text-tertiary);margin-top:2px;">${dateText}</div>
            </div>
          `;
        }
      }

      // Status badge
      let statusBadge = '<span class="badge badge-success">Active</span>';
      if (item.status === 'inactive') {
        statusBadge = '<span class="badge badge-secondary">Inactive</span>';
      }
      if (item.is_expired) {
        statusBadge = '<span class="badge badge-danger">Expired</span>';
      }

      rowsHtml += `
        <tr>
          <td><span style="font-weight:700;color:var(--text-tertiary);font-size:12px;">#${item.id}</span></td>
          <td>
            <div style="display:flex; flex-direction:column; gap:4px;">
              <div style="display:flex; align-items:center; gap:6px;">
                <span class="deriv-acc-badge" onclick="copyDerivText('${accId}', 'AccountID')" style="cursor:pointer;" title="คลิกเพื่อคัดลอก">
                  ${accId}
                </span>
              </div>
              <div style="font-size:13px; color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
                <span>${email}</span>
                <button class="deriv-mini-btn" onclick="copyDerivText('${email}', 'Email')" title="คัดลอกอีเมล" style="padding:2px 4px;">
                  <svg style="width:11px;height:11px;" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                </button>
              </div>
            </div>
          </td>
          <td>
            <div>
              <div class="deriv-tag-app">${appName}</div>
              <div class="deriv-tag-appid" onclick="copyDerivText('${appId}', 'AppID')" style="cursor:pointer;" title="คลิกเพื่อคัดลอก AppID">
                AppID: <span style="color:#00d4ff;">${appId}</span>
              </div>
            </div>
          </td>
          <td>
            <div style="display:flex; flex-direction:column; gap:4px;">
              <div style="font-weight:600; color:var(--text-primary); font-size:13px;">${tokenName}</div>
              ${tokenDisplay}
            </div>
          </td>
          <td>${expiryBadge}</td>
          <td>${statusBadge}</td>
          <td style="text-align: right;">
            <div class="btn-action-group" style="justify-content: flex-end;">
              <button class="btn-table-action edit" onclick="window.openEditDerivModal(${item.id})" title="แก้ไข">
                <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                แก้ไข
              </button>
              <button class="btn-table-action delete" onclick="window.openDeleteDerivModal(${item.id}, '${escapeHtml(accId !== '-' ? accId : email)}')" title="ลบ">
                <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                ลบ
              </button>
            </div>
          </td>
        </tr>
      `;
    });

    tableBody.innerHTML = rowsHtml;
    if (tableSummary) tableSummary.textContent = `แสดง ${items.length} บัญชี`;
  }

  // Escape HTML helper
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Fetch Accounts from Backend API
  function loadAccounts() {
    fetch(API_URL + '?action=list')
      .then(res => res.json())
      .then(json => {
        if (json.success && Array.isArray(json.data)) {
          accountList = json.data;
          updateStats(accountList);
          applyFilterAndSearch();
        } else {
          showToast(json.error || 'โหลดข้อมูลไม่สำเร็จ', 'error');
        }
      })
      .catch(err => {
        console.error(err);
        showToast('ไม่สามารถเชื่อมต่อ Server ได้', 'error');
      });
  }

  // Filter & Search
  function applyFilterAndSearch() {
    const kw = (searchInput.value || '').trim().toLowerCase();
    const st = statusFilter.value;

    const filtered = accountList.filter(item => {
      // Status Filter
      if (st === 'active' && (item.status !== 'active' || item.is_expired)) return false;
      if (st === 'inactive' && item.status !== 'inactive') return false;
      if (st === 'expired' && !item.is_expired) return false;

      // Keyword Search
      if (kw === '') return true;
      const haystack = [
        item.email,
        item.appName,
        item.appId,
        item.tokenName,
        item.token,
        item.accountId,
        item.status
      ].filter(Boolean).join(' ').toLowerCase();

      return haystack.includes(kw);
    });

    renderTable(filtered);
  }

  // Search input events
  if (searchInput) {
    searchInput.addEventListener('input', applyFilterAndSearch);
  }
  if (statusFilter) {
    statusFilter.addEventListener('change', applyFilterAndSearch);
  }

  // Modal Handling
  function openModal(isEdit = false) {
    if (!modalBackdrop) return;
    modalTitle.textContent = isEdit ? 'แก้ไขข้อมูล Deriv Account' : 'เพิ่มบัญชี Deriv Account';
    modalBackdrop.classList.add('active');
  }

  function closeModal() {
    if (!modalBackdrop) return;
    modalBackdrop.classList.remove('active');
    accountForm.reset();
    derivIdInput.value = '';
    formActionInput.value = 'create';
    formToken.type = 'password';
  }

  document.getElementById('btnOpenAddModal')?.addEventListener('click', () => {
    accountForm.reset();
    derivIdInput.value = '';
    formActionInput.value = 'create';
    formStatus.value = 'active';
    openModal(false);
  });

  document.getElementById('btnModalClose')?.addEventListener('click', closeModal);
  document.getElementById('btnCancelDerivModal')?.addEventListener('click', closeModal);

  // Toggle Token Visibility
  btnToggleToken?.addEventListener('click', () => {
    if (formToken.type === 'password') {
      formToken.type = 'text';
    } else {
      formToken.type = 'password';
    }
  });

  // Edit Account Function
  window.openEditDerivModal = function(id) {
    const item = accountList.find(x => x.id == id);
    if (!item) return;

    derivIdInput.value = item.id;
    formActionInput.value = 'update';
    formEmail.value = item.email || '';
    formAccountId.value = item.accountId || '';
    formStatus.value = item.status || 'active';
    formAppName.value = item.appName || '';
    formAppId.value = item.appId || '';
    formTokenName.value = item.tokenName || '';
    formExpiryDate.value = item.expiryDate || '';
    formToken.value = item.token || '';

    openModal(true);
  };

  // Form Submission (Create / Update)
  accountForm?.addEventListener('submit', function(e) {
    e.preventDefault();

    const action = formActionInput.value;
    const payload = {
      action: action,
      id: derivIdInput.value,
      email: formEmail.value.trim(),
      accountId: formAccountId.value.trim(),
      status: formStatus.value,
      appName: formAppName.value.trim(),
      appId: formAppId.value.trim(),
      tokenName: formTokenName.value.trim(),
      expiryDate: formExpiryDate.value,
      token: formToken.value.trim()
    };

    const saveBtn = document.getElementById('btnSaveDeriv');
    const origText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner" style="width:14px;height:14px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:6px;"></span> บันทึก...';

    fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(json => {
      saveBtn.disabled = false;
      saveBtn.innerHTML = origText;

      if (json.success) {
        showToast(json.message || 'บันทึกข้อมูลเรียบร้อยแล้ว');
        closeModal();
        loadAccounts();
      } else {
        showToast(json.error || 'เกิดข้อผิดพลาดในการบันทึก', 'error');
      }
    })
    .catch(err => {
      saveBtn.disabled = false;
      saveBtn.innerHTML = origText;
      console.error(err);
      showToast('เกิดข้อผิดพลาดในการส่งข้อมูล', 'error');
    });
  });

  // Delete Confirmation Modal
  window.openDeleteDerivModal = function(id, targetName) {
    deleteTargetId = id;
    if (delAccTarget) delAccTarget.textContent = targetName || `#${id}`;
    if (deleteModalBackdrop) deleteModalBackdrop.classList.add('active');
  };

  function closeDeleteModal() {
    deleteTargetId = null;
    if (deleteModalBackdrop) deleteModalBackdrop.classList.remove('active');
  }

  btnCancelDelete?.addEventListener('click', closeDeleteModal);

  btnConfirmDelete?.addEventListener('click', () => {
    if (!deleteTargetId) return;

    btnConfirmDelete.disabled = true;
    btnConfirmDelete.textContent = 'กำลังลบ...';

    fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id: deleteTargetId })
    })
    .then(res => res.json())
    .then(json => {
      btnConfirmDelete.disabled = false;
      btnConfirmDelete.textContent = 'ลบข้อมูลถาวร';
      closeDeleteModal();

      if (json.success) {
        showToast(json.message || 'ลบข้อมูลสำเร็จ');
        loadAccounts();
      } else {
        showToast(json.error || 'ไม่สามารถลบข้อมูลได้', 'error');
      }
    })
    .catch(err => {
      btnConfirmDelete.disabled = false;
      btnConfirmDelete.textContent = 'ลบข้อมูลถาวร';
      closeDeleteModal();
      console.error(err);
      showToast('เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
    });
  });

  // Refresh Button
  document.getElementById('btnRefreshDeriv')?.addEventListener('click', () => {
    showToast('กำลังโหลดข้อมูลใหม่...');
    loadAccounts();
  });

  // Initial Load
  loadAccounts();

})();
</script>
