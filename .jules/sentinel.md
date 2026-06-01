## 2025-05-15 - [Privilege Escalation in User Registration]
**Vulnerability:** The public registration endpoint allowed users to specify their role, including the 'admin' role, without any server-side validation to prevent unauthorized administrative access.
**Learning:** Whitelisting roles in a FormRequest is not enough if the whitelist includes highly privileged roles like 'admin' that should never be granted through a public-facing self-service registration form.
**Prevention:** Remove administrative roles from public registration validation rules. Use a separate process or a protected administrative endpoint for creating admin users.
