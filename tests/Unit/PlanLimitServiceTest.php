<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SyncUsage;
use App\Services\PlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PlanLimitServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlanLimitService $planLimitService;
    protected User $user;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->planLimitService = new PlanLimitService();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test plan
        $this->plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'max_networks' => 5,
            'daily_sync_limit' => 100,
            'monthly_sync_limit' => 2000,
            'revenue_cap' => 10000,
            'orders_cap' => 500,
            'is_active' => true,
        ]);

        // Create subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    /** @test */
    public function it_can_get_active_subscription()
    {
        $subscription = $this->planLimitService->getActiveSubscription($this->user);
        
        $this->assertNotNull($subscription);
        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertEquals($this->plan->id, $subscription->plan_id);
    }

    /** @test */
    public function it_returns_null_for_user_without_subscription()
    {
        $userWithoutSubscription = User::factory()->create();
        
        $subscription = $this->planLimitService->getActiveSubscription($userWithoutSubscription);
        
        $this->assertNull($subscription);
    }

    /** @test */
    public function it_can_assert_user_is_subscribed()
    {
        $this->expectNotToPerformAssertions();
        
        $this->planLimitService->assertSubscribed($this->user);
    }

    /** @test */
    public function it_throws_exception_for_unsubscribed_user()
    {
        $userWithoutSubscription = User::factory()->create();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription required');
        
        $this->planLimitService->assertSubscribed($userWithoutSubscription);
    }

    /** @test */
    public function it_throws_exception_for_expired_subscription()
    {
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $subscription->update(['status' => 'expired']);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription inactive or expired');
        
        $this->planLimitService->assertSubscribed($this->user);
    }

    /** @test */
    public function it_allows_trial_subscription()
    {
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $subscription->update([
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);
        
        $this->expectNotToPerformAssertions();
        
        $this->planLimitService->assertSubscribed($this->user);
    }

    /** @test */
    public function it_can_assert_can_add_network()
    {
        // Mock the getActiveNetworkConnectionsCount method
        $this->user->shouldReceive('getActiveNetworkConnectionsCount')->andReturn(3);
        
        $this->expectNotToPerformAssertions();
        
        $this->planLimitService->assertCanAddNetwork($this->user);
    }

    /** @test */
    public function it_throws_exception_when_network_limit_reached()
    {
        // Mock the getActiveNetworkConnectionsCount method to return max networks
        $this->user->shouldReceive('getActiveNetworkConnectionsCount')->andReturn(5);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plan limit: max networks reached');
        
        $this->planLimitService->assertCanAddNetwork($this->user);
    }

    /** @test */
    public function it_can_assert_can_sync()
    {
        $this->expectNotToPerformAssertions();
        
        $this->planLimitService->assertCanSync($this->user);
    }

    /** @test */
    public function it_throws_exception_when_daily_sync_limit_reached()
    {
        // Create daily usage that exceeds the limit
        SyncUsage::create([
            'user_id' => $this->user->id,
            'period' => 'daily',
            'window_start' => now()->startOfDay(),
            'window_end' => now()->endOfDay(),
            'sync_count' => 100, // At the limit
        ]);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plan limit: daily sync limit reached');
        
        $this->planLimitService->assertCanSync($this->user);
    }

    /** @test */
    public function it_throws_exception_when_monthly_sync_limit_reached()
    {
        // Create monthly usage that exceeds the limit
        SyncUsage::create([
            'user_id' => $this->user->id,
            'period' => 'monthly',
            'window_start' => now()->startOfMonth(),
            'window_end' => now()->endOfMonth(),
            'sync_count' => 2000, // At the limit
        ]);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plan limit: monthly sync limit reached');
        
        $this->planLimitService->assertCanSync($this->user);
    }

    /** @test */
    public function it_can_increment_sync_count()
    {
        $this->planLimitService->incrementSyncCount($this->user, 5);
        
        $dailyUsage = SyncUsage::where('user_id', $this->user->id)
            ->where('period', 'daily')
            ->whereDate('window_start', now())
            ->first();
            
        $this->assertNotNull($dailyUsage);
        $this->assertEquals(5, $dailyUsage->sync_count);
        
        $monthlyUsage = SyncUsage::where('user_id', $this->user->id)
            ->where('period', 'monthly')
            ->whereMonth('window_start', now()->month)
            ->first();
            
        $this->assertNotNull($monthlyUsage);
        $this->assertEquals(5, $monthlyUsage->sync_count);
    }

    /** @test */
    public function it_can_increment_revenue_count()
    {
        $this->planLimitService->incrementRevenueCount($this->user, 1000);
        
        $dailyUsage = SyncUsage::where('user_id', $this->user->id)
            ->where('period', 'daily')
            ->whereDate('window_start', now())
            ->first();
            
        $this->assertNotNull($dailyUsage);
        $this->assertEquals(1000, $dailyUsage->revenue_count);
    }

    /** @test */
    public function it_can_increment_orders_count()
    {
        $this->planLimitService->incrementOrdersCount($this->user, 10);
        
        $dailyUsage = SyncUsage::where('user_id', $this->user->id)
            ->where('period', 'daily')
            ->whereDate('window_start', now())
            ->first();
            
        $this->assertNotNull($dailyUsage);
        $this->assertEquals(10, $dailyUsage->orders_count);
    }

    /** @test */
    public function it_can_get_usage_for_period()
    {
        // Create usage record
        SyncUsage::create([
            'user_id' => $this->user->id,
            'period' => 'daily',
            'window_start' => now()->startOfDay(),
            'window_end' => now()->endOfDay(),
            'sync_count' => 25,
            'revenue_count' => 500,
            'orders_count' => 5,
        ]);
        
        $usage = $this->planLimitService->getUsage($this->user, 'daily');
        
        $this->assertNotNull($usage);
        $this->assertEquals(25, $usage->sync_count);
        $this->assertEquals(500, $usage->revenue_count);
        $this->assertEquals(5, $usage->orders_count);
    }

    /** @test */
    public function it_returns_null_for_no_usage()
    {
        $usage = $this->planLimitService->getUsage($this->user, 'daily');
        
        $this->assertNull($usage);
    }

    /** @test */
    public function it_can_check_if_within_sync_window()
    {
        // Test with hour-based sync window
        $plan = Plan::factory()->create([
            'sync_window_unit' => 'hour',
            'sync_window_size' => 2,
        ]);
        
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $subscription->update(['plan_id' => $plan->id]);
        
        $result = $this->planLimitService->isWithinSyncWindow($this->user);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_check_sync_window_with_time_restrictions()
    {
        // Test with time restrictions
        $plan = Plan::factory()->create([
            'sync_window_unit' => 'hour',
            'sync_window_size' => 1,
            'sync_allowed_from_time' => '09:00:00',
            'sync_allowed_to_time' => '17:00:00',
        ]);
        
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $subscription->update(['plan_id' => $plan->id]);
        
        // Mock current time to be within allowed hours
        Carbon::setTestNow(Carbon::createFromTime(12, 0, 0));
        
        $result = $this->planLimitService->isWithinSyncWindow($this->user);
        
        $this->assertTrue($result);
        
        // Mock current time to be outside allowed hours
        Carbon::setTestNow(Carbon::createFromTime(20, 0, 0));
        
        $result = $this->planLimitService->isWithinSyncWindow($this->user);
        
        $this->assertFalse($result);
        
        Carbon::setTestNow(); // Reset
    }
}