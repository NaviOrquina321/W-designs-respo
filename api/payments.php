<?php
// api/payments.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM payments ORDER BY created_at DESC");
    $data = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $data]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('PAY-' . rand(100, 999));
    $studentId = $input['student_id'] ?? ($input['studentId'] ?? '');
    $studentName = $input['student_name'] ?? ($input['studentName'] ?? '');
    $tutorId = $input['tutor_id'] ?? ($input['tutorId'] ?? '');
    $tutorName = $input['tutor_name'] ?? ($input['tutorName'] ?? '');
    $methodType = $input['method'] ?? 'GCash';
    $refNo = $input['ref_no'] ?? ($input['refNo'] ?? ('GC-' . rand(1000000000, 9999999999)));
    $amount = (int)($input['amount'] ?? 385);
    $status = $input['status'] ?? 'Confirmed';
    $payoutStatus = $input['payout_status'] ?? 'Pending';

    $stmt = $pdo->prepare("INSERT INTO payments (id, student_id, student_name, tutor_id, tutor_name, method, ref_no, amount, status, payout_status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $studentId, $studentName, $tutorId, $tutorName, $methodType, $refNo, $amount, $status, $payoutStatus]);

    echo json_encode(['status' => 'success', 'message' => 'Payment recorded successfully', 'id' => $id]);
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';

    if (isset($input['payout_status'])) {
        $stmt = $pdo->prepare("UPDATE payments SET payout_status = ? WHERE id = ?");
        $stmt->execute([$input['payout_status'], $id]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Payment updated successfully']);
}
?>