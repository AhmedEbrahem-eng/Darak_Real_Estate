## 2024-08-08 - Protect Image Deletion Authorization Gap

**Vulnerability:** The image deletion endpoint `DELETE /api/images/{imageId}` was publicly accessible without any authentication or authorization checks, allowing unauthenticated users to delete property images.
**Learning:** Route-level middleware alone might be missed for single resource endpoints like `DELETE /api/images/{imageId}` if not grouped under authentication wrappers. Furthermore, controller actions must check resource ownership or administrative privilege before invoking deletion logic on underlying repositories.
**Prevention:** Always pair `['auth:sanctum', 'checkTokenExpiry']` middleware on routes modifying or deleting resources and enforce ownership validation (`$image->property->user_id === Auth::id()`) alongside admin overrides in controller handlers.
