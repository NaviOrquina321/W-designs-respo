<?php
// admin.php - Standalone Admin Monitoring Portal (Direct Open Access)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TutorLink - Admin Monitoring & System Management</title>
  <meta name="description" content="TutorLink Administrator Portal for managing students, tutors, schedules, payments, notifications, and reports.">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <!-- Top Navigation Header -->
  <header>
    <nav class="wrap">
      <div class="logo" id="nav-logo" style="cursor: pointer;" onclick="window.location.href='admin.php'">TutorLink <span style="font-size: 0.8rem; font-weight: normal; color: var(--muted); margin-left: 6px;">Admin Portal</span></div>

      <div class="nav-links">
        <a href="index.php" class="link">Main Site</a>
      </div>

      <div class="nav-actions">
        <a href="index.php" class="btn btn-secondary btn-small">Return to Main Site</a>
      </div>
    </nav>
  </header>

  <!-- MAIN ADMIN DASHBOARD CONTENT -->
  <main class="wrap margin-top" id="admin-main-content">
    <div class="dashboard-container">
      <div class="dashboard-header">
        <div>
          <h2>Admin Monitoring & System Management</h2>
          <p class="sub-text">FDD Modules: Manage Students, Tutor Matching, Schedule, Payments, Notifications, & Reports.</p>
        </div>
        <button class="btn btn-primary btn-icon-flex" id="generate-admin-report-btn">
          <svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
          Export Activity Report
        </button>
      </div>

      <!-- Admin KPI Metrics -->
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-num" id="admin-stat-total-students">0</span>
          <span class="stat-label">Total Registered Students</span>
        </div>
        <div class="stat-card">
          <span class="stat-num" id="admin-stat-total-tutors">0</span>
          <span class="stat-label">Verified Tutors</span>
        </div>
        <div class="stat-card">
          <span class="stat-num" id="admin-stat-total-volume">₱0</span>
          <span class="stat-label">Total Tutoring Volume</span>
        </div>
        <div class="stat-card">
          <span class="stat-num" id="admin-stat-platform-commission">₱0</span>
          <span class="stat-label">Platform Commission (10%)</span>
        </div>
      </div>

      <!-- Admin FDD Management Tabs -->
      <div class="admin-tab-bar">
        <button class="admin-tab-btn active" data-tab="tab-students">Manage Students</button>
        <button class="admin-tab-btn" data-tab="tab-matching">Manage Tutor Matching</button>
        <button class="admin-tab-btn" data-tab="tab-schedule">Manage Schedule</button>
        <button class="admin-tab-btn" data-tab="tab-payments">Manage Payments</button>
        <button class="admin-tab-btn" data-tab="tab-notifications">Manage Notifications</button>
        <button class="admin-tab-btn" data-tab="tab-reports">Manage Reports</button>
        <button class="admin-tab-btn" data-tab="tab-tutors">Manage Tutors</button>
        <button class="admin-tab-btn" data-tab="tab-subjects">Manage Subjects</button>
      </div>

      <!-- 1. Manage Students -->
      <div class="admin-tab-content active" id="tab-students">
        <div class="admin-subtab-bar">
          <button class="admin-subtab-btn active" data-subtab="subtab-student-view">View Student List</button>
          <button class="admin-subtab-btn" data-subtab="subtab-student-validate">Validate Student List</button>
        </div>

        <div class="admin-subtab-content active" id="subtab-student-view">
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
              <h3>New Calendar Schedule Slot</h3>
            </div>
            <form id="admin-add-schedule-form" class="profile-card-edit">
              <div class="form-group">
                <label>Select Tutor</label>
                <select id="admin-sch-tutor-select" class="form-select"></select>
              </div>
              <div class="form-group">
                <label>Subject</label>
                <input type="text" id="admin-sch-subject" class="form-input" placeholder="e.g. Calculus II" required>
              </div>
              <div class="form-group">
                <label>Date & Time Slot</label>
                <input type="text" id="admin-sch-dateslot" class="form-input" placeholder="e.g. 2026-03-20 (02:00 PM - 04:00 PM)" required>
              </div>
              <button type="submit" class="btn btn-primary margin-top">Create Schedule Slot</button>
            </form>
          </div>
        </div>

        <div class="admin-subtab-content" id="subtab-sch-modify">
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
              <h3>Send Broadcast Notifications</h3>
            </div>
            <div class="profile-card-edit">
              <div class="form-group">
                <label>Notification Recipient</label>
                <select id="admin-notif-target" class="form-select">
                  <option value="all">All Users (Students & Tutors)</option>
                  <option value="students">All Students</option>
                  <option value="tutors">All Tutors</option>
                </select>
              </div>
              <div class="form-group">
                <label>Message Content</label>
                <input type="text" id="admin-notif-message" placeholder="Type notification broadcast message..." class="form-input">
              </div>
              <button class="btn btn-primary full-width margin-top" id="admin-send-notif-btn">Send Notification Broadcast</button>
            </div>
          </div>
        </div>

        <div class="admin-subtab-content" id="subtab-notif-view">
          <div class="dashboard-section">
            <div class="section-title-bar">
              <h3>View Notifications History</h3>
            </div>
            <div id="admin-notifications-log" class="cards-list">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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
          <div class="dashboard-section">
            <div class="section-title-bar">
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

      <!-- 7. Manage Tutors -->
      <div class="admin-tab-content" id="tab-tutors">
        <div class="dashboard-section">
          <div class="section-title-bar">
            <h3>Tutor Verification & Account Management</h3>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>Tutor ID</th>
                <th>Full Name</th>
                <th>Subjects</th>
                <th>Application Status</th>
                <th>Account Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="admin-tutors-table-body">
              <!-- Dynamic tutors list -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- 8. Manage Subjects -->
      <div class="admin-tab-content" id="tab-subjects">
        <div class="dashboard-section">
          <div class="section-title-bar">
            <h3>System Subjects & Academic Categories</h3>
            <button class="btn btn-primary btn-small" id="admin-add-subject-btn">+ Add New Subject</button>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>Subject ID</th>
                <th>Subject Name</th>
                <th>Category</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="admin-subjects-table-body">
              <!-- Dynamic subjects list -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div id="toast" class="toast hidden"></div>

  <script src="app.js"></script>
  <script>
    // Direct open access for Admin Portal
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof switchRole === 'function') {
        switchRole('admin', { id: 'ADMIN-001', name: 'System Admin', role: 'admin' });
      }
    });
  </script>
</body>
</html>
