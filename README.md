# Laracloak

<p align="center">
  <img src="public/laracloak.png" width="120" alt="Laracloak Logo" onerror="this.src='https://via.placeholder.com/120?text=🛡️'">
</p>

<p align="center">
  <strong>🛡️ Proxy Seguro Open Source para Automatizaciones</strong>
</p>

<p align="center">
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="MIT License"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.1+-blue.svg" alt="PHP 8.1+"></a>
  <a href="#"><img src="https://img.shields.io/badge/Laravel-11-red.svg" alt="Laravel 11"></a>
</p>

---

## 🚀 Descripción

Laracloak es una plataforma **open source** construida con **Laravel** que actúa como un "front-office" seguro y un "proxy opaco" para instancias externas (n8n, Make, APIs, etc.). Su objetivo es permitir la interacción con flujos de automatización **sin exponer la infraestructura upstream** al usuario final.

### Características Clave
- **Opacidad de Endpoints**: Ningún detalle del endpoint (URLs, headers, errores internos) es visible en el navegador o logs de red.
- **Autorización "Default Deny"**: Sistema de permisos granular a nivel de página, categoría y grupo.
- **Renderizado Dinámico**: Interfaces generadas a partir de configuraciones JSON controladas desde el panel de administración.
- **Sanitización de Respuestas**: Filtrado automático de claves sensibles en respuestas.
- **Personalización Visual**: Temas integrados (Dark, Light, Glassmorphism) seleccionables por el usuario.

---

## 🏗️ Arquitectura y Flujo de Datos

El sistema se basa en un modelo de capas para garantizar la máxima seguridad:

1.  **Capa de Presentación (Blade)**: El usuario interactúa con una interfaz Laravel pura.
2.  **Capa de Proxy (FrontController)**: Valida permisos y reenvía peticiones al servicio upstream.
3.  **Capa de Integración (UpstreamService)**: Gestiona la comunicación segura con el endpoint usando credenciales encriptadas.
4.  **Upstream**: Ejecuta la lógica del proceso y devuelve resultados que son sanitizados antes de llegar al usuario.

---

## 👥 Roles y Funcionalidades

### 🔐 Administrador
*   **Gestión de Usuarios**: Crear, editar y eliminar cuentas de usuario.
*   **Matriz de Permisos**: Asignar acceso de "Ver" o "Editar" de forma individual o mediante **Grupos**.
*   **Organización por Categorías**: Agrupar páginas para facilitar la gestión masiva de permisos.
*   **Gestión de Credenciales**: Configurar tokens de acceso y credenciales para los servicios upstream de forma encriptada.
*   **Logs de Auditoría**: Trazabilidad completa de quién hizo qué y cuándo.

### 📝 Editor
*   **Gestión de Páginas**: Crear slugs amigables para el front-end.
*   **Configuración JSON**: Definir la estructura de la página y el mapeo de datos entre el front-end y endpoint.
*   **Publicación**: Controlar la visibilidad de las herramientas de proxy.

### 👤 Usuario Final
*   **Dashboard Personalizado**: Acceso solo a las páginas para las que ha sido autorizado.
*   **Interacción Segura**: Uso de formularios y visualizadores de datos sin riesgo de exponer la infraestructura.
*   **Perfil y Temas**: Personalización de la experiencia visual mediante selectores de tema.

---

## 🛡️ Sistema de Permisos

El sistema utiliza relaciones **polimórficas** para permitir una flexibilidad total:
- **Usuario -> Página**: Permiso directo.
- **Usuario -> Grupo -> Página**: Permiso heredado por pertenencia a grupo.
- **Usuario -> Categoría**: Acceso a todas las páginas dentro de esa categoría.
- **Usuario -> Grupo -> Categoría**: Combinación de lo anterior.

---

## 🛠️ Instalación y Configuración

1.  **Clonar el repositorio**.
2.  **Instalar dependencias**:
    ```cmd
    composer install
    npm install && npm run build
    ```
3.  **Configuración de entorno**:
    ```cmd
    cp .env.example .env
    php artisan key:generate
    ```
4.  **Migraciones y Seeders**:
    ```cmd
    php artisan migrate --seed
    ```

---

## ⚠️ Notas de Desarrollo (Windows)

Debido a comportamientos específicos de la shell en Windows, todos los comandos de ejecución de agentes deben seguir este formato para evitar bloqueos:

```cmd
cmd /c <your_command> & ::
```
*(Ver [agent_command_fix.md](file:///C:/laragon/www/javi/agent_command_fix.md) para más detalles).*

---

## 📄 Licencia
Este proyecto es software de código abierto bajo la licencia [MIT](https://opensource.org/licenses/MIT).
