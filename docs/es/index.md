---
layout: home

hero:
  name: "Laracloak"
  text: "Proxy Seguro e Interfaz Dinámica"
  tagline: Descubre una forma simple y segura de conectar tus herramientas sin complicaciones. Protección total para tus endpoints de n8n, Make y APIs externas.
  actions:
    - theme: brand
      text: Inicio Rápido
      link: /es/getting_started
    - theme: alt
      text: Ver en GitHub
      link: https://github.com/fuseprods/laracloak
  image:
    src: ../assets/logo.png
    alt: Laracloak Logo

features:
  - title: Seguridad sin secretos 🛡️
    details: Recibe y envía datos sin exponer nunca la ubicación real de tus servidores. Laracloak actúa como una capa de invisibilidad usando un modelo de seguridad "Default Deny".
  - title: Simplicidad Total ✨
    details: Tus usuarios interactúan con una interfaz amigable (Dashboards y Formularios), mientras el sistema maneja la parte compleja en el servidor.
  - title: Aprendizaje Abierto 🎨
    details: Como proyecto Open Source, Laracloak está diseñado para que explores cómo funcionan los proxies seguros y las interfaces dinámicas.
---

## Echa un vistazo al interior

### 📊 Dashboards en Tiempo Real
Visualiza datos desde tus servicios upstream (n8n, Make) con tasas de refresco automáticas y sin almacenamiento en base de datos.

![Captura de pantalla Dashboard Laracloak](../assets/dashboards.png)

### 📝 Formularios Interactivos
Crea formularios de entrada seguros que sanean y reenvían datos a tus webhooks de automatización.

![Captura de pantalla Editor de Formularios Laracloak](../assets/forms.png)

---

## 📚 Secciones de la Documentación

- **[Guía de Inicio Rápido](getting_started.md)**: Instalación, requisitos previos y acceso inicial.
- **[Configuración](configuration.md)**: Variables de entorno, base de datos y configuración de temas.
- **[Roles y Permisos](user_roles.md)**: Comprendiendo el modelo "Default Deny" y la gestión de grupos.
- **[Upstream Proxy](upstream_proxy.md)**: Cómo funciona la seguridad, credenciales y sanitización.
- **[Guía General de Páginas](creating_pages.md)**: El modelo de páginas en Laracloak.
- **[Formularios (Forms)](forms.md)**: Guía detallada y ejemplos de configuración para formularios de salida.
- **[Dashboards (Paneles)](dashboards.md)**: Guía detallada y ejemplos para visualización de datos de entrada.
- **[Sistema de Diseño](design_system.md)**: Identidad visual y tokens CSS.
- **[🔐 Autenticación JWT](jwt_usage.md)**: Configuración de Upstreams con generación de JWT.

---

## ⚡ Enlaces Útiles
- [Repositorio en GitHub](https://github.com/fuseprods/laracloak)
- [Licencia MIT](https://github.com/fuseprods/laracloak/blob/main/LICENSE)
