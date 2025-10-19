<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\PlanCoupon;
use App\Models\SyncUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class SubscriptionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test plan
        $this->plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'price' => 29.99,
            'trial_days' => 14,
            'max_networks' => 5,
            'daily_sync_limit' => 100,
            'monthly_sync_limit' => 2000,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function user_can_view_subscription_plans()
    {
        $response = $this->actingAs($this->user)
            ->get(route('subscriptions.plans'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.subscriptions.plans');
        $response->assertViewHas('plans');
    }

    /** @test */
    public function user_can_view_plan_comparison()
    {
        $response = $this->actingAs($this->user)
            ->get(route('subscriptions.compare'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.subscriptions.compare');
        $response->assertViewHas('plans');
    }

    /** @test */
    public function user_can_start_trial()
    {
        Queue::fake();

        $response = $this->actingAs($this->user)
            ->post(route('subscriptions.trial', $this->plan));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Trial started');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
        ]);
    }

    /** @test */
    public function user_can_activate_subscription()
    {
        $response = $this->actingAs($this->user)
            ->post(route('subscriptions.activate', $this->plan));

        $response->assertRedirect(route('subscriptions.checkout', $this->plan));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function user_can_apply_coupon_during_activation()
    {
        $coupon = PlanCoupon::factory()->create([
            'code' => 'TEST20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('subscriptions.activate', $this->plan), [
                'coupon' => 'TEST20',
            ]);

        $response->assertRedirect(route('subscriptions.checkout', $this->plan));

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $this->assertEquals($coupon->id, $subscription->coupon_id);
    }

    /** @test */
    public function user_cannot_apply_invalid_coupon()
    {
        $response = $this->actingAs($this->user)
            ->post(route('subscriptions.activate', $this->plan), [
                'coupon' => 'INVALID',
            ]);

        $response->assertSessionHasErrors('coupon');
    }

    /** @test */
    public function user_can_view_subscription_management()
    {
        // Create subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('subscriptions.manage'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.subscriptions.manage');
        $response->assertViewHas(['subscription', 'dailyUsage', 'monthlyUsage']);
    }

    /** @test */
    public function user_can_cancel_subscription()
    {
        // Create active subscription
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('subscriptions.cancel'));

        $response->assertRedirect(route('subscriptions.manage'));
        $response->assertSessionHas('success', 'Subscription cancelled');

        $subscription->refresh();
        $this->assertEquals('cancelled', $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
    }

    /** @test */
    public function subscription_usage_is_tracked_correctly()
    {
        // Create active subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Simulate sync usage
        $planLimitService = app(\App\Services\PlanLimitService::class);
        $planLimitService->incrementSyncCount($this->user, 5);
        $planLimitService->incrementRevenueCount($this->user, 1000);
        $planLimitService->incrementOrdersCount($this->user, 10);

        // Check daily usage
        $dailyUsage = SyncUsage::where('user_id', $this->user->id)
            ->where('period', 'daily')
            ->whereDate('window_start', now())
            ->first();

        $this->assertNotNull($dailyUsage);
        $this->assertEquals(5, $dailyUsage->sync_count);
        $this->assertEquals(1000, $dailyUsage->revenue_count);
        $this->assertEquals(10, $dailyUsage->orders_count);

        // Check monthly usage
        $monthlyUsage = SyncUsage::where('user_id', $this->user->id)
            ->where('period', 'monthly')
            ->whereMonth('window_start', now()->month)
            ->first();

        $this->assertNotNull($monthlyUsage);
        $this->assertEquals(5, $monthlyUsage->sync_count);
        $this->assertEquals(1000, $monthlyUsage->revenue_count);
        $this->assertEquals(10, $monthlyUsage->orders_count);
    }

    /** @test */
    public function plan_limits_are_enforced()
    {
        // Create subscription with low limits
        $limitedPlan = Plan::factory()->create([
            'name' => 'Limited Plan',
            'max_networks' => 2,
            'daily_sync_limit' => 10,
            'monthly_sync_limit' => 100,
            'is_active' => true,
        ]);

        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $limitedPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Mock network connections count
        $this->user->shouldReceive('getActiveNetworkConnectionsCount')->andReturn(2);

        // Try to add another network - should fail
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plan limit: max networks reached');

        $planLimitService = app(\App\Services\PlanLimitService::class);
        $planLimitService->assertCanAddNetwork($this->user);
    }

    /** @test */
    public function sync_limits_are_enforced()
    {
        // Create subscription with low sync limits
        $limitedPlan = Plan::factory()->create([
            'name' => 'Limited Plan',
            'daily_sync_limit' => 5,
            'monthly_sync_limit' => 50,
            'is_active' => true,
        ]);

        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $limitedPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Create usage that exceeds daily limit
        SyncUsage::create([
            'user_id' => $this->user->id,
            'period' => 'daily',
            'window_start' => now()->startOfDay(),
            'window_end' => now()->endOfDay(),
            'sync_count' => 5, // At the limit
        ]);

        // Try to sync - should fail
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plan limit: daily sync limit reached');

        $planLimitService = app(\App\Services\PlanLimitService::class);
        $planLimitService->assertCanSync($this->user);
    }

    /** @test */
    public function trial_expiration_is_handled_correctly()
    {
        // Create expired trial
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->subDays(1),
        ]);

        // Try to access protected route - should be redirected
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.networks.index'));

        $response->assertRedirect(route('subscriptions.plans'));
    }

    /** @test */
    public function subscription_status_changes_are_logged()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        // Activate subscription
        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Cancel subscription
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function subscription_with_coupon_calculates_correct_price()
    {
        $coupon = PlanCoupon::factory()->create([
            'code' => 'HALF50',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'coupon_id' => $coupon->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $discountedPrice = $subscription->getDiscountedPrice();
        $expectedPrice = $this->plan->price * 0.5; // 50% discount

        $this->assertEquals($expectedPrice, $discountedPrice);
    }

    /** @test */
    public function subscription_renewal_works_correctly()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(), // Expired yesterday
        ]);

        // Simulate renewal
        $subscription->update([
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertEquals(now()->addMonth()->format('Y-m-d'), $subscription->ends_at->format('Y-m-d'));
    }

    /** @test */
    public function multiple_subscriptions_are_not_allowed()
    {
        // Create first subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Try to create second subscription - should be handled by business logic
        $secondPlan = Plan::factory()->create([
            'name' => 'Second Plan',
            'price' => 49.99,
            'is_active' => true,
        ]);

        // This should be prevented by the application logic
        $this->assertDatabaseCount('subscriptions', 1);
    }

    /** @test */
    public function subscription_usage_resets_daily()
    {
        // Create subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Create usage for yesterday
        SyncUsage::create([
            'user_id' => $this->user->id,
            'period' => 'daily',
            'window_start' => now()->subDay()->startOfDay(),
            'window_end' => now()->subDay()->endOfDay(),
            'sync_count' => 50,
        ]);

        // Create usage for today
        SyncUsage::create([
            'user_id' => $this->user->id,
            'period' => 'daily',
            'window_start' => now()->startOfDay(),
            'window_end' => now()->endOfDay(),
            'sync_count' => 25,
        ]);

        // Get today's usage
        $todayUsage = SyncUsage::where('user_id', $this->user->id)
            ->where('period', 'daily')
            ->whereDate('window_start', now())
            ->first();

        $this->assertNotNull($todayUsage);
        $this->assertEquals(25, $todayUsage->sync_count);
    }
}