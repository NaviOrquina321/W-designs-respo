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

## Added: separate Admin Portal
- New `admin-login.php`: its own sign-in page for administrators. `admin.php` is the dashboard.
- Admin and main site use different session cookies (`TUTORLINK_ADMIN` and `TUTORLINK_MAIN`), so both can be open and signed in at the same time. They still use the same API and database.
- Each portal only accepts its own users, and a session from one cannot be reused on the other.
- Fixed `admin.php` reloading itself in a loop (the shared script redirected admins to the page they were already on).
- Main site: the "Admins" card and the footer link open the Admin Portal in a new tab. Admin header has "Open Main Site".

## Added: real AI Tutor Matching
- New `api/match.php`: the server asks an AI model (Claude or Gemini) to rank the tutors, then strictly validates the answer (known tutor ids only, integer score 0-100, plain-text reason).
- Falls back to rule-based scoring when there is no key, a timeout (10 s), an HTTP error or an invalid answer. The screen shows "AI Match" or "Rule-based match" so nobody is misled.
- Key lives in `ai_config.php` (gitignored, blocked from download). `ai_config.sample.php` is committed.
- Privacy: student name and email are never sent. Tutor bio text is treated as data, not instructions.
- Cost control: 10-minute result cache and a limit of 10 AI calls per hour per login.
- Rule-based fallback fixed: the old time-of-day check never matched, and tutors with no reviews were scored as 0 stars (now neutral 3.5).
- Tested against a fake AI server (success, fenced JSON, garbage, unknown tutor id, HTTP 500, timeout, cache, privacy, permissions). Not tested against a real provider because no key or internet AI access was available here.

## Added: browse tutors by subject
- The student tutor directory now has subject buttons with a tutor count (All subjects, Calculus, Physics...), a "N tutors available" line, and subject tags on every card. The subject being browsed is filled in and listed first; other subjects are outlined. The search box still works together with the subject filter.
- Cards show "Verified tutor" (approval requires diploma, TOR and ID to be verified) and "New tutor" when there are no reviews yet.
- Original design using TutorLink's own colors. Tested at desktop and phone width.

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

## Merge of Jules' commit e09e01f (admin audit)
- Merged with all earlier review fixes kept (separate portals, AI match endpoint, subject browsing, security rules). Committed `tutorlink.db` removed from tracking.
- Fixed: `api/reports.php` crashed with HTTP 500 (`sanitizeCsvCell` declared twice) so the whole admin Reports tab and CSV export failed.
- Fixed: CSV export used page navigation, which does not carry the admin-portal header, so the download would be rejected. It now downloads via fetch.
- Added to Reports (FDD): Completed Tutoring, Weekly Sessions and Monthly Sessions shortcuts, plus a Status filter (also applied to the CSV). Date and status inputs are validated server side.
- Payments: demo GCash auto-confirms the payment; the admin Payments tab can still set Confirmed/Pending (FDD "Confirm Payment Status"). Tutors can only start/complete sessions whose payment is Confirmed.
- Notifications and payment-provider notifications now carry the recipient id (`target_id`).
- Verified on a clean SQLite DB: API flow (book, double-book guard, payment, admin confirm/cancel, reopen blocked, reports, broadcast) and admin UI in a browser (all tabs, report shortcuts, CSV download). MySQL and the real AI key are still untested.
