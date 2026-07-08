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
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
