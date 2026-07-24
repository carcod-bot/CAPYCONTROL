<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteParkedSale extends Model
{
    protected $fillable = [
        'cash_register_id',
        'customer_id',
        'customer_name',
        'items',
        'total',
        'status'
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}
