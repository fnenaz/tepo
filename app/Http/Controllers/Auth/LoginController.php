<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Menampilkan form login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses autentikasi login pengguna.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Mencegah serangan Session Fixation
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // Redirect intended jika ada url sebelumnya, atau fallback ke dashboard sesuai role
        return redirect()->intended($this->getDashboardRouteForUser($user));
    }

    /**
     * Memproses logout pengguna dengan aman.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Invalidate session saat ini
        $request->session()->invalidate();

        // Regenerasi CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    /**
     * Menentukan route dashboard berdasarkan role pengguna.
     */
    protected function getDashboardRouteForUser(User $user): string
    {
        return match ($user->role) {
            'counselor' => route('counselor.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('patient.dashboard'),
        };
    }
}
