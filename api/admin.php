<?php
// api/admin.php - Live SQL Admin KPI Calculations Endpoint
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$user = requireRole(['admin']);

try {
    // 1. Total Registered Students
    $stmtStudents = $pdo->query("SELECT COUNT(*) AS total_students FROM students");
    $totalStudents = intval($stmtStudents->fetch()['total_students']);

    // 2. Total Tutors
    $stmtTutors = $pdo->query("SELECT COUNT(*) AS total_tutors FROM tutors WHERE deactivated = 0");
    $totalTutors = intval($stmtTutors->fetch()['total_tutors']);

    // 3. Total Tutoring Volume (Sessions not cancelled)
    $stmtVolume = $pdo->query("SELECT COALESCE(SUM(total_paid), 0) AS total_volume FROM sessions WHERE status != 'Cancelled'");
    $totalVolume = intval($stmtVolume->fetch()['total_volume']);

    // 4. Platform Service Fee Commission
    $stmtCommission = $pdo->query("SELECT COALESCE(SUM(commission_fee), 0) AS total_commission FROM sessions WHERE status != 'Cancelled'");
    $totalCommission = intval($stmtCommission->fetch()['total_commission']);

    // Additional Count Cards
    $stmtTotalSes = $pdo->query("SELECT COUNT(*) AS cnt FROM sessions");
    $totalSessions = intval($stmtTotalSes->fetch()['cnt']);

    $stmtConfSes = $pdo->query("SELECT COUNT(*) AS cnt FROM sessions WHERE status = 'Confirmed'");
    $confirmedSessions = intval($stmtConfSes->fetch()['cnt']);

    $stmtProgSes = $pdo->query("SELECT COUNT(*) AS cnt FROM sessions WHERE status = 'In Progress'");
    $inProgressSessions = intval($stmtProgSes->fetch()['cnt']);

    $stmtCompSes = $pdo->query("SELECT COUNT(*) AS cnt FROM sessions WHERE status = 'Completed'");
    $completedSessions = intval($stmtCompSes->fetch()['cnt']);

    $stmtCancSes = $pdo->query("SELECT COUNT(*) AS cnt FROM sessions WHERE status = 'Cancelled'");
    $cancelledSessions = intval($stmtCancSes->fetch()['cnt']);

    $stmtPendMatches = $pdo->query("SELECT COUNT(*) AS cnt FROM matches WHERE status = 'Pending Review'");
    $pendingMatches = intval($stmtPendMatches->fetch()['cnt']);

    $stmtPendPay = $pdo->query("SELECT COUNT(*) AS cnt FROM payments WHERE status = 'Pending'");
    $pendingPayments = intval($stmtPendPay->fetch()['cnt']);

    // 5 Latest Sessions
    $stRecentSes = $pdo->query("SELECT id, student_name, tutor_name, subject, session_date, time_slot, total_paid, status, created_at FROM sessions ORDER BY created_at DESC LIMIT 5");
    $recentSessions = toCamelArray($stRecentSes->fetchAll());

    // 5 Latest Payments
    $stRecentPay = $pdo->query("SELECT id, student_name, tutor_name, method, ref_no, amount, status, created_at FROM payments ORDER BY created_at DESC LIMIT 5");
    $recentPayments = toCamelArray($stRecentPay->fetchAll());

    echo json_encode([
        'status' => 'success',
        'sqlite_fallback' => !empty($isSqliteFallback),
        'data' => [
            'total_students' => $totalStudents,
            'total_tutors' => $totalTutors,
            'total_volume' => $totalVolume,
            'net_commission' => $totalCommission,
            'total_sessions' => $totalSessions,
            'confirmed_sessions' => $confirmedSessions,
            'in_progress_sessions' => $inProgressSessions,
            'completed_sessions' => $completedSessions,
            'cancelled_sessions' => $cancelledSessions,
            'pending_matches' => $pendingMatches,
            'pending_payments' => $pendingPayments,
            'recent_sessions' => $recentSessions,
            'recent_payments' => $recentPayments
        ]
    ]);
} catch (PDOException $e) {
    error_log("Admin KPI computation error: " . $e->getMessage());
    jsonError(500, 'Error computing Admin SQL metrics.');
}
