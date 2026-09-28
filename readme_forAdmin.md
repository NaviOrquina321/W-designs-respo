# TutorLink - System Administrator Guide & Documentation (`readme_forAdmin.md`)

Welcome to the **TutorLink System Administrator Manual**. This guide provides system administration instructions, credentials, database connection procedures, and monitoring workflows for TutorLink.

---

## 1. System Admin Access & Credentials

To log in to the TutorLink Admin Dashboard:

* **Access URL:** `http://localhost:3000` (or `http://localhost/tutorlink/` on XAMPP)
* **Admin Email:** `admin@tutorlink.ph`
* **Admin Password:** `admin123` (or any non-empty password during direct email authentication)

> **Note:** Logins with `admin@tutorlink.ph` or containing `admin` automatically log you into the System Admin role (`ADMIN-001`).

---

## 2. Administrator Dashboard Capabilities

The Admin Dashboard provides administrative controls across 8 FDD modules:

1. **System KPI Monitoring Cards:**
   - **Total Registered Students:** Real-time count of registered students.
   - **Verified Tutors Count:** Real-time count of active tutors.
   - **Total Transaction Volume:** Aggregate gross value of session payments (₱).
   - **Platform Net Revenue:** 10% platform commission auto-calculated from completed sessions.

2. **Student & Tutor Account Governance:**
   - Account Validation & Document Status verification.
   - One-click **Deactivate / Reactivate** user toggle.
   - Full student profile and tutor profile detailed view modals.

3. **Tutor Document & Credentials Verification:**
   - Audit diploma, transcript of records (TOR), and government ID upload statuses.
   - Approve or decline tutor registrations.

4. **Tutor Matching Governance:**
   - Review AI match compatibility scores.
   - Approve or reassign tutors for student requests.

5. **Schedule & Session Auditing:**
   - Live session logs and status monitoring (Confirmed, Completed, Rescheduled).

6. **Financial Management & Tutor Payouts:**
   - Payment transaction ledger.
   - Process net payout (90%) directly to tutors after session completion.

7. **Notification Broadcast:**
   - Broadcast platform announcements to Students, Tutors, or All Users simultaneously.

8. **Printable Performance & Audit Reports:**
   - Generate printable performance reports with financial volume breakdowns and audit trails (`window.print()`).

---

## 3. Database Architecture & XAMPP Setup Instructions

TutorLink is designed for zero-configuration integration with **MySQL / MariaDB** (via XAMPP or standalone MySQL server).

### Database Credentials (`api/config.php`):
* **Host:** `localhost`
* **Database Name:** `tutorlink_db`
* **Username:** `root`
* **Password:** `""` (empty string)

### Manual Database Import (phpMyAdmin):
1. Open `http://localhost/phpmyadmin/`
2. Import `database.sql` located at the root of the project.
3. The SQL script creates all required relational tables (`admins`, `students`, `tutors`, `subjects`, `matches`, `schedules`, `payments`, `sessions`, `notifications`) and seeds the default System Admin account.

*Note: The backend API includes an auto-installer in `api/config.php` that will automatically create the `tutorlink_db` database and its tables if MySQL is running!*

---

## 4. Fresh System Operating State

To ensure isolated data for all new user registrations, the initial database schema starts in a clean state:
* All total volume, earnings, and completed session reports start at **0 / ₱0**.
* New students and tutors receive isolated accounts upon registration with zero initial sessions or transaction history.
