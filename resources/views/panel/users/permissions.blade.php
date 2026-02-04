@extends('panel.layout')

@section('content')
    <div class="header">
        <h1>{{ __('Permissions: :name', ['name' => $user->name]) }}</h1>
        <div class="user-menu">
            <a href="{{ route('panel.users') }}" class="btn btn-secondary">
                {{ __('Back to Users') }}
            </a>
        </div>
    </div>

    <form action="{{ route('panel.users.permissions.update', $user) }}" method="POST">
        @csrf

        @if($user->role === 'admin')
            <div class="alert alert-success">
                <strong>{{ __('Admin User:') }}</strong>
                {{ __('This user has full access to all pages and settings. Direct permissions are optional but not required.') }}
            </div>
        @endif

        <div class="card">
            <h2 class="card-title">{{ __('Group Memberships / Departments') }}</h2>
            <p class="card-subtitle">{{ __('User will inherit all permissions assigned to these groups.') }}</p>
            <div class="form-group">
                <select id="group-select" name="groups[]" multiple placeholder="{{ __('Search and select groups...') }}"
                    autocomplete="off">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ $userGroups->contains($group->id) ? 'checked' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">{{ __('Direct Permissions (Overriding Groups)') }}</h2>
            <p class="card-subtitle">
                {{ __('Assign specific access that this user has regardless of their groups. Type 3+ chars to search.') }}
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
                <!-- Pages Section -->
                <div class="form-group">
                    <label>📄 {{ __('Pages (Can View)') }}</label>
                    <select class="tag-select" name="view_pages[]" multiple placeholder="{{ __('Add pages to view...') }}">
                        @foreach($pages as $page)
                            @php $hasPerm = $userPermissions->where('object_type', get_class($page))->where('object_id', $page->id)->where('can_view', true)->isNotEmpty(); @endphp
                            <option value="{{ $page->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $page->slug }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>🛠️ {{ __('Pages (Can Edit)') }}</label>
                    <select class="tag-select" name="edit_pages[]" multiple placeholder="{{ __('Add pages to edit...') }}">
                        @foreach($pages as $page)
                            @php $hasPerm = $userPermissions->where('object_type', get_class($page))->where('object_id', $page->id)->where('can_edit', true)->isNotEmpty(); @endphp
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
                            @php $hasPerm = $userPermissions->where('object_type', get_class($category))->where('object_id', $category->id)->where('can_view', true)->isNotEmpty(); @endphp
                            <option value="{{ $category->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>🛠️ {{ __('Categories (Can Edit)') }}</label>
                    <select class="tag-select" name="edit_categories[]" multiple
                        placeholder="{{ __('Add categories to edit...') }}">
                        @foreach($categories as $category)
                            @php $hasPerm = $userPermissions->where('object_type', get_class($category))->where('object_id', $category->id)->where('can_edit', true)->isNotEmpty(); @endphp
                            <option value="{{ $category->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card-actions">
            <button type="submit" class="btn btn-primary">{{ __('Update User Access') }}</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Unify all selects with the same config
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