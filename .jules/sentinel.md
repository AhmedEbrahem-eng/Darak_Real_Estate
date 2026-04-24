## 2025-01-24 - [IDOR in Tour and Review Management]
**Vulnerability:** Insecure Direct Object Reference (IDOR) in tour approval/decline and review management. Any user could approve/decline any tour request, and any authenticated user could update/delete any review.
**Learning:** Security middleware (`auth:sanctum`) only verifies authentication, not authorization. Business logic must explicitly check if the authenticated user has the right to perform the action on the specific resource (e.g., being the landlord of the property or the owner of the review).
**Prevention:** Always implement authorization checks in controllers or repositories. Use the authenticated user's ID (`Auth::id()`) to scope queries or verify ownership before performing mutations.
