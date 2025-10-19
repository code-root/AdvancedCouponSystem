<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleManagementController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        // Get permissions grouped by resource
        $permissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();
        $permissionsData = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);
            $group = $parts[0] ?? 'other';
            $permissionsData[$group][] = $permission;
        }

        // Get all roles with users count
        $roles = Role::where('guard_name', 'admin')->withCount('users')->orderBy('id', 'DESC')->get();

        // Get statistics
        $stats = [
            'total_roles' => Role::where('guard_name', 'admin')->count(),
            'total_permissions' => Permission::where('guard_name', 'admin')->count(),
            'users_with_roles' => User::whereHas('roles')->count(),
            'admin_users' => \App\Models\Admin::count(),
        ];

        return view('admin.roles.index', compact('roles', 'permissionsData', 'stats'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        // Get permissions grouped by resource
        $permissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();
        $permissionsData = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);
            $group = $parts[0] ?? 'other';
            $permissionsData[$group][] = $permission;
        }
        
        return view('admin.roles.create', compact('permissionsData'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
            'guard_name' => 'admin'
        ]);

        if (!empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role->load('permissions')
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully');
    }

    /**
     * AJAX store role.
     */
    public function storeAjax(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit($id)
    {
        $role = Role::where('guard_name', 'admin')->findOrFail($id);
        
        // Get permissions grouped by resource
        $permissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();
        $permissionsData = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);
            $group = $parts[0] ?? 'other';
            $permissionsData[$group][] = $permission;
        }

        return view('admin.roles.edit', compact('role', 'permissionsData'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::where('guard_name', 'admin')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
        ]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role->load('permissions')
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully');
    }

    /**
     * AJAX update role.
     */
    public function updateAjax(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $role = Role::where('guard_name', 'admin')->findOrFail($id);
        
        // Prevent deletion of super-admin role
        if ($role->name === 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super-admin role'
            ], 403);
        }
        
        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role that has assigned users'
            ], 403);
        }
        
        $role->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * AJAX destroy role.
     */
    public function destroyAjax($id)
    {
        return $this->destroy($id);
    }

    /**
     * Assign permissions to role via AJAX.
     */
    public function assignPermissionsAjax(Request $request, $id)
    {
        $role = Role::where('guard_name', 'admin')->findOrFail($id);
        
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = Permission::whereIn('id', $validated['permissions'])->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'data' => $role->load('permissions')
        ]);
    }

    /**
     * Clone role.
     */
    public function cloneRole(Request $request, $id)
    {
        $originalRole = Role::where('guard_name', 'admin')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
        ]);

        $newRole = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? $originalRole->display_name,
            'description' => $originalRole->description,
            'guard_name' => 'admin'
        ]);

        // Copy permissions
        $newRole->syncPermissions($originalRole->permissions);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role cloned successfully',
                'data' => $newRole->load('permissions')
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role cloned successfully');
    }

    /**
     * Export roles.
     */
    public function exportRoles()
    {
        $roles = Role::where('guard_name', 'admin')->with('permissions')->get();
        
        $data = $roles->map(function ($role) {
            return [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->implode(', '),
                'users_count' => $role->users()->count(),
                'created_at' => $role->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Import roles.
     */
    public function importRoles(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,csv|max:2048'
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        
        $imported = 0;
        $errors = [];

        try {
            if ($extension === 'json') {
                $data = json_decode(file_get_contents($file->getPathname()), true);
            } else {
                // Handle CSV import
                $data = array_map('str_getcsv', file($file->getPathname()));
                $header = array_shift($data);
                $data = array_map(function($row) use ($header) {
                    return array_combine($header, $row);
                }, $data);
            }

            foreach ($data as $roleData) {
                try {
                    $role = Role::create([
                        'name' => $roleData['name'],
                        'display_name' => $roleData['display_name'] ?? null,
                        'description' => $roleData['description'] ?? null,
                        'guard_name' => 'admin'
                    ]);

                    // Import permissions if provided
                    if (isset($roleData['permissions']) && is_array($roleData['permissions'])) {
                        $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
                        $role->syncPermissions($permissions);
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'role' => $roleData['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process import file: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} roles successfully",
            'imported' => $imported,
            'errors' => $errors
        ]);
    }
}
