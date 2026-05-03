## 2026-05-03 - [IDOR in Tour Deletion and Unsecured Admin Routes]
**Vulnerability:** IDOR in `TourController::deleteTour` allowed any authenticated user to delete any tour. Additionally, tour approval and decline routes were public and unauthenticated.
**Learning:** Repositories providing direct access by ID without authorization checks are prone to IDOR. Administrative actions like approving tours must be explicitly protected by role-based middleware.
**Prevention:** Implement authorization checks in controllers to verify user ownership or administrative privileges before performing destructive or state-changing operations. Always group sensitive routes under authentication and authorization middleware.
