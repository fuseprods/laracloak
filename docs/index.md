---
layout: home

hero:
  name: "Laracloak"
  text: "Secure Proxy & Dynamic Interface"
  tagline: Discover a simple and secure way to connect your tools without exposing infrastructure. Total protection for your n8n, Make, and external API endpoints.
  actions:
    - theme: brand
      text: Quick Start
      link: /getting_started
    - theme: alt
      text: View on GitHub
      link: https://github.com/fuseprods/laracloak
  image:
    src: /logo.png
    alt: Laracloak Logo

features:
  - title: Security without secrets 🛡️
    details: Receive and send data without ever exposing the real location of your servers. Laracloak acts as an invisibility layer using a "Default Deny" security model.
  - title: Total Simplicity ✨
    details: Your users interact with a friendly interface (Dashboards & Forms), while the system handles the complex part on the server.
  - title: Open Learning 🎨
    details: As an Open Source project, Laracloak is designed for you to explore how secure proxies and dynamic interfaces work.
---

## Take a look inside

### 📊 Real-time Dashboards
Visualize data from your upstream services (n8n, Make) with automatic refresh rates and zero database storage.

![Laracloak Dashboard Screenshot](assets/dashboards.png)

### 📝 Interactive Forms
Create secure input forms that sanitize and forward usage data to your automation webhooks.

![Laracloak Form Editor Screenshot](assets/forms.png)

---

## 📚 Documentation Sections

- **[Quick Start Guide](getting_started.md)**: Installation, prerequisites, and initial access.
- **[Configuration](configuration.md)**: Environment variables, database, and theme customization.
- **[Roles and Permissions](user_roles.md)**: Understanding the "Default Deny" model and group management.
- **[Upstream Proxy](upstream_proxy.md)**: How security, credentials, and sanitization work.
- **[General Page Guide](creating_pages.md)**: The page model in Laracloak.
- **[Dynamic Forms](forms.md)**: Detailed guide and configuration examples for output forms.
- **[Dashboards](dashboards.md)**: Detailed guide and examples for input data visualization.
- **[Design System](design_system.md)**: Visual identity and CSS tokens.
- **[🔐 JWT Authentication](jwt_usage.md)**: Configuring Upstreams with JWT generation.

---

## ⚡ Useful Links
- [GitHub Repository](https://github.com/fuseprods/laracloak)
- [MIT License](https://github.com/fuseprods/laracloak/blob/main/LICENSE)
