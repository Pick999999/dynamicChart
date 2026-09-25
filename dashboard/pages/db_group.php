<?php
/**
 * DB Group Page
 * Vision UI Dark Glassmorphism Design
 * Renders database table groups from table_groups.json
 * Uses CSS 3D Cylinder visualization from css/db.css (as in db.html)
 */

header('Content-Type: text/html; charset=utf-8');

// Load table_groups.json
$jsonPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'table_groups.json';
$groups = [];
$errorMsg = null;

if (file_exists($jsonPath)) {
    $raw = file_get_contents($jsonPath);
    $groups = json_decode($raw, true);
    if ($groups === null && json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = 'Failed to parse JSON: ' . json_last_error_msg();
    }
} else {
    $errorMsg = 'File not found: table_groups.json at ' . htmlspecialchars($jsonPath);
}

// Calculate stats
$totalGroups = is_array($groups) ? count($groups) : 0;
$totalTables = 0;
if (is_array($groups)) {
    foreach ($groups as $g) {
        if (!empty($g['tablelist']) && is_array($g['tablelist'])) {
            $totalTables += count($g['tablelist']);
        }
    }
}

// Mapping of table names to existing dashboard pages
$tablePageMap = [
    'vpsmaster'           => ['page' => 'vps_master', 'name' => 'VPS Master'],
    'vpsbillinghistory'   => ['page' => 'billing', 'name' => 'AWS Billing'],
    'derivaccount'        => ['page' => 'deriv_account', 'name' => 'Deriv Account'],
    'asset'               => ['page' => 'tables', 'name' => 'Tables'],
    'full_analysis_data'  => ['page' => 'load_full_analysis_data', 'name' => 'Load FullAnalysis'],
    'ticks'               => ['page' => 'tables', 'name' => 'Tables'],
    'candles'             => ['page' => 'tables', 'name' => 'Tables'],
    'candle_analysis'     => ['page' => 'pktrend_action', 'name' => 'pkTrend Action'],
    'vpstradedata'        => ['page' => 'load_vps_trade_data', 'name' => 'Load VPS Trades'],
    'derivtradehistory'   => ['page' => 'load_deriv_trade_history', 'name' => 'Fetch Deriv Trades'],
    'tradehead'           => ['page' => 'calendar_trade', 'name' => 'Calendar Trade'],
    'trade_history'       => ['page' => 'analysis_trade_hist', 'name' => 'Analysis Trade Hist']
];

// Color palettes for group themes
$groupThemes = [
    1 => [
        'name' => 'Indigo / Blue',
        'top_a' => '#a5b4fc',
        'top_b' => '#6366f1',
        'body_a' => '#c7d2fe',
        'body_b' => '#818cf8',
        'bottom' => '#4f46e5',
        'badge_bg' => 'rgba(99, 102, 241, 0.15)',
        'badge_color' => '#818cf8',
        'border' => 'rgba(99, 102, 241, 0.25)',
        'glow' => 'rgba(99, 102, 241, 0.2)'
    ],
    2 => [
        'name' => 'Emerald / Teal',
        'top_a' => '#6ee7b7',
        'top_b' => '#10b981',
        'body_a' => '#a7f3d0',
        'body_b' => '#34d399',
        'bottom' => '#059669',
        'badge_bg' => 'rgba(16, 185, 129, 0.15)',
        'badge_color' => '#34d399',
        'border' => 'rgba(16, 185, 129, 0.25)',
        'glow' => 'rgba(16, 185, 129, 0.2)'
    ],
    3 => [
        'name' => 'Purple / Violet',
        'top_a' => '#d8b4fe',
        'top_b' => '#a855f7',
        'body_a' => '#e9d5ff',
        'body_b' => '#c084fc',
        'bottom' => '#7e22ce',
        'badge_bg' => 'rgba(168, 85, 247, 0.15)',
        'badge_color' => '#c084fc',
        'border' => 'rgba(168, 85, 247, 0.25)',
        'glow' => 'rgba(168, 85, 247, 0.2)'
    ],
    4 => [
        'name' => 'Rose / Amber',
        'top_a' => '#fda4af',
        'top_b' => '#f43f5e',
        'body_a' => '#fecdd3',
        'body_b' => '#fb7185',
        'bottom' => '#e11d48',
        'badge_bg' => 'rgba(244, 63, 94, 0.15)',
        'badge_color' => '#fb7185',
        'border' => 'rgba(244, 63, 94, 0.25)',
        'glow' => 'rgba(244, 63, 94, 0.2)'
    ]
];
?>

