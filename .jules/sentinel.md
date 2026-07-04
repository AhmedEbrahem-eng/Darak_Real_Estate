## 2024-05-22 - [Broken Authorization and IDOR in Reviews]
**Vulnerability:** Administrative routes (reports, dashboard, amenities) were completely exposed without any authentication or authorization middleware. Additionally, reviews could be updated or deleted by any authenticated user (IDOR) due to missing ownership checks in the controller.
**Learning:** Even when a repository exists, controllers must still perform authorization checks before delegating to repository methods that perform mutations. Relying on "security by obscurity" for admin routes is not a defense.
**Prevention:** Always group administrative routes under a restricted middleware group (e.g., `auth:sanctum`, `admin`). For resource modification, implement ownership checks in the controller or use Laravel Policies.
