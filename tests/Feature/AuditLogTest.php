<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\AdminAuditLog;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Admin::factory()->create(['active' => true]);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_can_view_audit_logs()
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit-logs.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.audit-logs.index');
    }

    /** @test */
    public function admin_can_view_audit_log_details()
    {
        $auditLog = AdminAuditLog::factory()->create([
            'admin_id' => $this->admin->id,
            'action' => 'created',
            'description' => 'Created new user',
            'model_type' => User::class,
            'model_id' => $this->user->id,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit-logs.show', $auditLog->id))
            ->assertStatus(200)
            ->assertViewIs('admin.audit-logs.show')
            ->assertViewHas('log');
    }

    /** @test */
    public function admin_can_export_audit_logs()
    {
        AdminAuditLog::factory()->count(5)->create();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit-logs.export'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function admin_can_filter_audit_logs_by_action()
    {
        AdminAuditLog::factory()->create(['action' => 'created']);
        AdminAuditLog::factory()->create(['action' => 'updated']);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit-logs.index', ['action' => 'created']));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function admin_can_filter_audit_logs_by_date_range()
    {
        AdminAuditLog::factory()->create(['created_at' => now()->subDays(5)]);
        AdminAuditLog::factory()->create(['created_at' => now()->subDays(10)]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit-logs.index', [
                'date_from' => now()->subDays(7)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function audit_log_is_created_when_admin_creates_subscription()
    {
        $this->actingAs($this->admin, 'admin');

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $this->admin->id,
            'action' => 'created',
            'model_type' => Subscription::class,
            'model_id' => $subscription->id,
        ]);
    }

    /** @test */
    public function audit_log_is_created_when_admin_updates_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin, 'admin');

        $subscription->update(['status' => 'canceled']);

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $this->admin->id,
            'action' => 'updated',
            'model_type' => Subscription::class,
            'model_id' => $subscription->id,
        ]);
    }

    /** @test */
    public function audit_log_is_created_when_admin_deletes_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->admin, 'admin');

        $subscription->delete();

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $this->admin->id,
            'action' => 'deleted',
            'model_type' => Subscription::class,
            'model_id' => $subscription->id,
        ]);
    }

    /** @test */
    public function audit_log_includes_request_information()
    {
        $this->actingAs($this->admin, 'admin');

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $auditLog = AdminAuditLog::where('model_id', $subscription->id)->first();

        $this->assertNotNull($auditLog->ip_address);
        $this->assertNotNull($auditLog->user_agent);
        $this->assertNotNull($auditLog->url);
        $this->assertNotNull($auditLog->method);
    }
}