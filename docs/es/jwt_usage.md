# Uso de Credenciales JWT

Laracloak permite la autenticación mediante JWT (JSON Web Token) de dos formas:
1. **HMAC (Secreto Compartido)**: Firma básica usando una clave secreta compartida.
2. **PEM (RSA/EC)**: Firma asimétrica usando una Clave Privada.

## Configuración vía UI

El formulario de creación/edición de credenciales ahora proporciona una interfaz amigable para configurar la generación dinámica de JWT. Ya no es necesario editar JSON manualmente.

### 1. Tipo de Clave (Key Type)
Selecciona el tipo de clave que deseas usar:
- **HMAC Secret (Shared)**: Usa una clave simétrica (como `HS256`).
- **PEM Key (RSA/EC)**: Usa una clave privada asimétrica (como `RS256`).

### 2. Algoritmo
Basado en el Tipo de Clave seleccionado, los algoritmos disponibles se poblarán automáticamente:
- Para HMAC: `HS256`, `HS384`, `HS512`.
- Para PEM: `RS256`, `RS384`, `RS512`, `ES256`, etc.

### 3. Claves / Secretos
Dependiendo del Tipo de Clave, aparecerán campos diferentes:
- **Shared Secret**: Para HMAC. Introduce tu cadena secreta aquí.
- **Private Key**: Para PEM. Pega el contenido completo de tu PEM (empezando con `-----BEGIN PRIVATE KEY-----`).
- **Public Key**: (Opcional/Informativo) Para PEM. Puede guardarse como referencia.

> [!NOTE]
> Todas las claves sensibles (Secretos y Claves Privadas) se almacenan de forma segura en la base de datos (`auth_value`), encriptadas en reposo.

## Claims Automáticos
Laracloak genera automáticamente los siguientes claims estándar para cada petición:
- `iss`: Issuer (URL de la aplicación).
- `iat`: Issued At (Marca de tiempo actual).
- `exp`: Expiration (Marca de tiempo actual + 60 segundos).
