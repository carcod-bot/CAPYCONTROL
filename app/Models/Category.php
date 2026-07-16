<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'active', 'department_id'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function promotions()
    {
        return $this->morphMany(Promotion::class, 'promotable');
    }
}
