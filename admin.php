<?php
require_once __DIR__ . '/api/config.php';   // starts the TUTORLINK_ADMIN session
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: admin-login.php');
    exit;
}
$adminName = $_SESSION['name'] ?? 'System Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TutorLink Admin Control Room</title>
  <link rel="icon" type="image/png" href="assets/favicon.png">
  <link rel="stylesheet" href="styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --sf-sidebar-bg: #0f172a;
      --sf-sidebar-text: #94a3b8;
      --sf-sidebar-active-bg: rgba(255, 255, 255, 0.08);
      --sf-sidebar-active-text: #ffffff;
      --sf-accent-gold: #c5a059;
      --sf-canvas-bg: #f1f5f9;
      --sf-card-bg: #ffffff;
      --sf-card-border: #e2e8f0;
      --sf-ink-primary: #0f172a;
      --sf-ink-muted: #64748b;
    }

    body {
      background: var(--sf-canvas-bg);
      color: var(--sf-ink-primary);
      font-family: 'Plus Jakarta Sans', sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
    }

    /* Sidebar Navigation */
    .admin-sidebar {
      width: 260px;
      background: var(--sf-sidebar-bg);
      color: var(--sf-sidebar-text);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      min-height: 100vh;
    }

    .sidebar-header {
      padding: 24px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .sidebar-logo-text {
      font-family: 'Cinzel', serif;
      font-weight: 700;
      font-size: 1.15rem;
      color: #ffffff;
      letter-spacing: 0.5px;
    }

    .sidebar-nav {
      padding: 16px 12px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .sidebar-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      color: var(--sf-sidebar-text);
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      font-size: 0.92rem;
      transition: all 0.2s ease;
    }

    .sidebar-item:hover {
      background: rgba(255, 255, 255, 0.04);
      color: #ffffff;
    }

    .sidebar-item.active {
      background: var(--sf-sidebar-active-bg);
      color: var(--sf-sidebar-active-text);
      font-weight: 600;
      border-left: 3px solid var(--sf-accent-gold);
    }

    .sidebar-item svg {
      width: 18px;
      height: 18px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Main Content Area */
    .admin-main {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .admin-topbar {
      height: 64px;
      background: #ffffff;
      border-bottom: 1px solid var(--sf-card-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
    }

    .admin-header-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--sf-ink-primary);
    }

    .admin-user-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .admin-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--sf-sidebar-bg);
      color: var(--sf-accent-gold);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.9rem;
    }

    .admin-content {
      padding: 32px;
      flex-grow: 1;
    }

    .admin-tab-view {
      display: none;
    }

    .admin-tab-view.active {
      display: block;
    }

    /* Dashboard Cards Grid */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 28px;
    }

    .kpi-card {
      background: #ffffff;
      border: 1px solid var(--sf-card-border);
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      cursor: pointer;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .kpi-card .kpi-label {
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--sf-ink-muted);
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .kpi-card .kpi-val {
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--sf-ink-primary);
    }

    .kpi-card .kpi-sub {
      font-size: 0.82rem;
      color: var(--sf-ink-muted);
      margin-top: 4px;
    }

    /* Panel & Table Layout */
    .card-panel {
      background: #ffffff;
      border: 1px solid var(--sf-card-border);
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      margin-bottom: 24px;
    }

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
      flex-wrap: wrap;
      gap: 12px;
    }

    .panel-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--sf-ink-primary);
      margin: 0;
    }

    .table-responsive {
      overflow-x: auto;
    }

    .admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.88rem;
      text-align: left;
    }

    .admin-table th {
      background: #f8fafc;
      padding: 12px 14px;
      font-size: 0.76rem;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--sf-ink-muted);
      border-bottom: 1px solid var(--sf-card-border);
      letter-spacing: 0.5px;
    }

    .admin-table td {
      padding: 14px;
      border-bottom: 1px solid #f1f5f9;
      color: var(--sf-ink-primary);
      vertical-align: middle;
    }

    .admin-table tr:last-child td {
      border-bottom: none;
    }

    .admin-table tr:hover td {
      background: #f8fafc;
    }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .badge-success { background: #dcfce7; color: #166534; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #e0f2fe; color: #075985; }

    /* Filters Bar & Controls */
    .filter-bar {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 16px;
      background: #f8fafc;
      padding: 12px 16px;
      border-radius: 8px;
      border: 1px solid var(--sf-card-border);
    }

    .filter-bar input, .filter-bar select {
      padding: 8px 12px;
      border: 1px solid var(--sf-card-border);
      border-radius: 6px;
      font-size: 0.88rem;
      outline: none;
      background: #ffffff;
    }

    /* Pagination Bar */
    .pagination-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 16px;
      margin-top: 12px;
      border-top: 1px solid #f1f5f9;
      font-size: 0.85rem;
      color: var(--sf-ink-muted);
    }

    .pagination-controls {
      display: flex;
      gap: 6px;
    }

    .btn-page {
      padding: 6px 12px;
      border: 1px solid var(--sf-card-border);
      background: #ffffff;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.82rem;
    }

    .btn-page.active {
      background: var(--sf-sidebar-bg);
      color: #ffffff;
      border-color: var(--sf-sidebar-bg);
    }

    .btn-page:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Toast Notifications */
    .admin-toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      padding: 12px 20px;
      border-radius: 8px;
      background: #0f172a;
      color: #ffffff;
      font-size: 0.9rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
      display: none;
      animation: fadeIn 0.2s ease;
    }

    /* Modal Overlay Rules */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(15, 23, 42, 0.6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(2px);
    }
    .modal-overlay.active {
      display: flex;
    }

    /* Print Stylesheet for Reports */
    @media print {
      body {
        background: #ffffff !important;
        color: #000000 !important;
      }
      .admin-sidebar, .admin-topbar, .filter-bar, .no-print, .btn, .pagination-bar {
        display: none !important;
      }
      .admin-main, .admin-content {
        padding: 0 !important;
        margin: 0 !important;
      }
      .card-panel {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
      }
      .admin-table th, .admin-table td {
        border: 1px solid #ccc !important;
      }
    }
  </style>
</head>
<body>

  <!-- SIDEBAR NAVIGATION -->
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <img src="assets/logo.png" alt="TutorLink" style="width: 28px; height: 28px;">
      <span class="sidebar-logo-text">TUTORLINK</span>
    </div>
    <nav class="sidebar-nav">
      <a href="#" class="sidebar-item active" data-tab="dashboard" onclick="switchTab('dashboard', event)">
        <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
        Dashboard
      </a>
      <a href="#" class="sidebar-item" data-tab="students" onclick="switchTab('students', event)">
        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Students
      </a>
      <a href="#" class="sidebar-item" data-tab="matching" onclick="switchTab('matching', event)">
        <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Tutor Matching
      </a>
      <a href="#" class="sidebar-item" data-tab="schedule" onclick="switchTab('schedule', event)">
        <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
        Schedule
      </a>
      <a href="#" class="sidebar-item" data-tab="payments" onclick="switchTab('payments', event)">
        <svg viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
        Payments
      </a>
      <a href="#" class="sidebar-item" data-tab="notifications" onclick="switchTab('notifications', event)">
        <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
        Notifications
      </a>
      <a href="#" class="sidebar-item" data-tab="reports" onclick="switchTab('reports', event)">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        Reports
      </a>
    </nav>
  </aside>

  <!-- MAIN CANVAS -->
  <main class="admin-main">
    <header class="admin-topbar">
      <h1 class="admin-header-title" id="tab-header-title">Dashboard</h1>
      <div class="admin-user-profile">
        <button class="btn btn-primary btn-small" onclick="openModal('modal-create-admin')" style="margin-right: 12px;">+ Create Admin Account</button>
        <div class="admin-avatar">SA</div>
        <span style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($adminName); ?></span>
        <button class="btn btn-secondary btn-small" onclick="handleLogout()" style="margin-left: 12px;">Log Out</button>
      </div>
    </header>

    <div class="admin-content">
      <div id="error-banner" class="alert alert-danger" style="display: none; margin-bottom: 20px;"></div>
      <div id="success-banner" class="alert alert-success" style="display: none; margin-bottom: 20px;"></div>

      <!-- 1. DASHBOARD -->
      <div id="tab-dashboard" class="admin-tab-view active">
        <div class="kpi-grid">
          <div class="kpi-card" onclick="switchTab('students')">
            <div class="kpi-label">Total Registered Students</div>
            <div class="kpi-val" id="kpi-students">0</div>
          </div>
          <div class="kpi-card" onclick="switchTab('matching')">
            <div class="kpi-label">Active Tutors</div>
            <div class="kpi-val" id="kpi-tutors">0</div>
          </div>
          <div class="kpi-card" onclick="switchTab('reports')">
            <div class="kpi-label">Tutoring Volume</div>
            <div class="kpi-val" id="kpi-volume">₱0.00</div>
          </div>
          <div class="kpi-card" onclick="switchTab('reports')">
            <div class="kpi-label">Platform Revenue (10%)</div>
            <div class="kpi-val" id="kpi-commission">₱0.00</div>
          </div>
        </div>

        <div class="kpi-grid">
          <div class="kpi-card" onclick="switchTab('schedule')">
            <div class="kpi-label">Upcoming Sessions</div>
            <div class="kpi-val" id="kpi-upcoming">0</div>
          </div>
          <div class="kpi-card" onclick="switchTab('reports')">
            <div class="kpi-label">Completed Sessions</div>
            <div class="kpi-val" id="kpi-completed">0</div>
          </div>
          <div class="kpi-card" onclick="switchTab('payments')">
            <div class="kpi-label">Pending Payments</div>
            <div class="kpi-val" id="kpi-pending-payments">0</div>
          </div>
          <div class="kpi-card" onclick="switchTab('matching')">
            <div class="kpi-label">Pending Matches</div>
            <div class="kpi-val" id="kpi-pending-matches">0</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
          <div class="card-panel">
            <h3 class="panel-title" style="margin-bottom: 16px;">Recent Tutoring Sessions</h3>
            <div class="table-responsive">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Tutor</th>
                    <th>Subject</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="dashboard-recent-sessions-body">
                  <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="card-panel">
            <h3 class="panel-title" style="margin-bottom: 16px;">Recent Admin Activity</h3>
            <div id="dashboard-audit-log-list" style="font-size: 0.85rem;">
              <p style="text-align:center; color: var(--sf-ink-muted);">Loading audit log...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. STUDENTS -->
      <div id="tab-students" class="admin-tab-view">
        <div class="card-panel">
          <div class="panel-header">
            <h3 class="panel-title">View Student List & Validate Student List</h3>
          </div>

          <div class="filter-bar">
            <input type="text" id="stu-search-input" placeholder="Search by name or email..." onkeyup="renderStudentsTable()">
            <select id="stu-filter-validated" onchange="renderStudentsTable()">
              <option value="">All Validation Status</option>
              <option value="1">Validated</option>
              <option value="0">Unvalidated</option>
            </select>
            <select id="stu-filter-active" onchange="renderStudentsTable()">
              <option value="">All Account Status</option>
              <option value="0">Active</option>
              <option value="1">Deactivated</option>
            </select>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Grade Level</th>
                  <th>Subjects Needed</th>
                  <th>Sessions Completed</th>
                  <th>Validated</th>
                  <th>Status</th>
                  <th>Joined Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="students-table-body">
                <tr><td colspan="10" style="text-align:center;">Loading students...</td></tr>
              </tbody>
            </table>
          </div>

          <div id="students-pagination" class="pagination-bar"></div>
        </div>
      </div>

      <!-- 3. TUTOR MATCHING -->
      <div id="tab-matching" class="admin-tab-view">
        <div class="card-panel">
          <div class="panel-header">
            <h3 class="panel-title">Review Matching Results, Approve Matching Sessions & Cancel Matching Sessions</h3>
          </div>

          <div class="filter-bar">
            <input type="text" id="mat-search-input" placeholder="Search student or tutor..." onkeyup="renderMatchesTable()">
            <select id="mat-status-filter" onchange="renderMatchesTable()">
              <option value="">All Statuses</option>
              <option value="Pending Review">Pending Review</option>
              <option value="Approved">Approved</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Match ID</th>
                  <th>Student</th>
                  <th>Tutor</th>
                  <th>Subject</th>
                  <th>Score</th>
                  <th>Reason</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="matches-table-body">
                <tr><td colspan="9" style="text-align:center;">Loading match requests...</td></tr>
              </tbody>
            </table>
          </div>

          <div id="matches-pagination" class="pagination-bar"></div>
        </div>

        <div class="card-panel" style="margin-top: 24px;">
          <div class="panel-header">
            <h3 class="panel-title">Registered Tutors List</h3>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Hourly Rate</th>
                  <th>Rating</th>
                  <th>Subjects</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tutors-table-body">
                <tr><td colspan="8" style="text-align:center;">Loading tutors...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 4. SCHEDULE -->
      <div id="tab-schedule" class="admin-tab-view">
        <div class="card-panel">
          <div class="panel-header">
            <h3 class="panel-title">New Calendar Schedule</h3>
            <button class="btn btn-primary btn-small" onclick="openNewCalendarScheduleModal()">+ Add Available Slot</button>
          </div>

          <div class="filter-bar">
            <select id="sch-filter-tutor" onchange="renderSchedulesTable()">
              <option value="">All Tutors</option>
            </select>
            <select id="sch-filter-status" onchange="renderSchedulesTable()">
              <option value="">All Statuses</option>
              <option value="Available">Available</option>
              <option value="Booked">Booked</option>
              <option value="Blocked">Blocked</option>
            </select>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tutor</th>
                  <th>Date & Time Slot</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Booked Student</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="schedules-table-body">
                <tr><td colspan="7" style="text-align:center;">Loading schedules...</td></tr>
              </tbody>
            </table>
          </div>

          <div id="schedules-pagination" class="pagination-bar"></div>
        </div>

        <div class="card-panel" style="margin-top: 24px;">
          <div class="panel-header">
            <h3 class="panel-title">Modify Sessions Schedule</h3>
          </div>

          <div class="filter-bar">
            <input type="text" id="ses-search-input" placeholder="Search student or tutor..." onkeyup="renderSessionsTable()">
            <select id="ses-status-filter" onchange="renderSessionsTable()">
              <option value="">All Session Statuses</option>
              <option value="Confirmed">Confirmed</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Tutor</th>
                  <th>Subject</th>
                  <th>Date & Time</th>
                  <th>Hourly Rate</th>
                  <th>Commission</th>
                  <th>Total Paid</th>
                  <th>GCash Ref</th>
                  <th>Status</th>
                  <th>Payout</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="sessions-table-body">
                <tr><td colspan="11" style="text-align:center;">Loading scheduled sessions...</td></tr>
              </tbody>
            </table>
          </div>

          <div id="sessions-pagination" class="pagination-bar"></div>
        </div>
      </div>

      <!-- 5. PAYMENTS -->
      <div id="tab-payments" class="admin-tab-view">
        <div class="card-panel">
          <div class="panel-header">
            <h3 class="panel-title">New Payment Records & Confirm Payment Status</h3>
          </div>

          <div class="filter-bar">
            <input type="text" id="pay-search-input" placeholder="Search reference no or student..." onkeyup="renderPaymentsTable()">
            <select id="pay-status-filter" onchange="renderPaymentsTable()">
              <option value="">All Payment Statuses</option>
              <option value="Pending">Pending</option>
              <option value="Confirmed">Confirmed</option>
            </select>
            <select id="pay-payout-filter" onchange="renderPaymentsTable()">
              <option value="">All Payout Statuses</option>
              <option value="Unpaid">Unpaid</option>
              <option value="Paid Out">Paid Out</option>
            </select>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Payment ID</th>
                  <th>Student</th>
                  <th>Tutor</th>
                  <th>Method</th>
                  <th>Reference No</th>
                  <th>Amount</th>
                  <th>Commission</th>
                  <th>Status</th>
                  <th>Payout Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="payments-table-body">
                <tr><td colspan="11" style="text-align:center;">Loading payment transactions...</td></tr>
              </tbody>
              <tfoot id="payments-table-foot" style="font-weight: 700; background: #f8fafc;">
              </tfoot>
            </table>
          </div>

          <div id="payments-pagination" class="pagination-bar"></div>
        </div>
      </div>

      <!-- 6. NOTIFICATIONS -->
      <div id="tab-notifications" class="admin-tab-view">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
          <div class="card-panel">
            <h3 class="panel-title" style="margin-bottom: 16px;">Send Notifications</h3>
            <form onsubmit="handleSendNotification(event)">
              <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 0.85rem; font-weight: 600;">Audience Target</label>
                <select id="notif-target-role" class="input-field" style="width: 100%; padding: 8px;" onchange="toggleSpecificUserPicker()">
                  <option value="all">All Users (Students + Tutors)</option>
                  <option value="students">All Students</option>
                  <option value="tutors">All Tutors</option>
                  <option value="student_specific">Specific Student</option>
                  <option value="tutor_specific">Specific Tutor</option>
                </select>
              </div>

              <div id="specific-user-picker-group" class="form-group" style="margin-bottom: 14px; display: none;">
                <label style="font-size: 0.85rem; font-weight: 600;">Select Recipient</label>
                <select id="notif-specific-user-id" class="input-field" style="width: 100%; padding: 8px;">
                  <option value="">Select recipient...</option>
                </select>
              </div>

              <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 0.85rem; font-weight: 600;">Title (Max 100 chars)</label>
                <input type="text" id="notif-title" class="input-field" maxlength="100" placeholder="Notification Title" style="width: 100%; padding: 8px;" required>
              </div>

              <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 0.85rem; font-weight: 600;">Message (Max 500 chars)</label>
                <textarea id="notif-message" class="input-field" maxlength="500" rows="4" placeholder="Type notification message here..." style="width: 100%; padding: 8px;" required></textarea>
              </div>

              <div class="form-group" style="margin-bottom: 18px;">
                <label style="font-size: 0.85rem; font-weight: 600;">Priority Level</label>
                <select id="notif-priority" class="input-field" style="width: 100%; padding: 8px;">
                  <option value="normal">Normal</option>
                  <option value="high">High Priority / Urgent</option>
                </select>
              </div>

              <button type="submit" class="btn btn-primary full-width">Send Notifications</button>
            </form>
          </div>

          <div class="card-panel">
            <div class="panel-header">
              <h3 class="panel-title">View Notifications</h3>
              <div style="display: flex; gap: 8px;">
                <button class="btn btn-secondary btn-small active" id="btn-notif-tab-sent" onclick="switchNotifSubTab('sent')">Sent Log</button>
                <button class="btn btn-secondary btn-small" id="btn-notif-tab-inbox" onclick="switchNotifSubTab('inbox')">Admin Inbox</button>
              </div>
            </div>
            <div id="notifications-log-list" style="max-height: 500px; overflow-y: auto;">
              <p style="text-align:center; color: var(--sf-ink-muted);">Loading notifications...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. REPORTS (NO CSV) -->
      <div id="tab-reports" class="admin-tab-view">
        <div class="card-panel">
          <div class="panel-header">
            <h3 class="panel-title">Reports Overview</h3>
            <button type="button" class="btn btn-primary btn-small no-print" onclick="window.print()">Print Report</button>
          </div>

          <div class="no-print" style="display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap;">
            <button type="button" class="btn btn-secondary btn-small" id="btn-rep-completed" onclick="applyReportPreset('View Completed Tutoring')">View Completed Tutoring</button>
            <button type="button" class="btn btn-secondary btn-small" id="btn-rep-weekly" onclick="applyReportPreset('View Weekly Sessions')">View Weekly Sessions</button>
            <button type="button" class="btn btn-secondary btn-small" id="btn-rep-monthly" onclick="applyReportPreset('View Monthly Sessions')">View Monthly Sessions</button>
          </div>

          <div class="filter-bar no-print">
            <input type="date" id="rep-date-from" onchange="loadReportsData()">
            <input type="date" id="rep-date-to" onchange="loadReportsData()">
            <select id="rep-status-select" onchange="loadReportsData()">
              <option value="">All Statuses</option>
              <option value="Completed">Completed</option>
              <option value="Confirmed">Confirmed</option>
              <option value="In Progress">In Progress</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            <select id="rep-tutor-select" onchange="loadReportsData()">
              <option value="">All Tutors</option>
            </select>
            <input type="text" id="rep-search-input" placeholder="Search student or subject..." onkeyup="loadReportsData()">
          </div>

          <div class="kpi-grid" style="margin-top: 16px;">
            <div class="kpi-card">
              <div class="kpi-label">Total Sessions</div>
              <div class="kpi-val" id="rep-kpi-total-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Completed Sessions</div>
              <div class="kpi-val" id="rep-kpi-completed-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Cancelled Sessions</div>
              <div class="kpi-val" id="rep-kpi-cancelled-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Total Paid</div>
              <div class="kpi-val" id="rep-kpi-total-paid">₱0.00</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Total Commission</div>
              <div class="kpi-val" id="rep-kpi-total-commission">₱0.00</div>
            </div>
          </div>

          <div class="table-responsive" style="margin-top: 20px;">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Session ID</th>
                  <th>Date & Time</th>
                  <th>Student</th>
                  <th>Tutor</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Hourly Rate</th>
                  <th>Commission</th>
                  <th>Total Paid</th>
                  <th>GCash Ref</th>
                  <th>Payout Status</th>
                </tr>
              </thead>
              <tbody id="reports-table-body">
                <tr><td colspan="11" style="text-align:center;">Loading report sessions...</td></tr>
              </tbody>
            </table>
          </div>

          <div id="reports-pagination" class="pagination-bar no-print"></div>
        </div>
      </div>

    </div>
  </main>

  <!-- MODALS -->

  <!-- Modal: Student Details Drawer -->
  <div id="modal-view-student" class="modal-overlay">
    <div class="modal-card card-panel" style="max-width: 550px;">
      <div class="modal-header">
        <h3 class="modal-title">Student Details</h3>
        <button class="close-btn" onclick="closeModal('modal-view-student')">&times;</button>
      </div>
      <div id="student-detail-content">Loading...</div>
    </div>
  </div>

  <!-- Modal: Edit Student -->
  <div id="modal-edit-student" class="modal-overlay">
    <div class="modal-card card-panel" style="max-width: 500px;">
      <div class="modal-header">
        <h3 class="modal-title">Validate Student List - Edit Student</h3>
        <button class="close-btn" onclick="closeModal('modal-edit-student')">&times;</button>
      </div>
      <form onsubmit="handleSaveEditStudent(event)">
        <input type="hidden" id="edit-stu-id">
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Student Name</label>
          <input type="text" id="edit-stu-name" class="input-field" required style="width:100%; padding:8px;">
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Grade Level</label>
          <input type="text" id="edit-stu-grade" class="input-field" required style="width:100%; padding:8px;">
        </div>
        <div class="form-group" style="margin-bottom: 16px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Subjects Needed</label>
          <input type="text" id="edit-stu-subjects" class="input-field" style="width:100%; padding:8px;">
        </div>
        <div style="display:flex; justify-content:flex-end; gap: 8px;">
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-edit-student')">Cancel</button>
          <button type="submit" class="btn btn-primary btn-small">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Reassign Tutor Match -->
  <div id="modal-reassign-match" class="modal-overlay">
    <div class="modal-card card-panel" style="max-width: 450px;">
      <div class="modal-header">
        <h3 class="modal-title">Reassign Tutor Match</h3>
        <button class="close-btn" onclick="closeModal('modal-reassign-match')">&times;</button>
      </div>
      <form onsubmit="handleConfirmReassignMatch(event)">
        <input type="hidden" id="reassign-match-id">
        <div class="form-group" style="margin-bottom: 16px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Select New Tutor</label>
          <select id="reassign-tutor-select" class="input-field" style="width: 100%; padding: 8px;" required>
          </select>
        </div>
        <div style="display:flex; justify-content:flex-end; gap: 8px;">
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-reassign-match')">Cancel</button>
          <button type="submit" class="btn btn-primary btn-small">Reassign Match</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: New Calendar Schedule -->
  <div id="modal-add-schedule" class="modal-overlay">
    <div class="modal-card card-panel" style="max-width: 500px;">
      <div class="modal-header">
        <h3 class="modal-title">New Calendar Schedule - Add Available Slot</h3>
        <button class="close-btn" onclick="closeModal('modal-add-schedule')">&times;</button>
      </div>
      <form onsubmit="handleSaveNewScheduleSlot(event)">
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Select Tutor</label>
          <select id="sch-add-tutor" class="input-field" style="width:100%; padding:8px;" required onchange="populateAddScheduleSubjects()">
          </select>
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Date</label>
          <input type="date" id="sch-add-date" class="input-field" required style="width:100%; padding:8px;">
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Time Slot (e.g. 09:00 AM - 10:00 AM)</label>
          <input type="text" id="sch-add-slot" class="input-field" placeholder="09:00 AM - 10:00 AM" required style="width:100%; padding:8px;">
        </div>
        <div class="form-group" style="margin-bottom: 16px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Subject</label>
          <select id="sch-add-subject" class="input-field" style="width:100%; padding:8px;" required>
          </select>
        </div>
        <div style="display:flex; justify-content:flex-end; gap: 8px;">
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-add-schedule')">Cancel</button>
          <button type="submit" class="btn btn-primary btn-small">Create Slot</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Reschedule Session -->
  <div id="modal-reschedule-session" class="modal-overlay">
    <div class="modal-card card-panel" style="max-width: 450px;">
      <div class="modal-header">
        <h3 class="modal-title">Modify Sessions Schedule - Reschedule</h3>
        <button class="close-btn" onclick="closeModal('modal-reschedule-session')">&times;</button>
      </div>
      <form onsubmit="handleConfirmReschedule(event)">
        <input type="hidden" id="resch-session-id">
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">New Date</label>
          <input type="date" id="resch-date" class="input-field" required style="width:100%; padding:8px;">
        </div>
        <div class="form-group" style="margin-bottom: 16px;">
          <label style="font-size: 0.85rem; font-weight: 600;">New Time Slot</label>
          <input type="text" id="resch-slot" class="input-field" placeholder="09:00 AM - 10:00 AM" required style="width:100%; padding:8px;">
        </div>
        <div style="display:flex; justify-content:flex-end; gap: 8px;">
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-reschedule-session')">Cancel</button>
          <button type="submit" class="btn btn-primary btn-small">Confirm Reschedule</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Create Admin Account -->
  <div id="modal-create-admin" class="modal-overlay">
    <div class="modal-card card-panel" style="max-width: 450px;">
      <div class="modal-header">
        <h3 class="modal-title">Create Admin Account</h3>
        <button class="close-btn" onclick="closeModal('modal-create-admin')">&times;</button>
      </div>
      <form onsubmit="handleCreateAdminAccount(event)">
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Full Name</label>
          <input type="text" id="create-admin-name" class="input-field" required style="width:100%; padding:8px;" placeholder="Admin Full Name">
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Email Address</label>
          <input type="email" id="create-admin-email" class="input-field" required style="width:100%; padding:8px;" placeholder="admin@domain.com">
        </div>
        <div class="form-group" style="margin-bottom: 16px;">
          <label style="font-size: 0.85rem; font-weight: 600;">Password (Min 8 Chars)</label>
          <input type="password" id="create-admin-pass" class="input-field" required minlength="8" style="width:100%; padding:8px;" placeholder="••••••••">
        </div>
        <div style="display:flex; justify-content:flex-end; gap: 8px;">
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-create-admin')">Cancel</button>
          <button type="submit" class="btn btn-primary btn-small">Create Account</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Element -->
  <div id="admin-toast-box" class="admin-toast"></div>

  <!-- ADMIN JAVASCRIPT -->
  <script>
    let currentTab = 'dashboard';
    let currentNotifSubTab = 'sent';
    let allStudentsCache = [];
    let allTutorsCache = [];
    let allMatchesCache = [];
    let allSchedulesCache = [];
    let allSessionsCache = [];
    let allPaymentsCache = [];
    let currentReportPage = 1;
    let currentReportPreset = 'completed';

    // Pagination state
    let pageState = {
      students: 1,
      matches: 1,
      schedules: 1,
      sessions: 1,
      payments: 1,
      reports: 1
    };

    function showToast(msg, isError = false) {
      const box = document.getElementById('admin-toast-box');
      if (!box) return;
      box.textContent = msg;
      box.style.background = isError ? '#991b1b' : '#0f172a';
      box.style.display = 'block';
      setTimeout(() => { box.style.display = 'none'; }, 3500);
    }

    function showError(msg) { showToast(msg, true); }
    function showSuccess(msg) { showToast(msg, false); }

    async function api(url, options = {}) {
      try {
        options.headers = options.headers || {};
        options.headers['X-TutorLink-Portal'] = 'admin';
        if (options.body && typeof options.body === 'object') {
          options.headers['Content-Type'] = 'application/json';
          options.body = JSON.stringify(options.body);
        }
        const response = await fetch(url, options);

        if (response.status === 401) {
          window.location.href = 'admin-login.php';
          return null;
        }

        const json = await response.json();
        if (!response.ok || json.status === 'error') {
          throw new Error(json.message || 'API Request failed');
        }
        return json;
      } catch (err) {
        showError(err.message);
        throw err;
      }
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function formatMoney(amount) {
      const num = parseFloat(amount) || 0;
      return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function openModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.add('active');
    }

    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.remove('active');
    }

    function switchTab(tabName, event) {
      if (event) event.preventDefault();
      currentTab = tabName;

      document.querySelectorAll('.sidebar-item').forEach(item => {
        item.classList.toggle('active', item.getAttribute('data-tab') === tabName);
      });

      document.querySelectorAll('.admin-tab-view').forEach(view => {
        view.classList.toggle('active', view.id === 'tab-' + tabName);
      });

      const tabTitles = {
        'dashboard': 'Dashboard',
        'students': 'Students',
        'matching': 'Tutor Matching',
        'schedule': 'Schedule',
        'payments': 'Payments',
        'notifications': 'Notifications',
        'reports': 'Reports'
      };

      const titleEl = document.getElementById('tab-header-title');
      if (titleEl) titleEl.textContent = tabTitles[tabName] || 'Admin Dashboard';

      loadTabData(tabName);
    }

    function loadTabData(tabName) {
      if (tabName === 'dashboard') loadDashboard();
      else if (tabName === 'students') loadStudents();
      else if (tabName === 'matching') { loadMatches(); loadTutors(); }
      else if (tabName === 'schedule') { loadSchedules(); loadSessions(); }
      else if (tabName === 'payments') loadPayments();
      else if (tabName === 'notifications') loadNotifications();
      else if (tabName === 'reports') loadReportsData();
    }

    async function handleLogout() {
      try { await api('api/logout.php', { method: 'POST' }); } catch (e) {}
      window.location.href = 'admin-login.php';
    }

    async function handleCreateAdminAccount(e) {
      e.preventDefault();
      const name = document.getElementById('create-admin-name').value.trim();
      const email = document.getElementById('create-admin-email').value.trim();
      const password = document.getElementById('create-admin-pass').value;

      try {
        const json = await api('api/register.php', {
          method: 'POST',
          body: { role: 'admin', name, email, password }
        });
        showSuccess(json.message || 'New admin account created.');
        closeModal('modal-create-admin');
        document.getElementById('create-admin-name').value = '';
        document.getElementById('create-admin-email').value = '';
        document.getElementById('create-admin-pass').value = '';
        loadDashboard();
      } catch (err) {}
    }

    // 1. DASHBOARD
    async function loadDashboard() {
      try {
        const json = await api('api/admin.php');
        if (json && json.data) {
          const d = json.data;
          document.getElementById('kpi-students').textContent = d.total_students || 0;
          document.getElementById('kpi-tutors').textContent = d.total_tutors || 0;
          document.getElementById('kpi-volume').textContent = formatMoney(d.total_volume);
          document.getElementById('kpi-commission').textContent = formatMoney(d.net_commission);

          document.getElementById('kpi-upcoming').textContent = d.upcoming_sessions || 0;
          document.getElementById('kpi-completed').textContent = d.completed_sessions || 0;
          document.getElementById('kpi-pending-payments').textContent = d.pending_payments || 0;
          document.getElementById('kpi-pending-matches').textContent = d.pending_matches || 0;

          // Render 5 Recent Sessions
          const recSes = d.recent_sessions || [];
          const sesBody = document.getElementById('dashboard-recent-sessions-body');
          if (recSes.length === 0) {
            sesBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No sessions logged yet.</td></tr>';
          } else {
            sesBody.innerHTML = recSes.map(s => `
              <tr>
                <td><code>${escapeHtml(s.id)}</code></td>
                <td><strong>${escapeHtml(s.studentName)}</strong></td>
                <td><strong>${escapeHtml(s.tutorName)}</strong></td>
                <td>${escapeHtml(s.subject)}</td>
                <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(s.status)}</span></td>
              </tr>
            `).join('');
          }

          // Render Recent Audit Log
          const auditList = d.recent_audit_logs || [];
          const auditContainer = document.getElementById('dashboard-audit-log-list');
          if (auditList.length === 0) {
            auditContainer.innerHTML = '<p style="text-align:center; color: var(--sf-ink-muted);">No administrative actions logged yet.</p>';
          } else {
            auditContainer.innerHTML = auditList.map(a => `
              <div style="border-bottom: 1px solid #f1f5f9; padding: 8px 0;">
                <div><strong>${escapeHtml(a.action)}</strong> on <code>${escapeHtml(a.target)}</code></div>
                <div style="font-size: 0.76rem; color: var(--sf-ink-muted);">${escapeHtml(a.createdAt || a.time || '')}</div>
              </div>
            `).join('');
          }
        }
      } catch (e) {}
    }

    // 2. STUDENTS (View Student List & Validate Student List)
    async function loadStudents() {
      const tbody = document.getElementById('students-table-body');
      tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Loading students...</td></tr>';
      try {
        const json = await api('api/students.php');
        allStudentsCache = json.data || [];
        renderStudentsTable();
      } catch (e) {}
    }

    function renderStudentsTable() {
      const tbody = document.getElementById('students-table-body');
      const term = (document.getElementById('stu-search-input')?.value || '').toLowerCase();
      const valFilter = document.getElementById('stu-filter-validated')?.value || '';
      const actFilter = document.getElementById('stu-filter-active')?.value || '';

      let filtered = allStudentsCache.filter(s => {
        const matchTerm = (s.name || '').toLowerCase().includes(term) || (s.email || '').toLowerCase().includes(term);
        const matchVal = valFilter !== '' ? String(s.validated ? 1 : 0) === valFilter : true;
        const matchAct = actFilter !== '' ? String(s.deactivated ? 1 : 0) === actFilter : true;
        return matchTerm && matchVal && matchAct;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">No matching students found.</td></tr>';
        document.getElementById('students-pagination').innerHTML = '';
        return;
      }

      const paged = paginateArray(filtered, pageState.students, 25);
      renderPaginationControls('students-pagination', paged, (p) => { pageState.students = p; renderStudentsTable(); });

      tbody.innerHTML = paged.items.map(s => `
        <tr>
          <td><code>${escapeHtml(s.id)}</code></td>
          <td><strong>${escapeHtml(s.name)}</strong></td>
          <td>${escapeHtml(s.email)}</td>
          <td>${escapeHtml(s.grade)}</td>
          <td>${escapeHtml(s.subjectsNeeded || 'None')}</td>
          <td>${s.sessionsCompleted || 0}</td>
          <td><span class="badge ${s.validated ? 'badge-success' : 'badge-pending'}">${s.validated ? 'Validated' : 'Unvalidated'}</span></td>
          <td><span class="badge ${s.deactivated ? 'badge-danger' : 'badge-success'}">${s.deactivated ? 'Deactivated' : 'Active'}</span></td>
          <td>${escapeHtml(s.createdAt ? s.createdAt.substring(0, 10) : '')}</td>
          <td>
            <button class="btn btn-secondary btn-small" onclick="viewStudentDetails('${escapeHtml(s.id)}')">View Student List</button>
            <button class="btn btn-primary btn-small" onclick="openEditStudentModal('${escapeHtml(s.id)}')">Edit</button>
            <button class="btn btn-secondary btn-small" onclick="toggleStudentValidate('${escapeHtml(s.id)}', ${!s.validated})">${s.validated ? 'Unvalidate' : 'Validate Student List'}</button>
            <button class="btn btn-secondary btn-small" onclick="toggleStudentDeactivate('${escapeHtml(s.id)}', ${!s.deactivated})">${s.deactivated ? 'Reactivate' : 'Deactivate'}</button>
          </td>
        </tr>
      `).join('');
    }

    function viewStudentDetails(id) {
      const s = allStudentsCache.find(x => x.id === id);
      if (!s) return;
      const content = document.getElementById('student-detail-content');
      content.innerHTML = `
        <p><strong>Student ID:</strong> <code>${escapeHtml(s.id)}</code></p>
        <p><strong>Full Name:</strong> ${escapeHtml(s.name)}</p>
        <p><strong>Email Address:</strong> ${escapeHtml(s.email)}</p>
        <p><strong>Grade Level:</strong> ${escapeHtml(s.grade)}</p>
        <p><strong>Bio / Notes:</strong> ${escapeHtml(s.bio || 'No bio provided.')}</p>
        <p><strong>Subjects Needed:</strong> ${escapeHtml(s.subjectsNeeded || 'None')}</p>
        <p><strong>Sessions Completed:</strong> ${s.sessionsCompleted || 0}</p>
        <p><strong>Validation Status:</strong> ${s.validated ? 'Validated' : 'Unvalidated'}</p>
        <p><strong>Account Status:</strong> ${s.deactivated ? 'Deactivated' : 'Active'}</p>
      `;
      openModal('modal-view-student');
    }

    function openEditStudentModal(id) {
      const s = allStudentsCache.find(x => x.id === id);
      if (!s) return;
      document.getElementById('edit-stu-id').value = s.id;
      document.getElementById('edit-stu-name').value = s.name;
      document.getElementById('edit-stu-grade').value = s.grade;
      document.getElementById('edit-stu-subjects').value = s.subjectsNeeded || '';
      openModal('modal-edit-student');
    }

    async function handleSaveEditStudent(e) {
      e.preventDefault();
      const id = document.getElementById('edit-stu-id').value;
      const name = document.getElementById('edit-stu-name').value.trim();
      const grade = document.getElementById('edit-stu-grade').value.trim();
      const subjects_needed = document.getElementById('edit-stu-subjects').value.trim();

      try {
        await api('api/students.php', {
          method: 'PUT',
          body: { id, name, grade, subjects_needed }
        });
        showSuccess('Student record updated.');
        closeModal('modal-edit-student');
        loadStudents();
      } catch (err) {}
    }

    async function toggleStudentValidate(id, newStatus) {
      if (!confirm(`Are you sure you want to ${newStatus ? 'validate' : 'unvalidate'} this student?`)) return;
      try {
        await api('api/students.php', {
          method: 'PUT',
          body: { id, validated: newStatus }
        });
        showSuccess('Student validation status updated.');
        loadStudents();
      } catch (e) {}
    }

    async function toggleStudentDeactivate(id, newDeact) {
      if (!confirm(`Are you sure you want to ${newDeact ? 'deactivate' : 'reactivate'} this student account?`)) return;
      try {
        await api('api/students.php', {
          method: 'PUT',
          body: { id, deactivated: newDeact }
        });
        showSuccess(`Student account ${newDeact ? 'deactivated' : 'reactivated'}.`);
        loadStudents();
      } catch (e) {}
    }

    // 3. TUTOR MATCHING (Review Matching Results, Approve Matching Sessions, Cancel Matching Sessions)
    async function loadMatches() {
      const tbody = document.getElementById('matches-table-body');
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">Loading match requests...</td></tr>';
      try {
        const json = await api('api/matches.php');
        allMatchesCache = json.data || [];
        renderMatchesTable();
      } catch (e) {}
    }

    function renderMatchesTable() {
      const tbody = document.getElementById('matches-table-body');
      const term = (document.getElementById('mat-search-input')?.value || '').toLowerCase();
      const statusFilter = document.getElementById('mat-status-filter')?.value || '';

      let filtered = allMatchesCache.filter(m => {
        const matchTerm = (m.studentName || '').toLowerCase().includes(term) || (m.tutorName || '').toLowerCase().includes(term);
        const matchStatus = statusFilter !== '' ? m.status === statusFilter : true;
        return matchTerm && matchStatus;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No match requests found.</td></tr>';
        document.getElementById('matches-pagination').innerHTML = '';
        return;
      }

      const paged = paginateArray(filtered, pageState.matches, 25);
      renderPaginationControls('matches-pagination', paged, (p) => { pageState.matches = p; renderMatchesTable(); });

      tbody.innerHTML = paged.items.map(m => `
        <tr>
          <td><code>${escapeHtml(m.id)}</code></td>
          <td><strong>${escapeHtml(m.studentName)}</strong></td>
          <td><strong>${escapeHtml(m.tutorName)}</strong></td>
          <td>${escapeHtml(m.subject)}</td>
          <td><strong style="color:var(--sf-accent-gold);">${m.score}%</strong></td>
          <td><span title="${escapeHtml(m.matchReason)}">${escapeHtml(m.matchReason.substring(0, 30))}...</span></td>
          <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : m.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(m.status)}</span></td>
          <td>${escapeHtml(m.createdAt ? m.createdAt.substring(0, 10) : '')}</td>
          <td>
            ${m.status === 'Pending Review' ? `<button class="btn btn-primary btn-small" onclick="approveMatch('${escapeHtml(m.id)}')">Approve Matching Sessions</button>` : ''}
            <button class="btn btn-secondary btn-small" onclick="openReassignMatchModal('${escapeHtml(m.id)}')">Reassign</button>
            ${m.status !== 'Cancelled' ? `<button class="btn btn-secondary btn-small" onclick="cancelMatch('${escapeHtml(m.id)}')">Cancel Matching Sessions</button>` : ''}
          </td>
        </tr>
      `).join('');
    }

    async function approveMatch(id) {
      if (!confirm('Approve this tutor match request?')) return;
      try {
        await api('api/matches.php', {
          method: 'PUT',
          body: { id, status: 'Approved' }
        });
        showSuccess('Approve Matching Sessions completed.');
        loadMatches();
      } catch (e) {}
    }

    async function cancelMatch(id) {
      const reason = prompt('Please enter cancellation reason for this match:');
      if (!reason) return;
      try {
        await api('api/matches.php', {
          method: 'PUT',
          body: { id, status: 'Cancelled', reason }
        });
        showSuccess('Cancel Matching Sessions completed.');
        loadMatches();
      } catch (e) {}
    }

    function openReassignMatchModal(id) {
      document.getElementById('reassign-match-id').value = id;
      const sel = document.getElementById('reassign-tutor-select');
      sel.innerHTML = allTutorsCache.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.name)} (${escapeHtml(t.subjects)})</option>`).join('');
      openModal('modal-reassign-match');
    }

    async function handleConfirmReassignMatch(e) {
      e.preventDefault();
      const id = document.getElementById('reassign-match-id').value;
      const tutor_id = document.getElementById('reassign-tutor-select').value;
      try {
        await api('api/matches.php', {
          method: 'PUT',
          body: { id, tutor_id, status: 'Reassigned' }
        });
        showSuccess('Match reassigned successfully.');
        closeModal('modal-reassign-match');
        loadMatches();
      } catch (e) {}
    }

    async function loadTutors() {
      const tbody = document.getElementById('tutors-table-body');
      try {
        const json = await api('api/tutors.php');
        allTutorsCache = json.data || [];
        tbody.innerHTML = allTutorsCache.map(t => `
          <tr>
            <td><code>${escapeHtml(t.id)}</code></td>
            <td><strong>${escapeHtml(t.name)}</strong></td>
            <td>${escapeHtml(t.email || '')}</td>
            <td>${formatMoney(t.hourlyRate)}</td>
            <td>⭐ ${t.rating} (${t.reviewsCount})</td>
            <td>${escapeHtml(t.subjects)}</td>
            <td><span class="badge ${t.deactivated ? 'badge-danger' : 'badge-success'}">${t.deactivated ? 'Deactivated' : 'Active'}</span></td>
            <td>
              <button class="btn btn-secondary btn-small" onclick="toggleTutorDeactivate('${escapeHtml(t.id)}', ${!t.deactivated})">${t.deactivated ? 'Reactivate' : 'Deactivate'}</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    async function toggleTutorDeactivate(id, newDeact) {
      if (!confirm(`Are you sure you want to ${newDeact ? 'deactivate' : 'reactivate'} this tutor?`)) return;
      try {
        await api('api/tutors.php', {
          method: 'PUT',
          body: { id, deactivated: newDeact }
        });
        showSuccess(`Tutor ${newDeact ? 'deactivated' : 'reactivated'}.`);
        loadTutors();
      } catch (e) {}
    }

    // 4. SCHEDULE (New Calendar Schedule & Modify Sessions Schedule)
    async function loadSchedules() {
      const tbody = document.getElementById('schedules-table-body');
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading schedules...</td></tr>';
      try {
        const json = await api('api/schedules.php');
        allSchedulesCache = json.data || [];

        const tutorSel = document.getElementById('sch-filter-tutor');
        if (tutorSel) {
          tutorSel.innerHTML = '<option value="">All Tutors</option>' + allTutorsCache.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.name)}</option>`).join('');
        }

        renderSchedulesTable();
      } catch (e) {}
    }

    function renderSchedulesTable() {
      const tbody = document.getElementById('schedules-table-body');
      const tutorFilter = document.getElementById('sch-filter-tutor')?.value || '';
      const statusFilter = document.getElementById('sch-filter-status')?.value || '';

      let filtered = allSchedulesCache.filter(s => {
        const matchTut = tutorFilter !== '' ? s.tutorId === tutorFilter : true;
        const matchStat = statusFilter !== '' ? s.status === statusFilter : true;
        return matchTut && matchStat;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No schedule slots found.</td></tr>';
        document.getElementById('schedules-pagination').innerHTML = '';
        return;
      }

      const paged = paginateArray(filtered, pageState.schedules, 25);
      renderPaginationControls('schedules-pagination', paged, (p) => { pageState.schedules = p; renderSchedulesTable(); });

      tbody.innerHTML = paged.items.map(s => `
        <tr>
          <td><code>${escapeHtml(s.id)}</code></td>
          <td><strong>${escapeHtml(s.tutorName)}</strong></td>
          <td>${escapeHtml(s.dateSlot)}</td>
          <td>${escapeHtml(s.subject)}</td>
          <td><span class="badge ${s.status === 'Available' ? 'badge-success' : s.status === 'Booked' ? 'badge-info' : 'badge-danger'}">${escapeHtml(s.status)}</span></td>
          <td>${escapeHtml(s.studentName || 'Unbooked')}</td>
          <td>
            ${s.status !== 'Booked' ? `<button class="btn btn-secondary btn-small" onclick="deleteScheduleSlot('${escapeHtml(s.id)}')">Delete</button>` : '<span style="font-size:0.8rem; color:var(--sf-ink-muted);">Locked</span>'}
          </td>
        </tr>
      `).join('');
    }

    function openNewCalendarScheduleModal() {
      const sel = document.getElementById('sch-add-tutor');
      sel.innerHTML = allTutorsCache.filter(t => !t.deactivated).map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.name)}</option>`).join('');
      populateAddScheduleSubjects();
      openModal('modal-add-schedule');
    }

    function populateAddScheduleSubjects() {
      const tutorId = document.getElementById('sch-add-tutor').value;
      const tut = allTutorsCache.find(t => t.id === tutorId);
      const subSel = document.getElementById('sch-add-subject');
      if (tut && tut.subjects) {
        const subs = tut.subjects.split(',').map(s => s.trim());
        subSel.innerHTML = subs.map(s => `<option value="${escapeHtml(s)}">${escapeHtml(s)}</option>`).join('');
      } else {
        subSel.innerHTML = '<option value="Mathematics">Mathematics</option>';
      }
    }

    async function handleSaveNewScheduleSlot(e) {
      e.preventDefault();
      const tutor_id = document.getElementById('sch-add-tutor').value;
      const date = document.getElementById('sch-add-date').value;
      const time_slot = document.getElementById('sch-add-slot').value.trim();
      const subject = document.getElementById('sch-add-subject').value;

      try {
        await api('api/schedules.php', {
          method: 'POST',
          body: { tutor_id, date, time_slot, subject }
        });
        showSuccess('New Calendar Schedule slot created.');
        closeModal('modal-add-schedule');
        loadSchedules();
      } catch (err) {}
    }

    async function deleteScheduleSlot(id) {
      if (!confirm('Delete this schedule slot?')) return;
      try {
        await api('api/schedules.php', {
          method: 'DELETE',
          body: { id }
        });
        showSuccess('Schedule slot deleted.');
        loadSchedules();
      } catch (e) {}
    }

    async function loadSessions() {
      const tbody = document.getElementById('sessions-table-body');
      tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Loading scheduled sessions...</td></tr>';
      try {
        const json = await api('api/sessions.php');
        allSessionsCache = json.data || [];
        renderSessionsTable();
      } catch (e) {}
    }

    function renderSessionsTable() {
      const tbody = document.getElementById('sessions-table-body');
      const term = (document.getElementById('ses-search-input')?.value || '').toLowerCase();
      const filterStatus = document.getElementById('ses-status-filter')?.value || '';

      let filtered = allSessionsCache.filter(s => {
        const matchTerm = (s.studentName || '').toLowerCase().includes(term) || (s.tutorName || '').toLowerCase().includes(term);
        const matchStatus = filterStatus !== '' ? s.status === filterStatus : true;
        return matchTerm && matchStatus;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">No scheduled sessions found.</td></tr>';
        document.getElementById('sessions-pagination').innerHTML = '';
        return;
      }

      const paged = paginateArray(filtered, pageState.sessions, 25);
      renderPaginationControls('sessions-pagination', paged, (p) => { pageState.sessions = p; renderSessionsTable(); });

      tbody.innerHTML = paged.items.map(s => `
        <tr>
          <td><strong>${escapeHtml(s.studentName)}</strong></td>
          <td><strong>${escapeHtml(s.tutorName)}</strong></td>
          <td>${escapeHtml(s.subject)}</td>
          <td>${escapeHtml(s.sessionDate)} (${escapeHtml(s.timeSlot)})</td>
          <td>${formatMoney(s.hourlyRate)}</td>
          <td>${formatMoney(s.commissionFee)}</td>
          <td><strong>${formatMoney(s.totalPaid)}</strong></td>
          <td><code>${escapeHtml(s.gcashRef)}</code></td>
          <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(s.status)}</span></td>
          <td><span class="badge ${s.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${escapeHtml(s.payoutStatus)}</span></td>
          <td>
            ${s.status === 'Confirmed' ? `<button class="btn btn-secondary btn-small" onclick="openRescheduleModal('${escapeHtml(s.id)}')">Modify Sessions Schedule</button>` : ''}
            ${s.status !== 'Cancelled' && s.status !== 'Completed' ? `<button class="btn btn-secondary btn-small" onclick="cancelSession('${escapeHtml(s.id)}')">Cancel</button>` : ''}
          </td>
        </tr>
      `).join('');
    }

    function openRescheduleModal(id) {
      document.getElementById('resch-session-id').value = id;
      openModal('modal-reschedule-session');
    }

    async function handleConfirmReschedule(e) {
      e.preventDefault();
      const id = document.getElementById('resch-session-id').value;
      const session_date = document.getElementById('resch-date').value;
      const time_slot = document.getElementById('resch-slot').value.trim();

      try {
        await api('api/sessions.php', {
          method: 'PUT',
          body: { id, session_date, time_slot }
        });
        showSuccess('Modify Sessions Schedule completed.');
        closeModal('modal-reschedule-session');
        loadSessions();
      } catch (e) {}
    }

    async function cancelSession(id) {
      const reason = prompt('Please enter cancellation reason for this session:');
      if (!reason) return;
      try {
        await api('api/sessions.php', {
          method: 'PUT',
          body: { id, status: 'Cancelled', reason }
        });
        showSuccess('Session cancelled.');
        loadSessions();
      } catch (e) {}
    }

    // 5. PAYMENTS (New Payment Records & Confirm Payment Status)
    async function loadPayments() {
      const tbody = document.getElementById('payments-table-body');
      tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Loading payment transactions...</td></tr>';
      try {
        const json = await api('api/payments.php');
        allPaymentsCache = json.data || [];
        renderPaymentsTable();
      } catch (e) {}
    }

    function renderPaymentsTable() {
      const tbody = document.getElementById('payments-table-body');
      const term = (document.getElementById('pay-search-input')?.value || '').toLowerCase();
      const statusFilter = document.getElementById('pay-status-filter')?.value || '';
      const payoutFilter = document.getElementById('pay-payout-filter')?.value || '';

      let filtered = allPaymentsCache.filter(p => {
        const matchTerm = (p.refNo || '').toLowerCase().includes(term) || (p.studentName || '').toLowerCase().includes(term);
        const matchStatus = statusFilter !== '' ? p.status === statusFilter : true;
        const matchPayout = payoutFilter !== '' ? p.payoutStatus === payoutFilter : true;
        return matchTerm && matchStatus && matchPayout;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">No payment records found.</td></tr>';
        document.getElementById('payments-table-foot').innerHTML = '';
        document.getElementById('payments-pagination').innerHTML = '';
        return;
      }

      let totalCollected = 0;
      let totalCommission = 0;
      let pendingAmount = 0;

      filtered.forEach(p => {
        const amt = parseFloat(p.amount) || 0;
        const comm = parseFloat(p.commissionFee) || roundComm(amt);
        if (p.status === 'Confirmed') {
          totalCollected += amt;
          totalCommission += comm;
        } else {
          pendingAmount += amt;
        }
      });

      document.getElementById('payments-table-foot').innerHTML = `
        <tr>
          <td colspan="5">Totals (Filtered)</td>
          <td>${formatMoney(totalCollected)}</td>
          <td>${formatMoney(totalCommission)}</td>
          <td colspan="4">Pending Amount: ${formatMoney(pendingAmount)}</td>
        </tr>
      `;

      const paged = paginateArray(filtered, pageState.payments, 25);
      renderPaginationControls('payments-pagination', paged, (p) => { pageState.payments = p; renderPaymentsTable(); });

      tbody.innerHTML = paged.items.map(p => `
        <tr>
          <td><code>${escapeHtml(p.id)}</code></td>
          <td><strong>${escapeHtml(p.studentName)}</strong></td>
          <td><strong>${escapeHtml(p.tutorName)}</strong></td>
          <td>${escapeHtml(p.method)}</td>
          <td><code>${escapeHtml(p.refNo)}</code></td>
          <td>${formatMoney(p.amount)}</td>
          <td>${formatMoney(p.commissionFee || roundComm(p.amount))}</td>
          <td><span class="badge ${p.status === 'Confirmed' ? 'badge-success' : 'badge-pending'}">${escapeHtml(p.status)}</span></td>
          <td><span class="badge ${p.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${escapeHtml(p.payoutStatus)}</span></td>
          <td>${escapeHtml(p.createdAt ? p.createdAt.substring(0, 10) : '')}</td>
          <td>
            ${p.status !== 'Confirmed' ? `<button class="btn btn-primary btn-small" onclick="togglePaymentConfirm('${escapeHtml(p.id)}', 'Confirmed')">Confirm Payment Status</button>` : ''}
            ${p.payoutStatus !== 'Paid Out' ? `<button class="btn btn-secondary btn-small" onclick="processPayout('${escapeHtml(p.id)}')">Process Payout</button>` : ''}
          </td>
        </tr>
      `).join('');
    }

    function roundComm(amt) {
      return Math.round((parseFloat(amt) || 0) * 0.10 / 1.10 * 100) / 100;
    }

    async function togglePaymentConfirm(id, newStatus) {
      if (!confirm(`Are you sure you want to set payment status to ${newStatus}?`)) return;
      try {
        await api('api/payments.php', {
          method: 'PUT',
          body: { id, status: newStatus }
        });
        showSuccess('Confirm Payment Status completed.');
        loadPayments();
      } catch (e) {}
    }

    async function processPayout(id) {
      if (!confirm('Process payout for this tutor payment?')) return;
      try {
        await api('api/payments.php', {
          method: 'PUT',
          body: { id, payout_status: 'Paid Out' }
        });
        showSuccess('Payout processed successfully.');
        loadPayments();
      } catch (e) {}
    }

    // 6. NOTIFICATIONS (Send Notifications & View Notifications)
    function switchNotifSubTab(subTab) {
      currentNotifSubTab = subTab;
      document.getElementById('btn-notif-tab-sent').classList.toggle('active', subTab === 'sent');
      document.getElementById('btn-notif-tab-inbox').classList.toggle('active', subTab === 'inbox');
      loadNotifications();
    }

    function toggleSpecificUserPicker() {
      const role = document.getElementById('notif-target-role').value;
      const pickerGroup = document.getElementById('specific-user-picker-group');
      const sel = document.getElementById('notif-specific-user-id');

      if (role === 'student_specific') {
        pickerGroup.style.display = 'block';
        sel.innerHTML = '<option value="">Select student...</option>' + allStudentsCache.map(s => `<option value="${escapeHtml(s.id)}">${escapeHtml(s.name)} (${escapeHtml(s.email)})</option>`).join('');
      } else if (role === 'tutor_specific') {
        pickerGroup.style.display = 'block';
        sel.innerHTML = '<option value="">Select tutor...</option>' + allTutorsCache.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.name)} (${escapeHtml(t.email || '')})</option>`).join('');
      } else {
        pickerGroup.style.display = 'none';
      }
    }

    async function handleSendNotification(e) {
      e.preventDefault();
      const audience = document.getElementById('notif-target-role').value;
      const specificUserId = document.getElementById('notif-specific-user-id').value;
      const title = document.getElementById('notif-title').value.trim();
      const message = document.getElementById('notif-message').value.trim();
      const priority = document.getElementById('notif-priority').value;

      if (inArray(audience, ['all', 'students', 'tutors'])) {
        if (!confirm(`Are you sure you want to send this broadcast notification to ${audience.toUpperCase()}?`)) return;
      }

      try {
        const json = await api('api/notifications.php', {
          method: 'POST',
          body: { audience, specific_user_id: specificUserId, title, message, priority }
        });
        showSuccess(json.message || 'Send Notifications completed.');
        document.getElementById('notif-title').value = '';
        document.getElementById('notif-message').value = '';
        loadNotifications();
      } catch (err) {}
    }

    function inArray(val, arr) { return arr.includes(val); }

    async function loadNotifications() {
      const container = document.getElementById('notifications-log-list');
      container.innerHTML = '<p style="text-align:center; color: var(--sf-ink-muted);">Loading notifications...</p>';
      try {
        const json = await api(`api/notifications.php?tab=${currentNotifSubTab}`);
        const notifs = json.data || [];
        if (notifs.length === 0) {
          container.innerHTML = '<p style="text-align:center; color: var(--sf-ink-muted);">No notifications log found.</p>';
          return;
        }

        if (currentNotifSubTab === 'sent') {
          container.innerHTML = notifs.map(n => `
            <div class="card-panel" style="padding: 14px; margin-bottom: 10px;">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong>${escapeHtml(n.title)}</strong>
                <span class="badge badge-info">Reads: ${n.readCount || 0}</span>
              </div>
              <p style="margin: 6px 0; font-size: 0.9rem;">${escapeHtml(n.message)}</p>
              <div style="font-size: 0.78rem; color: var(--sf-ink-muted); text-align: right;">${escapeHtml(n.createdAt || '')}</div>
            </div>
          `).join('');
        } else {
          container.innerHTML = notifs.map(n => `
            <div class="card-panel" style="padding: 14px; margin-bottom: 10px; background: ${n.isRead ? '#ffffff' : '#f0f9ff'};">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong>${escapeHtml(n.title)}</strong>
                <span class="badge ${n.isRead ? 'badge-success' : 'badge-pending'}">${n.isRead ? 'Read' : 'Unread'}</span>
              </div>
              <p style="margin: 6px 0; font-size: 0.9rem;">${escapeHtml(n.message)}</p>
              <div style="font-size: 0.78rem; color: var(--sf-ink-muted); text-align: right;">${escapeHtml(n.createdAt || '')}</div>
            </div>
          `).join('');
        }
      } catch (e) {}
    }

    // 7. REPORTS (View Completed Tutoring, View Weekly Sessions, View Monthly Sessions - NO CSV)
    function applyReportPreset(presetLabel) {
      currentReportPreset = presetLabel;
      currentReportPage = 1;
      document.getElementById('btn-rep-completed').classList.toggle('active', presetLabel === 'View Completed Tutoring');
      document.getElementById('btn-rep-weekly').classList.toggle('active', presetLabel === 'View Weekly Sessions');
      document.getElementById('btn-rep-monthly').classList.toggle('active', presetLabel === 'View Monthly Sessions');
      loadReportsData();
    }

    async function loadReportsData() {
      const tbody = document.getElementById('reports-table-body');
      tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Loading report sessions...</td></tr>';

      const dateFrom = document.getElementById('rep-date-from')?.value || '';
      const dateTo = document.getElementById('rep-date-to')?.value || '';
      const status = document.getElementById('rep-status-select')?.value || '';
      const tutorId = document.getElementById('rep-tutor-select')?.value || '';
      const search = document.getElementById('rep-search-input')?.value || '';

      const url = `api/reports.php?preset=${encodeURIComponent(currentReportPreset)}&start_date=${dateFrom}&end_date=${dateTo}&status=${status}&tutor_id=${tutorId}&search=${encodeURIComponent(search)}&page=${currentReportPage}&per_page=25`;

      try {
        const json = await api(url);
        if (json && json.summary) {
          const s = json.summary;
          document.getElementById('rep-kpi-total-sessions').textContent = s.total_sessions || 0;
          document.getElementById('rep-kpi-completed-sessions').textContent = s.completed_sessions || 0;
          document.getElementById('rep-kpi-cancelled-sessions').textContent = s.cancelled_sessions || 0;
          document.getElementById('rep-kpi-total-paid').textContent = formatMoney(s.total_paid);
          document.getElementById('rep-kpi-total-commission').textContent = formatMoney(s.total_commission);
        }

        const sessions = json.sessions || [];
        if (sessions.length === 0) {
          tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">No sessions found for this report period.</td></tr>';
          document.getElementById('reports-pagination').innerHTML = '';
          return;
        }

        const pag = json.pagination || { total_records: sessions.length, current_page: 1, total_pages: 1 };
        renderPaginationControls('reports-pagination', {
          items: sessions,
          page: pag.current_page,
          totalPages: pag.total_pages,
          totalItems: pag.total_records
        }, (p) => { currentReportPage = p; loadReportsData(); });

        tbody.innerHTML = sessions.map(s => `
          <tr>
            <td><code>${escapeHtml(s.id)}</code></td>
            <td>${escapeHtml(s.sessionDate)} (${escapeHtml(s.timeSlot)})</td>
            <td><strong>${escapeHtml(s.studentName)}</strong></td>
            <td><strong>${escapeHtml(s.tutorName)}</strong></td>
            <td>${escapeHtml(s.subject)}</td>
            <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(s.status)}</span></td>
            <td>${formatMoney(s.hourlyRate)}</td>
            <td>${formatMoney(s.commissionFee)}</td>
            <td><strong>${formatMoney(s.totalPaid)}</strong></td>
            <td><code>${escapeHtml(s.gcashRef)}</code></td>
            <td><span class="badge ${s.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${escapeHtml(s.payoutStatus)}</span></td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    // Helper Pagination Functions
    function paginateArray(arr, page, perPage) {
      const totalItems = arr.length;
      const totalPages = Math.max(1, Math.ceil(totalItems / perPage));
      const currPage = Math.max(1, Math.min(page, totalPages));
      const start = (currPage - 1) * perPage;
      return {
        items: arr.slice(start, start + perPage),
        page: currPage,
        totalPages,
        totalItems
      };
    }

    function renderPaginationControls(containerId, pagData, onPageChange) {
      const el = document.getElementById(containerId);
      if (!el) return;
      if (pagData.totalPages <= 1) {
        el.innerHTML = `<span>Showing all ${pagData.totalItems} records</span>`;
        return;
      }

      let buttonsHtml = '';
      for (let i = 1; i <= pagData.totalPages; i++) {
        buttonsHtml += `<button class="btn-page ${i === pagData.page ? 'active' : ''}">${i}</button>`;
      }

      el.innerHTML = `
        <span>Page ${pagData.page} of ${pagData.totalPages} (${pagData.totalItems} total records)</span>
        <div class="pagination-controls">${buttonsHtml}</div>
      `;

      el.querySelectorAll('.btn-page').forEach((btn, idx) => {
        btn.onclick = () => onPageChange(idx + 1);
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadDashboard();
      // Pre-cache tutors and students for select dropdowns
      api('api/tutors.php').then(j => { allTutorsCache = j.data || []; }).catch(e => {});
      api('api/students.php').then(j => { allStudentsCache = j.data || []; }).catch(e => {});
    });
  </script>
</body>
</html>
