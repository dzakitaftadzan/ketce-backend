<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'order_code', 'subtotal', 'shipping_cost', 'total', 'payment_status', 'order_status', 'payment_proof', 'notes'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function address() { return $this->belongsTo(Address::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function delivery() { return $this->hasOne(Delivery::class); }

    // Scope untuk filter status pesanan
    public function scopeByStatus($query, $status)
    {
        return $query->where('order_status', $status);
    }

    // Cek apakah pesanan masih bisa dibatalkan customer
    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->order_status, ['pending', 'confirmed']);
    }
}