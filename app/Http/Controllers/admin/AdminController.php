<?php

namespace App\Http\Controllers\admin;

use App\Models\Admin;
use App\Models\Status;
use App\Models\Target;
use App\Models\SiteSetting;
use App\Http\Controllers\Controller;
use App\Repository\AdminRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\admin\AdminUpdateRequest;
use Illuminate\Http\Request;


class AdminController extends Controller
{
//    Admin Auth
    protected $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function login()
    {
        return view('admin.login');
    }


    public function postLogin(Request $request)
    {
        // Validate the login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->remember ? 1 : 0;
        // Check if the user is already logged in
        // Attempt to login using the web guard
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $user = Auth::guard('admin')->user();
            // Check if the account is active
            if ($user->active == 0) {
                Auth::guard('admin')->logout();
                return redirect()->route('login')->with('error', 'Your account is inactive. Please contact support.')->withInput($request->only('email'));
            }
            // If active, redirect to the dashboard
            return redirect()->route('admin.dashboard')->with('success', 'Signed in');
        }

        // If credentials are invalid, redirect back with an error message
        return redirect()->route('admin.login')->with('error', 'Login details are not valid')->withInput($request->only('email'));
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        return to_route('admin.login');
    }

    public function dashboard(Request $request)
    {
        // Get site settings statistics
        $totalSettings = SiteSetting::count();
        $activeSettings = SiteSetting::where('is_active', true)->count();
        $recentChangesCount = SiteSetting::where('last_modified_at', '>=', now()->subDays(7))->count();
        $languagesCount = SiteSetting::distinct('locale')->count('locale');

        // Get recent changes
        $recentChanges = SiteSetting::with(['creator', 'updater'])
            ->orderBy('last_modified_at', 'desc')
            ->limit(5)
            ->get();

        // Get settings by group
        $settingsByGroup = SiteSetting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('admin.dashboard', compact(
            'totalSettings',
            'activeSettings', 
            'recentChangesCount',
            'languagesCount',
            'recentChanges',
            'settingsByGroup'
        ));
    }


//    A Profile
    public function profile()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.profile', [
            'user' => $user
        ]);
    }

    public function postProfile(AdminUpdateRequest $request)
    {
        $user = Auth::guard('admin')->user();
        $data = $request->except('password');
        if ($request->password != null) {
            $user->password = Hash::make($request->password);
        }
        $user->update($data);
        return back()->with('success', 'Profile updated successfully');
    }

    public function getLeads($categoryId)
    {
        $value = $this->adminRepository->getLeads($categoryId);

        if (!$value) {
            return back()->with('error', 'Not Enough Patients');

        }
        return back()->with('success', 'Patients retrieved successfully');
    }

    public function markAllNotificationsRead()
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $admin->unreadNotifications()->update(['read_at' => now()]);
        }
        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * Display site settings dashboard
     */
    public function siteSettingsDashboard()
    {
        // Get statistics
        $totalSettings = SiteSetting::count();
        $activeSettings = SiteSetting::where('is_active', true)->count();
        $recentChangesCount = SiteSetting::where('last_modified_at', '>=', now()->subDays(7))->count();
        $languagesCount = SiteSetting::distinct('locale')->count('locale');

        // Get recent changes
        $recentChanges = SiteSetting::with(['creator', 'updater'])
            ->orderBy('last_modified_at', 'desc')
            ->limit(10)
            ->get();

        // Get settings by group
        $settingsByGroup = SiteSetting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('admin.site-settings.dashboard', compact(
            'totalSettings',
            'activeSettings', 
            'recentChangesCount',
            'languagesCount',
            'recentChanges',
            'settingsByGroup'
        ));
    }
}
