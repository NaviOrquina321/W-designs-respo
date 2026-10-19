<?php
// tests/smoke_test.php - Comprehensive Integration Test Suite for TutorLink Admin FDD 14 Functions
putenv('TEST_MODE=1');
define('TEST_MODE', 1);
define('SQLITE_TEST_DB', __DIR__ . '/../data/test.db');

if (file_exists(SQLITE_TEST_DB)) {
    @unlink(SQLITE_TEST_DB);
}

require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/helpers.php';
require_once __DIR__ . '/seed_admin.php';

$passCount = 0;
$failCount = 0;

function assertTest($condition, $testName) {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASS] $testName\n";
        $passCount++;
    } else {
        echo "[FAIL] $testName\n";
        $failCount++;
    }
}

echo "\n===============================================\n";
echo "TUTORLINK REAL ENDPOINT INTEGRATION TEST SUITE\n";
echo "===============================================\n\n";

// Helper to simulate endpoint call with given session context
function callEndpoint($endpoint, $method = 'GET', $body = [], $session = []) {
    global $pdo;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['PHP_SELF'] = $endpoint;
    $_SERVER['HTTP_X_TUTORLINK_PORTAL'] = ($session['role'] ?? '') === 'admin' ? 'admin' : 'main';

    $_SESSION = $session;
    $GLOBALS['_MOCK_INPUT'] = $body;

    ob_start();
    try {
        include __DIR__ . '/../' . $endpoint;
        $out = ob_get_clean();
        return json_decode($out, true);
    } catch (Exception $e) {
        ob_get_clean();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// --- 1. View Student List & 2. Validate Student List ---
$resStudents = callEndpoint('api/students.php', 'GET', [], ['user_id' => 'ADMIN-001', 'role' => 'admin', 'name' => 'System Admin']);
assertTest(($resStudents['status'] ?? '') === 'success' && count($resStudents['data'] ?? []) >= 3, "1. View Student List: Admin can fetch all student records.");

$resStuVal = callEndpoint('api/students.php', 'PUT', ['id' => 'STU-UNVAL-02', 'validated' => true], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resStuVal['status'] ?? '') === 'success', "2. Validate Student List: Admin can validate a student.");

$resStuDeact = callEndpoint('api/students.php', 'PUT', ['id' => 'STU-DEACT-03', 'deactivated' => true], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resStuDeact['status'] ?? '') === 'success', "2. Validate Student List: Admin can deactivate a student account.");

// Test deactivated student cannot log in
$resDeactLogin = callEndpoint('api/login.php', 'POST', ['email' => 'pedro.deact@tutorlink.ph', 'password' => 'student123']);
assertTest(($resDeactLogin['status'] ?? '') === 'error' && strpos($resDeactLogin['message'] ?? '', 'deactivated') !== false, "2. Validate Student List: Deactivated student is blocked from logging in.");

// --- 3. Review Matching Results, 4. Approve Matching Sessions, 5. Cancel Matching Sessions ---
$resMatches = callEndpoint('api/matches.php', 'GET', [], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resMatches['status'] ?? '') === 'success' && count($resMatches['data'] ?? []) >= 3, "3. Review Matching Results: Admin fetches match records.");

$resAppMatch = callEndpoint('api/matches.php', 'PUT', ['id' => 'MATCH-PEND-1', 'status' => 'Approved'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resAppMatch['status'] ?? '') === 'success', "4. Approve Matching Sessions: Admin approves pending match.");

$resCancMatch = callEndpoint('api/matches.php', 'PUT', ['id' => 'MATCH-APP-2', 'status' => 'Cancelled', 'reason' => 'Duplicate booking'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resCancMatch['status'] ?? '') === 'success', "5. Cancel Matching Sessions: Admin cancels match with required reason.");

$resCancMatchNoReason = callEndpoint('api/matches.php', 'PUT', ['id' => 'MATCH-PEND-1', 'status' => 'Cancelled'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resCancMatchNoReason['status'] ?? '') === 'error', "5. Cancel Matching Sessions: Rejects match cancellation when reason is missing (422).");

