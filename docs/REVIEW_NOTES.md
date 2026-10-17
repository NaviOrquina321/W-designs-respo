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

---

## Claude review of commit 53e756c (fixes applied on branch `jules-reviewed`)
- **Security:** the SQLite DB, `database.sql`, `docs/` and even `.git/config` could be downloaded when running with `php -S`. Added `router.php` (`php -S localhost:8000 router.php`), `.git`/docs/tests rules in `.htaccess`, and moved the SQLite fallback to `data/tutorlink.db` (folder is access-denied).
- **Sessions:** an admin could reopen or complete a Cancelled session. Status is now forward-only and Completed/Cancelled are final. A session can only be started or completed when its payment is Confirmed (also for admin completion).
- **Login:** logging in loaded only guest data (no sessions or notifications) until a page refresh. The role is now set before the sync.
- **Calendar:** "My Schedule" was hardcoded to March 2026. It is now a real month calendar with navigation.
- **Styling:** added the missing CSS for the Matching Session workspace and the notifications panel.
- **Reports API:** date and status filters are validated.
- **Repo hygiene:** `test-results/.last-run.json` untracked.
- Verified on a clean SQLite DB: API flow, security downloads blocked, admin 7 tabs, report shortcuts, CSV, student and admin signed in together in one browser. MySQL and a real AI key are still untested.
