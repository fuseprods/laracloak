# Editor de Páginas - Constructor Visual

El editor de páginas proporciona una interfaz de arrastrar y soltar estilo "Visual Composer" para crear y editar páginas sin escribir JSON manualmente.

## Descripción General

Al crear o editar una página en el panel (`/panel/pages/{id}/edit`), verás una interfaz de constructor visual en lugar de un área de texto JSON sin formato. Por defecto, utiliza el **Visual Builder**, pero puedes activar la vista **⚙️ Advanced (JSON)** si es necesario.

## Componentes de la Interfaz

### 1. Gestión de Filas y Estructura
- Haz clic en **"➕ Añadir Fila"** para añadir una nueva fila de contenido.
- **Selector de Estructura Visual**: Haz clic en el botón **"📐 Estructura"** en cualquier fila para abrir el modal de selección.
  - **Paso 1**: Elige el número de columnas (de 1 a 6).
  - **Paso 2**: Elige un patrón de diseño visual (ej: simétrico 50/50, o asimétrico 25/50/25).
- Las filas se pueden reordenar mediante los **asideros de arrastrar y soltar ☰** a la izquierda.

### 2. Selección de Widgets y Campos
Cada columna contiene un selector desplegable de widgets. Los widgets disponibles dependen del tipo de página.

**Widgets de Dashboard:**
- `kpi`: Tarjetas de estadísticas con indicadores de tendencia.
- `chart`: Gráficos interactivos (Línea, Barras, Área, Donut, Tarta).
- `table`: Tablas de datos dinámicas con columnas definidas.
- `leaderboard`, `gauge`, `progress-card`, `alert-card`, etc.
- `info`: Tarjetas de texto estático para información suplementaria.

**Campos de Formulario:**
- Los formularios ahora soportan la **misma rejilla de varias columnas** que los dashboards.
- Campos disponibles: `text`, `textarea`, `select`, `file`, `rating`, `number`, `email`, `date`.

### 3. Interacción Inteligente de Arrastrar y Soltar
- **Reordenar Filas**: Arrastra el **asidero ☰** para mover una fila completa.
- **Movimiento Inteligente de Widgets**: 
    - **Insertar y Desplazar**: Soltar sobre un espacio **ocupado** inserta el widget y desplaza los demás.
    - **Reemplazar**: Soltar sobre un **marcador vacío** reemplaza ese espacio, preservando el número de columnas actual.
    - **Reordenar**: Los movimientos en la misma fila se gestionan inteligentemente para evitar conflictos de índice.
    - **Limpieza Automática**: La columna de origen siempre se elimina y las filas vacías se borran.
- **Feedback Visual**: Indicadores de soltado en tiempo real y estados hover guían tus colocaciones.

### 4. Paleta de Campos Disponibles
Cuando haces clic en **"Ejecutar Prueba"** en la Configuración de Destino:
- Las claves JSON de la respuesta aparecen en la paleta de **"📦 Campos Disponibles"**.
- Haz clic en cualquier clave para autocompletar la "Clave" (Dashboard) o el "Nombre" (Formulario) del **widget seleccionado** (resaltado con un borde azul).

---

## Lado Técnico

### Persistencia del Diseño
El diseño se almacena dentro del JSON de configuración mediante marcadores especiales:

1.  **Widgets `break`**: Estos marcadores definen dónde comienza una nueva fila.
2.  **Propiedad `layout`**: Almacenada en el widget `break` para definir la plantilla de la rejilla (ej: `"layout": "1fr 2fr 1fr"`).
3.  **Tipo `none`**: Los espacios vacíos en la rejilla se almacenan como `{ "type": "none" }` para preservar la estructura al volver a editar.

### Implementación de la Rejilla
El sistema utiliza **CSS Grid** con variables dinámicas:
- `--cols`: Número de columnas de igual ancho.
- `--layout`: Patrón de `grid-template-columns` personalizado para filas asimétricas.

La clase `.dashboard-row` en `panel-base.css` gestiona esta lógica:
```css
.dashboard-row {
    display: grid;
    grid-template-columns: var(--layout, repeat(var(--cols, 1), minmax(0, 1fr)));
    gap: 1.5rem;
}
```

---

## Ejemplo de Estructura JSON

```json
{
  "title": "Resumen de Clientes",
  "widgets": [
    { "type": "kpi", "key": "total", "label": "Total" },
    { "type": "kpi", "key": "active", "label": "Activos" },
    { "type": "break", "layout": "1fr 2fr" },
    { "type": "chart", "key": "history", "label": "Crecimiento", "subtype": "area" },
    { "type": "none" }
  ]
}
```

---

## Consejos y Mejores Prácticas
- **Formularios Multicolumna**: Usa columnas en los formularios para agrupar campos relacionados (ej: Ciudad y Código Postal en la misma fila).
- **Espacios Vacíos**: Puedes dejar columnas vacías para crear alineaciones específicas; el front-end omitirá su renderizado pero respetará el espacio.
- **Datos Anidados**: Usa la notación de punto (ej: `results[0].meta.value`) para las claves de datos.
- **Rendimiento**: Los gráficos y widgets complejos están optimizados para re-renderizados dentro del diseño.
