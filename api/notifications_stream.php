<?php
// api/notifications_stream.php - Real-Time Server-Sent Events (SSE) Stream
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// Ensure headers for Server-Sent Events
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

$user = requireLogin();
$userId = $user['id'];
$userRole = $user['role'];

// Release session lock so other HTTP requests are not blocked
session_write_close();
@set_time_limit(0);

$lastHash = '';
$start = time();
$maxDuration = 25; // Keep connection open for max 25 seconds per request cycle

echo "retry: 3000\n\n";
@ob_flush();
@flush();

while (time() - $start < $maxDuration) {
    if (connection_aborted()) {
        break;
    }

    if ($userRole === 'admin') {
        $sql = "SELECT n.id, n.recipient_user_id, n.recipient_role, n.target_role, n.target, n.type, n.title, n.message,
                       n.related_session_id, n.related_payment_id, n.related_user_id, n.action_url, n.priority,
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
                LIMIT 30";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
    } else {
        $sql = "SELECT n.id, n.recipient_user_id, n.recipient_role, n.target_role, n.target, n.type, n.title, n.message,
                       n.related_session_id, n.related_payment_id, n.related_user_id, n.action_url, n.priority,
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
                LIMIT 30";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId, $userRole, $userRole, $userId]);
    }

    $notifications = $stmt->fetchAll();
    $formatted = toCamelArray($notifications);

    $unreadCount = 0;
    foreach ($formatted as $item) {
        if (empty($item['isRead'])) {
            $unreadCount++;
        }
    }

    $currentHash = md5(json_encode($formatted));

    if ($currentHash !== $lastHash) {
        $lastHash = $currentHash;
        $payload = json_encode([
            'notifications' => $formatted,
            'unreadCount' => $unreadCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        echo "event: notification\n";
        echo "data: {$payload}\n\n";
        @ob_flush();
        @flush();
    } else {
        // Send heartbeat ping every 10 seconds
        echo ": ping\n\n";
        @ob_flush();
        @flush();
    }

    sleep(2);
}
