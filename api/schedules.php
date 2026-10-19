<?php
// api/schedules.php - Schedules REST API with Admin Audit & FDD Validation
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT * FROM schedules ORDER BY created_at DESC");
        $schedules = $stmt->fetchAll();
    } elseif ($user['role'] === 'tutor') {
        $stmt = $pdo->prepare("SELECT * FROM schedules WHERE tutor_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $schedules = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM schedules WHERE status = 'Available' OR student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $rawSchedules = $stmt->fetchAll();

        $schedules = [];
        foreach ($rawSchedules as $sch) {
            if ($sch['student_id'] !== $user['id']) {
                $sch['student_id'] = null;
                $sch['student_name'] = null;
            }
            $schedules[] = $sch;
        }
    }
    echo json_encode(['status' => 'success', 'data' => toCamelArray($schedules)]);
} elseif ($method === 'POST') {
    $user = requireRole(['tutor', 'admin']);
    $input = getInput();

    $tutorId = $user['role'] === 'tutor' ? $user['id'] : trim($input['tutor_id'] ?? ($input['tutorId'] ?? ''));
    $dateStr = trim($input['date'] ?? ($input['date_slot_date'] ?? ''));
    $timeSlot = trim($input['time_slot'] ?? ($input['timeSlot'] ?? ''));
    $subject = trim($input['subject'] ?? 'Mathematics');

    // Handle full date_slot string if passed directly
    $dateSlot = trim($input['date_slot'] ?? ($input['dateSlot'] ?? ''));
    if (empty($dateSlot) && !empty($dateStr) && !empty($timeSlot)) {
        $dateSlot = "$dateStr $timeSlot";
    }

    if (empty($tutorId)) {
        jsonError(422, 'Tutor ID required.');
    }
    if (empty($dateSlot)) {
        jsonError(422, 'Date and time slot required.');
    }

    // Parse date component
    $parts = explode(' ', $dateSlot);
    $checkDate = $parts[0] ?? '';
    if (!empty($checkDate)) {
        $d = DateTime::createFromFormat('Y-m-d', $checkDate);
        if ($d && $checkDate < date('Y-m-d')) {
            jsonError(422, 'Cannot add schedule slot for a past date.');
        }
    }

    // Verify tutor exists and is active
    $stT = $pdo->prepare("SELECT id, name, deactivated FROM tutors WHERE id = ?");
    $stT->execute([$tutorId]);
    $tut = $stT->fetch();

    if (!$tut) {
        jsonError(404, 'Selected tutor not found.');
    }
    if (!empty($tut['deactivated'])) {
        jsonError(422, 'Cannot add schedule slot for a deactivated tutor.');
    }
    $tutorName = $tut['name'];

    // Check duplicate slot for tutor
    $stDup = $pdo->prepare("SELECT COUNT(*) as cnt FROM schedules WHERE tutor_id = ? AND date_slot = ?");
    $stDup->execute([$tutorId, $dateSlot]);
    if ($stDup->fetch()['cnt'] > 0) {
        jsonError(422, 'A schedule slot for this tutor at the specified date and time already exists.');
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

    if ($user['role'] === 'admin') {
        logAdminAction('New Calendar Schedule', $schId, null, [
            'tutor_id' => $tutorId,
            'tutor_name' => $tutorName,
            'date_slot' => $dateSlot,
            'subject' => $subject
        ]);
    }

    $stFetch = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $stFetch->execute([$schId]);
    $row = $stFetch->fetch();

    echo json_encode([
        'status' => 'success',
        'message' => 'Schedule slot created successfully.',
        'data' => toCamelArray($row)
    ]);
} elseif ($method === 'PUT') {
    $user = requireRole(['tutor', 'admin']);
    $input = getInput();
    $schId = trim($input['id'] ?? '');

    if (empty($schId)) {
        jsonError(422, 'Schedule ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $st->execute([$schId]);
    $sch = $st->fetch();

    if (!$sch) {
        jsonError(404, 'Schedule record not found.');
    }

    if ($sch['status'] === 'Booked') {
        jsonError(422, 'Cannot modify booked schedule slots.');
    }

    if ($user['role'] === 'tutor' && $sch['tutor_id'] !== $user['id']) {
        jsonError(403, 'You can only modify your own schedule slots.');
    }

    $fields = [];
    $params = [];

    if (isset($input['date_slot']) || isset($input['dateSlot'])) {
        $ds = trim($input['date_slot'] ?? $input['dateSlot']);
        if (empty($ds)) jsonError(422, 'Date slot cannot be empty.');
        $fields[] = "`date_slot` = ?";
        $params[] = $ds;
    }
    if (isset($input['subject'])) {
        $fields[] = "`subject` = ?";
        $params[] = trim($input['subject']);
    }
    if (isset($input['status'])) {
        $statusVal = trim($input['status']);
        if (!in_array($statusVal, ['Available', 'Blocked'], true)) {
            jsonError(422, "Schedule status must be 'Available' or 'Blocked'.");
        }
        $fields[] = "`status` = ?";
        $params[] = $statusVal;
    }

    if (empty($fields)) {
        jsonError(422, 'No valid fields provided for update.');
    }

    $params[] = $schId;
    $sql = "UPDATE schedules SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($user['role'] === 'admin') {
        logAdminAction('New Calendar Schedule', $schId, ['status' => $sch['status']], $input);
    }

    echo json_encode(['status' => 'success', 'message' => 'Schedule slot updated successfully.']);
} elseif ($method === 'DELETE') {
    $user = requireRole(['tutor', 'admin']);
    $input = getInput();
    $schId = trim($input['id'] ?? ($_GET['id'] ?? ''));

    if (empty($schId)) {
        jsonError(422, 'Schedule ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $st->execute([$schId]);
    $sch = $st->fetch();

    if (!$sch) {
        jsonError(404, 'Schedule slot not found.');
    }

    if ($sch['status'] === 'Booked') {
        jsonError(422, 'Cannot delete booked schedule slots.');
    }

    if ($user['role'] === 'tutor' && $sch['tutor_id'] !== $user['id']) {
        jsonError(403, 'You can only delete your own schedule slots.');
    }

    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->execute([$schId]);

    if ($user['role'] === 'admin') {
        logAdminAction('New Calendar Schedule (Delete)', $schId, $sch, null);
    }

    echo json_encode(['status' => 'success', 'message' => 'Schedule slot deleted successfully.']);
} else {
    jsonError(405, 'Method not allowed');
}
