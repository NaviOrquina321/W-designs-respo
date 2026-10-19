<?php
// api/matches.php - Matches REST API with Admin Audit & FDD Validation
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireLogin();
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT * FROM matches ORDER BY created_at DESC");
        $matches = $stmt->fetchAll();
    } elseif ($user['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT * FROM matches WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $matches = $stmt->fetchAll();
    } elseif ($user['role'] === 'tutor') {
        $stmt = $pdo->prepare("SELECT * FROM matches WHERE tutor_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $matches = $stmt->fetchAll();
    } else {
        jsonError(403, 'Forbidden');
    }
    echo json_encode(['status' => 'success', 'data' => toCamelArray($matches)]);
} elseif ($method === 'POST') {
    $user = requireRole(['student']);
    $input = getInput();

    $tutorId = trim($input['tutor_id'] ?? ($input['tutorId'] ?? ''));
    $subject = trim($input['subject'] ?? 'Mathematics');
    $score = max(0, min(100, intval($input['score'] ?? 80)));
    $matchReason = trim($input['match_reason'] ?? ($input['matchReason'] ?? 'Rule-Based Compatibility Match'));

    if (empty($tutorId)) {
        jsonError(422, 'Tutor ID required.');
    }

    $stT = $pdo->prepare("SELECT id, name, deactivated FROM tutors WHERE id = ?");
    $stT->execute([$tutorId]);
    $tutor = $stT->fetch();

    if (!$tutor) {
        jsonError(404, 'Tutor not found.');
    }
    if (!empty($tutor['deactivated'])) {
        jsonError(422, 'Selected tutor is inactive.');
    }

    $tutorName = $tutor['name'];
    $studentId = $user['id'];
    $studentName = $user['name'];

    $stDup = $pdo->prepare("SELECT COUNT(*) as cnt FROM matches WHERE student_id = ? AND tutor_id = ? AND subject = ? AND status = 'Pending Review'");
    $stDup->execute([$studentId, $tutorId, $subject]);
    if ($stDup->fetch()['cnt'] > 0) {
        jsonError(422, 'You already have a pending match request with this tutor for this subject.');
    }

    $matchId = newId('MATCH-');

    $stmt = $pdo->prepare("INSERT INTO matches (id, student_id, student_name, tutor_id, tutor_name, subject, score, status, match_reason) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending Review', ?)");
    $stmt->execute([
        $matchId,
        $studentId,
        $studentName,
        $tutorId,
        $tutorName,
        $subject,
        $score,
        $matchReason
    ]);

    notify($pdo, 'tutor', $tutorId, 'New Tutor Match Request', "$studentName submitted a match request for $subject.");
    notify($pdo, 'admin', 'ADMIN-001', 'New Match Request Submitted', "$studentName requested match with $tutorName for $subject.");

    $stGet = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
    $stGet->execute([$matchId]);
    $row = $stGet->fetch();

    echo json_encode([
        'status' => 'success',
        'message' => 'Match request generated.',
        'data' => toCamelArray($row)
    ]);
} elseif ($method === 'PUT') {
    $user = requireRole(['admin', 'tutor']);
    $input = getInput();
    $matchId = trim($input['id'] ?? '');

    if (empty($matchId)) {
        jsonError(422, 'Match ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
    $st->execute([$matchId]);
    $match = $st->fetch();

    if (!$match) {
        jsonError(404, 'Match record not found.');
    }

    if ($user['role'] === 'tutor' && $match['tutor_id'] !== $user['id']) {
        jsonError(403, 'You can only update match requests assigned to you.');
    }

    $fields = [];
    $params = [];
    $newStatus = null;
    $cancelReason = trim($input['reason'] ?? ($input['cancel_reason'] ?? ''));

    if (isset($input['status'])) {
        $statusVal = trim($input['status']);
        $validStatuses = ['Approved', 'Cancelled', 'Pending Review', 'Reassigned'];
        if (!in_array($statusVal, $validStatuses, true)) {
            jsonError(422, 'Invalid match status.');
        }

        if ($statusVal === 'Cancelled' && empty($cancelReason)) {
            jsonError(422, 'Cancellation reason is required.');
        }

        $fields[] = "`status` = ?";
        $params[] = $statusVal;
        $newStatus = $statusVal;
    }

    if (isset($input['score']) && $user['role'] === 'admin') {
        $score = max(0, min(100, intval($input['score'])));
        $fields[] = "`score` = ?";
        $params[] = $score;
    }

    if (isset($input['tutor_id']) && $user['role'] === 'admin') {
        $newTutId = trim($input['tutor_id']);
        $stTut = $pdo->prepare("SELECT name FROM tutors WHERE id = ?");
        $stTut->execute([$newTutId]);
        $tut = $stTut->fetch();
        if ($tut) {
            $fields[] = "`tutor_id` = ?";
            $params[] = $newTutId;
            $fields[] = "`tutor_name` = ?";
            $params[] = $tut['name'];
        } else {
            jsonError(404, 'New tutor not found.');
        }
    }

    if (empty($fields)) {
        jsonError(422, 'No valid fields provided for update.');
    }

    $params[] = $matchId;
    $sql = "UPDATE matches SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($user['role'] === 'admin') {
        if ($newStatus === 'Approved') {
            logAdminAction('Approve Matching Sessions', $matchId, ['status' => $match['status']], ['status' => 'Approved']);
            notify($pdo, 'student', $match['student_id'], 'Match Approved', "Your match request for {$match['subject']} with {$match['tutor_name']} was approved by Admin.");
            notify($pdo, 'tutor', $match['tutor_id'], 'Match Approved', "Match request with {$match['student_name']} for {$match['subject']} was approved by Admin.");
        } elseif ($newStatus === 'Cancelled') {
            logAdminAction('Cancel Matching Sessions', $matchId, ['status' => $match['status']], ['status' => 'Cancelled', 'reason' => $cancelReason]);
            notify($pdo, 'student', $match['student_id'], 'Match Cancelled', "Match request for {$match['subject']} with {$match['tutor_name']} was cancelled. Reason: $cancelReason");
            notify($pdo, 'tutor', $match['tutor_id'], 'Match Cancelled', "Match request for {$match['subject']} with {$match['student_name']} was cancelled. Reason: $cancelReason");
        } else {
            logAdminAction('Review Matching Results', $matchId, ['status' => $match['status']], ['status' => $newStatus ?? $match['status']]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Match request updated successfully.']);
} else {
    jsonError(405, 'Method not allowed');
}
