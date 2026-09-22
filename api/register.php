<?php
// api/register.php - User Registration API
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $role = strtolower(trim($input['role'] ?? 'student'));
    $name = trim($input['name'] ?? '');
    $email = strtolower(trim($input['email'] ?? ''));
    $password = $input['password'] ?? '';
    $specialty = trim($input['specialty'] ?? '');

    // Files status if tutor registration
    $diplomaStatus = $input['diploma_status'] ?? 'Pending';
    $torStatus = $input['tor_status'] ?? 'Pending';
    $idStatus = $input['id_status'] ?? 'Pending';

    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit();
    }

    // Check if email already registered
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE LOWER(email) = ?");
    $stmt->execute([$email]);
    $res = $stmt->fetch();
    if ($res['cnt'] > 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'This email address is already registered.']);
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    if ($role === 'student') {
        $userId = 'STU-' . rand(1000, 9999);

        // 1. Insert into users table
        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'student', ?)");
        $stmtUser->execute([$userId, $email, $passwordHash, $name]);

        // 2. Insert into students table (validated defaults to 0 for admin gating)
        $stmtStu = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed, sessions_completed) VALUES (?, ?, ?, ?, 0, 0, ?, ?, 0)");
        $stmtStu->execute([
            $userId,
            $name,
            $email,
            $specialty,
            "$specialty Student eager to connect with expert tutors on TutorLink.",
            $specialty
        ]);

        // 3. Create Admin Notification
        $notifId = 'notif-' . time() . '-' . rand(100, 999);
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message) VALUES (?, 'admin', 'System Admin', 'New Student Registered', ?)");
        $stmtNotif->execute([$notifId, "New student registered: $name ($email)"]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful! Welcome to TutorLink.',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'student',
                'validated' => false
            ]
        ]);
    } else {
        $userId = 'tut-' . rand(1000, 9999);
        $initials = implode('', array_map(function($n) { return strtoupper($n[0] ?? ''); }, explode(' ', $name)));

        // 1. Insert into users table
        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'tutor', ?)");
        $stmtUser->execute([$userId, $email, $passwordHash, $name]);

        // 2. Insert into tutors table
        $stmtTut = $pdo->prepare("INSERT INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, diploma_status, tor_status, id_status, approval_status, available_days, available_time_slots, availability_slots) VALUES (?, ?, ?, ?, 0.0, 0, 350, ?, 'Step-by-Step Explanation', ?, 1, 0, ?, ?, ?, 'Pending Review', 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')");
        $stmtTut->execute([
            $userId,
            $name,
            $email,
            $initials,
            $specialty,
            "$specialty Specialist tutor. Dedicated to student growth.",
            $diplomaStatus,
            $torStatus,
            $idStatus
        ]);

        // 3. Create Admin Notification
        $notifId = 'notif-' . time() . '-' . rand(100, 999);
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message) VALUES (?, 'admin', 'System Admin', 'New Tutor Registered', ?)");
        $stmtNotif->execute([$notifId, "New tutor registered: $name ($email) - Verification Pending."]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Tutor registration complete! Your application is pending review.',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'tutor',
                'approval_status' => 'Pending Review'
            ]
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>