<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'vehicle_type',
        'is_active',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Tambahkan ini untuk nilai default jika field tidak diisi
    protected $attributes = [
        'role' => 'customer', // Default role adalah customer
        'is_active' => true,  // Default user aktif
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}