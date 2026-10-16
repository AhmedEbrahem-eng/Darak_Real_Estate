# Sentinel's Journal

## 2026-03-31 - Unprotected Admin Overview Dashboard Route
**Vulnerability:** The `/api/dashboard/home` route group in `Backend/routes/api.php` was missing authentication and role middleware, allowing unauthenticated public requests to query system-wide analytics and entity counts.
**Learning:** Admin endpoints placed outside protected middleware blocks in `routes/api.php` expose sensitive system statistics.
**Prevention:** Always protect administrative route groups with `['auth:sanctum', 'checkTokenExpiry', 'admin']` middleware.
