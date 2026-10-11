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

set_exception_handler(function ($e) {
    error_log("Unhandled exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error occurred.']);
    exit();
});

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
    global $pdo;
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        jsonError(401, 'Unauthorized: Please log in.');
    }

    $userId = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Dynamic account deactivation check
    if ($role === 'student' && isset($pdo)) {
        $st = $pdo->prepare("SELECT deactivated FROM students WHERE id = ?");
        $st->execute([$userId]);
        $row = $st->fetch();
        if ($row && !empty($row['deactivated'])) {
            session_destroy();
            jsonError(403, 'Your student account has been deactivated by an administrator.');
        }
    } elseif ($role === 'tutor' && isset($pdo)) {
        $st = $pdo->prepare("SELECT deactivated FROM tutors WHERE id = ?");
        $st->execute([$userId]);
        $row = $st->fetch();
        if ($row && !empty($row['deactivated'])) {
            session_destroy();
            jsonError(403, 'Your tutor account has been deactivated by an administrator.');
        }
    }

    return [
        'id' => $userId,
        'role' => $role,
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

function snakeToCamel($string) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
}

function toCamelArray($data) {
    if (!is_array($data)) return $data;

    // Handle indexed array of row objects
    if (array_keys($data) === range(0, count($data) - 1)) {
        return array_map('toCamelArray', $data);
    }

    $result = [];
    foreach ($data as $key => $value) {
        $camelKey = is_string($key) ? snakeToCamel($key) : $key;
        if (is_array($value)) {
            $value = toCamelArray($value);
        }
        $result[$camelKey] = $value;
    }

    // Add UI compatibility aliases if present in row assoc array
    if (isset($result['sessionDate']) && !isset($result['date'])) {
        $result['date'] = $result['sessionDate'];
    }
    if (isset($result['createdAt']) && !isset($result['time'])) {
        $result['time'] = date('M d, Y h:i A', strtotime($result['createdAt']));
    }
    if (isset($result['createdTime']) && !isset($result['time'])) {
        $result['time'] = $result['createdTime'];
    }

    return $result;
}
?>