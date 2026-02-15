# Protocolo JWT en Laracloak

Este documento describe la especificación técnica de los JSON Web Tokens (JWT) generados por el sistema de credenciales de Laracloak para autenticarse contra servicios upstream.

## Librería y Algoritmos

- **Librería**: `firebase/php-jwt` v6.0+
- **Algoritmo por defecto**: `HS256` (HMAC con SHA-256).
- **Algoritmos soportados**: Todos los soportados por la librería para firma simétrica (HS384, HS512, etc.), siempre que `credential.auth_value` contenga la clave secreta.

## Estructura del Token

El token generado consta de tres partes: Header, Payload y Signature.

### 1. Header

```json
{
  "typ": "JWT",
  "alg": "HS256"
}
```
*El algoritmo (`alg`) es configurable mediante `settings.alg`.*

### 2. Payload

El payload incluye claims estándar y personalizados.

#### Claims Estándar (Automáticos)

| Claim | Descripción | Valor |
|-------|-------------|-------|
| `iss` | Issuer (Emisor) | `config('app.url')` (La URL base de Laracloak) |
| `iat` | Issued At (Emitido) | Timestamp actual (fijado en el momento de la petición) |
| `exp` | Expiration (Expira) | `iat` + 60 segundos |
| `nbf` | Not Before | (No establecido por defecto) |

#### Claims Personalizados

Cualquier par clave-valor definido en `credential.settings.claims` se fusionará en el payload, sobrescribiendo los calculados automáticamente si coinciden las claves.

**Ejemplo de Payload Final:**

```json
{
  "iss": "https://laracloak.internal",
  "iat": 1708000000,
  "exp": 1708000060,
  "role": "service-account",
  "scope": "read:data"
}
```

### 3. Signature

La firma se genera utilizando el algoritmo especificado y la clave secreta almacenada en `credential.auth_value`.

`HMACSHA256(base64UrlEncode(header) + "." + base64UrlEncode(payload), secret)`

## Flujo de Autenticación

1. **Petición**: Un usuario o sistema solicita un recurso a través de Laracloak que apunta a un upstream protegido.
2. **Resolución**: Laracloak identifica la credencial asociada al upstream.
3. **Generación**: Si la credencial es de tipo `jwt` y modo `generation`:
    - Se desencripta `auth_value` para obtener el secreto.
    - Se construye el payload.
    - Se firma el token.
4. **Inyección**: El token se añade a la cabecera HTTP de la petición al upstream:
   `Authorization: Bearer <token_generado>`
5. **Envío**: La petición firmada se envía al upstream.
