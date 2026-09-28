<?php
// api/admin.php - Live SQL Admin KPI Calculations Endpoint
require_once 'config.php';

try {
    // 1. Total Registered Students
    $stmtStudents = $pdo->query("SELECT COUNT(*) AS total_students FROM students");
    $totalStudents = intval($stmtStudents->fetch()['total_students']);

    // 2. Verified Tutors
    $stmtTutors = $pdo->query("SELECT COUNT(*) AS total_tutors FROM tutors");
    $totalTutors = intval($stmtTutors->fetch()['total_tutors']);

    // 3. Total Tutoring Volume
    $stmtVolume = $pdo->query("SELECT COALESCE(SUM(total_paid), 0) AS total_volume FROM sessions");
    $totalVolume = intval($stmtVolume->fetch()['total_volume']);

    // 4. Platform Commission (10%)
    $stmtCommission = $pdo->query("SELECT COALESCE(SUM(commission_fee), 0) AS total_commission FROM sessions");
    $totalCommission = intval($stmtCommission->fetch()['total_commission']);

    // If commission sum in sessions is 0, calculate as 10% of total volume
    if ($totalCommission === 0 && $totalVolume > 0) {
        $totalCommission = round($totalVolume * 0.10);
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_students' => $totalStudents,
            'total_tutors' => $totalTutors,
            'total_volume' => $totalVolume,
            'platform_commission' => $totalCommission
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error computing Admin SQL metrics: ' . $e->getMessage()
    ]);
}
?>
