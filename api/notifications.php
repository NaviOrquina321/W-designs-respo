<?php
// api/notifications.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
    $data = $stmt->fetchAll();

    $formatted = array_map(function($n) {
        $n['targetRole'] = $n['target_role'] ?? 'all';
        $n['createdTime'] = $n['created_time'] ?? 'Just now';
        $n['read'] = (bool)($n['is_read'] ?? 0);
        return $n;
    }, $data);

    echo json_encode(['status' => 'success', 'data' => $formatted]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('notif-' . time() . '-' . rand(100, 999));
    $targetRole = $input['targetRole'] ?? ($input['target_role'] ?? 'all');
    $target = $input['target'] ?? 'All Users';
    $title = $input['title'] ?? 'Notification';
    $message = $input['message'] ?? '';
    $createdTime = $input['createdTime'] ?? 'Just now';

    $stmt = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message, created_time, is_read)
                           VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([$id, $targetRole, $target, $title, $message, $createdTime]);

    echo json_encode(['status' => 'success', 'message' => 'Notification created successfully', 'id' => $id]);
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $pdo->exec("UPDATE notifications SET is_read = 1");
    }

    echo json_encode(['status' => 'success', 'message' => 'Notifications marked as read']);
}
?>