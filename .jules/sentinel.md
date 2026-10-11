## 2025-05-19 - Administrative Report Endpoints Missing Middleware
**Vulnerability:** Unauthenticated users could access administrative report endpoints (`/api/report-users`, `/api/report-properties`, `/api/reason-report`), allowing deletion of landlord user accounts, properties, and reports without authentication or authorization.
**Learning:** Routes defined in `routes/api.php` lacked outer or inner middleware groups enforcing Sanctum authentication and admin role verification (`admin`).
**Prevention:** Explicitly group administrative endpoints under `['auth:sanctum', 'checkTokenExpiry', 'admin']` middleware in `routes/api.php` and verify endpoint security with feature tests.
