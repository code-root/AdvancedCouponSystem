<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Network;
use App\Models\Campaign;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionLimitService
{
    /**
     * Check if user can create a new order.
     */
    public function canCreateOrder(User $user): bool
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $ordersLimit = $plan->features['orders_limit'] ?? -1;
        
        if ($ordersLimit === -1) {
            return true; // Unlimited
        }

        $currentMonthOrders = $this->getCurrentMonthOrders($user);
        return $currentMonthOrders < $ordersLimit;
    }

    /**
     * Check if user can generate revenue within limit.
     */
    public function canGenerateRevenue(User $user, float $amount): bool
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $revenueLimit = $plan->features['revenue_limit'] ?? -1;
        
        if ($revenueLimit === -1) {
            return true; // Unlimited
        }

        $currentMonthRevenue = $this->getCurrentMonthRevenue($user);
        return ($currentMonthRevenue + $amount) <= $revenueLimit;
    }

    /**
     * Get remaining orders for current month.
     */
    public function getRemainingOrders(User $user): int
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return 0;
        }

        $plan = $subscription->plan;
        $ordersLimit = $plan->features['orders_limit'] ?? -1;
        
        if ($ordersLimit === -1) {
            return -1; // Unlimited
        }

        $currentMonthOrders = $this->getCurrentMonthOrders($user);
        return max(0, $ordersLimit - $currentMonthOrders);
    }

    /**
     * Get remaining revenue for current month.
     */
    public function getRemainingRevenue(User $user): float
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return 0;
        }

        $plan = $subscription->plan;
        $revenueLimit = $plan->features['revenue_limit'] ?? -1;
        
        if ($revenueLimit === -1) {
            return -1; // Unlimited
        }

        $currentMonthRevenue = $this->getCurrentMonthRevenue($user);
        return max(0, $revenueLimit - $currentMonthRevenue);
    }

    /**
     * Get current month orders count.
     */
    public function getCurrentMonthOrders(User $user): int
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return $user->purchases()
            ->whereBetween('order_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Get current month revenue.
     */
    public function getCurrentMonthRevenue(User $user): float
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return $user->purchases()
            ->whereBetween('order_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'approved')
            ->sum('revenue') ?? 0;
    }

    /**
     * Get comprehensive usage statistics for user.
     */
    public function getUsageStatistics(User $user): array
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return [
                'networks' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
                'campaigns' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
                'syncs' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
                'orders' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
                'revenue' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
            ];
        }

        $plan = $subscription->plan;
        $features = $plan->features;

        // Networks usage
        $networksCount = $user->networks()->count();
        $networksLimit = $features['networks_limit'] ?? 0;
        $networksPercentage = $networksLimit > 0 ? round(($networksCount / $networksLimit) * 100, 2) : 0;

        // Campaigns usage
        $campaignsCount = $user->campaigns()->count();
        $campaignsLimit = $features['campaigns_limit'] ?? -1;
        $campaignsPercentage = $campaignsLimit > 0 ? round(($campaignsCount / $campaignsLimit) * 100, 2) : 0;

        // Syncs usage (monthly)
        $currentMonthSyncs = $user->syncLogs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $syncsLimit = $features['syncs_per_month'] ?? -1;
        $syncsPercentage = $syncsLimit > 0 ? round(($currentMonthSyncs / $syncsLimit) * 100, 2) : 0;

        // Orders usage
        $currentMonthOrders = $this->getCurrentMonthOrders($user);
        $ordersLimit = $features['orders_limit'] ?? -1;
        $ordersPercentage = $ordersLimit > 0 ? round(($currentMonthOrders / $ordersLimit) * 100, 2) : 0;

        // Revenue usage
        $currentMonthRevenue = $this->getCurrentMonthRevenue($user);
        $revenueLimit = $features['revenue_limit'] ?? -1;
        $revenuePercentage = $revenueLimit > 0 ? round(($currentMonthRevenue / $revenueLimit) * 100, 2) : 0;

        return [
            'networks' => [
                'used' => $networksCount,
                'limit' => $networksLimit,
                'percentage' => $networksPercentage,
                'remaining' => $networksLimit > 0 ? max(0, $networksLimit - $networksCount) : -1,
            ],
            'campaigns' => [
                'used' => $campaignsCount,
                'limit' => $campaignsLimit,
                'percentage' => $campaignsPercentage,
                'remaining' => $campaignsLimit > 0 ? max(0, $campaignsLimit - $campaignsCount) : -1,
            ],
            'syncs' => [
                'used' => $currentMonthSyncs,
                'limit' => $syncsLimit,
                'percentage' => $syncsPercentage,
                'remaining' => $syncsLimit > 0 ? max(0, $syncsLimit - $currentMonthSyncs) : -1,
            ],
            'orders' => [
                'used' => $currentMonthOrders,
                'limit' => $ordersLimit,
                'percentage' => $ordersPercentage,
                'remaining' => $ordersLimit > 0 ? max(0, $ordersLimit - $currentMonthOrders) : -1,
            ],
            'revenue' => [
                'used' => $currentMonthRevenue,
                'limit' => $revenueLimit,
                'percentage' => $revenuePercentage,
                'remaining' => $revenueLimit > 0 ? max(0, $revenueLimit - $currentMonthRevenue) : -1,
            ],
        ];
    }

    /**
     * Check if user can add a network.
     */
    public function canAddNetwork(User $user): bool
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $networksLimit = $plan->features['networks_limit'] ?? 0;
        
        if ($networksLimit === -1) {
            return true; // Unlimited
        }

        $currentNetworks = $user->networks()->count();
        return $currentNetworks < $networksLimit;
    }

    /**
     * Check if user can add a campaign.
     */
    public function canAddCampaign(User $user): bool
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $campaignsLimit = $plan->features['campaigns_limit'] ?? -1;
        
        if ($campaignsLimit === -1) {
            return true; // Unlimited
        }

        $currentCampaigns = $user->campaigns()->count();
        return $currentCampaigns < $campaignsLimit;
    }

    /**
     * Check if user can sync data.
     */
    public function canSyncData(User $user): bool
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $syncsLimit = $plan->features['syncs_per_month'] ?? -1;
        
        if ($syncsLimit === -1) {
            return true; // Unlimited
        }

        // Check current month syncs
        $currentMonthSyncs = $user->syncLogs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $currentMonthSyncs < $syncsLimit;
    }
}
