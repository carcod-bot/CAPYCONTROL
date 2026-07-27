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
        'payment_cash_session_id',
        'payment_user_id',
        'payment_method_id',
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

    public function paymentSession()
    {
        return $this->belongsTo(CashSession::class, 'payment_cash_session_id');
    }

    public function paymentUser()
    {
        return $this->belongsTo(User::class, 'payment_user_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
