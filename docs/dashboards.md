# 📊 Dashboards Guide

Dashboards allow you to visualize information in real-time coming from an external service. Laracloak periodically queries the upstream and automatically updates the visual components.

## Configuration Structure (JSON)

The Dashboard engine looks for specific keys in the upstream JSON response to populate the widgets.

### Basic Schema
```json
{
  "title": "Server Status",
  "widgets": [
    {
      "key": "uptime",
      "label": "Active Time",
      "type": "stat-card",
      "desc": "Days since last reboot"
    },
    {
      "key": "status.memory_usage",
      "label": "RAM Usage",
      "type": "stat-card"
    },
    {
      "key": "active_users",
      "label": "Users Connected now",
      "type": "table",
      "columns": [
        { "key": "name", "label": "Name" },
        { "key": "last_seen", "label": "Last seen" }
      ]
    }
  ]
}
```

## 📚 Widget Library

Below are the 13 supported widget types. Use the value from the **Type** column in your JSON configuration.

| Widget | Type (`type`) | Ideal Use |
| :--- | :--- | :--- |
| **KPI / Stat Card** | `kpi` or `stat-card` | Individual metrics (Sales, Users). |
| **Sparkline** | `sparkline` | KPI with simple trend chart. |
| **Progress Card** | `progress-card` | Loading or completion percentage (0-100%). |
| **Alert Card** | `alert-card` | System status (OK, Error, Warning). |
| **Line Chart** | `chart` (subtype: `line`) | Temporal trends (Traffic, CPU). |
| **Bar Chart** | `chart` (subtype: `bar`) | Comparisons by category or time. |
| **Area Chart** | `chart` (subtype: `area`) | Cumulative volume over time. |
| **Donut Chart** | `chart` (subtype: `donut`) | Simple composition (traffic sources). |
| **Pie Chart** | `chart` (subtype: `pie`) | Similar to Donut, different style. |
| **Gauge / Radial** | `chart` (subtype: `gauge`) | Circular meter (Speed, % Usage). |
| **Table** | `table` | Tabular data listings. |
| **Leaderboard** | `leaderboard` | Table with ranking (🥇 🥈 🥉). |
| **Timeline** | `timeline` | Vertical list of events/milestones. |
| **Log Stream** | `log-stream` | Monitor system logs and traces. |
| **HTML Card** | `html-card` | Free content (Disabled by default). |
| **Informative** | `info` | Static card with text and status. |
| **Separator** | `break` | Forced line break in the grid. |
| **Hidden** | `none` | Invisible element to adjust widths. |

---

## 🛠️ Configuration and API Detail

### 1. KPI / Stat Card
Displays a primary value and optionally a change (delta) relative to the previous period.

**Panel Configuration:**
```json
{
  "type": "kpi",
  "key": "sales_today",
  "label": "Sales Today",
  "desc": "vs yesterday"
}
```

**API Response (Simple Option):**
```json
{ "sales_today": "$1,250" }
```

**API Response (Full Option):**
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
KPI that includes a small line chart to show the recent trend.

**Panel Configuration:**
```json
{
  "type": "sparkline",
  "key": "memory_trend",
  "label": "Memory Usage"
}
```

**API Response:**
```json
{
  "memory_trend": {
    "value": "64%",
    "history": [45, 46, 50, 55, 60, 64, 62, 64] // Array of points
  }
}
```

---

### 3. Progress Card
Visual progress bar.

**Panel Configuration:**
```json
{
  "type": "progress-card",
  "key": "disk_usage",
  "label": "Disk Space"
}
```

**API Response:**
```json
{
  "disk_usage": 78 // Numerical value 0-100
}
```

---

### 4. Alert Card
Highlighted card to communicate the health status of a system.

**Panel Configuration:**
```json
{
  "type": "alert-card",
  "key": "api_status",
  "label": "API Status",
  "status": "success" // Default status if the API doesn't send it
}
```

**API Response:**
```json
{
  "api_status": {
    "title": "Operational",
    "status": "success", // success, warning, danger, info
    "time": "5 min ago" // Optional
  }
}
```

---

### 5. Charts
The engine uses ApexCharts. All share the base structure, changing the `subtype`.

