<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'guard_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public $timestamps = true;
}
