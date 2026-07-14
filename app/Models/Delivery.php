<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'order_id', 
        'courier_id', 
        'status', 
        'delivery_code',
    ];

    // Relasi balik ke Order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi ke Courier (User)
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}