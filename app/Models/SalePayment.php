<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_method_id',
        'currency_id',
        'payment_method_name',
        'amount_base',
        'amount_local',
        'exchange_rate',
        'reference'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
