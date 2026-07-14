# Sentinel's Journal 🛡️

## 2024-05-15 - [Privilege Escalation in User Registration]
**Vulnerability:** The registration endpoint allowed users to specify their own role, including 'admin', through the `role` parameter in the request.
**Learning:** Even if a request is validated using a FormRequest, we must ensure that sensitive fields like `role` have strict whitelists that exclude administrative privileges for public-facing endpoints.
**Prevention:** Remove 'admin' from the allowed roles in the registration validation rules and handle administrative user creation through a separate, secure process (e.g., seeding or a protected admin dashboard).
