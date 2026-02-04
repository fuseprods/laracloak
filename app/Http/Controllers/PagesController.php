<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\AuditLog;
use App\Services\UpstreamService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Category;
use App\Models\User;
use App\Models\Group;
use App\Models\Permission;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Page::query();

        // Security: Non-admins only see pages they have edit permission for
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

        $pages = $query->withCount('categories')->latest()->paginate(10);
        return view('panel.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $credentials = \App\Models\Credential::all();
        $categories = Category::all();
        return view('panel.pages.create', compact('credentials', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Page::class);

        $validated = $request->validate([
            'slug' => ['required', 'alpha_dash', 'unique:pages'],
            'destination_url' => ['required', 'url'],
            'upstream_method' => ['required', Rule::in(['GET', 'POST'])],
            'type' => ['required', Rule::in(['form', 'dashboard'])],
            'config' => ['nullable', 'json'],
            'response_filters' => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'credential_id' => ['nullable', 'exists:credentials,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        if (!empty($validated['credential_id'])) {
            $credential = \App\Models\Credential::find($validated['credential_id']);
            if (!$credential->isDomainAllowed($validated['destination_url'])) {
                return back()->withInput()->withErrors(['destination_url' => __("The URL domain is not allowed by the selected credential ':name'.", ['name' => $credential->name])]);
            }
        }

        $page = Page::create([
            'slug' => $validated['slug'],
            'destination_url' => $validated['destination_url'],
            'upstream_method' => $validated['upstream_method'],
            'type' => $validated['type'],
            'config' => json_decode($validated['config'] ?? '{}', true),
            'response_filters' => array_filter(array_map('trim', explode(',', $validated['response_filters'] ?? ''))),
            'is_published' => $validated['is_published'] ?? false,
            'credential_id' => $validated['credential_id'] ?? null,
        ]);

        $page->categories()->sync($validated['categories'] ?? []);

        $this->logAction('page_created', 'Page', $page->id, null, $page->toArray());

        return redirect()->route('panel.pages.index')->with('success', __('Page created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $page);

        $credentials = \App\Models\Credential::all();
        $categories = Category::all();
        $users = User::all();
        $groups = Group::all();
        $pagePermissions = $page->permissions;

        return view('panel.pages.edit', compact('page', 'credentials', 'categories', 'users', 'groups', 'pagePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $page);

        $validated = $request->validate([
            'slug' => ['required', 'alpha_dash', Rule::unique('pages')->ignore($page->id)],
            'destination_url' => ['required', 'url'],
            'upstream_method' => ['required', Rule::in(['GET', 'POST'])],
            'type' => ['required', Rule::in(['form', 'dashboard'])],
            'config' => ['nullable', 'json'],
            'response_filters' => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'credential_id' => ['nullable', 'exists:credentials,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'permissions' => ['nullable', 'array'],
        ]);

        if (!empty($validated['credential_id'])) {
            $credential = \App\Models\Credential::find($validated['credential_id']);
            if (!$credential->isDomainAllowed($validated['destination_url'])) {
                return back()->withInput()->withErrors(['destination_url' => __("The URL domain is not allowed by the selected credential ':name'.", ['name' => $credential->name])]);
            }
        }

        $oldValues = $page->toArray();

        $page->fill([
            'slug' => $validated['slug'],
            'destination_url' => $validated['destination_url'],
            'upstream_method' => $validated['upstream_method'],
            'type' => $validated['type'],
            'config' => json_decode($validated['config'] ?? '{}', true),
            'response_filters' => array_filter(array_map('trim', explode(',', $validated['response_filters'] ?? ''))),
            'is_published' => $validated['is_published'] ?? false,
            'credential_id' => $validated['credential_id'] ?? null,
        ]);

        $page->save();

        // Sync Categories
        $page->categories()->sync($validated['categories'] ?? []);

        // Sync Permissions (Polymorphic) where OBJECT is this Page
        $page->permissions()->delete();

        $subjectTypes = [
            'users' => User::class,
            'groups' => Group::class,
        ];

        foreach ($subjectTypes as $key => $modelClass) {
            $viewIds = $request->input("view_{$key}") ?? [];
            $editIds = $request->input("edit_{$key}") ?? [];
            $allIds = array_unique(array_merge($viewIds, $editIds));

            foreach ($allIds as $id) {
                $page->permissions()->create([
                    'subject_type' => $modelClass,
                    'subject_id' => $id,
                    'can_view' => in_array($id, $viewIds) || in_array($id, $editIds),
                    'can_edit' => in_array($id, $editIds),
                ]);
            }
        }

        $this->logAction('page_updated', 'Page', $page->id, $oldValues, $page->toArray());

        return redirect()->route('panel.pages.index')->with('success', __('Page updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete', $page);

        $oldValues = $page->toArray();
        $page->delete();

        $this->logAction('page_deleted', 'Page', $page->id, $oldValues, null);

        return redirect()->route('panel.pages.index')->with('success', __('Page deleted successfully.'));
    }

    /**
     * Test connection to the upstream service and see filtered results.
     */
    public function testUpstream(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Page::class);

        $validated = $request->validate([
            'destination_url' => ['required', 'url'],
            'upstream_method' => ['required', Rule::in(['GET', 'POST'])],
            'credential_id' => ['nullable', 'exists:credentials,id'],
            'response_filters' => ['nullable', 'string'],
        ]);

        $filters = array_filter(array_map('trim', explode(',', $request->response_filters ?? '')));
        $credential = $request->credential_id ? \App\Models\Credential::find($request->credential_id) : null;

        try {
            $upstream = app(UpstreamService::class);
            $response = $upstream->call($validated['destination_url'], $validated['upstream_method'], [], $credential);

            $contentType = $response->header('Content-Type');
            $body = $response->body();

            if (str_contains($contentType, 'application/json')) {
                $json = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $json = $this->applyFilters($json, $filters);
                    return response()->json([
                        'status' => $response->status(),
                        'contentType' => $contentType,
                        'data' => $json,
                        'formatted' => json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    ]);
                }
            }

            return response()->json([
                'status' => $response->status(),
                'contentType' => $contentType,
                'data' => $body,
                'formatted' => $body
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function applyFilters(array $data, array $customFilters): array
    {
        $sensitiveKeys = array_merge(['headers', 'webhookUrl', 'executionMode', 'stack', 'debug', 'request'], $customFilters);

        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveKeys, true)) {
                unset($data[$key]);
                continue;
            }
            if (is_array($value)) {
                $data[$key] = $this->applyFilters($value, $customFilters);
            }
        }
        return $data;
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
