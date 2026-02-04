@php
    // These variables need to be passed from the controller or computed here
    $users = $users ?? \App\Models\User::all();
    $groups = $groups ?? \App\Models\Group::all();
    $pagePermissions = $object->permissions;
@endphp

<div
    style="margin-bottom: 2rem; background: rgba(0,0,0,0.1); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid var(--border);">
    <h3 style="margin-bottom: 1rem; font-size: 1rem; color: var(--primary);">{{ __('🔒 Access Control') }}</h3>
    <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 1.5rem;">
        {{ __('Specify which users or groups can access this page.') }}
    </p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Groups Section -->
        <div class="form-group">
            <label>👥 {{ __('User Groups (Can View)') }}</label>
            <select class="tag-select" name="view_groups[]" multiple placeholder="{{ __('Add groups...') }}">
                @foreach($groups as $group)
                    @php $hasPerm = $pagePermissions->where('subject_type', get_class($group))->where('subject_id', $group->id)->where('can_view', true)->isNotEmpty(); @endphp
                    <option value="{{ $group->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>🛠️ {{ __('User Groups (Can Edit)') }}</label>
            <select class="tag-select" name="edit_groups[]" multiple placeholder="{{ __('Add groups...') }}">
                @foreach($groups as $group)
                    @php $hasPerm = $pagePermissions->where('subject_type', get_class($group))->where('subject_id', $group->id)->where('can_edit', true)->isNotEmpty(); @endphp
                    <option value="{{ $group->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Users Section -->
        <div class="form-group">
            <label>👤 {{ __('Individual Users (Can View)') }}</label>
            <select class="tag-select" name="view_users[]" multiple placeholder="{{ __('Add users...') }}">
                @foreach($users as $user)
                    @php $hasPerm = $pagePermissions->where('subject_type', get_class($user))->where('subject_id', $user->id)->where('can_view', true)->isNotEmpty(); @endphp
                    <option value="{{ $user->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>🛠️ {{ __('Individual Users (Can Edit)') }}</label>
            <select class="tag-select" name="edit_users[]" multiple placeholder="{{ __('Add users...') }}">
                @foreach($users as $user)
                    @php $hasPerm = $pagePermissions->where('subject_type', get_class($user))->where('subject_id', $user->id)->where('can_edit', true)->isNotEmpty(); @endphp
                    <option value="{{ $user->id }}" {{ $hasPerm ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tag-select').forEach(el => {
            if (!el.tomselect) {
                new TomSelect(el, {
                    plugins: ['remove_button'],
                    maxOptions: 50
                });
            }
        });
    });
</script>