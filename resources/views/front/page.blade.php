@extends('layouts.guest')

@section('content')
    <div class="{{ $page->type === 'form' ? 'container container-sm' : 'welcome-portal-container' }}">
        @if($page->type === 'form')
            <x-front.form :config="$page->config ?? []" :slug="$page->slug" />
        @elseif($page->type === 'dashboard')
            <x-front.dashboard :config="$page->config ?? []" :slug="$page->slug" :refresh-rate="$page->refresh_rate"
                :page-id="$page->id" :can-edit="Auth::check() && Auth::user()->can('update', $page)" />
        @else
            <div class="alert alert-error">
                Unknown page type: {{ $page->type }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('js/front.js') }}?v={{ time() }}"></script>
@endpush