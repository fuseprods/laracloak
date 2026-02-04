<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Category;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Gate;

class DashboardsController extends Controller
{
    public function index(Request $request)
    {
        $query = Page::where('type', 'dashboard');

        if (auth()->user()->role !== 'admin') {
            $query->whereHas('permissions', function ($q) {
                $q->where('subject_type', User::class)
                    ->where('subject_id', auth()->id())
                    ->where('can_edit', true);
            })->orWhereHas('permissions', function ($q) {
                $q->where('subject_type', Group::class)
                    ->whereIn('subject_id', auth()->user()->groups->pluck('id'))
                    ->where('can_edit', true);
            });
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('slug', 'like', '%' . $request->search . '%')
                    ->orWhere('destination_url', 'like', '%' . $request->search . '%');
            });
        }

        $dashboards = $query->withCount('categories')->latest()->paginate(10);
        return view('panel.dashboards.index', compact('dashboards'));
    }

    public function create()
    {
        $credentials = \App\Models\Credential::all();
        $categories = Category::all();
        return view('panel.dashboards.create', compact('credentials', 'categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Page::class);

        $validated = $request->validate([
            'slug' => ['required', 'alpha_dash', 'unique:pages'],
            'destination_url' => ['required', 'url'],
            'upstream_method' => ['required', Rule::in(['GET', 'POST'])],
            'config' => ['nullable', 'json'],
            'response_filters' => ['nullable', 'string'],
            'refresh_rate' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
            'credential_id' => ['nullable', 'exists:credentials,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        $page = Page::create([
            'slug' => $validated['slug'],
            'destination_url' => $validated['destination_url'],
            'upstream_method' => $validated['upstream_method'],
            'type' => 'dashboard',
            'config' => json_decode($validated['config'] ?? '{}', true),
            'response_filters' => array_filter(array_map('trim', explode(',', $validated['response_filters'] ?? ''))),
            'refresh_rate' => $validated['refresh_rate'] ?? 60,
            'is_published' => $validated['is_published'] ?? false,
            'credential_id' => $validated['credential_id'] ?? null,
        ]);

        $page->categories()->sync($validated['categories'] ?? []);

        $this->logAction('dashboard_created', 'Page', $page->id, null, $page->toArray());

        return redirect()->route('panel.dashboards.index')->with('success', __('Dashboard created successfully.'));
    }

    public function edit(Page $dashboard)
    {
        if ($dashboard->type !== 'dashboard')
            abort(404);
        Gate::authorize('update', $dashboard);

        $credentials = \App\Models\Credential::all();
        $categories = Category::all();
        $users = User::all();
        $groups = Group::all();
        $pagePermissions = $dashboard->permissions;

        return view('panel.dashboards.edit', compact('dashboard', 'credentials', 'categories', 'users', 'groups', 'pagePermissions'));
    }

    public function update(Request $request, Page $dashboard)
    {
        if ($dashboard->type !== 'dashboard')
            abort(404);
        Gate::authorize('update', $dashboard);

        $validated = $request->validate([
            'slug' => ['required', 'alpha_dash', Rule::unique('pages')->ignore($dashboard->id)],
            'destination_url' => ['required', 'url'],
            'upstream_method' => ['required', Rule::in(['GET', 'POST'])],
            'config' => ['nullable', 'json'],
            'response_filters' => ['nullable', 'string'],
            'refresh_rate' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
            'credential_id' => ['nullable', 'exists:credentials,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        $oldValues = $dashboard->toArray();

        $dashboard->fill([
            'slug' => $validated['slug'],
            'destination_url' => $validated['destination_url'],
            'upstream_method' => $validated['upstream_method'],
            'config' => json_decode($validated['config'] ?? '{}', true),
            'response_filters' => array_filter(array_map('trim', explode(',', $validated['response_filters'] ?? ''))),
            'refresh_rate' => $validated['refresh_rate'] ?? 60,
            'is_published' => $validated['is_published'] ?? false,
            'credential_id' => $validated['credential_id'] ?? null,
        ]);

        $dashboard->save();
        $dashboard->categories()->sync($validated['categories'] ?? []);

        $this->syncPermissions($dashboard, $request);

        $this->logAction('dashboard_updated', 'Page', $dashboard->id, $oldValues, $dashboard->toArray());

        return redirect()->route('panel.dashboards.index')->with('success', __('Dashboard updated successfully.'));
    }

    public function destroy(Page $dashboard)
    {
        Gate::authorize('delete', $dashboard);
        $dashboard->delete();
        return redirect()->route('panel.dashboards.index')->with('success', __('Dashboard deleted successfully.'));
    }

    private function syncPermissions(Page $page, Request $request)
    {
        $page->permissions()->delete();
        $subjectTypes = ['users' => User::class, 'groups' => Group::class];
        foreach ($subjectTypes as $key => $modelClass) {
            $viewIds = $request->input("view_{$key}") ?? [];
            $editIds = $request->input("edit_{$key}") ?? [];
            $allIds = array_unique(array_merge($viewIds, $editIds));
            foreach ($allIds as $id) {
                $page->permissions()->create([
                    'subject_type' => $modelClass,
                    'subject_id' => $id,
                    'can_view' => true,
                    'can_edit' => in_array($id, $editIds),
                ]);
            }
        }
    }

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
