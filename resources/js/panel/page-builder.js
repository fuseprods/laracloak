/**
 * Page Builder - Visual Composer-Style Editor
 * 
 * Provides a drag-and-drop interface for creating page configurations
 * without writing JSON manually.
 */

class PageBuilder {
    constructor(container, options = {}) {
        this.container = container;
        this.pageType = options.pageType || 'dashboard';
        this.initialConfig = options.config || {};
        this.onConfigChange = options.onConfigChange || (() => { });
        this.translations = options.translations || {};

        this.rows = [];
        this.availableFields = [];
        this.availablePayload = null;
        this.activePaletteTab = 'schema';
        this.expandedSchemaPaths = new Set();
        this.selectedWidget = null;

        this.widgetTypes = this.pageType === 'dashboard'
            ? this.getDashboardWidgetTypes()
            : this.getFormFieldTypes();

        this.init();
    }

    __(key, replacements = {}) {
        let text = this.translations[key] || key;
        for (const [placeholder, value] of Object.entries(replacements)) {
            text = text.replace(`:${placeholder}`, value);
        }
        return text;
    }

    getDashboardWidgetTypes() {
        return [
            { value: '', label: this.__('-- Select Widget --'), settings: [] },
            { value: 'kpi', label: this.__('📊 KPI / Stat Card'), settings: ['key', 'label', 'desc'] },
            { value: 'sparkline', label: this.__('📈 Sparkline'), settings: ['key', 'label'] },
            { value: 'progress-card', label: this.__('📶 Progress Bar'), settings: ['key', 'label'] },
            { value: 'alert-card', label: this.__('🚨 Alert Card'), settings: ['key', 'label', 'status'] },
            { value: 'chart', label: this.__('📉 Chart'), settings: ['key', 'label', 'subtype', 'height'] },
            { value: 'gauge', label: this.__('🎯 Gauge'), settings: ['key', 'label', 'units'] },
            { value: 'table', label: this.__('📋 Table'), settings: ['key', 'label', 'columns'] },
            { value: 'leaderboard', label: this.__('🏆 Leaderboard'), settings: ['key', 'label', 'columns'] },
            { value: 'timeline', label: this.__('📅 Timeline'), settings: ['key', 'label'] },
            { value: 'log-stream', label: this.__('📜 Log Stream'), settings: ['key', 'label'] },
            { value: 'info', label: this.__('ℹ️ Info Card (Static)'), settings: ['label', 'value', 'desc', 'status'] },
            { value: 'break', label: this.__('↩️ Row Break'), settings: [] },
        ];
    }

    getFormFieldTypes() {
        return [
            { value: '', label: this.__('-- Select Field Type --'), settings: [] },
            { value: 'text', label: this.__('📝 Text Input'), settings: ['name', 'label', 'placeholder', 'required'] },
            { value: 'textarea', label: this.__('📄 Text Area'), settings: ['name', 'label', 'placeholder', 'required'] },
            { value: 'select', label: this.__('📋 Dropdown'), settings: ['name', 'label', 'options', 'required'] },
            { value: 'file', label: this.__('📎 File Upload'), settings: ['name', 'label', 'required'] },
            { value: 'rating', label: this.__('⭐ Star Rating'), settings: ['name', 'label', 'required'] },
            { value: 'number', label: this.__('🔢 Number'), settings: ['name', 'label', 'placeholder', 'required'] },
            { value: 'email', label: this.__('📧 Email'), settings: ['name', 'label', 'placeholder', 'required'] },
            { value: 'date', label: this.__('📆 Date'), settings: ['name', 'label', 'required'] },
        ];
    }

