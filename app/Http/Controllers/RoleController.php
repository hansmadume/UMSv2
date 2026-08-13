<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users', 'permissions');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $roles = $query->orderBy('name')->paginate(10)->withQueryString();

        return inertia('Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return inertia('Roles/Create', ['permissions' => $permissions]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:64'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'status' => $validated['status'],
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'Role Created',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');

        return inertia('Roles/Show', ['role' => $role]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $role->load('permissions');
        $assignedPermissions = $role->permissions->pluck('id')->toArray();

        return inertia('Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'assignedPermissions' => $assignedPermissions,
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:64'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'status' => $validated['status'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'Role Updated',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Request $request, Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete role because it is assigned to users.');
        }

        $role->permissions()->detach();
        $role->delete();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'Role Deleted',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
