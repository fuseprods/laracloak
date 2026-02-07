# Configuración de la Aplicación

Laracloak se configura principalmente a través de variables de entorno en el archivo `.env`.

## 🌍 Variables de Entorno (.env)

### Configuración General
- `APP_NAME`: El nombre de tu instancia (por defecto "Laracloak").
- `APP_URL`: La URL base de la aplicación (vital para generar enlaces correctamente).
- `APP_KEY`: Clave de encriptación de Laravel. Generada con `php artisan key:generate`.

### Base de Datos
Por defecto usamos SQLite para mayor sencillez:
```env
DB_CONNECTION=sqlite
```
Si prefieres MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_db
DB_USERNAME=usuario_db
DB_PASSWORD=secreto
```

---

## 🎨 Temas Visuales

Laracloak soporta múltiples temas que pueden ser seleccionados por cada usuario desde su perfil. El sistema de temas inyecta dinámicamente variables CSS.

### Temas disponibles:
1.  **Dark**: Modo oscuro clásico centrado en azules profundos y grises.
2.  **Light**: Modo claro para entornos muy iluminados.
3.  **Glassmorphism**: Estética futurista con transparencias, desenfoques (frosted glass) y bordes brillantes.

Para desarrolladores: Los temas se encuentran en `resources/css/themes/` y se sirven a través del `AssetController`.

---

## 🔐 Seguridad Upstream

La aplicación utiliza el driver `encrypted` de Eloquent para almacenar las `destination_url` y los tokens de las `Credentials`. Esto significa que incluso si alguien obtiene acceso a la base de datos, no podrá leer las URLs de tus flujos de n8n o tus API Keys sin la `APP_KEY`.
