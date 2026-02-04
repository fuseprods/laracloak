<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Page;
use App\Models\User;
use App\Services\UpstreamService;
use Illuminate\Support\Facades\Http;

class ProxyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock Upstream Service config
        // Base URL logic removed, but API key still supported as fallback
        config(['services.upstream.api_key' => 'secret-global-key']);
    }

    public function test_unauthenticated_user_cannot_access_front()
    {
        $page = Page::create([
            'slug' => 'dashboard',
            'destination_url' => 'https://api.example.com/webhook/1',
            'type' => 'dashboard',
            'upstream_method' => 'GET',
            'is_published' => true
        ]);

        $response = $this->get('/front/dashboard');
        $response->assertRedirect('/login');

        $response = $this->post('/front/dashboard/action');
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_access_page()
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = Page::create([
            'slug' => 'restricted',
            'destination_url' => 'https://api.example.com/webhook/1',
            'type' => 'dashboard',
            'upstream_method' => 'GET',
            'is_published' => true
        ]);

        // User exists but has no permission on this page
        $response = $this->actingAs($user)->get('/front/restricted');
        $response->assertForbidden();

        $response = $this->actingAs($user)->post('/front/restricted/action');
        $response->assertForbidden();
    }

    public function test_authorized_user_can_access_page()
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = Page::create([
            'slug' => 'allowed',
            'destination_url' => 'https://api.example.com/webhook/1',
            'type' => 'dashboard',
            'upstream_method' => 'GET',
            'is_published' => true
        ]);

        // Grant permission
        $user->accessiblePages()->attach($page->id, ['can_view' => true]);

        $response = $this->actingAs($user)->get('/front/allowed');
        $response->assertStatus(200);
        $response->assertViewIs('front.page');
    }

    public function test_admin_can_bypass_permissions()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::create([
            'slug' => 'any',
            'destination_url' => 'https://api.example.com/webhook/1',
            'type' => 'dashboard',
            'upstream_method' => 'GET',
            'is_published' => true
        ]);

        $response = $this->actingAs($admin)->get('/front/any');
        $response->assertStatus(200);
    }

    public function test_proxy_hides_upstream_details_on_error()
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = Page::create([
            'slug' => 'error-page',
            'destination_url' => 'https://hidden-upstream.com/webhook/fail',
            'type' => 'form',
            'upstream_method' => 'POST',
            'is_published' => true
        ]);
        $user->accessiblePages()->attach($page->id, ['can_view' => true]);

        // Mock Upstream Service Failure
        Http::fake([
            'hidden-upstream.com/*' => Http::response(['internal' => 'stack_trace'], 500)
        ]);

        $response = $this->actingAs($user)->postJson('/front/error-page/action');

        // Assert Generic Error
        $response->assertStatus(503);
        $response->assertJson([
            'error' => 'Service Unavailable',
            'code' => '503'
        ]);

        // Assert NO Leakage
        $content = $response->getContent();
        $this->assertStringNotContainsString('hidden-upstream.com', $content);
        $this->assertStringNotContainsString('stack_trace', $content);
        $this->assertStringNotContainsString('webhook/fail', $content);
    }

    public function test_proxy_sends_correct_data_upstream()
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = Page::create([
            'slug' => 'data-page',
            'destination_url' => 'https://hidden-upstream.com/webhook/success',
            'type' => 'form',
            'upstream_method' => 'POST',
            'is_published' => true
        ]);
        $user->accessiblePages()->attach($page->id, ['can_view' => true]);

        Http::fake([
            'hidden-upstream.com/webhook/success' => Http::response(['success' => true], 200)
        ]);

        $response = $this->actingAs($user)->postJson('/front/data-page/action', ['foo' => 'bar']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify request sent to upstream
        Http::assertSent(function ($request) {
            return $request->url() == 'https://hidden-upstream.com/webhook/success' &&
                $request['foo'] == 'bar' &&
                $request->hasHeader('X-UPSTREAM-KEY'); // Fallback global key check
        });
    }

    public function test_upstream_url_scheme_validation()
    {
        $service = new UpstreamService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid destination configuration');

        // Invalid scheme (file://) or path traversal attempt if passed as URL
        $service->call('file:///etc/passwd');
    }

    public function test_upstream_malformed_url_validation()
    {
        $service = new UpstreamService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid destination configuration');

        $service->call('not-a-url');
    }
}
