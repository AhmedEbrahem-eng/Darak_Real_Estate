# Sentinel's Journal - Critical Security Learnings

## 2024-11-20 - Insecure and Unauthenticated Property Image Deletion
**Vulnerability:** The API endpoint for deleting property images (`DELETE /api/images/{imageId}`) was completely exposed without any authentication or authorization middlewares. Any public, unauthenticated user could issue a request to delete any property image in the database.
**Learning:** This occurred because the route was defined outside of the authenticated route groups and did not have any controller-level checks comparing the property owner's ID or administrative status with the current authenticated user's ID.
**Prevention:** Always secure destructive endpoints (like DELETE or PUT) under proper authentication middlewares (`auth:sanctum` and `checkTokenExpiry`), and perform explicit authorization checks at the controller/repository level to ensure only authorized owners or administrators can perform resource modifications.
