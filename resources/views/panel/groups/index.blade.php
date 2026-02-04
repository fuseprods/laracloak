@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('User Groups / Departments') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.groups.create') }}" class="btn btn-primary">
                <span>➕</span> {{ __('Create Group') }}
            </a>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('panel.groups.index') }}"
            style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <input type="text" name="search" placeholder="{{ __('Search groups by name or description...') }}"
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
                        <th>{{ __('Users') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        <tr>
                            <td style="font-weight: 600;">{{ $group->name }}</td>
                            <td style="color: var(--text-muted);">{{ $group->description ?? __('No description') }}</td>
                            <td>
                                <span class="badge badge-editor">
                                    {{ __(':count users', ['count' => $group->users_count]) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.75rem;">
                                    <a href="{{ route('panel.groups.edit', $group) }}" class="btn btn-sm btn-primary">
                                        {{ __('Edit / Permissions') }}
                                    </a>
                                    <form action="{{ route('panel.groups.destroy', $group) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Are you sure? This will NOT delete users, only the group.') }}');">
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
            {{ $groups->links() }}
        </div>
    </div>
@endsection