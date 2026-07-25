## 2026-07-24 - [Privilege Escalation during Registration]
**Vulnerability:** Unrestricted role assignment in the public registration Request validation rules (`RegisterUserRequest.php`) allowed standard registrants to supply the role `admin` and elevate their privileges directly.
**Learning:** Overly permissive whitelisting in Request validation (`in:admin,user,landlord`) without restricted endpoint separation or model-level checks can lead to direct privilege escalation.
**Prevention:** Always restrict privilege-affecting input parameters like `role` or `is_admin` to non-privileged roles on public-facing endpoints. Separate admin creation workflows completely from public registration.
