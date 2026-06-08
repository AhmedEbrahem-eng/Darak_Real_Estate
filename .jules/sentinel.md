## 2025-05-15 - Privilege Escalation via Self-Registration

**Vulnerability:** The `RegisterUserRequest` validation rules included `admin` in the allowed `role` whitelist, allowing any user to register with full administrative privileges by passing `role: admin` in the request body.

**Learning:** This existed because the application used a common registration endpoint for all roles without server-side enforcement of which roles are "self-assignable". While the frontend might have restricted the choice, the API remained wide open to manipulation.

**Prevention:** Never allow sensitive roles (like `admin`) to be assigned via public registration endpoints. Roles should be either hardcoded to a default (e.g., `user`) or validated against a strict, safe whitelist. Administrative roles should only be assigned by existing administrators through a separate, protected management interface.

## 2025-05-15 - Group Route Security and Selective Exemption

**Vulnerability:** Several administrative routes (reports, dashboard, amenities) were completely unprotected, allowing any user (or unauthenticated visitor) to view and delete sensitive data.

**Learning:** When applying security middleware to route groups (e.g., `admin`), some endpoints within that group may still require regular user access (e.g., users should be able to *create* a report, but not *view* or *delete* them).

**Prevention:** Use Laravel's `withoutMiddleware()` method to selectively lower security requirements for specific routes within a protected group. This allows for cleaner route organization while maintaining "Defense in Depth" by ensuring the most restrictive middleware is the default for the group. Always verify that unauthenticated and unauthorized requests are correctly rejected with 401 or 403 status codes.
