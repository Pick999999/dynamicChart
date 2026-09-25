<?php
/**
 * All Load Data Page
 * Vision UI Dark Glassmorphism Design
 * Inspects loaded data from table_load_data.json / table_loaded_data.json
 * Filters by VPS from vpsMaster and Datepicker
 * Provides AJAX trigger to execute generate_loaded_data_json.php
 */

header('Content-Type: text/html; charset=utf-8');

require_once dirname(__DIR__, 2) . '/php/db.php';

// 1. ดึงรายการ VPS จาก vpsMaster และ Deriv Accounts
$vpsList = [];
$derivAccounts = [];
try {
    $pdo = getDbConnection();
    $stmtVps = $pdo->query("SELECT id, vpsCode, vpsName, publicIP, url, portno, DerivAccountID, remark FROM vpsMaster ORDER BY CASE WHEN id = 9999 THEN 99999 ELSE id END ASC");
    $vpsList = $stmtVps->fetchAll(PDO::FETCH_ASSOC);

    $stmtAcc = $pdo->query("SELECT id, email, appName, appId, accountId, status, token FROM derivAccount WHERE status = 'active'");
    $derivAccounts = $stmtAcc->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback if DB query fails
    $vpsList = [
        ['id' => 1, 'vpsCode' => '1', 'vpsName' => 'AWS London (nutv99)', 'publicIP' => '18.170.39.77', 'url' => 'https://pkderiv.online', 'portno' => null, 'DerivAccountID' => 3],
        ['id' => 2, 'vpsCode' => '2', 'vpsName' => 'Google Clould', 'publicIP' => 'gpkderiv.shop', 'url' => 'https://gpkderiv.shop', 'portno' => null, 'DerivAccountID' => 4],
        ['id' => 3, 'vpsCode' => '3', 'vpsName' => 'Oracle-Free-Tier-3', 'publicIP' => '161.118.203.228', 'url' => 'http://161.118.203.228:3000', 'portno' => 3000, 'DerivAccountID' => 5],
        ['id' => 4, 'vpsCode' => '4', 'vpsName' => 'Oracle-Free-Tier-4', 'publicIP' => '161.118.217.177', 'url' => 'http://161.118.217.177:3000', 'portno' => 3000, 'DerivAccountID' => 3],
        ['id' => 9999, 'vpsCode' => '9999', 'vpsName' => 'serverDeriv', 'publicIP' => 'Deriv Direct Feed', 'url' => null, 'portno' => null, 'DerivAccountID' => null]
    ];
}

// 2. ตรวจสอบและอ่านข้อมูลจากไฟล์ JSON
$jsonPath1 = dirname(__DIR__, 2) . '/table_load_data.json';
$jsonPath2 = dirname(__DIR__, 2) . '/table_loaded_data.json';
$activeJsonPath = file_exists($jsonPath1) ? $jsonPath1 : (file_exists($jsonPath2) ? $jsonPath2 : null);

$loadedData = [];
$lastModifiedTime = null;
if ($activeJsonPath && file_exists($activeJsonPath)) {
    $rawJson = file_get_contents($activeJsonPath);
    $loadedData = json_decode($rawJson, true) ?: [];
    $lastModifiedTime = date('Y-m-d H:i:s', filemtime($activeJsonPath));
}

// Map VPS Name lookup by code
$vpsNameMap = [];
foreach ($vpsList as $v) {
    $codeKey = (string)$v['vpsCode'];
    $vpsNameMap[$codeKey] = $v['vpsName'];
    $vpsNameMap[(string)$v['id']] = $v['vpsName'];
}
$vpsNameMap['9999'] = 'serverDeriv (Deriv Market)';
?>

