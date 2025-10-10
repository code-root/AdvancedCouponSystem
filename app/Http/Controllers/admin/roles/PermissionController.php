<?php

namespace App\Http\Controllers\admin\roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-permissions')->only(['index']);
    }

    public function index(Request $request)
    {
        $permissions = Permission::withCount('roles')->orderBy('name')->get();
        return view('admin.permissions.index', compact('permissions'));
    }
}


