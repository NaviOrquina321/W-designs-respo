<?php
// api/payment_provider.php - GCash Demo Payment Provider
if (!isset($pdo)) {
    require_once __DIR__ . '/config.php';
}
require_once __DIR__ . '/helpers.php';

function processDemoGCashPayment($pdo, $student, $tutorRecord, $subject, $sessionDate, $timeSlot, $customGcashRef = null) {
    $hourlyRate = intval($tutorRecord['hourly_rate']);
    $commissionFee = intval(round($hourlyRate * 0.10));
    $totalPaid = $hourlyRate + $commissionFee;

    $sessionId = newId('SES-');
    $customGcashRef = trim($customGcashRef ?? '');
    $gcashRef = !empty($customGcashRef) ? $customGcashRef : ('GC-' . random_int(1000000000, 9999999999));
    $studentName = $student['name'];
    $tutorName = $tutorRecord['name'];

    $pdo->beginTransaction();

    try {
        // Double booking guard for tutor
        $stChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE tutor_id = ? AND session_date = ? AND time_slot = ? AND status != 'Cancelled'");
        $stChk->execute([$tutorRecord['id'], $sessionDate, $timeSlot]);
        if ($stChk->fetch()['cnt'] > 0) {
            $pdo->rollBack();
            jsonError(400, 'This tutor is already booked for the selected date and time slot.');
        }

        // Concurrent booking guard for student
        $stStuChk = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE student_id = ? AND session_date = ? AND time_slot = ? AND status != 'Cancelled'");
        $stStuChk->execute([$student['id'], $sessionDate, $timeSlot]);
        if ($stStuChk->fetch()['cnt'] > 0) {
            $pdo->rollBack();
            jsonError(400, 'You already have another tutoring session scheduled at this date and time slot.');
        }

        // 1. Create session (status Confirmed)
        $stmtSes = $pdo->prepare("INSERT INTO sessions (id, student_id, student_name, tutor_id, tutor_name, subject, session_date, time_slot, hourly_rate, commission_fee, total_paid, gcash_ref, status, payout_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', 'Unpaid', 'Booked via Demo GCash e-Wallet.')");
        $stmtSes->execute([
            $sessionId,
            $student['id'],
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

        // 2. Create payment (status Confirmed)
        $paymentId = newId('PAY-');
        $stmtPay = $pdo->prepare("INSERT INTO payments (id, student_id, student_name, tutor_id, tutor_name, method, ref_no, amount, status, payout_status) VALUES (?, ?, ?, ?, ?, 'GCash', ?, ?, 'Confirmed', 'Unpaid')");
        $stmtPay->execute([
            $paymentId,
            $student['id'],
            $studentName,
            $tutorRecord['id'],
            $tutorName,
            $gcashRef,
            $totalPaid
        ]);

        // 3. Connect schedule
        $stSchCheck = $pdo->prepare("SELECT id FROM schedules WHERE tutor_id = ? AND date_slot = ? AND status = 'Available'");
        $stSchCheck->execute([$tutorRecord['id'], "$sessionDate $timeSlot"]);
        $matchingScheduleRow = $stSchCheck->fetch();

        if ($matchingScheduleRow) {
            $stSchUpd = $pdo->prepare("UPDATE schedules SET student_id = ?, student_name = ?, subject = ?, status = 'Booked' WHERE id = ?");
            $stSchUpd->execute([$student['id'], $studentName, $subject, $matchingScheduleRow['id']]);
        } else {
            $schId = newId('SCH-');
            $stSchIns = $pdo->prepare("INSERT INTO schedules (id, tutor_id, tutor_name, student_id, student_name, date_slot, subject, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Booked')");
            $stSchIns->execute([$schId, $tutorRecord['id'], $tutorName, $student['id'], $studentName, "$sessionDate $timeSlot", $subject]);
        }

        // 4. Notifications
        notify($pdo, 'student', $student['id'], 'Demo Payment Confirmed', "Session booked & Demo GCash payment confirmed for $subject on $sessionDate ($timeSlot). Ref: $gcashRef");
        notify($pdo, 'tutor', $tutorRecord['id'], 'New Paid Session Booked', "$studentName booked a paid $subject tutoring session for $sessionDate ($timeSlot).");
        notify($pdo, 'admin', 'ADMIN-001', 'New Session Payment Confirmed', "Demo GCash payment confirmed for $studentName with $tutorName ($sessionDate $timeSlot).");

        $pdo->commit();

        $stNew = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
        $stNew->execute([$sessionId]);
        return $stNew->fetch();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
