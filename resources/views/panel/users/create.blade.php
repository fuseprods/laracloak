@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Create User') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.users') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to Users') }}
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <form method="POST" action="{{ route('panel.users.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">{{ __('Full Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">{{ __('Email Address') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="role">{{ __('Role') }}</label>
                <select id="role" name="role" required>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>{{ __('User (Restricted Access)') }}
                    </option>
                    <option value="editor" {{ old('role') == 'editor' ? 'selected' : '' }}>
                        {{ __('Editor (Can Manage Pages)') }}
                    </option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>{{ __('Admin (Full Access)') }}
                    </option>
                </select>
                @error('role')
                    <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    @error('password')
                        <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">
                    {{ __('Create User') }}
                </button>
            </div>
        </form>
    </div>
@endsection