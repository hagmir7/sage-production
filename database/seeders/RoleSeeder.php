<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'view users',
            'edit users',
            'delete users',
            'create posts',
            'edit posts',
            'delete posts',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles with permissions
        $rolesPermissions = [
            'admin' => [
                'view users',
                'edit users',
                'delete users',
                'create posts',
                'edit posts',
                'delete posts',
            ],
            'editor' => [
                'create posts',
                'edit posts',
            ],
            'viewer' => [
                'view users',
            ],
        ];

        // Create roles and assign permissions
        foreach ($rolesPermissions as $role => $perms) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions($perms);
        }
    }
}
