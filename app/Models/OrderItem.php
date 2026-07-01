<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // Pastikan 'order_id', 'product_id', 'quantity', 'price' sudah sesuai dengan kolom di database
    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'price'
    ];

    /**
     * Relasi ke Order (Setiap item milik satu order)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke Product (Opsional: jika ingin mengakses detail produk dari item)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}