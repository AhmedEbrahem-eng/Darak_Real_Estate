# Sentinel Security Journal 🛡️

## 2024-11-20 - [Admin Privilege Escalation via Public User Registration]
**Vulnerability:** The public user registration form accepted any role name, including `admin`. Anyone could submit a request with `role: admin` and register themselves as a full administrator of the platform.
**Learning:** This vulnerability existed because the request validation rules in `RegisterUserRequest` used an overly permissive list (`in:admin,user,landlord`) without verifying if the registrant was authorized to assume administrative privileges. Furthermore, the `admin.txt` documentation guided users/admins to use the public register endpoint to create the admin account.
**Prevention:** Remove `admin` from the whitelisted values of any self-registration endpoint. Seed any default administrative accounts securely during database migration or system setup, rather than exposing administrative privilege creation on public-facing routes.
