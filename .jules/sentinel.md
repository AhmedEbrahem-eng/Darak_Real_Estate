## 2024-05-21 - [CRITICAL] Privilege Escalation in Registration
**Vulnerability:** The registration endpoint allowed users to specify their role, including 'admin', in the request payload.
**Learning:** Whitelisting roles in validation without excluding privileged roles like 'admin' allows any user to gain administrative access.
**Prevention:** Always exclude administrative or other privileged roles from public-facing registration or profile update forms. Use a separate process or admin-only endpoint for elevated privilege assignments.
