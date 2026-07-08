<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ean_code',
        'private_code',
        'size_type',
        'department_id',
        'category_id',
        'price_usd',
        'image',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_usd' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Generate the next private code based on settings
     */
    public static function generatePrivateCode(): string
    {
        $mode = Setting::get('private_code_mode', 'incremental');
        $start = (int) Setting::get('private_code_start', '1');

        $lastProduct = static::orderByRaw('CAST(private_code AS UNSIGNED) DESC')->first();
        $lastCode = $lastProduct ? (int) $lastProduct->private_code : $start - 1;

        $nextCode = max($lastCode + 1, $start);

        return (string) $nextCode;
    }
}
