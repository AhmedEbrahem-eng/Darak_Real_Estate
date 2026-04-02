## 2024-05-22 - [Role Escalation & Broken Access Control]
**Vulnerability:** User registration allowed self-assignment of the 'admin' role, and multiple administrative endpoints were completely unprotected.
**Learning:** Overly permissive registration validation combined with unauthenticated/unauthorized route definitions created a critical security gap where any user could gain administrative privileges or access sensitive management functions.
**Prevention:** Strictly whitelist roles in registration validation and ensure all administrative routes are explicitly grouped under authentication and authorization middleware.
