# TutorLink - Automated CLI Test Suite (`README_TESTS.md`)

This directory contains the automated PHP CLI smoke test suite for verifying TutorLink's backend REST APIs, authentication security, financial calculations, scheduling rules, and report exports.

---

## How to Run the Tests

Execute the following command in your terminal from the project root:

```bash
php tests/smoke_test.php
```

---

## What Is Covered

1. **Role Password & Auth Verification:** Validates BCRYPT hash verification for Admin, Student, and Tutor roles.
2. **Dual Session Cookie Login Gates:** Confirms that Admin logins are rejected on `index.php` (`TUTORLINK_MAIN`) and Student/Tutor logins are rejected on `admin-login.php` (`TUTORLINK_ADMIN`).
3. **Demo GCash Auto-Confirmation:** Verifies that session booking via `api/payment_provider.php` creates single session, payment, and schedule records in a single PDO transaction with instant `Confirmed` status.
4. **Double Booking Guard:** Verifies that attempting to book a tutor for an already occupied date and slot throws an error.
5. **Past Slot Protection:** Validates that booking past dates or slots earlier today is detected and rejected.
6. **Student 24-Hour Reschedule Enforcement:** Verifies that student reschedules are allowed for sessions starting > 24 hours in the future and rejected for sessions starting <= 24 hours.
7. **Targeted Role Notifications:** Verifies targeted notification routing and delivery to specific students and tutors.
8. **Formula-Safe CSV Export Sanitization:** Verifies that CSV cell exports starting with `=`, `+`, `-`, or `@` are sanitized with a leading single quote `'`.
9. **Schedule Privacy & Data Isolation:** Verifies schedule slot associations.

---

## Notes
- The test suite operates on an isolated, temporary SQLite database (`tests/test_temp.db`) created on demand and deleted upon completion.
- Returns exit code `0` on all passes or non-zero (`1`) on any failure.
