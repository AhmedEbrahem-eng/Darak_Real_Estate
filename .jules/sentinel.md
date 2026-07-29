## 2024-07-29 - [Registration Privilege Escalation]
**Vulnerability:** The application allowed self-registration with any user role, including the administrative role `admin`, via the public register API `/api/register`. This enabled anonymous visitors to escalate their privileges to administrator.
**Learning:** The validation whitelist in `RegisterUserRequest` contained `'admin'` and did not restrict registering users from selecting the administrative role directly from client input.
**Prevention:** Restrict registration requests to only safe user-level roles (`user`, `landlord`), and seed default administrative accounts securely from a controlled environment seeder or CLI commands.
