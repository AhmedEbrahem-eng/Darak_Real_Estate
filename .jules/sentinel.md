## 2024-05-17 - Privilege Escalation via User Registration Role Selection
**Vulnerability:** The registration endpoint accepted a `role` field from the user and directly used it to create the user record, allowing anyone to register as an `admin`.
**Learning:** Sensitive fields like 'role' must be strictly whitelisted and validated to exclude administrative privileges from self-registration processes.
**Prevention:** In `RegisterUserRequest`, the `role` field validation must only include non-administrative roles (e.g., `in:user,landlord`).

## 2024-05-17 - Broken Access Control on Administrative Routes
**Vulnerability:** Several administrative API routes (reports, amenities, dashboard) were accessible without proper authorization checks, allowing unauthenticated or regular users to access sensitive data and management functions.
**Learning:** Defaulting to open routes and forgetting to apply role-based middleware leads to significant data exposure and system manipulation risks.
**Prevention:** Group sensitive administrative routes and explicitly apply `auth:sanctum` and `admin` middleware. Maintain a dedicated `SecurityTest` suite to verify role-based access control.
