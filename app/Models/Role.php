<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get users with this role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Get permissions assigned to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * Check if role is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if this is the Administrator role.
     */
    public function isAdministrator(): bool
    {
        return $this->name === 'Administrator';
    }

    /**
     * Check if this is the Manager role.
     */
    public function isManager(): bool
    {
        return $this->name === 'Manager';
    }

    /**
     * Get role key (lowercase normalized).
     */
    public function getKey(): string
    {
        $normalized = strtolower(trim($this->name ?? 'guest'));

        if (in_array($normalized, ['admin', 'administrator', 'super admin'], true)) {
            return 'administrator';
        }

        if ($normalized === 'manager') {
            return 'manager';
        }

        if ($normalized === 'staff') {
            return 'staff';
        }

        return 'guest';
    }
}