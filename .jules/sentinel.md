## 2024-05-15 - Privilege Escalation in Registration
**Vulnerability:** New users could register with any role, including 'admin', by specifying it in the request payload.
**Learning:** Whitelisting roles in validation is essential when the role is provided by the client.
**Prevention:** Always explicitly define the allowed roles for public-facing registration endpoints and exclude administrative roles from this whitelist.
