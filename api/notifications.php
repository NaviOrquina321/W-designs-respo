<?php
// api/notifications.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
    $notifications = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $notifications]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('notif-' . time());
    $target = $input['target'] ?? 'All Users';
    $title = $input['title'] ?? 'System Broadcast';
    $message = $input['message'] ?? '';
    $createdTime = $input['created_time'] ?? 'Just now';
    $isRead = isset($input['is_read']) ? ($input['is_read'] ? 1 : 0) : 0;

    $stmt = $pdo->prepare("INSERT INTO notifications (id, target, title, message, created_time, is_read)
                           VALUES (?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE is_read=?");
    $stmt->execute([$id, $target, $title, $message, $createdTime, $isRead, $isRead]);

    echo json_encode(['status' => 'success', 'message' => 'Notification recorded', 'id' => $id]);
}
?>
