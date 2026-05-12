## 2025-05-15 - [BOLA in Review Management]
**Vulnerability:** Broken Object Level Authorization (BOLA) and missing authentication on review update and delete endpoints.
**Learning:** Routes defined in `api.php` without middleware and controllers lacking ownership checks allowed any user (or unauthenticated attacker) to modify or delete any review by ID.
**Prevention:** Always protect state-changing routes with authentication middleware and implement explicit ownership or role-based access control (RBAC) in controllers before executing repository actions. Added `findReview` to the repository to facilitate these checks.
