# Guía de Inicio Rápido

Bienvenido a **Laracloak**, la plataforma para construir interfaces seguras sobre servicios de automatización externos.

## 📋 Requisitos Previos

Asegúrate de tener instalados los siguientes componentes en tu servidor o entorno local:

- **PHP 8.2+** (con extensiones: bcmath, curl, openssl, mbstring, xml, zip, sqlite3)
- **Composer** (Gestor de dependencias de PHP)
- **Node.js 18+ & NPM** (Para compilar activos estáticos)
- **Servidor Web**: Apache, Nginx o Laragon (recomendado en Windows)
- **Base de Datos**: MySQL/MariaDB (Recomendado para producción), o SQLite

## 🚀 Instalación Paso a Paso

### 1. Clonar el repositorio
```bash
git clone https://github.com/fuseprods/laracloak.git laracloak
cd laracloak
```

### 2. Instalar dependencias
Usa Composer para el backend y NPM para el frontend:
```bash
composer install
npm install
npm run build
```

### 3. Configurar el entorno
Copia el archivo de ejemplo y genera la clave de cifrado:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Preparar la Base de Datos
> [!TIP]
> **Recomendación**: Para entornos de producción, recomendamos encarecidamente utilizar **MySQL 8.0+** o **MariaDB**. SQLite es excelente para pruebas o instancias locales pequeñas.

#### Opción A: MySQL / MariaDB (Recomendado)
1. Crea una base de datos vacía (ej: `laracloak`).
2. Actualiza tu archivo `.env` con tus credenciales:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laracloak
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_password
   ```
3. Ejecuta las migraciones y datos iniciales (seeders):
   ```bash
   php artisan migrate --seed
   ```

#### Opción B: SQLite
1. Asegúrate de que tu `.env` esté configurado para SQLite: `DB_CONNECTION=sqlite`.
2. Crea el archivo de base de datos si no existe:
   - **Windows**: `type nul > database/database.sqlite`
   - **Linux/Mac**: `touch database/database.sqlite`
3. Ejecuta las migraciones: `php artisan migrate --seed`

### 5. Iniciar la aplicación
Si estás usando el servidor integrado de PHP:
```bash
php artisan serve
```

Tras ejecutar el comando `--seed`, se crea un usuario administrador por defecto. Puedes personalizar estas credenciales en tu archivo `.env` antes de ejecutar el seeder:

- **Email Inicial**: `INITIAL_ADMIN_EMAIL` (Por defecto: `admin@laracloak.com`)
- **Password Inicial**: `INITIAL_ADMIN_PASSWORD` (Por defecto: `password`)

> [!IMPORTANT]
> Cambia estos valores en tu `.env` antes de ejecutar `php artisan migrate --seed` para una instalación inicial más segura.

![Placeholder: Captura de pantalla de la pantalla de Login](img/login_screen.png)

## 🌐 Instalación en Hosting Compartido

Si estás instalando Laracloak en un hosting compartido (cPanel, Plesk, etc.), es fundamental configurar correctamente el **Document Root**.

### 1. Configurar el Document Root
La mayoría de los hostings compartidos apuntan a una carpeta llamada `public_html`, `www` o `httpdocs`. Laravel requiere que el servidor web apunte a la carpeta `/public` del proyecto por seguridad.

**Opción Recomendada:**
Cambia la configuración de tu dominio o subdominio en el panel de control para que el **Document Root** sea `public` (ej: `/home/usuario/laracloak/public`).

### 2. Métodos si no puedes cambiar el Root
Si tu hosting no permite cambiar el Document Root, puedes usar uno de estos métodos (ordenados de más a menos recomendado):

#### Opción A: Enlace Simbólico (Symlink)
Si tienes acceso SSH, puedes renombrar tu carpeta `public_html` (previo backup) y crear un enlace simbólico que apunte a la carpeta `public` de Laracloak:

```bash
ln -s /home/usuario/laracloak/public /home/usuario/public_html
```

#### Opción B: Renombrar carpeta `public` a `public_html`
Puedes renombrar físicamente la carpeta `public` a `public_html` y decirle a Laravel que use esa nueva ruta. Para ello, edita el archivo `bootstrap/app.php` de la siguiente manera:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(...)
    ->withMiddleware(...)
    ->withExceptions(...)
    // Añade esta línea:
    ->usePublicPath(realpath(base_path('../public_html'))) 
    ->create();
```

#### Opción C: Método .htaccess (Poco recomendado)
> [!CAUTION]
> **Riesgo de Seguridad**: Este método es el más peligroso. Si el servidor no está perfectamente configurado, archivos sensibles como `.env`, logs de error o la base de datos SQLite podrían quedar expuestos al público. Solo úsalo si no tienes otra opción y asegúrate de proteger los archivos ocultos.

Crea un archivo `.htaccess` en la raíz de tu proyecto (fuera de `public/`) con el siguiente contenido:

```apache
<IfModule mod_rewrite.c>
   RewriteEngine On
   RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 🛠️ Siguientes Pasos
1.  [Configura las Credenciales](upstream_proxy.md) para tus servicios upstream (n8n, APIs).
2.  [Crea tu primera Página](creating_pages.md) (Formulario o Dashboard).
3.  Organiza a tus usuarios por [Grupos y Categorías](user_roles.md).
