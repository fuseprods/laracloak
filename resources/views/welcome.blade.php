@extends('layouts.guest')

@section('content')
    <div class="welcome-portal-container">
        @auth
            @include('partials.home.user-portal')
        @else
            @include('partials.home.guest-landing')
        @endauth
    </div>
@endsection