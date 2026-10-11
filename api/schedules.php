<?php
// api/schedules.php - Schedules REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    $stmt = $pdo->query("SELECT * FROM schedules ORDER BY created_at DESC");
    $schedules = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => toCamelArray($schedules)]);
} elseif ($method === 'POST') {
    $user = requireRole(['tutor', 'admin']);
    $input = getInput();

    $tutorId = $user['role'] === 'tutor' ? $user['id'] : trim($input['tutor_id'] ?? $input['tutorId'] ?? $user['id']);
    $tutorName = $user['role'] === 'tutor' ? $user['name'] : trim($input['tutor_name'] ?? $input['tutorName'] ?? $user['name']);
    $dateSlot = trim($input['date_slot'] ?? $input['dateSlot'] ?? '');
    $subject = trim($input['subject'] ?? 'Mathematics');

    if (empty($dateSlot)) {
        jsonError(400, 'Date and time slot required.');
    }

    $schId = newId('SCH-');

    $stmt = $pdo->prepare("INSERT INTO schedules (id, tutor_id, tutor_name, date_slot, subject, status) VALUES (?, ?, ?, ?, ?, 'Available')");
    $stmt->execute([
        $schId,
        $tutorId,
        $tutorName,
        $dateSlot,
        $subject
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Schedule slot created.',
        'data' => [
            'id' => $schId,
            'tutor_id' => $tutorId,
            'tutor_name' => $tutorName,
            'date_slot' => $dateSlot,
            'subject' => $subject,
            'status' => 'Available'
        ]
    ]);
} elseif ($method === 'PUT') {
    $user = requireRole(['tutor', 'admin']);
    $input = getInput();
    $schId = trim($input['id'] ?? '');

    if (empty($schId)) {
        jsonError(400, 'Schedule ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $st->execute([$schId]);
    $sch = $st->fetch();

    if (!$sch) {
        jsonError(404, 'Schedule record not found.');
    }

    if ($user['role'] === 'tutor' && $sch['tutor_id'] !== $user['id']) {
        jsonError(403, 'You can only modify your own schedule slots.');
    }

    $fields = [];
    $params = [];

    if (isset($input['date_slot'])) {
        $fields[] = "`date_slot` = ?";
        $params[] = trim($input['date_slot']);
    }
    if (isset($input['subject'])) {
        $fields[] = "`subject` = ?";
        $params[] = trim($input['subject']);
    }
    if (isset($input['status'])) {
        $fields[] = "`status` = ?";
        $params[] = trim($input['status']);
    }

    if (empty($fields)) {
        jsonError(400, 'No valid fields provided for update.');
    }

    $params[] = $schId;
    $sql = "UPDATE schedules SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['status' => 'success', 'message' => 'Schedule slot updated.']);
} elseif ($method === 'DELETE') {
    $user = requireRole(['tutor', 'admin']);
    $input = getInput();
    $schId = trim($input['id'] ?? ($_GET['id'] ?? ''));

    if (empty($schId)) {
        jsonError(400, 'Schedule ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $st->execute([$schId]);
    $sch = $st->fetch();

    if (!$sch) {
        jsonError(404, 'Schedule slot not found.');
    }

    if ($user['role'] === 'tutor' && $sch['tutor_id'] !== $user['id']) {
        jsonError(403, 'You can only delete your own schedule slots.');
    }

    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->execute([$schId]);

    echo json_encode(['status' => 'success', 'message' => 'Schedule slot deleted.']);
} else {
    jsonError(405, 'Method not allowed');
}
?>