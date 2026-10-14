-- ========================================================
-- TutorLink - An Intelligent Tutor-Student Matching System
-- Relational Database Schema & Setup Script
-- Target Environment: XAMPP / MariaDB / MySQL 5.7+
-- ========================================================

CREATE DATABASE IF NOT EXISTS `tutorlink_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tutorlink_db`;

-- Schema Version Table
CREATE TABLE IF NOT EXISTS `schema_version` (
  `version` INT PRIMARY KEY,
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `schema_version` (`version`) VALUES (1) ON DUPLICATE KEY UPDATE `version`=`version`;

-- 1. Table: users (Unified Authentication Table)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(20) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','student','tutor') NOT NULL,
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
  `hourly_rate` DECIMAL(10,2) NOT NULL,
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
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE SET NULL
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
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table: payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` VARCHAR(20) PRIMARY KEY,
  `student_id` VARCHAR(20),
  `student_name` VARCHAR(100) NOT NULL,
  `tutor_id` VARCHAR(20),
  `tutor_name` VARCHAR(100),
  `method` VARCHAR(50) NOT NULL,
  `ref_no` VARCHAR(100) NOT NULL UNIQUE,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Confirmed',
  `payout_status` VARCHAR(50) DEFAULT 'Unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE SET NULL
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
  FOREIGN KEY (`tutor_id`) REFERENCES `tutors`(`id`) ON DELETE RESTRICT,
  INDEX `idx_sessions_tutor_date` (`tutor_id`, `session_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Table: notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` VARCHAR(50) PRIMARY KEY,
  `target_role` VARCHAR(50) DEFAULT 'all',
  `target` VARCHAR(100) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
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
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`session_id`) REFERENCES `sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Table: notification_reads
CREATE TABLE IF NOT EXISTS `notification_reads` (
  `notification_id` VARCHAR(50) NOT NULL,
  `user_id` VARCHAR(20) NOT NULL,
  `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed initial default users & subjects
INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `name`) VALUES
('ADMIN-001', 'admin@tutorlink.ph', '$2y$10$4KtVRKjPTI1Etb2VYt0nVeQsv0tBnHxSEUIYUheMZ.NRFL63mXud.', 'admin', 'System Admin'),
('STU-101', 'maria@tutorlink.ph', '$2y$10$EoaI4ZcneLctw4BNmbF2UOocpbRSAoA/1e5BmhvkStB0nApxWIxzi', 'student', 'Maria Santos'),
('tut-1', 'alex@tutorlink.ph', '$2y$10$tM/i.UWwhopnQ1j8kNPUuO/ZYt9Xjkt.dSwBAKUSAecnnxoWeSEQy', 'tutor', 'Prof. Alex Rivera')
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `admins` (`id`, `name`, `email`, `role`) VALUES
('ADMIN-001', 'System Admin', 'admin@tutorlink.ph', 'admin')
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `students` (`id`, `name`, `email`, `grade`, `validated`, `deactivated`, `bio`, `subjects_needed`, `sessions_completed`) VALUES
('STU-101', 'Maria Santos', 'maria@tutorlink.ph', 'Senior High', 1, 0, 'Grade 12 STEM Student focusing on Advanced Calculus and College Entrance Exam preparation.', 'Calculus, Physics', 0)
ON DUPLICATE KEY UPDATE `email`=`email`;

INSERT INTO `tutors` (`id`, `name`, `email`, `initials`, `rating`, `reviews_count`, `hourly_rate`, `subjects`, `learning_styles`, `bio`, `available`, `deactivated`, `diploma_status`, `tor_status`, `id_status`, `approval_status`, `available_days`, `available_time_slots`, `availability_slots`) VALUES
('tut-1', 'Prof. Alex Rivera', 'alex@tutorlink.ph', 'AR', 5.0, 1, 350, 'Calculus, Physics, Mathematics', 'Visual & Diagrams, Step-by-Step Explanation', 'Licensed High School & College Mathematics educator with 7+ years teaching STEM subjects.', 1, 0, 'Verified', 'Verified', 'Verified', 'Approved', 'Mon,Tue,Wed,Thu,Fri', '09:00 AM - 05:00 PM', '09:00 AM, 02:00 PM, 04:00 PM')
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
