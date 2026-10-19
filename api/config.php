<?php
// api/config.php - TutorLink Database Bootstrap & One-Time Schema Migration
ini_set('display_errors', '0');
date_default_timezone_set('Asia/Manila');

// Dual Session Cookie setup (TUTORLINK_ADMIN vs TUTORLINK_MAIN)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if (session_status() === PHP_SESSION_NONE) {
    $script = basename($_SERVER['PHP_SELF'] ?? '');
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $portalHeader = $_SERVER['HTTP_X_TUTORLINK_PORTAL'] ?? '';
    $isAdminContext = ($script === 'admin.php' || $script === 'admin-login.php' || $portalHeader === 'admin' || strpos($ref, 'admin') !== false);

    $cookieName = $isAdminContext ? 'TUTORLINK_ADMIN' : 'TUTORLINK_MAIN';
    session_name($cookieName);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $isHttps
    ]);
    session_start();
}

// Load DB credentials from config.local.php if available, or config.sample.php / defaults
if (file_exists(__DIR__ . '/../config.local.php')) {
    require_once __DIR__ . '/../config.local.php';
} elseif (file_exists(__DIR__ . '/../config.sample.php')) {
    require_once __DIR__ . '/../config.sample.php';
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'tutorlink_db');

$pdo = null;
$isSqliteFallback = false;

