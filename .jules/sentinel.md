## 2024-08-08 - [Unprotected Administrative Report & Reason Endpoints]
**Vulnerability:** Missing authorization and authentication middleware on administrative endpoints (`report-users`, `report-properties`, and `reason-report` write routes) allowed unauthenticated/regular users to retrieve, delete, or alter reports and report reasons.
**Learning:** Adding endpoints under groups without a comprehensive, explicit middleware strategy can lead to newly introduced administrative endpoints being left entirely public.
**Prevention:** Always group administrative endpoints under explicit, secured route groups using `auth:sanctum`, `checkTokenExpiry`, and custom `admin` middlewares, and cover security policies with automated test suites.
