/**
 * Laracloak Frontend Engine (Interaction Layer)
 * Handles AJAX interactions for server-rendered components.
 */
console.log('🚀 Laracloak Frontend Engine Loaded');

class Api {
    static async post(url, data) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(data)
        });

        const contentType = response.headers.get('content-type') || '';

        if (!response.ok) {
            // Try to parse error as JSON
            try {
                const error = await response.json();
                throw new Error(error.error || `Error ${response.status}`);
            } catch (e) {
                throw new Error(e.message || `Error ${response.status}`);
            }
        }

        // Robust JSON detection (handles case-insensitivity and extra parameters)
        if (contentType.toLowerCase().includes('application/json')) {
            const payload = await response.json();
            return { type: 'json', payload: payload, headers: response.headers };
        } else {
            // Return Blob for everything else (text, images, audio)
            return { type: 'blob', payload: await response.blob(), mimeType: contentType || 'text/plain', headers: response.headers };
        }
    }
}

class FormHandler {
    constructor() {
        const form = document.getElementById('dynamic-form');
        if (form) {
            this.form = form;
            this.slug = form.dataset.slug;
            this.attachEvents();
        }
    }

    attachEvents() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('submit-btn');
            const spinner = btn.querySelector('.spinner');
            const errorBox = document.getElementById('form-error');
            const successBox = document.getElementById('form-success');

            // Reset state
            errorBox.style.display = 'none';
            successBox.style.display = 'none';
            successBox.innerHTML = ''; // Clear previous content
            btn.disabled = true;
            spinner.style.display = 'block';

            // Gather Data
            const formData = new FormData(this.form);
            const data = Object.fromEntries(formData.entries());

            try {
                const result = await Api.post(`/front/${this.slug}/action`, data);
                console.log('API Result:', result);

                let messageHtml = '';

                if (result.type === 'json') {
                    const json = result.payload;
                    // Handle JSON
                    if (json.message) messageHtml = `<p>${json.message}</p>`;
                    else if (json.success && typeof json.success === 'string') messageHtml = `<p>${json.success}</p>`;
                    else messageHtml = `<pre style="white-space: pre-wrap; font-family: monospace;">Response: ${JSON.stringify(json, null, 2)}</pre>`;

                } else if (result.type === 'blob') {
                    const blob = result.payload;
                    const url = URL.createObjectURL(blob);
                    const mime = result.mimeType;

                    if (mime.startsWith('image/')) {
                        messageHtml = `<p>Image Received:</p><img src="${url}" style="max-width: 100%; border-radius: 8px; margin-top: 10px;">`;
                    } else if (mime.startsWith('audio/')) {
                        messageHtml = `<p>Audio Received:</p><audio controls src="${url}" style="width: 100%; margin-top: 10px;"></audio>`;
                    } else if (mime.startsWith('video/')) {
                        messageHtml = `<p>Video Received:</p><video controls src="${url}" style="width: 100%; max-height: 400px; margin-top: 10px;"></video>`;
                    } else {
                        // Text or Binary download
                        if (mime.startsWith('text/') || mime === '') {
                            const text = await blob.text();
                            messageHtml = `<pre style="white-space: pre-wrap;">${text}</pre>`;
                        } else {
                            messageHtml = `<p>File Received:</p><a href="${url}" download="file_${Date.now()}" class="btn btn-primary" style="margin-top: 10px;">Download File</a>`;
                        }
                    }
                }

                // --- CUSTOM LARACLOAK HANDLERS ---
                const successMsg = result.headers.get('X-Laracloak-Success');
                const redirectUrl = result.headers.get('X-Laracloak-Redirect');

                if (successMsg) {
                    messageHtml = this.hex2utf8(successMsg);
                }

                if (redirectUrl) {
                    messageHtml += `<p class="mt-4"><small>Redireccionando...</small></p>`;
                }
                // --- END CUSTOM HANDLERS ---

                successBox.innerHTML = messageHtml;
                successBox.style.display = 'block';
                this.form.reset();

                if (redirectUrl) {
                    setTimeout(() => location.href = redirectUrl, 2000);
                }

            } catch (err) {
                console.error(err);
                errorBox.textContent = err.message || "An error occurred while communicating with the service.";
                errorBox.style.display = 'block';
            } finally {
                btn.disabled = false;
                spinner.style.display = 'none';
            }
        });
    }

    hex2utf8(hex) {
        try {
            let str = '';
            for (let i = 0; i < hex.length; i += 2) {
                str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
            }
            return decodeURIComponent(escape(str));
        } catch (e) {
            return hex;
        }
    }
}

