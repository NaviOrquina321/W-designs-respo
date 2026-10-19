<?php
// api/sessions.php - Session Management & Booking REST API with Admin Audit & Forward-Only Rules
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/payment_provider.php';

$method = $_SERVER['REQUEST_METHOD'];

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
    checkSessionTimerThresholds($pdo, $sessions);
    echo json_encode(['status' => 'success', 'data' => toCamelArray($sessions)]);
} elseif ($method === 'POST') {
    $user = requireRole(['student']);
    $input = getInput();

    $tutorId = trim($input['tutor_id'] ?? ($input['tutorId'] ?? ''));
    $subject = trim($input['subject'] ?? 'Mathematics');
    $sessionDate = trim($input['session_date'] ?? ($input['date'] ?? ''));
    $timeSlot = trim($input['time_slot'] ?? ($input['timeSlot'] ?? ''));

    if (empty($tutorId) || empty($sessionDate) || empty($timeSlot)) {
        jsonError(422, 'Missing required booking details (tutor, date, time slot).');
    }

    $d = DateTime::createFromFormat('Y-m-d', $sessionDate);
    if (!$d || $d->format('Y-m-d') !== $sessionDate) {
        jsonError(422, 'Invalid date format. Use Y-m-d.');
    }
    $today = date('Y-m-d');
    if ($sessionDate < $today) {
        jsonError(422, 'Cannot book a session for a past date.');
    }
    if ($sessionDate === $today) {
        $slotStart = parseSlotStartTime($sessionDate, $timeSlot);
        if ($slotStart > 0 && $slotStart < time()) {
            jsonError(422, 'Cannot book a time slot earlier today that has already passed.');
        }
    }
    $maxDate = date('Y-m-d', strtotime('+1 year'));
    if ($sessionDate > $maxDate) {
        jsonError(422, 'Cannot book more than 1 year in advance.');
    }

    $stStu = $pdo->prepare("SELECT id, name, validated, deactivated FROM students WHERE id = ? OR LOWER(email) = ?");
    $stStu->execute([$user['id'], strtolower($user['email'])]);
    $studentRecord = $stStu->fetch();
    if (!$studentRecord || !empty($studentRecord['deactivated'])) {
        jsonError(403, 'Your student account is inactive or deactivated.');
    }

    $stTut = $pdo->prepare("SELECT id, name, hourly_rate, deactivated, available_days, blocked_dates, subjects, availability_slots FROM tutors WHERE id = ?");
    $stTut->execute([$tutorId]);
    $tutorRecord = $stTut->fetch();
    if (!$tutorRecord) {
        jsonError(404, 'Selected tutor not found.');
    }
    if (!empty($tutorRecord['deactivated'])) {
        jsonError(422, 'Selected tutor is inactive or currently not available for booking.');
    }

    $tutorSubjects = array_map('strtolower', array_map('trim', explode(',', $tutorRecord['subjects'] ?? '')));
    if (!in_array(strtolower($subject), $tutorSubjects, true)) {
        jsonError(422, "Tutor does not offer the subject '$subject'.");
    }

    $tutorSlots = array_map('trim', explode(',', $tutorRecord['availability_slots'] ?? ''));
    $tutorSlots = array_filter($tutorSlots);

    $stSchCheck = $pdo->prepare("SELECT id FROM schedules WHERE tutor_id = ? AND date_slot = ? AND status = 'Available'");
    $stSchCheck->execute([$tutorRecord['id'], "$sessionDate $timeSlot"]);
    $matchingScheduleRow = $stSchCheck->fetch();

    if (!in_array($timeSlot, $tutorSlots, true) && !$matchingScheduleRow) {
        jsonError(422, 'Invalid time slot for the selected tutor.');
    }

    if (!empty($tutorRecord['blocked_dates'])) {
        $blockedList = array_map('trim', explode(',', $tutorRecord['blocked_dates']));
        if (in_array($sessionDate, $blockedList, true)) {
            jsonError(422, 'Selected tutor is unavailable on this date.');
        }
    }

    $customGcashRef = trim($input['gcash_ref'] ?? ($input['gcashRef'] ?? ''));
    if ($customGcashRef !== '' && !preg_match('/^[A-Za-z0-9\-]{6,30}$/', $customGcashRef)) {
        jsonError(400, 'GCash reference must be 6-30 letters, numbers or dashes.');
    }

    try {
        $createdSession = processDemoGCashPayment($pdo, $studentRecord, $tutorRecord, $subject, $sessionDate, $timeSlot, $customGcashRef);

        echo json_encode([
            'status' => 'success',
            'message' => 'Demo GCash payment processed and session confirmed.',
            'session' => toCamelArray($createdSession)
        ]);
    } catch (Exception $e) {
        error_log("Session booking DB error: " . $e->getMessage());
        jsonError(500, 'Transaction failed during booking: ' . $e->getMessage());
    }
} elseif ($method === 'PUT') {
    $user = requireLogin();
    $input = getInput();
    $sessionId = trim($input['id'] ?? '');

    if (empty($sessionId)) {
        jsonError(422, 'Session ID required.');
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

        if (isset($input['status'])) {
            $newStatus = trim($input['status']);
            if ($newStatus === 'In Progress') {
                if ($session['status'] !== 'Confirmed') {
                    jsonError(422, 'Can only start a session that is Confirmed and paid.');
                }
                $nowFormatted = date('Y-m-d H:i:s');
                $fields[] = "`status` = 'In Progress'";
                $fields[] = "`started_at` = ?";
                $params[] = $nowFormatted;
                notify($pdo, 'student', $session['student_id'], 'Session Live', 'Your tutor started the session, join now.');
            } elseif ($newStatus === 'Completed') {
                if ($session['status'] !== 'Confirmed' && $session['status'] !== 'In Progress') {
                    jsonError(422, 'Can only complete a Confirmed or In Progress session.');
                }
                $fields[] = "`status` = 'Completed'";
                $stInc = $pdo->prepare("UPDATE students SET sessions_completed = sessions_completed + 1 WHERE id = ?");
                $stInc->execute([$session['student_id']]);
                notify($pdo, 'student', $session['student_id'], 'Session Completed', 'Your session has been completed by the tutor.');
            } else {
                jsonError(422, 'Invalid status transition for tutor.');
            }
        }

        if (isset($input['notes'])) {
            $fields[] = "`notes` = ?";
            $params[] = trim($input['notes']);
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
            jsonError(403, 'You can only manage your own sessions.');
        }

        if (isset($input['session_date']) || isset($input['time_slot'])) {
            if ($session['status'] !== 'Confirmed') {
                jsonError(422, 'Can only reschedule confirmed sessions.');
            }

            $currentSlotTs = parseSlotStartTime($session['session_date'], $session['time_slot']);
            if (($currentSlotTs - time()) <= 86400) {
                jsonError(422, 'Rescheduling is only allowed at least 24 hours prior to the session start time.');
            }

            $newDate = trim($input['session_date'] ?? $session['session_date']);
            $newSlot = trim($input['time_slot'] ?? $session['time_slot']);

            $d = DateTime::createFromFormat('Y-m-d', $newDate);
            if (!$d || $d->format('Y-m-d') !== $newDate) {
                jsonError(422, 'Invalid date format.');
            }
            if ($newDate < date('Y-m-d')) {
                jsonError(422, 'Cannot reschedule to a past date.');
            }

            $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND id != ? AND status != 'Cancelled'");
            $stChk->execute([$session['tutor_id'], $newDate, $newSlot, $sessionId]);
            if ($stChk->fetch()['cnt'] > 0) {
                jsonError(422, 'Tutor is already booked for the proposed time slot.');
            }

            $oldSlot = $session['session_date'] . ' ' . $session['time_slot'];
            $stSchUpd = $pdo->prepare("UPDATE schedules SET date_slot = ? WHERE tutor_id = ? AND date_slot = ? AND student_id = ?");
            $stSchUpd->execute(["$newDate $newSlot", $session['tutor_id'], $oldSlot, $session['student_id']]);

            $stmtUpd = $pdo->prepare("UPDATE sessions SET session_date = ?, time_slot = ? WHERE id = ?");
            $stmtUpd->execute([$newDate, $newSlot, $sessionId]);

            notify($pdo, 'tutor', $session['tutor_id'], 'Session Rescheduled by Student', "{$session['student_name']} rescheduled their session to $newDate at $newSlot.");

            echo json_encode(['status' => 'success', 'message' => 'Session rescheduled successfully.']);
            exit();
        }

        if ($session['status'] !== 'Completed') {
            jsonError(422, 'You can only rate a completed session.');
        }

        if (!empty($session['rating']) && intval($session['rating']) > 0) {
            jsonError(422, 'You have already rated this session.');
        }

        $rating = intval($input['rating'] ?? 0);
        $feedback = trim($input['feedback'] ?? '');

        if ($rating < 1 || $rating > 5) {
            jsonError(422, 'Rating must be between 1 and 5 stars.');
        }

        $stmt = $pdo->prepare("UPDATE sessions SET rating = ?, feedback = ? WHERE id = ?");
        $stmt->execute([$rating, $feedback, $sessionId]);

        $stAvg = $pdo->prepare("SELECT COUNT(*) as rev_cnt, AVG(rating) as avg_rat FROM sessions WHERE tutor_id = ? AND rating > 0 AND status = 'Completed'");
        $stAvg->execute([$session['tutor_id']]);
        $stats = $stAvg->fetch();

        if ($stats && $stats['rev_cnt'] > 0) {
            $newCnt = intval($stats['rev_cnt']);
            $newRat = round(floatval($stats['avg_rat']), 1);
            $stUpd = $pdo->prepare("UPDATE tutors SET rating = ?, reviews_count = ? WHERE id = ?");
            $stUpd->execute([$newRat, $newCnt, $session['tutor_id']]);
        }

        notify($pdo, 'tutor', $session['tutor_id'], "New $rating-Star Rating Received", "{$session['student_name']} rated your {$session['subject']} session $rating star(s)" . ($feedback ? ": \"$feedback\"" : "."));
        notify($pdo, 'admin', 'ADMIN-001', "Session Rated ($rating Stars)", "{$session['student_name']} rated {$session['tutor_name']} $rating star(s) for {$session['subject']}." . ($feedback ? " Feedback: \"$feedback\"" : ""));

        echo json_encode(['status' => 'success', 'message' => 'Rating submitted successfully.']);
    } elseif ($user['role'] === 'admin') {
        $fields = [];
        $params = [];
        $oldState = ['status' => $session['status'], 'session_date' => $session['session_date'], 'time_slot' => $session['time_slot']];

        // Status transition validation (forward-only rules)
        // Confirmed -> In Progress, Completed, Cancelled
        // In Progress -> Completed, Cancelled
        // Completed, Cancelled -> Final
        if (isset($input['status'])) {
            $newStatus = trim($input['status']);
            $currentStatus = $session['status'];

            if ($currentStatus === 'Completed' || $currentStatus === 'Cancelled') {
                jsonError(422, "Cannot change status of a $currentStatus session. Final status rules apply.");
            }

            if ($newStatus === 'Completed') {
                // Check payment status
                $stPayCheck = $pdo->prepare("SELECT status FROM payments WHERE ref_no = ?");
                $stPayCheck->execute([$session['gcash_ref']]);
                $payRow = $stPayCheck->fetch();
                $payStatus = $payRow['status'] ?? 'Confirmed';

                if ($payStatus !== 'Confirmed') {
                    jsonError(422, 'Cannot mark session as Completed until its payment status is Confirmed.');
                }
            }

            if ($currentStatus === 'Confirmed' && !in_array($newStatus, ['In Progress', 'Completed', 'Cancelled'], true)) {
                jsonError(422, 'Invalid status transition from Confirmed.');
            }

            if ($currentStatus === 'In Progress' && !in_array($newStatus, ['Completed', 'Cancelled'], true)) {
                jsonError(422, 'Invalid status transition from In Progress.');
            }

            $fields[] = "`status` = ?";
            $params[] = $newStatus;

            if ($newStatus === 'In Progress' && empty($session['started_at'])) {
                $fields[] = "`started_at` = ?";
                $params[] = date('Y-m-d H:i:s');
            }

            if ($newStatus === 'Cancelled') {
                $cancelReason = trim($input['reason'] ?? ($input['cancel_reason'] ?? 'Admin cancellation'));
                if (empty($cancelReason)) {
                    jsonError(422, 'Cancellation reason is required.');
                }

                // Free tutor slot
                $oldSlot = $session['session_date'] . ' ' . $session['time_slot'];
                $stSchDel = $pdo->prepare("UPDATE schedules SET status = 'Available', student_id = NULL, student_name = NULL WHERE tutor_id = ? AND date_slot = ?");
                $stSchDel->execute([$session['tutor_id'], $oldSlot]);

                // Flag payment "Needs refund/review"
                $stPayFlag = $pdo->prepare("UPDATE payments SET status = 'Needs refund/review' WHERE ref_no = ?");
                $stPayFlag->execute([$session['gcash_ref']]);

                notify($pdo, 'student', $session['student_id'], 'Session Cancelled by Admin', "Your session with {$session['tutor_name']} on {$session['session_date']} was cancelled. Reason: $cancelReason");
                notify($pdo, 'tutor', $session['tutor_id'], 'Session Cancelled by Admin', "Your session with {$session['student_name']} on {$session['session_date']} was cancelled. Reason: $cancelReason");
            } elseif ($newStatus === 'Completed') {
                $stInc = $pdo->prepare("UPDATE students SET sessions_completed = sessions_completed + 1 WHERE id = ?");
                $stInc->execute([$session['student_id']]);
                notify($pdo, 'student', $session['student_id'], 'Session Completed', "Your session with {$session['tutor_name']} was marked as Completed.");
                notify($pdo, 'tutor', $session['tutor_id'], 'Session Completed', "Your session with {$session['student_name']} was marked as Completed.");
            }
        }

        // Reschedule
        if (isset($input['session_date']) || isset($input['time_slot']) || isset($input['sessionDate']) || isset($input['timeSlot'])) {
            if ($session['status'] !== 'Confirmed') {
                jsonError(422, 'Can only reschedule confirmed sessions.');
            }

            $newDate = trim($input['session_date'] ?? ($input['sessionDate'] ?? $session['session_date']));
            $newSlot = trim($input['time_slot'] ?? ($input['timeSlot'] ?? $session['time_slot']));

            $d = DateTime::createFromFormat('Y-m-d', $newDate);
            if (!$d || $d->format('Y-m-d') !== $newDate) {
                jsonError(422, 'Invalid date format.');
            }
            if ($newDate < date('Y-m-d')) {
                jsonError(422, 'Cannot reschedule to a past date.');
            }

            // Conflict check
            $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND id != ? AND status != 'Cancelled'");
            $stChk->execute([$session['tutor_id'], $newDate, $newSlot, $sessionId]);
            if ($stChk->fetch()['cnt'] > 0) {
                jsonError(422, 'Tutor is already booked for this date and time slot.');
            }

            // Free old schedule slot, book new schedule slot
            $oldSlot = $session['session_date'] . ' ' . $session['time_slot'];
            $stSchUpd = $pdo->prepare("UPDATE schedules SET status = 'Available', student_id = NULL, student_name = NULL WHERE tutor_id = ? AND date_slot = ? AND student_id = ?");
            $stSchUpd->execute([$session['tutor_id'], $oldSlot, $session['student_id']]);

            $newSlotKey = "$newDate $newSlot";
            $stSchBook = $pdo->prepare("UPDATE schedules SET status = 'Booked', student_id = ?, student_name = ? WHERE tutor_id = ? AND date_slot = ?");
            $stSchBook->execute([$session['student_id'], $session['student_name'], $session['tutor_id'], $newSlotKey]);

            $fields[] = "`session_date` = ?";
            $params[] = $newDate;
            $fields[] = "`time_slot` = ?";
            $params[] = $newSlot;

            notify($pdo, 'student', $session['student_id'], 'Session Rescheduled', "Your session with {$session['tutor_name']} was rescheduled to $newDate at $newSlot.");
            notify($pdo, 'tutor', $session['tutor_id'], 'Session Rescheduled', "Your session with {$session['student_name']} was rescheduled to $newDate at $newSlot.");
        }

        if (isset($input['payout_status'])) {
            $payoutVal = trim($input['payout_status']);
            if ($payoutVal === 'Paid Out' && $session['status'] !== 'Completed') {
                jsonError(422, 'Payout can only be issued for completed sessions.');
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

            logAdminAction('Modify Sessions Schedule', $sessionId, $oldState, $input);
        }

        echo json_encode(['status' => 'success', 'message' => 'Session schedule updated successfully.']);
    } else {
        jsonError(403, 'Forbidden action.');
    }
} else {
    jsonError(405, 'Method not allowed');
}
