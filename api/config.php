<?php
// api/config.php - TutorLink Database Bootstrap & One-Time Schema Migration
ini_set('display_errors', '0');
date_default_timezone_set('Asia/Manila');

// Two portals share this backend and database but NOT their login session:
//   main  -> index.php  (students and tutors)
//   admin -> admin.php / admin-login.php (administrators only)
// Each portal has its own session cookie, so an admin and a student can be signed in
// at the same time in the same browser, in two tabs.
$portal = 'main';
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isApiCall = (strpos($scriptPath, '/api/') !== false);   // api/admin.php is an API, not the admin page
if ((!$isApiCall && in_array(basename($scriptPath), ['admin.php', 'admin-login.php'], true))
    || strtolower($_SERVER['HTTP_X_TUTORLINK_PORTAL'] ?? '') === 'admin') {
    $portal = 'admin';
}
if (!defined('TUTORLINK_PORTAL')) {
    define('TUTORLINK_PORTAL', $portal);
}

if (session_status() === PHP_SESSION_NONE) {
    session_name($portal === 'admin' ? 'TUTORLINK_ADMIN' : 'TUTORLINK_MAIN');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax'
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

try {
    // 1. Attempt MySQL Connection
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
} catch (PDOException $e) {
    // 2. Fallback to SQLite if MySQL is offline
    try {
        $isSqliteFallback = true;
        error_log("DATABASE WARNING: MySQL connection failed (" . $e->getMessage() . "). Fallback to SQLite (data/tutorlink.db).");

        // Keep the SQLite file inside data/ (blocked from web access by data/.htaccess and router.php)
        $dataDir = __DIR__ . '/../data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0775, true);
        }
        $dbPath = $dataDir . '/tutorlink.db';
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
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
          `id` $autoInc,
          `user_id` VARCHAR(20) NOT NULL UNIQUE,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `password_hash` VARCHAR(255) NOT NULL,
          `role` VARCHAR(50) NOT NULL,
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
          `validated` TINYINT(1) DEFAULT 0,
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
          `hourly_rate` INT NOT NULL,
          `subjects` VARCHAR(255) NOT NULL,
          `learning_styles` VARCHAR(255) NOT NULL,
          `bio` TEXT,
          `available` TINYINT(1) DEFAULT 1,
          `deactivated` TINYINT(1) DEFAULT 0,
          `diploma_status` VARCHAR(50) DEFAULT 'Pending',
          `tor_status` VARCHAR(50) DEFAULT 'Pending',
          `id_status` VARCHAR(50) DEFAULT 'Pending',
          `approval_status` VARCHAR(50) DEFAULT 'Pending Review',
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
          `amount` INT NOT NULL,
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
          `hourly_rate` INT NOT NULL,
          `commission_fee` INT NOT NULL,
          `total_paid` INT NOT NULL,
          `gcash_ref` VARCHAR(100) NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Confirmed',
          `payout_status` VARCHAR(50) DEFAULT 'Unpaid',
          `notes` TEXT,
          `rating` INT DEFAULT 0,
          `feedback` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE RESTRICT,
          FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE RESTRICT
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
          `id` VARCHAR(50) PRIMARY KEY,
          `target_role` VARCHAR(50) DEFAULT 'all',
          `target_id` VARCHAR(20),
          `target` VARCHAR(100) NOT NULL,
          `title` VARCHAR(150) NOT NULL,
          `message` TEXT NOT NULL,
          `is_read` TINYINT(1) DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

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
            $stmtTut = $pdo->prepare("INSERT INTO `tutors` (`id`, `name`, `email`, `initials`, `rating`, `reviews_count`, `hourly_rate`, `subjects`, `learning_styles`, `bio`, `available`, `deactivated`, `diploma_status`, `tor_status`, `id_status`, `approval_status`, `available_days`, `available_time_slots`, `availability_slots`) VALUES (?, ?, ?, ?, 0.0, 0, 350, ?, ?, ?, 1, 0, 'Verified', 'Verified', 'Verified', 'Approved', 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')");
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
        $currentVersion = 1;
    }

    // Version 2: notifications are matched by user id instead of by name
    if ($currentVersion < 2) {
        $hasTargetId = false;
        if ($isSQLite) {
            foreach ($pdo->query("PRAGMA table_info(`notifications`)")->fetchAll() as $col) {
                if ($col['name'] === 'target_id') { $hasTargetId = true; break; }
            }
        } else {
            $hasTargetId = (bool)$pdo->query("SHOW COLUMNS FROM `notifications` LIKE 'target_id'")->fetch();
        }
        if (!$hasTargetId) {
            $pdo->exec("ALTER TABLE `notifications` ADD COLUMN `target_id` VARCHAR(20)");
        }
        $pdo->exec("INSERT INTO `schema_version` (`version`) VALUES (2);");
    }
} catch (PDOException $e) {
    error_log("Schema migration error: " . $e->getMessage());
}
?>