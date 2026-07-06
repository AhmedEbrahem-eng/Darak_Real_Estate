## 2024-05-23 - [Insecure Direct Object Reference in Image Deletion]
**Vulnerability:** The `DELETE /api/images/{imageId}` endpoint lacked any authentication or authorization checks, allowing any user (including guests) to delete any image from the database and filesystem by providing its ID.
**Learning:** Repositories used for deletion should provide a way to find the object first so the controller can perform authorization checks against its relationships (e.g., the owner of the property the image belongs to).
**Prevention:** Always wrap destructive endpoints with `auth:sanctum` and implement explicit ownership checks in the controller before calling repository delete methods.
