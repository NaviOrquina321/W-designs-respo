<?php
require_once __DIR__ . '/api/config.php';   // starts the TUTORLINK_ADMIN session
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: admin-login.php');
    exit;
}
$adminName = $_SESSION['name'] ?? 'Admin';
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
    }

    h1, h2, h3, h4 {
      font-family: 'Cinzel', serif;
    }

    .admin-layout {
      display: flex;
      min-height: 100vh;
    }

    /* SvelteForge DashboardPack Sidebar */
    .admin-sidebar {
      width: 260px;
      background: var(--sf-sidebar-bg);
      color: #ffffff;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      border-right: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sidebar-brand {
      padding: 22px 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sidebar-brand img {
      height: 38px;
      width: 38px;
      border-radius: 50%;
      object-fit: contain;
      box-shadow: 0 0 10px rgba(197, 160, 89, 0.3);
    }

    .sidebar-brand span {
      font-family: 'Cinzel', serif;
      font-weight: 700;
      font-size: 1.25rem;
      color: #ffffff;
      letter-spacing: -0.01em;
    }

    .sidebar-menu {
      list-style: none;
      padding: 16px 12px;
      margin: 0;
      flex: 1;
    }

    .sidebar-item {
      margin-bottom: 4px;
    }

    .sidebar-item a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 16px;
      color: var(--sf-sidebar-text);
      text-decoration: none;
      font-size: 0.92rem;
      font-weight: 500;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .sidebar-item a svg {
      width: 18px;
      height: 18px;
      stroke: var(--sf-sidebar-text);
      transition: stroke 0.2s ease;
    }

    .sidebar-item a:hover {
      background: var(--sf-sidebar-active-bg);
      color: #ffffff;
    }

    .sidebar-item a:hover svg {
      stroke: #ffffff;
    }

    .sidebar-item.active a {
      background: var(--sf-sidebar-active-bg);
      color: #ffffff;
      font-weight: 600;
      box-shadow: inset 3px 0 0 var(--sf-accent-gold);
    }

    .sidebar-item.active a svg {
      stroke: var(--sf-accent-gold);
    }

    .sidebar-footer {
      padding: 16px 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 0.82rem;
      color: var(--sf-sidebar-text);
    }

    /* Main Section */
    .admin-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .admin-header {
      background: #ffffff;
      border-bottom: 1px solid var(--sf-card-border);
      padding: 16px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 10;
      box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .admin-header-title {
      font-size: 1.3rem;
      margin: 0;
      color: var(--sf-ink-primary);
    }

    .admin-content {
      padding: 32px;
      flex: 1;
    }

    /* Banners & Cards */
    .alert-banner {
      background: #fef3c7;
      color: #92400e;
      border: 1px solid #fde68a;
      padding: 12px 18px;
      border-radius: 8px;
      margin-bottom: 24px;
      font-weight: 600;
    }

    .error-banner {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fca5a5;
      padding: 12px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
      display: none;
    }

    .success-banner {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
      padding: 12px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
      display: none;
    }

    .admin-tab-view {
      display: none;
    }

    .admin-tab-view.active {
      display: block;
    }

    /* SvelteForge KPI Cards */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 28px;
    }

    .kpi-card {
      background: var(--sf-card-bg);
      border: 1px solid var(--sf-card-border);
      border-radius: 12px;
      padding: 22px 24px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }

    .kpi-label {
      font-size: 0.8rem;
      color: var(--sf-ink-muted);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .kpi-value {
      font-family: 'Cinzel', serif;
      font-size: 1.95rem;
      font-weight: 700;
      color: var(--sf-ink-primary);
      margin-top: 6px;
    }

    .card-panel {
      background: var(--sf-card-bg);
      border: 1px solid var(--sf-card-border);
      border-radius: 12px;
      padding: 26px;
      margin-bottom: 28px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    /* Modern Table Styling */
    .table-responsive {
      width: 100%;
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.88rem;
    }

    .data-table th {
      background: #f8fafc;
      color: var(--sf-ink-primary);
      font-weight: 600;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      padding: 12px 16px;
      border-bottom: 1px solid var(--sf-card-border);
    }

    .data-table td {
      padding: 14px 16px;
      border-bottom: 1px solid var(--sf-card-border);
      color: var(--sf-ink-primary);
      vertical-align: middle;
    }

    .data-table tr:hover td {
      background: #f8fafc;
    }

    /* Modals */
    .modal-backdrop {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(3px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      padding: 20px;
    }

    .modal-backdrop.active {
      display: flex;
    }

    .modal-card {
      background: #ffffff;
      border-radius: 12px;
      max-width: 560px;
      width: 100%;
      padding: 28px;
      max-height: 90vh;
      overflow-y: auto;
      border: 1px solid var(--sf-card-border);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--sf-card-border);
    }

    .print-only {
      display: none;
    }

    @media (max-width: 768px) {
      .admin-layout { flex-direction: column; }
      .admin-sidebar { width: 100%; }
      .sidebar-menu { display: flex; overflow-x: auto; padding: 8px; }
      .sidebar-item a { white-space: nowrap; padding: 8px 12px; }
      .admin-header { padding: 14px 20px; }
      .admin-content { padding: 20px; }
    }

    @media print {
      .admin-sidebar, .admin-header, .btn, .no-print { display: none !important; }
      .admin-main, .admin-content { padding: 0 !important; background: #ffffff !important; }
      .data-table { width: 100% !important; border: 1px solid #000000 !important; }
      .data-table th, .data-table td { border: 1px solid #000000 !important; color: #000000 !important; }
      .print-only { display: block !important; }
    }
  </style>
</head>
<body>

  <div class="admin-layout">
    <!-- Left Sidebar (SvelteForge DashboardPack Style) -->
    <aside class="admin-sidebar">
      <div class="sidebar-brand">
        <img src="assets/logo.png" alt="TutorLink Logo" onerror="this.style.display='none'">
        <span>TutorLink Admin</span>
      </div>
      <ul class="sidebar-menu">
        <li class="sidebar-item active" data-tab="dashboard">
          <a href="#" onclick="switchTab('dashboard', event)">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
          </a>
        </li>
        <li class="sidebar-item" data-tab="students">
          <a href="#" onclick="switchTab('students', event)">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Students
          </a>
        </li>
        <li class="sidebar-item" data-tab="tutors">
          <a href="#" onclick="switchTab('tutors', event)">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
            Tutors
          </a>
        </li>
        <li class="sidebar-item" data-tab="schedules">
          <a href="#" onclick="switchTab('schedules', event)">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Schedules
          </a>
        </li>
        <li class="sidebar-item" data-tab="payments">
          <a href="#" onclick="switchTab('payments', event)">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            Payments
          </a>
        </li>
        <li class="sidebar-item" data-tab="notifications">
          <a href="#" onclick="switchTab('notifications', event)" style="position: relative;">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Notifications
            <span id="admin-notif-badge" class="badge badge-pending" style="display: none; position: absolute; right: 12px; font-size: 0.72rem; padding: 2px 6px;">0</span>
          </a>
        </li>
        <li class="sidebar-item" data-tab="reports">
          <a href="#" onclick="switchTab('reports', event)">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Manage Reports
          </a>
        </li>
      </ul>
      <div class="sidebar-footer">
        <div>Control Room Active</div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">TutorLink v2.0</div>
      </div>
    </aside>

    <!-- Main Section -->
    <div class="admin-main">
      <header class="admin-header">
        <h2 class="admin-header-title" id="tab-header-title">Dashboard Overview</h2>
        <div style="display: flex; align-items: center; gap: 14px;">
          <span>Welcome, <strong><?= htmlspecialchars($adminName) ?></strong></span>
          <button class="btn btn-secondary btn-small" onclick="handleLogout()">Log Out</button>
        </div>
      </header>

      <main class="admin-content">
        <!-- Message Banners -->
        <div id="error-banner" class="error-banner"></div>
        <div id="success-banner" class="success-banner"></div>

        <?php if (!empty($isSqliteFallback)): ?>
          <div class="alert-banner">MySQL is offline. Using SQLite fallback database.</div>
        <?php endif; ?>

        <!-- TAB 1: DASHBOARD -->
        <div id="tab-dashboard" class="admin-tab-view active">
          <!-- Primary Overview KPIs -->
          <div class="kpi-grid">
            <div class="kpi-card">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="kpi-label">Registered Students</div>
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="kpi-students">0</div>
            </div>

            <div class="kpi-card">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="kpi-label">Active Tutors</div>
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="kpi-tutors">0</div>
            </div>

            <div class="kpi-card">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="kpi-label">Tutoring Volume</div>
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="kpi-volume">₱0.00</div>
            </div>

            <div class="kpi-card">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="kpi-label">Platform Revenue (10%)</div>
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #f3e8ff; color: #7c3aed; display: flex; align-items: center; justify-content: center;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
              </div>
              <div class="kpi-value" id="kpi-commission">₱0.00</div>
            </div>
          </div>

          <!-- Activity Summary Count Cards -->
          <div class="kpi-grid">
            <div class="kpi-card">
              <div class="kpi-label">Total Sessions</div>
              <div class="kpi-value" id="kpi-total-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Confirmed Sessions</div>
              <div class="kpi-value" id="kpi-confirmed-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">In Progress Sessions</div>
              <div class="kpi-value" id="kpi-in-progress-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Completed Sessions</div>
              <div class="kpi-value" id="kpi-completed-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Cancelled Sessions</div>
              <div class="kpi-value" id="kpi-cancelled-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Pending Match Requests</div>
              <div class="kpi-value" id="kpi-pending-matches">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Unconfirmed Payments</div>
              <div class="kpi-value" id="kpi-pending-payments">0</div>
            </div>
          </div>

          <!-- Recent Activity Log -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
            <div class="card-panel">
              <h3 style="margin-top: 0; margin-bottom: 14px;">Recent Tutoring Sessions</h3>
              <div class="table-responsive">
                <table class="data-table">
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
              <h3 style="margin-top: 0; margin-bottom: 14px;">Recent Payment Transactions</h3>
              <div class="table-responsive">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Ref No</th>
                      <th>Student</th>
                      <th>Tutor</th>
                      <th>Amount</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="dashboard-recent-payments-body">
                    <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: STUDENTS -->
        <div id="tab-students" class="admin-tab-view">
          <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <h3 style="margin: 0;">Students Management</h3>
              <input type="text" id="stu-search-input" class="form-input" style="max-width: 280px;" placeholder="Search name or email..." oninput="loadStudents()">
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Grade</th>
                    <th>Validation Status</th>
                    <th>Account Status</th>
                    <th>Completed</th>
                    <th>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="students-table-body">
                  <tr><td colspan="9" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 3: TUTORS (Integrated Registered Tutors + Review Matching Results) -->
        <div id="tab-tutors" class="admin-tab-view">
          <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <h3 style="margin: 0;">Registered Tutors Directory</h3>
              <input type="text" id="tut-search-input" class="form-input" style="max-width: 280px;" placeholder="Search name, email, or subject..." oninput="loadTutors()">
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Hourly Rate</th>
                    <th>Rating</th>
                    <th>Reviews</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="tutors-table-body">
                  <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Integrated Matching Requests Section -->
          <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <h3 style="margin: 0;">Review Matching Results & Matching Sessions</h3>
              <select id="mat-status-filter" class="form-select" style="max-width: 220px;" onchange="loadMatches()">
                <option value="">All Statuses</option>
                <option value="Pending Review">Pending Review</option>
                <option value="Approved">Approved</option>
                <option value="Cancelled">Cancelled</option>
                <option value="Reassigned">Reassigned</option>
              </select>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Student</th>
                    <th>Tutor</th>
                    <th>Subject</th>
                    <th>Match Score</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="matches-table-body">
                  <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 4: SCHEDULES (Integrated New Calendar Schedule + Modify Sessions Schedule) -->
        <div id="tab-schedules" class="admin-tab-view">
          <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <h3 style="margin: 0;">New Calendar Schedule</h3>
              <button class="btn btn-primary btn-small" onclick="openAddScheduleModal()">+ Add Available Slot</button>
            </div>
            <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
              <select id="sch-filter-tutor" class="form-select" style="max-width: 220px;" onchange="loadSchedules()">
                <option value="">All Tutors</option>
              </select>
              <select id="sch-filter-status" class="form-select" style="max-width: 200px;" onchange="loadSchedules()">
                <option value="">All Statuses</option>
                <option value="Available">Available</option>
                <option value="Booked">Booked</option>
                <option value="Blocked">Blocked</option>
              </select>
            </div>
            <div class="table-responsive">
              <table class="data-table">
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
          </div>

          <!-- Integrated Modify Sessions Schedule Section -->
          <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <h3 style="margin: 0;">Modify Sessions Schedule</h3>
              <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" id="ses-search-input" class="form-input" style="max-width: 220px;" placeholder="Search student or tutor..." oninput="loadSessions()">
                <select id="ses-status-filter" class="form-select" style="max-width: 180px;" onchange="loadSessions()">
                  <option value="">All Statuses</option>
                  <option value="Confirmed">Confirmed</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
              </div>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Student</th>
                    <th>Tutor</th>
                    <th>Subject</th>
                    <th>Date & Time</th>
                    <th>Rate</th>
                    <th>Commission</th>
                    <th>Total Paid</th>
                    <th>GCash Ref</th>
                    <th>Status</th>
                    <th>Payout</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="sessions-table-body">
                  <tr><td colspan="11" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 5: PAYMENTS (Integrated Payment Records + Confirm Payment Status) -->
        <div id="tab-payments" class="admin-tab-view">
          <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <h3 style="margin: 0;">Payment Transaction Records</h3>
              <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" id="pay-search-input" class="form-input" style="max-width: 220px;" placeholder="Search GCash Ref No..." oninput="loadPayments()">
                <select id="pay-status-filter" class="form-select" style="max-width: 180px;" onchange="loadPayments()">
                  <option value="">All Statuses</option>
                  <option value="Pending">Pending</option>
                  <option value="Confirmed">Confirmed</option>
                </select>
              </div>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Tutor</th>
                    <th>Method</th>
                    <th>GCash Ref #</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payout</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="payments-table-body">
                  <tr><td colspan="10" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 6: NOTIFICATIONS (Send Notifications + View Notifications) -->
        <div id="tab-notifications" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Send Broadcast Notifications</h3>
            <form onsubmit="handleSendNotification(event)">
              <div class="form-group">
                <label>Target Audience</label>
                <select id="notif-target" class="form-select" required>
                  <option value="all">All Users (Students & Tutors)</option>
                  <option value="student">All Students</option>
                  <option value="tutor">All Tutors</option>
                </select>
              </div>
              <div class="form-group">
                <label>Title</label>
                <input type="text" id="notif-title" class="form-input" required placeholder="System Announcement">
              </div>
              <div class="form-group">
                <label>Message Content</label>
                <textarea id="notif-message" class="form-input" rows="3" required placeholder="Type notification content..."></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-small">Send Notification Broadcast</button>
            </form>
          </div>

          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">View Notifications History Log</h3>
            <div id="notifications-log-list">
              <p style="text-align:center; color: var(--sf-ink-muted);">Loading...</p>
            </div>
          </div>
        </div>

        <!-- TAB 7: MANAGE REPORTS -->
        <div id="tab-reports" class="admin-tab-view">
          <div class="card-panel no-print">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <h3 style="margin: 0;">Activity Reports & Filter Presets</h3>
              <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary btn-small" onclick="applyPresetFilter('completed')">View Completed Tutoring</button>
                <button type="button" class="btn btn-secondary btn-small" onclick="applyPresetFilter('weekly')">View Weekly Sessions</button>
                <button type="button" class="btn btn-secondary btn-small" onclick="applyPresetFilter('monthly')">View Monthly Sessions</button>
              </div>
            </div>
            <form onsubmit="handleFilterReports(event)">
              <div class="form-row">
                <div class="form-group">
                  <label>Start Date</label>
                  <input type="date" id="rep-start-date" class="form-input">
                </div>
                <div class="form-group">
                  <label>End Date</label>
                  <input type="date" id="rep-end-date" class="form-input">
                </div>
                <div class="form-group">
                  <label>Tutor</label>
                  <select id="rep-tutor-select" class="form-select">
                    <option value="">All Tutors</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Subject</label>
                  <select id="rep-subject-select" class="form-select">
                    <option value="">All Subjects</option>
                  </select>
                </div>
              </div>
              <div style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary btn-small">Apply Filters</button>
                <button type="button" class="btn btn-secondary btn-small" onclick="resetReportFilters()">Reset Filters</button>
                <button type="button" class="btn btn-outline btn-small" onclick="exportCSV('sessions')">Export Sessions CSV</button>
                <button type="button" class="btn btn-outline btn-small" onclick="exportCSV('payments')">Export Payments CSV</button>
                <button type="button" class="btn btn-primary btn-small" onclick="triggerReportPrint()">Print Report</button>
              </div>
            </form>
          </div>

          <!-- Printable Report Header -->
          <div class="print-only" style="margin-bottom: 20px;">
            <h2 style="margin: 0; font-family: 'Cinzel', serif; color: #000000;">TutorLink System Activity & Financial Report</h2>
            <p style="margin-top: 4px; font-size: 0.9rem; color: #333333;" id="rep-print-timestamp">Generated on: </p>
          </div>

          <div class="kpi-grid">
            <div class="kpi-card">
              <div class="kpi-label">Booked Volume</div>
              <div class="kpi-value" id="rep-booked-volume">₱0.00</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Completed Revenue</div>
              <div class="kpi-value" id="rep-completed-revenue">₱0.00</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Platform Commission (10%)</div>
              <div class="kpi-value" id="rep-commission">₱0.00</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Total Sessions</div>
              <div class="kpi-value" id="rep-total-sessions">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Completed Sessions</div>
              <div class="kpi-value" id="rep-completed-sessions">0</div>
            </div>
          </div>

          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 12px;">Sessions Activity Summary</h3>
            <div id="rep-active-filter" style="margin-bottom: 14px; font-weight: 600; font-size: 0.95rem; color: var(--oxford-navy);">Active Filter: All Time</div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Session ID</th>
                    <th>Student</th>
                    <th>Tutor</th>
                    <th>Subject</th>
                    <th>Date & Slot</th>
                    <th>Total Fee</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="reports-table-body">
                  <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- MODAL: View Profile -->
  <div class="modal-backdrop" id="modal-view-profile">
    <div class="modal-card">
      <div class="modal-header">
        <h3 id="profile-modal-title" style="margin: 0;">User Profile Details</h3>
        <button class="close-btn" onclick="closeModal('modal-view-profile')">&times;</button>
      </div>
      <div id="profile-modal-content"></div>
    </div>
  </div>

  <!-- MODAL: Edit Student -->
  <div class="modal-backdrop" id="modal-edit-student">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin: 0;">Edit Student Profile</h3>
        <button class="close-btn" onclick="closeModal('modal-edit-student')">&times;</button>
      </div>
      <form onsubmit="handleSaveStudent(event)">
        <input type="hidden" id="edit-stu-id" value="">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" id="edit-stu-name" class="form-input" required>
        </div>
        <div class="form-group">
          <label>Grade Level</label>
          <input type="text" id="edit-stu-grade" class="form-input" required>
        </div>
        <div class="form-group">
          <label>Bio / Notes</label>
          <textarea id="edit-stu-bio" class="form-input" rows="3"></textarea>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 16px;">
          <button type="submit" class="btn btn-primary btn-small">Save Changes</button>
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-edit-student')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Edit Tutor -->
  <div class="modal-backdrop" id="modal-edit-tutor">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin: 0;">Edit Tutor Profile</h3>
        <button class="close-btn" onclick="closeModal('modal-edit-tutor')">&times;</button>
      </div>
      <form onsubmit="handleSaveTutor(event)">
        <input type="hidden" id="edit-tut-id" value="">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" id="edit-tut-name" class="form-input" required>
        </div>
        <div class="form-group">
          <label>Hourly Rate (₱)</label>
          <input type="number" id="edit-tut-rate" class="form-input" min="1" max="10000" required>
        </div>
        <div class="form-group">
          <label>Subjects Taught (comma-separated)</label>
          <input type="text" id="edit-tut-subjects" class="form-input" required placeholder="e.g. Mathematics, Calculus">
        </div>
        <div class="form-group">
          <label>Professional Bio</label>
          <textarea id="edit-tut-bio" class="form-input" rows="3"></textarea>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 16px;">
          <button type="submit" class="btn btn-primary btn-small">Save Changes</button>
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-edit-tutor')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Reassign Match -->
  <div class="modal-backdrop" id="modal-reassign-match">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin: 0;">Reassign Match Request</h3>
        <button class="close-btn" onclick="closeModal('modal-reassign-match')">&times;</button>
      </div>
      <form onsubmit="handleConfirmReassign(event)">
        <input type="hidden" id="reassign-match-id" value="">
        <div class="form-group">
          <label>Select New Active Tutor</label>
          <select id="reassign-tutor-select" class="form-select" required></select>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 16px;">
          <button type="submit" class="btn btn-primary btn-small">Reassign Match</button>
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-reassign-match')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Add Schedule Slot -->
  <div class="modal-backdrop" id="modal-add-schedule">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin: 0;">Add Available Schedule Slot</h3>
        <button class="close-btn" onclick="closeModal('modal-add-schedule')">&times;</button>
      </div>
      <form onsubmit="handleSaveScheduleSlot(event)">
        <div class="form-group">
          <label>Tutor</label>
          <select id="sch-add-tutor" class="form-select" required></select>
        </div>
        <div class="form-group">
          <label>Date</label>
          <input type="date" id="sch-add-date" class="form-input" required>
        </div>
        <div class="form-group">
          <label>Time Slot</label>
          <input type="text" id="sch-add-slot" class="form-input" placeholder="e.g. 09:00 AM - 10:00 AM" required>
        </div>
        <div class="form-group">
          <label>Subject</label>
          <select id="sch-add-subject" class="form-select" required></select>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 16px;">
          <button type="submit" class="btn btn-primary btn-small">Create Slot</button>
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-add-schedule')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Reschedule Session -->
  <div class="modal-backdrop" id="modal-reschedule-session">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin: 0;">Reschedule Session</h3>
        <button class="close-btn" onclick="closeModal('modal-reschedule-session')">&times;</button>
      </div>
      <form onsubmit="handleConfirmReschedule(event)">
        <input type="hidden" id="resch-session-id" value="">
        <div class="form-group">
          <label>New Date</label>
          <input type="date" id="resch-date" class="form-input" required>
        </div>
        <div class="form-group">
          <label>New Time Slot</label>
          <input type="text" id="resch-slot" class="form-input" required placeholder="e.g. 02:00 PM">
        </div>
        <div style="display: flex; gap: 10px; margin-top: 16px;">
          <button type="submit" class="btn btn-primary btn-small">Confirm Reschedule</button>
          <button type="button" class="btn btn-secondary btn-small" onclick="closeModal('modal-reschedule-session')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: View Chat Messages -->
  <div class="modal-backdrop" id="modal-view-chat">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin: 0;">Session Workspace Chat (Read-Only)</h3>
        <button class="close-btn" onclick="closeModal('modal-view-chat')">&times;</button>
      </div>
      <div class="chat-box" id="chat-messages-container">
        <p style="text-align:center; color: var(--sf-ink-muted);">Loading chat history...</p>
      </div>
    </div>
  </div>

  <!-- MODAL: View Payment Receipt -->
  <div class="modal-backdrop" id="modal-view-receipt">
    <div class="modal-card" style="max-width: 520px;">
      <div class="modal-header">
        <h3 style="margin: 0;">Official Payment Receipt</h3>
        <button class="close-btn" onclick="closeModal('modal-view-receipt')">&times;</button>
      </div>
      <div class="modal-body">
        <div id="admin-receipt-body" style="margin-bottom: 16px;"></div>
        <div style="display: flex; gap: 10px;">
          <button class="btn btn-secondary full-width" onclick="window.print()">Print / Save as PDF</button>
          <button class="btn btn-primary full-width" onclick="closeModal('modal-view-receipt')">Close Receipt</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentTab = 'dashboard';
    let allStudentsCache = [];
    let allTutorsCache = [];
    let allSubjectsCache = [];
    let allSessionsCache = [];
    let allPaymentsCache = [];
    let activePresetReport = '';

    // Helper: XSS Sanitization
    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str).replace(/[&<>"']/g, function(c) {
        return {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        }[c];
      });
    }

    // Helper: Money Formatting
    function formatMoney(amount) {
      const val = parseFloat(amount || 0);
      return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // API Wrapper
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

    function showError(msg) {
      const el = document.getElementById('error-banner');
      if (!el) return;
      el.textContent = msg;
      el.style.display = 'block';
      setTimeout(() => { el.style.display = 'none'; }, 4000);
    }

    function showSuccess(msg) {
      const el = document.getElementById('success-banner');
      if (!el) return;
      el.textContent = msg;
      el.style.display = 'block';
      setTimeout(() => { el.style.display = 'none'; }, 4000);
    }

    // Tab Navigation
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
        'dashboard': 'Dashboard Overview',
        'students': 'Students Management',
        'tutors': 'Tutors & Matching Control',
        'schedules': 'Schedules & Sessions Management',
        'payments': 'Payment Transactions Log',
        'notifications': 'Broadcast Notifications',
        'reports': 'Manage Reports'
      };

      const titleEl = document.getElementById('tab-header-title');
      if (titleEl) titleEl.textContent = tabTitles[tabName] || 'Admin Dashboard';

      loadTabData(tabName);
    }

    function loadTabData(tabName) {
      if (tabName === 'dashboard') loadDashboard();
      else if (tabName === 'students') loadStudents();
      else if (tabName === 'tutors') { loadTutors(); loadMatches(); }
      else if (tabName === 'schedules') { loadSchedules(); loadSessions(); }
      else if (tabName === 'payments') loadPayments();
      else if (tabName === 'notifications') loadNotifications();
      else if (tabName === 'reports') loadReports();
    }

    // Modal Operations
    function openModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.add('active');
    }

    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) el.classList.remove('active');
    }

    // Logout
    async function handleLogout() {
      try {
        await api('api/logout.php', { method: 'POST' });
      } catch (e) {}
      window.location.href = 'admin-login.php';
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

          document.getElementById('kpi-total-sessions').textContent = d.total_sessions || 0;
          document.getElementById('kpi-confirmed-sessions').textContent = d.confirmed_sessions || 0;
          document.getElementById('kpi-in-progress-sessions').textContent = d.in_progress_sessions || 0;
          document.getElementById('kpi-completed-sessions').textContent = d.completed_sessions || 0;
          document.getElementById('kpi-cancelled-sessions').textContent = d.cancelled_sessions || 0;
          document.getElementById('kpi-pending-matches').textContent = d.pending_matches || 0;
          document.getElementById('kpi-pending-payments').textContent = d.pending_payments || 0;

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

          // Render 5 Recent Payments
          const recPay = d.recent_payments || [];
          const payBody = document.getElementById('dashboard-recent-payments-body');
          if (recPay.length === 0) {
            payBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No payments logged yet.</td></tr>';
          } else {
            payBody.innerHTML = recPay.map(p => `
              <tr>
                <td><code>${escapeHtml(p.refNo)}</code></td>
                <td><strong>${escapeHtml(p.studentName)}</strong></td>
                <td><strong>${escapeHtml(p.tutorName)}</strong></td>
                <td>${formatMoney(p.amount)}</td>
                <td><span class="badge ${p.status === 'Confirmed' ? 'badge-success' : 'badge-pending'}">${escapeHtml(p.status)}</span></td>
              </tr>
            `).join('');
          }
        }
      } catch (e) {}
    }

    // 2. STUDENTS
    async function loadStudents() {
      const tbody = document.getElementById('students-table-body');
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">Loading students...</td></tr>';
      try {
        const json = await api('api/students.php');
        allStudentsCache = json.data || [];
        const term = (document.getElementById('stu-search-input')?.value || '').toLowerCase();

        const filtered = allStudentsCache.filter(s => {
          return (s.name || '').toLowerCase().includes(term) || (s.email || '').toLowerCase().includes(term);
        });

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No matching registered students found.</td></tr>';
          return;
        }

        tbody.innerHTML = filtered.map(s => `
          <tr>
            <td><code>${escapeHtml(s.id)}</code></td>
            <td><strong>${escapeHtml(s.name)}</strong></td>
            <td>${escapeHtml(s.email)}</td>
            <td>${escapeHtml(s.grade)}</td>
            <td><span class="badge ${s.validated ? 'badge-success' : 'badge-pending'}">${s.validated ? 'Validated' : 'Pending'}</span></td>
            <td><span class="badge ${s.deactivated ? 'badge-danger' : 'badge-success'}">${s.deactivated ? 'Deactivated' : 'Active'}</span></td>
            <td>${escapeHtml(s.sessionsCompleted || 0)}</td>
            <td>${escapeHtml(s.createdAt || s.time || '')}</td>
            <td>
              <button class="btn btn-secondary btn-small" onclick="viewStudentProfile('${escapeHtml(s.id)}')">Profile</button>
              <button class="btn btn-primary btn-small" onclick="openEditStudentModal('${escapeHtml(s.id)}')">Edit</button>
              <button class="btn btn-secondary btn-small" onclick="toggleStudentValidate('${escapeHtml(s.id)}', ${!s.validated})">${s.validated ? 'Revoke' : 'Validate'}</button>
              <button class="btn btn-secondary btn-small" onclick="toggleStudentDeactivate('${escapeHtml(s.id)}', ${!s.deactivated})">${s.deactivated ? 'Activate' : 'Deactivate'}</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    function viewStudentProfile(id) {
      const s = allStudentsCache.find(x => x.id === id);
      if (!s) return;
      document.getElementById('profile-modal-title').textContent = 'Student Profile: ' + s.name;
      document.getElementById('profile-modal-content').innerHTML = `
        <div class="card-panel" style="margin-bottom:0;">
          <p><strong>Student ID:</strong> <code>${escapeHtml(s.id)}</code></p>
          <p><strong>Name:</strong> ${escapeHtml(s.name)}</p>
          <p><strong>Email:</strong> ${escapeHtml(s.email)}</p>
          <p><strong>Grade Level:</strong> ${escapeHtml(s.grade)}</p>
          <p><strong>Validation Status:</strong> ${s.validated ? 'Validated' : 'Pending'}</p>
          <p><strong>Subjects Needed:</strong> ${escapeHtml(Array.isArray(s.subjectsNeeded) ? s.subjectsNeeded.join(', ') : (s.subjectsNeeded || 'None'))}</p>
          <p><strong>Bio / Notes:</strong></p>
          <p class="sub-text">${escapeHtml(s.bio || 'None provided.')}</p>
        </div>
      `;
      openModal('modal-view-profile');
    }

    function openEditStudentModal(id) {
      const s = allStudentsCache.find(x => x.id === id);
      if (!s) return;
      document.getElementById('edit-stu-id').value = s.id;
      document.getElementById('edit-stu-name').value = s.name || '';
      document.getElementById('edit-stu-grade').value = s.grade || '';
      document.getElementById('edit-stu-bio').value = s.bio || '';
      openModal('modal-edit-student');
    }

    async function handleSaveStudent(e) {
      e.preventDefault();
      const id = document.getElementById('edit-stu-id').value;
      const name = document.getElementById('edit-stu-name').value.trim();
      const grade = document.getElementById('edit-stu-grade').value.trim();
      const bio = document.getElementById('edit-stu-bio').value.trim();

      try {
        await api('api/students.php', {
          method: 'PUT',
          body: { id, name, grade, bio }
        });
        showSuccess('Student profile updated.');
        closeModal('modal-edit-student');
        loadStudents();
      } catch (e) {}
    }

    async function toggleStudentValidate(id, newVal) {
      try {
        await api('api/students.php', { method: 'PUT', body: { id, validated: newVal } });
        showSuccess('Student validation status updated.');
        loadStudents();
      } catch (e) {}
    }

    async function toggleStudentDeactivate(id, newVal) {
      if (!confirm('Are you sure you want to change this student account status?')) return;
      try {
        await api('api/students.php', { method: 'PUT', body: { id, deactivated: newVal } });
        showSuccess('Student account status updated.');
        loadStudents();
      } catch (e) {}
    }

    // 3. TUTORS & MATCHING
    async function loadTutors() {
      const tbody = document.getElementById('tutors-table-body');
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading tutors...</td></tr>';
      try {
        const json = await api('api/tutors.php');
        allTutorsCache = json.data || [];
        const term = (document.getElementById('tut-search-input')?.value || '').toLowerCase();

        const filtered = allTutorsCache.filter(t => {
          const subStr = Array.isArray(t.subjects) ? t.subjects.join(' ') : (t.subjects || '');
          return (t.name || '').toLowerCase().includes(term) || (t.email || '').toLowerCase().includes(term) || subStr.toLowerCase().includes(term);
        });

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No matching registered tutors found.</td></tr>';
          return;
        }

        tbody.innerHTML = filtered.map(t => {
          return `
            <tr>
              <td><strong>${escapeHtml(t.name)}</strong></td>
              <td>${escapeHtml(t.email || 'N/A')}</td>
              <td>${formatMoney(t.hourlyRate)}</td>
              <td>★ ${escapeHtml(parseFloat(t.rating || 0).toFixed(1))}</td>
              <td>${escapeHtml(t.reviewsCount || 0)}</td>
              <td><span class="badge ${t.deactivated ? 'badge-danger' : 'badge-success'}">${t.deactivated ? 'Deactivated' : 'Active'}</span></td>
              <td>
                <button class="btn btn-secondary btn-small" onclick="viewTutorProfile('${escapeHtml(t.id)}')">Profile</button>
                <button class="btn btn-primary btn-small" onclick="openEditTutorModal('${escapeHtml(t.id)}')">Edit</button>
                <button class="btn btn-secondary btn-small" onclick="toggleTutorDeactivate('${escapeHtml(t.id)}', ${!t.deactivated})">${t.deactivated ? 'Activate' : 'Deactivate'}</button>
              </td>
            </tr>
          `;
        }).join('');
      } catch (e) {}
    }

    function viewTutorProfile(id) {
      const t = allTutorsCache.find(x => x.id === id);
      if (!t) return;
      document.getElementById('profile-modal-title').textContent = 'Tutor Profile: ' + t.name;
      document.getElementById('profile-modal-content').innerHTML = `
        <div class="card-panel" style="margin-bottom:0;">
          <p><strong>Tutor ID:</strong> <code>${escapeHtml(t.id)}</code></p>
          <p><strong>Name:</strong> ${escapeHtml(t.name)}</p>
          <p><strong>Email:</strong> ${escapeHtml(t.email || 'N/A')}</p>
          <p><strong>Hourly Rate:</strong> ${formatMoney(t.hourlyRate)} / hr</p>
          <p><strong>Rating:</strong> ★ ${escapeHtml(parseFloat(t.rating || 0).toFixed(1))} (${escapeHtml(t.reviewsCount || 0)} reviews)</p>
          <p><strong>Subjects Taught:</strong> ${escapeHtml(Array.isArray(t.subjects) ? t.subjects.join(', ') : t.subjects)}</p>
          <p><strong>Learning Styles:</strong> ${escapeHtml(Array.isArray(t.learningStyles) ? t.learningStyles.join(', ') : t.learningStyles)}</p>
          <p><strong>Biography:</strong></p>
          <p class="sub-text">${escapeHtml(t.bio || 'None provided.')}</p>
        </div>
      `;
      openModal('modal-view-profile');
    }

    function openEditTutorModal(id) {
      const t = allTutorsCache.find(x => x.id === id);
      if (!t) return;
      document.getElementById('edit-tut-id').value = t.id;
      document.getElementById('edit-tut-name').value = t.name || '';
      document.getElementById('edit-tut-rate').value = t.hourlyRate || 350;
      document.getElementById('edit-tut-subjects').value = Array.isArray(t.subjects) ? t.subjects.join(', ') : (t.subjects || '');
      document.getElementById('edit-tut-bio').value = t.bio || '';
      openModal('modal-edit-tutor');
    }

    async function handleSaveTutor(e) {
      e.preventDefault();
      const id = document.getElementById('edit-tut-id').value;
      const name = document.getElementById('edit-tut-name').value.trim();
      const hourlyRate = parseInt(document.getElementById('edit-tut-rate').value);
      const subjects = document.getElementById('edit-tut-subjects').value.trim();
      const bio = document.getElementById('edit-tut-bio').value.trim();

      try {
        await api('api/tutors.php', {
          method: 'PUT',
          body: { id, name, hourlyRate, subjects, bio }
        });
        showSuccess('Tutor profile updated.');
        closeModal('modal-edit-tutor');
        loadTutors();
      } catch (e) {}
    }

    async function toggleTutorDeactivate(id, newVal) {
      if (!confirm('Are you sure you want to change this tutor account status?')) return;
      try {
        await api('api/tutors.php', { method: 'PUT', body: { id, deactivated: newVal } });
        showSuccess('Tutor account status updated.');
        loadTutors();
      } catch (e) {}
    }

    // MATCHING RESULTS
    async function loadMatches() {
      const tbody = document.getElementById('matches-table-body');
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Loading match requests...</td></tr>';
      try {
        const json = await api('api/matches.php');
        const matches = json.data || [];
        const filterStatus = (document.getElementById('mat-status-filter')?.value || '');

        const filtered = filterStatus ? matches.filter(m => m.status === filterStatus) : matches;

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No match requests found.</td></tr>';
          return;
        }

        tbody.innerHTML = filtered.map(m => `
          <tr>
            <td><strong>${escapeHtml(m.studentName)}</strong></td>
            <td><strong>${escapeHtml(m.tutorName)}</strong></td>
            <td>${escapeHtml(m.subject)}</td>
            <td><strong style="color:var(--emerald);">${escapeHtml(m.score)}%</strong></td>
            <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : m.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(m.status)}</span></td>
            <td><span class="sub-text">${escapeHtml(m.matchReason || 'Compatibility Match')}</span></td>
            <td>${escapeHtml(m.createdAt || '')}</td>
            <td>
              <button class="btn btn-primary btn-small" onclick="updateMatchStatus('${escapeHtml(m.id)}', 'Approved')">Approve Session</button>
              <button class="btn btn-secondary btn-small" onclick="openReassignMatchModal('${escapeHtml(m.id)}')">Reassign Tutor</button>
              <button class="btn btn-secondary btn-small" onclick="updateMatchStatus('${escapeHtml(m.id)}', 'Cancelled')">Cancel Session</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    async function updateMatchStatus(id, statusVal) {
      try {
        await api('api/matches.php', { method: 'PUT', body: { id, status: statusVal } });
        showSuccess('Match session status set to ' + statusVal);
        loadMatches();
      } catch (e) {}
    }

    async function openReassignMatchModal(matchId) {
      document.getElementById('reassign-match-id').value = matchId;
      if (allTutorsCache.length === 0) {
        try {
          const jsonT = await api('api/tutors.php');
          allTutorsCache = jsonT.data || [];
        } catch (e) {}
      }

      const activeTutors = allTutorsCache.filter(t => !t.deactivated);
      const sel = document.getElementById('reassign-tutor-select');
      sel.innerHTML = activeTutors.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.name)} (${escapeHtml(Array.isArray(t.subjects) ? t.subjects.join(', ') : t.subjects)})</option>`).join('');

      openModal('modal-reassign-match');
    }

    async function handleConfirmReassign(e) {
      e.preventDefault();
      const id = document.getElementById('reassign-match-id').value;
      const tutor_id = document.getElementById('reassign-tutor-select').value;

      try {
        await api('api/matches.php', {
          method: 'PUT',
          body: { id, tutor_id, status: 'Reassigned' }
        });
        showSuccess('Match session reassigned successfully.');
        closeModal('modal-reassign-match');
        loadMatches();
      } catch (e) {}
    }

    // 4. SCHEDULES & SESSIONS
    async function loadSchedules() {
      const tbody = document.getElementById('schedules-table-body');
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading schedules...</td></tr>';
      try {
        const json = await api('api/schedules.php');
        const schedules = json.data || [];

        const tutorFilter = document.getElementById('sch-filter-tutor')?.value || '';
        const statusFilter = document.getElementById('sch-filter-status')?.value || '';

        if (allTutorsCache.length === 0) {
          try {
            const jsonT = await api('api/tutors.php');
            allTutorsCache = jsonT.data || [];
          } catch (e) {}
        }
        const tutorFilterSel = document.getElementById('sch-filter-tutor');
        if (tutorFilterSel && tutorFilterSel.options.length <= 1 && allTutorsCache.length > 0) {
          tutorFilterSel.innerHTML = '<option value="">All Tutors</option>' + allTutorsCache.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.name)}</option>`).join('');
          tutorFilterSel.value = tutorFilter;
        }

        const filtered = schedules.filter(s => {
          const matchTutor = tutorFilter ? s.tutorId === tutorFilter : true;
          const matchStatus = statusFilter ? s.status === statusFilter : true;
          return matchTutor && matchStatus;
        });

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No schedule slots found.</td></tr>';
          return;
        }

        tbody.innerHTML = filtered.map(s => {
          const isBooked = (s.status === 'Booked');
          const isBlocked = (s.status === 'Blocked');
          return `
            <tr>
              <td><code>${escapeHtml(s.id)}</code></td>
              <td><strong>${escapeHtml(s.tutorName)}</strong></td>
              <td>${escapeHtml(s.dateSlot)}</td>
              <td>${escapeHtml(s.subject)}</td>
              <td><span class="badge ${isBooked ? 'badge-success' : isBlocked ? 'badge-danger' : 'badge-pending'}">${escapeHtml(s.status)}</span></td>
              <td>${escapeHtml(s.studentName || '—')}</td>
              <td>
                ${isBooked ? '<span class="sub-text">Booked (Read-Only)</span>' : `
                  <button class="btn btn-secondary btn-small" onclick="updateScheduleStatus('${escapeHtml(s.id)}', '${isBlocked ? 'Available' : 'Blocked'}')">${isBlocked ? 'Unblock' : 'Block'}</button>
                  <button class="btn btn-secondary btn-small" onclick="deleteScheduleSlot('${escapeHtml(s.id)}')">Delete</button>
                `}
              </td>
            </tr>
          `;
        }).join('');
      } catch (e) {}
    }

    async function updateScheduleStatus(id, newStatus) {
      try {
        await api('api/schedules.php', { method: 'PUT', body: { id, status: newStatus } });
        showSuccess('Schedule slot status updated to ' + newStatus);
        loadSchedules();
      } catch (e) {}
    }

    async function deleteScheduleSlot(id) {
      if (!confirm('Are you sure you want to delete this schedule slot?')) return;
      try {
        await api('api/schedules.php', { method: 'DELETE', body: { id } });
        showSuccess('Schedule slot deleted.');
        loadSchedules();
      } catch (e) {}
    }

    async function openAddScheduleModal() {
      if (allTutorsCache.length === 0) {
        try {
          const jsonT = await api('api/tutors.php');
          allTutorsCache = jsonT.data || [];
        } catch (e) {}
      }
      if (allSubjectsCache.length === 0) {
        try {
          const jsonS = await api('api/subjects.php');
          allSubjectsCache = jsonS.data || [];
        } catch (e) {}
      }

      const tutSel = document.getElementById('sch-add-tutor');
      tutSel.innerHTML = allTutorsCache.filter(t => !t.deactivated).map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.name)}</option>`).join('');

      const subSel = document.getElementById('sch-add-subject');
      subSel.innerHTML = allSubjectsCache.map(s => `<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>`).join('');

      openModal('modal-add-schedule');
    }

    async function handleSaveScheduleSlot(e) {
      e.preventDefault();
      const tutor_id = document.getElementById('sch-add-tutor').value;
      const dateVal = document.getElementById('sch-add-date').value;
      const slotVal = document.getElementById('sch-add-slot').value.trim();
      const subject = document.getElementById('sch-add-subject').value;

      if (!dateVal || !slotVal) {
        showError('Please provide both date and time slot.');
        return;
      }

      const date_slot = `${dateVal} ${slotVal}`;

      try {
        await api('api/schedules.php', {
          method: 'POST',
          body: { tutor_id, date_slot, subject }
        });
        showSuccess('New schedule slot created.');
        closeModal('modal-add-schedule');
        loadSchedules();
      } catch (e) {}
    }

    // MODIFY SESSIONS SCHEDULE
    async function loadSessions() {
      const tbody = document.getElementById('sessions-table-body');
      tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Loading scheduled sessions...</td></tr>';
      try {
        const json = await api('api/sessions.php');
        allSessionsCache = json.data || [];

        const term = (document.getElementById('ses-search-input')?.value || '').toLowerCase();
        const filterStatus = document.getElementById('ses-status-filter')?.value || '';

        const filtered = allSessionsCache.filter(s => {
          const matchName = (s.studentName || '').toLowerCase().includes(term) || (s.tutorName || '').toLowerCase().includes(term);
          const matchStatus = filterStatus ? s.status === filterStatus : true;
          return matchName && matchStatus;
        });

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">No sessions found.</td></tr>';
          return;
        }

        tbody.innerHTML = filtered.map(s => {
          let statusText = escapeHtml(s.status);
          if (s.status === 'In Progress' && s.startedAt) {
            const serverNowMs = s.serverNow ? new Date(s.serverNow).getTime() : Date.now();
            const startedAtMs = new Date(s.startedAt).getTime();
            const durationSecs = (parseInt(s.durationMinutes, 10) || 60) * 60;
            const elapsedSecs = Math.floor((serverNowMs - startedAtMs) / 1000);
            const remSecs = durationSecs - elapsedSecs;
            if (remSecs > 0) {
              const m = Math.floor(remSecs / 60);
              const sec = Math.floor(remSecs % 60);
              statusText += ` (${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')} left)`;
            } else {
              const overSec = Math.abs(remSecs);
              const m = Math.floor(overSec / 60);
              const sec = Math.floor(overSec % 60);
              statusText += ` (+${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')})`;
            }
          }

          return `
          <tr>
            <td><strong>${escapeHtml(s.studentName)}</strong></td>
            <td><strong>${escapeHtml(s.tutorName)}</strong></td>
            <td>${escapeHtml(s.subject)}</td>
            <td>${escapeHtml(s.sessionDate)} (${escapeHtml(s.timeSlot)})</td>
            <td>${formatMoney(s.hourlyRate)}</td>
            <td>${formatMoney(s.commissionFee)}</td>
            <td><strong>${formatMoney(s.totalPaid)}</strong></td>
            <td><code>${escapeHtml(s.gcashRef)}</code></td>
            <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${statusText}</span></td>
            <td><span class="badge ${s.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${escapeHtml(s.payoutStatus)}</span></td>
          `;
        }).join('');
            <td>
              <button class="btn btn-secondary btn-small" onclick="openRescheduleModal('${escapeHtml(s.id)}')">Reschedule</button>
              <button class="btn btn-secondary btn-small" onclick="cancelSession('${escapeHtml(s.id)}')">Cancel</button>
              <button class="btn btn-secondary btn-small" onclick="viewSessionChat('${escapeHtml(s.id)}')">Chat</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
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
        showSuccess('Session rescheduled successfully.');
        closeModal('modal-reschedule-session');
        loadSessions();
      } catch (e) {}
    }

    async function cancelSession(id) {
      if (!confirm('Are you sure you want to cancel this scheduled session?')) return;
      try {
        await api('api/sessions.php', {
          method: 'PUT',
          body: { id, status: 'Cancelled' }
        });
        showSuccess('Session cancelled.');
        loadSessions();
      } catch (e) {}
    }

    async function viewSessionChat(sessionId) {
      const container = document.getElementById('chat-messages-container');
      container.innerHTML = '<p style="text-align:center; color: var(--sf-ink-muted);">Loading chat history...</p>';
      openModal('modal-view-chat');

      try {
        const json = await api(`api/chat.php?session_id=${encodeURIComponent(sessionId)}`);
        const msgs = json.data || [];
        if (msgs.length === 0) {
          container.innerHTML = '<p style="text-align:center; color: var(--sf-ink-muted);">No chat messages logged for this session.</p>';
          return;
        }
        container.innerHTML = msgs.map(m => `
          <div class="chat-item">
            <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
              <strong>${escapeHtml(m.senderName)} (${escapeHtml(m.senderRole)})</strong>
              <span style="font-size:0.75rem; color:var(--sf-ink-muted);">${escapeHtml(m.timestamp)}</span>
            </div>
            <div>${escapeHtml(m.message)}</div>
          </div>
        `).join('');
      } catch (e) {}
    }

    // 5. PAYMENTS
    async function loadPayments() {
      const tbody = document.getElementById('payments-table-body');
      tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Loading payment transactions...</td></tr>';
      try {
        const json = await api('api/payments.php');
        allPaymentsCache = json.data || [];

        const term = (document.getElementById('pay-search-input')?.value || '').toLowerCase();
        const filterStatus = document.getElementById('pay-status-filter')?.value || '';

        const filtered = allPaymentsCache.filter(p => {
          const matchRef = (p.refNo || '').toLowerCase().includes(term);
          const matchStatus = filterStatus ? p.status === filterStatus : true;
          return matchRef && matchStatus;
        });

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">No payment transactions found.</td></tr>';
          return;
        }

        tbody.innerHTML = filtered.map(p => `
          <tr>
            <td><code>${escapeHtml(p.id)}</code></td>
            <td><strong>${escapeHtml(p.studentName)}</strong></td>
            <td><strong>${escapeHtml(p.tutorName || 'N/A')}</strong></td>
            <td>${escapeHtml(p.method)}</td>
            <td><code>${escapeHtml(p.refNo)}</code></td>
            <td>${formatMoney(p.amount)}</td>
            <td><span class="badge ${p.status === 'Confirmed' ? 'badge-success' : 'badge-pending'}">${escapeHtml(p.status)}</span></td>
            <td><span class="badge ${p.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${escapeHtml(p.payoutStatus)}</span></td>
            <td>${escapeHtml(p.createdAt || '')}</td>
            <td>
              ${p.status !== 'Confirmed' ? `<button class="btn btn-primary btn-small" onclick="updatePaymentStatus('${escapeHtml(p.id)}', 'Confirmed')">Confirm Payment Status</button>` : ''}
              ${p.payoutStatus !== 'Paid Out' ? `<button class="btn btn-secondary btn-small" onclick="processPayout('${escapeHtml(p.id)}')">Process Payout</button>` : ''}
              <button class="btn btn-secondary btn-small" onclick="openAdminReceiptModal('${escapeHtml(p.refNo)}')">View Receipt</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    async function updatePaymentStatus(id, newStatus) {
      try {
        await api('api/payments.php', { method: 'PUT', body: { id, status: newStatus } });
        showSuccess('Payment status confirmed successfully.');
        loadPayments();
      } catch (e) {}
    }

    async function processPayout(id) {
      try {
        await api('api/payments.php', { method: 'PUT', body: { id, payout_status: 'Paid Out' } });
        showSuccess('Payout status set to Paid Out.');
        loadPayments();
      } catch (e) {}
    }

    async function openAdminReceiptModal(refNo) {
      if (allSessionsCache.length === 0) {
        try {
          const jsonS = await api('api/sessions.php');
          allSessionsCache = jsonS.data || [];
        } catch (e) {}
      }

      const matchSes = allSessionsCache.find(s => s.gcashRef === refNo);
      const receiptBody = document.getElementById('admin-receipt-body');

      if (!matchSes) {
        receiptBody.innerHTML = `<p class="sub-text center-text">Receipt details not found for GCash Ref: <code>${escapeHtml(refNo)}</code>.</p>`;
      } else {
        const hourlyRate = parseFloat(matchSes.hourlyRate || matchSes.hourly_rate || 0);
        const commissionFee = parseFloat(matchSes.commissionFee || matchSes.commission_fee || (hourlyRate * 0.10));
        const totalPaid = parseFloat(matchSes.totalPaid || matchSes.total_paid || (hourlyRate + commissionFee));
        const dateFormatted = matchSes.createdAt ? new Date(matchSes.createdAt).toLocaleString('en-US', { timeZone: 'Asia/Manila', dateStyle: 'medium', timeStyle: 'short' }) : 'Just now';

        receiptBody.innerHTML = `
          <div class="receipt-preview-box receipt-print-area text-left" style="background: #ffffff; border: 1px solid var(--line); border-radius: 8px; padding: 20px; color: #0b132a;">
            <div class="receipt-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--oxford-navy); padding-bottom: 12px; margin-bottom: 16px;">
              <div>
                <strong style="font-size: 1.15rem; color: var(--oxford-navy); font-family: 'Cinzel', serif;">TutorLink - Payment Receipt</strong>
                <div style="font-size: 0.85rem; color: var(--ink-soft); margin-top: 2px;">Paid via GCash (Demo)</div>
              </div>
              <span class="badge badge-success" style="font-weight: 600;">Paid</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem; margin-bottom: 14px;">
              <div><span style="color: var(--sf-ink-muted);">Reference No.:</span> <strong style="font-family: monospace;">${escapeHtml(matchSes.gcashRef)}</strong></div>
              <div><span style="color: var(--sf-ink-muted);">Date & Time Paid:</span> <span>${escapeHtml(dateFormatted)}</span></div>
              <div><span style="color: var(--sf-ink-muted);">Payment Method:</span> <strong>GCash (Demo)</strong></div>
            </div>

            <div style="background: var(--panel); border: 1px solid var(--line); border-radius: 6px; padding: 12px; font-size: 0.9rem; margin-bottom: 14px;">
              <div style="margin-bottom: 4px;"><strong>Student Name:</strong> ${escapeHtml(matchSes.studentName)}</div>
              <div style="margin-bottom: 4px;"><strong>Tutor Name:</strong> ${escapeHtml(matchSes.tutorName)}</div>
              <div style="margin-bottom: 4px;"><strong>Subject:</strong> ${escapeHtml(matchSes.subject)}</div>
              <div><strong>Schedule:</strong> ${escapeHtml(matchSes.sessionDate)} (${escapeHtml(matchSes.timeSlot)})</div>
            </div>

            <div style="border-top: 1px dashed var(--line); padding-top: 10px; font-size: 0.9rem;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span>Tutoring Fee:</span> <span>${formatMoney(hourlyRate)}</span></div>
              <div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span>Service Fee (10%):</span> <span>${formatMoney(commissionFee)}</span></div>
              <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 700; color: var(--oxford-navy); border-top: 1px solid var(--line); padding-top: 8px; margin-top: 6px;">
                <span>Total Paid:</span>
                <span style="color: var(--emerald);">${formatMoney(totalPaid)}</span>
              </div>
            </div>

            <div style="margin-top: 16px; font-size: 0.78rem; color: var(--sf-ink-muted); text-align: center; border-top: 1px solid var(--line); padding-top: 10px;">
              This is a system-generated receipt. Demo payment - no real money was charged.
            </div>
          </div>
        `;
      }

      openModal('modal-view-receipt');
    }

    // 6. NOTIFICATIONS
    async function loadNotifications() {
      const container = document.getElementById('notifications-log-list');
      container.innerHTML = '<p style="text-align:center; color: var(--sf-ink-muted);">Loading history log...</p>';
      try {
        const json = await api('api/notifications.php');
        const notifs = json.data || [];
        if (notifs.length === 0) {
          container.innerHTML = '<p style="text-align:center; color: var(--sf-ink-muted);">No broadcast or system notifications logged.</p>';
          return;
        }
        container.innerHTML = notifs.map(n => `
          <div class="card-panel" style="padding: 14px; margin-bottom: 10px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap: wrap; gap: 8px;">
              <strong>${escapeHtml(n.title)}</strong>
              <div style="display:flex; gap: 8px; align-items:center;">
                <span class="badge badge-match">Target: ${escapeHtml(n.targetRole || n.target)}</span>
                <span class="badge ${n.isRead ? 'badge-success' : 'badge-pending'}">${n.isRead ? 'Read' : 'Unread'}</span>
              </div>
            </div>
            <p style="margin-top: 6px; font-size: 0.92rem;">${escapeHtml(n.message)}</p>
            <div style="font-size: 0.78rem; color: var(--sf-ink-muted); text-align: right;">${escapeHtml(n.time || n.createdAt || '')}</div>
          </div>
        `).join('');
      } catch (e) {}
    }

    async function handleSendNotification(e) {
      e.preventDefault();
      const target_role = document.getElementById('notif-target').value;
      const title = document.getElementById('notif-title').value.trim();
      const message = document.getElementById('notif-message').value.trim();

      const targetLabelMap = {
        'all': 'All Users',
        'student': 'All Students',
        'tutor': 'All Tutors'
      };

      try {
        await api('api/notifications.php', {
          method: 'POST',
          body: { target_role, target: targetLabelMap[target_role] || 'All Users', title, message }
        });
        showSuccess('Notification broadcast sent successfully.');
        document.getElementById('notif-title').value = '';
        document.getElementById('notif-message').value = '';
        loadNotifications();
      } catch (e) {}
    }

    // 7. MANAGE REPORTS
    async function loadReports() {
      if (allTutorsCache.length === 0) {
        try {
          const jsonT = await api('api/tutors.php');
          allTutorsCache = jsonT.data || [];
        } catch (e) {}
      }
      if (allSubjectsCache.length === 0) {
        try {
          const jsonSub = await api('api/subjects.php');
          allSubjectsCache = jsonSub.data || [];
        } catch (e) {}
      }

      const tutorSelect = document.getElementById('rep-tutor-select');
      if (tutorSelect && allTutorsCache.length > 0) {
        const cur = tutorSelect.value;
        tutorSelect.innerHTML = '<option value="">All Tutors</option>' + allTutorsCache.map(t => `
          <option value="${escapeHtml(t.id)}" ${t.id === cur ? 'selected' : ''}>${escapeHtml(t.name)}</option>
        `).join('');
      }

      const subjectSelect = document.getElementById('rep-subject-select');
      if (subjectSelect && allSubjectsCache.length > 0) {
        const curS = subjectSelect.value;
        subjectSelect.innerHTML = '<option value="">All Subjects</option>' + allSubjectsCache.map(s => `
          <option value="${escapeHtml(s.name)}" ${s.name === curS ? 'selected' : ''}>${escapeHtml(s.name)}</option>
        `).join('');
      }

      fetchReportsData();
    }

    function applyPresetFilter(type) {
      activePresetReport = type;
      const today = new Date();
      const formatDate = d => d.toISOString().split('T')[0];

      if (type === 'weekly') {
        const pastWeek = new Date(today);
        pastWeek.setDate(today.getDate() - 7);
        document.getElementById('rep-start-date').value = formatDate(pastWeek);
        document.getElementById('rep-end-date').value = formatDate(today);
      } else if (type === 'monthly') {
        const pastMonth = new Date(today);
        pastMonth.setDate(today.getDate() - 30);
        document.getElementById('rep-start-date').value = formatDate(pastMonth);
        document.getElementById('rep-end-date').value = formatDate(today);
      } else if (type === 'completed') {
        document.getElementById('rep-start-date').value = '';
        document.getElementById('rep-end-date').value = '';
      }
      fetchReportsData();
    }

    async function fetchReportsData() {
      const startDate = document.getElementById('rep-start-date').value;
      const endDate = document.getElementById('rep-end-date').value;
      const tutorId = document.getElementById('rep-tutor-select').value;
      const subject = document.getElementById('rep-subject-select').value;

      const params = new URLSearchParams();
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);
      if (tutorId) params.append('tutor_id', tutorId);
      if (subject) params.append('subject', subject);

      const tbody = document.getElementById('reports-table-body');
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading report sessions...</td></tr>';

      try {
        const json = await api('api/reports.php?' + params.toString());
        const sum = json.summary || {};
        let sessions = json.sessions || [];

        if (activePresetReport === 'completed') {
          sessions = sessions.filter(s => s.status === 'Completed');
        }

        document.getElementById('rep-booked-volume').textContent = formatMoney(sum.booked_volume || sum.total_revenue);
        document.getElementById('rep-completed-revenue').textContent = formatMoney(sum.completed_revenue || 0);
        document.getElementById('rep-commission').textContent = formatMoney(sum.total_commission);
        document.getElementById('rep-total-sessions').textContent = sessions.length;
        document.getElementById('rep-completed-sessions').textContent = sum.completed_sessions || sessions.filter(s => s.status === 'Completed').length;

        // Update Active Filter summary
        const filterParts = [];
        if (activePresetReport === 'completed') {
          filterParts.push('Completed Tutoring Preset');
        } else if (activePresetReport === 'weekly') {
          filterParts.push('Weekly Sessions Preset');
        } else if (activePresetReport === 'monthly') {
          filterParts.push('Monthly Sessions Preset');
        }

        if (startDate && endDate) {
          filterParts.push(`${startDate} - ${endDate}`);
        } else if (startDate) {
          filterParts.push(`From ${startDate}`);
        } else if (endDate) {
          filterParts.push(`Until ${endDate}`);
        } else if (!activePresetReport) {
          filterParts.push('All Time');
        }

        if (tutorId) {
          const tutorSel = document.getElementById('rep-tutor-select');
          const tutorText = (tutorSel && tutorSel.options[tutorSel.selectedIndex]) ? tutorSel.options[tutorSel.selectedIndex].text : tutorId;
          filterParts.push(`Tutor: ${tutorText}`);
        }
        if (subject) {
          filterParts.push(`Subject: ${subject}`);
        }

        const filterSummaryEl = document.getElementById('rep-active-filter');
        if (filterSummaryEl) {
          filterSummaryEl.textContent = 'Active Filter: ' + filterParts.join(' | ');
        }

        if (sessions.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 24px; color: var(--sf-ink-muted);"><div style="font-size: 1.1rem; font-weight: 600;">No sessions in this period</div><div style="font-size: 0.88rem; margin-top: 4px;">Try adjusting your date range or filter criteria.</div></td></tr>';
          return;
        }

        tbody.innerHTML = sessions.map(s => `
          <tr>
            <td><code>${escapeHtml(s.id)}</code></td>
            <td><strong>${escapeHtml(s.studentName)}</strong></td>
            <td><strong>${escapeHtml(s.tutorName)}</strong></td>
            <td>${escapeHtml(s.subject)}</td>
            <td>${escapeHtml(s.date || s.sessionDate)} (${escapeHtml(s.timeSlot)})</td>
            <td>${formatMoney(s.totalPaid)}</td>
            <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(s.status)}</span></td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    function handleFilterReports(e) {
      e.preventDefault();
      fetchReportsData();
    }

    function resetReportFilters() {
      activePresetReport = '';
      document.getElementById('rep-start-date').value = '';
      document.getElementById('rep-end-date').value = '';
      document.getElementById('rep-tutor-select').value = '';
      document.getElementById('rep-subject-select').value = '';
      fetchReportsData();
    }

    function exportCSV(type) {
      const startDate = document.getElementById('rep-start-date').value;
      const endDate = document.getElementById('rep-end-date').value;
      const tutorId = document.getElementById('rep-tutor-select').value;
      const subject = document.getElementById('rep-subject-select').value;

      const params = new URLSearchParams();
      params.append('export', type === 'sessions' ? 'sessions_csv' : 'payments_csv');
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);
      if (tutorId) params.append('tutor_id', tutorId);
      if (subject) params.append('subject', subject);

      window.location.href = 'api/reports.php?' + params.toString();
    }

    function triggerReportPrint() {
      const printTs = document.getElementById('rep-print-timestamp');
      if (printTs) {
        printTs.textContent = 'Generated on: ' + new Date().toLocaleString('en-US', { timeZone: 'Asia/Manila' });
      }
      window.print();
    }

    // Real-Time Notification SSE Stream
    let adminNotifSSE = null;

    function initAdminSSE() {
      if (adminNotifSSE) {
        adminNotifSSE.close();
        adminNotifSSE = null;
      }

      try {
        adminNotifSSE = new EventSource('api/notifications_stream.php');

        adminNotifSSE.addEventListener('notification', (e) => {
          try {
            const payload = JSON.parse(e.data);
            if (payload) {
              const count = payload.unreadCount || 0;
              const badge = document.getElementById('admin-notif-badge');
              if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'inline-block' : 'none';
              }

              if (currentTab === 'notifications') {
                loadNotifications();
              }

              if (Array.isArray(payload.notifications)) {
                payload.notifications.forEach(nn => {
                  if (!nn.read && (nn.priority === 'high' || nn.priority === 'urgent')) {
                    showSuccess('🔔 High Priority Notice: ' + nn.title + ' - ' + nn.message);
                  }
                });
              }
            }
          } catch (err) {}
        });
      } catch (err) {}
    }

    // Initialize Dashboard & SSE Stream on DOM Load
    document.addEventListener('DOMContentLoaded', () => {
      loadDashboard();
      initAdminSSE();
    });
  </script>
</body>
</html>
