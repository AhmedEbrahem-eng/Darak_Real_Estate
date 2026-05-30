## 2024-05-22 - Registration Privilege Escalation
**Vulnerability:** User registration endpoint allowed manual selection of the 'admin' role via the request body.
**Learning:** Whitelisting roles in a `FormRequest` validation rule is insufficient if the whitelist includes highly privileged roles like 'admin' on a public-facing endpoint.
**Prevention:** Remove administrative roles from public registration validation logic. Administrative accounts should be created via seeds, CLI, or an existing admin dashboard.

## 2024-05-22 - Authentication Information Leakage
**Vulnerability:** Raw exception messages were being returned in API error responses during authentication failures.
**Learning:** Returning `$th->getMessage()` can leak sensitive internal details (database structure, file paths).
**Prevention:** Always log the detailed exception and return a generic, non-descriptive error message to the client in production-ready API controllers.
