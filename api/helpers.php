<?php
// api/helpers.php - Core Security & API Utilities
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session cookie defaults
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Set JSON output header for API endpoints
header('Content-Type: application/json; charset=utf-8');

function jsonError($code, $message) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit();
}

function getInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }
    return $input ?? [];
}

function requireLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        jsonError(401, 'Unauthorized: Please log in.');
    }
    return [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email'] ?? '',
        'name' => $_SESSION['name'] ?? ''
    ];
}

function requireRole($allowedRoles) {
    $user = requireLogin();
    if (!in_array($user['role'], (array)$allowedRoles, true)) {
        jsonError(403, 'Forbidden: Insufficient privileges.');
    }
    return $user;
}

function newId($prefix = '') {
    return $prefix . bin2hex(random_bytes(6));
}
?>