<?php
// api/chat.php - Workspace Chat REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    $sessionId = trim($_GET['session_id'] ?? '');
    $afterId = intval($_GET['after_id'] ?? 0);

    if (empty($sessionId)) {
        jsonError(400, 'Session ID required.');
    }

    $st = $pdo->prepare("SELECT student_id, student_name, tutor_id, tutor_name FROM sessions WHERE id = ?");
    $st->execute([$sessionId]);
    $session = $st->fetch();

    if (!$session) {
        jsonError(404, 'Session not found.');
    }

    // Role check: Admin or participant student/tutor
    if ($user['role'] !== 'admin') {
        $isStudent = ($session['student_id'] === $user['id'] || strtolower($session['student_name']) === strtolower($user['name']));
        $isTutor = ($session['tutor_id'] === $user['id'] || strtolower($session['tutor_name']) === strtolower($user['name']));
        if (!$isStudent && !$isTutor) {
            jsonError(403, 'Forbidden: You are not a participant in this workspace session.');
        }
    }

    if ($afterId > 0) {
        $stmt = $pdo->prepare("SELECT id, session_id, sender_name, sender_role, message, timestamp, created_at FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC");
        $stmt->execute([$sessionId, $afterId]);
    } else {
        $stmt = $pdo->prepare("SELECT id, session_id, sender_name, sender_role, message, timestamp, created_at FROM chat_messages WHERE session_id = ? ORDER BY id ASC");
        $stmt->execute([$sessionId]);
    }

    $messages = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $messages]);
} elseif ($method === 'POST') {
    $user = requireLogin();
    $input = getInput();

    $sessionId = trim($input['sessionId'] ?? ($input['session_id'] ?? ''));
    $message = trim($input['message'] ?? '');

    if (empty($sessionId) || empty($message)) {
        jsonError(400, 'Session ID and message content required.');
    }

    if (mb_strlen($message) > 1000) {
        jsonError(400, 'Message exceeds 1000 characters limit.');
    }

    $st = $pdo->prepare("SELECT student_id, student_name, tutor_id, tutor_name FROM sessions WHERE id = ?");
    $st->execute([$sessionId]);
    $session = $st->fetch();

    if (!$session) {
        jsonError(404, 'Session not found.');
    }

    $isStudent = ($session['student_id'] === $user['id'] || strtolower($session['student_name']) === strtolower($user['name']));
    $isTutor = ($session['tutor_id'] === $user['id'] || strtolower($session['tutor_name']) === strtolower($user['name']));

    if (!$isStudent && !$isTutor && $user['role'] !== 'admin') {
        jsonError(403, 'Forbidden: You are not a participant in this workspace session.');
    }

    $senderName = $user['name'];
    $senderRole = $user['role'];
    $timestamp = date('h:i A');

    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender_name, sender_role, message, timestamp) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$sessionId, $senderName, $senderRole, $message, $timestamp]);
    $msgId = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent.',
        'data' => [
            'id' => $msgId,
            'session_id' => $sessionId,
            'sender_name' => $senderName,
            'sender_role' => $senderRole,
            'message' => $message,
            'timestamp' => $timestamp
        ]
    ]);
} else {
    jsonError(405, 'Method not allowed');
}
?>