## 2026-07-10 - [Broken Access Control and Admin Route Security]
**Vulnerability:** IDOR in `ReviewController` allowed any authenticated user to update or delete any review. Administrative endpoints for reports, dashboard, and amenities were accessible to non-admin users.
**Learning:** Controller methods that modify resources must always verify ownership or administrative privileges. Route groups should use consistent middleware stacks (`auth:sanctum`, `checkTokenExpiry`, `admin`) to enforce access control.
**Prevention:** Use a standard security pattern in controllers: 1. Retrieve resource, 2. Verify authorization (owner or admin), 3. Perform action. Always group related administrative routes under a common middleware stack.
