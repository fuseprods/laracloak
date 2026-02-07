# Configuration 🛠️

Laracloak is highly customizable through environment variables and the administrative panel.

## 📄 Environment (.env)

The `.env` file in the root of your project contains the core configuration.

### Basic Settings
*   `APP_NAME`: The name of your application.
*   `APP_ENV`: Set to `production` in live environments.
*   `APP_KEY`: Unique encryption key (generated via `php artisan key:generate`).
*   `APP_URL`: The public URL of your installation (e.g., `https://cloak.yourdomain.com`).

### Database
*   **SQLite (Default)**:
    ```env
    DB_CONNECTION=sqlite
    ```
*   **MySQL**:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=laracloak
    DB_USERNAME=user
    DB_PASSWORD=password
    ```

## 🎨 Personalization (Themes)

You can change the visual appearance of Laracloak from the **Settings** section in the admin panel.

### Available Themes
*   **Dark Mode**: High contrast, ideal for low-light environments.
*   **Light Mode**: Clean and classic professional look.
*   **Glassmorphism**: Modern look with semi-transparent elements and blurs.

## 🔄 Cache Management

If you make changes to the `.env` file or configuration files, you might need to clear the cache:

```bash
php artisan config:clear
php artisan cache:clear
```
