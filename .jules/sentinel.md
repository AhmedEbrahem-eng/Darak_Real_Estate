# Sentinel Security Journal

## 2024-10-21 - Unprotected API Resource Routes
**Vulnerability:** `Route::apiResource()` registers store, update, and destroy routes alongside read endpoints without applying authentication or authorization middleware, exposing CRUD operations publicly.
**Learning:** Default resource controllers in Laravel route files do not inherit middleware unless explicitly wrapped in middleware route groups or specified in controller constructors.
**Prevention:** Explicitly separate public read endpoints from administrative mutation routes in `routes/api.php` or define middleware inside the controller constructor.
