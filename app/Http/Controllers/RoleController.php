<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Use the same guard everywhere (default: web)
        $guard = config('auth.defaults.guard', 'web');

        // Only show permissions for this guard
        $permissions = Permission::where('guard_name', $guard)
            ->orderBy('name')
            ->get();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $guard = config('auth.defaults.guard', 'web');

        $validated = $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name',
            // We submit names, not IDs:
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => $guard,
        ]);

        if (!empty($validated['permissions'])) {
            // Sync by NAME (matches what the form sends)
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted.');
    }
}
