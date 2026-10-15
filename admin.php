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
  <title>TutorLink Admin Dashboard</title>
  <link rel="icon" type="image/png" href="assets/favicon.png">
  <link rel="stylesheet" href="styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: var(--panel, #f8fafc);
      color: var(--ink, #0b132a);
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
    .admin-sidebar {
      width: 250px;
      background: var(--oxford-navy, #0b132a);
      color: #ffffff;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      border-right: 1px solid rgba(197, 160, 89, 0.25);
    }
    .sidebar-brand {
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .sidebar-brand img {
      height: 36px;
      width: 36px;
      border-radius: 50%;
      object-fit: contain;
    }
    .sidebar-brand span {
      font-family: 'Cinzel', serif;
      font-weight: 700;
      font-size: 1.2rem;
      color: #ffffff;
    }
    .sidebar-menu {
      list-style: none;
      padding: 12px 0;
      margin: 0;
      flex: 1;
    }
    .sidebar-item a {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: #cbd5e1;
      text-decoration: none;
      font-size: 0.92rem;
      font-weight: 500;
      transition: all 0.15s ease;
      border-left: 3px solid transparent;
    }
    .sidebar-item a:hover, .sidebar-item.active a {
      background: rgba(255, 255, 255, 0.08);
      color: var(--warm-gold, #c5a059);
      border-left-color: var(--warm-gold, #c5a059);
    }
    .admin-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
    }
    .admin-header {
      background: #ffffff;
      border-bottom: 1px solid var(--line, #e2e8f0);
      padding: 16px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .admin-header-title {
      font-size: 1.25rem;
      margin: 0;
      color: var(--oxford-navy, #0b132a);
    }
    .admin-content {
      padding: 28px;
      flex: 1;
    }
    .alert-banner {
      background: #fef3c7;
      color: #92400e;
      border: 1px solid #fde68a;
      padding: 12px 18px;
      border-radius: 6px;
      margin-bottom: 24px;
      font-weight: 600;
    }
    .error-banner {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fca5a5;
      padding: 12px 18px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-weight: 500;
      display: none;
    }
    .success-banner {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
      padding: 12px 18px;
      border-radius: 6px;
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
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 28px;
    }
    .kpi-card {
      background: #ffffff;
      border: 1px solid var(--line, #e2e8f0);
      border-radius: 6px;
      padding: 22px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .kpi-label {
      font-size: 0.85rem;
      color: var(--muted, #64748b);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .kpi-value {
      font-family: 'Cinzel', serif;
      font-size: 2.1rem;
      font-weight: 700;
      color: var(--oxford-navy, #0b132a);
      margin-top: 6px;
    }
    .card-panel {
      background: #ffffff;
      border: 1px solid var(--line, #e2e8f0);
      border-radius: 6px;
      padding: 24px;
      margin-bottom: 28px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .form-row {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
    }
    .form-row .form-group {
      flex: 1;
      min-width: 200px;
    }
    .modal-backdrop {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(11, 19, 42, 0.7);
      backdrop-filter: blur(2px);
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
      border-radius: 6px;
      max-width: 550px;
      width: 100%;
      padding: 28px;
      max-height: 90vh;
      overflow-y: auto;
      border: 1px solid var(--line, #e2e8f0);
      box-shadow: 0 16px 40px rgba(0,0,0,0.18);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--line, #e2e8f0);
    }
    .chat-box {
      max-height: 300px;
      overflow-y: auto;
      background: var(--panel, #f8fafc);
      padding: 14px;
      border-radius: 4px;
      border: 1px solid var(--line, #e2e8f0);
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .chat-item {
      padding: 8px 12px;
      border-radius: 4px;
      background: #ffffff;
      border: 1px solid var(--line, #e2e8f0);
      font-size: 0.9rem;
    }
    @media (max-width: 768px) {
      .admin-layout { flex-direction: column; }
      .admin-sidebar { width: 100%; border-right: none; border-bottom: 1px solid rgba(197, 160, 89, 0.25); }
      .sidebar-menu { display: flex; overflow-x: auto; padding: 6px; }
      .sidebar-item a { padding: 8px 14px; white-space: nowrap; border-left: none; border-bottom: 3px solid transparent; }
      .sidebar-item.active a { border-bottom-color: var(--warm-gold, #c5a059); }
      .admin-header { padding: 14px 18px; }
      .admin-content { padding: 18px; }
    }
    @media print {
      .admin-sidebar, .admin-header, .btn, .no-print { display: none !important; }
      .admin-main, .admin-content { padding: 0 !important; background: #ffffff !important; }
      .data-table { width: 100% !important; border: 1px solid #000000 !important; }
      .data-table th, .data-table td { border: 1px solid #000000 !important; color: #000000 !important; }
    }
  </style>
</head>
<body>

  <div class="admin-layout">
    <!-- Left Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-brand">
        <img src="assets/logo.png" alt="TutorLink Logo" onerror="this.style.display='none'">
        <span>TutorLink Admin</span>
      </div>
      <ul class="sidebar-menu">
        <li class="sidebar-item active" data-tab="dashboard"><a href="#" onclick="switchTab('dashboard', event)">Dashboard</a></li>
        <li class="sidebar-item" data-tab="students"><a href="#" onclick="switchTab('students', event)">Students</a></li>
        <li class="sidebar-item" data-tab="tutors"><a href="#" onclick="switchTab('tutors', event)">Tutors</a></li>
        <li class="sidebar-item" data-tab="matches"><a href="#" onclick="switchTab('matches', event)">Matches</a></li>
        <li class="sidebar-item" data-tab="sessions"><a href="#" onclick="switchTab('sessions', event)">Sessions</a></li>
        <li class="sidebar-item" data-tab="payments"><a href="#" onclick="switchTab('payments', event)">Payments</a></li>
        <li class="sidebar-item" data-tab="subjects"><a href="#" onclick="switchTab('subjects', event)">Subjects</a></li>
        <li class="sidebar-item" data-tab="notifications"><a href="#" onclick="switchTab('notifications', event)">Notifications</a></li>
        <li class="sidebar-item" data-tab="reports"><a href="#" onclick="switchTab('reports', event)">Reports</a></li>
      </ul>
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
        <!-- Error & Success Message Banners -->
        <div id="error-banner" class="error-banner"></div>
        <div id="success-banner" class="success-banner"></div>

        <?php if (!empty($isSqliteFallback)): ?>
          <div class="alert-banner">MySQL is offline. Using SQLite fallback database.</div>
        <?php endif; ?>

        <!-- TAB 1: DASHBOARD -->
        <div id="tab-dashboard" class="admin-tab-view active">
          <div class="kpi-grid">
            <div class="kpi-card">
              <div class="kpi-label">Total Registered Students</div>
              <div class="kpi-value" id="kpi-students">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Verified Approved Tutors</div>
              <div class="kpi-value" id="kpi-tutors">0</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Total Transaction Volume</div>
              <div class="kpi-value" id="kpi-volume">₱0.00</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Platform Net Revenue (10%)</div>
              <div class="kpi-value" id="kpi-commission">₱0.00</div>
            </div>
          </div>
        </div>

        <!-- TAB 2: STUDENTS -->
        <div id="tab-students" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Students Management</h3>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Grade</th>
                    <th>Validation</th>
                    <th>Status</th>
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

        <!-- TAB 3: TUTORS -->
        <div id="tab-tutors" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Tutors Management & Verification</h3>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Hourly Rate</th>
                    <th>Rating</th>
                    <th>Reviews</th>
                    <th>Diploma</th>
                    <th>TOR</th>
                    <th>Gov ID</th>
                    <th>Approval</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="tutors-table-body">
                  <tr><td colspan="11" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 4: MATCHES -->
        <div id="tab-matches" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Tutor Match Requests</h3>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Student</th>
                    <th>Tutor</th>
                    <th>Subject</th>
                    <th>Score</th>
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

        <!-- TAB 5: SESSIONS -->
        <div id="tab-sessions" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Scheduled Tutoring Sessions</h3>
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

        <!-- TAB 6: PAYMENTS -->
        <div id="tab-payments" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Payment Transactions Log</h3>
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

        <!-- TAB 7: SUBJECTS -->
        <div id="tab-subjects" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;" id="subject-form-title">Add New Subject</h3>
            <form id="subject-form" onsubmit="handleSaveSubject(event)">
              <input type="hidden" id="subject-id" value="">
              <div class="form-row">
                <div class="form-group">
                  <label>Subject Name</label>
                  <input type="text" id="subject-name" class="form-input" required placeholder="e.g. Linear Algebra">
                </div>
                <div class="form-group">
                  <label>Category</label>
                  <select id="subject-category" class="form-select" required>
                    <option value="STEM">STEM</option>
                    <option value="Technology">Technology</option>
                    <option value="Humanities">Humanities</option>
                  </select>
                </div>
              </div>
              <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary btn-small">Save Subject</button>
                <button type="button" class="btn btn-secondary btn-small" onclick="resetSubjectForm()">Cancel / Reset</button>
              </div>
            </form>
          </div>

          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Subjects Catalog</h3>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="subjects-table-body">
                  <tr><td colspan="4" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 8: NOTIFICATIONS -->
        <div id="tab-notifications" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Send Broadcast Announcement</h3>
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
            <h3 style="margin-top: 0; margin-bottom: 16px;">Notification History Log</h3>
            <div id="notifications-log-list">
              <p style="text-align:center; color: var(--muted);">Loading...</p>
            </div>
          </div>
        </div>

        <!-- TAB 9: REPORTS -->
        <div id="tab-reports" class="admin-tab-view">
          <div class="card-panel">
            <h3 style="margin-top: 0; margin-bottom: 16px;">Activity Reports & Filter</h3>
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
              <div style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary btn-small">Apply Filters</button>
                <button type="button" class="btn btn-secondary btn-small" onclick="resetReportFilters()">Reset Filters</button>
                <button type="button" class="btn btn-outline btn-small" onclick="exportCSV('sessions')">Export Sessions CSV</button>
                <button type="button" class="btn btn-outline btn-small" onclick="exportCSV('payments')">Export Payments CSV</button>
              </div>
            </form>
          </div>

          <div class="kpi-grid">
            <div class="kpi-card">
              <div class="kpi-label">Filtered Revenue</div>
              <div class="kpi-value" id="rep-revenue">₱0.00</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Net Commission (10%)</div>
              <div class="kpi-value" id="rep-commission">₱0.00</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">Completed Sessions</div>
              <div class="kpi-value" id="rep-completed">0</div>
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
        <p style="text-align:center; color: var(--muted);">Loading chat history...</p>
      </div>
    </div>
  </div>

  <script>
    let currentTab = 'dashboard';
    let allTutorsCache = [];
    let allSubjectsCache = [];

    // Helper: XSS Sanitization
    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str).replace(/[&<>"']/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    // Helper: Currency Formatter
    function formatMoney(amount) {
      const val = parseFloat(amount || 0);
      return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Banner Messages
    function showError(msg) {
      const errEl = document.getElementById('error-banner');
      if (errEl) {
        errEl.textContent = msg;
        errEl.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    function clearError() {
      const errEl = document.getElementById('error-banner');
      if (errEl) errEl.style.display = 'none';
    }

    function showSuccess(msg) {
      const succEl = document.getElementById('success-banner');
      if (succEl) {
        succEl.textContent = msg;
        succEl.style.display = 'block';
        setTimeout(() => { succEl.style.display = 'none'; }, 4000);
      }
    }

    // Helper: Master API Fetcher
    async function api(url, options = {}) {
      clearError();
      options.credentials = 'same-origin';
      if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
        options.headers = Object.assign({ 'Content-Type': 'application/json' }, options.headers || {});
        options.body = JSON.stringify(options.body);
      }

      let res;
      try {
        res = await fetch(url, options);
      } catch (err) {
        showError('Network error: Could not connect to server.');
        throw err;
      }

      if (res.status === 401) {
        window.location.href = 'admin-login.php';
        return;
      }

      let text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch (e) {
        showError('Server error: Invalid JSON response received.');
        throw new Error('Invalid JSON response');
      }

      if (!res.ok || json.status === 'error') {
        const msg = json.message || 'Request failed';
        showError(msg);
        throw new Error(msg);
      }

      return json;
    }

    // Tab Navigation Switcher
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
        'tutors': 'Tutors Management & Verification',
        'matches': 'Tutor Match Requests',
        'sessions': 'Scheduled Tutoring Sessions',
        'payments': 'Payment Transactions Log',
        'subjects': 'Subjects Catalog',
        'notifications': 'Broadcast Notifications',
        'reports': 'Reports & Analytics'
      };

      const titleEl = document.getElementById('tab-header-title');
      if (titleEl) titleEl.textContent = tabTitles[tabName] || 'Admin Dashboard';

      loadTabData(tabName);
    }

    function loadTabData(tabName) {
      if (tabName === 'dashboard') loadDashboard();
      else if (tabName === 'students') loadStudents();
      else if (tabName === 'tutors') loadTutors();
      else if (tabName === 'matches') loadMatches();
      else if (tabName === 'sessions') loadSessions();
      else if (tabName === 'payments') loadPayments();
      else if (tabName === 'subjects') loadSubjects();
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
          document.getElementById('kpi-students').textContent = json.data.total_students || 0;
          document.getElementById('kpi-tutors').textContent = json.data.total_tutors || 0;
          document.getElementById('kpi-volume').textContent = formatMoney(json.data.total_volume);
          document.getElementById('kpi-commission').textContent = formatMoney(json.data.net_commission);
        }
      } catch (e) {}
    }

    // 2. STUDENTS
    async function loadStudents() {
      const tbody = document.getElementById('students-table-body');
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">Loading students...</td></tr>';
      try {
        const json = await api('api/students.php');
        const students = json.data || [];
        if (students.length === 0) {
          tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No registered students found.</td></tr>';
          return;
        }
        tbody.innerHTML = students.map(s => `
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
              <button class="btn btn-secondary btn-small" onclick="viewStudentProfile('${escapeHtml(s.id)}')">View Profile</button>
              <button class="btn btn-secondary btn-small" onclick="toggleStudentValidate('${escapeHtml(s.id)}', ${!s.validated})">${s.validated ? 'Revoke' : 'Validate'}</button>
              <button class="btn btn-secondary btn-small" onclick="toggleStudentDeactivate('${escapeHtml(s.id)}', ${!s.deactivated})">${s.deactivated ? 'Activate' : 'Deactivate'}</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    async function viewStudentProfile(id) {
      try {
        const json = await api('api/students.php');
        const s = (json.data || []).find(x => x.id === id);
        if (!s) return;
        document.getElementById('profile-modal-title').textContent = 'Student Profile: ' + s.name;
        document.getElementById('profile-modal-content').innerHTML = `
          <div class="card-panel" style="margin-bottom:0;">
            <p><strong>Student ID:</strong> <code>${escapeHtml(s.id)}</code></p>
            <p><strong>Name:</strong> ${escapeHtml(s.name)}</p>
            <p><strong>Email:</strong> ${escapeHtml(s.email)}</p>
            <p><strong>Grade Level:</strong> ${escapeHtml(s.grade)}</p>
            <p><strong>Subjects Needed:</strong> ${escapeHtml(Array.isArray(s.subjectsNeeded) ? s.subjectsNeeded.join(', ') : s.subjectsNeeded)}</p>
            <p><strong>Bio / Notes:</strong></p>
            <p class="sub-text">${escapeHtml(s.bio || 'None provided.')}</p>
          </div>
        `;
        openModal('modal-view-profile');
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

    // 3. TUTORS
    async function loadTutors() {
      const tbody = document.getElementById('tutors-table-body');
      tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Loading tutors...</td></tr>';
      try {
        const json = await api('api/tutors.php');
        allTutorsCache = json.data || [];
        if (allTutorsCache.length === 0) {
          tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">No tutors registered found.</td></tr>';
          return;
        }

        tbody.innerHTML = allTutorsCache.map(t => {
          const allVerified = (t.diplomaStatus === 'Verified' && t.torStatus === 'Verified' && t.idStatus === 'Verified');
          return `
            <tr>
              <td><strong>${escapeHtml(t.name)}</strong></td>
              <td>${escapeHtml(t.email || 'N/A')}</td>
              <td>₱${escapeHtml(t.hourlyRate)}</td>
              <td>★ ${escapeHtml(parseFloat(t.rating || 0).toFixed(1))}</td>
              <td>${escapeHtml(t.reviewsCount || 0)}</td>
              <td>
                <select class="form-select" onchange="updateTutorDoc('${escapeHtml(t.id)}', 'diploma_status', this.value)">
                  <option value="Pending" ${t.diplomaStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                  <option value="Verified" ${t.diplomaStatus === 'Verified' ? 'selected' : ''}>Verified</option>
                  <option value="Rejected" ${t.diplomaStatus === 'Rejected' ? 'selected' : ''}>Rejected</option>
                </select>
              </td>
              <td>
                <select class="form-select" onchange="updateTutorDoc('${escapeHtml(t.id)}', 'tor_status', this.value)">
                  <option value="Pending" ${t.torStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                  <option value="Verified" ${t.torStatus === 'Verified' ? 'selected' : ''}>Verified</option>
                  <option value="Rejected" ${t.torStatus === 'Rejected' ? 'selected' : ''}>Rejected</option>
                </select>
              </td>
              <td>
                <select class="form-select" onchange="updateTutorDoc('${escapeHtml(t.id)}', 'id_status', this.value)">
                  <option value="Pending" ${t.idStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                  <option value="Verified" ${t.idStatus === 'Verified' ? 'selected' : ''}>Verified</option>
                  <option value="Rejected" ${t.idStatus === 'Rejected' ? 'selected' : ''}>Rejected</option>
                </select>
              </td>
              <td><span class="badge ${t.approvalStatus === 'Approved' ? 'badge-success' : t.approvalStatus === 'Declined' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(t.approvalStatus)}</span></td>
              <td><span class="badge ${t.deactivated ? 'badge-danger' : 'badge-success'}">${t.deactivated ? 'Deactivated' : 'Active'}</span></td>
              <td>
                <button class="btn btn-secondary btn-small" onclick="viewTutorProfile('${escapeHtml(t.id)}')">Profile</button>
                <button class="btn btn-primary btn-small" ${!allVerified && t.approvalStatus !== 'Approved' ? 'disabled title="All 3 documents must be Verified before approval."' : ''} onclick="updateTutorApproval('${escapeHtml(t.id)}', 'Approved')">Approve</button>
                <button class="btn btn-secondary btn-small" onclick="updateTutorApproval('${escapeHtml(t.id)}', 'Declined')">Decline</button>
                <button class="btn btn-secondary btn-small" onclick="toggleTutorDeactivate('${escapeHtml(t.id)}', ${!t.deactivated})">${t.deactivated ? 'Activate' : 'Deactivate'}</button>
              </td>
            </tr>
          `;
        }).join('');
      } catch (e) {}
    }

    async function updateTutorDoc(id, field, val) {
      try {
        await api('api/tutors.php', { method: 'PUT', body: { id, [field]: val } });
        showSuccess('Document status updated.');
        loadTutors();
      } catch (e) {}
    }

    async function updateTutorApproval(id, statusVal) {
      try {
        await api('api/tutors.php', { method: 'PUT', body: { id, approval_status: statusVal } });
        showSuccess('Tutor approval status set to ' + statusVal);
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

    function viewTutorProfile(id) {
      const t = allTutorsCache.find(x => x.id === id);
      if (!t) return;
      document.getElementById('profile-modal-title').textContent = 'Tutor Profile: ' + t.name;
      document.getElementById('profile-modal-content').innerHTML = `
        <div class="card-panel" style="margin-bottom:0;">
          <p><strong>Tutor ID:</strong> <code>${escapeHtml(t.id)}</code></p>
          <p><strong>Name:</strong> ${escapeHtml(t.name)}</p>
          <p><strong>Email:</strong> ${escapeHtml(t.email || 'N/A')}</p>
          <p><strong>Hourly Rate:</strong> ₱${escapeHtml(t.hourlyRate)}</p>
          <p><strong>Subjects:</strong> ${escapeHtml(Array.isArray(t.subjects) ? t.subjects.join(', ') : t.subjects)}</p>
          <p><strong>Learning Styles:</strong> ${escapeHtml(Array.isArray(t.learningStyles) ? t.learningStyles.join(', ') : t.learningStyles)}</p>
          <p><strong>Available Days:</strong> ${escapeHtml(Array.isArray(t.availableDays) ? t.availableDays.join(', ') : t.availableDays)}</p>
          <p><strong>Availability Slots:</strong> ${escapeHtml(Array.isArray(t.availabilitySlots) ? t.availabilitySlots.join(', ') : t.availabilitySlots)}</p>
          <p><strong>Bio:</strong></p>
          <p class="sub-text">${escapeHtml(t.bio || 'None provided.')}</p>
        </div>
      `;
      openModal('modal-view-profile');
    }

    // 4. MATCHES
    async function loadMatches() {
      const tbody = document.getElementById('matches-table-body');
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Loading match requests...</td></tr>';
      try {
        const json = await api('api/matches.php');
        const matches = json.data || [];
        if (matches.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No match requests found.</td></tr>';
          return;
        }
        tbody.innerHTML = matches.map(m => `
          <tr>
            <td><strong>${escapeHtml(m.studentName)}</strong></td>
            <td><strong>${escapeHtml(m.tutorName)}</strong></td>
            <td>${escapeHtml(m.subject)}</td>
            <td><span class="badge badge-match">${escapeHtml(m.score)}% Match</span></td>
            <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : m.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(m.status)}</span></td>
            <td>${escapeHtml(m.matchReason || '')}</td>
            <td>${escapeHtml(m.createdAt || m.time || '')}</td>
            <td>
              <button class="btn btn-primary btn-small" onclick="updateMatchStatus('${escapeHtml(m.id)}', 'Approved')">Approve</button>
              <button class="btn btn-secondary btn-small" onclick="updateMatchStatus('${escapeHtml(m.id)}', 'Cancelled')">Cancel</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    async function updateMatchStatus(id, statusVal) {
      try {
        await api('api/matches.php', { method: 'PUT', body: { id, status: statusVal } });
        showSuccess('Match status updated.');
        loadMatches();
      } catch (e) {}
    }

    // 5. SESSIONS
    async function loadSessions() {
      const tbody = document.getElementById('sessions-table-body');
      tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Loading sessions...</td></tr>';
      try {
        const json = await api('api/sessions.php');
        const sessions = json.data || [];
        if (sessions.length === 0) {
          tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">No sessions found.</td></tr>';
          return;
        }
        tbody.innerHTML = sessions.map(s => `
          <tr>
            <td><strong>${escapeHtml(s.studentName)}</strong></td>
            <td><strong>${escapeHtml(s.tutorName)}</strong></td>
            <td>${escapeHtml(s.subject)}</td>
            <td>${escapeHtml(s.date || s.sessionDate)} (${escapeHtml(s.timeSlot)})</td>
            <td>₱${escapeHtml(s.hourlyRate)}</td>
            <td>₱${escapeHtml(s.commissionFee)}</td>
            <td><strong>₱${escapeHtml(s.totalPaid)}</strong></td>
            <td><code>${escapeHtml(s.gcashRef)}</code></td>
            <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-pending'}">${escapeHtml(s.status)}</span></td>
            <td><span class="badge ${s.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${escapeHtml(s.payoutStatus || 'Unpaid')}</span></td>
            <td>
              <button class="btn btn-secondary btn-small" onclick="openRescheduleSession('${escapeHtml(s.id)}', '${escapeHtml(s.date || s.sessionDate)}', '${escapeHtml(s.timeSlot)}')">Reschedule</button>
              ${s.status !== 'Cancelled' ? `<button class="btn btn-secondary btn-small" onclick="cancelAdminSession('${escapeHtml(s.id)}')">Cancel</button>` : ''}
              <button class="btn btn-outline btn-small" onclick="viewSessionChat('${escapeHtml(s.id)}')">View Chat</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    function openRescheduleSession(id, dateVal, slotVal) {
      document.getElementById('resch-session-id').value = id;
      document.getElementById('resch-date').value = dateVal || '';
      document.getElementById('resch-slot').value = slotVal || '02:00 PM';
      openModal('modal-reschedule-session');
    }

    async function handleConfirmReschedule(e) {
      e.preventDefault();
      const id = document.getElementById('resch-session-id').value;
      const session_date = document.getElementById('resch-date').value;
      const time_slot = document.getElementById('resch-slot').value;

      try {
        await api('api/sessions.php', { method: 'PUT', body: { id, session_date, time_slot } });
        closeModal('modal-reschedule-session');
        showSuccess('Session rescheduled successfully.');
        loadSessions();
      } catch (e) {}
    }

    async function cancelAdminSession(id) {
      if (!confirm('Are you sure you want to cancel this tutoring session?')) return;
      try {
        await api('api/sessions.php', { method: 'PUT', body: { id, status: 'Cancelled' } });
        showSuccess('Session cancelled.');
        loadSessions();
      } catch (e) {}
    }

    async function viewSessionChat(sessionId) {
      const container = document.getElementById('chat-messages-container');
      container.innerHTML = '<p style="text-align:center; color: var(--muted);">Loading chat history...</p>';
      openModal('modal-view-chat');

      try {
        const json = await api('api/chat.php?session_id=' + encodeURIComponent(sessionId));
        const msgs = json.data || [];
        if (msgs.length === 0) {
          container.innerHTML = '<p style="text-align:center; color: var(--muted);">No chat messages recorded for this session.</p>';
          return;
        }
        container.innerHTML = msgs.map(m => `
          <div class="chat-item">
            <strong>${escapeHtml(m.senderName)} (${escapeHtml(m.senderRole)}):</strong>
            <span>${escapeHtml(m.message)}</span>
            <div style="font-size:0.75rem; color: var(--muted); text-align:right;">${escapeHtml(m.timestamp || m.createdAt || '')}</div>
          </div>
        `).join('');
      } catch (e) {}
    }

    // 6. PAYMENTS
    async function loadPayments() {
      const tbody = document.getElementById('payments-table-body');
      tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Loading payments...</td></tr>';
      try {
        const json = await api('api/payments.php');
        const payments = json.data || [];
        if (payments.length === 0) {
          tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">No payment transactions logged.</td></tr>';
          return;
        }
        tbody.innerHTML = payments.map(p => `
          <tr>
            <td><code>${escapeHtml(p.id)}</code></td>
            <td><strong>${escapeHtml(p.studentName)}</strong></td>
            <td>${escapeHtml(p.tutorName || 'N/A')}</td>
            <td>${escapeHtml(p.method)}</td>
            <td><code>${escapeHtml(p.refNo)}</code></td>
            <td>₱${escapeHtml(p.amount)}</td>
            <td><span class="badge ${p.status === 'Confirmed' ? 'badge-success' : 'badge-pending'}">${escapeHtml(p.status)}</span></td>
            <td><span class="badge ${p.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${escapeHtml(p.payoutStatus || 'Unpaid')}</span></td>
            <td>${escapeHtml(p.createdAt || p.time || '')}</td>
            <td>
              ${p.status === 'Pending' ? `<button class="btn btn-primary btn-small" onclick="updatePaymentStatus('${escapeHtml(p.id)}', 'Confirmed')">Confirm</button>` : ''}
              <button class="btn btn-secondary btn-small" ${p.status !== 'Confirmed' || p.payoutStatus === 'Paid Out' ? 'disabled' : ''} onclick="processPayout('${escapeHtml(p.id)}')">Process Payout</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    async function updatePaymentStatus(id, statusVal) {
      try {
        await api('api/payments.php', { method: 'PUT', body: { id, status: statusVal } });
        showSuccess('Payment status confirmed.');
        loadPayments();
      } catch (e) {}
    }

    async function processPayout(id) {
      try {
        await api('api/payments.php', { method: 'PUT', body: { id, payout_status: 'Paid Out' } });
        showSuccess('Tutor payout processed successfully.');
        loadPayments();
      } catch (e) {}
    }

    // 7. SUBJECTS
    async function loadSubjects() {
      const tbody = document.getElementById('subjects-table-body');
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading subjects...</td></tr>';
      try {
        const json = await api('api/subjects.php');
        allSubjectsCache = json.data || [];
        if (allSubjectsCache.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No subjects in catalog.</td></tr>';
          return;
        }
        tbody.innerHTML = allSubjectsCache.map(s => `
          <tr>
            <td><code>${escapeHtml(s.id)}</code></td>
            <td><strong>${escapeHtml(s.name)}</strong></td>
            <td><span class="badge badge-match">${escapeHtml(s.category)}</span></td>
            <td>
              <button class="btn btn-secondary btn-small" onclick="editSubjectForm('${escapeHtml(s.id)}', '${escapeHtml(s.name)}', '${escapeHtml(s.category)}')">Edit</button>
              <button class="btn btn-secondary btn-small" onclick="deleteSubject('${escapeHtml(s.id)}')">Delete</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {}
    }

    function editSubjectForm(id, name, category) {
      document.getElementById('subject-id').value = id;
      document.getElementById('subject-name').value = name;
      document.getElementById('subject-category').value = category;
      document.getElementById('subject-form-title').textContent = 'Edit Subject ID: ' + id;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetSubjectForm() {
      document.getElementById('subject-id').value = '';
      document.getElementById('subject-name').value = '';
      document.getElementById('subject-category').value = 'STEM';
      document.getElementById('subject-form-title').textContent = 'Add New Subject';
    }

    async function handleSaveSubject(e) {
      e.preventDefault();
      const id = document.getElementById('subject-id').value;
      const name = document.getElementById('subject-name').value.trim();
      const category = document.getElementById('subject-category').value;

      try {
        const body = id ? { id, name, category } : { name, category };
        await api('api/subjects.php', { method: 'POST', body });
        showSuccess(id ? 'Subject updated.' : 'Subject added to catalog.');
        resetSubjectForm();
        loadSubjects();
      } catch (e) {}
    }

    async function deleteSubject(id) {
      if (!confirm('Are you sure you want to delete this subject?')) return;
      try {
        await api('api/subjects.php', { method: 'DELETE', body: { id } });
        showSuccess('Subject deleted.');
        loadSubjects();
      } catch (e) {}
    }

    // 8. NOTIFICATIONS
    async function loadNotifications() {
      const container = document.getElementById('notifications-log-list');
      container.innerHTML = '<p style="text-align:center; color: var(--muted);">Loading history log...</p>';
      try {
        const json = await api('api/notifications.php');
        const notifs = json.data || [];
        if (notifs.length === 0) {
          container.innerHTML = '<p style="text-align:center; color: var(--muted);">No broadcast or system notifications logged.</p>';
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
            <div style="font-size: 0.78rem; color: var(--muted); text-align: right;">${escapeHtml(n.time || n.createdAt || '')}</div>
          </div>
        `).join('');
      } catch (e) {}
    }

    async function handleSendNotification(e) {
      e.preventDefault();
      const target_role = document.getElementById('notif-target').value;
      const title = document.getElementById('notif-title').value.trim();
      const message = document.getElementById('notif-message').value.trim();

      try {
        await api('api/notifications.php', {
          method: 'POST',
          body: { target_role, target: 'All Users', title, message }
        });
        showSuccess('Notification broadcast sent successfully.');
        document.getElementById('notif-title').value = '';
        document.getElementById('notif-message').value = '';
        loadNotifications();
      } catch (e) {}
    }

    // 9. REPORTS
    async function loadReports() {
      // Populate select dropdowns if cache empty
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
        const sessions = json.sessions || [];

        document.getElementById('rep-revenue').textContent = formatMoney(sum.total_revenue);
        document.getElementById('rep-commission').textContent = formatMoney(sum.total_commission);
        document.getElementById('rep-completed').textContent = sum.completed_sessions || 0;

        // Update Active Filter summary
        const filterParts = [];
        if (startDate && endDate) {
          filterParts.push(`${startDate} - ${endDate}`);
        } else if (startDate) {
          filterParts.push(`From ${startDate}`);
        } else if (endDate) {
          filterParts.push(`Until ${endDate}`);
        } else {
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
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 24px; color: var(--muted);"><div style="font-size: 1.1rem; font-weight: 600;">No sessions in this period</div><div style="font-size: 0.88rem; margin-top: 4px;">Try adjusting your date range or filter criteria.</div></td></tr>';
          return;
        }

        tbody.innerHTML = sessions.map(s => `
          <tr>
            <td><code>${escapeHtml(s.id)}</code></td>
            <td><strong>${escapeHtml(s.studentName)}</strong></td>
            <td><strong>${escapeHtml(s.tutorName)}</strong></td>
            <td>${escapeHtml(s.subject)}</td>
            <td>${escapeHtml(s.date || s.sessionDate)} (${escapeHtml(s.timeSlot)})</td>
            <td>₱${escapeHtml(s.totalPaid)}</td>
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

    // Initialize Dashboard on DOM Load
    document.addEventListener('DOMContentLoaded', () => {
      loadDashboard();
    });
  </script>
</body>
</html>
