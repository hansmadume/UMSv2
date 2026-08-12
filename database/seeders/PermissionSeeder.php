<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Users', 'slug' => 'view_users', 'description' => 'View user list and details'],
            ['name' => 'Create Users', 'slug' => 'create_users', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'edit_users', 'description' => 'Edit existing users'],
            ['name' => 'Delete Users', 'slug' => 'delete_users', 'description' => 'Delete users'],
            ['name' => 'View Roles', 'slug' => 'view_roles', 'description' => 'View roles list'],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'description' => 'Create, edit, and delete roles'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Manage system settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $administrator = Role::where('name', 'Administrator')->first();
        $manager = Role::where('name', 'Manager')->first();
        $staff = Role::where('name', 'Staff')->first();
        $guest = Role::where('name', 'Guest')->first();

        if ($administrator) {
            $administrator->permissions()->sync(Permission::all()->pluck('id'));
        }

        if ($manager) {
            $managerPermissions = ['view_users', 'create_users', 'edit_users'];
            $manager->permissions()->sync(
                Permission::whereIn('slug', $managerPermissions)->pluck('id')
            );
        }

        if ($staff) {
            $staffPermissions = [];
            $staff->permissions()->sync(
                Permission::whereIn('slug', $staffPermissions)->pluck('id')
            );
        }

        if ($guest) {
            $guestPermissions = [];
            $guest->permissions()->sync(
                Permission::whereIn('slug', $guestPermissions)->pluck('id')
            );
        }
    }
}
