<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditInstallment extends Model
{
    protected $fillable = [
        'credit_account_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function creditAccount()
    {
        return $this->belongsTo(CreditAccount::class);
    }
}
