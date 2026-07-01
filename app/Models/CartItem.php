<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'product_name', 'quantity', 'price'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}