// --- WIDGET RENDERER ENGINE ---
class WidgetRenderer {
    constructor(container) {
        this.container = container;
        this.chartInstance = null;
    }

    render(config, data) {
        const type = config.type || 'unknown';

        switch (type) {
            case 'kpi':
            case 'stat-card':
                this.renderKPI(config, data);
                break;
            case 'sparkline':
                this.renderChart(config, data);
                break;
            case 'progress-card':
                this.renderProgress(config, data);
                break;
            case 'alert-card':
                this.renderAlert(config, data);
                break;
            case 'chart':
            case 'line':
            case 'bar':
            case 'area':
            case 'donut':
            case 'pie':
            case 'gauge':
                this.renderChart(config, data);
                break;
            case 'table':
            case 'leaderboard':
                this.renderTable(config, data);
                break;
            case 'timeline':
                this.renderTimeline(config, data);
                break;
            case 'log-stream':
                this.renderLogs(config, data);
                break;
            case 'html-card':
                this.renderHTML(config, data);
                break;
            default:
                console.warn(`Unknown widget type: ${type}`);
        }
    }

    // 1. KPI / Stat Card (Enhanced)
    renderKPI(config, data) {
        // Data format: "123" OR { value: "123", delta: { value: "+5%", color: "success" } }
        let value = data;
        let deltaHtml = '';

        if (typeof data === 'object' && data !== null) {
            value = data.value ?? '--';
            if (data.delta) {
                const color = data.delta.color || 'neutral'; // success, danger, warning, neutral
                const deltaVal = data.delta.value ?? '';
                const colorMap = { success: '#10b981', danger: '#ef4444', warning: '#f59e0b', neutral: '#94a3b8' };
                deltaHtml = `<span style="font-size: 0.8rem; color: ${colorMap[color]}; margin-left: 8px;">${deltaVal}</span>`;
            }
        }

        const html = `
            <div class="stat-value" style="display:flex; align-items:baseline;">
                ${value} ${deltaHtml}
            </div>
            ${config.desc ? `<div class="stat-desc">${config.desc}</div>` : ''}
        `;
        this.container.querySelector('.widget-body').innerHTML = html;
    }

    // 2. Alert Card
    renderAlert(config, data) {
        // Data: "System Normal" OR { title: "Error", status: "danger", time: "2m ago" }
        let status = config.status || 'info';
        let title = typeof data === 'string' ? data : (data?.title ?? '--');

        if (typeof data === 'object' && data?.status) status = data.status;

        const colorMap = {
            info: 'var(--info)',
            success: 'var(--success)',
            warning: 'var(--warning)',
            danger: 'var(--danger)'
        };

        this.container.style.borderLeft = `4px solid ${colorMap[status] || colorMap.info}`;

        const html = `
            <div class="stat-value" style="font-size:1.2rem;">${title}</div>
            ${data?.time ? `<div class="stat-desc">${data.time}</div>` : ''}
        `;
        this.container.querySelector('.widget-body').innerHTML = html;
    }

    // 3. Progress Card
    renderProgress(config, data) {
        // Data: 75 (number)
        const percent = Math.min(100, Math.max(0, parseFloat(data) || 0));

        const html = `
            <div style="margin-top: 1rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-weight:bold; font-size:1.5rem;">
                    <span>${percent}%</span>
                </div>
                <div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
                    <div style="width: ${percent}%; height: 100%; background: var(--primary); transition: width 0.5s ease;"></div>
                </div>
            </div>
        `;
        this.container.querySelector('.widget-body').innerHTML = html;
    }

