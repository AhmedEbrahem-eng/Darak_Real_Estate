## 2026-04-23 - [Insecure Direct Object Reference (IDOR) in Review Updates/Deletions]
**Vulnerability:** Authenticated users could update or delete any review by simply providing the review's ID in the API request.
**Learning:** Routes were missing 'auth' middleware, and the repository did not verify that the authenticated user owned the review before performing the action.
**Prevention:** Always use route middleware for authentication and implement explicit ownership checks in the application logic (repository or policy) for resource modification/deletion.
