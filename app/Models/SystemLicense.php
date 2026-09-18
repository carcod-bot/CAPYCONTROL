<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLicense extends Model
{
    use HasFactory;

    protected $table = 'system_license';

    protected $fillable = [
        'system_id',
        'license_type',
        'start_date',
        'end_date',
        'license_key',
        'signature',
    ];
}
