# TutorLink - Review Notes & System Status Report (`REVIEW_NOTES.md`)

## Executive Summary
All 11 requested project requirements and FDD features have been built, verified, and tested across TutorLink. The application runs on XAMPP (Apache + MySQL / MariaDB) with zero-config SQLite fallback resilience.

---

## Completed Core Features

### 1. AI Tutor Matching (`api/match.php` & `ai_config.sample.php`)
- **Server-side AI Integration:** `api/match.php` handles student matching requests, contacting the OpenAI completions API (10-second timeout, 10-minute session cache per student + subject).
- **Graceful Fallback:** If the API key is missing or the cURL call fails or returns unparseable JSON, the engine falls back to server-side rule-based matching labeled `"Rule-based match"` instead of `"AI match"`.
- **Fair Rating for New Tutors:** Unrated tutors (0 reviews) are assigned a neutral rating of 3.5 so they are not penalized during score calculation.

### 2. Calendar Scheduling & 24-Hour Reschedule Rule (`api/sessions.php`)
- **Past Date / Slot Guard:** Rejects past date bookings and time slots earlier today in Asia/Manila time.
- **24-Hour Reschedule Enforcement:** Students can reschedule `Confirmed` sessions only if the session start is more than 24 hours in the future (`session_start - now > 86400s`).
- **Tutor Notifications:** Rescheduling frees the old slot, reserves the new slot, and notifies the tutor.

### 3. Real-Time Notifications & 24-Hour Reminders (`api/notifications.php`)
- **5-Second Polling:** `app.js` polls every 5 seconds for unread alerts.
- **Single Mark Read:** Clicking a notification marks only that individual notification read via `PUT api/notifications.php?id=...`.
- **24-Hour Session Reminders:** Automated, deduplicated session reminders generated for student and tutor when a session starts in <= 24 hours.

### 4. Online Payment Through GCash (`api/payment_provider.php`)
- **Auto-Confirming Demo Provider:** `processDemoGCashPayment()` creates session, payment, and schedule records in a single PDO transaction with instant `Confirmed` status.
- **10% Service Fee Rules:** Student pays Hourly Rate + 10% Platform Fee, tutor receives 100% of hourly rate, and platform retains 10% commission.
- **Payment & Time-Window Gating:** Payment `Confirmed` is required to access the session. Join/Start actions are enabled strictly during the slot time window (from 15 minutes before slot start until slot end).

### 5. Live Virtual Workspace Session (`api/chat.php`)
- **Interactive Session Space:** Live chat with polling and shared notes panel.
- **Read-Only Archive:** Once a session status transitions to `Completed` or `Cancelled`, the workspace becomes read-only and disables message posting.

### 6. Admin Portal & Separate Logins (`admin-login.php` -> `admin.php`)
- **Separate Portal Login:** Admin login is handled via `admin-login.php`, maintaining isolated session cookies (`TUTORLINK_ADMIN` vs `TUTORLINK_MAIN`).
- **Subject Catalog Protection:** Deleting a subject referenced by existing tutors, match requests, or sessions is blocked.
- **Tutor Document Gate:** Approving a tutor registration requires Diploma, TOR, and Government ID all set to `Verified`.

### 7. Admin Monitoring Reports & CSV Export (`api/reports.php`)
- **Filters & Revenue Summary:** Date-range, tutor, and subject filters with revenue/commission KPI calculations.
- **Formula-Safe CSV Export:** Admin-only CSV download for sessions and payments. Cells starting with `=`, `+`, `-`, or `@` are sanitized with a leading single quote `'`.

### 8. Database Architecture & Migrations (`api/config.php` & `config.local.php`)
- **Config Inheritance:** Loads `config.local.php` if present; falls back to `config.sample.php` or XAMPP defaults (`localhost`, `root`, `""`).
- **One-Time Schema Migration:** Uses `schema_version` table to ensure DDL creation scripts run only once.
- **SQLite Fallback Warning:** Displays an admin warning banner on `admin.php` and writes an error log when SQLite fallback is activated.

---

## Simulated Components & Known Limitations

1. **Simulated GCash Payment:** Payments are processed via `api/payment_provider.php` for demonstration purposes. No real monetary transactions take place.
2. **Polling Architecture:** Real-time updates (chat, workspace, notifications) rely on HTTP polling (5s interval) rather than WebSockets.

---

## Summary of Changed & Created Files

- **`admin-login.php`**: Created dedicated administrator login portal.
- **`admin.php`**: Added Tutors management section, Scheduled Sessions ledger, report filter controls, CSV export buttons, and SQLite warning banner.
- **`ai_config.sample.php`**: Created sample configuration file for AI API completions key.
- **`config.sample.php`**: Created sample configuration file for database credentials.
- **`database.sql`**: Added `schema_version` table definition.
- **`README_TESTS.md`**: Documented CLI smoke test suite execution.
- **`README_XAMPP.md`**: Updated server setup guide and admin login URL.
- **`readme_forAdmin.md`**: Updated administrator manual and capabilities.
- **`REVIEW_NOTES.md`**: Complete summary of implemented features and system status.
- **`styles.css`**: Updated responsive styles down to 390px mobile viewports and added `@media print` rules.
- **`SYSTEM_ANALYSIS.md`**: Updated technical architecture report.
- **`api/admin.php`**: Added `sqlite_fallback` status flag to KPI endpoint.
- **`api/chat.php`**: Enforced read-only restriction for completed/cancelled sessions.
- **`api/config.php`**: Added dual session cookie support (`TUTORLINK_ADMIN` / `TUTORLINK_MAIN`), `config.local.php` loader, `schema_version` migration check, and SQLite fallback logging.
- **`api/helpers.php`**: Added `parseSlotStartTime()`, `parseSlotEndTime()`, and formula-safe `sanitizeCsvCell()`.
- **`api/login.php`**: Enforced portal role login gates.
- **`api/match.php`**: Created AI / rule-based matching endpoint with 10s cURL timeout and 10-minute cache.
- **`api/notifications.php`**: Added 24-hour deduplicated session reminder generator and single-item mark read handler.
- **`api/payment_provider.php`**: Created auto-confirming demo GCash payment processor.
- **`api/reports.php`**: Created filtered analytics and formula-safe CSV export endpoint.
- **`api/sessions.php`**: Added past date/slot booking guards, time-window gating, and student 24-hour reschedule enforcement.
- **`api/subjects.php`**: Enforced reference checks before subject deletion.
- **`api/tutors.php`**: Validated tutor subject choices against official subjects catalog.
- **`app.js`**: Updated AI matching fetch, 5s notification polling, time-window Join gating, and single notification mark read.
- **`index.php`**: Added favicon, reschedule modal, end-session notes modal, and schedule management form.
- **`tests/smoke_test.php`**: Implemented automated PHP CLI smoke test suite running on isolated SQLite database.
