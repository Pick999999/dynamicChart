/**
 * Vision UI Dashboard - Pure JavaScript SPA Router & Charts
 * Handles page navigation without refresh via AJAX (fetch)
 * and initializes Chart.js charts for dashboard page.
 */

(function () {
  'use strict';

  // =====================
  //  SPA Router
  // =====================
  const contentArea = document.getElementById('content-area');
  const navLinks = document.querySelectorAll('.nav-link[data-page]');
  const breadcrumbPage = document.getElementById('breadcrumb-page');
  const topbarTitle = document.getElementById('topbar-title');

  let currentPage = '';
  const pageCache = {};

  /**
   * Navigate to a page by loading its PHP content via AJAX
   * @param {string} page - page name (e.g., 'dashboard', 'tables')
   */
  function navigateTo(page) {
    if (page === currentPage) return;
    currentPage = page;

    // Update active nav link
    navLinks.forEach(function (link) {
      if (link.dataset.page === page) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });

    // Update breadcrumb & title
    var pageTitles = {
      'all_load_data': 'All Load Data',
      'db_group': 'DB Group',
      'db_diagram': 'DB Diagram',
      'analysis_trade_hist': 'Analysis Trade Hist',
      'analysis_trade_chart': 'Analysis Trade Chart',
      'deriv_full_analysis_v2': 'Deriv Full Analysis V2',
      'calendar_trade': 'Calendar Trade',
      'calendar_trade_hist_v2': 'Calendar Trade Hist V2',
      'pktrend_action': 'pkTrend Action',
      'load_vps_trade_data': 'Load VPS Trade Data',
      'load_deriv_trade_history': 'Fetch Deriv Trade History',
      'load_full_analysis_data': 'Load FullAnalysis Data',
      'vps_master': 'VPS Master',
      'deriv_account': 'Deriv Account',
      'dashboard': 'Dashboard',
      'tables': 'Tables',
      'billing': 'Billing',
      'rtl': 'RTL',
      'profile': 'Profile',
      'signin': 'Sign In',
      'signup': 'Sign Up'
    };
    var pageTitle = pageTitles[page] || (page.charAt(0).toUpperCase() + page.slice(1).replace(/_/g, ' '));
    if (breadcrumbPage) breadcrumbPage.textContent = pageTitle;
    if (topbarTitle) topbarTitle.textContent = pageTitle;

    // Update URL hash
    history.pushState({ page: page }, '', '#' + page);

    // Check cache (bypass for calendar_trade, calendar_trade_hist_v2, db_diagram, db_group, all_load_data, analysis_trade_chart, deriv_full_analysis_v2 to allow live updates)
    if (page !== 'calendar_trade' && page !== 'calendar_trade_hist_v2' && page !== 'db_diagram' && page !== 'db_group' && page !== 'all_load_data' && page !== 'analysis_trade_chart' && page !== 'deriv_full_analysis_v2' && pageCache[page]) {
      renderPage(pageCache[page]);
      return;
    }

    // Show loader
    contentArea.innerHTML =
      '<div class="page-loader"><div class="spinner"></div></div>';

    // Fetch page content from PHP
    fetch('pages/' + page + '.php?_t=' + Date.now())
      .then(function (response) {
        if (!response.ok) throw new Error('Page not found');
        return response.text();
      })
      .then(function (html) {
        pageCache[page] = html;
        renderPage(html);
      })
      .catch(function (err) {
        contentArea.innerHTML =
          '<div class="page-content">' +
          '<div class="card" style="text-align:center;padding:60px 24px;">' +
          '<h2 style="margin-bottom:8px;">Page Not Found</h2>' +
          '<p style="color:var(--text-tertiary);">The page "' +
          page +
          '" could not be loaded.</p>' +
          '<p style="color:var(--text-tertiary);font-size:12px;margin-top:8px;">' +
          err.message +
          '</p>' +
          '</div></div>';
      });
  }

  /**
   * Render page HTML into content area and execute inline scripts
   */
  function renderPage(html) {
    contentArea.innerHTML = html;

    // Execute inline scripts
    var scripts = contentArea.querySelectorAll('script');
    scripts.forEach(function (oldScript) {
      var newScript = document.createElement('script');
      if (oldScript.src) {
        newScript.src = oldScript.src;
      } else {
        newScript.textContent = oldScript.textContent;
      }
      oldScript.parentNode.replaceChild(newScript, oldScript);
    });

    // Animate progress bars
    setTimeout(function () {
      var fills = contentArea.querySelectorAll('.progress-fill');
      fills.forEach(function (fill) {
        var w = fill.style.width;
        fill.style.width = '0';
        requestAnimationFrame(function () {
          fill.style.width = w;
        });
      });
    }, 100);
  }

  // Bind nav clicks
  navLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      var page = this.dataset.page;
      navigateTo(page);

      // Close mobile sidebar
      var sidebar = document.querySelector('.sidebar');
      var overlay = document.querySelector('.sidebar-overlay');
      if (sidebar) sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('active');
    });
  });

  // Handle back/forward
  window.addEventListener('popstate', function (e) {
    if (e.state && e.state.page) {
      navigateTo(e.state.page);
    }
  });

  // =====================
  //  Sidebar Slide-to-Left Toggle
  // =====================
  var sidebarCollapseBtn = document.getElementById('sidebar-collapse-btn');
  var sidebarToggleBtn = document.getElementById('sidebar-toggle');
  var sidebar = document.getElementById('sidebar');
  var sidebarOverlay = document.querySelector('.sidebar-overlay');

  function isDesktop() {
    return window.innerWidth > 992;
  }

  function collapseSidebar() {
    if (isDesktop()) {
      document.body.classList.add('sidebar-collapsed');
      localStorage.setItem('vision_sidebar_collapsed', '1');
    } else {
      if (sidebar) sidebar.classList.remove('open');
      if (sidebarOverlay) sidebarOverlay.classList.remove('active');
    }
    triggerResize();
  }

  function expandSidebar() {
    if (isDesktop()) {
      document.body.classList.remove('sidebar-collapsed');
      localStorage.setItem('vision_sidebar_collapsed', '0');
    } else {
      if (sidebar) sidebar.classList.add('open');
      if (sidebarOverlay) sidebarOverlay.classList.add('active');
    }
    triggerResize();
  }

  function toggleSidebar() {
    if (isDesktop()) {
      if (document.body.classList.contains('sidebar-collapsed')) {
        expandSidebar();
      } else {
        collapseSidebar();
      }
    } else {
      if (sidebar) sidebar.classList.toggle('open');
      if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
    }
  }

  function triggerResize() {
    setTimeout(function () {
      window.dispatchEvent(new Event('resize'));
    }, 360);
  }

  if (sidebarCollapseBtn) {
    sidebarCollapseBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      collapseSidebar();
    });
  }

  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      toggleSidebar();
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function () {
      if (sidebar) sidebar.classList.remove('open');
      sidebarOverlay.classList.remove('active');
    });
  }

  // Restore saved sidebar collapsed state on desktop
  if (isDesktop() && localStorage.getItem('vision_sidebar_collapsed') === '1') {
    document.body.classList.add('sidebar-collapsed');
  }

  // =====================
  //  Theme Management
  // =====================
  var THEMES = [
    {
      key: 'navy',
      name: 'Vision Navy',
      desc: 'Default Dark Blue & Cyan',
      gradient: 'linear-gradient(135deg, #0075FF 0%, #00D4FF 100%)',
      primary: '#060b26'
    },
    {
      key: 'cyber',
      name: 'Cyber Midnight',
      desc: 'OLED Dark & Neon Cyan',
      gradient: 'linear-gradient(135deg, #00f2fe 0%, #4facfe 100%)',
      primary: '#070b13'
    },
    {
      key: 'purple',
      name: 'Royal Amethyst',
      desc: 'Cosmic Purple & Magenta',
      gradient: 'linear-gradient(135deg, #7928CA 0%, #FF0080 100%)',
      primary: '#0b071c'
    },
    {
      key: 'emerald',
      name: 'Emerald Obsidian',
      desc: 'Forest Slate & Mint',
      gradient: 'linear-gradient(135deg, #059669 0%, #34D399 100%)',
      primary: '#03140e'
    },
    {
      key: 'sunset',
      name: 'Sunset Ember',
      desc: 'Charcoal & Amber Magma',
      gradient: 'linear-gradient(135deg, #FF416C 0%, #FF8C00 100%)',
      primary: '#140905'
    },
    {
      key: 'crimson',
      name: 'Crimson Ruby',
      desc: 'Velvet Dark & Ruby Rose',
      gradient: 'linear-gradient(135deg, #e52d27 0%, #ff4b72 100%)',
      primary: '#15050a'
    },
    {
      key: 'light',
      name: 'Crystal Light',
      desc: 'Frosted Glass Clean Light',
      gradient: 'linear-gradient(135deg, #0284c7 0%, #0075FF 100%)',
      primary: '#f1f5f9'
    }
  ];

  var themeToggleBtn = document.getElementById('theme-toggle-btn');
  var themeDropdown = document.getElementById('theme-dropdown');
  var themeListContainer = document.getElementById('theme-list');
  var themeCurrentBadge = document.getElementById('theme-current-badge');
  var settingsBtn = document.getElementById('settings-btn');

  function renderThemeList() {
    if (!themeListContainer) return;
    var currentTheme = localStorage.getItem('vision_theme') || 'navy';

    themeListContainer.innerHTML = THEMES.map(function (theme) {
      var isActive = theme.key === currentTheme;
      return (
        '<button class="theme-option' +
        (isActive ? ' active' : '') +
        '" data-theme-key="' +
        theme.key +
        '">' +
        '<div class="theme-option-info">' +
        '<div class="theme-preview-pill" style="background:' +
        theme.gradient +
        '">' +
        '<div class="theme-preview-dot" style="background:' +
        theme.primary +
        '"></div>' +
        '</div>' +
        '<div class="theme-option-text">' +
        '<span class="theme-option-name">' +
        theme.name +
        '</span>' +
        '<span class="theme-option-desc">' +
        theme.desc +
        '</span>' +
        '</div>' +
        '</div>' +
        '<div class="theme-option-check">' +
        '<svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>' +
        '</div>' +
        '</button>'
      );
    }).join('');

    themeListContainer.querySelectorAll('.theme-option').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var selectedKey = this.getAttribute('data-theme-key');
        applyTheme(selectedKey);
        if (themeDropdown) themeDropdown.classList.remove('show');
      });
    });
  }

  function applyTheme(key) {
    if (key === 'navy') {
      document.documentElement.removeAttribute('data-theme');
    } else {
      document.documentElement.setAttribute('data-theme', key);
    }
    localStorage.setItem('vision_theme', key);

    var foundTheme = THEMES.find(function (t) { return t.key === key; }) || THEMES[0];
    if (themeCurrentBadge) {
      themeCurrentBadge.textContent = foundTheme.name;
    }

    if (themeListContainer) {
      themeListContainer.querySelectorAll('.theme-option').forEach(function (btn) {
        if (btn.getAttribute('data-theme-key') === key) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
    }

    // Refresh charts if on dashboard
    if (window.initDashboardCharts && document.getElementById('salesChart')) {
      window.initDashboardCharts();
    }
  }

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (themeDropdown) themeDropdown.classList.toggle('show');
    });
  }

  if (settingsBtn) {
    settingsBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (themeDropdown) themeDropdown.classList.toggle('show');
    });
  }

  document.addEventListener('click', function (e) {
    if (
      themeDropdown &&
      !themeDropdown.contains(e.target) &&
      (!themeToggleBtn || !themeToggleBtn.contains(e.target)) &&
      (!settingsBtn || !settingsBtn.contains(e.target))
    ) {
      themeDropdown.classList.remove('show');
    }
  });

  // Apply saved theme immediately
  var savedTheme = localStorage.getItem('vision_theme') || 'navy';
  applyTheme(savedTheme);
  renderThemeList();

  // Initial page load from hash or default to dashboard
  var initialPage = window.location.hash.replace('#', '') || 'dashboard';
  navigateTo(initialPage);

  // =====================
  //  Chart.js Initialization
  // =====================
  var salesChartInstance = null;
  var activeUsersChartInstance = null;

  window.initDashboardCharts = function () {
    initSalesChart();
    initActiveUsersChart();
  };

  function initSalesChart() {
    var canvas = document.getElementById('salesChart');
    if (!canvas) return;

    if (salesChartInstance) {
      salesChartInstance.destroy();
      salesChartInstance = null;
    }

    var ctx = canvas.getContext('2d');
    var computed = getComputedStyle(document.documentElement);
    var accentBlue = computed.getPropertyValue('--accent-blue').trim() || '#0075FF';
    var accentCyan = computed.getPropertyValue('--accent-cyan').trim() || '#00D4FF';

    // Create gradient fills
    var gradientLine1 = ctx.createLinearGradient(0, 0, 0, 280);
    gradientLine1.addColorStop(0, accentBlue + '66');
    gradientLine1.addColorStop(1, 'rgba(0, 0, 0, 0.0)');

    var gradientLine2 = ctx.createLinearGradient(0, 0, 0, 280);
    gradientLine2.addColorStop(0, accentCyan + '33');
    gradientLine2.addColorStop(1, 'rgba(0, 0, 0, 0.0)');

    salesChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [
          'Jan',
          'Feb',
          'Mar',
          'Apr',
          'May',
          'Jun',
          'Jul',
          'Aug',
          'Sep',
          'Oct',
          'Nov',
          'Dec',
        ],
        datasets: [
          {
            label: 'Revenue',
            data: [500, 400, 300, 220, 500, 250, 400, 230, 500, 350, 250, 400],
            borderColor: '#0075FF',
            backgroundColor: gradientLine1,
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#0075FF',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
          },
          {
            label: 'Profit',
            data: [200, 230, 300, 350, 370, 420, 550, 350, 400, 500, 380, 300],
            borderColor: '#00D4FF',
            backgroundColor: gradientLine2,
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: '#00D4FF',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            backgroundColor: 'rgba(6, 11, 40, 0.95)',
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,0.7)',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            cornerRadius: 12,
            padding: 14,
            titleFont: { weight: '700', size: 13 },
            bodyFont: { size: 12 },
            displayColors: true,
            boxPadding: 6,
          },
        },
        scales: {
          x: {
            grid: {
              display: false,
              drawBorder: false,
            },
            ticks: {
              color: 'rgba(255,255,255,0.3)',
              font: { size: 11, weight: '500' },
            },
          },
          y: {
            grid: {
              color: 'rgba(255,255,255,0.04)',
              drawBorder: false,
            },
            ticks: {
              color: 'rgba(255,255,255,0.3)',
              font: { size: 11 },
              stepSize: 100,
            },
            min: 0,
            max: 600,
          },
        },
      },
    });
  }

  function initActiveUsersChart() {
    var canvas = document.getElementById('activeUsersChart');
    if (!canvas) return;

    if (activeUsersChartInstance) {
      activeUsersChartInstance.destroy();
      activeUsersChartInstance = null;
    }

    var ctx = canvas.getContext('2d');

    activeUsersChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: [
          '01',
          '02',
          '03',
          '04',
          '05',
          '06',
          '07',
          '08',
          '09',
          '10',
        ],
        datasets: [
          {
            label: 'Active Users',
            data: [480, 350, 220, 500, 430, 280, 550, 200, 380, 480],
            backgroundColor: function (context) {
              var chart = context.chart;
              var ctx2 = chart.ctx;
              var gradient = ctx2.createLinearGradient(0, 0, 0, 200);
              gradient.addColorStop(0, 'rgba(255, 255, 255, 0.95)');
              gradient.addColorStop(1, 'rgba(255, 255, 255, 0.3)');
              return gradient;
            },
            borderRadius: 6,
            borderSkipped: false,
            barThickness: 12,
            maxBarThickness: 14,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            backgroundColor: 'rgba(6, 11, 40, 0.95)',
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,0.7)',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            cornerRadius: 12,
            padding: 14,
          },
        },
        scales: {
          x: {
            grid: {
              display: false,
              drawBorder: false,
            },
            ticks: {
              display: false,
            },
          },
          y: {
            grid: {
              display: false,
              drawBorder: false,
            },
            ticks: {
              display: false,
            },
            min: 0,
            max: 600,
          },
        },
      },
    });
  }
})();
