# TutorLink Admin FDD Module & Function Mapping

This document provides an explicit mapping between the 14 Functional Decomposition Diagram (FDD) functions (under the 6 admin modules), their page view/tab in `admin.php`, UI button/control labels, backend REST API endpoints, and corresponding automated integration test assertions.

## FDD Admin Functions Mapping Table

| Module | Exact FDD Function Name | Page / Tab | UI Button / Control Label | API Endpoint | Integration Test Assertions |
|---|---|---|---|---|---|
| **Students** | `View Student List` | `Students` | Button: `View Student List` | `GET api/students.php` | `1. View Student List: Admin can fetch all student records.` |
| **Students** | `Validate Student List` | `Students` | Buttons: `Validate Student List`, `Unvalidate`, `Deactivate`, `Reactivate` | `PUT api/students.php` | `2. Validate Student List: Admin can validate a student.`<br>`2. Validate Student List: Admin can deactivate a student account.`<br>`2. Validate Student List: Deactivated student is blocked from logging in.` |
| **Tutor Matching** | `Review Matching Results` | `Tutor Matching` | Tab / Section: `Review Matching Results` | `GET api/matches.php` | `3. Review Matching Results: Admin fetches match records.` |
| **Tutor Matching** | `Approve Matching Sessions` | `Tutor Matching` | Button: `Approve Matching Sessions` | `PUT api/matches.php` | `4. Approve Matching Sessions: Admin approves pending match.` |
| **Tutor Matching** | `Cancel Matching Sessions` | `Tutor Matching` | Button: `Cancel Matching Sessions` | `PUT api/matches.php` | `5. Cancel Matching Sessions: Admin cancels match with required reason.`<br>`5. Cancel Matching Sessions: Rejects match cancellation when reason is missing (422).` |
| **Schedule** | `New Calendar Schedule` | `Schedule` | Button: `+ Add Available Slot` inside `New Calendar Schedule` | `POST api/schedules.php` | `6. New Calendar Schedule: Admin creates available schedule slot.`<br>`6. New Calendar Schedule: Rejects duplicate slot creation (422).` |
| **Schedule** | `Modify Sessions Schedule` | `Schedule` | Button: `Modify Sessions Schedule` / Reschedule | `PUT api/sessions.php` | `7. Modify Sessions Schedule: Admin reschedules confirmed session.`<br>`7. Modify Sessions Schedule: Admin completes confirmed session.`<br>`7. Modify Sessions Schedule: Rejects reverting completed session (final status rule).` |
| **Payments** | `New Payment Records` | `Payments` | Tab / Section: `New Payment Records` | `GET api/payments.php` | `8. New Payment Records: Admin fetches payment logs.` |
| **Payments** | `Confirm Payment Status` | `Payments` | Button: `Confirm Payment Status` | `PUT api/payments.php` | `9. Confirm Payment Status: Admin confirms payment status.`<br>`9. Confirm Payment Status: Rejects reverting paid out payment to pending.` |
| **Notifications** | `Send Notifications` | `Notifications` | Form Submit Button: `Send Notifications` | `POST api/notifications.php` | `10. Send Notifications: Admin sends targeted notification.` |
| **Notifications** | `View Notifications` | `Notifications` | Sub-tabs: `Sent Log`, `Admin Inbox` | `GET api/notifications.php` | `11. View Notifications: Admin fetches sent notification log.` |
| **Reports** | `View Completed Tutoring` | `Reports` | Button: `View Completed Tutoring` | `GET api/reports.php?preset=completed` | `12. View Completed Tutoring: Admin generates reports summary.` |
| **Reports** | `View Weekly Sessions` | `Reports` | Button: `View Weekly Sessions` | `GET api/reports.php?preset=weekly` | `12. View Completed Tutoring: Admin generates reports summary.` |
| **Reports** | `View Monthly Sessions` | `Reports` | Button: `View Monthly Sessions` | `GET api/reports.php?preset=monthly` | `12. View Completed Tutoring: Admin generates reports summary.` |

---

## Architectural & Security Rules Enforced

1. **Exact Naming Standards:** The word "Manage" has been removed across all Admin UI sidebar navigation items and page headers.
2. **CSV Export Removal:** CSV export buttons and `format=csv` processing have been completely removed. `api/reports.php` returns a `422 Unprocessable Entity` error if CSV format is requested, and `window.print()` styling is provided for official reports printing.
3. **Admin Session Isolation:** Admin authentication requires `TUTORLINK_ADMIN` session context and enforces `requireRole(['admin'])` on all admin API calls.
4. **Audit Logging:** Every data mutation initiated by an admin writes a record to `admin_audit_log` with admin ID, action name, target ID, old value, and new value.
