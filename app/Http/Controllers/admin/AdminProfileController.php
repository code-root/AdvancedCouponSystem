<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    /**
     * Display admin profile page.
     */
    public function index()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Update admin profile.
     */
    public function update(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
        ]);
        
        $user->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()
            ]);
        }
        
        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Update admin password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);
        
        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
        }
        
        return back()->with('success', 'Password updated successfully');
    }

    /**
     * AJAX update profile.
     */
    public function updateAjax(Request $request)
    {
        return $this->update($request);
    }

    /**
     * AJAX update password.
     */
    public function updatePasswordAjax(Request $request)
    {
        return $this->updatePassword($request);
    }

    /**
     * Upload avatar.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::guard('admin')->user();
        
        // Delete old avatar if exists
        if ($user->avatar) {
            \Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $path]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Avatar updated successfully',
                'avatar_url' => asset('storage/' . $path)
            ]);
        }

        return back()->with('success', 'Avatar updated successfully');
    }
}

