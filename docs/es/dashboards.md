# 📊 Guía de Dashboards (Paneles de Control)

Los Dashboards permiten visualizar información en tiempo real proveniente de un servicio externo. Laracloak consulta al upstream periódicamente y actualiza los componentes visuales automáticamente.

## Estructura de Configuración (JSON)

El motor de Dashboards busca claves específicas en la respuesta JSON del upstream para rellenar los widgets.

### Esquema Básico
```json
{
  "title": "Estado del Servidor",
  "widgets": [
    {
      "key": "uptime",
      "label": "Tiempo Activo",
      "type": "stat-card",
      "desc": "Días desde el último reinicio"
    },
    {
      "key": "status.memory_usage",
      "label": "Consumo RAM",
      "type": "stat-card"
    },
    {
      "key": "active_users",
      "label": "Usuarios Conectados ahora",
      "type": "table",
      "columns": [
        { "key": "name", "label": "Nombre" },
        { "key": "last_seen", "label": "Última conexión" }
      ]
    }
  ]
}
```

## 📚 Biblioteca de Widgets

A continuación se detallan los 13 tipos de widgets soportados. Usa el valor de la columna **Tipo** en tu configuración JSON.

| Widget | Tipo (`type`) | Uso Ideal |
| :--- | :--- | :--- |
| **KPI / Stat Card** | `kpi` o `stat-card` | Métricas individuales (Ventas, Usuarios). |
| **Sparkline** | `sparkline` | KPI con gráfico de tendencia simple. |
| **Progress Card** | `progress-card` | Porcentaje de carga o completitud (0-100%). |
| **Alert Card** | `alert-card` | Estado del sistema (OK, Error, Warning). |
| **Line Chart** | `chart` (subtype: `line`) | Tendencias temporales (Tráfico, CPU). |
| **Bar Chart** | `chart` (subtype: `bar`) | Comparativas por categoría o tiempo. |
| **Area Chart** | `chart` (subtype: `area`) | Volumen acumulado en el tiempo. |
| **Donut Chart** | `chart` (subtype: `donut`) | Composición simple (fuentes de tráfico). |
| **Pie Chart** | `chart` (subtype: `pie`) | Similar a Donut, diferente estilo. |
| **Gauge / Radial** | `chart` (subtype: `gauge`) | Medidor circular (Velocidad, % Uso). |
| **Table** | `table` | Listados de datos tabulares. |
| **Leaderboard** | `leaderboard` | Tabla con ranking (🥇 🥈 🥉). |
| **Timeline** | `timeline` | Lista vertical de eventos/hitos. |
| **Log Stream** | `log-stream` | Visor de logs y trazas del sistema. |
| **HTML Card** | `html-card` | Contenido libre (Desactivado por defecto). |
| **Informativo** | `info` | Tarjeta estática con texto y estado. |
| **Separador** | `break` | Salto de línea forzado en el grid. |
| **Oculto** | `none` | Elemento invisible para ajustar anchos. |

---

## 🛠️ Detalle de Configuración y API

### 1. KPI / Stat Card
Muestra un valor principal y opcionalmente un cambio (delta) respecto al periodo anterior.

**Configuración Panel:**
```json
{
  "type": "kpi",
  "key": "sales_today",
  "label": "Ventas Hoy",
  "desc": "vs ayer"
}
```

**Respuesta API (Opción Simple):**
```json
{ "sales_today": "$1,250" }
```

**Respuesta API (Opción Completa):**
```json
{
  "sales_today": {
    "value": "$1,250",
    "delta": { "value": "+15%", "color": "success" } // color: success, warning, danger, neutral
  }
}
```

---

### 2. Sparkline
KPI que incluye un pequeño gráfico de línea para mostrar la tendencia reciente.

**Configuración Panel:**
```json
{
  "type": "sparkline",
  "key": "memory_trend",
  "label": "Uso de Memoria"
}
```

**Respuesta API:**
```json
{
  "memory_trend": {
    "value": "64%",
    "history": [45, 46, 50, 55, 60, 64, 62, 64] // Array de puntos
  }
}
```

---

### 3. Progress Card
Barra de progreso visual.

**Configuración Panel:**
```json
{
  "type": "progress-card",
  "key": "disk_usage",
  "label": "Espacio en Disco"
}
```

**Respuesta API:**
```json
{
  "disk_usage": 78 // Valor numérico 0-100
}
```

---

### 4. Alert Card
Tarjeta destacada para comunicar el estado de salud de un sistema.

**Configuración Panel:**
```json
{
  "type": "alert-card",
  "key": "api_status",
  "label": "Estado del API",
  "status": "success" // Default status si la API no lo envía
}
```

**Respuesta API:**
```json
{
  "api_status": {
    "title": "Operativo",
    "status": "success", // success, warning, danger, info
    "time": "Hace 5 min" // Opcional
  }
}
```

---

### 5. Charts (Gráficos)
El motor usa ApexCharts. Todos comparten la estructura base, cambiando el `subtype`.

#### Line / Bar / Area
**Configuración Panel:**
```json
{
  "type": "chart",
  "subtype": "line", // o "bar", "area"
  "key": "web_traffic",
  "label": "Tráfico Web",
  "height": 300 // Opcional
}
```

