@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Edit Form: :slug', ['slug' => $form->slug]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.forms.index') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to List') }}
            </a>
            <a href="{{ route('front.show', $form->slug) }}" target="_blank" class="btn btn-primary">
                <span>👁️</span> {{ __('Preview') }}
            </a>
        </div>
    </div>

    <div class="content-section wide">
        <form method="POST" action="{{ route('panel.forms.update', $form) }}">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label for="slug">{{ __('URL Slug') }}</label>
                    <div style="display: flex; align-items: center;">
                        <span
                            style="padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-right: none; border-radius: 0.5rem 0 0 0.5rem; color: var(--text-muted);">/front/</span>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $form->slug) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category-select">{{ __('Categories') }}</label>
                    <select id="category-select" name="categories[]" multiple>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $form->categories->contains($category->id) ? 'selected' : '' }}>📂 {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div
                style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">
                    {{ __('🔌 Endpoint Configuration') }}
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="credential_id">{{ __('Credential') }}</label>
                        <select id="credential_id" name="credential_id">
                            <option value="">{{ __('-- No Authentication --') }}</option>
                            @foreach($credentials as $credential)
                                <option value="{{ $credential->id }}" {{ $form->credential_id == $credential->id ? 'selected' : '' }}>{{ $credential->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="upstream_method">{{ __('Method') }}</label>
                        <select name="upstream_method" readonly>
                            <option value="POST">POST</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mt-4">
                    <label for="destination_url">{{ __('Destination URL') }}</label>
                    <input type="url" id="destination_url" name="destination_url"
                        value="{{ old('destination_url', $form->destination_url) }}" required>
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
                            value="{{ old('success_message', $form->success_message) }}">
                    </div>
                    <div class="form-group">
                        <label for="redirect_url">{{ __('Redirect URL (Optional)') }}</label>
                        <input type="url" id="redirect_url" name="redirect_url"
                            value="{{ old('redirect_url', $form->redirect_url) }}">
                    </div>
                </div>
            </div>

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
                        placeholder='{ ... }'>{{ old('config', json_encode($form->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
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

            <!-- Access Control (simplified for the example) -->
            @include('panel.pages.partials.permissions', ['object' => $form])

            <div
                style="margin-top: 2rem; background: rgba(0,0,0,0.1); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $form->is_published) ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer;">
                <label for="is_published" style="margin-bottom: 0; cursor: pointer; font-weight: 500;">
                    🚀 {{ __('Publish this form') }}
                    <small
                        style="display: block; color: var(--text-muted); font-weight: normal;">{{ __('If unchecked, it will be saved as a draft and only visible to staff.') }}</small>
                </label>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="submit" class="btn btn-primary">{{ __('Update Form') }}</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect('#category-select', { plugins: ['remove_button'] });
        });
    </script>
@endsection
