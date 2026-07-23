# Sentinel Security Journal 🛡️

## 2024-11-20 - [Missing Property Deletion Authorization]
**Vulnerability:** The property deletion route (`DELETE /api/properties/{id}`) had absolutely no authentication or authorization middleware, allowing anyone (including unauthenticated guests) to delete any property in the database.
**Learning:** High-impact administrative or modifying operations are often added during development without standard routing protection groups. Grouping routes or applying strict default-deny policies on REST resource methods is critical.
**Prevention:** Always group modifying resource routes under authentication and role-based or ownership authorization middleware (e.g. `auth:sanctum` combined with checking `$property->user_id` against `Auth::id()`). Use automated regression tests specifically targeting unauthorized users attempting delete/update/create operations.
