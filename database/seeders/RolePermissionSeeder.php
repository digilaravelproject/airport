<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Always clear cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Prefixes we care about
        $includePrefixes = [
            'permissions',
            'roles',
            'clients',
            'locations',
            'channels',
            'inventories',
            'inventory-packages',
            'packages',
            'utility',
            'reports',
            'help',
        ];

        // Gather all named routes
        $allRouteNames = collect(Route::getRoutes())
            ->map(fn ($r) => $r->getName())
            ->filter() // remove nulls
            ->unique()
            ->values();

        // Filter by prefix
        $permissionNames = $allRouteNames->filter(function ($name) use ($includePrefixes) {
            foreach ($includePrefixes as $prefix) {
                if (Str::startsWith($name, $prefix . '.')) {
                    return true;
                }
            }
            return in_array($name, $includePrefixes, true);
        })->values();

        // Create permissions
        foreach ($permissionNames as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
        }

        // Roles
        $admin   = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $client  = Role::firstOrCreate(['name' => 'Client']);

        // Admin gets all permissions
        $admin->syncPermissions(Permission::all());

        // Manager gets CRUD except destroy + ping/reboot
        $managerPerms = $permissionNames->filter(function ($name) {
            // Allow everything except destroy
            return !Str::endsWith($name, '.destroy');
        })->values();

        $manager->syncPermissions(Permission::whereIn('name', $managerPerms)->get());

        // Client only gets reports.*
        $clientPerms = $permissionNames->filter(fn($name) => Str::startsWith($name, 'reports.'));
        $client->syncPermissions(Permission::whereIn('name', $clientPerms)->get());

        // Rebuild cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
