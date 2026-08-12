<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'full_name',
        'status',
        'contact_number',
        'address',
        'profile_photo',
        'last_login',
        'role_id',
        'name',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'last_login' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password_hash'] = Hash::make($value);
    }

    public function setPasswordHashAttribute(string $value): void
    {
        if (str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$') || str_starts_with($value, '$2b$')) {
            $this->attributes['password_hash'] = $value;
        } else {
            $this->attributes['password_hash'] = Hash::make($value);
        }
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getDisplayName(): string
    {
        return $this->full_name ?? $this->username ?? $this->email ?? 'User';
    }
}