    // 4. Charts (ApexCharts Wrapper)
    renderChart(config, data) {
        const body = this.container.querySelector('.widget-body');

        // Prepare Series & Options
        // Data expectation: { series: [], categories: [] } OR just simple series array

        let series = [];
        let categories = [];

        if (Array.isArray(data)) {
            // Simple array [10, 20, 30] -> Assume single series
            series = [{ name: config.label || 'Data', data: data }];
        } else if (typeof data === 'object') {
            series = data.series || [];
            categories = data.categories || [];
        }

        // Determine Chart Type
        let chartType = config.subtype || 'line';
        if (config.type === 'bar' || config.type === 'area' || config.type === 'donut' || config.type === 'pie' || config.type === 'gauge') {
            chartType = config.type === 'gauge' ? 'radialBar' : config.type;
        }

        // Resolve Theme Colors from CSS Variables
        const getStyle = (name) => getComputedStyle(document.body).getPropertyValue(name).trim();
        const textMuted = getStyle('--text-muted') || '#94a3b8';
        const borderColor = getStyle('--border') || '#334155';
        const textMain = getStyle('--text-main') || '#f8fafc';

        // Detect Mode: If text-main is darker than mid-grey, it's a Light theme
        const isLight = (color) => {
            const hex = color.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            return r < 128; // Simple heuristic for dark text
        };
        const themeMode = isLight(textMain) ? 'light' : 'dark';

        // Build Options
        let options = {
            series: series,
            chart: {
                type: chartType,
                height: config.height || 250,
                background: 'transparent',
                toolbar: { show: false },
                animations: { enabled: true },
                foreColor: textMuted
            },
            theme: { mode: themeMode, palette: 'palette1' },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: categories,
                labels: { style: { colors: textMuted } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: textMuted } }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4
            },
            colors: ['#7c3aed', '#10b981', '#f59e0b', '#ef4444'],
            tooltip: { theme: themeMode }
        };

        // Specific overrides
        if (config.type === 'sparkline') {
            options.chart.sparkline = { enabled: true };
            options.chart.height = 60;
            options.stroke.width = 2;

            if (data.history) {
                series = [{ name: config.label || 'Data', data: data.history }];
                options.series = series;

                // Stabilize DOM for Sparkline (Don't destroy container on every update)
                let valEl = body.querySelector('.stat-value');
                let chartContainer = body.querySelector('.sparkline-container');

                if (!valEl) {
                    body.innerHTML = `<div class="stat-value"></div><div class="sparkline-container"></div>`;
                    valEl = body.querySelector('.stat-value');
                    chartContainer = body.querySelector('.sparkline-container');
                }

                valEl.textContent = data.value ?? '';

                // Render or Update
                try {
                    if (!this.chartInstance || !chartContainer.querySelector('.apexcharts-canvas')) {
                        if (this.chartInstance) this.chartInstance.destroy();
                        this.chartInstance = new ApexCharts(chartContainer, options);
                        this.chartInstance.render();
                    } else {
                        this.chartInstance.updateOptions(options);
                    }
                } catch (e) {
                    console.error('Sparkline Render Error:', e);
                }
                return;
            }
        }

        if (chartType === 'radialBar' && config.type === 'gauge') {
            options.plotOptions = {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    dataLabels: {
                        name: { show: false },
                        value: { offsetY: 10, fontSize: '24px', color: '#f8fafc', formatter: (val) => val + (config.units || '') }
                    }
                }
            };
            // Gauge usually expects a single number in series array [75]
            if (typeof data === 'number' || (data && typeof data === 'object' && data.value !== undefined)) {
                const val = (typeof data === 'number') ? data : data.value;
                options.series = [val];
            }
        }

        // Render or Update Generic Charts
        try {
            if (!this.chartInstance || !body.querySelector('.apexcharts-canvas')) {
                body.innerHTML = ''; // Clear spinner or placeholders
                this.chartInstance = new ApexCharts(body, options);
                this.chartInstance.render();
            } else {
                this.chartInstance.updateOptions(options);
            }
        } catch (e) {
            console.error('Chart Render Error:', e, config);
        }
    }

    renderTable(tableConfig, data) {
        // Data: Array of objects
        const rows = Array.isArray(data) ? data : [];
        const body = this.container.querySelector('.widget-body');

        if (rows.length === 0) {
            body.innerHTML = `<div class="text-center text-muted p-4">No data available</div>`;
            return;
        }

        let html = `<div class="table-responsive"><table class="datatable"><thead><tr>`;

        // Headers
        const columns = tableConfig.columns || [];
        if (columns.length === 0 && rows.length > 0) {
            // Auto-detect columns from first row if not specified
            Object.keys(rows[0]).forEach(k => columns.push({ key: k, label: k }));
        }

        columns.forEach(col => {
            html += `<th>${col.label || col.key}</th>`;
        });
        html += `</tr></thead><tbody>`;

        // Rows
        rows.forEach((row, index) => {
            html += `<tr>`;
            columns.forEach(col => {
                let val = row[col.key] ?? '-';

                // Leaderboard logic: Add rank if requested
                if (tableConfig.type === 'leaderboard' && col.key === '#') {
                    val = typeof row.rank !== 'undefined' ? row.rank : (index + 1);
                    if (val === 1) val = '🥇';
                    if (val === 2) val = '🥈';
                    if (val === 3) val = '🥉';
                }

                html += `<td>${val}</td>`;
            });
            html += `</tr>`;
        });

        html += `</tbody></table></div>`;
        body.innerHTML = html;
    }

    renderTimeline(config, data) {
        // Data: [{ time: "10:00", title: "Deploy", type: "success" }, ...]
        const events = Array.isArray(data) ? data : [];
        const body = this.container.querySelector('.widget-body');

        let html = `<div style="display:flex; flex-direction:column; gap:1rem; padding-left:0.5rem;">`;
        events.forEach(ev => {
            const color = ev.type === 'error' ? '#ef4444' : (ev.type === 'success' ? '#10b981' : '#94a3b8');
            html += `
                <div style="display:flex; gap:1rem; align-items:flex-start; position:relative;">
                    <div style="min-width:60px; font-size:0.8rem; color:#94a3b8; text-align:right;">${ev.time || ''}</div>
                    <div style="width:2px; background:#334155; position:absolute; left:70px; top:0; bottom:-1rem;"></div>
                    <div style="min-width:10px; height:10px; border-radius:50%; background:${color}; position:relative; z-index:2; margin-top:5px;"></div>
                    <div style="flex:1;">
                        <div style="font-weight:600; font-size:0.9rem;">${ev.title}</div>
                        ${ev.desc ? `<div style="font-size:0.8rem; color:#64748b;">${ev.desc}</div>` : ''}
                    </div>
                </div>
             `;
        });
        html += `</div>`;
        body.innerHTML = html;
    }

    renderLogs(config, data) {
        // Log Stream
        const logs = Array.isArray(data) ? data : [];
        const body = this.container.querySelector('.widget-body');

        let html = `<div style="background:#0f172a; padding:1rem; border-radius:6px; font-family:monospace; font-size:0.8rem; max-height:300px; overflow-y:auto;">`;
        logs.forEach(log => {
            const color = log.level === 'error' ? '#f87171' : (log.level === 'warn' ? '#fbbf24' : '#94a3b8');
            html += `<div style="margin-bottom:4px; border-bottom:1px solid #1e293b; padding-bottom:2px;">
                <span style="color:#64748b; margin-right:8px;">${log.time || ''}</span>
                <span style="color:${color}; font-weight:bold; margin-right:8px;">[${(log.level || 'INFO').toUpperCase()}]</span>
                <span style="color:#e2e8f0;">${log.message}</span>
             </div>`;
        });
        html += `</div>`;
        body.innerHTML = html;
    }

    renderHTML(config, data) {
        // Disabled by default for security, but implemented if needed securely or specific widgets
        this.container.querySelector('.widget-body').innerHTML =
            `<div class="alert alert-warning">HTML Widget disabled for security reasons.</div>`;
    }
}


