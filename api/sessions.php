<?php
// api/sessions.php - Session Management & Booking REST API
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
    if ($sessionDate === $today) {
        $slotStart = parseSlotStartTime($sessionDate, $timeSlot);
        if ($slotStart > 0 && $slotStart < time()) {
            jsonError(400, 'Cannot book a time slot earlier today that has already passed.');
        }
    }
    $maxDate = date('Y-m-d', strtotime('+1 year'));
    if ($sessionDate > $maxDate) {
        jsonError(400, 'Cannot book more than 1 year in advance.');
    }

    // Verify student is active
    $stStu = $pdo->prepare("SELECT id, name, validated, deactivated FROM students WHERE id = ? OR LOWER(email) = ?");
    $stStu->execute([$user['id'], strtolower($user['email'])]);
    $studentRecord = $stStu->fetch();
    if (!$studentRecord || !empty($studentRecord['deactivated'])) {
        jsonError(403, 'Your student account is inactive or not found.');
    }

    // Verify tutor exists and is not deactivated
    $stTut = $pdo->prepare("SELECT id, name, hourly_rate, deactivated, available_days, blocked_dates, subjects, availability_slots FROM tutors WHERE id = ?");
    $stTut->execute([$tutorId]);
    $tutorRecord = $stTut->fetch();
    if (!$tutorRecord) {
        jsonError(404, 'Selected tutor not found.');
    }
    if (!empty($tutorRecord['deactivated'])) {
        jsonError(400, 'Selected tutor is inactive or currently not available for booking.');
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

    $customGcashRef = trim($input['gcash_ref'] ?? ($input['gcashRef'] ?? ''));
    $studentName = $studentRecord['name'];
    $tutorName = $tutorRecord['name'];

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
                    jsonError(400, 'Can only start a session that is Confirmed and paid.');
                }

                // Check time window: 15 minutes before slot start until slot end
                $now = time();
                $slotStart = parseSlotStartTime($session['session_date'], $session['time_slot']);
                $slotEnd = parseSlotEndTime($session['session_date'], $session['time_slot']);

                if ($slotStart > 0 && ($now < ($slotStart - 900) || $now > $slotEnd)) {
                    jsonError(400, 'Session can only be started during its scheduled time window (from 15 minutes before slot start until slot end).');
                }

                $nowFormatted = date('Y-m-d H:i:s');
                $fields[] = "`status` = 'In Progress'";
                $fields[] = "`started_at` = ?";
                $params[] = $nowFormatted;
                notify($pdo, 'student', $session['student_id'], 'Session Live', 'Your tutor started the session, join now.');
            } elseif ($newStatus === 'Completed') {
                if ($session['status'] !== 'Confirmed' && $session['status'] !== 'In Progress') {
                    jsonError(400, 'Can only complete a Confirmed or In Progress session.');
                }
                $fields[] = "`status` = 'Completed'";
                // sessions_completed rises once
                $stInc = $pdo->prepare("UPDATE students SET sessions_completed = sessions_completed + 1 WHERE id = ?");
                $stInc->execute([$session['student_id']]);
                notify($pdo, 'student', $session['student_id'], 'Session Completed', 'Your session has been completed by the tutor.');
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

            notify($pdo, 'student', $session['student_id'], 'Session Rescheduled', "Your session with {$session['tutor_name']} has been rescheduled to $newDate at $newSlot.");
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

        // Student reschedule handler
        if (isset($input['session_date']) || isset($input['time_slot'])) {
            if ($session['status'] !== 'Confirmed') {
                jsonError(400, 'Can only reschedule confirmed sessions.');
            }

            $currentSlotTs = parseSlotStartTime($session['session_date'], $session['time_slot']);
            if (($currentSlotTs - time()) <= 86400) {
                jsonError(400, 'Rescheduling is only allowed at least 24 hours prior to the session start time.');
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
            if ($newDate === date('Y-m-d')) {
                $newStart = parseSlotStartTime($newDate, $newSlot);
                if ($newStart > 0 && $newStart < time()) {
                    jsonError(400, 'Cannot reschedule to a slot earlier today that has already passed.');
                }
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
                jsonError(400, 'Tutor is already booked for the proposed time slot.');
            }

            // Update schedules table entry
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

        // Trigger notification to tutor
        notify($pdo, 'tutor', $session['tutor_id'], "New $rating-Star Rating Received", "{$session['student_name']} rated your {$session['subject']} session $rating star(s)" . ($feedback ? ": \"$feedback\"" : "."), [
            'type' => 'session_rated',
            'recipient_user_id' => $session['tutor_id'],
            'recipient_role' => 'tutor',
            'related_session_id' => $sessionId,
            'action_url' => "#session-$sessionId",
            'priority' => 'normal'
        ]);

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
            $fields[] = "`status` = ?";
            $params[] = $newStatus;

            if ($newStatus === 'Cancelled') {
                // Free schedule slot
                $oldSlot = $session['session_date'] . ' ' . $session['time_slot'];
                $stSchDel = $pdo->prepare("DELETE FROM schedules WHERE tutor_id = ? AND date_slot = ? AND student_id = ?");
                $stSchDel->execute([$session['tutor_id'], $oldSlot, $session['student_id']]);

                notify($pdo, 'student', $session['student_id'], 'Session Cancelled', "Your session with {$session['tutor_name']} on {$session['session_date']} was cancelled by Admin.");
                notify($pdo, 'tutor', $session['tutor_id'], 'Session Cancelled', "Your session with {$session['student_name']} on {$session['session_date']} was cancelled by Admin.");
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

            notify($pdo, 'student', $session['student_id'], 'Session Rescheduled', "Your session with {$session['tutor_name']} was rescheduled to $newDate at $newSlot.");
            notify($pdo, 'tutor', $session['tutor_id'], 'Session Rescheduled', "Your session with {$session['student_name']} was rescheduled to $newDate at $newSlot.");
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