if (getenv('TEST_MODE') === '1' || defined('TEST_MODE')) {
    $isSqliteFallback = true;
    $dbPath = defined('SQLITE_TEST_DB') ? SQLITE_TEST_DB : (getenv('SQLITE_TEST_DB') ?: __DIR__ . '/../data/test.db');
    if (!is_dir(dirname($dbPath))) {
        @mkdir(dirname($dbPath), 0777, true);
    }
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("PRAGMA foreign_keys = ON;");
} else {
    try {
        // 1. Attempt MySQL Connection
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");
        $pdo->exec("SET time_zone = '+08:00'");
    } catch (PDOException $e) {
        // 2. Fallback to SQLite if MySQL is offline
        try {
            $isSqliteFallback = true;
            error_log("DATABASE WARNING: MySQL connection failed (" . $e->getMessage() . "). Fallback to SQLite (tutorlink.db).");

            $dbPath = __DIR__ . '/../tutorlink.db';
            $pdo = new PDO("sqlite:$dbPath", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdo->exec("PRAGMA foreign_keys = ON;");
        } catch (Exception $sqle) {
            error_log("Database Connection Failed: " . $sqle->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database Connection Failed']);
            exit();
        }
    }
}

// One-time Schema Migration Engine (using schema_version table)
try {
    $isSQLite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
    $autoInc = $isSQLite ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";

    // Ensure schema_version table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `schema_version` (
      `version` INT PRIMARY KEY,
      `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    $stmtVer = $pdo->query("SELECT MAX(version) as max_ver FROM `schema_version`");
    $rowVer = $stmtVer->fetch();
    $currentVersion = intval($rowVer['max_ver'] ?? 0);

    if ($currentVersion < 1) {
        // Run Table Schemas
        $roleType = $isSQLite ? "VARCHAR(50) NOT NULL" : "ENUM('admin','student','tutor') NOT NULL";

        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
          `id` $autoInc,
          `user_id` VARCHAR(20) NOT NULL UNIQUE,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `password_hash` VARCHAR(255) NOT NULL,
          `role` $roleType,
          `name` VARCHAR(100) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
          `id` VARCHAR(20) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `role` VARCHAR(50) DEFAULT 'admin',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `students` (
          `id` VARCHAR(20) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `grade` VARCHAR(50) NOT NULL,
          `validated` TINYINT(1) DEFAULT 1,
          `deactivated` TINYINT(1) DEFAULT 0,
          `bio` TEXT,
          `subjects_needed` VARCHAR(255),
          `sessions_completed` INT DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `tutors` (
          `id` VARCHAR(20) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) UNIQUE,
          `initials` VARCHAR(10) NOT NULL,
          `rating` DECIMAL(3,1) DEFAULT 0.0,
          `reviews_count` INT DEFAULT 0,
          `hourly_rate` DECIMAL(10,2) NOT NULL,
          `subjects` VARCHAR(255) NOT NULL,
          `learning_styles` VARCHAR(255) NOT NULL,
          `bio` TEXT,
          `available` TINYINT(1) DEFAULT 1,
          `deactivated` TINYINT(1) DEFAULT 0,
          `available_days` VARCHAR(255) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
          `available_time_slots` VARCHAR(255) DEFAULT '09:00 AM - 05:00 PM',
          `blocked_dates` TEXT,
          `availability_slots` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `subjects` (
          `id` VARCHAR(20) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `category` VARCHAR(100) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `matches` (
          `id` VARCHAR(20) PRIMARY KEY,
          `student_id` VARCHAR(20),
          `student_name` VARCHAR(100) NOT NULL,
          `tutor_id` VARCHAR(20),
          `tutor_name` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `score` INT NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Pending Review',
          `match_reason` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE SET NULL,
          FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE SET NULL
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `schedules` (
          `id` VARCHAR(20) PRIMARY KEY,
          `tutor_id` VARCHAR(20),
          `tutor_name` VARCHAR(100) NOT NULL,
          `student_id` VARCHAR(20),
          `student_name` VARCHAR(100),
          `date_slot` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Available',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE SET NULL,
          FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE SET NULL
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
          `id` VARCHAR(20) PRIMARY KEY,
          `student_id` VARCHAR(20),
          `student_name` VARCHAR(100) NOT NULL,
          `tutor_id` VARCHAR(20),
          `tutor_name` VARCHAR(100),
          `method` VARCHAR(50) NOT NULL,
          `ref_no` VARCHAR(100) NOT NULL,
          `amount` DECIMAL(10,2) NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Confirmed',
          `payout_status` VARCHAR(50) DEFAULT 'Unpaid',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE SET NULL,
          FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE SET NULL
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `sessions` (
          `id` VARCHAR(20) PRIMARY KEY,
          `student_id` VARCHAR(20) NOT NULL,
          `student_name` VARCHAR(100) NOT NULL,
          `tutor_id` VARCHAR(20) NOT NULL,
          `tutor_name` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `session_date` DATE NOT NULL,
          `time_slot` VARCHAR(50) NOT NULL,
          `duration_minutes` INT DEFAULT 60,
          `started_at` DATETIME NULL DEFAULT NULL,
          `hourly_rate` DECIMAL(10,2) NOT NULL,
          `commission_fee` DECIMAL(10,2) NOT NULL,
          `total_paid` DECIMAL(10,2) NOT NULL,
          `gcash_ref` VARCHAR(100) NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Confirmed',
          `payout_status` VARCHAR(50) DEFAULT 'Unpaid',
          `notes` TEXT,
          `rating` INT DEFAULT NULL,
          `feedback` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE RESTRICT,
          FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE RESTRICT
        );");

        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_sessions_tutor_date ON sessions(tutor_id, session_date);");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
          `id` VARCHAR(50) PRIMARY KEY,
          `recipient_user_id` VARCHAR(50) DEFAULT NULL,
          `recipient_role` VARCHAR(50) DEFAULT NULL,
          `target_role` VARCHAR(50) DEFAULT 'all',
          `target` VARCHAR(100) NOT NULL,
          `type` VARCHAR(100) DEFAULT 'general',
          `title` VARCHAR(150) NOT NULL,
          `message` TEXT NOT NULL,
          `related_session_id` VARCHAR(50) DEFAULT NULL,
          `related_payment_id` VARCHAR(50) DEFAULT NULL,
          `related_user_id` VARCHAR(50) DEFAULT NULL,
          `action_url` VARCHAR(255) DEFAULT NULL,
          `priority` VARCHAR(20) DEFAULT 'normal',
          `is_read` TINYINT(1) DEFAULT 0,
          `read_at` TIMESTAMP NULL DEFAULT NULL,
          `dedupe_key` VARCHAR(150) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notifications_role_target ON notifications(target_role, target, is_read);");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `chat_messages` (
          `id` $autoInc,
          `session_id` VARCHAR(20) NOT NULL,
          `sender_name` VARCHAR(100) NOT NULL,
          `sender_role` VARCHAR(50) NOT NULL,
          `message` TEXT NOT NULL,
          `timestamp` VARCHAR(50) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`session_id`) REFERENCES `sessions`(`id`) ON DELETE CASCADE
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `notification_reads` (
          `notification_id` VARCHAR(50) NOT NULL,
          `user_id` VARCHAR(20) NOT NULL,
          `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`notification_id`, `user_id`)
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
          `id` $autoInc,
          `email` VARCHAR(100) NOT NULL,
          `ip_address` VARCHAR(45) NOT NULL,
          `attempt_time` INT NOT NULL
        );");

        // Seed initial default users
        $checkUser = $pdo->query("SELECT COUNT(*) as cnt FROM `users`")->fetch();
        if ($checkUser['cnt'] == 0) {
            $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
            $studentHash = password_hash('student123', PASSWORD_BCRYPT);
            $tutorHash = password_hash('tutor123', PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `name`) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['ADMIN-001', 'admin@tutorlink.ph', $adminHash, 'admin', 'System Admin']);

            $stmtAdmin = $pdo->prepare("INSERT INTO `admins` (`id`, `name`, `email`, `role`) VALUES (?, ?, ?, ?)");
            $stmtAdmin->execute(['ADMIN-001', 'System Admin', 'admin@tutorlink.ph', 'admin']);

            $stmt->execute(['STU-101', 'maria@tutorlink.ph', $studentHash, 'student', 'Maria Santos']);
            $stmtStu = $pdo->prepare("INSERT INTO `students` (`id`, `name`, `email`, `grade`, `validated`, `deactivated`, `bio`, `subjects_needed`, `sessions_completed`) VALUES (?, ?, ?, ?, 1, 0, ?, ?, 0)");
            $stmtStu->execute(['STU-101', 'Maria Santos', 'maria@tutorlink.ph', 'Senior High', 'Grade 12 STEM Student focusing on Advanced Calculus.', 'Calculus, Physics']);

            $stmt->execute(['tut-1', 'alex@tutorlink.ph', $tutorHash, 'tutor', 'Prof. Alex Rivera']);
            $stmtTut = $pdo->prepare("INSERT INTO `tutors` (`id`, `name`, `email`, `initials`, `rating`, `reviews_count`, `hourly_rate`, `subjects`, `learning_styles`, `bio`, `available`, `deactivated`, `available_days`, `available_time_slots`, `availability_slots`) VALUES (?, ?, ?, ?, 5.0, 1, 350, ?, ?, ?, 1, 0, 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')");
            $stmtTut->execute(['tut-1', 'Prof. Alex Rivera', 'alex@tutorlink.ph', 'AR', 'Calculus, Physics, Mathematics', 'Visual & Diagrams, Step-by-Step Explanation', 'Licensed High School & College Educator with 7+ years teaching STEM.']);

            $stmtSub = $pdo->prepare("INSERT INTO `subjects` (`id`, `name`, `category`) VALUES (?, ?, ?)");
            $subjects = [
                ['SUB-101', 'Mathematics', 'STEM'],
                ['SUB-102', 'Calculus', 'STEM'],
                ['SUB-103', 'Physics', 'STEM'],
                ['SUB-104', 'Chemistry', 'STEM'],
                ['SUB-105', 'Programming', 'Technology'],
                ['SUB-106', 'English', 'Humanities'],
                ['SUB-107', 'Literature', 'Humanities']
            ];
            foreach ($subjects as $sub) {
                $stmtSub->execute($sub);
            }
        }

        $pdo->exec("INSERT INTO `schema_version` (`version`) VALUES (1);");
    }

    if ($currentVersion < 2) {
        $cols = [
            "recipient_user_id VARCHAR(50) DEFAULT NULL",
            "recipient_role VARCHAR(50) DEFAULT NULL",
            "type VARCHAR(100) DEFAULT 'general'",
            "related_session_id VARCHAR(50) DEFAULT NULL",
            "related_payment_id VARCHAR(50) DEFAULT NULL",
            "related_user_id VARCHAR(50) DEFAULT NULL",
            "action_url VARCHAR(255) DEFAULT NULL",
            "priority VARCHAR(20) DEFAULT 'normal'",
            "read_at TIMESTAMP NULL DEFAULT NULL",
            "dedupe_key VARCHAR(150) DEFAULT NULL"
        ];
        foreach ($cols as $col) {
            $colName = explode(' ', $col)[0];
            try {
                if ($isSQLite) {
                    $pdo->exec("ALTER TABLE `notifications` ADD COLUMN $col;");
                } else {
                    $pdo->exec("ALTER TABLE `notifications` ADD COLUMN `$colName` " . substr($col, strlen($colName) + 1) . ";");
                }
            } catch (Exception $ex) {
                // Column may already exist
            }
        }
        try {
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notifications_recipient ON notifications(recipient_user_id, is_read);");
        } catch (Exception $ex) {}

        $pdo->exec("INSERT INTO `schema_version` (`version`) VALUES (2);");
    }

    if ($currentVersion < 3) {
        $sessionCols = [
            "duration_minutes INT DEFAULT 60",
            "started_at DATETIME NULL DEFAULT NULL"
        ];
        foreach ($sessionCols as $col) {
            $colName = explode(' ', $col)[0];
            try {
                if ($isSQLite) {
                    $pdo->exec("ALTER TABLE `sessions` ADD COLUMN $col;");
                } else {
                    $pdo->exec("ALTER TABLE `sessions` ADD COLUMN `$colName` " . substr($col, strlen($colName) + 1) . ";");
                }
            } catch (Exception $ex) {
                // Column may already exist
            }
        }
        $pdo->exec("INSERT INTO `schema_version` (`version`) VALUES (3);");
    }

    if ($currentVersion < 4) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_audit_log` (
          `id` $autoInc,
          `admin_id` VARCHAR(50) NOT NULL,
          `action` VARCHAR(100) NOT NULL,
          `target` VARCHAR(100) NOT NULL,
          `old_value` TEXT NULL,
          `new_value` TEXT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");
        $pdo->exec("INSERT INTO `schema_version` (`version`) VALUES (4);");
    }
} catch (PDOException $e) {
    error_log("Schema migration error: " . $e->getMessage());
    if (getenv('TEST_MODE') !== '1') {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Schema migration error occurred. Please check server logs.']);
        exit();
    }
}
