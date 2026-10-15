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
    $notifyConfirmed = false;
    $notifyPayout = false;

    if (isset($input['status'])) {
        $statusVal = trim($input['status']);
        if (!in_array($statusVal, ['Pending', 'Confirmed'], true)) {
            jsonError(400, "Payment status must be 'Pending' or 'Confirmed'.");
        }

        // Prevent reverting Confirmed -> Pending if payout is Paid Out OR linked session is In Progress / Completed
        if ($payment['status'] === 'Confirmed' && $statusVal === 'Pending') {
            if ($payment['payout_status'] === 'Paid Out') {
                jsonError(400, 'Cannot revert payment status to Pending after payout has been processed.');
            }
            $stSes = $pdo->prepare("SELECT status FROM sessions WHERE gcash_ref = ?");
            $stSes->execute([$payment['ref_no']]);
            $linkedSes = $stSes->fetch();
            if ($linkedSes && in_array($linkedSes['status'], ['In Progress', 'Completed'], true)) {
                jsonError(400, "Cannot revert payment status to Pending when linked session is '{$linkedSes['status']}'.");
            }
        }

        $fields[] = "`status` = ?";
        $params[] = $statusVal;

        if ($statusVal === 'Confirmed' && $payment['status'] !== 'Confirmed') {
            $notifyConfirmed = true;
        }
    }

    if (isset($input['payout_status']) || isset($input['payoutStatus'])) {
        $payoutVal = trim($input['payout_status'] ?? $input['payoutStatus']);
        if (!in_array($payoutVal, ['Unpaid', 'Paid Out'], true)) {
            jsonError(400, "Payout status must be 'Unpaid' or 'Paid Out'.");
        }

        // Prevent reverting Paid Out -> Unpaid
        if ($payment['payout_status'] === 'Paid Out' && $payoutVal === 'Unpaid') {
            jsonError(400, 'Cannot revert payout status once Paid Out.');
        }

        $currentStatus = isset($input['status']) ? trim($input['status']) : $payment['status'];

        if ($payoutVal === 'Paid Out') {
            if ($currentStatus !== 'Confirmed') {
                jsonError(400, 'Cannot process payout for an unconfirmed payment.');
            }
            $stSesChk = $pdo->prepare("SELECT status FROM sessions WHERE gcash_ref = ?");
            $stSesChk->execute([$payment['ref_no']]);
            $linkedSes = $stSesChk->fetch();
            if ($linkedSes && $linkedSes['status'] !== 'Completed') {
                jsonError(400, 'Cannot process payout until the tutoring session is marked Completed.');
            }
        }

        $fields[] = "`payout_status` = ?";
        $params[] = $payoutVal;

        // Sync linked sessions table row by gcash_ref = ref_no
        $stSes = $pdo->prepare("UPDATE sessions SET payout_status = ? WHERE gcash_ref = ?");
        $stSes->execute([$payoutVal, $payment['ref_no']]);

        if ($payoutVal === 'Paid Out' && $payment['payout_status'] !== 'Paid Out') {
            $notifyPayout = true;
        }
    }

    if (empty($fields)) {
        jsonError(400, 'No valid fields provided for update.');
    }

    $params[] = $paymentId;
    $sql = "UPDATE payments SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Call notify AFTER database update succeeds, using user IDs
    if ($notifyConfirmed) {
        notify($pdo, 'student', $payment['student_id'], 'Payment Confirmed', "Your payment of ₱{$payment['amount']} (Ref: {$payment['ref_no']}) has been confirmed by Admin.");
    }
    if ($notifyPayout) {
        notify($pdo, 'tutor', $payment['tutor_id'], 'Payout Processed', "Your payout for Ref: {$payment['ref_no']} has been processed.");
    }

    echo json_encode(['status' => 'success', 'message' => 'Payment updated by admin.']);
} else {
    jsonError(405, 'Method not allowed');
}
