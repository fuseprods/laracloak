@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Edit User Group: :name', ['name' => $group->name]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.groups.index') }}" class="btn btn-secondary">
                {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="content-section">
        <form action="{{ route('panel.groups.update', $group) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="section-block">
                <h2 class="card-title">{{ __('Basic Information') }}</h2>
                <div class="form-group">
                    <label for="name">{{ __('Group Name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" required>
                </div>
                <div class="form-group">
                    <label for="description">{{ __('Description') }}</label>
                    <textarea name="description" id="description"
                        rows="2">{{ old('description', $group->description) }}</textarea>
                </div>
            </div>

            <div class="section-block">
                <h2 class="card-title">{{ __('Users in this Group') }}</h2>
                <p class="card-subtitle">{{ __('Select users that belong to this department/group.') }}</p>
                <div class="form-group">
                    <select id="user-select" name="users[]" multiple placeholder="{{ __('Search and select users...') }}"
                        autocomplete="off">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $group->users->contains($user->id) ? 'checked' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-block">
                <h2 class="card-title">{{ __('Group Permissions (Bulk Access)') }}</h2>
                <p class="card-subtitle">
                    {{ __('All users in this group will inherit these permissions. Type 3+ chars to search.') }}
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
                    <!-- Pages Section -->
                    <div class="form-group">
                        <label>📄 {{ __('Pages (Can View)') }}</label>
                        <select class="tag-select" name="view_pages[]" multiple
                            placeholder="{{ __('Add pages to view...') }}">
                            @foreach($pages as $page)
                                @php $hasPerm = $groupPermissions->where('object_type', get_class($page))->where('object_id', $page->id)->where('can_view', true)->isNotEmpty(); @endphp
                                <option value="{{ $page->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $page->slug }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🛠️ {{ __('Pages (Can Edit)') }}</label>
                        <select class="tag-select" name="edit_pages[]" multiple
                            placeholder="{{ __('Add pages to edit...') }}">
                            @foreach($pages as $page)
                                @php $hasPerm = $groupPermissions->where('object_type', get_class($page))->where('object_id', $page->id)->where('can_edit', true)->isNotEmpty(); @endphp
                                <option value="{{ $page->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $page->slug }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categories Section -->
                    <div class="form-group">
                        <label>📂 {{ __('Categories (Can View)') }}</label>
                        <select class="tag-select" name="view_categories[]" multiple
                            placeholder="{{ __('Add categories to view...') }}">
                            @foreach($categories as $category)
                                @php $hasPerm = $groupPermissions->where('object_type', get_class($category))->where('object_id', $category->id)->where('can_view', true)->isNotEmpty(); @endphp
                                <option value="{{ $category->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🛠️ {{ __('Categories (Can Edit)') }}</label>
                        <select class="tag-select" name="edit_categories[]" multiple
                            placeholder="{{ __('Add categories to edit...') }}">
                            @foreach($categories as $category)
                                @php $hasPerm = $groupPermissions->where('object_type', get_class($category))->where('object_id', $category->id)->where('can_edit', true)->isNotEmpty(); @endphp
                                <option value="{{ $category->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-actions">
                <button type="submit" class="btn btn-primary">{{ __('Save Group & Permissions') }}</button>
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