<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'status',
        'guard_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAdministrator(): bool
    {
        return $this->name === 'Administrator';
    }

    public function isManager(): bool
    {
        return $this->name === 'Manager';
    }

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
