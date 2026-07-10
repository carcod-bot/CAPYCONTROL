<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_number',
        'provider_id',
        'brand_id',
        'expiry_date',
        'initial_quantity',
        'current_quantity',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'initial_quantity' => 'decimal:3',
        'current_quantity' => 'decimal:3',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function inventoryAdjustments()
    {
        return $this->belongsToMany(InventoryAdjustment::class, 'inventory_adjustment_batch')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
