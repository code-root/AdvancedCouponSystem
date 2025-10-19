<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin-specific permissions
        $permissions = [
            // Dashboard permissions
            'view-dashboard',
            
            // User Management permissions
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'impersonate-users',
            'export-users',
            
            // Role & Permission Management
            'manage-roles',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'assign-roles',
            
            'manage-permissions',
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            
            // Settings Management
            'manage-settings',
            'view-settings',
            'edit-general-settings',
            'edit-branding-settings',
            'edit-smtp-settings',
            'edit-seo-settings',
            'edit-payment-settings',
            
            // Reports permissions
            'view-reports',
            'view-user-sessions',
            'view-network-sessions',
            'view-sync-logs',
            'view-sync-statistics',
            'export-reports',
            
            // System Management
            'manage-system',
            'manage-countries',
            'manage-campaigns',
            'manage-networks',
            'manage-plans',
            'manage-subscriptions',
            
            // Admin Management
            'manage-admins',
            'view-admins',
            'create-admins',
            'edit-admins',
            'delete-admins',
            'assign-admin-roles',
            
            // Audit & Security
            'view-audit-logs',
            'manage-security',
            'view-system-logs',
        ];

        // Create permissions with admin guard
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        // Create admin roles
        $roles = [
            'super-admin' => [
                'description' => 'Full system access with all permissions',
                'permissions' => $permissions
            ],
            'admin-manager' => [
                'description' => 'Can manage other admins and system settings',
                'permissions' => [
                    'view-dashboard',
                    'manage-users',
                    'view-users',
                    'create-users',
                    'edit-users',
                    'delete-users',
                    'export-users',
                    'manage-roles',
                    'view-roles',
                    'create-roles',
                    'edit-roles',
                    'assign-roles',
                    'manage-permissions',
                    'view-permissions',
                    'manage-settings',
                    'view-settings',
                    'edit-general-settings',
                    'edit-branding-settings',
                    'edit-smtp-settings',
                    'edit-seo-settings',
                    'edit-payment-settings',
                    'view-reports',
                    'view-user-sessions',
                    'view-network-sessions',
                    'view-sync-logs',
                    'view-sync-statistics',
                    'export-reports',
                    'manage-system',
                    'manage-countries',
                    'manage-campaigns',
                    'manage-networks',
                    'manage-plans',
                    'manage-subscriptions',
                    'manage-admins',
                    'view-admins',
                    'create-admins',
                    'edit-admins',
                    'assign-admin-roles',
                    'view-audit-logs',
                    'view-system-logs',
                ]
            ],
            'content-manager' => [
                'description' => 'Can manage content, users, and view reports',
                'permissions' => [
                    'view-dashboard',
                    'manage-users',
                    'view-users',
                    'create-users',
                    'edit-users',
                    'export-users',
                    'view-roles',
                    'view-permissions',
                    'view-settings',
                    'edit-branding-settings',
                    'edit-seo-settings',
                    'view-reports',
                    'view-user-sessions',
                    'view-network-sessions',
                    'view-sync-logs',
                    'view-sync-statistics',
                    'export-reports',
                    'manage-countries',
                    'manage-campaigns',
                    'view-admins',
                ]
            ],
            'support-agent' => [
                'description' => 'Can view users and reports, limited system access',
                'permissions' => [
                    'view-dashboard',
                    'view-users',
                    'export-users',
                    'view-roles',
                    'view-permissions',
                    'view-settings',
                    'view-reports',
                    'view-user-sessions',
                    'view-network-sessions',
                    'view-sync-logs',
                    'view-sync-statistics',
                    'export-reports',
                    'view-admins',
                ]
            ],
            'viewer' => [
                'description' => 'Read-only access to dashboard and reports',
                'permissions' => [
                    'view-dashboard',
                    'view-users',
                    'view-roles',
                    'view-permissions',
                    'view-settings',
                    'view-reports',
                    'view-user-sessions',
                    'view-network-sessions',
                    'view-sync-logs',
                    'view-sync-statistics',
                    'view-admins',
                ]
            ]
        ];

        // Create roles with admin guard
        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'admin'
            ]);

            // Assign permissions to role
            $role->syncPermissions($roleData['permissions']);
        }

        // Assign super-admin role to existing admin
        $admin = Admin::first();
        if ($admin) {
            $admin->assignRole('super-admin');
        }

        $this->command->info('Admin permissions and roles created successfully!');
    }
}
