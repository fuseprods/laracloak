# 📝 Guía de Formularios (Forms)

Los Formularios en Laracloak permiten capturar datos del usuario y enviarlos de forma segura a un servicio upstream (como n8n, Make o un API propia) sin exponer la URL de destino ni las credenciales.

## Estructura de Configuración (JSON)

El campo "UI Configuration" define cómo se renderiza el formulario.

### Esquema Básico
```json
{
  "title": "Solicitud de Soporte",
  "description": "Por favor, completa los datos para ayudarte.",
  "submit_label": "Enviar Ticket",
  "fields": [
    {
      "name": "asunto",
      "label": "Asunto de la consulta",
      "type": "text",
      "placeholder": "Ej: Problema con acceso",
      "required": true
    },
    {
      "name": "categoria",
      "label": "Categoría",
      "type": "select",
      "options": ["Técnico", "Facturación", "Otros"],
      "required": true
    },
    {
      "name": "mensaje",
      "label": "Detalles",
      "type": "textarea",
      "placeholder": "Explica tu problema...",
      "required": false
    }
  ]
}
```

## Propiedades de los Campos

| Propiedad | Descripción |
| :--- | :--- |
| `name` | El ID del campo que se enviará al upstream (ej: `email`). |
| `label` | Texto que verá el usuario encima del campo. |
| `type` | Tipo de entrada: `text`, `email`, `number`, `tel`, `textarea`, `select`, `password`. |
| `placeholder` | Texto de ayuda dentro del campo. |
| `required` | `true` o `false`. Añade validación HTML5 básica. |
| `options` | (Solo para `select`) Lista de strings con las opciones. |

## Handlers de Éxito (Propiedades de la Página)

Más allá del JSON, al crear un Formulario puedes configurar:

1.  **Success Message**: Un mensaje que se mostrará en un cuadro verde al usuario tras el envío exitoso.
    *   *Ejemplo*: "¡Gracias! Hemos recibido tu solicitud correctamente."
2.  **Redirect URL**: Una URL a la que el navegador enviará al usuario automáticamente tras 2 segundos de éxito.
    *   *Ejemplo*: `https://miempresa.com/gracias`

## Ejemplo Completo: Registro de Evento

**Configuración JSON:**
```json
{
  "title": "Inscripción Webinar AI",
  "submit_label": "Reservar mi plaza",
  "fields": [
    {
      "name": "user_email",
      "label": "Email Corporativo",
      "type": "email",
      "required": true
    },
    {
      "name": "job_title",
      "label": "Cargo actual",
      "type": "text",
      "placeholder": "Ej: CTO, Developer..."
    },
    {
      "name": "newsletter",
      "label": "Deseo suscribirme",
      "type": "select",
      "options": ["Sí", "No"]
    }
  ]
}
```

**Comportamiento:**
Al pulsar "Reservar mi plaza", Laracloak:
1.  Recoge `user_email`, `job_title` y `newsletter`.
2.  Añade las credenciales configuradas (si las hay).
3.  Hace un POST al **Destination URL**.
4.  Si el upstream responde 200 OK, muestra el **Success Message** y redirige si hay una **Redirect URL**.

![Placeholder: Captura de pantalla del constructor de formularios visual](img/form_builder.png)

![Placeholder: Captura de pantalla del formulario final renderizado en el front-end](img/form_frontend.png)
