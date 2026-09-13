## 2025-05-18 - [Prevent Unauthenticated Admin Role Registration]
**Vulnerability:** The public registration request validation rule allowed `role` to be set to `admin`, allowing unauthenticated self-registration as an administrator.
**Learning:** Public registration endpoints must strictly limit role selection to non-administrative roles (`user`, `landlord`).
**Prevention:** Remove `admin` from allowed values in `RegisterUserRequest` validation rules.
