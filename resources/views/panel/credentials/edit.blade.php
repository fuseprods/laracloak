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

    <div class="content-section wide">
        <form method="POST" action="{{ route('panel.credentials.update', $credential) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">{{ __('Friendly Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $credential->name) }}" class="form-control"
                    required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group mb-8">
                <label for="type">{{ __('Authentication Type') }}</label>
                <select id="type" name="type" class="form-control" required onchange="updateAuthFields()">
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
                @error('type') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <!-- Dynamic Auth Fields -->
            <div class="auth-container">
                <h3 class="auth-title">{{ __('Credentials') }}</h3>

                <div class="alert-info-light">
                    💡
                    {{ __('Leave secrets empty to keep the existing ones. Only fill them if you want to update the authentication values.') }}
                </div>

                <div id="field-auth-key" class="form-group">
                    <label id="label-auth-key" for="auth_key">{{ __('Username') }}</label>
                    <input type="text" id="auth_key" name="auth_key" value="{{ old('auth_key', $credential->auth_key) }}"
                        class="form-control" placeholder="{{ __('username') }}">
                    @error('auth_key') <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div id="field-auth-value" class="form-group">
                    <label id="label-auth-value" for="auth_value">{{ __('Password') }}</label>
                    <input type="text" id="auth_value" name="auth_value" value="" class="form-control"
                        placeholder="{{ __('Leave empty to keep current secret...') }}">
                    @error('auth_value') <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <!-- JWT Config (Reordered: Type -> Algorithm -> Keys) -->
                <div id="jwt-config-section" class="jwt-config-container">

                    <!-- 1. Key Type -->
                    <div class="form-group">
                        <label for="key_type">{{ __('Key Type') }}</label>
                        <select id="key_type" class="form-control" onchange="updateJwtFields()">
                            <option value="hmac">{{ __('HMAC Secret (Shared)') }}</option>
                            <option value="pem">{{ __('PEM Key (RSA/EC)') }}</option>
                        </select>
                    </div>

                    <!-- 2. Algorithm -->
                    <div id="field-algorithm" class="form-group">
                        <label for="jwt_alg">{{ __('Algorithm') }}</label>
                        <select id="jwt_alg" class="form-control" onchange="syncJwtSettings()">
                            <!-- Populated by JS -->
                        </select>
                    </div>

                    <!-- 3. Keys / Secrets -->

                    <!-- HMAC Secret -->
                    <div id="field-hmac-secret" class="form-group" style="display: none;">
                        <label for="jwt_hmac_secret">{{ __('Shared Secret') }}</label>
                        <input type="text" id="jwt_hmac_secret" class="form-control code-input"
                            placeholder="{{ __('Leave empty to keep existing secret') }}"
                            oninput="syncJwtHmacToAuthValue()">
                    </div>

                    <!-- PEM Private Key -->
                    <div id="field-private-key" class="form-group" style="display: none;">
                        <label for="jwt_private_key">{{ __('Private Key') }}</label>
                        <textarea id="jwt_private_key" rows="4" class="code-input"
                            placeholder="{{ __('Leave empty to keep existing key') }}"
                            oninput="syncJwtToAuthValue()"></textarea>
                    </div>

                    <!-- PEM Public Key -->
                    <div id="field-public-key" class="form-group" style="display: none;">
                        <label for="jwt_public_key">{{ __('Public Key') }}</label>
                        <textarea id="jwt_public_key" rows="4" class="code-input" oninput="syncJwtSettings()"></textarea>
                    </div>

                    <!-- Hidden JSON settings storage -->
                    <div class="form-group">
                        <textarea id="settings" name="settings"
                            style="display:none;">{{ old('settings', json_encode($credential->settings)) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-group mb-8">
                <label for="allowed_domains">{{ __('Allowed Domains (Whitelist)') }}</label>
                <textarea id="allowed_domains" name="allowed_domains" rows="5" class="code-input"
                    placeholder="*.api-service.com">{{ old('allowed_domains', implode("\n", $credential->allowed_domains ?? [])) }}</textarea>
                <small class="helper-text">
                    {{ __('Enter one domain pattern per line. Wildcards (*) are supported.') }}
                </small>
                @error('allowed_domains') <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{{ __('Update Credential') }}</button>
            </div>
        </form>
    </div>

    <script>
        // Init state from server
        const initialSettings = @json($credential->settings ?? []);

        function updateAuthFields() {
            const type = document.getElementById('type').value;
            const keyGroup = document.getElementById('field-auth-key');
            const keyLabel = document.getElementById('label-auth-key');
            const keyInput = document.getElementById('auth_key');

            const valueLabel = document.getElementById('label-auth-value');
            const valueInput = document.getElementById('auth_value');
            const valueGroup = document.getElementById('field-auth-value');

            const jwtSection = document.getElementById('jwt-config-section');

            // Reset visibility
            keyGroup.style.display = 'block';
            valueGroup.style.display = 'block';
            jwtSection.style.display = 'none';

            if (type === 'basic') {
                keyLabel.innerText = '{{ __('Username') }}';
                valueLabel.innerText = '{{ __('Password') }}';
                valueInput.placeholder = '{{ __('Enter new password to update...') }}';
            } else if (type === 'header') {
                keyLabel.innerText = '{{ __('Header Name') }}';
                valueLabel.innerText = '{{ __('Header Value') }}';
                valueInput.placeholder = '{{ __('Enter new value to update...') }}';
            } else if (type === 'jwt') {
                keyGroup.style.display = 'none';
                valueGroup.style.display = 'none'; // Hide global password for JWT

                jwtSection.style.display = 'block';
                updateJwtFields();
            }
        }

        function updateJwtFields() {
            // Determine initial Key Type
            let currentKeyType = document.getElementById('key_type').value;

            // Only override if it's the first load (we can check if alg is populated maybe?)
            if (document.getElementById('jwt_alg').options.length === 0) {
                if (initialSettings.mode === 'generation') {
                    if (initialSettings.alg && initialSettings.alg.startsWith('RS')) {
                        currentKeyType = 'pem';
                    } else {
                        currentKeyType = 'hmac';
                    }
                } else {
                    currentKeyType = 'hmac'; // Default fallback
                }
                document.getElementById('key_type').value = currentKeyType;
            }

            const keyType = document.getElementById('key_type').value;

            const hmacGroup = document.getElementById('field-hmac-secret');
            const privateKeyGroup = document.getElementById('field-private-key');
            const publicKeyGroup = document.getElementById('field-public-key');

            // Reset
            hmacGroup.style.display = 'none';
            privateKeyGroup.style.display = 'none';
            publicKeyGroup.style.display = 'none';

            if (keyType === 'hmac') {
                hmacGroup.style.display = 'block';
                fillAlgorithms(['HS256', 'HS384', 'HS512']);
                syncJwtSettings();
            }
            else if (keyType === 'pem') {
                privateKeyGroup.style.display = 'block';
                publicKeyGroup.style.display = 'block';
                fillAlgorithms(['RS256', 'RS384', 'RS512']);
                syncJwtSettings();
            }
        }

        function fillAlgorithms(algos) {
            const select = document.getElementById('jwt_alg');
            const current = initialSettings.alg || select.value; // Try to keep existing or settings value
            select.innerHTML = '';
            algos.forEach(alg => {
                const opt = document.createElement('option');
                opt.value = alg;
                opt.innerText = alg;
                select.appendChild(opt);
            });
            // Select if exists in new list
            if (algos.includes(current)) {
                select.value = current;
            } else {
                select.value = algos[0];
            }
        }

        function syncJwtHmacToAuthValue() {
            const secret = document.getElementById('jwt_hmac_secret').value;
            if (secret) {
                document.getElementById('auth_value').value = secret;
            }
        }

        function syncJwtToAuthValue() {
            const secret = document.getElementById('jwt_private_key').value;
            if (secret) {
                document.getElementById('auth_value').value = secret;
            }
        }

        function syncJwtSettings() {
            const keyType = document.getElementById('key_type').value;

            const alg = document.getElementById('jwt_alg').value;
            const pubKey = document.getElementById('jwt_public_key').value;

            // Start with existing settings to preserve unknown claims or create new
            const settings = initialSettings || {};
            settings.mode = 'generation';
            settings.alg = alg;
            if (!settings.claims) settings.claims = { iss: window.location.origin };

            if (keyType === 'pem' && pubKey) {
                settings.public_key = pubKey;
            }

            document.getElementById('settings').value = JSON.stringify(settings, null, 4);
        }

        // Hydrate public key
        if (initialSettings.public_key) {
            document.getElementById('jwt_public_key').value = initialSettings.public_key;
        }

        // Init
        updateAuthFields();
    </script>
@endsection