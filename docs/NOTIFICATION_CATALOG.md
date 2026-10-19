# TutorLink Notification System Event Catalog

This document provides a comprehensive mapping of all system-generated and admin-dispatched notifications within TutorLink.

## Notification Catalog Table

| Event / Action Name | Trigger Source File | Target Recipient Role | Target Recipient ID | Priority | Title Template | Message Template / Description | Action URL (`action_url`) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **User Registration Alert** | `api/register.php` | `admin` | `ADMIN-001` | Normal | New Student / Tutor Registered | Alert to admin containing registered user's full name, role, and email. | `#students` or `#tutors` |
| **New Tutor Match Request** | `api/match.php` | `tutor` | `{tutor_id}` | Normal | New Tutor Match Request | `{student_name} matched with you for {subject}.` | `#matches` |
| **Session Booking Confirmed** | `api/payment_provider.php` | `student` | `{student_id}` | High | Session Confirmed | `Your session with {tutor_name} on {date} at {slot} is confirmed.` | `#workspace-{session_id}` |
| **Session Booking Notice** | `api/payment_provider.php` | `tutor` | `{tutor_id}` | High | New Booking Received | `You have a new session with {student_name} on {date} at {slot}.` | `#workspace-{session_id}` |
| **24-Hour Session Reminder** | `cron.php` / `api/notifications.php` | `student` / `tutor` | `{user_id}` | High | Upcoming Session Reminder (24 Hours) | `Reminder: Your {subject} session with {other_name} is tomorrow on {date} at {slot}.` | `#workspace-{session_id}` |
| **1-Hour Session Reminder** | `cron.php` / `api/notifications.php` | `student` / `tutor` | `{user_id}` | High | Session Starting Soon (1 Hour) | `Urgent: Your {subject} session with {other_name} starts in less than an hour at {slot}.` | `#workspace-{session_id}` |
| **Chat Message Received** | `api/chat.php` | `student` / `tutor` | `{recipient_id}` | Normal | New Message from {sender_name} | `{sender_name}: "{message_snippet}"` | `#workspace-{session_id}` |
| **Session Started Live** | `api/sessions.php` | `student` | `{student_id}` | High | Session Live | `Your tutor started the session, join now.` | `#workspace-{session_id}` |
| **Session Completed** | `api/sessions.php` | `student` | `{student_id}` | Normal | Session Completed | `Your session has been completed by the tutor.` | `#workspace-{session_id}` |
| **Session Rescheduled** | `api/sessions.php` | `student` / `tutor` | `{recipient_id}` | High | Session Rescheduled | `Session with {name} was rescheduled to {new_date} at {new_slot}.` | `#workspace-{session_id}` |
| **Session Cancelled** | `api/sessions.php` | `student` & `tutor` | `{student_id}` & `{tutor_id}` | High | Session Cancelled | `Your session on {date} was cancelled by Admin.` | `#sessions` |
| **Session Rating Received** | `api/sessions.php` | `tutor` | `{tutor_id}` | Normal | New {rating}-Star Rating Received | `{student_name} rated your {subject} session {rating} star(s).` | `#session-{session_id}` |
| **Admin System Broadcast** | `api/notifications.php` / `api/notify.php` | `all` / `student` / `tutor` | `All Users` | Normal | `{broadcast_title}` | `{broadcast_message}` | N/A |

---

## Technical Architecture Overview

1. **Database Schema Version 2:**
   - Table: `notifications`
   - Fields: `id`, `recipient_user_id`, `recipient_role`, `target_role`, `target`, `type`, `title`, `message`, `related_session_id`, `related_payment_id`, `related_user_id`, `action_url`, `priority`, `is_read`, `read_at`, `dedupe_key` (UNIQUE), `created_at`.
   - Table: `notification_reads`
   - Fields: `(notification_id, user_id)` composite primary key for tracking broadcast read states per user.

2. **Real-Time Delivery via SSE:**
   - Endpoint: `api/notifications_stream.php` (`text/event-stream`).
   - Automatically polls DB for user-targeted unread notifications, sending periodic JSON payloads (`event: notification`) to connected clients (`app.js` and `admin.php`).
   - Session lock is immediately closed (`session_write_close()`) to allow concurrent non-blocking HTTP requests.

3. **Background Reminder Engine:**
   - Script: `cron.php` (CLI or Admin web execution).
   - Scans active sessions occurring within the next 24 hours or 1 hour, constructing deduplication keys (`rem-{session_id}-{role}-{type}`) to prevent duplicate notifications.
