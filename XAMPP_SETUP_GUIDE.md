# TutorLink - XAMPP Setup & MySQL Database Guide

To run the TutorLink application locally with full database functionality, XAMPP (or any Apache + MySQL stack) is required.

## Step 1: Install XAMPP
Download and install XAMPP for Windows, macOS, or Linux from [https://www.apachefriends.org](https://www.apachefriends.org).

## Step 2: Move Application Files
Copy the entire TutorLink project folder into your XAMPP `htdocs` directory:
- **Windows:** `C:\xampp\htdocs\tutorlink\`
- **macOS:** `/Applications/XAMPP/htdocs/tutorlink/`
- **Linux:** `/opt/lampp/htdocs/tutorlink/`

## Step 3: Start XAMPP Server
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

## Step 4: Import Database (Automatic or Manual)
- **Automatic Setup:** Simply open `http://localhost/tutorlink/` in your browser. The backend script `api/config.php` will automatically create `tutorlink_db` and all required tables (`students`, `tutors`, `subjects`, `matches`, `schedules`, `payments`, `sessions`, `notifications`).
- **Manual Setup via phpMyAdmin:**
  1. Open `http://localhost/phpmyadmin/`.
  2. Click **Import**.
  3. Select `database.sql` from the project root folder.
  4. Click **Go**.

## Step 5: Access TutorLink
Navigate to `http://localhost/tutorlink/` in your browser.

- All new user registrations (Students & Tutors) will automatically be saved into the MySQL database tables.
- Each new user receives a fresh, isolated dashboard page with personal data isolation.
- New user sign-ups trigger automatic notifications to the System Admin.
