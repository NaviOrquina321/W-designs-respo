<?php
// admin.php - Standalone Admin Monitoring Portal (Direct Open Access)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apex Admin - TutorLink Management</title>
  <meta name="description" content="TutorLink Administrator Portal for managing students, tutors, schedules, payments, notifications, and reports.">
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
            <a href="#dashboard" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="analytics">
            <a href="#analytics" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              <span>Analytics</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="students">
            <a href="#tab-students" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <span>Students</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="matching">
            <a href="#tab-matching" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
              <span>Tutor Matching</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="schedule">
            <a href="#tab-schedule" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <span>Calendar Schedule</span>
            </a>
          </li>
        </ul>

        <div class="nav-section-title">COMMERCE & FINANCE</div>
        <ul class="sidebar-menu">
          <li class="menu-item" data-admin-nav="payments">
            <a href="#tab-payments" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              <span>Payments & Fees</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="reports">
            <a href="#tab-reports" class="menu-link">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              <span>Reports</span>
            </a>
          </li>
        </ul>

        <div class="nav-section-title">SYSTEM</div>
        <ul class="sidebar-menu">
          <li class="menu-item" data-admin-nav="notifications">
            <a href="#tab-notifications" class="menu-link">
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
          <button class="btn btn-emerald" id="generate-admin-report-btn">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Order / Export
          </button>

          <div class="header-icon-group">
            <button class="icon-btn" title="Theme Toggle">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <button class="icon-btn" title="System Settings">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </button>
            <button class="icon-btn relative" title="Notifications">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span class="notification-badge"></span>
            </button>
          </div>

          <div class="user-pill-avatar">DU</div>
        </div>
      </header>

      <!-- Dashboard View Content Area -->
      <main class="content-container">
        <div class="dashboard-title-area">
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Welcome back, Admin. Here's what's happening with your business today.</p>
        </div>

        <!-- Apex Style KPI Metric Cards -->
        <div class="apex-kpi-grid">
          <div class="apex-kpi-card">
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

          <div class="apex-kpi-card">
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

          <div class="apex-kpi-card">
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

          <div class="apex-kpi-card">
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
                <!-- Horizontal Grid Lines -->
                <line x1="0" y1="40" x2="600" y2="40" stroke="#f1f5f9" stroke-dasharray="4"/>
                <line x1="0" y1="90" x2="600" y2="90" stroke="#f1f5f9" stroke-dasharray="4"/>
                <line x1="0" y1="140" x2="600" y2="140" stroke="#f1f5f9" stroke-dasharray="4"/>
                <line x1="0" y1="190" x2="600" y2="190" stroke="#f1f5f9" stroke-dasharray="4"/>

                <!-- Area Fill -->
                <path d="M0,180 Q60,160 120,130 T240,110 T360,70 T480,85 T600,40 L600,220 L0,220 Z" fill="url(#chartGradient)"/>
                <!-- Stroke Line -->
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

        <!-- System Management Modules Container -->
        <div class="management-section-wrapper margin-top-lg">
          <div class="admin-tab-bar">
            <button class="admin-tab-btn active" data-tab="tab-students">Manage Students</button>
            <button class="admin-tab-btn" data-tab="tab-matching">Manage Tutor Matching</button>
            <button class="admin-tab-btn" data-tab="tab-schedule">Manage Schedule</button>
            <button class="admin-tab-btn" data-tab="tab-payments">Manage Payments</button>
            <button class="admin-tab-btn" data-tab="tab-notifications">Manage Notifications</button>
            <button class="admin-tab-btn" data-tab="tab-reports">Manage Reports</button>
          </div>

          <!-- 1. Manage Students -->
          <div class="admin-tab-content active" id="tab-students">
            <div class="admin-subtab-bar">
              <button class="admin-subtab-btn active" data-subtab="subtab-student-view">View Student List</button>
              <button class="admin-subtab-btn" data-subtab="subtab-student-validate">Validate Student List</button>
            </div>

            <div class="admin-subtab-content active" id="subtab-student-view">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>View Student List</h3>
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

            <div class="admin-subtab-content" id="subtab-student-validate">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>Validate Student Registrations</h3>
                </div>
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Student ID</th>
                      <th>Full Name</th>
                      <th>Grade Level</th>
                      <th>Validation Status</th>
                      <th>Validate Action</th>
                    </tr>
                  </thead>
                  <tbody id="admin-students-validate-table-body">
                    <!-- Dynamic pending validation student rows -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 2. Manage Tutor Matching -->
          <div class="admin-tab-content" id="tab-matching">
            <div class="admin-subtab-bar">
              <button class="admin-subtab-btn active" data-subtab="subtab-match-review">Review Matching Results</button>
              <button class="admin-subtab-btn" data-subtab="subtab-match-approve">Approve Matching Sessions</button>
              <button class="admin-subtab-btn" data-subtab="subtab-match-cancel">Cancel Matching Sessions</button>
            </div>

            <div class="admin-subtab-content active" id="subtab-match-review">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>Review Matching Results</h3>
                </div>
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Match ID</th>
                      <th>Student</th>
                      <th>Matched Tutor</th>
                      <th>Subject</th>
                      <th>Compatibility Score</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="admin-matching-table-body">
                    <!-- Dynamic match rows -->
                  </tbody>
                </table>
              </div>
            </div>

            <div class="admin-subtab-content" id="subtab-match-approve">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>Approve Matching Sessions</h3>
                </div>
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Match ID</th>
                      <th>Student</th>
                      <th>Tutor</th>
                      <th>Subject</th>
                      <th>Status</th>
                      <th>Approve Action</th>
                    </tr>
                  </thead>
                  <tbody id="admin-matching-approve-table-body">
                    <!-- Dynamic pending match rows -->
                  </tbody>
                </table>
              </div>
            </div>

            <div class="admin-subtab-content" id="subtab-match-cancel">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>Cancel Matching Sessions</h3>
                </div>
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Match ID</th>
                      <th>Student</th>
                      <th>Tutor</th>
                      <th>Subject</th>
                      <th>Status</th>
                      <th>Cancel Action</th>
                    </tr>
                  </thead>
                  <tbody id="admin-matching-cancel-table-body">
                    <!-- Dynamic active match rows -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 3. Manage Schedule -->
          <div class="admin-tab-content" id="tab-schedule">
            <div class="admin-subtab-bar">
              <button class="admin-subtab-btn active" data-subtab="subtab-sch-new">New Calendar Schedule</button>
              <button class="admin-subtab-btn" data-subtab="subtab-sch-modify">Modify Sessions Schedule</button>
            </div>

            <div class="admin-subtab-content active" id="subtab-sch-new">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>New Calendar Schedule Slot</h3>
                </div>
                <form id="admin-add-schedule-form" class="profile-card-edit" style="padding: 20px;">
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
                  <button type="submit" class="btn btn-emerald margin-top">Create Schedule Slot</button>
                </form>
              </div>
            </div>

            <div class="admin-subtab-content" id="subtab-sch-modify">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>Modify Sessions Schedule</h3>
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
          </div>

          <!-- 4. Manage Payments -->
          <div class="admin-tab-content" id="tab-payments">
            <div class="admin-subtab-bar">
              <button class="admin-subtab-btn active" data-subtab="subtab-pay-new">New Payment Records</button>
              <button class="admin-subtab-btn" data-subtab="subtab-pay-confirm">Confirm Payment Status</button>
            </div>

            <div class="admin-subtab-content active" id="subtab-pay-new">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>New Payment Records</h3>
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

            <div class="admin-subtab-content" id="subtab-pay-confirm">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>Confirm Payment Status</h3>
                </div>
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Payment ID</th>
                      <th>Student</th>
                      <th>GCash Ref #</th>
                      <th>Amount (₱)</th>
                      <th>Status</th>
                      <th>Confirm Action</th>
                    </tr>
                  </thead>
                  <tbody id="admin-payments-confirm-table-body">
                    <!-- Dynamic pending payment rows -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 5. Manage Notifications -->
          <div class="admin-tab-content" id="tab-notifications">
            <div class="admin-subtab-bar">
              <button class="admin-subtab-btn active" data-subtab="subtab-notif-send">Send Notifications</button>
              <button class="admin-subtab-btn" data-subtab="subtab-notif-view">View Notifications</button>
            </div>

            <div class="admin-subtab-content active" id="subtab-notif-send">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>Send Broadcast Notifications</h3>
                </div>
                <div class="profile-card-edit" style="padding: 20px;">
                  <div class="form-group margin-bottom">
                    <label>Notification Recipient</label>
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
              </div>
            </div>

            <div class="admin-subtab-content" id="subtab-notif-view">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>View Notifications History</h3>
                </div>
                <div id="admin-notifications-log" class="cards-list" style="padding: 20px;">
                  <!-- Broadcast log -->
                </div>
              </div>
            </div>
          </div>

          <!-- 6. Manage Reports -->
          <div class="admin-tab-content" id="tab-reports">
            <div class="admin-subtab-bar">
              <button class="admin-subtab-btn active" data-subtab="subtab-rep-completed">View Completed Tutoring</button>
              <button class="admin-subtab-btn" data-subtab="subtab-rep-weekly">View Weekly Sessions</button>
              <button class="admin-subtab-btn" data-subtab="subtab-rep-monthly">View Monthly Sessions</button>
            </div>

            <div class="admin-subtab-content active" id="subtab-rep-completed">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>View Completed Tutoring Sessions</h3>
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

            <div class="admin-subtab-content" id="subtab-rep-weekly">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>View Weekly Sessions (Rolling 7 Days)</h3>
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
                  <tbody id="admin-reports-weekly-table-body">
                    <!-- Dynamic weekly report rows -->
                  </tbody>
                </table>
              </div>
            </div>

            <div class="admin-subtab-content" id="subtab-rep-monthly">
              <div class="card-table-wrapper">
                <div class="table-header-title">
                  <h3>View Monthly Sessions (Rolling 30 Days)</h3>
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
                  <tbody id="admin-reports-monthly-table-body">
                    <!-- Dynamic monthly report rows -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <div id="toast" class="toast hidden"></div>

  <script src="app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof switchRole === 'function') {
        switchRole('admin', { id: 'ADMIN-001', name: 'System Admin', role: 'admin' });
      }

      // Sidebar link handling for smooth module tab switching
      document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
        item.addEventListener('click', (e) => {
          document.querySelectorAll('.sidebar-menu .menu-item').forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          const navTarget = item.getAttribute('data-admin-nav');
          if (navTarget && navTarget !== 'dashboard' && navTarget !== 'analytics') {
            const tabBtn = document.querySelector(`.admin-tab-btn[data-tab="tab-${navTarget}"]`);
            if (tabBtn) tabBtn.click();
          }
        });
      });
    });
  </script>
</body>
</html>
