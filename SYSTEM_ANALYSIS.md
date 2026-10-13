# TutorLink System Architecture & Technical Analysis Report

## Executive Summary
**TutorLink** is a full-stack tutor-student matching and learning management web application built using a minimalist monochrome UI aesthetic backed by PHP REST APIs and relational database persistence (supporting MySQL with zero-config SQLite fallback).

The platform facilitates rule-based tutor matching recommendations, calendar booking scheduling, simulated GCash e-Wallet payments with platform service fee calculations, live virtual workspace collaboration with embedded chat and notes, post-session evaluations, and admin back-office management.

---

## 1. System Architecture Overview

```
+-----------------------------------------------------------------------------------+
|                                  FRONTEND LAYER                                   |
|  +-------------------------------------+  +------------------------------------+  |
|  | index.php / app.js                  |  | admin.php                          |  |
|  | - Student & Tutor Web Portals       |  | - Back-Office Administrative Portal|  |
|  | - Interactive Drawers & Workspace   |  | - Single-Canvas Tab Navigation     |  |
|  | - GCash Checkout Integration        |  | - Real-time Analytics & Controls   |  |
|  +-------------------------------------+  +------------------------------------+  |
|                                    |                                              |
|                           HTTP REST Requests (JSON)                               |
|                                    v                                              |
+-----------------------------------------------------------------------------------+
|                                 BACKEND API LAYER                                 |
|  +-----------------------------------------------------------------------------+  |
|  | PHP PDO REST Endpoints (/api/*.php)                                         |  |
|  |  - login.php & register.php   (Authentication & Password Hashing)           |  |
|  |  - matches.php & tutors.php   (Rule-based Matching & Tutor Profiles)          |  |
|  |  - sessions.php & chat.php    (Session Management & Real-time Messaging)     |  |
|  |  - payments.php & admin.php   (GCash Transactions & Back-Office Analytics)   |  |
|  |  - notifications.php          (Targeted Role Notifications)                  |  |
|  +-----------------------------------------------------------------------------+  |
|                                    |                                              |
|                         Database Abstraction (PDO Driver)                         |
|                                    v                                              |
+-----------------------------------------------------------------------------------+
|                                 PERSISTENCE LAYER                                 |
|  +------------------------------------+   +------------------------------------+  |
|  | Primary: MySQL (tutorlink_db)      |   | Fallback: SQLite (tutorlink.db)    |  |
|  | - Auto-schema Migration            |   | - File-based Portable Storage      |  |
|  +------------------------------------+   +------------------------------------+  |
+-----------------------------------------------------------------------------------+
```

### Key Architectural Highlights:
1. **Decoupled REST API Architecture:** All frontend UI components communicate asynchronously with PHP endpoints via `fetch()` API calls, passing and receiving structured JSON payloads.
2. **Dual-Database Resilience:** The `api/config.php` bootstrap automatically attempts a connection to a local MySQL instance (`tutorlink_db`). If MySQL is offline, it seamlessly falls back to file-backed SQLite (`tutorlink.db`), ensuring zero-downtime execution in any host environment.
3. **Monochrome Design System:** Built with custom CSS variables (`--ink`, `--paper`, `--panel`, `--line`, `--muted`), providing a high-contrast, professional, accessibility-compliant user experience without heavy external framework bloat.
4. **Role Isolation:** Dynamic view switching based on active session credentials for three primary user roles:
   - **Students:** Browse tutors, request matching, book sessions, submit GCash payments, join virtual workspaces, and leave feedback.
   - **Tutors:** Manage schedule slots, accept/reject match requests, conduct live virtual workspace sessions, and track earnings/payouts.
   - **Admins:** High-level platform metrics, user validation, payment verification, session oversight, broadcast notifications, and detailed system analytics.

---

## 2. Relational Database Schema & Data Models

The database consists of 11 relational tables configured with strict auto-migration and auto-seeding.

