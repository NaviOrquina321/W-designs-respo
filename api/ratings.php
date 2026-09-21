<?php
// api/ratings.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $tutorId = $data['tutor_id'] ?? 'tut-1';
    $studentName = $data['student_name'] ?? 'Maria Santos';
    $subject = $data['subject'] ?? 'Mathematics';
    $stars = intval($data['stars'] ?? 5);
    $comment = trim($data['comment'] ?? '');

    $id = 'RAT-' . rand(100, 999);
    $stmt = $pdo->prepare("INSERT INTO ratings (id, tutor_id, student_name, subject, stars, comment) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $tutorId, $studentName, $subject, $stars, $comment]);

    // Recalculate average rating
    $avgStmt = $pdo->prepare("SELECT AVG(stars) as avg_rating, COUNT(*) as cnt FROM ratings WHERE tutor_id = ?");
    $avgStmt->execute([$tutorId]);
    $stats = $avgStmt->fetch();

    if ($stats && $stats['cnt'] > 0) {
        $newRating = round($stats['avg_rating'], 1);
        $newCount = $stats['cnt'];
        $updateStmt = $pdo->prepare("UPDATE tutors SET rating = ?, reviews_count = ? WHERE id = ?");
        $updateStmt->execute([$newRating, $newCount, $tutorId]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Rating submitted successfully']);
    exit();
}
?>