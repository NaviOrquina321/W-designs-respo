<?php
// api/tutors.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM tutors ORDER BY created_at DESC");
    $tutors = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $tutors]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('tut-' . rand(1, 99));
    $name = $input['name'] ?? '';
    $initials = $input['initials'] ?? '';
    $rating = $input['rating'] ?? 5.0;
    $reviewsCount = $input['reviews_count'] ?? 0;
    $hourlyRate = $input['hourly_rate'] ?? 350;
    $subjects = $input['subjects'] ?? '';
    $learningStyles = $input['learning_styles'] ?? '';
    $bio = $input['bio'] ?? '';
    $available = isset($input['available']) ? ($input['available'] ? 1 : 0) : 1;

    $stmt = $pdo->prepare("INSERT INTO tutors (id, name, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE hourly_rate=?, subjects=?, learning_styles=?, available=?");
    $stmt->execute([$id, $name, $initials, $rating, $reviewsCount, $hourlyRate, $subjects, $learningStyles, $bio, $available, $hourlyRate, $subjects, $learningStyles, $available]);

    echo json_encode(['status' => 'success', 'message' => 'Tutor profile updated successfully']);
}
?>
