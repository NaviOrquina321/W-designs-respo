<?php
// api/config.php - Default XAMPP MySQL Configuration
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tutorlink_db';

try {
    // Attempt connection
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO_ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO_FETCH_ASSOC
    ]);

    // Ensure database exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname` ");

    // Ensure tables exist automatically if empty
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `students` (
          `id` VARCHAR(20) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `grade` VARCHAR(50) NOT NULL,
          `validated` TINYINT(1) DEFAULT 1,
          `deactivated` TINYINT(1) DEFAULT 0,
          `bio` TEXT,
          `subjects_needed` VARCHAR(255),
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `tutors` (
          `id` VARCHAR(20) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `initials` VARCHAR(10) NOT NULL,
          `rating` DECIMAL(3,1) DEFAULT 5.0,
          `reviews_count` INT DEFAULT 0,
          `hourly_rate` INT NOT NULL,
          `subjects` VARCHAR(255) NOT NULL,
          `learning_styles` VARCHAR(255) NOT NULL,
          `bio` TEXT,
          `available` TINYINT(1) DEFAULT 1,
          `deactivated` TINYINT(1) DEFAULT 0,
          `diploma_status` VARCHAR(50) DEFAULT 'Verified',
          `tor_status` VARCHAR(50) DEFAULT 'Verified',
          `id_status` VARCHAR(50) DEFAULT 'Verified',
          `approval_status` VARCHAR(50) DEFAULT 'Approved',
          `available_days` VARCHAR(255) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
          `available_time_slots` VARCHAR(255) DEFAULT '09:00 AM - 05:00 PM',
          `blocked_dates` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `subjects` (
          `id` VARCHAR(20) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `category` VARCHAR(100) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `matches` (
          `id` VARCHAR(20) PRIMARY KEY,
          `student_name` VARCHAR(100) NOT NULL,
          `tutor_name` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `score` INT NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Pending Review',
          `match_reason` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `sessions` (
          `id` VARCHAR(20) PRIMARY KEY,
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
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `payments` (
          `id` VARCHAR(20) PRIMARY KEY,
          `student_name` VARCHAR(100) NOT NULL,
          `method` VARCHAR(50) NOT NULL,
          `ref_no` VARCHAR(100) NOT NULL,
          `amount` INT NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Confirmed',
          `payout_status` VARCHAR(50) DEFAULT 'Pending',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `notifications` (
          `id` VARCHAR(50) PRIMARY KEY,
          `target` VARCHAR(100) NOT NULL,
          `title` VARCHAR(150) NOT NULL,
          `message` TEXT NOT NULL,
          `created_time` VARCHAR(50) DEFAULT 'Just now',
          `is_read` TINYINT(1) DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'XAMPP MySQL Database Connection Failed: ' . $e->getMessage()
    ]);
    exit();
}
?>