<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $web_roles = [
            'artisan',
            'providers',
            'co-operate_accounts',
            'private_accounts',
            'affiliates',
            'suppliers',
            'department'
        ];

        $admin_roles = [
            'super_admin',
            'staff',
            'admin',
            'manager'
        ];

        foreach ($web_roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        foreach ($admin_roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'admin']);
        }
    }
}
