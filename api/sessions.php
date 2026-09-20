<?php
// api/sessions.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM sessions ORDER BY created_at DESC");
    $sessions = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $sessions]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('SESS-' . rand(100, 999));
    $studentName = $input['student_name'] ?? '';
    $tutorId = $input['tutor_id'] ?? '';
    $tutorName = $input['tutor_name'] ?? '';
    $subject = $input['subject'] ?? '';
    $sessionDate = $input['session_date'] ?? date('Y-m-d');
    $timeSlot = $input['time_slot'] ?? '';
    $hourlyRate = $input['hourly_rate'] ?? 350;
    $commissionFee = $input['commission_fee'] ?? 35;
    $totalPaid = $input['total_paid'] ?? 385;
    $gcashRef = $input['gcash_ref'] ?? '';
    $status = $input['status'] ?? 'Confirmed';
    $notes = $input['notes'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO sessions (id, student_name, tutor_id, tutor_name, subject, session_date, time_slot, hourly_rate, commission_fee, total_paid, gcash_ref, status, notes)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE status=?, notes=?");
    $stmt->execute([$id, $studentName, $tutorId, $tutorName, $subject, $sessionDate, $timeSlot, $hourlyRate, $commissionFee, $totalPaid, $gcashRef, $status, $notes, $status, $notes]);

    echo json_encode(['status' => 'success', 'message' => 'Session created/updated successfully', 'id' => $id]);
}
?>
