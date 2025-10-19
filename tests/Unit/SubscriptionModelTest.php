<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\PlanCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class SubscriptionModelTest extends TestCase
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
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_create_subscription()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertEquals($this->plan->id, $subscription->plan_id);
        $this->assertEquals('trial', $subscription->status);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertInstanceOf(User::class, $subscription->user);
        $this->assertEquals($this->user->id, $subscription->user->id);
    }

    /** @test */
    public function it_belongs_to_plan()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertInstanceOf(Plan::class, $subscription->plan);
        $this->assertEquals($this->plan->id, $subscription->plan->id);
    }

    /** @test */
    public function it_can_have_coupon()
    {
        $coupon = PlanCoupon::factory()->create([
            'code' => 'TEST20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
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

        $this->assertInstanceOf(PlanCoupon::class, $subscription->coupon);
        $this->assertEquals($coupon->id, $subscription->coupon->id);
    }

    /** @test */
    public function it_can_check_if_trial_is_active()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertTrue($subscription->isTrialActive());
    }

    /** @test */
    public function it_can_check_if_trial_is_expired()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->subDays(1),
        ]);

        $this->assertFalse($subscription->isTrialActive());
    }

    /** @test */
    public function it_can_check_if_subscription_is_active()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(29),
        ]);

        $this->assertTrue($subscription->isActive());
    }

    /** @test */
    public function it_can_check_if_subscription_is_expired()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(31),
            'ends_at' => now()->subDays(1),
        ]);

        $this->assertFalse($subscription->isActive());
    }

    /** @test */
    public function it_can_check_if_subscription_is_cancelled()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'cancelled',
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(29),
            'cancelled_at' => now(),
        ]);

        $this->assertTrue($subscription->isCancelled());
    }

    /** @test */
    public function it_can_get_days_remaining_in_trial()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $daysRemaining = $subscription->getTrialDaysRemaining();

        $this->assertEquals(7, $daysRemaining);
    }

    /** @test */
    public function it_returns_zero_days_remaining_for_expired_trial()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->subDays(1),
        ]);

        $daysRemaining = $subscription->getTrialDaysRemaining();

        $this->assertEquals(0, $daysRemaining);
    }

    /** @test */
    public function it_can_get_days_remaining_in_subscription()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(29),
        ]);

        $daysRemaining = $subscription->getSubscriptionDaysRemaining();

        $this->assertEquals(29, $daysRemaining);
    }

    /** @test */
    public function it_returns_zero_days_remaining_for_expired_subscription()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(31),
            'ends_at' => now()->subDays(1),
        ]);

        $daysRemaining = $subscription->getSubscriptionDaysRemaining();

        $this->assertEquals(0, $daysRemaining);
    }

    /** @test */
    public function it_can_calculate_discounted_price()
    {
        $coupon = PlanCoupon::factory()->create([
            'code' => 'TEST20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
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

        // 29.99 - (29.99 * 0.20) = 23.992
        $this->assertEquals(23.992, $discountedPrice);
    }

    /** @test */
    public function it_returns_original_price_when_no_coupon()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $price = $subscription->getDiscountedPrice();

        $this->assertEquals($this->plan->price, $price);
    }

    /** @test */
    public function it_can_calculate_discount_amount()
    {
        $coupon = PlanCoupon::factory()->create([
            'code' => 'TEST20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
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

        $discountAmount = $subscription->getDiscountAmount();

        // 29.99 * 0.20 = 5.998
        $this->assertEquals(5.998, $discountAmount);
    }

    /** @test */
    public function it_returns_zero_discount_when_no_coupon()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $discountAmount = $subscription->getDiscountAmount();

        $this->assertEquals(0, $discountAmount);
    }

    /** @test */
    public function it_can_get_next_billing_date()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(29),
        ]);

        $nextBillingDate = $subscription->getNextBillingDate();

        $this->assertInstanceOf(Carbon::class, $nextBillingDate);
        $this->assertEquals(now()->addDays(29)->format('Y-m-d'), $nextBillingDate->format('Y-m-d'));
    }

    /** @test */
    public function it_returns_null_for_next_billing_date_when_cancelled()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'cancelled',
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(29),
            'cancelled_at' => now(),
        ]);

        $nextBillingDate = $subscription->getNextBillingDate();

        $this->assertNull($nextBillingDate);
    }

    /** @test */
    public function it_can_scope_active_subscriptions()
    {
        // Create active subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(29),
        ]);

        // Create expired subscription
        Subscription::create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => $this->plan->id,
            'status' => 'expired',
            'starts_at' => now()->subDays(31),
            'ends_at' => now()->subDays(1),
        ]);

        $activeSubscriptions = Subscription::active()->get();

        $this->assertCount(1, $activeSubscriptions);
        $this->assertEquals('active', $activeSubscriptions->first()->status);
    }

    /** @test */
    public function it_can_scope_trial_subscriptions()
    {
        // Create trial subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        // Create active subscription
        Subscription::create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(29),
        ]);

        $trialSubscriptions = Subscription::trial()->get();

        $this->assertCount(1, $trialSubscriptions);
        $this->assertEquals('trial', $trialSubscriptions->first()->status);
    }

    /** @test */
    public function it_can_scope_expiring_trials()
    {
        // Create trial expiring in 3 days
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(3),
        ]);

        // Create trial expiring in 10 days
        Subscription::create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(10),
        ]);

        $expiringTrials = Subscription::expiringTrials(5)->get();

        $this->assertCount(1, $expiringTrials);
        $this->assertEquals($this->user->id, $expiringTrials->first()->user_id);
    }

    /** @test */
    public function it_can_scope_expired_trials()
    {
        // Create expired trial
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->subDays(1),
        ]);

        // Create active trial
        Subscription::create([
            'user_id' => User::factory()->create()->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $expiredTrials = Subscription::expiredTrials()->get();

        $this->assertCount(1, $expiredTrials);
        $this->assertEquals($this->user->id, $expiredTrials->first()->user_id);
    }
}