| Table Name | Primary Key | Description & Key Columns |
| :--- | :--- | :--- |
| `users` | `id` (Auto-Inc) | Central authentication repository storing `user_id`, `email`, `password_hash` (BCRYPT), `role`, and `name`. |
| `students` | `id` (VARCHAR) | Student profile data including `grade`, `validated`, `deactivated`, `subjects_needed`, and `sessions_completed`. |
| `tutors` | `id` (VARCHAR) | Comprehensive tutor profiles: `rating`, `hourly_rate`, `subjects`, `learning_styles`, `approval_status`, and availability settings. |
| `admins` | `id` (VARCHAR) | Administrative staff accounts. |
| `subjects` | `id` (VARCHAR) | Catalog of academic subjects categorized by STEM, Humanities, and Technology. |
| `matches` | `id` (VARCHAR) | Matching requests generated between students and tutors with match compatibility `score` and `status`. |
| `schedules` | `id` (VARCHAR) | Individual tutor time slots and calendar availability. |
| `payments` | `id` (VARCHAR) | Financial records detailing GCash transactions, transaction reference numbers (`ref_no`), amounts, and payout status. |
| `sessions` | `id` (VARCHAR) | Confirmed tutoring appointments tracking hourly rate, commission fee, total paid, status, notes, rating, and student feedback. |
| `notifications` | `id` (VARCHAR) | In-app alerts routed by `target_role` (`student`, `tutor`, `admin`, or `all`). |
| `chat_messages` | `id` (Auto-Inc) | Live virtual workspace chat messages linked to specific `session_id` records. |

---

## 3. End-to-End System Workflow & Features

### A. Authentication & Session Security
- **Unified Login:** Accounts are looked up directly by email, automatically retrieving password hash and account role without requiring manual role dropdown selection.
- **BCRYPT Hashing:** All password creations and updates utilize PHP's standard `password_hash(..., PASSWORD_BCRYPT)` and `password_verify()` routines.
- **PHP Server Sessions:** Authentication relies on secure server-side PHP sessions with HTTP-only cookies (`SameSite=Lax`).

### B. Rule-Based Tutor Matching & Scheduling
- **Rule-Based Compatibility Scoring:** Calculates match fit percentage in the browser based on subject match (50%), learning style match (20%), time slot availability (15%), and tutor rating (15%).
- **Match Requests:** Requests are submitted to tutors who can accept or cancel them.
- **Slot Reservation:** Students select available dates and time slots published by approved tutors or admin-created available slots.

### C. Simulated GCash e-Wallet Checkout
- **Fee Rule:** Student pays Hourly Rate + 10% Service Fee (e.g. ₱350 rate + ₱35 fee = ₱385 total). Tutor receives 100% of hourly rate (₱350), and the platform retains the 10% service fee (₱35).
- **Instant Receipt Generation:** Generates valid simulated GCash transaction reference numbers (`GC-XXXXXXXX`) stored in `payments` and `sessions` tables.

### D. Live Virtual Workspace & Session Completion
- **Collaborative Session Space:** Embedded canvas whiteboard/drawing tools and instant messaging (`chat_messages`).
- **Controlled Completion:** Tutors finalize sessions within the Live Workspace where they record post-session notes/remarks.
- **Student Reviews:** Students provide 1-5 star ratings and written feedback upon session completion, triggering server-side recomputation of the tutor's aggregate rating.

### E. Admin Back-Office Dashboard (`admin.php`)
- **Single-Canvas Tab Interface:** Navigation across Overview KPIs, Students, Tutors, Tutor Matching, Schedules, Payments, Reports, and Broadcast Notifications.
- **System Administration:** Admin controls for tutor verification, document audit (Diploma, TOR, ID), user deactivation/reactivation, broadcast announcements, payment confirmation, and payout processing.

---

## 4. Security & Technical Architecture

| Component | Assessment | Recommendation |
| :--- | :--- | :--- |
| **Password Storage** | **Secure:** BCRYPT hashing is enforced for all account creation and authentication calls. | Maintain current BCRYPT cost factor. |
| **SQL Injection** | **Secure:** All API endpoints utilize PHP PDO prepared statements with parameter binding. | Continue strict usage of PDO bound variables. |
| **Session Security** | **Secure:** Native PHP server sessions with strict HttpOnly and SameSite cookie policies. | Clear session cookies on logout. |
| **Role Authorization** | **Secure:** Server-side `requireRole()` checks on all protected API routes. | Enforce ID-only and role-scoped access. |

---

## Conclusion
The TutorLink codebase provides a complete web system for managing tutor-student sessions with rule-based matching recommendations and simulated GCash checkout workflows.
