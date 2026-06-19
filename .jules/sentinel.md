## 2024-05-15 - [Privilege Escalation in Registration]
**Vulnerability:** Users could self-assign the 'admin' role during registration by including it in the request payload.
**Learning:** Whitelisting 'admin' in a public registration validation rule creates a mass assignment risk for high-privilege roles.
**Prevention:** Never include administrative roles in the allowed values for public registration or update endpoints.

## 2024-05-15 - [Sensitive Information Leakage in Exceptions]
**Vulnerability:** Catch blocks were returning `$th->getMessage()` directly in API responses.
**Learning:** Raw exception messages can leak sensitive internal details (database structure, file paths, logic) to attackers.
**Prevention:** Always log the full exception internally and return a sanitized, generic error message to the client.

## 2024-05-15 - [Predictable Social Login Passwords]
**Vulnerability:** New users created via social login were assigned predictable passwords like 'my-google'.
**Learning:** Using a static or predictable pattern for passwords, even for social login users who are expected to use OAuth, creates a weak authentication point.
**Prevention:** Generate secure, random passwords for users created through social login providers.
