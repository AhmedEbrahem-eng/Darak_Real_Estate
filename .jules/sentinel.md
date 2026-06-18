## 2024-05-22 - [Privilege Escalation & IDOR Fixes]
**Vulnerability:** Privilege Escalation via `role` field in registration and IDOR in property deletion.
**Learning:** Default validation rules in `RegisterUserRequest` included 'admin' in the allowed roles, enabling any user to register as an administrator. Additionally, the property deletion route was completely public and lacked ownership checks.
**Prevention:** Always restrict role assignments in public registration endpoints. Ensure all destructive actions (DELETE/PUT) are protected by authentication and implement strict ownership or role-based authorization checks at the controller/repository level.
