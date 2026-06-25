## 2024-05-24 - [Privilege Escalation and Route Hardening]
**Vulnerability:** Users could self-register as administrators by passing 'role' => 'admin' in the registration request. Additionally, multiple administrative endpoints (reporting, dashboard, amenities) were missing authentication and authorization middleware.
**Learning:** Input validation whitelists for roles must exclude administrative roles unless specifically intended for an internal-only registration process. Defaulting routes to "unprotected" can lead to massive security gaps if not systematically reviewed.
**Prevention:** Remove administrative roles from public-facing registration validators. Use middleware groups to enforce security policies across all administrative route prefixes.
