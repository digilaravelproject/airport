<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionResetSeeder extends Seeder
{
    public function run(): void
    {
        // IMPORTANT: clear cached permissions first
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Use DB transaction for consistency
        DB::transaction(function () {
            // Disable FKs to truncate safely (MySQL)
            Schema::disableForeignKeyConstraints();

            // Truncate pivot tables first, then permissions
            DB::table('role_has_permissions')->truncate();
            DB::table('model_has_permissions')->truncate();
            DB::table('permissions')->truncate();

            Schema::enableForeignKeyConstraints();

            // Insert ONLY these permissions
            $guard = config('auth.defaults.guard', 'web');

            $names = [
                'manage subscriber',
                'manage inventory',
                'manage channels',
                'manage package',
                'manage allocations',   // change to 'aalocation' if that's what you really want
                'manage utilities',
                'manage reports',
                'manage users',
                'manage permission',
                'helps',
            ];

            foreach ($names as $name) {
                Permission::create([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
            }
        });

        // Clear permission cache again after changes
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
