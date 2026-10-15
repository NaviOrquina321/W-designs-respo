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
    echo json_encode(['status' => 'success', 'data' => toCamelArray($students)]);
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

        $notifyValidated = false;
        if (isset($input['validated'])) {
            $isVal = filter_var($input['validated'], FILTER_VALIDATE_BOOLEAN);
            $fields[] = "`validated` = ?";
            $params[] = $isVal ? 1 : 0;
            if ($isVal) {
                $notifyValidated = true;
            }
        }
        if (isset($input['deactivated'])) {
            $fields[] = "`deactivated` = ?";
            $params[] = filter_var($input['deactivated'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }
        if (isset($input['name'])) {
            $newName = trim($input['name']);
            $fields[] = "`name` = ?";
            $params[] = $newName;

            $stmtU = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmtU->execute([$newName, $studentId]);
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

        if ($notifyValidated) {
            notify($pdo, 'student', $studentId, 'Account Validated', 'Your student account has been validated by Admin. You may now book sessions with tutors.');
        }

        echo json_encode(['status' => 'success', 'message' => 'Student updated by admin.']);
    } elseif ($user['role'] === 'student') {
        if ($user['id'] !== $studentId) {
            jsonError(403, 'You can only update your own profile.');
        }

        $fields = [];
        $params = [];

        if (isset($input['name'])) {
            $newName = trim($input['name']);
            if (empty($newName)) jsonError(400, 'Name cannot be empty.');
            $fields[] = "`name` = ?";
            $params[] = $newName;
            $_SESSION['name'] = $newName;

            $stmtU = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmtU->execute([$newName, $studentId]);
        }
        if (isset($input['grade'])) {
            $fields[] = "`grade` = ?";
            $params[] = trim($input['grade']);
        }
        if (isset($input['bio'])) {
            $fields[] = "`bio` = ?";
            $params[] = trim($input['bio']);
        }
        if (isset($input['subjects_needed']) || isset($input['subjectsNeeded'])) {
            $val = $input['subjects_needed'] ?? $input['subjectsNeeded'];
            $subVal = is_array($val) ? implode(', ', $val) : trim($val);
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
