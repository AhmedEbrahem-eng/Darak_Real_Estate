## 2026-08-02 - Image Deletion Missing Authentication and IDOR Authorization Check
**Vulnerability:** The property image deletion endpoint `DELETE /api/images/{imageId}` was publicly accessible with no authentication middleware (`auth:sanctum`, `checkTokenExpiry`) and lacked authorization checks, allowing any user to delete any image in the system.
**Learning:** Controller actions deleting related resources (like child images belonging to a property) must verify ownership of the parent entity (`$image->property->user_id === $user->id`) or an administrative role before performing the deletion.
**Prevention:** Always pair mutation routes with appropriate authentication middlewares and check entity ownership in the controller or a policy before invoking deletion in the repository.
