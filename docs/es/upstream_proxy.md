# Configuración del Upstream Proxy

Laracloak actúa como un intermediario seguro entre tus usuarios finales y tus servicios de infraestructura (como n8n, Make, o APIs privadas).

## 🛡️ ¿Cómo funciona el Proxy?

Cuando un usuario accede a una "Página" en Laracloak:
1.  El sistema valida que el usuario tenga permiso de **Ver**.
2.  La petición se envía al servidor de Laracloak, **no directamente al servicio upstream**.
3.  Laracloak adjunta las **Credenciales** (API Keys, Bearer Tokens) configuradas para esa página.
4.  La respuesta del servicio upstream es recibida por Laracloak, **sanitizada** (se eliminan cabeceras sensibles y trazas de error internas) y entregada al usuario.

**Resultado**: El usuario nunca conoce la URL real del servicio, ni los tokens de acceso, ni la estructura interna de tu automatización.

## 🔑 Gestión de Credenciales

Antes de crear páginas, debes configurar cómo Laracloak se autenticará ante tus servicios.

### Tipos de Autenticación Soportados:
-   **No Auth**: Sin autenticación.
-   **API Key**: Envía una clave en una cabecera personalizada (ej: `X-API-KEY`).
-   **Bearer Token**: Envía un token en la cabecera `Authorization`.

### Niveles de Seguridad:
Las credenciales están protegidas por:
-   **Cifrado AES-256**: Los valores de los tokens se guardan cifrados en la base de datos.
-   **Restricción de Dominios**: Puedes limitar una credencial para que solo funcione con ciertos dominios (ej: `*.n8n.cloud`).

![Placeholder: Captura de pantalla de la gestión de Credenciales](img/credentials_management.png)

## 🎛️ Configuración de Destino (Destination URL)

Al crear una página (Formulario o Dashboard), definirás la **URL de Destino**.

-   **Método**: GET o POST (normalmente POST para formularios de n8n).
-   **Seguridad**: Si eliges una credencial, Laracloak se asegurará de que el dominio de la URL coincida con los permitidos por esa credencial.

## 🧼 Sanitización y Filtrado Automático

Laracloak aplica dos niveles de filtrado a las respuestas JSON del upstream:

1.  **Filtros Globales**: Por seguridad, siempre se eliminan las siguientes claves: `headers`, `webhookUrl`, `executionMode`, `stack`, `debug`, `request`.
2.  **Filtros Personalizados**: En la configuración de cada página, puedes definir una lista de claves (separadas por comas) que quieres eliminar de la respuesta antes de que llegue al usuario final. Esto es útil para ocultar IDs internos, metadatos innecesarios o tokens secundarios.

## 🔍 Zona de Pruebas (Debugger)

Para facilitar la configuración, el editor de páginas incluye una **Zona de Pruebas**. Esta herramienta permite:
-   Realizar una solicitud real al servicio upstream con la configuración actual (URL, Método, Credenciales).
-   Ver la respuesta exacta que recibiría el frontend, aplicando tanto los filtros globales como los personalizados.
-   Visualizar el resultado en texto plano formateado (Pretty Print) para depurar la estructura antes de publicar la página.

**Nota**: El debugger utiliza el servidor de Laracloak para realizar la petición, por lo que hereda todas las protecciones de seguridad del proxy.
