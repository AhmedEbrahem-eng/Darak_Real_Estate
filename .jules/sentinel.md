## 2024-05-24 - Unauthorized Admin Registration & Broken Access Control
**Vulnerability:** Privilege Escalation & Insecure Direct Object Reference
**Learning:** The 'register' endpoint allowed any user to assign themselves the 'admin' role via the request body. Additionally, several administrative routes (reports, property types, amenities) were missing authentication and authorization middleware.
**Prevention:** Always whitelist allowed roles in both request validation and controller logic. Group all administrative routes under strict authentication and 'admin' role-check middleware.
