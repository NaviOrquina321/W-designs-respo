<?php
// admin.php - Standalone Admin Monitoring Portal (Direct Open Access)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apex Admin - TutorLink Management</title>
  <meta name="description" content="TutorLink Administrator Portal with Main Page Canvas Tabs for Management Modules.">
  <link rel="stylesheet" href="styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <div class="admin-layout">
    <!-- Dark Left Sidebar Navigation -->
    <aside class="admin-sidebar">
      <div class="sidebar-brand">
        <div class="brand-logo">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <div class="brand-text">
          <span class="brand-title">Apex</span>
          <span class="brand-subtitle">DASHBOARD</span>
        </div>
      </div>

      <div class="sidebar-scroll">
        <div class="nav-section-title">OVERVIEW</div>
        <ul class="sidebar-menu">
          <li class="menu-item active" data-admin-nav="dashboard">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('dashboard')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
              <span>Dashboard Overview</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="students">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('students')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <span>Students</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="matching">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('matching')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
              <span>Tutor Matching</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="schedule">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('schedule')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <span>Schedule</span>
            </a>
          </li>
        </ul>

        <div class="nav-section-title">COMMERCE & FINANCE</div>
        <ul class="sidebar-menu">
          <li class="menu-item" data-admin-nav="payments">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('payments')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              <span>Payments</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="reports">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('reports')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              <span>Reports</span>
            </a>
          </li>
        </ul>

        <div class="nav-section-title">SYSTEM</div>
        <ul class="sidebar-menu">
          <li class="menu-item" data-admin-nav="notifications">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('notifications')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span>Notifications</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="index.php" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              <span>Main Application</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- User Profile Box -->
      <div class="sidebar-footer">
        <div class="user-profile">
          <div class="avatar">DU</div>
          <div class="user-info">
            <span class="user-name">Demo User</span>
            <span class="user-role">Admin</span>
          </div>
          <a href="index.php" title="Return to App" class="logout-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </a>
        </div>
      </div>
    </aside>

    <!-- Main Content Canvas -->
    <div class="admin-main">
      <!-- Top Header Navigation -->
      <header class="admin-header">
        <div class="header-search">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" placeholder="Search anything..." class="search-input">
          <span class="search-shortcut">⌘K</span>
        </div>

        <div class="header-actions">
          <button class="btn btn-emerald" onclick="switchMainAdminTab('students')">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Students
          </button>

          <button class="btn btn-outline" id="generate-admin-report-btn" onclick="switchMainAdminTab('reports')">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Reports
          </button>

          <div class="header-icon-group">
            <button class="icon-btn" title="Theme Toggle">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <button class="icon-btn relative" title="Notifications" onclick="switchMainAdminTab('notifications')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span class="notification-badge"></span>
            </button>
          </div>

          <div class="user-pill-avatar">DU</div>
        </div>
      </header>

      <!-- Dashboard View Content Area -->
      <main class="content-container">

        <!-- Left Side Drawer Trigger Banner -->
        <div class="left-drawer-trigger-banner margin-bottom">
          <button class="btn btn-emerald" onclick="openAdminLeftDrawer()">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;margin-right:6px;"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            Open Management Modules Drawer (Left Side)
          </button>
        </div>

        <!-- Dashboard Overview (Default Page View) -->
        <div id="main-tab-dashboard-kpis" class="dashboard-kpi-wrapper">
          <div class="dashboard-title-area">
            <div>
              <h1 class="page-title">Dashboard Overview</h1>
              <p class="page-subtitle">Welcome back, Admin. Select any management module tab above to switch views.</p>
            </div>
          </div>

          <!-- Apex Style KPI Metric Cards -->
          <div class="apex-kpi-grid">
            <div class="apex-kpi-card" onclick="switchMainAdminTab('payments')">
              <div class="kpi-header">
                <span class="kpi-label">Total Revenue</span>
                <div class="kpi-icon-wrapper green-light">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="admin-stat-total-volume">₱0</div>
              <div class="kpi-trend positive">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <span>+12.5%</span> <span class="trend-sub">vs last month</span>
              </div>
              <div class="kpi-sparkline">
                <svg viewBox="0 0 300 40" preserveAspectRatio="none">
                  <path d="M0,30 Q40,10 80,25 T160,15 T240,28 T300,5" fill="none" stroke="#10b981" stroke-width="2"/>
                </svg>
              </div>
            </div>

            <div class="apex-kpi-card" onclick="switchMainAdminTab('students')">
              <div class="kpi-header">
                <span class="kpi-label">Active Registered Students</span>
                <div class="kpi-icon-wrapper cyan-light">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="admin-stat-total-students">0</div>
              <div class="kpi-trend positive">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <span>+8.2%</span> <span class="trend-sub">vs last month</span>
              </div>
              <div class="kpi-sparkline">
                <svg viewBox="0 0 300 40" preserveAspectRatio="none">
                  <path d="M0,25 Q50,35 100,20 T200,15 T300,8" fill="none" stroke="#06b6d4" stroke-width="2"/>
                </svg>
              </div>
            </div>

            <div class="apex-kpi-card" onclick="switchMainAdminTab('matching')">
              <div class="kpi-header">
                <span class="kpi-label">Verified Tutors</span>
                <div class="kpi-icon-wrapper blue-light">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="admin-stat-total-tutors">0</div>
              <div class="kpi-trend negative">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                <span>-3.1%</span> <span class="trend-sub">vs last month</span>
              </div>
              <div class="kpi-sparkline">
                <svg viewBox="0 0 300 40" preserveAspectRatio="none">
                  <path d="M0,15 Q60,5 120,25 T240,20 T300,32" fill="none" stroke="#3b82f6" stroke-width="2"/>
                </svg>
              </div>
            </div>

            <div class="apex-kpi-card" onclick="switchMainAdminTab('reports')">
              <div class="kpi-header">
                <span class="kpi-label">Platform Commission (10%)</span>
                <div class="kpi-icon-wrapper amber-light">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="admin-stat-platform-commission">₱0</div>
              <div class="kpi-trend positive">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <span>+24.7%</span> <span class="trend-sub">vs last month</span>
              </div>
              <div class="kpi-sparkline">
                <svg viewBox="0 0 300 40" preserveAspectRatio="none">
                  <path d="M0,35 Q70,28 140,22 T250,12 T300,5" fill="none" stroke="#f59e0b" stroke-width="2"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Apex Visual Analytics Charts Grid -->
          <div class="charts-grid">
            <!-- Main Chart -->
            <div class="chart-card main-chart-card">
              <div class="chart-header">
                <div>
                  <h3 class="chart-title">Overview</h3>
                  <p class="chart-subtitle">Monthly performance for the current year</p>
                </div>
                <div class="pill-segmented">
                  <button class="pill-btn active">Revenue</button>
                  <button class="pill-btn">Orders</button>
                  <button class="pill-btn">Profit</button>
                </div>
              </div>
              <div class="chart-body">
                <svg class="overview-svg-chart" viewBox="0 0 600 220" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                      <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                    </linearGradient>
                  </defs>
                  <line x1="0" y1="40" x2="600" y2="40" stroke="#f1f5f9" stroke-dasharray="4"/>
                  <line x1="0" y1="90" x2="600" y2="90" stroke="#f1f5f9" stroke-dasharray="4"/>
                  <line x1="0" y1="140" x2="600" y2="140" stroke="#f1f5f9" stroke-dasharray="4"/>
                  <line x1="0" y1="190" x2="600" y2="190" stroke="#f1f5f9" stroke-dasharray="4"/>
                  <path d="M0,180 Q60,160 120,130 T240,110 T360,70 T480,85 T600,40 L600,220 L0,220 Z" fill="url(#chartGradient)"/>
                  <path d="M0,180 Q60,160 120,130 T240,110 T360,70 T480,85 T600,40" fill="none" stroke="#10b981" stroke-width="3"/>
                </svg>
              </div>
            </div>

            <!-- Donut Traffic Sources Chart -->
            <div class="chart-card side-chart-card">
              <div class="chart-header">
                <div>
                  <h3 class="chart-title">Traffic Sources</h3>
                  <p class="chart-subtitle">Where your visitors come from</p>
                </div>
              </div>
              <div class="donut-chart-wrapper">
                <svg class="donut-svg" viewBox="0 0 160 160">
                  <circle cx="80" cy="80" r="60" fill="none" stroke="#e2e8f0" stroke-width="20"/>
                  <circle cx="80" cy="80" r="60" fill="none" stroke="#10b981" stroke-width="20" stroke-dasharray="131 245" stroke-dashoffset="0"/>
                  <circle cx="80" cy="80" r="60" fill="none" stroke="#06b6d4" stroke-width="20" stroke-dasharray="105 271" stroke-dashoffset="-131"/>
                  <circle cx="80" cy="80" r="60" fill="none" stroke="#3b82f6" stroke-width="20" stroke-dasharray="82 294" stroke-dashoffset="-236"/>
                  <circle cx="80" cy="80" r="60" fill="none" stroke="#8b5cf6" stroke-width="20" stroke-dasharray="56 320" stroke-dashoffset="-318"/>
                </svg>
                <div class="donut-center-text">
                  <span class="donut-number">284K</span>
                  <span class="donut-label">Visits</span>
                </div>
              </div>
              <div class="legend-list">
                <div class="legend-item"><span class="dot" style="background:#10b981;"></span> Direct <span class="pct">35%</span></div>
                <div class="legend-item"><span class="dot" style="background:#06b6d4;"></span> Organic <span class="pct">28%</span></div>
                <div class="legend-item"><span class="dot" style="background:#3b82f6;"></span> Referral <span class="pct">22%</span></div>
                <div class="legend-item"><span class="dot" style="background:#8b5cf6;"></span> Social <span class="pct">15%</span></div>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- SLIDE-OUT LEFT DRAWER FOR MANAGEMENT MODULES -->
  <div id="admin-left-drawer-backdrop" class="drawer-backdrop left-side-drawer hidden" onclick="if(event.target === this) closeAdminLeftDrawer();">
    <div class="drawer-panel drawer-panel-left drawer-large">
      <div class="drawer-header">
        <div class="drawer-header-brand">
          <div class="brand-logo" style="background:#10b981; width:32px; height:32px;">
            <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
          </div>
          <div>
            <div class="drawer-title">Admin Management Modules</div>
            <div class="drawer-subtitle">Apex Control Panel</div>
          </div>
        </div>
        <button class="drawer-close-btn" onclick="closeAdminLeftDrawer()">&times;</button>
      </div>

      <!-- TAB BAR INSIDE LEFT DRAWER (Exact image.png labels) -->
      <div class="drawer-nav-pills">
        <button class="drawer-pill-btn active" data-drawer-tab="students" onclick="switchDrawerAdminTab('students')">
          Manage Students
        </button>
        <button class="drawer-pill-btn" data-drawer-tab="matching" onclick="switchDrawerAdminTab('matching')">
          Manage Tutor Matching
        </button>
        <button class="drawer-pill-btn" data-drawer-tab="schedule" onclick="switchDrawerAdminTab('schedule')">
          Manage Schedule
        </button>
        <button class="drawer-pill-btn" data-drawer-tab="payments" onclick="switchDrawerAdminTab('payments')">
          Manage Payments
        </button>
        <button class="drawer-pill-btn" data-drawer-tab="notifications" onclick="switchDrawerAdminTab('notifications')">
          Manage Notifications
        </button>
        <button class="drawer-pill-btn" data-drawer-tab="reports" onclick="switchDrawerAdminTab('reports')">
          Manage Report
        </button>
      </div>

      <div class="drawer-body">
        <!-- MODULE 1: Manage Students -->
        <div id="drawer-tab-content-students" class="drawer-tab-view">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title" style="font-size:1.3rem;">Manage Students</h2>
              <p class="page-subtitle">View registered student accounts and validate registrations.</p>
            </div>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Registered Students List</h3>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Student ID</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Grade Level</th>
                  <th>Validation Status</th>
                  <th>Account Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="admin-students-table-body">
                <!-- Dynamic student rows -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- MODULE 2: Manage Tutor Matching -->
        <div id="drawer-tab-content-matching" class="drawer-tab-view hidden">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title" style="font-size:1.3rem;">Manage Tutor Matching</h2>
              <p class="page-subtitle">Review AI matching recommendations and approve tutoring sessions.</p>
            </div>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Tutor Match Requests</h3>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Match ID</th>
                  <th>Student</th>
                  <th>Matched Tutor</th>
                  <th>Subject</th>
                  <th>Compatibility</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="admin-matching-table-body">
                <!-- Dynamic match rows -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- MODULE 3: Manage Schedule -->
        <div id="drawer-tab-content-schedule" class="drawer-tab-view hidden">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title" style="font-size:1.3rem;">Manage Schedule</h2>
              <p class="page-subtitle">Create and modify calendar schedule slots for verified tutors.</p>
            </div>
          </div>

          <div class="card-table-wrapper margin-bottom">
            <div class="table-header-title">
              <h3>New Schedule Slot</h3>
            </div>
            <form id="admin-add-schedule-form" style="padding: 20px;">
              <div class="form-group margin-bottom">
                <label>Select Tutor</label>
                <select id="admin-sch-tutor-select" class="form-select"></select>
              </div>
              <div class="form-group margin-bottom">
                <label>Subject</label>
                <input type="text" id="admin-sch-subject" class="form-input" placeholder="e.g. Calculus II" required>
              </div>
              <div class="form-group margin-bottom">
                <label>Date & Time Slot</label>
                <input type="text" id="admin-sch-dateslot" class="form-input" placeholder="e.g. 2026-03-20 (02:00 PM - 04:00 PM)" required>
              </div>
              <button type="submit" class="btn btn-emerald">Create Schedule Slot</button>
            </form>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Active Schedules</h3>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Schedule ID</th>
                  <th>Tutor</th>
                  <th>Date & Slot</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="admin-schedule-table-body">
                <!-- Dynamic schedule rows -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- MODULE 4: Manage Payments -->
        <div id="drawer-tab-content-payments" class="drawer-tab-view hidden">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title" style="font-size:1.3rem;">Manage Payments & Fees</h2>
              <p class="page-subtitle">Monitor GCash transactions, payment statuses, and tutor payouts.</p>
            </div>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Payment Transactions Log</h3>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Payment ID</th>
                  <th>Payer (Student)</th>
                  <th>Method</th>
                  <th>GCash Ref #</th>
                  <th>Amount (₱)</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="admin-payments-table-body">
                <!-- Dynamic payment rows -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- MODULE 5: Manage Notifications -->
        <div id="drawer-tab-content-notifications" class="drawer-tab-view hidden">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title" style="font-size:1.3rem;">Manage Notifications</h2>
              <p class="page-subtitle">Send system broadcasts and view notification history log.</p>
            </div>
          </div>

          <div class="card-table-wrapper margin-bottom" style="padding: 20px;">
            <h3 class="margin-bottom">Send Broadcast Notification</h3>
            <div class="form-group margin-bottom">
              <label>Recipient Target</label>
              <select id="admin-notif-target" class="form-select">
                <option value="all">All Users (Students & Tutors)</option>
                <option value="students">All Students</option>
                <option value="tutors">All Tutors</option>
              </select>
            </div>
            <div class="form-group margin-bottom">
              <label>Message Content</label>
              <input type="text" id="admin-notif-message" placeholder="Type notification broadcast message..." class="form-input">
            </div>
            <button class="btn btn-emerald full-width margin-top" id="admin-send-notif-btn">Send Notification Broadcast</button>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Notification History Log</h3>
            </div>
            <div id="admin-notifications-log" class="cards-list" style="padding: 20px;">
              <!-- Broadcast log -->
            </div>
          </div>
        </div>

        <!-- MODULE 6: Manage Report -->
        <div id="drawer-tab-content-reports" class="drawer-tab-view hidden">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title" style="font-size:1.3rem;">Manage Reports</h2>
              <p class="page-subtitle">View completed tutoring sessions and print activity summaries.</p>
            </div>
            <button class="btn btn-emerald" onclick="window.print()">Print Activity Summary</button>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Completed Tutoring Sessions</h3>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Session ID</th>
                  <th>Student</th>
                  <th>Tutor</th>
                  <th>Subject</th>
                  <th>Date</th>
                  <th>Fee</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="admin-reports-completed-table-body">
                <!-- Dynamic completed report rows -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="drawer-footer">
        <div class="drawer-footer-info">
          <span class="status-indicator active"></span>
          <span>Admin Modules Live Sync</span>
        </div>
        <button class="btn btn-secondary btn-small" onclick="closeAdminLeftDrawer()">Close Drawer</button>
      </div>
    </div>
  </div>

  <div id="toast" class="toast hidden"></div>

  <script src="app.js"></script>
  <script>
    function openAdminLeftDrawer(tabName) {
      document.getElementById('admin-left-drawer-backdrop').classList.remove('hidden');
      if (tabName) {
        switchDrawerAdminTab(tabName);
      }
    }

    function closeAdminLeftDrawer() {
      document.getElementById('admin-left-drawer-backdrop').classList.add('hidden');
    }

    function switchDrawerAdminTab(tabName) {
      document.querySelectorAll('.drawer-tab-view').forEach(v => v.classList.add('hidden'));

      const activeView = document.getElementById(`drawer-tab-content-${tabName}`);
      if (activeView) activeView.classList.remove('hidden');

      document.querySelectorAll('.drawer-pill-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-drawer-tab') === tabName) {
          btn.classList.add('active');
        }
      });
    }

    function switchMainAdminTab(tabName) {
      if (tabName === 'dashboard') {
        document.getElementById('main-tab-dashboard-kpis').classList.remove('hidden');
        closeAdminLeftDrawer();
      } else {
        openAdminLeftDrawer(tabName);
      }

      // Sync active state on sidebar navigation
      document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-admin-nav') === tabName) {
          item.classList.add('active');
        }
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      if (typeof switchRole === 'function') {
        switchRole('admin', { id: 'ADMIN-001', name: 'System Admin', role: 'admin' });
      }
    });
  </script>
</body>
</html>
