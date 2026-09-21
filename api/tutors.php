<?php
// api/tutors.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM tutors ORDER BY created_at DESC");
    $tutors = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $tutors]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $action = $input['action'] ?? 'save';

    if ($action === 'verify_credentials') {
        $id = $input['id'] ?? '';
        $doc = $input['doc_type'] ?? 'diploma'; // diploma, tor, id
        $status = $input['status'] ?? 'Verified'; // Verified, Pending, Rejected

        $col = ($doc === 'tor') ? 'tor_status' : (($doc === 'id') ? 'id_status' : 'diploma_status');
        $stmt = $pdo->prepare("UPDATE tutors SET $col = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode(['status' => 'success', 'message' => "Credential $doc updated to $status"]);
        exit();
    }

    if ($action === 'update_approval') {
        $id = $input['id'] ?? '';
        $status = $input['approval_status'] ?? 'Approved'; // Approved, Pending, Rejected

        $stmt = $pdo->prepare("UPDATE tutors SET approval_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode(['status' => 'success', 'message' => "Tutor approval status updated to $status"]);
        exit();
    }

    if ($action === 'toggle_active') {
        $id = $input['id'] ?? '';
        $deactivated = isset($input['deactivated']) ? ($input['deactivated'] ? 1 : 0) : 0;

        $stmt = $pdo->prepare("UPDATE tutors SET deactivated = ? WHERE id = ?");
        $stmt->execute([$deactivated, $id]);

        echo json_encode(['status' => 'success', 'message' => "Tutor active status updated"]);
        exit();
    }

    $id = $input['id'] ?? ('tut-' . rand(1, 99));
    $name = $input['name'] ?? '';
    $initials = $input['initials'] ?? '';
    $rating = $input['rating'] ?? 5.0;
    $reviewsCount = $input['reviews_count'] ?? 0;
    $hourlyRate = $input['hourly_rate'] ?? 350;
    $subjects = $input['subjects'] ?? '';
    $learningStyles = $input['learning_styles'] ?? '';
    $bio = $input['bio'] ?? '';
    $available = isset($input['available']) ? ($input['available'] ? 1 : 0) : 1;
    $deactivated = isset($input['deactivated']) ? ($input['deactivated'] ? 1 : 0) : 0;
    $availableDays = $input['available_days'] ?? 'Mon,Tue,Wed,Thu,Fri';
    $availableTimeSlots = $input['available_time_slots'] ?? '09:00 AM - 05:00 PM';
    $blockedDates = $input['blocked_dates'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO tutors (id, name, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, available_days, available_time_slots, blocked_dates)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE hourly_rate=?, subjects=?, learning_styles=?, bio=?, available=?, deactivated=?, available_days=?, available_time_slots=?, blocked_dates=?");
    $stmt->execute([$id, $name, $initials, $rating, $reviewsCount, $hourlyRate, $subjects, $learningStyles, $bio, $available, $deactivated, $availableDays, $availableTimeSlots, $blockedDates, $hourlyRate, $subjects, $learningStyles, $bio, $available, $deactivated, $availableDays, $availableTimeSlots, $blockedDates]);

    echo json_encode(['status' => 'success', 'message' => 'Tutor profile updated successfully']);
}
?>