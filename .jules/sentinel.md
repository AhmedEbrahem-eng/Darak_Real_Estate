## 2025-02-15 - [Registration Privilege Escalation]
**Vulnerability:** Registration request accepted the 'admin' role directly from the request payload, enabling any self-registering user to escalate privileges to a super-administrator.
**Learning:** Hardcoding a list of roles inside validation rules (e.g., `in:admin,user,landlord`) without separating public self-registration from internal administrative user management exposes the system to privilege escalation.
**Prevention:** Restrict self-registration routes/validation to non-administrative roles only (e.g., `in:user,landlord`), and require a separate secured administrative interface or database seeders to provision administrator accounts.
