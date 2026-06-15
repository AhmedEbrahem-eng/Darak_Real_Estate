## 2024-06-15 - Privilege Escalation in Registration
**Vulnerability:** The registration endpoint allowed users to specify an 'admin' role, granting themselves full administrative privileges.
**Learning:** Whitelisting 'admin' in a public registration request validation is a critical security flaw.
**Prevention:** Always restrict role assignments in public endpoints. Administrative roles should only be assigned by existing admins or through secure, internal processes.

## 2024-06-15 - Information Leakage in Auth Controllers
**Vulnerability:** Catch blocks in authentication-related controllers were returning `$th->getMessage()`, potentially exposing database details and stack traces to clients.
**Learning:** Generic error messages are essential for public-facing APIs to prevent information disclosure.
**Prevention:** Log detailed exceptions internally and return sanitized, generic error messages to the user.
