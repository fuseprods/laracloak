@props(['config', 'slug', 'refreshRate' => 60, 'canEdit' => false, 'pageId' => null])

<div class="dashboard-container" data-slug="{{ $slug }}">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $config['title'] ?? 'Dashboard' }}</h1>

            @if($canEdit)
                <a href="{{ route('panel.dashboards.edit', $pageId) }}"
                    class="btn btn-ghost btn-sm text-muted-foreground hover:text-primary transition-colors p-1 rounded-full"
                    title="Edit Dashboard">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                </a>
            @endif
        </div>

        <div class="flex items-center gap-6">
            <!-- Refresh Interval Controls -->
            <div class="flex items-center gap-4 bg-card rounded-md px-3 py-1.5 border border-border shadow-sm">
                <div class="flex items-center gap-3 border-r border-border pr-3">
                    <button id="pause-btn"
                        class="btn btn-ghost btn-sm p-1.5 hover:bg-muted rounded text-muted-foreground hover:text-foreground transition-colors"
                        title="Pause/Resume Auto-refresh">
                        <svg id="pause-icon" style="width:20px;height:20px;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg id="play-icon" style="width:20px;height:20px; display:none;" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>

                    <select id="interval-select"
                        class="text-sm py-1.5 px-2 border-none bg-transparent focus:ring-0 cursor-pointer font-medium text-muted-foreground hover:text-foreground transition-colors"
                        style="width: auto; min-width: 60px;">
                        <option value="5">5s</option>
                        <option value="10">10s</option>
                        <option value="30">30s</option>
                        <option value="60">1m</option>
                        <option value="300">5m</option>
                        <option value="900">15m</option>
                        <option value="1800">30m</option>
                        <option value="3600">1h</option>
                        <option value="0">Off</option>
                    </select>
                </div>

                <span id="refresh-timer" class="text-sm text-muted-foreground font-mono px-1"
                    style="min-width: 60px; text-align: right;">--</span>
            </div>

            <!-- Manual Refresh -->
            <button id="refresh-now-btn" class="btn btn-primary btn-sm shadow-sm px-4 py-2 ml-4" title="Refresh Now">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    @php
        $widgets = $config['widgets'] ?? [];
        $rows = [];
        $currentRow = [];

        foreach ($widgets as $widget) {
            $type = $widget['type'] ?? 'unknown';

            if ($type === 'break') {
                if (!empty($currentRow)) {
                    $rows[] = $currentRow;
                    $currentRow = [];
                }
                continue;
            }

            if ($type === 'none') continue;

            // Auto-break if limit reached (Max 6 columns)
            if (count($currentRow) >= 6) {
                $rows[] = $currentRow;
                $currentRow = [];
            }

            $currentRow[] = $widget;
        }
        if (!empty($currentRow)) {
            $rows[] = $currentRow;
        }
    @endphp

    @foreach($rows as $row)
        @php $count = count($row); @endphp
        
        <div class="dashboard-row" style="--cols: {{ $count }};">
            @foreach($row as $widget)
                @php 
                    $type = $widget['type'] ?? 'unknown'; 
                    // Handling full width types inside the grid by preserving them or putting them in 1-col rows?
                    // If a row has a table, typically users put a break before/after.
                    // If they didn't, we render it as just another widget, but Tables might overflow if squeezed.
                    // Using min-width: 0 helps prevent grid blowout.
                @endphp

                @if(in_array($type, ['table', 'leaderboard', 'timeline', 'log-stream', 'html-card']))
                     <div class="glass-card widget-container col-span-full" 
                          data-widget-type="{{ $type }}"
                          data-widget-key="{{ $widget['key'] ?? '' }}" 
                          data-config='@json($widget)'>
        
                        <h3 class="mb-4">{{ $widget['label'] ?? '' }}</h3>
                        <div class="widget-body">
                            <div class="text-center text-muted p-4">Waiting for data...</div>
                        </div>
                    </div>
                @else
                    {{-- Visual Style for static/alert widgets --}}
                    @php
                        $statusMap = ['success' => 'var(--success)', 'warning' => 'var(--warning)', 'danger' => 'var(--danger)', 'info' => 'var(--info)'];
                        $borderStyle = '';
                        if(in_array($type, ['alert-card', 'info']) && isset($widget['status'])) {
                            $color = $statusMap[$widget['status']] ?? 'var(--info)';
                            $borderStyle = "border-left: 4px solid $color;";
                        }
                    @endphp

                    <div class="stat-card widget-container" 
                        data-widget-type="{{ $type }}" 
                        data-widget-key="{{ $widget['key'] ?? '' }}"
                        data-config='@json($widget)'
                        style="{{ $borderStyle }}">
                        
                        {{-- Initial Skeleton --}}
                        <div class="stat-label">{{ $widget['label'] ?? '' }}</div>
                        <div class="widget-body">
                            @if($type === 'info')
                                {{-- Static Content for Info Widget --}}
                                @if(!empty($widget['title']) || !empty($widget['value']))
                                    <div class="stat-value" style="font-size:1.2rem;">{{ $widget['value'] ?? $widget['title'] ?? '' }}</div>
                                @endif
                                @if(!empty($widget['desc']))
                                    <div class="stat-desc mt-2">{{ $widget['desc'] }}</div>
                                @endif
                            @else
                                <div class="spinner" style="display:block; border-color: rgba(255,255,255,0.1); border-top-color: var(--text-muted);"></div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach
</div>

<script>
    // Minimal config injection just for data fetching, NOT for structure
    window.DASHBOARD_REFRESH_RATE = {{ $refreshRate ?? 60 }};
</script>