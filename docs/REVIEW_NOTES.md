# TutorLink - Review Notes & System Status Report (`REVIEW_NOTES.md`)

## Executive Summary
All requested project requirements and FDD features have been polished, verified, and tested across TutorLink. The application runs seamlessly on XAMPP (Apache + MySQL / MariaDB) with zero-config SQLite fallback resilience.

---

## Polished FDD Features & Specific Improvements

### 1. Landing Page Feature Teaser Boxes (`index.php`, `styles.css`, `app.js`)
- **Teaser Behavior:** The 5 feature teaser cards (AI Tutor Matching, Calendar Scheduling, Real-Time Notifications, Online Payment, Matching Session) serve exclusively as teasers.
- **Unauthenticated Flow:** Clicking any card opens the Sign In modal (`modal-auth` on the login tab) and displays a short contextual toast. No unauthenticated navigation or API calls take place.
- **Authenticated Flow:** If a user is already signed in, clicking a teaser card immediately navigates them to their active dashboard (`switchRole()`).
- **Accessibility & Styling:** Replaced inline `style="cursor:pointer"` with CSS class `.feature-card-interactive`. Added `role="button"`, `tabindex="0"`, and `Enter`/`Space` keydown event handlers for full keyboard accessibility.

### 2. Admin Payment Status Safeguards (`api/payments.php`)
- **Confirmed Payment Guard:** Reverting a `Confirmed` payment back to `Pending` is strictly blocked if the associated payout status is `Paid Out` OR if the linked tutoring session status is `In Progress` or `Completed`.

### 3. Student Account Validation & Notifications (`api/students.php`, `admin.php`)
- **Status Display:** Student validation status is clearly displayed in the Admin Students table via color-coded badges (`Validated` vs `Pending`).
- **Student Alert:** When an admin validates a student account, an automated notification ("Account Validated") is sent directly to the student to inform them that they can begin booking sessions.

### 4. Admin Reports Filters & Empty State (`admin.php`, `api/reports.php`)
- **Active Filter Summary:** Displays a prominent active filter indicator badge above the report table (e.g., `Active Filter: 12 Oct - 18 Oct | Tutor: Prof. Alex Rivera`).
- **Visible Empty State:** When no sessions match the selected filter criteria, a clear empty state block is rendered ("No sessions in this period").

### 5. Admin Notifications Read Tracking (`admin.php`, `api/notifications.php`)
- **Read/Unread Indicators:** The Admin Notifications log renders individual status badges (`Read` / `Unread`) for logged notification broadcasts based on database read receipts (`notification_reads`).

### 6. Real API Automated Smoke Test Suite (`tests/smoke_test.php`)
- **Real Schema Boot:** Rewrote `tests/smoke_test.php` to boot the actual `api/config.php` database bootstrap engine against a temporary SQLite database (`data/test.db`).
- **End-to-End Coverage:** Tests real admin/student authentication, session booking, double-booking rejection, 24-hour reschedule lock, session cancellation, reports queries, and notifications read tracking.
- **Clean Cleanup:** Automatically deletes `data/test.db` upon test completion. All test artifacts are properly ignored in `.gitignore`.

### 7. Helper Function Centralization (`api/helpers.php`)
- **Single Source of Truth:** All shared API helper functions (`jsonError`, `getInput`, `requireLogin`, `requireRole`, `newId`, `parseSlotStartTime`, `parseSlotEndTime`, `sanitizeCsvCell`, `notify`, `toCamelArray`) are defined strictly in `api/helpers.php` to prevent duplicate function declaration conflicts.

---

## Code Cleanup & Removed Legacy Code

1. **Duplicate Declarations Removed:** Removed duplicate `sanitizeCsvCell` function declaration from `api/reports.php`.
2. **Obsolete Files:** Removed legacy `index.html` and unneeded root `.db` test artifacts.
3. **Closing PHP Tags:** Removed trailing closing `?>` tags across all pure API files in `api/` to prevent accidental whitespace injection in JSON responses.
4. **Git Cleanup:** Updated `.gitignore` to prevent committing generated `.db` files, `test-results/`, and `logs/`.

---

## File Changes Summary

- **`index.php`**: Added keyboard-accessible attributes (`role="button"`, `tabindex="0"`, `.feature-card-interactive`) to landing teaser cards.
- **`styles.css`**: Added `.feature-card-interactive` cursor and focus outline styles.
- **`app.js`**: Implemented `handleFeatureTeaserClick()` handling login prompt vs dashboard navigation for teaser cards.
- **`admin.php`**: Added active filter summary indicator and empty state in Reports view, and Read/Unread status badges in Notifications log.
- **`api/config.php`**: Added `TEST_MODE=1` environment switch for testing real schema migrations against `data/test.db`.
- **`api/helpers.php`**: Centralized all shared utility and notification helper functions.
- **`api/payments.php`**: Added linked session status check blocking payment status reversion.
- **`api/students.php`**: Added student notification trigger upon account validation.
- **`tests/smoke_test.php`**: Rewrote CLI integration test runner executing 11 passing checks against a temporary test database.
- **`.gitignore`**: Added `data/*.db` and log file exclusion rules.
