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
            ['name' => 'dashboard.view', 'slug' => 'dashboard.view', 'description' => 'Access the main dashboard and overview statistics'],
            ['name' => 'profile.view', 'slug' => 'profile.view', 'description' => 'View own profile information and account details'],
            ['name' => 'profile.update', 'slug' => 'profile.update', 'description' => 'Edit own profile information and preferences'],
            ['name' => 'password.update', 'slug' => 'password.update', 'description' => 'Change own account password'],

            ['name' => 'users.view', 'slug' => 'users.view', 'description' => 'View the complete list of users in the system'],
            ['name' => 'users.view_details', 'slug' => 'users.view_details', 'description' => 'View detailed information for individual users'],
            ['name' => 'users.create', 'slug' => 'users.create', 'description' => 'Create new user accounts and send invitations'],
            ['name' => 'users.update', 'slug' => 'users.update', 'description' => 'Edit existing user accounts, roles, and details'],
            ['name' => 'users.delete', 'slug' => 'users.delete', 'description' => 'Permanently remove user accounts from the system'],
            ['name' => 'users.activate', 'slug' => 'users.activate', 'description' => 'Re-enable disabled user accounts so they can log in'],
            ['name' => 'users.deactivate', 'slug' => 'users.deactivate', 'description' => 'Temporarily disable user accounts without deleting them'],

            ['name' => 'roles.view', 'slug' => 'roles.view', 'description' => 'View the list of roles and their permission sets'],
            ['name' => 'roles.create', 'slug' => 'roles.create', 'description' => 'Create new roles to group permissions for users'],
            ['name' => 'roles.update', 'slug' => 'roles.update', 'description' => 'Edit existing roles and modify their permissions'],
            ['name' => 'roles.delete', 'slug' => 'roles.delete', 'description' => 'Delete roles that are no longer needed'],

            ['name' => 'permissions.view', 'slug' => 'permissions.view', 'description' => 'View all available permissions in the system'],
            ['name' => 'permissions.create', 'slug' => 'permissions.create', 'description' => 'Create new custom permissions for fine-grained access control'],
            ['name' => 'permissions.update', 'slug' => 'permissions.update', 'description' => 'Edit existing permissions and their descriptions'],
            ['name' => 'permissions.delete', 'slug' => 'permissions.delete', 'description' => 'Remove custom permissions from the system'],
            ['name' => 'permissions.assign', 'slug' => 'permissions.assign', 'description' => 'Assign permissions to roles when configuring access'],

            ['name' => 'tickets.view', 'slug' => 'tickets.view', 'description' => 'View all support tickets across all users'],
            ['name' => 'tickets.create', 'slug' => 'tickets.create', 'description' => 'Create new support tickets on behalf of users'],
            ['name' => 'tickets.update', 'slug' => 'tickets.update', 'description' => 'Edit ticket details such as subject and priority'],
            ['name' => 'tickets.reply', 'slug' => 'tickets.reply', 'description' => 'Reply to any support ticket as support staff'],
            ['name' => 'tickets.assign', 'slug' => 'tickets.assign', 'description' => 'Assign tickets to support staff or managers'],
            ['name' => 'tickets.change_status', 'slug' => 'tickets.change_status', 'description' => 'Update ticket status: Open, In Progress, Resolved, Closed'],
            ['name' => 'tickets.change_priority', 'slug' => 'tickets.change_priority', 'description' => 'Update ticket priority: Low, Medium, High, Urgent'],
            ['name' => 'tickets.resolve', 'slug' => 'tickets.resolve', 'description' => 'Mark tickets as resolved and notify the customer'],
            ['name' => 'tickets.close', 'slug' => 'tickets.close', 'description' => 'Close resolved tickets and end the support thread'],
            ['name' => 'tickets.reopen', 'slug' => 'tickets.reopen', 'description' => 'Reopen closed tickets if the issue persists'],
            ['name' => 'tickets.delete', 'slug' => 'tickets.delete', 'description' => 'Permanently delete support tickets from the system'],
            ['name' => 'tickets.view_own', 'slug' => 'tickets.view_own', 'description' => 'View only the tickets this user created'],
            ['name' => 'tickets.reply_own', 'slug' => 'tickets.reply_own', 'description' => 'Reply only to this user\'s own tickets, not others\''],
            ['name' => 'tickets.close_own', 'slug' => 'tickets.close_own', 'description' => 'Close only this user\'s own tickets'],

            ['name' => 'staff.view', 'slug' => 'staff.view', 'description' => 'View the list of support staff and their assignments'],
            ['name' => 'staff.manage', 'slug' => 'staff.manage', 'description' => 'Manage support staff accounts and assignments'],

            ['name' => 'notifications.view', 'slug' => 'notifications.view', 'description' => 'View system notifications and announcements'],
            ['name' => 'notifications.send', 'slug' => 'notifications.send', 'description' => 'Send notifications and announcements to users'],

            ['name' => 'audit_logs.view', 'slug' => 'audit_logs.view', 'description' => 'View system audit logs and activity history'],
            ['name' => 'audit_logs.export', 'slug' => 'audit_logs.export', 'description' => 'Export audit logs for external review or compliance'],

            ['name' => 'settings.view', 'slug' => 'settings.view', 'description' => 'View system configuration and application settings'],
            ['name' => 'settings.update', 'slug' => 'settings.update', 'description' => 'Modify system configuration and application settings'],

            ['name' => 'support.contact', 'slug' => 'support.contact', 'description' => 'Access the contact support form and submit requests'],
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
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_roles',
            'manage_roles',
            'manage_settings',
            'view_audit_logs',
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
            $managerPermissions = [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'password.update',
                'users.view',
                'users.view_details',
                'users.create',
                'users.update',
                'users.activate',
                'users.deactivate',
                'tickets.view',
                'tickets.create',
                'tickets.reply',
                'tickets.assign',
                'tickets.change_status',
                'tickets.change_priority',
                'tickets.resolve',
                'tickets.close',
                'tickets.reopen',
                'tickets.delete',
                'staff.view',
                'staff.manage',
                'notifications.view',
                'notifications.send',
                'audit_logs.view',
                'support.contact',
            ];
            $manager->permissions()->sync(
                Permission::whereIn('slug', $managerPermissions)->pluck('id')
            );
        }

        if ($supportStaff) {
            $supportStaffPermissions = [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'password.update',
                'users.view',
                'users.view_details',
                'tickets.view',
                'tickets.create',
                'tickets.reply',
                'tickets.assign',
                'tickets.change_status',
                'tickets.change_priority',
                'tickets.resolve',
                'tickets.close',
                'tickets.reopen',
                'notifications.view',
                'notifications.send',
                'support.contact',
            ];
            $supportStaff->permissions()->sync(
                Permission::whereIn('slug', $supportStaffPermissions)->pluck('id')
            );
        }

        if ($staff) {
            $staffPermissions = [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'password.update',
                'tickets.create',
                'tickets.view_own',
                'tickets.reply_own',
                'tickets.close_own',
                'support.contact',
            ];
            $staff->permissions()->sync(
                Permission::whereIn('slug', $staffPermissions)->pluck('id')
            );
        }

        if ($guest) {
            $guestPermissions = [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'tickets.create',
                'tickets.view_own',
                'tickets.reply_own',
                'support.contact',
            ];
            $guest->permissions()->sync(
                Permission::whereIn('slug', $guestPermissions)->pluck('id')
            );
        }
    }
}
