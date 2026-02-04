@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Edit Dashboard: :slug', ['slug' => $dashboard->slug]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.dashboards.index') }}" class="btn"
                style="background: var(--bg-card); border: 1px solid var(--border);">
                <span>⬅️</span> {{ __('Back to List') }}
            </a>
            <a href="{{ route('front.show', $dashboard->slug) }}" target="_blank" class="btn btn-primary">
                <span>👁️</span> {{ __('Preview') }}
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <form method="POST" action="{{ route('panel.dashboards.update', $dashboard) }}">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label for="slug">{{ __('URL Slug') }}</label>
                    <div style="display: flex; align-items: center;">
                        <span
                            style="padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-right: none; border-radius: 0.5rem 0 0 0.5rem; color: var(--text-muted);">/front/</span>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $dashboard->slug) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category-select">{{ __('Categories') }}</label>
                    <select id="category-select" name="categories[]" multiple>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $dashboard->categories->contains($category->id) ? 'selected' : '' }}>📂 {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div
                style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">
                    {{ __('🔌 Data Source Configuration') }}
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="credential_id">{{ __('Credential (Optional)') }}</label>
                        <select id="credential_id" name="credential_id">
                            <option value="">{{ __('-- No Authentication --') }}</option>
                            @foreach($credentials as $credential)
                                <option value="{{ $credential->id }}" {{ $dashboard->credential_id == $credential->id ? 'selected' : '' }}>{{ $credential->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="refresh_rate">{{ __('Auto Refresh') }}</label>
                        <select id="refresh_rate" name="refresh_rate">
                            @foreach([5 => '5s', 10 => '10s', 30 => '30s', 60 => '1m', 300 => '5m', 900 => '15m', 1800 => '30m', 3600 => '1h', 0 => 'Off'] as $val => $label)
                                <option value="{{ $val }}" {{ old('refresh_rate', $dashboard->refresh_rate) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="upstream_method">{{ __('Method') }}</label>
                        <select id="upstream_method" name="upstream_method" required>
                            <option value="GET" {{ $dashboard->upstream_method == 'GET' ? 'selected' : '' }}>GET</option>
                            <option value="POST" {{ $dashboard->upstream_method == 'POST' ? 'selected' : '' }}>POST</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="destination_url">{{ __('Source URL') }}</label>
                        <input type="url" id="destination_url" name="destination_url"
                            value="{{ old('destination_url', $dashboard->destination_url) }}" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="response_filters">{{ __('Response Key Filters (Optional)') }}</label>
                    <input type="text" id="response_filters" name="response_filters"
                        value="{{ old('response_filters', implode(', ', (array) ($dashboard->response_filters ?? []))) }}"
                        placeholder="ej: internal_id, secret">
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
                        response_filters: document.getElementById('response_filters').value,
                    };

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

            <div class="form-group">
                <label for="config">{{ __('Dashboard Widgets (JSON)') }}</label>
                <textarea id="config" name="config" rows="12"
                    style="font-family: monospace; background: #0f172a; color: #a5b4fc; padding: 1rem;">{{ old('config', json_encode($dashboard->config, JSON_PRETTY_PRINT)) }}</textarea>
            </div>

            @include('panel.pages.partials.permissions', ['object' => $dashboard])

            <div
                style="margin-top: 2rem; background: rgba(0,0,0,0.1); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $dashboard->is_published) ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer;">
                <label for="is_published" style="margin-bottom: 0; cursor: pointer; font-weight: 500;">
                    🚀 {{ __('Publish this dashboard') }}
                    <small
                        style="display: block; color: var(--text-muted); font-weight: normal;">{{ __('If unchecked, it will be saved as a draft and only visible to staff.') }}</small>
                </label>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="submit" class="btn btn-primary">{{ __('Update Dashboard') }}</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect('#category-select', { plugins: ['remove_button'] });
        });
    </script>
@endsection