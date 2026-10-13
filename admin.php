<?php
// admin.php - Standalone Admin Monitoring Portal
require_once __DIR__ . '/api/config.php';

// Strictly enforce Admin Session Access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TutorLink Admin</title>
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
      <div class="sidebar-brand" style="display: flex; align-items: center; gap: 10px;">
        <img src="assets/logo.png" alt="TutorLink Logo" style="height: 38px; width: 38px; object-fit: contain; border-radius: 50%;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'38\' height=\'38\' viewBox=\'0 0 38 38\'><circle cx=\'19\' cy=\'19\' r=\'18\' fill=\'%2310b981\'/><text x=\'50%\' y=\'55%\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23ffffff\' font-family=\'sans-serif\' font-size=\'14\' font-weight=\'bold\'>TL</text></svg>';">
        <div class="brand-text">
          <span class="brand-title">TutorLink</span>
          <span class="brand-subtitle">ADMIN PORTAL</span>
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
          <li class="menu-item" data-admin-nav="tutors">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('tutors')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span>Tutors</span>
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
          <li class="menu-item" data-admin-nav="notifications">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('notifications')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span>Notifications</span>
            </a>
          </li>
          <li class="menu-item" data-admin-nav="reports">
            <a href="javascript:void(0)" class="menu-link" onclick="switchMainAdminTab('reports')">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              <span>Reports</span>
            </a>
          </li>
        </ul>

      </div>

    </aside>

    <!-- Main Content Canvas -->
    <div class="admin-main">
      <!-- Top Header Navigation -->
      <header class="admin-header">
        <div class="header-left">
          <button class="admin-mobile-toggle" onclick="toggleAdminSidebar()" aria-label="Toggle Sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>
          <div class="header-breadcrumb">
            <span class="breadcrumb-prefix">Admin Portal</span>
            <span class="breadcrumb-separator">/</span>
            <span id="current-admin-tab-title" class="breadcrumb-active">Dashboard</span>
          </div>
        </div>

        <div class="header-actions" style="display: flex; align-items: center; gap: 12px;">
          <button class="icon-btn relative" title="Notifications" onclick="switchMainAdminTab('notifications')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="notification-badge"></span>
          </button>
          <button id="admin-logout-btn" class="btn btn-secondary btn-small" style="display: flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Log Out</span>
          </button>
        </div>
      </header>

      <!-- Dashboard View Content Area -->
      <main class="content-container">

        <!-- SECTION 1: Dashboard Overview KPIs & Visual Analytics -->
        <section id="admin-section-dashboard" class="admin-canvas-section active margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h1 class="page-title">Dashboard Overview</h1>
              <p class="page-subtitle">Welcome back, Admin. Here is your real-time platform overview.</p>
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
            </div>

            <div class="apex-kpi-card" onclick="switchMainAdminTab('tutors')">
              <div class="kpi-header">
                <span class="kpi-label">Verified Tutors</span>
                <div class="kpi-icon-wrapper blue-light">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="admin-stat-total-tutors">0</div>
              <div class="kpi-trend positive">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <span>Active</span>
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
            </div>
          </div>
        </section>

        <!-- SECTION 2: Tutors Management Module -->
        <section id="admin-section-tutors" class="admin-canvas-section margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title">Tutor Management</h2>
              <p class="page-subtitle">Verify documents, approve tutor registrations, and manage tutor account statuses.</p>
            </div>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Tutors Directory</h3>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Tutor ID</th>
                    <th>Name</th>
                    <th>Subjects</th>
                    <th>Rate (₱)</th>
                    <th>Diploma</th>
                    <th>TOR</th>
                    <th>Gov ID</th>
                    <th>Approval Status</th>
                    <th>Account Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="admin-tutors-table-body">
                  <!-- Dynamic tutor rows -->
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- SECTION 3: Students Module -->
        <section id="admin-section-students" class="admin-canvas-section margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title">Students</h2>
              <p class="page-subtitle">View registered student accounts and validate registrations.</p>
            </div>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Registered Students List</h3>
            </div>
            <div class="table-responsive">
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
        </section>

        <!-- SECTION 4: Tutor Matching Module -->
        <section id="admin-section-matching" class="admin-canvas-section margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title">Tutor Matching</h2>
              <p class="page-subtitle">Review AI matching recommendations and approve tutoring sessions.</p>
            </div>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Tutor Match Requests</h3>
            </div>
            <div class="table-responsive">
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
        </section>

        <!-- SECTION 5: Schedule Module -->
        <section id="admin-section-schedule" class="admin-canvas-section margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title">Schedule</h2>
              <p class="page-subtitle">Modify scheduled sessions and create available calendar schedule slots.</p>
            </div>
          </div>

          <div class="card-table-wrapper margin-bottom">
            <div class="table-header-title">
              <h3>Scheduled Sessions</h3>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Session ID</th>
                    <th>Student</th>
                    <th>Tutor</th>
                    <th>Subject</th>
                    <th>Date & Slot</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="admin-scheduled-sessions-table-body">
                  <!-- Dynamic scheduled sessions rows -->
                </tbody>
              </table>
            </div>
          </div>

          <div class="card-table-wrapper margin-bottom">
            <div class="table-header-title">
              <h3>New Calendar Schedule Slot</h3>
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
              <h3>Active Available Schedules</h3>
            </div>
            <div class="table-responsive">
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
        </section>

        <!-- SECTION 6: Payments Module -->
        <section id="admin-section-payments" class="admin-canvas-section margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title">Payments & Fees</h2>
              <p class="page-subtitle">Monitor GCash transactions, payment statuses, and tutor payouts.</p>
            </div>
          </div>

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Payment Transactions Log</h3>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Payment ID</th>
                    <th>Payer (Student)</th>
                    <th>Method</th>
                    <th>GCash Ref #</th>
                    <th>Amount (₱)</th>
                    <th>Status</th>
                    <th>Payout Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="admin-payments-table-body">
                  <!-- Dynamic payment rows -->
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- SECTION 7: Reports Module -->
        <section id="admin-section-reports" class="admin-canvas-section margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title">Reports</h2>
              <p class="page-subtitle">View completed tutoring sessions and print activity summaries.</p>
            </div>
            <div style="display: flex; gap: 8px;">
              <button class="btn btn-emerald" onclick="window.print()">Print Activity Summary</button>
            </div>
          </div>

          <div class="card-table-wrapper margin-bottom">
            <div class="table-header-title">
              <h3>All Completed Tutoring Sessions</h3>
            </div>
            <div class="table-responsive">
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

          <div class="card-table-wrapper margin-bottom">
            <div class="table-header-title">
              <h3>Weekly Sessions (Last 7 Days)</h3>
            </div>
            <div class="table-responsive">
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

          <div class="card-table-wrapper">
            <div class="table-header-title">
              <h3>Monthly Sessions (Last 30 Days)</h3>
            </div>
            <div class="table-responsive">
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
        </section>

        <!-- SECTION 8: Notifications Module -->
        <section id="admin-section-notifications" class="admin-canvas-section margin-bottom">
          <div class="dashboard-title-area">
            <div>
              <h2 class="page-title">Notifications</h2>
              <p class="page-subtitle">Send system broadcasts and view notification history log.</p>
            </div>
          </div>

          <div class="card-table-wrapper margin-bottom" style="padding: 20px;">
            <h3 class="margin-bottom">Send Broadcast Notification</h3>
            <div class="form-group margin-bottom">
              <label>Recipient Target</label>
              <select id="admin-notif-target" class="form-select">
                <option value="all">All Users (Students & Tutors)</option>
                <option value="student">All Students</option>
                <option value="tutor">All Tutors</option>
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
        </section>

      </main>
    </div>
  </div>

  <!-- Notification Toast Container -->
  <div class="toast-container" id="toast-container"></div>

  <!-- Reschedule Modal for Admin Schedule Modifying -->
  <div class="modal-backdrop" id="modal-reschedule">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Modify Session Schedule</h3>
        <button class="close-btn" id="close-reschedule-modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="reschedule-session-form">
          <input type="hidden" id="reschedule-session-id">
          <div class="form-group">
            <label>New Date</label>
            <input type="date" id="reschedule-date-input" class="form-input" required>
          </div>
          <div class="form-group">
            <label>New Time Slot</label>
            <select id="reschedule-timeslot-select" class="form-select" required>
              <!-- Populated dynamically -->
            </select>
          </div>
          <div class="modal-actions margin-top">
            <button type="button" class="btn btn-secondary" id="cancel-reschedule-btn">Cancel</button>
            <button type="submit" class="btn btn-primary" id="confirm-reschedule-btn">Confirm Reschedule</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="app.js"></script>
  <script>
    const tabTitles = {
      'dashboard': 'Dashboard Overview',
      'tutors': 'Tutor Management',
      'students': 'Student Management',
      'matching': 'Tutor Matching',
      'schedule': 'Schedules',
      'payments': 'Payments & Fees',
      'reports': 'System Reports',
      'notifications': 'Notifications'
    };

    function switchMainAdminTab(tabName) {
      const sections = document.querySelectorAll('.admin-canvas-section');
      sections.forEach(sec => sec.classList.remove('active'));

      const targetSection = document.getElementById(`admin-section-${tabName}`);
      if (targetSection) {
        targetSection.classList.add('active');
      }

      const titleElem = document.getElementById('current-admin-tab-title');
      if (titleElem && tabTitles[tabName]) {
        titleElem.textContent = tabTitles[tabName];
      }

      document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-admin-nav') === tabName) {
          item.classList.add('active');
        }
      });

      const sidebar = document.querySelector('.admin-sidebar');
      if (sidebar && sidebar.classList.contains('mobile-open')) {
        sidebar.classList.remove('mobile-open');
      }
    }

    function toggleAdminSidebar() {
      const sidebar = document.querySelector('.admin-sidebar');
      if (sidebar) {
        sidebar.classList.toggle('mobile-open');
      }
    }

    // Admin-specific rendering handlers
    function renderAdminTutors() {
      const tbody = document.getElementById('admin-tutors-table-body');
      if (!tbody) return;

      if (state.tutors.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; color: var(--ink-soft); padding: 20px;">No tutors registered yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = state.tutors.map(t => {
        const docsAllVerified = (t.diplomaStatus === 'Verified' && t.torStatus === 'Verified' && t.idStatus === 'Verified');
        const isApproved = t.approvalStatus === 'Approved';

        return `
          <tr>
            <td><code>${esc(t.id)}</code></td>
            <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(t.id)}')">${esc(t.name)}</strong></td>
            <td>${esc(t.subjects.join(', '))}</td>
            <td>₱${esc(t.hourlyRate)}</td>
            <td>
              <span class="badge ${t.diplomaStatus === 'Verified' ? 'badge-success' : t.diplomaStatus === 'Rejected' ? 'badge-danger' : 'badge-info'}">${esc(t.diplomaStatus)}</span>
              <div style="margin-top: 4px; display: flex; gap: 4px;">
                <button class="btn btn-secondary btn-small" onclick="updateTutorDoc('${esc(t.id)}', 'diploma_status', 'Verified')">Verify</button>
                <button class="btn btn-secondary btn-small" onclick="updateTutorDoc('${esc(t.id)}', 'diploma_status', 'Rejected')">Reject</button>
              </div>
            </td>
            <td>
              <span class="badge ${t.torStatus === 'Verified' ? 'badge-success' : t.torStatus === 'Rejected' ? 'badge-danger' : 'badge-info'}">${esc(t.torStatus)}</span>
              <div style="margin-top: 4px; display: flex; gap: 4px;">
                <button class="btn btn-secondary btn-small" onclick="updateTutorDoc('${esc(t.id)}', 'tor_status', 'Verified')">Verify</button>
                <button class="btn btn-secondary btn-small" onclick="updateTutorDoc('${esc(t.id)}', 'tor_status', 'Rejected')">Reject</button>
              </div>
            </td>
            <td>
              <span class="badge ${t.idStatus === 'Verified' ? 'badge-success' : t.idStatus === 'Rejected' ? 'badge-danger' : 'badge-info'}">${esc(t.idStatus)}</span>
              <div style="margin-top: 4px; display: flex; gap: 4px;">
                <button class="btn btn-secondary btn-small" onclick="updateTutorDoc('${esc(t.id)}', 'id_status', 'Verified')">Verify</button>
                <button class="btn btn-secondary btn-small" onclick="updateTutorDoc('${esc(t.id)}', 'id_status', 'Rejected')">Reject</button>
              </div>
            </td>
            <td><span class="badge ${isApproved ? 'badge-success' : t.approvalStatus === 'Declined' ? 'badge-danger' : 'badge-info'}">${esc(t.approvalStatus)}</span></td>
            <td><span class="badge ${t.deactivated ? 'badge-danger' : 'badge-success'}">${t.deactivated ? 'Deactivated' : 'Active'}</span></td>
            <td>
              ${!docsAllVerified && !isApproved ? `
                <button class="btn btn-secondary btn-small" disabled title="Cannot approve tutor: Diploma, TOR, and ID status must all be Verified.">Approve</button>
              ` : `
                <button class="btn btn-primary btn-small" onclick="updateTutorApproval('${esc(t.id)}', 'Approved')">Approve</button>
              `}
              <button class="btn btn-secondary btn-small" onclick="updateTutorApproval('${esc(t.id)}', 'Declined')">Decline</button>
              <button class="btn btn-secondary btn-small" onclick="toggleDeactivateTutor('${esc(t.id)}')">${t.deactivated ? 'Activate' : 'Deactivate'}</button>
            </td>
          </tr>
        `;
      }).join('');
    }

    window.updateTutorDoc = async function(tutorId, field, value) {
      try {
        const res = await fetch('api/tutors.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: tutorId, [field]: value })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Doc update failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminTutors();
        showToast('Document status updated.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    window.updateTutorApproval = async function(tutorId, statusVal) {
      try {
        const res = await fetch('api/tutors.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: tutorId, approval_status: statusVal })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Approval update failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminTutors();
        showToast(`Tutor approval status set to ${statusVal}.`);
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    window.toggleDeactivateTutor = async function(tutorId) {
      const tutor = state.tutors.find(t => t.id === tutorId);
      if (!tutor) return;
      try {
        const res = await fetch('api/tutors.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: tutorId, deactivated: !tutor.deactivated })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Deactivation update failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminTutors();
        showToast('Tutor account status updated.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    function renderAdminStudents() {
      const tbody = document.getElementById('admin-students-table-body');
      if (!tbody) return;

      if (state.students.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No students registered yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = state.students.map(s => `
        <tr>
          <td><code>${esc(s.id)}</code></td>
          <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(s.id)}')">${esc(s.name)}</strong></td>
          <td>${esc(s.email)}</td>
          <td>${esc(s.grade)}</td>
          <td><span class="badge ${s.validated ? 'badge-success' : 'badge-info'}">${s.validated ? 'Validated' : 'Pending Validation'}</span></td>
          <td><span class="badge ${s.deactivated ? 'badge-danger' : 'badge-success'}">${s.deactivated ? 'Deactivated' : 'Active'}</span></td>
          <td>
            <button class="btn btn-secondary btn-small" onclick="viewStudentProfile('${esc(s.id)}')">Profile</button>
            <button class="btn btn-secondary btn-small" onclick="toggleValidateStudent('${esc(s.id)}')">${s.validated ? 'Revoke' : 'Validate Account'}</button>
            <button class="btn btn-secondary btn-small" onclick="toggleDeactivateStudent('${esc(s.id)}')">${s.deactivated ? 'Activate' : 'Deactivate'}</button>
          </td>
        </tr>
      `).join('');
    }

    window.toggleValidateStudent = async function(studentId) {
      const student = state.students.find(s => s.id === studentId);
      if (!student) return;
      try {
        const res = await fetch('api/students.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: studentId, validated: !student.validated })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Validation update failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminStudents();
        showToast('Student validation status updated.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    window.toggleDeactivateStudent = async function(studentId) {
      const student = state.students.find(s => s.id === studentId);
      if (!student) return;
      try {
        const res = await fetch('api/students.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: studentId, deactivated: !student.deactivated })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Deactivation update failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminStudents();
        showToast('Student account status updated.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    function renderAdminMatching() {
      const tbody = document.getElementById('admin-matching-table-body');
      if (!tbody) return;

      if (state.matches.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No match requests recorded yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = state.matches.map(m => `
        <tr>
          <td><code>${esc(m.id)}</code></td>
          <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(m.studentId || m.studentName)}')">${esc(m.studentName)}</span></td>
          <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(m.tutorId || m.tutorName)}')">${esc(m.tutorName)}</strong></td>
          <td>${esc(m.subject)}</td>
          <td><span class="badge badge-match">${esc(m.score)}% Match</span></td>
          <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : m.status === 'Cancelled' ? 'badge-danger' : 'badge-info'}">${esc(m.status)}</span></td>
          <td>
            ${m.status === 'Pending Review' ? `
              <button class="btn btn-primary btn-small" onclick="approveMatch('${esc(m.id)}')">Approve</button>
              <button class="btn btn-secondary btn-small" onclick="cancelMatch('${esc(m.id)}')">Cancel</button>
            ` : `<span class="sub-text">Processed</span>`}
          </td>
        </tr>
      `).join('');
    }

    window.approveMatch = async function(matchId) {
      try {
        const res = await fetch('api/matches.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: matchId, status: 'Approved' })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Match approval failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminMatching();
        showToast('Match request approved.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    window.cancelMatch = async function(matchId) {
      try {
        const res = await fetch('api/matches.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: matchId, status: 'Cancelled' })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Match cancel failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminMatching();
        showToast('Match request cancelled.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    function renderAdminSchedule() {
      const tbodySch = document.getElementById('admin-schedule-table-body');
      const tbodySes = document.getElementById('admin-scheduled-sessions-table-body');
      const tutorSelect = document.getElementById('admin-sch-tutor-select');

      if (tutorSelect) {
        tutorSelect.innerHTML = state.tutors.filter(t => !t.deactivated).map(t => `
          <option value="${esc(t.id)}">${esc(t.name)} (${esc(t.subjects.join(', '))})</option>
        `).join('');
      }

      if (tbodySes) {
        if (state.sessions.length === 0) {
          tbodySes.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No scheduled tutoring sessions found.</td></tr>`;
        } else {
          tbodySes.innerHTML = state.sessions.map(s => `
            <tr>
              <td><code>${esc(s.id)}</code></td>
              <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(s.studentId)}')">${esc(s.studentName)}</span></td>
              <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(s.tutorId)}')">${esc(s.tutorName)}</strong></td>
              <td>${esc(s.subject)}</td>
              <td>${esc(s.date || s.sessionDate)} (${esc(s.timeSlot)})</td>
              <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-info'}">${esc(s.status)}</span></td>
              <td>
                ${s.status === 'Confirmed' ? `
                  <button class="btn btn-secondary btn-small" onclick="openRescheduleModal('${esc(s.id)}')">Modify</button>
                  <button class="btn btn-secondary btn-small" onclick="cancelAdminSession('${esc(s.id)}')">Cancel Session</button>
                ` : `<span class="sub-text">Processed</span>`}
              </td>
            </tr>
          `).join('');
        }
      }

      if (tbodySch) {
        if (state.schedules.length === 0) {
          tbodySch.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">No available schedule slots created yet.</td></tr>`;
        } else {
          tbodySch.innerHTML = state.schedules.map(sch => `
            <tr>
              <td><code>${esc(sch.id)}</code></td>
              <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(sch.tutorId)}')">${esc(sch.tutorName)}</strong></td>
              <td>${esc(sch.dateSlot)}</td>
              <td>${esc(sch.subject)}</td>
              <td><span class="badge ${sch.status === 'Available' ? 'badge-success' : 'badge-info'}">${esc(sch.status)}</span></td>
              <td>
                <button class="btn btn-secondary btn-small" onclick="deleteScheduleSlot('${esc(sch.id)}')">Remove Slot</button>
              </td>
            </tr>
          `).join('');
        }
      }
    }

    window.cancelAdminSession = async function(sessionId) {
      try {
        const res = await fetch('api/sessions.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: sessionId, status: 'Cancelled' })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Session cancellation failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminSchedule();
        showToast('Session cancelled and schedule freed.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    window.deleteScheduleSlot = async function(schId) {
      try {
        const res = await fetch('api/schedules.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: schId })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Delete schedule failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminSchedule();
        showToast('Schedule slot removed.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    function renderAdminPayments() {
      const tbody = document.getElementById('admin-payments-table-body');
      if (!tbody) return;

      if (state.payments.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--ink-soft); padding: 20px;">No payment records logged yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = state.payments.map(p => `
        <tr>
          <td><code>${esc(p.id)}</code></td>
          <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(p.studentId)}')">${esc(p.studentName)}</span></td>
          <td>${esc(p.method)}</td>
          <td><code>${esc(p.refNo)}</code></td>
          <td>₱${esc(p.amount)}</td>
          <td><span class="badge ${p.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${esc(p.status)}</span></td>
          <td><span class="badge ${p.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${esc(p.payoutStatus || 'Unpaid')}</span></td>
          <td>
            ${p.status === 'Pending' ? `
              <button class="btn btn-primary btn-small" onclick="confirmPayment('${esc(p.id)}')">Confirm Payment</button>
            ` : ''}
            ${p.status === 'Confirmed' && p.payoutStatus !== 'Paid Out' ? `
              <button class="btn btn-secondary btn-small" onclick="processTutorPayout('${esc(p.id)}')">Process Payout</button>
            ` : ''}
          </td>
        </tr>
      `).join('');
    }

    window.confirmPayment = async function(paymentId) {
      try {
        const res = await fetch('api/payments.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: paymentId, status: 'Confirmed' })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Confirm payment failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminPayments();
        showToast('Payment confirmed!');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    window.processTutorPayout = async function(paymentId) {
      try {
        const res = await fetch('api/payments.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: paymentId, payout_status: 'Paid Out' })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Payout failed.', 'error');
          return;
        }
        await syncWithDatabase();
        renderAdminPayments();
        showToast('Tutor payout processed successfully.');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };

    function renderAdminReports() {
      const tbodyComp = document.getElementById('admin-reports-completed-table-body');
      const tbodyWk = document.getElementById('admin-reports-weekly-table-body');
      const tbodyMo = document.getElementById('admin-reports-monthly-table-body');

      const completed = state.sessions.filter(s => s.status === 'Completed');
      const today = new Date();
      const sevenDaysAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
      const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

      const weekly = state.sessions.filter(s => (s.date || s.sessionDate) >= sevenDaysAgo);
      const monthly = state.sessions.filter(s => (s.date || s.sessionDate) >= thirtyDaysAgo);

      if (tbodyComp) tbodyComp.innerHTML = renderReportRows(completed);
      if (tbodyWk) tbodyWk.innerHTML = renderReportRows(weekly);
      if (tbodyMo) tbodyMo.innerHTML = renderReportRows(monthly);
    }

    function renderReportRows(list) {
      if (list.length === 0) {
        return `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 16px;">No sessions found for this report filter.</td></tr>`;
      }
      return list.map(s => `
        <tr>
          <td><code>${esc(s.id)}</code></td>
          <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(s.studentId)}')">${esc(s.studentName)}</span></td>
          <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(s.tutorId)}')">${esc(s.tutorName)}</strong></td>
          <td>${esc(s.subject)}</td>
          <td>${esc(s.date || s.sessionDate)}</td>
          <td>₱${esc(s.totalPaid)}</td>
          <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : 'badge-info'}">${esc(s.status)}</span></td>
        </tr>
      `).join('');
    }

    function renderAdminNotifications() {
      const log = document.getElementById('admin-notifications-log');
      if (!log) return;

      if (state.notifications.length === 0) {
        log.innerHTML = `<p class="sub-text center-text" style="padding: 16px;">No broadcast notifications log recorded.</p>`;
        return;
      }

      log.innerHTML = state.notifications.map(n => `
        <div class="session-card" style="margin-bottom: 8px;">
          <div>
            <strong>${esc(n.title)}</strong> <span class="badge badge-info">Target: ${esc(n.targetRole || n.target)}</span>
            <p class="sub-text" style="margin-top: 4px;">${esc(n.message)}</p>
            <div class="sub-text" style="font-size: 0.78rem;">${esc(n.time)}</div>
          </div>
        </div>
      `).join('');
    }

    // Override renderAllViews for admin page
    function renderAllViews() {
      renderAdminTutors();
      renderAdminStudents();
      renderAdminMatching();
      renderAdminSchedule();
      renderAdminPayments();
      renderAdminReports();
      renderAdminNotifications();
    }

    document.addEventListener('DOMContentLoaded', () => {
      switchMainAdminTab('dashboard');

      document.getElementById('admin-add-schedule-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const tutorId = document.getElementById('admin-sch-tutor-select').value;
        const subject = document.getElementById('admin-sch-subject').value;
        const dateSlot = document.getElementById('admin-sch-dateslot').value;

        try {
          const res = await fetch('api/schedules.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              tutor_id: tutorId,
              subject,
              date_slot: dateSlot
            })
          });
          const data = await res.json();
          if (!res.ok || data.status !== 'success') {
            showToast(data.message || 'Schedule creation failed.', 'error');
            return;
          }
          await syncWithDatabase();
          renderAdminSchedule();
          showToast('New calendar schedule slot created!');
          document.getElementById('admin-add-schedule-form').reset();
        } catch (e) {
          showToast('Could not connect to server.', 'error');
        }
      });

      document.getElementById('admin-send-notif-btn')?.addEventListener('click', async () => {
        const targetRole = document.getElementById('admin-notif-target').value;
        const message = document.getElementById('admin-notif-message').value.trim();

        if (!message) {
          showToast('Please enter notification content.', 'error');
          return;
        }

        try {
          const res = await fetch('api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              target_role: targetRole,
              target: 'All Users',
              title: 'System Broadcast',
              message: message
            })
          });
          const data = await res.json();
          if (!res.ok || data.status !== 'success') {
            showToast(data.message || 'Broadcast failed.', 'error');
            return;
          }
          document.getElementById('admin-notif-message').value = '';
          await syncWithDatabase();
          renderAdminNotifications();
          showToast('Notification broadcast sent!');
        } catch (e) {
          showToast('Could not connect to server.', 'error');
        }
      });

      document.getElementById('admin-logout-btn')?.addEventListener('click', async () => {
        try {
          await fetch('api/logout.php', { method: 'POST' });
        } catch (e) {}
        window.location.href = 'index.php';
      });
    });
  </script>
</body>
</html>
