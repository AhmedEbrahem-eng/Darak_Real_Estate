## 2026-05-04 - [Critical] Privilege Escalation in Registration
**Vulnerability:** The registration endpoint allowed users to specify an 'admin' role, granting them full administrative access.
**Learning:** Input validation whitelists must be carefully reviewed to ensure sensitive roles cannot be self-assigned.
**Prevention:** Explicitly exclude administrative roles from public registration validation rules.
## 2026-05-04 - [High] Information Leakage in Auth Responses
**Vulnerability:** AuthController returned raw exception messages in 500 error responses.
**Learning:** Returning internal error details can expose system architecture, environment variables, or database structures.
**Prevention:** Catch all exceptions at the controller level, log them for internal review, and return generic, non-descriptive error messages to the client.
