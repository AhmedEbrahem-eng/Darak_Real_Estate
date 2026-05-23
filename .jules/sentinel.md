## 2026-05-23 - [IDOR and Broken Access Control Pattern]
**Vulnerability:** Found widespread missing ownership checks (IDOR) and unprotected administrative routes.
**Learning:** The application relied on 'auth:sanctum' for authentication but lacked granular authorization. Sensitive actions like deleting properties or approving tours didn't verify if the authenticated user owned the resource.
**Prevention:** Always implement authorization checks (e.g., using Laravel Policies or manual ownership verification) in addition to authentication. Group administrative routes under 'admin' middleware and strictly validate roles during registration.
