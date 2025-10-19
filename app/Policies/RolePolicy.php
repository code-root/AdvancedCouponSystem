<?php

namespace App\Policies;

use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-roles', 'admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Role $role): bool
    {
        return $admin->hasPermissionTo('manage-roles', 'admin') && $role->guard_name === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-roles', 'admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Role $role): bool
    {
        // Cannot update super-admin role
        if ($role->name === 'super-admin') {
            return false;
        }

        return $admin->hasPermissionTo('manage-roles', 'admin') && $role->guard_name === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Role $role): bool
    {
        // Cannot delete super-admin role
        if ($role->name === 'super-admin') {
            return false;
        }

        // Cannot delete role that has users
        if ($role->users()->count() > 0) {
            return false;
        }

        return $admin->hasPermissionTo('manage-roles', 'admin') && $role->guard_name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Role $role): bool
    {
        return $admin->hasPermissionTo('manage-roles', 'admin') && $role->guard_name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Role $role): bool
    {
        // Only super-admin can permanently delete roles
        return $admin->hasRole('super-admin') && 
               $role->guard_name === 'admin' && 
               $role->name !== 'super-admin';
    }

    /**
     * Determine whether the user can assign permissions to the role.
     */
    public function assignPermissions(Admin $admin, Role $role): bool
    {
        // Cannot assign permissions to super-admin role
        if ($role->name === 'super-admin') {
            return false;
        }

        return $admin->hasPermissionTo('manage-roles', 'admin') && $role->guard_name === 'admin';
    }

    /**
     * Determine whether the user can clone the role.
     */
    public function clone(Admin $admin, Role $role): bool
    {
        return $admin->hasPermissionTo('manage-roles', 'admin') && $role->guard_name === 'admin';
    }

    /**
     * Determine whether the user can export roles.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-roles', 'admin');
    }

    /**
     * Determine whether the user can import roles.
     */
    public function import(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-roles', 'admin');
    }
}

