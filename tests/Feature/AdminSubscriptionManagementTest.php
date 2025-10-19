<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSubscriptionManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Admin::factory()->create(['active' => true]);
        $this->user = User::factory()->create();
        $this->plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_subscriptions_index()
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.subscriptions.index');
    }

    /** @test */
    public function admin_can_view_subscription_statistics()
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.statistics'))
            ->assertStatus(200)
            ->assertViewIs('admin.subscriptions.statistics');
    }

    /** @test */
    public function admin_can_view_subscription_details()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.show', $subscription->id))
            ->assertStatus(200)
            ->assertViewIs('admin.subscriptions.show')
            ->assertViewHas('subscription');
    }

    /** @test */
    public function admin_can_cancel_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.cancel', $subscription->id), [
                'reason' => 'User requested cancellation'
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'canceled',
        ]);
    }

    /** @test */
    public function admin_can_manually_activate_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.manual-activate', $subscription->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_extend_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'ends_at' => now()->addDays(30),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.extend', $subscription->id), [
                'days' => 7
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $subscription->refresh();
        $this->assertEquals(37, now()->diffInDays($subscription->ends_at));
    }

    /** @test */
    public function admin_can_export_subscriptions()
    {
        Subscription::factory()->count(5)->create();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.export'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function admin_can_filter_subscriptions_by_status()
    {
        Subscription::factory()->create(['status' => 'active']);
        Subscription::factory()->create(['status' => 'canceled']);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertViewHas('subscriptions');
    }

    /** @test */
    public function admin_can_search_subscriptions_by_user()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        Subscription::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertViewHas('subscriptions');
    }
}