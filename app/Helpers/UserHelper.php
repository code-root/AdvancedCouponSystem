<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UserHelper
{
    /**
     * Get the target user ID for queries
     * If current user is a sub-user, returns parent's ID
     * Otherwise, returns current user's ID
     */
    public static function getTargetUserId(): ?int
    {
        if (!Auth::check()) {
            return null;
        }
        
        // Check if admin is logged in (with error handling)
        if (self::isAdminLoggedIn()) {
            return null; // Admin doesn't have a target user ID
        }
        
        // Get the authenticated user and check if it's a User model
        $authenticatedUser = Auth::user();
        
        // Ensure we have a User model, not Admin model
        if (!$authenticatedUser instanceof User) {
            return null;
        }
        
        /** @var User $user */
        $user = $authenticatedUser;
        
        return $user->isSubUser() ? $user->parent_user_id : $user->id;
    }
    
    /**
     * Get the current authenticated user ID
     */
    public static function getCurrentUserId(): ?int
    {
        return Auth::id();
    }
    
    /**
     * Check if current user can access data of specific user
     * Optimized with caching to reduce database queries
     */
    public static function canAccessUserData(int $targetUserId): bool
    {
        // Admin can access all user data
        if (self::isAdminLoggedIn()) {
            return true;
        }
        
        // Get the authenticated user and check if it's a User model
        $authenticatedUser = Auth::user();
        if (!$authenticatedUser || !$authenticatedUser instanceof User) {
            return false;
        }
        
        /** @var User $currentUser */
        $currentUser = $authenticatedUser;
        $currentId = $currentUser->id;
        
        // Can access own data
        if ($targetUserId === $currentId) {
            return true;
        }
        
        // Can access parent's data if sub-user
        if ($currentUser->isSubUser() && $targetUserId === $currentUser->parent_user_id) {
            return true;
        }
        
        // Use cache to avoid repeated database queries for the same user
        $cacheKey = "user_access_{$currentId}_{$targetUserId}";
        
        return Cache::remember($cacheKey, 300, function () use ($targetUserId, $currentId) {
            // Can access sub-users data
            $targetUser = User::select('created_by')->find($targetUserId);
            return $targetUser && $targetUser->created_by === $currentId;
        });
    }
    
    /**
     * Check if admin is logged in with error handling and caching
     * This method safely checks admin authentication without throwing exceptions
     */
    private static function isAdminLoggedIn(): bool
    {
        try {
            // Check if admin guard is configured
            if (!Config::has('auth.guards.admin')) {
                return false;
            }
            
            // Use session-based caching to avoid repeated guard checks
            $sessionKey = 'admin_logged_in_status';
            if (session()->has($sessionKey)) {
                return session($sessionKey);
            }
            
            $isLoggedIn = Auth::guard('admin')->check();
            session([$sessionKey => $isLoggedIn]);
            
            return $isLoggedIn;
        } catch (\Exception $e) {
            // Log the error for debugging but don't throw it
            Log::warning('Admin guard check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get admin user safely with error handling
     */
    public static function getAdminUser()
    {
        try {
            if (self::isAdminLoggedIn()) {
                return Auth::guard('admin')->user();
            }
            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to get admin user: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clear user access cache for a specific user
     * Call this when user permissions change
     */
    public static function clearUserAccessCache(?int $userId = null): void
    {
        if ($userId) {
            // Clear cache for specific user
            $pattern = "user_access_{$userId}_*";
            Cache::forget($pattern);
        } else {
            // Clear all user access cache
            Cache::forget('user_access_*');
        }
    }
    
    /**
     * Clear admin login status cache
     * Call this when admin logs in/out
     */
    public static function clearAdminStatusCache(): void
    {
        session()->forget('admin_logged_in_status');
    }
    
    /**
     * Get the current authenticated user as User model
     * Returns null if not authenticated or if admin is logged in
     */
    public static function getCurrentUser(): ?User
    {
        if (!Auth::check()) {
            return null;
        }
        
        // Check if admin is logged in
        if (self::isAdminLoggedIn()) {
            return null;
        }
        
        $authenticatedUser = Auth::user();
        
        // Ensure we have a User model, not Admin model
        if (!$authenticatedUser instanceof User) {
            return null;
        }
        
        return $authenticatedUser;
    }
    
    /**
     * Check if current authenticated user is a regular user (not admin)
     */
    public static function isRegularUser(): bool
    {
        return self::getCurrentUser() !== null;
    }
}

