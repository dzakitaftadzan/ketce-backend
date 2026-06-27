<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_variant_id', 'product_name', 'variant_info', 'quantity', 'price'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}