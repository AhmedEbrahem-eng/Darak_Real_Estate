## 2024-05-22 - Registration Privilege Escalation
**Vulnerability:** The public user registration endpoint (`/api/register`) allowed the `role` field to be set to `admin`, granting full administrative privileges to any self-registered user.
**Learning:** Defaulting to a whitelist that includes administrative roles in public forms is a high-risk pattern. Validation rules must strictly exclude internal or privileged roles from user-controlled inputs.
**Prevention:** Always exclude the `admin` role (and any other privileged roles) from public registration validation logic. Administrative accounts should only be created through secure, internal channels.
