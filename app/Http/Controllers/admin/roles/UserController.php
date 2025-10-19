<?php

namespace App\Http\Controllers\admin\roles;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function __construct()
    {
        // Middleware is now handled in routes or individual methods
        // This constructor can be used for other initialization if needed
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Admin::orderBy('id', 'DESC')->get();
        
        // Calculate statistics
        $stats = [
            'total_users' => \App\Models\User::count(),
            'active_subscriptions' => \App\Models\Subscription::where('status', 'active')->count(),
            'trial_users' => \App\Models\User::whereHas('subscription', function($query) {
                $query->where('status', 'trial');
            })->count(),
            'expired_subscriptions' => \App\Models\Subscription::where('status', 'expired')->count(),
            'no_subscription' => \App\Models\User::whereDoesntHave('subscription')->count(),
            'users_this_month' => \App\Models\User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        return view('admin.users.index', compact('data', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = Admin::create($input);
        $user->assignRole($request->input('roles'));
        return redirect()->back()
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Admin::find($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Admin::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        return view('admin.users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email,' . $id . ',id',
            'password' => 'same:confirm-password',
            'roles' => 'required',
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $user = Admin::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $user->assignRole($request->input('roles'));
        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function toggleStatus(Request $request)
    {
        $user = Admin::find($request->id);

        if ($user) {
            if ($user->active == 1) {
                $active = 0;
            } else {
                $active = 1;
            }
            $user->update(['active' => $active ?? 0]);
            return response()->json([
                'success' => true,
                'active' => $user->active,
                'active_api' => $active,
            ]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Admin::find($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    public function updateToken(Request $request)
    {
        try {
            $request->user()->update(['fcm_token' => $request->token]);
            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false
            ], 500);
        }
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        session()->flush();
        session()->regenerate();
        session()->flash('success', 'You have been logged out successfully.');

        return redirect()->route('admin.login');
    }

    public function profile()
    {
        $data = Admin::where('id', auth('admin')->user()->id)->first();
        return view('admin.users.profile', compact('data'));
    }


    public function updateProfile(Request $request)
    {
        $user = auth('admin')->user();
        $user = Admin::find($user->id);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:admins,email,' . $user->id,
            'password' => 'same:confirm-password',
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $user->update($input);
        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $user = auth('admin')->user();
        $user = Admin::find($user->id);
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|same:confirm-password',
        ]);

        if (Hash::check($request->old_password, $user->password)) {
            $input['password'] = Hash::make($request->password);
            $user->update($input);
            return redirect()->back()->with('success', 'Password updated successfully');
        } else {
            return redirect()->back()->with('error', 'Old password is incorrect');
        }
    }

    public function changeProfilePicture(Request $request)
    {
        $user = auth('admin')->user();
        $user = Admin::find($user->id);
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $user->update(['profile_picture' => $filename]);
            return redirect()->back()->with('success', 'Profile picture updated successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to upload profile picture');
        }
    }

    public function deleteProfilePicture()
    {
        $user = auth('admin')->user();
        $user = Admin::find($user->id);
        if ($user->profile_picture) {
            unlink(public_path('images/' . $user->profile_picture));
            $user->update(['profile_picture' => null]);
            return redirect()->back()->with('success', 'Profile picture deleted successfully');
        } else {
            return redirect()->back()->with('error', 'No profile picture to delete');
        }
    }

    public function deleteAccount()
    {
        $user = auth('admin')->user();
        $user = Admin::find($user->id);
        if ($user) {
            $user->delete();
            return redirect()->route('admin.login')->with('success', 'Account deleted successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to delete account');
        }
    }

    public function getUserById($id)
    {
        $user = Admin::find($id);
        return response()->json($user);
    }

    public function getUserByEmail($email)
    {
        $user = Admin::where('email', $email)->first();
        return response()->json($user);
    }

    public function getUserByPhone($phone)
    {
        $user = Admin::where('phone', $phone)->first();
        return response()->json($user);
    }

    public function getUserByName($name)
    {
        $user = Admin::where('name', 'like', '%' . $name . '%')->get();
        return response()->json($user);
    }

    public function getUserByRole($role)
    {
        $user = Admin::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })->get();
        return response()->json($user);
    }

    public function getUserByStatus($status)
    {
        $user = Admin::where('active', $status)->get();
        return response()->json($user);
    }

    public function getUserByCreatedAt($created_at)
    {
        $user = Admin::whereDate('created_at', $created_at)->get();
        return response()->json($user);
    }

    public function getUserByUpdatedAt($updated_at)
    {
        $user = Admin::whereDate('updated_at', $updated_at)->get();
        return response()->json($user);
    }

    public function getUserByLastLogin($last_login)
    {
        $user = Admin::whereDate('last_login', $last_login)->get();
        return response()->json($user);
    }

    public function getUserByLastActivity($last_activity)
    {
        $user = Admin::whereDate('last_activity', $last_activity)->get();
        return response()->json($user);
    }

    public function getUserByLastPasswordChange($last_password_change)
    {
        $user = Admin::whereDate('last_password_change', $last_password_change)->get();
        return response()->json($user);
    }

    public function getUserByLastProfileUpdate($last_profile_update)
    {
        $user = Admin::whereDate('last_profile_update', $last_profile_update)->get();
        return response()->json($user);
    }

    public function getUserByLastProfilePictureUpdate($last_profile_picture_update)
    {
        $user = Admin::whereDate('last_profile_picture_update', $last_profile_picture_update)->get();
        return response()->json($user);
    }

    public function getUserByLastProfilePictureDelete($last_profile_picture_delete)
    {
        $user = Admin::whereDate('last_profile_picture_delete', $last_profile_picture_delete)->get();
        return response()->json($user);
    }

    public function getUserByLastProfilePictureChange($last_profile_picture_change)
    {
        $user = Admin::whereDate('last_profile_picture_change', $last_profile_picture_change)->get();
        return response()->json($user);
    }

    public function getUserByLastProfilePictureUpload($last_profile_picture_upload)
    {
        $user = Admin::whereDate('last_profile_picture_upload', $last_profile_picture_upload)->get();
        return response()->json($user);
    }

    public function getUserByLastProfilePictureDownload($last_profile_picture_download)
    {
        $user = Admin::whereDate('last_profile_picture_download', $last_profile_picture_download)->get();
        return response()->json($user);
    }
}
