@extends('layouts.auth')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Buat Akun TEPO</h1>
    <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
        Daftarkan akun untuk mencatat suasana hati harian, jadwal resep obat, atau menjadi pendamping sebaya anonim.
    </p>
</div>

@if ($errors->any())
    <div class="mb-5 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 text-sm" role="alert">
        <p class="font-semibold mb-1">Periksa Kembali Data Isian</p>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-800">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register.store') }}" class="space-y-4">
    @csrf

    <fieldset>
        <legend class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
            Peran Pengguna <span class="text-rose-600">*</span>
        </legend>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="flex flex-col p-3.5 rounded-lg border border-slate-300 cursor-pointer hover:border-teal-700 has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50/50">
                <span class="flex items-center gap-2 font-semibold text-slate-900 text-sm">
                    <input
                        type="radio"
                        name="role"
                        value="patient"
                        class="text-teal-700 focus:ring-teal-700"
                        {{ old('role', 'patient') === 'patient' ? 'checked' : '' }}
                    >
                    <span>Pasien / Pemantau</span>
                </span>
                <span class="text-xs text-slate-600 mt-1 leading-snug">
                    Catat multi-mood (-3 s/d +3), multi-resep obat, jam tidur, dan laporan.
                </span>
            </label>

            <label class="flex flex-col p-3.5 rounded-lg border border-slate-300 cursor-pointer hover:border-teal-700 has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50/50">
                <span class="flex items-center justify-between">
                    <span class="flex items-center gap-2 font-semibold text-slate-900 text-sm">
                        <input
                            type="radio"
                            name="role"
                            value="counselor"
                            class="text-teal-700 focus:ring-teal-700"
                            {{ old('role') === 'counselor' ? 'checked' : '' }}
                        >
                        <span>Mahasiswa Psikologi</span>
                    </span>
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-200 text-slate-800">DEMO</span>
                </span>
                <span class="text-xs text-slate-600 mt-1 leading-snug">
                    Pendampingan sebaya rahasia (identitas kedua pihak disamarkan).
                </span>
            </label>
        </div>
    </fieldset>

    <div>
        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Nama Lengkap (Dirahasiakan saat Konsultasi) <span class="text-rose-600">*</span>
        </label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name') }}"
            required
            autocomplete="name"
            placeholder="Nama lengkap Anda"
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-teal-700 text-sm bg-white"
        >
    </div>

    <div>
        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Alamat Email <span class="text-rose-600">*</span>
        </label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email') }}"
            required
            autocomplete="email"
            placeholder="nama@email.com"
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-teal-700 text-sm bg-white"
        >
    </div>

    <div>
        <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Nomor Telepon <span class="text-slate-500 font-normal lowercase">(opsional)</span>
        </label>
        <input
            type="tel"
            name="phone"
            id="phone"
            value="{{ old('phone') }}"
            autocomplete="tel"
            placeholder="08123456789"
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-teal-700 text-sm bg-white"
        >
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Kata Sandi <span class="text-rose-600">*</span>
            </label>
            <input
                type="password"
                name="password"
                id="password"
                required
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-teal-700 text-sm bg-white"
            >
        </div>
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Konfirmasi Sandi <span class="text-rose-600">*</span>
            </label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Ulangi kata sandi"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-teal-700 text-sm bg-white"
            >
        </div>
    </div>

    <div class="pt-1">
        <label for="terms" class="flex items-start gap-2.5 cursor-pointer">
            <input
                type="checkbox"
                name="terms"
                id="terms"
                value="1"
                required
                class="w-4 h-4 mt-0.5 text-teal-700 border-slate-300 rounded focus:ring-teal-700 cursor-pointer"
                {{ old('terms') ? 'checked' : '' }}
            >
            <span class="text-xs text-slate-600 leading-relaxed">
                Saya memahami bahwa data pencatatan harian disimpan secara rahasia, dan sesi konsultasi sebaya menerapkan anonimitas dua arah.
            </span>
        </label>
    </div>

    <div class="pt-2">
        <button
            type="submit"
            class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 focus:ring-offset-2 transition-colors cursor-pointer"
        >
            Daftar Akun
        </button>
    </div>
</form>

<div class="mt-6 pt-5 border-t border-slate-200 text-center">
    <p class="text-sm text-slate-600">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-semibold text-teal-700 hover:text-teal-800 hover:underline ml-1">
            Masuk ke Akun
        </a>
    </p>
</div>
@endsection