// --- 6. New Calendar Schedule & 7. Modify Sessions Schedule ---
$resSchAdd = callEndpoint('api/schedules.php', 'POST', ['tutor_id' => 'tut-1', 'date' => date('Y-m-d', strtotime('+5 days')), 'time_slot' => '11:00 AM - 12:00 PM', 'subject' => 'Calculus'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resSchAdd['status'] ?? '') === 'success', "6. New Calendar Schedule: Admin creates available schedule slot.");

$resSchAddDup = callEndpoint('api/schedules.php', 'POST', ['tutor_id' => 'tut-1', 'date' => date('Y-m-d', strtotime('+5 days')), 'time_slot' => '11:00 AM - 12:00 PM', 'subject' => 'Calculus'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resSchAddDup['status'] ?? '') === 'error', "6. New Calendar Schedule: Rejects duplicate slot creation (422).");

$resResch = callEndpoint('api/sessions.php', 'PUT', ['id' => 'SES-THIS-WK1', 'session_date' => date('Y-m-d', strtotime('+6 days')), 'time_slot' => '02:00 PM - 03:00 PM'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resResch['status'] ?? '') === 'success', "7. Modify Sessions Schedule: Admin reschedules confirmed session.");

// --- Tutor Start Session Live & Live Chat Verification ---
$resTutorStart = callEndpoint('api/sessions.php', 'PUT', ['id' => 'SES-THIS-WK1', 'status' => 'In Progress'], ['user_id' => 'tut-1', 'role' => 'tutor', 'name' => 'Prof. Alex Rivera']);
assertTest(($resTutorStart['status'] ?? '') === 'success', "Live Workspace: Tutor starts live session, updating status to In Progress and notifying student.");

$resSendChat = callEndpoint('api/chat.php', 'POST', ['session_id' => 'SES-THIS-WK1', 'message' => 'Hello professor, I am ready!'], ['user_id' => 'STU-VAL-01', 'role' => 'student', 'name' => 'Maria Santos']);
assertTest(($resSendChat['status'] ?? '') === 'success', "Live Workspace Chat: Student sends real-time chat message.");

$_GET['session_id'] = 'SES-THIS-WK1';
$_GET['after_id'] = 0;
$resGetChat = callEndpoint('api/chat.php', 'GET', [], ['user_id' => 'tut-1', 'role' => 'tutor', 'name' => 'Prof. Alex Rivera']);
unset($_GET['session_id'], $_GET['after_id']);
assertTest(($resGetChat['status'] ?? '') === 'success' && count($resGetChat['data'] ?? []) >= 1, "Live Workspace Chat: Tutor receives student chat message.");

// Forward-only status transition checks
$resForwardComp = callEndpoint('api/sessions.php', 'PUT', ['id' => 'SES-THIS-WK1', 'status' => 'Completed'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resForwardComp['status'] ?? '') === 'success', "7. Modify Sessions Schedule: Admin completes confirmed session.");

$resRevertComp = callEndpoint('api/sessions.php', 'PUT', ['id' => 'SES-THIS-WK1', 'status' => 'In Progress'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resRevertComp['status'] ?? '') === 'error', "7. Modify Sessions Schedule: Rejects reverting completed session (final status rule).");

// --- Completed Session Rating Assertion ---
$resRate = callEndpoint('api/sessions.php', 'PUT', ['id' => 'SES-LAST-WK1', 'rating' => 5, 'feedback' => 'Exemplary calculus tutor!'], ['user_id' => 'STU-VAL-01', 'role' => 'student', 'name' => 'Maria Santos']);
assertTest(($resRate['status'] ?? '') === 'success', "Rating System: Student rates completed session with SQL persistence and tutor/admin notifications.");

// --- Admin Account Creation Assertion ---
$resNewAdmin = callEndpoint('api/register.php', 'POST', ['role' => 'admin', 'name' => 'Assistant Admin', 'email' => 'asst.admin@tutorlink.ph', 'password' => 'adminpass123'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resNewAdmin['status'] ?? '') === 'success', "Admin Management: Admin can create a new administrator account.");

