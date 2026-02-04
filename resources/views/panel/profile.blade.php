@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('My Profile') }}</h1>
    </div>

    {{-- Account Settings Form --}}
    <div class="card card-container">
        <h3 class="card-title">👤 {{ __('Account Settings') }}</h3>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">{{ __('Full Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">{{ __('Email Address') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="locale">{{ __('Language') }}</label>
                <select id="locale" name="locale" class="form-control">
                    <option value="en" {{ old('locale', $user->locale) === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                    <option value="es" {{ old('locale', $user->locale) === 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                </select>
                @error('locale')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-section">
                <h4 class="card-subtitle">{{ __('Change Password (Optional)') }}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">{{ __('New Password') }}</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current">
                        @error('password')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">{{ __('Confirm New Password') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>

            <div class="card-actions">
                <button type="submit" class="btn btn-primary">{{ __('Save Account') }}</button>
            </div>
        </form>
    </div>

    {{-- Theme Selection Form --}}
    <div class="card card-container">
        <h3 class="card-title">🎨 {{ __('Interface Theme') }}</h3>
        <p class="card-subtitle">{{ __('Choose your preferred interface appearance.') }}</p>

        <form id="theme-form" method="POST" action="{{ route('theme.update') }}">
            @csrf
            <input type="hidden" name="theme" id="theme-input" value="{{ auth()->user()->theme }}">

            <div class="theme-grid">
                {{-- Dark Theme --}}
                <div class="theme-option {{ auth()->user()->theme === 'dark' ? 'active' : '' }}" data-theme="dark">
                    <div class="theme-preview theme-preview-dark">
                        <div class="preview-header"></div>
                        <div class="preview-content">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                    </div>
                    <span class="theme-label">🌙 {{ __('Dark') }}</span>
                    <span class="theme-desc">{{ __('Darkly inspired') }}</span>
                </div>

                {{-- Light Theme --}}
                <div class="theme-option {{ auth()->user()->theme === 'light' ? 'active' : '' }}" data-theme="light">
                    <div class="theme-preview theme-preview-light">
                        <div class="preview-header"></div>
                        <div class="preview-content">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                    </div>
                    <span class="theme-label">☀️ {{ __('Light') }}</span>
                    <span class="theme-desc">{{ __('Flatly inspired') }}</span>
                </div>

                {{-- Glass Theme --}}
                <div class="theme-option {{ auth()->user()->theme === 'glass' ? 'active' : '' }}" data-theme="glass">
                    <div class="theme-preview theme-preview-glass">
                        <div class="preview-header"></div>
                        <div class="preview-content">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                    </div>
                    <span class="theme-label">🧊 {{ __('Glass') }}</span>
                    <span class="theme-desc">{{ __('Glassmorphism') }}</span>
                </div>
            </div>

            <div class="card-actions">
                <button type="submit" class="btn btn-primary">{{ __('Save Theme') }}</button>
            </div>
        </form>
    </div>

    <script>
        // Theme selection (preview only, does not save until form submit)
        document.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', function () {
                const theme = this.dataset.theme;

                // Update hidden input
                document.getElementById('theme-input').value = theme;

                // Update active state
                document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');

                // Preview theme immediately (but don't save yet)
                const html = document.documentElement;
                html.classList.remove('theme-dark', 'theme-light', 'theme-glass');
                html.classList.add(`theme-${theme}`);

                // Swap theme CSS file for accurate preview of theme-specific colors
                const themeLink = document.getElementById('theme-link');
                if (themeLink) {
                    // We can reconstruct the route URL pattern here
                    const baseUrl = "{{ route('assets.theme.css', ':theme') }}";
                    themeLink.href = baseUrl.replace(':theme', theme);
                }
            });
        });

        // Handle form submission via AJAX
        document.getElementById('theme-form').addEventListener('submit', async function (e) {
            e.preventDefault();

            const theme = document.getElementById('theme-input').value;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.textContent = "{{ __('Saving...') }}";
            btn.disabled = true;

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ theme: theme })
                });

                if (!response.ok) throw new Error("{{ __('Failed to save theme') }}");

                btn.textContent = "{{ __('✓ Saved!') }}";
                setTimeout(() => { btn.textContent = originalText; btn.disabled = false; }, 2000);

            } catch (err) {
                console.error(err);
                btn.textContent = "{{ __('Error!') }}";
                setTimeout(() => { btn.textContent = originalText; btn.disabled = false; }, 2000);
            }
        });
    </script>
@endsection