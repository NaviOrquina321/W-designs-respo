# TutorLink - XAMPP / MySQL Setup Guide

TutorLink is designed for easy, zero-configuration deployment on **XAMPP**.

---

## Quick 3-Step Setup Instructions

### Step 1: Copy Files to XAMPP
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
3. Click **Choose File** and select `database.sql` from the project folder.
4. Scroll down and click **Import** (or **Go**).

*Note: The system also includes an automatic database auto-installer in `api/config.php` that will create the `tutorlink_db` database automatically if it does not already exist!*

---

## Access the Web Application
Open your browser and navigate to:
```
http://localhost/tutorlink/
```

### Test User Accounts (Log In)
- **Student Role:** Maria Santos (`maria@tutorlink.ph`)
- **Tutor Role:** Prof. Alex Rivera (`alex@tutorlink.ph`)
- **Admin Role:** System Admin (`admin@tutorlink.ph`)
