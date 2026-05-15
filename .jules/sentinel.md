## 2025-01-24 - IDOR in Review Management
**Vulnerability:** Insecure Direct Object Reference (IDOR) allowed any user to update or delete reviews belonging to others. Routes were also missing authentication middleware.
**Learning:** Even when using repositories for data access, authorization checks must be explicitly performed at the controller level or via Policies to ensure the authenticated user has permission to modify the specific resource instance.
**Prevention:** Always wrap sensitive mutation routes with authentication middleware and implement instance-level authorization checks (e.g., `$review->user_id === auth()->id()`).
