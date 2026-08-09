## 2026-08-09 - [Sensitive Data Exposure via Raw Exception Logging]
**Vulnerability:** Raw exception messages (`$th->getMessage()`) were directly returned to client APIs in the `catch` blocks of various authentication-related controllers (`AuthController`, `ForgotPasswordController`, `ResetPasswordController`, and `SocialLoginController`).
**Learning:** Returning exception details to API clients can leak critical internals (such as database structure, schemas, table names, third-party service credentials, or internal environment details).
**Prevention:** Always log the exception details to server logs (using Laravel's `Log::error($th)`) and return a generic, secure response (e.g., `'An error occurred.'`) to client requests.
