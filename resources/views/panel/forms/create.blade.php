@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Create Outbound Form') }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.forms.index') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="content-section wide">
        <form method="POST" action="{{ route('panel.forms.store') }}">
            @csrf

            <!-- Basic Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label for="slug">{{ __('URL Slug') }}</label>
                    <div style="display: flex; align-items: center;">
                        <span
                            style="padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-right: none; border-radius: 0.5rem 0 0 0.5rem; color: var(--text-muted);">/front/</span>
                        <input type="text" id="slug" name="slug" value="{{ old('slug') }}" placeholder="contact-form"
                            style="border-radius: 0 0.5rem 0.5rem 0;" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category-select">{{ __('Organization (Categories)') }}</label>
                    <select id="category-select" name="categories[]" multiple
                        placeholder="{{ __('Select categories...') }}">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ is_array(old('categories')) && in_array($category->id, old('categories')) ? 'selected' : '' }}>📂 {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Destination Configuration -->
            <div
                style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">
                    {{ __('🔌 Endpoint Configuration') }}
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="credential_id">{{ __('Credential (Optional)') }}</label>
                        <select id="credential_id" name="credential_id">
                            <option value="">{{ __('-- No Authentication --') }}</option>
                            @foreach($credentials as $credential)
                                <option value="{{ $credential->id }}" {{ old('credential_id') == $credential->id ? 'selected' : '' }}>{{ $credential->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="upstream_method">{{ __('Method') }}</label>
                        <select id="upstream_method" name="upstream_method" required>
                            <option value="POST">POST</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="destination_url">{{ __('Destination URL') }}</label>
                    <input type="url" id="destination_url" name="destination_url" value="{{ old('destination_url') }}"
                        placeholder="https://n8n.example.com/webhook/..." required>
                </div>
            </div>

            <!-- Specialized Form Properties -->
            <div
                style="background: rgba(59, 130, 246, 0.05); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid rgba(59, 130, 246, 0.2); margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1rem; color: #3b82f6;">{{ __('📝 Form Handlers') }}</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="success_message">{{ __('Success Message') }}</label>
                        <input type="text" id="success_message" name="success_message"
                            value="{{ old('success_message', __('Petición enviada correctamente.')) }}">
                    </div>
                    <div class="form-group">
                        <label for="redirect_url">{{ __('Redirect URL (Optional)') }}</label>
                        <input type="url" id="redirect_url" name="redirect_url" value="{{ old('redirect_url') }}"
                            placeholder="https://example.com/thanks">
                    </div>
                </div>
            </div>

            <!-- Test Area -->
            <div id="test-area"
                style="background: rgba(15, 23, 42, 0.5); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="font-size: 1rem; color: var(--text-secondary);">🔍 {{ __('Test Connection') }}</h3>
                    <button type="button" id="btn-test-upstream" class="btn btn-sm"
                        style="background: var(--primary); color: white; border: none;">
                        {{ __('Run Test Call') }}
                    </button>
                </div>

                <div id="test-results" style="display: none;">
                    <div style="background: #000; border-radius: 0.25rem; padding: 1rem; position: relative;">
                        <div id="test-status"
                            style="position: absolute; top: 0.5rem; right: 0.5rem; font-size: 0.75rem; font-weight: bold;">
                        </div>
                        <pre id="test-output"
                            style="color: #10b981; font-family: monospace; font-size: 0.8125rem; overflow: auto; max-height: 300px; white-space: pre-wrap;"></pre>
                    </div>
                </div>
                <div id="test-loader" style="display: none; text-align: center; padding: 1rem; color: var(--text-muted);">
                    {{ __('Connecting to upstream...') }}
                </div>
            </div>

            <script>
                document.getElementById('btn-test-upstream').addEventListener('click', async function () {
                    const btn = this;
                    const results = document.getElementById('test-results');
                    const output = document.getElementById('test-output');
                    const status = document.getElementById('test-status');
                    const loader = document.getElementById('test-loader');

                    const data = {
                        destination_url: document.getElementById('destination_url').value,
                        upstream_method: document.getElementById('upstream_method').value,
                        credential_id: document.getElementById('credential_id').value,
                        response_filters: "", // Forms usually don't filter much but we could add the field later
                    };

                    if (!data.destination_url) {
                        alert('{{ __("Please enter a destination URL first.") }}');
                        return;
                    }

                    btn.disabled = true;
                    loader.style.display = 'block';
                    results.style.display = 'none';

                    try {
                        const response = await fetch('{{ route("panel.pages.test") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();
                        loader.style.display = 'none';
                        results.style.display = 'block';

                        if (response.ok && result.ok) {
                            status.textContent = 'OK';
                            status.style.color = '#10b981';
                            output.textContent = `${result.message || 'Connection successful.'} (HTTP ${result.status})`;
                            if (window.pageBuilder && typeof window.pageBuilder.setAvailableInput === 'function') {
                                window.pageBuilder.setAvailableInput(result.payload ?? null);
                            }
                        } else {
                            status.textContent = 'ERROR';
                            status.style.color = '#ef4444';
                            output.textContent = result.message || 'Connection failed.';
                            if (window.pageBuilder && typeof window.pageBuilder.setAvailableInput === 'function') {
                                window.pageBuilder.setAvailableInput(null);
                            }
                        }
                    } catch (e) {
                        loader.style.display = 'none';
                        results.style.display = 'block';
                        status.textContent = 'EXCEPTION';
                        status.style.color = '#ef4444';
                        output.textContent = '{{ __("Connection test failed.") }}';
                        if (window.pageBuilder && typeof window.pageBuilder.setAvailableInput === 'function') {
                            window.pageBuilder.setAvailableInput(null);
                        }
                    } finally {
                        btn.disabled = false;
                    }
                });
            </script>

            <!-- UI Configuration - Visual Builder -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <label style="margin: 0;">{{ __('Form Fields') }}</label>
                    <button type="button" id="toggle-json-mode" class="btn btn-sm"
                        style="background: var(--bg-card); border: 1px solid var(--border);">
                        <span id="toggle-json-label">⚙️ {{ __('Advanced (JSON)') }}</span>
                    </button>
                </div>

                <!-- Visual Builder -->
                <div id="visual-builder-container"></div>

                <!-- JSON Mode (Hidden by default) -->
                <div id="json-mode" style="display: none;">
                    <textarea id="config" name="config" rows="12"
                        style="width: 100%; font-family: monospace; background: #0f172a; color: #a5b4fc; padding: 1rem; border: 1px solid var(--border); border-radius: 0.5rem;"
                        placeholder='{ ... }'>{{ old('config', '{"fields": []}') }}</textarea>
                </div>
            </div>

            <link rel="stylesheet" href="{{ route('panel.assets.page-builder.css') }}?v={{ time() }}">
            <script src="{{ route('panel.assets.page-builder.js') }}?v={{ time() }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const container = document.getElementById('visual-builder-container');
                    const jsonTextarea = document.getElementById('config');
                    const jsonMode = document.getElementById('json-mode');
                    const toggleBtn = document.getElementById('toggle-json-mode');
                    const toggleLabel = document.getElementById('toggle-json-label');

                    let isJsonMode = false;
                    let initialConfig = {};

                    try {
                        const configText = jsonTextarea.value.trim();
                        initialConfig = configText ? JSON.parse(configText) : {};
                    } catch (e) {
                        initialConfig = {};
                    }

                    @include('panel.pages.partials.page-builder-translations')
                    const builder = new PageBuilder(container, {
                        pageType: 'form',
                        config: initialConfig,
                        translations: window.pageBuilderTranslations,
                        onConfigChange: function (config) {
                            jsonTextarea.value = JSON.stringify(config, null, 2);
                        }
                    });

                    window.pageBuilder = builder;

                    toggleBtn.addEventListener('click', function () {
                        isJsonMode = !isJsonMode;

                        if (isJsonMode) {
                            container.style.display = 'none';
                            jsonMode.style.display = 'block';
                            toggleLabel.innerHTML = '🎨 {{ __("Visual Builder") }}';
                        } else {
                            try {
                                const newConfig = JSON.parse(jsonTextarea.value);
                                builder.rows = [];
                                builder.loadConfig(newConfig);
                            } catch (e) {
                                alert('{{ __("Invalid JSON. Please fix the syntax before switching to visual mode.") }}');
                                isJsonMode = true;
                                return;
                            }
                            container.style.display = 'block';
                            jsonMode.style.display = 'none';
                            toggleLabel.innerHTML = '⚙️ {{ __("Advanced (JSON)") }}';
                        }
                    });
                });
            </script>

            <div
                style="margin-top: 2rem; background: rgba(0,0,0,0.1); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer;">
                <label for="is_published" style="margin-bottom: 0; cursor: pointer; font-weight: 500;">
                    🚀 {{ __('Publish this form immediately') }}
                    <small
                        style="display: block; color: var(--text-muted); font-weight: normal;">{{ __('If unchecked, it will be saved as a draft and only visible to staff.') }}</small>
                </label>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{{ __('Create Form') }}</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect('#category-select', { plugins: ['remove_button'] });
        });
    </script>
@endsection
