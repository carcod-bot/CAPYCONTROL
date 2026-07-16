<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'promotable_type',
        'promotable_id',
        'start_date',
        'end_date',
        'active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function promotable()
    {
        return $this->morphTo();
    }
}
