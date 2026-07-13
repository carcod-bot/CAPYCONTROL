<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    protected $fillable = [
        'cash_register_id', 'user_id', 'status', 'turn_number',
        'opening_amount', 'expected_amount', 'actual_amount', 'difference',
        'total_sales', 'total_returns', 'total_withdrawals', 'pending_invoices',
        'opened_at', 'closed_at', 'closing_notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'difference' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Check if session is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Get total withdrawals amount
     */
    public function totalWithdrawalsAmount(): float
    {
        return $this->movements()->where('type', 'withdrawal')->sum('amount');
    }

    /**
     * Get total deposits amount
     */
    public function totalDepositsAmount(): float
    {
        return $this->movements()->where('type', 'deposit')->sum('amount');
    }

    /**
     * Close this session
     */
    public function closeSession(?float $actualAmount = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'actual_amount' => $actualAmount,
            'difference' => $actualAmount !== null ? $actualAmount - $this->expected_amount : null,
            'closing_notes' => $notes,
        ]);
    }
}
