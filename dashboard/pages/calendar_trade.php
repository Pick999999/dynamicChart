<?php
/**
 * Calendar Trade Page
 * Vision UI Dark Glassmorphism Design
 * 
 * Uses pure JavaScript with TOAST UI Calendar (https://ui.toast.com/tui-calendar)
 * Displays trading history aggregated from vpsTradeData and vpsMaster
 * Shows list of VPS active on each trading day with trade count, win/loss, profit
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-calendar-trade">
  <style>
    /* =========================================================
       Scoped Styles for Calendar Trade (Vision UI Dark Glass)
       ========================================================= */
    .ct-wrapper {
      display: flex;
      flex-direction: column;
      gap: 20px;
      animation: ctFadeIn 0.3s ease-in-out;
    }

    @keyframes ctFadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* --- Control Header Card --- */
    .ct-header-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.94));
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      border-radius: var(--radius-md, 16px);
      padding: 20px 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card, 0 8px 32px 0 rgba(0, 0, 0, 0.37));
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .ct-header-title-group {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .ct-header-icon {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
      flex-shrink: 0;
    }

    .ct-header-icon svg {
      width: 24px;
      height: 24px;
      fill: #fff;
    }

    .ct-header-title {
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      margin: 0 0 2px 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .ct-header-sub {
      font-size: 12px;
      color: var(--text-tertiary, rgba(255, 255, 255, 0.5));
      margin: 0;
    }

    /* Navigation & Filters */
    .ct-controls-group {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }

    .ct-btn-nav {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #fff;
      border-radius: 8px;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
    }

    .ct-btn-nav:hover {
      background: rgba(0, 117, 255, 0.2);
      border-color: #0075ff;
      color: #38bdf8;
      transform: translateY(-1px);
    }

    .ct-btn-today {
      background: linear-gradient(135deg, rgba(0, 117, 255, 0.25), rgba(0, 212, 255, 0.25));
      border: 1px solid rgba(0, 212, 255, 0.4);
      color: #38bdf8;
    }

    .ct-month-title {
      font-size: 17px;
      font-weight: 800;
      color: #fff;
      min-width: 170px;
      text-align: center;
      letter-spacing: 0.3px;
      display: inline-block;
    }

    .ct-select {
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #fff;
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 13px;
      font-family: inherit;
      cursor: pointer;
      outline: none;
      transition: all 0.2s;
    }

    .ct-select:focus {
      border-color: #0075ff;
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
    }

    .ct-select option {
      background: #0d1527;
      color: #fff;
    }

    /* --- KPI Summary Cards --- */
    .ct-kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
    }

    .ct-kpi-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.94));
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      border-radius: var(--radius-md, 16px);
      padding: 16px 20px;
      backdrop-filter: blur(20px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: var(--shadow-card, 0 8px 32px 0 rgba(0, 0, 0, 0.37));
      transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .ct-kpi-card:hover {
      transform: translateY(-2px);
      border-color: rgba(255, 255, 255, 0.18);
    }

    .ct-kpi-label {
      font-size: 11px;
      font-weight: 600;
      color: var(--text-tertiary, rgba(255, 255, 255, 0.5));
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .ct-kpi-val {
      font-size: 20px;
      font-weight: 800;
      color: #fff;
    }

    .ct-kpi-sub {
      font-size: 11px;
      color: var(--text-tertiary, rgba(255, 255, 255, 0.5));
      margin-top: 2px;
    }

    .ct-kpi-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .ct-kpi-icon svg {
      width: 20px;
      height: 20px;
      fill: #fff;
    }

    /* --- Legend & VPS Badges Bar --- */
    .ct-legend-card {
      background: rgba(6, 11, 40, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 12px;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 14px;
      font-size: 12px;
    }

    .ct-legend-title {
      font-weight: 700;
      color: #94a3b8;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-right: 4px;
    }

    .ct-vps-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 11.5px;
      cursor: pointer;
      transition: all 0.2s;
      user-select: none;
    }

    .ct-vps-pill:hover {
      transform: scale(1.05);
      filter: brightness(1.2);
    }

    .ct-vps-pill-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }

    /* --- Calendar Container Card --- */
    .ct-calendar-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.94));
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      border-radius: var(--radius-md, 16px);
      padding: 20px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card, 0 8px 32px 0 rgba(0, 0, 0, 0.37));
      min-height: 650px;
      position: relative;
    }

    /* TOAST UI Calendar Overrides for Dark Glassmorphism */
    #ct-tui-calendar {
      height: 720px;
      width: 100%;
      font-family: inherit;
    }

    .toastui-calendar-root {
      background-color: transparent !important;
      color: #e2e8f0 !important;
      font-family: inherit !important;
      border: none !important;
    }

    .toastui-calendar-layout {
      background-color: transparent !important;
    }

    .toastui-calendar-month {
      background-color: transparent !important;
    }

    .toastui-calendar-month-day-grid {
      background-color: transparent !important;
    }

    .toastui-calendar-day-name__item {
      color: #94a3b8 !important;
      font-weight: 700 !important;
      font-size: 13px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
      padding: 10px 0 !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .toastui-calendar-daygrid-cell {
      background-color: rgba(10, 19, 48, 0.35) !important;
      border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
      transition: background-color 0.15s ease;
      cursor: pointer;
    }

    .toastui-calendar-daygrid-cell:hover {
      background-color: rgba(0, 117, 255, 0.1) !important;
    }

    .toastui-calendar-daygrid-cell.toastui-calendar-near-month-day {
      background-color: rgba(6, 11, 40, 0.6) !important;
      opacity: 0.35;
    }

    .toastui-calendar-grid-cell-date {
      color: #cbd5e1 !important;
      font-size: 13px !important;
      font-weight: 600 !important;
      padding: 4px 8px !important;
    }

    .toastui-calendar-today .toastui-calendar-grid-cell-date {
      color: #00d4ff !important;
      font-weight: 800 !important;
    }

    /* TOAST UI Event Custom Styling */
    .toastui-calendar-weekday-event {
      border-radius: 6px !important;
      margin: 2px 3px !important;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4) !important;
      cursor: pointer !important;
      transition: transform 0.15s ease, filter 0.15s ease !important;
      overflow: visible !important;
      height: 24px !important;
      line-height: 24px !important;
    }

    .toastui-calendar-weekday-event-title {
      overflow: visible !important;
      display: flex !important;
      align-items: center !important;
      width: 100% !important;
      height: 100% !important;
      padding: 0 !important;
    }

    .toastui-calendar-weekday-event:hover {
      transform: scale(1.02) !important;
      filter: brightness(1.25) !important;
      z-index: 20 !important;
    }

    .ct-event-badge {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      gap: 4px !important;
      padding: 1px 4px !important;
      font-size: 11px !important;
      white-space: nowrap !important;
      overflow: visible !important;
      color: #fff !important;
      font-weight: 600 !important;
      width: 100% !important;
      height: 100% !important;
      box-sizing: border-box !important;
    }

    .ct-event-left {
      display: flex !important;
      align-items: center !important;
      gap: 3px !important;
      flex-shrink: 0 !important;
    }

    .ct-event-vps-code {
      background: rgba(0, 0, 0, 0.5);
      padding: 1px 4px;
      border-radius: 3px;
      font-size: 10px;
      font-weight: 800;
      color: #fff;
      flex-shrink: 0;
    }

    .ct-event-profit {
      font-weight: 800;
      font-size: 10px;
      padding: 1px 4px;
      border-radius: 3px;
      background: rgba(0, 0, 0, 0.4);
      flex-shrink: 0;
    }
    .ct-profit-pos { color: #34d399; }
    .ct-profit-neg { color: #f87171; }
    .ct-profit-zero { color: #94a3b8; }

    /* Asset Code Buttons on Calendar Events */
    .ct-event-assets {
      display: inline-flex !important;
      align-items: center !important;
      gap: 2px !important;
      flex-shrink: 0 !important;
    }

    .ct-asset-btn {
      background: rgba(0, 0, 0, 0.65) !important;
      border: 1px solid rgba(0, 212, 255, 0.5) !important;
      color: #38bdf8 !important;
      font-family: inherit !important;
      font-size: 9.5px !important;
      font-weight: 800 !important;
      padding: 1px 4px !important;
      border-radius: 3px !important;
      cursor: pointer !important;
      line-height: 1.2 !important;
      transition: all 0.15s ease !important;
      white-space: nowrap !important;
      text-decoration: none !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 2px !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5) !important;
    }

    .ct-asset-btn:hover {
      background: #00d4ff !important;
      color: #060b26 !important;
      border-color: #00d4ff !important;
      transform: scale(1.12) !important;
      box-shadow: 0 0 10px rgba(0, 212, 255, 0.9) !important;
    }

    /* Modal VPS Card Asset Buttons */
    .ct-modal-vps-assets {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
      margin-top: 8px;
      padding-top: 8px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .ct-modal-asset-btn {
      background: rgba(0, 117, 255, 0.2);
      border: 1px solid rgba(0, 212, 255, 0.4);
      color: #38bdf8;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      box-shadow: 0 2px 6px rgba(0, 117, 255, 0.2);
    }

    .ct-modal-asset-btn:hover {
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      color: #ffffff;
      border-color: #00d4ff;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 212, 255, 0.4);
    }

    .ct-table-asset-btn {
      background: rgba(0, 117, 255, 0.15);
      border: 1px solid rgba(0, 212, 255, 0.3);
      color: #38bdf8;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .ct-table-asset-btn:hover {
      background: #0075ff;
      color: #fff;
      border-color: #00d4ff;
      box-shadow: 0 2px 8px rgba(0, 212, 255, 0.4);
    }

    /* Day Header with trade count badge */
    .ct-day-header-badge {
      display: inline-flex;
      align-items: center;
      gap: 3px;
      font-size: 10px;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: 10px;
      background: rgba(0, 117, 255, 0.25);
      border: 1px solid rgba(0, 212, 255, 0.3);
      color: #38bdf8;
      margin-left: 4px;
    }

    /* --- Day Details Drawer / Modal --- */
    .ct-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(3, 7, 26, 0.85);
      backdrop-filter: blur(8px);
      z-index: 9999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      animation: ctFadeIn 0.2s ease;
    }

    .ct-modal-card {
      background: #080f28;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      width: 95%;
      max-width: 1080px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7), 0 0 30px rgba(0, 117, 255, 0.2);
      display: flex;
      flex-direction: column;
    }

    .ct-modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(13, 24, 60, 0.6);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .ct-modal-title-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .ct-modal-title {
      font-size: 18px;
      font-weight: 800;
      color: #fff;
      margin: 0;
    }

    .ct-modal-date-badge {
      background: linear-gradient(135deg, #0075ff, #00d4ff);
      color: #fff;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 700;
    }

    .ct-modal-close-btn {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #94a3b8;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 18px;
      transition: all 0.2s;
    }

    .ct-modal-close-btn:hover {
      background: rgba(244, 63, 94, 0.2);
      border-color: #f43f5e;
      color: #fff;
    }

    .ct-modal-body {
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Modal Day KPI Cards */
    .ct-modal-kpi-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
      gap: 12px;
    }

    .ct-modal-kpi {
      background: rgba(15, 28, 70, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 12px 16px;
    }

    .ct-modal-kpi .label {
      font-size: 11px;
      color: #94a3b8;
      font-weight: 600;
    }

    .ct-modal-kpi .val {
      font-size: 18px;
      font-weight: 800;
      color: #fff;
      margin-top: 4px;
    }

    /* Modal VPS Cards Grid */
    .ct-modal-vps-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 14px;
    }

    .ct-vps-card {
      background: rgba(10, 20, 50, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 14px 16px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .ct-vps-card:hover, .ct-vps-card.active {
      border-color: #00d4ff;
      background: rgba(15, 32, 80, 0.95);
      box-shadow: 0 4px 16px rgba(0, 212, 255, 0.25);
    }

    .ct-vps-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 8px;
    }

    .ct-vps-card-title {
      font-size: 13.5px;
      font-weight: 700;
      color: #fff;
    }

    .ct-vps-card-stats {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      color: #94a3b8;
    }

    /* Modal Trades Table */
    .ct-table-card {
      background: rgba(10, 19, 48, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      overflow: hidden;
    }

    .ct-table-header-bar {
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(255, 255, 255, 0.03);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      flex-wrap: wrap;
      gap: 10px;
    }

    .ct-table-title {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .ct-table-wrap {
      max-height: 380px;
      overflow-y: auto;
    }

    .ct-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      text-align: left;
    }

    .ct-table th {
      background: rgba(15, 28, 70, 0.8);
      color: #94a3b8;
      font-weight: 700;
      padding: 10px 14px;
      position: sticky;
      top: 0;
      z-index: 2;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      white-space: nowrap;
    }

    .ct-table td {
      padding: 8px 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: #e2e8f0;
      white-space: nowrap;
    }

    .ct-table tr:hover td {
      background: rgba(0, 117, 255, 0.08);
    }

    /* Badges in table */
    .badge-win {
      background: rgba(16, 185, 129, 0.2);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.4);
      padding: 2px 8px;
      border-radius: 6px;
      font-weight: 700;
    }

    .badge-loss {
      background: rgba(239, 68, 68, 0.2);
      color: #f87171;
      border: 1px solid rgba(239, 68, 68, 0.4);
      padding: 2px 8px;
      border-radius: 6px;
      font-weight: 700;
    }

    .badge-skipped {
      background: rgba(148, 163, 184, 0.15);
      color: #94a3b8;
      border: 1px solid rgba(148, 163, 184, 0.3);
      padding: 2px 8px;
      border-radius: 6px;
      font-weight: 600;
    }

    .badge-call {
      background: rgba(16, 185, 129, 0.15);
      color: #10b981;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 4px;
    }

    .badge-put {
      background: rgba(239, 68, 68, 0.15);
      color: #ef4444;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 4px;
    }

    /* Empty state */
    .ct-empty-state {
      text-align: center;
      padding: 50px 20px;
      color: #94a3b8;
    }
  </style>

  <div class="ct-wrapper">

    <!-- 1. Top Control & Navigation Bar -->
    <div class="ct-header-card">
      <div class="ct-header-title-group">
        <div class="ct-header-icon">
          <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5v-5z"/></svg>
        </div>
        <div>
          <h2 class="ct-header-title">Calendar Trade (ประวัติการเทรด)</h2>
          <p class="ct-header-sub">แสดงสถิติประวัติการเทรดรายวัน และรายการเครื่อง VPS (vpsTradeData + vpsMaster)</p>
        </div>
      </div>

      <!-- Navigation & Filters -->
      <div class="ct-controls-group">
        <!-- Month Navigator Buttons -->
        <button class="ct-btn-nav" id="ctBtnPrev" title="เดือนก่อนหน้า (Previous Month)">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
        </button>
        <span class="ct-month-title" id="ctMonthTitle">September 2026</span>
        <button class="ct-btn-nav" id="ctBtnNext" title="เดือนถัดไป (Next Month)">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
        </button>

        <button class="ct-btn-nav ct-btn-today" id="ctBtnToday" title="กลับมาเดือนปัจจุบัน">
          📅 Today
        </button>

        <!-- Available Month Quick Select -->
        <select class="ct-select" id="ctSelectMonth" title="เลือกเดือนที่มีข้อมูลในระบบ">
          <option value="">⏳ โหลดเดือน...</option>
        </select>

        <!-- VPS Filter Dropdown -->
        <select class="ct-select" id="ctSelectVps" title="กรองเฉพาะ VPS ที่ต้องการ">
          <option value="">🖥️ VPS ทั้งหมด (All)</option>
        </select>

        <!-- Refresh Button -->
        <button class="ct-btn-nav" id="ctBtnRefresh" title="รีเฟรชข้อมูล">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
        </button>
      </div>
    </div>

    <!-- 2. Monthly KPIs Grid -->
    <div class="ct-kpi-grid">
      <!-- Total Trades -->
      <div class="ct-kpi-card">
        <div>
          <div class="ct-kpi-label">Total Trades (เดือนนี้)</div>
          <div class="ct-kpi-val" id="ctKpiTrades">0</div>
          <div class="ct-kpi-sub" id="ctKpiTradesSub">0 ไม้ทั้งหมด</div>
        </div>
        <div class="ct-kpi-icon" style="background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);">
          <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
        </div>
      </div>

      <!-- Net Profit / Loss -->
      <div class="ct-kpi-card">
        <div>
          <div class="ct-kpi-label">Net Profit / Loss</div>
          <div class="ct-kpi-val" id="ctKpiProfit">$0.00</div>
          <div class="ct-kpi-sub" id="ctKpiProfitSub">ผลตอบแทนสุทธิ</div>
        </div>
        <div class="ct-kpi-icon" id="ctKpiProfitIcon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
          <svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
        </div>
      </div>

      <!-- Win Rate -->
      <div class="ct-kpi-card">
        <div>
          <div class="ct-kpi-label">Win Rate %</div>
          <div class="ct-kpi-val" id="ctKpiWinRate">0.0%</div>
          <div class="ct-kpi-sub" id="ctKpiWinRateSub">Win: 0 | Loss: 0</div>
        </div>
        <div class="ct-kpi-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);">
          <svg viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0011 15.9V19H7v2h10v-2h-4v-3.1c1.8-.45 3.19-1.98 3.61-3.96C19.08 11.63 21 9.55 21 8V7c0-1.1-.9-2-2-2zm-14 3V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
        </div>
      </div>

      <!-- Active Trading Days -->
      <div class="ct-kpi-card">
        <div>
          <div class="ct-kpi-label">Trading Days</div>
          <div class="ct-kpi-val" id="ctKpiDays">0 วัน</div>
          <div class="ct-kpi-sub" id="ctKpiDaysSub">วันที่มีประวัติการเทรด</div>
        </div>
        <div class="ct-kpi-icon" style="background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%);">
          <svg viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>
        </div>
      </div>

      <!-- Active VPS Servers -->
      <div class="ct-kpi-card">
        <div>
          <div class="ct-kpi-label">Active VPS</div>
          <div class="ct-kpi-val" id="ctKpiVps">0 เครื่อง</div>
          <div class="ct-kpi-sub" id="ctKpiVpsSub">VPS ที่ทำงานในเดือนนี้</div>
        </div>
        <div class="ct-kpi-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%);">
          <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z"/></svg>
        </div>
      </div>
    </div>

    <!-- 3. VPS Legend Bar -->
    <div class="ct-legend-card" id="ctLegendBar">
      <span class="ct-legend-title">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        VPS Colors:
      </span>
      <div id="ctLegendItems" style="display:flex; flex-wrap:wrap; gap:8px;">
        <!-- Populated dynamically -->
      </div>
    </div>

    <!-- 4. TOAST UI Calendar View -->
    <div class="ct-calendar-card">
      <div id="ct-tui-calendar"></div>
    </div>

  </div>

  <!-- =========================================================
       5. Day Details Modal
       ========================================================= -->
  <div class="ct-modal-overlay" id="ctModalOverlay">
    <div class="ct-modal-card">
      <!-- Modal Header -->
      <div class="ct-modal-header">
        <div class="ct-modal-title-wrap">
          <h3 class="ct-modal-title">รายละเอียดการเทรดประจำวัน</h3>
          <span class="ct-modal-date-badge" id="ctModalDateBadge">2026-09-15</span>
        </div>
        <button class="ct-modal-close-btn" id="ctModalCloseBtn" title="ปิดหน้าต่าง">✕</button>
      </div>

      <!-- Modal Body -->
      <div class="ct-modal-body">
        <!-- Daily KPIs -->
        <div class="ct-modal-kpi-row">
          <div class="ct-modal-kpi">
            <div class="label">จำนวนไม้ทั้งหมด</div>
            <div class="val" id="ctModalDayTrades">0</div>
          </div>
          <div class="ct-modal-kpi">
            <div class="label">ชนะ (Win) / แพ้ (Loss)</div>
            <div class="val" id="ctModalDayWinLoss" style="color:#34d399;">0 / 0</div>
          </div>
          <div class="ct-modal-kpi">
            <div class="label">ข้าม (Skipped)</div>
            <div class="val" id="ctModalDaySkip" style="color:#94a3b8;">0</div>
          </div>
          <div class="ct-modal-kpi">
            <div class="label">ผลตอบแทนสุทธิ (Net Profit)</div>
            <div class="val" id="ctModalDayProfit">$0.00</div>
          </div>
        </div>

        <!-- VPS Active On This Day -->
        <div>
          <div style="font-size:13px; font-weight:700; color:#cbd5e1; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
            <span>🖥️ VPS ที่มีรายการเทรดในวันนี้:</span>
            <span style="font-size:11px; color:#94a3b8;">(คลิกเพื่อกรองเฉพาะ VPS)</span>
          </div>
          <div class="ct-modal-vps-grid" id="ctModalVpsGrid">
            <!-- Populated dynamically -->
          </div>
        </div>

        <!-- Detailed Trades Table -->
        <div class="ct-table-card">
          <div class="ct-table-header-bar">
            <div class="ct-table-title">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="#00d4ff"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
              <span>รายการเทรดรายไม้ (<span id="ctTableTradeCount">0</span> รายการ)</span>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
              <!-- Status Filter inside modal -->
              <select class="ct-select" id="ctModalStatusFilter" style="padding:4px 10px; font-size:12px;">
                <option value="ALL">สถานะทั้งหมด</option>
                <option value="WIN">เฉพาะ Win</option>
                <option value="LOSS">เฉพาะ Loss</option>
                <option value="SKIPPED">เฉพาะ Skipped</option>
              </select>
            </div>
          </div>

          <div class="ct-table-wrap">
            <table class="ct-table" id="ctTradesTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>เวลา (Time)</th>
                  <th>VPS Server</th>
                  <th>สินทรัพย์ (Symbol)</th>
                  <th>คำสั่ง (Action)</th>
                  <th>กลยุทธ์ (Strategy)</th>
                  <th>Case Code</th>
                  <th>ทุน (Money)</th>
                  <th>Entry / Exit Spot</th>
                  <th>สถานะ (WinStatus)</th>
                  <th>กำไร (Profit)</th>
                </tr>
              </thead>
              <tbody id="ctTradesTableBody">
                <tr><td colspan="11" class="ct-empty-state">กำลังโหลดข้อมูล...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
/**
 * Pure JavaScript TOAST UI Calendar Integration for Calendar Trade
 */
(function () {
  'use strict';

  // API Endpoints
  const API_CALENDAR_URL = '../php/api_calendar_trade.php';

  // State
  let currentYear = 2026;
  let currentMonth = 9; // 1-12
  let selectedServerCode = '';
  let calendarInstance = null;
  let cachedMonthData = null;
  let currentDayTrades = [];
  let currentSelectedVpsInModal = '';

  // DOM Elements
  const btnPrev = document.getElementById('ctBtnPrev');
  const btnNext = document.getElementById('ctBtnNext');
  const btnToday = document.getElementById('ctBtnToday');
  const btnRefresh = document.getElementById('ctBtnRefresh');
  const monthTitle = document.getElementById('ctMonthTitle');
  const selectMonth = document.getElementById('ctSelectMonth');
  const selectVps = document.getElementById('ctSelectVps');

  // KPI elements
  const kpiTrades = document.getElementById('ctKpiTrades');
  const kpiTradesSub = document.getElementById('ctKpiTradesSub');
  const kpiProfit = document.getElementById('ctKpiProfit');
  const kpiProfitSub = document.getElementById('ctKpiProfitSub');
  const kpiProfitIcon = document.getElementById('ctKpiProfitIcon');
  const kpiWinRate = document.getElementById('ctKpiWinRate');
  const kpiWinRateSub = document.getElementById('ctKpiWinRateSub');
  const kpiDays = document.getElementById('ctKpiDays');
  const kpiVps = document.getElementById('ctKpiVps');
  const legendItems = document.getElementById('ctLegendItems');

  // Modal elements
  const modalOverlay = document.getElementById('ctModalOverlay');
  const modalCloseBtn = document.getElementById('ctModalCloseBtn');
  const modalDateBadge = document.getElementById('ctModalDateBadge');
  const modalDayTrades = document.getElementById('ctModalDayTrades');
  const modalDayWinLoss = document.getElementById('ctModalDayWinLoss');
  const modalDaySkip = document.getElementById('ctModalDaySkip');
  const modalDayProfit = document.getElementById('ctModalDayProfit');
  const modalVpsGrid = document.getElementById('ctModalVpsGrid');
  const modalStatusFilter = document.getElementById('ctModalStatusFilter');
  const tableTradeCount = document.getElementById('ctTableTradeCount');
  const tableBody = document.getElementById('ctTradesTableBody');

  // Predefined vibrant palette for different VPS servers
  const VPS_COLOR_PALETTES = [
    { bg: 'rgba(0, 117, 255, 0.85)', border: '#00d4ff', text: '#ffffff', dot: '#00d4ff' }, // Blue
    { bg: 'rgba(16, 185, 129, 0.85)', border: '#34d399', text: '#ffffff', dot: '#10b981' }, // Emerald
    { bg: 'rgba(245, 158, 11, 0.85)', border: '#fbbf24', text: '#ffffff', dot: '#f59e0b' }, // Amber
    { bg: 'rgba(139, 92, 246, 0.85)', border: '#a78bfa', text: '#ffffff', dot: '#8b5cf6' }, // Purple
    { bg: 'rgba(236, 72, 153, 0.85)', border: '#f472b6', text: '#ffffff', dot: '#ec4899' }, // Pink
    { bg: 'rgba(6, 182, 212, 0.85)', border: '#22d3ee', text: '#ffffff', dot: '#06b6d4' }   // Cyan
  ];

  function getVpsColor(serverCode) {
    const idx = Math.abs(parseInt(serverCode, 10) || 0) % VPS_COLOR_PALETTES.length;
    return VPS_COLOR_PALETTES[idx];
  }

  const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  const MONTH_NAMES_THAI = [
    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
  ];

  /**
   * Initialize TOAST UI Calendar instance
   */
  function initTuiCalendar() {
    const container = document.getElementById('ct-tui-calendar');
    if (!container) return;

    // Verify Toast UI Calendar library is loaded
    const CalendarClass = window.toastui ? window.toastui.Calendar : (window.tui ? window.tui.Calendar : null);
    if (!CalendarClass) {
      container.innerHTML = `
        <div style="padding:60px 20px; text-align:center; color:#f87171;">
          <h3>⚠️ ไม่พบไลบรารี TOAST UI Calendar</h3>
          <p>กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตเพื่อโหลด CDN จาก toastui-calendar</p>
        </div>`;
      return;
    }

    // Clean up previous instance if any
    if (calendarInstance) {
      try { calendarInstance.destroy(); } catch (e) {}
      calendarInstance = null;
    }

    calendarInstance = new CalendarClass(container, {
      defaultView: 'month',
      useFormPopup: false,
      useDetailPopup: false,
      isReadOnly: true,
      usageStatistics: false,
      month: {
        dayNames: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
        startDayOfWeek: 0,
        visibleWeeksCount: 0,
        isAlways6Weeks: false,
        visibleEventCount: 5,
        workweek: false
      },
      theme: {
        common: {
          backgroundColor: 'transparent',
          border: '1px solid rgba(255, 255, 255, 0.08)',
          gridSelection: {
            backgroundColor: 'rgba(0, 117, 255, 0.1)',
            border: '1px solid #0075ff'
          },
          dayName: { color: '#94a3b8' },
          today: { color: '#00d4ff' },
          holiday: { color: '#f43f5e' }
        },
        month: {
          dayExceptThisMonth: { color: 'rgba(255, 255, 255, 0.2)' },
          dayName: {
            borderLeft: 'none',
            backgroundColor: 'transparent'
          },
          holidayExceptThisMonth: { color: 'rgba(244, 63, 94, 0.25)' },
          moreView: {
            backgroundColor: '#0a1330',
            border: '1px solid rgba(255, 255, 255, 0.2)',
            boxShadow: '0 12px 36px rgba(0,0,0,0.7)',
            width: 320
          },
          moreViewTitle: {
            backgroundColor: '#0f1c46'
          },
          gridCell: {
            header: { height: 28 },
            footer: { height: null }
          }
        }
      },
      template: {
        // Month view event template: Show VPS code, name, trades count and profit
        monthDayEvent(event) {
          return renderEventTemplate(event);
        },
        allday(event) {
          return renderEventTemplate(event);
        },
        time(event) {
          return renderEventTemplate(event);
        }
      }
    });

    // Handle click on event
    calendarInstance.on('clickEvent', function (res) {
      if (res.event && res.event.raw && res.event.raw.date) {
        openDayDetailsModal(res.event.raw.date, res.event.raw.vps ? res.event.raw.vps.serverCode : '');
      }
    });

    // Handle click on date cell
    calendarInstance.on('selectDateTime', function (res) {
      if (res && res.start) {
        const d = new Date(res.start);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        openDayDetailsModal(`${y}-${m}-${day}`);
      }
    });

    // Fallback click listener on day grid cells
    container.addEventListener('click', function (e) {
      const cell = e.target.closest('.toastui-calendar-daygrid-cell');
      if (cell) {
        // Look for any event inside or check date
        const ev = cell.querySelector('[data-trade-date]');
        if (ev && ev.dataset.tradeDate) {
          openDayDetailsModal(ev.dataset.tradeDate);
        }
      }
    });
  }

  // Global handler for clicking on an asset button to open Resistance Lab 1
  window.openResistanceLab = function (e, vps, assetCode, dateStr) {
    if (e) {
      e.stopPropagation();
      e.preventDefault();
    }
    const targetUrl = `../resistanceLab/lab1.html?vps=${encodeURIComponent(vps)}&assetcode=${encodeURIComponent(assetCode)}&daytrade=${encodeURIComponent(dateStr)}`;
    window.open(targetUrl, '_blank');
  };

  /**
   * HTML template for rendering an event on a calendar day
   */
  function renderEventTemplate(event) {
    const raw = event.raw || {};
    const vps = raw.vps || {};
    const tradeCount = vps.totalTrades || 0;
    const profit = typeof vps.totalProfit === 'number' ? vps.totalProfit : 0;
    const profitClass = profit > 0 ? 'ct-profit-pos' : (profit < 0 ? 'ct-profit-neg' : 'ct-profit-zero');
    const profitText = (profit > 0 ? '+$' : (profit < 0 ? '-$' : '$')) + Math.abs(profit).toFixed(2);
    const dateStr = raw.date || '';
    const vpsCode = vps.vpsCode || vps.serverCode || '';

    // Extract asset codes for this VPS on this day
    const assetCodes = Array.isArray(vps.assetCodes) && vps.assetCodes.length > 0 
      ? vps.assetCodes 
      : (vps.symbols ? vps.symbols.split(',').map(s => s.trim()).filter(Boolean) : []);

    const assetBtnsHtml = assetCodes.map(sym => `
      <button type="button" class="ct-asset-btn" 
              onclick="openResistanceLab(event, '${vpsCode}', '${sym}', '${dateStr}')" 
              title="เปิด ${sym} บน VPS #${vpsCode} (${dateStr}) ใน Resistance Lab 1">
        ${sym}
      </button>
    `).join('');

    return `
      <div class="ct-event-badge" data-trade-date="${dateStr}" data-server-code="${vps.serverCode || ''}" title="คลิกเพื่อดูสรุปประจำวัน (${dateStr})">
        <div class="ct-event-left">
          <span class="ct-event-vps-code">#${vpsCode}</span>
          <span class="ct-event-vps-name">${vps.vpsName || 'VPS'}</span>
          <span class="ct-event-profit ${profitClass}">${profitText}</span>
        </div>
        ${assetBtnsHtml ? `<div class="ct-event-assets">${assetBtnsHtml}</div>` : ''}
      </div>
    `;
  }

  /**
   * Load VPS list from api_calendar_trade.php (action=vps_list)
   */
  async function loadVpsList() {
    try {
      const res = await fetch(`${API_CALENDAR_URL}?action=vps_list`);
      const json = await res.json();
      if (json.success && Array.isArray(json.data)) {
        // Build Select Dropdown options
        let optionsHtml = '<option value="">🖥️ VPS ทั้งหมด (All)</option>';
        json.data.forEach(v => {
          optionsHtml += `<option value="${v.vpsCode}">[${v.vpsCode}] ${v.vpsName} ${v.publicIP ? '('+v.publicIP+')' : ''}</option>`;
        });
        selectVps.innerHTML = optionsHtml;

        // Render Legend Bar items
        let legendHtml = '';
        json.data.forEach(v => {
          const color = getVpsColor(v.vpsCode);
          legendHtml += `
            <div class="ct-vps-pill" style="background:${color.bg}; border:1px solid ${color.border}; color:${color.text};" data-vps-code="${v.vpsCode}">
              <span class="ct-vps-pill-dot" style="background:${color.dot};"></span>
              <span>[${v.vpsCode}] ${v.vpsName}</span>
            </div>
          `;
        });
        legendItems.innerHTML = legendHtml;

        // Click on legend pill to filter
        legendItems.querySelectorAll('.ct-vps-pill').forEach(pill => {
          pill.addEventListener('click', function () {
            const vCode = this.dataset.vpsCode;
            selectVps.value = selectVps.value === vCode ? '' : vCode;
            selectedServerCode = selectVps.value;
            loadMonthData();
          });
        });
      }
    } catch (err) {
      console.warn('Error loading VPS list:', err);
    }
  }

  /**
   * Load Month Data from API and update Calendar
   */
  async function loadMonthData() {
    updateTitleDisplay();

    try {
      const url = `${API_CALENDAR_URL}?action=month_summary&year=${currentYear}&month=${currentMonth}${selectedServerCode ? '&serverCode='+encodeURIComponent(selectedServerCode) : ''}`;
      const res = await fetch(url);
      const json = await res.json();

      if (!json.success) {
        throw new Error(json.error || 'Failed to fetch data');
      }

      cachedMonthData = json;

      // Update KPI Cards
      const kpis = json.kpis || {};
      kpiTrades.textContent = (kpis.totalTrades || 0).toLocaleString();
      kpiTradesSub.textContent = `Win: ${kpis.winCount || 0} | Loss: ${kpis.lossCount || 0} | Skip: ${kpis.skipCount || 0}`;

      const netProfit = kpis.netProfit || 0;
      kpiProfit.textContent = (netProfit >= 0 ? '+$' : '-$') + Math.abs(netProfit).toFixed(2);
      kpiProfit.style.color = netProfit > 0 ? '#34d399' : (netProfit < 0 ? '#f87171' : '#fff');
      if (kpiProfitIcon) {
        kpiProfitIcon.style.background = netProfit >= 0 
          ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
          : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
      }

      kpiWinRate.textContent = `${kpis.winRate || 0}%`;
      kpiWinRateSub.textContent = `จาก ${kpis.winCount + kpis.lossCount} ไม้ตัดสิน`;

      kpiDays.textContent = `${kpis.activeDays || 0} วัน`;
      kpiVps.textContent = `${kpis.activeVpsCount || 0} เครื่อง`;

      // Update available months dropdown if present
      if (Array.isArray(json.availableMonths) && json.availableMonths.length > 0) {
        const currentYM = `${currentYear}-${String(currentMonth).padStart(2, '0')}`;
        selectMonth.innerHTML = json.availableMonths.map(m => {
          const parts = m.ym.split('-');
          const y = parseInt(parts[0], 10);
          const mn = parseInt(parts[1], 10);
          const label = `${MONTH_NAMES[mn - 1]} ${y} (${m.cnt} trades)`;
          const isSelected = m.ym === currentYM ? 'selected' : '';
          return `<option value="${m.ym}" ${isSelected}>${label}</option>`;
        }).join('');
      }

      // Move calendar view to selected month/year
      if (calendarInstance) {
        const targetDate = new Date(currentYear, currentMonth - 1, 1);
        calendarInstance.setDate(targetDate);
        calendarInstance.clear();

        // Convert days data into TOAST UI Calendar events
        const events = [];
        const days = json.days || [];

        days.forEach(dayItem => {
          const dayStr = dayItem.date;
          const vpsList = dayItem.vpsList || [];

          vpsList.forEach((vps, idx) => {
            const palette = getVpsColor(vps.vpsCode || vps.serverCode);
            const profit = typeof vps.totalProfit === 'number' ? vps.totalProfit : 0;
            const profitFormatted = (profit >= 0 ? '+' : '') + profit.toFixed(2);

            events.push({
              id: `trade_${dayStr}_vps_${vps.serverCode}_${idx}`,
              calendarId: String(vps.serverCode),
              title: `[${vps.vpsCode}] ${vps.vpsName}: ${vps.totalTrades}T (${profitFormatted}$)`,
              start: `${dayStr}T00:00:00`,
              end: `${dayStr}T23:59:59`,
              isAllday: true,
              category: 'allday',
              backgroundColor: palette.bg,
              borderColor: palette.border,
              color: '#ffffff',
              raw: {
                date: dayStr,
                vps: vps
              }
            });
          });
        });

        calendarInstance.createEvents(events);
      }

    } catch (err) {
      console.error('Error loading calendar month data:', err);
    }
  }

  function updateTitleDisplay() {
    const enName = MONTH_NAMES[currentMonth - 1];
    const thName = MONTH_NAMES_THAI[currentMonth - 1];
    const thYear = currentYear + 543;
    monthTitle.innerHTML = `${enName} ${currentYear} <span style="font-size:12px; font-weight:500; opacity:0.6; display:block;">(${thName} ${thYear})</span>`;
  }

  /**
   * Open Day Details Modal
   */
  async function openDayDetailsModal(dateStr, defaultVpsFilter = '') {
    modalDateBadge.textContent = dateStr;
    modalOverlay.style.display = 'flex';
    currentSelectedVpsInModal = defaultVpsFilter;

    // Reset table state
    tableBody.innerHTML = '<tr><td colspan="11" class="ct-empty-state"><div class="spinner" style="margin:20px auto;"></div>กำลังดึงประวัติการเทรดของวันที่ ' + dateStr + '...</td></tr>';
    tableTradeCount.textContent = '...';

    try {
      const url = `${API_CALENDAR_URL}?action=day_details&date=${dateStr}`;
      const res = await fetch(url);
      const json = await res.json();

      if (!json.success) {
        throw new Error(json.error || 'Failed to fetch day details');
      }

      currentDayTrades = json.trades || [];
      const summary = json.summary || {};

      // Fill modal KPIs
      modalDayTrades.textContent = `${summary.totalTrades || 0} ไม้`;
      modalDayWinLoss.textContent = `${summary.winCount || 0} Win / ${summary.lossCount || 0} Loss`;
      modalDaySkip.textContent = `${summary.skipCount || 0} ไม้`;

      const dayProfit = summary.totalProfit || 0;
      modalDayProfit.textContent = (dayProfit >= 0 ? '+$' : '-$') + Math.abs(dayProfit).toFixed(2);
      modalDayProfit.style.color = dayProfit > 0 ? '#34d399' : (dayProfit < 0 ? '#f87171' : '#fff');

      // Fill VPS list cards for that day
      const vpsSummary = summary.vpsSummary || [];
      renderModalVpsCards(vpsSummary);

      // Render Trades Table
      filterAndRenderTrades();

    } catch (err) {
      tableBody.innerHTML = `<tr><td colspan="11" class="ct-empty-state" style="color:#f87171;">❌ ${err.message}</td></tr>`;
    }
  }

  function renderModalVpsCards(vpsSummary) {
    if (!vpsSummary || vpsSummary.length === 0) {
      modalVpsGrid.innerHTML = '<div style="color:#94a3b8; font-size:12px;">ไม่มีข้อมูล VPS ในวันนี้</div>';
      return;
    }

    let cardsHtml = `
      <div class="ct-vps-card ${!currentSelectedVpsInModal ? 'active' : ''}" data-vps-code="">
        <div class="ct-vps-card-header">
          <span class="ct-vps-card-title">🖥️ ทั้งหมด (${vpsSummary.length} VPS)</span>
        </div>
        <div class="ct-vps-card-stats">
          <span>คลิกเพื่อดูทุกเครื่อง</span>
        </div>
      </div>
    `;

    vpsSummary.forEach(v => {
      const color = getVpsColor(v.vpsCode);
      const p = v.totalProfit || 0;
      const pClass = p > 0 ? 'color:#34d399;' : (p < 0 ? 'color:#f87171;' : 'color:#94a3b8;');
      const pText = (p >= 0 ? '+$' : '-$') + Math.abs(p).toFixed(2);
      const isActive = String(currentSelectedVpsInModal) === String(v.serverCode) ? 'active' : '';
      const vCode = v.vpsCode || v.serverCode;
      const currentDateStr = modalDateBadge ? modalDateBadge.textContent : '';

      // Extract unique assets for this VPS on this day
      const dayVpsTrades = currentDayTrades.filter(t => String(t.serverCode) === String(v.serverCode));
      const vpsAssets = Array.from(new Set(dayVpsTrades.map(t => t.symbol || t.assetCode).filter(Boolean)));

      const assetBtns = vpsAssets.map(sym => `
        <button type="button" class="ct-modal-asset-btn" 
                onclick="openResistanceLab(event, '${vCode}', '${sym}', '${currentDateStr}')" 
                title="เปิด ${sym} บน VPS #${vCode} (${currentDateStr}) ใน Resistance Lab 1">
          📊 ${sym} ↗
        </button>
      `).join('');

      cardsHtml += `
        <div class="ct-vps-card ${isActive}" data-vps-code="${v.serverCode}" style="border-left: 4px solid ${color.border};">
          <div class="ct-vps-card-header">
            <span class="ct-vps-card-title">[${v.vpsCode}] ${v.vpsName}</span>
            <span style="font-weight:800; font-size:12px; ${pClass}">${pText}</span>
          </div>
          <div class="ct-vps-card-stats">
            <span>ไม้: <b>${v.totalTrades}</b> (W:${v.winCount} L:${v.lossCount} S:${v.skipCount})</span>
            <span>${v.publicIP ? v.publicIP : ''}</span>
          </div>
          ${assetBtns ? `<div class="ct-modal-vps-assets"><span style="font-size:11px; color:#94a3b8; font-weight:600;">Assets:</span> ${assetBtns}</div>` : ''}
        </div>
      `;
    });

    modalVpsGrid.innerHTML = cardsHtml;

    // Attach click listeners to cards
    modalVpsGrid.querySelectorAll('.ct-vps-card').forEach(card => {
      card.addEventListener('click', function (e) {
        // If clicked on an asset button inside the card, don't filter card
        if (e.target && e.target.closest('.ct-modal-asset-btn')) return;
        modalVpsGrid.querySelectorAll('.ct-vps-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        currentSelectedVpsInModal = this.dataset.vpsCode || '';
        filterAndRenderTrades();
      });
    });
  }

  function filterAndRenderTrades() {
    let filtered = currentDayTrades;

    // Filter by VPS
    if (currentSelectedVpsInModal) {
      filtered = filtered.filter(t => String(t.serverCode) === String(currentSelectedVpsInModal));
    }

    // Filter by WinStatus
    const stFilter = modalStatusFilter.value;
    if (stFilter !== 'ALL') {
      filtered = filtered.filter(t => String(t.WinStatus || '').toUpperCase() === stFilter);
    }

    tableTradeCount.textContent = filtered.length;

    if (filtered.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="11" class="ct-empty-state">ไม่พบรายการเทรดที่ตรงกับเงื่อนไข</td></tr>';
      return;
    }

    const currentDateStr = modalDateBadge ? modalDateBadge.textContent : '';
    let rowsHtml = '';
    filtered.forEach((t, i) => {
      const wStatus = (t.WinStatus || '').toUpperCase();
      let statusBadge = `<span class="badge-skipped">${t.WinStatus || 'Idle'}</span>`;
      if (wStatus === 'WIN') statusBadge = '<span class="badge-win">WIN</span>';
      else if (wStatus === 'LOSS') statusBadge = '<span class="badge-loss">LOSS</span>';

      const action = (t.thisAction || '').toUpperCase();
      let actionBadge = `<span style="color:#94a3b8;">${t.thisAction || '-'}</span>`;
      if (action === 'CALL') actionBadge = '<span class="badge-call">CALL ▲</span>';
      else if (action === 'PUT') actionBadge = '<span class="badge-put">PUT ▼</span>';

      const profit = t.ThisProfit || 0;
      const profitFormatted = (profit >= 0 ? '+$' : '-$') + Math.abs(profit).toFixed(2);
      const profitStyle = profit > 0 ? 'color:#34d399; font-weight:700;' : (profit < 0 ? 'color:#f87171; font-weight:700;' : 'color:#94a3b8;');

      const spotText = t.entrySpot ? `${t.entrySpot} / ${t.exitSpot || '-'}` : '-';

      rowsHtml += `
        <tr>
          <td style="color:#94a3b8;">${i + 1}</td>
          <td><b>${t.time}</b></td>
          <td>
            <span style="background:rgba(255,255,255,0.06); padding:2px 6px; border-radius:4px; font-weight:600;">
              [${t.vpsCode}] ${t.vpsName}
            </span>
          </td>
          <td>
            <button type="button" class="ct-table-asset-btn" 
                    onclick="openResistanceLab(event, '${t.vpsCode || t.serverCode}', '${t.symbol}', '${currentDateStr}')" 
                    title="เปิด ${t.symbol} ใน Resistance Lab 1">
              ${t.symbol} ↗
            </button>
          </td>
          <td>${actionBadge}</td>
          <td style="font-size:11px; color:#cbd5e1;">${t.tradeStrategy || '-'}</td>
          <td style="font-family:monospace; font-size:11px; color:#f59e0b;">${t.codeStrategy || '-'}</td>
          <td>${t.MoneyTrade ? '$' + t.MoneyTrade : '-'}</td>
          <td style="font-family:monospace; font-size:11px;">${spotText}</td>
          <td>${statusBadge}</td>
          <td style="${profitStyle}">${profitFormatted}</td>
        </tr>
      `;
    });

    tableBody.innerHTML = rowsHtml;
  }

  // Event Listeners
  btnPrev.addEventListener('click', function () {
    currentMonth--;
    if (currentMonth < 1) {
      currentMonth = 12;
      currentYear--;
    }
    loadMonthData();
  });

  btnNext.addEventListener('click', function () {
    currentMonth++;
    if (currentMonth > 12) {
      currentMonth = 1;
      currentYear++;
    }
    loadMonthData();
  });

  btnToday.addEventListener('click', function () {
    // Current date in DB data is 2026-09
    currentYear = 2026;
    currentMonth = 9;
    loadMonthData();
  });

  btnRefresh.addEventListener('click', function () {
    loadMonthData();
  });

  selectMonth.addEventListener('change', function () {
    if (this.value) {
      const parts = this.value.split('-');
      currentYear = parseInt(parts[0], 10);
      currentMonth = parseInt(parts[1], 10);
      loadMonthData();
    }
  });

  selectVps.addEventListener('change', function () {
    selectedServerCode = this.value;
    loadMonthData();
  });

  modalStatusFilter.addEventListener('change', function () {
    filterAndRenderTrades();
  });

  modalCloseBtn.addEventListener('click', function () {
    modalOverlay.style.display = 'none';
  });

  modalOverlay.addEventListener('click', function (e) {
    if (e.target === modalOverlay) {
      modalOverlay.style.display = 'none';
    }
  });

  // Keyboard escape to close modal
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modalOverlay.style.display === 'flex') {
      modalOverlay.style.display = 'none';
    }
  });

  // Initial Boot
  initTuiCalendar();
  loadVpsList();
  loadMonthData();

})();
</script>
