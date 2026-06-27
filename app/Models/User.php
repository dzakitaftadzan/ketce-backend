<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'is_active', 'login_attempts', 'locked_until'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'locked_until' => 'datetime',
    ];

    // Relasi
    public function orders() { return $this->hasMany(Order::class); }
    public function addresses() { return $this->hasMany(Address::class); }
    public function carts() { return $this->hasMany(Cart::class); }
    public function deliveries() { return $this->hasMany(Delivery::class, 'courier_id'); }

    // Helper Method Cek Role
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function isCourier(): bool { return $this->role === 'kurir'; }

    // Cek apakah akun sedang dikunci
    public function isLocked(): bool
    {
        return $this->locked_until && Carbon::now()->lessThan($this->locked_until);
    }
}