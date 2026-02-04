<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use App\Models\Page;
use App\Models\Category;
use App\Models\Permission;

class GroupsController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $groups = $query->withCount('users')->latest()->paginate(10);
        return view('panel.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('panel.groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:groups|max:255',
            'description' => 'nullable',
        ]);

        Group::create($validated);

        return redirect()->route('panel.groups.index')->with('success', __('Group created successfully.'));
    }

    public function edit(Group $group)
    {
        $users = User::all();
        $pages = Page::all();
        $categories = Category::all();
        $groupPermissions = $group->permissions;

        return view('panel.groups.edit', compact('group', 'users', 'pages', 'categories', 'groupPermissions'));
    }

    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:groups,name,' . $group->id,
            'description' => 'nullable',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'permissions' => 'nullable|array',
        ]);

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Sync Users
        $group->users()->sync($validated['users'] ?? []);

        // Sync Permissions (Polymorphic)
        $group->permissions()->delete();

        $resourceTypes = [
            'pages' => Page::class,
            'categories' => Category::class,
        ];

        foreach ($resourceTypes as $key => $modelClass) {
            $viewIds = $request->input("view_{$key}") ?? [];
            $editIds = $request->input("edit_{$key}") ?? [];
            $allIds = array_unique(array_merge($viewIds, $editIds));

            foreach ($allIds as $id) {
                $group->permissions()->create([
                    'object_type' => $modelClass,
                    'object_id' => $id,
                    'can_view' => in_array($id, $viewIds) || in_array($id, $editIds),
                    'can_edit' => in_array($id, $editIds),
                ]);
            }
        }

        return redirect()->route('panel.groups.index')->with('success', __('Group updated successfully.'));
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->route('panel.groups.index')->with('success', __('Group deleted successfully.'));
    }
}
