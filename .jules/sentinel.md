## 2024-06-13 - Secure Error Handling in AuthController
**Vulnerability:** Information leakage through exception messages in API responses.
**Learning:** Returning `$th->getMessage()` directly to the client can expose database structure, table names, and other sensitive internal details when an unexpected error occurs.
**Prevention:** Always log the full exception object for internal debugging and return a generic, non-descriptive error message to the client in production-ready code.

## 2024-06-13 - Privilege Escalation in User Registration
**Vulnerability:** Users could self-assign the 'admin' role during registration.
**Learning:** The `RegisterUserRequest` validation rules included 'admin' in the allowed values for the `role` field, which was then directly used in `User::create()`.
**Prevention:** Explicitly exclude administrative roles from public registration validation rules. Use separate processes or guarded attributes for assigning high-privilege roles.
