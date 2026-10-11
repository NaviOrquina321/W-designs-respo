<?php
// api/sessions.php - Session Management & Booking REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT * FROM sessions ORDER BY created_at DESC");
        $sessions = $stmt->fetchAll();
    } elseif ($user['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE student_id = ? OR LOWER(student_name) = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id'], strtolower($user['name'])]);
        $sessions = $stmt->fetchAll();
    } elseif ($user['role'] === 'tutor') {
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE tutor_id = ? OR LOWER(tutor_name) = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id'], strtolower($user['name'])]);
        $sessions = $stmt->fetchAll();
    } else {
        jsonError(403, 'Forbidden');
    }
    echo json_encode(['status' => 'success', 'data' => $sessions]);
} elseif ($method === 'POST') {
    $user = requireRole(['student']);
    $input = getInput();

    $tutorId = trim($input['tutor_id'] ?? ($input['tutorId'] ?? ''));
    $subject = trim($input['subject'] ?? 'Mathematics');
    $sessionDate = trim($input['session_date'] ?? ($input['date'] ?? ''));
    $timeSlot = trim($input['time_slot'] ?? ($input['timeSlot'] ?? '02:00 PM'));

    if (empty($tutorId) || empty($sessionDate) || empty($timeSlot)) {
        jsonError(400, 'Missing required booking details (tutor, date, time slot).');
    }

    // Reject past date
    $today = date('Y-m-d');
    if ($sessionDate < $today) {
        jsonError(400, 'Cannot book a session for a past date.');
    }

    // Verify student is validated and not deactivated
    $stStu = $pdo->prepare("SELECT id, name, validated, deactivated FROM students WHERE id = ? OR LOWER(email) = ?");
    $stStu->execute([$user['id'], strtolower($user['email'])]);
    $studentRecord = $stStu->fetch();
    if (!$studentRecord || !empty($studentRecord['deactivated'])) {
        jsonError(403, 'Your student account is inactive or not found.');
    }
    if (empty($studentRecord['validated'])) {
        jsonError(403, 'Your student account is pending validation by an administrator.');
    }

    // Verify tutor exists, is approved, and not deactivated
    $stTut = $pdo->prepare("SELECT id, name, hourly_rate, approval_status, deactivated FROM tutors WHERE id = ?");
    $stTut->execute([$tutorId]);
    $tutorRecord = $stTut->fetch();
    if (!$tutorRecord) {
        jsonError(404, 'Selected tutor not found.');
    }
    if ($tutorRecord['approval_status'] !== 'Approved' || !empty($tutorRecord['deactivated'])) {
        jsonError(400, 'Selected tutor is currently not available for booking.');
    }

    // Reject double booking (same tutor + date + time_slot)
    $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND status != 'Cancelled'");
    $stChk->execute([$tutorRecord['id'], $sessionDate, $timeSlot]);
    if ($stChk->fetch()['cnt'] > 0) {
        jsonError(400, 'This tutor is already booked for the selected date and time slot.');
    }

    // Server-computed fee breakdown: Student pays Rate + 10% Service Fee; Tutor gets 100% Rate
    $hourlyRate = intval($tutorRecord['hourly_rate']);
    $commissionFee = intval(round($hourlyRate * 0.10));
    $totalPaid = $hourlyRate + $commissionFee;

    $sessionId = newId('SES-');
    $gcashRef = 'GC-' . rand(1000000000, 9999999999);
    $studentName = $studentRecord['name'];
    $tutorName = $tutorRecord['name'];

    try {
        $pdo->beginTransaction();

        // 1. Insert session row
        $stmtSes = $pdo->prepare("INSERT INTO sessions (id, student_id, student_name, tutor_id, tutor_name, subject, session_date, time_slot, hourly_rate, commission_fee, total_paid, gcash_ref, status, payout_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', 'Unpaid', 'Booked via GCash e-Wallet.')");
        $stmtSes->execute([
            $sessionId,
            $studentRecord['id'],
            $studentName,
            $tutorRecord['id'],
            $tutorName,
            $subject,
            $sessionDate,
            $timeSlot,
            $hourlyRate,
            $commissionFee,
            $totalPaid,
            $gcashRef
        ]);

        // 2. Insert single corresponding payment row
        $paymentId = newId('PAY-');
        $stmtPay = $pdo->prepare("INSERT INTO payments (id, student_id, student_name, tutor_id, tutor_name, method, ref_no, amount, status, payout_status) VALUES (?, ?, ?, ?, ?, 'GCash', ?, ?, 'Confirmed', 'Pending')");
        $stmtPay->execute([
            $paymentId,
            $studentRecord['id'],
            $studentName,
            $tutorRecord['id'],
            $tutorName,
            $gcashRef,
            $totalPaid
        ]);

        // 3. Create notifications for student & tutor
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (id, target_role, target, title, message) VALUES (?, ?, ?, ?, ?)");
        $stmtNotif->execute([newId('notif-'), 'student', $studentName, 'Session Booked & Paid!', "Your $subject session with $tutorName is confirmed for $sessionDate at $timeSlot. Ref: $gcashRef"]);
        $stmtNotif->execute([newId('notif-'), 'tutor', $tutorName, 'New Student Session Booked', "$studentName booked a $subject tutoring session for $sessionDate at $timeSlot."]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Session booked and payment confirmed successfully.',
            'session' => [
                'id' => $sessionId,
                'student_id' => $studentRecord['id'],
                'student_name' => $studentName,
                'tutor_id' => $tutorRecord['id'],
                'tutor_name' => $tutorName,
                'subject' => $subject,
                'session_date' => $sessionDate,
                'time_slot' => $timeSlot,
                'hourly_rate' => $hourlyRate,
                'commission_fee' => $commissionFee,
                'total_paid' => $totalPaid,
                'gcash_ref' => $gcashRef,
                'status' => 'Confirmed',
                'payout_status' => 'Unpaid'
            ]
        ]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Session booking DB error: " . $e->getMessage());
        jsonError(500, 'Database transaction failed during booking.');
    }
} elseif ($method === 'PUT') {
    $user = requireLogin();
    $input = getInput();
    $sessionId = trim($input['id'] ?? '');

    if (empty($sessionId)) {
        jsonError(400, 'Session ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
    $st->execute([$sessionId]);
    $session = $st->fetch();

    if (!$session) {
        jsonError(404, 'Session record not found.');
    }

    if ($user['role'] === 'tutor') {
        if ($session['tutor_id'] !== $user['id'] && strtolower($session['tutor_name']) !== strtolower($user['name'])) {
            jsonError(403, 'You can only manage your own teaching sessions.');
        }

        $fields = [];
        $params = [];

        if (isset($input['status']) && $input['status'] === 'Completed') {
            $fields[] = "`status` = 'Completed'";
            // Increment student sessions_completed
            $stInc = $pdo->prepare("UPDATE students SET sessions_completed = sessions_completed + 1 WHERE id = ? OR LOWER(name) = ?");
            $stInc->execute([$session['student_id'], strtolower($session['student_name'])]);
        }
        if (isset($input['tutorStarted'])) {
            $fields[] = "`notes` = ?";
            $params[] = 'Session started by tutor.';
        }
        if (isset($input['notes'])) {
            $fields[] = "`notes` = ?";
            $params[] = trim($input['notes']);
        }
        if (isset($input['feedback'])) {
            $fields[] = "`feedback` = ?";
            $params[] = trim($input['feedback']);
        }

        // Reschedule handling
        if (isset($input['session_date']) && isset($input['time_slot'])) {
            $newDate = trim($input['session_date']);
            $newSlot = trim($input['time_slot']);

            if ($newDate < date('Y-m-d')) {
                jsonError(400, 'Cannot reschedule to a past date.');
            }

            // Check double booking
            $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND id != ? AND status != 'Cancelled'");
            $stChk->execute([$session['tutor_id'], $newDate, $newSlot, $sessionId]);
            if ($stChk->fetch()['cnt'] > 0) {
                jsonError(400, 'This tutor is already booked for the proposed rescheduled time slot.');
            }

            $fields[] = "`session_date` = ?";
            $params[] = $newDate;
            $fields[] = "`time_slot` = ?";
            $params[] = $newSlot;
        }

        if (!empty($fields)) {
            $params[] = $sessionId;
            $sql = "UPDATE sessions SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        echo json_encode(['status' => 'success', 'message' => 'Session updated by tutor.']);
    } elseif ($user['role'] === 'student') {
        if ($session['student_id'] !== $user['id'] && strtolower($session['student_name']) !== strtolower($user['name'])) {
            jsonError(403, 'You can only rate your own sessions.');
        }

        if ($session['status'] !== 'Completed') {
            jsonError(400, 'You can only rate a completed session.');
        }

        $rating = intval($input['rating'] ?? 0);
        $feedback = trim($input['feedback'] ?? '');

        if ($rating < 1 || $rating > 5) {
            jsonError(400, 'Rating must be between 1 and 5 stars.');
        }

        $stmt = $pdo->prepare("UPDATE sessions SET rating = ?, feedback = ? WHERE id = ?");
        $stmt->execute([$rating, $feedback, $sessionId]);

        // Recompute tutor rating and reviews_count on server
        $stAvg = $pdo->prepare("SELECT COUNT(*) as rev_cnt, AVG(rating) as avg_rat FROM sessions WHERE tutor_id = ? AND rating > 0 AND status = 'Completed'");
        $stAvg->execute([$session['tutor_id']]);
        $stats = $stAvg->fetch();

        if ($stats && $stats['rev_cnt'] > 0) {
            $newCnt = intval($stats['rev_cnt']);
            $newRat = round(floatval($stats['avg_rat']), 1);
            $stUpd = $pdo->prepare("UPDATE tutors SET rating = ?, reviews_count = ? WHERE id = ?");
            $stUpd->execute([$newRat, $newCnt, $session['tutor_id']]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Rating submitted successfully.']);
    } elseif ($user['role'] === 'admin') {
        $fields = [];
        $params = [];

        if (isset($input['status'])) {
            $fields[] = "`status` = ?";
            $params[] = trim($input['status']);
        }
        if (isset($input['payout_status'])) {
            $payoutVal = trim($input['payout_status']);
            if ($payoutVal === 'Paid Out' && $session['status'] !== 'Completed') {
                jsonError(400, 'Payout can only be issued for completed sessions.');
            }
            $fields[] = "`payout_status` = ?";
            $params[] = $payoutVal;

            // Sync payments table payout_status
            $stPay = $pdo->prepare("UPDATE payments SET payout_status = ? WHERE ref_no = ?");
            $stPay->execute([$payoutVal, $session['gcash_ref']]);
        }

        if (!empty($fields)) {
            $params[] = $sessionId;
            $sql = "UPDATE sessions SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        echo json_encode(['status' => 'success', 'message' => 'Session updated by admin.']);
    } else {
        jsonError(403, 'Forbidden action.');
    }
} else {
    jsonError(405, 'Method not allowed');
}
?>