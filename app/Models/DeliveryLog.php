<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLog extends Model
{
    protected $fillable = ['delivery_id', 'status', 'description', 'created_by'];
    public $timestamps = false;

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
