@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Page Categories') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.categories.create') }}" class="btn btn-primary">
                <span>➕</span> {{ __('Create Category') }}
            </a>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('panel.categories.index') }}"
            style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <input type="text" name="search" placeholder="{{ __('Search categories by name or description...') }}"
                    value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Pages') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td style="font-weight: 600;">{{ $category->name }}</td>
                            <td style="color: var(--text-muted);">{{ $category->description ?? __('No description') }}</td>
                            <td>
                                <span class="badge badge-admin">
                                    {{ __(':count pages', ['count' => $category->pages_count]) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.75rem;">
                                    <a href="{{ route('panel.categories.edit', $category) }}" class="btn btn-sm btn-primary">
                                        {{ __('Edit / Permissions') }}
                                    </a>
                                    <form action="{{ route('panel.categories.destroy', $category) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Are you sure? This will NOT delete pages, only the category.') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $categories->links() }}
        </div>
    </div>
@endsection