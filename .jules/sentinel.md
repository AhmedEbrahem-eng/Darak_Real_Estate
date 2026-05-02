## 2025-05-14 - Broken Access Control on Administrative Routes
**Vulnerability:** Several administrative routes (report-users, report-properties, reason-report, dashboard, amenities, property-types, tours) were exposed to the public or lacked role-based access control, allowing any user to view or modify sensitive data.
**Learning:** In a large route file, it's easy to overlook routes that are defined outside of protected middleware groups. Prefixing routes doesn't automatically protect them.
**Prevention:** Always group administrative routes under a common middleware stack that includes authentication and role-based authorization. Use automated security tests to verify that these routes return 401/403 for unauthorized requests.
