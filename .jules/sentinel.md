## 2025-05-16 - Broken Access Control in Tour Management
**Vulnerability:** Publicly accessible endpoints for tour approval/declination (`/api/tours/{id}/approve` and `/api/tours/{id}/decline`) lacked authentication and ownership checks.
**Learning:** Sensitive mutation endpoints were accidentally defined outside the `auth:sanctum` middleware group, and the controller lacked logic to verify if the requester was the actual landlord of the property.
**Prevention:** Always wrap sensitive state-changing routes in authentication middleware and implement explicit ownership verification in controllers or policies before calling repository methods.
