# Laracloak

<p align="center">
  <img src="public/laracloak.png" width="120" alt="Laracloak Logo" onerror="this.src='https://via.placeholder.com/120?text=🛡️'">
</p>

<p align="center">
  <strong>Open Source Secure Proxy for Automations</strong>
</p>

<p align="center">
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="MIT License"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.1+-blue.svg" alt="PHP 8.1+"></a>
  <a href="#"><img src="https://img.shields.io/badge/Laravel-11-red.svg" alt="Laravel 11"></a>
</p>

---

## 🚀 Description

Laracloak is an **open source** platform built with **Laravel** that acts as a secure "front office" and an "opaque proxy" for external instances (n8n, Make, APIs, etc.). Its goal is to enable interaction with automation flows **without exposing upstream infrastructure** to end users. It has been developed with Google Antigravity and different models (Gemini, Claude...) so it’s possible that there are inconsistencies in the code and some critical security flaws. **This is a project under development and not recommended for production environments.**

### Key Features
- **Endpoint Opacity**: No endpoint details (URLs, headers, internal errors) are visible in the browser or network logs.
- **"Default Deny" Authorization**: Granular permission system at page, category, and group level.
- **Dynamic Rendering**: Interfaces generated from JSON configurations managed from the admin panel.
- **Response Sanitization**: Automatic filtering of sensitive keys in responses.
- **Visual Customization**: Built-in themes (Dark, Light, Glassmorphism) selectable by the user.

---

## 🏗️ Architecture and Data Flow

The system is based on a layered model to ensure maximum security:

1. **Presentation Layer (Blade)**: The user interacts with a pure Laravel interface.
2. **Proxy Layer (FrontController)**: Validates permissions and forwards requests to the upstream service.
3. **Integration Layer (UpstreamService)**: Manages secure communication with the endpoint using encrypted credentials.
4. **Upstream**: Executes the process logic and returns results that are sanitized before reaching the user.

---

## 👥 Roles and Features

### 🔐 Administrator
* **User Management**: Create, edit, and delete user accounts.
* **Permission Matrix**: Assign "View" or "Edit" access individually or via **Groups**.
* **Category Organization**: Group pages to simplify bulk permission management.
* **Credential Management**: Configure access tokens and credentials for upstream services in encrypted form.
* **Audit Logs**: Full traceability of who did what and when.

### 📝 Editor
* **Page Management**: Create friendly slugs for the front-end.
* **JSON Configuration**: Define the page structure and data mapping between the front-end and the endpoint.
* **Publishing**: Control the visibility of proxy tools.

### 👤 End User
* **Personalized Dashboard**: Access only the pages they have been authorized for.
* **Secure Interaction**: Use forms and data viewers without risking infrastructure exposure.
* **Profile and Themes**: Customize the visual experience via theme selectors.

---

## 🛡️ Permissions System

The system uses **polymorphic** relationships to allow full flexibility:
- **User -> Page**: Direct permission.
- **User -> Group -> Page**: Inherited permission through group membership.
- **User -> Category**: Access to all pages within that category.
- **User -> Group -> Category**: Combination of the above.

---

## 🛠️ Installation and Setup

1. **Clone the repository**.
2. **Install dependencies**:
    ```cmd
    composer install
    npm install && npm run build
    ```
3. **Environment configuration**:
    ```cmd
    cp .env.example .env
    php artisan key:generate
    ```
4. **Migrations and Seeders**:
    ```cmd
    php artisan migrate --seed
    ```

---

## ⚠️ Development Notes (Windows)

Due to Windows-specific shell behavior, all agent execution commands had to follow this format to avoid errors:

```cmd
cmd /c <your_command> & ::
```

---

## 📝 To-Do

- **Looking for ideas**: Please, open an issue and share your ideas.  

---

## 📄 License
This project is open source software under the [MIT](https://opensource.org/licenses/MIT) license.