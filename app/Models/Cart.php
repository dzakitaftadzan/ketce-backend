<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    // Pastikan fillable mencakup user_id
    protected $fillable = ['user_id'];

    // Relasi ke CartItem (karena kamu menggunakan CartItem)
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}