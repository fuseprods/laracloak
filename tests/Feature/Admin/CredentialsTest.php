<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Credential;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);
    }

    public function test_admin_can_access_credentials_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.credentials.index'));
        $response->assertStatus(200);
    }

    public function test_editor_cannot_access_credentials_index()
    {
        $response = $this->actingAs($this->editor)->get(route('admin.credentials.index'));
        $response->assertForbidden(); // Should be 403
    }

    public function test_admin_can_create_credential()
    {
        $data = [
            'name' => 'Test API Key',
            'type' => 'header',
            'auth_key' => 'X-API-KEY',
            'auth_value' => 'secret-123',
            'allowed_domains' => "*.example.com\napi.test.com"
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.credentials.store'), $data);

        $response->assertRedirect(route('admin.credentials.index'));
        $this->assertDatabaseHas('credentials', ['name' => 'Test API Key', 'type' => 'header']);

        $credential = Credential::where('name', 'Test API Key')->first();
        $this->assertTrue($credential->isDomainAllowed('https://api.example.com/webhook'));
        $this->assertTrue($credential->isDomainAllowed('https://api.test.com/v1'));
        $this->assertFalse($credential->isDomainAllowed('https://evil.com/hack'));
    }

    public function test_page_creation_requires_whitelisted_domain_if_credential_used()
    {
        $credential = Credential::create([
            'name' => 'Restricted Cred',
            'type' => 'basic',
            'auth_key' => 'user',
            'auth_value' => 'pass',
            'allowed_domains' => ['api.allowed.com']
        ]);

        // 1. Try creating page with INVALID domain for this credential
        $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
            'slug' => 'fail-page',
            'type' => 'form',
            'destination_url' => 'https://api.forbidden.com/endpoint',
            'upstream_method' => 'POST',
            'credential_id' => $credential->id
        ]);

        $response->assertSessionHasErrors('destination_url');
        $this->assertDatabaseMissing('pages', ['slug' => 'fail-page']);

        // 2. Try creating page with VALID domain
        $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
            'slug' => 'success-page',
            'type' => 'form',
            'destination_url' => 'https://api.allowed.com/endpoint',
            'upstream_method' => 'POST',
            'credential_id' => $credential->id
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', ['slug' => 'success-page']);
    }
}
