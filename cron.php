<?php
// cron.php - Automated Reminders & Background Task Runner
if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/api/config.php';
    require_once __DIR__ . '/api/helpers.php';
    $user = requireRole(['admin']);
} else {
    define('TEST_MODE', 1);
    require_once __DIR__ . '/api/config.php';
    require_once __DIR__ . '/api/helpers.php';
}

$now = time();
$processed = 0;
$remindersSent = 0;

try {
    $stmt = $pdo->query("SELECT * FROM sessions WHERE status IN ('Confirmed', 'In Progress')");
    $sessions = $stmt->fetchAll();

    foreach ($sessions as $ses) {
        $startTs = parseSlotStartTime($ses['session_date'], $ses['time_slot']);
        $diff = $startTs - $now;
        $processed++;

        if ($startTs > 0 && $diff > 0 && $diff <= 86400) {
            $recipients = [
                ['id' => $ses['student_id'], 'role' => 'student', 'name' => $ses['student_name'], 'other' => $ses['tutor_name']],
                ['id' => $ses['tutor_id'], 'role' => 'tutor', 'name' => $ses['tutor_name'], 'other' => $ses['student_name']]
            ];

            foreach ($recipients as $rec) {
                if ($diff <= 3600) {
                    $remType = 'session_reminder_1h';
                    $remTitle = 'Session Starting Soon (1 Hour)';
                    $remMsg = "Urgent: Your {$ses['subject']} session with {$rec['other']} starts in less than an hour at {$ses['time_slot']}.";
                } else {
                    $remType = 'session_reminder_24h';
                    $remTitle = 'Upcoming Session Reminder (24 Hours)';
                    $remMsg = "Reminder: Your {$ses['subject']} session with {$rec['other']} is scheduled for tomorrow on {$ses['session_date']} at {$ses['time_slot']}.";
                }

                $dedupeKey = "rem-{$ses['id']}-{$rec['role']}-$remType";
                $sent = notify($pdo, $rec['role'], $rec['id'], $remTitle, $remMsg, [
                    'type' => $remType,
                    'recipient_user_id' => $rec['id'],
                    'recipient_role' => $rec['role'],
                    'related_session_id' => $ses['id'],
                    'priority' => 'high',
                    'dedupe_key' => $dedupeKey
                ]);

                if ($sent) {
                    $remindersSent++;
                }
            }
        }
    }

    $res = [
        'status' => 'success',
        'message' => 'Cron background execution completed.',
        'sessions_scanned' => $processed,
        'reminders_dispatched' => $remindersSent,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if (php_sapi_name() === 'cli') {
        echo "[CRON] " . $res['message'] . " (Scanned: {$res['sessions_scanned']}, Sent: {$res['reminders_dispatched']})\n";
    } else {
        echo json_encode($res);
    }
} catch (Exception $e) {
    if (php_sapi_name() === 'cli') {
        echo "[CRON ERROR] " . $e->getMessage() . "\n";
    } else {
        jsonError(500, "Cron execution failed: " . $e->getMessage());
    }
}
