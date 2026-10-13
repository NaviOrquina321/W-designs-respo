<?php
// api/matches.php - Matches REST API
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
        jsonError(400, 'Tutor ID required.');
    }

    // Verify tutor exists, is approved and not deactivated
    $stT = $pdo->prepare("SELECT id, name, subjects, approval_status, deactivated FROM tutors WHERE id = ?");
    $stT->execute([$tutorId]);
    $tutor = $stT->fetch();

    if (!$tutor) {
        jsonError(404, 'Tutor not found.');
    }
    if ($tutor['approval_status'] !== 'Approved' || !empty($tutor['deactivated'])) {
        jsonError(400, 'Selected tutor is not approved or is inactive.');
    }

    $offered = array_map('strtolower', array_map('trim', explode(',', $tutor['subjects'] ?? '')));
    if (!in_array(strtolower($subject), $offered, true)) {
        jsonError(400, "Tutor does not offer the subject '$subject'.");
    }
    $matchReason = mb_substr($matchReason, 0, 300);

    $tutorName = $tutor['name'];
    $studentId = $user['id'];
    $studentName = $user['name'];

    // Reject duplicate pending match for same student + tutor + subject
    $stDup = $pdo->prepare("SELECT COUNT(*) as cnt FROM matches WHERE student_id = ? AND tutor_id = ? AND subject = ? AND status = 'Pending Review'");
    $stDup->execute([$studentId, $tutorId, $subject]);
    if ($stDup->fetch()['cnt'] > 0) {
        jsonError(400, 'You already have a pending match request with this tutor for this subject.');
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

    // Notify tutor and admin
    notify($pdo, 'tutor', $tutorName, 'New Tutor Match Request', "$studentName submitted a match request for $subject.", $tutorId);
    notify($pdo, 'admin', 'System Admin', 'New Match Request Submitted', "$studentName requested match with $tutorName for $subject.");

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
        jsonError(400, 'Match ID required.');
    }

    $st = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
    $st->execute([$matchId]);
    $match = $st->fetch();

    if (!$match) {
        jsonError(404, 'Match record not found.');
    }

    if ($user['role'] === 'tutor') {
        if ($match['tutor_id'] !== $user['id']) {
            jsonError(403, 'You can only update match requests assigned to you.');
        }
    }

    $fields = [];
    $params = [];

    if (isset($input['status'])) {
        $statusVal = trim($input['status']);
        $validStatuses = ['Approved', 'Cancelled', 'Pending Review', 'Reassigned'];
        if (!in_array($statusVal, $validStatuses, true)) {
            jsonError(400, 'Invalid match status.');
        }
        $fields[] = "`status` = ?";
        $params[] = $statusVal;

        if ($statusVal === 'Approved') {
            notify($pdo, 'student', $match['student_name'], 'Match Request Approved', "{$match['tutor_name']} accepted your match request for {$match['subject']}.", $match['student_id']);
        } elseif ($statusVal === 'Cancelled') {
            notify($pdo, 'student', $match['student_name'], 'Match Request Cancelled', "Match request for {$match['subject']} with {$match['tutor_name']} was cancelled.", $match['student_id']);
        }
    }

    if (isset($input['score'])) {
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
        }
    }

    if (empty($fields)) {
        jsonError(400, 'No valid fields provided for update.');
    }

    $params[] = $matchId;
    $sql = "UPDATE matches SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['status' => 'success', 'message' => 'Match request updated.']);
} else {
    jsonError(405, 'Method not allowed');
}
?>