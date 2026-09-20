-- ========================================================
-- TutorLink - Database Schema & Seed Data for XAMPP / MySQL
-- Database Name: tutorlink_db
-- ========================================================

CREATE DATABASE IF NOT EXISTS `tutorlink_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tutorlink_db`;

-- 1. Table: students
CREATE TABLE IF NOT EXISTS `students` (
  `id` VARCHAR(20) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `grade` VARCHAR(50) NOT NULL,
  `validated` TINYINT(1) DEFAULT 1,
  `bio` TEXT,
  `subjects_needed` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table: tutors
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
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table: matches
CREATE TABLE IF NOT EXISTS `matches` (
  `id` VARCHAR(20) PRIMARY KEY,
  `student_name` VARCHAR(100) NOT NULL,
  `tutor_name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `score` INT NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Pending Review',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table: schedules
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` VARCHAR(20) PRIMARY KEY,
  `tutor_name` VARCHAR(100) NOT NULL,
  `date_slot` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table: payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` VARCHAR(20) PRIMARY KEY,
  `student_name` VARCHAR(100) NOT NULL,
  `method` VARCHAR(50) NOT NULL,
  `ref_no` VARCHAR(100) NOT NULL,
  `amount` INT NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Confirmed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table: sessions
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
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table: notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` VARCHAR(50) PRIMARY KEY,
  `target` VARCHAR(100) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_time` VARCHAR(50) DEFAULT 'Just now',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================================
-- SEED INITIAL DATA
-- ========================================================

INSERT INTO `students` (`id`, `name`, `email`, `grade`, `validated`, `bio`, `subjects_needed`) VALUES
('STU-101', 'Maria Santos', 'maria@tutorlink.ph', 'Senior High', 1, 'Grade 12 STEM Student focusing on Advanced Calculus and College Entrance Exam preparation.', 'Calculus, Physics'),
('STU-102', 'Juan Dela Cruz', 'juan@tutorlink.ph', 'College', 1, '2nd Year Computer Science student looking for web development and algorithms mentoring.', 'Programming, Mathematics'),
('STU-103', 'Angela Torres', 'angela@tutorlink.ph', 'High School', 0, 'Grade 10 student striving to build strong foundations in High School Algebra and Chemistry.', 'Mathematics, Chemistry')
ON DUPLICATE KEY UPDATE `name`=`name`;

INSERT INTO `tutors` (`id`, `name`, `initials`, `rating`, `reviews_count`, `hourly_rate`, `subjects`, `learning_styles`, `bio`, `available`) VALUES
('tut-1', 'Prof. Alex Rivera', 'AR', 4.9, 38, 350, 'Mathematics, Calculus, Physics', 'Visual & Diagrams, Step-by-Step Explanation', 'Licensed Mathematics Professor with 8+ years experience making complex algebra and calculus easy to grasp.', 1),
('tut-2', 'Engr. Bea Soriano', 'BS', 4.8, 29, 400, 'Programming, Mathematics, Calculus', 'Hands-on Practice, Step-by-Step Explanation', 'Software Engineer & Code Instructor specializing in Python, JavaScript, and data structures.', 1),
('tut-3', 'Dr. Carlos Mendoza', 'CM', 5.0, 45, 450, 'Physics, Chemistry', 'Auditory & Discussion, Visual & Diagrams', 'Physics PhD graduate dedicated to interactive, real-world physics experiments and conceptual learning.', 1),
('tut-4', 'Ms. Diana Reyes', 'DR', 4.7, 22, 300, 'English, Literature', 'Step-by-Step Explanation, Auditory & Discussion', 'English Literature Specialist assisting students in essay writing, grammar, and oral communications.', 1)
ON DUPLICATE KEY UPDATE `name`=`name`;

INSERT INTO `matches` (`id`, `student_name`, `tutor_name`, `subject`, `score`, `status`) VALUES
('MATCH-201', 'Maria Santos', 'Prof. Alex Rivera', 'Calculus II', 98, 'Approved'),
('MATCH-202', 'Angela Torres', 'Dr. Carlos Mendoza', 'Physics', 92, 'Pending Review')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `schedules` (`id`, `tutor_name`, `date_slot`, `subject`, `status`) VALUES
('SCH-301', 'Prof. Alex Rivera', '2026-03-16 (02:00 PM)', 'Calculus II', 'Available'),
('SCH-302', 'Engr. Bea Soriano', '2026-03-17 (10:00 AM)', 'Programming', 'Booked'),
('SCH-303', 'Dr. Carlos Mendoza', '2026-03-18 (09:00 AM)', 'Physics', 'Available')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `payments` (`id`, `student_name`, `method`, `ref_no`, `amount`, `status`) VALUES
('PAY-401', 'Maria Santos', 'GCash', 'GC-9920182341', 385, 'Confirmed'),
('PAY-402', 'Maria Santos', 'GCash', 'GC-8812039481', 440, 'Confirmed'),
('PAY-403', 'Juan Dela Cruz', 'GCash', 'GC-7712938471', 495, 'Confirmed'),
('PAY-404', 'Angela Torres', 'PayMaya', 'PM-5510293841', 330, 'Pending Confirmation')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `sessions` (`id`, `student_name`, `tutor_id`, `tutor_name`, `subject`, `session_date`, `time_slot`, `hourly_rate`, `commission_fee`, `total_paid`, `gcash_ref`, `status`, `notes`) VALUES
('SESS-101', 'Maria Santos', 'tut-1', 'Prof. Alex Rivera', 'Calculus II', '2026-03-15', '02:00 PM', 350, 35, 385, 'GC-9920182341', 'Confirmed', 'Review derivatives and integration techniques for upcoming midterm.'),
('SESS-100', 'Maria Santos', 'tut-2', 'Engr. Bea Soriano', 'Programming', '2026-03-10', '10:00 AM', 400, 40, 440, 'GC-8812039481', 'Completed', 'Intro to JavaScript Functions and DOM Manipulation.'),
('SESS-099', 'Juan Dela Cruz', 'tut-3', 'Dr. Carlos Mendoza', 'Physics', '2026-03-08', '02:00 PM', 450, 45, 495, 'GC-7712938471', 'Completed', 'Newtonian Physics & Equilibrium problems.')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `notifications` (`id`, `target`, `title`, `message`, `created_time`, `is_read`) VALUES
('notif-1', 'Maria Santos', 'Session Confirmed!', 'Your Calculus II session with Prof. Alex Rivera is confirmed for March 15 at 2:00 PM.', '10 mins ago', 0),
('notif-2', 'Maria Santos', 'GCash Payment Received', 'Payment of P385.00 confirmed (Ref: GC-9920182341). Receipt available in dashboard.', '12 mins ago', 0),
('notif-3', 'All Users', 'Welcome to TutorLink', 'Explore AI Tutor Matching or browse available tutors to start your personalized learning.', '1 day ago', 1)
ON DUPLICATE KEY UPDATE `id`=`id`;