<div class="page-content" id="page-db-group">
  <style>
    /* =========================================================
       Scoped Styles for DB Group Page (Vision UI Dark Theme)
       Incorporating CSS-drawn database cylinder from css/db.css
       ========================================================= */
    #page-db-group {
      display: flex;
      flex-direction: column;
      gap: 24px;
      width: 100%;
      color: #e2e8f0;
      font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    }

    /* Header Banner Card */
    .dbgroup-hero-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: var(--radius-md, 16px);
      padding: 22px 26px;
      backdrop-filter: blur(20px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
    }

    .dbgroup-hero-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .dbgroup-hero-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      box-shadow: 0 4px 18px rgba(99, 102, 241, 0.4);
      flex-shrink: 0;
    }
    .dbgroup-hero-icon svg {
      width: 28px;
      height: 28px;
    }

    .dbgroup-hero-title h2 {
      margin: 0;
      font-size: 22px;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: -0.01em;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .dbgroup-hero-title p {
      margin: 4px 0 0;
      font-size: 13px;
      color: var(--text-tertiary, #a0aec0);
    }

    .dbgroup-stats-row {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .dbgroup-stat-chip {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 8px 14px;
      font-size: 12.5px;
      color: #cbd5e1;
    }
    .dbgroup-stat-chip strong {
      color: #ffffff;
      font-weight: 700;
      font-size: 14px;
    }
    .dbgroup-stat-chip .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }

    /* Action bar / Search Filter */
    .dbgroup-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .dbgroup-search-box {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid rgba(226, 232, 240, 0.12);
      border-radius: 12px;
      padding: 8px 16px;
      min-width: 280px;
      flex: 1;
      max-width: 480px;
      backdrop-filter: blur(16px);
      transition: border-color 0.2s;
    }
    .dbgroup-search-box:focus-within {
      border-color: #6366f1;
      box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
    }
    .dbgroup-search-box svg {
      width: 18px;
      height: 18px;
      fill: #94a3b8;
    }
    .dbgroup-search-box input {
      background: transparent;
      border: none;
      color: #f8fafc;
      font-size: 13.5px;
      outline: none;
      width: 100%;
    }
    .dbgroup-search-box input::placeholder {
      color: #64748b;
    }

    .dbgroup-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(99, 102, 241, 0.15);
      border: 1px solid rgba(99, 102, 241, 0.3);
      color: #a5b4fc;
      border-radius: 10px;
      padding: 9px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
    }
    .dbgroup-btn:hover {
      background: rgba(99, 102, 241, 0.28);
      border-color: #818cf8;
      color: #ffffff;
      transform: translateY(-1px);
    }
    .dbgroup-btn svg {
      width: 16px;
      height: 16px;
      fill: currentColor;
    }

    /* =========================================================
       Group Container Card
       ========================================================= */
    .db-group-container {
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: var(--radius-md, 18px);
      padding: 24px;
      backdrop-filter: blur(20px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      transition: border-color 0.25s, box-shadow 0.25s;
      position: relative;
      overflow: hidden;
    }

    .db-group-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: var(--group-accent, #6366f1);
      opacity: 0.85;
    }

    .db-group-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 22px;
      flex-wrap: wrap;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      padding-bottom: 16px;
    }

    .db-group-info {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .db-group-title-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .db-group-badge {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 20px;
      background: var(--group-badge-bg, rgba(99, 102, 241, 0.15));
      color: var(--group-badge-color, #a5b4fc);
      border: 1px solid var(--group-border, rgba(99, 102, 241, 0.3));
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .db-group-name {
      font-size: 19px;
      font-weight: 700;
      color: #ffffff;
      margin: 0;
      letter-spacing: -0.01em;
    }

    .db-group-desc {
      font-size: 13.5px;
      color: #94a3b8;
      margin: 0;
      line-height: 1.5;
      max-width: 800px;
    }

    .db-group-meta {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .db-group-count-tag {
      font-size: 12px;
      font-weight: 600;
      color: #cbd5e1;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 5px 11px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* =========================================================
       Grid of Table Cards inside each Container
       ========================================================= */
    .db-table-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 20px;
    }

    /* Card housing the CSS Cylinder and Table Details */
    .db-table-card {
      background: rgba(15, 23, 42, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      padding: 22px 18px 18px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      transition: all 0.25s ease;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(12px);
    }
    .db-table-card:hover {
      transform: translateY(-5px);
      border-color: var(--group-border, rgba(99, 102, 241, 0.45));
      box-shadow: 0 12px 28px var(--group-glow, rgba(99, 102, 241, 0.25));
      background: rgba(21, 32, 58, 0.85);
    }

    /* =========================================================
       CSS-drawn Database Cylinder (Faithful to css/db.css & db.html)
       ========================================================= */
    .cylinder {
      position: relative;
      width: 104px;
      height: 92px;
      margin-top: 6px;
      flex-shrink: 0;
      transition: transform 0.25s ease;
    }
    .db-table-card:hover .cylinder {
      transform: scale(1.05);
    }

    .cylinder-top {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 34px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--cyl-top-a, #a5b4fc), var(--cyl-top-b, #6d6ff5));
      z-index: 3;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.35);
    }

    .cylinder-body {
      position: absolute;
      top: 16px;
      left: 0;
      width: 100%;
      height: 66px;
      background: linear-gradient(180deg, var(--cyl-body-a, #c7d2fe), var(--cyl-body-b, #8f92f7));
      z-index: 1;
    }

    .cylinder-bottom {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 20px;
      border-radius: 50%;
      background: var(--cyl-bottom, #5457e6);
      z-index: 0;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
    }

    .cylinder-top svg {
      width: 24px;
      height: 24px;
      position: relative;
      top: -2px;
      filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
    }

    /* Table Label & Details */
    .db-table-meta-box {
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      gap: 6px;
    }

    .db-table-name {
      font-family: 'JetBrains Mono', 'IBM Plex Mono', 'Courier New', monospace;
      font-weight: 800;
      font-size: 15px;
      color: #f8fafc;
      letter-spacing: 0.2px;
      line-height: 1.35;
      margin: 0;
      word-break: break-word;
    }

    .db-table-desc {
      font-size: 12.5px;
      color: #94a3b8;
      line-height: 1.45;
      margin: 0;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      min-height: 38px;
    }

    /* URL Badge / Data endpoint */
    .db-url-pill {
      width: 100%;
      background: rgba(0, 0, 0, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 8px;
      padding: 6px 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      font-family: 'JetBrains Mono', 'IBM Plex Mono', monospace;
      font-size: 11px;
      color: #38bdf8;
      margin-top: 4px;
      transition: border-color 0.2s;
    }
    .db-url-pill:hover {
      border-color: rgba(56, 189, 248, 0.4);
    }
    .db-url-text {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      flex: 1;
      text-align: left;
    }
    .db-url-copy-btn {
      background: transparent;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      padding: 2px 4px;
      display: flex;
      align-items: center;
      border-radius: 4px;
      transition: color 0.15s, background 0.15s;
    }
    .db-url-copy-btn:hover {
      color: #38bdf8;
      background: rgba(56, 189, 248, 0.15);
    }
    .db-url-copy-btn svg {
      width: 13px;
      height: 13px;
      fill: currentColor;
    }

    /* Card Action Footer */
    .db-card-actions {
      width: 100%;
      display: flex;
      gap: 8px;
      margin-top: 4px;
    }
    .db-action-link {
      flex: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 6px 10px;
      font-size: 11.5px;
      font-weight: 600;
      color: #cbd5e1;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    .db-action-link:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #ffffff;
      border-color: rgba(255, 255, 255, 0.2);
    }
    .db-action-link.primary {
      background: var(--group-badge-bg, rgba(99, 102, 241, 0.15));
      border-color: var(--group-border, rgba(99, 102, 241, 0.3));
      color: var(--group-badge-color, #a5b4fc);
    }
    .db-action-link.primary:hover {
      background: var(--group-accent, #6366f1);
      color: #ffffff;
    }
    .db-action-link svg {
      width: 12px;
      height: 12px;
      fill: currentColor;
    }

    /* Toast Notification */
    .db-toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: #1e293b;
      color: #f8fafc;
      border: 1px solid rgba(99, 102, 241, 0.5);
      border-radius: 10px;
      padding: 10px 18px;
      font-size: 13px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 99999;
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .db-toast.show {
      transform: translateY(0);
      opacity: 1;
    }

    /* Empty search hint */
    .db-no-results {
      text-align: center;
      padding: 40px 20px;
      background: rgba(15, 23, 42, 0.4);
      border: 1px dashed rgba(255, 255, 255, 0.1);
      border-radius: 14px;
      color: #94a3b8;
      font-size: 14px;
      display: none;
    }

    @media (max-width: 640px) {
      .db-table-grid {
        grid-template-columns: 1fr;
      }
      .cylinder {
        width: 88px;
        height: 80px;
      }
      .cylinder-top { height: 30px; }
      .cylinder-body { top: 14px; height: 56px; }
      .cylinder-bottom { height: 20px; }
    }
  </style>

  <!-- Hero Banner Card -->
  <div class="dbgroup-hero-card">
    <div class="dbgroup-hero-left">
      <div class="dbgroup-hero-icon">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Cylinder Icon -->
          <ellipse cx="12" cy="5" rx="8" ry="3" fill="currentColor" fill-opacity="0.3" stroke="currentColor" stroke-width="1.8"/>
          <path d="M4 5v14c0 1.66 3.58 3 8 3s8-1.34 8-3V5" stroke="currentColor" stroke-width="1.8"/>
          <path d="M4 12c0 1.66 3.58 3 8 3s8-1.34 8-3" stroke="currentColor" stroke-width="1.8"/>
        </svg>
      </div>
      <div class="dbgroup-hero-title">
        <h2>
          <span>Database Table Groups</span>
          <span style="font-size:12px; font-weight:600; background:rgba(99,102,241,0.2); color:#a5b4fc; padding:2px 8px; border-radius:12px; border:1px solid rgba(99,102,241,0.3);">
            MySQL: dynamic_chart
          </span>
        </h2>
        <p>กลุ่มของ Database Tables ทั้งหมด ถูกจัดแบ่งตามฟังก์ชันการทำงาน และอ่านโครงสร้างจาก <code>table_groups.json</code></p>
      </div>
    </div>

    <!-- Stats summary -->
    <div class="dbgroup-stats-row">
      <div class="dbgroup-stat-chip">
        <span class="dot" style="background:#818cf8; box-shadow:0 0 8px #818cf8;"></span>
        <span>กลุ่มทั้งหมด:</span>
        <strong id="stat-group-count"><?php echo $totalGroups; ?></strong>
      </div>
      <div class="dbgroup-stat-chip">
        <span class="dot" style="background:#34d399; box-shadow:0 0 8px #34d399;"></span>
        <span>ตารางทั้งหมด:</span>
        <strong id="stat-table-count"><?php echo $totalTables; ?></strong>
      </div>
      <div class="dbgroup-stat-chip">
        <span class="dot" style="background:#f59e0b; box-shadow:0 0 8px #f59e0b;"></span>
        <span>Data Source:</span>
        <strong style="font-family:monospace; font-size:12px;">table_groups.json</strong>
      </div>
    </div>
  </div>

  <!-- Search & Action Toolbar -->
  <div class="dbgroup-toolbar">
    <div class="dbgroup-search-box">
      <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
      <input type="text" id="dbgroup-search-input" placeholder="ค้นหาตาราง, กลุ่ม, หรือ URL API..." autocomplete="off">
    </div>

    <div style="display:flex; gap:10px; align-items:center;">
      <button class="dbgroup-btn" id="dbgroup-btn-diagram" onclick="window.location.hash = '#db_diagram';">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 4.02 2 6.5v11C2 19.98 6.48 22 12 22s10-2.02 10-4.5v-11C22 4.02 17.52 2 12 2zm0 2.2c3.96 0 7.8 1.15 7.8 2.3 0 1.15-3.84 2.3-7.8 2.3s-7.8-1.15-7.8-2.3c0-1.15 3.84-2.3 7.8-2.3z"/></svg>
        <span>เปิด DB Diagram</span>
      </button>
      <button class="dbgroup-btn" id="dbgroup-btn-refresh" title="โหลดข้อมูลใหม่จากไฟล์ JSON">
        <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
        <span>รีเฟรช</span>
      </button>
    </div>
  </div>

  <?php if ($errorMsg): ?>
    <div style="background:rgba(239, 68, 68, 0.15); border:1px solid rgba(239, 68, 68, 0.3); border-radius:12px; padding:16px 20px; color:#fca5a5;">
      <strong>ข้อผิดพลาด:</strong> <?php echo $errorMsg; ?>
    </div>
  <?php endif; ?>

  <div id="db-groups-wrapper" style="display:flex; flex-direction:column; gap:24px;">
    <?php if (!empty($groups) && is_array($groups)): ?>
      <?php foreach ($groups as $gIdx => $group): 
        $gCode = $group['groupcode'] ?? ($gIdx + 1);
        $gName = $group['groupname'] ?? ('กลุ่มที่ ' . $gCode);
        $gDesc = $group['groupDesc'] ?? '';
        $tables = $group['tablelist'] ?? [];
        $theme = $groupThemes[$gCode] ?? $groupThemes[1];
      ?>
        <!-- Group Container -->
        <div class="db-group-container" 
             data-group-code="<?php echo htmlspecialchars($gCode); ?>"
             data-group-name="<?php echo htmlspecialchars($gName); ?>"
             style="
               --group-accent: <?php echo $theme['bottom']; ?>;
               --group-badge-bg: <?php echo $theme['badge_bg']; ?>;
               --group-badge-color: <?php echo $theme['badge_color']; ?>;
               --group-border: <?php echo $theme['border']; ?>;
               --group-glow: <?php echo $theme['glow']; ?>;
               --cyl-top-a: <?php echo $theme['top_a']; ?>;
               --cyl-top-b: <?php echo $theme['top_b']; ?>;
               --cyl-body-a: <?php echo $theme['body_a']; ?>;
               --cyl-body-b: <?php echo $theme['body_b']; ?>;
               --cyl-bottom: <?php echo $theme['bottom']; ?>;
             ">
          
          <div class="db-group-header">
            <div class="db-group-info">
              <div class="db-group-title-row">
                <span class="db-group-badge">
                  <svg width="8" height="8" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="currentColor"/></svg>
                  Group #<?php echo htmlspecialchars($gCode); ?>
                </span>
                <h3 class="db-group-name"><?php echo htmlspecialchars($gName); ?></h3>
              </div>
              <?php if (!empty($gDesc)): ?>
                <p class="db-group-desc"><?php echo htmlspecialchars($gDesc); ?></p>
              <?php endif; ?>
            </div>

            <div class="db-group-meta">
              <span class="db-group-count-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
                <span><?php echo count($tables); ?> ตาราง</span>
              </span>
            </div>
          </div>

          <!-- Grid of Tables in this Group -->
          <div class="db-table-grid">
            <?php foreach ($tables as $tIdx => $t): 
              $tableName = $t['tablename'] ?? '';
              $tableDesc = $t['tableDescription'] ?? '';
              $dataUrl   = $t['getDataFromUrl'] ?? '';
              $normalizedName = strtolower(str_replace([' ', '_'], '', $tableName));
              $pageInfo = $tablePageMap[$normalizedName] ?? null;

              // Cylinder icons vary slightly by table type
              $iconSvg = '
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4 20l1-4L15 6l3 3-10 10-4 1z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                  <path d="M13 8l3 3" stroke="white" stroke-width="1.8"/>
                </svg>
              ';
              if (strpos($normalizedName, 'trade') !== false) {
                $iconSvg = '
                  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.5 18.5l6-6 4 4 7-7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 9.5h6.5V16" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                ';
              } elseif (strpos($normalizedName, 'candle') !== false || strpos($normalizedName, 'tick') !== false) {
                $iconSvg = '
                  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="7" y="6" width="10" height="12" rx="1.5" stroke="white" stroke-width="1.8"/>
                    <path d="M12 2v4M12 18v4" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                  </svg>
                ';
              } elseif (strpos($normalizedName, 'vps') !== false) {
                $iconSvg = '
                  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="4" width="18" height="6" rx="1.5" stroke="white" stroke-width="1.8"/>
                    <rect x="3" y="14" width="18" height="6" rx="1.5" stroke="white" stroke-width="1.8"/>
                    <circle cx="6.5" cy="7" r="1" fill="white"/>
                    <circle cx="6.5" cy="17" r="1" fill="white"/>
                  </svg>
                ';
              } elseif (strpos($normalizedName, 'account') !== false || strpos($normalizedName, 'asset') !== false) {
                $iconSvg = '
                  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="4.5" stroke="white" stroke-width="1.8"/>
                    <path d="M4 20c0-3.5 3.5-5.5 8-5.5s8 2 8 5.5" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                  </svg>
                ';
              }
            ?>
              <!-- Single Table Card -->
              <div class="db-table-card" 
                   data-table-name="<?php echo htmlspecialchars(strtolower($tableName)); ?>"
                   data-table-desc="<?php echo htmlspecialchars(strtolower($tableDesc)); ?>"
                   data-table-url="<?php echo htmlspecialchars(strtolower($dataUrl)); ?>">
                
                <!-- 3D CSS Database Cylinder (From css/db.css) -->
                <div class="cylinder" title="<?php echo htmlspecialchars($tableName); ?>">
                  <div class="cylinder-top">
                    <?php echo $iconSvg; ?>
                  </div>
                  <div class="cylinder-body"></div>
                  <div class="cylinder-bottom"></div>
                </div>

                <!-- Table Metadata -->
                <div class="db-table-meta-box">
                  <h4 class="db-table-name"><?php echo htmlspecialchars($tableName); ?></h4>
                  <p class="db-table-desc" title="<?php echo htmlspecialchars($tableDesc); ?>">
                    <?php echo htmlspecialchars($tableDesc); ?>
                  </p>
                </div>

                <!-- URL Pill Badge -->
                <?php if (!empty($dataUrl)): ?>
                  <div class="db-url-pill" title="URL ปลายทางสำหรับจัดการ/ดึงข้อมูลตารางนี้">
                    <span style="opacity:0.6; font-size:10px;">URL:</span>
                    <span class="db-url-text"><?php echo htmlspecialchars($dataUrl); ?></span>
                    <button class="db-url-copy-btn" 
                            data-copy="<?php echo htmlspecialchars($dataUrl); ?>" 
                            title="คัดลอก URL">
                      <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                    </button>
                  </div>
                <?php endif; ?>

                <!-- Action links -->
                <div class="db-card-actions">
                  <?php if ($pageInfo): ?>
                    <button type="button" 
                            class="db-action-link primary" 
                            onclick="window.location.hash = '#<?php echo $pageInfo['page']; ?>';" 
                            title="เปิดหน้า <?php echo htmlspecialchars($pageInfo['name']); ?>">
                      <svg viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                      <span><?php echo htmlspecialchars($pageInfo['name']); ?></span>
                    </button>
                  <?php endif; ?>

                  <button type="button" 
                          class="db-action-link" 
                          onclick="window.location.hash = '#db_diagram';" 
                          title="ดู Schema ใน DB Diagram">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 4.02 2 6.5v11C2 19.98 6.48 22 12 22s10-2.02 10-4.5v-11C22 4.02 17.52 2 12 2zm0 2.2c3.96 0 7.8 1.15 7.8 2.3 0 1.15-3.84 2.3-7.8 2.3s-7.8-1.15-7.8-2.3c0-1.15 3.84-2.3 7.8-2.3z"/></svg>
                    <span>ERD</span>
                  </button>
                </div>

              </div>
            <?php endforeach; ?>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="db-no-results" id="db-no-results">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5" style="margin-bottom:12px;">
        <circle cx="11" cy="11" r="8"/>
        <path d="M21 21l-4.35-4.35"/>
      </svg>
      <div style="font-size:16px; font-weight:600; color:#e2e8f0; margin-bottom:4px;">ไม่พบข้อมูลตารางที่ค้นหา</div>
      <div>กรุณาลองค้นหาด้วยชื่อตาราง, คำอธิบาย หรือ URL อื่น</div>
    </div>
  </div>

  <!-- Toast Notification element -->
  <div class="db-toast" id="db-toast">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="#34d399"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
    <span id="db-toast-text">คัดลอก URL สำเร็จ</span>
  </div>
</div>

<script>
(function() {
  'use strict';

  // Search filter functionality
  const searchInput = document.getElementById('dbgroup-search-input');
  const groupContainers = document.querySelectorAll('.db-group-container');
  const noResults = document.getElementById('db-no-results');
  const toast = document.getElementById('db-toast');
  const toastText = document.getElementById('db-toast-text');

  function showToast(msg) {
    if (!toast) return;
    toastText.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => {
      toast.classList.remove('show');
    }, 2500);
  }

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.trim().toLowerCase();
      let totalVisibleCards = 0;

      groupContainers.forEach(container => {
        const groupName = (container.dataset.groupName || '').toLowerCase();
        const cards = container.querySelectorAll('.db-table-card');
        let groupHasVisibleCard = false;

        cards.forEach(card => {
          const tableName = card.dataset.tableName || '';
          const tableDesc = card.dataset.tableDesc || '';
          const tableUrl = card.dataset.tableUrl || '';

          const matches = !q || 
            tableName.includes(q) || 
            tableDesc.includes(q) || 
            tableUrl.includes(q) || 
            groupName.includes(q);

          if (matches) {
            card.style.display = 'flex';
            groupHasVisibleCard = true;
            totalVisibleCards++;
          } else {
            card.style.display = 'none';
          }
        });

        container.style.display = groupHasVisibleCard ? 'block' : 'none';
      });

      if (noResults) {
        noResults.style.display = (totalVisibleCards === 0 && q.length > 0) ? 'block' : 'none';
      }
    });
  }

  // Copy to clipboard
  document.querySelectorAll('.db-url-copy-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const textToCopy = this.getAttribute('data-copy');
      if (!textToCopy) return;

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(() => {
          showToast('คัดลอก URL แล้ว: ' + textToCopy);
        }).catch(() => fallbackCopy(textToCopy));
      } else {
        fallbackCopy(textToCopy);
      }
    });
  });

  function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      showToast('คัดลอก URL แล้ว: ' + text);
    } catch (err) {
      showToast('ไม่สามารถคัดลอก URL ได้');
    }
    document.body.removeChild(textArea);
  }

  // Refresh button
  const refreshBtn = document.getElementById('dbgroup-btn-refresh');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', function() {
      showToast('กำลังโหลดข้อมูลใหม่...');
      if (typeof navigateTo === 'function') {
        navigateTo('db_group');
      } else {
        window.location.reload();
      }
    });
  }

})();
</script>