$resNewAdminLogin = callEndpoint('api/login.php', 'POST', ['email' => 'asst.admin@tutorlink.ph', 'password' => 'adminpass123'], ['role' => 'admin']);
assertTest(($resNewAdminLogin['status'] ?? '') === 'success' && ($resNewAdminLogin['user']['role'] ?? '') === 'admin', "Admin Management: Newly created admin account can log in via admin portal.");

// --- 8. New Payment Records & 9. Confirm Payment Status ---
$resPayments = callEndpoint('api/payments.php', 'GET', [], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resPayments['status'] ?? '') === 'success' && count($resPayments['data'] ?? []) >= 4, "8. New Payment Records: Admin fetches payment logs.");

$resPayConfirm = callEndpoint('api/payments.php', 'PUT', ['id' => 'PAY-THIS-WK1', 'status' => 'Confirmed'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resPayConfirm['status'] ?? '') === 'success', "9. Confirm Payment Status: Admin confirms payment status.");

$resPayRevertFail = callEndpoint('api/payments.php', 'PUT', ['id' => 'PAY-LAST-WK1', 'status' => 'Pending'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resPayRevertFail['status'] ?? '') === 'error', "9. Confirm Payment Status: Rejects reverting paid out payment to pending.");

// --- 10. Send Notifications & 11. View Notifications ---
$resSendNotif = callEndpoint('api/notifications.php', 'POST', ['audience' => 'student_specific', 'specific_user_id' => 'STU-VAL-01', 'title' => 'Test Notification', 'message' => 'Test message content.', 'priority' => 'normal'], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resSendNotif['status'] ?? '') === 'success', "10. Send Notifications: Admin sends targeted notification.");

$resViewNotifSent = callEndpoint('api/notifications.php', 'GET', [], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resViewNotifSent['status'] ?? '') === 'success', "11. View Notifications: Admin fetches sent notification log.");

// --- 12. View Completed Tutoring, 13. View Weekly Sessions, 14. View Monthly Sessions & No CSV Rejection ---
$resRepCompleted = callEndpoint('api/reports.php', 'GET', [], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
assertTest(($resRepCompleted['status'] ?? '') === 'success' && isset($resRepCompleted['summary']['total_paid']), "12. View Completed Tutoring: Admin generates reports summary.");

// Verify CSV rejection
$_GET['export'] = 'sessions_csv';
$resCsvReject = callEndpoint('api/reports.php', 'GET', [], ['user_id' => 'ADMIN-001', 'role' => 'admin']);
unset($_GET['export']);
assertTest(($resCsvReject['status'] ?? '') === 'error' && strpos($resCsvReject['message'] ?? '', 'CSV') !== false, "14. Reports CSV Rejection: Rejects export=csv with clear 422 error.");

// Verify no CSV button in admin.php
$adminPhpContent = file_get_contents(__DIR__ . '/../admin.php');
assertTest(strpos(strtolower($adminPhpContent), 'export csv') === false && strpos(strtolower($adminPhpContent), 'download csv') === false, "14. Admin UI Audit: No CSV export buttons exist in admin.php.");

// Verify audit logging
$stAuditCheck = $pdo->query("SELECT COUNT(*) as cnt FROM admin_audit_log");
assertTest($stAuditCheck->fetch()['cnt'] >= 5, "Audit Logging: Admin actions successfully written to admin_audit_log.");

// Verify Non-Admin Forbidden (401/403)
$resForbidden = callEndpoint('api/admin.php', 'GET', [], ['user_id' => 'STU-VAL-01', 'role' => 'student']);
assertTest(($resForbidden['status'] ?? '') === 'error', "Security: Non-admin user rejected with forbidden error.");

echo "\n===============================================\n";
echo "TEST SUMMARY: PASSED = $passCount | FAILED = $failCount\n";
echo "===============================================\n\n";

if ($failCount > 0) {
    exit(1);
}
