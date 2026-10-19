<?php
// api/register.php - User & Admin Registration API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    jsonError(405, 'Method not allowed');
}

$input = getInput();
$role = strtolower(trim($input['role'] ?? ''));
$name = trim($input['name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';
$grade = trim($input['grade'] ?? ($input['specialty'] ?? 'Senior High'));
$subject = trim($input['subject'] ?? ($input['specialty'] ?? 'Mathematics'));

if (!in_array($role, ['student', 'tutor', 'admin'], true)) {
    jsonError(422, 'Role must be student, tutor, or admin.');
}

if ($role === 'admin') {
    // Only an existing authenticated admin can create another admin account
    $currentAdmin = requireRole(['admin']);
}

if (empty($name) || empty($email) || empty($password)) {
    jsonError(422, 'Please fill in all required fields.');
}

if (mb_strlen($name) > 100) {
    jsonError(422, 'Full name cannot exceed 100 characters.');
}

if (mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError(422, 'Please enter a valid email address (max 100 characters).');
}

if (strlen($password) < 8) {
    jsonError(422, 'Password must be at least 8 characters long.');
}

// Validate tutor subject against catalog
if ($role === 'tutor') {
    $stmtCat = $pdo->prepare("SELECT COUNT(*) as cnt FROM subjects WHERE LOWER(name) = ?");
    $stmtCat->execute([strtolower($subject)]);
    if ($stmtCat->fetch()['cnt'] == 0) {
        jsonError(422, "Subject '$subject' is not in the subjects catalog.");
    }
}

try {
    // Check duplicate email
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE LOWER(email) = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()['cnt'] > 0) {
        jsonError(422, 'This email address is already registered.');
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $pdo->beginTransaction();

    if ($role === 'admin') {
        $userId = newId('ADMIN-');

        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'admin', ?)");
        $stmtUser->execute([$userId, $email, $passwordHash, $name]);

        $stmtAdmin = $pdo->prepare("INSERT INTO admins (id, name, email, role) VALUES (?, ?, ?, 'admin')");
        $stmtAdmin->execute([$userId, $name, $email]);

        logAdminAction('Create Admin Account', $userId, null, ['name' => $name, 'email' => $email]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'New administrator account created successfully.',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'admin'
            ]
        ]);
    } elseif ($role === 'student') {
        $userId = newId('STU-');

        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'student', ?)");
        $stmtUser->execute([$userId, $email, $passwordHash, $name]);

        $stmtStu = $pdo->prepare("INSERT INTO students (id, name, email, grade, validated, deactivated, bio, subjects_needed, sessions_completed) VALUES (?, ?, ?, ?, 1, 0, ?, ?, 0)");
        $stmtStu->execute([
            $userId,
            $name,
            $email,
            $grade,
            "$grade Student eager to connect with expert tutors on TutorLink.",
            $subject
        ]);

        notify($pdo, 'admin', 'ADMIN-001', 'New Student Registered', "New student registered: $name ($email)");

        $pdo->commit();

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
    } else { // tutor
        $userId = newId('tut-');
        $rawInitials = implode('', array_map(function($n) { return strtoupper($n[0] ?? ''); }, explode(' ', $name)));
        $initials = mb_substr($rawInitials, 0, 3);

        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, email, password_hash, role, name) VALUES (?, ?, ?, 'tutor', ?)");
        $stmtUser->execute([$userId, $email, $passwordHash, $name]);

        $stmtTut = $pdo->prepare("INSERT INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, available_days, available_time_slots, availability_slots) VALUES (?, ?, ?, ?, 0.0, 0, 350, ?, 'Step-by-Step Explanation', ?, 1, 0, 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')");
        $stmtTut->execute([
            $userId,
            $name,
            $email,
            $initials,
            $subject,
            "$grade Specialist Tutor in $subject. Dedicated to student growth."
        ]);

        notify($pdo, 'admin', 'ADMIN-001', 'New Tutor Registered', "New tutor registered: $name ($email).");

        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'tutor';
        $_SESSION['name'] = $name;

        echo json_encode([
            'status' => 'success',
            'message' => 'Tutor registration complete! Welcome to TutorLink.',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'tutor'
            ]
        ]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Registration error: " . $e->getMessage());
    jsonError(500, 'Registration failed. Please try again later.');
}
