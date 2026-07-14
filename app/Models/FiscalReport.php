<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_event_id',
        'report_type',
        'report_number',
        'raw_data'
    ];

    public function posEvent()
    {
        return $this->belongsTo(PosEvent::class);
    }
}
