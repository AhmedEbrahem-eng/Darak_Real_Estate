## 2024-06-13 - [CRITICAL] Privilege Escalation via Role Self-Assignment
**Vulnerability:** The `RegisterUserRequest` validation rules included 'admin' in the allowed 'role' list, allowing any user to register with administrative privileges.
**Learning:** Input validation whitelists for sensitive fields like 'role' must be strictly audited to exclude high-privilege values that should only be assigned by existing administrators or through secure, separate processes.
**Prevention:** Remove 'admin' or other sensitive roles from public-facing registration or update requests. Use middleware to protect administrative routes and verify role-based access on every request.
