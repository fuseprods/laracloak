@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Add Credential') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.credentials.index') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="content-section wide">
        <form method="POST" action="{{ route('panel.credentials.store') }}">
            @csrf

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="name">{{ __('Friendly Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                    placeholder="{{ __('e.g. Production API') }}" required>
                @error('name') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="type">{{ __('Authentication Type') }}</label>
                <select id="type" name="type" required onchange="updateAuthFields()">
                    <option value="basic" {{ old('type') == 'basic' ? 'selected' : '' }}>{{ __('Basic Auth (User/Pass)') }}
                    </option>
                    <option value="header" {{ old('type') == 'header' ? 'selected' : '' }}>{{ __('Header Auth (Key/Value)') }}
                    </option>
                    <option value="jwt" {{ old('type') == 'jwt' ? 'selected' : '' }}>{{ __('JWT / Bearer Token') }}</option>
                </select>
                @error('type') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div> @enderror
            </div>

            <!-- Dynamic Auth Fields -->
            <div
                style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <h3 id="auth-section-title" style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">
                    {{ __('Credentials') }}
                </h3>

                <div id="field-auth-key" class="form-group" style="margin-bottom: 1rem;">
                    <label id="label-auth-key" for="auth_key">{{ __('Username') }}</label>
                    <input type="text" id="auth_key" name="auth_key" value="{{ old('auth_key') }}"
                        placeholder="{{ __('username') }}">
                    @error('auth_key') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div id="field-auth-value" class="form-group">
                    <label id="label-auth-value" for="auth_value">{{ __('Password') }}</label>
                    <input type="text" id="auth_value" name="auth_value" value="{{ old('auth_value') }}"
                        placeholder="{{ __('secret') }}" required>
                    @error('auth_value') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="allowed_domains">{{ __('Allowed Domains (Whitelist)') }}</label>
                <textarea id="allowed_domains" name="allowed_domains" rows="5"
                    style="width: 100%; padding: 1rem; background: #0f172a; border: 1px solid var(--border); border-radius: 0.5rem; color: #e2e8f0; font-family: monospace;"
                    placeholder="*.api-service.com&#10;api.example.com&#10;https://specific-service.com">{{ old('allowed_domains') }}</textarea>
                <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                    {{ __('Enter one domain pattern per line. Wildcards (*) are supported.') }} <br>
                    Examples: <code>*.api-service.com</code> or <code>192.168.1.50</code>
                </small>
                @error('allowed_domains') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{{ __('Save Credential') }}</button>
            </div>
        </form>
    </div>

    <script>
        function updateAuthFields() {
            const type = document.getElementById('type').value;
            const keyGroup = document.getElementById('field-auth-key');
            const keyLabel = document.getElementById('label-auth-key');
            const keyInput = document.getElementById('auth_key');

            const valueLabel = document.getElementById('label-auth-value');
            const valueInput = document.getElementById('auth_value');

            if (type === 'basic') {
                keyGroup.style.display = 'block';
                keyLabel.innerText = '{{ __('Username') }}';
                keyInput.placeholder = 'username';
                valueLabel.innerText = '{{ __('Password') }}';
                valueInput.placeholder = 'password';
            } else if (type === 'header') {
                keyGroup.style.display = 'block';
                keyLabel.innerText = '{{ __('Header Name') }}';
                keyInput.placeholder = 'X-API-KEY';
                valueLabel.innerText = '{{ __('Header Value') }}';
                valueInput.placeholder = 'secret-value';
            } else if (type === 'jwt') {
                keyGroup.style.display = 'none';
                valueLabel.innerText = '{{ __('Bearer Token') }}';
                valueInput.placeholder = 'eyJhbGciOi...';
            }
        }
        // Init
        updateAuthFields();
    </script>
@endsection