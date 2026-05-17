## 2025-05-17 - IDOR in Property Deletion
**Vulnerability:** The `DELETE /api/properties/{id}` endpoint was publicly accessible and lacked authorization checks, allowing any user (or even unauthenticated visitors) to delete any property record by its ID.
**Learning:** Routes defined outside of middleware groups must be explicitly protected. Repository methods that perform deletions should ideally accept model instances already verified for existence and ownership to prevent redundant queries and ensure authorization is handled at the controller level.
**Prevention:** Always wrap sensitive state-changing routes (POST, PUT, DELETE) in `auth:sanctum` middleware and implement explicit ownership or role-based checks before performing the action.
