## 2024-05-24 - [Insecure Admin Registration and Broken Access Control]
**Vulnerability:** Found that the registration endpoint allowed users to assign themselves the 'admin' role, and several administrative routes (amenities, reports, dashboard) were exposed without proper role-based middleware.
**Learning:** Default validation rules and route groups often miss role-specific restrictions, leading to privilege escalation and unauthorized data access.
**Prevention:** Strictly whitelist roles in registration requests and ensure all administrative routes are protected by an 'admin' middleware in addition to authentication.
