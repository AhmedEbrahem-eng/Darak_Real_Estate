## 2025-05-15 - Registration Privilege Escalation
**Vulnerability:** The registration endpoint allowed users to specify their own role, including 'admin', through the 'role' field in the request.
**Learning:** Default validation rules that include 'admin' in an `in:...` list for public registration forms are a high-risk pattern for privilege escalation.
**Prevention:** Always exclude administrative roles from public registration validation logic. Use a separate process or restricted endpoint for creating administrative accounts.
