# Sentinel's Security Journal 🛡️

## 2023-11-20 - Privilege Escalation via Registration Role Parameter
**Vulnerability:** The application's registration request class (`RegisterUserRequest.php`) included `'admin'` in the allowed list of roles. An unauthenticated attacker could self-register with full administrative privileges by passing `'role': 'admin'`.
**Learning:** This occurred due to overly permissive validation constraints, coupled with direct mass assignment or direct property assignment of the user input parameter `role` without restrictive checks.
**Prevention:** Never include elevated roles (e.g. `'admin'`) in public-facing, self-service registration whitelists. Any administrative account seeding should be managed via secure offline commands, database seeding, or strictly authenticated internal admin actions.
