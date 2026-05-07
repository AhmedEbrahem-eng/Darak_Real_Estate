## 2026-05-07 - Broken Access Control in Tour Management
**Vulnerability:** Publicly accessible endpoints for approving and declining tour requests without authentication or ownership verification.
**Learning:** Routes defined outside of middleware-protected groups can inadvertently expose sensitive operations to unauthenticated users, leading to unauthorized state changes.
**Prevention:** Always group sensitive API routes within authentication and authorization middleware. Implement explicit ownership checks in controllers or policies to ensure users can only modify resources they own or have permission to manage.
