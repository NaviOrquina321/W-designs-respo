-- ========================================================
-- TutorLink - Database Migration Script
-- For upgrading an existing MySQL/MariaDB tutorlink_db schema
-- ========================================================

USE `tutorlink_db`;

-- 1. Remove tutor document verification & approval columns
ALTER TABLE `tutors` DROP COLUMN `diploma_status`;
ALTER TABLE `tutors` DROP COLUMN `tor_status`;
ALTER TABLE `tutors` DROP COLUMN `id_status`;
ALTER TABLE `tutors` DROP COLUMN `approval_status`;

-- 2. Add failed_login_attempts table for MySQL compatibility
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempt_time` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Align index on notifications table
ALTER TABLE `notifications` ADD INDEX `idx_notifications_role_target` (`target_role`, `target`, `is_read`);

-- Instructions for SQLite Fallback (tutorlink.db):
-- If using the SQLite fallback file (tutorlink.db), simply delete the existing 'tutorlink.db' file.
-- Upon the next HTTP request, api/config.php will automatically regenerate 'tutorlink.db'
-- using the new streamlined schema without document status or approval columns.
