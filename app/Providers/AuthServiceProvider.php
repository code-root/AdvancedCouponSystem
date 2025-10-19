<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Admin;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Admin::class => AdminPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates if needed
        Gate::define('manage-admin-users', function (Admin $admin) {
            return $admin->hasPermissionTo('manage-admins', 'admin');
        });

        Gate::define('manage-user-management', function (Admin $admin) {
            return $admin->hasPermissionTo('manage-users', 'admin');
        });

        Gate::define('view-reports', function (Admin $admin) {
            return $admin->hasPermissionTo('view-reports', 'admin');
        });

        Gate::define('manage-system', function (Admin $admin) {
            return $admin->hasPermissionTo('manage-system', 'admin');
        });

        Gate::define('manage-settings', function (Admin $admin) {
            return $admin->hasPermissionTo('manage-settings', 'admin');
        });
    }
}

