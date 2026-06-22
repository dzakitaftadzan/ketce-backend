<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse; // Menggunakan trait format JSON standar kita

    /**
     * Fitur Registrasi Customer
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($request->password);
        $validated['role'] = 'customer'; // Default registrasi mandiri sebagai customer

        $user = User::create($validated);
        
        // Buat token akses otomatis setelah register sukses
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Registrasi akun berhasil', 21);
    }

    /**
     * Fitur Login dengan Proteksi Pembatasan Akses (Rate Limiting)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Membuat kunci unik berdasarkan email dan IP user
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // Cek apakah user terlalu banyak mencoba login (maks 5 kali gagal)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return $this->errorResponse(
                "Terlalu banyak percobaan login. Akun Anda ditangguhkan sementara. Silakan coba lagi dalam {$minutes} menit.",
                429
            );
        }

        $user = User::where('email', $request->email)->first();

        // Validasi kecocokan email dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Hitung satu kali kegagalan login, kunci akun selama 15 menit (900 detik) jika sudah 5 kali gagal
            RateLimiter::hit($throttleKey, 900);

            return $this->errorResponse('Email atau password yang Anda masukkan salah.', 401);
        }

        // Jika berhasil login, hapus riwayat kegagalan percobaan login
        RateLimiter::clear($throttleKey);

        // Hapus token lama agar 1 user hanya aktif di 1 perangkat/sesi (opsional, demi keamanan)
        $user->tokens()->delete();
        
        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Login berhasil');
    }

    /**
     * Fitur Ambil Profil User yang Sedang Aktif
     */
    public function profile(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Data profil berhasil diambil');
    }

    /**
     * Fitur Logout (Hapus Token Sesi)
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus token yang sedang digunakan untuk mengakses API ini
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil, sesi token telah dihapus');
    }
}