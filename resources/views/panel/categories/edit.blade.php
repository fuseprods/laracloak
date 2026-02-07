@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Edit Category: :name', ['name' => $category->name]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.categories.index') }}" class="btn btn-secondary">
                {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="content-section">
        <form action="{{ route('panel.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="section-block">
                <h2 class="card-title">{{ __('Basic Information') }}</h2>
                <div class="form-group">
                    <label for="name">{{ __('Category Name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required>
                </div>
                <div class="form-group">
                    <label for="description">{{ __('Description') }}</label>
                    <textarea name="description" id="description"
                        rows="2">{{ old('description', $category->description) }}</textarea>
                </div>
            </div>

            <div class="section-block">
                <h2 class="card-title">{{ __('Pages in this Category') }}</h2>
                <p class="card-subtitle">{{ __('Select which pages belong to this category.') }}</p>
                <div class="form-group">
                    <select id="page-select" name="pages[]" multiple placeholder="{{ __('Search and select pages...') }}"
                        autocomplete="off">
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ $category->pages->contains($page->id) ? 'checked' : '' }}>
                                📄 {{ $page->slug }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-block">
                <h2 class="card-title">{{ __('Access Control (Who can see this Category?)') }}</h2>
                <p class="card-subtitle">
                    {{ __('Anyone with access here will see ALL pages in this category. Type 3+ chars to search.') }}
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
                    <!-- Groups Section -->
                    <div class="form-group">
                        <label>👥 {{ __('User Groups (Can View)') }}</label>
                        <select class="tag-select" name="view_groups[]" multiple
                            placeholder="{{ __('Add groups to view...') }}">
                            @foreach($groups as $group)
                                @php $hasPerm = $categoryPermissions->where('subject_type', get_class($group))->where('subject_id', $group->id)->where('can_view', true)->isNotEmpty(); @endphp
                                <option value="{{ $group->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🛠️ {{ __('User Groups (Can Edit)') }}</label>
                        <select class="tag-select" name="edit_groups[]" multiple
                            placeholder="{{ __('Add groups to edit...') }}">
                            @foreach($groups as $group)
                                @php $hasPerm = $categoryPermissions->where('subject_type', get_class($group))->where('subject_id', $group->id)->where('can_edit', true)->isNotEmpty(); @endphp
                                <option value="{{ $group->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Users Section -->
                    <div class="form-group">
                        <label>👤 {{ __('Individual Users (Can View)') }}</label>
                        <select class="tag-select" name="view_users[]" multiple
                            placeholder="{{ __('Add users to view...') }}">
                            @foreach($users as $user)
                                @php $hasPerm = $categoryPermissions->where('subject_type', get_class($user))->where('subject_id', $user->id)->where('can_view', true)->isNotEmpty(); @endphp
                                <option value="{{ $user->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $user->name }}
                                    ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🛠️ {{ __('Individual Users (Can Edit)') }}</label>
                        <select class="tag-select" name="edit_users[]" multiple
                            placeholder="{{ __('Add users to edit...') }}">
                            @foreach($users as $user)
                                @php $hasPerm = $categoryPermissions->where('subject_type', get_class($user))->where('subject_id', $user->id)->where('can_edit', true)->isNotEmpty(); @endphp
                                <option value="{{ $user->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $user->name }}
                                    ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-actions">
                <button type="submit" class="btn btn-primary">{{ __('Save Category & Access') }}</button>
            </div>
    </div>
    </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('select[multiple]').forEach(el => {
                new TomSelect(el, {
                    plugins: ['remove_button'],
                    create: false,
                    maxOptions: 50,
                    onType: function (str) {
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
@endsection