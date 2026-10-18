<?php
// api/public_stats.php - Public Aggregate System Statistics Endpoint
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// Allow public browser/CDN caching for 60 seconds
header('Cache-Control: public, max-age=60');

try {
    // 1. Count active registered tutors
    $stTut = $pdo->query("SELECT COUNT(*) as cnt FROM tutors WHERE deactivated = 0");
    $totalTutors = intval($stTut->fetch()['cnt'] ?? 0);

    // 2. Count active registered students
    $stStu = $pdo->query("SELECT COUNT(*) as cnt FROM students WHERE deactivated = 0");
    $totalStudents = intval($stStu->fetch()['cnt'] ?? 0);

    // 3. Count completed tutoring sessions
    $stSes = $pdo->query("SELECT COUNT(*) as cnt FROM sessions WHERE status = 'Completed'");
    $completedSessions = intval($stSes->fetch()['cnt'] ?? 0);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'totalTutors' => $totalTutors,
            'totalStudents' => $totalStudents,
            'completedSessions' => $completedSessions
        ]
    ]);
} catch (Exception $e) {
    error_log("public_stats error: " . $e->getMessage());
    echo json_encode([
        'status' => 'success',
        'data' => [
            'totalTutors' => 0,
            'totalStudents' => 0,
            'completedSessions' => 0
        ]
    ]);
}
