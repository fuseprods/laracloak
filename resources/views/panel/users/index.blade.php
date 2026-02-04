@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('User Management') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.users.create') }}" class="btn btn-primary">
                <span>➕</span> {{ __('Create User') }}
            </a>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('panel.users') }}" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <input type="text" name="search" placeholder="{{ __('Search by name or email...') }}"
                    value="{{ request('search') }}">
            </div>
            <div style="width: 200px;">
                <select name="role" onchange="this.form.submit()">
                    <option value="">{{ __('All Roles') }}</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                    <option value="editor" {{ request('role') == 'editor' ? 'selected' : '' }}>{{ __('Editor') }}</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>{{ __('User') }}</option>
                </select>
            </div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email Address') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Joined') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td style="font-weight: 500;">
                                <div>{{ $user->name }}</div>
                            </td>
                            <td style="color: var(--text-muted);">{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-{{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.875rem;">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.75rem;">
                                    <a href="{{ route('panel.users.edit', $user) }}" class="btn btn-sm btn-primary">
                                        {{ __('Edit') }}
                                    </a>
                                    <a href="{{ route('panel.users.permissions', $user) }}" class="btn btn-sm"
                                        style="background: var(--bg-dark); border: 1px solid var(--border);">
                                        {{ __('Permissions') }}
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('panel.users.delete', $user) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
@endsection