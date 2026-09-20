<?php
// api/students.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $students]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('STU-' . rand(100, 999));
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $grade = $input['grade'] ?? '';
    $validated = isset($input['validated']) ? ($input['validated'] ? 1 : 0) : 1;
    $bio = $input['bio'] ?? '';
    $subjectsNeeded = $input['subjects_needed'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, bio, subjects_needed)
                           VALUES (?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE name=?, grade=?, bio=?, subjects_needed=?");
    $stmt->execute([$id, $name, $email, $grade, $validated, $bio, $subjectsNeeded, $name, $grade, $bio, $subjectsNeeded]);

    echo json_encode(['status' => 'success', 'message' => 'Student saved successfully', 'id' => $id]);
}
?>
