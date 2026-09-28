<?php
// api/students.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$isSQLite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $students]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('STU-' . rand(100, 999));
    $name = $input['name'] ?? '';
    $email = strtolower(trim($input['email'] ?? ''));
    $grade = $input['grade'] ?? '';
    $validated = isset($input['validated']) ? ($input['validated'] ? 1 : 0) : 0;
    $deactivated = isset($input['deactivated']) ? ($input['deactivated'] ? 1 : 0) : 0;
    $bio = $input['bio'] ?? '';
    $subjectsNeeded = $input['subjects_needed'] ?? ($input['subjectsNeeded'] ?? '');

    if ($isSQLite) {
        $stmt = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                               ON CONFLICT(email) DO UPDATE SET name=excluded.name, grade=excluded.grade, validated=excluded.validated, deactivated=excluded.deactivated, bio=excluded.bio, subjects_needed=excluded.subjects_needed");
        $stmt->execute([$id, $name, $email, $grade, $validated, $deactivated, $bio, $subjectsNeeded]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE name=?, grade=?, validated=?, deactivated=?, bio=?, subjects_needed=?");
        $stmt->execute([$id, $name, $email, $grade, $validated, $deactivated, $bio, $subjectsNeeded, $name, $grade, $validated, $deactivated, $bio, $subjectsNeeded]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Student saved successfully', 'id' => $id]);
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Student ID is required']);
        exit();
    }

    if (isset($input['validated'])) {
        $stmt = $pdo->prepare("UPDATE students SET validated = ? WHERE id = ?");
        $stmt->execute([$input['validated'] ? 1 : 0, $id]);
    }

    if (isset($input['deactivated'])) {
        $stmt = $pdo->prepare("UPDATE students SET deactivated = ? WHERE id = ?");
        $stmt->execute([$input['deactivated'] ? 1 : 0, $id]);
    }

    if (isset($input['name'])) {
        $stmt = $pdo->prepare("UPDATE students SET name = ?, grade = ?, bio = ?, subjects_needed = ? WHERE id = ?");
        $stmt->execute([
            $input['name'],
            $input['grade'] ?? '',
            $input['bio'] ?? '',
            $input['subjects_needed'] ?? '',
            $id
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Student updated successfully']);
}
?>