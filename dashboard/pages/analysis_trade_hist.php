<?php
/**
 * Analysis Trade History Page
 * 2-Panel Layout:
 * - Left Panel: Distinct Trading Dates from tradeHead.startTimeTrade (dd/mm/yyyy)
 * - Right Panel: AimaraJS Treeview { serverCode -> tradeRoundNo -> assetCode } + Round & Asset Details
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-analysis-trade-hist">
  <style>
    /* Scoped Styles for Analysis Trade History */
    .ath-layout {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 24px;
      align-items: start;
    }

    @media (max-width: 992px) {
      .ath-layout {
        grid-template-columns: 1fr;
      }
    }

    .ath-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.94));
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      border-radius: var(--radius-md, 16px);
      padding: 20px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card, 0 8px 32px 0 rgba(0, 0, 0, 0.37));
    }

    .ath-panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
      padding-bottom: 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .ath-panel-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .ath-panel-title svg {
      width: 20px;
      height: 20px;
      fill: var(--accent-primary, #0075ff);
    }

    /* Left Panel Date Picker Filter */
    .ath-date-picker-box {
      margin-bottom: 14px;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .ath-dt-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary, #cbd5e1);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .ath-dt-label svg {
      width: 15px;
      height: 15px;
      fill: var(--accent-primary, #0075ff);
    }

    .ath-dt-input-group {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .ath-dt-input {
      flex: 1;
      background: rgba(10, 19, 48, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      padding: 8px 12px;
      color: #fff;
      font-size: 13px;
      font-family: inherit;
      outline: none;
      color-scheme: dark;
      transition: all 0.2s ease;
      cursor: pointer;
      box-sizing: border-box;
    }

    .ath-dt-input:focus {
      border-color: var(--accent-primary, #0075ff);
      box-shadow: 0 0 8px rgba(0, 117, 255, 0.35);
      background: rgba(15, 28, 70, 0.95);
    }

    .ath-dt-clear-btn {
      padding: 8px 12px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      color: var(--text-secondary, #cbd5e1);
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s ease;
    }

    .ath-dt-clear-btn:hover {
      background: rgba(0, 117, 255, 0.2);
      border-color: var(--accent-primary, #0075ff);
      color: #fff;
    }

    .ath-date-list {
      max-height: calc(100vh - 280px);
      min-height: 260px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 8px;
      padding-right: 4px;
    }

    .ath-date-list::-webkit-scrollbar {
      width: 6px;
    }
    .ath-date-list::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 3px;
    }

    .ath-date-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 14px;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .ath-date-item:hover {
      background: rgba(0, 117, 255, 0.12);
      border-color: rgba(0, 117, 255, 0.35);
      transform: translateX(3px);
    }

    .ath-date-item.active {
      background: linear-gradient(135deg, rgba(0, 117, 255, 0.3) 0%, rgba(30, 20, 100, 0.4) 100%);
      border-color: var(--accent-primary, #0075ff);
      box-shadow: 0 0 14px rgba(0, 117, 255, 0.3);
    }

    .ath-date-info {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    .ath-date-text {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      letter-spacing: 0.5px;
    }

    .ath-date-meta {
      font-size: 11px;
      color: var(--text-tertiary, #a0aec0);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .ath-badge {
      display: inline-flex;
      align-items: center;
      padding: 2px 7px;
      font-size: 11px;
      font-weight: 600;
      border-radius: 6px;
      line-height: 1.2;
    }

    .ath-badge-count {
      background: rgba(0, 117, 255, 0.2);
      color: #60a5fa;
      border: 1px solid rgba(0, 117, 255, 0.35);
    }

    .ath-badge-success {
      background: rgba(16, 185, 129, 0.2);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.35);
    }

    .ath-badge-danger {
      background: rgba(239, 68, 68, 0.2);
      color: #f87171;
      border: 1px solid rgba(239, 68, 68, 0.35);
    }

    .ath-badge-purple {
      background: rgba(147, 51, 234, 0.2);
      color: #c084fc;
      border: 1px solid rgba(147, 51, 234, 0.35);
    }

    /* Right Panel Toolbar */
    .ath-right-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 16px;
      padding-bottom: 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .ath-selected-date-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 16px;
      font-weight: 700;
      color: #fff;
    }

    .ath-toolbar {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .ath-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 12px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .ath-btn:hover {
      background: rgba(0, 117, 255, 0.2);
      border-color: var(--accent-primary, #0075ff);
      transform: translateY(-1px);
    }

    .ath-btn svg {
      width: 14px;
      height: 14px;
      fill: currentColor;
    }

    /* Tree View Box */
    .ath-tree-wrapper {
      background: rgba(10, 19, 48, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 12px;
      padding: 16px 20px;
      min-height: 380px;
      max-height: 520px;
      overflow-y: auto;
      box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.4);
    }

    .ath-tree-wrapper::-webkit-scrollbar {
      width: 6px;
    }
    .ath-tree-wrapper::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 3px;
    }

    /* Tree Node Content Custom Styling */
    .tree-node-server-title {
      font-weight: 700;
      color: #38ef7d;
      letter-spacing: 0.3px;
      white-space: nowrap;
    }

    .tree-node-round-title {
      font-weight: 600;
      color: #60a5fa;
      white-space: nowrap;
    }

    .tree-node-asset-title {
      font-weight: 600;
      color: #fca5a5;
      white-space: nowrap;
    }

    .tree-node-stat {
      font-size: 11px;
      margin-left: 6px;
      color: var(--text-tertiary, #a0aec0);
      white-space: nowrap;
    }

    /* Details Drawer / Card */
    .ath-detail-card {
      margin-top: 20px;
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 18px;
    }

    .ath-detail-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .ath-detail-title {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .ath-meta-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 12px;
      margin-bottom: 16px;
    }

    .ath-meta-item {
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.04);
      border-radius: 8px;
      padding: 8px 12px;
    }

    .ath-meta-label {
      font-size: 11px;
      color: var(--text-tertiary, #a0aec0);
      margin-bottom: 2px;
    }

    .ath-meta-value {
      font-size: 13px;
      font-weight: 600;
      color: #fff;
    }

    /* Trades table */
    .ath-table-container {
      overflow-x: auto;
      max-height: 280px;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }

    .ath-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      text-align: left;
    }

    .ath-table th {
      background: rgba(15, 23, 42, 0.9);
      color: var(--text-secondary, #cbd5e1);
      font-weight: 600;
      padding: 10px 12px;
      position: sticky;
      top: 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      white-space: nowrap;
    }

    .ath-table td {
      padding: 8px 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      color: var(--text-primary, #e2e8f0);
      white-space: nowrap;
    }

    .ath-table tbody tr:hover {
      background: rgba(0, 117, 255, 0.08);
    }

    /* Empty state & loader */
    .ath-empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 20px;
      color: var(--text-tertiary, #a0aec0);
      text-align: center;
      gap: 10px;
    }

    .ath-empty-state svg {
      width: 48px;
      height: 48px;
      fill: rgba(255, 255, 255, 0.15);
    }
  </style>

  <!-- Page Header -->
  <div class="mb-4">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
      <div>
        <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 4px;">
          📊 Analysis Trade History
        </h2>
        <p style="color: var(--text-secondary, #a0aec0); font-size: 13px; margin: 0;">
          สำรวจประวัติการเทรดแบบลำดับชั้น { Server &rarr; Trade Round &rarr; Asset Code } ด้วย AimaraJS Treeview
        </p>
      </div>
      <button class="ath-btn" id="ath-btn-refresh-all" title="รีเฟรชข้อมูลทั้งหมด">
        <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
        Refresh Data
      </button>
    </div>
  </div>

  <!-- Main 2-Panel Layout -->
  <div class="ath-layout">
    
    <!-- LEFT PANEL: Trading Dates -->
    <div class="ath-card">
      <div class="ath-panel-header">
        <div class="ath-panel-title">
          <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z"/></svg>
          Trading Dates
        </div>
        <span class="ath-badge ath-badge-count" id="ath-dates-count">0 dates</span>
      </div>

      <!-- Date Picker Filter -->
      <div class="ath-date-picker-box">
        <label for="ath-date-filter" class="ath-dt-label">
          <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z"/></svg>
          <span>เลือกวันที่ (Date Picker):</span>
        </label>
        <div class="ath-dt-input-group">
          <input type="date" id="ath-date-filter" class="ath-dt-input">
          <button type="button" id="ath-date-filter-clear" class="ath-dt-clear-btn" title="แสดงวันที่ทั้งหมด">
            ทั้งหมด
          </button>
        </div>
      </div>

      <!-- Date Items Container -->
      <div class="ath-date-list" id="ath-date-list">
        <div class="ath-empty-state">
          <div class="spinner" style="width:24px;height:24px;"></div>
          <span>กำลังโหลดวันที่...</span>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL: AimaraJS TreeView & Details -->
    <div class="ath-card">
      <div class="ath-right-header">
        <div class="ath-selected-date-badge">
          <svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:var(--accent-primary,#0075ff);"><path d="M22 11V3h-7v3H9V3H2v8h7V8h2v10h4v3h7v-8h-7v3h-2V8h2v3h7zM4 5h3v4H4V5zm13 14h3v4h-3v-4zm0-14h3v4h-3V5z"/></svg>
          <span id="ath-active-date-title">เลือกวันที่จากแถบซ้าย</span>
          <span class="ath-badge ath-badge-purple" id="ath-tree-stats-badge" style="display:none;">0 Servers</span>
        </div>

        <div class="ath-toolbar">
          <button class="ath-btn" id="ath-btn-expand-all" title="ขยายทุกกิ่ง">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
            Expand All
          </button>
          <button class="ath-btn" id="ath-btn-collapse-all" title="ยุบทุกกิ่ง">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/></svg>
            Collapse All
          </button>
          <button class="ath-btn" id="ath-btn-reload-tree" title="โหลด Tree ใหม่อีกครั้ง">
            <svg viewBox="0 0 24 24"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
            Reload
          </button>
        </div>
      </div>

      <!-- Aimara TreeView Render Container -->
      <div class="ath-tree-wrapper" id="ath-tree-container">
        <div class="ath-empty-state">
          <svg viewBox="0 0 24 24"><path d="M22 11V3h-7v3H9V3H2v8h7V8h2v10h4v3h7v-8h-7v3h-2V8h2v3h7zM4 5h3v4H4V5zm13 14h3v4h-3v-4zm0-14h3v4h-3V5z"/></svg>
          <p>กรุณาคลิกเลือกวันที่จากแถบด้านซ้าย เพื่อค้นหาและแสดงโครงสร้างเซิร์ฟเวอร์ รอบการเทรด และคู่เงิน</p>
        </div>
      </div>

      <!-- Selected Node Detail Card -->
      <div class="ath-detail-card" id="ath-detail-card" style="display:none;">
        <div class="ath-detail-header">
          <div class="ath-detail-title">
            <span id="ath-detail-icon">📌</span>
            <span id="ath-detail-heading">รายละเอียดโหนดที่เลือก</span>
          </div>
          <span class="ath-badge ath-badge-count" id="ath-detail-type-badge">Asset</span>
        </div>

        <!-- Metadata Grid -->
        <div class="ath-meta-grid" id="ath-meta-grid">
          <!-- Populated dynamically -->
        </div>

        <!-- Trades Table for Asset -->
        <div id="ath-trades-section" style="display:none;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span style="font-size:12px; font-weight:700; color:var(--text-secondary,#cbd5e1);">รายการเทรดในรอบนี้ (vpsTradeData)</span>
            <span class="ath-badge ath-badge-count" id="ath-trades-count-badge">0 trades</span>
          </div>
          <div class="ath-table-container">
            <table class="ath-table" id="ath-trades-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>เวลาซื้อ (Purchase)</th>
                  <th>Action</th>
                  <th>ผลลัพธ์ (Win/Loss)</th>
                  <th>เงินเทรด ($)</th>
                  <th>กำไร ($)</th>
                  <th>Balance ($)</th>
                  <th>Diff Spot</th>
                </tr>
              </thead>
              <tbody id="ath-trades-tbody">
                <!-- Populated dynamically -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<script>
(function () {
  'use strict';

  var currentTree = null;
  var allDates = [];
  var activeDateVal = null;
  var activeDateDisplay = null;

  var dateListEl = document.getElementById('ath-date-list');
  var dateFilterInput = document.getElementById('ath-date-filter');
  var datesCountBadge = document.getElementById('ath-dates-count');
  var activeDateTitle = document.getElementById('ath-active-date-title');
  var treeStatsBadge = document.getElementById('ath-tree-stats-badge');
  var treeContainer = document.getElementById('ath-tree-container');

  var btnExpandAll = document.getElementById('ath-btn-expand-all');
  var btnCollapseAll = document.getElementById('ath-btn-collapse-all');
  var btnReloadTree = document.getElementById('ath-btn-reload-tree');
  var btnRefreshAll = document.getElementById('ath-btn-refresh-all');

  var detailCard = document.getElementById('ath-detail-card');
  var detailIcon = document.getElementById('ath-detail-icon');
  var detailHeading = document.getElementById('ath-detail-heading');
  var detailTypeBadge = document.getElementById('ath-detail-type-badge');
  var metaGrid = document.getElementById('ath-meta-grid');
  var tradesSection = document.getElementById('ath-trades-section');
  var tradesCountBadge = document.getElementById('ath-trades-count-badge');
  var tradesTbody = document.getElementById('ath-trades-tbody');

  /**
   * 1. Load dates from API (tradeHead.startTimeTrade formatted as dd/mm/yyyy)
   */
  function fetchTradingDates(autoSelectFirst) {
    dateListEl.innerHTML = '<div class="ath-empty-state"><div class="spinner" style="width:24px;height:24px;"></div><span>กำลังโหลดวันที่...</span></div>';

    fetch('../php/api_analysis_trade_hist.php?action=get_dates')
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (!data.success) throw new Error(data.error || 'Failed to load dates');

        allDates = data.dates || [];
        datesCountBadge.textContent = allDates.length + ' dates';
        renderDateList(allDates);

        if (allDates.length > 0 && autoSelectFirst) {
          selectDate(allDates[0].date_val, allDates[0].date_display);
        } else if (allDates.length === 0) {
          treeContainer.innerHTML = '<div class="ath-empty-state"><p>ไม่พบข้อมูลประวัติการเทรดในตาราง tradeHead</p></div>';
        }
      })
      .catch(function (err) {
        dateListEl.innerHTML = '<div class="ath-empty-state"><span style="color:#f87171;">เกิดข้อผิดพลาด: ' + err.message + '</span></div>';
      });
  }

  /**
   * Render list of dates
   */
  function renderDateList(dates) {
    if (dates.length === 0) {
      dateListEl.innerHTML = '<div class="ath-empty-state"><p>ไม่พบวันที่ที่ค้นหา</p></div>';
      return;
    }

    var html = '';
    dates.forEach(function (d) {
      var isActive = d.date_val === activeDateVal ? ' active' : '';
      html += '<div class="ath-date-item' + isActive + '" data-val="' + d.date_val + '" data-disp="' + d.date_display + '">';
      html += '  <div class="ath-date-info">';
      html += '    <span class="ath-date-text">📅 ' + d.date_display + '</span>';
      html += '    <span class="ath-date-meta">';
      html += '      <span>' + d.total_servers + ' Servers</span> &bull; <span>' + d.total_rounds + ' Rounds</span>';
      html += '    </span>';
      html += '  </div>';
      html += '  <span class="ath-badge ath-badge-count">' + d.total_assets + ' Assets</span>';
      html += '</div>';
    });

    dateListEl.innerHTML = html;

    // Bind click handlers
    dateListEl.querySelectorAll('.ath-date-item').forEach(function (item) {
      item.addEventListener('click', function () {
        var val = this.dataset.val;
        var disp = this.dataset.disp;
        selectDate(val, disp);
      });
    });
  }

  /**
   * Select a date & load Treeview
   */
  function selectDate(dateVal, dateDisplay) {
    activeDateVal = dateVal;
    activeDateDisplay = dateDisplay;

    // Keep datepicker input in sync
    if (dateFilterInput && dateFilterInput.value !== dateVal) {
      dateFilterInput.value = dateVal;
    }

    // Update active highlight in left panel
    dateListEl.querySelectorAll('.ath-date-item').forEach(function (item) {
      if (item.dataset.val === dateVal) {
        item.classList.add('active');
      } else {
        item.classList.remove('active');
      }
    });

    // Update header
    activeDateTitle.textContent = 'วันที่: ' + dateDisplay;
    treeStatsBadge.style.display = 'inline-flex';
    treeStatsBadge.textContent = 'กำลังโหลด...';

    // Hide detail card while loading
    detailCard.style.display = 'none';

    // Fetch tree data
    loadTreeData(dateVal);
  }

  /**
   * 2. Load AimaraJS Treeview for selected date
   */
  function loadTreeData(dateVal) {
    treeContainer.innerHTML = '<div class="ath-empty-state"><div class="spinner" style="width:28px;height:28px;"></div><span>กำลังค้นหาเซิร์ฟเวอร์และรอบการเทรด...</span></div>';

    fetch('../php/api_analysis_trade_hist.php?action=get_tree_data&date=' + encodeURIComponent(dateVal))
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (!data.success) throw new Error(data.error || 'Failed to fetch tree data');

        var servers = data.servers || [];
        treeStatsBadge.textContent = servers.length + ' Servers';

        if (servers.length === 0) {
          treeContainer.innerHTML = '<div class="ath-empty-state"><p>ไม่พบรายการเซิร์ฟเวอร์ในวันที่ ' + activeDateDisplay + '</p></div>';
          return;
        }

        buildAimaraTree(servers);
      })
      .catch(function (err) {
        treeContainer.innerHTML = '<div class="ath-empty-state"><span style="color:#f87171;">เกิดข้อผิดพลาด: ' + err.message + '</span></div>';
        treeStatsBadge.textContent = 'Error';
      });
  }

  /**
   * 3. Construct Tree using AimaraJS Library
   * Hierarchy: { Server -> tradeRoundNo -> assetCode }
   */
  function buildAimaraTree(servers) {
    if (typeof createTree !== 'function') {
      treeContainer.innerHTML = '<div class="ath-empty-state"><span style="color:#f87171;">AimaraJS library is not loaded</span></div>';
      return;
    }

    // Initialize AimaraJS Tree
    currentTree = createTree('ath-tree-container', 'transparent');

    servers.forEach(function (srv) {
      // 1. Root Level: Server Node
      var serverLabel = '<span class="tree-node-server-title">Server [' + srv.serverCode + '] ' + srv.serverName + '</span>' +
                        (srv.publicIP ? '<span class="tree-node-stat">(' + srv.publicIP + ')</span>' : '') +
                        '<span class="tree-badge tree-badge-server">' + srv.rounds.length + ' Rounds</span>';

      var serverNode = currentTree.createNode(
        serverLabel,
        true, // expanded by default
        AIMARA_ICONS.server,
        null, // root
        { type: 'server', data: srv },
        null
      );

      // 2. Child Level 1: tradeRoundNo Node
      srv.rounds.forEach(function (rnd) {
        // Build asset list + profit badges directly on the round node (as requested)
        var assetPills = '<span class="tree-asset-pills-group">';
        var roundTotalProfit = 0;

        rnd.assets.forEach(function (ast) {
          var pnl = parseFloat(ast.totalProfit) || 0;
          roundTotalProfit += pnl;
          var pnlPrefix = pnl >= 0 ? '+$' : '-$';
          var absPnl = Math.abs(pnl).toFixed(2);
          var pillClass = pnl > 0 ? 'tree-asset-pill-win' : (pnl < 0 ? 'tree-asset-pill-loss' : 'tree-asset-pill-zero');

          var chartUrl = 'analysis_trade_chart.php?serverCode=' + srv.serverCode +
                         '&tradeRoundNo=' + rnd.tradeRoundNo +
                         '&assetCode=' + encodeURIComponent(ast.assetCode) +
                         '&date=' + encodeURIComponent(activeDateVal || '');

          assetPills += '<span class="tree-asset-pill ' + pillClass + '" ' +
                          'onclick="event.stopPropagation(); window.open(\'' + chartUrl + '\', \'_blank\');" ' +
                          'style="cursor:pointer;" ' +
                          'title="คลิกเพื่อเปิดดูกราฟแท่งเทียน ' + ast.assetCode + ' ในแท็บใหม่ ↗">' +
                          '<span class="asset-code">' + ast.assetCode + '</span>: ' +
                          '<span class="asset-pnl">' + pnlPrefix + absPnl + '</span>' +
                          '<span style="font-size:10px; margin-left:3px; opacity:0.85;">↗</span>' +
                        '</span>';
        });

        // Round total profit badge
        var roundTotalPrefix = roundTotalProfit >= 0 ? '+$' : '-$';
        var roundTotalClass = roundTotalProfit > 0 ? 'tree-badge-win' : (roundTotalProfit < 0 ? 'tree-badge-loss' : 'tree-badge-round');
        assetPills += '<span class="tree-badge ' + roundTotalClass + '" title="Total Profit Round #' + rnd.tradeRoundNo + '" style="font-size:11px;font-weight:700;">' +
                        'Total: ' + roundTotalPrefix + Math.abs(roundTotalProfit).toFixed(2) +
                      '</span>';
        assetPills += '</span>';

        var roundLabel = '<span class="tree-node-round-title">Round #' + rnd.tradeRoundNo + '</span>' +
                         '<span class="tree-node-stat">[' + rnd.usestrategyCode + '] ' + rnd.durationTrade + '</span>' +
                         assetPills;

        var roundNode = serverNode.createChildNode(
          roundLabel,
          false, // collapsed by default to keep tidy
          AIMARA_ICONS.round,
          { type: 'round', data: rnd, server: srv },
          null
        );

        // 3. Child Level 2: assetCode Node
        rnd.assets.forEach(function (ast) {
          var pnlBadgeClass = ast.totalProfit >= 0 ? 'tree-badge-win' : 'tree-badge-loss';
          var pnlPrefix = ast.totalProfit >= 0 ? '+$' : '-$';
          var absPnl = Math.abs(ast.totalProfit).toFixed(2);

          var chartUrl = 'analysis_trade_chart.php?serverCode=' + srv.serverCode +
                         '&tradeRoundNo=' + rnd.tradeRoundNo +
                         '&assetCode=' + encodeURIComponent(ast.assetCode) +
                         '&date=' + encodeURIComponent(activeDateVal || '');

          var assetLabel = '<span class="tree-node-asset-title">' + ast.assetCode + '</span>' +
                           '<span class="tree-node-stat">(W:' + ast.MaxWinCon + ' | L:' + ast.MaxLossCon + ')</span>' +
                           '<span class="tree-badge ' + pnlBadgeClass + '">' + pnlPrefix + absPnl + ' (' + ast.winCount + 'W/' + ast.lossCount + 'L)</span>' +
                           '<button type="button" class="ath-btn" onclick="event.stopPropagation(); window.open(\'' + chartUrl + '\', \'_blank\');" style="padding:2px 8px; font-size:10px; margin-left:6px; height:20px; border-radius:4px;" title="เปิดดูกราฟแท่งเทียนในแท็บใหม่">📊 ดูกราฟ ↗</button>';

          roundNode.createChildNode(
            assetLabel,
            false,
            AIMARA_ICONS.asset,
            { type: 'asset', data: ast, round: rnd, server: srv, chartUrl: chartUrl },
            null
          );
        });
      });
    });

    // Render tree to DOM
    currentTree.drawTree();

    // Node click event listener for details
    currentTree.nodeClickEvent = function (node) {
      if (!node.tag) return;
      showNodeDetails(node.tag);
    };
  }

  /**
   * 4. Display metadata and trade details when a tree node is clicked
   */
  function showNodeDetails(tag) {
    detailCard.style.display = 'block';

    if (tag.type === 'server') {
      var s = tag.data;
      detailIcon.textContent = '🖥️';
      detailHeading.textContent = 'Server: ' + s.serverName + ' (Code: ' + s.serverCode + ')';
      detailTypeBadge.className = 'ath-badge ath-badge-success';
      detailTypeBadge.textContent = 'Server Info';

      metaGrid.innerHTML = [
        metaItem('Server Code', s.serverCode),
        metaItem('Server Name', s.serverName),
        metaItem('Public IP', s.publicIP || 'N/A'),
        metaItem('Total Rounds', s.rounds.length)
      ].join('');

      tradesSection.style.display = 'none';

    } else if (tag.type === 'round') {
      var r = tag.data;
      var srv = tag.server;
      detailIcon.textContent = '⏱️';
      detailHeading.textContent = 'Round #' + r.tradeRoundNo + ' (Server ' + srv.serverCode + ' - ' + srv.serverName + ')';
      detailTypeBadge.className = 'ath-badge ath-badge-count';
      detailTypeBadge.textContent = 'Trade Round';

      metaGrid.innerHTML = [
        metaItem('Strategy Code', r.usestrategyCode),
        metaItem('Start Time', r.startTimeTrade || '-'),
        metaItem('Stop Time', r.stopTimeTrade || '-'),
        metaItem('Duration', r.durationTrade || '-'),
        metaItem('Total Assets', r.assets.length)
      ].join('');

      tradesSection.style.display = 'none';

    } else if (tag.type === 'asset') {
      var a = tag.data;
      var r = tag.round;
      var srv = tag.server;

      var chartUrl = tag.chartUrl || ('analysis_trade_chart.php?serverCode=' + srv.serverCode +
                     '&tradeRoundNo=' + r.tradeRoundNo +
                     '&assetCode=' + encodeURIComponent(a.assetCode) +
                     '&date=' + encodeURIComponent(activeDateVal || ''));

      detailIcon.textContent = '📈';
      detailHeading.textContent = 'Asset: ' + a.assetCode + ' (Round #' + r.tradeRoundNo + ' - ' + srv.serverName + ')';
      detailTypeBadge.className = 'ath-badge ath-badge-purple';
      detailTypeBadge.innerHTML = '<a href="' + chartUrl + '" target="_blank" style="color:inherit;text-decoration:none;font-weight:700;">📊 ดูกราฟแท่งเทียน ↗</a>';

      var pnlColor = a.totalProfit >= 0 ? '#34d399' : '#f87171';
      var pnlStr = (a.totalProfit >= 0 ? '+$' : '-$') + Math.abs(a.totalProfit).toFixed(2);

      metaGrid.innerHTML = [
        metaItem('Asset Symbol', a.assetCode),
        metaItem('Max Win Con', a.MaxWinCon),
        metaItem('Max Loss Con', a.MaxLossCon),
        metaItem('Total Trades', a.totalTrades),
        metaItem('Win / Loss', a.winCount + ' Win / ' + a.lossCount + ' Loss (' + a.skippedCount + ' Skip)'),
        '<div class="ath-meta-item"><div class="ath-meta-label">Total PnL</div><div class="ath-meta-value" style="color:' + pnlColor + ';">' + pnlStr + '</div></div>',
        '<div class="ath-meta-item" style="grid-column:1/-1;display:flex;align-items:center;justify-content:center;background:rgba(0,117,255,0.15);border:1px solid rgba(0,117,255,0.4);border-radius:8px;padding:8px;"><a href="' + chartUrl + '" target="_blank" class="ath-btn" style="background:linear-gradient(135deg,#0075ff 0%,#00d4ff 100%);color:#fff;border:none;font-weight:700;padding:8px 20px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">📊 เปิดดูกราฟแท่งเทียน (Lightweight Chart) ในแท็บใหม่ ↗</a></div>'
      ].join('');

      // Fetch trades from vpsTradeData
      loadAssetTrades(srv.serverCode, r.tradeRoundNo, a.assetCode);
    }
  }

  function metaItem(label, val) {
    return '<div class="ath-meta-item"><div class="ath-meta-label">' + label + '</div><div class="ath-meta-value">' + (val !== null && val !== undefined ? val : '-') + '</div></div>';
  }

  /**
   * 5. Load trades for selected asset
   */
  function loadAssetTrades(serverCode, tradeRoundNo, assetCode) {
    tradesSection.style.display = 'block';
    tradesTbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--text-tertiary);">กำลังโหลดประวัติเทรด...</td></tr>';
    tradesCountBadge.textContent = 'loading...';

    var url = '../php/api_analysis_trade_hist.php?action=get_asset_trades&serverCode=' + serverCode +
              '&tradeRoundNo=' + tradeRoundNo +
              '&assetCode=' + encodeURIComponent(assetCode);

    fetch(url)
      .then(function (res) { return res.json(); })
      .then(function (data) {
        var trades = data.trades || [];
        tradesCountBadge.textContent = trades.length + ' trades';

        if (trades.length === 0) {
          tradesTbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--text-tertiary);">ไม่มีบันทึกการเทรดใน vpsTradeData สำหรับคู่นี้</td></tr>';
          return;
        }

        var rowsHtml = '';
        trades.forEach(function (t) {
          var isWin = (t.WinStatus || '').toLowerCase() === 'win';
          var isLoss = (t.WinStatus || '').toLowerCase() === 'loss';
          var badgeClass = isWin ? 'ath-badge-success' : (isLoss ? 'ath-badge-danger' : 'ath-badge-count');
          var profitColor = (parseFloat(t.ThisProfit) || 0) >= 0 ? '#34d399' : '#f87171';

          rowsHtml += '<tr>';
          rowsHtml += '  <td><b>#' + (t.tradeNo || '-') + '.' + (t.subTradeNo || '-') + '</b></td>';
          rowsHtml += '  <td>' + (t.purchaseTimeDisplay || '-') + '</td>';
          rowsHtml += '  <td><b>' + (t.thisAction || '-') + '</b></td>';
          rowsHtml += '  <td><span class="ath-badge ' + badgeClass + '">' + (t.WinStatus || '-') + '</span></td>';
          rowsHtml += '  <td>$' + (parseFloat(t.MoneyTrade) || 0).toFixed(2) + '</td>';
          rowsHtml += '  <td style="color:' + profitColor + ';font-weight:700;">' + (parseFloat(t.ThisProfit) >= 0 ? '+$' : '-$') + Math.abs(parseFloat(t.ThisProfit) || 0).toFixed(2) + '</td>';
          rowsHtml += '  <td>$' + (parseFloat(t.GrandBalance) || 0).toFixed(2) + '</td>';
          rowsHtml += '  <td>' + (t.diffSpot !== null ? parseFloat(t.diffSpot).toFixed(4) : '-') + '</td>';
          rowsHtml += '</tr>';
        });

        tradesTbody.innerHTML = rowsHtml;
      })
      .catch(function (err) {
        tradesTbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:#f87171;">เกิดข้อผิดพลาด: ' + err.message + '</td></tr>';
      });
  }

  // Filter dates input (Date Picker)
  if (dateFilterInput) {
    dateFilterInput.addEventListener('change', function () {
      var pickedVal = this.value; // Format: YYYY-MM-DD
      if (!pickedVal) {
        renderDateList(allDates);
        return;
      }
      var matched = allDates.find(function (d) { return d.date_val === pickedVal; });
      if (matched) {
        renderDateList([matched]);
        selectDate(matched.date_val, matched.date_display);
      } else {
        var pParts = pickedVal.split('-');
        var pickedDisp = pParts.length === 3 ? (pParts[2] + '/' + pParts[1] + '/' + pParts[0]) : pickedVal;
        selectDate(pickedVal, pickedDisp);
      }
    });
  }

  var btnClearDateFilter = document.getElementById('ath-date-filter-clear');
  if (btnClearDateFilter) {
    btnClearDateFilter.addEventListener('click', function () {
      if (dateFilterInput) dateFilterInput.value = '';
      renderDateList(allDates);
      if (allDates.length > 0) {
        selectDate(allDates[0].date_val, allDates[0].date_display);
      }
    });
  }

  // Toolbar Actions
  if (btnExpandAll) {
    btnExpandAll.addEventListener('click', function () {
      if (currentTree) currentTree.expandAll();
    });
  }

  if (btnCollapseAll) {
    btnCollapseAll.addEventListener('click', function () {
      if (currentTree) currentTree.collapseAll();
    });
  }

  if (btnReloadTree) {
    btnReloadTree.addEventListener('click', function () {
      if (activeDateVal) loadTreeData(activeDateVal);
    });
  }

  if (btnRefreshAll) {
    btnRefreshAll.addEventListener('click', function () {
      fetchTradingDates(false);
      if (activeDateVal) loadTreeData(activeDateVal);
    });
  }

  // Initial load
  fetchTradingDates(true);

})();
</script>
