<?php
// api/students.php - Students REST API with Admin Audit & FDD Validation
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
        jsonError(422, 'Student ID required.');
    }

    $stCurr = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stCurr->execute([$studentId]);
    $student = $stCurr->fetch();

    if (!$student) {
        jsonError(404, 'Student not found.');
    }

    if ($user['role'] === 'admin') {
        $fields = [];
        $params = [];
        $oldState = [
            'validated' => (int)$student['validated'],
            'deactivated' => (int)$student['deactivated'],
            'name' => $student['name'],
            'grade' => $student['grade'],
            'subjects_needed' => $student['subjects_needed']
        ];
        $newState = $oldState;

        if (isset($input['validated'])) {
            $isVal = filter_var($input['validated'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $fields[] = "`validated` = ?";
            $params[] = $isVal;
            $newState['validated'] = $isVal;

            if ($isVal !== (int)$student['validated']) {
                $valAction = $isVal ? 'Validate Student List' : 'Unvalidate Student';
                notify($pdo, 'student', $studentId, 'Account Validation Status Updated', $isVal ? 'Your account has been validated by Admin.' : 'Your validation status has been set to unvalidated by Admin.');
                logAdminAction($valAction, $studentId, ['validated' => $student['validated']], ['validated' => $isVal]);
            }
        }

        if (isset($input['deactivated'])) {
            $isDeact = filter_var($input['deactivated'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $fields[] = "`deactivated` = ?";
            $params[] = $isDeact;
            $newState['deactivated'] = $isDeact;

            if ($isDeact !== (int)$student['deactivated']) {
                $deactAction = $isDeact ? 'Deactivate Student' : 'Reactivate Student';
                notify($pdo, 'student', $studentId, 'Account Status Updated', $isDeact ? 'Your account has been deactivated by an administrator.' : 'Your account has been reactivated. You may now log in.');
                logAdminAction($deactAction, $studentId, ['deactivated' => $student['deactivated']], ['deactivated' => $isDeact]);
            }
        }

        if (isset($input['name'])) {
            $newName = trim($input['name']);
            if (empty($newName)) {
                jsonError(422, 'Student name cannot be empty.');
            }
            $fields[] = "`name` = ?";
            $params[] = $newName;
            $newState['name'] = $newName;

            $stmtU = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmtU->execute([$newName, $studentId]);
        }

        if (isset($input['grade'])) {
            $newGrade = trim($input['grade']);
            if (empty($newGrade)) {
                jsonError(422, 'Grade level cannot be empty.');
            }
            $fields[] = "`grade` = ?";
            $params[] = $newGrade;
            $newState['grade'] = $newGrade;
        }

        if (isset($input['subjects_needed']) || isset($input['subjectsNeeded'])) {
            $val = $input['subjects_needed'] ?? $input['subjectsNeeded'];
            $subVal = is_array($val) ? implode(', ', $val) : trim($val);
            $fields[] = "`subjects_needed` = ?";
            $params[] = $subVal;
            $newState['subjects_needed'] = $subVal;
        }

        if (isset($input['bio'])) {
            $fields[] = "`bio` = ?";
            $params[] = trim($input['bio']);
        }

        if (empty($fields)) {
            jsonError(422, 'No valid fields provided for update.');
        }

        $params[] = $studentId;
        $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        logAdminAction('Validate Student List', $studentId, $oldState, $newState);

        echo json_encode(['status' => 'success', 'message' => 'Student record updated successfully.']);
    } elseif ($user['role'] === 'student') {
        if ($user['id'] !== $studentId) {
            jsonError(403, 'You can only update your own profile.');
        }

        $fields = [];
        $params = [];

        if (isset($input['name'])) {
            $newName = trim($input['name']);
            if (empty($newName)) jsonError(422, 'Name cannot be empty.');
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
            jsonError(422, 'No valid fields provided for update.');
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
