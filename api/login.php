<?php
// api/login.php - Real Authentication & Password Verification
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $email = strtolower(trim($input['email'] ?? ''));
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please provide both email and password.']);
        exit();
    }

    // Lookup user in users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password. User account not found.']);
        exit();
    }

    // Verify password hash
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid password. Please check your credentials.']);
        exit();
    }

    // Fetch detail record based on role
    $details = null;
    if ($user['role'] === 'student') {
        $st = $pdo->prepare("SELECT * FROM students WHERE id = ? OR email = ?");
        $st->execute([$user['user_id'], $user['email']]);
        $details = $st->fetch();
    } elseif ($user['role'] === 'tutor') {
        $st = $pdo->prepare("SELECT * FROM tutors WHERE id = ? OR email = ?");
        $st->execute([$user['user_id'], $user['email']]);
        $details = $st->fetch();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => $user['user_id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
            'details' => $details
        ]
    ]);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>