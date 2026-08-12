<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles', 'role');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($roleId = $request->input('role_id')) {
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $roles = Role::where('status', 'active')->orderBy('name')->get();

        return inertia('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'status', 'role_id']),
        ]);
    }

    public function create()
    {
        $roles = Role::where('status', 'active')->orderBy('name')->get();
        return inertia('Users/Create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'full_name' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'full_name' => $validated['full_name'],
            'name' => $validated['full_name'],
            'password_hash' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'contact_number' => $validated['contact_number'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user->assignRole($role);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'User Created',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles');
        return inertia('Users/Show', ['user' => $user]);
    }

    public function edit(User $user)
    {
        $roles = Role::where('status', 'active')->orderBy('name')->get();
        return inertia('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'full_name' => ['required', 'string', 'max:150'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->full_name = $validated['full_name'];
        $user->name = $validated['full_name'];
        $user->status = $validated['status'];
        $user->contact_number = $validated['contact_number'] ?? null;
        $user->address = $validated['address'] ?? null;

        if (!empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        $user->save();

        $role = Role::findOrFail($validated['role_id']);
        $user->syncRoles([$role]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'User Updated',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $user->status = 'inactive';
        $user->deleted_at = now();
        $user->save();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->getDisplayName(),
            'action' => 'User Deleted',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}