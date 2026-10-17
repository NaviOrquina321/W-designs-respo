<?php
// api/reports.php - Admin Monitoring Reports & CSV Export API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$user = requireRole(['admin']);

$startDate = trim($_GET['start_date'] ?? ($_GET['startDate'] ?? ''));
$endDate = trim($_GET['end_date'] ?? ($_GET['endDate'] ?? ''));
$tutorId = trim($_GET['tutor_id'] ?? ($_GET['tutorId'] ?? ''));
$subject = trim($_GET['subject'] ?? '');
$export = trim($_GET['export'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
if ($statusFilter !== '' && !in_array($statusFilter, ['Confirmed', 'In Progress', 'Completed', 'Cancelled'], true)) {
    jsonError(400, 'Invalid status filter.');
}
foreach (['start_date' => $startDate, 'end_date' => $endDate] as $k => $v) {
    if ($v !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
        jsonError(400, "Invalid $k. Use YYYY-MM-DD.");
    }
}

// Build session filter query
$sesWhere = ["1=1"];
$sesParams = [];

if (!empty($startDate)) {
    $sesWhere[] = "session_date >= ?";
    $sesParams[] = $startDate;
}
if (!empty($endDate)) {
    $sesWhere[] = "session_date <= ?";
    $sesParams[] = $endDate;
}
if (!empty($tutorId)) {
    $sesWhere[] = "tutor_id = ?";
    $sesParams[] = $tutorId;
}
if ($statusFilter !== '') {
    $sesWhere[] = "status = ?";
    $sesParams[] = $statusFilter;
}
if (!empty($subject)) {
    $sesWhere[] = "LOWER(subject) = ?";
    $sesParams[] = strtolower($subject);
}

$sesSql = "SELECT * FROM sessions WHERE " . implode(' AND ', $sesWhere) . " ORDER BY session_date DESC";
$stmtSes = $pdo->prepare($sesSql);
$stmtSes->execute($sesParams);
$filteredSessions = $stmtSes->fetchAll();

if ($export === 'sessions_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tutorlink_sessions_export_' . date('Ymd_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM
    fputcsv($output, ['Session ID', 'Student ID', 'Student Name', 'Tutor ID', 'Tutor Name', 'Subject', 'Date', 'Time Slot', 'Hourly Rate', 'Commission Fee', 'Total Paid', 'GCash Ref', 'Status', 'Payout Status']);

    foreach ($filteredSessions as $s) {
        fputcsv($output, [
            sanitizeCsvCell($s['id']),
            sanitizeCsvCell($s['student_id']),
            sanitizeCsvCell($s['student_name']),
            sanitizeCsvCell($s['tutor_id']),
            sanitizeCsvCell($s['tutor_name']),
            sanitizeCsvCell($s['subject']),
            sanitizeCsvCell($s['session_date']),
            sanitizeCsvCell($s['time_slot']),
            sanitizeCsvCell($s['hourly_rate']),
            sanitizeCsvCell($s['commission_fee']),
            sanitizeCsvCell($s['total_paid']),
            sanitizeCsvCell($s['gcash_ref']),
            sanitizeCsvCell($s['status']),
            sanitizeCsvCell($s['payout_status'])
        ]);
    }
    fclose($output);
    exit();
}

if ($export === 'payments_csv') {
    $payWhere = ["1=1"];
    $payParams = [];

    if (!empty($startDate)) {
        $payWhere[] = "created_at >= ?";
        $payParams[] = $startDate . " 00:00:00";
    }
    if (!empty($endDate)) {
        $payWhere[] = "created_at <= ?";
        $payParams[] = $endDate . " 23:59:59";
    }
    if (!empty($tutorId)) {
        $payWhere[] = "tutor_id = ?";
        $payParams[] = $tutorId;
    }

    $stmtPay = $pdo->prepare("SELECT * FROM payments WHERE " . implode(' AND ', $payWhere) . " ORDER BY created_at DESC");
    $stmtPay->execute($payParams);
    $filteredPayments = $stmtPay->fetchAll();

    // Filter by subject if specified
    if (!empty($subject)) {
        $stSubRefs = $pdo->prepare("SELECT DISTINCT gcash_ref FROM sessions WHERE LOWER(subject) = ?");
        $stSubRefs->execute([strtolower($subject)]);
        $allowedRefs = array_column($stSubRefs->fetchAll(), 'gcash_ref');

        $filteredPayments = array_values(array_filter($filteredPayments, function($p) use ($allowedRefs) {
            return in_array($p['ref_no'], $allowedRefs, true);
        }));
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tutorlink_payments_export_' . date('Ymd_His') . '.csv"');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM
    fputcsv($output, ['Payment ID', 'Student ID', 'Student Name', 'Tutor ID', 'Tutor Name', 'Method', 'GCash Ref No', 'Amount', 'Status', 'Payout Status', 'Date']);

    foreach ($filteredPayments as $p) {
        fputcsv($output, [
            sanitizeCsvCell($p['id']),
            sanitizeCsvCell($p['student_id']),
            sanitizeCsvCell($p['student_name']),
            sanitizeCsvCell($p['tutor_id']),
            sanitizeCsvCell($p['tutor_name']),
            sanitizeCsvCell($p['method']),
            sanitizeCsvCell($p['ref_no']),
            sanitizeCsvCell($p['amount']),
            sanitizeCsvCell($p['status']),
            sanitizeCsvCell($p['payout_status']),
            sanitizeCsvCell($p['created_at'])
        ]);
    }
    fclose($output);
    exit();
}

// Return JSON summary
$bookedVolume = 0;
$totalCommission = 0;
$completedCount = 0;
$completedRevenue = 0;

foreach ($filteredSessions as $s) {
    if ($s['status'] !== 'Cancelled') {
        $bookedVolume += intval($s['total_paid']);
        $totalCommission += intval($s['commission_fee']);
    }
    if ($s['status'] === 'Completed') {
        $completedCount++;
        $completedRevenue += intval($s['total_paid']);
    }
}

echo json_encode([
    'status' => 'success',
    'summary' => [
        'booked_volume' => $bookedVolume,
        'completed_revenue' => $completedRevenue,
        'total_revenue' => $bookedVolume,
        'total_commission' => $totalCommission,
        'completed_sessions' => $completedCount,
        'total_sessions_count' => count($filteredSessions)
    ],
    'sessions' => toCamelArray($filteredSessions)
]);
