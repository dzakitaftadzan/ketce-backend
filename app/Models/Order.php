<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Tambahkan semua field yang akan diupdate ke dalam $fillable
    protected $fillable = [
        'order_status',
        'payment_status',
        // Tambahkan field lain jika ada (misal: total_price, etc)
    ];

    // Relasi yang mungkin dibutuhkan
    public function orderItems() {
        return $this->hasMany(OrderItem::class);
    }
}