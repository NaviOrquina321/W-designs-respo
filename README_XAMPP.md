# TutorLink - XAMPP / Local Server Setup Guide

TutorLink is designed for easy deployment on **XAMPP** or local PHP development servers.

---

## Quick 3-Step Setup Instructions

### Step 1: Copy Files to Server
Copy or move the `tutorlink` project folder into your XAMPP `htdocs` directory:
- **Windows:** `C:\xampp\htdocs\tutorlink`
- **macOS:** `/Applications/XAMPP/htdocs/tutorlink`

---

### Step 2: Start Apache & MySQL in XAMPP
1. Open **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

---

### Step 3: Import Database (phpMyAdmin)
1. Open your browser and go to `http://localhost/phpmyadmin/`
2. Import `database.sql` from the project folder.

*Note: `api/config.php` automatically creates the `tutorlink_db` database and schema if it does not already exist. If MySQL is offline it silently falls back to a SQLite file at `data/tutorlink.db`, so always start MySQL in XAMPP or your data will go into that separate file.*

The `.htaccess` files in the project root and in `data/` stop browsers from downloading the database, `.sql` and `.md` files. Keep Apache's `AllowOverride` enabled for the project folder (XAMPP's default allows it).

### Running without XAMPP (PHP dev server)
```
php -S localhost:8000 router.php
```
`router.php` applies the same download protection that `.htaccess` gives on Apache.

### Session rules to know when testing
- Booking uses a demo GCash flow: the session is *Confirmed* and the payment is recorded as *Confirmed* automatically.
- The admin can still change a payment's status in the Payments tab. A tutor can only start or complete a session whose payment is Confirmed.
- Completed and Cancelled sessions cannot be reopened.

---

## Access the Web Application
TutorLink has two separate sites that share the same database. You can keep both open at the same time, in two tabs:

| Site | URL | Who signs in |
|------|-----|--------------|
| Main site | `http://localhost/tutorlink/` | Students and tutors |
| Admin Portal | `http://localhost/tutorlink/admin-login.php` | Administrators only |

Each site has its own login session, so an admin and a student can be signed in together in the same browser. An admin account cannot sign in on the main site, and student or tutor accounts cannot sign in on the Admin Portal. The main site footer and the admin header both have a link to open the other site in a new tab.

- **Student & Tutor Portal:** `http://localhost/tutorlink/`
- **Admin Portal Entry:** `http://localhost/tutorlink/admin-login.php`

### Test User Accounts
- **Student Role:** Maria Santos (`maria@tutorlink.ph` / `student123`)
- **Tutor Role:** Prof. Alex Rivera (`alex@tutorlink.ph` / `tutor123`)
- **Admin Role:** System Admin (`admin@tutorlink.ph` / `admin123`)

---

## Turning on AI Tutor Matching

TutorLink ranks tutors with an AI model that runs on the **server** (the key never reaches the browser).
Without a key it still works: it uses a rule-based matcher and labels results **"Rule-based match"** instead of **"AI match"**.

1. Get an API key from your provider:
   - **Claude (Anthropic):** https://console.anthropic.com (paid, pay per use)
   - **Gemini (Google AI Studio):** https://aistudio.google.com (has a free tier)
2. Copy `ai_config.sample.php` to `ai_config.php` (same folder).
3. Open `ai_config.php`, set `'provider'` (`'anthropic'` or `'gemini'`), paste your key into `'api_key'`, and check that `'model'` is a model your provider currently offers.
4. Save. Log in as a student, open **Find a Tutor**, and the results should say **AI Match**.

Notes:
- `ai_config.php` is gitignored and blocked from web download. Never commit or share it.
- If the AI is slow (over 10 seconds), returns something invalid, or the key is wrong, the student still gets rule-based results.
- Results are remembered for 10 minutes per student and search, and each student can trigger at most 10 AI calls per hour, to control cost.
- Only the student's grade level, subjects needed and bio, plus public tutor details, are sent to the AI. Names and emails are never sent.
