@extends('layouts.base')

@section('title_suffix', ' - Panel')

@push('styles')
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <style>
        /* Global Tom Select Fixes for current themes */
        .ts-control {
            background: var(--bg-input) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border) !important;
            border-radius: 0.5rem !important;
        }

        .ts-dropdown {
            background: var(--bg-card) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border) !important;
        }

        .ts-dropdown .active {
            background: var(--primary) !important;
            color: white !important;
        }

        .ts-dropdown .option {
            color: var(--text-main) !important;
        }

        .ts-control .item {
            background: var(--primary) !important;
            color: white !important;
            border-radius: 4px !important;
        }

        .theme-glass .ts-control {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1) !important;
        }
    </style>
@endpush

@section('body_content')
    <!-- Panel Header -->
    <header class="panel-header">
        <a href="{{ url('/') }}" class="brand" title="{{ __('Back to Portal') }}">
            <img src="{{ asset('laracloak.png') }}" onerror="this.outerHTML='🛡️'" alt="Icon">
            <span>{{ config('app.name', 'Laracloak') }}</span>
        </a>

        <div class="header-right" style="display: flex; align-items: center; gap: 1rem;">
            <span class="text-muted" style="font-size: 0.875rem;">{{ auth()->user()->name }}</span>
            <div
                style="width: 32px; height: 32px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
        </div>
    </header>

    <div style="display: flex;">
        <!-- Sidebar -->
        <aside class="sidebar">
            <nav>
                <a href="{{ route('panel.index') }}"
                    class="nav-link {{ request()->routeIs('panel.index') ? 'active' : '' }}">
                    <span>📊</span> {{ __('Dashboard') }}
                </a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('panel.users') }}"
                        class="nav-link {{ request()->routeIs('panel.users*') ? 'active' : '' }}">
                        <span>👤</span> {{ __('Users') }}
                    </a>
                    <a href="{{ route('panel.groups.index') }}"
                        class="nav-link {{ request()->routeIs('panel.groups*') ? 'active' : '' }}">
                        <span>👥</span> {{ __('User Groups') }}
                    </a>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'editor', 'staff']))
                    <div class="nav-label"
                        style="padding: 1rem 1rem 0.5rem; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">
                        {{ __('Management') }}</div>
                    <a href="{{ route('panel.forms.index') }}"
                        class="nav-link {{ request()->routeIs('panel.forms*') ? 'active' : '' }}">
                        <span>📝</span> {{ __('Forms') }}
                    </a>
                    <a href="{{ route('panel.dashboards.index') }}"
                        class="nav-link {{ request()->routeIs('panel.dashboards*') ? 'active' : '' }}">
                        <span>📊</span> {{ __('Dashboards') }}
                    </a>
                    <a href="{{ route('panel.categories.index') }}"
                        class="nav-link {{ request()->routeIs('panel.categories*') ? 'active' : '' }}">
                        <span>📂</span> {{ __('Categories') }}
                    </a>
                @endif

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('panel.credentials.index') }}"
                        class="nav-link {{ request()->routeIs('panel.credentials*') ? 'active' : '' }}">
                        <span>🔑</span> {{ __('Credentials') }}
                    </a>
                @endif
            </nav>
            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border);">
                <a href="{{ route('profile.show') }}"
                    class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span>⚙️</span> {{ __('My Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link"
                        style="width: 100%; background: none; border: none; cursor: pointer; text-align: left;">
                        <span>🚪</span> {{ __('Logout') }}
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>
@endsection