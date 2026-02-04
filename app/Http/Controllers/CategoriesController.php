<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Page;
use App\Models\User;
use App\Models\Group;
use App\Models\Permission;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $categories = $query->withCount('pages')->latest()->paginate(10);
        return view('panel.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('panel.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:categories|max:255',
            'description' => 'nullable',
        ]);

        Category::create($validated);

        return redirect()->route('panel.categories.index')->with('success', __('Category created successfully.'));
    }

    public function edit(Category $category)
    {
        $pages = Page::all();
        $users = User::all();
        $groups = Group::all();
        $categoryPermissions = $category->permissions;

        return view('panel.categories.edit', compact('category', 'pages', 'users', 'groups', 'categoryPermissions'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable',
            'pages' => 'nullable|array',
            'pages.*' => 'exists:pages,id',
            'permissions' => 'nullable|array',
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Sync Pages
        $category->pages()->sync($validated['pages'] ?? []);

        // Sync Permissions (Polymorphic) where OBJECT is this Category
        $category->permissions()->delete();

        $subjectTypes = [
            'users' => User::class,
            'groups' => Group::class,
        ];

        foreach ($subjectTypes as $key => $modelClass) {
            $viewIds = $request->input("view_{$key}") ?? [];
            $editIds = $request->input("edit_{$key}") ?? [];
            $allIds = array_unique(array_merge($viewIds, $editIds));

            foreach ($allIds as $id) {
                $category->permissions()->create([
                    'subject_type' => $modelClass,
                    'subject_id' => $id,
                    'can_view' => in_array($id, $viewIds) || in_array($id, $editIds),
                    'can_edit' => in_array($id, $editIds),
                ]);
            }
        }

        return redirect()->route('panel.categories.index')->with('success', __('Category updated successfully.'));
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('panel.categories.index')->with('success', __('Category deleted successfully.'));
    }
}
