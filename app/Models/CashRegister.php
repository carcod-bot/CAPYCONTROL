<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = ['number', 'name', 'location', 'hostname', 'ip_address', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function sessions()
    {
        return $this->hasMany(CashSession::class);
    }

    /**
     * Get the current active session (if any)
     */
    public function activeSession()
    {
        return $this->hasOne(CashSession::class)->where('status', 'open')->latestOfMany();
    }

    /**
     * Check if this register has an open session
     */
    public function isOpen(): bool
    {
        return $this->activeSession()->exists();
    }
}
