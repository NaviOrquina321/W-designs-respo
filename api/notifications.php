<?php
// api/notifications.php - Notifications REST API with Performance Optimizations
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    $userId = $user['id'];
    $userRole = $user['role'];

    // Rate-limit 24-hour reminder check to once per minute per user
    $lastCheck = $_SESSION['last_reminder_check'] ?? 0;
    if (time() - $lastCheck >= 60) {
        $_SESSION['last_reminder_check'] = time();

        if ($userRole === 'student' || $userRole === 'tutor') {
            $roleCol = ($userRole === 'student') ? 'student_id' : 'tutor_id';
            $stSes = $pdo->prepare("SELECT * FROM sessions WHERE $roleCol = ? AND (status = 'Confirmed' OR status = 'In Progress')");
            $stSes->execute([$userId]);
            $activeSessions = $stSes->fetchAll();

            $now = time();
            foreach ($activeSessions as $ses) {
                $startTs = parseSlotStartTime($ses['session_date'], $ses['time_slot']);
                $diff = $startTs - $now;

                if ($startTs > 0 && $diff > 0 && $diff <= 86400) {
                    $targetUserId = ($userRole === 'student') ? $ses['student_id'] : $ses['tutor_id'];
                    $otherName = ($userRole === 'student') ? $ses['tutor_name'] : $ses['student_name'];

                    if ($diff <= 3600) {
                        $remType = 'session_reminder_1h';
                        $remTitle = 'Session Starting Soon (1 Hour)';
                        $remMsg = "Urgent: Your {$ses['subject']} session with $otherName starts in less than an hour at {$ses['time_slot']}.";
                    } else {
                        $remType = 'session_reminder_24h';
                        $remTitle = 'Upcoming Session Reminder (24 Hours)';
                        $remMsg = "Reminder: Your {$ses['subject']} session with $otherName is tomorrow on {$ses['session_date']} at {$ses['time_slot']}.";
                    }

                    $dedupeKey = "rem-{$ses['id']}-$userRole-$remType";
                    notify($pdo, $userRole, $targetUserId, $remTitle, $remMsg, [
                        'type' => $remType,
                        'recipient_user_id' => $targetUserId,
                        'recipient_role' => $userRole,
                        'related_session_id' => $ses['id'],
                        'priority' => 'high',
                        'dedupe_key' => $dedupeKey
                    ]);
                }
            }
        }
    }

    // Release session lock for non-blocking read-only polling
    session_write_close();

    if ($userRole === 'admin') {
        $sql = "SELECT n.id, n.recipient_user_id, n.recipient_role, n.target_role, n.target, n.type, n.title, n.message,
                       n.related_session_id, n.related_payment_id, n.related_user_id, n.action_url, n.priority, n.dedupe_key,
                       CASE WHEN nr.user_id IS NOT NULL OR n.is_read = 1 THEN 1 ELSE 0 END as is_read,
                       COALESCE(nr.read_at, n.read_at) as read_at,
                       n.created_at
                FROM notifications n
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                WHERE n.recipient_user_id = ?
                   OR n.target = ?
                   OR n.recipient_role = 'admin'
                   OR n.target_role = 'admin'
                   OR n.target_role = 'all'
                   OR n.target = 'All Users'
                ORDER BY n.created_at DESC
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
    } else {
        $sql = "SELECT n.id, n.recipient_user_id, n.recipient_role, n.target_role, n.target, n.type, n.title, n.message,
                       n.related_session_id, n.related_payment_id, n.related_user_id, n.action_url, n.priority, n.dedupe_key,
                       CASE WHEN nr.user_id IS NOT NULL OR n.is_read = 1 THEN 1 ELSE 0 END as is_read,
                       COALESCE(nr.read_at, n.read_at) as read_at,
                       n.created_at
                FROM notifications n
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                WHERE n.recipient_user_id = ?
                   OR n.target = ?
                   OR n.recipient_role = ?
                   OR (n.target_role = ? AND (n.target = ? OR n.target = 'All Users'))
                   OR n.target_role = 'all'
                   OR n.target = 'All Users'
                ORDER BY n.created_at DESC
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId, $userRole, $userRole, $userId]);
    }

    $notifications = $stmt->fetchAll();
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
} elseif ($method === 'DELETE') {
    $user = requireLogin();
    $userId = $user['id'];
    $userRole = $user['role'];
    $notifId = trim($_GET['id'] ?? '');

    if (empty($notifId)) {
        jsonError(400, 'Notification ID is required for deletion.');
    }

    if ($userRole === 'admin') {
        $stDel = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stDel->execute([$notifId]);
    } else {
        $stDel = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND (recipient_user_id = ? OR target = ?)");
        $stDel->execute([$notifId, $userId, $userId]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Notification deleted successfully.']);
} elseif ($method === 'PUT') {
    $user = requireLogin();
    $userId = $user['id'];
    $userRole = $user['role'];
    $input = getInput();
    $notifId = trim($input['id'] ?? ($_GET['id'] ?? ''));

    $isSQLite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
    $insertIgnore = $isSQLite ? "INSERT OR IGNORE" : "INSERT IGNORE";

    if (!empty($notifId)) {
        $stRead = $pdo->prepare("$insertIgnore INTO notification_reads (notification_id, user_id) VALUES (?, ?)");
        $stRead->execute([$notifId, $userId]);

        $stUpd = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND target = ?");
        $stUpd->execute([$notifId, $userId]);

        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
    } else {
        if ($userRole === 'admin') {
            $stNotifs = $pdo->query("SELECT id FROM notifications");
        } else {
            $stNotifs = $pdo->prepare("SELECT id FROM notifications WHERE target_role = 'all' OR target = 'All Users' OR (target_role = ? AND (target = ? OR target = 'All Users'))");
            $stNotifs->execute([$userRole, $userId]);
        }

        $allRows = $stNotifs->fetchAll();
        $stIns = $pdo->prepare("$insertIgnore INTO notification_reads (notification_id, user_id) VALUES (?, ?)");
        foreach ($allRows as $r) {
            $stIns->execute([$r['id'], $userId]);
        }

        echo json_encode(['status' => 'success', 'message' => 'All notifications marked as read.']);
    }
} else {
    jsonError(405, 'Method not allowed');
}
