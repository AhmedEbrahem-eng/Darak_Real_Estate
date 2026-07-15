
## 2026-07-15 - [Review IDOR and Missing Authentication]
**Vulnerability:** Review update and delete endpoints lacked both authentication middleware and authorization ownership checks (IDOR).
**Learning:** Even when some routes in a group are protected, siblings might be overlooked. The `ReviewRepository` also had an insecure pattern where it overwrote the `user_id` before deletion, masking the lack of authorization.
**Prevention:** Always apply authentication middleware to all state-changing routes. Implement ownership checks at the controller level before calling repository methods for update/delete.
