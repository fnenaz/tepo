<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Token');

        if (is_string($token)) {
            $token = trim($token);
        }

        if (! empty($token) && ! str_starts_with($token, '{{')) {
            try {
                $userId = (int) Crypt::decryptString($token);
                $user = User::find($userId);

                if (! $user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token tidak valid atau pengguna tidak ditemukan.',
                    ], 401);
                }

                Auth::setUser($user);

                return $next($request);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token autentikasi tidak valid atau kedaluwarsa.',
                ], 401);
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated. Silakan login terlebih dahulu melalui POST /api/login.',
        ], 401);
    }
}
