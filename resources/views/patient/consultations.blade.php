<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsultasi Sebaya Anonim (DEMO) - TEPO</title>
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
                    <a href="{{ route('patient.dashboard') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        Pencatatan Harian
                    </a>
                    <a href="{{ route('patient.reports') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        Tabel 2 Minggu & Grafik
                    </a>
                    <a href="{{ route('patient.consultations') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold bg-slate-900 text-white">
                        Konsultasi Anonim (DEMO)
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-mono text-slate-600 bg-slate-100 px-2.5 py-1 rounded border border-slate-200">
                    Identitas Anda: {{ $user->anonymous_patient_code }}
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">

        <!-- Header & Jaminan Kerahasiaan Dua Arah (Double-Blind Anonymity) -->
        <div class="border-b border-slate-200 pb-6">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-teal-800">Protokol Kerahasiaan Dua Arah</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-800">FITUR DEMO</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">
                Ruang Konsultasi Sebaya Anonim
            </h1>
            <p class="text-sm text-slate-600 mt-1.5 max-w-3xl leading-relaxed">
                Pada fitur ini, <strong class="text-slate-900">identitas kedua belah pihak disamarkan sepenuhnya</strong>. Mahasiswa psikologi tidak mengetahui nama, email, atau identitas asli Anda, dan Anda juga tidak mengetahui identitas pribadi mahasiswa pendamping. Sistem hanya menampilkan kode samaran (<span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $user->anonymous_patient_code }}</span>) agar Anda dapat bercerita secara lepas tanpa rasa takut dihakimi.
            </p>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 text-sm" role="alert">
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Kolom Kiri (5 Kolom): Mulai Sesi Konsultasi Anonim Baru -->
            <section class="lg:col-span-5 bg-white rounded-lg border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-900">Mulai Sesi Konsultasi Rahasia</h2>
                <p class="text-xs text-slate-600 mt-1 mb-5">
                    Pilih penugasan acak otomatis atau pilih berdasarkan bidang fokus pendamping sebaya di bawah ini.
                </p>

                <form method="POST" action="{{ route('patient.consultations.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="counselor_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Pendamping Sebaya (Anonim)
                        </label>
                        <select
                            name="counselor_id"
                            id="counselor_id"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white"
                        >
                            <option value="">Penugasan Acak Rahasia Otomatis (Direkomendasikan)</option>
                            @foreach ($counselors as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->anonymous_counselor_code }} ({{ Str::limit($c->bio, 55) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Cerita / Keluhan yang Ingin Disampaikan <span class="text-rose-600">*</span>
                        </label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="4"
                            required
                            placeholder="Ceritakan apa yang sedang Anda rasakan hari ini dengan leluasa. Identitas Anda terlindungi sepenuhnya..."
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 bg-white"
                        >{{ old('notes') }}</textarea>
                    </div>

                    <div class="p-3 rounded bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">
                        <p class="font-semibold text-slate-800">Ringkasan Privasi Sesi Ini:</p>
                        <p>1. Nama asli Anda disembunyikan dan diganti menjadi <strong class="font-mono text-slate-900">{{ $user->anonymous_patient_code }}</strong>.</p>
                        <p>2. Pendamping hanya melihat kode samaran dan isi pesan yang Anda kirimkan di ruang obrolan.</p>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm transition-colors cursor-pointer">
                        Mulai Obrolan Anonim Sekarang
                    </button>
                </form>
            </section>

            <!-- Kolom Kanan (7 Kolom): Daftar Ruang Konsultasi Anonim Saya & Daftar Fokus Pendamping -->
            <div class="lg:col-span-7 space-y-6">

                <section class="bg-white rounded-lg border border-slate-200 p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">
                        Daftar Sesi Konsultasi Anonim Anda
                    </h2>

                    @if ($myConsultations->count() > 0)
                        <div class="space-y-3">
                            @foreach ($myConsultations as $item)
                                <div class="p-4 rounded-lg border border-slate-200 hover:border-slate-300 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-bold font-mono text-slate-900 bg-white border border-slate-300 px-2 py-0.5 rounded">
                                                {{ $item->counselor ? $item->counselor->anonymous_counselor_code : 'Pendamping Anonim' }}
                                            </span>
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded {{ $item->status === 'active' ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-700' }}">
                                                {{ $item->status === 'active' ? 'Sesi Aktif' : 'Selesai' }}
                                            </span>
                                            <span class="text-xs text-slate-500 tabular-nums">
                                                {{ $item->created_at->format('d M Y, H:i') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-700 line-clamp-2">
                                            {{ $item->notes }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            Total pesan: {{ $item->chatMessages->count() }} pesan
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route('consultations.show', $item->id) }}"
                                        class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold whitespace-nowrap self-start sm:self-center"
                                    >
                                        Buka Ruang Obrolan
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500 py-4">
                            Anda belum memiliki riwayat sesi konsultasi anonim. Silakan mulai sesi baru melalui formulir di sebelah kiri.
                        </p>
                    @endif
                </section>

                <!-- Informasi Bidang Fokus Pendamping Sebaya (Tanpa Identitas Pribadi) -->
                <section class="bg-white rounded-lg border border-slate-200 p-6">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-3">
                        Daftar Kode Pendamping Sebaya yang Tersedia
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($counselors as $c)
                            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold font-mono text-slate-900">{{ $c->anonymous_counselor_code }}</span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-teal-100 text-teal-900">Identitas Rahasia</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                    {{ $c->bio ?: 'Fokus Pendampingan: Konseling sebaya empatik dan regulasi suasana hati harian.' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

            </div>
        </div>

    </main>
</body>
</html>
