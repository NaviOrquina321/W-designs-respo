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
        $stmt = $pdo->prepare("SELECT * FROM matches WHERE student_id = ? OR LOWER(student_name) = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id'], strtolower($user['name'])]);
        $matches = $stmt->fetchAll();
    } elseif ($user['role'] === 'tutor') {
        $stmt = $pdo->prepare("SELECT * FROM matches WHERE tutor_id = ? OR LOWER(tutor_name) = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id'], strtolower($user['name'])]);
        $matches = $stmt->fetchAll();
    } else {
        jsonError(403, 'Forbidden');
    }
    echo json_encode(['status' => 'success', 'data' => toCamelArray($matches)]);
} elseif ($method === 'POST') {
    $user = requireRole(['student']);
    $input = getInput();

    $tutorId = trim($input['tutor_id'] ?? ($input['tutorId'] ?? ''));
    $tutorName = trim($input['tutor_name'] ?? ($input['tutorName'] ?? 'Tutor'));
    $subject = trim($input['subject'] ?? 'Mathematics');
    $score = max(0, min(100, intval($input['score'] ?? 80)));
    $matchReason = trim($input['match_reason'] ?? ($input['matchReason'] ?? 'AI Compatibility Match'));

    $studentId = $user['id'];
    $studentName = $user['name'];

    $matchId = newId('MATCH-');

    $stmt = $pdo->prepare("INSERT INTO matches (id, student_id, student_name, tutor_id, tutor_name, subject, score, status, match_reason) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending Review', ?)");
    $stmt->execute([
        $matchId,
        !empty($studentId) ? $studentId : null,
        $studentName,
        !empty($tutorId) ? $tutorId : null,
        $tutorName,
        $subject,
        $score,
        $matchReason
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Match request generated.',
        'data' => [
            'id' => $matchId,
            'student_id' => $studentId,
            'student_name' => $studentName,
            'tutor_id' => $tutorId,
            'tutor_name' => $tutorName,
            'subject' => $subject,
            'score' => $score,
            'status' => 'Pending Review'
        ]
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
        if ($match['tutor_id'] !== $user['id'] && strtolower($match['tutor_name']) !== strtolower($user['name'])) {
            jsonError(403, 'You can only update match requests assigned to you.');
        }
    }

    $fields = [];
    $params = [];

    if (isset($input['status'])) {
        $statusVal = trim($input['status']);
        if (in_array($statusVal, ['Approved', 'Cancelled', 'Pending Review'], true)) {
            $fields[] = "`status` = ?";
            $params[] = $statusVal;
        }
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