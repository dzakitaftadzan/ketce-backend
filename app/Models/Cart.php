<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = ['user_id', 'product_variant_id', 'quantity'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}