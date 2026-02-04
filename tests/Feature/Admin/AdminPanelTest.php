<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Page;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_non_admin_cannot_access_admin_panel()
    {
        $response = $this->actingAs($this->user)->get('/admin');
        $response->assertForbidden();
    }

    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    public function test_admin_can_create_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/users', $userData);

        $response->assertRedirect(route('admin.users'));
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user_created']);
    }

    public function test_admin_can_update_user_role()
    {
        $targetUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)->put("/admin/users/{$targetUser->id}", [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'role' => 'editor',
        ]);

        $response->assertRedirect(route('admin.users'));
        $this->assertEquals('editor', $targetUser->fresh()->role);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user_updated']);
    }

    public function test_admin_can_manage_permissions()
    {
        $targetUser = User::factory()->create(['role' => 'user']);
        $page = Page::create(['slug' => 'test-page', 'destination_url' => 'https://example.com/webhook']);

        $permissions = [
            'permissions' => [
                $page->id => ['can_view' => '1', 'can_edit' => '1']
            ]
        ];

        $response = $this->actingAs($this->admin)->post("/admin/users/{$targetUser->id}/permissions", $permissions);

        $response->assertRedirect();

        $this->assertTrue($targetUser->accessiblePages()->where('page_id', $page->id)->wherePivot('can_view', true)->exists());
        $this->assertDatabaseHas('audit_logs', ['action' => 'permissions_updated']);
    }
}
