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
    } elseif ($user && $user['role'] === 'tutor') {
        $stmt = $pdo->prepare("SELECT id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, diploma_status, tor_status, id_status, approval_status, available_days, available_time_slots, blocked_dates, availability_slots, created_at FROM tutors WHERE (approval_status = 'Approved' AND deactivated = 0) OR id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $rawTutors = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, deactivated, diploma_status, tor_status, id_status, approval_status, available_days, available_time_slots, blocked_dates, availability_slots, created_at FROM tutors WHERE approval_status = 'Approved' AND deactivated = 0 ORDER BY created_at DESC");
        $rawTutors = $stmt->fetchAll();
    }

    $tutors = [];
    foreach ($rawTutors as $t) {
        $isOwnerOrAdmin = ($user && ($user['role'] === 'admin' || $user['id'] === $t['id']));
        if (!$isOwnerOrAdmin) {
            unset($t['email'], $t['diploma_status'], $t['tor_status'], $t['id_status']);
        }

        $camel = toCamelArray($t);

        // Parse array fields
        $camel['subjects'] = !empty($t['subjects']) ? array_values(array_filter(array_map('trim', explode(',', $t['subjects'])))) : [];
        $camel['learningStyles'] = !empty($t['learning_styles']) ? array_values(array_filter(array_map('trim', explode(',', $t['learning_styles'])))) : [];
        $camel['availableDays'] = !empty($t['available_days']) ? array_values(array_filter(array_map('trim', explode(',', $t['available_days'])))) : [];
        $camel['availabilitySlots'] = !empty($t['availability_slots']) ? array_values(array_filter(array_map('trim', explode(',', $t['availability_slots'])))) : [];

        // Types
        $camel['hourlyRate'] = (int)($t['hourly_rate'] ?? 0);
        $camel['reviewsCount'] = (int)($t['reviews_count'] ?? 0);
        $camel['rating'] = (float)($t['rating'] ?? 0.0);
        $camel['available'] = (bool)(int)($t['available'] ?? 0);
        $camel['deactivated'] = (bool)(int)($t['deactivated'] ?? 0);

        $tutors[] = $camel;
    }

    echo json_encode(['status' => 'success', 'data' => $tutors]);
} elseif ($method === 'PUT') {
    $user = requireLogin();
    $input = getInput();
    $tutorId = trim($input['id'] ?? '');

    if (empty($tutorId)) {
        jsonError(400, 'Tutor ID required.');
    }

    $stmtFetch = $pdo->prepare("SELECT * FROM tutors WHERE id = ?");
    $stmtFetch->execute([$tutorId]);
    $existing = $stmtFetch->fetch();
    if (!$existing) {
        jsonError(404, 'Tutor not found.');
    }

    if ($user['role'] === 'admin') {
        $fields = [];
        $params = [];

        $newDiploma = isset($input['diploma_status']) ? trim($input['diploma_status']) : $existing['diploma_status'];
        $newTor = isset($input['tor_status']) ? trim($input['tor_status']) : $existing['tor_status'];
        $newId = isset($input['id_status']) ? trim($input['id_status']) : $existing['id_status'];
        $validDocs = ['Verified', 'Pending', 'Rejected'];

        if (isset($input['diploma_status'])) {
            if (!in_array($newDiploma, $validDocs, true)) jsonError(400, 'Invalid diploma_status.');
            $fields[] = "`diploma_status` = ?";
            $params[] = $newDiploma;
        }
        if (isset($input['tor_status'])) {
            if (!in_array($newTor, $validDocs, true)) jsonError(400, 'Invalid tor_status.');
            $fields[] = "`tor_status` = ?";
            $params[] = $newTor;
        }
        if (isset($input['id_status'])) {
            if (!in_array($newId, $validDocs, true)) jsonError(400, 'Invalid id_status.');
            $fields[] = "`id_status` = ?";
            $params[] = $newId;
        }

        if (isset($input['approval_status']) || isset($input['approvalStatus'])) {
            $appStatus = trim($input['approval_status'] ?? $input['approvalStatus']);
            $validApp = ['Approved', 'Pending Review', 'Declined'];
            if (!in_array($appStatus, $validApp, true)) {
                jsonError(400, 'Invalid approval_status.');
            }
            if ($appStatus === 'Approved') {
                if ($newDiploma !== 'Verified' || $newTor !== 'Verified' || $newId !== 'Verified') {
                    jsonError(400, 'Cannot approve tutor: Diploma, TOR, and ID status must all be Verified.');
                }
            }
            $fields[] = "`approval_status` = ?";
            $params[] = $appStatus;

            if ($appStatus !== $existing['approval_status']) {
                notify($pdo, 'tutor', $existing['name'], 'Tutor Application Status Update', "Your application status has been updated to $appStatus.", $existing['id']);
            }
        }

        if (isset($input['deactivated'])) {
            $fields[] = "`deactivated` = ?";
            $params[] = $input['deactivated'] ? 1 : 0;
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
            $newName = trim($input['name']);
            if (empty($newName)) jsonError(400, 'Name cannot be empty.');
            $fields[] = "`name` = ?";
            $params[] = $newName;
            $_SESSION['name'] = $newName;

            $stmtU = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmtU->execute([$newName, $tutorId]);
        }

        if (isset($input['hourlyRate']) || isset($input['hourly_rate'])) {
            $rate = intval($input['hourlyRate'] ?? $input['hourly_rate']);
            if ($rate < 1 || $rate > 10000) {
                jsonError(400, 'Hourly rate must be between 1 and 10000.');
            }
            $fields[] = "`hourly_rate` = ?";
            $params[] = $rate;
        }

        if (isset($input['subjects'])) {
            $subj = is_array($input['subjects']) ? implode(', ', $input['subjects']) : trim($input['subjects']);
            $chosenList = array_filter(array_map('trim', explode(',', $subj)));

            $stCat = $pdo->query("SELECT name FROM subjects");
            $catList = array_map('strtolower', array_column($stCat->fetchAll(), 'name'));

            foreach ($chosenList as $cs) {
                if (!in_array(strtolower($cs), $catList, true)) {
                    jsonError(400, "Subject '$cs' is not in the subjects catalog.");
                }
            }

            $fields[] = "`subjects` = ?";
            $params[] = implode(', ', $chosenList);
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
        if (isset($input['availabilitySlots']) || isset($input['availability_slots'])) {
            $slots = $input['availabilitySlots'] ?? $input['availability_slots'];
            $slotsVal = is_array($slots) ? implode(', ', $slots) : trim($slots);
            $fields[] = "`availability_slots` = ?";
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