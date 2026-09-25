<?php
/**
 * Dashboard Page Content
 * Returns HTML fragment for the main dashboard view
 */

header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-dashboard">
  <!-- Stats Row -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-info">
        <h4>Today's Money</h4>
        <div class="stat-value">
          <h2>$53,000</h2>
          <span class="stat-change up">+55%</span>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm0-4h-2V7h2v8z"/></svg>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <h4>Today's Users</h4>
        <div class="stat-value">
          <h2>2,300</h2>
          <span class="stat-change up">+3%</span>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <h4>New Clients</h4>
        <div class="stat-value">
          <h2>+3,052</h2>
          <span class="stat-change down">-14%</span>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <h4>Total Sales</h4>
        <div class="stat-value">
          <h2>$173,000</h2>
          <span class="stat-change up">+8%</span>
        </div>
      </div>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24"><path d="M7 18h2V6H7v12zm4 4h2V2h-2v20zm-8-8h2v-4H3v4zm12 4h2V8h-2v10zm4-6v2h2v-2h-2z"/></svg>
      </div>
    </div>
  </div>

  <!-- Welcome + Satisfaction + Referral -->
  <div class="grid-1-1-1">
    <!-- Welcome Card -->
    <div class="card welcome-card">
      <div class="welcome-content">
        <p>Welcome back</p>
        <h2>Mark Johnson</h2>
        <p class="subtitle">Glad to see you again.<br>Ask me anything.</p>
        <a href="#" class="welcome-link">
          Tap to record →
        </a>
      </div>
      <div class="jellyfish-glow"></div>
      <div class="welcome-image">
        <div style="width:200px;height:200px;background:radial-gradient(ellipse at center, rgba(100,149,237,0.3) 0%, rgba(65,105,225,0.15) 30%, transparent 70%);border-radius:50%;position:relative;display:flex;align-items:center;justify-content:center;">
          <div style="width:80px;height:100px;background:radial-gradient(ellipse at center, rgba(147,112,219,0.6) 0%, rgba(100,149,237,0.4) 50%, transparent 80%);border-radius:50% 50% 30% 30%;filter:blur(2px);animation:jellyFloat 3s ease-in-out infinite alternate;"></div>
        </div>
      </div>
      <style>
        @keyframes jellyFloat {
          0% { transform: translateY(0) scale(1); }
          100% { transform: translateY(-10px) scale(1.05); }
        }
      </style>
    </div>

    <!-- Satisfaction Rate -->
    <div class="card satisfaction-card">
      <div class="card-header" style="width:100%;">
        <div>
          <h3 class="card-title">Satisfaction Rate</h3>
          <p class="card-subtitle">From all projects</p>
        </div>
      </div>
      <div class="progress-ring-container">
        <svg class="progress-ring" width="160" height="160" viewBox="0 0 160 160">
          <defs>
            <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" style="stop-color:#0075FF"/>
              <stop offset="100%" style="stop-color:#00D4FF"/>
            </linearGradient>
          </defs>
          <circle class="progress-ring-bg" cx="80" cy="80" r="70" fill="none" stroke-width="10"/>
          <circle class="progress-ring-fill" cx="80" cy="80" r="70" fill="none" stroke-width="10"
                  stroke-dasharray="439.8" stroke-dashoffset="22" data-target="22"/>
        </svg>
        <div class="progress-value">95<span>%</span></div>
      </div>
      <p class="progress-label">Based on likes</p>
      <div class="progress-legend">
        <div class="progress-legend-item">
          <div class="progress-legend-dot" style="background:var(--accent-blue);"></div>
          0%
        </div>
        <div class="progress-legend-item">
          <div class="progress-legend-dot" style="background:var(--accent-cyan);"></div>
          100%
        </div>
      </div>
    </div>

    <!-- Referral Tracking -->
    <div class="card referral-card">
      <div class="referral-stats">
        <div style="margin-bottom:8px;">
          <h3 class="card-title">Referral Tracking</h3>
        </div>
        <div class="referral-stat-item">
          <h4>Invited</h4>
          <div class="value">145 people</div>
        </div>
        <div class="referral-stat-item">
          <h4>Bonus</h4>
          <div class="value">1,465</div>
        </div>
      </div>
      <div class="referral-circle-container">
        <div class="score-circle">
          <div class="score-circle-inner">
            <span class="label">Safety</span>
            <span class="score">9.3</span>
            <span class="label">Total Score</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sales Overview + Active Users -->
  <div class="grid-wide-narrow">
    <div class="card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Sales overview</h3>
          <p class="card-subtitle">
            <span class="stat-change up" style="font-size:13px;">+5% more</span> in 2023
          </p>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Active Users</h3>
          <p class="card-subtitle">
            <span class="stat-change up" style="font-size:13px;">(+23%)</span> than last week
          </p>
        </div>
      </div>
      <div class="chart-container" style="height:200px;">
        <canvas id="activeUsersChart"></canvas>
      </div>
      <div class="active-users-metrics">
        <div class="metric-item">
          <div class="metric-icon">
            <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Users
          </div>
          <div class="metric-value">32,984</div>
        </div>
        <div class="metric-item">
          <div class="metric-icon">
            <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/></svg>
            Clicks
          </div>
          <div class="metric-value">2,42m</div>
        </div>
        <div class="metric-item">
          <div class="metric-icon">
            <svg viewBox="0 0 24 24"><path d="M7 18h2V6H7v12zm4 4h2V2h-2v20zm-8-8h2v-4H3v4zm12 4h2V8h-2v10zm4-6v2h2v-2h-2z"/></svg>
            Sales
          </div>
          <div class="metric-value">2,400$</div>
        </div>
        <div class="metric-item">
          <div class="metric-icon">
            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
            Items
          </div>
          <div class="metric-value">320</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Projects Table + Orders Overview -->
  <div class="grid-2">
    <div class="card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Projects</h3>
          <p class="card-subtitle">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="var(--accent-green)" style="vertical-align:middle;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            <span style="color:var(--text-secondary);"> 30 done</span> this month
          </p>
        </div>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Companies</th>
            <th>Members</th>
            <th>Budget</th>
            <th>Completion</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="table-project">
                <div class="table-project-icon" style="background:rgba(117,81,255,0.15);color:#7551FF;">⚡</div>
                <span class="table-project-name">Chakra Vision UI</span>
              </div>
            </td>
            <td>
              <div class="table-members">
                <div class="table-member-avatar" style="background:#0075FF;">M</div>
                <div class="table-member-avatar" style="background:#7551FF;">K</div>
                <div class="table-member-avatar" style="background:#01B574;">A</div>
                <div class="table-member-avatar" style="background:#FFB547;">R</div>
              </div>
            </td>
            <td>$14,000</td>
            <td>
              <div class="table-progress">
                <div class="progress-bar"><div class="progress-fill" style="width:60%"></div></div>
                <span class="progress-text">60%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="table-project">
                <div class="table-project-icon" style="background:rgba(0,117,255,0.15);color:#0075FF;">🎯</div>
                <span class="table-project-name">Add Progress Track</span>
              </div>
            </td>
            <td>
              <div class="table-members">
                <div class="table-member-avatar" style="background:#E31A1A;">S</div>
                <div class="table-member-avatar" style="background:#0075FF;">J</div>
              </div>
            </td>
            <td>$3,000</td>
            <td>
              <div class="table-progress">
                <div class="progress-bar"><div class="progress-fill" style="width:10%"></div></div>
                <span class="progress-text">10%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="table-project">
                <div class="table-project-icon" style="background:rgba(1,181,116,0.15);color:#01B574;">🛒</div>
                <span class="table-project-name">Fix Platform Errors</span>
              </div>
            </td>
            <td>
              <div class="table-members">
                <div class="table-member-avatar" style="background:#FFB547;">A</div>
                <div class="table-member-avatar" style="background:#0075FF;">D</div>
              </div>
            </td>
            <td>Not set</td>
            <td>
              <div class="table-progress">
                <div class="progress-bar"><div class="progress-fill" style="width:100%"></div></div>
                <span class="progress-text">100%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="table-project">
                <div class="table-project-icon" style="background:rgba(0,212,255,0.15);color:#00D4FF;">📱</div>
                <span class="table-project-name">Launch Mobile App</span>
              </div>
            </td>
            <td>
              <div class="table-members">
                <div class="table-member-avatar" style="background:#7551FF;">N</div>
                <div class="table-member-avatar" style="background:#01B574;">P</div>
                <div class="table-member-avatar" style="background:#0075FF;">T</div>
              </div>
            </td>
            <td>$20,500</td>
            <td>
              <div class="table-progress">
                <div class="progress-bar"><div class="progress-fill" style="width:100%"></div></div>
                <span class="progress-text">100%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="table-project">
                <div class="table-project-icon" style="background:rgba(255,181,71,0.15);color:#FFB547;">📊</div>
                <span class="table-project-name">Add New Pricing Page</span>
              </div>
            </td>
            <td>
              <div class="table-members">
                <div class="table-member-avatar" style="background:#E31A1A;">W</div>
                <div class="table-member-avatar" style="background:#0075FF;">E</div>
                <div class="table-member-avatar" style="background:#7551FF;">Q</div>
              </div>
            </td>
            <td>$500</td>
            <td>
              <div class="table-progress">
                <div class="progress-bar"><div class="progress-fill" style="width:25%"></div></div>
                <span class="progress-text">25%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="table-project">
                <div class="table-project-icon" style="background:rgba(227,26,26,0.15);color:#E31A1A;">🔧</div>
                <span class="table-project-name">Redesign Online Store</span>
              </div>
            </td>
            <td>
              <div class="table-members">
                <div class="table-member-avatar" style="background:#01B574;">B</div>
                <div class="table-member-avatar" style="background:#FFB547;">C</div>
              </div>
            </td>
            <td>$2,000</td>
            <td>
              <div class="table-progress">
                <div class="progress-bar"><div class="progress-fill" style="width:40%"></div></div>
                <span class="progress-text">40%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Orders overview</h3>
          <p class="card-subtitle">
            <span class="stat-change up" style="font-size:13px;">+30%</span> this month
          </p>
        </div>
      </div>
      <div class="timeline">
        <div class="timeline-item">
          <div class="timeline-dot-container">
            <div class="timeline-dot green"></div>
            <div class="timeline-line"></div>
          </div>
          <div class="timeline-content">
            <h4>$2400, Design changes</h4>
            <p>22 DEC 7:20 PM</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot-container">
            <div class="timeline-dot red"></div>
            <div class="timeline-line"></div>
          </div>
          <div class="timeline-content">
            <h4>New order #1832412</h4>
            <p>21 DEC 11:00 PM</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot-container">
            <div class="timeline-dot blue"></div>
            <div class="timeline-line"></div>
          </div>
          <div class="timeline-content">
            <h4>Server payments for April</h4>
            <p>21 DEC 9:34 PM</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot-container">
            <div class="timeline-dot orange"></div>
            <div class="timeline-line"></div>
          </div>
          <div class="timeline-content">
            <h4>New card added for order #4395133</h4>
            <p>20 DEC 2:20 AM</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot-container">
            <div class="timeline-dot purple"></div>
            <div class="timeline-line"></div>
          </div>
          <div class="timeline-content">
            <h4>Unlock packages for development</h4>
            <p>18 DEC 4:54 AM</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot-container">
            <div class="timeline-dot blue"></div>
            <div class="timeline-line"></div>
          </div>
          <div class="timeline-content">
            <h4>New order #9583120</h4>
            <p>17 DEC 12:30 PM</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Initialize charts after content loads
  if (typeof initDashboardCharts === 'function') {
    initDashboardCharts();
  }
</script>
