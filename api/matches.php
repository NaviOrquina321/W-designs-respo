<?php
// api/matches.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM matches ORDER BY created_at DESC");
    $data = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $data]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('match-' . rand(100, 999));
    $studentId = $input['student_id'] ?? ($input['studentId'] ?? '');
    $studentName = $input['student_name'] ?? ($input['studentName'] ?? '');
    $tutorId = $input['tutor_id'] ?? ($input['tutorId'] ?? '');
    $tutorName = $input['tutor_name'] ?? ($input['tutorName'] ?? '');
    $subject = $input['subject'] ?? '';
    $score = (int)($input['score'] ?? 95);
    $status = $input['status'] ?? 'Pending Review';
    $reason = $input['match_reason'] ?? ($input['reason'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO matches (id, student_id, student_name, tutor_id, tutor_name, subject, score, status, match_reason)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $studentId, $studentName, $tutorId, $tutorName, $subject, $score, $status, $reason]);

    echo json_encode(['status' => 'success', 'message' => 'Match created successfully', 'id' => $id]);
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Match ID is required']);
        exit();
    }

    if (isset($input['tutor_id']) || isset($input['tutorName'])) {
        $tutorId = $input['tutor_id'] ?? ($input['tutorId'] ?? '');
        $tutorName = $input['tutor_name'] ?? ($input['tutorName'] ?? '');
        $stmt = $pdo->prepare("UPDATE matches SET tutor_id = ?, tutor_name = ?, status = 'Reassigned' WHERE id = ?");
        $stmt->execute([$tutorId, $tutorName, $id]);
    }

    if (isset($input['status'])) {
        $stmt = $pdo->prepare("UPDATE matches SET status = ? WHERE id = ?");
        $stmt->execute([$input['status'], $id]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Match updated successfully']);
}
?>