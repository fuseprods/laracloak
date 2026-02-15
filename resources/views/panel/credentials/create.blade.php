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

            <div class="form-group">
                <label for="name">{{ __('Friendly Name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control"
                    placeholder="{{ __('e.g. Production API') }}" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group mb-8">
                <label for="type">{{ __('Authentication Type') }}</label>
                <select id="type" name="type" class="form-control" required onchange="updateAuthFields()">
                    <option value="basic" {{ old('type') == 'basic' ? 'selected' : '' }}>{{ __('Basic Auth (User/Pass)') }}
                    </option>
                    <option value="header" {{ old('type') == 'header' ? 'selected' : '' }}>{{ __('Header Auth (Key/Value)') }}
                    </option>
                    <option value="jwt" {{ old('type') == 'jwt' ? 'selected' : '' }}>{{ __('JWT / Bearer Token') }}</option>
                </select>
                @error('type') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <!-- Dynamic Auth Fields -->
            <div class="auth-container">
                <h3 id="auth-section-title" class="auth-title">
                    {{ __('Credentials') }}
                </h3>

                <!-- Basic/Header Fields -->
                <div id="field-auth-key" class="form-group">
                    <label id="label-auth-key" for="auth_key">{{ __('Username') }}</label>
                    <input type="text" id="auth_key" name="auth_key" value="{{ old('auth_key') }}" class="form-control"
                        placeholder="{{ __('username') }}">
                    @error('auth_key') <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div id="field-auth-value" class="form-group">
                    <label id="label-auth-value" for="auth_value">{{ __('Password') }}</label>
                    <input type="text" id="auth_value" name="auth_value" value="{{ old('auth_value') }}"
                        class="form-control" placeholder="{{ __('secret') }}">
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
                            placeholder="{{ __('secret-key') }}" oninput="syncJwtHmacToAuthValue()">
                    </div>

                    <!-- PEM Private Key -->
                    <div id="field-private-key" class="form-group" style="display: none;">
                        <label for="jwt_private_key">{{ __('Private Key') }}</label>
                        <textarea id="jwt_private_key" rows="4" class="code-input"
                            oninput="syncJwtToAuthValue()"></textarea>
                    </div>

                    <!-- PEM Public Key -->
                    <div id="field-public-key" class="form-group" style="display: none;">
                        <label for="jwt_public_key">{{ __('Public Key') }}</label>
                        <textarea id="jwt_public_key" rows="4" class="code-input" oninput="syncJwtSettings()"></textarea>
                    </div>

                    <!-- Hidden JSON settings storage -->
                    <div class="form-group">
                        <textarea id="settings" name="settings" style="display:none;">{{ old('settings') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-group mb-8">
                <label for="allowed_domains">{{ __('Allowed Domains (Whitelist)') }}</label>
                <textarea id="allowed_domains" name="allowed_domains" rows="5" class="code-input"
                    placeholder="*.api-service.com&#10;api.example.com&#10;https://specific-service.com">{{ old('allowed_domains') }}</textarea>
                <small class="helper-text">
                    {{ __('Enter one domain pattern per line. Wildcards (*) are supported.') }} <br>
                    Examples: <code>*.api-service.com</code> or <code>192.168.1.50</code>
                </small>
                @error('allowed_domains') <div class="error-text">{{ $message }}</div>
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
            const valueGroup = document.getElementById('field-auth-value');

            const jwtSection = document.getElementById('jwt-config-section');

            // Reset visibility
            keyGroup.style.display = 'block';
            valueGroup.style.display = 'block';
            jwtSection.style.display = 'none';

            if (type === 'basic') {
                keyLabel.innerText = '{{ __('Username') }}';
                keyInput.placeholder = 'username';
                valueLabel.innerText = '{{ __('Password') }}';
                valueInput.placeholder = 'password';
            } else if (type === 'header') {
                keyLabel.innerText = '{{ __('Header Name') }}';
                keyInput.placeholder = 'X-API-KEY';
                valueLabel.innerText = '{{ __('Header Value') }}';
                valueInput.placeholder = 'secret-value';
            } else if (type === 'jwt') {
                keyGroup.style.display = 'none';
                valueGroup.style.display = 'none'; // Hide global password, use specific fields

                // Show JWT Config
                jwtSection.style.display = 'block';
                updateJwtFields();
            }
        }

        function updateJwtFields() {
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
            const current = select.value;
            select.innerHTML = '';
            algos.forEach(alg => {
                const opt = document.createElement('option');
                opt.value = alg;
                opt.innerText = alg;
                select.appendChild(opt);
            });
            if (algos.includes(current)) {
                select.value = current;
            } else {
                select.value = algos[0];
            }
        }

        function syncJwtHmacToAuthValue() {
            const secret = document.getElementById('jwt_hmac_secret').value;
            document.getElementById('auth_value').value = secret;
        }

        function syncJwtToAuthValue() {
            const secret = document.getElementById('jwt_private_key').value;
            document.getElementById('auth_value').value = secret;
        }

        function syncJwtSettings() {
            const keyType = document.getElementById('key_type').value;
            const alg = document.getElementById('jwt_alg').value;
            const pubKey = document.getElementById('jwt_public_key').value;

            const settings = {
                mode: 'generation',
                alg: alg,
                claims: {
                    iss: window.location.origin
                }
            };

            if (keyType === 'pem' && pubKey) {
                settings.public_key = pubKey;
            }

            document.getElementById('settings').value = JSON.stringify(settings, null, 4);
        }

        // Init
        updateAuthFields();
    </script>
@endsection