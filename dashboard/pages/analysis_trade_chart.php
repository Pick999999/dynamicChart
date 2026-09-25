<?php
/**
 * Analysis Trade Chart - Dashboard Page Wrapper
 * Integrates analysis_trade_chart.php into Vision UI Dashboard SPA
 */
header('Content-Type: text/html; charset=utf-8');

$serverCode = isset($_GET['serverCode']) ? intval($_GET['serverCode']) : 0;
$tradeRoundNo = isset($_GET['tradeRoundNo']) ? intval($_GET['tradeRoundNo']) : 0;
$assetCode = isset($_GET['assetCode']) ? trim($_GET['assetCode']) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : '';

$queryParams = [];
if ($serverCode > 0) $queryParams['serverCode'] = $serverCode;
if ($tradeRoundNo > 0) $queryParams['tradeRoundNo'] = $tradeRoundNo;
if (!empty($assetCode)) $queryParams['assetCode'] = $assetCode;
if (!empty($date)) $queryParams['date'] = $date;

$queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
$standaloneUrl = 'analysis_trade_chart.php' . $queryString;
?>

<div class="page-content" id="page-analysis-trade-chart" style="padding: 0; display: flex; flex-direction: column; gap: 12px; height: calc(100vh - 110px); min-height: 800px;">
  <style>
    .atc-dash-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      padding: 12px 20px;
      background: var(--bg-card, rgba(6, 11, 40, 0.94));
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      border-radius: var(--radius-md, 14px);
      box-shadow: var(--shadow-card, 0 8px 32px 0 rgba(0, 0, 0, 0.37));
      backdrop-filter: blur(20px);
    }

    .atc-dash-title-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .atc-dash-icon {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 14px rgba(255, 126, 95, 0.35);
      color: #060b26;
    }

    .atc-dash-icon svg {
      width: 20px;
      height: 20px;
      fill: currentColor;
    }

    .atc-dash-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .atc-dash-sub {
      font-size: 11px;
      color: var(--text-tertiary, #94a3b8);
    }

    .atc-dash-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .atc-dash-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.15);
      background: rgba(255, 255, 255, 0.06);
      color: #fff;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .atc-dash-btn:hover {
      background: rgba(0, 117, 255, 0.25);
      border-color: var(--accent-primary, #0075ff);
      transform: translateY(-1px);
    }

    .atc-iframe-container {
      flex: 1;
      width: 100%;
      height: 100%;
      border-radius: var(--radius-md, 14px);
      overflow: hidden;
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      background: #060b26;
      box-shadow: var(--shadow-card, 0 8px 32px 0 rgba(0, 0, 0, 0.37));
    }

    .atc-iframe {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
    }
  </style>

  <!-- Top Action Bar -->
  <div class="atc-dash-header">
    <div class="atc-dash-title-group">
      <div class="atc-dash-icon">
        <svg viewBox="0 0 24 24"><path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/><path d="M4 19h16v2H4z"/></svg>
      </div>
      <div>
        <h2 class="atc-dash-title">Trade Candlestick Chart Analysis</h2>
        <p class="atc-dash-sub">วิเคราะห์กราฟแท่งเทียน 1 นาที, สัญญาณ Win/Loss Markers, Bollinger Bands, Flat/Choppy Zone และ Lab Mode</p>
      </div>
    </div>

    <div class="atc-dash-actions">
      <button type="button" class="atc-dash-btn" id="btn-reload-chart-iframe" title="รีเฟรชหน้ากราฟ">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
        รีเฟรช
      </button>
      <a href="<?php echo htmlspecialchars($standaloneUrl); ?>" target="_blank" rel="noopener noreferrer" class="atc-dash-btn" id="btn-open-full-window" title="เปิดกราฟในหน้าต่างใหม่แบบเต็มจอ">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
        เปิดเต็มจอ (Full Window ↗)
      </a>
    </div>
  </div>

  <!-- Embedded Chart Frame -->
  <div class="atc-iframe-container">
    <iframe id="atc-chart-frame" class="atc-iframe" src="<?php echo htmlspecialchars($standaloneUrl); ?>" allow="fullscreen"></iframe>
  </div>

  <script>
  (function () {
    'use strict';
    var reloadBtn = document.getElementById('btn-reload-chart-iframe');
    var iframe = document.getElementById('atc-chart-frame');
    var fullWindowBtn = document.getElementById('btn-open-full-window');

    if (reloadBtn && iframe) {
      reloadBtn.addEventListener('click', function () {
        try {
          iframe.contentWindow.location.reload();
        } catch (e) {
          iframe.src = iframe.src;
        }
      });
    }

    // Listen to iframe URL changes or sync if possible
    window.addEventListener('message', function (e) {
      if (e.data && e.data.type === 'chart_nav' && fullWindowBtn) {
        fullWindowBtn.href = e.data.url;
      }
    });
  })();
  </script>
</div>
