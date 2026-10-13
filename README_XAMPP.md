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

*Note: `api/config.php` automatically creates the `tutorlink_db` database and schema if it does not already exist, with a fallback to SQLite (`tutorlink.db`) if MySQL is offline.*

---

## Access the Web Application
Open your browser and navigate to:
```
http://localhost/tutorlink/
```

### Test User Accounts (Log In)
- **Student Role:** Maria Santos (`maria@tutorlink.ph` / `student123`)
- **Tutor Role:** Prof. Alex Rivera (`alex@tutorlink.ph` / `tutor123`)
- **Admin Role:** System Admin (`admin@tutorlink.ph` / `admin123`)
