<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditLevel extends Model
{
    protected $fillable = [
        'name',
        'required_purchases',
        'down_payment_type',
        'down_payment_value',
        'installments_count',
        'payment_frequency',
        'limit_increase_percentage',
    ];

    protected $casts = [
        'down_payment_value' => 'decimal:2',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
