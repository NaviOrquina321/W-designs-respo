<?php
// api/tutors.php - Tutors REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = isset($_SESSION['role']) ? ['id' => $_SESSION['user_id'] ?? '', 'role' => $_SESSION['role']] : null;

    if ($user && $user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, diploma_status, tor_status, id_status, approval_status, available_days, available_time_slots, blocked_dates, availability_slots, created_at FROM tutors ORDER BY created_at DESC");
        $rawTutors = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, diploma_status, tor_status, id_status, approval_status, available_days, available_time_slots, blocked_dates, availability_slots, created_at FROM tutors WHERE (approval_status = 'Approved' AND deactivated = 0) OR id = ?");
        $stmt->execute([$user['id'] ?? '']);
        $rawTutors = $stmt->fetchAll();
    }

    // Strip sensitive fields (email, credential statuses) for other tutors
    $tutors = [];
    foreach ($rawTutors as $t) {
        $isOwnerOrAdmin = ($user && ($user['role'] === 'admin' || $user['id'] === $t['id']));
        if (!$isOwnerOrAdmin) {
            unset($t['email'], $t['diploma_status'], $t['tor_status'], $t['id_status']);
        }
        $tutors[] = $t;
    }

    echo json_encode(['status' => 'success', 'data' => toCamelArray($tutors)]);
} elseif ($method === 'PUT') {
    $user = requireLogin();
    $input = getInput();
    $tutorId = trim($input['id'] ?? '');

    if (empty($tutorId)) {
        jsonError(400, 'Tutor ID required.');
    }

    if ($user['role'] === 'admin') {
        $fields = [];
        $params = [];

        if (isset($input['approval_status']) || isset($input['approvalStatus'])) {
            $fields[] = "`approval_status` = ?";
            $params[] = trim($input['approval_status'] ?? $input['approvalStatus']);
        }
        if (isset($input['deactivated'])) {
            $fields[] = "`deactivated` = ?";
            $params[] = $input['deactivated'] ? 1 : 0;
        }
        if (isset($input['diploma_status'])) {
            $fields[] = "`diploma_status` = ?";
            $params[] = trim($input['diploma_status']);
        }
        if (isset($input['tor_status'])) {
            $fields[] = "`tor_status` = ?";
            $params[] = trim($input['tor_status']);
        }
        if (isset($input['id_status'])) {
            $fields[] = "`id_status` = ?";
            $params[] = trim($input['id_status']);
        }

        if (empty($fields)) {
            jsonError(400, 'No valid fields provided for update.');
        }

        $params[] = $tutorId;
        $sql = "UPDATE tutors SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Tutor updated by admin.']);
    } elseif ($user['role'] === 'tutor') {
        if ($user['id'] !== $tutorId) {
            jsonError(403, 'You can only update your own tutor profile.');
        }

        $fields = [];
        $params = [];

        if (isset($input['name'])) {
            $fields[] = "`name` = ?";
            $params[] = trim($input['name']);
            $_SESSION['name'] = trim($input['name']);
        }
        if (isset($input['hourlyRate']) || isset($input['hourly_rate'])) {
            $rate = intval($input['hourlyRate'] ?? $input['hourly_rate']);
            if ($rate > 0) {
                $fields[] = "`hourly_rate` = ?";
                $params[] = $rate;
            }
        }
        if (isset($input['subjects'])) {
            $subj = is_array($input['subjects']) ? implode(', ', $input['subjects']) : trim($input['subjects']);
            $fields[] = "`subjects` = ?";
            $params[] = $subj;
        }
        if (isset($input['learningStyles']) || isset($input['learning_styles'])) {
            $styles = $input['learningStyles'] ?? $input['learning_styles'];
            $styleVal = is_array($styles) ? implode(', ', $styles) : trim($styles);
            $fields[] = "`learning_styles` = ?";
            $params[] = $styleVal;
        }
        if (isset($input['bio'])) {
            $fields[] = "`bio` = ?";
            $params[] = trim($input['bio']);
        }
        if (isset($input['available'])) {
            $fields[] = "`available` = ?";
            $params[] = $input['available'] ? 1 : 0;
        }
        if (isset($input['availableDays']) || isset($input['available_days'])) {
            $days = $input['availableDays'] ?? $input['available_days'];
            $daysVal = is_array($days) ? implode(',', $days) : trim($days);
            $fields[] = "`available_days` = ?";
            $params[] = $daysVal;
        }
        if (isset($input['availableTimeSlots']) || isset($input['available_time_slots'])) {
            $slotsVal = trim($input['availableTimeSlots'] ?? $input['available_time_slots']);
            $fields[] = "`available_time_slots` = ?";
            $params[] = $slotsVal;
        }
        if (isset($input['blockedDates']) || isset($input['blocked_dates'])) {
            $blockedVal = trim($input['blockedDates'] ?? $input['blocked_dates']);
            $fields[] = "`blocked_dates` = ?";
            $params[] = $blockedVal;
        }

        if (empty($fields)) {
            jsonError(400, 'No valid fields provided for update.');
        }

        $params[] = $tutorId;
        $sql = "UPDATE tutors SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Tutor profile updated successfully.']);
    } else {
        jsonError(403, 'Forbidden action.');
    }
} else {
    jsonError(405, 'Method not allowed');
}
?>