<?php
// api/payments.php - Payments REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT * FROM payments ORDER BY created_at DESC");
        $payments = $stmt->fetchAll();
    } elseif ($user['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $payments = $stmt->fetchAll();
    } elseif ($user['role'] === 'tutor') {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE tutor_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $payments = $stmt->fetchAll();
    } else {
        jsonError(403, 'Forbidden');
    }
    echo json_encode(['status' => 'success', 'data' => toCamelArray($payments)]);
} elseif ($method === 'PUT') {
    $user = requireRole(['admin']);
    $input = getInput();
    $paymentId = trim($input['id'] ?? '');

    if (empty($paymentId)) {
        jsonError(400, 'Payment ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $st->execute([$paymentId]);
    $payment = $st->fetch();

    if (!$payment) {
        jsonError(404, 'Payment record not found.');
    }

    $fields = [];
    $params = [];

    if (isset($input['status'])) {
        $fields[] = "`status` = ?";
        $params[] = trim($input['status']);
    }

    if (isset($input['payout_status'])) {
        $payoutVal = trim($input['payout_status']);
        $fields[] = "`payout_status` = ?";
        $params[] = $payoutVal;

        // Sync linked sessions table row by gcash_ref = ref_no
        $stSes = $pdo->prepare("UPDATE sessions SET payout_status = ? WHERE gcash_ref = ?");
        $stSes->execute([$payoutVal, $payment['ref_no']]);
    }

    if (empty($fields)) {
        jsonError(400, 'No valid fields provided for update.');
    }

    $params[] = $paymentId;
    $sql = "UPDATE payments SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['status' => 'success', 'message' => 'Payment updated by admin.']);
} else {
    jsonError(405, 'Method not allowed');
}
?>