    init() {
        this.render();
        this.loadConfig(this.initialConfig);
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="page-builder">
                <div class="pb-header">
                    <div class="pb-title-group">
                        <label for="pb-title">📄 ${this.pageType === 'dashboard' ? this.__('Dashboard Title') : this.__('Form Title')}</label>
                        <input type="text" id="pb-title" class="pb-title-input" placeholder="${this.__('Enter title...')}">
                    </div>
                    ${this.pageType === 'form' ? `
                        <div class="pb-title-group">
                            <label for="pb-description">${this.__('Description')}</label>
                            <input type="text" id="pb-description" class="pb-title-input" placeholder="${this.__('Optional description...')}">
                        </div>
                        <div class="pb-title-group">
                            <label for="pb-submit-label">${this.__('Submit Button Text')}</label>
                            <input type="text" id="pb-submit-label" class="pb-title-input" value="${this.__('Submit')}" placeholder="${this.__('Submit')}">
                        </div>
                    ` : ''}
                </div>
                
                <div class="pb-palette" id="pb-palette" style="display: none;">
                    <div class="pb-palette-header">
                        <span>📦 ${this.__('Available Input')}</span>
                        <span class="pb-palette-hint">${this.__('Drag a field to a widget or click to assign it')}</span>
                    </div>
                    <div class="pb-palette-tabs" id="pb-palette-tabs">
                        <button type="button" class="pb-palette-tab active" data-tab="schema">${this.__('Schema')}</button>
                        <button type="button" class="pb-palette-tab" data-tab="json">${this.__('JSON')}</button>
                    </div>
                    <div class="pb-palette-content">
                        <div class="pb-palette-view active" id="pb-palette-schema"></div>
                        <div class="pb-palette-view" id="pb-palette-json"></div>
                    </div>
                </div>
                
                <div class="pb-rows" id="pb-rows"></div>
                
                <button type="button" class="pb-add-row-btn" id="pb-add-row">
                    <span>➕</span> ${this.__('Add Row')}
                </button>
            </div>
        `;
    }

    bindEvents() {
        // Add row button
        this.container.querySelector('#pb-add-row').addEventListener('click', () => {
            this.addRow();
        });

        // Title/description changes
        this.container.querySelector('#pb-title').addEventListener('input', () => this.syncConfig());
        if (this.pageType === 'form') {
            this.container.querySelector('#pb-description').addEventListener('input', () => this.syncConfig());
            this.container.querySelector('#pb-submit-label').addEventListener('input', () => this.syncConfig());
        }

        // Delegate events for dynamic elements
        this.container.addEventListener('click', (e) => {
            const target = e.target;

            // Delete row
            if (target.closest('.pb-row-delete')) {
                const rowEl = target.closest('.pb-row');
                const rowId = rowEl.dataset.rowId;
                this.deleteRow(rowId);
            }

            // Delete widget
            if (target.closest('.pb-widget-delete')) {
                const widgetEl = target.closest('.pb-widget');
                const rowId = widgetEl.closest('.pb-row').dataset.rowId;
                const colIndex = parseInt(widgetEl.dataset.colIndex);
                this.deleteWidget(rowId, colIndex);
            }

            // Palette tab switch
            if (target.closest('.pb-palette-tab')) {
                const tab = target.closest('.pb-palette-tab').dataset.tab;
                this.switchPaletteTab(tab);
                return;
            }

            // Schema node toggle
            if (target.closest('.pb-schema-toggle')) {
                const path = target.closest('.pb-schema-toggle').dataset.path;
                this.toggleSchemaPath(path);
                return;
            }

            // Palette field click
            if (target.closest('.pb-field-item')) {
                const field = target.closest('.pb-field-item').dataset.field;
                this.applyFieldToSelectedWidget(field);
                return;
            }

            // Widget selection for palette
            if (target.closest('.pb-widget') && !target.closest('.pb-widget-delete') && !target.closest('.pb-widget-type') && !target.closest('.pb-widget-setting')) {
                this.selectWidget(target.closest('.pb-widget'));
            }

            if (target.closest('.pb-structure-btn')) {
                const rowEl = target.closest('.pb-row');
                const rowId = rowEl.dataset.rowId;
                this.openLayoutPicker(rowId);
            }
        });

        // Layout change
        this.container.addEventListener('change', (e) => {
            const target = e.target;

            if (target.classList.contains('pb-layout-select')) {
                const rowEl = target.closest('.pb-row');
                const rowId = rowEl.dataset.rowId;
                const value = target.value;

                if (value.includes('fr')) {
                    // Asymmetric layout
                    const columns = value.split(' ').length;
                    this.changeRowLayout(rowId, columns, value);
                } else {
                    // Simple columns
                    this.changeRowLayout(rowId, parseInt(value), null);
                }
            }

            if (target.classList.contains('pb-widget-type')) {
                const widgetEl = target.closest('.pb-widget');
                const rowId = widgetEl.closest('.pb-row').dataset.rowId;
                const colIndex = parseInt(widgetEl.dataset.colIndex);
                this.changeWidgetType(rowId, colIndex, target.value);
            }

            // Any setting change
            if (target.classList.contains('pb-widget-setting')) {
                this.syncConfig();
            }
        });

        // Input changes for settings
        this.container.addEventListener('input', (e) => {
            if (e.target.classList.contains('pb-widget-setting')) {
                this.syncConfig();
            }
        });

        // ===== DRAG AND DROP FOR ROWS =====
        this.container.addEventListener('dragstart', (e) => {
            const fieldEl = e.target.closest('.pb-field-item');
            const rowHandle = e.target.closest('.pb-row-handle');
            const widgetEl = e.target.closest('.pb-widget');

            if (fieldEl) {
                const field = fieldEl.dataset.field;
                if (!field) return;

                fieldEl.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'copyMove';
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    type: 'field',
                    field: field
                }));
            } else if (rowHandle) {
                const rowEl = rowHandle.closest('.pb-row');
                rowEl.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    type: 'row',
                    rowId: rowEl.dataset.rowId
                }));
            } else if (widgetEl && !e.target.closest('select') && !e.target.closest('input')) {
                widgetEl.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    type: 'widget',
                    rowId: widgetEl.closest('.pb-row').dataset.rowId,
                    colIndex: parseInt(widgetEl.dataset.colIndex)
                }));
            }
        });

        this.container.addEventListener('dragend', (e) => {
            this.container.querySelectorAll('.dragging').forEach(el => el.classList.remove('dragging'));
            this.container.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
            this.container.querySelectorAll('.drag-over-top, .drag-over-bottom').forEach(el => {
                el.classList.remove('drag-over-top', 'drag-over-bottom');
            });
        });

        this.container.addEventListener('dragover', (e) => {
            e.preventDefault();

            const rowEl = e.target.closest('.pb-row');
            const widgetEl = e.target.closest('.pb-widget');

            if (widgetEl && !widgetEl.classList.contains('dragging')) {
                e.dataTransfer.dropEffect = 'move';
                // Clear other highlights
                this.container.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
                widgetEl.classList.add('drag-over');
            } else if (rowEl && !rowEl.classList.contains('dragging')) {
                e.dataTransfer.dropEffect = 'move';
                // Determine position (top or bottom half)
                const rect = rowEl.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;

                this.container.querySelectorAll('.drag-over-top, .drag-over-bottom').forEach(el => {
                    el.classList.remove('drag-over-top', 'drag-over-bottom');
                });

                if (e.clientY < midY) {
                    rowEl.classList.add('drag-over-top');
                } else {
                    rowEl.classList.add('drag-over-bottom');
                }
            }
        });

        this.container.addEventListener('dragleave', (e) => {
            const widgetEl = e.target.closest('.pb-widget');
            const rowEl = e.target.closest('.pb-row');
            if (widgetEl) widgetEl.classList.remove('drag-over');
            if (rowEl) rowEl.classList.remove('drag-over-top', 'drag-over-bottom');
        });

        this.container.addEventListener('drop', (e) => {
            e.preventDefault();

            let data;
            try {
                data = JSON.parse(e.dataTransfer.getData('text/plain'));
            } catch (err) {
                return;
            }

            if (data.type === 'row') {
                this.handleRowDrop(data, e);
            } else if (data.type === 'widget') {
                this.handleWidgetDrop(data, e);
            } else if (data.type === 'field') {
                this.handleFieldDrop(data, e);
            }

            // Clean up
            this.container.querySelectorAll('.dragging, .drag-over, .drag-over-top, .drag-over-bottom').forEach(el => {
                el.classList.remove('dragging', 'drag-over', 'drag-over-top', 'drag-over-bottom');
            });
        });
    }

    handleRowDrop(data, e) {
        const targetRowEl = e.target.closest('.pb-row');
        if (!targetRowEl) return;

        const sourceRowId = data.rowId;
        const targetRowId = targetRowEl.dataset.rowId;

        if (sourceRowId === targetRowId) return;

        const sourceIndex = this.rows.findIndex(r => r.id === sourceRowId);
        const targetIndex = this.rows.findIndex(r => r.id === targetRowId);

        if (sourceIndex === -1 || targetIndex === -1) return;

        // Remove source row
        const [sourceRow] = this.rows.splice(sourceIndex, 1);

        // Determine insert position based on drop position
        const rect = targetRowEl.getBoundingClientRect();
        const midY = rect.top + rect.height / 2;
        let insertIndex = this.rows.findIndex(r => r.id === targetRowId);

        if (e.clientY > midY) {
            insertIndex++;
        }

        // Insert at new position
        this.rows.splice(insertIndex, 0, sourceRow);

        this.renderRows();
        this.syncConfig();
    }

    handleWidgetDrop(data, e) {
        const targetWidgetEl = e.target.closest('.pb-widget');
        if (!targetWidgetEl) return;

        const sourceRowId = data.rowId;
        const sourceColId = data.colIndex;
        const targetRowId = targetWidgetEl.closest('.pb-row').dataset.rowId;
        const targetColIndex = parseInt(targetWidgetEl.dataset.colIndex);

        // Don't drop on itself
        if (sourceRowId === targetRowId && sourceColId === targetColIndex) return;

        const sourceRow = this.rows.find(r => r.id === sourceRowId);
        const targetRow = this.rows.find(r => r.id === targetRowId);
        if (!sourceRow || !targetRow) return;

        const targetWidget = targetRow.widgets[targetColIndex];
        const isTargetOccupied = targetWidget && targetWidget.type && targetWidget.type !== 'none';

        // 6-column limit validation (only for insertions)
        if (isTargetOccupied && targetRow.columns >= 6 && sourceRowId !== targetRowId) {
            alert(this.__("Maximum of 6 columns reached for this row."));
            return;
        }

        // 1. Extract source widget
        const [sourceWidget] = sourceRow.widgets.splice(sourceColId, 1);
        sourceRow.columns = sourceRow.widgets.length;
        sourceRow.layout = null;

        // 2. Adjust target index if same row
        let adjustedTargetIndex = targetColIndex;
        if (sourceRowId === targetRowId && sourceColId < targetColIndex) {
            adjustedTargetIndex--;
        }

        // 3. Place in target
        if (isTargetOccupied) {
            // INSERT & SHIFT
            targetRow.widgets.splice(adjustedTargetIndex, 0, sourceWidget);
        } else {
            // REPLACE PLACEHOLDER
            // Since we spliced the source, the row shifted. 
            // Dropping on an empty slot should just fill it, BUT we want to remove the source too.
            // If we replace, the row column count stays the same as it was before the move.
            // However, our general rule is "Always remove source".
            // So if we replace an empty slot, the target row count stays the same, 
            // and the source row count decreases.
            targetRow.widgets[adjustedTargetIndex] = sourceWidget;
        }

        targetRow.columns = targetRow.widgets.length;
        targetRow.layout = null;

        // 4. Automatic cleanup
        if (sourceRow.columns === 0 && this.rows.length > 1) {
            this.rows = this.rows.filter(r => r.id !== sourceRowId);
        }

        this.renderRows();
        this.syncConfig();
    }

    handleFieldDrop(data, e) {
        const targetWidgetEl = e.target.closest('.pb-widget');
        if (!targetWidgetEl) return;

        const field = data.field;
        if (!field) return;

        const rowId = targetWidgetEl.closest('.pb-row').dataset.rowId;
        const colIndex = parseInt(targetWidgetEl.dataset.colIndex);
        this.applyFieldToWidget(rowId, colIndex, field);
    }

    loadConfig(config) {
        // Set title
        const titleInput = this.container.querySelector('#pb-title');
        titleInput.value = config.title || '';

        const isForm = this.pageType === 'form';
        if (isForm) {
            this.container.querySelector('#pb-description').value = config.description || '';
            this.container.querySelector('#pb-submit-label').value = config.submit_label || 'Submit';
        }

        const items = isForm ? (config.fields || []) : (config.widgets || []);
        if (items.length === 0) return;

        let groups = [];
        let currentGroup = [];
        let currentLayout = null;

        items.forEach(item => {
            if (item.type === 'break') {
                groups.push({ items: currentGroup, layout: currentLayout });
                currentGroup = [];
                currentLayout = item.layout || null;
            } else {
                currentGroup.push(item);
            }
        });
        groups.push({ items: currentGroup, layout: currentLayout });

        groups.forEach((group, index) => {
            if (group.items.length > 0 || group.layout) {
                // Determine row columns based on layout or group length (up to 6)
                let groupSize = 1;
                if (group.layout) {
                    groupSize = group.layout.split(' ').length;
                } else {
                    groupSize = Math.max(1, Math.min(6, group.items.length));
                }

                const row = this.addRow(groupSize, false);
                row.layout = group.layout;

                group.items.forEach((item, i) => {
                    if (i < groupSize) {
                        row.widgets[i] = item.type === 'none' ? {} : { ...item };
                    }
                });
            }
        });

        this.renderRows();
    }

    addRow(columns = 2, render = true) {
        const row = {
            id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            columns: columns,
            widgets: Array(columns).fill(null).map(() => ({}))
        };
        this.rows.push(row);

        if (render) {
            this.renderRows();
            this.syncConfig();
        }

        return row;
    }

    deleteRow(rowId) {
        this.rows = this.rows.filter(r => r.id !== rowId);
        this.renderRows();
        this.syncConfig();
    }

    changeRowLayout(rowId, columns, layout = null) {
        const row = this.rows.find(r => r.id === rowId);
        if (!row) return;

        const oldWidgets = row.widgets;
        row.columns = columns;
        row.layout = layout;
        row.widgets = Array(columns).fill(null).map((_, i) => oldWidgets[i] || {});

        this.renderRows();
        this.syncConfig();
    }

    changeWidgetType(rowId, colIndex, type) {
        const row = this.rows.find(r => r.id === rowId);
        if (!row) return;

        const widgetDef = this.widgetTypes.find(w => w.value === type);
        row.widgets[colIndex] = { type: type };

        this.renderRows();
        this.syncConfig();
    }

    deleteWidget(rowId, colIndex) {
        const row = this.rows.find(r => r.id === rowId);
        if (!row) return;

        row.widgets[colIndex] = {};
        this.renderRows();
        this.syncConfig();
    }

    selectWidget(widgetEl) {
        // Remove previous selection
        this.container.querySelectorAll('.pb-widget.selected').forEach(el => el.classList.remove('selected'));
        widgetEl.classList.add('selected');
        this.selectedWidget = {
            rowId: widgetEl.closest('.pb-row').dataset.rowId,
            colIndex: parseInt(widgetEl.dataset.colIndex)
        };
    }

    applyFieldToSelectedWidget(field) {
        if (!this.selectedWidget) {
            alert(this.__('Please select a widget first by clicking on it'));
            return;
        }

        this.applyFieldToWidget(this.selectedWidget.rowId, this.selectedWidget.colIndex, field);
    }

    applyFieldToWidget(rowId, colIndex, field) {
        const row = this.rows.find(r => r.id === rowId);
        if (!row) return;

        const widget = row.widgets[colIndex];
        if (!widget) return;

        // Apply to key (dashboard) or name (form)
        if (this.pageType === 'dashboard') {
            widget.key = field;
        } else {
            widget.name = field;
        }

        this.renderRows();
        this.syncConfig();
    }

    setAvailableFields(fields) {
        this.availableFields = Array.isArray(fields) ? fields : [];
        this.availablePayload = null;
        this.expandedSchemaPaths.clear();
        this.renderPalette();
    }

    setAvailableInput(payload) {
        this.availablePayload = payload ?? null;
        this.availableFields = extractFieldKeys(this.availablePayload ?? {});
        this.expandedSchemaPaths.clear();
        this.activePaletteTab = 'schema';
        this.renderPalette();
    }

    renderPalette() {
        const palette = this.container.querySelector('#pb-palette');
        const hasFields = this.availableFields.length > 0;
        const hasPayload = this.availablePayload !== null;

        if (!hasFields && !hasPayload) {
            palette.style.display = 'none';
            return;
        }

        palette.style.display = 'block';
        this.renderPaletteViews();
        this.switchPaletteTab(this.activePaletteTab);
    }

    renderPaletteViews() {
        const schemaContainer = this.container.querySelector('#pb-palette-schema');
        const jsonContainer = this.container.querySelector('#pb-palette-json');

        if (!schemaContainer || !jsonContainer) return;

        schemaContainer.innerHTML = this.renderSchemaView();
        jsonContainer.innerHTML = this.renderJsonView();
    }

    switchPaletteTab(tab) {
        const validTab = tab === 'json' ? 'json' : 'schema';
        this.activePaletteTab = validTab;

        this.container.querySelectorAll('.pb-palette-tab').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.tab === validTab);
        });

        this.container.querySelectorAll('.pb-palette-view').forEach((view) => {
            view.classList.toggle('active', view.id === `pb-palette-${validTab}`);
        });
    }

    toggleSchemaPath(path) {
        if (!path) return;

        if (this.expandedSchemaPaths.has(path)) {
            this.expandedSchemaPaths.delete(path);
        } else {
            this.expandedSchemaPaths.add(path);
        }

        this.renderPaletteViews();
        this.switchPaletteTab('schema');
    }

    renderSchemaView() {
        if (this.availablePayload === null) {
            if (this.availableFields.length === 0) {
                return `<div class="pb-empty-note">${this.__('No fields detected yet')}</div>`;
            }

            return `
                <div class="pb-schema-flat">
                    ${this.availableFields.map((field) => this.renderFieldChip(field, 'flat', field, '', { mode: 'flat' })).join('')}
                </div>
            `;
        }

        const nodes = this.buildRootSchemaNodes(this.availablePayload);
        if (nodes.length === 0) {
            return `<div class="pb-empty-note">${this.__('No mappable keys found in input')}</div>`;
        }

        const itemLabel = nodes.length === 1 ? this.__('item') : this.__('items');

        return `
            <div class="pb-schema-count">${nodes.length} ${this.escapeHtml(itemLabel)}</div>
            <div class="pb-schema-tree">
                ${nodes.map((node) => this.renderSchemaNode(node)).join('')}
            </div>
        `;
    }

    renderSchemaNode(node, depth = 0) {
        const isExpandable = node.children.length > 0;
        const isExpanded = this.expandedSchemaPaths.has(node.path);
        const isNodeStyle = isExpandable || node.type === 'object' || node.type === 'array';

        return `
            <div class="pb-schema-node">
                <div class="pb-schema-row" data-depth="${depth}" style="--pb-depth: ${depth};">
                    ${isExpandable ? `
                        <button type="button" class="pb-schema-toggle ${isExpanded ? 'is-expanded' : ''}" data-path="${this.escapeAttribute(node.path)}" title="${this.__('Expand/Collapse')}">
                            <span class="pb-schema-toggle-icon" aria-hidden="true">
                                <svg viewBox="0 0 12 12" focusable="false" aria-hidden="true">
                                    <path d="M4 2.5L8 6L4 9.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </button>
                    ` : '<span class="pb-schema-toggle-spacer"></span>'}
                    ${this.renderFieldChip(node.path, node.type, node.label, node.preview, { isNode: isNodeStyle })}
                </div>
                ${isExpandable && isExpanded ? `
                    <div class="pb-schema-children">
                        ${node.children.map((child) => this.renderSchemaNode(child, depth + 1)).join('')}
                    </div>
                ` : ''}
            </div>
        `;
    }
    renderFieldChip(path, type = 'flat', label = null, preview = '', options = {}) {
        const resolvedLabel = label ?? path;
        const mode = options.mode || (options.isNode ? 'node' : (type === 'flat' ? 'flat' : 'leaf'));

        if (mode === 'node') {
            const metaText = this.getSchemaMetaText(type, preview);
            return `
                <button type="button"
                    class="pb-field-item pb-field-item-node"
                    draggable="true"
                    data-field="${this.escapeAttribute(path)}"
                    title="${this.escapeAttribute(path)}">
                    <span class="pb-field-node-icon">${this.escapeHtml(this.getNodeIcon(type))}</span>
                    <span class="pb-field-label">${this.escapeHtml(resolvedLabel)}</span>
                    ${metaText ? `<span class="pb-field-meta">${this.escapeHtml(metaText)}</span>` : ''}
                </button>
            `;
        }

        if (mode === 'flat') {
            return `
                <button type="button"
                    class="pb-field-item pb-field-item-flat"
                    draggable="true"
                    data-field="${this.escapeAttribute(path)}"
                    title="${this.escapeAttribute(path)}">
                    <span class="pb-field-label">${this.escapeHtml(resolvedLabel)}</span>
                </button>
            `;
        }

        const valueText = this.getLeafValueText(type, preview);
        const typeToken = this.getTypeToken(type);

        return `
            <button type="button"
                class="pb-field-item pb-field-item-leaf"
                draggable="true"
                data-field="${this.escapeAttribute(path)}"
                title="${this.escapeAttribute(path)}">
                <span class="pb-field-type">${this.escapeHtml(typeToken)}</span>
                <span class="pb-field-label">${this.escapeHtml(resolvedLabel)}</span>
                ${valueText ? `<span class="pb-field-value">${this.escapeHtml(valueText)}</span>` : ''}
            </button>
        `;
    }

    renderJsonView() {
        if (this.availablePayload === null) {
            return `<div class="pb-empty-note">${this.__('No JSON payload available')}</div>`;
        }

        let prettyJson;
        try {
            prettyJson = JSON.stringify(this.availablePayload, null, 2);
        } catch (e) {
            prettyJson = String(this.availablePayload);
        }

        return `<pre class="pb-json-view">${this.syntaxHighlight(prettyJson)}</pre>`;
    }

    syntaxHighlight(json) {
        const escaped = this.escapeHtml(json);
        return escaped.replace(
            /("(?:\\u[\da-fA-F]{4}|\\[^u]|[^\\"])*")(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d+)?(?:[eE][+\-]?\d+)?/g,
            (match, stringToken, keyToken, boolNullToken) => {
                if (keyToken) return `<span class="pb-json-key">${stringToken}</span>:`;
                if (stringToken) return `<span class="pb-json-string">${stringToken}</span>`;
                if (boolNullToken) {
                    if (boolNullToken === 'null') return `<span class="pb-json-null">${boolNullToken}</span>`;
                    return `<span class="pb-json-boolean">${boolNullToken}</span>`;
                }
                return `<span class="pb-json-number">${match}</span>`;
            }
        );
    }

    buildRootSchemaNodes(payload) {
        let root = payload;

        if (Array.isArray(root)) {
            if (root.length === 0) return [];

            const first = root[0];
            if (first && typeof first === 'object') {
                root = first;
            } else {
                return [this.buildSchemaNode('[0]', first, '0', true)];
            }
        }

        if (root && typeof root === 'object') {
            return Object.entries(root).map(([key, value]) => this.buildSchemaNode(key, value, key, true));
        }

        return [this.buildSchemaNode('$value', root, '$value', true)];
    }

    buildSchemaNode(label, value, path, includeChildren = true) {
        const type = this.getValueType(value);
        const preview = this.getValuePreview(value, type);
        const children = includeChildren ? this.buildChildren(value, path) : [];

        return {
            label,
            path,
            type,
            preview,
            children,
        };
    }

    buildChildren(value, parentPath) {
        if (Array.isArray(value)) {
            if (value.length === 0) return [];

            const first = value[0];
            if (first && typeof first === 'object') {
                return Object.entries(first).map(([key, child]) =>
                    this.buildSchemaNode(key, child, `${parentPath}.0.${key}`, true)
                );
            }

            return [this.buildSchemaNode('[0]', first, `${parentPath}.0`, true)];
        }

        if (value && typeof value === 'object') {
            return Object.entries(value).map(([key, child]) =>
                this.buildSchemaNode(key, child, `${parentPath}.${key}`, true)
            );
        }

        return [];
    }

    getValueType(value) {
        if (Array.isArray(value)) return 'array';
        if (value === null) return 'null';
        if (typeof value === 'object') return 'object';
        return typeof value;
    }

    getValuePreview(value, type) {
        if (type === 'object') return '';
        if (type === 'array') return `(${value.length})`;
        if (type === 'string') return value.length > 80 ? `${value.slice(0, 80)}...` : `${value}`;
        if (type === 'null') return '';
        return String(value);
    }

    getSchemaMetaText(type, preview) {
        if (type === 'object') return 'object';
        if (type === 'array') return preview ? `array${preview}` : 'array';
        return type;
    }

    getLeafValueText(type, preview) {
        if (type === 'null') return 'null';
        if (type === 'boolean') return preview || 'false';
        if (type === 'number') return preview || '0';
        if (type === 'string') return preview || '';
        return preview || '';
    }

    getNodeIcon(type) {
        const map = {
            object: '🧊',
            array: '🧊',
        };

        return map[type] || '🧊';
    }

    getTypeToken(type) {
        const map = {
            string: 'T',
            number: '#',
            boolean: '?',
            null: '0',
            object: '{}',
            array: '[]',
            flat: 'K',
        };

        return map[type] || 'T';
    }

    escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    escapeAttribute(value) {
        return this.escapeHtml(value).replace(/"/g, '&quot;');
    }

    renderRows() {
        const container = this.container.querySelector('#pb-rows');

        container.innerHTML = this.rows.map(row => {
            const layout = row.layout || '';
            const pattern = this.getPatternsForCols(row.columns).find(p => p.value === layout || p.value === `repeat(${row.columns}, 1fr)`);
            const patternLabel = pattern ? pattern.label : (layout ? layout.replace(/fr/g, '').replace(/ /g, '/') : this.__('Equal'));

            const layoutLabel = this.__('Structure: :label', { label: `${row.columns} (${patternLabel})` });

            return `
                <div class="pb-row" data-row-id="${row.id}">
                    <div class="pb-row-header">
                        <span class="pb-row-handle" draggable="true" title="${this.__('Drag to reorder row')}">☰</span>
                        <button type="button" class="pb-structure-btn">
                            📐 ${layoutLabel}
                        </button>
                        <button type="button" class="pb-row-delete" title="${this.__('Delete row')}">🗑️</button>
                    </div>
                    <div class="pb-row-columns" style="${layout ? `--layout: ${layout};` : `--cols: ${row.columns};`}">
                        ${row.widgets.map((widget, colIndex) => this.renderWidget(widget, colIndex)).join('')}
                    </div>
                </div>
            `;
        }).join('');
    }

    openLayoutPicker(rowId) {
        const row = this.rows.find(r => r.id === rowId);
        if (!row) return;

        // Step 1: Select Column Count
        const modal = this.showModal(this.__('Select Columns'), `
            <div class="pb-col-count-grid">
                ${[1, 2, 3, 4, 5, 6].map(num => `
                    <div class="pb-col-opt" data-cols="${num}">
                        <span class="pb-col-opt-num">${num}</span>
                        <span class="pb-col-opt-label">${num === 1 ? this.__('Column') : this.__('Columns')}</span>
                    </div>
                `).join('')}
            </div>
        `);

        // Handle Step 1 click using delegation on the modal content
        modal.querySelector('.pb-modal-content').addEventListener('click', (e) => {
            const opt = e.target.closest('.pb-col-opt');
            if (opt) {
                const cols = parseInt(opt.dataset.cols);
                this.showPatterns(rowId, cols);
            }
        });
    }

    showPatterns(rowId, cols) {
        const patterns = this.getPatternsForCols(cols);

        // Step 2: Select Pattern
        const modal = this.showModal(this.__('Select Layout (:cols Cols)', { cols }), `
            <div class="pb-pattern-grid">
                ${patterns.map(p => `
                    <div class="pb-pattern-opt" data-layout="${p.value}" data-cols="${cols}">
                        <div class="pb-pattern-preview" style="grid-template-columns: ${p.value};">
                            ${p.value.split(' ').map(() => `<div class="pb-pattern-box"></div>`).join('')}
                        </div>
                        <span class="pb-pattern-label">${p.label}</span>
                    </div>
                `).join('')}
            </div>
        `, true, () => this.openLayoutPicker(rowId));

        // Handle Pattern selection
        modal.querySelector('.pb-modal-content').addEventListener('click', (e) => {
            const opt = e.target.closest('.pb-pattern-opt');
            if (opt) {
                const layout = opt.dataset.layout;
                const columns = parseInt(opt.dataset.cols);
                const isAsymmetric = layout.includes(' ') || (cols === 2 && layout !== '1fr 1fr') || (cols === 3 && layout !== '1fr 1fr 1fr');
                this.changeRowLayout(rowId, columns, isAsymmetric ? layout : null);
                this.closeModal();
            }
        });
    }

    getPatternsForCols(cols) {
        const patterns = {
            1: [{ value: '1fr', label: '100%' }],
            2: [
                { value: '1fr 1fr', label: '50 / 50' },
                { value: '1fr 2fr', label: '33 / 66' },
                { value: '2fr 1fr', label: '66 / 33' },
                { value: '1fr 3fr', label: '25 / 75' },
                { value: '3fr 1fr', label: '75 / 25' }
            ],
            3: [
                { value: '1fr 1fr 1fr', label: '33 / 33 / 33' },
                { value: '1fr 2fr 1fr', label: '25 / 50 / 25' },
                { value: '2fr 1fr 1fr', label: '50 / 25 / 25' },
                { value: '1fr 1fr 2fr', label: '25 / 25 / 50' },
                { value: '1fr 4fr 1fr', label: '16 / 66 / 16' }
            ],
            4: [
                { value: '1fr 1fr 1fr 1fr', label: '25 / 25 / 25 / 25' },
                { value: '2fr 1fr 1fr 2fr', label: '33 / 16 / 16 / 33' }
            ],
            5: [{ value: '1fr 1fr 1fr 1fr 1fr', label: '20 / 20 / 20 / 20 / 20' }],
            6: [{ value: '1fr 1fr 1fr 1fr 1fr 1fr', label: '16 / 16 / 16 / 16 / 16 / 16' }]
        };
        return patterns[cols] || [{ value: `repeat(${cols}, 1fr)`, label: this.__('Equal') }];
    }

    showModal(title, content, showBack = false, onBack = null) {
        this.closeModal(); // Ensure no duplicates

        const overlay = document.createElement('div');
        overlay.className = 'pb-modal-overlay';
        overlay.innerHTML = `
            <div class="pb-modal">
                <div class="pb-modal-header">
                    <h3 class="pb-modal-title">${title}</h3>
                    <button type="button" class="pb-modal-close">&times;</button>
                </div>
                <div class="pb-modal-content">
                    ${content}
                </div>
                ${showBack ? `
                    <div class="pb-modal-footer">
                        <button type="button" class="pb-back-btn">← ${this.__('Back')}</button>
                    </div>
                ` : ''}
            </div>
        `;

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.closest('.pb-modal-close')) {
                this.closeModal();
            }
            if (e.target.closest('.pb-back-btn') && onBack) {
                onBack();
            }
        });

        document.body.appendChild(overlay);
        return overlay;
    }

    closeModal() {
        const existing = document.querySelector('.pb-modal-overlay');
        if (existing) existing.remove();
    }

    renderWidget(widget, colIndex) {
        const type = widget.type || '';
        const widgetDef = this.widgetTypes.find(w => w.value === type);
        const settings = widgetDef ? widgetDef.settings : [];

        return `
            <div class="pb-widget ${this.selectedWidget && this.selectedWidget.colIndex === colIndex ? 'selected' : ''}" data-col-index="${colIndex}" draggable="true" title="${this.__('Drag to swap with another widget')}">
                <div class="pb-widget-header">
                    <select class="pb-widget-type">
                        ${this.widgetTypes.map(wt => `
                            <option value="${wt.value}" ${wt.value === type ? 'selected' : ''}>${wt.label}</option>
                        `).join('')}
                    </select>
                    ${type ? `<button type="button" class="pb-widget-delete" title="${this.__('Remove widget')}">✕</button>` : ''}
                </div>
                ${type && settings.length > 0 ? `
                    <div class="pb-widget-settings">
                        ${this.renderWidgetSettings(widget, settings)}
                    </div>
                ` : ''}
            </div>
        `;
    }

    renderWidgetSettings(widget, settings) {
        return settings.map(setting => {
            const value = widget[setting] || '';

            if (setting === 'status') {
                return `
                    <div class="pb-setting">
                        <label>${this.__('Status')}</label>
                        <select class="pb-widget-setting" data-setting="status">
                            <option value="info" ${value === 'info' ? 'selected' : ''}>${this.__('Info (Blue)')}</option>
                            <option value="success" ${value === 'success' ? 'selected' : ''}>${this.__('Success (Green)')}</option>
                            <option value="warning" ${value === 'warning' ? 'selected' : ''}>${this.__('Warning (Yellow)')}</option>
                            <option value="danger" ${value === 'danger' ? 'selected' : ''}>${this.__('Danger (Red)')}</option>
                        </select>
                    </div>
                `;
            }

            if (setting === 'subtype') {
                return `
                    <div class="pb-setting">
                        <label>${this.__('Chart Type')}</label>
                        <select class="pb-widget-setting" data-setting="subtype">
                            <option value="line" ${value === 'line' ? 'selected' : ''}>${this.__('Line')}</option>
                            <option value="bar" ${value === 'bar' ? 'selected' : ''}>${this.__('Bar')}</option>
                            <option value="area" ${value === 'area' ? 'selected' : ''}>${this.__('Area')}</option>
                            <option value="donut" ${value === 'donut' ? 'selected' : ''}>${this.__('Donut')}</option>
                            <option value="pie" ${value === 'pie' ? 'selected' : ''}>${this.__('Pie')}</option>
                        </select>
                    </div>
                `;
            }

            if (setting === 'required') {
                return `
                    <div class="pb-setting pb-setting-checkbox">
                        <label>
                            <input type="checkbox" class="pb-widget-setting" data-setting="required" ${value ? 'checked' : ''}>
                            ${this.__('Required')}
                        </label>
                    </div>
                `;
            }

            if (setting === 'options') {
                const optionsStr = Array.isArray(value) ? value.join(', ') : (value || '');
                return `
                    <div class="pb-setting">
                        <label>${this.__('Options (comma-separated)')}</label>
                        <input type="text" class="pb-widget-setting" data-setting="options" value="${optionsStr}" placeholder="${this.__('Option 1, Option 2, Option 3')}">
                    </div>
                `;
            }

            if (setting === 'columns') {
                const columnsStr = Array.isArray(value)
                    ? value.map(c => typeof c === 'object' ? `${c.key}:${c.label}` : c).join(', ')
                    : '';
                return `
                    <div class="pb-setting">
                        <label>${this.__('Columns (key:label, ...)')}</label>
                        <input type="text" class="pb-widget-setting" data-setting="columns" value="${columnsStr}" placeholder="${this.__('id:ID, name:Name, status:Status')}">
                    </div>
                `;
            }

            if (setting === 'height') {
                return `
                    <div class="pb-setting">
                        <label>${this.__('Height (px)')}</label>
                        <input type="number" class="pb-widget-setting" data-setting="height" value="${value || 250}" min="100" max="600">
                    </div>
                `;
            }

            // Default text input
            const labels = {
                key: this.__('Data Key'),
                label: this.__('Label'),
                desc: this.__('Description'),
                name: this.__('Field Name'),
                placeholder: this.__('Placeholder'),
                value: this.__('Value'),
                units: this.__('Units')
            };

            return `
            <div class="pb-setting">
                <label>${labels[setting] || setting}</label>
                <input type="${setting === 'number' ? 'number' : 'text'}" class="pb-widget-setting" data-setting="${setting}" value="${value}" placeholder="${labels[setting] || ''}">
            </div>
        `;
        }).join('');
    }

    syncConfig() {
        const config = this.getConfig();
        this.onConfigChange(config);
    }

    getConfig() {
        const title = this.container.querySelector('#pb-title').value;

        if (this.pageType === 'form') {
            const description = this.container.querySelector('#pb-description').value;
            const submitLabel = this.container.querySelector('#pb-submit-label').value;

            const fields = [];
            this.rows.forEach((row) => {
                // Add break item with layout info for every row
                const breakItem = { type: 'break' };
                if (row.layout) {
                    breakItem.layout = row.layout;
                } else {
                    breakItem.columns = row.columns;
                }
                fields.push(breakItem);

                row.widgets.forEach(widget => {
                    const field = this.collectWidgetSettings(widget);
                    if (!field.type) field.type = 'none';
                    fields.push(field);
                });
            });

            return {
                title,
                description: description || undefined,
                fields,
                submit_label: submitLabel || 'Submit'
            };
        } else {
            const widgets = [];
            this.rows.forEach((row) => {
                // Add break item with layout info for every row
                const breakItem = { type: 'break' };
                if (row.layout) {
                    breakItem.layout = row.layout;
                } else {
                    breakItem.columns = row.columns;
                }
                widgets.push(breakItem);

                row.widgets.forEach(widget => {
                    const w = this.collectWidgetSettings(widget);
                    if (!w.type) w.type = 'none';
                    widgets.push(w);
                });
            });

            return { title, widgets };
        }
    }

    collectWidgetSettings(widget) {
        const widgetEl = this.findWidgetElement(widget);
        const result = { type: widget.type };

        if (widgetEl) {
            widgetEl.querySelectorAll('.pb-widget-setting').forEach(input => {
                const setting = input.dataset.setting;
                let value;

                if (input.type === 'checkbox') {
                    value = input.checked;
                } else if (setting === 'options') {
                    value = input.value.split(',').map(s => s.trim()).filter(Boolean);
                } else if (setting === 'columns') {
                    value = input.value.split(',').map(s => {
                        const parts = s.trim().split(':');
                        return parts.length === 2
                            ? { key: parts[0].trim(), label: parts[1].trim() }
                            : { key: parts[0].trim(), label: parts[0].trim() };
                    }).filter(c => c.key);
                } else if (setting === 'height') {
                    value = parseInt(input.value) || 250;
                } else {
                    value = input.value;
                }

                if (value !== undefined && value !== '' && !(Array.isArray(value) && value.length === 0)) {
                    result[setting] = value;
                }
            });
        } else {
            // Fallback: use stored widget data
            Object.assign(result, widget);
        }

        return result;
    }

    findWidgetElement(widget) {
        // Find element matching this widget's data
        for (const row of this.rows) {
            for (let i = 0; i < row.widgets.length; i++) {
                if (row.widgets[i] === widget) {
                    const rowEl = this.container.querySelector(`[data-row-id="${row.id}"]`);
                    if (rowEl) {
                        return rowEl.querySelector(`[data-col-index="${i}"]`);
                    }
                }
            }
        }
        return null;
    }
}

// Extract nested keys from JSON response
function extractFieldKeys(obj, prefix = '') {
    let keys = [];

    if (Array.isArray(obj)) {
        if (obj.length > 0 && typeof obj[0] === 'object') {
            keys = keys.concat(extractFieldKeys(obj[0], prefix));
        }
    } else if (obj && typeof obj === 'object') {
        for (const key in obj) {
            const fullKey = prefix ? `${prefix}.${key}` : key;
            keys.push(fullKey);

            if (typeof obj[key] === 'object' && obj[key] !== null) {
                keys = keys.concat(extractFieldKeys(obj[key], fullKey));
            }
        }
    }

    return keys;
}

// Export for use
window.PageBuilder = PageBuilder;
window.extractFieldKeys = extractFieldKeys;
