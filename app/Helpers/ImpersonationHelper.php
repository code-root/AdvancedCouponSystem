<?php

namespace App\Helpers;

class ImpersonationHelper
{
    /**
     * Check if admin is currently impersonating a user
     */
    public static function isImpersonating(): bool
    {
        return session()->has('admin_impersonating') && session('admin_impersonating') !== null;
    }

    /**
     * Get the admin ID who is impersonating
     */
    public static function getAdminId(): ?int
    {
        return session('admin_impersonating');
    }

    /**
     * Get the impersonated user ID
     */
    public static function getImpersonatedUserId(): ?int
    {
        return session('admin_impersonating_user_id');
    }

    /**
     * Get the impersonated user name
     */
    public static function getImpersonatedUserName(): ?string
    {
        return session('admin_impersonating_user_name');
    }

    /**
     * Get the impersonation token for validation
     */
    public static function getImpersonationToken(): ?string
    {
        return session('admin_impersonation_token');
    }

    /**
     * Start impersonation session
     */
    public static function startImpersonation(int $adminId, int $userId, string $userName): void
    {
        $token = bin2hex(random_bytes(16));
        
        session([
            'admin_impersonating' => $adminId,
            'admin_impersonating_user_id' => $userId,
            'admin_impersonating_user_name' => $userName,
            'admin_impersonation_token' => $token,
            'admin_impersonation_started_at' => now()->toISOString(),
        ]);
    }

    /**
     * Stop impersonation session
     */
    public static function stopImpersonation(): void
    {
        session()->forget([
            'admin_impersonating',
            'admin_impersonating_user_id',
            'admin_impersonating_user_name',
            'admin_impersonation_token',
            'admin_impersonation_started_at',
        ]);
    }

    /**
     * Validate impersonation session
     */
    public static function validateImpersonation(): bool
    {
        if (!self::isImpersonating()) {
            return false;
        }

        $adminId = self::getAdminId();
        $userId = self::getImpersonatedUserId();
        $token = self::getImpersonationToken();

        // Basic validation
        if (!$adminId || !$userId || !$token) {
            self::stopImpersonation();
            return false;
        }

        // Check if admin still exists and is active
        $admin = \App\Models\Admin::find($adminId);
        if (!$admin || !$admin->active) {
            self::stopImpersonation();
            return false;
        }

        // Check if user still exists
        $user = \App\Models\User::find($userId);
        if (!$user) {
            self::stopImpersonation();
            return false;
        }

        return true;
    }

    /**
     * Get impersonation duration
     */
    public static function getImpersonationDuration(): ?int
    {
        $startedAt = session('admin_impersonation_started_at');
        if (!$startedAt) {
            return null;
        }

        return now()->diffInMinutes(\Carbon\Carbon::parse($startedAt));
    }

    /**
     * Check if impersonation session is expired (optional timeout)
     */
    public static function isImpersonationExpired(int $maxMinutes = 60): bool
    {
        $duration = self::getImpersonationDuration();
        return $duration !== null && $duration > $maxMinutes;
    }
}
