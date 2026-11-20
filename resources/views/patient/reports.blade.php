<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan 2 Minggu & Grafik Klinis - TEPO</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
            .print-break-avoid { break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- Navigasi Utama -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 no-print">
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
                    <a href="{{ route('patient.reports') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold bg-slate-900 text-white">
                        Tabel 2 Minggu & Grafik
                    </a>
                    <a href="{{ route('patient.consultations') }}" class="px-3.5 py-2 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        Konsultasi Anonim (DEMO)
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-3.5 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cetak / Simpan PDF Klinis
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">

        <!-- Header Laporan & Filter Periode -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-teal-800">Rekam Pemantauan Klinis Pasien</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">
                    Catatan Mood Harian ({{ $days }} Hari Terakhir)
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Pasien: <span class="font-semibold text-slate-900">{{ $user->name }}</span> &middot;
                    Periode: <span class="font-semibold text-slate-900 tabular-nums">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2 no-print">
                @foreach ([7 => '7 Hari', 14 => '2 Minggu (14 Hari)', 30 => '30 Hari'] as $dVal => $dLabel)
                    <a
                        href="{{ route('patient.reports', ['days' => $dVal]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors {{ $days === $dVal ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100' }}"
                    >
                        {{ $dLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Evaluasi Klinis Otomatis -->
        <section class="p-5 rounded-lg border {{ $weeklyEval->urgent_control ? 'bg-amber-50 border-amber-300 text-amber-950' : 'bg-white border-slate-200 text-slate-900' }} print-break-avoid">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                <h2 class="text-sm font-bold uppercase tracking-wider">
                    Ringkasan Evaluasi Sistem
                </h2>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded {{ $weeklyEval->urgent_control ? 'bg-amber-200 text-amber-950' : 'bg-emerald-100 text-emerald-900' }}">
                    {{ $weeklyEval->urgent_control ? 'Disarankan Konsultasi Klinis' : 'Kondisi Terpantau Terkendali' }}
                </span>
            </div>
            <p class="text-sm leading-relaxed">
                {{ $weeklyEval->system_recommendation }}
            </p>
        </section>

        <!-- Ringkasan Otomatis (Persis Format Lembar Catatan Mood 2 Minggu) -->
        <section class="bg-white rounded-lg border border-slate-200 overflow-hidden print-break-avoid" aria-labelledby="heading-auto-summary">
            <div class="px-6 py-4 bg-slate-100 border-b border-slate-200">
                <h2 id="heading-auto-summary" class="text-sm font-bold uppercase tracking-wider text-slate-800">
                    Ringkasan (Terhitung Otomatis)
                </h2>
            </div>
            <div class="divide-y divide-slate-200 text-sm">
                <div class="px-6 py-3 flex items-center justify-between">
                    <span class="text-slate-700 font-medium">Hari yang sudah diisi</span>
                    <span class="font-bold text-slate-900 tabular-nums">{{ $summaryMetrics['filled_days'] }} hari ({{ $summaryMetrics['total_mood_entries'] }} catatan sesi)</span>
                </div>
                <div class="px-6 py-3 flex items-center justify-between">
                    <span class="text-slate-700 font-medium">Rata-rata mood (skala -3 s/d +3)</span>
                    <span class="font-bold text-slate-900 tabular-nums">{{ $summaryMetrics['avg_mood'] > 0 ? '+' . number_format($summaryMetrics['avg_mood'], 2) : number_format($summaryMetrics['avg_mood'], 2) }}</span>
                </div>
                <div class="px-6 py-3 flex items-center justify-between">
                    <span class="text-slate-700 font-medium">Mood terendah / tertinggi</span>
                    <span class="font-bold text-slate-900 tabular-nums">
                        {{ $summaryMetrics['min_mood'] > 0 ? '+' . $summaryMetrics['min_mood'] : $summaryMetrics['min_mood'] }}
                        /
                        {{ $summaryMetrics['max_mood'] > 0 ? '+' . $summaryMetrics['max_mood'] : $summaryMetrics['max_mood'] }}
                    </span>
                </div>
                <div class="px-6 py-3 flex items-center justify-between">
                    <span class="text-slate-700 font-medium">Rata-rata jam tidur</span>
                    <span class="font-bold text-slate-900 tabular-nums">{{ $summaryMetrics['avg_sleep'] }} jam</span>
                </div>
                <div class="px-6 py-3 flex items-center justify-between">
                    <span class="text-slate-700 font-medium">Hari minum obat (Ya)</span>
                    <span class="font-bold text-teal-800 tabular-nums">{{ $summaryMetrics['med_days_yes'] }} hari</span>
                </div>
            </div>
        </section>

        <!-- Grafik Visualisasi Tren Mood (-3 s/d +3), Energi (1-5), dan Jam Tidur -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 print-break-avoid">
            <section class="bg-white rounded-lg border border-slate-200 p-6">
                <h2 class="text-base font-bold text-slate-900">Grafik Skala Mood (-3 s/d +3) & Energi (1-5)</h2>
                <p class="text-xs text-slate-500 mt-0.5 mb-4">Perbandingan fluktuasi suasana hati dan tingkat energi harian</p>
                <div class="h-64">
                    <canvas id="moodEnergyChart"></canvas>
                </div>
            </section>

            <section class="bg-white rounded-lg border border-slate-200 p-6">
                <h2 class="text-base font-bold text-slate-900">Grafik Durasi Jam Tidur Harian</h2>
                <p class="text-xs text-slate-500 mt-0.5 mb-4">Total jam tidur semalam dalam rentang periode</p>
                <div class="h-64">
                    <canvas id="sleepChart"></canvas>
                </div>
            </section>
        </div>

        <!-- Daftar Resep Obat Aktif Pasien -->
        <section class="bg-white rounded-lg border border-slate-200 p-6 print-break-avoid">
            <h2 class="text-base font-bold text-slate-900 mb-3">Daftar Resep Obat Aktif Pasien</h2>
            @if ($prescriptions->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($prescriptions as $rx)
                        <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-900">{{ $rx->medication_name }}</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-teal-100 text-teal-900 tabular-nums">{{ $rx->dosage }}</span>
                            </div>
                            <p class="text-xs text-slate-700 mt-1 font-medium">Resep: {{ $rx->frequency }}</p>
                            @if ($rx->prescribing_doctor)
                                <p class="text-[11px] text-slate-500 mt-0.5">Dokter: {{ $rx->prescribing_doctor }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500">Belum ada data resep obat terdaftar.</p>
            @endif
        </section>

        <!-- Tabel Lembar Catatan Harian Lengkap (Format Klinis 2 Minggu) -->
        <section class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-base font-bold text-slate-900">
                    Tabel Lembar Pemantauan Harian
                </h2>
                <p class="text-xs text-slate-600 mt-0.5">
                    Mood: -3 sangat rendah/depresi &middot; -2 rendah &middot; -1 agak turun &middot; 0 stabil &middot; +1 agak tinggi &middot; +2 tinggi &middot; +3 sangat tinggi/sulit dikendalikan
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Hari</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Mood (-3 s/d +3)</th>
                            <th class="py-3 px-4">Jam Tidur</th>
                            <th class="py-3 px-4">Energi (1-5)</th>
                            <th class="py-3 px-4">Minum Obat</th>
                            <th class="py-3 px-4">Catatan / Pemicu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($dailyRows as $row)
                            <tr class="hover:bg-slate-50 align-top">
                                <td class="py-3 px-4 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $row['day_name'] }}
                                </td>
                                <td class="py-3 px-4 text-slate-800 whitespace-nowrap tabular-nums">
                                    {{ $row['date_formatted'] }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="space-y-1">
                                        @foreach ($row['moods'] as $m)
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold tabular-nums px-2 py-0.5 rounded {{ $m->mood_level <= -2 ? 'bg-sky-100 text-sky-900' : ($m->mood_level >= 2 ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-900') }}">
                                                    {{ $m->formatted_mood_level }}
                                                </span>
                                                <span class="text-slate-600">({{ $m->period }} {{ \Carbon\Carbon::parse($m->entry_time)->format('H:i') }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap tabular-nums font-medium text-slate-900">
                                    {{ $row['sleep'] ? (float) $row['sleep']->duration_hours . ' jam' : '-' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap tabular-nums font-semibold text-slate-900">
                                    @foreach ($row['moods'] as $m)
                                        <div>{{ $m->energy_level }}</div>
                                    @endforeach
                                </td>
                                <td class="py-3 px-4">
                                    @if ($row['meds']->count() > 0)
                                        <span class="font-bold {{ $row['took_medication'] ? 'text-emerald-800' : 'text-rose-800' }}">
                                            {{ $row['took_medication'] ? 'Ya' : 'Tidak' }}
                                        </span>
                                        <div class="text-[11px] text-slate-500 mt-0.5 space-y-0.5">
                                            @foreach ($row['meds'] as $medItem)
                                                <div>{{ $medItem->medication_name }} ({{ $medItem->dosage }})</div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-800">
                                    @foreach ($row['moods'] as $m)
                                        @if ($m->daily_notes)
                                            <div>{{ $m->daily_notes }}</div>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">
                                    Belum ada data pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <script>
        const labels = @json($chartLabels);
        const moodData = @json($moodLevelsData);
        const energyData = @json($energyLevelsData);
        const sleepData = @json($sleepHoursData);

        new Chart(document.getElementById('moodEnergyChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Skala Mood (-3 s/d +3)',
                        data: moodData,
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.1)',
                        borderWidth: 2,
                        tension: 0.25,
                        spanGaps: true,
                        pointRadius: 4
                    },
                    {
                        label: 'Tingkat Energi (1 s/d 5)',
                        data: energyData,
                        borderColor: '#b45309',
                        backgroundColor: 'rgba(180, 83, 9, 0.08)',
                        borderDash: [4, 4],
                        borderWidth: 2,
                        tension: 0.25,
                        spanGaps: true,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: -3,
                        max: 5,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        new Chart(document.getElementById('sleepChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Jam Tidur (Jam)',
                        data: sleepData,
                        backgroundColor: '#334155',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 14
                    }
                }
            }
        });
    </script>
</body>
</html>
