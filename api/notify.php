<?php
// api/notify.php - Modular Central Notification Dispatcher
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $user = requireRole(['admin']);
    $input = getInput();

    $title = trim($input['title'] ?? '');
    $message = trim($input['message'] ?? '');

    if (empty($title) || empty($message)) {
        jsonError(400, 'Title and message are required.');
    }

    $targetRole = trim($input['target_role'] ?? ($input['targetRole'] ?? 'all'));
    $target = trim($input['target'] ?? 'All Users');
    $recipientUserId = $input['recipient_user_id'] ?? ($input['recipientUserId'] ?? null);
    $recipientRole = $input['recipient_role'] ?? ($input['recipientRole'] ?? null);

    $meta = [
        'type' => $input['type'] ?? 'broadcast',
        'recipient_user_id' => $recipientUserId,
        'recipient_role' => $recipientRole,
        'related_session_id' => $input['related_session_id'] ?? ($input['relatedSessionId'] ?? null),
        'related_payment_id' => $input['related_payment_id'] ?? ($input['relatedPaymentId'] ?? null),
        'related_user_id' => $input['related_user_id'] ?? ($input['relatedUserId'] ?? null),
        'action_url' => $input['action_url'] ?? ($input['actionUrl'] ?? null),
        'priority' => $input['priority'] ?? 'normal',
        'dedupe_key' => $input['dedupe_key'] ?? ($input['dedupeKey'] ?? null)
    ];

    $notifId = notify($pdo, $targetRole, $target, $title, $message, $meta);

    if (!$notifId) {
        jsonError(400, 'Failed to create notification or duplicate notification suppressed.');
    }

    $st = $pdo->prepare("SELECT * FROM notifications WHERE id = ?");
    $st->execute([$notifId]);
    $row = $st->fetch();

    echo json_encode([
        'status' => 'success',
        'message' => 'Notification dispatched successfully.',
        'data' => toCamelArray($row)
    ]);
} else {
    jsonError(405, 'Method not allowed');
}
