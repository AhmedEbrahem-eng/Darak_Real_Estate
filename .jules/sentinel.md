## 2024-05-24 - [Insecure Default Role Assignment]
**Vulnerability:** User registration endpoint allowed manual 'role' assignment, including 'admin', enabling any user to gain full administrative access.
**Learning:** Whitelisting 'in:' validation rules in Laravel's FormRequest is insufficient if the whitelist includes sensitive roles that should not be self-assigned.
**Prevention:** Strictly exclude administrative roles from public registration validation and use dedicated administration panels or database seeding for admin user creation.

## 2024-05-24 - [Unprotected Administrative API Endpoints]
**Vulnerability:** Multiple administrative API routes (reports, dashboard, amenities) were missing 'admin' middleware, allowing any authenticated user (and in some cases, unauthenticated users) to access and modify sensitive data.
**Learning:** Route groups without explicit middleware application often default to broader access than intended, especially when developers assume a prefix like 'admin' implies protection.
**Prevention:** Always wrap administrative routes in a dedicated middleware group that enforces both authentication and role-based authorization.
