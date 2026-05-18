## 2024-05-21 - IDOR in Property Deletion
**Vulnerability:** The property deletion endpoint was entirely unprotected, allowing any user (or unauthenticated visitor) to delete any property by its ID.
**Learning:** Missing middleware and ownership checks on destructive endpoints can lead to catastrophic data loss and unauthorized actions.
**Prevention:** Always apply authentication middleware to sensitive routes and implement authorization logic to verify that the requester has the right to perform the action on the specific resource.
