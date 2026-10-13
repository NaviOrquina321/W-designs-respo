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
2. Click on the **Import** tab at the top.
3. Drop `tutorlink_db` if it exists, then click **Import** and select `database.sql` from the project folder.
4. Click **Import** (or **Go**).

*Note: `api/config.php` automatically creates the `tutorlink_db` database and schema if it does not already exist. If MySQL is offline it silently falls back to a SQLite file at `data/tutorlink.db`, so always start MySQL in XAMPP or your data will go into that separate file.*

The `.htaccess` files in the project root and in `data/` stop browsers from downloading the database, `.sql` and `.md` files. Keep Apache's `AllowOverride` enabled for the project folder (XAMPP's default allows it).

### Running without XAMPP (PHP dev server)
```
php -S localhost:8000 router.php
```
`router.php` applies the same download protection that `.htaccess` gives on Apache.

### Session rules to know when testing
- A booked session starts out as *Confirmed* but its GCash payment is *Pending*.
- The admin must confirm the payment (Payments tab) before the tutor can start or complete the session.
- Completed and Cancelled sessions cannot be reopened.

---

## Access the Web Application
TutorLink has two separate sites that share the same database. You can keep both open at the same time, in two tabs:

| Site | URL | Who signs in |
|------|-----|--------------|
| Main site | `http://localhost/tutorlink/` | Students and tutors |
| Admin Portal | `http://localhost/tutorlink/admin-login.php` | Administrators only |

Each site has its own login session, so an admin and a student can be signed in together in the same browser. An admin account cannot sign in on the main site, and student or tutor accounts cannot sign in on the Admin Portal. The main site footer and the admin header both have a link to open the other site in a new tab.

### Test User Accounts (Log In)
- **Student Role:** Maria Santos (`maria@tutorlink.ph` / `student123`)
- **Tutor Role:** Prof. Alex Rivera (`alex@tutorlink.ph` / `tutor123`)
- **Admin Role:** System Admin (`admin@tutorlink.ph` / `admin123`)
