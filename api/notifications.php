<?php
// api/notifications.php - Notifications REST API with FDD Send & View Notifications
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

    session_write_close();

    $tab = $_GET['tab'] ?? 'sent'; // 'sent' or 'inbox' for admin

    if ($userRole === 'admin') {
        if ($tab === 'inbox') {
            // Admin Inbox: system notifications sent TO admin
            $sql = "SELECT n.id, n.recipient_user_id, n.recipient_role, n.target_role, n.target, n.type, n.title, n.message,
                           n.related_session_id, n.related_payment_id, n.related_user_id, n.action_url, n.priority, n.dedupe_key,
                           CASE WHEN nr.user_id IS NOT NULL OR n.is_read = 1 THEN 1 ELSE 0 END as is_read,
                           COALESCE(nr.read_at, n.read_at) as read_at,
                           n.created_at
                    FROM notifications n
                    LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                    WHERE n.recipient_role = 'admin' OR n.recipient_user_id = ? OR n.target_role = 'admin'
                    ORDER BY n.created_at DESC
                    LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $userId]);
        } else {
            // Admin Sent: broadcast / sent notifications history log with recipient count & read count
            $sql = "SELECT n.id, n.recipient_user_id, n.recipient_role, n.target_role, n.target, n.type, n.title, n.message,
                           n.priority, n.created_at,
                           (SELECT COUNT(*) FROM notification_reads nr WHERE nr.notification_id = n.id) AS read_count
                    FROM notifications n
                    ORDER BY n.created_at DESC
                    LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
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

    $audience = trim($input['audience'] ?? ($input['target_role'] ?? 'all'));
    $specificUserId = trim($input['specific_user_id'] ?? ($input['specificUserId'] ?? ''));
    $title = trim($input['title'] ?? '');
    $message = trim($input['message'] ?? '');
    $priority = trim($input['priority'] ?? 'normal');

    if (empty($title)) {
        jsonError(422, 'Notification title is required.');
    }
    if (mb_strlen($title) > 100) {
        jsonError(422, 'Notification title must not exceed 100 characters.');
    }
    if (empty($message)) {
        jsonError(422, 'Notification message is required.');
    }
    if (mb_strlen($message) > 500) {
        jsonError(422, 'Notification message must not exceed 500 characters.');
    }

    // Rate-limit broadcast notifications (max 10 broadcasts per hour)
    if (in_array($audience, ['all', 'students', 'tutors', 'All Users', 'All Students', 'All Tutors'], true)) {
        $oneHourAgo = date('Y-m-d H:i:s', time() - 3600);
        $stRate = $pdo->prepare("SELECT COUNT(*) as cnt FROM admin_audit_log WHERE action = 'Send Notifications' AND created_at >= ?");
        $stRate->execute([$oneHourAgo]);
        if ($stRate->fetch()['cnt'] >= 10) {
            jsonError(429, 'Broadcast notification rate limit reached (max 10 per hour). Please try again later.');
        }
    }

    $recipients = [];

    if ($audience === 'student_specific') {
        if (empty($specificUserId)) jsonError(422, 'Please select a student recipient.');
        $st = $pdo->prepare("SELECT id, name FROM students WHERE id = ?");
        $st->execute([$specificUserId]);
        $row = $st->fetch();
        if (!$row) jsonError(404, 'Selected student not found.');
        $recipients[] = ['id' => $row['id'], 'role' => 'student'];
    } elseif ($audience === 'tutor_specific') {
        if (empty($specificUserId)) jsonError(422, 'Please select a tutor recipient.');
        $st = $pdo->prepare("SELECT id, name FROM tutors WHERE id = ?");
        $st->execute([$specificUserId]);
        $row = $st->fetch();
        if (!$row) jsonError(404, 'Selected tutor not found.');
        $recipients[] = ['id' => $row['id'], 'role' => 'tutor'];
    } elseif ($audience === 'students' || $audience === 'All Students') {
        $st = $pdo->query("SELECT id FROM students WHERE deactivated = 0");
        while ($row = $st->fetch()) {
            $recipients[] = ['id' => $row['id'], 'role' => 'student'];
        }
    } elseif ($audience === 'tutors' || $audience === 'All Tutors') {
        $st = $pdo->query("SELECT id FROM tutors WHERE deactivated = 0");
        while ($row = $st->fetch()) {
            $recipients[] = ['id' => $row['id'], 'role' => 'tutor'];
        }
    } else { // 'all' or 'All Users'
        $st1 = $pdo->query("SELECT id FROM students WHERE deactivated = 0");
        while ($row = $st1->fetch()) {
            $recipients[] = ['id' => $row['id'], 'role' => 'student'];
        }
        $st2 = $pdo->query("SELECT id FROM tutors WHERE deactivated = 0");
        while ($row = $st2->fetch()) {
            $recipients[] = ['id' => $row['id'], 'role' => 'tutor'];
        }
    }

    $sentCount = 0;
    foreach ($recipients as $rec) {
        notify($pdo, $rec['role'], $rec['id'], $title, $message, [
            'type' => 'broadcast',
            'recipient_user_id' => $rec['id'],
            'recipient_role' => $rec['role'],
            'priority' => $priority
        ]);
        $sentCount++;
    }

    logAdminAction('Send Notifications', "Audience: $audience", null, [
        'title' => $title,
        'message' => $message,
        'recipients_count' => $sentCount
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => "Send Notifications completed. Sent to $sentCount users.",
        'data' => [
            'sent_count' => $sentCount,
            'title' => $title,
            'audience' => $audience
        ]
    ]);
} elseif ($method === 'DELETE') {
    $user = requireLogin();
    $userId = $user['id'];
    $userRole = $user['role'];
    $notifId = trim($_GET['id'] ?? '');

    if (empty($notifId)) {
        jsonError(422, 'Notification ID is required for deletion.');
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

        $stUpd = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (recipient_user_id = ? OR recipient_role = 'admin')");
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
