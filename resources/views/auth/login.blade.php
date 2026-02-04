@extends('layouts.guest')

@section('title_suffix', ' - Login')

@push('styles')
    <style>
        #login-card {
            max-width: 700px !important;
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="container py-16 flex justify-center items-center" style="flex: 1;">
        <div class="glass-card" id="login-card">
            <div class="text-center mb-8">
                <h1>{{ __('Welcome Back!') }}</h1>
                <p class="subtitle" style="margin-bottom: 0;">{{ __('Please login to your account') }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">{{ __('Email Address') }}</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}"
                        placeholder="{{ __('Email Address') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">{{ __('Password') }}</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="{{ __('Password') }}" required>
                </div>

                <div class="flex items-center mb-4" style="gap: 0.5rem;">
                    <input type="checkbox" id="remember" name="remember" style="width: auto;">
                    <label class="form-label" for="remember"
                        style="margin-bottom: 0; cursor: pointer;">{{ __('Remember me') }}</label>
                </div>

                <div class="mt-8">
                    <button type="submit" class="btn btn-primary btn-block">{{ __('Login') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection