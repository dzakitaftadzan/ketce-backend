<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'order_id', 'courier_id', 'status', 'notes', 'picked_up_at', 'delivered_at'
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function courier() { return $this->belongsTo(User::class, 'courier_id'); }
    public function logs() { return $this->hasMany(DeliveryLog::class); }
}