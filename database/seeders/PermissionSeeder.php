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
            ['name' => 'view_users', 'slug' => 'view_users', 'description' => 'View user list and details'],
            ['name' => 'create_users', 'slug' => 'create_users', 'description' => 'Create new users'],
            ['name' => 'edit_users', 'slug' => 'edit_users', 'description' => 'Edit existing users'],
            ['name' => 'delete_users', 'slug' => 'delete_users', 'description' => 'Delete users'],
            ['name' => 'view_roles', 'slug' => 'view_roles', 'description' => 'View roles list'],
            ['name' => 'manage_roles', 'slug' => 'manage_roles', 'description' => 'Create, edit, and delete roles'],
            ['name' => 'manage_settings', 'slug' => 'manage_settings', 'description' => 'Manage system settings'],
            ['name' => 'view_audit_logs', 'slug' => 'view_audit_logs', 'description' => 'View audit logs'],
            ['name' => 'tickets.view', 'slug' => 'tickets.view', 'description' => 'View support tickets'],
            ['name' => 'tickets.create', 'slug' => 'tickets.create', 'description' => 'Create support tickets'],
            ['name' => 'tickets.update', 'slug' => 'tickets.update', 'description' => 'Update support tickets'],
            ['name' => 'tickets.reply', 'slug' => 'tickets.reply', 'description' => 'Reply to support tickets'],
            ['name' => 'tickets.assign', 'slug' => 'tickets.assign', 'description' => 'Assign tickets to support staff'],
            ['name' => 'tickets.change_status', 'slug' => 'tickets.change_status', 'description' => 'Update ticket status'],
            ['name' => 'tickets.change_priority', 'slug' => 'tickets.change_priority', 'description' => 'Update ticket priority'],
            ['name' => 'tickets.resolve', 'slug' => 'tickets.resolve', 'description' => 'Mark tickets as resolved'],
            ['name' => 'tickets.close', 'slug' => 'tickets.close', 'description' => 'Close tickets'],
            ['name' => 'tickets.reopen', 'slug' => 'tickets.reopen', 'description' => 'Reopen closed tickets'],
            ['name' => 'tickets.delete', 'slug' => 'tickets.delete', 'description' => 'Delete support tickets'],
        ];

        $oldSlugs = [
            'view_own_tickets',
            'create_support_ticket',
            'reply_to_tickets',
            'close_own_tickets',
            'view_all_tickets',
            'assign_tickets',
            'change_ticket_status',
            'change_ticket_priority',
            'view_ticket_details',
            'add_internal_notes',
            'upload_attachments',
            'resolve_tickets',
            'close_tickets',
            'reopen_tickets',
        ];

        Permission::whereIn('slug', $oldSlugs)->delete();

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        Permission::whereNotIn('slug', array_column($permissions, 'slug'))->delete();

        $administrator = Role::where('name', 'Administrator')->first();
        $manager = Role::where('name', 'Manager')->first();
        $staff = Role::where('name', 'Staff')->first();
        $guest = Role::where('name', 'Guest')->first();
        $supportStaff = Role::firstOrCreate(
            ['name' => 'Support Staff'],
            ['description' => 'Support staff with limited user management and support ticket access']
        );

        if ($administrator) {
            $administrator->permissions()->sync(Permission::all()->pluck('id'));
        }

        if ($manager) {
            $managerPermissions = ['view_users', 'create_users', 'edit_users'];
            $manager->permissions()->sync(
                Permission::whereIn('slug', $managerPermissions)->pluck('id')
            );
        }

        if ($supportStaff) {
            $supportStaffPermissions = [
                'tickets.view',
                'tickets.create',
                'tickets.update',
                'tickets.reply',
                'tickets.assign',
                'tickets.change_status',
                'tickets.change_priority',
                'tickets.resolve',
                'tickets.close',
                'tickets.reopen',
            ];
            $supportStaff->permissions()->sync(
                Permission::whereIn('slug', $supportStaffPermissions)->pluck('id')
            );
        }

        if ($staff) {
            $staffPermissions = ['tickets.view', 'tickets.create', 'tickets.reply', 'tickets.close'];
            $staff->permissions()->sync(
                Permission::whereIn('slug', $staffPermissions)->pluck('id')
            );
        }

        if ($guest) {
            $guestPermissions = ['tickets.view', 'tickets.create', 'tickets.reply', 'tickets.close'];
            $guest->permissions()->sync(
                Permission::whereIn('slug', $guestPermissions)->pluck('id')
            );
        }
    }
}
