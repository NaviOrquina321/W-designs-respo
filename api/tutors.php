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
        $doc = $input['doc_type'] ?? 'diploma';
        $status = $input['status'] ?? 'Verified';

        $col = ($doc === 'tor') ? 'tor_status' : (($doc === 'id') ? 'id_status' : 'diploma_status');
        $stmt = $pdo->prepare("UPDATE tutors SET $col = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode(['status' => 'success', 'message' => "Credential $doc updated to $status"]);
        exit();
    }

    if ($action === 'update_approval') {
        $id = $input['id'] ?? '';
        $status = $input['approval_status'] ?? 'Approved';

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

    $id = $input['id'] ?? ('tut-' . rand(10, 99));
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
    $isNew = isset($input['is_new_registration']) && $input['is_new_registration'];

    $stmt = $pdo->prepare("INSERT INTO tutors (id, name, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, available_days, available_time_slots, blocked_dates)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON CONFLICT(id) DO UPDATE SET hourly_rate=excluded.hourly_rate, subjects=excluded.subjects, learning_styles=excluded.learning_styles, bio=excluded.bio, available=excluded.available, deactivated=excluded.deactivated, available_days=excluded.available_days, available_time_slots=excluded.available_time_slots, blocked_dates=excluded.blocked_dates");
    $stmt->execute([$id, $name, $initials, $rating, $reviewsCount, $hourlyRate, $subjects, $learningStyles, $bio, $available, $deactivated, $availableDays, $availableTimeSlots, $blockedDates]);

    if ($isNew) {
        $notifId = 'notif-' . time() . '-' . rand(100, 999);
        $notifStmt = $pdo->prepare("INSERT INTO notifications (id, target, title, message) VALUES (?, 'admin', 'New Tutor Registered', ?)");
        $notifStmt->execute([$notifId, "New tutor registered: $name ($subjects)"]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Tutor profile saved successfully', 'id' => $id]);
}
?>