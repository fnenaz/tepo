@extends('layouts.auth')

@section('title', 'Masuk ke Akun')

@section('content')
<div class="mb-7">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Masuk ke Akun TEPO</h1>
    <p class="text-sm text-slate-600 mt-2 leading-relaxed">
        Lanjutkan pencatatan mood harian, pemantauan jam tidur, kepatuhan resep obat, atau sesi konsultasi anonim Anda.
    </p>
</div>

@if ($errors->any())
    <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 text-sm" role="alert">
        <p class="font-semibold mb-1">Autentikasi Gagal</p>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-800">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login.store') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Alamat Email
        </label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="email"
            placeholder="nama@email.com"
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-teal-700 text-sm text-slate-900 bg-white"
        >
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Kata Sandi
        </label>
        <div class="relative">
            <input
                type="password"
                name="password"
                id="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                class="w-full pl-3.5 pr-20 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-teal-700 text-sm text-slate-900 bg-white"
            >
            <button
                type="button"
                onclick="togglePassword()"
                id="toggle-password-btn"
                class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-600 hover:text-slate-900 focus:outline-none cursor-pointer"
                aria-label="Tampilkan atau sembunyikan kata sandi"
            >
                Lihat
            </button>
        </div>
    </div>

    <div class="flex items-center justify-between pt-1">
        <label for="remember" class="flex items-center gap-2 cursor-pointer select-none">
            <input
                type="checkbox"
                name="remember"
                id="remember"
                value="1"
                class="w-4 h-4 text-teal-700 border-slate-300 rounded focus:ring-teal-700 cursor-pointer"
            >
            <span class="text-xs font-medium text-slate-700">Ingat sesi perangkat ini</span>
        </label>
    </div>

    <div class="pt-1">
        <button
            type="submit"
            class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 focus:ring-offset-2 transition-colors cursor-pointer"
        >
            Masuk
        </button>
    </div>
</form>

<div class="mt-6 p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">
    <p class="font-semibold text-slate-800">Akun Pengujian Cepat (Kata Sandi: <code class="font-mono bg-slate-200 px-1 py-0.5 rounded">password123</code>)</p>
    <p>Pasien (Data Klinis 14 Hari): <code class="font-mono text-slate-800">pasien@tepo.test</code></p>
    <p>Mahasiswa Psikologi (Anonim): <code class="font-mono text-slate-800">konselor1@tepo.test</code></p>
</div>

<div class="mt-6 pt-5 border-t border-slate-200 text-center">
    <p class="text-sm text-slate-600">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-semibold text-teal-700 hover:text-teal-800 hover:underline ml-1">
            Daftar Akun Baru
        </a>
    </p>
</div>
@endsection

@section('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const btn = document.getElementById('toggle-password-btn');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            btn.textContent = 'Sembunyikan';
        } else {
            passwordInput.type = 'password';
            btn.textContent = 'Lihat';
        }
    }
</script>
@endsection
