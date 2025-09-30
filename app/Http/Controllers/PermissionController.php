<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        // Get all roles except Admin
        $roles = Role::where('name', '!=', 'Admin')->get();
        $permissions = Permission::all();
        return view('permissions.index', compact('roles', 'permissions'));
    }

    public function update(Request $request)
    {
        $role = Role::findById($request->role_id); // Or Role::find($request->role_id);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->back()->with('success', 'Permissions updated successfully.');
    }
}
