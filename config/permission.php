<?php

use App\Models\Permission;
use App\Models\Role;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Guard
    |--------------------------------------------------------------------------
    |
    | The default guard to use for authentication and permission checks.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The models used by the package.
    |
    */

    'models' => [
        'permission' => Permission::class,
        'role' => Role::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | The database tables used by the package.
    |
    */

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Keys
    |--------------------------------------------------------------------------
    |
    | The keys used to cache permission and role checks.
    |
    */

    'cache' => [
        'expiration_time' => DateInterval::createFromDateString('24 hours'),

        'key' => 'spatie.permission.cache',

        'model_key' => 'name',

        'store' => 'array',
    ],

];
