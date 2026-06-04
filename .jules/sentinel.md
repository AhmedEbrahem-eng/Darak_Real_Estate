## 2024-06-04 - [BOLA in Tour Management]
**Vulnerability:** Tour approval and decline endpoints were publicly accessible and lacked ownership verification.
**Learning:** Functional endpoints were added to `api.php` without middleware protection or authorization logic, assuming only valid requests would reach them.
**Prevention:** Use middleware groups for all non-public routes and always verify that the authenticated user owns the resource being modified.
