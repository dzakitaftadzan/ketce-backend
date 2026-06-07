<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryLog extends Model
{
    public $timestamps = false; 

    protected $fillable = [
        'delivery_id', 'status', 'description', 'created_by'
    ];

    public function delivery() { return $this->belongsTo(Delivery::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}