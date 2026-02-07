@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Edit User: :name', ['name' => $user->name]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.users') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to Users') }}
            </a>
        </div>
    </div>

    <div class="content-section wide">
        <form method="POST" action="{{ route('panel.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">{{ __('Full Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">{{ __('Email Address') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="role">{{ __('Role') }}</label>
                <select id="role" name="role" required>
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>
                        {{ __('User (Restricted Access)') }}
                    </option>
                    <option value="editor" {{ old('role', $user->role) == 'editor' ? 'selected' : '' }}>
                        {{ __('Editor (Can Manage Pages)') }}
                    </option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                        {{ __('Admin (Full Access)') }}
                    </option>
                </select>
                @error('role')
                    <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin: 2rem 0; border-top: 1px solid var(--border); padding-top: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">{{ __('Change Password (Optional)') }}</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="password">{{ __('New Password') }}</label>
                        <input type="password" id="password" name="password"
                            placeholder="{{ __('Leave blank to keep current') }}">
                        @error('password')
                            <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">{{ __('Confirm New Password') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">
                    {{ __('Update User') }}
                </button>
            </div>
        </form>
    </div>
@endsection