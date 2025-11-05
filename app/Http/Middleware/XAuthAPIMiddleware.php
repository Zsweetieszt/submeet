<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XAuthAPIMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil nilai header X-AUTH
        $xAuth = $request->header('x-api-key');

        // Bisa diset di .env biar aman
        $validToken = env('X_AUTH_TOKEN');

        // Jika token tidak diset di env atau header tidak cocok, tolak akses
        if (empty($validToken) || $xAuth !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
