## 2025-01-24 - Privilege Escalation in Registration
**Vulnerability:** The public user registration endpoint allowed users to specify the 'admin' role, granting them full administrative access to the application.
**Learning:** Input validation rules must explicitly whitelist only the intended roles for public-facing endpoints. Relying on default behavior or an overly permissive `in:` rule can lead to privilege escalation.
**Prevention:** Strictly validate the 'role' field in registration requests and ensure 'admin' is never an option for public registration. Use middleware to protect administrative routes.

## 2025-01-24 - Information Disclosure via Exception Messages
**Vulnerability:** API endpoints returned raw exception messages in 500 error responses, potentially leaking stack traces, database structure, and other sensitive system details.
**Learning:** Raw exceptions should never be returned to the client in production. They provide a roadmap for attackers to understand the system's internals.
**Prevention:** Implement global or local `try-catch` blocks that log the detailed exception internally for developers and return a generic, non-informative error message to the user.
