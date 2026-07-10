<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sale extends Model
{
    protected $fillable = [
        'cash_session_id',
        'user_id',
        'customer_id',
        'payment_method',
        'total_amount',
        'tax_amount',
        'tendered_amount',
        'change_amount',
        'status',
        'ticket_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'tendered_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public static function generateTicketNumber()
    {
        $prefix = 'TKT-';
        $lastSale = self::latest('id')->first();
        if (!$lastSale) {
            return $prefix . '00000001';
        }

        $lastNumber = intval(str_replace($prefix, '', $lastSale->ticket_number));
        return $prefix . str_pad($lastNumber + 1, 8, '0', STR_PAD_LEFT);
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
