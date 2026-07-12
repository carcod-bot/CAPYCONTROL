<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $fillable = [
        'cash_session_id', 'user_id', 'type', 'amount', 'reason', 'notes', 'payment_method_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the type label in Spanish
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'withdrawal' => 'Retiro',
            'deposit' => 'Depósito',
            'adjustment' => 'Ajuste',
            default => $this->type,
        };
    }
}
