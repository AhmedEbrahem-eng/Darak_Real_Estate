## 2024-09-24 - Unprotected Admin Endpoints in Report Management
**Vulnerability:** Report management routes (`report-users` and `report-properties`) were defined outside middleware groups, exposing user deletion (`deleteLandlord`) and property deletion (`deleteProperty`) endpoints to unauthenticated users.
**Learning:** Controller methods in route files without explicit middleware inherit public access by default.
**Prevention:** Group all administrative actions under `['auth:sanctum', 'checkTokenExpiry', 'admin']` middleware array in `routes/api.php` and enforce automated security tests verifying 401/403 responses.
