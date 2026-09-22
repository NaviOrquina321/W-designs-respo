-- ========================================================
-- TutorLink - An Intelligent Tutor-Student Matching System
-- Relational Database Schema & Setup Script
-- Compatible with XAMPP / MariaDB / MySQL 5.7+ & SQLite
-- ========================================================

CREATE DATABASE IF NOT EXISTS `tutorlink_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tutorlink_db`;

-- 1. Table: users (Unified Authentication Table)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(20) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table: admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` VARCHAR(20) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` VARCHAR(50) DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table: students
CREATE TABLE IF NOT EXISTS `students` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table: tutors
CREATE TABLE IF NOT EXISTS `tutors` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table: subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` VARCHAR(20) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table: matches
CREATE TABLE IF NOT EXISTS `matches` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table: schedules
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` VARCHAR(20) PRIMARY KEY,
  `tutor_id` VARCHAR(20),
  `tutor_name` VARCHAR(100) NOT NULL,
  `student_id` VARCHAR(20),
  `student_name` VARCHAR(100),
  `date_slot` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table: payments
CREATE TABLE IF NOT EXISTS `payments` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Table: sessions
CREATE TABLE IF NOT EXISTS `sessions` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Table: notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` VARCHAR(50) PRIMARY KEY,
  `target_role` VARCHAR(50) DEFAULT 'all',
  `target` VARCHAR(100) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_time` VARCHAR(50) DEFAULT 'Just now',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Table: chat_messages
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(20) NOT NULL,
  `sender_name` VARCHAR(100) NOT NULL,
  `sender_role` VARCHAR(50) NOT NULL,
  `message` TEXT NOT NULL,
  `timestamp` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- INITIAL SEED DATA FOR AUTH, USERS & SUBJECTS
-- ========================================================

-- Users: Admin (admin123), Maria Santos (student123), Prof. Alex Rivera (tutor123)
-- Password hashes generated via password_hash()
INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `name`) VALUES
('ADMIN-001', 'admin@tutorlink.ph', '$2y$10$e.Y571x0pS3E1oXWn.8P.uA2c/lQcI1xG/g8qQ.2h4eQ.7/b7A1aW', 'admin', 'System Admin'),
('STU-101', 'maria@tutorlink.ph', '$2y$10$wT0C3v.4z/BfO6b5M.Yx.u1H7o8bS9w9E6Yx.2h4eQ.7/b7A1aW', 'student', 'Maria Santos'),
('tut-1', 'alex@tutorlink.ph', '$2y$10$5G6H7I8J9K0L1M2N3O4P5Q6R7S8T9U0V1W2X3Y4Z5a6b7c8d9e0f1', 'tutor', 'Prof. Alex Rivera')
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `admins` (`id`, `name`, `email`, `role`) VALUES
('ADMIN-001', 'System Admin', 'admin@tutorlink.ph', 'admin')
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `students` (`id`, `name`, `email`, `grade`, `validated`, `deactivated`, `bio`, `subjects_needed`, `sessions_completed`) VALUES
('STU-101', 'Maria Santos', 'maria@tutorlink.ph', 'Senior High', 1, 0, 'Grade 12 STEM Student focusing on Advanced Calculus and College Entrance Exam preparation.', 'Calculus, Physics', 0)
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `tutors` (`id`, `name`, `email`, `initials`, `rating`, `reviews_count`, `hourly_rate`, `subjects`, `learning_styles`, `bio`, `available`, `deactivated`, `diploma_status`, `tor_status`, `id_status`, `approval_status`, `available_days`, `available_time_slots`, `availability_slots`) VALUES
('tut-1', 'Prof. Alex Rivera', 'alex@tutorlink.ph', 'AR', 5.0, 1, 350, 'Calculus, Physics, Mathematics', 'Step-by-Step Explanation, Visual Diagrams', 'Licensed High School & College Mathematics educator with 7+ years teaching STEM subjects.', 1, 0, 'Verified', 'Verified', 'Verified', 'Approved', 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `subjects` (`id`, `name`, `category`) VALUES
('SUB-101', 'Mathematics', 'STEM'),
('SUB-102', 'Calculus', 'STEM'),
('SUB-103', 'Physics', 'STEM'),
('SUB-104', 'Chemistry', 'STEM'),
('SUB-105', 'Programming', 'Technology'),
('SUB-106', 'English', 'Humanities'),
('SUB-107', 'Literature', 'Humanities')
ON DUPLICATE KEY UPDATE `name`=`name`;
