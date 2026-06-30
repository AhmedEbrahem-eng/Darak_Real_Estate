## 2024-05-22 - [HIGH] Unprotected Administrative Routes
**Vulnerability:** Several administrative routes (report management, dashboard, amenities/property-types modification) were exposed without proper authorization checks, allowing guests or regular users to perform sensitive actions.
**Learning:** In Laravel, using `apiResource` or `prefix` without wrapping them in middleware groups can lead to accidentally exposed endpoints if security is not applied at the route level.
**Prevention:** Always group administrative routes under a dedicated middleware group (e.g., `['auth:sanctum', 'admin']`) and verify protections with feature tests.
