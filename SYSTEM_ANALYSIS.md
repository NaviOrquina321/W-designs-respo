# TutorLink System Architecture & Technical Analysis Report

## Executive Summary
**TutorLink** is a full-stack tutor-student matching and learning management web application built using PHP REST APIs and relational database persistence (supporting MySQL with zero-config SQLite fallback).

The platform facilitates AI and rule-based tutor matching recommendations (`api/match.php`), calendar booking scheduling with 24-hour reschedule enforcement, simulated GCash e-Wallet payments with platform service fee calculations (`api/payment_provider.php`), live virtual workspace collaboration with embedded chat and notes, post-session evaluations, and admin back-office management (`admin-login.php` -> `admin.php`).

---

## 1. System Architecture Overview

```
+-----------------------------------------------------------------------------------+
|                                  FRONTEND LAYER                                   |
|  +-------------------------------------+  +------------------------------------+  |
|  | index.php / app.js                  |  | admin-login.php -> admin.php       |  |
|  | - Student & Tutor Web Portals       |  | - Back-Office Administrative Portal|  |
|  | - Interactive Drawers & Workspace   |  | - Single-Canvas Tab Navigation     |  |
|  | - Demo GCash Checkout               |  | - Reports & CSV Export             |  |
|  +-------------------------------------+  +------------------------------------+  |
|                                    |                                              |
|                           HTTP REST Requests (JSON)                               |
|                                    v                                              |
+-----------------------------------------------------------------------------------+
|                                 BACKEND API LAYER                                 |
|  +-----------------------------------------------------------------------------+  |
|  | PHP PDO REST Endpoints (/api/*.php)                                         |  |
|  |  - login.php & register.php   (Authentication & Dual Session Cookies)       |  |
|  |  - match.php & tutors.php     (AI Matching Engine & Tutor Profiles)          |  |
|  |  - payment_provider.php       (Auto-confirm Demo GCash Checkout)             |  |
|  |  - sessions.php & chat.php    (Session Management & Workspace Messaging)     |  |
|  |  - notifications.php          (5s Polling & 24h Session Reminders)           |  |
|  |  - reports.php                (Filtered Analytics & Formula-safe CSV Export) |  |
|  +-----------------------------------------------------------------------------+  |
|                                    |                                              |
|                         Database Abstraction (PDO Driver)                         |
|                                    v                                              |
+-----------------------------------------------------------------------------------+
|                                 PERSISTENCE LAYER                                 |
|  +------------------------------------+   +------------------------------------+  |
|  | Primary: MySQL (tutorlink_db)      |   | Fallback: SQLite (tutorlink.db)    |  |
|  | - One-time schema_version Migration |   | - File-based Portable Storage      |  |
|  +------------------------------------+   +------------------------------------+  |
+-----------------------------------------------------------------------------------+
```

### Key Architectural Highlights:
1. **Decoupled REST API Architecture:** Frontend UI components communicate asynchronously with PHP endpoints via `fetch()` API calls.
2. **Dual Session Cookie Security:** `TUTORLINK_MAIN` manages student/tutor sessions on `index.php`, while `TUTORLINK_ADMIN` strictly manages administrator sessions on `admin.php`.
3. **AI & Rule-Based Fallback Matching:** `api/match.php` executes cURL requests to AI completions API (with 10-second timeout, 10-min session cache, and sanitization). On missing key or failure, it seamlessly falls back to server-side rule-based matching with neutral 3.5 rating for unrated tutors.
4. **Auto-Confirming Payment Provider:** `api/payment_provider.php` creates session and payment records in a single transaction, auto-confirming payments for immediate workspace access during scheduled session windows.

---

## 2. Relational Database Schema & Data Models

The database consists of 11 relational tables managed via `schema_version`.

| Table Name | Primary Key | Description & Key Columns |
| :--- | :--- | :--- |
| `schema_version` | `version` (INT) | Migration version tracker executing database DDL scripts only once. |
| `users` | `id` (Auto-Inc) | Central authentication repository storing `user_id`, `email`, `password_hash` (BCRYPT), `role`, and `name`. |
| `students` | `id` (VARCHAR) | Student profile data including `grade`, `validated`, `deactivated`, `subjects_needed`, and `sessions_completed`. |
| `tutors` | `id` (VARCHAR) | Tutor profiles: `rating`, `hourly_rate`, `subjects`, `learning_styles`, `diploma_status`, `tor_status`, `id_status`, `approval_status`, and availability slots. |
| `admins` | `id` (VARCHAR) | Administrative staff accounts. |
| `subjects` | `id` (VARCHAR) | Academic subject catalog categorized by STEM, Humanities, and Technology. |
| `matches` | `id` (VARCHAR) | Matching requests generated between students and tutors with match compatibility `score` and `status`. |
| `schedules` | `id` (VARCHAR) | Individual tutor time slots and calendar availability. |
| `payments` | `id` (VARCHAR) | Financial records detailing GCash transactions, transaction reference numbers (`ref_no`), amounts, and payout status. |
| `sessions` | `id` (VARCHAR) | Confirmed tutoring appointments tracking hourly rate, commission fee, total paid, status, notes, rating, and student feedback. |
| `notifications` | `id` (VARCHAR) | In-app alerts routed by `target_role` (`student`, `tutor`, `admin`, or `all`). |
| `chat_messages` | `id` (Auto-Inc) | Live virtual workspace chat messages linked to specific `session_id` records. |

---

## Conclusion
The TutorLink codebase provides a complete web system for managing tutor-student matching, calendar scheduling, demo GCash payments, live session workspaces, and back-office administration.
