# Creación de Páginas: El Modelo Laracloak

Laracloak separa las interfaces en dos tipos fundamentales basándose en el flujo de los datos. Ambas comparten el sistema de **Slugs** (URLs públicas `/front/{slug}`) y el sistema de **Permisos** granulares.

## 📁 Tipos de Páginas

### 1. [📝 Formularios (Forms)](forms.md)
Diseñados para la **salida de datos** (User -> Upstream).
*   **Ideal para**: Webhooks de n8n, registros, peticiones de servicio, recolección de leads.
*   **Propiedades clave**: Mensajes de éxito y redirecciones automáticas.
*   **Método habitual**: `POST`.

### 2. [📊 Dashboards (Paneles)](dashboards.md)
Diseñados para la **entrada de datos** (Upstream -> User).
*   **Ideal para**: Monitorización de servidores, KPIs de ventas, listados de usuarios, estados de procesos.
*   **Propiedades clave**: Tasa de refresco automática (Auto-refresh).
*   **Método habitual**: `GET`.

![Placeholder: Captura de pantalla del formulario de creación de página](img/page_creation_form.png)

## 🛠️ Conceptos Compartidos

### Slugs y URLs Públicas
Cuando creas cualquier página, defines un **Slug**. Este slug determina la URL definitiva:
`https://tu-dominio.com/front/mi-slug-personalizado`

### Seguridad y Visibilidad
1.  **Estado Publicado**: Si una página no está publicada, será invisible para los usuarios normales (404), pero los Editores y Administradores podrán seguir viéndola para realizar pruebas.
2.  **Control de Acceso (ACL)**: Laracloak aplica un modelo "Default Deny". Debes asignar explícitamente qué Grupos o Usuarios individuales tienen permiso de "Ver" o "Editar" cada página.

### JSON de Configuración
Independientemente del tipo, la apariencia de cada página se define mediante un objeto JSON en el editor. Consulta las guías específicas para ver los esquemas soportados:
-   [JSON para Formularios](forms.md#esquema-básico)
-   [JSON para Dashboards](dashboards.md#esquema-básico)

---

## 🔍 Depuración (Zona de Pruebas)
Laracloak incluye un **Debugger** integrado en el editor. Antes de guardar o publicar, puedes ejecutar una "Petición de Prueba" para ver exactamente qué datos está devolviendo el upstream y cómo se están aplicando los filtros de seguridad.
