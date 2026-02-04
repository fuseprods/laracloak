@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Edit Credential: :name', ['name' => $credential->name]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.credentials.index') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <form method="POST" action="{{ route('panel.credentials.update', $credential) }}">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="name">{{ __('Friendly Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $credential->name) }}" required>
                @error('name') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="type">{{ __('Authentication Type') }}</label>
                <select id="type" name="type" required onchange="updateAuthFields()">
                    <option value="basic" {{ old('type', $credential->type) == 'basic' ? 'selected' : '' }}>
                        {{ __('Basic Auth (User/Pass)') }}
                    </option>
                    <option value="header" {{ old('type', $credential->type) == 'header' ? 'selected' : '' }}>
                        {{ __('Header Auth (Key/Value)') }}
                    </option>
                    <option value="jwt" {{ old('type', $credential->type) == 'jwt' ? 'selected' : '' }}>
                        {{ __('JWT / Bearer Token') }}
                    </option>
                </select>
                @error('type') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div> @enderror
            </div>

            <!-- Dynamic Auth Fields -->
            <div
                style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">{{ __('Credentials') }}</h3>

                <div class="alert"
                    style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 0.75rem; border-radius: 0.25rem; margin-bottom: 1rem; font-size: 0.85rem;">
                    💡
                    {{ __('Leave secrets empty to keep the existing ones. Only fill them if you want to update the authentication values.') }}
                </div>

                <div id="field-auth-key" class="form-group" style="margin-bottom: 1rem;">
                    <label id="label-auth-key" for="auth_key">{{ __('Username') }}</label>
                    <input type="text" id="auth_key" name="auth_key" value="{{ old('auth_key', $credential->auth_key) }}"
                        placeholder="{{ __('username') }}">
                    @error('auth_key') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div id="field-auth-value" class="form-group">
                    <label id="label-auth-value" for="auth_value">{{ __('Password') }}</label>
                    <input type="text" id="auth_value" name="auth_value" value=""
                        placeholder="{{ __('Leave empty to keep current secret...') }}">
                    @error('auth_value') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="allowed_domains">{{ __('Allowed Domains (Whitelist)') }}</label>
                <textarea id="allowed_domains" name="allowed_domains" rows="5"
                    style="width: 100%; padding: 1rem; background: #0f172a; border: 1px solid var(--border); border-radius: 0.5rem; color: #e2e8f0; font-family: monospace;"
                    placeholder="*.api-service.com">{{ old('allowed_domains', implode("\n", $credential->allowed_domains ?? [])) }}</textarea>
                <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                    {{ __('Enter one domain pattern per line. Wildcards (*) are supported.') }}
                </small>
                @error('allowed_domains') <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{{ __('Update Credential') }}</button>
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
                valueLabel.innerText = '{{ __('Password') }}';
                valueInput.placeholder = '{{ __('Enter new password to update...') }}';
            } else if (type === 'header') {
                keyGroup.style.display = 'block';
                keyLabel.innerText = '{{ __('Header Name') }}';
                valueLabel.innerText = '{{ __('Header Value') }}';
                valueInput.placeholder = '{{ __('Enter new value to update...') }}';
            } else if (type === 'jwt') {
                keyGroup.style.display = 'none';
                valueLabel.innerText = '{{ __('Bearer Token') }}';
                valueInput.placeholder = '{{ __('Enter new token to update...') }}';
            }
        }
        // Init
        updateAuthFields();
    </script>
@endsection