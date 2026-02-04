@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Page Management') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.pages.create') }}" class="btn btn-primary">
                <span>➕</span> {{ __('Create Page') }}
            </a>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('panel.pages.index') }}"
            style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <input type="text" name="search" placeholder="{{ __('Search pages by slug or destination...') }}"
                    value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Slug / Public URL') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Destination') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td style="font-weight: 500;">
                                <div>{{ $page->slug }}</div>
                                <small style="color: var(--text-muted); font-family: monospace;">
                                    <a href="{{ route('front.show', $page->slug) }}" target="_blank"
                                        style="color: inherit; text-decoration: none;">
                                        /front/{{ $page->slug }} ↗
                                    </a>
                                </small>
                            </td>
                            <td>
                                @if($page->type == 'dashboard')
                                    <span class="badge"
                                        style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">{{ __('Dashboard') }}</span>
                                @else
                                    <span class="badge"
                                        style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">{{ __('Form') }}</span>
                                @endif
                            </td>
                            <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                                {{ $page->upstream_method }} {{ Str::limit($page->destination_url, 30) }}
                            </td>
                            <td>
                                @if($page->is_published)
                                    <span class="badge badge-admin">{{ __('Published') }}</span>
                                @else
                                    <span class="badge badge-user">{{ __('Draft') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.75rem;">
                                    <a href="{{ route('front.show', $page->slug) }}" target="_blank" class="btn btn-sm"
                                        style="background: var(--bg-dark); border: 1px solid var(--border);">
                                        {{ __('Preview') }}
                                    </a>
                                    <a href="{{ route('panel.pages.edit', $page) }}" class="btn btn-sm btn-primary">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('panel.pages.destroy', $page) }}" method="POST"
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
                                {{ __('No pages found. Start by creating a new proxy page.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $pages->links() }}
        </div>
    </div>
@endsection