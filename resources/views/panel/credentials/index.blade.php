@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Credentials') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.credentials.create') }}" class="btn btn-primary">
                <span>➕</span> {{ __('Add Credential') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Allowed Domains') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($credentials as $credential)
                        <tr>
                            <td style="font-weight: 500;">
                                {{ $credential->name }}
                            </td>
                            <td>
                                <span class="badge"
                                    style="background: rgba(16, 185, 129, 0.1); color: #10b981; text-transform: uppercase;">
                                    {{ $credential->type }}
                                </span>
                            </td>
                            <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                                @if(empty($credential->allowed_domains))
                                    <span style="color: var(--danger);">{{ __('No domains (Blocked)') }}</span>
                                @else
                                    {{ __(':count patterns', ['count' => count($credential->allowed_domains)]) }}
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ Str::limit(implode(', ', $credential->allowed_domains), 40) }}
                                    </div>
                                @endif
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">
                                {{ $credential->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.75rem;">
                                    <a href="{{ route('panel.credentials.edit', $credential) }}" class="btn btn-sm btn-primary">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('panel.credentials.destroy', $credential) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Delete this credential? Used by :count pages.', ['count' => $credential->pages_count ?? 0]) }}');">
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
                                {{ __('No credentials found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $credentials->links() }}
        </div>
    </div>
@endsection