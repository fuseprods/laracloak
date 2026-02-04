@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Dashboard') }}</h1>
        <div class="user-menu">
            <span>{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card">
            <h3 style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">👥 {{ __('Total Groups') }}
            </h3>
            <div style="font-size: 2rem; font-weight: 700;">{{ $stats['groups'] }}</div>
        </div>
        <div class="card">
            <h3 style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">📂 {{ __('Categories') }}</h3>
            <div style="font-size: 2rem; font-weight: 700;">{{ $stats['categories'] }}</div>
        </div>
        <div class="card">
            <h3 style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">📝 {{ __('Forms') }}</h3>
            <div style="font-size: 2rem; font-weight: 700;">{{ $stats['forms'] }}</div>
        </div>
        <div class="card">
            <h3 style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">📊 {{ __('Dashboards') }}</h3>
            <div style="font-size: 2rem; font-weight: 700;">{{ $stats['dashboards'] }}</div>
        </div>
        <div class="card">
            <h3 style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">👤 {{ __('User Count') }}</h3>
            <div style="font-size: 2rem; font-weight: 700;">{{ $stats['users'] }}</div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">{{ __('Recent Audit Logs') }}</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Action') }}</th>
                        <th>{{ __('Target') }}</th>
                        <th>{{ __('IP Address') }}</th>
                        <th>{{ __('Time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['recent_logs'] as $log)
                        <tr>
                            <td>
                                @if($log->user)
                                    {{ $log->user->name }}
                                @else
                                    <span style="color: var(--text-muted);">{{ __('System') }}</span>
                                @endif
                            </td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->target_type }} #{{ $log->target_id }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection