<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string', // Bisa berisi email atau nomor HP
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $result = $this->authService->login($data);

        return response()->json($result);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[0-9]/'],
            'role' => 'sometimes|string|in:admin,customer,kurir',
            'phone' => 'nullable|string',
        ]);

        $user = $this->authService->register($data);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user
        ], 201);
    }

    public function googleLogin(Request $request)
    {
        $data = $request->validate([
            'credential' => 'required',
            'device_name' => 'required',
        ]);

        $result = $this->authService->googleLogin($data);

        return response()->json($result);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logout berhasil']);
    }
}