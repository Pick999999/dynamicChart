<?php
/**
 * DB Diagram Page
 * Vision UI Dark Glassmorphism Design
 * Interactive Entity Relationship Diagram powered by GoJS
 * Reading schema & structure from MySQL database: dynamic_chart
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-db-diagram">
  <style>
    /* Scoped Styles for DB Diagram Page */
    #page-db-diagram {
      display: flex;
      flex-direction: column;
      gap: 16px;
      width: 100%;
    }

    .erd-header-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: var(--radius-md, 16px);
      padding: 18px 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card, 0 20px 27px 0 rgba(0, 0, 0, 0.05));
    }

    .erd-header-top {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 16px;
    }

    .erd-title-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .erd-icon-badge {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      box-shadow: 0 4px 15px rgba(0, 117, 255, 0.35);
      flex-shrink: 0;
    }

    .erd-icon-badge svg {
      width: 22px;
      height: 22px;
      fill: currentColor;
    }

    .erd-title-text h2 {
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .erd-title-text p {
      font-size: 13px;
      color: var(--text-tertiary, #a0aec0);
      margin: 2px 0 0;
    }

    .erd-db-pill {
      background: rgba(0, 212, 255, 0.12);
      border: 1px solid rgba(0, 212, 255, 0.3);
      color: #00d4ff;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      font-family: monospace;
    }

    /* Stats Overview */
    .erd-stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 12px;
    }

    .erd-stat-box {
      background: rgba(10, 19, 48, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 10px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .erd-stat-label {
      font-size: 11px;
      color: var(--text-tertiary, #a0aec0);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .erd-stat-val {
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      font-family: monospace;
    }

    /* Toolbar */
    .erd-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: 14px;
      padding: 12px 18px;
      backdrop-filter: blur(20px);
    }

    .erd-toolbar-left, .erd-toolbar-right {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 10px;
    }

    .erd-search-wrap {
      position: relative;
      min-width: 220px;
    }

    .erd-search-wrap svg {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      width: 14px;
      height: 14px;
      fill: var(--text-tertiary, #a0aec0);
      pointer-events: none;
    }

    .erd-search-input {
      width: 100%;
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: 8px;
      padding: 7px 12px 7px 30px;
      color: #fff;
      font-size: 12px;
      outline: none;
      transition: all 0.2s ease;
    }

    .erd-search-input:focus {
      border-color: #0075ff;
      box-shadow: 0 0 0 2px rgba(0, 117, 255, 0.25);
    }

    .erd-select {
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: 8px;
      padding: 7px 12px;
      color: #fff;
      font-size: 12px;
      outline: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .erd-select:focus {
      border-color: #0075ff;
    }

    .erd-select option {
      background: #0b1437;
      color: #fff;
    }

    .erd-btn {
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      padding: 7px 12px;
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .erd-btn:hover {
      background: rgba(255, 255, 255, 0.14);
      border-color: rgba(255, 255, 255, 0.24);
      transform: translateY(-1px);
    }

    .erd-btn svg {
      width: 14px;
      height: 14px;
      fill: currentColor;
    }

    .erd-btn-primary {
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      border: none;
      box-shadow: 0 2px 8px rgba(0, 117, 255, 0.35);
    }

    .erd-btn-primary:hover {
      box-shadow: 0 4px 14px rgba(0, 117, 255, 0.5);
    }

    /* Main Diagram Layout Workspace */
    .erd-workspace {
      display: flex;
      gap: 16px;
      position: relative;
      min-height: 680px;
      height: calc(100vh - 270px);
    }

    /* Left Drawer: Table Selector List */
    .erd-drawer {
      width: 280px;
      flex-shrink: 0;
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: var(--radius-md, 16px);
      padding: 14px;
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      gap: 10px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }

    .erd-drawer.collapsed {
      width: 0;
      padding: 0;
      margin: 0;
      border: none;
      opacity: 0;
      pointer-events: none;
    }

    .erd-drawer-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-bottom: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .erd-drawer-title {
      font-size: 13px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .erd-table-list {
      flex: 1;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 4px;
      padding-right: 4px;
    }

    .erd-table-list::-webkit-scrollbar {
      width: 4px;
    }

    .erd-table-list::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 4px;
    }

    .erd-table-item {
      padding: 7px 10px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid transparent;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      transition: all 0.15s ease;
      font-size: 12px;
      color: #e2e8f0;
    }

    .erd-table-item:hover {
      background: rgba(0, 117, 255, 0.12);
      border-color: rgba(0, 117, 255, 0.3);
      color: #fff;
    }

    .erd-table-item.active {
      background: rgba(0, 117, 255, 0.25);
      border-color: #0075ff;
      color: #fff;
      font-weight: 600;
    }

    .erd-table-item-name {
      display: flex;
      align-items: center;
      gap: 6px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .erd-table-item-badge {
      font-size: 10px;
      background: rgba(255, 255, 255, 0.07);
      padding: 2px 6px;
      border-radius: 10px;
      color: var(--text-tertiary, #a0aec0);
      font-family: monospace;
      flex-shrink: 0;
    }

    /* Canvas Container */
    .erd-canvas-container {
      flex: 1;
      position: relative;
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: var(--radius-md, 16px);
      backdrop-filter: blur(20px);
      overflow: hidden;
      box-shadow: var(--shadow-card, 0 20px 27px 0 rgba(0, 0, 0, 0.05));
    }

    #myDiagramDiv {
      width: 100%;
      height: 100%;
      background-color: transparent;
      outline: none;
    }

    /* Diagram Overlays / Floaters */
    .erd-floating-controls {
      position: absolute;
      bottom: 20px;
      right: 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      z-index: 10;
    }

    .erd-float-btn {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: rgba(11, 20, 55, 0.88);
      border: 1px solid rgba(255, 255, 255, 0.18);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
      transition: all 0.2s ease;
    }

    .erd-float-btn:hover {
      background: #0075ff;
      border-color: #0075ff;
      transform: scale(1.08);
    }

    .erd-float-btn svg {
      width: 16px;
      height: 16px;
      fill: currentColor;
    }

    .erd-legend-overlay {
      position: absolute;
      bottom: 20px;
      left: 20px;
      background: rgba(11, 20, 55, 0.92);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 11px;
      color: #e2e8f0;
      z-index: 10;
      backdrop-filter: blur(12px);
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      pointer-events: none;
      box-shadow: 0 4px 14px rgba(0,0,0,0.4);
    }

    .erd-legend-item {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .erd-legend-dot {
      width: 10px;
      height: 10px;
      border-radius: 2px;
    }

    /* Loading Spinner */
    .erd-loader-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(6, 11, 40, 0.85);
      backdrop-filter: blur(8px);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 16px;
      z-index: 50;
      transition: opacity 0.3s ease;
    }

    .erd-loader-spinner {
      width: 48px;
      height: 48px;
      border: 3px solid rgba(0, 117, 255, 0.2);
      border-top-color: #0075ff;
      border-radius: 50%;
      animation: erd-spin 0.8s linear infinite;
    }

    @keyframes erd-spin {
      to { transform: rotate(360deg); }
    }
  </style>

  <!-- 1. Header Card with Database Metadata & Statistics -->
  <div class="erd-header-card">
    <div class="erd-header-top">
      <div class="erd-title-group">
        <div class="erd-icon-badge">
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 4.02 2 6.5v11C2 19.98 6.48 22 12 22s10-2.02 10-4.5v-11C22 4.02 17.52 2 12 2zm0 2.2c3.96 0 7.8 1.15 7.8 2.3 0 1.15-3.84 2.3-7.8 2.3s-7.8-1.15-7.8-2.3c0-1.15 3.84-2.3 7.8-2.3zm8 6.47c-1.63 1.09-4.66 1.73-8 1.73s-6.37-.64-8-1.73V8.87c2.14 1.13 5.09 1.73 8 1.73s5.86-.6 8-1.73v1.8zm0 4c-1.63 1.09-4.66 1.73-8 1.73s-6.37-.64-8-1.73v-1.8c2.14 1.13 5.09 1.73 8 1.73s5.86-.6 8-1.73v1.8zm0 4.13c-2.14 1.13-5.09 1.7-8 1.7s-5.86-.57-8-1.7v-1.87c1.63 1.09 4.66 1.73 8 1.73s6.37-.64 8-1.73v1.87z"/></svg>
        </div>
        <div class="erd-title-text">
          <h2>
            Entity Relationship Diagram
            <span class="erd-db-pill">MySQL: dynamic_chart</span>
          </h2>
          <p>ไดอะแกรมโครงสร้างฐานข้อมูล ความสัมพันธ์ระหว่างตาราง และฟิลด์ข้อมูลตามแบบอย่าง GoJS Entity Relationship</p>
        </div>
      </div>

      <div style="display:flex; align-items:center; gap:8px;">
        <button class="erd-btn" id="erdBtnToggleDrawer" title="ซ่อน/แสดง รายการตาราง">
          <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
          <span id="erdDrawerBtnText">ซ่อนรายการตาราง</span>
        </button>
        <button class="erd-btn erd-btn-primary" id="erdBtnReload">
          <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
          <span>โหลดใหม่ (Refresh)</span>
        </button>
      </div>
    </div>

    <!-- Statistics Overview Grid -->
    <div class="erd-stats-row">
      <div class="erd-stat-box">
        <div>
          <div class="erd-stat-label">Total Tables</div>
          <div class="erd-stat-val" id="statTablesCount">-</div>
        </div>
        <span style="font-size:20px; opacity:0.8;">🗂️</span>
      </div>
      <div class="erd-stat-box">
        <div>
          <div class="erd-stat-label">Total Columns</div>
          <div class="erd-stat-val" id="statColumnsCount">-</div>
        </div>
        <span style="font-size:20px; opacity:0.8;">📋</span>
      </div>
      <div class="erd-stat-box">
        <div>
          <div class="erd-stat-label">Explicit FKs</div>
          <div class="erd-stat-val" id="statExplicitFKs">-</div>
        </div>
        <span style="font-size:20px; opacity:0.8;">🔗</span>
      </div>
      <div class="erd-stat-box">
        <div>
          <div class="erd-stat-label">Inferred Relations</div>
          <div class="erd-stat-val" id="statInferredRelations">-</div>
        </div>
        <span style="font-size:20px; opacity:0.8;">💡</span>
      </div>
    </div>
  </div>

  <!-- 2. Controls Toolbar -->
  <div class="erd-toolbar">
    <div class="erd-toolbar-left">
      <!-- Search Input -->
      <div class="erd-search-wrap">
        <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        <input type="text" class="erd-search-input" id="erdSearchInput" placeholder="ค้นหาชื่อ Table / Column...">
      </div>

      <!-- Layout Selector -->
      <label style="font-size:12px; color:var(--text-tertiary); display:flex; align-items:center; gap:6px;">
        <span>Layout:</span>
        <select class="erd-select" id="erdLayoutSelect">
          <option value="forceDirected">Force-Directed</option>
          <option value="layeredDigraph">Layered Digraph</option>
          <option value="circular">Circular</option>
          <option value="grid">Grid</option>
        </select>
      </label>

      <!-- Theme Selector -->
      <label style="font-size:12px; color:var(--text-tertiary); display:flex; align-items:center; gap:6px;">
        <span>Theme:</span>
        <select class="erd-select" id="erdThemeSelect">
          <option value="dark">Vision Dark</option>
          <option value="light">Modern Light</option>
          <option value="navy">Vision Navy</option>
        </select>
      </label>

      <!-- Relationship Filter -->
      <label style="font-size:12px; color:var(--text-tertiary); display:flex; align-items:center; gap:6px;">
        <span>Relations:</span>
        <select class="erd-select" id="erdRelationSelect">
          <option value="all">ทั้งหมด (All Relations)</option>
          <option value="explicit">เฉพาะ Foreign Keys จริง (Explicit Only)</option>
          <option value="none">ซ่อนเส้นเชื่อม (Hide Links)</option>
        </select>
      </label>
    </div>

    <div class="erd-toolbar-right">
      <!-- Collapse / Expand all tables -->
      <button class="erd-btn" id="erdBtnToggleAll" title="ยุบหรือขยายฟิลด์ทุกตาราง">
        <span>↔️ ยุบ/ขยายทั้งหมด</span>
      </button>

      <!-- Reset View -->
      <button class="erd-btn" id="erdBtnFitView" title="จัดตำแหน่งให้อยู่กึ่งกลางหน้าจอ">
        <svg viewBox="0 0 24 24"><path d="M15 3l2.3 2.3-2.89 2.87 1.42 1.42L18.7 6.7 21 9V3h-6zM3 9l2.3-2.3 2.87 2.89 1.42-1.42L6.7 5.3 9 3H3v6zm6 12l-2.3-2.3 2.89-2.87-1.42-1.42L5.3 17.3 3 15v6h6zm12-6l-2.3 2.3-2.87-2.89-1.42 1.42 2.89 2.87L15 21h6v-6z"/></svg>
        <span>Fit View</span>
      </button>

      <!-- Export Image -->
      <button class="erd-btn" id="erdBtnExportPng" title="ส่งออกเป็นรูปภาพ PNG">
        <svg viewBox="0 0 24 24"><path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2v9.67z"/></svg>
        <span>Export PNG</span>
      </button>
    </div>
  </div>

  <!-- 3. Workspace: Drawer + Diagram Canvas -->
  <div class="erd-workspace">
    <!-- Left Drawer: Table Selector -->
    <div class="erd-drawer" id="erdDrawer">
      <div class="erd-drawer-header">
        <span class="erd-drawer-title">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="#0075ff"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
          รายชื่อตาราง (<span id="erdDrawerTableCount">0</span>)
        </span>
      </div>
      <div class="erd-table-list" id="erdTableList">
        <!-- Populated dynamically -->
      </div>
    </div>

    <!-- Right: GoJS Diagram Canvas -->
    <div class="erd-canvas-container">
      <div id="myDiagramDiv"></div>

      <!-- Floating Zoom Controls -->
      <div class="erd-floating-controls">
        <button class="erd-float-btn" id="erdZoomIn" title="ขยาย (Zoom In)">
          <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        </button>
        <button class="erd-float-btn" id="erdZoomOut" title="ย่อ (Zoom Out)">
          <svg viewBox="0 0 24 24"><path d="M19 13H5v-2h14v2z"/></svg>
        </button>
        <button class="erd-float-btn" id="erdZoomReset" title="ขนาดปกติ 100%">
          <span style="font-size:11px;font-weight:700;">1:1</span>
        </button>
      </div>

      <!-- Color Legend Overlay -->
      <div class="erd-legend-overlay">
        <div class="erd-legend-item">
          <div class="erd-legend-dot" style="background:#a855f7;"></div>
          <span>Primary Key</span>
        </div>
        <div class="erd-legend-item">
          <div class="erd-legend-dot" style="background:#ef4444;"></div>
          <span>Foreign Key</span>
        </div>
        <div class="erd-legend-item">
          <div class="erd-legend-dot" style="background:#10b981;"></div>
          <span>Numeric</span>
        </div>
        <div class="erd-legend-item">
          <div class="erd-legend-dot" style="background:#3b82f6;"></div>
          <span>Text / String</span>
        </div>
        <div class="erd-legend-item">
          <div class="erd-legend-dot" style="background:#eab308;"></div>
          <span>Date / Time</span>
        </div>
      </div>

      <!-- Loading Overlay -->
      <div class="erd-loader-overlay" id="erdLoader">
        <div class="erd-loader-spinner"></div>
        <div style="color:#fff; font-size:14px; font-weight:600;">กำลังอ่านโครงสร้างฐานข้อมูล dynamic_chart...</div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';

  // Ensure GoJS is loaded
  function loadGoJsIfNeeded(callback) {
    if (typeof go !== 'undefined') {
      callback();
      return;
    }
    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/gojs@3.0.19/release/go.js';
    script.onload = function() {
      // Also optionally load Figures.js
      var figScript = document.createElement('script');
      figScript.src = 'https://cdn.jsdelivr.net/npm/gojs@3.0.19/extensions/Figures.js';
      figScript.onload = function() { callback(); };
      figScript.onerror = function() { callback(); };
      document.head.appendChild(figScript);
    };
    script.onerror = function() {
      console.error('Failed to load GoJS library');
    };
    document.head.appendChild(script);
  }

  // Diagram state
  let myDiagram = null;
  let rawSchemaData = null;
  let allCollapsed = false;

  // DOM Elements
  const loader = document.getElementById('erdLoader');
  const statTables = document.getElementById('statTablesCount');
  const statColumns = document.getElementById('statColumnsCount');
  const statExplicitFKs = document.getElementById('statExplicitFKs');
  const statInferred = document.getElementById('statInferredRelations');
  const tableListContainer = document.getElementById('erdTableList');
  const drawerTableCount = document.getElementById('erdDrawerTableCount');
  const searchInput = document.getElementById('erdSearchInput');
  const layoutSelect = document.getElementById('erdLayoutSelect');
  const themeSelect = document.getElementById('erdThemeSelect');
  const relationSelect = document.getElementById('erdRelationSelect');
  const btnToggleAll = document.getElementById('erdBtnToggleAll');
  const btnFitView = document.getElementById('erdBtnFitView');
  const btnExportPng = document.getElementById('erdBtnExportPng');
  const btnReload = document.getElementById('erdBtnReload');
  const btnToggleDrawer = document.getElementById('erdBtnToggleDrawer');
  const drawerBtnText = document.getElementById('erdDrawerBtnText');
  const drawer = document.getElementById('erdDrawer');

  // Color Theme definitions for Diagram
  const THEME_CONFIGS = {
    dark: {
      divBg: 'transparent',
      nodeFill: '#0b1437',
      nodeStroke: 'rgba(0, 117, 255, 0.4)',
      headerFill: '#0e1d4d',
      headerText: '#ffffff',
      itemText: '#e2e8f0',
      itemType: '#94a3b8',
      linkStroke: '#0075ff',
      linkInferred: '#38bdf8',
      linkText: '#93c5fd'
    },
    light: {
      divBg: '#f8fafc',
      nodeFill: '#ffffff',
      nodeStroke: '#cbd5e1',
      headerFill: '#f1f5f9',
      headerText: '#0f172a',
      itemText: '#334155',
      itemType: '#64748b',
      linkStroke: '#2563eb',
      linkInferred: '#0284c7',
      linkText: '#1d4ed8'
    },
    navy: {
      divBg: '#050c26',
      nodeFill: '#08173d',
      nodeStroke: '#00d4ff',
      headerFill: '#0a225c',
      headerText: '#00d4ff',
      itemText: '#f1f5f9',
      itemType: '#7dd3fc',
      linkStroke: '#00d4ff',
      linkInferred: '#38bdf8',
      linkText: '#7dd3fc'
    }
  };

  /**
   * Initialize GoJS Diagram
   */
  function initDiagram() {
    if (myDiagram) {
      myDiagram.div = null;
    }

    const currentThemeKey = themeSelect ? themeSelect.value : 'dark';
    const theme = THEME_CONFIGS[currentThemeKey] || THEME_CONFIGS.dark;

    // Build Diagram
    myDiagram = new go.Diagram('myDiagramDiv', {
      allowDelete: false,
      allowCopy: false,
      'undoManager.isEnabled': true,
      layout: new go.ForceDirectedLayout({
        isInitial: true,
        maxIterations: 200,
        defaultSpringLength: 120,
        defaultElectricalCharge: 180
      }),
      'animationManager.isEnabled': true
    });

    // Item template for attributes in a node
    const itemTemplate = new go.Panel('Horizontal', {
      margin: new go.Margin(2, 0),
      alignment: go.Spot.Left
    }).add(
      new go.Shape({
        desiredSize: new go.Size(12, 12),
        strokeWidth: 0,
        margin: new go.Margin(0, 6, 0, 0)
      })
      .bind('figure', 'figure', function(f) {
        // Fallback to standard figures if Figures.js extension isn't loaded
        if (typeof go.Shape.getFigureProperties !== 'undefined' && go.Shape.getFigureProperties(f)) {
          return f;
        }
        if (f === 'Decision') return 'Diamond';
        if (f === 'Circle') return 'Ellipse';
        return 'RoundedRectangle';
      })
      .bind('fill', 'color', function(c) {
        const colorMap = {
          purple: '#a855f7',
          red: '#ef4444',
          green: '#10b981',
          yellow: '#eab308',
          blue: '#3b82f6',
          gray: '#94a3b8'
        };
        return colorMap[c] || c;
      }),
      new go.TextBlock({
        font: '12px Inter, system-ui, -apple-system, sans-serif',
        stroke: theme.itemText
      })
      .bind('text', 'name')
      .bind('font', 'iskey', function(k) {
        return k ? 'bold 12px Inter, system-ui, sans-serif' : '12px Inter, system-ui, sans-serif';
      })
    );

    // Node template representing a database table
    myDiagram.nodeTemplate = new go.Node('Auto', {
      selectionAdorned: true,
      resizable: false,
      fromSpot: go.Spot.AllSides,
      toSpot: go.Spot.AllSides,
      layoutConditions: go.LayoutConditions.Standard & ~go.LayoutConditions.NodeSized
    })
    .bindTwoWay('location')
    .add(
      // Outer border shape
      new go.Shape('RoundedRectangle', {
        fill: theme.nodeFill,
        stroke: theme.nodeStroke,
        strokeWidth: 2,
        parameter1: 10 // corner radius
      }),
      new go.Panel('Table', {
        margin: 6,
        stretch: go.Stretch.Fill,
        minSize: new go.Size(180, NaN)
      })
      .addRowDefinition(0, { sizing: go.Sizing.None })
      .add(
        // Header Panel: Table Name, Row Count, & PanelExpanderButton
        new go.Panel('Table', {
          row: 0,
          stretch: go.Stretch.Horizontal,
          background: theme.headerFill,
          margin: new go.Margin(0, 0, 4, 0),
          padding: new go.Margin(4, 6, 4, 6)
        })
        .addColumnDefinition(0, { stretch: go.Stretch.Horizontal })
        .add(
          new go.Panel('Horizontal', { column: 0, alignment: go.Spot.Left })
          .add(
            new go.TextBlock({
              font: 'bold 14px Inter, system-ui, sans-serif',
              stroke: theme.headerText,
              margin: new go.Margin(0, 6, 0, 0)
            }).bind('text', 'key'),
            new go.TextBlock({
              font: '10px monospace',
              stroke: '#00d4ff',
              background: 'rgba(0, 212, 255, 0.12)',
              margin: new go.Margin(0, 6, 0, 0),
              isMultiline: false
            }).bind('text', 'rowCount', function(r) {
              return typeof r === 'number' ? r.toLocaleString() + ' rows' : '';
            })
          ),
          // Expander button for attributes list
          go.GraphObject.build('PanelExpanderButton', {
            column: 1,
            alignment: go.Spot.Right
          }, 'LIST')
        ),

        // Collapsible LIST Panel
        new go.Panel('Table', {
          name: 'LIST',
          row: 1,
          alignment: go.Spot.TopLeft,
          stretch: go.Stretch.Horizontal
        })
        .add(
          // Regular & PK Attributes Header
          new go.TextBlock('Columns & Attributes', {
            row: 0,
            alignment: go.Spot.Left,
            margin: new go.Margin(4, 18, 2, 2),
            font: 'bold 11px Inter, sans-serif',
            stroke: 'rgba(255, 255, 255, 0.5)'
          }),
          go.GraphObject.build('PanelExpanderButton', {
            row: 0,
            alignment: go.Spot.Right
          }, 'NonInherited'),

          // Vertical panel for items
          new go.Panel('Vertical', {
            row: 1,
            name: 'NonInherited',
            alignment: go.Spot.TopLeft,
            defaultAlignment: go.Spot.Left,
            itemTemplate: itemTemplate
          }).bind('itemArray', 'items'),

          // Foreign Key / Inherited Attributes Header (if any)
          new go.TextBlock('Foreign Keys', {
            row: 2,
            alignment: go.Spot.Left,
            margin: new go.Margin(6, 18, 2, 2),
            font: 'bold 11px Inter, sans-serif',
            stroke: '#ef4444'
          }).bind('visible', 'inheritedItems', function(arr) {
            return Array.isArray(arr) && arr.length > 0;
          }),
          go.GraphObject.build('PanelExpanderButton', {
            row: 2,
            alignment: go.Spot.Right
          }, 'Inherited')
          .bind('visible', 'inheritedItems', function(arr) {
            return Array.isArray(arr) && arr.length > 0;
          }),

          // Vertical panel for foreign key items
          new go.Panel('Vertical', {
            row: 3,
            name: 'Inherited',
            alignment: go.Spot.TopLeft,
            defaultAlignment: go.Spot.Left,
            itemTemplate: itemTemplate
          }).bind('itemArray', 'inheritedItems')
        )
      )
    );

    // Link template representing an entity relationship
    myDiagram.linkTemplate = new go.Link({
      selectionAdorned: true,
      layerName: 'Background',
      reshapable: true,
      routing: go.Routing ? go.Routing.AvoidsNodes : go.Link.AvoidsNodes,
      corner: 6,
      curve: go.Curve.JumpOver
    })
    .add(
      new go.Shape({
        strokeWidth: 2
      })
      .bind('stroke', 'isExplicit', function(exp) {
        return exp ? theme.linkStroke : theme.linkInferred;
      })
      .bind('strokeDashArray', 'isExplicit', function(exp) {
        return exp ? null : [4, 4]; // Dashed for inferred relationships
      }),
      // Arrowhead pointing to parent table
      new go.Shape({
        toArrow: 'Standard',
        strokeWidth: 0
      })
      .bind('fill', 'isExplicit', function(exp) {
        return exp ? theme.linkStroke : theme.linkInferred;
      }),
      // From label (cardinality 0..N)
      new go.TextBlock({
        textAlign: 'center',
        font: 'bold 11px Inter, monospace',
        segmentIndex: 0,
        segmentOffset: new go.Point(NaN, NaN),
        segmentOrientation: go.Orientation.Upright
      })
      .bind('text', 'text')
      .bind('stroke', 'isExplicit', function(exp) {
        return exp ? theme.linkText : '#38bdf8';
      }),
      // To label (cardinality 1)
      new go.TextBlock({
        textAlign: 'center',
        font: 'bold 11px Inter, monospace',
        segmentIndex: -1,
        segmentOffset: new go.Point(NaN, NaN),
        segmentOrientation: go.Orientation.Upright
      })
      .bind('text', 'toText')
      .bind('stroke', 'isExplicit', function(exp) {
        return exp ? theme.linkText : '#38bdf8';
      })
    );

    // Click on node to highlight in sidebar
    myDiagram.addDiagramListener('ObjectSingleClicked', function(e) {
      const part = e.subject.part;
      if (part instanceof go.Node) {
        highlightTableInSidebar(part.data.key);
      }
    });
  }

  /**
   * Highlight table in Left Drawer
   */
  function highlightTableInSidebar(tableName) {
    const items = tableListContainer.querySelectorAll('.erd-table-item');
    items.forEach(function(el) {
      if (el.dataset.table === tableName) {
        el.classList.add('active');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      } else {
        el.classList.remove('active');
      }
    });
  }

  /**
   * Focus & zoom to a specific table node
   */
  function focusTable(tableName) {
    if (!myDiagram) return;
    const node = myDiagram.findNodeForKey(tableName);
    if (node) {
      myDiagram.select(node);
      myDiagram.commandHandler.scrollToPart(node);
      highlightTableInSidebar(tableName);
    }
  }

  /**
   * Render Sidebar Table Drawer
   */
  function renderTableList(nodes) {
    tableListContainer.innerHTML = '';
    drawerTableCount.textContent = nodes.length;

    nodes.forEach(function(node) {
      const item = document.createElement('div');
      item.className = 'erd-table-item';
      item.dataset.table = node.key;
      item.innerHTML = `
        <div class="erd-table-item-name" title="${node.key}">
          <span>📄</span>
          <span>${node.key}</span>
        </div>
        <div class="erd-table-item-badge" title="${node.columnCount} columns, ${node.rowCount} rows">
          ${node.columnCount} col
        </div>
      `;
      item.addEventListener('click', function() {
        focusTable(node.key);
      });
      tableListContainer.appendChild(item);
    });
  }

  /**
   * Apply Relationship Filter (All / Explicit / None)
   */
  function applyRelationFilter() {
    if (!myDiagram || !rawSchemaData) return;
    const filter = relationSelect.value;
    let filteredLinks = rawSchemaData.linkDataArray || [];

    if (filter === 'explicit') {
      filteredLinks = filteredLinks.filter(function(l) { return l.isExplicit; });
    } else if (filter === 'none') {
      filteredLinks = [];
    }

    myDiagram.model.linkDataArray = filteredLinks;
  }

  /**
   * Apply Layout Change
   */
  function applyLayout() {
    if (!myDiagram) return;
    const layoutKey = layoutSelect.value;

    if (layoutKey === 'forceDirected') {
      myDiagram.layout = new go.ForceDirectedLayout({
        isInitial: true,
        maxIterations: 200,
        defaultSpringLength: 120,
        defaultElectricalCharge: 180
      });
    } else if (layoutKey === 'layeredDigraph') {
      myDiagram.layout = new go.LayeredDigraphLayout({
        direction: 90,
        layerSpacing: 60,
        columnSpacing: 40
      });
    } else if (layoutKey === 'circular') {
      myDiagram.layout = new go.CircularLayout({
        spacing: 30
      });
    } else if (layoutKey === 'grid') {
      myDiagram.layout = new go.GridLayout({
        wrappingColumn: 4,
        spacing: new go.Size(40, 40)
      });
    }
    myDiagram.layoutDiagram(true);
  }

  /**
   * Fetch Schema from Backend API
   */
  function fetchSchema() {
    loader.style.display = 'flex';
    loader.style.opacity = '1';

    fetch('../php/get_db_schema.php?_t=' + Date.now())
      .then(function(res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function(data) {
        if (data.status !== 'success') {
          throw new Error(data.message || 'Failed to fetch schema');
        }

        rawSchemaData = data;

        // Update Stats
        statTables.textContent = data.summary.totalTables || 0;
        statColumns.textContent = data.summary.totalColumns || 0;
        statExplicitFKs.textContent = data.summary.explicitRelations || 0;
        statInferred.textContent = data.summary.inferredRelations || 0;

        // Render Table List in Drawer
        renderTableList(data.nodeDataArray);

        // Populate GoJS Model
        initDiagram();
        myDiagram.model = new go.GraphLinksModel({
          copiesArrays: true,
          copiesArrayObjects: true,
          nodeDataArray: data.nodeDataArray,
          linkDataArray: data.linkDataArray
        });

        // Hide Loader
        setTimeout(function() {
          loader.style.opacity = '0';
          setTimeout(function() {
            loader.style.display = 'none';
          }, 300);
        }, 200);
      })
      .catch(function(err) {
        console.error('Schema fetch error:', err);
        loader.innerHTML = `
          <div style="text-align:center; color:#ef4444; max-width:400px; padding:20px;">
            <div style="font-size:32px; margin-bottom:12px;">⚠️</div>
            <div style="font-size:16px; font-weight:700; margin-bottom:6px;">เกิดข้อผิดพลาดในการโหลด Schema</div>
            <div style="font-size:12px; color:#94a3b8; margin-bottom:16px;">${err.message}</div>
            <button class="erd-btn erd-btn-primary" onclick="location.reload()">ลองใหม่อีกครั้ง</button>
          </div>
        `;
      });
  }

  // ==========================================
  // Event Listeners & Interactive Controls
  // ==========================================

  // Search input filter & highlight
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.trim().toLowerCase();
      if (!myDiagram) return;

      if (!q) {
        myDiagram.clearHighlighteds();
        // Reset sidebar list
        const items = tableListContainer.querySelectorAll('.erd-table-item');
        items.forEach(function(el) { el.style.display = 'flex'; });
        return;
      }

      let firstMatchNode = null;
      myDiagram.startTransaction('highlight');
      myDiagram.nodes.each(function(node) {
        const data = node.data;
        const tableName = (data.key || '').toLowerCase();
        let match = tableName.includes(q);

        // Also search columns
        if (!match && data.items) {
          match = data.items.some(function(it) {
            return (it.colName || '').toLowerCase().includes(q);
          });
        }

        node.isHighlighted = match;
        if (match && !firstMatchNode) {
          firstMatchNode = node;
        }
      });
      myDiagram.commitTransaction('highlight');

      // Filter sidebar list
      const items = tableListContainer.querySelectorAll('.erd-table-item');
      items.forEach(function(el) {
        const tName = el.dataset.table.toLowerCase();
        el.style.display = tName.includes(q) ? 'flex' : 'none';
      });

      // Scroll to first match
      if (firstMatchNode) {
        myDiagram.select(firstMatchNode);
        myDiagram.commandHandler.scrollToPart(firstMatchNode);
      }
    });
  }

  // Theme change
  if (themeSelect) {
    themeSelect.addEventListener('change', function() {
      if (rawSchemaData) {
        initDiagram();
        myDiagram.model = new go.GraphLinksModel({
          copiesArrays: true,
          copiesArrayObjects: true,
          nodeDataArray: rawSchemaData.nodeDataArray,
          linkDataArray: rawSchemaData.linkDataArray
        });
        applyRelationFilter();
      }
    });
  }

  // Layout change
  if (layoutSelect) {
    layoutSelect.addEventListener('change', applyLayout);
  }

  // Relationship filter change
  if (relationSelect) {
    relationSelect.addEventListener('change', applyRelationFilter);
  }

  // Toggle Collapse / Expand all
  if (btnToggleAll) {
    btnToggleAll.addEventListener('click', function() {
      if (!myDiagram) return;
      allCollapsed = !allCollapsed;
      myDiagram.startTransaction('toggleCollapseAll');
      myDiagram.nodes.each(function(node) {
        const list = node.findObject('LIST');
        if (list) {
          list.visible = !allCollapsed;
        }
      });
      myDiagram.commitTransaction('toggleCollapseAll');
      btnToggleAll.querySelector('span').textContent = allCollapsed ? '↔️ ขยายทั้งหมด' : '↔️ ยุบทั้งหมด';
    });
  }

  // Zoom Controls
  document.getElementById('erdZoomIn')?.addEventListener('click', function() {
    if (myDiagram) myDiagram.commandHandler.increaseZoom(1.2);
  });
  document.getElementById('erdZoomOut')?.addEventListener('click', function() {
    if (myDiagram) myDiagram.commandHandler.decreaseZoom(0.8);
  });
  document.getElementById('erdZoomReset')?.addEventListener('click', function() {
    if (myDiagram) myDiagram.commandHandler.resetZoom(1.0);
  });
  if (btnFitView) {
    btnFitView.addEventListener('click', function() {
      if (myDiagram) myDiagram.commandHandler.zoomToFit();
    });
  }

  // Export PNG Image
  if (btnExportPng) {
    btnExportPng.addEventListener('click', function() {
      if (!myDiagram) return;
      const imgData = myDiagram.makeImageData({
        scale: 1.5,
        background: '#0b1437',
        type: 'image/png'
      });
      const link = document.createElement('a');
      link.download = 'dynamic_chart_db_diagram_' + Date.now() + '.png';
      link.href = imgData;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  }

  // Reload button
  if (btnReload) {
    btnReload.addEventListener('click', fetchSchema);
  }

  // Toggle Left Drawer
  if (btnToggleDrawer) {
    btnToggleDrawer.addEventListener('click', function() {
      drawer.classList.toggle('collapsed');
      const isCollapsed = drawer.classList.contains('collapsed');
      drawerBtnText.textContent = isCollapsed ? 'แสดงรายการตาราง' : 'ซ่อนรายการตาราง';
      setTimeout(function() {
        if (myDiagram) myDiagram.requestUpdate();
      }, 350);
    });
  }

  // Bootstrapping
  loadGoJsIfNeeded(function() {
    fetchSchema();
  });

})();
</script>
