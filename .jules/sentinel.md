## 2024-05-15 - [Privilege Escalation via Role Selection]
**Vulnerability:** The registration endpoint allowed users to specify any role, including 'admin', during registration.
**Learning:** Whitelisting roles in validation without excluding sensitive ones like 'admin' allows any user to gain full administrative access.
**Prevention:** Always exclude administrative roles from public-facing registration or update endpoints. Admin roles should only be assigned through an existing administrative interface or database seeders.
