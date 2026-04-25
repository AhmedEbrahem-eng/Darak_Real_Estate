## 2026-04-25 - IDOR and Unprotected Routes in Tour Management
**Vulnerability:** Tour approval and decline endpoints were publicly accessible and lacked ownership verification (IDOR).
**Learning:** Routes defined outside of middleware groups are exposed to the public. Even when authenticated, missing ownership checks allow users to manipulate data belonging to others.
**Prevention:** Always group sensitive routes under authentication middleware and implement strict authorization checks (Policies or manual checks) to verify record ownership.
