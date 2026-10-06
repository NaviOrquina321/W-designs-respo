<?php
// api/schedules.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM schedules ORDER BY created_at DESC");
    $data = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $data]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('sch-' . rand(100, 999));
    $tutorId = $input['tutor_id'] ?? ($input['tutorId'] ?? '');
    $tutorName = $input['tutor_name'] ?? ($input['tutorName'] ?? '');
    $studentId = $input['student_id'] ?? ($input['studentId'] ?? '');
    $studentName = $input['student_name'] ?? ($input['studentName'] ?? '');
    $dateSlot = $input['date_slot'] ?? ($input['dateSlot'] ?? '');
    $subject = $input['subject'] ?? '';
    $status = $input['status'] ?? 'Available';

    $stmt = $pdo->prepare("INSERT INTO schedules (id, tutor_id, tutor_name, student_id, student_name, date_slot, subject, status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $tutorId, $tutorName, $studentId, $studentName, $dateSlot, $subject, $status]);

    echo json_encode(['status' => 'success', 'message' => 'Schedule created successfully', 'id' => $id]);
}
?>