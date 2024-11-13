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
            Role::insertOrIgnore(['name' => $role, 'guard_name' => 'web']);
        }

        foreach ($admin_roles as $role) {
            Role::insertOrIgnore(['name' => $role, 'guard_name' => 'admin']);
        }

        // Get all model files from app/Models directory
        $models = glob(app_path('Models') . '/*.php');
        $allPermissions = collect();

        foreach ($models as $model) {
            // Get model name without path and .php extension
            $modelName = strtolower(basename($model, '.php'));
            
            // Generate CRUD permissions for each model
            $permissions = [
                'create_' . $modelName,
                'read_' . $modelName,
                'update_' . $modelName,
                'delete_' . $modelName,
                'list_' . $modelName,
            ];

            foreach ($permissions as $permission) {
                Permission::insertOrIgnore([
                    'name' => $permission,
                    'guard_name' => 'admin'
                ]);
                Permission::insertOrIgnore([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
                $allPermissions->push($permission);
            }
        }

        // Assign all permissions to super admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdminRole->syncPermissions($allPermissions);
    }
}