**Respuesta API:**
```json
{
  "web_traffic": {
    "categories": ["Lun", "Mar", "Mie", "Jue", "Vie"],
    "series": [
      { "name": "Visitas", "data": [120, 400, 350, 500, 480] }
    ]
  }
}
```

#### Donut / Pie / Gauge
**Configuración Panel:**
```json
{
  "type": "chart",
  "subtype": "donut", // o "pie", "gauge"
  "key": "browser_share",
  "label": "Navegadores"
}
```

**Respuesta API (Simple):**
```json
{ "browser_share": [40, 30, 20, 10] } // Valores numéricos
```

**Respuesta API (Nombrada):**
```json
{
  "browser_share": {
    "categories": ["Chrome", "Firefox", "Safari", "Edge"],
    "series": [40, 30, 20, 10]
  }
}
```

---

### 6. Table & Leaderboard
Tablas responsivas. `leaderboard` añade automáticamente iconos de medallas a la primera columna numérica o de índice.

**Configuración Panel:**
```json
{
  "type": "leaderboard", // o "table"
  "key": "top_vendors",
  "label": "Mejores Vendedores",
  "columns": [
    { "key": "#", "label": "Rank" }, // Use '#' genérico para ranking automático
    { "key": "name", "label": "Nombre" },
    { "key": "total", "label": "Ventas" }
  ]
}
```

**Respuesta API:**
```json
{
  "top_vendors": [
    { "name": "Ana", "total": "$5,000" },
    { "name": "Carlos", "total": "$4,200" },
    { "name": "Sofia", "total": "$3,800" }
  ]
}
```

---

### 7. Timeline
Lista vertical de eventos para logs de actividad o auditoría.

**Configuración Panel:**
```json
{
  "type": "timeline",
  "key": "deploy_history",
  "label": "Historial de Despliegues"
}
```

**Respuesta API:**
```json
{
  "deploy_history": [
    { 
      "time": "10:30 AM", 
      "title": "Versión 2.1 Lanzada", 
      "desc": "Corrección de bugs críticos", 
      "type": "success" // success, error, info
    },
    { 
      "time": "09:15 AM", 
      "title": "Build Fallido", 
      "type": "error" 
    }
  ]
}
```

### 8. Log Stream
Visor compacto para trazas técnicas.

**Configuración Panel:**
```json
{
  "type": "log-stream",
  "key": "app_logs",
  "label": "Logs del Sistema"
}
```

**Respuesta API:**
```json
{
  "app_logs": [
    { "time": "12:00:01", "level": "info", "message": "Cron job started" },
### 9. Layout & Utilities
Widgets especiales para controlar la distribución y mostrar información estática.

#### Info Card
Tarjeta estática que no realiza peticiones a la API. Útil para instrucciones o estados fijos.
```json
{
  "type": "info",
  "label": "Mantenimiento",
  "value": "Programado para el Viernes", // Opcional
  "desc": "22:00 - 02:00 UTC", // Opcional
  "status": "warning" // success, warning, danger, info
}
```

#### Break (Salto de Línea)
Fuerza que los siguientes widgets comiencen en una nueva fila. Útil para agrupar visualmente sin crear múltiples dashboards.
```json
{ "type": "break" }
```

#### None (Oculto)
Widget "fantasma" que no se renderiza. Se utiliza para ajustar la distribución automática del grid.
Por ejemplo, si tienes 4 elementos en una fila (25% cada uno), puedes cambiar uno a `none` para que los 3 restantes ocupen el 33% (gracias al ajuste automático `auto-fit`).
```json
{ "type": "none" }
```
```
 
 ## Configuración de Refresco (Auto-Refresh)

En la configuración de la página (fuera del JSON), puedes definir el **Refresh Rate** en segundos.
*   **Valor 0**: Desactiva el refresco automático (solo carga al abrir).
*   **Valor recomendad**: 60 (un minuto) para evitar sobrecargar los servicios upstream.

## Ejemplo de Datos del Upstream (Respuesta del API)

Para que el ejemplo anterior funcione, el servicio upstream deba responder algo como esto:

```json
{
  "uptime": "14 días",
  "status": {
    "memory_usage": "2.4 GB"
  },
  "active_users": [
    { "name": "Admin", "last_seen": "Hace 2 min" },
    { "name": "Editor_1", "last_seen": "Hace 10 min" }
  ]
}
```

## Ejemplo Completo: Monitoreo de Infraestructura
 
 **Configuración JSON:**
 ```json
 {
  "title": "Estado del Sistema",
  "widgets": [
    {
      "key": "server.cpu_usage",
      "label": "Carga CPU",
      "type": "progress-card"
    },
    {
      "key": "server.status_message",
      "label": "Estado Global",
      "type": "alert-card",
      "status": "success"
    },
    {
      "key": "recent_logs",
      "label": "Logs Recientes",
      "type": "table",
      "columns": [
        { "key": "time", "label": "Hora" },
        { "key": "event", "label": "Evento" }
      ]
    },
    {
      "key": "custom_footer_html",
      "type": "html-card",
      "span": 2
    }
  ]
 }
 ```
