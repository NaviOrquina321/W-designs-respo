<?php
// api/sessions.php - Session Management & Booking REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

// A session can only start or finish after the admin has confirmed its GCash payment.
function requirePaymentConfirmed($pdo, $session) {
    $st = $pdo->prepare("SELECT status FROM payments WHERE ref_no = ?");
    $st->execute([$session['gcash_ref']]);
    $pay = $st->fetch();
    if (!$pay || $pay['status'] !== 'Confirmed') {
        jsonError(400, 'The payment for this session has not been confirmed by the admin yet.');
    }
}

if ($method === 'GET') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT * FROM sessions ORDER BY created_at DESC");
        $sessions = $stmt->fetchAll();
    } elseif ($user['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $sessions = $stmt->fetchAll();
    } elseif ($user['role'] === 'tutor') {
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE tutor_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $sessions = $stmt->fetchAll();
    } else {
        jsonError(403, 'Forbidden');
    }
    echo json_encode(['status' => 'success', 'data' => toCamelArray($sessions)]);
} elseif ($method === 'POST') {
    $user = requireRole(['student']);
    $input = getInput();

    $tutorId = trim($input['tutor_id'] ?? ($input['tutorId'] ?? ''));
    $subject = trim($input['subject'] ?? 'Mathematics');
    $sessionDate = trim($input['session_date'] ?? ($input['date'] ?? ''));
    $timeSlot = trim($input['time_slot'] ?? ($input['timeSlot'] ?? ''));

    if (empty($tutorId) || empty($sessionDate) || empty($timeSlot)) {
        jsonError(400, 'Missing required booking details (tutor, date, time slot).');
    }

    // Strict Date Validation (Y-m-d)
    $d = DateTime::createFromFormat('Y-m-d', $sessionDate);
    if (!$d || $d->format('Y-m-d') !== $sessionDate) {
        jsonError(400, 'Invalid date format. Use Y-m-d.');
    }
    $today = date('Y-m-d');
    if ($sessionDate < $today) {
        jsonError(400, 'Cannot book a session for a past date.');
    }
    $maxDate = date('Y-m-d', strtotime('+1 year'));
    if ($sessionDate > $maxDate) {
        jsonError(400, 'Cannot book more than 1 year in advance.');
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
    $stTut = $pdo->prepare("SELECT id, name, hourly_rate, approval_status, deactivated, available_days, blocked_dates, subjects, availability_slots FROM tutors WHERE id = ?");
    $stTut->execute([$tutorId]);
    $tutorRecord = $stTut->fetch();
    if (!$tutorRecord) {
        jsonError(404, 'Selected tutor not found.');
    }
    if ($tutorRecord['approval_status'] !== 'Approved' || !empty($tutorRecord['deactivated'])) {
        jsonError(400, 'Selected tutor is currently not available for booking.');
    }

    // Subject case-insensitive check
    $tutorSubjects = array_map('strtolower', array_map('trim', explode(',', $tutorRecord['subjects'] ?? '')));
    if (!in_array(strtolower($subject), $tutorSubjects, true)) {
        jsonError(400, "Tutor does not offer the subject '$subject'.");
    }

    // Time slot validation: tutor's availability_slots OR an admin 'Available' schedule row
    $tutorSlots = array_map('trim', explode(',', $tutorRecord['availability_slots'] ?? ''));
    $tutorSlots = array_filter($tutorSlots);

    // Check if an admin Available schedule row exists for this tutor, date, slot
    $stSchCheck = $pdo->prepare("SELECT id FROM schedules WHERE tutor_id = ? AND date_slot = ? AND status = 'Available'");
    $stSchCheck->execute([$tutorRecord['id'], "$sessionDate $timeSlot"]);
    $matchingScheduleRow = $stSchCheck->fetch();

    if (!in_array($timeSlot, $tutorSlots, true) && !$matchingScheduleRow) {
        jsonError(400, 'Invalid time slot for the selected tutor.');
    }

    // Validate tutor blocked dates
    if (!empty($tutorRecord['blocked_dates'])) {
        $blockedList = array_map('trim', explode(',', $tutorRecord['blocked_dates']));
        if (in_array($sessionDate, $blockedList, true)) {
            jsonError(400, 'Selected tutor is unavailable on this date.');
        }
    }

    // Fee breakdown: Student pays Rate + 10% Service Fee; Tutor gets 100% Rate
    $hourlyRate = intval($tutorRecord['hourly_rate']);
    $commissionFee = intval(round($hourlyRate * 0.10));
    $totalPaid = $hourlyRate + $commissionFee;

    $sessionId = newId('SES-');
    $gcashRef = 'GC-' . random_int(1000000000, 9999999999);
    $studentName = $studentRecord['name'];
    $tutorName = $tutorRecord['name'];

    try {
        $pdo->beginTransaction();

        // Lock the tutor row so two simultaneous bookings cannot take the same slot (MySQL only)
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $pdo->prepare("SELECT id FROM tutors WHERE id = ? FOR UPDATE")->execute([$tutorRecord['id']]);
        }

        // Concurrency Guard: Reject double booking
        $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND status != 'Cancelled'");
        $stChk->execute([$tutorRecord['id'], $sessionDate, $timeSlot]);
        if ($stChk->fetch()['cnt'] > 0) {
            $pdo->rollBack();
            jsonError(400, 'This tutor is already booked for the selected date and time slot.');
        }

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

        // 2. Insert payment row with status 'Pending' and payout_status 'Unpaid'
        $paymentId = newId('PAY-');
        $stmtPay = $pdo->prepare("INSERT INTO payments (id, student_id, student_name, tutor_id, tutor_name, method, ref_no, amount, status, payout_status) VALUES (?, ?, ?, ?, ?, 'GCash', ?, ?, 'Pending', 'Unpaid')");
        $stmtPay->execute([
            $paymentId,
            $studentRecord['id'],
            $studentName,
            $tutorRecord['id'],
            $tutorName,
            $gcashRef,
            $totalPaid
        ]);

        // 3. Connect schedules: insert or update schedule row with status 'Booked'
        if ($matchingScheduleRow) {
            $stSchUpd = $pdo->prepare("UPDATE schedules SET student_id = ?, student_name = ?, subject = ?, status = 'Booked' WHERE id = ?");
            $stSchUpd->execute([$studentRecord['id'], $studentName, $subject, $matchingScheduleRow['id']]);
        } else {
            $schId = newId('SCH-');
            $stSchIns = $pdo->prepare("INSERT INTO schedules (id, tutor_id, tutor_name, student_id, student_name, date_slot, subject, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Booked')");
            $stSchIns->execute([$schId, $tutorRecord['id'], $tutorName, $studentRecord['id'], $studentName, "$sessionDate $timeSlot", $subject]);
        }

        // 4. Server notifications
        notify($pdo, 'student', $studentName, 'Session Booked', 'Session booked. Payment awaiting admin confirmation.', $studentRecord['id']);
        notify($pdo, 'tutor', $tutorName, 'New Match/Session Request', "$studentName booked a $subject tutoring session for $sessionDate at $timeSlot.", $tutorRecord['id']);
        notify($pdo, 'admin', 'System Admin', 'New Session & Payment', "New session booked by $studentName with $tutorName ($sessionDate $timeSlot). Payment pending confirmation.");

        $pdo->commit();

        $stNew = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
        $stNew->execute([$sessionId]);
        $row = $stNew->fetch();

        echo json_encode([
            'status' => 'success',
            'message' => 'Session booked successfully.',
            'session' => toCamelArray($row)
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
        if ($session['tutor_id'] !== $user['id']) {
            jsonError(403, 'You can only manage your own teaching sessions.');
        }

        $fields = [];
        $params = [];

        // Status transition handling
        if (isset($input['status'])) {
            $newStatus = trim($input['status']);
            if ($newStatus === 'In Progress') {
                if ($session['status'] !== 'Confirmed') {
                    jsonError(400, 'Can only start a session that is Confirmed.');
                }
                requirePaymentConfirmed($pdo, $session);
                $fields[] = "`status` = 'In Progress'";
                notify($pdo, 'student', $session['student_name'], 'Session Live', 'Your tutor started the session, join now.', $session['student_id']);
            } elseif ($newStatus === 'Completed') {
                if ($session['status'] !== 'Confirmed' && $session['status'] !== 'In Progress') {
                    jsonError(400, 'Can only complete a Confirmed or In Progress session.');
                }
                requirePaymentConfirmed($pdo, $session);
                $fields[] = "`status` = 'Completed'";
                // sessions_completed rises once
                $stInc = $pdo->prepare("UPDATE students SET sessions_completed = sessions_completed + 1 WHERE id = ?");
                $stInc->execute([$session['student_id']]);
                notify($pdo, 'student', $session['student_name'], 'Session Completed', 'Your session has been completed by the tutor.', $session['student_id']);
            } else {
                jsonError(400, 'Invalid status transition for tutor.');
            }
        }

        // Tutor remarks go only to notes
        if (isset($input['notes'])) {
            $fields[] = "`notes` = ?";
            $params[] = trim($input['notes']);
        }

        // Reschedule by tutor: allowed only when status is 'Confirmed'
        if (isset($input['session_date']) || isset($input['time_slot'])) {
            if ($session['status'] !== 'Confirmed') {
                jsonError(400, 'Can only reschedule confirmed sessions.');
            }
            $newDate = trim($input['session_date'] ?? $session['session_date']);
            $newSlot = trim($input['time_slot'] ?? $session['time_slot']);

            $d = DateTime::createFromFormat('Y-m-d', $newDate);
            if (!$d || $d->format('Y-m-d') !== $newDate) {
                jsonError(400, 'Invalid date format.');
            }
            if ($newDate < date('Y-m-d')) {
                jsonError(400, 'Cannot reschedule to a past date.');
            }

            // Check tutor slots
            $stT = $pdo->prepare("SELECT availability_slots FROM tutors WHERE id = ?");
            $stT->execute([$session['tutor_id']]);
            $tRec = $stT->fetch();
            $tSlots = array_map('trim', explode(',', $tRec['availability_slots'] ?? ''));

            $stSchCheck = $pdo->prepare("SELECT id FROM schedules WHERE tutor_id = ? AND date_slot = ? AND status = 'Available'");
            $stSchCheck->execute([$session['tutor_id'], "$newDate $newSlot"]);
            $mSch = $stSchCheck->fetch();

            if (!in_array($newSlot, $tSlots, true) && !$mSch) {
                jsonError(400, 'Proposed slot is not in tutor availability.');
            }

            // Check double booking
            $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND id != ? AND status != 'Cancelled'");
            $stChk->execute([$session['tutor_id'], $newDate, $newSlot, $sessionId]);
            if ($stChk->fetch()['cnt'] > 0) {
                jsonError(400, 'This tutor is already booked for the proposed rescheduled time slot.');
            }

            // Update schedules table entry
            $oldSlot = $session['session_date'] . ' ' . $session['time_slot'];
            $stSchUpd = $pdo->prepare("UPDATE schedules SET date_slot = ? WHERE tutor_id = ? AND date_slot = ? AND student_id = ?");
            $stSchUpd->execute(["$newDate $newSlot", $session['tutor_id'], $oldSlot, $session['student_id']]);

            $fields[] = "`session_date` = ?";
            $params[] = $newDate;
            $fields[] = "`time_slot` = ?";
            $params[] = $newSlot;

            notify($pdo, 'student', $session['student_name'], 'Session Rescheduled', "Your session with {$session['tutor_name']} has been rescheduled to $newDate at $newSlot.", $session['student_id']);
        }

        if (!empty($fields)) {
            $params[] = $sessionId;
            $sql = "UPDATE sessions SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        echo json_encode(['status' => 'success', 'message' => 'Session updated by tutor.']);
    } elseif ($user['role'] === 'student') {
        if ($session['student_id'] !== $user['id']) {
            jsonError(403, 'You can only rate your own sessions.');
        }

        if ($session['status'] !== 'Completed') {
            jsonError(400, 'You can only rate a completed session.');
        }

        if (!empty($session['rating']) && intval($session['rating']) > 0) {
            jsonError(400, 'You have already rated this session.');
        }

        $rating = intval($input['rating'] ?? 0);
        $feedback = trim($input['feedback'] ?? '');

        if ($rating < 1 || $rating > 5) {
            jsonError(400, 'Rating must be between 1 and 5 stars.');
        }

        // Store rating and student's feedback
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
            $newStatus = trim($input['status']);
            $validAdminStatuses = ['Confirmed', 'In Progress', 'Completed', 'Cancelled'];
            if (!in_array($newStatus, $validAdminStatuses, true)) {
                jsonError(400, 'Invalid status for admin update.');
            }

            // Only forward transitions: a Completed or Cancelled session can never be reopened
            $allowedNext = [
                'Confirmed' => ['In Progress', 'Completed', 'Cancelled'],
                'In Progress' => ['Completed', 'Cancelled'],
                'Completed' => [],
                'Cancelled' => []
            ];
            if ($newStatus !== $session['status']) {
                if (!in_array($newStatus, $allowedNext[$session['status']] ?? [], true)) {
                    jsonError(400, "A {$session['status']} session cannot be changed to $newStatus.");
                }
                if ($newStatus === 'In Progress' || $newStatus === 'Completed') {
                    requirePaymentConfirmed($pdo, $session);
                }

                $fields[] = "`status` = ?";
                $params[] = $newStatus;

                if ($newStatus === 'Completed') {
                    $stInc = $pdo->prepare("UPDATE students SET sessions_completed = sessions_completed + 1 WHERE id = ?");
                    $stInc->execute([$session['student_id']]);
                    notify($pdo, 'student', $session['student_name'], 'Session Completed', 'Your session has been marked completed by the admin.', $session['student_id']);
                }

                if ($newStatus === 'Cancelled') {
                    // Free schedule slot
                    $oldSlot = $session['session_date'] . ' ' . $session['time_slot'];
                    $stSchDel = $pdo->prepare("DELETE FROM schedules WHERE tutor_id = ? AND date_slot = ? AND student_id = ?");
                    $stSchDel->execute([$session['tutor_id'], $oldSlot, $session['student_id']]);

                    notify($pdo, 'student', $session['student_name'], 'Session Cancelled', "Your session with {$session['tutor_name']} on {$session['session_date']} was cancelled by Admin.", $session['student_id']);
                    notify($pdo, 'tutor', $session['tutor_name'], 'Session Cancelled', "Your session with {$session['student_name']} on {$session['session_date']} was cancelled by Admin.", $session['tutor_id']);
                }
            }
        }

        if (isset($input['session_date']) || isset($input['time_slot']) || isset($input['sessionDate']) || isset($input['timeSlot'])) {
            $newDate = trim($input['session_date'] ?? ($input['sessionDate'] ?? $session['session_date']));
            $newSlot = trim($input['time_slot'] ?? ($input['timeSlot'] ?? $session['time_slot']));

            if ($session['status'] !== 'Confirmed') {
                jsonError(400, 'Can only reschedule confirmed sessions.');
            }

            $d = DateTime::createFromFormat('Y-m-d', $newDate);
            if (!$d || $d->format('Y-m-d') !== $newDate) {
                jsonError(400, 'Invalid date format.');
            }
            if ($newDate < date('Y-m-d')) {
                jsonError(400, 'Cannot reschedule to a past date.');
            }

            // Check double booking
            $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND id != ? AND status != 'Cancelled'");
            $stChk->execute([$session['tutor_id'], $newDate, $newSlot, $sessionId]);
            if ($stChk->fetch()['cnt'] > 0) {
                jsonError(400, 'Tutor is already booked for this date and time slot.');
            }

            $oldSlot = $session['session_date'] . ' ' . $session['time_slot'];
            $stSchUpd = $pdo->prepare("UPDATE schedules SET date_slot = ? WHERE tutor_id = ? AND date_slot = ? AND student_id = ?");
            $stSchUpd->execute(["$newDate $newSlot", $session['tutor_id'], $oldSlot, $session['student_id']]);

            $fields[] = "`session_date` = ?";
            $params[] = $newDate;
            $fields[] = "`time_slot` = ?";
            $params[] = $newSlot;

            notify($pdo, 'student', $session['student_name'], 'Session Rescheduled', "Your session with {$session['tutor_name']} was rescheduled to $newDate at $newSlot.", $session['student_id']);
            notify($pdo, 'tutor', $session['tutor_name'], 'Session Rescheduled', "Your session with {$session['student_name']} was rescheduled to $newDate at $newSlot.", $session['tutor_id']);
        }

        if (isset($input['payout_status'])) {
            $payoutVal = trim($input['payout_status']);
            if ($payoutVal === 'Paid Out' && $session['status'] !== 'Completed') {
                jsonError(400, 'Payout can only be issued for completed sessions.');
            }
            $fields[] = "`payout_status` = ?";
            $params[] = $payoutVal;

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