<?php
// api/tutors.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM tutors ORDER BY created_at DESC");
    $tutors = $stmt->fetchAll();

    // Normalize data fields for JSON
    $formatted = array_map(function($t) {
        $t['rating'] = (float)$t['rating'];
        $t['reviewsCount'] = (int)$t['reviews_count'];
        $t['hourlyRate'] = (int)$t['hourly_rate'];
        $t['subjects'] = array_map('trim', explode(',', $t['subjects']));
        $t['learningStyles'] = array_map('trim', explode(',', $t['learning_styles']));
        $t['availableDays'] = array_map('trim', explode(',', $t['available_days'] ?? 'Mon,Tue,Wed,Thu,Fri'));
        $t['availableTimeSlots'] = $t['available_time_slots'] ?? '09:00 AM - 05:00 PM';
        $t['blockedDates'] = $t['blocked_dates'] ?? '';
        $t['availabilitySlots'] = !empty($t['availability_slots']) ? array_map('trim', explode(',', $t['availability_slots'])) : ['09:00 AM', '02:00 PM', '04:00 PM'];
        $t['diplomaStatus'] = $t['diploma_status'] ?? 'Pending';
        $t['torStatus'] = $t['tor_status'] ?? 'Pending';
        $t['idStatus'] = $t['id_status'] ?? 'Pending';
        $t['approvalStatus'] = $t['approval_status'] ?? 'Pending Review';
        $t['deactivated'] = (bool)$t['deactivated'];
        $t['available'] = (bool)$t['available'];
        return $t;
    }, $tutors);

    echo json_encode(['status' => 'success', 'data' => $formatted]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $id = $input['id'] ?? ('tut-' . rand(100, 999));
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $initials = $input['initials'] ?? 'TR';
    $rating = (float)($input['rating'] ?? 0.0);
    $reviewsCount = (int)($input['reviewsCount'] ?? ($input['reviews_count'] ?? 0));
    $hourlyRate = (int)($input['hourlyRate'] ?? ($input['hourly_rate'] ?? 350));
    $subjects = is_array($input['subjects'] ?? null) ? implode(', ', $input['subjects']) : ($input['subjects'] ?? '');
    $learningStyles = is_array($input['learningStyles'] ?? null) ? implode(', ', $input['learningStyles']) : ($input['learningStyles'] ?? 'Step-by-Step Explanation');
    $bio = $input['bio'] ?? '';
    $diplomaStatus = $input['diplomaStatus'] ?? ($input['diploma_status'] ?? 'Pending');
    $torStatus = $input['torStatus'] ?? ($input['tor_status'] ?? 'Pending');
    $idStatus = $input['idStatus'] ?? ($input['id_status'] ?? 'Pending');
    $approvalStatus = $input['approvalStatus'] ?? ($input['approval_status'] ?? 'Pending Review');

    $stmt = $pdo->prepare("INSERT INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, diploma_status, tor_status, id_status, approval_status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE name=?, rating=?, reviews_count=?, hourly_rate=?, subjects=?, learning_styles=?, bio=?, diploma_status=?, tor_status=?, id_status=?, approval_status=?");
    $stmt->execute([
        $id, $name, $email, $initials, $rating, $reviewsCount, $hourlyRate, $subjects, $learningStyles, $bio, $diplomaStatus, $torStatus, $idStatus, $approvalStatus,
        $name, $rating, $reviewsCount, $hourlyRate, $subjects, $learningStyles, $bio, $diplomaStatus, $torStatus, $idStatus, $approvalStatus
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Tutor saved successfully', 'id' => $id]);
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tutor ID is required']);
        exit();
    }

    if (isset($input['rating'])) {
        $stmt = $pdo->prepare("UPDATE tutors SET rating = ?, reviews_count = ? WHERE id = ? OR name = ?");
        $stmt->execute([(float)$input['rating'], (int)($input['reviewsCount'] ?? 1), $id, $id]);
    }

    if (isset($input['approval_status']) || isset($input['approvalStatus'])) {
        $status = $input['approval_status'] ?? $input['approvalStatus'];
        $stmt = $pdo->prepare("UPDATE tutors SET approval_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    if (isset($input['deactivated'])) {
        $stmt = $pdo->prepare("UPDATE tutors SET deactivated = ? WHERE id = ?");
        $stmt->execute([$input['deactivated'] ? 1 : 0, $id]);
    }

    if (isset($input['diplomaStatus']) || isset($input['diploma_status'])) {
        $status = $input['diplomaStatus'] ?? $input['diploma_status'];
        $stmt = $pdo->prepare("UPDATE tutors SET diploma_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    if (isset($input['torStatus']) || isset($input['tor_status'])) {
        $status = $input['torStatus'] ?? $input['tor_status'];
        $stmt = $pdo->prepare("UPDATE tutors SET tor_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    if (isset($input['idStatus']) || isset($input['id_status'])) {
        $status = $input['idStatus'] ?? $input['id_status'];
        $stmt = $pdo->prepare("UPDATE tutors SET id_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    if (isset($input['bio']) || isset($input['hourlyRate']) || isset($input['subjects'])) {
        $subjects = is_array($input['subjects'] ?? null) ? implode(', ', $input['subjects']) : ($input['subjects'] ?? '');
        $learningStyles = is_array($input['learningStyles'] ?? null) ? implode(', ', $input['learningStyles']) : ($input['learningStyles'] ?? '');
        $stmt = $pdo->prepare("UPDATE tutors SET bio = ?, hourly_rate = ?, subjects = ?, learning_styles = ? WHERE id = ?");
        $stmt->execute([
            $input['bio'] ?? '',
            (int)($input['hourlyRate'] ?? $input['hourly_rate'] ?? 350),
            $subjects,
            $learningStyles,
            $id
        ]);
    }

    if (isset($input['availableDays']) || isset($input['available_days'])) {
        $days = is_array($input['availableDays'] ?? null) ? implode(', ', $input['availableDays']) : ($input['available_days'] ?? '');
        $timeSlots = $input['availableTimeSlots'] ?? ($input['available_time_slots'] ?? '');
        $blockedDates = $input['blockedDates'] ?? ($input['blocked_dates'] ?? '');
        $stmt = $pdo->prepare("UPDATE tutors SET available_days = ?, available_time_slots = ?, blocked_dates = ? WHERE id = ?");
        $stmt->execute([$days, $timeSlots, $blockedDates, $id]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Tutor profile updated successfully']);
}
?>