class DashboardHandler {
    constructor() {
        this.container = document.querySelector('.dashboard-container');
        if (!this.container) return;

        this.slug = this.container.dataset.slug;
        this.renderers = new Map();

        // Timer state
        this.timer = null;
        this.isPaused = false;

        // Load preference or default
        // User requested: Always default to the server-provided value on load, ignoring previous session storage.
        // const savedInterval = localStorage.getItem('dashboard_interval_' + this.slug);
        // this.interval = savedInterval ? parseInt(savedInterval) : (window.DASHBOARD_REFRESH_RATE || 60);
        this.interval = (typeof window.DASHBOARD_REFRESH_RATE !== 'undefined') ? window.DASHBOARD_REFRESH_RATE : 60;

        // Bind Controls
        this.pauseBtn = document.getElementById('pause-btn');
        this.playIcon = document.getElementById('play-icon');
        this.pauseIcon = document.getElementById('pause-icon');
        this.intervalSelect = document.getElementById('interval-select');
        this.refreshBtn = document.getElementById('refresh-now-btn');
        this.timerLabel = document.getElementById('refresh-timer');

        if (this.intervalSelect) this.intervalSelect.value = this.interval;

        // Last Fetch Time
        this.lastFetch = Date.now();
        this.uiTimer = null;

        this.bindEvents();

        // Initialize Renderers for each widget
        this.container.querySelectorAll('.widget-container').forEach(el => {
            const key = el.dataset.widgetKey;
            if (key) {
                this.renderers.set(key, {
                    element: el,
                    renderer: new WidgetRenderer(el),
                    config: JSON.parse(el.dataset.config || '{}')
                });
            }
        });

        this.init();
    }

    bindEvents() {
        if (this.refreshBtn) {
            this.refreshBtn.addEventListener('click', () => {
                this.fetchData();
                this.resetTimer();
            });
        }

        if (this.pauseBtn) {
            this.pauseBtn.addEventListener('click', () => this.togglePause());
        }

        if (this.intervalSelect) {
            this.intervalSelect.addEventListener('change', (e) => {
                this.interval = parseInt(e.target.value);
                localStorage.setItem('dashboard_interval_' + this.slug, this.interval);
                this.updateTimeLabel(); // Update immediately on change

                if (this.interval === 0) {
                    this.stopTimer();
                    this.setPausedUI(true);
                } else {
                    this.startTimer();
                    this.setPausedUI(false);
                }
            });
        }
    }

