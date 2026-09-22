<?php
// api/config.php - TutorLink Database Connection & Auto Migration
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tutorlink_db';

$pdo = null;

try {
    // 1. Attempt MySQL connection (for standard XAMPP environment)
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname` ");
} catch (PDOException $e) {
    // 2. Fallback to SQLite if local MySQL server is offline
    try {
        $dbPath = __DIR__ . '/../tutorlink.db';
        $pdo = new PDO("sqlite:$dbPath", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (Exception $sqle) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database Connection Failed: ' . $sqle->getMessage()
        ]);
        exit();
    }
}

// Ensure database schema and tables exist
try {
    $isSQLite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');

    $autoInc = $isSQLite ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";
    $textType = "TEXT";

    // 1. Table: users
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
      `id` $autoInc,
      `user_id` VARCHAR(20) NOT NULL UNIQUE,
      `email` VARCHAR(100) NOT NULL UNIQUE,
      `password_hash` VARCHAR(255) NOT NULL,
      `role` VARCHAR(50) NOT NULL,
      `name` VARCHAR(100) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Table: admins
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
      `id` VARCHAR(20) PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `email` VARCHAR(100) NOT NULL UNIQUE,
      `role` VARCHAR(50) DEFAULT 'admin',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 3. Table: students
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

    // 4. Table: tutors
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

    // 5. Table: subjects
    $pdo->exec("CREATE TABLE IF NOT EXISTS `subjects` (
      `id` VARCHAR(20) PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `category` VARCHAR(100) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 6. Table: matches
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
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 7. Table: schedules
    $pdo->exec("CREATE TABLE IF NOT EXISTS `schedules` (
      `id` VARCHAR(20) PRIMARY KEY,
      `tutor_id` VARCHAR(20),
      `tutor_name` VARCHAR(100) NOT NULL,
      `student_id` VARCHAR(20),
      `student_name` VARCHAR(100),
      `date_slot` VARCHAR(100) NOT NULL,
      `subject` VARCHAR(100) NOT NULL,
      `status` VARCHAR(50) DEFAULT 'Available',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 8. Table: payments
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
      `payout_status` VARCHAR(50) DEFAULT 'Pending',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 9. Table: sessions
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
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 10. Table: notifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS `notifications` (
      `id` VARCHAR(50) PRIMARY KEY,
      `target_role` VARCHAR(50) DEFAULT 'all',
      `target` VARCHAR(100) NOT NULL,
      `title` VARCHAR(150) NOT NULL,
      `message` TEXT NOT NULL,
      `created_time` VARCHAR(50) DEFAULT 'Just now',
      `is_read` TINYINT(1) DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 11. Table: chat_messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS `chat_messages` (
      `id` $autoInc,
      `session_id` VARCHAR(20) NOT NULL,
      `sender_name` VARCHAR(100) NOT NULL,
      `sender_role` VARCHAR(50) NOT NULL,
      `message` TEXT NOT NULL,
      `timestamp` VARCHAR(50) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Seed Admin Account if users table is empty
    $checkUser = $pdo->query("SELECT COUNT(*) as cnt FROM `users`")->fetch();
    if ($checkUser['cnt'] == 0) {
        $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $studentHash = password_hash('student123', PASSWORD_BCRYPT);
        $tutorHash = password_hash('tutor123', PASSWORD_BCRYPT);

        // Insert Admin
        $stmt = $pdo->prepare("INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `name`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['ADMIN-001', 'admin@tutorlink.ph', $adminHash, 'admin', 'System Admin']);

        $stmtAdmin = $pdo->prepare("INSERT INTO `admins` (`id`, `name`, `email`, `role`) VALUES (?, ?, ?, ?)");
        $stmtAdmin->execute(['ADMIN-001', 'System Admin', 'admin@tutorlink.ph', 'admin']);

        // Insert Seed Student Maria Santos
        $stmt->execute(['STU-101', 'maria@tutorlink.ph', $studentHash, 'student', 'Maria Santos']);
        $stmtStu = $pdo->prepare("INSERT INTO `students` (`id`, `name`, `email`, `grade`, `validated`, `deactivated`, `bio`, `subjects_needed`, `sessions_completed`) VALUES (?, ?, ?, ?, 1, 0, ?, ?, 0)");
        $stmtStu->execute(['STU-101', 'Maria Santos', 'maria@tutorlink.ph', 'Senior High', 'Grade 12 STEM Student focusing on Advanced Calculus and College Entrance Exam preparation.', 'Calculus, Physics']);

        // Insert Seed Tutor Prof. Alex Rivera
        $stmt->execute(['tut-1', 'alex@tutorlink.ph', $tutorHash, 'tutor', 'Prof. Alex Rivera']);
        $stmtTut = $pdo->prepare("INSERT INTO `tutors` (`id`, `name`, `email`, `initials`, `rating`, `reviews_count`, `hourly_rate`, `subjects`, `learning_styles`, `bio`, `available`, `deactivated`, `diploma_status`, `tor_status`, `id_status`, `approval_status`, `available_days`, `available_time_slots`, `availability_slots`) VALUES (?, ?, ?, ?, 5.0, 1, 350, ?, ?, ?, 1, 0, 'Verified', 'Verified', 'Verified', 'Approved', 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')");
        $stmtTut->execute(['tut-1', 'Prof. Alex Rivera', 'alex@tutorlink.ph', 'AR', 'Calculus, Physics, Mathematics', 'Step-by-Step Explanation, Visual Diagrams', 'Licensed High School & College Mathematics educator with 7+ years teaching STEM subjects.']);

        // Seed Default Subjects
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

} catch (PDOException $e) {
    // Ignore migration warnings
}
?>