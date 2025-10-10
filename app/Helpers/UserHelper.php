<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        
        /** @var User $user */
        $user = Auth::user();
        
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
     */
    public static function canAccessUserData(int $targetUserId): bool
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentId = $currentUser->id;
        
        // Can access own data
        if ($targetUserId === $currentId) {
            return true;
        }
        
        // Can access parent's data if sub-user
        if ($currentUser->isSubUser() && $targetUserId === $currentUser->parent_user_id) {
            return true;
        }
        
        // Can access sub-users data
        $targetUser = User::find($targetUserId);
        if ($targetUser && $targetUser->created_by === $currentId) {
            return true;
        }
        
        return false;
    }
}

