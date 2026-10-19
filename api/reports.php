<?php
// api/reports.php - Reports REST API with FDD Presets & Server-Side Pagination (NO CSV)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$user = requireRole(['admin']);

// Reject any CSV export request
if (isset($_GET['export']) || isset($_GET['format'])) {
    $exp = strtolower($_GET['export'] ?? $_GET['format'] ?? '');
    if (strpos($exp, 'csv') !== false) {
        jsonError(422, 'CSV export feature has been removed. Use Print Report instead.');
    }
}

$preset = trim($_GET['preset'] ?? 'completed'); // 'completed', 'weekly', 'monthly'
$startDate = trim($_GET['start_date'] ?? ($_GET['startDate'] ?? ''));
$endDate = trim($_GET['end_date'] ?? ($_GET['endDate'] ?? ''));
$status = trim($_GET['status'] ?? '');
$tutorId = trim($_GET['tutor_id'] ?? ($_GET['tutorId'] ?? ''));
$searchTerm = strtolower(trim($_GET['search'] ?? ''));
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = max(1, min(100, intval($_GET['per_page'] ?? ($_GET['perPage'] ?? 25))));

// Validate inputs
$validPresets = ['completed', 'weekly', 'monthly', 'custom', 'View Completed Tutoring', 'View Weekly Sessions', 'View Monthly Sessions'];
if (!in_array($preset, $validPresets, true)) {
    jsonError(422, 'Invalid preset filter requested.');
}

if (!empty($startDate)) {
    $d1 = DateTime::createFromFormat('Y-m-d', $startDate);
    if (!$d1 || $d1->format('Y-m-d') !== $startDate) {
        jsonError(422, 'Invalid start_date format. Use Y-m-d.');
    }
}
if (!empty($endDate)) {
    $d2 = DateTime::createFromFormat('Y-m-d', $endDate);
    if (!$d2 || $d2->format('Y-m-d') !== $endDate) {
        jsonError(422, 'Invalid end_date format. Use Y-m-d.');
    }
}

// Preset date ranges in Asia/Manila timezone
$todayTs = time();
if ($preset === 'weekly' || $preset === 'View Weekly Sessions') {
    // Current week Monday to Sunday
    $monday = date('Y-m-d', strtotime('monday this week', $todayTs));
    $sunday = date('Y-m-d', strtotime('sunday this week', $todayTs));
    if (empty($startDate)) $startDate = $monday;
    if (empty($endDate)) $endDate = $sunday;
} elseif ($preset === 'monthly' || $preset === 'View Monthly Sessions') {
    // Current calendar month
    $firstDay = date('Y-m-01', $todayTs);
    $lastDay = date('Y-m-t', $todayTs);
    if (empty($startDate)) $startDate = $firstDay;
    if (empty($endDate)) $endDate = $lastDay;
} elseif ($preset === 'completed' || $preset === 'View Completed Tutoring') {
    if (empty($status)) {
        $status = 'Completed';
    }
}

$where = ["1=1"];
$params = [];

if (!empty($startDate)) {
    $where[] = "session_date >= ?";
    $params[] = $startDate;
}
if (!empty($endDate)) {
    $where[] = "session_date <= ?";
    $params[] = $endDate;
}
if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
}
if (!empty($tutorId)) {
    $where[] = "tutor_id = ?";
    $params[] = $tutorId;
}

$sql = "SELECT * FROM sessions WHERE " . implode(' AND ', $where) . " ORDER BY session_date DESC, created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allMatched = $stmt->fetchAll();

// Apply client search filter if provided
if (!empty($searchTerm)) {
    $allMatched = array_values(array_filter($allMatched, function($s) use ($searchTerm) {
        return strpos(strtolower($s['student_name'] ?? ''), $searchTerm) !== false
            || strpos(strtolower($s['tutor_name'] ?? ''), $searchTerm) !== false
            || strpos(strtolower($s['subject'] ?? ''), $searchTerm) !== false
            || strpos(strtolower($s['id'] ?? ''), $searchTerm) !== false;
    }));
}

// Calculate Report Summary Totals (Cancelled sessions excluded from money totals)
$totalSessionsCount = count($allMatched);
$completedSessionsCount = 0;
$cancelledSessionsCount = 0;
$totalPaidVolume = 0;
$totalCommissionFee = 0;

foreach ($allMatched as $s) {
    if ($s['status'] === 'Completed') {
        $completedSessionsCount++;
    } elseif ($s['status'] === 'Cancelled') {
        $cancelledSessionsCount++;
    }

    if ($s['status'] !== 'Cancelled') {
        $totalPaidVolume += floatval($s['total_paid'] ?? 0);
        $totalCommissionFee += floatval($s['commission_fee'] ?? 0);
    }
}

// Server-side pagination (25/page)
$totalRows = count($allMatched);
$totalPages = max(1, ceil($totalRows / $perPage));
$offset = ($page - 1) * $perPage;
$paginatedSessions = array_slice($allMatched, $offset, $perPage);

echo json_encode([
    'status' => 'success',
    'preset' => $preset,
    'summary' => [
        'total_sessions' => $totalSessionsCount,
        'completed_sessions' => $completedSessionsCount,
        'cancelled_sessions' => $cancelledSessionsCount,
        'total_paid' => $totalPaidVolume,
        'total_commission' => $totalCommissionFee
    ],
    'pagination' => [
        'total_records' => $totalRows,
        'current_page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages
    ],
    'sessions' => toCamelArray($paginatedSessions)
]);
