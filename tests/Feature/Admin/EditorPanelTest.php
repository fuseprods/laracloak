<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorPanelTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $editor;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_editor_cannot_access_user_management()
    {
        $response = $this->actingAs($this->editor)->get(route('admin.users'));
        $response->assertForbidden(); // Should be 403 (IsAdmin middleware)
    }

    public function test_editor_can_access_pages_management()
    {
        $response = $this->actingAs($this->editor)->get(route('admin.pages.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.pages.index');
    }

    public function test_user_cannot_access_pages_management()
    {
        $response = $this->actingAs($this->user)->get(route('admin.pages.index'));
        $response->assertForbidden(); // Should be 403 (IsStaff middleware)
    }

    public function test_editor_can_create_page()
    {
        $pageData = [
            'slug' => 'new-proxy-page',
            'type' => 'form',
            'destination_url' => 'https://api.example.com/webhook/test',
            'upstream_method' => 'POST',
            'is_published' => '1',
            'config' => '{"foo":"bar"}'
        ];

        $response = $this->actingAs($this->editor)->post(route('admin.pages.store'), $pageData);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', ['slug' => 'new-proxy-page']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'page_created', 'user_id' => $this->editor->id]);
    }

    public function test_editor_can_update_page()
    {
        $page = Page::create([
            'slug' => 'old-slug',
            'type' => 'form',
            'destination_url' => 'https://api.example.com/old/path',
            'is_published' => true
        ]);

        $response = $this->actingAs($this->editor)->put(route('admin.pages.update', $page), [
            'slug' => 'new-slug',
            'type' => 'dashboard',
            'destination_url' => 'https://api.example.com/new/path',
            'upstream_method' => 'GET',
            'is_published' => '0',
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', ['slug' => 'new-slug', 'type' => 'dashboard']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'page_updated']);
    }

    public function test_editor_can_delete_page()
    {
        $page = Page::create([
            'slug' => 'to-delete',
            'type' => 'form',
            'destination_url' => 'https://api.example.com/delete/me'
        ]);

        $response = $this->actingAs($this->editor)->delete(route('admin.pages.destroy', $page));

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'page_deleted']);
    }
}
