<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="theme-{{ auth()->check() ? auth()->user()->theme : 'dark' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Design System (Protected for authenticated users) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @auth
        <link href="{{ route('assets.panel.css') }}" rel="stylesheet">
        <link href="{{ route('assets.theme.css', auth()->user()->theme) }}" rel="stylesheet">
    @else
        <link href="{{ asset('css/front.css') }}?v={{ time() }}" rel="stylesheet">
    @endauth
</head>

<body>
    @yield('content')

    <!-- Core Engine -->
    <script src="{{ asset('js/front.js') }}?v={{ time() }}"></script>
</body>

</html>