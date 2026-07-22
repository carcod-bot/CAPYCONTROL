<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'currency_id', 'code', 'description', 'value', 'max_change_amount', 'min_purchase_amount',
        'is_real_denomination', 'allows_change', 'used_in_pos', 'electronic_verification',
        'cash_advance', 'admin_serial', 'auto_declare', 'auto_deposit', 'used_in_admin_billing',
        'is_credit'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function promotions()
    {
        return $this->morphMany(Promotion::class, 'promotable');
    }
}
