<?php
// api/login.php - Authentication Endpoint with Rate Limiting & Timing Attack Protection
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
    jsonError(400, 'Please enter email and password.');
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$now = time();
$cutoff = $now - 900; // 15 minutes window

// Rate limit check: 5 failed attempts per 15 minutes
$stLock = $pdo->prepare("SELECT COUNT(*) as cnt FROM failed_login_attempts WHERE email = ? AND ip_address = ? AND attempt_time > ?");
$stLock->execute([$email, $ip, $cutoff]);
if ($stLock->fetch()['cnt'] >= 5) {
    jsonError(429, 'Too many failed login attempts. Please try again in 15 minutes.');
}

// Lookup user
$stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

$dummyHash = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1e8c9gS/p.U.pGZ40U45LqU3q1r5nGO';

if (!$user) {
    password_verify($password, $dummyHash); // Timing attack mitigation
    $stFail = $pdo->prepare("INSERT INTO failed_login_attempts (email, ip_address, attempt_time) VALUES (?, ?, ?)");
    $stFail->execute([$email, $ip, $now]);
    jsonError(401, 'Invalid email or password.');
}

if (!password_verify($password, $user['password_hash'])) {
    $stFail = $pdo->prepare("INSERT INTO failed_login_attempts (email, ip_address, attempt_time) VALUES (?, ?, ?)");
    $stFail->execute([$email, $ip, $now]);
    jsonError(401, 'Invalid email or password.');
}

// Separate login gates for Admin vs Main site
$isMainPortal = (session_name() === 'TUTORLINK_MAIN');
if ($isMainPortal && $user['role'] === 'admin') {
    jsonError(403, 'Administrator accounts must log in via the Admin Portal at admin-login.php.');
}
if (!$isMainPortal && $user['role'] !== 'admin') {
    jsonError(403, 'Only administrator accounts may log in to the Admin Portal.');
}

// Check deactivation status
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

// Clear failed attempts on successful authentication
$stClr = $pdo->prepare("DELETE FROM failed_login_attempts WHERE email = ? AND ip_address = ?");
$stClr->execute([$email, $ip]);

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
