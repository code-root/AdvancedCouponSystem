<?php

namespace App\Http\Controllers\admin\roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        // Middleware is now handled in routes or individual methods
        // This constructor can be used for other initialization if needed
    }

    public function index(Request $request)
    {
        $permissions = Permission::withCount('roles')->orderBy('name')->get();
        return view('admin.permissions.index', compact('permissions'));
    }
}


