## 2024-07-24 - Unprotected Administrative and Amenity Write Endpoints
**Vulnerability:** Anyone (including unauthenticated guests and low-privileged users) could view the administrative overview dashboard (`GET /dashboard/home`) and create, update, modify availability, or delete amenities (`POST /amenities`, `PUT /amenities/*`, `DELETE /amenities/*`).
**Learning:** These routes were completely omitted from any route-level middleware protection in `Backend/routes/api.php`, exposing raw database writes and internal statistics to the public.
**Prevention:** Always group administrative or write endpoints under authorization groups utilizing `auth:sanctum`, `checkTokenExpiry`, and `admin` middleware in Laravel's routing files to guarantee strict access control.
