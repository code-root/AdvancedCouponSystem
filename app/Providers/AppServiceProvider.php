<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\NetworkConnection;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Observers\TrackingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers for automatic tracking
        NetworkConnection::observe(TrackingObserver::class);
        Campaign::observe(TrackingObserver::class);
        Coupon::observe(TrackingObserver::class);
        Purchase::observe(TrackingObserver::class);
    }
}
