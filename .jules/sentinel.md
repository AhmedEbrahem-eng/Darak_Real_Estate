# Sentinel's Journal

## 2025-05-20 - Missing Authorization on Administrative Report Endpoints
**Vulnerability:** Administrative user and property report routes (`/api/report-users` and `/api/report-properties`) were unauthenticated and lacked admin role verification, allowing any guest user to view report lists or delete reports, landlords, and properties.
**Learning:** Routes defined in `routes/api.php` outside of middleware groups do not automatically inherit authentication or role authorization middleware.
**Prevention:** Group all administrative report routes under `['auth:sanctum', 'checkTokenExpiry', 'admin']` middleware groups and enforce explicit security testing for all API endpoints.
