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
    $isNew = isset($input['is_new_registration']) && $input['is_new_registration'];

    $stmt = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, bio, subjects_needed)
                           VALUES (?, ?, ?, ?, ?, ?, ?)
                           ON CONFLICT(id) DO UPDATE SET name=excluded.name, grade=excluded.grade, bio=excluded.bio, subjects_needed=excluded.subjects_needed");
    $stmt->execute([$id, $name, $email, $grade, $validated, $bio, $subjectsNeeded]);

    if ($isNew) {
        $notifId = 'notif-' . time() . '-' . rand(100, 999);
        $notifStmt = $pdo->prepare("INSERT INTO notifications (id, target, title, message) VALUES (?, 'admin', 'New Student Registered', ?)");
        $notifStmt->execute([$notifId, "New student registered: $name ($email)"]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Student saved successfully', 'id' => $id]);
}
?>