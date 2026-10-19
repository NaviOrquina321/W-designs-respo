# TutorLink - System Architecture & Technical Analysis (`SYSTEM_ANALYSIS.md`)

## 1. System Overview
TutorLink is a lightweight, high-performance web platform built using vanilla JavaScript, CSS, HTML, and PHP with MySQL database integration (and automatic zero-config SQLite fallback).

## 2. Key Modules & Database Schema
- **Users & Portals:** Unified `users` table storing BCRYPT password hashes. Isolated session cookies (`TUTORLINK_MAIN` for student/tutor portals vs `TUTORLINK_ADMIN` for administrator portal).
- **Students & Tutors:** `students` table and `tutors` table storing profile information, hourly rates, availability, and active status (`deactivated = 0`).
- **Matching Engine:** `api/match.php` executes AI-assisted or rule-based matching considering subject expertise, learning style compatibility, rating, and schedule availability.
- **Scheduling & Calendar:** `schedules` and `sessions` tables enforcing double-booking prevention and 24-hour reschedule locks.
- **Payments:** `payments` table storing GCash transaction reference numbers, 10% platform fee calculations, and payout tracking.
- **Notifications & Chat:** Real-time polling endpoints (`api/notifications.php` and `api/chat.php`) for unread alerts and session workspace messaging.
