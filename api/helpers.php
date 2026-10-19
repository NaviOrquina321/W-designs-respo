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
    if (isset($GLOBALS['_MOCK_INPUT']) && is_array($GLOBALS['_MOCK_INPUT'])) {
        return $GLOBALS['_MOCK_INPUT'];
    }
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    return is_array($input) ? $input : (is_array($_POST) ? $_POST : []);
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

function parseSlotDurationMinutes($slotStr) {
    if (empty($slotStr)) return 60;
    $parts = explode('-', $slotStr);
    if (count($parts) < 2) return 60;
    $startStr = trim($parts[0]);
    $endStr = trim($parts[1]);
    $dummyDate = '2026-01-01 ';
    $dtStart = DateTime::createFromFormat('Y-m-d h:i A', $dummyDate . $startStr, new DateTimeZone('Asia/Manila'));
    if (!$dtStart) $dtStart = DateTime::createFromFormat('Y-m-d H:i', $dummyDate . $startStr, new DateTimeZone('Asia/Manila'));

    $dtEnd = DateTime::createFromFormat('Y-m-d h:i A', $dummyDate . $endStr, new DateTimeZone('Asia/Manila'));
    if (!$dtEnd) $dtEnd = DateTime::createFromFormat('Y-m-d H:i', $dummyDate . $endStr, new DateTimeZone('Asia/Manila'));

    if ($dtStart && $dtEnd) {
        $diffSeconds = $dtEnd->getTimestamp() - $dtStart->getTimestamp();
        if ($diffSeconds > 0) {
            return (int)round($diffSeconds / 60);
        }
    }
    return 60;
}

function checkSessionTimerThresholds($pdo, $sessions) {
    if (!$pdo || empty($sessions)) return;
    $now = time();

    foreach ($sessions as $session) {
        if (($session['status'] ?? '') !== 'In Progress' || empty($session['started_at'])) {
            continue;
        }

        $startedTs = strtotime($session['started_at']);
        if (!$startedTs) continue;

        $durationMins = intval($session['duration_minutes'] ?? 60);
        if ($durationMins <= 0) $durationMins = 60;
        $durationSecs = $durationMins * 60;

        $elapsedSecs = $now - $startedTs;
        $remainingSecs = $durationSecs - $elapsedSecs;

        $sessionId = $session['id'];
        $subject = $session['subject'] ?? 'Tutoring';
        $studentId = $session['student_id'] ?? null;
        $tutorId = $session['tutor_id'] ?? null;

        // 5 Minutes Left Threshold
        if ($remainingSecs <= 300) {
            if ($studentId) {
                notify($pdo, 'student', $studentId, '5 Minutes Remaining', "Only 5 minutes remaining for your $subject session.", [
                    'type' => 'session_timer_warn',
                    'related_session_id' => $sessionId,
                    'dedupe_key' => "timer-5min-$sessionId-$studentId"
                ]);
            }
            if ($tutorId) {
                notify($pdo, 'tutor', $tutorId, '5 Minutes Remaining', "Only 5 minutes remaining for your $subject session.", [
                    'type' => 'session_timer_warn',
                    'related_session_id' => $sessionId,
                    'dedupe_key' => "timer-5min-$sessionId-$tutorId"
                ]);
            }
        }

        // 0 Minutes Left Threshold (Time is up)
        if ($remainingSecs <= 0) {
            if ($studentId) {
                notify($pdo, 'student', $studentId, 'Session Time Is Up', "Time is up for your $subject session.", [
                    'type' => 'session_timer_up',
                    'related_session_id' => $sessionId,
                    'dedupe_key' => "timer-0min-$sessionId-$studentId"
                ]);
            }
            if ($tutorId) {
                notify($pdo, 'tutor', $tutorId, 'Session Time Is Up', "Time is up for your $subject session. Please end the session when finished.", [
                    'type' => 'session_timer_up',
                    'related_session_id' => $sessionId,
                    'dedupe_key' => "timer-0min-$sessionId-$tutorId"
                ]);
            }
        }
    }
}

function snakeToCamel($string) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
}

function notify($pdo, $role, $target, $title, $message, $meta = []) {
    if (!$pdo) return false;
    $id = 'notif-' . bin2hex(random_bytes(6));

    $type = $meta['type'] ?? 'general';
    $recipientUserId = $meta['recipient_user_id'] ?? ($role === 'all' || $target === 'all' ? null : $target);
    $recipientRole = $meta['recipient_role'] ?? ($role === 'all' ? null : $role);
    $relatedSessionId = $meta['related_session_id'] ?? null;
    $relatedPaymentId = $meta['related_payment_id'] ?? null;
    $relatedUserId = $meta['related_user_id'] ?? null;
    $actionUrl = $meta['action_url'] ?? null;
    $priority = $meta['priority'] ?? 'normal';
    $dedupeKey = $meta['dedupe_key'] ?? null;

    if ($dedupeKey) {
        $chk = $pdo->prepare("SELECT id FROM notifications WHERE dedupe_key = ?");
        $chk->execute([$dedupeKey]);
        if ($chk->fetch()) {
            return false;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (
            id, recipient_user_id, recipient_role, target_role, target, type, title, message,
            related_session_id, related_payment_id, related_user_id, action_url, priority, dedupe_key, is_read
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

        $stmt->execute([
            $id, $recipientUserId, $recipientRole, $role, $target, $type, $title, $message,
            $relatedSessionId, $relatedPaymentId, $relatedUserId, $actionUrl, $priority, $dedupeKey
        ]);
        return $id;
    } catch (Exception $e) {
        error_log("notify() error: " . $e->getMessage());
        return false;
    }
}

function notifyMany($pdo, $recipients, $type, $title, $message, $meta = []) {
    if (!$pdo || empty($recipients)) return [];
    $ids = [];
    foreach ($recipients as $rec) {
        $rRole = is_array($rec) ? ($rec['role'] ?? 'all') : 'all';
        $rTarget = is_array($rec) ? ($rec['id'] ?? $rec['target'] ?? 'all') : $rec;
        $m = array_merge($meta, [
            'recipient_user_id' => $rTarget !== 'all' ? $rTarget : null,
            'recipient_role' => $rRole !== 'all' ? $rRole : null,
            'type' => $type
        ]);
        if (isset($meta['dedupe_prefix'])) {
            $m['dedupe_key'] = $meta['dedupe_prefix'] . '-' . $rTarget;
        }
        $res = notify($pdo, $rRole, $rTarget, $title, $message, $m);
        if ($res) $ids[] = $res;
    }
    return $ids;
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
    $numKeys = ['amount', 'rating', 'hourlyRate', 'totalPaid', 'commissionFee', 'score', 'reviewsCount', 'sessionsCompleted', 'durationMinutes'];
    foreach ($numKeys as $nk) {
        if (isset($result[$nk]) && $result[$nk] !== null) {
            $result[$nk] = is_float($result[$nk] + 0) ? (float)$result[$nk] : (int)$result[$nk];
        }
    }

    if (!isset($result['serverNow'])) {
        $result['serverNow'] = date('c');
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

function logAdminAction($action, $target, $oldVal = null, $newVal = null) {
    global $pdo;
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        return;
    }
    $adminId = $_SESSION['user_id'] ?? 'ADMIN-001';
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_audit_log (admin_id, action, target, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $adminId,
            $action,
            $target,
            is_array($oldVal) || is_object($oldVal) ? json_encode($oldVal) : (string)$oldVal,
            is_array($newVal) || is_object($newVal) ? json_encode($newVal) : (string)$newVal
        ]);
    } catch (Exception $e) {
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}
