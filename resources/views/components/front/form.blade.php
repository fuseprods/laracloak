@props(['config', 'slug'])

<div class="glass-card container-sm mx-auto">
    <h1 class="text-center mb-2">{{ $config['title'] ?? 'Untitled Form' }}</h1>
    @if(isset($config['description']))
        <p class="subtitle text-center">{{ $config['description'] }}</p>
    @endif

    @php
        $fields = $config['fields'] ?? [];
        $rows = [];
        $currentRow = [];
        $currentLayout = null;

        foreach ($fields as $field) {
            $type = $field['type'] ?? 'text';

            if ($type === 'break') {
                if (!empty($currentRow) || $currentLayout !== null) {
                    $rows[] = ['fields' => $currentRow, 'layout' => $currentLayout];
                }
                $currentRow = [];
                $currentLayout = $field['layout'] ?? null;
                continue;
            }

            // if ($type === 'none') continue; // This line is removed

            // Auto-break if limit reached (Max 6 columns) // This block is removed
            // if (count($currentRow) >= 6) {
            //     $rows[] = ['fields' => $currentRow, 'layout' => $currentLayout];
            //     $currentRow = [];
            //     $currentLayout = null;
            // }

            $currentRow[] = $field;
        }
        if (!empty($currentRow)) {
            $rows[] = ['fields' => $currentRow, 'layout' => $currentLayout];
        }
    @endphp

    <form id="dynamic-form" data-slug="{{ $slug }}">
        <div id="form-error" class="alert alert-error" style="display: none;"></div>
        <div id="form-success" class="alert alert-success" style="display: none;"></div>

        @foreach($rows as $row)
            @php 
                $count = count($row['fields']);
                $layout = $row['layout'] ?? null;
            @endphp
            <div class="dashboard-row" style="{{ $layout ? "--layout: $layout;" : "--cols: $count;" }}">
                @foreach($row['fields'] as $field)
                    @php $type = $field['type'] ?? 'text'; @endphp

                    @if($type === 'none')
                        <div class="form-group-placeholder"></div>
                    @else
                        <div class="form-group">
                            <label class="form-label">
                                {{ $field['label'] ?? $field['name'] ?? '' }}
                                @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                            </label>

                            @if($type === 'textarea')
                                <textarea name="{{ $field['name'] }}" class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}"
                                    @if(!empty($field['required'])) required @endif></textarea>

                            @elseif($type === 'select')
                                <select name="{{ $field['name'] }}" class="form-control" @if(!empty($field['required'])) required @endif>
                                    @foreach($field['options'] ?? [] as $option)
                                        @if(is_array($option))
                                            <option value="{{ $option['value'] ?? '' }}">{{ $option['label'] ?? ($option['value'] ?? '') }}</option>
                                        @else
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endif
                                    @endforeach
                                </select>

                            @elseif($type === 'file')
                                <div class="file-input-wrapper">
                                    <input type="file" name="{{ $field['name'] }}" class="form-control" @if(!empty($field['required'])) required @endif>
                                    <small class="text-muted">{{ __('Max size: 5MB') }}</small>
                                </div>

                            @elseif($type === 'rating')
                                <div class="star-rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="star{{ $i }}-{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $i }}"
                                            {{ !empty($field['required']) && $i == 0 ? 'required' : '' }} />
                                        <label for="star{{ $i }}-{{ $field['name'] }}" title="{{ $i }} stars">★</label>
                                    @endfor
                                </div>

                            @else
                                <input type="{{ $type }}" name="{{ $field['name'] ?? '' }}" class="form-control"
                                    placeholder="{{ $field['placeholder'] ?? '' }}" @if(!empty($field['required'])) required @endif>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach

        <style>
            .star-rating {
                display: flex;
                flex-direction: row-reverse;
                justify-content: flex-end;
                gap: 0.25rem;
                font-size: 1.5rem;
            }

            .star-rating input {
                display: none;
            }

            .star-rating label {
                color: var(--border);
                cursor: pointer;
                transition: color 0.2s;
            }

            .star-rating label:hover,
            .star-rating label:hover~label,
            .star-rating input:checked~label {
                color: var(--warning);
            }

            .file-input-wrapper {
                background: rgba(0, 0, 0, 0.1);
                padding: 1rem;
                border-radius: 8px;
                border: 1px dashed var(--border);
            }
        </style>

        <div class="mt-8">
            <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                <div class="spinner"></div>
                <span>{{ $config['submit_label'] ?? 'Submit' }}</span>
            </button>
        </div>
    </form>
</div>