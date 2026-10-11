<?php
// api/notifications.php - Notifications REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT id, target_role, target, title, message, is_read, created_at FROM notifications ORDER BY created_at DESC");
        $notifications = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT id, target_role, target, title, message, is_read, created_at FROM notifications WHERE target_role = 'all' OR (target_role = ? AND (target = ? OR target = 'All Users')) ORDER BY created_at DESC");
        $stmt->execute([$user['role'], $user['name']]);
        $notifications = $stmt->fetchAll();
    }
    echo json_encode(['status' => 'success', 'data' => toCamelArray($notifications)]);
} elseif ($method === 'POST') {
    $user = requireLogin();
    $input = getInput();

    $targetRole = trim($input['target_role'] ?? ($input['targetRole'] ?? 'all'));
    $target = trim($input['target'] ?? 'All Users');
    $title = trim($input['title'] ?? 'Notification');
    $message = trim($input['message'] ?? '');

    if (empty($message)) {
        jsonError(400, 'Notification message required.');
    }

    if ($user['role'] !== 'admin' && ($targetRole === 'all' || $target === 'All Users')) {
        jsonError(403, 'Only administrators can send system-wide notifications.');
    }

    $notifId = newId('notif-');

    $stmt = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message, is_read) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$notifId, $targetRole, $target, $title, $message]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Notification sent.',
        'data' => [
            'id' => $notifId,
            'target_role' => $targetRole,
            'target' => $target,
            'title' => $title,
            'message' => $message,
            'is_read' => 0
        ]
    ]);
} elseif ($method === 'PUT') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $pdo->exec("UPDATE notifications SET is_read = 1");
    } else {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE target_role = 'all' OR (target_role = ? AND (target = ? OR target = 'All Users'))");
        $stmt->execute([$user['role'], $user['name']]);
    }
    echo json_encode(['status' => 'success', 'message' => 'Notifications marked as read.']);
} else {
    jsonError(405, 'Method not allowed');
}
?>