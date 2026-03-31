## 2025-05-22 - [Privilege Escalation in User Registration]
**Vulnerability:** The public user registration endpoint (`/api/register`) allowed any user to assign themselves the `admin` role by including it in the request payload.
**Learning:** Laravel's `in:` validation rule for roles must explicitly exclude administrative roles on public-facing registration requests to prevent unauthorized privilege escalation.
**Prevention:** Strictly define allowed roles for public registration (e.g., `user`, `landlord`) and ensure `admin` or other elevated roles are only assignable through a separate, protected administrative interface.
