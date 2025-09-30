<?php

namespace Database\Seeders; // <── this line is required

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view reports',
            'manage inventory',
            'manage channels',
            'manage packages',
            'manage allocations',
            'manage subscribers',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $client = Role::firstOrCreate(['name' => 'Client']);

        // Admin has all permissions
        $admin->syncPermissions(Permission::all());

        // Manager limited
        $manager->syncPermissions(['manage inventory', 'manage channels']);

        // Client only view
        $client->syncPermissions(['view reports']);
    }
}
