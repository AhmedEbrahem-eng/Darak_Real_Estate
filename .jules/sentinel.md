## 2026-04-07 - [Privilege Escalation via Registration]
**Vulnerability:** The `RegisterUserRequest` validation rules explicitly whitelisted the `admin` role, allowing any user to register with full administrative privileges.
**Learning:** Whitelisting is good, but including high-privilege roles in a public-facing registration whitelist defeats the purpose of role-based access control.
**Prevention:** High-privilege roles should only be assignable via a protected administrative interface or manual database intervention.

## 2026-04-07 - [Information Leakage in API Responses]
**Vulnerability:** API controllers were returning raw exception messages (`$th->getMessage()`) in catch blocks, potentially exposing database schema details and internal logic.
**Learning:** Raw exception messages are invaluable for developers but dangerous for users.
**Prevention:** Always log the detailed error internally and return a generic, non-descriptive message to the client.

## 2026-04-07 - [Broken Access Control on Admin Routes]
**Vulnerability:** Multiple administrative endpoints (users list, report management, dashboard) were accessible to any authenticated user or even guests because they lacked the `admin` middleware.
**Learning:** Routes are insecure by default unless explicitly protected by appropriate middleware.
**Prevention:** Group related administrative routes and apply a strict `admin` middleware. Conduct periodic audits of `routes/api.php`.
