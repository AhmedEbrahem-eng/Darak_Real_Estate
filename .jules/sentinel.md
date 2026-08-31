## 2026-08-31 - [IDOR in Notification Deletion]
**Vulnerability:** Notification deletion endpoint (`DELETE /api/notifications/{id}`) allowed any authenticated user to delete notifications belonging to other users.
**Learning:** `NotificationRepository::deleteNotification` performed direct deletion by ID without checking whether the authenticated user was the recipient (`to_user_id`), sender (`from_user_id`), or an administrator.
**Prevention:** Always verify resource ownership or administrative rights at the controller level before delegating deletion to repository methods.
