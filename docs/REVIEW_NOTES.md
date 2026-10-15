# TutorLink - Review Notes & System Status Report (`REVIEW_NOTES.md`)

## Executive Summary
TutorLink has been polished against the project Functional Design Document (FDD). The application runs seamlessly on XAMPP (Apache + MySQL / MariaDB) with zero-config SQLite fallback resilience.

---

## FDD Alignment & Summary of Changes

### Part A - Tutor Document Verification & Approval Removal
- Removed `diploma_status`, `tor_status`, `id_status`, and `approval_status` from `database.sql`, `api/config.php`, `api/register.php`, `api/tutors.php`, `api/match.php`, `api/matches.php`, `api/sessions.php`, `api/admin.php`, `admin.php`, `app.js`, and `index.php`.
- Tutors become visible and bookable immediately upon registration as long as `deactivated = 0`.
- Created `migration.sql` for existing MySQL databases.

### Part B - API Bug Fixes
- **MySQL Login Fix:** Created `failed_login_attempts` in `database.sql` and `migration.sql` using MySQL `INT AUTO_INCREMENT PRIMARY KEY` and removed inline SQLite `CREATE TABLE` from `api/login.php` and `admin-login.php`.
- **Notification User IDs:** Updated `notify()` calls in `api/payment_provider.php` and `api/sessions.php` to pass User IDs (`$student['id']`, `$tutorRecord['id']`, `$session['student_id']`, `$session['tutor_id']`, `'ADMIN-001'`) so alerts correctly reach target user accounts.
- **Schema Alignment:** Synchronized `database.sql` and `api/config.php` definitions (`payments.ref_no` UNIQUE, `idx_notifications_role_target` index).
- **Error Privacy:** Masked detailed SQL migration errors in `api/config.php` and `api/register.php` with safe user response messages.

### Part C - Landing Page Non-Clickable Info Boxes
- Removed button role, tabindex, `.feature-card-interactive` class, and click/keydown event handlers from feature cards and audience cards in `index.php` and `app.js`.
- Removed hover lift/gold border from `.feature-card` in `styles.css`.
- Added "Admin Login" link in `index.php` footer.

### Part D - Student Auto-Validation
- Set student default `validated = 1` on registration in `api/register.php`.
- Removed student validation check from `api/sessions.php`.
- Removed validation column and buttons from `admin.php` while preserving account deactivation features.

---

## File Changes Summary

- **`database.sql`**: Streamlined schema removing document status columns and adding `failed_login_attempts` table.
- **`migration.sql`**: Created ALTER script for existing MySQL databases.
- **`api/config.php`**: Streamlined tutor schema setup and seed data.
- **`api/register.php`**: Auto-validates registered students and simplifies tutor registration.
- **`api/tutors.php`**: Streamlined tutor endpoints removing approval checks.
- **`api/match.php` & `api/matches.php`**: Simplified tutor availability filters to `deactivated = 0`.
- **`api/sessions.php`**: Removed student validation check and updated notification recipient User IDs.
- **`api/payment_provider.php`**: Updated notification recipient User IDs.
- **`api/admin.php` & `admin.php`**: Streamlined Admin Dashboard Tutors and Students views.
- **`app.js`**: Removed teaser handlers and document status mappings.
- **`index.php`**: Updated teaser boxes and footer links.
- **`styles.css`**: Removed feature card hover styling.
- **`tests/smoke_test.php`**: Integration test suite verifying real API endpoints (11 passing tests).
