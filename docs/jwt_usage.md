# JWT Credential Usage

Laracloak allows authentication via JWT (JSON Web Token) in two ways:
1. **HMAC (Shared Secret)**: Basic signing using a shared secret key.
2. **PEM (RSA/EC)**: Asymmetric signing using a Private Key.

## Configuration via UI

The credential creation/edit form now provides a user-friendly interface to configure dynamic JWT generation. You no longer need to edit JSON manually.

### 1. Key Type
Select the type of key you want to use:
- **HMAC Secret (Shared)**: Uses a symmetric key (like `HS256`).
- **PEM Key (RSA/EC)**: Uses an asymmetric private key (like `RS256`).

### 2. Algorithm
Based on the selected Key Type, the available algorithms will populate automatically:
- For HMAC: `HS256`, `HS384`, `HS512`.
- For PEM: `RS256`, `RS384`, `RS512`, `ES256`, etc.

### 3. Keys / Secrets
Depending on the Key Type, different fields will appear:
- **Shared Secret**: For HMAC. Enter your secret string here.
- **Private Key**: For PEM. Paste your full PEM content (starting with `-----BEGIN PRIVATE KEY-----`).
- **Public Key**: (Optional/Informational) For PEM. Can be stored for reference.

> [!NOTE]
> All sensitive keys (Secrets and Private Keys) are stored securely in the database (`auth_value`), encrypted at rest.

## Automatic Claims
Laracloak automatically generates the following standard claims for every request:
- `iss`: Issuer (Application URL).
- `iat`: Issued At (Current timestamp).
- `exp`: Expiration (Current timestamp + 60 seconds).
