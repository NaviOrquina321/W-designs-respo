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

    echo json_encode([
        'status' => 'success',
        'sqlite_fallback' => !empty($isSqliteFallback),
        'data' => [
            'total_students' => $totalStudents,
            'total_tutors' => $totalTutors,
            'total_volume' => $totalVolume,
            'net_commission' => $totalCommission
        ]
    ]);
} catch (PDOException $e) {
    error_log("Admin KPI computation error: " . $e->getMessage());
    jsonError(500, 'Error computing Admin SQL metrics.');
}
