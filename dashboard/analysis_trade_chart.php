<?php
/**
 * analysis_trade_chart.php
 * Standalone Lightweight Candlestick Chart for Asset Trading Analysis
 * 
 * Features:
 * - Real 1-minute candlestick chart powered by TradingView Lightweight Charts
 * - Automatic historical candle loading via Deriv Public WebSocket & Local MySQL
 * - Trade entry/exit markers with Win/Loss indicators
 * - Toggle filters for winCon markers & lossCon markers
 * - Crosshair inspection tooltip with full trade metadata
 * - Synchronized interactive trades table (click trade -> zoom to candle)
 */

header('Content-Type: text/html; charset=utf-8');

// Action API for zoneAnalysis.js file operations
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'load_zone_analysis') {
        header('Content-Type: application/json; charset=utf-8');
        $filePath = __DIR__ . '/js/zoneAnalysis.js';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            echo json_encode([
                'success' => true,
                'content' => $content,
                'path' => 'dashboard/js/zoneAnalysis.js',
                'size' => strlen($content),
                'mtime' => filemtime($filePath)
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'File not found']);
        }
        exit;
    }

    if ($_GET['action'] === 'save_zone_analysis' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $content = isset($json['content']) ? $json['content'] : (isset($_POST['content']) ? $_POST['content'] : null);

        if ($content === null) {
            echo json_encode(['success' => false, 'error' => 'No content provided']);
            exit;
        }

        $dest1 = __DIR__ . '/js/zoneAnalysis.js';
        $dest2 = dirname(__DIR__) . '/zoneAnalysis.js';

        $w1 = @file_put_contents($dest1, $content);
        $w2 = @file_put_contents($dest2, $content);

        if ($w1 !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'บันทึกไฟล์ zoneAnalysis.js สำเร็จ (' . strlen($content) . ' bytes)',
                'time' => date('H:i:s'),
                'size' => strlen($content)
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ไม่สามารถเขียนไฟล์ได้ กรุณาตรวจสอบสิทธิ์การเขียนไฟล์']);
        }
        exit;
    }
}

$serverCode = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : 0;
$tradeRoundNo = isset($_GET['tradeRoundNo']) ? intval($_GET['tradeRoundNo']) : 0;
$assetCode = isset($_GET['assetCode']) ? trim($_GET['assetCode']) : '1HZ10V';
$date = isset($_GET['date']) ? trim($_GET['date']) : '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chart Analysis - <?php echo htmlspecialchars($assetCode); ?> (Round #<?php echo $tradeRoundNo; ?>)</title>
  <link rel="stylesheet" href="css/style.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Lightweight Charts v4 -->
  <script src="https://unpkg.com/lightweight-charts@4.2.2/dist/lightweight-charts.standalone.production.js"></script>
  <!-- JSZip v3 for Unzipping VPS Trade Data -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <!-- Choppiness Combined Indicator (Macro + Micro 15M) -->
  <script src="js/choppinessCombined.js"></script>
  <!-- Zone Analysis Data Structure & Helpers -->
  <script src="js/zoneAnalysis.js"></script>
  <style>
    :root {
      --bg-dark: #060b26;
      --bg-card: rgba(10, 19, 48, 0.94);
      --border-color: rgba(255, 255, 255, 0.1);
      --text-primary: #ffffff;
      --text-secondary: #cbd5e1;
      --text-tertiary: #94a3b8;
      --accent-blue: #0075ff;
      --accent-cyan: #00d4ff;
      --accent-green: #10b981;
      --accent-red: #ef4444;
      --accent-purple: #8b5cf6;
      --shadow-card: 0 8px 32px 0 rgba(0, 0, 0, 0.4);
      --radius-sm: 8px;
      --radius-md: 14px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--bg-dark);
      color: var(--text-primary);
      min-height: 100vh;
      padding: 20px;
      background-image: 
        radial-gradient(circle at 15% 20%, rgba(0, 117, 255, 0.12) 0%, transparent 45%),
        radial-gradient(circle at 85% 75%, rgba(139, 92, 246, 0.1) 0%, transparent 45%);
    }

    .chart-page-container {
      max-width: 1600px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    /* Top Bar */
    .chart-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 14px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 16px 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }

    /* Filter Control Bar */
    .chart-filter-bar {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 14px 20px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }

    .filter-controls-wrap {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px 18px;
    }

    .filter-item {
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1 1 180px;
      min-width: 140px;
    }

    .filter-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .filter-label svg {
      color: var(--accent-cyan);
    }

    .filter-select {
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.16);
      border-radius: 8px;
      color: #fff;
      padding: 8px 12px;
      font-size: 13px;
      font-weight: 600;
      font-family: inherit;
      outline: none;
      transition: all 0.2s ease;
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%2300d4ff'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      padding-right: 28px;
    }

    .filter-select:focus {
      border-color: var(--accent-cyan);
      box-shadow: 0 0 10px rgba(0, 212, 255, 0.25);
    }

    .filter-select option {
      background: #060b26;
      color: #fff;
      padding: 6px;
    }

    .filter-date-group {
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .filter-date-group .filter-select {
      flex: 1;
    }

    .filter-date-input {
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.16);
      border-radius: 8px;
      color: #fff;
      padding: 7px 8px;
      font-size: 12px;
      outline: none;
      cursor: pointer;
      color-scheme: dark;
      width: 40px;
      transition: border-color 0.2s ease;
    }

    .filter-date-input:focus {
      border-color: var(--accent-cyan);
    }

    .btn-load-filter {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      margin-top: 18px;
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(0, 117, 255, 0.35);
      transition: all 0.2s ease;
      white-space: nowrap;
      align-self: flex-end;
    }

    .btn-load-filter:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(0, 117, 255, 0.5);
      filter: brightness(1.1);
    }

    .btn-load-filter:active {
      transform: translateY(0);
    }

    .btn-sync-today {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      margin-top: 18px;
      background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
      transition: all 0.2s ease;
      white-space: nowrap;
      align-self: flex-end;
    }

    .btn-sync-today:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(16, 185, 129, 0.55);
      filter: brightness(1.1);
    }

    .btn-sync-today:active {
      transform: translateY(0);
    }

    .badge-round {
      display: inline-block;
      padding: 2px 7px;
      font-size: 11px;
      font-weight: 700;
      border-radius: 4px;
      background: rgba(14, 165, 233, 0.2);
      color: #38bdf8;
      border: 1px solid rgba(56, 189, 248, 0.3);
      white-space: nowrap;
    }

    /* Source Database Tables Bar */
    .source-tables-bar {
      background: rgba(6, 11, 40, 0.75);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: var(--radius-md);
      padding: 10px 18px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-top: -2px;
      margin-bottom: 2px;
    }

    .tables-bar-header {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
    }

    .tables-bar-title {
      font-size: 11px;
      font-weight: 800;
      color: var(--accent-cyan);
      letter-spacing: 0.8px;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .tables-bar-subtitle {
      font-size: 11px;
      color: var(--text-muted);
      font-weight: 400;
    }

    .tables-btn-group {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
    }

    .btn-table-tag {
      background: rgba(10, 19, 48, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      padding: 6px 12px;
      color: #f1f5f9;
      font-size: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      outline: none;
      font-family: inherit;
    }

    .btn-table-tag:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
    }

    .btn-table-tag .table-tag-name {
      font-family: 'Share Tech Mono', monospace;
      font-size: 12.5px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }

    .btn-table-tag .table-tag-badge {
      font-size: 10px;
      font-weight: 500;
      padding: 2px 6px;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-secondary);
    }

    /* Tag color themes */
    .btn-table-tag.tag-head {
      border-color: rgba(56, 189, 248, 0.35);
    }
    .btn-table-tag.tag-head:hover {
      background: rgba(56, 189, 248, 0.15);
      border-color: #38bdf8;
      box-shadow: 0 0 14px rgba(56, 189, 248, 0.3);
    }
    .btn-table-tag.tag-head .table-tag-name { color: #38bdf8; }

    .btn-table-tag.tag-trades {
      border-color: rgba(52, 211, 153, 0.35);
    }
    .btn-table-tag.tag-trades:hover {
      background: rgba(52, 211, 153, 0.15);
      border-color: #34d399;
      box-shadow: 0 0 14px rgba(52, 211, 153, 0.3);
    }
    .btn-table-tag.tag-trades .table-tag-name { color: #34d399; }

    .btn-table-tag.tag-vps {
      border-color: rgba(129, 140, 248, 0.35);
    }
    .btn-table-tag.tag-vps:hover {
      background: rgba(129, 140, 248, 0.15);
      border-color: #818cf8;
      box-shadow: 0 0 14px rgba(129, 140, 248, 0.3);
    }
    .btn-table-tag.tag-vps .table-tag-name { color: #818cf8; }

    .btn-table-tag.tag-fallback {
      border-color: rgba(251, 146, 60, 0.35);
    }
    .btn-table-tag.tag-fallback:hover {
      background: rgba(251, 146, 60, 0.15);
      border-color: #fb923c;
      box-shadow: 0 0 14px rgba(251, 146, 60, 0.3);
    }
    .btn-table-tag.tag-fallback .table-tag-name { color: #fb923c; }

    .btn-table-tag.tag-candles {
      border-color: rgba(192, 132, 252, 0.35);
    }
    .btn-table-tag.tag-candles:hover {
      background: rgba(192, 132, 252, 0.15);
      border-color: #c084fc;
      box-shadow: 0 0 14px rgba(192, 132, 252, 0.3);
    }
    .btn-table-tag.tag-candles .table-tag-name { color: #c084fc; }

    .btn-table-tag.tag-config {
      border-color: rgba(148, 163, 184, 0.3);
    }
    .btn-table-tag.tag-config:hover {
      background: rgba(148, 163, 184, 0.15);
      border-color: #94a3b8;
      box-shadow: 0 0 14px rgba(148, 163, 184, 0.25);
    }
    .btn-table-tag.tag-config .table-tag-name { color: #cbd5e1; }

    /* Button Sync Candles in Table Bar (แนวทางที่ 2) */
    .btn-table-tag.btn-sync-candles-tag {
      background: linear-gradient(135deg, rgba(139, 92, 246, 0.22) 0%, rgba(0, 117, 255, 0.22) 100%);
      border-color: rgba(168, 85, 247, 0.55);
      box-shadow: 0 0 10px rgba(168, 85, 247, 0.2);
      transition: all 0.25s ease;
      cursor: pointer;
    }
    .btn-table-tag.btn-sync-candles-tag:hover {
      background: linear-gradient(135deg, rgba(139, 92, 246, 0.42) 0%, rgba(0, 117, 255, 0.42) 100%);
      border-color: #c084fc;
      box-shadow: 0 0 18px rgba(168, 85, 247, 0.45);
      transform: translateY(-2px);
    }
    .btn-table-tag.btn-sync-candles-tag .table-tag-name {
      color: #f3e8ff;
      font-weight: 700;
    }
    .badge-sync {
      background: rgba(168, 85, 247, 0.3) !important;
      color: #e9d5ff !important;
      border-color: rgba(168, 85, 247, 0.5) !important;
    }

    /* Table Modal Popup */
    .table-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(2, 6, 23, 0.82);
      backdrop-filter: blur(8px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 99999;
      padding: 16px;
      animation: fadeIn 0.2s ease;
    }

    .table-modal-overlay.active {
      display: flex;
    }

    .table-modal-card {
      background: #0b1329;
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: 16px;
      width: 100%;
      max-width: 680px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7), 0 0 35px rgba(0, 117, 255, 0.18);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalSlideUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .table-modal-header {
      padding: 16px 20px;
      background: rgba(255, 255, 255, 0.03);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .table-modal-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .table-modal-close {
      background: transparent;
      border: none;
      color: #94a3b8;
      font-size: 20px;
      cursor: pointer;
      padding: 4px 8px;
      border-radius: 6px;
      transition: all 0.2s;
      line-height: 1;
    }
    .table-modal-close:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
    }

    .table-modal-body {
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      max-height: 70vh;
      overflow-y: auto;
    }

    .table-modal-section-title {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .table-modal-desc {
      font-size: 13.5px;
      color: #cbd5e1;
      line-height: 1.6;
    }

    .table-modal-code {
      background: #040817;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 12px 14px;
      font-family: 'Share Tech Mono', monospace;
      font-size: 12.5px;
      color: #38bdf8;
      line-height: 1.5;
      overflow-x: auto;
      white-space: pre-wrap;
      word-break: break-word;
    }

    .table-modal-columns-list {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .col-pill {
      font-family: 'Share Tech Mono', monospace;
      font-size: 11px;
      padding: 2px 7px;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.06);
      color: #93c5fd;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .table-modal-footer {
      padding: 12px 20px;
      background: rgba(255, 255, 255, 0.02);
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn-modal-copy {
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 12.5px;
      font-weight: 700;
      padding: 8px 16px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .btn-modal-copy:hover {
      filter: brightness(1.15);
      transform: translateY(-1px);
    }

    .btn-modal-close-action {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      color: #cbd5e1;
      font-size: 12.5px;
      font-weight: 600;
      padding: 8px 14px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-modal-close-action:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
    }

    /* Auto-Load Confirmation & Stepper Modal */
    .auto-load-modal-card {
      max-width: 650px;
      border: 1px solid rgba(0, 212, 255, 0.25);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 35px rgba(0, 212, 255, 0.2);
    }
    .auto-load-date-badge {
      font-size: 11px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 20px;
      background: rgba(0, 212, 255, 0.15);
      border: 1px solid rgba(0, 212, 255, 0.4);
      color: #38bdf8;
      font-family: 'Share Tech Mono', monospace;
    }
    .auto-load-alert-box {
      display: flex;
      gap: 14px;
      align-items: flex-start;
      background: rgba(245, 158, 11, 0.1);
      border: 1px solid rgba(245, 158, 11, 0.35);
      border-radius: 10px;
      padding: 14px 18px;
    }
    .auto-load-alert-icon {
      font-size: 26px;
      line-height: 1;
    }
    .auto-load-step-preview-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 8px;
    }
    .step-preview-row {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 8px;
      padding: 10px 14px;
    }
    .step-preview-num {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: rgba(0, 117, 255, 0.25);
      border: 1px solid rgba(0, 117, 255, 0.6);
      color: #60a5fa;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 800;
      flex-shrink: 0;
    }
    .step-preview-title {
      font-size: 13px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 2px;
    }
    .step-preview-desc {
      font-size: 11.5px;
      color: var(--text-tertiary);
      line-height: 1.4;
    }
    /* Stepper Live Progress */
    .auto-load-stepper {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .stepper-item {
      display: flex;
      gap: 14px;
      align-items: center;
      padding: 12px 16px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    .stepper-item.active {
      background: rgba(0, 117, 255, 0.12);
      border-color: rgba(0, 212, 255, 0.5);
      box-shadow: 0 0 16px rgba(0, 212, 255, 0.25);
    }
    .stepper-item.done {
      background: rgba(16, 185, 129, 0.1);
      border-color: rgba(16, 185, 129, 0.4);
    }
    .stepper-item.error {
      background: rgba(239, 68, 68, 0.1);
      border-color: rgba(239, 68, 68, 0.4);
    }
    .stepper-icon-box {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      color: #94a3b8;
      flex-shrink: 0;
      transition: all 0.3s ease;
    }
    .stepper-item.active .stepper-icon-box {
      background: #0075ff;
      border-color: #00d4ff;
      color: #fff;
      box-shadow: 0 0 12px rgba(0, 212, 255, 0.6);
      animation: pulseGlow 1.5s infinite alternate;
    }
    @keyframes pulseGlow {
      from { box-shadow: 0 0 6px rgba(0, 212, 255, 0.4); }
      to { box-shadow: 0 0 16px rgba(0, 212, 255, 0.8); }
    }
    .stepper-item.done .stepper-icon-box {
      background: #10b981;
      border-color: #34d399;
      color: #fff;
    }
    .stepper-item.error .stepper-icon-box {
      background: #ef4444;
      border-color: #f87171;
      color: #fff;
    }
    .stepper-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .stepper-title {
      font-size: 13px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .stepper-desc {
      font-size: 11px;
      color: var(--text-tertiary);
    }
    .stepper-status-badge {
      font-size: 10px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .badge-pending {
      background: rgba(255, 255, 255, 0.08);
      color: #94a3b8;
    }
    .badge-running {
      background: rgba(0, 117, 255, 0.3);
      color: #60a5fa;
      border: 1px solid rgba(0, 117, 255, 0.5);
    }
    .badge-done {
      background: rgba(16, 185, 129, 0.25);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.45);
    }
    .badge-error {
      background: rgba(239, 68, 68, 0.25);
      color: #f87171;
      border: 1px solid rgba(239, 68, 68, 0.45);
    }
    .auto-load-log-wrap {
      margin-top: 10px;
    }
    .auto-load-log-title {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-secondary);
      margin-bottom: 6px;
      display: flex;
      justify-content: space-between;
    }
    .auto-load-log-console {
      background: #040817;
      border: 1px solid rgba(255, 255, 255, 0.09);
      border-radius: 8px;
      padding: 10px 12px;
      font-family: 'Share Tech Mono', monospace;
      font-size: 11px;
      max-height: 140px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .log-line {
      line-height: 1.4;
    }
    .log-line.info { color: #94a3b8; }
    .log-line.success { color: #34d399; }
    .log-line.warn { color: #fbbf24; }
    .log-line.error { color: #f87171; }
    .btn-modal-primary-action {
      background: linear-gradient(135deg, #0075ff 0%, #00d4ff 100%);
      border: none;
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      padding: 8px 18px;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 15px rgba(0, 117, 255, 0.4);
    }
    .btn-modal-primary-action:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(0, 212, 255, 0.5);
    }
    .btn-modal-primary-action:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    .chart-title-area {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: var(--radius-sm);
      color: #fff;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.2s ease;
    }

    .back-btn:hover {
      background: rgba(0, 117, 255, 0.25);
      border-color: var(--accent-blue);
      transform: translateX(-2px);
    }

    .chart-main-title {
      font-size: 20px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff;
    }

    .symbol-pill {
      background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
      color: #060b26;
      font-size: 13px;
      font-weight: 800;
      padding: 3px 10px;
      border-radius: 6px;
      letter-spacing: 0.5px;
    }

    .meta-tag {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 6px;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-secondary);
    }

    .meta-tag b {
      color: #fff;
    }

    /* KPI Summary Cards */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 14px;
    }

    .kpi-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 14px 18px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }

    .kpi-label {
      font-size: 11px;
      font-weight: 600;
      color: var(--text-tertiary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .kpi-value {
      font-size: 20px;
      font-weight: 800;
      color: #fff;
    }

    .kpi-sub {
      font-size: 11px;
      color: var(--text-tertiary);
      margin-top: 2px;
    }

    /* Controls & Toggles Bar */
    .controls-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 12px 18px;
    }

    .toggles-group {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .toggle-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      font-size: 12px;
      font-weight: 700;
      border-radius: 8px;
      cursor: pointer;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.05);
      color: var(--text-secondary);
      transition: all 0.2s ease;
      user-select: none;
    }

    .toggle-btn:hover {
      border-color: rgba(255, 255, 255, 0.25);
      color: #fff;
    }

    .toggle-btn.active.btn-win-con {
      background: rgba(16, 185, 129, 0.22);
      border-color: rgba(16, 185, 129, 0.5);
      color: #34d399;
      box-shadow: 0 0 10px rgba(16, 185, 129, 0.25);
    }

    .toggle-btn.active.btn-loss-con {
      background: rgba(239, 68, 68, 0.22);
      border-color: rgba(239, 68, 68, 0.5);
      color: #f87171;
      box-shadow: 0 0 10px rgba(239, 68, 68, 0.25);
    }

    .toggle-btn.active.btn-all-trades {
      background: rgba(0, 117, 255, 0.22);
      border-color: rgba(0, 117, 255, 0.5);
      color: #60a5fa;
      box-shadow: 0 0 10px rgba(0, 117, 255, 0.25);
    }

    .toggle-btn.active.btn-label-mode {
      background: rgba(139, 92, 246, 0.22);
      border-color: rgba(139, 92, 246, 0.5);
      color: #c084fc;
    }

    .toggle-btn.active.btn-tooltip {
      background: rgba(14, 165, 233, 0.25);
      border-color: #0ea5e9;
      color: #38bdf8;
      box-shadow: 0 0 10px rgba(14, 165, 233, 0.35);
    }

    .toggle-btn.active.btn-choppy-combined {
      background: rgba(168, 85, 247, 0.25);
      border-color: #a855f7;
      color: #c084fc;
      box-shadow: 0 0 10px rgba(168, 85, 247, 0.35);
    }

    .toggle-btn.active.btn-mark-zone {
      background: rgba(251, 191, 36, 0.22);
      border-color: rgba(251, 191, 36, 0.5);
      color: #fbbf24;
      box-shadow: 0 0 10px rgba(251, 191, 36, 0.25);
    }

    .toggle-btn.active.btn-bollinger {
      background: rgba(255, 152, 0, 0.22);
      border-color: rgba(255, 152, 0, 0.5);
      color: #ffb74d;
      box-shadow: 0 0 10px rgba(255, 152, 0, 0.25);
    }

    .toggle-btn.active.btn-analyze-zones {
      background: rgba(6, 182, 212, 0.22);
      border-color: rgba(6, 182, 212, 0.5);
      color: #38bdf8;
      box-shadow: 0 0 10px rgba(6, 182, 212, 0.25);
    }

    /* BB Squeeze Switch inside popover */
    .bb-squeeze-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
      padding: 6px 0;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .bb-squeeze-row .ath-switch input:checked + .ath-slider {
      background-color: #e040fb;
      box-shadow: 0 0 10px rgba(224, 64, 251, 0.45);
    }

    /* BB Flat Choppy Switch inside popover and toolbar */
    .bb-flat-choppy-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
      padding: 6px 0;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .bb-flat-choppy-row .ath-switch input:checked + .ath-slider,
    #switch-flat-choppy:checked + .ath-slider {
      background-color: #f59e0b;
      box-shadow: 0 0 10px rgba(245, 158, 11, 0.45);
    }

    /* Bollinger Settings Popover */
    .bb-settings-anchor {
      position: relative;
      display: inline-flex;
    }

    .bb-settings-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 30px;
      height: 30px;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.05);
      color: var(--text-secondary);
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 14px;
      margin-left: -2px;
    }

    .bb-settings-btn:hover {
      background: rgba(255, 152, 0, 0.2);
      border-color: rgba(255, 152, 0, 0.5);
      color: #ffb74d;
    }

    .bb-popover {
      display: none;
      position: absolute;
      top: calc(100% + 8px);
      left: 50%;
      transform: translateX(-50%);
      z-index: 200;
      min-width: 260px;
      background: rgba(10, 19, 48, 0.96);
      border: 1px solid rgba(255, 152, 0, 0.3);
      border-radius: 12px;
      padding: 16px 18px;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(255, 152, 0, 0.08);
      backdrop-filter: blur(20px);
    }

    .bb-popover.open {
      display: block;
      animation: bbFadeIn 0.2s ease;
    }

    @keyframes bbFadeIn {
      from { opacity: 0; transform: translateX(-50%) translateY(-6px); }
      to   { opacity: 1; transform: translateX(-50%) translateY(0); }
    }

    .bb-popover-title {
      font-size: 13px;
      font-weight: 800;
      color: #ffb74d;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 6px;
      letter-spacing: 0.3px;
    }

    .bb-popover-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
      gap: 10px;
    }

    .bb-popover-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-secondary);
      white-space: nowrap;
    }

    .bb-popover-input {
      width: 72px;
      padding: 5px 8px;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.15);
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      font-family: 'Plus Jakarta Sans', monospace;
      text-align: center;
      outline: none;
      transition: border-color 0.2s ease;
    }

    .bb-popover-input:focus {
      border-color: rgba(255, 152, 0, 0.6);
      box-shadow: 0 0 8px rgba(255, 152, 0, 0.2);
    }

    .bb-popover-select {
      width: 100px;
      padding: 5px 8px;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.15);
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      font-family: 'Plus Jakarta Sans', monospace;
      outline: none;
      cursor: pointer;
      transition: border-color 0.2s ease;
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%23ffb74d'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 8px center;
      padding-right: 24px;
    }

    .bb-popover-select:focus {
      border-color: rgba(255, 152, 0, 0.6);
      box-shadow: 0 0 8px rgba(255, 152, 0, 0.2);
    }

    .bb-popover-select option {
      background: #0a1330;
      color: #fff;
      font-weight: 700;
    }

    .bb-apply-btn {
      width: 100%;
      margin-top: 4px;
      padding: 7px 12px;
      font-size: 12px;
      font-weight: 700;
      border-radius: 8px;
      border: 1px solid rgba(255, 152, 0, 0.4);
      background: linear-gradient(135deg, rgba(255, 152, 0, 0.25) 0%, rgba(255, 87, 34, 0.18) 100%);
      color: #ffb74d;
      cursor: pointer;
      transition: all 0.2s ease;
      letter-spacing: 0.3px;
    }

    .bb-apply-btn:hover {
      background: linear-gradient(135deg, rgba(255, 152, 0, 0.4) 0%, rgba(255, 87, 34, 0.3) 100%);
      border-color: #ffb74d;
      box-shadow: 0 0 12px rgba(255, 152, 0, 0.3);
    }

    .action-btn.btn-clear-zones {
      border-color: rgba(251, 191, 36, 0.4);
      color: #fbbf24;
    }

    .action-btn.btn-clear-zones:hover {
      background: rgba(251, 191, 36, 0.25);
      border-color: #fbbf24;
    }

    .zone-count-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 18px;
      height: 18px;
      padding: 0 5px;
      border-radius: 9px;
      background: rgba(251, 191, 36, 0.3);
      color: #fbbf24;
      font-size: 10px;
      font-weight: 800;
      margin-left: 4px;
    }

    /* Switchbox for Tooltip */
    .ath-switch-wrap {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 12px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      margin-left: 4px;
      user-select: none;
    }

    .ath-switch-title {
      font-size: 12px;
      font-weight: 700;
      color: var(--text-secondary);
    }

    .ath-switch {
      position: relative;
      display: inline-block;
      width: 36px;
      height: 20px;
      margin: 0;
      cursor: pointer;
    }

    .ath-switch input {
      opacity: 0;
      width: 0;
      height: 0;
      position: absolute;
    }

    .ath-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(255, 255, 255, 0.18);
      transition: 0.22s ease;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .ath-slider:before {
      position: absolute;
      content: "";
      height: 14px;
      width: 14px;
      left: 2px;
      bottom: 2px;
      background-color: #fff;
      transition: 0.22s ease;
      border-radius: 50%;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }

    .ath-switch input:checked + .ath-slider {
      background-color: var(--accent-blue, #0075ff);
      box-shadow: 0 0 10px rgba(0, 117, 255, 0.45);
    }

    .ath-switch input:checked + .ath-slider:before {
      transform: translateX(16px);
    }

    .ath-switch-text {
      font-size: 11px;
      font-weight: 800;
      color: #34d399;
      min-width: 24px;
      letter-spacing: 0.5px;
    }

    .ath-switch-text.off {
      color: var(--text-tertiary, #94a3b8);
    }

    .chart-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .action-btn {
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.06);
      color: #fff;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .action-btn:hover {
      background: rgba(0, 117, 255, 0.25);
      border-color: var(--accent-blue);
    }

    /* Save Config Button & Status */
    .btn-save-config {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(6, 182, 212, 0.25));
      border: 1px solid rgba(16, 185, 129, 0.5);
      color: #34d399;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: 700;
    }

    .btn-save-config:hover {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.45), rgba(6, 182, 212, 0.45));
      border-color: #10b981;
      color: #fff;
      box-shadow: 0 0 14px rgba(16, 185, 129, 0.4);
      transform: translateY(-1px);
    }

    .btn-save-config:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .config-status-text {
      font-size: 11px;
      font-weight: 600;
      color: #34d399;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: opacity 0.3s ease;
    }

    /* Floating Toast Notification */
    .config-toast {
      position: fixed;
      top: 24px;
      right: 24px;
      z-index: 99999;
      background: rgba(10, 19, 48, 0.95);
      border: 1px solid rgba(16, 185, 129, 0.5);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 16px rgba(16, 185, 129, 0.3);
      color: #fff;
      padding: 12px 20px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      backdrop-filter: blur(12px);
      transform: translateY(-50px);
      opacity: 0;
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      pointer-events: none;
    }

    .config-toast.show {
      transform: translateY(0);
      opacity: 1;
      pointer-events: auto;
    }

    .config-toast.error {
      border-color: rgba(239, 68, 68, 0.6);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 16px rgba(239, 68, 68, 0.3);
    }
    .config-toast.warn {
      border-color: rgba(245, 158, 11, 0.6);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 16px rgba(245, 158, 11, 0.35);
    }
    .config-toast.info {
      border-color: rgba(0, 212, 255, 0.6);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 16px rgba(0, 212, 255, 0.35);
    }
    .config-toast.sync {
      border-color: rgba(192, 132, 252, 0.7);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 20px rgba(192, 132, 252, 0.45);
    }
    .badge-timeline-mode {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 6px;
      background: rgba(245, 158, 11, 0.18);
      border: 1px solid rgba(245, 158, 11, 0.5);
      color: #fbbf24;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .chart-empty-btn-sync {
      background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%) !important;
      border-color: #a855f7 !important;
      box-shadow: 0 0 14px rgba(168, 85, 247, 0.35) !important;
    }
    .chart-empty-btn-sync:hover {
      background: linear-gradient(135deg, #9333ea 0%, #1d4ed8 100%) !important;
      box-shadow: 0 0 20px rgba(168, 85, 247, 0.55) !important;
      transform: translateY(-1px);
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #eab308;
    }

    .status-dot.connected {
      background: #10b981;
      box-shadow: 0 0 8px #10b981;
    }

    /* Main Chart Card */
    .chart-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 16px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
      position: relative;
    }

    #chart-container {
      width: 100%;
      height: 560px;
      position: relative;
    }

    /* Floating Tooltip */
    .chart-tooltip {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 100;
      background: rgba(6, 11, 40, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.18);
      border-radius: 12px;
      padding: 12px 16px;
      pointer-events: none;
      font-size: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.65);
      min-width: 280px;
      max-width: 360px;
      transition: opacity 0.15s ease;
      opacity: 0;
      backdrop-filter: blur(14px);
    }

    .chart-tooltip.visible {
      opacity: 1;
    }

    /* Floating Draggable Analyze Zones Panel (ขนาดเทียบเท่า tooltip) */
    .analyze-zones-panel {
      position: fixed;
      top: 140px;
      right: 24px;
      z-index: 1000;
      width: 320px;
      min-width: 280px;
      max-width: 360px;
      background: rgba(6, 11, 40, 0.95);
      border: 1px solid rgba(56, 189, 248, 0.35);
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.65), 0 0 16px rgba(56, 189, 248, 0.15);
      backdrop-filter: blur(14px);
      color: #e2e8f0;
      user-select: none;
      transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .analyze-zones-panel.dragging {
      opacity: 0.92;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.8), 0 0 24px rgba(56, 189, 248, 0.35);
      cursor: grabbing !important;
    }

    .analyze-zones-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 8px;
      margin-bottom: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
      cursor: grab;
    }

    .analyze-zones-panel.dragging .analyze-zones-header {
      cursor: grabbing;
    }

    .analyze-zones-title {
      display: flex;
      align-items: center;
      gap: 6px;
      font-weight: 700;
      font-size: 13px;
      color: #38bdf8;
      letter-spacing: 0.3px;
    }

    .analyze-zones-close-btn {
      background: transparent;
      border: none;
      color: #94a3b8;
      font-size: 14px;
      cursor: pointer;
      padding: 2px 6px;
      border-radius: 4px;
      transition: all 0.15s ease;
      line-height: 1;
    }

    .analyze-zones-close-btn:hover {
      color: #f43f5e;
      background: rgba(244, 63, 94, 0.15);
    }

    .analyze-zones-body {
      min-height: 80px;
      user-select: text;
    }

    .analyze-zones-result {
      font-size: 12px;
      line-height: 1.5;
    }

    .analyze-zones-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 16px 8px;
      text-align: center;
      background: rgba(255, 255, 255, 0.02);
      border: 1px dashed rgba(255, 255, 255, 0.1);
      border-radius: 8px;
    }

    /* Main Tabs Navigation */
    .main-tabs-nav {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding-bottom: 10px;
    }

    .main-tab-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(15, 23, 42, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: var(--radius-sm);
      padding: 10px 18px;
      color: var(--text-secondary);
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s ease;
      user-select: none;
    }

    .main-tab-btn:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #ffffff;
      border-color: rgba(255, 255, 255, 0.28);
    }

    .main-tab-btn.active {
      background: linear-gradient(135deg, rgba(0, 117, 255, 0.25), rgba(0, 212, 255, 0.18));
      border-color: rgba(0, 212, 255, 0.55);
      color: #38bdf8;
      box-shadow: 0 4px 16px rgba(0, 117, 255, 0.35);
    }

    .tab-badge {
      font-size: 10px;
      padding: 2px 7px;
      border-radius: 4px;
      background: rgba(245, 158, 11, 0.2);
      border: 1px solid rgba(245, 158, 11, 0.45);
      color: #fbbf24;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    .tab-content-pane {
      display: none;
      animation: tabFadeIn 0.2s ease;
    }

    .tab-content-pane.active {
      display: block;
    }

    @keyframes tabFadeIn {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Code Editor Card (Tab 2) */
    .code-editor-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-card);
      backdrop-filter: blur(16px);
      padding: 16px 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      min-height: 640px;
    }

    .code-editor-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      padding-bottom: 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .code-editor-file-info {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
    }

    .code-editor-filename {
      font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
      font-weight: 700;
      color: #38bdf8;
      background: rgba(56, 189, 248, 0.1);
      border: 1px solid rgba(56, 189, 248, 0.25);
      padding: 4px 10px;
      border-radius: 6px;
    }

    .code-editor-meta {
      color: var(--text-tertiary);
      font-size: 12px;
    }

    .code-editor-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .code-editor-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      font-size: 12px;
      font-weight: 700;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
      border: 1px solid transparent;
      user-select: none;
    }

    .btn-editor-reload {
      background: rgba(255, 255, 255, 0.06);
      border-color: rgba(255, 255, 255, 0.18);
      color: var(--text-secondary);
    }

    .btn-editor-reload:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
    }

    .btn-editor-save {
      background: linear-gradient(135deg, #10b981, #059669);
      color: #fff;
      box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
    }

    .btn-editor-save:hover {
      background: linear-gradient(135deg, #059669, #047857);
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.45);
      transform: translateY(-1px);
    }

    .btn-editor-save:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    .code-editor-status {
      font-size: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .code-editor-status.saved {
      color: #34d399;
    }

    .code-editor-status.modified {
      color: #fbbf24;
    }

    .code-editor-status.saving {
      color: #38bdf8;
    }

    .code-editor-status.error {
      color: #f87171;
    }

    .zone-code-editor-wrap {
      position: relative;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .zone-code-editor {
      width: 100%;
      height: 560px;
      min-height: 480px;
      background: #030718;
      color: #e2e8f0;
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: 8px;
      padding: 14px 16px;
      font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', 'Courier New', monospace;
      font-size: 13px;
      line-height: 1.6;
      tab-size: 2;
      resize: vertical;
      outline: none;
      white-space: pre;
      overflow-wrap: normal;
      overflow-x: auto;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .zone-code-editor:focus {
      border-color: rgba(56, 189, 248, 0.5);
      box-shadow: 0 0 16px rgba(56, 189, 248, 0.2);
    }

    .tt-header {
      font-weight: 700;
      color: #fff;
      margin-bottom: 6px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding-bottom: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .tt-ohlc {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 3px 12px;
      color: var(--text-tertiary);
      font-size: 11px;
      margin-bottom: 6px;
    }

    .tt-ohlc span b {
      color: #fff;
    }

    .tt-trade-box {
      margin-top: 8px;
      padding-top: 8px;
      border-top: 1px dashed rgba(255, 255, 255, 0.15);
    }

    .tt-grid-fields {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .tt-field-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
      font-size: 12px;
    }

    .tt-field-label {
      color: var(--text-tertiary, #94a3b8);
      font-size: 11px;
      font-weight: 600;
    }

    .tt-field-val {
      color: #fff;
      font-size: 12px;
      text-align: right;
    }

    .badge-color-dot {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 1px 7px;
      border-radius: 4px;
      font-weight: 700;
      font-size: 11px;
      text-transform: capitalize;
    }

    .badge-color-green {
      background: rgba(16, 185, 129, 0.2);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.45);
    }

    .badge-color-red {
      background: rgba(239, 68, 68, 0.2);
      color: #f87171;
      border: 1px solid rgba(239, 68, 68, 0.45);
    }

    .badge-color-other {
      background: rgba(255, 255, 255, 0.1);
      color: #cbd5e1;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    /* Bottom Trades Section */
    .trades-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 18px 24px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow-card);
    }

    .trades-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .trades-title {
      font-size: 15px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .trades-table-wrap {
      max-height: 320px;
      overflow-y: auto;
      overflow-x: auto;
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 8px;
    }

    .trades-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      text-align: left;
    }

    .trades-table th {
      background: rgba(15, 23, 42, 0.95);
      color: var(--text-secondary);
      font-weight: 700;
      padding: 10px 14px;
      position: sticky;
      top: 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      white-space: nowrap;
    }

    .trades-table td {
      padding: 8px 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      color: var(--text-primary);
      white-space: nowrap;
    }

    .trades-table tbody tr {
      cursor: pointer;
      transition: background 0.15s ease;
    }

    .trades-table tbody tr:hover {
      background: rgba(0, 117, 255, 0.12);
    }

    .trades-table tbody tr.active-row {
      background: rgba(0, 117, 255, 0.25);
      border-left: 3px solid var(--accent-blue);
    }

    .badge-win {
      background: rgba(16, 185, 129, 0.2);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.4);
      padding: 2px 7px;
      border-radius: 4px;
      font-weight: 700;
    }

    .badge-loss {
      background: rgba(239, 68, 68, 0.2);
      color: #f87171;
      border: 1px solid rgba(239, 68, 68, 0.4);
      padding: 2px 7px;
      border-radius: 4px;
      font-weight: 700;
    }

    .badge-skip {
      background: rgba(148, 163, 184, 0.15);
      color: #94a3b8;
      padding: 2px 7px;
      border-radius: 4px;
    }

    /* Lab Mode Bar */
    .lab-mode-bar {
      display: none;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      background: rgba(10, 19, 48, 0.96);
      border: 1px solid rgba(0, 230, 118, 0.3);
      border-radius: var(--radius-sm);
      padding: 10px 18px;
      animation: labFadeIn 0.25s ease;
    }

    .lab-mode-bar.active {
      display: flex;
    }

    @keyframes labFadeIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .lab-controls {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .lab-btn {
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 700;
      border-radius: 6px;
      border: 1px solid rgba(0, 230, 118, 0.3);
      background: rgba(0, 230, 118, 0.1);
      color: #00e676;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .lab-btn:hover {
      background: rgba(0, 230, 118, 0.25);
      border-color: #00e676;
      box-shadow: 0 0 10px rgba(0, 230, 118, 0.2);
    }

    .lab-btn:disabled {
      opacity: 0.35;
      cursor: not-allowed;
      box-shadow: none;
    }

    .lab-btn.lab-btn-reset {
      border-color: rgba(251, 191, 36, 0.4);
      background: rgba(251, 191, 36, 0.1);
      color: #fbbf24;
    }

    .lab-btn.lab-btn-reset:hover {
      background: rgba(251, 191, 36, 0.25);
      border-color: #fbbf24;
    }

    .lab-btn.lab-btn-exit {
      border-color: rgba(239, 68, 68, 0.4);
      background: rgba(239, 68, 68, 0.1);
      color: #f87171;
    }

    .lab-btn.lab-btn-exit:hover {
      background: rgba(239, 68, 68, 0.25);
      border-color: #f87171;
    }

    .lab-info {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .lab-info-tag {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 6px;
      background: rgba(0, 230, 118, 0.08);
      border: 1px solid rgba(0, 230, 118, 0.2);
      color: #a5d6a7;
    }

    .lab-info-tag b {
      color: #00e676;
    }

    .lab-badge {
      font-size: 11px;
      font-weight: 800;
      padding: 3px 9px;
      border-radius: 6px;
      letter-spacing: 0.5px;
    }

    .lab-badge-active {
      background: linear-gradient(135deg, rgba(0, 230, 118, 0.25), rgba(0, 200, 83, 0.15));
      border: 1px solid rgba(0, 230, 118, 0.5);
      color: #00e676;
      box-shadow: 0 0 12px rgba(0, 230, 118, 0.15);
    }

    .toggle-btn.active.btn-lab-mode {
      background: rgba(0, 230, 118, 0.22);
      border-color: rgba(0, 230, 118, 0.5);
      color: #00e676;
      box-shadow: 0 0 10px rgba(0, 230, 118, 0.25);
    }

    /* Loader */
    .chart-loader {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(6, 11, 40, 0.85);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 20;
      border-radius: var(--radius-md);
      gap: 12px;
    }

    .chart-spinner {
      width: 36px;
      height: 36px;
      border: 3px solid rgba(255, 255, 255, 0.1);
      border-top-color: var(--accent-blue);
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }

    /* Empty Candles Notice Banner */
    .chart-empty-notice {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(13, 22, 53, 0.96);
      border: 1px solid rgba(245, 158, 11, 0.4);
      border-radius: var(--radius-md);
      padding: 24px 30px;
      max-width: 560px;
      width: 90%;
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(245, 158, 11, 0.15);
      z-index: 15;
      text-align: center;
      display: none;
      backdrop-filter: blur(10px);
    }
    .chart-empty-notice.active {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
    }
    .chart-empty-icon {
      font-size: 38px;
      line-height: 1;
    }
    .chart-empty-title {
      font-size: 16px;
      font-weight: 700;
      color: #fbbf24;
    }
    .chart-empty-desc {
      font-size: 13px;
      color: var(--text-secondary);
      line-height: 1.6;
    }
    .chart-empty-actions {
      display: flex;
      gap: 10px;
      margin-top: 6px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .chart-empty-btn {
      padding: 8px 16px;
      border-radius: 8px;
      border: 1px solid var(--border-color);
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .chart-empty-btn:hover {
      background: rgba(255, 255, 255, 0.16);
      border-color: rgba(255, 255, 255, 0.3);
    }
    .chart-empty-btn-primary {
      background: #0075ff;
      border-color: #0075ff;
    }
    .chart-empty-btn-primary:hover {
      background: #0060d4;
    }

    /* Button View Loss Con */
    .btn-view-loss-con {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      margin-top: 18px;
      background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
      transition: all 0.2s ease;
      white-space: nowrap;
      align-self: flex-end;
    }

    .btn-view-loss-con:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(245, 158, 11, 0.55);
      filter: brightness(1.1);
    }

    .btn-view-loss-con:active {
      transform: translateY(0);
    }

    /* Button Eval Karma */
    .btn-eval-karma {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      margin-top: 18px;
      background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(139, 92, 246, 0.35);
      transition: all 0.2s ease;
      white-space: nowrap;
      align-self: flex-end;
    }
    .btn-eval-karma:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(139, 92, 246, 0.55);
      filter: brightness(1.1);
    }
    .btn-eval-karma:active {
      transform: translateY(0);
    }

    /* Karma Eval Modal */
    .karma-eval-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(2, 6, 23, 0.88);
      backdrop-filter: blur(12px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 99999;
      padding: 16px;
      animation: fadeIn 0.2s ease;
    }
    .karma-eval-modal-overlay.active {
      display: flex;
    }
    .karma-eval-modal-card {
      background: linear-gradient(160deg, #0f172a 0%, #1e1b4b 100%);
      border: 1px solid rgba(139, 92, 246, 0.3);
      border-radius: 16px;
      width: 95%;
      max-width: 920px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 40px rgba(139, 92, 246, 0.15);
      animation: slideUp 0.3s ease;
    }
    .karma-eval-modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 24px;
      border-bottom: 1px solid rgba(139, 92, 246, 0.2);
    }
    .karma-eval-modal-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      font-weight: 700;
      color: #c4b5fd;
    }
    .karma-eval-modal-body {
      padding: 20px 24px 24px;
    }
    .karma-eval-params-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 18px;
      padding: 12px 16px;
      background: rgba(139, 92, 246, 0.08);
      border: 1px solid rgba(139, 92, 246, 0.15);
      border-radius: 10px;
    }
    .karma-eval-param-tag {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 10px;
      background: rgba(139, 92, 246, 0.15);
      border: 1px solid rgba(139, 92, 246, 0.25);
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      color: #c4b5fd;
      font-family: 'JetBrains Mono', monospace;
    }
    .karma-eval-param-tag .param-val {
      color: #e9d5ff;
    }
    .karma-eval-kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 12px;
      margin-bottom: 20px;
    }
    .karma-kpi-card {
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 16px;
      text-align: center;
    }
    .karma-kpi-card.highlight-good {
      border-color: rgba(16, 185, 129, 0.4);
      background: rgba(16, 185, 129, 0.06);
    }
    .karma-kpi-card.highlight-warn {
      border-color: rgba(245, 158, 11, 0.4);
      background: rgba(245, 158, 11, 0.06);
    }
    .karma-kpi-card.highlight-bad {
      border-color: rgba(239, 68, 68, 0.4);
      background: rgba(239, 68, 68, 0.06);
    }
    .karma-kpi-label {
      font-size: 11px;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }
    .karma-kpi-value {
      font-size: 22px;
      font-weight: 800;
      color: #e2e8f0;
      font-family: 'JetBrains Mono', monospace;
    }
    .karma-kpi-sub {
      font-size: 11px;
      color: #64748b;
      margin-top: 4px;
    }
    .karma-kpi-arrow {
      font-size: 13px;
      font-weight: 700;
    }
    .karma-kpi-arrow.green { color: #34d399; }
    .karma-kpi-arrow.red { color: #f87171; }
    .karma-kpi-arrow.yellow { color: #fbbf24; }
    .karma-eval-section-title {
      font-size: 13px;
      font-weight: 700;
      color: #c4b5fd;
      margin: 16px 0 10px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .karma-eval-table-wrap {
      overflow-x: auto;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.06);
    }
    .karma-eval-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }
    .karma-eval-table th {
      background: rgba(139, 92, 246, 0.12);
      color: #c4b5fd;
      font-weight: 700;
      padding: 10px 12px;
      text-align: left;
      border-bottom: 1px solid rgba(139, 92, 246, 0.2);
      white-space: nowrap;
    }
    .karma-eval-table td {
      padding: 8px 12px;
      color: #cbd5e1;
      border-bottom: 1px solid rgba(255,255,255,0.04);
    }
    .karma-eval-table tr:hover td {
      background: rgba(139, 92, 246, 0.06);
    }
    .karma-eval-table .badge-filtered {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 700;
    }
    .badge-filtered.loss-saved {
      background: rgba(16, 185, 129, 0.2);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .badge-filtered.win-lost {
      background: rgba(245, 158, 11, 0.2);
      color: #fbbf24;
      border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .karma-eval-empty {
      text-align: center;
      padding: 40px 20px;
      color: #64748b;
      font-size: 14px;
    }
    .karma-yellow-candle-banner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 16px;
      padding: 10px 16px;
      background: linear-gradient(90deg, rgba(234, 179, 8, 0.16), rgba(234, 179, 8, 0.06));
      border: 1px solid rgba(234, 179, 8, 0.4);
      border-radius: 8px;
      font-size: 13px;
      color: #fef08a;
    }
    .btn-clear-karma-colors {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #cbd5e1;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s;
    }
    .btn-clear-karma-colors:hover {
      background: rgba(239, 68, 68, 0.25);
      border-color: rgba(239, 68, 68, 0.5);
      color: #fca5a5;
    }
    .karma-eval-table tr.row-loss-saved {
      cursor: pointer;
      transition: background 0.15s;
    }
    .karma-eval-table tr.row-loss-saved:hover {
      background: rgba(234, 179, 8, 0.15) !important;
    }


    /* Loss Con Modal Overlay & Card */
    .loss-con-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(2, 6, 23, 0.85);
      backdrop-filter: blur(10px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 99999;
      padding: 16px;
      animation: fadeIn 0.2s ease;
    }

    .loss-con-modal-overlay.active {
      display: flex;
    }

    .loss-con-modal-card {
      background: #0b1329;
      border: 1px solid rgba(245, 158, 11, 0.35);
      border-radius: 16px;
      width: 100%;
      max-width: 1200px;
      max-height: 90vh;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 35px rgba(245, 158, 11, 0.2);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .loss-con-modal-header {
      padding: 16px 24px;
      background: linear-gradient(90deg, rgba(245, 158, 11, 0.18), rgba(11, 19, 41, 0.85));
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .loss-con-modal-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .loss-con-modal-body {
      padding: 20px 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      overflow-y: auto;
      max-height: calc(90vh - 80px);
    }

    .loss-con-table-wrapper {
      overflow-x: auto;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(15, 23, 42, 0.6);
    }

    .loss-con-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
      text-align: center;
      white-space: nowrap;
    }

    .loss-con-table th {
      padding: 10px 14px;
      background: rgba(30, 41, 59, 0.95);
      color: #94a3b8;
      font-weight: 700;
      font-size: 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      border-right: 1px solid rgba(255, 255, 255, 0.05);
    }

    .loss-con-table th.asset-group-th {
      background: linear-gradient(180deg, rgba(56, 189, 248, 0.2) 0%, rgba(30, 41, 59, 0.95) 100%);
      color: #38bdf8;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 0.5px;
      border-bottom: 2px solid rgba(56, 189, 248, 0.4);
      border-left: 1px solid rgba(56, 189, 248, 0.2);
      border-right: 1px solid rgba(56, 189, 248, 0.2);
    }

    .loss-con-table td {
      padding: 10px 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      border-right: 1px solid rgba(255, 255, 255, 0.05);
      color: #cbd5e1;
    }

    .loss-con-table tr:hover td {
      background: rgba(255, 255, 255, 0.04);
    }

    .loss-con-highlight {
      color: #fde047 !important;
      font-weight: 800 !important;
      background: rgba(234, 179, 8, 0.18);
      border-radius: 4px;
      padding: 2px 7px;
      display: inline-block;
    }

    .loss-con-summary-bar {
      display: flex;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      padding: 12px 18px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 13px;
    }

    .loss-con-summary-item {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .loss-con-summary-label {
      color: #94a3b8;
    }

    .loss-con-summary-val {
      font-weight: 700;
      color: #fff;
    }
  </style>
</head>
<body>
  <!-- Floating Toast Notification -->
  <div id="config-toast" class="config-toast">
    <span id="config-toast-icon">✅</span>
    <span id="config-toast-msg">บันทึกการตั้งค่าแล้ว</span>
  </div>

  <div class="chart-page-container">
    
    <!-- Top Bar -->
    <div class="chart-topbar">
      <div class="chart-title-area">
        <a href="index.php#analysis_trade_hist" class="back-btn" title="กลับสู่หน้า Analysis Trade History">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
          กลับสู่ Dashboard
        </a>
        <h1 class="chart-main-title">
          <span class="symbol-pill" id="title-symbol"><?php echo htmlspecialchars($assetCode); ?></span>
          <span>Trade Candlestick Chart</span>
        </h1>
        <span class="meta-tag" id="meta-server">Server [<b><?php echo $serverCode; ?></b>]</span>
        <span class="meta-tag" id="meta-round">Round #<b><?php echo $tradeRoundNo; ?></b></span>
        <span class="meta-tag" id="meta-strategy">Strategy: <b id="val-strategy">Loading...</b></span>
        <span class="meta-tag" id="meta-date">Date: <b><?php echo htmlspecialchars($date); ?></b></span>
      </div>

      <div class="status-badge">
        <span class="status-dot" id="ws-status-dot"></span>
        <span id="ws-status-text">Connecting candles...</span>
      </div>
    </div>

    <!-- Filter Control Bar (VPS, Date, Asset, Round) -->
    <div class="chart-filter-bar">
      <div class="filter-controls-wrap">
        <!-- VPS Input/Select -->
        <div class="filter-item">
          <label for="filter-vps" class="filter-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zm0 7h16c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2zM6 7h2v2H6V7zm0 7h2v2H6v-2zm0 7h2v2H6v-2z"/></svg>
            VPS Server:
          </label>
          <select id="filter-vps" class="filter-select">
            <option value="">กำลังโหลดเซิร์ฟเวอร์...</option>
          </select>
        </div>

        <!-- Date Input/Select -->
        <div class="filter-item" style="flex: 1.3 1 240px;">
          <label for="filter-date" class="filter-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
            Trading Date:
          </label>
          <div class="filter-date-group">
            <select id="filter-date" class="filter-select">
              <option value="">กำลังโหลดวันที่...</option>
            </select>
            <input type="date" id="filter-date-picker" class="filter-date-input" title="หรือเลือกวันที่จากปฏิทิน">
          </div>
        </div>

        <!-- Asset Input/Select -->
        <div class="filter-item">
          <label for="filter-asset" class="filter-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
            Asset / Symbol:
          </label>
          <select id="filter-asset" class="filter-select">
            <option value="">กำลังโหลดสินทรัพย์...</option>
          </select>
        </div>

        <!-- Round Input/Select -->
        <div class="filter-item">
          <label for="filter-round" class="filter-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
            Round:
          </label>
          <select id="filter-round" class="filter-select">
            <option value="0">✨ ทุกรอบของวัน (All Rounds - ทั้งวัน)</option>
          </select>
        </div>

        <!-- Load Button -->
        <button type="button" id="btn-load-filter" class="btn-load-filter" title="โหลดข้อมูลกราฟตามตัวเลือกที่กำหนด">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
          โหลดกราฟ (Load)
        </button>

        <!-- Sync Today Button (00:05 to Now) -->
        <button type="button" id="btn-sync-today" class="btn-sync-today" title="ดึงข้อมูลล่าสุดทั้งแท่งเทียนและรายการเทรด (00:05 - now) จาก Deriv และ VPS เข้าสู่ระบบ">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M11 21h-1l1-7H7.5c-.88 0-.33-.75-.31-.78C8.48 10.94 10.42 7.54 13 3h1l-1 7h3.5c.49 0 .56.33.47.51l-.07.15C12.96 17.55 11 21 11 21z"/></svg>
          ⚡ ดึงข้อมูลล่าสุด (00:05 - now)
        </button>

        <!-- View Loss Con Button -->
        <button type="button" id="btn-view-loss-con" class="btn-view-loss-con" title="ดูสรุป Max Loss Con / Max Win Con ทุกรอบของ VPS และวันที่ที่เลือก">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
          📊 View Loss Con
        </button>

        <!-- Karma Evaluation Button -->
        <button type="button" id="btn-eval-karma" class="btn-eval-karma" title="ประเมินผลว่า Karma Filter (Choppiness Combined) ช่วยลด Loss ได้เท่าไร">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-1 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
          ⚖️ ประเมินผล Karma
        </button>
      </div>
    </div>

    <!-- Source Database Tables Bar (แสดงตารางที่หน้านี้ต้องใช้) -->
    <div class="source-tables-bar" id="sourceTablesBar">
      <div class="tables-bar-header">
        <span class="tables-bar-title">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 3C7.58 3 4 4.79 4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7c0-2.21-3.58-4-8-4zm0 2c3.87 0 6 1.5 6 2s-2.13 2-6 2-6-1.5-6-2 2.13-2 6-2zm6 5.7c-.88.48-2.24.9-3.88 1.12C13.3 11.93 12.66 12 12 12s-1.3-.07-2.12-.18C8.24 11.6 6.88 11.18 6 10.7V8.53C7.45 9.44 9.61 10 12 10s4.55-.56 6-1.47v2.17zm0 4.5c-.88.48-2.24.9-3.88 1.12C13.3 16.43 12.66 16.5 12 16.5s-1.3-.07-2.12-.18C8.24 16.1 6.88 15.68 6 15.2v-2.17c1.45.91 3.61 1.47 6 1.47s4.55-.56 6-1.47v2.17z"/></svg>
          DATABASE TABLES USED:
        </span>
        <span class="tables-bar-subtitle">ตารางที่หน้านี้ดึงข้อมูลมาแสดงผล (คลิกปุ่มเพื่อดูรายละเอียด Schema & Query SQL)</span>
      </div>
      <div class="tables-btn-group">
        <button type="button" class="btn-table-tag tag-head" onclick="window.showTableDetailModal('tradeHead')" title="คลิกเพื่อดูรายละเอียดและ SQL ของ tradeHead">
          <span class="table-tag-icon">📋</span>
          <span class="table-tag-name">tradeHead</span>
          <span class="table-tag-badge">Metadata รอบเทรด</span>
        </button>
        <button type="button" class="btn-table-tag tag-trades" onclick="window.showTableDetailModal('vpsTradeData')" title="คลิกเพื่อดูรายละเอียดและ SQL ของ vpsTradeData">
          <span class="table-tag-icon">⚡</span>
          <span class="table-tag-name">vpsTradeData</span>
          <span class="table-tag-badge">รายการไม้เทรด</span>
        </button>
        <button type="button" class="btn-table-tag tag-vps" onclick="window.showTableDetailModal('vpsMaster')" title="คลิกเพื่อดูรายละเอียดและ SQL ของ vpsMaster">
          <span class="table-tag-icon">🖥️</span>
          <span class="table-tag-name">vpsMaster</span>
          <span class="table-tag-badge">VPS Server Info</span>
        </button>
        <button type="button" class="btn-table-tag tag-fallback" onclick="window.showTableDetailModal('derivTradeHistory')" title="คลิกเพื่อดูรายละเอียดและ SQL ของ derivTradeHistory">
          <span class="table-tag-icon">🔄</span>
          <span class="table-tag-name">derivTradeHistory</span>
          <span class="table-tag-badge">Broker Fallback</span>
        </button>
        <button type="button" class="btn-table-tag tag-candles" onclick="window.showTableDetailModal('candles')" title="คลิกเพื่อดูรายละเอียดและที่มาของ candles">
          <span class="table-tag-icon">🕯️</span>
          <span class="table-tag-name">candles</span>
          <span class="table-tag-badge">แท่งเทียน OHLC / WS</span>
        </button>
        <button type="button" class="btn-table-tag btn-sync-candles-tag" id="btn-sync-candles-top" onclick="window.triggerSyncCandles()" title="ดึงข้อมูลแท่งเทียน 1 นาทีจาก Deriv และบันทึกลงฐานข้อมูล MySQL (แนวทางที่ 2 & 3)">
          <span class="table-tag-icon">⚡</span>
          <span class="table-tag-name">ดึงแท่งเทียนเข้า DB</span>
          <span class="table-tag-badge badge-sync">Sync Deriv</span>
        </button>
        <button type="button" class="btn-table-tag tag-config" onclick="window.showTableDetailModal('page_config')" title="คลิกเพื่อดูรายละเอียดและ SQL ของ page_config">
          <span class="table-tag-icon">⚙️</span>
          <span class="table-tag-name">page_config</span>
          <span class="table-tag-badge">Layout & Settings</span>
        </button>
      </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">Total Trades</div>
        <div class="kpi-value" id="kpi-total-trades">-</div>
        <div class="kpi-sub" id="kpi-duration">Duration: -</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Win / Loss</div>
        <div class="kpi-value" style="color:#34d399;" id="kpi-win-loss">-</div>
        <div class="kpi-sub" id="kpi-win-rate">Win Rate: -%</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Net Profit ($)</div>
        <div class="kpi-value" id="kpi-total-profit">-</div>
        <div class="kpi-sub" id="kpi-skipped">Skipped: -</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Max Condition Config</div>
        <div class="kpi-value" style="color:#60a5fa;" id="kpi-max-con">W:- | L:-</div>
        <div class="kpi-sub">MaxWinCon / MaxLossCon</div>
      </div>
    </div>

    <!-- Main View Tabs: Chart Analysis vs zoneAnalysis.js Editor -->
    <div class="main-tabs-nav">
      <button type="button" class="main-tab-btn active" id="tab-btn-chart">
        <span style="font-size:15px;">📈</span>
        <span>กราฟแท่งเทียน (Chart)</span>
      </button>
      <button type="button" class="main-tab-btn" id="tab-btn-editor">
        <span style="font-size:15px;">📝</span>
        <span>แก้ไข zoneAnalysis.js</span>
        <span class="tab-badge" id="editor-dirty-badge" style="display:none;">Modified</span>
      </button>
    </div>

    <!-- Tab 1: Chart View -->
    <div class="tab-content-pane active" id="tab-pane-chart">
      <!-- Controls & Marker Toggles -->
      <div class="controls-bar">
      <div class="toggles-group">
        <span style="font-size:12px; font-weight:700; color:var(--text-secondary); margin-right:4px;">🎯 Markers:</span>
        <button type="button" class="toggle-btn active btn-win-con" id="btn-toggle-win-con" title="เปิด/ปิด การแสดง Marker winCon">
          🟢 winCon Markers
        </button>
        <button type="button" class="toggle-btn active btn-loss-con" id="btn-toggle-loss-con" title="เปิด/ปิด การแสดง Marker lossCon">
          🔴 lossCon Markers
        </button>
        <button type="button" class="toggle-btn btn-all-trades" id="btn-toggle-all-trades" title="แสดง Trades ทั้งหมดรวมทั้งที่ Skipped">
          🔘 All Signals
        </button>
        <button type="button" class="toggle-btn btn-label-mode" id="btn-toggle-label-mode" title="สลับรูปแบบข้อความบน Marker">
          🏷️ Label: Con Count
        </button>
        <button type="button" class="toggle-btn btn-tooltip active" id="btn-toggle-tooltip" title="เปิด/ปิด การแสดง Tooltip รายละเอียดการเทรด">
          💬 Tooltip
        </button>
        <div class="bb-settings-anchor">
          <button type="button" class="toggle-btn btn-choppy-combined" id="btn-toggle-choppy-combined" title="เปิด/ปิด การแรเงาโซน Choppiness Combined (Macro + Micro 15M)">
            🌊 Choppy Combined
          </button>
          <button type="button" class="bb-settings-btn" id="btn-choppy-combined-settings" title="ตั้งค่า Choppy Combined">⚙️</button>
          <div class="bb-popover" id="choppy-combined-popover">
            <div class="bb-popover-title">🌊 Choppy Combined Settings</div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">Combine Mode</span>
              <select class="bb-popover-select" id="choppy-input-mode">
                <option value="or" selected>OR (Macro หรือ Micro - แนะนำ)</option>
                <option value="and">AND (ทั้งสองพร้อมกัน)</option>
                <option value="microOnly">Micro Only (1M Candles)</option>
                <option value="macroOnly">Macro Only (CHOP 15M)</option>
              </select>
            </div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">Group Size (นาที)</span>
              <input type="number" class="bb-popover-input" id="choppy-input-groupsize" value="15" min="2" max="60">
            </div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">CHOP Threshold</span>
              <input type="number" class="bb-popover-input" id="choppy-input-chopthreshold" value="61.8" step="0.5" min="30" max="90">
            </div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">ER Threshold</span>
              <input type="number" class="bb-popover-input" id="choppy-input-erthreshold" value="0.35" step="0.05" min="0.05" max="0.9">
            </div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">Body Ratio Max</span>
              <input type="number" class="bb-popover-input" id="choppy-input-bodyratio" value="0.30" step="0.05" min="0.05" max="0.8">
            </div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">Min Color Switches</span>
              <input type="number" class="bb-popover-input" id="choppy-input-switches" value="3" min="1" max="15">
            </div>
            <div class="bb-popover-actions">
              <button type="button" class="bb-popover-btn primary" id="btn-save-choppy-settings">ตกลง</button>
            </div>
          </div>
        </div>
        <div class="ath-switch-wrap" title="เปิด/ปิด การแสดง Marker รหัส code_no บนแท่งเทียน">
          <span class="ath-switch-title">🏷️ Marker code_no:</span>
          <label class="ath-switch">
            <input type="checkbox" id="switch-code-no-marker" checked>
            <span class="ath-slider"></span>
          </label>
          <span class="ath-switch-text" id="switch-code-no-marker-text">ON</span>
        </div>
        <div class="ath-switch-wrap" title="เปิด/ปิด การแสดง Tooltip รายละเอียดการเทรดบนแท่งเทียน">
          <span class="ath-switch-title">💬 Tooltip:</span>
          <label class="ath-switch">
            <input type="checkbox" id="switch-tooltip" checked>
            <span class="ath-slider"></span>
          </label>
          <span class="ath-switch-text" id="switch-tooltip-text">ON</span>
        </div>
        <div class="ath-switch-wrap" title="เปิด/ปิด การวาดเส้น Entry/Exit Spot เมื่อคลิกแท่งเทียน">
          <span class="ath-switch-title">📏 Spot Lines:</span>
          <label class="ath-switch">
            <input type="checkbox" id="switch-spot-lines">
            <span class="ath-slider"></span>
          </label>
          <span class="ath-switch-text off" id="switch-spot-lines-text">OFF</span>
        </div>
        <button type="button" class="toggle-btn btn-mark-zone" id="btn-toggle-mark-zone" title="เปิด/ปิด โหมดวาด Mark Zone เมื่อคลิกแท่งเทียน (ขอบเขตบน=max แท่งก่อนหน้า, ขอบเขตล่าง=min แท่งที่คลิก)">
          📐 Mark Zone
        </button>
        <button type="button" class="toggle-btn btn-analyze-zones" id="btn-toggle-analyze-zones" title="เปิด/ปิด หน้าต่าง Analyze Zones">
          📊 Analyze Zones
        </button>

        <!-- Bollinger Bands Toggle + Settings -->
        <button type="button" class="toggle-btn btn-bollinger" id="btn-toggle-bollinger" title="เปิด/ปิด Bollinger Bands overlay">
          📈 Bollinger Bands
        </button>
        <div class="bb-settings-anchor">
          <button type="button" class="bb-settings-btn" id="btn-bb-settings" title="ตั้งค่า Bollinger Bands">⚙️</button>
          <div class="bb-popover" id="bb-popover">
            <div class="bb-popover-title">⚙️ Bollinger Bands Settings</div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">MA Type</span>
              <select class="bb-popover-select" id="bb-input-matype">
                <option value="sma" selected>SMA</option>
                <option value="ema">EMA</option>
                <option value="hma">HMA</option>
              </select>
            </div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">Period</span>
              <input type="number" class="bb-popover-input" id="bb-input-period" value="20" min="2" max="200">
            </div>
            <div class="bb-popover-row">
              <span class="bb-popover-label">Std Dev Multiplier</span>
              <input type="number" class="bb-popover-input" id="bb-input-stddev" value="2" min="0.1" max="5" step="0.1">
            </div>
            <div class="bb-squeeze-row">
              <span class="bb-popover-label">🟣 Squeeze Zone</span>
              <div style="display:flex; align-items:center; gap:6px;">
                <label class="ath-switch">
                  <input type="checkbox" id="bb-switch-squeeze">
                  <span class="ath-slider"></span>
                </label>
                <span class="ath-switch-text off" id="bb-squeeze-text" style="min-width:28px;">OFF</span>
              </div>
            </div>
            <div class="bb-popover-row" id="bb-squeeze-threshold-row" style="display:none;">
              <span class="bb-popover-label">Squeeze Percentile (%)</span>
              <input type="number" class="bb-popover-input" id="bb-input-squeeze-pct" value="20" min="5" max="50" step="1">
            </div>
            <div class="bb-popover-row" id="bb-squeeze-lookback-row" style="display:none;">
              <span class="bb-popover-label">Lookback Window</span>
              <input type="number" class="bb-popover-input" id="bb-input-squeeze-lookback" value="120" min="20" max="500" step="1">
            </div>
            <div class="bb-flat-choppy-row">
              <span class="bb-popover-label" title="แรเงา Zone เมื่อเส้นกลาง BB แบนราบและผ่ากลางแท่งเทียน">⚖️ Flat / Choppy Zone</span>
              <div style="display:flex; align-items:center; gap:6px;">
                <label class="ath-switch">
                  <input type="checkbox" id="bb-switch-flat-choppy">
                  <span class="ath-slider"></span>
                </label>
                <span class="ath-switch-text off" id="bb-flat-choppy-text" style="min-width:28px;">OFF</span>
              </div>
            </div>
            <div class="bb-popover-row" id="bb-flat-slope-row" style="display:none;">
              <span class="bb-popover-label" title="เกณฑ์ความชันสูงสุดของเส้นกลาง (slope % ต่อแท่ง)">Slope Threshold (%)</span>
              <input type="number" class="bb-popover-input" id="bb-input-flat-slope" value="0.008" min="0.001" max="0.1" step="0.001">
            </div>
            <div class="bb-popover-row" id="bb-flat-minbars-row" style="display:none;">
              <span class="bb-popover-label" title="จำนวนแท่งต่อเนื่องขั้นต่ำที่ผ่ากลางเส้น">Min Piercing Bars</span>
              <input type="number" class="bb-popover-input" id="bb-input-flat-minbars" value="3" min="2" max="15" step="1">
            </div>
            <button type="button" class="bb-apply-btn" id="btn-bb-apply">✓ Apply Settings</button>
          </div>
        </div>
        <div class="ath-switch-wrap" title="เปิด/ปิด แรเงา Zone เมื่อเส้นกลาง BB แบนราบและผ่ากลางแท่งเทียน (Choppy / Flat Zone)">
          <span class="ath-switch-title">⚖️ Flat/Choppy:</span>
          <label class="ath-switch">
            <input type="checkbox" id="switch-flat-choppy">
            <span class="ath-slider"></span>
          </label>
          <span class="ath-switch-text off" id="switch-flat-choppy-text">OFF</span>
        </div>
      </div>

      <div class="chart-actions">
        <button type="button" class="action-btn" id="btn-fit-chart" title="ปรับมุมมองให้พอดีกับกราฟทั้งหมด">
          Fit Content
        </button>
        <button type="button" class="action-btn" id="btn-first-trade" title="เลื่อนไปที่จุดเทรดแรก">
          ⏮ First Trade
        </button>
        <button type="button" class="action-btn" id="btn-last-trade" title="เลื่อนไปที่จุดเทรดสุดท้าย">
          ⏭ Last Trade
        </button>
        <button type="button" class="action-btn btn-clear-zones" id="btn-clear-zones" title="ลบ Mark Zone ทั้งหมดออกจากกราฟ">
          🗑️ Clear Zones <span class="zone-count-badge" id="zone-count-badge" style="display:none;">0</span>
        </button>
        <button type="button" class="toggle-btn btn-lab-mode" id="btn-toggle-lab-mode" title="เปิด/ปิด Lab Mode จำลอง real-time แบบ step-by-step">
          🧪 Lab Mode
        </button>
        <button type="button" class="action-btn btn-save-config" id="btn-save-config" title="บันทึกการตั้งค่าทั้งหมดลง MySQL database (pageConfig)">
          💾 บันทึกค่า
        </button>
        <span id="config-status-text" class="config-status-text"></span>
      </div>
    </div>

    <!-- Lab Mode Floating Control Bar -->
    <div class="lab-mode-bar" id="lab-mode-bar">
      <div class="lab-badge">🧪 Lab Mode</div>
      <div class="lab-controls">
        <button type="button" class="lab-btn lab-btn-prev5" id="lab-btn-prev5" title="ถอยกลับ 5 แท่ง (Shift+←)">⏮ -5</button>
        <button type="button" class="lab-btn" id="lab-btn-prev" title="ถอยกลับ 1 แท่ง (←)">◀ Prev</button>
        <button type="button" class="lab-btn" id="lab-btn-next" title="เพิ่มแท่งถัดไป 1 แท่ง (→)">Next ▶</button>
        <button type="button" class="lab-btn" id="lab-btn-next5" title="เพิ่ม 5 แท่ง (Shift+→)">+5 ⏭</button>
        <button type="button" class="lab-btn lab-btn-reset" id="lab-btn-reset" title="กลับไปจุดที่ตัดครั้งแรก">🔄 Reset</button>
        <button type="button" class="lab-btn lab-btn-exit" id="lab-btn-exit" title="ปิด Lab Mode คืนค่ากราฟ">✕ Exit Lab</button>
      </div>
      <div class="lab-info">
        <span class="lab-info-tag">📊 แท่งที่: <b id="lab-info-idx">0</b> / <b id="lab-info-total">0</b></span>
        <span class="lab-info-tag" id="lab-info-squeeze">🟣 Squeeze: <b>-</b></span>
        <span class="lab-info-tag">💡 คลิกแท่งเทียนเพื่อตั้งจุดเริ่มต้น</span>
      </div>
    </div>

    <!-- Candlestick Chart Card -->
    <div class="chart-card">
      <div id="chart-container"></div>
      
      <!-- Floating Draggable Analyze Zones Panel (div analyzeZones) -->
      <div class="analyze-zones-panel" id="analyzeZones" style="display: none;">
        <div class="analyze-zones-header" id="analyze-zones-header">
          <div class="analyze-zones-title">
            <span>📊</span>
            <span>Analyze Zones</span>
          </div>
          <button type="button" class="analyze-zones-close-btn" id="btn-close-analyze-zones" title="ปิดหน้าต่าง">✕</button>
        </div>
        <div class="analyze-zones-body" id="analyzeZonesBody">
          <!-- div ย่อยสำหรับแสดงผลการวิเคราะห์ -->
          <div class="analyze-zones-result" id="analyzeZonesResult">
            <div class="analyze-zones-placeholder">
              <div style="font-size: 18px; margin-bottom: 4px; opacity: 0.7;">🔍</div>
              <div style="color: #94a3b8; font-weight: 500;">รอผลการวิเคราะห์โซน</div>
              <div style="color: #64748b; font-size: 11px; margin-top: 2px;">ผลการวิเคราะห์จะแสดงในส่วนนี้</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Crosshair Inspection Tooltip -->
      <div class="chart-tooltip" id="chart-tooltip">
        <div class="tt-header">
          <span id="tt-time">-</span>
          <span id="tt-badge" class="badge-win" style="display:none;">WIN</span>
        </div>
        <div class="tt-ohlc">
          <span>O: <b id="tt-open">-</b></span>
          <span>H: <b id="tt-high">-</b></span>
          <span>L: <b id="tt-low">-</b></span>
          <span>C: <b id="tt-close">-</b></span>
        </div>
        <!-- Choppy Combined summary row on every candle -->
        <div class="tt-chop-box" id="tt-chop-box" style="display:none; font-size:11px; margin-top:4px; padding-top:4px; border-top:1px solid rgba(255,255,255,0.08);">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="color:#94a3b8; font-size:10px;">🌊 Choppy 15M:</span>
            <span id="tt-chop-badge"></span>
          </div>
          <div id="tt-chop-metrics" style="font-family:monospace; font-size:10px; color:#c084fc; text-align:right; margin-top:2px;"></div>
        </div>
        <div class="tt-trade" id="tt-trade-info" style="display:none;">
          <!-- Populated when candle has trade -->
        </div>
      </div>

      <!-- Loading Overlay -->
      <div class="chart-loader" id="chart-loader">
        <div class="chart-spinner"></div>
        <div style="font-size:14px; font-weight:600; color:#fff;" id="loader-msg">กำลังดึงข้อมูลการเทรดและแท่งเทียน...</div>
      </div>

      <!-- Empty Candles / No Spot Notice -->
      <div class="chart-empty-notice" id="chart-empty-notice">
        <div class="chart-empty-icon" id="empty-notice-icon">⚠️</div>
        <div class="chart-empty-title" id="empty-notice-title">ไม่พบข้อมูลแท่งเทียน (Candlestick Data)</div>
        <div class="chart-empty-desc" id="empty-notice-desc">
          ไม่มีประวัติแท่งเทียน 1 นาทีในระบบ และข้อมูลการเทรดชุดนี้ไม่มีราคา Spot สำหรับสร้างแท่งเทียนจำลอง
        </div>
        <div class="chart-empty-actions" id="empty-notice-actions">
          <button type="button" class="chart-empty-btn chart-empty-btn-sync" id="btn-empty-sync-deriv" onclick="window.triggerSyncCandles()">⚡ ซิงค์แท่งเทียนจาก Deriv ลง DB</button>
          <button type="button" class="chart-empty-btn" id="btn-empty-timeline" onclick="window.forceTradeTimelineMode()">📊 สลับไปโหมด Trade Timeline</button>
          <button type="button" class="chart-empty-btn chart-empty-btn-primary" id="btn-empty-switch-date" style="display:none;">📅 สลับไปยังวันที่มีรอบนี้</button>
          <button type="button" class="chart-empty-btn" onclick="location.reload();">🔄 ลองโหลดใหม่</button>
        </div>
      </div>
    </div>

    <!-- Bottom Interactive Trades Table -->
    <div class="trades-card">
      <div class="trades-header">
        <div class="trades-title">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="var(--accent-blue)"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
          <span>รายการเทรดในรอบนี้ (คลิกแถวเพื่อกระโดดไปยังแท่งเทียนบนกราฟ)</span>
        </div>
        <span class="meta-tag" id="trades-count-tag">0 Trades</span>
      </div>

      <div class="trades-table-wrap">
        <table class="trades-table" id="trades-table">
          <thead>
            <tr>
              <th># No</th>
              <th>Round</th>
              <th>เวลาซื้อ (Purchase Time)</th>
              <th>Color</th>
              <th>Action</th>
              <th>code_no</th>
              <th>Strategy</th>
              <th>ผลลัพธ์ (Win/Loss)</th>
              <th>winCon</th>
              <th>lossCon</th>
              <th>เงินเทรด ($)</th>
              <th>กำไรสุทธิ ($)</th>
              <th>Balance ($)</th>
              <th>Entry Spot</th>
              <th>Exit Spot</th>
              <th>Diff Spot</th>
            </tr>
          </thead>
          <tbody id="trades-tbody">
            <tr>
              <td colspan="16" style="text-align:center; padding:30px; color:var(--text-tertiary);">กำลังโหลดข้อมูล...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    </div><!-- End Tab 1: Chart View -->

    <!-- Tab 2: zoneAnalysis.js Code Editor -->
    <div class="tab-content-pane" id="tab-pane-editor">
      <div class="code-editor-card">
        <div class="code-editor-header">
          <div class="code-editor-file-info">
            <span style="font-size:18px;">📄</span>
            <span class="code-editor-filename">dashboard/js/zoneAnalysis.js</span>
            <span class="code-editor-meta" id="editor-meta">Lines: - | Size: -</span>
          </div>
          <div class="code-editor-actions">
            <span class="code-editor-status saved" id="editor-status">● พร้อมใช้งาน</span>
            <button type="button" class="code-editor-btn btn-editor-reload" id="btn-reload-zone-analysis" title="โหลดเนื้อหาไฟล์ใหม่จากดิสก์">
              🔄 โหลดใหม่
            </button>
            <button type="button" class="code-editor-btn btn-editor-save" id="btn-save-zone-analysis" title="บันทึกลงไฟล์ zoneAnalysis.js (Ctrl+S)">
              💾 บันทึกไฟล์ (Save)
            </button>
          </div>
        </div>
        <div class="zone-code-editor-wrap">
          <textarea id="zoneAnalysisEditor" class="zone-code-editor" spellcheck="false" placeholder="กำลังโหลดไฟล์ zoneAnalysis.js..."></textarea>
        </div>
        <div class="code-editor-footer" style="display:flex; justify-content:space-between; align-items:center; font-size:11px; color:var(--text-tertiary); padding-top:6px;">
          <span>💡 คีย์ลัด: <b>Ctrl+S</b> เพื่อบันทึกไฟล์ทันที | กด <b>Tab</b> เพื่อเว้นวรรค 2 spaces</span>
          <span id="editor-last-saved">ยังไม่ได้บันทึกในเซสชันนี้</span>
        </div>
      </div>
    </div><!-- End Tab 2: Editor View -->

  </div>

  <!-- Loss Con Summary Modal Overlay -->
  <div id="loss-con-modal" class="loss-con-modal-overlay">
    <div class="loss-con-modal-card">
      <div class="loss-con-modal-header">
        <div class="loss-con-modal-title">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="#f59e0b"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
          <span id="loss-con-modal-title-text">📊 สรุป Loss Con & Win Con ประจำวัน</span>
        </div>
        <button type="button" class="table-modal-close" id="btn-close-loss-con-modal" title="ปิดหน้าต่าง">✕</button>
      </div>
      <div class="loss-con-modal-body">
        <div id="loss-con-summary-bar" class="loss-con-summary-bar" style="display:none;">
          <div class="loss-con-summary-item">
            <span class="loss-con-summary-label">VPS:</span>
            <span class="loss-con-summary-val" id="loss-con-sum-vps">-</span>
          </div>
          <div class="loss-con-summary-item">
            <span class="loss-con-summary-label">วันที่:</span>
            <span class="loss-con-summary-val" id="loss-con-sum-date">-</span>
          </div>
          <div class="loss-con-summary-item">
            <span class="loss-con-summary-label">จำนวนรอบ:</span>
            <span class="loss-con-summary-val" id="loss-con-sum-rounds">-</span>
          </div>
          <div class="loss-con-summary-item">
            <span class="loss-con-summary-label">กำไรรวมทุกรอบ:</span>
            <span class="loss-con-summary-val" id="loss-con-sum-profit" style="font-size:14px;">-</span>
          </div>
        </div>

        <div class="loss-con-table-wrapper">
          <table class="loss-con-table" id="loss-con-table">
            <thead id="loss-con-thead">
              <!-- Dynamically populated -->
            </thead>
            <tbody id="loss-con-tbody">
              <tr>
                <td style="padding: 40px; color: var(--text-tertiary);">กำลังโหลดข้อมูลสรุป Loss Con...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Karma Evaluation Modal -->
  <div id="karma-eval-modal" class="karma-eval-modal-overlay">
    <div class="karma-eval-modal-card">
      <div class="karma-eval-modal-header">
        <div class="karma-eval-modal-title">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="#a855f7"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-1 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8z"/></svg>
          <span id="karma-eval-modal-title-text">⚖️ ประเมินผล Karma Filter (Choppiness Combined)</span>
        </div>
        <button type="button" class="table-modal-close" id="btn-close-karma-eval" title="ปิดหน้าต่าง">✕</button>
      </div>
      <div class="karma-eval-modal-body">
        <div id="karma-eval-params-bar" class="karma-eval-params-bar"></div>
        <div id="karma-eval-kpi-grid" class="karma-eval-kpi-grid"></div>
        <div id="karma-eval-detail-section"></div>
      </div>
    </div>
  </div>

  <script>
  (function () {
    'use strict';

    // Query parameters
    var serverCode = <?php echo json_encode($serverCode); ?>;
    var tradeRoundNo = <?php echo json_encode($tradeRoundNo); ?>;
    var assetCode = <?php echo json_encode($assetCode); ?>;
    var tradeDate = <?php echo json_encode($date); ?>;

    // Timezone offset: UTC+7 (Thailand) in seconds
    var TZ_OFFSET_SEC = 7 * 3600;

    // State
    var chart = null;
    var candleSeries = null;
    var allCandles = [];
    var allTrades = [];
    var karmaProtectedCandleTimes = {};
    var karmaProtectedTrades = {};
    var tradeByTimeMap = {};
    var selectedTradeId = null;
    var selectedCandleTime = null;
    var summaryMinTime = 0;
    var summaryMaxTime = 0;
    var isTimelineFallback = false;
    var serverObjectsMap = {};
    var currentAutoLoadVps = null;
    var currentAutoLoadDate = '';
    var currentAutoLoadServerCode = 0;
    window.isAutoLoading = false;

    // Toggles state
    var showWinCon = true;
    var showLossCon = true;
    var showAllTrades = false;
    var showCodeNoMarker = true;
    var showTooltip = true;
    var showSpotLines = false;
    var showMarkZone = false;
    var showBollinger = false;
    var bbPeriod = 20;
    var bbStdDev = 2;
    var bbMaType = 'sma'; // 'sma', 'ema', 'hma'
    var showSqueeze = false;
    var squeezePct = 20; // percentile threshold for squeeze detection
    var squeezeLookback = 120; // rolling window size for causal percentile
    var showFlatChoppy = false;
    var flatSlopeThreshold = 0.008; // % change of middle band slope per candle (default 0.008%)
    var flatMinBars = 3; // minimum consecutive candles piercing middle band
    var labelMode = 'con'; // 'con', 'pnl', or 'codeno'
    var showChoppyCombined = false;
    var choppyCombineMode = 'or'; // 'or', 'and', 'microOnly', 'macroOnly'
    var choppyGroupSize = 15;
    var choppyChopThreshold = 61.8;
    var choppyBodyRatioThreshold = 0.3;
    var choppyErThreshold = 0.35;
    var choppyMinSwitches = 3;
    var choppyCombinedResults = [];
    var choppyCombinedMap = {};
    var choppyCombinedPrimitive = null;

    // Bollinger Band series references
    var bbUpperSeries = null;
    var bbMiddleSeries = null;
    var bbLowerSeries = null;

    // Squeeze & Flat/Choppy zone primitives
    var squeezeZonePrimitive = null;
    var flatChoppyZonePrimitive = null;

    // Lab Mode state
    var labMode = false;
    var fullDataCandle = [];
    var labCutIndex = -1;
    var labOriginalCutIndex = -1;

    // Spot price lines state
    var activeEntryLine = null;
    var activeExitLine = null;

    // Mark Zone state
    var showMarkZone = false;
    var markZones = [];           // Array of { topLine, bottomLine, primitive }
    var btnToggleMarkZone = null;
    var btnClearZones = null;
    var zoneCountBadge = null;

    // Analyze Zones panel state
    var showAnalyzeZones = false;
    var panelAnalyzeZones = null;
    var btnToggleAnalyzeZones = null;
    var btnCloseAnalyzeZones = null;

    // DOM Elements
    var chartContainer = document.getElementById('chart-container');
    var chartLoader = document.getElementById('chart-loader');
    var loaderMsg = document.getElementById('loader-msg');
    var wsStatusDot = document.getElementById('ws-status-dot');
    var wsStatusText = document.getElementById('ws-status-text');

    var btnToggleWinCon = document.getElementById('btn-toggle-win-con');
    var btnToggleLossCon = document.getElementById('btn-toggle-loss-con');
    var btnToggleAllTrades = document.getElementById('btn-toggle-all-trades');
    var btnToggleLabelMode = document.getElementById('btn-toggle-label-mode');
    var btnToggleTooltip = document.getElementById('btn-toggle-tooltip');
    var btnToggleChoppyCombined = document.getElementById('btn-toggle-choppy-combined');
    var ttChopBox = document.getElementById('tt-chop-box');
    var ttChopBadge = document.getElementById('tt-chop-badge');
    var ttChopMetrics = document.getElementById('tt-chop-metrics');
    var switchCodeNoMarker = document.getElementById('switch-code-no-marker');
    var switchCodeNoMarkerText = document.getElementById('switch-code-no-marker-text');
    var switchTooltip = document.getElementById('switch-tooltip');
    var switchTooltipText = document.getElementById('switch-tooltip-text');
    var switchSpotLines = document.getElementById('switch-spot-lines');
    var switchSpotLinesText = document.getElementById('switch-spot-lines-text');
    var switchFlatChoppy = document.getElementById('switch-flat-choppy');
    var switchFlatChoppyText = document.getElementById('switch-flat-choppy-text');
    var btnFitChart = document.getElementById('btn-fit-chart');
    var btnFirstTrade = document.getElementById('btn-first-trade');
    var btnLastTrade = document.getElementById('btn-last-trade');
    btnToggleMarkZone = document.getElementById('btn-toggle-mark-zone');
    btnClearZones = document.getElementById('btn-clear-zones');
    zoneCountBadge = document.getElementById('zone-count-badge');
    panelAnalyzeZones = document.getElementById('analyzeZones');
    btnToggleAnalyzeZones = document.getElementById('btn-toggle-analyze-zones');
    btnCloseAnalyzeZones = document.getElementById('btn-close-analyze-zones');

    var tooltip = document.getElementById('chart-tooltip');
    var ttTime = document.getElementById('tt-time');
    var ttBadge = document.getElementById('tt-badge');
    var ttOpen = document.getElementById('tt-open');
    var ttHigh = document.getElementById('tt-high');
    var ttLow = document.getElementById('tt-low');
    var ttClose = document.getElementById('tt-close');
    var ttTradeInfo = document.getElementById('tt-trade-info');

    var tradesTbody = document.getElementById('trades-tbody');
    var tradesCountTag = document.getElementById('trades-count-tag');

    // Filter Elements
    var filterVps = document.getElementById('filter-vps');
    var filterDate = document.getElementById('filter-date');
    var filterDatePicker = document.getElementById('filter-date-picker');
    var filterAsset = document.getElementById('filter-asset');
    var filterRound = document.getElementById('filter-round');
    var btnLoadFilter = document.getElementById('btn-load-filter');
    var btnSyncToday = document.getElementById('btn-sync-today');
    var roundTrueDate = '';

    /**
     * 1. Initialize Lightweight Chart
     */
    function initChart() {
      chart = LightweightCharts.createChart(chartContainer, {
        layout: {
          background: { type: 'solid', color: '#070c27' },
          textColor: '#94a3b8',
          fontSize: 12,
          fontFamily: "'Plus Jakarta Sans', -apple-system, sans-serif"
        },
        grid: {
          vertLines: { color: 'rgba(255, 255, 255, 0.05)' },
          horzLines: { color: 'rgba(255, 255, 255, 0.05)' }
        },
        crosshair: {
          mode: LightweightCharts.CrosshairMode.Normal,
          vertLine: {
            color: 'rgba(0, 117, 255, 0.5)',
            width: 1,
            style: LightweightCharts.LineStyle.Dashed
          },
          horzLine: {
            color: 'rgba(0, 117, 255, 0.5)',
            width: 1,
            style: LightweightCharts.LineStyle.Dashed
          }
        },
        rightPriceScale: {
          borderColor: 'rgba(255, 255, 255, 0.1)',
          autoScale: true,
          scaleMargins: {
            top: 0.15,
            bottom: 0.15
          }
        },
        timeScale: {
          borderColor: 'rgba(255, 255, 255, 0.1)',
          timeVisible: true,
          secondsVisible: false
        }
      });

      // Add Candlestick series
      candleSeries = chart.addCandlestickSeries({
        upColor: '#10b981',
        downColor: '#ef4444',
        borderVisible: false,
        wickUpColor: '#10b981',
        wickDownColor: '#ef4444'
      });

      // Crosshair inspection move
      chart.subscribeCrosshairMove(handleCrosshairMove);

      // Click handler for spot lines
      chart.subscribeClick(handleChartClick);

      // Window resize
      window.addEventListener('resize', function () {
        if (chart && chartContainer) {
          chart.applyOptions({
            width: chartContainer.clientWidth,
            height: chartContainer.clientHeight
          });
        }
      });
    }

    /**
     * Filter Bar Controller
     */
    function fetchFilterOptions(isInitial) {
      showLoader('กำลังโหลดตัวเลือกตัวกรอง (VPS, Date, Asset)...');
      var url = '../php/api_analysis_trade_hist.php?action=get_chart_filter_options' +
                '&serverCode=' + encodeURIComponent(serverCode || 0) +
                '&date=' + encodeURIComponent(tradeDate || '') +
                '&assetCode=' + encodeURIComponent(assetCode || '');

      return fetch(url)
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (!data.success) throw new Error(data.error || 'Failed to load filters');

          // 1. Populate VPS
          if (filterVps && data.servers) {
            serverObjectsMap = {};
            filterVps.innerHTML = '';
            data.servers.forEach(function (s) {
              serverObjectsMap[s.serverCode] = s;
              var opt = document.createElement('option');
              opt.value = s.serverCode;
              var name = s.serverName || ('Server #' + s.serverCode);
              opt.textContent = '[' + s.serverCode + '] ' + name + (s.totalHeadRounds ? ' (' + s.totalHeadRounds + ' rnd)' : '');
              filterVps.appendChild(opt);
            });
            if (serverCode > 0) {
              filterVps.value = serverCode;
            } else if (filterVps.options.length > 0) {
              filterVps.selectedIndex = 0;
              serverCode = parseInt(filterVps.value, 10);
            }
          }

          // 2. Populate Dates
          if (filterDate && data.dates) {
            filterDate.innerHTML = '';
            data.dates.forEach(function (d) {
              var opt = document.createElement('option');
              opt.value = d.date_val;
              opt.textContent = d.date_display + ' (' + d.total_rounds + ' Rnd, ' + d.total_assets + ' Ast)';
              filterDate.appendChild(opt);
            });
            if (tradeDate && tradeDate !== '') {
              filterDate.value = tradeDate;
              if (filterDatePicker) filterDatePicker.value = tradeDate;
            } else if (filterDate.options.length > 0) {
              filterDate.selectedIndex = 0;
              tradeDate = filterDate.value;
              if (filterDatePicker) filterDatePicker.value = tradeDate;
            }
          }

          // 3. Populate Assets
          updateAssetDropdown(data.availableAssets, data.allAssets);

          // 4. Populate Rounds
          updateRoundDropdown(data.availableRounds);

          // Update header badges
          updateHeaderBadges();

          if (isInitial) {
            loadData();
          }
        })
        .catch(function (err) {
          console.error('Error loading filter options:', err);
          if (isInitial) loadData();
        });
    }

    function updateAssetDropdown(availableAssets, allAssets) {
      if (!filterAsset) return;
      var assetsToUse = (availableAssets && availableAssets.length > 0) ? availableAssets : (allAssets || []);
      var prevVal = filterAsset.value;
      filterAsset.innerHTML = '';
      assetsToUse.forEach(function (ast) {
        var opt = document.createElement('option');
        opt.value = ast;
        opt.textContent = ast;
        filterAsset.appendChild(opt);
      });
      if (assetCode && assetsToUse.indexOf(assetCode) !== -1) {
        filterAsset.value = assetCode;
      } else if (prevVal && assetsToUse.indexOf(prevVal) !== -1) {
        filterAsset.value = prevVal;
        assetCode = prevVal;
      } else if (filterAsset.options.length > 0) {
        filterAsset.selectedIndex = 0;
        assetCode = filterAsset.value;
      }
    }

    function updateRoundDropdown(availableRounds) {
      if (!filterRound) return;
      var prevVal = parseInt(filterRound.value, 10);
      filterRound.innerHTML = '';
      var autoOpt = document.createElement('option');
      autoOpt.value = '0';
      autoOpt.textContent = '✨ ทุกรอบของวัน (All Rounds - ทั้งวัน)';
      filterRound.appendChild(autoOpt);

      var hasMatch = false;
      if (availableRounds && availableRounds.length > 0) {
        availableRounds.forEach(function (r) {
          var rNo = parseInt(r.tradeRoundNo, 10);
          if (rNo <= 0) return; // Skip 0 as autoOpt is already added
          var opt = document.createElement('option');
          opt.value = String(rNo);
          opt.textContent = 'Round #' + rNo + (r.usestrategyCode ? ' [' + r.usestrategyCode + ']' : '');
          filterRound.appendChild(opt);
          if (tradeRoundNo > 0 && rNo === tradeRoundNo) hasMatch = true;
          if (prevVal > 0 && rNo === prevVal) hasMatch = true;
        });
      }
      if (tradeRoundNo > 0 && hasMatch) {
        filterRound.value = String(tradeRoundNo);
      } else if (prevVal > 0 && hasMatch) {
        filterRound.value = String(prevVal);
      } else {
        filterRound.value = '0';
      }
    }

    function refreshContextualFilters() {
      var sVal = parseInt(filterVps ? filterVps.value : serverCode, 10) || 0;
      var dVal = (filterDate ? filterDate.value : '') || (filterDatePicker ? filterDatePicker.value : '') || tradeDate || '';
      var aVal = (filterAsset ? filterAsset.value : '') || assetCode || '';

      var url = '../php/api_analysis_trade_hist.php?action=get_chart_filter_options' +
                '&serverCode=' + encodeURIComponent(sVal) +
                '&date=' + encodeURIComponent(dVal) +
                '&assetCode=' + encodeURIComponent(aVal);

      fetch(url)
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (!data.success) return;
          updateAssetDropdown(data.availableAssets, data.allAssets);
          updateRoundDropdown(data.availableRounds);
        })
        .catch(function (err) { console.error('Contextual filter refresh error:', err); });
    }

    function updateHeaderBadges() {
      var symbolPill = document.getElementById('title-symbol');
      if (symbolPill) symbolPill.textContent = assetCode || 'N/A';
      var metaServer = document.getElementById('meta-server');
      if (metaServer) metaServer.innerHTML = 'Server [<b>' + (serverCode || 0) + '</b>]';
      var metaRound = document.getElementById('meta-round');
      if (metaRound) {
        metaRound.innerHTML = tradeRoundNo > 0 ? ('Round #<b>' + tradeRoundNo + '</b>') : 'Round: <b>All Rounds (ทุกรอบ)</b>';
      }
      var metaDate = document.getElementById('meta-date');
      if (metaDate) metaDate.innerHTML = 'Date: <b>' + (tradeDate || '-') + '</b>';
    }

    function clearChartState() {
      clearSpotLines();
      if (typeof clearAllMarkZones === 'function') clearAllMarkZones();
      var emptyNotice = document.getElementById('chart-empty-notice');
      if (emptyNotice) emptyNotice.classList.remove('active');
      allTrades = [];
      allCandles = [];
      karmaProtectedCandleTimes = {};
      karmaProtectedTrades = {};
      tradeByTimeMap = {};
      selectedTradeId = null;
      selectedCandleTime = null;
      fullDataCandle = [];
      if (candleSeries) candleSeries.setData([]);
      if (bbUpperSeries) bbUpperSeries.setData([]);
      if (bbMiddleSeries) bbMiddleSeries.setData([]);
      if (bbLowerSeries) bbLowerSeries.setData([]);
      if (tradesTbody) {
        tradesTbody.innerHTML = '<tr><td colspan="15" style="text-align:center; padding:30px; color:var(--text-tertiary);">กำลังโหลดข้อมูล...</td></tr>';
      }
      showLoader('กำลังโหลดข้อมูลการเทรดและแท่งเทียน...');
    }

    function showLoader(msg) {
      if (chartLoader) {
        chartLoader.style.display = 'flex';
        if (loaderMsg) loaderMsg.textContent = msg || 'กำลังโหลดข้อมูล...';
      }
    }

    /**
     * 2. Load Trade & Metadata from Backend API
     */
    function loadData() {
      loaderMsg.textContent = 'กำลังโหลดข้อมูลการเทรดจากฐานข้อมูล...';

      var url = '../php/api_analysis_trade_hist.php?action=get_asset_chart_data' +
                '&serverCode=' + encodeURIComponent(serverCode) +
                '&tradeRoundNo=' + encodeURIComponent(tradeRoundNo) +
                '&assetCode=' + encodeURIComponent(assetCode) +
                '&date=' + encodeURIComponent(tradeDate);

      fetch(url)
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (!data.success) throw new Error(data.error || 'Failed to fetch chart data');

          // Render metadata & KPI
          renderMetadata(data);

          allTrades = data.trades || [];

          // Precompute 1-based displayWinCon and displayLossCon so markers and displays start from 1
          var runningWin = 0;
          allTrades.forEach(function (t) {
            var status = (t.WinStatus || '').toLowerCase();
            if (status === 'win') {
              runningWin++;
              var wVal = parseInt(t.winCon, 10);
              t.displayWinCon = (!isNaN(wVal) && wVal > 0) ? wVal : runningWin;
            } else if (status === 'loss') {
              runningWin = 0;
              var lVal = parseInt(t.lossCon, 10);
              t.displayLossCon = !isNaN(lVal) ? (lVal + 1) : 1;
            }
          });

          indexTrades(allTrades);
          renderTradesTable(allTrades);

          // Store summary time bounds for manual sync
          summaryMinTime = data.summary ? parseInt(data.summary.minTime, 10) : 0;
          summaryMaxTime = data.summary ? parseInt(data.summary.maxTime, 10) : 0;

          // If candles are already stored in MySQL, use them directly
          if (data.candles && data.candles.length > 0) {
            isTimelineFallback = false;
            allCandles = data.candles.map(function (c) {
              return {
                time: c.time + TZ_OFFSET_SEC,
                open: parseFloat(c.open),
                high: parseFloat(c.high),
                low: parseFloat(c.low),
                close: parseFloat(c.close)
              };
            });
            applyCandlesAndMarkers();
            setWsStatus(true, 'MySQL DB (' + allCandles.length + ' bars)');
            showToast('✅ โหลดแท่งเทียน ' + allCandles.length + ' แท่งจากฐานข้อมูล MySQL สำเร็จ', 'success', 3200);
            hideLoader();

            // If coverage is incomplete (e.g. today, data only up to 07:00 when now is 11:40)
            if (!data.hasCoverage && (data.isToday || tradeRoundNo <= 0)) {
              showToast('⚡ ตรวจพบแท่งเทียนยังไม่ถึงเวลาปัจจุบัน — กำลังดึงแท่งเทียนเพิ่มจาก Deriv (00:05 - now)...', 'sync', 4000);
              fetchCandlesFromDeriv(data.dayStart0005 || summaryMinTime, data.dayEnd || summaryMaxTime, false);
            }
          } else {
            // แจ้งเตือนผู้ใช้ว่าใน DB ยังไม่มี candles กำลังต่อดึงจาก Deriv WS
            showToast('⚠️ ตรวจพบว่ายังไม่มีแท่งเทียนใน MySQL — กำลังดึงจาก Deriv API (00:05 - now)...', 'warn', 4500);
            fetchCandlesFromDeriv(data.dayStart0005 || summaryMinTime, data.dayEnd || summaryMaxTime, false);
          }
        })
        .catch(function (err) {
          loaderMsg.innerHTML = '<span style="color:#f87171;">เกิดข้อผิดพลาด: ' + err.message + '</span>';
          setWsStatus(false, 'Data Error');
          showToast('❌ เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + err.message, 'error', 5000);
        });
    }

    /**
     * Index trades by timeCandle epoch for instant lookup
     */
    function indexTrades(trades) {
      tradeByTimeMap = {};
      trades.forEach(function (t) {
        var tc = parseInt(t.timeCandle, 10);
        var pt = parseInt(t.purchaseTime, 10);
        var key = tc > 0 ? tc : pt;
        if (key > 0) {
          // Normalize to start of minute + shift to Thai timezone for chart alignment
          var normTime = Math.floor(key / 60) * 60 + TZ_OFFSET_SEC;
          if (!tradeByTimeMap[normTime]) tradeByTimeMap[normTime] = [];
          tradeByTimeMap[normTime].push(t);
        }
      });
    }

    /**
     * แนวทางที่ 3: Auto-Save Candles to MySQL Database
     */
    function saveCandlesToDb(symbol, srvCode, rawCandles) {
      if (!rawCandles || rawCandles.length === 0) return;
      showToast('⚡ กำลังบันทึกแท่งเทียน ' + rawCandles.length + ' แท่งลงในฐานข้อมูล MySQL...', 'sync', 3500);

      var payload = {
        symbol: symbol,
        serverCode: srvCode || 1,
        granularity: 60,
        candles: rawCandles.map(function (c) {
          return {
            epoch: c.epoch || c.time,
            open: parseFloat(c.open),
            high: parseFloat(c.high),
            low: parseFloat(c.low),
            close: parseFloat(c.close)
          };
        })
      };

      fetch('../php/save_market_data.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) {
          var count = res.candlesSaved || rawCandles.length;
          showToast('✅ ซิงค์และบันทึกแท่งเทียน ' + count + ' แท่งเข้าสู่ MySQL สำเร็จแล้ว!', 'success', 5000);
          setWsStatus(true, 'MySQL DB (Auto-cached ' + count + ' bars)');
        } else {
          showToast('⚠️ แจ้งเตือน: ไม่สามารถบันทึกลง DB ได้ (' + (res.error || 'Unknown') + ')', 'warn', 4000);
        }
      })
      .catch(function (err) {
        console.warn('saveCandlesToDb error:', err);
        showToast('⚠️ เกิดข้อผิดพลาดในการบันทึกแท่งเทียน: ' + err.message, 'warn', 4000);
      });
    }

    /**
     * Helper: Get active Deriv WebSocket URL (OTP-authenticated or modern public endpoint)
     */
    async function getDerivWsUrl() {
      try {
        var otpRes = await fetch('../php/api_deriv_account.php?action=get_ws_url');
        if (otpRes.ok) {
          var otpJson = await otpRes.json();
          if (otpJson.success && otpJson.wsUrl) {
            return otpJson.wsUrl;
          }
          if (otpJson.fallbackWsUrl) {
            return otpJson.fallbackWsUrl;
          }
        }
      } catch (e) {
        console.warn('Failed to fetch OTP WebSocket URL, using public fallback:', e);
      }
      return 'wss://api.derivws.com/trading/v1/options/ws/public';
    }

    /**
     * 3. Fetch Candles from Deriv WebSocket API (with Multi-Endpoint Fallback & Auto-Save)
     */
    async function fetchCandlesFromDeriv(minTime, maxTime, isManualTrigger) {
      setWsStatus(false, 'Deriv WS Connecting...');
      loaderMsg.textContent = 'กำลังเชื่อมต่อ Deriv API เพื่อดึงแท่งเทียนย้อนหลัง 1 นาที...';
      if (isManualTrigger) {
        showToast('⚡ กำลังเชื่อมต่อ Deriv WebSocket เพื่อดึงแท่งเทียนย้อนหลัง...', 'sync', 4000);
      }

      var nowEpoch = Math.floor(Date.now() / 1000);
      var end = maxTime > 0 ? Math.min(maxTime, nowEpoch) : nowEpoch;
      var start = minTime > 0 ? minTime : (end - 7200);

      // If full day / tradeRoundNo <= 0 or tradeDate is specified, start at 00:05:00 Bangkok time
      if (tradeDate) {
        try {
          var d0005 = Math.floor(new Date(tradeDate + 'T00:05:00+07:00').getTime() / 1000);
          if (tradeRoundNo <= 0 || !minTime || minTime > d0005) {
            start = d0005;
          }
        } catch (e) {}
      }

      // Candidate WebSocket endpoints (Prioritize modern OTP/active endpoint)
      var activeWs = await getDerivWsUrl();
      var wsUrls = [activeWs];
      if (activeWs !== 'wss://api.derivws.com/trading/v1/options/ws/public') {
        wsUrls.push('wss://api.derivws.com/trading/v1/options/ws/public');
      }

      var wsIndex = 0;

      function tryNextWs() {
        if (wsIndex >= wsUrls.length) {
          showToast('⚠️ ไม่สามารถเชื่อมต่อ Deriv WebSocket ได้ — กำลังสลับสู่โหมด Trade Timeline Fallback', 'warn', 5000);
          handleCandleFallback();
          return;
        }

        var currentUrl = wsUrls[wsIndex++];
        var ws;
        try {
          ws = new WebSocket(currentUrl);
        } catch (e) {
          tryNextWs();
          return;
        }

        var timeoutTimer = setTimeout(function () {
          try { ws.close(); } catch (e) {}
          tryNextWs();
        }, 7000);

        ws.onopen = function () {
          setWsStatus(true, 'Deriv WS Requesting...');
          var req = {
            ticks_history: assetCode,
            style: 'candles',
            granularity: 60,
            start: start,
            end: end,
            adjust_start_time: 1
          };
          ws.send(JSON.stringify(req));
        };

        ws.onmessage = function (event) {
          clearTimeout(timeoutTimer);
          try {
            var res = JSON.parse(event.data);
            if (res.error) {
              console.warn('Deriv WS error (' + currentUrl + '):', res.error);
              tryNextWs();
              return;
            }
            if (res.candles && res.candles.length > 0) {
              isTimelineFallback = false;
              var newCandles = res.candles.map(function (c) {
                return {
                  time: (c.epoch || c.time) + TZ_OFFSET_SEC,
                  open: parseFloat(c.open),
                  high: parseFloat(c.high),
                  low: parseFloat(c.low),
                  close: parseFloat(c.close)
                };
              });

              var candleMap = {};
              allCandles.forEach(function (c) { candleMap[c.time] = c; });
              newCandles.forEach(function (c) { candleMap[c.time] = c; });
              allCandles = Object.values(candleMap).sort(function (a, b) { return a.time - b.time; });

              applyCandlesAndMarkers();
              setWsStatus(true, 'Deriv WS (' + allCandles.length + ' bars)');
              hideLoader();
              showToast('✅ ซิงค์แท่งเทียน ' + res.candles.length + ' แท่งจาก Deriv WS สำเร็จ', 'success', 3500);

              // แนวทางที่ 3: บันทึกลง MySQL ทันที
              saveCandlesToDb(assetCode, serverCode, res.candles);
            } else {
              tryNextWs();
            }
          } catch (e) {
            tryNextWs();
          }
          try { ws.close(); } catch (e) {}
        };

        ws.onerror = function () {
          clearTimeout(timeoutTimer);
          tryNextWs();
        };
      }

      tryNextWs();
    }

    /**
     * แนวทางที่ 4: Fallback สลับโหมด Trade Timeline จาก derivTradeHistory / vpsTradeData
     * วาดแท่งเทียนและแกนเวลาต่อเนื่อง ไม่ปล่อยให้กราฟว่างเปล่า พร้อมแสดงตำแหน่งและผลการเทรดทุกไม้
     */
    function handleCandleFallback() {
      isTimelineFallback = true;
      setWsStatus(false, 'Trade Timeline (Fallback)');

      var emptyNotice = document.getElementById('chart-empty-notice');
      var noticeTitle = document.getElementById('empty-notice-title');
      var noticeDesc = document.getElementById('empty-notice-desc');
      var btnSwitchDate = document.getElementById('btn-empty-switch-date');

      if (!allTrades || allTrades.length === 0) {
        allCandles = [];
        if (candleSeries) candleSeries.setData([]);
        if (emptyNotice) {
          emptyNotice.classList.add('active');
          if (noticeTitle) noticeTitle.textContent = 'ไม่พบประวัติการเทรดและแท่งเทียน';
          if (noticeDesc) noticeDesc.textContent = 'ไม่มีรายการเทรดในเซิร์ฟเวอร์, รอบ และวันที่ที่เลือก';
          if (btnSwitchDate) btnSwitchDate.style.display = 'none';
        }
        hideLoader();
        return;
      }

      // We have trades! Dismiss full empty block
      if (emptyNotice) emptyNotice.classList.remove('active');

      // Sort trades chronologically
      var sortedTrades = allTrades.slice().sort(function (a, b) {
        var ta = parseInt(a.timeCandle, 10) || parseInt(a.purchaseTime, 10) || 0;
        var tb = parseInt(b.timeCandle, 10) || parseInt(b.purchaseTime, 10) || 0;
        return ta - tb;
      });

      // Find first valid reference price (entrySpot, exitSpot, barrier, or default 1000.00)
      var refPrice = 0;
      for (var i = 0; i < sortedTrades.length; i++) {
        var es = parseFloat(sortedTrades[i].entrySpot) || 0;
        var xs = parseFloat(sortedTrades[i].exitSpot) || 0;
        var br = parseFloat(sortedTrades[i].barrier) || 0;
        if (es > 0) { refPrice = es; break; }
        if (xs > 0) { refPrice = xs; break; }
        if (br > 0) { refPrice = br; break; }
      }
      var hasRealPrice = refPrice > 0;
      if (!hasRealPrice) {
        refPrice = 1000.00;
      }

      // Determine trade time boundaries
      var firstTradeTime = parseInt(sortedTrades[0].timeCandle, 10) || parseInt(sortedTrades[0].purchaseTime, 10) || 0;
      var lastTradeTime = parseInt(sortedTrades[sortedTrades.length - 1].timeCandle, 10) || parseInt(sortedTrades[sortedTrades.length - 1].purchaseTime, 10) || 0;

      var minEpoch = firstTradeTime > 0 ? (Math.floor(firstTradeTime / 60) * 60 - 300) : (summaryMinTime || 0);
      var maxEpoch = lastTradeTime > 0 ? (Math.ceil(lastTradeTime / 60) * 60 + 300) : (summaryMaxTime || (minEpoch + 3600));

      if (minEpoch <= 0 || maxEpoch <= minEpoch) {
        minEpoch = Math.floor(Date.now() / 1000) - 3600;
        maxEpoch = Math.floor(Date.now() / 1000);
      }

      // Map trades by minute timestamp (normalized)
      var tradeMinuteMap = {};
      sortedTrades.forEach(function (t) {
        var tc = parseInt(t.timeCandle, 10) || parseInt(t.purchaseTime, 10) || 0;
        if (tc > 0) {
          var norm = Math.floor(tc / 60) * 60;
          if (!tradeMinuteMap[norm]) tradeMinuteMap[norm] = [];
          tradeMinuteMap[norm].push(t);
        }
      });

      // Synthesize continuous 1-minute candle bars spanning the entire trade duration
      var synthCandles = [];
      var currentPrice = refPrice;

      for (var ep = minEpoch; ep <= maxEpoch; ep += 60) {
        var openP = currentPrice;
        var closeP = currentPrice;
        var highP = currentPrice;
        var lowP = currentPrice;

        var minuteTrades = tradeMinuteMap[ep];
        if (minuteTrades && minuteTrades.length > 0) {
          var t = minuteTrades[0];
          var rawEntry = parseFloat(t.entrySpot) || 0;
          var rawExit = parseFloat(t.exitSpot) || 0;
          var profit = parseFloat(t.ThisProfit) || 0;

          if (rawEntry > 0 && rawExit > 0) {
            openP = rawEntry;
            closeP = rawExit;
            var sp = Math.max(Math.abs(openP - closeP), openP * 0.0003);
            highP = Math.max(openP, closeP) + (sp * 0.25);
            lowP = Math.min(openP, closeP) - (sp * 0.25);
            currentPrice = closeP;
          } else if (rawEntry > 0) {
            openP = rawEntry;
            closeP = openP * (1 + (profit > 0 ? 0.0003 : (profit < 0 ? -0.0003 : 0)));
            highP = Math.max(openP, closeP) * 1.0002;
            lowP = Math.min(openP, closeP) * 0.9998;
            currentPrice = closeP;
          } else {
            var step = (profit !== 0) ? (profit > 0 ? 0.4 : -0.4) : (Math.sin(ep) * 0.15);
            closeP = openP + step;
            highP = Math.max(openP, closeP) + 0.25;
            lowP = Math.min(openP, closeP) - 0.25;
            currentPrice = closeP;
          }
        } else {
          var wave = (Math.sin(ep / 120) * (refPrice * 0.00006));
          closeP = openP + wave;
          highP = Math.max(openP, closeP) + Math.abs(wave) * 0.5;
          lowP = Math.min(openP, closeP) - Math.abs(wave) * 0.5;
          currentPrice = closeP;
        }

        synthCandles.push({
          time: ep + TZ_OFFSET_SEC,
          open: parseFloat(openP.toFixed(2)),
          high: parseFloat(highP.toFixed(2)),
          low: parseFloat(lowP.toFixed(2)),
          close: parseFloat(closeP.toFixed(2))
        });
      }

      allCandles = synthCandles;
      applyCandlesAndMarkers();

      var modeDesc = hasRealPrice ? 'Trade Spot Baseline' : 'Trade Timeline Synthesized';
      setWsStatus(true, modeDesc + ' (' + allCandles.length + ' bars)');

      if (hasRealPrice) {
        showToast('📊 สลับสู่โหมด Trade Timeline: วาดตำแหน่งการเทรด ' + allTrades.length + ' ไม้จากราคา Spot อ้างอิง', 'info', 5000);
      } else {
        showToast('📊 สลับสู่โหมด Trade Timeline: วาดตำแหน่งการเทรด ' + allTrades.length + ' ไม้บนแกนเวลา (โหมดจำลองเส้นทาง)', 'warn', 5000);
      }

      hideLoader();
    }

    /**
     * Trigger Manual Candle Sync (แนวทางที่ 2)
     */
    window.triggerSyncCandles = function () {
      showToast('⚡ เริ่มต้นซิงค์แท่งเทียน 1 นาทีจาก Deriv API...', 'sync', 3500);
      showLoader('กำลังเชื่อมต่อ Deriv API เพื่อดึงแท่งเทียนและบันทึกลง MySQL...');
      var minT = summaryMinTime || (Math.floor(Date.now() / 1000) - 7200);
      var maxT = summaryMaxTime || Math.floor(Date.now() / 1000);
      fetchCandlesFromDeriv(minT, maxT, true);
    };

    /**
     * Force Switch to Timeline Mode (แนวทางที่ 4)
     */
    window.forceTradeTimelineMode = function () {
      showToast('📊 สลับไปแสดงผลในโหมด Trade Timeline ทันที...', 'info', 3000);
      handleCandleFallback();
    };

    /**
     * 4. Apply Candles to Series & Render Markers (Supports Karma Yellow Highlighting)
     */
    function renderCandlesWithKarmaColors() {
      if (!candleSeries || allCandles.length === 0) return;
      var hasProtected = karmaProtectedCandleTimes && Object.keys(karmaProtectedCandleTimes).length > 0;
      if (!hasProtected) {
        candleSeries.setData(allCandles);
      } else {
        var colored = allCandles.map(function(c) {
          if (karmaProtectedCandleTimes[c.time]) {
            return {
              time: c.time,
              open: c.open,
              high: c.high,
              low: c.low,
              close: c.close,
              color: '#facc15',       // Vibrant Yellow (Protected Candle)
              borderColor: '#eab308', // Amber / Gold Border
              wickColor: '#facc15'    // Yellow Wick
            };
          }
          return {
            time: c.time,
            open: c.open,
            high: c.high,
            low: c.low,
            close: c.close
          };
        });
        candleSeries.setData(colored);
      }
      updateMarkers();
    }

    function applyCandlesAndMarkers() {
      if (!candleSeries || allCandles.length === 0) return;

      var emptyNotice = document.getElementById('chart-empty-notice');
      if (emptyNotice) emptyNotice.classList.remove('active');

      // Capture full data on first load for Lab Mode
      if (fullDataCandle.length === 0) {
        fullDataCandle = allCandles.slice();
      }

      renderCandlesWithKarmaColors();
      calculateChoppinessCombined();
      if (showBollinger) {
        updateBollingerBands();
      } else if (showFlatChoppy) {
        updateFlatChoppyZones();
      }
      if (showChoppyCombined) {
        updateChoppyCombinedZones();
      }
      chart.timeScale().fitContent();
    }

    /**
     * Moving Average Helpers: SMA, EMA, WMA, HMA
     */
    function calcSMA(closes, period) {
      var result = [];
      for (var i = 0; i < closes.length; i++) {
        if (i < period - 1) { result.push(null); continue; }
        var sum = 0;
        for (var j = i - period + 1; j <= i; j++) sum += closes[j];
        result.push(sum / period);
      }
      return result;
    }

    function calcEMA(closes, period) {
      var result = [];
      var k = 2 / (period + 1);
      var prev = null;
      for (var i = 0; i < closes.length; i++) {
        if (i < period - 1) { result.push(null); continue; }
        if (prev === null) {
          // Seed EMA with SMA of first 'period' values
          var sum = 0;
          for (var j = i - period + 1; j <= i; j++) sum += closes[j];
          prev = sum / period;
        } else {
          prev = closes[i] * k + prev * (1 - k);
        }
        result.push(prev);
      }
      return result;
    }

    function calcWMA(closes, period) {
      var result = [];
      var weightSum = period * (period + 1) / 2;
      for (var i = 0; i < closes.length; i++) {
        if (i < period - 1) { result.push(null); continue; }
        var wSum = 0;
        for (var j = 0; j < period; j++) {
          wSum += closes[i - period + 1 + j] * (j + 1);
        }
        result.push(wSum / weightSum);
      }
      return result;
    }

    function calcHMA(closes, period) {
      // Hull Moving Average = WMA( 2*WMA(n/2) - WMA(n), sqrt(n) )
      var halfPeriod = Math.max(Math.floor(period / 2), 1);
      var sqrtPeriod = Math.max(Math.round(Math.sqrt(period)), 1);

      var wmaHalf = calcWMA(closes, halfPeriod);
      var wmaFull = calcWMA(closes, period);

      // Build diff series: 2*WMA(n/2) - WMA(n)
      var diff = [];
      for (var i = 0; i < closes.length; i++) {
        if (wmaHalf[i] === null || wmaFull[i] === null) {
          diff.push(null);
        } else {
          diff.push(2 * wmaHalf[i] - wmaFull[i]);
        }
      }

      // Filter out nulls at the beginning, compute WMA on the non-null portion
      var firstValid = -1;
      for (var i = 0; i < diff.length; i++) {
        if (diff[i] !== null) { firstValid = i; break; }
      }
      if (firstValid < 0) return new Array(closes.length).fill(null);

      var diffClean = diff.slice(firstValid);
      var hmaClean = calcWMA(diffClean, sqrtPeriod);

      var result = new Array(firstValid).fill(null);
      for (var i = 0; i < hmaClean.length; i++) result.push(hmaClean[i]);
      return result;
    }

    /**
     * Bollinger Bands Calculation & Rendering
     */
    function calculateBollingerBands(candles, period, multiplier, maType) {
      var closes = candles.map(function (c) { return c.close; });

      // Compute MA based on selected type
      var maValues;
      if (maType === 'ema') {
        maValues = calcEMA(closes, period);
      } else if (maType === 'hma') {
        maValues = calcHMA(closes, period);
      } else {
        maValues = calcSMA(closes, period);
      }

      var upper = [], middle = [], lower = [];
      for (var i = 0; i < candles.length; i++) {
        if (maValues[i] === null) continue;
        var ma = maValues[i];

        // Standard deviation over period window (or available window for HMA)
        var windowStart = Math.max(0, i - period + 1);
        var windowLen = i - windowStart + 1;
        var sum = 0;
        for (var j = windowStart; j <= i; j++) sum += closes[j];
        var mean = sum / windowLen;
        var sqDiffSum = 0;
        for (var j = windowStart; j <= i; j++) {
          var d = closes[j] - mean;
          sqDiffSum += d * d;
        }
        var stdDev = Math.sqrt(sqDiffSum / windowLen);

        middle.push({ time: candles[i].time, value: ma });
        upper.push({ time: candles[i].time, value: ma + multiplier * stdDev });
        lower.push({ time: candles[i].time, value: ma - multiplier * stdDev });
      }
      return { upper: upper, middle: middle, lower: lower };
    }

    function updateBollingerBands() {
      if (!chart || allCandles.length < 5) {
        removeBollingerBands();
        return;
      }

      var bb = calculateBollingerBands(allCandles, bbPeriod, bbStdDev, bbMaType);

      // Create series if not yet created
      if (!bbUpperSeries) {
        bbUpperSeries = chart.addLineSeries({
          color: 'rgba(255, 152, 0, 0.6)',
          lineWidth: 1,
          lineStyle: LightweightCharts.LineStyle.Dashed,
          priceLineVisible: false,
          lastValueVisible: false,
          crosshairMarkerVisible: false,
          autoscaleInfoProvider: function() { return null; }
        });
      }
      if (!bbMiddleSeries) {
        bbMiddleSeries = chart.addLineSeries({
          color: 'rgba(255, 183, 77, 0.85)',
          lineWidth: 2,
          priceLineVisible: false,
          lastValueVisible: false,
          crosshairMarkerVisible: false,
          autoscaleInfoProvider: function() { return null; }
        });
      }
      if (!bbLowerSeries) {
        bbLowerSeries = chart.addLineSeries({
          color: 'rgba(255, 152, 0, 0.6)',
          lineWidth: 1,
          lineStyle: LightweightCharts.LineStyle.Dashed,
          priceLineVisible: false,
          lastValueVisible: false,
          crosshairMarkerVisible: false,
          autoscaleInfoProvider: function() { return null; }
        });
      }

      bbUpperSeries.setData(bb.upper);
      bbMiddleSeries.setData(bb.middle);
      bbLowerSeries.setData(bb.lower);

      // Update squeeze zones if enabled
      if (showSqueeze) {
        updateSqueezeZones(bb);
      } else {
        removeSqueezeZones();
      }

      // Update flat/choppy zones if enabled
      if (showFlatChoppy) {
        updateFlatChoppyZones(bb);
      } else {
        removeFlatChoppyZones();
      }
    }

    function removeBollingerBands() {
      if (bbUpperSeries) {
        try { chart.removeSeries(bbUpperSeries); } catch (e) {}
        bbUpperSeries = null;
      }
      if (bbMiddleSeries) {
        try { chart.removeSeries(bbMiddleSeries); } catch (e) {}
        bbMiddleSeries = null;
      }
      if (bbLowerSeries) {
        try { chart.removeSeries(bbLowerSeries); } catch (e) {}
        bbLowerSeries = null;
      }
      removeSqueezeZones();
      if (!showFlatChoppy) {
        removeFlatChoppyZones();
      }
    }

    /**
     * Squeeze Zone Detection & Rendering
     * Uses ROLLING WINDOW percentile (causal) — suitable for real-time trading.
     * At each candle, the bandwidth is compared to the percentile of
     * the preceding 'lookback' candles — so detection has 0-candle lag.
     */
    function detectSqueezeZones(bb, percentile, lookback) {
      if (!bb.upper || bb.upper.length < 2) return [];

      // Calculate normalized bandwidth for each point
      var bandwidths = [];
      for (var i = 0; i < bb.upper.length; i++) {
        var bw = bb.upper[i].value - bb.lower[i].value;
        var mid = bb.middle[i].value;
        var normalizedBw = mid > 0 ? bw / mid : bw;
        bandwidths.push({
          time: bb.upper[i].time,
          bw: normalizedBw
        });
      }

      // Determine squeeze at each candle via rolling window percentile
      var squeezeFlags = [];
      for (var i = 0; i < bandwidths.length; i++) {
        // Window = preceding 'lookback' values (not including current)
        var windowStart = Math.max(0, i - lookback);
        var windowEnd = i; // exclusive — use [windowStart, i) as history
        if (windowEnd - windowStart < 5) {
          // Not enough history to calculate meaningful percentile
          squeezeFlags.push(false);
          continue;
        }

        // Collect window bandwidths and sort
        var windowBws = [];
        for (var j = windowStart; j < windowEnd; j++) {
          windowBws.push(bandwidths[j].bw);
        }
        windowBws.sort(function (a, b) { return a - b; });

        var pctIdx = Math.floor(windowBws.length * percentile / 100);
        var threshold = windowBws[Math.min(pctIdx, windowBws.length - 1)];

        squeezeFlags.push(bandwidths[i].bw <= threshold);
      }

      // Find contiguous squeeze zones
      var zones = [];
      var inSqueeze = false;
      var zoneStart = null;

      for (var i = 0; i < squeezeFlags.length; i++) {
        if (squeezeFlags[i] && !inSqueeze) {
          inSqueeze = true;
          zoneStart = i;
        } else if (!squeezeFlags[i] && inSqueeze) {
          inSqueeze = false;
          zones.push({
            startTime: bandwidths[zoneStart].time,
            endTime: bandwidths[i - 1].time,
            startIdx: zoneStart,
            endIdx: i - 1
          });
        }
      }
      // Close trailing zone
      if (inSqueeze && zoneStart !== null) {
        zones.push({
          startTime: bandwidths[zoneStart].time,
          endTime: bandwidths[bandwidths.length - 1].time,
          startIdx: zoneStart,
          endIdx: bandwidths.length - 1
        });
      }

      return zones;
    }

    function updateSqueezeZones(bb) {
      removeSqueezeZones();
      if (!chart || !candleSeries || !bb.upper || bb.upper.length < 2) return;

      var zones = detectSqueezeZones(bb, squeezePct, squeezeLookback);
      if (zones.length === 0) return;

      squeezeZonePrimitive = new SqueezeZonesPrimitive(zones);
      candleSeries.attachPrimitive(squeezeZonePrimitive);
    }

    function removeSqueezeZones() {
      if (squeezeZonePrimitive && candleSeries) {
        try { candleSeries.detachPrimitive(squeezeZonePrimitive); } catch (e) {}
        squeezeZonePrimitive = null;
      }
    }

    /**
     * SqueezeZonesPrimitive — draws vertical semi-transparent bands for squeeze regions
     */
    function SqueezeZonesPrimitive(zones) {
      this._zones = zones;
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
      this._paneView = new SqueezeZonesPaneView(this);
    }

    SqueezeZonesPrimitive.prototype.attached = function (param) {
      this._series = param.series;
      this._chart = param.chart;
      this._requestUpdate = param.requestUpdate;
    };

    SqueezeZonesPrimitive.prototype.detached = function () {
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
    };

    SqueezeZonesPrimitive.prototype.updateAllViews = function () {
      this._paneView.update(this);
    };

    SqueezeZonesPrimitive.prototype.paneViews = function () {
      return [this._paneView];
    };

    function SqueezeZonesPaneView(primitive) {
      this._primitive = primitive;
      this._rects = []; // [{x0, x1, height}]
    }

    SqueezeZonesPaneView.prototype.update = function (source) {
      if (!source) source = this._primitive;
      var chart = source._chart;
      var series = source._series;
      if (!chart || !series) return;

      var timeScale = chart.timeScale();
      var chartEl = chart.chartElement ? chart.chartElement() : null;
      var chartHeight = chartEl ? chartEl.clientHeight : 600;

      this._rects = [];
      var zones = source._zones;

      for (var i = 0; i < zones.length; i++) {
        var z = zones[i];
        // Convert time to x coordinate
        var x0 = timeScale.timeToCoordinate(z.startTime);
        var x1 = timeScale.timeToCoordinate(z.endTime);
        if (x0 === null || x1 === null) continue;

        // Add half-bar padding on each side
        var barSpacing = timeScale.options ? timeScale.options().barSpacing || 6 : 6;
        this._rects.push({
          x0: x0 - barSpacing / 2,
          x1: x1 + barSpacing / 2,
          height: chartHeight
        });
      }
    };

    SqueezeZonesPaneView.prototype.renderer = function () {
      var rects = this._rects;

      return {
        draw: function (target) {
          target.useBitmapCoordinateSpace(function (scope) {
            var ctx = scope.context;
            var ratio = scope.horizontalPixelRatio;
            var vRatio = scope.verticalPixelRatio;

            ctx.fillStyle = 'rgba(170, 0, 255, 0.08)';

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;
              var w = px1 - px0;
              if (w < 1 * ratio) w = 1 * ratio;
              ctx.fillRect(px0, 0, w, r.height * vRatio);
            }

            // Draw subtle top/bottom border lines for squeeze zones
            ctx.strokeStyle = 'rgba(170, 0, 255, 0.25)';
            ctx.lineWidth = 1 * ratio;
            ctx.setLineDash([3 * ratio, 3 * ratio]);

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;

              // Left border of squeeze zone
              ctx.beginPath();
              ctx.moveTo(px0, 0);
              ctx.lineTo(px0, r.height * vRatio);
              ctx.stroke();

              // Right border
              ctx.beginPath();
              ctx.moveTo(px1, 0);
              ctx.lineTo(px1, r.height * vRatio);
              ctx.stroke();
            }

            ctx.setLineDash([]);
          });
        }
      };
    };

    /**
     * Flat / Choppy Zone Detection & Rendering
     * Identifies zones where:
     * 1. Bollinger middle band slope is nearly horizontal:
     *    |mid[i] - mid[i-1]| / mid[i-1] * 100 <= slopeThresholdPct
     * 2. Candle pierces / straddles the middle band:
     *    candle.low <= mid[i] && mid[i] <= candle.high
     * 3. Condition is sustained for at least minConsecutiveBars
     */
    function detectFlatChoppyZones(bb, slopeThresholdPct, minBars) {
      if (!bb || !bb.middle || bb.middle.length < 2 || !allCandles || allCandles.length === 0) return [];

      var midMap = {};
      for (var i = 0; i < bb.middle.length; i++) {
        midMap[bb.middle[i].time] = bb.middle[i].value;
      }

      var matchFlags = [];
      var candleTimes = [];
      for (var i = 0; i < allCandles.length; i++) {
        var c = allCandles[i];
        candleTimes.push(c.time);

        var mid = midMap[c.time];
        var prevC = i > 0 ? allCandles[i - 1] : null;
        var prevMid = prevC ? midMap[prevC.time] : null;

        if (mid === undefined || prevMid === undefined || mid === null || prevMid === null) {
          matchFlags.push(false);
          continue;
        }

        var slopePct = (Math.abs(mid - prevMid) / prevMid) * 100;
        var pierces = (c.low <= mid && mid <= c.high);

        matchFlags.push(slopePct <= slopeThresholdPct && pierces);
      }

      var zones = [];
      var startIdx = null;
      for (var i = 0; i < matchFlags.length; i++) {
        if (matchFlags[i]) {
          if (startIdx === null) startIdx = i;
        } else {
          if (startIdx !== null) {
            var len = i - startIdx;
            if (len >= minBars) {
              zones.push({
                startTime: candleTimes[startIdx],
                endTime: candleTimes[i - 1],
                startIdx: startIdx,
                endIdx: i - 1,
                len: len
              });
            }
            startIdx = null;
          }
        }
      }
      if (startIdx !== null) {
        var len = matchFlags.length - startIdx;
        if (len >= minBars) {
          zones.push({
            startTime: candleTimes[startIdx],
            endTime: candleTimes[candleTimes.length - 1],
            startIdx: startIdx,
            endIdx: candleTimes.length - 1,
            len: len
          });
        }
      }

      return zones;
    }

    function updateFlatChoppyZones(bb) {
      removeFlatChoppyZones();
      if (!chart || !candleSeries || allCandles.length < 5) return;

      // Defensive: If all candle prices are identical, avoid fake whole-chart choppy zone
      var firstClose = allCandles[0].close;
      var hasVariation = allCandles.some(function (c) { return Math.abs(c.close - firstClose) > 0.0001; });
      if (!hasVariation) return;

      if (!bb) {
        bb = calculateBollingerBands(allCandles, bbPeriod, bbStdDev, bbMaType);
      }
      var zones = detectFlatChoppyZones(bb, flatSlopeThreshold, flatMinBars);
      if (zones.length === 0) return;

      flatChoppyZonePrimitive = new FlatChoppyZonesPrimitive(zones);
      candleSeries.attachPrimitive(flatChoppyZonePrimitive);
    }

    function removeFlatChoppyZones() {
      if (flatChoppyZonePrimitive && candleSeries) {
        try { candleSeries.detachPrimitive(flatChoppyZonePrimitive); } catch (e) {}
        flatChoppyZonePrimitive = null;
      }
    }

    /**
     * FlatChoppyZonesPrimitive — draws vertical semi-transparent amber bands for flat/choppy regions
     */
    function FlatChoppyZonesPrimitive(zones) {
      this._zones = zones;
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
      this._paneView = new FlatChoppyZonesPaneView(this);
    }

    FlatChoppyZonesPrimitive.prototype.attached = function (param) {
      this._series = param.series;
      this._chart = param.chart;
      this._requestUpdate = param.requestUpdate;
    };

    FlatChoppyZonesPrimitive.prototype.detached = function () {
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
    };

    FlatChoppyZonesPrimitive.prototype.updateAllViews = function () {
      this._paneView.update(this);
    };

    FlatChoppyZonesPrimitive.prototype.paneViews = function () {
      return [this._paneView];
    };

    function FlatChoppyZonesPaneView(primitive) {
      this._primitive = primitive;
      this._rects = [];
    }

    FlatChoppyZonesPaneView.prototype.update = function (source) {
      if (!source) source = this._primitive;
      var chart = source._chart;
      var series = source._series;
      if (!chart || !series) return;

      var timeScale = chart.timeScale();
      var chartEl = chart.chartElement ? chart.chartElement() : null;
      var chartHeight = chartEl ? chartEl.clientHeight : 600;

      this._rects = [];
      var zones = source._zones;

      for (var i = 0; i < zones.length; i++) {
        var z = zones[i];
        var x0 = timeScale.timeToCoordinate(z.startTime);
        var x1 = timeScale.timeToCoordinate(z.endTime);
        if (x0 === null || x1 === null) continue;

        var barSpacing = timeScale.options ? timeScale.options().barSpacing || 6 : 6;
        this._rects.push({
          x0: x0 - barSpacing / 2,
          x1: x1 + barSpacing / 2,
          height: chartHeight,
          len: z.len
        });
      }
    };

    FlatChoppyZonesPaneView.prototype.renderer = function () {
      var rects = this._rects;

      return {
        draw: function (target) {
          target.useBitmapCoordinateSpace(function (scope) {
            var ctx = scope.context;
            var ratio = scope.horizontalPixelRatio;
            var vRatio = scope.verticalPixelRatio;

            // Warm amber translucent background
            ctx.fillStyle = 'rgba(245, 158, 11, 0.12)';

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;
              var w = px1 - px0;
              if (w < 1 * ratio) w = 1 * ratio;
              ctx.fillRect(px0, 0, w, r.height * vRatio);
            }

            // Top banner stripe
            ctx.fillStyle = 'rgba(245, 158, 11, 0.28)';
            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;
              var w = px1 - px0;
              if (w < 1 * ratio) w = 1 * ratio;
              ctx.fillRect(px0, 0, w, 18 * vRatio);
            }

            // Dashed border lines
            ctx.strokeStyle = 'rgba(245, 158, 11, 0.50)';
            ctx.lineWidth = 1 * ratio;
            ctx.setLineDash([4 * ratio, 3 * ratio]);

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;

              // Left border
              ctx.beginPath();
              ctx.moveTo(px0, 0);
              ctx.lineTo(px0, r.height * vRatio);
              ctx.stroke();

              // Right border
              ctx.beginPath();
              ctx.moveTo(px1, 0);
              ctx.lineTo(px1, r.height * vRatio);
              ctx.stroke();
            }

            // Draw zone label in top banner
            ctx.font = 'bold ' + Math.round(10 * ratio) + 'px "Segoe UI", sans-serif';
            ctx.fillStyle = '#fbbf24';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.setLineDash([]);

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;
              var midX = (px0 + px1) / 2;
              if (px1 - px0 >= 45 * ratio) {
                ctx.fillText('⚖️ CHOPPY (' + r.len + ')', midX, 9 * vRatio);
              } else if (px1 - px0 >= 16 * ratio) {
                ctx.fillText('⚖️', midX, 9 * vRatio);
              }
            }
          });
        }
      };
    };

    /**
     * Choppy Combined (Macro + Micro) Calculation & Zone Primitive
     */
    function calculateChoppinessCombined() {
      if (!allCandles || allCandles.length < 5 || typeof window.analyzeChoppiness !== 'function') return;

      var smallCandles = allCandles.map(function(c) {
        return {
          epoch: c.time,
          open: c.open,
          high: c.high,
          low: c.low,
          close: c.close
        };
      });

      try {
        choppyCombinedResults = window.analyzeChoppiness(smallCandles, {
          groupSize: choppyGroupSize || 15,
          chopPeriod: 14,
          chopThreshold: choppyChopThreshold || 61.8,
          bodyRatioThreshold: choppyBodyRatioThreshold || 0.3,
          erThreshold: choppyErThreshold || 0.35,
          minSwitches: choppyMinSwitches || 3,
          combineMode: choppyCombineMode || 'or',
          includeIncomplete: true
        });
      } catch (e) {
        console.warn('analyzeChoppiness error:', e);
        choppyCombinedResults = [];
      }

      choppyCombinedMap = {};
      if (Array.isArray(choppyCombinedResults)) {
        choppyCombinedResults.forEach(function(big) {
          var count = big.candleCount || 15;
          for (var k = 0; k < count; k++) {
            var subEpoch = big.epoch + (k * 60);
            choppyCombinedMap[subEpoch] = big;
          }
        });
      }

      if (showChoppyCombined) {
        updateChoppyCombinedZones();
      }
    }

    function updateChoppyCombinedZones() {
      removeChoppyCombinedZones();
      if (!chart || !candleSeries || !choppyCombinedResults || choppyCombinedResults.length === 0) return;

      var zones = [];
      var currentZone = null;

      for (var i = 0; i < choppyCombinedResults.length; i++) {
        var item = choppyCombinedResults[i];
        if (item.isChoppy) {
          var count = item.candleCount || 15;
          var itemStart = item.epoch;
          var itemEnd = item.epoch + ((count - 1) * 60);

          if (!currentZone) {
            currentZone = {
              startTime: itemStart,
              endTime: itemEnd,
              count: count
            };
          } else {
            currentZone.endTime = itemEnd;
            currentZone.count += count;
          }
        } else {
          if (currentZone) {
            zones.push(currentZone);
            currentZone = null;
          }
        }
      }
      if (currentZone) {
        zones.push(currentZone);
      }

      if (zones.length === 0) return;

      choppyCombinedPrimitive = new ChoppyCombinedZonesPrimitive(zones);
      candleSeries.attachPrimitive(choppyCombinedPrimitive);
    }

    function removeChoppyCombinedZones() {
      if (choppyCombinedPrimitive && candleSeries) {
        try { candleSeries.detachPrimitive(choppyCombinedPrimitive); } catch (e) {}
        choppyCombinedPrimitive = null;
      }
    }

    function ChoppyCombinedZonesPrimitive(zones) {
      this._zones = zones;
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
      this._paneView = new ChoppyCombinedZonesPaneView(this);
    }

    ChoppyCombinedZonesPrimitive.prototype.attached = function (param) {
      this._series = param.series;
      this._chart = param.chart;
      this._requestUpdate = param.requestUpdate;
    };

    ChoppyCombinedZonesPrimitive.prototype.detached = function () {
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
    };

    ChoppyCombinedZonesPrimitive.prototype.updateAllViews = function () {
      this._paneView.update(this);
    };

    ChoppyCombinedZonesPrimitive.prototype.paneViews = function () {
      return [this._paneView];
    };

    function ChoppyCombinedZonesPaneView(primitive) {
      this._primitive = primitive;
      this._rects = [];
    }

    ChoppyCombinedZonesPaneView.prototype.update = function (source) {
      if (!source) source = this._primitive;
      var chart = source._chart;
      var series = source._series;
      if (!chart || !series) return;

      var timeScale = chart.timeScale();
      var chartEl = chart.chartElement ? chart.chartElement() : null;
      var chartHeight = chartEl ? chartEl.clientHeight : 600;

      this._rects = [];
      var zones = source._zones;

      for (var i = 0; i < zones.length; i++) {
        var z = zones[i];
        var x0 = timeScale.timeToCoordinate(z.startTime);
        var x1 = timeScale.timeToCoordinate(z.endTime);
        if (x0 === null || x1 === null) continue;

        var barSpacing = timeScale.options ? timeScale.options().barSpacing || 6 : 6;
        this._rects.push({
          x0: x0 - barSpacing / 2,
          x1: x1 + barSpacing / 2,
          height: chartHeight,
          count: z.count
        });
      }
    };

    ChoppyCombinedZonesPaneView.prototype.renderer = function () {
      var rects = this._rects;

      return {
        draw: function (target) {
          target.useBitmapCoordinateSpace(function (scope) {
            var ctx = scope.context;
            var ratio = scope.horizontalPixelRatio;
            var vRatio = scope.verticalPixelRatio;

            // Violet translucent background for Choppy Combined
            ctx.fillStyle = 'rgba(168, 85, 247, 0.14)';

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;
              var w = px1 - px0;
              if (w < 1 * ratio) w = 1 * ratio;
              ctx.fillRect(px0, 0, w, r.height * vRatio);
            }

            // Top banner stripe
            ctx.fillStyle = 'rgba(168, 85, 247, 0.32)';
            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;
              var w = px1 - px0;
              if (w < 1 * ratio) w = 1 * ratio;
              ctx.fillRect(px0, 0, w, 18 * vRatio);
            }

            // Dashed border lines
            ctx.strokeStyle = 'rgba(192, 132, 252, 0.65)';
            ctx.lineWidth = 1 * ratio;
            ctx.setLineDash([4 * ratio, 3 * ratio]);

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;

              // Left border
              ctx.beginPath();
              ctx.moveTo(px0, 0);
              ctx.lineTo(px0, r.height * vRatio);
              ctx.stroke();

              // Right border
              ctx.beginPath();
              ctx.moveTo(px1, 0);
              ctx.lineTo(px1, r.height * vRatio);
              ctx.stroke();
            }

            // Draw zone label in top banner
            ctx.font = 'bold ' + Math.round(10 * ratio) + 'px "Segoe UI", sans-serif';
            ctx.fillStyle = '#e9d5ff';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.setLineDash([]);

            for (var i = 0; i < rects.length; i++) {
              var r = rects[i];
              var px0 = r.x0 * ratio;
              var px1 = r.x1 * ratio;
              var midX = (px0 + px1) / 2;
              if (px1 - px0 >= 55 * ratio) {
                ctx.fillText('🌊 CHOP COMBINED (' + r.count + 'm)', midX, 9 * vRatio);
              } else if (px1 - px0 >= 20 * ratio) {
                ctx.fillText('🌊', midX, 9 * vRatio);
              }
            }
          });
        }
      };
    };

    /**
     * 5. Generate and Update Markers according to Active Toggles
     */
    function updateMarkers() {
      if (!candleSeries) return;

      var markers = [];

      allTrades.forEach(function (t) {
        var tc = parseInt(t.timeCandle, 10) || parseInt(t.purchaseTime, 10);
        if (!tc) return;

        var candleTime = Math.floor(tc / 60) * 60 + TZ_OFFSET_SEC;

        // Skip trades that occur after the current visible cutoff
        if (allCandles.length > 0 && candleTime > allCandles[allCandles.length - 1].time) {
          return;
        }

        // Check if candleTime exists in allCandles
        var hasCandle = allCandles.some(function (c) { return c.time === candleTime; });
        if (!hasCandle && allCandles.length > 0) {
          // Snap to closest candle only within 3 minutes (180s)
          var closest = allCandles.reduce(function (prev, curr) {
            return Math.abs(curr.time - candleTime) < Math.abs(prev.time - candleTime) ? curr : prev;
          });
          if (Math.abs(closest.time - candleTime) <= 180) {
            candleTime = closest.time;
          } else {
            return;
          }
        }

        var status = (t.WinStatus || '').toLowerCase();
        var isWin = status === 'win';
        var isLoss = status === 'loss';
        var isSkipped = status === 'skipped';

        var isCall = (t.thisAction || '').toLowerCase() === 'call';
        var isPut = (t.thisAction || '').toLowerCase() === 'put';

        // 1. Trade action marker (Win / Loss / Skip)
        var showTradeMarker = true;
        if (isWin && !showWinCon && !showAllTrades) showTradeMarker = false;
        if (isLoss && !showLossCon && !showAllTrades) showTradeMarker = false;
        if (isSkipped && !showAllTrades) showTradeMarker = false;

        if (showTradeMarker) {
          var position = isCall ? 'belowBar' : (isPut ? 'aboveBar' : 'inBar');
          var shape = isCall ? 'arrowUp' : (isPut ? 'arrowDown' : 'circle');

          var color = '#94a3b8';
          if (isWin) color = '#10b981';
          else if (isLoss) {
            var lConVal = t.displayLossCon !== undefined ? t.displayLossCon : (!isNaN(parseInt(t.lossCon, 10)) ? (parseInt(t.lossCon, 10) + 1) : 1);
            color = (lConVal >= 5) ? '#fde047' : '#ef4444';
          }

          // Marker label text
          var text = '';
          if (labelMode === 'con') {
            if (isWin) {
              var wVal = t.displayWinCon !== undefined ? t.displayWinCon : (!isNaN(parseInt(t.winCon, 10)) && parseInt(t.winCon, 10) > 0 ? parseInt(t.winCon, 10) : 1);
              text = 'W:' + wVal;
            } else if (isLoss) {
              var lVal = t.displayLossCon !== undefined ? t.displayLossCon : (!isNaN(parseInt(t.lossCon, 10)) ? (parseInt(t.lossCon, 10) + 1) : 1);
              text = 'L:' + lVal;
            } else {
              text = 'Skip';
            }
          } else if (labelMode === 'codeno') {
            var cNo = (t.code_no !== null && t.code_no !== undefined && t.code_no !== '' && parseInt(t.code_no, 10) > 0) ? parseInt(t.code_no, 10) : '-';
            text = 'No:' + cNo;
          } else {
            // PnL & Trade #
            var pnl = parseFloat(t.ThisProfit) || 0;
            var sign = pnl >= 0 ? '+' : '';
            text = '#' + (t.tradeNo || '') + ' ' + (isWin ? 'WIN ' : (isLoss ? 'LOSS ' : '')) + sign + pnl.toFixed(2);
          }

          if (karmaProtectedTrades && karmaProtectedTrades[t.id]) {
            text = '🛡️ ' + text;
          }

          markers.push({
            time: candleTime,
            position: position,
            color: color,
            shape: shape,
            text: text,
            id: 'trade_' + t.id
          });
        }

        // 2. Dedicated code_no marker (controlled by switchShowCodeNoMarker)
        if (showCodeNoMarker) {
          var cNoVal = (t.code_no !== null && t.code_no !== undefined && t.code_no !== '' && parseInt(t.code_no, 10) > 0) ? parseInt(t.code_no, 10) : null;
          if (cNoVal !== null) {
            // Display on opposite side of entry arrow for clean visual separation
            var codePos = isCall ? 'aboveBar' : 'belowBar';
            markers.push({
              time: candleTime,
              position: codePos,
              color: '#38bdf8',
              shape: 'circle',
              text: 'No:' + cNoVal,
              id: 'codeno_' + t.id
            });
          }
        }
      });

      // 3. Selected Trade Marker (Yellow) - Only one active selected marker at a time
      var selMarkerTime = selectedCandleTime;
      var isSelCall = false;

      if (selectedTradeId) {
        var selectedTrade = null;
        for (var si = 0; si < allTrades.length; si++) {
          if (parseInt(allTrades[si].id, 10) === selectedTradeId) {
            selectedTrade = allTrades[si];
            break;
          }
        }

        if (selectedTrade) {
          isSelCall = (selectedTrade.thisAction || '').toLowerCase() === 'call';
          if (!selMarkerTime) {
            var selTc = parseInt(selectedTrade.timeCandle, 10) || parseInt(selectedTrade.purchaseTime, 10);
            if (selTc) {
              selMarkerTime = Math.floor(selTc / 60) * 60 + TZ_OFFSET_SEC;
            }
          }
        }
      }

      if (selMarkerTime) {
        var selHasCandle = allCandles.some(function (c) { return c.time === selMarkerTime; });
        if (!selHasCandle && allCandles.length > 0) {
          var closestSel = allCandles.reduce(function (prev, curr) {
            return Math.abs(curr.time - selMarkerTime) < Math.abs(prev.time - selMarkerTime) ? curr : prev;
          });
          if (Math.abs(closestSel.time - selMarkerTime) <= 180) {
            selMarkerTime = closestSel.time;
          }
        }

        var selPos = isSelCall ? 'aboveBar' : 'belowBar';

        markers.push({
          time: selMarkerTime,
          position: selPos,
          color: 'yellow',
          shape: 'circle',
          text: 'SELECTED',
          id: 'selected_marker'
        });
      }

      // Sort markers ascending by time (required by Lightweight Charts)
      markers.sort(function (a, b) { return a.time - b.time; });
      candleSeries.setMarkers(markers);
    }

    /**
     * 6. Crosshair Move Handler for Tooltip
     */
    function handleCrosshairMove(param) {
      if (!showTooltip) {
        tooltip.classList.remove('visible');
        return;
      }

      if (!param.time || !param.point || param.point.x < 0 || param.point.y < 0) {
        tooltip.classList.remove('visible');
        return;
      }

      var priceData = null;
      if (param.seriesData && typeof param.seriesData.get === 'function') {
        priceData = param.seriesData.get(candleSeries);
      } else if (param.seriesPrices && typeof param.seriesPrices.get === 'function') {
        priceData = param.seriesPrices.get(candleSeries);
      }

      if (!priceData) {
        tooltip.classList.remove('visible');
        return;
      }

      var barTime = param.time;
      // barTime is already shifted to Thai timezone, use UTC methods to display correctly
      var dateObj = new Date(barTime * 1000);
      var dd = String(dateObj.getUTCDate()).padStart(2, '0');
      var mm = String(dateObj.getUTCMonth() + 1).padStart(2, '0');
      var yyyy = dateObj.getUTCFullYear() + 543; // Buddhist Era
      var hh = String(dateObj.getUTCHours()).padStart(2, '0');
      var mi = String(dateObj.getUTCMinutes()).padStart(2, '0');
      var ss = String(dateObj.getUTCSeconds()).padStart(2, '0');
      var dateStr = dd + '/' + mm + '/' + yyyy;
      var timeStr = hh + ':' + mi + ':' + ss;

      ttTime.textContent = dateStr + ' ' + timeStr;
      ttOpen.textContent = priceData.open.toFixed(2);
      ttHigh.textContent = priceData.high.toFixed(2);
      ttLow.textContent = priceData.low.toFixed(2);
      ttClose.textContent = priceData.close.toFixed(2);

      // Determine actual candle color from OHLC data
      var actualCandleColor = priceData.close >= priceData.open ? 'Green' : 'Red';

      // Check if there is a trade at this candle
      var tradesOnBar = tradeByTimeMap[barTime];
      var t = (tradesOnBar && tradesOnBar.length > 0) ? tradesOnBar[0] : null;

      // Fallback: check within +/- 60s
      if (!t && allTrades && allTrades.length > 0) {
        for (var ti = 0; ti < allTrades.length; ti++) {
          var tcEpoch = parseInt(allTrades[ti].timeCandle, 10) || parseInt(allTrades[ti].purchaseTime, 10);
          if (tcEpoch) {
            var trNorm = Math.floor(tcEpoch / 60) * 60 + TZ_OFFSET_SEC;
            if (Math.abs(trNorm - barTime) <= 60) {
              t = allTrades[ti];
              break;
            }
          }
        }
      }

      if (t) {
        var status = (t.WinStatus || '').toLowerCase();
        var isWin = status === 'win';
        var isLoss = status === 'loss';
        var statusClass = isWin ? 'badge-win' : (isLoss ? 'badge-loss' : 'badge-skip');
        var winStatusText = t.WinStatus || 'Trade';

        ttBadge.style.display = 'inline-block';
        ttBadge.className = statusClass;
        ttBadge.textContent = winStatusText;

        // 1. Time Display (Purchase Time from table, or timeCandleDisplay fallback)
        var timeDisplay = (t.purchaseTimeDisplay && t.purchaseTimeDisplay !== '-') ? t.purchaseTimeDisplay : (t.timeCandleDisplay || '-');

        // 2. Color (Badge matching table)
        var colorVal = (t.thisColor || actualCandleColor || '').toLowerCase();
        var colorBadgeClass = colorVal === 'green' ? 'badge-color-green' : (colorVal === 'red' ? 'badge-color-red' : 'badge-color-other');
        var colorText = t.thisColor || actualCandleColor || '-';
        var colorDisplay = '<span class="badge-color-dot ' + colorBadgeClass + '">● ' + colorText + '</span>';

        // 3. code_no
        var codeNoDisplay = (t.code_no !== null && t.code_no !== undefined && t.code_no !== '' && parseInt(t.code_no, 10) > 0) ? parseInt(t.code_no, 10) : '-';

        // 4. Strategy
        var strategyVal = t.codeStrategy || t.tradeStrategy || '-';

        // 5. Action
        var actionVal = t.thisAction || '-';
        var actionColor = actionVal.toLowerCase() === 'call' ? '#34d399' : (actionVal.toLowerCase() === 'put' ? '#f87171' : '#60a5fa');

        // 6. Win/Loss status badge
        var winLossDisplay = '<span class="' + statusClass + '">' + winStatusText + '</span>';

        // Extra info: lossCon, PnL
        var isWinTrade = (t.WinStatus || '').toLowerCase() === 'win';
        var isLossTrade = (t.WinStatus || '').toLowerCase() === 'loss';
        var lConNum = t.displayLossCon !== undefined ? t.displayLossCon : (!isNaN(parseInt(t.lossCon, 10)) ? (parseInt(t.lossCon, 10) + 1) : null);
        var lConStyle = (lConNum !== null && lConNum >= 5) ? 'style="color:#fde047; font-weight:700;"' : '';
        var wDisplay = isWinTrade ? (t.displayWinCon || (!isNaN(parseInt(t.winCon, 10)) && parseInt(t.winCon, 10) > 0 ? parseInt(t.winCon, 10) : 1)) : '-';
        var lDisplay = isLossTrade ? (lConNum !== null ? lConNum : 1) : '-';
        var pnl = parseFloat(t.ThisProfit) || 0;
        var pnlColor = pnl >= 0 ? '#34d399' : '#f87171';
        var pnlText = (pnl >= 0 ? '+$' : '-$') + Math.abs(pnl).toFixed(2);

        // Choppy Combined metrics for this candle
        var chopCombined = choppyCombinedMap[barTime];
        var chopBadgeHtml = '';
        var chopDetailHtml = '';

        if (chopCombined) {
          var isChop = chopCombined.isChoppy;
          var chopStatusText = isChop ? 'CHOPPY (Macro+Micro)' : (chopCombined.isChoppyMacro ? 'MACRO CHOPPY' : (chopCombined.isChoppyMicro ? 'MICRO CHOPPY' : 'TREND / CLEAR'));
          var chopStatusColor = isChop ? '#f87171' : ((chopCombined.isChoppyMacro || chopCombined.isChoppyMicro) ? '#fbbf24' : '#34d399');
          var chopBgColor = isChop ? 'rgba(239, 68, 68, 0.22)' : ((chopCombined.isChoppyMacro || chopCombined.isChoppyMicro) ? 'rgba(245, 158, 11, 0.20)' : 'rgba(16, 185, 129, 0.20)');
          var chopBorderColor = isChop ? 'rgba(239, 68, 68, 0.5)' : ((chopCombined.isChoppyMacro || chopCombined.isChoppyMicro) ? 'rgba(245, 158, 11, 0.5)' : 'rgba(16, 185, 129, 0.5)');

          chopBadgeHtml = '<span style="background:' + chopBgColor + '; color:' + chopStatusColor + '; border:1px solid ' + chopBorderColor + '; padding:1px 6px; border-radius:4px; font-weight:700; font-size:10px;">' + chopStatusText + '</span>';
          
          var chopValStr = chopCombined.chop !== null ? chopCombined.chop.toFixed(1) : '-';
          var erStr = chopCombined.efficiencyRatio !== undefined ? chopCombined.efficiencyRatio.toFixed(2) : '-';
          var swStr = chopCombined.switchCount !== undefined ? chopCombined.switchCount : '-';
          var brStr = chopCombined.bodyRatio !== undefined ? chopCombined.bodyRatio.toFixed(2) : '-';
          
          chopDetailHtml = 'CHOP:' + chopValStr + ' | ER:' + erStr + ' | Sw:' + swStr + ' | Body:' + brStr;
        }

        ttTradeInfo.style.display = 'block';
        ttTradeInfo.innerHTML = [
          '<div class="tt-trade-box">',
          '  <div style="font-size:11px; font-weight:700; color:#38bdf8; margin-bottom:6px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:4px;">',
          '    <span>Trade #' + (t.tradeNo || '-') + '.' + (t.subTradeNo || '-') + '</span>',
          '    <span class="badge-round">Rnd #' + (t.tradeRoundNo || 1) + '</span>',
          '  </div>',
          '  <div class="tt-grid-fields">',
          '    <div class="tt-field-row">',
          '      <span class="tt-field-label">Time Display:</span>',
          '      <span class="tt-field-val" style="font-family:monospace; font-size:11px; color:#e2e8f0;">' + timeDisplay + '</span>',
          '    </div>',
          '    <div class="tt-field-row">',
          '      <span class="tt-field-label">Color:</span>',
          '      <span class="tt-field-val">' + colorDisplay + '</span>',
          '    </div>',
          '    <div class="tt-field-row">',
          '      <span class="tt-field-label">code_no:</span>',
          '      <span class="tt-field-val"><b style="color:#38bdf8; font-family:monospace; font-size:12px;">' + codeNoDisplay + '</b></span>',
          '    </div>',
          '    <div class="tt-field-row">',
          '      <span class="tt-field-label">Strategy:</span>',
          '      <span class="tt-field-val"><span style="font-family:monospace; font-size:11px; color:#fde047; font-weight:600;" title="' + strategyVal + '">' + strategyVal + '</span></span>',
          '    </div>',
          '    <div class="tt-field-row">',
          '      <span class="tt-field-label">Action:</span>',
          '      <span class="tt-field-val"><b style="color:' + actionColor + ';">' + actionVal + '</b></span>',
          '    </div>',
          '    <div class="tt-field-row">',
          '      <span class="tt-field-label">ผลลัพธ์:</span>',
          '      <span class="tt-field-val">' + winLossDisplay + '</span>',
          '    </div>',
          '    <div class="tt-field-row" style="margin-top:4px; padding-top:4px; border-top:1px dashed rgba(255,255,255,0.1);">',
          '      <span class="tt-field-label">winCon / lossCon:</span>',
          '      <span class="tt-field-val">W:' + wDisplay + ' | <span ' + lConStyle + '>L:' + lDisplay + '</span></span>',
          '    </div>',
          '    <div class="tt-field-row">',
          '      <span class="tt-field-label">PnL:</span>',
          '      <span class="tt-field-val" style="color:' + pnlColor + '; font-weight:700;">' + pnlText + '</span>',
          '    </div>',
          chopBadgeHtml ? [
            '    <div class="tt-field-row" style="margin-top:4px; padding-top:4px; border-top:1px dashed rgba(255,255,255,0.1);">',
            '      <span class="tt-field-label">🌊 Choppy Combined:</span>',
            '      <span class="tt-field-val">' + chopBadgeHtml + '</span>',
            '    </div>',
            '    <div class="tt-field-row">',
            '      <span class="tt-field-label">CHOP/ER/Sw:</span>',
            '      <span class="tt-field-val" style="font-family:monospace; font-size:10px; color:#c084fc;">' + chopDetailHtml + '</span>',
            '    </div>'
          ].join('') : '',
          '  </div>',
          '</div>'
        ].join('');
      } else {
        ttBadge.style.display = 'none';
        ttTradeInfo.style.display = 'none';
      }

      // Standalone Choppy Combined box for candle (when hovering anywhere)
      var candleChop = choppyCombinedMap[barTime];
      if (candleChop && ttChopBox) {
        var isChopCandle = candleChop.isChoppy;
        var chopStatusText = isChopCandle ? 'CHOPPY (Macro+Micro)' : (candleChop.isChoppyMacro ? 'MACRO CHOPPY' : (candleChop.isChoppyMicro ? 'MICRO CHOPPY' : 'TREND / CLEAR'));
        var chopStatusColor = isChopCandle ? '#f87171' : ((candleChop.isChoppyMacro || candleChop.isChoppyMicro) ? '#fbbf24' : '#34d399');
        var chopBgColor = isChopCandle ? 'rgba(239, 68, 68, 0.22)' : ((candleChop.isChoppyMacro || candleChop.isChoppyMicro) ? 'rgba(245, 158, 11, 0.20)' : 'rgba(16, 185, 129, 0.20)');
        var chopBorderColor = isChopCandle ? 'rgba(239, 68, 68, 0.5)' : ((candleChop.isChoppyMacro || candleChop.isChoppyMicro) ? 'rgba(245, 158, 11, 0.5)' : 'rgba(16, 185, 129, 0.5)');

        if (ttChopBadge) {
          ttChopBadge.innerHTML = '<span style="background:' + chopBgColor + '; color:' + chopStatusColor + '; border:1px solid ' + chopBorderColor + '; padding:1px 6px; border-radius:4px; font-weight:700; font-size:10px;">' + chopStatusText + '</span>';
        }
        if (ttChopMetrics) {
          var chopValStr = candleChop.chop !== null ? candleChop.chop.toFixed(1) : '-';
          var erStr = candleChop.efficiencyRatio !== undefined ? candleChop.efficiencyRatio.toFixed(2) : '-';
          var swStr = candleChop.switchCount !== undefined ? candleChop.switchCount : '-';
          ttChopMetrics.textContent = 'CHOP:' + chopValStr + ' | ER:' + erStr + ' | Sw:' + swStr;
        }
        ttChopBox.style.display = 'block';
      } else if (ttChopBox) {
        ttChopBox.style.display = 'none';
      }

      tooltip.classList.add('visible');
    }

    /**
     * 7. Render Metadata & KPI Cards
     */
    function renderMetadata(data) {
      var r = data.round || {};
      var s = data.server || {};
      var sum = data.summary || {};

      if (r && r.startTimeTrade) {
        roundTrueDate = r.startTimeTrade.split(' ')[0] || '';
      }

      var isAllRounds = (tradeRoundNo <= 0 || (r && r.tradeRoundNo === 0));
      if (!isAllRounds && r && r.tradeRoundNo) {
        tradeRoundNo = r.tradeRoundNo;
        if (filterRound) filterRound.value = String(tradeRoundNo);
      } else if (isAllRounds) {
        tradeRoundNo = 0;
        if (filterRound) filterRound.value = '0';
      }

      var symbolPill = document.getElementById('title-symbol');
      if (symbolPill) symbolPill.textContent = assetCode || 'N/A';
      var metaDate = document.getElementById('meta-date');
      if (metaDate) {
        var dateMismatchTag = (roundTrueDate && tradeDate && roundTrueDate !== tradeDate)
          ? ' <span style="color:#f59e0b; font-size:11px; font-weight:600;" title="วันที่เริ่มรอบจริง">(รอบจริง: ' + roundTrueDate + ')</span>'
          : '';
        metaDate.innerHTML = 'Date: <b>' + (tradeDate || '-') + '</b>' + dateMismatchTag;
      }

      document.getElementById('val-strategy').textContent = r.usestrategyCode || '-';
      document.getElementById('meta-server').innerHTML = 'Server [<b>' + (s.serverCode || serverCode) + '</b>] ' + (s.serverName || '');
      var roundDisplay = isAllRounds ? (r.tradeRoundDisplay || 'All Rounds (ทุกรอบ)') : ('Round #' + (r.tradeRoundNo || tradeRoundNo || '-'));
      var metaRoundEl = document.getElementById('meta-round');
      if (metaRoundEl) {
        metaRoundEl.innerHTML = 'Round: <b>' + roundDisplay + '</b>';
      }

      document.getElementById('kpi-total-trades').textContent = sum.totalTrades || 0;
      document.getElementById('kpi-duration').textContent = 'Duration: ' + (r.durationTrade || '-');

      var totalWins = sum.winCount || 0;
      var totalLosses = sum.lossCount || 0;
      var activeTrades = totalWins + totalLosses;
      var winRate = activeTrades > 0 ? ((totalWins / activeTrades) * 100).toFixed(1) : 0;

      document.getElementById('kpi-win-loss').textContent = totalWins + 'W / ' + totalLosses + 'L';
      document.getElementById('kpi-win-rate').textContent = 'Win Rate: ' + winRate + '%';

      var pnl = parseFloat(sum.totalProfit) || 0;
      var pnlEl = document.getElementById('kpi-total-profit');
      pnlEl.textContent = (pnl >= 0 ? '+$' : '-$') + Math.abs(pnl).toFixed(2);
      pnlEl.style.color = pnl >= 0 ? '#34d399' : '#f87171';

      document.getElementById('kpi-skipped').textContent = 'Skipped: ' + (sum.skippedCount || 0);
      document.getElementById('kpi-max-con').textContent = 'W:' + (r.MaxWinCon || 0) + ' | L:' + (r.MaxLossCon || 0);
    }

    /**
     * 8. Render Trades Table
     */
    function renderTradesTable(trades) {
      tradesCountTag.textContent = trades.length + ' Trades';

      if (trades.length === 0) {
        tradesTbody.innerHTML = '<tr><td colspan="16" style="text-align:center; padding:30px; color:var(--text-tertiary);">ไม่มีรายการเทรดในรอบนี้</td></tr>';
        return;
      }

      var html = '';
      trades.forEach(function (t) {
        var isWin = (t.WinStatus || '').toLowerCase() === 'win';
        var isLoss = (t.WinStatus || '').toLowerCase() === 'loss';
        var badgeClass = isWin ? 'badge-win' : (isLoss ? 'badge-loss' : 'badge-skip');
        var pnl = parseFloat(t.ThisProfit) || 0;
        var pnlColor = pnl >= 0 ? '#34d399' : '#f87171';

        var colorVal = (t.thisColor || '').toLowerCase();
        var colorBadgeClass = colorVal === 'green' ? 'badge-color-green' : (colorVal === 'red' ? 'badge-color-red' : 'badge-color-other');
        var colorBadge = t.thisColor ? '<span class="badge-color-dot ' + colorBadgeClass + '">● ' + t.thisColor + '</span>' : '-';
        var strat = t.codeStrategy || t.tradeStrategy || '-';
        var codeNoDisplay = (t.code_no !== null && t.code_no !== undefined && t.code_no !== '' && parseInt(t.code_no, 10) > 0) ? parseInt(t.code_no, 10) : '-';

        html += '<tr data-time="' + (t.timeCandle || t.purchaseTime) + '" data-id="' + t.id + '">';
        html += '  <td><b>#' + (t.tradeNo || '-') + '.' + (t.subTradeNo || '-') + '</b></td>';
        html += '  <td><span class="badge-round">Rnd #' + (t.tradeRoundNo || 1) + '</span></td>';
        html += '  <td>' + (t.purchaseTimeDisplay || '-') + '</td>';
        html += '  <td>' + colorBadge + '</td>';
        html += '  <td><b>' + (t.thisAction || '-') + '</b></td>';
        html += '  <td><b style="color:#38bdf8; font-family:monospace; font-size:12px;">' + codeNoDisplay + '</b></td>';
        html += '  <td><span style="font-family:monospace; font-size:11px; color:#fde047;">' + strat + '</span></td>';
        html += '  <td><span class="' + badgeClass + '">' + (t.WinStatus || '-') + '</span></td>';
        var isWinTrade = (t.WinStatus || '').toLowerCase() === 'win';
        var isLossTrade = (t.WinStatus || '').toLowerCase() === 'loss';
        var lConNum = t.displayLossCon !== undefined ? t.displayLossCon : (!isNaN(parseInt(t.lossCon, 10)) ? (parseInt(t.lossCon, 10) + 1) : null);
        var lConStyle = (lConNum !== null && lConNum >= 5) ? ' style="color:#fde047; font-weight:700;"' : '';
        var winConCell = isWinTrade ? (t.displayWinCon || (!isNaN(parseInt(t.winCon, 10)) && parseInt(t.winCon, 10) > 0 ? parseInt(t.winCon, 10) : 1)) : '-';
        var lossConCell = isLossTrade ? (lConNum !== null ? lConNum : 1) : '-';
        html += '  <td>' + winConCell + '</td>';
        html += '  <td' + lConStyle + '>' + lossConCell + '</td>';
        html += '  <td>$' + (parseFloat(t.MoneyTrade) || 0).toFixed(2) + '</td>';
        html += '  <td style="color:' + pnlColor + '; font-weight:700;">' + (pnl >= 0 ? '+$' : '-$') + Math.abs(pnl).toFixed(2) + '</td>';
        html += '  <td>$' + (parseFloat(t.GrandBalance) || 0).toFixed(2) + '</td>';
        html += '  <td>' + (t.entrySpot || '-') + '</td>';
        html += '  <td>' + (t.exitSpot || '-') + '</td>';
        html += '  <td>' + (t.diffSpot !== null && t.diffSpot !== undefined ? parseFloat(t.diffSpot).toFixed(4) : '-') + '</td>';
        html += '</tr>';
      });

      tradesTbody.innerHTML = html;

      // Bind row click to jump to candle & set selected marker in yellow
      tradesTbody.querySelectorAll('tr').forEach(function (row) {
        row.addEventListener('click', function () {
          tradesTbody.querySelectorAll('tr').forEach(function (r) { r.classList.remove('active-row'); });
          this.classList.add('active-row');

          var tId = parseInt(this.dataset.id, 10);
          selectedTradeId = tId;

          var tEpoch = parseInt(this.dataset.time, 10);
          if (tEpoch) {
            selectedCandleTime = Math.floor(tEpoch / 60) * 60 + TZ_OFFSET_SEC;
          }

          // Re-render markers with selected trade highlighted in yellow
          updateMarkers();

          if (tEpoch && chart) {
            var norm = Math.floor(tEpoch / 60) * 60 + TZ_OFFSET_SEC;
            chart.timeScale().setVisibleRange({
              from: norm - 900,
              to: norm + 900
            });
          }
        });
      });
    }

    /**
     * Helper to set WebSocket status badge
     */
    function setWsStatus(connected, text) {
      if (connected) {
        wsStatusDot.className = 'status-dot connected';
      } else {
        wsStatusDot.className = 'status-dot';
      }
      wsStatusText.textContent = text;
    }

    function hideLoader() {
      if (chartLoader) chartLoader.style.display = 'none';
    }

    // ==========================================
    //  Event Listeners for Filter Controls
    // ==========================================
    if (filterVps) {
      filterVps.addEventListener('change', function () {
        serverCode = parseInt(this.value, 10) || 0;
        refreshContextualFilters();
      });
    }

    function checkDateAndPromptIfEmpty(sCode, dStr) {
      if (!sCode || !dStr) return;
      fetch('../php/api_analysis_trade_hist.php?action=check_date_data&serverCode=' + encodeURIComponent(sCode) + '&date=' + encodeURIComponent(dStr))
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success && res.hasData === false) {
            window.openAutoLoadConfirmModal(sCode, dStr, res.vps, res.timeGapDescription);
          }
        })
        .catch(function (e) {
          console.warn('Check date error:', e);
        });
    }

    if (filterDate) {
      filterDate.addEventListener('change', function () {
        tradeDate = this.value;
        if (filterDatePicker) filterDatePicker.value = tradeDate;
        refreshContextualFilters();
        var sc = parseInt(filterVps ? filterVps.value : serverCode, 10) || 0;
        if (sc > 0 && tradeDate) {
          checkDateAndPromptIfEmpty(sc, tradeDate);
        }
      });
    }

    if (filterDatePicker) {
      filterDatePicker.addEventListener('change', function () {
        tradeDate = this.value;
        if (filterDate) filterDate.value = tradeDate;
        refreshContextualFilters();
        var sc = parseInt(filterVps ? filterVps.value : serverCode, 10) || 0;
        if (sc > 0 && tradeDate) {
          checkDateAndPromptIfEmpty(sc, tradeDate);
        }
      });
    }

    if (filterAsset) {
      filterAsset.addEventListener('change', function () {
        assetCode = this.value;
        refreshContextualFilters();
      });
    }

    if (filterRound) {
      filterRound.addEventListener('change', function () {
        tradeRoundNo = parseInt(this.value, 10) || 0;
      });
    }

    if (btnLoadFilter) {
      btnLoadFilter.addEventListener('click', function () {
        serverCode = parseInt(filterVps.value, 10) || 0;
        tradeDate = filterDate.value || (filterDatePicker ? filterDatePicker.value : '') || '';
        assetCode = filterAsset.value || '1HZ75V';
        tradeRoundNo = parseInt(filterRound.value, 10) || 0;

        if (serverCode > 0 && tradeDate) {
          fetch('../php/api_analysis_trade_hist.php?action=check_date_data&serverCode=' + encodeURIComponent(serverCode) + '&date=' + encodeURIComponent(tradeDate))
            .then(function(r) { return r.json(); })
            .then(function(chk) {
              if (chk.success && chk.hasData === false) {
                window.openAutoLoadConfirmModal(serverCode, tradeDate, chk.vps, chk.timeGapDescription);
                return;
              }
              if (chk.success && chk.isToday && chk.needsUpdate) {
                var confirmMsg = 'ตรวจพบว่าข้อมูลในระบบมีถึง ' + (chk.maxRecordedDisplay || '-') + ' แต่ขณะนี้เป็นเวลา ' + (chk.currentTime || '-') + '\n\n' +
                                 'ต้องการดึงข้อมูลล่าสุด (00:05 - now) จาก Deriv & VPS เลยหรือไม่?\n\n' +
                                 '• ตกลง (OK): ทำการซิงค์ข้อมูลใหม่ทันที\n' +
                                 '• ยกเลิก (Cancel): โหลดเฉพาะข้อมูลที่มีอยู่ในฐานข้อมูลตอนนี้';
                if (confirm(confirmMsg)) {
                  window.openAutoLoadConfirmModal(serverCode, tradeDate, chk.vps, chk.timeGapDescription);
                  return;
                }
              }
              proceedLoadChart();
            })
            .catch(function() {
              proceedLoadChart();
            });
        } else {
          proceedLoadChart();
        }
      });
    }

    if (btnSyncToday) {
      btnSyncToday.addEventListener('click', function () {
        serverCode = parseInt(filterVps.value, 10) || 0;
        tradeDate = filterDate.value || (filterDatePicker ? filterDatePicker.value : '') || '';
        assetCode = filterAsset.value || '1HZ75V';
        tradeRoundNo = parseInt(filterRound.value, 10) || 0;

        if (serverCode > 0 && tradeDate) {
          window.openAutoLoadConfirmModal(serverCode, tradeDate, serverObjectsMap[serverCode], 'ดึงข้อมูลล่าสุด (00:05 - now) จาก Deriv & VPS เข้าสู่ระบบ');
        } else {
          showToast('⚠️ กรุณาเลือก VPS Server และวันที่ก่อนดำเนินการ', 'warn', 3500);
        }
      });
    }

    function proceedLoadChart() {
      var newUrl = window.location.pathname + '?serverCode=' + encodeURIComponent(serverCode) +
                   '&tradeRoundNo=' + encodeURIComponent(tradeRoundNo) +
                   '&assetCode=' + encodeURIComponent(assetCode) +
                   '&date=' + encodeURIComponent(tradeDate);
      window.history.pushState(null, '', newUrl);

      updateHeaderBadges();
      clearChartState();
      loadData();
    }

    // ==========================================
    //  Event Listeners for Toolbar Toggles
    // ==========================================

    btnToggleWinCon.addEventListener('click', function () {
      showWinCon = !showWinCon;
      this.classList.toggle('active', showWinCon);
      updateMarkers();
    });

    btnToggleLossCon.addEventListener('click', function () {
      showLossCon = !showLossCon;
      this.classList.toggle('active', showLossCon);
      updateMarkers();
    });

    btnToggleAllTrades.addEventListener('click', function () {
      showAllTrades = !showAllTrades;
      this.classList.toggle('active', showAllTrades);
      updateMarkers();
    });

    btnToggleLabelMode.addEventListener('click', function () {
      if (labelMode === 'con') {
        labelMode = 'pnl';
        this.textContent = '🏷️ Label: Trade # & PnL';
        this.classList.add('active');
      } else if (labelMode === 'pnl') {
        labelMode = 'codeno';
        this.textContent = '🏷️ Label: Code No';
        this.classList.add('active');
      } else {
        labelMode = 'con';
        this.textContent = '🏷️ Label: Con Count';
        this.classList.remove('active');
      }
      updateMarkers();
    });

    if (switchCodeNoMarker) {
      switchCodeNoMarker.addEventListener('change', function () {
        showCodeNoMarker = this.checked;
        if (switchCodeNoMarkerText) {
          switchCodeNoMarkerText.textContent = showCodeNoMarker ? 'ON' : 'OFF';
          switchCodeNoMarkerText.classList.toggle('off', !showCodeNoMarker);
        }
        updateMarkers();
      });
    }

    function setTooltipState(enabled) {
      showTooltip = !!enabled;
      if (btnToggleTooltip) {
        btnToggleTooltip.classList.toggle('active', showTooltip);
      }
      if (switchTooltip) {
        switchTooltip.checked = showTooltip;
      }
      if (switchTooltipText) {
        switchTooltipText.textContent = showTooltip ? 'ON' : 'OFF';
        switchTooltipText.classList.toggle('off', !showTooltip);
      }
      if (!showTooltip && tooltip) {
        tooltip.classList.remove('visible');
      }
    }

    if (btnToggleTooltip) {
      btnToggleTooltip.addEventListener('click', function () {
        setTooltipState(!showTooltip);
      });
    }

    function setChoppyCombinedState(enabled) {
      showChoppyCombined = !!enabled;
      if (btnToggleChoppyCombined) {
        btnToggleChoppyCombined.classList.toggle('active', showChoppyCombined);
      }
      if (showChoppyCombined) {
        updateChoppyCombinedZones();
      } else {
        removeChoppyCombinedZones();
      }
    }

    if (btnToggleChoppyCombined) {
      btnToggleChoppyCombined.addEventListener('click', function () {
        setChoppyCombinedState(!showChoppyCombined);
      });
    }

    var btnChoppyCombinedSettings = document.getElementById('btn-choppy-combined-settings');
    var choppyCombinedPopover = document.getElementById('choppy-combined-popover');
    var choppyInputMode = document.getElementById('choppy-input-mode');
    var choppyInputGroupSize = document.getElementById('choppy-input-groupsize');
    var choppyInputChopThreshold = document.getElementById('choppy-input-chopthreshold');
    var choppyInputErThreshold = document.getElementById('choppy-input-erthreshold');
    var choppyInputBodyRatio = document.getElementById('choppy-input-bodyratio');
    var choppyInputSwitches = document.getElementById('choppy-input-switches');
    var btnSaveChoppySettings = document.getElementById('btn-save-choppy-settings');

    if (btnChoppyCombinedSettings && choppyCombinedPopover) {
      btnChoppyCombinedSettings.addEventListener('click', function (e) {
        e.stopPropagation();
        choppyCombinedPopover.classList.toggle('open');
      });
    }

    document.addEventListener('click', function (e) {
      if (choppyCombinedPopover && choppyCombinedPopover.classList.contains('open')) {
        if (!choppyCombinedPopover.contains(e.target) && e.target !== btnChoppyCombinedSettings) {
          choppyCombinedPopover.classList.remove('open');
        }
      }
    });

    function applyChoppySettingsLive() {
      if (choppyInputMode) choppyCombineMode = choppyInputMode.value || 'or';
      if (choppyInputGroupSize) choppyGroupSize = Math.max(2, Math.min(60, parseInt(choppyInputGroupSize.value, 10) || 15));
      if (choppyInputChopThreshold) choppyChopThreshold = parseFloat(choppyInputChopThreshold.value) || 61.8;
      if (choppyInputErThreshold) choppyErThreshold = parseFloat(choppyInputErThreshold.value) || 0.35;
      if (choppyInputBodyRatio) choppyBodyRatioThreshold = parseFloat(choppyInputBodyRatio.value) || 0.3;
      if (choppyInputSwitches) choppyMinSwitches = parseInt(choppyInputSwitches.value, 10) || 3;

      calculateChoppinessCombined();
      if (showChoppyCombined) {
        updateChoppyCombinedZones();
      }
    }

    if (btnSaveChoppySettings) {
      btnSaveChoppySettings.addEventListener('click', function () {
        applyChoppySettingsLive();
        if (choppyCombinedPopover) choppyCombinedPopover.classList.remove('open');
        savePageConfig();
      });
    }

    [choppyInputMode, choppyInputGroupSize, choppyInputChopThreshold, choppyInputErThreshold, choppyInputBodyRatio, choppyInputSwitches].forEach(function (el) {
      if (el) {
        el.addEventListener('change', applyChoppySettingsLive);
      }
    });

    if (switchTooltip) {
      switchTooltip.addEventListener('change', function () {
        setTooltipState(this.checked);
      });
    }

    if (switchSpotLines) {
      switchSpotLines.addEventListener('change', function () {
        showSpotLines = this.checked;
        if (switchSpotLinesText) {
          switchSpotLinesText.textContent = showSpotLines ? 'ON' : 'OFF';
          switchSpotLinesText.classList.toggle('off', !showSpotLines);
        }
        if (!showSpotLines) {
          clearSpotLines();
        }
      });
    }

    btnFitChart.addEventListener('click', function () {
      if (chart) chart.timeScale().fitContent();
    });

    btnFirstTrade.addEventListener('click', function () {
      if (allTrades.length > 0 && chart) {
        var first = allTrades[0];
        var epoch = parseInt(first.timeCandle || first.purchaseTime, 10);
        if (epoch) {
          var norm = Math.floor(epoch / 60) * 60 + TZ_OFFSET_SEC;
          chart.timeScale().setVisibleRange({ from: norm - 600, to: norm + 1800 });
        }
      }
    });

    btnLastTrade.addEventListener('click', function () {
      if (allTrades.length > 0 && chart) {
        var last = allTrades[allTrades.length - 1];
        var epoch = parseInt(last.timeCandle || last.purchaseTime, 10);
        if (epoch) {
          var norm = Math.floor(epoch / 60) * 60 + TZ_OFFSET_SEC;
          chart.timeScale().setVisibleRange({ from: norm - 1800, to: norm + 600 });
        }
      }
    });

    // Mark Zone toggle
    if (btnToggleMarkZone) {
      btnToggleMarkZone.addEventListener('click', function () {
        showMarkZone = !showMarkZone;
        this.classList.toggle('active', showMarkZone);
      });
    }

    if (btnClearZones) {
      btnClearZones.addEventListener('click', function () {
        clearAllMarkZones();
      });
    }

    // Analyze Zones toggle & drag setup
    function setAnalyzeZonesVisible(show) {
      showAnalyzeZones = (typeof show === 'boolean') ? show : !showAnalyzeZones;
      if (panelAnalyzeZones) {
        panelAnalyzeZones.style.display = showAnalyzeZones ? 'block' : 'none';
      }
      if (btnToggleAnalyzeZones) {
        btnToggleAnalyzeZones.classList.toggle('active', showAnalyzeZones);
      }
    }

    if (btnToggleAnalyzeZones) {
      btnToggleAnalyzeZones.addEventListener('click', function () {
        setAnalyzeZonesVisible(!showAnalyzeZones);
      });
    }

    if (btnCloseAnalyzeZones) {
      btnCloseAnalyzeZones.addEventListener('click', function () {
        setAnalyzeZonesVisible(false);
      });
    }

    // Draggable setup for analyzeZones
    (function setupAnalyzeZonesDraggable() {
      var panel = document.getElementById('analyzeZones');
      var header = document.getElementById('analyze-zones-header');
      if (!panel || !header) return;

      var isDragging = false;
      var startX = 0, startY = 0;
      var origLeft = 0, origTop = 0;

      function onMouseDown(e) {
        if (e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) return;
        isDragging = true;
        panel.classList.add('dragging');

        var rect = panel.getBoundingClientRect();
        origLeft = rect.left;
        origTop = rect.top;
        startX = e.clientX;
        startY = e.clientY;

        panel.style.left = origLeft + 'px';
        panel.style.top = origTop + 'px';
        panel.style.right = 'auto';
        panel.style.bottom = 'auto';

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
        e.preventDefault();
      }

      function onMouseMove(e) {
        if (!isDragging) return;
        var dx = e.clientX - startX;
        var dy = e.clientY - startY;

        var newLeft = origLeft + dx;
        var newTop = origTop + dy;

        var pad = 6;
        var maxLeft = window.innerWidth - panel.offsetWidth - pad;
        var maxTop = window.innerHeight - panel.offsetHeight - pad;

        newLeft = Math.max(pad, Math.min(newLeft, maxLeft));
        newTop = Math.max(pad, Math.min(newTop, maxTop));

        panel.style.left = newLeft + 'px';
        panel.style.top = newTop + 'px';
      }

      function onMouseUp() {
        if (!isDragging) return;
        isDragging = false;
        panel.classList.remove('dragging');
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
      }

      function onTouchStart(e) {
        if (e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) return;
        if (e.touches.length !== 1) return;
        var touch = e.touches[0];
        isDragging = true;
        panel.classList.add('dragging');

        var rect = panel.getBoundingClientRect();
        origLeft = rect.left;
        origTop = rect.top;
        startX = touch.clientX;
        startY = touch.clientY;

        panel.style.left = origLeft + 'px';
        panel.style.top = origTop + 'px';
        panel.style.right = 'auto';
        panel.style.bottom = 'auto';

        document.addEventListener('touchmove', onTouchMove, { passive: false });
        document.addEventListener('touchend', onTouchEnd);
      }

      function onTouchMove(e) {
        if (!isDragging || e.touches.length !== 1) return;
        var touch = e.touches[0];
        var dx = touch.clientX - startX;
        var dy = touch.clientY - startY;

        var newLeft = origLeft + dx;
        var newTop = origTop + dy;

        var pad = 6;
        var maxLeft = window.innerWidth - panel.offsetWidth - pad;
        var maxTop = window.innerHeight - panel.offsetHeight - pad;

        newLeft = Math.max(pad, Math.min(newLeft, maxLeft));
        newTop = Math.max(pad, Math.min(newTop, maxTop));

        panel.style.left = newLeft + 'px';
        panel.style.top = newTop + 'px';
        e.preventDefault();
      }

      function onTouchEnd() {
        if (!isDragging) return;
        isDragging = false;
        panel.classList.remove('dragging');
        document.removeEventListener('touchmove', onTouchMove);
        document.removeEventListener('touchend', onTouchEnd);
      }

      header.addEventListener('mousedown', onMouseDown);
      header.addEventListener('touchstart', onTouchStart, { passive: true });
    })();

    // ==========================================
    //  Main Tabs & zoneAnalysis.js Editor
    // ==========================================
    var tabBtnChart = document.getElementById('tab-btn-chart');
    var tabBtnEditor = document.getElementById('tab-btn-editor');
    var tabPaneChart = document.getElementById('tab-pane-chart');
    var tabPaneEditor = document.getElementById('tab-pane-editor');
    var zoneAnalysisEditor = document.getElementById('zoneAnalysisEditor');
    var btnSaveZoneAnalysis = document.getElementById('btn-save-zone-analysis');
    var btnReloadZoneAnalysis = document.getElementById('btn-reload-zone-analysis');
    var editorStatus = document.getElementById('editor-status');
    var editorMeta = document.getElementById('editor-meta');
    var editorDirtyBadge = document.getElementById('editor-dirty-badge');
    var editorLastSaved = document.getElementById('editor-last-saved');

    var originalZoneAnalysisContent = '';
    var isZoneAnalysisLoaded = false;

    function switchMainTab(targetTab) {
      if (targetTab === 'chart') {
        if (tabBtnChart) tabBtnChart.classList.add('active');
        if (tabBtnEditor) tabBtnEditor.classList.remove('active');
        if (tabPaneChart) tabPaneChart.classList.add('active');
        if (tabPaneEditor) tabPaneEditor.classList.remove('active');

        // Resize Lightweight Chart upon returning to chart tab
        if (chart && chartContainer) {
          setTimeout(function () {
            chart.resize(chartContainer.clientWidth, 560);
          }, 50);
        }
      } else if (targetTab === 'editor') {
        if (tabBtnEditor) tabBtnEditor.classList.add('active');
        if (tabBtnChart) tabBtnChart.classList.remove('active');
        if (tabPaneEditor) tabPaneEditor.classList.add('active');
        if (tabPaneChart) tabPaneChart.classList.remove('active');

        if (!isZoneAnalysisLoaded) {
          loadZoneAnalysisFile();
        }
      }
    }

    if (tabBtnChart) {
      tabBtnChart.addEventListener('click', function () {
        switchMainTab('chart');
      });
    }

    if (tabBtnEditor) {
      tabBtnEditor.addEventListener('click', function () {
        switchMainTab('editor');
      });
    }

    function updateEditorMeta(text) {
      if (!editorMeta) return;
      var lines = text.split('\n').length;
      var bytes = new Blob([text]).size;
      var sizeStr = (bytes > 1024) ? (bytes / 1024).toFixed(1) + ' KB' : bytes + ' B';
      editorMeta.textContent = 'Lines: ' + lines + ' | Size: ' + sizeStr;
    }

    function loadZoneAnalysisFile() {
      if (editorStatus) {
        editorStatus.textContent = '⏳ กำลังโหลด...';
        editorStatus.className = 'code-editor-status saving';
      }
      fetch('analysis_trade_chart.php?action=load_zone_analysis&t=' + Date.now())
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success && zoneAnalysisEditor) {
            zoneAnalysisEditor.value = data.content;
            originalZoneAnalysisContent = data.content;
            isZoneAnalysisLoaded = true;
            updateEditorMeta(data.content);
            if (editorStatus) {
              editorStatus.textContent = '● พร้อมใช้งาน';
              editorStatus.className = 'code-editor-status saved';
            }
            if (editorDirtyBadge) editorDirtyBadge.style.display = 'none';
          } else {
            if (editorStatus) {
              editorStatus.textContent = '✕ เกิดข้อผิดพลาด: ' + (data.error || 'โหลดไม่สำเร็จ');
              editorStatus.className = 'code-editor-status error';
            }
          }
        })
        .catch(function (err) {
          if (editorStatus) {
            editorStatus.textContent = '✕ ไม่สามารถเชื่อมต่อเพื่อโหลดไฟล์ได้';
            editorStatus.className = 'code-editor-status error';
          }
        });
    }

    function saveZoneAnalysisFile() {
      if (!zoneAnalysisEditor) return;
      var content = zoneAnalysisEditor.value;

      if (editorStatus) {
        editorStatus.textContent = '💾 กำลังบันทึก...';
        editorStatus.className = 'code-editor-status saving';
      }
      if (btnSaveZoneAnalysis) btnSaveZoneAnalysis.disabled = true;

      fetch('analysis_trade_chart.php?action=save_zone_analysis', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: content })
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (btnSaveZoneAnalysis) btnSaveZoneAnalysis.disabled = false;
          if (data.success) {
            originalZoneAnalysisContent = content;
            updateEditorMeta(content);
            if (editorStatus) {
              editorStatus.textContent = '✓ บันทึกสำเร็จ (' + (data.time || '') + ')';
              editorStatus.className = 'code-editor-status saved';
            }
            if (editorDirtyBadge) editorDirtyBadge.style.display = 'none';
            if (editorLastSaved) editorLastSaved.textContent = 'บันทึกล่าสุด: ' + (data.time || '');

            // Dynamically evaluate updated script so changes are immediate in runtime
            try {
              eval(content);
              console.log('[zoneAnalysis.js] Runtime state updated successfully');
            } catch (evalErr) {
              console.warn('[zoneAnalysis.js] Dynamic reload warning:', evalErr);
            }
          } else {
            if (editorStatus) {
              editorStatus.textContent = '✕ ' + (data.error || 'บันทึกไม่สำเร็จ');
              editorStatus.className = 'code-editor-status error';
            }
          }
        })
        .catch(function (err) {
          if (btnSaveZoneAnalysis) btnSaveZoneAnalysis.disabled = false;
          if (editorStatus) {
            editorStatus.textContent = '✕ ข้อผิดพลาดในการส่งข้อมูล';
            editorStatus.className = 'code-editor-status error';
          }
        });
    }

    if (btnReloadZoneAnalysis) {
      btnReloadZoneAnalysis.addEventListener('click', function () {
        if (zoneAnalysisEditor && zoneAnalysisEditor.value !== originalZoneAnalysisContent) {
          if (!confirm('ไฟล์มีการแก้ไขที่ยังไม่ได้บันทึก ต้องการโหลดใหม่ทับข้อมูลปัจจุบันหรือไม่?')) {
            return;
          }
        }
        loadZoneAnalysisFile();
      });
    }

    if (btnSaveZoneAnalysis) {
      btnSaveZoneAnalysis.addEventListener('click', function () {
        saveZoneAnalysisFile();
      });
    }

    if (zoneAnalysisEditor) {
      zoneAnalysisEditor.addEventListener('input', function () {
        updateEditorMeta(this.value);
        var isDirty = (this.value !== originalZoneAnalysisContent);
        if (editorDirtyBadge) {
          editorDirtyBadge.style.display = isDirty ? 'inline-block' : 'none';
        }
        if (editorStatus) {
          if (isDirty) {
            editorStatus.textContent = '⚠️ มีการแก้ไขที่ยังไม่ได้บันทึก';
            editorStatus.className = 'code-editor-status modified';
          } else {
            editorStatus.textContent = '● บันทึกแล้ว';
            editorStatus.className = 'code-editor-status saved';
          }
        }
      });

      // Shortcut: Ctrl+S to save, Tab key for 2 spaces indent
      zoneAnalysisEditor.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
          e.preventDefault();
          saveZoneAnalysisFile();
          return;
        }

        if (e.key === 'Tab') {
          e.preventDefault();
          var start = this.selectionStart;
          var end = this.selectionEnd;
          this.value = this.value.substring(0, start) + '  ' + this.value.substring(end);
          this.selectionStart = this.selectionEnd = start + 2;
          this.dispatchEvent(new Event('input'));
        }
      });
    }

    // Bollinger Bands toggle
    var btnToggleBollinger = document.getElementById('btn-toggle-bollinger');
    var btnBbSettings = document.getElementById('btn-bb-settings');
    var bbPopover = document.getElementById('bb-popover');
    var bbInputPeriod = document.getElementById('bb-input-period');
    var bbInputStdDev = document.getElementById('bb-input-stddev');
    var bbInputMaType = document.getElementById('bb-input-matype');
    var bbSwitchSqueeze = document.getElementById('bb-switch-squeeze');
    var bbSqueezeText = document.getElementById('bb-squeeze-text');
    var bbSqueezeThresholdRow = document.getElementById('bb-squeeze-threshold-row');
    var bbSqueezeLookbackRow = document.getElementById('bb-squeeze-lookback-row');
    var bbInputSqueezePct = document.getElementById('bb-input-squeeze-pct');
    var bbInputSqueezeLookback = document.getElementById('bb-input-squeeze-lookback');
    var bbSwitchFlatChoppy = document.getElementById('bb-switch-flat-choppy');
    var bbFlatChoppyText = document.getElementById('bb-flat-choppy-text');
    var bbFlatSlopeRow = document.getElementById('bb-flat-slope-row');
    var bbFlatMinBarsRow = document.getElementById('bb-flat-minbars-row');
    var bbInputFlatSlope = document.getElementById('bb-input-flat-slope');
    var bbInputFlatMinBars = document.getElementById('bb-input-flat-minbars');
    var btnBbApply = document.getElementById('btn-bb-apply');

    function updateBollingerLabel() {
      if (!btnToggleBollinger) return;
      var typeLabel = bbMaType.toUpperCase();
      var sqLabel = showSqueeze ? ' 🟣' : '';
      var fcLabel = showFlatChoppy ? ' ⚖️' : '';
      btnToggleBollinger.textContent = '📈 BB (' + typeLabel + ' ' + bbPeriod + ', ' + bbStdDev + 'σ)' + sqLabel + fcLabel;
    }

    // Debounce helper for live numeric input updates
    var bbDebounceTimer = null;
    function bbDebouncedUpdate(delay) {
      if (bbDebounceTimer) clearTimeout(bbDebounceTimer);
      bbDebounceTimer = setTimeout(function () {
        applyBBSettingsLive();
      }, delay || 300);
    }

    // Shared function: read all settings and update BB immediately
    function applyBBSettingsLive() {
      var newPeriod = parseInt(bbInputPeriod.value, 10);
      var newStdDev = parseFloat(bbInputStdDev.value);
      var newMaType = bbInputMaType ? bbInputMaType.value : 'sma';
      var newSqueezePct = parseInt(bbInputSqueezePct ? bbInputSqueezePct.value : '20', 10);
      var newSqueezeLookback = parseInt(bbInputSqueezeLookback ? bbInputSqueezeLookback.value : '120', 10);
      var newFlatSlope = parseFloat(bbInputFlatSlope ? bbInputFlatSlope.value : '0.008');
      var newFlatMinBars = parseInt(bbInputFlatMinBars ? bbInputFlatMinBars.value : '3', 10);

      if (newPeriod >= 2 && newPeriod <= 200) bbPeriod = newPeriod;
      if (newStdDev >= 0.1 && newStdDev <= 5) bbStdDev = newStdDev;
      if (['sma', 'ema', 'hma'].indexOf(newMaType) >= 0) bbMaType = newMaType;
      if (newSqueezePct >= 5 && newSqueezePct <= 50) squeezePct = newSqueezePct;
      if (newSqueezeLookback >= 20 && newSqueezeLookback <= 500) squeezeLookback = newSqueezeLookback;
      if (!isNaN(newFlatSlope) && newFlatSlope > 0) flatSlopeThreshold = newFlatSlope;
      if (!isNaN(newFlatMinBars) && newFlatMinBars >= 1 && newFlatMinBars <= 30) flatMinBars = newFlatMinBars;

      if (showBollinger) {
        updateBollingerBands();
      } else if (showFlatChoppy) {
        updateFlatChoppyZones();
      }
      updateBollingerLabel();
    }

    function syncFlatChoppyUI(enabled) {
      showFlatChoppy = enabled;
      if (switchFlatChoppy) switchFlatChoppy.checked = showFlatChoppy;
      if (switchFlatChoppyText) {
        switchFlatChoppyText.textContent = showFlatChoppy ? 'ON' : 'OFF';
        switchFlatChoppyText.classList.toggle('off', !showFlatChoppy);
      }
      if (bbSwitchFlatChoppy) bbSwitchFlatChoppy.checked = showFlatChoppy;
      if (bbFlatChoppyText) {
        bbFlatChoppyText.textContent = showFlatChoppy ? 'ON' : 'OFF';
        bbFlatChoppyText.classList.toggle('off', !showFlatChoppy);
      }
      if (bbFlatSlopeRow) {
        bbFlatSlopeRow.style.display = showFlatChoppy ? 'flex' : 'none';
      }
      if (bbFlatMinBarsRow) {
        bbFlatMinBarsRow.style.display = showFlatChoppy ? 'flex' : 'none';
      }

      if (showFlatChoppy) {
        if (!showBollinger && btnToggleBollinger) {
          showBollinger = true;
          btnToggleBollinger.classList.add('active');
        }
        updateBollingerBands();
      } else {
        removeFlatChoppyZones();
        if (showBollinger) {
          updateBollingerBands();
        }
      }
      updateBollingerLabel();
    }

    // --- Live auto-update listeners ---

    // Flat / Choppy toggle on Toolbar & inside Popover
    if (switchFlatChoppy) {
      switchFlatChoppy.addEventListener('change', function () {
        syncFlatChoppyUI(this.checked);
      });
    }
    if (bbSwitchFlatChoppy) {
      bbSwitchFlatChoppy.addEventListener('change', function () {
        syncFlatChoppyUI(this.checked);
      });
    }
    if (bbInputFlatSlope) {
      bbInputFlatSlope.addEventListener('input', function () {
        bbDebouncedUpdate(300);
      });
    }
    if (bbInputFlatMinBars) {
      bbInputFlatMinBars.addEventListener('input', function () {
        bbDebouncedUpdate(300);
      });
    }

    // MA Type select → instant update
    if (bbInputMaType) {
      bbInputMaType.addEventListener('change', function () {
        applyBBSettingsLive();
      });
    }

    // Period input → debounced update
    if (bbInputPeriod) {
      bbInputPeriod.addEventListener('input', function () {
        bbDebouncedUpdate(300);
      });
    }

    // Std Dev input → debounced update
    if (bbInputStdDev) {
      bbInputStdDev.addEventListener('input', function () {
        bbDebouncedUpdate(300);
      });
    }

    // Squeeze toggle → instant update
    if (bbSwitchSqueeze) {
      bbSwitchSqueeze.addEventListener('change', function () {
        showSqueeze = this.checked;
        if (bbSqueezeText) {
          bbSqueezeText.textContent = showSqueeze ? 'ON' : 'OFF';
          bbSqueezeText.classList.toggle('off', !showSqueeze);
        }
        if (bbSqueezeThresholdRow) {
          bbSqueezeThresholdRow.style.display = showSqueeze ? 'flex' : 'none';
        }
        if (bbSqueezeLookbackRow) {
          bbSqueezeLookbackRow.style.display = showSqueeze ? 'flex' : 'none';
        }
        applyBBSettingsLive();
      });
    }

    // Squeeze percentile → debounced update
    if (bbInputSqueezePct) {
      bbInputSqueezePct.addEventListener('input', function () {
        bbDebouncedUpdate(300);
      });
    }

    // Squeeze lookback → debounced update
    if (bbInputSqueezeLookback) {
      bbInputSqueezeLookback.addEventListener('input', function () {
        bbDebouncedUpdate(300);
      });
    }

    // BB main toggle
    if (btnToggleBollinger) {
      btnToggleBollinger.addEventListener('click', function () {
        showBollinger = !showBollinger;
        this.classList.toggle('active', showBollinger);
        if (showBollinger) {
          updateBollingerBands();
          updateBollingerLabel();
        } else {
          removeBollingerBands();
        }
      });
    }

    // Settings gear button
    if (btnBbSettings) {
      btnBbSettings.addEventListener('click', function (e) {
        e.stopPropagation();
        bbPopover.classList.toggle('open');
      });
    }

    // Close popover on outside click
    document.addEventListener('click', function (e) {
      if (bbPopover && bbPopover.classList.contains('open')) {
        if (!bbPopover.contains(e.target) && e.target !== btnBbSettings) {
          bbPopover.classList.remove('open');
        }
      }
    });

    // Apply button now just closes the popover (settings are already live) and saves config
    if (btnBbApply) {
      btnBbApply.addEventListener('click', function () {
        bbPopover.classList.remove('open');
        savePageConfig();
      });
    }

    // ==========================================
    //  Page Configuration (MySQL `pageConfig`)
    // ==========================================

    var btnSaveConfig = document.getElementById('btn-save-config');
    var configStatusText = document.getElementById('config-status-text');
    var configToast = document.getElementById('config-toast');
    var configToastMsg = document.getElementById('config-toast-msg');
    var configToastIcon = document.getElementById('config-toast-icon');
    var toastTimer = null;

    /**
     * Enhanced Multi-type Toast Notification
     * @param {string} msg Message to display
     * @param {string|boolean} type 'success' (default), 'error', 'warn', 'info', 'sync'
     * @param {number} duration Timeout in ms
     */
    function showToast(msg, type, duration) {
      if (!configToast) return;
      if (toastTimer) clearTimeout(toastTimer);
      if (configToastMsg) configToastMsg.textContent = msg || '';

      var isErr = (type === true || type === 'error');
      var isWarn = (type === 'warn');
      var isInfo = (type === 'info');
      var isSync = (type === 'sync');

      var icon = '✅';
      if (isErr) icon = '❌';
      else if (isWarn) icon = '⚠️';
      else if (isInfo) icon = 'ℹ️';
      else if (isSync) icon = '⚡';

      if (configToastIcon) configToastIcon.textContent = icon;

      configToast.classList.remove('error', 'warn', 'info', 'sync');
      if (isErr) configToast.classList.add('error');
      else if (isWarn) configToast.classList.add('warn');
      else if (isInfo) configToast.classList.add('info');
      else if (isSync) configToast.classList.add('sync');

      configToast.classList.add('show');
      var d = duration || (isErr || isWarn || isSync ? 4500 : 3200);
      toastTimer = setTimeout(function () {
        configToast.classList.remove('show');
      }, d);
    }
    window.showToast = showToast;

    function setConfigStatus(msg, color, timeoutMs) {
      if (!configStatusText) return;
      configStatusText.textContent = msg || '';
      if (color) configStatusText.style.color = color;
      if (timeoutMs) {
        setTimeout(function () {
          if (configStatusText.textContent === msg) {
            configStatusText.textContent = '';
          }
        }, timeoutMs);
      }
    }

    function gatherCurrentConfig() {
      var scm = switchCodeNoMarker ? switchCodeNoMarker.checked : showCodeNoMarker;
      var stt = switchTooltip ? switchTooltip.checked : showTooltip;
      var ssl = switchSpotLines ? switchSpotLines.checked : showSpotLines;
      var sfc = switchFlatChoppy ? switchFlatChoppy.checked : showFlatChoppy;
      return {
        showWinCon: showWinCon,
        showLossCon: showLossCon,
        showAllTrades: showAllTrades,
        labelMode: labelMode,
        showCodeNoMarker: scm,
        showTooltip: stt,
        showSpotLines: ssl,
        showMarkZone: showMarkZone,
        showAnalyzeZones: showAnalyzeZones,
        showBollinger: showBollinger,
        bbMaType: bbMaType,
        bbPeriod: bbPeriod,
        bbStdDev: bbStdDev,
        showSqueeze: showSqueeze,
        squeezePct: squeezePct,
        squeezeLookback: squeezeLookback,
        showFlatChoppy: sfc,
        showChoppyCombined: showChoppyCombined,
        choppyCombineMode: choppyCombineMode,
        choppyGroupSize: choppyGroupSize,
        choppyChopThreshold: choppyChopThreshold,
        choppyBodyRatioThreshold: choppyBodyRatioThreshold,
        choppyErThreshold: choppyErThreshold,
        choppyMinSwitches: choppyMinSwitches,
        flatSlopeThreshold: flatSlopeThreshold,
        flatMinBars: flatMinBars
      };
    }

    function applyConfig(cfg) {
      if (!cfg || typeof cfg !== 'object') return;

      // 1. winCon marker
      if (typeof cfg.showWinCon === 'boolean') {
        showWinCon = cfg.showWinCon;
        if (btnToggleWinCon) btnToggleWinCon.classList.toggle('active', showWinCon);
      }
      // 2. lossCon marker
      if (typeof cfg.showLossCon === 'boolean') {
        showLossCon = cfg.showLossCon;
        if (btnToggleLossCon) btnToggleLossCon.classList.toggle('active', showLossCon);
      }
      // 3. all signals
      if (typeof cfg.showAllTrades === 'boolean') {
        showAllTrades = cfg.showAllTrades;
        if (btnToggleAllTrades) btnToggleAllTrades.classList.toggle('active', showAllTrades);
      }
      // 4. label mode
      if (typeof cfg.labelMode === 'string') {
        labelMode = cfg.labelMode;
        if (btnToggleLabelMode) {
          if (labelMode === 'pnl') {
            btnToggleLabelMode.textContent = '🏷️ Label: Trade # & PnL';
            btnToggleLabelMode.classList.add('active');
          } else if (labelMode === 'codeno') {
            btnToggleLabelMode.textContent = '🏷️ Label: Code No';
            btnToggleLabelMode.classList.add('active');
          } else {
            labelMode = 'con';
            btnToggleLabelMode.textContent = '🏷️ Label: Con Count';
            btnToggleLabelMode.classList.remove('active');
          }
        }
      }
      // 5. switch code_no marker
      if (typeof cfg.showCodeNoMarker === 'boolean') {
        showCodeNoMarker = cfg.showCodeNoMarker;
        if (switchCodeNoMarker) {
          switchCodeNoMarker.checked = showCodeNoMarker;
        }
        if (switchCodeNoMarkerText) {
          switchCodeNoMarkerText.textContent = showCodeNoMarker ? 'ON' : 'OFF';
          switchCodeNoMarkerText.classList.toggle('off', !showCodeNoMarker);
        }
      }
      // 6. switch tooltip
      if (typeof cfg.showTooltip === 'boolean') {
        setTooltipState(cfg.showTooltip);
      }
      // 7. switch spot lines
      if (typeof cfg.showSpotLines === 'boolean') {
        showSpotLines = cfg.showSpotLines;
        if (switchSpotLines) {
          switchSpotLines.checked = showSpotLines;
        }
        if (switchSpotLinesText) {
          switchSpotLinesText.textContent = showSpotLines ? 'ON' : 'OFF';
          switchSpotLinesText.classList.toggle('off', !showSpotLines);
        }
        if (!showSpotLines) clearSpotLines();
      }
      // 8. mark zone
      if (typeof cfg.showMarkZone === 'boolean') {
        showMarkZone = cfg.showMarkZone;
        if (btnToggleMarkZone) btnToggleMarkZone.classList.toggle('active', showMarkZone);
      }
      // 8.1. analyze zones panel
      if (typeof cfg.showAnalyzeZones === 'boolean') {
        setAnalyzeZonesVisible(cfg.showAnalyzeZones);
      }
      // 9. Bollinger settings
      if (typeof cfg.bbMaType === 'string' && ['sma', 'ema', 'hma'].indexOf(cfg.bbMaType) >= 0) {
        bbMaType = cfg.bbMaType;
        if (bbInputMaType) bbInputMaType.value = bbMaType;
      }
      if (typeof cfg.bbPeriod === 'number' || (!isNaN(Number(cfg.bbPeriod)) && cfg.bbPeriod !== '')) {
        bbPeriod = Math.max(2, Math.min(200, parseInt(cfg.bbPeriod, 10) || 20));
        if (bbInputPeriod) bbInputPeriod.value = bbPeriod;
      }
      if (typeof cfg.bbStdDev === 'number' || (!isNaN(Number(cfg.bbStdDev)) && cfg.bbStdDev !== '')) {
        bbStdDev = Math.max(0.1, Math.min(5, parseFloat(cfg.bbStdDev) || 2));
        if (bbInputStdDev) bbInputStdDev.value = bbStdDev;
      }
      if (typeof cfg.showSqueeze === 'boolean') {
        showSqueeze = cfg.showSqueeze;
        if (bbSwitchSqueeze) bbSwitchSqueeze.checked = showSqueeze;
        if (bbSqueezeText) {
          bbSqueezeText.textContent = showSqueeze ? 'ON' : 'OFF';
          bbSqueezeText.classList.toggle('off', !showSqueeze);
        }
        if (bbSqueezeThresholdRow) bbSqueezeThresholdRow.style.display = showSqueeze ? 'flex' : 'none';
        if (bbSqueezeLookbackRow) bbSqueezeLookbackRow.style.display = showSqueeze ? 'flex' : 'none';
      }
      if (typeof cfg.squeezePct === 'number' || (!isNaN(Number(cfg.squeezePct)) && cfg.squeezePct !== '')) {
        squeezePct = Math.max(5, Math.min(50, parseInt(cfg.squeezePct, 10) || 20));
        if (bbInputSqueezePct) bbInputSqueezePct.value = squeezePct;
      }
      if (typeof cfg.squeezeLookback === 'number' || (!isNaN(Number(cfg.squeezeLookback)) && cfg.squeezeLookback !== '')) {
        squeezeLookback = Math.max(20, Math.min(500, parseInt(cfg.squeezeLookback, 10) || 120));
        if (bbInputSqueezeLookback) bbInputSqueezeLookback.value = squeezeLookback;
      }
      // 10. Bollinger main toggle
      if (typeof cfg.showBollinger === 'boolean') {
        showBollinger = cfg.showBollinger;
        if (btnToggleBollinger) btnToggleBollinger.classList.toggle('active', showBollinger);
      }
      // 11. Flat / Choppy Zone
      if (typeof cfg.showFlatChoppy === 'boolean') {
        showFlatChoppy = cfg.showFlatChoppy;
        if (switchFlatChoppy) switchFlatChoppy.checked = showFlatChoppy;
        if (switchFlatChoppyText) {
          switchFlatChoppyText.textContent = showFlatChoppy ? 'ON' : 'OFF';
          switchFlatChoppyText.classList.toggle('off', !showFlatChoppy);
        }
        if (bbSwitchFlatChoppy) bbSwitchFlatChoppy.checked = showFlatChoppy;
        if (bbFlatChoppyText) {
          bbFlatChoppyText.textContent = showFlatChoppy ? 'ON' : 'OFF';
          bbFlatChoppyText.classList.toggle('off', !showFlatChoppy);
        }
        if (bbFlatSlopeRow) bbFlatSlopeRow.style.display = showFlatChoppy ? 'flex' : 'none';
        if (bbFlatMinBarsRow) bbFlatMinBarsRow.style.display = showFlatChoppy ? 'flex' : 'none';
      }
      if (typeof cfg.showChoppyCombined === 'boolean') {
        setChoppyCombinedState(cfg.showChoppyCombined);
      }
      if (typeof cfg.choppyCombineMode === 'string' && ['or', 'and', 'microOnly', 'macroOnly'].indexOf(cfg.choppyCombineMode) >= 0) {
        choppyCombineMode = cfg.choppyCombineMode;
        if (choppyInputMode) choppyInputMode.value = choppyCombineMode;
      }
      if (typeof cfg.choppyGroupSize === 'number' || (!isNaN(Number(cfg.choppyGroupSize)) && cfg.choppyGroupSize !== '')) {
        choppyGroupSize = Math.max(2, Math.min(60, parseInt(cfg.choppyGroupSize, 10) || 15));
        if (choppyInputGroupSize) choppyInputGroupSize.value = choppyGroupSize;
      }
      if (typeof cfg.choppyChopThreshold === 'number' || (!isNaN(Number(cfg.choppyChopThreshold)) && cfg.choppyChopThreshold !== '')) {
        choppyChopThreshold = parseFloat(cfg.choppyChopThreshold) || 61.8;
        if (choppyInputChopThreshold) choppyInputChopThreshold.value = choppyChopThreshold;
      }
      if (typeof cfg.choppyErThreshold === 'number' || (!isNaN(Number(cfg.choppyErThreshold)) && cfg.choppyErThreshold !== '')) {
        choppyErThreshold = parseFloat(cfg.choppyErThreshold) || 0.35;
        if (choppyInputErThreshold) choppyInputErThreshold.value = choppyErThreshold;
      }
      if (typeof cfg.choppyBodyRatioThreshold === 'number' || (!isNaN(Number(cfg.choppyBodyRatioThreshold)) && cfg.choppyBodyRatioThreshold !== '')) {
        choppyBodyRatioThreshold = parseFloat(cfg.choppyBodyRatioThreshold) || 0.3;
        if (choppyInputBodyRatio) choppyInputBodyRatio.value = choppyBodyRatioThreshold;
      }
      if (typeof cfg.choppyMinSwitches === 'number' || (!isNaN(Number(cfg.choppyMinSwitches)) && cfg.choppyMinSwitches !== '')) {
        choppyMinSwitches = parseInt(cfg.choppyMinSwitches, 10) || 3;
        if (choppyInputSwitches) choppyInputSwitches.value = choppyMinSwitches;
      }
      if (typeof cfg.flatSlopeThreshold === 'number' || (!isNaN(Number(cfg.flatSlopeThreshold)) && cfg.flatSlopeThreshold !== '')) {
        flatSlopeThreshold = Math.max(0.0001, Math.min(1.0, parseFloat(cfg.flatSlopeThreshold) || 0.008));
        if (bbInputFlatSlope) bbInputFlatSlope.value = flatSlopeThreshold;
      }
      if (typeof cfg.flatMinBars === 'number' || (!isNaN(Number(cfg.flatMinBars)) && cfg.flatMinBars !== '')) {
        flatMinBars = Math.max(1, Math.min(20, parseInt(cfg.flatMinBars, 10) || 3));
        if (bbInputFlatMinBars) bbInputFlatMinBars.value = flatMinBars;
      }

      // Re-render chart series if data is already present
      if (candleSeries && allCandles.length > 0) {
        if (showBollinger) {
          updateBollingerBands();
        } else {
          removeBollingerBands();
          if (showFlatChoppy) {
            updateFlatChoppyZones();
          }
        }
        updateMarkers();
      }
      updateBollingerLabel();
    }

    async function loadPageConfig() {
      try {
        var targetKey = assetCode || 'default';
        var url = '../php/api_page_config.php?action=get&pageName=analysis_trade_chart&configKey=' + encodeURIComponent(targetKey) + '&_t=' + Date.now();
        var res = await fetch(url);
        var json = await res.json();
        if (json.success && json.configData) {
          applyConfig(json.configData);
          setConfigStatus('⚙️ โหลดการตั้งค่าจาก DB แล้ว (' + (json.configKey || targetKey) + ')', '#34d399', 3500);
        }
      } catch (err) {
        console.warn('loadPageConfig failed:', err);
      }
    }

    async function savePageConfig() {
      var cfg = gatherCurrentConfig();
      var key = assetCode || 'default';

      if (btnSaveConfig) {
        btnSaveConfig.disabled = true;
        btnSaveConfig.textContent = '⏳ กำลังบันทึก...';
      }
      setConfigStatus('กำลังบันทึกลง MySQL pageConfig...', '#fbbf24');

      try {
        var res = await fetch('../php/api_page_config.php?action=save', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            pageName: 'analysis_trade_chart',
            configKey: key,
            configData: cfg
          })
        });
        var json = await res.json();
        if (!json.success) {
          throw new Error(json.error || 'บันทึกไม่สำเร็จ');
        }

        // Also ensure 'default' is updated if we are on a specific asset
        if (key !== 'default') {
          fetch('../php/api_page_config.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              pageName: 'analysis_trade_chart',
              configKey: 'default',
              configData: cfg
            })
          }).catch(function () {});
        }

        setConfigStatus('✅ บันทึกค่าลง MySQL เรียบร้อย (' + key + ')', '#34d399', 4000);
        showToast('🎉 บันทึกการตั้งค่าลง MySQL table pageConfig เรียบร้อยแล้ว!');
      } catch (err) {
        setConfigStatus('❌ บันทึกไม่สำเร็จ: ' + err.message, '#f87171', 5000);
        showToast('❌ บันทึกไม่สำเร็จ: ' + err.message, true);
      } finally {
        if (btnSaveConfig) {
          btnSaveConfig.disabled = false;
          btnSaveConfig.textContent = '💾 บันทึกค่า';
        }
      }
    }

    if (btnSaveConfig) {
      btnSaveConfig.addEventListener('click', function () {
        savePageConfig();
      });
    }

    // ==========================================
    //  Lab Mode Implementation
    // ==========================================

    var labModeBar = document.getElementById('lab-mode-bar');
    var labBtnPrev = document.getElementById('lab-btn-prev');
    var labBtnNext = document.getElementById('lab-btn-next');
    var labBtnPrev5 = document.getElementById('lab-btn-prev5');
    var labBtnNext5 = document.getElementById('lab-btn-next5');
    var labBtnReset = document.getElementById('lab-btn-reset');
    var labBtnExit = document.getElementById('lab-btn-exit');
    var labInfoIdx = document.getElementById('lab-info-idx');
    var labInfoTotal = document.getElementById('lab-info-total');
    var labInfoSqueeze = document.getElementById('lab-info-squeeze');

    // Add Lab Mode toggle button to toggles group
    var btnToggleLabMode = document.getElementById('btn-toggle-lab-mode');
    if (btnToggleLabMode) {
      btnToggleLabMode.addEventListener('click', function () {
        if (!labMode) {
          enableLabMode();
        } else {
          disableLabMode();
        }
      });
    }

    function enableLabMode() {
      if (fullDataCandle.length === 0 && allCandles.length > 0) {
        fullDataCandle = allCandles.slice();
      }
      if (fullDataCandle.length === 0) return;

      labMode = true;
      labCutIndex = fullDataCandle.length - 1;
      labOriginalCutIndex = labCutIndex;
      if (btnToggleLabMode) btnToggleLabMode.classList.add('active');
      if (labModeBar) labModeBar.classList.add('active');
      updateLabInfo();
    }

    function disableLabMode() {
      labMode = false;
      labCutIndex = -1;
      labOriginalCutIndex = -1;
      if (btnToggleLabMode) btnToggleLabMode.classList.remove('active');
      if (labModeBar) labModeBar.classList.remove('active');

      // Restore full candle data
      if (fullDataCandle.length > 0) {
        allCandles = fullDataCandle.slice();
        candleSeries.setData(allCandles);
        updateMarkers();
        if (showBollinger) updateBollingerBands();
        chart.timeScale().fitContent();
      }
    }

    function labModeCutAtCandle(clickedTime) {
      var cutIdx = -1;
      for (var i = 0; i < fullDataCandle.length; i++) {
        if (fullDataCandle[i].time === clickedTime) {
          cutIdx = i;
          break;
        }
      }
      if (cutIdx < 0) return;

      labCutIndex = cutIdx;
      labOriginalCutIndex = cutIdx;
      labApplySlice();
    }

    function labModeNext(count) {
      if (!labMode || fullDataCandle.length === 0) return;
      var steps = count || 1;
      var newIdx = Math.min(labCutIndex + steps, fullDataCandle.length - 1);
      if (newIdx === labCutIndex) return;
      var oldIdx = labCutIndex;
      labCutIndex = newIdx;
      // ถ้าเดินหน้าทีละ 1 แท่ง ใช้ append mode (smooth ไม่ flash)
      labApplySlice(steps === 1 && newIdx === oldIdx + 1);
    }

    function labModePrev(count) {
      if (!labMode || fullDataCandle.length === 0) return;
      var steps = count || 1;
      var newIdx = Math.max(labCutIndex - steps, 0);
      if (newIdx === labCutIndex) return;
      labCutIndex = newIdx;
      labApplySlice(false);
    }

    function labModeReset() {
      if (!labMode || labOriginalCutIndex < 0) return;
      labCutIndex = labOriginalCutIndex;
      labApplySlice(false);
    }

    function labApplySlice(appendMode) {
      allCandles = fullDataCandle.slice(0, labCutIndex + 1);

      // บันทึก visible range ก่อนเสมอ เพื่อให้กราฟอยู่ใน position เดิม
      var prevRange = chart.timeScale().getVisibleLogicalRange();

      if (appendMode && allCandles.length > 0) {
        // ===== Smooth append: ต่อแท่งเทียนใหม่โดยไม่ reset view =====
        var newCandle = allCandles[allCandles.length - 1];
        candleSeries.update(newCandle);
        // อัพเดต BB แบบ append ถ้าเปิด BB ไว้
        if (showBollinger) {
          labUpdateBBAppend();
        }
        updateMarkers();
      } else {
        // ===== Jump mode: setData ทั้งชุด =====
        candleSeries.setData(allCandles);
        updateMarkers();
        if (showBollinger) {
          updateBollingerBands();
        } else if (showFlatChoppy) {
          updateFlatChoppyZones();
        }
      }

      // คืนค่า range เดิมทุกกรณี — กราฟไม่เลื่อน ไม่ scroll ไปไหน
      if (prevRange !== null) {
        chart.timeScale().setVisibleLogicalRange(prevRange);
      }

      // Re-trigger mark zones redraw if any exist
      if (markZones && markZones.length > 0) {
        markZones.forEach(function (z) {
          if (z.primitive && z.primitive._requestUpdate) {
            z.primitive._requestUpdate();
          }
        });
      }

      updateLabInfo();
    }

    // อัพเดต BB series แบบ append เฉพาะจุดสุดท้าย (smooth, ไม่ flash)
    function labUpdateBBAppend() {
      if (!bbUpperSeries || !bbMiddleSeries || !bbLowerSeries) {
        // ยังไม่มี series — ต้องสร้างใหม่เต็มๆ
        updateBollingerBands();
        return;
      }
      var bb = calculateBollingerBands(allCandles, bbPeriod, bbStdDev, bbMaType);
      if (bb.upper.length === 0) return;

      var lastUpper = bb.upper[bb.upper.length - 1];
      var lastMiddle = bb.middle[bb.middle.length - 1];
      var lastLower = bb.lower[bb.lower.length - 1];

      if (lastUpper && lastUpper.value !== null) bbUpperSeries.update(lastUpper);
      if (lastMiddle && lastMiddle.value !== null) bbMiddleSeries.update(lastMiddle);
      if (lastLower && lastLower.value !== null) bbLowerSeries.update(lastLower);

      // Squeeze ต้อง recalculate ทั้งหมดเพราะ rolling window เปลี่ยน
      if (showSqueeze) {
        updateSqueezeZones(bb);
      }
      if (showFlatChoppy) {
        updateFlatChoppyZones(bb);
      }
    }

    function updateLabInfo() {
      if (labInfoIdx) labInfoIdx.textContent = labCutIndex + 1;
      if (labInfoTotal) labInfoTotal.textContent = fullDataCandle.length;

      // Update squeeze info
      if (labInfoSqueeze && showBollinger && showSqueeze && allCandles.length > 0) {
        var bb = calculateBollingerBands(allCandles, bbPeriod, bbStdDev, bbMaType);
        if (bb.upper.length > 0) {
          var lastBw = bb.upper[bb.upper.length - 1].value - bb.lower[bb.lower.length - 1].value;
          var lastMid = bb.middle[bb.middle.length - 1].value;
          var normBw = lastMid > 0 ? (lastBw / lastMid).toFixed(6) : lastBw.toFixed(6);
          var zones = detectSqueezeZones(bb, squeezePct, squeezeLookback);
          var lastTime = allCandles[allCandles.length - 1].time;
          var inSqueeze = zones.some(function (z) { return lastTime >= z.startTime && lastTime <= z.endTime; });
          labInfoSqueeze.innerHTML = '🟣 Squeeze: <b style="color:' + (inSqueeze ? '#e040fb' : '#94a3b8') + ';">' + (inSqueeze ? '● ACTIVE' : '○ None') + '</b> | BW: <b>' + normBw + '</b>';
        }
      } else if (labInfoSqueeze) {
        labInfoSqueeze.innerHTML = '🟣 Squeeze: <b>-</b>';
      }

      // Update flat/choppy info
      var labInfoFlatChoppy = document.getElementById('lab-info-flat-choppy');
      if (labInfoFlatChoppy && showFlatChoppy && allCandles.length > 0) {
        var bbFC = calculateBollingerBands(allCandles, bbPeriod, bbStdDev, bbMaType);
        var fcZones = detectFlatChoppyZones(bbFC, flatSlopeThreshold, flatMinBars);
        var lastTimeFC = allCandles[allCandles.length - 1].time;
        var inChoppy = fcZones.some(function (z) { return lastTimeFC >= z.startTime && lastTimeFC <= z.endTime; });
        labInfoFlatChoppy.innerHTML = '⚖️ Choppy: <b style="color:' + (inChoppy ? '#fbbf24' : '#94a3b8') + ';">' + (inChoppy ? '● ACTIVE' : '○ None') + '</b>';
      } else if (labInfoFlatChoppy) {
        labInfoFlatChoppy.innerHTML = '⚖️ Choppy: <b>-</b>';
      }

      // Update button disabled states
      if (labBtnPrev) labBtnPrev.disabled = labCutIndex <= 0;
      if (labBtnPrev5) labBtnPrev5.disabled = labCutIndex <= 0;
      if (labBtnNext) labBtnNext.disabled = labCutIndex >= fullDataCandle.length - 1;
      if (labBtnNext5) labBtnNext5.disabled = labCutIndex >= fullDataCandle.length - 1;
    }

    // Lab Mode button event listeners
    if (labBtnNext) labBtnNext.addEventListener('click', function () { labModeNext(1); });
    if (labBtnPrev) labBtnPrev.addEventListener('click', function () { labModePrev(1); });
    if (labBtnNext5) labBtnNext5.addEventListener('click', function () { labModeNext(5); });
    if (labBtnPrev5) labBtnPrev5.addEventListener('click', function () { labModePrev(5); });
    if (labBtnReset) labBtnReset.addEventListener('click', function () { labModeReset(); });
    if (labBtnExit) labBtnExit.addEventListener('click', function () { disableLabMode(); });

    // Keyboard shortcuts for Lab Mode
    document.addEventListener('keydown', function (e) {
      if (!labMode) return;
      // Don't trigger if user is typing in an input
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;

      if (e.key === 'ArrowRight' && e.shiftKey) {
        e.preventDefault();
        labModeNext(5);
      } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        labModeNext(1);
      } else if (e.key === 'ArrowLeft' && e.shiftKey) {
        e.preventDefault();
        labModePrev(5);
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        labModePrev(1);
      } else if (e.key === 'Escape') {
        e.preventDefault();
        disableLabMode();
      }
    });

    // Run initialization
    initChart();
    loadPageConfig().finally(function () {
      fetchFilterOptions(true);
    });

    /**
     * 10. Clear existing spot price lines
     */
    function clearSpotLines() {
      if (activeEntryLine && candleSeries) {
        try { candleSeries.removePriceLine(activeEntryLine); } catch (e) {}
        activeEntryLine = null;
      }
      if (activeExitLine && candleSeries) {
        try { candleSeries.removePriceLine(activeExitLine); } catch (e) {}
        activeExitLine = null;
      }
    }

    /**
     * 11. Handle chart click — draw entry/exit spot horizontal lines + Mark Zone
     */
    function handleChartClick(param) {
      if (!param.time || !candleSeries) return;

      var barTime = param.time;

      // === Mark Zone Mode (works in both normal mode and lab mode) ===
      if (showMarkZone) {
        handleMarkZoneClick(barTime);
        return; // When Mark Zone mode is active, draw/toggle zone without cutting candle or selecting trade
      }

      // === Lab Mode: Cut at clicked candle ===
      if (labMode && fullDataCandle.length > 0) {
        labModeCutAtCandle(barTime);
        return; // Don't process other click modes in lab mode
      }

      // Set selectedCandleTime to clicked candle
      selectedCandleTime = barTime;

      // === Select Trade on Clicked Candle ===
      var tradesOnBar = tradeByTimeMap[barTime];
      var matchedTrade = (tradesOnBar && tradesOnBar.length > 0) ? tradesOnBar[0] : null;

      if (!matchedTrade && allTrades && allTrades.length > 0) {
        for (var ti = 0; ti < allTrades.length; ti++) {
          var tcEpoch = parseInt(allTrades[ti].timeCandle, 10) || parseInt(allTrades[ti].purchaseTime, 10);
          if (tcEpoch) {
            var trNorm = Math.floor(tcEpoch / 60) * 60 + TZ_OFFSET_SEC;
            if (Math.abs(trNorm - barTime) <= 60) {
              matchedTrade = allTrades[ti];
              break;
            }
          }
        }
      }

      if (matchedTrade) {
        selectedTradeId = parseInt(matchedTrade.id, 10);
      } else {
        selectedTradeId = null;
      }

      // Update markers to put the yellow SELECTED marker on this candle (and clear old one)
      updateMarkers();

      // Find corresponding row in table, highlight it, and scroll it to the very top
      if (tradesTbody) {
        var targetRow = null;
        var allRows = tradesTbody.querySelectorAll('tr');
        allRows.forEach(function (r) {
          var rId = parseInt(r.dataset.id, 10);
          var rTime = parseInt(r.dataset.time, 10);
          var rNorm = rTime ? (Math.floor(rTime / 60) * 60 + TZ_OFFSET_SEC) : null;

          var isMatch = false;
          if (selectedTradeId && rId === selectedTradeId) {
            isMatch = true;
          } else if (rNorm && rNorm === barTime) {
            isMatch = true;
          } else if (rNorm && Math.abs(rNorm - barTime) <= 60) {
            isMatch = true;
          }

          if (isMatch) {
            if (!targetRow) targetRow = r;
            r.classList.add('active-row');
          } else {
            r.classList.remove('active-row');
          }
        });

        if (targetRow) {
          var tableWrap = document.querySelector('.trades-table-wrap');
          if (tableWrap) {
            var rowRect = targetRow.getBoundingClientRect();
            var wrapRect = tableWrap.getBoundingClientRect();
            var thEl = tableWrap.querySelector('th');
            var headerHeight = thEl ? thEl.offsetHeight : 37;
            var targetScrollTop = tableWrap.scrollTop + (rowRect.top - wrapRect.top) - headerHeight;
            tableWrap.scrollTo({
              top: Math.max(0, targetScrollTop),
              behavior: 'smooth'
            });
          }
        }
      }

      // === Spot Lines Mode ===
      if (showSpotLines) {
        clearSpotLines();
        if (matchedTrade) {
          var entryVal = parseFloat(matchedTrade.entrySpot);
          var exitVal = parseFloat(matchedTrade.exitSpot);

          if (entryVal && entryVal > 0) {
            activeEntryLine = candleSeries.createPriceLine({
              price: entryVal,
              color: '#10b981',
              lineWidth: 2,
              lineStyle: LightweightCharts.LineStyle.Dashed,
              axisLabelVisible: true,
              title: 'Entry ' + entryVal.toFixed(2)
            });
          }

          if (exitVal && exitVal > 0) {
            activeExitLine = candleSeries.createPriceLine({
              price: exitVal,
              color: '#f59e0b',
              lineWidth: 2,
              lineStyle: LightweightCharts.LineStyle.Dashed,
              axisLabelVisible: true,
              title: 'Exit ' + exitVal.toFixed(2)
            });
          }
        }
      }
    }

    /**
     * 12. Mark Zone — draw horizontal zone band on candle click
     *     upperZone = Max(High แท่งก่อนหน้า, High แท่งที่คลิก)
     *     lowerZone = Min(Low แท่งก่อนหน้า, Low แท่งที่คลิก)
     */
    function handleMarkZoneClick(barTime) {
      // Find the clicked candle and the previous candle
      var candleList = (allCandles && allCandles.length > 0) ? allCandles : fullDataCandle;
      var clickedIdx = -1;
      for (var i = 0; i < candleList.length; i++) {
        if (candleList[i].time === barTime) {
          clickedIdx = i;
          break;
        }
      }

      // Fallback search in fullDataCandle if not found in allCandles
      if (clickedIdx < 0 && fullDataCandle && fullDataCandle.length > 0) {
        candleList = fullDataCandle;
        for (var fi = 0; fi < candleList.length; fi++) {
          if (candleList[fi].time === barTime) {
            clickedIdx = fi;
            break;
          }
        }
      }

      if (clickedIdx < 0) return; // candle not found
      if (clickedIdx === 0) return; // no previous candle available

      var prevCandle = candleList[clickedIdx - 1];
      var clickedCandle = candleList[clickedIdx];

      // เปรียบเทียบค่า max ของ 2 แท่ง (แท่งปัจจุบันและแท่งก่อนหน้า) เลือกค่า max ที่สูงสุดมาเป็น upper zone
      var upperZone = Math.max(prevCandle.high, clickedCandle.high);

      // เปรียบเทียบค่า min ของ 2 แท่ง (แท่งปัจจุบันและแท่งก่อนหน้า) เลือกค่า min ที่ต่ำสุดมาเป็น lower zone
      var lowerZone = Math.min(prevCandle.low, clickedCandle.low);

      var zoneTop = upperZone;
      var zoneBottom = lowerZone;

      // Avoid zero-height zones
      if (Math.abs(zoneTop - zoneBottom) < 0.0001) return;

      // Check if an identical zone already exists (toggle off = remove it)
      for (var j = 0; j < markZones.length; j++) {
        if (Math.abs(markZones[j].top - zoneTop) < 0.0001 && Math.abs(markZones[j].bottom - zoneBottom) < 0.0001) {
          removeMarkZone(j);
          return;
        }
      }

      // Cycle through zone colors for visual distinction
      var zoneColors = [
        { fill: 'rgba(251, 191, 36, 0.12)', border: '#fbbf24', label: '#fbbf24' },  // amber
        { fill: 'rgba(139, 92, 246, 0.12)', border: '#8b5cf6', label: '#8b5cf6' },  // purple
        { fill: 'rgba(6, 182, 212, 0.12)',  border: '#06b6d4', label: '#06b6d4' },  // cyan
        { fill: 'rgba(236, 72, 153, 0.12)', border: '#ec4899', label: '#ec4899' },  // pink
        { fill: 'rgba(34, 197, 94, 0.12)',  border: '#22c55e', label: '#22c55e' },   // green
        { fill: 'rgba(249, 115, 22, 0.12)', border: '#f97316', label: '#f97316' }   // orange
      ];
      var colorIdx = markZones.length % zoneColors.length;
      var zoneColor = zoneColors[colorIdx];

      var zoneNo = markZones.length + 1;

      // นำไปกำหนดค่า zoneNo, timecandle, color, upperZone, lowerZone ในตัวแปร zones / zonesAnalysis ที่สร้างมาใหม่
      var newZoneData = {
        zoneNo: zoneNo,
        timecandle: String(barTime),
        color: zoneColor.border,
        upperZone: upperZone,
        lowerZone: lowerZone,
        candleList: []
      };

      if (typeof createZone === 'function') {
        createZone(newZoneData);
      } else {
        if (typeof zonesAnalysis !== 'undefined' && Array.isArray(zonesAnalysis)) {
          zonesAnalysis.push(newZoneData);
        }
        if (typeof zones !== 'undefined' && Array.isArray(zones) && zones !== zonesAnalysis) {
          zones.push(newZoneData);
        }
      }

      // Create price lines for top and bottom boundaries
      var topLine = candleSeries.createPriceLine({
        price: zoneTop,
        color: zoneColor.border,
        lineWidth: 1,
        lineStyle: LightweightCharts.LineStyle.Dotted,
        axisLabelVisible: true,
        title: '▲ Zone ' + zoneNo + ' Top'
      });

      var bottomLine = candleSeries.createPriceLine({
        price: zoneBottom,
        color: zoneColor.border,
        lineWidth: 1,
        lineStyle: LightweightCharts.LineStyle.Dotted,
        axisLabelVisible: true,
        title: '▼ Zone ' + zoneNo + ' Bot'
      });

      // Create horizontal band primitive
      var bandPrimitive = new HorizontalBandPrimitive(zoneTop, zoneBottom, zoneColor.fill, zoneColor.border);
      candleSeries.attachPrimitive(bandPrimitive);

      markZones.push({
        top: zoneTop,
        bottom: zoneBottom,
        topLine: topLine,
        bottomLine: bottomLine,
        primitive: bandPrimitive,
        barTime: barTime,
        zoneData: newZoneData
      });

      updateZoneCountBadge();
    }

    /**
     * Remove a single mark zone by index
     */
    function removeMarkZone(idx) {
      if (idx < 0 || idx >= markZones.length) return;
      var z = markZones[idx];
      try { candleSeries.removePriceLine(z.topLine); } catch (e) {}
      try { candleSeries.removePriceLine(z.bottomLine); } catch (e) {}
      try { candleSeries.detachPrimitive(z.primitive); } catch (e) {}
      markZones.splice(idx, 1);

      // ลบออกจากตัวแปร zones / zonesAnalysis และจัดลำดับ zoneNo ใหม่
      var targetArray = (typeof zonesAnalysis !== 'undefined' && Array.isArray(zonesAnalysis))
        ? zonesAnalysis
        : ((typeof zones !== 'undefined' && Array.isArray(zones)) ? zones : null);

      if (targetArray && idx < targetArray.length) {
        targetArray.splice(idx, 1);
        for (var k = 0; k < targetArray.length; k++) {
          targetArray[k].zoneNo = k + 1;
        }
      }

      updateZoneCountBadge();
    }

    /**
     * Clear all mark zones
     */
    function clearAllMarkZones() {
      for (var i = markZones.length - 1; i >= 0; i--) {
        var z = markZones[i];
        try { candleSeries.removePriceLine(z.topLine); } catch (e) {}
        try { candleSeries.removePriceLine(z.bottomLine); } catch (e) {}
        try { candleSeries.detachPrimitive(z.primitive); } catch (e) {}
      }
      markZones = [];

      // ล้างข้อมูลในตัวแปร zones / zonesAnalysis
      if (typeof clearZonesAnalysis === 'function') {
        clearZonesAnalysis();
      } else {
        if (typeof zonesAnalysis !== 'undefined' && Array.isArray(zonesAnalysis)) {
          zonesAnalysis.length = 0;
        }
        if (typeof zones !== 'undefined' && Array.isArray(zones)) {
          zones.length = 0;
        }
      }

      updateZoneCountBadge();
    }

    /**
     * Update zone count badge display
     */
    function updateZoneCountBadge() {
      if (!zoneCountBadge) return;
      if (markZones.length > 0) {
        zoneCountBadge.textContent = markZones.length;
        zoneCountBadge.style.display = 'inline-flex';
      } else {
        zoneCountBadge.style.display = 'none';
      }
    }

    /**
     * HorizontalBandPrimitive — draws a filled horizontal band across the full chart width
     * using the Lightweight Charts Series Primitives API
     */
    function HorizontalBandPrimitive(topPrice, bottomPrice, fillColor, borderColor) {
      this._topPrice = topPrice;
      this._bottomPrice = bottomPrice;
      this._fillColor = fillColor;
      this._borderColor = borderColor;
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
      this._paneView = new HorizontalBandPaneView(this);
    }

    HorizontalBandPrimitive.prototype.attached = function (param) {
      this._series = param.series;
      this._chart = param.chart;
      this._requestUpdate = param.requestUpdate;
      if (this._requestUpdate) {
        this._requestUpdate();
      }
    };

    HorizontalBandPrimitive.prototype.detached = function () {
      this._series = null;
      this._chart = null;
      this._requestUpdate = null;
    };

    HorizontalBandPrimitive.prototype.updateAllViews = function () {
      this._paneView.update(this);
    };

    HorizontalBandPrimitive.prototype.paneViews = function () {
      return [this._paneView];
    };

    /**
     * HorizontalBandPaneView — renders the filled rectangle
     */
    function HorizontalBandPaneView(primitive) {
      this._primitive = primitive;
      this._topY = 0;
      this._bottomY = 0;
    }

    HorizontalBandPaneView.prototype.zOrder = function () {
      return 'bottom';
    };

    HorizontalBandPaneView.prototype.update = function (source) {
      if (!source) source = this._primitive;
      var series = source._series;
      var chart = source._chart;
      if (!series || !chart) return;

      // Convert prices to pixel Y coordinates
      var priceScale = series.priceScale();
      if (!priceScale) return;

      // Use coordinate conversion
      var topY = series.priceToCoordinate(source._topPrice);
      var bottomY = series.priceToCoordinate(source._bottomPrice);

      if (topY === null || bottomY === null) return;

      this._topY = Math.min(topY, bottomY);
      this._bottomY = Math.max(topY, bottomY);
    };

    HorizontalBandPaneView.prototype.renderer = function () {
      var topY = this._topY;
      var bottomY = this._bottomY;
      var fillColor = this._primitive._fillColor;
      var borderColor = this._primitive._borderColor;

      return {
        draw: function (target) {
          target.useBitmapCoordinateSpace(function (scope) {
            var ctx = scope.context;
            var ratio = scope.horizontalPixelRatio;
            var vRatio = scope.verticalPixelRatio;

            var x0 = 0;
            var x1 = scope.bitmapSize.width;
            var y0 = topY * vRatio;
            var y1 = bottomY * vRatio;
            var h = y1 - y0;

            if (h <= 0) return;

            // Fill the zone
            ctx.fillStyle = fillColor;
            ctx.fillRect(x0, y0, x1, h);

            // Draw top & bottom border lines
            ctx.strokeStyle = borderColor;
            ctx.lineWidth = 1 * ratio;
            ctx.setLineDash([4 * ratio, 3 * ratio]);

            ctx.beginPath();
            ctx.moveTo(x0, y0);
            ctx.lineTo(x1, y0);
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(x0, y1);
            ctx.lineTo(x1, y1);
            ctx.stroke();

            ctx.setLineDash([]);
          });
        }
      };
    };
    // ==========================================
    //  Auto-Load Pipeline & Modal Controller
    // ==========================================

    function getVpsExportUrl(vps, date) {
      if (!vps) return '';
      var base = '';
      if (vps.url && vps.url.trim() !== '') {
        base = vps.url.trim().replace(/\/+$/, '');
      } else if (vps.publicIP && vps.publicIP.trim() !== '') {
        var ip = vps.publicIP.trim().replace(/\/+$/, '');
        if (vps.portno && String(vps.portno).trim() !== '') {
          base = ip + ':' + String(vps.portno).trim();
        } else {
          base = ip;
        }
      } else {
        base = window.location.origin;
      }
      if (!/^https?:\/\//i.test(base)) {
        if (base.includes('pkderiv.online') || base.includes('gpkderiv.shop') || base.endsWith('.shop') || base.endsWith('.online')) {
          base = 'https://' + base;
        } else {
          base = 'http://' + base;
        }
      }
      return base + '/api/export_trade_data?startdatetime=' + date;
    }

    window.openAutoLoadConfirmModal = function(sCode, dStr, vpsObj, customReason) {
      currentAutoLoadServerCode = sCode;
      currentAutoLoadDate = dStr;
      currentAutoLoadVps = vpsObj || (serverObjectsMap[sCode] || null);

      var modal = document.getElementById('autoLoadModal');
      var dateBadge = document.getElementById('autoLoadDateBadge');
      var dateText = document.getElementById('promptDateText');
      var vpsText = document.getElementById('promptVpsText');
      var promptBody = document.getElementById('autoLoadPromptBody');
      var progressBody = document.getElementById('autoLoadProgressBody');
      var promptFooter = document.getElementById('autoLoadPromptFooter');
      var progressFooter = document.getElementById('autoLoadProgressFooter');
      var promptAlertTitle = document.getElementById('promptAlertTitle');
      var promptAlertDesc = document.getElementById('promptAlertDesc');

      if (dateBadge) dateBadge.textContent = dStr;
      if (dateText) dateText.textContent = dStr;
      var vName = currentAutoLoadVps ? ('[' + (currentAutoLoadVps.vpsCode || sCode) + '] ' + (currentAutoLoadVps.vpsName || 'Server #' + sCode)) : ('Server #' + sCode);
      if (vpsText) vpsText.textContent = vName;

      if (customReason) {
        if (promptAlertTitle) promptAlertTitle.textContent = '⚡ ดึงและอัปเดตข้อมูลล่าสุด (00:05 - now)';
        if (promptAlertDesc) promptAlertDesc.innerHTML = customReason + '<br>บนเซิร์ฟเวอร์ <b style="color:#38bdf8;">' + vName + '</b> ประจำวันที่ <b style="color:#fff;">' + dStr + '</b>';
      } else {
        if (promptAlertTitle) promptAlertTitle.textContent = 'ไม่พบข้อมูลในระบบสำหรับวันที่เลือก';
        if (promptAlertDesc) promptAlertDesc.innerHTML = 'ตรวจพบว่าวันที่ <b style="color:#fff;">' + dStr + '</b> บนเซิร์ฟเวอร์ <b style="color:#38bdf8;">' + vName + '</b> ยังไม่มีข้อมูลในฐานข้อมูล (derivTradeHistory, candles, vpsTradeData)';
      }

      // Reset to Prompt state
      if (promptBody) promptBody.style.display = 'block';
      if (progressBody) progressBody.style.display = 'none';
      if (promptFooter) promptFooter.style.display = 'flex';
      if (progressFooter) progressFooter.style.display = 'none';
      window.isAutoLoading = false;

      if (modal) modal.classList.add('active');
    };

    window.closeAutoLoadModal = function() {
      if (window.isAutoLoading) return;
      var modal = document.getElementById('autoLoadModal');
      if (modal) modal.classList.remove('active');
    };

    function appendAutoLoadLog(msg, type) {
      var consoleEl = document.getElementById('autoLoadLogConsole');
      if (!consoleEl) return;
      var timeStr = new Date().toTimeString().split(' ')[0];
      var div = document.createElement('div');
      div.className = 'log-line ' + (type || 'info');
      div.textContent = '[' + timeStr + '] ' + msg;
      consoleEl.appendChild(div);
      consoleEl.scrollTop = consoleEl.scrollHeight;

      var counter = document.getElementById('logCounter');
      if (counter) counter.textContent = consoleEl.children.length + ' events';
    }

    function setStepperStep(stepNum, status, badgeText, descText) {
      var item = document.getElementById('stepperStep' + stepNum);
      var badge = document.getElementById('stepBadge' + stepNum);
      var desc = document.getElementById('stepDesc' + stepNum);
      var iconBox = item ? item.querySelector('.stepper-icon') : null;

      if (item) {
        item.classList.remove('active', 'done', 'error');
        if (status === 'active') item.classList.add('active');
        else if (status === 'done') item.classList.add('done');
        else if (status === 'error') item.classList.add('error');
      }
      if (badge) {
        badge.className = 'stepper-status-badge ' + (status === 'active' ? 'badge-running' : (status === 'done' ? 'badge-done' : (status === 'error' ? 'badge-error' : 'badge-pending')));
        badge.textContent = badgeText || (status === 'active' ? 'กำลังทำงาน' : (status === 'done' ? 'สำเร็จ' : (status === 'error' ? 'ข้อผิดพลาด' : 'รอดำเนินการ')));
      }
      if (desc && descText) {
        desc.textContent = descText;
      }
      if (iconBox) {
        if (status === 'done') iconBox.textContent = '✓';
        else if (status === 'error') iconBox.textContent = '✕';
        else iconBox.textContent = String(stepNum);
      }
    }

    // Step 1: Load derivTradeHistory from Deriv (with pagination) and extract unique assets
    async function executeStep1_DerivTradeHistory(sCode, tDate) {
      var startEpoch = Math.floor(new Date(tDate + 'T00:00:00+07:00').getTime() / 1000);
      var endEpoch = Math.floor(new Date(tDate + 'T23:59:59+07:00').getTime() / 1000);

      // Get authenticated OTP WebSocket URL from server-side PHP
      var wsUrl = await getDerivWsUrl();
      appendAutoLoadLog('🔑 ได้รับ Deriv WS URL (OTP auth) สำเร็จ', 'info');

      var LIMIT = 100;

      // Use paginated fetch with offset — keeps WS open until all pages are retrieved
      var trades = await new Promise(function(resolve) {
        var allTrades = [];
        var offset = 0;
        var ws;
        try {
          ws = new WebSocket(wsUrl);
        } catch (e) {
          appendAutoLoadLog('❌ ไม่สามารถเปิด WebSocket ได้: ' + e.message, 'error');
          resolve([]);
          return;
        }

        // Safety timeout: 30 seconds max for the entire pagination cycle
        var safetyTimer = setTimeout(function() {
          appendAutoLoadLog('⏱️ Timeout 30s → ปิด WS, ได้ trades สะสม ' + allTrades.length + ' รายการ', 'warn');
          try { ws.close(); } catch (e) {}
          resolve(allTrades);
        }, 30000);

        function sendProfitTableReq() {
          var req = {
            profit_table: 1,
            description: 1,
            limit: LIMIT,
            offset: offset,
            date_from: startEpoch,
            date_to: endEpoch,
            sort: 'ASC'
          };
          appendAutoLoadLog('📤 ส่งคำขอ profit_table (offset: ' + offset + ', limit: ' + LIMIT + ')...', 'info');
          ws.send(JSON.stringify(req));
        }

        ws.onopen = function() {
          appendAutoLoadLog('🔗 Deriv WebSocket เชื่อมต่อสำเร็จ! เริ่มดึง profit_table...', 'success');
          offset = 0;
          sendProfitTableReq();
        };

        ws.onmessage = function(evt) {
          try {
            var data = JSON.parse(evt.data);

            // Handle API errors
            if (data.error) {
              appendAutoLoadLog('❌ Deriv API Error: ' + (data.error.message || data.error.code || 'Unknown'), 'error');
              clearTimeout(safetyTimer);
              try { ws.close(); } catch (e) {}
              resolve(allTrades);
              return;
            }

            if (data.msg_type === 'profit_table' && data.profit_table) {
              var txns = data.profit_table.transactions || [];
              for (var i = 0; i < txns.length; i++) {
                allTrades.push(txns[i]);
              }
              appendAutoLoadLog('📥 ได้รับ ' + txns.length + ' รายการ (ยอดสะสม: ' + allTrades.length + ' trades)', 'info');

              // Check if more pages available
              if (txns.length >= LIMIT) {
                offset += LIMIT;
                sendProfitTableReq();
              } else {
                // All pages fetched — done!
                appendAutoLoadLog('🎉 ดึง profit_table สมบูรณ์! รวมทั้งหมด ' + allTrades.length + ' รายการ', 'success');
                clearTimeout(safetyTimer);
                try { ws.close(); } catch (e) {}
                resolve(allTrades);
              }
            }
          } catch (e) {
            appendAutoLoadLog('⚠️ JSON parse error จาก WS message: ' + e.message, 'warn');
          }
        };

        ws.onerror = function() {
          appendAutoLoadLog('❌ WebSocket เกิดข้อผิดพลาด (ได้ trades สะสม: ' + allTrades.length + ')', 'error');
          clearTimeout(safetyTimer);
          resolve(allTrades);
        };

        ws.onclose = function() {
          // If resolve hasn't been called yet by onmessage, resolve now
          clearTimeout(safetyTimer);
          resolve(allTrades);
        };
      });

      // Save trades into derivTradeHistory if any returned
      if (trades && trades.length > 0) {
        appendAutoLoadLog('💾 บันทึก ' + trades.length + ' ไม้ลง MySQL (derivTradeHistory)...', 'info');
        try {
          var saveResp = await fetch('../php/save_trade_history.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ serverCode: sCode, trades: trades })
          });
          var saveJson = await saveResp.json();
          appendAutoLoadLog('✅ MySQL บันทึกสำเร็จ (inserted: ' + (saveJson.inserted || 0) + ', duplicates: ' + (saveJson.duplicates || 0) + ')', 'success');
        } catch (saveErr) {
          appendAutoLoadLog('⚠️ บันทึก derivTradeHistory ผิดพลาด: ' + saveErr.message, 'error');
        }
      } else {
        appendAutoLoadLog('ℹ️ ไม่พบ trade จาก Deriv profit_table ในวันที่ ' + tDate, 'info');
      }

      // Extract unique assets from fetched trades
      var foundAssets = [];
      trades.forEach(function(t) {
        // profit_table uses underlying_symbol, but also check shortcode fallback
        var sym = t.underlying_symbol || t.symbol;
        if (!sym && t.shortcode) {
          var m = t.shortcode.match(/(?:CALL|PUT|DIGITDIFF|DIGITEVEN|DIGITODD|DIGITMATCH|DIGITOVER|DIGITUNDER)_(1HZ\d+V|R_\d+|RDBEAR|RDBULL)/i);
          if (m) sym = m[1];
        }
        if (sym && foundAssets.indexOf(sym) === -1) {
          foundAssets.push(sym);
        }
      });

      // If no assets discovered from broker profit_table, provide standard default assets
      if (foundAssets.length === 0) {
        foundAssets = ['1HZ10V', '1HZ25V', '1HZ50V', '1HZ75V', '1HZ100V'];
        appendAutoLoadLog('ℹ️ ไม่พบรายการออเดอร์ใน profit_table → ใช้สินทรัพย์มาตรฐาน: ' + foundAssets.join(', '), 'info');
      }

      return { tradeCount: trades.length, assets: foundAssets };
    }

    // Step 2: Load candles from Deriv for each discovered asset (00:05:00 to now)
    async function executeStep2_DerivCandles(assets, tDate, sCode) {
      var startEpoch = Math.floor(new Date(tDate + 'T00:05:00+07:00').getTime() / 1000);
      var nowEpoch = Math.floor(Date.now() / 1000);
      var endEpoch = Math.min(Math.floor(new Date(tDate + 'T23:59:59+07:00').getTime() / 1000), nowEpoch);
      var totalCandles = 0;
      var wsUrl = await getDerivWsUrl();

      for (var i = 0; i < assets.length; i++) {
        var sym = assets[i];
        appendAutoLoadLog('📥 [' + (i + 1) + '/' + assets.length + '] ดึงแท่งเทียน 1 นาทีสำหรับ ' + sym + '...', 'info');

        var candles = await new Promise(function(resolve) {
          var ws;
          try {
            ws = new WebSocket(wsUrl);
          } catch (e) {
            resolve([]);
            return;
          }

          var timer = setTimeout(function() {
            try { ws.close(); } catch (e) {}
            resolve([]);
          }, 8000);

          ws.onopen = function() {
            ws.send(JSON.stringify({
              ticks_history: sym,
              style: 'candles',
              granularity: 60,
              start: startEpoch,
              end: endEpoch,
              adjust_start_time: 1
            }));
          };

          ws.onmessage = function(evt) {
            clearTimeout(timer);
            var cList = [];
            try {
              var res = JSON.parse(evt.data);
              if (res.candles && res.candles.length > 0) {
                cList = res.candles.map(function(c) {
                  return {
                    epoch: c.epoch || c.time,
                    open: c.open,
                    high: c.high,
                    low: c.low,
                    close: c.close
                  };
                });
              }
            } catch (e) {}
            try { ws.close(); } catch (e) {}
            resolve(cList);
          };

          ws.onerror = function() {
            clearTimeout(timer);
            resolve([]);
          };
        });

        if (candles.length > 0) {
          await fetch('../php/save_market_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              symbol: sym,
              serverCode: sCode,
              granularity: 60,
              candles: candles
            })
          });
          totalCandles += candles.length;
          appendAutoLoadLog('✅ ' + sym + ': บันทึก ' + candles.length + ' แท่งลงตาราง candles', 'success');
        } else {
          appendAutoLoadLog('⚠️ ' + sym + ': ไม่ได้รับแท่งเทียนจาก Deriv WS', 'warn');
        }
      }

      return { totalCandles: totalCandles };
    }

    // Step 3: Fetch vpsTradeData from VPS API (/api/export_trade_data)
    async function executeStep3_VpsTradeData(vps, tDate, sCode) {
      if (typeof JSZip === 'undefined') {
        throw new Error('ไม่พบ JSZip Library ในหน้าเว็บ กรุณารีเฟรชหน้านี้');
      }

      var exportUrl = getVpsExportUrl(vps, tDate);
      appendAutoLoadLog('🌐 ดาวน์โหลด ZIP จาก: ' + exportUrl, 'info');

      var arrayBuffer = null;
      try {
        var resp = await fetch(exportUrl);
        if (!resp.ok) throw new Error('Status ' + resp.status);
        arrayBuffer = await resp.arrayBuffer();
        appendAutoLoadLog('✅ Direct fetch สำเร็จ (' + arrayBuffer.byteLength + ' bytes)', 'success');
      } catch (directErr) {
        appendAutoLoadLog('🔄 Direct fetch ไม่สำเร็จ (' + directErr.message + ') -> สลับไปใช้ PHP Proxy...', 'info');
        var proxyResp = await fetch('../php/save_gcp_trades.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'download_and_save',
            exportUrl: exportUrl,
            serverCode: sCode
          })
        });
        var proxyJson = await proxyResp.json();
        if (!proxyJson.success || !proxyJson.zipBase64) {
          throw new Error(proxyJson.error || 'ดาวน์โหลด ZIP จาก VPS ผ่าน PHP Proxy ไม่สำเร็จ');
        }
        var binaryString = atob(proxyJson.zipBase64);
        var bytes = new Uint8Array(binaryString.length);
        for (var k = 0; k < binaryString.length; k++) {
          bytes[k] = binaryString.charCodeAt(k);
        }
        arrayBuffer = bytes.buffer;
        appendAutoLoadLog('✅ ดาวน์โหลดผ่าน PHP Proxy สำเร็จ (' + bytes.length + ' bytes)', 'success');
      }

      appendAutoLoadLog('📂 กำลังแตกไฟล์ ZIP ด้วย JSZip ใน Browser...', 'info');
      var zip = await JSZip.loadAsync(arrayBuffer);

      // 1. Process tradeHead.json
      var roundsSaved = 0;
      var headFile = zip.file('tradeHead.json') || zip.file(/.*tradeHead\.json$/i)[0];
      if (headFile) {
        appendAutoLoadLog('📋 พบไฟล์ tradeHead.json -> ส่งบันทึกลง MySQL...', 'info');
        var headText = await headFile.async('text');
        var headData = JSON.parse(headText);
        var headResp = await fetch('../php/save_trade_rounds.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            serverCode: sCode,
            tradeHead: headData
          })
        });
        var headJson = await headResp.json();
        roundsSaved = headJson.savedRounds || (Array.isArray(headData) ? headData.length : 1);
        appendAutoLoadLog('✅ บันทึก tradeHead สำเร็จ (' + roundsSaved + ' รอบ)', 'success');
      }

      // 2. Process trades files
      var tradeFiles = zip.file(/.*trades.*\.json$/i);
      if (tradeFiles.length === 0) {
        tradeFiles = zip.file(/\.json$/i);
      }
      var allTradesExtracted = [];
      for (var f of tradeFiles) {
        if (f.name.includes('tradeHead.json')) continue;
        try {
          var tText = await f.async('text');
          var parsed = JSON.parse(tText);
          if (Array.isArray(parsed)) allTradesExtracted = allTradesExtracted.concat(parsed);
          else if (parsed.trades && Array.isArray(parsed.trades)) allTradesExtracted = allTradesExtracted.concat(parsed.trades);
        } catch (e) {}
      }

      var tradesSaved = 0;
      if (allTradesExtracted.length > 0) {
        appendAutoLoadLog('⚡ บันทึก ' + allTradesExtracted.length + ' ไม้ลง MySQL (vpsTradeData)...', 'info');
        var trResp = await fetch('../php/save_gcp_trades.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            serverCode: sCode,
            trades: allTradesExtracted
          })
        });
        var trJson = await trResp.json();
        tradesSaved = trJson.totalSaved || allTradesExtracted.length;
        appendAutoLoadLog('✅ บันทึก vpsTradeData สำเร็จ (' + tradesSaved + ' ไม้)', 'success');
      }

      return { roundsCount: roundsSaved, tradesCount: tradesSaved };
    }

    // Orchestrator: Start Auto-Load Pipeline
    window.startAutoLoadPipeline = async function() {
      window.isAutoLoading = true;
      var promptBody = document.getElementById('autoLoadPromptBody');
      var progressBody = document.getElementById('autoLoadProgressBody');
      var promptFooter = document.getElementById('autoLoadPromptFooter');
      var progressFooter = document.getElementById('autoLoadProgressFooter');
      var btnFinish = document.getElementById('btnFinishAutoLoad');
      var btnCancel = document.getElementById('btnCancelAutoLoad');

      if (promptBody) promptBody.style.display = 'none';
      if (progressBody) progressBody.style.display = 'block';
      if (promptFooter) promptFooter.style.display = 'none';
      if (progressFooter) progressFooter.style.display = 'flex';
      if (btnFinish) btnFinish.style.display = 'none';
      if (btnCancel) btnCancel.disabled = true;

      var logConsole = document.getElementById('autoLoadLogConsole');
      if (logConsole) logConsole.innerHTML = '';
      setStepperStep(1, 'active', 'กำลังเชื่อมต่อ...', 'กำลังดึง derivTradeHistory จาก Deriv API...');
      setStepperStep(2, 'pending', 'รอดำเนินการ', 'รอรายการสินทรัพย์จากขั้นตอนที่ 1');
      setStepperStep(3, 'pending', 'รอดำเนินการ', 'รอดำเนินการ');

      var sCode = currentAutoLoadServerCode;
      var tDate = currentAutoLoadDate;
      var vps = currentAutoLoadVps;

      appendAutoLoadLog('🚀 เริ่มกระบวนการโหลดข้อมูลอัตโนมัติ สำหรับวันที่ ' + tDate + ' (VPS: ' + sCode + ')', 'info');

      try {
        // Step 1: derivTradeHistory & extract assets
        appendAutoLoadLog('🔑 [Step 1] กำลังเชื่อมต่อ Deriv API เพื่อดึง profit_table...', 'info');
        var step1Result = await executeStep1_DerivTradeHistory(sCode, tDate, vps);
        var foundAssets = step1Result.assets;
        setStepperStep(1, 'done', 'สำเร็จ (' + step1Result.tradeCount + ' ไม้)', 'พบสินทรัพย์ ' + foundAssets.length + ' รายการ: ' + foundAssets.join(', '));
        appendAutoLoadLog('✅ [Step 1] บันทึก derivTradeHistory สำเร็จ (' + step1Result.tradeCount + ' รายการ)', 'success');
        appendAutoLoadLog('🎯 [Step 1] รายชื่อสินทรัพย์เป้าหมาย: ' + foundAssets.join(', '), 'info');

        // Step 2: Candles for each asset
        setStepperStep(2, 'active', 'กำลังดึงแท่งเทียน...', 'กำลังดึงแท่งเทียน 1 นาทีสำหรับ ' + foundAssets.length + ' สินทรัพย์...');
        appendAutoLoadLog('🕯️ [Step 2] เริ่มดึงข้อมูลแท่งเทียน 1 นาที (OHLC) จาก Deriv...', 'info');
        var step2Result = await executeStep2_DerivCandles(foundAssets, tDate, sCode);
        setStepperStep(2, 'done', 'สำเร็จ (' + step2Result.totalCandles + ' แท่ง)', 'บันทึกแท่งเทียน ' + foundAssets.length + ' สินทรัพย์ รวม ' + step2Result.totalCandles + ' แท่ง');
        appendAutoLoadLog('✅ [Step 2] บันทึกแท่งเทียนลงตาราง candles สำเร็จ รวม ' + step2Result.totalCandles + ' แท่ง', 'success');

        // Step 3: vpsTradeData & tradeHead
        setStepperStep(3, 'active', 'กำลังดึงจาก VPS...', 'กำลังดาวน์โหลดและแตกไฟล์ ZIP จาก VPS API...');
        appendAutoLoadLog('📦 [Step 3] ส่งคำขอ /api/export_trade_data ไปยัง VPS...', 'info');
        var step3Result = await executeStep3_VpsTradeData(vps, tDate, sCode);
        setStepperStep(3, 'done', 'สำเร็จ (' + step3Result.tradesCount + ' ไม้)', 'บันทึก tradeHead (' + step3Result.roundsCount + ' รอบ) และ vpsTradeData (' + step3Result.tradesCount + ' ไม้)');
        appendAutoLoadLog('✅ [Step 3] แตกไฟล์และบันทึก vpsTradeData สำเร็จ (' + step3Result.tradesCount + ' ไม้, ' + step3Result.roundsCount + ' รอบ)', 'success');

        // Completion
        appendAutoLoadLog('🎉 ทุกขั้นตอนเสร็จสมบูรณ์ 100%! กำลังอัปเดตหน้าจอ...', 'success');
        showToast('✅ โหลดข้อมูลของวันที่ ' + tDate + ' สำเร็จครบถ้วนแล้ว!', 'success', 5000);

        if (btnFinish) btnFinish.style.display = 'inline-flex';
        if (btnCancel) btnCancel.disabled = false;
        window.isAutoLoading = false;

        setTimeout(function() {
          window.finishAutoLoadAndRender();
        }, 1500);

      } catch (err) {
        console.error('Auto-load pipeline error:', err);
        appendAutoLoadLog('❌ ข้อผิดพลาด: ' + err.message, 'error');
        showToast('❌ การโหลดข้อมูลขัดข้อง: ' + err.message, 'error', 6000);
        if (btnCancel) btnCancel.disabled = false;
        window.isAutoLoading = false;
      }
    };

    window.finishAutoLoadAndRender = function() {
      window.closeAutoLoadModal();
      fetchFilterOptions(false).then(function() {
        clearChartState();
        loadData();
      });
    };

    // =============================================
    // Karma Evaluation System
    // =============================================
    var btnEvalKarma = document.getElementById('btn-eval-karma');
    var karmaEvalModal = document.getElementById('karma-eval-modal');
    var btnCloseKarmaEval = document.getElementById('btn-close-karma-eval');

    function evaluateKarmaFilter() {
      if (!allTrades || allTrades.length === 0) {
        showToast('⚠️ ไม่มีข้อมูลเทรด กรุณาโหลดข้อมูลก่อน', 'error', 3000);
        return;
      }
      if (!allCandles || allCandles.length < 5 || typeof window.analyzeChoppiness !== 'function') {
        showToast('⚠️ ไม่มีข้อมูลแท่งเทียนเพียงพอ หรือไม่พบ analyzeChoppiness', 'error', 3000);
        return;
      }

      // Sync settings from popover if function exists
      if (typeof applyChoppySettingsLive === 'function') {
        applyChoppySettingsLive();
      }

      // 1. Run analyzeChoppiness with current parameters
      var smallCandles = allCandles.map(function(c) {
        return { epoch: c.time, open: c.open, high: c.high, low: c.low, close: c.close };
      });

      var curMode = choppyCombineMode || 'or';
      var curGroupSize = choppyGroupSize || 15;
      var curChopThreshold = choppyChopThreshold || 61.8;
      var curBodyRatio = choppyBodyRatioThreshold || 0.3;
      var curER = choppyErThreshold || 0.35;
      var curSwitches = choppyMinSwitches || 3;

      var evalResults;
      try {
        evalResults = window.analyzeChoppiness(smallCandles, {
          groupSize: curGroupSize,
          chopPeriod: 14,
          chopThreshold: curChopThreshold,
          bodyRatioThreshold: curBodyRatio,
          erThreshold: curER,
          minSwitches: curSwitches,
          combineMode: curMode,
          includeIncomplete: true
        });
      } catch (e) {
        showToast('❌ analyzeChoppiness error: ' + e.message, 'error', 4000);
        return;
      }

      // Build choppy epoch map
      var evalChopMap = {};
      if (Array.isArray(evalResults)) {
        evalResults.forEach(function(big) {
          var count = big.candleCount || curGroupSize;
          for (var k = 0; k < count; k++) {
            evalChopMap[big.epoch + (k * 60)] = big;
          }
        });
      }

      // 2. Classify trades
      var origWins = 0, origLosses = 0, origSkipped = 0;
      var origProfit = 0, origMaxLossCon = 0;
      var avoidedLosses = 0, avoidedWins = 0;
      var savedLossMoney = 0, lostWinMoney = 0;
      var filteredTrades = [];
      var remainingTrades = [];

      allTrades.forEach(function(t) {
        var ws = (t.WinStatus || '').toLowerCase();
        var isWin = ws === 'win';
        var isLoss = ws === 'loss';
        var profit = parseFloat(t.ThisProfit) || 0;

        if (isWin) origWins++;
        if (isLoss) origLosses++;
        if (!isWin && !isLoss) origSkipped++;

        origProfit += profit;
        if (isLoss) {
          var lc = t.displayLossCon !== undefined ? t.displayLossCon : (!isNaN(parseInt(t.lossCon, 10)) ? (parseInt(t.lossCon, 10) + 1) : 1);
          if (lc > origMaxLossCon) origMaxLossCon = lc;
        }

        // Check if this trade falls in choppy zone
        var tc = parseInt(t.timeCandle, 10) || 0;
        var pt = parseInt(t.purchaseTime, 10) || 0;
        var key = tc > 0 ? tc : pt;
        if (key <= 0) {
          remainingTrades.push(t);
          return;
        }
        var normTime = Math.floor(key / 60) * 60 + TZ_OFFSET_SEC;
        var targetCandleTime = normTime;

        var chop = evalChopMap[normTime];
        if (!chop && allCandles.length > 0) {
          var closest = allCandles.reduce(function(prev, curr) {
            return Math.abs(curr.time - normTime) < Math.abs(prev.time - normTime) ? curr : prev;
          });
          if (Math.abs(closest.time - normTime) <= 180) {
            chop = evalChopMap[closest.time];
            targetCandleTime = closest.time;
          }
        } else if (allCandles.length > 0) {
          var hasExact = allCandles.some(function(c) { return c.time === normTime; });
          if (!hasExact) {
            var closestCandle = allCandles.reduce(function(prev, curr) {
              return Math.abs(curr.time - normTime) < Math.abs(prev.time - normTime) ? curr : prev;
            });
            if (Math.abs(closestCandle.time - normTime) <= 180) {
              targetCandleTime = closestCandle.time;
            }
          }
        }
        var inChoppy = chop && chop.isChoppy;

        if (inChoppy && (isWin || isLoss)) {
          // This trade would be filtered by Karma
          filteredTrades.push({
            trade: t,
            chopData: chop,
            candleTime: targetCandleTime,
            originalStatus: t.WinStatus,
            impact: isLoss ? 'LOSS_SAVED' : 'WIN_LOST'
          });
          if (isLoss) {
            avoidedLosses++;
            savedLossMoney += Math.abs(profit);
          } else {
            avoidedWins++;
            lostWinMoney += profit;
          }
        } else {
          remainingTrades.push(t);
        }
      });

      // Update protected candles and paint them yellow immediately
      karmaProtectedCandleTimes = {};
      karmaProtectedTrades = {};
      filteredTrades.forEach(function(ft) {
        if (ft.impact === 'LOSS_SAVED' && ft.candleTime) {
          karmaProtectedCandleTimes[ft.candleTime] = true;
          karmaProtectedTrades[ft.trade.id] = true;
        }
      });
      renderCandlesWithKarmaColors();

      // 3. Re-simulate consecutive loss/win on remaining trades
      var newWins = 0, newLosses = 0, newProfit = 0;
      var newMaxLossCon = 0, newMaxWinCon = 0;
      var curLoss = 0, curWin = 0;

      remainingTrades.forEach(function(t) {
        var ws = (t.WinStatus || '').toLowerCase();
        var profit = parseFloat(t.ThisProfit) || 0;
        if (ws === 'win') {
          newWins++;
          newProfit += profit;
          curWin++;
          curLoss = 0;
          if (curWin > newMaxWinCon) newMaxWinCon = curWin;
        } else if (ws === 'loss') {
          newLosses++;
          newProfit += profit;
          curLoss++;
          curWin = 0;
          if (curLoss > newMaxLossCon) newMaxLossCon = curLoss;
        }
      });

      // 4. Calculate original max win con
      var origMaxWinCon = 0;
      var tmpW = 0;
      allTrades.forEach(function(t) {
        var ws = (t.WinStatus || '').toLowerCase();
        if (ws === 'win') {
          tmpW++;
          if (tmpW > origMaxWinCon) origMaxWinCon = tmpW;
        } else if (ws === 'loss') {
          tmpW = 0;
        }
      });

      var origWinRate = (origWins + origLosses) > 0 ? ((origWins / (origWins + origLosses)) * 100) : 0;
      var newWinRate = (newWins + newLosses) > 0 ? ((newWins / (newWins + newLosses)) * 100) : 0;
      var netSaved = savedLossMoney - lostWinMoney;

      // 5. Render Modal
      var paramsBar = document.getElementById('karma-eval-params-bar');
      var kpiGrid = document.getElementById('karma-eval-kpi-grid');
      var detailSection = document.getElementById('karma-eval-detail-section');

      // Params bar
      if (paramsBar) {
        paramsBar.innerHTML =
          '<span class="karma-eval-param-tag">Mode: <span class="param-val">' + curMode.toUpperCase() + '</span></span>' +
          '<span class="karma-eval-param-tag">Group: <span class="param-val">' + curGroupSize + 'M</span></span>' +
          '<span class="karma-eval-param-tag">CHOP ≥ <span class="param-val">' + curChopThreshold + '</span></span>' +
          '<span class="karma-eval-param-tag">ER ≤ <span class="param-val">' + curER + '</span></span>' +
          '<span class="karma-eval-param-tag">BodyRatio ≤ <span class="param-val">' + curBodyRatio + '</span></span>' +
          '<span class="karma-eval-param-tag">Switches ≥ <span class="param-val">' + curSwitches + '</span></span>' +
          '<span class="karma-eval-param-tag">Choppy Zones: <span class="param-val">' + evalResults.filter(function(r) { return r.isChoppy; }).length + '/' + evalResults.length + '</span></span>';
      }

      // KPI Grid
      if (kpiGrid) {
        var lossConClass = newMaxLossCon < origMaxLossCon ? 'highlight-good' : (newMaxLossCon === origMaxLossCon ? '' : 'highlight-bad');
        var lossConArrow = newMaxLossCon < origMaxLossCon ? '<span class="karma-kpi-arrow green">▼ ลดลง ' + (origMaxLossCon - newMaxLossCon) + '</span>' : (newMaxLossCon === origMaxLossCon ? '<span class="karma-kpi-arrow yellow">— เท่าเดิม</span>' : '<span class="karma-kpi-arrow red">▲ เพิ่มขึ้น ' + (newMaxLossCon - origMaxLossCon) + '</span>');

        var profitDiff = newProfit - origProfit;
        var profitClass = profitDiff >= 0 ? 'highlight-good' : 'highlight-bad';
        var profitArrow = profitDiff >= 0 ? '<span class="karma-kpi-arrow green">▲ +$' + profitDiff.toFixed(2) + '</span>' : '<span class="karma-kpi-arrow red">▼ -$' + Math.abs(profitDiff).toFixed(2) + '</span>';

        var wrDiff = newWinRate - origWinRate;
        var wrClass = wrDiff >= 0 ? 'highlight-good' : 'highlight-warn';
        var wrArrow = wrDiff >= 0 ? '<span class="karma-kpi-arrow green">▲ +' + wrDiff.toFixed(1) + '%</span>' : '<span class="karma-kpi-arrow red">▼ ' + wrDiff.toFixed(1) + '%</span>';

        kpiGrid.innerHTML =
          '<div class="karma-kpi-card ' + (avoidedLosses > 0 ? 'highlight-good' : '') + '">' +
            '<div class="karma-kpi-label">🛡️ Loss ที่ป้องกันได้</div>' +
            '<div class="karma-kpi-value" style="color:#34d399;">' + avoidedLosses + ' <span style="font-size:14px;">ไม้</span></div>' +
            '<div class="karma-kpi-sub">ประหยัด $' + savedLossMoney.toFixed(2) + '</div>' +
          '</div>' +
          '<div class="karma-kpi-card ' + (avoidedWins > 0 ? 'highlight-warn' : '') + '">' +
            '<div class="karma-kpi-label">⚠️ Win ที่พลาดไป</div>' +
            '<div class="karma-kpi-value" style="color:#fbbf24;">' + avoidedWins + ' <span style="font-size:14px;">ไม้</span></div>' +
            '<div class="karma-kpi-sub">สูญเสีย $' + lostWinMoney.toFixed(2) + '</div>' +
          '</div>' +
          '<div class="karma-kpi-card ' + lossConClass + '">' +
            '<div class="karma-kpi-label">📉 Max Loss Con</div>' +
            '<div class="karma-kpi-value">' + origMaxLossCon + ' → ' + newMaxLossCon + '</div>' +
            '<div class="karma-kpi-sub">' + lossConArrow + '</div>' +
          '</div>' +
          '<div class="karma-kpi-card ' + wrClass + '">' +
            '<div class="karma-kpi-label">🎯 Win Rate</div>' +
            '<div class="karma-kpi-value">' + origWinRate.toFixed(1) + '% → ' + newWinRate.toFixed(1) + '%</div>' +
            '<div class="karma-kpi-sub">' + wrArrow + '</div>' +
          '</div>' +
          '<div class="karma-kpi-card ' + profitClass + '">' +
            '<div class="karma-kpi-label">💰 Net Profit</div>' +
            '<div class="karma-kpi-value">$' + origProfit.toFixed(2) + ' → $' + newProfit.toFixed(2) + '</div>' +
            '<div class="karma-kpi-sub">' + profitArrow + '</div>' +
          '</div>' +
          '<div class="karma-kpi-card">' +
            '<div class="karma-kpi-label">📊 สัดส่วนไม้กรอง</div>' +
            '<div class="karma-kpi-value">' + filteredTrades.length + ' <span style="font-size:14px;">/ ' + (origWins + origLosses) + ' ไม้</span></div>' +
            '<div class="karma-kpi-sub">กรองออก ' + ((origWins + origLosses) > 0 ? ((filteredTrades.length / (origWins + origLosses)) * 100).toFixed(1) : 0) + '% ของไม้จริง</div>' +
          '</div>';
      }

      // Detail Table & Yellow Candle Banner
      if (detailSection) {
        var numProtected = Object.keys(karmaProtectedCandleTimes).length;
        var yellowBannerHtml = '';
        if (numProtected > 0) {
          yellowBannerHtml =
            '<div id="karma-yellow-banner" class="karma-yellow-candle-banner">' +
              '<div style="display:flex; align-items:center; gap:8px;">' +
                '<span style="font-size:18px;">🟡</span>' +
                '<span>ระบายสีแท่งเทียนที่ได้รับการป้องกัน (Loss Saved) <b style="color:#fff;">' + numProtected + ' แท่ง</b> เป็น <b style="color:#facc15;">สีเหลือง (Yellow)</b> บนกราฟแล้ว (คลิกที่แถวไม้ในตารางเพื่อซูมไปดูบนชาร์ต)</span>' +
              '</div>' +
              '<button type="button" class="btn-clear-karma-colors" onclick="window.clearKarmaCandleColors()">✕ คืนค่าสีเดิม</button>' +
            '</div>';
        }

        if (filteredTrades.length === 0) {
          detailSection.innerHTML =
            '<div class="karma-eval-section-title">📋 รายละเอียดไม้ที่ถูกกรอง</div>' +
            '<div class="karma-eval-empty">' +
              '<div style="font-size:36px; margin-bottom:10px;">🎉</div>' +
              '<div>ไม่มีไม้เทรดใดตกอยู่ในโซน Choppy ตาม Parameter ปัจจุบัน</div>' +
              '<div style="margin-top:6px; font-size:12px; color:#8b5cf6;">ลองปรับ Parameter ให้ Sensitivity สูงขึ้น เช่น ลด ER Threshold หรือเพิ่ม Body Ratio ในปุ่มเฟือง ⚙️ Choppy Settings</div>' +
            '</div>';
        } else {
          var tableHtml = yellowBannerHtml +
            '<div class="karma-eval-section-title">📋 รายละเอียดไม้ที่ถูกกรอง (' + filteredTrades.length + ' ไม้)</div>' +
            '<div class="karma-eval-table-wrap">' +
            '<table class="karma-eval-table">' +
            '<thead><tr>' +
              '<th>#</th><th>เวลา</th><th>Action</th><th>code_no</th><th>ผลเดิม</th><th>Stake ($)</th><th>PnL ($)</th><th>ผลกระทบ</th><th>ER</th><th>BodyRatio</th><th>Switches</th><th>CHOP</th>' +
            '</tr></thead><tbody>';

          filteredTrades.forEach(function(ft, idx) {
            var t = ft.trade;
            var cd = ft.chopData;
            var isLoss = ft.impact === 'LOSS_SAVED';
            var badgeClass = isLoss ? 'loss-saved' : 'win-lost';
            var badgeText = isLoss ? '🛡️ Loss Saved' : '⚠️ Win Lost';
            var pnl = parseFloat(t.ThisProfit) || 0;
            var pnlColor = pnl >= 0 ? '#34d399' : '#f87171';
            var timeDisplay = t.purchaseTimeDisplay || t.timeCandleDisplay || '-';
            var origBadge = (t.WinStatus || '').toLowerCase() === 'win'
              ? '<span style="color:#34d399; font-weight:700;">Win</span>'
              : '<span style="color:#f87171; font-weight:700;">Loss</span>';

            var rowClass = isLoss ? 'row-loss-saved' : '';
            var rowTitle = isLoss ? 'title="คลิกเพื่อซูมดูกราฟแท่งสีเหลืองนี้"' : '';
            var rowClick = isLoss && ft.candleTime ? 'onclick="window.zoomToKarmaCandle(' + ft.candleTime + ')"' : '';

            tableHtml += '<tr class="' + rowClass + '" ' + rowTitle + ' ' + rowClick + '>' +
              '<td>' + (idx + 1) + (isLoss ? ' 🟡' : '') + '</td>' +
              '<td style="font-family:monospace; font-size:11px;">' + timeDisplay + '</td>' +
              '<td><b>' + (t.thisAction || '-') + '</b></td>' +
              '<td style="color:#38bdf8;">' + (t.code_no || '-') + '</td>' +
              '<td>' + origBadge + '</td>' +
              '<td>$' + (parseFloat(t.MoneyTrade) || 0).toFixed(2) + '</td>' +
              '<td style="color:' + pnlColor + '; font-weight:700;">' + (pnl >= 0 ? '+' : '') + '$' + pnl.toFixed(2) + '</td>' +
              '<td><span class="badge-filtered ' + badgeClass + '">' + badgeText + '</span></td>' +
              '<td>' + (cd ? cd.efficiencyRatio : '-') + '</td>' +
              '<td>' + (cd ? cd.bodyRatio : '-') + '</td>' +
              '<td>' + (cd ? cd.switchCount : '-') + '</td>' +
              '<td>' + (cd && cd.chop !== null ? cd.chop : 'N/A') + '</td>' +
            '</tr>';
          });

          tableHtml += '</tbody></table></div>';

          // Summary sentence
          var summaryHtml = '<div style="margin-top:16px; padding:14px 18px; background:rgba(139,92,246,0.08); border:1px solid rgba(139,92,246,0.18); border-radius:10px; font-size:13px; color:#e2e8f0; line-height:1.7;">';
          summaryHtml += '<b style="color:#c4b5fd;">📝 สรุปผล:</b> ';
          if (netSaved > 0) {
            summaryHtml += 'การใช้ Karma Filter ช่วย<b style="color:#34d399;">ป้องกัน Loss ได้ ' + avoidedLosses + ' ไม้ (ประหยัด $' + savedLossMoney.toFixed(2) + ')</b>';
            if (avoidedWins > 0) {
              summaryHtml += ' แต่พลาด Win ไป ' + avoidedWins + ' ไม้ ($' + lostWinMoney.toFixed(2) + ')';
            }
            summaryHtml += ' <b style="color:#34d399;">ผลสุทธิดีขึ้น +$' + netSaved.toFixed(2) + '</b>';
          } else if (netSaved === 0 && filteredTrades.length === 0) {
            summaryHtml += 'ไม่มีไม้ใดถูกกรอง ตาม Parameter ปัจจุบัน';
          } else {
            summaryHtml += 'Karma Filter กรองออก ' + filteredTrades.length + ' ไม้ ';
            summaryHtml += '(ป้องกัน Loss ' + avoidedLosses + ' ไม้ = $' + savedLossMoney.toFixed(2) + ', พลาด Win ' + avoidedWins + ' ไม้ = $' + lostWinMoney.toFixed(2) + ') ';
            summaryHtml += '<b style="color:#fbbf24;">ผลสุทธิ ' + (netSaved >= 0 ? '+' : '-') + '$' + Math.abs(netSaved).toFixed(2) + '</b>';
          }
          if (newMaxLossCon < origMaxLossCon) {
            summaryHtml += '<br>Max Loss Con ลดลงจาก <b style="color:#f87171;">' + origMaxLossCon + '</b> เหลือ <b style="color:#34d399;">' + newMaxLossCon + '</b> ไม้';
          }
          summaryHtml += '</div>';
          detailSection.innerHTML = tableHtml + summaryHtml;
        }
      }

      // Show modal
      if (karmaEvalModal) karmaEvalModal.classList.add('active');
    }

    function closeKarmaEvalModal() {
      if (karmaEvalModal) karmaEvalModal.classList.remove('active');
    }

    if (btnEvalKarma) {
      btnEvalKarma.addEventListener('click', evaluateKarmaFilter);
    }
    if (btnCloseKarmaEval) {
      btnCloseKarmaEval.addEventListener('click', closeKarmaEvalModal);
    }
    if (karmaEvalModal) {
      karmaEvalModal.addEventListener('click', function(e) {
        if (e.target === karmaEvalModal) closeKarmaEvalModal();
      });
    }

    window.zoomToKarmaCandle = function(candleTime) {
      if (!chart || !candleTime) return;
      chart.timeScale().setVisibleRange({
        from: candleTime - 1200,
        to: candleTime + 1200
      });
      closeKarmaEvalModal();
      showToast('🔍 ซูมไปยังแท่งเทียนที่ป้องกันได้ (สีเหลือง 🟡)', 'info', 2500);
    };

    window.clearKarmaCandleColors = function() {
      karmaProtectedCandleTimes = {};
      karmaProtectedTrades = {};
      renderCandlesWithKarmaColors();
      showToast('คืนค่าสีแท่งเทียนเดิมเรียบร้อยแล้ว', 'info', 2000);
      var banner = document.getElementById('karma-yellow-banner');
      if (banner) banner.style.display = 'none';
    };

    window.evaluateKarmaFilter = evaluateKarmaFilter;
    window.closeKarmaEvalModal = closeKarmaEvalModal;

  })();
  </script>

  <!-- Table Detail Modal Component -->
  <div id="tableDetailModal" class="table-modal-overlay" onclick="if(event.target===this) window.closeTableDetailModal();">
    <div class="table-modal-card">
      <div class="table-modal-header">
        <div class="table-modal-title">
          <span id="modalTableIcon" style="font-size:20px;">📋</span>
          <span id="modalTableName" style="font-family:'Share Tech Mono', monospace; font-size:17px; color:#38bdf8;">tableHead</span>
          <span id="modalTableBadge" style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:6px; background:rgba(0,117,255,0.2); color:#93c5fd;">Metadata</span>
        </div>
        <button type="button" class="table-modal-close" onclick="window.closeTableDetailModal();" title="ปิดหน้าต่าง">✕</button>
      </div>

      <div class="table-modal-body">
        <div>
          <div class="table-modal-section-title">📌 บทบาท / หน้าที่ในหน้านี้ (Role):</div>
          <div id="modalTableRole" class="table-modal-desc">-</div>
        </div>

        <div>
          <div class="table-modal-section-title">🎯 พารามิเตอร์ปัจจุบันที่ใช้ฟิลเตอร์ (Active Query Context):</div>
          <div id="modalTableContext" style="font-family:'Share Tech Mono', monospace; font-size:12px; color:#cbd5e1; background:rgba(255,255,255,0.04); padding:8px 12px; border-radius:6px; border:1px solid rgba(255,255,255,0.06);">
            -
          </div>
        </div>

        <div>
          <div class="table-modal-section-title">📋 คอลัมน์ที่ถูกคิวรี (Queried Columns):</div>
          <div id="modalTableColumns" class="table-modal-columns-list">
            -
          </div>
        </div>

        <div>
          <div class="table-modal-section-title">💻 ตัวอย่าง SQL Query ที่ทำงานจริงในระบบ:</div>
          <div id="modalTableSql" class="table-modal-code">-</div>
        </div>
      </div>

      <div class="table-modal-footer">
        <button type="button" class="btn-modal-copy" id="btnModalCopySql" onclick="window.copyTableModalSql();">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
          <span id="btnModalCopyText">คัดลอก SQL</span>
        </button>
        <button type="button" class="btn-modal-close-action" onclick="window.closeTableDetailModal();">ปิด</button>
      </div>
    </div>
  </div>

  <!-- Auto-Load Confirmation & Progress Modal -->
  <div id="autoLoadModal" class="table-modal-overlay" onclick="if(event.target===this && !window.isAutoLoading) window.closeAutoLoadModal();">
    <div class="table-modal-card auto-load-modal-card">
      <div class="table-modal-header">
        <div class="table-modal-title">
          <span style="font-size:22px;">📡</span>
          <span id="autoLoadModalTitle" style="font-size:16px; font-weight:700; color:#fff;">โหลดข้อมูลรอบวันอัตโนมัติ</span>
          <span id="autoLoadDateBadge" class="auto-load-date-badge">2026-09-22</span>
        </div>
        <button type="button" class="table-modal-close" onclick="if(!window.isAutoLoading) window.closeAutoLoadModal();" title="ปิดหน้าต่าง">✕</button>
      </div>

      <!-- State 1: Confirmation Prompt -->
      <div id="autoLoadPromptBody" class="table-modal-body">
        <div class="auto-load-alert-box">
          <div class="auto-load-alert-icon">⚠️</div>
          <div>
            <div id="promptAlertTitle" style="font-weight:700; color:#fbbf24; font-size:14px; margin-bottom:4px;">ไม่พบข้อมูลในระบบสำหรับวันที่เลือก</div>
            <div id="promptAlertDesc" style="font-size:12px; color:#cbd5e1; line-height:1.5;">
              ตรวจพบว่าวันที่ <b id="promptDateText" style="color:#fff;">-</b> บนเซิร์ฟเวอร์ <b id="promptVpsText" style="color:#38bdf8;">-</b> ยังไม่มีข้อมูลในฐานข้อมูล (derivTradeHistory, candles, vpsTradeData)
            </div>
          </div>
        </div>

        <div style="font-size:13px; font-weight:600; color:#94a3b8; margin-top:4px;">
          คุณต้องการให้ระบบเริ่มขั้นตอนการโหลดและจัดเก็บข้อมูลอัตโนมัติตามลำดับนี้หรือไม่?
        </div>

        <div class="auto-load-step-preview-list">
          <div class="step-preview-row">
            <span class="step-preview-num">1</span>
            <div>
              <div class="step-preview-title">🔄 โหลด derivTradeHistory & สกัด Asset</div>
              <div class="step-preview-desc">ดึงประวัติการออกออเดอร์จาก Deriv Broker ของวันนั้น บันทึกลงตาราง derivTradeHistory และตรวจสอบรายการ Asset ที่มีออเดอร์</div>
            </div>
          </div>
          <div class="step-preview-row">
            <span class="step-preview-num">2</span>
            <div>
              <div class="step-preview-title">🕯️ โหลด candles จาก Deriv ตาม Asset ที่พบ</div>
              <div class="step-preview-desc">ดึงแท่งเทียน 1 นาที (OHLC) จาก Deriv WebSocket ตามสินทรัพย์ที่สกัดได้ บันทึกลงตาราง candles</div>
            </div>
          </div>
          <div class="step-preview-row">
            <span class="step-preview-num">3</span>
            <div>
              <div class="step-preview-title">📦 ดึง vpsTradeData & tradeHead จาก VPS API</div>
              <div class="step-preview-desc">ดาวน์โหลดไฟล์ ZIP จาก API ของ VPS (/api/export_trade_data) แตกไฟล์และบันทึกลง vpsTradeData & tradeHead</div>
            </div>
          </div>
        </div>
      </div>

      <!-- State 2: Live Stepper Progress -->
      <div id="autoLoadProgressBody" class="table-modal-body" style="display:none;">
        <div class="auto-load-stepper">
          <!-- Step 1 -->
          <div class="stepper-item" id="stepperStep1">
            <div class="stepper-icon-box">
              <span class="stepper-icon">1</span>
            </div>
            <div class="stepper-content">
              <div class="stepper-title">
                <span>1. derivTradeHistory & Assets</span>
                <span class="stepper-status-badge badge-pending" id="stepBadge1">รอดำเนินการ</span>
              </div>
              <div class="stepper-desc" id="stepDesc1">ดึงประวัติออเดอร์จาก Deriv และสกัดหารายชื่อ Assets</div>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="stepper-item" id="stepperStep2">
            <div class="stepper-icon-box">
              <span class="stepper-icon">2</span>
            </div>
            <div class="stepper-content">
              <div class="stepper-title">
                <span>2. Deriv Candles (1-Minute)</span>
                <span class="stepper-status-badge badge-pending" id="stepBadge2">รอดำเนินการ</span>
              </div>
              <div class="stepper-desc" id="stepDesc2">ดึงแท่งเทียน 1 นาทีตามสินทรัพย์ที่พบลงตาราง candles</div>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="stepper-item" id="stepperStep3">
            <div class="stepper-icon-box">
              <span class="stepper-icon">3</span>
            </div>
            <div class="stepper-content">
              <div class="stepper-title">
                <span>3. vpsTradeData & tradeHead</span>
                <span class="stepper-status-badge badge-pending" id="stepBadge3">รอดำเนินการ</span>
              </div>
              <div class="stepper-desc" id="stepDesc3">ดึงข้อมูลรอบเทรดจาก VPS API (/api/export_trade_data)</div>
            </div>
          </div>
        </div>

        <!-- Live Log Console -->
        <div class="auto-load-log-wrap">
          <div class="auto-load-log-title">
            <span>📝 บันทึกการทำงานสด (Live Logs):</span>
            <span id="logCounter" style="font-family:monospace; font-size:11px; color:#94a3b8;">0 events</span>
          </div>
          <div id="autoLoadLogConsole" class="auto-load-log-console"></div>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="table-modal-footer">
        <div id="autoLoadPromptFooter" style="display:flex; width:100%; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-modal-close-action" onclick="window.closeAutoLoadModal();">ยกเลิก</button>
          <button type="button" class="btn-modal-primary-action" id="btnConfirmAutoLoad" onclick="window.startAutoLoadPipeline();">
            <span>🚀 เริ่มโหลดข้อมูล (Start Load)</span>
          </button>
        </div>
        <div id="autoLoadProgressFooter" style="display:none; width:100%; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-modal-primary-action" id="btnFinishAutoLoad" style="display:none;" onclick="window.finishAutoLoadAndRender();">
            <span>📊 แสดงกราฟทันที (Open Chart)</span>
          </button>
          <button type="button" class="btn-modal-close-action" id="btnCancelAutoLoad" onclick="window.closeAutoLoadModal();">ปิด</button>
        </div>
      </div>
    </div>
  </div>

  <script>
  (function() {
    var TABLE_INFOS = {
      tradeHead: {
        name: 'tradeHead',
        icon: '📋',
        badge: 'Metadata รอบเทรด',
        role: 'เก็บข้อมูลหัวตาราง/ภาพรวมของรอบเทรด (Round Overview) เช่น เวลาเริ่ม-สิ้นสุด, ระยะเวลาที่เทรด, ยอด MaxLoss Con ของรอบ, ค่ากลยุทธ์ usestrategyCode เพื่อนำมาแสดงในส่วนหัวชาร์ตและ KPI',
        columns: ['serverCode', 'tradeRoundNo', 'assetCode', 'startTimeTrade', 'stopTimeTrade', 'durationTrade', 'MaxLossCon', 'usestrategyCode', 'created_at'],
        getSql: function(ctx) {
          return "SELECT th.*,\n" +
                 "       COALESCE(vm.vpsName, CONCAT('Server #', th.serverCode)) AS serverName,\n" +
                 "       COALESCE(vm.publicIP, '') AS publicIP\n" +
                 "FROM tradeHead th\n" +
                 "LEFT JOIN vpsMaster vm ON (th.serverCode = vm.vpsCode OR th.serverCode = vm.id)\n" +
                 "WHERE th.serverCode = " + ctx.serverCode + "\n" +
                 "  AND th.tradeRoundNo = " + ctx.tradeRoundNo + "\n" +
                 "  AND th.assetCode = '" + ctx.assetCode + "'\n" +
                 (ctx.date ? "  AND (DATE(th.startTimeTrade) = '" + ctx.date + "' OR DATE(th.created_at) = '" + ctx.date + "')\n" : "") +
                 "ORDER BY th.id DESC LIMIT 1;";
        }
      },
      vpsTradeData: {
        name: 'vpsTradeData',
        icon: '⚡',
        badge: 'รายการไม้เทรด',
        role: 'ตารางหลักที่เก็บข้อมูลบันทึกการเทรดทุกไม้ (Trade Executions) เช่น เวลาแท่งเทียน, เวลาซื้อ-ขาย, ทิศทาง Action (BUY/SELL), ราคาเปิด-ปิด (entrySpot, exitSpot), ผลแพ้-ชนะ (WinStatus), กำไร (ThisProfit) และค่าอินดิเคเตอร์ นำมาพล็อต Marker และเส้นสัญญาณบนกราฟ',
        columns: ['id', 'serverCode', 'tradeRoundNo', 'assetCode', 'tradeNo', 'subTradeNo', 'timeCandle', 'purchaseTime', 'sellTime', 'thisAction', 'thisColor', 'targetColor', 'codeStrategy', 'WinStatus', 'winCon', 'lossCon', 'MoneyTrade', 'ThisProfit', 'GrandBalance', 'entrySpot', 'exitSpot', 'diffSpot', 'tradeStrategy', 'isAnomaly'],
        getSql: function(ctx) {
          return "SELECT id, serverCode, tradeRoundNo, assetCode, tradeNo, subTradeNo,\n" +
                 "       timeCandle, purchaseTime, sellTime, thisAction, thisColor, targetColor,\n" +
                 "       codeStrategy, WinStatus, winCon, lossCon, MoneyTrade, ThisProfit, GrandBalance,\n" +
                 "       entrySpot, exitSpot, diffSpot, tradeStrategy, isAnomaly\n" +
                 "FROM vpsTradeData\n" +
                 "WHERE serverCode = " + ctx.serverCode + "\n" +
                 "  AND tradeRoundNo = " + ctx.tradeRoundNo + "\n" +
                 "  AND assetCode = '" + ctx.assetCode + "'\n" +
                 (ctx.date ? "  AND (DATE(FROM_UNIXTIME(COALESCE(NULLIF(timeCandle, 0), NULLIF(purchaseTime, 0)))) = '" + ctx.date + "'\n" +
                             "       OR (COALESCE(timeCandle, 0) = 0 AND DATE(created_at) = '" + ctx.date + "'))\n" : "") +
                 "ORDER BY timeCandle ASC, id ASC;";
        }
      },
      vpsMaster: {
        name: 'vpsMaster',
        icon: '🖥️',
        badge: 'VPS Server Info',
        role: 'เก็บข้อมูลรายชื่อเซิร์ฟเวอร์ VPS ทั้งหมด เช่น ชื่อเซิร์ฟเวอร์ (vpsName), ไอพี (publicIP), บัญชี Deriv (DerivAccountID) เพื่อนำมา Join แสดงชื่อเครื่องในดรอปดาวน์และส่วนหัวของชาร์ต',
        columns: ['id', 'vpsCode', 'vpsName', 'publicIP', 'privateIP', 'portno', 'url', 'DerivAccountID', 'remark'],
        getSql: function(ctx) {
          return "SELECT id, vpsCode, vpsName, publicIP, DerivAccountID\n" +
                 "FROM vpsMaster\n" +
                 "WHERE vpsCode = " + ctx.serverCode + " OR id = " + ctx.serverCode + ";";
        }
      },
      derivTradeHistory: {
        name: 'derivTradeHistory',
        icon: '🔄',
        badge: 'Broker Fallback',
        role: 'ตารางบันทึกประวัติการออกออเดอร์โดยตรงจาก Deriv Broker (ผ่าน WebSocket / Statement API) ทำหน้าที่เป็น Fallback สำรองอัตโนมัติ หากรอบนั้นยังไม่มีข้อมูลใน vpsTradeData ชาร์ตจะดึงจากตารางนี้มาแสดงแทนทันที',
        columns: ['id', 'serverCode', 'contract_id', 'symbol', 'action', 'barrier', 'buy_price', 'payout', 'profit', 'purchase_time', 'sell_time', 'status'],
        getSql: function(ctx) {
          return "SELECT *\n" +
                 "FROM derivTradeHistory\n" +
                 "WHERE serverCode = " + ctx.serverCode + "\n" +
                 "  AND symbol = '" + ctx.assetCode + "'\n" +
                 (ctx.date ? "  AND purchase_time BETWEEN UNIX_TIMESTAMP('" + ctx.date + " 00:00:00') AND UNIX_TIMESTAMP('" + ctx.date + " 23:59:59')\n" : "") +
                 "ORDER BY purchase_time ASC;";
        }
      },
      candles: {
        name: 'candles',
        icon: '🕯️',
        badge: 'แท่งเทียน OHLC / WS',
        role: 'ข้อมูลแท่งเทียนราคาย้อนหลังราย 1 นาที (Open, High, Low, Close, Epoch) สำหรับวาดแท่งเทียนบนกราฟ โดยระบบจะลองดึงผ่านตารางแคช candles ใน DB ก่อน หากไม่มีจะสลับไปดึงสดผ่าน Deriv WebSocket wss://ws.derivws.com/websockets/v3',
        columns: ['symbol', 'granularity', 'epoch', 'open', 'high', 'low', 'close'],
        getSql: function(ctx) {
          return "-- กรณีดึงจากตารางแคชใน MySQL Database:\n" +
                 "SELECT epoch, open, high, low, close\n" +
                 "FROM candles\n" +
                 "WHERE symbol = '" + ctx.assetCode + "'\n" +
                 (ctx.date ? "  AND epoch BETWEEN UNIX_TIMESTAMP('" + ctx.date + " 00:00:00') AND UNIX_TIMESTAMP('" + ctx.date + " 23:59:59')\n" : "") +
                 "ORDER BY epoch ASC;\n\n" +
                 "-- กรณีดึงสดผ่าน Deriv Public WebSocket (Default):\n" +
                 "-- wss://ws.derivws.com/websockets/v3 -> ticks_history (style: 'candles', granularity: 60)";
        }
      },
      page_config: {
        name: 'page_config',
        icon: '⚙️',
        badge: 'Layout & Settings',
        role: 'เก็บการตั้งค่าสถานะหน้าจอของผู้ใช้ (Panel Layout, Indicator Visibilities, Timeframes, Custom Overlay States) ที่ผู้ใช้กดบันทึกหรือปรับแต่งไว้ในหน้านี้ เพื่อให้เปิดกลับมาแล้วได้ค่าเดิม',
        columns: ['id', 'page_name', 'config_key', 'config_value', 'updated_at'],
        getSql: function(ctx) {
          return "SELECT config_value\n" +
                 "FROM page_config\n" +
                 "WHERE page_name = 'analysis_trade_chart'\n" +
                 "  AND config_key = 'settings_" + ctx.serverCode + "_" + ctx.assetCode + "';";
        }
      }
    };

    function getActiveContext() {
      var urlParams = new URLSearchParams(window.location.search);
      var sc = document.getElementById('filter-vps')?.value || urlParams.get('serverCode') || '1';
      var rd = document.getElementById('filter-round')?.value || urlParams.get('tradeRoundNo') || '10';
      var ast = document.getElementById('filter-asset')?.value || urlParams.get('assetCode') || '1HZ75V';
      var dt = document.getElementById('filter-date')?.value || document.getElementById('filter-date-picker')?.value || urlParams.get('date') || '2026-09-21';
      return { serverCode: sc, tradeRoundNo: rd, assetCode: ast, date: dt };
    }

    window.showTableDetailModal = function(tableKey) {
      var info = TABLE_INFOS[tableKey];
      if (!info) return;

      var ctx = getActiveContext();
      document.getElementById('modalTableIcon').textContent = info.icon;
      document.getElementById('modalTableName').textContent = info.name;
      document.getElementById('modalTableBadge').textContent = info.badge;
      document.getElementById('modalTableRole').textContent = info.role;

      document.getElementById('modalTableContext').innerHTML =
        'serverCode = <span style="color:#38bdf8;">' + ctx.serverCode + '</span> | ' +
        'tradeRoundNo = <span style="color:#34d399;">' + ctx.tradeRoundNo + '</span> | ' +
        'assetCode = <span style="color:#fbbf24;">\'' + ctx.assetCode + '\'</span> | ' +
        'date = <span style="color:#f472b6;">\'' + ctx.date + '\'</span>';

      var colContainer = document.getElementById('modalTableColumns');
      colContainer.innerHTML = '';
      info.columns.forEach(function(col) {
        var pill = document.createElement('span');
        pill.className = 'col-pill';
        pill.textContent = col;
        colContainer.appendChild(pill);
      });

      var sqlText = info.getSql(ctx);
      document.getElementById('modalTableSql').textContent = sqlText;

      document.getElementById('btnModalCopyText').textContent = 'คัดลอก SQL';
      document.getElementById('tableDetailModal').classList.add('active');
    };

    window.closeTableDetailModal = function() {
      var modal = document.getElementById('tableDetailModal');
      if (modal) modal.classList.remove('active');
    };

    window.copyTableModalSql = function() {
      var sqlText = document.getElementById('modalTableSql').textContent;
      if (!sqlText) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(sqlText).then(function() {
          document.getElementById('btnModalCopyText').textContent = '✓ คัดลอกแล้ว!';
          setTimeout(function() {
            var btnText = document.getElementById('btnModalCopyText');
            if (btnText) btnText.textContent = 'คัดลอก SQL';
          }, 2000);
        });
      } else {
        var ta = document.createElement('textarea');
        ta.value = sqlText;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        document.getElementById('btnModalCopyText').textContent = '✓ คัดลอกแล้ว!';
        setTimeout(function() {
          var btnText = document.getElementById('btnModalCopyText');
          if (btnText) btnText.textContent = 'คัดลอก SQL';
        }, 2000);
      }
    };

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        window.closeTableDetailModal();
        closeLossConModal();
      }
    });

    // ==========================================
    // Loss Con Summary Modal Logic
    // ==========================================
    var lossConModal = document.getElementById('loss-con-modal');
    var btnViewLossCon = document.getElementById('btn-view-loss-con');
    var btnCloseLossConModal = document.getElementById('btn-close-loss-con-modal');
    var lossConThead = document.getElementById('loss-con-thead');
    var lossConTbody = document.getElementById('loss-con-tbody');
    var lossConSummaryBar = document.getElementById('loss-con-summary-bar');
    var lossConSumVps = document.getElementById('loss-con-sum-vps');
    var lossConSumDate = document.getElementById('loss-con-sum-date');
    var lossConSumRounds = document.getElementById('loss-con-sum-rounds');
    var lossConSumProfit = document.getElementById('loss-con-sum-profit');
    var lossConModalTitleText = document.getElementById('loss-con-modal-title-text');

    function openLossConModal() {
      var vpsEl = document.getElementById('filter-vps');
      var dateEl = document.getElementById('filter-date');
      var datePickerEl = document.getElementById('filter-date-picker');
      var urlParams = new URLSearchParams(window.location.search);

      var curVps = (vpsEl && parseInt(vpsEl.value, 10)) ? parseInt(vpsEl.value, 10) : (parseInt(urlParams.get('serverCode'), 10) || 0);
      var curDate = (dateEl && dateEl.value) ? dateEl.value : ((datePickerEl && datePickerEl.value) ? datePickerEl.value : (urlParams.get('date') || ''));

      if (!curVps || !curDate) {
        if (typeof showToast === 'function') {
          showToast('กรุณาเลือก VPS Server และ Trading Date ให้เรียบร้อยก่อน', 'warning');
        } else {
          alert('กรุณาเลือก VPS Server และ Trading Date ให้เรียบร้อยก่อน');
        }
        return;
      }

      var vpsName = (vpsEl && vpsEl.options && vpsEl.options[vpsEl.selectedIndex] && vpsEl.options[vpsEl.selectedIndex].text && !vpsEl.options[vpsEl.selectedIndex].text.includes('กำลังโหลด')) 
                    ? vpsEl.options[vpsEl.selectedIndex].text 
                    : ('Server #' + curVps);

      if (lossConModalTitleText) {
        lossConModalTitleText.textContent = '📊 สรุป Loss Con & Win Con ตามรอบ (' + vpsName + ' | วันที่: ' + curDate + ')';
      }
      if (lossConModal) lossConModal.classList.add('active');
      if (lossConThead) lossConThead.innerHTML = '';
      if (lossConTbody) lossConTbody.innerHTML = '<tr><td colspan="10" style="padding: 40px; text-align: center; color: var(--text-tertiary);">กำลังโหลดข้อมูลสรุป Loss Con...</td></tr>';
      if (lossConSummaryBar) lossConSummaryBar.style.display = 'none';

      fetch('../php/api_analysis_trade_hist.php?action=get_loss_con_summary&serverCode=' + encodeURIComponent(curVps) + '&date=' + encodeURIComponent(curDate))
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (!data.success) {
            if (lossConTbody) lossConTbody.innerHTML = '<tr><td colspan="10" style="padding: 30px; text-align: center; color: #ef4444;">เกิดข้อผิดพลาด: ' + (data.error || 'ไม่สามารถโหลดข้อมูลได้') + '</td></tr>';
            return;
          }
          renderLossConTable(data, vpsName, curDate);
        })
        .catch(function(err) {
          if (lossConTbody) lossConTbody.innerHTML = '<tr><td colspan="10" style="padding: 30px; text-align: center; color: #ef4444;">โหลดข้อมูลล้มเหลว: ' + err.message + '</td></tr>';
        });
    }

    function closeLossConModal() {
      if (lossConModal) {
        lossConModal.classList.remove('active');
      }
    }

    function renderLossConTable(data, vpsName, curDate) {
      var assets = data.distinctAssets || [];
      var rounds = data.rounds || [];

      if (rounds.length === 0) {
        if (lossConThead) lossConThead.innerHTML = '';
        if (lossConTbody) lossConTbody.innerHTML = '<tr><td colspan="6" style="padding: 40px; text-align: center; color: var(--text-tertiary);">ไม่พบข้อมูลรอบการเทรดสำหรับ VPS และวันที่นี้</td></tr>';
        return;
      }

      // Summary Bar
      var totalProfit = 0;
      rounds.forEach(function(r) { totalProfit += (parseFloat(r.profit) || 0); });
      if (lossConSummaryBar) {
        lossConSummaryBar.style.display = 'flex';
        if (lossConSumVps) lossConSumVps.textContent = vpsName;
        if (lossConSumDate) lossConSumDate.textContent = curDate;
        if (lossConSumRounds) lossConSumRounds.textContent = rounds.length + ' รอบ';
        if (lossConSumProfit) {
          var pSign = totalProfit >= 0 ? '+$' : '-$';
          lossConSumProfit.textContent = pSign + Math.abs(totalProfit).toFixed(2);
          lossConSumProfit.style.color = totalProfit >= 0 ? '#10b981' : '#ef4444';
        }
      }

      // 1. Build THEAD
      // Row 1: roundno, starttime, stoptime, [Asset group colspans], profit ในรอบนี้
      var headRow1 = '<tr>';
      headRow1 += '  <th rowspan="2" style="width: 80px;">รอบ (Round)</th>';
      headRow1 += '  <th rowspan="2" style="min-width: 140px;">Start Time</th>';
      headRow1 += '  <th rowspan="2" style="min-width: 140px;">Stop Time</th>';
      assets.forEach(function(a) {
        headRow1 += '  <th colspan="3" class="asset-group-th">🎯 ' + a + '</th>';
      });
      headRow1 += '  <th rowspan="2" style="min-width: 110px;">Profit ในรอบนี้</th>';
      headRow1 += '</tr>';

      // Row 2: subcolumns for each asset: Max Loss Con, Max Win Con, Time of Max Loss Con
      var headRow2 = '<tr>';
      assets.forEach(function(a) {
        headRow2 += '  <th style="min-width: 90px; color: #f87171;">Max Loss Con</th>';
        headRow2 += '  <th style="min-width: 90px; color: #34d399;">Max Win Con</th>';
        headRow2 += '  <th style="min-width: 140px; color: #94a3b8;">Time of Max Loss Con</th>';
      });
      headRow2 += '</tr>';

      if (lossConThead) lossConThead.innerHTML = headRow1 + headRow2;

      // 2. Build TBODY
      var bodyHtml = '';
      rounds.forEach(function(r) {
        bodyHtml += '<tr>';
        bodyHtml += '  <td><span class="badge-round">Rnd #' + r.tradeRoundNo + '</span></td>';
        bodyHtml += '  <td style="font-family: monospace; font-size: 12px;">' + (r.startTimeTrade || '-') + '</td>';
        bodyHtml += '  <td style="font-family: monospace; font-size: 12px;">' + (r.stopTimeTrade || '-') + '</td>';

        assets.forEach(function(a) {
          var aData = (r.assets && r.assets[a]) ? r.assets[a] : null;
          if (aData) {
            var mL = parseInt(aData.maxLossCon, 10) || 0;
            var mW = parseInt(aData.maxWinCon, 10) || 0;
            var tL = aData.timeOfMaxLossCon || '-';

            // Highlight loss con >= 5 in yellow
            var lossStyle = (mL >= 5) 
              ? 'class="loss-con-highlight"' 
              : 'style="color: #cbd5e1; font-weight: 600;"';

            bodyHtml += '  <td><span ' + lossStyle + '>' + mL + '</span></td>';
            bodyHtml += '  <td style="color: #10b981; font-weight: 600;">' + mW + '</td>';
            bodyHtml += '  <td style="font-family: monospace; font-size: 12px; color: #94a3b8;">' + tL + '</td>';
          } else {
            bodyHtml += '  <td style="color: #64748b;">-</td>';
            bodyHtml += '  <td style="color: #64748b;">-</td>';
            bodyHtml += '  <td style="color: #64748b;">-</td>';
          }
        });

        var pnl = parseFloat(r.profit) || 0;
        var pnlColor = pnl >= 0 ? '#10b981' : '#ef4444';
        var pnlText = (pnl >= 0 ? '+$' : '-$') + Math.abs(pnl).toFixed(2);
        bodyHtml += '  <td style="color: ' + pnlColor + '; font-weight: 700;">' + pnlText + '</td>';
        bodyHtml += '</tr>';
      });

      if (lossConTbody) lossConTbody.innerHTML = bodyHtml;
    }

    if (btnViewLossCon) {
      btnViewLossCon.addEventListener('click', openLossConModal);
    }
    if (btnCloseLossConModal) {
      btnCloseLossConModal.addEventListener('click', closeLossConModal);
    }
    if (lossConModal) {
      lossConModal.addEventListener('click', function(e) {
        if (e.target === lossConModal) {
          closeLossConModal();
        }
      });
    }

  })();
  </script>
</body>
</html>
