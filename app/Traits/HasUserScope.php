<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasUserScope
{
    /**
     * Get the target user ID for data queries
     * Returns parent's ID if current user is a sub-user
     */
    protected function getTargetUserId(): int
    {
        /** @var User $user */
        $user = Auth::user();
        
        return $user->isSubUser() ? $user->parent_user_id : $user->id;
    }
    
    /**
     * Get the current logged-in user ID
     * This is used to track who made the action
     */
    protected function getCurrentUserId(): int
    {
        return Auth::id();
    }
    
    /**
     * Get the user ID to save in created_by/updated_by fields
     * Always returns current user ID (even if sub-user)
     */
    protected function getActionUserId(): int
    {
        return Auth::id();
    }
}

