<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'role',
        'role_id',
        'permissions',
        'dark_mode',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'    => 'hashed',
            'permissions' => 'array',
            'dark_mode'   => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // ── Permission Helpers ─────────────────────────────────────────────────

    /**
     * Get all effective permissions: role permissions + user extra permissions.
     */
    public function effectivePermissions(): array
    {
        if ($this->isAdmin()) {
            return \App\Http\Controllers\UserController::ALL_PERMISSIONS;
        }

        $rolePerms  = $this->roleModel ? ($this->roleModel->permissions ?? []) : [];
        $extraPerms = $this->permissions ?? [];

        return array_values(array_unique(array_merge($rolePerms, $extraPerms)));
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        return in_array($permission, $this->effectivePermissions());
    }

    /**
     * Legacy helper — still works everywhere.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
