<?php
// api/login.php - Authentication Endpoint
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    jsonError(405, 'Method not allowed');
}

$input = getInput();
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    jsonError(400, 'Invalid email or password.');
}

// Lookup user in users table
$stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    jsonError(401, 'Invalid email or password.');
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    jsonError(401, 'Invalid email or password.');
}

// Each portal only signs in its own kind of user
if (TUTORLINK_PORTAL === 'admin' && $user['role'] !== 'admin') {
    jsonError(403, 'This is the administrator portal. Students and tutors sign in on the main TutorLink site.');
}
if (TUTORLINK_PORTAL === 'main' && $user['role'] === 'admin') {
    jsonError(403, 'Administrators sign in through the Admin Portal (admin-login.php).');
}

// Check deactivation status for students and tutors
if ($user['role'] === 'student') {
    $st = $pdo->prepare("SELECT deactivated FROM students WHERE id = ? OR email = ?");
    $st->execute([$user['user_id'], $user['email']]);
    $student = $st->fetch();
    if ($student && !empty($student['deactivated'])) {
        jsonError(403, 'Your account has been deactivated. Please contact support.');
    }
} elseif ($user['role'] === 'tutor') {
    $st = $pdo->prepare("SELECT deactivated FROM tutors WHERE id = ? OR email = ?");
    $st->execute([$user['user_id'], $user['email']]);
    $tutor = $st->fetch();
    if ($tutor && !empty($tutor['deactivated'])) {
        jsonError(403, 'Your account has been deactivated. Please contact support.');
    }
}

// Set session state
session_regenerate_id(true);
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['name'] = $user['name'];

echo json_encode([
    'status' => 'success',
    'message' => 'Login successful',
    'user' => [
        'id' => $user['user_id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role']
    ]
]);
?>