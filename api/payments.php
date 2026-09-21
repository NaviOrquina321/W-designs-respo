<?php
// api/payments.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM payments ORDER BY created_at DESC");
    $payments = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $payments]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('PAY-' . rand(100, 999));
    $studentName = $input['student_name'] ?? '';
    $methodType = $input['method'] ?? 'GCash';
    $refNo = $input['ref_no'] ?? '';
    $amount = $input['amount'] ?? 0;
    $status = $input['status'] ?? 'Confirmed';

    $stmt = $pdo->prepare("INSERT INTO payments (id, student_name, method, ref_no, amount, status)
                           VALUES (?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE status=?");
    $stmt->execute([$id, $studentName, $methodType, $refNo, $amount, $status, $status]);

    echo json_encode(['status' => 'success', 'message' => 'Payment record saved', 'id' => $id]);
}
?>
