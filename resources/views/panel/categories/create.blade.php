@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Create Page Category') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.categories.index') }}" class="btn btn-secondary">
                {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="card card-container">
        <form action="{{ route('panel.categories.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">{{ __('Category Name (e.g., Sales Dashboards)') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    placeholder="{{ __('Enter category name...') }}">
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="description">{{ __('Description') }}</label>
                <textarea name="description" id="description" rows="3"
                    placeholder="{{ __('Optional description...') }}">{{ old('description') }}</textarea>
                @error('description') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="card-actions">
                <button type="submit" class="btn btn-primary">{{ __('Create Category') }}</button>
            </div>
        </form>
    </div>
@endsection