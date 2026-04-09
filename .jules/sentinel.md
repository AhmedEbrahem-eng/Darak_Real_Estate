## 2026-04-09 - [Privilege Escalation in Registration]
**Vulnerability:** The public `/api/register` endpoint allowed users to self-assign the `admin` role by providing it in the request body.
**Learning:** Registration validation rules included 'admin' in the allowed whitelist for the `role` field, directly translating user input into database roles without additional checks.
**Prevention:** Strictly exclude administrative roles from public-facing registration validation. Ensure that high-privilege roles can only be assigned through restricted administrative interfaces or secure seeding processes.
