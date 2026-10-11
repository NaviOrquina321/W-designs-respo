<?php
// api/students.php - Students REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT id, name, email, grade, validated, deactivated, bio, subjects_needed, sessions_completed, created_at FROM students ORDER BY created_at DESC");
        $students = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, grade, validated, deactivated, bio, subjects_needed, sessions_completed, created_at FROM students WHERE id = ? OR LOWER(email) = ?");
        $stmt->execute([$user['id'], strtolower($user['email'])]);
        $students = $stmt->fetchAll();
    }
    echo json_encode(['status' => 'success', 'data' => $students]);
} elseif ($method === 'PUT') {
    $user = requireLogin();
    $input = getInput();
    $studentId = trim($input['id'] ?? '');

    if (empty($studentId)) {
        jsonError(400, 'Student ID required.');
    }

    if ($user['role'] === 'admin') {
        $fields = [];
        $params = [];

        if (isset($input['validated'])) {
            $fields[] = "`validated` = ?";
            $params[] = $input['validated'] ? 1 : 0;
        }
        if (isset($input['deactivated'])) {
            $fields[] = "`deactivated` = ?";
            $params[] = $input['deactivated'] ? 1 : 0;
        }
        if (isset($input['name'])) {
            $fields[] = "`name` = ?";
            $params[] = trim($input['name']);
        }
        if (isset($input['grade'])) {
            $fields[] = "`grade` = ?";
            $params[] = trim($input['grade']);
        }
        if (isset($input['bio'])) {
            $fields[] = "`bio` = ?";
            $params[] = trim($input['bio']);
        }

        if (empty($fields)) {
            jsonError(400, 'No valid fields provided for update.');
        }

        $params[] = $studentId;
        $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Student updated by admin.']);
    } elseif ($user['role'] === 'student') {
        if ($user['id'] !== $studentId) {
            jsonError(403, 'You can only update your own profile.');
        }

        $fields = [];
        $params = [];

        if (isset($input['name'])) {
            $fields[] = "`name` = ?";
            $params[] = trim($input['name']);
            $_SESSION['name'] = trim($input['name']);
        }
        if (isset($input['grade'])) {
            $fields[] = "`grade` = ?";
            $params[] = trim($input['grade']);
        }
        if (isset($input['bio'])) {
            $fields[] = "`bio` = ?";
            $params[] = trim($input['bio']);
        }
        if (isset($input['subjects_needed'])) {
            $subVal = is_array($input['subjects_needed']) ? implode(', ', $input['subjects_needed']) : trim($input['subjects_needed']);
            $fields[] = "`subjects_needed` = ?";
            $params[] = $subVal;
        }

        if (empty($fields)) {
            jsonError(400, 'No valid fields provided for update.');
        }

        $params[] = $studentId;
        $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
    } else {
        jsonError(403, 'Forbidden action.');
    }
} else {
    jsonError(405, 'Method not allowed');
}
?>