<?php
/**
 * RTL Page Content - Right-to-Left Layout Demo
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-rtl" dir="rtl" style="text-align:right;">
  <!-- Stats Row RTL -->
  <div class="stats-grid">
    <div class="stat-card" style="flex-direction:row-reverse;">
      <div class="stat-info" style="text-align:right;">
        <h4>أموال اليوم</h4>
        <div class="stat-value" style="justify-content:flex-end;">
          <span class="stat-change up">+55%</span>
          <h2>$53,000</h2>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
      </div>
    </div>
    <div class="stat-card" style="flex-direction:row-reverse;">
      <div class="stat-info" style="text-align:right;">
        <h4>مستخدمو اليوم</h4>
        <div class="stat-value" style="justify-content:flex-end;">
          <span class="stat-change up">+3%</span>
          <h2>2,300</h2>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
      </div>
    </div>
    <div class="stat-card" style="flex-direction:row-reverse;">
      <div class="stat-info" style="text-align:right;">
        <h4>عملاء جدد</h4>
        <div class="stat-value" style="justify-content:flex-end;">
          <span class="stat-change down">-14%</span>
          <h2>+3,052</h2>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
      </div>
    </div>
    <div class="stat-card" style="flex-direction:row-reverse;">
      <div class="stat-info" style="text-align:right;">
        <h4>إجمالي المبيعات</h4>
        <div class="stat-value" style="justify-content:flex-end;">
          <span class="stat-change up">+8%</span>
          <h2>$173,000</h2>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M7 18h2V6H7v12zm4 4h2V2h-2v20zm-8-8h2v-4H3v4zm12 4h2V8h-2v10zm4-6v2h2v-2h-2z"/></svg>
      </div>
    </div>
  </div>

  <div class="grid-2">
    <div class="card welcome-card" style="min-height:220px;">
      <div class="welcome-content" style="text-align:right;">
        <p>مرحبًا بعودتك</p>
        <h2>مارك جونسون</h2>
        <p class="subtitle">سعيد لرؤيتك مرة أخرى.<br>اسألني أي شيء.</p>
        <a href="#" class="welcome-link">← انقر للتسجيل</a>
      </div>
      <div class="jellyfish-glow" style="left:40px;right:auto;"></div>
    </div>

    <div class="card">
      <div class="card-header" style="flex-direction:row-reverse;">
        <div style="text-align:right;">
          <h3 class="card-title">نظرة عامة على الطلبات</h3>
          <p class="card-subtitle">
            <span class="stat-change up" style="font-size:13px;">+30%</span> هذا الشهر
          </p>
        </div>
      </div>
      <div class="timeline">
        <div class="timeline-item" style="flex-direction:row-reverse;">
          <div class="timeline-content" style="text-align:right;">
            <h4>2400 دولار ، تغييرات التصميم</h4>
            <p>22 ديسمبر 7:20 مساءً</p>
          </div>
          <div class="timeline-dot-container">
            <div class="timeline-dot green"></div>
            <div class="timeline-line"></div>
          </div>
        </div>
        <div class="timeline-item" style="flex-direction:row-reverse;">
          <div class="timeline-content" style="text-align:right;">
            <h4>طلب جديد #1832412</h4>
            <p>21 ديسمبر 11:00 مساءً</p>
          </div>
          <div class="timeline-dot-container">
            <div class="timeline-dot red"></div>
            <div class="timeline-line"></div>
          </div>
        </div>
        <div class="timeline-item" style="flex-direction:row-reverse;">
          <div class="timeline-content" style="text-align:right;">
            <h4>مدفوعات الخادم لأبريل</h4>
            <p>21 ديسمبر 9:34 مساءً</p>
          </div>
          <div class="timeline-dot-container">
            <div class="timeline-dot blue"></div>
            <div class="timeline-line"></div>
          </div>
        </div>
        <div class="timeline-item" style="flex-direction:row-reverse;">
          <div class="timeline-content" style="text-align:right;">
            <h4>بطاقة جديدة مضافة للطلب #4395133</h4>
            <p>20 ديسمبر 2:20 صباحًا</p>
          </div>
          <div class="timeline-dot-container">
            <div class="timeline-dot orange"></div>
            <div class="timeline-line"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
