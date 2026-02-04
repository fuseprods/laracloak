<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Page;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class PanelController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'pages' => Page::count(),
            'forms' => Page::where('type', 'form')->count(),
            'dashboards' => Page::where('type', 'dashboard')->count(),
            'groups' => \App\Models\Group::count(),
            'categories' => \App\Models\Category::count(),
            'recent_logs' => AuditLog::with('user')->latest()->take(10)->get()
        ];

        return view('panel.index', compact('stats'));
    }

    /**
     * List all users.
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(10);

        return view('panel.users.index', compact('users'));
    }

    /**
     * Show create user form.
     */
    public function createUser()
    {
        return view('panel.users.create');
    }

    /**
     * Store a new user.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'editor', 'user'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $this->logAction('user_created', 'User', $user->id, null, $user->toArray());

        return redirect()->route('panel.users')->with('success', __('User created successfully.'));
    }

    /**
     * Show edit user form.
     */
    public function editUser(User $user)
    {
        return view('panel.users.edit', compact('user'));
    }

    /**
     * Update user details.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'editor', 'user'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $oldValues = $user->only(['name', 'email', 'role']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->logAction('user_updated', 'User', $user->id, $oldValues, $user->only(['name', 'email', 'role']));

        return redirect()->route('panel.users')->with('success', __('User updated successfully.'));
    }

    /**
     * Delete user.
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('You cannot delete yourself.'));
        }

        $oldValues = $user->toArray();
        $user->delete();

        $this->logAction('user_deleted', 'User', $user->id, $oldValues, null);

        return redirect()->route('panel.users')->with('success', __('User deleted successfully.'));
    }

    /**
     * Show permissions matrix for a user.
     */
    public function permissions(User $user)
    {
        $pages = Page::all();
        $categories = \App\Models\Category::all();
        $groups = \App\Models\Group::all();
        $userPermissions = $user->permissions;
        $userGroups = $user->groups;

        return view('panel.users.permissions', compact('user', 'pages', 'categories', 'groups', 'userPermissions', 'userGroups'));
    }

    /**
     * Update permissions and group memberships for a user.
     */
    public function updatePermissions(Request $request, User $user)
    {
        // Update Group Memberships
        $user->groups()->sync($request->groups ?? []);

        // Update Direct Permissions (Polymorphic) where SUBJECT is this User
        $user->permissions()->delete();

        $resourceTypes = [
            'pages' => Page::class,
            'categories' => \App\Models\Category::class,
        ];

        foreach ($resourceTypes as $key => $modelClass) {
            $viewIds = $request->input("view_{$key}") ?? [];
            $editIds = $request->input("edit_{$key}") ?? [];

            // Merge all IDs to process them once
            $allIds = array_unique(array_merge($viewIds, $editIds));

            foreach ($allIds as $id) {
                $user->permissions()->create([
                    'object_type' => $modelClass,
                    'object_id' => $id,
                    'can_view' => in_array($id, $viewIds) || in_array($id, $editIds), // If they can edit, they can view
                    'can_edit' => in_array($id, $editIds),
                ]);
            }
        }

        $this->logAction('permissions_updated', 'User', $user->id, null, ['permissions_updated' => true]);

        return back()->with('success', __('User permissions and group memberships updated successfully.'));
    }

    /**
     * Helper to log actions.
     */
    protected function logAction($action, $targetType, $targetId, $oldValues, $newValues)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
