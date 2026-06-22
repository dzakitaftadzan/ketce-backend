<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // 1. Pastikan semua kolom ini diizinkan untuk diisi (termasuk category, base_price, dan image)
    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'category', 
        'base_price', 
        'image', 
        'is_active'
    ];

    // 2. 💡 INI YANG PALING PENTING: Ubah teks biasa menjadi Array/JSON
    protected $casts = [
        'image' => 'array',
        'is_active' => 'boolean',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Jika kamu punya relasi lain, biarkan saja di bawah ini...
}