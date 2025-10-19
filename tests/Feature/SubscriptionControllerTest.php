<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
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
        $this->plan = Plan::factory()->create();
    }

    /** @test */
    public function admin_can_view_subscriptions_index()
    {
        Subscription::factory()->count(3)->create();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.subscriptions.index');
    }

    /** @test */
    public function admin_can_view_subscription_statistics()
    {
        Subscription::factory()->count(5)->create();

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
            ->assertRedirect();

        $subscription->refresh();
        $this->assertEquals('canceled', $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
    }

    /** @test */
    public function admin_can_upgrade_subscription()
    {
        $oldPlan = Plan::factory()->create(['name' => 'Basic Plan']);
        $newPlan = Plan::factory()->create(['name' => 'Premium Plan']);
        
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $oldPlan->id,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.upgrade', $subscription->id), [
                'plan_id' => $newPlan->id
            ])
            ->assertRedirect();

        $subscription->refresh();
        $this->assertEquals($newPlan->id, $subscription->plan_id);
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
            ->assertRedirect();

        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->ends_at);
    }

    /** @test */
    public function admin_can_extend_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'ends_at' => now()->addDays(30),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.extend', $subscription->id), [
                'days' => 30
            ])
            ->assertRedirect();

        $subscription->refresh();
        $this->assertTrue($subscription->ends_at->gt(now()->addDays(30)));
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
    public function admin_can_filter_subscriptions_by_plan()
    {
        $plan1 = Plan::factory()->create();
        $plan2 = Plan::factory()->create();
        
        Subscription::factory()->create(['plan_id' => $plan1->id]);
        Subscription::factory()->create(['plan_id' => $plan2->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['plan_id' => $plan1->id]));

        $response->assertStatus(200);
        $response->assertViewHas('subscriptions');
    }

    /** @test */
    public function admin_can_filter_subscriptions_by_date_range()
    {
        Subscription::factory()->create(['created_at' => now()->subDays(5)]);
        Subscription::factory()->create(['created_at' => now()->subDays(10)]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', [
                'date_from' => now()->subDays(7)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d')
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('subscriptions');
    }

    /** @test */
    public function admin_can_search_subscriptions_by_user()
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        
        Subscription::factory()->create(['user_id' => $user1->id]);
        Subscription::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertViewHas('subscriptions');
    }

    /** @test */
    public function user_can_view_their_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard.subscription.index'))
            ->assertStatus(200)
            ->assertViewIs('dashboard.subscription.index')
            ->assertViewHas('subscription');
    }

    /** @test */
    public function user_can_cancel_their_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->post(route('dashboard.subscription.cancel', $subscription->id), [
                'reason' => 'No longer needed'
            ])
            ->assertRedirect();

        $subscription->refresh();
        $this->assertEquals('canceled', $subscription->status);
    }

    /** @test */
    public function user_can_upgrade_their_subscription()
    {
        $oldPlan = Plan::factory()->create(['name' => 'Basic Plan']);
        $newPlan = Plan::factory()->create(['name' => 'Premium Plan']);
        
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $oldPlan->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('dashboard.subscription.upgrade', $subscription->id), [
                'plan_id' => $newPlan->id
            ])
            ->assertRedirect();

        $subscription->refresh();
        $this->assertEquals($newPlan->id, $subscription->plan_id);
    }

    /** @test */
    public function user_can_view_available_plans()
    {
        Plan::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->get(route('dashboard.subscription.plans'))
            ->assertStatus(200)
            ->assertViewIs('dashboard.subscription.plans')
            ->assertViewHas('plans');
    }

    /** @test */
    public function user_can_subscribe_to_plan()
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->user)
            ->post(route('dashboard.subscription.subscribe', $plan->id))
            ->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
        ]);
    }
}

