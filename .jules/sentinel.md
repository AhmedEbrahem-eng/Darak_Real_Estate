## 2024-11-20 - [Unprotected Property Deletion Endpoint]
**Vulnerability:** Anyone can delete any property by ID without authentication or authorization.
**Learning:** Route definitions like `Route::delete('/{id}', ...)` were declared in a public prefix block (`properties`) without any middleware or ownership check in the controller.
**Prevention:** Ensure all write operations (POST, PUT, DELETE) are wrapped in `auth:sanctum` and `checkTokenExpiry` middlewares, and always perform model ownership checks (`Auth::id() === $model->user_id` or admin role bypass) in the controller or repository.
