<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            'super-admin' => 'Super Administrator',
            'admin' => 'Administrator', 
            'user' => 'Regular User',
            'moderator' => 'Moderator'
        ];

        foreach ($roles as $name => $displayName) {
            Role::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Create permissions
        $permissions = [
            // User permissions
            'view-dashboard' => 'View Dashboard',
            'manage-campaigns' => 'Manage Campaigns',
            'manage-coupons' => 'Manage Coupons',
            'view-reports' => 'View Reports',
            'manage-networks' => 'Manage Networks',
            
            // Admin permissions
            'view-admin-dashboard' => 'View Admin Dashboard',
            'manage-users' => 'Manage Users',
            'manage-plans' => 'Manage Plans',
            'manage-subscriptions' => 'Manage Subscriptions',
            'view-admin-reports' => 'View Admin Reports',
            'manage-system-settings' => 'Manage System Settings',
            'manage-roles' => 'Manage Roles',
            'manage-permissions' => 'Manage Permissions',
            
            // Super admin permissions
            'manage-admins' => 'Manage Administrators',
            'manage-system' => 'Manage System',
            'view-system-logs' => 'View System Logs',
            'manage-backups' => 'Manage Backups',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Assign permissions to roles
        $superAdmin = Role::findByName('super-admin');
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::findByName('admin');
        $admin->givePermissionTo([
            'view-admin-dashboard',
            'manage-users',
            'manage-plans',
            'manage-subscriptions',
            'view-admin-reports',
            'manage-system-settings',
            'manage-roles',
            'manage-permissions',
        ]);

        $moderator = Role::findByName('moderator');
        $moderator->givePermissionTo([
            'view-admin-dashboard',
            'manage-users',
            'view-admin-reports',
        ]);

        $user = Role::findByName('user');
        $user->givePermissionTo([
            'view-dashboard',
            'manage-campaigns',
            'manage-coupons',
            'view-reports',
            'manage-networks',
        ]);
    }
}
