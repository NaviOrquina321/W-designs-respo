<?php
// api/chat.php - Session Workspace Live Chat Transport API
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sessionId = $_GET['session_id'] ?? '';
    if (empty($sessionId)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'session_id query parameter is required']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? ORDER BY id ASC");
    $stmt->execute([$sessionId]);
    $messages = $stmt->fetchAll();

    $formatted = array_map(function($m) {
        return [
            'id' => $m['id'],
            'sessionId' => $m['session_id'],
            'senderName' => $m['sender_name'],
            'senderRole' => $m['sender_role'],
            'message' => $m['message'],
            'timestamp' => $m['timestamp']
        ];
    }, $messages);

    echo json_encode(['status' => 'success', 'data' => $formatted]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $sessionId = $input['sessionId'] ?? ($input['session_id'] ?? '');
    $senderName = $input['senderName'] ?? ($input['sender_name'] ?? 'User');
    $senderRole = $input['senderRole'] ?? ($input['sender_role'] ?? 'student');
    $message = trim($input['message'] ?? '');
    $timestamp = $input['timestamp'] ?? date('h:i A');

    if (empty($sessionId) || empty($message)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'session_id and message are required']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender_name, sender_role, message, timestamp)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$sessionId, $senderName, $senderRole, $message, $timestamp]);
    $msgId = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent',
        'data' => [
            'id' => $msgId,
            'sessionId' => $sessionId,
            'senderName' => $senderName,
            'senderRole' => $senderRole,
            'message' => $message,
            'timestamp' => $timestamp
        ]
    ]);
}
?>