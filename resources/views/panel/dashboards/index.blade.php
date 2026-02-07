@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Dashboard Management') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.dashboards.create') }}" class="btn btn-primary">
                <span>➕</span> {{ __('Create Dashboard') }}
            </a>
        </div>
    </div>

    <div class="content-section full-width">
        <form method="GET" action="{{ route('panel.dashboards.index') }}"
            style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <input type="text" name="search" placeholder="{{ __('Search dashboards by slug or source...') }}"
                    value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Slug / Public URL') }}</th>
                        <th>{{ __('Data Source') }}</th>
                        <th>{{ __('Refresh') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dashboards as $dashboard)
                        <tr>
                            <td style="font-weight: 500;">
                                <div>{{ $dashboard->slug }}</div>
                                <small style="color: var(--text-muted); font-family: monospace;">
                                    <a href="{{ route('front.show', $dashboard->slug) }}" target="_blank"
                                        style="color: inherit; text-decoration: none;">
                                        /front/{{ $dashboard->slug }} ↗
                                    </a>
                                </small>
                            </td>
                            <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                                {{ $dashboard->upstream_method }} {{ Str::limit($dashboard->destination_url, 30) }}
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                    {{ $dashboard->refresh_rate ?? 60 }}s
                                </span>
                            </td>
                            <td>
                                @if($dashboard->is_published)
                                    <span class="badge badge-admin">{{ __('Published') }}</span>
                                @else
                                    <span class="badge badge-user">{{ __('Draft') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.75rem;">
                                    <a href="{{ route('front.show', $dashboard->slug) }}" target="_blank" class="btn btn-sm"
                                        style="background: var(--bg-dark); border: 1px solid var(--border);">
                                        {{ __('Preview') }}
                                    </a>
                                    <a href="{{ route('panel.dashboards.edit', $dashboard) }}" class="btn btn-sm btn-primary">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('panel.dashboards.destroy', $dashboard) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                                {{ __('No dashboards found. Start by creating a new inbound dashboard.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $dashboards->links() }}
        </div>
    </div>
@endsection