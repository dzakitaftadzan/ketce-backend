<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[] ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 2. Cek apakah role user ada di dalam daftar roles yang diizinkan
        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden: You do not have the required access.'
            ], 403);
        }

        return $next($request);
    }
}