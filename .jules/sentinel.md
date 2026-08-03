# Sentinel Security Journal

## 2024-11-20 - Unauthenticated & Unauthorized Property Deletion
**Vulnerability:** The property deletion route (`DELETE /api/properties/{id}`) lacked any authentication or authorization middlewares/checks, allowing guest users to delete arbitrary properties.
**Learning:** Security controls must be consistently applied both at the route level (using authentication middleware like `auth:sanctum` and `checkTokenExpiry`) and at the controller level (performing ownership or administrative role verification).
**Prevention:** Always verify incoming request credentials and enforce authorization checks against model ownership before invoking repository-level state changes.