#### Line / Bar / Area
**Panel Configuration:**
```json
{
  "type": "chart",
  "subtype": "line", // or "bar", "area"
  "key": "web_traffic",
  "label": "Web Traffic",
  "height": 300 // Optional
}
```

**API Response:**
```json
{
  "web_traffic": {
    "categories": ["Mon", "Tue", "Wed", "Thu", "Fri"],
    "series": [
      { "name": "Visits", "data": [120, 400, 350, 500, 480] }
    ]
  }
}
```

#### Donut / Pie / Gauge
**Panel Configuration:**
```json
{
  "type": "chart",
  "subtype": "donut", // or "pie", "gauge"
  "key": "browser_share",
  "label": "Browsers"
}
```

**API Response (Simple):**
```json
{ "browser_share": [40, 30, 20, 10] } // Numerical values
```

**API Response (Named):**
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
Responsive tables. `leaderboard` automatically adds medal icons to the first numeric or index column.

**Panel Configuration:**
```json
{
  "type": "leaderboard", // or "table"
  "key": "top_vendors",
  "label": "Top Vendors",
  "columns": [
    { "key": "#", "label": "Rank" }, // Use generic '#' for automatic ranking
    { "key": "name", "label": "Name" },
    { "key": "total", "label": "Sales" }
  ]
}
```

**API Response:**
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
Vertical list of events for activity logs or audit.

**Panel Configuration:**
```json
{
  "type": "timeline",
  "key": "deploy_history",
  "label": "Deployment History"
}
```

**API Response:**
```json
{
  "deploy_history": [
    { 
      "time": "10:30 AM", 
      "title": "Version 2.1 Released", 
      "desc": "Critical bug fixes", 
      "type": "success" // success, error, info
    },
    { 
      "time": "09:15 AM", 
      "title": "Build Failed", 
      "type": "error" 
    }
  ]
}
```

---

### 8. Log Stream
Compact viewer for technical traces.

**Panel Configuration:**
```json
{
  "type": "log-stream",
  "key": "app_logs",
  "label": "System Logs"
}
```

**API Response:**
```json
{
  "app_logs": [
    { "time": "12:00:01", "level": "info", "message": "Cron job started" },
    { "time": "12:00:05", "level": "error", "message": "Connection timeout" }
  ]
}
```

---

### 9. Layout & Utilities
Special widgets to control distribution and show static information.

#### Info Card
Static card that doesn't make API requests. Useful for instructions or fixed status.
```json
{
  "type": "info",
  "label": "Maintenance",
  "value": "Scheduled for Friday", // Optional
  "desc": "22:00 - 02:00 UTC", // Optional
  "status": "warning" // success, warning, danger, info
}
```

#### Break (Line Break)
Forces subsequent widgets to start on a new row. Useful for visual grouping without creating multiple dashboards.
```json
{ "type": "break" }
```

#### None (Hidden)
"Ghost" widget that doesn't render. Used to adjust automatic grid distribution.
For example, if you have 4 elements in a row (25% each), you can change one to `none` so that the remaining 3 take up 33% (thanks to the `auto-fit` adjustment).
```json
{ "type": "none" }
```

## Refresh Configuration (Auto-Refresh)

In the page settings (outside the JSON), you can define the **Refresh Rate** in seconds.
*   **Value 0**: Disables automatic refresh (only loads on open).
*   **Recommended value**: 60 (one minute) to avoid overcharging upstream services.

## Upstream Data Example (API Response)

For the previous example to work, the upstream service must respond with something like this:

```json
{
  "uptime": "14 days",
  "status": {
    "memory_usage": "2.4 GB"
  },
  "active_users": [
    { "name": "Admin", "last_seen": "2 min ago" },
    { "name": "Editor_1", "last_seen": "10 min ago" }
  ]
}
```

## Complete Example: Infrastructure Monitoring

**JSON Configuration:**
```json
{
  "title": "System Status",
  "widgets": [
    {
      "key": "server.cpu_usage",
      "label": "CPU Load",
      "type": "progress-card"
    },
    {
      "key": "server.status_message",
      "label": "Global Status",
      "type": "alert-card",
      "status": "success"
    },
    {
      "key": "recent_logs",
      "label": "Recent Logs",
      "type": "table",
      "columns": [
        { "key": "time", "label": "Time" },
        { "key": "event", "label": "Event" }
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
