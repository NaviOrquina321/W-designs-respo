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
                    $remId = "rem-{$ses['id']}-$userRole";
                    $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE id = ?");
                    $stChk->execute([$remId]);

                    if ($stChk->fetch()['cnt'] == 0) {
                        $targetUserId = ($userRole === 'student') ? $ses['student_id'] : $ses['tutor_id'];
                        $otherName = ($userRole === 'student') ? $ses['tutor_name'] : $ses['student_name'];
                        $remMsg = "Reminder: Your {$ses['subject']} session with $otherName starts on {$ses['session_date']} at {$ses['time_slot']}.";

                        $stIns = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message, is_read) VALUES (?, ?, ?, ?, ?, 0)");
                        $stIns->execute([$remId, $userRole, $targetUserId, 'Upcoming Session Reminder', $remMsg]);
                    }
                }
            }
        }
    }

    // Release session lock for non-blocking read-only polling
    session_write_close();

    if ($userRole === 'admin') {
        $sql = "SELECT n.id, n.target_role, n.target, n.title, n.message,
                       CASE WHEN nr.user_id IS NOT NULL OR n.is_read = 1 THEN 1 ELSE 0 END as is_read,
                       n.created_at
                FROM notifications n
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
    } else {
        $sql = "SELECT n.id, n.target_role, n.target, n.title, n.message,
                       CASE WHEN nr.user_id IS NOT NULL OR n.is_read = 1 THEN 1 ELSE 0 END as is_read,
                       n.created_at
                FROM notifications n
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                WHERE n.target_role = 'all'
                   OR n.target = 'All Users'
                   OR (n.target_role = ? AND (n.target = ? OR n.target = 'All Users'))
                ORDER BY n.created_at DESC
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userRole, $userId]);
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
