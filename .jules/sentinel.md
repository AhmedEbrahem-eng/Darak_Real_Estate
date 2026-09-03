## 2026-03-05 - [User Directory Route Protection]
**Vulnerability:** The `GET /api/users` endpoint allowed any authenticated regular user to retrieve a full listing of registered users in the database, exposing personal user details.
**Learning:** Route groups that use `auth:sanctum` without role authorization middleware can inadvertently expose sensitive administrative resources to non-admin users.
**Prevention:** Always explicitly attach administrative authorization middleware (`admin`) to administrative listing/management endpoints and test all access control levels (unauthenticated, non-admin, admin).
