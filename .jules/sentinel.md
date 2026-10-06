## 2025-02-17 - Unprotected Property Deletion Endpoint
**Vulnerability:** The property deletion route `DELETE /api/properties/{id}` lacked authentication middleware (`auth:sanctum`) and authorization checks, allowing unauthenticated and non-owner users to delete arbitrary property records.
**Learning:** API routes performing state-modifying operations (such as deletion) must be explicitly protected with authentication middleware and enforce authorization checks in the controller against record ownership (`user_id`) or administrative roles (`admin`).
**Prevention:** Ensure all deletion and modification routes in `routes/api.php` specify `auth:sanctum` and `checkTokenExpiry` middleware, and enforce ownership or role checks before calling repository deletion methods.
