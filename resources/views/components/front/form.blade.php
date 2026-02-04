@props(['config', 'slug'])

<div class="glass-card container-sm mx-auto">
    <h1 class="text-center mb-2">{{ $config['title'] ?? 'Untitled Form' }}</h1>
    @if(isset($config['description']))
        <p class="subtitle text-center">{{ $config['description'] }}</p>
    @endif

    <form id="dynamic-form" data-slug="{{ $slug }}">
        <div id="form-error" class="alert alert-error" style="display: none;"></div>
        <div id="form-success" class="alert alert-success" style="display: none;"></div>

        @foreach($config['fields'] ?? [] as $field)
            <div class="form-group">
                <label class="form-label">
                    {{ $field['label'] ?? $field['name'] }}
                    @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                </label>

                @if(($field['type'] ?? 'text') === 'textarea')
                    <textarea name="{{ $field['name'] }}" class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}"
                        @if(!empty($field['required'])) required @endif></textarea>

                @elseif(($field['type'] ?? 'text') === 'select')
                    <select name="{{ $field['name'] }}" class="form-control" @if(!empty($field['required'])) required @endif>
                        @foreach($field['options'] ?? [] as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>

                @else
                    <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" class="form-control"
                        placeholder="{{ $field['placeholder'] ?? '' }}" @if(!empty($field['required'])) required @endif>
                @endif
            </div>
        @endforeach

        <div class="mt-8">
            <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                <div class="spinner"></div>
                <span>{{ $config['submit_label'] ?? 'Submit' }}</span>
            </button>
        </div>
    </form>
</div>