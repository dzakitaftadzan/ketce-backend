<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
    'user_id', 'label', 'recipient_name', 'street', 'city', 'province', 'postal_code', 'is_default'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}