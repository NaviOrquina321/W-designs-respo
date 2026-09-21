<?php
// api/workspace.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sessionId = $_GET['session_id'] ?? '';
    if (empty($sessionId)) {
        echo json_encode(['status' => 'error', 'message' => 'Session ID required']);
        exit();
    }

    // Get notes
    $nStmt = $pdo->prepare("SELECT notes FROM workspace_notes WHERE session_id = ?");
    $nStmt->execute([$sessionId]);
    $notesRow = $nStmt->fetch();

    // Get chat messages
    $cStmt = $pdo->prepare("SELECT * FROM workspace_chat WHERE session_id = ? ORDER BY created_at ASC");
    $cStmt->execute([$sessionId]);
    $chatRows = $cStmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'notes' => $notesRow ? $notesRow['notes'] : '',
        'chat' => $chatRows
    ]);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $data['action'] ?? 'save_notes';

    if ($action === 'save_notes') {
        $sessionId = $data['session_id'] ?? '';
        $notes = $data['notes'] ?? '';

        $stmt = $pdo->prepare("INSERT INTO workspace_notes (session_id, notes) VALUES (?, ?) ON CONFLICT(session_id) DO UPDATE SET notes = excluded.notes");
        $stmt->execute([$sessionId, $notes]);

        echo json_encode(['status' => 'success', 'message' => 'Notes saved successfully']);
        exit();
    }

    if ($action === 'send_chat') {
        $sessionId = $data['session_id'] ?? '';
        $senderName = $data['sender_name'] ?? 'User';
        $senderRole = $data['sender_role'] ?? 'student';
        $message = trim($data['message'] ?? '');

        if (!empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO workspace_chat (session_id, sender_name, sender_role, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sessionId, $senderName, $senderRole, $message]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Message sent']);
        exit();
    }
}
?>