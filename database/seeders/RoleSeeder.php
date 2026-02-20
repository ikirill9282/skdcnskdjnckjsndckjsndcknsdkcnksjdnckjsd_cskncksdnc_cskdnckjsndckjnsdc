<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'super-admin',
            'admin',
            'company-admin',
            'manager',
            'client',
            'user',
        ] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
