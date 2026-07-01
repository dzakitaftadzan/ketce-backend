<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    // Ini wajib ada agar mass assignment (pengisian data) diizinkan
    protected $fillable = ['user_id', 'address_line', 'city'];

    // Menghubungkan alamat ke user (satu alamat milik satu user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}