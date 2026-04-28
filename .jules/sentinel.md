## 2026-04-28 - Insecure Direct Object Reference (IDOR) in Tour Management
**Vulnerability:** Tour approval and decline endpoints were exposed publicly and lacked authorization checks, allowing any user to approve or decline any tour request.
**Learning:** Routes defined outside of middleware groups and controllers that don't verify ownership of related models (e.g., property ownership for tour actions) create high-impact IDOR vulnerabilities.
**Prevention:** Always group sensitive routes under authentication middleware and implement strict ownership/authorization checks in controllers or repositories, verifying that the authenticated user has the right to perform the action on the specific resource.
