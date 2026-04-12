## 2025-05-15 - Privilege Escalation in User Registration
**Vulnerability:** The public user registration endpoint (`/api/register`) allowed users to specify the `admin` role in the request payload, enabling any user to register as an administrator.
**Learning:** In Laravel applications, always strictly validate and whitelist the `role` field in request objects, especially when using mass assignment or direct input from the request.
**Prevention:** Explicitly define allowed roles in the `FormRequest` validation rules and exclude high-privilege roles like `admin` from public-facing registration logic.
