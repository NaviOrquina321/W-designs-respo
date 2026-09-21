<?php
// api/config.php - TutorLink Database & PHP Server Configuration
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$db_file = __DIR__ . '/../tutorlink_db.sqlite';

try {
    // Universal SQLite connection
    $pdo = new \PDO("sqlite:" . $db_file);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

    // Auto-create database schema if missing
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
          `id` VARCHAR(50) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `password_hash` VARCHAR(255) NOT NULL,
          `role` VARCHAR(20) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `students` (
          `id` VARCHAR(50) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `grade` VARCHAR(50) NOT NULL,
          `validated` TINYINT(1) DEFAULT 0,
          `deactivated` TINYINT(1) DEFAULT 0,
          `bio` TEXT,
          `subjects_needed` VARCHAR(255),
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `tutors` (
          `id` VARCHAR(50) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(100) NOT NULL UNIQUE,
          `initials` VARCHAR(10) NOT NULL,
          `rating` DECIMAL(3,1) DEFAULT 5.0,
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
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `subjects` (
          `id` VARCHAR(50) PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `category` VARCHAR(100) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `matches` (
          `id` VARCHAR(50) PRIMARY KEY,
          `student_id` VARCHAR(50),
          `student_name` VARCHAR(100) NOT NULL,
          `tutor_id` VARCHAR(50),
          `tutor_name` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `score` INT NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Pending Review',
          `match_reason` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `schedules` (
          `id` VARCHAR(50) PRIMARY KEY,
          `tutor_id` VARCHAR(50),
          `tutor_name` VARCHAR(100) NOT NULL,
          `date_slot` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Available',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `payments` (
          `id` VARCHAR(50) PRIMARY KEY,
          `student_id` VARCHAR(50),
          `student_name` VARCHAR(100) NOT NULL,
          `method` VARCHAR(50) NOT NULL,
          `ref_no` VARCHAR(100) NOT NULL,
          `amount` INT NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Confirmed',
          `payout_status` VARCHAR(50) DEFAULT 'Pending',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `sessions` (
          `id` VARCHAR(50) PRIMARY KEY,
          `student_id` VARCHAR(50),
          `student_name` VARCHAR(100) NOT NULL,
          `tutor_id` VARCHAR(50) NOT NULL,
          `tutor_name` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `session_date` VARCHAR(50) NOT NULL,
          `time_slot` VARCHAR(50) NOT NULL,
          `hourly_rate` INT NOT NULL,
          `commission_fee` INT NOT NULL,
          `total_paid` INT NOT NULL,
          `gcash_ref` VARCHAR(100) NOT NULL,
          `status` VARCHAR(50) DEFAULT 'Confirmed',
          `payout_status` VARCHAR(50) DEFAULT 'Unpaid',
          `notes` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `notifications` (
          `id` VARCHAR(50) PRIMARY KEY,
          `target_role` VARCHAR(50) DEFAULT 'all',
          `target` VARCHAR(100) NOT NULL,
          `title` VARCHAR(150) NOT NULL,
          `message` TEXT NOT NULL,
          `created_time` VARCHAR(50) DEFAULT 'Just now',
          `is_read` TINYINT(1) DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `workspace_notes` (
          `session_id` VARCHAR(50) PRIMARY KEY,
          `notes` TEXT,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `workspace_chat` (
          `id` INTEGER PRIMARY KEY AUTOINCREMENT,
          `session_id` VARCHAR(50) NOT NULL,
          `sender_name` VARCHAR(100) NOT NULL,
          `sender_role` VARCHAR(20) NOT NULL,
          `message` TEXT NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `ratings` (
          `id` VARCHAR(50) PRIMARY KEY,
          `tutor_id` VARCHAR(50) NOT NULL,
          `student_name` VARCHAR(100) NOT NULL,
          `subject` VARCHAR(100) NOT NULL,
          `stars` INT NOT NULL,
          `comment` TEXT,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Seed default credentials if empty
    $userCheck = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCheck == 0) {
        $defaultPassword = password_hash("password123", PASSWORD_BCRYPT);

        // Seed Maria Santos (Student)
        $pdo->exec("INSERT INTO users (id, name, email, password_hash, role) VALUES ('STU-101', 'Maria Santos', 'maria@tutorlink.ph', '$defaultPassword', 'student')");
        $pdo->exec("INSERT INTO students (id, name, email, grade, validated, bio, subjects_needed) VALUES ('STU-101', 'Maria Santos', 'maria@tutorlink.ph', 'Senior High', 1, 'Grade 12 STEM Student focusing on Advanced Calculus.', 'Calculus, Physics')");

        // Seed Prof. Alex Rivera (Tutor)
        $pdo->exec("INSERT INTO users (id, name, email, password_hash, role) VALUES ('tut-1', 'Prof. Alex Rivera', 'prof.alex@tutorlink.ph', '$defaultPassword', 'tutor')");
        $pdo->exec("INSERT INTO tutors (id, name, email, initials, rating, reviews_count, hourly_rate, subjects, learning_styles, bio, available, diploma_status, tor_status, id_status, approval_status) VALUES ('tut-1', 'Prof. Alex Rivera', 'prof.alex@tutorlink.ph', 'AR', 4.9, 38, 350, 'Mathematics, Calculus, Physics', 'Visual & Diagrams, Step-by-Step Explanation', 'Licensed Mathematics Professor with 8+ years experience.', 1, 'Verified', 'Verified', 'Verified', 'Approved')");

        // Seed System Admin
        $pdo->exec("INSERT INTO users (id, name, email, password_hash, role) VALUES ('ADMIN-001', 'System Admin', 'admin@tutorlink.ph', '$defaultPassword', 'admin')");

        // Seed Default Subjects
        $pdo->exec("INSERT INTO subjects (id, name, category) VALUES
            ('SUB-101', 'Mathematics', 'STEM'),
            ('SUB-102', 'Calculus', 'STEM'),
            ('SUB-103', 'Physics', 'STEM'),
            ('SUB-104', 'Chemistry', 'STEM'),
            ('SUB-105', 'Programming', 'Technology'),
            ('SUB-106', 'English', 'Humanities'),
            ('SUB-107', 'Literature', 'Humanities')
        ");
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database Connection Error: ' . $e->getMessage()
    ]);
    exit();
}
?>