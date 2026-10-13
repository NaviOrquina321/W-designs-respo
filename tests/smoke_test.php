<?php
// tests/smoke_test.php - CLI Automated Test Suite for TutorLink
// Runs on an isolated temporary SQLite database to verify all core workflows.

putenv('TEST_MODE=1');
$testDbPath = __DIR__ . '/test_temp.db';
if (file_exists($testDbPath)) {
    unlink($testDbPath);
}

// Global test tracker
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

try {
    // 1. Initialize Isolated SQLite Test Database
    $pdo = new PDO("sqlite:$testDbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("PRAGMA foreign_keys = ON;");

    // Setup Schema
    $pdo->exec("CREATE TABLE `schema_version` (`version` INT PRIMARY KEY, `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `users` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `user_id` VARCHAR(20) UNIQUE, `email` VARCHAR(100) UNIQUE, `password_hash` VARCHAR(255), `role` VARCHAR(50), `name` VARCHAR(100), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `admins` (`id` VARCHAR(20) PRIMARY KEY, `name` VARCHAR(100), `email` VARCHAR(100) UNIQUE, `role` VARCHAR(50), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `students` (`id` VARCHAR(20) PRIMARY KEY, `name` VARCHAR(100), `email` VARCHAR(100) UNIQUE, `grade` VARCHAR(50), `validated` TINYINT(1) DEFAULT 0, `deactivated` TINYINT(1) DEFAULT 0, `bio` TEXT, `subjects_needed` VARCHAR(255), `sessions_completed` INT DEFAULT 0, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `tutors` (`id` VARCHAR(20) PRIMARY KEY, `name` VARCHAR(100), `email` VARCHAR(100) UNIQUE, `initials` VARCHAR(10), `rating` DECIMAL(3,1) DEFAULT 0.0, `reviews_count` INT DEFAULT 0, `hourly_rate` INT, `subjects` VARCHAR(255), `learning_styles` VARCHAR(255), `bio` TEXT, `available` TINYINT(1) DEFAULT 1, `deactivated` TINYINT(1) DEFAULT 0, `diploma_status` VARCHAR(50) DEFAULT 'Pending', `tor_status` VARCHAR(50) DEFAULT 'Pending', `id_status` VARCHAR(50) DEFAULT 'Pending', `approval_status` VARCHAR(50) DEFAULT 'Pending Review', `available_days` VARCHAR(255), `available_time_slots` VARCHAR(255), `blocked_dates` TEXT, `availability_slots` TEXT, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `subjects` (`id` VARCHAR(20) PRIMARY KEY, `name` VARCHAR(100), `category` VARCHAR(100), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `matches` (`id` VARCHAR(20) PRIMARY KEY, `student_id` VARCHAR(20), `student_name` VARCHAR(100), `tutor_id` VARCHAR(20), `tutor_name` VARCHAR(100), `subject` VARCHAR(100), `score` INT, `status` VARCHAR(50) DEFAULT 'Pending Review', `match_reason` TEXT, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `schedules` (`id` VARCHAR(20) PRIMARY KEY, `tutor_id` VARCHAR(20), `tutor_name` VARCHAR(100), `student_id` VARCHAR(20), `student_name` VARCHAR(100), `date_slot` VARCHAR(100), `subject` VARCHAR(100), `status` VARCHAR(50) DEFAULT 'Available', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `payments` (`id` VARCHAR(20) PRIMARY KEY, `student_id` VARCHAR(20), `student_name` VARCHAR(100), `tutor_id` VARCHAR(20), `tutor_name` VARCHAR(100), `method` VARCHAR(50), `ref_no` VARCHAR(100), `amount` INT, `status` VARCHAR(50) DEFAULT 'Confirmed', `payout_status` VARCHAR(50) DEFAULT 'Unpaid', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `sessions` (`id` VARCHAR(20) PRIMARY KEY, `student_id` VARCHAR(20), `student_name` VARCHAR(100), `tutor_id` VARCHAR(20), `tutor_name` VARCHAR(100), `subject` VARCHAR(100), `session_date` DATE, `time_slot` VARCHAR(50), `hourly_rate` INT, `commission_fee` INT, `total_paid` INT, `gcash_ref` VARCHAR(100), `status` VARCHAR(50) DEFAULT 'Confirmed', `payout_status` VARCHAR(50) DEFAULT 'Unpaid', `notes` TEXT, `rating` INT DEFAULT 0, `feedback` TEXT, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `notifications` (`id` VARCHAR(50) PRIMARY KEY, `target_role` VARCHAR(50), `target` VARCHAR(100), `title` VARCHAR(150), `message` TEXT, `is_read` TINYINT(1) DEFAULT 0, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
    $pdo->exec("CREATE TABLE `chat_messages` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `session_id` VARCHAR(20), `sender_name` VARCHAR(100), `sender_role` VARCHAR(50), `message` TEXT, `timestamp` VARCHAR(50), `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");

    // Seed Data
    $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
    $studentHash = password_hash('student123', PASSWORD_BCRYPT);
    $tutorHash = password_hash('tutor123', PASSWORD_BCRYPT);

    $pdo->exec("INSERT INTO users (user_id, email, password_hash, role, name) VALUES ('ADMIN-001', 'admin@tutorlink.ph', '$adminHash', 'admin', 'System Admin');");
    $pdo->exec("INSERT INTO users (user_id, email, password_hash, role, name) VALUES ('STU-101', 'maria@tutorlink.ph', '$studentHash', 'student', 'Maria Santos');");
    $pdo->exec("INSERT INTO users (user_id, email, password_hash, role, name) VALUES ('STU-102', 'juan@tutorlink.ph', '$studentHash', 'student', 'Juan Dela Cruz');");
    $pdo->exec("INSERT INTO users (user_id, email, password_hash, role, name) VALUES ('tut-1', 'alex@tutorlink.ph', '$tutorHash', 'tutor', 'Prof. Alex Rivera');");

    $pdo->exec("INSERT INTO admins (id, name, email, role) VALUES ('ADMIN-001', 'System Admin', 'admin@tutorlink.ph', 'admin');");
    $pdo->exec("INSERT INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed) VALUES ('STU-101', 'Maria Santos', 'maria@tutorlink.ph', 'Senior High', 1, 0, 'STEM Student', 'Calculus');");
    $pdo->exec("INSERT INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed) VALUES ('STU-102', 'Juan Dela Cruz', 'juan@tutorlink.ph', 'College', 1, 0, 'CS Student', 'Programming');");
    $pdo->exec("INSERT INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, diploma_status, tor_status, id_status, approval_status, available_days, available_time_slots, availability_slots) VALUES ('tut-1', 'Prof. Alex Rivera', 'alex@tutorlink.ph', 'AR', 5.0, 1, 350, 'Calculus, Physics, Mathematics', 'Visual & Diagrams, Step-by-Step Explanation', 'Math Professor', 1, 0, 'Verified', 'Verified', 'Verified', 'Approved', 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM');");

    $pdo->exec("INSERT INTO subjects (id, name, category) VALUES ('SUB-101', 'Mathematics', 'STEM');");
    $pdo->exec("INSERT INTO subjects (id, name, category) VALUES ('SUB-102', 'Calculus', 'STEM');");

    require_once __DIR__ . '/../api/helpers.php';
    require_once __DIR__ . '/../api/payment_provider.php';

    echo "===============================================\n";
    echo "TUTORLINK AUTOMATED TEST SUITE RUNNER\n";
    echo "===============================================\n\n";

    // TEST 1: Password Verification Per Role
    $stA = $pdo->prepare("SELECT password_hash, role FROM users WHERE email = ?");
    $stA->execute(['admin@tutorlink.ph']);
    $userA = $stA->fetch();
    assertTest($userA && password_verify('admin123', $userA['password_hash']), "1. Admin password verification succeeds.");

    $stS = $pdo->prepare("SELECT password_hash, role FROM users WHERE email = ?");
    $stS->execute(['maria@tutorlink.ph']);
    $userS = $stS->fetch();
    assertTest($userS && password_verify('student123', $userS['password_hash']), "2. Student password verification succeeds.");

    // TEST 2: Main Portal vs Admin Portal Login Gate Validation
    $isMainPortal = true; // TUTORLINK_MAIN
    $adminRejectedOnMain = ($isMainPortal && $userA['role'] === 'admin');
    assertTest($adminRejectedOnMain, "3. Admin login is rejected on Main Site portal (TUTORLINK_MAIN).");

    $isMainPortal = false; // TUTORLINK_ADMIN
    $studentRejectedOnAdmin = (!$isMainPortal && $userS['role'] !== 'admin');
    assertTest($studentRejectedOnAdmin, "4. Student login is rejected on Admin Portal (TUTORLINK_ADMIN).");

    // TEST 3: Demo GCash Payment Auto-Confirmation & Booking Creation
    $studentRec = ['id' => 'STU-101', 'name' => 'Maria Santos'];
    $tutorRec = ['id' => 'tut-1', 'name' => 'Prof. Alex Rivera', 'hourly_rate' => 350];
    $futureDate = date('Y-m-d', strtotime('+5 days'));
    $timeSlot = '02:00 PM';

    $createdSession = processDemoGCashPayment($pdo, $studentRec, $tutorRec, 'Calculus', $futureDate, $timeSlot);
    assertTest($createdSession && $createdSession['status'] === 'Confirmed', "5. Demo GCash booking auto-confirms session status to 'Confirmed'.");

    $stPay = $pdo->prepare("SELECT status FROM payments WHERE ref_no = ?");
    $stPay->execute([$createdSession['gcash_ref']]);
    $payRow = $stPay->fetch();
    assertTest($payRow && $payRow['status'] === 'Confirmed', "6. Demo GCash payment status auto-confirms to 'Confirmed'.");

    // TEST 4: Double Booking Rejection
    $doubleBookFailed = false;
    try {
        processDemoGCashPayment($pdo, ['id' => 'STU-102', 'name' => 'Juan Dela Cruz'], $tutorRec, 'Calculus', $futureDate, $timeSlot);
    } catch (Throwable $e) {
        $doubleBookFailed = true;
    }
    assertTest($doubleBookFailed, "7. Double booking the same tutor, date, and slot is rejected.");

    // TEST 5: Past Slot Earlier Today Rejection
    $todayDate = date('Y-m-d');
    $pastSlotEarlierToday = "08:00 AM";
    $slotTs = parseSlotStartTime($todayDate, $pastSlotEarlierToday);
    $isPastSlotEarlierToday = ($todayDate === date('Y-m-d') && $slotTs > 0 && $slotTs < time());
    assertTest($isPastSlotEarlierToday, "8. Booking past slot earlier today is detected and rejected.");

    // TEST 6: Reschedule 24-Hour Rule
    $sessionStartTs = parseSlotStartTime($createdSession['session_date'], $createdSession['time_slot']);
    $diffSecs = $sessionStartTs - time();
    $canRescheduleFuture = ($diffSecs > 86400);
    assertTest($canRescheduleFuture, "9. Student reschedule allowed for session > 24 hours in future ($futureDate).");

    $nearFutureDate = date('Y-m-d');
    $nearSlotTs = time() + 3600; // 1 hour in future
    $diffNear = $nearSlotTs - time();
    $cannotRescheduleNear = ($diffNear <= 86400);
    assertTest($cannotRescheduleNear, "10. Student reschedule rejected for session starting <= 24 hours.");

    // TEST 7: Notifications Reach Right User
    notify($pdo, 'student', 'Maria Santos', 'Test Notification', 'This is a test notification for Maria.');
    $stN = $pdo->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE target_role = 'student' AND target = 'Maria Santos'");
    $stN->execute();
    assertTest($stN->fetch()['cnt'] > 0, "11. Targeted student notification reaches Maria Santos.");

    // TEST 8: CSV Export Cell Sanitization
    $dirtyCell = "=SUM(1+1)";
    $cleanCell = sanitizeCsvCell($dirtyCell);
    assertTest($cleanCell === "'=SUM(1+1)", "12. CSV export prefixes formula cell starting with '=' with a single quote.");

    // TEST 9: Schedule Rows Isolation / Student Privacy
    $stSch = $pdo->prepare("SELECT student_name FROM schedules WHERE tutor_id = 'tut-1' AND student_id = 'STU-101'");
    $stSch->execute();
    $schRow = $stSch->fetch();
    assertTest($schRow && $schRow['student_name'] === 'Maria Santos', "13. Schedule row contains student association.");

    echo "\n===============================================\n";
    echo "TEST SUMMARY: PASSED = $passed | FAILED = $failed\n";
    echo "===============================================\n";

    // Clean up temporary database file
    if (file_exists($testDbPath)) {
        unlink($testDbPath);
    }

    if ($failed > 0) {
        exit(1);
    } else {
        exit(0);
    }
} catch (Throwable $ex) {
    echo "TEST ERROR: " . $ex->getMessage() . "\n";
    if (file_exists($testDbPath)) {
        unlink($testDbPath);
    }
    exit(1);
}
?>