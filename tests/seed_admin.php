<?php
// tests/seed_admin.php - Database Seeder for Admin Testing
require_once __DIR__ . '/../api/config.php';

echo "Seeding TutorLink Database with Representative Admin Test Data...\n";

try {
    // 1. Seed Students (Validated, Unvalidated, Deactivated)
    $studentPassword = password_hash('student123', PASSWORD_BCRYPT);

    $students = [
        ['STU-VAL-01', 'Maria Santos', 'maria.val@tutorlink.ph', 'Senior High', 1, 0, 'Validated student focusing on Calculus.', 'Calculus, Physics', 2],
        ['STU-UNVAL-02', 'Juan Dela Cruz', 'juan.unval@tutorlink.ph', 'College Prep', 0, 0, 'Unvalidated student needing math help.', 'Mathematics', 0],
        ['STU-DEACT-03', 'Pedro Penduko', 'pedro.deact@tutorlink.ph', 'Junior High', 1, 1, 'Deactivated student account.', 'English', 0]
    ];

    $stUser = $pdo->prepare("INSERT OR REPLACE INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'student', ?)");
    $stStu = $pdo->prepare("INSERT OR REPLACE INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed, sessions_completed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($students as $s) {
        $stUser->execute([$s[0], $s[2], $studentPassword, $s[1]]);
        $stStu->execute($s);
    }

    // 2. Seed Tutors
    $tutorPassword = password_hash('tutor123', PASSWORD_BCRYPT);
    $tutors = [
        ['tut-1', 'Prof. Alex Rivera', 'alex@tutorlink.ph', 'AR', 5.0, 5, 350, 'Calculus, Physics, Mathematics', 'Visual & Diagrams', 'Licensed High School Educator', 1, 0, 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM'],
        ['tut-2', 'Dr. Elena Reyes', 'elena@tutorlink.ph', 'ER', 4.8, 3, 400, 'Chemistry, Physics', 'Step-by-Step Explanation', 'PhD in Chemistry', 1, 0, 'Mon,Wed,Fri', '10:00 AM - 04:00 PM', '10:00 AM, 01:00 PM']
    ];

    $stTutUser = $pdo->prepare("INSERT OR REPLACE INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'tutor', ?)");
    $stTut = $pdo->prepare("INSERT OR REPLACE INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, available_days, available_time_slots, availability_slots) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($tutors as $t) {
        $stTutUser->execute([$t[0], $t[2], $tutorPassword, $t[1]]);
        $stTut->execute($t);
    }

    // 3. Seed Matches
    $matches = [
        ['MATCH-PEND-1', 'STU-VAL-01', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'Calculus', 92, 'Pending Review', 'High subject compatibility.'],
        ['MATCH-APP-2', 'STU-UNVAL-02', 'Juan Dela Cruz', 'tut-1', 'Prof. Alex Rivera', 'Mathematics', 88, 'Approved', 'Matching schedule and learning style.'],
        ['MATCH-CANC-3', 'STU-DEACT-03', 'Pedro Penduko', 'tut-2', 'Dr. Elena Reyes', 'Chemistry', 75, 'Cancelled', 'Student request cancelled.']
    ];

    $stMatch = $pdo->prepare("INSERT OR REPLACE INTO matches (id, student_id, student_name, tutor_id, tutor_name, subject, score, status, match_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($matches as $m) {
        $stMatch->execute($m);
    }

    // 4. Seed Sessions (This Week, Last Week, This Month, Last Month)
    $today = date('Y-m-d');
    $thisMon = date('Y-m-d', strtotime('monday this week'));
    $thisWed = date('Y-m-d', strtotime('wednesday this week'));
    $lastWed = date('Y-m-d', strtotime('-1 week wednesday'));
    $lastMonthDate = date('Y-m-d', strtotime('-1 month wednesday'));

    $sessions = [
        ['SES-THIS-WK1', 'STU-VAL-01', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'Calculus', $thisWed, '09:00 AM - 10:00 AM', 60, null, 350, 35, 385, 'GC-THISWK111', 'Confirmed', 'Unpaid', 'Upcoming session this week.'],
        ['SES-THIS-WK2', 'STU-VAL-01', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'Physics', $thisMon, '02:00 PM - 03:00 PM', 60, date('Y-m-d H:i:s', strtotime('-1 hour')), 350, 35, 385, 'GC-THISWK222', 'In Progress', 'Unpaid', 'Live in progress session.'],
        ['SES-LAST-WK1', 'STU-VAL-01', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'Calculus', $lastWed, '09:00 AM - 10:00 AM', 60, $lastWed . ' 09:00:00', 350, 35, 385, 'GC-LASTWK111', 'Completed', 'Paid Out', 'Completed session last week.'],
        ['SES-LAST-MO1', 'STU-UNVAL-02', 'Juan Dela Cruz', 'tut-2', 'Dr. Elena Reyes', 'Chemistry', $lastMonthDate, '01:00 PM - 02:00 PM', 60, null, 400, 40, 440, 'GC-LASTMO111', 'Cancelled', 'Unpaid', 'Cancelled last month session.']
    ];

    $stSes = $pdo->prepare("INSERT OR REPLACE INTO sessions (id, student_id, student_name, tutor_id, tutor_name, subject, session_date, time_slot, duration_minutes, started_at, hourly_rate, commission_fee, total_paid, gcash_ref, status, payout_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($sessions as $s) {
        $stSes->execute($s);
    }

    // 5. Seed Payments
    $payments = [
        ['PAY-THIS-WK1', 'STU-VAL-01', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'GCash', 'GC-THISWK111', 385.00, 'Confirmed', 'Unpaid'],
        ['PAY-THIS-WK2', 'STU-VAL-01', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'GCash', 'GC-THISWK222', 385.00, 'Confirmed', 'Unpaid'],
        ['PAY-LAST-WK1', 'STU-VAL-01', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'GCash', 'GC-LASTWK111', 385.00, 'Confirmed', 'Paid Out'],
        ['PAY-LAST-MO1', 'STU-UNVAL-02', 'Juan Dela Cruz', 'tut-2', 'Dr. Elena Reyes', 'GCash', 'GC-LASTMO111', 440.00, 'Needs refund/review', 'Unpaid']
    ];

    $stPay = $pdo->prepare("INSERT OR REPLACE INTO payments (id, student_id, student_name, tutor_id, tutor_name, method, ref_no, amount, status, payout_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($payments as $p) {
        $stPay->execute($p);
    }

    // 6. Seed Notifications
    $notifs = [
        ['notif-seed-1', 'STU-VAL-01', 'student', 'student', 'STU-VAL-01', 'broadcast', 'Welcome to TutorLink', 'Your account has been seeded successfully.', 'normal', 0]
    ];
    $stNotif = $pdo->prepare("INSERT OR REPLACE INTO notifications (id, recipient_user_id, recipient_role, target_role, target, type, title, message, priority, is_read) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($notifs as $n) {
        $stNotif->execute($n);
    }

    echo "Seeding completed successfully.\n";
} catch (Exception $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
    exit(1);
}
