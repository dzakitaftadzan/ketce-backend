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
        'tracking_number'
    ];

    // Relasi balik ke Order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}