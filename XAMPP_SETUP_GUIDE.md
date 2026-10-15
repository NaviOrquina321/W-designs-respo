# TutorLink - Quick XAMPP & PHP Server Setup Guide

TutorLink supports both full database persistence via **XAMPP / PHP** and instant local preview fallback mode.

## Why did you see "Authentication server connection error"?
This message appears if you opened `index.html` directly or ran a static web server (`python -m http.server`) without starting PHP/MySQL. JavaScript `fetch()` calls to `api/auth.php` require PHP to execute.

---

## How to Run TutorLink with Active Backend (Choose Option A or B):

### Option A: Using XAMPP (Recommended)
1. Install **XAMPP** from [apachefriends.org](https://www.apachefriends.org).
2. Copy the project folder into your XAMPP `htdocs` directory:
   - Windows: `C:\xampp\htdocs\tutorlink\`
   - macOS: `/Applications/XAMPP/htdocs/tutorlink/`
3. Open **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.
4. Open your browser and go to: `http://localhost/tutorlink/`
   *(Database tables and seed data will be created automatically!)*

---

### Option B: Using PHP Built-in Server
1. Open terminal inside the project directory.
2. Run command:
   ```bash
   php -S localhost:8000 -t .
   ```
3. Open your browser and go to: `http://localhost:8000/`

---

## Offline Fallback Mode
If PHP is not running, TutorLink now automatically falls back to instant offline mode:
- You can log in, register, test AI tutor matching, book sessions, view receipts, and navigate all dashboards directly in your browser.
