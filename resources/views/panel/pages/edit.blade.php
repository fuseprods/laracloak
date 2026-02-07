@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Edit Page: :slug', ['slug' => $page->slug]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.pages.index') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to List') }}
            </a>
            <a href="{{ route('front.show', $page->slug) }}" target="_blank" class="btn btn-primary">
                <span>👁️</span> {{ __('Preview') }}
            </a>
        </div>
    </div>

    <div class="content-section wide">
        <form method="POST" action="{{ route('panel.pages.update', $page) }}">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label for="slug">{{ __('URL Slug') }}</label>
                    <div style="display: flex; align-items: center;">
                        <span
                            style="padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-right: none; border-radius: 0.5rem 0 0 0.5rem; color: var(--text-muted);">/front/</span>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $page->slug) }}"
                            placeholder="my-dashboard" style="border-radius: 0 0.5rem 0.5rem 0;" required>
                    </div>
                    @error('slug')
                        <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type">{{ __('Page Type') }}</label>
                    <select id="type" name="type" required>
                        <option value="form" {{ old('type', $page->type) == 'form' ? 'selected' : '' }}>{{ __('Form (Input Data)') }}
                        </option>
                        <option value="dashboard" {{ old('type', $page->type) == 'dashboard' ? 'selected' : '' }}>{{ __('Dashboard (View Data)') }}</option>
                    </select>
                </div>
            </div>

            <!-- Page Categories -->
            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">{{ __('Page Categories') }}</label>
                <div class="form-group">
                    <select id="category-select" name="categories[]" multiple placeholder="{{ __('Search and select categories...') }}" autocomplete="off">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $page->categories->contains($category->id) ? 'selected' : '' }}>
                                📂 {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Direct Access Control -->
            <div style="margin-bottom: 2rem; background: rgba(0,0,0,0.1); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">{{ __('🔒 Access Control (Who can see this Page?)') }}</h3>
                <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 1.5rem;">
                    {{ __('Specify which users or groups have direct access to THIS page. Type 3+ chars to search.') }}
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <!-- Groups Section -->
                    <div class="form-group">
                        <label>👥 {{ __('User Groups (Can View)') }}</label>
                        <select class="tag-select" name="view_groups[]" multiple placeholder="{{ __('Add groups to view...') }}">
                            @foreach($groups as $group)
                                @php $hasPerm = $pagePermissions->where('subject_type', get_class($group))->where('subject_id', $group->id)->where('can_view', true)->isNotEmpty(); @endphp
                                <option value="{{ $group->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🛠️ {{ __('User Groups (Can Edit)') }}</label>
                        <select class="tag-select" name="edit_groups[]" multiple placeholder="{{ __('Add groups to edit...') }}">
                            @foreach($groups as $group)
                                @php $hasPerm = $pagePermissions->where('subject_type', get_class($group))->where('subject_id', $group->id)->where('can_edit', true)->isNotEmpty(); @endphp
                                <option value="{{ $group->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Users Section -->
                    <div class="form-group">
                        <label>👤 {{ __('Individual Users (Can View)') }}</label>
                        <select class="tag-select" name="view_users[]" multiple placeholder="{{ __('Add users to view...') }}">
                            @foreach($users as $user)
                                @php $hasPerm = $pagePermissions->where('subject_type', get_class($user))->where('subject_id', $user->id)->where('can_view', true)->isNotEmpty(); @endphp
                                <option value="{{ $user->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🛠️ {{ __('Individual Users (Can Edit)') }}</label>
                        <select class="tag-select" name="edit_users[]" multiple placeholder="{{ __('Add users to edit...') }}">
                            @foreach($users as $user)
                                @php $hasPerm = $pagePermissions->where('subject_type', get_class($user))->where('subject_id', $user->id)->where('can_edit', true)->isNotEmpty(); @endphp
                                <option value="{{ $user->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('select[multiple]').forEach(el => {
                        new TomSelect(el, {
                            plugins: ['remove_button'],
                            create: false,
                            maxOptions: 50,
                            onType: function(str) {
                                if (str.length > 0 && str.length < 3) {
                                    this.close();
                                } else if (str.length >= 3) {
                                    this.open();
                                }
                            }
                        });
                    });
                });
            </script>

            <!-- Upstream Configuration -->
            <div
                style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">{{ __('🔌 Destination Configuration') }}</h3>

                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="credential_id">{{ __('Credential (Optional)') }}</label>
                        <select id="credential_id" name="credential_id">
                            <option value="">{{ __('-- No Authentication --') }}</option>
                            @foreach($credentials as $credential)
                                <option value="{{ $credential->id }}" {{ old('credential_id', $page->credential_id) == $credential->id ? 'selected' : '' }}>
                                    {{ $credential->name }}
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">{{ __('Select a credential to secure requests to this destination.') }}</small>
                    </div>
                </div>

                 <div style="display: grid; grid-template-columns: 140px 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="upstream_method">{{ __('Method') }}</label>
                        <select id="upstream_method" name="upstream_method" required>
                            <option value="POST" {{ old('upstream_method', $page->upstream_method) == 'POST' ? 'selected' : '' }}>POST</option>
                            <option value="GET" {{ old('upstream_method', $page->upstream_method) == 'GET' ? 'selected' : '' }}>GET</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="destination_url">{{ __('Destination URL') }}</label>
                        <input type="url" id="destination_url" name="destination_url"
                            value="{{ old('destination_url', $page->destination_url) }}"
                            placeholder="https://api.example.com/webhook/..." required>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">{{ __('The full URL where the request will be proxied to.') }}</small>
                    </div>
                </div>
                @error('destination_url')
                    <div style="color: var(--danger); font-size: 0.875rem;">{{ $message }}</div>
                @enderror

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="response_filters">{{ __('Response Key Filters (Optional)') }}</label>
                    <input type="text" id="response_filters" name="response_filters" value="{{ old('response_filters', is_array($page->response_filters) ? implode(', ', $page->response_filters) : '') }}"
                        placeholder="ej: internal_id, meta, secret_token">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        {{ __('Comma-separated list of JSON keys to recursively remove from the upstream response.') }}
                    </small>
                </div>
            </div>

            <!-- Test Area -->
            <div id="test-area" style="background: rgba(15, 23, 42, 0.5); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="font-size: 1rem; color: var(--text-secondary);">🔍 {{ __('Test Connection') }}</h3>
                    <button type="button" id="btn-test-upstream" class="btn btn-sm" style="background: var(--primary); color: white; border: none;">
                        {{ __('Run Test Call') }}
                    </button>
                </div>
                
                <div id="test-results" style="display: none;">
                    <div style="background: #000; border-radius: 0.25rem; padding: 1rem; position: relative;">
                        <div id="test-status" style="position: absolute; top: 0.5rem; right: 0.5rem; font-size: 0.75rem; font-weight: bold;"></div>
                        <pre id="test-output" style="color: #10b981; font-family: monospace; font-size: 0.8125rem; overflow: auto; max-height: 300px; white-space: pre-wrap;"></pre>
                    </div>
                </div>
                <div id="test-loader" style="display: none; text-align: center; padding: 1rem; color: var(--text-muted);">
                    {{ __('Connecting to upstream...') }}
                </div>
            </div>

            <script>
                document.getElementById('btn-test-upstream').addEventListener('click', async function() {
                    const btn = this;
                    const results = document.getElementById('test-results');
                    const output = document.getElementById('test-output');
                    const status = document.getElementById('test-status');
                    const loader = document.getElementById('test-loader');
                    
                    const data = {
                        destination_url: document.getElementById('destination_url').value,
                        upstream_method: document.getElementById('upstream_method').value,
                        credential_id: document.getElementById('credential_id').value,
                        response_filters: document.getElementById('response_filters').value,
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

                        if (response.ok) {
                            status.textContent = `HTTP ${result.status}`;
                            status.style.color = '#10b981';
                            output.textContent = result.formatted;
                            
                            // Extract fields and pass to PageBuilder
                            if (result.data && window.pageBuilder && typeof extractFieldKeys === 'function') {
                                const fields = extractFieldKeys(result.data);
                                window.pageBuilder.setAvailableFields(fields);
                            }
                        } else {
                            status.textContent = 'ERROR';
                            status.style.color = '#ef4444';
                            output.textContent = result.error || 'Unknown error';
                        }
                    } catch (e) {
                        loader.style.display = 'none';
                        results.style.display = 'block';
                        status.textContent = 'EXCEPTION';
                        status.style.color = '#ef4444';
                        output.textContent = e.message;
                    } finally {
                        btn.disabled = false;
                    }
                });
            </script>

            <!-- UI Configuration - Visual Builder -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <label style="margin: 0;">{{ __('UI Configuration') }}</label>
                    <button type="button" id="toggle-json-mode" class="btn btn-sm" style="background: var(--bg-card); border: 1px solid var(--border);">
                        <span id="toggle-json-label">⚙️ {{ __('Advanced (JSON)') }}</span>
                    </button>
                </div>
                
                <!-- Visual Builder -->
                <div id="visual-builder-container"></div>
                
                <!-- JSON Mode (Hidden by default) -->
                <div id="json-mode" style="display: none;">
                    <textarea id="config" name="config" rows="15"
                        style="width: 100%; padding: 1rem; background: #0f172a; border: 1px solid var(--border); border-radius: 0.5rem; color: #a5b4fc; font-family: monospace; line-height: 1.5;"
                        placeholder='{ ... }'>{{ old('config', json_encode($page->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                </div>
                
                @error('config')
                    <div style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror
            </div>
            
            <link rel="stylesheet" href="{{ asset('css/page-builder.css') }}">
            <script src="{{ asset('js/page-builder.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.getElementById('visual-builder-container');
                    const jsonTextarea = document.getElementById('config');
                    const jsonMode = document.getElementById('json-mode');
                    const toggleBtn = document.getElementById('toggle-json-mode');
                    const toggleLabel = document.getElementById('toggle-json-label');
                    
                    let isJsonMode = false;
                    let initialConfig = {};
                    
                    // Parse initial config
                    try {
                        const configText = jsonTextarea.value.trim();
                        initialConfig = configText ? JSON.parse(configText) : {};
                    } catch (e) {
                        console.error('Invalid initial config JSON:', e);
                        initialConfig = {};
                    }
                    
                    // Initialize Page Builder
                    const builder = new PageBuilder(container, {
                        pageType: '{{ $page->type }}',
                        config: initialConfig,
                        onConfigChange: function(config) {
                            jsonTextarea.value = JSON.stringify(config, null, 2);
                        }
                    });
                    
                    // Expose builder globally for test call integration
                    window.pageBuilder = builder;
                    
                    // Toggle JSON Mode
                    toggleBtn.addEventListener('click', function() {
                        isJsonMode = !isJsonMode;
                        
                        if (isJsonMode) {
                            container.style.display = 'none';
                            jsonMode.style.display = 'block';
                            toggleLabel.innerHTML = '🎨 {{ __("Visual Builder") }}';
                        } else {
                            // Parse JSON and reload builder
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

            <!-- Publishing -->
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }} style="width: 1.25rem; height: 1.25rem;">
                    <span>{{ __('Publish this page immediately') }}</span>
                </label>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="button" class="btn btn-danger"
                    onclick="if(confirm('{{ __('Delete this page permanently?') }}')) document.getElementById('delete-form').submit();">
                    {{ __('Delete Page') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('Update Page') }}
                </button>
            </div>
        </form>

        <form id="delete-form" action="{{ route('panel.pages.destroy', $page) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection