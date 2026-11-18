<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Menampilkan form registrasi pengguna baru.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Memproses pendaftaran pengguna baru.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        // Login otomatis setelah registrasi berhasil
        Auth::login($user);

        // Regenerasi session ID untuk mencegah session fixation
        $request->session()->regenerate();

        // Redirect sesuai peran
        return $this->redirectBasedOnRole($user);
    }

    /**
     * Menentukan redirect tujuan berdasarkan role pengguna.
     */
    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'counselor' => redirect()->route('counselor.dashboard')->with('success', 'Selamat datang di TEPO! Akun mahasiswa psikologi Anda telah aktif.'),
            'admin' => redirect()->route('admin.dashboard')->with('success', 'Selamat datang Admin TEPO!'),
            default => redirect()->route('patient.dashboard')->with('success', 'Selamat datang di TEPO! Mari mulai melacak kesehatan mental Anda hari ini.'),
        };
    }
}
