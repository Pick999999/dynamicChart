<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Vision UI Dashboard - Premium dark admin panel with glassmorphism design">
  <title>Vision UI Dashboard</title>
  <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="css/Aimara.css">
  <link rel="stylesheet" href="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gojs@3.0.19/release/go.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gojs@3.0.19/extensions/Figures.js"></script>
  <script src="js/Aimara.js"></script>
</head>
<body>
  <div class="app-wrapper">
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <a href="#" class="sidebar-brand" onclick="return false;">
          <div class="sidebar-brand-icon">V</div>
          <div class="sidebar-brand-text">
            VISION UI FREE
            <span>Dashboard</span>
          </div>
        </a>
        <button class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="ซ่อนเมนู (Collapse Menu)" aria-label="Collapse Menu">
          <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
        </button>
      </div>

      <nav class="sidebar-nav">
        <ul class="nav-list">
          <li class="nav-item">
            <button class="nav-link active" data-page="dashboard">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
              </span>
              Dashboard
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="db_group">
              <span class="nav-icon" style="background:rgba(99, 102, 241, 0.2); color:#818cf8; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 4.02 2 6.5v11C2 19.98 6.48 22 12 22s10-2.02 10-4.5v-11C22 4.02 17.52 2 12 2zm0 2.2c3.96 0 7.8 1.15 7.8 2.3 0 1.15-3.84 2.3-7.8 2.3s-7.8-1.15-7.8-2.3c0-1.15 3.84-2.3 7.8-2.3zm8 6.47c-1.63 1.09-4.66 1.73-8 1.73s-6.37-.64-8-1.73V8.87c2.14 1.13 5.09 1.73 8 1.73s5.86-.6 8-1.73v1.8zm0 4c-1.63 1.09-4.66 1.73-8 1.73s-6.37-.64-8-1.73v-1.8c2.14 1.13 5.09 1.73 8 1.73s5.86-.6 8-1.73v1.8zm0 4.13c-2.14 1.13-5.09 1.7-8 1.7s-5.86-.57-8-1.7v-1.87c1.63 1.09 4.66 1.73 8 1.73s6.37-.64 8-1.73v1.87z"/></svg>
              </span>
              DB Group
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="all_load_data">
              <span class="nav-icon" style="background:rgba(0, 212, 255, 0.18); color:#00d4ff; box-shadow: 0 2px 8px rgba(0, 212, 255, 0.3);">
                <svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
              </span>
              All Load Data
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="tables">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 18H4V4h16v16zM6 6h12v2H6zm0 4h12v2H6zm0 4h8v2H6z"/></svg>
              </span>
              Tables
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="db_diagram">
              <span class="nav-icon" style="background:rgba(0, 117, 255, 0.2); color:#00d4ff; box-shadow: 0 2px 8px rgba(0, 117, 255, 0.3);">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 4.02 2 6.5v11C2 19.98 6.48 22 12 22s10-2.02 10-4.5v-11C22 4.02 17.52 2 12 2zm0 2.2c3.96 0 7.8 1.15 7.8 2.3 0 1.15-3.84 2.3-7.8 2.3s-7.8-1.15-7.8-2.3c0-1.15 3.84-2.3 7.8-2.3zm8 6.47c-1.63 1.09-4.66 1.73-8 1.73s-6.37-.64-8-1.73V8.87c2.14 1.13 5.09 1.73 8 1.73s5.86-.6 8-1.73v1.8zm0 4c-1.63 1.09-4.66 1.73-8 1.73s-6.37-.64-8-1.73v-1.8c2.14 1.13 5.09 1.73 8 1.73s5.86-.6 8-1.73v1.8zm0 4.13c-2.14 1.13-5.09 1.7-8 1.7s-5.86-.57-8-1.7v-1.87c1.63 1.09 4.66 1.73 8 1.73s6.37-.64 8-1.73v1.87z"/></svg>
              </span>
              DB Diagram
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="billing">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
              </span>
              Billing
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="vps_master">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z"/></svg>
              </span>
              VPS Master
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="deriv_account">
              <span class="nav-icon" style="background:rgba(0, 212, 255, 0.15); color:#00d4ff;">
                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
              </span>
              Deriv Account
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="load_vps_trade_data">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
              </span>
              Load vpsTradeData
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="load_deriv_trade_history">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
              </span>
              Fetch Deriv Trades
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="load_full_analysis_data">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
              </span>
              Load FullAnalysis Data
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="analysis_trade_hist">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M22 11V3h-7v3H9V3H2v8h7V8h2v10h4v3h7v-8h-7v3h-2V8h2v3h7zM4 5h3v4H4V5zm13 14h3v4h-3v-4zm0-14h3v4h-3V5z"/></svg>
              </span>
              Analysis Trade Hist
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="analysis_trade_chart">
              <span class="nav-icon" style="background:rgba(255, 126, 95, 0.2); color:#ff7e5f; box-shadow: 0 2px 8px rgba(255, 126, 95, 0.3);">
                <svg viewBox="0 0 24 24"><path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/><path d="M4 19h16v2H4z"/></svg>
              </span>
              Analysis Trade Chart
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="deriv_full_analysis_v2">
              <span class="nav-icon" style="background:rgba(102, 126, 234, 0.2); color:#818cf8; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.35);">
                <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
              </span>
              Deriv Full Analysis V2
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="calendar_trade">
              <span class="nav-icon" style="background:rgba(245, 158, 11, 0.15); color:#f59e0b;">
                <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5v-5z"/></svg>
              </span>
              Calendar Trade
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="calendar_trade_hist_v2">
              <span class="nav-icon" style="background:rgba(236, 72, 153, 0.15); color:#ec4899;">
                <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
              </span>
              Calendar Trade Hist V2
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="pktrend_action">
              <span class="nav-icon" style="background:rgba(1, 181, 116, 0.15); color:#01b574;">
                <svg viewBox="0 0 24 24"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
              </span>
              pkTrend Action
            </button>
          </li>
          <li class="nav-item">
            <a href="../resistanceLab/lab1.html" target="_blank" rel="noopener noreferrer" class="nav-link" style="display:flex; justify-content:space-between; align-items:center;" title="เปิด Resistance Lab 1 ในแท็บใหม่ (New Tab)">
              <span style="display:flex; align-items:center; gap:12px;">
                <span class="nav-icon" style="background:rgba(244, 63, 94, 0.15); color:#f43f5e;">
                  <svg viewBox="0 0 24 24"><path d="M19 19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2l3-7.5V6H7V4h10v2h-1v5.5l3 7.5zM10 6v5.5l-2.4 6h8.8l-2.4-6V6h-4z"/></svg>
                </span>
                Resistance Lab 1
              </span>
              <span style="display:inline-flex; align-items:center; opacity:0.6; margin-left:auto;">
                <svg viewBox="0 0 24 24" style="width:14px; height:14px; fill:currentColor;"><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
              </span>
            </a>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="rtl">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
              </span>
              RTL
            </button>
          </li>
        </ul>

        <p class="nav-section-title">Account Pages</p>
        <ul class="nav-list">
          <li class="nav-item">
            <button class="nav-link" data-page="profile">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
              </span>
              Profile
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="signin">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/></svg>
              </span>
              Sign In
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-page="signup">
              <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
              </span>
              Sign Up
            </button>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <button class="topbar-btn sidebar-toggle-btn" id="sidebar-toggle" title="แสดง/ซ่อนเมนู (Toggle Sidebar)" aria-label="Toggle Sidebar">
            <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
          </button>
          <div class="topbar-breadcrumb">
            <div class="breadcrumb-path">
              <a href="#">Pages</a>
              <span>/</span>
              <span id="breadcrumb-page">Dashboard</span>
            </div>
            <h1 class="topbar-title" id="topbar-title">Dashboard</h1>
          </div>
        </div>

        <div class="topbar-actions">
          <div class="topbar-search">
            <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <input type="text" placeholder="Type here..." id="search-input">
          </div>

          <!-- Theme Palette Selector -->
          <div class="theme-picker-container" id="theme-picker-container">
            <button class="topbar-btn" id="theme-toggle-btn" title="เปลี่ยนธีมสี (Theme Colors)" aria-label="Choose Theme">
              <svg viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L4.35 19.4c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l1.9-1.9C9.22 19.59 10.57 20 12 20c4.97 0 9-4.03 9-9s-4.03-9-9-9zm0 15c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/><circle cx="12" cy="7" r="1.5"/><circle cx="7.5" cy="11.5" r="1.5"/><circle cx="9.5" cy="16" r="1.5"/><circle cx="14.5" cy="16" r="1.5"/><circle cx="16.5" cy="11.5" r="1.5"/></svg>
              <span class="theme-active-dot"></span>
            </button>
            <div class="theme-dropdown" id="theme-dropdown">
              <div class="theme-dropdown-header">
                <div class="theme-dropdown-title">
                  <svg viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L4.35 19.4c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l1.9-1.9C9.22 19.59 10.57 20 12 20c4.97 0 9-4.03 9-9s-4.03-9-9-9zm0 15c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/><circle cx="12" cy="7" r="1.5"/><circle cx="7.5" cy="11.5" r="1.5"/><circle cx="9.5" cy="16" r="1.5"/><circle cx="14.5" cy="16" r="1.5"/><circle cx="16.5" cy="11.5" r="1.5"/></svg>
                  <span>Color Themes</span>
                </div>
                <span class="theme-current-badge" id="theme-current-badge">Vision Navy</span>
              </div>
              <div class="theme-list" id="theme-list">
                <!-- Populated dynamically by app.js -->
              </div>
            </div>
          </div>

          <button class="topbar-btn" title="Sign In">
            <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          </button>

          <button class="topbar-btn" id="settings-btn" title="Settings / Theme">
            <svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
          </button>

          <button class="topbar-btn" title="Notifications">
            <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
            <span class="badge">3</span>
          </button>
        </div>
      </header>

      <!-- Dynamic Content Area -->
      <div id="content-area">
        <div class="page-loader"><div class="spinner"></div></div>
      </div>
    </main>
  </div>

  <!-- App JavaScript -->
  <script src="js/app.js"></script>
</body>
</html>
