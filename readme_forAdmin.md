# TutorLink - System Administrator Guide & Documentation (`readme_forAdmin.md`)

Welcome to the **TutorLink System Administrator Manual**. This guide provides system administration instructions, credentials, database connection procedures, and monitoring workflows for TutorLink.

---

## 1. System Admin Access & Credentials

To log in to the TutorLink Admin Dashboard:

* **Admin Portal Login URL:** `http://localhost/tutorlink/admin-login.php`
* **Admin Email:** `admin@tutorlink.ph`
* **Admin Password:** `admin123`

---

## 2. Administrator Dashboard Capabilities

The Admin Dashboard manages 11 relational tables (`users`, `admins`, `students`, `tutors`, `subjects`, `matches`, `schedules`, `payments`, `sessions`, `notifications`, `chat_messages`):

1. **System KPI Monitoring Cards:**
   - **Total Registered Students:** Real-time count of registered students.
   - **Verified Tutors Count:** Real-time count of active tutors.
   - **Total Transaction Volume:** Aggregate gross value of session payments (₱).
   - **Platform Net Revenue:** 10% platform commission auto-calculated from non-cancelled sessions.

2. **Student & Tutor Governance:**
   - Account Validation & Document Status verification.
   - One-click **Deactivate / Reactivate** user toggle.
   - Full student profile and tutor profile detailed view modals.

3. **Tutor Document & Credentials Verification:**
   - Audit diploma, transcript of records (TOR), and government ID upload statuses.
   - Approve or decline tutor registrations. Approval requires Diploma, TOR, and Government ID all set to Verified.

4. **Tutor Matching Governance:**
   - Review AI / Rule-based matching recommendations.
   - Approve or cancel tutors for student requests.

5. **Schedule & Session Auditing:**
   - Live session logs and status monitoring (Confirmed, In Progress, Completed, Cancelled).
   - Cancel sessions and reschedule confirmed sessions.

6. **Financial Management & Tutor Payouts:**
   - Payment transaction ledger.
   - Process payouts to tutors after session completion (tutor receives 100% of hourly rate, platform retains the 10% service fee paid by student).

7. **Notification Broadcast:**
   - Broadcast platform announcements to Students, Tutors, or All Users simultaneously.

8. **Monitoring Reports & CSV Export:**
   - Date-range filter, tutor filter, subject filter, revenue summary, and admin-only CSV export for sessions and payments.

---

## 3. Database Architecture & Setup Instructions

### Database Credentials (`config.local.php` or `config.sample.php`):
* **Host:** `localhost`
* **Database Name:** `tutorlink_db`
* **Username:** `root`
* **Password:** `""` (empty string)

### Manual Database Import (phpMyAdmin):
1. Open `http://localhost/phpmyadmin/`
2. Import `database.sql` located at the root of the project.
