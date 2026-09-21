-- ========================================================
-- TutorLink - An Intelligent Tutor-Student Matching System
-- MySQL Relational Database Schema & Setup Script
-- Compatible with XAMPP / MariaDB / MySQL 5.7+
-- ========================================================

CREATE DATABASE IF NOT EXISTS `tutorlink_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tutorlink_db`;

-- 1. Table: admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` VARCHAR(20) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` VARCHAR(50) DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table: students
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

-- 3. Table: tutors
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

-- 4. Table: subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` VARCHAR(20) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table: matches
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

-- 6. Table: schedules
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` VARCHAR(20) PRIMARY KEY,
  `tutor_name` VARCHAR(100) NOT NULL,
  `date_slot` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table: payments
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

-- 8. Table: sessions
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

-- 9. Table: notifications
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

-- ========================================================
-- INITIAL DEFAULT SYSTEM ADMIN ACCOUNT & DEFAULT SUBJECTS
-- ========================================================

INSERT INTO `admins` (`id`, `name`, `email`, `role`) VALUES
('ADMIN-001', 'System Admin', 'admin@tutorlink.ph', 'admin')
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
