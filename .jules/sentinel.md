## 2025-05-14 - Privilege Escalation in Registration
**Vulnerability:** The public registration endpoint allowed users to self-assign the `admin` role by passing it in the request.
**Learning:** Overly permissive validation rules in `FormRequest` classes can lead to privilege escalation if sensitive fields like `role` are included without restriction.
**Prevention:** Always whitelist only the non-privileged roles in public registration validation rules. Admin accounts should be created through a separate, secure process (seeding or administrative dashboard).
