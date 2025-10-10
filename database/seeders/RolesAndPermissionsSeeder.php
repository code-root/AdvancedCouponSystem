<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Broker permissions
            'view brokers',
            'create brokers',
            'edit brokers',
            'delete brokers',
            
            // Campaign permissions
            'view campaigns',
            'create campaigns',
            'edit campaigns',
            'delete campaigns',
            
            // Coupon permissions
            'view coupons',
            'create coupons',
            'edit coupons',
            'delete coupons',
            'validate coupons',
            'redeem coupons',
            
            // Purchase permissions
            'view purchases',
            'create purchases',
            'edit purchases',
            'delete purchases',
            
            // Country permissions
            'view countries',
            'create countries',
            'edit countries',
            'delete countries',
            
            // Report permissions
            'view reports',
            'export reports',
            
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            
            // Settings permissions
            'view settings',
            'edit settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin role - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin role - has most permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view brokers', 'create brokers', 'edit brokers', 'delete brokers',
            'view campaigns', 'create campaigns', 'edit campaigns', 'delete campaigns',
            'view coupons', 'create coupons', 'edit coupons', 'delete coupons',
            'validate coupons', 'redeem coupons',
            'view purchases', 'create purchases', 'edit purchases', 'delete purchases',
            'view countries', 'create countries', 'edit countries', 'delete countries',
            'view reports', 'export reports',
            'view users', 'create users', 'edit users',
            'view settings',
        ]);

        // Manager role - can manage campaigns and coupons
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'view brokers',
            'view campaigns', 'create campaigns', 'edit campaigns',
            'view coupons', 'create coupons', 'edit coupons',
            'validate coupons', 'redeem coupons',
            'view purchases', 'create purchases', 'edit purchases',
            'view countries',
            'view reports',
        ]);

        // User role - basic permissions
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo([
            'view campaigns',
            'view coupons',
            'validate coupons',
            'redeem coupons',
            'view purchases',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
