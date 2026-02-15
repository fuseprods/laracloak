<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CredentialsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $credentials = Credential::latest()->paginate(10);
        return view('panel.credentials.index', compact('credentials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('panel.credentials.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['basic', 'header', 'jwt'])],
            'auth_key' => ['nullable', 'string'], // Required if basic or header, but logic handled in view/usage? Let's enforce via UI validation mostly, backend loose for now or strict?
            // Strict validation:
            // if basic: auth_key (user), auth_value (pass)
            // if header: auth_key (name), auth_value (value)
            // if jwt: auth_key (null), auth_value (token)
            'auth_value' => ['required', 'string'],
            'allowed_domains' => ['nullable', 'string'], // Input as textarea
            'settings' => ['nullable', 'json'],
        ]);

        $domains = array_filter(array_map('trim', explode("\n", $validated['allowed_domains'] ?? '')));
        $settings = !empty($validated['settings']) ? json_decode($validated['settings'], true) : null;

        $credential = Credential::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'auth_key' => $validated['auth_key'],
            'auth_value' => $validated['auth_value'],
            'allowed_domains' => array_values($domains),
            'settings' => $settings,
        ]);

        $this->logAction('credential_created', 'Credential', $credential->id, null, ['name' => $credential->name]);

        return redirect()->route('panel.credentials.index')->with('success', __('Credential created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Credential $credential)
    {
        return view('panel.credentials.edit', compact('credential'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Credential $credential)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['basic', 'header', 'jwt'])],
            'auth_key' => ['nullable', 'string'],
            'auth_value' => ['nullable', 'string'],
            'allowed_domains' => ['nullable', 'string'],
            'settings' => ['nullable', 'json'],
        ]);

        $domains = array_filter(array_map('trim', explode("\n", $validated['allowed_domains'] ?? '')));
        $settings = !empty($validated['settings']) ? json_decode($validated['settings'], true) : null;

        $oldValues = ['name' => $credential->name];

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'auth_key' => $validated['auth_key'],
            'allowed_domains' => array_values($domains),
            'settings' => $settings,
        ];

        if (!empty($validated['auth_value'])) {
            $data['auth_value'] = $validated['auth_value'];
        }

        $credential->update($data);

        $this->logAction('credential_updated', 'Credential', $credential->id, $oldValues, ['name' => $credential->name]);

        return redirect()->route('panel.credentials.index')->with('success', __('Credential updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Credential $credential)
    {
        $oldValues = ['name' => $credential->name];
        $credential->delete();

        $this->logAction('credential_deleted', 'Credential', $credential->id, $oldValues, null);

        return redirect()->route('panel.credentials.index')->with('success', __('Credential deleted successfully.'));
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
