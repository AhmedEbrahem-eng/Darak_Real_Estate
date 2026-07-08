## 2024-07-08 - [IDOR and Missing Function Level Access Control]
**Vulnerability:** Several endpoints allowed unauthenticated or unauthorized access to sensitive administrative data and permitted users to modify or delete resources they did not own (IDOR).
**Learning:** Routes were not consistently protected by middleware, and controllers lacked ownership verification logic, relying solely on repositories which often didn't perform authorization.
**Prevention:** Always group administrative routes under dedicated middleware. Implement ownership checks in controllers before calling repository methods for update/delete operations. Ensure all sensitive routes are protected by `auth:sanctum`.
