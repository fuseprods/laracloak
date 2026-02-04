<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="theme-{{ auth()->check() ? auth()->user()->theme : 'dark' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laracloak') }}@yield('title_suffix')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('laracloak.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('laracloak.png') }}?v=1">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core Design System -->
    <link href="{{ route('assets.panel.css') }}?v={{ time() }}" rel="stylesheet">
    <link id="theme-link"
        href="{{ route('assets.theme.css', auth()->check() ? auth()->user()->theme : 'dark') }}?v={{ time() }}"
        rel="stylesheet">

    @stack('styles')
</head>

<body>
    @yield('body_content')

    <!-- Global Components (Toasts, etc.) -->
    @if (session('success') || session('error'))
        <div class="toast {{ session('success') ? 'toast-success' : 'toast-error' }} show" id="toast">
            <span>{{ session('success') ? '✅' : '❌' }}</span>
            <div>
                <strong>{{ session('success') ? __('Success') : __('Error') }}</strong>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
                    {{ session('success') ?? session('error') }}
                </p>
            </div>
        </div>
        <script>
            setTimeout(() => { document.getElementById('toast').classList.remove('show'); }, 5000);
        </script>
    @endif

    @stack('scripts')
</body>

</html>