<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'document_id',
        'phone',
        'email',
        'address',
        'credit_limit',
        'current_balance',
        'credit_status',
        'credit_level_id',
        'total_purchases',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creditAccounts()
    {
        return $this->hasMany(CreditAccount::class);
    }

    public function creditPayments()
    {
        return $this->hasMany(CreditPayment::class);
    }

    /**
     * Check if customer has enough available credit
     */
    public function hasAvailableCredit($amount)
    {
        if ($this->credit_status !== 'active') {
            return false;
        }
        
        $available = $this->credit_limit - $this->current_balance;
        return $available >= $amount;
    }

    /**
     * Add debt to current balance
     */
    public function addDebt($amount)
    {
        $this->current_balance += $amount;
        $this->save();
    }

    /**
     * Reduce debt from current balance
     */
    public function reduceDebt($amount)
    {
        $this->current_balance -= $amount;
        if ($this->current_balance < 0) {
            $this->current_balance = 0;
        }
        $this->save();
    }

    public function creditLevel()
    {
        return $this->belongsTo(CreditLevel::class);
    }

    /**
     * Auto-update credit level based on total purchases
     */
    public function updateCreditLevel()
    {
        $bestLevel = \App\Models\CreditLevel::where('required_purchases', '<=', $this->total_purchases)
            ->orderBy('required_purchases', 'desc')
            ->first();
            
        if ($bestLevel && $this->credit_level_id !== $bestLevel->id) {
            $this->credit_level_id = $bestLevel->id;
            
            if ($bestLevel->limit_increase_percentage > 0) {
                $increaseFactor = $bestLevel->limit_increase_percentage / 100;
                $this->credit_limit += ($this->credit_limit * $increaseFactor);
            }
            
            $this->save();
        }
    }
}
