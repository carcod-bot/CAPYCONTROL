<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code', 'description', 'symbol', 'max_decimals', 'is_default',
        'is_active', 'exchange_rate', 'iso_code', 'observation', 'used_in_pos'
    ];

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
