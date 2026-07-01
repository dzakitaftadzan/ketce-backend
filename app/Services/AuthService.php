<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $data)
    {
        $loginType = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::where($loginType, $data['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Akun tidak ditemukan.'],
            ]);
        }
        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Data login tidak cocok.'],
            ]);
        }

        return [
            'token' => $user->createToken($data['device_name'])->plainTextToken,
            'user'  => $user,
            'role'  => $user->role,
        ];
    }

    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // Jangan di-hash manual, model sudah menggunakan 'hashed' cast
            'role' => $data['role'] ?? 'customer',
            'phone' => $data['phone'] ?? null,
        ]);

        return $user;
    }

    public function googleLogin(array $data)
    {
        $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID', 'placeholder')]);
        $payload = $client->verifyIdToken($data['credential']);

        if (!$payload) {
            throw ValidationException::withMessages([
                'credential' => ['Token Google tidak valid atau sudah kadaluarsa.'],
            ]);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $payload['email'])->first();

        if ($user) {
            // Update google_id jika belum ada
            if (!$user->google_id) {
                $user->update(['google_id' => $payload['sub']]);
            }
        } else {
            // Register user baru
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'google_id' => $payload['sub'],
                'role' => 'customer',
            ]);
        }

        return [
            'token' => $user->createToken($data['device_name'])->plainTextToken,
            'user'  => $user,
            'role'  => $user->role,
        ];
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }
}
