# Sentinel Security Journal 🛡️

## 2024-05-24 - [Broken Access Control: Unauthorized Admin Registration]
**Vulnerability:** The user registration API (`/api/register`) allowed any user to specify their own role, including 'admin'.
**Learning:** Input validation rules for roles should always be whitelisted and exclude high-privilege roles like 'admin' unless specifically intended for an administrative creation endpoint.
**Prevention:** Strictly validate the 'role' field against a safe whitelist and manage administrative users through a secure, restricted process.

## 2024-05-24 - [Information Leakage: Verbose Error Messages]
**Vulnerability:** API catch blocks were returning raw exception messages using `$th->getMessage()`, potentially exposing database structures, file paths, and internal logic.
**Learning:** Raw exceptions often contain sensitive system information. Always return generic, user-friendly error messages in production APIs.
**Prevention:** Implement a consistent error handling strategy that logs detailed errors internally but returns generic messages to the client.

## 2024-05-24 - [Broken Access Control: Unprotected Administrative Routes]
**Vulnerability:** Several administrative routes (reports, dashboard, amenities) lacked mandatory authentication and authorization middleware, making them accessible to anyone.
**Learning:** Route groups and individual sensitive endpoints must be explicitly protected by security middleware. Relying on "security by obscurity" or assuming front-end restrictions is insufficient.
**Prevention:** Always apply `auth:sanctum` and role-based middleware (like `admin`) to any route that performs sensitive operations or accesses private data.
