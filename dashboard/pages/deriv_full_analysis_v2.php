<?php
/**
 * Deriv Full Analysis V2 - Dashboard Page Wrapper
 * Integrates phpAllPredictAPI/deriv-full-analysis-v2.html into Vision UI Dashboard SPA
 */
header('Content-Type: text/html; charset=utf-8');

$standaloneUrl = '../phpAllPredictAPI/deriv-full-analysis-v2.html';
?>

<div class="page-content" id="page-deriv-full-analysis-v2" style="padding: 0; display: flex; flex-direction: column; gap: 12px; height: calc(100vh - 110px); min-height: 850px;">
  <style>
    .dfa-dash-header {
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

    .dfa-dash-title-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .dfa-dash-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4);
      color: #ffffff;
      flex-shrink: 0;
    }

    .dfa-dash-icon svg {
      width: 22px;
      height: 22px;
      fill: currentColor;
    }

    .dfa-dash-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .dfa-dash-badge {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: 20px;
      background: rgba(102, 126, 234, 0.25);
      border: 1px solid rgba(102, 126, 234, 0.5);
      color: #a5b4fc;
      letter-spacing: 0.5px;
    }

    .dfa-dash-sub {
      font-size: 11px;
      color: var(--text-tertiary, #94a3b8);
      margin-top: 2px;
    }

    .dfa-dash-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .dfa-dash-btn {
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

    .dfa-dash-btn:hover {
      background: rgba(102, 126, 234, 0.3);
      border-color: #667eea;
      transform: translateY(-1px);
    }

    .dfa-dash-btn.primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
    }

    .dfa-dash-btn.primary:hover {
      background: linear-gradient(135deg, #5a6fd6 0%, #683d94 100%);
      box-shadow: 0 6px 18px rgba(102, 126, 234, 0.5);
    }

    .dfa-iframe-container {
      flex: 1;
      width: 100%;
      height: 100%;
      border-radius: var(--radius-md, 14px);
      overflow: hidden;
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      background: #0a0c0f;
      box-shadow: var(--shadow-card, 0 8px 32px 0 rgba(0, 0, 0, 0.37));
    }

    .dfa-iframe {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
    }
  </style>

  <!-- Top Action Bar -->
  <div class="dfa-dash-header">
    <div class="dfa-dash-title-group">
      <div class="dfa-dash-icon">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
      </div>
      <div>
        <h2 class="dfa-dash-title">
          Deriv Full Analysis V2
          <span class="dfa-dash-badge">AI Predict</span>
        </h2>
        <p class="dfa-dash-sub">วิเคราะห์แท่งเทียน AI (Claude, ChatGPT, DeepSeek, Sort/NoSort) พร้อม Deriv WebSocket OTP & Account Selector</p>
      </div>
    </div>

    <div class="dfa-dash-actions">
      <button type="button" class="dfa-dash-btn" id="btn-reload-dfa-iframe" title="รีเฟรชหน้าวิเคราะห์">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
        รีเฟรช
      </button>
      <a href="<?php echo htmlspecialchars($standaloneUrl); ?>" target="_blank" rel="noopener noreferrer" class="dfa-dash-btn primary" title="เปิดหน้านี้ในแท็บใหม่แบบเต็มจอ">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
        เปิดเต็มจอ (Full Window ↗)
      </a>
    </div>
  </div>

  <!-- Embedded Iframe Frame -->
  <div class="dfa-iframe-container">
    <iframe id="dfa-analysis-frame" class="dfa-iframe" src="<?php echo htmlspecialchars($standaloneUrl); ?>" allow="fullscreen"></iframe>
  </div>

  <script>
  (function () {
    'use strict';
    var reloadBtn = document.getElementById('btn-reload-dfa-iframe');
    var iframe = document.getElementById('dfa-analysis-frame');

    if (reloadBtn && iframe) {
      reloadBtn.addEventListener('click', function () {
        try {
          iframe.contentWindow.location.reload();
        } catch (e) {
          iframe.src = iframe.src;
        }
      });
    }
  })();
  </script>
</div>
