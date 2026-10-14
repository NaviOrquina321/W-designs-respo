<?php
// api/notifications.php - Notifications REST API with 24-Hour Reminders & Single Mark Read
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();

    // Check & generate 24-hour session reminders (deduplicated)
    if ($user['role'] === 'student' || $user['role'] === 'tutor') {
        $roleCol = ($user['role'] === 'student') ? 'student_id' : 'tutor_id';
        $stSes = $pdo->prepare("SELECT * FROM sessions WHERE $roleCol = ? AND (status = 'Confirmed' OR status = 'In Progress')");
        $stSes->execute([$user['id']]);
        $activeSessions = $stSes->fetchAll();

        $now = time();
        foreach ($activeSessions as $ses) {
            $startTs = parseSlotStartTime($ses['session_date'], $ses['time_slot']);
            $diff = $startTs - $now;

            // Session starting within 24 hours (0 < diff <= 86400)
            if ($startTs > 0 && $diff > 0 && $diff <= 86400) {
                $remTitle = 'Upcoming Session Reminder';
                $targetName = ($user['role'] === 'student') ? $ses['student_name'] : $ses['tutor_name'];
                $otherName = ($user['role'] === 'student') ? $ses['tutor_name'] : $ses['student_name'];

                // Check if reminder already generated for this session & user
                $chkStr = "%[Session: {$ses['id']}]%";
                $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE target_role = ? AND (target_id = ? OR (target_id IS NULL AND target = ?)) AND title = ? AND message LIKE ?");
                $stChk->execute([$user['role'], $user['id'], $targetName, $remTitle, $chkStr]);

                if ($stChk->fetch()['cnt'] == 0) {
                    $remMsg = "Reminder: Your {$ses['subject']} session with $otherName starts on {$ses['session_date']} at {$ses['time_slot']}. [Session: {$ses['id']}]";
                    notify($pdo, $user['role'], $targetName, $remTitle, $remMsg, $user['id']);
                }
            }
        }
    }

    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT id, target_role, target, title, message, is_read, created_at FROM notifications ORDER BY created_at DESC");
        $notifications = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT id, target_role, target, title, message, is_read, created_at FROM notifications WHERE target_role = 'all' OR (target_role = ? AND (target_id = ? OR (target_id IS NULL AND target = ?))) ORDER BY created_at DESC");
        $stmt->execute([$user['role'], $user['id'], $user['name']]);
        $notifications = $stmt->fetchAll();
    }

    echo json_encode(['status' => 'success', 'data' => toCamelArray($notifications)]);
} elseif ($method === 'POST') {
    $user = requireRole(['admin']);
    $input = getInput();

    $targetRole = trim($input['target_role'] ?? ($input['targetRole'] ?? 'all'));
    $target = trim($input['target'] ?? 'All Users');
    $title = trim($input['title'] ?? 'System Broadcast');
    $message = trim($input['message'] ?? '');

    if (empty($message)) {
        jsonError(400, 'Notification message required.');
    }

    $notifId = newId('notif-');

    $stmt = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message, is_read) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$notifId, $targetRole, $target, $title, $message]);

    $stmtFetch = $pdo->prepare("SELECT id, target_role, target, title, message, is_read, created_at FROM notifications WHERE id = ?");
    $stmtFetch->execute([$notifId]);
    $row = $stmtFetch->fetch();

    echo json_encode([
        'status' => 'success',
        'message' => 'Notification broadcast sent successfully.',
        'data' => toCamelArray($row)
    ]);
} elseif ($method === 'PUT') {
    $user = requireLogin();
    $input = getInput();
    $notifId = trim($input['id'] ?? ($_GET['id'] ?? ''));

    if (!empty($notifId)) {
        // Mark single notification as read
        if ($user['role'] === 'admin') {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->execute([$notifId]);
        } else {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (target_role = 'all' OR (target_role = ? AND (target_id = ? OR (target_id IS NULL AND target = ?))))");
            $stmt->execute([$notifId, $user['role'], $user['id'], $user['name']]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
    } else {
        // Mark all as read
        if ($user['role'] === 'admin') {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE target_role = 'admin'");
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE target_role = ? AND (target_id = ? OR (target_id IS NULL AND target = ?))");
            $stmt->execute([$user['role'], $user['id'], $user['name']]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Notifications marked as read.']);
    }
} else {
    jsonError(405, 'Method not allowed');
}
?>