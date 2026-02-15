# Uso de Credenciales JWT

Laracloak permite la autenticación mediante JWT (JSON Web Token) de dos formas:
1. **Estático**: Proporcionando un token ya generado.
2. **Generación Dinámica**: Generando un token firmado en cada petición.

## Configuración para Generación Dinámica

Para configurar una credencial que genere JWTs dinámicamente, debes establecer los siguientes valores en la base de datos (o formulario de creación):

- **Type**: `jwt`
- **Auth Key**: (Opcional) Puede usarse como identificador de la clave (kid), pero para HMAC suele ignorarse.
- **Auth Value**: La **clave secreta** (Secret Key) que se usará para firmar el token. Esta se guarda encriptada.
- **Settings**: Un objeto JSON con la configuración de generación.

### Estructura del JSON `settings`

```json
{
    "mode": "generation",
    "alg": "HS256",
    "claims": {
        "iss": "mi-app-id",
        "custom_claim": "valor"
    }
}
```

- `mode`: Debe ser `"generation"` para activar este modo. Si se omite o es otro valor, se usará `auth_value` como un token Bearer estático.
- `alg`: El algoritmo de firma. Por defecto `HS256`. Soportados: `HS256`, `HS384`, `HS512`.
- `claims`: (Opcional) Reclamaciones personalizadas que se añadirán al payload.
    - `iss`: Issuer (Emisor). Por defecto es la URL de la aplicación.
    - `iat`: Issued At (Emitido en). Se genera automáticamente (ahora).
    - `exp`: Expiration (Expiración). Se genera automáticamente (ahora + 60 segundos).

### Ejemplo de SQL

```sql
INSERT INTO credentials (name, type, auth_value, settings, created_at, updated_at)
VALUES ('Upstream Service A', 'jwt', 'super-secret-key-123', '{"mode": "generation", "alg": "HS256"}', NOW(), NOW());
```
