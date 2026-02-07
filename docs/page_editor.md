# Page Editor - Visual Builder

The page editor provides a Visual Composer-style drag-and-drop interface for creating and editing pages without writing JSON manually.

## Overview

When creating or editing a page in the panel (`/panel/pages/{id}/edit`), you'll see a visual builder interface instead of a raw JSON textarea. By default, it uses the **Visual Builder**, but you can toggle the **⚙️ Advanced (JSON)** view if needed.

## Interface Components

### 1. Row Management & Structure
- Click **"➕ Add Row"** to add a new content row.
- **Visual Structure Picker**: Click the **"📐 Structure"** button on any row to open the selection modal.
  - **Step 1**: Choose the number of columns (1 to 6).
  - **Step 2**: Choose a visual layout pattern (e.g., symmetric 50/50, or asymmetric 25/50/25).
- Rows can be reordered via the **☰ drag-and-drop handles** on the left.

### 2. Widget & Field Selection
Each column contains a widget selector dropdown. Available widgets depend on page type.

**Dashboard Widgets:**
- `kpi`: Stat cards with trend indicators.
- `chart`: Interactive charts (Line, Bar, Area, Donut, Pie).
- `table`: Dynamic data tables with defined columns.
- `leaderboard`, `gauge`, `progress-card`, `alert-card`, etc.
- `info`: Static text cards for supplementary info.

**Form Fields:**
- Forms now support the **same multi-column grid** as dashboards.
- Available fields: `text`, `textarea`, `select`, `file`, `rating`, `number`, `email`, `date`.

### 3. Smart Drag-and-Drop Interaction
- **Reorder Rows**: Drag the **☰ handle** to move an entire row.
- **Smart Move Widgets**: 
    - **Insert & Shift**: Dropping onto an **occupied** slot inserts the widget and shifts others.
    - **Replace**: Dropping onto an **empty placeholder** replaces that slot, preserving the current column count.
    - **Reorder**: Same-row moves are intelligently handled to prevent index conflicts.
    - **Auto-Cleanup**: The source column is always removed, and empty rows are deleted.
- **Visual Feedback**: Real-time drop indicators and hover states guide your placements.

### 4. Available Fields Palette
When you click **"Run Test Call"** in the Destination Configuration:
- JSON keys from the response appear in the **"📦 Available Fields"** palette.
- Click any key to auto-fill the "Key" (Dashboard) or "Name" (Form) setting of the **selected widget** (highlighted with a blue border).

---

## Technical Side

### Layout Persistence
The layout is stored within the configuration JSON using special markers:

1.  **`break` widgets**: These markers define where a new row starts.
2.  **`layout` property**: Stored in the `break` widget to define the grid template (e.g., `"layout": "1fr 2fr 1fr"`).
3.  **`none` type**: Empty slots in the grid are stored as `{ "type": "none" }` to preserve the grid structure upon re-editing.

### Grid Implementation
The system uses **CSS Grid** with dynamic variables:
- `--cols`: Number of equal-width columns.
- `--layout`: Custom `grid-template-columns` pattern for asymmetric rows.

The `.dashboard-row` class in `panel-base.css` handles this logic:
```css
.dashboard-row {
    display: grid;
    grid-template-columns: var(--layout, repeat(var(--cols, 1), minmax(0, 1fr)));
    gap: 1.5rem;
}
```

---

## Example JSON Structure

```json
{
  "title": "Customer Overview",
  "widgets": [
    { "type": "kpi", "key": "total", "label": "Total" },
    { "type": "kpi", "key": "active", "label": "Active" },
    { "type": "break", "layout": "1fr 2fr" },
    { "type": "chart", "key": "history", "label": "Growth", "subtype": "area" },
    { "type": "none" }
  ]
}
```

---

## Tips & Best Practices
- **Multi-column Forms**: Use columns in forms to group related fields (e.g., City and Zip on the same row).
- **Empty Slots**: You can leave columns empty to create specific alignments; the front-end will skip rendering them but honor the space.
- **Nested Data**: Use dot notation (e.g., `results[0].meta.value`) for the data keys.
- **Performance**: Charts and complex widgets are optimized for re-renders within the layout.
