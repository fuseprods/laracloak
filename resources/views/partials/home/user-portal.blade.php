<div class="dashboard-header mb-8">
    <h1 class="welcome-text">{{ __('Welcome, :name', ['name' => Auth::user()->name]) }}</h1>
    <p class="subtitle">{{ __('Select an application to start or manage the platform.') }}</p>
</div>

<div class="stats-grid">
    <!-- Admin Card -->
    @if(Auth::user()->role === 'admin')
        <a href="{{ route('panel.index') }}" class="glass-card stat-card" style="border-color: var(--primary-glow);">
            <div class="card-icon">⚡</div>
            <div class="card-title">{{ __('Admin Panel') }}</div>
            <div class="card-desc">
                {{ __('Access global settings, user management, security, and system logs.') }}
            </div>
            <div class="card-footer">
                <span>{{ __('Configuration') }}</span>
                <span>&rarr;</span>
            </div>
        </a>
    @endif

    <!-- User Pages -->
    @forelse($pages as $page)
        <a href="{{ route('front.show', $page->slug) }}" class="glass-card stat-card">
            <div class="card-icon">🚀</div>
            <div class="card-title">{{ ucfirst($page->slug) }}</div>
            <div class="card-desc">
                {{ $page->description ?? __('Access the automated tool :name.', ['name' => ucfirst($page->slug)]) }}
            </div>
            <div class="card-footer">
                <span><span class="status-dot"></span>{{ __('Active') }}</span>
                <span>{{ __('Open') }} &rarr;</span>
            </div>
        </a>
    @empty
        @if(Auth::user()->role !== 'admin')
            <div
                style="grid-column: 1/-1; text-align: center; padding: 4rem; color: var(--text-muted); border: 1px dashed var(--border); border-radius: 12px;">
                <p>{{ __('You currently have no assigned applications.') }}</p>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;">{{ __('Contact an administrator to request access.') }}</p>
            </div>
        @endif
    @endforelse
</div>