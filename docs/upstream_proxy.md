# Upstream Proxy 🛡️

Laracloak acts as a secure bridge between the end-user and your automation services (such as n8n, Make, or custom APIs). Its primary mission is to protect your infrastructure by never exposing upstream credentials, URLs, or internal logic directly to the browser.

## 🔒 How it Works

When a user interacts with a Form or Dashboard in Laracloak:

1.  **Request Capture**: Laracloak receives the request on its own backend.
2.  **Authentication & Authorization**: The system verifies that the user has the required permissions (Role-Based Access Control - RBAC).
3.  **Credential Injection**: Laracloak retrieves the encrypted credentials for the target service and injects them into the request (e.g., Bearer tokens, Basic Auth).
4.  **Backend Execution**: The request is sent from server to server (Laracloak -> Upstream Service).
5.  **Response Sanitization**: Laracloak receives the response, removes sensitive headers or internal data, and delivers only the necessary information to the user's browser.

## 🛠️ Configuration

Credentials for upstream services are configured in the `.env` file or via the administrative panel (depending on the specific module).

### Key Variables

| Variable | Description |
| :--- | :--- |
| `UPSTREAM_URL` | The base URL of the service (e.g., `https://n8n.yourdomain.com`). |
| `UPSTREAM_AUTH_TYPE` | Type of authentication (e.g., `Header`, `Basic`, `None`). |
| `UPSTREAM_AUTH_VALUE` | The credential itself (encrypted in the database). |

## 🛡️ Security Advantages

*   **No exposed URLs**: The user only sees your Laracloak domain.
*   **Encrypted Credentials**: Tokens are never stored in plain text and never leave the server.
*   **CORS Protection**: Since requests are made from the backend, you avoid browser-level Cross-Origin Resource Sharing issues.
*   **Audit Log**: Every request through the proxy is logged for security monitoring.
