## 2024-05-23 - [Privilege Escalation in User Registration]
**Vulnerability:** The user registration endpoint allowed new users to register with the 'admin' role by passing `role=admin` in the request.
**Learning:** Publicly accessible registration forms should never allow users to self-assign sensitive roles. In this application, the `RegisterUserRequest` validation rule was too permissive.
**Prevention:** Explicitly whitelist only non-privileged roles (e.g., 'user', 'landlord') in the registration request validation and handle administrative account creation through a separate, protected process.
