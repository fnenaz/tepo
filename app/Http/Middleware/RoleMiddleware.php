<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $isApi = $request->is('api/*') || $request->expectsJson();

        if (! Auth::check()) {
            if ($isApi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu melalui POST /api/login.',
                ], 401);
            }

            return redirect()->route('login')->with('warning', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $next($request);
        }

        if (! in_array($user->role, $roles, true)) {
            if ($isApi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses resource ini.',
                ], 403);
            }

            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
