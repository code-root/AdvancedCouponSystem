<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-admins', 'admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('manage-admins', 'admin') || $admin->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-admins', 'admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Admin $model): bool
    {
        // Super admin can update anyone
        if ($admin->hasRole('super-admin')) {
            return true;
        }

        // Admin can update themselves
        if ($admin->id === $model->id) {
            return true;
        }

        // Admin manager can update other admins (except super-admin)
        if ($admin->hasRole('admin-manager') && !$model->hasRole('super-admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Admin $model): bool
    {
        // Cannot delete super-admin
        if ($model->hasRole('super-admin')) {
            return false;
        }

        // Cannot delete yourself
        if ($admin->id === $model->id) {
            return false;
        }

        // Super admin can delete anyone (except other super-admins)
        if ($admin->hasRole('super-admin')) {
            return true;
        }

        // Admin manager can delete other admins (except super-admin)
        if ($admin->hasRole('admin-manager') && !$model->hasRole('super-admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('manage-admins', 'admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Admin $model): bool
    {
        // Only super-admin can permanently delete
        return $admin->hasRole('super-admin') && !$model->hasRole('super-admin');
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRoles(Admin $admin, Admin $model): bool
    {
        // Super admin can assign roles to anyone
        if ($admin->hasRole('super-admin')) {
            return true;
        }

        // Admin manager can assign roles (except super-admin role)
        if ($admin->hasRole('admin-manager') && !$model->hasRole('super-admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can toggle status.
     */
    public function toggleStatus(Admin $admin, Admin $model): bool
    {
        // Cannot toggle super-admin status
        if ($model->hasRole('super-admin')) {
            return false;
        }

        // Cannot toggle your own status
        if ($admin->id === $model->id) {
            return false;
        }

        // Super admin can toggle anyone's status
        if ($admin->hasRole('super-admin')) {
            return true;
        }

        // Admin manager can toggle other admins' status
        if ($admin->hasRole('admin-manager') && !$model->hasRole('super-admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view audit logs.
     */
    public function viewAuditLogs(Admin $admin): bool
    {
        return $admin->hasPermissionTo('view-audit-logs', 'admin');
    }

    /**
     * Determine whether the user can manage security settings.
     */
    public function manageSecurity(Admin $admin): bool
    {
        return $admin->hasPermissionTo('manage-security', 'admin');
    }
}

