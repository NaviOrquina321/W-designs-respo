<?php
// api/register.php - User Registration API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    jsonError(405, 'Method not allowed');
}

$input = getInput();
$role = strtolower(trim($input['role'] ?? 'student'));
$name = trim($input['name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';
$grade = trim($input['grade'] ?? ($input['specialty'] ?? 'Senior High'));
$subject = trim($input['subject'] ?? ($input['specialty'] ?? 'Mathematics'));

if (empty($name) || empty($email) || empty($password)) {
    jsonError(400, 'Please fill in all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError(400, 'Please enter a valid email address.');
}

if (strlen($password) < 6) {
    jsonError(400, 'Password must be at least 6 characters long.');
}

try {
    // Check duplicate email
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE LOWER(email) = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()['cnt'] > 0) {
        jsonError(400, 'This email address is already registered.');
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    if ($role === 'student') {
        $userId = newId('STU-');

        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'student', ?)");
        $stmtUser->execute([$userId, $email, $passwordHash, $name]);

        $stmtStu = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed, sessions_completed) VALUES (?, ?, ?, ?, 0, 0, ?, ?, 0)");
        $stmtStu->execute([
            $userId,
            $name,
            $email,
            $grade,
            "$grade Student eager to connect with expert tutors on TutorLink.",
            $subject
        ]);

        $notifId = newId('notif-');
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message) VALUES (?, 'admin', 'System Admin', 'New Student Registered', ?)");
        $stmtNotif->execute([$notifId, "New student registered: $name ($email)"]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'student';
        $_SESSION['name'] = $name;

        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful! Welcome to TutorLink.',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'student'
            ]
        ]);
    } else {
        $userId = newId('tut-');
        $initials = implode('', array_map(function($n) { return strtoupper($n[0] ?? ''); }, explode(' ', $name)));

        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'tutor', ?)");
        $stmtUser->execute([$userId, $email, $passwordHash, $name]);

        $stmtTut = $pdo->prepare("INSERT INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, diploma_status, tor_status, id_status, approval_status, available_days, available_time_slots, availability_slots) VALUES (?, ?, ?, ?, 0.0, 0, 350, ?, 'Step-by-Step Explanation', ?, 1, 0, 'Pending', 'Pending', 'Pending', 'Pending Review', 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')");
        $stmtTut->execute([
            $userId,
            $name,
            $email,
            $initials,
            $subject,
            "$grade Specialist Tutor in $subject. Dedicated to student growth."
        ]);

        $notifId = newId('notif-');
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message) VALUES (?, 'admin', 'System Admin', 'New Tutor Registered', ?)");
        $stmtNotif->execute([$notifId, "New tutor registered: $name ($email) - Verification Pending."]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'tutor';
        $_SESSION['name'] = $name;

        echo json_encode([
            'status' => 'success',
            'message' => 'Tutor registration complete! Your application is pending review.',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'tutor'
            ]
        ]);
    }
} catch (PDOException $e) {
    error_log("Registration DB error: " . $e->getMessage());
    jsonError(500, 'Database error during registration.');
}
?>