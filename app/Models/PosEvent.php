<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosEvent extends Model
{
    protected $fillable = [
        'cash_session_id',
        'user_id',
        'supervisor_username',
        'event_type',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
