<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'description', 'permissions', 'is_system'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system'   => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }
}
