## 2026-03-01 - [Insecure Property Image Deletion Endpoint]
**Vulnerability:** The image deletion endpoint `images/{imageId}` was exposed publicly to unauthenticated users with no ownership verification, allowing any visitor to delete any property image.
**Learning:** Endpoints that handle asset deletion or state changes must always be guarded with standard authentication middleware and clear ownership/authorization checks at the controller/repository level.
**Prevention:** Always pair `auth:sanctum` and `checkTokenExpiry` middlewares for secure routes, and explicitly compare the authenticated user ID (`Auth::id()`) with the owner ID of the parent resource before performing write or delete operations.
