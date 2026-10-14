<?php
// api/helpers.php - Core Security & API Utilities
// session handling is owned by api/config.php

// Set JSON output header for API endpoints
header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function ($e) {
    error_log("Unhandled exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error occurred.']);
    exit();
});

function jsonError($code, $message) {
    if (getenv('TEST_MODE') === '1' || defined('TEST_MODE')) {
        throw new Exception($message, $code);
    }
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit();
}

function getInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) ? $input : [];
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

function parseSlotStartTime($dateStr, $slotStr) {
    $parts = explode('-', $slotStr);
    $timePart = trim($parts[0]);
    $dt = DateTime::createFromFormat('Y-m-d h:i A', "$dateStr $timePart", new DateTimeZone('Asia/Manila'));
    if (!$dt) {
        $dt = DateTime::createFromFormat('Y-m-d H:i', "$dateStr $timePart", new DateTimeZone('Asia/Manila'));
    }
    return $dt ? $dt->getTimestamp() : 0;
}

function parseSlotEndTime($dateStr, $slotStr) {
    $parts = explode('-', $slotStr);
    $timePart = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
    $dt = DateTime::createFromFormat('Y-m-d h:i A', "$dateStr $timePart", new DateTimeZone('Asia/Manila'));
    if (!$dt) {
        $dt = DateTime::createFromFormat('Y-m-d H:i', "$dateStr $timePart", new DateTimeZone('Asia/Manila'));
    }
    // If no end time given, default to 1 hour after start
    if (!$dt) {
        $startTs = parseSlotStartTime($dateStr, $slotStr);
        return $startTs > 0 ? $startTs + 3600 : 0;
    }
    return $dt->getTimestamp();
}

function sanitizeCsvCell($val) {
    $str = strval($val ?? '');
    if (strlen($str) > 0 && in_array($str[0], ['=', '+', '-', '@'], true)) {
        return "'" . $str;
    }
    return $str;
}

function snakeToCamel($string) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
}

function notify($pdo, $role, $target, $title, $message) {
    if (!$pdo) return;
    $id = 'notif-' . bin2hex(random_bytes(6));
    $stmt = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message, is_read) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$id, $role, $target, $title, $message]);
}

function toCamelArray($data) {
    if (!is_array($data)) return $data;

    // Handle empty or indexed array of row objects
    if (empty($data)) return [];
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

    // Cast boolean flags
    $boolKeys = ['validated', 'deactivated', 'available', 'isRead'];
    foreach ($boolKeys as $bk) {
        if (isset($result[$bk])) {
            $result[$bk] = (bool)(int)$result[$bk];
        }
    }
    if (isset($result['isRead'])) {
        $result['read'] = $result['isRead'];
    }

    // Cast numeric fields
    $numKeys = ['amount', 'rating', 'hourlyRate', 'totalPaid', 'commissionFee', 'score', 'reviewsCount', 'sessionsCompleted'];
    foreach ($numKeys as $nk) {
        if (isset($result[$nk]) && $result[$nk] !== null) {
            $result[$nk] = is_float($result[$nk] + 0) ? (float)$result[$nk] : (int)$result[$nk];
        }
    }

    // Add UI compatibility aliases if present in row assoc array
    if (isset($result['sessionDate']) && !isset($result['date'])) {
        $result['date'] = $result['sessionDate'];
    }
    if (isset($result['createdAt']) && !isset($result['time'])) {
        $result['time'] = date('M d, Y h:i A', strtotime($result['createdAt']));
    }

    return $result;
}
