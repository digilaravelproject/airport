<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        // Get all roles including Admin, Manager, Client
        $roles = Role::all();
        $permissions = Permission::orderBy('name')->get();
        return view('permissions.index', compact('roles', 'permissions'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array'
        ]);

        $role = Role::findById($request->role_id);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->back()->with('success', "Permissions updated for {$role->name}.");
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'New permission created successfully.');
    }
}
