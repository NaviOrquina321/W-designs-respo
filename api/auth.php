<?php
// api/auth.php - Real Authentication & Password Verification
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $data['action'] ?? 'login';

    if ($action === 'login') {
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Get role specific record
            $roleData = null;
            if ($user['role'] === 'student') {
                $stStmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
                $stStmt->execute([$email]);
                $roleData = $stStmt->fetch();
            } else if ($user['role'] === 'tutor') {
                $tutStmt = $pdo->prepare("SELECT * FROM tutors WHERE email = ?");
                $tutStmt->execute([$email]);
                $roleData = $tutStmt->fetch();
            }

            // Log login event in user_logs
            try {
                $logStmt = $pdo->prepare("INSERT INTO user_logs (user_id, user_name, email, role, action_type) VALUES (?, ?, ?, ?, 'LOGIN')");
                $logStmt->execute([$user['id'], $user['name'], $user['email'], $user['role']]);
            } catch (\Exception $e) { /* ignore log error */ }

            echo json_encode([
                'status' => 'success',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'profile' => $roleData
            ]);
            exit();
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password. Authentication failed.']);
            exit();
        }
    }

    if ($action === 'register') {
        $role = $data['role'] ?? 'student';
        $fullname = trim($data['fullname'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $specialty = trim($data['specialty'] ?? '');

        if (empty($fullname) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Name, email, and password are required.']);
            exit();
        }

        // Check if email already registered
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'An account with this email already exists.']);
            exit();
        }

        $id = ($role === 'student' ? 'STU-' : 'tut-') . rand(100, 999);
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users
        $uStmt = $pdo->prepare("INSERT INTO users (id, name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $uStmt->execute([$id, $fullname, $email, $passwordHash, $role]);

        if ($role === 'student') {
            $stStmt = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, bio, subjects_needed) VALUES (?, ?, ?, ?, 0, ?, ?)");
            $stStmt->execute([$id, $fullname, $email, $specialty, "$specialty student eager to learn.", $specialty]);
        } else {
            $initials = implode('', array_map(fn($n) => $n[0] ?? '', explode(' ', $fullname)));
            $tutStmt = $pdo->prepare("INSERT INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, diploma_status, tor_status, id_status, approval_status) VALUES (?, ?, ?, ?, 5.0, 0, 350, ?, 'Step-by-Step Explanation', ?, 1, 'Pending', 'Pending', 'Pending', 'Pending Review')");
            $tutStmt->execute([$id, $fullname, $email, $initials, $specialty, "$specialty Specialist tutor."]);
        }

        // Notify admin
        $notifId = 'notif-' . time() . '-' . rand(100, 999);
        $nStmt = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message) VALUES (?, 'admin', 'System Admin', 'New User Registration', ?)");
        $nStmt->execute([$notifId, "New $role registered: $fullname ($email)"]);

        // Log registration event in user_logs
        try {
            $logStmt = $pdo->prepare("INSERT INTO user_logs (user_id, user_name, email, role, action_type) VALUES (?, ?, ?, ?, 'REGISTER')");
            $logStmt->execute([$id, $fullname, $email, $role]);
        } catch (\Exception $e) { /* ignore log error */ }

        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful',
            'user' => [
                'id' => $id,
                'name' => $fullname,
                'email' => $email,
                'role' => $role
            ]
        ]);
        exit();
    }
}
?>