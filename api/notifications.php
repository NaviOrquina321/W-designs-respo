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
        $stmt = $pdo->prepare("SELECT id, target_role, target, title, message, is_read, created_at FROM notifications WHERE target_role = 'all' OR (target_role = ? AND target = ?) ORDER BY created_at DESC");
        $stmt->execute([$user['role'], $user['name']]);
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
    if ($user['role'] === 'admin') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE target_role = 'admin'");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE target_role = ? AND target = ?");
        $stmt->execute([$user['role'], $user['name']]);
    }
    echo json_encode(['status' => 'success', 'message' => 'Targeted notifications marked as read.']);
} else {
    jsonError(405, 'Method not allowed');
}
?>