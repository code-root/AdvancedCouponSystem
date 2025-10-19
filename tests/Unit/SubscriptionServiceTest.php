<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $subscriptionService;
    protected User $user;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->subscriptionService = new SubscriptionService();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test plan
        $this->plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'price' => 29.99,
            'trial_days' => 14,
            'max_networks' => 5,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_start_trial_for_user()
    {
        $this->subscriptionService->startTrial($this->user, $this->plan);

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        
        $this->assertNotNull($subscription);
        $this->assertEquals('trial', $subscription->status);
        $this->assertEquals($this->plan->id, $subscription->plan_id);
        $this->assertNotNull($subscription->trial_ends_at);
        $this->assertTrue(Carbon::parse($subscription->trial_ends_at)->isAfter(now()));
    }

    /** @test */
    public function it_can_activate_subscription()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->subscriptionService->activate($subscription);

        $subscription->refresh();
        
        $this->assertEquals('active', $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->ends_at);
    }

    /** @test */
    public function it_can_cancel_subscription()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->subscriptionService->cancel($subscription);

        $subscription->refresh();
        
        $this->assertEquals('cancelled', $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
    }

    /** @test */
    public function it_can_apply_coupon_to_subscription()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $coupon = \App\Models\PlanCoupon::factory()->create([
            'code' => 'TEST20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        $result = $this->subscriptionService->applyCoupon($subscription, $coupon);

        $this->assertTrue($result);
        
        $subscription->refresh();
        $this->assertEquals($coupon->id, $subscription->coupon_id);
    }

    /** @test */
    public function it_cannot_apply_inactive_coupon()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $coupon = \App\Models\PlanCoupon::factory()->create([
            'code' => 'INACTIVE',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'is_active' => false,
        ]);

        $result = $this->subscriptionService->applyCoupon($subscription, $coupon);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_check_if_user_has_active_subscription()
    {
        // No subscription
        $this->assertFalse($this->subscriptionService->hasActiveSubscription($this->user));

        // Trial subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->assertTrue($this->subscriptionService->hasActiveSubscription($this->user));

        // Active subscription
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $subscription->update(['status' => 'active']);

        $this->assertTrue($this->subscriptionService->hasActiveSubscription($this->user));

        // Expired subscription
        $subscription->update(['status' => 'expired']);

        $this->assertFalse($this->subscriptionService->hasActiveSubscription($this->user));
    }

    /** @test */
    public function it_can_get_user_subscription()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $userSubscription = $this->subscriptionService->getUserSubscription($this->user);

        $this->assertNotNull($userSubscription);
        $this->assertEquals($subscription->id, $userSubscription->id);
    }

    /** @test */
    public function it_returns_null_for_user_without_subscription()
    {
        $userSubscription = $this->subscriptionService->getUserSubscription($this->user);

        $this->assertNull($userSubscription);
    }
}