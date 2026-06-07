<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek apakah user sudah login dan role-nya ada di daftar yang diizinkan
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json([
                'success' => false, 
                'message' => 'Akses ditolak: Kamu tidak memiliki izin untuk fitur ini.'
            ], 403);
        }

        return $next($request);
    }
}