    async init() {
        await this.fetchData();
        this.startTimer();
        this.startUITimer();
    }

    startTimer() {
        this.stopTimer();
        if (this.interval > 0 && !this.isPaused) {
            console.log(`Auto-refresh active: ${this.interval}s`);
            this.timer = setInterval(() => this.fetchData(), this.interval * 1000);
        }
    }

    startUITimer() {
        if (this.uiTimer) clearInterval(this.uiTimer);
        this.uiTimer = setInterval(() => this.updateTimeLabel(), 1000);
        this.updateTimeLabel();
    }

    updateTimeLabel() {
        if (!this.timerLabel) return;

        const secondsSince = Math.floor((Date.now() - this.lastFetch) / 1000);

        // Determine unit based on dropdown interval
        // If interval is seconds (<60), show seconds.
        // If interval is minutes (>=60), show minutes rounded.
        // If interval is hours (>=3600), show hours rounded? Or mix?
        // User requested: "Units passed on dropdown units and rounded to integers".

        let val, unit;

        if (this.interval >= 3600) {
            val = Math.round(secondsSince / 3600);
            unit = 'h';
        } else if (this.interval >= 60) {
            val = Math.round(secondsSince / 60);
            unit = 'm';
        } else {
            val = secondsSince;
            unit = 's';
        }

        this.timerLabel.textContent = `${val}${unit} ago`;
    }

    stopTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    resetTimer() {
        this.stopTimer();
        this.startTimer();
    }

    togglePause() {
        this.isPaused = !this.isPaused;
        this.setPausedUI(this.isPaused);

        if (this.isPaused) {
            this.stopTimer();
        } else {
            this.fetchData();
            this.startTimer();
        }
    }

    setPausedUI(isPaused) {
        if (!this.playIcon || !this.pauseIcon) return;

        if (isPaused) {
            this.playIcon.style.display = 'block';
            this.pauseIcon.style.display = 'none';
            this.pauseBtn.classList.add('text-warning');
        } else {
            this.playIcon.style.display = 'none';
            this.pauseIcon.style.display = 'block';
            this.pauseBtn.classList.remove('text-warning');
        }
    }

    async fetchData() {
        try {
            const btn = this.refreshBtn;
            if (btn) btn.classList.add('animate-spin');

            this.lastFetch = Date.now(); // Track time
            this.updateTimeLabel();

            const response = await Api.post(`/front/${this.slug}/action`, {});

            console.group(`Dashboard Data Received [${new Date().toLocaleTimeString()}]`);
            console.log('Payload:', response.payload);

            if (response.type === 'json') {
                this.updateWidgets(response.payload);
            } else {
                console.warn('Expected JSON response but received:', response.type);
            }
            console.groupEnd();
        } catch (err) {
            console.error('Dashboard Fetch Error:', err);
        } finally {
            if (this.refreshBtn) this.refreshBtn.classList.remove('animate-spin');
        }
    }

    updateWidgets(data) {
        this.renderers.forEach((handler, key) => {
            try {
                const value = this.getNestedValue(data, key);
                if (value !== undefined) {
                    handler.renderer.render(handler.config, value);
                } else {
                    console.debug(`No data found for widget "${key}"`);
                    // Optional: remove spinner and show empty state
                    const body = handler.element.querySelector('.widget-body');
                    if (body && body.querySelector('.spinner')) {
                        body.innerHTML = '<div class="text-muted-foreground text-sm">--</div>';
                    }
                }
            } catch (e) {
                console.error(`Error updating widget "${key}":`, e);
                const body = handler.element.querySelector('.widget-body');
                if (body) body.innerHTML = `<div class="text-danger text-sm">Render Error</div>`;
            }
        });
    }

    getNestedValue(obj, key) {
        if (!obj) return undefined;

        // Handle n8n common response: [{...}] instead of {...}
        let root = obj;
        if (Array.isArray(obj) && obj.length > 0) {
            root = obj[0];
        }

        return key.split('.').reduce((o, i) => (o ? o[i] : undefined), root);
    }
}

// Main App
document.addEventListener('DOMContentLoaded', () => {
    console.log('🏁 DOM Content Loaded - Initializing Laracloak Handlers');
    // Auto-detect components based on DOM presence
    new FormHandler();
    const dashboard = new DashboardHandler();

    if (dashboard.container) {
        console.log(`📊 Dashboard detected with ${dashboard.renderers.size} widgets.`);
    }
});
