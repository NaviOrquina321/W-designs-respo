# TutorLink code review: fixes and what is still open

Reviewed branch: `jules/tutorlink-system-analysis-setup-10361298479451547798` (commit 57201c1).
Tested by running the PHP API locally (SQLite) and calling every role's main flows.

## Fixed in this review
| # | Problem | Fix |
|---|---------|-----|
| 1 | SQLite database `tutorlink.db` was downloadable by URL (contains password hashes) | DB moved to `data/`, `.htaccess` (Apache) and `router.php` (PHP dev server) block `.db/.sql/.md/.log/.json` |
| 2 | Tutor or admin could start/complete a session while its payment was still Pending | New check: payment must be Confirmed first |
| 3 | Admin could reopen Completed/Cancelled sessions (double booking, wrong counters) | Forward-only status transitions; admin completion now updates `sessions_completed` |
| 4 | Notifications were matched by user NAME, so two users with the same name saw each other's | Added `notifications.target_id`; matched by id (auto-migrates old databases) |
| 5 | Any logged-in student could see who booked other people's slots | Student/tutor names hidden on other people's schedule rows |
| 6 | Schedule slots accepted any text, any status, and duplicates | Date format check, past-date block, duplicate block, status whitelist |
| 7 | Match requests accepted subjects the tutor doesn't teach | Server verifies the subject; reason text capped at 300 chars |
| 8 | Two simultaneous bookings could take the same slot (MySQL) | Tutor row locked during booking |
| 9 | Seed tutor started with a fake 5.0 rating and 1 review | Seed is now 0.0 / 0 reviews |
| 10 | Session cookie lacked the Secure flag on HTTPS | Added |
| 11 | Clutter in the repo | Removed `test-results/` and 4 unused ring-page images; added `.gitignore` |

## Verified working (no change needed)
Login, registration, role checks on every endpoint, prepared statements everywhere, output escaping (XSS) in `app.js` and `admin.php`, server-side price and 10% commission, double-booking block, rating recalculation, chat access limited to session participants.

## Still open (needs a decision or more work)
1. **AI Tutor Matching is rule-based**, not AI. Your doc promises an AI API. Needs a server-side AI call (with the current scoring as fallback) or a doc wording change.
2. **GCash is simulated.** It generates a reference number and does not call GCash. Fine for a demo; say so in the doc.
3. **Students cannot cancel or reschedule their own sessions.** Only tutors and admin can.
4. **No login rate limiting, no password reset, no email verification.** Minimum password length is 6.
5. **Demo accounts use known passwords** (`admin123`, `student123`, `tutor123`). Change them before any real use.
6. **Silent SQLite fallback** when MySQL is off can hide data in a separate file.
7. **The browser UI was not clicked through** in this review (no browser was available). The PHP API and JS syntax were tested, but the screens still need a manual pass.
