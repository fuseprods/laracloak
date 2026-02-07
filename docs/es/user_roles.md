# Roles, Grupos y Permisos

Laracloak utiliza un modelo de permisos basado en el principio de menor privilegio: **Default Deny** (Por defecto, nadie tiene acceso a nada).

## 👥 Tipos de Roles

Existen tres niveles de acceso global en el sistema:

1.  **Administrador**:
    -   Acceso total al Panel de Control.
    -   Gestión de usuarios, credenciales, grupos y categorías.
    -   Visualización de logs de auditoría.
2.  **Staff (Editor)**:
    -   Puede crear y editar "Páginas".
    -   No puede gestionar usuarios ni credenciales sensibles.
3.  **Usuario Final**:
    -   Solo puede ver las páginas a las que se le ha dado acceso explícito.
    -   Puede configurar su perfil y tema visual.

---

## 🛡️ Niveles de Permiso

Los permisos se pueden asignar a **Usuarios** o **Grupos** sobre tres tipos de objetos:

### 1. Permiso sobre Página
El nivel más granular. Permite acceso uno a uno.
-   **Ver**: El sujeto puede ver e interactuar con la página en el `/front`.
-   **Editar**: El sujeto puede modificar la configuración JSON de la página en el `/panel`.

### 2. Permiso sobre Categoría
Si un usuario tiene permiso sobre una **Categoría**, heredará automáticamente ese permiso sobre todas las **Páginas** vinculadas a esa categoría. Es ideal para departamentos (ej: Categoría "Marketing").

### 3. Pertenencia a Grupo
Los usuarios pueden pertenecer a múltiples grupos. Los permisos asignados a un grupo (sobre páginas o categorías) se suman a los permisos individuales del usuario.

![Placeholder: Captura de pantalla de la matriz de permisos en el perfil de usuario](img/permission_matrix.png)

---

## 📝 Ejemplo de Configuración
-   **Grupo "Soporte"**: Tiene permiso de **Ver** sobre la **Categoría "Herramientas Internas"**.
-   **Usuario "Juan"**: Pertenece al grupo "Soporte".
-   **Resultado**: Juan puede ver todas las páginas (dashboards/formularios) de herramientas internas sin necesidad de asignarle cada una individualmente.
