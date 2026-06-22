<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'category', 'base_price', 'image', 'is_active'
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);
        });
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Accessor untuk format Rupiah
    public function getPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price, 0, ',', '.');
    }
}