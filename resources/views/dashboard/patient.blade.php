<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Pemantauan Harian - TEPO</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- Navigasi Utama -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('patient.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo TEPO" width="36" height="36" class="w-9 h-9 rounded-full object-cover border border-slate-300">
                    <div>
                        <span class="font-bold text-slate-900 tracking-tight block leading-none">TEPO</span>
                        <span class="text-[11px] text-slate-500 font-medium">Pemantauan Bipolar</span>
                    </div>
                </a>

                <nav class="hidden md:flex items-center gap-1" aria-label="Menu Utama">
                    <a href="{{ route('patient.dashboard') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold bg-slate-900 text-white">
                        Pencatatan Harian
                    </a>
                    <a href="{{ route('patient.reports') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        Tabel 2 Minggu & Grafik
                    </a>
                    <a href="{{ route('patient.consultations') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        Konsultasi Anonim (DEMO)
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:block text-right">
                    <span class="text-xs font-semibold text-slate-900 block">{{ $user->name }}</span>
                    <span class="text-[11px] text-slate-500 font-mono">Kode Anonim: {{ $user->anonymous_patient_code }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-md text-xs font-semibold text-slate-700 hover:text-rose-700 border border-slate-300 hover:border-rose-300 bg-white transition-colors cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        <!-- Menu Mobile -->
        <nav class="md:hidden flex border-t border-slate-200 bg-slate-50 px-4 py-2 gap-2 overflow-x-auto" aria-label="Menu Mobile">
            <a href="{{ route('patient.dashboard') }}" class="px-3 py-1.5 rounded text-xs font-semibold bg-slate-900 text-white whitespace-nowrap">Pencatatan Harian</a>
            <a href="{{ route('patient.reports') }}" class="px-3 py-1.5 rounded text-xs font-semibold text-slate-700 bg-white border border-slate-200 whitespace-nowrap">Tabel 2 Minggu & Grafik</a>
            <a href="{{ route('patient.consultations') }}" class="px-3 py-1.5 rounded text-xs font-semibold text-slate-700 bg-white border border-slate-200 whitespace-nowrap">Konsultasi Anonim</a>
        </nav>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">

        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-teal-800">Lembar Catatan Harian Pasien</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">
                    Pemantauan Mood, Jam Tidur, dan Resep Obat
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Tanggal Hari Ini: <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>. Anda dapat mencatat lebih dari satu perubahan suasana hati dan lebih dari satu jenis obat beserta resepnya dalam sehari.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('patient.reports') }}" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 text-xs font-semibold transition-colors">
                    Lihat Tabel 2 Minggu & Laporan PDF
                </a>
                <a href="{{ route('patient.consultations') }}" class="px-3.5 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold transition-colors">
                    Buka Ruang Konsultasi Anonim
                </a>
            </div>
        </div>

        <!-- Notifikasi Sistem -->
        @if (session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="p-4 rounded-lg bg-slate-100 border border-slate-300 text-slate-800 text-sm font-medium" role="status">
                {{ session('info') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 text-sm" role="alert">
                <p class="font-semibold mb-1">Periksa Kembali Data Input:</p>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Ringkasan Otomatis 2 Minggu (14 Hari Terakhir) -->
        <section aria-labelledby="heading-ringkasan">
            <div class="flex items-center justify-between mb-3">
                <h2 id="heading-ringkasan" class="text-sm font-bold uppercase tracking-wider text-slate-700">
                    Ringkasan Otomatis 14 Hari Terakhir
                </h2>
                <span class="text-xs text-slate-500">Terhitung otomatis dari catatan harian Anda</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-lg border border-slate-200">
                    <span class="text-xs text-slate-500 font-medium block">Hari yang Sudah Diisi</span>
                    <span class="text-2xl font-bold text-slate-900 tabular-nums mt-1 block">
                        {{ $clinicalSummary['filled_days'] }} <span class="text-xs font-normal text-slate-500">hari ({{ $clinicalSummary['total_mood_logs'] }} sesi)</span>
                    </span>
                </div>
                <div class="bg-white p-4 rounded-lg border border-slate-200">
                    <span class="text-xs text-slate-500 font-medium block">Rata-Rata Mood (-3 s/d +3)</span>
                    <span class="text-2xl font-bold text-slate-900 tabular-nums mt-1 block">
                        {{ $clinicalSummary['avg_mood'] > 0 ? '+' . number_format($clinicalSummary['avg_mood'], 2) : number_format($clinicalSummary['avg_mood'], 2) }}
                    </span>
                </div>
                <div class="bg-white p-4 rounded-lg border border-slate-200">
                    <span class="text-xs text-slate-500 font-medium block">Mood Terendah / Tertinggi</span>
                    <span class="text-2xl font-bold text-slate-900 tabular-nums mt-1 block">
                        {{ $clinicalSummary['min_mood'] > 0 ? '+' . $clinicalSummary['min_mood'] : $clinicalSummary['min_mood'] }}
                        <span class="text-slate-400 font-normal">/</span>
                        {{ $clinicalSummary['max_mood'] > 0 ? '+' . $clinicalSummary['max_mood'] : $clinicalSummary['max_mood'] }}
                    </span>
                </div>
                <div class="bg-white p-4 rounded-lg border border-slate-200">
                    <span class="text-xs text-slate-500 font-medium block">Rata-Rata Jam Tidur</span>
                    <span class="text-2xl font-bold text-slate-900 tabular-nums mt-1 block">
                        {{ $clinicalSummary['avg_sleep'] }} <span class="text-xs font-normal text-slate-500">jam/malam</span>
                    </span>
                </div>
                <div class="bg-white p-4 rounded-lg border border-slate-200 col-span-2 sm:col-span-1">
                    <span class="text-xs text-slate-500 font-medium block">Hari Minum Obat (Ya)</span>
                    <span class="text-2xl font-bold text-teal-800 tabular-nums mt-1 block">
                        {{ $clinicalSummary['med_days_taken'] }} <span class="text-xs font-normal text-slate-500">dari {{ $clinicalSummary['filled_days'] }} hari tercatat</span>
                    </span>
                </div>
            </div>
        </section>

        <!-- Panduan Skala Klinis Singkat -->
        <section class="bg-white rounded-lg border border-slate-200 p-4 text-xs text-slate-700 space-y-1.5">
            <div class="font-bold text-slate-900">Petunjuk Skala Pengisian Harian:</div>
            <p>
                <span class="font-semibold text-slate-900">Skala Mood (-3 s/d +3):</span>
                <span class="font-mono">-3</span> sangat rendah/depresi &middot;
                <span class="font-mono">-2</span> rendah &middot;
                <span class="font-mono">-1</span> agak turun &middot;
                <span class="font-mono">0</span> stabil &middot;
                <span class="font-mono">+1</span> agak tinggi &middot;
                <span class="font-mono">+2</span> tinggi &middot;
                <span class="font-mono">+3</span> sangat tinggi/sulit dikendalikan (manik).
            </p>
            <p>
                <span class="font-semibold text-slate-900">Skala Energi (1 s/d 5):</span>
                <span class="font-mono">1</span> (sangat lemas) sampai <span class="font-mono">5</span> (sangat berenergi) &middot;
                <span class="font-semibold text-slate-900">Jam Tidur:</span> total jam tidur semalam (contoh: <span class="font-mono">7.5</span> jam).
            </p>
        </section>

        <!-- GRID UTAMA: PENCATATAN MULTI-MOOD & MANAJEMEN MULTI-RESEP OBAT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- KOLOM KIRI (7 KOLOM): Multi-Mood Harian & Jam Tidur -->
            <div class="lg:col-span-7 space-y-8">

                <!-- 1. Pencatatan Multi-Mood & Energi Harian -->
                <section class="bg-white rounded-lg border border-slate-200 p-6" aria-labelledby="heading-mood">
                    <div class="border-b border-slate-200 pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 id="heading-mood" class="text-lg font-bold text-slate-900">
                                1. Catat Suasana Hati & Tingkat Energi
                            </h2>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Dalam sehari Anda dapat mencatat lebih dari 1 mood (misalnya pagi manik, sore depresi, malam stabil).
                            </p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded bg-slate-100 text-slate-800 self-start">
                            Hari ini: {{ $todayMoods->count() }} sesi tercatat
                        </span>
                    </div>

                    <form method="POST" action="{{ route('patient.mood.store') }}" class="space-y-5">
                        @csrf

                        <!-- Waktu Sesi & Jam Pencatatan -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="entry_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tanggal
                                </label>
                                <input
                                    type="date"
                                    name="entry_date"
                                    id="entry_date"
                                    value="{{ old('entry_date', now()->toDateString()) }}"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white tabular-nums"
                                >
                            </div>
                            <div>
                                <label for="period" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Sesi Waktu <span class="text-rose-600">*</span>
                                </label>
                                <select
                                    name="period"
                                    id="period"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white"
                                >
                                    @php
                                        $currentHour = (int) now()->format('H');
                                        $defaultPeriod = $currentHour < 11 ? 'Pagi' : ($currentHour < 15 ? 'Siang' : ($currentHour < 19 ? 'Sore' : 'Malam'));
                                    @endphp
                                    @foreach (['Pagi', 'Siang', 'Sore', 'Malam'] as $p)
                                        <option value="{{ $p }}" {{ old('period', $defaultPeriod) === $p ? 'selected' : '' }}>
                                            Sesi {{ $p }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="entry_time" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jam Pencatatan <span class="text-rose-600">*</span>
                                </label>
                                <input
                                    type="time"
                                    name="entry_time"
                                    id="entry_time"
                                    value="{{ old('entry_time', now()->format('H:i')) }}"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white tabular-nums"
                                >
                            </div>
                        </div>

                        <!-- Pilihan Skala Mood Klinis (-3 s/d +3) -->
                        <fieldset>
                            <legend class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Skala Mood (-3 s/d +3) <span class="text-rose-600">*</span>
                            </legend>
                            <div class="grid grid-cols-1 sm:grid-cols-7 gap-2">
                                @php
                                    $moodScaleOptions = [
                                        -3 => ['label' => 'Sangat Rendah / Depresi', 'short' => 'Depresi Berat'],
                                        -2 => ['label' => 'Rendah', 'short' => 'Rendah'],
                                        -1 => ['label' => 'Agak Turun', 'short' => 'Agak Turun'],
                                        0  => ['label' => 'Stabil / Normal', 'short' => 'Stabil'],
                                        1  => ['label' => 'Agak Tinggi', 'short' => 'Agak Tinggi'],
                                        2  => ['label' => 'Tinggi', 'short' => 'Tinggi'],
                                        3  => ['label' => 'Sangat Tinggi / Manik', 'short' => 'Manik'],
                                    ];
                                    $selectedMoodLevel = (int) old('mood_level', 0);
                                @endphp
                                @foreach ($moodScaleOptions as $val => $info)
                                    <label class="flex sm:flex-col items-center justify-between sm:justify-center p-2.5 rounded-lg border border-slate-300 cursor-pointer hover:border-teal-700 has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50/70 text-center transition-colors">
                                        <input
                                            type="radio"
                                            name="mood_level"
                                            value="{{ $val }}"
                                            class="sr-only"
                                            {{ $selectedMoodLevel === $val ? 'checked' : '' }}
                                        >
                                        <span class="text-base font-bold tabular-nums {{ $val < 0 ? 'text-sky-800' : ($val > 0 ? 'text-amber-800' : 'text-teal-800') }}">
                                            {{ $val > 0 ? '+' . $val : $val }}
                                        </span>
                                        <span class="text-[11px] font-medium text-slate-600 sm:mt-1 leading-tight">
                                            {{ $info['short'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <!-- Pilihan Tingkat Energi (1 s/d 5) -->
                        <fieldset>
                            <legend class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Tingkat Energi (1 = Sangat Lemas, 5 = Sangat Berenergi) <span class="text-rose-600">*</span>
                            </legend>
                            <div class="grid grid-cols-5 gap-2">
                                @php
                                    $energyOptions = [
                                        1 => 'Sangat Lemas',
                                        2 => 'Lemas',
                                        3 => 'Normal',
                                        4 => 'Berenergi',
                                        5 => 'Sangat Tinggi',
                                    ];
                                    $selectedEnergy = (int) old('energy_level', 3);
                                @endphp
                                @foreach ($energyOptions as $eVal => $eLabel)
                                    <label class="flex flex-col items-center justify-center p-2.5 rounded-lg border border-slate-300 cursor-pointer hover:border-teal-700 has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50/70 text-center transition-colors">
                                        <input
                                            type="radio"
                                            name="energy_level"
                                            value="{{ $eVal }}"
                                            class="sr-only"
                                            {{ $selectedEnergy === $eVal ? 'checked' : '' }}
                                        >
                                        <span class="text-base font-bold text-slate-900 tabular-nums">{{ $eVal }}</span>
                                        <span class="text-[11px] text-slate-600 mt-0.5 leading-tight">{{ $eLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <!-- Catatan / Pemicu -->
                        <div>
                            <label for="daily_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Catatan / Pemicu Suasana Hati
                            </label>
                            <textarea
                                name="daily_notes"
                                id="daily_notes"
                                rows="2"
                                placeholder="Contoh: Pagi ini banyak bicara dan banyak ide tugas; atau sore ini merasa takut bertemu orang..."
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white"
                            >{{ old('daily_notes') }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm transition-colors cursor-pointer"
                        >
                            Tambah Catatan Sesi Mood
                        </button>
                    </form>

                    <!-- Daftar Sesi Mood yang Tercatat Hari Ini -->
                    <div class="mt-6 pt-5 border-t border-slate-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                            Riwayat Perubahan Mood Hari Ini ({{ \Carbon\Carbon::today()->format('d M Y') }})
                        </h3>

                        @if ($todayMoods->count() > 0)
                            <div class="space-y-2.5">
                                @foreach ($todayMoods as $mEntry)
                                    <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="space-y-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-xs font-bold text-slate-900 bg-white border border-slate-300 px-2 py-0.5 rounded tabular-nums">
                                                    {{ $mEntry->period }} ({{ \Carbon\Carbon::parse($mEntry->entry_time)->format('H:i') }})
                                                </span>
                                                <span class="text-xs font-bold px-2.5 py-0.5 rounded tabular-nums {{ $mEntry->mood_level <= -2 ? 'bg-sky-100 text-sky-900' : ($mEntry->mood_level >= 2 ? 'bg-amber-100 text-amber-900' : 'bg-teal-100 text-teal-900') }}">
                                                    Mood: {{ $mEntry->formatted_mood_level }} ({{ $mEntry->mood_label }})
                                                </span>
                                                <span class="text-xs font-semibold text-slate-700 bg-slate-200/70 px-2 py-0.5 rounded tabular-nums">
                                                    Energi: {{ $mEntry->energy_level }}/5
                                                </span>
                                            </div>
                                            @if ($mEntry->daily_notes)
                                                <p class="text-xs text-slate-700">
                                                    <span class="font-semibold">Pemicu/Catatan:</span> {{ $mEntry->daily_notes }}
                                                </p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('patient.mood.destroy', $mEntry->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-rose-700 hover:text-rose-900 underline cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-500">Belum ada sesi suasana hati yang dicatat untuk hari ini.</p>
                        @endif
                    </div>

                    <!-- Rekomendasi Koping Otomatis bila Sesi Terakhir Manik atau Depresi -->
                    @if ($todayMood && $todayMood->copingRecommendations->count() > 0)
                        <div class="mt-6 pt-5 border-t border-slate-200">
                            <div class="p-4 rounded-lg border {{ $todayMood->mood === 'manic' ? 'bg-amber-50/70 border-amber-200' : 'bg-sky-50/70 border-sky-200' }}">
                                <h3 class="text-xs font-bold uppercase tracking-wider {{ $todayMood->mood === 'manic' ? 'text-amber-900' : 'text-sky-900' }}">
                                    Saran Regulasi Mandiri untuk Sesi Terakhir ({{ $todayMood->mood_label }})
                                </h3>
                                <div class="mt-3 grid grid-cols-1 gap-2.5">
                                    @foreach ($todayMood->copingRecommendations as $rec)
                                        <div class="bg-white p-3 rounded border border-slate-200">
                                            <p class="text-xs font-bold text-slate-900">{{ $rec->title }}</p>
                                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">{{ $rec->description }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </section>

                <!-- 2. Pencatatan Jam Tidur Harian -->
                <section class="bg-white rounded-lg border border-slate-200 p-6" aria-labelledby="heading-sleep">
                    <div class="border-b border-slate-200 pb-4 mb-5 flex items-center justify-between">
                        <div>
                            <h2 id="heading-sleep" class="text-lg font-bold text-slate-900">
                                2. Catat Total Jam Tidur Semalam
                            </h2>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Isi total jam tidur semalam (misalnya 7.5 jam) atau jam mulai tidur dan jam bangun.
                            </p>
                        </div>
                        @if ($todaySleep)
                            <span class="text-xs font-bold px-2.5 py-1 rounded bg-teal-50 border border-teal-200 text-teal-900 tabular-nums">
                                Tercatat: {{ $todaySleep->duration_hours }} Jam
                            </span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('patient.sleep.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="duration_hours" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Total Jam Tidur (Jam) <span class="text-rose-600">*</span>
                                </label>
                                <input
                                    type="number"
                                    step="0.5"
                                    min="0.5"
                                    max="24"
                                    name="duration_hours"
                                    id="duration_hours"
                                    value="{{ old('duration_hours', $todaySleep->duration_hours ?? '7.5') }}"
                                    required
                                    placeholder="7.5"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 tabular-nums"
                                >
                            </div>
                            <div>
                                <label for="sleep_start" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jam Mulai Tidur
                                </label>
                                <input
                                    type="time"
                                    name="sleep_start"
                                    id="sleep_start"
                                    value="{{ old('sleep_start', $todaySleep ? \Carbon\Carbon::parse($todaySleep->sleep_start)->format('H:i') : '23:00') }}"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 tabular-nums"
                                >
                            </div>
                            <div>
                                <label for="sleep_end" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jam Bangun
                                </label>
                                <input
                                    type="time"
                                    name="sleep_end"
                                    id="sleep_end"
                                    value="{{ old('sleep_end', $todaySleep ? \Carbon\Carbon::parse($todaySleep->sleep_end)->format('H:i') : '06:30') }}"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 tabular-nums"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="quality" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kualitas Tidur (1 - 5)
                                </label>
                                <select name="quality" id="quality" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white">
                                    @php $currentQ = (int) old('quality', $todaySleep->quality ?? 4); @endphp
                                    <option value="5" {{ $currentQ === 5 ? 'selected' : '' }}>5 - Sangat Nyenyak</option>
                                    <option value="4" {{ $currentQ === 4 ? 'selected' : '' }}>4 - Nyenyak / Cukup</option>
                                    <option value="3" {{ $currentQ === 3 ? 'selected' : '' }}>3 - Biasa Saja</option>
                                    <option value="2" {{ $currentQ === 2 ? 'selected' : '' }}>2 - Sering Terbangun</option>
                                    <option value="1" {{ $currentQ === 1 ? 'selected' : '' }}>1 - Sulit Tidur / Insomnia</option>
                                </select>
                            </div>
                            <div>
                                <label for="sleep_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Catatan Tidur
                                </label>
                                <input
                                    type="text"
                                    name="notes"
                                    id="sleep_notes"
                                    value="{{ old('notes', $todaySleep->notes ?? '') }}"
                                    placeholder="Misal: Tidur tepat waktu atau terbangun dini hari"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700"
                                >
                            </div>
                        </div>

                        <button type="submit" class="py-2.5 px-5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm transition-colors cursor-pointer">
                            Simpan Catatan Jam Tidur
                        </button>
                    </form>
                </section>

            </div>

            <!-- KOLOM KANAN (5 KOLOM): Manajemen Multi-Resep Obat & Log Minum Obat Harian -->
            <div class="lg:col-span-5 space-y-8">

                <!-- 3. Daftar Resep Obat Aktif (Masing-Masing Obat Punya Resep Sendiri) -->
                <section class="bg-white rounded-lg border border-slate-200 p-6" aria-labelledby="heading-prescriptions">
                    <div class="border-b border-slate-200 pb-4 mb-4">
                        <h2 id="heading-prescriptions" class="text-lg font-bold text-slate-900">
                            3. Daftar Resep Obat Pribadi
                        </h2>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Setiap jenis obat memiliki dosis dan aturan pakai (resep) masing-masing.
                        </p>
                    </div>

                    @if ($prescriptions->count() > 0)
                        <div class="space-y-3 mb-5">
                            @foreach ($prescriptions as $rx)
                                <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50 flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-bold text-slate-900">{{ $rx->medication_name }}</span>
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-teal-100 text-teal-900 tabular-nums">{{ $rx->dosage }}</span>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-700">
                                            Resep / Jadwal: <span class="font-normal text-slate-800">{{ $rx->frequency }}</span>
                                        </p>
                                        @if ($rx->prescribing_doctor)
                                            <p class="text-[11px] text-slate-500">Dokter: {{ $rx->prescribing_doctor }}</p>
                                        @endif
                                        @if ($rx->instructions)
                                            <p class="text-[11px] text-slate-600">{{ $rx->instructions }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('patient.prescriptions.destroy', $rx->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-700 hover:text-rose-900 font-semibold underline cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500 mb-4">Belum ada resep obat terdaftar. Tambahkan resep obat Anda di bawah ini.</p>
                    @endif

                    <!-- Form Tambah Resep Obat Baru -->
                    <details class="group border border-slate-200 rounded-lg bg-white">
                        <summary class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-teal-800 cursor-pointer select-none flex items-center justify-between">
                            <span>Tambah Jenis Obat & Resep Baru</span>
                            <span class="text-slate-500 group-open:rotate-180 transition-transform">v</span>
                        </summary>
                        <form method="POST" action="{{ route('patient.prescriptions.store') }}" class="p-4 pt-2 border-t border-slate-200 space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="rx_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Obat <span class="text-rose-600">*</span></label>
                                    <input type="text" name="medication_name" id="rx_name" required placeholder="Misal: Valproate" class="w-full px-3 py-2 rounded border border-slate-300 text-xs">
                                </div>
                                <div>
                                    <label for="rx_dosage" class="block text-xs font-semibold text-slate-700 mb-1">Dosis Resep <span class="text-rose-600">*</span></label>
                                    <input type="text" name="dosage" id="rx_dosage" required placeholder="Misal: 250 mg" class="w-full px-3 py-2 rounded border border-slate-300 text-xs">
                                </div>
                            </div>
                            <div>
                                <label for="rx_freq" class="block text-xs font-semibold text-slate-700 mb-1">Aturan Pakai / Jadwal Resep <span class="text-rose-600">*</span></label>
                                <input type="text" name="frequency" id="rx_freq" required placeholder="Misal: 2x1 sehari (Pagi & Malam sesudah makan)" class="w-full px-3 py-2 rounded border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label for="rx_doc" class="block text-xs font-semibold text-slate-700 mb-1">Nama Dokter / Psikiater</label>
                                <input type="text" name="prescribing_doctor" id="rx_doc" placeholder="Misal: dr. Hendra Wijaya, Sp.KJ" class="w-full px-3 py-2 rounded border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label for="rx_inst" class="block text-xs font-semibold text-slate-700 mb-1">Instruksi Khusus Resep</label>
                                <input type="text" name="instructions" id="rx_inst" placeholder="Misal: Diminum sesudah makan, jangan dihentikan mendadak" class="w-full px-3 py-2 rounded border border-slate-300 text-xs">
                            </div>
                            <button type="submit" class="w-full py-2 px-3 rounded bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold cursor-pointer">
                                Simpan ke Daftar Resep
                            </button>
                        </form>
                    </details>
                </section>

                <!-- 4. Pencatatan Konsumsi Multi-Obat Harian -->
                <section class="bg-white rounded-lg border border-slate-200 p-6" aria-labelledby="heading-medlog">
                    <div class="border-b border-slate-200 pb-4 mb-4">
                        <h2 id="heading-medlog" class="text-lg font-bold text-slate-900">
                            4. Catat Konsumsi Obat Hari Ini
                        </h2>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Pilih obat dari resep aktif Anda untuk mencatat setiap obat yang diminum hari ini.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('patient.medication.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="prescription_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Pilih dari Resep Obat Aktif
                            </label>
                            <select
                                name="prescription_id"
                                id="prescription_id"
                                onchange="toggleManualMedFields(this.value)"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white"
                            >
                                @foreach ($prescriptions as $rx)
                                    <option value="{{ $rx->id }}">
                                        {{ $rx->medication_name }} ({{ $rx->dosage }}) - Resep: {{ $rx->frequency }}
                                    </option>
                                @endforeach
                                <option value="">Input Obat Lain Secara Manual...</option>
                            </select>
                        </div>

                        <div id="manual-med-fields" class="{{ $prescriptions->count() > 0 ? 'hidden' : '' }} space-y-3 p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="medication_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Obat</label>
                                    <input type="text" name="medication_name" id="medication_name" placeholder="Nama obat" class="w-full px-3 py-2 rounded border border-slate-300 text-xs bg-white">
                                </div>
                                <div>
                                    <label for="dosage" class="block text-xs font-semibold text-slate-700 mb-1">Dosis</label>
                                    <input type="text" name="dosage" id="dosage" placeholder="Misal: 300 mg" class="w-full px-3 py-2 rounded border border-slate-300 text-xs bg-white">
                                </div>
                            </div>
                            <div>
                                <label for="prescription_rule" class="block text-xs font-semibold text-slate-700 mb-1">Aturan Resep Obat Ini</label>
                                <input type="text" name="prescription_rule" id="prescription_rule" placeholder="Misal: 2x1 sehari pagi & malam" class="w-full px-3 py-2 rounded border border-slate-300 text-xs bg-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="quantity_taken" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jumlah Diminum (Tablet)
                                </label>
                                <input
                                    type="number"
                                    name="quantity_taken"
                                    id="quantity_taken"
                                    value="{{ old('quantity_taken', 1) }}"
                                    min="0"
                                    max="20"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm tabular-nums"
                                >
                            </div>
                            <div>
                                <label for="scheduled_time" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jam Minum
                                </label>
                                <input
                                    type="time"
                                    name="scheduled_time"
                                    id="scheduled_time"
                                    value="{{ old('scheduled_time', now()->format('H:i')) }}"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm tabular-nums"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Status Kepatuhan Resep
                            </label>
                            <select name="status" id="status" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                                <option value="on_time">Ya - Diminum Tepat Waktu Sesuai Resep</option>
                                <option value="missed">Tidak - Dosis Terlewat / Tidak Diminum</option>
                                <option value="overdose">Peringatan - Diminum Melebihi Dosis Resep</option>
                            </select>
                        </div>

                        <div>
                            <label for="med_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Catatan Tambahan
                            </label>
                            <input
                                type="text"
                                name="notes"
                                id="med_notes"
                                placeholder="Misal: Diminum sesudah makan pagi"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm"
                            >
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm transition-colors cursor-pointer">
                            Catat Minum Obat
                        </button>
                    </form>

                    <!-- Daftar Obat yang Sudah Dicatat Hari Ini -->
                    <div class="mt-6 pt-5 border-t border-slate-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                            Rekam Obat yang Diminum Hari Ini ({{ $todayMeds->count() }} Jenis/Dosis)
                        </h3>

                        @if ($todayMeds->count() > 0)
                            <div class="space-y-2.5">
                                @foreach ($todayMeds as $med)
                                    <div class="p-3 rounded-lg border border-slate-200 bg-slate-50 flex items-start justify-between gap-2">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-bold text-slate-900">{{ $med->medication_name }} ({{ $med->dosage }})</span>
                                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded {{ $med->status === 'on_time' ? 'bg-emerald-100 text-emerald-900' : ($med->status === 'missed' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-900') }}">
                                                    {{ $med->status === 'on_time' ? 'Ya (Sesuai Resep)' : ($med->status === 'missed' ? 'Tidak Diminum' : 'Melebihi Dosis') }}
                                                </span>
                                            </div>
                                            @if ($med->prescription_rule)
                                                <p class="text-[11px] text-slate-600">Resep: {{ $med->prescription_rule }}</p>
                                            @endif
                                            <p class="text-[11px] text-slate-500 tabular-nums">
                                                Jumlah: {{ $med->quantity_taken }} tablet
                                                @if ($med->scheduled_time)
                                                    &middot; Jam {{ \Carbon\Carbon::parse($med->scheduled_time)->format('H:i') }}
                                                @endif
                                                @if ($med->notes)
                                                    &middot; {{ $med->notes }}
                                                @endif
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('patient.medication.destroy', $med->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-rose-700 hover:text-rose-900 underline cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-500">Belum ada obat yang dicatat hari ini.</p>
                        @endif
                    </div>
                </section>

            </div>
        </div>

        <!-- TABEL RINGKAS CATATAN HARIAN 2 MINGGU -->
        <section class="bg-white rounded-lg border border-slate-200 overflow-hidden" aria-labelledby="heading-table-2weeks">
            <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 id="heading-table-2weeks" class="text-lg font-bold text-slate-900">
                        Tabel Catatan Mood Harian (14 Hari Terakhir)
                    </h2>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Menampilkan seluruh sesi mood (-3 s/d +3), jam tidur, energi (1-5), kepatuhan multi-obat, serta catatan pemicu.
                    </p>
                </div>
                <a href="{{ route('patient.reports') }}" class="px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold self-start sm:self-auto">
                    Buka Halaman Grafik & Cetak PDF
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Hari & Tanggal</th>
                            <th class="py-3 px-4">Sesi Waktu</th>
                            <th class="py-3 px-4">Mood (-3 s/d +3)</th>
                            <th class="py-3 px-4">Jam Tidur</th>
                            <th class="py-3 px-4">Energi (1-5)</th>
                            <th class="py-3 px-4">Minum Obat & Resep</th>
                            <th class="py-3 px-4">Catatan / Pemicu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php
                            $dayMap = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
                        @endphp
                        @forelse ($entries14Days as $entry)
                            @php
                                $dateKey = $entry->entry_date->toDateString();
                                $sleepItem = $sleeps14Days->get($dateKey);
                                $medItems = $meds14Days->get($dateKey, collect());
                                $hasTakenMed = $medItems->where('status', 'on_time')->count() > 0;
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-semibold text-slate-900 whitespace-nowrap tabular-nums">
                                    {{ $dayMap[$entry->entry_date->format('D')] ?? $entry->entry_date->format('D') }}, {{ $entry->entry_date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 whitespace-nowrap tabular-nums">
                                    {{ $entry->period }} ({{ \Carbon\Carbon::parse($entry->entry_time)->format('H:i') }})
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-bold tabular-nums px-2 py-0.5 rounded {{ $entry->mood_level <= -2 ? 'bg-sky-100 text-sky-900' : ($entry->mood_level >= 2 ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-900') }}">
                                        {{ $entry->formatted_mood_level }}
                                    </span>
                                    <span class="text-slate-600 ml-1">{{ $entry->mood_label }}</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap tabular-nums text-slate-800">
                                    {{ $sleepItem ? $sleepItem->duration_hours . ' jam' : '-' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap tabular-nums font-semibold text-slate-800">
                                    {{ $entry->energy_level }} / 5
                                </td>
                                <td class="py-3 px-4">
                                    @if ($medItems->count() > 0)
                                        <span class="font-bold {{ $hasTakenMed ? 'text-emerald-800' : 'text-rose-800' }}">
                                            {{ $hasTakenMed ? 'Ya' : 'Tidak' }}
                                        </span>
                                        <span class="text-slate-500 block text-[11px]">
                                            {{ $medItems->where('status', 'on_time')->pluck('medication_name')->unique()->implode(', ') ?: 'Dosis terlewat' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-700 max-w-xs">
                                    {{ $entry->daily_notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">
                                    Belum ada catatan harian. Silakan isi form di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <footer class="bg-white border-t border-slate-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <span>&copy; {{ date('Y') }} TEPO (Teman Bipolar). Sistem Pemantauan Klinis Mandiri.</span>
            <span>Privasi Terlindungi &middot; Konsultasi Sebaya Anonim Dua Arah</span>
        </div>
    </footer>

    <script>
        function toggleManualMedFields(val) {
            const box = document.getElementById('manual-med-fields');
            if (val === '') {
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
