## 2024-05-22 - [Broken Access Control on Admin Routes]
**Vulnerability:** Several administrative routes (dashboard overview, user listing, report management, amenity/property-type modification) were publicly accessible or only required authentication without checking for the 'admin' role.
**Learning:** Grouping routes by functional prefix (e.g., `dashboard/`, `amenities/`) without applying a shared security middleware layer can lead to accidental exposure of sensitive data and management functions.
**Prevention:** Always wrap administrative and sensitive management routes in a dedicated `admin` middleware group. Use `withoutMiddleware()` sparingly to allow specific, lower-privileged actions within those groups if necessary.
