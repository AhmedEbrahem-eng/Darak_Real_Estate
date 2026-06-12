## 2026-06-12 - Generic Error Messages and Role Restriction
**Vulnerability:** Information leakage via exception messages in API responses and Privilege Escalation via self-assigned 'admin' role during registration.
**Learning:** Returning `$th->getMessage()` in a production environment can expose sensitive database structure or stack traces. Additionally, allowing 'admin' in the registration role whitelist permits unauthorized privilege escalation.
**Prevention:** Always use generic error messages for end-users and log detailed exceptions for internal debugging. Restrict administrative role assignment to secure, non-public channels.
