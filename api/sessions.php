<?php
// api/sessions.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM sessions ORDER BY session_date ASC, time_slot ASC");
    $data = $stmt->fetchAll();

    $formatted = array_map(function($s) {
        $s['hourlyRate'] = (int)$s['hourly_rate'];
        $s['commissionFee'] = (int)$s['commission_fee'];
        $s['totalPaid'] = (int)$s['total_paid'];
        $s['gcashRef'] = $s['gcash_ref'];
        $s['sessionDate'] = $s['session_date'];
        $s['timeSlot'] = $s['time_slot'];
        $s['studentId'] = $s['student_id'] ?? '';
        $s['studentName'] = $s['student_name'];
        $s['tutorId'] = $s['tutor_id'];
        $s['tutorName'] = $s['tutor_name'];
        $s['payoutStatus'] = $s['payout_status'];
        return $s;
    }, $data);

    echo json_encode(['status' => 'success', 'data' => $formatted]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('SES-' . rand(100, 999));
    $studentId = $input['studentId'] ?? ($input['student_id'] ?? '');
    $studentName = $input['studentName'] ?? ($input['student_name'] ?? '');
    $tutorId = $input['tutorId'] ?? ($input['tutor_id'] ?? '');
    $tutorName = $input['tutorName'] ?? ($input['tutor_name'] ?? '');
    $subject = $input['subject'] ?? '';
    $sessionDate = $input['sessionDate'] ?? ($input['session_date'] ?? date('Y-m-d'));
    $timeSlot = $input['timeSlot'] ?? ($input['time_slot'] ?? '02:00 PM');
    $hourlyRate = (int)($input['hourlyRate'] ?? ($input['hourly_rate'] ?? 350));
    $commissionFee = (int)($input['commissionFee'] ?? ($input['commission_fee'] ?? 35));
    $totalPaid = (int)($input['totalPaid'] ?? ($input['total_paid'] ?? 385));
    $gcashRef = $input['gcashRef'] ?? ($input['gcash_ref'] ?? ('GC-' . rand(1000000000, 9999999999)));
    $status = $input['status'] ?? 'Confirmed';
    $payoutStatus = $input['payoutStatus'] ?? ($input['payout_status'] ?? 'Unpaid');
    $notes = $input['notes'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO sessions (id, student_id, student_name, tutor_id, tutor_name, subject, session_date, time_slot, hourly_rate, commission_fee, total_paid, gcash_ref, status, payout_status, notes)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $studentId, $studentName, $tutorId, $tutorName, $subject, $sessionDate, $timeSlot, $hourlyRate, $commissionFee, $totalPaid, $gcashRef, $status, $payoutStatus, $notes]);

    // Also record in payments
    $payId = 'PAY-' . rand(100, 999);
    $payStmt = $pdo->prepare("INSERT INTO payments (id, student_id, student_name, tutor_id, tutor_name, method, ref_no, amount, status, payout_status) VALUES (?, ?, ?, ?, ?, 'GCash', ?, ?, 'Confirmed', 'Pending')");
    $payStmt->execute([$payId, $studentId, $studentName, $tutorId, $tutorName, $gcashRef, $totalPaid]);

    echo json_encode(['status' => 'success', 'message' => 'Session booked successfully', 'id' => $id]);
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Session ID is required']);
        exit();
    }

    if (isset($input['notes'])) {
        $stmt = $pdo->prepare("UPDATE sessions SET notes = ? WHERE id = ?");
        $stmt->execute([$input['notes'], $id]);
    }

    if (isset($input['status'])) {
        $stmt = $pdo->prepare("UPDATE sessions SET status = ? WHERE id = ?");
        $stmt->execute([$input['status'], $id]);
    }

    if (isset($input['rating'])) {
        $stmt = $pdo->prepare("UPDATE sessions SET rating = ?, feedback = ? WHERE id = ?");
        $stmt->execute([(int)$input['rating'], $input['feedback'] ?? '', $id]);
    }

    if (isset($input['payout_status'])) {
        $stmt = $pdo->prepare("UPDATE sessions SET payout_status = ? WHERE id = ?");
        $stmt->execute([$input['payout_status'], $id]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Session updated successfully']);
}
?>