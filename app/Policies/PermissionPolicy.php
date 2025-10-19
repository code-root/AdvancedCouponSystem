<?php

namespace App\Policies;

use App\Models\Admin;
use Spatie\Permission\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Permission $permission): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin') && $permission->guard_name === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Permission $permission): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin') && $permission->guard_name === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Permission $permission): bool
    {
        // Cannot delete permission that is assigned to roles
        if ($permission->roles()->count() > 0) {
            return false;
        }

        return $admin->hasPermissionTo('manage-permissions', 'admin') && $permission->guard_name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Permission $permission): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin') && $permission->guard_name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Permission $permission): bool
    {
        // Only super-admin can permanently delete permissions
        return $admin->hasRole('super-admin') && 
               $permission->guard_name === 'admin' && 
               $permission->roles()->count() === 0;
    }

    /**
     * Determine whether the user can bulk create permissions.
     */
    public function bulkCreate(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin');
    }

    /**
     * Determine whether the user can search permissions.
     */
    public function search(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin');
    }

    /**
     * Determine whether the user can get permission groups.
     */
    public function getGroups(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-permissions', 'admin');
    }
}

