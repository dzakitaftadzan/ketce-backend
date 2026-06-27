<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = ['user_id', 'label', 'recipient_name', 'phone', 'full_address', 'city', 'province', 'postal_code', 'is_default'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}