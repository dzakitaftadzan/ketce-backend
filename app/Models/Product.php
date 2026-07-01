<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory; // Penting untuk testing dan seeder

    // Menentukan kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'name', 
        'price', 
        'stock',
        'description', // Tambahkan jika ada
        'image_url'    // Tambahkan jika ada
    ];

    // Mengatur format data (Casting) agar perhitungan harga akurat
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    /**
     * Relasi ke OrderItem
     * Produk bisa berada di banyak item pesanan.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}