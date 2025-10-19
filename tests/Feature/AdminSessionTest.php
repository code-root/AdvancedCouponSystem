<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSessionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Admin::factory()->create(['active' => true]);
    }

    /** @test */
    public function admin_can_view_active_sessions()
    {
        AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.sessions.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.sessions.index');
    }

    /** @test */
    public function admin_can_view_their_own_sessions()
    {
        AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.sessions.my-sessions'))
            ->assertStatus(200)
            ->assertViewIs('admin.sessions.my-sessions');
    }

    /** @test */
    public function admin_can_view_session_statistics()
    {
        AdminSession::factory()->count(3)->create([
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.sessions.statistics'))
            ->assertStatus(200)
            ->assertViewIs('admin.sessions.statistics');
    }

    /** @test */
    public function admin_can_view_session_details()
    {
        $session = AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.sessions.show', $session->id))
            ->assertStatus(200)
            ->assertViewIs('admin.sessions.show')
            ->assertViewHas('session');
    }

    /** @test */
    public function admin_can_terminate_session()
    {
        $session = AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.sessions.terminate', $session->id))
            ->assertRedirect();

        $session->refresh();
        $this->assertFalse($session->is_active);
        $this->assertNotNull($session->logout_at);
    }

    /** @test */
    public function admin_can_terminate_all_sessions_for_admin()
    {
        AdminSession::factory()->count(3)->create([
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.sessions.terminate-all', $this->admin->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('admin_sessions', [
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_terminate_their_own_session()
    {
        $session = AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.sessions.terminate-my', $session->id))
            ->assertRedirect();

        $session->refresh();
        $this->assertFalse($session->is_active);
    }

    /** @test */
    public function admin_can_filter_sessions_by_admin()
    {
        $otherAdmin = Admin::factory()->create();
        
        AdminSession::factory()->create(['admin_id' => $this->admin->id]);
        AdminSession::factory()->create(['admin_id' => $otherAdmin->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.sessions.index', ['admin_id' => $this->admin->id]));

        $response->assertStatus(200);
        $response->assertViewHas('sessions');
    }

    /** @test */
    public function admin_can_search_sessions()
    {
        AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
            'ip_address' => '192.168.1.1',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.sessions.index', ['search' => '192.168.1.1']));

        $response->assertStatus(200);
        $response->assertViewHas('sessions');
    }

    /** @test */
    public function session_tracking_middleware_creates_session_on_login()
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'));

        $this->assertDatabaseHas('admin_sessions', [
            'admin_id' => $this->admin->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function session_tracking_middleware_updates_last_activity()
    {
        $session = AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
            'is_active' => true,
            'last_activity' => now()->subMinutes(10),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'));

        $session->refresh();
        $this->assertTrue($session->last_activity->gt(now()->subMinutes(10)));
    }
}

