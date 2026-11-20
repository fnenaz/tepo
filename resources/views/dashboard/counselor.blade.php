<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Pendamping Sebaya Anonim (DEMO) - TEPO</title>
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

    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo TEPO" width="36" height="36" class="w-9 h-9 rounded-full object-cover border border-slate-300">
                <div>
                    <span class="font-bold text-slate-900 tracking-tight block leading-none">TEPO - Panel Mahasiswa Psikologi (DEMO)</span>
                    <span class="text-[11px] text-slate-500 font-mono">Kode Samaran Anda di Layar Pasien: {{ $user->anonymous_counselor_code }}</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-md text-xs font-semibold text-slate-700 hover:text-rose-700 border border-slate-300 bg-white cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">

        <!-- Banner Penjelasan Kerahasiaan Dua Arah untuk Mahasiswa Psikologi -->
        <section class="bg-white rounded-lg border border-slate-200 p-6">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider text-teal-800">Protokol Etika & Anonimitas Dua Arah</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-800">DEMO</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">
                Daftar Sesi Pendampingan Pasien Anonim
            </h1>
            <p class="text-sm text-slate-600 mt-1.5 max-w-3xl leading-relaxed">
                Demi menjaga ruang aman bagi kedua belah pihak, <strong class="text-slate-900">Anda tidak dapat melihat nama, email, maupun nomor telepon pasien</strong>, dan pasien juga hanya melihat Anda sebagai <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $user->anonymous_counselor_code }}</span>. Kebijakan ini memastikan pasien dapat menyampaikan kondisi sebenarnya secara jujur tanpa rasa takut dihakimi, sementara Anda memperoleh pengalaman pendampingan kasus yang autentik.
            </p>
        </section>

        <!-- Daftar Sesi Konsultasi Masuk -->
        <section class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Antrean & Riwayat Sesi Konsultasi</h2>
                <span class="text-xs font-semibold text-slate-600 tabular-nums">{{ $consultations->count() }} sesi terdaftar</span>
            </div>

            @if ($consultations->count() > 0)
                <div class="divide-y divide-slate-200">
                    @foreach ($consultations as $session)
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <span class="text-xs font-bold font-mono text-slate-900 bg-slate-100 border border-slate-300 px-2.5 py-0.5 rounded">
                                        {{ $session->patient ? $session->patient->anonymous_patient_code : 'Pasien Anonim' }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $session->status === 'active' ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $session->status === 'active' ? 'Sesi Aktif' : 'Selesai' }}
                                    </span>
                                    <span class="text-xs text-slate-500 tabular-nums">
                                        Dimulai: {{ $session->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-800">
                                    <span class="font-semibold">Keluhan Awal:</span> {{ $session->notes }}
                                </p>
                                <p class="text-xs text-slate-500 tabular-nums">
                                    Total interaksi: {{ $session->chatMessages->count() }} pesan
                                </p>
                            </div>

                            <a
                                href="{{ route('consultations.show', $session->id) }}"
                                class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold whitespace-nowrap self-start sm:self-center"
                            >
                                Buka Ruang Obrolan Anonim
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-sm text-slate-500">
                    Belum ada sesi konsultasi anonim yang masuk.
                </div>
            @endif
        </section>

    </main>
</body>
</html>
