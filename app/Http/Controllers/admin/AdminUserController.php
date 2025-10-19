<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    /**
     * Display a listing of admin users.
     */
    public function index()
    {
        $admins = Admin::with('roles')->orderBy('id', 'DESC')->get();
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        
        // Calculate statistics
        $stats = [
            'total_admins' => Admin::count(),
            'active_admins' => Admin::where('active', true)->count(),
            'inactive_admins' => Admin::where('active', false)->count(),
            'super_admins' => Admin::role('super-admin')->count(),
            'admin_managers' => Admin::role('admin-manager')->count(),
            'content_managers' => Admin::role('content-manager')->count(),
            'support_agents' => Admin::role('support-agent')->count(),
        ];

        return view('admin.admin-users.index', compact('admins', 'roles', 'stats'));
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create()
    {
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        return view('admin.admin-users.create', compact('roles'));
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'active' => $validated['active'] ?? true,
        ]);

        // Assign roles
        if (!empty($validated['roles'])) {
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $admin->assignRole($roles);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully',
                'data' => $admin->load('roles')
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Admin user created successfully');
    }

    /**
     * Display the specified admin user.
     */
    public function show($id)
    {
        $admin = Admin::with('roles')->findOrFail($id);
        return view('admin.users.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin user.
     */
    public function edit($id)
    {
        $admin = Admin::with('roles')->findOrFail($id);
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        return view('admin.admin-users.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified admin user.
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'active' => $validated['active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $admin->update($updateData);

        // Sync roles
        if (isset($validated['roles'])) {
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $admin->syncRoles($roles);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin user updated successfully',
                'data' => $admin->load('roles')
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Admin user updated successfully');
    }

    /**
     * Remove the specified admin user.
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent deletion of super-admin
        if ($admin->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super-admin user'
            ], 403);
        }
        
        // Prevent self-deletion
        if ($admin->id === auth()->guard('admin')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }
        
        $admin->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Admin user deleted successfully'
        ]);
    }

    /**
     * Toggle admin status.
     */
    public function toggleStatus(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent deactivating super-admin
        if ($admin->hasRole('super-admin') && $admin->active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate super-admin user'
            ], 403);
        }
        
        $admin->update(['active' => !$admin->active]);
        
        return response()->json([
            'success' => true,
            'message' => 'Admin status updated successfully',
            'active' => $admin->active
        ]);
    }

    /**
     * Assign roles to admin.
     */
    public function assignRoles(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $roles = Role::whereIn('id', $validated['roles'])->get();
        $admin->syncRoles($roles);

        return response()->json([
            'success' => true,
            'message' => 'Roles assigned successfully',
            'data' => $admin->load('roles')
        ]);
    }

    /**
     * AJAX store admin user.
     */
    public function storeAjax(Request $request)
    {
        return $this->store($request);
    }

    /**
     * AJAX update admin user.
     */
    public function updateAjax(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    /**
     * AJAX destroy admin user.
     */
    public function destroyAjax($id)
    {
        return $this->destroy($id);
    }
}