<div class="page-content" id="page-all-load-data">
  <style>
    /* Scoped Styles for All Load Data Page */
    #page-all-load-data {
      display: flex;
      flex-direction: column;
      gap: 22px;
      width: 100%;
      color: #e2e8f0;
      font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    }

    /* Hero Header Banner */
    .ald-hero {
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
      gap: 18px;
    }

    .ald-hero-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .ald-hero-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      box-shadow: 0 4px 18px rgba(0, 117, 255, 0.4);
      flex-shrink: 0;
    }
    .ald-hero-icon svg {
      width: 28px;
      height: 28px;
    }

    .ald-hero-title h2 {
      margin: 0;
      font-size: 22px;
      font-weight: 700;
      color: #ffffff;
      display: flex;
      align-items: center;
      gap: 10px;
      letter-spacing: -0.01em;
    }
    .ald-hero-title p {
      margin: 4px 0 0;
      font-size: 13px;
      color: var(--text-tertiary, #a0aec0);
    }

    .ald-hero-actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    /* Run Generate Button */
    .ald-btn-sync {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      background: linear-gradient(135deg, #0075ff 0%, #0055d4 100%);
      color: #ffffff;
      border: 1px solid rgba(0, 212, 255, 0.35);
      border-radius: 12px;
      padding: 10px 20px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0, 117, 255, 0.35);
      transition: all 0.25s ease;
    }
    .ald-btn-sync:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 117, 255, 0.5);
      border-color: #00d4ff;
    }
    .ald-btn-sync:active {
      transform: translateY(0);
    }
    .ald-btn-sync svg {
      width: 17px;
      height: 17px;
      fill: currentColor;
      transition: transform 0.6s ease;
    }
    .ald-btn-sync.running svg {
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      100% { transform: rotate(360deg); }
    }

    /* VPS & Date Control Card */
    .ald-controls-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: var(--radius-md, 16px);
      padding: 22px 24px;
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .ald-controls-section-title {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #38bdf8;
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 0;
    }

    /* VPS Grid Container */
    .ald-vps-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 12px;
    }

    .ald-vps-chip {
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 12px 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      user-select: none;
      position: relative;
    }
    .ald-vps-chip:hover {
      background: rgba(30, 41, 59, 0.85);
      border-color: rgba(56, 189, 248, 0.4);
      transform: translateY(-2px);
    }
    .ald-vps-chip.active {
      background: linear-gradient(135deg, rgba(0, 117, 255, 0.25) 0%, rgba(0, 212, 255, 0.15) 100%);
      border-color: #00d4ff;
      box-shadow: 0 0 16px rgba(0, 212, 255, 0.3);
    }

    .ald-vps-chip-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.06);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #38bdf8;
      flex-shrink: 0;
    }
    .ald-vps-chip.active .ald-vps-chip-icon {
      background: #0075ff;
      color: #ffffff;
      box-shadow: 0 2px 10px rgba(0, 117, 255, 0.4);
    }
    .ald-vps-chip-icon svg {
      width: 18px;
      height: 18px;
    }

    .ald-vps-chip-body {
      overflow: hidden;
      flex: 1;
    }
    .ald-vps-chip-name {
      font-size: 13px;
      font-weight: 700;
      color: #ffffff;
      margin: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .ald-vps-chip-sub {
      font-size: 11px;
      color: #94a3b8;
      margin: 2px 0 0;
      font-family: monospace;
    }

    /* Datepicker & Quick Filter Row */
    .ald-date-row {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      padding-top: 6px;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .ald-date-input-group {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(15, 23, 42, 0.8);
      border: 1px solid rgba(226, 232, 240, 0.15);
      border-radius: 12px;
      padding: 8px 16px;
      transition: border-color 0.2s;
    }
    .ald-date-input-group:focus-within {
      border-color: #00d4ff;
      box-shadow: 0 0 0 2px rgba(0, 212, 255, 0.25);
    }
    .ald-date-input-group svg {
      width: 18px;
      height: 18px;
      fill: #38bdf8;
    }
    .ald-date-input-group input[type="date"] {
      background: transparent;
      border: none;
      color: #ffffff;
      font-family: inherit;
      font-size: 13.5px;
      outline: none;
      cursor: pointer;
      color-scheme: dark;
    }

    .ald-quick-dates {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }
    .ald-quick-chip {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12px;
      color: #cbd5e1;
      cursor: pointer;
      transition: all 0.2s;
    }
    .ald-quick-chip:hover {
      background: rgba(56, 189, 248, 0.15);
      border-color: #38bdf8;
      color: #ffffff;
    }
    .ald-quick-chip.active {
      background: #0075ff;
      border-color: #00d4ff;
      color: #ffffff;
      font-weight: 600;
    }

    /* Stat Cards Row */
    .ald-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 16px;
    }
    .ald-stat-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: 14px;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      backdrop-filter: blur(16px);
    }
    .ald-stat-text p {
      margin: 0;
      font-size: 11.5px;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .ald-stat-text h3 {
      margin: 4px 0 0;
      font-size: 20px;
      font-weight: 700;
      color: #ffffff;
    }
    .ald-stat-badge {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .ald-stat-badge svg {
      width: 22px;
      height: 22px;
    }

    /* Results Table Card */
    .ald-table-card {
      background: var(--bg-card, rgba(6, 11, 40, 0.74));
      border: 1px solid var(--border-color, rgba(226, 232, 240, 0.1));
      border-radius: var(--radius-md, 16px);
      padding: 20px 24px;
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      gap: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .ald-table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .ald-table-title {
      font-size: 16px;
      font-weight: 700;
      color: #ffffff;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .ald-table-search {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(15, 23, 42, 0.8);
      border: 1px solid rgba(226, 232, 240, 0.12);
      border-radius: 10px;
      padding: 6px 12px;
      width: 240px;
    }
    .ald-table-search input {
      background: transparent;
      border: none;
      color: #ffffff;
      font-size: 12.5px;
      outline: none;
      width: 100%;
    }
    .ald-table-search svg {
      width: 15px;
      height: 15px;
      fill: #94a3b8;
    }

    /* The HTML Table */
    .ald-table-wrap {
      overflow-x: auto;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .ald-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
      text-align: left;
    }
    .ald-table th {
      background: rgba(15, 23, 42, 0.85);
      color: #94a3b8;
      font-weight: 600;
      padding: 12px 16px;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.06em;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      white-space: nowrap;
    }
    .ald-table td {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: #cbd5e1;
      vertical-align: middle;
    }
    .ald-table tbody tr:hover td {
      background: rgba(30, 41, 59, 0.5);
    }
    .ald-table tbody tr:last-child td {
      border-bottom: none;
    }

    /* Badges */
    .ald-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11.5px;
      font-weight: 600;
    }
    .ald-badge.group3 {
      background: rgba(168, 85, 247, 0.15);
      color: #c084fc;
      border: 1px solid rgba(168, 85, 247, 0.3);
    }
    .ald-badge.group4 {
      background: rgba(244, 63, 94, 0.15);
      color: #fb7185;
      border: 1px solid rgba(244, 63, 94, 0.3);
    }

    .ald-server-tag {
      font-family: monospace;
      font-size: 12px;
      background: rgba(56, 189, 248, 0.12);
      color: #38bdf8;
      padding: 3px 8px;
      border-radius: 6px;
      border: 1px solid rgba(56, 189, 248, 0.25);
      display: inline-block;
    }

    .ald-num-badge {
      font-family: 'JetBrains Mono', 'IBM Plex Mono', monospace;
      font-weight: 700;
      color: #34d399;
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.25);
      padding: 4px 10px;
      border-radius: 8px;
      display: inline-block;
      min-width: 70px;
      text-align: right;
    }

    /* Status Cell & Load Buttons */
    .ald-status-cell-wrap {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      flex-wrap: wrap;
    }
    .ald-status-ready {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 11px;
      font-weight: 600;
      color: #34d399;
      background: rgba(16, 185, 129, 0.12);
      padding: 4px 10px;
      border-radius: 14px;
      border: 1px solid rgba(16, 185, 129, 0.25);
      white-space: nowrap;
    }
    .ald-btn-load {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.18) 0%, rgba(217, 119, 6, 0.3) 100%);
      border: 1px solid rgba(245, 158, 11, 0.55);
      color: #fbbf24;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 11.5px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 2px 10px rgba(245, 158, 11, 0.2);
      transition: all 0.2s ease;
      white-space: nowrap;
      text-decoration: none;
    }
    .ald-btn-load:hover {
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.4) 0%, rgba(217, 119, 6, 0.6) 100%);
      border-color: #f59e0b;
      color: #ffffff;
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(245, 158, 11, 0.45);
    }
    .ald-btn-load:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    .ald-btn-load svg {
      width: 13px;
      height: 13px;
      fill: currentColor;
    }
    .ald-btn-load.ald-btn-load-more {
      background: linear-gradient(135deg, rgba(14, 165, 233, 0.18) 0%, rgba(2, 132, 199, 0.3) 100%);
      border-color: rgba(56, 189, 248, 0.5);
      color: #38bdf8;
      box-shadow: 0 2px 10px rgba(14, 165, 233, 0.2);
    }
    .ald-btn-load.ald-btn-load-more:hover {
      background: linear-gradient(135deg, rgba(14, 165, 233, 0.45) 0%, rgba(2, 132, 199, 0.65) 100%);
      border-color: #38bdf8;
      color: #ffffff;
      box-shadow: 0 4px 15px rgba(14, 165, 233, 0.45);
    }

    /* Wait Screen Fullscreen Overlay */
    .ald-wait-overlay {
      position: fixed;
      inset: 0;
      background: rgba(3, 7, 24, 0.86);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      z-index: 999999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 24px;
      animation: aldWaitFadeIn 0.25s ease-out;
    }
    @keyframes aldWaitFadeIn {
      from { opacity: 0; transform: scale(0.97); }
      to   { opacity: 1; transform: scale(1); }
    }
    .ald-wait-modal {
      background: linear-gradient(145deg, rgba(11, 20, 55, 0.96) 0%, rgba(6, 11, 40, 0.98) 100%);
      border: 1px solid rgba(0, 212, 255, 0.35);
      box-shadow: 0 24px 70px rgba(0, 0, 0, 0.75), 0 0 45px rgba(0, 117, 255, 0.22);
      border-radius: 20px;
      width: 100%;
      max-width: 660px;
      padding: 26px 30px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      color: #f8fafc;
      box-sizing: border-box;
    }
    .ald-wait-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 16px;
    }
    .ald-wait-header-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .ald-wait-spinner-wrap {
      position: relative;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .ald-wait-spinner {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      border: 3px solid rgba(0, 212, 255, 0.18);
      border-top-color: #00d4ff;
      border-right-color: #0075ff;
      animation: aldSpin 0.9s linear infinite;
    }
    .ald-wait-spinner.done {
      border-color: #10b981;
      animation: none;
    }
    @keyframes aldSpin {
      100% { transform: rotate(360deg); }
    }
    .ald-wait-spinner-icon {
      width: 26px;
      height: 26px;
      color: #38bdf8;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .ald-wait-spinner-icon svg {
      width: 22px;
      height: 22px;
      fill: currentColor;
    }
    .ald-wait-title {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: -0.01em;
    }
    .ald-wait-meta {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 6px;
    }
    .ald-wait-badge {
      font-size: 11.5px;
      font-family: monospace;
      padding: 2px 9px;
      border-radius: 6px;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #93c5fd;
    }
    .ald-wait-badge.tbl {
      background: rgba(168, 85, 247, 0.15);
      border-color: rgba(168, 85, 247, 0.35);
      color: #d8b4fe;
      font-weight: 700;
    }
    .ald-wait-badge.date {
      background: rgba(56, 189, 248, 0.15);
      border-color: rgba(56, 189, 248, 0.35);
      color: #38bdf8;
      font-weight: 700;
    }
    .ald-wait-btn-close {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #cbd5e1;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      font-size: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      line-height: 1;
      transition: all 0.2s;
    }
    .ald-wait-btn-close:hover {
      background: rgba(239, 68, 68, 0.25);
      border-color: #ef4444;
      color: #ffffff;
    }

    /* Stepper Tracker */
    .ald-wait-stepper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 12px;
      padding: 12px 18px;
      gap: 8px;
    }
    .ald-step-item {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 12px;
      color: #64748b;
      font-weight: 600;
      transition: all 0.25s;
    }
    .ald-step-item.active {
      color: #38bdf8;
    }
    .ald-step-item.done {
      color: #34d399;
    }
    .ald-step-dot {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
    }
    .ald-step-item.active .ald-step-dot {
      background: #0075ff;
      border-color: #00d4ff;
      color: #ffffff;
      box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
    }
    .ald-step-item.done .ald-step-dot {
      background: #10b981;
      border-color: #34d399;
      color: #ffffff;
    }
    .ald-step-line {
      flex: 1;
      height: 2px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 2px;
      min-width: 12px;
    }

    /* Realtime Console Log Terminal */
    .ald-wait-console {
      background: #070d1e;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .ald-wait-console-header {
      background: rgba(15, 23, 42, 0.95);
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      padding: 8px 14px;
      font-size: 11.5px;
      font-weight: 700;
      color: #94a3b8;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .ald-wait-log-list {
      padding: 12px 14px;
      max-height: 200px;
      min-height: 140px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-family: 'JetBrains Mono', 'IBM Plex Mono', Consolas, monospace;
      font-size: 12px;
      line-height: 1.5;
    }
    .ald-wait-log-entry {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      word-break: break-word;
    }
    .ald-wait-log-entry.info {
      color: #94a3b8;
    }
    .ald-wait-log-entry.success {
      color: #34d399;
    }
    .ald-wait-log-entry.warn {
      color: #fbbf24;
    }
    .ald-wait-log-entry.error {
      color: #f87171;
    }

    /* Footer */
    .ald-wait-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      padding-top: 4px;
    }
    .ald-wait-note {
      font-size: 12px;
      color: #94a3b8;
      font-style: italic;
    }
    .ald-wait-btn-action {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: #ffffff;
      border: none;
      border-radius: 10px;
      padding: 10px 22px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
      transition: all 0.2s;
    }
    .ald-wait-btn-action:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(16, 185, 129, 0.55);
    }

    /* Empty state */
    .ald-empty-state {
      text-align: center;
      padding: 48px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      color: #94a3b8;
    }
    .ald-empty-state svg {
      width: 52px;
      height: 52px;
      stroke: #64748b;
    }
    .ald-empty-title {
      font-size: 16px;
      font-weight: 600;
      color: #e2e8f0;
    }

    /* Toast */
    .ald-toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: #1e293b;
      color: #f8fafc;
      border: 1px solid rgba(0, 212, 255, 0.5);
      border-radius: 12px;
      padding: 12px 20px;
      font-size: 13px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      gap: 10px;
      z-index: 99999;
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ald-toast.show {
      transform: translateY(0);
      opacity: 1;
    }
  </style>

  <!-- Hero Header -->
  <div class="ald-hero">
    <div class="ald-hero-left">
      <div class="ald-hero-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <ellipse cx="12" cy="5" rx="9" ry="3"/>
          <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
          <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
        </svg>
      </div>
      <div class="ald-hero-title">
        <h2>
          <span>All Load Data Inspector</span>
          <span style="font-size:11px; font-weight:600; background:rgba(0,212,255,0.15); color:#38bdf8; padding:3px 10px; border-radius:12px; border:1px solid rgba(0,212,255,0.3);">
            JSON Monitor
          </span>
        </h2>
        <p>ตรวจสอบสถิติข้อมูลที่ถูกโหลดแล้วในตารางกลุ่ม 3 และ 4 แยกตาม VPS และวันที่</p>
      </div>
    </div>

    <div class="ald-hero-actions">
      <div style="font-size:11.5px; color:#94a3b8; text-align:right;">
        <span>อัปเดตล่าสุด:</span>
        <strong id="ald-last-updated" style="color:#ffffff; font-family:monospace;"><?php echo $lastModifiedTime ?: 'ยังไม่มีข้อมูล'; ?></strong>
      </div>
      <button class="ald-btn-sync" id="ald-btn-generate" title="รันคำสั่ง generate_load_data_json.php ผ่าน AJAX">
        <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
        <span id="ald-btn-text">Run generate_load_data_json.php</span>
      </button>
    </div>
  </div>

  <!-- Controls Container (VPS + Datetimepicker) -->
  <div class="ald-controls-card">
    
    <!-- 1. VPS Selection Container -->
    <div style="display:flex; flex-direction:column; gap:10px;">
      <div class="ald-controls-section-title">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2z"/></svg>
        <span>1. เลือกเซิร์ฟเวอร์ VPS (จาก vpsMaster)</span>
      </div>

      <div class="ald-vps-grid" id="ald-vps-container">
        <!-- All VPS Option -->
        <div class="ald-vps-chip active" data-vps="all">
          <div class="ald-vps-chip-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 2h-6c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm-1 10h-4v-7h4v7z"/></svg>
          </div>
          <div class="ald-vps-chip-body">
            <div class="ald-vps-chip-name">ทั้งหมด (All VPS)</div>
            <div class="ald-vps-chip-sub">แสดงทุกเซิร์ฟเวอร์</div>
          </div>
        </div>

        <?php foreach ($vpsList as $vps): 
          $code = $vps['vpsCode'] ?: $vps['id'];
          $name = $vps['vpsName'];
          $ip   = $vps['publicIP'] ?: ($vps['remark'] ?: 'Direct Feed');
        ?>
          <div class="ald-vps-chip" data-vps="<?php echo htmlspecialchars($code); ?>">
            <div class="ald-vps-chip-icon">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z"/></svg>
            </div>
            <div class="ald-vps-chip-body">
              <div class="ald-vps-chip-name" title="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></div>
              <div class="ald-vps-chip-sub">Code: <?php echo htmlspecialchars($code); ?> • <?php echo htmlspecialchars($ip); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 2. Datepicker Selection Row -->
    <div class="ald-date-row">
      <div style="display:flex; flex-direction:column; gap:6px;">
        <span style="font-size:11px; font-weight:700; color:#38bdf8; text-transform:uppercase; letter-spacing:0.06em;">2. เลือกวันที่ (Datetimepicker)</span>
        <div class="ald-date-input-group">
          <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5v-5z"/></svg>
          <input type="date" id="ald-date-picker" value="">
        </div>
      </div>

      <div style="display:flex; flex-direction:column; gap:6px; flex:1;">
        <span style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.06em;">วันที่มีข้อมูลล่าสุด (Quick Filter)</span>
        <div class="ald-quick-dates" id="ald-quick-dates-container">
          <button type="button" class="ald-quick-chip active" data-date="all">ทุกวันที่ (All Dates)</button>
          <!-- Filled dynamically with dates that have records -->
        </div>
      </div>
    </div>

  </div>

  <!-- Stats Grid -->
  <div class="ald-stats-grid">
    <div class="ald-stat-card">
      <div class="ald-stat-text">
        <p>เซิร์ฟเวอร์ที่เลือก</p>
        <h3 id="stat-selected-vps" style="font-size:16px;">ทั้งหมด (All VPS)</h3>
      </div>
      <div class="ald-stat-badge" style="background:rgba(0,117,255,0.15); color:#00d4ff;">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2z"/></svg>
      </div>
    </div>

    <div class="ald-stat-card">
      <div class="ald-stat-text">
        <p>วันที่ที่เลือก</p>
        <h3 id="stat-selected-date" style="font-size:16px;">ทุกวันที่</h3>
      </div>
      <div class="ald-stat-badge" style="background:rgba(168,85,247,0.15); color:#c084fc;">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/></svg>
      </div>
    </div>

    <div class="ald-stat-card">
      <div class="ald-stat-text">
        <p>ตารางที่มีข้อมูล</p>
        <h3 id="stat-table-count">0 ตาราง</h3>
      </div>
      <div class="ald-stat-badge" style="background:rgba(245,158,11,0.15); color:#f59e0b;">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
      </div>
    </div>

    <div class="ald-stat-card">
      <div class="ald-stat-text">
        <p>รวมจำนวนข้อมูลที่โหลด</p>
        <h3 id="stat-total-rows" style="color:#34d399;">0</h3>
      </div>
      <div class="ald-stat-badge" style="background:rgba(16,185,129,0.15); color:#34d399;">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>
      </div>
    </div>
  </div>

  <!-- Results Table Card -->
  <div class="ald-table-card">
    <div class="ald-table-header">
      <div class="ald-table-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="#00d4ff"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
        <span>รายการข้อมูลที่โหลดแล้ว (ตามตัวกรอง)</span>
        <span id="ald-match-count" style="font-size:12px; color:#94a3b8; font-weight:normal;">(0 รายการ)</span>
      </div>

      <div class="ald-table-search">
        <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        <input type="text" id="ald-table-search-input" placeholder="ค้นหาตาราง..." autocomplete="off">
      </div>
    </div>

    <div class="ald-table-wrap">
      <table class="ald-table" id="ald-main-table">
        <thead>
          <tr>
            <th style="width:50px;">ลำดับ</th>
            <th>กลุ่ม (Group)</th>
            <th>ชื่อตาราง (Table Name)</th>
            <th>เซิร์ฟเวอร์ (Server)</th>
            <th>วันที่ (Date)</th>
            <th>เวลาเริ่มต้น (Start Time)</th>
            <th>เวลาสิ้นสุด (Stop Time)</th>
            <th style="text-align:right;">จำนวนข้อมูล (Rows)</th>
            <th style="text-align:center;">สถานะ</th>
          </tr>
        </thead>
        <tbody id="ald-table-tbody">
          <!-- Populated by JavaScript -->
        </tbody>
      </table>

      <!-- Empty State -->
      <div class="ald-empty-state" id="ald-empty-state" style="display:none;">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div class="ald-empty-title">ไม่พบข้อมูลที่โหลดตามเงื่อนไขที่เลือก</div>
        <div id="ald-empty-msg" style="font-size:13px; max-width:480px; line-height:1.5;">
          ลองเปลี่ยนวันที่ หรือเลือกเซิร์ฟเวอร์ VPS อื่นเพื่อตรวจสอบข้อมูล
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div class="ald-toast" id="ald-toast">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="#34d399"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
    <span id="ald-toast-msg">อัปเดตข้อมูลสำเร็จ</span>
  </div>

  <!-- Fullscreen Wait Screen Modal -->
  <div class="ald-wait-overlay" id="ald-wait-overlay">
    <div class="ald-wait-modal">
      <!-- Header -->
      <div class="ald-wait-header">
        <div class="ald-wait-header-left">
          <div class="ald-wait-spinner-wrap">
            <div class="ald-wait-spinner" id="ald-wait-spinner"></div>
            <div class="ald-wait-spinner-icon">
              <svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
            </div>
          </div>
          <div>
            <h3 class="ald-wait-title" id="ald-wait-title">กำลังโหลดข้อมูลเข้า MySQL (Live AJAX Sync)</h3>
            <div class="ald-wait-meta">
              <span class="ald-wait-badge tbl" id="ald-wait-tbl-badge">Table: -</span>
              <span class="ald-wait-badge" id="ald-wait-vps-badge">VPS: -</span>
              <span class="ald-wait-badge date" id="ald-wait-date-badge">Date: -</span>
            </div>
          </div>
        </div>
        <button type="button" class="ald-wait-btn-close" id="ald-wait-btn-close" title="ปิดหน้าต่าง">&times;</button>
      </div>

      <!-- Stepper Tracker -->
      <div class="ald-wait-stepper">
        <div class="ald-step-item" id="ald-step-1">
          <span class="ald-step-dot">1</span>
          <span class="ald-step-text">เชื่อมต่อแหล่งข้อมูล</span>
        </div>
        <div class="ald-step-line"></div>
        <div class="ald-step-item" id="ald-step-2">
          <span class="ald-step-dot">2</span>
          <span class="ald-step-text">ดึง & จัดการข้อมูล</span>
        </div>
        <div class="ald-step-line"></div>
        <div class="ald-step-item" id="ald-step-3">
          <span class="ald-step-dot">3</span>
          <span class="ald-step-text">บันทึกเข้า MySQL</span>
        </div>
        <div class="ald-step-line"></div>
        <div class="ald-step-item" id="ald-step-4">
          <span class="ald-step-dot">4</span>
          <span class="ald-step-text">อัปเดตสถิติ JSON</span>
        </div>
      </div>

      <!-- Realtime Log Terminal Console -->
      <div class="ald-wait-console">
        <div class="ald-wait-console-header">
          <span>⚡ Live Console Logs</span>
          <span id="ald-wait-status-text" style="color:#38bdf8;">กำลังประมวลผล...</span>
        </div>
        <div class="ald-wait-log-list" id="ald-wait-log-list"></div>
      </div>

      <!-- Footer Action -->
      <div class="ald-wait-footer">
        <span class="ald-wait-note" id="ald-wait-note">กรุณารอสักครู่ ระบบกำลังโหลดข้อมูลและบันทึกลง MySQL...</span>
        <button type="button" class="ald-wait-btn-action" id="ald-wait-btn-done" style="display:none;">
          เสร็จสิ้น / ปิดหน้าต่าง
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';

  // Embed PHP Initial Data
  let rawGroupsData       = <?php echo json_encode($loadedData, JSON_UNESCAPED_UNICODE) ?: '[]'; ?>;
  const vpsNameMap        = <?php echo json_encode($vpsNameMap, JSON_UNESCAPED_UNICODE) ?: '{}'; ?>;
  const vpsMasterList     = <?php echo json_encode($vpsList, JSON_UNESCAPED_UNICODE) ?: '[]'; ?>;
  const derivAccountsList = <?php echo json_encode($derivAccounts, JSON_UNESCAPED_UNICODE) ?: '[]'; ?>;

  // UI Element Selectors
  const vpsChips      = document.querySelectorAll('.ald-vps-chip');
  const datePicker    = document.getElementById('ald-date-picker');
  const quickDatesWrap= document.getElementById('ald-quick-dates-container');
  const tableBody     = document.getElementById('ald-table-tbody');
  const emptyState    = document.getElementById('ald-empty-state');
  const emptyMsg      = document.getElementById('ald-empty-msg');
  const tableSearch   = document.getElementById('ald-table-search-input');
  const btnGenerate   = document.getElementById('ald-btn-generate');
  const btnText       = document.getElementById('ald-btn-text');
  const toastEl       = document.getElementById('ald-toast');
  const toastMsg      = document.getElementById('ald-toast-msg');
  const lastUpdatedEl = document.getElementById('ald-last-updated');

  // Stat Elements
  const statSelectedVps  = document.getElementById('stat-selected-vps');
  const statSelectedDate = document.getElementById('stat-selected-date');
  const statTableCount   = document.getElementById('stat-table-count');
  const statTotalRows    = document.getElementById('stat-total-rows');
  const matchCountEl     = document.getElementById('ald-match-count');

  // Wait Screen Elements
  const waitOverlay    = document.getElementById('ald-wait-overlay');
  const waitTitle      = document.getElementById('ald-wait-title');
  const waitTblBadge   = document.getElementById('ald-wait-tbl-badge');
  const waitVpsBadge   = document.getElementById('ald-wait-vps-badge');
  const waitDateBadge  = document.getElementById('ald-wait-date-badge');
  const waitSpinner    = document.getElementById('ald-wait-spinner');
  const waitCloseBtn   = document.getElementById('ald-wait-btn-close');
  const waitStatusText = document.getElementById('ald-wait-status-text');
  const waitLogList    = document.getElementById('ald-wait-log-list');
  const waitNote       = document.getElementById('ald-wait-note');
  const waitBtnDone    = document.getElementById('ald-wait-btn-done');

  // Filter State
  let currentVps  = 'all';
  let currentDate = 'all';
  let searchKeyword = '';

  function showToast(msg, isSuccess = true) {
    if (!toastEl) return;
    toastMsg.textContent = msg;
    toastEl.style.borderColor = isSuccess ? 'rgba(52, 211, 153, 0.6)' : 'rgba(239, 68, 68, 0.6)';
    toastEl.classList.add('show');
    setTimeout(() => toastEl.classList.remove('show'), 3000);
  }

  // Wait Screen UI Control Functions
  function appendWaitLog(msg, type = 'info') {
    if (!waitLogList) return;
    const time = new Date().toLocaleTimeString();
    const entry = document.createElement('div');
    entry.className = `ald-wait-log-entry ${type}`;
    let icon = 'ℹ️';
    if (type === 'success') icon = '✅';
    else if (type === 'warn') icon = '⚠️';
    else if (type === 'error') icon = '❌';
    entry.innerHTML = `<span style="opacity:0.6;">[${time}]</span> <span>${icon} ${msg}</span>`;
    waitLogList.appendChild(entry);
    waitLogList.scrollTop = waitLogList.scrollHeight;
  }

  function setWaitStep(stepNumber, status = 'active') {
    for (let i = 1; i <= 4; i++) {
      const stepEl = document.getElementById(`ald-step-${i}`);
      if (!stepEl) continue;
      if (i < stepNumber) {
        stepEl.className = 'ald-step-item done';
        stepEl.querySelector('.ald-step-dot').innerHTML = '✓';
      } else if (i === stepNumber) {
        stepEl.className = `ald-step-item ${status}`;
        stepEl.querySelector('.ald-step-dot').innerHTML = (status === 'done') ? '✓' : i;
      } else {
        stepEl.className = 'ald-step-item';
        stepEl.querySelector('.ald-step-dot').innerHTML = i;
      }
    }
  }

  function openWaitScreen(tableName, serverCode, dateVal) {
    const vps = findVps(serverCode);
    const vName = vps ? vps.vpsName : (vpsNameMap[serverCode] || `Server ${serverCode}`);

    waitTblBadge.textContent = `Table: ${tableName}`;
    waitVpsBadge.textContent = `VPS: [${serverCode}] ${vName}`;
    waitDateBadge.textContent = `Date: ${dateVal}`;

    waitTitle.textContent = `กำลังโหลดข้อมูล ${tableName} เข้า MySQL`;
    waitStatusText.textContent = 'เริ่มต้นกระบวนการ...';
    waitStatusText.style.color = '#38bdf8';
    waitNote.textContent = 'กรุณารอสักครู่ ระบบกำลังโหลดข้อมูลจาก VPS / Deriv เข้าสู่ MySQL...';

    waitSpinner.className = 'ald-wait-spinner';
    waitBtnDone.style.display = 'none';
    waitCloseBtn.style.display = 'none';
    waitLogList.innerHTML = '';
    setWaitStep(1, 'active');

    waitOverlay.style.display = 'flex';
  }

  function finishWaitScreen(isSuccess, message) {
    if (isSuccess) {
      setWaitStep(4, 'done');
      waitSpinner.className = 'ald-wait-spinner done';
      waitStatusText.textContent = '✅ ทำงานสำเร็จสมบูรณ์!';
      waitStatusText.style.color = '#34d399';
      waitNote.textContent = message || 'โหลดข้อมูลเข้า MySQL และอัปเดตสถิติ JSON เรียบร้อยแล้ว';
    } else {
      waitSpinner.className = 'ald-wait-spinner done';
      waitStatusText.textContent = '❌ เกิดข้อผิดพลาด';
      waitStatusText.style.color = '#f87171';
      waitNote.textContent = message || 'การโหลดข้อมูลหยุดชะงักเนื่องจากมีข้อผิดพลาด';
    }
    waitBtnDone.style.display = 'inline-block';
    waitCloseBtn.style.display = 'flex';
  }

  function closeWaitScreen() {
    waitOverlay.style.display = 'none';
  }

  if (waitCloseBtn) waitCloseBtn.addEventListener('click', closeWaitScreen);
  if (waitBtnDone) waitBtnDone.addEventListener('click', closeWaitScreen);

  // Helper Functions
  function findVps(serverCode) {
    if (!serverCode || serverCode === 'all') return vpsMasterList[0] || null;
    return vpsMasterList.find(v => String(v.vpsCode) === String(serverCode) || String(v.id) === String(serverCode)) || vpsMasterList[0] || null;
  }

  function getVpsExportUrl(vps, date) {
    if (!vps) return '';
    let base = vps.url;
    if (!base && vps.publicIP) {
      base = vps.publicIP + (vps.portno ? ':' + vps.portno : '');
    }
    if (!base) base = 'https://pkderiv.online';
    base = base.replace(/\/+$/, '');
    if (!/^https?:\/\//i.test(base)) {
      if (base.includes('pkderiv.online') || base.includes('gpkderiv.shop') || base.endsWith('.shop') || base.endsWith('.online')) {
        base = 'https://' + base;
      } else {
        base = 'http://' + base;
      }
    }
    return `${base}/api/export_trade_data?startdatetime=${date}`;
  }

  function findDerivAccount(vps) {
    if (vps && vps.DerivAccountID) {
      const found = derivAccountsList.find(a => String(a.id) === String(vps.DerivAccountID));
      if (found) return found;
    }
    return derivAccountsList[0] || null;
  }

  async function triggerRegenerateJson() {
    setWaitStep(4, 'active');
    appendWaitLog('🔄 กำลังรัน generate_loaded_data_json.php เพื่ออัปเดตสถิติ...', 'info');
    try {
      const res = await fetch('../generate_loaded_data_json.php?_t=' + Date.now(), {
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();
      if (json.success && Array.isArray(json.data)) {
        rawGroupsData = json.data;
        if (json.generated_at && lastUpdatedEl) {
          lastUpdatedEl.textContent = json.generated_at;
        }
        updateQuickDates();
        renderTable();
        appendWaitLog('✅ อัปเดตสถิติ JSON และรีเฟรชตารางหน้าเว็บสำเร็จ!', 'success');
      }
    } catch (err) {
      appendWaitLog(`⚠️ ไม่สามารถอัปเดตสถิติ JSON อัตโนมัติ: ${err.message}`, 'warn');
    }
  }

  // ==========================================
  // AJAX DATA LOADERS FOR GROUP 3 & GROUP 4
  // ==========================================

  // 1. VPS Trade Data & Trade Head Loader (Group 4)
  async function loadVpsTradeDataAjax(vps, targetDate, serverCode, targetTable) {
    appendWaitLog(`🌐 เริ่มต้นโหลดข้อมูล ${targetTable} จาก VPS [${vps.vpsCode}] ${vps.vpsName}...`, 'info');
    setWaitStep(1, 'done');
    setWaitStep(2, 'active');

    const exportUrl = getVpsExportUrl(vps, targetDate);
    appendWaitLog(`📡 ร้องขอ Export Archive: ${exportUrl}`, 'info');

    let arrayBuffer = null;
    try {
      // Direct fetch attempt
      const res = await fetch(exportUrl);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      arrayBuffer = await res.arrayBuffer();
      appendWaitLog(`✅ ดาวน์โหลดผ่าน Direct Connection สำเร็จ (${(arrayBuffer.byteLength/1024).toFixed(1)} KB)`, 'success');
    } catch (directErr) {
      appendWaitLog(`ℹ️ Direct connection ไม่สามารถใช้งานได้ (${directErr.message}) -> สลับไปใช้ PHP Proxy...`, 'info');
      const proxyRes = await fetch('../php/save_gcp_trades.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'download_and_save',
          exportUrl: exportUrl,
          serverCode: serverCode,
          date: targetDate
        })
      });
      if (!proxyRes.ok) {
        const txt = await proxyRes.text();
        throw new Error(`PHP Proxy Error: ${txt}`);
      }
      const proxyJson = await proxyRes.json();
      if (!proxyJson.success || !proxyJson.zipBase64) {
        throw new Error(proxyJson.error || proxyJson.message || 'ไม่สามารถดาวน์โหลด ZIP จาก VPS ได้');
      }
      appendWaitLog(`✅ ดาวน์โหลดผ่าน PHP Backend Proxy สำเร็จ (${(proxyJson.zipSize/1024).toFixed(1)} KB)`, 'success');
      const binStr = atob(proxyJson.zipBase64);
      const u8 = new Uint8Array(binStr.length);
      for (let i = 0; i < binStr.length; i++) {
        u8[i] = binStr.charCodeAt(i);
      }
      arrayBuffer = u8.buffer;
    }

    if (typeof JSZip === 'undefined') {
      throw new Error('ไม่พบ JSZip Library ในหน้าเว็บ กรุณารีเฟรชหน้านี้');
    }

    appendWaitLog('📂 กำลังแตกไฟล์ ZIP ด้วย JSZip ใน Browser...', 'info');
    const zip = await JSZip.loadAsync(arrayBuffer);
    const files = Object.keys(zip.files);
    appendWaitLog(`📂 แตกไฟล์ ZIP เรียบร้อย พบ ${files.length} รายการ`, 'success');

    let allTrades = [];
    let tradeHeadData = null;

    for (const [filename, zipEntry] of Object.entries(zip.files)) {
      if (zipEntry.dir) continue;
      const content = await zipEntry.async('text');
      let json;
      try { json = JSON.parse(content); } catch (e) { continue; }

      const cleanPath = filename.replace(/^\.\//, '');
      const parts = cleanPath.split('/');

      if (parts.length === 1 && parts[0] === 'tradeHead.json') {
        tradeHeadData = json;
      } else if (parts.length === 2) {
        const [assetCode, jsonFile] = parts;
        if (jsonFile === 'trades.json' && Array.isArray(json)) {
          json.forEach(tr => {
            if (!tr.assetCode) tr.assetCode = assetCode;
            if (!tr.symbol) tr.symbol = assetCode;
            allTrades.push(tr);
          });
        } else if (jsonFile === 'tradeHead.json' && !tradeHeadData) {
          tradeHeadData = json;
        }
      }
    }

    setWaitStep(2, 'done');
    setWaitStep(3, 'active');

    let savedSummary = [];

    // Save tradeHead if present
    if (tradeHeadData && (targetTable === 'tradehead' || targetTable === 'vpstradedata')) {
      appendWaitLog(`💾 กำลังบันทึกข้อมูล tradeHead (${Array.isArray(tradeHeadData) ? tradeHeadData.length : 1} รอบ)...`, 'info');
      const thRes = await fetch('../php/save_trade_rounds.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          serverCode: serverCode,
          tradeHead: Array.isArray(tradeHeadData) ? tradeHeadData : [tradeHeadData]
        })
      });
      const thJson = await thRes.json();
      if (thJson.success) {
        appendWaitLog(`✅ บันทึก tradeHead เรียบร้อย (${thJson.savedRows || 0} แถว)`, 'success');
        savedSummary.push(`tradeHead: ${thJson.savedRows || 0} รายการ`);
      } else {
        appendWaitLog(`⚠️ บันทึก tradeHead มีข้อผิดพลาด: ${thJson.error || ''}`, 'warn');
      }
    }

    // Save vpsTradeData if present
    if (allTrades.length > 0 && (targetTable === 'vpstradedata' || targetTable === 'tradehead')) {
      appendWaitLog(`💾 กำลังบันทึก vpsTradeData รวม ${allTrades.length} records ลง MySQL...`, 'info');
      const gcpRes = await fetch('../php/save_gcp_trades.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          serverCode: serverCode,
          trades: allTrades
        })
      });
      const gcpJson = await gcpRes.json();
      if (gcpJson.success) {
        appendWaitLog(`🎉 บันทึก vpsTradeData สำเร็จรวม ${gcpJson.totalSaved || allTrades.length} รายการ`, 'success');
        savedSummary.push(`vpsTradeData: ${gcpJson.totalSaved || allTrades.length} รายการ`);
      } else {
        throw new Error(gcpJson.error || 'บันทึก vpsTradeData ล้มเหลว');
      }
    }

    if (savedSummary.length === 0) {
      appendWaitLog('ℹ️ ไม่พบข้อมูล Trade ในไฟล์ ZIP สำหรับวันนี้', 'warn');
    }

    await triggerRegenerateJson();
    return `บันทึกข้อมูลสำเร็จ (${savedSummary.join(', ') || '0 รายการ'})`;
  }

  // 2. Deriv Trade History Loader (Group 4)
  async function loadDerivTradeHistoryAjax(vps, targetDate, serverCode) {
    appendWaitLog(`🔑 กำลังค้นหาข้อมูลบัญชี Deriv สำหรับ VPS ${serverCode}...`, 'info');
    setWaitStep(1, 'active');

    const acc = findDerivAccount(vps);
    if (!acc || !acc.appId) {
      throw new Error(`ไม่พบการตั้งค่าบัญชี Deriv (App ID / Token) สำหรับ VPS ${serverCode}`);
    }

    appendWaitLog(`👤 พบบัญชี Deriv: [${acc.accountId || 'ID'}] ${acc.appName || acc.email} (AppID: ${acc.appId})`, 'info');

    const startEpoch = Math.floor(new Date(`${targetDate}T00:00:00+07:00`).getTime() / 1000);
    const endEpoch = Math.floor(new Date(`${targetDate}T23:59:59+07:00`).getTime() / 1000);

    let wsUrl = null;
    try {
      appendWaitLog(`🔑 กำลังขอสิทธิ์ OTP สำหรับบัญชี [${acc.accountId || acc.id}]...`, 'info');
      const accParam = acc.id ? `&id=${acc.id}` : (acc.accountId ? `&accountId=${acc.accountId}` : '');
      const otpApiRes = await fetch(`../php/api_deriv_account.php?action=get_ws_url${accParam}`);
      if (otpApiRes.ok) {
        const otpApiJson = await otpApiRes.json();
        if (otpApiJson.success && otpApiJson.wsUrl) {
          wsUrl = otpApiJson.wsUrl;
          appendWaitLog('✅ ได้รับ OTP WebSocket URL สำเร็จ!', 'success');
        } else if (otpApiJson.error) {
          appendWaitLog(`ℹ️ OTP helper: ${otpApiJson.error}`, 'warn');
        }
      }
    } catch (e) {
      appendWaitLog(`ℹ️ ขอ OTP ผ่านระบบหลักไม่สำเร็จ (${e.message}) -> ลอง Direct REST...`, 'info');
    }

    if (!wsUrl && acc.accountId && acc.token) {
      try {
        appendWaitLog(`🔑 ขอสิทธิ์ OTP ผ่าน REST API ตรง สำหรับ Account ${acc.accountId}...`, 'info');
        const otpRes = await fetch(`https://api.derivws.com/trading/v1/options/accounts/${acc.accountId}/otp`, {
          method: 'POST',
          headers: {
            'Deriv-App-ID': acc.appId,
            'Authorization': 'Bearer ' + acc.token
          }
        });
        const otpJson = await otpRes.json();
        if (otpRes.ok && otpJson.data && otpJson.data.url) {
          wsUrl = otpJson.data.url;
          appendWaitLog('✅ ได้รับ OTP URL สำหรับเชื่อมต่อ WebSocket สำเร็จ', 'success');
        }
      } catch (otpErr) {
        appendWaitLog(`⚠️ ขอ OTP ตรงไม่สำเร็จ: ${otpErr.message}`, 'warn');
      }
    }

    if (!wsUrl) {
      throw new Error(`ไม่สามารถขอสิทธิ์ OTP สำหรับบัญชี [${acc.accountId || acc.appName}] ได้ — กรุณาตรวจสอบว่า Token ในหน้าจัดการบัญชี Deriv ยังไม่หมดอายุ`);
    }

    setWaitStep(1, 'done');
    setWaitStep(2, 'active');

    const fetchedTrades = await new Promise((resolve, reject) => {
      const trades = [];
      let offset = 0;
      const LIMIT = 100;
      const ws = new WebSocket(wsUrl);

      const timeoutId = setTimeout(() => {
        try { ws.close(); } catch (e) {}
        reject(new Error('WebSocket connection timeout (30s)'));
      }, 30000);

      function sendProfitTableReq() {
        appendWaitLog(`📤 ร้องขอ profit_table (offset: ${offset}, limit: ${LIMIT})...`, 'info');
        ws.send(JSON.stringify({
          profit_table: 1,
          description: 1,
          limit: LIMIT,
          offset: offset,
          date_from: startEpoch,
          date_to: endEpoch,
          sort: 'ASC'
        }));
      }

      ws.onopen = function() {
        appendWaitLog('🔗 WebSocket เชื่อมต่อไปยัง Deriv.com สำเร็จ!', 'success');
        if (wsUrl.includes('token=') || wsUrl.includes('otp=')) {
          sendProfitTableReq();
        } else if (acc.token) {
          appendWaitLog('🔐 ส่งคำขอ Authorize ด้วย Token...', 'info');
          ws.send(JSON.stringify({ authorize: acc.token }));
        } else {
          sendProfitTableReq();
        }
      };

      ws.onmessage = function(event) {
        let data;
        try { data = JSON.parse(event.data); } catch (e) { return; }

        if (data.error) {
          clearTimeout(timeoutId);
          ws.close();
          reject(new Error(`Deriv API Error: ${data.error.message || data.error.code || 'Unknown'}`));
          return;
        }

        if (data.msg_type === 'authorize') {
          appendWaitLog('✅ Authorize สำเร็จ!', 'success');
          sendProfitTableReq();
        } else if (data.msg_type === 'profit_table') {
          const pt = data.profit_table;
          const txns = (pt && pt.transactions) ? pt.transactions : [];
          txns.forEach(t => trades.push(t));
          appendWaitLog(`📥 ได้รับ ${txns.length} รายการ (ยอดสะสม: ${trades.length} trades)...`, 'info');

          if (txns.length >= LIMIT) {
            offset += LIMIT;
            sendProfitTableReq();
          } else {
            clearTimeout(timeoutId);
            ws.close();
            resolve(trades);
          }
        }
      };

      ws.onerror = function(err) {
        clearTimeout(timeoutId);
        reject(new Error('WebSocket connection error'));
      };
    });

    setWaitStep(2, 'done');
    setWaitStep(3, 'active');

    appendWaitLog(`🎉 ดึงข้อมูลจาก Deriv สำเร็จ รวม ${fetchedTrades.length} รายการ`, 'success');

    if (fetchedTrades.length > 0) {
      appendWaitLog(`💾 กำลังบันทึกประวัติการเทรดลง MySQL (derivTradeHistory)...`, 'info');
      const saveRes = await fetch('../php/save_trade_history.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          serverCode: serverCode,
          trades: fetchedTrades
        })
      });
      const saveJson = await saveRes.json();
      if (saveJson.success) {
        appendWaitLog(`🎉 [MySQL] บันทึกประวัติการเทรด ${saveJson.count || fetchedTrades.length} รายการเรียบร้อย!`, 'success');
      } else {
        throw new Error(saveJson.error || 'ไม่สามารถบันทึกลงฐานข้อมูลได้');
      }
    } else {
      appendWaitLog('ℹ️ ไม่พบรายการประวัติการเทรดใน Deriv สำหรับวันนี้', 'warn');
    }

    await triggerRegenerateJson();
    return `ดึงและบันทึกประวัติการเทรด Deriv สำเร็จ (${fetchedTrades.length} รายการ)`;
  }

  // 3. Full Analysis Data Loader (Group 3)
  async function loadFullAnalysisDataAjax(vps, targetDate, serverCode) {
    appendWaitLog(`🌐 กำลังเตรียมส่งคำขอ FullAnalysisData ไปยัง VPS [${vps.vpsCode}]...`, 'info');
    setWaitStep(1, 'active');

    let vpsBase = vps.url;
    if (!vpsBase && vps.publicIP) {
      vpsBase = vps.publicIP + (vps.portno ? ':' + vps.portno : '');
    }
    if (!vpsBase) vpsBase = 'https://pkderiv.online';
    vpsBase = vpsBase.replace(/\/+$/, '');
    if (!/^https?:\/\//i.test(vpsBase)) {
      vpsBase = 'http://' + vpsBase;
    }

    // Discover assets from database or fallback standard
    let assets = [];
    try {
      appendWaitLog(`🔍 ตรวจสอบรายการสินทรัพย์ที่มีการเทรดในวันที่ ${targetDate}...`, 'info');
      const aRes = await fetch(`../php/api_deriv_trade_history.php?action=get_assets&serverCode=${serverCode}&date=${targetDate}`);
      if (aRes.ok) {
        const aJson = await aRes.json();
        if (aJson && Array.isArray(aJson.assets) && aJson.assets.length > 0) {
          assets = aJson.assets;
          appendWaitLog(`🎯 พบ ${assets.length} สินทรัพย์จากประวัติการเทรด: ${assets.join(', ')}`, 'info');
        }
      }
    } catch (e) {}

    if (assets.length === 0) {
      assets = ['1HZ10V', '1HZ25V', '1HZ50V', '1HZ75V', '1HZ100V', 'R_10', 'R_25', 'R_50', 'R_75', 'R_100'];
      appendWaitLog(`ℹ️ ใช้รายการสินทรัพย์มาตรฐาน 10 ตัว: ${assets.join(', ')}`, 'info');
    }

    setWaitStep(1, 'done');
    setWaitStep(2, 'active');

    const startDt = `${targetDate} 00:00:00`;
    const endDt   = `${targetDate} 23:59:59`;
    let allCandles = [];

    for (let i = 0; i < assets.length; i++) {
      const sym = assets[i];
      appendWaitLog(`📡 [${i + 1}/${assets.length}] ดึง FullAnalysis สำหรับ ${sym}...`, 'info');
      const queryStr = `assetcode=${encodeURIComponent(sym)}&startdatetime=${encodeURIComponent(startDt)}&enddatetime=${encodeURIComponent(endDt)}&timeframe=1M`;
      const directUrl = `${vpsBase}/getFullAnalysisData?${queryStr}`;
      const proxyUrl  = `../php/proxy_full_analysis.php?vps_url=${encodeURIComponent(vpsBase)}&${queryStr}`;

      let dataJson = null;
      try {
        const res = await fetch(directUrl, { signal: AbortSignal.timeout(12000) });
        if (res.ok) dataJson = await res.json();
        else throw new Error(`HTTP ${res.status}`);
      } catch (dErr) {
        try {
          const pRes = await fetch(proxyUrl);
          if (pRes.ok) dataJson = await pRes.json();
        } catch (pErr) {}
      }

      if (dataJson) {
        let candleList = [];
        if (Array.isArray(dataJson)) candleList = dataJson;
        else if (dataJson.candles && Array.isArray(dataJson.candles)) candleList = dataJson.candles;
        else if (dataJson.data && Array.isArray(dataJson.data)) candleList = dataJson.data;

        if (candleList.length > 0) {
          candleList.forEach(c => {
            if (!c.assetCode) c.assetCode = sym;
            allCandles.push(c);
          });
          appendWaitLog(`✅ ได้รับข้อมูล ${sym} จำนวน ${candleList.length.toLocaleString()} แท่ง`, 'success');
        } else {
          appendWaitLog(`⚠️ ${sym}: ไม่พบข้อมูลแท่งเทียนใน VPS`, 'warn');
        }
      } else {
        appendWaitLog(`⚠️ ไม่สามารถติดต่อ VPS เพื่อดึงข้อมูล ${sym} ได้`, 'warn');
      }
    }

    setWaitStep(2, 'done');
    setWaitStep(3, 'active');

    if (allCandles.length > 0) {
      appendWaitLog(`💾 กำลังบันทึก full_analysis_data รวม ${allCandles.length.toLocaleString()} แท่งเทียนลง MySQL...`, 'info');
      const saveRes = await fetch('../php/save_full_analysis.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          serverCode: String(serverCode),
          candles: allCandles
        })
      });
      const saveJson = await saveRes.json();
      if (saveJson.success) {
        appendWaitLog(`🎉 บันทึกลง MySQL สำเร็จ (${saveJson.count || allCandles.length} แท่งเทียน)`, 'success');
      } else {
        throw new Error(saveJson.error || 'บันทึกลง full_analysis_data ล้มเหลว');
      }
    } else {
      appendWaitLog('ℹ️ ไม่พบแท่งเทียนที่วิเคราะห์ได้จาก VPS สำหรับวันนี้', 'warn');
    }

    await triggerRegenerateJson();
    return `บันทึก full_analysis_data สำเร็จ (${allCandles.length} แท่งเทียน)`;
  }

  // 4. Market Data (Candles & Ticks) Loader from Deriv.com (Group 3)
  async function loadMarketDataAjax(tableName, vps, targetDate, serverCode) {
    const isCandles = (tableName === 'candles');
    appendWaitLog(`🌐 กำลังเชื่อมต่อ Deriv.com เพื่อโหลด ${tableName}...`, 'info');
    setWaitStep(1, 'active');

    const acc = findDerivAccount(vps);
    let wsUrl = null;

    // ขอ OTP ผ่าน PHP API ก่อน (ปลอดภัย ไม่ติด CORS)
    try {
      appendWaitLog('🔑 กำลังขอสิทธิ์ OTP สำหรับเชื่อมต่อ Deriv API...', 'info');
      const accParam = (acc && acc.id) ? `&id=${acc.id}` : (acc && acc.accountId ? `&accountId=${acc.accountId}` : '');
      const otpApiRes = await fetch(`../php/api_deriv_account.php?action=get_ws_url${accParam}`);
      if (otpApiRes.ok) {
        const otpApiJson = await otpApiRes.json();
        if (otpApiJson.success && otpApiJson.wsUrl) {
          wsUrl = otpApiJson.wsUrl;
          appendWaitLog(`✅ ได้รับ OTP WebSocket URL สำเร็จ (Account: ${otpApiJson.accountId || 'OTP'})`, 'success');
        }
      }
    } catch (e) {
      appendWaitLog(`ℹ️ ขอ OTP ผ่านระบบหลักไม่สำเร็จ (${e.message})`, 'info');
    }

    if (!wsUrl && acc && acc.accountId && acc.token) {
      try {
        const otpRes = await fetch(`https://api.derivws.com/trading/v1/options/accounts/${acc.accountId}/otp`, {
          method: 'POST',
          headers: {
            'Deriv-App-ID': acc.appId,
            'Authorization': 'Bearer ' + acc.token
          }
        });
        const otpJson = await otpRes.json();
        if (otpRes.ok && otpJson.data && otpJson.data.url) {
          wsUrl = otpJson.data.url;
          appendWaitLog('✅ ได้รับ OTP URL ผ่าน Direct REST สำเร็จ', 'success');
        }
      } catch (e) {}
    }

    if (!wsUrl) {
      wsUrl = 'wss://api.derivws.com/trading/v1/options/ws/public';
      appendWaitLog('🌐 ใช้ Public WebSocket สำรอง: ' + wsUrl, 'info');
    }

    // Target epoch range
    const startEpoch = Math.floor(new Date(`${targetDate}T00:00:00+07:00`).getTime() / 1000);
    const endEpoch   = Math.floor(new Date(`${targetDate}T23:59:59+07:00`).getTime() / 1000);

    // Assets to fetch
    let assets = ['1HZ10V', '1HZ25V', '1HZ50V', '1HZ75V', '1HZ100V'];
    try {
      const aRes = await fetch(`../php/api_deriv_trade_history.php?action=get_assets&serverCode=${serverCode}&date=${targetDate}`);
      if (aRes.ok) {
        const aJson = await aRes.json();
        if (aJson && Array.isArray(aJson.assets) && aJson.assets.length > 0) {
          assets = aJson.assets;
        }
      }
    } catch (e) {}

    appendWaitLog(`🎯 รายการสินทรัพย์เป้าหมาย (${assets.length} รายการ): ${assets.join(', ')}`, 'info');
    setWaitStep(1, 'done');
    setWaitStep(2, 'active');

    const ws = new WebSocket(wsUrl);
    let totalLoaded = 0;

    await new Promise((resolve, reject) => {
      const timeoutId = setTimeout(() => {
        try { ws.close(); } catch (e) {}
        resolve();
      }, 40000);

      ws.onopen = async function() {
        appendWaitLog('🔗 เชื่อมต่อ WebSocket Deriv สำเร็จ! เริ่มดึง Market Data...', 'success');

        for (let i = 0; i < assets.length; i++) {
          const sym = assets[i];
          appendWaitLog(`📥 [${i + 1}/${assets.length}] ขอ ${tableName} สำหรับ ${sym}...`, 'info');

          await new Promise((resAsset) => {
            const assetHandler = async function(evt) {
              let d;
              try { d = JSON.parse(evt.data); } catch (e) { return; }

              if (d.msg_type === 'candles' && isCandles) {
                const cList = (d.candles || []).map(c => ({
                  time: c.epoch,
                  open: c.open,
                  high: c.high,
                  low: c.low,
                  close: c.close
                }));
                if (cList.length > 0) {
                  totalLoaded += cList.length;
                  appendWaitLog(`✅ ได้รับ ${sym} ${cList.length} candles -> บันทึกลง MySQL...`, 'success');
                  await fetch('../php/save_market_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                      symbol: sym,
                      serverCode: serverCode,
                      granularity: 60,
                      candles: cList
                    })
                  });
                }
                ws.removeEventListener('message', assetHandler);
                resAsset();
              } else if ((d.msg_type === 'history' || d.msg_type === 'ticks') && !isCandles) {
                const h = d.history;
                const tList = [];
                if (h && h.times && h.prices) {
                  for (let k = 0; k < h.times.length; k++) {
                    tList.push({ time: h.times[k], price: h.prices[k] });
                  }
                }
                if (tList.length > 0) {
                  totalLoaded += tList.length;
                  appendWaitLog(`✅ ได้รับ ${sym} ${tList.length} ticks -> บันทึกลง MySQL...`, 'success');
                  await fetch('../php/save_market_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                      symbol: sym,
                      serverCode: serverCode,
                      ticks: tList
                    })
                  });
                }
                ws.removeEventListener('message', assetHandler);
                resAsset();
              } else if (d.error) {
                appendWaitLog(`⚠️ ${sym}: ${d.error.message || 'Data error'}`, 'warn');
                ws.removeEventListener('message', assetHandler);
                resAsset();
              }
            };

            ws.addEventListener('message', assetHandler);

            const isToday = (new Date().toISOString().slice(0, 10) === targetDate);
            const reqEnd = isToday ? 'latest' : endEpoch;

            if (isCandles) {
              ws.send(JSON.stringify({
                ticks_history: sym,
                adjust_start_time: 1,
                end: reqEnd,
                start: startEpoch,
                style: 'candles',
                granularity: 60
              }));
            } else {
              ws.send(JSON.stringify({
                ticks_history: sym,
                adjust_start_time: 1,
                count: 5000,
                end: reqEnd,
                start: startEpoch,
                style: 'ticks'
              }));
            }

            setTimeout(() => {
              ws.removeEventListener('message', assetHandler);
              resAsset();
            }, 6000);
          });
        }

        clearTimeout(timeoutId);
        try { ws.close(); } catch (e) {}
        resolve();
      };

      ws.onerror = function(err) {
        clearTimeout(timeoutId);
        reject(new Error('WebSocket connection error'));
      };
    });

    setWaitStep(2, 'done');
    setWaitStep(3, 'done');

    await triggerRegenerateJson();
    return `บันทึกข้อมูล ${tableName} เรียบร้อย รวม ${totalLoaded.toLocaleString()} รายการ`;
  }

  // 5. Candle Analysis Loader (Group 3)
  async function loadCandleAnalysisAjax(vps, targetDate, serverCode) {
    appendWaitLog(`🌐 กำลังโหลดผลการวิเคราะห์ candle_analysis สำหรับวันที่ ${targetDate}...`, 'info');
    setWaitStep(1, 'active');
    const resMsg = await loadFullAnalysisDataAjax(vps, targetDate, serverCode);
    return resMsg;
  }

  // Master Dispatcher for AJAX Loading
  async function executeAjaxLoad(tableName, serverCode, rowDate, loadTitle) {
    let targetDate = (rowDate && rowDate !== '-') ? rowDate : (currentDate !== 'all' ? currentDate : (datePicker?.value || ''));
    if (!targetDate) {
      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      targetDate = `${y}-${m}-${d}`;
    }

    const sCode = (serverCode && serverCode !== '-') ? serverCode : (currentVps !== 'all' ? currentVps : '1');
    const vps = findVps(sCode);

    openWaitScreen(tableName, sCode, targetDate);

    try {
      let resultMsg = '';
      const tLower = tableName.toLowerCase();

      if (tLower === 'vpstradedata' || tLower === 'tradehead') {
        resultMsg = await loadVpsTradeDataAjax(vps, targetDate, sCode, tLower);
      } else if (tLower === 'derivtradehistory' || tLower === 'trade_history') {
        resultMsg = await loadDerivTradeHistoryAjax(vps, targetDate, sCode);
      } else if (tLower === 'full_analysis_data') {
        resultMsg = await loadFullAnalysisDataAjax(vps, targetDate, sCode);
      } else if (tLower === 'candles' || tLower === 'ticks') {
        resultMsg = await loadMarketDataAjax(tLower, vps, targetDate, sCode);
      } else if (tLower === 'candle_analysis') {
        resultMsg = await loadCandleAnalysisAjax(vps, targetDate, sCode);
      } else {
        throw new Error(`ไม่รองรับตาราง ${tableName}`);
      }

      finishWaitScreen(true, resultMsg);
      showToast(`✅ ${resultMsg}`);
    } catch (err) {
      appendWaitLog(`❌ เกิดข้อผิดพลาด: ${err.message}`, 'error');
      finishWaitScreen(false, `ข้อผิดพลาด: ${err.message}`);
      showToast(`❌ เกิดข้อผิดพลาด: ${err.message}`, false);
    }
  }

  // Flatten all data rows for easy filtering
  function getFlattenedRows() {
    const rows = [];
    if (!Array.isArray(rawGroupsData)) return rows;

    rawGroupsData.forEach(group => {
      const gCode = group.groupcode;
      const gName = group.groupname;
      const tables = group.tablelist || [];

      tables.forEach(tbl => {
        const tName = tbl.tablename;
        const dataDateList = tbl.dataDate || [];

        dataDateList.forEach(item => {
          const sCode = item.serverCode !== null && item.serverCode !== undefined ? String(item.serverCode) : null;
          const startDt = item.startdatetime || '';
          const stopDt = item.stopdatetime || '';
          const count = parseInt(item['จำนวนข้อมูล'] || 0, 10);
          
          const datePart = startDt.split(' ')[0] || '';

          rows.push({
            groupCode: gCode,
            groupName: gName,
            tableName: tName,
            serverCode: sCode,
            date: datePart,
            startdatetime: startDt,
            stopdatetime: stopDt,
            numData: count
          });
        });
      });
    });

    return rows;
  }

  // Populate Quick Date Chips from actual data
  function updateQuickDates() {
    if (!quickDatesWrap) return;

    const allRows = getFlattenedRows();
    const datesSet = new Set();

    allRows.forEach(r => {
      if (r.date && (currentVps === 'all' || r.serverCode === currentVps)) {
        datesSet.add(r.date);
      }
    });

    const sortedDates = Array.from(datesSet).sort().reverse();

    quickDatesWrap.innerHTML = `
      <button type="button" class="ald-quick-chip ${currentDate === 'all' ? 'active' : ''}" data-date="all">
        ทุกวันที่ (All Dates)
      </button>
    `;

    sortedDates.slice(0, 7).forEach(d => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = `ald-quick-chip ${currentDate === d ? 'active' : ''}`;
      btn.dataset.date = d;
      btn.textContent = d;
      quickDatesWrap.appendChild(btn);
    });

    quickDatesWrap.querySelectorAll('.ald-quick-chip').forEach(btn => {
      btn.addEventListener('click', function() {
        quickDatesWrap.querySelectorAll('.ald-quick-chip').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentDate = this.dataset.date;
        datePicker.value = (currentDate === 'all') ? '' : currentDate;
        renderTable();
      });
    });
  }

  // Master definitions of all MySQL tables in Group 3 & Group 4
  const masterTables = [
    // Group 3
    { groupCode: 3, groupName: "Group 3: แท่งเทียน/Indicator", tableName: "full_analysis_data", defaultServer: "1", loadPage: "load_full_analysis_data", loadTitle: "Load FullAnalysis Data" },
    { groupCode: 3, groupName: "Group 3: แท่งเทียน/Indicator", tableName: "ticks", defaultServer: "9999", loadPage: "tables", loadTitle: "Load Ticks Data" },
    { groupCode: 3, groupName: "Group 3: แท่งเทียน/Indicator", tableName: "candles", defaultServer: "9999", loadPage: "tables", loadTitle: "Load Candles Data" },
    { groupCode: 3, groupName: "Group 3: แท่งเทียน/Indicator", tableName: "candle_analysis", defaultServer: "9999", loadPage: "pktrend_action", loadTitle: "Run Candle Analysis" },
    // Group 4
    { groupCode: 4, groupName: "Group 4: ผลการเทรด", tableName: "vpstradedata", defaultServer: "2", loadPage: "load_vps_trade_data", loadTitle: "Load VPS Trade Data" },
    { groupCode: 4, groupName: "Group 4: ผลการเทรด", tableName: "derivtradehistory", defaultServer: "1", loadPage: "load_deriv_trade_history", loadTitle: "Fetch Deriv Trades" },
    { groupCode: 4, groupName: "Group 4: ผลการเทรด", tableName: "tradehead", defaultServer: "2", loadPage: "load_vps_trade_data", loadTitle: "Load TradeHead Data" },
    { groupCode: 4, groupName: "Group 4: ผลการเทรด", tableName: "trade_history", defaultServer: "2", loadPage: "load_deriv_trade_history", loadTitle: "Fetch Trade History" }
  ];

  // Render HTML Table
  function renderTable() {
    const allRows = getFlattenedRows();
    const rowsToDisplay = [];

    if (currentDate !== 'all') {
      masterTables.forEach(m => {
        const matches = allRows.filter(r => {
          const matchTable = (r.tableName.toLowerCase() === m.tableName.toLowerCase());
          const matchDate  = (r.date === currentDate);
          const matchVps   = (currentVps === 'all') || (r.serverCode === currentVps);
          return matchTable && matchDate && matchVps;
        });

        if (matches.length > 0) {
          matches.forEach(item => {
            rowsToDisplay.push({
              ...item,
              hasData: true,
              loadPage: m.loadPage,
              loadTitle: m.loadTitle
            });
          });
        } else {
          const displayServer = (currentVps !== 'all') ? currentVps : (m.defaultServer || null);
          rowsToDisplay.push({
            groupCode: m.groupCode,
            groupName: m.groupName,
            tableName: m.tableName,
            serverCode: displayServer,
            date: currentDate,
            startdatetime: '-',
            stopdatetime: '-',
            numData: 0,
            hasData: false,
            loadPage: m.loadPage,
            loadTitle: m.loadTitle
          });
        }
      });
    } else {
      masterTables.forEach(m => {
        const matches = allRows.filter(r => {
          const matchTable = (r.tableName.toLowerCase() === m.tableName.toLowerCase());
          const matchVps   = (currentVps === 'all') || (r.serverCode === currentVps);
          return matchTable && matchVps;
        });

        if (matches.length > 0) {
          matches.forEach(item => {
            rowsToDisplay.push({
              ...item,
              hasData: true,
              loadPage: m.loadPage,
              loadTitle: m.loadTitle
            });
          });
        } else {
          const displayServer = (currentVps !== 'all') ? currentVps : (m.defaultServer || null);
          rowsToDisplay.push({
            groupCode: m.groupCode,
            groupName: m.groupName,
            tableName: m.tableName,
            serverCode: displayServer,
            date: '-',
            startdatetime: '-',
            stopdatetime: '-',
            numData: 0,
            hasData: false,
            loadPage: m.loadPage,
            loadTitle: m.loadTitle
          });
        }
      });
    }

    // Filter by search keyword
    const filtered = rowsToDisplay.filter(r => {
      if (!searchKeyword) return true;
      return r.tableName.toLowerCase().includes(searchKeyword) ||
             r.groupName.toLowerCase().includes(searchKeyword) ||
             (r.serverCode && String(r.serverCode).includes(searchKeyword)) ||
             (r.date && r.date.includes(searchKeyword));
    });

    // Update Statistics
    const loadedItems = filtered.filter(r => r.hasData);
    const tablesWithData = new Set(loadedItems.map(r => r.tableName)).size;
    const totalRowsCount = loadedItems.reduce((acc, r) => acc + r.numData, 0);

    statTableCount.textContent = `${tablesWithData} / 8 ตาราง`;
    statTotalRows.textContent = totalRowsCount.toLocaleString();
    statSelectedVps.textContent = (currentVps === 'all') ? 'ทั้งหมด (All VPS)' : (vpsNameMap[currentVps] || `Server ${currentVps}`);
    statSelectedDate.textContent = (currentDate === 'all') ? 'ทุกวันที่' : currentDate;
    matchCountEl.textContent = `(${filtered.length} รายการ: มีข้อมูล ${loadedItems.length}, รอโหลด ${filtered.length - loadedItems.length})`;

    if (filtered.length === 0) {
      tableBody.innerHTML = '';
      emptyState.style.display = 'flex';
      return;
    }

    emptyState.style.display = 'none';

    // Render HTML Table
    let html = '';
    filtered.forEach((r, idx) => {
      const serverLabel = r.serverCode ? (vpsNameMap[r.serverCode] || `Server ${r.serverCode}`) : 'ส่วนกลาง (Global)';
      const serverBadge = r.serverCode ? 
        `<span class="ald-server-tag">VPS ${r.serverCode}</span> <span style="font-size:12px; color:#cbd5e1;">${serverLabel}</span>` : 
        `<span style="color:#94a3b8; font-style:italic;">-</span>`;
      const groupBadge = r.groupCode === 3 ? 
        `<span class="ald-badge group3">Group 3: แท่งเทียน/Indicator</span>` : 
        `<span class="ald-badge group4">Group 4: ผลการเทรด</span>`;

      const numBadge = r.hasData ? 
        `<span class="ald-num-badge">${r.numData.toLocaleString()}</span>` : 
        `<span style="color:#64748b; font-family:monospace; font-weight:600;">-</span>`;

      // Status cell: Always include Load Data button for every table row!
      const statusBadge = `
        <span class="ald-status-ready">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
          พร้อมใช้งาน
        </span>`;

      const loadBtnClass = r.hasData ? "ald-btn-load ald-btn-load-more" : "ald-btn-load";
      const loadBtnText  = r.hasData ? "Load Data" : "Load Data";
      const loadBtnTitle = r.hasData ? `โหลดข้อมูลเพิ่มเติมสำหรับตาราง ${r.tableName}` : `โหลดข้อมูลตาราง ${r.tableName}`;

      const loadBtn = `
        <button type="button" class="${loadBtnClass}" 
                data-table="${r.tableName}" 
                data-server="${r.serverCode || ''}" 
                data-date="${r.date !== '-' ? r.date : ''}"
                data-group="${r.groupCode}"
                data-title="${r.loadTitle}" 
                title="${loadBtnTitle}">
          <svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
          <span>${loadBtnText}</span>
        </button>`;

      const statusCell = r.hasData ? 
        `<div class="ald-status-cell-wrap">${statusBadge} ${loadBtn}</div>` : 
        `<div class="ald-status-cell-wrap">${loadBtn}</div>`;

      html += `
        <tr>
          <td style="color:#64748b; font-family:monospace;">${idx + 1}</td>
          <td>${groupBadge}</td>
          <td>
            <strong style="color:#ffffff; font-family:'JetBrains Mono',monospace; font-size:13.5px;">${r.tableName}</strong>
          </td>
          <td>${serverBadge}</td>
          <td style="font-family:monospace; color:${r.date !== '-' ? '#38bdf8' : '#64748b'}; font-weight:600;">${r.date}</td>
          <td style="font-family:monospace; font-size:12px; color:${r.hasData ? '#94a3b8' : '#64748b'};">${r.startdatetime}</td>
          <td style="font-family:monospace; font-size:12px; color:${r.hasData ? '#94a3b8' : '#64748b'};">${r.stopdatetime}</td>
          <td style="text-align:right;">${numBadge}</td>
          <td style="text-align:center;">${statusCell}</td>
        </tr>
      `;
    });

    tableBody.innerHTML = html;

    // Attach AJAX Load event to every Load Data button
    tableBody.querySelectorAll('.ald-btn-load').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const tName = this.dataset.table;
        const sCode = this.dataset.server;
        const rDate = this.dataset.date;
        const title = this.dataset.title || tName;
        
        executeAjaxLoad(tName, sCode, rDate, title);
      });
    });
  }

  // 1. VPS Chip Click Handlers
  vpsChips.forEach(chip => {
    chip.addEventListener('click', function() {
      vpsChips.forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      currentVps = this.dataset.vps;
      updateQuickDates();
      renderTable();
    });
  });

  // 2. Datepicker change handler
  if (datePicker) {
    datePicker.addEventListener('change', function() {
      if (this.value) {
        currentDate = this.value;
        quickDatesWrap.querySelectorAll('.ald-quick-chip').forEach(b => {
          b.classList.toggle('active', b.dataset.date === currentDate);
        });
      } else {
        currentDate = 'all';
        quickDatesWrap.querySelector('[data-date="all"]')?.classList.add('active');
      }
      renderTable();
    });
  }

  // 3. Table Search Filter
  if (tableSearch) {
    tableSearch.addEventListener('input', function() {
      searchKeyword = this.value.trim().toLowerCase();
      renderTable();
    });
  }

  // 4. Run generate_load_data_json.php via AJAX
  if (btnGenerate) {
    btnGenerate.addEventListener('click', function() {
      if (this.disabled) return;
      this.disabled = true;
      this.classList.add('running');
      btnText.textContent = 'กำลังรันสคริปต์ generate...';

      fetch('../generate_loaded_data_json.php?_t=' + Date.now(), {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      })
      .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(res => {
        btnGenerate.disabled = false;
        btnGenerate.classList.remove('running');
        btnText.textContent = 'Run generate_load_data_json.php';

        if (res.success && Array.isArray(res.data)) {
          rawGroupsData = res.data;
          if (res.generated_at && lastUpdatedEl) {
            lastUpdatedEl.textContent = res.generated_at;
          }
          showToast('✅ รัน generate_load_data_json.php และสร้าง JSON ใหม่สำเร็จ!');
          updateQuickDates();
          renderTable();
        } else {
          showToast('⚠️ สคริปต์ทำงานเสร็จแต่อาจมีข้อผิดพลาด');
        }
      })
      .catch(err => {
        btnGenerate.disabled = false;
        btnGenerate.classList.remove('running');
        btnText.textContent = 'Run generate_load_data_json.php';
        showToast('❌ ไม่สามารถรันสคริปต์ได้: ' + err.message, false);
      });
    });
  }

  // Initial render
  updateQuickDates();
  renderTable();

})();
</script>
