## 2025-05-15 - [Privilege Escalation & Information Leakage]
**Vulnerability:** User registration allowed self-assignment of the 'admin' role, and authentication endpoints leaked internal error details.
**Learning:** Whitelist-based validation for roles is essential in public registration. Generic error responses coupled with internal logging prevent information leakage while maintaining debuggability.
**Prevention:** Strictly validate sensitive input fields and ensure all catch blocks return user-safe messages while logging the full exception for internal review.
