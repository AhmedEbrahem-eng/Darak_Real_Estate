## 2025-05-14 - [Privilege Escalation via Registration]
**Vulnerability:** The registration endpoint allowed users to self-assign the 'admin' role through the 'role' field in the request payload.
**Learning:** Insecure default validation rules in `RegisterUserRequest` included 'admin' in the allowed roles for public registration.
**Prevention:** Strictly whitelist only non-privileged roles (e.g., 'user', 'landlord') in public registration validation. Admin accounts should only be created through a secure administrative interface or database seeding.
