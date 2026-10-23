## 2024-10-23 - Prevent Self-Registration Privilege Escalation
**Vulnerability:** Public user registration endpoint allowed specifying `role: admin` in `RegisterUserRequest`, granting unauthenticated callers administrative privileges.
**Learning:** `RegisterUserRequest` allowed `in:admin,user,landlord` without role authorization checks.
**Prevention:** Restrict public registration `role` validation rules to `in:user,landlord` and seed initial admin accounts via database seeders.
