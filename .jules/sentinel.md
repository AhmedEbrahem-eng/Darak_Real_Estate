## 2024-05-17 - Administrative Routes and Review IDOR
**Vulnerability:** Several administrative routes (amenities, dashboard, reports) were missing 'admin' middleware, and review update/delete endpoints were missing ownership checks (IDOR).
**Learning:** Routes outside of the main admin group were left unprotected, and repository methods were performing operations without validating the requester's authority.
**Prevention:** Always group administrative routes under proper middleware and implement ownership checks in controllers or via policies.
