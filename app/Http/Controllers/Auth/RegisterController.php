<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign default role if it exists
        if (Role::where('name', 'user')->exists()) {
            $user->assignRole('user');
        }

        // Send email verification notification (only if email is configured)
        if (config('mail.default') !== 'log' && config('mail.mailers.smtp.host')) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                \Log::error('Failed to send verification email: ' . $e->getMessage());
            }
        }

        Auth::login($user);

        // Redirect based on email configuration
        if (config('mail.default') === 'log' || !config('mail.mailers.smtp.host')) {
            return redirect()->route('dashboard')
                ->with('info', 'Registration successful! Email verification is disabled. Please configure SMTP to enable it.');
        }

        return redirect()->route('verification.notice')
            ->with('success', 'Registration successful! Please check your email to verify your account.');
    }
}

