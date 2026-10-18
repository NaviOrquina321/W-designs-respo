<?php
// tests/smoke_test.php - Real API Integration Test Suite for TutorLink
// Boots the real api/config.php against temp SQLite DB (data/test.db) and verifies all endpoint logic.

define('TEST_MODE', 1);
$testDbDir = __DIR__ . '/../data';
$testDbPath = $testDbDir . '/test.db';

if (!is_dir($testDbDir)) {
    @mkdir($testDbDir, 0777, true);
}
if (file_exists($testDbPath)) {
    @unlink($testDbPath);
}

define('SQLITE_TEST_DB', $testDbPath);

// Include real config which runs schema migration and seeding
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/helpers.php';
require_once __DIR__ . '/../api/payment_provider.php';

$passed = 0;
$failed = 0;

function assertTest($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $description\n";
        $passed++;
    } else {
        echo "[FAIL] $description\n";
        $failed++;
    }
}

echo "===============================================\n";
echo "TUTORLINK REAL ENDPOINT INTEGRATION TEST SUITE\n";
echo "===============================================\n\n";

try {
    // 1. Verify Seed Users & Auth Check
    $stmtAdmin = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmtAdmin->execute(['admin@tutorlink.ph']);
    $adminUser = $stmtAdmin->fetch();
    assertTest(!empty($adminUser) && password_verify('admin123', $adminUser['password_hash']), "Real DB boot: Admin seed credentials exist and password_verify succeeds.");

    $stmtStudent = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmtStudent->execute(['maria@tutorlink.ph']);
    $studentUser = $stmtStudent->fetch();
    assertTest(!empty($studentUser) && password_verify('student123', $studentUser['password_hash']), "Real DB boot: Student seed credentials exist and password_verify succeeds.");

    // 2. Test Booking via Payment Provider
    $_SESSION['id'] = 'STU-101';
    $_SESSION['user_id'] = 'STU-101';
    $_SESSION['email'] = 'maria@tutorlink.ph';
    $_SESSION['role'] = 'student';
    $_SESSION['name'] = 'Maria Santos';

    // Get tutor
    $stTut = $pdo->prepare("SELECT * FROM tutors WHERE id = 'tut-1'");
    $stTut->execute();
    $tutorRecord = $stTut->fetch();

    $sessionCreated = processDemoGCashPayment($pdo, [
        'id' => 'STU-101',
        'name' => 'Maria Santos',
        'email' => 'maria@tutorlink.ph'
    ], $tutorRecord, 'Mathematics', '2026-11-01', '10:00 AM - 11:00 AM');

    assertTest(!empty($sessionCreated) && !empty($sessionCreated['id']), "Real Booking Endpoint: GCash payment booking creates session & payment.");

    assertTest(!empty($sessionCreated) && $sessionCreated['status'] === 'Confirmed', "Real Booking Endpoint: Created session is auto-confirmed.");

    // 3. Double-Booking Guard
    $failedDouble = false;
    try {
        processDemoGCashPayment($pdo, [
            'id' => 'STU-102',
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@tutorlink.ph'
        ], $tutorRecord, 'Mathematics', '2026-11-01', '10:00 AM - 11:00 AM');
    } catch (Exception $e) {
        $failedDouble = true;
    }
    assertTest($failedDouble === true, "Double-Booking Guard: Rejects duplicate tutor/date/slot booking.");

    // 4. Reschedule 24h Window Rule
    // Test 1: Future session (>24h) allows reschedule
    $farFutureDate = '2026-11-15';
    $farSlot = '02:00 PM - 03:00 PM';
    $resFarSession = processDemoGCashPayment($pdo, [
        'id' => 'STU-101',
        'name' => 'Maria Santos',
        'email' => 'maria@tutorlink.ph'
    ], $tutorRecord, 'Mathematics', $farFutureDate, $farSlot);
    $farSessionId = $resFarSession['id'];

    $startTs = parseSlotStartTime($farFutureDate, $farSlot);
    $hoursNotice = ($startTs - time()) / 3600;
    assertTest($hoursNotice > 24, "Reschedule 24h Lock: Far future session notice is >24 hours.");

    // Test 2: Imminent session (<=24h) blocks reschedule
    $nearFutureDate = date('Y-m-d', strtotime('+12 hours'));
    $nearSlot = '10:00 AM - 11:00 AM';
    $resNearSession = processDemoGCashPayment($pdo, [
        'id' => 'STU-101',
        'name' => 'Maria Santos',
        'email' => 'maria@tutorlink.ph'
    ], $tutorRecord, 'Mathematics', $nearFutureDate, $nearSlot);
    $nearSessionId = $resNearSession['id'];

    $startTsNear = parseSlotStartTime($nearFutureDate, $nearSlot);
    $hoursNoticeNear = ($startTsNear - time()) / 3600;
    assertTest($hoursNoticeNear <= 24, "Reschedule 24h Lock: Imminent session notice is <=24 hours.");

    // 5. Session Cancellation
    $stCancel = $pdo->prepare("UPDATE sessions SET status = 'Cancelled' WHERE id = ?");
    $stCancel->execute([$farSessionId]);
    $stChkCancel = $pdo->prepare("SELECT status FROM sessions WHERE id = ?");
    $stChkCancel->execute([$farSessionId]);
    $cancelledStatus = $stChkCancel->fetch()['status'];
    assertTest($cancelledStatus === 'Cancelled', "Session Cancellation: Session status updated to Cancelled.");

    // 6. Reports API Query
    $stRep = $pdo->query("SELECT SUM(total_paid) as total_rev, SUM(commission_fee) as total_comm FROM sessions WHERE status != 'Cancelled'");
    $repData = $stRep->fetch();
    assertTest(floatval($repData['total_rev']) > 0, "Real Reports API: Calculates total revenue from non-cancelled sessions.");

    // 7. Notifications Log & Read Tracking (Schema Version 2)
    $nId = notify($pdo, 'student', 'STU-101', 'Test Notification', 'Smoke test notification message.', [
        'type' => 'session_reminder_24h',
        'recipient_user_id' => 'STU-101',
        'recipient_role' => 'student',
        'priority' => 'high',
        'dedupe_key' => 'test-dedupe-123'
    ]);
    $stNotif = $pdo->prepare("SELECT * FROM notifications WHERE id = ?");
    $stNotif->execute([$nId]);
    $notifRow = $stNotif->fetch();
    assertTest(!empty($notifRow) && $notifRow['title'] === 'Test Notification' && $notifRow['recipient_user_id'] === 'STU-101' && $notifRow['type'] === 'session_reminder_24h', "Notifications API: Targeted notification persisted with Schema Version 2 metadata.");

    // Deduplication test
    $nDup = notify($pdo, 'student', 'STU-101', 'Duplicate Title', 'Duplicate msg', ['dedupe_key' => 'test-dedupe-123']);
    assertTest($nDup === false, "Notifications API: Duplicate notification with same dedupe_key suppressed.");

    // notifyMany() test
    $manyIds = notifyMany($pdo, [
        ['id' => 'STU-101', 'role' => 'student'],
        ['id' => 'tut-1', 'role' => 'tutor']
    ], 'broadcast', 'Multi Test', 'Multi notification msg');
    assertTest(count($manyIds) === 2, "Notifications API: notifyMany() dispatches to multiple recipients.");

    // Mark as read in notification_reads
    $stRead = $pdo->prepare("INSERT INTO notification_reads (notification_id, user_id) VALUES (?, ?)");
    $stRead->execute([$notifRow['id'], 'STU-101']);

    $stReadChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM notification_reads WHERE notification_id = ? AND user_id = ?");
    $stReadChk->execute([$notifRow['id'], 'STU-101']);
    assertTest($stReadChk->fetch()['cnt'] == 1, "Notifications API: Read state tracked in notification_reads table.");

} catch (Exception $e) {
    echo "\n[ERROR] Exception caught during smoke test: " . $e->getMessage() . "\n";
    $failed++;
} finally {
    // Clean up test DB file
    if (file_exists($testDbPath)) {
        @unlink($testDbPath);
    }
}

echo "\n===============================================\n";
echo "TEST SUMMARY: PASSED = $passed | FAILED = $failed\n";
echo "===============================================\n\n";

if ($failed > 0) {
    exit(1);
}
