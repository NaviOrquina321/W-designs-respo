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
|  | - Student & Tutor Web Portals       |  | - Apex-Style Back-Office Portal    |  |
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
|  |  - matches.php & tutors.php   (Automated Matching & Tutor Profiles)          |  |
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
- **Isolated User Storage:** Newly registered accounts receive distinct SQL database records, preventing data crosstalk.

### B. Intelligent Tutor Matching & Scheduling
- **Compatibility Scoring:** Calculates match fit percentage based on subject overlap, student grade level, and tutor learning style parameters.
- **Match Requests:** Requests are submitted to tutors who can accept or decline them directly from their dashboard.
- **Slot Reservation:** Students select available dates and time slots published by approved tutors.

### C. GCash Direct e-Wallet Checkout
- **Transparent Fee Structure:** System calculates Tutor Fee + Platform Commission Fee (e.g., ₱350 Tutor Fee + ₱50 Platform Fee = ₱400 Total).
- **Instant Receipt Generation:** Generates valid GCash transaction reference numbers (`GC-XXXXXXXX`) stored in `payments` and `sessions` tables.
- **SSL Trust Integration:** Checkout modal features security trust badges ("256-bit SSL Encrypted Direct Checkout").

### D. Live Virtual Workspace & Session Completion
- **Collaborative Session Space:** Embedded canvas whiteboard/drawing tools and instant messaging (`chat_messages`).
- **Controlled Completion:** Tutors finalize sessions within the Live Workspace where they record post-session notes/remarks.
- **Student Reviews:** Students provide 1-5 star ratings and written feedback upon session completion, automatically updating the tutor's aggregate rating in the database.

### E. Admin Back-Office Dashboard (`admin.php`)
- **Apex-Style Interface:** Features a dark left sidebar (`#0f172a`), top breadcrumbs header, and single-canvas tab routing (`switchMainAdminTab`).
- **Interactive Metric Morphing:** Dynamic chart pill buttons (`Revenue`, `Orders`, `Profit`) that smoothly morph SVG analytical vector line paths.
- **System Administration:** Admin controls for tutor verification, user deactivation/reactivation, broadcast system announcements, and payout management.

---

## 4. Strengths & Architectural Advantages

1. **High Portability:** Runs seamlessly on standard XAMPP (Apache + MySQL) as well as standalone PHP CLI server environments using SQLite fallback.
2. **Clean UI/UX Design System:** Custom responsive CSS layout avoiding heavy Javascript dependencies or slow external UI frameworks.
3. **Data Integrity:** Explicit foreign key identifiers (`STU-XXX`, `TUT-XXX`, `ADM-XXX`, `SES-XXX`) maintaining clean audit trails across transactions.
4. **Targeted Role Notifications:** System events trigger alerts filtered strictly by relevant role targets.

---

## 5. Security & Edge Case Evaluation

| Component | Assessment | Recommendation |
| :--- | :--- | :--- |
| **Password Storage** | **Secure:** BCRYPT hashing is enforced for all account creation and authentication calls. | Maintain current BCRYPT cost factor. |
| **SQL Injection** | **Secure:** All API endpoints utilize PHP PDO prepared statements with parameter binding. | Continue strict usage of PDO bound variables. |
| **Session Persistence** | **Functional:** Utilizes `localStorage` for client-side state maintenance across reloads. | For enterprise production, issue HTTP-only JWTs or PHP native server sessions. |
| **CORS Policy** | **Permissive:** `Access-Control-Allow-Origin: *` in `config.php`. | Restrict CORS origin header to specific domain names in production deployments. |

---

## 6. Verification & Quality Assurance

The system underwent automated Playwright testing across browser viewports, verifying:
- Authentication & registration pipelines for Students and Tutors.
- Admin dashboard tab navigation, dynamic chart toggles, and notification feeds.
- Live workspace chat and drawing board operations.
- GCash payment modal checkout flow and receipt generation.

---

## Conclusion
The TutorLink codebase provides a clean, functional web application for managing tutor-student sessions with rule-based matching recommendations and simulated GCash checkout workflows.
