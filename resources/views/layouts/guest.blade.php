@extends('layouts.base')

@section('body_content')
    <header class="panel-header" style="justify-content: space-between;">
        <a href="{{ url('/') }}" class="brand">
            <img src="{{ asset('laracloak.png') }}" onerror="this.outerHTML='🛡️'" alt="Icon">
            <span>{{ config('app.name', 'Laracloak') }}</span>
        </a>
        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <!-- Language Switcher -->
            <div class="locale-switcher" style="display: flex; gap: 0.5rem; font-size: 0.8rem; font-weight: 600;">
                <a href="?lang=en"
                    style="color: {{ app()->getLocale() == 'en' ? 'var(--primary)' : 'var(--text-muted)' }}; text-decoration: none;">EN</a>
                <span style="color: var(--border);">|</span>
                <a href="?lang=es"
                    style="color: {{ app()->getLocale() == 'es' ? 'var(--primary)' : 'var(--text-muted)' }}; text-decoration: none;">ES</a>
            </div>

            @auth
                <a href="{{ route('panel.index') }}" class="btn btn-primary"
                    style="padding: 0.5rem 1rem; font-size: 0.875rem;">{{ __('Go to Panel') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-muted"
                        style="background:none; border:none; cursor:pointer; font-size: 0.875rem;">{{ __('Logout') }}</button>
                </form>
            @endauth
        </div>
    </header>

    <main style="flex: 1; display: flex; flex-direction: column; margin-top: var(--header-height); padding: 2rem 0;">
        @yield('content')
    </main>

    <footer
        style="border-top: 1px solid var(--border); padding: 2rem; text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-top: 4rem;">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laracloak') }}. {{ __('All rights reserved.') }}</p>
    </footer>
@endsection